<link rel="stylesheet" href="inc/assets/vendor/sweetalert2/dist/sweetalert2.min.css">

<!-- Tax collection for the app/API surface. Rendered through template->front(),
     which no longer loads Bootstrap or the Material Design Kit, so this view is
     plain Tailwind on the portal.css token layer.

     Every jQuery hook class below (.tax_collect_infos, .search_btn_,
     .typing_num_ber, .year_collec_single, .collct_amnt, .amnt_countss,
     .assign_img_check, .year_collect, .total_amount_typing, .tax_collect_btn,
     .collect_action_btn, .print_btn) is load-bearing — the script at the bottom
     selects on them. Renaming one silently breaks the page. The AJAX endpoints
     (searching_tax, taxs_paid, slip) and their payloads are unchanged. -->

<div class="mx-auto flex w-full max-w-7xl flex-col gap-6 px-4 py-6 lg:px-8">

    <!--begin::Toolbar-->
    <div class="flex flex-wrap items-end justify-between gap-3 border-b border-neutral-200 pb-4">
        <div class="flex flex-col gap-1">
            <nav aria-label="breadcrumb">
                <ol class="flex items-center gap-2 text-xs font-medium text-neutral-500">
                    <li><a href="<?= base_url() ?>" class="hover:text-primary">হোম</a></li>
                    <li aria-hidden="true">/</li>
                    <li class="font-semibold text-neutral-700">ট্যাক্স কালেকশন</li>
                    <li aria-hidden="true">/</li>
                    <li class="font-semibold text-neutral-700">সার্চ</li>
                </ol>
            </nav>
            <h1 class="m-0 text-2xl font-bold text-neutral-900">ট্যাক্স কালেকশন</h1>
        </div>
        <a class="ht-btn ht-btn-secondary" href="api/login/logout">লগআউট</a>
    </div>
    <!--end::Toolbar-->

    <div class="flex flex-col gap-6 lg:flex-row">

        <!--begin::Aside — search-->
        <div class="w-full lg:w-72">
            <form action="#" class="ht-card p-5">
                <div class="flex flex-col gap-2">
                    <label class="ht-label" for="holding_search">হোল্ডিং নম্বর</label>
                    <input id="holding_search" type="text" class="ht-field typing_num_ber"
                           name="search" value="" placeholder="Search by Holding Number" />
                </div>
                <div class="mt-4 flex items-center justify-end gap-3">
                    <a class="ht-btn ht-btn-ghost">রিসেট</a>
                    <a class="ht-btn ht-btn-primary search_btn_">সার্চ</a>
                </div>
            </form>
        </div>
        <!--end::Aside-->

        <!--begin::Content — result panel (filled by AJAX)-->
        <div class="flex-1">
            <div class="ht-card flex min-h-64 items-center justify-center p-8">
                <div class="nk-block nk-block-lg tax_collect_infos w-full">
                    <div class="border-2 border-dashed border-neutral-300 p-10 text-center text-lg text-neutral-500">
                        হোল্ডিং নম্বর দিয়ে সার্চ করুন
                    </div>
                </div>
            </div>
        </div>
        <!--end::Content-->

    </div>
</div>

<script src="inc/assets/vendor/sweetalert2/dist/sweetalert2.all.min.js"></script>

<script>

    function get_holdings_person(hold_num) {

        $.ajax({
            type: "post",
            url: "searching_tax",
            data: {
                input_val: hold_num
            },
            dataType: "json",
            success: function (res) {

                if (!$.trim(res)) {
                $('.tax_collect_infos').html(`
                        <div class="border-2 border-dashed border-danger-300 p-10 text-center text-lg text-danger">
                            হোল্ডিং নম্বর সঠিক নয়
                        </div>`);
                }else {

                    let loop_data = '';
                    let asd = '';
                    for (let o = 0; o < res.c_year_s.length; o++) {
                        asd += `
                                <tr class="year_collec_single border-b border-neutral-100">
                                    <td>
                                        <div class="assign_img_check"></div>
                                        <input type="text" readonly class="ht-field sals${0} year_collect" name="" id="inlineFormInputGroup" value="${res.c_year_s[o]}" >
                                    </td>
                                    <td>
                                        <input type="text" readonly class="ht-field vals${0} collct_amnt" name="" onkeypress="return (event.charCode !=8 && event.charCode ==0 || (event.charCode >= 48 && event.charCode <= 57))" value="${res.get_search_data.holding_rates_yearly}" id="inlineFormInputGroup" >
                                    </td>
                                </tr>`;
                    }

                    for (let l = 0; l < res.paid_amount.length; l++) {
                        loop_data += `
                                    <tr class="border-b border-neutral-100">
                                        <td class="py-2 text-sm">${res.paid_amount[l].entry_date}</td>
                                        <td class="py-2 text-sm">${res.paid_amount[l].payable_year}</td>
                                        <td class="py-2 text-sm tabular">${res.paid_amount[l].paid_amount}</td>
                                        <td class="py-2 text-sm">
                                            <button type="button" class="ht-btn ht-btn-ghost print_btn" invoice_no_attr="${res.paid_amount[l].voucher}">Print</button>
                                        </td>
                                    </tr>`;
                    }

                    $('.tax_collect_infos').html(`
                        <div class="flex flex-col gap-6">
                            <h2 class="mb-3 text-center text-xl font-bold text-neutral-900">হোল্ডিং তথ্য</h2>

                            <div class="text-center">
                                <a class="mt-2 mb-0 text-2xl font-bold text-neutral-800">
                                    ${res.get_search_data.family_person_name_head}
                                </a>
                                <div class="mb-4 mt-1 font-medium text-neutral-500">
                                    ${res.get_search_data.holding_number}
                                </div>
                            </div>

                            <div class="flex flex-col items-center gap-4 lg:flex-row">
                                <div class="flex w-full flex-col gap-2 lg:w-1/3">
                                    <label class="ht-label" for="total_amount_input">পরিশোধযোগ্য (রেট: ${res.get_search_data.holding_rates_yearly})</label>
                                    <div class="flex items-stretch gap-2">
                                        <span class="flex items-center rounded-md border border-neutral-300 bg-neutral-50 px-3 text-xl">&#2547;</span>
                                        <input id="total_amount_input" type="text" readonly
                                            onkeypress="return (event.charCode !=8 && event.charCode ==0 || (event.charCode >= 48 && event.charCode <= 57))"
                                            class="ht-field total_amount_typing tabular text-base font-bold text-neutral-900" value="0"
                                            placeholder="Total Amount" name="total_amu">
                                    </div>
                                    <table class="mt-3 w-full border-collapse text-sm ass_tables">${asd}</table>
                                </div>
                                <div class="collect_action_btn flex w-full justify-center lg:w-1/3">
                                    <button type="button" person_id_attr="${res.get_search_data.data_id}"
                                        class="ht-btn ht-btn-primary w-full text-sm font-bold">
                                        কালেক্ট করুন
                                    </button>
                                </div>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="w-full border-collapse text-left">
                                    <thead>
                                        <tr class="border-b-2 border-neutral-200">
                                            <th class="py-2 pr-4 text-xs font-semibold uppercase text-neutral-500">তারিখ</th>
                                            <th class="py-2 pr-4 text-xs font-semibold uppercase text-neutral-500">বছর</th>
                                            <th class="py-2 pr-4 text-xs font-semibold uppercase text-neutral-500">টাকা</th>
                                            <th class="py-2 text-xs font-semibold uppercase text-neutral-500">অবস্থা</th>
                                        </tr>
                                    </thead>
                                    <tbody>${loop_data}</tbody>
                                </table>
                            </div>
                        </div>
                    `);
                }
            }
        });
    }


    $(document).on('click', '.year_collec_single', function () {

        if($(this).find('.collct_amnt').hasClass('amnt_countss')){
            $(this).find('.collct_amnt').removeClass('amnt_countss')
        }else{
            $(this).find('.collct_amnt').addClass('amnt_countss');
        }

        if($(this).find('.assign_img_check').hasClass('img_exists')){
            $(this).find('.assign_img_check').removeClass('img_exists');
            $(this).find('.assign_img_check').html('');
            $(this).find('.year_collect').removeAttr('name');
            $(this).find('.collct_amnt').removeAttr('name');
        }else{
            $(this).find('.assign_img_check').addClass('img_exists');
            $(this).find('.assign_img_check').html(`<img class="cursor-pointer" width="30px" height="20px" src="inc/assets/images/check_icon.png" alt="">`);
            $(this).find('.year_collect').attr('name', 'entry_sals[]');
            $(this).find('.collct_amnt').attr('name', 'entry_stk[]');
        }
        total_c_amnt();
    });


    $(document).on('click', '.search_btn_', function () {
        let typing_num_ber = $('.typing_num_ber').val();
        get_holdings_person(typing_num_ber);
    });

    $(document).on('click', '.tax_collect_btn', function () {
        let types_amnt = $('.total_amount_typing').val();
        if (types_amnt <= 0) {
            Swal.fire({
                title: 'টাকার পরিমাণ শূন্য',
                text: 'অনুগ্রহ করে যাচাই করুন, ০ টাকা আদায় করা যাবে না।',
                icon: 'warning',
                confirmButtonText: 'ঠিক আছে',
                confirmButtonColor: '#4f46e5'
            });
            return;
        }

        let typing_num_ber = $('.typing_num_ber').val();
        let person_id = $(this).attr('person_id_attr');
        let entry_sals = $('input[name="entry_sals[]"]').map(function(){ return this.value; }).get();
        let entry_stk = $('input[name="entry_stk[]"]').map(function(){ return this.value; }).get();

        vConfirm('কর আদায় নিশ্চিতকরণ', `আপনি কি মোট ৳ ${types_amnt} টাকা কর আদায় করতে চান?`).then(function(confirmed) {
            if (!confirmed) return;
            $.ajax({
                type: "post",
                url: "taxs_paid",
                data: {
                    person: person_id,
                    amounts: types_amnt,
                    'entry_sals[]': entry_sals,
                    'entry_stk[]': entry_stk,
                },
                success: function (res) {
                        $('.collect_action_btn').html('');
                        Swal.fire({
                            title: '<strong>টাকা সংগ্রহ সফল</strong>',
                            icon: 'success',
                            html: `${types_amnt}/- সংগ্রহ সফল<br> <button type="button" class="ht-btn ht-btn-primary mt-2 print_btn" invoice_no_attr="${res}">প্রিন্ট</button>`,
                            showConfirmButton: false,
                        });
                    }
                });
            }
        }
    });

    $(document).on('keyup', '.amnt_countss', function () {
        total_c_amnt();
    });

    function total_c_amnt() {
        var sum_t = 0;
        $('.amnt_countss').each(function(){
            var bill_r = $(this).val();
            if (bill_r != 0) {
                sum_t += parseInt(bill_r);
            }
        });
        $('.total_amount_typing').val(sum_t);
    }

    $(document).on('click', '.print_btn', function () {
        let unqss = $(this).attr('invoice_no_attr');
        window.open('slip?unqs='+unqss,'_blank', 'width=800,height=800,left=300,top=300');

        setTimeout(function(){ window.location.href = "apps_api"; }, 5000);
    });

</script>
