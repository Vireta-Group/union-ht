<?php
/**
 * Product-site about page.
 *
 * Ported from resources/js/pages/AboutPage.jsx. It also used
 * resources/js/components/CountUp.jsx, which the home page uses as well, so it
 * stays inline on both pages rather than becoming a shared partial.
 *
 * Three conversions worth knowing about:
 *
 *   - The page-root <div className="py-12 bg-white"> became this <main> and
 *     kept its own classes, because Template::saas() expects the page body
 *     wrapped in <main class="grow">.
 *   - Every navigation button became a real link, and the one support button
 *     became the same data-toggle="modal" / data-target="#supportModal"
 *     contract the rest of the product site uses, which ht-modal.js already
 *     handles. No JavaScript was added to this file.
 *   - The animated counter component became an element carrying
 *     data-saas-countup / data-saas-suffix / data-saas-duration, with the final
 *     formatted value written into the markup so the numbers are correct before
 *     any script runs. saas.js animates them when they scroll into view.
 *     `tabular-nums` is kept from the counter's own wrapper class.
 */

$saas_pillars = array(
    array(
        'icon'  => 'shield-check',
        'title' => 'সম্পূর্ণ নিরাপদ আইসোলেটেড ক্লাউড',
        'desc'  => 'এক ইউনিয়নের ডাটা অন্য ইউনিয়নের সাথে কখনোই মিশবে না। সম্পূর্ণ পৃথক কুয়েরি আইসোলেশন।',
    ),
    array(
        'icon'  => 'file-text',
        'title' => 'সরকারি গেজেট ও ফরমেট শতভাগ অনুসৃত',
        'desc'  => 'স্থানীয় সরকার বিভাগ (LGD) নির্দেশিত সর্বশেষ প্রত্যয়নপত্র ও রসিদ টেমপ্লেট অন্তর্ভুক্ত।',
    ),
    array(
        'icon'  => 'user-check',
        'title' => 'সহজ বাংলা ইউজার ইন্টারফেস',
        'desc'  => 'কোনো বিশেষ আইটি জ্ঞান ছাড়াই উদ্যোক্তা ও সচিব মাত্র ১ দিনেই পুরো সিস্টেম আয়ত্ত করতে পারেন।',
    ),
);

$saas_stats = array(
    array('12',   '+',   '1500', '12+',    'text-indigo-600',  'অনবোর্ডেড ইউনিয়ন পরিষদ'),
    array('5935', '+',   '2000', '5,935+', 'text-amber-700',   'সংরক্ষিত পারিবারিক রেকর্ড'),
    array('4374', '+',   '2200', '4,374+', 'text-emerald-600', 'ডিজিটাল ট্যাক্স ইনভয়েস'),
    array('99',   '.৯%', '1800', '99.৯%',  'text-indigo-900',  'সার্ভিস নির্ভরযোগ্যতা'),
);

$saas_infra = array(
    array('hard-drives',
          'w-12 h-12 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 text-2xl mb-4',
          'স্বয়ংক্রিয় ক্লাউড ব্যাকআপ',
          'প্রতিদিন মধ্যরাতে ইউনিয়ন পরিষদের প্রতিটি ডাটাবেজ ব্যাকআপ হয়ে অফ-সাইট ক্লাউড স্টোরেজে জমা হয়।'),
    array('lock-key',
          'w-12 h-12 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 text-2xl mb-4',
          'এসএসএল ও রিকোয়েস্ট সিকিউরিটি',
          'নাগরিকের তথ্য ও ট্যাক্স লেনদেনের প্রতিটি ডাটা প্যাকেট ২৫৬-বিট এসএসএল এনক্রিপশনের মাধ্যমে স্থানান্তরিত হয়।'),
    array('buildings',
          'w-12 h-12 rounded-xl bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-700 text-2xl mb-4',
          'কাস্টম ডোমেন বাইন্ডিং',
          'প্রতিটি ইউনিয়ন পরিষদ তাদের নিজস্ব সরকারি ডোমেন (`.gov.bd` বা `.com`) এই প্ল্যাটফর্মে কোনো কনফ্লিক্ট ছাড়া বাইন্ড করতে পারে।'),
);
?>

        <main class="grow py-12">

            <!-- 1. Page header -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-16">
                <div class="text-center max-w-3xl mx-auto">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800 mb-4">
                        <?= icon('info', 16, 'bold') ?>
                        <span>আমাদের পরিচিতি ও মিশন</span>
                    </span>
                    <h1 class="text-3xl sm:text-5xl font-extrabold text-neutral-900 tracking-tight leading-tight">
                        বাংলাদেশের ইউনিয়ন পরিষদসমূহকে <br>
                        <span class="text-indigo-600">১০০% ডিজিটাল ও স্বয়ংক্রিয়</span> করার অঙ্গীকার
                    </h1>
                    <p class="mt-4 text-sm sm:text-base text-neutral-600 leading-relaxed">
                        ViretaDev একটি আধুনিক ক্লাউড সফটওয়্যার কোম্পানি। তৃণমূল পর্যায়ের ইউনিয়ন পরিষদকে সনাতন খাতা-কলমের জটিলতা থেকে মুক্ত করে দ্রুত, স্বচ্ছ ও নির্ভরযোগ্য নাগরিক সেবা নিশ্চিত করাই আমাদের প্রধান উদ্দেশ্য।
                    </p>
                </div>
            </div>

            <!-- 2. Story + the four pillars card -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-20">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">

                    <div class="lg:col-span-6 space-y-5 text-sm text-neutral-600 leading-relaxed text-justify">
                        <h2 class="text-2xl sm:text-3xl font-bold text-neutral-900 leading-snug">
                            কেন সনাতন পদ্ধতি বাদ দিয়ে <br>
                            <span class="text-indigo-600">ক্লাউড SaaS মডেলে</span> যাওয়া জরুরি?
                        </h2>
                        <p>
                            ঐতিহ্যগতভাবে প্রতিটি ইউনিয়ন পরিষদে হোল্ডিং ট্যাক্স আদায় ও সনদ বিতরণে ব্যবহৃত হতো বাঁধানো রেজিস্টার খাতা। খাতা ছিঁড়ে যাওয়া, বৃষ্টিতে ভিজে নষ্ট হওয়া, হিসাবের গরমিল এবং ভূয়া সনদের ঝুঁকি ছিল প্রতিনিয়ত।
                        </p>
                        <p>
                            ViretaDev প্ল্যাটফর্মের মাধ্যমে আমরা প্রতিটি ইউনিয়ন পরিষদকে একটি আধুনিক মাল্টি-টেন্যান্ট ক্লাউড কাঠামোর মধ্যে নিয়ে এসেছি। এখানে কম্পিউটার নষ্ট হওয়া বা ডাটা মুছে যাওয়ার কোনো শঙ্কা নেই। প্রতিদিনের ব্যাকআপ স্বয়ংক্রিয়ভাবে সংরক্ষিত থাকে ডেডিকেটেড ক্লাউড স্টোরেজে।
                        </p>
                        <p>
                            চেয়ারম্যান, সচিব এবং হিসাব সহকারী যেকোনো স্থান থেকে মোবাইল বা ল্যাপটপে পরিষদের সব রিপোর্ট ও আদায়কৃত রাজস্ব লাইভ মনিটর করতে পারেন।
                        </p>

                        <div class="pt-2 flex flex-wrap gap-3">
                            <a href="<?= site_url('plans') ?>"
                               class="ht-btn ht-btn-primary text-xs">
                                <span>সাবস্ক্রিপশন প্যাকেজ দেখুন</span>
                                <?= icon('arrow-right', 14, 'bold') ?>
                            </a>
                            <button type="button" data-toggle="modal" data-target="#supportModal"
                                    class="ht-btn ht-btn-secondary text-xs">
                                <span>টেকনিক্যাল টিমের সাথে কথা বলুন</span>
                            </button>
                        </div>
                    </div>

                    <div class="lg:col-span-6">
                        <div class="p-8 sm:p-10 neu-dark-glass text-white relative overflow-hidden">
                            <?= icon('buildings', 180, 'regular', 'absolute -bottom-10 -right-10 text-white/5 pointer-events-none') ?>

                            <span class="text-amber-400 text-xs font-bold uppercase tracking-wider flex items-center gap-1.5">
                                <?= icon('sparkle', 16, 'fill') ?>
                                আমাদের ৪টি মূল ভিত্তি
                            </span>
                            <h3 class="text-xl sm:text-2xl font-bold mt-2 mb-6 leading-snug">
                                স্বচ্ছতা, গতি, নিরাপত্তা ও নাগরিক সন্তুষ্টি
                            </h3>

                            <div class="space-y-4 text-xs sm:text-sm text-indigo-100">
                                <?php foreach ($saas_pillars as $pillar): ?>
                                <div class="flex items-start gap-3 bg-indigo-900/60 p-3.5 rounded-2xl border border-indigo-800">
                                    <?= icon($pillar['icon'], 24, 'fill', 'text-amber-400 shrink-0 mt-0.5') ?>
                                    <div>
                                        <h4 class="font-bold text-white"><?= $pillar['title'] ?></h4>
                                        <p class="text-xs text-indigo-200 mt-0.5"><?= $pillar['desc'] ?></p>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>

                        </div>
                    </div>

                </div>
            </div>

            <!-- 3. Stats counter bar -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-20">
                <div class="p-6 sm:p-8 neu-glass-card">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center divide-y sm:divide-y-0 sm:divide-x divide-neutral-200/60">

                        <?php foreach ($saas_stats as $stat): ?>
                        <div class="p-2 sm:p-4">
                            <span class="block text-3xl sm:text-4xl font-black tabular-nums <?= $stat[4] ?>"
                                  data-saas-countup="<?= $stat[0] ?>" data-saas-suffix="<?= $stat[1] ?>"
                                  data-saas-duration="<?= $stat[2] ?>"><?= $stat[3] ?></span>
                            <span class="text-xs sm:text-sm text-neutral-500 font-medium mt-1 block"><?= $stat[5] ?></span>
                        </div>
                        <?php endforeach; ?>

                    </div>
                </div>
            </div>

            <!-- 4. Technical infrastructure & security highlights -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-20">
                <div class="text-center max-w-2xl mx-auto mb-12">
                    <h2 class="text-2xl sm:text-3xl font-bold text-neutral-900">
                        কারিগরি অবকাঠামো ও ডাটা নিরাপত্তা
                    </h2>
                    <p class="text-xs sm:text-sm text-neutral-500 mt-2">
                        সর্বাধুনিক ক্লাউড প্রযুক্তি ও সুরক্ষা ব্যবস্থা দিয়ে আমাদের প্ল্যাটফর্ম পরিচালিত।
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <?php foreach ($saas_infra as $item): ?>
                    <div class="ht-card neu-glass-card p-6 flex flex-col justify-between">
                        <div class="<?= $item[1] ?>">
                            <?= icon($item[0], 24, 'bold') ?>
                        </div>
                        <h3 class="text-base font-bold text-neutral-900"><?= $item[2] ?></h3>
                        <p class="text-xs text-neutral-500 mt-2 leading-relaxed">
                            <?= $item[3] ?>
                        </p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- 5. Call to action card -->
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="p-8 sm:p-10 neu-dark-glass text-white text-center relative overflow-hidden">
                    <h2 class="text-2xl sm:text-3xl font-bold mb-3">
                        আপনার ইউনিয়ন পরিষদ কি ডিজিটালাইজেশনের জন্য প্রস্তুত?
                    </h2>
                    <p class="text-xs sm:text-sm text-indigo-200 max-w-xl mx-auto mb-6 leading-relaxed">
                        আজই আমাদের লাইভ ডেমো পরীক্ষা করুন অথবা আমাদের বিশেষজ্ঞদের সাথে সরাসরি কথা বলে ডেমো বুক করুন।
                    </p>
                    <div class="flex flex-wrap justify-center gap-3">
                        <a href="<?= site_url('contact') ?>"
                           class="ht-btn ht-btn-primary bg-white text-indigo-900 hover:bg-neutral-100 text-xs font-bold">
                            সরাসরি যোগাযোগ করুন
                        </a>
                        <a href="<?= site_url('plans') ?>"
                           class="ht-btn ht-btn-secondary text-xs text-white border-white/30 hover:bg-white/10">
                            প্যাকেজসমূহ দেখুন
                        </a>
                    </div>
                </div>
            </div>

        </main>
