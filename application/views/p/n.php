<body class="min-h-screen flex flex-col bg-slate-50 antialiased selection:bg-indigo-200 selection:text-indigo-950">

    <?php
    $tenant_sub = function_exists('tenant_subscription') ? tenant_subscription() : null;
    $tenant_sub_expired = function_exists('is_tenant_subscription_expired') ? is_tenant_subscription_expired() : false;

    $sub_plan_title = $tenant_sub->pkg_name_bn ?? ($tenant_sub->name_bn ?? 'স্ট্যান্ডার্ড');
    $sub_cycle_label = ($tenant_sub && ($tenant_sub->billing_cycle ?? '') === 'monthly') ? 'মাসিক' : 'বার্ষিক';

    $sub_days_remaining = 0;
    $sub_next_payment_formatted = 'নির্ধারিত নেই';

    if ($tenant_sub && !empty($tenant_sub->end_date)) {
        try {
            $today_dt = new DateTime(date('Y-m-d'));
            $end_dt = new DateTime($tenant_sub->end_date);
            $diff_dt = $today_dt->diff($end_dt);
            $sub_days_remaining = $diff_dt->invert ? 0 : (int)$diff_dt->days;

            $en_m = ['January','February','March','April','May','June','July','August','September','October','November','December'];
            $bn_m = ['জানুয়ারি','ফেব্রুয়ারি','মার্চ','এপ্রিল','মে','জুন','জুলাই','আগস্ট','সেপ্টেম্বর','অক্টোবর','নভেম্বর','ডিসেম্বর'];
            $formatted_date = date('d F, Y', strtotime($tenant_sub->end_date));
            $formatted_date = str_replace($en_m, $bn_m, $formatted_date);
            $sub_next_payment_formatted = bn_num($formatted_date);
        } catch (Exception $e) {
            $sub_days_remaining = 0;
        }
    }

    $sub_btn_theme_cls = $tenant_sub_expired 
        ? 'bg-rose-950/70 border-rose-700/80 hover:bg-rose-900/80 text-rose-200' 
        : 'bg-neutral-800 border-neutral-700 hover:bg-neutral-700 hover:border-neutral-600 text-neutral-200';

    $sub_card_header_theme_cls = $tenant_sub_expired 
        ? 'from-rose-50 via-white to-rose-50/50 border-b border-rose-100' 
        : 'from-indigo-50/80 via-white to-amber-50/40 border-b border-neutral-100';
    ?>

    <?php if (!empty($temp_support_info)): ?>
    <!-- Temporary Support Session Banner -->
    <div class="bg-linear-to-r from-amber-500 via-amber-400 to-amber-500 text-neutral-950 font-bold text-xs py-2 px-4 shadow-sm z-50 sticky top-0 border-b border-amber-600/40">
        <div class="max-w-7xl mx-auto flex items-center justify-between gap-4">
            <span class="flex items-center gap-2">
                <i class="ph-bold ph-shield-warning text-base text-amber-950 animate-pulse"></i>
                <span>অস্থায়ী কারিগরি সহায়তা মোড সক্রিয় — নির্ধারিত মেয়াদ বাকি: <strong><?= bn_num($temp_support_info['hours_left']); ?> ঘণ্টা <?= bn_num($temp_support_info['minutes_left']); ?> মিনিট</strong></span>
            </span>
            <a href="logout" class="px-3 py-1 rounded-lg bg-neutral-950 hover:bg-neutral-800 text-white text-[11px] font-bold transition shadow-xs">
                সহায়তা শেষ করুন &rarr;
            </a>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($tenant_sub_expired): ?>
    <!-- Expired Subscription Read-Only Mode Banner -->
    <div class="bg-linear-to-r from-rose-700 via-rose-600 to-rose-700 text-white font-semibold text-xs py-2.5 px-4 shadow-md z-40 sticky top-0 border-b border-rose-800">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <span class="flex items-center justify-center w-6 h-6 rounded-full bg-white/20 text-white shrink-0 animate-pulse">
                    <i class="ph-bold ph-warning-octagon text-sm"></i>
                </span>
                <span>
                    <strong class="font-bold underline decoration-rose-300">সাবস্ক্রিপশন মেয়াদোত্তীর্ণ (রিড-অনলি মোড):</strong>
                    আপনার ইউনিয়ন পরিষদের সাবস্ক্রিপশন প্ল্যানের মেয়াদ শেষ হয়েছে। আপনি ড্যাশবোর্ড ও সকল রেকর্ড দেখতে পারবেন, তবে নতুন তথ্য তৈরি বা সংশোধন বন্ধ রয়েছে।
                </span>
            </div>
            <a href="subscription" class="px-3.5 py-1.5 rounded-xl bg-white text-rose-800 hover:bg-rose-50 font-bold text-xs transition-colors shrink-0 shadow-xs flex items-center gap-1.5">
                <i class="ph-bold ph-arrow-clockwise"></i>
                <span>নবায়ন করুন &rarr;</span>
            </a>
        </div>
    </div>
    <?php endif; ?>

    <!-- Primary Top Header Bar -->
    <header class="bg-neutral-900 text-white border-b border-neutral-800 sticky <?= !empty($temp_support_info) ? 'top-8.5' : 'top-0' ?> z-40">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-2">
            
            <!-- Left: Union Parishad Branding -->
            <div class="flex items-center gap-2.5 sm:gap-3.5 min-w-0">
                <a href="dashboard" class="flex items-center gap-2.5 sm:gap-3 group min-w-0">
                    <?php if (!empty($ins_info->ins_logo_path)): ?>
                        <img src="<?= media_url($ins_info->ins_logo_path, 'image/png'); ?>" alt="Logo" class="w-9 h-9 sm:w-10 sm:h-10 rounded-full object-contain bg-white p-0.5 shadow-xs shrink-0">
                    <?php else: ?>
                        <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold text-base sm:text-lg shadow-xs shrink-0">
                            <i class="ph-bold ph-buildings"></i>
                        </div>
                    <?php endif; ?>
                    <div class="min-w-0">
                        <h1 class="text-sm sm:text-lg font-bold tracking-tight text-white leading-tight truncate group-hover:text-indigo-300 transition-colors">
                            <?= htmlspecialchars($ins_info->ins_name ?? 'ইউনিয়ন পরিষদ', ENT_QUOTES, 'UTF-8'); ?>
                        </h1>
                        <p class="text-[10px] sm:text-xs text-neutral-400 truncate hidden xs:block sm:block">ডিজিটাল ইউনিয়ন ম্যানেজমেন্ট সিস্টেম</p>
                    </div>
                </a>
            </div>

            <!-- Right Controls & Profile -->
            <div class="flex items-center gap-2 sm:gap-4">
                
                <!-- Super Admin Quick Jump (If Privileged) -->
                <?php /* The platform panel belongs to the 'sadmin' role alone —
                        this is Super_admin::__construct()'s gate, verbatim. It used
                        to also test is_admin() and user_id == 1, and is_admin() is
                        in_group('admin') — the group every union parishad's own
                        administrator holds (users 8, 9, 11: কাঞ্চন, বাঘমারা, রূপসা).
                        So all three tenant admins were shown a "সুপার অ্যাডমিন"
                        button that led to a 403. A link the target refuses is a
                        leak of what the platform panel is, not a convenience. */ ?>
                <?php if ($this->ion_auth->in_group('sadmin')): ?>
                    <a href="super_admin" class="hidden md:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-400 hover:bg-amber-500 text-neutral-950 font-bold text-xs shadow-xs transition-colors">
                        <i class="ph-bold ph-shield-star text-sm"></i>
                        <span>সুপার অ্যাডমিন</span>
                    </a>
                <?php endif; ?>

                <!-- Subscription Plan Badge & Dropdown Card -->
                <div class="relative hidden sm:block" id="subPlanDropdown">
                    <button type="button" onclick="toggleDropdown('subPlanDropdownContent')" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl border transition-all cursor-pointer <?= $sub_btn_theme_cls; ?>">
                        <span class="flex items-center justify-center w-5 h-5 rounded-lg <?= $tenant_sub_expired ? 'bg-rose-500/20 text-rose-400' : 'bg-amber-400/20 text-amber-300' ?>">
                            <i class="ph-bold <?= $tenant_sub_expired ? 'ph-warning' : 'ph-sparkle' ?> text-xs"></i>
                        </span>
                        <div class="flex items-center gap-1.5 text-xs">
                            <span class="font-bold text-white"><?= htmlspecialchars($sub_plan_title, ENT_QUOTES, 'UTF-8'); ?></span>
                            <?php if ($tenant_sub_expired): ?>
                                <span class="px-1.5 py-0.5 rounded-md text-[10px] font-extrabold bg-rose-500/20 text-rose-300 border border-rose-500/40">মেয়াদ শেষ</span>
                            <?php elseif ($sub_days_remaining <= 30): ?>
                                <span class="px-1.5 py-0.5 rounded-md text-[10px] font-extrabold bg-amber-500/20 text-amber-300 border border-amber-500/40"><?= bn_num($sub_days_remaining); ?> দিন বাকি</span>
                            <?php else: ?>
                                <span class="px-1.5 py-0.5 rounded-md text-[10px] font-medium bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">সক্রিয়</span>
                            <?php endif; ?>
                        </div>
                        <i class="ph-bold ph-caret-down text-[10px] text-neutral-400 transition-transform duration-200"></i>
                    </button>

                    <!-- Subscription Plan Dropdown Card -->
                    <div id="subPlanDropdownContent" class="tenant-dropdown-menu hidden absolute right-0 mt-2 rounded-2xl bg-white shadow-2xl border border-neutral-200 overflow-hidden z-50 text-neutral-800 text-xs" style="width: 360px; min-width: 340px; max-width: calc(100vw - 1.5rem);">
                        <!-- Card Top / Plan Badge -->
                        <div class="p-4 bg-linear-to-br <?= $sub_card_header_theme_cls; ?>">
                            <div class="flex items-center justify-between mb-2">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold <?= $tenant_sub_expired ? 'bg-rose-100 text-rose-700' : 'bg-indigo-100 text-indigo-700' ?>">
                                    <i class="ph-bold <?= $tenant_sub_expired ? 'ph-warning-circle' : 'ph-crown' ?>"></i>
                                    <?= htmlspecialchars($sub_plan_title, ENT_QUOTES, 'UTF-8'); ?> প্ল্যান
                                </span>
                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-extrabold tracking-wide <?= $tenant_sub_expired ? 'bg-rose-600 text-white' : 'bg-emerald-600 text-white' ?>">
                                    <?= $tenant_sub_expired ? 'মেয়াদোত্তীর্ণ' : 'সক্রিয় প্ল্যান' ?>
                                </span>
                            </div>
                            <div class="flex items-baseline justify-between mt-2 pt-1 border-t border-neutral-200/40">
                                <div>
                                    <span class="text-2xl font-black text-neutral-900 leading-none">
                                        &#2547; <?= bn_num(number_format((float)($tenant_sub->price_yearly ?? 0))); ?>
                                    </span>
                                    <span class="text-xs text-neutral-500 font-medium">/ <?= $sub_cycle_label ?></span>
                                </div>
                                <span class="text-xs text-neutral-500">
                                    বিলিং চক্র: <strong class="text-neutral-700"><?= $sub_cycle_label ?></strong>
                                </span>
                            </div>
                        </div>

                        <!-- Card Body: Days Remaining & Next Payment Date -->
                        <div class="p-4 space-y-3 bg-white">
                            <div class="grid grid-cols-2 gap-2.5">
                                <div class="p-3 rounded-xl bg-neutral-50 border border-neutral-100 flex flex-col justify-between">
                                    <span class="flex items-center gap-1.5 text-[11px] font-semibold text-neutral-500 mb-1">
                                        <i class="ph-bold ph-hourglass-medium text-indigo-500"></i> অবশিষ্ট মেয়াদ
                                    </span>
                                    <div>
                                        <strong class="block text-sm font-black <?= $tenant_sub_expired ? 'text-rose-600' : ($sub_days_remaining <= 30 ? 'text-amber-600' : 'text-neutral-900') ?>">
                                            <?php if ($tenant_sub_expired): ?>
                                                মেয়াদ শেষ
                                            <?php else: ?>
                                                <?= bn_num($sub_days_remaining); ?> দিন বাকি
                                            <?php endif; ?>
                                        </strong>
                                        <span class="block text-[10px] text-neutral-400 mt-0.5">পরিকল্পনা সক্রিয়</span>
                                    </div>
                                </div>

                                <div class="p-3 rounded-xl bg-neutral-50 border border-neutral-100 flex flex-col justify-between">
                                    <span class="flex items-center gap-1.5 text-[11px] font-semibold text-neutral-500 mb-1">
                                        <i class="ph-bold ph-calendar-check text-emerald-500"></i> পরবর্তী পেমেন্ট
                                    </span>
                                    <div>
                                        <strong class="block text-xs font-bold text-neutral-900 whitespace-nowrap">
                                            <?= $sub_next_payment_formatted; ?>
                                        </strong>
                                        <span class="block text-[10px] text-neutral-400 mt-0.5">নবায়ন বিলিং তারিখ</span>
                                    </div>
                                </div>
                            </div>

                            <?php if ($tenant_sub_expired): ?>
                            <!-- Expired Alert Box -->
                            <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-[11px] space-y-1 leading-relaxed">
                                <div class="flex items-center gap-1.5 font-bold text-rose-900">
                                    <i class="ph-bold ph-warning-octagon text-sm"></i>
                                    <span>রিড-অনলি মোড সক্রিয়</span>
                                </div>
                                <p>আপনার সাবস্ক্রিপশন প্ল্যানের মেয়াদ উত্তীর্ণ হয়েছে। বর্তমানে সব রেকর্ড শুধু দেখা যাবে, নতুন তথ্য এন্ট্রি বন্ধ রয়েছে।</p>
                            </div>
                            <?php endif; ?>

                            <!-- Bottom Actions & Balance -->
                            <div class="border-t border-neutral-100 pt-3 space-y-2">
                                <div class="flex items-center justify-between text-[11px] text-neutral-500">
                                    <span>পরিষদ কোড: <strong><?= bn_num(tenant_code()); ?></strong></span>
                                    <span>স্ট্যাটাস: <strong class="<?= $tenant_sub_expired ? 'text-rose-600' : 'text-emerald-600' ?>"><?= $tenant_sub_expired ? 'নিষ্ক্রিয়' : 'সচল' ?></strong></span>
                                    <!-- Keep hidden class for AJAX balance update compatibility -->
                                    <span class="ins_total_bal_now hidden">0</span>
                                </div>
                                <div class="pt-1">
                                    <a href="subscription" class="w-full text-center py-2 px-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs transition-colors shadow-xs flex items-center justify-center gap-1.5">
                                        <i class="ph-bold ph-credit-card"></i>
                                        <span>প্ল্যান বিস্তারিত ও নবায়ন &rarr;</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Public Portal Link -->
                <a href="<?= base_url(); ?>" target="_blank" class="p-2 rounded-lg text-neutral-400 hover:text-white hover:bg-neutral-800 transition-colors" title="পাবলিক পোর্টাল দেখুন">
                    <i class="ph-bold ph-arrow-square-out text-lg"></i>
                </a>

                <!-- User Dropdown Menu -->
                <div class="relative" id="userMenuDropdown">
                    <button type="button" onclick="toggleDropdown('userMenuDropdownContent')" class="flex items-center gap-2.5 p-1.5 rounded-xl hover:bg-neutral-800 transition-colors cursor-pointer">
                        <div class="w-8 h-8 rounded-full bg-indigo-600 text-white font-bold text-xs flex items-center justify-center border-2 border-indigo-400 shadow-xs">
                            <?= mb_substr($loggedUser['userName'] ?? 'U', 0, 1, 'UTF-8'); ?>
                        </div>
                        <div class="hidden md:block text-left">
                            <span class="block text-xs font-bold text-white leading-tight">
                                <?= htmlspecialchars($loggedUser['userName'] ?? 'অ্যাডমিন', ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                            <span class="block text-[11px] text-neutral-400 leading-tight">
                                <?= htmlspecialchars($loggedUser['userGroupName'] ?? 'প্রশাসক', ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </div>
                        <i class="ph-bold ph-caret-down text-xs text-neutral-400"></i>
                    </button>

                    <!-- Dropdown Content -->
                    <div id="userMenuDropdownContent" class="tenant-dropdown-menu hidden absolute right-0 w-56 max-w-[calc(100vw-1.5rem)] rounded-2xl bg-white shadow-xl border border-neutral-200 py-2 z-50 text-neutral-800 text-xs">
                        <div class="px-4 py-2.5 border-b border-neutral-100">
                            <strong class="block font-bold text-neutral-900"><?= htmlspecialchars($loggedUser['userName'] ?? 'ইউজার', ENT_QUOTES, 'UTF-8'); ?></strong>
                            <span class="text-neutral-500 text-[11px]"><?= htmlspecialchars($loggedUser['userEmail'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <a href="dashboard" class="flex items-center gap-2.5 px-4 py-2 hover:bg-indigo-50 hover:text-indigo-600 transition-colors font-medium">
                            <i class="ph-bold ph-gauge text-sm text-indigo-600"></i> ড্যাশবোর্ড
                        </a>
                        <a href="my_profile" class="flex items-center gap-2.5 px-4 py-2 hover:bg-indigo-50 hover:text-indigo-600 transition-colors font-medium">
                            <i class="ph-bold ph-user-circle text-sm text-neutral-500"></i> আমার প্রোফাইল
                        </a>
                        <a href="subscription" class="flex items-center gap-2.5 px-4 py-2 hover:bg-indigo-50 hover:text-indigo-600 transition-colors font-medium">
                            <i class="ph-bold ph-credit-card text-sm text-amber-500"></i> সাবস্ক্রিপশন ও নবায়ন
                        </a>
                        <a href="support" class="flex items-center gap-2.5 px-4 py-2 hover:bg-indigo-50 hover:text-indigo-600 transition-colors font-medium">
                            <i class="ph-bold ph-headset text-sm text-indigo-600"></i> হেল্প ও সাপোর্ট
                        </a>
                        <button type="button" onclick="openTenantReviewModal()" class="w-full text-left flex items-center gap-2.5 px-4 py-2 hover:bg-amber-50 hover:text-amber-700 transition-colors font-medium cursor-pointer">
                            <i class="ph-bold ph-star text-sm text-amber-500"></i> ViretaDev রিভিউ দিন
                        </button>
                        <div class="border-t border-neutral-100 my-1"></div>
                        <a href="logout" class="flex items-center gap-2.5 px-4 py-2 text-rose-600 hover:bg-rose-50 transition-colors font-medium">
                            <i class="ph-bold ph-sign-out text-sm"></i> লগআউট
                        </a>
                    </div>
                </div>

            </div>

        </div>
    </header>

    <!-- Sub-Navbar Navigation Bar -->
    <nav class="bg-white border-b border-neutral-200 shadow-2xs sticky top-16 z-30 w-full">
        <div class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between min-h-12 py-1">
                
                <div class="tenant-subnav-items flex items-center gap-1 sm:gap-2 py-1 max-w-full">
                    
                    <?php
                        $nav_uri = uri_string();
                        $subnav_link_cls = function(bool $active): string {
                            return $active
                                ? 'bg-indigo-50 text-indigo-700 font-bold'
                                : 'hover:bg-neutral-100 text-neutral-600 font-medium';
                        };
                        $subnav_icon_cls = function(bool $active): string {
                            return $active ? 'text-indigo-600' : 'text-neutral-500';
                        };
                    ?>

                    <!-- Dashboard Link -->
                    <?php $is_dash = ($nav_uri === 'dashboard' || $nav_uri === 'dashboard_u' || $nav_uri === ''); ?>
                    <a href="dashboard" class="shrink-0 whitespace-nowrap px-3 sm:px-3.5 py-1.5 rounded-xl text-xs sm:text-sm flex items-center gap-1.5 transition-colors <?= $subnav_link_cls($is_dash); ?>">
                        <i class="ph-bold ph-gauge text-base <?= $subnav_icon_cls($is_dash); ?>"></i>
                        <span>ড্যাশবোর্ড</span>
                    </a>

                    <!-- Holding Tax Dropdown -->
                    <?php $is_holding = in_array($nav_uri, ['taxCollection', 'receipt_show', 'searchHoldingss', 'view_datas', 'view_data_doc', 'addData', 'cashDeposite', 'reports']); ?>
                    <div class="relative shrink-0 flex items-center" id="holdingMenuDropdown">
                        <button type="button" onclick="toggleDropdown('holdingMenuDropdownContent')" class="shrink-0 whitespace-nowrap px-2.5 sm:px-3 py-1.5 rounded-xl text-xs sm:text-sm flex items-center gap-1.5 transition-colors cursor-pointer <?= $subnav_link_cls($is_holding); ?>">
                            <i class="ph-bold ph-house-line text-base text-neutral-500"></i>
                            <span>হোল্ডিং কর</span>
                            <i class="ph-bold ph-caret-down text-[10px] text-neutral-400 transition-transform duration-200"></i>
                        </button>
                        <div id="holdingMenuDropdownContent" class="tenant-dropdown-menu hidden absolute left-0 max-w-[calc(100vw-1.5rem)] w-60 rounded-2xl bg-white shadow-xl border border-neutral-200 py-2 z-50 text-neutral-800 text-xs">
                            <a href="taxCollection" class="flex items-center gap-2.5 px-4 py-2 hover:bg-indigo-50 hover:text-indigo-600 font-semibold text-indigo-700 transition-colors">
                                <i class="ph-bold ph-hand-coins text-sm text-indigo-600"></i> ট্যাক্স আদায়
                            </a>
                            <a href="receipt_show" class="flex items-center gap-2.5 px-4 py-2 hover:bg-neutral-50 font-medium transition-colors">
                                <i class="ph-bold ph-receipt text-sm text-emerald-600"></i> রশিদ প্রিন্ট
                            </a>
                            <div class="border-t border-neutral-100 my-1"></div>
                            <a href="searchHoldingss" class="flex items-center gap-2.5 px-4 py-2 hover:bg-neutral-50 font-medium transition-colors">
                                <i class="ph-bold ph-magnifying-glass text-sm text-sky-600"></i> হোল্ডিং খুঁজুন
                            </a>
                            <a href="view_datas" class="flex items-center gap-2.5 px-4 py-2 hover:bg-neutral-50 font-medium transition-colors">
                                <i class="ph-bold ph-list-dashes text-sm text-neutral-600"></i> হোল্ডিং তালিকা
                            </a>
                            <a href="view_data_doc" class="flex items-center gap-2.5 px-4 py-2 hover:bg-neutral-50 font-medium transition-colors">
                                <i class="ph-bold ph-file-arrow-down text-sm text-amber-600"></i> ওয়ার্ডভিত্তিক Doc ডাউনলোড
                            </a>
                            <a href="addData" class="flex items-center gap-2.5 px-4 py-2 hover:bg-neutral-50 font-medium transition-colors">
                                <i class="ph-bold ph-plus-circle text-sm text-emerald-600"></i> নতুন হোল্ডিং যোগ
                            </a>
                            <div class="border-t border-neutral-100 my-1"></div>
                            <a href="cashDeposite" class="flex items-center gap-2.5 px-4 py-2 hover:bg-neutral-50 font-medium transition-colors">
                                <i class="ph-bold ph-bank text-sm text-indigo-600"></i> ক্যাশ ডিপোজিট
                            </a>
                            <a href="reports" class="flex items-center gap-2.5 px-4 py-2 hover:bg-neutral-50 font-medium transition-colors">
                                <i class="ph-bold ph-chart-bar text-sm text-rose-600"></i> কর আদায় রিপোর্ট
                            </a>
                        </div>
                    </div>

                    <!-- Sonod Dropdown -->
                    <?php if (!$this->ion_auth->in_group('collect')): ?>
                    <?php $is_sonod = in_array($nav_uri, ['newSonod', 'sonod_list', 'addMySonod', 'sonodTheme', 'sonod_audit']); ?>
                    <div class="relative shrink-0 flex items-center" id="sonodMenuDropdown">
                        <button type="button" onclick="toggleDropdown('sonodMenuDropdownContent')" class="shrink-0 whitespace-nowrap px-2.5 sm:px-3 py-1.5 rounded-xl text-xs sm:text-sm flex items-center gap-1.5 transition-colors cursor-pointer <?= $subnav_link_cls($is_sonod); ?>">
                            <i class="ph-bold ph-certificate text-base text-neutral-500"></i>
                            <span>সনদ</span>
                            <i class="ph-bold ph-caret-down text-[10px] text-neutral-400 transition-transform duration-200"></i>
                        </button>
                        <div id="sonodMenuDropdownContent" class="tenant-dropdown-menu hidden absolute right-0 sm:right-auto sm:left-0 w-52 max-w-[calc(100vw-1.5rem)] rounded-2xl bg-white shadow-xl border border-neutral-200 py-2 z-50 text-neutral-800 text-xs">
                            <a href="newSonod" class="flex items-center gap-2.5 px-4 py-2 hover:bg-indigo-50 hover:text-indigo-600 font-medium transition-colors whitespace-nowrap">
                                <i class="ph-bold ph-plus-circle text-sm text-indigo-600"></i> নতুন সনদ তৈরি
                            </a>
                            <a href="sonod_list" class="flex items-center gap-2.5 px-4 py-2 hover:bg-indigo-50 hover:text-indigo-600 font-medium transition-colors whitespace-nowrap">
                                <i class="ph-bold ph-list-dashes text-sm text-emerald-600"></i> ইস্যুকৃত সনদের তালিকা
                            </a>
                            <a href="addMySonod" class="flex items-center gap-2.5 px-4 py-2 hover:bg-indigo-50 hover:text-indigo-600 font-medium transition-colors whitespace-nowrap">
                                <i class="ph-bold ph-sliders-horizontal text-sm text-sky-600"></i> সনদ কাস্টমাইজেশন
                            </a>
                            <a href="sonodTheme" class="flex items-center gap-2.5 px-4 py-2 hover:bg-indigo-50 hover:text-indigo-600 font-medium transition-colors whitespace-nowrap">
                                <i class="ph-bold ph-palette text-sm text-purple-600"></i> থিম ও ডিজাইন সেটিংস
                            </a>
                            <div class="my-1 border-t border-neutral-100"></div>
                            <a href="sonod_audit" class="flex items-center gap-2.5 px-4 py-2 hover:bg-amber-50 hover:text-amber-700 font-medium transition-colors whitespace-nowrap">
                                <i class="ph-bold ph-clock-counter-clockwise text-sm text-amber-600"></i> সনদ অডিট লগ
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Approvals Menu (Chairman, Socib, Admin, Sadmin) -->
                    <?php if ($this->ion_auth->in_group(array('admin', 'uphead', 'upsocib', 'sadmin'))): ?>
                        <?php 
                            $tenant_p_id = (int) tenant_code();
                            $pending_count = function_exists('approval_pending_count') ? approval_pending_count($tenant_p_id) : 0;
                            $is_approvals = ($nav_uri === 'approvals');
                        ?>
                        <a href="approvals" class="shrink-0 whitespace-nowrap px-3 sm:px-3.5 py-1.5 rounded-xl text-xs sm:text-sm flex items-center gap-1.5 transition-colors <?= $subnav_link_cls($is_approvals); ?>">
                            <i class="ph-bold ph-seal-check text-base <?= $subnav_icon_cls($is_approvals); ?>"></i>
                            <span>অনুমোদন</span>
                            <?php if ($pending_count > 0): ?>
                                <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-500 text-white animate-pulse">
                                    <?= bn_num($pending_count); ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    <?php endif; ?>

                    <!-- Settings Dropdown -->
                    <?php if ($this->ion_auth->in_group(array('admin', 'uphead', 'upsocib'))): ?>
                    <?php $is_settings = in_array($nav_uri, ['userManagement', 'sliderOption', 'plates_set', 'sys_set', 'emp_view', 'upMem', 'chair_man_sign', 'subscription']); ?>
                    <div class="relative shrink-0 flex items-center" id="settingsMenuDropdown">
                        <button type="button" onclick="toggleDropdown('settingsMenuDropdownContent')" class="shrink-0 whitespace-nowrap px-2.5 sm:px-3 py-1.5 rounded-xl text-xs sm:text-sm flex items-center gap-1.5 transition-colors cursor-pointer <?= $subnav_link_cls($is_settings); ?>">
                            <i class="ph-bold ph-gear text-base text-neutral-500"></i>
                            <span>সেটিং</span>
                            <i class="ph-bold ph-caret-down text-[10px] text-neutral-400 transition-transform duration-200"></i>
                        </button>
                        <div id="settingsMenuDropdownContent" class="tenant-dropdown-menu hidden absolute right-0 sm:right-auto sm:left-0 w-56 max-w-[calc(100vw-1.5rem)] rounded-2xl bg-white shadow-xl border border-neutral-200 py-2 z-50 text-neutral-800 text-xs">
                            <a href="subscription" class="flex items-center gap-2.5 px-4 py-2 hover:bg-neutral-50 font-medium transition-colors">
                                <i class="ph-bold ph-credit-card text-sm text-indigo-600"></i> সাবস্ক্রিপশন ও বিলিং
                            </a>
                            <div class="border-t border-neutral-100 my-1"></div>
                            <a href="userManagement" class="flex items-center gap-2.5 px-4 py-2 hover:bg-neutral-50 font-medium transition-colors">
                                <i class="ph-bold ph-users text-sm text-indigo-600"></i> ইউজার ম্যানেজমেন্ট
                            </a>
                            <a href="sliderOption" class="flex items-center gap-2.5 px-4 py-2 hover:bg-neutral-50 font-medium transition-colors">
                                <i class="ph-bold ph-images text-sm text-sky-600"></i> পোর্টাল স্লাইডার
                            </a>
                            <a href="plates_set" class="flex items-center gap-2.5 px-4 py-2 hover:bg-neutral-50 font-medium transition-colors">
                                <i class="ph-bold ph-paint-brush text-sm text-amber-600"></i> প্লেট সেটিং
                            </a>
                            <a href="sys_set" class="flex items-center gap-2.5 px-4 py-2 hover:bg-neutral-50 font-medium transition-colors">
                                <i class="ph-bold ph-sliders-horizontal text-sm text-neutral-600"></i> সিস্টেম সেটিং
                            </a>
                            <div class="border-t border-neutral-100 my-1"></div>
                            <a href="emp_view" class="flex items-center gap-2.5 px-4 py-2 hover:bg-neutral-50 font-medium transition-colors">
                                <i class="ph-bold ph-identification-badge text-sm text-emerald-600"></i> কর্মকর্তা/কর্মচারী
                            </a>
                            <a href="upMem" class="flex items-center gap-2.5 px-4 py-2 hover:bg-neutral-50 font-medium transition-colors">
                                <i class="ph-bold ph-user-check text-sm text-indigo-600"></i> জনপ্রতিনিধি তালিকা
                            </a>
                            <a href="chair_man_sign" class="flex items-center gap-2.5 px-4 py-2 hover:bg-neutral-50 font-medium transition-colors">
                                <i class="ph-bold ph-pen-nib text-sm text-rose-600"></i> চেয়ারম্যানের স্বাক্ষর
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Support Link -->
                    <?php $is_support = ($nav_uri === 'support'); ?>
                    <a href="support" class="shrink-0 whitespace-nowrap px-3 sm:px-3.5 py-1.5 rounded-xl text-xs sm:text-sm flex items-center gap-1.5 transition-colors <?= $subnav_link_cls($is_support); ?>">
                        <i class="ph-bold ph-headset text-base <?= $subnav_icon_cls($is_support); ?>"></i>
                        <span>সাপোর্ট</span>
                    </a>

                </div>

                <div class="text-xs text-neutral-400 hidden sm:block">
                    আজকের তারিখ: <strong class="text-neutral-700"><?= bn_num(date('d F, Y')); ?></strong>
                </div>

            </div>
        </div>
    </nav>

    <!-- Main Content Container -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6 flex-1 w-full">