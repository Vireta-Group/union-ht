<?php
/**
 * Product-site pricing page.
 *
 * Ported from resources/js/pages/PlansPage.jsx.
 *
 * Three conversions worth knowing about:
 *
 *   - Plan selection. The React page called onSelectPlan(planKey), which wrote
 *     the choice into app state and handed it to /register on client-side
 *     navigation. That state is gone, so each plan CTA is now a real link to
 *     /register?plan=<key>, which is what Front::register() reads to preselect
 *     the form. The two keys the JSX used, 'standard' and 'premium', are both in
 *     that whitelist. The third card never chose a plan — its CTA opened the
 *     support modal, so it still does.
 *   - The billing-cycle toggle. That was useState('yearly') in the component.
 *     It is now a ?cycle= query param read here, the same way saas.js honours
 *     ?tab= on the policy page: the default (yearly) is rendered server-side, so
 *     prices are correct without JavaScript, and either cycle is a shareable URL.
 *   - The plan FAQs were never an accordion in React — every answer was open —
 *     so they stay a plain list here. No accordion contract is added.
 */

$saas_cycle = ($this->input->get('cycle') === 'monthly') ? 'monthly' : 'yearly';
$saas_is_yearly = ($saas_cycle === 'yearly');

if (!empty($saas_packages)) {
    $saas_plans = array();
    foreach ($saas_packages as $p) {
        $is_pop = ($p->package_key === 'premium');
        $is_custom = ($p->price_yearly == 0 && $p->price_monthly == 0);
        $yearly_txt = $is_custom ? 'কাস্টম' : taka($p->price_yearly);
        $monthly_txt = $is_custom ? 'কাস্টম' : taka($p->price_monthly);
        $yearly_unit = $is_custom ? ' / আলোচনা সাপেক্ষে' : ' / বাৎসরিক';
        $monthly_unit = $is_custom ? ' / আলোচনা সাপেক্ষে' : ' / প্রতি মাসে';

        $feat_list = array();
        if (!empty($p->features)) {
            $raw_feats = preg_split('/[\r\n|]+/', $p->features);
            foreach ($raw_feats as $rf) {
                $rf = trim($rf);
                if ($rf !== '') { $feat_list[] = $rf; }
            }
        }
        if ($p->max_holdings !== null) {
            $feat_list[] = bn_number($p->max_holdings) . ' হোল্ডিং ডাটা এন্ট্রি সীমা';
        }
        if ($p->max_users !== null) {
            $feat_list[] = bn_number($p->max_users) . ' জন অপারেটর ও ইউজার আইডি';
        }

        $saas_plans[] = array(
            'key'       => $is_custom ? null : $p->package_key,
            'card_cls'  => $is_pop
                ? 'ht-card neu-glass-card p-7 border-2 border-indigo-600/90 rounded-3xl relative flex flex-col justify-between ring-2 ring-indigo-500/20'
                : 'ht-card neu-glass-card p-7 rounded-3xl flex flex-col justify-between',
            'badge'     => $is_pop ? 'সবচেয়ে জনপ্রিয়' : null,
            'title'     => $p->name_bn . ' প্যাকেজ',
            'sub'       => $is_custom ? 'একসাথে ৫+ ইউনিয়ন পরিষদের জন্য' : ($is_pop ? 'সম্পূর্ণ পেপারলেস ও স্মার্ট ইউনিয়ন পরিষদ' : 'মাঝারি ইউনিয়ন পরিষদের জন্য উপযুক্ত'),
            'price'     => array($yearly_txt, $monthly_txt),
            'unit'      => array($yearly_unit, $monthly_unit),
            'price_cls' => $is_pop ? 'text-indigo-600' : 'text-neutral-900',
            'features'  => $feat_list,
            'cta'       => $is_custom ? 'সরাসরি কথা বলুন' : ($p->name_bn . ' নির্বাচন ও নিবন্ধন'),
            'cta_cls'   => $is_pop
                ? 'ht-btn ht-btn-primary w-full justify-center py-2.5 shadow-md cursor-pointer'
                : 'ht-btn ht-btn-secondary w-full justify-center py-2.5 cursor-pointer',
            'rule_cls'  => $is_pop ? 'mt-8 pt-4 border-t border-neutral-200/60' : 'mt-8 pt-4 border-t border-neutral-200/60',
        );
    }
} else {
    $saas_plans = array(
        array(
            'key'      => 'standard',
            'card_cls' => 'ht-card neu-glass-card p-7 rounded-3xl flex flex-col justify-between',
            'badge'    => null,
            'title'    => 'স্ট্যান্ডার্ড প্যাকেজ',
            'sub'      => 'মাঝারি ইউনিয়ন পরিষদের জন্য উপযুক্ত',
            'price'    => array('৳২৫,০০০', '৳২,৪০০'),
            'unit'     => array(' / বাৎসরিক', ' / প্রতি মাসে'),
            'price_cls' => 'text-neutral-900',
            'features' => array(
                'হোল্ডিং ট্যাক্স ও কর আদায় ম্যানেজমেন্ট',
                'পরিবার ভিত্তিক ডাটাশিট এন্ট্রি',
                'সাবডোমেন হোস্টিং (`union.viretaup.com`)',
                '৩ জন অপারেটর ও ইউজার অ্যাকাউন্ট',
                'সার্বক্ষণিক ফোন ও অনলাইন সাপোর্ট',
            ),
            'cta'       => 'স্ট্যান্ডার্ড নির্বাচন ও নিবন্ধন',
            'cta_cls'   => 'ht-btn ht-btn-secondary w-full justify-center py-2.5 cursor-pointer',
            'rule_cls'  => 'mt-8 pt-4 border-t border-neutral-200/60',
        ),
        array(
            'key'      => 'premium',
            'card_cls' => 'ht-card neu-glass-card p-7 border-2 border-indigo-600/90 rounded-3xl relative flex flex-col justify-between ring-2 ring-indigo-500/20',
            'badge'    => 'সবচেয়ে জনপ্রিয়',
            'title'    => 'প্রিমিয়াম প্যাকেজ',
            'sub'      => 'সম্পূর্ণ পেপারলেস ও স্মার্ট ইউনিয়ন পরিষদ',
            'price'    => array('৳৪০,০০০', '৳৩,৮০০'),
            'unit'     => array(' / বাৎসরিক', ' / প্রতি মাসে'),
            'price_cls' => 'text-indigo-600',
            'features' => array(
                'স্ট্যান্ডার্ড প্যাকেজের সকল সুবিধা',
                'ডিজিটাল সনদপত্র ও কিউআর কোড ভেরিফিকেশন',
                'কাস্টম ডোমেন বাইন্ডিং (`unionup.gov.bd`)',
                'আনলিমিটেড ইউজার ও অপারেটর অ্যাকাউন্ট',
                'বাল্ক এসএমএস নোটিফিকেশন ইন্টিগ্রেশন',
                'সরাসরি সাইটে গিয়ে স্টাফ ট্রেনিং ও অনবোর্ডিং',
            ),
            'cta'       => 'প্রিমিয়াম নির্বাচন ও নিবন্ধন',
            'cta_cls'   => 'ht-btn ht-btn-primary w-full justify-center py-2.5 shadow-md cursor-pointer',
            'rule_cls'  => 'mt-8 pt-4 border-t border-neutral-200/60',
        ),
        array(
            'key'      => null,
            'card_cls' => 'ht-card neu-glass-card p-7 rounded-3xl flex flex-col justify-between',
            'badge'    => null,
            'title'    => 'উপজেলা ক্লাস্টার',
            'sub'      => 'একসাথে ৫+ ইউনিয়ন পরিষদের জন্য',
            'price'    => array('কাস্টম', 'কাস্টম'),
            'unit'     => array(' / আলোচনা সাপেক্ষে', ' / আলোচনা সাপেক্ষে'),
            'price_cls' => 'text-neutral-900',
            'features' => array(
                'উপজেলা নির্বাহী কর্মকর্তার (UNO) মনিটরিং পোর্টাল',
                'আনলিমিটেড ইউনিয়ন পরিষদ অ্যাক্টিভেশন',
                'ডেডিকেটেড ক্লাউড ডাটাবেজ ইনস্ট্যান্স',
                'অগ্রাধিকারমূলক ২৪/৭ ভিআইপি টেক সাপোর্ট',
            ),
            'cta'       => 'সরাসরি কথা বলুন',
            'cta_cls'   => 'ht-btn ht-btn-secondary w-full justify-center py-2.5',
            'rule_cls'  => 'mt-8 pt-4 border-t border-neutral-200/60',
        ),
    );
}

$saas_compare_cols = array(
    array('feat' => 'standard', 'head' => 'স্ট্যান্ডার্ড', 'head_cls' => 'p-4 font-bold text-center text-neutral-900 w-36',                   'cell_cls' => 'p-4 text-center'),
    array('feat' => 'premium',  'head' => 'প্রিমিয়াম',  'head_cls' => 'p-4 font-bold text-center text-indigo-700 bg-indigo-50/70 w-36',      'cell_cls' => 'p-4 text-center bg-indigo-50/30 font-semibold text-indigo-900'),
    array('feat' => 'cluster',  'head' => 'ক্লাস্টার',  'head_cls' => 'p-4 font-bold text-center text-neutral-900 w-36',                   'cell_cls' => 'p-4 text-center'),
);

$saas_compare = array(
    array('হোল্ডিং ট্যাক্স ও কর আদায় সিস্টেম',                       true,        true,        true),
    array('পরিবার ভিত্তিক ডাটাশিট এন্ট্রি',                        true,        true,        true),
    array('সাবডোমেন হোস্টিং (`union.viretaup.com`)',                true,        true,        true),
    array('ডিজিটাল সনদ ও কিউআর কোড ভেরিফিকেশন',                     false,       true,        true),
    array('কাস্টম সরকারি ডোমেন বাইন্ডিং (`.gov.bd`)',                 false,       true,        true),
    array('বাল্ক এসএমএস গেটওয়ে ইন্টিগ্রেশন',                        false,       true,        true),
    array('অপারেটর ও ইউজার অ্যাকাউন্ট সংখ্যা',                        '৩ জন',      'আনলিমিটেড', 'আনলিমিটেড'),
    array('সরাসরি সাইটে গিয়ে স্টাফ ট্রেনিং ও অনবোর্ডিং',            'অনলাইন',    'সরাসরি সাইটে', 'ডেডিকেটেড টিম'),
    array('উপজেলা নির্বাহী কর্মকর্তার (UNO) মনিটরিং পোর্টাল',      false,       false,       true),
    array('ডেডিকেটেড ক্লাউড ডাটাবেজ ইনস্ট্যান্স',                    false,       false,       true),
    array('টেকনিক্যাল সাপোর্ট চ্যানেল',                              'ফোন ও অনলাইন', 'অগ্রাধিকারমূলক ২৪/৭', 'ভিআইপি ২৪/৭ ডেডিকেটেড'),
);

$saas_plan_faqs = array(
    array('পেমেন্ট কীভাবে পরিশোধ করতে হয়?',
          'ব্যাংক ট্রান্সফার, পে-অর্ডার বা সরাসরি সরকারি চেক এবং বিকাশ/নগদের মাধ্যমে বাৎসরিক বা মাসিক পেমেন্ট পরিশোধ করা যায়। পরিশোধের সাথে সাথেই অফিশিয়াল ইনভয়েস ও রসিদ প্রদান করা হয়।'),
    array('অ্যাক্টিভেশন হতে কতক্ষণ সময় লাগে?',
          'প্যাকেজ নির্বাচনের পর সাধারণত ২৪ ঘণ্টার মধ্যেই ইউনিয়ন পরিষদের সাবডোমেন ও অ্যাডমিন প্যানেল রেডি হয়ে যায়। এরপর ২-৩ দিনের মধ্যে প্রাথমিক ডাটা সেটআপ সম্পন্ন হয়।'),
    array('কোনো লুকানো বা অতিরিক্ত মাসিক ফি আছে কি?',
          'না। আমাদের বাৎসরিক প্যাকেজে ক্লাউড হোস্টিং, ডাটাবেজ ব্যাকআপ, সফটওয়্যার মেইনটেন্যান্স এবং টেক সাপোর্ট অন্তর্ভুক্ত থাকে। কোনো লুকানো চার্জ নেই।'),
);
?>

        <main class="grow py-12">

            <!-- 1. Page header + billing cycle toggle -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-12">
                <div class="text-center max-w-3xl mx-auto">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-100/80 text-amber-900 border border-amber-200/60 mb-4 backdrop-blur-xs">
                        <?= icon('tag', 16, 'bold') ?>
                        <span>স্বচ্ছ ও সাশ্রয়ী প্রাইসিং</span>
                    </span>
                    <h1 class="text-3xl sm:text-5xl font-extrabold text-neutral-900 tracking-tight leading-tight">
                        ইউনিয়ন পরিষদের জন্য বাৎসরিক প্যাকেজসমূহ
                    </h1>
                    <p class="mt-4 text-sm sm:text-base text-neutral-600 leading-relaxed">
                        কোনো লুকানো খরচ নেই। আপনার ইউনিয়ন পরিষদের আয়তন ও প্রয়োজনীয় ফিচার অনুযায়ী সঠিক প্যাকেজ নির্বাচন করুন।
                    </p>

                    <?php /* Was useState('yearly'). Now ?cycle= picks the price pair
                              server-side, so the yearly default is in the HTML
                              before any script runs. */ ?>
                    <div class="mt-8 inline-flex p-1.5 neu-glass-inset rounded-2xl">
                        <a href="<?= site_url('plans?cycle=yearly') ?>"<?= $saas_is_yearly ? ' aria-current="page"' : '' ?>
                           class="px-5 py-2 rounded-xl text-xs sm:text-sm font-bold transition-all cursor-pointer <?= $saas_is_yearly ? 'bg-indigo-600 text-white shadow-md' : 'text-neutral-600 hover:text-indigo-600' ?>">
                            বাৎসরিক বিলিং (১৫% সাশ্রয়ী)
                        </a>
                        <a href="<?= site_url('plans?cycle=monthly') ?>"<?= $saas_is_yearly ? '' : ' aria-current="page"' ?>
                           class="px-5 py-2 rounded-xl text-xs sm:text-sm font-bold transition-all cursor-pointer <?= $saas_is_yearly ? 'text-neutral-600 hover:text-indigo-600' : 'bg-indigo-600 text-white shadow-md' ?>">
                            মাসিক বিলিং
                        </a>
                    </div>
                </div>
            </div>

            <!-- 2. The three pricing cards. A plan CTA is a link that carries
                 the plan key to /register, which replaces the React handoff of
                 the selected plan through state. -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-20">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto items-stretch">

                    <?php foreach ($saas_plans as $plan):
                        $saas_price_i = $saas_is_yearly ? 0 : 1;
                    ?>
                    <div class="<?= $plan['card_cls'] ?>">
                        <?php if (!empty($plan['badge'])): ?>
                        <span class="absolute -top-3.5 right-6 bg-indigo-600 text-white text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wider shadow-xs">
                            <?= $plan['badge'] ?>
                        </span>
                        <?php endif; ?>

                        <div>
                            <h3 class="text-xl font-bold text-neutral-900"><?= $plan['title'] ?></h3>
                            <p class="text-xs text-neutral-500 mt-1"><?= $plan['sub'] ?></p>

                            <div class="mt-5 pb-5 border-b border-neutral-200">
                                <span class="text-4xl font-extrabold <?= $plan['price_cls'] ?>"><?= $plan['price'][$saas_price_i] ?></span>
                                <span class="text-xs text-neutral-400"><?= $plan['unit'][$saas_price_i] ?></span>
                            </div>

                            <ul class="mt-6 space-y-3.5 text-xs text-neutral-600">
                                <?php foreach ($plan['features'] as $feature): ?>
                                <li class="flex items-center gap-2.5">
                                    <?= icon('check', 16, 'bold', 'text-emerald-600 shrink-0') ?>
                                    <span><?= $feature ?></span>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                        <div class="<?= $plan['rule_cls'] ?>">
                            <?php if ($plan['key'] !== null): ?>
                            <a href="<?= site_url('register?plan=' . $plan['key'] . '&cycle=' . $saas_cycle) ?>" class="<?= $plan['cta_cls'] ?>">
                                <?= $plan['cta'] ?>
                            </a>
                            <?php else: ?>
                            <?php /* Cluster pricing is negotiated, so this card
                                     never chose a plan — it opened the support
                                     modal, and still does. */ ?>
                            <button type="button" data-toggle="modal" data-target="#supportModal" class="<?= $plan['cta_cls'] ?>">
                                <?= $plan['cta'] ?>
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>

                </div>
            </div>

            <!-- 3. Complete feature comparison table -->
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 mb-20">
                <div class="text-center max-w-2xl mx-auto mb-10">
                    <h2 class="text-2xl sm:text-3xl font-bold text-neutral-900">
                        বিস্তারিত ফিচার তুলনা (Comparison Matrix)
                    </h2>
                    <p class="text-xs sm:text-sm text-neutral-500 mt-1">
                        কোন প্যাকেজে কী কী সুবিধা পাবেন তা এক নজরে দেখে নিন।
                    </p>
                </div>

                <div class="overflow-x-auto neu-glass-card rounded-3xl p-1">
                    <!-- min-w keeps the plan-comparison columns legible on a phone.
                         w-full alone squeezes four columns into ~90px each and the
                         Bengali labels wrap into unreadable slivers. The
                         overflow-x-auto wrapper above turns that extra width into
                         an inner scroll instead of sliding the whole page. -->
                    <table class="w-full text-left border-collapse text-xs sm:text-sm" style="min-width: 640px;">
                        <thead>
                            <tr class="border-b border-neutral-200 bg-neutral-50">
                                <th class="p-4 font-bold text-neutral-900">ফিচারসমূহ</th>
                                <?php foreach ($saas_compare_cols as $col): ?>
                                <th class="<?= $col['head_cls'] ?>"><?= $col['head'] ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 text-neutral-700">
                            <?php foreach ($saas_compare as $feat):
                                $saas_col_idx = 0;
                            ?>
                            <tr class="hover:bg-neutral-50/80 transition-colors">
                                <td class="p-4 font-medium text-neutral-900"><?= $feat[0] ?></td>

                                <?php foreach ($saas_compare_cols as $col):
                                    $saas_cell = $feat[++$saas_col_idx];
                                ?>
                                <td class="<?= $col['cell_cls'] ?>">
                                    <?php if (is_bool($saas_cell)): ?>
                                    <?= $saas_cell
                                        ? icon('check', 18, 'bold', 'text-emerald-600 mx-auto')
                                        : icon('x', 16, 'bold', 'text-neutral-300 mx-auto') ?>
                                    <?php elseif ($col['feat'] === 'premium'): ?>
                                    <?php /* The premium cell already carries
                                             font-semibold in its own class. */ ?>
                                    <span><?= $saas_cell ?></span>
                                    <?php else: ?>
                                    <span class="font-semibold"><?= $saas_cell ?></span>
                                    <?php endif; ?>
                                </td>
                                <?php endforeach; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 4. Plan FAQs. Not an accordion in the React original: every
                     answer was visible at once, and that is what this is. -->
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-xl mx-auto mb-8">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800 mb-2">
                        <?= icon('question', 16, 'bold') ?>
                        <span>প্রাইসিং সংক্রান্ত প্রশ্ন</span>
                    </span>
                    <h2 class="text-2xl font-bold text-neutral-900">
                        প্যাকেজ ও পেমেন্ট বিষয়ক জিজ্ঞাসা
                    </h2>
                </div>

                <div class="space-y-4">
                    <?php foreach ($saas_plan_faqs as $faq): ?>
                    <div class="p-5 rounded-2xl neu-glass-card">
                        <h4 class="font-bold text-neutral-900 text-sm"><?= $faq[0] ?></h4>
                        <p class="text-xs sm:text-sm text-neutral-600 mt-2 leading-relaxed"><?= $faq[1] ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </main>
