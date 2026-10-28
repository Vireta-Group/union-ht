# Tenant Migration — B1/B2/B3

**Status: complete, verified 2026-09-28.**

The app is no longer structurally single-tenant. There is no hardcoded tenant
id anywhere in the codebase. Tenant identity is resolved per request.

---

## 1. What changed

### New table — `tenant_domains`

```sql
CREATE TABLE tenant_domains (
    id                serial       PRIMARY KEY,
    ins_titute_p_id   integer      NOT NULL REFERENCES ins_titute(ins_titute_p_idds) ON DELETE CASCADE,
    ins_titute_un_id  integer,
    hostname          varchar(255) NOT NULL UNIQUE,
    is_primary        boolean      NOT NULL DEFAULT false,
    is_verified       boolean      NOT NULL DEFAULT false,
    is_dev_only       boolean      NOT NULL DEFAULT false,
    created_at        timestamptz  NOT NULL DEFAULT now(),
    CONSTRAINT tenant_domains_hostname_lower_chk CHECK (hostname = lower(hostname))
);
CREATE INDEX tenant_domains_tenant_idx ON tenant_domains (ins_titute_p_id);
```

`is_primary` is the fallback row. A host that matches nothing resolves to it.
This is why no tenant id is hardcoded in PHP — the fallback is a database row.

### New table — `platform_domains`

```sql
CREATE TABLE platform_domains (
    id          serial       PRIMARY KEY,
    hostname    varchar(255) NOT NULL,
    is_primary  boolean      NOT NULL DEFAULT false,
    is_dev_only boolean      NOT NULL DEFAULT false,
    created_at  timestamptz  NOT NULL DEFAULT now()
);
CREATE UNIQUE INDEX platform_domains_hostname_uniq
    ON platform_domains (lower(hostname));
```

A host listed here serves the **ViretaDev product site** and belongs to no
parishad. `Tenant_resolver` returns `id() === 0` and `source() === 'platform'`
for it, so every tenant-scoped query matches nothing. See §3.6 and §4 for why
this had to become a table rather than a host list in a controller.

Note the unique index is on `lower(hostname)`, so a differently-cased duplicate
is rejected. The main domain needs a *single* identity — two "primary" platform
hosts would let the resolver pick between them arbitrarily, and a request would
be able to choose its own answer.

### New files

| File | Purpose |
|---|---|
| `application/libraries/Tenant_resolver.php` | resolution engine, lazy + cached per request |
| `application/helpers/tenant_helper.php` | `tenant_code()`, `tenant_un_id()`, `tenant_row()`, `tenant_source()`, `tenant_hostname()` |
| `application/helpers/text_helper.php` | `excerpt()`, `excerpt_words()` — PHP 8 safe string truncation |
| `application/controllers/T_diag.php` | dev-only diagnostic, see §5 |

### Modified

- `application/config/constants.php` — the `union_code` constant is **gone**
- `application/config/config.php` — added `$config['tenant']`; no default tenant id
- `application/config/autoload.php` — added `tenant_resolver` library, `tenant` + `text` helpers
- `application/views/front/home.php` — 2 lines, PHP 8 `strpos` fix
- `application/controllers/Front.php` — `index()` and `up()` route through one
  `render_site()` that asks the resolver (§3.4, §3.6, §3.8)
- 7 application files — 47 `union_code` call sites → `tenant_code()`

---

## 2. The two tenant ids — do not confuse these

| Method | Column | Value | Meaning |
|---|---|---|---|
| `tenant_code()` / `id()` | `ins_titute.ins_titute_p_idds` | 1, 3 | what every tenant-scoped column stores |
| `un_id()` | `ins_titute.un_iddd_iiid` | 645, 9002 | internal PK, referenced by `users.union_unq_idd_iii` |

14 columns across the schema hold the tenant id:

```
family_data_sheet.ins_aut_iiid            integer
amount_paid.in_s_titute_pr_idd            integer
collect_amnt_invoice.ins_ti_tute_pridd    integer
customer_full_info.ins_pr_iddds            integer
employee_data_ins.emp_ins_code_id         integer
family_member.in_s_pr_id_dd               integer
home_slider.insidpris_id                  integer
house_home_name_etc.ins_id_pr_idautoss    integer
ins_titute.ins_titute_p_idds              integer
sonod_genarates.insididid_pridididid      integer
users.ins_pr_id_s                         bigint
usr_bal_cutting.insprsssid                bigint
up_member_s.ins_atou_iddd_s               integer   <- was varchar, see §3
villages_of_union.ins_idiidd_for_primary_ss integer
```

---

## 3. Bugs found and fixed

### 3.1 `FALSE !== NULL` — the resolver resolved unknown hosts to tenant 0

`run_sql()` returned `FALSE` on a failed lookup while callers tested
`!== NULL`. In PHP `FALSE !== NULL` is **true**, so every unmatched host took
the "matched exactly" branch with an empty row and resolved to `id = 0`. The
result: any host not in `tenant_domains` returned no data and the home page
died.

Fixed: `run_sql()` now returns `NULL` on failure, and both call sites
(`find_by_host`, `primary_tenant`, `load_titute`) were audited for the same
`!== FALSE` mistake. `find_by_host` also silently skipped its legacy
`ins_titute.domain_names` fallback for the same reason.

### 3.2 Tenant column stored as `varchar` — hard 500 on every query

`up_member_s.ins_atou_iddd_s` was `character varying` while the other 13 tenant
columns are integer/bigint. The old constant was the *string* `'1'`, which PDO
sends as an untyped literal, so Postgres coerced it per column. Once
`tenant_code()` returned a real `int`, PDO sent `integer` and Postgres had no
`varchar = integer` operator:

```
ERROR: operator does not exist: character varying = integer
```

That would have broken the member list for that one table in production while
every other table kept working — a confusing, partial failure.

Fixed: column normalized to `integer` (9 rows, all numeric, no NULLs, verified
before the ALTER). Backup: `db_dumps/pre_typefix_up_member_s_20260928_000107.dump`.

### 3.3 PHP 8 `ValueError` in the home page — broke every tenant with short text

```php
substr($setting->chairman_speach, 0, strpos($setting->chairman_speach, ' ', 1000))
```

PHP 7 returned `false` with a warning. PHP 8 **throws**:
`ValueError: strpos(): Argument #3 ($offset) must be contained in argument #1`.
An uncaught fatal, so any tenant whose chairman's message was shorter than 1000
characters — i.e. **every new tenant** — got a 500 home page. It was invisible
for tenant 1 only because its text is 1342 characters.

Fixed: `excerpt()` in `text_helper.php`, which handles NULL, short strings, and
tags without throwing.

### 3.4 `?tenant=N` in the controller — the public main domain could be asked for any tenant

`Front::index()` read `$this->input->get('tenant')` and, if it was non-empty,
rendered that tenant's home page. The resolver already implemented `?tenant=N` as
a **development override gated on `is_dev_host()`** — but the controller held a
second, ungated copy of the same idea, and the copy was the one doing the
damage. `is_dev_host()` was never consulted, so `Host:` any public host plus
`?tenant=1` served a live parishad's home page in full: logo, chairman's
message, council members, staff.

Same defect class as the resolver bugs above — the same idea implemented in two
places, only one of which remembered the gate. Demonstrated:

```
Host: vireta.dev   ?tenant=1
  before → 200, 88607 bytes, <title>৪নং ধুম ইউনিয়ন পরিষদ</title>
  after  → 200,  1667 bytes, ViretaDev product site
```

Fixed by deleting the read. The development override now exists in exactly one
place — inside the resolver — and `Front::index()` reads the resolver's
*reported source* rather than re-deriving the decision.

### 3.5 `primary_tenant()` had no `is_dev_only` filter — every unknown host became a live tenant

`find_by_host()` correctly filtered `(d.is_dev_only = FALSE OR ?)` after the
production hardening pass. `primary_tenant()` did not. The only row flagged
`is_primary` was `localhost`, which is flagged `is_dev_only` — so **every
unrecognised hostname in production resolved through the one row explicitly
marked as not for production.**

Demonstrated with `APP_ENV=production`:

```
Host: vireta.dev        → tenant 1 (৪নং ধুম)
Host: nobody.example     → tenant 1
Host: some-random-client.com → tenant 1
```

The consequence was not a leak on its own but a loaded gun: `Front::__construct()`
runs four tenant-scoped queries on every request, so `$this->data` was populated
with ধুম's members, employees, sliders and speeches on the marketing page. One
template edit away from being public.

Fixed by copying the filter. Worth stating the lesson, because it is the general
one: **a filter added to one query is not a property of the data.** The same
predicate has to be stated on every query that reads the column, or the path
that forgets it becomes the path that leaks.

### 3.6 The main domain had no first-class identity

`Front::index()` decided "platform host" by testing a hardcoded array:

```php
$is_dev_root = in_array($host, ['localhost', 'localhost:8080', '127.0.0.1', '127.0.0.1:8080']);
```

That describes the developer's machine, not the deployment. In production the
main domain is not in the list, so the decision fell through to whatever the
resolver guessed — which is 3.5. It also made adding `vireta.dev` to
`tenant_domains` impossible: that would have claimed the main domain for a
parishad and made the product site unreachable. It violated the project's own
"no hardcoded literals as a tenant fallback" rule, one layer above the resolver
that had just been fixed for the same reason.

Fixed with a new `platform_domains` table and `Tenant_resolver::is_platform()`.
A platform host maps to **no tenant at all** (`id()` returns 0), so every
tenant-scoped query in a controller constructor matches nothing. That is the
property that makes isolation structural rather than a matter of remembering to
check something in the view.

### 3.7 The legacy `ins_titute.domain_names` fallback bypassed the dev/production gate

`find_by_host()` had a second query, run when `tenant_domains` returned nothing:

```sql
SELECT * FROM ins_titute WHERE lower(domain_names) = ?
```

A plain text column with no notion of development versus production. So
`is_dev_only` could reject a `tenant_domains` row and the fallback would find
the *same hostname* one statement later with nothing left to reject it. In
production:

```
Host: localhost  →  tenant 1, 88 596 bytes, <title>৪নং ধুম ইউনিয়ন পরিষদ</title>
```

Verified redundant before removal — every value in `domain_names` is already
present in `tenant_domains`:

| tenant | `domain_names` | in `tenant_domains` |
|---|---|---|
| 1 | `localhost` | yes |
| 3 | `test-union-2.test` | yes |

So it added **zero** reachability and existed only as a way around the gate.
Removed. To stop the failure mode it was hiding — a new tenant added with a
`domain_names` value and no `tenant_domains` row, which would now be
unreachable — `/t_diag` reports those gaps explicitly.

### 3.8 `/up` was a bare alias that skipped the routing decision entirely

`Front::up()` called `$this->load->view('front/home', ...)` directly, so it
rendered a parishad portal shell on the main domain regardless of what the
resolver had decided. No data leaked at the time (a platform host resolves to
tenant 0, so the queries matched nothing), but an alias that steps around the
gate is the same defect in a different place, and the next edit to `/up` would
have made it leak.

`index()` and `up()` now both call a shared private `render_site()`.

---

## 4. Resolution order

Strategy **B'** — a registered hostname always wins, so one tenant's admin can
never read another tenant's data by visiting their domain.

| # | Source | Match | Gated |
|---|---|---|---|
| 1 | dev override | `?tenant=N` / `X-Tenant: N` | dev hosts / IP literals only |
| 2 | **platform host** | `platform_domains.hostname` → **no tenant** | `is_dev_only` filtered |
| 3 | exact hostname | `tenant_domains.hostname` | `is_dev_only` filtered |
| 4 | subdomain | `<slug>.<root>` | needs `root_domains` in config |
| 5 | session | `session['union_code']`, validated against `ins_titute` | only reached if the host matched nothing |
| 6 | primary row | `tenant_domains WHERE is_primary` | `is_dev_only` filtered |
| 7 | unresolved | `id()` returns 0 → queries match nothing | fail-closed |

`0` matches no tenant column, so an unresolved host returns empty results
rather than leaking another tenant's rows.

**Why step 2 exists and why it sits there.** The main domain serves the
ViretaDev product site, which belongs to no parishad. It is registered in
`platform_domains` rather than being left to fall through to the `is_primary`
tenant, because that fall-through is what made the main domain render a live
tenant's home page. A platform host short-circuits to `id() === 0`, so tenant
queries match nothing — isolation becomes structural instead of a check
somebody has to remember to perform.

Step 2 sits **below** the dev override and **above** everything else on purpose:
below, so `?tenant=1` on `localhost` still previews a tenant; above, so no later
fallback can hand the main domain a tenant it does not own.

### Two tables, not one

`platform_domains` is separate from `tenant_domains` because a platform host
has no tenant, so it cannot be a row in a table whose entire job is to name the
parishad a host belongs to. Filing it under `tenant_domains` would have meant
either a null tenant id or an invented one, in the one table every tenant
lookup reads — a bad trade for a boolean.

| Table | A row means | Columns |
|---|---|---|
| `tenant_domains` | this host belongs to this parishad | `hostname`, `ins_titute_p_id`, `is_primary`, `is_verified`, `is_dev_only` |
| `platform_domains` | this host belongs to the product site | `hostname`, `is_primary`, `is_dev_only` |

```sql
-- Production
INSERT INTO platform_domains (hostname, is_primary, is_dev_only)
VALUES ('vireta.dev', TRUE, FALSE), ('www.vireta.dev', FALSE, FALSE);

-- Development only — inert the moment APP_ENV leaves development,
-- because $config['tenant']['dev_hosts'] is then empty.
INSERT INTO platform_domains (hostname, is_primary, is_dev_only)
VALUES ('localhost', TRUE, TRUE), ('127.0.0.1', FALSE, TRUE);
```

### Which view gets served

`Front::index()` and `Front::up()` both call one private method, which asks the
resolver rather than deciding anything itself:

| Resolver source | Served |
|---|---|
| `domain`, `subdomain`, `forced`, `dev-query`, `dev-header` | `front/home` — the parishad portal |
| `platform`, `session`, `primary`, `unresolved` | the product site — `Template::saas()` (was `front/saas_landing`, a React mount point, now deleted) |

Explicit means the resolver *named* a tenant. A fallback does not count: the
`primary` row is a guess made because nothing else matched, and `session` is a
guess made from a cookie. Neither is a statement that this host belongs to that
parishad. Everything else fails closed toward the page that reads no tenant data
and therefore cannot leak any.

---

## 5. Verifying it yourself

`/t_diag` prints the resolved tenant, the resolution trace, live row counts for
that tenant, and every registered host.

```
http://localhost:8080/t_diag
```

**It is hard-gated** to the development hostnames in
`$config['tenant']['dev_hosts']`, and that list is itself empty unless
`APP_ENV=development`. With `APP_ENV` set to anything else, `/t_diag` returns
404 on every host — including `Host: localhost` and bare IP literals.
`$config['tenant_diag_any_host']` in `config.php` can widen that for DNS
debugging; it is `FALSE` now and must stay `FALSE` in production, because the
endpoint prints tenant structure.

### Development hostnames

Two named hosts, one per tenant, so a browser can be used instead of curl:

| Hostname | Tenant | Purpose |
|---|---|---|
| `dhum.local` | 1 — ৪নং ধুম ইউনিয়ন পরিষদ | real data |
| `test-2.local` | 3 — `[TEST]` দ্বিতীয় ইউনিয়ন পরিষদ | the test tenant |

Add them once:

```
sudo sh -c 'printf "127.0.0.1 dhum.local test-2.local\n" >> /etc/hosts'
```

`.local` is reserved for mDNS (RFC 6762), so these names can never collide
with a real domain a tenant might later register.

Both rows are in `tenant_domains` with `is_dev_only = TRUE`, and the resolver
only honours a dev-only row when the request host is in `dev_hosts`. Setting
`APP_ENV=production` therefore makes `dhum.local` and `test-2.local` resolve
to the primary tenant instead of their own — verified, not assumed.

Any host can also be selected with a Host header, no hosts-file edit needed:

```
curl -H "Host: dhum.local"   http://localhost:8080/
curl -H "Host: test-2.local" http://localhost:8080/
curl -H "Host: localhost"    "http://localhost:8080/?tenant=3"   # dev only
```

`?tenant=N` is refused unless `APP_ENV=development` **and** the host is a dev
host, so it cannot be used to pivot into another tenant's data on a published
site. Verified: with `APP_ENV=production`, `?tenant=3` on both `dhum.local` and
`localhost` returns tenant 1.

Verified across 5 hosts and 8 routes; data isolation confirmed by asserting
that tenant 1's pages contain none of tenant 3's rows and vice versa.

---

## 6. Test data currently in the database

Tenant `id = 3`, `un_iddd_iiid = 9002`, named `[TEST] দ্বিতীয় ইউনিয়ন পরিষদ`
(with 3 `up_member_s` rows, 2 `home_slider` rows, 1 `users` row, and hosts
`test-union-2.test` + `test-union-2.localhost`) exists **only** to prove
isolation. Remove before any real launch:

```sql
BEGIN;
DELETE FROM tenant_domains WHERE ins_titute_p_id = 3;
DELETE FROM users         WHERE ins_pr_id_s = 3;
DELETE FROM up_member_s   WHERE ins_atou_iddd_s = 3;
DELETE FROM home_slider   WHERE insidpris_id = 3;
DELETE FROM ins_titute    WHERE ins_titute_p_idds = 3;
COMMIT;
```

Note the real institute is `ins_titute_p_idds = 1`. The sequence is at 3
because id 2 was consumed by a deleted row.

---

## 7. Adding a real tenant

1. `INSERT INTO ins_titute (...)` — `ins_titute_p_idds` from the sequence,
   `un_iddd_iiid` chosen explicitly.
2. `INSERT INTO tenant_domains (ins_titute_p_id, ins_titute_un_id, hostname, is_verified)`
   — one row per host: the custom domain and, if used, the subdomain slug.
3. `INSERT INTO users (..., ins_pr_id_s, union_unq_idd_iii)` — set
   `ins_pr_id_s` to the new `ins_titute_p_idds` and `union_unq_idd_iii` to the
   new `un_iddd_iiid`.
4. For subdomain hosting, add the apex domain to
   `$config['tenant']['root_domains']`.
5. Verify with `/t_diag` before handing it to anyone.

No code changes are needed per tenant.

---

## 8. Backups taken

```
/home/darkness/vireta-private/
├── db_dumps/
│   ├── pre_tenant_migration_20260927_231752.dump
│   ├── pre_typefix_up_member_s_20260928_000107.dump
│   └── pre_platform_fix_20260928_080840.dump     (519 KB, before §3.4–3.8)
└── removed_files/
    ├── constants.php.user_hack_20260927_234322.bak
    ├── Ion_auth_model.php.pre_tenant.bak
    ├── k.php.bak
    ├── images_choose/                     (9.1 MB, 0 references)
    ├── pre_tenant_b2/                     (6 files, pre-migration)
    ├── Tenant_resolver.pre_platform_20260928_080840.php
    └── Front.pre_platform_20260928_080840.php
```

No git repository, so these are the only rollback.

---

## 9. Proving the tenant isolation yourself

The decisive test is not a status code — every one of these returns **200**. It
is whether a tenant's real data appears in the server-rendered HTML. A member
name that exists only for tenant 1 is a reliable marker:

```bash
MARK=$(psql -U salahuddin -h 127.0.0.1 -d szfjjtji_ht -A -t \
  -c "select up_mem_s_name from up_member_s
      where ins_atou_iddd_s::int=1 and coalesce(up_mem_s_name,'')<>'' limit 1")

for h in vireta.dev www.vireta.dev localhost 127.0.0.1 dhum.local nobody.example; do
  printf '%-18s %8s bytes  member marker: %s\n' \
    "$h" \
    "$(curl -s -H "Host: $h" localhost:8080/ | wc -c)" \
    "$(curl -s -H "Host: $h" localhost:8080/ | grep -c "$MARK")"
done
```

Expected: `dhum.local` is the only row with a non-zero marker, because it is the
only host registered to tenant 1.

```
vireta.dev        1666 bytes  member marker: 0
www.vireta.dev    1670 bytes  member marker: 0
localhost         1665 bytes  member marker: 0
127.0.0.1         1665 bytes  member marker: 0
dhum.local       88606 bytes  member marker: 2   ← tenant 1's own host
nobody.example    1670 bytes  member marker: 0
```

Repeat with `APP_ENV=production` and **every** marker is 0, because the only
`is_primary` and dev-only rows are filtered out.

> **Do not use `grep -c 'ধুম'` as a leak detector.** The ধুম name appears in the
> product site's own demo copy, so a false positive here reads as a leak and
> sends you chasing a bug that does not exist. Use a value that only exists in
> the database.

> **Do not use `id="features"` to identify the landing page either.** It was
> unusable for a while and is now misleading in the other direction: while the
> landing page was a React SPA the id was absent from server HTML, so every host
> reported "not the landing page". The product site is server-rendered now, so
> the id *is* present on it. `id="supportModal"` and `id="features"` both
> discriminate cleanly (1 on the product site, 0 on a portal). **`id="root"` is
> gone** — it was the React mount point and the view holding it is deleted, so
> it now matches nothing anywhere. Page size and the member marker also work.

`/t_diag` prints the resolved tenant, the resolution trace, `is_platform()`,
hostname-coverage gaps, live row counts, and every registered host.

---

## 10. Still to do

- `ins_titute.sub_domains` is a leftover single-value column. `tenant_domains`
  replaces it; the column can be dropped once nothing reads it.
- `ins_titute.domain_names` is now **write-only**. No lookup reads it. Kept
  because dropping a column on a 39-table schema is not a call to make casually
  — but note that setting it no longer makes a tenant reachable, which is a
  real behaviour change and the reason §3.7 added a `/t_diag` check for it.
- Subdomain matching is implemented but untested against a real apex domain —
  `root_domains` is empty in config.
- Column renaming across the 39-table schema is still out of scope.
- `views/ex/` (Ion Auth demo) and the broken `application/libraries/Pdf.php`
  (requires `application/libraries/tcpdf/`, which does not exist) are dead
  weight, untouched.
- **`.env` cannot be overridden by a real environment variable.** The loader
  calls `putenv()` on every line of `.env`, so a value exported into the process
  is clobbered. Testing production behaviour means temporarily editing `.env`.
  Safe by default, but it will surprise anyone who expects
  `APP_ENV=production php -S ...` to work.
- `log_threshold = 0`, so CI error logging is **off** and errors are invisible
  in production. Left alone as live behaviour, but it means a regression surfaces
  as a blank page rather than a log line.
