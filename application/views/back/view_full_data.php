<!-- Breadcrumb & Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-neutral-200">
    <div>
        <nav class="flex items-center gap-2 text-xs text-neutral-500 mb-1">
            <a href="dashboard" class="hover:text-indigo-600 transition flex items-center gap-1">
                <i class="ph ph-house text-sm"></i> ড্যাশবোর্ড
            </a>
            <i class="ph ph-caret-right text-[10px] text-neutral-400"></i>
            <span class="text-neutral-800 font-medium">হোল্ডিং রেজিস্ট্রার</span>
        </nav>
        <h1 class="text-xl sm:text-2xl font-bold text-neutral-900 tracking-tight flex items-center gap-2">
            <span class="w-2.5 h-6 bg-indigo-600 rounded-full inline-block"></span>
            ওয়ার্ডভিত্তিক পূর্ণাঙ্গ হোল্ডিং রেজিস্ট্রার
        </h1>
        <p class="text-xs sm:text-sm text-neutral-500 mt-0.5">ইউনিয়নের সকল ওয়ার্ডের নাগরিক তালিকা ও হোল্ডিং প্লেট ব্যবস্থাপনা</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="addData" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm transition">
            <i class="ph-bold ph-plus text-sm"></i> নতুন হোল্ডিং এন্ট্রি
        </a>
    </div>
</div>

<!-- Controls Bar -->
<div class="bg-white rounded-2xl border border-neutral-200/80 shadow-sm p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div class="flex flex-wrap items-center gap-3">
        <div class="flex items-center gap-2">
            <label class="text-xs font-bold text-neutral-700">ওয়ার্ড নির্বাচন:</label>
            <select class="text-xs font-semibold px-3 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition selecting_ward_no">
                <?php for ($w = 1; $w <= 9; $w++) { 
                    $w_str = sprintf('%02d', $w);
                ?>
                    <option value="<?= $w_str; ?>">ওয়ার্ড নং <?= bn_num($w_str); ?></option>
                <?php } ?>
            </select>
        </div>

        <span class="text-neutral-300 hidden sm:inline">|</span>

        <div id="holding_count_badge" class="text-xs font-semibold text-neutral-600 bg-neutral-100 px-3 py-1.5 rounded-xl border border-neutral-200/60">
            মোট হোল্ডিং: <span class="text-indigo-600 font-bold" id="total_holdings_count">০</span> টি
        </div>
    </div>

    <div>
        <button type="button" class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white rounded-xl text-xs font-semibold shadow-2xs transition full_plate_btnss cursor-pointer">
            <i class="ph-bold ph-download-simple text-xs"></i>
            <span>এই ওয়ার্ডের হোল্ডিং প্লেট ডাউনলোড</span>
        </button>
    </div>
</div>

<!-- Data Table Card -->
<div class="bg-white rounded-2xl border border-neutral-200/80 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-neutral-50/80 border-b border-neutral-200/80 text-neutral-600 font-bold">
                    <th class="py-3 px-4 w-12 text-center">ক্রমিক</th>
                    <th class="py-3 px-4">নাগরিক ও হোল্ডিং</th>
                    <th class="py-3 px-4">পিতা ও মাতা</th>
                    <th class="py-3 px-4">যোগাযোগ</th>
                    <th class="py-3 px-4">গ্রাম ও বাড়ি</th>
                    <th class="py-3 px-4 text-right">শুরু ও করের হার</th>
                    <th class="py-3 px-4 text-center w-24">অ্যাকশন</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-100 html_tr_datas">
                <tr>
                    <td colspan="7" class="py-12 text-center text-neutral-400">
                        <div class="flex flex-col items-center gap-2">
                            <i class="ph ph-spinner animate-spin text-2xl text-indigo-600"></i>
                            <span>তথ্য লোড হচ্ছে...</span>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
    $(document).ready(function() {
        get_data_by_wards();
    });

    $(document).on('change', '.selecting_ward_no', function () {
        let ward_noss = $(this).val();
        get_data_by_wards(ward_noss);
    });

    function get_data_by_wards(ward) {
        if (!ward) ward = $('.selecting_ward_no').val() || '01';

        $('.html_tr_datas').html(`
            <tr>
                <td colspan="7" class="py-12 text-center text-neutral-400">
                    <div class="flex flex-col items-center gap-2">
                        <i class="ph ph-spinner animate-spin text-2xl text-indigo-600"></i>
                        <span>তথ্য লোড হচ্ছে...</span>
                    </div>
                </td>
            </tr>
        `);

        $.ajax({
            type: "post",
            url: "getDataByWardNo",
            data: { wards: ward },
            dataType: "json",
            success: function (res) {
                let html_data = '';
                let total = (Array.isArray(res)) ? res.length : 0;
                $('#total_holdings_count').text(total);

                if (total === 0) {
                    $('.html_tr_datas').html(`
                        <tr>
                            <td colspan="7" class="py-12 text-center text-neutral-400">
                                <div class="flex flex-col items-center gap-2">
                                    <i class="ph ph-folder-open text-3xl text-neutral-300"></i>
                                    <span>এই ওয়ার্ডে কোন হোল্ডিং তথ্য পাওয়া যায়নি।</span>
                                </div>
                            </td>
                        </tr>
                    `);
                    return;
                }

                for (let a = 0; a < res.length; a++) {
                    let r = res[a];
                    html_data += `
                        <tr class="hover:bg-neutral-50/80 transition group">
                            <td class="py-3 px-4 text-center font-mono text-neutral-500">${a + 1}</td>
                            <td class="py-3 px-4">
                                <a href="view?uniq_id=${r.uniqID}" class="font-bold text-neutral-900 group-hover:text-indigo-600 transition block">
                                    ${r.family_person_name_head || '—'}
                                </a>
                                <span class="inline-block mt-0.5 px-2 py-0.5 rounded text-[10px] font-mono font-semibold bg-indigo-50 text-indigo-700">
                                    হোল্ডিং: ${r.holding_number || '—'}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-neutral-600 leading-relaxed">
                                <div><span class="text-neutral-400">পিতা:</span> ${r.pason_father_name || '—'}</div>
                                <div><span class="text-neutral-400">মাতা:</span> ${r.parson_mother_name || '—'}</div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-mono text-neutral-800">${r.mobile_number_one || '—'}</div>
                                ${r.mobile_number_two ? `<div class="font-mono text-neutral-500 text-[11px]">${r.mobile_number_two}</div>` : ''}
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 rounded-lg text-xs font-semibold bg-neutral-100 text-neutral-700 block max-w-fit">
                                    ${r.bn_vill_names || ''}${r.house_na_me ? ', ' + r.house_na_me : ''}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="font-bold text-neutral-900">৳ ${r.holding_rates_yearly || '0'}</div>
                                <div class="text-[11px] text-neutral-500 font-mono">${r.holding_starting_years || '—'}</div>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <a href="view?uniq_id=${r.uniqID}" class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition" title="বিস্তারিত দেখুন">
                                    <i class="ph-bold ph-eye text-sm"></i>
                                </a>
                            </td>
                        </tr>`;
                }
                $('.html_tr_datas').html(html_data);
            },
            error: function() {
                $('.html_tr_datas').html(`
                    <tr>
                        <td colspan="7" class="py-8 text-center text-rose-500">
                            তথ্য লোড করতে সমস্যা হয়েছে। পুনরায় চেষ্টা করুন।
                        </td>
                    </tr>
                `);
            }
        });
    }

    $(document).on('click', '.full_plate_btnss', function () {
        let ward_no = $('.selecting_ward_no').val(); 
        window.open('fullPlateWard?ward=' + ward_no, '_blank', 'width=800,height=800,left=300,top=300'); 
    });
</script>
