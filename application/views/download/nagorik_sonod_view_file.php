<?php
$is_en = (($main_data->en_bn_type ?? 1) == 2);
$up_name = !empty($main_data->union_name) ? $main_data->union_name : ($is_en ? (!empty($ins_info->ins_name_en) ? $ins_info->ins_name_en : (!empty($ins_info->ins_name) ? $ins_info->ins_name : 'Union Parishad')) : (!empty($ins_info->ins_name) ? $ins_info->ins_name : 'ইউনিয়ন পরিষদ'));
$upazila_name = !empty($main_data->upazila_name) ? $main_data->upazila_name : ($is_en ? (!empty($ins_info->up_name) ? $ins_info->up_name : '') : (!empty($ins_info->up_bn_name) ? $ins_info->up_bn_name : ''));
$dist_name = !empty($main_data->district_name) ? $main_data->district_name : ($is_en ? (!empty($ins_info->dist_name) ? $ins_info->dist_name : '') : (!empty($ins_info->dist_bn_name) ? $ins_info->dist_bn_name : ''));
$c_post_office = !empty($main_data->post_office) ? $main_data->post_office : '';

$sonod_num = (string)($main_data->cgp_pr_idiidid_pr ?? ($main_data->sonod_uniqs ?? ''));
$sonod_title = !empty($main_data->certificate_title_head_text) ? $main_data->certificate_title_head_text : ($is_en ? 'Citizenship Certificate' : 'নাগরিকত্ব সনদপত্র');
$issue_date = !empty($main_data->timeing_stmpsss) ? date('d-m-Y', (int)$main_data->timeing_stmpsss) : date('d-m-Y');

if (empty($theme_config) || !is_array($theme_config)) {
    $theme_config = function_exists('get_instance') ? get_instance()->SettingModel->getTenantSonodThemeConfig($main_data->insididid_pridididid ?? tenant_code()) : [];
}

$is_checked = function($val, $default = true) {
    if ($val === null || $val === '') return $default;
    return ($val === true || $val === '1' || $val === 1 || $val === 'yes' || $val === 'true');
};

// Colors & Palette
$palette = $theme_config['theme_color'] ?? ($theme_config['palette'] ?? 'indigo');
$primary_color = '#1e3a8a'; // Royal Navy default
$accent_color = '#0284c7';

if ($palette === 'emerald') {
    $primary_color = '#065f46';
    $accent_color = '#059669';
} elseif ($palette === 'slate') {
    $primary_color = '#1e293b';
    $accent_color = '#475569';
} elseif ($palette === 'crimson') {
    $primary_color = '#881337';
    $accent_color = '#be123c';
} elseif ($palette === 'custom' && !empty($theme_config['primary_color'])) {
    $primary_color = $theme_config['primary_color'];
    $accent_color = !empty($theme_config['accent_color']) ? $theme_config['accent_color'] : '#2563eb';
}

// Monogram & Logos
$top_left_monogram = $theme_config['top_left_monogram'] ?? 'bd_gov';
$left_logo_url = base_url('inc/assets/sonod_img/bd_gov.png');
if ($top_left_monogram === 'bangabandhu') {
    $left_logo_url = base_url('inc/assets/sonod_img/bangabandhu.png');
} elseif ($top_left_monogram === 'nojrul') {
    $left_logo_url = base_url('inc/assets/sonod_img/nojrul.png');
} elseif ($top_left_monogram === 'bd_gov') {
    $left_logo_url = base_url('inc/assets/sonod_img/bd_gov.png');
} elseif ($top_left_monogram === 'custom' && !empty($theme_config['monogram_custom_url'])) {
    $left_logo_url = $theme_config['monogram_custom_url'];
} elseif ($top_left_monogram === 'none') {
    $left_logo_url = '';
}

$tenant_logo_url = !empty($ins_info->ins_logo_path) ? media_url($ins_info->ins_logo_path) : base_url('inc/assets/sonod_img/bd_gov.png');
$selected_border = $theme_config['border_design'] ?? 'border_1';
$border_frame_url = '';
if ($selected_border !== 'none') {
    $border_frame_url = base_url('inc/assets/sonod_img/borders/' . $selected_border . '.png');
}
$selected_text_layout = $theme_config['text_layout'] ?? 'layout_grid_classic';

$watermark_visible = $is_checked($theme_config['watermark_visible'] ?? null, true);
$watermark_opacity = !empty($theme_config['watermark_opacity']) ? floatval($theme_config['watermark_opacity']) : 0.08;
$watermark_src = base_url('inc/assets/sonod_img/bd_gov.png');

$show_govt_logo = $is_checked($theme_config['show_govt_logo'] ?? null, true);
$show_up_logo = $is_checked($theme_config['show_up_logo'] ?? null, true);

// Signatory Mode (3-Person vs 2-Person vs 1-Person)
$signatory_mode = $theme_config['signatory_count'] ?? '2_person';
$is_2_person = ($signatory_mode === '2_person');
$is_1_person = ($signatory_mode === '1_person');

$show_member_sign = !$is_2_person && !$is_1_person && $is_checked($theme_config['show_member_sign'] ?? false, false);
$show_sec_sign    = !$is_1_person && $is_checked($theme_config['show_sec_sign'] ?? true, true);
$show_chairman_sign = $is_checked($theme_config['show_chairman_sign'] ?? true, true);
$chairman_sign_url = !empty($ins_info->cm_sign_s64) ? media_url($ins_info->cm_sign_s64) : '';
$sec_sign_url = !empty($ins_info->socib_pic_base64s) ? media_url($ins_info->socib_pic_base64s) : '';

$active_sign_count = ($show_member_sign ? 1 : 0) + ($show_sec_sign ? 1 : 0) + ($show_chairman_sign ? 1 : 0);
$col_width = $active_sign_count > 0 ? (100 / $active_sign_count) . '%' : '50%';

$portal_domain = function_exists('tenant_hostname') ? tenant_hostname() : ($_SERVER['HTTP_HOST'] ?? 'upsheba.com');

$contact_phone_list = [];
if (!empty($ins_info->socib_phone)) {
    $contact_phone_list[] = $is_en ? $ins_info->socib_phone : BanglaConverter::en2bn($ins_info->socib_phone);
}
if (!empty($ins_info->chairman_phone) && $ins_info->chairman_phone !== ($ins_info->socib_phone ?? '')) {
    $contact_phone_list[] = $is_en ? $ins_info->chairman_phone : BanglaConverter::en2bn($ins_info->chairman_phone);
}
if (empty($contact_phone_list) && !empty($ins_info->support_phone)) {
    $contact_phone_list[] = $is_en ? $ins_info->support_phone : BanglaConverter::en2bn($ins_info->support_phone);
}
$contact_phones = implode(', ', $contact_phone_list);

$contact_email = !empty($ins_info->ins_emails) ? $ins_info->ins_emails : (!empty($ins_info->socib_email) ? $ins_info->socib_email : (!empty($ins_info->support_email) ? $ins_info->support_email : ''));
$is_pad_mode = (isset($_GET['pad']) && $_GET['pad'] == '1') || !empty($pad_mode);

// Applicant Photo Check (only authentic photos from DB or uploads)
$applicant_photo_url = '';
$tenant_id = (int)($main_data->insididid_pridididid ?? tenant_code());
$sonod_uniq = (string)($main_data->sonod_uniqs ?? '');
$disk_photo = 'uploads/tenant-' . $tenant_id . '/citizen-' . $sonod_uniq . '.png';

if (file_exists(FCPATH . $disk_photo)) {
    $applicant_photo_url = base_url($disk_photo);
} elseif (!empty($main_data->applicant_photo)) {
    $applicant_photo_url = media_url($main_data->applicant_photo);
} elseif (!empty($main_data->user_picture)) {
    $applicant_photo_url = media_url($main_data->user_picture);
} elseif (!empty($main_data->verify_person_pic_base64)) {
    $applicant_photo_url = media_url($main_data->verify_person_pic_base64);
} elseif (!empty($main_data->user_img_base64)) {
    $applicant_photo_url = media_url($main_data->user_img_base64);
} elseif (!empty($main_data->profile_image)) {
    $applicant_photo_url = media_url($main_data->profile_image);
}
// NO DEMO FALLBACK: When empty, no dummy photo is rendered.

$photo_pos = 'right';
?>
<!DOCTYPE html>
<html lang="<?= $is_en ? 'en' : 'bn'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($sonod_title); ?> | <?= htmlspecialchars($up_name); ?></title>
    
    <!-- Unified Global Sonod Stylesheet -->
    <link rel="stylesheet" href="<?= base_url('inc/assets/css/sonod_global_print.css'); ?>?v=<?= filemtime(FCPATH . 'inc/assets/css/sonod_global_print.css'); ?>">
    
    <style>
        :root {
            --cert-primary: <?= $primary_color; ?>;
            --cert-accent: <?= $accent_color; ?>;
        }
    </style>
</head>
<body class="sonod-body <?= $is_pad_mode ? 'pad-print-mode' : ''; ?>">

    <!-- Top Action Bar (Screen Only - Hidden in Print) -->
    <div class="no-print-bar">
        <div class="title-group">
            <span class="cert-name"><?= htmlspecialchars($sonod_title); ?></span>
            <span class="cert-track">সনদ নং: <?= htmlspecialchars($sonod_num); ?></span>
            <?php if ($is_pad_mode): ?>
                <span style="background: #065f46; color: #fff; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;">প্যাড প্রিন্ট মোড</span>
            <?php endif; ?>
        </div>
        <div class="actions">
            <button onclick="window.print()" class="btn-cert-action btn-cert-print">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                প্রিন্ট করুন
            </button>
            <a href="<?= base_url('psonod?id=' . urlencode($main_data->sonod_uniqs ?? '') . '&download=1' . ($is_pad_mode ? '&pad=1' : '')); ?>" class="btn-cert-action btn-cert-pdf">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                <?= $is_pad_mode ? 'প্যাড PDF ডাউনলোড' : 'PDF ডাউনলোড'; ?>
            </a>
            <?php if (!$is_pad_mode): ?>
                <a href="<?= base_url('nsonod?id=' . urlencode($main_data->sonod_uniqs ?? '') . '&pad=1'); ?>" class="btn-cert-action btn-cert-pad" title="ইউনিয়নের নিজস্ব ছাপানো প্যাডে প্রিন্ট করার জন্য">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    প্যাডে প্রিন্ট মোড
                </a>
            <?php else: ?>
                <a href="<?= base_url('nsonod?id=' . urlencode($main_data->sonod_uniqs ?? '')); ?>" class="btn-cert-action btn-cert-pad" title="সম্পূর্ণ ডিজাইন ও বর্ডার সহ সনদ দেখতে">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16v16H4z"/><path d="M4 9h16"/></svg>
                    পূর্ণাঙ্গ সনদ মোড
                </a>
            <?php endif; ?>
            <button onclick="window.close()" class="btn-cert-action btn-cert-close">
                বন্ধ করুন
            </button>
        </div>
    </div>

    <!-- Printable A4 Sheet -->
    <div class="sonod-page-container">
        
        <!-- Ornate Outer Frame or Custom Uploaded Pad Design (Hidden in Pad Mode) -->
        <?php if (!$is_pad_mode): ?>
            <?php if (($theme_config['design_mode'] ?? 'digital') === 'custom_upload' && !empty($theme_config['custom_pad_path'])): ?>
                <div class="cert-frame-overlay custom-pad-overlay" style="background-image: url('<?= media_url($theme_config['custom_pad_path']); ?>'); background-size: 100% 100%; background-repeat: no-repeat; opacity: <?= (float)($theme_config['custom_pad_opacity'] ?? 1.0); ?>;"></div>
            <?php else: ?>
                <?php if (!empty($border_frame_url)): ?>
                    <div class="cert-frame-overlay" style="background-image: url('<?= $border_frame_url; ?>');"></div>
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Centered Background Watermark (Hidden in Pad Mode) -->
        <?php if (!$is_pad_mode && $watermark_visible): ?>
            <img class="cert-watermark-bg" src="<?= $watermark_src; ?>" alt="Government Watermark" style="opacity: <?= $watermark_opacity; ?>;">
        <?php endif; ?>

        <!-- Main Content Flow -->
        <div class="sonod-content-wrapper">
            
            <div>
                <!-- 1. Header Area or Pad Guide -->
                <?php if (!$is_pad_mode): ?>
                    <table class="sonod-header-table">
                        <tr>
                            <td class="header-logo-left">
                                <?php if ($show_up_logo && !empty($tenant_logo_url)): ?>
                                    <img src="<?= $tenant_logo_url; ?>" alt="Union Parishad Seal">
                                <?php endif; ?>
                            </td>
                            <td class="header-text-center">
                                <div class="header-gov-name"><?= $is_en ? "Government of the People's Republic of Bangladesh" : "গণপ্রজাতন্ত্রী বাংলাদেশ সরকার"; ?></div>
                                <div class="header-up-title"><?= htmlspecialchars($up_name); ?></div>
                                <div class="header-up-location">
                                    <?php if ($is_en): ?>
                                         <?= !empty($upazila_name) ? ('Upazila: ' . htmlspecialchars($upazila_name) . ', ') : ''; ?>
                                         <?= !empty($dist_name) ? ('District: ' . htmlspecialchars($dist_name) . '.') : ''; ?>
                                    <?php else: ?>
                                        <?php if (!empty($upazila_name)): ?>উপজেলা: <?= htmlspecialchars($upazila_name); ?>, <?php endif; ?>
                                        <?php if (!empty($dist_name)): ?>জেলা: <?= htmlspecialchars($dist_name); ?>।<?php endif; ?>
                                    <?php endif; ?>
                                </div>
                                <div class="header-up-domain"><?= htmlspecialchars($portal_domain); ?></div>
                            </td>
                            <td class="header-seal-right">
                                <?php if ($show_govt_logo && !empty($left_logo_url)): ?>
                                    <img src="<?= $left_logo_url; ?>" alt="National Monogram">
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>

                    <!-- Issue Date (Right Aligned Under Seal) -->
                    <div class="header-issue-date">
                        <?= $is_en ? ("Date: " . htmlspecialchars($issue_date)) : ("তারিখ: " . BanglaConverter::en2bn($issue_date) . " ইং"); ?>
                    </div>
                <?php else: ?>
                    <!-- Pre-printed Pad Letterhead On-Screen Guide -->
                    <div class="pad-guide-header">
                        <div class="pad-guide-badge">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            প্যাড প্রিন্ট প্রিভিউ মোড
                        </div>
                        <div class="pad-guide-title">🏛️ <?= htmlspecialchars($up_name); ?> — অফিশিয়াল লেটারহেড প্যাড স্থান</div>
                        <div class="pad-guide-sub">ইউনিয়ন পরিষদের নিজস্ব ছাপানো প্যাডে প্রিন্ট বা PDF ডাউনলোডের সময় এই অংশটি স্বয়ংক্রিয়ভাবে ফাঁকা থাকবে যাতে অফিসিয়াল প্যাডের লোগো ও হেডার অক্ষত থাকে।</div>
                    </div>

                    <!-- Direct Official Pad Meta Row (Memo & Date) -->
                    <div class="pad-meta-bar">
                        <div class="pad-meta-memo">
                            <span class="pad-meta-label"><?= $is_en ? 'Memo / Sonod No:' : 'স্মারক / সনদ নং:'; ?></span>
                            <span class="pad-meta-val"><?= $is_en ? htmlspecialchars($sonod_num) : BanglaConverter::en2bn($sonod_num); ?></span>
                        </div>
                        <div class="pad-meta-date">
                            <span class="pad-meta-label"><?= $is_en ? 'Date:' : 'তারিখ:'; ?></span>
                            <span class="pad-meta-val"><?= $is_en ? htmlspecialchars($issue_date) : (BanglaConverter::en2bn($issue_date) . " ইং"); ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- 2. Certificate Title & Discrete Digit Number Box -->
                <div class="cert-title-section">
                    <div class="cert-main-title"><?= htmlspecialchars($sonod_title); ?></div>
                    
                    <div class="cert-number-bar">
                        <span class="cert-number-label"><?= htmlspecialchars($sonod_title); ?> <?= $is_en ? 'No.' : 'নং.'; ?></span>
                        <?php foreach (str_split($sonod_num) as $digit): ?>
                            <span class="digit-box"><?= $is_en ? htmlspecialchars($digit) : BanglaConverter::en2bn($digit); ?></span>
                        <?php endforeach; ?>
                    </div>

                    <!-- Applicant Photo (if available) -->
                    <?php if (!empty($applicant_photo_url)): ?>
                        <div class="cert-photo-slot pos-<?= htmlspecialchars($photo_pos); ?>">
                            <img src="<?= $applicant_photo_url; ?>" alt="Citizen Photo">
                        </div>
                    <?php endif; ?>
                </div>

                <!-- 3. Citizen Profile / Key-Value Details -->
                <div class="cert-body-intro">
                    <?= $is_en ? "This is to certify that:" : "এই মর্মে সনদপত্র প্রদান করা যাচ্ছে যে,"; ?>
                </div>

                <?php 
                $c_name = $is_en ? (!empty($main_data->ver_per_info_name_en) ? $main_data->ver_per_info_name_en : (!empty($main_data->name_en) ? $main_data->name_en : $main_data->ver_per_info_name_bn)) : $main_data->ver_per_info_name_bn;
                $c_fname = $is_en ? (!empty($main_data->ver_per_info_father_name_en) ? $main_data->ver_per_info_father_name_en : (!empty($main_data->father_name_en) ? $main_data->father_name_en : $main_data->ver_per_info_father_name_bn)) : $main_data->ver_per_info_father_name_bn;
                $c_mname = $is_en ? (!empty($main_data->ver_per_info_mother_name_en) ? $main_data->ver_per_info_mother_name_en : (!empty($main_data->mother_name_en) ? $main_data->mother_name_en : $main_data->ver_per_info_mother_name_bn)) : $main_data->ver_per_info_mother_name_bn;
                $c_nid = !empty($main_data->nid_no17) ? $main_data->nid_no17 : (!empty($main_data->nid_num10) ? $main_data->nid_num10 : (!empty($main_data->nid_number) ? $main_data->nid_number : (!empty($main_data->ver_per_nid_autopr_ididi) && strlen((string)$main_data->ver_per_nid_autopr_ididi) > 5 ? $main_data->ver_per_nid_autopr_ididi : '')));
                $c_mobile = $main_data->mobile_number ?? ($main_data->user_phone ?? '');
                $c_ward = $main_data->ver_per_info_ward_no ?? ($main_data->ward_number ?? '');
                $c_vill = $main_data->custom_village_name ?? ($main_data->village_name ?? '');
                $c_holding = $main_data->custom_house_name ?? ($main_data->holding_no ?? '');
                ?>

                <table class="cert-details-grid">
                    <tr>
                        <td class="lbl"><?= $is_en ? 'Citizen Name' : 'নাম'; ?></td>
                        <td class="sep">:</td>
                        <td class="val"><?= htmlspecialchars($c_name ?: '—'); ?></td>
                        <?php if (!empty($c_fname)): ?>
                            <td class="lbl" style="padding-left: 20px;"><?= $is_en ? 'Father/Husband' : 'পিতা/স্বামীর নাম'; ?></td>
                            <td class="sep">:</td>
                            <td class="val"><?= htmlspecialchars($c_fname); ?></td>
                        <?php endif; ?>
                    </tr>
                    <tr>
                        <td class="lbl"><?= $is_en ? 'Mother\'s Name' : 'মাতার নাম'; ?></td>
                        <td class="sep">:</td>
                        <td class="val"><?= htmlspecialchars($c_mname ?: '—'); ?></td>
                        <?php if (!empty($c_nid)): ?>
                            <td class="lbl" style="padding-left: 20px;"><?= $is_en ? 'National ID / BRN' : 'ন্যাশনাল আইডি নং'; ?></td>
                            <td class="sep">:</td>
                            <td class="val"><?= $is_en ? htmlspecialchars($c_nid) : BanglaConverter::en2bn($c_nid); ?></td>
                        <?php endif; ?>
                    </tr>
                    <?php if (!empty($c_mobile)): ?>
                        <tr>
                            <td class="lbl"><?= $is_en ? 'Mobile' : 'মোবাইল নং'; ?></td>
                            <td class="sep">:</td>
                            <td class="val"><?= $is_en ? htmlspecialchars($c_mobile) : BanglaConverter::en2bn($c_mobile); ?></td>
                            <td colspan="3"></td>
                        </tr>
                    <?php endif; ?>
                </table>

                <!-- Dual Column Address Block -->
                <table class="cert-address-table">
                    <tr>
                        <td style="padding-right: 15px;">
                            <div class="address-block-title"><?= $is_en ? 'Present Address' : 'বর্তমান ঠিকানা'; ?></div>
                            <div><?= $is_en ? 'Holding No: ' : 'হোল্ডিং নং: '; ?><strong><?= !empty($c_holding) ? ($is_en ? htmlspecialchars($c_holding) : BanglaConverter::en2bn($c_holding)) : '—'; ?></strong>, <?= $is_en ? 'Village: ' : 'গ্রাম: '; ?><strong><?= htmlspecialchars($c_vill ?: '—'); ?></strong></div>
                            <div><?= $is_en ? 'Ward No: ' : 'ওয়ার্ড নং: '; ?><strong><?= !empty($c_ward) ? ($is_en ? htmlspecialchars($c_ward) : BanglaConverter::en2bn($c_ward)) : '—'; ?></strong><?= !empty($c_post_office) ? (', ' . ($is_en ? 'Post Office: ' : 'ডাকঘর: ') . '<strong>' . htmlspecialchars($c_post_office) . '</strong>') : ''; ?></div>
                            <div><?= $is_en ? 'Union: ' : 'ইউনিয়ন: '; ?><strong><?= htmlspecialchars($up_name); ?></strong>, <?= $is_en ? 'Upazila: ' : 'উপজেলা: '; ?><strong><?= htmlspecialchars($upazila_name ?: '—'); ?></strong>, <?= $is_en ? 'District: ' : 'জেলা: '; ?><strong><?= htmlspecialchars($dist_name ?: '—'); ?></strong></div>
                        </td>
                        <td style="padding-left: 15px; border-left: 1px dashed #cbd5e1;">
                            <div class="address-block-title"><?= $is_en ? 'Permanent Address' : 'স্থায়ী ঠিকানা'; ?></div>
                            <div><?= $is_en ? 'Holding No: ' : 'হোল্ডিং নং: '; ?><strong><?= !empty($c_holding) ? ($is_en ? htmlspecialchars($c_holding) : BanglaConverter::en2bn($c_holding)) : '—'; ?></strong>, <?= $is_en ? 'Village: ' : 'গ্রাম: '; ?><strong><?= htmlspecialchars($c_vill ?: '—'); ?></strong></div>
                            <div><?= $is_en ? 'Ward No: ' : 'ওয়ার্ড নং: '; ?><strong><?= !empty($c_ward) ? ($is_en ? htmlspecialchars($c_ward) : BanglaConverter::en2bn($c_ward)) : '—'; ?></strong><?= !empty($c_post_office) ? (', ' . ($is_en ? 'Post Office: ' : 'ডাকঘর: ') . '<strong>' . htmlspecialchars($c_post_office) . '</strong>') : ''; ?></div>
                            <div><?= $is_en ? 'Union: ' : 'ইউনিয়ন: '; ?><strong><?= htmlspecialchars($up_name); ?></strong>, <?= $is_en ? 'Upazila: ' : 'উপজেলা: '; ?><strong><?= htmlspecialchars($upazila_name ?: '—'); ?></strong>, <?= $is_en ? 'District: ' : 'জেলা: '; ?><strong><?= htmlspecialchars($dist_name ?: '—'); ?></strong></div>
                        </td>
                    </tr>
                </table>

                <!-- Body Certificate Specific HTML / Text -->
                <?php if (!empty($main_data->sonod_details_html)): ?>
                    <div class="cert-declaration-text">
                        <?= $main_data->sonod_details_html; ?>
                    </div>
                <?php else: ?>
                    <div class="cert-declaration-text">
                        <?php if ($is_en): ?>
                            He/She is a permanent resident of <?= htmlspecialchars($up_name); ?> and a Bangladeshi citizen by birth. To the best of my knowledge, he/she is not involved in any anti-state or anti-social activities and possesses a good moral character. I wish him/her every success and prosperity in life.
                        <?php else: ?>
                            তিনি <?= htmlspecialchars($up_name); ?>-এর একজন স্থায়ী বাসিন্দা ও জন্মগতভাবে বাংলাদেশী নাগরিক। আমার জানামতে তিনি রাষ্ট্র বা সমাজবিরোধী কোনো কার্যকলাপে জড়িত নন এবং তাহার নৈতিক চরিত্র ভালো। আমি তাহার সর্বাঙ্গীণ মঙ্গল ও উন্নতি কামনা করি।
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            </div>

            <!-- Bottom Block: Signatures & Footer -->
            <div>
                <!-- 4. Dynamic Signatures -->
                <table class="cert-signatures-table">
                    <tr>
                        <!-- Col 1: Ward Member (Optional) -->
                        <?php if ($show_member_sign): ?>
                            <td style="width: <?= $col_width; ?>;">
                                <div class="sign-box">
                                    <div class="sign-img-slot">
                                        <!-- Member signature placeholder / image if registered -->
                                    </div>
                                    <div class="sign-line">
                                        <div class="sign-name"><?= !empty($c_ward) ? ($is_en ? ('Ward Member, Ward No: ' . htmlspecialchars($c_ward)) : (BanglaConverter::en2bn($c_ward) . ' নং ওয়ার্ড মেম্বার')) : ($is_en ? 'Ward Member' : 'সংশ্লিষ্ট ওয়ার্ড মেম্বার'); ?></div>
                                        <div class="sign-designation"><?= htmlspecialchars($up_name); ?></div>
                                        <div class="sign-location"><?= htmlspecialchars($upazila_name); ?><?= !empty($dist_name) ? ', ' . htmlspecialchars($dist_name) : ''; ?></div>
                                    </div>
                                </div>
                            </td>
                        <?php endif; ?>

                        <!-- Col 2: Administrative Officer / UP Secretary -->
                        <?php if ($show_sec_sign): ?>
                            <td style="width: <?= $col_width; ?>;">
                                <div class="sign-box">
                                    <div class="sign-img-slot">
                                        <?php if (!empty($sec_sign_url)): ?>
                                            <img src="<?= $sec_sign_url; ?>" alt="Secretary Signature">
                                        <?php endif; ?>
                                    </div>
                                    <div class="sign-line">
                                        <div class="sign-name"><?= !empty($ins_info->socib_name) ? htmlspecialchars($ins_info->socib_name) : ($is_en ? 'Secretary / Admin Officer' : 'ইউপি সচিব / কর্মকর্তা'); ?></div>
                                        <div class="sign-designation"><?= $is_en ? 'Administrative Officer / Secretary' : 'প্রশাসনিক কর্মকর্তা / সচিব'; ?></div>
                                        <div class="sign-location"><?= htmlspecialchars($up_name); ?></div>
                                    </div>
                                </div>
                            </td>
                        <?php endif; ?>

                        <!-- Col 3: Chairman / UNO / Administrator -->
                        <?php if ($show_chairman_sign): ?>
                            <td style="width: <?= $col_width; ?>;">
                                <div class="sign-box">
                                    <div class="sign-img-slot">
                                        <?php if (!empty($chairman_sign_url)): ?>
                                            <img src="<?= $chairman_sign_url; ?>" alt="Chairman Signature">
                                        <?php endif; ?>
                                    </div>
                                    <div class="sign-line">
                                        <div class="sign-name"><?= !empty($ins_info->cm_name) ? htmlspecialchars($ins_info->cm_name) : ($is_en ? 'Chairman / Administrator' : 'ইউপি চেয়ারম্যান / প্রশাসক'); ?></div>
                                        <div class="sign-designation"><?= $is_en ? 'Chairman / Administrator' : 'চেয়ারম্যান / প্রশাসক'; ?></div>
                                        <div class="sign-location"><?= htmlspecialchars($up_name); ?></div>
                                    </div>
                                </div>
                            </td>
                        <?php endif; ?>
                    </tr>
                </table>
            </div>

            <!-- 5. Verification Footer Bar (QR Code on Right) -->
            <table class="cert-footer-table">
                <tr>
                    <td class="footer-instruction-col" style="text-align: right; padding-right: 12px;">
                        <div><?= $is_en ? 'Instructions: ' : 'নির্দেশাবলী: '; ?><?= $is_en ? ('Scan the QR code on the right to verify online or visit ' . htmlspecialchars($portal_domain)) : ('সার্টিফিকেটটি যাচাই করতে ডানপাশের QR Code স্ক্যান করুন অথবা <strong>' . htmlspecialchars($portal_domain) . '</strong> ওয়েবসাইট ভিজিট করুন।'); ?></div>
                        <div style="margin-top: 2px; font-size: 11px; color: #64748b;"><?= $is_en ? 'Parishad Info: ' : 'পরিষদের তথ্য: '; ?><?= htmlspecialchars($is_en ? $up_en_name : $up_name); ?><?= !empty($contact_phones) ? (' | ' . ($is_en ? 'Helpline: ' : 'হেল্পলাইন: ') . $contact_phones) : ''; ?><?= !empty($contact_email) ? (' | ' . ($is_en ? 'Email: ' : 'ইমেইল: ') . htmlspecialchars($contact_email)) : ''; ?></div>
                    </td>
                    <td class="footer-qr-col" style="width: 62px; text-align: right;">
                        <?php if (!empty($qr_code)): ?>
                            <img src="data:image/png;base64,<?= $qr_code; ?>" alt="Verification QR Code" style="width: 56px; height: 56px;">
                        <?php endif; ?>
                    </td>
                </tr>
            </table>

        </div>
    </div>

</body>
</html>
