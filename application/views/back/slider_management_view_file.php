<!-- Breadcrumb & Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-neutral-200">
    <div>
        <nav class="flex items-center gap-2 text-xs text-neutral-500 mb-1">
            <a href="dashboard" class="hover:text-indigo-600 transition flex items-center gap-1">
                <i class="ph ph-house text-sm"></i> ড্যাশবোর্ড
            </a>
            <i class="ph ph-caret-right text-[10px] text-neutral-400"></i>
            <span class="text-neutral-800 font-medium">স্লাইডার ব্যবস্থাপনা</span>
        </nav>
        <h1 class="text-xl sm:text-2xl font-bold text-neutral-900 tracking-tight flex items-center gap-2">
            <span class="w-2.5 h-6 bg-indigo-600 rounded-full inline-block"></span>
            হোম পেজ স্লাইডার ব্যবস্থাপনা
        </h1>
        <p class="text-xs sm:text-sm text-neutral-500 mt-0.5">ইউনিয়নের প্রথম পাতার স্লাইডার ছবি যোগ ও মুছে ফেলার ব্যবস্থাপনা</p>
    </div>
</div>

<!-- Status Message -->
<div id="show_alert_msg"></div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Upload Card -->
    <div class="bg-white rounded-2xl border border-neutral-200/80 shadow-sm p-5 space-y-4">
        <div class="flex items-center gap-2 pb-3 border-b border-neutral-100">
            <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                <i class="ph-bold ph-image-square text-base"></i>
            </div>
            <h3 class="text-sm font-bold text-neutral-900">নতুন স্লাইডার যোগ করুন</h3>
        </div>

        <div class="aspect-video rounded-xl border-2 border-dashed border-neutral-200 bg-neutral-50 flex items-center justify-center overflow-hidden">
            <img src="" alt="" class="object-cover w-full h-full uploaded_cm_pic_show hidden">
            <div class="text-center text-neutral-400 no_preview_placeholder">
                <i class="ph ph-image text-4xl mb-2 block"></i>
                <span class="text-xs">ছবি প্রিভিউ এখানে দেখাবে</span>
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-neutral-700 mb-1.5">স্লাইডার ছবি বেছে নিন</label>
            <p class="text-[11px] text-neutral-400 mb-2">শুধুমাত্র PNG ও JPG, সর্বোচ্চ ১ MB</p>
            <input type="file" accept="image/*" class="block w-full text-xs text-neutral-700 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer upload_cm_pic_files">
        </div>

        <button type="button" class="w-full py-2 px-4 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white rounded-xl text-xs font-semibold shadow-2xs transition flex items-center justify-center gap-1.5 changePic">
            <i class="ph-bold ph-plus text-xs"></i>
            <span>স্লাইডার যোগ করুন</span>
        </button>
    </div>

    <!-- Sliders Table Card -->
    <div class="lg:col-span-2 bg-white rounded-2xl border border-neutral-200/80 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-neutral-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-neutral-900 flex items-center gap-2">
                <i class="ph ph-images-square text-indigo-600 text-base"></i>
                বর্তমান স্লাইডার তালিকা
            </h3>
            <button type="button" onclick="getAllSliders()" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-neutral-100 hover:bg-neutral-200 text-neutral-700 rounded-xl text-xs font-semibold transition">
                <i class="ph ph-arrows-clockwise text-xs"></i> রিফ্রেশ
            </button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-neutral-50/80 border-b border-neutral-200/80 text-neutral-600 font-bold">
                        <th class="py-3 px-4 w-12 text-center">ক্রমিক</th>
                        <th class="py-3 px-4">স্লাইডার ছবি</th>
                        <th class="py-3 px-4 text-center w-24">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 html_tr_datas">
                    <tr>
                        <td colspan="3" class="py-12 text-center text-neutral-400">
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
</div>

<script>
    getAllSliders();

    function getAllSliders() {
        $.ajax({
            type: "post",
            url: "getSldr",
            data: "",
            dataType: "json",
            success: function (r) {
                let html = '';
                if (!r || r.length === 0) {
                    html = `
                        <tr>
                            <td colspan="3" class="py-12 text-center text-neutral-400">
                                <div class="flex flex-col items-center gap-2">
                                    <i class="ph ph-images-square text-3xl text-neutral-300"></i>
                                    <span>কোন স্লাইডার নেই</span>
                                </div>
                            </td>
                        </tr>`;
                } else {
                    for (let l = 0; l < r.length; l++) {
                        let imgPath = r[l].sliderbase64 || '';
                        let imgSrc = imgPath.startsWith('tenant-') ? `uploads/${imgPath}` : (imgPath ? `data:image/jpeg;base64,${imgPath}` : '');
                        html += `
                            <tr class="hover:bg-neutral-50/80 transition">
                                <td class="py-3 px-4 text-center font-mono text-neutral-500">${l + 1}</td>
                                <td class="py-3 px-4">
                                    ${imgSrc ? `<img src="${imgSrc}" alt="" class="h-16 rounded-xl object-cover border border-neutral-200">` : '<span class="text-neutral-400">ছবি নেই</span>'}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <button type="button" class="inline-flex items-center justify-center w-7 h-7 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white transition deleted_this_sldr"
                                        slrdID="${r[l].home_slider_id_un_p}" title="মুছে ফেলুন">
                                        <i class="ph-bold ph-trash text-xs"></i>
                                    </button>
                                </td>
                            </tr>`;
                    }
                }
                $('.html_tr_datas').html(html);
            }
        });
    }

    $(document).on('click', '.changePic', function () {
        var formData = new FormData();
        if ($('.upload_cm_pic_files').get(0).files.length === 0) {
            $('#show_alert_msg').html('<div class="mb-3 p-3 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-xs font-semibold">ছবি নির্বাচন করুন।</div>');
            return;
        }
        formData.append('pics', $('.upload_cm_pic_files')[0].files[0]);
        $.ajax({
            type: "post",
            url: "insertNewSlider",
            data: formData,
            processData: false,
            contentType: false,
            success: function (rs) {
                getAllSliders();
                $('#show_alert_msg').html('<div class="mb-3 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-xs font-semibold">স্লাইডার সফলভাবে যোগ করা হয়েছে</div>');
                setTimeout(() => { $('#show_alert_msg').html(''); }, 4000);
                $('.uploaded_cm_pic_show').attr('src', '').addClass('hidden');
                $('.no_preview_placeholder').show();
                $('.upload_cm_pic_files').val(null);
            }
        });
    });

    $(document).on('click', '.deleted_this_sldr', function () {
        let id = $(this).attr('slrdID');
        vConfirm('স্লাইডার মুছে ফেলা', 'আপনি কি এই স্লাইডার মুছতে নিশ্চিত?', { danger: true }).then(function(confirmed) {
            if (!confirmed) return;
            $.ajax({
                type: "post",
                url: "deleteThisSlider",
                data: { id: id },
                success: function (r) {
                    getAllSliders();
                    $('#show_alert_msg').html('<div class="mb-3 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-xs font-semibold">স্লাইডার মুছে ফেলা হয়েছে</div>');
                    setTimeout(() => { $('#show_alert_msg').html(''); }, 4000);
                }
            });
        });
    });

    $(document).on('change', '.upload_cm_pic_files', function () {
        if (this.files && this.files[0]) {
            let reader = new FileReader();
            reader.onload = function (e) {
                $('.uploaded_cm_pic_show').attr('src', e.target.result).removeClass('hidden');
                $('.no_preview_placeholder').hide();
            };
            reader.readAsDataURL(this.files[0]);
        }
    });
</script>
