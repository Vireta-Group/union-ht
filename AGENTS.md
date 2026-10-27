# AGENTS.md — ViretaDev / HT X ViretaDev

Multi-tenant SaaS portal for Bangladeshi union parishads, built by converting a
single-institute CodeIgniter 3 app. PHP + Tailwind (public) + Material Design Kit
(admin, mid-migration) + PostgreSQL.

The user writes Banglish. **All user-facing text must be in Banglish/Bengali.**

## Read these first

| File | What it settles |
|---|---|
| `docs/SADMIN-PANEL.md` | the 7-page platform panel, `require_sadmin()`, the read-only user module |
| `docs/ARCHITECTURE.md` | how the app is wired |
| `docs/TENANT-MIGRATION.md` | tenant resolution, the two-id trap, the isolation bugs |
| `docs/DISCOUNT.md` | the ছাড় feature and the three "obvious wrong changes" |
| `docs/MEDIA-STORAGE.md` | images out of base64 columns into `uploads/`, the `media_url()` contract |
| `docs/DESIGN-SYSTEM.md` | palette, token layers, the namespace collision |
| `docs/REDESIGN-SCOPE.md` | the original scope ledger (superseded by `REMAINING-WORK.md`) |
| `docs/REMAINING-WORK.md` | **the current progress ledger** — what is left, in order |
| `deploy/DEPLOY.md` | VPS deploy, nginx rules replacing `.htaccess`, production checks |

`docs/DESIGN-SYSTEM.md`'s status header still says "Home page not yet rebuilt" —
stale. Trust `REMAINING-WORK.md` for progress.

## Repo state — check before assuming

- **No git repository.** No branches, no diffs, no `git checkout`. Rollback is
  manual copies in `/home/darkness/vireta-private/` (`db_dumps/`, `backups/`,
  `migrations/`). Take a `pg_dump` before any schema change.
- **No automated tests. No CI. No linter config.**
- **No `composer.json`.** `vendor/` exists but is **not reinstallable from this
  repo** (mpdf, tecnickcom/tcpdf, spatie/pdf-to-image) while `config.php` sets
  `composer_autoload = 'vendor/autoload.php'`. `composer install` cannot reproduce
  it. **Do not delete or regenerate `vendor/`** — `Download.php` (mPDF) depends on it.
- **No `package.json` and no `node_modules`.** `npm run build` fails with ENOENT.
  See "CSS" below — this is load-bearing and easy to get wrong.
- **The user edits files in parallel.** Re-read a file immediately before editing.

## Runtime

```bash
php -S localhost:8080 router.php        # the dev server
```

- **`router.php` is required, not `-t .`.** It mirrors the `/.htaccess` deny rules
  because `php -S` ignores `.htaccess` — without it `.env` and any dump are served
  raw. It also blocks script extensions under `uploads/`, which
  `uploads/.htaccess` does explicitly (the root `AddHandler` for `.php` is
  recursive).
- **`/etc/hosts` is not writable without sudo**, so resolve tenants with a header:
  `curl -H "Host: dhum.local" http://localhost:8080/`. The browser tool is not
  connected to this app.
- **`/t_diag` is the fastest way to understand any request.** Host, `id()`,
  `un_id()`, resolution source, full trace, the `ins_titute` row, per-table row
  counts, every `tenant_domains` / `platform_domains` row, hosts in
  `domain_names` with no domain row, per-tenant branding, and a `media_url()`
  self-test. Gated on `is_development` **and** the literal `dev_hosts` list — not
  the `.local` suffix rule — so it answers only on `localhost`, `127.0.0.1`,
  `::1`, `dhum.local`, `test-2.local`. Everywhere else `show_404`.
- **PHP is 8.5.** This is PHP 7-era CI3 code. `substr($s, 0, strpos($s, ' ', 1000))`
  now **throws `ValueError`** instead of warning. Use `excerpt()` /
  `excerpt_words()` from `text_helper.php`.
- **`log_threshold = 0`** — CI error logging is off. A fatal is a blank page with
  nothing in `application/logs/`. Debug with `curl` and the response body.

## Database

```bash
set -a; . ./.env; set +a
PGPASSWORD="$DB_PASSWORD" psql -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USER" -d "$DB_NAME"
```

`PGPASSWORD` is required — a bare `psql` prompts and then fails. Back up first:

```bash
PGPASSWORD="$DB_PASSWORD" pg_dump -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USER" \
  -Fc -d "$DB_NAME" -f /home/darkness/vireta-private/db_dumps/<name>.dump
```

Schema history is in `migrations/*.sql` (two files, both 2026-09-30, the sadmin
panel). Live: **6 tenants (1, 3, 4, 5, 10, 11)** and **7 users**.

### Two switches, not one: `APP_ENV` and `CI_ENV`

| | Source | Controls |
|---|---|---|
| `ENVIRONMENT` (CI constant) | `$_SERVER['CI_ENV']`, else `APP_ENV` parsed from `.env` by `index.php` | error reporting, and the `.local`/`.localhost`/`.test` branch of `allows_dev_domains()` |
| `$config['is_development']` | `env('APP_ENV')` via the `.env` loader in `config.php` — **`.env` only** | `dev_hosts`, `allow_dev_override`, whether `/t_diag` exists, the signup throttle |

`.env` **cannot** be overridden by a real environment variable: the loader at the
top of `config.php` calls `putenv()` on every `.env` line, clobbering the process
environment, and `is_development` reads that loader. `APP_ENV=production php -S …`
silently stays `development`. **To test production behaviour, edit `.env` and
restore it** — `CI_ENV` moves only the `ENVIRONMENT` constant and leaves a hybrid
state where `dev_hosts` and `?tenant=N` are still live.

Current `.env`: `APP_ENV=development`, `APP_TENANT_APEX=localhost`.

## Tenant resolution — the part most likely to be got wrong

`Tenant_resolver` is the single source of truth for "which parishad is this?".
First match wins, and **the order is load-bearing** (its own docblock at the top
of `Tenant_resolver.php` is authoritative):

```
1. dev override   ?tenant=N / X-Tenant: N     (dev hosts only)
2. platform host  platform_domains           -> id() 0, NO tenant
3. exact hostname tenant_domains
4. subdomain      <slug>.<root domain>
5. session        authenticated user's tenant  (only if the host matched nothing)
6. primary row    tenant_domains WHERE is_primary
7. unresolved     id() 0 -> queries match nothing
```

| Table | A row means | If it matches |
|---|---|---|
| `tenant_domains` | this host belongs to **this parishad** | serve the portal |
| `platform_domains` | this host is the **ViretaDev product site** | serve the landing page, resolve to `id() === 0` |

`tenant_domains` columns are `ins_titute_p_id`, `ins_titute_un_id`, `hostname`,
`is_primary`, `is_verified`, `is_dev_only` — **not** `tenant_id`.

**Platform beats tenant, and `localhost` proves it.** `localhost` is a row in
*both* tables. It serves the product site (step 2 beats step 3). Never assume a
host's table membership determines the result; read the trace in `/t_diag`.

`Front::render_site()` (private) is the only place that picks portal vs landing.
It keys on the resolver's **reported source** — `domain`, `subdomain`, `forced`,
`dev-query`, `dev-header` get the portal; `platform`, `session`, `primary`,
`unresolved` get the landing page.

### Step 4 is live — `root_domains` is `['localhost']`, not empty

`config.php` derives `root_domains` from `APP_TENANT_APEX`, and `.env` sets that
to `localhost`. So `root_domains === ['localhost']` and `match_subdomain()`
**does** fire: it strips `.localhost` and looks up the **bare slug** row.
`Tenant_creator` writes that bare-slug row on purpose. The resolver's own
fallback default is `array()`, so subdomain matching dies only if `APP_TENANT_APEX`
is empty.

Do not assume a host resolves by suffix. `is_dev_host()` accepts any host ending
`.local` / `.localhost` / `.test` plus bare IPs, and that branch has no environment
check of its own — it survives only because the empty-`dev_hosts` guard at the top
returns FALSE first, and `dev_hosts` is non-empty only when `is_development`.

### Step 6 does not resolve to 0, and `is_primary` is not unique

`primary_tenant()` is `WHERE is_primary = TRUE AND (is_dev_only = FALSE OR ?)`
with `ORDER BY d.id LIMIT 1`. The bind is `'y'` when `allows_dev_domains()` is true
for the request host, `'n'` otherwise (Postgres booleans, so it really filters).
There are currently **four** `is_primary` rows: `localhost`→1,
`baghmaraup.gov.bd`→5, `rupshaup.gov.bd`→10, `salahuddin.localhost`→11.

Net effect: in development an unmatched host falls back to **tenant 1 (ধুম)**;
in production `localhost` is filtered out by `is_dev_only`, so it falls back to
**tenant 5 (বাঘমারা)**. Inserting or flipping an `is_primary` row silently changes
which parishad an unknown host serves — so **exactly one** `is_primary` row per
tenant, which is what `Tenant_creator` guarantees.

`ins_titute.domain_names` is **write-only**. No lookup reads it. Setting it does
not make a tenant reachable; `tenant_domains` is the only hostname table.

### An unknown host is isolated by the view gate, not the resolver

This is the trap. The resolver *does* hand a tenant to an unknown host (step 6).
Only `render_site()` decides such a host gets the product site, and it runs on `/`
(and `/up`) only. Verified live:

```bash
# Home page: correct — the product site, no tenant rows.
curl -s -H "Host: foo.local" http://localhost:8080/ | grep -c 'id="supportModal"'   # 1

# A DATA route reading tenant_code() directly: returns TENANT 1's row, byte-identical
# to dhum.local, the one host that is supposed to serve it.
curl -s -H "Host: foo.local"   http://localhost:8080/src_holding -X POST -d "holding_number=&ward=" | head -c 60
curl -s -H "Host: dhum.local" http://localhost:8080/src_holding -X POST -d "holding_number=&ward=" | head -c 60
```

So a green home-page check proves `render_site()` and nothing else. Any new
controller or model query reading `tenant_code()` / `id()` directly serves the
fallback tenant on unregistered hosts. **Test new tenant-scoped endpoints with a
data route or `/t_diag`, never with `/`.**

### Proving isolation

Every host returns **HTTP 200** regardless, so status codes prove nothing. Use a
value that exists only in the database — a real member name, e.g. `জাফর আহম্মেদ`
(tenant 1):

```bash
curl -s -H "Host: <host>" http://localhost:8080/ | grep -c "জাফর আহম্মেদ"
```

- **`id="root"` does not exist anywhere** — it was the React mount point in the
  retired `front/saas_landing.php`. Checking for it reports "not the product site"
  for every host. The markers that work are **`id="supportModal"`** (in
  `saas/partials/support_modal.php`) and **`id="features"`** (in
  `saas/pages/home.php`) — 1 against a portal's 0.
- Do **not** use `grep -c 'ধুম'` as a leak detector; it appears in the product
  site's own demo copy.

### A 200 with an empty body is usually a redirect, not a crash

The admin auth gate is `redirect('logout', 'refresh')` (`Auth`, `Dashboard`,
`Sonod`). CI3's `'refresh'` emits **`Refresh:0;url=…`**, not `Location:`, and
does **not** change the status code. So an unauthenticated admin request is
**HTTP 200 with a 0-byte body**, and `curl` does not follow `Refresh:` — `-L` will
not help.

```bash
curl -s -D - -o /dev/null -H "Host: dhum.local" http://localhost:8080/dashboard \
  | grep -i '^Refresh:'   # => Refresh:0;url=http://dhum.local/logout
```

Absent that header, *then* suspect a crash. Three distinct shapes to tell apart:
0-byte body (auth redirect), ~8.7 KB body (CI3 404 / `show_404()` via
`errors/html/_ht_error.php`), and a 500 "Unable to load the requested file" page.
The gate is correct — the admin **can** be inspected, you just log in first:

```bash
J=/tmp/cj.txt
curl -s -D - -o /dev/null -c $J -H "Host: dhum.local" \
  -d "identity=admin@admin.com" -d "password=password" \
  http://localhost:8080/login_check | grep -i '^Refresh:'   # => Refresh:0;url=…/dashboard
curl -s -b $J -c $J -o page.html -H "Host: dhum.local" http://localhost:8080/dashboard
```

`login_check` is the `Auth` controller and takes `identity` + `password`; `/login`
is only the form. The session cookie comes back as `#HttpOnly_…`, so a `grep -v '^#'`
on the jar makes it look empty — a curl artefact, not a failed login.

## The two tenant gates — separate, non-interchangeable

Both live in the autoloaded `tenant_helper.php`. There is no base controller in
this codebase, which is exactly why these are functions: three controllers needed
`enforce_tenant_scope()` and a fourth inline copy is how the hole happened.

### `enforce_tenant_scope()` — "is this user reading their OWN parishad's data?"

Call it from the constructor of **any controller that serves tenant data**.
Currently in `Dashboard`, `Sonod`, `Download` (all three also call the next one).

- Returns quietly (never denies) for an anonymous caller, and for
  `tenant_code() === 0` (a platform host, which those controllers redirect instead).
- **A `sadmin` gets a 403 here**, told to use `/super_admin`. This is deliberate
  and current: the panel's `?tenant=N` switch lives in `Super_admin`, which does
  not call this guard.
- Otherwise it compares by **inequality**, not `> 0`: a non-`sadmin` user with a
  NULL `ins_pr_id_s` has no parishad, so *any* tenant is somebody else's.

Without it, a রূপসা chairman (`ins_pr_id_s = 10`) signed in on `baghmaraup.gov.bd`
was served বাঘমারা's entire dataset, because `tenant_code()` answers to the host.

**It does not replace tenant filters in queries.** It decides which parishad you
are; it cannot stop you naming a row id belonging to another one. That lives on
the query — see IDOR below.

### `enforce_tenant_status()` — suspension and subscription expiry

Call it right after `enforce_tenant_scope()`. It halts with **403** when
`ins_titute.is_active` is false, and with **402 on writes** / a notice on reads
when the tenant's `subscriptions` row is `expired`/`cancelled` or `end_date` has
passed (falling back to `ins_titute.expire_times` when there is no subscription
row). It self-heals `status` to `expired` when the date has passed. `sadmin` is
exempt. All six live tenants currently have active yearly subscriptions
(`subscriptions` has a UNIQUE constraint on `ins_titute_p_id`).

### `require_sadmin()` — "may this caller reach the platform panel at all?"

In `sadmin_helper.php`, called as the **last** statement of the constructor of all
nine platform controllers (`Super_admin`, `Sadmin_tenants`, `Sadmin_subscriptions`,
`Sadmin_support`, `Sadmin_users`, `Sadmin_settings`, `Sadmin_audit`, `Support`,
`Support_sse`). It **halts** — `show_error(403)` for anonymous *and* for signed-in
non-sadmin, deliberately the same, because distinguishing them is an
account-existence oracle. It returns nothing on success, so put it last.

## Auth: who is who (changed since the earlier notes)

- **`admin@admin.com` is NOT a `sadmin`.** It is user 1, groups `admin, uphead`,
  `ins_pr_id_s = 1` — ধুম's own administrator. The login recipe above works
  because it is a *tenant-1* account on `dhum.local` (tenant 1), not because it
  is a platform super admin.
- **The platform super admin is `sadmin@viretadev.com`** (id 31, groups
  `admin, sadmin`, `ins_pr_id_s` NULL). Use it to exercise `/sadmin*`.
- Parishedad admins log in by `u_type` slug (`dhum-union`, `kanchan`, `baghmara`,
  `rupsha`, `salahuddin`) — ion_auth looks users up by email **and** `u_type`,
  the fallback also by `username`. That identity widening is kept on purpose.
- **The login backdoor is gone, verified in the code.** The
  `['password','admin','841998']` list and the identity lockout exemption survive
  only inside the "THE FALLBACK IS GONE" comment block in
  `application/models/Ion_auth_model.php`. `verify_password()` is now
  `password_verify()` against bcrypt; `is_max_login_attempts_exceeded()` keeps only
  the `ENVIRONMENT === 'development'` half. Do not reintroduce either. The lesson
  is recorded in the comment: **removing a branch from a caller does not remove it
  from the callee, and a docblock asserting a fix is not evidence of one.**
- `password_viewss_s` is **not written anywhere** and is **NULL for all 7 users**.
  The column stays as legacy. If it ever needs re-clearing:
  `UPDATE users SET password_viewss_s = NULL WHERE password_viewss_s IS NOT NULL;`

## IDOR — guessing a primary key crosses tenants

Tenant columns, so scoping them is a `->where()`, not a join:

| Table | Tenant column |
|---|---|
| `family_data_sheet` | `ins_aut_iiid` |
| `family_member` | `in_s_pr_id_dd` |

`AddMemberModel` had no filter on any read or delete, so `delete_member()` ran a
bare `DELETE FROM family_member WHERE fm_id = ?` and `get_datasheet_by_id()` was
called with `$this->input->get('id')` — any parishad admin could read or delete any
other's rows. All four methods are scoped now. **Any new endpoint taking a row id
from the request must filter on the tenant column itself.**

`SonodCrudModel` is deliberately partly unscoped — its class docblock lists which
is which and why, including three certificate lookups reachable **anonymously**
through `Download` (no auth gate in its constructor). They return an empty form
today: latent risk, not a live leak, and a product question rather than a bug fix.

## The two tenant ids — do not confuse them

- `ins_titute.ins_titute_p_idds` (1, 3, 4, 5, 10, 11) — what `tenant_code()` returns
- `ins_titute.un_iddd_iiid` (645, 9002, 9010; **NULL for tenants 4, 5, 11**) — the
  internal PK that `users.union_unq_idd_iii` references

Putting one where the other belongs silently writes rows to the wrong parishad.
`tenant_code()` / `tenant_un_id()` / `tenant_row()` / `tenant_source()` /
`tenant_hostname()` (all in `tenant_helper.php`, autoloaded) return the right one.
**`un_iddd_iiid` is nullable and three live tenants have it NULL** — check before
assuming a non-null `un_id()`. Both hardcodes that used to get this wrong are
fixed: `Front::insert_data()` writes `'unid' => tenant_un_id() ?: null`, and
`api_login()` uses `(int)($user->ins_pr_id_s ?? 0)`.

### Media: never emit an image column raw

Six columns moved from base64 blobs to per-tenant paths in
`uploads/tenant-<ins_titute_p_idds>/` (no schema change, same columns):
`ins_titute.ins_logo_path`, `ins_en_logo_path`, `cm_pic_base64`, `cm_sign_s64`,
`socib_pic_base64s`, `home_slider.sliderbase64`. The `_base64` names are
historical — they now hold `tenant-1/logo.png`-style paths.

**Always render through `media_url($value, $mime)`** (`media_helper.php`). It
accepts a path, bare base64, or a full `data:` URI, and returns a 1×1 transparent
GIF for empty/NULL/missing-file — it never returns `''`, which browsers treat as a
re-request for the page. Putting a raw column into `<img src>` silently breaks
every tenant's branding.

## Landmines — verified fixed; do not "restore" or reintroduce

- **`ins_titute_ins_titute_p_idds_seq` can be behind `max(ins_titute_p_idds)`,**
  and the first signup after a `pg_restore` or a hand-edited row collides with an
  existing tenant. `Tenant_creator` checks the insert and rolls back
  (`trans_start`/`trans_commit`), because before the guard it took `insert_id()`
  from a **failed** insert — the last id the connection saw, i.e. somebody else's
  tenant — and attached the new parishad's hostnames and admin to it. This was hit
  for real on tenant 10. After any dump restore:
  `SELECT setval('ins_titute_ins_titute_p_idds_seq', (SELECT max(ins_titute_p_idds) FROM ins_titute));`
- **CI3's `where()` is not Laravel's.** The third argument is `$escape`, not an
  operator: `->where('time', '>=', $since)` produces `WHERE "time" = '>='` and a
  Postgres cast error. The operator belongs in the key — `->where('time >=', $since)`.
- **CI3 `redirect()` is 303, not 302.**
- **`api_login()` content-negotiates**, choosing JSON vs redirect by *testing
  whether the raw body parses as JSON, or the request carries `X-Requested-With`* —
  not by a parameter, and not by the header alone. A form POST carrying the header
  still gets JSON. The login form and the register success screen's one-click
  button are plain `<form method="post">`s that depend on the redirect branch; on
  it, failure bounces to `/login?identity=…` with the reason in flashdata —
  **never put the password in the query.**
- **`signup_throttle()` purges rows OUTSIDE the window, never inside it.** Deleting
  the live rows is silent and fatal to the feature: the count always returns 0, the
  limit is never reached, and the endpoint stays unlimited for the life of the
  process. The signature is "the table has exactly one row no matter how many
  attempts were made". It is off in development (`tenant_signup.enforce`).
- **`is_dev_only` must appear on every query that reads the column** — in
  `Tenant_resolver.php` that is `find_by_host()`, `find_platform_by_host()` and
  `primary_tenant()`, all with the same `AND (is_dev_only = FALSE OR ?)` +
  `allows_dev_domains()` shape. A filter on one query is not a property of the
  data: this is how `primary_tenant()` leaked a live tenant into every unknown host.
- **Signup is the de-facto tenant creation route, throttled per IP but still
  unauthenticated**, and it creates the tenant `is_verified = TRUE` from a
  visitor-supplied subdomain. The throttle is the only thing between an open signup
  and a hijacked slug. `GET /register` is a second URL for the same operation, not
  a new capability. Two checks live in `Front::register()`, **not** in the creator —
  `api_register_tenant` was never sent those fields, so moving them would change
  the JSON endpoint's behaviour.
- **`discount_amounts` is `varchar`, not integer.** Any query that sums it needs
  `NULLIF(discount_amounts,'')::int`, or an empty string becomes a cast error
  instead of a zero. See `docs/DISCOUNT.md`.
- **Tenant creation lives in `application/models/Tenant_creator.php`, not in the
  controller.** `Front::create_tenant()` and the panel's onboard button used to be
  two copies that disagreed (apex source, bare-slug row, `is_primary`, insert
  check). One copy now; both callers use it. It deliberately does **not** authorise
  the caller — each caller owns that.

## Production and nginx

`deploy/` holds `DEPLOY.md`, `.env.example`, `nginx/vireta-upstream.conf` (vhosts)
and `nginx/vireta-app.conf` (shared `location` rules). The `.env` template lives in
`deploy/`, **not** the repo root — a working `.env.example` at the root invites
someone to fill it in and commit it.

- **nginx does not read `.htaccess`.** Every deny rule in `/.htaccess` and
  `/uploads/.htaccess` is restated in `vireta-app.conf`, and they are load-bearing:
  `config.php` reads `.env` from `FCPATH`, so `.env` is inside the webroot and the
  dotfile deny is the only thing keeping the DB password off the internet. If you
  add a deny, add it to **both**, and put it **above** `location ~ \.php$` — nginx
  takes the first matching regex location in file order, so a deny after the PHP
  handler is dead text.
- **Production refuses to boot** (`index.php` returns 503 with a plain-text list)
  while `APP_ENCRYPTION_KEY` is missing, still the published default `allion5`, or
  under 32 chars, or while `APP_TENANT_APEX` is empty.
- **The encryption key is `env('APP_ENCRYPTION_KEY', 'allion5')`,** not a literal.
- **`cookie_secure` is derived from `APP_ENV`; `sess_httponly` is always TRUE.**
- **Wildcard subdomains need a wildcard certificate.** The tenant vhost is
  `server_name <apex> *.<apex>`. `--webroot` cannot issue a wildcard.
- **`nginx -t` only parses.** The routing assertions that matter (`.env` 404,
  `uploads/x.php` 403, `application/**` 404, `/dashboard` reaching the front
  controller) need a running server; `deploy/DEPLOY.md` §5 lists them.

## CSS — there is no build step you can run

**`package.json` does not exist and `node_modules` is absent. `npm run build`
fails with ENOENT.** There is no `tailwind.config.js`; `@theme inline` in
`resources/css/portal.css` replaced it. Do not create one, and do not promise a
rebuild you cannot perform.

| Source | Generated output |
|---|---|
| `resources/css/portal.css` | `inc/assets/css/portal.css` |
| `resources/css/tokens.css` | `inc/assets/css/tokens.css` |

`inc/assets/css/portal.css` and `tokens.css` are **committed build output**.
Editing them by hand is the only way to change them, and it is silently
overwritten whenever someone restores the toolchain — so also edit the source in
`resources/`. `.vscode/settings.json` already excludes them as generated.

The one build helper that still runs is the tokens copy:

```bash
node ./resources/build/copy-tokens.mjs
```

It content-checks and prints `tokens.css unchanged` when the committed copy
matches, and copies when they differ. **Editing `tokens.css` requires running
this** or the admin renders the old palette while the public pages render the new
one — `portal.css` `@import`s tokens and inlines them, but `p/h.php` links
`tokens.css` and `brand.css` directly, so the admin does not go through Tailwind.

Nothing else in `inc/assets/` is npm-managed. `inc/assets/js/ht-modal.js` and
`inc/assets/js/saas.js` are **hand-written, no build step**; editing them takes
effect on reload. The public site needs **no npm at runtime**. The toolchain is
only needed if you add a Tailwind class that is not already in
`inc/assets/css/portal.css`.

**Namespaces are deliberately disjoint.** `tokens.css` owns everything under
`--brand-*`; `portal.css` maps that onto Tailwind's `--color-*`. They must never
collide. `--color-primary: var(--color-primary)` in `:root` is invalid at
computed-value time and renders every `bg-primary` as nothing, silently. The
namespace split is the fix, not load order. Components are a **CSS layer**
(`.ht-btn` `.ht-card` `.ht-field` `.ht-label` `.ht-badge` in `@layer components`),
not CI partials.

## Frontend architecture

**Two independent stacks. Do not merge them.**

- **Parishad portal** — server-rendered PHP. `front/home.php` is a thin composer
  that loads **8** partials from `front/partials/` in order (header, navbar,
  hero_slider, quick_services, speeches, members_carousel, employees_grid,
  footer) and holds no markup of its own. **The portal's HTML is in
  `front/partials/`, not `home.php`.** Loads `inc/assets/css/portal.css`.
- **Product site** — server-rendered PHP, since the React SPA was retired.
  `Template::saas($page, $data)`, called *from* `Front`'s route methods. It has its
  own `<head>` and Google Fonts and does **not** use `p/h.php`, so `$ht_shell` is
  never set on this path and the admin CSS stays out. One view per route under
  `views/saas/pages/`, plus `head.php`/`foot.php` and
  `partials/{navbar,footer,support_button,support_modal}.php`.

There is **no client-side router**. Every `<button onClick={navigate}>` became a
real `<a href>`, so each product page is its own URL and its own `Front` method.
React state that had to survive a page load became a query param (`?plan=`,
`?cycle=`, `?tab=`, `?cat=`, `?q=`); state that had to survive a *submission*
became a real form control.

`Template::saas()` must load its views as a **flat sequence**. CI3's
`load->view()` appends to `final_output` and returns nothing usable, so
`<?= $this->load->view('partial') ?>` inside a view is a bug.

The Phosphor React components became the **Phosphor webfont**, `<i class="ph ph-name">`
via `icon($name, $size, $weight, $class)` (`icon_helper.php`, autoloaded). The
"Ported from resources/js/…" docblock at the top of each view is provenance for a
deleted path — history, not a live file.

### Layout is concatenated views, not a master template

`Template::front()` / `::back()` / `::sonod()` / `::sadmin()` each call
`load->view()` in sequence. There is no layout inheritance.

```
front()   p/h  →  content view                       →  p/f
back()    p/h  →  p/n (admin sidebar) → p/mdk_open → content → p/mdk_close → p/f
sonod()   p/h  →  p/n                 → p/mdk_open → content → p/mdk_close → p/f
sadmin()  its own platform shell
```

`p/h.php` and `p/f.php` are shared; `p/n.php` and the two `p/mdk_*.php` are not.
That is why the stylesheet block is gated on `$ht_shell`, which `back()` and
`sonod()` set and `front()` does not. **These methods are `static`** and are called
**both ways** — `$this->template->back(...)` and `Template::back(...)` — so grepping
for one spelling looks like dead code and is not.

**`p/` is Tailwind, not MDK.** All shell views were converted; the sidebar uses
`ph-bold ph-*` Phosphor icons. Do not assume the shell is still Material Design Kit
just because `app.css` is in play.

### The MDK bridge — TEMPORARY, and half of it is easy to miss

`views/back/` (29 files) and `views/sonod/` (4) are mostly converted now — only
**44** MDK-style class uses remain, in `up_members_view_files`, `employee_view_file`,
`profile_info`, `search_holding_info_view`, `print_slip`, `user_view` — but the
bridge is **still live**, and **half of it is not a stylesheet**:

| Gated on `!empty($ht_shell)` in `p/h.php` | Why |
|---|---|
| `app.css` (3.1 MB MDK) | `.card .btn .form-control .table .row .col-md-* .badge .alert .dropdown .input-group` |
| `vendor-material-icons.css` | MDK icon webfont (app.css names the family but ships no `@font-face`) |
| `flatpickr.min.css` + `js/flatpickr.js` | `data-toggle="flatpickr"` inputs |
| `toastr.min.css` | two views load the `.js` but never the `.css` |

**`app.css` on its own does nothing.** Every rule in it is written
`[dir=ltr] <selector>` — **4,685 occurrences** — and `vendor-material-icons.css` /
`vendor-select2.css` are gated the same way. `p/h.php` opens
`<html lang="bn" class="scroll-smooth">` with **no `dir` attribute**, so the
stylesheet matches nothing. The other half of the bridge is `p/mdk_open.php` /
`p/mdk_close.php`: a `<div dir="ltr">` around the content view only. Dropping the
stylesheets without the wrapper silently unstyles the admin, and dropping the
wrapper without the stylesheets does exactly the same. **They go together, and the
wrapper is the half nobody looks for.**

**Why a wrapper and not `dir="ltr"` on `<html>`:** MDK's utilities all carry
`!important`, and ~20 collide by name with classes the Tailwind shell uses
(`bg-white text-white flex flex-row border border-2 rounded-lg rounded-xl mt-1 mt-2
mt-auto mx-auto my-1 p-2 px-3 px-4 py-2 py-5 text-center text-left`), several with a
*different* value — MDK's `.rounded-xl` is `0.25rem` against Tailwind's
`--brand-radius-xl`, and its `.flex` is `flex: 1 1 0%`. On `<html>` all 20 would go
to MDK and visibly break the header and sidebar.

**MDK's JavaScript is deliberately not restored** — `material-design-kit.js`,
`bootstrap.min.js`, `popper.min.js`, `js/app.js` are loaded by no view. `p/f.php`
drives its own dropdowns through `toggleDropdown()` and `p/h.php` shims
`$.fn.modal` onto `window.htModal`; the real Bootstrap bundle would install a
second modal implementation over the top of both.

**Why `p/mdk_open.php` is a view and not an `echo()`:** `CI_Loader::view()`
appends to `CI_Output::$final_output` and returns nothing usable, and
`final_output` flushes at end of request — so `echo '<div dir="ltr">'` between two
`load->view()` calls reaches the browser *ahead of `<html>`. Same trap as
`saas/foot.php`.

**Setting `$ht_shell` outside `back()`/`sonod()`, or loading `app.css` from a
public page, breaks the public pages.** When the last MDK view is converted,
delete the gate block in `p/h.php`, both `p/mdk_*.php`, and their `load->view()`
calls in `Template::back()` / `::sonod()` **together**.

### `/login` is two pages, split on the resolver

`Front::login()` branches on `tenant_source()` exactly as `render_site()` branches
for `/`: `domain` / `subdomain` / `forced` / `dev-query` / `dev-header` get
`front/login` (the parishad's own); `platform` or unresolved get
`saas/pages/login` (the product site's). Both post to **`api_login`**, so the
credential check, the `sadmin` branch and `tenant_login_url()` are shared; only the
page around the form differs.

`front/login.php` deliberately does **not** load `partials/navbar.php` — that
navbar's own primary button is প্যানেল লগইন, which would link the page to itself.

### Modal handling without Bootstrap

Gone: `p/h.php`'s `front()` path, `api/login.php`, `api/collect_sam.php`, and the
whole React product site. The only real Bootstrap-shaped dependency left is
**modals**, driven by **`inc/assets/js/ht-modal.js`** — hand-written, no build
step, no dependencies. It reads the *existing* Bootstrap attributes on purpose
(`data-toggle="modal"`, `data-target`, `data-dismiss`, `data-bs-*`,
`data-toggle="collapse"`) so the markup was not rewritten. The modal box CSS is a
block in the `@layer components` section of `resources/css/portal.css`.
`window.htModal.open/close/closeAll` is exposed for inline scripts. The product
site uses the same contract: a dozen-odd `data-toggle="modal"` triggers across the
navbar, footer, support button and pages, all pointing at the one
`id="supportModal"` that every product page loads.

- **Tailwind v4's automatic source detection is on**, so a new class in a directory
  no `@source` mentions is still generated, as long as it is under the project root
  (there is no `.gitignore`, so detection covers everything). Do not add `@source`
  lines to "fix" a missing class — and remember a variant is part of the name
  (`lg:grid-cols-4`, not `grid-cols-4`).
- **5 legacy standalone public pages** — `front/add.php`, `single_member_views.php`,
  `single_employee_views.php`, `search_holding_number_view_pages.php`,
  `single_holding_person_view_file.php` — were converted to Tailwind and load
  `portal.css` + `legacy-profile.css` with no Bootstrap, owl or Font Awesome.

### Known-dead and broken admin routes — do not "fix" silently

- **Nine `template->back()` / `Template::back()` calls name a view that does not
  exist**, so those routes answer 404: `report_view` → `back/reports_view_show`,
  `viewdata` → `showData`, `test` → `test`, `back/invoice_search_view_file`,
  `back/taxEntryView`, `back/acc_setting_s`, `family_data_form`,
  `success_invoice`, and `tax_collection` (which exists, but as
  `back/tax_collection.php` — a missing `back/` prefix). `my_profile` was fixed.
  None of these is linked from the sidebar.
- `sadmin_users`' **write** half is deliberately not built and needs approval first
  (it touches `users`/`users_groups`); the read page and `api_reset_password` are
  built. `routes.php` carries a `/* NOT BUILT */` block saying the controller does
  not exist — **that comment is stale**, the controller exists.
- `back/dashboard.php` (the 988-line orphan) has been **deleted**, archived to
  `/home/darkness/vireta-private/backups/20260929_orphan_views/`. `Dashboard::index()`
  renders `back/fdash.php`.

## Dead code — leave alone unless asked

- `application/views/ex/` — dead Ion Auth demo
- `application/libraries/Pdf.php` — requires `application/libraries/tcpdf/`, which
  does not exist; only referenced from a commented line. mPDF replaced it.
- `inc/assets/css/vendor-fontawesome-free.css` — 1.06 MB. **The `<link>` has been
  dropped from `front/partials/header.php`** and 0 `fa-` classes remain on public
  pages, so nothing loads it now. Do not delete the file itself without checking
  `legacy-profile.css` and the owl-carousel partials.

## Before changing schema or auth

Get explicit approval first for: DB passwords, `encryption_key` (changing it
invalidates sessions and encrypted data), the plaintext password paths and the
admin backdoor above, and anything touching `users`/`users_groups`. All migrations
so far were additive and non-destructive.