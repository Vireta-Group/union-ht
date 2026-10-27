<?php
$totalDues = (float)($totalCollects - $totalPaids) - ((float)$totalCollects * (float)($user_infos->percentager ?? 0) / 100);
$checkUniq = htmlspecialchars($_GET['check'] ?? ($user_infos->user_uniq_qq_reails ?? ''), ENT_QUOTES, 'UTF-8');
?>

<!-- Breadcrumb & Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-neutral-200">
    <div>
        <nav class="flex items-center gap-2 text-xs text-neutral-500 mb-1">
            <a href="dashboard" class="hover:text-indigo-600 transition flex items-center gap-1">
                <i class="ph ph-house text-sm"></i> ড্যাশবোর্ড
            </a>
            <i class="ph ph-caret-right text-[10px] text-neutral-400"></i>
            <a href="userManagement" class="hover:text-indigo-600 transition">
                ব্যবহারকারী ব্যবস্থাপনা
            </a>
            <i class="ph ph-caret-right text-[10px] text-neutral-400"></i>
            <span class="text-neutral-800 font-medium">প্রোফাইল</span>
        </nav>
        <h1 class="text-lg sm:text-xl font-bold text-neutral-900 tracking-tight flex items-center gap-2">
            <span class="w-2 h-5 bg-indigo-600 rounded-full inline-block"></span>
            ব্যবহারকারী ও আদায়কারী প্রোফাইল
        </h1>
    </div>
    <div class="flex items-center gap-2">
        <a href="userManagement" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-neutral-700 bg-white border border-neutral-300 hover:bg-neutral-100 shadow-2xs transition">
            <i class="ph ph-arrow-left text-xs"></i>
            <span>তালিকায় ফিরে যান</span>
        </a>
        <button type="button" data-toggle="modal" data-target="#edit_user" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 active:scale-98 shadow-2xs transition cursor-pointer">
            <i class="ph-bold ph-pencil-simple text-xs"></i>
            <span>তথ্য সম্পাদনা</span>
        </button>
    </div>
</div>

<!-- Alert Message Container -->
<div class="success_alert_msg my-2"></div>

<!-- Profile Info Banner -->
<div class="bg-white rounded-2xl border border-neutral-200/80 shadow-2xs p-4 sm:p-5 my-3">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white font-bold text-lg flex items-center justify-center shadow-xs shrink-0">
                <?= strtoupper(mb_substr($user_infos->user_person_name ?? 'U', 0, 1, 'UTF-8')); ?>
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="text-base font-bold text-neutral-900">
                        <?= htmlspecialchars($user_infos->user_person_name ?? 'ব্যবহারকারী', ENT_QUOTES); ?>
                    </h2>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200/80">
                        <?= htmlspecialchars($user_infos->name ?? ($user_infos->u_type ?? 'আদায়কারী'), ENT_QUOTES); ?>
                    </span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200/80">
                        কমিশন: <?= htmlspecialchars($user_infos->percentager ?? '0', ENT_QUOTES); ?>%
                    </span>
                </div>
                <div class="flex flex-wrap items-center gap-y-1 gap-x-4 mt-1.5 text-xs text-neutral-500">
                    <?php if (!empty($user_infos->user_phone_no)): ?>
                        <span class="flex items-center gap-1">
                            <i class="ph ph-phone text-neutral-400"></i>
                            <span class="font-mono text-neutral-700"><?= htmlspecialchars($user_infos->user_phone_no, ENT_QUOTES); ?></span>
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($user_infos->email) || !empty($user_infos->user_email_no)): ?>
                        <span class="flex items-center gap-1">
                            <i class="ph ph-envelope-simple text-neutral-400"></i>
                            <span class="text-neutral-700"><?= htmlspecialchars($user_infos->email ?: $user_infos->user_email_no, ENT_QUOTES); ?></span>
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($user_infos->address_full)): ?>
                        <span class="flex items-center gap-1">
                            <i class="ph ph-map-pin text-neutral-400"></i>
                            <span class="text-neutral-700"><?= htmlspecialchars($user_infos->address_full, ENT_QUOTES); ?></span>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main 2-Column Section -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-4 my-3">
    <!-- Left Column: Info & Balances (5 Cols) -->
    <div class="lg:col-span-5 space-y-3">
        <div class="bg-white rounded-2xl border border-neutral-200/80 shadow-2xs overflow-hidden">
            <div class="px-4 py-3 border-b border-neutral-100 bg-neutral-50/60 flex items-center justify-between">
                <h3 class="text-xs font-bold text-neutral-800 flex items-center gap-1.5">
                    <i class="ph-bold ph-wallet text-indigo-600"></i>
                    <span>হিসাব ও কমিশন বিবরণী</span>
                </h3>
            </div>
            
            <div class="divide-y divide-neutral-100 text-xs">
                <div class="px-4 py-2.5 flex items-center justify-between">
                    <span class="text-neutral-600 font-medium">মোট আদায় (Total Collect)</span>
                    <span class="font-mono font-bold text-neutral-900">৳ <?= number_format((float)$totalCollects, 2); ?></span>
                </div>
                <div class="px-4 py-2.5 flex items-center justify-between">
                    <span class="text-neutral-600 font-medium">প্রাপ্য কমিশন (<?= $user_infos->percentager ?? 0; ?>%)</span>
                    <span class="font-mono font-bold text-indigo-600">৳ <?= number_format((float)($totalCollects * ($user_infos->percentager ?? 0) / 100), 2); ?></span>
                </div>
                <div class="px-4 py-2.5 flex items-center justify-between">
                    <span class="text-neutral-600 font-medium">মোট জমা (Total Paid)</span>
                    <span class="font-mono font-bold text-blue-600">৳ <?= number_format((float)$totalPaids, 2); ?></span>
                </div>
                <div class="px-4 py-2.5 flex items-center justify-between bg-neutral-50/40">
                    <span class="text-neutral-800 font-bold">মোট বকেয়া (Total Due)</span>
                    <span class="font-mono font-bold text-rose-600 text-sm">৳ <?= number_format((float)$totalDues, 2); ?></span>
                </div>
            </div>

            <!-- Quick Collect Form (if due exists) -->
            <?php if ($totalDues > 0): ?>
                <div class="p-3 bg-amber-50/60 border-t border-amber-200/60">
                    <label class="block text-[11px] font-bold text-neutral-700 mb-1.5">বকেয়া অর্থ জমা গ্রহণ</label>
                    <div class="flex items-center gap-2">
                        <div class="relative flex-1">
                            <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-neutral-400 text-xs font-bold">৳</span>
                            <input type="text" class="w-full text-xs pl-6 pr-2.5 py-1.5 bg-white border border-neutral-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 text-neutral-900 font-mono typing_amounts" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');" placeholder="টাকার পরিমাণ">
                        </div>
                        <button type="button" class="px-3.5 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold shadow-2xs transition submit_amount_btn cursor-pointer shrink-0">
                            জমা গ্রহণ
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pending Approval History -->
        <?php if (!empty($allInactiveCut)): ?>
            <div class="bg-white rounded-2xl border border-neutral-200/80 shadow-2xs overflow-hidden">
                <div class="px-4 py-2.5 border-b border-neutral-100 bg-neutral-50/60 flex items-center justify-between">
                    <h4 class="text-xs font-bold text-neutral-800 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        <span>অনুমোদনের অপেক্ষায় থাকা জমা</span>
                    </h4>
                    <span class="text-[11px] text-neutral-500 font-mono"><?= count($allInactiveCut); ?> টি</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="bg-neutral-50/60 border-b border-neutral-100 text-neutral-600 font-semibold">
                                <th class="py-2 px-3">তারিখ</th>
                                <th class="py-2 px-3 text-right">পরিমাণ</th>
                                <th class="py-2 px-3 text-center w-20">অ্যাকশন</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100">
                            <?php foreach ($allInactiveCut as $cuts): ?>
                                <tr class="hover:bg-neutral-50/50 transition">
                                    <td class="py-2 px-3 font-mono text-neutral-600"><?= date('d-m-Y', $cuts->timesingstime); ?></td>
                                    <td class="py-2 px-3 text-right font-mono font-bold text-emerald-600">৳ <?= number_format((float)$cuts->usr_amnt_cuttt, 2); ?></td>
                                    <td class="py-2 px-3 text-center">
                                        <button type="button" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-md text-[11px] font-bold shadow-2xs transition appruveBtn cursor-pointer" thisAttrID="<?= $cuts->usr_bal_cutting_idiiis; ?>">
                                            অনুমোদন
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Right Column: Reports (7 Cols) -->
    <div class="lg:col-span-7 space-y-3">
        <div class="bg-white rounded-2xl border border-neutral-200/80 shadow-2xs p-4 sm:p-5">
            <h3 class="text-xs font-bold text-neutral-800 mb-3 flex items-center gap-1.5">
                <i class="ph-bold ph-chart-line-up text-indigo-600"></i>
                <span>তারিখ ভিত্তিক রিপোর্ট জেনারেটর</span>
            </h3>

            <!-- Collect Report Inline Group -->
            <div class="bg-neutral-50/70 p-3.5 rounded-xl border border-neutral-200/80 mb-3">
                <div class="text-xs font-bold text-neutral-800 mb-2 flex items-center gap-1.5">
                    <i class="ph-bold ph-receipt text-indigo-600"></i>
                    <span>আদায় বিবরণী (তারিখ অনুযায়ী)</span>
                </div>
                <div class="flex flex-col sm:flex-row items-center gap-2">
                    <div class="w-full sm:flex-1">
                        <input type="text" class="w-full text-xs px-3 py-1.5 bg-white border border-neutral-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 text-neutral-900 startDates" data-toggle="flatpickr" value="<?= date('Y-m-d'); ?>" placeholder="শুরুর তারিখ">
                    </div>
                    <div class="w-full sm:flex-1">
                        <input type="text" class="w-full text-xs px-3 py-1.5 bg-white border border-neutral-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 text-neutral-900 endDates" data-toggle="flatpickr" value="<?= date('Y-m-d'); ?>" placeholder="সমাপ্তি তারিখ">
                    </div>
                    <button type="button" class="w-full sm:w-auto px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 active:scale-98 text-white rounded-xl text-xs font-semibold shadow-2xs transition showDetailsReportBtn cursor-pointer shrink-0 flex items-center justify-center gap-1.5">
                        <i class="ph-bold ph-file-text text-xs"></i>
                        <span>রিপোর্ট দেখুন</span>
                    </button>
                </div>
            </div>

            <!-- Paid Report Inline Group -->
            <div class="bg-neutral-50/70 p-3.5 rounded-xl border border-neutral-200/80">
                <div class="text-xs font-bold text-neutral-800 mb-2 flex items-center gap-1.5">
                    <i class="ph-bold ph-bank text-emerald-600"></i>
                    <span>পরিষদ তহবিলে জমা বিবরণী (তারিখ অনুযায়ী)</span>
                </div>
                <div class="flex flex-col sm:flex-row items-center gap-2">
                    <div class="w-full sm:flex-1">
                        <input type="text" class="w-full text-xs px-3 py-1.5 bg-white border border-neutral-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 text-neutral-900 strDates" data-toggle="flatpickr" value="<?= date('Y-m-d'); ?>" placeholder="শুরুর তারিখ">
                    </div>
                    <div class="w-full sm:flex-1">
                        <input type="text" class="w-full text-xs px-3 py-1.5 bg-white border border-neutral-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 text-neutral-900 endDate" data-toggle="flatpickr" value="<?= date('Y-m-d'); ?>" placeholder="সমাপ্তি তারিখ">
                    </div>
                    <button type="button" class="w-full sm:w-auto px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 active:scale-98 text-white rounded-xl text-xs font-semibold shadow-2xs transition showPaidDetailsShowBtn cursor-pointer shrink-0 flex items-center justify-center gap-1.5">
                        <i class="ph-bold ph-eye text-xs"></i>
                        <span>জমা বিবরণ</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Edit User Info -->
<div id="edit_user" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="edit_user-title" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered max-w-lg mx-auto" role="document">
        <div class="modal-content bg-white rounded-2xl shadow-2xl border border-neutral-200 overflow-hidden" style="pointer-events: auto;">
            
            <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-100 bg-white">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <i class="ph-bold ph-user-gear text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-neutral-900" id="edit_user-title">ব্যবহারকারী তথ্য সম্পাদনা</h3>
                        <p class="text-[11px] text-neutral-500">আদায়কারীর ব্যক্তিগত ও কমিশনের তথ্য পরিবর্তন করুন</p>
                    </div>
                </div>
                <button type="button" class="w-8 h-8 rounded-xl text-neutral-400 hover:text-neutral-700 hover:bg-neutral-100 flex items-center justify-center transition cursor-pointer" data-dismiss="modal" aria-label="Close">
                    <i class="ph ph-x text-lg"></i>
                </button>
            </div>

            <div class="p-6 bg-white space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="Fullss" class="block text-xs font-semibold text-neutral-700 mb-1.5">পূর্ণ নাম <span class="text-rose-500">*</span></label>
                        <input type="text" id="Fullss" class="w-full text-xs px-3.5 py-2 bg-white border border-neutral-300 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 text-neutral-900 transition full_name_s" placeholder="আদায়কারীর পূর্ণ নাম" value="<?= htmlspecialchars($user_infos->user_person_name ?? '', ENT_QUOTES); ?>">
                    </div>
                    <div>
                        <label for="Phone" class="block text-xs font-semibold text-neutral-700 mb-1.5">মোবাইল নম্বর <span class="text-rose-500">*</span></label>
                        <input type="text" id="Phone" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');" class="w-full text-xs px-3.5 py-2 bg-white border border-neutral-300 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 text-neutral-900 font-mono transition phone_nos" placeholder="০১XXXXXXXXX" value="<?= htmlspecialchars($user_infos->user_phone_no ?? '', ENT_QUOTES); ?>">
                    </div>
                    <div>
                        <label for="Email" class="block text-xs font-semibold text-neutral-700 mb-1.5">ইমেইল ঠিকানা</label>
                        <input type="email" id="Email" class="w-full text-xs px-3.5 py-2 bg-white border border-neutral-300 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 text-neutral-900 transition users_emails" placeholder="ইমেইল লিখুন" value="<?= htmlspecialchars($user_infos->email ?? ($user_infos->user_email_no ?? ''), ENT_QUOTES); ?>">
                    </div>
                    <div>
                        <label for="Parcentage" class="block text-xs font-semibold text-neutral-700 mb-1.5">কমিশন শতকরা হার (%) <span class="text-rose-500">*</span></label>
                        <input type="text" id="Parcentage" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');" class="w-full text-xs px-3.5 py-2 bg-white border border-neutral-300 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 text-neutral-900 font-mono transition collect_parcent" placeholder="কমিশন হার" value="<?= htmlspecialchars($user_infos->percentager ?? '0', ENT_QUOTES); ?>">
                    </div>
                    <div class="col-span-2">
                        <label for="Address" class="block text-xs font-semibold text-neutral-700 mb-1.5">পূর্ণ ঠিকানা</label>
                        <input type="text" id="Address" class="w-full text-xs px-3.5 py-2 bg-white border border-neutral-300 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 text-neutral-900 transition full_addresss" placeholder="গ্রাম, ডাকঘর, উপজেলা, জেলা" value="<?= htmlspecialchars($user_infos->address_full ?? '', ENT_QUOTES); ?>">
                    </div>
                </div>

                <input type="hidden" class="thisUserID" value="<?= $user_infos->udc_list_auto_p_iidd; ?>">
            </div>

            <!-- Footer / Clean Buttons -->
            <div class="bg-neutral-50 px-5 py-3 border-t border-neutral-200 flex items-center justify-end gap-2">
                <button type="button" class="px-3 py-1.5 rounded-xl text-xs font-semibold text-neutral-700 bg-white border border-neutral-300 hover:bg-neutral-100 shadow-2xs transition cursor-pointer" data-dismiss="modal">
                    বাতিল
                </button>
                <button type="button" class="px-3.5 py-1.5 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 active:scale-98 shadow-2xs transition editing_userBtn cursor-pointer flex items-center gap-1.5">
                    <i class="ph-bold ph-check text-xs"></i>
                    <span>সংরক্ষণ করুন</span>
                </button>
            </div>

        </div>
    </div>
</div>

<script>
    let totalDueSs = '<?php echo (float)$totalDues; ?>';
    let userUniqCheck = '<?php echo $checkUniq; ?>';

    $(document).ready(function() {
        $('#edit_user').appendTo('body');
    });

    $(document).on('click', '.showDetailsReportBtn', function () {
        let startDates = $('.startDates').val();
        let endDates = $('.endDates').val();
        let url_link = 'userCollectHistory?sDate=' + startDates + '&eDates=' + endDates + '&check=' + userUniqCheck;
        window.open(url_link, '_blank', 'location=yes,height=800,width=800,scrollbars=yes,status=yes');
    });

    $(document).on('click', '.showPaidDetailsShowBtn', function () {
        let startDate = $('.strDates').val();
        let endDate = $('.endDate').val();
        let url_links = 'userPaidHistory?sDate=' + startDate + '&eDates=' + endDate + '&check=' + userUniqCheck;
        window.open(url_links, '_blank', 'location=yes,height=800,width=800,scrollbars=yes,status=yes');
    });

    $(document).on('click', '.appruveBtn', function () {
        let id = $(this).attr('thisAttrID');
        vConfirm('জমা অনুমোদন', 'আপনি কি নিশ্চিতভাবে এই জমার অর্থ অনুমোদন করতে চান?').then(function(confirmed) {
            if (!confirmed) return;
            $.ajax({
                type: "post",
                url: "approveDeposite",
                data: { id: id },
                success: function (r) {
                    if (r == 0) {
                        $('.success_alert_msg').html('<div class="p-3 bg-amber-50 border border-amber-200 text-amber-700 rounded-xl text-xs font-semibold text-center">সতর্কতা! পুনরায় যাচাই করুন</div>');
                    } else {
                        $('.success_alert_msg').html('<div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-xs font-semibold text-center">জমা সফলভাবে অনুমোদিত হয়েছে</div>');
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    }
                }
            });
        });
    });

    $(document).on('click', '.submit_amount_btn', function () {
        let typing_amounts = parseFloat($('.typing_amounts').val() || 0);
        if (typing_amounts <= 0) {
            $('.success_alert_msg').html('<div class="p-3 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-xs font-semibold text-center">অনুগ্রহ করে সঠিক টাকার পরিমাণ লিখুন</div>');
            return;
        }

        if (typing_amounts > parseFloat(totalDueSs)) {
            $('.success_alert_msg').html('<div class="p-3 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-xs font-semibold text-center">সতর্কতা! জমার পরিমাণ বকেয়া টাকার চেয়ে বেশি হতে পারে না</div>');
            return;
        }

        vConfirm('বকেয়া জমা গ্রহণ', 'আপনি কি নিশ্চিতভাবে ৳ ' + typing_amounts + ' বকেয়া জমা গ্রহণ করতে চান?').then(function(confirmed) {
            if (!confirmed) return;
            $.ajax({
                type: "post",
                url: "dueCollect",
                data: {
                    userUniq: userUniqCheck,
                    amnt: typing_amounts
                },
                success: function (r) {
                    if (r == 0) {
                        $('.success_alert_msg').html('<div class="p-3 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-xs font-semibold text-center">সতর্কতা! পরিমাণ যাচাই করে পুনরায় চেষ্টা করুন</div>');
                    } else {
                        $('.submit_amount_btn').hide();
                        $('.success_alert_msg').html('<div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-xs font-semibold text-center">বকেয়া অর্থ সফলভাবে জমা হয়েছে</div>');
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    }
                }
            });
        });
    });

    $(document).on('click', '.editing_userBtn', function () {
        let btn = $(this);
        let origHtml = btn.html();
        btn.prop('disabled', true).html('<i class="ph ph-spinner animate-spin text-sm"></i> সংরক্ষণ হচ্ছে...');

        $.ajax({
            type: "post",
            url: "editThisUserAdmn",
            data: {
                name: $('.full_name_s').val(),
                phone: $('.phone_nos').val(),
                email: $('.users_emails').val(),
                percent: $('.collect_parcent').val(),
                address: $('.full_addresss').val(),
                checking: userUniqCheck
            },
            success: function () {
                $('#edit_user').modal('hide');
                $('.success_alert_msg').html('<div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-xs font-semibold text-center">ব্যবহারকারীর তথ্য সফলভাবে হালনাগাদ হয়েছে</div>');
                setTimeout(() => {
                    location.reload();
                }, 1500);
            },
            error: function () {
                btn.prop('disabled', false).html(origHtml);
                vAlert('তথ্য সংরক্ষণ করতে সমস্যা হয়েছে।', 'ত্রুটি', 'error');
            }
        });
    });
</script>