<?php
$is_en = (($main_data->en_bn_type ?? 1) == 2);
$up_name = !empty($main_data->union_name) ? $main_data->union_name : ($is_en ? (!empty($ins_info->ins_name_en) ? $ins_info->ins_name_en : (!empty($ins_info->ins_name) ? $ins_info->ins_name : 'Union Parishad')) : (!empty($ins_info->ins_name) ? $ins_info->ins_name : 'ইউনিয়ন পরিষদ'));
$upazila_name = !empty($main_data->upazila_name) ? $main_data->upazila_name : ($is_en ? (!empty($ins_info->up_name) ? $ins_info->up_name : '') : (!empty($ins_info->up_bn_name) ? $ins_info->up_bn_name : ''));
$dist_name = !empty($main_data->district_name) ? $main_data->district_name : ($is_en ? (!empty($ins_info->dist_name) ? $ins_info->dist_name : '') : (!empty($ins_info->dist_bn_name) ? $ins_info->dist_bn_name : ''));
$c_post_office = !empty($main_data->post_office) ? $main_data->post_office : '';

$sonod_num = (string)($main_data->cgp_pr_idiidid_pr ?? ($main_data->sonod_uniqs ?? ''));
$sonod_title = !empty($main_data->certificate_title_head_text) ? $main_data->certificate_title_head_text : ($is_en ? 'Inheritance Certificate' : 'ওয়ারিশ সনদপত্র');
$issue_date = !empty($main_data->timeing_stmpsss) ? date('d-m-Y', (int)$main_data->timeing_stmpsss) : date('d-m-Y');

if (empty($theme_config) || !is_array($theme_config)) {
    $theme_config = function_exists('get_instance') ? get_instance()->SettingModel->getTenantSonodThemeConfig($main_data->insididid_pridididid ?? tenant_code()) : [];
}

$is_checked = function($val, $default = true) {
    if ($val === null || $val === '') return $default;
    return ($val === true || $val === '1' || $val === 1 || $val === 'yes' || $val === 'true');
};

$palette = $theme_config['theme_color'] ?? ($theme_config['palette'] ?? 'indigo');
$primary_color = '#1e3a8a';
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
$is_pad_mode = (isset($_GET['pad']) && $_GET['pad'] == '1') || !empty($pad_mode);

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

                    <?php if (!empty($applicant_photo_url)): ?>
                        <div class="cert-photo-slot">
                            <img src="<?= $applicant_photo_url; ?>" alt="<?= $is_en ? 'Applicant Photo' : 'আবেদনকারীর ছবি'; ?>">
                        </div>
                    <?php endif; ?>
                </div>

                <!-- 3. Deceased Person Information Statement -->
                <div class="cert-body-intro">
                    <?= $is_en ? "This is to certify that:" : "এই মর্মে প্রত্যয়ন করা যাচ্ছে যে,"; ?>
                </div>

                <div class="cert-declaration-text" style="margin-top: 0; line-height: 1.85;">
                    <?php
                    $d_name = !empty($dead_per->warish_person_bn_name) ? $dead_per->warish_person_bn_name : '—';
                    $d_fname = !empty($dead_per->warish_father_bn) ? $dead_per->warish_father_bn : '';
                    $d_mname = !empty($dead_per->warish_mother_bn) ? $dead_per->warish_mother_bn : '';
                    $d_vill = !empty($dead_per->bn_vill_names) ? $dead_per->bn_vill_names : '';
                    $d_ward = !empty($dead_per->ward_noo) ? $dead_per->ward_noo : '';
                    $d_death_cert = !empty($dead_per->warish_dead_certify_no) ? $dead_per->warish_dead_certify_no : '';
                    ?>
                    <?php if ($is_en): ?>
                        Late <strong><?= htmlspecialchars($d_name); ?></strong><?= !empty($d_fname) ? (', Father/Husband: <strong>' . htmlspecialchars($d_fname) . '</strong>') : ''; ?><?= !empty($d_mname) ? (', Mother: <strong>' . htmlspecialchars($d_mname) . '</strong>') : ''; ?>, 
                        Address: <?= !empty($d_vill) ? ('Village: ' . htmlspecialchars($d_vill) . ', ') : ''; ?><?= !empty($d_ward) ? ('Ward No: ' . BanglaConverter::bn2en($d_ward) . ', ') : ''; ?><?= !empty($c_post_office) ? ('Post Office: ' . htmlspecialchars($c_post_office) . ', ') : ''; ?>Union: <?= htmlspecialchars($up_name); ?>, Upazila: <?= htmlspecialchars($upazila_name); ?>, District: <?= htmlspecialchars($dist_name); ?>. 
                        He/She was a permanent resident of this Union. <?= !empty($d_death_cert) ? ('(Death Reg. No: ' . BanglaConverter::bn2en($d_death_cert) . '). ') : ''; ?>At the time of demise, the deceased left behind the following legal heir(s):
                    <?php else: ?>
                        মরহুম/মরহুমা <strong><?= htmlspecialchars($d_name); ?></strong><?= !empty($d_fname) ? (', পিতা/স্বামীর নাম: <strong>' . htmlspecialchars($d_fname) . '</strong>') : ''; ?><?= !empty($d_mname) ? (', মাতা: <strong>' . htmlspecialchars($d_mname) . '</strong>') : ''; ?>, 
                        ঠিকানা: <?= !empty($d_vill) ? ('গ্রাম: ' . htmlspecialchars($d_vill) . ', ') : ''; ?><?= !empty($d_ward) ? ('ওয়ার্ড নং: ' . BanglaConverter::en2bn($d_ward) . ', ') : ''; ?><?= !empty($c_post_office) ? ('ডাকঘর: ' . htmlspecialchars($c_post_office) . ', ') : ''; ?>ইউনিয়ন: <?= htmlspecialchars($up_name); ?>, উপজেলা: <?= htmlspecialchars($upazila_name); ?>, জেলা: <?= htmlspecialchars($dist_name); ?>। 
                        তিনি অত্র ইউনিয়নের একজন স্থায়ী বাসিন্দা ছিলেন। <?= !empty($d_death_cert) ? ('(তাঁহার মৃত্যু নিবন্ধন নং- ' . BanglaConverter::en2bn($d_death_cert) . ')। ') : ''; ?>মৃত্যুকালে তিনি নিম্নলিখিত ওয়ারিশগণ কে রেখে মৃত্যুবরণ করেন:
                    <?php endif; ?>
                </div>

                <!-- 4. Inheritors Table (ওয়ারিশগণের তালিকা) -->
                <table class="cert-data-table">
                    <thead>
                        <tr>
                            <th style="width: 45px;"><?= $is_en ? 'SL' : 'নং'; ?></th>
                            <th><?= $is_en ? 'Name of Heir' : 'ওয়ারিশের নাম'; ?></th>
                            <th style="width: 120px;"><?= $is_en ? 'Relationship' : 'সম্পর্ক'; ?></th>
                            <th style="width: 110px;"><?= $is_en ? 'Age / Date of Birth' : 'বয়স / জন্ম তারিখ'; ?></th>
                            <th style="width: 170px;"><?= $is_en ? 'NID / Birth Reg. No.' : 'এনআইডি / জন্ম নিবন্ধন'; ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($warish_gons) && is_array($warish_gons)): ?>
                            <?php $sl = 1; foreach ($warish_gons as $single_per): ?>
                                <tr>
                                    <td class="center"><?= $is_en ? $sl : BanglaConverter::en2bn($sl); ?></td>
                                    <td><strong><?= htmlspecialchars($single_per->names_per ?? ''); ?></strong></td>
                                    <td class="center"><?= htmlspecialchars($single_per->relation_pers ?? ''); ?></td>
                                    <td class="center"><?= !empty($single_per->dob_pers) ? ($is_en ? BanglaConverter::bn2en($single_per->dob_pers) : BanglaConverter::en2bn($single_per->dob_pers)) : '-'; ?></td>
                                    <td class="center"><?= !empty($single_per->nid_pers) ? ($is_en ? BanglaConverter::bn2en($single_per->nid_pers) : BanglaConverter::en2bn($single_per->nid_pers)) : '-'; ?></td>
                                </tr>
                            <?php $sl++; endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="center" style="padding: 12px; color: #64748b;"><?= $is_en ? 'No legal heir information found.' : 'কোনো ওয়ারিশের তথ্য পাওয়া যায়নি।'; ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <!-- Investigation Note & Disclaimer -->
                <div style="font-size: 13.5px; color: #334155; line-height: 1.7; margin-top: 8px;">
                    <div><?= !empty($main_data->sonod_details_html) ? $main_data->sonod_details_html : ($is_en ? 'This inheritance certificate is issued upon proper inquiry by the respective ward member and verification of local dignitaries.' : 'সংশ্লিষ্ট ওয়ার্ডের ইউপি সদস্যের তদন্ত সাপেক্ষে এবং স্থানীয় গণ্যমান্য ব্যক্তিবর্গের সাক্ষ্যমতে অত্র ওয়ারিশ সনদপত্র প্রদান করা হইল।'); ?></div>
                    <div style="font-size: 12px; color: #64748b; margin-top: 3px;">
                        <em><?= $is_en ? 'Note: If this certificate is obtained through false information or concealing facts by forgery, it shall be considered null and void.' : 'বিঃদ্রঃ কোন প্রকার ভুল তথ্য কিংবা জালিয়াতির মাধ্যমে তথ্য গোপন করে সনদ গ্রহণ করলে উক্ত ওয়ারিশ সনদ বাতিল বলে গণ্য হবে।'; ?></em>
                    </div>
                </div>

                <!-- Applicant Information Row -->
                <?php if (!empty($main_data->ver_per_info_name_bn)): ?>
                    <div style="margin-top: 10px; font-size: 14px; font-weight: 600; color: #1e293b;">
                        <?= $is_en ? 'Applicant: ' : 'আবেদনকারী: '; ?><?= htmlspecialchars($main_data->ver_per_info_name_bn); ?>
                    </div>
                <?php endif; ?>

            </div>

            <!-- Bottom Block: Signatures & Footer -->
            <div>
                <!-- 5. Dynamic Signatures -->
                <table class="cert-signatures-table">
                    <tr>
                        <!-- Col 1: Ward Member (Optional) -->
                        <?php if ($show_member_sign): ?>
                            <td style="width: <?= $col_width; ?>;">
                                <div class="sign-box">
                                    <div class="sign-img-slot">
                                        <!-- Member signature placeholder / image if available -->
                                    </div>
                                    <div class="sign-line">
                                        <div class="sign-name"><?= !empty($d_ward) ? ($is_en ? ('Ward Member, Ward No: ' . htmlspecialchars($d_ward)) : (BanglaConverter::en2bn($d_ward) . ' নং ওয়ার্ড মেম্বার')) : ($is_en ? 'Ward Member' : 'সংশ্লিষ্ট ওয়ার্ড মেম্বার'); ?></div>
                                        <div class="sign-designation"><?= htmlspecialchars($up_name); ?></div>
                                        <div class="sign-location"><?= htmlspecialchars($upazila_name); ?><?= !empty($dist_name) ? ', ' . htmlspecialchars($dist_name) : ''; ?></div>
                                    </div>
                                </div>
                            </td>
                        <?php endif; ?>

                        <!-- Col 2: UP Secretary / Administrative Officer -->
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

            <!-- 6. Verification Footer Bar -->
            <table class="cert-footer-table">
                <tr>
                    <td class="footer-qr-col">
                        <?php if (!empty($qr_code)): ?>
                            <img src="data:image/png;base64,<?= $qr_code; ?>" alt="Verification QR Code">
                        <?php endif; ?>
                    </td>
                    <td class="footer-instruction-col">
                        <div><?= $is_en ? 'Instructions: Scan the QR Code or visit ' : 'নির্দেশাবলী: সার্টিফিকেটটি QR Code দিয়ে অথবা '; ?><strong><?= htmlspecialchars($portal_domain); ?></strong><?= $is_en ? ' to verify online.' : ' ওয়েবসাইট থেকে যাচাই করুন।'; ?></div>
                        <div style="margin-top: 2px;"><?= $is_en ? 'Parishad Info: ' : 'পরিষদের তথ্য: '; ?><?= htmlspecialchars($up_name); ?><?= !empty($ins_info->socib_phone) ? ((', ' . ($is_en ? 'Phone: ' : 'ফোন: ')) . ($is_en ? htmlspecialchars($ins_info->socib_phone) : BanglaConverter::en2bn($ins_info->socib_phone))) : ''; ?><?= !empty($ins_info->socib_email) ? ((', ' . ($is_en ? 'Email: ' : 'ইমেইল: ')) . htmlspecialchars($ins_info->socib_email)) : ''; ?></div>
                    </td>
                </tr>
            </table>

        </div>
    </div>

</body>
</html>