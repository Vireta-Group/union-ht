# Media Storage — B6a

**Status: complete, verified 2026-09-28.**

Tenant branding and slider images moved out of the database and into
per-tenant files. **Pixels are unchanged** — the decoded bytes were written
unmodified, no resize and no re-encode. Only the delivery changed.

---

## 1. What moved

Six columns, all of which belong to the tenant rather than to a record:

| Table | Column | Was | Now |
|---|---|---|---|
| `ins_titute` | `ins_logo_path` | 2,829,708 chars of base64 | `tenant-1/logo.png` |
| `ins_titute` | `ins_en_logo_path` | empty | — |
| `ins_titute` | `cm_pic_base64` | 333,548 chars | `tenant-1/chairman.png` |
| `ins_titute` | `cm_sign_s64` | 259,560 chars | `tenant-1/signature.png` |
| `ins_titute` | `socib_pic_base64s` | empty | — |
| `home_slider` | `sliderbase64` | 324 KB + 374 KB | `tenant-1/slider-2.jpg`, `slider-3.jpg` |

2.96 MB of base64 became 5 files, 2.96 MB on disk.

No schema change. No column rename. The columns were already `text` and now
hold a short path. `ins_logo_path` is literally named "path"; the others keep
a name that mentions base64, because renaming the schema is out of scope.

---

## 2. Storage layout

```
uploads/
├── .htaccess              deny rule — nothing here ever executes
├── index.html             stops directory listing
└── tenant-<ins_titute_p_idds>/
    ├── logo.png
    ├── logo-en.png
    ├── chairman.png
    ├── signature.png
    ├── secretary.png
    └── slider-<uniqid>.jpg
```

Per-tenant directories are a necessity, not tidiness: without them, tenant
2's logo overwrites tenant 1's the moment both are configured.

`uploads/` sits in the webroot because the images have to be publicly
servable, and it has its own deny rule so a future upload bug cannot become
remote code execution. `router.php` mirrors the same rule, because the PHP
built-in server ignores `.htaccess`.

---

## 3. Pixel identity — verified, not assumed

| File | Bytes | md5 (first 16) | Type |
|---|---|---|---|
| `tenant-1/logo.png` | 2,122,281 | `9aef6145f1791e0a` | PNG 1603 × 1500 |
| `tenant-1/chairman.png` | 250,161 | `c7891f5fd72123aa` | PNG 581 × 882 |
| `tenant-1/signature.png` | 194,668 | `0d8db825699a8bbf` | PNG 1500 × 921 |
| `tenant-1/slider-2.jpg` | 249,579 | `dd31f9fe6fcd81bb` | JPEG |
| `tenant-1/slider-3.jpg` | 287,970 | `31feb1a158197ba1` | JPEG |

The pre-migration logo column held 2,829,708 base64 characters.
2,829,708 × 3/4 = 2,122,281 bytes — exactly the file on disk. The migration
also md5-compares the written file against the decoded bytes before touching
the database, so a row can never point at a file holding different bytes.

## 4. Page weight

```
before   6.45 MB   99.2% of the response was base64 image data
after    0.05 MB   59 KB of HTML
```

The images themselves still total ~2.96 MB, but they are now separate
cacheable responses instead of part of every page. The logo was previously
inlined **twice** per page — once as a 1603×1500 favicon — so a 6.45 MB
download happened on *every* page view and could never be cached.

Resizing was deliberately **not** done; that is a separate decision and the
user asked for pixels as-is. The logo is a 1603×1500 photograph-derived PNG
(0.883 bytes/pixel — far too heavy for flat logo art) shown at navbar size.
Generating a 300×300 variant later would cut ~95% with no visible change.

---

## 5. The compatibility contract

`media_url()` accepts all three shapes, so a partially-migrated database
still renders:

| Stored value | Result |
|---|---|
| `tenant-1/logo.png` | `http://host/uploads/tenant-1/logo.png` |
| `iVBORw0KGgo...` (bare base64) | `data:image/png;base64,iVBORw0KGgo...` |
| `data:image/png;base64,...` | returned untouched |
| `''` / `NULL` | 1×1 transparent GIF |
| a path whose file is gone | 1×1 transparent GIF |

`url()` never returns an empty string: the browser would treat that as a
request for the current page and re-download the HTML.

All five cases are asserted in `/t_diag` under **media_url() self-test**, so
this is checked rather than hoped for.

### Call sites

**46 call sites across 25 view files** were converted:

```php
// before
src="data:image/png;base64,<?= $setting->ins_logo_path; ?>"
src="data:image/png;base64, <?= $ins_info->cm_sign_s64; ?>"          // with a space
src="data:image/png;base64,${r[l].sliderbase64}"                      // JS-style lookup
// after
src="<?= media_url($setting->ins_logo_path, 'image/png'); ?>"
src="<?= media_url($ins_info->cm_sign_s64, 'image/png'); ?>"
src="${media_url($r[l]->sliderbase64, 'image/jpeg')}"
```

### Deliberately left as inline base64

Seven `data:` URIs remain in the codebase and are correct:

| Location | Variable | Why |
|---|---|---|
| `download/single_plate_view.php:29` | `$qr_code` | barcode generated at render time |
| `download/plate_view_by_ward_no.php:46` | `$qr_code` | " |
| `download/warish_sonod_view_file.php:298,415` | `$bar_code`, `$qr_code` | " |
| `download/print_recite_view_files.php:86` | `$qr_code` | " |
| `ex/back/view_profile.php:70`, `ex/partial/header.php:425` | `$loggedUser['userAvatar']` | Ion Auth demo, `$loggedUser` is never set in this app |

Barcodes are produced in PHP during the request and have no database column
and no tenant, so there is nothing to move. `$loggedUser` is a leftover
Metronic demo array that has always rendered as a broken image.

---

## 6. Writers

Every `base64_encode(file_get_contents($_FILES[...]))` for these six columns
was replaced with `media_save()`:

| Method | Field | Slug |
|---|---|---|
| `Dashboard::update_system_setting` | `union_logo_bn` | `logo` |
| `Dashboard::update_system_setting` | `union_logo_eng` | `logo-en` |
| `Dashboard::update_chairMan_pic_tures` | `pics` | `chairman` |
| `Dashboard::update_chairMan_signs` | `sign` | `signature` |
| `Dashboard::insert_slider_manage` | `pics` | `slider-<uniqid>` |

`update_system_setting` had four near-identical branches that differed only
in which image key was set. They were collapsed into one flow: build the text
payload once, add an image key only when the upload actually succeeded. A
rejected upload leaves the existing logo intact — the old code would have
written an empty value and blanked it.

### Upload validation

The old code accepted any file type whatsoever — no extension check, no MIME
check, no size check. Writing to disk would have turned that into an RCE
path, so `save_upload()` now:

- rejects anything that is not a real upload (`is_uploaded_file`)
- sniffs the type with `getimagesize()`, which parses the file header
- derives the extension from the sniffed type, never the client's filename
- allows only PNG / JPEG / GIF / WebP / BMP — **SVG is deliberately excluded**
  because an SVG is a script container
- writes a developer-supplied slug, sanitised, with no path traversal

A PHP script renamed `logo.png` is rejected rather than stored.

---

## 7. Not done — record images (B6b)

Seven more image columns belong to a *record*, not a tenant:

```
family_data_sheet.profile_image / home_image
family_member.profile_image
up_member_s.up_mem_pic_base64s
employee_data_ins.emp_pic_base64
customer_full_info.user_img_base64
person_verify_nid_data_info_saving.verify_person_pic_base64
```

Their three writers in `Dashboard.php` (`emp_pic_base64`, `up_mem_pic_base64s`
×2) still use the old unvalidated `base64_encode(file_get_contents(...))`.
That is unchanged from before B6a and remains unvalidated.

Two things make this cheap when it is picked up:

1. **there is almost no data** — every one of these columns is empty except
   `employee_data_ins.emp_pic_base64`, which has 1 non-empty row out of 2.
   A migration would be a handful of rows, not 5,935.
2. `media_url()` already renders both shapes, so records can be migrated one
   at a time with no code change.

The unvalidated writers are the part still worth fixing first — they are
independent of where the bytes end up.

---

## 8. Rollback

```
/home/darkness/vireta-private/
├── db_dumps/pre_b6a_media_20260928_002122.dump    ins_titute + home_slider
├── migrations/b6a_media_to_files.php               re-runnable, dry run by default
├── removed_files/pre_b6a_views/                   22 view files
└── removed_files/pre_b6a_Dashboard.php.bak
```

Restoring the dump puts the base64 back. The `media_url()` code is
backward compatible in both directions, so the views do not need reverting
even if the data is.

Run the migration with no arguments to preview; nothing is written until
`--apply` is passed.
