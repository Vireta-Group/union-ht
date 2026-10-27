<!-- Breadcrumb & Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-neutral-200">
    <div>
        <nav class="flex items-center gap-2 text-xs text-neutral-500 mb-1">
            <a href="dashboard" class="hover:text-indigo-600 transition flex items-center gap-1">
                <i class="ph ph-house text-sm"></i> ড্যাশবোর্ড
            </a>
            <i class="ph ph-caret-right text-[10px] text-neutral-400"></i>
            <span class="text-neutral-800 font-medium">নতুন হোল্ডিং এন্ট্রি</span>
        </nav>
        <h1 class="text-xl sm:text-2xl font-bold text-neutral-900 tracking-tight flex items-center gap-2">
            <span class="w-2.5 h-6 bg-indigo-600 rounded-full inline-block"></span>
            নতুন হোল্ডিং ও করদাতা নিবন্ধন
        </h1>
        <p class="text-xs sm:text-sm text-neutral-500 mt-0.5">নাগরিকের তথ্য ও বার্ষিক হোল্ডিং কর নির্ধারণ ফরম পূরণ করুন</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="search_holding_info" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold bg-white border border-neutral-300 text-neutral-700 hover:bg-neutral-50 shadow-sm transition">
            <i class="ph ph-magnifying-glass text-sm"></i> হোল্ডিং খুঁজুন
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <!-- Left Column: NID Quick Search / Info Card -->
    <div class="lg:col-span-1 space-y-6">
        <?php if (!empty($gl_info->nid_search_activity) && $gl_info->nid_search_activity == 1) { ?>
            <div class="bg-white rounded-2xl border border-neutral-200/80 shadow-sm p-5 space-y-4">
                <div class="flex items-center gap-2 pb-3 border-b border-neutral-100">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                        <i class="ph-bold ph-identification-card text-lg"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-neutral-900 leading-tight">NID দিয়ে স্বয়ংক্রিয় পূরণ</h2>
                        <span class="text-[11px] text-neutral-500">জাতীয় তথ্যভাণ্ডার যাচাই</span>
                    </div>
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-neutral-700 mb-1">জাতীয় পরিচয়পত্র (NID) নং</label>
                        <input type="text" class="w-full text-xs px-3 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition type_nid_num" placeholder="১০ বা ১৭ ডিজিটের NID">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-neutral-700 mb-1">জন্ম তারিখ (DOB)</label>
                        <input type="text" class="w-full text-xs px-3 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition type_dob_s datePicks" placeholder="YYYY-MM-DD">
                    </div>
                    <button type="button" class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white rounded-xl text-xs font-semibold shadow-sm transition flex items-center justify-center gap-2 search_nid_datas">
                        <i class="ph-bold ph-magnifying-glass text-sm"></i>
                        <span>তথ্য অনুসন্ধান করুন</span>
                    </button>
                </div>
            </div>
        <?php } else { ?>
            <div class="bg-white rounded-2xl border border-neutral-200/80 shadow-sm p-5">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                        <i class="ph-bold ph-info text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold text-neutral-900">নির্দেশনা</h3>
                        <p class="text-[11px] text-neutral-500 mt-0.5 leading-relaxed">তারকা (<span class="text-rose-500 font-bold">*</span>) চিহ্নিত সকল তথ্য সঠিকভাবে বাংলায় অথবা ইংরেজিতে পূরণ করুন।</p>
                    </div>
                </div>
            </div>
        <?php } ?>

        <div class="bg-neutral-50 rounded-2xl border border-neutral-200/60 p-5 space-y-3">
            <h4 class="text-xs font-bold text-neutral-800 uppercase tracking-wider flex items-center gap-1.5">
                <i class="ph ph-lightbulb text-amber-500 text-base"></i> সহায়ক তথ্য
            </h4>
            <ul class="text-xs text-neutral-600 space-y-2 leading-relaxed">
                <li class="flex items-start gap-2">
                    <i class="ph-bold ph-check text-emerald-600 mt-0.5 shrink-0"></i>
                    <span>ওয়ার্ড নির্বাচন করলে স্বয়ংক্রিয়ভাবে উক্ত ওয়ার্ডের গ্রাম লোড হবে।</span>
                </li>
                <li class="flex items-start gap-2">
                    <i class="ph-bold ph-check text-emerald-600 mt-0.5 shrink-0"></i>
                    <span>তালিকার বাইরে নতুন বাড়ির জন্য ড্রপডাউন থেকে 'Add New Home' চাপুন।</span>
                </li>
                <li class="flex items-start gap-2">
                    <i class="ph-bold ph-check text-emerald-600 mt-0.5 shrink-0"></i>
                    <span>হোল্ডিং শুরুর বছর লিখলে পরবর্তী অর্থবছর স্বয়ংক্রিয় গণিত হবে।</span>
                </li>
            </ul>
        </div>
    </div>

    <!-- Right Column: Main Registration Form -->
    <div class="lg:col-span-3">
        <form id="kt_ecommerce_settings_general_form" action="insert_family_user_data" method="post" class="bg-white rounded-2xl border border-neutral-200/80 shadow-sm overflow-hidden">
            <div class="p-6 space-y-6">

                <!-- Section 1: Personal Information -->
                <div>
                    <div class="flex items-center gap-2 pb-3 mb-4 border-b border-neutral-100">
                        <span class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xs">১</span>
                        <h3 class="text-sm font-bold text-neutral-900">নাগরিকের ব্যক্তিগত তথ্য</h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-neutral-700 mb-1">
                                নাগরিকের পূর্ণ নাম <span class="text-rose-500 font-bold">*</span>
                            </label>
                            <input type="text" required name="name" class="w-full text-xs px-3.5 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition fullNameAssgn" placeholder="উদাঃ মোঃ রফিকুল ইসলাম">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-neutral-700 mb-1">পিতার নাম</label>
                            <input type="text" name="fatherName" class="w-full text-xs px-3.5 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition fatheNames" placeholder="পিতার পূর্ণ নাম">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-neutral-700 mb-1">মাতার নাম</label>
                            <input type="text" name="motherName" class="w-full text-xs px-3.5 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition mothersNames" placeholder="মাতার পূর্ণ নাম">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-neutral-700 mb-1">জাতীয় পরিচয়পত্র (NID) নম্বর</label>
                            <input type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');" name="nidNumber" class="w-full text-xs px-3.5 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition searchingNIDno" placeholder="১০ বা ১৭ ডিজিট">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-neutral-700 mb-1">জন্ম তারিখ</label>
                            <input type="text" name="dateOfBirth" class="w-full text-xs px-3.5 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition searchingDOBs datePicks" placeholder="YYYY-MM-DD">
                        </div>
                    </div>
                </div>

                <!-- Section 2: Address & Contact -->
                <div>
                    <div class="flex items-center gap-2 pb-3 mb-4 border-b border-neutral-100">
                        <span class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xs">২</span>
                        <h3 class="text-sm font-bold text-neutral-900">ঠিকানা ও যোগাযোগের বিবরণ</h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-neutral-700 mb-1">বর্তমান ঠিকানা</label>
                            <input type="text" name="presentAddress" class="w-full text-xs px-3.5 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition prsntAddrsAssgn" placeholder="গ্রাম, ডাকঘর, উপজেলা, জেলা">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-neutral-700 mb-1">স্থায়ী ঠিকানা</label>
                            <input type="text" name="parmanentAddress" class="w-full text-xs px-3.5 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition prmntAddrsShow" placeholder="গ্রাম, ডাকঘর, উপজেলা, জেলা">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-neutral-700 mb-1">প্রাথমিক মোবাইল নম্বর</label>
                            <input type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');" name="mobileNumber" class="w-full text-xs px-3.5 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition" placeholder="০১XXXXXXXXX">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-neutral-700 mb-1">বিকল্প মোবাইল নম্বর</label>
                            <input type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');" name="secondaryMobileNumber" class="w-full text-xs px-3.5 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition" placeholder="০১XXXXXXXXX">
                        </div>
                    </div>
                </div>

                <!-- Section 3: Holding Location -->
                <div>
                    <div class="flex items-center gap-2 pb-3 mb-4 border-b border-neutral-100">
                        <span class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xs">৩</span>
                        <h3 class="text-sm font-bold text-neutral-900">হোল্ডিং ও এলাকা নির্ধারণ</h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-neutral-700 mb-1">
                                ওয়ার্ড নং <span class="text-rose-500 font-bold">*</span>
                            </label>
                            <select class="w-full text-xs px-3 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition selectingWard_no" name="wordNumber" required>
                                <option value="">ওয়ার্ড নির্বাচন করুন</option>
                                <?php for ($w = 1; $w <= 9; $w++) { 
                                    $w_val = sprintf('%02d', $w);
                                ?>
                                    <option value="<?= $w_val; ?>">ওয়ার্ড নং <?= bn_num($w_val); ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-neutral-700 mb-1">
                                গ্রাম <span class="text-rose-500 font-bold">*</span>
                            </label>
                            <select class="w-full text-xs px-3 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition selectingVillage" name="villageName" required>
                                <option value="">প্রথমে ওয়ার্ড সিলেক্ট করুন</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-neutral-700 mb-1">
                                বাড়ি <span class="text-rose-500 font-bold">*</span>
                            </label>
                            <select class="w-full text-xs px-3 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition selectingHouse" name="houseName" required>
                                <option value="">প্রথমে গ্রাম সিলেক্ট করুন</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-neutral-700 mb-1">
                                হোল্ডিং নম্বর <span class="text-rose-500 font-bold">*</span>
                            </label>
                            <input type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');" required name="holdingNumber" class="w-full text-xs px-3.5 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition" placeholder="উদাঃ ১২">
                        </div>
                    </div>
                </div>

                <!-- Section 4: Holding Tax & Assessment -->
                <div>
                    <div class="flex items-center gap-2 pb-3 mb-4 border-b border-neutral-100">
                        <span class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xs">৪</span>
                        <h3 class="text-sm font-bold text-neutral-900">হোল্ডিং ও বার্ষিক কর নির্ধারণ</h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-neutral-700 mb-1">
                                শুরুর অর্থবছর <span class="text-rose-500 font-bold">*</span>
                            </label>
                            <div class="flex items-center gap-1.5">
                                <input type="text" name="holdingStartingDateFirst" maxlength="4" required oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');" class="w-1/2 text-xs px-3 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition text-center starting_year_firsts" placeholder="2024">
                                <span class="text-neutral-400 font-bold text-sm">-</span>
                                <input type="text" name="holdingStartingDateSecs" maxlength="4" readonly required class="w-1/2 text-xs px-3 py-2 bg-neutral-100 border border-neutral-200 rounded-xl text-neutral-600 text-center font-semibold starting_year_secs" placeholder="2025">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-neutral-700 mb-1">
                                হোল্ডিং এর অবস্থা/ধরণ <span class="text-rose-500 font-bold">*</span>
                            </label>
                            <input type="text" required name="holdingCondition" class="w-full text-xs px-3.5 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition" placeholder="উদাঃ পাকা / সেমি-পাকা / কাঁচা">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-neutral-700 mb-1">
                                বার্ষিক করের হার (টাকা) <span class="text-rose-500 font-bold">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-neutral-400 text-xs font-bold">৳</span>
                                <input type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');" required name="holdingRatesYearly" class="w-full text-xs pl-8 pr-3.5 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition font-semibold" placeholder="উদাঃ ৩০০">
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Action Buttons Footer -->
            <div class="bg-neutral-50/80 px-6 py-4 border-t border-neutral-200/80 flex items-center justify-end gap-3">
                <button type="reset" class="px-5 py-2.5 rounded-xl text-xs font-semibold text-neutral-600 hover:text-neutral-900 hover:bg-neutral-200/60 transition">
                    রিসেট করুন
                </button>
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 shadow-sm transition">
                    <i class="ph-bold ph-floppy-disk text-sm"></i>
                    <span>হোল্ডিং তথ্য সংরক্ষণ করুন</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Add Home Modal -->
<div id="kt_modal_add_user" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered max-w-md mx-auto my-4" role="document">
        <div class="modal-content bg-white rounded-2xl shadow-2xl border border-neutral-200 overflow-hidden w-full flex flex-col max-h-[calc(100vh-3.5rem)]">
            <div class="flex items-center justify-between px-5 py-4 border-b border-neutral-100 shrink-0 bg-white">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <i class="ph-bold ph-house-line text-base"></i>
                    </div>
                    <h3 class="text-sm font-bold text-neutral-900">নতুন বাড়ি যোগ করুন</h3>
                </div>
                <button type="button" class="w-7 h-7 rounded-xl text-neutral-400 hover:text-neutral-700 hover:bg-neutral-100 flex items-center justify-center transition" aria-label="বন্ধ করুন" title="বন্ধ করুন" data-dismiss="modal">
                    <i class="ph ph-x text-base"></i>
                </button>
            </div>

            <div class="p-5 space-y-4 overflow-y-auto flex-1 min-h-0 bg-white">
                <div>
                    <label class="block text-xs font-semibold text-neutral-700 mb-1.5">
                        বাড়ির নাম <span class="text-rose-500 font-bold">*</span>
                    </label>
                    <input type="text" name="home_name" class="w-full text-xs px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition home_name_typess" placeholder="উদাঃ খাঁ বাড়ি / চৌধুরী বাড়ি">
                </div>
                <p class="text-[11px] text-neutral-500">নির্বাচিত ওয়ার্ড ও গ্রামের আওতায় নতুন বাড়ি যুক্ত করা হবে।</p>
            </div>

            <div class="bg-neutral-50 px-5 py-3.5 border-t border-neutral-100 flex items-center justify-end gap-2 shrink-0">
                <button type="button" class="px-4 py-2 rounded-xl text-xs font-semibold text-neutral-600 hover:text-neutral-900 hover:bg-neutral-200/50 transition" data-dismiss="modal">
                    বাতিল
                </button>
                <button type="button" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white rounded-xl text-xs font-bold shadow-sm transition home_name_add_btn">
                    <i class="ph-bold ph-plus text-xs"></i>
                    <span>যুক্ত করুন</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).on('change', '.selectingWard_no', function () {
        let thisWard_val = $(this).val();
        if (!thisWard_val) {
            $('.selectingVillage').html('<option value="">প্রথমে ওয়ার্ড সিলেক্ট করুন</option>');
            $('.selectingHouse').html('<option value="">প্রথমে গ্রাম সিলেক্ট করুন</option>');
            return;
        }
        $.ajax({
            type: "post",
            url: "getVillByWard",
            data: { ward_n: thisWard_val },
            success: function (res) {
                let h_data = '';
                if (Array.isArray(res)) {
                    for (let l = 0; l < res.length; l++) {
                        h_data += `<option value="${res[l].villages_of_union_a_iidd}">${res[l].bn_vill_names}</option>`; 
                    }
                }
                $('.selectingVillage').html(`<option value="">গ্রাম নির্বাচন করুন</option>${h_data}`);
                $('.selectingHouse').html('<option value="">প্রথমে গ্রাম সিলেক্ট করুন</option>');
            }
        });
    });

    $(document).on('change', '.selectingVillage', function () {
        let thisVillage_val = $(this).val();
        if (thisVillage_val) {
            get_homes_infos(thisVillage_val);
        } else {
            $('.selectingHouse').html('<option value="">প্রথমে গ্রাম সিলেক্ট করুন</option>');
        }
    });

    $(document).on('keyup', '.starting_year_firsts', function () {
        let first_years = parseInt($(this).val(), 10);
        if ($(this).val() == '' || isNaN(first_years)) {
            $('.starting_year_secs').val('');
        } else {
            $('.starting_year_secs').val(first_years + 1);
        }
    });

    $(document).on('click', '.home_name_add_btn', function () {
        let house_name = $('.home_name_typess').val().trim();
        let vill_no = $('.selectingVillage').val();
        let ward_no = $('.selectingWard_no').val();

        if (!house_name) {
            Swal.fire({
                title: 'বাড়ির নাম প্রয়োজন',
                text: 'অনুগ্রহ করে নতুন বাড়ির নাম লিখুন।',
                icon: 'warning',
                confirmButtonText: 'ঠিক আছে',
                confirmButtonColor: '#4f46e5'
            });
            return;
        }

        $.ajax({
            type: "post",
            url: "addNewHome",
            data: {
                ward_no: ward_no,
                vill_no: vill_no,
                homes_text: house_name,
            },
            success: function (res) {
                $('#kt_modal_add_user').modal('hide');
                $('.home_name_typess').val('');
                get_homes_infos(vill_no);
            }
        });
    });

    function get_homes_infos(vil_id) {
        $.ajax({
            type: "post",
            url: "getHouseByVillage",
            data: { vill_nos: vil_id },
            success: function (res) {
                let h_data = '';
                if (Array.isArray(res)) {
                    for (let l = 0; l < res.length; l++) {
                        h_data += `<option value="${res[l].house_home_name_etc_iddiddd}">${res[l].house_na_me}</option>`; 
                    }
                }
                $('.selectingHouse').html(`<option value="">বাড়ি নির্বাচন করুন</option>${h_data}<option value="nwad" class="font-bold text-indigo-600">+ নতুন বাড়ি যোগ করুন (Add New Home)</option>`);
            }
        });
    }

    $(document).on('change', '.selectingHouse', function () {
        let thisHouse_val = $(this).val();
        if (thisHouse_val == 'nwad') {
            $('#kt_modal_add_user').modal('show'); 
        }
    });

    $(document).on('click', '.search_nid_datas', function () {
        let n = $('.type_nid_num').val();
        let d = $('.type_dob_s').val();
        if (!n) {
            Swal.fire({
                title: 'NID নম্বর প্রয়োজন',
                text: 'অনুগ্রহ করে জাতীয় পরিচয়পত্র (NID) নম্বর দিন।',
                icon: 'warning',
                confirmButtonText: 'ঠিক আছে',
                confirmButtonColor: '#4f46e5'
            });
            return;
        }
        $.ajax({
            type: "post",
            url: "nid_v",
            data: { n: n, d: d },
            success: function (res) {
                if (res && res.success) {
                    $('.fullNameAssgn').val(res.name || '');
                    $('.fatheNames').val(res.father || '');
                    $('.mothersNames').val(res.mother || '');
                    $('.prsntAddrsAssgn').val(res.presentAddress || '');
                    if (res.success.data && res.success.data.permanentAddress) {
                        let pa = res.success.data.permanentAddress;
                        $('.prmntAddrsShow').val(`বাড়ীঃ ${pa.homeOrHoldingNo || ''}, ${pa.villageOrRoad || ''}, ${pa.unionOrWard || ''}, ${pa.upozila || ''}, ${pa.district || ''}`);
                    }
                    $('.searchingNIDno').val(n);
                    $('.searchingDOBs').val(res.dob || d);
                } else {
                    Swal.fire({
                        title: 'তথ্য পাওয়া যায়নি',
                        text: 'প্রদত্ত NID ও জন্মতারিখের কোনো তথ্য নির্বাচন সার্ভারে পাওয়া যায়নি।',
                        icon: 'error',
                        confirmButtonText: 'ঠিক আছে',
                        confirmButtonColor: '#4f46e5'
                    });
                }
            }
        });
    });
</script>
