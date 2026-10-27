<!-- Modern Dynamic Footer -->
<footer class="bg-neutral-900 text-neutral-300 mt-auto border-t border-neutral-800" id="contact">
    
    <!-- Top Contact Cards Bar -->
    <div class="border-b border-neutral-800 bg-neutral-950">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <!-- Address Card -->
                <div class="flex items-center gap-4 p-4 rounded-xl bg-neutral-900 border border-neutral-800">
                    <div class="w-12 h-12 rounded-xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400 text-2xl shrink-0">
                        <i class="ph ph-map-pin"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-semibold text-neutral-400 uppercase tracking-wider">কার্যালয়ের ঠিকানা</h4>
                        <p class="text-sm font-medium text-white mt-0.5"><?= htmlspecialchars($setting->addre_s_s ?? 'ইউনিয়ন পরিষদ কার্যালয়', ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                </div>

                <!-- Phone Card -->
                <div class="flex items-center gap-4 p-4 rounded-xl bg-neutral-900 border border-neutral-800">
                    <div class="w-12 h-12 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400 text-2xl shrink-0">
                        <i class="ph ph-phone"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-semibold text-neutral-400 uppercase tracking-wider">জরুরি যোগাযোগ</h4>
                        <p class="text-sm font-medium text-white mt-0.5">
                            <?= htmlspecialchars(!empty($setting->socib_phone) ? $setting->socib_phone : ($setting->chairman_phone ?? '০১৮...'), ENT_QUOTES, 'UTF-8'); ?>
                        </p>
                    </div>
                </div>

                <!-- Email Card -->
                <div class="flex items-center gap-4 p-4 rounded-xl bg-neutral-900 border border-neutral-800">
                    <div class="w-12 h-12 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 text-2xl shrink-0">
                        <i class="ph ph-envelope"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-semibold text-neutral-400 uppercase tracking-wider">ইমেইল ঠিকানা</h4>
                        <p class="text-sm font-medium text-white mt-0.5"><?= htmlspecialchars($setting->ins_emails ?? 'info@up.gov.bd', ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Main Footer Links & Info -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-8">
            
            <!-- Union Info & Logo (5 cols) -->
            <div class="md:col-span-5 space-y-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white p-1 flex items-center justify-center">
                        <img src="<?= media_url($setting->ins_logo_path ?? '', 'image/png'); ?>" 
                             alt="<?= htmlspecialchars($setting->ins_name ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
                             class="max-w-full max-h-full object-contain">
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-white"><?= htmlspecialchars($setting->ins_name ?? '', ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p class="text-xs text-neutral-400 font-medium"><?= htmlspecialchars($setting->ins_title_ss ?? 'ডিজিটাল ইউনিয়ন পরিষদ', ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                </div>
                <p class="text-xs text-neutral-400 leading-relaxed max-w-sm">
                    ডিজিটাল প্রযুক্তির মাধ্যমে ইউনিয়ন পরিষদের সার্বিক সেবা দ্রুত, স্বচ্ছ ও নির্ভরযোগ্যভাবে জনগণের দোরগোড়ায় পৌঁছে দিতে এই অনলাইন বাতায়ন পরিচালিত হচ্ছে।
                </p>

                <!-- Union Social & Official Channels (Official Brand Colors & Larger Size) -->
                <div class="flex items-center gap-2.5 pt-2">
                    <a href="https://facebook.com" target="_blank" rel="noopener noreferrer"
                       class="flex h-9 w-9 items-center justify-center rounded-xl transition-all duration-200 hover:scale-105"
                       style="background: rgba(24, 119, 242, 0.12); border: 1px solid rgba(24, 119, 242, 0.3); color: #1877F2;"
                       onmouseover="this.style.background='#1877F2'; this.style.color='#FFFFFF';"
                       onmouseout="this.style.background='rgba(24, 119, 242, 0.12)'; this.style.color='#1877F2';"
                       title="অফিশিয়াল ফেসবুক পেজ" aria-label="Facebook">
                        <i class="ph-fill ph-facebook-logo" style="font-size: 19px;"></i>
                    </a>
                    <a href="https://youtube.com" target="_blank" rel="noopener noreferrer"
                       class="flex h-9 w-9 items-center justify-center rounded-xl transition-all duration-200 hover:scale-105"
                       style="background: rgba(255, 0, 0, 0.12); border: 1px solid rgba(255, 0, 0, 0.3); color: #FF0000;"
                       onmouseover="this.style.background='#FF0000'; this.style.color='#FFFFFF';"
                       onmouseout="this.style.background='rgba(255, 0, 0, 0.12)'; this.style.color='#FF0000';"
                       title="ইউটিউব চ্যানেল" aria-label="YouTube">
                        <i class="ph-fill ph-youtube-logo" style="font-size: 19px;"></i>
                    </a>
                    <a href="https://bangladesh.gov.bd" target="_blank" rel="noopener noreferrer"
                       class="flex h-9 w-9 items-center justify-center rounded-xl transition-all duration-200 hover:scale-105"
                       style="background: rgba(16, 185, 129, 0.12); border: 1px solid rgba(16, 185, 129, 0.3); color: #10B981;"
                       onmouseover="this.style.background='#006A4E'; this.style.color='#FFFFFF';"
                       onmouseout="this.style.background='rgba(16, 185, 129, 0.12)'; this.style.color='#10B981';"
                       title="জাতীয় তথ্য বাতায়ন" aria-label="National Portal">
                        <i class="ph-fill ph-globe-hemisphere-east" style="font-size: 19px;"></i>
                    </a>
                </div>
            </div>

            <!-- Quick Links (3 cols) -->
            <div class="md:col-span-3">
                <h4 class="text-xs font-bold text-white uppercase tracking-wider mb-3">জরুরি লিংক</h4>
                <ul class="space-y-2 text-xs">
                    <li><a href="<?= base_url(); ?>" class="text-neutral-400 hover:text-white transition-colors">হোম পেজ</a></li>
                    <li><a href="search_hold" class="text-neutral-400 hover:text-white transition-colors">হোল্ডিং ট্যাক্স যাচাই</a></li>
                    <li><a href="add" class="text-neutral-400 hover:text-white transition-colors">পারিবারিক ডাটা এন্ট্রি</a></li>
                    <li><a href="login" class="text-neutral-400 hover:text-white transition-colors">অ্যাডমিন / স্টাফ লগইন</a></li>
                </ul>
            </div>

            <!-- Government National Emergency Hotlines (4 cols) -->
            <div class="md:col-span-4">
                <h4 class="text-xs font-bold text-white uppercase tracking-wider mb-3">জাতীয় জরুরি সেবা হেল্পলাইন</h4>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div class="p-2.5 rounded-lg bg-neutral-800 border border-neutral-700/80">
                        <span class="block text-amber-400 font-bold text-sm">৯৯৯</span>
                        <span class="text-[11px] text-neutral-400">জাতীয় জরুরি সেবা</span>
                    </div>
                    <div class="p-2.5 rounded-lg bg-neutral-800 border border-neutral-700/80">
                        <span class="block text-indigo-400 font-bold text-sm">৩৩৩</span>
                        <span class="text-[11px] text-neutral-400">সরকারি তথ্য ও সেবা</span>
                    </div>
                    <div class="p-2.5 rounded-lg bg-neutral-800 border border-neutral-700/80">
                        <span class="block text-emerald-400 font-bold text-sm">১০৯</span>
                        <span class="text-[11px] text-neutral-400">নারী ও শিশু সহায়তা</span>
                    </div>
                    <div class="p-2.5 rounded-lg bg-neutral-800 border border-neutral-700/80">
                        <span class="block text-rose-400 font-bold text-sm">১০৬</span>
                        <span class="text-[11px] text-neutral-400">দুদক অভিযোগ</span>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Bottom Copyright & ViretaDev SaaS Branding -->
    <div class="border-t border-neutral-800 bg-neutral-950 py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-neutral-500">
            <div>
                © <?= date('Y'); ?> <span class="text-neutral-300 font-medium"><?= htmlspecialchars($setting->ins_name ?? 'ইউনিয়ন পরিষদ', ENT_QUOTES, 'UTF-8'); ?></span>। সর্বস্বত্ব সংরক্ষিত।
            </div>
            <div class="flex items-center gap-1.5 text-xs text-neutral-400">
                <span>কারিগরি সহায়তায়:</span>
                <a href="https://viretadev.com" target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center text-indigo-400 hover:text-indigo-300 font-medium transition-colors"
                   title="ViretaDev Technologies">
                    <span class="viretadev-typewriter-text" data-viretadev-typewriter='[
                        "ViretaDev SaaS",
                        "স্মার্ট ইউনিয়ন পরিষদ",
                        "ক্লাউড গভর্নেন্স",
                        "ডিজিটাল ইউপি সিস্টেম"
                    ]'>ViretaDev SaaS</span>
                    <span class="viretadev-typewriter-cursor">|</span>
                </a>
            </div>
        </div>
    </div>

    <script>
    (function() {
        function getGraphemes(str) {
            if (typeof Intl !== 'undefined' && Intl.Segmenter) {
                try {
                    var seg = new Intl.Segmenter('bn', { granularity: 'grapheme' });
                    return Array.from(seg.segment(str)).map(function(s) { return s.segment; });
                } catch(e) {}
            }
            return Array.from(str);
        }

        function initTypewriters() {
            var elements = document.querySelectorAll('[data-viretadev-typewriter]');
            elements.forEach(function(el) {
                if (el.__typewriterRunning) return;
                el.__typewriterRunning = true;

                var words;
                try {
                    words = JSON.parse(el.getAttribute('data-viretadev-typewriter'));
                } catch (e) {
                    words = [el.textContent.trim()];
                }
                if (!words || !words.length) return;

                var wordIdx = 0;
                var isDeleting = false;
                var graphemes = getGraphemes(words[0]);
                var charIdx = graphemes.length;
                var paused = false;

                var container = el.closest('a') || el.parentElement || el;
                container.addEventListener('mouseenter', function() { paused = true; });
                container.addEventListener('mouseleave', function() { paused = false; });

                function tick() {
                    if (paused) {
                        setTimeout(tick, 200);
                        return;
                    }

                    var currentWord = words[wordIdx];
                    var currentGraphemes = getGraphemes(currentWord);

                    if (isDeleting) {
                        charIdx--;
                    } else {
                        charIdx++;
                    }

                    el.textContent = currentGraphemes.slice(0, Math.max(0, charIdx)).join('');

                    var speed = isDeleting ? 35 : 75;

                    if (!isDeleting && charIdx >= currentGraphemes.length) {
                        speed = 2400;
                        isDeleting = true;
                    } else if (isDeleting && charIdx <= 0) {
                        isDeleting = false;
                        wordIdx = (wordIdx + 1) % words.length;
                        charIdx = 0;
                        speed = 400;
                    }

                    setTimeout(tick, speed);
                }

                setTimeout(function() {
                    isDeleting = true;
                    tick();
                }, 2500);
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initTypewriters);
        } else {
            initTypewriters();
        }
    })();
    </script>

</footer>

<!-- Owl Carousel Script Initializations -->
<script>
    $(document).ready(function(){
        $('.up-member').owlCarousel({
            loop: true,            
            nav: false,
            dots: true,
            autoplay: true,
            autoplayTimeout: 4500,
            autoplayHoverPause: true,
            smartSpeed: 450,
            margin: 16,
            responsive: {
                0: { items: 1 },
                576: { items: 2 },
                768: { items: 3 },
                1024: { items: 4 },
                1280: { items: 4 }
            }
        });

        $('.up-member-2').owlCarousel({
            loop: true,            
            nav: false,
            dots: true,
            autoplay: true,
            autoplayTimeout: 3500,
            autoplayHoverPause: true,
            smartSpeed: 400,
            margin: 16,
            responsive: {
                0: { items: 1 },
                576: { items: 2 },
                768: { items: 3 },
                1024: { items: 4 },
                1280: { items: 4 }
            }
        });

        // Auto-initialize any datepickers on public pages
        if (typeof $.fn.datepicker !== 'undefined') {
            $('input[type="date"], input.datePicks').not('.hasDatepicker').each(function() {
                var el = this;
                try { el.type = 'text'; } catch(e) { $(el).attr('type', 'text'); }
                $(el).attr('autocomplete', 'off')
                     .datepicker({
                         dateFormat: 'yy-mm-dd',
                         changeMonth: true,
                         changeYear: true,
                         yearRange: "1920:+10",
                         showAnim: "fadeIn"
                     });
            });
        }
    });
</script>

<!--
    Support hub for the public portal. Loaded at the end of the footer so it
    is present on every page that uses this footer (home, login, and the
    single-member / single-holding / holding-search views) without each one
    having to know about it.

    A separate file from p/support_modal.php on purpose — see the note at the
    top of that one. The short version: nobody is signed in here, so nothing
    can be pre-filled except the parishad's own name, and the problem being
    reported is usually about one person's record rather than the parishad's
    data.

    print:hidden keeps it out of any printed document.
-->
<?php $this->load->view('front/partials/support_modal'); ?>

</body>
</html>
