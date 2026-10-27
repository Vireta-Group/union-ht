<?php 
    $totalDue = ($totalCollect ?? 0) - ($totalCutting ?? 0) - (($totalCollect ?? 0) * ($loggedUser['userParcent'] ?? 0) / 100);
?>

<!-- Breadcrumb & Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-neutral-200">
    <div>
        <nav class="flex items-center gap-2 text-xs text-neutral-500 mb-1">
            <a href="dashboard" class="hover:text-indigo-600 transition flex items-center gap-1">
                <i class="ph ph-house text-sm"></i> ড্যাশবোর্ড
            </a>
            <i class="ph ph-caret-right text-[10px] text-neutral-400"></i>
            <span class="text-neutral-800 font-medium">নগদ জমা</span>
        </nav>
        <h1 class="text-xl sm:text-2xl font-bold text-neutral-900 tracking-tight flex items-center gap-2">
            <span class="w-2.5 h-6 bg-indigo-600 rounded-full inline-block"></span>
            নগদ আদায় ও জমা ব্যবস্থাপনা
        </h1>
        <p class="text-xs sm:text-sm text-neutral-500 mt-0.5">আদায়কারীর সংগৃহীত কর জমা প্রদান এবং কমিশন হিসাব বিবরণী</p>
    </div>
</div>

<!-- Main Layout -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Left: Collector Profile & Balance Card -->
    <div class="lg:col-span-1 space-y-5">
        <!-- Collector Identity -->
        <div class="bg-white rounded-2xl border border-neutral-200/80 shadow-sm p-6 text-center space-y-3">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center">
                <i class="ph-bold ph-user-circle text-4xl"></i>
            </div>
            <div>
                <h2 class="text-base font-bold text-neutral-900 leading-tight">
                    <?= htmlspecialchars($loggedUser['userName'] ?? '—', ENT_QUOTES, 'UTF-8'); ?>
                </h2>
                <div class="mt-1.5 inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 text-amber-700 rounded-full text-xs font-semibold border border-amber-200/60">
                    <i class="ph ph-percent text-sm"></i>
                    <span>কমিশন হার: <?= bn_num($loggedUser['userParcent'] ?? 0); ?>%</span>
                </div>
            </div>

            <!-- Due Balance Highlight -->
            <div class="mt-4 pt-4 border-t border-neutral-100">
                <div class="text-xs text-neutral-500 font-medium mb-1">পরিশোধযোগ্য বকেয়া</div>
                <div class="text-3xl font-bold <?= $totalDue > 0 ? 'text-rose-600' : 'text-emerald-600'; ?>">
                    ৳ <?= bn_num(number_format((float)$totalDue)); ?>/-
                </div>
                <div class="text-[11px] text-neutral-400 mt-1">
                    <?= $totalDue > 0 ? 'জমা বাকি আছে' : 'সব জমা সম্পন্ন হয়েছে'; ?>
                </div>
            </div>
        </div>

        <!-- Submit Deposit -->
        <?php if ($totalDue > 0) { ?>
        <div class="bg-white rounded-2xl border border-neutral-200/80 shadow-sm p-5 space-y-4">
            <div class="flex items-center gap-2 pb-3 border-b border-neutral-100">
                <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i class="ph-bold ph-money text-base"></i>
                </div>
                <h3 class="text-sm font-bold text-neutral-900">টাকা জমা দিন</h3>
            </div>

            <div id="deposit_status_msg"></div>

            <div>
                <label class="block text-xs font-semibold text-neutral-700 mb-1.5">জমার পরিমাণ (টাকা)</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-neutral-400 text-sm font-bold">৳</span>
                    <input type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');"
                        class="w-full text-sm pl-9 pr-4 py-3 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 transition font-bold typing_amounts shadow-2xs"
                        placeholder="পরিমাণ লিখুন">
                </div>
                <p class="text-[11px] text-neutral-400 mt-1">সর্বোচ্চ: ৳ <?= bn_num(number_format((float)$totalDue)); ?></p>
            </div>

            <button type="button" class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white rounded-xl text-sm font-bold shadow-sm hover:shadow transition-all flex items-center justify-center gap-2 submit_amount_btn cursor-pointer">
                <i class="ph-bold ph-arrow-circle-up text-base"></i>
                <span>জমা নিশ্চিত করুন</span>
            </button>
        </div>
        <?php } ?>

        <!-- Pending Deposits Card -->
        <div class="bg-white rounded-2xl border border-neutral-200/80 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-neutral-100">
                <h3 class="text-sm font-bold text-neutral-900 flex items-center gap-2">
                    <i class="ph ph-clock text-amber-500 text-base"></i>
                    অনুমোদন-প্রতীক্ষায় জমা
                </h3>
            </div>
            <div class="divide-y divide-neutral-50">
                <?php if (!empty($allInactiveCut) && count($allInactiveCut) > 0) { ?>
                    <?php foreach ($allInactiveCut as $cuts) { ?>
                    <div class="px-5 py-3 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                                <i class="ph ph-clock-clockwise text-sm"></i>
                            </div>
                            <span class="text-xs text-neutral-600 font-medium">
                                <?= date('d-m-Y', $cuts->timesingstime ?? time()); ?>
                            </span>
                        </div>
                        <span class="font-bold text-xs text-neutral-900">
                            ৳ <?= bn_num(number_format((float)($cuts->usr_amnt_cuttt ?? 0))); ?>
                        </span>
                    </div>
                    <?php } ?>
                <?php } else { ?>
                    <div class="px-5 py-6 text-center text-xs text-neutral-400">
                        কোন অনুমোদন-প্রতীক্ষায় জমা নেই
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <!-- Right: Balance History & Stats -->
    <div class="lg:col-span-2 space-y-5">
        <!-- KPI Summary Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl border border-neutral-200/80 shadow-sm p-4 space-y-2">
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i class="ph-bold ph-arrows-in text-base"></i>
                </div>
                <div class="text-[11px] text-neutral-500 font-medium">মোট সংগ্রহ</div>
                <div class="text-lg font-bold text-neutral-900">৳ <?= bn_num(number_format((float)($totalCollect ?? 0))); ?></div>
            </div>
            <div class="bg-white rounded-2xl border border-neutral-200/80 shadow-sm p-4 space-y-2">
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i class="ph-bold ph-arrows-out text-base"></i>
                </div>
                <div class="text-[11px] text-neutral-500 font-medium">মোট জমা দেওয়া</div>
                <div class="text-lg font-bold text-neutral-900">৳ <?= bn_num(number_format((float)($totalCutting ?? 0))); ?></div>
            </div>
            <div class="bg-white rounded-2xl border border-neutral-200/80 shadow-sm p-4 space-y-2">
                <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i class="ph-bold ph-percent text-base"></i>
                </div>
                <div class="text-[11px] text-neutral-500 font-medium">মোট কমিশন</div>
                <div class="text-lg font-bold text-neutral-900">৳ <?= bn_num(number_format((float)(($totalCollect ?? 0) * ($loggedUser['userParcent'] ?? 0) / 100))); ?></div>
            </div>
            <div class="bg-white rounded-2xl border border-neutral-200/80 shadow-sm p-4 space-y-2">
                <div class="w-8 h-8 rounded-lg <?= $totalDue > 0 ? 'bg-rose-50 text-rose-600' : 'bg-emerald-50 text-emerald-600'; ?> flex items-center justify-center">
                    <i class="ph-bold <?= $totalDue > 0 ? 'ph-warning' : 'ph-check-circle'; ?> text-base"></i>
                </div>
                <div class="text-[11px] text-neutral-500 font-medium">বকেয়া জমা</div>
                <div class="text-lg font-bold <?= $totalDue > 0 ? 'text-rose-600' : 'text-emerald-600'; ?>">৳ <?= bn_num(number_format((float)$totalDue)); ?></div>
            </div>
        </div>

        <!-- Summary Table Card -->
        <div class="bg-white rounded-2xl border border-neutral-200/80 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-neutral-100 flex items-center gap-2">
                <i class="ph-bold ph-table text-indigo-600 text-base"></i>
                <h3 class="text-sm font-bold text-neutral-900">ব্যালেন্স বিবরণী</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left border-collapse">
                    <thead>
                        <tr class="bg-neutral-50/80 border-b border-neutral-200/80">
                            <th class="py-3 px-5 text-neutral-600 font-bold">বিবরণ</th>
                            <th class="py-3 px-5 text-right text-neutral-600 font-bold">পরিমাণ (টাকা)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100">
                        <tr class="hover:bg-neutral-50/70 transition">
                            <td class="py-3 px-5 font-medium text-neutral-700 flex items-center gap-2">
                                <i class="ph ph-arrows-in text-blue-500"></i> মোট সংগৃহীত
                            </td>
                            <td class="py-3 px-5 text-right font-bold text-neutral-900">
                                ৳ <?= bn_num(number_format((float)($totalCollect ?? 0))); ?>/-
                            </td>
                        </tr>
                        <tr class="hover:bg-neutral-50/70 transition">
                            <td class="py-3 px-5 font-medium text-neutral-700 flex items-center gap-2">
                                <i class="ph ph-arrows-out text-emerald-500"></i> মোট জমা দেওয়া হয়েছে
                            </td>
                            <td class="py-3 px-5 text-right font-bold text-neutral-900">
                                ৳ <?= bn_num(number_format((float)($totalCutting ?? 0))); ?>/-
                            </td>
                        </tr>
                        <tr class="hover:bg-neutral-50/70 transition">
                            <td class="py-3 px-5 font-medium text-neutral-700 flex items-center gap-2">
                                <i class="ph ph-percent text-amber-500"></i> কমিশন (<?= bn_num($loggedUser['userParcent'] ?? 0); ?>%)
                            </td>
                            <td class="py-3 px-5 text-right font-bold text-amber-700">
                                ৳ <?= bn_num(number_format((float)(($totalCollect ?? 0) * ($loggedUser['userParcent'] ?? 0) / 100))); ?>/-
                            </td>
                        </tr>
                        <tr class="bg-rose-50/60 border-t-2 border-rose-200/60">
                            <td class="py-3 px-5 font-bold text-neutral-900 flex items-center gap-2">
                                <i class="ph-bold ph-warning-circle text-rose-500"></i> বকেয়া জমা (পরিশোধযোগ্য)
                            </td>
                            <td class="py-3 px-5 text-right font-bold text-lg <?= $totalDue > 0 ? 'text-rose-600' : 'text-emerald-600'; ?>">
                                ৳ <?= bn_num(number_format((float)$totalDue)); ?>/-
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- All-time totals (cumulative) -->
        <div class="bg-white rounded-2xl border border-neutral-200/80 shadow-sm p-5 space-y-3">
            <h3 class="text-sm font-bold text-neutral-900 pb-2 border-b border-neutral-100 flex items-center gap-2">
                <i class="ph ph-chart-bar text-indigo-600 text-base"></i>
                সার্বকালীন পরিসংখ্যান
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                <div class="flex items-center justify-between px-4 py-3 bg-neutral-50 rounded-xl border border-neutral-100">
                    <span class="text-neutral-600">মোট সংগ্রহ (সার্বকাল)</span>
                    <span class="font-bold text-neutral-900">৳ <?= bn_num(number_format((float)($allCollects ?? 0))); ?>/-</span>
                </div>
                <div class="flex items-center justify-between px-4 py-3 bg-neutral-50 rounded-xl border border-neutral-100">
                    <span class="text-neutral-600">মোট জমা (সার্বকাল)</span>
                    <span class="font-bold text-neutral-900">৳ <?= bn_num(number_format((float)($allCutting ?? 0))); ?>/-</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    var totalDueJs = <?= (float)$totalDue; ?>;

    $(document).on('click', '.submit_amount_btn', function () {
        let typing_amounts = parseFloat($('.typing_amounts').val());

        if (!typing_amounts || isNaN(typing_amounts) || typing_amounts <= 0) {
            $('#deposit_status_msg').html('<div class="mb-3 p-3 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl text-xs font-semibold">পরিমাণ সঠিকভাবে লিখুন</div>');
            return;
        }

        if (typing_amounts > totalDueJs) {
            $('#deposit_status_msg').html('<div class="mb-3 p-3 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-xs font-semibold">পরিমাণ বকেয়ার চেয়ে বেশি হতে পারবে না!</div>');
            return;
        }

        let $btn = $(this);
        vConfirm('জমা প্রদান নিশ্চিতকরণ', 'আপনি কি ৳ ' + typing_amounts + ' টাকা জমা দিতে নিশ্চিত?').then(function(confirmed) {
            if (!confirmed) return;

            $btn.prop('disabled', true).html('<i class="ph-bold ph-spinner animate-spin text-sm"></i> প্রক্রিয়াকরণ...');

            $.ajax({
                type: "post",
                url: "depositeDueAmount",
                data: {
                    userUniq: '<?php echo htmlspecialchars($loggedUser['userUniqs'] ?? '', ENT_QUOTES, 'UTF-8'); ?>',
                    amnt: typing_amounts
                },
                success: function (r) {
                    if (r == 0) {
                        $('#deposit_status_msg').html('<div class="mb-3 p-3 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl text-xs font-semibold">পরিমাণ যাচাই করুন, জমা হয়নি।</div>');
                        $btn.prop('disabled', false).html('<i class="ph-bold ph-arrow-circle-up text-base"></i> <span>জমা নিশ্চিত করুন</span>');
                    } else {
                        $('#deposit_status_msg').html('<div class="mb-3 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-xs font-semibold">জমা সফল হয়েছে! পুনরায় লোড হচ্ছে...</div>');
                        setTimeout(() => { location.reload(); }, 2000);
                    }
                },
                error: function() {
                    $('#deposit_status_msg').html('<div class="mb-3 p-3 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-xs font-semibold">সংযোগ সমস্যা। পুনরায় চেষ্টা করুন।</div>');
                    $btn.prop('disabled', false);
                }
            });
        });
    });
</script>