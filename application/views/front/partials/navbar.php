<?php
$CI =& get_instance();
$seg1 = (string) $CI->uri->segment(1);
$is_home    = in_array($seg1, array('', 'home', 'up'), true);
$is_holding = in_array($seg1, array('search_hold', 'src_holding', 'holding_tax', 'single_holding_person_methods'), true);
$is_entry   = in_array($seg1, array('add'), true);
$is_reps    = in_array($seg1, array('singleUser', 'singleEmp'), true);

$cls_active = 'px-3 py-2 rounded-xl text-sm font-semibold text-indigo-600 bg-indigo-50 transition-colors';
$cls_idle   = 'px-3 py-2 rounded-xl text-sm font-medium text-neutral-600 hover:text-neutral-900 hover:bg-neutral-100 transition-colors';

$mcls_active = 'block px-3 py-2 rounded-md text-base font-semibold text-indigo-600 bg-indigo-50';
$mcls_idle   = 'block px-3 py-2 rounded-md text-base font-medium text-neutral-700 hover:bg-neutral-100';

$is_logged_in = (isset($CI->ion_auth) && $CI->ion_auth->logged_in());
$is_sadmin    = ($is_logged_in && function_exists('is_sadmin') && is_sadmin());
$panel_url    = $is_sadmin ? 'super_admin' : 'dashboard';
$panel_title  = $is_sadmin ? 'সুপার প্যানেল' : 'ড্যাশবোর্ড';
$panel_icon   = $is_sadmin ? 'ph-shield-check' : 'ph-gauge';
?>
<!-- Top Announcement Bar -->
<div class="bg-indigo-900 text-indigo-100 text-xs py-1.5 px-4 hidden sm:block border-b border-indigo-800 overflow-hidden">
    <div class="max-w-7xl mx-auto flex justify-between items-center gap-4">
        <div class="flex items-center gap-3 overflow-hidden flex-1 min-w-0 mr-4">
            <span class="inline-flex items-center gap-1.5 bg-indigo-800/90 px-2 py-0.5 rounded text-[11px] font-semibold text-indigo-200 shrink-0 border border-indigo-700/60 shadow-2xs">
                <i class="ph ph-check-circle text-emerald-400 text-sm"></i> ডিজিটাল সেবা পোর্টাল
            </span>
            <div class="marquee-container overflow-hidden whitespace-nowrap flex-1 min-w-0">
                <div class="marquee-content inline-flex items-center text-indigo-200 text-xs">
                    <div class="marquee-group inline-flex items-center gap-6 pr-6">
                        <span>স্বাগতম <strong class="text-white font-medium"><?= htmlspecialchars($setting->ins_name ?? '', ENT_QUOTES, 'UTF-8'); ?></strong>-এর অনলাইন তথ্যসেবা বাতায়নে</span>
                        <span class="text-indigo-400 opacity-60">•</span>
                        <span>ঘরে বসেই হোল্ডিং ট্যাক্স যাচাই ও পরিশোধ করুন</span>
                        <span class="text-indigo-400 opacity-60">•</span>
                        <span>অনলাইনে চারিত্রিক, নাগরিকত্ব ও ওয়ারিশান সনদের আবেদন করুন</span>
                        <span class="text-indigo-400 opacity-60">•</span>
                        <span>স্মার্ট নাগরিক সেবায় আপনার ইউনিয়ন পরিষদ সর্বদা সচেষ্ট</span>
                        <span class="text-indigo-400 opacity-60">•</span>
                    </div>
                    <div class="marquee-group inline-flex items-center gap-6 pr-6" aria-hidden="true">
                        <span>স্বাগতম <strong class="text-white font-medium"><?= htmlspecialchars($setting->ins_name ?? '', ENT_QUOTES, 'UTF-8'); ?></strong>-এর অনলাইন তথ্যসেবা বাতায়নে</span>
                        <span class="text-indigo-400 opacity-60">•</span>
                        <span>ঘরে বসেই হোল্ডিং ট্যাক্স যাচাই ও পরিশোধ করুন</span>
                        <span class="text-indigo-400 opacity-60">•</span>
                        <span>অনলাইনে চারিত্রিক, নাগরিকত্ব ও ওয়ারিশান সনদের আবেদন করুন</span>
                        <span class="text-indigo-400 opacity-60">•</span>
                        <span>স্মার্ট নাগরিক সেবায় আপনার ইউনিয়ন পরিষদ সর্বদা সচেষ্ট</span>
                        <span class="text-indigo-400 opacity-60">•</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-4 text-[11px] shrink-0">
            <?php if (!empty($setting->socib_phone)): ?>
                <a href="tel:<?= htmlspecialchars($setting->socib_phone, ENT_QUOTES, 'UTF-8'); ?>" class="hover:text-white flex items-center gap-1 transition-colors">
                    <i class="ph ph-phone text-amber-400 text-sm"></i> হেল্পলাইন: <?= htmlspecialchars($setting->socib_phone, ENT_QUOTES, 'UTF-8'); ?>
                </a>
            <?php endif; ?>
            <?php if (!empty($setting->ins_emails)): ?>
                <a href="mailto:<?= htmlspecialchars($setting->ins_emails, ENT_QUOTES, 'UTF-8'); ?>" class="hover:text-white flex items-center gap-1 transition-colors">
                    <i class="ph ph-envelope text-amber-400 text-sm"></i> <?= htmlspecialchars($setting->ins_emails, ENT_QUOTES, 'UTF-8'); ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Main Sticky Navbar -->
<header class="sticky top-0 z-40 bg-white border-b border-neutral-200 shadow-xs transition-all">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-20">
            <!-- Brand Logo & Title -->
            <a href="<?= base_url(); ?>" class="flex items-center gap-3.5 group">
                <div class="relative w-12 h-12 shrink-0 flex items-center justify-center rounded-xl bg-neutral-100 p-1 border border-neutral-200 shadow-xs group-hover:scale-105 transition-transform duration-200">
                    <img src="<?= media_url($setting->ins_logo_path ?? '', 'image/png'); ?>" 
                         alt="<?= htmlspecialchars($setting->ins_name ?? 'লোগো', ENT_QUOTES, 'UTF-8'); ?>" 
                         class="max-w-full max-h-full object-contain">
                </div>
                <div>
                    <h1 class="text-lg md:text-xl font-bold text-neutral-900 leading-tight group-hover:text-indigo-600 transition-colors">
                        <?= htmlspecialchars($setting->ins_name ?? 'ইউনিয়ন পরিষদ', ENT_QUOTES, 'UTF-8'); ?>
                    </h1>
                    <p class="text-xs text-neutral-500 font-medium">
                        <?= htmlspecialchars($setting->ins_title_ss ?? 'ডিজিটাল নাগরিক সেবা কেন্দ্র', ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                </div>
            </a>

            <!-- Desktop Nav Links -->
            <nav class="hidden md:flex items-center gap-1 lg:gap-2">
                <a href="<?= base_url(); ?>" class="<?= $is_home ? $cls_active : $cls_idle ?>">
                    হোম
                </a>
                <a href="<?= $is_home ? '#services' : base_url('#services'); ?>" class="<?= $cls_idle ?>">
                    নাগরিক সেবা
                </a>
                <a href="search_hold" class="<?= $is_holding ? $cls_active : $cls_idle ?>">
                    হোল্ডিং ট্যাক্স
                </a>
                <a href="add" class="<?= $is_entry ? $cls_active : $cls_idle ?>">
                    তথ্য এন্ট্রি
                </a>
                <a href="<?= $is_home ? '#leaders' : base_url('#leaders'); ?>" class="<?= $is_reps ? $cls_active : $cls_idle ?>">
                    জনপ্রতিনিধি
                </a>
                <a href="<?= $is_home ? '#contact' : base_url('#contact'); ?>" class="<?= $cls_idle ?>">
                    যোগাযোগ
                </a>
            </nav>

            <!-- Action Button -->
            <div class="hidden sm:flex items-center gap-2">
                <?php if ($is_logged_in): ?>
                    <a href="<?= site_url($panel_url); ?>" class="ht-btn ht-btn-primary shadow-sm hover:shadow-md transition-all">
                        <i class="ph <?= $panel_icon ?> text-xs"></i>
                        <span><?= $panel_title ?></span>
                    </a>
                    <a href="logout" class="flex h-9 w-9 items-center justify-center rounded-xl border border-neutral-200 text-neutral-500 hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600 transition-colors" title="লগআউট">
                        <i class="ph ph-sign-out text-sm"></i>
                    </a>
                <?php else: ?>
                    <a href="login" class="ht-btn ht-btn-primary shadow-sm hover:shadow-md transition-all">
                        <i class="ph ph-lock-key text-xs"></i>
                        <span>প্যানেল লগইন</span>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Mobile Hamburger Button -->
            <div class="flex md:hidden">
                <button type="button" 
                        onclick="document.getElementById('mobileMenu').classList.toggle('hidden')"
                        class="p-2.5 rounded-xl text-neutral-600 hover:text-neutral-900 hover:bg-neutral-100 focus:outline-none"
                        aria-label="Toggle navigation">
                    <i class="ph ph-list text-2xl"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Dropdown Menu -->
    <div id="mobileMenu" class="hidden md:hidden border-t border-neutral-200 bg-white px-4 pt-3 pb-5 space-y-2">
        <a href="<?= base_url(); ?>" class="<?= $is_home ? $mcls_active : $mcls_idle ?>">হোম</a>
        <a href="<?= $is_home ? '#services' : base_url('#services'); ?>" class="<?= $mcls_idle ?>">নাগরিক সেবা</a>
        <a href="search_hold" class="<?= $is_holding ? $mcls_active : $mcls_idle ?>">হোল্ডিং ট্যাক্স</a>
        <a href="add" class="<?= $is_entry ? $mcls_active : $mcls_idle ?>">তথ্য এন্ট্রি</a>
        <a href="<?= $is_home ? '#leaders' : base_url('#leaders'); ?>" class="<?= $is_reps ? $mcls_active : $mcls_idle ?>">জনপ্রতিনিধি</a>
        <a href="<?= $is_home ? '#contact' : base_url('#contact'); ?>" class="<?= $mcls_idle ?>">যোগাযোগ</a>
        <div class="pt-3 border-t border-neutral-100">
            <?php if ($is_logged_in): ?>
                <div class="flex gap-2">
                    <a href="<?= site_url($panel_url); ?>" class="ht-btn ht-btn-primary flex-1 justify-center">
                        <i class="ph <?= $panel_icon ?> text-xs"></i>
                        <span><?= $panel_title ?></span>
                    </a>
                    <a href="logout" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-neutral-200 text-neutral-600 hover:bg-rose-50 hover:text-rose-600">
                        <i class="ph ph-sign-out text-base"></i>
                    </a>
                </div>
            <?php else: ?>
                <a href="login" class="ht-btn ht-btn-primary w-full justify-center">
                    <i class="ph ph-lock-key text-xs"></i>
                    <span>প্যানেল লগইন</span>
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>
