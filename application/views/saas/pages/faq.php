<?php
/**
 * Product-site FAQ page.
 *
 * Ported from resources/js/pages/FaqPage.jsx, which held its fourteen
 * questions in five categories behind two pieces of React state: a live search
 * box and a category filter. Neither of those survives a static view on its
 * own — a text box that filters nothing is worse than no text box — so both
 * became real, no-JavaScript request states on this same URL:
 *
 *   - The search box is now a GET form posting to /faq. Front::faq() passes no
 *     query data, so the view reads $_GET['q'] itself and applies the same rule
 *     as the original useMemo: a question is kept when its question or its
 *     answer contains the term, case-insensitively. The JSX "no results" block
 *     is what a search with no hits renders, so it is kept.
 *   - The six category buttons are now <a href="/faq?cat=…">. The view keeps
 *     only that category; the default cat=all shows all fourteen, in the order
 *     COMPREHENSIVE_FAQS had them. Absolute hrefs, not a bare "?cat=tax",
 *     because saas/head.php sets a <base href> and a relative query string
 *     would resolve to the landing page instead of /faq.
 *
 * The accordion is unchanged in behaviour: exclusive, first question open,
 * toggled by data-saas-accordion in saas.js. Panel ids are faqPanel<n> counted
 * over the filtered list, so they stay unique and stay short whatever the
 * filter. `hidden` is merged into the panel's single class attribute — the
 * JSX only rendered an open panel, so this is the smallest markup that lets
 * saas.js open a closed one.
 *
 * The two footer buttons lost their handlers and kept their markup: the
 * support one is the shared modal trigger, the contact one is an <a href>.
 *
 * @var string $saas_path  Active route, '/faq'.
 */

$saas_faq_cats = array(
    array('id' => 'all',      'label' => 'সকল প্রশ্ন',             'icon' => 'question'),
    array('id' => 'tax',      'label' => 'ট্যাক্স ও হোল্ডিং',      'icon' => 'receipt'),
    array('id' => 'sonod',    'label' => 'সনদ ও কিউআর কোড',      'icon' => 'certificate'),
    array('id' => 'training', 'label' => 'অনবোর্ডিং ও ট্রেনিং',   'icon' => 'graduation-cap'),
    array('id' => 'security', 'label' => 'ডাটা সিকিউরিটি',        'icon' => 'shield-check'),
    array('id' => 'billing',  'label' => 'পেমেন্ট ও সাবস্ক্রিপশন', 'icon' => 'credit-card'),
);

/* The five categories, in the order the JSX grouped them, each one labelled
   with the FAQ_CATEGORIES entry above. Order is the JSX array order, so this is
   still the 'all' list the React page showed on first paint. */
$saas_faqs = array(
    array('tax',
        'সফটওয়্যারটির মাধ্যমে কীভাবে হোল্ডিং ট্যাক্স নির্ধারণ ও আদায় করা হয়?',
        'স্থানীয় সরকার (ইউনিয়ন পরিষদ) ট্যাক্সেশন রুলস অনুযায়ী প্রতিটি ওয়ার্ড ও গ্রামের বসতবাড়ি ও বাণিজ্যিক প্রতিষ্ঠানের পাকা, আধাপাকা বা কাঁচা ঘর এবং বার্ষিক ভাড়ার মূল্যের ভিত্তিতে স্বয়ংক্রিয়ভাবে হোল্ডিং ট্যাক্স নির্ধারণ করা হয়। আদায়কারী মাঠ পর্যায়ে বা অফিসে বসে মোবাইল/কম্পিউটার দিয়ে নগদ গ্রহণ করে তাৎক্ষণিক প্রিন্টেড মানি রসিদ প্রদান করতে পারেন।'),
    array('tax',
        'পুরোনো খাতা-কলমের বকেয়া বা পূর্ববর্তী বছরের হিসাব কীভাবে সিস্টেমে আনা যাবে?',
        'ডাটা এন্ট্রির সময় প্রতিটি হোল্ডিংয়ের পূর্ববর্তী বছরের বকেয়া ট্যাক্স (Previous Due) ইনপুট দেওয়ার ব্যবস্থা রয়েছে। সিস্টেম স্বয়ংক্রিয়ভাবে চলতি বছর ও পূর্বের বকেয়া যোগ করে মোট কর হিসাব করে এবং ইনভয়েসে আলাদা করে দেখায়।'),
    array('tax',
        'করদাতা কি ঘরে বসে ট্যাক্স স্ট্যাটাস যাচাই করতে পারবেন?',
        'হ্যাঁ। প্রতিটি ইউনিয়নের পাবলিক পোর্টালে "হোল্ডিং সার্চ ও কর যাচাই" মেন্যু রয়েছে। সেখানে হোল্ডিং নম্বর বা মোবাইল নম্বর দিয়ে নাগরিক তার বকেয়া বা পরিশোধিত রসিদের বিস্তারিত দেখতে পান।'),

    array('sonod',
        'কী কী ধরণের নাগরিক সনদপত্র এই সিস্টেম থেকে ইস্যু করা যায়?',
        'নাগরিকত্ব সনদ, ওয়ারিশ সনদ, ট্রেড লাইসেন্স, চারিত্রিক সনদ, ভূমিহীন সনদ, অবিবাহিত সনদ, বার্ষিক আয়ের প্রত্যয়নপত্র, একই নামের প্রত্যয়ন এবং সাধারণ প্রত্যয়নপত্রসহ প্রয়োজনীয় সকল স্থানীয় সরকার সনদ প্রস্তুত ও প্রিন্ট করা যায়।'),
    array('sonod',
        'কিউআর কোড (QR Code) ভেরিফিকেশন কীভাবে কাজ করে?',
        'প্রতিটি সনদে একটি ইউনিক এনক্রিপ্টেড কিউআর কোড প্রিন্ট থাকে। যেকোনো স্মার্টফোনের ক্যামেরা বা কিউআর স্ক্যানার দিয়ে স্ক্যান করলে সরকারি ডাটাবেজ থেকে মূল সনদের সত্যতা, চেয়ারম্যান ও সচিবের ডিজিটাল স্বাক্ষর ও ইস্যুর তারিখ সরাসরি পর্দায় প্রদর্শিত হয়। ফলে জাল সনদ তৈরির কোনো সুযোগ থাকে না।'),
    array('sonod',
        'চেয়ারম্যানের ডিজিটাল স্বাক্ষর কীভাবে সনদে যুক্ত হবে?',
        'অ্যাডমিন প্যানেল থেকে চেয়ারম্যান ও সচিবের অনুমোদিত ডিজিটাল সিলমোহর ও স্বাক্ষর একবার আপলোড করে দিলে সনদ চূড়ান্ত অনুমোদনের পর তা স্বয়ংক্রিয়ভাবে সনদের নির্ধারিত স্থানে ওয়াটারমার্কসহ বসে যায়।'),

    array('training',
        'সফটওয়্যারটি চালু করতে কি ইউনিয়ন অফিসে নতুন সার্ভার কেনা লাগবে?',
        'না, কোনো বাড়তি সার্ভার বা ভারী যন্ত্রাংশ কেনার কোনো প্রয়োজন নেই। এটি ১০০% ক্লাউড ভিত্তিক SaaS প্ল্যাটফর্ম। যেকোনো কম্পিউটার, ল্যাপটপ বা স্মার্টফোনের ব্রাউজার এবং সাধারণ ইন্টারনেট সংযোগ থাকলেই এটি চালানো সম্ভব।'),
    array('training',
        'ইউনিয়নের উদ্যোক্তা ও স্টাফদের কি প্রশিক্ষণ দেওয়া হয়?',
        'হ্যাঁ। আমাদের দক্ষ অনবোর্ডিং টিম সরাসরি আপনার ইউনিয়ন পরিষদ কার্যালয়ে উপস্থিত হয়ে চেয়ারম্যান, সচিব, হিসাব সহকারী ও ইউডিসি উদ্যোক্তাদের পূর্ণাঙ্গ ব্যবহারিক প্রশিক্ষণ প্রদান করে। এছাড়াও ভিডিও টিউটোরিয়াল ও ম্যানুয়াল গাইড সরবরাহ করা হয়।'),
    array('training',
        'পুরোনো রেজিস্টার বইয়ের ডাটা এন্ট্রি করতে কতদিন সময় লাগে?',
        'ইউনিয়নের লোকবল ও পরিবারের সংখ্যার উপর নির্ভর করে সাধারণত ৩ থেকে ৭ কর্মদিবসের মধ্যে ইউডিসি উদ্যোক্তাদের মাধ্যমে পুরো ইউনিয়নের পরিবার ভিত্তিক ডাটা এন্ট্রি সম্পন্ন করা যায়। আমাদের রয়েছে এক্সেল ফাইল থেকে বাল্ক আপলোডের বিশেষ সুবিধা।'),

    array('security',
        'এক ইউনিয়নের ডাটা বা রাজস্ব তথ্য কি অন্য ইউনিয়ন দেখতে পারবে?',
        'কখনোই নয়। আমাদের প্ল্যাটফর্ম মাল্টি-টেন্যান্ট আইসোলেশন আর্কিটেকচারে তৈরি। প্রতিটি ইউনিয়ন পরিষদের ডাটা আলাদা ভার্চুয়াল স্পেসে এনক্রিপ্ট করা থাকে। ফলে এক ইউনিয়নের কোনো তথ্য অন্য ইউনিয়নের ড্যাশবোর্ডে প্রবেশের কোনো প্রযুক্তিগত সুযোগ নেই।'),
    array('security',
        'সার্ভার নষ্ট হলে বা ইন্টারনেট বিভ্রাটে ডাটা হারানোর ঝুঁকি আছে কি?',
        'না। আমাদের ডাটাবেজ ক্লাউডে দৈনিক একাধিকবার স্বয়ংক্রিয়ভাবে এনক্রিপ্টেড ব্যাকআপ নেওয়া হয় এবং তা আলাদা জিওগ্রাফিক্যাল ক্লাউড ডেটাসেন্টারে সংরক্ষিত থাকে। তাই যেকোনো দুর্ঘটনায় ১০০% ডাটা রিকভারি নিশ্চিত।'),

    array('billing',
        'প্যাকেজের মূল্য কীভাবে পরিশোধ করতে হয়?',
        'ইউনিয়ন পরিষদের অফিশিয়াল ব্যাংক চেক, পে-অর্ডার, ব্যাংক ট্রান্সফার অথবা সরকার নির্ধারিত নিয়ম মেনে বিল পরিশোধ করা যায়। পেমেন্ট সম্পন্ন হওয়ার সাথে সাথেই ভ্যাট চালানের কপি ও অফিশিয়াল ইনভয়েস প্রদান করা হয়।'),
    array('billing',
        'বাৎসরিক সাবস্ক্রিপশন ফি ছাড়া কোনো লুকানো চার্জ আছে কি?',
        'না, কোনো প্রকার লুকানো বা অতিরিক্ত মাসিক ফি নেই। বাৎসরিক প্যাকেজের মধ্যে ক্লাউড হোস্টিং, সিকিউরিটি আপডেট, ব্যাকআপ এবং আনলিমিটেড সাপোর্ট অন্তর্ভুক্ত থাকে।'),
    array('billing',
        'প্যাকেজ পরবর্তীতে আপগ্রেড করা সম্ভব কি?',
        'হ্যাঁ, যেকোনো সময় স্ট্যান্ডার্ড থেকে প্রিমিয়াম বা ক্লাস্টার প্যাকেজে আপগ্রেড করা সম্ভব। পূর্বের সকল ডাটা সম্পূর্ণ অক্ষত রেখে এক ক্লিকেই নতুন ফিচারে এক্সেস পাওয়া যায়।'),
);

/* --- the two request states the React useState hooks used to hold --------- */

$saas_faq_q = trim((string) $this->input->get('q', FALSE));

$saas_faq_cat = (string) $this->input->get('cat', FALSE);
$saas_faq_cat_known = array();
foreach ($saas_faq_cats as $saas_faq_cat_row) {
    $saas_faq_cat_known[$saas_faq_cat_row['id']] = TRUE;
}
if (!isset($saas_faq_cat_known[$saas_faq_cat])) {
    $saas_faq_cat = 'all';
}

$saas_faq_list = array();
foreach ($saas_faqs as $saas_faq_row) {
    if ($saas_faq_cat !== 'all' && $saas_faq_row[0] !== $saas_faq_cat) {
        continue;
    }
    // Same test as the JSX useMemo: question OR answer contains the term.
    if ($saas_faq_q !== ''
        && stripos($saas_faq_row[1], $saas_faq_q) === FALSE
        && stripos($saas_faq_row[2], $saas_faq_q) === FALSE) {
        continue;
    }
    $saas_faq_list[] = $saas_faq_row;
}
?>

        <main class="grow">

            <div class="py-12 bg-white">

                <!-- Header Banner -->
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-10">
                    <div class="text-center max-w-3xl mx-auto">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800 mb-3">
                            <?= icon('question', 16, 'bold') ?>
                            <span>সাহায্য ও সমাধান কেন্দ্র</span>
                        </span>
                        <h1 class="text-3xl sm:text-5xl font-black text-neutral-900 tracking-tight leading-tight">
                            সচরাচর জিজ্ঞাসিত প্রশ্নাবলী (FAQ)
                        </h1>
                        <p class="mt-4 text-sm sm:text-base text-neutral-600 leading-relaxed">
                            ViretaDev ডিজিটাল ইউনিয়ন পরিষদ ম্যানেজমেন্ট সফটওয়্যার সংক্রান্ত যাবতীয় প্রশ্ন, প্রযুক্তিগত বৈশিষ্ট্য ও পরিচালনার নিয়মাবলী।
                        </p>

                        <!-- Search. The JSX filtered on every keystroke; this is a
                             GET to the same URL and the view does the matching. -->
                        <form method="get" action="<?= site_url('faq') ?>" class="mt-8 max-w-xl mx-auto relative">
                            <input type="hidden" name="cat" value="<?= html_escape($saas_faq_cat) ?>">
                            <?= icon('magnifying-glass', 20, 'bold', 'absolute left-4 top-3.5 text-neutral-400') ?>
                            <input type="text" name="q" value="<?= html_escape($saas_faq_q) ?>"
                                   placeholder="আপনার প্রশ্ন লিখে খুঁজুন (যেমন: ট্যাক্স, সনদ, ব্যাকআপ, ডোমেন)..."
                                   class="ht-field w-full pl-12 pr-4 py-3 rounded-2xl text-sm">
                        </form>
                    </div>
                </div>

                <!-- Category filter. React swapped the list in state; these are
                     real URLs, so a filtered FAQ page can be linked and reloaded. -->
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-10 text-center">
                    <div class="neu-glass-inset p-1.5 rounded-2xl inline-flex flex-wrap items-center justify-center gap-1.5">
                        <?php foreach ($saas_faq_cats as $saas_faq_cat_row):
                            $saas_faq_cat_on = ($saas_faq_cat_row['id'] === $saas_faq_cat);
                        ?>
                        <a href="<?= site_url('faq') . '?cat=' . $saas_faq_cat_row['id'] ?>"
                           <?= $saas_faq_cat_on ? 'aria-current="true"' : '' ?>
                           class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold flex items-center gap-2 cursor-pointer transition-all <?= $saas_faq_cat_on ? 'bg-indigo-600 text-white shadow-md' : 'text-neutral-600 hover:text-indigo-600 hover:bg-white/60' ?>">
                            <?= icon($saas_faq_cat_row['icon'], 16, 'bold') ?>
                            <span><?= $saas_faq_cat_row['label'] ?></span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- FAQ Accordion List. Exclusive; first question open. -->
                <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 mb-16">
                    <?php if (empty($saas_faq_list)): ?>
                    <div class="p-12 text-center bg-neutral-50 rounded-3xl border border-neutral-200">
                        <?= icon('question', 40, 'regular', 'mx-auto text-neutral-400 mb-3') ?>
                        <h3 class="text-base font-bold text-neutral-800">কোনো ফলাফল পাওয়া যায়নি</h3>
                        <p class="text-xs text-neutral-500 mt-1">অন্য কোনো শব্দ দিয়ে অনুসন্ধান করুন অথবা সরাসরি সাপোর্ট টিমের সাথে কথা বলুন।</p>
                    </div>
                    <?php else: ?>
                    <div class="space-y-4" data-saas-accordion>
                        <?php foreach ($saas_faq_list as $saas_faq_i => $saas_faq_row):
                            $saas_faq_panel = 'faqPanel' . $saas_faq_i;
                            $saas_faq_open  = ($saas_faq_i === 0);
                        ?>
                        <div class="neu-glass-card rounded-2xl overflow-hidden transition-all">
                            <button type="button" data-saas-accordion
                                    aria-controls="<?= $saas_faq_panel ?>" aria-expanded="<?= $saas_faq_open ? 'true' : 'false' ?>"
                                    class="w-full p-5 text-left flex items-center justify-between gap-4 font-bold text-neutral-900 hover:text-indigo-600 transition-colors cursor-pointer">
                                <span class="text-sm sm:text-base leading-snug"><?= $saas_faq_row[1] ?></span>
                                <span class="w-8 h-8 rounded-full bg-neutral-100 flex items-center justify-center shrink-0 text-neutral-600">
                                    <?php /* The React version swapped CaretUp for
                                             CaretDown on state. One icon plus a
                                             rotation reads the same. */ ?>
                                    <span data-saas-caret class="<?= $saas_faq_open ? 'rotate-180' : '' ?>"><?= icon('caret-down', 16, 'bold') ?></span>
                                </span>
                            </button>
                            <div id="<?= $saas_faq_panel ?>" class="px-5 pb-5 text-xs sm:text-sm text-neutral-600 leading-relaxed border-t border-neutral-100 pt-3<?= $saas_faq_open ? '' : ' hidden' ?>">
                                <?= $saas_faq_row[2] ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Still Have Questions CTA -->
                <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="neu-dark-glass text-white p-8 sm:p-10 text-center relative overflow-hidden">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-400 text-neutral-950 mb-3">
                            <?= icon('sparkle', 14, 'fill') ?>
                            <span>২৪/৭ ডেডিকেটেড সাপোর্ট</span>
                        </span>
                        <h2 class="text-2xl sm:text-3xl font-black tracking-tight mb-3">
                            আপনার কাঙ্ক্ষিত প্রশ্নটি কি খুঁজে পাননি?
                        </h2>
                        <p class="text-xs sm:text-sm text-neutral-400 max-w-xl mx-auto mb-6 leading-relaxed">
                            আমাদের টেকনিক্যাল প্রতিনিধিগণ আপনার ইউনিয়নের যেকোনো জিজ্ঞাসা বা সরাসরি ডেমোর জন্য সর্বদা প্রস্তুত।
                        </p>
                        <div class="flex flex-wrap items-center justify-center gap-3">
                            <button type="button" data-toggle="modal" data-target="#supportModal"
                                    class="px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-bold flex items-center gap-2 cursor-pointer transition-colors shadow-sm">
                                <?= icon('headset', 18, 'bold') ?>
                                <span>সরাসরি হেল্পডেস্কে কল করুন</span>
                            </button>
                            <a href="<?= site_url('contact') ?>"
                               class="px-6 py-3 rounded-xl bg-neutral-800 hover:bg-neutral-700 text-white text-xs sm:text-sm font-bold flex items-center gap-2 cursor-pointer transition-colors">
                                <span>বার্তা পাঠান</span>
                                <?= icon('arrow-right', 16, 'bold') ?>
                            </a>
                        </div>
                    </div>
                </div>

            </div>

        </main>
