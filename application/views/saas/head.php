<!DOCTYPE html>
<?php
/**
 * Product-site shell, head + opening wrapper.
 *
 * This replaces front/saas_landing.php, which was a ~1.7 kB React mount point
 * that shipped a ~700 kB Vite bundle. The nine product pages are now ordinary
 * server-rendered views loaded in sequence by Template::saas():
 *
 *   saas/head → saas/partials/navbar → <page view> → saas/partials/footer → saas/foot
 *
 * Each page is a real URL served by its own Front controller method, so
 * navigation is ordinary <a href> — no client-side router, no history API, and
 * every page works with JavaScript disabled.
 *
 * The only script this shell loads is inc/assets/js/ht-modal.js (for the
 * support modal) and inc/assets/js/saas.js (mobile nav, FAQ accordion, plan
 * tabs, count-up). Both are hand-written and dependency-free.
 */
?>
<html lang="bn" class="scroll-smooth">
<head>
    <base href="<?= base_url(); ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($saas_title ?? 'ViretaDev — স্মার্ট ইউনিয়ন পরিষদ ম্যানেজমেন্ট SaaS প্ল্যাটফর্ম', ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="description" content="<?= htmlspecialchars($saas_desc ?? 'বাংলাদেশের ইউনিয়ন পরিষদসমূহের জন্য আধুনিক, ক্লাউড-বেজড মাল্টি-টেন্যান্ট ই-গভর্ন্যান্স, হোল্ডিং ট্যাক্স অটোমেশন ও ডিজিটাল সনদপত্র সফটওয়্যার।', ENT_QUOTES, 'UTF-8') ?>">

    <!-- Fonts: Adorsho Lipi (আদর্শ লিপি) + Hind Siliguri + Inter, hosted locally (no Google CDN) -->
    <link rel="stylesheet" href="inc/assets/vendor/fonts/adorsho-lipi.css">
    <link rel="stylesheet" href="inc/assets/vendor/fonts/hind-siliguri.css">
    <link rel="stylesheet" href="inc/assets/vendor/fonts/inter.css">

    <!-- Phosphor icon webfont, hosted locally (no CDN). Regular/bold/fill, woff2
         only. The CSS files are downloaded from the @phosphor-icons/web package
         into inc/assets/vendor/phosphor/. Same three weights the portal loads. -->
    <link rel="stylesheet" href="inc/assets/vendor/phosphor/regular/style.css">
    <link rel="stylesheet" href="inc/assets/vendor/phosphor/bold/style.css">
    <link rel="stylesheet" href="inc/assets/vendor/phosphor/fill/style.css">

    <link rel="stylesheet" href="inc/assets/css/portal.css">
    <link rel="stylesheet" href="inc/assets/css/custom-scrollbar.css">
    <link rel="stylesheet" href="inc/assets/css/modern-theme.css">
    <style>
        :root {
            /* 1536px wide container for large screens (2XL) */
            --container-7xl: 96rem;
        }
        /* Adorsho Lipi for all Bengali. !important is required, not decorative:
           <body> carries the `font-sans` utility, which is a class (0,1,0) and so
           outranks a bare `body` selector (0,0,1). The same four spellings the
           other three shells use are listed so whichever @font-face alias the
           browser matched still resolves. */
        body, input, select, textarea, button, table, th, td, h1, h2, h3, h4, h5, h6 {
            font-family: 'AdarshaLipi', 'Adarsha Lipi', 'AdorshoLipi', 'Adorsho Lipi', 'Hind Siliguri', 'Inter', system-ui, sans-serif !important;
        }

        /* Stop the shell itself from ever scrolling sideways. Individual wide
           bits (the plans comparison table) are already wrapped in
           overflow-x-auto and should scroll inside their card. This is the
           outer backstop for anything that leaks past those wrappers.

           clip rather than hidden: overflow-x on html/body would make the
           viewport a scroll container and break the sticky navbar
           (sticky top-0 in partials/navbar.php). clip does not.

           Inline rather than a Tailwind utility because .overflow-x-clip is
           absent from the compiled inc/assets/css/portal.css, and that bundle
           cannot be rebuilt in this environment (no package.json, no
           node_modules, no Tailwind CLI). */
        html, body {
            overflow-x: clip;
        }

        /* Prevent layout shift / jitter when scrollbars appear/disappear */
        html {
            scrollbar-gutter: stable;
        }

        /* Smooth page transition styles */
        #page-loader-bar {
            position: fixed;
            top: 0;
            left: 0;
            height: 3px;
            width: 0%;
            background: linear-gradient(90deg, #4f46e5, #818cf8, #06b6d4);
            z-index: 9999;
            transition: width 0.25s ease, opacity 0.25s ease;
            box-shadow: 0 0 10px rgba(99, 102, 241, 0.7);
            pointer-events: none;
            opacity: 0;
        }

        main {
            transition: opacity 0.16s cubic-bezier(0.16, 1, 0.3, 1), transform 0.16s cubic-bezier(0.16, 1, 0.3, 1);
        }
        main.ht-page-exit {
            opacity: 0;
            transform: translateY(6px);
        }
        main.ht-page-enter {
            opacity: 0;
            transform: translateY(-6px);
        }

        /* Native View Transition API animations when supported */
        @keyframes htPageFadeIn {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes htPageFadeOut {
            from { opacity: 1; transform: translateY(0); }
            to { opacity: 0; transform: translateY(-6px); }
        }
        /* Skeleton Shimmer Primitives */
        @keyframes htShimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
        .ht-skeleton {
            background: linear-gradient(90deg, #f1f5f9 0%, #e2e8f0 50%, #f1f5f9 100%);
            background-size: 200% 100%;
            animation: htShimmer 1.5s infinite linear;
            border-radius: 0.5rem;
        }
        .ht-skeleton-dark {
            background: linear-gradient(90deg, #1e293b 0%, #334155 50%, #1e293b 100%);
            background-size: 200% 100%;
            animation: htShimmer 1.5s infinite linear;
            border-radius: 0.5rem;
        }
    </style>
    <?php $this->load->view('common/prod_security_guard'); ?>
</head>
<body class="min-h-screen bg-surface font-sans text-neutral-800 antialiased selection:bg-indigo-200 selection:text-indigo-950">

    <div class="flex min-h-screen flex-col">

        <?php /* Flash messages. The React app had no equivalent — the register
                 and login flows posted through fetch and read the response
                 inline — so /register and /login now surface the session's
                 flashdata the same way the parishad portal does. */ ?>
        <?php foreach (array('message', 'error', 'success') as $saas_flash_key):
            $saas_flash = $this->session->flashdata($saas_flash_key);
            if (empty($saas_flash)) { continue; }
            $saas_flash_tone = ($saas_flash_key === 'error') ? 'danger' : ($saas_flash_key === 'success' ? 'success' : 'primary');
        ?>
            <div class="mx-auto w-full max-w-7xl px-4 pt-4 sm:px-6 lg:px-8">
                <div class="ht-badge ht-badge-<?= $saas_flash_tone ?> w-full justify-start gap-2 rounded-lg px-4 py-2.5 text-sm"
                     role="status">
                    <?= icon($saas_flash_key === 'error' ? 'info' : 'check-circle', 16, 'fill') ?>
                    <span><?= $saas_flash ?></span>
                </div>
            </div>
        <?php endforeach; ?>
