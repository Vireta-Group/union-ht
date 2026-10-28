<?php
/**
 * Product-site navbar.
 *
 * Ported from resources/js/components/Navbar.jsx. Two things changed and both
 * are improvements:
 *
 *   1. Navigation is now real <a href> instead of <button onClick={navigate}>.
 *      React's client-side router was faking URLs; now every product page is
 *      its own request, so middle-click, "open in new tab", the back button and
 *      no-JS all behave the way a visitor expects.
 *
 *   2. The active-page highlight reads $saas_path, which Template::saas()
 *      passes in. In React this was the currentPath prop.
 *
 * The mobile menu used to be conditionally rendered from React state. It is now
 * always in the DOM and hidden, toggled by saas.js via data-saas-toggle.
 *
 * @var string $saas_path  Active route, e.g. '/about'. Defaults to '/'.
 */

$saas_path = $saas_path ?? '/';

$saas_links = array(
    '/'        => 'হোম',
    '/about'   => 'আমাদের সম্পর্কে',
    '/plans'   => 'প্যাকেজ প্ল্যান',
    '/reviews' => 'রিভিউ',
    '/contact' => 'যোগাযোগ',
    '/faq'     => 'FAQ',
);

/** Desktop nav item classes. */
$saas_nav_class = function ($is_active, $base = '') {
    return $base . ' px-2.5 py-1.5 rounded-lg text-xs xl:text-sm font-bold whitespace-nowrap transition-colors '
        . ($is_active
            ? 'text-indigo-600 bg-indigo-50'
            : 'text-neutral-700 hover:text-indigo-600 hover:bg-neutral-100');
};
?>
        <!-- Top Announcement & Support Bar -->
        <div class="border-b border-neutral-800 bg-neutral-950 px-4 py-1.5 text-xs text-neutral-300 overflow-hidden">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4">
                <div class="flex items-center gap-2.5 overflow-hidden flex-1 min-w-0 mr-4">
                    <span class="inline-flex shrink-0 items-center gap-1.5 rounded border border-emerald-500/30 bg-emerald-500/20 px-2 py-0.5 text-[10px] font-semibold text-emerald-400">
                        <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-emerald-400"></span> লাইভ SaaS প্ল্যাটফর্ম
                    </span>
                    <div class="marquee-container overflow-hidden whitespace-nowrap flex-1 min-w-0">
                        <div class="marquee-content inline-flex items-center text-neutral-300 text-xs">
                            <div class="marquee-group inline-flex items-center gap-6 pr-6">
                                <span>বাংলাদেশের ইউনিয়ন পরিষদসমূহের জন্য তৈরি স্বয়ংক্রিয় ক্লাউড সলিউশন</span>
                                <span class="text-neutral-600">•</span>
                                <span>এক ক্লিকেই হোল্ডিং ট্যাক্স, ট্রেড লাইসেন্স ও অনলাইন নাগরিক সনদ ব্যবস্থাপনা</span>
                                <span class="text-neutral-600">•</span>
                                <span>নিরাপদ ডাটা ব্যাকআপ ও রিয়েল-টাইম কিউআর কোড ভেরিফিকেশন সিস্টেম</span>
                                <span class="text-neutral-600">•</span>
                                <span>স্মার্ট ইউনিয়ন গড়তে আজই আপনার পরিষদের জন্য রেজিস্ট্রেশন করুন</span>
                                <span class="text-neutral-600">•</span>
                            </div>
                            <div class="marquee-group inline-flex items-center gap-6 pr-6" aria-hidden="true">
                                <span>বাংলাদেশের ইউনিয়ন পরিষদসমূহের জন্য তৈরি স্বয়ংক্রিয় ক্লাউড সলিউশন</span>
                                <span class="text-neutral-600">•</span>
                                <span>এক ক্লিকেই হোল্ডিং ট্যাক্স, ট্রেড লাইসেন্স ও অনলাইন নাগরিক সনদ ব্যবস্থাপনা</span>
                                <span class="text-neutral-600">•</span>
                                <span>নিরাপদ ডাটা ব্যাকআপ ও রিয়েল-টাইম কিউআর কোড ভেরিফিকেশন সিস্টেম</span>
                                <span class="text-neutral-600">•</span>
                                <span>স্মার্ট ইউনিয়ন গড়তে আজই আপনার পরিষদের জন্য রেজিস্ট্রেশন করুন</span>
                                <span class="text-neutral-600">•</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex shrink-0 items-center gap-4 text-[11px]">
                    <button type="button" data-toggle="modal" data-target="#supportModal"
                            class="flex cursor-pointer items-center gap-1.5 text-amber-400 transition-colors hover:text-amber-300">
                        <?= icon('headset', 14, 'bold') ?> ২৪/৭ হেল্পডেস্ক ও সাপোর্ট
                    </button>
                    <!--
                        The phone comes from satting_infos like every other
                        contact channel. It used to be a hand-written
                        tel:01813152799 with the DISPLAY TEXT set to the
                        literal placeholder "০১৭১৩-XXXXXX" — the bar read as
                        "01713-XXXXXX" while the link dialled a real number,
                        so the most prominent number on every page of the
                        product site was visibly unfinished.

                        The separator and the link are both dropped when
                        there is no number, rather than leaving a dangling
                        "|" beside nothing, and the support button goes
                        first in that case because the modal still works.
                    -->
                    <?php $ht_nav_c = platform_contact(); ?>
                    <?php if ($ht_nav_c['phone1_href'] !== ''): ?>
                    <span class="text-neutral-700">|</span>
                    <a href="<?= $ht_nav_c['phone1_href'] ?>" class="flex items-center gap-1 whitespace-nowrap transition-colors hover:text-white">
                        <?= icon('phone', 13, 'bold', 'text-indigo-400') ?> <?= $ht_nav_c['phone1_label'] ?>
                    </a>
                    <?php elseif ($ht_nav_c['phone2_href'] !== ''): ?>
                    <span class="text-neutral-700">|</span>
                    <a href="<?= $ht_nav_c['phone2_href'] ?>" class="flex items-center gap-1 whitespace-nowrap transition-colors hover:text-white">
                        <?= icon('phone', 13, 'bold', 'text-indigo-400') ?> <?= $ht_nav_c['phone2_label'] ?>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Main Navigation Header -->
        <header class="sticky top-0 z-40 border-b border-neutral-200 bg-white shadow-xs transition-all">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between sm:h-18">

                    <!-- Logo & Brand Name -->
                    <a href="<?= site_url() ?>"
                       class="group flex shrink-0 items-center gap-2.5 text-left">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-700 text-xl font-black text-white shadow-xs transition-colors group-hover:bg-indigo-800">
                            V
                        </div>
                        <div>
                            <div class="flex items-center gap-1.5">
                                <span class="text-xl font-black tracking-tight text-neutral-900 sm:text-2xl">
                                    Vireta<span class="text-indigo-600">Dev</span>
                                </span>
                                <span class="rounded border border-indigo-200 bg-indigo-100 px-1.5 py-0.5 text-[9px] font-bold tracking-wider text-indigo-700 uppercase">
                                    SaaS
                                </span>
                            </div>
                            <p class="hidden text-[10px] leading-none font-medium text-neutral-500 sm:block">
                                ইউনিয়ন পরিষদ ক্লাউড ই-গভর্ন্যান্স
                            </p>
                        </div>
                    </a>

                    <!-- Desktop Navigation Links -->
                    <nav class="hidden items-center gap-1 xl:flex">
                        <?php foreach ($saas_links as $saas_href => $saas_label): ?>
                            <a href="<?= site_url(ltrim($saas_href, '/')) ?>"
                               <?= $saas_path === $saas_href ? 'aria-current="page"' : '' ?>
                               class="<?= $saas_nav_class($saas_path === $saas_href) ?>"><?= $saas_label ?></a>
                        <?php endforeach; ?>
                    </nav>

<?php
$CI =& get_instance();
$is_logged_in = isset($CI->ion_auth) && $CI->ion_auth->logged_in();
$logged_user = $is_logged_in ? $CI->ion_auth->user()->row() : null;
$is_sadmin = $is_logged_in && ($CI->ion_auth->in_group('sadmin') || ($logged_user->u_type ?? '') === 'sadmin');
$target_url = $is_logged_in ? (function_exists('tenant_login_url') ? tenant_login_url($logged_user) : ($is_sadmin ? site_url('super_admin') : site_url('dashboard'))) : site_url('login');
$target_label = $is_sadmin ? 'সুপার অ্যাডমিন' : 'ড্যাশবোর্ড';
$target_icon = $is_sadmin ? 'shield-star' : 'gauge';
?>
                    <!-- Actions: Support + Login / Dashboard + Register -->
                    <div class="hidden shrink-0 items-center gap-2 sm:flex">
                        <button type="button" data-toggle="modal" data-target="#supportModal"
                                class="ht-btn ht-btn-secondary px-3 py-1.5 text-xs whitespace-nowrap">
                            <?= icon('headset', 15, 'bold', 'text-indigo-600') ?>
                            <span>সাপোর্ট</span>
                        </button>
                        <?php if ($is_logged_in): ?>
                            <a href="<?= $target_url ?>"
                               class="ht-btn px-3 py-1.5 text-xs whitespace-nowrap bg-indigo-600 hover:bg-indigo-700 text-white font-bold shadow-xs transition-all">
                                <?= icon($target_icon, 14, 'bold') ?>
                                <span><?= $target_label ?></span>
                            </a>
                            <a href="<?= site_url('logout') ?>"
                               class="ht-btn px-2.5 py-1.5 text-xs whitespace-nowrap text-rose-600 hover:bg-rose-50 border border-rose-200 transition-all font-semibold"
                               title="লগআউট">
                                <?= icon('sign-out', 14, 'bold') ?>
                            </a>
                        <?php else: ?>
                            <a href="<?= site_url('login') ?>"
                               class="ht-btn px-3 py-1.5 text-xs whitespace-nowrap <?= $saas_path === '/login' ? 'bg-neutral-900 text-white' : 'ht-btn-secondary text-neutral-800 hover:text-indigo-600' ?>">
                                <?= icon('lock-key', 14, 'bold') ?>
                                <span>লগইন</span>
                            </a>
                            <a href="<?= site_url('register') ?>"
                               class="ht-btn px-3.5 py-1.5 text-xs whitespace-nowrap shadow-xs hover:shadow-md <?= $saas_path === '/register' ? 'bg-indigo-900 text-white ring-2 ring-indigo-500' : 'ht-btn-primary' ?>">
                                <?= icon('sparkle', 14, 'fill') ?>
                                <span>নিবন্ধন</span>
                            </a>
                        <?php endif; ?>
                    </div>

                    <!-- Mobile Menu Button -->
                    <div class="flex xl:hidden">
                        <button type="button"
                                data-saas-toggle="mobile-nav"
                                aria-expanded="false" aria-controls="saasMobileNav"
                                class="cursor-pointer rounded-xl p-2 text-neutral-600 hover:bg-neutral-100"
                                aria-label="Toggle navigation">
                            <span data-saas-icon="open"><?= icon('list', 22, 'bold') ?></span>
                            <span data-saas-icon="close" class="hidden"><?= icon('x', 22, 'bold') ?></span>
                        </button>
                    </div>

                </div>
            </div>

            <!-- Mobile Navigation Menu. Hidden by default; saas.js toggles .hidden -->
            <div id="saasMobileNav" class="hidden space-y-2 border-t border-neutral-200 bg-white px-4 py-4 xl:hidden">
                <?php foreach ($saas_links as $saas_href => $saas_label): ?>
                    <a href="<?= site_url(ltrim($saas_href, '/')) ?>"
                       class="block w-full <?= $saas_nav_class($saas_path === $saas_href, 'text-sm font-semibold') ?>"><?= $saas_label ?></a>
                <?php endforeach; ?>
                <a href="<?= site_url('policy') ?>"
                    class="block w-full <?= $saas_nav_class($saas_path === '/policy', 'text-sm font-semibold') ?>">পলিসি ও শর্তাবলী (Policy)</a>
                <a href="<?= site_url('register') ?>"
                    class="block w-full <?= $saas_nav_class($saas_path === '/register', 'text-sm font-semibold') ?>">প্যাকেজ ও নিবন্ধন (Register)</a>
                <div class="flex flex-col gap-2 border-t border-neutral-100 pt-3">
                    <div class="flex gap-2">
                        <button type="button" data-toggle="modal" data-target="#supportModal"
                                class="ht-btn ht-btn-secondary flex-1 justify-center text-xs">
                            <?= icon('headset', 16, 'bold', 'text-indigo-600') ?>
                            <span>সাপোর্ট</span>
                        </button>
                        <?php if ($is_logged_in): ?>
                            <a href="<?= $target_url ?>"
                               class="ht-btn bg-indigo-600 text-white flex-1 justify-center text-xs font-bold shadow-xs">
                                <?= icon($target_icon, 14, 'bold') ?>
                                <span><?= $target_label ?></span>
                            </a>
                            <a href="<?= site_url('logout') ?>"
                               class="ht-btn ht-btn-secondary text-rose-600 px-3 justify-center text-xs" title="লগআউট">
                                <?= icon('sign-out', 14, 'bold') ?>
                            </a>
                        <?php else: ?>
                            <a href="<?= site_url('login') ?>"
                               class="ht-btn ht-btn-secondary flex-1 justify-center text-xs">
                                <?= icon('lock-key', 14, 'bold') ?>
                                <span>লগইন</span>
                            </a>
                        <?php endif; ?>
                    </div>
                    <?php if (!$is_logged_in): ?>
                        <a href="<?= site_url('register') ?>"
                           class="ht-btn ht-btn-primary w-full justify-center text-xs">
                            <?= icon('sparkle', 14, 'fill') ?>
                            <span>প্যাকেজ বুক ও নিবন্ধন করুন</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </header>
