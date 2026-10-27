<?php
/**
 * Subscriptions module.
 *
 * Rendered inside Template::sadmin(). The page heading and sidebar come
 * from the shell; this file is the content.
 *
 * ONE PAGE, THREE THINGS TO READ
 * The summary is deliberately at the top and computed over the whole
 * table, not over the current filter — a "মোট" that changes when you
 * filter is a "মোট" that lies. The tenant table is below it, and the
 * packages editor is a separate panel further down rather than a
 * separate page, because a sadmin changing a price and a sadmin looking
 * at who is on that price are usually in the same session.
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Status a tenant actually presents to the panel, which is not always the
 * status in the column.
 *
 * 'active' with a date in the past is possible — nothing in the schema
 * prevents it, api_renew() is not the only way a date is written, and the
 * migration seeded from a legacy column that was empty for three tenants.
 * Showing the stored status there would tell the operator a lapsed
 * parishad is fine. A tenant with no row at all is a third thing again:
 * unassigned, not cancelled.
 */
function sa_sub_effective_status($row)
{
    if (empty($row->subscription_id)) {
        return 'unassigned';
    }
    if ($row->status === 'active' && $row->end_date < date('Y-m-d')) {
        return 'expired';
    }
    return $row->status;
}
?>

<!-- ===================== Summary ===================== -->
<?php
$summary = [
    ['label' => 'মোট সাবস্ক্রিপশন', 'value' => $subs_total,   'icon' => 'ph-credit-card', 'href' => '',                 'cls' => 'text-neutral-900'],
    ['label' => 'সক্রিয়',            'value' => $subs_active,  'icon' => 'ph-check-circle','href' => '?status=active',  'cls' => 'text-emerald-600'],
    ['label' => 'মেয়াদ শেষ / বাতিল',  'value' => $subs_lapsed,  'icon' => 'ph-warning',     'href' => '?status=lapsed',  'cls' => $subs_lapsed > 0 ? 'text-rose-600' : 'text-neutral-400'],
    ['label' => 'প্যাকেজ নেই',        'value' => $subs_unassigned, 'icon' => 'ph-question',  'href' => '?status=unassigned', 'cls' => $subs_unassigned > 0 ? 'text-amber-600' : 'text-neutral-400'],
];
?>
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
    <?php foreach ($summary as $s):
        $qs = $s['href'];
        $is_on = ($filter === ltrim($qs, '?status=')) && $qs !== '';
    ?>
        <?php if ($qs === ''): ?>
            <div class="bg-white border border-neutral-200 rounded-2xl p-4 sm:p-5 shadow-xs">
        <?php else: ?>
            <a href="<?= base_url('sadmin_subscriptions' . $qs); ?>"
               class="bg-white border <?= $is_on ? 'border-indigo-400 ring-1 ring-indigo-200' : 'border-neutral-200 hover:border-indigo-300' ?> rounded-2xl p-4 sm:p-5 shadow-xs hover:shadow-sm transition-all">
        <?php endif; ?>
            <div class="flex items-start justify-between gap-2">
                <span class="text-xs font-semibold text-neutral-500"><?= $s['label'] ?></span>
                <i class="ph <?= $s['icon'] ?> text-lg text-neutral-300 shrink-0"></i>
            </div>
            <div class="mt-2 text-2xl sm:text-3xl font-black <?= $s['cls'] ?> bengali-num leading-none">
                <?= bn_number(number_format($s['value'])) ?>
            </div>
        <?php if ($qs === ''): ?>
            </div>
        <?php else: ?>
            </a>
        <?php endif; ?>
    <?php endforeach; ?>
</div>

<!--
    Money.

    The "বকেয়া" figure is the price of each active tenant's current term,
    NOT a computed balance. It is labelled as such, in the text below the
    numbers, because the difference is the whole point: a tenant who paid
    two years up front still counts their current term as outstanding here,
    and an operator who assumes otherwise will chase money that is already
    in the bank. Making the number wrong-but-obvious beats making it
    precise-looking.
-->
<div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
    <div class="bg-white border border-neutral-200 rounded-2xl p-4 sm:p-5 shadow-xs flex items-start gap-3">
        <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0">
            <i class="ph ph-wallet"></i>
        </div>
        <div class="min-w-0">
            <p class="text-xs font-semibold text-neutral-500">আদায়কৃত</p>
            <p class="text-2xl font-black text-neutral-900 bengali-num mt-0.5"><?= htmlspecialchars(taka($collected), ENT_QUOTES, 'UTF-8') ?></p>
            <p class="text-[11px] text-neutral-400 mt-0.5">
                <?= bn_number($collected_count) ?>টি পেমেন্ট রেকর্ড থেকে
            </p>
        </div>
    </div>
    <div class="bg-white border border-neutral-200 rounded-2xl p-4 sm:p-5 shadow-xs flex items-start gap-3">
        <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shrink-0">
            <i class="ph ph-hourglass-medium"></i>
        </div>
        <div class="min-w-0">
            <p class="text-xs font-semibold text-neutral-500">বর্তমান মেয়াদের মূল্য</p>
            <p class="text-2xl font-black text-neutral-900 bengali-num mt-0.5"><?= htmlspecialchars(taka($outstanding), ENT_QUOTES, 'UTF-8') ?></p>
            <p class="text-[11px] text-neutral-400 mt-0.5">সক্রিয় সাবস্ক্রিপশনসমূহের বাৎসরিক মোট মূল্যমান</p>
        </div>
    </div>
</div>

<!-- ===================== Filter bar ===================== -->
<div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:flex-wrap">
    <a href="<?= base_url('sadmin_subscriptions'); ?>"
       class="whitespace-nowrap px-3 py-1.5 rounded-lg text-xs font-bold <?= $filter === '' ? 'bg-neutral-900 text-white' : 'bg-white border border-neutral-200 text-neutral-600 hover:bg-neutral-50' ?>">
        <span class="lang-bn">সবাই</span><span class="lang-en">All</span>
    </a>
    <a href="<?= base_url('sadmin_subscriptions?status=active'); ?>"
       class="whitespace-nowrap px-3 py-1.5 rounded-lg text-xs font-bold <?= $filter === 'active' ? 'bg-neutral-900 text-white' : 'bg-white border border-neutral-200 text-neutral-600 hover:bg-neutral-50' ?>">
        <span class="lang-bn">সক্রিয়</span><span class="lang-en">Active</span>
    </a>
    <a href="<?= base_url('sadmin_subscriptions?status=expiring'); ?>#expiring"
       class="whitespace-nowrap px-3 py-1.5 rounded-lg text-xs font-bold <?= $filter === 'expiring' ? 'bg-neutral-900 text-white' : 'bg-white border border-neutral-200 text-neutral-600 hover:bg-neutral-50' ?>">
        <span class="lang-bn">৩০ দিনে মেয়াদ শেষ</span><span class="lang-en">Expiring in 30d</span>
    </a>
    <a href="<?= base_url('sadmin_subscriptions?status=lapsed'); ?>"
       class="whitespace-nowrap px-3 py-1.5 rounded-lg text-xs font-bold <?= $filter === 'lapsed' ? 'bg-neutral-900 text-white' : 'bg-white border border-neutral-200 text-neutral-600 hover:bg-neutral-50' ?>">
        <span class="lang-bn">মেয়াদ শেষ / বাতিল</span><span class="lang-en">Expired / Cancelled</span>
    </a>
    <a href="<?= base_url('sadmin_subscriptions?status=unassigned'); ?>"
       class="whitespace-nowrap px-3 py-1.5 rounded-lg text-xs font-bold <?= $filter === 'unassigned' ? 'bg-neutral-900 text-white' : 'bg-white border border-neutral-200 text-neutral-600 hover:bg-neutral-50' ?>">
        <span class="lang-bn">প্যাকেজ নেই</span><span class="lang-en">Unassigned</span>
    </a>
</div>

<!-- ===================== Tenant subscriptions ===================== -->
<section id="expiring" class="min-w-0 bg-white border border-neutral-200 rounded-2xl shadow-xs overflow-hidden">
    <header class="flex items-center justify-between gap-3 px-4 sm:px-5 py-3.5 border-b border-neutral-200">
        <h2 class="text-sm font-bold text-neutral-900 flex items-center gap-2">
            <i class="ph ph-buildings text-neutral-500"></i>
            <span class="lang-bn">ইউনিয়ন ও তাদের সাবস্ক্রিপশন</span>
            <span class="lang-en">Tenants & Subscriptions</span>
        </h2>
        <span class="text-xs text-neutral-500">
            <span class="lang-bn bengali-num"><?= bn_number(count($rows)) ?>টি দেখানো হচ্ছে</span>
            <span class="lang-en font-mono">Showing <?= count($rows) ?></span>
        </span>
    </header>

    <?php if (empty($rows)): ?>
        <div class="px-4 sm:px-5 py-12 text-center">
            <i class="ph ph-funnel text-3xl text-neutral-300"></i>
            <p class="mt-2 text-sm text-neutral-500">
                <span class="lang-bn">এই ফিল্টারে কোনো ইউনিয়ন নেই।</span>
                <span class="lang-en">No tenants match this filter.</span>
            </p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
        <table class="w-full min-w-[900px]">
            <thead class="bg-neutral-50 border-b border-neutral-200">
                <tr>
                    <th class="py-3 px-4 text-left text-[11px] font-bold uppercase tracking-wider text-neutral-500"><span class="lang-bn">ইউনিয়ন</span><span class="lang-en">Union / Parishad</span></th>
                    <th class="py-3 px-4 text-left text-[11px] font-bold uppercase tracking-wider text-neutral-500"><span class="lang-bn">প্যাকেজ</span><span class="lang-en">Package / Plan</span></th>
                    <th class="py-3 px-4 text-left text-[11px] font-bold uppercase tracking-wider text-neutral-500"><span class="lang-bn">মেয়াদ</span><span class="lang-en">Duration & Expiry</span></th>
                    <th class="py-3 px-4 text-left text-[11px] font-bold uppercase tracking-wider text-neutral-500"><span class="lang-bn">অবস্থা</span><span class="lang-en">Status</span></th>
                    <th class="py-3 px-4 text-left text-[11px] font-bold uppercase tracking-wider text-neutral-500"><span class="lang-bn">পেমেন্ট</span><span class="lang-en">Payments</span></th>
                    <th class="py-3 px-4 text-right text-[11px] font-bold uppercase tracking-wider text-neutral-500 pr-5"><span class="lang-bn">অ্যাকশন</span><span class="lang-en">Actions</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-100">
            <?php foreach ($rows as $r):
                $eff   = sa_sub_effective_status($r);
                $left  = $r->end_date ? subscription_days_left($r->end_date) : NULL;
                $pays  = $r->subscription_id ? ($payments_by_sub[$r->subscription_id] ?? []) : [];
                $paid_sum = 0;
                foreach ($pays as $p) { if ($p->paid_at !== NULL) $paid_sum += (int) $p->amount; }
            ?>
                <tr class="hover:bg-neutral-50 transition-colors">

                    <td class="py-3.5 px-4">
                        <p class="text-sm font-semibold text-neutral-900"><?= htmlspecialchars($r->ins_name, ENT_QUOTES, 'UTF-8') ?></p>
                        <p class="text-[11px] text-neutral-500 font-mono mt-0.5">
                            <?= htmlspecialchars($r->domain_names ?: $r->sub_domains, ENT_QUOTES, 'UTF-8') ?>
                        </p>
                        <?php if (!$r->is_active): ?>
                            <span class="inline-block mt-1 px-1.5 py-0.5 rounded bg-neutral-100 text-neutral-600 text-[10px] font-bold">টেন্যান্ট স্থগিত</span>
                        <?php endif; ?>
                    </td>

                    <td class="py-3.5 px-4 text-xs">
                        <?php if ($r->name_bn): ?>
                            <p class="font-bold text-neutral-800"><?= htmlspecialchars($r->name_bn, ENT_QUOTES, 'UTF-8') ?></p>
                            <p class="text-neutral-500 mt-0.5"><?= htmlspecialchars(taka($r->price_yearly), ENT_QUOTES, 'UTF-8') ?>/বছর</p>
                        <?php else: ?>
                            <span class="text-amber-600 font-bold">নিয়োগ করা হয়নি</span>
                        <?php endif; ?>
                    </td>

                    <td class="py-3.5 px-4 text-xs">
                        <?php if ($r->end_date): ?>
                            <p class="font-semibold text-neutral-800 bengali-num"><?= bn_number(date('d M Y', strtotime($r->end_date))) ?></p>
                            <p class="text-neutral-500 mt-0.5">
                                শুরু <?= bn_number(date('d M Y', strtotime($r->start_date))) ?>
                                · <?= $r->billing_cycle === 'monthly' ? 'মাসিক' : 'বার্ষিক' ?>
                            </p>
                        <?php else: ?>
                            <span class="text-neutral-400">—</span>
                        <?php endif; ?>
                    </td>

                    <td class="py-3.5 px-4">
                        <?php
                        $chip_key = ($eff === 'unassigned') ? 'pending' : $eff;
                        list($lbl, $cls) = status_chip($chip_key);
                        if ($eff === 'unassigned') { $lbl = 'নিয়োগ হয়নি'; }
                        ?>
                        <span class="inline-block px-2 py-1 rounded-lg border text-[11px] font-bold <?= $cls ?>"><?= $lbl ?></span>
                        <?php if ($left !== NULL && $eff === 'active'): ?>
                            <p class="text-[11px] mt-1 font-bold <?= $left <= 30 ? 'text-rose-600' : 'text-neutral-500' ?> bengali-num">
                                <?= bn_number($left) ?> দিন বাকি
                            </p>
                        <?php endif; ?>
                    </td>

                    <td class="py-3.5 px-4 text-xs">
                        <?php if (empty($pays)): ?>
                            <span class="text-neutral-400">কোনো রেকর্ড নেই</span>
                        <?php else: ?>
                            <p class="font-bold text-neutral-800 bengali-num"><?= htmlspecialchars(taka($paid_sum), ENT_QUOTES, 'UTF-8') ?></p>
                            <p class="text-[11px] text-neutral-500 mt-0.5"><?= bn_number(count($pays)) ?>টি রেকর্ড</p>
                            <button type="button"
                                    onclick="saTogglePayments(<?= (int) $r->subscription_id; ?>, this)"
                                    class="mt-1 text-[11px] font-bold text-indigo-600 hover:underline cursor-pointer">
                                বিস্তারিত দেখুন
                            </button>
                            <div id="sa-pay-<?= (int) $r->subscription_id; ?>" class="hidden mt-2 pl-3 border-l-2 border-neutral-200 space-y-1.5">
                                <?php foreach ($pays as $p): ?>
                                    <div class="text-[11px]">
                                        <span class="font-mono text-neutral-600"><?= htmlspecialchars($p->invoice_no, ENT_QUOTES, 'UTF-8') ?></span>
                                        <span class="font-bold text-neutral-800 bengali-num"><?= htmlspecialchars(taka($p->amount), ENT_QUOTES, 'UTF-8') ?></span>
                                        <span class="text-neutral-500">
                                            · <?= htmlspecialchars($payment_methods[$p->payment_method] ?? $p->payment_method, ENT_QUOTES, 'UTF-8') ?>
                                            · <?= $p->paid_at ? bn_number(date('d M Y', strtotime($p->paid_at))) : 'অপরিশোধিত' ?>
                                        </span>
                                        <?php if (!empty($p->reference)): ?>
                                            <span class="block text-neutral-400">রেফারেন্স: <?= htmlspecialchars($p->reference, ENT_QUOTES, 'UTF-8') ?></span>
                                        <?php endif; ?>
                                        <button type="button"
                                                onclick="saDeletePayment(<?= (int) $p->payment_id; ?>, '<?= htmlspecialchars($p->invoice_no, ENT_QUOTES, 'UTF-8'); ?>')"
                                                class="text-rose-600 hover:underline font-bold mt-0.5">রেকর্ড মুছুন</button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </td>

                    <td class="py-3.5 px-4 text-right pr-5">
                        <?php if ($r->subscription_id): ?>
                            <div class="inline-flex flex-wrap items-center justify-end gap-1.5">
                                <button type="button"
                                        onclick="saRenew(<?= (int) $r->subscription_id; ?>, '<?= htmlspecialchars($r->ins_name, ENT_QUOTES, 'UTF-8'); ?>', '<?= htmlspecialchars(date('Y-m-d', strtotime($r->end_date)), ENT_QUOTES, 'UTF-8'); ?>', '<?= $r->billing_cycle === 'monthly' ? 'month' : 'year' ?>')"
                                        title="মেয়াদ একটি বিলিং চক্রের জন্য বাড়ান"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-300 font-bold text-xs transition-colors cursor-pointer">
                                    <i class="ph ph-arrows-clockwise text-xs"></i> নবায়ন
                                </button>
                                <button type="button"
                                        onclick="saOpenAssign(<?= (int) $r->subscription_id; ?>, <?= (int) $r->package_id ?? 0 ?>)"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-800 border border-indigo-300 font-bold text-xs transition-colors cursor-pointer">
                                    <i class="ph ph-swap text-xs"></i> প্যাকেজ
                                </button>
                                <button type="button"
                                        onclick="saOpenDates(<?= (int) $r->subscription_id; ?>, '<?= htmlspecialchars($r->start_date, ENT_QUOTES, 'UTF-8'); ?>', '<?= htmlspecialchars($r->end_date, ENT_QUOTES, 'UTF-8'); ?>', '<?= htmlspecialchars($r->status, ENT_QUOTES, 'UTF-8'); ?>', '<?= htmlspecialchars($r->note, ENT_QUOTES, 'UTF-8'); ?>')"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-neutral-100 hover:bg-neutral-200 text-neutral-800 border border-neutral-300 font-bold text-xs transition-colors cursor-pointer">
                                    <i class="ph ph-calendar-blank text-xs"></i> তারিখ
                                </button>
                                <button type="button"
                                        onclick="saOpenPayment(<?= (int) $r->subscription_id; ?>, '<?= htmlspecialchars($r->ins_name, ENT_QUOTES, 'UTF-8'); ?>')"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-neutral-900 hover:bg-neutral-800 text-white font-bold text-xs transition-colors cursor-pointer">
                                    <i class="ph ph-currency-bdt text-xs"></i> পেমেন্ট
                                </button>
                                <button type="button"
                                        onclick="saCancel(<?= (int) $r->subscription_id; ?>, '<?= htmlspecialchars($r->ins_name, ENT_QUOTES, 'UTF-8'); ?>', <?= count($pays) ?>)"
                                        title="সাবস্ক্রিপশন বাতিল ও পেমেন্ট রেকর্ড আর্কাইভ"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-800 border border-rose-300 font-bold text-xs transition-colors cursor-pointer">
                                    <i class="ph ph-x-circle text-xs"></i>
                                </button>
                            </div>
                        <?php else: ?>
                            <a href="<?= base_url('sadmin_tenants'); ?>"
                               class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-300 font-bold text-xs transition-colors">
                                <i class="ph ph-arrow-square-out text-xs"></i>
                                ইউনিয়ন পাতা থেকে নিয়োগ
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
</section>

<!-- ===================== Packages ===================== -->
<section class="bg-white border border-neutral-200 rounded-2xl shadow-xs overflow-hidden">
    <header class="flex items-center justify-between gap-3 px-4 sm:px-5 py-3.5 border-b border-neutral-200">
        <h2 class="text-sm font-bold text-neutral-900 flex items-center gap-2">
            <i class="ph ph-package text-neutral-500"></i>
            প্যাকেজ সম্ভার
        </h2>
        <button type="button" onclick="saOpenPackage()"
                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs transition-colors cursor-pointer">
            <i class="ph ph-plus"></i> নতুন প্যাকেজ
        </button>
    </header>

    <div class="p-3 sm:p-4">
        <p class="text-[11px] text-emerald-800 bg-emerald-50 border border-emerald-200 rounded-lg px-3 py-2 mb-3">
            <i class="ph ph-check-circle"></i>
            এখানে প্যাকেজ, মূল্য বা ফিচার পরিবর্তন করলে তা স্বয়ংক্রিয়ভাবে পাবলিক প্যাকেজ পেজ (<span class="font-mono">plans</span>) এবং রেজিস্ট্রেশন ফর্মে লাইভ কার্যকর হবে।
        </p>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">
            <?php foreach ($packages as $p): ?>
                <div class="border <?= $p->is_active ? 'border-neutral-200' : 'border-neutral-200 opacity-60' ?> rounded-xl p-4">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="text-sm font-black text-neutral-900 truncate"><?= htmlspecialchars($p->name_bn, ENT_QUOTES, 'UTF-8') ?></p>
                            <p class="text-[11px] text-neutral-400 font-mono mt-0.5"><?= htmlspecialchars($p->package_key, ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                        <?php if (!$p->is_active): ?>
                            <span class="shrink-0 px-1.5 py-0.5 rounded bg-neutral-200 text-neutral-600 text-[10px] font-bold">নিষ্ক্রিয়</span>
                        <?php endif; ?>
                    </div>

                    <div class="mt-3 flex items-baseline gap-3">
                        <span class="text-lg font-black text-neutral-900 bengali-num"><?= htmlspecialchars(taka($p->price_yearly), ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="text-[11px] text-neutral-400">/ বছর</span>
                        <span class="text-xs text-neutral-500 bengali-num"><?= htmlspecialchars(taka($p->price_monthly), ENT_QUOTES, 'UTF-8') ?> / মাস</span>
                    </div>

                    <div class="mt-2 text-[11px] text-neutral-500 space-y-0.5">
                        <p>হোল্ডিং সীমা: <?= $p->max_holdings === NULL ? 'আনলিমিটেড' : bn_number($p->max_holdings) ?></p>
                        <p>ইউজার সীমা: <?= $p->max_users === NULL ? 'আনলিমিটেড' : bn_number($p->max_users) ?></p>
                    </div>

                    <button type="button"
                            onclick='saOpenPackage(<?= htmlspecialchars(json_encode([
                                'package_id'    => (int) $p->package_id,
                                'package_key'   => $p->package_key,
                                'name_bn'       => $p->name_bn,
                                'name_en'       => $p->name_en,
                                'price_monthly' => (int) $p->price_monthly,
                                'price_yearly'  => (int) $p->price_yearly,
                                'max_holdings'  => $p->max_holdings,
                                'max_users'     => $p->max_users,
                                'features'      => $p->features,
                                'sort_order'    => (int) $p->sort_order,
                                'is_active'     => (bool) $p->is_active,
                            ], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?>)'
                            class="mt-3 w-full px-3 py-1.5 rounded-lg bg-neutral-100 hover:bg-neutral-200 text-neutral-800 font-bold text-xs transition-colors cursor-pointer">
                        সম্পাদনা
                    </button>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===================== Modals ===================== -->
<div id="saModal" class="fixed inset-0 z-50 bg-neutral-900/60 backdrop-blur-xs hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-4 sm:p-6 shadow-xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-3 border-b border-neutral-200">
            <h3 id="saModalTitle" class="text-base font-bold text-neutral-900"></h3>
            <button type="button" onclick="saCloseModal()" class="text-neutral-400 hover:text-neutral-700 cursor-pointer">
                <i class="ph ph-x text-lg"></i>
            </button>
        </div>
        <div id="saModalBody" class="mt-4 text-xs"></div>
        <div id="saModalMsg" class="mt-3 hidden"></div>
    </div>
</div>

<script>
    const SA = (path, payload) => fetch('<?= base_url('sadmin_subscriptions/'); ?>' + path, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(payload)
    }).then(r => r.json());

    function saOpen(title, html) {
        document.getElementById('saModalTitle').textContent = title;
        document.getElementById('saModalBody').innerHTML = html;
        document.getElementById('saModalMsg').className = 'mt-3 hidden';
        const m = document.getElementById('saModal');
        m.classList.remove('hidden');
        m.classList.add('flex');
    }

    function saCloseModal() {
        const m = document.getElementById('saModal');
        m.classList.add('hidden');
        m.classList.remove('flex');
    }

    function saMsg(kind, text) {
        const box = document.getElementById('saModalMsg');
        const cls = kind === 'ok'
            ? 'mt-3 p-3 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200'
            : 'mt-3 p-3 rounded-xl bg-rose-50 text-rose-700 border border-rose-200';
        box.className = cls;
        box.textContent = text;
    }

    function saTogglePayments(subId, btn) {
        const box = document.getElementById('sa-pay-' + subId);
        const hidden = box.classList.toggle('hidden');
        btn.textContent = hidden ? 'বিস্তারিত দেখুন' : 'বিস্তারিত লুকান';
    }

    const SA_INPUT = 'w-full p-2.5 rounded-lg border border-neutral-300 text-xs focus:border-indigo-600 focus:outline-none';
    const SA_LABEL = 'block font-bold text-neutral-700 mb-1';

    function saAddDays(days) {
        const endInp = document.getElementById('saEnd');
        const startInp = document.getElementById('saStart');
        let base = (endInp && endInp.value) ? new Date(endInp.value) : (startInp && startInp.value ? new Date(startInp.value) : new Date());
        if (isNaN(base.getTime())) base = new Date();
        base.setDate(base.getDate() + parseInt(days, 10));
        const y = base.getFullYear();
        const m = String(base.getMonth() + 1).padStart(2, '0');
        const d = String(base.getDate()).padStart(2, '0');
        if (endInp) endInp.value = `${y}-${m}-${d}`;
    }

    function saRenew(subId, tenantName, currentEnd, cycle) {
        const unit = cycle === 'month' ? '১ মাস' : '১ বছর';
        vConfirm(tenantName + ' — মেয়াদ নবায়ন', 'আপনি কি নিশ্চিত যে এই ইউনিয়ন পরিষদের সাবস্ক্রিপশনের মেয়াদ ' + currentEnd + ' থেকে আরও ' + unit + ' বৃদ্ধি করে সক্রিয় করতে চান?').then(function(confirmed) {
            if (!confirmed) return;
            SA('api_renew', {subscription_id: subId})
                .then(r => { if (r.success) { vAlert(r.message, 'সফল', 'success').then(() => location.reload()); } else vAlert(r.message, 'ত্রুটি', 'error'); })
                .catch(() => vAlert('সার্ভারে সংযোগ স্থাপন করা যায়নি।', 'ত্রুটি', 'error'));
        });
    }

    function saCancel(subId, tenantName, payCount) {
        const payWarn = payCount > 0
            ? '\n\nসতর্কতা: এই সাবস্ক্রিপশনে ' + payCount + 'টি পেমেন্ট রেকর্ড যুক্ত আছে।'
            : '';
        vConfirm(tenantName + ' — সাবস্ক্রিপশন বাতিল', 'আপনি কি নিশ্চিত যে এই ইউনিয়ন পরিষদের সাবস্ক্রিপশন বাতিল করতে চান?' + payWarn, { danger: true }).then(function(confirmed) {
            if (!confirmed) return;
            SA('api_cancel', {subscription_id: subId})
                .then(r => { if (r.success) { vAlert(r.message, 'সফল', 'success').then(() => location.reload()); } else vAlert(r.message, 'ত্রুটি', 'error'); })
                .catch(() => vAlert('সার্ভারে সংযোগ স্থাপন করা যায়নি।', 'ত্রুটি', 'error'));
        });
    }

    function saOpenAssign(subId, currentPackageId) {
        let opts = '';
        <?php foreach ($packages as $p): ?>
            opts += '<option value="<?= (int) $p->package_id; ?>"' +
                    (<?= (int) $p->package_id; ?> === currentPackageId ? ' selected' : '') + '>' +
                    '<?= htmlspecialchars($p->name_bn . ' — ' . taka($p->price_yearly) . '/বছর', ENT_QUOTES, 'UTF-8'); ?>' +
                    '</option>';
        <?php endforeach; ?>

        saOpen('প্যাকেজ পরিবর্তন / Change Package', `
            <p class="text-neutral-600 mb-3 text-xs">প্যাকেজ পরিবর্তন করলে নতুন প্যাকেজের নির্ধারিত সুবিধা ও বার্ষিক মূল্যমান প্রযোজ্য হবে।</p>
            <label class="${SA_LABEL}">প্যাকেজ / Package</label>
            <select id="saAssignPkg" class="${SA_INPUT}">${opts}</select>
            <div class="flex justify-end gap-2 mt-4 pt-3 border-t border-neutral-200">
                <button type="button" onclick="saCloseModal()" class="px-4 py-2 rounded-lg bg-neutral-100 font-bold cursor-pointer">বাতিল / Cancel</button>
                <button type="button" onclick="saDoAssign(${subId})" class="px-5 py-2 rounded-lg bg-indigo-700 text-white font-bold cursor-pointer">সংরক্ষণ / Save</button>
            </div>`);
    }

    function saDoAssign(subId) {
        const packageId = document.getElementById('saAssignPkg').value;
        SA('api_assign', {subscription_id: subId, package_id: packageId})
            .then(r => { if (r.success) { alert(r.message); location.reload(); } else saMsg('err', r.message); })
            .catch(() => saMsg('err', 'সার্ভারে সংযোগ স্থাপন করা যায়নি।'));
    }

    function saOpenDates(subId, start, end, status, note) {
        let statusOpts = '';
        ['active', 'pending', 'expired', 'cancelled'].forEach(s => {
            statusOpts += `<option value="${s}"${s === status ? ' selected' : ''}>${s}</option>`;
        });

        saOpen('তারিখ ও অবস্থা সম্পাদনা / Edit Dates & Status', `
            <p class="text-neutral-600 mb-3 text-xs">সাবস্ক্রিপশনের শুরুর তারিখ, মেয়াদের তারিখ ও বর্তমান অবস্থা নির্ধারণ করুন:</p>
            <div class="mb-3">
                <label class="block font-semibold text-neutral-600 mb-1 text-[11px]">দ্রুত দিন যোগ করুন (Quick Add Duration):</label>
                <div class="flex flex-wrap gap-1.5">
                    <button type="button" onclick="saAddDays(30)" class="px-2.5 py-1 rounded-lg border border-neutral-200 bg-neutral-50 hover:bg-indigo-50 text-[11px] font-semibold text-neutral-700 cursor-pointer">+১ মাস (30d)</button>
                    <button type="button" onclick="saAddDays(90)" class="px-2.5 py-1 rounded-lg border border-neutral-200 bg-neutral-50 hover:bg-indigo-50 text-[11px] font-semibold text-neutral-700 cursor-pointer">+৩ মাস (90d)</button>
                    <button type="button" onclick="saAddDays(180)" class="px-2.5 py-1 rounded-lg border border-neutral-200 bg-neutral-50 hover:bg-indigo-50 text-[11px] font-semibold text-neutral-700 cursor-pointer">+৬ মাস (180d)</button>
                    <button type="button" onclick="saAddDays(365)" class="px-2.5 py-1 rounded-lg border border-neutral-200 bg-neutral-50 hover:bg-indigo-50 text-[11px] font-semibold text-neutral-700 cursor-pointer">+১ বছর (365d)</button>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="${SA_LABEL}">শুরুর তারিখ / Start Date</label>
                    <input type="date" id="saStart" value="${start}" class="${SA_INPUT}">
                </div>
                <div>
                    <label class="${SA_LABEL}">শেষের তারিখ / End Date</label>
                    <input type="date" id="saEnd" value="${end}" class="${SA_INPUT}">
                </div>
            </div>
            <div class="mt-3">
                <label class="${SA_LABEL}">অবস্থা / Status</label>
                <select id="saStatus" class="${SA_INPUT}">${statusOpts}</select>
            </div>
            <div class="mt-3">
                <label class="${SA_LABEL}">নোট / Note</label>
                <input type="text" id="saNote" value="${note || ''}" class="${SA_INPUT}">
            </div>
            <div class="flex justify-end gap-2 mt-4 pt-3 border-t border-neutral-200">
                <button type="button" onclick="saCloseModal()" class="px-4 py-2 rounded-lg bg-neutral-100 font-bold cursor-pointer">বাতিল / Cancel</button>
                <button type="button" onclick="saDoDates(${subId})" class="px-5 py-2 rounded-lg bg-indigo-700 text-white font-bold cursor-pointer">সংরক্ষণ / Save</button>
            </div>`);
    }

    function saDoDates(subId) {
        SA('api_set_dates', {
            subscription_id: subId,
            start_date: document.getElementById('saStart').value,
            end_date: document.getElementById('saEnd').value,
            status: document.getElementById('saStatus').value,
            note: document.getElementById('saNote').value
        }).then(r => { if (r.success) { alert(r.message); location.reload(); } else saMsg('err', r.message); })
          .catch(() => saMsg('err', 'সার্ভারে সংযোগ স্থাপন করা যায়নি।'));
    }

    function saOpenPayment(subId, tenantName) {
        saOpen('পেমেন্ট রেকর্ড করুন', `
            <p class="text-neutral-600 mb-3 text-xs">
                <span class="font-bold text-neutral-800">${tenantName}</span><br>
                ইউনিয়ন পরিষদ থেকে প্রাপ্ত পেমেন্টের তথ্য এন্ট্রি করুন:
            </p>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="${SA_LABEL}">পরিমাণ (৳) *</label>
                    <input type="number" id="saAmount" min="1" step="1" class="${SA_INPUT} bengali-num">
                </div>
                <div>
                    <label class="${SA_LABEL}">তারিখ</label>
                    <input type="date" id="saPaidAt" value="<?= date('Y-m-d'); ?>" class="${SA_INPUT}">
                </div>
            </div>
            <div class="mt-3">
                <label class="${SA_LABEL}">পদ্ধতি</label>
                <select id="saMethod" class="${SA_INPUT}">
                    <?php foreach ($payment_methods as $k => $v): ?>
                        <option value="<?= htmlspecialchars($k, ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mt-3">
                <label class="${SA_LABEL}">রেফারেন্স (TrxID / চেক নম্বর)</label>
                <input type="text" id="saRef" class="${SA_INPUT}" placeholder="ঐচ্ছিক">
            </div>
            <div class="mt-3">
                <label class="${SA_LABEL}">নোট</label>
                <input type="text" id="saPayNote" class="${SA_INPUT}" placeholder="ঐচ্ছিক">
            </div>
            <div class="flex justify-end gap-2 mt-4 pt-3 border-t border-neutral-200">
                <button type="button" onclick="saCloseModal()" class="px-4 py-2 rounded-lg bg-neutral-100 font-bold cursor-pointer">বাতিল</button>
                <button type="button" onclick="saDoPayment(${subId})" class="px-5 py-2 rounded-lg bg-indigo-700 text-white font-bold cursor-pointer">রেকর্ড করুন</button>
            </div>`);
    }

    function saDoPayment(subId) {
        const amount = document.getElementById('saAmount').value;
        if (!amount || Number(amount) <= 0) { saMsg('err', 'পেমেন্টের পরিমাণ লিখুন।'); return; }

        SA('api_add_payment', {
            subscription_id: subId,
            amount: Number(amount),
            paid_at: document.getElementById('saPaidAt').value,
            payment_method: document.getElementById('saMethod').value,
            reference: document.getElementById('saRef').value,
            note: document.getElementById('saPayNote').value
        }).then(r => {
            if (r.success) { alert(r.message); location.reload(); }
            else saMsg('err', r.message);
        }).catch(() => saMsg('err', 'সার্ভারে সংযোগ স্থাপন করা যায়নি।'));
    }

    function saDeletePayment(payId, invoice) {
        vConfirm('পেমেন্ট রেকর্ড মুছে ফেলা', 'ইনভয়েস #' + invoice + '-এর পেমেন্ট রেকর্ডটি কি মুছে ফেলতে চান?', { danger: true }).then(function(confirmed) {
            if (!confirmed) return;
            SA('api_delete_payment', {payment_id: payId})
                .then(r => { if (r.success) { vAlert(r.message, 'সফল', 'success').then(() => location.reload()); } else vAlert(r.message, 'ত্রুটি', 'error'); })
                .catch(() => vAlert('সার্ভারে সংযোগ স্থাপন করা যায়নি।', 'ত্রুটি', 'error'));
        });
    }

    function saOpenPackage(p) {
        const isNew = !p;
        p = p || {
            package_id: 0, package_key: '', name_bn: '', name_en: '',
            price_monthly: 0, price_yearly: 0, max_holdings: null, max_users: null,
            features: '', sort_order: 99, is_active: true
        };

        saOpen(isNew ? 'নতুন প্যাকেজ' : 'প্যাকেজ সম্পাদনা: ' + p.name_bn, `
            ${isNew ? '' : `<p class="text-neutral-600 mb-3">প্যাকেজ কী (<span class="font-mono">${p.package_key}</span>) বদলানো যায় না — পাবলিক পাতার লিংক এই কী দিয়ে তৈরি।</p>`}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="${SA_LABEL}">বাংলা নাম *</label>
                    <input type="text" id="saPkgNameBn" value="${p.name_bn}" class="${SA_INPUT}">
                </div>
                <div>
                    <label class="${SA_LABEL}">ইংরেজি নাম</label>
                    <input type="text" id="saPkgNameEn" value="${p.name_en || ''}" class="${SA_INPUT}">
                </div>
            </div>
            ${isNew ? `
            <div class="mt-3">
                <label class="${SA_LABEL}">প্যাকেজ কী</label>
                <input type="text" id="saPkgKey" value="${p.package_key}" placeholder="খালি রাখলে নাম থেকে বানানো হবে" class="${SA_INPUT} font-mono">
            </div>` : ''}
            <div class="grid grid-cols-2 gap-3 mt-3">
                <div>
                    <label class="${SA_LABEL}">বার্ষিক মূল্য (৳)</label>
                    <input type="number" id="saPkgYear" min="0" step="1" value="${p.price_yearly}" class="${SA_INPUT} bengali-num">
                </div>
                <div>
                    <label class="${SA_LABEL}">মাসিক মূল্য (৳)</label>
                    <input type="number" id="saPkgMonth" min="0" step="1" value="${p.price_monthly}" class="${SA_INPUT} bengali-num">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3 mt-3">
                <div>
                    <label class="${SA_LABEL}">হোল্ডিং সীমা</label>
                    <input type="number" id="saPkgHoldings" min="0" step="1"
                           value="${p.max_holdings === null ? '' : p.max_holdings}" placeholder="খালি = আনলিমিটেড"
                           class="${SA_INPUT} bengali-num">
                </div>
                <div>
                    <label class="${SA_LABEL}">ইউজার সীমা</label>
                    <input type="number" id="saPkgUsers" min="0" step="1"
                           value="${p.max_users === null ? '' : p.max_users}" placeholder="খালি = আনলিমিটেড"
                           class="${SA_INPUT} bengali-num">
                </div>
            </div>
            <div class="mt-3">
                <label class="${SA_LABEL}">ফিচার (প্রতি লাইনে একটি)</label>
                <textarea id="saPkgFeatures" rows="4" class="${SA_INPUT}">${p.features || ''}</textarea>
            </div>
            <div class="grid grid-cols-2 gap-3 mt-3">
                <div>
                    <label class="${SA_LABEL}">ক্রম</label>
                    <input type="number" id="saPkgSort" value="${p.sort_order}" class="${SA_INPUT} bengali-num">
                </div>
                <div class="flex items-end">
                    <label class="flex items-center gap-2 font-bold text-neutral-700 cursor-pointer pb-2.5">
                        <input type="checkbox" id="saPkgActive" ${p.is_active ? 'checked' : ''} class="w-4 h-4">
                        সক্রিয়
                    </label>
                </div>
            </div>
            <div class="flex items-center justify-between gap-2 mt-4 pt-3 border-t border-neutral-200">
                <div>
                    ${!isNew ? `<button type="button" onclick="saDeletePackage(${p.package_id}, '${p.name_bn.replace(/'/g, "\\'")}')" class="px-3.5 py-2 rounded-lg bg-red-50 hover:bg-red-100 text-red-600 font-bold text-xs flex items-center gap-1 cursor-pointer transition-colors"><i class="ph ph-trash"></i> মুছে ফেলুন</button>` : ''}
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="saCloseModal()" class="px-4 py-2 rounded-lg bg-neutral-100 font-bold cursor-pointer">বাতিল</button>
                    <button type="button" onclick="saDoPackage(${p.package_id})" class="px-5 py-2 rounded-lg bg-indigo-700 text-white font-bold cursor-pointer">সংরক্ষণ</button>
                </div>
            </div>`);
    }

    function saDeletePackage(packageId, name) {
        vConfirm('প্যাকেজ মুছে ফেলা', 'আপনি কি নিশ্চিত যে "' + name + '" প্যাকেজটি মুছে ফেলতে চান?', { danger: true }).then(function(confirmed) {
            if (!confirmed) return;
            SA('api_delete_package', {package_id: packageId})
                .then(r => {
                    if (r.success) {
                        vAlert(r.message, 'সফল', 'success').then(() => location.reload());
                    } else {
                        saMsg('err', r.message);
                    }
                })
                .catch(() => saMsg('err', 'সার্ভারে সংযোগ স্থাপন করা যায়নি।'));
        });
    }

    function saDoPackage(packageId) {
        const payload = {
            package_id: packageId,
            name_bn: document.getElementById('saPkgNameBn').value,
            name_en: document.getElementById('saPkgNameEn').value,
            price_yearly: Number(document.getElementById('saPkgYear').value || 0),
            price_monthly: Number(document.getElementById('saPkgMonth').value || 0),
            max_holdings: document.getElementById('saPkgHoldings').value,
            max_users: document.getElementById('saPkgUsers').value,
            features: document.getElementById('saPkgFeatures').value,
            sort_order: Number(document.getElementById('saPkgSort').value || 0),
            is_active: document.getElementById('saPkgActive').checked
        };
        if (!packageId) {
            payload.package_key = document.getElementById('saPkgKey').value;
        }

        SA('api_save_package', payload)
            .then(r => { if (r.success) { alert(r.message); location.reload(); } else saMsg('err', r.message); })
            .catch(() => saMsg('err', 'সার্ভারে সংযোগ স্থাপন করা যায়নি।'));
    }
</script>
