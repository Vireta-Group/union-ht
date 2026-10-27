<?php
/**
 * Super-admin sidebar navigation.
 *
 * Load order matters: sa_head.php opens the flex row that wraps the
 * sidebar and the content area, and it opens <div class="flex ...">
 * immediately before this view. sa_foot.php closes it. There is no
 * layout inheritance — this is the third half of Template::sadmin()'s
 * flat sequence, not a partial of anything.
 *
 * The link list is $sa_nav, built by sadmin_nav_items($sa_seg) in the
 * helper, not a hardcoded <ul> here. That is what keeps the "current
 * page" highlight honest: the highlight and the href come from the same
 * array entry, so a link cannot point at a page whose highlight
 * disagrees with it.
 */
?>
<!-- Backdrop for the off-canvas sidebar below md. -->
<div id="saBackdrop"
     onclick="document.getElementById('saSidebar').classList.add('-translate-x-full'); this.classList.add('hidden')"
     class="hidden md:hidden fixed inset-0 z-40 bg-neutral-900/60 backdrop-blur-xs"></div>

<aside id="saSidebar"
       class="fixed inset-y-0 left-0 z-50 w-72 bg-white p-4 shadow-2xl -translate-x-full transition-transform duration-200 overflow-y-auto flex flex-col justify-between
              md:translate-x-0 md:z-auto md:w-56 lg:w-60 md:shrink-0 md:bg-white md:border md:border-neutral-200/80 md:rounded-2xl md:p-3 md:shadow-xs md:sticky md:top-20 md:self-start md:max-h-[calc(100vh-6rem)] md:overflow-y-auto">

    <div class="space-y-4">
        <!-- Mobile close button -->
        <div class="md:hidden flex items-center justify-between pb-3 border-b border-neutral-100">
            <span class="text-xs font-bold text-neutral-400 uppercase tracking-wider">মেনু</span>
            <button type="button"
                    onclick="document.getElementById('saSidebar').classList.add('-translate-x-full'); document.getElementById('saBackdrop').classList.add('hidden')"
                    class="p-1 rounded-lg text-neutral-400 hover:text-neutral-700 hover:bg-neutral-100">
                <i class="ph ph-x text-lg"></i>
            </button>
        </div>

        <nav class="space-y-1">
            <p class="px-3 pt-1 pb-2 text-[10px] font-bold uppercase tracking-wider text-neutral-400">
                <span class="lang-bn">প্ল্যাটফর্ম</span>
                <span class="lang-en">Platform</span>
            </p>

            <?php foreach ($sa_nav as $item):
                if (count($item) >= 5) {
                    list($segment, $label_bn, $label_en, $icon, $badge_key) = $item;
                } else {
                    list($segment, $label_bn, $icon, $badge_key) = $item;
                    $label_en = '';
                }
                $is_active = (($sa_seg ?? '') === $segment);
                $badge_val = ($badge_key !== NULL && !empty($sa_badges[$badge_key]))
                    ? (int) $sa_badges[$badge_key] : 0;
            ?>
                <a href="<?= base_url($segment); ?>"
                   <?= $is_active ? 'aria-current="page"' : '' ?>
                   class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs sm:text-sm font-semibold transition-all <?= $is_active
                        ? 'bg-indigo-600 text-white shadow-xs'
                        : 'text-neutral-600 hover:bg-neutral-100/80 hover:text-neutral-900' ?>">
                    <i class="ph <?= $icon ?> <?= $is_active ? 'text-white' : 'text-neutral-400' ?> text-lg shrink-0"></i>
                    <span class="flex-1 truncate">
                        <span class="lang-bn"><?= htmlspecialchars($label_bn, ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="lang-en"><?= htmlspecialchars(!empty($label_en) ? $label_en : $label_bn, ENT_QUOTES, 'UTF-8') ?></span>
                    </span>
                    <?php if ($badge_val > 0): ?>
                        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bengali-num <?= $is_active ? 'bg-white/20 text-white' : 'bg-rose-100 text-rose-700' ?>"><?= bn_number($badge_val) ?></span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>

            <?php if (!empty($sa_badges['expiring'])): ?>
                <a href="<?= base_url('sadmin_subscriptions'); ?>#expiring"
                   class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs bg-amber-50 text-amber-900 border border-amber-200/80 hover:bg-amber-100 transition-colors font-semibold mt-2">
                    <i class="ph ph-timer text-lg text-amber-600 shrink-0"></i>
                    <span class="flex-1 truncate">
                        <span class="lang-bn">মেয়াদ শেষ হওয়ার কাছাকাছি</span>
                        <span class="lang-en">Expiring Soon</span>
                    </span>
                    <span class="px-1.5 py-0.5 rounded-full bg-amber-200 text-amber-900 text-[10px] font-bold bengali-num"><?= bn_number($sa_badges['expiring']) ?></span>
                </a>
            <?php endif; ?>
        </nav>
    </div>

    <?php $sa_display_name = trim($sa_user->first_name ?? '') ?: ($sa_user->username ?? '—'); ?>
    <div class="pt-3 mt-4 border-t border-neutral-100">
        <div class="flex items-center gap-2.5 p-1 rounded-xl hover:bg-neutral-50 transition-colors">
            <div class="w-8 h-8 rounded-lg bg-neutral-900 text-white flex items-center justify-center text-xs font-black shrink-0 shadow-2xs">
                <?= htmlspecialchars(mb_substr((string) $sa_display_name, 0, 1), ENT_QUOTES, 'UTF-8') ?>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xs font-bold text-neutral-800 truncate leading-tight"><?= htmlspecialchars($sa_display_name, ENT_QUOTES, 'UTF-8') ?></p>
                <p class="text-[10px] text-neutral-400 truncate leading-tight mt-0.5"><?= htmlspecialchars($sa_user->email ?? '', ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <a href="<?= base_url('logout'); ?>" class="p-1.5 rounded-lg text-neutral-400 hover:text-rose-600 hover:bg-rose-50 transition-colors" title="লগআউট">
                <i class="ph ph-sign-out text-sm"></i>
            </a>
        </div>
    </div>
</aside>

<main class="flex-1 min-w-0 space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-neutral-200/70">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-neutral-900 tracking-tight">
                <span class="lang-bn"><?= htmlspecialchars($sa_title, ENT_QUOTES, 'UTF-8') ?></span>
                <span class="lang-en"><?= htmlspecialchars($sa_title_en ?? $sa_title, ENT_QUOTES, 'UTF-8') ?></span>
            </h1>
            <?php if (!empty($sa_desc)): ?>
                <p class="text-xs sm:text-sm text-neutral-500 mt-1">
                    <span class="lang-bn"><?= htmlspecialchars($sa_desc, ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="lang-en"><?= htmlspecialchars($sa_desc_en ?? $sa_desc, ENT_QUOTES, 'UTF-8') ?></span>
                </p>
            <?php endif; ?>
        </div>
    </div>
