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
            নতুন গ্লোবাল সনদ যুক্ত করুন
        </h1>
        <p class="text-xs sm:text-sm text-neutral-500 mt-0.5">ডিফল্ট সনদের শিরোনাম, হেডিং ও ফরম্যাট কনফিগার করুন</p>
    </div>
</div>

<div class="bg-white rounded-2xl border border-neutral-200/80 shadow-sm p-5 sm:p-6 space-y-5">
    <div class="flex items-center gap-2 pb-3 border-b border-neutral-100">
        <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
            <i class="ph-bold ph-certificate text-base"></i>
        </div>
        <h3 class="text-sm font-bold text-neutral-900">সনদ বিবরণ ও কনফিগারেশন</h3>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-semibold text-neutral-700 mb-1.5">সনদের হেডিং (Sonod Heading)</label>
            <input class="w-full text-xs sm:text-sm px-3.5 py-2.5 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition sonodHeading" type="text" placeholder="উদাঃ নাগরিকত্ব সনদপত্র">
        </div>
        <div>
            <label class="block text-xs font-semibold text-neutral-700 mb-1.5">সনদের টাইটেল (Sonod Title)</label>
            <input class="w-full text-xs sm:text-sm px-3.5 py-2.5 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition sonodTitle" type="text" placeholder="উদাঃ ওয়ারিশান সনদ">
        </div>
    </div>

    <!-- Language Selector Tabs -->
    <div>
        <label class="block text-xs font-semibold text-neutral-700 mb-1.5">ভাষা নির্বাচন</label>
        <div class="inline-flex p-1 rounded-xl bg-neutral-100 border border-neutral-200 gap-1">
            <label class="px-4 py-1.5 rounded-lg text-xs font-bold transition cursor-pointer flex items-center gap-2 has-[:checked]:bg-white has-[:checked]:text-indigo-700 has-[:checked]:shadow-2xs text-neutral-600 hover:text-neutral-900">
                <input class="selectingRadioCheck sr-only" type="radio" name="selectLangTypes" id="inlineRadio1" value="1" checked>
                <span>বাংলা</span>
            </label>
            <label class="px-4 py-1.5 rounded-lg text-xs font-bold transition cursor-pointer flex items-center gap-2 has-[:checked]:bg-white has-[:checked]:text-indigo-700 has-[:checked]:shadow-2xs text-neutral-600 hover:text-neutral-900">
                <input class="selectingRadioCheck sr-only" type="radio" name="selectLangTypes" id="inlineRadio2" value="2">
                <span>English</span>
            </label>
        </div>
    </div>

    <!-- Rich Editor Container -->
    <div class="space-y-1.5">
        <label class="block text-xs font-semibold text-neutral-700">সনদের মূল বয়ান ও ফরম্যাট</label>
        <div class="border border-neutral-200 rounded-xl overflow-hidden">
            <textarea id="rich_editor"></textarea>
        </div>
    </div>

    <div class="flex items-center justify-end pt-3 border-t border-neutral-100">
        <button type="button" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white rounded-xl text-xs font-bold shadow-sm transition saveGlobalSonod cursor-pointer">
            <i class="ph-bold ph-floppy-disk text-base"></i>
            <span>সনদ সংরক্ষণ করুন</span>
        </button>
    </div>
</div>

<script src="inc/assets/vendor/sweetalert2/dist/sweetalert2.all.min.js"></script>
<script src="inc/assets/vendor/tinymce/js/tinymce/tinymce.js"></script>

<script>
    tinymce.init({
        selector: '#rich_editor',
        plugins: 'preview importcss searchreplace autolink autosave save directionality code visualblocks visualchars fullscreen image link media template codesample table charmap pagebreak nonbreaking anchor insertdatetime advlist lists wordcount help charmap quickbars emoticons accordion',
        editimage_cors_hosts: ['picsum.photos'],
        menubar: 'file edit view insert format tools table help',
        toolbar: "undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | align numlist bullist | link image | table media | forecolor backcolor removeformat | code fullscreen preview | print",
        autosave_ask_before_unload: true,
        height: 350,
        image_caption: true,
        quickbars_selection_toolbar: 'bold italic | quicklink h2 h3 blockquote quickimage quicktable',
        toolbar_mode: 'sliding'
    });

    $(document).on('click', '.saveGlobalSonod', function () {
        if ($('.sonodHeading').val() == '' || $('.sonodTitle').val() == '' || $('input[name="selectLangTypes"]:checked').val() == '') {
            Swal.fire({
                title: 'তথ্য অসম্পূর্ণ',
                text: 'অনুগ্রহ করে সকল প্রয়োজনীয় ফিল্ড পূরণ করুন।',
                icon: 'warning',
                confirmButtonText: 'ঠিক আছে',
                confirmButtonColor: '#4f46e5'
            });
        } else {
            let $btn = $(this);
            $btn.prop('disabled', true);
            $.ajax({
                type: "post",
                url: "addGSonod",
                data: {
                    head: $('.sonodHeading').val(),
                    title: $('.sonodTitle').val(),
                    langType: $('input[name="selectLangTypes"]:checked').val(),
                    data: tinymce.get("rich_editor").getContent()
                },
                success: function (r) {
                    $btn.prop('disabled', false);
                    tinymce.get("rich_editor").setContent('');
                    $('.sonodHeading').val('');
                    $('.sonodTitle').val('');
                    Swal.fire({
                        title: 'সনদ সংরক্ষিত হয়েছে',
                        icon: 'success',
                        text: 'গ্লোবাল সনদ সফলভাবে যুক্ত করা হয়েছে।',
                        confirmButtonText: 'ঠিক আছে',
                        confirmButtonColor: '#4f46e5'
                    });
                },
                error: function() {
                    $btn.prop('disabled', false);
                }
            });
        }
    });
</script>
