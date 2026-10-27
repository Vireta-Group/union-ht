<section class="py-10 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- Left: Chairman Vision (5 cols) -->
            <div class="lg:col-span-5">
                <div class="ht-card p-6 sm:p-7 border border-neutral-200 bg-white shadow-sm relative overflow-hidden">
                    <div class="flex items-center gap-3 pb-4 border-b border-neutral-100">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-200 flex items-center justify-center text-indigo-700 text-xl">
                            <i class="ph ph-megaphone"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-neutral-900">চেয়ারম্যানের বার্তা</h3>
                            <p class="text-xs text-neutral-500 font-medium"><?= htmlspecialchars($setting->cm_name_bn ?? 'ইউপি চেয়ারম্যান', ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                    </div>

                    <div class="py-4 text-xs sm:text-sm text-neutral-600 leading-relaxed text-justify space-y-2">
                        <p>
                            <?= excerpt($setting->chairman_speach ?? 'জনগণের সেবা নিশ্চিতকরণ এবং ডিজিটাল বাংলাদেশ গড়ার প্রত্যয়ে আমাদের ইউনিয়ন পরিষদ নিরলসভাবে কাজ করে যাচ্ছে।', 320); ?>
                        </p>
                    </div>

                    <div class="pt-3 border-t border-neutral-100 flex justify-between items-center">
                        <span class="text-[11px] text-neutral-400">সর্বশেষ হালনাগাদ তথ্য</span>
                        <button type="button" 
                                data-toggle="modal" 
                                data-target="#CMDScribeModel"
                                class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 flex items-center gap-1 group">
                            <span>সম্পূর্ণ পড়ুন</span>
                            <i class="ph ph-arrow-right text-xs group-hover:translate-x-1 transition-transform"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right: Union Parishad Information & Basic Overview (7 cols) -->
            <div class="lg:col-span-7">
                <div class="ht-card p-6 sm:p-7 border border-neutral-200 bg-white shadow-sm relative">
                    <div class="flex items-center gap-3 pb-4 border-b border-neutral-100">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 border border-amber-200 flex items-center justify-center text-amber-700 text-xl">
                            <i class="ph ph-buildings"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-neutral-900">ইউনিয়ন পরিষদ পরিচিতি ও তথ্য</h3>
                            <p class="text-xs text-neutral-500 font-medium"><?= htmlspecialchars($setting->ins_name ?? '', ENT_QUOTES, 'UTF-8'); ?> বাতায়ন</p>
                        </div>
                    </div>

                    <div class="py-4 text-xs sm:text-sm text-neutral-600 leading-relaxed text-justify space-y-2">
                        <p>
                            <?= excerpt($setting->basic_infoss ?? 'আমাদের ইউনিয়ন পরিষদ দেশের একটি ঐতিহ্যবাহী অঞ্চল। কৃষি, শিক্ষা ও সংস্কৃতির পাশাপাশি ডিজিটাল নাগরিক সেবায় এই ইউনিয়ন পরিষদ অগ্রণী ভূমিকা পালন করে আসছে।', 420); ?>
                        </p>
                    </div>

                    <div class="pt-3 border-t border-neutral-100 flex justify-between items-center">
                        <div class="flex items-center gap-3 text-xs text-neutral-500">
                            <?php if (!empty($setting->socib_phone)): ?>
                                <span class="flex items-center gap-1">
                                    <i class="ph ph-phone text-amber-500 text-sm"></i> <?= htmlspecialchars($setting->socib_phone, ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <button type="button" 
                                data-toggle="modal" 
                                data-target="#discribeModel"
                                class="text-xs font-semibold text-amber-700 hover:text-amber-900 flex items-center gap-1 group">
                            <span>বিস্তারিত পরিচিতি</span>
                            <i class="ph ph-arrow-right text-xs group-hover:translate-x-1 transition-transform"></i>
                        </button>
                    </div>
                </div>
            </div>

        </div>

    </div>
</section>

<!-- Modals for Full Information -->
<div class="modal fade" id="discribeModel" tabindex="-1" aria-labelledby="discribeModelLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content rounded-2xl border-0 shadow-xl">
            <div class="modal-header bg-neutral-50 border-b border-neutral-200 px-6 py-4 rounded-t-2xl">
                <h5 class="modal-title text-base font-bold text-neutral-900 flex items-center gap-2" id="discribeModelLabel">
                    <i class="ph ph-buildings text-indigo-600 text-lg"></i>
                    <span>ইউনিয়ন পরিষদ পরিচিতি ও তথ্য</span>
                </h5>
                <button type="button" class="close text-neutral-400 hover:text-neutral-700" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-6 text-sm text-neutral-700 leading-relaxed space-y-4">
                <?= nl2br($setting->basic_infoss ?? 'কোনো অতিরিক্ত তথ্য পাওয়া যায়নি।'); ?>
            </div>
            <div class="modal-footer bg-neutral-50 border-t border-neutral-100 px-6 py-3 rounded-b-2xl">
                <button type="button" class="ht-btn ht-btn-secondary text-xs" data-dismiss="modal">বন্ধ করুন</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="CMDScribeModel" tabindex="-1" aria-labelledby="CMDScribeModelLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content rounded-2xl border-0 shadow-xl">
            <div class="modal-header bg-neutral-50 border-b border-neutral-200 px-6 py-4 rounded-t-2xl">
                <h5 class="modal-title text-base font-bold text-neutral-900 flex items-center gap-2" id="CMDScribeModelLabel">
                    <i class="ph ph-megaphone text-indigo-600 text-lg"></i>
                    <span>চেয়ারম্যানের সম্পূর্ণ বার্তা</span>
                </h5>
                <button type="button" class="close text-neutral-400 hover:text-neutral-700" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-6 text-sm text-neutral-700 leading-relaxed space-y-4">
                <?= nl2br($setting->chairman_speach ?? 'কোনো বার্তা সংরক্ষিত নেই।'); ?>
            </div>
            <div class="modal-footer bg-neutral-50 border-t border-neutral-100 px-6 py-3 rounded-b-2xl">
                <button type="button" class="ht-btn ht-btn-secondary text-xs" data-dismiss="modal">বন্ধ করুন</button>
            </div>
        </div>
    </div>
</div>
