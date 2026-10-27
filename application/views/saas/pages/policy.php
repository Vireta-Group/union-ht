<?php
/**
 * Product-site policy page.
 *
 * Ported from resources/js/pages/PolicyPage.jsx. /policy, /privacy and /terms
 * are three URLs for this one view — Front::policy(), ::privacy() and ::terms()
 * each render it and pass $saas_tab, so the open tab is right in the first byte
 * of HTML rather than being corrected by JavaScript after paint. saas.js only
 * steps in when ?tab= disagrees with what the controller picked.
 *
 * Two things changed, both forced by saas.js:
 *
 *   1. The tab buttons do not keep their JSX colours. saas.js toggles exactly
 *      border-indigo-600, text-indigo-600, text-neutral-500 and hidden, so an
 *      active tab is text-indigo-600 with an indigo border and an inactive one
 *      is text-neutral-500. The JSX pairs (bg-indigo-700 text-white shadow-md
 *      vs. text-neutral-700) would leave text-white in the class list fighting
 *      the class the script just added, and the server-rendered state and the
 *      scripted state would disagree after the first click. The pill shape,
 *      padding, icon and rounded-2xl are the JSX's; the active background is
 *      now bg-indigo-50 so it still reads against text-indigo-600.
 *   2. All four panels are in the DOM and the inactive three carry `hidden`,
 *      because the script switches panels by toggling that one class. The JSX
 *      mounted only the active panel.
 *
 * TABS in the JSX had four entries — privacy, terms, security, billing — but
 * only the first two have a route in Front.php, so on a cold load only privacy
 * and terms can be server-selected. The ?tab= row under the header is what
 * reaches the other two, from any of the three URLs; the view reads ?tab=
 * ahead of the controller's choice, and the unknown-key guard below falls back
 * to privacy rather than rendering four inactive panels.
 *
 * The legal-helpdesk button lost its handler and became the shared
 * support-modal trigger.
 *
 * @var string $saas_path  Active route: '/policy', '/privacy' or '/terms'.
 * @var string $saas_tab   Tab the route selected. Defaults to 'privacy'.
 */

$saas_tab = $saas_tab ?? 'privacy';

/* ?tab= wins over the route. The ?tab= row under the header is the only way to
   reach security and billing, because Front.php has no route that selects them.
   FALSE is the second argument so xss_clean cannot normalise the value out from
   under the isset() check below. */
$saas_qtab = $this->input->get('tab', FALSE);
if (is_string($saas_qtab) && $saas_qtab !== '') {
    $saas_tab = $saas_qtab;
}

/* TABS, in the JSX order. Panel ids are the aria-controls targets. */
$saas_panels = array(
    'privacy'  => array('id' => 'policyTabPrivacy',  'label' => 'প্রাইভেসি পলিসি',         'icon' => 'shield-check'),
    'terms'    => array('id' => 'policyTabTerms',    'label' => 'সেবার শর্তাবলী (Terms)',    'icon' => 'file-text'),
    'security' => array('id' => 'policyTabSecurity', 'label' => 'ডাটা নিরাপত্তা ও SLA',      'icon' => 'cloud-check'),
    'billing'  => array('id' => 'policyTabBilling',  'label' => 'সাবস্ক্রিপশন ও রিফান্ড',   'icon' => 'credit-card'),
);

if (!isset($saas_panels[$saas_tab])) {
    $saas_tab = 'privacy';
}

/* Panel class list: the JSX list, plus `hidden` on every panel the route did
   not select. One class attribute, so the script's classList.toggle('hidden')
   is the only thing that ever changes a panel's visibility. */
$saas_panel_cls = function ($key) use ($saas_tab) {
    return 'space-y-8 animate-in fade-in duration-150' . ($key === $saas_tab ? '' : ' hidden');
};

/* ?tab= deep links. Absolute, so the query string does not resolve against the
   <base href> in saas/head.php and land on the landing page. */
$saas_self_url = site_url(trim($saas_path ?? '/policy', '/')) . '?tab=';
?>

        <main class="grow">

            <div class="py-12 min-h-screen">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                    <!-- Header -->
                    <div class="text-center max-w-3xl mx-auto mb-10">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800 mb-3">
                            <?= icon('scales', 16, 'bold') ?>
                            <span>আইনগত ও প্রাতিষ্ঠানিক নীতিমালা</span>
                        </span>
                        <h1 class="text-3xl sm:text-5xl font-black text-neutral-900 tracking-tight leading-tight">
                            পলিসি ও সেবার শর্তাবলী
                        </h1>
                        <p class="mt-3 text-sm sm:text-base text-neutral-600 leading-relaxed">
                            স্থানীয় সরকার (ইউনিয়ন পরিষদ) আইন, আইসিটি অ্যাক্ট এবং সরকারি ডাটা সুরক্ষা প্রটোকল মেনেই ViretaDev পরিচালিত হয়।
                        </p>
                    </div>

                    <!-- Tab Navigation. Buttons, not links: saas.js owns the switch
                         and writes ?tab=, so the open panel is shareable. -->
                    <div class="flex justify-center mb-10">
                        <div class="neu-glass-inset p-1.5 rounded-2xl inline-flex flex-wrap items-center justify-center gap-1.5" data-saas-tabs>
                            <?php foreach ($saas_panels as $saas_key => $saas_panel):
                                $saas_on = ($saas_key === $saas_tab);
                            ?>
                            <?php 
                                $saas_tab_style = $saas_on ? 'bg-indigo-600 text-white shadow-md' : 'text-neutral-600 hover:text-indigo-600 hover:bg-white/60';
                            ?>
                            <button type="button" data-saas-tab="<?= $saas_key ?>"
                                    aria-controls="<?= $saas_panel['id'] ?>" aria-selected="<?= $saas_on ? 'true' : 'false' ?>"
                                    class="px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold flex items-center gap-2 cursor-pointer transition-all <?= $saas_tab_style ?>">
                                <?= icon($saas_panel['icon'], 18, 'bold') ?>
                                <span><?= $saas_panel['label'] ?></span>
                            </button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- A tab a route does not select is still one click and one
                         query string away, which is how the JSX reached it. -->
                    <div class="mb-10 text-center text-xs text-neutral-500">
                        <span class="mr-2">সরাসরি খুলুন:</span>
                        <?php foreach ($saas_panels as $saas_key => $saas_panel): ?>
                        <a href="<?= $saas_self_url . $saas_key ?>"
                           class="ml-2 font-bold <?= $saas_key === $saas_tab ? 'text-indigo-600' : 'text-indigo-700 hover:underline' ?>"><?= $saas_panel['label'] ?></a>
                        <?php endforeach; ?>
                    </div>

                    <!-- Main Content Area -->
                    <div class="max-w-4xl mx-auto ht-card neu-glass-card p-6 sm:p-12 leading-relaxed text-neutral-700">

                        <!-- TAB 1: Privacy Policy -->
                        <div id="<?= $saas_panels['privacy']['id'] ?>" class="<?= $saas_panel_cls('privacy') ?>">
                            <div class="border-b border-neutral-200 pb-5">
                                <span class="text-xs font-bold text-indigo-600 uppercase tracking-wider">সর্বশেষ হালনাগাদ: জানুয়ারি ২০২৬</span>
                                <h2 class="text-2xl font-black text-neutral-900 mt-1">গোপনীয়তা ও ডাটা সুরক্ষা নীতিমালা (Privacy Policy)</h2>
                                <p class="text-xs text-neutral-500 mt-1">নাগরিক এবং ইউনিয়ন পরিষদের যাবতীয় তথ্যের শতভাগ গোপনীয়তা ও সুরক্ষা নিশ্চিতকরণ (৩০টি অনুচ্ছেদ)</p>
                            </div>

                            <div class="space-y-6 text-xs sm:text-sm text-neutral-600 leading-relaxed">
                                <div class="grid grid-cols-1 gap-4">
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">১. সংগৃহীত নাগরিক তথ্যের সুনির্দিষ্ট পরিধি</h4>
                                        <p>ইউনিয়ন পরিষদের কর নির্ধারণ, খানাভিত্তিক হোল্ডিং তালিকা প্রস্তুত এবং সনদ প্রদানের জন্য সফটওয়্যারে পরিবারের প্রধান ও সদস্যদের নাম, জাতীয় পরিচয়পত্র (NID) নম্বর, মোবাইল নম্বর, পেশা ও ঠিকানাসমূহ সংরক্ষিত হয়।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">২. এনআইডি ও জন্ম সনদের এনক্রিপ্টেড সংরক্ষণ</h4>
                                        <p>নাগরিকদের জাতীয় পরিচয়পত্র নম্বর ও জন্ম নিবন্ধন নম্বর ক্লাউড ডাটাবেজে এনক্রিপ্টেড অবস্থায় সংরক্ষিত থাকে এবং কোনো অননুমোদিত ব্যক্তির দৃষ্টিগোচরে আসে না।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">৩. মোবাইল নম্বর ও যোগাযোগ তথ্যের সীমাবদ্ধ ব্যবহার</h4>
                                        <p>সংগৃহীত মোবাইল নম্বর শুধুমাত্র কর পরিশোধের রসিদ নিশ্চিতকরণ, সনদ প্রস্তুতের আপডেট ও জরুরি সরকারি সতর্কবার্তা প্রেরণে ব্যবহৃত হবে।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">৪. পারিবারিক সম্পর্ক ও খানাপ্রধানের ডাটা সুরক্ষা</h4>
                                        <p>পারিবারিক ডাটাশিটে সংরক্ষিত নির্ভরশীল সদস্যদের বয়স, সম্পর্ক ও অন্যান্য স্পর্শকাতর তথ্যাবলী কঠোর গোপনীয়তায় ইউনিয়ন পরিষদের অভ্যন্তরীণ রেজিস্টারে সংরক্ষিত থাকবে।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">৫. টেন্যান্ট-লেভেল লজিক্যাল ডাটাবেজ পৃথকীকরণ (Tenant Isolation)</h4>
                                        <p>ViretaDev একটি সুরক্ষিত মাল্টি-টেন্যান্ট ক্লাউড আর্কিটেকচার। প্রতিটি ইউনিয়ন পরিষদের ডাটা আলাদা টেন্যান্ট আইডিতে সম্পূর্ণ পৃথক ও আইসোলেটেড থাকে, যাতে অন্য কোনো পরিষদ তা দেখতে না পারে।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">৬. বাণিজ্যিক উদ্দেশ্যে তথ্য বিক্রয় বা বিনিময় সম্পূর্ণ নিষিদ্ধ</h4>
                                        <p>নাগরিকদের কোনো ব্যক্তিগত বা অর্থনৈতিক তথ্য কোনো বাণিজ্যিক প্রতিষ্ঠান, বিপণন সংস্থা বা বিজ্ঞাপনদাতার কাছে বিক্রয়, ভাড়া বা হস্তান্তর করা সম্পূর্ণরূপে নিষিদ্ধ।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">৭. ক্রস-টেন্যান্ট কুয়েরি ব্লকিং ও নিরাপত্তা ফিল্টার</h4>
                                        <p>সফটওয়্যারের ব্যাকএন্ডে প্রতিটি ডাটাবেজ কুয়েরিতে অটোমেটিক টেন্যান্ট ফিল্টারিং বাধ্যতামূলক, ফলে একজন অ্যাডমিন অন্য ইউনিয়নের ডাটা কখনোই ফেচ করতে পারবে না।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">৮. রোল-ভিত্তিক অভ্যন্তরীণ অ্যাক্সেস কন্ট্রোল (RBAC)</h4>
                                        <p>ইউপি চেয়ারম্যান, সচিব, হিসাব সহকারী ও ইউডিসি উদ্যোক্তাদের দায়িত্ব অনুযায়ী আলাদা আলাদা পারমিশন থাকে, যা অননুমোদিত ডাটা অ্যাক্সেস রোধ করে।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">৯. পাসওয়ার্ড হ্যাশিং (Bcrypt) ও মাস্টার-কি নিরাপত্তা</h4>
                                        <p>ব্যবহারকারীর প্রতিটি পাসওয়ার্ড ইন্ডাস্ট্রি-স্ট্যান্ডার্ড Bcrypt অ্যালগরিদমে হ্যাশ করে জমা রাখা হয়; এমনকি সিস্টেম অ্যাডমিনও মূল পাসওয়ার্ড পড়তে পারে না।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">১০. সিস্টেম অ্যাডমিনিস্ট্রেটরদের ব্যাকএন্ড নিয়ন্ত্রণ</h4>
                                        <p>প্ল্যাটফর্মের সেন্ট্রাল সুপার অ্যাডমিন শুধুমাত্র সার্ভার রক্ষণাবেক্ষণ ও টেকনিক্যাল ট্রাবলশুটের জন্য লগ পর্যবেক্ষণ করতে পারবেন, নাগরিকের ব্যক্তিগত তথ্যে হস্তক্ষেপ করতে পারবেন না।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">১১. নাগরিকের নিজস্ব তথ্য সংশোধনের অধিকার</h4>
                                        <p>কোনো নাগরিকের তথ্যে ভুল থাকলে তিনি সংশ্লিষ্ট ইউনিয়ন পরিষদে প্রয়োজনীয় প্রমাণসহ আবেদন করে তার হোল্ডিং বা সনদের তথ্য সংশোধনের আবেদন করতে পারবেন।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">১২. ভুল বা বিকৃত রেকর্ড অপসারণের নিয়ম</h4>
                                        <p>ভুলবশত দ্বৈত বা বিকৃত এন্ট্রি হলে ইউপি সচিবের লিখিত অনুমোদন সাপেক্ষে অডিট লগ সংরক্ষণপূর্বক রেকর্ড পরিমার্জন করা যাবে।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">১৩. ইউপি সচিব ও চেয়ারম্যানের পূর্বানুমোদন ছাড়া রূপান্তর নিষিদ্ধ</h4>
                                        <p>ইউনিয়ন পরিষদের কোনো অফিশিয়াল রেকর্ড বা সনদ সংশ্লিষ্ট চেয়ারম্যান বা দায়িত্বপ্রাপ্ত কর্মকর্তার ডিজিটাল ছাড়পত্র ছাড়া পরিবর্তনযোগ্য নয়।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">১৪. ইউডিসি উদ্যোক্তাদের সীমিত ভিউ ও এন্ট্রি পারমিশন</h4>
                                        <p>ডিজিটাল সেন্টারের অপারেটররা শুধুমাত্র নতুন তথ্য এন্ট্রি ও নির্ধারিত সনদ প্রিন্ট করার অনুমতি পাবেন; মুছে ফেলা বা কর পরিবর্তন করার ক্ষমতা সংরক্ষিত থাকবে না।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">১৫. হোল্ডিং ট্যাক্স অ্যাসেসমেন্ট তথ্যের গোপনীয়তা</h4>
                                        <p>একটি পরিবারের নির্ধারিত কর মূল্যায়ন ও বাৎসরিক আয়ের তথ্য গোপনীয় দাপ্তরিক নথি হিসেবে সংরক্ষিত থাকবে এবং জনসমক্ষে প্রকাশ করা হবে না।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">১৬. কর বকেয়া ও ছাড়ের স্পর্শকাতর অডিট লগ</h4>
                                        <p>যদি কোনো করদাতার বকেয়া মওকুফ বা ছাড় দেওয়া হয়, তার সুনির্দিষ্ট আইনি কারণ এবং অনুমোদনকারীর পরিচয় অপরিবর্তনীয় অডিট লগে সংরক্ষিত থাকবে।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">১৭. ডিজিটাল সনদের কিউআর কোড ভেরিফিকেশনে তথ্যের পরিধি</h4>
                                        <p>সনদের কিউআর কোড স্ক্যান করলে শুধুমাত্র সনদটির মৌলিক সত্যতা, সনদের ক্রম ও ইস্যুর তারিখ প্রদর্শিত হবে; অপ্রয়োজনীয় ব্যক্তিগত তথ্য গোপন রাখা হবে।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">১৮. অপ্রাপ্তবয়স্ক ও এতিম নাগরিকদের তথ্যের বিশেষ প্রাতিষ্ঠানিক প্রটেকশন</h4>
                                        <p>ওয়ারিশান বা পারিবারিক ডাটাশিটে অপ্রাপ্তবয়স্কদের অভিভাবকত্ব ও সম্পত্তির তথ্যাবলী বিশেষ নিরাপত্তা ফ্ল্যাগের মাধ্যমে সুরক্ষিত রাখা হয়।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">১৯. ব্রাউজার সেশন, অথেনটিকেশন টোকেন ও কুকি সিকিউরিটি</h4>
                                        <p>অ্যাডমিন ও স্টাফ সেশন কুকিগুলোতে HttpOnly এবং Secure ফ্ল্যাগ কার্যকর থাকে, যাতে স্ক্রিপ্ট ইঞ্জেকশন বা হাইজ্যাকিং প্রতিহত হয়।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">২০. আইপি লগিং ও সন্দেহজনক কার্যকলাপ প্রতিরোধ</h4>
                                        <p>প্রতিটি লগইন চেষ্টা ও সংবেদনশীল লেনদেনের সময় আইপি ঠিকানা ও টাইমস্ট্যাম্প সংরক্ষিত হয়, যা সাইবার নিরাপত্তা বিশ্লেষণে সহায়তা করে।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">২১. ডাটাবেজ স্ন্যাপশট ও ব্যাকআপের এনক্রিপশন</h4>
                                        <p>প্রতিদিনের ক্লাউড ব্যাকআপ ডাম্প ফাইলগুলো AES-256 এনক্রিপশনের মাধ্যমে এনক্রিপ্ট করে ক্লাউড কোল্ড-স্টোরেজে সংরক্ষণ করা হয়।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">২২. মেয়াদোত্তীর্ণ বা নিষ্ক্রিয় ডাটার রিটেনশন পলিসি</h4>
                                        <p>কোনো নাগরিক স্থায়ীভাবে স্থানান্তরিত হলে বা ইউনিয়ন বিভক্ত হলে পূর্বের রেকর্ড সরকারি অডিট নির্দেশিকা অনুযায়ী আর্কাইভ আকারে সংরক্ষিত থাকবে।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">২৩. ডাটা ব্রিচ নোটিফিকেশন মেকানিজম</h4>
                                        <p>অনাকাঙ্ক্ষিত কোনো নিরাপত্তা বিঘ্ন ঘটলে প্ল্যাটফর্ম কর্তৃপক্ষ অনধিক ২৪ ঘণ্টার মধ্যে সংশ্লিষ্ট ইউনিয়ন পরিষদ কর্তৃপক্ষকে লিখিতভাবে অবহিত করবে।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">২৪. আদালত বা সরকারের আইন প্রয়োগকারী সংস্থার লিখিত আদেশ প্রটোকল</h4>
                                        <p>শুধুমাত্র দেশের উপযুক্ত আদালতের আনুষ্ঠানিক নির্দেশনা বা সরকারের প্রজ্ঞাপন সাপেক্ষে নির্দিষ্ট মামলার তথ্য আইন প্রয়োগকারী সংস্থাকে প্রদান করা হতে পারে।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">২৫. থার্ড-পার্টি পেমেন্ট গেটওয়ের ডাটা বিচ্ছিন্নতা</h4>
                                        <p>নাগরিক অনলাইনে কর বা ফি পরিশোধ করলে ব্যাংক বা মোবাইল ওয়ালেটের পিন/ওটিপি প্ল্যাটফর্মে কখনো সংরক্ষিত হয় না; লেনদেন সরাসরি গেটওয়ে স্তরে সম্পন্ন হয়।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">২৬. প্রিন্টেড মানি রসিদ ও সনদের সিকিউরিটি কোড</h4>
                                        <p>প্রিন্টকৃত প্রতিটি রসিদে স্বয়ংক্রিয় ইউনিক ট্রানজ্যাকশন আইডি ও জেনারেট টাইম থাকে, যা বাইরে ফটোকপি জালিয়াতি রোধ করে।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">২৭. ডাটা স্থানান্তর বা মাইগ্রেশনে কঠোর গোপনীয়তা</h4>
                                        <p>নতুন ইউনিয়ন পরিষদ যুক্ত হওয়ার সময় এক্সেল শিট থেকে বাল্ক ডাটা আমদানির ক্ষেত্রে সম্পূর্ণ সুরক্ষিত চ্যানেলে ডাটা প্রসেস করা হয়।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">২৮. ছবি ও ডিজিটাল স্বাক্ষরের সুরক্ষিত পাথ স্টোরেজ</h4>
                                        <p>চেয়ারম্যানের স্বাক্ষর ও লোগো সুরক্ষিত আপলোড ডিরেক্টরিতে থাকে এবং সরাসরি পাবলিক ব্রাউজিং থেকে প্রোটেক্টেড থাকে।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">২৯. ইউপি দায়িত্ব হস্তান্তরকালে পূর্বতন ডাটা হ্যান্ডওভার প্রটোকল</h4>
                                        <p>নির্বাচনের পর নতুন পরিষদ গঠিত হলে উপজেলা প্রশাসনের সমন্বয়ে পূর্বতন ডাটা অক্ষত রেখে প্রাতিষ্ঠানিক পাসওয়ার্ড হস্তান্তর নিশ্চিত করা হয়।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">৩০. প্রাইভেসি পলিসি পরিবর্তনের পূর্ব নোটিশ ও সম্মতি</h4>
                                        <p>নীতিমালায় কোনো পরিবর্তন আনা হলে ন্যূনতম ৩০ দিন পূর্বে প্রতিটি ইউনিয়ন পরিষদের ড্যাশবোর্ডে নোটিফিকেশন জারির মাধ্যমে অবহিত করা হবে।</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 2: Terms of Service -->
                        <div id="<?= $saas_panels['terms']['id'] ?>" class="<?= $saas_panel_cls('terms') ?>">
                            <div class="border-b border-neutral-200 pb-5">
                                <span class="text-xs font-bold text-indigo-600 uppercase tracking-wider">অফিশিয়াল সার্ভিস এগ্রিমেন্ট</span>
                                <h2 class="text-2xl font-black text-neutral-900 mt-1">সেবার শর্তাবলী (Terms of Service)</h2>
                                <p class="text-xs text-neutral-500 mt-1">সফটওয়্যার ব্যবহারের আইনি ও প্রশাসনিক বিধিমালা (৩০টি সুস্পষ্ট ধারা)</p>
                            </div>

                            <div class="space-y-4 text-xs sm:text-sm text-neutral-600 leading-relaxed">
                                <div class="grid grid-cols-1 gap-4">
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">১. চুক্তির বৈধতা ও আইনগত এখতিয়ার</h4>
                                        <p>এই শর্তাবলী ইউনিয়ন পরিষদ এবং ViretaDev ক্লাউড প্ল্যাটফর্মের মধ্যে একটি আইনি সার্ভিস এগ্রিমেন্ট হিসেবে গণ্য হবে এবং বাংলাদেশ স্থানীয় সরকার আইনের অধীন পরিচালিত হবে।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">২. প্রাতিষ্ঠানিক লাইসেন্সের পরিধি</h4>
                                        <p>সাবস্ক্রিপশনের অধীনে সংশ্লিষ্ট ইউনিয়ন পরিষদকে সফটওয়্যারটি অ-হস্তান্তরযোগ্য ও অ-একচেটিয়াভাবে ব্যবহারের প্রাতিষ্ঠানিক ক্লাউড অনুমতি প্রদান করা হয়।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">৩. ইউপি চেয়ারম্যান ও সচিবের প্রাতিষ্ঠানিক অনুমোদন</h4>
                                        <p>প্ল্যাটফর্মে অ্যাকাউন্ট খোলা ও সক্রিয়করণের জন্য ইউনিয়ন পরিষদের সিলযুক্ত অফিশিয়াল লেটারহেডে লিখিত আবেদন বা সম্মতিপত্র আবশ্যক।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">৪. ক্রেডেনশিয়াল ও পাসওয়ার্ডের সুরক্ষা</h4>
                                        <p>ইউজারনেম ও পাসওয়ার্ডের গোপনীয়তা রক্ষার সম্পূর্ণ দায়দায়িত্ব ইউনিয়ন পরিষদের। পাসওয়ার্ড ফাঁসের কারণে সৃষ্ট যেকোনো সমস্যার জন্য পরিষদ কর্তৃপক্ষ দায়ী থাকবে।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">৫. অননুমোদিত ইউজার সৃষ্টির নিষেধাজ্ঞা</h4>
                                        <p>ইউনিয়ন পরিষদের বাইরের কোনো ব্যক্তি বা অননুমোদিত এজেন্টকে ড্যাশবোর্ডের অ্যাক্সেস প্রদান করা চুক্তিভঙ্গ হিসেবে গণ্য হবে।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">৬. সঠিক ও নির্ভরযোগ্য নাগরিক ডাটা এন্ট্রি</h4>
                                        <p>হোল্ডিং করদাতা এবং সনদের তথ্যাদি সঠিক ও জাতীয় ডাটাবেজের সাথে সামঞ্জস্যপূর্ণভাবে এন্ট্রি করার প্রশাসনিক দায়িত্ব ইউনিয়ন পরিষদের।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">৭. সনদ ইস্যুকরণে যথাযথ তদন্ত ও যাচাই</h4>
                                        <p>নাগরিকত্ব, চারিত্রিক ও ওয়ারিশান সনদ ইস্যু করার পূর্বে স্থানীয় ইউপি সদস্যের তদন্ত ও সঠিকতা নিশ্চিত করে তবেই সনদ অনুমোদন করতে হবে।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">৮. ভুয়া বা জাল সনদ তৈরিতে তাৎক্ষণিক আইনি ব্যবস্থা</h4>
                                        <p>প্ল্যাটফর্ম ব্যবহার করে উদ্দেশ্যমূলকভাবে জাল বা ভুয়া তথ্য দিয়ে সনদ তৈরির প্রমাণ পাওয়া গেলে সংশ্লিষ্ট অপারেটরের অ্যাকাউন্ট বাতিল ও আইনি পদক্ষেপ নেওয়া হবে।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">৯. সরকারি কর নির্ধারণ বিধিমালা অনুসরণ</h4>
                                        <p>হোল্ডিং ট্যাক্স অ্যাসেসমেন্ট করার সময় স্থানীয় সরকার পল্লী উন্নয়ন ও সমবায় মন্ত্রণালয় কর্তৃক প্রণীত মডেল ট্যাক্স সিডিউল অনুসরণ করতে হবে।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">১০. কর ছাড় (Discount) সংক্রান্ত নিয়মের অডিট ট্রেইল</h4>
                                        <p>কর ছাড়ে নিয়মের ব্যত্যয় ঠেকাতে সিস্টেমে ছাড়ের কারণ এবং উপযুক্ত অনুমোদনের তথ্য নথিভুক্ত থাকতে হবে, যা সরকারি অডিটে প্রদর্শিত হবে।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">১১. সফটওয়্যার প্ল্যাটফর্মের মেধা-সম্পত্তি অধিকার</h4>
                                        <p>ViretaDev প্ল্যাটফর্মের সোর্স কোড, ডিজাইন, অ্যালগরিদম ও ইউআই ইন্টারফেসের সমস্ত স্বত্বাধিকার ViretaDev Technologies-এর নিজস্ব মেধা-সম্পদ।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">১২. রিভার্স ইঞ্জিনিয়ারিং ও কোড চুরির আইনি দণ্ডনীয়তা</h4>
                                        <p>সফটওয়্যারটির কোনো অংশ কপি, রিভার্স ইঞ্জিনিয়ারিং বা ক্লোন করার চেষ্টা কপিরাইট আইন অনুযায়ী শাস্তিযোগ্য অপরাধ।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">১৩. সাইবার নিরাপত্তা ও সিস্টেম আক্রমণ নিষেধাজ্ঞা</h4>
                                        <p>কোনো ব্যবহারকারী প্ল্যাটফর্মের সার্ভারে ম্যালওয়্যার, স্ক্র্যাপার বা অননুমোদিত অটোমেটেড স্ক্রিপ্ট চালাতে পারবে না।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">১৪. গ্রহণযোগ্য ব্যবহার নীতিমালা (AUP)</h4>
                                        <p>প্ল্যাটফর্মটি শুধুমাত্র ইউনিয়ন পরিষদের বৈধ প্রশাসনিক, নাগরিক সেবা ও রাজস্ব আদায় সংক্রান্ত দাপ্তরিক কাজেই ব্যবহার করা যাবে।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">১৫. বাল্ক এসএমএস অপব্যবহার বা স্প্যামিংয়ের নিষেধাজ্ঞা</h4>
                                        <p>নাগরিকদের মোবাইল নম্বরে কোনো প্রকার রাজনৈতিক প্রচারণা, ব্যক্তিগত বা বাণিজ্যিক স্প্যাম মেসেজ পাঠানো কঠোরভাবে নিষিদ্ধ।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">১৬. বাৎসরিক সাবস্ক্রিপশন ফি পরিশোধের নিয়ম</h4>
                                        <p>নির্ধারিত প্যাকেজ অনুযায়ী বাৎসরিক সাবস্ক্রিপশন ফি ইনভয়েস প্রাপ্তির ৩০ দিনের মধ্যে ব্যাংকিং বা ডিজিটাল মাধ্যমে পরিশোধ করতে হবে।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">১৭. ফি বকেয়া থাকলে নোটিশ সাপেক্ষে সেবা স্থগিতকরণ</h4>
                                        <p>পরপর দুই দফা লিখিত নোটিশ প্রদানের পরও সাবস্ক্রিপশন ফি বকেয়া থাকলে সফটওয়্যার সাময়িকভাবে ভিউ-অনলি মোডে চলে যেতে পারে।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">১৮. ডাটা এক্সপোর্ট ও ব্যাকআপ নেওয়ার নিয়মিত দায়িত্ব</h4>
                                        <p>ইউনিয়ন পরিষদ যেকোনো সময় তাদের হোল্ডিং ও পরিবারের ডাটা এক্সেল/সিএসভি আকারে এক্সপোর্ট করে লোকাল ফাইলে ব্যাকআপ রাখতে পারবে।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">১৯. সেবা আপগ্রেড ও নতুন ফিচার সংযোজনের নীতিমালা</h4>
                                        <p>সরকারি কোনো নতুন গেজেট বা ফরম্যাট পরিবর্তন হলে ViretaDev স্বয়ংক্রিয়ভাবে কোনো অতিরিক্ত ফি ছাড়াই সিস্টেমে তা আপডেট করে দেবে।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">২০. ইন্টারনেট ও লোকাল ডিভাইসের দায়মুক্তি</h4>
                                        <p>ইউনিয়ন পরিষদের লোকাল কম্পিউটার নষ্ট হওয়া বা স্থানীয় ইন্টারনেট সংযোগ বিঘ্নিত হওয়ার জন্য প্ল্যাটফর্ম দায়ী থাকবে না।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">২১. উদ্যোক্তা পরিবর্তন ও পারমিশন রিসেট</h4>
                                        <p>ইউডিসি উদ্যোক্তা বা কম্পিউটার অপারেটর পরিবর্তিত হলে পরিষদ তাৎক্ষণিকভাবে আগের ইউজারের পাসওয়ার্ড নিষ্ক্রিয় করবে।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">২২. প্রিন্ট রসিদ ও ব্যাংকিং হিসাব মেলানোর বাধ্যবাধকতা</h4>
                                        <p>আদায়কৃত করের অর্থের সাথে ব্যাংকে জমাকৃত ক্যাশ মেমো নিয়মিত মিলিয়ে নেওয়ার দায়িত্ব ইউনিয়ন পরিষদ হিসাব শাখার।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">২৩. ট্রেড লাইসেন্স ইস্যুকরণে প্রয়োজনীয় কাগজের সত্যতা যাচাই</h4>
                                        <p>ব্যবসায়িক প্রতিষ্ঠানের ট্রেড লাইসেন্স ইস্যুর পূর্বে জমির মালিকানা বা ভাড়াচুক্তি ও এনআইডির অনুলিপি যাচাইয়ের নিয়ম পালন করতে হবে।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">২৪. ইউপি নির্বাচন পরবর্তী হ্যান্ডওভার সুরক্ষা</h4>
                                        <p>ইউপি নির্বাচন শেষে বিজয়ী নতুন চেয়ারম্যান ও প্যানেলের কাছে দাপ্তরিক দায়িত্ব হস্তান্তরের জন্য কেন্দ্রীয় সুপার অ্যাডমিন সহায়তা প্রদান করবে।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">২৫. বার্ষিক নিরীক্ষা (Audit) রিপোর্টে গ্রহণযোগ্যতা</h4>
                                        <p>সফটওয়্যার থেকে জেনারেটকৃত ট্যাক্স ও সনদ রেজিস্টার সিএ ফার্ম ও সরকারি নিরীক্ষা বিভাগের অডিটে আইনগত প্রমাণ হিসেবে উপস্থাপিত হতে পারবে।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">২৬. জাতীয় জরুরি অবস্থা ও ফোর্সম্যাজিউর (Force Majeure)</h4>
                                        <p>প্রাকৃতিক দুর্যোগ, জাতীয় যুদ্ধাবস্থা বা বৈশ্বিক ইন্টারনেট ব্ল্যাকআউটের মতো পরিস্থিতিতে উদ্ভূত সাময়িক বিঘ্নের ক্ষেত্রে দায়মুক্তি প্রযোজ্য।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">২৭. শর্তাবলী লঙ্ঘনে অ্যাকাউন্ট সাসপেনশন</h4>
                                        <p>কোনো ইউজার ইচ্ছাকৃতভাবে ক্ষতিকর সাইবার কর্মকাণ্ডে লিপ্ত হলে পূর্ব নোটিশ ছাড়াই তার পারমিশন বাতিল করার অধিকার সংরক্ষিত।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">২৮. চুক্তি সমাপ্তি (Termination) ও ডাটা রিলিজ</h4>
                                        <p>চুক্তি সমাপ্তির আবেদন করলে ইউনিয়ন পরিষদকে তাদের সম্পূর্ণ ডাটাবেজ এক্সেল/এসকিউএল ডাম্প আকারে বুঝিয়ে দেওয়া হবে।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">২৯. বিরোধ নিষ্পত্তি ও স্থানীয় সরকার আদালতের এখতিয়ার</h4>
                                        <p>যেকোনো আইনি বিরোধ প্রথমত আলোচনার মাধ্যমে এবং পরবর্তীতে বাংলাদেশ স্থানীয় সরকার ট্রাইব্যুনাল বা দেওয়ানি আদালতের মাধ্যমে নিষ্পত্তি হবে।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">৩০. সেবার শর্তাবলীর সংশোধনী কার্যকর হওয়া</h4>
                                        <p>সরকারি নীতিমালার প্রেক্ষিতে শর্তাবলীতে কোনো পরিবর্তন আনা হলে তা প্ল্যাটফর্মের ওয়েবসাইটে প্রকাশের সাথে সাথে কার্যকর বলে গণ্য হবে।</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 3: Security & SLA -->
                        <div id="<?= $saas_panels['security']['id'] ?>" class="<?= $saas_panel_cls('security') ?>">
                            <div class="border-b border-neutral-200 pb-5">
                                <span class="text-xs font-bold text-indigo-600 uppercase tracking-wider">সার্ভিস লেভেল এগ্রিমেন্ট (SLA)</span>
                                <h2 class="text-2xl font-black text-neutral-900 mt-1">ডাটা নিরাপত্তা ও ক্লাউড আপটাইম গ্যারান্টি (SLA)</h2>
                                <p class="text-xs text-neutral-500 mt-1">২৪/৭ অনলাইন প্রাপ্যতা, ব্যাকআপ ও সার্ভার সুরক্ষা নিশ্চয়তা (৩০টি সুনির্দিষ্ট পয়েন্ট)</p>
                            </div>

                            <div class="space-y-4 text-xs sm:text-sm text-neutral-600 leading-relaxed">
                                <div class="grid grid-cols-1 gap-4">
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">১. ৯৯.৯% ক্লাউড সার্ভার আপটাইম প্রতিশ্রুতি (Monthly SLA)</h4>
                                        <p>প্রতি ক্যালেন্ডার মাসে ৯৯.৯% নিরবচ্ছিন্ন সার্ভার প্রাপ্যতা নিশ্চিত করা হয়, যাতে নাগরিক সেবা কখনো বন্ধ না থাকে।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">২. ২৪/৭ রিয়েল-টাইম সার্ভার মনিটরিং ও হেলথ চেক</h4>
                                        <p>অটোমেটেড হার্টবিট ও রিসোর্স মনিটর প্রতি ৬০ সেকেন্ড পর পর সিপিইউ, র‍্যাম ও ডাটাবেজ স্বাস্থ্য পর্যবেক্ষণ করে।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">৩. টিয়ার-৩ ও টিয়ার-৪ সার্টিফাইড ডেটাসেন্টার হোস্টিং</h4>
                                        <p>সার্ভারসমূহ আন্তর্জাতিক মানের টিয়ার-৩/৪ ডেটাসেন্টারে সুরক্ষিত, যেখানে সার্বক্ষণিক পাওয়ার ও কুলিং ব্যাকআপ বিদ্যমান।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">৪. এন্ড-টু-এন্ড TLS 1.3 ও ২৫৬-বিট SSL এনক্রিপশন</h4>
                                        <p>নাগরিক ও অ্যাডমিনের সমস্ত ব্রাউজার ট্র্যাফিক লেটেস্ট টিএলএস ও ২৫৬-বিট এনক্রিপশন দ্বারা সাইবার আক্রমণ থেকে সুরক্ষিত।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">৫. ডাটাবেজ স্তরে ডাটা-অ্যাট-রেস্ট এনক্রিপশন</h4>
                                        <p>পোস্টগ্রেস সার্ভারের ডিস্ক ভলিউম সম্পূর্ণ এনক্রিপ্টেড থাকে, ফলে হার্ডওয়্যার চুরি হলেও কোনো ডাটা পড়া সম্ভব নয়।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">৬. দৈনিক পূর্ণাঙ্গ স্বয়ংক্রিয় ক্লাউড ব্যাকআপ</h4>
                                        <p>প্রতিদিন মধ্যরাতে প্রতিটি ইউনিয়ন পরিষদের যাবতীয় ডাটাবেজ টেবিলের পূর্ণ স্ন্যাপশট ব্যাকআপ নেওয়া হয়।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">৭. অফসাইট সেকেন্ডারি সার্ভার রেপ্লিকেশন</h4>
                                        <p>মূল সার্ভারের বাইরে একটি ভিন্ন ভৌগোলিক অবস্থানে অফসাইট ব্যাকআপ সিঙ্ক করা থাকে, যা অগ্নিকাণ্ড বা দুর্যোগে সুরক্ষা দেয়।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">৮. পয়েন্ট-ইন-টাইম রিকভারি (PITR) সক্ষমতা</h4>
                                        <p>যেকোনো অনাকাঙ্ক্ষিত দুর্ঘটনার ক্ষেত্রে বিগত ৩০ দিনের যেকোনো সুনির্দিষ্ট মুহূর্তের ডাটা হুবহু ফিরিয়ে আনার সক্ষমতা রয়েছে।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">৯. কঠোর মাল্টি-টেন্যান্ট আইসোলেশন প্রটোকল</h4>
                                        <p>মেমোরি ও প্রসেস লেভেলে প্রতিটি ইউনিয়নের রিকোয়েস্ট পৃথক পাইপলাইনে এক্সিকিউট হয়; নো-লিক গ্যারান্টি বিদ্যমান।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">১০. এসকিউএল ইনজেকশন (SQLi) প্রতিরোধ ব্যবস্থা</h4>
                                        <p>কোড লেভেলে পিএইচপি পিডিও ও সিআই৩ কোয়েরি বাইন্ডিং ব্যবহার করা হয়েছে, যা সকল ধরনের এসকিউএল ইনজেকশন ব্লক করে।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">১১. ক্রস-সাইট স্ক্রিপ্টিং (XSS) ফিল্টারিং</h4>
                                        <p>ইনপুট ও আউটপুট ফিল্টারে প্রতিটি ব্যবহারকারী ডাটা HTML5 নিরাপদ স্যানিটাইজেশন ও htmlspecialchars দিয়ে রেন্ডার হয়।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">১২. ব্রুট-ফোর্স অ্যাটাক ব্লকিং ও ইন্টেলিজেন্ট থ্রোটলিং</h4>
                                        <p>একটানা ভুল পাসওয়ার্ড চেষ্টা করলে স্বয়ংক্রিয়ভাবে সংশ্লিষ্ট আইপি নির্দিষ্ট সময়ের জন্য লক হয়ে যায়।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">১৩. অটোমেটেড সিআরএফ (CSRF) প্রটেকশন</h4>
                                        <p>প্রতিটি ফর্ম সাবমিশনে ডায়নামিক সিআরএফ টোকেন ব্যবহৃত হয়, যা অন্য ওয়েবসাইট থেকে অনাকাঙ্ক্ষিত ফর্ম সাবমিশন রুখে দেয়।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">১৪. ক্লাউড ফায়ারওয়াল ও ডব্লিউএএফ (WAF) লেয়ার</h4>
                                        <p>ওয়েব অ্যাপ্লিকেশন ফায়ারওয়াল দূষিত বট, স্পাইডার ও ম্যালিশিয়াস ট্র্যাফিক সার্ভারে ঢোকার আগেই ড্রপ করে।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">১৫. নিয়মিত সিকিউরিটি প্যাচ ও কার্নেল আপডেট</h4>
                                        <p>অপারেটিং সিস্টেম ও পিএইচপি ইঞ্জিনে জিরো-ডে বা নিরাপত্তা ত্রুটি প্রকাশ পাওয়া মাত্রই তাৎক্ষণিক প্যাচ প্রয়োগ করা হয়।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">১৬. আপলোড মিডিয়া ও ফাইলের কঠোর স্যান্ডবক্সিং</h4>
                                        <p>আপলোডকৃত ছবি বা ডকুমেন্ট ডিরেক্টরিতে পিএইচপি স্ক্রিপ্ট এক্সিকিউশন সম্পূর্ণ ব্লক রাখা হয় (AddHandler Deny)।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">১৭. নির্ধারিত রক্ষণাবেক্ষণ সময়সূচি ও পূর্ব নোটিশ</h4>
                                        <p>সার্ভার আপগ্রেডের জন্য কোনো ডাউনটাইম প্রয়োজন হলে কমপক্ষে ৪৮ ঘণ্টা পূর্বে ইউনিয়নসমূহকে অবহিত করা হবে।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">১৮. অফ-পিক আওয়ারে মেইনটেন্যান্স উইন্ডো</h4>
                                        <p>সব ধরনের শিডিউলড মেইনটেন্যান্স সাধারণ অফিস সময়ের বাইরে রাত ১টা থেকে ভোর ৪টার মধ্যে সম্পন্ন করা হয়।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">১৯. রিকভারি টাইম অবজেক্টিভ (RTO) অনধিক ৪ ঘণ্টা</h4>
                                        <p>যেকোনো বিপর্যয়কালীন পরিস্থিতিতে সম্পূর্ণ সিস্টেম সর্বোচ্চ ৪ ঘণ্টার মধ্যে পুনরায় লাইভ করার নিশ্চয়তা।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">২০. রিকভারি পয়েন্ট অবজেক্টিভ (RPO) অনধিক ১৫ মিনিট</h4>
                                        <p>রিয়েল-টাইম ট্রানজ্যাকশন লগের কারণে আকস্মিক সার্ভার ক্র্যাশেও ১৫ মিনিটের বেশি তথ্য হারানোর কোনো সুযোগ নেই।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">২১. ক্লাউড হার্ডওয়্যার রিডানড্যান্সি ও ফেইলওভার</h4>
                                        <p>একটি সার্ভার নোডে কারিগরি সমস্যা দেখা দিলে সেকেন্ডারি অটোমেটেড নোড ট্র্যাফিক হ্যান্ডেল করতে প্রস্তুত থাকে।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">২২. ডিডস (DDoS) আক্রমণ প্রতিরোধ ও ট্র্যাফিক স্ক্রাবিং</h4>
                                        <p>অস্বাভাবিক মাত্রার ট্র্যাফিক আক্রমণ প্রতিহত করতে মাল্টি-গিগাবিট ক্লাউড ডিফেন্স ফিল্টার সার্বক্ষণিক সচল থাকে।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">২৩. অডিট লগ ও অ্যাক্টিভিটি ট্র্যাকিংয়ের অপরিবর্তনীয়তা</h4>
                                        <p>অ্যাডমিনের কোনো অ্যাকশন, কর হ্রাস বা সনদ ইস্যুর লগ ডেটাবেজে স্থায়ীভাবে রেকর্ড থাকে এবং তা এডিট করা যায় না।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">২৪. জরুরি সাপোর্ট রেসপন্স টাইম (SLA Priority 1)</h4>
                                        <p>সার্ভার ডাউন বা সিস্টেম ক্র্যাশ সংক্রান্ত জরুরি টিকিটের ক্ষেত্রে অনধিক ৩০-৬০ মিনিটের মধ্যে টেকনিক্যাল রেসপন্স প্রদান।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">২৫. সাধারণ টেকনিক্যাল কোয়েরি সমাধান (SLA Priority 2)</h4>
                                        <p>সাধারণ ফিচার বা প্রিন্টিং সংক্রান্ত টিকিট সর্বোচ্চ ৬ কর্মঘণ্টার মধ্যে সমাধানের প্রতিশ্রুতি।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">২৬. পোস্টগ্রেস এসিড (ACID) ট্রানজ্যাকশন ইন্টিগ্রিটি</h4>
                                        <p>ট্যাক্স পরিশোধ ও সনদ তৈরিতে ডাটাবেজ ট্রানজ্যাকশন রোলব্যাক নিশ্চিত করে, যাতে ডাটা অসম্পূর্ণ অবস্থায় জমা না হয়।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">২৭. অন-ডিমান্ড সেলফ ডাটা ব্যাকআপ ডাউনলোড</h4>
                                        <p>ইউনিয়ন পরিষদ প্রশাসন যেকোনো সময় তাদের ড্যাশবোর্ড থেকে তাৎক্ষণিক সম্পূর্ণ ডাটার কপি ডাউনলোড করে নিতে পারে।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">২৮. দুর্যোগ ব্যবস্থাপনা ও ডিজাস্টার রিকভারি প্ল্যান</h4>
                                        <p>বন্যা, সাইক্লোন বা বিদ্যুৎ বিপর্যয়েও ক্লাউড অবকাঠামো নিরবচ্ছিন্ন রেখে রিমোট অ্যাক্সেস নিশ্চিত করার প্রটোকল।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">২৯. পর্যায়ক্রমিক সাইবার সিকিউরিটি অডিট</h4>
                                        <p>প্রতি ত্রৈমাসিকে অভিজ্ঞ সাইবার বিশেষজ্ঞদের দ্বারা কোড অডিট ও ভালনারেবিলিটি স্ক্যানিং সম্পন্ন করা হয়।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">৩০. এসএলএ লঙ্ঘনে সার্ভিস ক্রেডিট প্রদান</h4>
                                        <p>যদি কোনো মাসে আপটাইম ৯৯.৯% এর নিচে নেমে যায়, তবে সাবস্ক্রিপশন মেয়াদে আনুপাতিক অতিরিক্ত ফ্রি দিন যুক্ত করা হবে।</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 4: Billing & Refund -->
                        <div id="<?= $saas_panels['billing']['id'] ?>" class="<?= $saas_panel_cls('billing') ?>">
                            <div class="border-b border-neutral-200 pb-5">
                                <span class="text-xs font-bold text-indigo-600 uppercase tracking-wider">বিলিং প্রসিডিউর</span>
                                <h2 class="text-2xl font-black text-neutral-900 mt-1">সাবস্ক্রিপশন, নবায়ন ও রিফান্ড পলিসি</h2>
                                <p class="text-xs text-neutral-500 mt-1">স্বচ্ছ বাৎসরিক ফি, ট্রায়াল ও সরকারি বিল পরিশোধ নিয়মাবলী (৩০টি ধারা)</p>
                            </div>

                            <div class="space-y-4 text-xs sm:text-sm text-neutral-600 leading-relaxed">
                                <div class="grid grid-cols-1 gap-4">
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">১. সাবস্ক্রিপশন প্যাকেজের মেয়াদ নির্ধারণ</h4>
                                        <p>ইউনিয়ন পরিষদসমূহ সাধারণত এক বছর মেয়াদে সাবস্ক্রিপশন গ্রহণ করে; এছাড়া আলোচনা সাপেক্ষে ৩ বা ৫ বছরের দীর্ঘমেয়াদি চুক্তি করা যায়।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">২. বাৎসরিক প্যাকেজের নির্ধারিত ও স্বচ্ছ মূল্য</h4>
                                        <p>প্ল্যাটফর্মে প্রদর্শিত মূল্যের বাইরে কোনো অদৃশ্য বা লুকানো চার্জ নেই; সম্পূর্ণ সেবামূল্য পরিষ্কারভাবে ইনভয়েসে উল্লেখ থাকে।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">৩. কোনো লুকানো মেইনটেন্যান্স বা হোস্টিং ফি নেই</h4>
                                        <p>ক্লাউড সার্ভার, ব্যাকআপ, এসএসএল সার্টিফিকেট ও নিয়মিত সফটওয়্যার আপডেট বাৎসরিক প্যাকেজ ফির ভেতরেই অন্তর্ভুক্ত।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">৪. ৩০ দিনের ফ্রি টেস্ট ডেমো ও অনবোর্ডিং ট্রায়াল</h4>
                                        <p>প্রতিটি নতুন ইউনিয়ন পরিষদ আনুষ্ঠানিকভাবে চুক্তি স্বাক্ষরের পূর্বে ৩০ দিনের জন্য সম্পূর্ণ ফিচারসহ ফ্রি ট্রায়াল ব্যবহার করতে পারবে।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">৫. প্যাকেজ অনুযায়ী ইউজার ও হোল্ডিং সীমার স্পষ্টতা</h4>
                                        <p>স্ট্যান্ডার্ড বা প্রিমিয়াম প্যাকেজে উল্লেখিত ইউজার সংখ্যা ও হোল্ডিং ডাটা এন্ট্রি সীমা স্বচ্ছভাবে ড্যাশবোর্ডে প্রদর্শিত থাকে।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">৬. সাবস্ক্রিপশন সক্রিয়করণের সময়সীমা</h4>
                                        <p>পেমেন্ট নিশ্চিত বা সরকারি ওয়ার্ক অর্ডার প্রাপ্তির সর্বোচ্চ ১ ঘণ্টার মধ্যে ইউনিয়ন পরিষদের পোর্টাল ও অ্যাডমিন অ্যাক্সেস সক্রিয় হয়।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">৭. গ্রহণযোগ্য সরকারি বিল পরিশোধের মাধ্যমসমূহ</h4>
                                        <p>ইউনিয়ন পরিষদ তাদের ব্যাংক একাউন্ট থেকে একাউন্টস পেয়ী চেক, বিএফটিএন (BEFTN), আরটিজিএস অথবা অনলাইন চালানের মাধ্যমে ফি প্রদান করতে পারে।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">৮. অফিশিয়াল সিলযুক্ত চালান ও ভ্যাট ইনভয়েস</h4>
                                        <p>প্রতিটি পেমেন্টের বিপরীতে সরকারি নিরীক্ষায় প্রযোজ্য অফিশিয়াল মানি রসিদ ও ভ্যাট চালান সরবরাহ করা হয়।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">৯. নবায়ন ফি প্রদানের সময়সীমা ও অ্যালার্ট</h4>
                                        <p>সাবস্ক্রিপশনের মেয়াদ শেষ হওয়ার ৩০ দিন, ১৫ দিন ও ৭ দিন পূর্বে ইউপি চেয়ারম্যান ও সচিবের দাপ্তরিক ইমেইলে স্বয়ংক্রিয় নোটিশ পাঠানো হয়।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">১০. মেয়াদোত্তীর্ণের পর ৩০ দিনের গ্রেস পিরিয়ড (Grace Period)</h4>
                                        <p>মেয়াদ শেষ হলেও ইউনিয়ন পরিষদের সরকারি কাজ যেন বন্ধ না হয়, সেজন্য অতিরিক্ত ৩০ দিন সম্পূর্ণ সেবা চালু থাকে।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">১১. গ্রেস পিরিয়ডে সংরক্ষিত ডাটার শতভাগ অক্ষততা</h4>
                                        <p>গ্রেস পিরিয়ড চলাকালীন কোনো সংরক্ষিত ডাটা মুছে ফেলা বা পরিবর্তন করা হবে না; ডাটা সম্পূর্ণ সুরক্ষিত থাকবে।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">১২. গ্রেস পিরিয়ড পরবর্তী ভিউ-অনলি স্ট্যাটাস</h4>
                                        <p>গ্রেস পিরিয়ড শেষ হওয়ার পর পেমেন্ট না হওয়া পর্যন্ত নতুন এন্ট্রি সাময়িক স্থগিত থাকলেও পূর্বের ডাটা ভিউ ও ডাউনলোড করা যাবে।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">১৩. ১৪ দিনের শতভাগ মানি-ব্যাক রিফান্ড গ্যারান্টি</h4>
                                        <p>প্যাকেজ ক্রয়ের প্রথম ১৪ দিনের মধ্যে সফটওয়্যারের কার্যকারিতায় অসন্তুষ্ট হলে কোনো প্রশ্ন ছাড়াই সম্পূর্ণ অর্থ রিফান্ড করা হবে।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">১৪. রিফান্ড পাওয়ার ক্ষেত্রে গ্রহণযোগ্য কারণসমূহ</h4>
                                        <p>সিস্টেমের বড় কোনো টেকনিক্যাল ত্রুটি যা আমাদের টিম ৭ দিনেও সমাধান করতে ব্যর্থ হয়েছে, তা রিফান্ডের উপযুক্ত কারণ হিসেবে গণ্য।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">১৫. প্রশাসনিক পরিবর্তনের ক্ষেত্রে রিফান্ড সীমাবদ্ধতা</h4>
                                        <p>ইউনিয়ন পরিষদের অভ্যন্তরীণ রাজনীতি বা নির্বাচনের কারণে কর্মকর্তা পরিবর্তন হলে ইতোমধ্যে ব্যবহৃত মেয়াদের ফি রিফান্ডযোগ্য হবে না।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">১৬. অন-সাইট প্রশিক্ষণ ও ম্যানুয়াল ডাটা এন্ট্রি ফি</h4>
                                        <p>ইঞ্জিনিয়ারদের সাইটে গিয়ে সরাসরি প্রশিক্ষণ ও পুরোনো খাতা ম্যানুয়াল ডাটা এন্ট্রি সেবা দেওয়া হলে সেই অংশটি অফেরতযোগ্য হিসেবে বিবেচিত হবে।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">১৭. রিফান্ড আবেদন প্রক্রিয়া ও চিঠির বাধ্যবাধকতা</h4>
                                        <p>রিফান্ডের জন্য ইউপি চেয়ারম্যান বা সচিবের স্বাক্ষরযুক্ত দাপ্তরিক আবেদনপত্র সাপোর্ট ডেস্কে দাখিল করতে হবে।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">১৮. রিফান্ড প্রসেসিংয়ের সময়সীমা (সর্বোচ্চ ৭ কার্যদিবস)</h4>
                                        <p>আবেদন অনুমোদিত হওয়ার সর্বোচ্চ ৭ কার্যদিবসের মধ্যে ইউনিয়ন পরিষদের অফিশিয়াল ব্যাংক একাউন্টে অর্থ স্থানান্তর সম্পন্ন হবে।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">১৯. প্যাকেজ আপগ্রেড করার নিয়ম (প্রো-রেটা সমন্বয়)</h4>
                                        <p>স্ট্যান্ডার্ড থেকে প্রিমিয়ামে আপগ্রেড করতে চাইলে পূর্বের প্যাকেজের অবশিষ্ট দিনের টাকা সমন্বয় করে অতিরিক্ত অংশ পরিশোধ করা যাবে।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">২০. প্যাকেজ ডাউনগ্রেড করার সময়সীমা</h4>
                                        <p>প্যাকেজ ডাউনগ্রেডের আবেদন বাৎসরিক মেয়াদ শেষ হওয়ার অন্তত ১৫ দিন পূর্বে জানাতে হবে, যা পরবর্তী বিলিং সাইকেলে কার্যকর হবে।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">২১. বাল্ক এসএমএস রিচার্জের পৃথক হিসাব ও মেয়াদহীনতা</h4>
                                        <p>বাল্ক এসএমএস বান্ডেলের টাকা কখনোই মেয়াদোত্তীর্ণ হয় না; সাবস্ক্রিপশন নবায়ন থাকলেও এসএমএস ব্যালেন্স আজীবন অক্ষত থাকে।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">২২. সরকারি তহবিল ছাড় দেরির ক্ষেত্রে বিশেষ বিবেচনা</h4>
                                        <p>সরকারি বরাদ্দ বা রাজস্ব তহবিল ছাড় পেতে বিলম্ব হলে ইউনিয়ন পরিষদের অনুরোধে অতিরিক্ত সময়সীমা মঞ্জুর করা হয়।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">২৩. সাবস্ক্রিপশন বাতিলের নিয়ম ও নোটিশ</h4>
                                        <p>কোনো ইউনিয়ন পরিষদ সেবা বাতিল করতে চাইলে মেয়াদ শেষ হওয়ার ৩০ দিন পূর্বে লিখিত নোটিশ প্রদান করতে হবে।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">২৪. বাতিলকৃত অ্যাকাউন্টের ডাটা সম্পূর্ণ হস্তান্তরের নিশ্চয়তা</h4>
                                        <p>চুক্তি সমাপ্তির সাথে সাথে ইউনিয়ন পরিষদকে তাদের যাবতীয় ডাটাবেজ ব্যাকআপ ও ডিজিটাল আর্কাইভ বিনামূল্যে হস্তান্তর করা হবে।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">২৫. চুক্তি বাতিলের ৯০ দিন পর ক্লাউড ডিলিট পলিসি</h4>
                                        <p>চুক্তি সমাপ্তির ৯০ দিন পর্যন্ত কোল্ড স্টোরেজে ব্যাকআপ সংরক্ষিত থাকবে; এরপর স্থায়ীভাবে ডাটা ডিলিট করা হবে।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">২৬. দীর্ঘমেয়াদি অগ্রিম সাবস্ক্রিপশনে বিশেষ ছাড়</h4>
                                        <p>একসাথে ৩ বা ৫ বছরের অগ্রিম সাবস্ক্রিপশন গ্রহণকারী ইউনিয়ন পরিষদের জন্য ১৫% থেকে ২৫% বিশেষ ছাড় সুবিধা প্রযোজ্য।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">২৭. উপজেলা ক্লাস্টারভিত্তিক যৌথ সাবস্ক্রিপশন সুবিধা</h4>
                                        <p>একই উপজেলার সকল ইউনিয়ন পরিষদ একসাথে অনবোর্ড হলে বিশেষ ছাড় ও সেন্ট্রাল ইউএনও মনিটরিং পোর্টাল প্রদান করা হয়।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">২৮. সফটওয়্যার ভার্সন আপগ্রেডের সম্পূর্ণ ফ্রি নীতি</h4>
                                        <p>সফটওয়্যারের নতুন ভার্সন বা সিকিউরিটি ফিচার রিলিজ হলে চলমান সাবস্ক্রিপশনধারীরা সম্পূর্ণ বিনামূল্যে তা উপভোগ করবেন।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">২৯. নিজস্ব সরকারি ডোমেন (.gov.bd) বাইন্ডিং সুবিধা</h4>
                                        <p>প্রিমিয়াম প্যাকেজে ইউনিয়ন পরিষদের নিজস্ব সরকারি ডোমেন ও ডিএনএস সেটআপে টেকনিক্যাল টিম সম্পূর্ণ ফ্রি সহায়তা দেবে।</p>
                                    </div>
                                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                        <h4 class="font-bold text-neutral-900 text-sm mb-1">৩০. বিলিং নীতিমালার স্বচ্ছতা ও প্রাতিষ্ঠানিক জবাবদিহিতা</h4>
                                        <p>বিলিং ও রিফান্ড সংক্রান্ত যেকোনো মতানৈক্যে সংশ্লিষ্ট উপজেলা প্রশাসন ও স্থানীয় সরকার বিভাগের মতামতকে প্রাধান্য দেওয়া হবে।</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Contact Support Footer in Policy -->
                        <div class="mt-12 pt-6 border-t border-neutral-200 flex flex-col sm:flex-row items-center justify-between gap-4">
                            <div class="text-xs text-neutral-500">
                                পলিসি বা নিরাপত্তা সংক্রান্ত কোনো জিজ্ঞাসা থাকলে আমাদের আইনি টিমের সাথে যোগাযোগ করুন।
                            </div>
                            <button type="button" data-toggle="modal" data-target="#supportModal"
                                    class="px-5 py-2.5 rounded-xl bg-indigo-700 hover:bg-indigo-800 text-white text-xs font-bold flex items-center gap-2 cursor-pointer transition-colors shadow-xs">
                                <?= icon('envelope-simple', 16, 'bold') ?>
                                <span>লিগ্যাল ও টেকনিক্যাল হেল্পডেস্ক</span>
                            </button>
                        </div>

                    </div>

                </div>
            </div>

        </main>
