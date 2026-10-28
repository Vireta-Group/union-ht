<!-- Breadcrumb & Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-neutral-200">
    <div>
        <nav class="flex items-center gap-2 text-xs text-neutral-500 mb-1">
            <a href="dashboard" class="hover:text-indigo-600 transition flex items-center gap-1">
                <i class="ph ph-house text-sm"></i> ড্যাশবোর্ড
            </a>
            <i class="ph ph-caret-right text-[10px] text-neutral-400"></i>
            <a href="view_datas" class="hover:text-indigo-600 transition">হোল্ডিং রেজিস্ট্রার</a>
            <i class="ph ph-caret-right text-[10px] text-neutral-400"></i>
            <span class="text-neutral-800 font-medium">ডকুমেন্ট ভিউ</span>
        </nav>
        <h1 class="text-xl sm:text-2xl font-bold text-neutral-900 tracking-tight flex items-center gap-2">
            <span class="w-2.5 h-6 bg-indigo-600 rounded-full inline-block"></span>
            হোল্ডিং ডেটা শিট ও ডকুমেন্ট ভিউ
        </h1>
        <p class="text-xs sm:text-sm text-neutral-500 mt-0.5">অফিসিয়াল নথিপত্র ও ওয়ার্ডভিত্তিক ডেটা শিট প্রিন্ট ও ডাউনলোড</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="view_datas" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold bg-white border border-neutral-300 text-neutral-700 hover:bg-neutral-50 shadow-sm transition">
            <i class="ph ph-table text-sm"></i> সাধারণ রেজিস্ট্রার
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

        <div class="text-xs font-semibold text-neutral-600 bg-neutral-100 px-3 py-1.5 rounded-xl border border-neutral-200/60">
            মোট রেকর্ড: <span class="text-indigo-600 font-bold" id="doc_count">০</span> টি
        </div>
    </div>

    <div>
        <button type="button" class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white rounded-xl text-xs font-semibold shadow-2xs transition download_doc_btns cursor-pointer">
            <i class="ph-bold ph-file-doc text-xs"></i>
            <span>ওয়ার্ড ডেটা শিট ডাউনলোড (DOC)</span>
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
                    <th class="py-3 px-4">হোল্ডিং নং</th>
                    <th class="py-3 px-4">নাগরিকের নাম</th>
                    <th class="py-3 px-4">পিতার নাম</th>
                    <th class="py-3 px-4">বাড়ির নাম</th>
                    <th class="py-3 px-4">ধরণ</th>
                    <th class="py-3 px-4 text-right">বার্ষিক কর</th>
                    <th class="py-3 px-4 text-center">শুরুর বছর</th>
                    <th class="py-3 px-4 text-center w-20">অ্যাকশন</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-100 html_tr_datas">
                <tr>
                    <td colspan="9" class="py-12 text-center text-neutral-400">
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
                <td colspan="9" class="py-12 text-center text-neutral-400">
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
                $('#doc_count').text(total);

                if (total === 0) {
                    $('.html_tr_datas').html(`
                        <tr>
                            <td colspan="9" class="py-12 text-center text-neutral-400">
                                <div class="flex flex-col items-center gap-2">
                                    <i class="ph ph-file-text text-3xl text-neutral-300"></i>
                                    <span>এই ওয়ার্ডে কোন রেকর্ড পাওয়া যায়নি।</span>
                                </div>
                            </td>
                        </tr>
                    `);
                    return;
                }

                for (let a = 0; a < res.length; a++) {
                    let r = res[a];
                    html_data += `
                        <tr class="hover:bg-neutral-50/80 transition">
                            <td class="py-3 px-4 text-center font-mono text-neutral-500">${a + 1}</td>
                            <td class="py-3 px-4 font-mono font-bold text-neutral-800">${r.holding_number || '—'}</td>
                            <td class="py-3 px-4 font-bold text-neutral-900">${r.family_person_name_head || '—'}</td>
                            <td class="py-3 px-4 text-neutral-600">${r.pason_father_name || '—'}</td>
                            <td class="py-3 px-4 text-neutral-700">${r.house_na_me || '—'}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[11px] bg-neutral-100 text-neutral-600 font-medium">
                                    ${r.holding_condition || '—'}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right font-bold text-neutral-900">৳ ${r.holding_rates_yearly || '0'}</td>
                            <td class="py-3 px-4 text-center font-mono text-neutral-600">${r.holding_starting_years || '—'}</td>
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
                        <td colspan="9" class="py-8 text-center text-rose-500">
                            তথ্য লোড করতে সমস্যা হয়েছে। পুনরায় চেষ্টা করুন।
                        </td>
                    </tr>
                `);
            }
        });
    }

    $(document).on('click', '.download_doc_btns', function () {
        let ward_no = $('.selecting_ward_no').val(); 
        window.open('d_doc?ward=' + ward_no, '_blank'); 
    });
</script>
