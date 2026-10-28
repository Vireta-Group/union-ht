<?php 
    $uniq_id = $this->input->get('uniq_id'); 
?>

<!-- Breadcrumb & Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-neutral-200">
    <div>
        <nav class="flex items-center gap-2 text-xs text-neutral-500 mb-1">
            <a href="dashboard" class="hover:text-indigo-600 transition flex items-center gap-1">
                <i class="ph ph-house text-sm"></i> ড্যাশবোর্ড
            </a>
            <i class="ph ph-caret-right text-[10px] text-neutral-400"></i>
            <a href="addData" class="hover:text-indigo-600 transition">নতুন হোল্ডিং</a>
            <i class="ph ph-caret-right text-[10px] text-neutral-400"></i>
            <span class="text-neutral-800 font-medium">সফল এন্ট্রি</span>
        </nav>
        <h1 class="text-xl sm:text-2xl font-bold text-neutral-900 tracking-tight flex items-center gap-2">
            <span class="w-2.5 h-6 bg-emerald-600 rounded-full inline-block"></span>
            হোল্ডিং ডেটা এন্ট্রি সম্পন্ন হয়েছে
        </h1>
        <p class="text-xs sm:text-sm text-neutral-500 mt-0.5">নতুন হোল্ডিং ও নাগরিকের তথ্য সিস্টেমে সংরক্ষিত হয়েছে</p>
    </div>
</div>

<!-- Success Card -->
<div class="max-w-2xl mx-auto my-8">
    <div class="bg-white rounded-3xl border border-neutral-200/80 shadow-sm p-8 sm:p-12 text-center space-y-6">
        <!-- Success Icon -->
        <div class="w-20 h-20 mx-auto rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center ring-8 ring-emerald-50/50">
            <i class="ph-bold ph-check-circle text-5xl"></i>
        </div>

        <div class="space-y-2">
            <h2 class="text-2xl font-bold text-neutral-900">অভিনন্দন! এন্ট্রি সফল হয়েছে</h2>
            <p class="text-xs sm:text-sm text-neutral-500 max-w-md mx-auto leading-relaxed">
                নাগরিকের হোল্ডিং তথ্য, নির্ধারিত করের হার এবং অন্যান্য বিবরণ ডেটাবেজে সঠিকভাবে সংরক্ষিত হয়েছে।
            </p>
            <?php if (!empty($uniq_id)) { ?>
                <div class="inline-block mt-2 px-3 py-1 bg-neutral-100 rounded-lg text-xs font-mono text-neutral-600 border border-neutral-200">
                    ট্র্যাকিং আইডি: <?= htmlspecialchars($uniq_id, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php } ?>
        </div>

        <!-- Action Buttons -->
        <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-3">
            <a href="addData" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl text-xs font-bold text-neutral-700 bg-neutral-100 hover:bg-neutral-200 active:bg-neutral-300 transition">
                <i class="ph-bold ph-plus text-sm"></i>
                <span>আরেকটি নতুন এন্ট্রি করুন</span>
            </a>
            <?php if (!empty($uniq_id)) { ?>
                <a href="view?uniq_id=<?= htmlspecialchars($uniq_id, ENT_QUOTES, 'UTF-8'); ?>" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-7 py-3 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 shadow-sm transition">
                    <i class="ph-bold ph-eye text-sm"></i>
                    <span>হোল্ডিং বিস্তারিত ও প্লেট দেখুন</span>
                </a>
            <?php } ?>
        </div>
    </div>
</div>