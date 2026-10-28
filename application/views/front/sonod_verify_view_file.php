<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * sonod_verify_view_file — Official Online Certificate Verification View
 *
 * Provides a government-grade, public certificate authentication portal
 * branded with the issuing Union Parishad. Displays full beneficiary
 * profile, official seal, issuance date, verification QR code, and print actions.
 */

$page_title = $page_title ?? 'সনদপত্র অনলাইন যাচাই';
$page_desc  = $page_desc ?? 'ডিজিটাল ইউনিয়ন পরিষদ প্ল্যাটফর্মে ইস্যুকৃত সনদপত্রের সরকারি অনলাইন সত্যতা যাচাই';
$view_data  = ($this->data ?? get_defined_vars());
$view_data['page_title'] = $page_title;
$view_data['page_desc']  = $page_desc;

$this->load->view('front/partials/header', $view_data);
$this->load->view('front/partials/navbar', $view_data);

// Format helper
$fmt_en2bn = function_exists('en2bn') ? 'en2bn' : function($v) { return $v; };

$sonod_title = !empty($sonod->certificate_title_head_text) ? $sonod->certificate_title_head_text : (!empty($sonod->certify_titles) ? $sonod->certify_titles : 'নাগরিকত্ব সনদপত্র');
$sonod_num   = !empty($sonod->cgp_pr_idiidid_pr) ? (string)$sonod->cgp_pr_idiidid_pr : (!empty($sonod->sonod_uniqs) ? $sonod->sonod_uniqs : '');
$issue_stamp = !empty($sonod->timeing_stmpsss) ? (int)$sonod->timeing_stmpsss : (!empty($sonod->dating_stampingsss) ? strtotime($sonod->dating_stampingsss) : time());
$issue_date  = date('d/m/Y', $issue_stamp);

$up_name     = !empty($sonod->ins_name) ? $sonod->ins_name : (!empty($setting->ins_name) ? $setting->ins_name : 'ইউনিয়ন পরিষদ');
$up_upazila  = !empty($sonod->up_bn_name) ? $sonod->up_bn_name : (!empty($setting->up_bn_name) ? $setting->up_bn_name : '');
$up_dist     = !empty($sonod->dist_bn_name) ? $sonod->dist_bn_name : (!empty($setting->dist_bn_name) ? $setting->dist_bn_name : '');
$cm_name     = !empty($sonod->cm_name_bn) ? $sonod->cm_name_bn : (!empty($setting->cm_name_bn) ? $setting->cm_name_bn : 'ইউপি চেয়ারম্যান');
$sec_phone   = !empty($sonod->socib_phone) ? $sonod->socib_phone : (!empty($setting->socib_phone) ? $setting->socib_phone : '');

$citizen_name = !empty($sonod->ver_per_info_name_bn) ? $sonod->ver_per_info_name_bn : (!empty($sonod->user_person_name) ? $sonod->user_person_name : '');
$father_name  = !empty($sonod->ver_per_info_father_name_bn) ? $sonod->ver_per_info_father_name_bn : '';
$mother_name  = !empty($sonod->ver_per_info_mother_name_bn) ? $sonod->ver_per_info_mother_name_bn : '';
$ward_no      = isset($sonod->ver_per_info_ward_no) && $sonod->ver_per_info_ward_no !== '' ? $sonod->ver_per_info_ward_no : (!empty($sonod->ins_wardno) ? $sonod->ins_wardno : '');
$nid_no       = !empty($sonod->nid_num10) ? $sonod->nid_num10 : (!empty($sonod->nid_no17) ? $sonod->nid_no17 : (!empty($sonod->user_nid_no) ? (string)$sonod->user_nid_no : ''));

$uniq_hash    = !empty($sonod->sonod_uniqs) ? $sonod->sonod_uniqs : '';
?>

<style>
@media print {
    header, nav, footer, .no-print-area, #backToTopBtn {
        display: none !important;
    }
    body {
        background: #ffffff !important;
        color: #000000 !important;
        font-size: 12pt !important;
    }
    .print-sheet {
        box-shadow: none !important;
        border: 2px solid #000000 !important;
        margin: 0 !important;
        padding: 20px !important;
        max-width: 100% !important;
        width: 100% !important;
    }
}
</style>

<main class="min-h-screen bg-slate-50/70 py-8 sm:py-12">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Breadcrumb & Top Bar -->
        <div class="no-print-area flex flex-wrap items-center justify-between gap-3 mb-6">
            <div class="flex items-center gap-2 text-xs text-neutral-500">
                <a href="<?= base_url() ?>" class="hover:text-neutral-900 transition-colors flex items-center gap-1">
                    <i class="ph-bold ph-house"></i>
                    <span>হোম</span>
                </a>
                <span>/</span>
                <span class="text-neutral-700 font-medium">সনদপত্র অনলাইন যাচাই</span>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" onclick="window.print()" class="ht-btn ht-btn-secondary text-xs py-1.5 px-3">
                    <i class="ph-bold ph-printer"></i>
                    <span>যাচাই স্লিপ প্রিন্ট</span>
                </button>
            </div>
        </div>

        <?php if ($is_verified && $sonod): ?>
            <!-- ============================================================ -->
            <!-- VERIFIED AUTHENTIC CERTIFICATE VIEW                          -->
            <!-- ============================================================ -->
            <div class="print-sheet bg-white rounded-3xl border border-neutral-200/90 shadow-xl shadow-neutral-900/5 overflow-hidden">
                
                <!-- Verification Status Header Strip -->
                <div class="verify-status-banner">
                    <div class="relative z-10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-3xl shadow-inner shrink-0" style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.35); color: #ffffff;">
                                <i class="ph-bold ph-seal-check text-white"></i>
                            </div>
                            <div>
                                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold tracking-wide uppercase mb-1.5" style="background: rgba(16, 185, 129, 0.35); border: 1px solid rgba(110, 231, 183, 0.45); color: #ecfdf5;">
                                    <span class="w-2 h-2 rounded-full animate-pulse" style="background: #6ee7b7;"></span>
                                    <span>সরকারি ডাটাবেজে সক্রিয় ও বৈধ</span>
                                </div>
                                <h1 class="text-xl sm:text-2xl font-bold tracking-tight" style="color: #ffffff !important; text-shadow: 0 1px 2px rgba(0,0,0,0.15);">
                                    <?= htmlspecialchars($sonod_title, ENT_QUOTES, 'UTF-8') ?>
                                </h1>
                                <p class="text-xs mt-1" style="color: #d1fae5 !important;">
                                    ইস্যুকারী কর্তৃপক্ষ: <?= htmlspecialchars($up_name, ENT_QUOTES, 'UTF-8') ?>
                                    <?php if (!empty($up_upazila)): ?> | উপজেলা: <?= htmlspecialchars($up_upazila, ENT_QUOTES, 'UTF-8') ?><?php endif; ?>
                                    <?php if (!empty($up_dist)): ?>, জেলা: <?= htmlspecialchars($up_dist, ENT_QUOTES, 'UTF-8') ?><?php endif; ?>
                                </p>
                            </div>
                        </div>

                        <div class="sm:text-right rounded-2xl px-4 py-3 self-stretch sm:self-auto" style="background: rgba(0, 0, 0, 0.22); border: 1px solid rgba(255, 255, 255, 0.25);">
                            <span class="block text-[11px] uppercase font-bold tracking-wider" style="color: #a7f3d0 !important;">সনদ ট্র্যাকিং নম্বর</span>
                            <div class="flex items-center justify-between sm:justify-end gap-2.5 mt-1">
                                <span class="text-lg font-extrabold tracking-wider font-mono" style="color: #ffffff !important;"><?= htmlspecialchars($sonod_num, ENT_QUOTES, 'UTF-8') ?></span>
                                <button type="button" onclick="copySonodNumber('<?= addslashes($sonod_num) ?>', this)"
                                        class="no-print-area p-1.5 rounded-lg hover:bg-white/20 transition-all inline-flex items-center gap-1 cursor-pointer"
                                        style="color: #a7f3d0;" title="কপি করুন">
                                    <i class="ph-bold ph-copy text-sm copy-btn-icon"></i>
                                    <span class="copy-btn-text text-[10px] font-bold text-emerald-200 hidden">কপি হয়েছে!</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Verification Body Content -->
                <div class="p-6 sm:p-8 space-y-8">
                    
                    <!-- Quick Meta Bar -->
                    <div class="verify-meta-grid p-4 rounded-2xl bg-neutral-50 border border-neutral-200/80 text-xs">
                        <div>
                            <span class="text-neutral-400 block text-[11px]">ইস্যুর তারিখ</span>
                            <span class="font-bold text-neutral-800"><?= $fmt_en2bn($issue_date) ?> ইং</span>
                        </div>
                        <div>
                            <span class="text-neutral-400 block text-[11px]">অনুমোদন স্ট্যাটাস</span>
                            <span class="font-bold text-emerald-700 flex items-center gap-1">
                                <i class="ph-fill ph-check-circle text-emerald-600"></i>
                                <span>অনুমোদিত ও স্বাক্ষরিত</span>
                            </span>
                        </div>
                        <div>
                            <span class="text-neutral-400 block text-[11px]">স্বাক্ষরকারী</span>
                            <span class="font-bold text-neutral-800"><?= htmlspecialchars($cm_name, ENT_QUOTES, 'UTF-8') ?> (চেয়ারম্যান)</span>
                        </div>
                        <div>
                            <span class="text-neutral-400 block text-[11px]">যাচাইকরণের সময়</span>
                            <span class="font-bold text-neutral-800"><?= $fmt_en2bn(date('d/m/Y h:i A')) ?></span>
                        </div>
                    </div>

                    <!-- Citizen / Beneficiary Profile Information -->
                    <div>
                        <div class="flex items-center gap-2 mb-4 pb-2 border-b border-neutral-200">
                            <i class="ph-bold ph-user-circle text-lg text-indigo-600"></i>
                            <h2 class="text-sm sm:text-base font-bold text-neutral-900">নাগরিক / প্রত্যয়নপ্রাপ্ত ব্যক্তির তথ্যাবলী</h2>
                        </div>

                        <div class="verify-profile-grid">
                            <div class="p-4 rounded-2xl border border-neutral-200/80 bg-white space-y-3 text-xs">
                                <div class="flex justify-between items-center py-1 border-b border-neutral-100">
                                    <span class="text-neutral-500 font-medium">নাগরিকের নাম (বাংলা)</span>
                                    <span class="font-bold text-neutral-900 text-sm"><?= htmlspecialchars($citizen_name ?: 'তথ্য সংরক্ষিত', ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                                <?php if (!empty($sonod->ver_per_info_name_en)): ?>
                                    <div class="flex justify-between items-center py-1 border-b border-neutral-100">
                                        <span class="text-neutral-500 font-medium">নাগরিকের নাম (English)</span>
                                        <span class="font-semibold text-neutral-800"><?= htmlspecialchars($sonod->ver_per_info_name_en, ENT_QUOTES, 'UTF-8') ?></span>
                                    </div>
                                <?php endif; ?>
                                <div class="flex justify-between items-center py-1 border-b border-neutral-100">
                                    <span class="text-neutral-500 font-medium">পিতা / স্বামীর নাম</span>
                                    <span class="font-bold text-neutral-800"><?= htmlspecialchars($father_name ?: '—', ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                                <div class="flex justify-between items-center py-1">
                                    <span class="text-neutral-500 font-medium">মাতার নাম</span>
                                    <span class="font-bold text-neutral-800"><?= htmlspecialchars($mother_name ?: '—', ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                            </div>

                            <div class="p-4 rounded-2xl border border-neutral-200/80 bg-white space-y-3 text-xs">
                                <div class="flex justify-between items-center py-1 border-b border-neutral-100">
                                    <span class="text-neutral-500 font-medium">ওয়ার্ড নম্বর</span>
                                    <span class="font-bold text-neutral-900">ওয়ার্ড নং <?= $fmt_en2bn($ward_no !== '' ? $ward_no : '১') ?></span>
                                </div>
                                <?php if (!empty($nid_no)): ?>
                                    <div class="flex justify-between items-center py-1 border-b border-neutral-100">
                                        <span class="text-neutral-500 font-medium">জাতীয় পরিচয়পত্র (NID)</span>
                                        <span class="font-mono font-bold text-neutral-800"><?= htmlspecialchars($nid_no, ENT_QUOTES, 'UTF-8') ?></span>
                                    </div>
                                <?php endif; ?>
                                <div class="flex justify-between items-center py-1 border-b border-neutral-100">
                                    <span class="text-neutral-500 font-medium">ইউনিয়ন পরিষদ</span>
                                    <span class="font-bold text-neutral-800"><?= htmlspecialchars($up_name, ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                                <div class="flex justify-between items-center py-1">
                                    <span class="text-neutral-500 font-medium">উপজেলা ও জেলা</span>
                                    <span class="font-bold text-neutral-800"><?= htmlspecialchars($up_upazila, ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars($up_dist, ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Certificate Content Body (Official Paragraph) -->
                    <?php if (!empty($sonod->sonod_details_html)): ?>
                        <div>
                            <div class="flex items-center gap-2 mb-3 pb-2 border-b border-neutral-200">
                                <i class="ph-bold ph-quotes text-lg text-emerald-600"></i>
                                <h2 class="text-sm sm:text-base font-bold text-neutral-900">সনদের মূল বিবরণ ও প্রত্যয়ন অনুচ্ছেদ</h2>
                            </div>
                            <div class="p-5 rounded-2xl bg-amber-50/40 border border-amber-200/70 text-neutral-800 text-sm leading-relaxed font-serif">
                                <?= $sonod->sonod_details_html ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- QR Code & Digital Security Seal Section -->
                    <div class="flex flex-col sm:flex-row items-center gap-6 p-6 rounded-2xl bg-neutral-50/80 border border-neutral-200/90 text-neutral-800">
                        <div class="shrink-0 flex justify-center">
                            <div class="p-3 bg-white rounded-2xl border border-neutral-200 shadow-sm">
                                <?php if (!empty($qr_code)): ?>
                                    <img src="data:image/png;base64,<?= $qr_code ?>" alt="Verification QR" class="w-32 h-32 block">
                                <?php else: ?>
                                    <div class="w-32 h-32 bg-neutral-100 flex items-center justify-center text-neutral-400">
                                        <i class="ph-bold ph-qr-code text-4xl"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0 space-y-2 text-xs">
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 font-bold uppercase tracking-wider text-[11px]">
                                <i class="ph-bold ph-shield-check text-sm text-emerald-600"></i>
                                <span>ডিজিটাল অডিট ও কিউআর ভেরিফিকেশন সিস্টেম</span>
                            </div>
                            <h3 class="text-base sm:text-lg font-bold text-neutral-900">
                                সত্যতা নিশ্চিতকরণ ও অ্যান্টি-জালিয়াতি সুরক্ষা
                            </h3>
                            <p class="text-neutral-600 leading-relaxed text-xs sm:text-sm">
                                এই সনদটি <?= htmlspecialchars($up_name, ENT_QUOTES, 'UTF-8') ?> এর ডিজিটাল ক্লাউড গভর্নেন্স সিস্টেমে অনুমোদিত ও ডাটাবেজে সুরক্ষিত রয়েছে। যেকোনো স্মার্টফোন দিয়ে পাশের কিউআর কোডটি স্ক্যান করে যেকোনো সময় সনদটির সত্যতা অনলাইনে যাচাই করা যাবে।
                            </p>
                            <?php if (!empty($uniq_hash)): ?>
                                <div class="pt-1 flex flex-wrap items-center gap-2 text-xs text-neutral-500 font-mono">
                                    <span class="font-sans font-medium text-neutral-600">ডিজিটাল ট্র্যাকিং হ্যাশ (UID):</span>
                                    <span class="px-2.5 py-1 rounded-lg bg-white border border-neutral-300 text-emerald-700 font-bold tracking-wider">
                                        <?= htmlspecialchars($uniq_hash, ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Bottom Action Buttons -->
                    <div class="no-print-area flex flex-wrap items-center justify-between gap-3 pt-4 border-t border-neutral-200">
                        <div class="text-xs text-neutral-500">
                            সহায়তার জন্য যোগাযোগ: <strong><?= !empty($sec_phone) ? $fmt_en2bn($sec_phone) : 'ইউপি সচিব' ?></strong>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <a href="<?= site_url('sonod-v') ?>" class="ht-btn ht-btn-secondary text-xs">
                                <i class="ph-bold ph-magnifying-glass"></i>
                                <span>অন্য সনদ যাচাই করুন</span>
                            </a>
                            <button type="button" onclick="window.print()" class="ht-btn ht-btn-primary text-xs">
                                <i class="ph-bold ph-printer"></i>
                                <span>যাচাই স্লিপ প্রিন্ট করুন</span>
                            </button>
                        </div>
                    </div>

                </div>
            </div>

        <?php else: ?>
            <!-- ============================================================ -->
            <!-- INVALID OR UNSEARCHED STATE                                  -->
            <!-- ============================================================ -->
            <div class="max-w-2xl mx-auto bg-white rounded-3xl border border-neutral-200/90 shadow-xl shadow-neutral-900/5 p-8 sm:p-10 text-center space-y-6">
                
                <?php if (!empty($searched_no)): ?>
                    <div class="w-16 h-16 rounded-2xl bg-rose-50 text-rose-500 mx-auto flex items-center justify-center text-3xl border border-rose-200">
                        <i class="ph-bold ph-warning-circle"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-neutral-900">সনদপত্রটি ডাটাবেজে পাওয়া যায়নি</h2>
                        <p class="text-xs text-neutral-500 mt-1 max-w-md mx-auto">
                            "<strong><?= htmlspecialchars($searched_no, ENT_QUOTES, 'UTF-8') ?></strong>" নম্বরযুক্ত কোনো অনুমোদিত সনদ ডাটাবেজে রেকর্ড করা নেই। অনুগ্রহ করে নম্বরটি সঠিকভাবে দেখে পুনরায় চেষ্টা করুন।
                        </p>
                    </div>
                <?php else: ?>
                    <div class="w-16 h-16 rounded-2xl bg-indigo-50 text-indigo-600 mx-auto flex items-center justify-center text-3xl border border-indigo-200">
                        <i class="ph-bold ph-magnifying-glass"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-neutral-900">অনলাইন সনদপত্র যাচাই করুন</h2>
                        <p class="text-xs text-neutral-500 mt-1 max-w-md mx-auto">
                            আপনার হাতে থাকা সনদপত্রের ট্র্যাকিং নম্বরটি প্রবেশ করিয়ে ডিজিটাল ডাটাবেজে এর সত্যতা ও গ্রহণযোগ্যতা যাচাই করুন।
                        </p>
                    </div>
                <?php endif; ?>

                <!-- Sonod Number Search Form -->
                <form action="<?= site_url('sonod-v') ?>" method="get" class="max-w-md mx-auto flex gap-2">
                    <input type="text" name="sonod_no" required
                           value="<?= htmlspecialchars($searched_no ?? '', ENT_QUOTES, 'UTF-8') ?>"
                           placeholder="সনদ নম্বর লিখুন (যেমন: 20261008100001)"
                           class="flex-1 px-4 py-2.5 rounded-xl border border-neutral-300 text-sm text-neutral-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <button type="submit" class="ht-btn ht-btn-primary text-xs px-5">
                        <i class="ph-bold ph-magnifying-glass"></i>
                        <span>যাচাই করুন</span>
                    </button>
                </form>

                <div class="pt-4 border-t border-neutral-100 flex items-center justify-center gap-4 text-xs text-neutral-400">
                    <a href="<?= base_url() ?>" class="hover:text-neutral-700 transition-colors flex items-center gap-1">
                        <i class="ph-bold ph-arrow-left"></i>
                        <span>হোম পেজে ফিরুন</span>
                    </a>
                    <span>&bull;</span>
                    <a href="<?= site_url('search_hold') ?>" class="hover:text-neutral-700 transition-colors">
                        হোল্ডিং ট্যাক্স যাচাই
                    </a>
                </div>

            </div>
        <?php endif; ?>

    </div>
</main>

<script>
function copySonodNumber(text, btn) {
    if (!text) return;

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text);
    } else {
        var textArea = document.createElement("textarea");
        textArea.value = text;
        textArea.style.position = "fixed";
        textArea.style.left = "-999999px";
        textArea.style.top = "-999999px";
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        try {
            document.execCommand('copy');
        } catch (err) {}
        textArea.remove();
    }

    if (btn) {
        var icon = btn.querySelector('.copy-btn-icon');
        var textEl = btn.querySelector('.copy-btn-text');
        if (icon) {
            icon.className = 'ph-bold ph-check text-sm text-emerald-300 copy-btn-icon';
        }
        if (textEl) {
            textEl.classList.remove('hidden');
        }
        setTimeout(function() {
            if (icon) {
                icon.className = 'ph-bold ph-copy text-sm copy-btn-icon';
            }
            if (textEl) {
                textEl.classList.add('hidden');
            }
        }, 2000);
    }

    showCustomToast('সনদ নম্বর সফলভাবে কপি করা হয়েছে!');
}

function showCustomToast(message) {
    var oldToast = document.getElementById('viretadevCustomToast');
    if (oldToast) oldToast.remove();

    var toast = document.createElement('div');
    toast.id = 'viretadevCustomToast';
    toast.style.cssText = 'position: fixed; top: 28px; left: 50%; transform: translate(-50%, -20px); z-index: 999999; display: inline-flex; align-items: center; gap: 10px; padding: 10px 20px; border-radius: 9999px; background: rgba(15, 23, 42, 0.95); color: #ffffff; font-size: 13px; font-weight: 600; box-shadow: 0 12px 32px rgba(0, 0, 0, 0.35); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.18); opacity: 0; transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); pointer-events: none;';
    
    toast.innerHTML = '<span style="width: 22px; height: 22px; border-radius: 50%; background: rgba(16, 185, 129, 0.25); color: #34d399; display: flex; align-items: center; justify-content: center; font-size: 13px;"><i class="ph-bold ph-check"></i></span><span>' + message + '</span>';

    document.body.appendChild(toast);

    requestAnimationFrame(function() {
        toast.style.opacity = '1';
        toast.style.transform = 'translate(-50%, 0)';
    });

    setTimeout(function() {
        toast.style.opacity = '0';
        toast.style.transform = 'translate(-50%, -15px)';
        setTimeout(function() {
            if (toast && toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }, 2400);
}
</script>

<?php
$this->load->view('front/partials/footer', $view_data);
?>
