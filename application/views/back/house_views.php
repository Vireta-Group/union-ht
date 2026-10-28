<!-- Breadcrumb & Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-neutral-200">
    <div>
        <nav class="flex items-center gap-2 text-xs text-neutral-500 mb-1">
            <a href="dashboard" class="hover:text-indigo-600 transition flex items-center gap-1">
                <i class="ph ph-house text-sm"></i> ড্যাশবোর্ড
            </a>
            <i class="ph ph-caret-right text-[10px] text-neutral-400"></i>
            <span class="text-neutral-800 font-medium">বাড়ি ব্যবস্থাপনা</span>
        </nav>
        <h1 class="text-xl sm:text-2xl font-bold text-neutral-900 tracking-tight flex items-center gap-2">
            <span class="w-2.5 h-6 bg-indigo-600 rounded-full inline-block"></span>
            ইউনিয়নের বাড়ির তালিকা ও সংযোজন
        </h1>
        <p class="text-xs sm:text-sm text-neutral-500 mt-0.5">ওয়ার্ড ও গ্রামভিত্তিক বাড়ি/প্যারা ব্যবস্থাপনা ও নাম সংশোধন</p>
    </div>
</div>

<!-- Add New House Card -->
<div class="bg-white rounded-2xl border border-neutral-200/80 shadow-sm p-5 space-y-4">
    <div class="flex items-center gap-2 pb-3 border-b border-neutral-100">
        <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
            <i class="ph-bold ph-plus-circle text-base"></i>
        </div>
        <h3 class="text-sm font-bold text-neutral-900">নতুন বাড়ি যোগ করুন</h3>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-4 gap-4 items-end">
        <div>
            <label class="block text-xs font-semibold text-neutral-700 mb-1">ওয়ার্ড নং</label>
            <select class="w-full text-xs px-3.5 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition selectingWard_no">
                <option value="">ওয়ার্ড নির্বাচন করুন</option>
                <?php for ($w = 1; $w <= 9; $w++) { 
                    $w_str = sprintf('%02d', $w);
                ?>
                    <option value="<?= $w_str; ?>">ওয়ার্ড নং <?= bn_num($w_str); ?></option>
                <?php } ?>
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-neutral-700 mb-1">গ্রাম</label>
            <select class="w-full text-xs px-3.5 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition selectingVillage">
                <option value="">প্রথমে ওয়ার্ড সিলেক্ট করুন</option>
            </select>
        </div>

        <div class="lg:col-span-2 flex items-end gap-2">
            <div class="flex-1">
                <label class="block text-xs font-semibold text-neutral-700 mb-1">বাড়ির নাম</label>
                <input type="text" class="w-full text-xs px-3.5 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition house_home_names" placeholder="উদাঃ খাঁ বাড়ি / চৌধুরী বাড়ি">
            </div>
            <button type="button" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white rounded-xl text-xs font-semibold shadow-2xs transition shrink-0 add_new_submit_btn">
                <i class="ph-bold ph-plus text-xs"></i>
                <span>যোগ করুন</span>
            </button>
        </div>
    </div>
</div>

<!-- House List Table Card -->
<div class="bg-white rounded-2xl border border-neutral-200/80 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-neutral-100 flex items-center justify-between">
        <h3 class="text-sm font-bold text-neutral-900 flex items-center gap-2">
            <i class="ph-bold ph-house-line text-indigo-600 text-base"></i>
            <span>নিবন্ধিত বাড়ির তালিকা</span>
        </h3>
        <span class="text-xs font-semibold text-neutral-500 bg-neutral-100 px-3 py-1 rounded-full" id="house_count">
            মোট বাড়ি: ০
        </span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-neutral-50/80 border-b border-neutral-200/80 text-neutral-600 font-bold">
                    <th class="py-3 px-4 w-16 text-center">ক্রমিক</th>
                    <th class="py-3 px-4">বাড়ির নাম</th>
                    <th class="py-3 px-4">গ্রাম</th>
                    <th class="py-3 px-4 text-center">ওয়ার্ড নং</th>
                    <th class="py-3 px-4 text-center w-28">অ্যাকশন</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-100 html_tr_datas">
                <tr>
                    <td colspan="5" class="py-12 text-center text-neutral-400">
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

    $(document).on('change', '.selectingWard_no', function () {
        let ward_noss = $(this).val();
        get_data_by_wards(ward_noss);

        if (!ward_noss) {
            $('.selectingVillage').html('<option value="">প্রথমে ওয়ার্ড সিলেক্ট করুন</option>');
            return;
        }

        $.ajax({
            type: "post",
            url: "getVillByWard",
            data: { ward_n: ward_noss },
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

    function get_data_by_wards(ward) {
        if (!ward) ward = $('.selectingWard_no').val() || '';

        $.ajax({
            type: "post",
            url: "getHomesbyWard",
            data: { wards: ward },
            dataType: "json",
            success: function (res) {
                let html_data = '';
                let total = (Array.isArray(res)) ? res.length : 0;
                $('#house_count').text('মোট বাড়ি: ' + total);

                if (total === 0) {
                    $('.html_tr_datas').html(`
                        <tr>
                            <td colspan="5" class="py-12 text-center text-neutral-400">
                                <div class="flex flex-col items-center gap-2">
                                    <i class="ph ph-house text-3xl text-neutral-300"></i>
                                    <span>কোন বাড়ি পাওয়া যায়নি।</span>
                                </div>
                            </td>
                        </tr>
                    `);
                    return;
                }

                for (let a = 0; a < res.length; a++) {
                    let r = res[a];
                    html_data += `
                        <tr class="hover:bg-neutral-50/80 transition group home_name_tr_show">
                            <td class="py-3 px-4 text-center font-mono text-neutral-500">${a + 1}</td>
                            <td class="py-3 px-4 font-bold text-neutral-900 house_name_asgn">
                                ${r.house_na_me || '—'}
                            </td>
                            <td class="py-3 px-4 text-neutral-700 font-medium">${r.bn_vill_names || '—'}</td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700">
                                    ওয়ার্ড ${r.wrds_no_s || '—'}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <button type="button" class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-neutral-100 text-neutral-700 hover:bg-indigo-600 hover:text-white transition editing_house_name_btn" attr_ids="${r.house_home_name_etc_iddiddd}" title="নাম সংশোধন">
                                    <i class="ph-bold ph-pencil-simple text-sm"></i>
                                </button>
                            </td>
                        </tr>`;
                }
                $('.html_tr_datas').html(html_data);
            },
            error: function() {
                $('.html_tr_datas').html(`
                    <tr>
                        <td colspan="5" class="py-8 text-center text-rose-500">
                            তথ্য লোড করতে সমস্যা হয়েছে।
                        </td>
                    </tr>
                `);
            }
        });
    }

    $(document).on('click', '.add_new_submit_btn', function () {
        let ward_no = $('.selectingWard_no').val();
        let vill_no = $('.selectingVillage').val();
        let homes_text = $('.house_home_names').val().trim();

        if (!ward_no || !vill_no || !homes_text) {
            Swal.fire({
                title: 'তথ্য অসম্পূর্ণ',
                text: 'অনুগ্রহ করে ওয়ার্ড, গ্রাম এবং বাড়ির নাম লিখুন।',
                icon: 'warning',
                confirmButtonText: 'ঠিক আছে',
                confirmButtonColor: '#4f46e5'
            });
            return;
        }

        $.ajax({
            type: "post",
            url: "addNewHome",
            data: {
                ward_no: ward_no,
                vill_no: vill_no,
                homes_text: homes_text,
            },
            success: function (re) {
                $('.house_home_names').val('');
                get_data_by_wards(ward_no);
            }
        });
    });

    $(document).on('click', '.editing_house_name_btn', function () {
        let $btn = $(this);
        $btn.html('<i class="ph-bold ph-check text-base text-emerald-600"></i>');
        $btn.addClass('edit_homesss_btn bg-emerald-50 hover:bg-emerald-600 hover:text-white').removeClass('editing_house_name_btn bg-neutral-100');
        let $row = $btn.closest('.home_name_tr_show');
        let house_name_val = $row.find('.house_name_asgn').text().trim();

        $row.find('.house_name_asgn').html(`
            <input type="text" class="w-full text-xs px-2.5 py-1.5 bg-white border border-indigo-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 house_name_s_edit_text" value="${house_name_val}">
        `);
        $row.find('.house_name_s_edit_text').focus();
    });

    $(document).on('click', '.edit_homesss_btn', function () {
        let $btn = $(this);
        let ward_no = $('.selectingWard_no').val();
        let house_id = $btn.attr('attr_ids');
        let $row = $btn.closest('.home_name_tr_show');
        let house_names_val = $row.find('.house_name_s_edit_text').val().trim();

        if (!house_names_val) {
            Swal.fire({
                title: 'বাড়ির নাম প্রয়োজন',
                text: 'বাড়ির নাম খালি রাখা যাবে না।',
                icon: 'warning',
                confirmButtonText: 'ঠিক আছে',
                confirmButtonColor: '#4f46e5'
            });
            return;
        }

        $.ajax({
            type: "post",
            url: "editHomeName",
            data: {
                id: house_id,
                name: house_names_val,
            },
            success: function (r) {
                get_data_by_wards(ward_no);
            }
        });
    });
</script>
