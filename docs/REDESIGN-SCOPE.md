# Multi-Tenant Redesign — Full Scope Document

**Project:** HT X ViretaDev (Union Parishad Management System)
**Date:** 2026-09-27 (revised 2026-09-28)
**Decisions confirmed:** Subdomain + custom domain · Tailwind CSS rebuild · Per-tenant light branding (agency product, not government) · **All pages take the modern theme, "as is"** (2026-09-28 — supersedes the earlier "Phase 1 = home page only" scope)

> **Scope change 2026-09-28.** The original scope was design system + home page
> only, with the admin explicitly excluded (D1, D2 below). You then directed
> that every page take the modern theme. That is honoured, but in three
> different treatments, because the pages are not alike:
>
> | Tier | Views | Treatment |
> |---|---|---|
> | **Public** | `front/`, `sonod/`, `api/` | Tailwind, built from scratch |
> | **Admin** | `back/` + `p/h` `p/n` `p/f` | **Token overlay** on Material Design Kit — no markup rewrite, no plugin breakage |
> | **Paper** | `download/`, printable `p/` | **Print-first CSS** — deliberately *no* screen theme |
>
> Reasoning and the full colour mapping are in `docs/DESIGN-SYSTEM.md`. D1 and
> D2 below are amended accordingly: the admin is restyled, not rewritten;
> Material Design Kit and Bootstrap are kept, not removed.

---

## PROGRESS

| Item | Status |
|---|---|
| **B5 — security: dumps hidden, password rotated, phpinfo removed** | ✅ **DONE 2026-09-27** |
| **B1–B3 — tenant resolver, `tenant_domains`, de-constant** | ✅ **DONE 2026-09-28** |
| **B6a — tenant branding + sliders to per-tenant files** | ✅ **DONE 2026-09-28** |
| **B7 — platform-domain routing + 5 isolation bugs** | ✅ **DONE 2026-09-28** |
| **B8 — super admin panel access gate** | ✅ **DONE 2026-09-28** — role-only (`sadmin` group), platform super admin has **no tenant id** (users row NULLed), login lands on the panel |
| **C1 — Tailwind v4 pipeline** | ✅ **DONE 2026-09-28** |
| **C2 — design token layer** | ✅ **DONE 2026-09-28** — 17/17 contrast pairs pass |
| **C3 — component layer** | ✅ **DONE 2026-09-28** — CSS-based, not CI partials |
| C3a — admin token overlay (`brand.css` on MDK) | ✅ built + loads; ⬜ not yet visually verified (needs a login) |
| C4–C5 — SaaS landing page | ✅ **DONE 2026-09-28** — 11 server-rendered views in `application/views/saas/pages/`, one per route. Built as a React SPA first, then **ported to PHP and the React chain deleted** (no `inc/assets/dist/`, no Vite, `package.json` is Tailwind-only). |
| C4–C7 — parishad portal home page | ✅ **BUILT BY YOU 2026-09-28** — 38 lines + 8 partials, server-rendered |
| Paper tier — `inc/assets/css/print.css` | ⬜ |
| B6b — record images (`profile_image`, `emp_pic_base64`, …) | ⬜ (only 1 non-empty row; writers still unvalidated) |
| B4 — per-tenant branding wiring | ⬜ deferred at your direction |
| **C8 — legacy public pages: Bootstrap → Tailwind** | ✅ **DONE 2026-09-28** — the 5 standalone `single_*`/search/holding pages + `warish_sonod_view_file.php` no longer load Bootstrap/owl. See changelog below |
| C8a — `front/add.php` (heaviest Bootstrap 5 page) | ✅ **DONE 2026-09-28** — Bootstrap 5 CDN out, converted to the same Tailwind navbar/footer system, modal now on `ht-modal.js`. See changelog below |
| C8b — admin (`back/` + `p/` shell) Bootstrap removal | ⬜ blocked — not verifiable without an admin session (auth gate = `Refresh:0` 0-byte body) |

### B7 changelog — platform routing and tenant isolation

Five defects, all the same shape: **the same idea implemented in more than one
place, and only one of the copies remembered the gate.** Full write-up in
`docs/TENANT-MIGRATION.md` §3.4–3.8.

| # | Defect | Was | Now |
|---|---|---|---|
| 3.4 | `Front::index()` read `?tenant=N` with no host gate | `Host: vireta.dev?tenant=1` → 88 607 b, a live parishad's home page | product site; the override lives only in the resolver, gated on `is_dev_host()` |
| 3.5 | `primary_tenant()` had no `is_dev_only` filter | in production, every unknown host → tenant 1 | filtered, same predicate as `find_by_host()` |
| 3.6 | main domain identified by a hardcoded localhost array | described the dev machine, unreachable in production, and made `vireta.dev` impossible to register | new `platform_domains` table + `Tenant_resolver::is_platform()` |
| 3.7 | legacy `ins_titute.domain_names` fallback had no dev/prod gate | `Host: localhost` in production → tenant 1, 88 596 b | removed after proving it redundant; `/t_diag` now reports coverage gaps |
| 3.8 | `/up` was a bare alias skipping the routing decision | rendered a portal shell on the main domain | both routes call one private `render_site()` |

**The design idea underneath:** a platform host resolves to `id() === 0`, so
every tenant-scoped query in a controller constructor matches nothing. Isolation
becomes *structural* rather than a check somebody has to remember to perform in
a view. `Front::__construct()` runs four tenant queries on every request, which
is exactly why the fix had to be made at resolution time and not at render time.

Verified with a real tenant-1 member name as the marker (`জাফর আহম্মেদ`), in
both development and `APP_ENV=production`: `dhum.local` is the only host that
returns it, and in production no host does.

**Behaviour changes you should know about:**
- `ins_titute.domain_names` is now **write-only**. Setting it no longer makes a
  tenant reachable. Both existing values were already in `tenant_domains`, so
  nothing broke — but a new tenant added without a `tenant_domains` row will be
  unreachable, which is why `/t_diag` reports that case.
- `/up` no longer force-renders the portal on an arbitrary host. Use
  `?tenant=N` on a dev host instead.
- `localhost` in production is now the product site, not tenant 1.

### C8 changelog — legacy public pages: Bootstrap → Tailwind (2026-09-28)

Five standalone public pages still shipped their own Bootstrap (4.6 local plus
5-from-CDN in `add.php`), owl.carousel and Font Awesome. They are now on the
same Tailwind system as the portal:

| View | What changed |
|---|---|
| `front/single_member_views.php` | head → `portal.css` + `legacy-profile.css` + local Hind Siliguri + local Phosphor; navbar/footer/copyright → Tailwind; body grids → `lg:grid-cols-12` / `sm:grid-cols-12`; card → `legacy-profile.css` `.card` (now `background-color:#fff`, `min-height:450px`); `rounded-circle img-fluid` → `rounded-full`; `text-muted` → `text-neutral-500`; owl initiation blocks **removed** (owl JS no longer loaded — they would have thrown TypeError) |
| `front/single_employee_views.php` | same treatment, sibling template (extra Address row) |
| `front/search_holding_number_view_pages.php` | form `input-group` → Tailwind flex; inputs `form-control` → border/rounded classes; SEARCH button `btn btn-outline-primary` → `bg-indigo-600`; **the JS template strings inside the `dataasign_here` success block were converted too** (they embed Bootstrap classes); the `height:0px` + negative-margin hack on the search card removed |
| `front/single_holding_person_view_file.php` | avatar/info cards → Tailwind; `<center>` → `text-center` divs; `list-group list-group-flush` → `divide-y` flex lists; paid/unpaid `row col-md-6` → `md:grid-cols-2`; `.scrollable` kept |
| `front/add.php` | **Bootstrap 5 CDN (css+js) removed**; `<base href>` bug fixed (`<?php base_url(); ?>` → `<?= base_url(); ?>`); navbar + footer added (the page previously had **neither** — this was reported live by the owner as "navbar/footer নাই"); whole form (`form-control`/`form-select`/grid/action buttons) → Tailwind (labels `text-sm font-semibold`, inputs `border-neutral-300 rounded`, grids `sm:grid-cols-2` / `lg:grid-cols-4` / `lg:grid-cols-3`); flash alert → Tailwind; the Add-Home modal kept its `.modal`/`.modal-dialog` shell (styled by portal.css modal block) but now opens via the `.selectingHouse` change handler calling `window.htModal.open(...)` / `.close(...)` instead of Boootstrap's `$().modal('show'/'hide')`; `#spin_loader` `.modal()` calls (element no longer exists on the page) → guarded htModal calls that no-op when the element is absent; **all AJAX hooks preserved** (`selectingWard_no`→`frontGetVillByWard`, `selectingVillage`→`getHomeByVill`, `home_name_add_btn`→`addNewHomeFront`, NID search handler left intact); `ht-modal.js` now loaded on this page |
| **Navbar search button (all 5 pages)** | the owner asked "navbar-এ search button addon কই?" — every converted page's navbar now has a circular magnifier button (`ph ph-magnifying-glass`, `w-10 h-10 rounded-full border-neutral-300`) linking to `search_hold`; on `search_holding_number_view_pages.php` the old active `Search` nav-link was replaced by the same icon button (de-duplication, same destination) |
| `download/warish_sonod_view_file.php` | Bootstrap 5 CDN out; `.text-center`/`.text-danger` moved into its own `<style>` |

Shared pieces:
- **`inc/assets/css/legacy-profile.css`** — the extracted style block from the
  member page, now owns `.card` (added `background-color:#fff` + changed
  `height:450px` to `min-height:450px; height:auto` so a long right-hand card no
  longer clips at 450 px), `.card-body`-equivalent padding via `p-5`, `.nav-link`
  / `.nav-active`, body `#e0e0e0` + **Hind Siliguri font stack**, `.scrollable`.
- **Fonts local** — `inc/assets/vendor/fonts/{hind-siliguri,inter,red-rose}.css`
  + woff2 bundles; no Google Fonts on these pages. (Red Rose link was swapped
  for Hind Siliguri — Red Rose was only ever a dead `@import` inside the removed
  `<style>` block.)
- **Phosphor local** — `inc/assets/vendor/phosphor/regular/style.css`; footer
  icons are `ph ph-map-pin` / `ph-phone` / `ph-envelope`, styled by
  `.single-cta i` in `legacy-profile.css`.
- **No Bootstrap / owl / FontAwesome CSS or JS** remains on any of the 6 pages
  (grep-verified; `nav-item`/`nav-link`/`sr-only` class names remain but are
  styled by our own CSS, not Bootstrap).

Not a regression, verified: `pub_view/<empty-placeholder-uniqid>` (the
`src_holding` empty-search row, `data_id=814`, all NULLs) returns **HTTP 500 on
the pre-conversion backup too** — the controller's year-loop chokes on its NULL
`holding_starting_years` before the view is rendered; it is a pre-existing data
bug in `Front::single_holding_person_methods()`, unrelated to this conversion.
Rows with real data render 200.

Still pending: the admin (`back/` + `p/` shell) which is a D1/D2 override at your
direction but cannot be verified without an admin session.


### B8 changelog — super admin panel access gate (2026-09-28)

**Defect:** `Super_admin::__construct()` admitted anyone with Ion Auth's
`admin` group via `is_admin()` — the same group every union parishad's own
administrator holds (`admin` group rows: users 1, 8, 9, 11). So কাঞ্চন,
বাঘমারা and রূপসা's parishad admins all entered the **platform-wide** panel and
saw the identical global view: every tenant, every admin, cross-tenant
`dashboard?tenant=N` switching included. The panel's own deny message already
claims "শুধুমাত্র সেন্ট্রাল প্ল্যাটফর্ম সুপার অ্যাডমিন" — the check just did not
match the claim.

**Fix (your direction 2026-09-28): the platform super admin is now a tenant-less
role, and the panel is reachable by that role alone.**

1. **Role-only gate** — `Super_admin::__construct` requires
   `in_group('sadmin')`. Neither `is_admin()` (every parishad's `admin` group)
   nor a hardcoded `id == 1` admits anyone. Parishad admins get the 403.
2. **No tenant id on the platform super admin** — `users.ins_pr_id_s` and
   `users.union_unq_idd_iii` are `NULL` for `administrator` (id 1). It belongs
   to the whole SaaS, not to ধুম or any parishad. Backup:
   `vireta-private/db_dumps/20260928_superadmin_notenant.dump`. (`customer_full_info.ins_pr_iddds` for the same row still holds `1` — display-only join key, not read for tenant isolation.)
3. **Login lands on the panel** — `Front::api_login()` routes a `sadmin` to
   `/super_admin` instead of a tenant dashboard; the JSON branch answers
   `tenant_id: 0`.
4. **Empty-dashboard trap closed** — `Dashboard::__construct()`, when `tenant_code() === 0` (platform host), sends a `sadmin` to the panel instead of a barren `0`-tenant dashboard. The panel's `dashboard?tenant=N` switch links are unaffected (they resolve a real tenant).

**Why "5 accounts, same data" was not a rendering bug:** the super admin panel
is a single global dashboard by design — one table of all tenants, global
totals. Anyone who passes the gate sees that *same* page. Before this fix 4
accounts passed; after it, only the `sadmin` role.
**By design, not a leak:** on a tenant host (`dhum.local`), *any* logged-in
admin — even বাঘমারা's chairman — sees that host's parishad (`tenant_domains`
wins over session at resolver step 3). Each parishad's own admin should log in
via their own host (`baghmara.localhost`, `rupsha.localhost`, …) or use the
panel's `?tenant=` switch.

### C1–C3 changelog

- **Tailwind v4.3.3**, config-free — `@theme inline` in CSS replaces
  `tailwind.config.js`. `npm run build` produces `inc/assets/css/portal.css`
  (31 KB) and copies `tokens.css`; `npm run watch` for iteration.
- `resources/css/tokens.css` is the **single source of truth**. Everything in
  it is namespaced `--brand-*`; `portal.css` maps that onto Tailwind's
  `--color-*` names. The namespaces are deliberately disjoint — the first
  attempt collided and produced `--color-primary: var(--color-primary)`, which
  is invalid at computed-value time and silently renders every `bg-primary`
  as nothing. Full write-up in `docs/DESIGN-SYSTEM.md` §3.
- **Palette:** Warm Indigo + Terracotta. Primary `#4f46e5`, accent `#c2410c`,
  surface `#faf7f5` (warm off-white), text `#1c1917`. Chosen to avoid the
  green-and-white institutional look, because this is an agency's product sold
  to parishads, not a government portal.
- **Components are a CSS layer, not CI partials.** `.ht-btn` `.ht-card`
  `.ht-field` `.ht-label` `.ht-badge` in `@layer components`. The original C3
  plan named 14 CI partials; at this size the CSS layer is less indirection,
  not more. Revisit if the markup needs real composition.
- **17/17 contrast pairs pass WCAG AA.** Three failed on the first pass and
  were corrected rather than shipped — see `docs/DESIGN-SYSTEM.md` §4.
- **Admin:** `inc/assets/css/brand.css`, loaded after `app.css` by `p/h.php`
  **only when `Template::back()`/`::sonod()` sets `$ht_shell`**. `p/h.php` is
  shared by front and back, and the overlay deliberately restyles bare
  selectors (`body`, `.card`, `.form-control`) to reach inside MDK — on a
  public page those would hit the Bootstrap markup instead. Verified: 0
  references to `brand.css`/`tokens.css` on `/`, `/login`, `/search_hold`.
- **Bengali was missing entirely from the admin.** MDK's
  `--font-family-sans-serif` was `-apple-system, BlinkMacSystemFont, …`, so
  every label and data cell fell back to the browser default. Fixing the font
  stack changes the admin more than the colour swap did.

### B5 changelog

**Moved out of the webroot** → `/home/darkness/vireta-private/` (outside the docroot, not HTTP-reachable):
- `htdb_dump.sql` (6.1 MB), `db_back/szfjjtji_htdb.sql`, `db_back/szfjjtji_dhum_ht.sql`, `db_back/dt.sql` — 21 MB total
- `index.html.bak`
- `info.php` — was serving a full 95 KB `phpinfo()` page (PHP version, `$_SERVER`, docroot, module list)
- The now-empty `db_back/` directory was removed

**Fresh safety backup taken before any change:**
`/home/darkness/vireta-private/db_dumps/pre_tenant_migration_20260927_231752.dump` (3.6 MB, `pg_dump -Fc`)

**Password rotated:** the DB role password is no longer committed in `application/config/database.php`. It now reads from `.env` in the project root (`.env` is `chmod 600`).

**Files changed:**
- `.htaccess` — added deny rules for `sql|dump|bak|old|orig|save|swp|log|ini|conf|env|sh|tar|gz|zip` and for all dotfiles, for both Apache 2.4 and 2.2
- `router.php` — mirrored the same rules, because the PHP built-in dev server does **not** read `.htaccess` (without this, `.env` was served with HTTP 200 on `:8080`)
- `application/config/config.php` — added a dependency-free `env()` loader that reads `.env` into `$_ENV`; if `.env` is absent the app still boots
- `application/config/database.php` — reads credentials from `env()`; DSN is now assembled from env parts because CI3's PDO driver parses the subdriver out of the DSN string and fatals on an empty one

**Verified after the change:** homepage, `/login`, `/search_hold`, `/add`, `/singleEmp` all HTTP 200; all 4 dumps + `info.php` + `.env` now 404; old password rejected by Postgres; no `Tiger.1234` string remains in any PHP file.

> ⚠️ **Deployment note:** the new `.env` is **not** in version control. On the production host you must create it manually before the app will connect:
> ```
> DB_HOST / DB_PORT / DB_NAME / DB_USER / DB_PASSWORD
> ```
> Without it the app boots but every query fails with an empty-credential connection error (by design — it fails loudly rather than silently using a stale password).

---

## PART A — Verified facts (what I checked before planning)

| # | Finding | Impact |
|---|---|---|
| A1 | `application/config/constants.php:5` → `define('union_code', '1')` | **Primary blocker.** Tenant is a compile-time constant, so one codebase = one institute. Must become runtime-resolved. |
| A2 | `ins_titute` has **1 row** (`un_iddd_iiid=645`, `union_code=22`, `ins_titute_p_idds=1`) | Tenant registry table exists and is correct in shape. |
| A3 | `ins_titute.sub_domains` column exists but is **empty**, and is a **single** column | Cannot hold multiple domains per tenant. Needs a proper `tenant_domains` table. |
| A4 | `users.union_unq_idd_iii` = 645 | Users are **already** tenant-linked. Good foundation. |
| A5 | `satting_infos` contains **vendor/agency** fields (`vandor_name`, `vLogoPath`, `fb_link`, `youtubeLink`, `instagramLink`) | **Correction to my earlier read:** this is the *SaaS vendor's* branding, correctly global. **No change needed.** Tenant branding comes from `ins_titute`. |
| A6 | Tenant branding columns already exist on `ins_titute` (`ins_name`, `ins_logo_path`, `ins_emails`, `ins_address_eng`, `ins_titute_p_idds`) | Per-tenant branding needs **no schema change** — only a resolver to pick the right row. |
| A7 | `holding_rates_yearlys` is keyed by `usr_ide_id` (per-**user**) | Tax rates are per-user, not per-tenant. In a multi-tenant portal a tenant's own rate can be overwritten by another user. **Will fix in Phase 2, not Phase 1.** |
| A8 | `family_member` has no tenant column; scopes implicitly via `family_datasid` → `family_data_sheet.ins_aut_iiid` | Works, but an unscoped query returns cross-tenant rows silently. Flagged for Phase 2. |
| A9 | `amount_paid` (4,374 rows) **is** scoped — column is `in_s_titute_pr_idd` | Safe. (My first grep missed it because the name is `in_s_titute…`, not `ins…`.) |
| A10 | Live production-scale data: `family_data_sheet` 5,935 · `amount_paid` 4,374 · `collect_amnt_invoice` 1,659 · `house_home_name_etc` 1,006 | All migrations must be **additive and non-destructive**. |
| A11 | No `htmlspecialchars` / `xss_clean` anywhere; **331** raw `<?= $obj->prop ?>` echoes | Stored-XSS vector. Will be fixed **only in rewritten files**. |
| A12 | Node v22.23.1 + npm 10.9.8 present; Tailwind not installed | Tailwind build step is viable. |
| A13 | **Zero** dynamic class construction (`class="…{$var}…"`) in `views/front/*` | Tailwind JIT scanning is safe here. No safelist workaround needed. |
| A14 | Institute logo is a **1603×1500 PNG, 2.1 MB**, stored as base64 text, rendered **twice** per page | Causes the 6.7 MB homepage. Resolved as part of Phase 1. |
| A15 | `htdb_dump.sql` (6.3 MB) + `db_back/*.sql` (15 MB) are **web-accessible** (HTTP 200) | Pre-existing critical exposure. Listed in Phase 0. |

---

## PART B — PHASE 0: Multi-tenant foundation (BLOCKING)

Everything in B must land before the home page can be per-tenant.

### B1. Tenant resolver library ✅ WILL DO
- New `application/libraries/Tenant_resolver.php`
- Resolution order: **custom domain → subdomain → dev fallback**
- Matches `HTTP_HOST` against a new `tenant_domains` table (hostname, tenant_id, is_primary, verified_at)
- Resolves to a single `tenant_id`; caches per-request
- **Dev fallback:** when host is `localhost`/IP, read tenant from a `?tenant=` query param or a `X-Tenant` header, so multiple tenants are testable on one dev box

### B2. Replace the `union_code` constant with runtime context ✅ WILL DO
- `Tenant_resolver::id()` becomes the single source of truth
- All **49** usages of `union_code` across 7 files → runtime value
- `constants.php` keeps a **fallback default** so nothing breaks mid-migration
- Old constant is **not deleted** in Phase 0 (safety), only superseded

### B3. `tenant_domains` table ✅ WILL DO
| column | type | note |
|---|---|---|
| `id` | serial PK | |
| `tenant_id` | int FK → `ins_titute.un_iddd_iiid` | |
| `hostname` | varchar unique | e.g. `dhum4.portal.test`, `dhum4.com` |
| `is_primary` | bool | |
| `is_verified` | bool | |
| `created_at` | timestamptz | |
- Additive table; `ins_titute.sub_domains` is **left untouched** (read as a legacy seed only)

### B4. Per-tenant settings access ✅ WILL DO
- Replace the 6 views' direct `$setting->ins_*` reads with a tenant-scoped settings object
- **No schema change** — `ins_titute` already holds the fields (A6)
- Add a per-tenant brand palette column set (primary/accent/surface) so branding is data-driven

### B5. Kill the web-accessible database dumps ✅ WILL DO (security, do first)
- Move all `*.sql` **out of the webroot** into a non-public directory
- Add a deny rule for `*.sql`, `*.bak`, `*.log` in `.htaccess`
- `index.html.bak` also removed from the webroot
- Rotate the DB password currently hardcoded at `application/config/database.php:11`
- Move credentials to `.env`-style file outside version control

### B6. Fix the 2.1 MB logo ✅ WILL DO
- Resize institute logo to 200px (2× of the 100px render) → ~10–20 KB
- Stop storing base64 in the DB; store as a **file** with a normal URL
- Render logo **once** (favicon points at a real small `.ico`/`.png`, navbar at the file)
- Expected homepage: **6.7 MB → well under 200 KB**

---

## PART C — PHASE 1: Design system (public) + admin overlay

### C1. Tailwind build pipeline ✅ DONE
- Tailwind **v4.3.3** via `@tailwindcss/cli`, config-free — `@theme inline` in
  CSS replaces `tailwind.config.js`
- `package.json` at the project root; `npm run build` → both artefacts,
  `npm run watch` for iteration
- Source in `resources/css/`, compiled output committed to `inc/assets/css/`
  so deployment needs no build step
- **Two entry points, to keep Tailwind and Material Design Kit apart:**
  - `portal.css` → Tailwind — public pages
  - `brand.css` → overlay on `app.css` — admin pages, **no markup change**
- Scanning is safe: zero dynamic class construction in `views/front/*` (A13)

### C2. Design token layer ✅ DONE
- Light palette, Warm Indigo + Terracotta
- `resources/css/tokens.css` — colour ramps, semantic roles, type scale,
  radius, shadow, spacing, all as CSS custom properties
- **One source of truth.** `portal.css` maps `--brand-*` onto Tailwind's
  `--color-*`; `brand.css` reads the same file. A colour is written once.
- Tokens are **static for now.** Wiring them to tenant data is B4, deferred at
  your direction. Names are chosen so that is a later data change, not a
  restructure.
- Dark-mode-ready naming, but **no dark mode shipped** (D7)

### C3. Component layer ✅ DONE
Delivered as a CSS layer in `portal.css`, not CI partials:

`.ht-btn` (`-primary` `-accent` `-secondary` `-ghost`) · `.ht-card` ·
`.ht-field` · `.ht-label` · `.ht-badge` (`-primary` `-accent` `-success`
`-warning` `-danger` `-neutral`)

The original plan named 14 CI partials including `table`, `modal`, `navbar`,
`footer`, `pagination`, `skeleton`. Not built, because the public pages have a
narrow repeatable set of needs and a component library at this size adds
indirection. Revisit when the markup actually needs composition.

### C3a. Admin overlay ✅ BUILT, ⬜ NOT YET VISUALLY VERIFIED
`inc/assets/css/brand.css` overrides ~15 MDK custom properties plus the shape
rules CSS variables cannot express. `app.css` is untouched. It **loads** and
all variables resolve, but confirming it renders needs an admin login, which
has not been done.

### C4. New home page ⬜ NEXT
Rebuild of `application/views/front/home.php`, keeping current content and behaviour:
- Institution name, logo, address, phone, email
- Chairman's message + photo
- Council members and staff (from `up_member_s`, `employee_data_ins`)
- Slider (from `home_slider`)
- Search-holding entry point
- All Bangla content preserved verbatim

### C5. Mobile-first & responsive ⬜
Current markup is not meaningfully mobile-tested. New home page is built mobile-first with verified breakpoints.

### C6. Accessibility baseline ◐ PARTIAL
Done in the token layer: 17/17 contrast pairs verified against WCAG AA, visible
focus rings on both tiers, `tabular-nums` for amount columns, Bengali-safe
line-height. Not yet done: semantic landmarks, labelled fields, keyboard menu,
alt text — these are home-page work and land with C4.

### C7. SEO/meta baseline ⬜
Per-tenant `<title>`/description from tenant data, Open Graph tags, canonical URL, `lang="bn"`.

### C8. Print tier ⬜
`inc/assets/css/print.css` for `download/` and the printable `p/` views.
See `docs/DESIGN-SYSTEM.md` §2 for why these are deliberately excluded from
the screen theme.

---

## PART D — EXPLICITLY **NOT** IN THIS WORK (যা হবে না)

Being clear so there are no surprises:

| # | Will NOT happen | Why |
|---|---|---|
| D1 | **No admin markup rewrite.** The 28 `back/` views and the 618-line `p/` shell keep their markup exactly as they are. The admin is *restyled* via a CSS overlay | `app.css` is 16,049 lines with 1,533 hardcoded hex values, and MDK's JS, flatpickr, jQuery UI and select2 are all styled by it. Rewriting is not a retheme. See `docs/DESIGN-SYSTEM.md` §2 |
| D2 | **No Bootstrap or MDK removal.** Both stay. Tailwind is a *new* entry point for public pages only | Removing them breaks every admin view. `front/` was already loading Bootstrap 5 from a CDN alongside Bootstrap 4.6 locally — the new home page fixes that by loading neither |
| D2a | **No screen theme on printed documents.** `download/` and the printable `p/` views keep their fixed boxes, hard borders and `@page` rules, and get print-first CSS instead | A land-holding certificate is a legal document. Soft shadows and light tints disappear on paper; generous radii print as smudges |
| D3 | **No data model column renaming.** `ins_aut_iiid`, `ins_atou_iddd_s` etc. stay | 39 tables, live data — a rename is a huge risky change with no user-visible benefit |
| D4 | **No new features.** No tax calculation rewrite, no new reports, no sonod workflow change, no new user roles | Scope is design + tenancy only |
| D5 | **No `holding_rates_yearlys` per-tenant fix** (A7) | Deferred to Phase 2 — it changes money logic and needs its own testing |
| D6 | **No `family_member` tenant column** (A8) | Deferred to Phase 2; current implicit scoping still works |
| D7 | **No dark mode** | Tokens are named for it, but it is not built |
| D8 | **No offline / PWA / installable app** | Not requested |
| D9 | **No Bangla/English i18n system** | Bangla only, as today |
| D10 | **No SPA / framework conversion.** Stays server-rendered CodeIgniter 3 | Rewriting to React/Vue is a different project. **Update 2026-09-28: this held.** A React SPA for the product site was built, then ported back to PHP and deleted — 11 views, no Vite, no React. The decision is now true again rather than aspirational. |
| D11 | **No PHP/CodeIgniter upgrade.** Stays CI 3 on PHP 8.5 | Out of scope; note CI 3 is EOL — flagging, not acting |
| D12 | **No automatic tenant provisioning / billing / super-admin panel** | Multi-tenancy here means *data isolation + routing*, not a SaaS business layer |
| D13 | **No DNS or SSL certificate setup.** I prepare hostnames + docs; you do registrar/DNS | Needs your accounts |
| D14 | **No wholesale XSS escaping of all 331 outputs** (A11) | Only rewritten files get escaping. Full sweep is Phase 2 |
| D15 | **No test suite / CI setup** | Can be added on request |
| D16 | **No work on the 65 dead `views/ex/` files** | Ion Auth demo, never rendered, 22,403 lines. Left untouched |
| D17 | **No bundled Bengali webfont.** The stack names Hind Siliguri but the file is not in the repo | Depends on the tenant's system having it. Bundling a 200 KB+ subset is your call — say the word and I will do it |

---

## PART E — Risks I want you to know about

| Risk | Severity | Mitigation |
|---|---|---|
| Home page must query by `tenant_id` instead of the constant — if any query is missed, a tenant could see another tenant's data | **High** | Every query in the rewritten path is reviewed line-by-line; tenant filter applied centrally, not per-call |
| Adding a second tenant to a DB that was built for one could surface latent unscoped queries (A8) | **High** | Before launch: seed a 2nd test tenant, then diff query results per tenant |
| Tailwind + Bootstrap both present in the project | Medium | Separate CSS entry points (C1); home page never loads Bootstrap |
| `union_code` is used in 49 places | Medium | Phased replace with a fallback default; grep-verified zero leftovers |
| Live data in the same DB as dev | Medium | All Phase 0/1 DDL is additive; I'll take a fresh `pg_dump` before any migration |

---

## PART F — Files

Created:

```
resources/css/tokens.css               ← single source of truth
resources/css/portal.css               ← Tailwind entry, public pages
resources/build/copy-tokens.mjs        ← copies tokens where the browser can fetch them
package.json                           ← npm run build / build:css / build:admin / watch
inc/assets/css/portal.css              ← compiled, 31 KB
inc/assets/css/tokens.css              ← served copy for the admin
inc/assets/css/brand.css               ← admin overlay on MDK
application/libraries/Tenant_resolver.php
application/libraries/Tenant_media.php
application/helpers/tenant_helper.php
application/helpers/media_helper.php
application/helpers/text_helper.php
docs/ARCHITECTURE.md  TENANT-MIGRATION.md  MEDIA-STORAGE.md  DESIGN-SYSTEM.md
```

Not created, deliberately: `tailwind.config.js` (v4 is config-free) ·
`postcss.config.js` (same) · `inc/assets/css/admin.css` (the overlay
superseded it) · `application/views/partials/` (components are a CSS layer
instead — see C3) · `application/libraries/Tenant_branding.php` (that is B4).

Database additions: `tenant_domains` table. **Nothing dropped or renamed.**
B4 will add brand palette columns to `ins_titute`, also additively.

---

## PART G — Order of execution (as executed)

1. **B5** — dumps hide + password rotate ✅
2. **B1–B3** — tenant resolver + `tenant_domains` + de-constant ✅
3. **B6a** — logo/slider base64 → per-tenant files ✅
4. **C1–C3** — Tailwind pipeline + tokens + components ✅
5. **C3a** — admin token overlay ✅ built, ⬜ needs a login to verify visually
6. **C4–C7** — new home page ⬜ **next**
7. **C8** — print tier (`print.css`) ⬜
8. **B4** — wire per-tenant branding ⬜ deferred at your direction
9. Verify with the seeded second tenant, then hand over

---

## Open questions — answered

1. **B5 first?** Yes. Done 2026-09-27.
2. **Two entry domains?** Yes — `dhum.local` (tenant 1) and `test-2.local`
   (tenant 3), both `is_dev_only = TRUE` and gated off in production. See
   `docs/TENANT-MIGRATION.md` §5.
3. **Light palette** — I proposed, you took it: **Warm Indigo + Terracotta**.
4. **Home page content** — reorganising is fine; content itself is preserved
   verbatim.

## Still open, needing you

1. **`/etc/hosts`** is not writable without sudo. For the dev hostnames to
   resolve: `sudo sh -c 'printf "127.0.0.1 dhum.local test-2.local\n" >> /etc/hosts'`
2. **Delete the test tenant 3** before launch — SQL is in
   `docs/TENANT-MIGRATION.md` §6.
3. **`log_threshold = 0`** in `config.php` means CI error logging is OFF, so
   PHP errors are invisible. I have not changed it because it is a live
   production behaviour change, not a design one.
4. **`encryption_key = 'allion5'`** is weak. Same reason — I am not changing
   security config without your say-so. Note that changing it invalidates
   existing sessions and any encrypted data.
5. **`application/libraries/Pdf.php`** is broken: it requires
   `application/libraries/tcpdf/tcpdf.php`, which does not exist. Only
   referenced from a commented line at `api/Login.php:17`, and mPDF has
   already replaced it. Recommend deleting.
