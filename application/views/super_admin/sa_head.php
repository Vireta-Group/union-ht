<!DOCTYPE html>
<html lang="bn" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($sa_title, ENT_QUOTES, 'UTF-8') ?> — ViretaDev সুপার অ্যাডমিন</title>

    <!--
        <base> is here, not in sa_nav, because the panel renders anchor
        hrefs as bare segments (dashboard, super_admin) to match every
        other link in this codebase. Without <base> those resolve against
        the current directory, so /sadmin_settings would look for
        /sadmin_settings/sadmin_subscriptions.
    -->
    <base href="<?= base_url(); ?>">

    <!-- Fonts: Adorsho Lipi (আদর্শ লিপি) + Hind Siliguri + Inter, hosted locally (no Google CDN) -->
    <link rel="stylesheet" href="inc/assets/vendor/fonts/adorsho-lipi.css">
    <link rel="stylesheet" href="inc/assets/vendor/fonts/hind-siliguri.css">
    <link rel="stylesheet" href="inc/assets/vendor/fonts/inter.css">

    <!-- Design Tokens & Tailwind Portal CSS. No app.css, no MDK bridge: see
         Template::sadmin() for why this panel must never pay for 3.1 MB. -->
    <link rel="stylesheet" href="inc/assets/css/portal.css">

    <!-- jQuery UI + the parishad's datepicker skin. Super admin was the one
         shell that had neither, so every <input type="date"> in tenants /
         subscriptions / audit fell back to the browser's default picker.
         custom-datepicker.css styles .ui-datepicker, i.e. it does nothing
         without jquery-ui.min.js, so the two have to travel together. The
         same pair is loaded in p/h.php for the admin shell. -->
    <link rel="stylesheet" href="inc/assets/vendor/jquery-ui.css">
    <link rel="stylesheet" href="inc/assets/css/custom-datepicker.css">

    <!-- Phosphor Icons (local, no CDN) -->
    <link rel="stylesheet" type="text/css" href="inc/assets/vendor/phosphor/regular/style.css"/>
    <link rel="stylesheet" type="text/css" href="inc/assets/vendor/phosphor/bold/style.css"/>
    <link rel="stylesheet" type="text/css" href="inc/assets/vendor/phosphor/fill/style.css"/>

    <!-- SweetAlert2 & Modern Alerts System -->
    <link rel="stylesheet" href="inc/assets/vendor/sweetalert2/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="inc/assets/css/modern-alerts.css">
    <script src="inc/assets/vendor/sweetalert2/dist/sweetalert2.all.min.js"></script>
    <script src="inc/assets/js/modern-alerts.js"></script>

    <!-- Custom Modern WebKit Scrollbars -->
    <link rel="stylesheet" href="inc/assets/css/custom-scrollbar.css">

    <!-- Modern Dimensional & Tactile Theme Layer -->
    <link rel="stylesheet" href="inc/assets/css/modern-theme.css">

    <!-- GSAP for micro-animations (local, no CDN) -->
    <script src="inc/assets/vendor/gsap.min.js"></script>

    <!-- Super Admin Full Bilingual Engine (বাংলা ⇋ English) -->
    <script src="inc/assets/js/sadmin-i18n.js"></script>

    <style>
        body, input, select, textarea, button, table, th, td, h1, h2, h3, h4, h5, h6 {
            font-family: 'AdarshaLipi', 'Adarsha Lipi', 'AdorshoLipi', 'Adorsho Lipi', 'Hind Siliguri', 'Inter', system-ui, sans-serif !important;
        }
        body {
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

        /* Language Switcher Visibility Rules */
        html[data-sa-lang="en"] .lang-bn,
        html[data-sa-lang="en"] .lang-bn-block,
        html[data-sa-lang="en"] .lang-bn-inline {
            display: none !important;
        }
        html[data-sa-lang="en"] .lang-en {
            display: inline !important;
        }
        html[data-sa-lang="en"] .lang-en-block {
            display: block !important;
        }
        html[data-sa-lang="en"] .lang-en-inline {
            display: inline-block !important;
        }

        html:not([data-sa-lang="en"]) .lang-en,
        html:not([data-sa-lang="en"]) .lang-en-block,
        html:not([data-sa-lang="en"]) .lang-en-inline,
        html[data-sa-lang="bn"] .lang-en,
        html[data-sa-lang="bn"] .lang-en-block,
        html[data-sa-lang="bn"] .lang-en-inline {
            display: none !important;
        }

        /* Keep the shell itself from ever scrolling sideways without breaking sticky elements */
        html, body {
            max-width: 100vw !important;
            overflow-x: clip !important;
        }

        /* =====================================================================
         * Dark & Light Theme System for Super Admin & Dev Management
         * ================================================================== */
        /* Super Admin Topbar Header: Never transparent in any mode */
        #saHeader {
            background-color: #111827 !important;
            border-bottom: 1px solid #1f2937 !important;
            color: #ffffff !important;
        }
        @supports ((-webkit-backdrop-filter: blur(12px)) or (backdrop-filter: blur(12px))) {
            #saHeader {
                background-color: rgba(17, 24, 39, 0.96) !important;
                -webkit-backdrop-filter: blur(12px) !important;
                backdrop-filter: blur(12px) !important;
            }
        }
        html[data-theme="dark"] #saHeader {
            background-color: #0b0f19 !important;
            border-bottom-color: #1f2937 !important;
        }
        @supports ((-webkit-backdrop-filter: blur(12px)) or (backdrop-filter: blur(12px))) {
            html[data-theme="dark"] #saHeader {
                background-color: rgba(11, 15, 25, 0.96) !important;
            }
        }

        html[data-theme="dark"] body {
            background-color: #0b0f19 !important;
            color: #f8fafc !important;
        }
        html[data-theme="dark"] .bg-white {
            background-color: #111827 !important;
            color: #f9fafb !important;
        }
        html[data-theme="dark"] aside#saSidebar {
            background-color: #111827 !important;
            border-color: #1f2937 !important;
            color: #f9fafb !important;
        }
        html[data-theme="dark"] aside#saSidebar nav a:not([aria-current="page"]) {
            color: #9ca3af !important;
        }
        html[data-theme="dark"] aside#saSidebar nav a:not([aria-current="page"]):hover {
            background-color: #1f2937 !important;
            color: #f9fafb !important;
        }
        html[data-theme="dark"] aside#saSidebar nav a:not([aria-current="page"]) i {
            color: #6b7280 !important;
        }
        html[data-theme="dark"] aside#saSidebar nav a:not([aria-current="page"]):hover i {
            color: #9ca3af !important;
        }
        html[data-theme="dark"] aside#saSidebar .border-neutral-100 {
            border-color: #1f2937 !important;
        }
        html[data-theme="dark"] aside#saSidebar .hover\:bg-neutral-50:hover {
            background-color: #1f2937 !important;
        }
        html[data-theme="dark"] aside#saSidebar .text-neutral-800 {
            color: #f3f4f6 !important;
        }
        html[data-theme="dark"] aside#saSidebar .text-neutral-400 {
            color: #9ca3af !important;
        }
        html[data-theme="dark"] .border-neutral-200,
        html[data-theme="dark"] .border-neutral-200\/70,
        html[data-theme="dark"] .border-neutral-200\/80,
        html[data-theme="dark"] .border-neutral-100,
        html[data-theme="dark"] .border-neutral-300 {
            border-color: #1f2937 !important;
        }
        html[data-theme="dark"] .bg-neutral-50 {
            background-color: #162032 !important;
        }
        html[data-theme="dark"] .bg-neutral-100 {
            background-color: #1f2937 !important;
            color: #cbd5e1 !important;
        }
        html[data-theme="dark"] .hover\:bg-neutral-50:hover,
        html[data-theme="dark"] .hover\:bg-neutral-100:hover,
        html[data-theme="dark"] .hover\:bg-neutral-100\/80:hover {
            background-color: #1f2937 !important;
        }
        html[data-theme="dark"] .text-neutral-900 {
            color: #f9fafb !important;
        }
        html[data-theme="dark"] .text-neutral-800 {
            color: #f3f4f6 !important;
        }
        html[data-theme="dark"] .text-neutral-700 {
            color: #e5e7eb !important;
        }
        html[data-theme="dark"] .text-neutral-600 {
            color: #cbd5e1 !important;
        }
        html[data-theme="dark"] .text-neutral-500 {
            color: #9ca3af !important;
        }
        html[data-theme="dark"] .text-neutral-400 {
            color: #6b7280 !important;
        }
        html[data-theme="dark"] table thead.bg-neutral-50,
        html[data-theme="dark"] table thead {
            background-color: #162032 !important;
            border-color: #1f2937 !important;
        }
        html[data-theme="dark"] th {
            color: #9ca3af !important;
            border-color: #1f2937 !important;
        }
        html[data-theme="dark"] td {
            border-color: #1f2937 !important;
            color: #e5e7eb;
        }
        html[data-theme="dark"] tbody tr:hover td {
            background-color: #141f32 !important;
        }
        html[data-theme="dark"] input[type="text"],
        html[data-theme="dark"] input[type="email"],
        html[data-theme="dark"] input[type="password"],
        html[data-theme="dark"] input[type="search"],
        html[data-theme="dark"] input[type="number"],
        html[data-theme="dark"] input[type="date"],
        html[data-theme="dark"] select,
        html[data-theme="dark"] textarea {
            background-color: #0b0f19 !important;
            border-color: #374151 !important;
            color: #f9fafb !important;
        }
        html[data-theme="dark"] input::placeholder,
        html[data-theme="dark"] textarea::placeholder {
            color: #64748b !important;
        }
        html[data-theme="dark"] select option {
            background-color: #111827 !important;
            color: #f9fafb !important;
        }
        html[data-theme="dark"] .fixed.inset-0 .bg-white {
            background-color: #111827 !important;
            color: #f9fafb !important;
            border-color: #1f2937 !important;
        }
        html[data-theme="dark"] .bg-amber-50 {
            background-color: rgba(245, 158, 11, 0.1) !important;
            border-color: rgba(245, 158, 11, 0.3) !important;
        }
        html[data-theme="dark"] .text-amber-900 {
            color: #fcd34d !important;
        }
        html[data-theme="dark"] .bg-rose-50 {
            background-color: rgba(244, 63, 94, 0.1) !important;
            border-color: rgba(244, 63, 94, 0.3) !important;
        }
        html[data-theme="dark"] .text-rose-900 {
            color: #fda4af !important;
        }
        html[data-theme="dark"] .bg-emerald-50 {
            background-color: rgba(16, 185, 129, 0.1) !important;
            border-color: rgba(16, 185, 129, 0.3) !important;
        }
        html[data-theme="dark"] .text-emerald-900,
        html[data-theme="dark"] .text-emerald-700 {
            color: #6ee7b7 !important;
        }
        html[data-theme="dark"] .bg-indigo-50 {
            background-color: rgba(99, 102, 241, 0.12) !important;
            border-color: rgba(99, 102, 241, 0.3) !important;
        }
        html[data-theme="dark"] .text-indigo-900,
        html[data-theme="dark"] .text-indigo-700 {
            color: #a5b4fc !important;
        }
        html[data-theme="dark"] .bg-sky-50 {
            background-color: rgba(14, 165, 233, 0.1) !important;
            border-color: rgba(14, 165, 233, 0.3) !important;
        }
        html[data-theme="dark"] .text-sky-900,
        html[data-theme="dark"] .text-sky-700 {
            color: #7dd3fc !important;
        }
        html[data-theme="dark"] .bg-neutral-50.text-neutral-600 {
            background-color: #1f2937 !important;
            color: #9ca3af !important;
        }

        /* =====================================================================
         * Responsive Utilities for Super Admin
         * ================================================================== */
        @media (max-width: 767.98px) {
            html, body {
                overflow-x: clip !important;
                max-width: 100vw !important;
            }
            .max-w-\[1440px\] {
                width: 100% !important;
                max-width: 100vw !important;
                box-sizing: border-box !important;
                padding-left: 0.75rem !important;
                padding-right: 0.75rem !important;
            }
            main {
                width: 100% !important;
                max-width: 100% !important;
                min-width: 0 !important;
                overflow-x: hidden !important;
            }
            .grid {
                display: grid !important;
                grid-template-columns: 1fr !important;
            }
            .grid.grid-cols-2 {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            }
            .grid.grid-cols-3 {
                grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
            }
            .overflow-x-auto {
                width: 100% !important;
                max-width: 100% !important;
                -webkit-overflow-scrolling: touch !important;
            }
            input, select, textarea {
                max-width: 100% !important;
                box-sizing: border-box !important;
            }
        }
        @media (min-width: 640px) {
            .sm\:grid-cols-2 {
                display: grid !important;
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            }
            .sm\:grid-cols-3 {
                display: grid !important;
                grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
            }
            .sm\:grid-cols-6 {
                display: grid !important;
                grid-template-columns: repeat(6, minmax(0, 1fr)) !important;
            }
            .sm\:w-auto { width: auto !important; }
            .sm\:flex-row { flex-direction: row !important; }
            .sm\:items-center { align-items: center !important; }
            .sm\:justify-between { justify-content: space-between !important; }
            .sm\:inline { display: inline !important; }
            .sm\:inline-block { display: inline-block !important; }
            .sm\:block { display: block !important; }
            .sm\:flex { display: flex !important; }
            .sm\:hidden { display: none !important; }
        }
        @media (min-width: 768px) {
            .md\:flex-row {
                flex-direction: row !important;
            }
            .md\:hidden { display: none !important; }
            .md\:inline { display: inline !important; }
            .md\:inline-block { display: inline-block !important; }
            .md\:flex { display: flex !important; }
            .md\:block { display: block !important; }
            .md\:translate-x-0 { transform: none !important; }
            .md\:static { position: static !important; }
            .md\:sticky { position: sticky !important; }
            .md\:top-20 { top: 5rem !important; }
        }
        @media (min-width: 1024px) {
            .lg\:grid-cols-4 {
                display: grid !important;
                grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
            }
            .lg\:grid-cols-3 {
                display: grid !important;
                grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
            }
            .lg\:grid-cols-2 {
                display: grid !important;
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            }
        }
        @media (min-width: 1280px) {
            .xl\:grid-cols-3 {
                display: grid !important;
                grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
            }
        }
    </style>
    <script>
        (function() {
            try {
                var savedTheme = localStorage.getItem('vireta_sa_theme') || (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
                document.documentElement.setAttribute('data-theme', savedTheme);
                if (savedTheme === 'dark') {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
                var savedLang = localStorage.getItem('vireta_sa_lang') || 'bn';
                document.documentElement.setAttribute('data-sa-lang', savedLang);
                document.documentElement.setAttribute('lang', savedLang);
            } catch(e) {}
        })();

        function toggleSaTheme() {
            var current = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
            var next = current === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            if (next === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
            try {
                localStorage.setItem('vireta_sa_theme', next);
            } catch(e) {}
            updateSaThemeUi(next);
        }

        function updateSaThemeUi(theme) {
            var moon = document.getElementById('saThemeIconMoon');
            var sun = document.getElementById('saThemeIconSun');
            var btn = document.getElementById('saThemeToggleBtn');
            if (!btn) return;
            if (theme === 'dark') {
                if (moon) moon.classList.add('hidden');
                if (sun) sun.classList.remove('hidden');
                btn.setAttribute('title', 'লাইট মোডে পরিবর্তন করুন (Switch to Light Mode)');
            } else {
                if (moon) moon.classList.remove('hidden');
                if (sun) sun.classList.add('hidden');
                btn.setAttribute('title', 'ডার্ক মোডে পরিবর্তন করুন (Switch to Dark Mode)');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            var cur = document.documentElement.getAttribute('data-theme') || 'light';
            updateSaThemeUi(cur);
        });
    </script>
    <?php $this->load->view('common/prod_security_guard'); ?>
</head>
<body class="min-h-screen antialiased selection:bg-indigo-200 selection:text-indigo-950">

<!-- Top bar (Sticky Glassmorphic) -->
<header id="saHeader" class="bg-neutral-900 text-white border-b border-neutral-800 sticky top-0 z-40 shadow-xs transition-colors">
    <div class="max-w-360 mx-auto px-2.5 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-1.5 sm:gap-4 overflow-hidden">
        <div class="flex items-center gap-2 sm:gap-3.5 min-w-0">
            <!-- Hamburger: the sidebar is off-canvas below md. -->
            <button
                type="button"
                onclick="document.getElementById('saSidebar').classList.toggle('-translate-x-full'); document.getElementById('saBackdrop').classList.toggle('hidden')"
                class="md:hidden p-1.5 -ml-1 rounded-lg text-neutral-300 hover:text-white hover:bg-neutral-800 transition-colors shrink-0 cursor-pointer"
                aria-label="মেনু">
                <i class="ph ph-list text-xl"></i>
            </button>

            <a href="<?= base_url('super_admin'); ?>" class="flex items-center gap-2 sm:gap-3 min-w-0 group">
                <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-black text-base sm:text-lg shadow-xs shrink-0 group-hover:scale-105 transition-transform">
                    V
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-1.5 sm:gap-2">
                        <span class="text-sm sm:text-base font-black tracking-tight text-white leading-tight">ViretaDev</span>
                        <span class="hidden sm:inline-block px-1.5 py-0.5 rounded-md text-[9px] sm:text-[10px] font-bold bg-amber-400 text-neutral-950 uppercase tracking-wider shrink-0">
                            Super Admin
                        </span>
                    </div>
                    <p class="hidden sm:block text-[11px] text-neutral-400 truncate leading-tight mt-0.5">
                        সেন্ট্রাল মাল্টি-টেন্যান্ট গভর্ন্যান্স
                    </p>
                </div>
            </a>
        </div>

        <div class="flex items-center gap-1.5 sm:gap-2.5 shrink-0">
            <!-- Theme Switcher Button -->
            <button type="button" onclick="toggleSaTheme()" id="saThemeToggleBtn"
                    title="ডার্ক / লাইট থিম পরিবর্তন করুন"
                    aria-label="Toggle theme"
                    class="p-1.5 sm:px-2.5 sm:py-1 rounded-xl bg-neutral-800 hover:bg-neutral-700/80 border border-neutral-700/80 text-neutral-300 hover:text-white text-xs shadow-2xs shrink-0 cursor-pointer flex items-center gap-1.5 transition-colors">
                <i class="ph-bold ph-moon text-sm text-indigo-300" id="saThemeIconMoon"></i>
                <i class="ph-bold ph-sun text-sm text-amber-400 hidden" id="saThemeIconSun"></i>
                <span class="hidden md:inline text-[11px] font-semibold text-neutral-300">
                    <span class="lang-bn">থিম</span>
                    <span class="lang-en">Theme</span>
                </span>
            </button>

            <!-- Language Switcher Pill -->
            <div class="flex items-center p-0.5 rounded-xl bg-neutral-800 border border-neutral-700/80 text-xs shadow-2xs shrink-0">
                <button type="button" onclick="setSaLanguage('bn')" id="saLangBtnBn"
                        title="বাংলা ভাষা নির্বাচন করুন"
                        class="px-1.5 sm:px-2.5 py-0.5 sm:py-1 rounded-lg text-[10px] sm:text-xs font-bold transition-all text-white bg-indigo-600 shadow-xs cursor-pointer flex items-center gap-0.5 sm:gap-1">
                    <span>বাং</span>
                </button>
                <button type="button" onclick="setSaLanguage('en')" id="saLangBtnEn"
                        title="Switch to English"
                        class="px-1.5 sm:px-2.5 py-0.5 sm:py-1 rounded-lg text-[10px] sm:text-xs font-semibold transition-all text-neutral-400 hover:text-white cursor-pointer flex items-center gap-0.5 sm:gap-1">
                    <span>EN</span>
                </button>
            </div>

            <div class="hidden sm:block h-6 w-px bg-neutral-800"></div>

            <?php if (($sa_seg ?? '') === 'super_admin' || ($sa_seg ?? '') === 'sadmin_tenants'): ?>
                <button
                    type="button"
                    onclick="typeof openOnboardModal === 'function' ? openOnboardModal() : (window.location = '<?= base_url('sadmin_tenants'); ?>')"
                    class="px-2 sm:px-4 py-1.5 sm:py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:scale-98 text-white font-bold text-xs sm:text-sm flex items-center gap-1 sm:gap-1.5 shadow-xs transition-all cursor-pointer shrink-0"
                    title="নতুন ইউনিয়ন অনবোর্ড">
                    <i class="ph ph-plus-circle text-base"></i>
                    <span class="hidden sm:inline">নতুন ইউনিয়ন অনবোর্ড</span>
                    <span class="sm:hidden text-[11px]">অনবোর্ড</span>
                </button>
            <?php else: ?>
                <a href="<?= base_url('sadmin_tenants'); ?>"
                   class="px-2 sm:px-4 py-1.5 sm:py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs sm:text-sm flex items-center gap-1 sm:gap-1.5 shadow-xs transition-all shrink-0"
                   title="নতুন ইউনিয়ন অনবোর্ড">
                    <i class="ph ph-plus-circle text-base"></i>
                    <span class="hidden sm:inline">নতুন ইউনিয়ন অনবোর্ড</span>
                    <span class="sm:hidden text-[11px]">অনবোর্ড</span>
                </a>
            <?php endif; ?>

            <div class="hidden sm:block h-6 w-px bg-neutral-800"></div>

            <a href="<?= base_url('logout'); ?>" class="text-xs text-rose-400 hover:text-rose-300 flex items-center gap-1 py-1.5 px-1.5 sm:px-2.5 rounded-lg hover:bg-rose-950/40 transition-colors shrink-0" title="লগআউট">
                <i class="ph ph-sign-out text-base sm:text-sm"></i>
                <span class="hidden md:inline">লগআউট</span>
            </a>
        </div>
    </div>
</header>

<div class="max-w-360 mx-auto w-full px-3.5 sm:px-6 lg:px-8 py-5 sm:py-8 flex flex-col md:flex-row gap-5 lg:gap-8 items-start">
