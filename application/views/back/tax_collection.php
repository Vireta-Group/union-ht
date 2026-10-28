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
            <span class="text-indigo-600 font-semibold">ট্যাক্স আদায়</span>
        </nav>
        <h2 class="text-xl sm:text-2xl font-extrabold text-neutral-900 tracking-tight">
            হোল্ডিং ট্যাক্স আদায় ও ভাউচার
        </h2>
        <p class="text-xs text-neutral-500">নাগরিকের হোল্ডিং নম্বর দিয়ে বকেয়া কর অনুসন্ধান ও তাৎক্ষণিক আদায় করুন</p>
    </div>
    
    <div class="flex items-center gap-2">
        <a href="receipt_show" class="px-3 py-1.5 rounded-xl bg-white border border-neutral-200 hover:bg-neutral-50 text-neutral-700 font-semibold text-xs flex items-center gap-1.5 shadow-2xs transition-all">
            <i class="ph-bold ph-receipt text-xs text-emerald-600"></i> পূর্বের রশিদসমূহ
        </a>
        <a href="searchHoldingss" class="px-3 py-1.5 rounded-xl bg-white border border-neutral-200 hover:bg-neutral-50 text-neutral-700 font-semibold text-xs flex items-center gap-1.5 shadow-2xs transition-all">
            <i class="ph-bold ph-magnifying-glass text-xs text-sky-600"></i> হোল্ডিং খুঁজুন
        </a>
    </div>
</div>

<!-- Search Bar Box -->
<div class="bg-white rounded-2xl p-5 sm:p-6 border border-neutral-200/80 shadow-xs">
    <div class="max-w-2xl mx-auto space-y-3">
        <label for="holding_search_input" class="block text-xs font-bold text-neutral-700">
            হোল্ডিং নম্বর দিয়ে সার্চ করুন
        </label>
        <div class="flex items-center gap-2">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-neutral-400">
                    <i class="ph-bold ph-magnifying-glass text-base"></i>
                </div>
                <input 
                    type="text" 
                    id="holding_search_input"
                    class="typing_num_ber block w-full pl-9 pr-3.5 py-2 rounded-xl bg-neutral-50 border border-neutral-200 text-neutral-900 placeholder-neutral-400 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all" 
                    placeholder="হোল্ডিং নম্বর লিখুন (উদাঃ 5045-09 বা 5045)..."
                    autocomplete="off"
                />
            </div>
            <button 
                type="button" 
                class="search_btn_ px-3.5 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:scale-98 text-white font-semibold text-xs flex items-center gap-1.5 shadow-2xs transition-all cursor-pointer">
                <i class="ph-bold ph-magnifying-glass text-xs"></i>
                <span>অনুসন্ধান</span>
            </button>
        </div>
        <p class="text-[11px] text-neutral-400">
            টিপস: যেকোনো হোল্ডিং নম্বর লিখে কিবোর্ডের <kbd class="px-1.5 py-0.5 rounded bg-neutral-100 border text-neutral-600 font-mono text-[10px]">Enter</kbd> চাপুন
        </p>
    </div>
</div>

<!-- Tax Collection Display Container -->
<div class="tax_collect_infos">
    <!-- Initial Prompt State -->
    <div class="bg-white rounded-2xl p-8 border border-neutral-200/80 shadow-xs text-center space-y-3">
        <div class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-600 mx-auto flex items-center justify-center text-2xl">
            <i class="ph-bold ph-hand-coins"></i>
        </div>
        <h3 class="text-sm font-bold text-neutral-900">হোল্ডিং ট্যাক্স আদায়ের জন্য অনুসন্ধান করুন</h3>
        <p class="text-xs text-neutral-500 max-w-md mx-auto">
            উপরে হোল্ডিং নম্বর দিয়ে অনুসন্ধান করলে করদাতার নাম, বাৎসরিক কর হার এবং অপরিশোধিত বছরের তালিকা চলে আসবে।
        </p>
    </div>
</div>

<script>
    function bn_convert(number) {
        const en = ['0','1','2','3','4','5','6','7','8','9'];
        const bn = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
        return String(number).replace(/[0-9]/g, function(w){
            return bn[+w];
        });
    }

    function get_holdings_person(hold_num) {
        if (!hold_num.trim()) {
            Swal.fire({
                title: 'হোল্ডিং নম্বর প্রয়োজন',
                text: 'দয়া করে অনুসন্ধানের জন্য সঠিক হোল্ডিং নম্বর লিখুন।',
                icon: 'warning',
                confirmButtonText: 'ঠিক আছে',
                confirmButtonColor: '#4f46e5'
            });
            return;
        }

        $('.tax_collect_infos').html(`
            <div class="bg-white rounded-3xl p-12 border border-neutral-200/80 shadow-xs text-center space-y-3">
                <div class="inline-block animate-spin text-3xl text-indigo-600">
                    <i class="ph-bold ph-spinner-gap"></i>
                </div>
                <p class="text-xs text-neutral-500">হোল্ডিং ও কর সংক্রান্ত তথ্য খোঁজা হচ্ছে...</p>
            </div>
        `);
        
        $.ajax({
            type: "post",
            url: "searching_tax",
            data: {
                input_val: hold_num
            },
            dataType: "json",
            success: function (res) {
                if (!res || res == 0 || !res.get_search_data) {
                    $('.tax_collect_infos').html(`
                        <div class="bg-white rounded-3xl p-10 border border-rose-200 shadow-xs text-center space-y-3">
                            <div class="w-16 h-16 rounded-2xl bg-rose-50 text-rose-600 mx-auto flex items-center justify-center text-3xl">
                                <i class="ph-bold ph-warning-circle"></i>
                            </div>
                            <h3 class="text-base font-bold text-rose-700">হোল্ডিং নম্বর পাওয়া যায়নি!</h3>
                            <p class="text-xs text-neutral-500">
                                "${hold_num}" নম্বরের কোনো খানা বা হোল্ডিং রেকর্ড পাওয়া যায়নি। নম্বরটি যাচাই করে পুনরায় চেষ্টা করুন।
                            </p>
                        </div>
                    `);
                    return;
                }

                const data = res.get_search_data;
                const yearly_rate = parseInt(data.holding_rates_yearly) || 0;
                window.discountState = { on: false, amount: 0, reason: '' };

                // Render Year Selection Rows
                let years_html = '';
                if (res.c_year_s && res.c_year_s.length > 0) {
                    for (let o = 0; o < res.c_year_s.length; o++) {
                        const y = res.c_year_s[o];
                        years_html += `
                            <div class="year_collec_single group p-3 rounded-2xl border border-neutral-200 bg-neutral-50/60 hover:bg-indigo-50/50 hover:border-indigo-300 transition-all flex items-center justify-between cursor-pointer select-none">
                                <div class="flex items-center gap-3">
                                    <div class="assign_img_check w-6 h-6 rounded-lg border-2 border-neutral-300 group-hover:border-indigo-500 flex items-center justify-center transition-colors">
                                    </div>
                                    <div>
                                        <span class="text-xs font-bold text-neutral-800">${bn_convert(y)} অর্থবছর</span>
                                        <input type="hidden" class="year_collect" value="${y}">
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="text-xs font-black text-indigo-700">&#2547; ${bn_convert(yearly_rate)}</span>
                                    <input type="hidden" class="collct_amnt" value="${yearly_rate}">
                                </div>
                            </div>
                        `;
                    }
                } else {
                    years_html = `
                        <div class="p-4 rounded-2xl bg-emerald-50 text-emerald-800 text-xs font-semibold flex items-center gap-2">
                            <i class="ph-bold ph-check-circle text-lg"></i>
                            <span>এই হোল্ডিংয়ের সকল কর পরিশোধিত আছে। কোনো বকেয়া নেই।</span>
                        </div>
                    `;
                }

                // Render Paid History Table Rows
                let paid_history_html = '';
                if (res.paid_amount && res.paid_amount.length > 0) {
                    for (let l = 0; l < res.paid_amount.length; l++) {
                        const p = res.paid_amount[l];
                        paid_history_html += `
                            <tr class="hover:bg-neutral-50/70 transition-colors">
                                <td class="py-2.5 font-bold text-indigo-600">#${bn_convert(p.voucher)}</td>
                                <td class="py-2.5 text-neutral-500">${bn_convert(p.entry_date)}</td>
                                <td class="py-2.5 text-neutral-800 font-medium">${bn_convert(p.paidable_year)}</td>
                                <td class="py-2.5 font-bold text-emerald-600">&#2547; ${bn_convert(p.paid_amount)}</td>
                                <td class="py-2.5 text-right">
                                    <div class="inline-flex items-center gap-1.5 justify-end">
                                        <button type="button" class="printbtns p-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-600 hover:text-white text-indigo-700 transition-colors" invoice_no_attr="${p.voucher}" title="মানি রসিদ প্রিন্ট করুন">
                                            <i class="ph-bold ph-printer text-sm"></i>
                                        </button>
                                        <button type="button" class="print_pos_btn p-1.5 rounded-xl bg-neutral-100 hover:bg-neutral-200 text-neutral-600 transition-colors" invoice_no_attr="${p.voucher}" title="পিওএস / থার্মাল স্লিপ প্রিন্ট করুন">
                                            <i class="ph-bold ph-receipt text-sm"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `;
                    }
                } else {
                    paid_history_html = `
                        <tr>
                            <td colspan="5" class="py-6 text-center text-neutral-400 text-xs">
                                পূর্বে কোনো পরিশোধের তথ্য পাওয়া যায়নি।
                            </td>
                        </tr>
                    `;
                }

                // Render Complete Tax Collection Workspace
                $('.tax_collect_infos').html(`
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                        <!-- Left: Citizen Info & Tax Collection Form (7 cols) -->
                        <div class="lg:col-span-7 space-y-5">
                            
                            <!-- Citizen Summary Card -->
                            <div class="bg-white rounded-2xl p-5 sm:p-6 border border-neutral-200/80 shadow-xs">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-neutral-100">
                                    <div class="flex items-center gap-3.5">
                                        <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl font-bold shrink-0">
                                            <i class="ph-bold ph-user"></i>
                                        </div>
                                        <div>
                                            <h3 class="text-base font-bold text-neutral-900">${data.family_person_name_head || 'নাম পাওয়া যায়নি'}</h3>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-100 text-indigo-800">
                                                    হোল্ডিং: ${bn_convert(data.holding_number)}
                                                </span>
                                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-neutral-100 text-neutral-700">
                                                    ওয়ার্ড: ${bn_convert(data.word_number || '-')}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right sm:text-right">
                                        <span class="text-[11px] text-neutral-400 block">বাৎসরিক করের হার</span>
                                        <span class="text-base font-black text-indigo-700">&#2547; ${bn_convert(yearly_rate)}/-</span>
                                    </div>
                                </div>

                                <!-- Unpaid Years Checklist -->
                                <div class="mt-4 space-y-3">
                                    <div class="flex items-center justify-between">
                                        <label class="text-xs font-bold text-neutral-700">পরিশোধযোগ্য অর্থবছর নির্বাচন করুন</label>
                                        <span class="text-[11px] text-neutral-400">ক্লিক করে নির্বাচন করুন</span>
                                    </div>
                                    <div class="space-y-2">
                                        ${years_html}
                                    </div>
                                </div>

                                <!-- Discount Section (ছাড় / মওকুফ) -->
                                <div class="mt-5 pt-4 border-t border-neutral-100" data-discount-panel>
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-1.5">
                                            <i class="ph-bold ph-tag text-xs text-indigo-600"></i>
                                            <span class="text-xs font-bold text-neutral-700">বিশেষ ছাড় / মওকুফ</span>
                                            <span class="text-[11px] text-neutral-400 font-normal">(প্রযোজ্য ক্ষেত্রে)</span>
                                        </div>
                                        <button type="button" data-discount-toggle
                                                class="px-2.5 py-1 rounded-lg border border-dashed border-indigo-300 hover:border-indigo-500 hover:bg-indigo-50 text-indigo-600 font-bold text-xs flex items-center gap-1.5 transition-all cursor-pointer">
                                            <i class="ph-bold ph-tag text-xs"></i>
                                            <span data-discount-toggle-text>ডিসকাউন্ট দিন</span>
                                        </button>
                                    </div>

                                    <div class="hidden mt-3 p-4 sm:p-5 rounded-2xl bg-neutral-50/90 border border-neutral-200/80 space-y-4" data-discount-fields>
                                        <div style="display: flex; flex-wrap: wrap; gap: 1rem; width: 100%;">
                                            <div style="flex: 0 0 220px; min-width: 180px; max-width: 100%;">
                                                <label for="discount_amount_input" style="display: block; font-size: 0.8125rem; font-weight: 700; color: #374151; margin-bottom: 0.375rem; white-space: nowrap;">
                                                    ছাড়ের পরিমাণ (৳)
                                                </label>
                                                <div style="position: relative; width: 100%;">
                                                    <span style="position: absolute; top: 0; bottom: 0; left: 0; display: flex; align-items: center; padding-left: 0.875rem; color: #64748b; font-weight: 800; font-size: 0.875rem; pointer-events: none;">৳</span>
                                                    <input type="text" inputmode="numeric" id="discount_amount_input"
                                                           data-discount-amount
                                                           placeholder="০"
                                                           style="width: 100% !important; min-height: 42px; padding: 0.5rem 0.875rem 0.5rem 2.25rem !important; border-radius: 0.75rem !important; border: 1.5px solid #cbd5e1 !important; background: #ffffff !important; font-size: 0.9375rem !important; font-weight: 800 !important; color: #0f172a !important; box-sizing: border-box !important;">
                                                </div>
                                            </div>
                                            <div style="flex: 1 1 260px; min-width: 240px;">
                                                <label for="discount_reason_input" style="display: block; font-size: 0.8125rem; font-weight: 700; color: #374151; margin-bottom: 0.375rem; white-space: nowrap;">
                                                    ছাড়ের কারণ <span style="color: #e11d48;">*</span>
                                                </label>
                                                <input type="text" id="discount_reason_input" data-discount-reason maxlength="200"
                                                       placeholder="যেমন: কোটেড পরিষদ সদস্য / অসচ্ছল পরিবার"
                                                       style="width: 100% !important; min-height: 42px; padding: 0.5rem 0.875rem !important; border-radius: 0.75rem !important; border: 1.5px solid #cbd5e1 !important; background: #ffffff !important; font-size: 0.875rem !important; color: #0f172a !important; box-sizing: border-box !important;">
                                            </div>
                                        </div>
                                        <p data-discount-error class="hidden text-xs font-semibold text-rose-600" style="margin-top: 0.25rem;"></p>

                                        <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.75rem; padding: 0.75rem 1rem; border-radius: 0.75rem; background: #ffffff; border: 1px dashed #cbd5e1; font-size: 0.8125rem;" data-discount-summary>
                                            <div style="display: flex; align-items: center; gap: 0.5rem; color: #475569;">
                                                <span>মোট বিল: <strong style="color: #0f172a; font-weight: 800;">৳ <span data-disc-gross>০</span></strong></span>
                                                <span style="color: #cbd5e1;">−</span>
                                                <span>ছাড়: <strong style="color: #e11d48; font-weight: 800;">৳ <span data-disc-off>০</span></strong></span>
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 0.375rem; color: #475569;">
                                                <span>প্রকৃত আদায়যোগ্য:</span>
                                                <span style="font-size: 1rem; font-weight: 900; color: #4338ca;">৳ <span data-disc-net>০</span>/-</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Total Calculation & Collect Button -->
                                <div class="mt-5 pt-4 border-t border-neutral-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                    <div>
                                        <span class="text-xs text-neutral-400 block font-medium">সর্বমোট আদায়ের পরিমাণ</span>
                                        <div class="flex items-center gap-1.5 text-2xl font-black text-neutral-900">
                                            <span class="text-indigo-600">&#2547;</span>
                                            <input type="text" readonly class="total_amount_typing bg-transparent border-0 p-0 text-2xl font-black text-neutral-900 w-36 focus:outline-none" value="0">
                                        </div>
                                    </div>

                                    <div class="collect_action_btn">
                                        <button 
                                            type="button" 
                                            person_id_attr="${data.data_id}" 
                                            class="tax_collect_btn w-full sm:w-auto px-4 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:scale-98 text-white font-semibold text-xs flex items-center justify-center gap-1.5 shadow-2xs transition-all cursor-pointer">
                                            <i class="ph-bold ph-hand-coins text-sm"></i>
                                            <span>ট্যাক্স আদায় সম্পন্ন করুন</span>
                                        </button>
                                    </div>
                                </div>

                            </div>

                        </div>

                        <!-- Right: Previous Payment History (5 cols) -->
                        <div class="lg:col-span-5 bg-white rounded-2xl p-5 sm:p-6 border border-neutral-200/80 shadow-xs h-fit space-y-4">
                            <div class="flex items-center justify-between border-b border-neutral-100 pb-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                                        <i class="ph-bold ph-receipt"></i>
                                    </div>
                                    <h4 class="text-sm font-bold text-neutral-900">পূর্বের পরিশোধের ইতিহাস</h4>
                                </div>
                                <span class="text-[11px] text-neutral-400">রশিদ ভাউচার</span>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-xs">
                                    <thead>
                                        <tr class="text-neutral-400 border-b border-neutral-100 font-bold uppercase tracking-wider">
                                            <th class="py-2">ভাউচার</th>
                                            <th class="py-2">তারিখ</th>
                                            <th class="py-2">অর্থবছর</th>
                                            <th class="py-2">পরিমাণ</th>
                                            <th class="py-2 text-right">প্রিন্ট</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-neutral-100">
                                        ${paid_history_html}
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                `);
            },
            error: function () {
                $('.tax_collect_infos').html(`
                    <div class="bg-white rounded-3xl p-10 border border-rose-200 shadow-xs text-center space-y-3">
                        <div class="w-16 h-16 rounded-2xl bg-rose-50 text-rose-600 mx-auto flex items-center justify-center text-3xl">
                            <i class="ph-bold ph-x-circle"></i>
                        </div>
                        <h3 class="text-base font-bold text-rose-700">সার্ভার সংযোগে ত্রুটি ঘটেছে!</h3>
                        <p class="text-xs text-neutral-500">অনুগ্রহ করে ইন্টারনেট কানেকশন চেক করে পুনরায় চেষ্টা করুন।</p>
                    </div>
                `);
            }
        });
    }

    // Toggle Year Selection on Click
    $(document).on('click', '.year_collec_single', function () {
        const check_box = $(this).find('.assign_img_check');
        const collct_amnt = $(this).find('.collct_amnt');
        const year_collect = $(this).find('.year_collect');

        if (collct_amnt.hasClass('amnt_countss')) {
            collct_amnt.removeClass('amnt_countss');
            check_box.removeClass('bg-indigo-600 border-indigo-600 text-white').html('');
            year_collect.removeAttr('name');
            collct_amnt.removeAttr('name');
            $(this).removeClass('border-indigo-400 bg-indigo-50/40');
        } else {
            collct_amnt.addClass('amnt_countss');
            check_box.addClass('bg-indigo-600 border-indigo-600 text-white').html('<i class="ph-bold ph-check text-xs"></i>');
            year_collect.attr('name', 'entry_sals[]');
            collct_amnt.attr('name', 'entry_stk[]');
            $(this).addClass('border-indigo-400 bg-indigo-50/40');
        }
        total_c_amnt();
    });

    // Search Trigger (Click & Enter)
    $(document).on('click', '.search_btn_', function () {
        let typing_num_ber = $('.typing_num_ber').val();
        get_holdings_person(typing_num_ber);
    });

    $(document).on('keydown keypress', '.typing_num_ber', function (e) {
        if (e.key === 'Enter' || e.which === 13 || e.keyCode === 13) {
            e.preventDefault();
            let typing_num_ber = $(this).val();
            get_holdings_person(typing_num_ber);
        }
    });

    // Calculate Total
    function total_c_amnt() {
        let sum_t = 0;
        $('.amnt_countss').each(function () {
            let bill_r = $(this).val();
            if (bill_r && bill_r != 0) {
                sum_t += parseInt(bill_r);
            }
        });
        $('.total_amount_typing').val(sum_t);
        window.discountRecalc(sum_t);
    }

    /* ---------------------------------------------------------------
       ছাড় (discount) — per-invoice.

       Recalculated from the GROSS whenever the bill changes, because the
       total can be edited by picking more or fewer years AFTER the
       discount box is filled. A cached net would then be wrong.

       Listeners are delegated from document and the state is reset on
       modal show, because the collect modal is rebuilt on every open —
       the same reason .tax_collect_btn is delegated rather than bound.
    */
    window.discountState = { on: false, amount: 0, reason: '' };

    window.discountRecalc = function (gross) {
        var g = parseInt(gross, 10);
        if (isNaN(g) || g < 0) g = 0;
        var d = window.discountState.amount;
        if (isNaN(d) || d < 0) d = 0;
        $('[data-disc-gross]').text(bn_convert(g));
        $('[data-disc-off]').text(bn_convert(d));
        $('[data-disc-net]').text(bn_convert(Math.max(0, g - d)));
        if (window.discountState.on && d > 0 && g > 0 && d >= g) {
            discountError('ছাড়ের পরিমাণ মোট টাকার সমান বা বেশি হতে পারবে না।');
        } else {
            discountError('');
        }
    };

    function discountError(msg) {
        var el = $('[data-discount-error]');
        if (!msg) { el.addClass('hidden').text(''); return; }
        el.removeClass('hidden').text(msg);
    }

    $(document).on('click', '[data-discount-toggle]', function () {
        var on = !window.discountState.on;
        window.discountState.on = on;
        $('[data-discount-fields]').toggleClass('hidden', !on);
        if (on) {
            $(this).removeClass('border-indigo-300 text-indigo-600 hover:bg-indigo-50')
                   .addClass('border-rose-300 text-rose-600 bg-rose-50/60 hover:bg-rose-100');
            $(this).find('[data-discount-toggle-text]').text('ডিসকাউন্ট বাতিল');
            $(this).find('i').removeClass('ph-tag').addClass('ph-x');
            setTimeout(function() { $('#discount_amount_input').focus(); }, 100);
        } else {
            $(this).removeClass('border-rose-300 text-rose-600 bg-rose-50/60 hover:bg-rose-100')
                   .addClass('border-indigo-300 text-indigo-600 hover:bg-indigo-50');
            $(this).find('[data-discount-toggle-text]').text('ডিসকাউন্ট দিন');
            $(this).find('i').removeClass('ph-x').addClass('ph-tag');
            window.discountState.amount = 0;
            window.discountState.reason = '';
            $('[data-discount-amount]').val('');
            $('[data-discount-reason]').val('');
            discountError('');
        }
        window.discountRecalc($('.total_amount_typing').val());
    });

    $(document).on('input', '[data-discount-amount]', function () {
        // Digits only, max 7. Stripped as it is typed, so the box can never
        // hold something the server will reject for a reason the operator
        // cannot see.
        var v = $(this).val().replace(/[^0-9]/g, '').slice(0, 7);
        $(this).val(v);
        window.discountState.amount = v === '' ? 0 : parseInt(v, 10);
        window.discountRecalc($('.total_amount_typing').val());
    });

    $(document).on('input', '[data-discount-reason]', function () {
        window.discountState.reason = $(this).val();
        if ($(this).val().trim()) {
            discountError('');
        }
    });

    // New modal, new person: no discount carried over from the last one.
    $(document).on('shown.bs.modal', function () {
        window.discountState = { on: false, amount: 0, reason: '' };
        $('[data-discount-fields]').addClass('hidden');
        $('[data-discount-amount]').val('');
        $('[data-discount-reason]').val('');
        discountError('');
    });

    /**
     * The discount for a bill about to be collected.
     * Returns {amount, reason} or {error}. The server re-checks all of it:
     * a client check is a courtesy to the operator, not a gate.
     */
    function discountFor(gross) {
        if (!window.discountState.on) return { amount: 0, reason: '' };
        var d = window.discountState.amount;
        var r = (window.discountState.reason || '').trim();
        var g = parseInt(gross, 10);
        if (isNaN(g)) g = 0;
        if (d <= 0) return { amount: 0, reason: '' };
        if (d >= g) return { error: 'ছাড়ের পরিমাণ মোট টাকার সমান বা বেশি হতে পারবে না।' };
        if (!r)    return { error: 'ছাড় দিলে কারণ লিখতে হবে।' };
        if (r.length > 200) return { error: 'ছাড়ের কারণ ২০০ অক্ষরের মধ্যে লিখুন।' };
        return { amount: d, reason: r };
    }

    // Collect Tax Submit
    $(document).on('click', '.tax_collect_btn', function () {
        let types_amnt = $('.total_amount_typing').val();
        if (!types_amnt || types_amnt <= 0) {
            Swal.fire({
                title: 'কোনো অর্থবছর নির্বাচন করা হয়নি!',
                text: 'দয়া করে পরিশোধ করার জন্য কমপক্ষে একটি অর্থবছর নির্বাচন করুন।',
                icon: 'warning',
                confirmButtonText: 'ঠিক আছে',
                confirmButtonColor: '#4f46e5'
            });
            return;
        }

        let person_id = $(this).attr('person_id_attr');
        let entry_sals = $('input[name="entry_sals[]"]').map(function(){ return this.value; }).get();
        let entry_stk = $('input[name="entry_stk[]"]').map(function(){ return this.value; }).get();

        // The dialog quotes the NET figure and shows the discount
        // separately, so the operator confirms the number that will be
        // recorded. With a discount of 0 this reads exactly as the page
        // did before — most collections have no discount and must not
        // be shown a new line of arithmetic.
        var _disc  = discountFor(types_amnt);
        if (_disc.error) {
            Swal.fire({
                title: 'ছাড়ের তথ্য সমস্যা',
                text: _disc.error,
                icon: 'warning',
                confirmButtonText: 'ঠিক আছে',
                confirmButtonColor: '#b45309'
            });
            return;
        }

        var _gross = parseInt(types_amnt, 10); if (isNaN(_gross)) _gross = 0;
        var _off   = _disc.amount || 0;
        var _net   = Math.max(0, _gross - _off);
        var _reason = $('[data-discount-reason]').val();
        var _discLine = _off > 0
            ? '<div style="margin-top:.6rem;padding:.5rem .75rem;border-radius:.5rem;background:#fef2f2;font-size:.8125rem">'
              + 'মোট ৳' + bn_convert(_gross)
              + ' − ছাড় ৳' + bn_convert(_off)
              + '<div style="margin-top:.15rem">কারণ: <b>' + _reason + '</b></div></div>'
            : '';

        Swal.fire({
            title: 'ট্যাক্স আদায় নিশ্চিতকরণ',
            html: '<div>আপনি কি ৳ <b>' + bn_convert(_net) + '</b> টাকা কর আদায় করতে চান?</div>' + _discLine,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'হ্যাঁ, আদায় করুন',
            cancelButtonText: 'বাতিল',
            confirmButtonColor: '#4f46e5',
            cancelButtonColor: '#64748b'
        }).then((result) => {
            if (result.isConfirmed) {
                // Read the discount AFTER the operator confirms, not before:
                // the button handler has not run yet at this point, and the
                // net figure quoted in the dialog has to be the one posted.
                var disc = discountFor(types_amnt);
                if (disc.error) {
                    Swal.fire({
                        title: 'ছাড়ের তথ্য সমস্যা',
                        text: disc.error,
                        icon: 'warning',
                        confirmButtonColor: '#b45309'
                    });
                    return;
                }
                $.ajax({
                    type: "post",
                    url: "taxs_paid",
                    data: {
                        person: person_id,
                        // The GROSS, not the net. The server subtracts the
                        // discount itself (see insert_tax_paids) so the
                        // arithmetic happens in exactly one place. Sending a
                        // pre-subtracted figure from here as well would
                        // apply the discount twice.
                        amounts: types_amnt,
                        discount_amount: disc.amount || 0,
                        discount_reason: disc.reason || '',
                        'entry_sals[]': entry_sals,
                        'entry_stk[]': entry_stk,
                    },
                    success: function (res) {
                        $('.collect_action_btn').html('');
                        var _collected = _off > 0 ? _net : parseInt(types_amnt, 10);
                        var _discNote = _off > 0 ? ` <span class="text-rose-600 font-semibold">(ছাড়: ৳ ${bn_convert(_off)}/-)</span>` : '';
                        Swal.fire({
                            title: 'ট্যাক্স আদায় সফল হয়েছে!',
                            icon: 'success',
                            html: `
                                <div class="space-y-3 py-2">
                                    <p class="text-sm text-neutral-600 font-medium">মোট <strong>৳ ${bn_convert(_collected)}/-</strong> টাকা আদায় সম্পন্ন হয়েছে।${_discNote}</p>
                                    <div class="flex flex-wrap items-center justify-center gap-2 pt-2">
                                        <button type="button" class="print_full_receipt_btn px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs inline-flex items-center gap-1.5 shadow-sm transition-colors cursor-pointer" invoice_no_attr="${res}">
                                            <i class="ph-bold ph-printer text-sm"></i>
                                            <span>মানি রসিদ প্রিন্ট (ফুল পেজ)</span>
                                        </button>
                                        <button type="button" class="print_pos_slip_btn px-3.5 py-2 rounded-xl bg-neutral-100 hover:bg-neutral-200 text-neutral-800 font-bold text-xs inline-flex items-center gap-1.5 transition-colors cursor-pointer" invoice_no_attr="${res}">
                                            <i class="ph-bold ph-receipt text-sm"></i>
                                            <span>পিওএস / থার্মাল স্লিপ</span>
                                        </button>
                                    </div>
                                </div>
                            `,
                            showConfirmButton: false,
                        });
                    },
                    error: function() {
                        Swal.fire({
                            title: 'ত্রুটি!',
                            text: 'ট্যাক্স আদায় সম্পন্ন করা সম্ভব হয়নি। আবার চেষ্টা করুন।',
                            icon: 'error',
                            confirmButtonText: 'ঠিক আছে'
                        });
                    }
                });
            }
        });
    });

    // 1. Full Money Receipt Triggers (Default)
    $(document).on('click', '.print_full_receipt_btn, .printbtns', function () {
        let unqss = $(this).attr('invoice_no_attr');
        window.open('recipt_print?uniqs=' + unqss, '_blank'); 
        if ($(this).hasClass('print_full_receipt_btn')) {
            setTimeout(function() {
                window.location.href = "taxCollection";
            }, 1500);
        }
    });

    // 2. POS Thermal Slip Triggers
    $(document).on('click', '.print_pos_slip_btn, .print_pos_btn', function () {
        let unqss = $(this).attr('invoice_no_attr');
        window.open('slip_print?unqs=' + unqss, '_blank', 'width=450,height=650,left=300,top=100'); 
        if ($(this).hasClass('print_pos_slip_btn')) {
            setTimeout(function() {
                window.location.href = "taxCollection";
            }, 1500);
        }
    });

    // Auto-search if holding parameter is in URL
    $(document).ready(function() {
        const urlParams = new URLSearchParams(window.location.search);
        const qHold = urlParams.get('holding');
        if (qHold) {
            $('#holding_search_input').val(qHold);
            get_holdings_person(qHold);
        }
    });
</script>