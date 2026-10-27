<section class="py-6 lg:py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
            
            <!-- Left Column: Hero Banner Slider (8 cols) -->
            <div class="lg:col-span-8 flex flex-col">
                <div class="relative w-full h-80 sm:h-100 lg:h-112.5 rounded-2xl overflow-hidden shadow-sm border border-neutral-200 bg-neutral-900 group">
                    <?php if (!empty($sliders)): ?>
                        <div class="swiper heroSwiper h-full w-full">
                            <div class="swiper-wrapper h-full w-full">
                                <?php foreach ($sliders as $index => $slider): 
                                    $slider_src = media_url($slider->sliderbase64, 'image/jpeg');
                                ?>
                                    <div class="swiper-slide h-full w-full relative">
                                        <img src="<?= $slider_src; ?>" 
                                             class="w-full h-full object-cover object-center" 
                                             alt="<?= htmlspecialchars($slider->slider_title ?? 'স্লাইডার', ENT_QUOTES, 'UTF-8'); ?>">
                                        
                                        <!-- Solid Bottom Overlay for High Readability -->
                                        <div class="absolute inset-x-0 bottom-0 bg-linear-to-t from-neutral-950/95 via-neutral-950/70 to-transparent p-6 sm:p-8">
                                            <?php if (!empty($slider->slider_title)): ?>
                                                <div class="text-white max-w-2xl">
                                                    <h3 class="text-xl sm:text-2xl font-bold tracking-tight text-white mb-1.5 drop-shadow-sm">
                                                        <?= htmlspecialchars($slider->slider_title, ENT_QUOTES, 'UTF-8'); ?>
                                                    </h3>
                                                    <?php if (!empty($slider->slider_sub_title)): ?>
                                                        <p class="text-sm text-neutral-200 font-normal line-clamp-2">
                                                            <?= htmlspecialchars($slider->slider_sub_title, ENT_QUOTES, 'UTF-8'); ?>
                                                        </p>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Swiper Pagination -->
                            <div class="swiper-pagination bottom-3!"></div>

                            <!-- Swiper Navigation Buttons -->
                            <div class="swiper-button-prev w-10! h-10! rounded-full! bg-black/60! hover:bg-black/90! text-white! opacity-0 group-hover:opacity-100 transition-all after:text-sm!"></div>
                            <div class="swiper-button-next w-10! h-10! rounded-full! bg-black/60! hover:bg-black/90! text-white! opacity-0 group-hover:opacity-100 transition-all after:text-sm!"></div>
                        </div>

                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                if (typeof Swiper !== 'undefined') {
                                    new Swiper('.heroSwiper', {
                                        loop: true,
                                        speed: 700,
                                        autoplay: {
                                            delay: 4500,
                                            disableOnInteraction: false,
                                        },
                                        pagination: {
                                            el: '.swiper-pagination',
                                            clickable: true,
                                        },
                                        navigation: {
                                            nextEl: '.swiper-button-next',
                                            prevEl: '.swiper-button-prev',
                                        },
                                        effect: 'fade',
                                        fadeEffect: {
                                            crossFade: true
                                        }
                                    });
                                }
                            });
                        </script>
                    <?php else: ?>
                        <!-- Fallback Hero Banner (Solid Color) -->
                        <div class="h-full w-full flex flex-col justify-end p-8 bg-indigo-950 text-white relative">
                            <div class="absolute top-6 right-6 opacity-10">
                                <i class="ph ph-buildings text-9xl"></i>
                            </div>
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full bg-amber-500/20 text-amber-300 w-fit mb-3 border border-amber-500/30">
                                <i class="ph ph-star text-sm"></i> স্মার্ট বাংলাদেশ গড়ার অঙ্গীকার
                            </span>
                            <h2 class="text-2xl sm:text-3xl font-bold leading-tight mb-2">
                                <?= htmlspecialchars($setting->ins_name ?? 'ইউনিয়ন পরিষদ', ENT_QUOTES, 'UTF-8'); ?>
                            </h2>
                            <p class="text-neutral-300 text-sm max-w-xl font-normal leading-relaxed">
                                ডিজিটাল প্রযুক্তির মাধ্যমে জনগণের দোরগোড়ায় ইউনিয়ন পরিষদের নাগরিক সেবা পৌঁছে দেওয়াই আমাদের মূল লক্ষ্য।
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Column: Chairman Profile & Speech Card (4 cols) -->
            <div class="lg:col-span-4 flex flex-col">
                <div class="ht-card h-full p-6 flex flex-col justify-between relative overflow-hidden bg-white border border-neutral-200 shadow-sm">
                    <!-- Accent decorative bar (Solid) -->
                    <div class="absolute top-0 left-0 right-0 h-1 bg-indigo-600"></div>

                    <div class="text-center pt-2">
                        <!-- Chairman Photo with natural portrait framing -->
                        <div class="relative mx-auto mb-3.5" style="width: 140px; height: 175px;">
                            <div class="w-full h-full rounded-2xl overflow-hidden border-2 border-indigo-100 shadow-md bg-neutral-100">
                                <img src="<?= media_url($setting->cm_pic_base64 ?? '', 'image/png'); ?>" 
                                     alt="<?= htmlspecialchars($setting->cm_name_bn ?? 'চেয়ারম্যান', ENT_QUOTES, 'UTF-8'); ?>"
                                     class="w-full h-full object-cover object-top"
                                     style="object-fit: cover; object-position: top center;">
                            </div>
                        </div>

                        <!-- Name & Title with verified badge -->
                        <h3 class="text-lg font-bold text-neutral-900 leading-snug">
                            <?= htmlspecialchars($setting->cm_name_bn ?? 'চেয়ারম্যান মহোদয়', ENT_QUOTES, 'UTF-8'); ?>
                        </h3>
                        <div class="mt-1 flex items-center justify-center">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                <i class="ph ph-seal-check text-indigo-600 text-sm"></i>
                                <span>ইউপি চেয়ারম্যান</span>
                            </span>
                        </div>
                        <p class="text-[11px] text-neutral-500 font-medium mt-1">
                            <?= htmlspecialchars($setting->ins_name ?? '', ENT_QUOTES, 'UTF-8'); ?>
                        </p>

                        <!-- Quote/Speech snippet -->
                        <div class="mt-4 p-3.5 rounded-xl bg-neutral-50 border border-neutral-100 text-left relative">
                            <i class="ph ph-quotes text-neutral-300 text-2xl absolute top-2 right-3 opacity-40"></i>
                            <h4 class="text-xs font-bold text-neutral-700 uppercase tracking-wider mb-1 flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> চেয়ারম্যানের বার্তা
                            </h4>
                            <p class="text-xs text-neutral-600 leading-relaxed line-clamp-3">
                                <?= excerpt($setting->chairman_speach ?? 'জনকল্যাণে কাজ করাই আমাদের প্রত্যয়। ডিজিটাল সেবা গ্রহণে সবাইকে স্বাগতম।', 180); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Read full speech modal trigger button -->
                    <div class="mt-5">
                        <button type="button" 
                                data-toggle="modal" 
                                data-target="#CMDScribeModel"
                                class="ht-btn ht-btn-secondary w-full justify-center text-xs py-2.5">
                            <span>সম্পূর্ণ বার্তা পড়ুন</span>
                            <i class="ph ph-arrow-right text-xs"></i>
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
