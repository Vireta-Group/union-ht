<!-- Breadcrumb & Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-neutral-200">
    <div>
        <nav class="flex items-center gap-2 text-xs text-neutral-500 mb-1">
            <a href="dashboard" class="hover:text-indigo-600 transition flex items-center gap-1">
                <i class="ph ph-house text-sm"></i> ড্যাশবোর্ড
            </a>
            <i class="ph ph-caret-right text-[10px] text-neutral-400"></i>
            <span class="text-neutral-800 font-medium">সনদ ব্যবস্থাপনা</span>
            <i class="ph ph-caret-right text-[10px] text-neutral-400"></i>
            <span class="text-indigo-600 font-medium">কাস্টমাইজেশন</span>
        </nav>
        <h1 class="text-xl sm:text-2xl font-bold text-neutral-900 tracking-tight flex items-center gap-2">
            <span class="w-2.5 h-6 bg-indigo-600 rounded-full inline-block"></span>
            ইউনিয়ন সনদ কাস্টমাইজেশন ও নতুন সনদ তৈরি
        </h1>
        <p class="text-xs sm:text-sm text-neutral-500 mt-0.5">গ্লোবাল সনদ থেকে আপনার ইউনিয়নে কাস্টমাইজ করুন অথবা আপনার ইউনিয়নের জন্য নিজস্ব নতুন সনদ যুক্ত করুন</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="newSonod" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-xl text-xs font-semibold transition">
            <i class="ph-bold ph-plus-circle text-sm"></i>
            <span>সনদ ইস্যু করুন</span>
        </a>
        <a href="sonod_list" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-neutral-100 hover:bg-neutral-200 text-neutral-700 rounded-xl text-xs font-semibold transition">
            <i class="ph-bold ph-list-dashes text-sm"></i>
            <span>ইস্যুকৃত তালিকা</span>
        </a>
    </div>
</div>

<!-- Language Selector & Mode Header -->
<div class="bg-white rounded-2xl border border-neutral-200/80 shadow-xs p-5 sm:p-6 space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-neutral-100">
        <!-- Language Switcher -->
        <div>
            <label class="block text-xs font-bold text-neutral-700 mb-1.5">ভাষার ধরন নির্বাচন করুন</label>
            <div class="inline-flex p-1 rounded-xl bg-neutral-100 border border-neutral-200 gap-1">
                <button type="button" class="langSwitchBtn langBN active px-4 py-1.5 rounded-lg text-xs font-bold transition cursor-pointer bg-white text-indigo-700 shadow-2xs" data-lang="1">
                    <i class="ph-bold ph-translate text-sm mr-1"></i> বাংলা সনদ
                </button>
                <button type="button" class="langSwitchBtn langEN px-4 py-1.5 rounded-lg text-xs font-bold transition cursor-pointer text-neutral-600 hover:text-neutral-900" data-lang="2">
                    <i class="ph-bold ph-globe text-sm mr-1"></i> English Sonod
                </button>
            </div>
        </div>

        <!-- Mode Tabs -->
        <div>
            <label class="block text-xs font-bold text-neutral-700 mb-1.5">কাজের ধরন</label>
            <div class="inline-flex p-1 rounded-xl bg-neutral-100 border border-neutral-200 gap-1">
                <button type="button" class="modeSwitchBtn modeImport active px-3.5 py-1.5 rounded-lg text-xs font-bold transition cursor-pointer bg-white text-indigo-700 shadow-2xs" data-mode="import">
                    <i class="ph-bold ph-download-simple text-sm mr-1"></i> গ্লোবাল থেকে কাস্টমাইজ
                </button>
                <button type="button" class="modeSwitchBtn modeScratch px-3.5 py-1.5 rounded-lg text-xs font-bold transition cursor-pointer text-neutral-600 hover:text-neutral-900" data-mode="scratch">
                    <i class="ph-bold ph-sparkle text-sm mr-1"></i> নতুন সনদ তৈরি
                </button>
            </div>
        </div>
    </div>

    <!-- Hidden State Trackers -->
    <input type="hidden" id="editingCustomId" value="">
    <input type="hidden" id="selectedGlobalId" value="">
    <input type="hidden" id="activeLangType" value="1">

    <!-- Global Sonod Selection Box (Visible in 'import' mode) -->
    <div id="importSonodSection" class="space-y-4">
        <div class="bg-indigo-50/50 border border-indigo-100 rounded-xl p-4">
            <label class="block text-xs font-bold text-indigo-900 mb-1.5">
                <i class="ph-bold ph-certificate text-sm mr-1"></i> গ্লোবাল সনদ টেমপ্লেট নির্বাচন করুন
            </label>
            <p class="text-[11px] text-indigo-700/80 mb-2.5">সিস্টেমের ডিফল্ট টেমপ্লেটটি সিলেক্ট করলে তার ফরম্যাট নিচে স্বয়ংক্রিয়ভাবে লোড হবে, যা আপনার ইউনিয়নের জন্য কাস্টমাইজ করতে পারবেন।</p>
            <select id="globalSonodDropdown" class="w-full text-xs sm:text-sm px-3.5 py-2.5 bg-white border border-indigo-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition">
                <option value="">-- গ্লোবাল সনদ সিলেক্ট করুন --</option>
                <?php if (!empty($all_sonod)): ?>
                    <?php foreach ($all_sonod as $sonod): ?>
                        <option value="<?= $sonod->deft_cer_all_au_iddd; ?>" 
                                data-lang="<?= $sonod->en_bn_type; ?>" 
                                data-head="<?= htmlspecialchars($sonod->certify_titles ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                data-title="<?= htmlspecialchars($sonod->certify_title_text_s ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                class="sonod-opt-lang-<?= $sonod->en_bn_type; ?>"
                                <?= ($sonod->en_bn_type != 1) ? 'style="display:none;"' : ''; ?>>
                            <?= htmlspecialchars($sonod->certify_titles ?? '', ENT_QUOTES, 'UTF-8'); ?> (<?= htmlspecialchars($sonod->certify_title_text_s ?? '', ENT_QUOTES, 'UTF-8'); ?>)
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
    </div>

    <!-- Active Edit Indicator (hidden by default) -->
    <div id="editingBanner" class="hidden items-center justify-between p-3 bg-amber-50 border border-amber-200 rounded-xl text-amber-900 text-xs">
        <div class="flex items-center gap-2 font-semibold">
            <i class="ph-bold ph-pencil-simple text-amber-600 text-base"></i>
            <span>আপনি পূর্বে সংরক্ষিত একটি ইউনিয়ন সনদ সম্পাদনা করছেন: <strong id="editingSonodName"></strong></span>
        </div>
        <button type="button" id="cancelEditBtn" class="text-xs text-amber-700 hover:text-amber-950 underline font-bold cursor-pointer">
            বাতিল করুন
        </button>
    </div>

    <!-- Sonod Details Input Fields -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label for="sonodHeadingInput" class="block text-xs font-bold text-neutral-700 mb-1.5">
                সনদের আবেদন / শিরোনাম (Heading) <span class="text-rose-500">*</span>
            </label>
            <input class="w-full text-xs sm:text-sm px-3.5 py-2.5 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition" 
                   type="text" id="sonodHeadingInput" placeholder="উদাঃ নাগরিক সনদের আবেদন / Citizenship Certificate">
        </div>
        <div>
            <label for="sonodTitleInput" class="block text-xs font-bold text-neutral-700 mb-1.5">
                সনদের মূল নাম (Certificate Title) <span class="text-rose-500">*</span>
            </label>
            <input class="w-full text-xs sm:text-sm px-3.5 py-2.5 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition" 
                   type="text" id="sonodTitleInput" placeholder="উদাঃ নাগরিকত্ব সনদপত্র / Citizenship Certificate">
        </div>
    </div>

    <!-- TinyMCE Rich Editor Container -->
    <div class="space-y-1.5">
        <div class="flex items-center justify-between">
            <label class="block text-xs font-bold text-neutral-700">সনদের বিস্তারিত বয়ান ও ফরম্যাট (Template Body) <span class="text-rose-500">*</span></label>
            <span class="text-[11px] text-neutral-400">প্রয়োজনে টেক্সট, ফন্ট ও স্টাইল পরিবর্তন করতে পারেন</span>
        </div>
        <div class="border border-neutral-200 rounded-xl overflow-hidden">
            <textarea id="rich_editor"></textarea>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex items-center justify-between pt-3 border-t border-neutral-100">
        <button type="button" id="resetFormBtn" class="inline-flex items-center gap-1.5 px-4 py-2 bg-neutral-100 hover:bg-neutral-200 text-neutral-700 rounded-xl text-xs font-bold transition cursor-pointer">
            <i class="ph-bold ph-arrow-counter-clockwise text-sm"></i>
            <span>রিসেট</span>
        </button>
        <button type="button" id="saveSonodBtn" class="inline-flex items-center gap-2 px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white rounded-xl text-xs font-bold shadow-sm transition cursor-pointer">
            <i class="ph-bold ph-floppy-disk text-base"></i>
            <span id="saveBtnText">ইউনিয়ন সনদ সংরক্ষণ করুন</span>
        </button>
    </div>
</div>

<!-- Saved Union Custom Sonods List -->
<div class="bg-white rounded-2xl border border-neutral-200/80 shadow-xs p-5 sm:p-6 space-y-4 mt-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-neutral-100">
        <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <i class="ph-bold ph-files text-base"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold text-neutral-900">আপনার ইউনিয়নের কাস্টমাইজড সনদের তালিকা</h3>
                <p class="text-[11px] text-neutral-500">এই সনদগুলো নতুন সনদ তৈরি করার সময় স্বয়ংক্রিয়ভাবে ব্যবহৃত হবে</p>
            </div>
        </div>
        <span class="text-xs bg-neutral-100 text-neutral-600 px-3 py-1 rounded-full font-semibold">
            মোট কাস্টম সনদ: <?= !empty($custom_sonods) ? count($custom_sonods) : 0; ?> টি
        </span>
    </div>

    <?php if (!empty($custom_sonods)): ?>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-neutral-600">
                <thead class="bg-neutral-50 text-neutral-700 uppercase font-semibold border-b border-neutral-200">
                    <tr>
                        <th class="px-4 py-3">ক্র নং</th>
                        <th class="px-4 py-3">সনদের নাম</th>
                        <th class="px-4 py-3">শিরোনাম / হেড</th>
                        <th class="px-4 py-3">ভাষা</th>
                        <th class="px-4 py-3">উৎস</th>
                        <th class="px-4 py-3 text-right">পদক্ষেপ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100">
                    <?php $idx = 1; foreach ($custom_sonods as $csonod): ?>
                        <tr class="hover:bg-neutral-50/60 transition">
                            <td class="px-4 py-3 font-medium text-neutral-900"><?= $idx++; ?></td>
                            <td class="px-4 py-3 font-bold text-neutral-900">
                                <?= htmlspecialchars($csonod->cert_title_ss ?? '', ENT_QUOTES, 'UTF-8'); ?>
                            </td>
                            <td class="px-4 py-3">
                                <?= htmlspecialchars($csonod->certf_head_texts ?? '', ENT_QUOTES, 'UTF-8'); ?>
                            </td>
                            <td class="px-4 py-3">
                                <?php if (($csonod->en_bn_type ?? 1) == 2): ?>
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        <i class="ph-bold ph-globe text-xs"></i> English
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i class="ph-bold ph-translate text-xs"></i> বাংলা
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <?php if (!empty($csonod->deft_cer_all_id)): ?>
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-medium bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        <i class="ph-bold ph-check text-xs"></i> গ্লোবাল কাস্টমাইজড (#<?= $csonod->deft_cer_all_id; ?>)
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-medium bg-amber-50 text-amber-700 border border-amber-200">
                                        <i class="ph-bold ph-sparkle text-xs"></i> নিজস্ব নতুন সনদ
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-right space-x-1.5">
                                <button type="button" 
                                        class="editCustomSonodBtn inline-flex items-center gap-1 px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg text-xs font-semibold transition cursor-pointer"
                                        data-id="<?= $csonod->certifys_default_ins_ss_iiidddd; ?>"
                                        data-head="<?= htmlspecialchars($csonod->certf_head_texts ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                        data-title="<?= htmlspecialchars($csonod->cert_title_ss ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                        data-lang="<?= $csonod->en_bn_type ?? 1; ?>"
                                        data-gid="<?= $csonod->deft_cer_all_id ?? ''; ?>">
                                    <i class="ph-bold ph-pencil-simple text-xs"></i>
                                    <span>সম্পাদনা</span>
                                </button>
                                <button type="button" 
                                        class="deleteCustomSonodBtn inline-flex items-center gap-1 px-2.5 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-lg text-xs font-semibold transition cursor-pointer"
                                        data-id="<?= $csonod->certifys_default_ins_ss_iiidddd; ?>"
                                        data-title="<?= htmlspecialchars($csonod->cert_title_ss ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="ph-bold ph-trash text-xs"></i>
                                    <span>মুছুন</span>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="text-center py-8 bg-neutral-50 rounded-xl border border-dashed border-neutral-200">
            <i class="ph-bold ph-certificate text-3xl text-neutral-300 mb-2"></i>
            <p class="text-xs text-neutral-500 font-medium">আপনার ইউনিয়নের জন্য এখনও কোনো কাস্টম সনদ সংরক্ষণ করা হয়নি।</p>
            <p class="text-[11px] text-neutral-400 mt-0.5">উপরের ফর্ম থেকে যে কোনো গ্লোবাল সনদ কাস্টমাইজ করে অথবা সম্পূর্ণ নতুন সনদ তৈরি করে সংরক্ষণ করতে পারেন।</p>
        </div>
    <?php endif; ?>
</div>

<script src="inc/assets/vendor/sweetalert2/dist/sweetalert2.all.min.js"></script>
<script src="inc/assets/vendor/tinymce/js/tinymce/tinymce.js"></script>

<script>
    tinymce.init({
        selector: '#rich_editor',
        menubar: '',
        toolbar: "undo redo | blocks fontfamily fontsize | bold italic underline | align numlist bullist | link image | table | forecolor backcolor | code fullscreen",
        height: 380,
        content_style: 'body { font-family: "SolaimanLipi", "Kalpurush", Arial, sans-serif; font-size: 14px; }',
        quickbars_selection_toolbar: 'bold italic | quicklink h2 h3 blockquote'
    });

    function applyLanguageFilter(lang) {
        $('#activeLangType').val(lang);
        $('#globalSonodDropdown option').each(function () {
            var optLang = $(this).data('lang');
            if (!optLang) return; // Keep placeholder
            if (optLang == lang) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
        $('#globalSonodDropdown').val('');
    }

    // Language Toggle
    $(document).on('click', '.langSwitchBtn', function () {
        var lang = $(this).data('lang');
        $('.langSwitchBtn').removeClass('active bg-white text-indigo-700 shadow-2xs').addClass('text-neutral-600');
        $(this).addClass('active bg-white text-indigo-700 shadow-2xs').removeClass('text-neutral-600');
        applyLanguageFilter(lang);
    });

    // Mode Toggle (Import vs Scratch)
    $(document).on('click', '.modeSwitchBtn', function () {
        var mode = $(this).data('mode');
        $('.modeSwitchBtn').removeClass('active bg-white text-indigo-700 shadow-2xs').addClass('text-neutral-600');
        $(this).addClass('active bg-white text-indigo-700 shadow-2xs').removeClass('text-neutral-600');

        if (mode === 'import') {
            $('#importSonodSection').slideDown(150);
        } else {
            $('#importSonodSection').slideUp(150);
            $('#selectedGlobalId').val('');
            $('#sonodHeadingInput').val('');
            $('#sonodTitleInput').val('');
            if (tinymce.get("rich_editor")) {
                tinymce.get("rich_editor").setContent('');
            }
        }
    });

    // When Global Sonod is selected in dropdown
    $(document).on('change', '#globalSonodDropdown', function () {
        var gid = $(this).val();
        if (!gid) return;

        $('#selectedGlobalId').val(gid);

        $.ajax({
            type: "post",
            url: "getSingleSonod",
            data: { sonod_id: gid },
            success: function (r) {
                if (r && r.pi) {
                    $('#sonodHeadingInput').val(r.pi.certify_titles || '');
                    $('#sonodTitleInput').val(r.pi.certify_title_text_s || '');
                    if (tinymce.get("rich_editor")) {
                        tinymce.get("rich_editor").setContent(r.pi.certify_detail_ss || '');
                    }
                }
            }
        });
    });

    // Edit Saved Custom Sonod
    $(document).on('click', '.editCustomSonodBtn', function () {
        var id = $(this).data('id');
        var head = $(this).data('head');
        var title = $(this).data('title');
        var lang = $(this).data('lang');
        var gid = $(this).data('gid');

        $('#editingCustomId').val(id);
        $('#selectedGlobalId').val(gid || '');
        $('#sonodHeadingInput').val(head);
        $('#sonodTitleInput').val(title);
        $('#editingSonodName').text(title);
        $('#editingBanner').removeClass('hidden').addClass('flex');
        $('#saveBtnText').text('পরিবর্তন সংরক্ষণ করুন');

        // Set Language toggle
        $('.langSwitchBtn').removeClass('active bg-white text-indigo-700 shadow-2xs').addClass('text-neutral-600');
        $('.langSwitchBtn[data-lang="' + lang + '"]').addClass('active bg-white text-indigo-700 shadow-2xs').removeClass('text-neutral-600');
        applyLanguageFilter(lang);

        // Fetch detail content
        $.ajax({
            type: "post",
            url: "getMyCustomSonod",
            data: { custom_id: id },
            success: function (res) {
                if (res && res.status === 'success' && res.data) {
                    if (tinymce.get("rich_editor")) {
                        tinymce.get("rich_editor").setContent(res.data.certf_detail_s_sett || '');
                    }
                }
            }
        });

        $('html, body').animate({ scrollTop: $('#sonodHeadingInput').offset().top - 100 }, 300);
    });

    // Cancel Edit
    $(document).on('click', '#cancelEditBtn', function () {
        resetForm();
    });

    // Reset Form
    function resetForm() {
        $('#editingCustomId').val('');
        $('#selectedGlobalId').val('');
        $('#sonodHeadingInput').val('');
        $('#sonodTitleInput').val('');
        $('#editingBanner').removeClass('flex').addClass('hidden');
        $('#saveBtnText').text('ইউনিয়ন সনদ সংরক্ষণ করুন');
        $('#globalSonodDropdown').val('');
        if (tinymce.get("rich_editor")) {
            tinymce.get("rich_editor").setContent('');
        }
    }
    $(document).on('click', '#resetFormBtn', resetForm);

    // Save Custom Sonod
    $(document).on('click', '#saveSonodBtn', function () {
        var custom_id = $('#editingCustomId').val();
        var deft_cer_id = $('#selectedGlobalId').val();
        var head = $.trim($('#sonodHeadingInput').val());
        var title = $.trim($('#sonodTitleInput').val());
        var langType = $('#activeLangType').val();
        var content = tinymce.get("rich_editor") ? tinymce.get("rich_editor").getContent() : '';

        if (!head || !title) {
            Swal.fire({
                icon: 'warning',
                title: 'তথ্য অসম্পূর্ণ',
                text: 'অনুগ্রহ করে সনদের শিরোনাম এবং মূল নাম লিখুন।'
            });
            return;
        }

        if (!content) {
            Swal.fire({
                icon: 'warning',
                title: 'বয়ানের ফরম্যাট খালি',
                text: 'অনুগ্রহ করে সনদের মূল বয়ান ও ফরম্যাট লিখুন।'
            });
            return;
        }

        var $btn = $(this);
        $btn.prop('disabled', true).addClass('opacity-70');

        $.ajax({
            type: "post",
            url: "saveMyCustomSonod",
            data: {
                custom_id: custom_id,
                deft_cer_id: deft_cer_id,
                head: head,
                title: title,
                langType: langType,
                data: content
            },
            success: function (res) {
                $btn.prop('disabled', false).removeClass('opacity-70');
                if (res && res.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'সফল!',
                        text: res.message || 'সনদ সফলভাবে সংরক্ষিত হয়েছে।',
                        confirmButtonText: 'ঠিক আছে',
                        confirmButtonColor: '#4f46e5'
                    }).then(function () {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'ব্যর্থ হয়েছে',
                        text: res.message || 'সনদ সংরক্ষণ করা যায়নি।'
                    });
                }
            },
            error: function () {
                $btn.prop('disabled', false).removeClass('opacity-70');
                Swal.fire({
                    icon: 'error',
                    title: 'ত্রুটি',
                    text: 'সার্ভারে সংযোগে সমস্যা হয়েছে।'
                });
            }
        });
    });

    // Delete Custom Sonod
    $(document).on('click', '.deleteCustomSonodBtn', function () {
        var id = $(this).data('id');
        var title = $(this).data('title');

        Swal.fire({
            title: 'মুছে ফেলতে চান?',
            text: 'আপনি কি নিশ্চিত যে "' + title + '" সনদটি মুছে ফেলতে চান?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'হ্যাঁ, মুছুন',
            cancelButtonText: 'বাতিল'
        }).then(function (result) {
            if (result.isConfirmed) {
                $.ajax({
                    type: "post",
                    url: "deleteMyCustomSonod",
                    data: { custom_id: id },
                    success: function (res) {
                        if (res && res.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'মুছে ফেলা হয়েছে',
                                text: res.message,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(function () {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'ব্যর্থ',
                                text: res.message || 'মুছে ফেলা সম্ভব হয়নি।'
                            });
                        }
                    }
                });
            }
        });
    });
</script>
