# Design System — Warm Indigo + Terracotta

**Status: tokens, pipeline and component layer built 2026-09-28. Home page not
yet rebuilt.**

---

## 1. The shape of the problem

There is no master layout. `Template.php` assembles every page by
concatenating views:

```
p/h  (head + header — shared by front, back and sonod)
p/n  (admin sidebar — back and sonod only)
<content view>
p/f  (footer)
```

So the visible surface is far smaller than the file count suggests:

| Area | Files | Real lines | Note |
|---|---|---|---|
| `ex/` | 65 | 22,403 | Ion Auth demo, **dead** — never rendered |
| `p/` shell | 5 | 618 | `h` header, `n` sidebar, `f` footer |
| `back/` | 28 | — | admin content, Bootstrap-style class names |
| `front/` | 7 | — | public content |
| `download/` | 7 | — | **paper** — certificates, receipts |
| `sonod/` | 4 | — | public notices |
| `api/` | 3 | — | JSON + login |
| `errors/` | 10 | — | CodeIgniter built-ins |

128 views exist; 64 are live. The rest of this document assumes the live set.

---

## 2. Three tiers, three treatments

| Tier | Views | Treatment | Why |
|---|---|---|---|
| **Public** | `front/`, `sonod/`, `api/` | **Tailwind**, built from scratch | the shopfront; worth doing properly |
| **Admin** | `back/` + `p/h` `p/n` `p/f` | **Token overlay** on Material Design Kit | see below |
| **Paper** | `download/`, printable `p/` | **Print-first CSS**, no screen theme | see below |

### Why the admin is an overlay and not a rewrite

`inc/assets/css/app.css` is Material Design Kit: **16,049 lines, 3.1 MB,
1,533 hardcoded hex values.** MDK's own JavaScript, plus flatpickr, jQuery UI
and select2, are all styled by that file. Replacing it is a rewrite of the
admin, not a retheme of it.

What makes the overlay viable is that MDK exposes its palette as custom
properties (`app.css` lines 10–45). Redeclaring about fifteen of them on
`:root` after `app.css` has loaded recolours the sidebar, navbar, cards,
buttons, badges, tables, form controls and focus states — with no markup
change and no plugin breakage.

The mapping, with MDK's originals kept in comments so the substitution stays
auditable:

| MDK variable | was | now |
|---|---|---|
| `--primary` | `#6774DF` | `#4f46e5` |
| `--secondary` | `#6c757d` | `#5c5550` |
| `--info` | `#5dd2bc` | `#c2410c` (carries the warmth) |
| `--success` | `#7DC668` | `#047857` |
| `--warning` | `#F5B666` | `#b45309` |
| `--danger` | `#ff7076` | `#b91c1c` |
| `--light` | `#FAFBFE` | `#faf7f5` |
| `--dark` | `#1C2260` | `#1c1917` |
| `--gray` | `#6c757d` | `#736a63` |
| `--gray-dark` | `#343a40` | `#2e2a27` |
| `--dark-gray` | `#939FAD` | `#a8a099` |
| `--light-gray` | `#ECEEF0` | `#d6cfc8` |
| `--muted-light` | `#DBDFE4` | `#eae5e0` |
| `--font-family-sans-serif` | no Bengali face | Hind Siliguri stack |

Four of the status colours were darkened from MDK's originals specifically
because MDK's pale fills were too close to white to read as status, and
because the lighter values failed contrast once the background was warmed
from cool white to `#faf7f5`.

### Why the paper tier is excluded from the screen theme

`download/` and the printable `p/` views produce documents that are printed:
holding certificates, receipts, warish-nagad, tax plates. They currently use
fixed 350 px boxes, 1 px black borders, 150 px logos and `@page` rules.

A screen theme makes printed output worse. Soft shadows disappear, light
tints vanish on paper, and generous radii print as smudges. A land-holding
certificate is also a legal document, where legibility and a hard border are
the point, not a consideration.

They get print-first CSS instead. `inc/assets/css/print.css` — not yet
written.

---

## 3. Pipeline

```
resources/css/tokens.css      ← the single source of truth
        │
        ├── resources/css/portal.css      Tailwind entry, public pages
        │         @theme inline maps --brand-* → --color-* utilities
        │         ↓ npm run build:css
        │    inc/assets/css/portal.css
        │
        └── copied by npm run build:admin
              inc/assets/css/tokens.css  ← loaded by p/h.php

inc/assets/css/brand.css      ← admin overlay, hand-written, loaded after app.css
```

```bash
npm install
npm run build     # both
npm run watch     # Tailwind only, rebuilds on change
```

Tailwind v4.3.3, config-free — `@theme inline` in CSS replaces `tailwind.config.js`.

### One source of truth, and why it needed fixing first

`tokens.css` declares **everything** under the `--brand-*` namespace.
`portal.css` maps those onto Tailwind's own `--color-*` names. The two
namespaces are kept disjoint on purpose.

The first attempt did not do this, and produced

```css
--color-primary: var(--color-primary);   /* in :root, from @theme */
--color-primary: var(--brand-indigo-600); /* in :root, from tokens.css */
```

Two declarations of the same name in the same scope: the last one wins, the
first resolves to itself and is invalid at computed-value time, and every
`bg-primary` silently renders nothing. Splitting the namespaces removes the
possibility rather than relying on load order.

### Collision already present in the codebase

`front/` was loading **Bootstrap 5.3.2 from a CDN alongside Bootstrap 4.6
locally** in different view files. That collision is why the public side gets
its own entry point rather than sharing the admin stylesheet.

---

## 4. Colour

Full ramps are in `resources/css/tokens.css`. Summary:

| Role | Value | Ramp |
|---|---|---|
| primary | `#4f46e5` | indigo 600 |
| accent | `#c2410c` | terracotta 600 |
| background | `#ffffff` | neutral 0 |
| surface | `#faf7f5` | warm off-white |
| text | `#1c1917` | neutral 900 |
| border | `#eae5e0` / `#cec6be` | hairline / input |

**Neutral ramp is warm, not slate.** A cool grey under warm indigo and
terracotta reads as muddy.

### Contrast — 17 pairs, all verified

Every foreground/background pair clears WCAG AA. Three were wrong on the first
pass and were corrected rather than shipped:

| Pair | was | now | why it matters |
|---|---|---|---|
| muted text on `#faf7f5` | 4.30:1 ✗ | 4.96:1 | warming the background cost contrast |
| placeholder text | 2.41:1 ✗ | 4.96:1 | placeholder is text, not decoration |
| input border on `#faf7f5` | 1.45:1 ✗ | 1.58:1 | the border tells you where to click |

`--brand-neutral-400` (`#a8a099`, 2.41:1) is therefore **decorative only** and
must never carry text. That constraint is written into the token file.

---

## 5. Typography

Body stack leads with Bengali faces:

```css
"Hind Siliguri", "Noto Sans Bengali", "SolaimanLipi",
ui-sans-serif, system-ui, ...
```

The admin had **no Bengali face at all** — `--font-family-sans-serif` was
`-apple-system, BlinkMacSystemFont, "Segoe UI", ...`, so every label and data
cell fell back to whatever the browser supplied. Fixing this alone changes
the admin more than any colour did.

Line height is 1.75 for body copy. Bengali script has tall ascenders and deep
descenders; the browser default of 1.5 clips the matra on headings.

`.tabular` sets `font-variant-numeric: tabular-nums` for amount columns, so
digits do not shift as values change.

---

## 6. Components

Narrow and explicit, in `portal.css` under `@layer components` — no component
library:

`.ht-btn` + `-primary` `-accent` `-secondary` `-ghost`
`.ht-card`
`.ht-field` `.ht-label`
`.ht-badge` + `-primary` `-accent` `-success` `-warning` `-danger` `-neutral`

The public pages have a repeatable, narrow set of needs. Reaching for a
component library at this size would add more indirection than it removes.

---

## 7. Not done

- **Home page not rebuilt** — still the old Bootstrap 4.6 template
- **`print.css` not written** — paper tier unchanged
- **Admin overlay not visually verified** — it loads and all variables resolve,
  but the admin needs a login, which has not been done
- **No Bengali webfont bundled** — the stack names Hind Siliguri but the file
  is not in the repo, so this depends on the tenant's system having it
- **B4 per-tenant branding** — deferred; tokens are static for now
- **`views/ex/`** — 65 dead files, left untouched
