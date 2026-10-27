<?php
    $plan_name = $subscription->pkg_name_bn ?? ($subscription->name_bn ?? 'স্ট্যান্ডার্ড');
    $billing_cycle_bn = ($subscription && ($subscription->billing_cycle ?? '') === 'monthly') ? 'মাসিক' : 'বার্ষিক';
    $price_formatted = bn_num(number_format((float)($subscription->price_yearly ?? 0)));

    $start_date_bn = !empty($subscription->start_date) ? bn_num(date('d F, Y', strtotime($subscription->start_date))) : '—';
    $end_date_bn   = !empty($subscription->end_date) ? bn_num(date('d F, Y', strtotime($subscription->end_date))) : '—';
    
    // English to Bengali month translation
    $en_m = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    $bn_m = ['জানুয়ারি','ফেব্রুয়ারি','মার্চ','এপ্রিল','মে','জুন','জুলাই','আগস্ট','সেপ্টেম্বর','অক্টোবর','নভেম্বর','ডিসেম্বর'];
    $start_date_bn = str_replace($en_m, $bn_m, $start_date_bn);
    $end_date_bn   = str_replace($en_m, $bn_m, $end_date_bn);

    $days_highlight_cls = $is_expired 
        ? 'bg-rose-50 border-rose-200 text-rose-900' 
        : ($days_left <= 30 ? 'bg-amber-50 border-amber-200 text-amber-900' : 'bg-indigo-50/70 border-indigo-100 text-indigo-950');
?>

<div class="space-y-6">

    <!-- Flash Messages -->
    <?php if ($this->session->flashdata('success')): ?>
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm font-semibold flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2.5">
                <i class="ph-bold ph-check-circle text-lg text-emerald-600"></i>
                <span><?= htmlspecialchars($this->session->flashdata('success'), ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 cursor-pointer text-base">&times;</button>
        </div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('error')): ?>
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm font-semibold flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2.5">
                <i class="ph-bold ph-warning-circle text-lg text-rose-600"></i>
                <span><?= htmlspecialchars($this->session->flashdata('error'), ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700 cursor-pointer text-base">&times;</button>
        </div>
    <?php endif; ?>

    <!-- Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-neutral-200/80">
        <div>
            <nav class="flex items-center gap-2 text-xs text-neutral-500 mb-1">
                <a href="dashboard" class="hover:text-indigo-600 transition flex items-center gap-1">
                    <i class="ph-bold ph-house text-sm"></i> ড্যাশবোর্ড
                </a>
                <i class="ph-bold ph-caret-right text-[10px] text-neutral-400"></i>
                <span class="text-neutral-800 font-medium">সাবস্ক্রিপশন ও প্ল্যান নবায়ন</span>
            </nav>
            <h1 class="text-xl sm:text-2xl font-black text-neutral-900 tracking-tight flex items-center gap-2">
                <span class="w-2.5 h-6 bg-indigo-600 rounded-full inline-block"></span>
                সাবস্ক্রিপশন ব্যবস্থাপনা ও প্ল্যান নবায়ন
            </h1>
            <p class="text-xs sm:text-sm text-neutral-500 mt-0.5">আপনার ইউনিয়ন পরিষদের সক্রিয় লাইসেন্স, প্যাকেজ ফি এবং পেমেন্ট সংক্রান্ত তথ্য</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="support" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white border border-neutral-300 hover:bg-neutral-50 text-neutral-700 font-bold text-xs transition shadow-2xs">
                <i class="ph-bold ph-headset text-indigo-600"></i>
                <span>বিলিং হেল্পলাইন</span>
            </a>
        </div>
    </div>

    <?php if ($is_expired): ?>
    <!-- Expired Warning Banner -->
    <div class="p-4 rounded-2xl bg-linear-to-r from-rose-600 via-rose-500 to-rose-600 text-white shadow-md flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center shrink-0">
                <i class="ph-bold ph-warning-octagon text-2xl text-rose-100 animate-pulse"></i>
            </div>
            <div>
                <strong class="block text-sm font-bold">সাবস্ক্রিপশনের মেয়াদ শেষ হয়েছে (রিড-অনলি মোড সক্রিয়)</strong>
                <p class="text-xs text-rose-100 mt-0.5">বর্তমানে নতুন তথ্য এন্ট্রি বা পরিবর্তন বন্ধ রয়েছে। পরিষেবা নিরবচ্ছিন্ন রাখতে দ্রুত নবায়ন সম্পন্ন করুন।</p>
            </div>
        </div>
        <a href="#renewalForm" class="px-4 py-2 rounded-xl bg-white text-rose-800 hover:bg-rose-50 font-bold text-xs transition shadow-xs whitespace-nowrap">
            এখনই নবায়ন ফর্ম পূরণ করুন &darr;
        </a>
    </div>
    <?php endif; ?>

    <!-- Top Grid: Current Plan Details + Renewal Form -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Left: Current Subscription Overview (5 Cols) -->
        <div class="lg:col-span-5 space-y-5">
            <div class="bg-white rounded-3xl border border-neutral-200/90 shadow-sm p-6 overflow-hidden relative">
                <!-- Header Accent -->
                <div class="flex items-center justify-between pb-4 border-b border-neutral-100">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold <?= $is_expired ? 'bg-rose-100 text-rose-700' : 'bg-indigo-100 text-indigo-700' ?>">
                        <i class="ph-bold <?= $is_expired ? 'ph-warning-circle' : 'ph-crown' ?>"></i>
                        <?= htmlspecialchars($plan_name, ENT_QUOTES, 'UTF-8'); ?> প্ল্যান
                    </span>
                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-extrabold uppercase tracking-wider <?= $is_expired ? 'bg-rose-600 text-white' : 'bg-emerald-600 text-white' ?>">
                        <?= $is_expired ? 'মেয়াদোত্তীর্ণ' : 'সক্রিয় সাবস্ক্রিপশন' ?>
                    </span>
                </div>

                <!-- Price and Renewal Days -->
                <div class="py-5">
                    <div class="text-xs text-neutral-400 font-semibold uppercase tracking-wider">বার্ষিক প্যাকেজ ফি</div>
                    <div class="text-3xl sm:text-4xl font-black text-neutral-900 mt-1 leading-none bengali-num">
                        &#2547; <?= $price_formatted; ?>
                        <span class="text-xs font-medium text-neutral-500">/ <?= $billing_cycle_bn ?></span>
                    </div>

                    <!-- Days Remaining Highlight -->
                    <div class="mt-5 p-4 rounded-2xl border flex items-center justify-between <?= $days_highlight_cls; ?>">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl <?= $is_expired ? 'bg-rose-200/70 text-rose-700' : ($days_left <= 30 ? 'bg-amber-200/70 text-amber-700' : 'bg-indigo-200/70 text-indigo-700') ?> flex items-center justify-center text-xl shrink-0">
                                <i class="ph-bold <?= $is_expired ? 'ph-x-circle' : 'ph-hourglass-medium' ?>"></i>
                            </div>
                            <div>
                                <span class="block text-[11px] font-bold text-neutral-500">অবশিষ্ট লাইসেন্স মেয়াদ</span>
                                <strong class="block text-base sm:text-lg font-black">
                                    <?php if ($is_expired): ?>
                                        মেয়াদ উত্তীর্ণ
                                    <?php else: ?>
                                        <?= bn_num($days_left); ?> দিন বাকি
                                    <?php endif; ?>
                                </strong>
                            </div>
                        </div>
                        <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-md text-white <?= $is_expired ? 'bg-rose-600' : ($days_left <= 30 ? 'bg-amber-600' : 'bg-indigo-600') ?>">
                            <?= $is_expired ? 'বন্ধ' : ($days_left <= 30 ? 'শীঘ্রই শেষ' : 'সচল') ?>
                        </span>
                    </div>
                </div>

                <!-- Plan Timeline / Dates -->
                <div class="space-y-3 pt-3 border-t border-neutral-100 text-xs">
                    <div class="flex items-center justify-between py-1.5 border-b border-neutral-50">
                        <span class="text-neutral-500 flex items-center gap-1.5">
                            <i class="ph-bold ph-calendar text-neutral-400"></i> শুরু হওয়ার তারিখ:
                        </span>
                        <strong class="text-neutral-800"><?= $start_date_bn ?></strong>
                    </div>
                    <div class="flex items-center justify-between py-1.5 border-b border-neutral-50">
                        <span class="text-neutral-500 flex items-center gap-1.5">
                            <i class="ph-bold ph-calendar-check text-neutral-400"></i> পরবর্তী পেমেন্ট / মেয়াদ:
                        </span>
                        <strong class="text-neutral-800"><?= $end_date_bn ?></strong>
                    </div>
                    <div class="flex items-center justify-between py-1.5 border-b border-neutral-50">
                        <span class="text-neutral-500 flex items-center gap-1.5">
                            <i class="ph-bold ph-hash text-neutral-400"></i> পরিষদ কোড:
                        </span>
                        <strong class="text-neutral-800"><?= bn_num(tenant_code()); ?></strong>
                    </div>
                    <div class="flex items-center justify-between py-1.5">
                        <span class="text-neutral-500 flex items-center gap-1.5">
                            <i class="ph-bold ph-arrows-clockwise text-neutral-400"></i> স্বয়ংক্রিয় নবায়ন:
                        </span>
                        <span class="font-bold text-neutral-600">ম্যানুয়াল অনুমোদন</span>
                    </div>
                </div>
            </div>

            <!-- Billing Support Card -->
            <div class="bg-linear-to-br from-indigo-900 via-indigo-950 to-neutral-950 rounded-3xl p-5 text-white shadow-md space-y-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-white/10 text-amber-400 flex items-center justify-center text-lg">
                        <i class="ph-bold ph-phone-call"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-white">জরুরি বিলিং ও নবায়ন হেল্পলাইন</h4>
                        <p class="text-[11px] text-indigo-300">সরাসরি কথা বলুন আমাদের হিসাব ও গ্রাহক সহায়তা প্রতিনিধির সাথে</p>
                    </div>
                </div>
                <div class="pt-2 border-t border-white/10 flex items-center justify-between text-xs">
                    <span>হটলাইন: <strong class="text-amber-400 font-mono">+৮৮০ ১৭০০-০০০০০০</strong></span>
                    <a href="support" class="text-indigo-300 hover:text-white font-bold underline">টিকিট খুলুন &rarr;</a>
                </div>
            </div>
        </div>

        <!-- Right: Submit Renewal Payment (7 Cols) -->
        <div class="lg:col-span-7 space-y-5" id="renewalForm">
            <div class="bg-white rounded-3xl border border-neutral-200/90 shadow-sm p-6">
                <div class="flex items-center justify-between pb-4 border-b border-neutral-100 mb-5">
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-neutral-900 flex items-center gap-2">
                            <i class="ph-bold ph-credit-card text-indigo-600"></i>
                            প্ল্যান নবায়ন পেমেন্ট জমা দিন
                        </h3>
                        <p class="text-xs text-neutral-500 mt-0.5">নিচের পেমেন্ট গেটওয়েতে টাকা পাঠিয়ে TrxID/রেফারেন্স সাবমিট করুন</p>
                    </div>
                </div>

                <!-- Payment Accounts Info Box -->
                <div class="p-4 rounded-2xl bg-neutral-50 border border-neutral-200/80 mb-5 space-y-3">
                    <div class="text-xs font-bold text-neutral-700 flex items-center gap-1.5">
                        <i class="ph-bold ph-info text-indigo-600"></i>
                        নির্ধারিত অফিসিয়াল পেমেন্ট চ্যানেলসমূহ:
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <div class="p-3 rounded-xl bg-white border border-neutral-200">
                            <strong class="block text-rose-600 font-bold">bKash (মার্চেন্ট পেমেন্ট)</strong>
                            <span class="block text-neutral-700 font-mono text-sm font-bold mt-1">01700-000000</span>
                            <span class="block text-[10px] text-neutral-400 mt-0.5">Payment অপশন ব্যবহার করুন</span>
                        </div>
                        <div class="p-3 rounded-xl bg-white border border-neutral-200">
                            <strong class="block text-orange-600 font-bold">Nagad (মার্চেন্ট)</strong>
                            <span class="block text-neutral-700 font-mono text-sm font-bold mt-1">01700-000000</span>
                            <span class="block text-[10px] text-neutral-400 mt-0.5">মার্চেন্ট পে নির্বাচন করুন</span>
                        </div>
                    </div>
                    <div class="p-3 rounded-xl bg-white border border-neutral-200 text-xs text-neutral-600">
                        <strong class="block text-indigo-700 font-bold mb-1">ব্যাংক ট্রান্সফার (IBBL / City Bank)</strong>
                        <span>অ্যাকাউন্ট নাম: <strong>Vireta Technologies Ltd.</strong> | A/C: <strong>2050XXXXXXXXXX</strong> | ব্রাঞ্চ: ঢাকা</span>
                    </div>
                </div>

                <!-- Form -->
                <form action="subscription/submit_payment" method="POST" class="space-y-4">
                    <!-- Package Selection (Upgrade / Downgrade / Renew) -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-bold text-neutral-700">সাবস্ক্রিপশন প্যাকেজ নির্বাচন (আপগ্রেড / পরিবর্তন) *</label>
                            <span id="planChangeNotice" class="text-[11px] font-bold text-indigo-600"></span>
                        </div>
                        <select name="package_id" id="planSelectInput" onchange="handlePlanSelectChange(this)" class="w-full px-3.5 py-2.5 rounded-xl border border-neutral-300 bg-white text-xs font-bold text-neutral-800 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                            <?php if (!empty($packages)): foreach ($packages as $pkg): 
                                $is_curr = ((int)($subscription->package_id ?? 0) === (int)$pkg->package_id);
                            ?>
                                <option value="<?= $pkg->package_id ?>" data-price="<?= (int)$pkg->price_yearly ?>" data-name="<?= htmlspecialchars($pkg->name_bn, ENT_QUOTES, 'UTF-8') ?>" <?= $is_curr ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($pkg->name_bn, ENT_QUOTES, 'UTF-8') ?> — &#2547; <?= bn_num(number_format($pkg->price_yearly)) ?> / বছর <?= $is_curr ? '(বর্তমান সক্রিয় প্ল্যান)' : '' ?>
                                </option>
                            <?php endforeach; endif; ?>
                        </select>
                        <span class="block text-[11px] text-neutral-400 mt-1">প্যাকেজ পরিবর্তন করলে নতুন প্যাকেজের সুবিধাসমূহ স্বয়ংক্রিয়ভাবে প্রযোজ্য হবে।</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Payment Method -->
                        <div>
                            <label class="block text-xs font-bold text-neutral-700 mb-1.5">পেমেন্ট মাধ্যম *</label>
                            <select name="payment_method" required class="w-full px-3.5 py-2.5 rounded-xl border border-neutral-300 bg-white text-xs font-medium focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                                <option value="bkash">বিকাশ (bKash)</option>
                                <option value="nagad">নগদ (Nagad)</option>
                                <option value="bank">ব্যাংক ট্রান্সফার (Bank Deposit)</option>
                                <option value="cash">অফিস ক্যাশ (Cash)</option>
                                <option value="cheque">ব্যাংক চেক (Cheque)</option>
                                <option value="other">অন্যান্য</option>
                            </select>
                        </div>

                        <!-- Amount -->
                        <div>
                            <label class="block text-xs font-bold text-neutral-700 mb-1.5">প্রেরিত টাকার পরিমাণ (৳) *</label>
                            <input type="number" id="planAmountInput" name="amount" value="<?= (int)($subscription->price_yearly ?? 40000); ?>" required min="1000" class="w-full px-3.5 py-2.5 rounded-xl border border-neutral-300 bg-white text-xs font-bold text-neutral-900 focus:ring-2 focus:ring-indigo-500 focus:outline-none" placeholder="উদাহরণ: 40000">
                        </div>
                    </div>

                    <!-- Reference / TrxID -->
                    <div>
                        <label class="block text-xs font-bold text-neutral-700 mb-1.5">ট্রানজেকশন আইডি (TrxID) বা রেফারেন্স নম্বর *</label>
                        <input type="text" name="reference" required class="w-full px-3.5 py-2.5 rounded-xl border border-neutral-300 bg-white text-xs font-bold text-neutral-900 focus:ring-2 focus:ring-indigo-500 focus:outline-none font-mono" placeholder="যেমন: 9J47K8L2M বা ব্যাংক স্লিপ নং">
                        <span class="block text-[11px] text-neutral-400 mt-1">বিকাশ/নগদ পেমেন্ট সফল হওয়ার পর প্রাপ্ত TrxID লিখুন।</span>
                    </div>

                    <!-- Note -->
                    <div>
                        <label class="block text-xs font-bold text-neutral-700 mb-1.5">অতিরিক্ত নোট বা তথ্য (ঐচ্ছিক)</label>
                        <textarea name="note" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-neutral-300 bg-white text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none" placeholder="পেমেন্ট প্রেরক নম্বর বা বিশেষ কোনো তথ্য থাকলে লিখুন..."></textarea>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full py-3 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs sm:text-sm shadow-md transition-all cursor-pointer flex items-center justify-center gap-2">
                            <i class="ph-bold ph-paper-plane-tilt text-base"></i>
                            <span>নবায়ন ও প্যাকেজ পেমেন্ট তথ্য সাবমিট করুন</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <!-- Package Comparison Grid -->
    <div class="space-y-4 pt-2">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-base sm:text-lg font-bold text-neutral-900">উপলব্ধ সাবস্ক্রিপশন প্যাকেজসমূহ</h3>
                <p class="text-xs text-neutral-500">আপনার ইউনিয়ন পরিষদের প্রয়োজন অনুযায়ী উপযুক্ত প্ল্যান বাছাই, আপগ্রেড বা পরিবর্তন করুন</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php if (!empty($packages)): foreach ($packages as $pkg): 
                $is_current = ($subscription && (int)$subscription->package_id === (int)$pkg->package_id);
                $is_higher  = ($subscription && (int)$pkg->price_yearly > (int)($subscription->price_yearly ?? 0));
                $features = explode('|', $pkg->features ?? '');
            ?>
            <div class="bg-white rounded-3xl border <?= $is_current ? 'border-indigo-500 ring-2 ring-indigo-500/20 shadow-md' : 'border-neutral-200/90 shadow-sm' ?> p-6 flex flex-col justify-between transition-all hover:shadow-md relative overflow-hidden">
                <?php if ($is_higher): ?>
                    <div class="absolute top-0 right-0 bg-linear-to-l from-indigo-600 to-indigo-800 text-white text-[10px] font-black uppercase tracking-wider py-1 px-3 rounded-bl-xl shadow-xs">
                        উচ্চতর প্যাকেজ
                    </div>
                <?php endif; ?>

                <div>
                    <div class="flex items-center justify-between mb-3 <?= $is_higher ? 'pt-2' : '' ?>">
                        <h4 class="text-base font-bold text-neutral-900"><?= htmlspecialchars($pkg->name_bn, ENT_QUOTES, 'UTF-8'); ?></h4>
                        <?php if ($is_current): ?>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-indigo-600 text-white">বর্তমান সক্রিয়</span>
                        <?php endif; ?>
                    </div>

                    <div class="mb-4 pb-4 border-b border-neutral-100">
                        <div class="text-2xl font-black text-neutral-900 bengali-num">
                            <?php if ($pkg->price_yearly > 0): ?>
                                &#2547; <?= bn_num(number_format($pkg->price_yearly)); ?>
                                <span class="text-xs font-normal text-neutral-500">/ বছর</span>
                            <?php else: ?>
                                কাস্টম মূল্য
                                <span class="text-xs font-normal text-neutral-500">/ আলোচনা সাপেক্ষে</span>
                            <?php endif; ?>
                        </div>
                        <?php if ($pkg->price_monthly > 0): ?>
                            <div class="text-[11px] text-neutral-400 mt-0.5">বা মাসিক &#2547; <?= bn_num(number_format($pkg->price_monthly)); ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="space-y-2.5 text-xs text-neutral-600 mb-6">
                        <div class="font-bold text-neutral-700 text-[11px] uppercase tracking-wider mb-1">সুবিধাসমূহ:</div>
                        <?php foreach ($features as $f): if (!empty(trim($f))): ?>
                            <div class="flex items-start gap-2">
                                <i class="ph-bold ph-check text-emerald-600 text-sm shrink-0 mt-0.5"></i>
                                <span><?= htmlspecialchars(trim($f), ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                        <?php endif; endforeach; ?>
                    </div>
                </div>

                <div class="space-y-2 pt-2 border-t border-neutral-100">
                    <?php 
                        if ($is_current) {
                            $pkg_btn_cls = 'bg-indigo-50 text-indigo-700 hover:bg-indigo-100';
                        } elseif ($is_higher) {
                            $pkg_btn_cls = 'bg-indigo-600 hover:bg-indigo-700 text-white shadow-2xs';
                        } else {
                            $pkg_btn_cls = 'bg-neutral-100 hover:bg-neutral-200 text-neutral-800 shadow-2xs';
                        }
                    ?>
                    <?php if ($is_current): ?>
                        <button type="button" onclick="selectPlanForRenewal(<?= $pkg->package_id ?>, '<?= htmlspecialchars($pkg->name_bn, ENT_QUOTES, 'UTF-8') ?>', <?= (int)$pkg->price_yearly ?>, false)" class="w-full block text-center py-2.5 rounded-xl font-bold text-xs transition cursor-pointer <?= $pkg_btn_cls ?>">
                            <i class="ph-bold ph-arrows-clockwise mr-1"></i> এই প্ল্যান নবায়ন করুন
                        </button>
                    <?php else: ?>
                        <button type="button" onclick="selectPlanForRenewal(<?= $pkg->package_id ?>, '<?= htmlspecialchars($pkg->name_bn, ENT_QUOTES, 'UTF-8') ?>', <?= (int)$pkg->price_yearly ?>, <?= $is_higher ? 'true' : 'false' ?>)" class="w-full block text-center py-2.5 rounded-xl font-bold text-xs transition cursor-pointer <?= $pkg_btn_cls ?>">
                            <i class="ph-bold <?= $is_higher ? 'ph-arrow-up-right' : 'ph-arrows-left-right' ?> mr-1"></i>
                            <?= $is_higher ? 'এই প্যাকেজে আপগ্রেড করুন' : 'এই প্যাকেজে পরিবর্তন করুন' ?>
                        </button>

                        <!-- Instant Package Switch Form -->
                        <form action="subscription/change_package" method="POST" onsubmit="return confirm('আপনি কি নিশ্চিত যে আপনার বর্তমান প্যাকেজ পরিবর্তন করে <?= htmlspecialchars($pkg->name_bn, ENT_QUOTES, 'UTF-8') ?> প্যাকেজে স্যুইচ করতে চান?');">
                            <input type="hidden" name="package_id" value="<?= $pkg->package_id ?>">
                            <button type="submit" class="w-full text-center py-1 text-[11px] text-neutral-400 hover:text-indigo-600 hover:underline transition cursor-pointer">
                                তাৎক্ষণিক প্যাকেজ স্যুইচ করুন
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; endif; ?>
        </div>
    </div>

    <!-- Payment & Renewal History Table -->
    <div class="bg-white rounded-3xl border border-neutral-200/90 shadow-sm p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-neutral-100">
            <div>
                <h3 class="text-base font-bold text-neutral-900 flex items-center gap-2">
                    <i class="ph-bold ph-receipt text-indigo-600"></i>
                    নবায়ন ও পেমেন্ট হিস্ট্রি
                </h3>
                <p class="text-xs text-neutral-500 mt-0.5">আপনার পরিষদের জমাকৃত সাবস্ক্রিপশন পেমেন্ট ও ইনভয়েস তালিকা</p>
            </div>
            <span class="text-xs text-neutral-400 font-medium">মোট <?= bn_num(count($payments)); ?> টি রেকর্ড</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left border-collapse">
                <thead>
                    <tr class="bg-neutral-50 text-neutral-500 border-y border-neutral-200/80 font-bold">
                        <th class="py-3 px-4">ইনভয়েস নং</th>
                        <th class="py-3 px-4">তারিখ</th>
                        <th class="py-3 px-4">পেমেন্ট মাধ্যম</th>
                        <th class="py-3 px-4">TrxID / রেফারেন্স</th>
                        <th class="py-3 px-4 text-right">টাকার পরিমাণ</th>
                        <th class="py-3 px-4 text-center">স্ট্যাটাস</th>
                        <th class="py-3 px-4 text-center">ইনভয়েস ও রসিদ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 text-neutral-700">
                    <?php if (!empty($payments)): foreach ($payments as $pay): ?>
                    <tr class="hover:bg-neutral-50/60 transition-colors">
                        <td class="py-3 px-4 font-mono font-bold text-indigo-600">
                            <?= htmlspecialchars($pay->invoice_no, ENT_QUOTES, 'UTF-8'); ?>
                        </td>
                        <td class="py-3 px-4">
                            <?= !empty($pay->paid_at) ? bn_num(date('d/m/Y', strtotime($pay->paid_at))) : bn_num(date('d/m/Y', strtotime($pay->created_at))); ?>
                        </td>
                        <td class="py-3 px-4 uppercase font-bold text-neutral-600">
                            <?= htmlspecialchars($pay->payment_method, ENT_QUOTES, 'UTF-8'); ?>
                        </td>
                        <td class="py-3 px-4 font-mono text-neutral-800">
                            <?= htmlspecialchars($pay->reference ?? '—', ENT_QUOTES, 'UTF-8'); ?>
                        </td>
                        <td class="py-3 px-4 text-right font-black text-neutral-900 bengali-num">
                            &#2547; <?= bn_num(number_format((float)$pay->amount)); ?>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                সফল / রেকর্ডকৃত
                            </span>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <a href="subscription/invoice/<?= htmlspecialchars($pay->invoice_no, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs transition border border-indigo-200/80 shadow-2xs">
                                <i class="ph-bold ph-printer text-sm"></i>
                                <span>ইনভয়েস দেখুন</span>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr>
                        <td colspan="7" class="py-8 text-center text-neutral-400">
                            এখনো কোনো পূর্ববর্তী পেমেন্ট রেকর্ড নেই।
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Client-side Interactive Script for Plan Selection and Auto-Scroll -->
<script>
    function selectPlanForRenewal(pkgId, pkgName, price, isUpgrade) {
        const select = document.getElementById('planSelectInput');
        const amountInput = document.getElementById('planAmountInput');
        const notice = document.getElementById('planChangeNotice');
        const formEl = document.getElementById('renewalForm');

        if (select) {
            select.value = pkgId;
        }
        if (amountInput && price > 0) {
            amountInput.value = price;
        }
        if (notice) {
            notice.innerHTML = isUpgrade ? '★ আপগ্রেড নির্বাচন: ' + pkgName : 'নির্বাচিত: ' + pkgName;
        }
        if (formEl) {
            formEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
            const refInput = document.querySelector('input[name="reference"]');
            if (refInput) {
                setTimeout(() => refInput.focus(), 600);
            }
        }
    }

    function handlePlanSelectChange(select) {
        const opt = select.options[select.selectedIndex];
        const price = opt.getAttribute('data-price');
        const name = opt.getAttribute('data-name');
        const amountInput = document.getElementById('planAmountInput');
        const notice = document.getElementById('planChangeNotice');

        if (amountInput && price && parseInt(price) > 0) {
            amountInput.value = price;
        }
        if (notice) {
            notice.innerHTML = 'নির্বাচিত প্যাকেজ: ' + (name || '');
        }
    }
</script>
