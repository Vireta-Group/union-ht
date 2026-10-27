<!DOCTYPE html>
<html lang="bn" class="scroll-smooth">
<head>
    <base href="<?= base_url(); ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    // Pages that share this head may override the title and description by
    // setting $page_title / $page_desc before loading the partial. The five
    // standalone pages under front/ each used to carry their own <head> and so
    // could name themselves; now that they load this partial, this is how they
    // keep doing that. Both are escaped, same as the defaults were.
    $ht_title = ( ! empty($page_title))
        ? $setting->ins_name . ' — ' . $page_title
        : ($setting->ins_name ?? 'ইউনিয়ন পরিষদ ডিজিটাল সেবা');
    $ht_desc = $page_desc ?? ($setting->ins_title_ss ?? 'স্মার্ট ইউনিয়ন পরিষদ সেবা ও হোল্ডিং ট্যাক্স ম্যানেজমেন্ট সিস্টেম');
    ?>
    <title><?= htmlspecialchars($ht_title, ENT_QUOTES, 'UTF-8'); ?></title>
    <meta name="description" content="<?= htmlspecialchars($ht_desc, ENT_QUOTES, 'UTF-8'); ?>">
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="<?= media_url($setting->ins_logo_path ?? '', 'image/png'); ?>" type="image/x-icon">

    <!-- Fonts: Adorsho Lipi (আদর্শ লিপি) + Hind Siliguri + Inter, hosted locally (no Google CDN) -->
    <link rel="stylesheet" href="inc/assets/vendor/fonts/adorsho-lipi.css">
    <link rel="stylesheet" href="inc/assets/vendor/fonts/hind-siliguri.css">
    <link rel="stylesheet" href="inc/assets/vendor/fonts/inter.css">

    <!-- Compiled Design Tokens & Tailwind Portal CSS -->
    <link rel="stylesheet" href="inc/assets/css/portal.css">
    <link rel="stylesheet" href="inc/assets/css/custom-scrollbar.css">
    <link rel="stylesheet" href="inc/assets/css/modern-theme.css">

    <!-- jQuery UI CSS & Modern Customized Datepicker -->
    <link rel="stylesheet" href="inc/assets/vendor/jquery-ui.css">
    <link rel="stylesheet" href="inc/assets/css/custom-datepicker.css">

    <!-- Phosphor Icons: Stylesheet & FontScript CDN -->
    <!-- Phosphor icons, hosted locally (no CDN). Same three weights, woff2 only. -->
    <link rel="stylesheet" type="text/css" href="inc/assets/vendor/phosphor/regular/style.css"/>
    <link rel="stylesheet" type="text/css" href="inc/assets/vendor/phosphor/bold/style.css"/>
    <link rel="stylesheet" type="text/css" href="inc/assets/vendor/phosphor/fill/style.css"/>

    <?php /* FontAwesome Free Icons: REMOVED 2026-09-29.
             It was linked from here and nowhere else, so every public page was
             downloading 1,037 KB (204 KB gzipped) for zero icons: the portal is
             on Phosphor, and a grep for fa / fas / far / fab / fal classes
             across front/ and saas/ returns nothing, nor does glyphicon-* or
             icon-*.

             The admin was never served by it either — app.css ships no `.fa`
             rule and no @font-face, and p/h.php never linked it. That is why the
             two <i class="fa ..."> in back/profile_info.php have been drawing
             nothing; they are converted to Phosphor in their own step.

             The file itself stays until that last use is gone, so this is a link
             removal and not a deletion.

             A PHP comment, not an HTML one: this file is loaded by every public
             page, so an HTML comment here ships its own text to all of them. */ ?>

    <!-- Swiper CSS (Modern Hero Slider) -->
    <link rel="stylesheet" href="inc/assets/vendor/swiper/swiper-bundle.min.css">

    <!-- Owl Carousel CSS -->
    <link rel="stylesheet" href="inc/assets/vendor/owl.carousel/owl.carousel.min.css">

    <!-- jQuery + jQuery UI + Swiper + GSAP + Owl Carousel -->
    <script src="inc/assets/vendor/jquery.min.js"></script>
    <script src="inc/assets/vendor/jquery-ui.min.js"></script>
    <script src="inc/assets/vendor/swiper/swiper-bundle.min.js"></script>
    <script src="inc/assets/vendor/gsap.min.js"></script>
    <script src="inc/assets/vendor/owl.carousel/owl.carousel.min.js"></script>
    <script src="inc/assets/js/ht-modal.js"></script>

    <style>
        body, input, select, textarea, button, table, th, td, h1, h2, h3, h4, h5, h6 {
            font-family: 'AdarshaLipi', 'Adarsha Lipi', 'AdorshoLipi', 'Adorsho Lipi', 'Hind Siliguri', 'Inter', system-ui, -apple-system, sans-serif !important;
        }
        body {
            background-color: var(--brand-surface, #faf7f5);
            color: var(--brand-text, #1c1917);
        }
        .bengali-num {
            font-feature-settings: "tnum";
            font-variant-numeric: tabular-nums;
        }
    </style>
    <?php $this->load->view('common/prod_security_guard'); ?>
</head>
<body class="min-h-screen flex flex-col antialiased selection:bg-indigo-200 selection:text-indigo-950">
