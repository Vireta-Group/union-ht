<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * single_member_views — rebuilt 2026-09-29 onto the shared portal partials.
 *
 * This page used to ship its own <head>, its own hand-rolled navbar and the
 * legacy orange footer (footer-section / footer-cta / single-cta / cta-text /
 * copyright-area, all from legacy-profile.css, with #ff5e14 and #eb4027 written
 * inline), which is why it did not look like the rest of the site. It now loads
 * front/partials/header.php, /navbar.php and /footer.php, exactly as
 * front/home.php does, and legacy-profile.css is no longer referenced.
 *
 * The class moves that came with it: .card -> .ht-card, .scrollable ->
 * max-h-64 overflow-y-auto. The nav markup that used to carry .nav-item and
 * .nav-active belongs to the shared navbar now.
 *
 * Page-specific markup and the a5_script.js include below the fold were left as they were.
 */


// $page_title / $page_desc let front/partials/header.php name this page
// instead of falling back to the bare tenant name. They are assigned
// BEFORE $view_data is built: that array is what the partial receives,
// and when the controller has already populated $this->data,
// get_defined_vars() is never called, so anything set afterwards would
// simply not be in it.
$page_title = 'সদস্যের তথ্য';
$page_desc  = 'ইউনিয়ন পরিষদ সদস্যের হোল্ডিং ও ব্যক্তিগত তথ্য';
$view_data  = ($this->data ?? get_defined_vars());
$view_data['page_title'] = $page_title;
$view_data['page_desc']  = $page_desc;

$this->load->view('front/partials/header', $view_data);
$this->load->view('front/partials/navbar', $view_data);
?>



        <main class="py-8 sm:py-12 bg-neutral-50/50 min-h-[60vh]">
            <div class="max-w-6xl mx-auto px-4 sm:px-6">

                <!-- Breadcrumb -->
                <nav class="mb-6 flex items-center gap-2 text-xs text-neutral-500">
                    <a href="<?= base_url(); ?>" class="hover:text-indigo-600 transition-colors flex items-center gap-1">
                        <i class="ph ph-house text-sm"></i>
                        <span>হোম</span>
                    </a>
                    <i class="ph ph-caret-right text-[10px] text-neutral-400"></i>
                    <a href="<?= base_url('#leaders'); ?>" class="hover:text-indigo-600 transition-colors">জনপ্রতিনিধি</a>
                    <i class="ph ph-caret-right text-[10px] text-neutral-400"></i>
                    <span class="font-medium text-neutral-800"><?= htmlspecialchars($mbr->up_mem_s_name ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
                </nav>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                    
                    <!-- Left Column: Portrait & Quick Contact -->
                    <div class="lg:col-span-4">
                        <div class="ht-card p-6 text-center relative overflow-hidden bg-white border border-neutral-200 shadow-sm rounded-2xl">
                            <!-- Top accent -->
                            <div class="absolute top-0 left-0 right-0 h-1.5 bg-indigo-600"></div>

                            <!-- Avatar -->
                            <div class="relative w-36 h-36 mx-auto mb-4 mt-2">
                                <div class="w-full h-full rounded-2xl overflow-hidden border-2 border-indigo-100 shadow-sm bg-neutral-100 flex items-center justify-center">
                                    <?php if (empty($mbr->up_mem_pic_base64s)): ?>
                                        <img alt="<?= htmlspecialchars($mbr->up_mem_s_name ?? 'সদস্য', ENT_QUOTES, 'UTF-8'); ?>"
                                             src="inc/assets/himg/avatar1.png"
                                             class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <img alt="<?= htmlspecialchars($mbr->up_mem_s_name ?? 'সদস্য', ENT_QUOTES, 'UTF-8'); ?>"
                                             src="<?= media_url($mbr->up_mem_pic_base64s, 'image/png') ?>"
                                             class="w-full h-full object-cover">
                                    <?php endif; ?>
                                </div>
                                <span class="absolute -bottom-1 -right-1 w-7 h-7 bg-emerald-600 text-white rounded-full flex items-center justify-center text-xs shadow-sm border-2 border-white" title="জনপ্রতিনিধি">
                                    <i class="ph ph-check text-xs"></i>
                                </span>
                            </div>

                            <h2 class="text-xl font-bold text-neutral-900 leading-snug">
                                <?= htmlspecialchars($mbr->up_mem_s_name ?? '', ENT_QUOTES, 'UTF-8'); ?>
                            </h2>

                            <div class="mt-2 flex flex-wrap justify-center gap-1.5">
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                    <i class="ph ph-user-circle text-xs"></i>
                                    ইউপি সদস্য (মেম্বার)
                                </span>
                                <?php if (!empty($mbr->ward_no_sq)): ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                        <i class="ph ph-map-pin text-xs"></i>
                                        <?= htmlspecialchars($mbr->ward_no_sq, ENT_QUOTES, 'UTF-8'); ?> নং ওয়ার্ড
                                    </span>
                                <?php endif; ?>
                            </div>

                            <p class="mt-2 text-xs text-neutral-500 font-medium">
                                <?= htmlspecialchars($setting->ins_name ?? 'ইউনিয়ন পরিষদ', ENT_QUOTES, 'UTF-8'); ?>
                            </p>

                            <!-- Direct Actions -->
                            <?php if (!empty($mbr->mobile_s_no_phone)): ?>
                                <div class="mt-6 pt-5 border-t border-neutral-100 flex flex-col gap-2.5">
                                    <a href="tel:<?= htmlspecialchars($mbr->mobile_s_no_phone, ENT_QUOTES, 'UTF-8'); ?>"
                                       class="ht-btn ht-btn-primary w-full justify-center py-2.5 shadow-sm">
                                        <i class="ph ph-phone-call text-sm"></i>
                                        <span>সরাসরি কল করুন</span>
                                    </a>
                                    <a href="https://wa.me/88<?= preg_replace('/\D/', '', $mbr->mobile_s_no_phone); ?>"
                                       target="_blank" rel="noopener noreferrer"
                                       class="ht-btn ht-btn-secondary w-full justify-center py-2 text-xs">
                                        <i class="ph ph-whatsapp-logo text-emerald-600 text-sm"></i>
                                        <span>হোয়াটসঅ্যাপ বার্তা</span>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Right Column: Official Details Card -->
                    <div class="lg:col-span-8 space-y-6">
                        <div class="ht-card p-6 bg-white border border-neutral-200 shadow-sm rounded-2xl">
                            <!-- Card Header -->
                            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-neutral-100 pb-4 mb-5">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                                        <i class="ph ph-identification-card text-xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-base font-bold text-neutral-900 leading-tight">জনপ্রতিনিধি পরিচিতি ও বিবরণ</h3>
                                        <p class="text-xs text-neutral-500">ইউনিয়ন পরিষদ সদস্যের ব্যক্তিগত ও যোগাযোগ তথ্য</p>
                                    </div>
                                </div>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <i class="ph ph-shield-check text-sm"></i> তথ্য যাচাইকৃত
                                </span>
                            </div>

                            <!-- Info Grid -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <!-- Name -->
                                <div class="p-3.5 rounded-xl bg-neutral-50/80 border border-neutral-100 flex items-start gap-3">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white border border-neutral-200 text-neutral-600 shadow-xs">
                                        <i class="ph ph-user text-sm"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <span class="block text-[11px] font-semibold text-neutral-500 uppercase tracking-wide">সম্পূর্ণ নাম</span>
                                        <span class="block text-sm font-bold text-neutral-900 mt-0.5 truncate">
                                            <?= htmlspecialchars($mbr->up_mem_s_name ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </div>
                                </div>

                                <!-- Designation -->
                                <div class="p-3.5 rounded-xl bg-neutral-50/80 border border-neutral-100 flex items-start gap-3">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white border border-neutral-200 text-neutral-600 shadow-xs">
                                        <i class="ph ph-briefcase text-sm"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <span class="block text-[11px] font-semibold text-neutral-500 uppercase tracking-wide">পদবি ও দায়িত্ব</span>
                                        <span class="block text-sm font-bold text-neutral-900 mt-0.5">
                                            ইউপি সদস্য (মেম্বার)
                                        </span>
                                    </div>
                                </div>

                                <!-- Ward No -->
                                <div class="p-3.5 rounded-xl bg-neutral-50/80 border border-neutral-100 flex items-start gap-3">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white border border-neutral-200 text-neutral-600 shadow-xs">
                                        <i class="ph ph-map-trifold text-sm"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <span class="block text-[11px] font-semibold text-neutral-500 uppercase tracking-wide">ওয়ার্ড নম্বর</span>
                                        <span class="block text-sm font-bold text-neutral-900 mt-0.5">
                                            <?= htmlspecialchars($mbr->ward_no_sq ? $mbr->ward_no_sq . ' নং ওয়ার্ড' : 'সাধারণ ওয়ার্ড', ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </div>
                                </div>

                                <!-- Phone -->
                                <div class="p-3.5 rounded-xl bg-neutral-50/80 border border-neutral-100 flex items-start gap-3">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white border border-neutral-200 text-neutral-600 shadow-xs">
                                        <i class="ph ph-phone text-sm"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <span class="block text-[11px] font-semibold text-neutral-500 uppercase tracking-wide">মোবাইল নম্বর</span>
                                        <?php if (!empty($mbr->mobile_s_no_phone)): ?>
                                            <a href="tel:<?= htmlspecialchars($mbr->mobile_s_no_phone, ENT_QUOTES, 'UTF-8'); ?>" class="block text-sm font-bold text-indigo-600 hover:underline mt-0.5">
                                                <?= htmlspecialchars($mbr->mobile_s_no_phone, ENT_QUOTES, 'UTF-8'); ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="block text-sm text-neutral-400 mt-0.5">তথ্য নেই</span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Institute -->
                                <div class="sm:col-span-2 p-3.5 rounded-xl bg-neutral-50/80 border border-neutral-100 flex items-start gap-3">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white border border-neutral-200 text-neutral-600 shadow-xs">
                                        <i class="ph ph-buildings text-sm"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <span class="block text-[11px] font-semibold text-neutral-500 uppercase tracking-wide">প্রতিষ্ঠান / পরিষদ</span>
                                        <span class="block text-sm font-bold text-neutral-900 mt-0.5">
                                            <?= htmlspecialchars($setting->ins_name ?? 'ইউনিয়ন পরিষদ', ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Citizen Help Notice -->
                            <div class="mt-6 p-4 rounded-xl bg-indigo-50/60 border border-indigo-100 flex items-start gap-3">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-indigo-600 text-white text-sm">
                                    <i class="ph ph-info text-base"></i>
                                </div>
                                <div class="text-xs text-indigo-950 leading-relaxed">
                                    <span class="font-bold block mb-0.5">ওয়ার্ডভিত্তিক সেবা সংক্রান্ত তথ্য</span>
                                    ওয়ার্ডের নাগরিকগণ যেকোনো প্রত্যয়নপত্র, ওয়ারিশ সনদ, নাগরিক সনদ বা ভিজিডি/ভিজিএফ সহায়তা সংক্রান্ত বিষয়ে সংশ্লিষ্ট ওয়ার্ড সদস্যের সাথে সরাসরি যোগাযোগ করতে পারেন।
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </main>


        <!-- A5 Script -->
        <script src="inc/assets/js/a5_script.js"></script>

<?php
// Footer last: front/partials/footer.php closes </body></html> itself, so
// this file must not repeat them. Same order as front/home.php.
$this->load->view('front/partials/footer', $view_data);
