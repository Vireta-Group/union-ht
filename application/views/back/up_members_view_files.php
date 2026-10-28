<!-- Breadcrumb & Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-neutral-200">
    <div>
        <nav class="flex items-center gap-2 text-xs text-neutral-500 mb-1">
            <a href="dashboard" class="hover:text-indigo-600 transition flex items-center gap-1">
                <i class="ph ph-house text-sm"></i> ড্যাশবোর্ড
            </a>
            <i class="ph ph-caret-right text-[10px] text-neutral-400"></i>
            <span class="text-neutral-800 font-medium">ইউপি সদস্য</span>
        </nav>
        <h1 class="text-xl sm:text-2xl font-bold text-neutral-900 tracking-tight flex items-center gap-2">
            <span class="w-2.5 h-6 bg-indigo-600 rounded-full inline-block"></span>
            ইউনিয়ন পরিষদের নির্বাচিত সদস্য তালিকা
        </h1>
        <p class="text-xs sm:text-sm text-neutral-500 mt-0.5">ওয়ার্ড ভিত্তিক নির্বাচিত সদস্য নিবন্ধন ও তথ্য সম্পাদনা</p>
    </div>
    <div>
        <button type="button" data-toggle="modal" data-target="#add_new_user" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 active:scale-98 shadow-xs transition cursor-pointer">
            <i class="ph-bold ph-user-plus text-xs"></i>
            <span>নতুন সদস্য যোগ করুন</span>
        </button>
    </div>
</div>

<!-- Status Message -->
<div class="fade_msg hidden">
    <div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-xs font-semibold alert_msg"></div>
</div>

<!-- Members Table Card -->
<div class="bg-white rounded-2xl border border-neutral-200/80 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-neutral-50/80 border-b border-neutral-200/80 text-neutral-600 font-bold">
                    <th class="py-3 px-4 w-12 text-center">ক্রমিক</th>
                    <th class="py-3 px-4">নাম ও ছবি</th>
                    <th class="py-3 px-4 text-center">ওয়ার্ড নং</th>
                    <th class="py-3 px-4 text-center">মোবাইল</th>
                    <th class="py-3 px-4 text-center w-24">অ্যাকশন</th>
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

<!-- Modal: Add New Member -->
<div id="add_new_user" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered max-w-lg mx-auto my-4" role="document">
        <div class="modal-content bg-white rounded-2xl shadow-2xl border border-neutral-200 overflow-hidden w-full flex flex-col max-h-[calc(100vh-3.5rem)]">
            <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-100 shrink-0 bg-white">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <i class="ph-bold ph-user-plus text-lg"></i>
                    </div>
                    <h3 class="text-sm font-bold text-neutral-900">নতুন ইউপি সদস্য যোগ করুন</h3>
                </div>
                <button type="button" class="w-8 h-8 rounded-xl text-neutral-400 hover:text-neutral-700 hover:bg-neutral-100 flex items-center justify-center transition cursor-pointer" aria-label="বন্ধ করুন" title="বন্ধ করুন" data-dismiss="modal">
                    <i class="ph ph-x text-lg"></i>
                </button>
            </div>
            <div class="p-6 overflow-y-auto space-y-4 flex-1 min-h-0 bg-white">
                <div class="flex items-center gap-4">
                    <img src="" alt="" class="w-16 h-16 rounded-xl object-cover border border-neutral-200 bg-neutral-50 emp_pic_shows_new">
                    <div class="flex-1">
                        <label class="block text-xs font-semibold text-neutral-700 mb-1.5">সদস্যের ছবি</label>
                        <input type="file" class="block w-full text-xs text-neutral-700 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer emp_pic_new" accept="image/*">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-neutral-700 mb-1">পূর্ণ নাম <span class="text-rose-500">*</span></label>
                        <input type="text" class="w-full text-xs px-3.5 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition full_name_s_new" placeholder="সদস্যের পূর্ণ নাম">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-neutral-700 mb-1">ওয়ার্ড নং <span class="text-rose-500">*</span></label>
                        <select class="w-full text-xs px-3.5 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition ward_no_new_select">
                            <option value="">-- ওয়ার্ড নির্বাচন করুন --</option>
                            <option value="01">ওয়ার্ড নং ০১</option>
                            <option value="02">ওয়ার্ড নং ০২</option>
                            <option value="03">ওয়ার্ড নং ০৩</option>
                            <option value="04">ওয়ার্ড নং ০৪</option>
                            <option value="05">ওয়ার্ড নং ০৫</option>
                            <option value="06">ওয়ার্ড নং ০৬</option>
                            <option value="07">ওয়ার্ড নং ০৭</option>
                            <option value="08">ওয়ার্ড নং ০৮</option>
                            <option value="09">ওয়ার্ড নং ০৯</option>
                            <option value="01-02-03">ওয়ার্ড নং ০১-০২-০৩ (মহিলা)</option>
                            <option value="04-05-06">ওয়ার্ড নং ০৪-০৫-০৬ (মহিলা)</option>
                            <option value="07-08-09">ওয়ার্ড নং ০৭-০৮-০৯ (মহিলা)</option>
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-semibold text-neutral-700 mb-1">মোবাইল নম্বর</label>
                        <input type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');" class="w-full text-xs px-3.5 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition phone_nos_new" placeholder="০১XXXXXXXXX">
                    </div>
                </div>
            </div>
            <div class="bg-neutral-50 px-5 py-3 border-t border-neutral-100 flex items-center justify-end gap-2 shrink-0">
                <button type="button" class="px-3 py-1.5 rounded-xl text-xs font-semibold text-neutral-700 bg-white border border-neutral-300 hover:bg-neutral-100 shadow-2xs transition cursor-pointer" data-dismiss="modal">বাতিল</button>
                <button type="button" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold shadow-2xs transition entry_new_users cursor-pointer">
                    <i class="ph-bold ph-plus text-xs"></i> সদস্য যোগ করুন
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Edit Member -->
<div id="change_pass" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered max-w-lg mx-auto my-4" role="document">
        <div class="modal-content bg-white rounded-2xl shadow-2xl border border-neutral-200 overflow-hidden w-full flex flex-col max-h-[calc(100vh-3.5rem)]">
            <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-100 shrink-0 bg-white">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                        <i class="ph-bold ph-pencil-simple text-lg"></i>
                    </div>
                    <h3 class="text-sm font-bold text-neutral-900">সদস্যের তথ্য সম্পাদনা</h3>
                </div>
                <button type="button" class="w-8 h-8 rounded-xl text-neutral-400 hover:text-neutral-700 hover:bg-neutral-100 flex items-center justify-center transition cursor-pointer" aria-label="বন্ধ করুন" title="বন্ধ করুন" data-dismiss="modal">
                    <i class="ph ph-x text-lg"></i>
                </button>
            </div>
            <div class="p-6 overflow-y-auto space-y-4 flex-1 min-h-0 bg-white">
                <div class="flex items-center gap-4">
                    <img src="" alt="" class="w-16 h-16 rounded-xl object-cover border border-neutral-200 bg-neutral-50 emp_pic_shows_edit">
                    <div class="flex-1">
                        <label class="block text-xs font-semibold text-neutral-700 mb-1.5">নতুন ছবি (ঐচ্ছিক)</label>
                        <input type="file" class="block w-full text-xs text-neutral-700 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100 cursor-pointer emp_pic_edit" accept="image/*">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-neutral-700 mb-1">পূর্ণ নাম</label>
                        <input type="text" class="w-full text-xs px-3.5 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition full_name_s_edit" placeholder="সদস্যের পূর্ণ নাম">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-neutral-700 mb-1">ওয়ার্ড নং</label>
                        <select class="w-full text-xs px-3.5 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition select_ward_no_edit">
                            <option value="">-- নির্বাচন করুন --</option>
                            <option value="01">ওয়ার্ড নং ০১</option>
                            <option value="02">ওয়ার্ড নং ০২</option>
                            <option value="03">ওয়ার্ড নং ০৩</option>
                            <option value="04">ওয়ার্ড নং ০৪</option>
                            <option value="05">ওয়ার্ড নং ০৫</option>
                            <option value="06">ওয়ার্ড নং ০৬</option>
                            <option value="07">ওয়ার্ড নং ০৭</option>
                            <option value="08">ওয়ার্ড নং ০৮</option>
                            <option value="09">ওয়ার্ড নং ০৯</option>
                            <option value="01-02-03">ওয়ার্ড নং ০১-০২-০৩ (মহিলা)</option>
                            <option value="04-05-06">ওয়ার্ড নং ০৪-০৫-০৬ (মহিলা)</option>
                            <option value="07-08-09">ওয়ার্ড নং ০৭-০৮-০৯ (মহিলা)</option>
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-semibold text-neutral-700 mb-1">মোবাইল নম্বর</label>
                        <input type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');" class="w-full text-xs px-3.5 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition phone_nos_edit" placeholder="০১XXXXXXXXX">
                    </div>
                </div>
                <input type="hidden" class="this_member_ids" value="">
            </div>
            <div class="bg-neutral-50 px-5 py-3 border-t border-neutral-100 flex items-center justify-end gap-2 shrink-0">
                <button type="button" class="px-3 py-1.5 rounded-xl text-xs font-semibold text-neutral-700 bg-white border border-neutral-300 hover:bg-neutral-100 shadow-2xs transition cursor-pointer" data-dismiss="modal">বাতিল</button>
                <button type="button" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-semibold shadow-2xs transition updateThisMembers cursor-pointer">
                    <i class="ph-bold ph-check text-xs"></i> পরিবর্তন সংরক্ষণ করুন
                </button>
            </div>
        </div>
    </div>
</div>

<script src="inc/assets/vendor/toastr.min.js"></script>
<script src="inc/assets/js/toastr.js"></script>

<script>
    get_user_details();

    function get_user_details() {
        $.ajax({
            type: "post",
            url: "getInsUpMember",
            data: "",
            success: function (res) {
                let view_datas = '';
                if (!res || res.length === 0) {
                    view_datas = `
                        <tr>
                            <td colspan="5" class="py-12 text-center text-neutral-400">
                                <div class="flex flex-col items-center gap-2">
                                    <i class="ph ph-users text-3xl text-neutral-300"></i>
                                    <span>কোন সদস্য নেই</span>
                                </div>
                            </td>
                        </tr>`;
                } else {
                    for (let a = 0; a < res.length; a++) {
                        let r = res[a];
                        let picSrc = r.up_mem_pic_base64s ? `data:image/png;base64,${r.up_mem_pic_base64s}` : '';
                        view_datas += `
                            <tr class="hover:bg-neutral-50/80 transition">
                                <td class="py-3 px-4 text-center font-mono text-neutral-500">${a + 1}</td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-3">
                                        ${picSrc ? `<img src="${picSrc}" alt="" class="w-9 h-9 rounded-xl object-cover border border-neutral-200">` : `<div class="w-9 h-9 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center"><i class="ph ph-user text-base"></i></div>`}
                                        <span class="font-bold text-neutral-900">${r.up_mem_s_name || '—'}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700">ওয়ার্ড ${r.ward_no_sq || '—'}</span>
                                </td>
                                <td class="py-3 px-4 text-center font-mono text-neutral-700">${r.mobile_s_no_phone || '—'}</td>
                                <td class="py-3 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button type="button" class="inline-flex items-center justify-center w-7 h-7 rounded-xl bg-amber-50 text-amber-600 hover:bg-amber-500 hover:text-white transition edit_this_member"
                                            this_user_id="${r.up_member_s}" data-toggle="modal" data-target="#change_pass" title="সম্পাদনা">
                                            <i class="ph-bold ph-pencil-simple text-xs"></i>
                                        </button>
                                        <button type="button" class="inline-flex items-center justify-center w-7 h-7 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white transition deleteThisEmp"
                                            this_user_id="${r.up_member_s}" title="মুছে ফেলুন">
                                            <i class="ph-bold ph-trash text-xs"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>`;
                    }
                }
                $('.html_tr_datas').html(view_datas);
            }
        });
    }

    $(document).on('click', '.entry_new_users', function () {
        var formData = new FormData();
        if ($('.emp_pic_new')[0].files[0]) formData.append('pic_file', $('.emp_pic_new')[0].files[0]);
        formData.append('names', $('.full_name_s_new').val());
        formData.append('phone', $('.phone_nos_new').val());
        formData.append('ward_no', $('.ward_no_new_select').val());

        if (!$('.full_name_s_new').val() || !$('.ward_no_new_select').val()) {
            Swal.fire({
                title: 'তথ্য অসম্পূর্ণ',
                text: 'অনুগ্রহ করে নাম ও ওয়ার্ড নম্বর পূরণ করুন।',
                icon: 'warning',
                confirmButtonText: 'ঠিক আছে',
                confirmButtonColor: '#4f46e5'
            });
            return;
        }

        $.ajax({
            type: "post", url: "insertMem", data: formData, processData: false, contentType: false,
            success: function (res) {
                get_user_details();
                $('#add_new_user').modal('hide');
                $('.fade_msg').removeClass('hidden');
                $('.alert_msg').text('সদস্য সফলভাবে যোগ করা হয়েছে');
                setTimeout(() => { $('.fade_msg').addClass('hidden'); }, 5000);
                $('.full_name_s_new,.phone_nos_new,.ward_no_new_select').val('');
                $('.emp_pic_shows_new').attr('src', '');
            }
        });
    });

    $(document).on('click', '.updateThisMembers', function () {
        var formData = new FormData();
        if ($('.emp_pic_edit')[0].files[0]) formData.append('pic_file', $('.emp_pic_edit')[0].files[0]);
        formData.append('names', $('.full_name_s_edit').val());
        formData.append('phone', $('.phone_nos_edit').val());
        formData.append('ward_no', $('.select_ward_no_edit').val());
        formData.append('memID', $('.this_member_ids').val());

        $.ajax({
            type: "post", url: "updateUpMember", data: formData, processData: false, contentType: false,
            success: function (res) {
                get_user_details();
                $('#change_pass').modal('hide');
                $('.fade_msg').removeClass('hidden');
                $('.alert_msg').text('সদস্যের তথ্য আপডেট হয়েছে');
                setTimeout(() => { $('.fade_msg').addClass('hidden'); }, 5000);
            }
        });
    });

    $(document).on('click', '.edit_this_member', function () {
        let this_mem_id = $(this).attr('this_user_id');
        $.ajax({
            type: "post",
            url: "getMemSingle",
            data: { mem_id: this_mem_id },
            success: function (r) {
                $('.emp_pic_shows_edit').attr('src', r.up_mem_pic_base64s ? `data:image/png;base64,${r.up_mem_pic_base64s}` : '');
                $('.full_name_s_edit').val(r.up_mem_s_name);
                $('.phone_nos_edit').val(r.mobile_s_no_phone);
                $('.select_ward_no_edit option[value="' + r.ward_no_sq + '"]').prop('selected', true);
                $('.this_member_ids').val(r.up_member_s);
            }
        });
    });

    $(document).on('click', '.deleteThisEmp', function () {
        let this_emp_id = $(this).attr('this_user_id');
        vConfirm('সদস্য মুছে ফেলা', 'আপনি কি এই সদস্যের তথ্য মুছতে নিশ্চিত?', { danger: true }).then(function(confirmed) {
            if (!confirmed) return;
            $.ajax({
                type: "post",
                url: "delInsMem",
                data: { memID: this_emp_id },
                success: function (r) { get_user_details(); }
            });
        });
    });

    $(document).on('change', '.emp_pic_new', function () { showUploadedImage(this, '.emp_pic_shows_new'); });
    $(document).on('change', '.emp_pic_edit', function () { showUploadedImage(this, '.emp_pic_shows_edit'); });

    function showUploadedImage(input, targetSelector) {
        if (input.files && input.files[0]) {
            let reader = new FileReader();
            reader.onload = function (e) { $(targetSelector).attr('src', e.target.result); };
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
