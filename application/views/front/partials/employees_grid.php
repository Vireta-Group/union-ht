<section class="py-12 bg-white border-t border-neutral-200/80">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Section Title -->
        <div class="text-center max-w-2xl mx-auto mb-8">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 mb-2">
                <i class="ph ph-user text-amber-600 text-sm"></i> প্রশাসনিক টিম
            </span>
            <h2 class="text-2xl sm:text-3xl font-bold text-neutral-900 tracking-tight">
                কর্মকর্তা ও কর্মচারীবৃন্দ
            </h2>
            <p class="text-xs sm:text-sm text-neutral-500 mt-1">
                ইউনিয়ন পরিষদের প্রশাসনিক কার্যক্রম ও সার্বিক সেবা পরিচালনায় দায়িত্বরত ব্যক্তিবর্গ
            </p>
        </div>

        <?php if (!empty($up_emps)): ?>
            <div class="relative">
                <div class="team-slider up-member-2 owl-carousel">
                    <?php foreach ($up_emps as $emp): 
                        $emp_photo = !empty($emp->emp_pic_base64) 
                            ? media_url($emp->emp_pic_base64, 'image/png') 
                            : 'inc/assets/himg/avatar1.png';
                    ?>
                        <div class="p-2">
                            <div class="ht-card p-5 bg-neutral-50/60 border border-neutral-200/90 hover:shadow-lg transition-all duration-200 rounded-2xl flex flex-col items-center text-center group">
                                <!-- Photo with rounded rectangle border & shadow -->
                                <div class="relative w-24 h-24 mb-3.5 rounded-2xl overflow-hidden border-2 border-neutral-200 bg-white shadow-xs group-hover:scale-105 transition-transform duration-200">
                                    <img src="<?= $emp_photo; ?>" 
                                         alt="<?= htmlspecialchars($emp->emp_name ?? 'কর্মকর্তা', ENT_QUOTES, 'UTF-8'); ?>"
                                         class="w-full h-full object-cover">
                                </div>

                                <!-- Designation Pill -->
                                <span class="inline-block px-2.5 py-0.5 mb-1.5 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                                    <?= htmlspecialchars($emp->emp_designation ?? 'কর্মকর্তা', ENT_QUOTES, 'UTF-8'); ?>
                                </span>

                                <!-- Name -->
                                <h3 class="text-sm font-bold text-neutral-900 group-hover:text-amber-700 transition-colors line-clamp-1">
                                    <?= htmlspecialchars($emp->emp_name ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                </h3>

                                <!-- Mobile / Contact -->
                                <?php if (!empty($emp->emp_phone)): ?>
                                    <a href="tel:<?= htmlspecialchars($emp->emp_phone, ENT_QUOTES, 'UTF-8'); ?>" 
                                       class="text-xs text-neutral-500 hover:text-amber-700 font-medium mt-1 flex items-center gap-1.5 transition-colors">
                                        <i class="ph ph-phone text-xs text-amber-500"></i>
                                        <span><?= htmlspecialchars($emp->emp_phone, ENT_QUOTES, 'UTF-8'); ?></span>
                                    </a>
                                <?php endif; ?>

                                <?php if (!empty($emp->emp_address)): ?>
                                    <p class="text-[11px] text-neutral-400 mt-0.5 line-clamp-1 flex items-center gap-1">
                                        <i class="ph ph-map-pin text-xs"></i> <span><?= htmlspecialchars($emp->emp_address, ENT_QUOTES, 'UTF-8'); ?></span>
                                    </p>
                                <?php endif; ?>

                                <div class="mt-4 pt-3 border-t border-neutral-200/80 w-full">
                                    <a href="singleEmp?empNO=<?= htmlspecialchars($emp->employee_data_ins_di ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
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
            <p class="text-center text-neutral-400 text-sm py-8">কোনো কর্মকর্তা-কর্মচারীর তথ্য পাওয়া যায়নি।</p>
        <?php endif; ?>

    </div>
</section>
