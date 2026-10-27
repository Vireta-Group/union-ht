<!-- Breadcrumb & Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-neutral-200">
    <div>
        <nav class="flex items-center gap-2 text-xs text-neutral-500 mb-1">
            <a href="dashboard" class="hover:text-indigo-600 transition flex items-center gap-1">
                <i class="ph ph-house text-sm"></i> ড্যাশবোর্ড
            </a>
            <i class="ph ph-caret-right text-[10px] text-neutral-400"></i>
            <span class="text-neutral-800 font-medium">অনুমোদন বিবরণী</span>
        </nav>
        <h1 class="text-xl sm:text-2xl font-bold text-neutral-900 tracking-tight flex items-center gap-2">
            <span class="w-2.5 h-6 bg-amber-500 rounded-full inline-block"></span>
            অনুমোদন-প্রতীক্ষায় জমার তালিকা
        </h1>
        <p class="text-xs sm:text-sm text-neutral-500 mt-0.5">আদায়কারীদের জমা অনুমোদন ও ব্যালেন্স নিশ্চিতকরণ</p>
    </div>
</div>

<!-- Status Message -->
<div id="approval_msg" class="hidden"></div>

<!-- Table Card -->
<div class="bg-white rounded-2xl border border-neutral-200/80 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-neutral-100 flex items-center justify-between">
        <h3 class="text-sm font-bold text-neutral-900 flex items-center gap-2">
            <i class="ph-bold ph-clock text-amber-500 text-base"></i>
            অনুমোদন-প্রতীক্ষায় জমা
        </h3>
        <button type="button" onclick="get_pending_deposite_details()" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-neutral-100 hover:bg-neutral-200 text-neutral-700 rounded-xl text-xs font-semibold transition">
            <i class="ph ph-arrows-clockwise text-sm"></i> রিফ্রেশ
        </button>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-neutral-50/80 border-b border-neutral-200/80 text-neutral-600 font-bold">
                    <th class="py-3 px-4 w-12 text-center">ক্রমিক</th>
                    <th class="py-3 px-4">আদায়কারী</th>
                    <th class="py-3 px-4 text-center">জমার তারিখ</th>
                    <th class="py-3 px-4 text-right">পরিমাণ</th>
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
    get_pending_deposite_details();

    function get_pending_deposite_details() {
        $.ajax({
            type: "post",
            url: "pendingList",
            data: "",
            success: function (res) {
                let view_datas = '';
                if (!res || res.length === 0) {
                    view_datas = `
                        <tr>
                            <td colspan="5" class="py-12 text-center text-neutral-400">
                                <div class="flex flex-col items-center gap-2">
                                    <i class="ph ph-check-circle text-3xl text-emerald-400"></i>
                                    <span>কোন অনুমোদন-প্রতীক্ষায় জমা নেই</span>
                                </div>
                            </td>
                        </tr>`;
                } else {
                    for (let a = 0; a < res.length; a++) {
                        let r = res[a];
                        view_datas += `
                            <tr class="hover:bg-neutral-50/80 transition">
                                <td class="py-3 px-4 text-center font-mono text-neutral-500">${a + 1}</td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center">
                                            <i class="ph ph-user text-sm"></i>
                                        </div>
                                        <span class="font-bold text-neutral-900">${r.user_person_name || '—'}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-center text-neutral-600 font-mono">${r.dateingsdatesss || '—'}</td>
                                <td class="py-3 px-4 text-right font-bold text-neutral-900">৳ ${r.usr_amnt_cuttt || 0}</td>
                                <td class="py-3 px-4 text-center">
                                    <button type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 hover:bg-emerald-600 text-emerald-700 hover:text-white rounded-xl text-xs font-bold shadow-sm transition appruveBtn"
                                        thisAttrID="${r.usr_bal_cutting_idiiis}">
                                        <i class="ph-bold ph-check text-sm"></i>
                                        <span>অনুমোদন</span>
                                    </button>
                                </td>
                            </tr>`;
                    }
                }
                $('.html_tr_datas').html(view_datas);
            },
            error: function() {
                $('.html_tr_datas').html(`
                    <tr>
                        <td colspan="5" class="py-8 text-center text-rose-500 text-xs">
                            তথ্য লোড করতে সমস্যা হয়েছে।
                        </td>
                    </tr>
                `);
            }
        });
    }

    $(document).on('click', '.appruveBtn', function () {
        let $btn = $(this);
        let id = $btn.attr('thisAttrID');

        vConfirm('জমা অনুমোদন', 'আপনি কি এই জমা অনুমোদন করতে নিশ্চিত?').then(function(confirmed) {
            if (!confirmed) return;

            $btn.prop('disabled', true).html('<i class="ph-bold ph-spinner animate-spin text-sm"></i>');

            $.ajax({
                type: "post",
                url: "approveDeposite",
                data: { id: id },
                success: function (r) {
                    $('#approval_msg').removeClass('hidden').html(`
                        <div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-xs font-semibold">
                            অনুমোদন সফল হয়েছে
                        </div>
                    `);
                    setTimeout(() => { $('#approval_msg').addClass('hidden'); }, 5000);
                    get_pending_deposite_details();
                },
                error: function() {
                    $btn.prop('disabled', false).html('<i class="ph-bold ph-check text-sm"></i> <span>অনুমোদন</span>');
                }
            });
        });
    });
</script>