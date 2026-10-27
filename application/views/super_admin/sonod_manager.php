<?php
/**
 * Super Admin — Sonod Global Design & Template Manager View
 * =====================================================================
 * Tailwind + Phosphor icons on portal.css, loaded via Template::sadmin().
 *
 * @var array  $tenants
 * @var int    $selected_tenant_id
 * @var object $selected_tenant
 * @var array  $theme_config
 * @var array  $border_list
 * @var array  $color_palettes
 */

$flash = $this->session->flashdata('sa_flash');
$sel_border = $theme_config['border_design'] ?? 'border_1';
$sel_color  = $theme_config['primary_color'] ?? '#065f46';
$sel_mono   = $theme_config['top_left_monogram'] ?? 'bd_gov';
$is_pad     = ($theme_config['design_mode'] ?? 'digital') === 'custom_upload';
$mono_url   = base_url('inc/assets/sonod_img/' . ($sel_mono === 'nojrul' ? 'nojrul.png' : ($sel_mono === 'bangabandhu' ? 'bangabandhu.png' : 'bd_gov.png')));
$border_url = base_url('inc/assets/sonod_img/borders/' . $sel_border . '.png');
$gov_logo   = base_url('inc/assets/sonod_img/bd_gov.png');
$up_logo    = !empty($selected_tenant->ins_logo_path) ? media_url($selected_tenant->ins_logo_path) : $gov_logo;
?>

<div class="space-y-6">

    <!-- Top Alert / Notification Banner -->
    <?php if (!empty($flash)): 
        $is_ok = ($flash['type'] === 'success');
        $banner_cls = $is_ok ? 'bg-emerald-50 text-emerald-900 border-emerald-200' : 'bg-rose-50 text-rose-900 border-rose-200';
    ?>
        <div class="p-4 rounded-2xl flex items-center gap-3 border <?= $banner_cls ?>">
            <i class="ph <?= $is_ok ? 'ph-check-circle text-emerald-600' : 'ph-warning-circle text-rose-600' ?> text-xl shrink-0"></i>
            <span class="text-xs sm:text-sm font-semibold"><?= htmlspecialchars($flash['msg'], ENT_QUOTES, 'UTF-8') ?></span>
        </div>
    <?php endif; ?>

    <!-- Header Section with Tenant Switcher & Global Sync -->
    <div class="bg-white rounded-2xl p-5 sm:p-6 border border-neutral-200/80 shadow-xs space-y-4">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-50 border border-indigo-100 text-indigo-700 text-xs font-bold mb-2">
                    <i class="ph ph-certificate text-base"></i>
                    <span>সেন্ট্রাল সনদ আর্কিটেকচার ও ডিজাইন কন্ট্রোল</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-black text-neutral-900 tracking-tight">
                    সনদ ও টেমপ্লেট গ্লোবাল ম্যানেজার
                </h1>
                <p class="text-xs sm:text-sm text-neutral-500 mt-1 max-w-2xl leading-relaxed">
                    সকল ইউনিয়ন পরিষদের নাগরিকত্ব, ওয়ারিশান, ট্রেড লাইসেন্স ও অন্যান্য সনদের ৫৫টি অলঙ্কৃত বর্ডার ফ্রেম, মনোগ্রাম, কালার স্কিম ও প্রিন্ট মার্জিন এখান থেকে সেন্ট্রালি নিয়ন্ত্রণ করুন।
                </p>
            </div>

            <!-- Tenant Selector Dropdown -->
            <div class="flex flex-wrap items-center gap-3">
                <div class="bg-neutral-50 border border-neutral-200 p-1.5 rounded-xl flex items-center gap-2">
                    <span class="text-xs font-bold text-neutral-600 pl-2">ইউনিয়ন:</span>
                    <select id="tenantSelect"
                            onchange="window.location.href='<?= base_url('sadmin_sonod?tenant_id=') ?>' + this.value"
                            class="text-xs font-bold bg-white text-neutral-900 rounded-lg px-3 py-1.5 border border-neutral-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer">
                        <?php foreach ($tenants as $t): ?>
                            <option value="<?= (int)$t->ins_titute_p_idds ?>" <?= ((int)$t->ins_titute_p_idds === (int)$selected_tenant_id) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($t->ins_name ?: 'ID: ' . $t->ins_titute_p_idds, ENT_QUOTES, 'UTF-8') ?> (ID: <?= (int)$t->ins_titute_p_idds ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="button" onclick="document.getElementById('sonodSettingsForm').submit()"
                        class="ht-btn ht-btn-primary px-4 py-2 text-xs font-bold flex items-center gap-2 shadow-xs hover:shadow-md cursor-pointer">
                    <i class="ph ph-floppy-disk text-base"></i>
                    <span>ডিজাইন সংরক্ষণ করুন</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Primary Mode Tabs: Design & Frames VS Text & Body Templates -->
    <div class="flex flex-wrap items-center justify-between gap-3 bg-white p-2.5 rounded-2xl border border-neutral-200/80 shadow-xs">
        <div class="flex items-center gap-2">
            <button type="button" onclick="switchPrimaryMode('design')" id="btn-mode-design"
                    class="primary-mode-btn px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold flex items-center gap-2 transition-all bg-indigo-600 text-white shadow-xs cursor-pointer">
                <i class="ph ph-paint-brush-broad text-base"></i>
                <span>সনদ ফ্রেম ও ডিজাইন কন্ট্রোল</span>
            </button>
            <button type="button" onclick="switchPrimaryMode('texts')" id="btn-mode-texts"
                    class="primary-mode-btn px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold flex items-center gap-2 transition-all text-neutral-600 hover:bg-neutral-100 cursor-pointer">
                <i class="ph ph-file-text text-base"></i>
                <span>সনদ টেক্সট ও বয়ান টেমপ্লেট</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-indigo-100 text-indigo-700">গ্লোবাল ও কাস্টম</span>
            </button>
        </div>
        <div class="text-xs text-neutral-500 font-medium px-2">
            নির্বাচিত পরিষদ: <strong class="text-neutral-900"><?= htmlspecialchars($selected_tenant->ins_name ?? 'ইউনিয়ন', ENT_QUOTES, 'UTF-8') ?></strong>
        </div>
    </div>

    <!-- Panel 1: Design Controls & Live A4 Preview -->
    <div id="panel-mode-design">
        <form id="sonodSettingsForm" method="post" action="<?= base_url('sadmin_sonod/save') ?>" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <input type="hidden" name="tenant_id" value="<?= (int)$selected_tenant_id ?>">

        <!-- Left Controls Area (7 Cols) -->
        <div class="lg:col-span-7 space-y-6">

            <!-- Global Apply Checkbox Banner -->
            <div class="bg-amber-50 border border-amber-200/80 rounded-2xl p-4 flex items-start gap-3">
                <div class="pt-0.5">
                    <input type="checkbox" id="apply_to_all" name="apply_to_all" value="1"
                           class="w-4 h-4 text-amber-600 rounded border-amber-300 focus:ring-amber-500 cursor-pointer">
                </div>
                <label for="apply_to_all" class="text-xs text-amber-900 cursor-pointer select-none">
                    <span class="font-bold block text-sm">এক ক্লিকে সকল ইউনিয়নে এই ডিজাইন প্রয়োগ করুন (Global Sync)</span>
                    <span class="text-amber-700 leading-relaxed block mt-0.5">
                        চেকমার্ক দিলে সংরক্ষিত বর্ডার, মনোগ্রাম ও কালার স্কিমটি প্ল্যাটফর্মের সকল বিদ্যমান ও ভবিষ্যৎ ইউনিয়ন পরিষদে একযোগে কার্যকর হবে।
                    </span>
                </label>
            </div>

            <!-- Tab Navigation Header -->
            <div class="bg-white rounded-2xl border border-neutral-200/80 p-2 shadow-xs">
                <div class="flex flex-wrap gap-1 border-b border-neutral-100 pb-2 mb-3">
                    <button type="button" onclick="switchSonodTab('tab-borders')" id="btn-tab-borders"
                            class="sonod-tab-btn px-3 py-2 rounded-xl text-xs font-bold transition-all bg-indigo-600 text-white shadow-xs">
                        <i class="ph ph-frame-corners text-sm"></i> বর্ডার ফ্রেম (৫৫টি)
                    </button>
                    <button type="button" onclick="switchSonodTab('tab-colors')" id="btn-tab-colors"
                            class="sonod-tab-btn px-3 py-2 rounded-xl text-xs font-bold transition-all text-neutral-600 hover:bg-neutral-100">
                        <i class="ph ph-palette text-sm"></i> কালার ও ফন্ট
                    </button>
                    <button type="button" onclick="switchSonodTab('tab-monogram')" id="btn-tab-monogram"
                            class="sonod-tab-btn px-3 py-2 rounded-xl text-xs font-bold transition-all text-neutral-600 hover:bg-neutral-100">
                        <i class="ph ph-shield-check text-sm"></i> মনোগ্রাম ও ওয়াটারমার্ক
                    </button>
                    <button type="button" onclick="switchSonodTab('tab-layout')" id="btn-tab-layout"
                            class="sonod-tab-btn px-3 py-2 rounded-xl text-xs font-bold transition-all text-neutral-600 hover:bg-neutral-100">
                        <i class="ph ph-signature text-sm"></i> স্বাক্ষর ও লেআউট
                    </button>
                    <button type="button" onclick="switchSonodTab('tab-pad')" id="btn-tab-pad"
                            class="sonod-tab-btn px-3 py-2 rounded-xl text-xs font-bold transition-all text-neutral-600 hover:bg-neutral-100">
                        <i class="ph ph-printer text-sm"></i> প্যাড প্রিন্ট মোড
                    </button>
                </div>

                <!-- Tab 1: Borders -->
                <div id="tab-borders" class="sonod-tab-content space-y-4 p-2">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-neutral-900">সনদের বর্ডার ফ্রেম নির্বাচন করুন</h3>
                            <p class="text-[11px] text-neutral-500">সরকারি সনদপত্রের জন্য তৈরি ৫৫টি আল্ট্রা-হাই রেজুলেশন বর্ডার ফ্রেম।</p>
                        </div>
                        <div class="flex gap-1 bg-neutral-100 p-1 rounded-lg text-[11px] font-semibold text-neutral-600">
                            <button type="button" onclick="filterBorders('all', this)" class="border-filter-btn px-2 py-0.5 rounded bg-white shadow-2xs text-indigo-700">সকল</button>
                            <button type="button" onclick="filterBorders('royal', this)" class="border-filter-btn px-2 py-0.5 rounded hover:text-neutral-900">রাজকীয়</button>
                            <button type="button" onclick="filterBorders('classic', this)" class="border-filter-btn px-2 py-0.5 rounded hover:text-neutral-900">ক্লাসিক</button>
                            <button type="button" onclick="filterBorders('slim', this)" class="border-filter-btn px-2 py-0.5 rounded hover:text-neutral-900">স্লিম</button>
                            <button type="button" onclick="filterBorders('pattern', this)" class="border-filter-btn px-2 py-0.5 rounded hover:text-neutral-900">প্যাটার্ন</button>
                        </div>
                    </div>

                    <!-- Scrollable Border Grid (Max Height) -->
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 overflow-y-auto pr-1" style="max-height: 460px;" id="borderCardsGrid">
                        <?php foreach ($border_list as $b_key => $b_data): 
                            $is_active = ($sel_border === $b_key);
                        ?>
                            <label class="border-card cursor-pointer block rounded-xl border-2 transition-all p-2 relative bg-white <?= $is_active ? 'border-indigo-600 ring-2 ring-indigo-200 bg-indigo-50/20' : 'border-neutral-200 hover:border-neutral-300' ?>" data-category="<?= $b_data['cat'] ?>">
                                <input type="radio" name="border_design" value="<?= $b_key ?>" class="sr-only" <?= $is_active ? 'checked' : '' ?> onchange="onBorderChange('<?= $b_key ?>')">
                                <div class="w-full h-24 rounded-lg bg-neutral-50 border border-neutral-100 overflow-hidden relative flex items-center justify-center p-1">
                                    <img src="<?= base_url('inc/assets/sonod_img/borders/' . $b_key . '.png') ?>" alt="<?= $b_data['title'] ?>" class="w-full h-full object-contain">
                                </div>
                                <div class="mt-2 text-left">
                                    <span class="text-xs font-bold text-neutral-900 block truncate"><?= $b_data['title'] ?></span>
                                    <span class="text-[10px] text-neutral-500 block truncate"><?= $b_data['desc'] ?></span>
                                </div>
                                <?php if ($is_active): ?>
                                    <span class="border-check-badge absolute top-1.5 right-1.5 w-5 h-5 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs shadow-xs">
                                        <i class="ph ph-check"></i>
                                    </span>
                                <?php endif; ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Tab 2: Colors & Typography -->
                <div id="tab-colors" class="sonod-tab-content space-y-5 p-2 hidden">
                    <div>
                        <h3 class="text-sm font-bold text-neutral-900">অফিসিয়াল কালার প্যালেট</h3>
                        <p class="text-[11px] text-neutral-500">সনদের হেডার, বর্ডার ও অ্যাকসেন্ট কালার নির্ধারণ করুন।</p>
                    </div>

                    <!-- Preset Palettes -->
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <?php foreach ($color_palettes as $p_key => $p_data): 
                            $is_color_active = ($theme_config['theme_color'] ?? 'emerald') === $p_key;
                        ?>
                            <button type="button" onclick="setPalette('<?= $p_key ?>', '<?= $p_data['primary'] ?>', '<?= $p_data['accent'] ?>')"
                                    class="palette-btn p-3 rounded-xl border-2 text-left transition-all <?= $is_color_active ? 'border-indigo-600 ring-2 ring-indigo-200 bg-indigo-50/20' : 'border-neutral-200 hover:border-neutral-300' ?>">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="w-6 h-6 rounded-full shadow-xs" style="background-color: <?= $p_data['primary'] ?>;"></span>
                                    <span class="w-4 h-4 rounded-full shadow-2xs" style="background-color: <?= $p_data['accent'] ?>;"></span>
                                </div>
                                <span class="text-xs font-bold text-neutral-900 block truncate"><?= $p_data['name'] ?></span>
                                <span class="text-[10px] text-neutral-500 font-mono"><?= $p_data['primary'] ?></span>
                            </button>
                        <?php endforeach; ?>
                    </div>

                    <input type="hidden" id="theme_color_input" name="theme_color" value="<?= htmlspecialchars($theme_config['theme_color'] ?? 'emerald', ENT_QUOTES, 'UTF-8') ?>">

                    <!-- Custom Color Pickers -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-3 border-t border-neutral-100">
                        <div>
                            <label class="text-xs font-bold text-neutral-700 block mb-1.5">প্রাইমারি থিম কালার (Hex)</label>
                            <div class="flex items-center gap-2">
                                <input type="color" id="primaryColorPicker" value="<?= $theme_config['primary_color'] ?? '#065f46' ?>"
                                       oninput="updateCustomColor(this.value, 'primary')" class="w-10 h-10 p-0 rounded-lg border border-neutral-300 cursor-pointer">
                                <input type="text" id="primaryColorText" name="primary_color" value="<?= $theme_config['primary_color'] ?? '#065f46' ?>"
                                       oninput="updateCustomColor(this.value, 'primary')" class="ht-field text-xs font-mono font-bold flex-1 uppercase">
                            </div>
                        </div>

                        <div>
                            <label class="text-xs font-bold text-neutral-700 block mb-1.5">বর্ডার অ্যাকসেন্ট কালার (Hex)</label>
                            <div class="flex items-center gap-2">
                                <input type="color" id="borderColorPicker" value="<?= $theme_config['border_color'] ?? '#065f46' ?>"
                                       oninput="updateCustomColor(this.value, 'border')" class="w-10 h-10 p-0 rounded-lg border border-neutral-300 cursor-pointer">
                                <input type="text" id="borderColorText" name="border_color" value="<?= $theme_config['border_color'] ?? '#065f46' ?>"
                                       oninput="updateCustomColor(this.value, 'border')" class="ht-field text-xs font-mono font-bold flex-1 uppercase">
                            </div>
                        </div>
                    </div>

                    <!-- Font Family Selection -->
                    <div class="pt-3 border-t border-neutral-100">
                        <label class="text-xs font-bold text-neutral-700 block mb-1.5">সনদের অফিসিয়াল ফন্ট ফ্যামিলি</label>
                        <select name="font_family" id="fontFamilySelect" onchange="updatePreviewFont(this.value)" class="ht-field text-xs font-semibold">
                            <option value="tiro_bangla" <?= ($theme_config['font_family'] ?? '') === 'tiro_bangla' ? 'selected' : '' ?>>টিরো বাংলা (Tiro Bangla - অত্যন্ত মার্জিত ও প্রফেশনাল)</option>
                            <option value="solaimanlipi" <?= ($theme_config['font_family'] ?? '') === 'solaimanlipi' ? 'selected' : '' ?>>সোলাইমান লিপি (SolaimanLipi - প্রথাগত অফিশিয়াল লুক)</option>
                            <option value="kalpurush" <?= ($theme_config['font_family'] ?? '') === 'kalpurush' ? 'selected' : '' ?>>কালপুরুষ (Kalpurush - পরিষ্কার ও ক্লাসিক)</option>
                            <option value="hind_siliguri" <?= ($theme_config['font_family'] ?? '') === 'hind_siliguri' ? 'selected' : '' ?>>হিন্দ শিলিগুড়ি (Hind Siliguri - মডার্ন ডিজিটাল লুক)</option>
                        </select>
                    </div>
                </div>

                <!-- Tab 3: Monogram & Watermark -->
                <div id="tab-monogram" class="sonod-tab-content space-y-5 p-2 hidden">
                    <div>
                        <h3 class="text-sm font-bold text-neutral-900">টপ-লেফট মনোগ্রাম নির্বাচন</h3>
                        <p class="text-[11px] text-neutral-500">সনদের ওপরের বামপাশে জাতীয় বা বিশেষ স্মরণিকা মনোগ্রাম।</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <label class="monogram-card cursor-pointer p-3 rounded-xl border-2 transition-all flex items-center gap-3 <?= $sel_mono === 'bd_gov' ? 'border-indigo-600 bg-indigo-50/20' : 'border-neutral-200' ?>">
                            <input type="radio" name="top_left_monogram" value="bd_gov" class="sr-only" <?= $sel_mono === 'bd_gov' ? 'checked' : '' ?> onchange="onMonogramChange('bd_gov')">
                            <img src="<?= base_url('inc/assets/sonod_img/bd_gov.png') ?>" alt="BD Gov" class="w-10 h-10 object-contain">
                            <div>
                                <span class="text-xs font-bold text-neutral-900 block">সরকারি সিল</span>
                                <span class="text-[10px] text-neutral-500">গণপ্রজাতন্ত্রী বাংলাদেশ</span>
                            </div>
                        </label>

                        <label class="monogram-card cursor-pointer p-3 rounded-xl border-2 transition-all flex items-center gap-3 <?= $sel_mono === 'nojrul' ? 'border-indigo-600 bg-indigo-50/20' : 'border-neutral-200' ?>">
                            <input type="radio" name="top_left_monogram" value="nojrul" class="sr-only" <?= $sel_mono === 'nojrul' ? 'checked' : '' ?> onchange="onMonogramChange('nojrul')">
                            <img src="<?= base_url('inc/assets/sonod_img/nojrul.png') ?>" alt="Nojrul" class="w-10 h-10 object-contain">
                            <div>
                                <span class="text-xs font-bold text-neutral-900 block">নজরুল মনোগ্রাম</span>
                                <span class="text-[10px] text-neutral-500">জাতীয় কবি স্মরণিকা</span>
                            </div>
                        </label>

                        <label class="monogram-card cursor-pointer p-3 rounded-xl border-2 transition-all flex items-center gap-3 <?= $sel_mono === 'bangabandhu' ? 'border-indigo-600 bg-indigo-50/20' : 'border-neutral-200' ?>">
                            <input type="radio" name="top_left_monogram" value="bangabandhu" class="sr-only" <?= $sel_mono === 'bangabandhu' ? 'checked' : '' ?> onchange="onMonogramChange('bangabandhu')">
                            <img src="<?= base_url('inc/assets/sonod_img/bangabandhu.png') ?>" alt="Bangabandhu" class="w-10 h-10 object-contain">
                            <div>
                                <span class="text-xs font-bold text-neutral-900 block">মুজিব শতবর্ষ</span>
                                <span class="text-[10px] text-neutral-500">বঙ্গবন্ধু লোগো</span>
                            </div>
                        </label>
                    </div>

                    <!-- Watermark Settings -->
                    <div class="pt-4 border-t border-neutral-100 space-y-4">
                        <h4 class="text-xs font-bold text-neutral-900 uppercase tracking-wider">ব্যাকগ্রাউন্ড ওয়াটারমার্ক কনফিগারেশন</h4>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-bold text-neutral-700 block mb-1">ওয়াটারমার্ক টাইপ</label>
                                <select name="watermark_type" id="watermarkTypeSelect" onchange="updateWatermarkType(this.value)" class="ht-field text-xs">
                                    <option value="bd_gov" <?= ($theme_config['watermark_type'] ?? '') === 'bd_gov' ? 'selected' : '' ?>>বাংলাদেশ সরকার মনোগ্রাম</option>
                                    <option value="up_seal" <?= ($theme_config['watermark_type'] ?? '') === 'up_seal' ? 'selected' : '' ?>>ইউনিয়ন পরিষদ নিজস্ব সিল</option>
                                </select>
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label class="text-xs font-bold text-neutral-700">অপাসিটি (হালকা/গাঢ়)</label>
                                    <span id="opacityValLabel" class="text-xs font-mono font-bold text-indigo-700"><?= $theme_config['watermark_opacity'] ?? 0.07 ?></span>
                                </div>
                                <input type="range" name="watermark_opacity" min="0.02" max="0.25" step="0.01" value="<?= $theme_config['watermark_opacity'] ?? 0.07 ?>"
                                       oninput="updateWatermarkOpacity(this.value)" class="w-full accent-indigo-600 cursor-pointer">
                            </div>
                        </div>

                        <div class="flex items-center gap-6 pt-2">
                            <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-neutral-700">
                                <input type="checkbox" name="watermark_visible" value="1" <?= !empty($theme_config['watermark_visible']) ? 'checked' : '' ?>
                                       onchange="toggleWatermark(this.checked)" class="w-4 h-4 text-indigo-600 rounded">
                                <span>ওয়াটারমার্ক প্রদর্শন করুন</span>
                            </label>

                            <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-neutral-700">
                                <input type="checkbox" name="show_qr_code" value="1" <?= !empty($theme_config['show_qr_code']) ? 'checked' : '' ?>
                                       class="w-4 h-4 text-indigo-600 rounded">
                                <span>কিউআর কোড ভেরিফিকেশন সক্রিয় রাখুন</span>
                            </label>

                            <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-neutral-700">
                                <input type="checkbox" name="show_barcode" value="1" <?= !empty($theme_config['show_barcode']) ? 'checked' : '' ?>
                                       class="w-4 h-4 text-indigo-600 rounded">
                                <span>বারকোড প্রদর্শন করুন</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Tab 4: Signatures & Layout -->
                <div id="tab-layout" class="sonod-tab-content space-y-5 p-2 hidden">
                    <div>
                        <h3 class="text-sm font-bold text-neutral-900">স্বাক্ষরকারী বিন্যাস ও লেআউট</h3>
                        <p class="text-[11px] text-neutral-500">সনদের নিচের অংশের ৩-কলাম অথবা ২-কলাম সিগনেচার ব্লক কনফিগার করুন।</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <label class="p-3 rounded-xl border-2 cursor-pointer transition-all <?= ($theme_config['signatory_count'] ?? 'auto') === 'auto' ? 'border-indigo-600 bg-indigo-50/20' : 'border-neutral-200' ?>">
                            <input type="radio" name="signatory_count" value="auto" <?= ($theme_config['signatory_count'] ?? 'auto') === 'auto' ? 'checked' : '' ?> class="sr-only">
                            <span class="text-xs font-bold text-neutral-900 block">স্মার্ট অটো মোড</span>
                            <span class="text-[10px] text-neutral-500">সনদ অনুযায়ী স্বয়ংক্রিয় ২ বা ৩ জন</span>
                        </label>

                        <label class="p-3 rounded-xl border-2 cursor-pointer transition-all <?= ($theme_config['signatory_count'] ?? '') === '3_person' ? 'border-indigo-600 bg-indigo-50/20' : 'border-neutral-200' ?>">
                            <input type="radio" name="signatory_count" value="3_person" <?= ($theme_config['signatory_count'] ?? '') === '3_person' ? 'checked' : '' ?> class="sr-only">
                            <span class="text-xs font-bold text-neutral-900 block">৩-স্বাক্ষরকারী ব্লক</span>
                            <span class="text-[10px] text-neutral-500">মেম্বার + সচিব + চেয়ারম্যান</span>
                        </label>

                        <label class="p-3 rounded-xl border-2 cursor-pointer transition-all <?= ($theme_config['signatory_count'] ?? '') === '2_person' ? 'border-indigo-600 bg-indigo-50/20' : 'border-neutral-200' ?>">
                            <input type="radio" name="signatory_count" value="2_person" <?= ($theme_config['signatory_count'] ?? '') === '2_person' ? 'checked' : '' ?> class="sr-only">
                            <span class="text-xs font-bold text-neutral-900 block">২-স্বাক্ষরকারী ব্লক</span>
                            <span class="text-[10px] text-neutral-500">ইউপি সচিব + চেয়ারম্যান</span>
                        </label>
                    </div>

                    <div class="pt-4 border-t border-neutral-100 space-y-3">
                        <h4 class="text-xs font-bold text-neutral-900 uppercase tracking-wider">পৃথক স্বাক্ষর নিয়ন্ত্রণ</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <label class="flex items-center gap-2 p-2.5 rounded-lg border border-neutral-200 text-xs font-semibold text-neutral-800">
                                <input type="checkbox" name="show_member_sign" value="1" <?= !empty($theme_config['show_member_sign']) ? 'checked' : '' ?> class="w-4 h-4 text-indigo-600 rounded">
                                <span>সংশ্লিষ্ট ওয়ার্ড মেম্বার</span>
                            </label>
                            <label class="flex items-center gap-2 p-2.5 rounded-lg border border-neutral-200 text-xs font-semibold text-neutral-800">
                                <input type="checkbox" name="show_sec_sign" value="1" <?= !empty($theme_config['show_sec_sign']) ? 'checked' : '' ?> class="w-4 h-4 text-indigo-600 rounded">
                                <span>ইউপি সচিব</span>
                            </label>
                            <label class="flex items-center gap-2 p-2.5 rounded-lg border border-neutral-200 text-xs font-semibold text-neutral-800">
                                <input type="checkbox" name="show_chairman_sign" value="1" <?= !empty($theme_config['show_chairman_sign']) ? 'checked' : '' ?> class="w-4 h-4 text-indigo-600 rounded">
                                <span>ইউপি চেয়ারম্যান</span>
                            </label>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-neutral-100">
                        <label class="text-xs font-bold text-neutral-700 block mb-1.5">সনদের ফুটার নোট</label>
                        <input type="text" name="footer_text" value="<?= htmlspecialchars($theme_config['footer_text'] ?? 'ডিজিটাল ইউনিয়ন পরিষদ সেবা', ENT_QUOTES, 'UTF-8') ?>" class="ht-field text-xs font-medium">
                    </div>
                </div>

                <!-- Tab 5: Pre-printed Pad Mode -->
                <div id="tab-pad" class="sonod-tab-content space-y-5 p-2 hidden">
                    <div>
                        <h3 class="text-sm font-bold text-neutral-900">ইউনিয়নের ছাপা প্যাড (Pre-printed Pad) প্রিন্ট মোড</h3>
                        <p class="text-[11px] text-neutral-500">ইউনিয়ন পরিষদের নিজস্ব লেটারহেড কাগজে ছাপানোর জন্য মার্জিন ও সেটিংস।</p>
                    </div>

                    <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200 space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-xs font-bold text-neutral-900 block">প্যাড ক্লিয়ারেন্স টপ মার্জিন (Top Clearance Margin)</span>
                                <span class="text-[11px] text-neutral-500">ইউপি প্যাডের অফিশিয়াল মনোগ্রামের ওপর লেখা না পড়ার জন্য টপ স্পেসার।</span>
                            </div>
                            <span id="padMarginLabel" class="text-sm font-mono font-bold text-indigo-700"><?= (int)($theme_config['pad_top_margin_mm'] ?? 42) ?> mm</span>
                        </div>
                        <input type="range" name="pad_top_margin_mm" min="25" max="65" step="1" value="<?= (int)($theme_config['pad_top_margin_mm'] ?? 42) ?>"
                               oninput="document.getElementById('padMarginLabel').textContent = this.value + ' mm'"
                               class="w-full accent-indigo-600 cursor-pointer">
                    </div>

                    <!-- Custom Pad Upload -->
                    <div class="pt-4 border-t border-neutral-100 space-y-3">
                        <h4 class="text-xs font-bold text-neutral-900">কাস্টম প্যাড গ্রাফিক্স আপলোড (ঐচ্ছিক)</h4>
                        <p class="text-[11px] text-neutral-500">ডিজিটাল বর্ডারের পরিবর্তে ইউনিয়নের নিজস্ব হাই-রেজুলেশন স্ক্যান করা প্যাড ব্যাকগ্রাউন্ড হিসেবে ব্যবহার করতে পারেন।</p>
                        
                        <div class="flex items-center gap-4">
                            <input type="file" name="custom_pad_file" accept="image/png,image/jpeg,image/webp" class="text-xs text-neutral-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            
                            <?php if (!empty($theme_config['custom_pad_path'])): ?>
                                <label class="flex items-center gap-1.5 text-xs text-rose-600 cursor-pointer">
                                    <input type="checkbox" name="remove_custom_pad" value="1" class="rounded text-rose-600">
                                    <span>বিদ্যমান কাস্টম প্যাড মুছে ফেলুন</span>
                                </label>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Action Submit Bar -->
            <div class="flex items-center justify-between pt-2">
                <span class="text-xs text-neutral-500 font-medium">
                    নির্বাচিত ইউনিয়ন: <strong class="text-neutral-900"><?= htmlspecialchars($selected_tenant->ins_name ?? 'Union Parishad', ENT_QUOTES, 'UTF-8') ?></strong>
                </span>
                <button type="submit" class="ht-btn ht-btn-primary px-5 py-2.5 text-sm font-bold flex items-center gap-2 shadow-xs hover:shadow-md cursor-pointer">
                    <i class="ph ph-check-circle text-lg"></i>
                    <span>কনফিগারেশন সেভ করুন</span>
                </button>
            </div>

        </div>

        <!-- Right Side: Live Interactive Miniature Preview (5 Cols, Sticky) -->
        <div class="lg:col-span-5 sticky top-20">
            <div class="bg-neutral-900 text-white rounded-2xl p-4 shadow-xl border border-neutral-800 space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-neutral-800">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="text-xs font-bold tracking-wider uppercase text-neutral-300">লাইভ A4 প্রিভিউ (Live Preview)</span>
                    </div>
                    <span id="previewBorderName" class="text-[11px] font-mono text-indigo-400 font-bold"><?= $sel_border ?></span>
                </div>

                <!-- Scaled Miniature A4 Sheet Simulator (210mm x 297mm proportion ~ 1:1.414) -->
                <div class="w-full bg-neutral-800/80 rounded-xl p-3 flex items-center justify-center overflow-hidden">
                    <div id="miniCertCanvas"
                         class="w-full aspect-[1/1.414] bg-white text-neutral-900 rounded-sm shadow-2xl relative p-3 flex flex-col justify-between overflow-hidden select-none transition-all duration-200"
                         style="max-width: 340px; font-family: 'Hind Siliguri', sans-serif;">
                        
                        <!-- Background Border Frame -->
                        <div id="prevBorderFrame"
                             class="absolute inset-0 pointer-events-none z-10 transition-all duration-200"
                             style="background-image: url('<?= $border_url ?>'); background-size: 100% 100%; background-repeat: no-repeat;">
                        </div>

                        <!-- Center Watermark -->
                        <div id="prevWatermark"
                             class="absolute inset-0 flex items-center justify-center pointer-events-none z-2"
                             style="opacity: <?= $theme_config['watermark_opacity'] ?? 0.07 ?>;">
                            <img id="prevWatermarkImg" src="<?= $gov_logo ?>" alt="Watermark" class="w-32 h-32 object-contain filter grayscale">
                        </div>

                        <!-- Certificate Header -->
                        <div class="relative z-15 pt-1.5 px-1.5 flex items-start justify-between border-b border-neutral-200/60 pb-1.5">
                            <img id="prevMonogramImg" src="<?= $mono_url ?>" alt="Monogram" class="w-7 h-7 object-contain">
                            <div class="text-center flex-1 px-1">
                                <span class="text-[8px] font-bold text-neutral-500 uppercase tracking-widest block leading-none">গণপ্রজাতন্ত্রী বাংলাদেশ সরকার</span>
                                <h4 id="prevUpName" class="text-[11px] font-black tracking-tight leading-tight mt-0.5" style="color: <?= $sel_color ?>;">
                                    <?= htmlspecialchars($selected_tenant->ins_name ?? 'ধুম ইউনিয়ন পরিষদ', ENT_QUOTES, 'UTF-8') ?>
                                </h4>
                                <span class="text-[7.5px] text-neutral-500 block leading-none">উপজেলা ও জেলা কার্যালয়</span>
                            </div>
                            <img src="<?= $up_logo ?>" alt="UP Logo" class="w-7 h-7 object-contain">
                        </div>

                        <!-- Certificate Body Sample -->
                        <div class="relative z-15 px-2 py-1 text-center space-y-1.5">
                            <div class="inline-block px-3 py-0.5 rounded-full text-[9px] font-black text-white shadow-2xs"
                                 id="prevTitleBadge" style="background-color: <?= $sel_color ?>;">
                                নাগরিকত্ব সনদপত্র
                            </div>

                            <p class="text-[7.5px] text-neutral-600 leading-relaxed text-justify px-1">
                                এই মর্মে প্রত্যয়ন করা যাইতেছে যে, <strong class="text-neutral-900">মোছাঃ ফাতেমা বেগম</strong>, পিতা: মোঃ রফিকুল ইসলাম, এই ইউনিয়নের একজন স্থায়ী ও জন্মসূত্রে বাংলাদেশের নাগরিক। তাহার নৈতিক চরিত্র উত্তম এবং তিনি রাষ্ট্রবিরোধী কোনো কার্যকলাপে জড়িত নন।
                            </p>

                            <!-- Digit Box Simulation -->
                            <div class="inline-flex items-center gap-0.5 justify-center pt-0.5">
                                <span class="text-[7px] font-bold text-neutral-500 mr-1">সনদ নং:</span>
                                <span class="w-3.5 h-3.5 border border-neutral-300 text-[8px] font-bold flex items-center justify-center bg-neutral-50">২</span>
                                <span class="w-3.5 h-3.5 border border-neutral-300 text-[8px] font-bold flex items-center justify-center bg-neutral-50">০</span>
                                <span class="w-3.5 h-3.5 border border-neutral-300 text-[8px] font-bold flex items-center justify-center bg-neutral-50">২</span>
                                <span class="w-3.5 h-3.5 border border-neutral-300 text-[8px] font-bold flex items-center justify-center bg-neutral-50">৬</span>
                                <span class="w-3.5 h-3.5 border border-neutral-300 text-[8px] font-bold flex items-center justify-center bg-neutral-50">০</span>
                                <span class="w-3.5 h-3.5 border border-neutral-300 text-[8px] font-bold flex items-center justify-center bg-neutral-50">১</span>
                            </div>
                        </div>

                        <!-- Signatures & Verification Strip -->
                        <div class="relative z-15 px-1.5 pb-1">
                            <div class="grid grid-cols-3 gap-1 text-center border-t border-neutral-200/60 pt-1.5 mb-1.5 text-[7px] text-neutral-600">
                                <div>
                                    <div class="h-3 border-b border-dashed border-neutral-300 mb-0.5"></div>
                                    <span class="font-bold">ওয়ার্ড মেম্বার</span>
                                </div>
                                <div>
                                    <div class="h-3 border-b border-dashed border-neutral-300 mb-0.5"></div>
                                    <span class="font-bold">ইউপি সচিব</span>
                                </div>
                                <div>
                                    <div class="h-3 border-b border-dashed border-neutral-300 mb-0.5"></div>
                                    <span class="font-bold">চেয়ারম্যান</span>
                                </div>
                            </div>

                            <div class="flex items-center justify-between text-[6.5px] text-neutral-400 pt-0.5">
                                <span>ভেরিফিকেশন: viretadev.com</span>
                                <span id="prevFooterNote"><?= htmlspecialchars($theme_config['footer_text'] ?? 'ডিজিটাল ইউনিয়ন পরিষদ সেবা', ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="text-[11px] text-neutral-400 text-center leading-relaxed">
                    বর্ডার বা কালার পরিবর্তন করার সাথে সাথে উপরের লাইভ প্রিভিউতে পরিবর্তন লক্ষ্য করুন।
                </div>
            </div>
        </div>

        </form>
    </div> <!-- /#panel-mode-design -->

    <!-- Panel 2: Certificate Texts & Body Templates Manager (deft_cer_all & certifys_default_ins_ss) -->
    <div id="panel-mode-texts" class="hidden space-y-6">

        <!-- Informational Architecture Cards: Global Master vs Tenant Custom Isolation -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-indigo-50/80 border border-indigo-200/80 rounded-2xl p-5 space-y-2.5">
                <div class="flex items-center gap-2.5 text-indigo-900 font-bold text-sm">
                    <span class="w-8 h-8 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-base shadow-xs shrink-0">
                        <i class="ph ph-globe"></i>
                    </span>
                    <span>১. প্ল্যাটফর্ম গ্লোবাল মাস্টার টেমপ্লেট (deft_cer_all)</span>
                </div>
                <p class="text-xs text-indigo-950/80 leading-relaxed">
                    এটি প্ল্যাটফর্মের কেন্দ্রীয় সনদ ডাটাবেস। যখনই কোনো নতুন ইউনিয়ন পরিষদ আমাদের সিস্টেমে অনবোর্ড বা রেজিস্টার করবে, তারা স্বয়ংক্রিয়ভাবে এই বয়ান ও টেমপ্লেট উত্তরাধিকারসূত্রে (Inherit) ডিফল্ট হিসেবে পাবে।
                </p>
                <div class="text-[11px] font-semibold text-indigo-700 flex items-center gap-1.5 pt-1 border-t border-indigo-100">
                    <i class="ph ph-shield-check text-sm text-indigo-600"></i>
                    <span>সুপার অ্যাডমিন প্ল্যাটফর্মের কেন্দ্রীয় গ্লোবাল সনদ নিয়ন্ত্রণ করেন।</span>
                </div>
            </div>

            <div class="bg-emerald-50/80 border border-emerald-200/80 rounded-2xl p-5 space-y-2.5">
                <div class="flex items-center gap-2.5 text-emerald-900 font-bold text-sm">
                    <span class="w-8 h-8 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-base shadow-xs shrink-0">
                        <i class="ph ph-buildings"></i>
                    </span>
                    <span>২. ইউনিয়ন-স্পেসিফিক নিজস্ব কাস্টমাইজেশন (certifys_default_ins_ss)</span>
                </div>
                <p class="text-xs text-emerald-950/80 leading-relaxed">
                    প্রতিটি ইউনিয়ন পরিষদ চাইলে তাদের নিজস্ব পরিষদের জন্য সনদ টেক্সট পরিবর্তন বা কাস্টমাইজ করতে পারে। তাদের কাস্টম বয়ান সম্পূর্ণ পৃথক টেবিলে সংরক্ষিত হয় এবং আমাদের প্ল্যাটফর্মের সেন্ট্রাল গ্লোবাল মাস্টারে কখনোই কোনো প্রভাব ফেলে না!
                </p>
                <div class="text-[11px] font-semibold text-emerald-700 flex items-center gap-1.5 pt-1 border-t border-emerald-100">
                    <i class="ph ph-lock-key text-sm text-emerald-600"></i>
                    <span>১০০% নিরাপদ ডেটা আইসোলেশন ও সিঙ্গেল-ক্লিক গ্লোবাল রিস্টোরেশন সুবিধা।</span>
                </div>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="bg-white rounded-2xl border border-neutral-200/80 p-4 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
            <!-- Scope Filter Pills -->
            <div class="flex flex-wrap items-center gap-1.5">
                <button type="button" onclick="filterSonodScope('all', this)" class="sonod-scope-filter px-3 py-1.5 rounded-xl text-xs font-bold transition-all bg-indigo-600 text-white shadow-2xs cursor-pointer">
                    সকল সনদ (<?= count($global_sonods) ?>)
                </button>
                <button type="button" onclick="filterSonodScope('global', this)" class="sonod-scope-filter px-3 py-1.5 rounded-xl text-xs font-bold transition-all text-neutral-600 hover:bg-neutral-100 cursor-pointer">
                    <i class="ph ph-globe"></i> গ্লোবাল মাস্টার টেমপ্লেট
                </button>
                <button type="button" onclick="filterSonodScope('custom', this)" class="sonod-scope-filter px-3 py-1.5 rounded-xl text-xs font-bold transition-all text-neutral-600 hover:bg-neutral-100 cursor-pointer">
                    <i class="ph ph-buildings"></i> <?= htmlspecialchars($selected_tenant->ins_name ?? 'ইউনিয়ন', ENT_QUOTES, 'UTF-8') ?> কাস্টমাইজেশন (<?= count($tenant_override_map) ?>)
                </button>
            </div>

            <!-- Language & Search -->
            <div class="flex items-center gap-2">
                <div class="flex bg-neutral-100 p-1 rounded-xl text-xs font-semibold text-neutral-600">
                    <button type="button" onclick="filterSonodLang('all', this)" class="sonod-lang-filter px-2.5 py-1 rounded-lg bg-white text-indigo-700 shadow-2xs cursor-pointer">সকল</button>
                    <button type="button" onclick="filterSonodLang('bn', this)" class="sonod-lang-filter px-2.5 py-1 rounded-lg hover:text-neutral-900 cursor-pointer">বাংলা</button>
                    <button type="button" onclick="filterSonodLang('en', this)" class="sonod-lang-filter px-2.5 py-1 rounded-lg hover:text-neutral-900 cursor-pointer">English</button>
                </div>

                <div class="relative">
                    <input type="text" id="sonodSearchInput" onkeyup="searchSonodCards(this.value)" placeholder="সনদ খুঁজুন..."
                           class="w-44 sm:w-56 text-xs px-3 py-2 pl-8 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <i class="ph ph-magnifying-glass absolute left-2.5 top-2.5 text-neutral-400 text-sm pointer-events-none"></i>
                </div>
            </div>
        </div>

        <!-- Sonods Card Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="sonodsContainer">
            <?php foreach ($global_sonods as $gsonod): 
                $gid = (int)$gsonod->deft_cer_all_au_iddd;
                $has_custom = isset($tenant_override_map[$gid]);
                $csonod = $has_custom ? $tenant_override_map[$gid] : null;
                $is_bn = (($gsonod->en_bn_type ?? 1) == 1);
            ?>
                <div class="sonod-template-card bg-white rounded-2xl border transition-all p-5 shadow-xs hover:shadow-md space-y-4 <?= $has_custom ? 'border-emerald-300 ring-1 ring-emerald-200' : 'border-neutral-200/80' ?>"
                     data-gid="<?= $gid ?>"
                     data-has-custom="<?= $has_custom ? '1' : '0' ?>"
                     data-lang="<?= $is_bn ? 'bn' : 'en' ?>"
                     data-title="<?= htmlspecialchars(strtolower($gsonod->certify_titles . ' ' . ($csonod->cert_title_ss ?? '')), ENT_QUOTES, 'UTF-8') ?>">

                    <!-- Card Header -->
                    <div class="flex items-start justify-between gap-3 border-b border-neutral-100 pb-3">
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-sm shrink-0">
                                #<?= $gid ?>
                            </span>
                            <div>
                                <h3 class="text-sm font-bold text-neutral-900 leading-tight">
                                    <?= htmlspecialchars($gsonod->certify_titles, ENT_QUOTES, 'UTF-8') ?>
                                </h3>
                                <span class="text-[11px] text-neutral-500 font-medium">
                                    <?= $is_bn ? '🇧🇩 বাংলা সংস্করণ' : '🇬🇧 English Version' ?>
                                </span>
                            </div>
                        </div>

                        <!-- Status Badge -->
                        <?php if ($has_custom): ?>
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-200 flex items-center gap-1 shrink-0">
                                <i class="ph ph-buildings"></i> ইউনিয়ন কাস্টম সক্রিয়
                            </span>
                        <?php else: ?>
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-neutral-100 text-neutral-600 border border-neutral-200 flex items-center gap-1 shrink-0">
                                <i class="ph ph-globe"></i> গ্লোবাল মাস্টার
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Text Snippet Previews -->
                    <div class="space-y-2 text-xs">
                        <?php if ($has_custom): ?>
                            <div class="p-3 rounded-xl bg-emerald-50/50 border border-emerald-100 space-y-1">
                                <div class="flex items-center justify-between text-[11px] font-bold text-emerald-900">
                                    <span>🏛️ নির্বাচিত ইউনিয়নের কাস্টম শিরোনাম:</span>
                                    <span class="font-mono text-[10px] text-emerald-700">Custom ID #<?= (int)$csonod->certifys_default_ins_ss_iiidddd ?></span>
                                </div>
                                <div class="font-bold text-neutral-900">
                                    <?= htmlspecialchars($csonod->cert_title_ss, ENT_QUOTES, 'UTF-8') ?>
                                </div>
                                <p class="text-neutral-600 text-[11px] leading-relaxed line-clamp-2">
                                    <?= htmlspecialchars(strip_tags($csonod->certf_detail_s_sett), ENT_QUOTES, 'UTF-8') ?>
                                </p>
                            </div>
                        <?php endif; ?>

                        <div class="p-3 rounded-xl bg-neutral-50 border border-neutral-100 space-y-1">
                            <span class="text-[11px] font-bold text-indigo-900 block">
                                🌐 গ্লোবাল মাস্টার বয়ান (সেন্ট্রাল ডিফল্ট):
                            </span>
                            <div class="font-semibold text-neutral-800">
                                <?= htmlspecialchars($gsonod->certify_title_text_s, ENT_QUOTES, 'UTF-8') ?>
                            </div>
                            <p class="text-neutral-500 text-[11px] leading-relaxed line-clamp-2">
                                <?= htmlspecialchars(strip_tags($gsonod->certify_detail_ss), ENT_QUOTES, 'UTF-8') ?>
                            </p>
                        </div>
                    </div>

                    <!-- Actions Bar -->
                    <div class="pt-2 flex flex-wrap items-center justify-between gap-2 border-t border-neutral-100">
                        <div class="flex items-center gap-1.5">
                            <!-- Button 1: Edit Global Master (deft_cer_all) -->
                            <button type="button"
                                    onclick="openEditModal('global', <?= htmlspecialchars(json_encode([
                                        'gid' => $gid,
                                        'title' => $gsonod->certify_titles,
                                        'head_text' => $gsonod->certify_title_text_s,
                                        'detail' => $gsonod->certify_detail_ss,
                                        'lang' => (int)($gsonod->en_bn_type ?? 1),
                                    ]), ENT_QUOTES, 'UTF-8') ?>)"
                                    class="px-2.5 py-1.5 rounded-lg text-[11px] font-bold bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 flex items-center gap-1 cursor-pointer">
                                <i class="ph ph-globe"></i> গ্লোবাল মাস্টার এডিট
                            </button>

                            <!-- Button 2: Edit Tenant Custom Override (certifys_default_ins_ss) -->
                            <button type="button"
                                    onclick="openEditModal('tenant', <?= htmlspecialchars(json_encode([
                                        'gid' => $gid,
                                        'custom_id' => $has_custom ? (int)$csonod->certifys_default_ins_ss_iiidddd : 0,
                                        'title' => $has_custom ? $csonod->cert_title_ss : $gsonod->certify_titles,
                                        'head_text' => $has_custom ? $csonod->certf_head_texts : $gsonod->certify_title_text_s,
                                        'detail' => $has_custom ? $csonod->certf_detail_s_sett : $gsonod->certify_detail_ss,
                                        'lang' => $has_custom ? (int)($csonod->en_bn_type ?? 1) : (int)($gsonod->en_bn_type ?? 1),
                                    ]), ENT_QUOTES, 'UTF-8') ?>)"
                                    class="px-2.5 py-1.5 rounded-lg text-[11px] font-bold bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 flex items-center gap-1 cursor-pointer">
                                <i class="ph ph-pencil-simple"></i> <?= $has_custom ? 'ইউনিয়ন কাস্টম এডিট' : 'এই ইউনিয়নে কাস্টমাইজ করুন' ?>
                            </button>
                        </div>

                        <!-- Button 3: Reset to Global Master if overridden -->
                        <?php if ($has_custom): ?>
                            <button type="button"
                                    onclick="confirmResetOverride(<?= $gid ?>, <?= (int)$csonod->certifys_default_ins_ss_iiidddd ?>, '<?= htmlspecialchars($gsonod->certify_titles, ENT_QUOTES, 'UTF-8') ?>')"
                                    class="px-2.5 py-1.5 rounded-lg text-[11px] font-bold bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 flex items-center gap-1 cursor-pointer"
                                    title="কাস্টমাইজেশন মুছে ফেলে গ্লোবাল ডিফল্ট রিস্টোর করুন">
                                <i class="ph ph-arrow-counter-clockwise"></i> রিসেট
                            </button>
                        <?php endif; ?>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>

    </div> <!-- /#panel-mode-texts -->

    <!-- Modal: Edit Certificate Text (Global Master or Tenant Override) -->
    <div id="sonodTextEditModal" class="fixed inset-0 z-50 bg-neutral-900/60 backdrop-blur-xs overflow-y-auto p-3 sm:p-6 justify-center items-start sm:items-center" style="display: none;">
        <div class="relative bg-white rounded-3xl border border-neutral-200 shadow-2xl max-w-2xl w-full my-auto flex flex-col max-h-[90vh] overflow-hidden">

            <!-- Pinned Modal Header -->
            <div class="px-6 py-4.5 border-b border-neutral-200 flex items-start justify-between gap-4 shrink-0 bg-white z-20">
                <div class="flex items-center gap-3">
                    <span id="modalIconContainer" class="w-10 h-10 rounded-2xl flex items-center justify-center text-xl shrink-0 bg-indigo-50 text-indigo-700">
                        <i id="modalIcon" class="ph ph-globe"></i>
                    </span>
                    <div>
                        <h3 id="modalTitle" class="text-base font-bold text-neutral-900 leading-tight">
                            সনদ টেক্সট ও বয়ান সম্পাদনা
                        </h3>
                        <p id="modalSubtitle" class="text-xs text-neutral-500 mt-0.5">
                            টেম্পলেটের শিরোনাম, সাব-টাইটেল ও বিস্তারিত বয়ান পরিবর্তন করুন।
                        </p>
                    </div>
                </div>
                <button type="button" onclick="closeSonodTextModal()" class="w-8 h-8 rounded-full bg-neutral-100 hover:bg-neutral-200 text-neutral-600 flex items-center justify-center text-lg cursor-pointer transition-colors" aria-label="বন্ধ করুন">
                    <i class="ph ph-x"></i>
                </button>
            </div>

            <!-- Dynamic Form with Scrollable Body and Pinned Footer -->
            <form id="sonodTextModalForm" method="post" action="" class="flex flex-col flex-1 overflow-hidden min-h-0 m-0">
                <div class="p-6 overflow-y-auto flex-1 space-y-4 text-xs">

                    <!-- Modal Warning / Info Banner -->
                    <div id="modalNoticeBanner" class="p-3.5 rounded-xl border text-xs leading-relaxed flex items-start gap-2.5">
                        <i id="modalNoticeIcon" class="ph ph-info text-base shrink-0 pt-0.5"></i>
                        <span id="modalNoticeText"></span>
                    </div>

                    <input type="hidden" name="tenant_id" value="<?= (int)$selected_tenant_id ?>">
                    <input type="hidden" name="sonod_id" id="modalSonodId" value="0">
                <input type="hidden" name="global_id" id="modalGlobalId" value="0">
                <input type="hidden" name="custom_id" id="modalCustomId" value="0">

                <!-- Title & Language Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="sm:col-span-2">
                        <label class="text-xs font-bold text-neutral-700 block mb-1">
                            সনদের নাম / শিরোনাম <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="modalFieldTitle" name="" required
                               class="ht-field text-xs font-bold w-full"
                               placeholder="যেমন: নাগরিকত্ব সনদপত্র">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-neutral-700 block mb-1">ভাষা সংস্করণ</label>
                        <select id="modalFieldLang" name="en_bn_type" class="ht-field text-xs font-bold w-full">
                            <option value="1">বাংলা (১)</option>
                            <option value="2">English (২)</option>
                        </select>
                    </div>
                </div>

                <!-- Sub-Heading / Head Text -->
                <div>
                    <label class="text-xs font-bold text-neutral-700 block mb-1">
                        সনদের ভূমিকা / সাব-হেডিং বাক্য
                    </label>
                    <input type="text" id="modalFieldHead" name=""
                           class="ht-field text-xs w-full"
                           placeholder="যেমন: এই মর্মে প্রত্যয়ন পত্র প্রদান করা যাইতেছে যে...">
                </div>

                <!-- Main Body Template (Textarea) -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="text-xs font-bold text-neutral-700">
                            সনদের মূল বয়ান (Body Template) <span class="text-rose-500">*</span>
                        </label>
                        <span class="text-[10px] text-neutral-400 font-mono" id="charCounter">০ অক্ষর</span>
                    </div>
                    <textarea id="modalFieldDetail" name="" rows="6" required
                              oninput="updateCharCounter(this.value)"
                              class="ht-field text-xs w-full leading-relaxed resize-y font-normal"
                              placeholder="সনদের মূল বিবরণ ও প্রত্যয়নের বিবরণী লিখুন..."></textarea>
                </div>

                <!-- Token Chips Bar for 1-Click Variable Insertion -->
                <div class="p-3 rounded-xl bg-neutral-50 border border-neutral-200/80 space-y-2">
                    <span class="text-[11px] font-bold text-neutral-700 flex items-center gap-1.5">
                        <i class="ph ph-lightning text-indigo-600"></i>
                        <span>ক্লিক করে টেক্সটে ভ্যারিয়েবল টোকেন যুক্ত করুন:</span>
                    </span>
                    <div class="flex flex-wrap gap-1.5">
                        <button type="button" onclick="insertToken('{আবেদনকারীর_নাম}')" class="token-chip px-2 py-1 rounded-md bg-white hover:bg-indigo-50 hover:text-indigo-700 border border-neutral-200 text-[11px] font-semibold text-neutral-700 transition cursor-pointer">
                            + {আবেদনকারীর_নাম}
                        </button>
                        <button type="button" onclick="insertToken('{পিতার_নাম}')" class="token-chip px-2 py-1 rounded-md bg-white hover:bg-indigo-50 hover:text-indigo-700 border border-neutral-200 text-[11px] font-semibold text-neutral-700 transition cursor-pointer">
                            + {পিতার_নাম}
                        </button>
                        <button type="button" onclick="insertToken('{মাতার_নাম}')" class="token-chip px-2 py-1 rounded-md bg-white hover:bg-indigo-50 hover:text-indigo-700 border border-neutral-200 text-[11px] font-semibold text-neutral-700 transition cursor-pointer">
                            + {মাতার_নাম}
                        </button>
                        <button type="button" onclick="insertToken('{স্বামী/স্ত্রীর_নাম}')" class="token-chip px-2 py-1 rounded-md bg-white hover:bg-indigo-50 hover:text-indigo-700 border border-neutral-200 text-[11px] font-semibold text-neutral-700 transition cursor-pointer">
                            + {স্বামী/স্ত্রীর_নাম}
                        </button>
                        <button type="button" onclick="insertToken('{গ্রাম}')" class="token-chip px-2 py-1 rounded-md bg-white hover:bg-indigo-50 hover:text-indigo-700 border border-neutral-200 text-[11px] font-semibold text-neutral-700 transition cursor-pointer">
                            + {গ্রাম}
                        </button>
                        <button type="button" onclick="insertToken('{ওয়ার্ড}')" class="token-chip px-2 py-1 rounded-md bg-white hover:bg-indigo-50 hover:text-indigo-700 border border-neutral-200 text-[11px] font-semibold text-neutral-700 transition cursor-pointer">
                            + {ওয়ার্ড}
                        </button>
                        <button type="button" onclick="insertToken('{ডাকঘর}')" class="token-chip px-2 py-1 rounded-md bg-white hover:bg-indigo-50 hover:text-indigo-700 border border-neutral-200 text-[11px] font-semibold text-neutral-700 transition cursor-pointer">
                            + {ডাকঘর}
                        </button>
                        <button type="button" onclick="insertToken('{উপজেলা}')" class="token-chip px-2 py-1 rounded-md bg-white hover:bg-indigo-50 hover:text-indigo-700 border border-neutral-200 text-[11px] font-semibold text-neutral-700 transition cursor-pointer">
                            + {উপজেলা}
                        </button>
                        <button type="button" onclick="insertToken('{জেলা}')" class="token-chip px-2 py-1 rounded-md bg-white hover:bg-indigo-50 hover:text-indigo-700 border border-neutral-200 text-[11px] font-semibold text-neutral-700 transition cursor-pointer">
                            + {জেলা}
                        </button>
                        <button type="button" onclick="insertToken('{জাতীয়_পরিচয়পত্র_নং}')" class="token-chip px-2 py-1 rounded-md bg-white hover:bg-indigo-50 hover:text-indigo-700 border border-neutral-200 text-[11px] font-semibold text-neutral-700 transition cursor-pointer">
                            + {জাতীয়_পরিচয়পত্র_নং}
                        </button>
                        <button type="button" onclick="insertToken('{জন্ম_নিবন্ধন_নং}')" class="token-chip px-2 py-1 rounded-md bg-white hover:bg-indigo-50 hover:text-indigo-700 border border-neutral-200 text-[11px] font-semibold text-neutral-700 transition cursor-pointer">
                            + {জন্ম_নিবন্ধন_নং}
                        </button>
                        <button type="button" onclick="insertToken('{হোল্ডিং_নং}')" class="token-chip px-2 py-1 rounded-md bg-white hover:bg-indigo-50 hover:text-indigo-700 border border-neutral-200 text-[11px] font-semibold text-neutral-700 transition cursor-pointer">
                            + {হোল্ডিং_নং}
                        </button>
                    </div>
                </div>

                </div>

                <!-- Pinned Modal Actions Footer -->
                <div class="px-6 py-3.5 border-t border-neutral-200 flex items-center justify-end gap-3 shrink-0 bg-neutral-50/90 z-20">
                    <button type="button" onclick="closeSonodTextModal()"
                            class="px-4 py-2 rounded-xl bg-neutral-100 hover:bg-neutral-200 text-neutral-700 text-xs font-bold cursor-pointer transition-colors">
                        বাতিল
                    </button>
                    <button type="submit" id="modalSubmitBtn"
                            class="px-5 py-2 rounded-xl bg-indigo-700 hover:bg-indigo-800 text-white text-xs font-bold flex items-center gap-2 cursor-pointer shadow-xs transition-colors">
                        <i class="ph ph-check-circle text-base"></i>
                        <span>সংরক্ষণ করুন</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Hidden Form for Resetting Tenant Override -->
    <form id="resetTenantOverrideForm" method="post" action="<?= base_url('sadmin_sonod/reset_tenant_sonod_override') ?>" class="hidden">
        <input type="hidden" name="tenant_id" value="<?= (int)$selected_tenant_id ?>">
        <input type="hidden" name="custom_id" id="resetCustomId" value="0">
    </form>

</div>

<!-- Client-side Interactive Script -->
<script>
function switchPrimaryMode(mode) {
    if (mode === 'texts') {
        document.getElementById('panel-mode-design').classList.add('hidden');
        document.getElementById('panel-mode-texts').classList.remove('hidden');

        document.getElementById('btn-mode-texts').classList.add('bg-indigo-600', 'text-white', 'shadow-xs');
        document.getElementById('btn-mode-texts').classList.remove('text-neutral-600', 'hover:bg-neutral-100');

        document.getElementById('btn-mode-design').classList.remove('bg-indigo-600', 'text-white', 'shadow-xs');
        document.getElementById('btn-mode-design').classList.add('text-neutral-600', 'hover:bg-neutral-100');
        window.location.hash = 'texts';
    } else {
        document.getElementById('panel-mode-texts').classList.add('hidden');
        document.getElementById('panel-mode-design').classList.remove('hidden');

        document.getElementById('btn-mode-design').classList.add('bg-indigo-600', 'text-white', 'shadow-xs');
        document.getElementById('btn-mode-design').classList.remove('text-neutral-600', 'hover:bg-neutral-100');

        document.getElementById('btn-mode-texts').classList.remove('bg-indigo-600', 'text-white', 'shadow-xs');
        document.getElementById('btn-mode-texts').classList.add('text-neutral-600', 'hover:bg-neutral-100');
        window.location.hash = 'design';
    }
}

// Auto open texts panel if hash is #texts
if (window.location.hash === '#texts') {
    switchPrimaryMode('texts');
}

function filterSonodScope(scope, btn) {
    document.querySelectorAll('.sonod-scope-filter').forEach(function(b) {
        b.classList.remove('bg-indigo-600', 'text-white', 'shadow-2xs');
        b.classList.add('text-neutral-600', 'hover:bg-neutral-100');
    });
    btn.classList.add('bg-indigo-600', 'text-white', 'shadow-2xs');
    btn.classList.remove('text-neutral-600', 'hover:bg-neutral-100');

    document.querySelectorAll('.sonod-template-card').forEach(function(card) {
        var hasCustom = card.getAttribute('data-has-custom') === '1';
        if (scope === 'all') {
            card.style.display = '';
        } else if (scope === 'global') {
            card.style.display = !hasCustom ? '' : 'none';
        } else if (scope === 'custom') {
            card.style.display = hasCustom ? '' : 'none';
        }
    });
}

function filterSonodLang(lang, btn) {
    document.querySelectorAll('.sonod-lang-filter').forEach(function(b) {
        b.classList.remove('bg-white', 'text-indigo-700', 'shadow-2xs');
        b.classList.add('hover:text-neutral-900');
    });
    btn.classList.add('bg-white', 'text-indigo-700', 'shadow-2xs');
    btn.classList.remove('hover:text-neutral-900');

    document.querySelectorAll('.sonod-template-card').forEach(function(card) {
        var cardLang = card.getAttribute('data-lang');
        if (lang === 'all' || cardLang === lang) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
}

function searchSonodCards(val) {
    var query = (val || '').trim().toLowerCase();
    document.querySelectorAll('.sonod-template-card').forEach(function(card) {
        var title = card.getAttribute('data-title') || '';
        if (!query || title.indexOf(query) !== -1) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
}

function openEditModal(mode, data) {
    var modal = document.getElementById('sonodTextEditModal');
    var form = document.getElementById('sonodTextModalForm');
    var banner = document.getElementById('modalNoticeBanner');
    var bannerText = document.getElementById('modalNoticeText');
    var bannerIcon = document.getElementById('modalNoticeIcon');
    var modalTitle = document.getElementById('modalTitle');
    var modalSubtitle = document.getElementById('modalSubtitle');
    var iconContainer = document.getElementById('modalIconContainer');
    var icon = document.getElementById('modalIcon');

    if (mode === 'global') {
        form.action = '<?= base_url('sadmin_sonod/save_global_sonod_text') ?>';
        document.getElementById('modalSonodId').value = data.gid || 0;
        document.getElementById('modalGlobalId').value = 0;
        document.getElementById('modalCustomId').value = 0;

        document.getElementById('modalFieldTitle').name = 'certify_titles';
        document.getElementById('modalFieldHead').name = 'certify_title_text_s';
        document.getElementById('modalFieldDetail').name = 'certify_detail_ss';

        modalTitle.textContent = 'গ্লোবাল মাস্টার সনদ সম্পাদনা (deft_cer_all)';
        modalSubtitle.textContent = 'এই পরিবর্তনটি সকল নতুন ও যেসব পরিষদ কাস্টমাইজ করেনি তাদের জন্য প্রযোজ্য হবে।';

        iconContainer.className = 'w-10 h-10 rounded-2xl flex items-center justify-center text-xl shrink-0 bg-indigo-50 text-indigo-700';
        icon.className = 'ph ph-globe';

        banner.className = 'p-3.5 rounded-xl border text-xs leading-relaxed flex items-start gap-2.5 bg-indigo-50/70 border-indigo-200 text-indigo-900';
        bannerIcon.className = 'ph ph-shield-check text-base shrink-0 pt-0.5 text-indigo-600';
        bannerText.innerHTML = '<strong>গ্লোবাল আর্কিটেকচার নোটিশ:</strong> এটি প্ল্যাটফর্মের কেন্দ্রীয় গ্লোবাল টেমপ্লেট। এখানে পরিবর্তন করলে যেসব ইউনিয়ন তাদের নিজস্ব বয়ান সেভ করেনি, তারা সবাই স্বয়ংক্রিয়ভাবে এই হালনাগাদ করা বয়ানটি দেখতে পাবে।';
    } else {
        form.action = '<?= base_url('sadmin_sonod/save_tenant_sonod_override') ?>';
        document.getElementById('modalSonodId').value = 0;
        document.getElementById('modalGlobalId').value = data.gid || 0;
        document.getElementById('modalCustomId').value = data.custom_id || 0;

        document.getElementById('modalFieldTitle').name = 'cert_title_ss';
        document.getElementById('modalFieldHead').name = 'certf_head_texts';
        document.getElementById('modalFieldDetail').name = 'certf_detail_s_sett';

        modalTitle.textContent = 'ইউনিয়ন নিজস্ব সনদ কাস্টমাইজেশন (certifys_default_ins_ss)';
        modalSubtitle.textContent = 'নির্বাচিত পরিষদ: <?= htmlspecialchars($selected_tenant->ins_name ?? 'ইউনিয়ন', ENT_QUOTES, 'UTF-8') ?> এর জন্য সংরক্ষিত হবে।';

        iconContainer.className = 'w-10 h-10 rounded-2xl flex items-center justify-center text-xl shrink-0 bg-emerald-50 text-emerald-800';
        icon.className = 'ph ph-buildings';

        banner.className = 'p-3.5 rounded-xl border text-xs leading-relaxed flex items-start gap-2.5 bg-emerald-50/70 border-emerald-200 text-emerald-900';
        bannerIcon.className = 'ph ph-lock-key text-base shrink-0 pt-0.5 text-emerald-600';
        bannerText.innerHTML = '<strong>নিরাপদ আইসোলেশন নিশ্চয়তা:</strong> এই সম্পাদনাটি শুধুমাত্র <strong><?= htmlspecialchars($selected_tenant->ins_name ?? 'ইউনিয়ন', ENT_QUOTES, 'UTF-8') ?></strong> এর নিজস্ব ডাটাবেসে সংরক্ষিত হবে। এটি প্ল্যাটফর্মের কেন্দ্রীয় <strong>গ্লোবাল মাস্টারে কখনোই প্রভাব ফেলবে না</strong>!';
    }

    document.getElementById('modalFieldTitle').value = data.title || '';
    document.getElementById('modalFieldHead').value = data.head_text || '';
    document.getElementById('modalFieldDetail').value = data.detail || '';
    document.getElementById('modalFieldLang').value = data.lang || 1;
    updateCharCounter(data.detail || '');

    modal.style.display = 'flex';
}

function closeSonodTextModal() {
    document.getElementById('sonodTextEditModal').style.display = 'none';
}

function updateCharCounter(val) {
    var count = (val || '').length;
    document.getElementById('charCounter').textContent = count + ' অক্ষর';
}

function insertToken(token) {
    var textarea = document.getElementById('modalFieldDetail');
    if (!textarea) return;

    var start = textarea.selectionStart;
    var end = textarea.selectionEnd;
    var text = textarea.value;

    textarea.value = text.substring(0, start) + token + text.substring(end);
    textarea.focus();
    textarea.selectionStart = textarea.selectionEnd = start + token.length;
    updateCharCounter(textarea.value);
}

function confirmResetOverride(globalId, customId, sonodName) {
    if (confirm('আপনি কি নিশ্চিত যে "' + sonodName + '" এর জন্য এই ইউনিয়নের নিজস্ব কাস্টমাইজেশন মুছে ফেলে প্ল্যাটফর্ম গ্লোবাল মাস্টার টেমপ্লেট রিস্টোর করতে চান?')) {
        document.getElementById('resetCustomId').value = customId;
        document.getElementById('resetTenantOverrideForm').submit();
    }
}

function switchSonodTab(tabId) {
    document.querySelectorAll('.sonod-tab-content').forEach(function(el) {
        el.classList.add('hidden');
    });
    document.getElementById(tabId).classList.remove('hidden');

    document.querySelectorAll('.sonod-tab-btn').forEach(function(btn) {
        btn.classList.remove('bg-indigo-600', 'text-white', 'shadow-xs');
        btn.classList.add('text-neutral-600', 'hover:bg-neutral-100');
    });
    var activeBtn = document.getElementById('btn-' + tabId);
    if (activeBtn) {
        activeBtn.classList.add('bg-indigo-600', 'text-white', 'shadow-xs');
        activeBtn.classList.remove('text-neutral-600', 'hover:bg-neutral-100');
    }
}

function filterBorders(cat, btn) {
    document.querySelectorAll('.border-filter-btn').forEach(function(b) {
        b.classList.remove('bg-white', 'shadow-2xs', 'text-indigo-700');
        b.classList.add('hover:text-neutral-900');
    });
    btn.classList.add('bg-white', 'shadow-2xs', 'text-indigo-700');
    btn.classList.remove('hover:text-neutral-900');

    document.querySelectorAll('.border-card').forEach(function(card) {
        if (cat === 'all' || card.getAttribute('data-category') === cat) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

function onBorderChange(borderKey) {
    var borderUrl = '<?= base_url('inc/assets/sonod_img/borders/') ?>' + borderKey + '.png';
    document.getElementById('prevBorderFrame').style.backgroundImage = "url('" + borderUrl + "')";
    document.getElementById('previewBorderName').textContent = borderKey;

    document.querySelectorAll('.border-card').forEach(function(card) {
        card.classList.remove('border-indigo-600', 'ring-2', 'ring-indigo-200', 'bg-indigo-50/20');
        card.classList.add('border-neutral-200');
        var badge = card.querySelector('.border-check-badge');
        if (badge) badge.remove();
    });

    var selectedInput = document.querySelector('input[name="border_design"][value="' + borderKey + '"]');
    if (selectedInput) {
        var parentCard = selectedInput.closest('.border-card');
        if (parentCard) {
            parentCard.classList.add('border-indigo-600', 'ring-2', 'ring-indigo-200', 'bg-indigo-50/20');
            parentCard.classList.remove('border-neutral-200');
            var b = document.createElement('span');
            b.className = 'border-check-badge absolute top-1.5 right-1.5 w-5 h-5 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs shadow-xs';
            b.innerHTML = '<i class="ph ph-check"></i>';
            parentCard.appendChild(b);
        }
    }
}

function setPalette(key, primary, accent) {
    document.getElementById('theme_color_input').value = key;
    document.getElementById('primaryColorPicker').value = primary;
    document.getElementById('primaryColorText').value = primary;
    document.getElementById('borderColorPicker').value = accent;
    document.getElementById('borderColorText').value = accent;

    document.getElementById('prevUpName').style.color = primary;
    document.getElementById('prevTitleBadge').style.backgroundColor = primary;

    document.querySelectorAll('.palette-btn').forEach(function(btn) {
        btn.classList.remove('border-indigo-600', 'ring-2', 'ring-indigo-200', 'bg-indigo-50/20');
        btn.classList.add('border-neutral-200');
    });
    if (event && event.currentTarget) {
        event.currentTarget.classList.add('border-indigo-600', 'ring-2', 'ring-indigo-200', 'bg-indigo-50/20');
        event.currentTarget.classList.remove('border-neutral-200');
    }
}

function updateCustomColor(hex, type) {
    if (type === 'primary') {
        document.getElementById('primaryColorPicker').value = hex;
        document.getElementById('primaryColorText').value = hex;
        document.getElementById('prevUpName').style.color = hex;
        document.getElementById('prevTitleBadge').style.backgroundColor = hex;
    } else {
        document.getElementById('borderColorPicker').value = hex;
        document.getElementById('borderColorText').value = hex;
    }
}

function onMonogramChange(monoKey) {
    var monoMap = {
        'nojrul': '<?= base_url('inc/assets/sonod_img/nojrul.png') ?>',
        'bangabandhu': '<?= base_url('inc/assets/sonod_img/bangabandhu.png') ?>',
        'bd_gov': '<?= base_url('inc/assets/sonod_img/bd_gov.png') ?>'
    };
    if (monoMap[monoKey]) {
        document.getElementById('prevMonogramImg').src = monoMap[monoKey];
    }
    document.querySelectorAll('.monogram-card').forEach(function(card) {
        card.classList.remove('border-indigo-600', 'bg-indigo-50/20');
        card.classList.add('border-neutral-200');
    });
    var input = document.querySelector('input[name="top_left_monogram"][value="' + monoKey + '"]');
    if (input) {
        input.closest('.monogram-card').classList.add('border-indigo-600', 'bg-indigo-50/20');
        input.closest('.monogram-card').classList.remove('border-neutral-200');
    }
}

function updateWatermarkOpacity(val) {
    document.getElementById('opacityValLabel').textContent = val;
    document.getElementById('prevWatermark').style.opacity = val;
}

function toggleWatermark(show) {
    document.getElementById('prevWatermark').style.display = show ? 'flex' : 'none';
}
</script>
