<?php 
    $numbers = explode('-', $main_data->holding_number ?? '');
    $h_start_date = explode('-', $main_data->holding_starting_years ?? '');
    if (count($h_start_date) < 2) {
        $h_start_date = [$main_data->holding_starting_years ?? '', ''];
    }
?>

<!-- Breadcrumb & Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-neutral-200">
    <div>
        <nav class="flex items-center gap-2 text-xs text-neutral-500 mb-1">
            <a href="dashboard" class="hover:text-indigo-600 transition flex items-center gap-1">
                <i class="ph ph-house text-sm"></i> ড্যাশবোর্ড
            </a>
            <i class="ph ph-caret-right text-[10px] text-neutral-400"></i>
            <a href="search_holding_info" class="hover:text-indigo-600 transition">হোল্ডিং তালিকা</a>
            <i class="ph ph-caret-right text-[10px] text-neutral-400"></i>
            <span class="text-neutral-800 font-medium">হোল্ডিং প্রোফাইল</span>
        </nav>
        <h1 class="text-xl sm:text-2xl font-bold text-neutral-900 tracking-tight flex items-center gap-2">
            <span class="w-2.5 h-6 bg-indigo-600 rounded-full inline-block"></span>
            হোল্ডিং ও নাগরিক বিস্তারিত তথ্য
        </h1>
        <p class="text-xs sm:text-sm text-neutral-500 mt-0.5">হোল্ডিং নম্বর: <?= htmlspecialchars($main_data->holding_number ?? '', ENT_QUOTES, 'UTF-8'); ?> &bull; ওয়ার্ড <?= bn_num($main_data->word_number ?? ''); ?></p>
    </div>
    <div class="flex items-center gap-2">
        <a href="download/s_platev?uniq=<?= htmlspecialchars($main_data->uniqID ?? '', ENT_QUOTES, 'UTF-8'); ?>" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white shadow-2xs transition">
            <i class="ph-bold ph-download-simple text-xs"></i>
            <span>প্লেট ডাউনলোড</span>
        </a>
        <button type="button" data-toggle="modal" data-target="#update_user_details" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-indigo-600 hover:bg-indigo-700 text-white shadow-2xs transition cursor-pointer">
            <i class="ph-bold ph-pencil-simple text-xs"></i>
            <span>তথ্য সংশোধন</span>
        </button>
    </div>
</div>

<!-- Main Grid -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Column: Citizen Profile & Contact Card -->
    <div class="space-y-6">
        <!-- Profile Identity Card -->
        <div class="bg-white rounded-2xl border border-neutral-200/80 shadow-sm p-6 text-center">
            <div class="relative w-24 h-24 mx-auto mb-4">
                <?php if (empty($main_data->profile_image)) { ?>
                    <div class="w-24 h-24 rounded-2xl bg-neutral-100 border-2 border-neutral-200 flex items-center justify-center text-neutral-400">
                        <i class="ph ph-user text-4xl"></i>
                    </div>
                <?php } else { ?>
                    <img src="<?= media_url($main_data->profile_image, 'image/png') ?>" alt="Profile" class="w-24 h-24 rounded-2xl object-cover border-2 border-indigo-100 shadow-sm">
                <?php } ?>
                <span class="absolute -bottom-1 -right-1 px-2 py-0.5 bg-indigo-600 text-white text-[10px] font-bold rounded-lg shadow-sm">
                    ওয়ার্ড <?= bn_num($main_data->word_number ?? ''); ?>
                </span>
            </div>

            <h2 class="text-base sm:text-lg font-bold text-neutral-900 leading-tight">
                <?= htmlspecialchars($main_data->family_person_name_head ?? '', ENT_QUOTES, 'UTF-8'); ?>
            </h2>
            <div class="inline-flex items-center gap-1.5 mt-1.5 px-3 py-1 bg-indigo-50 text-indigo-700 rounded-full text-xs font-semibold">
                <i class="ph-bold ph-hash text-xs"></i>
                <span>হোল্ডিং: <?= htmlspecialchars($main_data->holding_number ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
            </div>

            <div class="mt-4 pt-4 border-t border-neutral-100 flex items-center justify-center gap-1 text-xs text-neutral-600">
                <i class="ph ph-map-pin text-rose-500 text-sm shrink-0"></i>
                <span><?= htmlspecialchars(($main_data->bn_vill_names ?? '') . ', ' . ($main_data->house_na_me ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
        </div>

        <!-- Detailed Specifications List -->
        <div class="bg-white rounded-2xl border border-neutral-200/80 shadow-sm p-5 space-y-3 text-xs">
            <h3 class="text-xs font-bold text-neutral-900 pb-2 border-b border-neutral-100 flex items-center gap-2">
                <i class="ph ph-address-book text-indigo-600 text-base"></i> যোগাযোগের বিবরণ
            </h3>

            <div class="flex items-center justify-between py-1.5 border-b border-neutral-50">
                <span class="text-neutral-500">পিতার নাম</span>
                <span class="font-medium text-neutral-800"><?= htmlspecialchars($main_data->pason_father_name ?? '—', ENT_QUOTES, 'UTF-8'); ?></span>
            </div>

            <div class="flex items-center justify-between py-1.5 border-b border-neutral-50">
                <span class="text-neutral-500">মাতার নাম</span>
                <span class="font-medium text-neutral-800"><?= htmlspecialchars($main_data->parson_mother_name ?? '—', ENT_QUOTES, 'UTF-8'); ?></span>
            </div>

            <div class="flex items-center justify-between py-1.5 border-b border-neutral-50">
                <span class="text-neutral-500">এনআইডি (NID)</span>
                <span class="font-mono font-medium text-neutral-800"><?= htmlspecialchars($main_data->nid_number ?? '—', ENT_QUOTES, 'UTF-8'); ?></span>
            </div>

            <div class="flex items-center justify-between py-1.5 border-b border-neutral-50">
                <span class="text-neutral-500">জন্ম তারিখ</span>
                <span class="font-medium text-neutral-800"><?= htmlspecialchars($main_data->date_of_birth ?? '—', ENT_QUOTES, 'UTF-8'); ?></span>
            </div>

            <div class="flex items-center justify-between py-1.5 border-b border-neutral-50">
                <span class="text-neutral-500">প্রাথমিক মোবাইল</span>
                <a href="tel:<?= htmlspecialchars($main_data->mobile_number_one ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="font-medium text-indigo-600 hover:underline">
                    <?= htmlspecialchars($main_data->mobile_number_one ?? '—', ENT_QUOTES, 'UTF-8'); ?>
                </a>
            </div>

            <?php if (!empty($main_data->mobile_number_two)) { ?>
                <div class="flex items-center justify-between py-1.5 border-b border-neutral-50">
                    <span class="text-neutral-500">বিকল্প মোবাইল</span>
                    <a href="tel:<?= htmlspecialchars($main_data->mobile_number_two ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="font-medium text-neutral-700 hover:underline">
                        <?= htmlspecialchars($main_data->mobile_number_two, ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                </div>
            <?php } ?>

            <div class="py-1.5 border-b border-neutral-50">
                <span class="text-neutral-500 block mb-0.5">বর্তমান ঠিকানা</span>
                <span class="font-medium text-neutral-800 leading-relaxed"><?= htmlspecialchars($main_data->parson_paranent_address ?? '—', ENT_QUOTES, 'UTF-8'); ?></span>
            </div>

            <div class="py-1.5">
                <span class="text-neutral-500 block mb-0.5">স্থায়ী ঠিকানা</span>
                <span class="font-medium text-neutral-800 leading-relaxed"><?= htmlspecialchars($main_data->parson_parmanent_address ?? '—', ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
        </div>
    </div>

    <!-- Right Column: Key Stats, Invoices, and Actions -->
    <div class="lg:col-span-2 space-y-6">
        <!-- 4 KPI Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl border border-neutral-200/80 shadow-sm p-4 flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                    <i class="ph-bold ph-currency-circle-dollar text-2xl"></i>
                </div>
                <div>
                    <span class="text-neutral-500 text-[11px] font-medium block">বার্ষিক করের হার</span>
                    <div class="text-base sm:text-lg font-bold text-neutral-900 leading-tight">
                        ৳ <?= bn_num(number_format((float)($main_data->holding_rates_yearly ?? 0))); ?>
                    </div>
                    <span class="text-[10px] text-neutral-400">প্রতি অর্থবছর</span>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-neutral-200/80 shadow-sm p-4 flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                    <i class="ph-bold ph-calendar-blank text-2xl"></i>
                </div>
                <div>
                    <span class="text-neutral-500 text-[11px] font-medium block">হোল্ডিং শুরুর সাল</span>
                    <div class="text-base sm:text-lg font-bold text-neutral-900 leading-tight">
                        <?= !empty($main_data->holding_starting_years) ? bn_num($main_data->holding_starting_years) : '—'; ?>
                    </div>
                    <span class="text-[10px] text-neutral-400">কার্যকর অর্থবছর</span>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-neutral-200/80 shadow-sm p-4 flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                    <i class="ph-bold ph-buildings text-2xl"></i>
                </div>
                <div>
                    <span class="text-neutral-500 text-[11px] font-medium block">হোল্ডিং এর ধরণ</span>
                    <div class="text-base sm:text-lg font-bold text-neutral-900 leading-tight">
                        <?= htmlspecialchars($main_data->holding_condition ?? 'সাধারণ', ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                    <span class="text-[10px] text-neutral-400">অবকাঠামো শ্রেণী</span>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-neutral-200/80 shadow-sm p-4 flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl <?= ($total_unpaid > 0) ? 'bg-rose-50 text-rose-600' : 'bg-emerald-50 text-emerald-600'; ?> flex items-center justify-center shrink-0">
                    <i class="ph-bold <?= ($total_unpaid > 0) ? 'ph-warning-circle' : 'ph-check-circle'; ?> text-2xl"></i>
                </div>
                <div>
                    <span class="text-neutral-500 text-[11px] font-medium block">মোট বকেয়া কর</span>
                    <div class="text-base sm:text-lg font-bold <?= ($total_unpaid > 0) ? 'text-rose-600' : 'text-emerald-600'; ?> leading-tight">
                        ৳ <?= bn_num(number_format((float)($total_unpaid ?? 0))); ?>
                    </div>
                    <span class="text-[10px] <?= ($total_unpaid > 0) ? 'text-rose-500 font-semibold' : 'text-emerald-500'; ?>">
                        <?= ($total_unpaid > 0) ? bn_num($unpaid_years_count ?? 1) . 'টি অর্থবছর বকেয়া' : 'সকল অর্থবছর পরিশোধিত'; ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Fiscal Year-wise Tax Arrears & Payment Status Ledger Card -->
        <div class="bg-white rounded-2xl border border-neutral-200/80 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-neutral-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <i class="ph-bold ph-calendar-check text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-neutral-900 leading-tight">অর্থবছরভিত্তিক কর স্থিতি ও বকেয়া হিসাব</h3>
                        <p class="text-[11px] text-neutral-500 mt-0.5">
                            হোল্ডিং শুরুর অর্থবছর (<?= !empty($main_data->holding_starting_years) ? bn_num($main_data->holding_starting_years) : '—'; ?>) থেকে বর্তমান সময় পর্যন্ত বিবরণী
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <?php if ($total_unpaid > 0): ?>
                        <a href="taxCollection?holding=<?= urlencode($main_data->holding_number ?? ''); ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-rose-600 hover:bg-rose-700 text-white shadow-2xs transition">
                            <i class="ph-bold ph-hand-coins text-xs"></i>
                            <span>বকেয়া কর আদায় করুন</span>
                        </a>
                    <?php else: ?>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                            <i class="ph-bold ph-check-circle text-xs"></i>
                            <span>সকল অর্থবছর পরিশোধিত</span>
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($tax_timeline) && count($tax_timeline) > 0): ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-neutral-50/80 border-b border-neutral-100 text-neutral-500 font-semibold">
                                <th class="py-2.5 px-4">অর্থবছর</th>
                                <th class="py-2.5 px-3 text-right">ধার্যকৃত কর</th>
                                <th class="py-2.5 px-3 text-right">পরিশোধিত</th>
                                <th class="py-2.5 px-3 text-right">বকেয়া কর</th>
                                <th class="py-2.5 px-4 text-center">স্থিতি</th>
                                <th class="py-2.5 px-4">পরিশোধের তারিখ ও রসিদ বিবরণ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100 text-neutral-800">
                            <?php foreach ($tax_timeline as $row): ?>
                                <tr class="hover:bg-neutral-50/60 transition-colors <?= ($row['status'] === 'unpaid') ? 'bg-rose-50/20' : ''; ?>">
                                    <td class="py-3 px-4 font-bold text-neutral-900 whitespace-nowrap">
                                        <?= bn_num($row['year']); ?> অর্থবছর
                                    </td>
                                    <td class="py-3 px-3 text-right font-medium text-neutral-600 whitespace-nowrap">
                                        ৳ <?= bn_num(number_format($row['rate'])); ?>/-
                                    </td>
                                    <td class="py-3 px-3 text-right font-bold <?= ($row['paid'] > 0) ? 'text-emerald-600' : 'text-neutral-400'; ?> whitespace-nowrap">
                                        ৳ <?= bn_num(number_format($row['paid'])); ?>/-
                                    </td>
                                    <td class="py-3 px-3 text-right font-bold <?= ($row['due'] > 0) ? 'text-rose-600 font-black' : 'text-neutral-400'; ?> whitespace-nowrap">
                                        ৳ <?= bn_num(number_format($row['due'])); ?>/-
                                    </td>
                                    <td class="py-3 px-4 text-center whitespace-nowrap">
                                        <?php if ($row['status'] === 'paid'): ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                                <i class="ph-bold ph-check text-[10px]"></i> পরিশোধিত
                                            </span>
                                        <?php elseif ($row['status'] === 'partial'): ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200/60">
                                                <i class="ph-bold ph-hourglass text-[10px]"></i> আংশিক পরিশোধ
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200/60">
                                                <i class="ph-bold ph-x text-[10px]"></i> বকেয়া
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4 text-xs text-neutral-500">
                                        <?php if (!empty($row['payments'])): ?>
                                            <?php foreach ($row['payments'] as $pm): ?>
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <a href="recipt_print?uniqs=<?= htmlspecialchars($pm->voucher, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" class="font-mono text-indigo-600 font-semibold hover:underline" title="রসিদ দেখুন">
                                                        #<?= htmlspecialchars($pm->voucher, ENT_QUOTES, 'UTF-8'); ?>
                                                    </a>
                                                    <span class="text-neutral-300">·</span>
                                                    <span>তারিখ: <?= !empty($pm->entry_date) ? bn_num($pm->entry_date) : '—'; ?></span>
                                                    <span class="text-neutral-300">·</span>
                                                    <span class="text-emerald-700 font-medium">আদায়: ৳ <?= bn_num(number_format($pm->paid_amount)); ?>/-</span>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span class="text-rose-500 font-semibold text-[11px]">পরিশোধ বাকি রয়েছে</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="bg-neutral-50/90 border-t border-neutral-200 font-bold text-xs text-neutral-900">
                            <tr>
                                <td class="py-3 px-4">সর্বমোট (<?= bn_num(count($tax_timeline)); ?>টি অর্থবছর)</td>
                                <td class="py-3 px-3 text-right">৳ <?= bn_num(number_format(array_sum(array_column($tax_timeline, 'rate')))); ?>/-</td>
                                <td class="py-3 px-3 text-right text-emerald-600">৳ <?= bn_num(number_format(array_sum(array_column($tax_timeline, 'paid')))); ?>/-</td>
                                <td class="py-3 px-3 text-right text-rose-600 font-black">৳ <?= bn_num(number_format(array_sum(array_column($tax_timeline, 'due')))); ?>/-</td>
                                <td colspan="2" class="py-3 px-4 text-neutral-600 font-normal">
                                    <span>পরিশোধিত: <b><?= bn_num($paid_years_count ?? 0); ?></b>টি</span>
                                    <span class="mx-1 text-neutral-300">&bull;</span>
                                    <span>বকেয়া: <b class="text-rose-600"><?= bn_num($unpaid_years_count ?? 0); ?></b>টি</span>
                                    <?php if (!empty($first_payment_date)): ?>
                                        <span class="mx-1 text-neutral-300">&bull;</span>
                                        <span>প্রথম কর পরিশোধ: <?= bn_num($first_payment_date); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <?php else: ?>
                <div class="p-6 text-center text-neutral-400 text-xs">
                    কোন অর্থবছর বিবরণী পাওয়া যায়নি।
                </div>
            <?php endif; ?>
        </div>

        <!-- Holding Invoices / Payment History Card -->
        <div class="bg-white rounded-2xl border border-neutral-200/80 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-neutral-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <i class="ph-bold ph-receipt text-base"></i>
                    </div>
                    <h3 class="text-sm font-bold text-neutral-900">কর পরিশোধ ও রসিদ বিবরণী</h3>
                </div>
                <span class="px-2.5 py-1 bg-neutral-100 rounded-full text-xs font-semibold text-neutral-600">
                    মোট রসিদ: <?= bn_num(count($invoces ?? [])); ?> টি
                </span>
            </div>

            <?php if (!empty($invoces) && count($invoces) > 0) { ?>
                <div class="divide-y divide-neutral-100">
                    <?php $sl = 1; foreach ($invoces as $invc) { 
                        $ht_discount = (int) ($invc->discount_amounts ?? 0);
                        $ht_net   = (float) ($invc->amnt_taka ?? 0);
                        $ht_gross = $ht_net + $ht_discount;
                        $inv_years = $invoice_years_map[$invc->invoice_no_s ?? ''] ?? [];
                    ?>
                        <div class="p-4 sm:p-5 hover:bg-neutral-50/70 transition-colors">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="flex items-start sm:items-center gap-3">
                                    <span class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-700 text-xs font-black flex items-center justify-center shrink-0 border border-indigo-100 shadow-2xs">
                                        <?= bn_num($sl); ?>
                                    </span>
                                    <div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="font-bold text-neutral-900 font-mono text-xs sm:text-sm bg-neutral-100 px-2.5 py-0.5 rounded-lg border border-neutral-200">
                                                #<?= htmlspecialchars($invc->invoice_no_s ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                            <?php if (!empty($invc->date_s_today_s)): ?>
                                                <span class="text-xs text-neutral-500 flex items-center gap-1 font-medium">
                                                    <i class="ph-bold ph-calendar text-neutral-400"></i>
                                                    <?= bn_num($invc->date_s_today_s); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-[11px] text-neutral-400 mt-1 flex items-center gap-1">
                                            <i class="ph-bold ph-user text-neutral-400"></i>
                                            আদায়কারী: <span class="text-neutral-700 font-medium"><?= htmlspecialchars($invc->user_person_name ?? '—', ENT_QUOTES, 'UTF-8'); ?></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between sm:justify-end gap-3 pt-2 sm:pt-0 border-t sm:border-t-0 border-neutral-100">
                                    <div class="text-left sm:text-right">
                                        <div class="text-base sm:text-lg font-black text-emerald-700 leading-tight">
                                            ৳ <?= bn_num(number_format($ht_net)); ?>
                                        </div>
                                        <?php if ($ht_discount > 0): ?>
                                            <div class="text-[10px] text-neutral-500 mt-0.5 flex items-center gap-1">
                                                <span class="line-through text-neutral-400">৳<?= bn_num(number_format($ht_gross)); ?></span>
                                                <span class="text-rose-600 font-bold bg-rose-50 px-1.5 py-0.2 rounded border border-rose-100">ছাড় ৳<?= bn_num(number_format($ht_discount)); ?></span>
                                                <?php if (!empty($invc->discount_refs_name)): ?>
                                                    <span class="italic text-neutral-400">(<?= htmlspecialchars($invc->discount_refs_name, ENT_QUOTES, 'UTF-8'); ?>)</span>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200/60 mt-0.5">
                                                <i class="ph-bold ph-check text-[9px]"></i> পরিশোধিত
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="flex items-center gap-1.5 shrink-0">
                                        <a href="recipt_print?uniqs=<?= htmlspecialchars($invc->invoice_no_s ?? '', ENT_QUOTES, 'UTF-8'); ?>" target="_blank" 
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200/60 rounded-xl text-xs font-bold transition shadow-2xs" 
                                           title="পূর্ণাঙ্গ মানি রসিদ প্রিন্ট করুন">
                                            <i class="ph-bold ph-printer text-xs"></i>
                                            <span>রশিদ প্রিন্ট</span>
                                        </a>
                                        <a href="slip_print?unqs=<?= htmlspecialchars($invc->invoice_no_s ?? '', ENT_QUOTES, 'UTF-8'); ?>" target="_blank" 
                                           class="inline-flex items-center gap-1.5 px-2.5 py-1.5 bg-neutral-100 hover:bg-neutral-200 text-neutral-700 border border-neutral-200 rounded-xl text-xs font-semibold transition shadow-2xs" 
                                           title="থার্মাল / পিওএস স্লিপ">
                                            <i class="ph-bold ph-receipt text-xs"></i>
                                            <span>পিওএস</span>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Bottom: Paid Fiscal Years Horizontal List -->
                            <?php if (!empty($inv_years)): ?>
                                <div class="mt-3 pt-2.5 border-t border-neutral-100 flex flex-wrap items-center gap-1.5">
                                    <span class="text-[11px] font-bold text-neutral-500 flex items-center gap-1 mr-1">
                                        <i class="ph-bold ph-check-circle text-emerald-500"></i> পরিশোধিত অর্থবছর:
                                    </span>
                                    <?php foreach ($inv_years as $iy): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80 shadow-2xs">
                                            <?= bn_num($iy); ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php $sl++; } ?>
                </div>
            <?php } else { ?>
                <div class="p-8 text-center text-neutral-400 space-y-2">
                    <i class="ph ph-receipt text-3xl text-neutral-300"></i>
                    <p class="text-xs">এখনও পর্যন্ত কোন কর পরিশোধের রসিদ নেই।</p>
                </div>
            <?php } ?>
        </div>
    </div>
</div>

<!-- Modal: Update Holding Details -->
<div id="update_user_details" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered max-w-2xl mx-auto my-4" role="document">
        <div class="modal-content bg-white rounded-2xl shadow-2xl border border-neutral-200 overflow-hidden w-full">
            <form class="form flex flex-col max-h-[calc(100vh-4rem)] m-0" action="updateDataSheet" method="post" id="kt_modal_update_user_form" enctype="multipart/form-data">
                <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-100 shrink-0 bg-white">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                            <i class="ph-bold ph-pencil-simple text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-neutral-900 leading-tight">হোল্ডিং ও নাগরিক তথ্য সংশোধন</h3>
                            <span class="text-[11px] text-neutral-500">প্রয়োজনীয় তথ্য পরিবর্তন করে আপডেট বাটনে চাপুন</span>
                        </div>
                    </div>
                    <button type="button" class="w-8 h-8 rounded-xl text-neutral-400 hover:text-neutral-700 hover:bg-neutral-100 flex items-center justify-center transition cursor-pointer" aria-label="বন্ধ করুন" title="বন্ধ করুন" data-dismiss="modal">
                        <i class="ph ph-x text-lg"></i>
                    </button>
                </div>

                <div class="p-6 flex-1 overflow-y-auto space-y-4 min-h-0 bg-white">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-neutral-700 mb-1">জাতীয় পরিচয়পত্র (NID) নম্বর</label>
                        <input type="text" class="w-full text-xs px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition" name="nid_number" value="<?= htmlspecialchars($main_data->nid_number ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-neutral-700 mb-1">জন্ম তারিখ</label>
                        <input type="text" class="w-full text-xs px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition datePicks" name="date_of_birth" value="<?= htmlspecialchars($main_data->date_of_birth ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-3">
                        <label class="block text-xs font-semibold text-neutral-700 mb-1">নাগরিকের পূর্ণ নাম</label>
                        <input type="text" required name="per_name" class="w-full text-xs px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition font-medium" value="<?= htmlspecialchars($main_data->family_person_name_head ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-neutral-700 mb-1">পিতার নাম</label>
                        <input type="text" name="f_name" class="w-full text-xs px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition" value="<?= htmlspecialchars($main_data->pason_father_name ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-neutral-700 mb-1">মাতার নাম</label>
                        <input type="text" name="m_name" class="w-full text-xs px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition" value="<?= htmlspecialchars($main_data->parson_mother_name ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-neutral-700 mb-1">প্রাথমিক মোবাইল</label>
                        <input type="text" name="mobile_no1s" class="w-full text-xs px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition" value="<?= htmlspecialchars($main_data->mobile_number_one ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-neutral-700 mb-1">বর্তমান ঠিকানা</label>
                        <input type="text" name="prs_address" class="w-full text-xs px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition" value="<?= htmlspecialchars($main_data->parson_paranent_address ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-neutral-700 mb-1">স্থায়ী ঠিকানা</label>
                        <input type="text" name="prmnt_address" class="w-full text-xs px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition" value="<?= htmlspecialchars($main_data->parson_parmanent_address ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-neutral-700 mb-1">হোল্ডিং নং</label>
                        <input type="text" required name="holding_number" class="w-full text-xs px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition font-semibold" value="<?= htmlspecialchars($numbers[0] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-neutral-700 mb-1">ওয়ার্ড নং</label>
                        <select class="w-full text-xs px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition selectingWard_no" name="wordNumber" required>
                            <option value="">ওয়ার্ড নির্বাচন করুন</option>
                            <?php for ($i = 1; $i < 10; $i++) { 
                                $w_val = '0' . $i;
                            ?>
                                <option value="<?= $w_val; ?>" <?= ($main_data->word_number == $w_val) ? 'selected' : ''; ?>>
                                    ওয়ার্ড <?= bn_num($w_val); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-neutral-700 mb-1">গ্রাম</label>
                        <select class="w-full text-xs px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition selectingVillage" name="villageName" required>
                            <option value="<?= htmlspecialchars($main_data->vill_pr_iddsid ?? '', ENT_QUOTES, 'UTF-8'); ?>" selected>
                                <?= htmlspecialchars($main_data->bn_vill_names ?? 'গ্রাম নির্বাচন করুন', ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-neutral-700 mb-1">বাড়ি</label>
                        <select class="w-full text-xs px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition selectingHouse" name="houseName" required>
                            <option value="<?= htmlspecialchars($main_data->house_etc_a_iddd ?? '', ENT_QUOTES, 'UTF-8'); ?>" selected>
                                <?= htmlspecialchars($main_data->house_na_me ?? 'বাড়ি নির্বাচন করুন', ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-neutral-700 mb-1">অর্থবছর</label>
                        <div class="flex items-center gap-2">
                            <input type="text" name="holdingStartingDateFirst" maxlength="4" required class="w-24 text-xs px-2.5 py-2.5 bg-white border border-slate-300 rounded-xl text-center font-bold starting_year_firsts" value="<?= htmlspecialchars($h_start_date[0] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            <span class="text-neutral-400 font-bold">-</span>
                            <input type="text" name="holdingStartingDateSecs" maxlength="4" readonly required class="w-24 text-xs px-2.5 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-center font-bold text-slate-600 starting_year_secs" value="<?= htmlspecialchars($h_start_date[1] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-neutral-700 mb-1">অবস্থা/ধরণ</label>
                        <input type="text" required name="holdingCondition" class="w-full text-xs px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition" value="<?= htmlspecialchars($main_data->holding_condition ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-neutral-700 mb-1">বার্ষিক করের হার (৳)</label>
                        <input type="text" required name="holdingRatesYearly" class="w-full text-xs px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition font-bold" value="<?= htmlspecialchars($main_data->holding_rates_yearly ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                </div>
            </div>

            <div class="bg-neutral-50 px-6 py-4 border-t border-neutral-100 flex items-center justify-end gap-3 shrink-0">
                <input type="hidden" name="data_id" value="<?= htmlspecialchars($main_data->data_id ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="uniqID" value="<?= htmlspecialchars($main_data->uniqID ?? '', ENT_QUOTES, 'UTF-8'); ?>">

                <button type="button" class="btn btn-secondary px-4 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-xl transition cursor-pointer" data-dismiss="modal">
                    বাতিল
                </button>
                <button type="submit" class="btn btn-primary inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-sm transition cursor-pointer">
                    <i class="ph-bold ph-check text-xs"></i>
                    <span>তথ্য আপডেট করুন</span>
                </button>
            </div>
        </form>
        </div>
    </div>
</div>

<script>
    $(document).on('change', '.selectingWard_no', function () {
        let thisWard_val = $(this).val();
        if (!thisWard_val) return;
        $.ajax({
            type: "post",
            url: "getVillByWard",
            data: { ward_n: thisWard_val },
            success: function (res) {
                let h_data = '';
                if (Array.isArray(res)) {
                    for (let l = 0; l < res.length; l++) {
                        h_data += `<option value="${res[l].villages_of_union_a_iidd}">${res[l].bn_vill_names}</option>`; 
                    }
                }
                $('.selectingVillage').html(`<option value="">গ্রাম নির্বাচন করুন</option>${h_data}`);
            }
        });
    });

    $(document).on('change', '.selectingVillage', function () {
        let thisVillage_val = $(this).val();
        if (thisVillage_val) {
            get_homes_infos(thisVillage_val);
        }
    });

    $(document).on('keyup', '.starting_year_firsts', function () {
        let first_years = parseInt($(this).val(), 10);
        if ($(this).val() == '' || isNaN(first_years)) {
            $('.starting_year_secs').val('');
        } else {
            $('.starting_year_secs').val(first_years + 1);
        }
    });

    function get_homes_infos(vil_id) {
        $.ajax({
            type: "post",
            url: "getHouseByVillage",
            data: { vill_nos: vil_id },
            success: function (res) {
                let h_data = '';
                if (Array.isArray(res)) {
                    for (let l = 0; l < res.length; l++) {
                        h_data += `<option value="${res[l].house_home_name_etc_iddiddd}">${res[l].house_na_me}</option>`; 
                    }
                }
                $('.selectingHouse').html(`<option value="">বাড়ি নির্বাচন করুন</option>${h_data}`);
            }
        });
    }
</script>
