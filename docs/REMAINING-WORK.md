# Remaining Work — 2026-09-29

> **Phase 0 is done except 0.2.** 0.1 and 0.3 landed 2026-09-29; their check
> results are in the "Done" section at the bottom. Everything below is the plan
> as written, with the completed steps marked.

Written after the tenant-isolation work (guard, IDOR, login split) and the
six-page public conversion. What follows is what was **left**, in the order I
would do it, with the reason and the check for each.

Facts behind this plan, all verified 2026-09-29:

| Fact | How it was established |
|---|---|
| `p/` shell is Tailwind; `back/` (28) + `sonod/` (4) are still MDK | `inc/assets/css/app.css` reloads behind `$ht_shell`; see the MDK bridge section of AGENTS.md |
| Every MDK rule is `[dir=ltr]`-scoped (4,685 of them) | so the bridge also needs `p/mdk_open.php` |
| Font Awesome: **0** classes on public pages, **2** in the admin | `grep -roE '\bfa[srlbd]? fa-[a-z-]+'` |
| `app.css` ships **no** `.fa` rule and **no** `@font-face` | so the admin's 2 FA icons already have no stylesheet at all |
| `vendor-fontawesome-free.css` is 1,037 KB and is linked only from `front/partials/header.php` | every public page pays it; the admin never did |
| `ins_title_ss` for tenant 1 is `স্মার্ট বাংলাদেশের,  স্মার্ট ইউনিয়ন` | a comma-separated list in a field every template renders as one line |
| `inc/assets/css/print.css` does not exist | the paper tier was never started |
| 8 `template->back()` calls name a view that does not exist | **none** of them is linked from the sidebar; all 19 sidebar links return 200 |
| `back/dashboard.php` (988 lines, 44 MDK icons) is an orphan | archived 2026-09-29 to `/home/darkness/vireta-private/backups/20260929_orphan_views/` |

---

## Phase 0 — quick wins · low risk · do these first

### 0.1 Drop Font Awesome from the public header — **1.04 MB off every public page**
`front/partials/header.php` links `vendor-fontawesome-free.css`. Nothing on a
public page uses it: the portal is on Phosphor. Verified 0 `fa-` classes across
`front/` and `saas/`.

Delete the `<link>`. Nothing to replace. **Check:** page still renders, and
`grep -c vendor-fontawesome` is 0 on `/`, `/search_hold`, `/add`, `/login`,
`/pub_view/*`.

Do **not** delete the file itself — see 1.2, which removes its last would-be user.

### 0.2 Fix `ins_title_ss` for tenant 1 — a data bug, not a code bug
`স্মার্ট বাংলাদেশের,  স্মার্ট ইউনিয়ন` renders as the subtitle on the login page,
the navbar, the footer and the `<meta name="description">`. Tenants 4, 5 and 10
already hold a proper single line, so this is one row, not a schema change.
**Needs your wording** — I will not invent a tagline.

### 0.3 Delete the 8 dead view calls
Each names a view file that does not exist. None is reachable from the menu.
Two of them are already commented out.

| Route | Wants | In method |
|---|---|---|
| `/report_view` | `back/reports_view_show.php` | `reports_view_page_show()` |
| `/viewdata` | `showData.php` | `show_famaily_data()` |
| `/test` | `test.php` | `test()` |
| `/searchInvoicess` | `back/invoice_search_view_file.php` | `search_collecting_invoice_invoice()` |
| `/collect_success` | `success_invoice.php` | `collect_successFul()` |
| `/dashboard/profile` | `my_profile.php` | `profile()` |
| `/dashboard/taxEntryViews` | `back/taxEntryView.php` | `taxEntryViews()` |
| `/dashboard/family_dataset_form?id=N` | `family_data_form.php` | `family_dataset_form()` |

The eighth is the one that hides: with no `id` it redirects before the view
loads, so it looks fine until someone passes a real one.

`/report_view` is the only one with a plausible rename — `back/reporting_view_page.php`
exists and is already used by `report_method()`. **Verify with a real `?id=`
before adopting it; do not assume.** `Dashboard::profile()` is a dead twin of
`user_profile_view()`, which is what `/my_profile` actually routes to and which
works.

**Check:** the 19 sidebar links all still 200; the eight URLs either 404 or
redirect to somewhere real, and none 500s.

---

## Phase 1 — security · one needs your decision

### 1.1 `Download` has no auth gate
`Download::__construct()` has no `logged_in()` redirect at all — it sets the user
if there is one and otherwise carries on. So `recipt_print`, `fullPlateWard`,
`psonod` and `nsonod` are reachable **anonymously**.

Checked 2026-09-29 with a real `collect_amnt_invoice_idd`: they return an empty
form, no tenant rows. So this is a latent risk, not a live leak.

`getSonodByUniqID()`, `get_dead_person()` and `get_all_warish_gon_person()` are
reached from here by uniq id. A citizen holding a sonod from one parishad has a
legitimate reason to read it from another — that is why they are unscoped. But
**death-registration detail is more sensitive than a certificate image**, and
`warish_somod_person` has no tenant column to scope it with even if we wanted to.

**This is a product question, not a bug fix, so I have not guessed.** Two options:
gate the whole controller, or gate only the two death-registration paths.

### 1.2 The admin's 2 Font Awesome icons
`back/profile_info.php` lines 53 and 127 use `fa fa-check` and
`fa fa-plus-circle`. They have **no stylesheet** — `app.css` ships no `.fa` rule
and the admin shell never linked `vendor-fontawesome-free.css`. They have been
rendering as empty boxes. Convert to `ph-check` / `ph-plus-circle`; the admin
shell already loads Phosphor.

---

## Phase 2 — `sonod/` · 4 views · self-contained

Do these first among the MDK work because they are few, they have one caller
(`Sonod` controller, already behind `enforce_tenant_scope()`), and every
conversion shrinks the temporary bridge.

| View | Lines | Notes |
|---|---|---|
| `sonod/create_new_sonods_view_file.php` | 932 | the big one — 236 MDK hits, select2, TinyMCE, flatpickr |
| `sonod/edit_sonod_view_file.php` | 118 | TinyMCE, sweetalert2 |
| `sonod/add_sonod_self_ins_view_file.php` | 100 | CKEditor, sweetalert2 |
| `sonod/index.php` | 154 | sweetalert2, TinyMCE |

Keep the JS contracts: `sonodM->get_sonod_by_todat()`, the select2 and TinyMCE
initialisers, and every `id` the sonod scripts bind to. `select2.min.css` and
`flatpickr` are the two third-party widgets with no Phosphor equivalent.

---

## Phase 3 — `back/` · 28 views · the big one

Ordered by size of win, not by file size, so the admin looks coherent early.

| Group | Views | Why first |
|---|---|---|
| **3a** dashboards | `fdash`, `dashboard_u` | what an admin sees on login |
| **3b** read-only lists | `house_views`, `view_full_data`, `view_full_data_doc`, `single_view`, `search_holding_info_view`, `all_deposite_list_view_page`, `reporting_view_page`, `up_members_view_files`, `employee_view_file`, `user_view` | mechanical, low risk, no JS beyond toastr |
| **3c** forms | `entry_data_form`, `system_sets`, `plates_system_sets`, `profile_info`, `change_cm_sign`, `slider_management_view_file` | shared field styling; do them together so the pattern lands once |
| **3d** money | `tax_collection`, `show_receiptss`, `deposite_user_cash_view_file`, `data_entry_success` | sweetalert2 + flatpickr; the highest-risk JS |
| **3e** leftovers | `view_profile`, `all_collect_reports`, `users_all_paid_reports`, `collected_historydate_viewFile`, `paid_infodate_viewFile`, `print_slip` | small |

Per view: replace MDK classes with `.ht-card` / `.ht-field` / `.ht-label` /
`.ht-btn`, `<i class="material-icons">` with `<i class="ph ph-*">`, and drop
`!important`-dependent assumptions. Keep every form `name`, every `id`, and every
AJAX hook.

**When the last one lands**, delete the bridge: the `$ht_shell` block in
`p/h.php`, both `p/mdk_*.php`, their two `load->view()` calls in
`Template::back()` / `::sonod()`, and `inc/assets/css/app.css` from the repo.

---

## Phase 4 — the paper tier

`download/` has 7 views (`print_recite_view_files`, `sonod_with_pad_view_file`,
`warish_sonod_view_file`, `nagorik_sonod_view_file`, `plate_view_by_ward_no`,
`single_plate_view`, `singlae_pview`) and no `print.css`.

**Deliberately no screen theme.** A holding certificate is a legal document:
soft shadows and light tints disappear on paper and generous radii print as
smudges. `print.css` gets `@page`, print colour settings and the fixed boxes these
documents already rely on. `inc/recept_assets/` is a separate legacy tree used
only by `print_recite_view_files` — leave it.

---

## Phase 5 — docs

- `docs/REDESIGN-SCOPE.md` is the progress ledger and is now behind: C8/C8a are
  done, `C3a`/`C8` (print tier) are not, and the admin is mid-conversion.
- Record the two owner decisions this plan needs: the `Download` gate, and the
  `ins_title_ss` wording.
- Keep `AGENTS.md`'s MDK-bridge section until Phase 3 finishes — it is the only
  record of why `app.css` and `<div dir="ltr">` are both needed.

---

## Done — Phase 0.1 and 0.3, 2026-09-29

### 0.1 Font Awesome unlinked from the public header ✅
`front/partials/header.php` no longer links `vendor-fontawesome-free.css`.
The file itself is still on disk — see 1.2, which removes its last would-be user.

Checked: `/`, `/search_hold`, `/add`, `/login`, `/up`, `/pub_view/*` and the
product site all return 200 with **zero** `vendor-fontawesome` references, and
`/` went from 88,772 to **88,645** bytes. **1,037 KB (204 KB gzipped) no longer
requested by any public page.** Owl Carousel, Phosphor and Hind Siliguri are
untouched — `footer.php` still calls `.owlCarousel()` twice, so the JS and CSS
must stay.

### 0.3 Eleven dead methods and eight routes removed ✅

`resources/build/audit_view_loads.py` is the tool for this and is worth keeping:
it resolves every `$this->load->view()` and `template->back()/sonod()/front()/saas()`
in all controllers against the view tree, and it is what found four of the
eleven — the first manual pass only looked at `template->back()` and so missed
every `$this->load->view()` target.

Removed from `Dashboard.php`: `reports_view_page_show`, `show_famaily_data`,
`test`, `search_collecting_invoice_invoice`, `profile`, `taxEntryViews`,
`collection_report_by_datetodate`, `collect_report_by_datetodate_nd_user`,
`user_account_setting_ss`, `collect_successFul`, `family_dataset_form`.
Removed routes: `viewdata`, `report_view`, `show_collect_details`,
`collect_detailsbyUser`, `collect_success`, `searchInvoicess`, `test`,
`update_acc_set`. Deleted `application/controllers/Welcome.php` — the untouched
CI3 installer scaffold whose docblock still calls itself the default controller,
loading a `master/backend/` directory this project never had.

**`/report_view` was not renamed to `back/reporting_view_page.php`.** That was my
first hypothesis and it is wrong: `reporting_view_page.php` reads `$users_info`,
which `report_method()` supplies, whereas `reports_view_page_show()` set
`$get_users`. A rename would have rendered the page with an undefined variable —
a quieter failure than a 500, which is worse.

**Two removed methods carried working logic, and it went with them:**
- `collect_successFul()` — voucher lookup (`get_paidAmntByVocherNo`,
  `get_invoiceByVocherNo`) for a payment-success receipt
- `family_dataset_form()` — the data-sheet edit form keyed on `?id=`

Neither had a caller, and neither had a view. Say so if either should come back
and the view gets rebuilt rather than guessed at.

Checked: **all 19 sidebar links return 200**, all twelve retired URLs now answer
CI3's **404** — never a 500 — and the audit reports `61 total, 61 resolve, 0 do
not`. `php -l` clean on both edited files.

### 0.2 Still open — needs your wording
`ins_titute.ins_title_ss` for tenant 1 is `স্মার্ট বাংলাদেশের,  স্মার্ট ইউনিয়ন`.
It renders as the subtitle on the login page, the navbar, the footer and the
`<meta name="description">`. Tenants 4, 5 and 10 already hold a proper single
line, so this is one row and no schema change. **I will not invent a tagline.**
