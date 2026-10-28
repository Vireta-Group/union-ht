<!-- Breadcrumb & Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-neutral-200">
    <div>
        <nav class="flex items-center gap-2 text-xs text-neutral-500 mb-1">
            <a href="dashboard" class="hover:text-indigo-600 transition flex items-center gap-1">
                <i class="ph ph-house text-sm"></i> ড্যাশবোর্ড
            </a>
            <i class="ph ph-caret-right text-[10px] text-neutral-400"></i>
            <span class="text-neutral-800 font-medium">সনদপত্র সেটিংস</span>
        </nav>
        <h1 class="text-xl sm:text-2xl font-bold text-neutral-900 tracking-tight flex items-center gap-2">
            <span class="w-2.5 h-6 bg-indigo-600 rounded-full inline-block"></span>
            সনদপত্রের শিরোনাম ও টেক্সট সেটিংস
        </h1>
        <p class="text-xs sm:text-sm text-neutral-500 mt-0.5">সনদ/প্রত্যয়নপত্রের উপরে, নিচে, ডানে ও বামে প্রদর্শিত টেক্সট কনফিগার করুন</p>
    </div>
</div>

<form action="dashboard/update_plate_set" enctype="multipart/form-data" method="post" class="space-y-6">
    <div class="bg-white rounded-2xl border border-neutral-200/80 shadow-sm p-5 sm:p-6 space-y-5">
        <div class="flex items-center gap-2 pb-3 border-b border-neutral-100">
            <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                <i class="ph-bold ph-file-text text-base"></i>
            </div>
            <h3 class="text-sm font-bold text-neutral-900">সনদ টেক্সট বিন্যাস</h3>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-neutral-700 mb-1.5">উপরের টেক্সট</label>
                <textarea name="up_text" rows="5" class="w-full text-xs px-3.5 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition resize-none" placeholder="উপরের টেক্সট"><?php echo htmlspecialchars($ins_info->plae_up_text ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>
            <div>
                <label class="block text-xs font-semibold text-neutral-700 mb-1.5">নিচের টেক্সট</label>
                <textarea name="down_text" rows="5" class="w-full text-xs px-3.5 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition resize-none" placeholder="নিচের টেক্সট"><?php echo htmlspecialchars($ins_info->plae_down_text ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>
            <div>
                <label class="block text-xs font-semibold text-neutral-700 mb-1.5">ডানের টেক্সট</label>
                <textarea name="danp_text" rows="5" class="w-full text-xs px-3.5 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition resize-none" placeholder="ডানের টেক্সট"><?php echo htmlspecialchars($ins_info->plae_right_text ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>
            <div>
                <label class="block text-xs font-semibold text-neutral-700 mb-1.5">বামের টেক্সট</label>
                <textarea name="bamp_text" rows="5" class="w-full text-xs px-3.5 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition resize-none" placeholder="বামের টেক্সট"><?php echo htmlspecialchars($ins_info->plae_left_text ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>
        </div>
    </div>

    <div class="flex justify-end">
        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 shadow-sm transition">
            <i class="ph-bold ph-floppy-disk text-base"></i>
            <span>পরিবর্তন সংরক্ষণ করুন</span>
        </button>
    </div>
</form>
