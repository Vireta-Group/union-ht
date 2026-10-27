<!-- Breadcrumb & Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-neutral-200">
    <div>
        <nav class="flex items-center gap-2 text-xs text-neutral-500 mb-1">
            <a href="dashboard" class="hover:text-indigo-600 transition flex items-center gap-1">
                <i class="ph ph-house text-sm"></i> ড্যাশবোর্ড
            </a>
            <i class="ph ph-caret-right text-[10px] text-neutral-400"></i>
            <span class="text-neutral-800 font-medium">প্রোফাইল</span>
        </nav>
        <h1 class="text-xl sm:text-2xl font-bold text-neutral-900 tracking-tight flex items-center gap-2">
            <span class="w-2.5 h-6 bg-indigo-600 rounded-full inline-block"></span>
            আমার প্রোফাইল ও নিরাপত্তা
        </h1>
        <p class="text-xs sm:text-sm text-neutral-500 mt-0.5">ব্যক্তিগত তথ্য, লগইন তথ্য ও পাসওয়ার্ড ব্যবস্থাপনা</p>
    </div>
</div>

<!-- Tab Navigation -->
<div class="flex gap-2 border-b border-neutral-200 pb-0">
    <button type="button" id="tab-profile-btn" onclick="switchTab('profile')" class="tab-btn inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold border-b-2 border-indigo-600 text-indigo-700 -mb-px">
        <i class="ph-bold ph-user-circle text-sm"></i> প্রোফাইল তথ্য
    </button>
    <button type="button" id="tab-security-btn" onclick="switchTab('security')" class="tab-btn inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold border-b-2 border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300 -mb-px transition">
        <i class="ph-bold ph-shield-check text-sm"></i> নিরাপত্তা
    </button>
</div>

<!-- Tab: Profile Information -->
<div id="tab-profile" class="tab-panel">
    <div class="bg-white rounded-2xl border border-neutral-200/80 shadow-sm p-5 sm:p-6 space-y-5">
        <div class="flex items-center gap-2 pb-3 border-b border-neutral-100">
            <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                <i class="ph-bold ph-user-circle text-base"></i>
            </div>
            <h3 class="text-sm font-bold text-neutral-900">ব্যক্তিগত তথ্য আপডেট</h3>
        </div>

        <form class="space-y-4" action="update_profile" method="post">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-neutral-700 mb-1.5">পূর্ণ নাম</label>
                    <input type="text" name="full_name" class="w-full text-xs px-3.5 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition" value="<?= htmlspecialchars($loggedUser['userName'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="পূর্ণ নাম">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-neutral-700 mb-1.5">লগইন ইউজারনেম</label>
                    <input type="text" name="iden_type" class="w-full text-xs px-3.5 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition" value="<?= htmlspecialchars($loggedUser['user_u_type'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="ইউজারনেম">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-neutral-700 mb-1.5">মোবাইল নম্বর</label>
                    <input type="text" name="phone_no" class="w-full text-xs px-3.5 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition" value="<?= htmlspecialchars($loggedUser['userPhone'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="০১XXXXXXXXX">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-neutral-700 mb-1.5">ইমেইল</label>
                    <input type="email" name="e_mail_no" class="w-full text-xs px-3.5 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition" value="<?= htmlspecialchars($loggedUser['userEmail'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="email@example.com">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-neutral-700 mb-1.5">ঠিকানা</label>
                    <input type="text" name="f_addr_ss" class="w-full text-xs px-3.5 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition" value="<?= htmlspecialchars($loggedUser['userAddress'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="সম্পূর্ণ ঠিকানা">
                </div>
            </div>
            <div class="flex justify-end pt-2">
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition">
                    <i class="ph-bold ph-floppy-disk text-sm"></i> তথ্য আপডেট করুন
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Tab: Security -->
<div id="tab-security" class="tab-panel hidden">
    <div class="bg-white rounded-2xl border border-neutral-200/80 shadow-sm p-5 sm:p-6 space-y-5">
        <div class="flex items-center gap-2 pb-3 border-b border-neutral-100">
            <div class="w-7 h-7 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center">
                <i class="ph-bold ph-shield-check text-base"></i>
            </div>
            <h3 class="text-sm font-bold text-neutral-900">পাসওয়ার্ড পরিবর্তন</h3>
        </div>

        <div class="p-3 bg-amber-50 border border-amber-200/60 rounded-xl text-xs text-amber-700">
            <i class="ph ph-warning text-sm mr-1"></i>
            শক্তিশালী পাসওয়ার্ড ব্যবহার করুন। পুরানো পাসওয়ার্ড সঠিক না হলে পরিবর্তন হবে না।
        </div>

        <div class="space-y-3 max-w-sm">
            <div>
                <label class="block text-xs font-semibold text-neutral-700 mb-1.5">পুরানো পাসওয়ার্ড</label>
                <div class="relative">
                    <input type="password" data-saas-password class="w-full text-xs px-3.5 pr-10 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 transition old_pass_type" placeholder="বর্তমান পাসওয়ার্ড">
                    <button type="button" data-saas-toggle-password class="absolute right-2.5 top-1/2 -translate-y-1/2 text-neutral-400 hover:text-neutral-600 cursor-pointer" aria-label="পাসওয়ার্ড দেখান">
                        <span data-saas-eye="show"><i class="ph ph-eye text-sm"></i></span>
                        <span data-saas-eye="hide" class="hidden"><i class="ph ph-eye-slash text-sm"></i></span>
                    </button>
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-neutral-700 mb-1.5">নতুন পাসওয়ার্ড</label>
                <div class="relative">
                    <input type="password" data-saas-password class="w-full text-xs px-3.5 pr-10 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 transition types_new_pass" placeholder="নতুন পাসওয়ার্ড">
                    <button type="button" data-saas-toggle-password class="absolute right-2.5 top-1/2 -translate-y-1/2 text-neutral-400 hover:text-neutral-600 cursor-pointer" aria-label="পাসওয়ার্ড দেখান">
                        <span data-saas-eye="show"><i class="ph ph-eye text-sm"></i></span>
                        <span data-saas-eye="hide" class="hidden"><i class="ph ph-eye-slash text-sm"></i></span>
                    </button>
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-neutral-700 mb-1.5">নতুন পাসওয়ার্ড নিশ্চিত করুন</label>
                <div class="relative">
                    <input type="password" data-saas-password class="w-full text-xs px-3.5 pr-10 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 transition types_confirm_pass" placeholder="পাসওয়ার্ড পুনরায় লিখুন">
                    <button type="button" data-saas-toggle-password class="absolute right-2.5 top-1/2 -translate-y-1/2 text-neutral-400 hover:text-neutral-600 cursor-pointer" aria-label="পাসওয়ার্ড দেখান">
                        <span data-saas-eye="show"><i class="ph ph-eye text-sm"></i></span>
                        <span data-saas-eye="hide" class="hidden"><i class="ph ph-eye-slash text-sm"></i></span>
                    </button>
                </div>
            </div>
            <div>
                <button type="button" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 shadow-sm transition change_pass_submitbtn">
                    <i class="ph-bold ph-lock-key-open text-sm"></i> পাসওয়ার্ড পরিবর্তন করুন
                </button>
            </div>
        </div>
    </div>
</div>

<script src="inc/assets/vendor/sweetalert2/dist/sweetalert2.all.min.js"></script>
<script>
    function switchTab(tab) {
        document.querySelectorAll('.tab-panel').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('.tab-btn').forEach(el => {
            el.classList.remove('border-indigo-600', 'text-indigo-700');
            el.classList.add('border-transparent', 'text-neutral-500');
        });
        document.getElementById('tab-' + tab).classList.remove('hidden');
        let btn = document.getElementById('tab-' + tab + '-btn');
        btn.classList.add('border-indigo-600', 'text-indigo-700');
        btn.classList.remove('border-transparent', 'text-neutral-500');
    }

    $(document).on('click', '.change_pass_submitbtn', function () {
        let old_pass = $('.old_pass_type').val();
        let new_take_pass = $('.types_new_pass').val();
        let new_confirm_pass = $('.types_confirm_pass').val();

        $.ajax({
            type: "post",
            url: "change_pass",
            data: { old: old_pass, new: new_take_pass, cnfrm: new_confirm_pass },
            success: function (r) {
                $('.old_pass_type,.types_new_pass,.types_confirm_pass').val('');
                Swal.fire({
                    title: `<strong>${r.msg}</strong>`,
                    icon: r.icon,
                    html: `${r.msg}`,
                    showConfirmButton: false,
                });
            }
        });
    });
</script>
