<!-- Breadcrumb & Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-neutral-200">
    <div>
        <nav class="flex items-center gap-2 text-xs text-neutral-500 mb-1">
            <a href="dashboard" class="hover:text-indigo-600 transition flex items-center gap-1">
                <i class="ph ph-house text-sm"></i> ড্যাশবোর্ড
            </a>
            <i class="ph ph-caret-right text-[10px] text-neutral-400"></i>
            <span class="text-neutral-800 font-medium">সনদ ব্যবস্থাপনা</span>
        </nav>
        <h1 class="text-xl sm:text-2xl font-bold text-neutral-900 tracking-tight flex items-center gap-2">
            <span class="w-2.5 h-6 bg-indigo-600 rounded-full inline-block"></span>
            গ্লোবাল সনদ সম্পাদনা
        </h1>
        <p class="text-xs sm:text-sm text-neutral-500 mt-0.5">বিদ্যমান সনদের শিরোনাম ও ফরম্যাট পরিবর্তন করুন</p>
    </div>
</div>

<div class="bg-white rounded-2xl border border-neutral-200/80 shadow-sm p-5 sm:p-6 space-y-5">
    <div class="flex items-center gap-2 pb-3 border-b border-neutral-100">
        <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
            <i class="ph-bold ph-pencil-simple text-base"></i>
        </div>
        <h3 class="text-sm font-bold text-neutral-900">সনদ নির্বাচন ও বিবরণ সম্পাদনা</h3>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-semibold text-neutral-700 mb-1.5">সনদ নির্বাচন করুন</label>
            <select class="w-full text-xs sm:text-sm px-3.5 py-2.5 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition sonod_selecting_s">
                <option value="">সনদ নির্বাচন করুন...</option>
                <?php if (!empty($all_sonod)): ?>
                    <?php foreach ($all_sonod as $sonod): ?>
                        <option value="<?= $sonod->deft_cer_all_au_iddd; ?>"><?= htmlspecialchars($sonod->certify_titles ?? '', ENT_QUOTES, 'UTF-8'); ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
        <div>
            <label for="sonodTitleLabel" class="block text-xs font-semibold text-neutral-700 mb-1.5">সনদের হেডিং / শিরোনাম</label>
            <input class="w-full text-xs sm:text-sm px-3.5 py-2.5 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition sonodTitle" type="text" id="sonodTitleLabel" placeholder="সনদের নাম">
        </div>
    </div>

    <!-- Rich Editor Container -->
    <div class="space-y-1.5">
        <label class="block text-xs font-semibold text-neutral-700">সনদের বিস্তারিত ফরম্যাট</label>
        <div class="border border-neutral-200 rounded-xl overflow-hidden">
            <textarea id="rich_editor"></textarea>
        </div>
    </div>

    <div class="flex items-center justify-end pt-3 border-t border-neutral-100">
        <button type="button" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white rounded-xl text-xs font-bold shadow-sm transition createNewSonod cursor-pointer">
            <i class="ph-bold ph-floppy-disk text-base"></i>
            <span>পরিবর্তন সংরক্ষণ করুন</span>
        </button>
    </div>
</div>

<script src="inc/assets/vendor/sweetalert2/dist/sweetalert2.all.min.js"></script>
<script src="inc/assets/vendor/tinymce/js/tinymce/tinymce.js"></script>

<script>
    tinymce.init({
        selector: '#rich_editor',
        menubar: '',
        toolbar: "undo redo | blocks fontfamily fontsize | bold italic underline | align numlist bullist | link image | table media | forecolor backcolor | code fullscreen",
        height: 350,
        quickbars_selection_toolbar: 'bold italic | quicklink h2 h3 blockquote quickimage quicktable'
    });

    $(document).on('change', '.sonod_selecting_s', function () {
        var selected_sonod_idd = $(this).val();
        if (!selected_sonod_idd) return;

        $.ajax({
            type: "post",
            url: "getSingleSonod",
            data: { sonod_id: selected_sonod_idd },
            success: function (r) {
                if (r && r.pi) {
                    $('.sonodTitle').val(r.pi.certify_title_text_s || '');
                    tinymce.get("rich_editor").setContent(r.pi.certify_detail_ss || '');
                }
            }
        });
    });

    $(document).on('click', '.createNewSonod', function () {
        var ck_data = tinymce.get("rich_editor").getContent();
        var head_data = $('.sonodTitle').val() || '';
        let $btn = $(this);
        $btn.prop('disabled', true);

        $.ajax({
            type: "post",
            url: "saveEditSonod",
            data: {
                head: head_data,
                data: ck_data
            },
            success: function (r) {
                $btn.prop('disabled', false);
                Swal.fire({
                    title: 'পরিবর্তন সংরক্ষিত',
                    icon: 'success',
                    text: 'সনদের তথ্য সফলভাবে আপডেট হয়েছে।',
                    confirmButtonText: 'ঠিক আছে',
                    confirmButtonColor: '#4f46e5'
                });
            },
            error: function() {
                $btn.prop('disabled', false);
            }
        });
    });
</script>
