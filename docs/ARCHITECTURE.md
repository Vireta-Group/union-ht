# System Architecture — HT X ViretaDev

A plain CodeIgniter 3 MVC application. No framework wrapper, no dependency injection, no ORM.

---

## 1. The two top-level halves

```
/                       ← WEBROOT (Apache docroot = here)
├── index.php           ← FRONT CONTROLLER. The only real entry point.
├── router.php          ← dev-only router for `php -S` (blocks .env/*.sql)
├── .htaccess           ← rewrites everything -> index.php
├── .env                ← credentials, chmod 600, HTTP-blocked
│
├── system/             ← CodeIgniter 3 CORE (vendor code, never edited)
├── vendor/             ← Composer: mPDF, TCPDF, PHPUnit, Spatie
├── inc/                ← frontend assets: css, js, fonts, images, vendor libs
│
└── application/        ← ALL application code lives here
```

**Everything you care about is in `application/`.** `system/` and `vendor/` are third-party — never touch them.

---

## 2. `application/` anatomy

| Dir | Count | Role |
|---|---|---|
| `config/` | 15 | Settings. DB creds, routes, autoload, Ion Auth config |
| `controllers/` | 8 | HTTP entry points. One class per URL segment |
| `models/` | 6 | All SQL lives here |
| `views/` | 129 | HTML output (no logic intended, but much logic leaked in) |
| `libraries/` | 5 | Reusable service classes |
| `language/` | 78 | 40 locales, but the app hardcodes Bangla — unused |
| `cache/ logs/ third_party/ core/ hooks/ helpers/` | 0 | **empty** — unused CI extension points |

---

## 3. Request lifecycle

A URL maps to `Controller::method()` by a route rule:

```
GET /search_hold
  → routes.php  $route['search_hold'] = 'front/search_holding_front_page'
  → Front::search_holding_front_page()          (controllers/Front.php)
  → SettingModel::get_ins_sliders()             (models/SettingModel.php)
  → SELECT * FROM home_slider WHERE insidpris_id = union_code
  → load->view('front/search_holding_number_view_pages', $data)
  → HTML out
```

Data always flows **controller → model → database**. Controllers never build SQL; models never echo HTML. That separation mostly holds in this codebase.

---

## 4. Controllers

| Controller | Lines | Role | Auth |
|---|---|---|---|
| `Front.php` | 226 | Public site: home, search, single person/member/employee, add | none |
| `Auth.php` | 889 | Login, logout, password reset, user & group CRUD | none |
| `Dashboard.php` | 1512 | **The admin panel** — 100 methods, everything else | required |
| `Sonod.php` | 312 | Certificate (সনদ) generation & verification | required |
| `Download.php` | 481 | PDF printing (mPDF/TCPDF) + Bangla font conversion | required |
| `Welcome.php` | 30 | CI demo stub — dead | — |
| `api/Login.php` | 136 | Mobile/API login | none |
| `api/Sample.php` | 81 | Demo API — dead | none |

### The auth guard

`Dashboard` guards itself in the constructor — every one of its 100 methods is protected by 4 lines:

```php
if (!$this->ion_auth->logged_in()) { redirect('logout', 'refresh'); }
$this->user_id = $this->ion_auth->user()->row()->user_full_tbl_id;
```

`Sonod` and `Download` repeat this pattern. `Front`, `Auth` and `api/*` have **no guard** — `api/*` is reachable with no authentication at all.

---

## 5. Models

| Model | Lines | Role |
|---|---|---|
| `Ion_auth_model.php` | 2804 | Vendored **Ion Auth** library. Login, hashing, groups |
| `FamilyModel.php` | 575 | The real workhorse. Holdings, invoices, balances, employees, members |
| `SonodCrudModel.php` | 126 | Certificate CRUD |
| `UserModel.php` | ~45 | User profile + geo-joined user info |
| `AddMemberModel.php` | ~55 | Data-sheet insert |
| `SettingModel.php` | ~52 | Institute settings, sliders, members, employees |

`FamilyModel` is where the tenant scoping lives — 22 `where('...', union_code)` calls.

---

## 6. The layout system (important for the redesign)

There is **no layout engine**. `libraries/Template.php` composes pages by concatenating views:

```php
Template::front('front/login', $data);
  → load->view('p/h', ...)   // <head> + all CSS   (126 lines)
  → load->view($view, ...)   // page content
  → load->view('p/f', ...)   // <footer> + spinner (98 lines)

Template::back('back/dashboard', $data);
  → p/h  +  p/n  (sidebar+navbar, 191 lines)  +  view  +  p/f
```

`views/p/` = the shared chrome. `p/h` is head, `p/n` is the admin sidebar/navbar, `p/f` is footer + spinner, `sf`/`sh` are unused alternates.

**But `front/home.php` bypasses this entirely** — it is a standalone document with its own `<head>`, its own CSS, and no `p/h`. That is why the public site and the admin look like two different products.

### Two separate design systems

| | Public pages | Admin + login |
|---|---|---|
| Template | `front/*.php` standalone | `p/h` + `p/n` + `back/*` |
| Framework | **Bootstrap 4.6** + Owl Carousel | **Material Design Kit** (`mdk-*`) + jQuery UI + Flatpickr + Perfect Scrollbar |
| Icons | Font Awesome | Material Icons + Font Awesome |
| Styling | inline `<style>` per view | `app.css` (3.1 MB) |
| Mobile | not really handled | template provides responsive |

`views/ex/` is Ion Auth's bundled **demo** views — unused dead weight.

---

## 7. Auth: Ion Auth (vendored)

Config in `config/ion_auth.php`:
- identity column = **`u_type`** (not username/email)
- hashing = **bcrypt**, cost 10
- default group `members`, admin group `admin`
- 3 failed attempts → 600s lockout, tracked by IP

Wired via `autoload.php`: `libraries = [database, email, session, curl, ion_auth]`, `helper = [url, file]`.

---

## 8. Other libraries

| Library | Role |
|---|---|
| `Template.php` | View composition (above) |
| `Ion_auth.php` | Auth facade over `Ion_auth_model` |
| `Curl.php` | HTTP client (395 lines) |
| `Getapi.php` | **External NID verification API** — hits a government service to validate NID + date of birth |
| `Pdf.php` | Trivial `extends TCPDF` shim |

---

## 9. The tenancy model (current)

**Single-tenant, resolved at compile time.**

```php
// config/constants.php
define('union_code', '1');
```

Every query hardcodes `union_code` (49 usages). `ins_titute` has exactly **one** row. So the deployment model is: *one institute gets one copy of the codebase with the constant edited to match.*

Two different tenant IDs coexist and are easy to confuse:

| ID | Value | Meaning | Where used |
|---|---|---|---|
| `ins_titute_p_idds` | `1` | the `union_code` | most `where()` clauses |
| `un_iddd_iiid` | `645` | internal institute PK | `users.union_unq_idd_iii`, `ins_titute` join |

`ins_titute.sub_domains` exists but is empty — subdomain tenancy was planned and never built.

---

## 10. Problems this architecture implies for the redesign

1. **No layout engine** — the redesign has to build one; `Template.php` is too thin to extend.
2. **Two design systems** — cannot be unified incrementally; a new system has to run alongside the old.
3. **`union_code` is a constant** — 49 usages across 7 files must become runtime values before any tenant logic is trustworthy.
4. **Auth is opt-in per controller** — a new controller that forgets the guard is publicly exposed by default. There is no central middleware.
5. **`api/*` has no auth at all** — needs auditing before it is ever exposed to multiple tenants.
6. **329 raw `<?= $obj->prop ?>`** in views, no escaping helper anywhere → stored XSS.
7. **Images stored as base64 in the DB** — a 2.1 MB logo, rendered twice per page, is why the homepage is 6.7 MB.
8. **`FamilyModel` is the tenant chokepoint** — good news: if the resolver is applied there centrally, most queries are covered by construction rather than by discipline.
