<!-- Breadcrumb & Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-neutral-200">
    <div>
        <nav class="flex items-center gap-2 text-xs text-neutral-500 mb-1">
            <a href="dashboard" class="hover:text-indigo-600 transition flex items-center gap-1">
                <i class="ph ph-house text-sm"></i> ড্যাশবোর্ড
            </a>
            <i class="ph ph-caret-right text-[10px] text-neutral-400"></i>
            <span class="text-neutral-800 font-medium">ব্যবহারকারী ও আরব্যাক ব্যবস্থাপনা</span>
        </nav>
        <h1 class="text-xl sm:text-2xl font-bold text-neutral-900 tracking-tight flex items-center gap-2">
            <span class="w-2.5 h-6 bg-indigo-600 rounded-full inline-block"></span>
            ব্যবহারকারী ও ভূমিকা (RBAC) ব্যবস্থাপনা
        </h1>
        <p class="text-xs sm:text-sm text-neutral-500 mt-0.5">ইউনিয়নের কর্মকর্তা-কর্মচারীদের ভূমিকা নির্ধারণ, পারমিশন ম্যাট্রিক্স ও অ্যাকাউন্ট নিয়ন্ত্রণ</p>
    </div>
    <div>
        <button type="button" data-toggle="modal" data-target="#add_new_user" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 active:scale-98 shadow-xs transition cursor-pointer">
            <i class="ph-bold ph-user-plus text-xs"></i>
            <span>নতুন ব্যবহারকারী যোগ করুন</span>
        </button>
    </div>
</div>

<!-- Tabs Navigation -->
<div class="flex items-center gap-2 border-b border-neutral-200 text-sm">
    <button type="button" onclick="switchUserTab('users')" id="tabBtnUsers" class="px-4 py-2.5 font-bold text-indigo-600 border-b-2 border-indigo-600 flex items-center gap-2 transition-colors cursor-pointer">
        <i class="ph-bold ph-users text-base"></i>
        <span>ব্যবহারকারী তালিকা ও রোল নির্ধারণ</span>
    </button>
    <button type="button" onclick="switchUserTab('matrix')" id="tabBtnMatrix" class="px-4 py-2.5 font-medium text-neutral-500 hover:text-neutral-800 flex items-center gap-2 transition-colors cursor-pointer">
        <i class="ph-bold ph-shield-check text-base"></i>
        <span>ভূমিকা ও অ্যাক্সেস ম্যাট্রিক্স (RBAC Matrix)</span>
    </button>
</div>

<!-- Status Message -->
<div class="fade_msg hidden">
    <div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-xs font-semibold alert_msg"></div>
</div>

<!-- TAB 1: USERS LIST & ROLE MANAGEMENT -->
<div id="tabContentUsers" class="space-y-4">
    <div class="bg-white rounded-2xl border border-neutral-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-neutral-50/80 border-b border-neutral-200/80 text-neutral-600 font-bold">
                        <th class="py-3 px-4 w-12 text-center">ক্রমিক</th>
                        <th class="py-3 px-4">নাম ও কমিশন</th>
                        <th class="py-3 px-4 text-center">মোবাইল নম্বর</th>
                        <th class="py-3 px-4">ভূমিকা / পদবী (Role)</th>
                        <th class="py-3 px-4 text-center">সক্রিয় অবস্থা</th>
                        <th class="py-3 px-4 text-center w-28">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 html_tr_datas">
                    <tr>
                        <td colspan="6" class="py-12 text-center text-neutral-400">
                            <div class="flex flex-col items-center gap-2">
                                <i class="ph ph-spinner animate-spin text-2xl text-indigo-600"></i>
                                <span>তথ্য লোড হচ্ছে...</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- TAB 2: RBAC PERMISSIONS MATRIX -->
<div id="tabContentMatrix" class="hidden space-y-5">
    
    <!-- Matrix Header Card -->
    <div class="bg-gradient-to-r from-indigo-900 via-indigo-800 to-neutral-900 rounded-2xl p-6 text-white shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="space-y-1">
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-indigo-500/30 text-indigo-200 border border-indigo-400/30 inline-block mb-1">
                নিরাপত্তা ও অ্যাক্সেস পলিসি
            </span>
            <h2 class="text-lg font-bold">ইউনিয়ন পরিষদ আরব্যাক (RBAC) ও দ্বি-স্তরীয় অনুমোদন মডেল</h2>
            <p class="text-xs text-indigo-200 max-w-2xl">
                কোন পদের ব্যবহারকারী সিস্টেমে কী কী সুবিধা ব্যবহার করতে পারবেন এবং কার অনুমোদন ক্ষমতা কতটুকু তা নিচে বিস্তারিত তুলে ধরা হলো।
            </p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <div class="text-right">
                <span class="block text-[11px] text-indigo-300">বর্তমান সক্রিয় ইউনিয়ন</span>
                <strong class="text-sm font-bold text-white"><?= htmlspecialchars($ins_info->ins_name ?? 'ইউনিয়ন পরিষদ', ENT_QUOTES, 'UTF-8'); ?></strong>
            </div>
        </div>
    </div>

    <!-- Permissions Table -->
    <div class="bg-white rounded-2xl border border-neutral-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-neutral-50 text-neutral-800 border-b border-neutral-200">
                        <th class="py-3.5 px-4 font-bold uppercase text-[11px] tracking-wider w-1/4">কার্যক্রম / মডিউল</th>
                        <th class="py-3.5 px-4 text-center font-bold text-amber-700 bg-amber-50/50 border-x border-neutral-200/60">
                            ইউপি চেয়ারম্যান<br><span class="text-[10px] font-normal text-amber-800">(uphead)</span>
                        </th>
                        <th class="py-3.5 px-4 text-center font-bold text-sky-700 bg-sky-50/50 border-r border-neutral-200/60">
                            ইউপি সচিব<br><span class="text-[10px] font-normal text-sky-800">(upsocib)</span>
                        </th>
                        <th class="py-3.5 px-4 text-center font-bold text-emerald-700 bg-emerald-50/50 border-r border-neutral-200/60">
                            ইউডিসি উদ্যোক্তা<br><span class="text-[10px] font-normal text-emerald-800">(udc)</span>
                        </th>
                        <th class="py-3.5 px-4 text-center font-bold text-indigo-700 bg-indigo-50/50">
                            ট্যাক্স আদায়কারী<br><span class="text-[10px] font-normal text-indigo-800">(collect)</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100">
                    
                    <!-- Row 1: Dashboard -->
                    <tr class="hover:bg-neutral-50/60 transition">
                        <td class="py-3 px-4 font-semibold text-neutral-900">
                            ১. ড্যাশবোর্ড ও সার্বিক পরিসংখ্যান
                            <span class="block text-[11px] font-normal text-neutral-500">ইউনিয়নের সামগ্রিক ডেটা বিশ্লেষণ ও পর্যবেক্ষণ</span>
                        </td>
                        <td class="py-3 px-4 text-center bg-amber-50/20 border-x border-neutral-100">
                            <span class="inline-flex items-center gap-1 text-emerald-600 font-bold"><i class="ph-bold ph-check-circle text-base"></i> পূর্ণ অ্যাক্সেস</span>
                        </td>
                        <td class="py-3 px-4 text-center bg-sky-50/20 border-r border-neutral-100">
                            <span class="inline-flex items-center gap-1 text-emerald-600 font-bold"><i class="ph-bold ph-check-circle text-base"></i> পূর্ণ অ্যাক্সেস</span>
                        </td>
                        <td class="py-3 px-4 text-center bg-emerald-50/20 border-r border-neutral-100">
                            <span class="inline-flex items-center gap-1 text-emerald-600 font-bold"><i class="ph-bold ph-check-circle text-base"></i> নিজস্ব তথ্য</span>
                        </td>
                        <td class="py-3 px-4 text-center bg-indigo-50/20">
                            <span class="inline-flex items-center gap-1 text-emerald-600 font-bold"><i class="ph-bold ph-check-circle text-base"></i> নিজস্ব তথ্য</span>
                        </td>
                    </tr>

                    <!-- Row 2: Tax Collection -->
                    <tr class="hover:bg-neutral-50/60 transition">
                        <td class="py-3 px-4 font-semibold text-neutral-900">
                            ২. হোল্ডিং ট্যাক্স আদায় ও অনুসন্ধান
                            <span class="block text-[11px] font-normal text-neutral-500">ট্যাক্স সংগ্রহ, রসিদ প্রিন্ট ও খানা রেকর্ড</span>
                        </td>
                        <td class="py-3 px-4 text-center bg-amber-50/20 border-x border-neutral-100">
                            <span class="inline-flex items-center gap-1 text-emerald-600 font-bold"><i class="ph-bold ph-check-circle text-base"></i> সম্পূর্ণ</span>
                        </td>
                        <td class="py-3 px-4 text-center bg-sky-50/20 border-r border-neutral-100">
                            <span class="inline-flex items-center gap-1 text-emerald-600 font-bold"><i class="ph-bold ph-check-circle text-base"></i> সম্পূর্ণ</span>
                        </td>
                        <td class="py-3 px-4 text-center bg-emerald-50/20 border-r border-neutral-100">
                            <span class="inline-flex items-center gap-1 text-emerald-600 font-bold"><i class="ph-bold ph-check-circle text-base"></i> আদায় ও প্রিন্ট</span>
                        </td>
                        <td class="py-3 px-4 text-center bg-indigo-50/20">
                            <span class="inline-flex items-center gap-1 text-emerald-600 font-bold"><i class="ph-bold ph-check-circle text-base"></i> আদায় ও প্রিন্ট</span>
                        </td>
                    </tr>

                    <!-- Row 3: Cash Deposit -->
                    <tr class="hover:bg-neutral-50/60 transition">
                        <td class="py-3 px-4 font-semibold text-neutral-900">
                            ৩. ক্যাশ ডিপোজিট জমা ও হিসাব
                            <span class="block text-[11px] font-normal text-neutral-500">সংগৃহীত করের টাকা ব্যাংকে/ইউনিয়নে জমা প্রদান</span>
                        </td>
                        <td class="py-3 px-4 text-center bg-amber-50/20 border-x border-neutral-100">
                            <span class="inline-flex items-center gap-1 text-neutral-400 font-medium"><i class="ph-bold ph-minus text-base"></i> প্রযোজ্য নয়</span>
                        </td>
                        <td class="py-3 px-4 text-center bg-sky-50/20 border-r border-neutral-100">
                            <span class="inline-flex items-center gap-1 text-neutral-400 font-medium"><i class="ph-bold ph-minus text-base"></i> প্রযোজ্য নয়</span>
                        </td>
                        <td class="py-3 px-4 text-center bg-emerald-50/20 border-r border-neutral-100">
                            <span class="inline-flex items-center gap-1 text-amber-600 font-bold"><i class="ph-bold ph-paper-plane-tilt text-base"></i> জমা প্রস্তাব</span>
                        </td>
                        <td class="py-3 px-4 text-center bg-indigo-50/20">
                            <span class="inline-flex items-center gap-1 text-amber-600 font-bold"><i class="ph-bold ph-paper-plane-tilt text-base"></i> জমা প্রস্তাব</span>
                        </td>
                    </tr>

                    <!-- Row 4: Sonod -->
                    <tr class="hover:bg-neutral-50/60 transition">
                        <td class="py-3 px-4 font-semibold text-neutral-900">
                            ৪. নাগরিক সনদপত্র তৈরি ও প্রিন্ট
                            <span class="block text-[11px] font-normal text-neutral-500">ওয়ারিশান, চারিত্রিক, ভূমিহীন ইত্যাদি সনদ ব্যবস্থাপনা</span>
                        </td>
                        <td class="py-3 px-4 text-center bg-amber-50/20 border-x border-neutral-100">
                            <span class="inline-flex items-center gap-1 text-emerald-600 font-bold"><i class="ph-bold ph-check-circle text-base"></i> সম্পূর্ণ</span>
                        </td>
                        <td class="py-3 px-4 text-center bg-sky-50/20 border-r border-neutral-100">
                            <span class="inline-flex items-center gap-1 text-emerald-600 font-bold"><i class="ph-bold ph-check-circle text-base"></i> সম্পূর্ণ</span>
                        </td>
                        <td class="py-3 px-4 text-center bg-emerald-50/20 border-r border-neutral-100">
                            <span class="inline-flex items-center gap-1 text-emerald-600 font-bold"><i class="ph-bold ph-check-circle text-base"></i> তৈরি ও প্রিন্ট</span>
                        </td>
                        <td class="py-3 px-4 text-center bg-indigo-50/20">
                            <span class="inline-flex items-center gap-1 text-rose-500 font-bold"><i class="ph-bold ph-x-circle text-base"></i> অনুমতি নেই</span>
                        </td>
                    </tr>

                    <!-- Row 5: User Management -->
                    <tr class="hover:bg-neutral-50/60 transition">
                        <td class="py-3 px-4 font-semibold text-neutral-900">
                            ৫. ইউজার ও কর্মচারী ব্যবস্থাপনা
                            <span class="block text-[11px] font-normal text-neutral-500">নতুন স্টাফ যুক্ত করা, রোল নির্ধারণ ও পাসওয়ার্ড রিসেট</span>
                        </td>
                        <td class="py-3 px-4 text-center bg-amber-50/20 border-x border-neutral-100">
                            <span class="inline-flex items-center gap-1 text-emerald-600 font-bold"><i class="ph-bold ph-check-circle text-base"></i> সম্পূর্ণ</span>
                        </td>
                        <td class="py-3 px-4 text-center bg-sky-50/20 border-r border-neutral-100">
                            <span class="inline-flex items-center gap-1 text-emerald-600 font-bold"><i class="ph-bold ph-check-circle text-base"></i> সম্পূর্ণ</span>
                        </td>
                        <td class="py-3 px-4 text-center bg-emerald-50/20 border-r border-neutral-100">
                            <span class="inline-flex items-center gap-1 text-rose-500 font-bold"><i class="ph-bold ph-x-circle text-base"></i> অনুমতি নেই</span>
                        </td>
                        <td class="py-3 px-4 text-center bg-indigo-50/20">
                            <span class="inline-flex items-center gap-1 text-rose-500 font-bold"><i class="ph-bold ph-x-circle text-base"></i> অনুমতি নেই</span>
                        </td>
                    </tr>

                    <!-- Row 6: System Settings -->
                    <tr class="hover:bg-neutral-50/60 transition">
                        <td class="py-3 px-4 font-semibold text-neutral-900">
                            ৬. সিস্টেম সেটিংস ও ইউনিয়ন কনফিগারেশন
                            <span class="block text-[11px] font-normal text-neutral-500">ইউনিয়নের নাম, ফোন, লোগো ও পোর্টাল তথ্য পরিবর্তন</span>
                        </td>
                        <td class="py-3 px-4 text-center bg-amber-50/20 border-x border-neutral-100">
                            <span class="inline-flex items-center gap-1 text-emerald-600 font-bold"><i class="ph-bold ph-check-circle text-base"></i> সরাসরি পরিবর্তন</span>
                        </td>
                        <td class="py-3 px-4 text-center bg-sky-50/20 border-r border-neutral-100">
                            <span class="inline-flex items-center gap-1 text-amber-600 font-bold"><i class="ph-bold ph-hourglass-medium text-base"></i> অনুমোদনের প্রস্তাব</span>
                        </td>
                        <td class="py-3 px-4 text-center bg-emerald-50/20 border-r border-neutral-100">
                            <span class="inline-flex items-center gap-1 text-rose-500 font-bold"><i class="ph-bold ph-x-circle text-base"></i> অনুমতি নেই</span>
                        </td>
                        <td class="py-3 px-4 text-center bg-indigo-50/20">
                            <span class="inline-flex items-center gap-1 text-rose-500 font-bold"><i class="ph-bold ph-x-circle text-base"></i> অনুমতি নেই</span>
                        </td>
                    </tr>

                    <!-- Row 7: Final Approvals -->
                    <tr class="hover:bg-neutral-50/60 transition bg-amber-50/30">
                        <td class="py-3.5 px-4 font-bold text-neutral-900">
                            ৭. চূড়ান্ত সিদ্ধান্ত ও অনুমোদন (Approvals Queue)
                            <span class="block text-[11px] font-normal text-neutral-500">প্রস্তাবিত সেটিংস পরিবর্তন ও ক্যাশ ডিপোজিট অনুমোদন</span>
                        </td>
                        <td class="py-3.5 px-4 text-center bg-amber-100/50 border-x border-neutral-200">
                            <span class="inline-flex items-center gap-1 text-emerald-800 font-extrabold px-2.5 py-1 rounded-full bg-emerald-100">
                                <i class="ph-bold ph-seal-check text-base"></i> চূড়ান্ত অনুমোদনকারী
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center bg-sky-50/20 border-r border-neutral-100">
                            <span class="inline-flex items-center gap-1 text-neutral-500 font-medium">প্রস্তাবক মাত্র (অনুমোদন নেই)</span>
                        </td>
                        <td class="py-3.5 px-4 text-center bg-emerald-50/20 border-r border-neutral-100">
                            <span class="inline-flex items-center gap-1 text-rose-500 font-bold"><i class="ph-bold ph-x-circle text-base"></i> অনুমতি নেই</span>
                        </td>
                        <td class="py-3.5 px-4 text-center bg-indigo-50/20">
                            <span class="inline-flex items-center gap-1 text-rose-500 font-bold"><i class="ph-bold ph-x-circle text-base"></i> অনুমতি নেই</span>
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>
    </div>

    <!-- Security Footnote -->
    <div class="p-4 rounded-2xl bg-neutral-100 border border-neutral-200/80 text-neutral-700 text-xs flex items-start gap-3">
        <i class="ph-bold ph-shield-check text-indigo-600 text-xl shrink-0 mt-0.5"></i>
        <div class="space-y-1">
            <strong class="font-bold text-neutral-900">স্ব-অনুমোদন সুরক্ষা (Self-Approval Protection):</strong>
            <p>
                চেয়ারম্যান নিজে কোনো প্রস্তাব তৈরি করলে ডেটাবেজ চেক কনস্ট্রেইন্টের (`approvals_no_self_approval`) কারণে তিনি নিজের প্রস্তাব নিজে অনুমোদন করতে পারবেন না। প্ল্যাটফর্ম সুপারএডমিন অথবা বিকল্প অনুমোদনকারীর সিদ্ধান্ত প্রয়োজন হবে।
            </p>
        </div>
    </div>

</div>

<!-- Modal: Add New User -->
<div id="add_new_user" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered max-w-lg mx-auto" role="document">
        <div class="modal-content bg-white rounded-2xl shadow-2xl border border-neutral-200 overflow-hidden" style="pointer-events: auto;">
            <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-100 bg-white">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <i class="ph-bold ph-user-plus text-lg"></i>
                    </div>
                    <h3 class="text-sm font-bold text-neutral-900">নতুন ব্যবহারকারী নিবন্ধন</h3>
                </div>
                <button type="button" class="w-8 h-8 rounded-xl text-neutral-400 hover:text-neutral-700 hover:bg-neutral-100 flex items-center justify-center transition cursor-pointer" aria-label="বন্ধ করুন" title="বন্ধ করুন" data-dismiss="modal">
                    <i class="ph ph-x text-lg"></i>
                </button>
            </div>

            <div class="p-6 bg-white space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-neutral-700 mb-1.5">পূর্ণ নাম <span class="text-rose-500">*</span></label>
                        <input type="text" class="w-full text-xs px-3.5 py-2 bg-white border border-neutral-300 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 text-neutral-900 transition full_name_s" placeholder="কর্মকর্তার পূর্ণ নাম">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-neutral-700 mb-1.5">মোবাইল নম্বর <span class="text-rose-500">*</span></label>
                        <input type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');" class="w-full text-xs px-3.5 py-2 bg-white border border-neutral-300 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 text-neutral-900 font-mono transition phone_nos" placeholder="০১XXXXXXXXX">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-neutral-700 mb-1.5">ইউজারনেম <span class="text-rose-500">*</span></label>
                        <input type="text" class="w-full text-xs px-3.5 py-2 bg-white border border-neutral-300 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 text-neutral-900 transition user_names" placeholder="লগইন ইউজারনেম">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-neutral-700 mb-1.5">পাসওয়ার্ড <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <input type="password" data-saas-password class="w-full text-xs px-3.5 pr-10 py-2 bg-white border border-neutral-300 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 text-neutral-900 transition pass_words" placeholder="শক্তিশালী পাসওয়ার্ড">
                            <button type="button" data-saas-toggle-password class="absolute right-2.5 top-1/2 -translate-y-1/2 text-neutral-400 hover:text-neutral-600 cursor-pointer" aria-label="পাসওয়ার্ড দেখান">
                                <span data-saas-eye="show"><i class="ph ph-eye text-sm"></i></span>
                                <span data-saas-eye="hide" class="hidden"><i class="ph ph-eye-slash text-sm"></i></span>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-neutral-700 mb-1.5">ইমেইল</label>
                        <input type="email" class="w-full text-xs px-3.5 py-2 bg-white border border-neutral-300 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 text-neutral-900 transition users_emails" placeholder="ইমেইল ঠিকানা">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-neutral-700 mb-1.5">ব্যবহারকারীর ভূমিকা (Role) <span class="text-rose-500">*</span></label>
                        <select class="w-full text-xs px-3.5 py-2 bg-white border border-neutral-300 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 text-neutral-900 font-semibold transition user_role_group_id">
                            <option value="7">ট্যাক্স আদায়কারী (Tax Collector)</option>
                            <option value="6">ইউডিসি উদ্যোক্তা (Digital Center)</option>
                            <option value="5">ইউপি সচিব (Secretary / Proposer)</option>
                            <option value="4">ইউপি চেয়ারম্যান (Chairman / Approver)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-neutral-700 mb-1.5">কমিশন (%) <span class="text-rose-500">*</span></label>
                        <input type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');" class="w-full text-xs px-3.5 py-2 bg-white border border-neutral-300 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 text-neutral-900 font-mono transition collect_parcent" value="0" placeholder="কমিশন হার (%)">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-neutral-700 mb-1.5">পূর্ণ ঠিকানা</label>
                        <input type="text" class="w-full text-xs px-3.5 py-2 bg-white border border-neutral-300 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 text-neutral-900 transition full_addresss" placeholder="গ্রাম, ডাকঘর, উপজেলা, জেলা">
                    </div>
                </div>
            </div>

            <div class="bg-neutral-50 px-5 py-3 border-t border-neutral-200 flex items-center justify-end gap-2">
                <button type="button" class="px-3 py-1.5 rounded-xl text-xs font-semibold text-neutral-700 bg-white border border-neutral-300 hover:bg-neutral-100 hover:text-neutral-900 shadow-2xs transition cursor-pointer" data-dismiss="modal">
                    বাতিল
                </button>
                <button type="button" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white rounded-xl text-xs font-semibold shadow-2xs transition entry_new_users cursor-pointer">
                    <i class="ph-bold ph-plus text-xs"></i> ব্যবহারকারী যোগ করুন
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Change Password -->
<div id="change_pass" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered max-w-sm mx-auto" role="document">
        <div class="modal-content bg-white rounded-2xl shadow-2xl border border-neutral-200 overflow-hidden" style="pointer-events: auto;">
            <div class="flex items-center justify-between px-5 py-4 border-b border-neutral-100 bg-white">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                        <i class="ph-bold ph-key text-base"></i>
                    </div>
                    <h3 class="text-sm font-bold text-neutral-900">পাসওয়ার্ড পরিবর্তন</h3>
                </div>
                <button type="button" class="w-7 h-7 rounded-xl text-neutral-400 hover:text-neutral-700 hover:bg-neutral-100 flex items-center justify-center transition cursor-pointer" aria-label="বন্ধ করুন" title="বন্ধ করুন" data-dismiss="modal">
                    <i class="ph ph-x text-base"></i>
                </button>
            </div>
            <div class="p-5 bg-white space-y-3">
                <div>
                    <label class="block text-xs font-semibold text-neutral-700 mb-1.5">নতুন পাসওয়ার্ড</label>
                    <input type="text" class="w-full text-xs px-3.5 py-2 bg-white border border-neutral-300 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 text-neutral-900 transition changing_passs" this_user_id="" placeholder="নতুন পাসওয়ার্ড লিখুন">
                </div>
            </div>
            <div class="bg-neutral-50 px-5 py-3 border-t border-neutral-200 flex items-center justify-end gap-2">
                <button type="button" class="px-3 py-1.5 rounded-xl text-xs font-semibold text-neutral-700 bg-white border border-neutral-300 hover:bg-neutral-100 hover:text-neutral-900 shadow-2xs transition cursor-pointer" data-dismiss="modal">
                    বাতিল
                </button>
                <button type="button" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-semibold shadow-2xs transition changes_pass_words cursor-pointer">
                    <i class="ph-bold ph-lock-key text-xs"></i> পরিবর্তন করুন
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Toggle styles -->
<style>
.ht-toggle-track {
    width: 38px;
    height: 22px;
    border-radius: 9999px;
    position: relative;
    transition: background-color 0.2s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.2s;
    cursor: pointer;
}
.ht-toggle-knob {
    width: 18px;
    height: 18px;
    background-color: #ffffff;
    border-radius: 9999px;
    position: absolute;
    top: 2px;
    left: 2px;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.25), 0 1px 2px -1px rgba(0, 0, 0, 0.2);
    transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}
.ht-toggle-active {
    background-color: #10b981 !important;
}
.ht-toggle-active .ht-toggle-knob {
    transform: translateX(16px);
}
.ht-toggle-inactive {
    background-color: #cbd5e1 !important;
}
.ht-toggle-inactive .ht-toggle-knob {
    transform: translateX(0);
}
.ht-toggle-disabled {
    opacity: 0.65;
    cursor: not-allowed !important;
}
</style>

<script>
    get_user_details();

    function switchUserTab(tab) {
        const btnUsers = document.getElementById('tabBtnUsers');
        const btnMatrix = document.getElementById('tabBtnMatrix');
        const contentUsers = document.getElementById('tabContentUsers');
        const contentMatrix = document.getElementById('tabContentMatrix');

        if (tab === 'users') {
            btnUsers.className = 'px-4 py-2.5 font-bold text-indigo-600 border-b-2 border-indigo-600 flex items-center gap-2 transition-colors cursor-pointer';
            btnMatrix.className = 'px-4 py-2.5 font-medium text-neutral-500 hover:text-neutral-800 flex items-center gap-2 transition-colors cursor-pointer';
            contentUsers.classList.remove('hidden');
            contentMatrix.classList.add('hidden');
        } else {
            btnMatrix.className = 'px-4 py-2.5 font-bold text-indigo-600 border-b-2 border-indigo-600 flex items-center gap-2 transition-colors cursor-pointer';
            btnUsers.className = 'px-4 py-2.5 font-medium text-neutral-500 hover:text-neutral-800 flex items-center gap-2 transition-colors cursor-pointer';
            contentMatrix.classList.remove('hidden');
            contentUsers.classList.add('hidden');
        }
    }

    function get_user_details() {
        $.ajax({
            type: "post",
            url: "getInsUsers",
            data: "",
            success: function (res) {
                let view_datas = '';
                if (!res || res.length === 0) {
                    view_datas = `
                        <tr>
                            <td colspan="6" class="py-12 text-center text-neutral-400">
                                <div class="flex flex-col items-center gap-2">
                                    <i class="ph ph-users text-3xl text-neutral-300"></i>
                                    <span>কোন ব্যবহারকারী নেই</span>
                                </div>
                            </td>
                        </tr>`;
                } else {
                    let totalActive = res.filter(u => u.active == 1).length;
                    let isSingleUser = res.length <= 1;

                    for (let a = 0; a < res.length; a++) {
                        let r = res[a];
                        let isActive = r.active == 1;
                        let isProtected = (isSingleUser || (totalActive <= 1 && isActive));
                        let toggleStateClass = isActive ? 'ht-toggle-active' : 'ht-toggle-inactive';
                        let disabledClass = isProtected ? 'ht-toggle-disabled' : '';

                        let roleId = r.primary_role_id || 7;

                        view_datas += `
                            <tr class="hover:bg-neutral-50/80 transition">
                                <td class="py-3 px-4 text-center font-mono text-neutral-500">${a + 1}</td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-indigo-50 text-indigo-600 font-bold text-xs flex items-center justify-center shrink-0 border border-indigo-100">
                                            ${(r.user_person_name || 'U').charAt(0).toUpperCase()}
                                        </div>
                                        <div>
                                            <a href="userView?check=${r.user_uniq_qq_reails}" class="font-bold text-neutral-900 hover:text-indigo-600 transition block">
                                                ${r.user_person_name || '—'}
                                            </a>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                <span class="text-[11px] text-neutral-500 font-mono">@${r.username || '—'}</span>
                                                <span class="text-[11px] text-neutral-400">•</span>
                                                <span class="text-[11px] text-neutral-500">কমিশন: ${r.percentager || 0}%</span>
                                                ${isProtected ? '<span class="text-[10px] text-amber-700 bg-amber-50 px-1.5 py-0.2 rounded border border-amber-200/80 font-medium">একমাত্র সক্রিয়</span>' : ''}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-center font-mono text-neutral-700">${r.user_phone_no || '—'}</td>
                                <td class="py-3 px-4">
                                    <select class="userRoleSelect text-xs font-semibold py-1 px-2.5 rounded-xl border border-neutral-300 bg-white hover:border-indigo-500 transition cursor-pointer" data-user-id="${r.id}">
                                        <option value="4" ${roleId == 4 ? 'selected' : ''}>ইউপি চেয়ারম্যান</option>
                                        <option value="5" ${roleId == 5 ? 'selected' : ''}>ইউপি সচিব</option>
                                        <option value="6" ${roleId == 6 ? 'selected' : ''}>ইউডিসি উদ্যোক্তা</option>
                                        <option value="7" ${roleId == 7 ? 'selected' : ''}>ট্যাক্স আদায়কারী</option>
                                    </select>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <div class="flex items-center justify-center">
                                        <label class="inline-flex items-center gap-2.5 select-none ${isProtected ? 'cursor-not-allowed' : 'cursor-pointer'}" title="${isProtected ? 'একমাত্র সক্রিয় ব্যবহারকারী নিষ্ক্রিয় করা যাবে না' : (isActive ? 'নিষ্ক্রিয় করতে ক্লিক করুন' : 'সক্রিয় করতে ক্লিক করুন')}">
                                            <input type="checkbox" class="sr-only activityToggleDiv" userAttrID="${r.id}" ${isActive ? 'checked' : ''} ${isProtected ? 'disabled' : ''}>
                                            <div class="ht-toggle-track ${toggleStateClass} ${disabledClass}">
                                                <div class="ht-toggle-knob"></div>
                                            </div>
                                            ${isActive 
                                                ? `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> সক্রিয়</span>`
                                                : `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-neutral-100 text-neutral-500 border border-neutral-200"><span class="w-1.5 h-1.5 rounded-full bg-neutral-400"></span> নিষ্ক্রিয়</span>`
                                            }
                                        </label>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <a href="userView?check=${r.user_uniq_qq_reails}" class="inline-flex items-center justify-center w-7 h-7 rounded-xl bg-neutral-100 text-neutral-600 hover:bg-indigo-600 hover:text-white transition" title="প্রোফাইল দেখুন">
                                            <i class="ph-bold ph-eye text-xs"></i>
                                        </a>
                                        <button type="button" class="inline-flex items-center justify-center w-7 h-7 rounded-xl bg-neutral-100 text-neutral-600 hover:bg-amber-500 hover:text-white transition user_pass_changing cursor-pointer"
                                            this_user_id="${r.id}" data-toggle="modal" data-target="#change_pass" title="পাসওয়ার্ড পরিবর্তন">
                                            <i class="ph-bold ph-key text-xs"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>`;
                    }
                }
                $('.html_tr_datas').html(view_datas);
            },
            error: function() {
                $('.html_tr_datas').html(`
                    <tr>
                        <td colspan="6" class="py-8 text-center text-rose-500 text-xs">তথ্য লোড করতে সমস্যা হয়েছে।</td>
                    </tr>
                `);
            }
        });
    }

    $(document).on('change', '.userRoleSelect', function() {
        let userId = $(this).data('user-id');
        let roleId = $(this).val();
        let selectElem = $(this);

        $.ajax({
            type: "POST",
            url: "changeUserRole",
            data: { user_id: userId, role_id: roleId },
            dataType: "json",
            success: function(res) {
                if (res && res.status === 'success') {
                    $('.fade_msg').removeClass('hidden');
                    $('.alert_msg').removeClass('bg-rose-50 border-rose-200 text-rose-700 bg-amber-50 border-amber-200 text-amber-700')
                                   .addClass('bg-emerald-50 border-emerald-200 text-emerald-700')
                                   .text(res.message);
                    setTimeout(() => { $('.fade_msg').addClass('hidden'); }, 4000);
                } else {
                    alert(res.message || 'ভূমিকা পরিবর্তন সম্ভব হয়নি।');
                    get_user_details();
                }
            },
            error: function() {
                alert('সার্ভারের সাথে সংযোগে ত্রুটি ঘটেছে।');
                get_user_details();
            }
        });
    });

    $(document).on('click', '.activityToggleDiv', function (e) {
        if ($(this).is(':disabled')) {
            e.preventDefault();
            $('.fade_msg').removeClass('hidden');
            $('.alert_msg').removeClass('bg-emerald-50 border-emerald-200 text-emerald-700 bg-rose-50 border-rose-200 text-rose-700')
                           .addClass('bg-amber-50 border-amber-200 text-amber-700')
                           .text('একমাত্র সক্রিয় ব্যবহারকারীকে নিষ্ক্রিয় করা যাবে না।');
            setTimeout(() => { $('.fade_msg').addClass('hidden'); }, 4000);
            return false;
        }
    });

    $(document).on('change', '.activityToggleDiv', function () {
        let userIs = $(this).attr('userAttrID');
        $.ajax({
            type: "post",
            url: "userChangeActivity",
            data: { cus_id: userIs },
            dataType: "json",
            success: function (res) {
                if (res && res.status === 'error') {
                    $('.fade_msg').removeClass('hidden');
                    $('.alert_msg').removeClass('bg-emerald-50 border-emerald-200 text-emerald-700 bg-amber-50 border-amber-200 text-amber-700')
                                   .addClass('bg-rose-50 border-rose-200 text-rose-700')
                                   .text(res.message || 'অ্যাকশন সম্পন্ন করা সম্ভব হয়নি।');
                    setTimeout(() => { $('.fade_msg').addClass('hidden'); }, 5000);
                } else {
                    $('.fade_msg').removeClass('hidden');
                    $('.alert_msg').removeClass('bg-rose-50 border-rose-200 text-rose-700 bg-amber-50 border-amber-200 text-amber-700')
                                   .addClass('bg-emerald-50 border-emerald-200 text-emerald-700')
                                   .text('অবস্থা সফলভাবে পরিবর্তন করা হয়েছে');
                    setTimeout(() => { $('.fade_msg').addClass('hidden'); }, 3000);
                }
                get_user_details();
            },
            error: function () {
                get_user_details();
            }
        });
    });

    $(document).on('click', '.entry_new_users', function () {
        let names = $('.full_name_s').val();
        let phone = $('.phone_nos').val();
        let email = $('.users_emails').val();
        let addrs = $('.full_addresss').val();
        let uname = $('.user_names').val();
        let upass = $('.pass_words').val();
        let cprcntg = $('.collect_parcent').val();
        let role_group_id = $('.user_role_group_id').val();

        if (!names || !phone || !uname || !upass) {
            Swal.fire({
                title: 'তথ্য অসম্পূর্ণ',
                text: 'অনুগ্রহ করে সকল প্রয়োজনীয় তথ্য পূরণ করুন।',
                icon: 'warning',
                confirmButtonText: 'ঠিক আছে',
                confirmButtonColor: '#4f46e5'
            });
            return;
        }

        $.ajax({
            type: "post",
            url: "entryNewUser",
            data: { names, phone, email, addrs, uname, upass, cprcntg, role_group_id },
            success: function (res) {
                get_user_details();
                $('#add_new_user').modal('hide');
                $('.fade_msg').removeClass('hidden');
                $('.alert_msg').text('ব্যবহারকারী সফলভাবে যোগ করা হয়েছে');
                setTimeout(() => { $('.fade_msg').addClass('hidden'); }, 5000);
                $('.full_name_s,.phone_nos,.users_emails,.full_addresss,.user_names,.pass_words').val('');
                $('.collect_parcent').val('0');
            }
        });
    });

    $(document).on('click', '.user_pass_changing', function () {
        let this_user_id = $(this).attr('this_user_id');
        $('.changing_passs').attr('this_user_id', this_user_id);
    });

    $(document).on('click', '.changes_pass_words', function () {
        let changing_passs = $('.changing_passs').val();
        let this_user_id = $('.changing_passs').attr('this_user_id');

        if (changing_passs && this_user_id) {
            $.ajax({
                type: "post",
                url: "changeUserPass",
                data: { pass: changing_passs, id: this_user_id },
                success: function (r) {
                    get_user_details();
                    $('#change_pass').modal('hide');
                    $('.fade_msg').removeClass('hidden');
                    $('.alert_msg').text('পাসওয়ার্ড সফলভাবে পরিবর্তন হয়েছে');
                    setTimeout(() => { $('.fade_msg').addClass('hidden'); }, 5000);
                    $('.changing_passs').val('');
                }
            });
        }
    });

    $(document).ready(function() {
        $('#add_new_user, #change_pass').appendTo('body');
    });
</script>
