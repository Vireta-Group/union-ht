<section id="leaders" class="py-12 bg-neutral-50/70 border-t border-neutral-200/80">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Section Title -->
        <div class="text-center max-w-2xl mx-auto mb-8">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800 mb-2">
                <i class="ph ph-users text-indigo-600 text-sm"></i> স্থানীয় নেতৃত্ব
            </span>
            <h2 class="text-2xl sm:text-3xl font-bold text-neutral-900 tracking-tight">
                সম্মানিত ইউনিয়ন পরিষদ সদস্যবৃন্দ
            </h2>
            <p class="text-xs sm:text-sm text-neutral-500 mt-1">
                ওয়ার্ড ভিত্তিক নির্বাচিত জনপ্রতিনিধিদের পরিচিতি ও যোগাযোগের তথ্য
            </p>
        </div>

        <?php if (!empty($up_mbrs)): ?>
            <div class="relative">
                <div class="team-slider up-member owl-carousel">
                    <?php foreach ($up_mbrs as $mbr): 
                        $mbr_photo = !empty($mbr->up_mem_pic_base64s) 
                            ? media_url($mbr->up_mem_pic_base64s, 'image/png') 
                            : 'inc/assets/himg/avatar1.png';
                    ?>
                        <div class="p-2">
                            <div class="ht-card p-5 bg-white border border-neutral-200/90 hover:shadow-lg transition-all duration-200 rounded-2xl flex flex-col items-center text-center group">
                                <!-- Photo with circular border & subtle scale on hover -->
                                <div class="relative w-24 h-24 mb-3.5 rounded-full overflow-hidden border-2 border-indigo-100 bg-neutral-100 shadow-xs group-hover:scale-105 transition-transform duration-200">
                                    <img src="<?= $mbr_photo; ?>" 
                                         alt="<?= htmlspecialchars($mbr->up_mem_s_name ?? 'সদস্য', ENT_QUOTES, 'UTF-8'); ?>"
                                         class="w-full h-full object-cover">
                                </div>

                                <!-- Ward Pill Badge -->
                                <span class="inline-block px-2.5 py-0.5 mb-2 rounded-full text-[11px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                    ওয়ার্ড নং- <?= htmlspecialchars($mbr->ward_no_sq ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                </span>

                                <!-- Member Name -->
                                <h3 class="text-sm font-bold text-neutral-900 group-hover:text-indigo-600 transition-colors line-clamp-1">
                                    <?= htmlspecialchars($mbr->up_mem_s_name ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                </h3>

                                <!-- Contact & Action -->
                                <?php if (!empty($mbr->mobile_s_no_phone)): ?>
                                    <a href="tel:<?= htmlspecialchars($mbr->mobile_s_no_phone, ENT_QUOTES, 'UTF-8'); ?>" 
                                       class="text-xs text-neutral-500 hover:text-indigo-600 font-medium mt-1 flex items-center gap-1.5 transition-colors">
                                        <i class="ph ph-phone text-xs text-amber-500"></i>
                                        <span><?= htmlspecialchars($mbr->mobile_s_no_phone, ENT_QUOTES, 'UTF-8'); ?></span>
                                    </a>
                                <?php endif; ?>

                                <div class="mt-4 pt-3 border-t border-neutral-100 w-full">
                                    <a href="singleUser?memNO=<?= htmlspecialchars($mbr->up_member_s ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
                                       class="ht-btn ht-btn-secondary w-full justify-center text-xs py-1.5">
                                        <span>বিস্তারিত</span>
                                        <i class="ph ph-arrow-right text-xs"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <p class="text-center text-neutral-400 text-sm py-8">কোনো সদস্যের তথ্য পাওয়া যায়নি।</p>
        <?php endif; ?>

    </div>
</section>
