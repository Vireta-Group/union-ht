<?php
/**
 * Product-site contact page.
 *
 * Ported from resources/js/pages/ContactPage.jsx and the
 * resources/js/components/Contact.jsx it rendered. That component is used by
 * this page only, so it became a section here rather than a partial.
 *
 * THE FORM NOW HAS AN ENDPOINT, and it did not for the whole life of the
 * React original. Contact.jsx's entire submit handler was this:
 *
 *     const handleSubmit = (e) => { e.preventDefault(); setSubmitted(true); };
 *
 * There is no fetch(), no XHR, no <form action>, no endpoint anywhere in the
 * SPA. The six fields were collected into React state and then discarded, and
 * setSubmitted() swapped the form for a green panel reading
 * "ধন্যবাদ! ডেমো রিকোয়েস্ট গৃহীত হয়েছে" that had never received a single byte. The request was never
 * sent; only the appearance of one was.
 *
 * It posts to Support::submit() at POST /support_submit. That endpoint is
 * content-negotiated (see the controller), and a plain <form method="post">
 * with no JS takes its redirect branch: on success it sets a flash message
 * and sends the browser back here, on failure it sets an error flash and
 * comes back here too, with the submitted values re-flashed so a validation
 * mistake does not cost the visitor their whole message. There is no
 * client-side ticket number and no success panel — the flash banner is the
 * acknowledgement, and /contact re-rendering with the banner at the top is
 * the whole flow. The ticket number is not shown to the visitor because the
 * helpdesk is not a self-service portal: nothing in the product depends on
 * the visitor being able to look a ticket up.
 *
 * FIELD NAMES CHANGED, deliberately. They were the React state keys
 * (unionName, contactPerson) and are now what the endpoint reads
 * (union_name, name). Nothing read them — the form had no reader — so the
 * rename costs nothing and keeps one vocabulary instead of two.
 *
 * The plan <select> still takes its default from a ?plan= query param, the
 * same conversion /plans -> /register already uses.
 *
 * @var string $saas_path  Active route, '/contact'. Read by the navbar and
 *                         footer for highlighting; unused on this page.
 */

// Every contact channel on this page comes from satting_infos via
// platform_contact() (application/helpers/platform_helper.php). Before that
// helper existed these values were written into the markup by hand and had
// already drifted away from the settings: the WhatsApp number and the inbox
// address here were both wrong, and the displayed phone was the literal
// placeholder "০১৭১৩-XXXXXX" sitting beside a working tel: link carrying the
// real number — the page said one thing and dialled another.
$saas_c = platform_contact();

/*
 * A blank settings field means the operator cleared that channel, and the
 * card is dropped rather than rendered empty. That is the behaviour the
 * settings hint promises for vphone2 ("খালি রাখলে মডালে দেখানো হবে না"),
 * and the reason this array is built conditionally rather than being a
 * literal.
 */
$saas_channels = array();

// The four channel cards. Identical markup in the React original apart from
// the icon, the colour pair and whether the value is a link or plain text.
if ($saas_c['phone1_href'] !== '') {
    $saas_channels[] = array(
        'icon'   => 'phone',
        'weight' => 'bold',
        'chip'   => 'bg-indigo-50 border border-indigo-100 text-indigo-600',
        'label'  => 'হটলাইন নম্বর',
        'value'  => $saas_c['phone1_label'],
        'href'   => $saas_c['phone1_href'],
        'kind'   => 'link',
        'hover'  => 'hover:text-indigo-600',
        // A note restating what the card already says is noise. The hours
        // appear here only when settings actually has them.
        'note'   => $saas_c['hours'] !== '' ? $saas_c['hours'] : 'সরাসরি ফোন কল',
    );
}

if ($saas_c['phone2_href'] !== '') {
    $saas_channels[] = array(
        'icon'   => 'phone',
        'weight' => 'regular',
        'chip'   => 'bg-indigo-50 border border-indigo-100 text-indigo-600',
        'label'  => 'বিকল্প নম্বর',
        'value'  => $saas_c['phone2_label'],
        'href'   => $saas_c['phone2_href'],
        'kind'   => 'link',
        'hover'  => 'hover:text-indigo-600',
        'note'   => 'নম্বরটি বন্ধ থাকলে অপর নম্বরে কল করুন',
    );
}

if ($saas_c['whatsapp_href'] !== '') {
    $saas_channels[] = array(
        'icon'   => 'whatsapp-logo',
        'weight' => 'fill',
        'chip'   => 'bg-emerald-50 border border-emerald-100 text-emerald-600',
        'label'  => 'WhatsApp চ্যাট',
        'value'  => '+' . $saas_c['whatsapp'],
        'href'   => $saas_c['whatsapp_href'],
        'kind'   => 'ext',
        'hover'  => 'hover:text-emerald-700',
        'note'   => 'তাত্ক্ষণিক স্ক্রিনশট ও চ্যাট',
    );
}

if ($saas_c['mail_href'] !== '') {
    $saas_channels[] = array(
        'icon'   => 'envelope',
        'weight' => 'bold',
        'chip'   => 'bg-amber-50 border border-amber-100 text-amber-700',
        'label'  => 'ইমেইল হেল্পডেস্ক',
        'value'  => $saas_c['mail'],
        'href'   => $saas_c['mail_href'],
        'kind'   => 'link',
        'hover'  => 'hover:text-amber-800',
        'note'   => 'অফিশিয়াল সাপোর্ট ইনবক্স',
    );
}

// Hours get their own card only when the phone card did not already carry
// them, so the same string never appears on the page twice.
if ($saas_c['hours'] !== '' && $saas_c['phone1_href'] === '') {
    $saas_channels[] = array(
        'icon'   => 'clock',
        'weight' => 'bold',
        'chip'   => 'bg-purple-50 border border-purple-100 text-purple-700',
        'label'  => 'অফিস সময়',
        'value'  => $saas_c['hours'],
        'href'   => '',
        'kind'   => 'span',
        'hover'  => '',
        'note'   => 'এই সময়ের বাইরে ফোন না পেলে বার্তা লিখে রাখুন',
    );
}

// If every channel was cleared, the grid would render as a row of empty
// columns. Say so in the same place rather than showing a bare layout.
$saas_no_channel = ($saas_channels === []);

/*
 * Re-populating the form after a rejected submission. Support::reply()
 * flashes the submitted values under 'old' on the failure branch only, so
 * this is empty on a first visit and after a success — a stale copy of
 * somebody's message must not reappear in a field they already sent.
 *
 * Every value goes through htmlspecialchars() at the point of output. The
 * flash round-trips through the session, so this is user input arriving by
 * a second route, and the reason it is printed with a helper rather than
 * trusted is the same as any other echo in this codebase.
 */
$saas_old = (array) $this->session->flashdata('old');
$saas_v   = function ($key) use ($saas_old) {
    return htmlspecialchars((string) ($saas_old[$key] ?? ''), ENT_QUOTES, 'UTF-8');
};

// The <select> options, loaded from DB packages if available.
$saas_plans = array();
if (!empty($saas_packages)) {
    foreach ($saas_packages as $p) {
        $price_str = ($p->price_yearly > 0) ? (taka($p->price_yearly) . ' / বাৎসরিক') : 'কাস্টম';
        $saas_plans[$p->package_key] = $p->name_bn . ' প্যাকেজ (' . $price_str . ')';
    }
} else {
    $saas_plans = array(
        'premium'  => 'প্রিমিয়াম প্যাকেজ (৳৪০,০০০ / বাৎসরিক)',
        'standard' => 'স্ট্যান্ডার্ড প্যাকেজ (৳২৫,০০০ / বাৎসরিক)',
        'cluster'  => 'উপজেলা ক্লাস্টার প্যাকেজ (কাস্টম)',
    );
}

// ?plan= preselect. /plans sends ?plan= to the register page already; on the
// React site the same value arrived here as the selectedPlan prop.
//
// The flashed value from a rejected submission WINS over the query param:
// a visitor who picks a package, is told their message is missing, and
// comes back, must not silently have the package reset to the default they
// had already changed. The param is only consulted when there is nothing
// to restore.
$saas_plan = (string) ($saas_old['plan'] ?? '');
if ($saas_plan === '') {
    $saas_plan = (string) $this->input->get('plan');
}
if ( ! isset($saas_plans[$saas_plan])) {
    $saas_plan = 'premium';
}

/*
 * The flash banner. flashdata() marks a key as read on FIRST access, so
 * both are read here into variables and never asked for again in the
 * markup — a second read returns '' and the banner silently disappears on
 * exactly the request it exists for.
 *
 * The text is escaped even though every message is written by
 * Support::reply() itself. A flash is session state, and the session is
 * one of the places a string can arrive from somewhere other than where it
 * was written; this is cheap and it is the rule the rest of the codebase
 * already follows.
 */
$saas_ok  = (string) $this->session->flashdata('success');
$saas_err = (string) $this->session->flashdata('error');
?>

        <main class="grow py-12">

            <!-- Page Header -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-12">
                <div class="text-center max-w-3xl mx-auto">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800 mb-4">
                        <?= icon('envelope', 16, 'bold') ?>
                        <span>সরাসরি যোগাযোগ ও সহায়তা</span>
                    </span>
                    <h1 class="text-3xl sm:text-5xl font-extrabold text-neutral-900 tracking-tight leading-tight">
                        আমাদের সাথে যোগাযোগ করুন
                    </h1>
                    <p class="mt-4 text-sm sm:text-base text-neutral-600 leading-relaxed">
                        আপনার ইউনিয়ন পরিষদের ডিজিটালাইজেশন শুরু করতে ডেমো রিকোয়েস্ট পাঠান অথবা সরাসরি আমাদের হটলাইনে ফোন করুন।
                    </p>
                </div>
            </div>

            <!-- Result of a POST to /support_submit. Read once, above. -->
            <?php if ($saas_ok !== '' || $saas_err !== ''): ?>
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 mb-10">
                <div class="flex items-start gap-3 rounded-2xl border px-4 py-3.5 <?= $saas_err !== ''
                    ? 'border-rose-200 bg-rose-50'
                    : 'border-emerald-200 bg-emerald-50' ?>">
                    <?= icon($saas_err !== '' ? 'warning-circle' : 'check-circle', 20, 'bold',
                        'mt-0.5 shrink-0 ' . ($saas_err !== '' ? 'text-rose-600' : 'text-emerald-600')) ?>
                    <div>
                        <p class="text-sm font-bold <?= $saas_err !== '' ? 'text-rose-900' : 'text-emerald-900' ?>">
                            <?= $saas_err !== '' ? 'পাঠানো যায়নি' : 'ধন্যবাদ! বার্তা পাওয়া গেছে' ?>
                        </p>
                        <p class="mt-0.5 text-xs leading-relaxed <?= $saas_err !== '' ? 'text-rose-800' : 'text-emerald-800' ?>">
                            <?= htmlspecialchars($saas_err !== '' ? $saas_err : $saas_ok, ENT_QUOTES, 'UTF-8') ?>
                        </p>
                        <?php if ($saas_err !== ''): ?>
                        <p class="mt-1.5 text-[11px] text-rose-700">
                            নিচের ফর্মে আপনার লেখা সব থেকে গেছে — ঠিক করে আবার পাঠাতে পারেন।
                        </p>
                        <?php else: ?>
                        <p class="mt-1.5 text-[11px] text-emerald-700">
                            আমাদের টিম সাধারণত একই কর্মদিবসের মধ্যে উত্তর দেয়। জরুরি প্রয়োজনে উপরের যোগাযোগের মাধ্যমগুলো ব্যবহার করুন।
                        </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Contact Cards Grid -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-16">
                <?php if ($saas_no_channel): ?>
                <!-- Every channel was cleared in the platform panel. An empty
                     grid of four columns would look like a broken page; this
                     says what actually happened and where to fix it. -->
                <div class="ht-card flex items-start gap-3 border border-amber-200 bg-amber-50 p-6 text-sm text-amber-900">
                    <?= icon('warning-circle', 20, 'bold', 'mt-0.5 shrink-0 text-amber-700') ?>
                    <div>
                        <p class="font-bold">কোনো যোগাযোগের মাধ্যম সেট করা নেই</p>
                        <p class="mt-0.5 text-xs leading-relaxed text-amber-800">
                            প্ল্যাটফর্ম প্যানেলের <span class="font-mono font-bold">/sadmin_settings</span> পাতার
                            "যোগাযোগ ও হেল্পডেস্ক" ট্যাবে ফোন, হোয়াটসঅ্যাপ ও ইমেইল খালি করা হয়েছে, তাই এখানে দেখানোর মতো কোনো নম্বর নেই।
                            নিচের ফর্মটি তবুও কাজ করছে — লিখে জমা দিলে সেটি আমাদের হেল্পডেস্কে পৌঁছে যাবে।
                        </p>
                    </div>
                </div>
                <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                    <?php foreach ($saas_channels as $saas_ch): ?>
                    <div class="ht-card neu-glass-card p-6 flex items-start gap-4">
                        <div class="w-12 h-12 rounded-xl <?= $saas_ch['chip'] ?> flex items-center justify-center text-2xl shrink-0">
                            <?= icon($saas_ch['icon'], 24, $saas_ch['weight']) ?>
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-xs font-bold text-neutral-400 uppercase tracking-wider"><?= $saas_ch['label'] ?></h4>
                            <?php if ($saas_ch['kind'] === 'span'): ?>
                            <span class="text-sm font-bold text-neutral-900 mt-0.5 block" style="overflow-wrap: break-word;"><?= $saas_ch['value'] ?></span>
                            <?php elseif ($saas_ch['kind'] === 'ext'): ?>
                            <a href="<?= $saas_ch['href'] ?>" target="_blank" rel="noopener noreferrer" class="text-sm font-bold text-neutral-900 <?= $saas_ch['hover'] ?> transition-colors mt-0.5 block break-all"><?= $saas_ch['value'] ?></a>
                            <?php else: ?>
                            <a href="<?= $saas_ch['href'] ?>" class="text-sm font-bold text-neutral-900 <?= $saas_ch['hover'] ?> transition-colors mt-0.5 block break-all"><?= $saas_ch['value'] ?></a>
                            <?php endif; ?>
                            <p class="text-[11px] text-neutral-500 mt-0.5"><?= $saas_ch['note'] ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Booking Form Component, from resources/js/components/Contact.jsx -->
            <section id="contact" class="py-16">
                <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="ht-card neu-glass-card p-8 sm:p-10">

                        <div class="text-center max-w-xl mx-auto mb-8">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800 mb-2">
                                <?= icon('envelope', 16, 'bold') ?>
                                <span>সরাসরি যোগাযোগ</span>
                            </span>
                            <h2 class="text-2xl sm:text-3xl font-bold text-neutral-900">
                                আপনার ইউনিয়ন পরিষদের জন্য ডেমো বুক করুন
                            </h2>
                            <p class="text-xs sm:text-sm text-neutral-500 mt-1">
                                নিচের তথ্যগুলো পূরণ করে পাঠিয়ে দিন, আমাদের টিম অতি দ্রুত আপনার সাথে যোগাযোগ করবে।
                            </p>
                        </div>

                        <!--
                            POST /support_submit — Support::submit().

                            A plain form POST with no JavaScript, on purpose. The
                            endpoint also answers JSON to an XHR caller, and the
                            contact page does not need one: a form that works
                            without JS is the right choice for a page whose
                            visitors are on a phone in a district town on a slow
                            connection, and it means the endpoint's redirect
                            branch is the one actually exercised in production
                            rather than the branch that only a test hits.

                            The honeypot is the field Support::submit() reads as
                            'website'. It is moved off-screen with left:-9999px
                            rather than hidden with display:none or
                            visibility:hidden, because a bot that only fills
                            visible fields skips those, and a real person cannot
                            tab to it (tabindex="-1") or hear it
                            (aria-hidden). See the controller for what it does
                            with a non-empty value.
                        -->
                        <form method="post" action="<?= base_url('support_submit') ?>" class="space-y-4">
                            <input type="hidden" name="return_to" value="/contact">

                            <div aria-hidden="true" class="absolute top-0 h-0 w-0 overflow-hidden" style="left: -9999px;">
                                <label for="saas-website">ওয়েবসাইট (খালি রাখুন)</label>
                                <input type="text" id="saas-website" name="website" tabindex="-1" autocomplete="off" value="">
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="ht-label text-xs font-bold text-neutral-700" for="saas-union">ইউনিয়ন পরিষদের নাম *</label>
                                    <input type="text" id="saas-union" name="union_name" class="ht-field"
                                           placeholder="যেমন: আপনার ইউনিয়ন পরিষদ" required
                                           value="<?= $saas_v('union_name') ?>">
                                </div>
                                <div>
                                    <label class="ht-label text-xs font-bold text-neutral-700" for="saas-location">উপজেলা ও জেলা *</label>
                                    <input type="text" id="saas-location" name="location" class="ht-field"
                                           placeholder="যেমন: মিরসরাই, চট্টগ্রাম" required
                                           value="<?= $saas_v('location') ?>">
                                </div>
                                <div>
                                    <label class="ht-label text-xs font-bold text-neutral-700" for="saas-name">যোগাযোগকারীর নাম ও পদবী *</label>
                                    <input type="text" id="saas-name" name="name" class="ht-field"
                                           placeholder="চেয়ারম্যান / সচিব / উদ্যোক্তা" required
                                           value="<?= $saas_v('name') ?>">
                                </div>
                                <div>
                                    <label class="ht-label text-xs font-bold text-neutral-700" for="saas-phone">মোবাইল নম্বর *</label>
                                    <input type="tel" id="saas-phone" name="phone" class="ht-field"
                                           placeholder="যেমন: ০১৭১২-৩৪৫৬৭৮" required
                                           value="<?= $saas_v('phone') ?>">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="ht-label text-xs font-bold text-neutral-700" for="saas-email">
                                        ইমেইল ঠিকানা
                                        <span class="font-normal text-neutral-400">(ঐচ্ছিক — মোবাইলের সাথে একটা দিলে উত্তর পাওয়া সহজ হয়)</span>
                                    </label>
                                    <input type="email" id="saas-email" name="email" class="ht-field"
                                           placeholder="যেমন: chairman@example.com" autocomplete="email"
                                           value="<?= $saas_v('email') ?>">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="ht-label text-xs font-bold text-neutral-700" for="saas-plan">কোন প্যাকেজে আগ্রহী?</label>
                                    <select id="saas-plan" name="plan" class="ht-field">
                                        <?php foreach ($saas_plans as $saas_value => $saas_label): ?>
                                        <option value="<?= $saas_value ?>" <?= $saas_value === $saas_plan ? 'selected' : '' ?>><?= $saas_label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="ht-label text-xs font-bold text-neutral-700" for="saas-message">
                                        আপনার প্রশ্ন বা সমস্যা <span class="font-normal text-neutral-400">(ঐচ্ছিক)</span>
                                    </label>
                                    <textarea id="saas-message" name="message" rows="4" class="ht-field resize-y"
                                              placeholder="যে বিষয়ে জানতে চান বা কোন সমস্যায় পড়েছেন, সংক্ষেপে লিখুন।"><?= $saas_v('message') ?></textarea>
                                </div>
                            </div>

                            <div class="pt-2">
                                <button type="submit" class="ht-btn ht-btn-primary w-full justify-center py-3 shadow-md">
                                    <?= icon('paper-plane-tilt', 18, 'bold') ?>
                                    <span>ডেমো রিকোয়েস্ট জমা দিন</span>
                                </button>
                                <p class="mt-2.5 text-center text-[11px] leading-relaxed text-neutral-500">
                                    জমা দিলে এই তথ্যগুলো শুধু আমাদের হেল্পডেস্কে পৌঁছে যাবে। বাধ্যতামূলক কোনো কিছু নেই, এবং এটি কোনো চুক্তা বা অর্ডার নয় —
                                    প্যাকেজ ক্রয়ের জন্য আলাদাভাবে যোগাযোগ করতে হবে।
                                </p>
                            </div>
                        </form>

                    </div>
                </div>
            </section>

        </main>
