    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-neutral-200 mt-auto py-5 text-center text-xs text-neutral-500">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-2">
            <div>
                &copy; <?= date('Y'); ?> <strong><?= htmlspecialchars($ins_info->ins_name ?? 'ইউনিয়ন পরিষদ', ENT_QUOTES, 'UTF-8'); ?></strong> &bull; সর্বস্বত্ব সংরক্ষিত
            </div>
            <div class="flex items-center gap-1.5 text-xs text-neutral-500">
                <span>কারিগরি সহায়তায়:</span>
                <a href="https://viretadev.com" target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center text-indigo-600 hover:text-indigo-700 font-medium transition-colors"
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

    <!-- Universal Dropdown and Balance Script -->
    <script>
        function positionDropdown(dropdown) {
            if (!dropdown) return;
            dropdown.style.left = '';
            dropdown.style.right = '';
            dropdown.style.transform = '';

            const rect = dropdown.getBoundingClientRect();
            const viewportWidth = window.innerWidth || document.documentElement.clientWidth;
            const padding = 12;

            if (rect.right > viewportWidth - padding) {
                dropdown.style.left = 'auto';
                dropdown.style.right = '0';
                
                const adjustedRect = dropdown.getBoundingClientRect();
                if (adjustedRect.left < padding) {
                    const shiftX = padding - adjustedRect.left;
                    dropdown.style.transform = 'translateX(' + shiftX + 'px)';
                }
            } else if (rect.left < padding) {
                dropdown.style.left = '0';
                dropdown.style.right = 'auto';
                const adjustedRect = dropdown.getBoundingClientRect();
                if (adjustedRect.left < padding) {
                    const shiftX = padding - adjustedRect.left;
                    dropdown.style.transform = 'translateX(' + shiftX + 'px)';
                }
            }
        }

        function toggleDropdown(id) {
            const dropdown = document.getElementById(id);
            if (!dropdown) return;
            
            const isOpening = dropdown.classList.contains('hidden');

            // Close all other open dropdowns first
            const allDropdowns = document.querySelectorAll('[id$="Content"]');
            allDropdowns.forEach(d => {
                if (d.id !== id && !d.classList.contains('hidden')) {
                    d.classList.add('hidden');
                    const btn = d.previousElementSibling;
                    if (btn) {
                        const caret = btn.querySelector('.ph-caret-down');
                        if (caret) caret.classList.remove('rotate-180');
                    }
                }
            });

            dropdown.classList.toggle('hidden');

            // Rotate caret indicator
            const btn = dropdown.previousElementSibling;
            if (btn) {
                const caret = btn.querySelector('.ph-caret-down');
                if (caret) {
                    if (isOpening) {
                        caret.classList.add('rotate-180');
                    } else {
                        caret.classList.remove('rotate-180');
                    }
                }
            }

            // Viewport boundary detection for small screens
            if (isOpening) {
                positionDropdown(dropdown);
            }
        }

        // Reposition on window resize if any dropdown is open
        window.addEventListener('resize', function() {
            const openDropdown = document.querySelector('[id$="Content"]:not(.hidden)');
            if (openDropdown) {
                positionDropdown(openDropdown);
            }
        });

        // Close dropdown when clicked outside
        window.addEventListener('click', function(e) {
            if (!e.target.closest('button[onclick^="toggleDropdown"]') && !e.target.closest('[id$="Content"]')) {
                const allDropdowns = document.querySelectorAll('[id$="Content"]');
                allDropdowns.forEach(d => {
                    if (!d.classList.contains('hidden')) {
                        d.classList.add('hidden');
                        const btn = d.previousElementSibling;
                        if (btn) {
                            const caret = btn.querySelector('.ph-caret-down');
                            if (caret) caret.classList.remove('rotate-180');
                        }
                    }
                });
            }
        });

        // jQuery UI Datepicker & Balance Query
        $(function() {
            if (typeof $.fn.datepicker !== 'undefined') {
                $(".datePicks").datepicker({
                    dateFormat: 'yy-mm-dd',
                    changeMonth: true,
                    changeYear: true,
                    yearRange: "1920:+10",
                    showAnim: "fadeIn"
                });
            }
            institute_present_balance();

            // GSAP subtle entrance for admin widgets
            if (typeof gsap !== 'undefined') {
                gsap.from('.grid > div, .grid > a, main > .bg-white', {
                    opacity: 0,
                    y: 10,
                    duration: 0.35,
                    stagger: 0.04,
                    ease: 'power2.out',
                    clearProps: 'all'
                });
            }
        });

        function institute_present_balance() {
            if (typeof $ !== 'undefined') {
                $.ajax({
                    type: "POST",
                    url: "balanceQuery",
                    data: "",
                    success: function (response) {
                        if (response) {
                            try {
                                var data = (typeof response === 'string') ? JSON.parse(response) : response;
                                var bal = (data.balance_bn !== undefined) 
                                    ? data.balance_bn 
                                    : ((data.total_add || 0) - (data.total_cut || 0));
                                $('.ins_total_bal_now').text(bal);
                            } catch (e) {
                                if (typeof response === 'string' && response.trim() !== '') {
                                    $('.ins_total_bal_now').text(response);
                                }
                            }
                        }
                    }
                });
            }
        }
    </script>

    <!-- Password visibility toggle for [data-saas-toggle-password] fields
         (view_profile, user_view, change_cm_sign, ...). Same contract as
         saas.js, hand-written and dependency-free. -->
    <script src="inc/assets/js/password-toggle.js"></script>
</body>
</html>
