<?php
/**
 * Support module — the ticket list.
 *
 * Rendered inside Template::sadmin(). The page heading and sidebar come
 * from the shell; this file is the content.
 *
 * NO DATA IS GENERATED CLIENT-SIDE
 * Every filter on this page is a query string parameter and every card is
 * a link carrying the whole current query. Two reasons: a helpdesk whose
 * "unassigned" count is a JavaScript loop over the visible rows reports
 * the number on screen and not the number in the table, and a ticket list
 * whose state lives in the URL can be linked to in an answer to somebody
 * else. The tenant filter and the search box build their hrefs from
 * $_GET so a second filter does not silently drop the first — the bug a
 * filter bar written as base_url('sadmin_support?status=open') has.
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Build a filter href that keeps the OTHER filters.
 *
 * Merged over $_GET rather than hand-written per link. A hand-written
 * href can only ever carry the one parameter its author thought about,
 * so "urgent tickets for ধুম" is unreachable the moment someone adds a
 * second filter.
 */
function sa_support_qs(array $overrides = [])
{
    $q = array_merge($_GET, $overrides);
    // An override to '' or null means "remove this filter", not "set it
    // to empty", otherwise ?status=&status=open is ambiguous.
    foreach ($q as $k => $v) {
        if ($v === '' || $v === NULL) {
            unset($q[$k]);
        }
    }
    return empty($q) ? '' : '?' . http_build_query($q);
}

/**
 * A short, readable age for a timestamp: "৩ দিন", "৫ মিনিট".
 *
 * Deliberately coarse. A helpdesk age shown to the second invites reading
 * it as a deadline, and the thing that actually matters — is this older
 * than a day — is answered by the unit, not the digits.
 */
function sa_ticket_age($ts)
{
    if (empty($ts)) {
        return ['—', ''];
    }
    $mins = max(0, (int) floor((time() - strtotime($ts)) / 60));
    if ($mins < 60) {
        return [bn_number($mins) . ' মিনিট', 'text-neutral-500'];
    }
    $hours = (int) floor($mins / 60);
    if ($hours < 24) {
        return [bn_number($hours) . ' ঘণ্টা', 'text-neutral-600'];
    }
    $days = (int) floor($hours / 24);
    return [bn_number($days) . ' দিন', $days >= 3 ? 'font-bold text-rose-600' : 'text-neutral-600'];
}
?>

<!-- ===================== Summary ===================== -->
<?php
/*
 * Counted over the WHOLE table in the controller, never over the filtered
 * rows — see Sadmin_support::index(). The first card has no href on
 * purpose: there is no "all tickets" filter value, so it is a plain div
 * rather than a link that would have to clear five query parameters to
 * work.
 */
$summary = [
    ['label' => 'মোট টিকিট',  'value' => $tk_total,     'icon' => 'ph-ticket',            'key' => '',          'cls' => 'text-neutral-900'],
    ['label' => 'অপেক্ষমাণ',   'value' => $tk_backlog,    'icon' => 'ph-inbox',             'key' => 'open',     'cls' => $tk_backlog > 0 ? 'text-sky-600' : 'text-neutral-400'],
    ['label' => 'জরুরি',       'value' => $tk_urgent,     'icon' => 'ph-warning-octagon',   'key' => 'urgent',   'cls' => $tk_urgent > 0 ? 'text-rose-600' : 'text-neutral-400'],
    ['label' => 'অনিয়োজিত',   'value' => $tk_unassigned, 'icon' => 'ph-user-question',     'key' => '',          'cls' => $tk_unassigned > 0 ? 'text-amber-600' : 'text-neutral-400'],
];
?>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
    <?php foreach ($summary as $s): ?>
        <?php if ($s['key'] === ''): ?>
            <div class="bg-white border border-neutral-200 rounded-2xl p-4 sm:p-5 shadow-xs">
        <?php else: ?>
            <?php
            /*
             * The urgent card filters by PRIORITY and the backlog card by
             * STATUS, so each href overrides one key and merges the rest.
             * sa_support_qs() is what makes that safe.
             */
            $href = $s['key'] === 'urgent'
                ? sa_support_qs(['priority' => 'urgent', 'status' => ''])
                : sa_support_qs(['status' => 'open']);
            $is_on = ($filter_status === $s['key'] && $filter_priority === '')
                  || ($s['key'] === 'urgent' && $filter_priority === 'urgent');
            ?>
            <a href="<?= base_url('sadmin_support' . $href); ?>"
               class="bg-white border <?= $is_on ? 'border-indigo-400 ring-1 ring-indigo-200' : 'border-neutral-200 hover:border-indigo-300' ?> rounded-2xl p-4 sm:p-5 shadow-xs hover:shadow-sm transition-all">
        <?php endif; ?>
            <div class="flex items-start justify-between gap-2">
                <span class="text-xs font-semibold text-neutral-500"><?= $s['label'] ?></span>
                <i class="ph <?= $s['icon'] ?> text-lg text-neutral-300 shrink-0"></i>
            </div>
            <div class="mt-2 text-2xl sm:text-3xl font-black <?= $s['cls'] ?> bengali-num leading-none">
                <?= bn_number(number_format($s['value'])) ?>
            </div>
        <?php if ($s['key'] === ''): ?>
            </div>
        <?php else: ?>
            </a>
        <?php endif; ?>
    <?php endforeach; ?>
</div>

<!--
    The oldest waiting ticket, on its own line.

    A count is a number you can compare to nothing; "the oldest open ticket
    is ১৯ days old" is the sentence that makes an operator do something.
    Rendered only when there IS one, because a permanent "— days" card on a
    quiet helpdesk is noise that trains people to ignore the row.
-->
<?php if (!empty($tk_oldest_open)): ?>
    <?php list($oldest_label, $oldest_cls) = sa_ticket_age($tk_oldest_open); ?>
    <div class="flex flex-wrap items-center gap-2 text-xs bg-white border border-neutral-200 rounded-2xl px-4 py-3 shadow-xs">
        <i class="ph ph-hourglass-medium text-lg text-neutral-400"></i>
        <span class="text-neutral-600">সবচেয়ে পুরনো অপেক্ষমাণ টিকিট:</span>
        <span class="font-bold <?= $oldest_cls; ?>"><?= $oldest_label; ?></span>
        <span class="text-neutral-400">·</span>
        <span class="font-mono text-neutral-500"><?= htmlspecialchars(date('Y-m-d H:i', strtotime($tk_oldest_open)), ENT_QUOTES, 'UTF-8'); ?></span>
    </div>
<?php endif; ?>

<!-- ===================== Filter bar ===================== -->
<div class="bg-white border border-neutral-200 rounded-2xl p-4 shadow-xs space-y-3">
    <form method="get" action="<?= base_url('sadmin_support'); ?>" class="flex flex-wrap items-end gap-2">
        <?php
        /*
         * The hidden fields carry the filters this form does not render as
         * inputs. Without them, typing in the search box silently throws
         * away the status filter — which is the single most confusing
         * thing a two-filter bar can do.
         */
        foreach (['status', 'priority', 'category', 'tenant_id', 'mine'] as $hk) {
            $hv = $_GET[$hk] ?? '';
            if ($hv !== '' && $hv !== NULL) {
                printf('<input type="hidden" name="%s" value="%s">', $hk, htmlspecialchars((string) $hv, ENT_QUOTES, 'UTF-8'));
            }
        }
        ?>
        <div class="w-full md:flex-1 md:min-w-[12rem]">
            <label class="block text-[11px] font-bold text-neutral-500 mb-1">খুঁজুন</label>
            <input type="search" name="q" value="<?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8'); ?>"
                   placeholder="বিষয়, টিকিট নম্বর, নাম, ইমেইল…"
                   class="w-full p-2.5 rounded-lg border border-neutral-300 text-xs focus:border-indigo-600 focus:outline-none">
        </div>
        <div class="w-full sm:w-auto">
            <label class="block text-[11px] font-bold text-neutral-500 mb-1">ইউনিয়ন</label>
            <select name="tenant_id" onchange="this.form.submit()"
                    class="w-full sm:w-auto p-2.5 rounded-lg border border-neutral-300 text-xs focus:border-indigo-600 focus:outline-none bg-white">
                <option value="">সবাই</option>
                <?php foreach ($tenants as $t): ?>
                    <option value="<?= (int) $t->ins_titute_p_idds; ?>" <?= $filter_tenant === (int) $t->ins_titute_p_idds ? 'selected' : '' ?>>
                        <?= htmlspecialchars($t->ins_name, ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="flex items-center gap-1.5 w-full sm:w-auto pt-1 sm:pt-0">
            <button type="submit" class="flex-1 sm:flex-initial px-4 py-2.5 rounded-lg bg-neutral-900 text-white text-xs font-bold cursor-pointer text-center">খুঁজুন</button>
            <?php if ($q !== '' || $filter_tenant > 0 || $filter_status !== '' || $filter_priority !== '' || $filter_category !== '' || $filter_mine !== ''): ?>
                <a href="<?= base_url('sadmin_support'); ?>"
                   class="px-3 py-2.5 rounded-lg bg-neutral-100 text-neutral-600 text-xs font-bold hover:bg-neutral-200 text-center">মুছুন</a>
            <?php endif; ?>
        </div>
    </form>

    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:flex-wrap pt-1 border-t border-neutral-100">
        <a href="<?= base_url('sadmin_support' . sa_support_qs(['status' => '', 'priority' => '', 'category' => ''])); ?>"
           class="whitespace-nowrap px-3 py-1.5 rounded-lg text-xs font-bold <?= $filter_status === '' && $filter_priority === '' ? 'bg-neutral-900 text-white' : 'bg-neutral-50 text-neutral-600 hover:bg-neutral-100' ?>">সবাই</a>
        <?php foreach ($statuses as $k => $label): ?>
            <a href="<?= base_url('sadmin_support' . sa_support_qs(['status' => $k])); ?>"
               class="whitespace-nowrap px-3 py-1.5 rounded-lg text-xs font-bold <?= $filter_status === $k ? 'bg-neutral-900 text-white' : 'bg-neutral-50 text-neutral-600 hover:bg-neutral-100' ?>"><?= $label; ?></a>
        <?php endforeach; ?>

        <span class="w-px h-5 bg-neutral-200 mx-1 shrink-0"></span>

        <?php foreach ($priorities as $k => $label): ?>
            <a href="<?= base_url('sadmin_support' . sa_support_qs(['priority' => $k])); ?>"
               class="whitespace-nowrap px-2.5 py-1.5 rounded-lg text-xs font-bold border <?= $filter_priority === $k ? 'border-transparent ' . status_chip($k)[1] : 'border-neutral-200 text-neutral-500 hover:bg-neutral-50' ?>"><?= $label; ?></a>
        <?php endforeach; ?>

        <span class="w-px h-5 bg-neutral-200 mx-1 shrink-0"></span>

        <?php foreach ($categories as $k => $label): ?>
            <a href="<?= base_url('sadmin_support' . sa_support_qs(['category' => $k])); ?>"
               class="whitespace-nowrap px-2.5 py-1.5 rounded-lg text-xs <?= $filter_category === $k ? 'bg-indigo-600 text-white font-bold' : 'text-neutral-500 hover:bg-neutral-50' ?>"><?= $label; ?></a>
        <?php endforeach; ?>

        <a href="<?= base_url('sadmin_support' . sa_support_qs(['mine' => $filter_mine === '1' ? '' : '1'])); ?>"
           class="whitespace-nowrap sm:ml-auto px-3 py-1.5 rounded-lg text-xs font-bold <?= $filter_mine === '1' ? 'bg-indigo-600 text-white' : 'bg-neutral-50 text-neutral-600 hover:bg-neutral-100' ?> shrink-0">
            <i class="ph ph-user"></i> আমার টিকিট
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
        <table class="w-full min-w-[780px] text-left">
            <thead class="bg-neutral-50 border-b border-neutral-200">
                <tr>
                    <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-neutral-500">টিকিট</th>
                    <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-neutral-500">বিষয়</th>
                    <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-neutral-500">ইউনিয়ন</th>
                    <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-neutral-500">অগ্রাধিকার</th>
                    <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-neutral-500">অবস্থা</th>
                    <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-neutral-500">দায়িত্ব</th>
                    <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-neutral-500">বয়স</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-100">
                <?php if (empty($rows)): ?>
                    <tr>
                        <td colspan="8" class="py-14 text-center">
                            <i class="ph ph-chat-circle-dots text-4xl text-neutral-200"></i>
                            <p class="mt-2 text-sm font-bold text-neutral-500">কোনো টিকিট পাওয়া যায়নি</p>
                            <p class="mt-1 text-xs text-neutral-400">
                                <?= ($q !== '' || $filter_status !== '' || $filter_priority !== '' || $filter_tenant > 0)
                                    ? 'ফিল্টার বদলে দেখুন, অথবা "মুছুন" চাপুন।'
                                    : 'এখনো কোনো টিকিট আসেনি। ইউনিয়ন পরিষদ সাপোর্ট ফর্ম থেকে পাঠালে এখানে দেখা যাবে।' ?>
                            </p>
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($rows as $k):
                    [$st_label, $st_cls] = status_chip($k->status);
                    [$pr_label, $pr_cls] = status_chip($k->priority);
                    [$age_label, $age_cls] = sa_ticket_age($k->created_at);
                    $replies = (int) ($reply_counts[(int) $k->ticket_id] ?? 0);
                ?>
                    <tr class="hover:bg-neutral-50/80 transition-colors">
                        <td class="py-3.5 px-4 align-top">
                            <a href="<?= base_url('sadmin_support/ticket/' . (int) $k->ticket_id); ?>"
                               class="font-mono text-[11px] font-bold text-indigo-700 hover:underline"><?= htmlspecialchars($k->ticket_no, ENT_QUOTES, 'UTF-8'); ?></a>
                            <div class="text-[10px] text-neutral-400 mt-0.5"><?= htmlspecialchars(date('Y-m-d H:i', strtotime($k->created_at)), ENT_QUOTES, 'UTF-8'); ?></div>
                        </td>

                        <td class="py-3.5 px-4 align-top max-w-md">
                            <a href="<?= base_url('sadmin_support/ticket/' . (int) $k->ticket_id); ?>"
                               class="font-bold text-neutral-900 text-sm hover:text-indigo-700 block"><?= htmlspecialchars($k->subject, ENT_QUOTES, 'UTF-8'); ?></a>
                            <div class="text-[11px] text-neutral-500 mt-0.5 flex items-center gap-1.5 flex-wrap">
                                <?php if (!empty($k->user_name)): ?>
                                    <span><i class="ph ph-user text-neutral-400"></i> <?= htmlspecialchars($k->user_name, ENT_QUOTES, 'UTF-8'); ?></span>
                                <?php endif; ?>
                                <?php if ($replies > 0): ?>
                                    <span class="text-sky-700"><i class="ph ph-chat-circle"></i> <?= bn_number($replies); ?></span>
                                <?php endif; ?>
                                <span class="text-neutral-400"><?= htmlspecialchars($categories[$k->category] ?? $k->category, ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                        </td>

                        <td class="py-3.5 px-4 align-top text-[11px]">
                            <?php if (!empty($k->ins_name)): ?>
                                <div class="font-bold text-neutral-800"><?= htmlspecialchars($k->ins_name, ENT_QUOTES, 'UTF-8'); ?></div>
                                <div class="font-mono text-neutral-400 text-[10px]"><?= htmlspecialchars($k->sub_domains ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
                            <?php else: ?>
                                <?php /* tenant_id is a nullable FK that goes to NULL when the parishad is deleted, and an anonymous visitor never had one. */ ?>
                                <span class="text-neutral-400">প্রোডাক্ট সাইট / অজানা</span>
                            <?php endif; ?>
                        </td>

                        <td class="py-3.5 px-4 align-top">
                            <span class="inline-block px-2 py-0.5 rounded-md border text-[10px] font-bold <?= $pr_cls; ?>"><?= $pr_label; ?></span>
                        </td>

                        <td class="py-3.5 px-4 align-top">
                            <span class="inline-block px-2 py-0.5 rounded-md border text-[10px] font-bold <?= $st_cls; ?>"><?= $st_label; ?></span>
                        </td>

                        <td class="py-3.5 px-4 align-top text-[11px]">
                            <?php if (!empty($k->assigned_name)): ?>
                                <?php if ((int) $k->assigned_to === $me_id): ?>
                                    <span class="font-bold text-indigo-700"><?= htmlspecialchars($k->assigned_name, ENT_QUOTES, 'UTF-8'); ?> (আপনি)</span>
                                <?php else: ?>
                                    <span class="text-neutral-700"><?= htmlspecialchars($k->assigned_name, ENT_QUOTES, 'UTF-8'); ?></span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-amber-700 font-bold">অনিয়োজিত</span>
                            <?php endif; ?>
                        </td>

                        <td class="py-3.5 px-4 align-top text-[11px] <?= $age_cls; ?>"><?= $age_label; ?></td>

                        <td class="py-3.5 px-4 align-top text-right">
                            <a href="<?= base_url('sadmin_support/ticket/' . (int) $k->ticket_id); ?>"
                               class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-neutral-100 text-neutral-700 text-[11px] font-bold hover:bg-neutral-200">
                                <i class="ph ph-arrow-right"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if (!empty($rows)): ?>
        <div class="px-4 py-3 border-t border-neutral-100 bg-neutral-50/60 text-[11px] text-neutral-500 flex items-center justify-between">
            <span>সর্বোচ্চ ৩০০টি টিকিট দেখানো হয়।</span>
            <span class="bengali-num"><?= bn_number(count($rows)); ?>টি দেখানো হচ্ছে</span>
        </div>
    <?php endif; ?>
</div>

<!--
    Limits, stated on the page rather than in a doc.

    An operator who files a support ticket and never hears back will assume
    the panel is broken. It is better to say here that nothing sends
    email, than to let them discover it by waiting.
-->
<div class="flex items-start gap-2 text-[11px] text-neutral-500 bg-amber-50 border border-amber-200 rounded-2xl px-4 py-3">
    <i class="ph ph-info text-amber-600 text-base shrink-0"></i>
    <p>
        এই মডিউলটি ইমেইল <b>পাঠায় না</b> — ইউনিয়ন পরিষদকে টিকিট গেলে নোটিফিকেশন যায় না,
        তাই ব্যাকলগ সাফ করতে এই পাতাই খুলতে হবে। পাঠানো টিকিটের কোনো ইমেইল নম্বর না থাকলে
        উত্তর দেওয়ার কোনো উপায়ই নেই, সেক্ষেত্রে ফোনে যোগাযোগ করতে হবে।
    </p>
</div>

<!--
    Live ticket notifications over Server-Sent Events.

    Before this, a ticket filed anonymously on the public portal only appeared
    when somebody reloaded this page. A parishad clerk reporting a broken page
    could sit unanswered until the next manual refresh, and the amber note
    above had to admit as much. The stream ends that.

    WHAT THE CLIENT DOES, AND DELIBERATELY DOES NOT DO
    It does NOT inject a row into the table. A ticket list is a rendered
    table with counts and filters; quietly appending a row to it would mean
    two code paths keeping the same markup in sync, and a half-updated
    table is worse than a stale one. So the stream only ever signals "the
    list is out of date" — a toast, a count bump, and a reload the operator
    chooses. That also means a notification cannot leak a visitor's message
    text onto every open manager screen.

    The stream is a plain EventSource with the session cookie; there is no
    token because the endpoint only ever reads. The server sends a
    'reconnect' event when it closes the connection on purpose (5 minute
    ceiling), which is handled as a normal reconnect rather than an error.
-->
<div id="saSseToast"
     class="pointer-events-none fixed right-4 bottom-4 z-50 hidden max-w-xs rounded-2xl border border-indigo-200 bg-white p-4 shadow-xl">
    <div class="flex items-start gap-2.5">
        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-indigo-600 text-white">
            <i class="ph ph-bell-ringing text-base"></i>
        </span>
        <div class="min-w-0">
            <p id="saSseToastTitle" class="text-xs font-bold text-neutral-900"></p>
            <p id="saSseToastBody" class="mt-0.5 text-[11px] text-neutral-500"></p>
        </div>
    </div>
    <div class="mt-2.5 flex items-center gap-1.5">
        <button type="button" id="saSseOpen"
                class="rounded-lg bg-indigo-600 px-2.5 py-1.5 text-[11px] font-bold text-white transition-colors hover:bg-indigo-700">
            এখনই দেখুন
        </button>
        <button type="button" id="saSseDismiss"
                class="rounded-lg px-2.5 py-1.5 text-[11px] font-semibold text-neutral-500 transition-colors hover:bg-neutral-100">
            পরে
        </button>
    </div>
</div>

<div id="saSseStatus"
     class="fixed bottom-4 left-4 z-50 hidden items-center gap-1.5 rounded-full border border-neutral-200 bg-white px-3 py-1.5 text-[10px] font-semibold text-neutral-500 shadow-sm">
    <span id="saSseDot" class="h-2 w-2 rounded-full bg-neutral-300"></span>
    <span id="saSseStatusText">লাইভ নোটিফিকেশন বন্ধ</span>
</div>

<script>
(function () {
    // EventSource is unsupported on old Safari and on IE. The panel is fully
    // usable without it — it just means a manual refresh again, which is
    // what the panel did before this file existed. So: detect, hide the
    // status pill, and stop. Do not throw.
    if (typeof EventSource === 'undefined') return;

    var toast   = document.getElementById('saSseToast');
    var title   = document.getElementById('saSseToastTitle');
    var body    = document.getElementById('saSseToastBody');
    var openBtn = document.getElementById('saSseOpen');
    var dismiss = document.getElementById('saSseDismiss');
    var pill    = document.getElementById('saSseStatus');
    var dot     = document.getElementById('saSseDot');
    var pillTxt = document.getElementById('saSseStatusText');

    if (!toast || typeof EventSource === 'undefined') return;

    var targetHref = '';
    var pending    = 0;
    var timer      = null;
    var src        = null;

    /* Ask the server whether a live stream is safe here, before opening one.
     * On the built-in `php -S` used in development, a long-lived connection
     * consumes one of a handful of workers and every other page in the app
     * stops responding — measured at an 8-second timeout. So the server
     * decides, and the panel quietly keeps its old manual-refresh behaviour
     * there. Detection is by SAPI, not by hostname, because a dev copy and
     * a VPS can share a name.
     *
     * This runs before anything else so a "no" leaves no half-open
     * connection, no toast, and no status pill. */
    fetch('<?= base_url('sadmin_support/live'); ?>', { credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (j) {
            if (j && j.viable) { connect(); }
            else if (j && j.why) { pillTxt.textContent = j.why; }
        })
        .catch(function () {
            // Probe failed, so we cannot know. Do NOT open the stream: the
            // downside of a wrong yes is a locked-up app, the downside of a
            // wrong no is only that the manager refreshes by hand.
            pill.classList.remove('hidden');
            pill.classList.add('flex');
            pillTxt.textContent = 'লাইভ নোটিফিকেশন যাচাই করা যায়নি';
        });

    // The stream itself. Only reached once the probe said yes.
    function connect() {
        src = new EventSource('<?= base_url('sadmin_support/stream'); ?>');


    function setStatus(state, text) {
        pill.classList.remove('hidden');
        pill.classList.add('flex');
        pillTxt.textContent = text;
        dot.className = 'h-2 w-2 rounded-full ' + (
            state === 'live'  ? 'bg-emerald-500' :
            state === 'wait'  ? 'bg-amber-500'   : 'bg-neutral-300'
        );
    }

    function showToast(isTicket, ev) {
        pending++;
        if (isTicket) {
            title.textContent = 'নতুন সাপোর্ট টিকিট';
            body.textContent  = (ev.subject || 'বিষয় লেখা নেই') +
                (ev.ticket_no ? '  ·  ' + ev.ticket_no : '');
            if (ev.ticket_id) {
                targetHref = 'sadmin_support/ticket/' + ev.ticket_id;
            }
        } else {
            title.textContent = 'নতুন উত্তর';
            body.textContent  = (ev.ticket_no ? 'টিকিট ' + ev.ticket_no : '') +
                (ev.by === 'customer' ? ' — ইউনিয়ন পরিষদ থেকে' : ' — কর্মকর্তা থেকে');
        }
        toast.classList.remove('hidden');

        // Auto-hide, but only when nothing else arrived meanwhile: a second
        // ticket while the first toast is up must not be swallowed by the
        // first one's timer.
        if (timer) clearTimeout(timer);
        timer = setTimeout(function () {
            if (pending > 0) { pending = 0; toast.classList.add('hidden'); }
        }, 12000);
    }

    openBtn.addEventListener('click', function () {
        if (targetHref) window.location.href = targetHref;
    });
    dismiss.addEventListener('click', function () {
        pending = 0;
        if (timer) clearTimeout(timer);
        toast.classList.add('hidden');
    });

    src.addEventListener('ready', function () {
        setStatus('live', 'লাইভ নোটিফিকেশন চালু');
    });

    src.addEventListener('ticket', function (e) {
        var d = {}; try { d = JSON.parse(e.data); } catch (x) {}
        setStatus('live', 'লাইভ নোটিফিকেশন চালু');
        showToast(true, d);
    });

    src.addEventListener('reply', function (e) {
        var d = {}; try { d = JSON.parse(e.data); } catch (x) {}
        setStatus('live', 'লাইভ নোটিফিকেশন চালু');
        showToast(false, d);
    });

    // The server closes on purpose at its ceiling. Reconnect immediately so
    // the gap is invisible, rather than waiting out the browser's backoff.
    src.addEventListener('reconnect', function () {
        setStatus('wait', 'সংযোগ পুনরায় চালু হচ্ছে…');
    });

    src.addEventListener('forbidden', function () {
        setStatus('off', 'লাইভ নোটিফিকেশন বন্ধ');
        src.close();
    });

    // Genuine failures: let the browser retry on its own, but say so, because
    // "silently no longer live" is how a manager ends up trusting a panel
    // that is not actually watching.
    src.onerror = function () {
        setStatus('wait', 'সংযোগ বিচ্ছিন্ন — পুনরায় চেষ্টা হচ্ছে');
    };
    }   // end connect()
})();
</script>
