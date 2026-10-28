<?php
/**
 * One support ticket — the thread, the controls and the reply box.
 *
 * Rendered inside Template::sadmin(). Reached from the ticket list, never
 * linked to from anywhere else.
 *
 * THE ORIGINAL POST IS NOT STYLED LIKE A REPLY
 * It comes out of the intake form and is rendered inside a quoted box, in
 * the requester's own voice. A staff reply is a message. Mixing the two
 * would make the first thing the customer wrote look like something the
 * support agent said, which is exactly the confusion that loses a dispute
 * about what was actually asked.
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Render a timestamp as "৩০ সেপ্টেম্বর ২০২৬, ১৪:০৫" — month name, not a
 * numeric timestamp.
 *
 * The panel shows these all over and a raw "2026-09-30 14:05" in a thread
 * is unreadable next to a Bangla sentence. English month names on purpose:
 * "সেপ্ট" is truncated on some renderers, and a wrong-looking month is
 * worse than an English one. This is the same choice the rest of the
 * project makes for anything a human reads at a glance.
 */
function sa_ticket_when($ts)
{
    if (empty($ts)) {
        return '—';
    }
    $m = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    $t = strtotime($ts);
    return bn_number((int) date('j', $t)) . ' ' . $m[(int) date('n', $t) - 1]
        . ' ' . bn_number(date('Y', $t)) . ', ' . bn_number(date('H:i', $t));
}
?>

<!--
    Back to the list.

    A flat link, not history(). history() takes an operator back to whatever
    they had open before this ticket, which for an agent working a queue is
    another ticket or the list under a different filter — and a "back" that
    lands somewhere unrelated is worse than a link that always means "the
    queue".
-->
<div class="flex items-center justify-between gap-3 flex-wrap">
    <a href="<?= base_url('sadmin_support'); ?>"
       class="inline-flex items-center gap-1.5 text-xs font-bold text-neutral-600 hover:text-indigo-700">
        <i class="ph ph-arrow-left"></i> সব টিকিটে ফিরুন
    </a>
    <span class="text-[11px] text-neutral-400 font-mono"><?= htmlspecialchars($ticket->ticket_no, ENT_QUOTES, 'UTF-8'); ?></span>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 items-start">

    <!-- ================= Thread ================= -->
    <div class="lg:col-span-2 space-y-4">

        <!-- The original request -->
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 sm:p-5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[11px] font-bold text-amber-700 uppercase tracking-wider">আসল প্রশ্ন</p>
                    <p class="font-bold text-neutral-900 text-sm mt-0.5"><?= htmlspecialchars($ticket->subject, ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
                <span class="text-[10px] text-amber-700 shrink-0"><?= htmlspecialchars(sa_ticket_when($ticket->created_at), ENT_QUOTES, 'UTF-8'); ?></span>
            </div>

            <!--
                The from-line carries the contact details an agent needs to
                answer, and says so when there are none. A ticket from the
                public form with no email or phone is not answerable, and
                the thread should say that at the top instead of three
                messages later.
            -->
            <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-[11px] text-amber-900/80">
                <span><i class="ph ph-user"></i> <?= htmlspecialchars($ticket->user_name ?: 'নাম দেওয়া হয়নি', ENT_QUOTES, 'UTF-8'); ?></span>
                <?php if (!empty($ticket->user_email)): ?>
                    <a href="mailto:<?= htmlspecialchars($ticket->user_email, ENT_QUOTES, 'UTF-8'); ?>"
                       class="font-mono hover:underline"><?= htmlspecialchars($ticket->user_email, ENT_QUOTES, 'UTF-8'); ?></a>
                <?php endif; ?>
                <?php if (!empty($ticket->user_phone)): ?>
                    <span class="font-mono"><?= htmlspecialchars($ticket->user_phone, ENT_QUOTES, 'UTF-8'); ?></span>
                <?php endif; ?>
                <span class="px-1.5 py-0.5 rounded bg-amber-100 border border-amber-300 font-bold"><?= htmlspecialchars($categories[$ticket->category] ?? $ticket->category, ENT_QUOTES, 'UTF-8'); ?></span>
            </div>

            <?php if (empty($ticket->user_email) && empty($ticket->user_phone)): ?>
                <p class="mt-2 text-[11px] font-bold text-rose-700 flex items-center gap-1">
                    <i class="ph ph-warning"></i>
                    এই টিকিটে ইমেইল বা ফোন নম্বর নেই — উত্তর দিতে হলে ফোনে যোগাযোগ করতে হবে।
                </p>
            <?php endif; ?>

            <div class="mt-3 pt-3 border-t border-amber-200 text-sm text-neutral-800 whitespace-pre-line leading-relaxed">
                <?= htmlspecialchars($ticket->message, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        </div>

        <!-- Replies -->
        <div id="saRepliesList" class="space-y-4">
            <?php if (empty($replies)): ?>
                <div id="saEmptyReplies" class="bg-white border border-dashed border-neutral-300 rounded-2xl py-10 text-center">
                    <i class="ph ph-chat-circle text-3xl text-neutral-200"></i>
                    <p class="mt-2 text-xs text-neutral-500">এখনো কোনো উত্তর দেওয়া হয়নি।</p>
                </div>
            <?php endif; ?>

            <?php foreach ($replies as $r): ?>
                <?php
                $is_staff = ($r->sender_role === 'staff');
                $who = $r->sender_name ?: ($r->sender_username ?: ($is_staff ? 'সাপোর্ট' : 'গ্রাহক'));
                ?>
                <div data-reply-id="<?= (int) $r->reply_id; ?>"
                     class="<?= $is_staff
                        ? 'bg-white border border-neutral-200 rounded-2xl p-4 sm:p-5'
                        : 'bg-sky-50 border border-sky-200 rounded-2xl p-4 sm:p-5'; ?> shadow-xs">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="w-7 h-7 rounded-lg <?= $is_staff ? 'bg-indigo-600 text-white' : 'bg-sky-200 text-sky-900' ?> flex items-center justify-center shrink-0">
                                <i class="ph <?= $is_staff ? 'ph-headset' : 'ph-user' ?> text-sm"></i>
                            </span>
                            <span class="font-bold text-sm <?= $is_staff ? 'text-indigo-800' : 'text-sky-900' ?>"><?= htmlspecialchars($who, ENT_QUOTES, 'UTF-8'); ?></span>
                            <span class="text-[10px] px-1.5 py-0.5 rounded <?= $is_staff ? 'bg-indigo-50 text-indigo-700' : 'bg-sky-100 text-sky-800' ?> font-bold"><?= $is_staff ? 'সাপোর্ট' : 'গ্রাহক'; ?></span>
                        </div>
                        <span class="text-[10px] text-neutral-400 shrink-0"><?= htmlspecialchars(sa_ticket_when($r->created_at), ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                    <div class="mt-2.5 text-sm text-neutral-800 whitespace-pre-line leading-relaxed">
                        <?= htmlspecialchars($r->message, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Reply composer -->
        <div class="bg-white border border-neutral-200 rounded-2xl p-4 sm:p-5 shadow-xs">
            <p class="text-xs font-bold text-neutral-700 flex items-center gap-1.5">
                <i class="ph ph-reply text-indigo-600"></i> উত্তর দিন
            </p>

            <textarea id="saReplyBody" rows="5" maxlength="5000"
                      placeholder="আপনার উত্তর লিখুন…"
                      class="mt-2 w-full p-3 rounded-lg border border-neutral-300 text-sm focus:border-indigo-600 focus:outline-none"></textarea>

            <div class="mt-2 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <p class="text-[11px] text-neutral-400">
                    উত্তরটি <b>ইমেইলে পাঠানো হবে না</b> — শুধু এই টিকিটে সংরক্ষিত থাকবে।
                </p>
                <button type="button" onclick="saSendReply(<?= (int) $ticket->ticket_id; ?>)"
                        class="w-full sm:w-auto px-5 py-2.5 rounded-lg bg-indigo-700 text-white text-xs font-bold hover:bg-indigo-800 cursor-pointer text-center">
                    উত্তর পাঠান
                </button>
            </div>

            <div id="saReplyMsg" class="mt-3 hidden"></div>
        </div>
    </div>

    <!-- ================= Sidebar ================= -->
    <div class="space-y-4">

        <!-- Status / priority -->
        <div class="bg-white border border-neutral-200 rounded-2xl p-4 shadow-xs">
            <p class="text-xs font-bold text-neutral-700 mb-3">অবস্থা ও অগ্রাধিকার</p>

            <label class="block text-[11px] font-bold text-neutral-500 mb-1">অবস্থা</label>
            <select id="saStatusSel" class="w-full p-2.5 rounded-lg border border-neutral-300 text-xs focus:border-indigo-600 focus:outline-none bg-white">
                <?php foreach ($statuses as $k => $label): ?>
                    <option value="<?= $k; ?>" <?= $ticket->status === $k ? 'selected' : '' ?>><?= $label; ?></option>
                <?php endforeach; ?>
            </select>

            <label class="block text-[11px] font-bold text-neutral-500 mb-1 mt-3">অগ্রাধিকার</label>
            <select id="saPrioritySel" class="w-full p-2.5 rounded-lg border border-neutral-300 text-xs focus:border-indigo-600 focus:outline-none bg-white">
                <?php foreach ($priorities as $k => $label): ?>
                    <option value="<?= $k; ?>" <?= $ticket->priority === $k ? 'selected' : '' ?>><?= $label; ?></option>
                <?php endforeach; ?>
            </select>

            <?php if (!empty($ticket->closed_at)): ?>
                <p class="mt-3 text-[11px] text-neutral-500 bg-neutral-50 border border-neutral-200 rounded-lg px-3 py-2">
                    <i class="ph ph-check-circle"></i>
                    সমাধান/বন্ধ হয়েছে: <?= htmlspecialchars(sa_ticket_when($ticket->closed_at), ENT_QUOTES, 'UTF-8'); ?>
                </p>
            <?php endif; ?>

            <button type="button" onclick="saSaveMeta()"
                    class="mt-3 w-full px-4 py-2.5 rounded-lg bg-neutral-900 text-white text-xs font-bold hover:bg-black cursor-pointer">
                সংরক্ষণ
            </button>
            <div id="saMetaMsg" class="mt-2 hidden"></div>
        </div>

        <!-- Assignment -->
        <div class="bg-white border border-neutral-200 rounded-2xl p-4 shadow-xs">
            <p class="text-xs font-bold text-neutral-700 mb-1">দায়িত্ব</p>
            <p class="text-[11px] text-neutral-400 mb-3">শুধু স্টাফকে টিকিট দেওয়া যায়।</p>

            <select id="saAssigneeSel" class="w-full p-2.5 rounded-lg border border-neutral-300 text-xs focus:border-indigo-600 focus:outline-none bg-white">
                <option value="0">— অনিয়োজিত —</option>
                <?php foreach ($assignees as $a): ?>
                    <option value="<?= (int) $a->id; ?>" <?= (int) $ticket->assigned_to === (int) $a->id ? 'selected' : '' ?>>
                        <?= htmlspecialchars(trim($a->first_name ?? '') ?: $a->username, ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="button" onclick="saSaveAssignee()"
                    class="mt-2.5 w-full px-4 py-2.5 rounded-lg bg-indigo-700 text-white text-xs font-bold hover:bg-indigo-800 cursor-pointer">
                দায়িত্ব দিন
            </button>
            <div id="saAssignMsg" class="mt-2 hidden"></div>
        </div>

        <!-- Requester -->
        <div class="bg-white border border-neutral-200 rounded-2xl p-4 shadow-xs text-[11px] space-y-2">
            <p class="text-xs font-bold text-neutral-700">যিনি পাঠিয়েছেন</p>
            <div class="text-neutral-600">
                <i class="ph ph-user text-neutral-400"></i>
                <?= htmlspecialchars($ticket->user_name ?: 'নাম দেওয়া হয়নি', ENT_QUOTES, 'UTF-8'); ?>
            </div>
            <?php if (!empty($ticket->user_email)): ?>
                <div class="font-mono text-neutral-600 break-all"><?= htmlspecialchars($ticket->user_email, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            <?php if (!empty($ticket->user_phone)): ?>
                <div class="font-mono text-neutral-600"><?= htmlspecialchars($ticket->user_phone, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            <div class="pt-2 border-t border-neutral-100 text-neutral-400">
                ইউনিয়ন: <?= htmlspecialchars($ticket->ins_name ?: 'প্রোডাক্ট সাইট / অজানা', ENT_QUOTES, 'UTF-8'); ?>
            </div>
            <?php if (!empty($ticket->tenant_id)): ?>
                <a href="<?= base_url('sadmin_tenants'); ?>" class="text-indigo-700 font-bold hover:underline">টেন্যান্ট তালিকায় দেখুন</a>
            <?php endif; ?>
        </div>

        <!-- Danger -->
        <div class="bg-white border border-rose-200 rounded-2xl p-4 shadow-xs">
            <p class="text-xs font-bold text-rose-700">টিকিট মুছুন</p>
            <p class="text-[11px] text-neutral-500 mt-1">
                শুধু স্প্যাম বা ভুল করে পাঠানো টিকিটের জন্য। উত্তর থাকলে মোছা যাবে না —
                সেক্ষেত্রে অবস্থা "বন্ধ" করে দিন।
            </p>
            <button type="button" onclick="saDeleteTicket(<?= (int) $ticket->ticket_id; ?>)"
                    class="mt-2.5 w-full px-4 py-2.5 rounded-lg bg-rose-600 text-white text-xs font-bold hover:bg-rose-700 cursor-pointer">
                <i class="ph ph-trash"></i> টিকিট মুছুন
            </button>
            <div id="saDeleteMsg" class="mt-2 hidden"></div>
        </div>
    </div>
</div>

<script>
    const SA_TICKET_ID = <?= (int) $ticket->ticket_id; ?>;

    const saPost = (path, payload) => fetch('sadmin_support/' + path, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(payload)
    }).then(r => r.json());

    function saBox(id, kind, text) {
        const el = document.getElementById(id);
        el.className = 'mt-2 p-2.5 rounded-lg text-[11px] font-bold ' + (kind === 'ok'
            ? 'bg-emerald-50 text-emerald-800 border border-emerald-200'
            : 'bg-rose-50 text-rose-700 border border-rose-200');
        el.textContent = text;
    }

    const SA_TICKET_TOKEN = <?= json_encode($ticket_token); ?>;
    const seenReplyIds = new Set();
    document.querySelectorAll('[data-reply-id]').forEach(el => {
        seenReplyIds.add(Number(el.getAttribute('data-reply-id')));
    });

    function playLiveChime() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(587.33, ctx.currentTime);
            osc.frequency.exponentialRampToValueAtTime(880, ctx.currentTime + 0.15);
            gain.gain.setValueAtTime(0.12, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.25);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + 0.25);
        } catch (e) {}
    }

    function appendReplyCard(r) {
        if (r.reply_id && seenReplyIds.has(Number(r.reply_id))) return;
        if (r.reply_id) seenReplyIds.add(Number(r.reply_id));

        const emptyBox = document.getElementById('saEmptyReplies');
        if (emptyBox) emptyBox.remove();

        const isStaff = (r.sender_role === 'staff' || r.by === 'staff');
        const who = r.sender_name || (isStaff ? 'সাপোর্ট' : 'গ্রাহক');
        const when = r.created_at || 'এইমাত্র';
        const rawMsg = r.message || '';
        const msg = rawMsg.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\n/g, '<br>');

        const card = document.createElement('div');
        if (r.reply_id) card.setAttribute('data-reply-id', r.reply_id);
        card.className = (isStaff
            ? 'bg-white border border-neutral-200 rounded-2xl p-4 sm:p-5'
            : 'bg-sky-50 border border-sky-300 rounded-2xl p-4 sm:p-5 ring-2 ring-sky-300 animate-pulse') + ' shadow-xs transition-all';

        card.innerHTML = `
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-7 h-7 rounded-lg ${isStaff ? 'bg-indigo-600 text-white' : 'bg-sky-200 text-sky-900'} flex items-center justify-center shrink-0">
                        <i class="ph ${isStaff ? 'ph-headset' : 'ph-user'} text-sm"></i>
                    </span>
                    <span class="font-bold text-sm ${isStaff ? 'text-indigo-800' : 'text-sky-900'}">${who}</span>
                    <span class="text-[10px] px-1.5 py-0.5 rounded ${isStaff ? 'bg-indigo-50 text-indigo-700' : 'bg-sky-100 text-sky-800'} font-bold">${isStaff ? 'সাপোর্ট' : 'গ্রাহক'}</span>
                    ${!isStaff ? '<span class="text-[10px] bg-emerald-100 text-emerald-800 font-bold px-1.5 py-0.5 rounded">নতুন লাইভ বার্তা</span>' : ''}
                </div>
                <span class="text-[10px] text-neutral-400 shrink-0">${when}</span>
            </div>
            <div class="mt-2.5 text-sm text-neutral-800 whitespace-pre-line leading-relaxed">
                ${msg}
            </div>
        `;

        const list = document.getElementById('saRepliesList');
        if (list) {
            list.appendChild(card);
            card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            setTimeout(() => card.classList.remove('ring-2', 'ring-sky-300', 'animate-pulse'), 5000);
        }

        if (!isStaff) {
            playLiveChime();
        }
    }

    function saSendReply(id) {
        const body = document.getElementById('saReplyBody');
        const text = body.value.trim();
        if (!text) { saBox('saReplyMsg', 'err', 'উত্তর লিখুন।'); return; }

        saBox('saReplyMsg', 'ok', 'পাঠানো হচ্ছে…');
        saPost('api_reply', {ticket_id: id, message: text})
            .then(r => {
                if (r.success) {
                    body.value = '';
                    saBox('saReplyMsg', 'ok', r.message || 'উত্তর যোগ হয়েছে।');
                    setTimeout(() => { document.getElementById('saReplyMsg').classList.add('hidden'); }, 3000);
                    if (r.reply) {
                        appendReplyCard(r.reply);
                    }
                } else {
                    saBox('saReplyMsg', 'err', r.message);
                }
            })
            .catch(() => saBox('saReplyMsg', 'err', 'সার্ভারে সংযোগ স্থাপন করা যায়নি।'));
    }

    // Connect to live SSE for real-time customer replies on this ticket
    (function initTicketSse() {
        if (typeof EventSource === 'undefined') return;
        try {
            const sseUrl = 'support/live_stream?ticket_id=' + SA_TICKET_ID + '&token=' + encodeURIComponent(SA_TICKET_TOKEN);
            const src = new EventSource(sseUrl);

            src.addEventListener('reply', function (e) {
                try {
                    const data = JSON.parse(e.data);
                    if (data && Number(data.ticket_id) === SA_TICKET_ID) {
                        appendReplyCard(data);
                    }
                } catch (err) {}
            });
        } catch (e) {}
    })();

    function saSaveMeta() {
        saPost('api_set_status', {
            ticket_id: SA_TICKET_ID,
            status: document.getElementById('saStatusSel').value,
            priority: document.getElementById('saPrioritySel').value
        }).then(r => {
            if (r.success) { saBox('saMetaMsg', 'ok', r.message); setTimeout(() => location.reload(), 900); }
            else saBox('saMetaMsg', 'err', r.message);
        }).catch(() => saBox('saMetaMsg', 'err', 'সার্ভারে সংযোগ স্থাপন করা যায়নি।'));
    }

    function saSaveAssignee() {
        saPost('api_set_status', {
            ticket_id: SA_TICKET_ID,
            assigned_to: Number(document.getElementById('saAssigneeSel').value)
        }).then(r => {
            if (r.success) { saBox('saAssignMsg', 'ok', r.message); setTimeout(() => location.reload(), 900); }
            else saBox('saAssignMsg', 'err', r.message);
        }).catch(() => saBox('saAssignMsg', 'err', 'সার্ভারে সংযোগ স্থাপন করা যায়নি।'));
    }

    function saDeleteTicket(id) {
        vConfirm('সাপোর্ট টিকিট মুছে ফেলা', 'আপনি কি নিশ্চিত যে এই সাপোর্ট টিকিটটি স্থায়ীভাবে মুছে ফেলতে চান?', { danger: true }).then(function(confirmed) {
            if (!confirmed) return;

            saPost('api_delete', {ticket_id: id})
                .then(r => { if (r.success) { window.location = 'sadmin_support'; } else saBox('saDeleteMsg', 'err', r.message); })
                .catch(() => saBox('saDeleteMsg', 'err', 'সার্ভারে সংযোগ স্থাপন করা যায়নি।'));
        });
    }
</script>