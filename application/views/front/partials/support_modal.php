<?php
/**
 * Public tenant portal — support hub (button + modal).
 *
 * WHY THIS EXISTS, AND WHY IT IS A SEPARATE FILE
 * ----------------------------------------------
 * The product site and the tenant admin panel both have a support button.
 * Neither helps the person most likely to hit a fault: a member of the
 * public standing in front of the union parishad office, or the clerk at
 * the counter who is not the chairman and does not hold a panel login.
 * They are on the PUBLIC portal (dhum.local and its siblings), which had
 * no support affordance at all — only a phone number buried in the footer.
 *
 * It is a separate file from p/support_modal.php rather than a shared
 * partial with a flag, because the two contexts genuinely differ:
 *
 *   p/support_modal.php  admin panel. Everyone there is signed in, so
 *                        name, email and the parishad's name are known and
 *                        get pre-filled. A support problem is usually about
 *                        the parishad's own data.
 *
 *   this file             public portal. Usually NOBODY is signed in, so
 *                        nothing can be pre-filled. The problem is usually
 *                        "this holding is not showing" — a question about
 *                        one person's own record, and the visitor may not
 *                        be the record holder.
 *
 * A shared partial would have needed $is_panel branches through the whole
 * form, and the two forms do not agree on what to even ask for.
 *
 * WHAT IT POSTS TO
 * ----------------
 * The same /support_submit the other two use, deliberately. That endpoint
 * is unauthenticated by design — its defences are a honeypot field and a
 * per-IP throttle, both already built and both verified working. Adding
 * auth here would have meant a second endpoint for no security gain, since
 * the point of this file is precisely the visitor who is not logged in.
 *
 * @see application/controllers/Support.php   the intake endpoint
 * @see application/controllers/Sadmin_support.php  the panel that answers
 * @see application/views/p/support_modal.php   the admin-panel equivalent
 * @see application/views/front/partials/footer.php  where this is loaded
 */
defined('BASEPATH') OR exit('No direct script access allowed');

$CI =& get_instance();

$ht_su = platform_contact();

/*
 * Pre-fill only if somebody IS signed in. The public portal has a real
 * login (front/login.php posts to api_login), so a chairman sitting at the
 * counter may well be signed in while a member is not — and the one who
 * is should not retype their email.
 *
 * ion_auth->user() is a QUERY BUILDER, not a user row (Ion_auth_model
 * declares both users() and user(); the builder wins the name lookup and
 * returns $this), so identity comes from the session plus one explicit
 * row fetch. See the long note in helpers/approval_helper.php.
 */
$ht_su_name = '';
$ht_su_mail = '';
$ht_su_uid  = $CI->session->userdata('user_id');
if ($ht_su_uid && isset($CI->ion_auth) && $CI->ion_auth->logged_in()) {
	$CI->db->reset_query();
	$CI->db->select('username, email')->from('users')->where('id', (int) $ht_su_uid)->limit(1);
	$ht_su_row = $CI->db->get()->row();
	if ($ht_su_row) {
		$ht_su_name = (string) $ht_su_row->username;
		$ht_su_mail = (string) $ht_su_row->email;
	}
}

/*
 * The parishad name IS known here — the resolver already worked out which
 * one this host belongs to — so it is pre-filled for everyone, signed in or
 * not. That is the one field a member should never have to type, and it is
 * what lets the panel attribute the ticket to a parishad instead of
 * leaving it unassigned. Read from ins_titute by the id tenant_code()
 * returns (ins_titute_p_idds), which is NOT the internal un_iddd_iiid.
 */
$ht_su_union = '';
$CI->db->reset_query();
$ht_su_tid = (int) tenant_code();
if ($ht_su_tid > 0) {
	$CI->db->select('ins_name')->from('ins_titute')->where('ins_titute_p_idds', $ht_su_tid)->limit(1);
	$ht_su_tenant = $CI->db->get()->row();
	if ($ht_su_tenant) {
		$ht_su_union = (string) $ht_su_tenant->ins_name;
	}
}
?>

<div class="print:hidden" style="position: fixed; right: 24px; bottom: 24px; z-index: 1050;">
    <button type="button"
            class="group relative flex cursor-pointer items-center justify-center rounded-full bg-indigo-700 text-white shadow-xl transition-all duration-200 hover:scale-105 hover:bg-indigo-800 active:scale-95"
            style="width: 52px; height: 52px;"
            data-toggle="modal" data-target="#pubSupportModal"
            title="সহায়তা ও সাপোর্ট">
        <?= icon('headset', 22, 'regular', 'transition-transform group-hover:rotate-12') ?>
        <span class="absolute top-0 right-0 h-3.5 w-3.5 rounded-full border-2 border-white bg-emerald-400"></span>
    </button>
</div>

<div class="modal fade" id="pubSupportModal" tabindex="-1" aria-labelledby="pubSupportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 520px; margin: 1.5rem auto;">
        <div class="modal-content overflow-hidden rounded-2xl border border-neutral-200 shadow-2xl">

            <div class="relative flex items-center justify-between border-b border-indigo-900 bg-indigo-950 px-5 py-3.5 text-white">
                <div class="flex items-center gap-2.5">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-white/20 bg-white/10 text-amber-400">
                        <?= icon('headset', 20, 'regular') ?>
                    </div>
                    <div class="min-w-0">
                        <h3 id="pubSupportModalLabel" class="text-sm font-bold leading-tight">সহায়তা ও সাপোর্ট</h3>
                        <p class="text-[11px] text-indigo-200">আপনার সমস্যা বা প্রশ্ন সরাসরি আমাদের টিমে জানান</p>
                    </div>
                </div>
                <button type="button" data-dismiss="modal" aria-label="বন্ধ করুন"
                        class="-mr-1 flex h-8 w-8 shrink-0 cursor-pointer items-center justify-center rounded-lg text-white transition-colors hover:bg-white/15">
                    <?= icon('x', 18, 'regular') ?>
                </button>
            </div>

            <div class="modal-body space-y-3.5 p-4 sm:p-5 text-xs">

                <!--
                    Channels, and only where the operator has filled one in
                    /sadmin_settings. A blank one is skipped rather than
                    rendered as a dead tel: link.
                -->
                <?php
                $ht_su_ch = array();
                if (!empty($ht_su['phone1'])) {
                    $ht_su_ch[] = array('href' => 'tel:' . $ht_su['phone1'], 'label' => $ht_su['phone1'], 'icon' => 'phone');
                }
                if (!empty($ht_su['whatsapp'])) {
                    $ht_su_ch[] = array(
                        'href'  => 'https://wa.me/' . preg_replace('/\D/', '', $ht_su['whatsapp']),
                        'label' => 'হোয়াটসঅ্যাপ',
                        'icon'  => 'whatsapp-logo',
                    );
                }
                if (!empty($ht_su['mail'])) {
                    $ht_su_ch[] = array('href' => 'mailto:' . $ht_su['mail'], 'label' => $ht_su['mail'], 'icon' => 'envelope');
                }
                ?>

                <?php if ($ht_su_ch): ?>
                <div class="grid gap-2 grid-cols-<?= count($ht_su_ch) > 2 ? '3' : '2' ?>">
                    <?php foreach ($ht_su_ch as $c): ?>
                    <a href="<?= htmlspecialchars($c['href'], ENT_QUOTES, 'UTF-8'); ?>"
                       class="flex items-center gap-2 rounded-xl border border-neutral-200 bg-neutral-50 px-2.5 py-2 transition-colors hover:border-indigo-300 hover:bg-indigo-50">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-indigo-600 text-white">
                            <?= icon($c['icon'], 14, 'regular') ?>
                        </span>
                        <span class="min-w-0">
                            <span class="block truncate font-semibold text-neutral-800 text-[11px]"><?= htmlspecialchars($c['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </span>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <div class="border-t border-neutral-100 pt-3">
                    <form id="pubSupportForm" class="space-y-2.5" novalidate>
                        <?php if (!empty($ht_su_union)): ?>
                            <input type="hidden" id="pubSupUnion" name="union_name" value="<?= htmlspecialchars($ht_su_union, ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-indigo-50/80 border border-indigo-100 text-[11px] text-indigo-900 font-medium">
                                <i class="ph ph-buildings text-indigo-600 text-xs"></i>
                                <span>ইউনিয়ন পরিষদ: <strong class="font-semibold"><?= htmlspecialchars($ht_su_union, ENT_QUOTES, 'UTF-8'); ?></strong></span>
                            </div>
                        <?php else: ?>
                            <div>
                                <label for="pubSupUnion" class="mb-1 block text-[11px] font-bold text-neutral-700">ইউনিয়ন পরিষদের নাম</label>
                                <input type="text" id="pubSupUnion" name="union_name"
                                       value=""
                                       placeholder="ইউনিয়নের নাম লিখুন"
                                       class="w-full rounded-xl border border-neutral-200 bg-neutral-50 px-3 py-1.5 text-xs focus:border-indigo-500 focus:outline-none">
                            </div>
                        <?php endif; ?>

                        <div>
                            <label for="pubSupSubject" class="mb-1 block text-[11px] font-bold text-neutral-700">বিষয় <span class="text-rose-600">*</span></label>
                            <input type="text" id="pubSupSubject" name="subject" required maxlength="255"
                                   placeholder="যেমন: হোল্ডিং নম্বর বা নাগরিক সেবা অনুসন্ধান"
                                   class="w-full rounded-xl border border-neutral-200 bg-neutral-50 px-3 py-2 text-xs focus:border-indigo-500 focus:outline-none">
                        </div>

                        <div>
                            <label for="pubSupMessage" class="mb-1 block text-[11px] font-bold text-neutral-700">আপনার সমস্যা বা জিজ্ঞাসা <span class="text-rose-600">*</span></label>
                            <textarea id="pubSupMessage" name="message" rows="3" required
                                      placeholder="কী বিষয়ে জানতে চান বা সমস্যা হচ্ছে বিস্তারিত লিখুন..."
                                      class="w-full resize-y rounded-xl border border-neutral-200 bg-neutral-50 px-3 py-2 text-xs focus:border-indigo-500 focus:outline-none"></textarea>
                        </div>

                        <div class="grid gap-2.5 sm:grid-cols-2">
                            <div>
                                <label for="pubSupName" class="mb-1 block text-[11px] font-bold text-neutral-700">আপনার নাম</label>
                                <input type="text" id="pubSupName" name="name"
                                       value="<?= htmlspecialchars($ht_su_name, ENT_QUOTES, 'UTF-8'); ?>"
                                       placeholder="নাম লিখুন"
                                       class="w-full rounded-xl border border-neutral-200 bg-neutral-50 px-3 py-1.5 text-xs focus:border-indigo-500 focus:outline-none">
                            </div>
                            <div>
                                <label for="pubSupPhone" class="mb-1 block text-[11px] font-bold text-neutral-700">মোবাইল নম্বর</label>
                                <input type="tel" id="pubSupPhone" name="phone"
                                       placeholder="০১XXXXXXXXX"
                                       class="w-full rounded-xl border border-neutral-200 bg-neutral-50 px-3 py-1.5 text-xs focus:border-indigo-500 focus:outline-none">
                            </div>
                        </div>

                        <div>
                            <label for="pubSupEmail" class="mb-1 block text-[11px] font-bold text-neutral-700">ইমেইল (ঐচ্ছিক)</label>
                            <input type="email" id="pubSupEmail" name="email"
                                   value="<?= htmlspecialchars($ht_su_mail, ENT_QUOTES, 'UTF-8'); ?>"
                                   placeholder="example@mail.com"
                                   class="w-full rounded-xl border border-neutral-200 bg-neutral-50 px-3 py-1.5 text-xs focus:border-indigo-500 focus:outline-none">
                        </div>

                        <!--
                            Honeypot. Support::submit() treats a filled value as
                            a bot, answers with a success message and stores
                            nothing, so a bot told it failed would just try the
                            next field name. Must stay in the markup.
                        -->
                        <div class="hidden" aria-hidden="true">
                            <label for="pubSupWebsite">Website</label>
                            <input type="text" id="pubSupWebsite" name="website" tabindex="-1" autocomplete="off">
                        </div>

                        <div id="pubSupResult" class="hidden rounded-xl border px-3 py-2 text-[11px] font-semibold" role="status"></div>

                        <div class="flex items-center justify-end gap-2 pt-1">
                            <button type="button" data-dismiss="modal"
                                    class="rounded-lg px-3 py-1.5 text-xs font-semibold text-neutral-600 transition-colors hover:bg-neutral-100">
                                বাতিল
                            </button>
                            <button type="submit" id="pubSupSend"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-1.5 text-xs font-bold text-white shadow-xs transition-colors hover:bg-indigo-700">
                                <i class="ph ph-paper-plane-tilt text-xs"></i>
                                <span>পাঠান</span>
                            </button>
                        </div>
                    </form>

                    <p class="mt-2.5 text-[10px] leading-relaxed text-neutral-400">
                        টিকিট সাবমিট করার পর একটি ট্র্যাকিং নম্বর দেওয়া হবে। সেটি সংরক্ষণ করুন।
                    </p>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
(function () {
    var form = document.getElementById('pubSupportForm');
    if (!form) return;

    var result  = document.getElementById('pubSupResult');
    var sendBtn = document.getElementById('pubSupSend');
    var busy    = false;

    function paint(ok, msg) {
        result.textContent = msg;
        result.className = 'rounded-xl border px-3 py-2.5 text-[11px] font-semibold '
            + (ok ? 'border-emerald-200 bg-emerald-50 text-emerald-800'
                  : 'border-rose-200 bg-rose-50 text-rose-800');
        result.classList.remove('hidden');
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (busy) return;

        var subject = form.subject.value.trim();
        var message = form.message.value.trim();
        if (!subject) { paint(false, 'বিষয় লিখুন।'); form.subject.focus(); return; }
        if (!message) { paint(false, 'আপনার সমস্যা বা প্রশ্নটি লিখুন।'); form.message.focus(); return; }

        busy = true;
        sendBtn.disabled = true;
        sendBtn.textContent = 'পাঠানো হচ্ছে…';

        var body = {
            subject:    subject,
            message:    message,
            name:       form.name.value.trim(),
            email:      form.email.value.trim(),
            phone:      form.phone.value.trim(),
            union_name: form.union_name.value.trim(),
            category:   'general',
            website:    form.website.value          // honeypot, left empty by humans
        };

        fetch('<?= base_url('support_submit'); ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify(body)
        })
        .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, j: j }; }); })
        .then(function (res) {
            if (res.ok && res.j && res.j.success) {
                paint(true, (res.j.message || 'ধন্যবাদ! আপনার বার্তা পৌঁছেছে।')
                    + (res.j.ticket_no ? '  টিকিট নম্বর: ' + res.j.ticket_no : ''));
                form.reset();
                // union_name and any pre-filled identity belong to the page,
                // not to the visitor, so put them back after reset().
                form.union_name.value = <?= json_encode($ht_su_union); ?>;
                form.name.value       = <?= json_encode($ht_su_name); ?>;
                form.email.value      = <?= json_encode($ht_su_mail); ?>;
            } else {
                paint(false, (res.j && res.j.message) || 'পাঠানো যায়নি। একটু পরে আবার চেষ্টা করুন।');
            }
        })
        .catch(function () {
            paint(false, 'সংযোগ বিচ্ছিন্ন। ইন্টারনেট সংযোগ পরীক্ষা করে আবার চেষ্টা করুন।');
        })
        .then(function () {
            busy = false;
            sendBtn.disabled = false;
            sendBtn.textContent = 'পাঠান';
        });
    });
})();
</script>
