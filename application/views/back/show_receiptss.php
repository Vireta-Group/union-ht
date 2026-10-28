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
            <span class="text-indigo-600 font-semibold">রশিদ ও ভাউচার</span>
        </nav>
        <h2 class="text-xl sm:text-2xl font-extrabold text-neutral-900 tracking-tight">
            ট্যাক্স রশিদ অনুসন্ধান ও প্রিন্ট
        </h2>
        <p class="text-xs text-neutral-500">আদায়কৃত ভাউচার বা হোল্ডিং নম্বর দিয়ে পূর্বের পরিশোধিত ট্যাক্স রশিদ ও স্লিপ ডাউনলোড করুন</p>
    </div>
    
    <div class="flex items-center gap-2">
        <a href="taxCollection" class="px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:scale-98 text-white font-semibold text-xs flex items-center gap-1.5 shadow-2xs transition-all">
            <i class="ph-bold ph-hand-coins text-xs"></i> নতুন ট্যাক্স আদায়
        </a>
    </div>
</div>

<!-- Search Panel -->
<div class="bg-white rounded-2xl p-5 sm:p-6 border border-neutral-200/80 shadow-xs space-y-4">
    
    <!-- Mode Switcher Tabs -->
    <div class="flex items-center justify-center">
        <div class="inline-flex p-1 rounded-xl bg-neutral-100 border border-neutral-200">
            <button 
                type="button" 
                class="invoice_mode_btn px-4 py-1.5 rounded-xl text-xs font-bold transition-all bg-white text-indigo-700 shadow-2xs border-0 outline-none focus:outline-none cursor-pointer flex items-center gap-1.5">
                <i class="ph-bold ph-receipt"></i>
                <span>রশিদ নম্বর দিয়ে</span>
            </button>
            <button 
                type="button" 
                class="holding_mode_btn px-4 py-1.5 rounded-xl text-xs font-bold transition-all text-neutral-600 hover:text-neutral-900 border-0 outline-none focus:outline-none cursor-pointer flex items-center gap-1.5">
                <i class="ph-bold ph-house-line"></i>
                <span>হোল্ডিং নম্বর দিয়ে</span>
            </button>
        </div>
    </div>

    <!-- Search Input -->
    <div class="max-w-xl mx-auto space-y-2">
        <label for="receipt_search_input" class="search_input_label block text-xs font-bold text-neutral-700 text-center">
            রশিদ / ভাউচার নম্বর লিখুন
        </label>
        <div class="flex items-center gap-2">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-neutral-400">
                    <i class="ph-bold ph-magnifying-glass text-base"></i>
                </div>
                <input 
                    type="text" 
                    id="receipt_search_input"
                    class="type_invoice_no_ss block w-full pl-9 pr-3.5 py-2 rounded-xl bg-neutral-50 border border-neutral-200 text-neutral-900 placeholder-neutral-400 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all text-center sm:text-left" 
                    placeholder="রশিদ নম্বর লিখুন (উদাঃ 1790500080)..."
                    autocomplete="off"
                />
            </div>
            <button 
                type="button" 
                class="search_invoice_ss_no_btn changeSrchBTN px-3.5 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:scale-98 text-white font-semibold text-xs flex items-center gap-1.5 shadow-2xs transition-all cursor-pointer">
                <i class="ph-bold ph-magnifying-glass text-xs"></i>
                <span>অনুসন্ধান</span>
            </button>
        </div>
    </div>

</div>

<!-- Results Section -->
<div class="space-y-4">
    <div class="flex items-center justify-between px-1">
        <h3 class="text-sm sm:text-base font-bold text-neutral-800 invoice_count flex items-center gap-2">
            <i class="ph-bold ph-receipt text-indigo-600"></i>
            <span>অনুসন্ধানের ফলাফল (০)</span>
        </h3>
        <span class="text-xs text-neutral-400">প্রিন্ট অপশন অন্তর্ভুক্ত</span>
    </div>

    <!-- Cards Grid -->
    <div class="invoice_displays grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        <div class="col-span-full bg-white rounded-2xl p-8 border border-neutral-200/80 shadow-xs text-center space-y-3">
            <div class="w-14 h-14 rounded-2xl bg-neutral-50 text-neutral-400 mx-auto flex items-center justify-center text-2xl">
                <i class="ph-bold ph-receipt"></i>
            </div>
            <h4 class="text-sm font-bold text-neutral-700">রশিদ অনুসন্ধান করুন</h4>
            <p class="text-xs text-neutral-400 max-w-sm mx-auto">
                উপরে রশিদ নম্বর অথবা হোল্ডিং নম্বর দিয়ে খুঁজলে ট্যাক্স স্লিপ ও মানি রসিদ প্রিন্ট করার অপশন চলে আসবে।
            </p>
        </div>
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

    let searchMode = 'invoice'; // 'invoice' or 'holding'

    $('.invoice_mode_btn').on('click', function () {
        searchMode = 'invoice';
        $(this).addClass('bg-white text-indigo-700 shadow-2xs').removeClass('text-neutral-600 hover:text-neutral-900');
        $('.holding_mode_btn').removeClass('bg-white text-indigo-700 shadow-2xs').addClass('text-neutral-600 hover:text-neutral-900');
        $('.search_input_label').text('রশিদ / ভাউচার নম্বর লিখুন');
        $('.type_invoice_no_ss').attr('placeholder', 'রশিদ নম্বর লিখুন (উদাঃ 1790500080)...').val('');
        $('.changeSrchBTN').addClass('search_invoice_ss_no_btn').removeClass('searchInvoiceNo_btnbyHolding');
    });

    $('.holding_mode_btn').on('click', function () {
        searchMode = 'holding';
        $(this).addClass('bg-white text-indigo-700 shadow-2xs').removeClass('text-neutral-600 hover:text-neutral-900');
        $('.invoice_mode_btn').removeClass('bg-white text-indigo-700 shadow-2xs').addClass('text-neutral-600 hover:text-neutral-900');
        $('.search_input_label').text('হোল্ডিং নম্বর লিখুন');
        $('.type_invoice_no_ss').attr('placeholder', 'হোল্ডিং নম্বর লিখুন (উদাঃ 5045-09)...').val('');
        $('.changeSrchBTN').addClass('searchInvoiceNo_btnbyHolding').removeClass('search_invoice_ss_no_btn');
    });

    // Helper: Build a single modern voucher card
    function buildReceiptCard(inv, paidYearsList) {
        let yearsBadges = '';
        if (paidYearsList && paidYearsList.length > 0) {
            for (let y of paidYearsList) {
                yearsBadges += `
                    <div class="flex items-center justify-between text-xs py-1 border-b border-neutral-100 last:border-0">
                        <span class="text-neutral-600">${bn_num(y.paidable_year)} অর্থবছর</span>
                        <strong class="text-neutral-900">&#2547; ${bn_num(y.paid_amount)}/-</strong>
                    </div>
                `;
            }
        }

        return `
            <div class="bg-white rounded-2xl p-5 border border-neutral-200/80 shadow-xs hover:shadow-md transition-all flex flex-col justify-between space-y-4">
                <div class="space-y-3">
                    <div class="flex items-center justify-between border-b border-neutral-100 pb-3">
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700">
                            রশিদ: #${bn_num(inv.invoice_no_s)}
                        </span>
                        <span class="text-xs text-neutral-400 font-medium">
                            ${bn_num(inv.date_s_today_s || '')}
                        </span>
                    </div>

                    <div>
                        <h4 class="text-base font-bold text-neutral-900">${inv.family_person_name_head || 'নাগরিকের নাম'}</h4>
                        <div class="text-xs text-neutral-500 mt-0.5 flex items-center gap-1">
                            <i class="ph-bold ph-user text-neutral-400"></i>
                            <span>আদায়কারী: <strong>${inv.user_person_name || 'অফিস'}</strong></span>
                        </div>
                    </div>

                    <div class="p-3 rounded-2xl bg-neutral-50 space-y-1.5">
                        <div class="text-[11px] font-bold text-neutral-400 uppercase tracking-wider">পরিশোধের বিবরণ</div>
                        ${yearsBadges || '<div class="text-xs text-neutral-400">কোনো বছরের বিবরণ নেই</div>'}
                    </div>
                </div>

                <div class="pt-3 border-t border-neutral-100 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-neutral-500">মোট আদায়কৃত টাকা:</span>
                        <span class="text-lg font-black text-emerald-600">&#2547; ${bn_num(inv.amnt_taka)}/-</span>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <a href="recipt_print?uniqs=${inv.invoice_no_s}" target="_blank" class="px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs flex items-center justify-center gap-1.5 shadow-2xs transition-colors">
                            <i class="ph-bold ph-printer text-xs"></i>
                            <span>রশিদ প্রিন্ট</span>
                        </a>
                        <a href="slip_print?unqs=${inv.invoice_no_s}" target="_blank" class="px-3 py-1.5 rounded-xl bg-neutral-100 hover:bg-neutral-200 text-neutral-800 font-semibold text-xs flex items-center justify-center gap-1.5 transition-colors">
                            <i class="ph-bold ph-file-text text-xs"></i>
                            <span>ট্যাক্স স্লিপ</span>
                        </a>
                    </div>
                </div>
            </div>
        `;
    }

    // 1. Search by Invoice #
    $(document).on('click', '.search_invoice_ss_no_btn', function () {
        let type_invoice = $('.type_invoice_no_ss').val().trim();
        if (!type_invoice) {
            Swal.fire({
                title: 'রশিদ নম্বর প্রয়োজন',
                text: 'অনুগ্রহ করে অনুসন্ধানের জন্য রশিদ নম্বর লিখুন।',
                icon: 'warning',
                confirmButtonText: 'ঠিক আছে',
                confirmButtonColor: '#4f46e5'
            });
            return;
        }

        $('.invoice_displays').html(`
            <div class="col-span-full py-10 text-center text-neutral-400">
                <div class="inline-block animate-spin text-2xl text-indigo-600 mb-2">
                    <i class="ph-bold ph-spinner-gap"></i>
                </div>
                <div>রশিদ অনুসন্ধান করা হচ্ছে...</div>
            </div>
        `);

        $.ajax({
            type: "post",
            url: "getInvcInfo",
            data: { invc_: type_invoice },
            dataType: "json",
            success: function (res) {
                if (res && res != 0 && res.invoice) {
                    $('.invoice_count').html(`<i class="ph-bold ph-receipt text-indigo-600"></i> <span>অনুসন্ধানের ফলাফল (${bn_num(1)})</span>`);
                    const card = buildReceiptCard(res.invoice, res.paid_year);
                    $('.invoice_displays').html(card);
                } else {
                    $('.invoice_count').html(`<i class="ph-bold ph-receipt text-indigo-600"></i> <span>অনুসন্ধানের ফলাফল (০)</span>`);
                    $('.invoice_displays').html(`
                        <div class="col-span-full bg-white rounded-3xl p-10 border border-rose-200 shadow-xs text-center space-y-2">
                            <div class="w-14 h-14 rounded-2xl bg-rose-50 text-rose-600 mx-auto flex items-center justify-center text-2xl">
                                <i class="ph-bold ph-warning-circle"></i>
                            </div>
                            <h4 class="text-sm font-bold text-rose-700">রশিদ পাওয়া যায়নি!</h4>
                            <p class="text-xs text-neutral-500">"${type_invoice}" নম্বরের কোনো রশিদের তথ্য ডেটাবেজে নেই।</p>
                        </div>
                    `);
                }
            }
        });
    });

    // 2. Search by Holding #
    $(document).on('click', '.searchInvoiceNo_btnbyHolding', function () {
        let type_holding = $('.type_invoice_no_ss').val().trim();
        if (!type_holding) {
            Swal.fire({
                title: 'হোল্ডিং নম্বর প্রয়োজন',
                text: 'অনুগ্রহ করে অনুসন্ধানের জন্য হোল্ডিং নম্বর লিখুন।',
                icon: 'warning',
                confirmButtonText: 'ঠিক আছে',
                confirmButtonColor: '#4f46e5'
            });
            return;
        }

        $('.invoice_displays').html(`
            <div class="col-span-full py-10 text-center text-neutral-400">
                <div class="inline-block animate-spin text-2xl text-indigo-600 mb-2">
                    <i class="ph-bold ph-spinner-gap"></i>
                </div>
                <div>হোল্ডিংয়ের সকল রশিদ খোঁজা হচ্ছে...</div>
            </div>
        `);

        $.ajax({
            type: "post",
            url: "getInvcInfoHolding",
            data: { invc_: type_holding },
            dataType: "json",
            success: function (res) {
                if (res && res != 0 && res.invoice && res.invoice.length > 0) {
                    $('.invoice_count').html(`<i class="ph-bold ph-receipt text-indigo-600"></i> <span>অনুসন্ধানের ফলাফল (${bn_num(res.invoice.length)})</span>`);
                    let allCards = '';
                    for (let inv of res.invoice) {
                        let matchingYears = [];
                        if (res.paid_year && res.paid_year.length > 0) {
                            matchingYears = res.paid_year.filter(y => y.voucher == inv.invoice_no_s);
                        }
                        allCards += buildReceiptCard(inv, matchingYears);
                    }
                    $('.invoice_displays').html(allCards);
                } else {
                    $('.invoice_count').html(`<i class="ph-bold ph-receipt text-indigo-600"></i> <span>অনুসন্ধানের ফলাফল (০)</span>`);
                    $('.invoice_displays').html(`
                        <div class="col-span-full bg-white rounded-3xl p-10 border border-rose-200 shadow-xs text-center space-y-2">
                            <div class="w-14 h-14 rounded-2xl bg-rose-50 text-rose-600 mx-auto flex items-center justify-center text-2xl">
                                <i class="ph-bold ph-warning-circle"></i>
                            </div>
                            <h4 class="text-sm font-bold text-rose-700">কোনো রশিদ পাওয়া যায়নি!</h4>
                            <p class="text-xs text-neutral-500">হোল্ডিং "${type_holding}" এর অধীনে কোনো রশিদের তথ্য নেই।</p>
                        </div>
                    `);
                }
            }
        });
    });

    // Enter Key Search Trigger
    $(document).on('keypress', '.type_invoice_no_ss', function (e) {
        if (e.which === 13) {
            $('.changeSrchBTN').click();
        }
    });
</script>