<?php
/**
 * Product-site home page.
 *
 * Ported from resources/js/pages/HomePage.jsx and the five components it
 * composed (Hero, Demos, Features, Reviews, Faq). Those components are used by
 * this page only, so they became sections here rather than partials.
 *
 * Two conversions worth knowing about:
 *
 *   - Every <button onClick={onNavigate('/x')}> became <a href="...">. Real
 *     requests, real URLs, no client router.
 *   - <CountUp end={5935} suffix="+" /> became an element with
 *     data-saas-countup="5935" data-saas-suffix="+". saas.js animates it when
 *     it scrolls into view and writes the final value server-side into the
 *     markup first, so the number is correct with JS disabled.
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
            'key'          => $is_custom ? null : $p->package_key,
            'is_pop'       => $is_pop,
            'card_cls'     => $is_pop
                ? 'ht-card p-8 border-2 border-indigo-600 rounded-3xl bg-white relative flex flex-col justify-between shadow-xl ring-4 ring-indigo-50 hover:shadow-2xl transition-all'
                : 'ht-card p-8 border border-neutral-200 rounded-3xl bg-white flex flex-col justify-between hover:border-indigo-300 hover:shadow-lg transition-all',
            'badge'        => $is_pop ? 'সবচেয়ে জনপ্রিয়' : null,
            'title'        => $p->name_bn . ' প্যাকেজ',
            'sub'          => $is_custom ? 'একসাথে ৫+ ইউনিয়ন পরিষদের জন্য' : ($is_pop ? 'সম্পূর্ণ পেপারলেস ও স্মার্ট ইউনিয়ন পরিষদ' : 'মাঝারি ইউনিয়ন পরিষদের জন্য উপযুক্ত'),
            'yearly_price' => $yearly_txt,
            'monthly_price'=> $monthly_txt,
            'yearly_unit'  => $yearly_unit,
            'monthly_unit' => $monthly_unit,
            'features'     => $feat_list,
            'cta'          => $is_custom ? 'সরাসরি যোগাযোগ করুন' : ($p->name_bn . ' প্যাকেজ শুরু করুন'),
            'cta_cls'      => $is_pop
                ? 'ht-btn ht-btn-primary w-full justify-center py-3 text-sm font-bold shadow-md cursor-pointer'
                : 'ht-btn ht-btn-secondary w-full justify-center py-3 text-sm font-bold cursor-pointer',
            'rule_cls'     => $is_pop ? 'mt-8 pt-5 border-t border-neutral-100' : 'mt-8 pt-5 border-t border-neutral-200',
        );
    }
} else {
    $saas_plans = array(
        array(
            'key'          => 'standard',
            'is_pop'       => false,
            'card_cls'     => 'ht-card p-8 border border-neutral-200 rounded-3xl bg-white flex flex-col justify-between hover:border-indigo-300 hover:shadow-lg transition-all',
            'badge'        => null,
            'title'        => 'স্ট্যান্ডার্ড প্যাকেজ',
            'sub'          => 'মাঝারি ইউনিয়ন পরিষদের জন্য উপযুক্ত',
            'yearly_price' => '৳২৫,০০০',
            'monthly_price'=> '৳২,৪০০',
            'yearly_unit'  => ' / বাৎসরিক',
            'monthly_unit' => ' / প্রতি মাসে',
            'features'     => array(
                'হোল্ডিং ট্যাক্স ও কর আদায় ম্যানেজমেন্ট',
                'পরিবার ভিত্তিক ডাটাশিট এন্ট্রি',
                'সাবডোমেন হোস্টিং (`union.viretaup.com`)',
                '৩ জন অপারেটর ও ইউজার অ্যাকাউন্ট',
                'সার্বক্ষণিক ফোন ও অনলাইন সাপোর্ট',
            ),
            'cta'          => 'স্ট্যান্ডার্ড প্যাকেজ শুরু করুন',
            'cta_cls'      => 'ht-btn ht-btn-secondary w-full justify-center py-3 text-sm font-bold cursor-pointer',
            'rule_cls'     => 'mt-8 pt-5 border-t border-neutral-200',
        ),
        array(
            'key'          => 'premium',
            'is_pop'       => true,
            'card_cls'     => 'ht-card p-8 border-2 border-indigo-600 rounded-3xl bg-white relative flex flex-col justify-between shadow-xl ring-4 ring-indigo-50 hover:shadow-2xl transition-all',
            'badge'        => 'সবচেয়ে জনপ্রিয়',
            'title'        => 'প্রিমিয়াম প্যাকেজ',
            'sub'          => 'সম্পূর্ণ পেপারলেস ও স্মার্ট ইউনিয়ন পরিষদ',
            'yearly_price' => '৳৪০,০০০',
            'monthly_price'=> '৳৩,৮০০',
            'yearly_unit'  => ' / বাৎসরিক',
            'monthly_unit' => ' / প্রতি মাসে',
            'features'     => array(
                'স্ট্যান্ডার্ড প্যাকেজের সকল সুবিধা',
                'ডিজিটাল সনদপত্র ও কিউআর কোড ভেরিফিকেশন',
                'কাস্টম ডোমেন বাইন্ডিং (`unionup.gov.bd`)',
                'আনলিমিটেড ইউজার ও অপারেটর অ্যাকাউন্ট',
                'বাল্ক এসএমএস নোটিফিকেশন ইন্টিগ্রেশন',
                'সরাসরি সাইটে গিয়ে স্টাফ ট্রেনিং ও অনবোর্ডিং',
            ),
            'cta'          => 'প্রিমিয়াম প্যাকেজ শুরু করুন',
            'cta_cls'      => 'ht-btn ht-btn-primary w-full justify-center py-3 text-sm font-bold shadow-md cursor-pointer',
            'rule_cls'     => 'mt-8 pt-5 border-t border-neutral-100',
        ),
        array(
            'key'          => null,
            'is_pop'       => false,
            'card_cls'     => 'ht-card p-8 border border-neutral-200 rounded-3xl bg-white flex flex-col justify-between hover:border-indigo-300 hover:shadow-lg transition-all',
            'badge'        => null,
            'title'        => 'উপজেলা ক্লাস্টার',
            'sub'          => 'একসাথে ৫+ ইউনিয়ন পরিষদের জন্য',
            'yearly_price' => 'কাস্টম',
            'monthly_price'=> 'কাস্টম',
            'yearly_unit'  => ' / আলোচনা সাপেক্ষে',
            'monthly_unit' => ' / আলোচনা সাপেক্ষে',
            'features'     => array(
                'উপজেলা নির্বাহী কর্মকর্তার (UNO) মনিটরিং পোর্টাল',
                'আনলিমিটেড ইউনিয়ন পরিষদ অ্যাক্টিভেশন',
                'ডেডিকেটেড ক্লাউড ডাটাবেজ ইনস্ট্যান্স',
                'অগ্রাধিকারমূলক ২৪/৭ ভিআইপি টেক সাপোর্ট',
            ),
            'cta'          => 'সরাসরি যোগাযোগ করুন',
            'cta_cls'      => 'ht-btn ht-btn-secondary w-full justify-center py-3 text-sm font-bold',
            'rule_cls'     => 'mt-8 pt-5 border-t border-neutral-200',
        ),
    );
}


$saas_modules = array(
    array('receipt',             'text-indigo-700 bg-indigo-100 hover:border-indigo-500', 'হোল্ডিং ট্যাক্স ও কর আদায়',
          '৯টি ওয়ার্ডের পাকা, আধা-পাকা ও কাঁচা বাড়ির বাৎসরিক ট্যাক্স অ্যাসেসমেন্ট, বকেয়া হিসাব এবং তাত্ক্ষণিক ডিজিটাল মানি রসিদ প্রিন্টিং।'),
    array('certificate',         'text-amber-700 bg-amber-100 hover:border-amber-500', 'ডিজিটাল সনদ ও কিউআর ভেরিফিকেশন',
          'চারিত্রিক, ওয়ারিশান, অবিবাহিত, ভূমিহীন ইত্যাদি সকল প্রকার সনদ এক ক্লিকে জেনারেট। কিউআর কোড স্ক্যান করে সত্যতা যাচাই।'),
    array('identification-card', 'text-emerald-700 bg-emerald-100 hover:border-emerald-500', 'নাগরিক ও পারিবারিক ডাটাবেজ',
          'ইউনিয়নের প্রতিটি খানাপ্রধান ও পরিবারের সদস্যদের এনআইডি, পেশা ও বয়স ভিত্তিক সম্পূর্ণ ডিজিটাল ডেটাবেজ।'),
    array('storefront',          'text-indigo-700 bg-indigo-50 hover:border-indigo-400', 'ট্রেড লাইসেন্স ব্যবস্থাপনা',
          'ইউনিয়নভুক্ত হাট-বাজার ও ব্যবসায়িক প্রতিষ্ঠানের ডিজিটাল ট্রেড লাইসেন্স প্রদান, বাৎসরিক নবায়ন ফি গ্রহণ ও রেকর্ড সংরক্ষণ।'),
    array('user-gear',           'text-rose-700 bg-rose-100 hover:border-rose-500', 'রোল ভিত্তিক ইউজার পারমিশন',
          'ইউপি চেয়ারম্যান, সচিব, হিসাব সহকারী, ইউডিসি উদ্যোক্তা ও ট্যাক্স কালেক্টরদের জন্য আলাদা আলাদা নিরাপদ অ্যাক্সেস কন্ট্রোল।'),
    array('chat-circle-dots',    'text-blue-700 bg-blue-100 hover:border-blue-500', 'বাল্ক এসএমএস নোটিফিকেশন',
          'কর পরিশোধের রিমাইন্ডার, সনদ প্রস্তুতের মেসেজ এবং জরুরি সরকারি নির্দেশনা সরাসরি নাগরিকের মোবাইলে এসএমএস পাঠানোর ব্যবস্থা।'),
);

$saas_reviews = array(
    array('মো: খলিলুর রহমান', 'ইউপি চেয়ারম্যান', 'আদর্শ মডেল ইউনিয়ন পরিষদ',
        'ViretaDev SaaS ব্যবহারের পর আমাদের ইউনিয়ন পরিষদের হোল্ডিং ট্যাক্স আদায় আগের বছরের চেয়ে দ্বিগুণ হয়েছে। নাগরিকরা এখন আর রসিদ বা কাগজ হারানোর ভয়ে থাকে না—সবকিছু অনলাইনে সংরক্ষিত থাকে।',
        'inc/assets/himg/avatar1.png'),
    array('মোহাম্মদ শাহ আলম', 'ইউপি সচিব', 'মীরসরাই মডেল ইউনিয়ন পরিষদ',
        'পূর্বে ওয়ারিশান ও চারিত্রিক সনদ তৈরি করতে ঘণ্টার পর ঘণ্টা খাতা দেখতে হতো। এখন মাত্র ১ ক্লিকে কিউআর কোডসহ সনদ তৈরি হয়ে যায়। ভূয়া সনদের সুযোগ সম্পূর্ণ বন্ধ হয়ে গেছে।',
        'inc/assets/himg/avatar2.png'),
    array('মোছা: জান্নাতুল ফেরদৌস', 'ইউডিসি ডিজিটাল উদ্যোক্তা', 'ইউনিয়ন ডিজিটাল সেন্টার',
        'ইন্টারফেসটি পুরোপুরি বাংলায় হওয়ায় আমাদের কাজ করা অত্যন্ত সহজ হয়েছে। গ্রামের সাধারণ মানুষ খুব সহজে তাদের হোল্ডিং ট্যাক্স যাচাই করতে পারছে। এটি প্রতিটি ইউপির জন্যই জরুরি।',
        'inc/assets/himg/avatar3.png'),
);

$saas_faqs = array(
    array('সফটওয়্যারটি ব্যবহার করতে কি কম্পিউটার বা সার্ভার কেনা লাগবে?',
          'না, কোনো সার্ভার কিনতে হবে না। এটি সম্পূর্ণ ক্লাউড ভিত্তিক SaaS প্ল্যাটফর্ম। যেকোনো কম্পিউটার, ল্যাপটপ বা স্মার্টফোনের ব্রাউজার দিয়েই ইন্টারনেট সংযোগ থাকলে এটি চালানো যায়।'),
    array('আমাদের ইউনিয়ন পরিষদের নিজস্ব ডোমেন বা সাবডোমেন ব্যবহার করা যাবে কি?',
          'হ্যাঁ, অবশ্যই। প্রতিটি ইউনিয়ন পরিষদ `unionname.viretaup.com` সাবডোমেন পাবে। অথবা চাইলে আপনার নিজস্ব অফিসিয়াল ডোমেন (যেমন: `yourunion.gov.bd`) আমাদের ক্লাউডে যুক্ত করা যাবে।'),
    array('পুরোনো খাতা-কলমের ডাটা কি সিস্টেমে এন্ট্রি করে দেওয়া হবে?',
          'আমাদের অনবোর্ডিং সাপোর্ট টিম আপনার ইউনিয়নের স্টাফ বা ডিজিটাল সেন্টারের উদ্যোক্তাদের হাতে-কলমে প্রশিক্ষণ প্রদান করবে যাতে খুব দ্রুত বাল্ক এক্সেল শিট থেকে বা ম্যানুয়াল এন্ট্রি দিয়ে ডাটা মাইগ্রেশন সম্পন্ন করা যায়।'),
    array('সরকারি নিয়ম ও গেজেট অনুযায়ী সনদ ও রসিদ কি ফরম্যাট করা?',
          'হ্যাঁ, বাংলাদেশ সরকারের স্থানীয় সরকার বিভাগ (LGD) কর্তৃক নির্ধারিত সর্বশেষ ফরম্যাট ও হোল্ডিং ট্যাক্স নির্ধারণ নীতিমালা মেনেই আমাদের মানি রসিদ ও সকল প্রকার সনদ ডিজাইন করা হয়েছে।'),
    array('নাগরিকের ডাটা কতটা নিরাপদ থাকবে?',
          'প্রতিটি ইউনিয়ন পরিষদের ডাটা আলাদা আইসোলেটেড ডাটাবেজ আর্কিটেকচারে এনক্রিপ্টেড থাকে এবং প্রতিদিন স্বয়ংক্রিয় ক্লাউড ব্যাকআপ নেওয়া হয়। ফলে ডাটা হারানো বা মুছে যাওয়ার কোনো ঝুঁকি নেই।'),
);
?>

        <main class="grow">

            <!-- 1. Hero Section with CountUp stats -->
            <section class="relative border-b border-neutral-200 bg-neutral-50 pt-12 pb-20">
                <div class="relative z-10 mx-auto max-w-7xl px-4 text-center sm:px-6 lg:px-8">

                    <span class="mb-6 inline-flex items-center gap-2 rounded-full border border-indigo-200 bg-indigo-100 px-3.5 py-1.5 text-xs font-semibold text-indigo-800 shadow-xs">
                        <?= icon('cloud', 16, 'fill', 'text-indigo-600') ?>
                        <span>নেক্সট-জেন মাল্টি-টেন্যান্ট B2B ই-গভর্ন্যান্স প্ল্যাটফর্ম</span>
                    </span>

                    <h1 class="mx-auto max-w-5xl text-3xl leading-tight font-extrabold tracking-tight text-neutral-900 sm:text-5xl sm:leading-tight lg:text-6xl">
                        আপনার ইউনিয়ন পরিষদকে করুন <br>
                        <span class="font-black text-indigo-600">১০০% ডিজিটাল, স্বচ্ছ ও পেপারলেস</span>
                    </h1>

                    <p class="mx-auto mt-5 max-w-3xl text-sm leading-relaxed text-neutral-600 sm:text-base">
                        হোল্ডিং ট্যাক্স অটোমেশন, ডিজিটাল সনদপত্র তৈরি, কিউআর কোড সত্যতা যাচাই এবং মোবাইল-ফার্স্ট সিটিজেন পোর্টাল—সবকিছু পরিচালিত হবে আপনার ইউনিয়নের নিজস্ব ব্র্যান্ডেড পোর্টালে।
                    </p>

                    <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
                        <a href="<?= site_url('register') ?>" class="ht-btn ht-btn-primary px-6 py-3 text-sm shadow-sm hover:shadow-md">
                            <?= icon('tag', 18, 'bold') ?>
                            <span>প্যাকেজ নির্বাচন ও নিবন্ধন করুন</span>
                        </a>
                        <a href="<?= site_url('plans') ?>" class="ht-btn ht-btn-secondary px-6 py-3 text-sm">
                            <?= icon('play-circle', 18, 'bold', 'text-indigo-600') ?>
                            <span>সাবস্ক্রিপশন প্যাকেজসমূহ</span>
                        </a>
                        <button type="button" data-toggle="modal" data-target="#supportModal" class="ht-btn ht-btn-accent px-6 py-3 text-sm">
                            <?= icon('headset', 18, 'fill') ?>
                            <span>সাপোর্ট টিমের সাথে কথা বলুন</span>
                        </button>
                    </div>

                    <?php /* data-saas-countup carries the target value. saas.js reads
                             it on scroll; the text inside is already the final
                             value, so the number is right without JS. */ ?>
                    <div class="mx-auto mt-14 grid max-w-6xl grid-cols-2 gap-6 border-t border-neutral-200/60 pt-8 text-center md:grid-cols-4">
                        <div class="neu-glass-card rounded-2xl p-5 text-center">
                            <span class="block text-3xl font-black text-indigo-600"
                                  data-saas-countup="12" data-saas-suffix="+" data-saas-duration="1500">12+</span>
                            <span class="mt-1 text-xs font-semibold text-neutral-500">সক্রিয় ইউনিয়ন পরিষদ</span>
                        </div>
                        <div class="neu-glass-card rounded-2xl p-5 text-center">
                            <span class="block text-3xl font-black text-amber-700"
                                  data-saas-countup="5935" data-saas-suffix="+" data-saas-duration="2000">5,935+</span>
                            <span class="mt-1 text-xs font-semibold text-neutral-500">পারিবারিক ডাটা এন্ট্রি</span>
                        </div>
                        <div class="neu-glass-card rounded-2xl p-5 text-center">
                            <span class="block text-3xl font-black text-emerald-600"
                                  data-saas-countup="4374" data-saas-suffix="+" data-saas-duration="2200">4,374+</span>
                            <span class="mt-1 text-xs font-semibold text-neutral-500">ট্যাক্স পেমেন্ট ও ইনভয়েস</span>
                        </div>
                        <div class="neu-glass-card rounded-2xl p-5 text-center">
                            <span class="block text-3xl font-black text-indigo-900"
                                  data-saas-countup="99" data-saas-prefix="9" data-saas-suffix=".৯%" data-saas-duration="1800">99.৯%</span>
                            <span class="mt-1 text-xs font-semibold text-neutral-500">ক্লাউড সার্ভিস আপটাইম</span>
                        </div>
                    </div>

                </div>
            </section>

            <!-- 2. Subscription Plans Section -->
            <section id="plans" class="border-b border-neutral-200 bg-neutral-50/60 py-20">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

                    <div class="mx-auto mb-12 max-w-3xl text-center">
                        <span class="mb-3 inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-3.5 py-1 text-xs font-bold text-amber-900 shadow-xs">
                            <?= icon('tag', 16, 'bold') ?>
                            <span>সাশ্রয়ী ও স্বচ্ছ প্যাকেজ প্ল্যান</span>
                        </span>
                        <h2 class="text-3xl font-extrabold tracking-tight text-neutral-900 sm:text-5xl">
                            আপনার ইউনিয়ন পরিষদের জন্য সেরা প্ল্যান বেছে নিন
                        </h2>
                        <p class="mt-3 text-sm text-neutral-600 sm:text-base leading-relaxed">
                            কোনো হিডেন চার্জ নেই। লাইভ ক্লাউড ব্যাকআপ, এনক্রিপ্টেড ডাটাবেজ এবং সার্বক্ষণিক সাপোর্ট সহ সম্পূর্ণ সরকারি উপযোগী সিস্টেম।
                        </p>

                        <!-- Interactive Billing Cycle Switch -->
                        <div class="mt-8 inline-flex p-1.5 neu-glass-inset rounded-2xl shadow-inner">
                            <button type="button" id="btnHomeCycleYearly" onclick="switchHomeBillingCycle('yearly')"
                               class="px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all cursor-pointer bg-white text-indigo-700 shadow-md border border-neutral-200/50">
                                বাৎসরিক বিলিং (১৫% সাশ্রয়ী)
                            </button>
                            <button type="button" id="btnHomeCycleMonthly" onclick="switchHomeBillingCycle('monthly')"
                               class="px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all cursor-pointer text-neutral-600 hover:text-indigo-600">
                                মাসিক বিলিং
                            </button>
                        </div>
                    </div>

                    <!-- 3 Pricing Cards Grid -->
                    <div class="mx-auto grid max-w-6xl grid-cols-1 gap-8 md:grid-cols-3 items-stretch">
                        <?php foreach ($saas_plans as $plan): ?>
                        <div class="<?= $plan['card_cls'] ?>">
                            <?php if (!empty($plan['badge'])): ?>
                            <span class="absolute -top-3.5 right-6 bg-indigo-600 text-white text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wider shadow-sm">
                                <?= $plan['badge'] ?>
                            </span>
                            <?php endif; ?>

                            <div>
                                <h3 class="text-xl font-bold text-neutral-900"><?= $plan['title'] ?></h3>
                                <p class="text-xs text-neutral-500 mt-1"><?= $plan['sub'] ?></p>

                                <div class="mt-5 pb-5 border-b border-neutral-200/70">
                                    <span class="text-4xl font-extrabold home-plan-price <?= $plan['is_pop'] ? 'text-indigo-600' : 'text-neutral-900' ?>"
                                          data-yearly-price="<?= $plan['yearly_price'] ?>"
                                          data-monthly-price="<?= $plan['monthly_price'] ?>">
                                        <?= $saas_is_yearly ? $plan['yearly_price'] : $plan['monthly_price'] ?>
                                    </span>
                                    <span class="text-xs text-neutral-500 home-plan-unit"
                                          data-yearly-unit="<?= $plan['yearly_unit'] ?>"
                                          data-monthly-unit="<?= $plan['monthly_unit'] ?>">
                                        <?= $saas_is_yearly ? $plan['yearly_unit'] : $plan['monthly_unit'] ?>
                                    </span>
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
                                <a href="<?= site_url('register?plan=' . $plan['key'] . '&cycle=' . $saas_cycle) ?>"
                                   class="home-plan-cta <?= $plan['cta_cls'] ?>"
                                   data-base-url="<?= site_url('register?plan=' . $plan['key']) ?>">
                                    <?= $plan['cta'] ?>
                                </a>
                                <?php else: ?>
                                <button type="button" data-toggle="modal" data-target="#supportModal" class="<?= $plan['cta_cls'] ?>">
                                    <?= $plan['cta'] ?>
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Compare Features Prompt Banner -->
                    <div class="mt-12 text-center">
                        <div class="inline-flex flex-col sm:flex-row items-center gap-3 rounded-2xl neu-glass-card px-6 py-4 shadow-sm">
                            <span class="text-xs sm:text-sm text-neutral-700 font-medium">সব প্যাকেজের বিস্তারিত ফিচার তুলনা ও শর্তাবলী দেখতে চান?</span>
                            <a href="<?= site_url('plans') ?>" class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-bold text-indigo-700 hover:text-indigo-900 underline underline-offset-4">
                                <span>সম্পূর্ণ ফিচার ম্যাট্রিক্স দেখুন</span>
                                <?= icon('arrow-right', 14, 'bold') ?>
                            </a>
                        </div>
                    </div>

                </div>
            </section>

            <script>
            function switchHomeBillingCycle(cycle) {
                var isYearly = (cycle === 'yearly');
                var btnY = document.getElementById('btnHomeCycleYearly');
                var btnM = document.getElementById('btnHomeCycleMonthly');
                if (btnY && btnM) {
                    if (isYearly) {
                        btnY.className = 'px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all cursor-pointer bg-white text-indigo-700 shadow-sm border border-neutral-200/50';
                        btnM.className = 'px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all cursor-pointer text-neutral-600 hover:text-neutral-900';
                    } else {
                        btnM.className = 'px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all cursor-pointer bg-white text-indigo-700 shadow-sm border border-neutral-200/50';
                        btnY.className = 'px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all cursor-pointer text-neutral-600 hover:text-neutral-900';
                    }
                }
                document.querySelectorAll('.home-plan-price').forEach(function(el) {
                    el.textContent = isYearly ? el.getAttribute('data-yearly-price') : el.getAttribute('data-monthly-price');
                });
                document.querySelectorAll('.home-plan-unit').forEach(function(el) {
                    el.textContent = isYearly ? el.getAttribute('data-yearly-unit') : el.getAttribute('data-monthly-unit');
                });
                document.querySelectorAll('.home-plan-cta').forEach(function(el) {
                    var base = el.getAttribute('data-base-url');
                    if (base) {
                        el.setAttribute('href', base + '&cycle=' + cycle);
                    }
                });
            }
            </script>

            <!-- 3. Platform modules -->
            <section id="features" class="border-b border-neutral-200 bg-white py-16">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

                    <div class="mx-auto mb-12 max-w-2xl text-center">
                        <span class="mb-2 inline-flex items-center gap-1.5 rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-800">
                            <?= icon('squares-four', 16, 'bold') ?>
                            <span>প্ল্যাটফর্ম মডিউলসমূহ</span>
                        </span>
                        <h2 class="text-2xl font-bold tracking-tight text-neutral-900 sm:text-4xl">
                            একটি সফটওয়্যারেই ইউনিয়ন পরিষদের সব কার্যক্রম
                        </h2>
                        <p class="mt-2 text-xs text-neutral-500 sm:text-sm">
                            প্রতিটি মডিউল বাস্তব ইউনিয়ন পরিষদের কর্মপদ্ধতি ও নিয়মনীতি মেনে তৈরি করা হয়েছে।
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                        <?php foreach ($saas_modules as $mod): ?>
                        <div class="ht-card border border-neutral-200 bg-neutral-50 p-6 transition-all hover:shadow-lg">
                            <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl border border-transparent text-2xl <?= $mod[1] ?>">
                                <?= icon($mod[0], 26, 'bold') ?>
                            </div>
                            <h3 class="text-lg font-bold text-neutral-900"><?= $mod[2] ?></h3>
                            <p class="mt-2 text-xs leading-relaxed text-neutral-500"><?= $mod[3] ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>

                </div>
            </section>

            <!-- 4. Reviews -->
            <section id="reviews" class="border-b border-neutral-200 bg-white py-16">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

                    <div class="mx-auto mb-12 max-w-2xl text-center">
                        <span class="mb-2 inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800">
                            <?= icon('chat-circle-dots', 16, 'bold') ?>
                            <span>সফল গ্রাহকদের প্রতিক্রিয়া</span>
                        </span>
                        <h2 class="text-2xl font-bold tracking-tight text-neutral-900 sm:text-4xl">
                            জনপ্রতিনিধি ও কর্মকর্তাদের মতামত
                        </h2>
                        <p class="mt-2 text-xs text-neutral-500 sm:text-sm">
                            দেশের বিভিন্ন ইউনিয়ন পরিষদের চেয়ারম্যান ও সচিবগণ কীভাবে ViretaDev দিয়ে সুফল পাচ্ছেন তা জেনে নিন।
                        </p>
                    </div>

                    <div class="mx-auto grid max-w-6xl grid-cols-1 gap-6 md:grid-cols-3">
                        <?php foreach ($saas_reviews as $review): ?>
                        <div class="ht-card flex flex-col justify-between rounded-2xl border border-neutral-200 bg-neutral-50/60 p-6 transition-all hover:border-indigo-300 hover:shadow-lg sm:p-7">

                            <div>
                                <div class="mb-4 flex items-center gap-1 text-amber-500">
                                    <?php for ($star = 0; $star < 5; $star++) {
                                        echo icon('star', 16, 'fill');
                                    } ?>
                                </div>
                                <p class="text-xs leading-relaxed text-neutral-700 italic sm:text-sm">"<?= $review[3] ?>"</p>
                            </div>

                            <div class="mt-6 flex items-center gap-3 border-t border-neutral-200/80 pt-4">
                                <?php /* The React version had an onError fallback to
                                         ui-avatars.com. Kept as a data attribute read by
                                         saas.js rather than an inline handler, so the
                                         markup carries no script. */ ?>
                                <div class="h-11 w-11 shrink-0 overflow-hidden rounded-full border border-indigo-200 bg-indigo-100">
                                    <img src="<?= $review[4] ?>" alt="<?= $review[0] ?>"
                                         class="h-full w-full object-cover"
                                         data-saas-avatar="<?= rawurlencode($review[0]) ?>">
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-neutral-900"><?= $review[0] ?></h4>
                                    <p class="text-xs font-semibold text-indigo-700"><?= $review[1] ?></p>
                                    <p class="text-[11px] text-neutral-500"><?= $review[2] ?></p>
                                </div>
                            </div>

                        </div>
                        <?php endforeach; ?>
                    </div>

                </div>
            </section>

            <!-- 5. FAQ. Exclusive accordion; first item open server-side. -->
            <section id="faq" class="border-b border-neutral-200 bg-neutral-50 py-16">
                <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">

                    <div class="mx-auto mb-12 max-w-2xl text-center">
                        <span class="mb-2 inline-flex items-center gap-1.5 rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-800">
                            <?= icon('question', 16, 'bold') ?>
                            <span>সচরাচর জিজ্ঞাসা</span>
                        </span>
                        <h2 class="text-2xl font-bold tracking-tight text-neutral-900 sm:text-4xl">
                            সাধারণ প্রশ্ন ও উত্তর (FAQ)
                        </h2>
                        <p class="mt-2 text-xs text-neutral-500 sm:text-sm">
                            ইউনিয়ন পরিষদ ডিজিটালাইজেশন সংক্রান্ত আপনার সাধারণ প্রশ্নগুলোর দ্রুত উত্তর।
                        </p>
                    </div>

                    <div class="space-y-3.5" data-saas-accordion>
                        <?php foreach ($saas_faqs as $i => $faq):
                            $panel_id = 'faqPanel' . $i;
                            $is_open  = ($i === 0);
                        ?>
                        <div class="overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-xs transition-all">
                            <button type="button" data-saas-accordion
                                    aria-controls="<?= $panel_id ?>" aria-expanded="<?= $is_open ? 'true' : 'false' ?>"
                                    class="flex w-full cursor-pointer items-center justify-between gap-4 p-5 text-left font-bold text-neutral-900 transition-colors hover:text-indigo-600">
                                <span class="text-sm sm:text-base"><?= $faq[0] ?></span>
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-neutral-100 text-neutral-600">
                                    <?php /* Both carets are rendered; CSS shows one.
                                             The React version swapped them on state. */ ?>
                                    <span data-saas-caret class="<?= $is_open ? 'rotate-180' : '' ?>">
                                        <?= icon('caret-down', 16, 'bold') ?>
                                    </span>
                                </span>
                            </button>
                            <div id="<?= $panel_id ?>" class="border-t border-neutral-100 px-5 pt-3 pb-5 text-xs leading-relaxed text-neutral-600 sm:text-sm<?= $is_open ? '' : ' hidden' ?>"><?php /* `hidden` belongs INSIDE the one class attribute. Emitting a
                                     second class="hidden" alongside it made the HTML parser
                                     keep the first and drop this whole styling list, so every
                                     panel but the first lost its padding, size and colour the
                                     moment it was opened. Same shape as saas/pages/faq.php. */ ?>
                                <?= $faq[1] ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                </div>
            </section>

            <!-- 6. CTA banner -->
            <section class="border-b border-indigo-900 bg-indigo-950 py-16 text-white">
                <div class="mx-auto max-w-6xl px-4 text-center sm:px-6 lg:px-8">
                    <span class="mb-4 inline-flex items-center gap-1.5 rounded-full border border-amber-400/30 bg-amber-400/20 px-3 py-1 text-xs font-semibold text-amber-300">
                        <?= icon('tag', 16, 'bold') ?>
                        <span>ডিজিটাল ইউনিয়ন পরিষদ প্ল্যাটফর্ম</span>
                    </span>
                    <h2 class="mb-4 text-2xl font-extrabold tracking-tight sm:text-4xl">
                        আপনার ইউনিয়নকে পেপারলেস করতে আর দেরি কেন?
                    </h2>
                    <p class="mx-auto mb-8 max-w-2xl text-sm leading-relaxed text-indigo-200 sm:text-base">
                        মাত্র ১ ক্লিকে শুরু করুন। আমাদের সাশ্রয়ী বাৎসরিক প্যাকেজ এবং সার্বক্ষণিক টেকনিক্যাল সাপোর্ট রয়েছে আপনার সাথে।
                    </p>
                    <div class="flex flex-wrap justify-center gap-4">
                        <a href="<?= site_url('plans') ?>" class="ht-btn ht-btn-primary px-6 py-3 text-sm font-bold shadow-lg">
                            <span>প্যাকেজ ও মূল্য তালিকা দেখুন</span>
                            <?= icon('arrow-right', 16, 'bold') ?>
                        </a>
                        <a href="<?= site_url('contact') ?>" class="ht-btn ht-btn-secondary px-6 py-3 text-sm font-bold shadow-md">
                            <span>সরাসরি ডেমো বুক করুন</span>
                        </a>
                    </div>
                </div>
            </section>

        </main>
