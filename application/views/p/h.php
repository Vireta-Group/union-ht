<!DOCTYPE html>
<html lang="bn" class="scroll-smooth">

<head>
    <base href="<?php echo base_url(); ?>">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= htmlspecialchars($ins_info->ins_name ?? 'ইউনিয়ন পরিষদ ড্যাশবোর্ড', ENT_QUOTES, 'UTF-8'); ?></title>

    <link rel="icon" type="image/x-icon" href="<?= media_url($ins_info->ins_logo_path ?? '', 'image/png') ?>" />

    <!-- Fonts: Adorsho Lipi (আদর্শ লিপি) + Hind Siliguri + Inter, hosted locally (no Google CDN) -->
    <link rel="stylesheet" href="inc/assets/vendor/fonts/adorsho-lipi.css">
    <link rel="stylesheet" href="inc/assets/vendor/fonts/hind-siliguri.css">
    <link rel="stylesheet" href="inc/assets/vendor/fonts/inter.css">

    <!-- Design Tokens & Tailwind Portal CSS -->
    <link rel="stylesheet" href="inc/assets/css/portal.css">

    <!-- Phosphor Icons -->
    <!-- Phosphor icons, hosted locally (no CDN). Regular + bold + fill, woff2 only. -->
    <link rel="stylesheet" type="text/css" href="inc/assets/vendor/phosphor/regular/style.css"/>
    <link rel="stylesheet" type="text/css" href="inc/assets/vendor/phosphor/bold/style.css"/>
    <link rel="stylesheet" type="text/css" href="inc/assets/vendor/phosphor/fill/style.css"/>

    <!-- jQuery UI CSS & Modern Datepicker Overlay -->
    <link type="text/css" href="inc/assets/vendor/jquery-ui.css" rel="stylesheet">
    <link type="text/css" href="inc/assets/css/custom-datepicker.css" rel="stylesheet">

    <!-- SweetAlert2 & Modern Alerts System -->
    <link rel="stylesheet" href="inc/assets/vendor/sweetalert2/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="inc/assets/css/modern-alerts.css">

    <!-- Custom Modern WebKit Scrollbars -->
    <link rel="stylesheet" href="inc/assets/css/custom-scrollbar.css">

    <!-- Modern Dimensional & Tactile Theme Layer -->
    <link rel="stylesheet" href="inc/assets/css/modern-theme.css">

    <?php
    /*
     * TEMPORARY BRIDGE — admin + sonod, while views/back (28) and views/sonod
     * (4) are converted from Material Design Kit to Tailwind one file at a
     * time. REMOVE THIS BLOCK when the last MDK view is converted.
     *
     * The shell (p/n, p/f) is already Tailwind; the content views are not.
     * With app.css unloaded those views rendered unstyled, and the 44
     * <i class="material-icons"> in back/dashboard.php drew nothing at all,
     * because the icon font ships as a separate stylesheet from app.css.
     *
     * This restores the STYLESHEETS AND ASSETS that MDK markup needs. It
     * deliberately does NOT restore MDK's JavaScript (material-design-kit.js,
     * bootstrap.min.js, popper.min.js, js/app.js) — none of those were loaded
     * by any view even before the conversion. Loading them would fight the new
     * shell: p/f.php drives its own dropdowns through toggleDropdown(), and the
     * shim below maps $.fn.modal onto window.htModal, so the real Bootstrap
     * bundle would install a second modal implementation on top of both. The
     * remaining views' JS needs are covered without them — flatpickr below,
     * and select2 / sweetalert2 / toastr load from their own views.
     *
     * Gated on $ht_shell, which Template::back() and ::sonod() set and
     * Template::front() does not, so no public page ever pays for the 3.1 MB.
     * This is a PHP comment rather than an HTML one on purpose: the file is
     * shared by all three shells, and an HTML comment here ships 20-odd lines
     * of prose into every admin page's markup.
     */
    if (!empty($ht_shell)): ?>
        <!-- MDK: .card .btn .form-control .table .row .col-md-* .badge .alert .dropdown -->
        <link rel="stylesheet" href="inc/assets/css/app.css">
        <!-- icon webfont — app.css names the family but ships no @font-face -->
        <link rel="stylesheet" href="inc/assets/css/vendor-material-icons.css">
        <!-- data-toggle="flatpickr" inputs: back/dashboard, back/profile_info, sonod/create_new -->
        <link rel="stylesheet" href="inc/assets/vendor/flatpickr/flatpickr.min.css">
        <link rel="stylesheet" href="inc/assets/vendor/flatpickr/airbnb.css">
        <!-- back/up_members + back/employee load toastr.min.js but never its .css -->
        <link rel="stylesheet" href="inc/assets/vendor/toastr.min.css">
    <?php endif; ?>

    <style>
        body,
        [dir=ltr] body,
        input, select, textarea, button, table, th, td, h1, h2, h3, h4, h5, h6, .card, .btn {
            font-family: 'AdarshaLipi', 'Adarsha Lipi', 'AdorshoLipi', 'Adorsho Lipi', 'Hind Siliguri', 'Inter', system-ui, sans-serif !important;
        }
        body,
        [dir=ltr] body {
            background-color: #f8fafc !important;
            background-image: 
                radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.055) 0px, transparent 50%),
                radial-gradient(at 100% 12%, rgba(249, 115, 22, 0.038) 0px, transparent 40%),
                radial-gradient(at 50% 100%, rgba(99, 102, 241, 0.045) 0px, transparent 55%) !important;
            background-attachment: fixed !important;
            color: #0f172a !important;
        }
        .bengali-num {
            font-feature-settings: "tnum";
            font-variant-numeric: tabular-nums;
        }
        /* MDK collision overrides: prevent .flex from growing uncontrollably */
        [dir=ltr] .flex {
            display: flex !important;
            flex-grow: 0;
            flex-shrink: 1;
            flex-basis: auto;
        }
        [dir=ltr] .flex-1,
        [dir=ltr] .grow {
            flex: 1 1 0% !important;
            flex-grow: 1 !important;
        }
        [dir=ltr] .shrink-0,
        [dir=ltr] .flex-shrink-0 {
            flex-shrink: 0 !important;
        }

        /* =====================================================================
         * Universal Form Controls & Inputs Normalization (Tailwind / Modern Theme)
         * ================================================================== */
        [dir=ltr] .form-control,
        [dir=ltr] input[type="text"],
        [dir=ltr] input[type="email"],
        [dir=ltr] input[type="password"],
        [dir=ltr] input[type="number"],
        [dir=ltr] input[type="tel"],
        [dir=ltr] input[type="url"],
        [dir=ltr] input[type="search"],
        [dir=ltr] textarea,
        [dir=ltr] select {
            background-color: #ffffff !important;
            border: 1px solid #cbd5e1 !important; /* slate-300 */
            border-radius: 0.75rem !important; /* rounded-xl (12px) */
            color: #0f172a !important; /* slate-900 */
            font-size: 0.8125rem !important; /* 13px */
            line-height: 1.25rem !important;
            padding: 0.5rem 0.85rem;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.03) !important;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out, background-color 0.15s ease-in-out !important;
            outline: none !important;
        }

        /* Ensure Tailwind padding utilities for input icons (pl-*, pr-*) are never crushed */
        [dir=ltr] input[class*="pl-7"], [dir=ltr] textarea[class*="pl-7"] { padding-left: 1.75rem !important; }
        [dir=ltr] input[class*="pl-8"], [dir=ltr] textarea[class*="pl-8"] { padding-left: 2rem !important; }
        [dir=ltr] input[class*="pl-9"], [dir=ltr] textarea[class*="pl-9"] { padding-left: 2.25rem !important; }
        [dir=ltr] input[class*="pl-10"], [dir=ltr] textarea[class*="pl-10"] { padding-left: 2.5rem !important; }
        [dir=ltr] input[class*="pl-11"], [dir=ltr] textarea[class*="pl-11"] { padding-left: 2.75rem !important; }
        [dir=ltr] input[class*="pl-12"], [dir=ltr] textarea[class*="pl-12"] { padding-left: 3rem !important; }
        [dir=ltr] input[class*="pr-7"], [dir=ltr] textarea[class*="pr-7"] { padding-right: 1.75rem !important; }
        [dir=ltr] input[class*="pr-8"], [dir=ltr] textarea[class*="pr-8"] { padding-right: 2rem !important; }
        [dir=ltr] input[class*="pr-9"], [dir=ltr] textarea[class*="pr-9"] { padding-right: 2.25rem !important; }
        [dir=ltr] input[class*="pr-10"], [dir=ltr] textarea[class*="pr-10"] { padding-right: 2.5rem !important; }
        [dir=ltr] input[class*="pr-11"], [dir=ltr] textarea[class*="pr-11"] { padding-right: 2.75rem !important; }
        [dir=ltr] input[class*="pr-12"], [dir=ltr] textarea[class*="pr-12"] { padding-right: 3rem !important; }

        /* Focus state with smooth Indigo glow */
        [dir=ltr] .form-control:focus,
        [dir=ltr] input[type="text"]:focus,
        [dir=ltr] input[type="email"]:focus,
        [dir=ltr] input[type="password"]:focus,
        [dir=ltr] input[type="number"]:focus,
        [dir=ltr] input[type="tel"]:focus,
        [dir=ltr] input[type="url"]:focus,
        [dir=ltr] input[type="search"]:focus,
        [dir=ltr] textarea:focus,
        [dir=ltr] select:focus {
            background-color: #ffffff !important;
            border-color: #6366f1 !important; /* indigo-500 */
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.18) !important;
            outline: none !important;
        }

        /* Placeholder */
        [dir=ltr] .form-control::placeholder,
        [dir=ltr] input::placeholder,
        [dir=ltr] textarea::placeholder {
            color: #94a3b8 !important; /* slate-400 */
            opacity: 1 !important;
        }

        /* Disabled / Readonly */
        [dir=ltr] .form-control:disabled,
        [dir=ltr] .form-control[readonly],
        [dir=ltr] input:disabled,
        [dir=ltr] input[readonly],
        [dir=ltr] textarea:disabled,
        [dir=ltr] textarea[readonly],
        [dir=ltr] select:disabled {
            background-color: #f1f5f9 !important; /* slate-100 */
            border-color: #e2e8f0 !important;
            color: #64748b !important;
            cursor: not-allowed !important;
            box-shadow: none !important;
        }

        /* Select Dropdowns with sleek SVG Chevron */
        [dir=ltr] select,
        [dir=ltr] .form-control select,
        [dir=ltr] select.form-control {
            appearance: none !important;
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke-width='2' stroke='%2364748b'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19.5 8.25l-7.5 7.5-7.5-7.5' /%3E%3C/svg%3E") !important;
            background-repeat: no-repeat !important;
            background-position: right 0.75rem center !important;
            background-size: 1rem 1rem !important;
            padding-right: 2.25rem !important;
            cursor: pointer !important;
        }

        /* Form Labels */
        [dir=ltr] label {
            font-size: 0.75rem !important; /* 12px */
            font-weight: 600 !important;
            color: #334155 !important; /* slate-700 */
            margin-bottom: 0.35rem !important;
            display: inline-block !important;
        }

        /* =====================================================================
         * Universal Button & Link Normalization (Kill Browser Focus Rings, Black Borders & Underlines)
         * ================================================================== */
        button,
        [type="button"],
        [type="reset"],
        [type="submit"] {
            border: 0;
            outline: none !important;
            -webkit-tap-highlight-color: transparent;
        }

        /* Kill the ugly black focus outline from app.css (-webkit-focus-ring-color) on all buttons & links */
        button:focus,
        button:focus-visible,
        button:active,
        [type="button"]:focus,
        [type="button"]:focus-visible,
        [type="button"]:active,
        [type="reset"]:focus,
        [type="reset"]:focus-visible,
        [type="reset"]:active,
        [type="submit"]:focus,
        [type="submit"]:focus-visible,
        [type="submit"]:active,
        .btn:focus,
        .btn:focus-visible,
        .btn:active,
        a:focus,
        a:focus-visible,
        a:active {
            outline: none !important;
            outline-style: none !important;
            outline-width: 0 !important;
            outline-color: transparent !important;
        }

        /* Kill ugly link underline / bottom border from MDK/Bootstrap on hover/focus */
        a,
        a:hover,
        a:focus,
        a:active,
        [dir=ltr] a,
        [dir=ltr] a:hover,
        [dir=ltr] a:focus,
        [dir=ltr] a:active {
            text-decoration: none !important;
        }

        /* Allow explicit underline only when explicitly requested via Tailwind class */
        a.underline,
        a[class*="underline"] {
            text-decoration: underline !important;
        }
        a.no-underline,
        a[class*="no-underline"] {
            text-decoration: none !important;
        }

        /* =====================================================================
         * Universal Admin Button Normalization — modern, sleek & proportional
         * ================================================================== */
        [dir=ltr] .btn,
        .admin-btn,
        button.btn,
        a.btn,
        input[type="submit"].btn,
        input[type="button"].btn {
            font-size: 0.8125rem !important; /* 13px */
            line-height: 1.25rem !important;
            padding: 0.45rem 1rem !important;
            border-radius: 0.75rem !important; /* rounded-xl (12px) */
            font-weight: 600 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 0.375rem !important;
            transition: all 0.15s ease-in-out !important;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
            cursor: pointer !important;
        }

        /* Compact action buttons in tables */
        table .btn,
        [dir=ltr] table .btn {
            font-size: 0.75rem !important;
            padding: 0.25rem 0.65rem !important;
            line-height: 1rem !important;
            border-radius: 0.5rem !important;
        }

        [dir=ltr] .btn:active,
        button.btn:active,
        a.btn:active {
            transform: scale(0.98) !important;
        }

        /* Primary Button */
        [dir=ltr] .btn-primary {
            background-color: #4f46e5 !important; /* indigo-600 */
            border-color: #4f46e5 !important;
            color: #ffffff !important;
        }
        [dir=ltr] .btn-primary:hover,
        [dir=ltr] .btn-primary:focus {
            background-color: #4338ca !important; /* indigo-700 */
            border-color: #4338ca !important;
            color: #ffffff !important;
            box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2), 0 2px 4px -2px rgba(79, 70, 229, 0.2) !important;
        }

        /* Secondary / White / Light Button */
        [dir=ltr] .btn-secondary,
        [dir=ltr] .btn-light,
        [dir=ltr] .btn-white {
            background-color: #ffffff !important;
            border: 1px solid #cbd5e1 !important;
            color: #334155 !important;
        }
        [dir=ltr] .btn-secondary:hover,
        [dir=ltr] .btn-light:hover,
        [dir=ltr] .btn-white:hover {
            background-color: #f8fafc !important;
            border-color: #94a3b8 !important;
            color: #0f172a !important;
        }

        /* Success Button */
        [dir=ltr] .btn-success {
            background-color: #059669 !important; /* emerald-600 */
            border-color: #059669 !important;
            color: #ffffff !important;
        }
        [dir=ltr] .btn-success:hover {
            background-color: #047857 !important; /* emerald-700 */
            border-color: #047857 !important;
            color: #ffffff !important;
        }

        /* Danger Button */
        [dir=ltr] .btn-danger {
            background-color: #e11d48 !important; /* rose-600 */
            border-color: #e11d48 !important;
            color: #ffffff !important;
        }
        [dir=ltr] .btn-danger:hover {
            background-color: #be123c !important; /* rose-700 */
            border-color: #be123c !important;
            color: #ffffff !important;
        }

        /* Small Button */
        [dir=ltr] .btn-sm,
        .btn-sm {
            font-size: 0.75rem !important; /* 12px */
            line-height: 1rem !important;
            padding: 0.25rem 0.65rem !important;
            border-radius: 0.5rem !important;
        }

        /* Large Button */
        [dir=ltr] .btn-lg,
        .btn-lg {
            font-size: 0.875rem !important; /* 14px */
            line-height: 1.25rem !important;
            padding: 0.6rem 1.25rem !important;
            border-radius: 0.875rem !important;
        }

        /* Ensure content sections have clean vertical spacing */
        [dir=ltr].space-y-6 > * + * {
            margin-top: 1.5rem !important;
        }

        /* =====================================================================
         * Tenant Sub-Navbar Flex Alignment & Dropdown Popout Support
         * ================================================================== */
        .tenant-subnav-items {
            display: flex !important;
            align-items: center !important;
            flex-wrap: wrap !important;
            max-width: 100% !important;
            overflow: visible !important;
        }
        .tenant-subnav-items > a,
        .tenant-subnav-items > div,
        .tenant-subnav-items button {
            display: flex !important;
            align-items: center !important;
            flex-shrink: 0 !important;
        }

        /* Dropdown menus must always render directly below the trigger button */
        .tenant-dropdown-menu,
        [id$="DropdownContent"] {
            top: 100% !important;
            margin-top: 0.375rem !important;
            z-index: 50 !important;
            max-height: calc(100vh - 120px);
            max-width: calc(100vw - 1.25rem);
            overflow-y: auto;
            overflow-x: hidden;
            box-sizing: border-box;
        }
    </style>

    <!-- jQuery -->
    <script src="inc/assets/vendor/jquery.min.js"></script>

    <!-- jQuery UI (for datepicker & drag) -->
    <script src="inc/assets/vendor/jquery-ui.min.js"></script>

    <!-- GSAP for micro-animations -->
    <script src="inc/assets/vendor/gsap.min.js"></script>

    <!-- Modal + collapse behaviour -->
    <script src="inc/assets/js/ht-modal.js"></script>
    <script>
        if (window.jQuery && !$.fn.modal && window.htModal) {
            $.fn.modal = function(action) {
                return this.each(function() {
                    if (action === 'show') window.htModal.open(this);
                    else if (action === 'hide') window.htModal.close(this);
                });
            };
        }
    </script>

    <!-- Global Settings -->
    <script src="inc/assets/js/settings.js"></script>

    <!-- SweetAlert2 & Modern Alerts System -->
    <script src="inc/assets/vendor/sweetalert2/dist/sweetalert2.all.min.js"></script>
    <script src="inc/assets/js/modern-alerts.js"></script>

    <?php /* flatpickr — same TEMPORARY BRIDGE gate as above. This webpack
            bundle is what turns data-toggle="flatpickr" into a live picker. */ ?>
    <?php if (!empty($ht_shell)): ?>
        <script src="inc/assets/vendor/flatpickr/flatpickr.min.js"></script>
        <script>
            if (typeof window.flatpickr !== 'undefined' && typeof jQuery !== 'undefined') {
                jQuery.fn.flatpickr = function(config) {
                    return this.each(function() {
                        flatpickr(this, config);
                    });
                };
            }
        </script>
        <script src="inc/assets/js/flatpickr.js"></script>
    <?php endif; ?>

    <?php $this->load->view('common/prod_security_guard'); ?>

</head>
