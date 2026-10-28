<?php
    $CI =& get_instance();
    $CI->load->model('FamilyModel');
?>

<!-- Welcome Banner & Quick CTA -->
<div class="rounded-2xl p-5 sm:p-6 text-white shadow-md relative overflow-hidden" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #1e1b4b 100%);">
    <div class="absolute -right-10 -bottom-10 opacity-10 text-9xl pointer-events-none">
        <i class="ph-fill ph-buildings"></i>
    </div>
    <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4 sm:gap-6">
        <div class="space-y-1.5">
            <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full bg-white/10 text-indigo-200 text-[11px] font-semibold backdrop-blur-xs mb-0.5">
                <i class="ph-bold ph-sparkle text-amber-400"></i>
                <span>স্মার্ট ইউনিয়ন পরিষদ পোর্টাল</span>
            </div>
            <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight text-white leading-tight">
                স্বাগতম, <?= htmlspecialchars($loggedUser['userName'] ?? 'ইউজার', ENT_QUOTES, 'UTF-8'); ?>!
            </h2>
            <p class="text-xs text-indigo-200 max-w-xl leading-relaxed">
                <?= htmlspecialchars($ins_info->ins_name ?? 'ইউনিয়ন পরিষদ', ENT_QUOTES, 'UTF-8'); ?> এর অনলাইন হোল্ডিং কর আদায়, সনদ প্রদান ও নাগরিক সেবা পোর্টাল পরিচালনা করুন।
            </p>
        </div>
        <div class="self-start md:self-center flex flex-wrap items-center gap-2 shrink-0">
            <a href="taxCollection" class="px-3.5 py-2 rounded-xl bg-white text-indigo-950 hover:bg-neutral-100 active:scale-98 font-bold text-xs inline-flex items-center gap-1.5 shadow-xs transition-all cursor-pointer shrink-0">
                <i class="ph-bold ph-hand-coins text-sm text-indigo-600"></i>
                <span>ট্যাক্স আদায় করুন</span>
            </a>
            <a href="searchHoldingss" class="px-3.5 py-2 rounded-xl bg-white/10 hover:bg-white/20 active:scale-98 text-white font-semibold text-xs inline-flex items-center gap-1.5 backdrop-blur-xs border border-white/20 transition-all cursor-pointer shrink-0">
                <i class="ph-bold ph-magnifying-glass text-sm"></i>
                <span>হোল্ডিং খুঁজুন</span>
            </a>
        </div>
    </div>
</div>

<!-- 6 KPI Summary Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5 sm:gap-4">
    
    <!-- Card 1: Total Holdings -->
    <div class="bg-white rounded-2xl p-4 sm:p-4.5 border border-neutral-200/80 shadow-xs hover:shadow-md transition-all flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-neutral-500 tracking-wide">মোট হোল্ডিং সংখ্যা</span>
                <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg shrink-0">
                    <i class="ph-fill ph-house-line"></i>
                </div>
            </div>
            <div>
                <div class="text-2xl font-black text-neutral-900 bengali-num leading-tight">
                    <?= bn_num(number_format($total_holdings ?? 0)); ?>
                    <span class="text-xs font-semibold text-neutral-500 ml-0.5">টি</span>
                </div>
                <p class="text-[11px] text-neutral-400 mt-0.5">নিবন্ধিত পরিবার / খানা</p>
            </div>
        </div>
        <div class="mt-3 pt-2.5 border-t border-neutral-100 flex items-center justify-between text-[11px]">
            <span class="text-indigo-600 font-semibold flex items-center gap-1">
                <i class="ph-bold ph-check-circle"></i> ১০০% সুরক্ষিত
            </span>
            <a href="view_datas" class="text-neutral-400 hover:text-indigo-600 font-medium transition-colors">তালিকা &rarr;</a>
        </div>
    </div>

    <!-- Card 2: Total Tax Collected -->
    <div class="bg-white rounded-2xl p-4 sm:p-4.5 border border-neutral-200/80 shadow-xs hover:shadow-md transition-all flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-neutral-500 tracking-wide">মোট কর আদায়</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg shrink-0">
                    <i class="ph-fill ph-wallet"></i>
                </div>
            </div>
            <div>
                <div class="text-2xl font-black text-neutral-900 bengali-num leading-tight">
                    &#2547; <?= bn_num(number_format($total_tax ?? 0)); ?>
                </div>
                <p class="text-[11px] text-neutral-400 mt-0.5">সর্বমোট রাজস্ব সংগ্রহ (নেট)</p>
            </div>
        </div>
        <div class="mt-3 pt-2.5 border-t border-neutral-100 flex items-center justify-between text-[11px]">
            <span class="text-emerald-600 font-semibold flex items-center gap-1">
                <i class="ph-bold ph-trend-up"></i> আদায়কৃত রাজস্ব
            </span>
            <a href="receipt_show" class="text-neutral-400 hover:text-emerald-600 font-medium transition-colors">রশিদ &rarr;</a>
        </div>
    </div>

    <!-- Card 3: Total Discount / Relief -->
    <div class="bg-white rounded-2xl p-4 sm:p-4.5 border border-neutral-200/80 shadow-xs hover:shadow-md transition-all flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-neutral-500 tracking-wide">মোট ছাড় / মওকুফ</span>
                <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center text-lg shrink-0">
                    <i class="ph-fill ph-tag"></i>
                </div>
            </div>
            <div>
                <div class="text-2xl font-black text-neutral-900 bengali-num leading-tight">
                    &#2547; <?= bn_num(number_format($total_discount ?? 0)); ?>
                </div>
                <p class="text-[11px] text-neutral-400 mt-0.5">মওকুফকৃত রাজস্ব সহায়তা</p>
            </div>
        </div>
        <div class="mt-3 pt-2.5 border-t border-neutral-100 flex items-center justify-between text-[11px]">
            <span class="text-rose-600 font-semibold flex items-center gap-1" title="মূল কর দাবি (গ্রস)">
                <i class="ph-bold ph-receipt"></i> দাবি: &#2547;<?= bn_num(number_format($total_gross_tax ?? 0)); ?>
            </span>
            <a href="receipt_show" class="text-neutral-400 hover:text-rose-600 font-medium transition-colors">বিবরণ &rarr;</a>
        </div>
    </div>

    <!-- Card 4: Today's Collection -->
    <div class="bg-white rounded-2xl p-4 sm:p-4.5 border border-neutral-200/80 shadow-xs hover:shadow-md transition-all flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-neutral-500 tracking-wide">আজকের আদায়</span>
                <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-lg shrink-0">
                    <i class="ph-fill ph-calendar-check"></i>
                </div>
            </div>
            <div>
                <div class="text-2xl font-black text-neutral-900 bengali-num leading-tight">
                    &#2547; <?= bn_num(number_format($today_tax ?? 0)); ?>
                </div>
                <p class="text-[11px] text-neutral-400 mt-0.5"><?= bn_num(date('d/m/Y')); ?> এর কালেকশন</p>
            </div>
        </div>
        <div class="mt-3 pt-2.5 border-t border-neutral-100 flex items-center justify-between text-[11px]">
            <span class="text-amber-600 font-semibold flex items-center gap-1">
                <i class="ph-bold ph-clock"></i> আজকের হিসাব
            </span>
            <a href="taxCollection" class="text-neutral-400 hover:text-amber-600 font-medium transition-colors">আদায় &rarr;</a>
        </div>
    </div>

    <!-- Card 5: Issued Sonod -->
    <div class="bg-white rounded-2xl p-4 sm:p-4.5 border border-neutral-200/80 shadow-xs hover:shadow-md transition-all flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-neutral-500 tracking-wide">ইস্যুকৃত সনদ</span>
                <div class="w-8 h-8 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center text-lg shrink-0">
                    <i class="ph-fill ph-certificate"></i>
                </div>
            </div>
            <div>
                <div class="text-2xl font-black text-neutral-900 bengali-num leading-tight">
                    <?= bn_num(number_format($total_sonod ?? 0)); ?>
                    <span class="text-xs font-semibold text-neutral-500 ml-0.5">টি</span>
                </div>
                <p class="text-[11px] text-neutral-400 mt-0.5">অনলাইন নাগরিক সনদ</p>
            </div>
        </div>
        <div class="mt-3 pt-2.5 border-t border-neutral-100 flex items-center justify-between text-[11px]">
            <span class="text-sky-600 font-semibold flex items-center gap-1">
                <i class="ph-bold ph-shield-check"></i> ভেরিফায়েড
            </span>
            <a href="newSonod" class="text-neutral-400 hover:text-sky-600 font-medium transition-colors">নতুন সনদ &rarr;</a>
        </div>
    </div>

    <!-- Card 6: Total Cash with UDC & Tax Collectors -->
    <div class="bg-white rounded-2xl p-4 sm:p-4.5 border border-neutral-200/80 shadow-xs hover:shadow-md transition-all flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-neutral-500 tracking-wide">কালেক্টরের কাছে জমা</span>
                <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center text-lg shrink-0">
                    <i class="ph-fill ph-hand-coins"></i>
                </div>
            </div>
            <div>
                <div class="text-2xl font-black text-neutral-900 bengali-num leading-tight">
                    &#2547; <?= bn_num(number_format($total_collector_due ?? 0)); ?>
                </div>
                <p class="text-[11px] text-neutral-400 mt-0.5">ইউডিসি ও আদায়কারীর কাছে বকেয়া</p>
            </div>
        </div>
        <div class="mt-3 pt-2.5 border-t border-neutral-100 flex items-center justify-between text-[11px]">
            <span class="text-purple-600 font-semibold flex items-center gap-1">
                <i class="ph-bold ph-vault"></i> প্রদেয় নগদ রাজস্ব
            </span>
            <a href="cashDeposite" class="text-neutral-400 hover:text-purple-600 font-medium transition-colors">নগদ জমা &rarr;</a>
        </div>
    </div>

</div>

<!-- Quick Actions Grid -->
<div class="bg-white rounded-2xl p-5 sm:p-6 border border-neutral-200/80 shadow-xs">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg shrink-0">
                <i class="ph-bold ph-squares-four"></i>
            </div>
            <h3 class="text-base sm:text-lg font-bold text-neutral-900">দ্রুত সেবা ও কার্যক্রম (Quick Actions)</h3>
        </div>

        <span class="text-xs text-neutral-400">৮টি সার্বক্ষণিক টুলস</span>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5 sm:gap-4">
        
        <!-- 1. Tax Collection -->
        <a href="taxCollection" class="group p-4 rounded-2xl border border-neutral-200/80 bg-neutral-50/50 hover:bg-white hover:border-indigo-300 hover:shadow-md transition-all flex flex-col items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-xl shadow-xs shrink-0 group-hover:scale-105 transition-transform">
                <i class="ph-bold ph-hand-coins"></i>
            </div>
            <div>
                <strong class="block text-xs sm:text-sm font-bold text-neutral-900 group-hover:text-indigo-600 transition-colors">ট্যাক্স আদায়</strong>
                <span class="text-[11px] text-neutral-500">কর সংগ্রহ ও রশিদ</span>
            </div>
        </a>

        <!-- 2. Search Holdings -->
        <a href="searchHoldingss" class="group p-4 rounded-2xl border border-neutral-200/80 bg-neutral-50/50 hover:bg-white hover:border-sky-300 hover:shadow-md transition-all flex flex-col items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-sky-600 text-white flex items-center justify-center text-xl shadow-xs shrink-0 group-hover:scale-105 transition-transform">
                <i class="ph-bold ph-magnifying-glass"></i>
            </div>
            <div>
                <strong class="block text-xs sm:text-sm font-bold text-neutral-900 group-hover:text-sky-600 transition-colors">হোল্ডিং খুঁজুন</strong>
                <span class="text-[11px] text-neutral-500">মালিক ও খানা তথ্য</span>
            </div>
        </a>

        <!-- 3. Receipt Print -->
        <a href="receipt_show" class="group p-4 rounded-2xl border border-neutral-200/80 bg-neutral-50/50 hover:bg-white hover:border-emerald-300 hover:shadow-md transition-all flex flex-col items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-xl shadow-xs shrink-0 group-hover:scale-105 transition-transform">
                <i class="ph-bold ph-receipt"></i>
            </div>
            <div>
                <strong class="block text-xs sm:text-sm font-bold text-neutral-900 group-hover:text-emerald-600 transition-colors">রশিদ প্রিন্ট</strong>
                <span class="text-[11px] text-neutral-500">পূর্বে আদায়কৃত ভাউচার</span>
            </div>
        </a>

        <!-- 4. Tax Report -->
        <a href="reports" class="group p-4 rounded-2xl border border-neutral-200/80 bg-neutral-50/50 hover:bg-white hover:border-rose-300 hover:shadow-md transition-all flex flex-col items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-rose-600 text-white flex items-center justify-center text-xl shadow-xs shrink-0 group-hover:scale-105 transition-transform">
                <i class="ph-bold ph-chart-bar"></i>
            </div>
            <div>
                <strong class="block text-xs sm:text-sm font-bold text-neutral-900 group-hover:text-rose-600 transition-colors">ট্যাক্স রিপোর্ট</strong>
                <span class="text-[11px] text-neutral-500">আদায় ও বকেয়া হিসাব</span>
            </div>
        </a>

        <!-- 5. Add Holding -->
        <a href="addData" class="group p-4 rounded-2xl border border-neutral-200/80 bg-neutral-50/50 hover:bg-white hover:border-indigo-300 hover:shadow-md transition-all flex flex-col items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-500 text-white flex items-center justify-center text-xl shadow-xs shrink-0 group-hover:scale-105 transition-transform">
                <i class="ph-bold ph-user-plus"></i>
            </div>
            <div>
                <strong class="block text-xs sm:text-sm font-bold text-neutral-900 group-hover:text-indigo-600 transition-colors">নতুন হোল্ডিং যোগ</strong>
                <span class="text-[11px] text-neutral-500">নতুন পরিবার এন্ট্রি</span>
            </div>
        </a>

        <!-- 6. New Sonod -->
        <a href="newSonod" class="group p-4 rounded-2xl border border-neutral-200/80 bg-neutral-50/50 hover:bg-white hover:border-sky-300 hover:shadow-md transition-all flex flex-col items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-sky-500 text-white flex items-center justify-center text-xl shadow-xs shrink-0 group-hover:scale-105 transition-transform">
                <i class="ph-bold ph-certificate"></i>
            </div>
            <div>
                <strong class="block text-xs sm:text-sm font-bold text-neutral-900 group-hover:text-sky-600 transition-colors">নতুন সনদ তৈরি</strong>
                <span class="text-[11px] text-neutral-500">অনলাইন নাগরিক সনদ</span>
            </div>
        </a>

        <!-- 7. Word Doc Download -->
        <a href="view_data_doc" class="group p-4 rounded-2xl border border-neutral-200/80 bg-neutral-50/50 hover:bg-white hover:border-amber-300 hover:shadow-md transition-all flex flex-col items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center text-xl shadow-xs shrink-0 group-hover:scale-105 transition-transform">
                <i class="ph-bold ph-file-arrow-down"></i>
            </div>
            <div>
                <strong class="block text-xs sm:text-sm font-bold text-neutral-900 group-hover:text-amber-600 transition-colors">ওয়ার্ডভিত্তিক Doc</strong>
                <span class="text-[11px] text-neutral-500">তালিকা ডাউনলোড</span>
            </div>
        </a>

        <!-- 8. User Management -->
        <a href="userManagement" class="group p-4 rounded-2xl border border-neutral-200/80 bg-neutral-50/50 hover:bg-white hover:border-neutral-400 hover:shadow-md transition-all flex flex-col items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-neutral-800 text-white flex items-center justify-center text-xl shadow-xs shrink-0 group-hover:scale-105 transition-transform">
                <i class="ph-bold ph-users-three"></i>
            </div>
            <div>
                <strong class="block text-xs sm:text-sm font-bold text-neutral-900 group-hover:text-neutral-700 transition-colors">ইউজার ম্যানেজমেন্ট</strong>
                <span class="text-[11px] text-neutral-500">কর্মী ও সংগ্রাহক লিস্ট</span>
            </div>
        </a>

    </div>
</div>

<!-- 2-Column Section: Recent Collections & Collector Performance -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

    <!-- Left: Recent Collections Table (7 cols) -->
    <div class="lg:col-span-7 bg-white rounded-3xl p-6 border border-neutral-200/80 shadow-xs flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                        <i class="ph-bold ph-receipt"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-neutral-900">সাম্প্রতিক ট্যাক্স আদায়</h3>
                        <p class="text-xs text-neutral-400">সর্বশেষ পরিশোধিত রশিদের তথ্য</p>
                    </div>
                </div>
                <a href="receipt_show" class="text-xs font-bold text-indigo-600 hover:text-indigo-700 flex items-center gap-1">
                    <span>সকল রশিদ দেখুন</span>
                    <i class="ph-bold ph-arrow-right"></i>
                </a>
            </div>

            <div class="overflow-x-auto -mx-6 px-6">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-neutral-100 text-neutral-400 font-bold uppercase tracking-wider">
                            <th class="py-2.5">রশিদ নং</th>
                            <th class="py-2.5">করদাতার নাম</th>
                            <th class="py-2.5 text-center">হোল্ডিং ও ওয়ার্ড</th>
                            <th class="py-2.5 text-right">আদায়ের পরিমাণ</th>
                            <th class="py-2.5 text-right">তারিখ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100">
                        <?php if (!empty($recent_collections)): ?>
                            <?php foreach ($recent_collections as $item): ?>
                                <tr class="hover:bg-neutral-50/80 transition-colors">
                                    <td class="py-3 font-bold text-indigo-600 bengali-num">
                                        <a href="receipt_show?invoice=<?= htmlspecialchars($item->invoice_no_s, ENT_QUOTES, 'UTF-8'); ?>" class="hover:underline">
                                            #<?= bn_num($item->invoice_no_s); ?>
                                        </a>
                                    </td>
                                    <td class="py-3 font-semibold text-neutral-900">
                                        <?= htmlspecialchars($item->family_person_name_head ?? 'অজ্ঞাত', ENT_QUOTES, 'UTF-8'); ?>
                                    </td>
                                    <td class="py-3 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-neutral-100 text-neutral-700 mr-1 bengali-num">
                                            হোল্ডিং: <?= bn_num($item->holding_number ?? '-'); ?>
                                        </span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-indigo-50 text-indigo-700 bengali-num">
                                            ওয়ার্ড: <?= bn_num($item->word_number ?? '-'); ?>
                                        </span>
                                    </td>
                                    <td class="py-3 text-right font-bold text-emerald-600 bengali-num">
                                        &#2547; <?= bn_num(number_format((float)$item->amnt_taka)); ?>/-
                                    </td>
                                    <td class="py-3 text-right text-neutral-400 bengali-num">
                                        <?= bn_num(date('d/m/Y', strtotime($item->date_s_today_s))); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="py-8 text-center text-neutral-400">
                                    <i class="ph-bold ph-receipt text-3xl text-neutral-300 block mb-1"></i>
                                    এখনো কোনো কর আদায়ের তথ্য পাওয়া যায়নি।
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4 pt-3 border-t border-neutral-100 text-center text-xs text-neutral-400">
            সর্বশেষ ৬টি লাইভ লেনদেন প্রদর্শিত হচ্ছে
        </div>
    </div>

    <!-- Right: Collector Performance (5 cols) -->
    <div class="lg:col-span-5 bg-white rounded-3xl p-6 border border-neutral-200/80 shadow-xs flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg">
                        <i class="ph-bold ph-users"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-neutral-900">আদায়কারী কালেকশন বিবরণী</h3>
                        <p class="text-xs text-neutral-400">কর্মী ও সংগ্রাহকদের পারফরম্যান্স</p>
                    </div>
                </div>
                <?php if ($this->ion_auth->is_admin()): ?>
                    <a href="userManagement" class="text-xs font-bold text-indigo-600 hover:text-indigo-700">ইউজার তালিকা &rarr;</a>
                <?php endif; ?>
            </div>

            <div class="space-y-3">
                <?php 
                    $totalDues = [];
                    $totalCollectedSum = 0;
                    if (!empty($user_infos)):
                        foreach ($user_infos as $user): 
                            $user_collected = (int)$CI->FamilyModel->get_user_collect_amount($user->udc_list_auto_p_iidd);
                            $user_cutting   = (int)$CI->FamilyModel->get_user_cutting_amount($user->udc_list_auto_p_iidd);
                            $percentage     = (float)$user->percentager;
                            $commission     = ($user_collected * $percentage / 100);
                            $net_due        = ($user_collected - $user_cutting) - $commission;
                            $totalDues[]    = $net_due;
                            $totalCollectedSum += $user_collected;
                ?>
                    <div class="p-3.5 rounded-2xl bg-neutral-50/70 border border-neutral-100 flex items-center justify-between hover:bg-neutral-50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-700 font-bold text-sm flex items-center justify-center border border-indigo-200">
                                <?= mb_substr($user->user_person_name, 0, 1, 'UTF-8'); ?>
                            </div>
                            <div>
                                <div class="font-bold text-xs sm:text-sm text-neutral-900"><?= htmlspecialchars($user->user_person_name, ENT_QUOTES, 'UTF-8'); ?></div>
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    <?php 
                                        $role_badge_map = [
                                            'uphead'  => ['title' => 'ইউপি চেয়ারম্যান', 'cls' => 'bg-amber-100 text-amber-800'],
                                            'upsocib' => ['title' => 'ইউপি সচিব', 'cls' => 'bg-sky-100 text-sky-800'],
                                            'udc'     => ['title' => 'ইউডিসি উদ্যোক্তা', 'cls' => 'bg-emerald-100 text-emerald-800'],
                                            'collect' => ['title' => 'ট্যাক্স আদায়কারী', 'cls' => 'bg-indigo-100 text-indigo-800'],
                                            'admin'   => ['title' => 'ইউপি চেয়ারম্যান', 'cls' => 'bg-amber-100 text-amber-800'],
                                        ];
                                        $user_rkey = $user->name ?? 'collect';
                                        $r_info = $role_badge_map[$user_rkey] ?? ['title' => 'ট্যাক্স আদায়কারী', 'cls' => 'bg-neutral-100 text-neutral-700'];
                                    ?>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold <?= $r_info['cls'] ?>">
                                        <?= htmlspecialchars($r_info['title'], ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                    <?php if ($percentage > 0): ?>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">
                                            কমিশন: <?= bn_num($percentage); ?>%
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-black text-neutral-900 bengali-num">
                                &#2547; <?= bn_num(number_format($user_collected)); ?>
                            </div>
                            <div class="text-[11px] text-neutral-400 bengali-num">
                                প্রদেয়: <span class="text-rose-600 font-bold">&#2547; <?= bn_num(number_format($net_due)); ?></span>
                            </div>
                        </div>
                    </div>
                <?php 
                        endforeach;
                    else:
                ?>
                    <div class="py-8 text-center text-neutral-400 text-xs">কোনো ব্যবহারকারীর তথ্য পাওয়া যায়নি।</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Summary Footer -->
        <div class="mt-4 pt-3.5 border-t border-neutral-100 bg-neutral-50/50 -mx-6 -mb-6 p-4 rounded-b-3xl flex items-center justify-between">
            <div>
                <span class="block text-xs font-bold text-neutral-700">মোট ইউজার আদায়:</span>
                <span class="block text-[11px] text-neutral-400 bengali-num">
                    মোট প্রদেয় বকেয়া: <strong class="text-rose-600">&#2547; <?= bn_num(number_format(array_sum($totalDues))); ?>/-</strong>
                </span>
            </div>
            <div class="text-base sm:text-lg font-black text-indigo-700 bengali-num">
                &#2547; <?= bn_num(number_format($totalCollectedSum)); ?>/-
            </div>
        </div>

    </div>

</div>
