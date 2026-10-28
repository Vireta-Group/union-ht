<!-- Breadcrumb & Header -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-neutral-200/80">
    <div>
        <nav class="flex items-center gap-2 text-xs text-neutral-400 mb-1">
            <a href="dashboard" class="hover:text-indigo-600 transition-colors flex items-center gap-1">
                <i class="ph-bold ph-gauge"></i> ড্যাশবোর্ড
            </a>
            <span>/</span>
            <span class="text-neutral-500">সনদ ব্যবস্থাপনা</span>
            <span>/</span>
            <span class="text-indigo-600 font-semibold">খসড়া সনদ তালিকা</span>
        </nav>
        <h2 class="text-xl sm:text-2xl font-extrabold text-neutral-900 tracking-tight flex items-center gap-2">
            <span class="w-2.5 h-6 bg-amber-500 rounded-full inline-block"></span>
            সংরক্ষিত খসড়া সনদ তালিকা (Drafts)
        </h2>
        <p class="text-xs text-neutral-500">অসম্পূর্ণ বা প্রক্রিয়াধীন সনদের সংরক্ষিত খসড়া সমূহ। এখান থেকে যেকোনো সময় পুনরায় কাজ শুরু বা সম্পূর্ণ করা যাবে।</p>
    </div>
    
    <div class="flex items-center gap-2">
        <a href="newSonod" class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:scale-98 text-white font-bold text-xs flex items-center gap-1.5 shadow-2xs transition-all cursor-pointer">
            <i class="ph-bold ph-plus-circle text-sm"></i>
            <span>নতুন সনদ প্রদান করুন</span>
        </a>
    </div>
</div>

<!-- Metric Stats Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div class="bg-white rounded-2xl border border-neutral-200/80 shadow-xs p-4 flex items-center gap-3">
        <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
            <i class="ph-bold ph-note-pencil text-2xl"></i>
        </div>
        <div>
            <span class="text-neutral-500 text-[11px] font-medium block">মোট সংরক্ষিত খসড়া</span>
            <div class="text-xl font-black text-neutral-900 leading-tight">
                <?= bn_num(count($drafts_list ?? [])); ?> টি
            </div>
            <span class="text-[10px] text-neutral-400">প্রক্রিয়াধীন সনদ</span>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-neutral-200/80 shadow-xs p-4 flex items-center gap-3">
        <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
            <i class="ph-bold ph-clock-counter-clockwise text-2xl"></i>
        </div>
        <div>
            <span class="text-neutral-500 text-[11px] font-medium block">পুনরুদ্ধার সুবিধা</span>
            <div class="text-base font-bold text-neutral-900 leading-tight">
                লাইভ ড্রাফট রিকভারি
            </div>
            <span class="text-[10px] text-neutral-400">যেকোনো ডিভাইস বা ব্রাউজার থেকে সরাসরি ওপেন</span>
        </div>
    </div>
</div>

<!-- Drafts Table Card -->
<div class="bg-white rounded-2xl border border-neutral-200/80 shadow-xs overflow-hidden">
    <div class="p-4 border-b border-neutral-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-xl bg-neutral-100 text-neutral-700 flex items-center justify-center">
                <i class="ph-bold ph-files text-base"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold text-neutral-900">খসড়া সনদের তালিকা</h3>
                <p class="text-[11px] text-neutral-400">সর্বশেষ আপডেট অনুযায়ী সাজানো</p>
            </div>
        </div>

        <div class="w-full sm:w-64">
            <div class="relative">
                <input type="text" id="draftSearchInput" placeholder="আবেদনকারী বা NID দিয়ে খুঁজুন..." class="w-full text-xs pl-8 pr-3 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                <i class="ph-bold ph-magnifying-glass absolute left-2.5 top-2.5 text-neutral-400 text-xs"></i>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-neutral-50/80 border-b border-neutral-200/60 text-neutral-500 font-bold uppercase tracking-wider text-[10px]">
                    <th class="py-3 px-4">আইডি</th>
                    <th class="py-3 px-4">সনদের শিরোনাম</th>
                    <th class="py-3 px-4">আবেদনকারী</th>
                    <th class="py-3 px-4">NID / জন্ম নিবন্ধন</th>
                    <th class="py-3 px-4 text-center">সর্বশেষ ধাপ</th>
                    <th class="py-3 px-4">সংরক্ষিত সময়</th>
                    <th class="py-3 px-4 text-right">অ্যাকশন</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-100 draft-table-body">
                <?php if (!empty($drafts_list)): ?>
                    <?php foreach ($drafts_list as $draft): ?>
                        <tr class="hover:bg-neutral-50/60 transition-colors draft-row" data-id="<?= $draft->id; ?>">
                            <td class="py-3 px-4 font-mono font-bold text-neutral-600">
                                #<?= $draft->id; ?>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-neutral-900"><?= htmlspecialchars($draft->draft_title ?: 'খসড়া সনদ', ENT_QUOTES, 'UTF-8'); ?></div>
                                <div class="text-[10px] text-neutral-400">ধাপ <?= bn_num($draft->step_number); ?> সম্পন্ন</div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-semibold text-neutral-800"><?= htmlspecialchars($draft->applicant_name ?: 'অনির্দিষ্ট', ENT_QUOTES, 'UTF-8'); ?></span>
                            </td>
                            <td class="py-3 px-4 font-mono text-neutral-600">
                                <?= htmlspecialchars($draft->applicant_nid_brn ?: '—', ENT_QUOTES, 'UTF-8'); ?>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    <i class="ph-bold ph-spinner-gap"></i>
                                    ধাপ <?= bn_num($draft->step_number); ?>
                                </span>
                            </td>
                            <td class="py-3 px-4 text-neutral-500 text-[11px]">
                                <?= date('d M Y, h:i A', $draft->updated_at); ?>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="newSonod?draft_id=<?= $draft->id; ?>" class="px-2.5 py-1.5 rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 font-bold text-xs transition flex items-center gap-1 cursor-pointer" title="সম্পূর্ণ করুন">
                                        <i class="ph-bold ph-pencil-simple"></i>
                                        <span>সম্পূর্ণ করুন</span>
                                    </a>
                                    <button type="button" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 flex items-center justify-center transition cursor-pointer delete-draft-btn" data-id="<?= $draft->id; ?>" title="মুছে ফেলুন">
                                        <i class="ph-bold ph-trash text-sm"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="py-12 text-center text-neutral-400">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <i class="ph-bold ph-folder-open text-3xl text-neutral-300"></i>
                                <span class="text-xs font-semibold">কোনো সংরক্ষিত খসড়া নেই</span>
                                <span class="text-[11px] text-neutral-400">সনদ তৈরি করার সময় স্বয়ংক্রিয়ভাবে খসড়া এখানে তালিকাভুক্ত হবে।</span>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Sweetalert2 -->
<script src="inc/assets/vendor/sweetalert2/dist/sweetalert2.all.min.js"></script>

<script>
    // Search Filter
    $('#draftSearchInput').on('keyup', function () {
        var term = $(this).val().toLowerCase();
        $('.draft-row').each(function () {
            var text = $(this).text().toLowerCase();
            $(this).toggle(text.indexOf(term) > -1);
        });
    });

    // Delete Draft Action
    $(document).on('click', '.delete-draft-btn', function () {
        var id = $(this).data('id');
        var $row = $(this).closest('tr');

        Swal.fire({
            title: '<strong>খসড়া মুছে ফেলতে চান?</strong>',
            text: 'এই খসড়াটি সম্পূর্ণ মুছে ফেলা হবে এবং পুনরুদ্ধার করা যাবে না।',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'হ্যাঁ, মুছুন',
            cancelButtonText: 'বাতিল',
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#64748b'
        }).then(function (result) {
            if (result.isConfirmed) {
                $.ajax({
                    type: "POST",
                    url: "deleteSonodDraft",
                    data: { id: id },
                    success: function (res) {
                        if (res && res.status === 'success') {
                            $row.fadeOut(300, function () { $(this).remove(); });
                            Swal.fire({
                                icon: 'success',
                                title: 'মুছে ফেলা হয়েছে',
                                text: res.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire('ত্রুটি', (res && res.message) || 'মুছতে ব্যর্থ হয়েছে', 'error');
                        }
                    },
                    error: function () {
                        Swal.fire('ত্রুটি', 'সার্ভারে যোগাযোগ করা যায়নি', 'error');
                    }
                });
            }
        });
    });
</script>
