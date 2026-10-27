<?php
/**
 * Super-admin overview.
 *
 * Rendered inside Template::sadmin(), so this file is a page and not a
 * document — sa_head.php has already opened <html> and the flex row, and
 * sa_nav.php has already drawn the sidebar and the page heading.
 */
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<!-- ===================== Metrics ===================== -->
<?php
$metrics = [
    [
        'label' => 'মোট ইউনিয়ন', 'label_en' => 'Total Tenants', 'value' => $total_tenants, 'is_currency' => false, 'icon' => 'ph-buildings',
        'sub'   => $suspended_count > 0 ? bn_number($suspended_count) . 'টি স্থগিত' : 'সবগুলো সক্রিয়',
        'sub_en' => $suspended_count > 0 ? $suspended_count . ' suspended' : 'All active',
        'sub_cls' => $suspended_count > 0 ? 'text-amber-600' : 'text-emerald-600',
        'href'  => 'sadmin_tenants',
    ],
    [
        'label' => 'চলতি মাসের আদায়', 'label_en' => 'This Month Sales', 'value' => $month_sales, 'is_currency' => true, 'icon' => 'ph-money',
        'sub'   => 'চলতি মাসে সংগৃহীত পেমেন্ট',
        'sub_en' => 'Collected this month',
        'sub_cls' => 'text-emerald-600',
        'href'  => 'sadmin_subscriptions',
    ],
    [
        'label' => 'মোট প্ল্যাটফর্ম রাজস্ব', 'label_en' => 'Total Collections', 'value' => $total_collected, 'is_currency' => true, 'icon' => 'ph-vault',
        'sub'   => ($total_collected_count > 0 ? bn_number($total_collected_count) . 'টি ইনভয়েস পেমেন্ট' : 'সাবস্ক্রিপশন ফি সংগ্রহ'),
        'sub_en' => $total_collected_count . ' invoices paid',
        'sub_cls' => 'text-indigo-600',
        'href'  => 'sadmin_subscriptions',
    ],
    [
        'label' => 'বাৎসরিক চুক্তি মূল্য (ARR)', 'label_en' => 'Annual Run Rate', 'value' => $total_arr, 'is_currency' => true, 'icon' => 'ph-chart-line-up',
        'sub' => 'সক্রিয় সাবস্ক্রিপশন চুক্তি ভ্যালু',
        'sub_en' => 'Active contracted ARR',
        'sub_cls' => 'text-neutral-500',
        'href' => 'sadmin_subscriptions',
    ],
    [
        'label' => 'ডোমেন ও ক্লাউড হোস্ট', 'label_en' => 'Domains & Hosts', 'value' => $total_domains, 'is_currency' => false, 'icon' => 'ph-globe',
        'sub'   => $is_development ? 'ডেভ হোস্টসহ সক্রিয়' : 'লাইভ হোস্টসহ সক্রিয়',
        'sub_en' => $is_development ? 'Incl. dev hosts' : 'Live hosts only',
        'sub_cls' => 'text-neutral-500', 'href' => 'sadmin_tenants',
    ],
    [
        'label' => 'মেয়াদ শেষের কাছাকাছি', 'label_en' => 'Expiring Soon', 'value' => $subs_expiring, 'is_currency' => false, 'icon' => 'ph-timer',
        'sub'   => $subs_expiring > 0 ? '৩০ দিনের মধ্যে মেয়াদ শেষ' : 'কোনোটিই নেই',
        'sub_en' => $subs_expiring > 0 ? 'Within 30 days' : 'None expiring',
        'sub_cls' => $subs_expiring > 0 ? 'text-amber-600' : 'text-emerald-600',
        'href'  => 'sadmin_subscriptions',
    ],
    [
        'label' => 'খোলা সাপোর্ট টিকিট', 'label_en' => 'Open Tickets', 'value' => $tickets_open, 'is_currency' => false, 'icon' => 'ph-lifebuoy',
        'sub'   => $tickets_urgent > 0 ? bn_number($tickets_urgent) . 'টি জরুরি' : 'জরুরি কোনোটি নেই',
        'sub_en' => $tickets_urgent > 0 ? $tickets_urgent . ' urgent' : 'No urgent tickets',
        'sub_cls' => $tickets_urgent > 0 ? 'text-rose-600' : 'text-emerald-600',
        'href'  => 'sadmin_support',
    ],
    [
        'label' => 'মেয়াদোত্তীর্ণ সাবস্ক্রিপশন', 'label_en' => 'Expired Subscriptions', 'value' => $subs_expired, 'is_currency' => false, 'icon' => 'ph-credit-card',
        'sub'   => $subs_total > 0 ? bn_number($subs_total) . 'টি মোট সাবস্ক্রিপশন' : 'কোনো সাবস্ক্রিপশন নেই',
        'sub_en' => $subs_total > 0 ? $subs_total . ' total subscriptions' : 'No subscriptions',
        'sub_cls' => $subs_expired > 0 ? 'text-rose-600' : 'text-emerald-600',
        'href'  => 'sadmin_subscriptions',
    ],
];
?>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
    <?php foreach ($metrics as $m): ?>
        <a href="<?= base_url($m['href']); ?>"
           class="group bg-white border border-neutral-200 rounded-2xl p-4 sm:p-5 shadow-xs hover:border-indigo-300 hover:shadow-sm transition-all">
            <div class="flex items-start justify-between gap-2">
                <span class="text-xs font-semibold text-neutral-600">
                    <span class="lang-bn"><?= $m['label'] ?></span>
                    <span class="lang-en"><?= $m['label_en'] ?? $m['label'] ?></span>
                </span>
                <i class="ph <?= $m['icon'] ?> text-lg text-neutral-300 group-hover:text-indigo-500 transition-colors shrink-0"></i>
            </div>
            <div class="mt-2 text-2xl sm:text-3xl font-black text-neutral-900 leading-none">
                <?php if (!empty($m['is_currency'])): ?>
                    <span class="lang-bn bengali-num">৳<?= bn_number(number_format($m['value'])) ?></span>
                    <span class="lang-en font-mono">৳<?= number_format($m['value']) ?></span>
                <?php else: ?>
                    <span class="lang-bn bengali-num"><?= bn_number(number_format($m['value'])) ?></span>
                    <span class="lang-en font-mono"><?= number_format($m['value']) ?></span>
                <?php endif; ?>
            </div>
            <div class="mt-1.5 text-[11px] font-medium <?= $m['sub_cls'] ?>">
                <span class="lang-bn"><?= $m['sub'] ?></span>
                <span class="lang-en"><?= $m['sub_en'] ?? $m['sub'] ?></span>
            </div>
        </a>
    <?php endforeach; ?>
</div>

<!-- ===================== Expiring + urgent ===================== -->
<div class="grid lg:grid-cols-2 gap-4 sm:gap-6">

    <!-- Expiring subscriptions -->
    <section class="bg-white border border-neutral-200 rounded-2xl shadow-xs overflow-hidden">
        <header class="flex items-center justify-between gap-3 px-4 sm:px-5 py-3.5 border-b border-neutral-200">
            <h2 class="text-sm font-bold text-neutral-900 flex items-center gap-2">
                <i class="ph ph-timer text-amber-600"></i>
                <span class="lang-bn">মেয়াদ শেষ হওয়ার কাছাকাছি</span>
                <span class="lang-en">Expiring Subscriptions</span>
            </h2>
            <a href="<?= base_url('sadmin_subscriptions'); ?>" class="text-xs font-semibold text-indigo-600 hover:underline">
                <span class="lang-bn">সব দেখুন</span>
                <span class="lang-en">View All</span>
            </a>
        </header>

        <?php if (empty($expiring_list)): ?>
            <div class="px-4 sm:px-5 py-10 text-center">
                <i class="ph ph-check-circle text-3xl text-emerald-400"></i>
                <p class="mt-2 text-sm text-neutral-500">
                    <span class="lang-bn">আগামী ৩০ দিনে কোনো সাবস্ক্রিপশনের মেয়াদ শেষ হচ্ছে না।</span>
                    <span class="lang-en">No subscriptions expiring in the next 30 days.</span>
                </p>
            </div>
        <?php else: ?>
            <ul class="divide-y divide-neutral-100">
                <?php foreach ($expiring_list as $s):
                    $left = subscription_days_left($s->end_date);
                ?>
                    <li class="px-4 sm:px-5 py-3 flex items-center justify-between gap-3 hover:bg-neutral-50 transition-colors">
                        <div class="min-w-0">
                            <a href="<?= base_url('sadmin_subscriptions'); ?>"
                               class="text-sm font-semibold text-neutral-800 hover:text-indigo-700 truncate block">
                                <?= htmlspecialchars($s->ins_name, ENT_QUOTES, 'UTF-8') ?>
                            </a>
                            <p class="text-[11px] text-neutral-500 mt-0.5">
                                <?= htmlspecialchars($s->name_bn, ENT_QUOTES, 'UTF-8') ?>
                                · <span class="lang-bn">শেষ <?= bn_number(date('d M Y', strtotime($s->end_date))) ?></span><span class="lang-en font-mono">Ends <?= date('d M Y', strtotime($s->end_date)) ?></span>
                            </p>
                        </div>
                        <?php
                            $badge_cls = ($left <= 7)
                                ? 'bg-rose-50 text-rose-700 border border-rose-200'
                                : 'bg-amber-50 text-amber-700 border border-amber-200';
                        ?>
                        <span class="shrink-0 px-2 py-1 rounded-lg text-[11px] font-bold <?= $badge_cls ?>">
                            <span class="lang-bn bengali-num"><?= bn_number($left) ?> দিন বাকি</span>
                            <span class="lang-en font-mono"><?= $left ?> days left</span>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <!-- Urgent tickets -->
    <section class="bg-white border border-neutral-200 rounded-2xl shadow-xs overflow-hidden">
        <header class="flex items-center justify-between gap-3 px-4 sm:px-5 py-3.5 border-b border-neutral-200">
            <h2 class="text-sm font-bold text-neutral-900 flex items-center gap-2">
                <i class="ph ph-fire text-rose-600"></i>
                <span class="lang-bn">জরুরি সাপোর্ট টিকিট</span>
                <span class="lang-en">Urgent Support Tickets</span>
            </h2>
            <a href="<?= base_url('sadmin_support'); ?>" class="text-xs font-semibold text-indigo-600 hover:underline">
                <span class="lang-bn">সব দেখুন</span>
                <span class="lang-en">View All</span>
            </a>
        </header>

        <?php if (empty($urgent_tickets)): ?>
            <div class="px-4 sm:px-5 py-10 text-center">
                <i class="ph ph-check-circle text-3xl text-emerald-400"></i>
                <p class="mt-2 text-sm text-neutral-500">
                    <span class="lang-bn">কোনো জরুরি টিকিট নেই।</span>
                    <span class="lang-en">No urgent tickets found.</span>
                </p>
            </div>
        <?php else: ?>
            <ul class="divide-y divide-neutral-100">
                <?php foreach ($urgent_tickets as $t): ?>
                    <li class="px-4 sm:px-5 py-3 hover:bg-neutral-50 transition-colors">
                        <a href="<?= base_url('sadmin_support/ticket/' . $t->ticket_id); ?>"
                           class="text-sm font-semibold text-neutral-800 hover:text-indigo-700 truncate block">
                            <?= htmlspecialchars($t->subject, ENT_QUOTES, 'UTF-8') ?>
                        </a>
                        <p class="text-[11px] text-neutral-500 mt-0.5">
                            <span class="font-mono"><?= htmlspecialchars($t->ticket_no, ENT_QUOTES, 'UTF-8') ?></span>
                            · <?= bn_number(date('d M Y', strtotime($t->created_at))) ?>
                            <?php if (!empty($t->user_name)): ?>
                                · <?= htmlspecialchars($t->user_name, ENT_QUOTES, 'UTF-8') ?>
                            <?php endif; ?>
                        </p>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
</div>

<!-- ===================== Recent activity ===================== -->
<section class="bg-white border border-neutral-200 rounded-2xl shadow-xs overflow-hidden">
    <header class="flex items-center justify-between gap-3 px-4 sm:px-5 py-3.5 border-b border-neutral-200">
        <h2 class="text-sm font-bold text-neutral-900 flex items-center gap-2">
            <i class="ph ph-scroll text-neutral-500"></i>
            <span class="lang-bn">সাম্প্রতিক কার্যক্রম</span>
            <span class="lang-en">Recent Activities</span>
        </h2>
        <a href="<?= base_url('sadmin_audit'); ?>" class="text-xs font-semibold text-indigo-600 hover:underline">
            <span class="lang-bn">পূর্ণ অডিট লগ</span>
            <span class="lang-en">Full Audit Log</span>
        </a>
    </header>

    <?php if (empty($recent_audit)): ?>
        <div class="px-4 sm:px-5 py-10 text-center">
            <i class="ph ph-clock-counter-clockwise text-3xl text-neutral-300"></i>
            <p class="mt-2 text-sm text-neutral-500">এখনও কোনো কার্যক্রম রেকর্ড হয়নি।</p>
            <p class="mt-1 text-[11px] text-neutral-400 max-w-md mx-auto">
                অডিট লগ ২০২৬-০৯-৩০ তারিখে চালু হয়েছে। এর আগের কোনো কার্যক্রমের রেকর্ড নেই এবং পূরণ করা হয়নি।
            </p>
        </div>
    <?php else: ?>
        <ul class="divide-y divide-neutral-100">
            <?php foreach ($recent_audit as $a): ?>
                <li class="px-4 sm:px-5 py-3 flex items-start gap-3 hover:bg-neutral-50 transition-colors">
                    <span class="shrink-0 mt-0.5 w-7 h-7 rounded-lg bg-neutral-100 text-neutral-500 flex items-center justify-center">
                        <i class="ph <?= match ($a->action) {
                            'create' => 'ph-plus',
                            'update' => 'ph-pencil-simple',
                            'delete' => 'ph-trash',
                            'login'  => 'ph-sign-in',
                            'login_failed' => 'ph-warning',
                            'logout' => 'ph-sign-out',
                            'toggle' => 'ph-toggle-right',
                            'send'   => 'ph-paper-plane-tilt',
                            default  => 'ph-eye',
                        } ?> text-sm"></i>
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs text-neutral-800 leading-relaxed">
                            <?php if (!empty($a->summary)): ?>
                                <?= htmlspecialchars($a->summary, ENT_QUOTES, 'UTF-8') ?>
                            <?php else: ?>
                                <span class="font-semibold"><?= htmlspecialchars($a->action, ENT_QUOTES, 'UTF-8') ?></span>
                                on <span class="font-mono"><?= htmlspecialchars($a->entity, ENT_QUOTES, 'UTF-8') ?></span>
                                <?php if (!empty($a->entity_id)): ?>
                                    #<?= htmlspecialchars($a->entity_id, ENT_QUOTES, 'UTF-8') ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        </p>
                        <p class="text-[11px] text-neutral-400 mt-0.5">
                            <?= htmlspecialchars($a->actor_name ?: 'সিস্টেম', ENT_QUOTES, 'UTF-8') ?>
                            · <?= bn_number(date('d M Y, h:i A', strtotime($a->created_at))) ?>
                        </p>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>

<!--
    The environment badge, at the bottom, plainly.

    Not decoration. A panel that lists every parishad in the platform while
    running against development tenants, with a development override
    (switch_tenant's ?tenant=N) live, is a different thing from the panel
    an operator sees in production, and nothing else on the page would
    tell them which one they are looking at.
-->
<?php
    $dev_badge_cls = $is_development
        ? 'bg-amber-100 text-amber-800 border border-amber-300'
        : 'bg-emerald-100 text-emerald-800 border border-emerald-300';
?>
<div class="flex items-center justify-center">
    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[11px] font-bold <?= $dev_badge_cls ?>">
        <i class="ph <?= $is_development ? 'ph-flask' : 'ph-shield-check' ?>"></i>
        <?= $is_development
            ? 'ডেভেলপমেন্ট মোড — ডেভ হোস্ট ও টেন্যান্ট ওভাররাইড সক্রিয়'
            : 'প্রোডাকশন মোড' ?>
    </span>
</div>
