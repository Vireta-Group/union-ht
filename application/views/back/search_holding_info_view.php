<!-- Breadcrumb & Header -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-neutral-200/80">
    <div>
        <nav class="flex items-center gap-2 text-xs text-neutral-400 mb-1">
            <a href="dashboard" class="hover:text-indigo-600 transition-colors flex items-center gap-1">
                <i class="ph-bold ph-gauge"></i> ড্যাশবোর্ড
            </a>
            <span>/</span>
            <span class="text-neutral-500">হোল্ডিং কর</span>
            <span>/</span>
            <span class="text-indigo-600 font-semibold">হোল্ডিং অনুসন্ধান</span>
        </nav>
        <h2 class="text-xl sm:text-2xl font-extrabold text-neutral-900 tracking-tight">
            হোল্ডিং ও নাগরিক খানা অনুসন্ধান
        </h2>
        <p class="text-xs text-neutral-500">হোল্ডিং নম্বর অথবা ওয়ার্ড, গ্রাম ও বাড়ির নাম দিয়ে করদাতার তথ্য খুঁজে বের করুন</p>
    </div>
    
    <div class="flex items-center gap-2">
        <a href="addData" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:scale-98 text-white font-bold text-xs flex items-center gap-1.5 shadow-2xs transition-all">
            <i class="ph-bold ph-plus-circle text-sm"></i> নতুন হোল্ডিং যোগ
        </a>
        <a href="view_datas" class="px-4 py-2 rounded-xl bg-white border border-neutral-200 hover:bg-neutral-50 text-neutral-700 font-bold text-xs flex items-center gap-1.5 shadow-2xs transition-all">
            <i class="ph-bold ph-list-dashes text-sm text-neutral-600"></i> সকল হোল্ডিং তালিকা
        </a>
    </div>
</div>

<!-- Search & Filtering Workspace -->
<div class="bg-white rounded-2xl p-5 sm:p-6 border border-neutral-200/80 shadow-xs space-y-6">
    
    <!-- Option 1: Direct Holding Number Search -->
    <div>
        <label class="block text-xs font-bold text-neutral-700 mb-2">
            ১. সরাসরি হোল্ডিং নম্বর দিয়ে খুঁজুন
        </label>
        <div class="flex items-center gap-2 max-w-xl">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-neutral-400">
                    <i class="ph-bold ph-magnifying-glass text-lg"></i>
                </div>
                <input 
                    type="text" 
                    class="type_holding_no_s block w-full pl-10 pr-4 py-2 rounded-xl bg-neutral-50 border border-neutral-200 text-neutral-900 placeholder-neutral-400 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all" 
                    placeholder="হোল্ডিং নম্বর লিখুন (উদাঃ 5045-09 বা 5045)..."
                    autocomplete="off"
                />
            </div>
            <button 
                type="button" 
                class="search_holding_no_btn px-3.5 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:scale-98 text-white font-semibold text-xs flex items-center gap-1.5 shadow-2xs transition-all cursor-pointer">
                <i class="ph-bold ph-magnifying-glass text-xs"></i>
                <span>সার্চ</span>
            </button>
        </div>
    </div>

    <!-- Divider -->
    <div class="relative flex py-2 items-center">
        <div class="flex-grow border-t border-neutral-200"></div>
        <span class="flex-shrink mx-4 text-xs font-bold text-neutral-400 uppercase tracking-wider">অথবা ফিল্টার করুন</span>
        <div class="flex-grow border-t border-neutral-200"></div>
    </div>

    <!-- Option 2: Hierarchical Filters -->
    <div>
        <label class="block text-xs font-bold text-neutral-700 mb-3">
            ২. এলাকাভিত্তিক নির্বাচন (ওয়ার্ড &rarr; গ্রাম &rarr; বাড়ির নাম)
        </label>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            
            <!-- Ward Select -->
            <div>
                <label for="select_ward" class="block text-[11px] font-bold text-neutral-500 mb-1">ওয়ার্ড নম্বর</label>
                <select id="select_ward" class="selectingWard_no block w-full px-3 py-2 rounded-xl bg-neutral-50 border border-neutral-200 text-neutral-900 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all cursor-pointer">
                    <option value="">ওয়ার্ড নির্বাচন করুন</option>
                    <option value="01">১ নং ওয়ার্ড</option>
                    <option value="02">২ নং ওয়ার্ড</option>
                    <option value="03">৩ নং ওয়ার্ড</option>
                    <option value="04">৪ নং ওয়ার্ড</option>
                    <option value="05">৫ নং ওয়ার্ড</option>
                    <option value="06">৬ নং ওয়ার্ড</option>
                    <option value="07">৭ নং ওয়ার্ড</option>
                    <option value="08">৮ নং ওয়ার্ড</option>
                    <option value="09">৯ নং ওয়ার্ড</option>
                </select>
            </div>

            <!-- Village Select -->
            <div>
                <label for="select_village" class="block text-[11px] font-bold text-neutral-500 mb-1">গ্রামের নাম</label>
                <select id="select_village" class="selectingVillage block w-full px-3 py-2 rounded-xl bg-neutral-50 border border-neutral-200 text-neutral-900 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all cursor-pointer">
                    <option value="">প্রথমে ওয়ার্ড নির্বাচন করুন</option>
                </select>
            </div>

            <!-- House Name Select -->
            <div>
                <label for="select_house" class="block text-[11px] font-bold text-neutral-500 mb-1">বাড়ির নাম</label>
                <select id="select_house" class="selectingHouse block w-full px-3 py-2 rounded-xl bg-neutral-50 border border-neutral-200 text-neutral-900 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all cursor-pointer">
                    <option value="">প্রথমে গ্রাম নির্বাচন করুন</option>
                </select>
            </div>

        </div>
    </div>

</div>

<!-- Results Table Container -->
<div class="bg-white rounded-2xl p-5 sm:p-6 border border-neutral-200/80 shadow-xs space-y-4">
    <div class="flex items-center justify-between border-b border-neutral-100 pb-3">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg">
                <i class="ph-bold ph-list-dashes"></i>
            </div>
            <div>
                <h3 class="text-base font-bold text-neutral-900">অনুসন্ধানের ফলাফল</h3>
                <p class="text-xs text-neutral-400">প্রাপ্ত হোল্ডিং ও নাগরিক তালিকা</p>
            </div>
        </div>
        <span class="text-xs font-semibold text-neutral-400 result_count_badge"></span>
    </div>

    <div class="overflow-x-auto -mx-6 px-6">
        <table class="w-full text-left text-xs">
            <thead>
                <tr class="text-neutral-400 border-b border-neutral-100 font-bold uppercase tracking-wider">
                    <th class="py-3">করদাতার নাম ও হোল্ডিং</th>
                    <th class="py-3">পিতা ও মাতার নাম</th>
                    <th class="py-3">মোবাইল নম্বর</th>
                    <th class="py-3">গ্রাম ও বাড়ি</th>
                    <th class="py-3 text-right">শুরু ও বাৎসরিক কর</th>
                    <th class="py-3 text-center">অ্যাকশন</th>
                </tr>
            </thead>
            <tbody class="html_tr_datas divide-y divide-neutral-100">
                <tr>
                    <td colspan="6" class="py-12 text-center text-neutral-400">
                        <i class="ph-bold ph-magnifying-glass text-3xl text-neutral-300 block mb-2"></i>
                        উপরে হোল্ডিং নম্বর দিয়ে খুঁজুন অথবা ওয়ার্ড নির্বাচন করুন
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
    function bn_num(number) {
        if (!number) return '';
        const en = ['0','1','2','3','4','5','6','7','8','9'];
        const bn = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
        return String(number).replace(/[0-9]/g, function(w){
            return bn[+w];
        });
    }

    // 1. Ward Change -> Fetch Villages
    $(document).on('change', '.selectingWard_no', function () {
        let thisWard_val = $(this).val();
        if (!thisWard_val) {
            $('.selectingVillage').html('<option value="">প্রথমে ওয়ার্ড নির্বাচন করুন</option>');
            $('.selectingHouse').html('<option value="">প্রথমে গ্রাম নির্বাচন করুন</option>');
            return;
        }

        $('.selectingVillage').html('<option value="">গ্রাম লোড হচ্ছে...</option>');
        $.ajax({
            type: "post",
            url: "getVillByWard",
            data: { ward_n: thisWard_val },
            dataType: "json",
            success: function (res) {
                let h_data = '';
                if (res && res.length > 0) {
                    for (let l = 0; l < res.length; l++) {
                        h_data += `<option value="${res[l].villages_of_union_a_iidd}">${res[l].bn_vill_names}</option>`;
                    }
                    $('.selectingVillage').html(`<option value="">গ্রাম নির্বাচন করুন (${bn_num(res.length)}টি পাওয়া গেছে)</option>${h_data}`);
                } else {
                    $('.selectingVillage').html('<option value="">কোনো গ্রাম পাওয়া যায়নি</option>');
                }
                $('.selectingHouse').html('<option value="">প্রথমে গ্রাম নির্বাচন করুন</option>');
            }
        });
    });

    // 2. Village Change -> Fetch Houses
    $(document).on('change', '.selectingVillage', function () {
        let thisVillage_val = $(this).val();
        if (!thisVillage_val) {
            $('.selectingHouse').html('<option value="">প্রথমে গ্রাম নির্বাচন করুন</option>');
            return;
        }

        $('.selectingHouse').html('<option value="">বাড়ি লোড হচ্ছে...</option>');
        $.ajax({
            type: "post",
            url: "getHouseByVillage",
            data: { vill_nos: thisVillage_val },
            dataType: "json",
            success: function (res) {
                let h_data = '';
                if (res && res.length > 0) {
                    for (let l = 0; l < res.length; l++) {
                        h_data += `<option value="${res[l].house_home_name_etc_iddiddd}">${res[l].house_na_me}</option>`;
                    }
                    $('.selectingHouse').html(`<option value="">বাড়ির নাম নির্বাচন করুন (${bn_num(res.length)}টি বাড়ি)</option>${h_data}`);
                } else {
                    $('.selectingHouse').html('<option value="">কোনো বাড়ি পাওয়া যায়নি</option>');
                }
            }
        });
    });

    // 3. House Change -> Fetch Holdings
    $(document).on('change', '.selectingHouse', function () {
        let house_noss = $(this).val();
        if (!house_noss) return;

        $('.html_tr_datas').html(`
            <tr>
                <td colspan="6" class="py-10 text-center text-neutral-400">
                    <div class="inline-block animate-spin text-2xl text-indigo-600 mb-2">
                        <i class="ph-bold ph-spinner-gap"></i>
                    </div>
                    <div>হোল্ডিং তথ্য খোঁজা হচ্ছে...</div>
                </td>
            </tr>
        `);

        $.ajax({
            type: "post",
            url: "getHoldingByHouse",
            data: { house_no: house_noss },
            dataType: "json",
            success: function (res) {
                if (res && res.length > 0) {
                    let html_data = '';
                    for (let a = 0; a < res.length; a++) {
                        const item = res[a];
                        html_data += `
                            <tr class="hover:bg-neutral-50/80 transition-colors">
                                <td class="py-3.5">
                                    <a href="view?uniq_id=${item.uniqID}" class="font-bold text-neutral-900 hover:text-indigo-600 text-sm block">
                                        ${item.family_person_name_head || 'অজ্ঞাত'}
                                    </a>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-indigo-50 text-indigo-700 mt-1">
                                        হোল্ডিং: ${bn_num(item.holding_number)}
                                    </span>
                                </td>
                                <td class="py-3.5 text-neutral-600">
                                    <div><span class="text-neutral-400 text-[10px]">পিতা:</span> ${item.pason_father_name || '-'}</div>
                                    <div><span class="text-neutral-400 text-[10px]">মাতা:</span> ${item.parson_mother_name || '-'}</div>
                                </td>
                                <td class="py-3.5">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-neutral-100 text-neutral-800">
                                        <i class="ph-bold ph-phone text-emerald-600"></i> ${bn_num(item.mobile_number_one || item.mobile_number_two || '-')}
                                    </span>
                                </td>
                                <td class="py-3.5 text-neutral-600">
                                    <div class="font-semibold text-neutral-800">${item.bn_vill_names || '-'}</div>
                                    <div class="text-[11px] text-neutral-400">${item.house_na_me || '-'}</div>
                                </td>
                                <td class="py-3.5 text-right">
                                    <div class="font-bold text-indigo-700">&#2547; ${bn_num(item.holding_rates_yearly)}/-</div>
                                    <div class="text-[11px] text-neutral-400">সাল: ${bn_num(item.holding_starting_years || '-')}</div>
                                </td>
                                <td class="py-3.5 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <a href="view?uniq_id=${item.uniqID}" class="p-2 rounded-xl bg-neutral-100 hover:bg-indigo-50 hover:text-indigo-600 text-neutral-600 transition-colors" title="বিস্তারিত দেখুন">
                                            <i class="ph-bold ph-eye text-sm"></i>
                                        </a>
                                        <button type="button" onclick="window.location='taxCollection'" class="p-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 transition-colors" title="ট্যাক্স আদায় করুন">
                                            <i class="ph-bold ph-hand-coins text-sm"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `;
                    }
                    $('.html_tr_datas').html(html_data);
                    $('.result_count_badge').text(`মোট ${bn_num(res.length)} টি খানা পাওয়া গেছে`);
                } else {
                    $('.html_tr_datas').html(`
                        <tr>
                            <td colspan="6" class="py-10 text-center text-rose-600 font-semibold">
                                <i class="ph-bold ph-warning-circle text-3xl block mb-2 text-rose-400"></i>
                                এই বাড়ির অধীনে কোনো হোল্ডিং তথ্য পাওয়া যায়নি।
                            </td>
                        </tr>
                    `);
                    $('.result_count_badge').text('');
                }
            }
        });
    });

    // 4. Direct Holding Number Search
    function performHoldingSearch() {
        let type_holding_no = $('.type_holding_no_s').val().trim();
        if (!type_holding_no) {
            Swal.fire({
                title: 'হোল্ডিং নম্বর প্রয়োজন',
                text: 'অনুগ্রহ করে অনুসন্ধানের জন্য হোল্ডিং নম্বর লিখুন।',
                icon: 'warning',
                confirmButtonText: 'ঠিক আছে',
                confirmButtonColor: '#4f46e5'
            });
            return;
        }

        $('.html_tr_datas').html(`
            <tr>
                <td colspan="6" class="py-10 text-center text-neutral-400">
                    <div class="inline-block animate-spin text-2xl text-indigo-600 mb-2">
                        <i class="ph-bold ph-spinner-gap"></i>
                    </div>
                    <div>হোল্ডিং অনুসন্ধান করা হচ্ছে...</div>
                </td>
            </tr>
        `);

        $.ajax({
            type: "post",
            url: "getHoldingByNum",
            data: { holding_no: type_holding_no },
            dataType: "json",
            success: function (res) {
                if (res && res.unqID) {
                    $('.html_tr_datas').html(`
                        <tr class="hover:bg-neutral-50/80 transition-colors">
                            <td class="py-3.5">
                                <a href="view?uniq_id=${res.unqID}" class="font-bold text-neutral-900 hover:text-indigo-600 text-sm block">
                                    ${res.pName || 'অজ্ঞাত'}
                                </a>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-indigo-50 text-indigo-700 mt-1">
                                    হোল্ডিং: ${bn_num(res.holdNo)}
                                </span>
                            </td>
                            <td class="py-3.5 text-neutral-600">
                                <div><span class="text-neutral-400 text-[10px]">পিতা:</span> ${res.fName || '-'}</div>
                                <div><span class="text-neutral-400 text-[10px]">মাতা:</span> ${res.mName || '-'}</div>
                            </td>
                            <td class="py-3.5">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-neutral-100 text-neutral-800">
                                    <i class="ph-bold ph-phone text-emerald-600"></i> ${bn_num(res.mobile1 || res.phone2 || '-')}
                                </span>
                            </td>
                            <td class="py-3.5 text-neutral-600">
                                <div class="font-semibold text-neutral-800">${res.villName || '-'}</div>
                                <div class="text-[11px] text-neutral-400">${res.homName || '-'}</div>
                            </td>
                            <td class="py-3.5 text-right">
                                <div class="font-bold text-indigo-700">&#2547; ${bn_num(res.rates)}/-</div>
                                <div class="text-[11px] text-neutral-400">সাল: ${bn_num(res.start || '-')}</div>
                            </td>
                            <td class="py-3.5 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="view?uniq_id=${res.unqID}" class="p-2 rounded-xl bg-neutral-100 hover:bg-indigo-50 hover:text-indigo-600 text-neutral-600 transition-colors" title="বিস্তারিত দেখুন">
                                        <i class="ph-bold ph-eye text-sm"></i>
                                    </a>
                                    <button type="button" onclick="window.location='taxCollection'" class="p-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 transition-colors" title="ট্যাক্স আদায় করুন">
                                        <i class="ph-bold ph-hand-coins text-sm"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `);
                    $('.result_count_badge').text('১টি রেকর্ড পাওয়া গেছে');
                } else {
                    $('.html_tr_datas').html(`
                        <tr>
                            <td colspan="6" class="py-10 text-center text-rose-600 font-semibold">
                                <i class="ph-bold ph-warning-circle text-3xl block mb-2 text-rose-400"></i>
                                "${type_holding_no}" নম্বরে কোনো হোল্ডিং পাওয়া যায়নি।
                            </td>
                        </tr>
                    `);
                    $('.result_count_badge').text('');
                }
            },
            error: function() {
                $('.html_tr_datas').html(`
                    <tr>
                        <td colspan="6" class="py-10 text-center text-rose-600">
                            সার্ভারে সংযোগ স্থাপন করা যায়নি। আবার চেষ্টা করুন।
                        </td>
                    </tr>
                `);
            }
        });
    }

    $(document).on('click', '.search_holding_no_btn', performHoldingSearch);
    $(document).on('keypress', '.type_holding_no_s', function (e) {
        if (e.which === 13) performHoldingSearch();
    });
</script>
