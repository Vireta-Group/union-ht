<?php
/**
 * Product-site reviews page.
 *
 * Ported from resources/js/pages/ReviewsPage.jsx, including its own six-entry
 * ALL_REVIEWS array - which is NOT the three-card reviews section the home
 * page has. That section keeps its own local $saas_reviews; nothing here is
 * shared with it and home.php was not touched.
 *
 * Three conversions:
 *
 *   - <img on-error={... ui-avatars.com}> became
 *     data-saas-avatar="<urlencoded name>", which saas.js already handles
 *     (see its initAvatarFallbacks). Same contract home.php uses, so the markup
 *     stays free of script.
 *   - The filter was a useState('all') that re-rendered the grid client-side.
 *     It is now ?cat= on the URL, so a filtered view is a real document, is
 *     shareable and survives a reload - the same conversion the policy tabs
 *     and the /plans -> /register plan preselect already use. It deliberately
 *     does NOT use the [data-saas-tabs] contract: that contract toggles
 *     border-indigo-600 / text-indigo-600 / text-neutral-500, and these tabs
 *     are active bg-indigo-700 / inactive bg-neutral-100, so saas.js would
 *     click them without visibly changing anything.
 *   - <button on-click={onNavigate('/x')}> became <a href="...">.
 *
 * ALL_REVIEWS also carried a `date` field on every entry that ReviewsPage.jsx
 * never rendered, so it is kept in the array below but is not printed. `id` and
 * `category` exist to key the list and drive the filter.
 *
 * @var string $saas_path  Active route, '/reviews'. Read by the navbar and
 *                         footer for highlighting; unused on this page.
 */

$saas_reviews_fallback = array(
    array(
        'id'           => 1,
        'category'     => 'chairman',
        'name'         => 'মো: খলিলুর রহমান',
        'designation'  => 'ইউপি চেয়ারম্যান',
        'organization' => 'আদর্শ মডেল ইউনিয়ন পরিষদ',
        'rating'       => 5,
        'date'         => 'সেপ্টেম্বর ২০২৬', // in ALL_REVIEWS, never rendered by the JSX
        'impact'       => 'ট্যাক্স আদায় বৃদ্ধি: +১১০%',
        'quote'        => 'ViretaDev SaaS ব্যবহারের পর আমাদের ইউনিয়ন পরিষদের হোল্ডিং ট্যাক্স আদায় আগের বছরের চেয়ে দ্বিগুণেরও বেশি বৃদ্ধি পেয়েছে। নাগরিকরা এখন আর রসিদ বা কাগজ হারানোর ভয়ে থাকে না—সবকিছু অনলাইনে সংরক্ষিত থাকে। গ্রামের প্রতিটি বাড়ি ডিজিটাল ডাটাবেজে অন্তর্ভুক্ত হয়েছে।',
        'avatar'       => 'inc/assets/himg/avatar1.png',
    ),
    array(
        'id'           => 2,
        'category'     => 'secretary',
        'name'         => 'মোহাম্মদ শাহ আলম',
        'designation'  => 'ইউপি সচিব',
        'organization' => 'মীরসরাই মডেল ইউনিয়ন পরিষদ',
        'rating'       => 5,
        'date'         => 'আগস্ট ২০২৬', // in ALL_REVIEWS, never rendered by the JSX
        'impact'       => 'সনদ প্রস্তুতি সময়: মাত্র ২ মিনিট',
        'quote'        => 'পূর্বে ওয়ারিশান ও চারিত্রিক সনদ তৈরি করতে ঘণ্টার পর ঘণ্টা পুরোনো ফাইল ও খাতা দেখতে হতো। এখন মাত্র ১ ক্লিকে কিউআর কোডসহ সনদ তৈরি হয়ে যায়। সবচেয়ে বড় বিষয়—ইউনিয়ন থেকে বের হওয়া কোনো সনদ আর কেউ নকল করতে পারে না, কিউআর স্ক্যান করলেই সত্যতা নিশ্চিত হওয়া যায়।',
        'avatar'       => 'inc/assets/himg/avatar2.png',
    ),
    array(
        'id'           => 3,
        'category'     => 'entrepreneur',
        'name'         => 'মোছা: জান্নাতুল ফেরদৌস',
        'designation'  => 'ইউডিসি ডিজিটাল উদ্যোক্তা',
        'organization' => 'ইউনিয়ন ডিজিটাল সেন্টার (UDC)',
        'rating'       => 5,
        'date'         => 'জুলাই ২০২৬', // in ALL_REVIEWS, never rendered by the JSX
        'impact'       => 'নাগরিক সেবা সন্তুষ্টি: ৯৯%',
        'quote'        => 'ইন্টারফেসটি পুরোপুরি বাংলায় হওয়ায় আমাদের কাজ করা অত্যন্ত সহজ হয়েছে। গ্রামের বয়োবৃদ্ধ নাগরিকরাও খুব সহজে তাদের হোল্ডিং ট্যাক্স ও সনদ সংক্রান্ত তথ্য জানতে পারছেন। কোনো টেকনিক্যাল সমস্যা হলে ViretaDev-এর হেল্পডেস্ক টিম কয়েক মিনিটেই সমাধান করে দেয়।',
        'avatar'       => 'inc/assets/himg/avatar3.png',
    ),
    array(
        'id'           => 4,
        'category'     => 'chairman',
        'name'         => 'এ কে এম মোস্তফা কামাল',
        'designation'  => 'ইউপি চেয়ারম্যান',
        'organization' => 'ফেনী সদর ইউনিয়ন পরিষদ',
        'rating'       => 5,
        'date'         => 'জুন ২০২৬', // in ALL_REVIEWS, never rendered by the JSX
        'impact'       => 'পেপারলেস প্রশাসন: ১০০%',
        'quote'        => 'আমাদের ইউনিয়ন পরিষদে কাগজপত্রের স্তূপ এবং ফাইল হারানোর যে সমস্যা ছিল, তা চিরতরে শেষ হয়েছে। আমি যখন মন্ত্রণালয়ে বা জেলা প্রশাসকের মিটিংয়ে থাকি, তখনো নিজের স্মার্টফোন থেকে পরিষদের রাজস্ব সংগ্রহ লাইভ দেখতে পাই। এটি সত্য অর্থেই আধুনিক স্মার্ট বাংলাদেশ।',
        'avatar'       => 'inc/assets/himg/avatar1.png',
    ),
    array(
        'id'           => 5,
        'category'     => 'secretary',
        'name'         => 'আব্দুল কাদের চৌধুরী',
        'designation'  => 'ইউপি সচিব',
        'organization' => 'বারৈয়ারহাট ইউনিয়ন পরিষদ',
        'rating'       => 5,
        'date'         => 'মে ২০২৬', // in ALL_REVIEWS, never rendered by the JSX
        'impact'       => 'হিসাব অডিট: শতভাগ স্বচ্ছ',
        'quote'        => 'বাৎসরিক অডিটের সময় আগে আমাদের রাতের পর রাত জেগে হিসাব মেলাতে হতো। ViretaDev সফটওয়্যারে প্রতিটি টাকার লেনদেন ডিজিটাল ইনভয়েস আকারে থাকায় বাৎসরিক অডিট রিপোর্ট মাত্র ১০ সেকেন্ডে জেনারেট হয়ে যায়। সরকারি কর্মকর্তাদের সামনে আমরা আত্মবিশ্বাসের সাথে রিপোর্ট তুলে ধরতে পারি।',
        'avatar'       => 'inc/assets/himg/avatar2.png',
    ),
    array(
        'id'           => 6,
        'category'     => 'entrepreneur',
        'name'         => 'তানভীর আহমেদ রিফাত',
        'designation'  => 'আইটি অপারেটর ও উদ্যোক্তা',
        'organization' => 'ডিজিটাল সেবা কেন্দ্র',
        'rating'       => 5,
        'date'         => 'এপ্রিল ২০২৬', // in ALL_REVIEWS, never rendered by the JSX
        'impact'       => 'ডাটা এন্ট্রি স্পিড: ৩ গুণ ফাস্ট',
        'quote'        => 'বাল্ক পারিবারিক ডাটা এন্ট্রি ফর্মটি অসাধারণ। খানাপ্রধানের এনআইডি নম্বর দিলে স্বয়ংক্রিয়ভাবে পরিবারের সদস্যদের তালিকা সাজানো যায়। ট্রেড লাইসেন্স নবায়নও এখন সম্পূর্ণ স্বয়ংক্রিয়। এই সফটওয়্যারটি ইউনিয়ন পরিষদের জন্য আশীর্বাদ স্বরূপ।',
        'avatar'       => 'inc/assets/himg/avatar3.png',
    ),
);

// Map database reviews dynamically
if (!empty($db_reviews) && is_array($db_reviews)) {
    $saas_reviews = array();
    foreach ($db_reviews as $dbr) {
        $category = 'citizen';
        $role_lower = mb_strtolower($dbr['role'] ?? '');
        if (str_contains($role_lower, 'চেয়ারম্যান') || str_contains($role_lower, 'chairman')) {
            $category = 'chairman';
        } elseif (str_contains($role_lower, 'সচিব') || str_contains($role_lower, 'secretary')) {
            $category = 'secretary';
        } elseif (str_contains($role_lower, 'উদ্যোক্তা') || str_contains($role_lower, 'entrepreneur') || str_contains($role_lower, 'অপারেটর')) {
            $category = 'entrepreneur';
        }

        $avatar = !empty($dbr['avatar']) ? $dbr['avatar'] : 'inc/assets/himg/avatar' . ((abs((int)$dbr['id']) % 3) + 1) . '.png';

        $saas_reviews[] = array(
            'id'           => (int)$dbr['id'],
            'category'     => $category,
            'name'         => $dbr['author'],
            'designation'  => $dbr['role'] ?: 'সম্মানিত নাগরিক',
            'organization' => $dbr['tenant_name'] ?: 'ইউনিয়ন পরিষদ',
            'rating'       => (int)($dbr['rating'] ?: 5),
            'impact'       => $dbr['impact'] ?: ($dbr['service'] ?: 'সফল ডিজিটাল রূপান্তর'),
            'quote'        => $dbr['content'],
            'avatar'       => $avatar,
        );
    }
} else {
    $saas_reviews = $saas_reviews_fallback;
}

// The filter tabs, in source order. 'all' is the only one with a count.
$saas_review_cats = array(
    'all'          => 'সকল রিভিউ (',
    'chairman'     => 'ইউপি চেয়ারম্যানবৃন্দ',
    'secretary'    => 'ইউপি সচিববৃন্দ',
    'entrepreneur' => 'ডিজিটাল সেন্টার উদ্যোক্তা',
    'citizen'      => 'নাগরিক ও গ্রাহকবৃন্দ',
);

// ?cat= decides the filter. Unknown values fall back to 'all' rather than
// rendering an empty grid.
$saas_cat = $this->input->get('cat');
if ( ! isset($saas_review_cats[$saas_cat])) {
    $saas_cat = 'all';
}

$saas_shown = $saas_reviews;
if ($saas_cat !== 'all') {
    $saas_shown = array();
    foreach ($saas_reviews as $saas_review) {
        if ($saas_review['category'] === $saas_cat) {
            $saas_shown[] = $saas_review;
        }
    }
}
?>

        <main class="grow py-12">

            <!-- Page Header -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-12">
                <div class="text-center max-w-3xl mx-auto">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100/80 text-emerald-800 border border-emerald-200/60 mb-4 backdrop-blur-xs">
                        <?= icon('chat-circle-dots', 16, 'bold') ?>
                        <span>গ্রাহক সন্তুষ্টি ও সাফল্যগাঁথা</span>
                    </span>
                    <h1 class="text-3xl sm:text-5xl font-extrabold text-neutral-900 tracking-tight leading-tight">
                        ইউনিয়ন পরিষদের ডিজিটাল সাফল্যের গল্প <br />
                        <span class="text-indigo-600 drop-shadow-xs">ও বাস্তব পর্যালোচনা</span>
                    </h1>
                    <p class="mt-4 text-sm sm:text-base text-neutral-600 leading-relaxed">
                        দেশের বিভিন্ন ইউনিয়ন পরিষদের সম্মানিত চেয়ারম্যান, সচিব, উদ্যোক্তা ও সেবাগ্রহীতা নাগরিকদের সরাসরি অভিজ্ঞতা ও মতামত।
                    </p>
                    <div class="mt-6 flex flex-wrap justify-center gap-3">
                        <button type="button" id="btnOpenReviewModal" data-toggle="modal" data-target="#reviewModal"
                                onclick="if(window.htModal){window.htModal.open(document.getElementById('reviewModal'));}"
                                class="ht-btn ht-btn-primary px-5 py-2.5 text-xs sm:text-sm font-bold shadow-md hover:shadow-lg inline-flex items-center gap-2 cursor-pointer">
                            <?= icon('chat-circle-dots', 16, 'fill') ?>
                            <span>আপনার মতামত / রিভিউ দিন</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Aggregate Rating & Highlights Banner (Neumorphic Glass Card) -->
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 mb-14">
                <div class="p-6 sm:p-8 neu-glass-card grid grid-cols-1 md:grid-cols-3 gap-6 text-center divide-y md:divide-y-0 md:divide-x divide-neutral-200/70">

                    <div class="pt-2 md:pt-0">
                        <div class="flex items-center justify-center gap-1 text-amber-500 mb-1.5">
                            <?php for ($saas_star = 0; $saas_star < 5; $saas_star++) {
                                echo icon('star', 20, 'fill');
                            } ?>
                        </div>
                        <span class="text-3xl font-black text-neutral-900 block tracking-tight">৫.০ / ৫.০</span>
                        <span class="text-xs text-neutral-500 font-medium">গড় গ্রাহক রেটিং স্কোর</span>
                    </div>

                    <div class="pt-4 md:pt-0">
                        <div class="w-9 h-9 rounded-xl bg-emerald-500/10 text-emerald-600 border border-emerald-500/20 flex items-center justify-center mx-auto mb-1.5 shadow-xs">
                            <?= icon('trend-up', 20, 'bold') ?>
                        </div>
                        <span class="text-3xl font-black text-emerald-600 block tracking-tight">+১০০%</span>
                        <span class="text-xs text-neutral-500 font-medium">রাজস্ব আদায় বৃদ্ধির রেকর্ড</span>
                    </div>

                    <div class="pt-4 md:pt-0">
                        <div class="w-9 h-9 rounded-xl bg-indigo-500/10 text-indigo-600 border border-indigo-500/20 flex items-center justify-center mx-auto mb-1.5 shadow-xs">
                            <?= icon('shield-check', 20, 'bold') ?>
                        </div>
                        <span class="text-3xl font-black text-indigo-700 block tracking-tight">১০০%</span>
                        <span class="text-xs text-neutral-500 font-medium">সনদের কিউআর সত্যতা ও নিরাপত্তা</span>
                    </div>

                </div>
            </div>

            <!-- Filter Tabs inside Inset Track -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-10 text-center">
                <div class="neu-glass-inset p-1.5 rounded-2xl inline-flex flex-wrap items-center justify-center gap-1.5">
                    <?php foreach ($saas_review_cats as $saas_key => $saas_label):
                        $saas_is_active = ($saas_cat === $saas_key);
                    ?>
                    <a href="<?= $saas_key === 'all' ? site_url('reviews') : site_url('reviews?cat=' . $saas_key) ?>"
                       <?= $saas_is_active ? 'aria-current="page"' : '' ?>
                       class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition-all cursor-pointer <?= $saas_is_active ? 'bg-indigo-600 text-white shadow-md' : 'text-neutral-600 hover:text-indigo-600 hover:bg-white/60' ?>">
                        <?php if ($saas_key === 'all') {
                            echo $saas_label . count($saas_reviews) . ')';
                        } else {
                            echo $saas_label;
                        } ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Reviews Grid -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-20">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($saas_shown as $saas_r): ?>
                    <div class="ht-card neu-glass-card p-6 sm:p-7 flex flex-col justify-between group">

                        <div>
                            <!-- Rating & Impact Badge -->
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-1 text-amber-500">
                                    <?php for ($saas_star = 0; $saas_star < $saas_r['rating']; $saas_star++) {
                                        echo icon('star', 16, 'fill');
                                    } ?>
                                </div>
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-emerald-500/10 text-emerald-800 border border-emerald-500/25">
                                    <?= icon('check-circle', 13, 'fill') ?>
                                    <span><?= $saas_r['impact'] ?></span>
                                </span>
                            </div>

                            <!-- Quote Content -->
                            <p class="text-xs sm:text-sm text-neutral-700 leading-relaxed italic">
                                "<?= $saas_r['quote'] ?>"
                            </p>
                        </div>

                        <!-- Author & Profile -->
                        <div class="mt-6 pt-4 border-t border-neutral-200/60 flex items-center gap-3.5">
                            <div class="w-12 h-12 rounded-full overflow-hidden bg-indigo-50 border border-indigo-200/80 shadow-xs shrink-0">
                                <img src="<?= $saas_r['avatar'] ?>" alt="<?= $saas_r['name'] ?>"
                                     class="w-full h-full object-cover"
                                     data-saas-avatar="<?= rawurlencode($saas_r['name']) ?>">
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-neutral-900 group-hover:text-indigo-600 transition-colors">
                                    <?= $saas_r['name'] ?>
                                </h4>
                                <p class="text-xs font-semibold text-indigo-700">
                                    <?= $saas_r['designation'] ?>
                                </p>
                                <p class="text-[11px] text-neutral-500 leading-tight mt-0.5">
                                    <?= $saas_r['organization'] ?>
                                </p>
                            </div>
                        </div>

                    </div>
                    <?php endforeach; ?>
                </div>
            </div>


            <!-- CTA Box at Bottom (Neumorphic Glass Panel) -->
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="p-8 sm:p-10 neu-glass-card text-center relative overflow-hidden">
                    <h3 class="text-xl sm:text-2xl font-bold text-neutral-900 mb-3">
                        আপনার ইউনিয়ন পরিষদকেও এই সাফল্যের অংশীদার করুন
                    </h3>
                    <p class="text-xs sm:text-sm text-neutral-600 max-w-md mx-auto mb-6">
                        আমাদের বিশেষজ্ঞদের সাথে কথা বলুন এবং আপনার ইউনিয়ন পরিষদের জন্য একটি ফ্রি ডেমো সেশন বুক করুন।
                    </p>
                    <div class="flex flex-wrap justify-center gap-3">
                        <a href="<?= site_url('contact') ?>" class="ht-btn ht-btn-primary text-xs font-bold py-2.5 px-5 shadow-md">
                            <span>ফ্রি ডেমো বুক করুন</span>
                            <?= icon('arrow-right', 14, 'bold') ?>
                        </a>
                        <button type="button" data-toggle="modal" data-target="#reviewModal"
                                onclick="if(window.htModal){window.htModal.open(document.getElementById('reviewModal'));}"
                                class="ht-btn ht-btn-secondary text-neutral-800 text-xs font-bold py-2.5 px-5 cursor-pointer inline-flex items-center gap-1.5">
                            <?= icon('chat-circle-dots', 15, 'bold') ?>
                            <span>মতামত বা রিভিউ দিন</span>
                        </button>
                        <a href="<?= site_url('plans') ?>" class="ht-btn ht-btn-secondary text-xs font-bold py-2.5 px-5">
                            <span>প্যাকেজসমূহ দেখুন</span>
                        </a>
                    </div>
                </div>
            </div>

        </main>

        <!-- Review Submission Modal -->
        <div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" style="max-width: 540px; margin: 1.75rem auto;">
                <div class="modal-content overflow-hidden rounded-3xl border border-white/80 shadow-2xl">
                    
                    <!-- Modal Header -->
                    <div class="relative px-6 py-4 text-white" style="background: linear-gradient(135deg, #090d16 0%, #1e1b4b 60%, #312e81 100%);">
                        <button type="button" data-dismiss="modal" aria-label="Close"
                                class="absolute top-4 right-4 flex h-8 w-8 cursor-pointer items-center justify-center rounded-full bg-white/10 text-white transition-colors hover:bg-white/20">
                            <?= icon('x', 18, 'bold') ?>
                        </button>
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white/10 border border-white/20 text-amber-400">
                                <?= icon('chat-circle-dots', 22, 'fill') ?>
                            </div>
                            <div>
                                <h3 id="reviewModalLabel" class="text-base font-bold text-white leading-tight">আপনার মতামত ও রিভিউ দিন</h3>
                                <p class="text-xs text-indigo-200 mt-0.5">ইউনিয়ন পরিষদের অভিজ্ঞতা বা সেবা সম্পর্কিত মতামত</p>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Body Form -->
                    <form id="htReviewSubmitForm" class="p-6 space-y-4" novalidate>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label for="revAuthor" class="block text-xs font-bold text-neutral-700 mb-1">
                                    আপনার নাম <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" id="revAuthor" name="author" required maxlength="120"
                                       class="ht-field text-xs py-2 px-3 w-full rounded-xl border border-neutral-300 focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600"
                                       placeholder="উদা: মোঃ আরিফুল ইসলাম">
                            </div>
                            <div>
                                <label for="revRole" class="block text-xs font-bold text-neutral-700 mb-1">
                                    পদবী / পরিচয় <span class="text-rose-500">*</span>
                                </label>
                                <select id="revRole" name="role"
                                        class="ht-field text-xs py-2 px-3 w-full rounded-xl border border-neutral-300 focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 bg-white">
                                    <option value="ইউপি চেয়ারম্যান">ইউপি চেয়ারম্যান</option>
                                    <option value="ইউপি সচিব">ইউপি সচিব</option>
                                    <option value="ইউডিসি উদ্যোক্তা">ইউডিসি উদ্যোক্তা</option>
                                    <option value="হিসাব সহকারী">হিসাব সহকারী</option>
                                    <option value="নাগরিক ও ব্যবসায়ী" selected>নাগরিক ও ব্যবসায়ী</option>
                                    <option value="সেবাগ্রহীতা নাগরিক">সেবাগ্রহীতা নাগরিক</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label for="revTenantName" class="block text-xs font-bold text-neutral-700 mb-1">
                                    ইউনিয়ন পরিষদ বা প্রতিষ্ঠানের নাম <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" id="revTenantName" name="tenant_name" required maxlength="150"
                                       class="ht-field text-xs py-2 px-3 w-full rounded-xl border border-neutral-300 focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600"
                                       placeholder="উদা: আপনার ইউনিয়ন পরিষদ">
                            </div>
                            <div>
                                <label for="revService" class="block text-xs font-bold text-neutral-700 mb-1">
                                    ব্যবহৃত সেবা
                                </label>
                                <input type="text" id="revService" name="service" maxlength="100"
                                       class="ht-field text-xs py-2 px-3 w-full rounded-xl border border-neutral-300 focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600"
                                       placeholder="উদা: হোল্ডিং ট্যাক্স, সনদ ও প্রত্যয়ন">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-neutral-700 mb-1">
                                সামগ্রিক রেটিং (স্টার) <span class="text-rose-500">*</span>
                            </label>
                            <div class="flex items-center gap-2" id="htStarSelector">
                                <input type="hidden" name="rating" id="revRatingInput" value="5">
                                <div class="flex items-center gap-1.5 cursor-pointer text-amber-400 select-none" id="htStarContainer">
                                    <span data-star="1" class="text-2xl hover:scale-110 transition-transform cursor-pointer">★</span>
                                    <span data-star="2" class="text-2xl hover:scale-110 transition-transform cursor-pointer">★</span>
                                    <span data-star="3" class="text-2xl hover:scale-110 transition-transform cursor-pointer">★</span>
                                    <span data-star="4" class="text-2xl hover:scale-110 transition-transform cursor-pointer">★</span>
                                    <span data-star="5" class="text-2xl hover:scale-110 transition-transform cursor-pointer">★</span>
                                </div>
                                <span id="revRatingLabel" class="text-xs font-bold text-neutral-600 ml-2">৫.০ / ৫.০ (অসাধারণ)</span>
                            </div>
                        </div>

                        <div>
                            <label for="revImpact" class="block text-xs font-bold text-neutral-700 mb-1">
                                কী ধরনের সুফল বা প্রভাব পেয়েছেন? (ঐচ্ছিক)
                            </label>
                            <input type="text" id="revImpact" name="impact" maxlength="100"
                                   class="ht-field text-xs py-2 px-3 w-full rounded-xl border border-neutral-300 focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600"
                                   placeholder="উদা: ট্যাক্স আদায় দ্বিগুণ বা সময় বাঁচে ৯০%">
                        </div>

                        <div>
                            <label for="revContent" class="block text-xs font-bold text-neutral-700 mb-1">
                                আপনার বিস্তারিত অভিজ্ঞতা ও মন্তব্য <span class="text-rose-500">*</span>
                            </label>
                            <textarea id="revContent" name="content" required rows="3" maxlength="2000"
                                      class="ht-field text-xs py-2 px-3 w-full rounded-xl border border-neutral-300 focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 resize-none"
                                      placeholder="সফটওয়্যারটির ব্যবহারের অভিজ্ঞতা, সুবিধা বা সেবার মান সম্পর্কে লিখুন…"></textarea>
                        </div>

                        <div id="htReviewSubmitAlert" class="hidden rounded-xl p-3 text-xs leading-relaxed" style="display: none;"></div>

                        <div class="pt-2 flex items-center justify-end gap-2.5">
                            <button type="button" data-dismiss="modal"
                                    class="ht-btn ht-btn-secondary text-xs font-bold py-2.5 px-4 cursor-pointer">
                                বাতিল
                            </button>
                            <button type="submit" id="htReviewSubmitBtn"
                                    class="ht-btn ht-btn-primary text-xs font-bold py-2.5 px-5 cursor-pointer inline-flex items-center gap-1.5">
                                <?= icon('paper-plane-tilt', 14, 'bold') ?>
                                <span>রিভিউ সাবমিট করুন</span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

        <script>
        (function() {
            var starContainer = document.getElementById('htStarContainer');
            var ratingInput   = document.getElementById('revRatingInput');
            var ratingLabel   = document.getElementById('revRatingLabel');
            var reviewForm    = document.getElementById('htReviewSubmitForm');
            var submitBtn     = document.getElementById('htReviewSubmitBtn');
            var alertBox      = document.getElementById('htReviewSubmitAlert');

            var labels = {
                1: '১.০ / ৫.০ (খুবই অসন্তুষ্ট)',
                2: '২.০ / ৫.০ (উন্নতি প্রয়োজন)',
                3: '৩.০ / ৫.০ (মোটামুটি সন্তোষজনক)',
                4: '৪.০ / ৫.০ (ভালো)',
                5: '৫.০ / ৫.০ (অসাধারণ)'
            };

            function updateStars(val) {
                if (ratingInput) ratingInput.value = val;
                if (ratingLabel) ratingLabel.textContent = labels[val] || (val + '.০ / ৫.০');
                if (starContainer) {
                    var stars = starContainer.querySelectorAll('span[data-star]');
                    stars.forEach(function(s, idx) {
                        if (idx < val) {
                            s.style.color = '#f59e0b';
                        } else {
                            s.style.color = '#d1d5db';
                        }
                    });
                }
            }

            if (starContainer) {
                var stars = starContainer.querySelectorAll('span[data-star]');
                stars.forEach(function(star) {
                    star.addEventListener('click', function() {
                        var v = parseInt(this.getAttribute('data-star'), 10) || 5;
                        updateStars(v);
                    });
                });
                updateStars(5);
            }

            if (reviewForm) {
                reviewForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    var author = (document.getElementById('revAuthor').value || '').trim();
                    var role = (document.getElementById('revRole').value || '').trim();
                    var tenantName = (document.getElementById('revTenantName').value || '').trim();
                    var service = (document.getElementById('revService').value || '').trim();
                    var rating = ratingInput ? parseInt(ratingInput.value, 10) : 5;
                    var impact = (document.getElementById('revImpact').value || '').trim();
                    var content = (document.getElementById('revContent').value || '').trim();

                    if (!author || !tenantName || !content) {
                        if (alertBox) {
                            alertBox.className = 'rounded-xl p-3 text-xs leading-relaxed bg-rose-50 text-rose-800 border border-rose-200';
                            alertBox.textContent = 'অনুগ্রহ করে নাম, ইউনিয়ন পরিষদ এবং আপনার মন্তব্য পূরণ করুন।';
                            alertBox.style.display = 'block';
                        }
                        return;
                    }

                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<span>জমা হচ্ছে…</span>';
                    }

                    var formData = new FormData();
                    formData.append('author', author);
                    formData.append('role', role);
                    formData.append('tenant_name', tenantName);
                    formData.append('service', service);
                    formData.append('rating', rating);
                    formData.append('impact', impact);
                    formData.append('content', content);

                    fetch('<?= site_url("api/reviews/submit"); ?>', {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    })
                    .then(function(res) { return res.json(); })
                    .then(function(data) {
                        if (data && data.success) {
                            if (alertBox) {
                                alertBox.className = 'rounded-xl p-3 text-xs leading-relaxed bg-emerald-50 text-emerald-800 border border-emerald-200';
                                alertBox.textContent = data.message || 'আপনার রিভিউ সফলভাবে জমা হয়েছে। অ্যাডমিন পর্যালোচনার পর এটি সাইটে প্রদর্শিত হবে।';
                                alertBox.style.display = 'block';
                            }
                            reviewForm.reset();
                            updateStars(5);
                            setTimeout(function() {
                                var modalEl = document.getElementById('reviewModal');
                                if (modalEl) {
                                    var closeBtn = modalEl.querySelector('[data-dismiss="modal"]');
                                    if (closeBtn) closeBtn.click();
                                }
                                if (alertBox) alertBox.style.display = 'none';
                            }, 2500);
                        } else {
                            if (alertBox) {
                                alertBox.className = 'rounded-xl p-3 text-xs leading-relaxed bg-rose-50 text-rose-800 border border-rose-200';
                                alertBox.textContent = (data && data.message) ? data.message : 'রিভিউ জমা দেওয়া সম্ভব হয়নি। আবার চেষ্টা করুন।';
                                alertBox.style.display = 'block';
                            }
                        }
                    })
                    .catch(function(err) {
                        if (alertBox) {
                            alertBox.className = 'rounded-xl p-3 text-xs leading-relaxed bg-rose-50 text-rose-800 border border-rose-200';
                            alertBox.textContent = 'নেটওয়ার্ক বা সার্ভার সমস্যা। অনুগ্রহ করে আবার চেষ্টা করুন।';
                            alertBox.style.display = 'block';
                        }
                    })
                    .finally(function() {
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = '<?= icon("paper-plane-tilt", 14, "bold") ?> <span>রিভিউ সাবমিট করুন</span>';
                        }
                    });
                });
            }
        })();
        </script>
