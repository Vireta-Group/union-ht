# The super-admin panel

**Status: all seven panel pages built and verified 2026-09-30. The user
module is read-only; its write half is not built, and needs approval first.**

The `sadmin` panel is the platform's control room: it sees every tenant, not
one parishad. It sits at `/super_admin` and is reachable only by a user in
the `sadmin` group.

This doc records what exists, what it deliberately does not do, and the
drift that is still open. It is referenced from the code, so it has to
exist.

---

## 1. Read this first

| | |
|---|---|
| **What the panel is NOT** | An access-control list. Nothing in it decides whether a tenant's site is served. |
| **The gate** | `require_sadmin()` in each constructor. It **halts**. |
| **The log** | Forward-looking only. There is no history before the first row and there never will be. |
| **The money** | Typed in by a human. There is no payment gateway. |

Read section 2 before changing anything in here. The two sentences that
matter most are in 2.2.

---

## 2. The three things that are easy to get wrong

### 2.1 The gate is a function, not a base controller

There is no `MY_Controller` in this project. Each panel controller calls
`require_sadmin()` (autoloaded from `sadmin_helper.php`) in its constructor.

It was a function because the panel is six controllers and pasting
`in_group('sadmin')` into six constructors is exactly the failure
`enforce_tenant_scope()` documents in AGENTS.md: the copy that gets
forgotten. The fifth inline copy is the one that forgets, and a forgotten
check on a platform panel is not a cosmetic bug.

`require_sadmin()` halts on failure and **both** failure modes answer 403 —
not signed in, and signed in as the wrong role. They are deliberately
indistinguishable, because telling them apart is an account-existence
oracle and a platform panel is the last place to leak one.

### 2.2 It records, it does not enforce

Nothing reads `subscriptions.status` to decide whether to serve a parishad's
site, and nothing reads `ins_titute.is_active`. A subscription that has
expired is a **fact in a table**, not a switch that has been thrown.

This is a decision, not an omission. What a lapsed tenant's visitors should
see — a renewal page, a read-only portal, an error, nothing — is three
different products, and picking one is a product decision for the owner, not
a schema change. The panel shows the state so the decision can be made from
real data.

**Do not read "expired" in this panel as "switched off".**

### 2.3 `payment_method` is a label, not a transaction

A row in `subscription_payments` means "a person confirmed they received
this". There is no bKash, Stripe or bank integration in this codebase and
none was added. `payment_method` is free text somebody typed.

A payment does **not** extend a subscription. Recording money and moving the
expiry date are two different acts by two different people on purpose, so
that a mismatch is visible instead of silent.

---

## 3. The modules

| URL | Controller | What it is |
|---|---|---|
| `/super_admin` | `Super_admin` | overview |
| `/sadmin_tenants` | `Sadmin_tenants` | tenant directory, onboarding, hostnames |
| `/sadmin_subscriptions` | `Sadmin_subscriptions` | packages, subscriptions, payments |
| `/sadmin_support` | `Sadmin_support` | the helpdesk |
| `/sadmin_settings` | `Sadmin_settings` | platform settings + per-tenant contact details |
| `/sadmin_sonod` | `Sadmin_sonod` | sonod global design, 55 border frames, monograms, pad clearances |
| `/sadmin_audit` | `Sadmin_audit` | the action trail, CSV export, retention |
| `/sadmin_users` | `Sadmin_users` | **read-only.** See section 6. |

All of them render through `Template::sadmin()`, which is deliberately NOT
`Template::back()`: no tenant-scoped queries, no MDK bridge, no `p/h.php`,
no 3.1 MB of `app.css`. The chrome is three files — `sa_head.php`,
`sa_nav.php`, `sa_foot.php`.

**The intake side is not a panel page.** `POST /support_submit` is
`Support::submit()`, a public ungated controller that is the only writer of
`support_tickets` from outside the panel. It was built with the panel and
sat with **no caller on the public site** until 2026-09-30, when `/contact` and
the support modal were wired to it. See 7.1.

Its route is declared in `routes.php:78` under a comment that says why it
carries no `sadmin` gate. The gate belongs on the *panel's* view of a ticket
(`Sadmin_support`, which has it); the endpoint that creates one is the public
door and is meant to be open.

---

## 4. The shell

`Template::sadmin()` resolves the current module **once**, from
`$CI->uri->segment(1)`, and hands it to the chrome as `$sa_seg`. Both
halves of the nav read it to mark the active link.

It is resolved in the library rather than in each view because
`uri_segment()` does not exist in CI3 and because two files that each
computed it would eventually compute it differently.

`sa_head.php` carries a `<base href>`, because the panel writes anchor hrefs
as bare segments (`dashboard`, `super_admin`) to match every other link in
this codebase, and without `<base>` those resolve against the current
directory — `/sadmin_settings` would look for
`/sadmin_settings/sadmin_subscriptions`.

### The debug page you will see first

`/super_admin` is the overview. If a link in a bookmarked answer or a cached
page still points at `super_admin/api_toggle_domain`, it resolves: the old
`super_admin/api_*` routes are kept as aliases. A 500 on a URL that used to
work is not a way to learn a module moved.

---

## 5. The audit log

`audit_logs` records what **platform staff** did, from the moment the table
was created. `audit_log()` is autoloaded, and `sadmin_write()` is what every
panel mutation goes through so that "did we log this" is a property of the
call rather than a thing to remember.

### 5.1 What is NOT in it

- **No backfill.** A migration can invent a plausible history, which is
  worse than no history, because a fabricated audit trail reads exactly like
  a real one. The first row is the first thing that happened.
- **No login events.** `login_attempts` predates this and is a different
  table. `Auth::login()` writes it, `api_login()` does not.
- **No parishad activity.** A tenant admin editing their own holdings
  generates nothing here. This is a cross-tenant control.
- **No public support tickets.** `support_tickets` **is** the record. Two
  tables holding the same fact means one outlives the other, and the audit
  log would end up counting tickets that have been deleted.
- **No failed writes.** The row is written *after* the write, on purpose. An
  entry for a change that never happened is not evidence.

**A gap in this log is not proof that nothing happened. It is proof that
nothing succeeded.** The page says this too, because it is the distinction
an operator needs and it is not obvious.

### 5.2 Retention

`api_purge_before()` deletes rows older than a cutoff, which is the one thing
an audit log exists to make hard. Three guards:

1. **A 90-day floor.** A request older than that is refused outright, so
   "delete everything" is not expressible. The floor is a class constant and
   is not adjustable from the panel — a retention floor you can edit from the
   panel is not a floor.
2. **The purge records itself, and cannot avoid it.** It deletes rows
   *older* than the cutoff and then writes its own row, which is newer. The
   one operation that destroys history is the best-preserved entry in it.
3. **A typed word, not a dialog.** The first press answers with the exact
   count that would go; the second press is the operator agreeing to that
   number rather than to one they guessed at.

On a young install the purge cannot do anything at all, which is correct
behaviour. The panel says so rather than hiding the card — a feature
discovered by reading a doc is a feature whose first use is its first sight.

### 5.3 `tenant_id` is NULL for platform actions

A platform settings change and a log purge are recorded with
`tenant_id = NULL`, **explicitly**. `audit_log()` falls back to
`tenant_code()` when the key is absent, and the panel is served on a parishad
host in development and on the apex in production. Without the explicit NULL,
changing the platform's support phone number would be filed under whichever
parishad the admin happened to be visiting, and the per-tenant filter would
report it as that parishad's change.

This bit every module during the build. It is a property of where the panel
happens to be running, so it is easy to forget and worth stating in each
controller — and it is stated in each controller.

### 5.4 CSV export

`/sadmin_audit/export` takes the same filters as the page. It is capped at
20,000 rows, and a truncated export says so **in the file**, because a
partial export that looks complete is worse than a refused one.

It is not audited. Reading is not a change, and auditing reads turns "show me
what changed" into "show me what has been looked at", which is a different
and much larger product.

---

## 6. The user module — deliberately read-only

`/sadmin_users` renders. It lists every account, filters by search, group,
tenant and login state, and shows the group breakdown. It **cannot change
anything, by decision.**

**The five write endpoints are declared and do not exist.**
`config/routes.php:61-66` carries all six routes and every `api_*` one 404s:

```
/sadmin_users                    200   (the read-only list)
/sadmin_users/api_save_user      404   (CI3's 404 page)
/...  api_toggle_active          404
/...  api_reset_password         404
/...  api_delete_user            404
/...  api_set_group              404
```

**This was asked and answered on 2026-09-30.** The five endpoints were put to
the owner as "keep read-only / build all five / build with guards / password
reset only", with the note that enabling them means a `sadmin` can edit any
account, reset any password, change any group and delete any user — including
other `sadmin`s and their own. The answer was **read-only**, so this section is
a decision on the record rather than an unfinished feature. Re-open it if the
operational need for a chairman password reset becomes real; if you do, ship
the guards in the same change, not after it.

They are left in `routes.php` on purpose, with a comment there saying so. A
declared-and-absent route is a loud 404; a deleted route leaves nothing
recording that the module was planned and why it stopped.

They are absent because all five are writes to `users` or `users_groups`, and
AGENTS.md requires **explicit approval** before touching either. Approval for
the two `users` writes that *were* requested on 2026-09-30 was given (clearing
`password_viewss_s`, deleting the probe account — 6.3); approval for these five
was asked and declined. **Do not add them because the read page exists.**

### 6.1 The three columns on it do not mean what their type says

`users` is ion_auth's original MySQL-shaped schema, imported as-is, and three
of its columns are typed wider than the values anyone puts in them. The page
renders each from the code that reads it, not from the datatype:

| Column | Actual type | What the code does | How the page shows it |
|---|---|---|---|
| `active` | `integer`, `CHECK (active >= 0)` | `Ion_auth_model::login()` tests `if ($user->active == 0)` | `0` বন্ধ, `1` চালু, anything else "অস্বাভাবিক" |
| `created_on` | `integer` unix seconds | — | `<= 1` → "—" + তারিখ নেই |
| `last_login` | `integer` unix seconds | — | `<= 1` → "—" + কখনো লগইন হয়নি |

**`active = 2` does not lock an account.** `== 0` is false for 2. Every live
row is 1, so nothing is wrong today — but a page that colours any non-1 red
would be reporting a lockout that does not exist, and that is the one thing
this page exists to be right about.

**The timestamps contain sentinels, not times.** `created_on = 1` and
`last_login = -1` (still on user 11) are what a create script writes when it
has no value. Rendered naively they print `1970-01-01` and `1969-12-31`,
which is a confident, wrong answer about when an account was made and last
used. The check is `<= 1`, not `== 0`, because the sentinels are 1 and -1.

Both sentinels were observed on **user 7**, the throwaway probe account from
the 2026-09-29 tenant-guard test. That row was deleted on 2026-09-30 (see 6.3),
so these two are no longer reproducible from the live data — the check stays
because the sentinel is what the create script writes, not because a row
currently shows it.

### 6.2 A user with no group can still log in

`Ion_auth_model::login()` never touches `users_groups`. What a groupless
account lacks is a **role**: every `in_group()` is false, so the sidebar
renders empty and no admin route accepts it. It is not a locked account, and
the page says "কোনো দায়িত্ব নেই" rather than "লগইন বন্ধ", because the fix is
different — one is setting `active = 0`, the other is adding a group.

There is **no live example any more.** User 7 (`test2@test.local` /
`test-tenant2-user`, `ins_pr_id_s = 3`) was the groupless account this
paragraph was written about — it was created by the 2026-09-29 tenant-guard
test and **deleted on 2026-09-30** with the approval of 6.3. It had no
`customer_full_info` row, so the delete touched only `users`.

Every one of the 5 remaining accounts has at least one group, so the page
currently shows no "কোনো দায়িত্ব নেই" row. The branch is kept because it is
the reachable state, not the current one: `Tenant_creator` can be called for
an account whose group insert fails, and a panel that cannot say "this account
can log in but owns nothing" is the panel that reports it as locked.

### 6.3 `password_viewss_s` — CLEARED 2026-09-30

**5 of the 6 accounts held a recoverable plaintext password in this column**
(users 1, 6, 8, 9 and 11; user 7, the probe account, was already clean). They
were **cleared on 2026-09-30**, on approval, after a `pg_dump`:

```sql
UPDATE users SET password_viewss_s = NULL WHERE password_viewss_s IS NOT NULL;
-- UPDATE 5
```

`users` is now 5 rows and the column is NULL on every one. The statement is
recorded here because it may need repeating after a dump restore, and because
the column is still in the schema and still nullable — nothing stops the next
tenant-creation path from writing to it again.

Before it was cleared, the column was inert: nothing compared it to anything.
The only two live references were the panel's `WHERE password_viewss_s IS NOT
NULL` **count** and an `!empty()` marker in the view — the value itself was
never selected for display, and that is why the page could report the count
without becoming a password viewer.

AGENTS.md recorded that the login backdoor reading this column was gone, and
that was true — but see the next section, because "gone from the model" turned
out to be the thing that was wrong.

### 6.4 `u_type` and `ins_pr_id_s` disagree

`u_type` is meant to be the tenant's slug. It is not, for some rows:

| User | `u_type` | `ins_pr_id_s` | that tenant's `sub_domains` |
|---|---|---|---|
| 1 | `dhum-union` | NULL | — (platform) |
| 6 | `bolai` | 1 ধুম | *empty* |
| 8, 9, 11 | `kanchan` / `baghmara` / `rupsha` | 4 / 5 / 10 | match |

(user 7 was the other `bolai`, against tenant 3; deleted 2026-09-30.)

So `ins_pr_id_s` is the authority and the slug is a label that has drifted —
exactly the "two tenant ids" rule in AGENTS.md, in a column nobody documents.

Counted as **three** states, not one "mismatch" number, because "differs" is
not one thing: the tenants that predate the conversion (1 and 5) have an
**empty** `sub_domains`, so for their users the question is *unanswerable*,
not answered wrongly. Reporting those as mismatches would be a finding about
a user that is really a finding about a tenant.

### 6.5 "Proving it": the login backdoor was live in the MODEL

AGENTS.md said the `['password', 'admin', '841998']` backdoor was removed. It
was not. It was removed from `Front::api_login()` — which calls
`password_verify()` **directly** and therefore never reached the code being
removed. The list that actually executed was in
`application/models/Ion_auth_model.php`, in two places, reached by
`Auth::login()` through `ion_auth->login()`:

```php
// verify_password()  — bypasses bcrypt entirely
if (($identity === 'admin@admin.com' || $identity === 'administrator'
     || $identity === 'dhum-union')
    && in_array($password, ['password', 'admin', '841998'], TRUE))
{
    return TRUE;
}

// is_max_login_attempts_exceeded()  — no brute-force limit
if (ENVIRONMENT === 'development'
    || in_array($identity, ['admin@admin.com', 'administrator', 'dhum-union']))
{
    return FALSE;
}
```

All three identity strings are identities of **user 1 — the only `sadmin`**,
whose `ins_pr_id_s` is NULL and who therefore passes `enforce_tenant_scope()`
for every tenant. The two halves belong together: unlimited attempts on an
account that also had three known passwords is one weakness.

**How it was found: by logging in, not by reading.** Posting
`password=841998` to `/login_check` returned a redirect to the dashboard,
while `password_verify()` against user 1's stored bcrypt hash accepts only
`password`. A review of AGENTS.md would not have found this, because AGENTS.md
was confidently correct about a branch in the wrong file.

**Both were removed on 2026-09-30** (backup:
`backups/20260930_pre_backdoor_removal/Ion_auth_model.php`). What is proven,
by HTTP against the real login form:

| Identity | `password` | `admin` | `841998` | `PASSWORD` | wrong |
|---|---|---|---|---|---|
| `admin@admin.com` | in | refused | refused | refused | refused |
| `administrator` | in | refused | refused | — | — |
| `dhum-union` | in | refused | refused | — | — |

**What is NOT proven: the lockout.** The `ENVIRONMENT === 'development'` half
of the exemption is kept deliberately, and locally that is the half that
decides — the admin still logs in at 7 recorded attempts, which is correct in
development and tells you nothing about production. Production cannot be
exercised here: `index.php`'s preflight refuses to boot
`ENVIRONMENT === 'production'` without an `APP_ENCRYPTION_KEY`, and `.env` has
none. So that half rests on the literals being absent from the file, not on an
observed refusal. **Verify it after the key exists** — and note that the
first thing to do then is delete the accumulated `login_attempts` rows, or
the `sadmin` will be locked out on its first production login.

One trap in testing this, cost a false alarm: `is_max_login_attempts_exceeded()`
returning FALSE does **not** mean attempts go unrecorded —
`increase_login_attempts()` writes the row either way, and a successful login
then calls `clear_login_attempts()` and deletes them. So "the row appeared"
and "the row disappeared" are both expected in normal operation and neither
says anything about the exemption. The exemption only ever showed up as *a
correct password being accepted*, which is what was actually tested.

Two related facts about that table, found on the way:

- **33 of its 35 rows have `login IS NULL`.** Those are `Auth::login()`'s
  **successful**-login history — device, browser, host — written through
  `UserModel::insert_login_info()` with no `login` key at all. The table is
  two things: ion_auth's brute-force counter and the app's login history. The
  NULL rows can never match `WHERE login = $identity`, so they never affect
  lockout counting. Benign, but it means "rows in `login_attempts`" is not a
  measure of attack attempts.
- The identity **is** recorded on failures: a wrong password for a fresh
  identity produced exactly one row carrying that identity.

---

## 7. Drift — what is open, and what was closed

These are real inconsistencies, found while building the panel. Each entry
says which it is. **Nothing here should be fixed quietly** — two of them are
`users` writes and one is a decision about the product, not a bug.

### 7.1 The product site did not read platform settings — FIXED 2026-09-30

This one was open while the panel was being built and is now closed. The
detail is kept because the *shape* of the bug is the point.

`Sadmin_settings` stores and audits the platform's contact details. Until
today the product site carried its own hardcoded copies of the same five
values, in three files, and they had already drifted away from the settings:

| Where | What it said | What settings said |
|---|---|---|
| `support_modal.php` | `wa.me/8801813152799` | `wa.me/8801713000111` |
| `contact.php` | `support@viretadev.com` | `hello@viretadev.com` |
| `support_modal.php` | `সবসময় খোলা` | `শনি–বৃহস্পতি, সকাল ৯টা – সন্ধ্যা ৬টা` |
| `support_modal.php` | visible text **`০১৭১৩-XXXXXX`** | a real number, `01813152799` |
| `contact.php` | `<form>` with **no `action`** | — |

The phone was the worst of them: the display text was a literal `XXXXXX`
placeholder sitting beside a `tel:` link that dialled a real number, so the
most prominent number on every page of the product site read as unfinished.
Nobody had noticed, because a value that looks wrong in the source is easy to
dismiss as a placeholder — but it was shipping.

**What replaced it.** `application/helpers/platform_helper.php`, autoloaded as
`platform`. One function, `platform_contact()`, is the only reader of
`satting_infos` outside the settings editor, and all three views call it. The
`tel:` / `wa.me` / `mailto:` schemes are built *in the helper* and never taken
from the database, then the value is filtered — so a stored `javascript:` payload
is unreachable. See section 5.4's note on export for the same reasoning applied
to CSV.

Two behaviours the helper establishes, and both are decisions rather than
accidents:

- **A blank field omits that channel.** The operator clearing `vphone2` means
  "we no longer have a second number", and a card rendered with an empty value
  would look like a bug. The navbar drops the `|` separator with it.
- **A missing `satting_infos` row is an outage, not a blank.** It falls back to
  hardcoded values and sets `stale => TRUE` so a view can say something. The
  helper catches, degrades and reports rather than throwing, because it runs in
  the footer path of every public page.

**The second half of the fix: the two forms posted nowhere.** `contact.php` had
a form with no `action`, and `support_modal.php` had no form at all. Both now
post to `Support::submit()` at `POST /support_submit`, and both branches of that
endpoint's content negotiation are exercised in production:

- `/contact` is a plain `<form method="post">` with no JS, and takes the
  redirect branch. It re-flashes the submitted values on a **failed**
  submission so a validation mistake does not cost the visitor the message they
  typed. Whitelisted, capped, and the honeypot is excluded — see the docblock on
  `Support::reply()`.
- The modal `fetch()`es JSON and renders the answer in place, because a `<form>`
  there would reload the page underneath the modal and lose the reader's place.
  `textContent` only; `<noscript>` points at `/contact`.

Verified live: 303 → `/contact` on both success and failure, the form
repopulated on failure (textarea, `plan` selection and honeypot blank all
checked), escaped XSS through the flashed value, honeypot answers success and
stores nothing, four open-redirect attempts refused by the `return_to`
sanitiser, and the 6th submission from one IP refused with 429 and
`৬০ মিনিট পরে আবার চেষ্টা করুন`.

**The settings → public-site chain was proven by moving the values, not by
reading the code.** `vphone1` set to `01999999999`, `vmail` to
`probe@example.org`, `vsupport_hours` to `PROBEHOURS`, then all four channels
blanked; checked on `/`, `/contact`, `/plans`, `/login`, `/register`, then
restored and re-checked. Two things that only show up when you do it:

- **The display label is Bangla, the `tel:` link is ASCII.** `০১৯৯৯-৯৯৯৯৯`
  beside `tel:01999999999`. Correct — the site is in Bangla and a phone
  dialled from a `tel:` href must be ASCII — but a test that greps for the
  ASCII form in the visible text reports "not found" and looks like a failure.
- **Blanking every channel produced the notice on both pages, with no
  dangling `|` in the navbar**, and the form still worked. That combination is
  the thing worth having tested: a settings page that can be emptied must not be
  able to empty the site's contact details into a broken layout.

`Sadmin_settings` itself no longer says its values are write-only; it says
they are shown in the navbar, the support modal and the contact page. Three
copies of that claim existed — the page banner, the group note in
`platform_specs()` and the controller's own docblock — and all three asserted
something false after the wiring, which is the usual way a doc becomes a lie.

**One thing that looks like protection and is not:** the session cookie is
named `xrf_token` (`config.php:117`, a CI3 template default). `csrf_protection`
is `FALSE`, so there is no CSRF token and no XSRF mechanism of any kind. A cookie
jar full of `xrf_token` reads like a token and is a session id.

### 7.2 `saas/pages/plans.php` hardcodes its own prices

`plans.php:34,54,77` carry `৳২৫,০০০` / `৳২,৪০০`, `৳৪০,০০০` / `৳৩,৮০০` and
`কাস্টম` — a literal PHP array in the view, in Bangla numerals, for both
billing cycles. The `packages` table and the `Sadmin_subscriptions` editor
both exist and neither is what the visitor sees. **A plan a sadmin edits in
the panel does not change the price on the pricing page.**

`contact.php` carries the **same three prices a second time**, in the `<option>`
labels of its plan `<select>` (`প্রিমিয়াম প্যাকেজ (৳৪০,০০০ / বাৎসরিক)` and so
on). That was true before the form was wired; the wiring did not add it. It
does mean there are now two places to edit rather than one, and they must agree
with each other or the pricing page and the contact form quote different
amounts for the same package on the same site. `Sadmin_subscriptions` writes
`subscriptions.end_date` and `ins_titute.package_key`; it has no price column to
write, which is the reason the drift exists at all.

### 7.3 The helpdesk sends no email

Nothing in the panel sends mail. A ticket filed from the public form produces
no notification, so the backlog has to be cleared by opening the page. A
ticket with no email address on it cannot be answered at all. Both are
stated on the support page for the same reason as the rest of this section.

### 7.4 `vLogoPath` / `vFaviconPath` shipped pointing at nothing

The row held `assets/images/logo.png` and `assets/images/favicon.png` — two
slashes, so `Tenant_media::is_path()` rejects them, and `media_url()` falls
through to its legacy-base64 branch and emits
`src="data:image/png;base64,assets/images/logo.png"`: a broken image behind
a src that looks correct in the markup. The file does not exist in the
repository.

The panel shows the raw string plus a warning rather than an `<img>` that
cannot load, and `api_upload_asset()` is what puts a real value in the
column. SVG is refused — it can carry script, and nothing in `uploads/` stops
PHP from running.

### 7.5 The `?tenant=N` switch is a development-only override

`switch_tenant()` uses a `Tenant_resolver` override that only exists on dev
hosts. **In production it lands on the fallback tenant** (5, বাঘমারা) and
shows that parishad's data. It is a convenience for development, not
impersonation, and it must not be described as the latter.

### 7.6 The legacy `satting_infos` columns are inert

`nid_search_activity` is the only one read anywhere
(`back/entry_data_form.php:27`). Every other `nid_*` / `sonod_create_rates`
column is read by nothing — verified by grep, and the settings page says so on
each field rather than presenting them as live settings.

### 7.7 Bengali text exists in two code point forms, so search misses

**This one is not drift in the normal sense — it is a live bug in every search
box in the application, and in the login query. It is fixed in the panel's
search only.**

Bengali য় is one visible letter with two code point sequences: the
precomposed **U+09DF**, and the pair **U+09AF U+09BC**. Which one you get
depends on the input method and on what copied the text. Both render
identically. Under a deterministic collation Postgres compares bytes, so they
are different strings, and neither `LIKE` nor `=` will match one against the
other. `UNICODE_NORMALIZE` is **not** enabled for this database, so it is not
a server setting that could be flipped.

**Do not call these "NFC" and "NFD" — in this Postgres they are the wrong way
round, and getting it backwards will produce a migration that changes nothing.**
Postgres 18.6's `normalize()` maps U+09DF **down** to U+09AF U+09BC and never
composes it back:

```sql
normalize(U&'\09AF\09BC', NFC) = U&'\09DF'      -- f
normalize(x, NFC)               = normalize(x, NFD)  -- t
normalize(U&'\09DF', NFC)       = normalize(U&'\09AF\09BC', NFC)  -- t
```

So the canonical form **here is the decomposed-looking pair**, and the `NFC` /
`NFD` arguments are interchangeable in this build. The code writes `NFC`
because that is the conventional spelling of the operation; do not "fix" it to
`NFD` expecting a difference.

Both forms are in this database, **in the same columns**:

| Column | U+09DF | U+09AF U+09BC |
|---|---|---|
| `users.username` | 2 | 0 |
| `users.first_name` | 2 | 0 |
| `ins_titute.ins_name` | 4 | **1** |
| `ins_titute.ins_title_ss` | 3 | **1** |
| `ins_titute.cm_name_bn` | 2 | 0 |
| `support_tickets.user_name` | 0 | **1** |
| `support_tickets.message` | 0 | **1** |
| `customer_full_info.user_person_name` | 3 | 0 |

The visible proof, on `/sadmin_users` before the fix:

```
?q=বাঘমারা     -> 1 row     (no য় in it)
?q=চেয়ারম্যান   -> 0 rows    (typed U+09AF U+09BC, stored is U+09DF)
```

`চেয়ারম্যান বাঘমারা` is a chairman's own `username` and the panel could not
find it by its name.

**What was done, and why only that.** The two panel pages that have a text
search compare `normalize(col, NFC) LIKE normalize(needle, NFC)` — both sides
canonicalised in the query, so the search works whichever form each side
arrived in, and **not one row of data changes**:

- `/sadmin_users` — verified: `?q=চেয়ারম্যান` now returns both chairmen
  (was 0) and `?q=চেয়ারম্যান বাঘমারা` finds the exact stored value. Every
  ASCII and Bangla search that worked before still works.
- `/sadmin_support` — verified across all five searched columns.

`/sadmin_tenants` and `/sadmin_subscriptions` are not in the list because
**neither page has a text search box at all**; they filter on ids, status and
enums only. There was nothing to fix there, which is worth knowing rather than
assuming.

The `LIKE` is hand-built rather than using `$this->db->like()`, because
`like()` can neither wrap the field in a function call nor normalise the
needle. `escape_like_str()` then `escape()` is CI3's own escaping and the
explicit `ESCAPE '!'` is the clause `like()` would have appended — verified
that `?q=100%`, `?q=_` and `?q=!` are literal, not wildcards.

A data migration (`UPDATE … SET col = normalize(col, NFC)`) was **not** run. It
would fix today's rows and not tomorrow's, because the next keystroke can be
either form again. The durable fix is to canonicalise on **write** as well as
on read, at every entry point that accepts Bangla, and that is a decision about
the whole application rather than about the panel.

#### 7.7.1 The login query has the same bug, and it is worse there

`Ion_auth_model::login()` looks the user up with plain equality:

```php
->where($this->identity_column, $identity)
->or_where('email', $identity)
->or_where('username', $identity)
```

`email` is ASCII, so that branch is safe. **`username` is not.** Three of the
six accounts have a Bengali `username`, and **two of those three contain
য়** — users 9 (`চেয়ারম্যান বাঘমারা`) and 11 (`চেয়ারম্যান রূপসা`), both stored
as U+09DF. Neither has ever logged in, so it has not bitten yet, and neither
of them can log in by typing their own name with a keyboard that produces the
other form. That is a locked-out account, not a search that comes up empty.

**Not changed.** Login is the most sensitive query in the application, and
widening it is the owner's decision, not a drive-by fix. The options are
(a) normalise the `username` column and normalise `$identity` on the way in,
(b) compare with `normalize(username, NFC) = normalize(?, NFC)`, which kills
any index on the column, or (c) leave it and rely on the fact that these three
accounts also have an ASCII `email` to log in with — which is what actually
happens today, and is why this is latent rather than broken.

---

## 8. Verifying it

There are no automated tests. Verification is `curl`, and the three failure
shapes have to be told apart (see AGENTS.md):

```bash
J=/tmp/cj.txt
curl -s -D - -o /dev/null -c $J -H "Host: dhum.local" \
  -d "identity=admin@admin.com" -d "password=password" \
  http://localhost:8080/login_check | grep -i '^Refresh:'

curl -s -b $J -c $J -H "Host: dhum.local" -o /dev/null -w "%{http_code}\n" \
  http://localhost:8080/sadmin_audit
```

**A 500 with a 0-byte body is an uncaught Throwable, and there will be
nothing in `application/logs/`** because `log_threshold = 0`. A panel 500 is
almost always a missing `require_sadmin()`, a missing `template` in
`autoload['libraries']`, or a query naming an alias a join never declared.

Filter the audit page rather than trusting a screenshot — every filter is a
URL:

```
/sadmin_audit?action=delete
/sadmin_audit?entity=platform_setting
/sadmin_audit?tenant_id=platform
/sadmin_audit?q=logo
/sadmin_audit?from=2026-09-30&to=2026-09-30
```

`tenant_id=platform` is a UI sentinel for `tenant_id IS NULL`, not a column
value.

### 8.1 The auth check, which is the one worth repeating

Six of the seven panel pages prove the panel works. None of them would have
caught the backdoor in 6.5, because it was not in the panel. This is the check
that catches it, and it costs two seconds:

```bash
for pw in password admin 841998; do
  rm -f /tmp/jw.txt
  printf '  %-10s %s\n' "$pw" "$(curl -s -D - -o /dev/null -c /tmp/jw.txt \
    -H 'Host: dhum.local' --data-urlencode 'identity=admin@admin.com' \
    --data-urlencode "password=$pw" http://localhost:8080/login_check \
    | grep -ci '^Refresh:0;url=.*dashboard' | sed 's/1/LOGGED IN/;s/^0$/refused/')"
done
```

Only `password` may answer `LOGGED IN`. Run it **after any edit to
`Ion_auth_model.php`, `Auth.php` or `Front::api_login()`** — and note that a
panel page rendering perfectly is not evidence about any of the three.

Note the asymmetry that made this easy to get wrong: a *failed* login is
written to `login_attempts` by `increase_login_attempts()` and a *successful*
one deletes those rows via `clear_login_attempts()`. So the row count goes up
and down on its own, and neither movement tells you whether the credential
check is sound. Only the redirect does.

---

## 9. Schema

Two additive migrations, both applied. Neither drops or renames anything.

| File | Adds |
|---|---|
| `migrations/20260930_sadmin_panel.sql` | `audit_logs`, `packages`, `subscriptions`, `subscription_payments`, `support_tickets`, `support_replies`, `tenant_domains.is_verified`, per-tenant social columns on `ins_titute` |
| `migrations/20260930_sadmin_panel_2.sql` | `tenant_payment_history` |

Backup taken before either: `db_dumps/20260930_pre_sadmin_panel.dump`.

`subscription_payments` has `ON DELETE CASCADE` from `subscriptions`, so
cancelling a subscription **copies its payments to `tenant_payment_history`
first** and then deletes the row. The history table exists because that
cascade would otherwise take the money with it.

`support_tickets.tenant_id` is a **nullable** FK with `ON DELETE SET NULL`,
and `ins_name` may legitimately be empty. The views print
"প্রোডাক্ট সাইট / অজানা" rather than a blank.

`support_replies.sender_role` has a CHECK of `'staff' | 'customer'`. **Branch
on the column, not on `sender_id`** — a customer reply has a `sender_id` and
a staff reply may not.

---

## 10. If you are extending this

- Use `sadmin_write()`, never a bare `$this->db->update()`. It is what makes
  the audit row and the write share one code path.
- Pass `'tenant_id' => NULL` explicitly on any platform-level write. See 5.3.
- Add a field to the controller's `*_specs()` and the view picks it up. The
  whitelist and the markup are one list on purpose — a field that renders in
  the panel and is then dropped by the endpoint reads as "saving does
  nothing".
- Render any image column through `media_url()`. It never returns `''`.
- Take a `pg_dump` first. There is no git.
