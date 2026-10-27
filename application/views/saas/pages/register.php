<?php
/**
 * Product-site registration form.
 *
 * Ported from resources/js/pages/RegisterPage.jsx (819 lines). That file held
 * the form and the success screen in one component, branching on a
 * registeredData state variable; the two are now two views, and Front::register()
 * picks between them. See saas/pages/register_done.php.
 *
 * The form is a real <form method="post"> with the field names
 * Front::create_tenant() reads. Four pieces of React state had to go somewhere,
 * and each went to the mechanism that actually fits rather than to new
 * JavaScript:
 *
 *   selectedPlan  → a radio group named `package`. It has to be submitted
 *                   with the form, so a link or a query param would not do.
 *                   ?plan= still pre-selects, from /plans.
 *   billingCycle  → ?cycle=yearly|monthly, matching the ?cycle= convention on
 *                   the /plans page. It only changes which price is printed.
 *   useCustomDomain → a real checkbox. The field it reveals is always rendered;
 *                   the server blanks custom_domain when the box is unticked.
 *                   saas.js hides and shows it for parity with the original.
 *   formData      → redisplayed from $saas_old after a server-side error, so a
 *                   validation failure no longer costs the visitor every field.
 *                   The password fields are never redisplayed.
 *
 * The subdomain sanitiser (handleSubdomainChange, which forced [a-z0-9-]) is not
 * reimplemented in JS: create_tenant() already applies the same filter server
 * side, so the field is now normalised in exactly one place.
 *
 * The live-preview card updates from $saas_old, so it shows real values after a
 * failed submit and the placeholder values on a first visit. React updated it
 * on every keystroke; that would need JavaScript and is the one behaviour here
 * that is gone.
 *
 * @var string      $saas_plan   Selected package: standard|premium|cluster.
 * @var string|null $saas_error  Error message from create_tenant(), or null.
 * @var array       $saas_old    Previously submitted values, or empty.
 */
defined('BASEPATH') OR exit('No direct script access allowed');

$saas_old = $saas_old ?? array();

/**
 * Read a previously submitted value, or a fallback.
 *
 * $field is a plain name, never user input, so $array[$field] is safe here.
 */
$saas_val = function ($field, $fallback = '') use ($saas_old) {
    return isset($saas_old[$field]) && $saas_old[$field] !== '' ? $saas_old[$field] : $fallback;
};

$saas_pkg_rows = array();
if (!empty($saas_packages) && is_array($saas_packages) && is_object(reset($saas_packages))) {
    foreach ($saas_packages as $p) {
        $feat_list = array();
        if (!empty($p->features)) {
            $raw_feats = preg_split('/[\r\n|]+/', $p->features);
            foreach ($raw_feats as $rf) {
                $rf = trim($rf);
                if ($rf !== '') { $feat_list[] = $rf; }
            }
        }
        $saas_pkg_rows[] = array(
            'key'          => $p->package_key,
            'name'         => $p->name_bn,
            'priceYearly'  => $p->price_yearly > 0 ? bn_number(number_format($p->price_yearly)) : 'কাস্টম',
            'priceMonthly' => $p->price_monthly > 0 ? bn_number(number_format($p->price_monthly)) : 'কাস্টম',
            'tag'          => ($p->package_key === 'premium') ? 'জনপ্রিয়' : (($p->package_key === 'cluster') ? 'উপজেলা' : 'বেসিক'),
            'popular'      => ($p->package_key === 'premium'),
            'highlight'    => $p->max_holdings ? (bn_number($p->max_holdings) . ' হোল্ডিং সীমা') : 'আনলিমিটেড হোল্ডিং',
            'features'     => $feat_list,
        );
    }
} else if (!empty($saas_packages) && is_array($saas_packages)) {
    $saas_pkg_rows = $saas_packages;
} else {
    $saas_pkg_rows = array(
        array(
            'key'          => 'standard',
            'name'         => 'স্ট্যান্ডার্ড',
            'priceYearly'  => '২৫,০০০',
            'priceMonthly' => '২,৪০০',
            'tag'          => 'বেসিক',
            'highlight'    => 'মাঝারি ইউনিয়নের জন্য',
            'features'     => array('হোল্ডিং ট্যাক্স ও কর আদায়', '৩ জন অপারেটর আইডি', 'সাবডোমেন হোস্টিং', 'ইমেইল সাপোর্ট'),
        ),
        array(
            'key'          => 'premium',
            'name'         => 'প্রিমিয়াম',
            'priceYearly'  => '৪০,০০০',
            'priceMonthly' => '৩,৮০০',
            'tag'          => 'জনপ্রিয়',
            'popular'      => true,
            'highlight'    => 'সবচেয়ে বেশি ব্যবহৃত',
            'features'     => array('ডিজিটাল সনদ ও কিউআর কোড', 'আনলিমিটেড ইউজার অ্যাকাউন্ট', 'কাস্টম ডোমেন (.gov.bd)', 'বাল্ক এসএমএস গেটওয়ে', 'অনসাইট স্টাফ ট্রেনিং'),
        ),
        array(
            'key'          => 'cluster',
            'name'         => 'ক্লাস্টার',
            'priceYearly'  => '১,২০,০০০',
            'priceMonthly' => '১১,০০০',
            'tag'          => 'উপজেলা',
            'highlight'    => 'মাল্টি-ইউনিয়ন ক্লাস্টার',
            'features'     => array('সম্পূর্ণ উপজেলা সেন্ট্রাল ড্যাশবোর্ড', 'UNO মনিটরিং পোর্টাল', 'ডেডিকেটেড ক্লাউড ব্যাকআপ', 'ভিআইপি ২৪/৭ ডেডিকেটেড টিম'),
        ),
    );
}
$saas_packages = $saas_pkg_rows;

// The card whose details the live preview and the confirmation screen quote.
$saas_current = $saas_packages[0];
foreach ($saas_packages as $saas_pkg) {
    if ($saas_pkg['key'] === $saas_plan) { $saas_current = $saas_pkg; }
}

$saas_cycle = ($this->input->get('cycle') === 'monthly') ? 'monthly' : 'yearly';
$saas_use_cd = ! empty($saas_old['custom_domain']);
?>
        <main class="grow">
        <div class="py-12 bg-neutral-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <!-- Header -->
                <div class="text-center max-w-3xl mx-auto mb-10">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800 mb-3">
                        <?= icon('sparkle', 15, 'fill') ?>
                        <span>স্বয়ংক্রিয় ক্লাউড অনবোর্ডিং</span>
                    </span>
                    <h1 class="text-3xl sm:text-4xl font-black text-neutral-900 tracking-tight">
                        প্যাকেজ নির্বাচন ও ইউনিয়ন পরিষদ নিবন্ধন
                    </h1>
                    <p class="mt-3 text-sm sm:text-base text-neutral-600 leading-relaxed">
                        আপনার পছন্দের প্যাকেজ নির্বাচন করুন এবং ইউনিয়ন পরিষদের প্রাথমিক তথ্য দিয়ে মাত্র ২ মিনিটে নিজস্ব ক্লাউড পোর্টাল চালু করুন।
                    </p>
                </div>

                <?php /* A real form, not a div. The package radios and every input
                         below carry the names create_tenant() reads. */ ?>
                <form method="post" action="<?= site_url('register') ?>" id="htRegisterForm">
                <input type="hidden" name="cycle" id="reg-cycle-input" value="<?= $saas_cycle ?>">

                <!-- Step 1: Package Selector Cards -->
                <div class="mb-12">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-indigo-700 text-white flex items-center justify-center text-xs font-bold">1</span>
                            <h2 class="text-lg font-bold text-neutral-900">প্যাকেজ নির্বাচন করুন</h2>
                        </div>

                        <!-- Cycle toggle (Pure Dynamic Client Switch) -->
                        <div class="inline-flex p-1.5 neu-glass-inset rounded-2xl text-xs gap-1">
                            <button type="button" id="btn-cycle-yearly"
                                    class="px-3.5 py-1.5 rounded-xl font-bold transition-all cursor-pointer <?= $saas_cycle === 'yearly' ? 'bg-indigo-600 text-white shadow-md' : 'text-neutral-600 hover:text-indigo-600' ?>">
                                বাৎসরিক বিলিং
                            </button>
                            <button type="button" id="btn-cycle-monthly"
                                    class="px-3.5 py-1.5 rounded-xl font-bold transition-all cursor-pointer <?= $saas_cycle === 'monthly' ? 'bg-indigo-600 text-white shadow-md' : 'text-neutral-600 hover:text-indigo-600' ?>">
                                মাসিক
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <?php foreach ($saas_packages as $saas_pkg):
                            $saas_is_sel = ($saas_pkg['key'] === $saas_plan);
                            ?>
                            <label class="saas-plan-card p-6 rounded-2xl border-2 transition-all bg-white relative flex flex-col justify-between cursor-pointer select-none <?= $saas_is_sel ? 'border-indigo-700 shadow-md ring-2 ring-indigo-700/20' : 'border-neutral-200 hover:border-indigo-300' ?>"
                                   data-saas-card="<?= $saas_pkg['key'] ?>">

                                <?php if ($saas_pkg['popular']): ?>
                                    <span class="absolute -top-3 right-4 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-500 text-neutral-950 uppercase tracking-wider">
                                        বেস্ট চয়েস
                                    </span>
                                <?php endif; ?>

                                <input type="radio" name="package" value="<?= $saas_pkg['key'] ?>"
                                       data-saas-plan-radio="<?= $saas_pkg['key'] ?>"
                                       data-saas-plan-name="<?= htmlspecialchars($saas_pkg['name'], ENT_QUOTES, 'UTF-8') ?>"
                                       data-price-yearly="<?= $saas_pkg['priceYearly'] ?>"
                                       data-price-monthly="<?= $saas_pkg['priceMonthly'] ?>"
                                       data-saas-plan-card
                                       <?= $saas_is_sel ? 'checked' : '' ?>
                                       class="sr-only">

                                <div class="block">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-bold uppercase tracking-wider text-neutral-500"><?= $saas_pkg['tag'] ?></span>
                                        <span class="w-5 h-5 rounded-full border-2 flex items-center justify-center <?= $saas_is_sel ? 'border-indigo-700 bg-indigo-700 text-white' : 'border-neutral-300' ?>"
                                              data-saas-plan-dot="<?= $saas_pkg['key'] ?>">
                                            <?php if ($saas_is_sel): ?><?= icon('check', 12, 'bold') ?><?php endif; ?>
                                        </span>
                                    </div>

                                    <span class="block text-xl font-black text-neutral-900 mt-1"><?= $saas_pkg['name'] ?></span>
                                    <span class="block text-xs text-neutral-500 mt-0.5"><?= $saas_pkg['highlight'] ?></span>

                                    <div class="mt-4 pb-4 border-b border-neutral-100 block">
                                        <span class="text-3xl font-black text-neutral-900">
                                            ৳<span data-card-price="<?= $saas_pkg['key'] ?>"><?= $saas_cycle === 'yearly' ? $saas_pkg['priceYearly'] : $saas_pkg['priceMonthly'] ?></span>
                                        </span>
                                        <span class="text-xs text-neutral-500" data-card-cycle-text>
                                            <?= $saas_cycle === 'yearly' ? ' / বাৎসরিক' : ' / প্রতি মাসে' ?>
                                        </span>
                                    </div>

                                    <div class="mt-4 space-y-2 text-xs text-neutral-600 block">
                                        <?php foreach ($saas_pkg['features'] as $saas_feat): ?>
                                            <div class="flex items-start gap-2">
                                                <?= icon('check-circle', 14, 'bold', 'text-emerald-600 shrink-0 mt-0.5') ?>
                                                <span><?= $saas_feat ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <div class="mt-6 pt-3 border-t border-neutral-100">
                                    <span class="block text-center py-2 rounded-xl text-xs font-bold transition-colors <?= $saas_is_sel ? 'bg-indigo-700 text-white' : 'bg-neutral-100 text-neutral-700' ?>"
                                          data-saas-plan-label="<?= $saas_pkg['key'] ?>">
                                        <?= $saas_is_sel ? 'নির্বাচিত হয়েছে' : 'প্যাকেজ বাছাই করুন' ?>
                                    </span>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Step 2 & 3: Details & Live Preview Layout -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                    <!-- Left Form (8 Cols) -->
                    <div class="lg:col-span-8 ht-card neu-glass-card p-6 sm:p-8">
                        <div class="flex items-center gap-2 mb-6">
                            <span class="w-6 h-6 rounded-full bg-indigo-700 text-white flex items-center justify-center text-xs font-bold">2</span>
                            <h2 class="text-lg font-bold text-neutral-900">ইউনিয়ন পরিষদ ও অ্যাডমিন তথ্য</h2>
                        </div>

                        <?php if (!empty($saas_error)): ?>
                            <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm flex items-start gap-3">
                                <?= icon('warning-circle', 20, 'bold', 'shrink-0 mt-0.5 text-red-600') ?>
                                <div>
                                    <strong>ত্রুটি:</strong> <?= $saas_error ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="space-y-6">

                            <!-- Section: Union Parishad Information -->
                            <div>
                                <h3 class="text-xs font-bold uppercase tracking-wider text-neutral-500 mb-3 flex items-center gap-1.5">
                                    <?= icon('buildings', 16, 'fill', 'text-indigo-600') ?>
                                    <span>ইউনিয়ন পরিষদের মূল তথ্য</span>
                                </h3>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label for="reg-union-name" class="block text-xs font-bold text-neutral-700 mb-1">
                                            ইউনিয়ন পরিষদের নাম (বাংলায়) <span class="text-red-500">*</span>
                                        </label>
                                        <input id="reg-union-name" name="union_name" type="text" required
                                               value="<?= html_escape($saas_val('union_name'), ENT_QUOTES, 'UTF-8') ?>"
                                               placeholder="যেমন: ৭নং কাঞ্চন ইউনিয়ন পরিষদ"
                                               class="ht-field w-full px-3.5 py-2.5 rounded-xl text-sm focus:border-indigo-600 focus:outline-none">
                                    </div>

                                    <div>
                                        <label for="reg-union-name-en" class="block text-xs font-bold text-neutral-700 mb-1">
                                            Union Name (English)
                                        </label>
                                        <input id="reg-union-name-en" name="union_name_en" type="text"
                                               value="<?= html_escape($saas_val('union_name_en'), ENT_QUOTES, 'UTF-8') ?>"
                                               placeholder="e.g. 7 No. Kanchan Union Parishad"
                                               class="ht-field w-full px-3.5 py-2.5 rounded-xl text-sm focus:border-indigo-600 focus:outline-none">
                                    </div>

                                    <!-- Domain Configuration -->
                                    <div class="sm:col-span-2 p-4 rounded-2xl bg-neutral-50 border border-neutral-200 space-y-3">
                                        <div>
                                            <div class="flex items-center justify-between mb-1">
                                                <label for="reg-subdomain" class="block text-xs font-bold text-neutral-800">
                                                    ক্লাউড সাবডোমেন প্রিফিক্স <span class="text-red-500">*</span>
                                                </label>
                                                <span class="text-[10px] font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded">
                                                    ইনস্ট্যান্ট ক্লাউড এক্সেস
                                                </span>
                                            </div>
                                            <div class="flex items-center rounded-xl border border-neutral-300 focus-within:border-indigo-600 overflow-hidden bg-white shadow-xs">
                                                <span class="px-3 py-2.5 bg-neutral-100 text-neutral-500 text-xs font-mono border-r border-neutral-200">
                                                    https://
                                                </span>
                                                <input id="reg-subdomain" name="subdomain" type="text" required
                                                       value="<?= html_escape($saas_val('subdomain'), ENT_QUOTES, 'UTF-8') ?>"
                                                       placeholder="kanchan"
                                                       class="grow px-3 py-2.5 text-sm font-mono text-neutral-900 focus:outline-none">
                                                <span class="px-3 py-2.5 bg-neutral-100 text-neutral-600 text-xs font-mono border-l border-neutral-200">
                                                    .viretaup.com
                                                </span>
                                            </div>
                                            <p class="text-[11px] text-neutral-500 mt-1">
                                                শুধুমাত্র ইংরেজি ছোট হাতের অক্ষর ও সংখ্যা (যেমন: kanchan, rupganj-up)
                                            </p>
                                        </div>

                                        <!-- Custom Domain Toggle -->
                                        <div class="pt-3 border-t border-neutral-200">
                                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                                <input type="checkbox" name="use_custom_domain" value="1"
                                                       data-saas-toggle="custom-domain"
                                                       <?= $saas_use_cd ? 'checked' : '' ?>
                                                       class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-neutral-300 cursor-pointer">
                                                <span class="text-xs font-bold text-neutral-900">
                                                    আমাদের ইউনিয়ন পরিষদের নিজস্ব ডোমেন আছে (যেমন: <code class="text-indigo-700 font-mono">kanchanup.gov.bd</code>)
                                                </span>
                                            </label>

                                            <?php /* Rendered unconditionally. React hid this
                                                     block behind the checkbox, which needs
                                                     JavaScript to reveal. Server-side the
                                                     checkbox decides: Front::register() blanks
                                                     custom_domain when the box is unticked, so
                                                     an always-visible optional field behaves
                                                     identically with or without JS. saas.js
                                                     still collapses it for parity. */ ?>
                                            <div data-saas-panel="custom-domain"
                                                 class="mt-3 space-y-2 <?= $saas_use_cd ? '' : 'hidden' ?>">
                                                <label for="reg-custom-domain" class="block text-xs font-bold text-neutral-700">
                                                    কাস্টম সরকারি ডোমেন নাম (FQDN)
                                                </label>
                                                <div class="flex items-center rounded-xl border border-neutral-300 focus-within:border-indigo-600 overflow-hidden bg-white shadow-xs">
                                                    <span class="px-3 py-2.5 bg-neutral-100 text-neutral-500 text-xs font-mono border-r border-neutral-200">
                                                        https://
                                                    </span>
                                                    <input id="reg-custom-domain" name="custom_domain" type="text"
                                                           value="<?= html_escape($saas_val('custom_domain'), ENT_QUOTES, 'UTF-8') ?>"
                                                           placeholder="dhumup.gov.bd বা kanchanup.com"
                                                           class="grow px-3 py-2.5 text-sm font-mono text-neutral-900 focus:outline-none">
                                                </div>

                                                <!-- DNS Instructions Callout -->
                                                <div class="p-3 bg-white rounded-xl border border-neutral-200 text-[11px] text-neutral-600 space-y-1">
                                                    <div class="font-bold text-neutral-900 flex items-center gap-1.5">
                                                        <?= icon('globe', 14, 'fill', 'text-indigo-600') ?>
                                                        <span>DNS কনফিগারেশন নির্দেশিকা:</span>
                                                    </div>
                                                    <p>আপনার ডোমেন কন্ট্রোল প্যানেলে (DNS Manager) গিয়ে একটি CNAME রেকর্ড যোগ করুন:</p>
                                                    <div class="bg-neutral-100 p-2 rounded font-mono text-[10px] text-neutral-900 border border-neutral-200 flex flex-wrap justify-between gap-1">
                                                        <span><strong>Host:</strong> @ অথবা www</span>
                                                        <span><strong>Type:</strong> CNAME</span>
                                                        <span><strong>Target:</strong> cname.viretaup.com</span>
                                                    </div>
                                                    <p class="text-[10px] text-neutral-500">
                                                        * DNS আপডেট হতে সাধারণত কিছুক্ষণ সময় লাগে। তবে আপনার সাবডোমেন দিয়ে সাথে সাথেই পোর্টাল চলবে।
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="sm:col-span-2">
                                        <label for="reg-location" class="block text-xs font-bold text-neutral-700 mb-1">
                                            উপজেলা ও জেলা / ঠিকানা
                                        </label>
                                        <div class="relative">
                                            <?= icon('map-pin', 18, 'fill', 'absolute left-3.5 top-3 text-neutral-400') ?>
                                            <input id="reg-location" name="location" type="text"
                                                   value="<?= html_escape($saas_val('location'), ENT_QUOTES, 'UTF-8') ?>"
                                                   placeholder="যেমন: রূপগঞ্জ, নারায়ণগঞ্জ"
                                                   class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-neutral-300 text-sm focus:border-indigo-600 focus:outline-none">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section: Admin Account Credentials -->
                            <div class="pt-4 border-t border-neutral-200">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-neutral-500 mb-3 flex items-center gap-1.5">
                                    <?= icon('user', 16, 'fill', 'text-indigo-600') ?>
                                    <span>অফিশিয়াল অ্যাডমিনিস্ট্রেটর অ্যাকাউন্ট</span>
                                </h3>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label for="reg-admin-name" class="block text-xs font-bold text-neutral-700 mb-1">
                                            দায়িত্বপ্রাপ্ত কর্মকর্তার নাম
                                        </label>
                                        <input id="reg-admin-name" name="admin_name" type="text"
                                               value="<?= html_escape($saas_val('admin_name'), ENT_QUOTES, 'UTF-8') ?>"
                                               placeholder="চেয়ারম্যান / সচিবের নাম"
                                               class="w-full px-3.5 py-2.5 rounded-xl border border-neutral-300 text-sm focus:border-indigo-600 focus:outline-none">
                                    </div>

                                    <div>
                                        <label for="reg-admin-phone" class="block text-xs font-bold text-neutral-700 mb-1">
                                            দাপ্তরিক মোবাইল নম্বর
                                        </label>
                                        <div class="relative">
                                            <?= icon('phone', 18, 'fill', 'absolute left-3.5 top-3 text-neutral-400') ?>
                                            <input id="reg-admin-phone" name="admin_phone" type="text"
                                                   value="<?= html_escape($saas_val('admin_phone'), ENT_QUOTES, 'UTF-8') ?>"
                                                   placeholder="017XXXXXXXX"
                                                   class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-neutral-300 text-sm focus:border-indigo-600 focus:outline-none">
                                        </div>
                                    </div>

                                    <div class="sm:col-span-2">
                                        <label for="reg-admin-email" class="block text-xs font-bold text-neutral-700 mb-1">
                                            অফিশিয়াল ইমেইল (লগইন ইউজারনেম) <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <?= icon('envelope-simple', 18, 'fill', 'absolute left-3.5 top-3 text-neutral-400') ?>
                                            <input id="reg-admin-email" name="admin_email" type="email" required
                                                   value="<?= html_escape($saas_val('admin_email'), ENT_QUOTES, 'UTF-8') ?>"
                                                   placeholder="up.kanchan@gmail.com বা chairman@kanchan.gov.bd"
                                                   class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-neutral-300 text-sm focus:border-indigo-600 focus:outline-none">
                                        </div>
                                    </div>

                                    <div>
                                        <label for="reg-admin-password" class="block text-xs font-bold text-neutral-700 mb-1">
                                            পাসওয়ার্ড নির্ধারণ করুন <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <?= icon('lock-key', 18, 'fill', 'absolute left-3.5 top-3 text-neutral-400') ?>
                                            <input id="reg-admin-password" name="admin_password" type="password" required
                                                   data-saas-password
                                                   placeholder="কমপক্ষে ৬ অক্ষর"
                                                   class="w-full pl-10 pr-10 py-2 rounded-xl border border-neutral-300 text-sm focus:border-indigo-600 focus:outline-none">
                                            <button type="button" data-saas-toggle-password
                                                    class="absolute top-2.5 right-3.5 cursor-pointer text-neutral-400 hover:text-neutral-600"
                                                    aria-label="পাসওয়ার্ড দেখান">
                                                <span data-saas-eye="show"><?= icon('eye', 18) ?></span>
                                                <span data-saas-eye="hide" class="hidden"><?= icon('eye-slash', 18) ?></span>
                                            </button>
                                        </div>
                                    </div>

                                    <div>
                                        <label for="reg-admin-password-2" class="block text-xs font-bold text-neutral-700 mb-1">
                                            পাসওয়ার্ড নিশ্চিত করুন <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <?= icon('lock-key', 18, 'fill', 'absolute left-3.5 top-3 text-neutral-400') ?>
                                            <input id="reg-admin-password-2" name="admin_password_confirm" type="password" required
                                                   data-saas-password
                                                   placeholder="একই পাসওয়ার্ড পুনরায় লিখুন"
                                                   class="w-full pl-10 pr-10 py-2 rounded-xl border border-neutral-300 text-sm focus:border-indigo-600 focus:outline-none">
                                            <button type="button" data-saas-toggle-password
                                                    class="absolute top-2.5 right-3.5 cursor-pointer text-neutral-400 hover:text-neutral-600"
                                                    aria-label="পাসওয়ার্ড দেখান">
                                                <span data-saas-eye="show"><?= icon('eye', 18) ?></span>
                                                <span data-saas-eye="hide" class="hidden"><?= icon('eye-slash', 18) ?></span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="pt-4 border-t border-neutral-200 flex flex-col sm:flex-row items-center justify-between gap-4">
                                <div class="text-xs text-neutral-500">
                                    নিবন্ধনের সাথে সাথেই আপনার ইউনিয়ন পোর্টাল সক্রিয় করা হবে।
                                </div>

                                <?php /* No spinner state: there is no fetch, so there is
                                         nothing to wait for. The button is a real
                                         submit and the response is a real navigation. */ ?>
                                <button type="submit"
                                        class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-indigo-700 hover:bg-indigo-800 active:scale-98 text-white font-bold text-sm shadow-sm flex items-center justify-center gap-2 cursor-pointer transition-all disabled:opacity-60">
                                    <span>রেজিস্ট্রেশন ও পোর্টাল অ্যাক্টিভেট করুন</span>
                                    <?= icon('arrow-right', 18, 'bold') ?>
                                </button>
                            </div>

                        </div>
                    </div>

                    <!-- Right Sidebar: Live Preview & Tenant Customization Highlights (4 Cols) -->
                    <div class="lg:col-span-4 space-y-6">

                        <!-- Live Preview Card -->
                        <div class="ht-card neu-glass-card p-6">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-200">
                                লাইভ প্রিভিউ (Live Preview)
                            </span>

                            <div class="mt-4 p-4 rounded-2xl neu-dark-glass text-white">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-indigo-600 flex items-center justify-center font-bold text-white text-base">
                                        V
                                    </div>
                                    <div>
                                        <h4 id="preview-union-name" class="text-sm font-bold truncate">
                                            <?= $saas_val('union_name', 'আপনার ইউনিয়ন পরিষদ') ?>
                                        </h4>
                                        <p id="preview-domain" class="text-[10px] text-neutral-400 font-mono truncate">
                                            <?php /* React composed this string inline from three
                                                     fields. The precedence is preserved: a custom
                                                     domain wins over the subdomain, and the
                                                     subdomain wins over the placeholder. */ ?>
                                            <?php if ($saas_use_cd && $saas_val('custom_domain') !== ''): ?>
                                                https://<?= $saas_val('custom_domain') ?>
                                            <?php elseif ($saas_val('subdomain') !== ''): ?>
                                                <?= $saas_val('subdomain') ?>.viretaup.com
                                            <?php else: ?>
                                                union-slug.viretaup.com
                                            <?php endif; ?>
                                        </p>
                                        <?php if ($saas_use_cd && $saas_val('custom_domain') !== ''): ?>
                                            <span id="preview-cd-badge" class="inline-block mt-1 px-1.5 py-0.2 rounded text-[9px] font-bold bg-amber-400 text-neutral-950">
                                                কাস্টম ডোমেন (.gov.bd)
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="mt-4 pt-3 border-t border-neutral-800 flex items-center justify-between text-xs">
                                    <span class="text-neutral-400">প্যাকেজ</span>
                                    <span id="preview-plan-name" class="font-bold text-amber-400"><?= $saas_current['name'] ?> প্যাকেজ</span>
                                </div>
                                <div class="mt-1 flex items-center justify-between text-xs">
                                    <span class="text-neutral-400">বিলিং মূল্য</span>
                                    <span id="preview-plan-price" class="font-bold text-neutral-200">
                                        ৳<?= $saas_cycle === 'yearly' ? $saas_current['priceYearly'] : $saas_current['priceMonthly'] ?>
                                        <span class="text-[10px] text-neutral-400 font-normal"><?= $saas_cycle === 'yearly' ? ' / বাৎসরিক' : ' / প্রতি মাসে' ?></span>
                                    </span>
                                </div>
                                <div class="mt-1 flex items-center justify-between text-xs">
                                    <span class="text-neutral-400">লোকেশন</span>
                                    <span id="preview-location" class="text-neutral-200 truncate"><?= $saas_val('location', 'ঠিকানা') ?></span>
                                </div>
                            </div>

                            <!-- Per-Tenant Customization List -->
                            <div class="mt-6">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-neutral-700 mb-3 flex items-center gap-1.5">
                                    <?= icon('sliders-horizontal', 16, 'fill', 'text-indigo-600') ?>
                                    <span>প্রতিটি ইউনিয়নের নিজস্ব কাস্টমাইজেশন</span>
                                </h4>

                                <ul class="space-y-3 text-xs text-neutral-600">
                                    <li class="flex items-start gap-2.5">
                                        <?= icon('identification-card', 18, 'fill', 'text-indigo-600 shrink-0 mt-0.5') ?>
                                        <div>
                                            <strong class="text-neutral-900">চেয়ারম্যান ও পরিষদ প্রোফাইল:</strong> চেয়ারম্যানের ছবি, বাণী, মেম্বার ও কর্মকর্তা তালিকা সম্পূর্ণ কাস্টমাইজড।
                                        </div>
                                    </li>
                                    <li class="flex items-start gap-2.5">
                                        <?= icon('image', 18, 'fill', 'text-indigo-600 shrink-0 mt-0.5') ?>
                                        <div>
                                            <strong class="text-neutral-900">লোগো ও ব্যানার স্লাইডার:</strong> প্রতিটি ইউনিয়নের নিজস্ব সিলমোহর লোগো এবং প্রধান পৃষ্ঠার ছবি আপলোড।
                                        </div>
                                    </li>
                                    <li class="flex items-start gap-2.5">
                                        <?= icon('globe', 18, 'fill', 'text-indigo-600 shrink-0 mt-0.5') ?>
                                        <div>
                                            <strong class="text-neutral-900">কাস্টম ডোমেন বাইন্ডিং:</strong> সাবডোমেন ছাড়াও সরাসরি সরকারি ডোমেন (`.gov.bd`) সংযুক্ত করার সুবিধা।
                                        </div>
                                    </li>
                                    <li class="flex items-start gap-2.5">
                                        <?= icon('shield-check', 18, 'fill', 'text-emerald-600 shrink-0 mt-0.5') ?>
                                        <div>
                                            <strong class="text-neutral-900">আইসোলেটেড ডাটাবেজ সিকিউরিটি:</strong> এক ইউনিয়নের ডাটা বা কর রাজস্ব অন্য ইউনিয়ন কখনোই দেখতে পারবে না।
                                        </div>
                                    </li>
                                </ul>
                            </div>

                            <div class="mt-6 pt-4 border-t border-neutral-100 text-center">
                                <p class="text-xs text-neutral-500">
                                    ইতিমধ্যে একটি অ্যাকাউন্ট আছে?
                                    <a href="<?= site_url('login') ?>" class="font-bold text-indigo-700 hover:underline">লগইন করুন</a>
                                </p>
                            </div>
                        </div>

                        <!-- Need Help Card -->
                        <div class="bg-neutral-100 border border-neutral-200 rounded-3xl p-5 text-center">
                            <h4 class="text-xs font-bold text-neutral-900 mb-1">অ্যাক্টিভেশনে সহায়তা চান?</h4>
                            <p class="text-xs text-neutral-600 mb-3">আমাদের সাপোর্ট টিম সরাসরি আপনার ইউনিয়ন অফিসে গিয়ে অনবোর্ডিং করিয়ে দেয়।</p>
                            <button type="button"
                                    data-toggle="modal" data-target="#supportModal"
                                    class="w-full py-2 rounded-xl bg-white border border-neutral-300 text-xs font-bold text-neutral-800 hover:bg-neutral-50 cursor-pointer transition-colors">
                                ২৪/৭ হেল্পডেস্কে যোগাযোগ
                            </button>
                        </div>

                    </div>

                </div>

                </form>

            </div>
        </div>
        </main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const cycleInput = document.getElementById('reg-cycle-input');
    const previewPlanName = document.getElementById('preview-plan-name');
    const previewPlanPrice = document.getElementById('preview-plan-price');
    const previewUnionName = document.getElementById('preview-union-name');
    const previewDomain = document.getElementById('preview-domain');
    const previewLocation = document.getElementById('preview-location');

    const inputUnion = document.getElementById('reg-union-name');
    const inputSubdomain = document.getElementById('reg-subdomain');
    const inputCustomDomain = document.getElementById('reg-custom-domain');
    const checkCustomDomain = document.getElementById('reg-use-custom-domain');
    const inputLocation = document.getElementById('reg-location');

    // Live update Union Name
    if (inputUnion && previewUnionName) {
        inputUnion.addEventListener('input', function () {
            previewUnionName.textContent = this.value.trim() || 'আপনার ইউনিয়ন পরিষদ';
        });
    }

    // Live update Domain preview
    function updateDomainPreview() {
        if (!previewDomain) return;
        const useCustom = checkCustomDomain && checkCustomDomain.checked;
        const cd = inputCustomDomain ? inputCustomDomain.value.trim() : '';
        const sub = inputSubdomain ? inputSubdomain.value.trim() : '';
        if (useCustom && cd) {
            previewDomain.textContent = 'https://' + cd;
        } else if (sub) {
            previewDomain.textContent = sub + '.viretaup.com';
        } else {
            previewDomain.textContent = 'union-slug.viretaup.com';
        }
    }
    if (inputSubdomain) inputSubdomain.addEventListener('input', updateDomainPreview);
    if (inputCustomDomain) inputCustomDomain.addEventListener('input', updateDomainPreview);
    if (checkCustomDomain) checkCustomDomain.addEventListener('change', updateDomainPreview);

    // Live update Location
    if (inputLocation && previewLocation) {
        inputLocation.addEventListener('input', function () {
            previewLocation.textContent = this.value.trim() || 'ঠিকানা';
        });
    }

    // Live update Plan, Price, Dot Checkmarks, and Card Styling
    const planRadios = document.querySelectorAll('input[name="package"]');
    function updatePlanPreview() {
        const checkedRadio = document.querySelector('input[name="package"]:checked');
        if (!checkedRadio) return;
        const pName = checkedRadio.getAttribute('data-saas-plan-name') || checkedRadio.value;
        const pYearly = checkedRadio.getAttribute('data-price-yearly');
        const pMonthly = checkedRadio.getAttribute('data-price-monthly');
        const cycle = cycleInput ? cycleInput.value : 'yearly';
        const isYearly = (cycle === 'yearly');
        const pVal = isYearly ? pYearly : pMonthly;

        if (previewPlanName) {
            previewPlanName.textContent = pName + ' প্যাকেজ';
        }
        if (previewPlanPrice) {
            previewPlanPrice.innerHTML = (pVal === 'কাস্টম' ? 'কাস্টম' : '৳' + pVal) +
                '<span class="text-[10px] text-neutral-400 font-normal"> ' + (isYearly ? ' / বাৎসরিক' : ' / প্রতি মাসে') + '</span>';
        }

        // Sync checkmark, dot, button text and borders for all cards
        planRadios.forEach(function (radio) {
            const k = radio.getAttribute('data-saas-plan-radio') || radio.value;
            const on = radio.checked;
            const dot = document.querySelector('[data-saas-plan-dot="' + k + '"]');
            const label = document.querySelector('[data-saas-plan-label="' + k + '"]');
            const card = radio.closest('.saas-plan-card') || radio.closest('.border-2');

            if (dot) {
                dot.className = on
                    ? 'w-5 h-5 rounded-full border-2 flex items-center justify-center border-indigo-700 bg-indigo-700 text-white'
                    : 'w-5 h-5 rounded-full border-2 flex items-center justify-center border-neutral-300';
                dot.innerHTML = on ? '<i class="ph-bold ph-check text-[11px]" style="font-size:11px;line-height:1;"></i>' : '';
            }
            if (label) {
                label.className = on
                    ? 'block text-center py-2 rounded-xl text-xs font-bold transition-colors bg-indigo-700 text-white'
                    : 'block text-center py-2 rounded-xl text-xs font-bold transition-colors bg-neutral-100 text-neutral-700';
                label.textContent = on ? 'নির্বাচিত হয়েছে' : 'প্যাকেজ বাছাই করুন';
            }
            if (card) {
                card.classList.toggle('border-indigo-700', on);
                card.classList.toggle('shadow-md', on);
                card.classList.toggle('ring-2', on);
                card.classList.toggle('ring-indigo-700/20', on);
                card.classList.toggle('border-neutral-200', !on);
            }
        });
    }

    planRadios.forEach(function (r) {
        r.addEventListener('change', updatePlanPreview);
    });

    document.querySelectorAll('.saas-plan-card').forEach(function (card) {
        card.addEventListener('click', function () {
            const r = card.querySelector('input[type="radio"]');
            if (r) {
                r.checked = true;
                updatePlanPreview();
            }
        });
    });

    // Dynamic Billing Cycle Switcher (Zero page refresh)
    const btnCycleYearly = document.getElementById('btn-cycle-yearly');
    const btnCycleMonthly = document.getElementById('btn-cycle-monthly');

    function setCycle(cycle) {
        if (!cycleInput) return;
        cycleInput.value = cycle;
        const isYearly = (cycle === 'yearly');

        if (btnCycleYearly && btnCycleMonthly) {
            if (isYearly) {
                btnCycleYearly.className = 'px-3 py-1.5 rounded-lg font-bold transition-all cursor-pointer bg-white text-indigo-700 shadow-xs';
                btnCycleMonthly.className = 'px-3 py-1.5 rounded-lg font-bold transition-all cursor-pointer text-neutral-600 hover:text-neutral-900';
            } else {
                btnCycleMonthly.className = 'px-3 py-1.5 rounded-lg font-bold transition-all cursor-pointer bg-white text-indigo-700 shadow-xs';
                btnCycleYearly.className = 'px-3 py-1.5 rounded-lg font-bold transition-all cursor-pointer text-neutral-600 hover:text-neutral-900';
            }
        }

        // Update card prices
        planRadios.forEach(function (radio) {
            const k = radio.getAttribute('data-saas-plan-radio') || radio.value;
            const pYearly = radio.getAttribute('data-price-yearly');
            const pMonthly = radio.getAttribute('data-price-monthly');
            const pVal = isYearly ? pYearly : pMonthly;
            const priceEl = document.querySelector('[data-card-price="' + k + '"]');
            if (priceEl) {
                priceEl.textContent = (pVal === 'কাস্টম' ? 'কাস্টম' : pVal);
            }
        });

        document.querySelectorAll('[data-card-cycle-text]').forEach(function (el) {
            el.textContent = isYearly ? ' / বাৎসরিক' : ' / প্রতি মাসে';
        });

        updatePlanPreview();
    }

    if (btnCycleYearly) {
        btnCycleYearly.addEventListener('click', function (e) {
            e.preventDefault();
            setCycle('yearly');
        });
    }
    if (btnCycleMonthly) {
        btnCycleMonthly.addEventListener('click', function (e) {
            e.preventDefault();
            setCycle('monthly');
        });
    }

    updatePlanPreview();
});
</script>
