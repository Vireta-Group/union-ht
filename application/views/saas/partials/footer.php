<?php
/**
 * Product-site footer.
 *
 * Ported from resources/js/components/Footer.jsx. As with the navbar, every
 * <button onClick={onNavigate}> became a real <a href>, so the footer works
 * without JavaScript and each link is a proper document request.
 *
 * @var string $saas_path  Active route, e.g. '/about'.
 */

$saas_path = $saas_path ?? '/';

$saas_quick_links = array(
    '/'         => 'হোম পেজ',
    '/about'    => 'আমাদের সম্পর্কে (About Us)',
    '/plans'    => 'সাবস্ক্রিপশন প্ল্যান (Plans)',
    '/reviews'  => 'রিভিউ ও সাফল্যগাঁথা (Reviews)',
    '/faq'      => 'সচরাচর জিজ্ঞাসা (FAQ)',
    '/policy'   => 'পলিসি ও শর্তাবলী (Policies)',
    '/contact'  => 'যোগাযোগ (Contact)',
);
?>
        <footer class="mt-auto border-t border-neutral-800 bg-neutral-950 py-10 text-xs text-neutral-400">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-8 border-b border-neutral-800 pb-8 md:grid-cols-12">

                    <!-- Brand Info -->
                    <div class="space-y-3 md:col-span-5">
                        <div class="flex items-center gap-2.5">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-600 text-lg font-bold text-white">
                                V
                            </div>
                            <span class="text-lg font-extrabold text-white">
                                Vireta<span class="text-indigo-400">Dev</span> SaaS
                            </span>
                        </div>
                        <p class="max-w-sm text-xs leading-relaxed text-neutral-400">
                            ইউনিয়ন পরিষদসমূহের প্রশাসনিক ও নাগরিক সেবা ডিজিটালকরণের নির্ভরযোগ্য প্রাইভেট ক্লাউড প্ল্যাটফর্ম।
                        </p>

                        <!-- Social Media Links (Official Brand Colors & Larger Size) -->
                        <div class="flex items-center gap-2.5 pt-2">
                            <a href="https://facebook.com" target="_blank" rel="noopener noreferrer"
                               class="flex h-10 w-10 items-center justify-center rounded-xl transition-all duration-200 hover:scale-105"
                               style="background: rgba(24, 119, 242, 0.1); border: 1px solid rgba(24, 119, 242, 0.25); color: #1877F2;"
                               onmouseover="this.style.background='#1877F2'; this.style.color='#FFFFFF';"
                               onmouseout="this.style.background='rgba(24, 119, 242, 0.1)'; this.style.color='#1877F2';"
                               title="ফেসবুক পেজ" aria-label="Facebook">
                                <i class="ph-fill ph-facebook-logo" style="font-size: 20px;"></i>
                            </a>
                            <a href="https://instagram.com" target="_blank" rel="noopener noreferrer"
                               class="flex h-10 w-10 items-center justify-center rounded-xl transition-all duration-200 hover:scale-105"
                               style="background: rgba(225, 48, 108, 0.1); border: 1px solid rgba(225, 48, 108, 0.25); color: #E1306C;"
                               onmouseover="this.style.background='linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%)'; this.style.color='#FFFFFF';"
                               onmouseout="this.style.background='rgba(225, 48, 108, 0.1)'; this.style.color='#E1306C';"
                               title="ইনস্টাগ্রাম" aria-label="Instagram">
                                <i class="ph-fill ph-instagram-logo" style="font-size: 20px;"></i>
                            </a>
                            <a href="https://linkedin.com" target="_blank" rel="noopener noreferrer"
                               class="flex h-10 w-10 items-center justify-center rounded-xl transition-all duration-200 hover:scale-105"
                               style="background: rgba(10, 102, 194, 0.1); border: 1px solid rgba(10, 102, 194, 0.25); color: #0A66C2;"
                               onmouseover="this.style.background='#0A66C2'; this.style.color='#FFFFFF';"
                               onmouseout="this.style.background='rgba(10, 102, 194, 0.1)'; this.style.color='#0A66C2';"
                               title="লিংকডইন" aria-label="LinkedIn">
                                <i class="ph-fill ph-linkedin-logo" style="font-size: 20px;"></i>
                            </a>
                            <a href="https://twitter.com" target="_blank" rel="noopener noreferrer"
                               class="flex h-10 w-10 items-center justify-center rounded-xl transition-all duration-200 hover:scale-105"
                               style="background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.2); color: #FFFFFF;"
                               onmouseover="this.style.background='#FFFFFF'; this.style.color='#000000';"
                               onmouseout="this.style.background='rgba(255, 255, 255, 0.08)'; this.style.color='#FFFFFF';"
                               title="এক্স (টুইটার)" aria-label="X (Twitter)">
                                <i class="ph-fill ph-x-logo" style="font-size: 19px;"></i>
                            </a>
                            <a href="https://youtube.com" target="_blank" rel="noopener noreferrer"
                               class="flex h-10 w-10 items-center justify-center rounded-xl transition-all duration-200 hover:scale-105"
                               style="background: rgba(255, 0, 0, 0.1); border: 1px solid rgba(255, 0, 0, 0.25); color: #FF0000;"
                               onmouseover="this.style.background='#FF0000'; this.style.color='#FFFFFF';"
                               onmouseout="this.style.background='rgba(255, 0, 0, 0.1)'; this.style.color='#FF0000';"
                               title="ইউটিউব" aria-label="YouTube">
                                <i class="ph-fill ph-youtube-logo" style="font-size: 20px;"></i>
                            </a>
                            <a href="https://github.com" target="_blank" rel="noopener noreferrer"
                               class="flex h-10 w-10 items-center justify-center rounded-xl transition-all duration-200 hover:scale-105"
                               style="background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.2); color: #E6EDF3;"
                               onmouseover="this.style.background='rgba(255, 255, 255, 0.2)'; this.style.color='#FFFFFF';"
                               onmouseout="this.style.background='rgba(255, 255, 255, 0.08)'; this.style.color='#E6EDF3';"
                               title="গিটহাব" aria-label="GitHub">
                                <i class="ph-fill ph-github-logo" style="font-size: 20px;"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Quick Links -->
                    <div class="md:col-span-3">
                        <h4 class="mb-3 font-bold tracking-wider text-white uppercase">দ্রুত পেজ লিংক</h4>
                        <ul class="space-y-2">
                            <?php foreach ($saas_quick_links as $saas_href => $saas_label): ?>
                                <li>
                                    <a href="<?= site_url(ltrim($saas_href, '/')) ?>"
                                       class="text-neutral-400 transition-colors hover:text-white"><?= $saas_label ?></a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <!-- Direct Support Column -->
                    <div class="md:col-span-4">
                        <h4 class="mb-3 font-bold tracking-wider text-white uppercase">সরাসরি সহায়তা</h4>
                        <p class="mb-2 text-neutral-400">
                            যেকোনো কারিগরি সহায়তা বা তথ্যের জন্য আমাদের সাথে সরাসরি যোগাযোগ করুন।
                        </p>
                        <button type="button" data-toggle="modal" data-target="#supportModal"
                                class="ht-btn ht-btn-secondary text-xs">
                            <?= icon('headset', 16, 'bold', 'text-indigo-400') ?>
                            <span>২৪/৭ হেল্পডেস্ক হাব</span>
                        </button>
                    </div>

                </div>

                <!-- Bottom Bar -->
                <div class="flex flex-col items-center justify-between gap-3 pt-6 text-neutral-500 sm:flex-row">
                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-neutral-400">
                        <span>&copy; <?= date('Y') ?> <a href="https://viretadev.com" target="_blank" rel="noopener noreferrer" class="text-neutral-300 font-medium hover:text-white transition-colors">ViretaDev Technologies</a>. সর্বস্বত্ব সংরক্ষিত।</span>
                        <span class="text-neutral-600 hidden sm:inline">&bull;</span>
                        <span class="inline-flex items-center gap-1.5">
                            <span class="text-neutral-400">কারিগরি সহায়তায়:</span>
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
                        </span>
                    </div>
                    <div class="flex flex-wrap items-center gap-4 text-[11px]">
                        <a href="<?= site_url('faq') ?>" class="transition-colors hover:text-white">FAQ</a>
                        <span>&bull;</span>
                        <a href="<?= site_url('privacy') ?>" class="transition-colors hover:text-white">প্রাইভেসি পলিসি</a>
                        <span>&bull;</span>
                        <a href="<?= site_url('terms') ?>" class="transition-colors hover:text-white">সেবার শর্তাবলী</a>
                        <span>&bull;</span>
                        <?php
                        $CI_foot =& get_instance();
                        $foot_logged = isset($CI_foot->ion_auth) && $CI_foot->ion_auth->logged_in();
                        $foot_user = $foot_logged ? $CI_foot->ion_auth->user()->row() : null;
                        $foot_is_sadmin = $foot_logged && ($CI_foot->ion_auth->in_group('sadmin') || ($foot_user->u_type ?? '') === 'sadmin');
                        $foot_target = $foot_logged ? (function_exists('tenant_login_url') ? tenant_login_url($foot_user) : ($foot_is_sadmin ? site_url('super_admin') : site_url('dashboard'))) : site_url('login');
                        $foot_text = $foot_logged ? ($foot_is_sadmin ? 'সুপার অ্যাডমিন প্যানেল' : 'ড্যাশবোর্ড') : 'প্যানেল লগইন';
                        ?>
                        <a href="<?= $foot_target ?>" class="transition-colors hover:text-white"><?= $foot_text ?></a>
                        <span>&bull;</span>
                        <button type="button" data-toggle="modal" data-target="#supportModal"
                                class="cursor-pointer transition-colors hover:text-white">সাপোর্ট</button>
                    </div>
                </div>

                <script>
                (function() {
                    function getGraphemes(str) {
                        if (typeof Intl !== 'undefined' && Intl.Segmenter) {
                            try {
                                var seg = new Intl.Segmenter('bn', { granularity: 'grapheme' });
                                return Array.from(seg.segment(str)).map(function(s) { return s.segment; });
                            } catch (e) {}
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
            </div>
        </footer>
