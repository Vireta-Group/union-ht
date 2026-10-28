<!-- Breadcrumb & Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-neutral-200">
    <div>
        <nav class="flex items-center gap-2 text-xs text-neutral-500 mb-1">
            <a href="dashboard" class="hover:text-indigo-600 transition flex items-center gap-1">
                <i class="ph ph-house text-sm"></i> ড্যাশবোর্ড
            </a>
            <i class="ph ph-caret-right text-[10px] text-neutral-400"></i>
            <span class="text-neutral-800 font-medium">রিপোর্ট</span>
        </nav>
        <h1 class="text-xl sm:text-2xl font-bold text-neutral-900 tracking-tight flex items-center gap-2">
            <span class="w-2.5 h-6 bg-indigo-600 rounded-full inline-block"></span>
            আদায় ও পরিশোধ রিপোর্ট
        </h1>
        <p class="text-xs sm:text-sm text-neutral-500 mt-0.5">তারিখ অনুযায়ী সংগ্রহ ও পরিশোধের বিস্তারিত প্রতিবেদন</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Collection Reports Card -->
    <div class="bg-white rounded-2xl border border-neutral-200/80 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-neutral-100 flex items-center gap-2">
            <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                <i class="ph-bold ph-arrows-in text-base"></i>
            </div>
            <h3 class="text-sm font-bold text-neutral-900">কর সংগ্রহ রিপোর্ট</h3>
        </div>
        <div class="p-5 space-y-5">

            <!-- Overall Collect Report -->
            <div class="space-y-2">
                <h4 class="text-xs font-bold text-neutral-700 uppercase tracking-wide">সামগ্রিক সংগ্রহ (তারিখ ভিত্তিক)</h4>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[11px] font-semibold text-neutral-600 mb-1">শুরুর তারিখ</label>
                        <input type="text" class="w-full text-xs px-3 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition datePicks startDates" placeholder="YYYY-MM-DD">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-neutral-600 mb-1">শেষ তারিখ</label>
                        <input type="text" class="w-full text-xs px-3 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition datePicks endDates" placeholder="YYYY-MM-DD">
                    </div>
                </div>
                <button type="button" class="w-full py-1.5 px-3.5 bg-blue-600 hover:bg-blue-700 active:scale-98 text-white rounded-xl text-xs font-semibold shadow-2xs transition flex items-center justify-center gap-1.5 showCollectDetailsReportBtn">
                    <i class="ph-bold ph-file-text text-xs"></i> রিপোর্ট দেখুন
                </button>
            </div>

            <hr class="border-neutral-100">

            <!-- Per-User Collect Report -->
            <div class="space-y-2">
                <h4 class="text-xs font-bold text-neutral-700 uppercase tracking-wide">আদায়কারী অনুযায়ী সংগ্রহ</h4>
                <div>
                    <label class="block text-[11px] font-semibold text-neutral-600 mb-1">আদায়কারী নির্বাচন করুন</label>
                    <select class="w-full text-xs px-3 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition userUniqs">
                        <option value="">-- আদায়কারী নির্বাচন করুন --</option>
                        <?php foreach (($users_info ?? []) as $user) { ?>
                            <option value="<?= htmlspecialchars($user->user_uniq_qq_reails ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                <?= htmlspecialchars($user->user_person_name ?? '', ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[11px] font-semibold text-neutral-600 mb-1">শুরুর তারিখ</label>
                        <input type="text" class="w-full text-xs px-3 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition datePicks strt_UCDates" placeholder="YYYY-MM-DD">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-neutral-600 mb-1">শেষ তারিখ</label>
                        <input type="text" class="w-full text-xs px-3 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition datePicks end_UCDate" placeholder="YYYY-MM-DD">
                    </div>
                </div>
                <button type="button" class="w-full py-1.5 px-3.5 bg-indigo-600 hover:bg-indigo-700 active:scale-98 text-white rounded-xl text-xs font-semibold shadow-2xs transition flex items-center justify-center gap-1.5 showUserCollectDetailsReportBtn">
                    <i class="ph-bold ph-user-list text-xs"></i> ব্যক্তিভিত্তিক সংগ্রহ রিপোর্ট
                </button>
            </div>
        </div>
    </div>

    <!-- Payment Reports Card -->
    <div class="bg-white rounded-2xl border border-neutral-200/80 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-neutral-100 flex items-center gap-2">
            <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <i class="ph-bold ph-arrows-out text-base"></i>
            </div>
            <h3 class="text-sm font-bold text-neutral-900">কর পরিশোধ রিপোর্ট</h3>
        </div>
        <div class="p-5 space-y-5">

            <!-- Overall Paid Report -->
            <div class="space-y-2">
                <h4 class="text-xs font-bold text-neutral-700 uppercase tracking-wide">সামগ্রিক পরিশোধ (তারিখ ভিত্তিক)</h4>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[11px] font-semibold text-neutral-600 mb-1">শুরুর তারিখ</label>
                        <input type="text" class="w-full text-xs px-3 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition datePicks strtDates" placeholder="YYYY-MM-DD">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-neutral-600 mb-1">শেষ তারিখ</label>
                        <input type="text" class="w-full text-xs px-3 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition datePicks endingDates" placeholder="YYYY-MM-DD">
                    </div>
                </div>
                <button type="button" class="w-full py-1.5 px-3.5 bg-emerald-600 hover:bg-emerald-700 active:scale-98 text-white rounded-xl text-xs font-semibold shadow-2xs transition flex items-center justify-center gap-1.5 showPaidDetailsReportBtn">
                    <i class="ph-bold ph-file-text text-xs"></i> রিপোর্ট দেখুন
                </button>
            </div>

            <hr class="border-neutral-100">

            <!-- Per-User Paid Report -->
            <div class="space-y-2">
                <h4 class="text-xs font-bold text-neutral-700 uppercase tracking-wide">আদায়কারী অনুযায়ী পরিশোধ</h4>
                <div>
                    <label class="block text-[11px] font-semibold text-neutral-600 mb-1">আদায়কারী নির্বাচন করুন</label>
                    <select class="w-full text-xs px-3 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition userUniq_s">
                        <option value="">-- আদায়কারী নির্বাচন করুন --</option>
                        <?php foreach (($users_info ?? []) as $user) { ?>
                            <option value="<?= htmlspecialchars($user->user_uniq_qq_reails ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                <?= htmlspecialchars($user->user_person_name ?? '', ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[11px] font-semibold text-neutral-600 mb-1">শুরুর তারিখ</label>
                        <input type="text" class="w-full text-xs px-3 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition datePicks strt_UP_Dates" placeholder="YYYY-MM-DD">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-neutral-600 mb-1">শেষ তারিখ</label>
                        <input type="text" class="w-full text-xs px-3 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition datePicks end_UP_Date" placeholder="YYYY-MM-DD">
                    </div>
                </div>
                <button type="button" class="w-full py-1.5 px-3.5 bg-teal-600 hover:bg-teal-700 active:scale-98 text-white rounded-xl text-xs font-semibold shadow-2xs transition flex items-center justify-center gap-1.5 showUserPaidDetailsReportBtns">
                    <i class="ph-bold ph-user-list text-xs"></i> ব্যক্তিভিত্তিক পরিশোধ রিপোর্ট
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).on('click', '.showCollectDetailsReportBtn', function () {
        let startDates = $('.startDates').val() || 'today';
        let endDates = $('.endDates').val() || 'today';
        let url_link = 'AllCollectreport?sDate=' + startDates + '&eDates=' + endDates;
        window.open(url_link, '_blank', 'location=yes,height=800,width=800,scrollbars=yes,status=yes');
    });

    $(document).on('click', '.showPaidDetailsReportBtn', function () {
        let strtDates = $('.strtDates').val() || 'today';
        let endDate = $('.endingDates').val() || 'today';
        let urlLink = 'AllPaidReport?sDate=' + strtDates + '&eDates=' + endDate;
        window.open(urlLink, '_blank', 'location=yes,height=800,width=800,scrollbars=yes,status=yes');
    });

    $(document).on('click', '.showUserCollectDetailsReportBtn', function () {
        let startDates = $('.strt_UCDates').val() || 'today';
        let endDates = $('.end_UCDate').val() || 'today';
        let userUniq = $('.userUniqs').val();
        let url_link = 'userCollectHistory?sDate=' + startDates + '&eDates=' + endDates + '&check=' + userUniq;
        window.open(url_link, '_blank', 'location=yes,height=800,width=800,scrollbars=yes,status=yes');
    });

    $(document).on('click', '.showUserPaidDetailsReportBtns', function () {
        let startDate = $('.strt_UP_Dates').val() || 'today';
        let endDate = $('.end_UP_Date').val() || 'today';
        let userUniqs = $('.userUniq_s').val();
        let url_links = 'userPaidHistory?sDate=' + startDate + '&eDates=' + endDate + '&check=' + userUniqs;
        window.open(url_links, '_blank', 'location=yes,height=800,width=800,scrollbars=yes,status=yes');
    });
</script>