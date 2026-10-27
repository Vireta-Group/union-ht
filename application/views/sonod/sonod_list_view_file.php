<!-- Breadcrumb & Header -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-neutral-200/80">
    <div>
        <nav class="flex items-center gap-2 text-xs text-neutral-400 mb-1">
            <a href="dashboard" class="hover:text-indigo-600 transition-colors flex items-center gap-1">
                <i class="ph-bold ph-gauge"></i> ড্যাশবোর্ড
            </a>
            <span>/</span>
            <span class="text-neutral-500">সনদ ব্যবস্থাপনা</span>
            <span>/</span>
            <span class="text-indigo-600 font-semibold">ইস্যুকৃত সনদ তালিকা</span>
        </nav>
        <h2 class="text-xl sm:text-2xl font-extrabold text-neutral-900 tracking-tight flex items-center gap-2">
            <span class="w-2.5 h-6 bg-indigo-600 rounded-full inline-block"></span>
            ইস্যুকৃত ডিজিটাল সনদ তালিকা
        </h2>
        <p class="text-xs text-neutral-500">ইউনিয়ন পরিষদ থেকে এ যাবত প্রস্তুত ও ইস্যুকৃত সকল নাগরিক সনদের তালিকা, প্রিন্ট ও ডাউনলোড সুবিধা</p>
    </div>
    
    <div class="flex items-center gap-2">
        <a href="sonod_audit" class="px-3.5 py-2 rounded-xl bg-amber-50 hover:bg-amber-100 border border-amber-200/90 text-amber-800 font-bold text-xs flex items-center gap-1.5 shadow-2xs transition-all cursor-pointer">
            <i class="ph-bold ph-clock-counter-clockwise text-sm text-amber-600"></i>
            <span>অডিট হিস্ট্রি লগ</span>
        </a>
        <a href="newSonod" class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:scale-98 text-white font-bold text-xs flex items-center gap-1.5 shadow-2xs transition-all cursor-pointer">
            <i class="ph-bold ph-plus-circle text-sm"></i>
            <span>নতুন সনদ প্রদান করুন</span>
        </a>
    </div>
</div>

<?php if ($this->session->flashdata('success')): ?>
    <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-2">
        <i class="ph-bold ph-check-circle text-emerald-600 text-base shrink-0"></i>
        <span><?= htmlspecialchars($this->session->flashdata('success'), ENT_QUOTES, 'UTF-8'); ?></span>
    </div>
<?php endif; ?>
<?php if ($this->session->flashdata('error')): ?>
    <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center gap-2">
        <i class="ph-bold ph-warning-circle text-rose-600 text-base shrink-0"></i>
        <span><?= htmlspecialchars($this->session->flashdata('error'), ENT_QUOTES, 'UTF-8'); ?></span>
    </div>
<?php endif; ?>

<?php 
    $today_str = date('Y-m-d');
    $today_count = 0;
    $types_count = [];
    if (!empty($sonod_list) && is_array($sonod_list)) {
        foreach ($sonod_list as $sn) {
            if (($sn->dating_stampingsss ?? '') === $today_str) {
                $today_count++;
            }
            $t_title = trim((string)($sn->certificate_title_head_text ?? 'অন্যান্য'));
            if ($t_title !== '') {
                $types_count[$t_title] = ($types_count[$t_title] ?? 0) + 1;
            }
        }
    }
?>

<!-- Metric Stats Cards -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div class="bg-white rounded-2xl border border-neutral-200/80 shadow-sm p-4 flex items-center gap-3">
        <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
            <i class="ph-bold ph-certificate text-2xl"></i>
        </div>
        <div>
            <span class="text-neutral-500 text-[11px] font-medium block">মোট ইস্যুকৃত সনদ</span>
            <div class="text-xl font-black text-neutral-900 leading-tight">
                <?= bn_num(count($sonod_list ?? [])); ?> টি
            </div>
            <span class="text-[10px] text-neutral-400">সর্বমোট নিবন্ধিত</span>
        </div>
    </div>

    <div id="card_today_filter" class="bg-white rounded-2xl border border-neutral-200/80 shadow-sm p-4 flex items-center gap-3 cursor-pointer hover:border-emerald-400 hover:shadow-md transition-all group" title="শুধুমাত্র আজকের ইস্যুকৃত সনদ দেখতে ক্লিক করুন">
        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
            <i class="ph-bold ph-calendar-check text-2xl"></i>
        </div>
        <div>
            <span class="text-neutral-500 text-[11px] font-medium block">আজকের ইস্যু</span>
            <div class="text-xl font-black text-emerald-600 leading-tight">
                <?= bn_num($today_count); ?> টি
            </div>
            <span class="text-[10px] text-neutral-400"><?= bn_num(date('d F, Y')); ?></span>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-neutral-200/80 shadow-sm p-4 flex items-center gap-3">
        <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
            <i class="ph-bold ph-squares-four text-2xl"></i>
        </div>
        <div>
            <span class="text-neutral-500 text-[11px] font-medium block">সনদের ধরনসমূহ</span>
            <div class="text-xl font-black text-amber-700 leading-tight">
                <?= bn_num(count($types_count)); ?> প্রকার
            </div>
            <span class="text-[10px] text-neutral-400">সক্রিয় সেবা</span>
        </div>
    </div>
</div>

<!-- Search, Date & Time Filter Panel -->
<div class="bg-white rounded-2xl p-4 sm:p-5 border border-neutral-200/80 shadow-xs space-y-3.5">
    <!-- Top Filter Controls: Search & Select Dropdowns -->
    <div class="flex flex-col lg:flex-row items-center gap-3">
        <div class="relative flex-1 w-full">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-neutral-400">
                <i class="ph-bold ph-magnifying-glass text-base"></i>
            </div>
            <input 
                type="text" 
                id="sonod_table_search"
                class="block w-full pl-9 pr-3.5 py-2.5 rounded-xl bg-neutral-50 border border-neutral-200 text-neutral-900 placeholder-neutral-400 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all" 
                placeholder="নাগরিকের নাম, পিতা, সনদ নম্বর বা আইডি দিয়ে খুঁজুন..."
                autocomplete="off"
            />
        </div>

        <div class="grid grid-cols-2 sm:flex sm:items-center gap-2 w-full lg:w-auto">
            <select id="sonod_type_filter" class="w-full sm:w-44 text-xs px-3 py-2.5 bg-neutral-50 border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 text-neutral-700 font-medium cursor-pointer">
                <option value="">সকল সনদের ধরণ</option>
                <?php foreach (array_keys($types_count) as $tp): ?>
                    <option value="<?= htmlspecialchars($tp, ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($tp, ENT_QUOTES, 'UTF-8'); ?></option>
                <?php endforeach; ?>
            </select>

            <select id="sonod_ward_filter" class="w-full sm:w-32 text-xs px-3 py-2.5 bg-neutral-50 border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 text-neutral-700 font-medium cursor-pointer">
                <option value="">সকল ওয়ার্ড</option>
                <?php for ($w = 1; $w <= 9; $w++): ?>
                    <option value="<?= $w; ?>">ওয়ার্ড ০<?= $w; ?></option>
                <?php endfor; ?>
            </select>

            <select id="sonod_sort_filter" class="w-full sm:w-48 text-xs px-3 py-2.5 bg-neutral-50 border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 text-neutral-700 font-medium cursor-pointer col-span-2 sm:col-span-1">
                <option value="time_desc">⏱️ সর্বশেষ ইস্যুকৃত (নতুন)</option>
                <option value="time_asc">⏱️ পূর্বে ইস্যুকৃত (পুরাতন)</option>
                <option value="no_desc">🔢 সনদ নম্বর (বড়-ছোট)</option>
                <option value="no_asc">🔢 সনদ নম্বর (ছোট-বড়)</option>
            </select>
        </div>
    </div>

    <!-- Date-wise & Time Filters Bar -->
    <div class="pt-3 border-t border-neutral-100 flex flex-col md:flex-row md:items-center justify-between gap-3">
        <!-- Quick Date Preset Pills -->
        <div class="flex flex-wrap items-center gap-1.5" id="datePresetGroup">
            <span class="text-xs font-bold text-neutral-500 mr-1 flex items-center gap-1">
                <i class="ph-bold ph-calendar-check text-indigo-500"></i> তারিখ ফিল্টার:
            </span>
            <button type="button" class="date-preset-btn active px-3 py-1.5 rounded-xl text-xs font-bold transition-all bg-indigo-600 text-white shadow-2xs cursor-pointer" data-range="all">সকল</button>
            <button type="button" class="date-preset-btn px-3 py-1.5 rounded-xl text-xs font-bold transition-all bg-neutral-100 hover:bg-neutral-200 text-neutral-700 cursor-pointer" data-range="today">আজকে (<?= bn_num($today_count); ?>)</button>
            <button type="button" class="date-preset-btn px-3 py-1.5 rounded-xl text-xs font-bold transition-all bg-neutral-100 hover:bg-neutral-200 text-neutral-700 cursor-pointer" data-range="yesterday">গতকাল</button>
            <button type="button" class="date-preset-btn px-3 py-1.5 rounded-xl text-xs font-bold transition-all bg-neutral-100 hover:bg-neutral-200 text-neutral-700 cursor-pointer" data-range="week">গত ৭ দিন</button>
            <button type="button" class="date-preset-btn px-3 py-1.5 rounded-xl text-xs font-bold transition-all bg-neutral-100 hover:bg-neutral-200 text-neutral-700 cursor-pointer" data-range="month">চলতি মাস</button>
        </div>

        <!-- Custom Date Range Picker & Reset -->
        <div class="flex flex-wrap items-center gap-2 sm:gap-2.5">
            <div class="flex items-center gap-1.5 text-xs text-neutral-600">
                <span class="text-[11px] font-bold text-neutral-500 whitespace-nowrap">তারিখ হতে:</span>
                <div class="relative flex items-center group cursor-pointer">
                    <input type="text" id="filter_from_date" 
                        placeholder="শুরুর তারিখ" 
                        class="w-36 sm:w-40 pl-3.5 pr-8 py-2 bg-neutral-50 hover:bg-white focus:bg-white border border-neutral-200 rounded-xl text-xs text-neutral-800 placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 cursor-pointer transition shadow-2xs" 
                        autocomplete="off" />
                    <i class="ph-bold ph-calendar text-indigo-500 absolute right-2.5 pointer-events-none text-base group-hover:scale-105 transition-transform"></i>
                </div>
            </div>
            <div class="flex items-center gap-1.5 text-xs text-neutral-600">
                <span class="text-[11px] font-bold text-neutral-500 whitespace-nowrap">পর্যন্ত:</span>
                <div class="relative flex items-center group cursor-pointer">
                    <input type="text" id="filter_to_date" 
                        placeholder="শেষ তারিখ" 
                        class="w-36 sm:w-40 pl-3.5 pr-8 py-2 bg-neutral-50 hover:bg-white focus:bg-white border border-neutral-200 rounded-xl text-xs text-neutral-800 placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 cursor-pointer transition shadow-2xs" 
                        autocomplete="off" />
                    <i class="ph-bold ph-calendar text-indigo-500 absolute right-2.5 pointer-events-none text-base group-hover:scale-105 transition-transform"></i>
                </div>
            </div>
            <button type="button" id="clear_all_filters" class="px-3.5 py-2 bg-neutral-100 hover:bg-neutral-200 text-neutral-700 rounded-xl text-xs font-bold transition cursor-pointer flex items-center gap-1.5 shadow-2xs shrink-0" title="সকল ফিল্টার রিসেট করুন">
                <i class="ph-bold ph-arrow-counter-clockwise"></i> রিসেট
            </button>
        </div>
    </div>
</div>

<!-- Issued Sonod Table Card -->
<div class="bg-white rounded-2xl border border-neutral-200/80 shadow-sm overflow-hidden">
    <div class="px-5 py-3.5 border-b border-neutral-100 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <i class="ph-bold ph-list text-indigo-600 text-base"></i>
            <span class="text-xs sm:text-sm font-bold text-neutral-900">ইস্যুকৃত সনদের তালিকা বিবরণী</span>
        </div>
        <span class="text-xs text-neutral-500 font-medium">
            মোট রেকর্ড: <b id="filtered_count" class="text-indigo-600"><?= bn_num(count($sonod_list ?? [])); ?></b> টি
        </span>
    </div>

    <?php if (!empty($sonod_list) && count($sonod_list) > 0): ?>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs" id="sonod_data_table">
                <thead>
                    <tr class="bg-neutral-50/80 border-b border-neutral-100 text-neutral-500 font-semibold">
                        <th class="py-3 px-4 w-12 text-center">#</th>
                        <th class="py-3 px-4">সনদ নম্বর / আইডি</th>
                        <th class="py-3 px-4">নাগরিকের তথ্য</th>
                        <th class="py-3 px-4">সনদের ধরণ</th>
                        <th class="py-3 px-3 text-center">ওয়ার্ড</th>
                        <th class="py-3 px-4">ইস্যুর তারিখ ও সময়</th>
                        <th class="py-3 px-4 text-right">ডাউনলোড ও প্রিন্ট অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 text-neutral-800" id="sonod_tbody">
                    <?php $sl = 1; foreach ($sonod_list as $row): 
                        $citizen_name = trim((string)($row->ver_per_info_name_bn ?? '—'));
                        $father_name  = trim((string)($row->ver_per_info_father_name_bn ?? '—'));
                        $sonod_title  = trim((string)($row->certificate_title_head_text ?? 'সনদপত্র'));
                        $ward_num     = (int)($row->ver_per_info_ward_no ?? 0);
                        $sonod_no     = trim((string)($row->cgp_pr_idiidid_pr ?? ''));
                        $uniq_id      = trim((string)($row->sonod_uniqs ?? ''));
                        $issue_date   = trim((string)($row->dating_stampingsss ?? '—'));
                        $t_stamp      = (int)($row->timeing_stmpsss ?? 0);
                        if ($t_stamp <= 0 && !empty($row->dating_stampingsss)) {
                            $t_stamp = strtotime($row->dating_stampingsss);
                        }
                        $time_formatted = ($t_stamp > 0 && !empty($row->timeing_stmpsss)) ? date('h:i A', $t_stamp) : '';
                    ?>
                        <tr class="hover:bg-neutral-50/70 transition-colors sonod_row" 
                            data-name="<?= htmlspecialchars($citizen_name . ' ' . $father_name, ENT_QUOTES, 'UTF-8'); ?>"
                            data-no="<?= htmlspecialchars($sonod_no . ' ' . $uniq_id, ENT_QUOTES, 'UTF-8'); ?>"
                            data-type="<?= htmlspecialchars($sonod_title, ENT_QUOTES, 'UTF-8'); ?>"
                            data-ward="<?= $ward_num; ?>"
                            data-date="<?= htmlspecialchars($issue_date, ENT_QUOTES, 'UTF-8'); ?>"
                            data-timestamp="<?= $t_stamp; ?>"
                            data-sonodno="<?= is_numeric($sonod_no) ? $sonod_no : 0; ?>">
                            
                            <td class="py-3.5 px-4 text-center font-bold text-neutral-400 row-serial">
                                <?= bn_num($sl); ?>
                            </td>

                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="font-bold text-neutral-900 font-mono text-xs">
                                    #<?= htmlspecialchars($sonod_no, ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                                <div class="text-[11px] font-mono text-neutral-400 mt-0.5">
                                    আইডি: <?= htmlspecialchars($uniq_id, ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            </td>

                            <td class="py-3.5 px-4">
                                <div class="font-bold text-neutral-900 text-xs sm:text-sm">
                                    <?= htmlspecialchars($citizen_name, ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                                <div class="text-[11px] text-neutral-500 mt-0.5">
                                    পিতা: <?= htmlspecialchars($father_name, ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            </td>

                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                    <i class="ph-bold ph-certificate text-xs"></i>
                                    <span><?= htmlspecialchars($sonod_title, ENT_QUOTES, 'UTF-8'); ?></span>
                                </span>
                            </td>

                            <td class="py-3.5 px-3 text-center whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-lg text-xs font-semibold bg-neutral-100 text-neutral-700">
                                    <?= ($ward_num > 0) ? 'ওয়ার্ড ' . bn_num($ward_num) : 'সাধারণ'; ?>
                                </span>
                            </td>

                            <td class="py-3.5 px-4 whitespace-nowrap text-xs text-neutral-600">
                                <div class="font-bold text-neutral-800 flex items-center gap-1.5">
                                    <i class="ph-bold ph-calendar-blank text-indigo-500 text-xs"></i>
                                    <span><?= bn_num($issue_date); ?></span>
                                </div>
                                <?php if (!empty($time_formatted)): ?>
                                    <div class="text-[11px] text-neutral-500 font-mono mt-0.5 flex items-center gap-1.5">
                                        <i class="ph-bold ph-clock text-amber-500 text-xs"></i>
                                        <span><?= bn_num($time_formatted); ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($row->issuer_username)): ?>
                                    <div class="text-[10px] text-neutral-400 mt-0.5">কর্তা: <?= htmlspecialchars($row->issuer_username, ENT_QUOTES, 'UTF-8'); ?></div>
                                <?php endif; ?>
                            </td>

                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5 justify-end">
                                    <?php 
                                        $t_stamp = (int)($row->timeing_stmpsss ?? 0);
                                        if ($t_stamp <= 0 && !empty($row->dating_stampingsss)) {
                                            $t_stamp = strtotime($row->dating_stampingsss);
                                        }
                                        $t_diff = time() - $t_stamp;
                                        $is_under_3h = ($t_diff >= 0 && $t_diff <= 10800);
                                        $is_auth = (isset($this->ion_auth) && $this->ion_auth->in_group(array('uphead', 'upsocib', 'admin', 'sadmin')));
                                    ?>
                                    <a href="editIssuedSonod?id=<?= urlencode($uniq_id); ?>" 
                                       style="background-color: <?= ($is_auth || $is_under_3h) ? '#d97706' : '#ea580c'; ?>; color: #ffffff;"
                                       class="px-2.5 py-1.5 rounded-xl font-bold text-xs inline-flex items-center gap-1 shadow-2xs hover:opacity-90 transition" 
                                       title="<?= ($is_auth || $is_under_3h) ? 'সনদ সম্পাদনা করুন (সরাসরি)' : '৩ ঘন্টা অতিক্রান্ত: সম্পাদনা করতে অনুমোদন লাগবে'; ?>">
                                        <i class="ph-bold <?= ($is_auth || $is_under_3h) ? 'ph-pencil-simple' : 'ph-lock-key'; ?> text-xs"></i>
                                        <span>সম্পাদনা</span>
                                    </a>

                                    <a href="psonod?id=<?= urlencode($uniq_id); ?>&download=1" 
                                       style="background-color: #0284c7; color: #ffffff;"
                                       class="px-2.5 py-1.5 rounded-xl font-bold text-xs inline-flex items-center gap-1 shadow-2xs hover:opacity-90 transition" 
                                       title="সরাসরি PDF ফাইল ডাউনলোড করুন">
                                        <i class="ph-bold ph-download-simple text-xs"></i>
                                        <span>ডাউনলোড</span>
                                    </a>

                                    <a href="psonod?id=<?= urlencode($uniq_id); ?>&pad=1" target="_blank" 
                                       style="background-color: #059669; color: #ffffff;"
                                       class="px-2.5 py-1.5 rounded-xl font-bold text-xs inline-flex items-center gap-1 shadow-2xs hover:opacity-90 transition" 
                                       title="অফিসিয়াল প্যাডসহ ভিউ ও প্রিন্ট করুন">
                                        <i class="ph-bold ph-printer text-xs"></i>
                                        <span>প্যাডসহ প্রিন্ট</span>
                                    </a>

                                    <a href="nsonod?id=<?= urlencode($uniq_id); ?>" target="_blank" 
                                       style="background-color: #4f46e5; color: #ffffff;"
                                       class="px-2.5 py-1.5 rounded-xl font-bold text-xs inline-flex items-center gap-1 shadow-2xs hover:opacity-90 transition" 
                                       title="সাদা কাগজে প্যাড ছাড়া প্রিন্ট করুন">
                                        <i class="ph-bold ph-file-text text-xs"></i>
                                        <span>প্যাড ছাড়া</span>
                                    </a>

                                    <?php if (!empty($sonod_no)): ?>
                                        <a href="sonod-v/<?= urlencode($sonod_no); ?>" target="_blank" 
                                           class="px-2 py-1.5 rounded-xl bg-neutral-100 hover:bg-neutral-200 text-neutral-700 font-semibold text-xs inline-flex items-center gap-1 transition" 
                                           title="পাবলিক অনলাইন যাচাই দেখুন">
                                            <i class="ph-bold ph-shield-check text-xs text-sky-600"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php $sl++; endforeach; ?>
                    <tr id="no_results_row" class="hidden">
                        <td colspan="7" class="py-12 text-center text-neutral-400">
                            <div class="w-12 h-12 rounded-2xl bg-neutral-50 text-neutral-300 mx-auto flex items-center justify-center text-xl mb-2">
                                <i class="ph-bold ph-magnifying-glass"></i>
                            </div>
                            <div class="text-xs font-bold text-neutral-700">কোনো তথ্য বা সনদ খুঁজে পাওয়া যায়নি</div>
                            <div class="text-[11px] text-neutral-400 mt-0.5">অন্য কোনো নাম, নম্বর অথবা ভিন্ন তারিখ দিয়ে পুনরায় চেষ্টা করুন</div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="p-12 text-center text-neutral-400 space-y-3">
            <div class="w-14 h-14 rounded-2xl bg-neutral-50 text-neutral-300 mx-auto flex items-center justify-center text-2xl">
                <i class="ph-bold ph-certificate"></i>
            </div>
            <h3 class="text-sm font-bold text-neutral-700">এখনও পর্যন্ত কোন সনদ ইস্যু করা হয়নি</h3>
            <p class="text-xs text-neutral-400 max-w-sm mx-auto">
                উপরে "নতুন সনদ প্রদান করুন" বাটনে চাপ দিয়ে নাগরিকের জাতীয় পরিচয়পত্র বা জন্ম নিবন্ধন দিয়ে তাৎক্ষণিক সনদ তৈরি করুন।
            </p>
            <div class="pt-2">
                <a href="newSonod" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 text-white font-bold text-xs shadow-sm hover:bg-indigo-700 transition">
                    <i class="ph-bold ph-plus-circle text-xs"></i>
                    <span>প্রথম সনদ তৈরি করুন</span>
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    $(document).ready(function() {
        let activePreset = 'all';

        function bn_convert(number) {
            const en = ['0','1','2','3','4','5','6','7','8','9'];
            const bn = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
            return String(number).replace(/[0-9]/g, function(w){
                return bn[+w];
            });
        }

        function formatISODate(d) {
            const year = d.getFullYear();
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            return year + '-' + month + '-' + day;
        }

        function applyFiltersAndSort() {
            const query = $('#sonod_table_search').val().toLowerCase().trim();
            const typeFilter = $('#sonod_type_filter').val();
            const wardFilter = $('#sonod_ward_filter').val();
            const sortBy = $('#sonod_sort_filter').val();

            let fromDate = $('#filter_from_date').val();
            let toDate = $('#filter_to_date').val();

            if (activePreset === 'today') {
                const now = new Date();
                fromDate = toDate = formatISODate(now);
            } else if (activePreset === 'yesterday') {
                const y = new Date();
                y.setDate(y.getDate() - 1);
                fromDate = toDate = formatISODate(y);
            } else if (activePreset === 'week') {
                const now = new Date();
                const w = new Date();
                w.setDate(w.getDate() - 7);
                fromDate = formatISODate(w);
                toDate = formatISODate(now);
            } else if (activePreset === 'month') {
                const now = new Date();
                const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
                const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);
                fromDate = formatISODate(firstDay);
                toDate = formatISODate(lastDay);
            }

            // Sort rows in DOM
            const tbody = $('#sonod_tbody');
            const rows = tbody.find('.sonod_row').get();

            rows.sort(function(a, b) {
                const $a = $(a);
                const $b = $(b);
                const timeA = parseInt($a.data('timestamp'), 10) || 0;
                const timeB = parseInt($b.data('timestamp'), 10) || 0;
                const noA = parseInt($a.data('sonodno'), 10) || 0;
                const noB = parseInt($b.data('sonodno'), 10) || 0;

                if (sortBy === 'time_desc') {
                    return timeB - timeA;
                } else if (sortBy === 'time_asc') {
                    return timeA - timeB;
                } else if (sortBy === 'no_desc') {
                    return noB - noA;
                } else if (sortBy === 'no_asc') {
                    return noA - noB;
                }
                return 0;
            });

            $.each(rows, function(idx, row) {
                tbody.append(row);
            });

            // Keep empty row at bottom
            const emptyRow = $('#no_results_row');
            if (emptyRow.length) {
                tbody.append(emptyRow);
            }

            // Filter visibility
            let visibleCount = 0;

            $(rows).each(function() {
                const row = $(this);
                const name = (row.data('name') || '').toLowerCase();
                const no = (row.data('no') || '').toLowerCase();
                const type = row.data('type') || '';
                const ward = String(row.data('ward') || '');
                const date = (row.data('date') || '').trim();

                const matchesQuery = !query || name.includes(query) || no.includes(query);
                const matchesType = !typeFilter || type === typeFilter;
                const matchesWard = !wardFilter || ward === wardFilter;

                let matchesDate = true;
                if (fromDate && date && date !== '—') {
                    matchesDate = matchesDate && (date >= fromDate);
                } else if (fromDate && (!date || date === '—')) {
                    matchesDate = false;
                }

                if (toDate && date && date !== '—') {
                    matchesDate = matchesDate && (date <= toDate);
                } else if (toDate && (!date || date === '—')) {
                    matchesDate = false;
                }

                if (matchesQuery && matchesType && matchesWard && matchesDate) {
                    row.show();
                    visibleCount++;
                    row.find('.row-serial').text(bn_convert(visibleCount));
                } else {
                    row.hide();
                }
            });

            $('#filtered_count').text(bn_convert(visibleCount));

            if (visibleCount === 0) {
                $('#no_results_row').removeClass('hidden');
            } else {
                $('#no_results_row').addClass('hidden');
            }
        }

        // Initialize ViretaDev Custom jQuery UI Datepicker (styled by custom-datepicker.css)
        if (typeof $.fn.datepicker !== 'undefined') {
            $('#filter_from_date, #filter_to_date').datepicker({
                dateFormat: 'yy-mm-dd',
                changeMonth: true,
                changeYear: true,
                yearRange: "2020:+5",
                showAnim: "fadeIn",
                onSelect: function(dateText) {
                    $('.date-preset-btn').removeClass('active bg-indigo-600 text-white shadow-2xs').addClass('bg-neutral-100 hover:bg-neutral-200 text-neutral-700');
                    activePreset = null;
                    applyFiltersAndSort();
                }
            });

            $('#filter_from_date, #filter_to_date').on('click focus', function() {
                $(this).datepicker('show');
            });
        }

        // Preset pill clicks
        $('.date-preset-btn').on('click', function() {
            $('.date-preset-btn').removeClass('active bg-indigo-600 text-white shadow-2xs').addClass('bg-neutral-100 hover:bg-neutral-200 text-neutral-700');
            $(this).addClass('active bg-indigo-600 text-white shadow-2xs').removeClass('bg-neutral-100 hover:bg-neutral-200 text-neutral-700');

            activePreset = $(this).data('range');
            if (typeof $.fn.datepicker !== 'undefined') {
                $('#filter_from_date, #filter_to_date').datepicker('setDate', null);
            }
            $('#filter_from_date').val('');
            $('#filter_to_date').val('');

            applyFiltersAndSort();
        });

        // Click on today card triggers today filter
        $('#card_today_filter').on('click', function() {
            $('.date-preset-btn[data-range="today"]').trigger('click');
        });

        // Custom date inputs typing/pasting
        $('#filter_from_date, #filter_to_date').on('input change', function() {
            $('.date-preset-btn').removeClass('active bg-indigo-600 text-white shadow-2xs').addClass('bg-neutral-100 hover:bg-neutral-200 text-neutral-700');
            activePreset = null;
            applyFiltersAndSort();
        });

        // Clear all filters
        $('#clear_all_filters').on('click', function() {
            $('#sonod_table_search').val('');
            $('#sonod_type_filter').val('');
            $('#sonod_ward_filter').val('');
            $('#sonod_sort_filter').val('time_desc');
            if (typeof $.fn.datepicker !== 'undefined') {
                $('#filter_from_date, #filter_to_date').datepicker('setDate', null);
            }
            $('#filter_from_date').val('');
            $('#filter_to_date').val('');

            $('.date-preset-btn').removeClass('active bg-indigo-600 text-white shadow-2xs').addClass('bg-neutral-100 hover:bg-neutral-200 text-neutral-700');
            $('.date-preset-btn[data-range="all"]').addClass('active bg-indigo-600 text-white shadow-2xs').removeClass('bg-neutral-100 hover:bg-neutral-200 text-neutral-700');
            activePreset = 'all';

            applyFiltersAndSort();
        });

        // Inputs & Selects
        $('#sonod_table_search').on('input', applyFiltersAndSort);
        $('#sonod_type_filter, #sonod_ward_filter, #sonod_sort_filter').on('change', applyFiltersAndSort);
    });
</script>
