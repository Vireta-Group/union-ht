<?php
/**
 * Audit log module — the platform action trail.
 *
 * Rendered inside Template::sadmin(). The heading and sidebar come from
 * the shell; this file is the content.
 *
 * FILTERS ARE LINKS, NOT A POST
 * Every filter control here is an <a> or a <select onchange=submit>, and
 * every one of them carries the OTHER current filters. Same rule as the
 * support list and for the same reason: an audit entry that cannot be
 * linked to cannot be quoted to the person who needs to read it, and a
 * two-filter bar written as base_url('sadmin_audit?action=delete') drops
 * the date range the operator already set.
 *
 * NO CLIENT-SIDE FILTERING
 * Every count and every row on this page comes from the database. An
 * audit log filtered in the browser reports what was on the page, not
 * what is in the table, and the difference is exactly the thing somebody
 * opens this page to find out.
 *
 * THE DIFF IS COLLAPSED, NOT HIDDEN
 * A row shows the summary; the before/after JSON is one click away. It is
 * not behind a hover, because the person reading an audit log is often on
 * a phone in a committee meeting and hovering is not a thing they can do.
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Build a filter href that keeps the OTHER filters.
 *
 * Merged over $_GET, with an override of '' or NULL meaning "remove this
 * filter" — otherwise the URL carries ?action=&action=delete and the
 * reader cannot tell which of the two is live.
 */
if ( ! function_exists('sa_audit_qs')) {
function sa_audit_qs(array $overrides = [])
{
    $q = array_merge($_GET, $overrides);

    foreach ($q as $k => $v) {
        if ($v === '' || $v === NULL) {
            unset($q[$k]);
        }
    }

    // page is not a filter. Keeping it means a narrowed list opens on the
    // page you were on, which is almost never the page you want.
    unset($q['page']);

    return empty($q) ? '' : '?' . http_build_query($q);
}
}

/**
 * The action chip for one row.
 *
 * Falls back to neutral for a value the constant does not carry, which
 * can only happen if a new action were added to the CHECK constraint and
 * not here — and this is the shape that shows it: an English key where
 * the rest of the column is Bangla, which is visible in a list.
 */
if ( ! function_exists('sa_action_chip')) {
function sa_action_chip($action)
{
    if (isset(Sadmin_audit::ACTIONS[$action])) {
        return Sadmin_audit::ACTIONS[$action];
    }
    return [$action, 'bg-neutral-100 text-neutral-600 border-neutral-200'];
}
}

/**
 * Is any filter active? Drives the "মুছুন" link and the empty-state copy.
 */
$has_filter = $filters['q'] !== ''
    || $filters['action'] !== ''
    || $filters['entity'] !== ''
    || $filters['actor_id'] > 0
    || $filters['tenant_id'] !== ''
    || $filters['from'] !== ''
    || $filters['to'] !== '';

/** Page-window href, for the pager. */
$page_qs = function ($p) {
    $q = $_GET;
    $q['page'] = $p;
    return '?' . http_build_query($q);
};
?>

<!-- ===================== Summary ===================== -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
    <?php foreach ($summary as $c): ?>
        <div class="bg-white border border-neutral-200/80 rounded-2xl p-4 shadow-xs hover:border-neutral-300 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-neutral-500"><?= $c['label']; ?></span>
                <div class="w-8 h-8 rounded-xl bg-neutral-50 border border-neutral-100 flex items-center justify-center <?= $c['cls']; ?>">
                    <i class="ph <?= $c['icon']; ?> text-base"></i>
                </div>
            </div>
            <p class="mt-2 text-2xl sm:text-3xl font-black <?= $c['cls']; ?> bengali-num leading-tight"><?= bn_number(number_format($c['value'])); ?></p>
        </div>
    <?php endforeach; ?>
</div>

<!--
    What this table is and is not, on the page.

    A gap in an audit log is ambiguous: it can mean nothing happened, or it
    can mean something happened and the write was not recorded. That
    distinction decides whether the operator escalates, and leaving it to
    a doc nobody has open is how a missing entry turns into a week of
    nobody knowing.
-->
<div class="flex items-start gap-3 text-xs text-neutral-700 bg-indigo-50/40 border border-indigo-100 rounded-2xl p-4 shadow-2xs leading-relaxed">
    <div class="w-7 h-7 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center shrink-0 mt-0.5">
        <i class="ph ph-info text-base"></i>
    </div>
    <div>
        <p>
            সুপার অ্যাডমিন প্ল্যাটফর্মে সেন্ট্রাল স্টাফ ও অ্যাডমিনিস্ট্রেটরদের দ্বারা সম্পাদিত সকল প্রশাসনিক পরিবর্তন, প্যাকেজ নিয়োগ ও সিকিউরিটি ইভেন্টের ইতিহাস এখানে সংরক্ষিত থাকে।
        </p>
        <?php if (!empty($oldest)): ?>
            <p class="text-neutral-400 font-mono text-[10px] mt-1">প্রথম লগ: <?= htmlspecialchars(date('Y-m-d H:i', strtotime($oldest)), ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>
    </div>
</div>

<!-- ===================== Filters ===================== -->
<div class="bg-white border border-neutral-200/80 rounded-2xl p-4 sm:p-5 shadow-xs">
    <form method="get" action="<?= base_url('sadmin_audit'); ?>" class="flex flex-wrap items-end gap-2.5">
        <?php
        /*
         * Carry the filters this row of controls does not render. Without
         * these, choosing a date range throws away the action filter and
         * choosing an action throws away the search box.
         */
        foreach (['q', 'entity', 'actor_id'] as $hk) {
            $hv = $_GET[$hk] ?? '';
            if ($hv !== '' && $hv !== NULL) {
                printf('<input type="hidden" name="%s" value="%s">', $hk, htmlspecialchars((string) $hv, ENT_QUOTES, 'UTF-8'));
            }
        }
        ?>
        <div class="w-full md:flex-1 md:min-w-[12rem]">
            <label class="block text-[11px] font-bold text-neutral-500 mb-1" for="saQ">খুঁজুন</label>
            <input type="search" id="saQ" name="q" value="<?= htmlspecialchars($filters['q'], ENT_QUOTES, 'UTF-8'); ?>"
                   placeholder="সারাংশ, নাম, আইপি…"
                   class="w-full px-3 py-2 rounded-xl border border-neutral-200 bg-neutral-50/50 text-xs focus:bg-white focus:border-indigo-600 focus:ring-2 focus:ring-indigo-100 transition-all outline-none">
        </div>

        <div class="w-full sm:w-auto">
            <label class="block text-[11px] font-bold text-neutral-500 mb-1" for="saAction">কাজ</label>
            <select id="saAction" name="action" onchange="this.form.submit()"
                    class="w-full sm:w-auto px-3 py-2 rounded-xl border border-neutral-200 bg-neutral-50/50 text-xs font-semibold text-neutral-700 focus:bg-white focus:border-indigo-600 focus:ring-2 focus:ring-indigo-100 transition-all outline-none cursor-pointer">
                <option value="">সব কাজ</option>
                <?php foreach (Sadmin_audit::ACTIONS as $k => [$label]): ?>
                    <option value="<?= $k; ?>" <?= $filters['action'] === $k ? 'selected' : '' ?>><?= $label; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="w-full sm:w-auto">
            <label class="block text-[11px] font-bold text-neutral-500 mb-1" for="saEntity">কোন জিনিস</label>
            <select id="saEntity" name="entity" onchange="this.form.submit()"
                    class="w-full sm:w-auto px-3 py-2 rounded-xl border border-neutral-200 bg-neutral-50/50 text-xs font-semibold text-neutral-700 focus:bg-white focus:border-indigo-600 focus:ring-2 focus:ring-indigo-100 transition-all outline-none cursor-pointer">
                <option value="">সব জিনিস</option>
                <?php foreach ($entities as $e): ?>
                    <option value="<?= htmlspecialchars($e->entity, ENT_QUOTES, 'UTF-8'); ?>" <?= $filters['entity'] === $e->entity ? 'selected' : '' ?>>
                        <?= htmlspecialchars($e->entity, ENT_QUOTES, 'UTF-8'); ?>
                        (<?= bn_number(number_format($e->n)); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="w-full sm:w-auto">
            <label class="block text-[11px] font-bold text-neutral-500 mb-1" for="saActor">কে</label>
            <select id="saActor" name="actor_id" onchange="this.form.submit()"
                    class="w-full sm:w-auto px-3 py-2 rounded-xl border border-neutral-200 bg-neutral-50/50 text-xs font-semibold text-neutral-700 focus:bg-white focus:border-indigo-600 focus:ring-2 focus:ring-indigo-100 transition-all outline-none cursor-pointer">
                <option value="">সবাই</option>
                <?php foreach ($actors as $a): ?>
                    <option value="<?= (int) $a->actor_id; ?>" <?= $filters['actor_id'] === (int) $a->actor_id ? 'selected' : '' ?>>
                        <?= htmlspecialchars($a->actor_name, ENT_QUOTES, 'UTF-8'); ?>
                        (<?= bn_number(number_format($a->n)); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="w-full sm:w-auto">
            <label class="block text-[11px] font-bold text-neutral-500 mb-1">তারিখ থেকে</label>
            <input type="text" autocomplete="off" name="from" value="<?= $filters['from']; ?>"
                   placeholder="YYYY-MM-DD"
                   class="sa-date w-full sm:w-auto px-3 py-2 rounded-xl border border-neutral-200 bg-neutral-50/50 text-xs text-neutral-700 focus:bg-white focus:border-indigo-600 focus:ring-2 focus:ring-indigo-100 transition-all outline-none">
        </div>
        <div class="w-full sm:w-auto">
            <label class="block text-[11px] font-bold text-neutral-500 mb-1">তারিখ পর্যন্ত</label>
            <input type="text" autocomplete="off" name="to" value="<?= $filters['to']; ?>"
                   placeholder="YYYY-MM-DD"
                   class="sa-date w-full sm:w-auto px-3 py-2 rounded-xl border border-neutral-200 bg-neutral-50/50 text-xs text-neutral-700 focus:bg-white focus:border-indigo-600 focus:ring-2 focus:ring-indigo-100 transition-all outline-none">
        </div>

        <div class="flex items-center gap-1.5 w-full sm:w-auto pt-1 sm:pt-0">
            <button type="submit" class="flex-1 sm:flex-initial px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white text-xs font-bold shadow-xs transition-all cursor-pointer">
                <i class="ph ph-magnifying-glass"></i> খুঁজুন
            </button>
            <?php if ($has_filter): ?>
                <a href="<?= base_url('sadmin_audit'); ?>"
                   class="px-3 py-2 rounded-xl bg-neutral-100 hover:bg-neutral-200 text-neutral-600 text-xs font-bold transition-all text-center">মুছুন</a>
            <?php endif; ?>
        </div>
    </form>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-3.5 mt-3.5 border-t border-neutral-100">
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-[11px] font-bold text-neutral-500">কোন দলের:</span>
            <div class="inline-flex p-0.5 bg-neutral-100 rounded-xl gap-0.5">
                <?php
                $tenant_opts = [
                    ''         => ['সবাই',        $filters['tenant_id'] === ''],
                    'platform' => ['প্ল্যাটফর্ম', $filters['tenant_id'] === 'platform'],
                ];
                foreach ($tenant_opts as $kv => [$label, $on]) {
                    printf(
                        '<a href="%s" class="px-3 py-1 rounded-lg text-xs font-bold transition-all %s">%s</a>',
                        htmlspecialchars(base_url('sadmin_audit' . sa_audit_qs(['tenant_id' => $kv])), ENT_QUOTES, 'UTF-8'),
                        $on ? 'bg-white text-neutral-900 shadow-2xs' : 'text-neutral-500 hover:text-neutral-900',
                        $label
                    );
                }
                ?>
            </div>
            <span class="text-[11px] text-neutral-400 sm:ml-2">
                ফিল্টারে <span class="font-bold text-neutral-700 bengali-num"><?= bn_number(number_format($total)); ?></span> টি লগ
            </span>
        </div>

        <a href="<?= htmlspecialchars(base_url('sadmin_audit/export' . ($has_filter ? sa_audit_qs([]) : '')), ENT_QUOTES, 'UTF-8'); ?>"
           class="inline-flex items-center justify-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-100 text-xs font-bold border border-emerald-200 shadow-2xs transition-all w-full sm:w-auto">
            <i class="ph ph-download-simple text-sm"></i> CSV ডাউনলোড
        </a>
    </div>
</div>

<!-- ===================== Table ===================== -->
<div class="min-w-0 bg-white border border-neutral-200 rounded-2xl shadow-xs overflow-hidden">
        <!-- Mobile scroll hint. The table keeps a min-width so its columns
             stay readable, which means a narrow screen has to scroll it
             sideways. The scrollbar is the only cue and touch devices hide
             it until you drag, so the row reads as "clipped" rather than
             "scrollable". This says so explicitly, and is hidden at sm:
             where the table mostly fits. -->
        <p class="sm:hidden mt-2 flex items-center gap-1.5 text-[11px] text-neutral-400">
            <i class="ph ph-arrows-left-right text-neutral-300"></i>
            <span>পুরো তালিকা দেখতে চওড়ায় স্ক্রল করুন</span>
        </p>


    <div class="overflow-x-auto">
        <table class="w-full min-w-[720px] text-left">
            <thead class="bg-neutral-50 border-b border-neutral-200">
                <tr>
                    <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-neutral-500 w-36">সময়</th>
                    <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-neutral-500 w-44">কে</th>
                    <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-neutral-500 w-28">কাজ</th>
                    <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-neutral-500">কী হয়েছে</th>
                    <th class="px-4 py-3 w-20"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-100">
                <?php if (empty($rows)): ?>
                    <tr>
                        <td colspan="5" class="py-14 text-center">
                            <i class="ph ph-scroll text-4xl text-neutral-200"></i>
                            <p class="mt-2 text-sm font-bold text-neutral-500">কোনো লগ পাওয়া যায়নি</p>
                            <p class="mt-1 text-xs text-neutral-400">
                                <?= $has_filter
                                    ? 'ফিল্টার বদলে দেখুন, অথবা "মুছুন" চাপুন।'
                                    : 'এখনো কোনো কাজ লেখা হয়নি। প্যানেল থেকে কিছু বদলালে এখানে দেখা যাবে।' ?>
                            </p>
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($rows as $r):
                    [$act_label, $act_cls] = sa_action_chip($r->action);
                    $has_diff = !empty($r->before_data) || !empty($r->after_data);
                ?>
                    <tr class="align-top">
                        <td class="py-3.5 px-4">
                            <div class="font-mono text-[11px] font-bold text-neutral-700"><?= bn_number(date('d M', strtotime($r->created_at))); ?></div>
                            <div class="font-mono text-[10px] text-neutral-400"><?= date('H:i:s', strtotime($r->created_at)); ?></div>
                        </td>

                        <td class="py-3.5 px-4 text-[11px]">
                            <div class="font-bold text-neutral-800"><?= htmlspecialchars($r->actor_name ?: '—', ENT_QUOTES, 'UTF-8'); ?></div>
                            <div class="text-neutral-400"><?= htmlspecialchars($r->actor_group ?: '—', ENT_QUOTES, 'UTF-8'); ?></div>
                            <?php if (!empty($r->ip_address)): ?>
                                <div class="font-mono text-[10px] text-neutral-300"><?= htmlspecialchars($r->ip_address, ENT_QUOTES, 'UTF-8'); ?></div>
                            <?php endif; ?>
                        </td>

                        <td class="py-3.5 px-4">
                            <span class="inline-block px-2 py-0.5 rounded-md border text-[10px] font-bold <?= $act_cls; ?>"><?= $act_label; ?></span>
                            <div class="mt-1 font-mono text-[10px] text-neutral-400"><?= htmlspecialchars($r->entity, ENT_QUOTES, 'UTF-8'); ?></div>
                        </td>

                        <td class="py-3.5 px-4">
                            <p class="text-xs text-neutral-800"><?= htmlspecialchars($r->summary ?: '—', ENT_QUOTES, 'UTF-8'); ?></p>
                            <div class="mt-1 flex flex-wrap items-center gap-2 text-[10px] text-neutral-400">
                                <?php if ($r->tenant_id === NULL): ?>
                                    <span class="px-1.5 py-0.5 rounded bg-neutral-100 text-neutral-500 font-bold">প্ল্যাটফর্ম</span>
                                <?php else: ?>
                                    <span class="px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-600 font-bold">ইউনিয়ন #<?= (int) $r->tenant_id; ?></span>
                                <?php endif; ?>
                                <?php if ($r->entity_id !== NULL && $r->entity_id !== ''): ?>
                                    <span class="font-mono">#<?= htmlspecialchars($r->entity_id, ENT_QUOTES, 'UTF-8'); ?></span>
                                <?php endif; ?>
                            </div>

                            <?php if ($has_diff): ?>
                                <button type="button" onclick="saAuditDiff(this, '<?= (int) $r->audit_id; ?>')"
                                        class="saDiffBtn mt-2 inline-flex items-center gap-1 text-[11px] font-bold text-indigo-600 hover:text-indigo-800 cursor-pointer">
                                    <i class="ph ph-caret-right text-xs"></i> পরিবর্তন দেখুন
                                </button>
                                <div id="saDiff-<?= (int) $r->audit_id; ?>" class="hidden mt-2"></div>
                            <?php endif; ?>
                        </td>

                        <td class="py-3.5 px-4 text-right font-mono text-[10px] text-neutral-300"><?= (int) $r->audit_id; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if (!empty($rows) && $pages > 1): ?>
        <div class="px-4 py-3 border-t border-neutral-100 bg-neutral-50/60 flex flex-wrap items-center justify-between gap-2 text-[11px] text-neutral-500">
            <span class="bengali-num">
                পাতা <?= bn_number($page); ?> / <?= bn_number($pages); ?>
                · মোট <?= bn_number(number_format($total)); ?> টি, প্রতি পাতায় <?= bn_number($per_page); ?> টি
            </span>
            <span class="flex items-center gap-1.5">
                <?php if ($page > 1): ?>
                    <a href="<?= htmlspecialchars(base_url('sadmin_audit' . $page_qs($page - 1)), ENT_QUOTES, 'UTF-8'); ?>"
                       class="px-3 py-1.5 rounded-lg bg-white border border-neutral-200 text-neutral-600 font-bold hover:bg-neutral-100">আগে</a>
                <?php endif; ?>
                <?php if ($page < $pages): ?>
                    <a href="<?= htmlspecialchars(base_url('sadmin_audit' . $page_qs($page + 1)), ENT_QUOTES, 'UTF-8'); ?>"
                       class="px-3 py-1.5 rounded-lg bg-neutral-900 text-white font-bold">পরে</a>
                <?php endif; ?>
            </span>
        </div>
    <?php endif; ?>
</div>

<!--
    The diff data, as data.

    Every before/after is inlined in a <script type="application/json">
    block rather than fetched. A diff is short, the page is already
    paginated at 50 rows, and an extra endpoint per row would mean 50
    requests to expand one row — while a <script type="application/json">
    block cannot execute, so an audit summary containing "</script>" is
    neutralised by the JSON_HEX_TAG flag below rather than by trusting
    an input column.
-->
<?php if (!empty($rows)): ?>
    <script type="application/json" id="saAuditData">
        <?php
        $payload = [];
        foreach ($rows as $r) {
            $dec = function ($j) {
                if ($j === NULL || $j === '') {
                    return [];
                }
                $d = json_decode($j, TRUE);
                return is_array($d) ? $d : ['(raw)' => (string) $j];
            };
            $payload[(int) $r->audit_id] = [
                'before' => $dec($r->before_data),
                'after'  => $dec($r->after_data),
            ];
        }
        echo json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP
        );
        ?>
    </script>
<?php endif; ?>

<!-- ===================== Retention ===================== -->
<?php
/*
 * The purge card is shown whether or not anything is old enough to purge.
 * Hiding it until rows are eligible means an operator discovers the
 * feature by reading a doc, and the first time they see it is the first
 * time they use it. Showing the floor with a count of 0 says the same
 * thing without the click.
 */
$floor_ts = strtotime($purge_floor);
?>
<div class="bg-white border border-neutral-200 rounded-2xl shadow-xs overflow-hidden">
    <div class="px-4 sm:px-5 py-3.5 border-b border-neutral-200">
        <h2 class="text-sm font-bold text-neutral-900">পুরনো লগ মোছা</h2>
        <p class="text-[11px] text-neutral-600 mt-1 leading-relaxed">
            অডিট লগ খালি হয়ে গেলে কারও কাজের প্রমাণ থাকে না, তাই মোছার সুযোগ সীমিত রাখা হয়েছে:
            গত <span class="font-bold"><?= bn_number(90); ?> দিনের</span> ভেতরের কোনো লগ মোছা যাবে না।
            সবচেয়ে নতুন অনুমোদিত তারিখ <span class="font-mono font-bold"><?= $purge_floor; ?></span>।
        </p>
    </div>

    <div class="p-4 sm:p-5">
        <?php if ($purgeable === 0): ?>
            <div class="flex items-center gap-2 text-xs text-neutral-500">
                <i class="ph ph-shield-check text-lg text-emerald-500"></i>
                এখন মোছার মতো কোনো লগ নেই — সবগুলোই
                <span class="font-mono"><?= $purge_floor; ?></span>-এর পরের।
            </div>
        <?php else: ?>
            <p class="text-xs text-amber-800 bg-amber-50 border border-amber-200 rounded-xl px-3 py-2 mb-3">
                <i class="ph ph-warning"></i>
                এই তারিখ দিলে <span class="font-bold bengali-num"><?= bn_number(number_format($purgeable)); ?></span> টি লগ মুছে যাবে।
                মোছা ফেরানো যাবে না।
            </p>

            <form onsubmit="return saAuditPurge(event)" class="flex flex-col sm:flex-row flex-wrap items-stretch sm:items-end gap-2.5">
                <div class="w-full sm:w-auto">
                    <label class="block text-[11px] font-bold text-neutral-500 mb-1" for="saPurgeDate">এই তারিখের আগের সব লগ মুছুন</label>
                    <input type="text" autocomplete="off" id="saPurgeDate" name="before"
                           placeholder="YYYY-MM-DD"
                           min="<?= $purge_floor; ?>" max="<?= date('Y-m-d'); ?>" value="<?= $purge_floor; ?>"
                           class="sa-date w-full sm:w-auto p-2.5 rounded-lg border border-neutral-300 text-xs focus:border-rose-600 focus:outline-none bg-white">
                </div>
                <div class="w-full sm:w-auto">
                    <label class="block text-[11px] font-bold text-neutral-500 mb-1" for="saPurgeConfirm">নিশ্চিত করতে <span class="font-mono">মুছুন</span> লিখুন</label>
                    <input type="text" id="saPurgeConfirm" name="confirm" autocomplete="off"
                           placeholder="মুছুন"
                           class="w-full sm:w-40 p-2.5 rounded-lg border border-neutral-300 text-xs focus:border-rose-600 focus:outline-none">
                </div>
                <button type="submit" class="w-full sm:w-auto px-4 py-2.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold cursor-pointer text-center">
                    <i class="ph ph-trash"></i> লগ মুছে ফেলুন
                </button>
                <span id="saPurgeMsg" class="text-[11px] font-bold self-center pb-1"></span>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
    const SA = (path, payload) => fetch('sadmin_audit/' + path, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(payload)
    }).then(r => r.json().catch(() => ({
        success: false,
        message: 'সার্ভার থেকে অগ্রহণযোগ্য উত্তর আসেনি (HTTP ' + r.status + ')।'
    })));

    /**
     * Toggle one row's diff.
     *
     * The data is read from the inline JSON block rather than fetched, so
     * this is a synchronous lookup and there is no loading state to get
     * wrong. The key is the audit_id, which is a bigint and therefore
     * never a valid JavaScript array index for a row in the millions —
     * the payload is an object keyed by that number, not an array.
     */
    function saAuditDiff(button, auditId) {
        const box = document.getElementById('saDiff-' + auditId);
        if (!box) return;

        if (!box.classList.contains('hidden')) {
            box.classList.add('hidden');
            button.innerHTML = '<i class="ph ph-caret-right text-xs"></i> পরিবর্তন দেখুন';
            return;
        }

        let data = {};
        try {
            data = JSON.parse(document.getElementById('saAuditData').textContent) || {};
        } catch (e) {
            box.textContent = 'পরিবর্তনের তথ্য পড়া যায়নি।';
            box.className = 'mt-2 text-[11px] text-rose-600';
            return;
        }

        const row = data[auditId] || {};
        box.textContent = '';                 // clear, then append nodes
        box.appendChild(saAuditDiffBody(row));

        box.classList.remove('hidden');
        button.innerHTML = '<i class="ph ph-caret-down text-xs"></i> লুকান';
    }

    /**
     * One side of a diff, as a key/value list.
     *
     * Built as DOM nodes with textContent, never as an HTML string
     * assigned to innerHTML. That is not a style preference, and getting
     * it wrong is the bug this function is shaped to prevent: the first
     * version of it built a string and set innerHTML, and while its own
     * comment claimed the opposite.
     *
     * before_data and after_data carry whatever an operator typed into a
     * settings form, so a stored value of `<img src=x onerror=...>` is
     * genuinely reachable from this table — it is in the database, in the
     * inline JSON block a few hundred pixels up, and one click away.
     * Concatenating it into markup executes it in the super-admin's
     * session, and the actor who put it there does not need any
     * privilege to do so: a support ticket is a public text field, and a
     * staff member quoting a customer's message into a reply is enough.
     *
     * textContent makes the escaping automatic and correct for both the
     * key (a column name) and the value (arbitrary input). The class
     * names below are the only markup emitted, and neither is built from
     * data.
     *
     * NULL is styled apart from the empty string because in a settings
     * diff they mean different things: NULL is "this field is not set"
     * and '' is "this field is set to nothing". A diff that renders both
     * as blank cannot answer "did they clear it or was it never set".
     */
    function saAuditPanel(head, side) {
        const wrap = document.createElement('div');
        wrap.className = 'rounded-lg border border-neutral-200 bg-neutral-50 p-2.5';

        const title = document.createElement('p');
        title.className = 'text-[10px] font-bold uppercase tracking-wider text-neutral-400 mb-1.5';
        title.textContent = head;
        wrap.appendChild(title);

        const keys = (side && typeof side === 'object') ? Object.keys(side) : [];

        if (keys.length === 0) {
            const none = document.createElement('p');
            none.className = 'text-[11px] text-neutral-400';
            none.textContent = '— কিছু নেই —';
            wrap.appendChild(none);
            return wrap;
        }

        const dl = document.createElement('dl');
        dl.className = 'space-y-1.5';

        for (const k of keys) {
            const v = side[k];
            const isNull = (v === null || v === undefined);

            const dt = document.createElement('dt');
            dt.className = 'font-mono text-[10px] text-neutral-500';
            dt.textContent = k;

            const dd = document.createElement('dd');
            dd.className = 'text-[11px] break-words '
                         + (isNull ? 'text-neutral-400 italic' : 'text-neutral-800');
            dd.textContent = isNull ? 'NULL' : (v === '' ? '(খালি)' : String(v));

            dl.appendChild(dt);
            dl.appendChild(dd);
        }

        wrap.appendChild(dl);
        return wrap;
    }

    /** Both sides of a diff, side by side. */
    function saAuditDiffBody(row) {
        const grid = document.createElement('div');
        grid.className = 'grid sm:grid-cols-2 gap-2';
        grid.appendChild(saAuditPanel('আগে', row && row.before));
        grid.appendChild(saAuditPanel('পরে',  row && row.after));
        return grid;
    }

    /**
     * The retention purge.
     *
     * The confirmation word is sent as-is, and the server refuses with a
     * count when it does not match — so the first press tells the operator
     * exactly how much is about to go, and the second press is them
     * agreeing to that number rather than to a number they guessed at.
     */
    async function saAuditPurge(event) {
        event.preventDefault();

        const date = document.getElementById('saPurgeDate').value;
        const word = document.getElementById('saPurgeConfirm').value;
        const msg  = document.getElementById('saPurgeMsg');

        const set = (kind, text) => {
            msg.className = 'text-[11px] font-bold self-center pb-1 ' + (
                kind === 'ok'  ? 'text-emerald-600' :
                kind === 'err' ? 'text-rose-600'    : 'text-neutral-400'
            );
            msg.textContent = text;
        };

        if (!date) { set('err', 'একটি তারিখ বেছে নিন।'); return false; }
        set('', 'যাচাই হচ্ছে…');

        try {
            const r = await SA('api_purge_before', {before: date, confirm: word});
            set(r.success ? 'ok' : 'err', r.message);
            if (r.success) setTimeout(() => location.reload(), 900);
        } catch (e) {
            set('err', 'সার্ভারে সংযোগ স্থাপন করা যায়নি।');
        }

        return false;
    }
</script>
