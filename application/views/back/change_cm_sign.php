<!-- Breadcrumb & Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-neutral-200">
    <div>
        <nav class="flex items-center gap-2 text-xs text-neutral-500 mb-1">
            <a href="dashboard" class="hover:text-indigo-600 transition flex items-center gap-1">
                <i class="ph ph-house text-sm"></i> ড্যাশবোর্ড
            </a>
            <i class="ph ph-caret-right text-[10px] text-neutral-400"></i>
            <span class="text-neutral-800 font-medium">চেয়ারম্যান সেটিংস</span>
        </nav>
        <h1 class="text-xl sm:text-2xl font-bold text-neutral-900 tracking-tight flex items-center gap-2">
            <span class="w-2.5 h-6 bg-indigo-600 rounded-full inline-block"></span>
            চেয়ারম্যানের ছবি ও স্বাক্ষর আপডেট
        </h1>
        <p class="text-xs sm:text-sm text-neutral-500 mt-0.5">চেয়ারম্যানের প্রোফাইল ছবি এবং সরকারি স্বাক্ষর আপডেট করুন</p>
    </div>
</div>

<!-- Status Message -->
<div id="show_alert_msg"></div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    <!-- Chairman Photo Upload Card -->
    <div class="bg-white rounded-2xl border border-neutral-200/80 shadow-sm p-5 space-y-4">
        <div class="flex items-center gap-2 pb-3 border-b border-neutral-100">
            <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                <i class="ph-bold ph-user-circle text-base"></i>
            </div>
            <h3 class="text-sm font-bold text-neutral-900">চেয়ারম্যানের ছবি পরিবর্তন</h3>
        </div>

        <div class="flex items-center gap-4">
            <img src="<?= media_url($setting_info->cm_pic_base64, 'image/png') ?>" alt="চেয়ারম্যান" class="w-20 h-20 rounded-2xl object-cover border border-neutral-200 uploaded_cm_pic_show" style="object-fit: cover; object-position: top center;">
            <div class="flex-1">
                <label class="block text-xs font-semibold text-neutral-700 mb-1.5">নতুন ছবি বেছে নিন (PNG, সর্বোচ্চ ১ MB)</label>
                <input type="file" accept="image/png" class="block w-full text-xs text-neutral-700 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer upload_cm_pic_files">
            </div>
        </div>

        <button type="button" class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white rounded-xl text-xs font-bold shadow-sm transition flex items-center justify-center gap-2 changePic">
            <i class="ph-bold ph-cloud-arrow-up text-sm"></i>
            <span>ছবি আপলোড করুন</span>
        </button>
    </div>

    <!-- Chairman Signature - Password-Gated -->
    <div class="bg-white rounded-2xl border border-neutral-200/80 shadow-sm p-5 space-y-4">
        <div class="flex items-center gap-2 pb-3 border-b border-neutral-100">
            <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                <i class="ph-bold ph-pen-nib text-base"></i>
            </div>
            <h3 class="text-sm font-bold text-neutral-900">চেয়ারম্যানের স্বাক্ষর পরিবর্তন</h3>
        </div>

        <div class="p-3 bg-amber-50 border border-amber-200/60 rounded-xl text-xs text-amber-700">
            <i class="ph ph-warning text-sm mr-1"></i>
            স্বাক্ষর পরিবর্তন করতে আপনার পাসওয়ার্ড যাচাই করতে হবে।
        </div>

        <div>
            <label class="block text-xs font-semibold text-neutral-700 mb-1.5">পাসওয়ার্ড</label>
            <div class="relative">
                <input type="password" data-saas-password class="w-full text-xs px-3.5 pr-10 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition types_pass" placeholder="লগইন পাসওয়ার্ড লিখুন">
                <button type="button" data-saas-toggle-password class="absolute right-2.5 top-1/2 -translate-y-1/2 text-neutral-400 hover:text-neutral-600 cursor-pointer" aria-label="পাসওয়ার্ড দেখান">
                    <span data-saas-eye="show"><i class="ph ph-eye text-sm"></i></span>
                    <span data-saas-eye="hide" class="hidden"><i class="ph ph-eye-slash text-sm"></i></span>
                </button>
            </div>
        </div>

        <div class="row assign_html space-y-3"></div>

        <button type="button" class="w-full py-2.5 px-4 bg-amber-600 hover:bg-amber-700 active:bg-amber-800 text-white rounded-xl text-xs font-bold shadow-sm transition flex items-center justify-center gap-2 cng passVerify">
            <i class="ph-bold ph-check text-sm"></i>
            <span>পাসওয়ার্ড যাচাই করুন</span>
        </button>
    </div>

</div>

<script>
    $(document).on('click', '.passVerify', function () {
        let $btn = $(this);
        $btn.prop('disabled', true).html('<i class="ph-bold ph-spinner animate-spin text-sm"></i> যাচাই হচ্ছে...');

        $.ajax({
            type: "post",
            url: "verify_pass",
            data: { pass: $('.types_pass').val() },
            success: function (r) {
                if (r == 1) {
                    $('.types_pass').prop('readonly', true).addClass('opacity-60 cursor-not-allowed');
                    $('.assign_html').html(`
                        <div class="space-y-3">
                            <div class="flex items-center gap-4">
                                <img src="<?= media_url($setting_info->cm_sign_s64, 'image/png') ?>" alt="বর্তমান স্বাক্ষর" class="h-16 border border-neutral-200 rounded-xl object-contain bg-neutral-50 uploaded_cm_sign_show">
                                <div class="flex-1">
                                    <label class="block text-xs font-semibold text-neutral-700 mb-1.5">নতুন স্বাক্ষর (PNG, সর্বোচ্চ ১ MB)</label>
                                    <input type="file" accept="image/png" class="block w-full text-xs text-neutral-700 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100 cursor-pointer upload_cm_sign_files">
                                </div>
                            </div>
                        </div>
                    `);
                    $btn.html('<i class="ph-bold ph-cloud-arrow-up text-sm"></i> <span>স্বাক্ষর আপডেট করুন</span>').prop('disabled', false);
                    $btn.addClass('update_sign').removeClass('passVerify');
                } else {
                    window.location.replace("logout");
                }
            },
            error: function () {
                $btn.html('<i class="ph-bold ph-check text-sm"></i> <span>পাসওয়ার্ড যাচাই করুন</span>').prop('disabled', false);
            }
        });
    });

    $(document).on('click', '.changePic', function () {
        var formData = new FormData();
        if ($('.upload_cm_pic_files').get(0).files.length === 0) {
            $('#show_alert_msg').html('<div class="mb-3 p-3 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-xs font-semibold">ছবি নির্বাচন করুন।</div>');
            return;
        }
        formData.append('pics', $('.upload_cm_pic_files')[0].files[0]);
        $.ajax({
            type: "post",
            url: "update_man_picture",
            data: formData,
            processData: false,
            contentType: false,
            success: function (rs) {
                if (rs == 1) {
                    $('#show_alert_msg').html('<div class="mb-3 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-xs font-semibold">ছবি সফলভাবে আপডেট হয়েছে</div>');
                    setTimeout(() => { $('#show_alert_msg').html(''); }, 4000);
                } else {
                    window.location.replace("logout");
                }
            }
        });
    });

    $(document).on('click', '.update_sign', function () {
        var formData = new FormData();
        if ($('.upload_cm_sign_files').get(0).files.length === 0) {
            $('#show_alert_msg').html('<div class="mb-3 p-3 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-xs font-semibold">স্বাক্ষর ফাইল নির্বাচন করুন।</div>');
            return;
        }
        formData.append('sign', $('.upload_cm_sign_files')[0].files[0]);
        formData.append('pass', $('.types_pass').val());
        $.ajax({
            type: "post",
            url: "update_man_sign",
            data: formData,
            processData: false,
            contentType: false,
            success: function (rs) {
                if (rs == 1) {
                    $('#show_alert_msg').html('<div class="mb-3 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-xs font-semibold">স্বাক্ষর সফলভাবে আপডেট হয়েছে। ড্যাশবোর্ডে নিয়ে যাওয়া হচ্ছে...</div>');
                    $('.cng').hide();
                    setTimeout(() => { window.location.replace("dashboard"); }, 5000);
                } else {
                    window.location.replace("logout");
                }
            }
        });
    });

    $(document).on('change', '.upload_cm_sign_files', function () { showUploadedImage(this, '.uploaded_cm_sign_show'); });
    $(document).on('change', '.upload_cm_pic_files', function () { showUploadedImage(this, '.uploaded_cm_pic_show'); });

    function showUploadedImage(input, targetSelector) {
        if (input.files && input.files[0]) {
            let reader = new FileReader();
            reader.onload = function (e) { $(targetSelector).attr('src', e.target.result); };
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
