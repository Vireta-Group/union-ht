<!-- Breadcrumb & Header -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-neutral-200/80">
    <div>
        <nav class="flex items-center gap-2 text-xs text-neutral-400 mb-1">
            <a href="dashboard" class="hover:text-indigo-600 transition-colors flex items-center gap-1">
                <i class="ph-bold ph-gauge"></i> ড্যাশবোর্ড
            </a>
            <span>/</span>
            <a href="sonod_list" class="hover:text-indigo-600 transition-colors">সনদ তালিকা</a>
            <span>/</span>
            <span class="text-indigo-600 font-semibold">সনদ সম্পাদনা</span>
        </nav>
        <h2 class="text-xl sm:text-2xl font-extrabold text-neutral-900 tracking-tight flex items-center gap-2">
            <span class="w-2.5 h-6 bg-amber-500 rounded-full inline-block"></span>
            ইস্যুকৃত সনদ তথ্য সম্পাদনা
        </h2>
        <p class="text-xs text-neutral-500">ধাপে ধাপে সনদের ভুলভ্রান্তি সংশোধন ও প্রয়োজনীয় তথ্যাদি হালনাগাদ করার ইন্টারফেস</p>
    </div>
    
    <div class="flex items-center gap-2">
        <a href="sonod_list" class="px-3.5 py-2 rounded-xl bg-neutral-100 hover:bg-neutral-200 active:scale-98 text-neutral-700 font-bold text-xs flex items-center gap-1.5 transition-all cursor-pointer">
            <i class="ph-bold ph-arrow-left text-sm"></i>
            <span>তালিকায় ফিরে যান</span>
        </a>
        <a href="psonod?id=<?= urlencode($sonod->sonod_uniqs ?? ''); ?>" target="_blank" class="px-3.5 py-2 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs flex items-center gap-1.5 transition-all">
            <i class="ph-bold ph-eye text-sm"></i>
            <span>বর্তমান সনদ ভিউ</span>
        </a>
    </div>
</div>

<!-- Flash messages -->
<?php if ($this->session->flashdata('error')): ?>
    <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm flex items-start gap-3">
        <i class="ph-bold ph-warning-circle text-rose-600 text-lg mt-0.5 shrink-0"></i>
        <div><?= htmlspecialchars($this->session->flashdata('error'), ENT_QUOTES, 'UTF-8'); ?></div>
    </div>
<?php endif; ?>
<?php if ($this->session->flashdata('success')): ?>
    <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm flex items-start gap-3">
        <i class="ph-bold ph-check-circle text-emerald-600 text-lg mt-0.5 shrink-0"></i>
        <div><?= htmlspecialchars($this->session->flashdata('success'), ENT_QUOTES, 'UTF-8'); ?></div>
    </div>
<?php endif; ?>

<!-- ROLE-BASED ACCESS STATUS CARD -->
<?php if ($can_edit): ?>
    <div class="p-4 rounded-2xl bg-indigo-50/80 border border-indigo-200/90 text-indigo-900 text-xs sm:text-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-2xs">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0 text-base shadow-xs">
                <i class="ph-bold ph-shield-check"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <strong class="font-bold text-indigo-950 text-sm">সনদ সম্পাদন সুবিধা সক্রিয়</strong>
                    <span class="px-2.5 py-0.5 rounded-full bg-indigo-200 text-indigo-800 text-[11px] font-bold"><?= htmlspecialchars($user_role_label ?? 'অনুমোদিত রোল', ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
                <p class="text-indigo-800/90 text-xs mt-0.5">
                    আপনার ইউজার রোল অনুযায়ী সনদের তথ্য সরাসরি সংশোধন ও পরিবর্তন সংরক্ষণ করার অনুমতি রয়েছে।
                </p>
            </div>
        </div>
        <span class="self-start sm:self-auto px-3 py-1 rounded-full bg-emerald-100 border border-emerald-300/80 text-emerald-800 font-bold text-xs inline-flex items-center gap-1.5 shrink-0">
            <i class="ph-bold ph-check-circle text-emerald-600"></i> সম্পাদনযোগ্য
        </span>
    </div>
<?php else: ?>
    <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-950 text-xs sm:text-sm flex items-start gap-3 shadow-2xs">
        <div class="w-8 h-8 rounded-xl bg-rose-600 text-white flex items-center justify-center shrink-0 text-base shadow-xs">
            <i class="ph-bold ph-shield-warning"></i>
        </div>
        <div>
            <strong class="font-bold text-rose-950 block text-sm">সম্পাদনার অনুমতি নেই</strong>
            <p class="text-rose-800 text-xs mt-0.5">
                আপনার বর্তমান রোল (<?= htmlspecialchars($user_role_label ?? 'সাধারণ ইউজার', ENT_QUOTES, 'UTF-8'); ?>) দিয়ে সনদ সম্পাদনা করা যাবে না। শুধুমাত্র ইউপি চেয়ারম্যান, সচিব, অ্যাডমিন বা ইউডিসি এটি সম্পাদন করতে পারবেন।
            </p>
        </div>
    </div>
<?php endif; ?>

<?php
$is_warish = !empty($warish_list) || stripos((string)($sonod->certificate_title_head_text ?? ''), 'ওয়ারিশ') !== false;
$is_trade  = !empty($trade_data) || (int)($sonod->deft_cer_aididid ?? 0) === 3 || (int)($sonod->deft_cer_aididid ?? 0) === 1230 || stripos((string)($sonod->certificate_title_head_text ?? ''), 'ট্রেড') !== false;
?>

<!-- STEPPER PROGRESS BAR COMPONENT -->
<div class="bg-white rounded-2xl border border-neutral-200/80 p-4 sm:p-5 shadow-xs">
    <div class="flex items-center justify-between relative">
        <!-- Connecting Line Background -->
        <div class="absolute left-6 right-6 top-5 sm:top-6 -translate-y-1/2 h-1 bg-neutral-100 z-0 hidden sm:block"></div>
        <div id="wizardProgressLine" class="absolute left-6 top-5 sm:top-6 -translate-y-1/2 h-1 bg-indigo-600 z-0 transition-all duration-300 hidden sm:block" style="width: 0%;"></div>

        <!-- Step 1 Tab -->
        <button type="button" onclick="goToStep(1)" id="stepTab1" class="relative z-10 flex flex-col items-center group cursor-pointer text-center focus:outline-none flex-1">
            <div id="stepCircle1" class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center font-bold text-sm shadow-md shadow-indigo-100 transition-all duration-300">
                <i class="ph-bold ph-identification-card text-lg"></i>
            </div>
            <div class="mt-2">
                <span id="stepTitle1" class="text-xs sm:text-sm font-bold text-indigo-900 block">১. সাধারণ ও নাগরিক তথ্য</span>
                <span class="text-[10px] text-neutral-400 hidden sm:block">পরিচিতি ও বিবরণ</span>
            </div>
        </button>

        <!-- Step 2 Tab -->
        <button type="button" onclick="goToStep(2)" id="stepTab2" class="relative z-10 flex flex-col items-center group cursor-pointer text-center focus:outline-none flex-1">
            <div id="stepCircle2" class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-neutral-100 text-neutral-500 border border-neutral-200 flex items-center justify-center font-bold text-sm transition-all duration-300">
                <i class="ph-bold ph-text-align-left text-lg"></i>
            </div>
            <div class="mt-2">
                <span id="stepTitle2" class="text-xs sm:text-sm font-semibold text-neutral-500 block">২. প্রত্যয়ন বক্তব্য</span>
                <span class="text-[10px] text-neutral-400 hidden sm:block">ভিজ্যুয়াল এডিটর</span>
            </div>
        </button>

        <!-- Step 3 Tab -->
        <button type="button" onclick="goToStep(3)" id="stepTab3" class="relative z-10 flex flex-col items-center group cursor-pointer text-center focus:outline-none flex-1">
            <div id="stepCircle3" class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-neutral-100 text-neutral-500 border border-neutral-200 flex items-center justify-center font-bold text-sm transition-all duration-300">
                <?php if ($is_trade): ?>
                    <i class="ph-bold ph-storefront text-lg"></i>
                <?php elseif ($is_warish): ?>
                    <i class="ph-bold ph-users-three text-lg"></i>
                <?php else: ?>
                    <i class="ph-bold ph-puzzle-piece text-lg"></i>
                <?php endif; ?>
            </div>
            <div class="mt-2">
                <span id="stepTitle3" class="text-xs sm:text-sm font-semibold text-neutral-500 block">
                    <?php if ($is_trade): ?>
                        ৩. ট্রেড লাইসেন্স
                    <?php elseif ($is_warish): ?>
                        ৩. ওয়ারিশ তালিকা
                    <?php else: ?>
                        ৩. সংযোজনী
                    <?php endif; ?>
                </span>
                <span class="text-[10px] text-neutral-400 hidden sm:block">বিষয়ভিত্তিক তথ্য</span>
            </div>
        </button>

        <!-- Step 4 Tab -->
        <button type="button" onclick="goToStep(4)" id="stepTab4" class="relative z-10 flex flex-col items-center group cursor-pointer text-center focus:outline-none flex-1">
            <div id="stepCircle4" class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-neutral-100 text-neutral-500 border border-neutral-200 flex items-center justify-center font-bold text-sm transition-all duration-300">
                <i class="ph-bold ph-check-circle text-lg"></i>
            </div>
            <div class="mt-2">
                <span id="stepTitle4" class="text-xs sm:text-sm font-semibold text-neutral-500 block">৪. চূড়ান্ত সংরক্ষণ</span>
                <span class="text-[10px] text-neutral-400 hidden sm:block">যাচাই ও অনুমোদন</span>
            </div>
        </button>
    </div>
</div>

<!-- MAIN STEPPER FORM -->
<form action="updateIssuedSonod" method="POST" enctype="multipart/form-data" id="editIssuedSonodForm" class="space-y-6">
    <input type="hidden" name="sonod_gen_ididid" value="<?= htmlspecialchars((string)($sonod->sonod_gen_ididid ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
    <input type="hidden" name="sonod_uniqs" value="<?= htmlspecialchars((string)($sonod->sonod_uniqs ?? ''), ENT_QUOTES, 'UTF-8'); ?>">

    <!-- ========================================== -->
    <!-- STEP 1: সাধারণ তথ্য ও নাগরিক পরিচিতি -->
    <!-- ========================================== -->
    <div id="wizardStep1" class="wizard-step space-y-6">
        <!-- Sub-card 1: সনদের প্রাথমিক বিবরণ -->
        <div class="bg-white rounded-2xl border border-neutral-200/80 shadow-xs overflow-hidden">
            <div class="px-5 py-4 border-b border-neutral-100 bg-neutral-50/50 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="ph-bold ph-certificate text-indigo-600 text-base"></i>
                    <h3 class="text-sm font-bold text-neutral-900">সনদের সাধারণ তথ্য ও শিরোনাম</h3>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-1 rounded-lg bg-neutral-100 text-neutral-700 text-xs font-mono font-bold">
                        সনদ নং: #<?= htmlspecialchars((string)($sonod->cgp_pr_idiidid_pr ?? '—'), ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                    <span class="px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 text-xs font-mono">
                        আইডি: <?= htmlspecialchars((string)($sonod->sonod_uniqs ?? '—'), ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                </div>
            </div>

            <div class="p-5 grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-neutral-700 mb-1.5">সনদের শিরোনাম <span class="text-rose-500">*</span></label>
                    <input 
                        type="text" 
                        name="certificate_title_head_text" 
                        id="inputCertTitle"
                        value="<?= htmlspecialchars((string)($sonod->certificate_title_head_text ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-neutral-200 bg-neutral-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 text-xs sm:text-sm font-semibold text-neutral-900"
                        <?= !$can_edit ? 'disabled' : ''; ?>
                        required
                    />
                </div>

                <div>
                    <label class="block text-xs font-bold text-neutral-700 mb-1.5">ইস্যুর তারিখ</label>
                    <div class="relative">
                        <input 
                            type="text" 
                            name="dating_stampingsss" 
                            id="inputIssueDate"
                            value="<?= htmlspecialchars((string)($sonod->dating_stampingsss ?? date('Y-m-d')), ENT_QUOTES, 'UTF-8'); ?>"
                            class="datePicks w-full px-3.5 py-2.5 pl-10 rounded-xl border border-neutral-200 bg-neutral-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 text-xs sm:text-sm text-neutral-900 font-medium cursor-pointer"
                            placeholder="YYYY-MM-DD"
                            autocomplete="off"
                            <?= !$can_edit ? 'disabled' : ''; ?>
                        />
                        <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-neutral-400 pointer-events-none">
                            <i class="ph-bold ph-calendar text-base"></i>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-neutral-700 mb-1.5">সনদের ভাষা</label>
                    <select 
                        name="en_bn_type" 
                        id="inputCertLang"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-neutral-200 bg-neutral-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 text-xs sm:text-sm text-neutral-900 font-semibold"
                        <?= !$can_edit ? 'disabled' : ''; ?>
                    >
                        <option value="1" <?= ((int)($sonod->en_bn_type ?? 1) === 1) ? 'selected' : ''; ?>>বাংলা (Bengali)</option>
                        <option value="2" <?= ((int)($sonod->en_bn_type ?? 1) === 2) ? 'selected' : ''; ?>>ইংরেজি (English)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Sub-card 2: নাগরিকের পরিচিতি ও ঠিকানা -->
        <div class="bg-white rounded-2xl border border-neutral-200/80 shadow-xs overflow-hidden">
            <div class="px-5 py-4 border-b border-neutral-100 bg-neutral-50/50 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="ph-bold ph-user text-indigo-600 text-base"></i>
                    <h3 class="text-sm font-bold text-neutral-900">নাগরিকের ব্যক্তিগত ও পারিবারিক তথ্য</h3>
                </div>
                <span class="text-xs text-neutral-400 font-medium">জাতীয় পরিচয়পত্র ও রেজিস্ট্রি বিবরণ</span>
            </div>

            <div class="p-5 space-y-4">
                <!-- Citizen Photo Upload / Preview -->
                <div class="flex flex-col sm:flex-row items-center gap-4 p-4 rounded-xl bg-neutral-50/80 border border-neutral-200">
                    <div class="w-16 h-24 rounded-xl overflow-hidden border-2 border-white shadow-xs bg-neutral-100 shrink-0 flex items-center justify-center">
                        <?php 
                        $t_id = (int)($sonod->insididid_pridididid ?? tenant_code());
                        $c_photo = FCPATH . 'uploads/tenant-' . $t_id . '/citizen-' . $sonod->sonod_uniqs . '.png';
                        $has_c_photo = file_exists($c_photo);
                        ?>
                        <img 
                            id="citizenPhotoPreview" 
                            src="<?= $has_c_photo ? (base_url('uploads/tenant-' . $t_id . '/citizen-' . $sonod->sonod_uniqs . '.png?v=' . time())) : base_url('inc/assets/himg/user_iconss.png'); ?>" 
                            alt="Citizen Photo" 
                            class="max-w-full max-h-full object-contain"
                        />
                    </div>
                    <div class="flex-1 text-center sm:text-left space-y-1">
                        <label class="block text-xs font-bold text-neutral-800">নাগরিকের পাসপোর্ট সাইজ ছবি</label>
                        <input 
                            type="file" 
                            name="citizen_photo_file" 
                            id="citizenPhotoFile"
                            accept="image/png,image/jpeg"
                            onchange="previewCitizenPhoto(this)"
                            class="text-xs text-neutral-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition cursor-pointer"
                            <?= !$can_edit ? 'disabled' : ''; ?>
                        />
                        <p class="text-[10px] text-neutral-400">নতুন ছবি নির্বাচন করলে সনদের ছবির স্থানে এটি স্বয়ংক্রিয়ভাবে প্রতিস্থাপিত হবে</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-neutral-700 mb-1.5">নাগরিকের নাম (বাংলা) <span class="text-rose-500">*</span></label>
                        <input 
                            type="text" 
                            name="ver_per_info_name_bn" 
                            id="inputNameBn"
                            value="<?= htmlspecialchars((string)($sonod->ver_per_info_name_bn ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-neutral-200 bg-neutral-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 text-xs sm:text-sm font-semibold text-neutral-900"
                            placeholder="নাগরিকের পূর্ণ নাম লিখুন"
                            <?= !$can_edit ? 'disabled' : ''; ?>
                            required
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-neutral-700 mb-1.5">নাগরিকের নাম (English)</label>
                        <input 
                            type="text" 
                            name="ver_per_info_name_en" 
                            id="inputNameEn"
                            value="<?= htmlspecialchars((string)($sonod->ver_per_info_name_en ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-neutral-200 bg-neutral-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 text-xs sm:text-sm text-neutral-900"
                            placeholder="Citizen name in English"
                            <?= !$can_edit ? 'disabled' : ''; ?>
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-neutral-700 mb-1.5">পিতার নাম (বাংলা)</label>
                        <input 
                            type="text" 
                            name="ver_per_info_father_name_bn" 
                            id="inputFatherBn"
                            value="<?= htmlspecialchars((string)($sonod->ver_per_info_father_name_bn ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-neutral-200 bg-neutral-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 text-xs sm:text-sm text-neutral-900"
                            <?= !$can_edit ? 'disabled' : ''; ?>
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-neutral-700 mb-1.5">পিতার নাম (English)</label>
                        <input 
                            type="text" 
                            name="ver_per_info_father_name_en" 
                            value="<?= htmlspecialchars((string)($sonod->ver_per_info_father_name_en ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-neutral-200 bg-neutral-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 text-xs sm:text-sm text-neutral-900"
                            <?= !$can_edit ? 'disabled' : ''; ?>
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-neutral-700 mb-1.5">মাতার নাম (বাংলা)</label>
                        <input 
                            type="text" 
                            name="ver_per_info_mother_name_bn" 
                            id="inputMotherBn"
                            value="<?= htmlspecialchars((string)($sonod->ver_per_info_mother_name_bn ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-neutral-200 bg-neutral-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 text-xs sm:text-sm text-neutral-900"
                            <?= !$can_edit ? 'disabled' : ''; ?>
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-neutral-700 mb-1.5">মাতার নাম (English)</label>
                        <input 
                            type="text" 
                            name="ver_per_info_mother_name_en" 
                            value="<?= htmlspecialchars((string)($sonod->ver_per_info_mother_name_en ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-neutral-200 bg-neutral-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 text-xs sm:text-sm text-neutral-900"
                            <?= !$can_edit ? 'disabled' : ''; ?>
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2 border-t border-neutral-100">
                    <div>
                        <label class="block text-xs font-bold text-neutral-700 mb-1.5">ওয়ার্ড নম্বর</label>
                        <select 
                            name="ver_per_info_ward_no" 
                            id="inputWardNo"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-neutral-200 bg-neutral-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 text-xs sm:text-sm text-neutral-900 font-semibold"
                            <?= !$can_edit ? 'disabled' : ''; ?>
                        >
                            <?php $curr_ward = (int)($sonod->ver_per_info_ward_no ?? 0); ?>
                            <option value="0">ওয়ার্ড নির্বাচন করুন</option>
                            <?php for ($w = 1; $w <= 9; $w++): ?>
                                <option value="<?= $w; ?>" <?= ($curr_ward === $w) ? 'selected' : ''; ?>>ওয়ার্ড ০<?= $w; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-neutral-700 mb-1.5">গ্রাম / পাড়া</label>
                        <input 
                            type="text" 
                            name="custom_village_name" 
                            id="inputVillage"
                            value="<?= htmlspecialchars((string)($sonod->custom_village_name ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-neutral-200 bg-neutral-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 text-xs sm:text-sm text-neutral-900"
                            placeholder="গ্রামের নাম"
                            <?= !$can_edit ? 'disabled' : ''; ?>
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-neutral-700 mb-1.5">বাড়ি / হোল্ডিং</label>
                        <input 
                            type="text" 
                            name="custom_house_name" 
                            id="inputHouse"
                            value="<?= htmlspecialchars((string)($sonod->custom_house_name ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-neutral-200 bg-neutral-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 text-xs sm:text-sm text-neutral-900"
                            placeholder="বাড়ির নাম"
                            <?= !$can_edit ? 'disabled' : ''; ?>
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 pt-2 border-t border-neutral-100">
                    <div>
                        <label class="block text-xs font-bold text-neutral-700 mb-1.5">ডাকঘর / পোস্ট অফিস</label>
                        <input 
                            type="text" 
                            name="post_office" 
                            id="inputPostOffice"
                            value="<?= htmlspecialchars((string)($sonod->post_office ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-neutral-200 bg-neutral-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 text-xs sm:text-sm text-neutral-900"
                            placeholder="ডাকঘরের নাম"
                            <?= !$can_edit ? 'disabled' : ''; ?>
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-neutral-700 mb-1.5">ইউনিয়ন পরিষদ</label>
                        <input 
                            type="text" 
                            name="union_name" 
                            id="inputUnionName"
                            value="<?= htmlspecialchars((string)($sonod->union_name ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-neutral-200 bg-neutral-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 text-xs sm:text-sm text-neutral-900"
                            placeholder="ইউনিয়নের নাম"
                            <?= !$can_edit ? 'disabled' : ''; ?>
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-neutral-700 mb-1.5">উপজেলা / থানা</label>
                        <input 
                            type="text" 
                            name="upazila_name" 
                            id="inputUpazilaName"
                            value="<?= htmlspecialchars((string)($sonod->upazila_name ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-neutral-200 bg-neutral-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 text-xs sm:text-sm text-neutral-900"
                            placeholder="উপজেলার নাম"
                            <?= !$can_edit ? 'disabled' : ''; ?>
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-neutral-700 mb-1.5">জেলা</label>
                        <input 
                            type="text" 
                            name="district_name" 
                            id="inputDistrictName"
                            value="<?= htmlspecialchars((string)($sonod->district_name ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-neutral-200 bg-neutral-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 text-xs sm:text-sm text-neutral-900"
                            placeholder="জেলার নাম"
                            <?= !$can_edit ? 'disabled' : ''; ?>
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 1 Navigation Controls -->
        <div class="flex items-center justify-between pt-2">
            <a href="sonod_list" class="px-5 py-2.5 rounded-xl border border-neutral-200 bg-white hover:bg-neutral-50 text-neutral-700 font-bold text-xs sm:text-sm transition">
                তালিকায় ফিরে যান
            </a>
            <button type="button" onclick="goToStep(2)" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:scale-98 text-white font-bold text-xs sm:text-sm flex items-center gap-2 shadow-xs transition-all cursor-pointer">
                <span>পরবর্তী ধাপ: প্রত্যয়ন বক্তব্য</span>
                <i class="ph-bold ph-arrow-right text-base"></i>
            </button>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- STEP 2: প্রত্যয়ন বক্তব্য ও মূল বিবরণী (TinyMCE) -->
    <!-- ========================================== -->
    <div id="wizardStep2" class="wizard-step space-y-6 hidden">
        <div class="bg-white rounded-2xl border border-neutral-200/80 shadow-xs overflow-hidden">
            <div class="px-5 py-4 border-b border-neutral-100 bg-neutral-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <i class="ph-bold ph-text-align-left text-indigo-600 text-base"></i>
                    <div>
                        <h3 class="text-sm font-bold text-neutral-900">সনদের প্রত্যয়ন বক্তব্য ও বিবরণী</h3>
                        <p class="text-[11px] text-neutral-400">এইচটিএমএল কোডের পরিবর্তে সরাসরি ভিজ্যুয়াল এডিটরে লেখা সম্পাদনা করুন</p>
                    </div>
                </div>
                <span class="self-start sm:self-auto px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 font-bold text-xs flex items-center gap-1">
                    <i class="ph-bold ph-magic-wand text-xs"></i> ভিজ্যুয়াল রিচ এডিটর
                </span>
            </div>

            <div class="p-5 space-y-3">
                <div class="p-3 rounded-xl bg-amber-50/80 border border-amber-200/80 text-amber-900 text-xs flex items-start gap-2.5">
                    <i class="ph-bold ph-info text-amber-600 text-base shrink-0 mt-0.5"></i>
                    <p class="leading-relaxed">
                        প্রিন্ট সনদের মূল প্রত্যয়ন বক্তব্য হিসেবে নিচের টেক্সটটি প্রদর্শিত হবে। কোনো র' (Raw) এইচটিএমএল কোড লেখার প্রয়োজন নেই; সরাসরি বোল্ড, আন্ডারলাইন, কালার কিংবা সাইজ পরিবর্তন করতে পারবেন।
                    </p>
                </div>

                <div class="rounded-xl border border-neutral-200 overflow-hidden shadow-2xs">
                    <textarea 
                        name="sonod_details_html" 
                        id="sonod_details_editor" 
                        rows="12"
                        class="w-full p-4 text-xs sm:text-sm text-neutral-900 font-sans leading-relaxed"
                        placeholder="এই মর্মে প্রত্যয়ন করা যাইতেছে যে..."
                        <?= !$can_edit ? 'disabled' : ''; ?>
                    ><?= htmlspecialchars((string)($sonod->sonod_details_html ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                </div>
            </div>
        </div>

        <!-- Step 2 Navigation Controls -->
        <div class="flex items-center justify-between pt-2">
            <button type="button" onclick="goToStep(1)" class="px-5 py-2.5 rounded-xl border border-neutral-200 bg-white hover:bg-neutral-50 text-neutral-700 font-bold text-xs sm:text-sm flex items-center gap-2 transition cursor-pointer">
                <i class="ph-bold ph-arrow-left text-base"></i>
                <span>পূর্ববর্তী ধাপ: সাধারণ তথ্য</span>
            </button>
            <button type="button" onclick="goToStep(3)" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:scale-98 text-white font-bold text-xs sm:text-sm flex items-center gap-2 shadow-xs transition-all cursor-pointer">
                <span>পরবর্তী ধাপ: বিষয়ভিত্তিক তথ্য</span>
                <i class="ph-bold ph-arrow-right text-base"></i>
            </button>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- STEP 3: বিষয়ভিত্তিক সংযোজনী (ট্রেড / ওয়ারিশ) -->
    <!-- ========================================== -->
    <div id="wizardStep3" class="wizard-step space-y-6 hidden">
        <?php if ($is_trade): ?>
            <!-- TRADE LICENSE SECTION -->
            <div class="bg-white rounded-2xl border border-neutral-200/80 shadow-xs overflow-hidden">
                <div class="px-5 py-4 border-b border-neutral-100 bg-neutral-50/50 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="ph-bold ph-storefront text-indigo-600 text-base"></i>
                        <h3 class="text-sm font-bold text-neutral-900">ট্রেড লাইসেন্স প্রতিষ্ঠান ও হিসাব তথ্যাবলী</h3>
                    </div>
                    <span class="text-xs text-neutral-400 font-medium">প্রতিষ্ঠান, মূলধন ও ফির হিসাব সম্পাদনা</span>
                </div>

                <div class="p-5 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-neutral-700 mb-1.5">ব্যবসা প্রতিষ্ঠানের নাম</label>
                            <input 
                                type="text" 
                                name="business_ins_name" 
                                id="trade_business_ins_name"
                                value="<?= htmlspecialchars((string)($trade_data->business_ins_name ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-neutral-200 bg-neutral-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 text-xs sm:text-sm font-bold text-neutral-900"
                                placeholder="প্রতিষ্ঠানের নাম"
                                <?= !$can_edit ? 'disabled' : ''; ?>
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-neutral-700 mb-1.5">ব্যবসার ধরণ</label>
                            <input 
                                type="text" 
                                name="business_types" 
                                value="<?= htmlspecialchars((string)($trade_data->business_types ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-neutral-200 bg-neutral-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 text-xs sm:text-sm text-neutral-900"
                                placeholder="যেমন: মুদি দোকান, স্টেশনারি"
                                <?= !$can_edit ? 'disabled' : ''; ?>
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-neutral-700 mb-1.5">ব্যবসার মূলধন (টাকা)</label>
                            <input 
                                type="text" 
                                name="muldhon" 
                                value="<?= htmlspecialchars((string)($trade_data->muldhon ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-neutral-200 bg-neutral-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 text-xs sm:text-sm text-neutral-900"
                                placeholder="মূলধন"
                                <?= !$can_edit ? 'disabled' : ''; ?>
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-neutral-700 mb-1.5">অর্থবছর</label>
                            <input 
                                type="text" 
                                name="license_yearss" 
                                value="<?= htmlspecialchars((string)($trade_data->license_yearss ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-neutral-200 bg-neutral-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 text-xs sm:text-sm text-neutral-900"
                                placeholder="যেমন: ২০২৬-২০২৭"
                                <?= !$can_edit ? 'disabled' : ''; ?>
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-neutral-700 mb-1.5">মালিকের মোবাইল নম্বর</label>
                            <input 
                                type="text" 
                                name="person_mobile_no" 
                                value="<?= htmlspecialchars((string)($trade_data->person_mobile_no ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-neutral-200 bg-neutral-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 text-xs sm:text-sm text-neutral-900"
                                placeholder="০১XXXXXXXXX"
                                <?= !$can_edit ? 'disabled' : ''; ?>
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-neutral-700 mb-1.5">ইস্যুর তারিখ</label>
                            <div class="relative">
                                <input 
                                    type="text" 
                                    name="license_issue_date" 
                                    value="<?= htmlspecialchars((string)($trade_data->license_issue_date ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                    class="datePicks w-full px-3.5 py-2.5 pl-10 rounded-xl border border-neutral-200 bg-neutral-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 text-xs sm:text-sm text-neutral-900 cursor-pointer"
                                    placeholder="YYYY-MM-DD"
                                    autocomplete="off"
                                    <?= !$can_edit ? 'disabled' : ''; ?>
                                />
                                <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-neutral-400 pointer-events-none">
                                    <i class="ph-bold ph-calendar text-base"></i>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-neutral-700 mb-1.5">মেয়াদ উত্তীর্ণের তারিখ</label>
                            <div class="relative">
                                <input 
                                    type="text" 
                                    name="license_expires_date" 
                                    value="<?= htmlspecialchars((string)($trade_data->license_expires_date ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                    class="datePicks w-full px-3.5 py-2.5 pl-10 rounded-xl border border-neutral-200 bg-neutral-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 text-xs sm:text-sm text-neutral-900 cursor-pointer"
                                    placeholder="YYYY-MM-DD"
                                    autocomplete="off"
                                    <?= !$can_edit ? 'disabled' : ''; ?>
                                />
                                <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-neutral-400 pointer-events-none">
                                    <i class="ph-bold ph-calendar text-base"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ফি এর হিসাব -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-3 border-t border-neutral-100 bg-neutral-50/70 p-4 rounded-xl">
                        <div>
                            <label class="block text-[11px] font-bold text-neutral-600 mb-1">বাৎসরিক ফি (টাকা)</label>
                            <input type="text" name="license_fee_yearly" id="trade_fee_yearly" oninput="calculateTradeTotal()" value="<?= htmlspecialchars((string)($trade_data->license_fee_yearly ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="w-full px-3 py-1.5 border border-neutral-200 rounded-lg text-xs font-semibold" <?= !$can_edit ? 'disabled' : ''; ?>>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-neutral-600 mb-1">১৫% ভ্যাট (টাকা)</label>
                            <input type="text" name="license_fee_vats" id="trade_fee_vats" oninput="calculateTradeTotal()" value="<?= htmlspecialchars((string)($trade_data->license_fee_vats ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="w-full px-3 py-1.5 border border-neutral-200 rounded-lg text-xs font-semibold" <?= !$can_edit ? 'disabled' : ''; ?>>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-neutral-600 mb-1">ব্যবসা ও পেশা কর</label>
                            <input type="text" name="pesha_bebsa_o_kor" id="trade_fee_tax" oninput="calculateTradeTotal()" value="<?= htmlspecialchars((string)($trade_data->pesha_bebsa_o_kor ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="w-full px-3 py-1.5 border border-neutral-200 rounded-lg text-xs font-semibold" <?= !$can_edit ? 'disabled' : ''; ?>>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-emerald-800 mb-1">মোট ফি (টাকা)</label>
                            <input type="text" name="total_charge" id="trade_total_charge" value="<?= htmlspecialchars((string)($trade_data->total_charge ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="w-full px-3 py-1.5 border border-emerald-300 bg-emerald-50 text-emerald-900 rounded-lg text-xs font-black" <?= !$can_edit ? 'disabled' : ''; ?>>
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif ($is_warish): ?>
            <!-- WARISH SECTION -->
            <div class="bg-white rounded-2xl border border-neutral-200/80 shadow-xs overflow-hidden">
                <div class="px-5 py-4 border-b border-neutral-100 bg-neutral-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <i class="ph-bold ph-users-three text-indigo-600 text-base"></i>
                        <div>
                            <h3 class="text-sm font-bold text-neutral-900">ওয়ারিশগণের তালিকা বিবরণী</h3>
                            <p class="text-[11px] text-neutral-400">উত্তরাধিকারীদের নাম, পিতা, সম্পর্ক ও জন্মতারিখ</p>
                        </div>
                    </div>
                    <?php if ($can_edit): ?>
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <button type="button" onclick="addWarishQuick('পুত্র', 'ছেলে')" class="px-2.5 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs flex items-center gap-1 transition cursor-pointer">
                                <i class="ph-bold ph-plus text-xs"></i> + পুত্র
                            </button>
                            <button type="button" onclick="addWarishQuick('কন্যা', 'মেয়ে')" class="px-2.5 py-1.5 rounded-lg bg-pink-50 hover:bg-pink-100 text-pink-700 font-bold text-xs flex items-center gap-1 transition cursor-pointer">
                                <i class="ph-bold ph-plus text-xs"></i> + কন্যা
                            </button>
                            <button type="button" onclick="addWarishQuick('স্ত্রী', 'স্ত্রী')" class="px-2.5 py-1.5 rounded-lg bg-purple-50 hover:bg-purple-100 text-purple-700 font-bold text-xs flex items-center gap-1 transition cursor-pointer">
                                <i class="ph-bold ph-plus text-xs"></i> + স্ত্রী
                            </button>
                            <button type="button" onclick="addWarishRow()" class="px-3 py-1.5 rounded-lg bg-neutral-800 hover:bg-black text-white font-bold text-xs flex items-center gap-1 transition cursor-pointer">
                                <i class="ph-bold ph-user-plus text-xs"></i> + অন্যান্য সদস্য
                            </button>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="p-5 overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse" id="warishTable">
                        <thead>
                            <tr class="border-b border-neutral-200 text-neutral-600 font-bold bg-neutral-50/80">
                                <th class="py-2.5 px-3">ক্রম</th>
                                <th class="py-2.5 px-3">ওয়ারিশের নাম <span class="text-rose-500">*</span></th>
                                <th class="py-2.5 px-3">পিতার নাম</th>
                                <th class="py-2.5 px-3">সম্পর্ক</th>
                                <th class="py-2.5 px-3">এনআইডি / জন্মনিবন্ধন</th>
                                <th class="py-2.5 px-3">জন্মতারিখ</th>
                                <?php if ($can_edit): ?>
                                    <th class="py-2.5 px-2 text-center w-12">মুছুন</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100" id="warishTableBody">
                            <?php if (!empty($warish_list)): ?>
                                <?php $idx = 1; foreach ($warish_list as $w): ?>
                                    <tr>
                                        <td class="py-2 px-3 text-neutral-400 font-mono font-bold text-center row-seq-no">
                                            <?= $idx++; ?>
                                        </td>
                                        <td class="py-2 px-3">
                                            <input type="text" name="warish_name[]" value="<?= htmlspecialchars((string)$w->names_per, ENT_QUOTES, 'UTF-8'); ?>" class="w-full px-2.5 py-1.5 border border-neutral-200 rounded-lg text-xs font-semibold" placeholder="নাম" <?= !$can_edit ? 'disabled' : ''; ?> required />
                                        </td>
                                        <td class="py-2 px-3">
                                            <input type="text" name="warish_father[]" value="<?= htmlspecialchars((string)$w->father_pers, ENT_QUOTES, 'UTF-8'); ?>" class="w-full px-2.5 py-1.5 border border-neutral-200 rounded-lg text-xs" placeholder="পিতা" <?= !$can_edit ? 'disabled' : ''; ?> />
                                        </td>
                                        <td class="py-2 px-3">
                                            <input type="text" name="warish_relation[]" value="<?= htmlspecialchars((string)$w->relation_pers, ENT_QUOTES, 'UTF-8'); ?>" class="w-full px-2.5 py-1.5 border border-neutral-200 rounded-lg text-xs font-medium" placeholder="সম্পর্ক" <?= !$can_edit ? 'disabled' : ''; ?> />
                                        </td>
                                        <td class="py-2 px-3">
                                            <input type="text" name="warish_nid[]" value="<?= htmlspecialchars((string)$w->nid_pers, ENT_QUOTES, 'UTF-8'); ?>" class="w-full px-2.5 py-1.5 border border-neutral-200 rounded-lg text-xs font-mono" placeholder="জাতীয় পরিচয়পত্র নং" <?= !$can_edit ? 'disabled' : ''; ?> />
                                        </td>
                                        <td class="py-2 px-3">
                                            <div class="relative">
                                                <input type="text" name="warish_dob[]" value="<?= htmlspecialchars((string)$w->dob_pers, ENT_QUOTES, 'UTF-8'); ?>" class="datePicks w-full px-2.5 py-1.5 pl-8 border border-neutral-200 rounded-lg text-xs cursor-pointer" placeholder="YYYY-MM-DD" autocomplete="off" <?= !$can_edit ? 'disabled' : ''; ?> />
                                                <i class="ph-bold ph-calendar absolute left-2.5 top-1/2 -translate-y-1/2 text-neutral-400 text-xs pointer-events-none"></i>
                                            </div>
                                        </td>
                                        <?php if ($can_edit): ?>
                                            <td class="py-2 px-2 text-center">
                                                <button type="button" onclick="removeWarishRow(this)" class="p-1 rounded-lg text-rose-500 hover:bg-rose-50 transition cursor-pointer">
                                                    <i class="ph-bold ph-trash text-sm"></i>
                                                </button>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="py-6 text-center text-neutral-400" id="noWarishMsg">
                                        কোনো ওয়ারিশের তথ্য যোগ করা হয়নি। উপরের বাটন ক্লিক করে ওয়ারিশ যোগ করুন।
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php else: ?>
            <!-- STANDARD CERTIFICATE INFO (NO SPECIAL ADDONS) -->
            <div class="bg-white rounded-2xl border border-neutral-200/80 p-8 text-center space-y-3 shadow-xs">
                <div class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-2xl mx-auto shadow-2xs">
                    <i class="ph-bold ph-check-circle"></i>
                </div>
                <h3 class="text-base font-bold text-neutral-900">অতিরিক্ত কোনো সংযোজন তথ্যাবলী প্রযোজ্য নয়</h3>
                <p class="text-xs text-neutral-500 max-w-md mx-auto leading-relaxed">
                    এই সনদটির জন্য কোনো ট্রেড লাইসেন্স বা ওয়ারিশান তথ্য প্রয়োজন নেই। সাধারণ তথ্য ও মূল প্রত্যয়ন বক্তব্য যাচাই করে সরাসরি চূড়ান্ত পর্যালোচনা ধাপে এগিয়ে যেতে পারেন।
                </p>
                <div class="pt-2">
                    <button type="button" onclick="goToStep(4)" class="px-5 py-2.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs inline-flex items-center gap-1.5 transition cursor-pointer">
                        <span>সরাসরি চূড়ান্ত পর্যালোচনায় যান</span>
                        <i class="ph-bold ph-arrow-right text-sm"></i>
                    </button>
                </div>
            </div>
        <?php endif; ?>

        <!-- Step 3 Navigation Controls -->
        <div class="flex items-center justify-between pt-2">
            <button type="button" onclick="goToStep(2)" class="px-5 py-2.5 rounded-xl border border-neutral-200 bg-white hover:bg-neutral-50 text-neutral-700 font-bold text-xs sm:text-sm flex items-center gap-2 transition cursor-pointer">
                <i class="ph-bold ph-arrow-left text-base"></i>
                <span>পূর্ববর্তী ধাপ: প্রত্যয়ন বক্তব্য</span>
            </button>
            <button type="button" onclick="goToStep(4)" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:scale-98 text-white font-bold text-xs sm:text-sm flex items-center gap-2 shadow-xs transition-all cursor-pointer">
                <span>পরবর্তী ধাপ: চূড়ান্ত পর্যালোচনা</span>
                <i class="ph-bold ph-arrow-right text-base"></i>
            </button>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- STEP 4: চূড়ান্ত পর্যালোচনা ও সংরক্ষণ -->
    <!-- ========================================== -->
    <div id="wizardStep4" class="wizard-step space-y-6 hidden">
        <!-- Review Summary Card -->
        <div class="bg-white rounded-2xl border border-neutral-200/80 shadow-xs overflow-hidden">
            <div class="px-5 py-4 border-b border-neutral-100 bg-neutral-50/50 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="ph-bold ph-check-square-offset text-indigo-600 text-base"></i>
                    <h3 class="text-sm font-bold text-neutral-900">সনদ তথ্যাবলী চূড়ান্ত পর্যালোচনা</h3>
                </div>
                <span class="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold flex items-center gap-1">
                    <i class="ph-bold ph-seal-check text-xs"></i> সংরক্ষণ প্রস্তুত
                </span>
            </div>

            <div class="p-5 space-y-5">
                <!-- Highlight Summary Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 p-4 rounded-xl bg-neutral-50/80 border border-neutral-200/80">
                    <div>
                        <span class="text-[10px] font-bold text-neutral-400 uppercase tracking-wider block">সনদের শিরোনাম</span>
                        <strong class="text-xs sm:text-sm font-bold text-neutral-900 block mt-0.5" id="revCertTitle">—</strong>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-neutral-400 uppercase tracking-wider block">নাগরিকের নাম</span>
                        <strong class="text-xs sm:text-sm font-bold text-indigo-700 block mt-0.5" id="revCitizenName">—</strong>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-neutral-400 uppercase tracking-wider block">ইস্যুর তারিখ</span>
                        <strong class="text-xs sm:text-sm font-bold text-neutral-800 block mt-0.5" id="revIssueDate">—</strong>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-neutral-400 uppercase tracking-wider block">ওয়ার্ড ও গ্রাম</span>
                        <strong class="text-xs sm:text-sm font-bold text-neutral-800 block mt-0.5" id="revWardVillage">—</strong>
                    </div>
                </div>

                <!-- Family & Identity Details Table -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div class="p-4 rounded-xl border border-neutral-100 bg-white space-y-2">
                        <h4 class="font-bold text-neutral-900 flex items-center gap-1.5 pb-1 border-b border-neutral-100">
                            <i class="ph-bold ph-user text-indigo-600"></i> নাগরিক ও পারিবারিক বিবরণ
                        </h4>
                        <div class="space-y-1 text-neutral-600">
                            <div class="flex justify-between py-1 border-b border-neutral-50">
                                <span>পিতার নাম:</span>
                                <strong class="text-neutral-900 font-semibold" id="revFatherName">—</strong>
                            </div>
                            <div class="flex justify-between py-1 border-b border-neutral-50">
                                <span>মাতার নাম:</span>
                                <strong class="text-neutral-900 font-semibold" id="revMotherName">—</strong>
                            </div>
                            <div class="flex justify-between py-1">
                                <span>বাড়ি / হোল্ডিং:</span>
                                <strong class="text-neutral-900 font-semibold" id="revHouseName">—</strong>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 rounded-xl border border-neutral-100 bg-white space-y-2">
                        <h4 class="font-bold text-neutral-900 flex items-center gap-1.5 pb-1 border-b border-neutral-100">
                            <i class="ph-bold ph-article text-indigo-600"></i> সনদের বিবরণী স্ট্যাটাস
                        </h4>
                        <div class="space-y-1 text-neutral-600">
                            <div class="flex justify-between py-1 border-b border-neutral-50">
                                <span>সনদের ভাষা:</span>
                                <strong class="text-neutral-900 font-semibold" id="revCertLang">বাংলা</strong>
                            </div>
                            <div class="flex justify-between py-1 border-b border-neutral-50">
                                <span>প্রত্যয়ন বডি টেক্সট:</span>
                                <strong class="text-emerald-700 font-semibold flex items-center gap-1">
                                    <i class="ph-bold ph-check text-xs"></i> এডিটরে সজ্জিত
                                </strong>
                            </div>
                            <div class="flex justify-between py-1">
                                <span>বিষয়ভিত্তিক সংযোজন:</span>
                                <strong class="text-neutral-900 font-semibold">
                                    <?php if ($is_trade): ?>
                                        ট্রেড লাইসেন্স ডাটা যুক্ত
                                    <?php elseif ($is_warish): ?>
                                        ওয়ারিশান তালিকা যুক্ত
                                    <?php else: ?>
                                        সাধারণ সনদ
                                    <?php endif; ?>
                                </strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Final Notice -->
                <div class="p-4 rounded-xl bg-indigo-50/70 border border-indigo-200/80 text-indigo-950 text-xs flex items-start gap-3">
                    <i class="ph-bold ph-shield-check text-indigo-600 text-lg mt-0.5 shrink-0"></i>
                    <div>
                        <strong class="font-bold block text-sm">তথ্য নিশ্চিতকরণ</strong>
                        <p class="text-indigo-900/90 mt-0.5 leading-relaxed">
                            আপনি কি নিশ্চিত যে সকল তথ্য নির্ভুলভাবে দেওয়া হয়েছে? "পরিবর্তন সংরক্ষণ করুন" বাটনে ক্লিক করলে সনদটি আপনার প্রদত্ত সংশোধিত তথ্যানুযায়ী ডাটাবেজে হালনাগাদ হবে এবং প্রিন্ট কপিতেও এই তথ্যগুলো প্রতিফলিত হবে।
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 4 Navigation & Save Controls -->
        <div class="flex items-center justify-between pt-2">
            <button type="button" onclick="goToStep(3)" class="px-5 py-2.5 rounded-xl border border-neutral-200 bg-white hover:bg-neutral-50 text-neutral-700 font-bold text-xs sm:text-sm flex items-center gap-2 transition cursor-pointer">
                <i class="ph-bold ph-arrow-left text-base"></i>
                <span>পূর্ববর্তী ধাপ: বিষয়ভিত্তিক তথ্য</span>
            </button>
            <div class="flex items-center gap-2">
                <a href="sonod_list" class="px-5 py-2.5 rounded-xl border border-neutral-200 bg-white hover:bg-neutral-50 text-neutral-700 font-bold text-xs sm:text-sm transition">
                    বাতিল
                </a>
                <?php if ($can_edit): ?>
                    <button type="submit" id="saveSonodBtn" class="px-7 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:scale-98 text-white font-bold text-xs sm:text-sm flex items-center gap-2 shadow-sm transition-all cursor-pointer">
                        <i class="ph-bold ph-floppy-disk text-base"></i>
                        <span>পরিবর্তন সংরক্ষণ করুন</span>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</form>

<!-- External vendor assets for Rich Editor (Datepicker is powered by ViretaDev custom-datepicker.css) -->
<script src="inc/assets/vendor/tinymce/js/tinymce/tinymce.js"></script>

<script>
var currentStep = 1;
var totalSteps = 4;

// Initialize ViretaDev Customized jQuery UI Datepicker (styled by custom-datepicker.css)
function initDatePicker() {
    if (typeof jQuery !== 'undefined' && typeof jQuery.fn.datepicker !== 'undefined') {
        $('.datePicks').not('.hasDatepicker').datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true,
            yearRange: "1920:+15",
            showAnim: "fadeIn"
        });
    }
}

// Initialize TinyMCE Rich Editor
function initRichEditor() {
    if (typeof tinymce !== 'undefined') {
        tinymce.init({
            selector: '#sonod_details_editor',
            menubar: false,
            toolbar: "undo redo | blocks fontfamily fontsize | bold italic underline forecolor backcolor | alignleft aligncenter alignright alignjustify | numlist bullist | removeformat | code fullscreen",
            height: 340,
            content_style: 'body { font-family: "SolaimanLipi", "Kalpurush", sans-serif; font-size: 15px; line-height: 1.8; color: #1e293b; padding: 14px; }',
            setup: function(editor) {
                editor.on('change keyup paste', function() {
                    tinymce.triggerSave();
                });
            }
        });
    }
}

// Switch between Steps
function goToStep(step) {
    if (step < 1 || step > totalSteps) return;

    // Validate required fields on Step 1 if moving forward
    if (currentStep === 1 && step > 1) {
        var titleInput = document.getElementById('inputCertTitle');
        var nameInput = document.getElementById('inputNameBn');
        if (titleInput && !titleInput.value.trim()) {
            alert('দয়া করে সনদের শিরোনাম পূরণ করুন।');
            titleInput.focus();
            return;
        }
        if (nameInput && !nameInput.value.trim()) {
            alert('দয়া করে নাগরিকের নাম (বাংলা) পূরণ করুন।');
            nameInput.focus();
            return;
        }
    }

    // Always trigger TinyMCE save so textarea is fresh
    if (typeof tinymce !== 'undefined') {
        tinymce.triggerSave();
    }

    // Hide all step sections
    for (var i = 1; i <= totalSteps; i++) {
        var el = document.getElementById('wizardStep' + i);
        if (el) el.classList.add('hidden');
    }

    // Show target step
    var targetEl = document.getElementById('wizardStep' + step);
    if (targetEl) targetEl.classList.remove('hidden');

    currentStep = step;
    var sonodKey = 'vireta_edit_step_' + (<?= json_encode($sonod->sonod_uniqs ?? ''); ?>);
    try {
        sessionStorage.setItem(sonodKey, step);
        if (window.history && window.history.replaceState) {
            window.history.replaceState(null, '', '#step' + step);
        }
    } catch (e) {}
    updateStepperUI();

    // Populate review summary on step 4
    if (step === 4) {
        syncReviewData();
    }

    // Scroll to top of wizard smoothly
    var formEl = document.getElementById('editIssuedSonodForm');
    if (formEl) {
        formEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

// Update UI Indicators for Stepper
function updateStepperUI() {
    var progressPct = ((currentStep - 1) / (totalSteps - 1)) * 100;
    var progressLine = document.getElementById('wizardProgressLine');
    if (progressLine) {
        progressLine.style.width = progressPct + '%';
    }

    for (var i = 1; i <= totalSteps; i++) {
        var circle = document.getElementById('stepCircle' + i);
        var title = document.getElementById('stepTitle' + i);

        if (i < currentStep) {
            // Completed step
            circle.className = "w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center font-bold text-sm shadow-md shadow-emerald-100 transition-all duration-300";
            circle.innerHTML = '<i class="ph-bold ph-check text-lg"></i>';
            title.className = "text-xs sm:text-sm font-bold text-emerald-800 block";
        } else if (i === currentStep) {
            // Active current step
            circle.className = "w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center font-bold text-sm shadow-md shadow-indigo-100 ring-4 ring-indigo-100 transition-all duration-300";
            title.className = "text-xs sm:text-sm font-bold text-indigo-900 block";
        } else {
            // Pending step
            circle.className = "w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-neutral-100 text-neutral-400 border border-neutral-200 flex items-center justify-center font-bold text-sm transition-all duration-300";
            title.className = "text-xs sm:text-sm font-semibold text-neutral-400 block";
        }
    }
}

// Sync Form Values into Step 4 Review Card
function syncReviewData() {
    var title = document.getElementById('inputCertTitle') ? document.getElementById('inputCertTitle').value : '—';
    var nameBn = document.getElementById('inputNameBn') ? document.getElementById('inputNameBn').value : '—';
    var date = document.getElementById('inputIssueDate') ? document.getElementById('inputIssueDate').value : '—';
    var father = document.getElementById('inputFatherBn') ? document.getElementById('inputFatherBn').value : '—';
    var mother = document.getElementById('inputMotherBn') ? document.getElementById('inputMotherBn').value : '—';
    var house = document.getElementById('inputHouse') ? document.getElementById('inputHouse').value : '—';
    var village = document.getElementById('inputVillage') ? document.getElementById('inputVillage').value : '';
    var ward = document.getElementById('inputWardNo') ? document.getElementById('inputWardNo').value : '0';
    var lang = document.getElementById('inputCertLang') ? document.getElementById('inputCertLang').value : '1';

    var wardText = (ward && ward !== '0') ? ('ওয়ার্ড ০' + ward) : 'ওয়ার্ড উল্লেখ নেই';
    var wardVillage = wardText + (village ? (', ' + village) : '');

    if (document.getElementById('revCertTitle')) document.getElementById('revCertTitle').innerText = title || '—';
    if (document.getElementById('revCitizenName')) document.getElementById('revCitizenName').innerText = nameBn || '—';
    if (document.getElementById('revIssueDate')) document.getElementById('revIssueDate').innerText = date || '—';
    if (document.getElementById('revWardVillage')) document.getElementById('revWardVillage').innerText = wardVillage;
    if (document.getElementById('revFatherName')) document.getElementById('revFatherName').innerText = father || '—';
    if (document.getElementById('revMotherName')) document.getElementById('revMotherName').innerText = mother || '—';
    if (document.getElementById('revHouseName')) document.getElementById('revHouseName').innerText = house || '—';
    if (document.getElementById('revCertLang')) document.getElementById('revCertLang').innerText = (lang == '2') ? 'ইংরেজি (English)' : 'বাংলা (Bengali)';
}

// Auto calculate Trade License Total
function calculateTradeTotal() {
    var yearly = parseFloat(document.getElementById('trade_fee_yearly') ? document.getElementById('trade_fee_yearly').value : 0) || 0;
    var vat = parseFloat(document.getElementById('trade_fee_vats') ? document.getElementById('trade_fee_vats').value : 0) || 0;
    var tax = parseFloat(document.getElementById('trade_fee_tax') ? document.getElementById('trade_fee_tax').value : 0) || 0;

    // If vat is 0 and yearly > 0, suggest 15% VAT
    var total = Math.round(yearly + vat + tax);
    var totalInput = document.getElementById('trade_total_charge');
    if (totalInput) {
        totalInput.value = total;
    }
}

// Warish row management
function addWarishQuick(relation, namePlaceholder) {
    addWarishRow('', '', relation, '', '');
}

function addWarishRow(name, father, relation, nid, dob) {
    const tbody = document.getElementById('warishTableBody');
    if (!tbody) return;

    var noMsg = document.getElementById('noWarishMsg');
    if (noMsg) {
        noMsg.closest('tr').remove();
    }

    name = name || '';
    father = father || '';
    relation = relation || '';
    nid = nid || '';
    dob = dob || '';

    const count = tbody.querySelectorAll('tr').length + 1;
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td class="py-2 px-3 text-neutral-400 font-mono font-bold text-center row-seq-no">${count}</td>
        <td class="py-2 px-3">
            <input type="text" name="warish_name[]" value="${name}" class="w-full px-2.5 py-1.5 border border-neutral-200 rounded-lg text-xs font-semibold" placeholder="নাম" required />
        </td>
        <td class="py-2 px-3">
            <input type="text" name="warish_father[]" value="${father}" class="w-full px-2.5 py-1.5 border border-neutral-200 rounded-lg text-xs" placeholder="পিতা" />
        </td>
        <td class="py-2 px-3">
            <input type="text" name="warish_relation[]" value="${relation}" class="w-full px-2.5 py-1.5 border border-neutral-200 rounded-lg text-xs font-medium" placeholder="সম্পর্ক" />
        </td>
        <td class="py-2 px-3">
            <input type="text" name="warish_nid[]" value="${nid}" class="w-full px-2.5 py-1.5 border border-neutral-200 rounded-lg text-xs font-mono" placeholder="জাতীয় পরিচয়পত্র নং" />
        </td>
        <td class="py-2 px-3">
            <div class="relative">
                <input type="text" name="warish_dob[]" value="${dob}" class="datePicks w-full px-2.5 py-1.5 pl-8 border border-neutral-200 rounded-lg text-xs cursor-pointer" placeholder="YYYY-MM-DD" autocomplete="off" />
                <i class="ph-bold ph-calendar absolute left-2.5 top-1/2 -translate-y-1/2 text-neutral-400 text-xs pointer-events-none"></i>
            </div>
        </td>
        <td class="py-2 px-2 text-center">
            <button type="button" onclick="removeWarishRow(this)" class="p-1 rounded-lg text-rose-500 hover:bg-rose-50 transition cursor-pointer">
                <i class="ph-bold ph-trash text-sm"></i>
            </button>
        </td>
    `;
    tbody.appendChild(tr);

    // Re-initialize datepicker for the new row
    initDatePicker();
    renumberWarishRows();
}

function removeWarishRow(btn) {
    const tr = btn.closest('tr');
    if (tr) {
        tr.remove();
        renumberWarishRows();
    }
}

function renumberWarishRows() {
    const rows = document.querySelectorAll('#warishTableBody tr');
    rows.forEach((row, idx) => {
        const seqEl = row.querySelector('.row-seq-no');
        if (seqEl) seqEl.textContent = idx + 1;
    });
}

function previewCitizenPhoto(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var img = document.getElementById('citizenPhotoPreview');
            if (img) img.src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// On DOM ready
document.addEventListener('DOMContentLoaded', function() {
    initDatePicker();
    initRichEditor();

    var sonodKey = 'vireta_edit_step_' + (<?= json_encode($sonod->sonod_uniqs ?? ''); ?>);
    var hashMatch = window.location.hash.match(/#step([1-4])/);
    var savedStep = hashMatch ? parseInt(hashMatch[1], 10) : parseInt(sessionStorage.getItem(sonodKey), 10);
    if (savedStep && savedStep >= 1 && savedStep <= totalSteps) {
        goToStep(savedStep);
    } else {
        updateStepperUI();
    }

    // Make sure form submit triggers TinyMCE save and clears step session
    var form = document.getElementById('editIssuedSonodForm');
    if (form) {
        form.addEventListener('submit', function() {
            try { sessionStorage.removeItem(sonodKey); } catch (e) {}
            if (typeof tinymce !== 'undefined') {
                tinymce.triggerSave();
            }
        });
    }
});
</script>
