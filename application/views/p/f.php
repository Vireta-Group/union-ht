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

    <!-- Tenant ViretaDev Review Modal -->
    <div id="tenantReviewModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-neutral-900/60 backdrop-blur-xs items-center justify-center p-4">
        <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl border border-neutral-200 overflow-hidden" onclick="event.stopPropagation()">
            <!-- Modal Header -->
            <div class="px-6 py-4 bg-linear-to-r from-amber-500/10 via-indigo-50/50 to-white border-b border-neutral-100 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-amber-500/20 text-amber-600 flex items-center justify-center">
                        <i class="ph-bold ph-star text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-neutral-900">ViretaDev প্ল্যাটফর্ম রিভিউ ও মতামত</h3>
                        <p class="text-[11px] text-neutral-500">আপনার মূল্যবান মতামত সরাসরি প্ল্যাটফর্মে যুক্ত হবে</p>
                    </div>
                </div>
                <button type="button" onclick="closeTenantReviewModal()" class="w-8 h-8 rounded-lg text-neutral-400 hover:text-neutral-700 hover:bg-neutral-100 flex items-center justify-center transition-colors">
                    <i class="ph-bold ph-x text-base"></i>
                </button>
            </div>

            <!-- Modal Body / Form -->
            <form id="tenantReviewForm" onsubmit="submitTenantReview(event)" class="p-6 space-y-4 text-xs">
                <!-- Tenant & User Info Summary (Auto-detected) -->
                <div class="p-3 bg-neutral-50 rounded-xl border border-neutral-200/80 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="ph-bold ph-buildings text-neutral-500 text-sm"></i>
                        <div>
                            <span class="text-[10px] text-neutral-500 block">ইউনিয়ন পরিষদ:</span>
                            <span class="font-bold text-neutral-900"><?= htmlspecialchars($ins_info->ins_name ?? 'ইউনিয়ন পরিষদ', ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                        যাচাইকৃত ইউপি
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-neutral-700 mb-1">আপনার নাম <span class="text-rose-500">*</span></label>
                        <input type="text" name="author" id="trv_author" required value="<?= htmlspecialchars($loggedUser['userName'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="w-full px-3 py-2 bg-white border border-neutral-300 rounded-xl focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none text-xs">
                    </div>
                    <div>
                        <label class="block font-semibold text-neutral-700 mb-1">পদবী / ভূমিকা</label>
                        <input type="text" name="role" id="trv_role" value="<?= htmlspecialchars($loggedUser['userGroupName'] ?? 'ইউপি কর্মকর্তা', ENT_QUOTES, 'UTF-8'); ?>" class="w-full px-3 py-2 bg-white border border-neutral-300 rounded-xl focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none text-xs" placeholder="উদাঃ ইউপি সচিব / উদ্যোক্তা">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-neutral-700 mb-1">ব্যবহৃত প্রধান সেবা</label>
                        <select name="service" id="trv_service" class="w-full px-3 py-2 bg-white border border-neutral-300 rounded-xl focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none text-xs">
                            <option value="সনদ ও ডিজিটাল ট্রেড লাইসেন্স" selected>সনদ ও ডিজিটাল ট্রেড লাইসেন্স</option>
                            <option value="হোল্ডিং ট্যাক্স ও অনলাইন রশিদ">হোল্ডিং ট্যাক্স ও অনলাইন রশিদ</option>
                            <option value="নাগরিক ডাটাবেজ ও ওয়ার্ড ব্যবস্থাপনা">নাগরিক ডাটাবেজ ও ওয়ার্ড ব্যবস্থাপনা</option>
                            <option value="সার্বিক ইউনিয়ন ডিজিটালাইজেশন">সার্বিক ইউনিয়ন ডিজিটালাইজেশন</option>
                            <option value="ক্লাউড ব্যাকআপ ও দ্রুত সাপোর্ট">ক্লাউড ব্যাকআপ ও দ্রুত সাপোর্ট</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-neutral-700 mb-1">প্রাপ্ত মূল সুবিধা / প্রভাব</label>
                        <input type="text" name="impact" id="trv_impact" class="w-full px-3 py-2 bg-white border border-neutral-300 rounded-xl focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none text-xs" placeholder="উদাঃ ট্যাক্স আদায় দ্বিগুণ / সেবা সময় হ্রাস">
                    </div>
                </div>

                <!-- Star Rating -->
                <div>
                    <label class="block font-semibold text-neutral-700 mb-1">রেটিং প্রদান করুন</label>
                    <div class="flex items-center gap-1.5" id="trv_stars">
                        <button type="button" onclick="setTenantRating(1)" class="text-amber-400 hover:scale-110 transition-transform"><i class="ph-fill ph-star text-xl"></i></button>
                        <button type="button" onclick="setTenantRating(2)" class="text-amber-400 hover:scale-110 transition-transform"><i class="ph-fill ph-star text-xl"></i></button>
                        <button type="button" onclick="setTenantRating(3)" class="text-amber-400 hover:scale-110 transition-transform"><i class="ph-fill ph-star text-xl"></i></button>
                        <button type="button" onclick="setTenantRating(4)" class="text-amber-400 hover:scale-110 transition-transform"><i class="ph-fill ph-star text-xl"></i></button>
                        <button type="button" onclick="setTenantRating(5)" class="text-amber-400 hover:scale-110 transition-transform"><i class="ph-fill ph-star text-xl"></i></button>
                        <span id="trv_rating_text" class="text-xs font-bold text-amber-600 ml-2">৫.০ (চমৎকার)</span>
                    </div>
                    <input type="hidden" name="rating" id="trv_rating_val" value="5">
                </div>

                <!-- Comments -->
                <div>
                    <label class="block font-semibold text-neutral-700 mb-1">আপনার বিস্তারিত মন্তব্য ও পরামর্শ <span class="text-rose-500">*</span></label>
                    <textarea name="content" id="trv_content" rows="3" required class="w-full px-3 py-2 bg-white border border-neutral-300 rounded-xl focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none text-xs resize-none" placeholder="সফটওয়্যার ব্যবহারের অভিজ্ঞতা কেমন এবং ইউনিয়ন পরিচালনায় কি কি সুবিধা পাচ্ছেন..."></textarea>
                </div>

                <!-- Status alert box -->
                <div id="trv_msg_box" class="hidden p-3 rounded-xl text-xs"></div>

                <!-- Footer / Actions -->
                <div class="pt-2 flex items-center justify-end gap-2 border-t border-neutral-100">
                    <button type="button" onclick="closeTenantReviewModal()" class="px-4 py-2 rounded-xl border border-neutral-200 text-neutral-700 hover:bg-neutral-50 font-medium transition-colors">
                        বাতিল
                    </button>
                    <button type="submit" id="trv_submit_btn" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold shadow-xs transition-colors flex items-center gap-1.5 cursor-pointer">
                        <i class="ph-bold ph-paper-plane-tilt"></i>
                        <span>রিভিউ জমা দিন</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tenant Review Script -->
    <script>
        var currentRating = 5;
        var ratingLabels = {
            1: '১.০ (খুব খারাপ)',
            2: '২.০ (উন্নতি দরকার)',
            3: '৩.০ (মোটামুটি)',
            4: '৪.০ (ভালো)',
            5: '৫.০ (অসাধারণ ও চমৎকার)'
        };

        function openTenantReviewModal() {
            var modal = document.getElementById('tenantReviewModal');
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                var content = document.getElementById('trv_content');
                if (content) setTimeout(function() { content.focus(); }, 150);
            }
        }

        function closeTenantReviewModal() {
            var modal = document.getElementById('tenantReviewModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
            var msgBox = document.getElementById('trv_msg_box');
            if (msgBox) {
                msgBox.className = 'hidden';
                msgBox.innerHTML = '';
            }
        }

        function setTenantRating(stars) {
            currentRating = stars;
            var valInput = document.getElementById('trv_rating_val');
            if (valInput) valInput.value = stars;
            var lbl = document.getElementById('trv_rating_text');
            if (lbl) lbl.textContent = ratingLabels[stars] || stars + '.০';

            var starBtns = document.querySelectorAll('#trv_stars button');
            starBtns.forEach(function(btn, idx) {
                var icon = btn.querySelector('i');
                if (idx < stars) {
                    icon.className = 'ph-fill ph-star text-xl text-amber-400';
                } else {
                    icon.className = 'ph-bold ph-star text-xl text-neutral-300';
                }
            });
        }

        function submitTenantReview(e) {
            e.preventDefault();
            var btn = document.getElementById('trv_submit_btn');
            var msgBox = document.getElementById('trv_msg_box');
            var form = document.getElementById('tenantReviewForm');
            if (!form) return;

            btn.disabled = true;
            btn.innerHTML = '<i class="ph-bold ph-spinner animate-spin"></i><span>জমা হচ্ছে...</span>';

            var formData = new FormData(form);

            fetch('api_submit_review', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                btn.disabled = false;
                btn.innerHTML = '<i class="ph-bold ph-paper-plane-tilt"></i><span>রিভিউ জমা দিন</span>';
                if (data.success) {
                    msgBox.className = 'p-3 rounded-xl text-xs bg-emerald-50 text-emerald-800 border border-emerald-200 block';
                    msgBox.innerHTML = '<div class="flex items-center gap-2"><i class="ph-bold ph-check-circle text-base text-emerald-600"></i><span>' + (data.message || 'আপনার রিভিউটি সফলভাবে জমা হয়েছে!') + '</span></div>';
                    setTimeout(function() {
                        closeTenantReviewModal();
                        form.reset();
                        setTenantRating(5);
                    }, 2000);
                } else {
                    msgBox.className = 'p-3 rounded-xl text-xs bg-rose-50 text-rose-800 border border-rose-200 block';
                    msgBox.innerHTML = '<div class="flex items-center gap-2"><i class="ph-bold ph-warning-circle text-base text-rose-600"></i><span>' + (data.message || 'রিভিউ জমা দিতে সমস্যা হয়েছে।') + '</span></div>';
                }
            })
            .catch(function(err) {
                btn.disabled = false;
                btn.innerHTML = '<i class="ph-bold ph-paper-plane-tilt"></i><span>রিভিউ জমা দিন</span>';
                msgBox.className = 'p-3 rounded-xl text-xs bg-rose-50 text-rose-800 border border-rose-200 block';
                msgBox.innerHTML = '<div class="flex items-center gap-2"><i class="ph-bold ph-warning-circle text-base text-rose-600"></i><span>সার্ভারের সাথে সংযোগ স্থাপন করা যায়নি।</span></div>';
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            var modal = document.getElementById('tenantReviewModal');
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        closeTenantReviewModal();
                    }
                });
            }
        });
    </script>

    <!-- Password visibility toggle for [data-saas-toggle-password] fields
         (view_profile, user_view, change_cm_sign, ...). Same contract as
         saas.js, hand-written and dependency-free. -->
    <script src="inc/assets/js/password-toggle.js"></script>
</body>
</html>
