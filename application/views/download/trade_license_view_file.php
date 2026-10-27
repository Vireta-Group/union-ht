<?php
$is_en = (($main_data->en_bn_type ?? 1) == 2);
$up_name = $is_en ? (!empty($ins_info->ins_name_en) ? $ins_info->ins_name_en : (!empty($ins_info->ins_name) ? $ins_info->ins_name : 'Union Parishad')) : (!empty($ins_info->ins_name) ? $ins_info->ins_name : 'ইউনিয়ন পরিষদ');
$upazila_name = $is_en ? (!empty($ins_info->up_name) ? $ins_info->up_name : '') : (!empty($ins_info->up_bn_name) ? $ins_info->up_bn_name : '');
$dist_name = $is_en ? (!empty($ins_info->dist_name) ? $ins_info->dist_name : '') : (!empty($ins_info->dist_bn_name) ? $ins_info->dist_bn_name : '');

$sonod_num = (string)($main_data->cgp_pr_idiidid_pr ?? ($main_data->sonod_uniqs ?? ''));
$sonod_title = !empty($main_data->certificate_title_head_text) ? $main_data->certificate_title_head_text : ($is_en ? 'Trade License' : 'ট্রেড লাইসেন্স সনদ');
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
$show_sec_sign = $is_checked($theme_config['show_sec_sign'] ?? null, true);
$show_chairman_sign = $is_checked($theme_config['show_chairman_sign'] ?? null, true);
$chairman_sign_url = !empty($ins_info->cm_sign_s64) ? media_url($ins_info->cm_sign_s64) : '';
$sec_sign_url = !empty($ins_info->socib_pic_base64s) ? media_url($ins_info->socib_pic_base64s) : '';

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

// Trade license specific details from trade_data or main_data
$tl_biz_name = $trade_data->business_ins_name ?? ($main_data->business_name ?? ($main_data->company_name ?? ''));
$tl_proprietor = $main_data->ver_per_info_name_bn ?? ($main_data->name_bn ?? ($main_data->proprietor_name ?? ''));
$tl_father = $main_data->ver_per_info_father_name_bn ?? ($main_data->father_name_bn ?? '');
$tl_mother = $main_data->ver_per_info_mother_name_bn ?? ($main_data->mother_name_bn ?? '');

if ($is_en) {
    if (!empty($main_data->ver_per_info_name_en) || !empty($main_data->name_en) || !empty($main_data->applicant_name_en)) {
        $tl_proprietor = !empty($main_data->ver_per_info_name_en) ? $main_data->ver_per_info_name_en : (!empty($main_data->name_en) ? $main_data->name_en : $main_data->applicant_name_en);
    }
    if (!empty($main_data->ver_per_info_father_name_en) || !empty($main_data->father_name_en) || !empty($main_data->f_name_en)) {
        $tl_father = !empty($main_data->ver_per_info_father_name_en) ? $main_data->ver_per_info_father_name_en : (!empty($main_data->father_name_en) ? $main_data->father_name_en : $main_data->f_name_en);
    }
    if (!empty($main_data->ver_per_info_mother_name_en) || !empty($main_data->mother_name_en) || !empty($main_data->m_name_en)) {
        $tl_mother = !empty($main_data->ver_per_info_mother_name_en) ? $main_data->ver_per_info_mother_name_en : (!empty($main_data->mother_name_en) ? $main_data->mother_name_en : $main_data->m_name_en);
    }
    if (empty($tl_biz_name) || $tl_biz_name === 'ফারাবি এন্টারপ্রাইজ') {
        $tl_biz_name = 'Farabi Enterprise';
    }
}

$tl_mobile = $trade_data->person_mobile_no ?? ($main_data->mobile_number ?? ($main_data->user_phone ?? ''));
$tl_nid = !empty($main_data->nid_no17) ? $main_data->nid_no17 : (!empty($main_data->nid_num10) ? $main_data->nid_num10 : (!empty($main_data->nid_number) ? $main_data->nid_number : (!empty($main_data->ver_per_nid_autopr_ididi) && strlen((string)$main_data->ver_per_nid_autopr_ididi) > 5 ? $main_data->ver_per_nid_autopr_ididi : '')));
$tl_biz_type = $trade_data->business_types ?? ($main_data->business_type ?? ($is_en ? 'Business / Trade' : 'ব্যবসা'));
$tl_serial_no = !empty($trade_data->sonod_no_gens) ? $trade_data->sonod_no_gens : (!empty($trade_data->license_no) ? $trade_data->license_no : '');
$tl_brn = !empty($main_data->birth_registration_num) ? $main_data->birth_registration_num : (!empty($main_data->brn_no) ? $main_data->brn_no : '');
$tl_tin = !empty($trade_data->tax_id_no) ? $trade_data->tax_id_no : (!empty($main_data->tin_number) ? $main_data->tin_number : '');
$tl_renew_date = !empty($trade_data->renewal_date) ? $trade_data->renewal_date : (!empty($main_data->renewal_date) ? $main_data->renewal_date : '');
$tl_capital = $trade_data->muldhon ?? ($main_data->capital_amount ?? '');
$tl_fee_yearly = $trade_data->license_fee_yearly ?? ($is_en ? '200/-' : '২০০/-');
$tl_vat = $trade_data->license_fee_vats ?? ($is_en ? '30/-' : '৩০/-');
$tl_tax = $trade_data->pesha_bebsa_o_kor ?? ($is_en ? '770/-' : '৭৭০/-');
$tl_adds = $trade_data->adds_texts ?? '';
$tl_due = $trade_data->bokeya ?? '';
$tl_service = $trade_data->services_charge ?? '';
$tl_total = $trade_data->total_charge ?? ($is_en ? '1000/-' : '১০০০/-');
$tl_expire_date = $trade_data->license_expires_date ?? ($is_en ? ('30-06-' . (date('Y') + 1)) : ('৩০-০৬-' . (date('Y') + 1)));
$tl_fiscal_year = $trade_data->license_yearss ?? ($trade_data->fiscal_year ?? (date('Y') . '-' . (date('Y') + 1)));

if ($is_en) {
    $tl_expire_date = BanglaConverter::bn2en($tl_expire_date);
    $tl_fiscal_year = BanglaConverter::bn2en($tl_fiscal_year);
    $tl_fee_yearly  = BanglaConverter::bn2en($tl_fee_yearly);
    $tl_vat         = BanglaConverter::bn2en($tl_vat);
    $tl_tax         = BanglaConverter::bn2en($tl_tax);
    $tl_total       = BanglaConverter::bn2en($tl_total);
    if (!empty($tl_due)) $tl_due = BanglaConverter::bn2en($tl_due);
    if (!empty($tl_service)) $tl_service = BanglaConverter::bn2en($tl_service);
    if (!empty($tl_adds)) $tl_adds = BanglaConverter::bn2en($tl_adds);
    if (!empty($tl_capital)) $tl_capital = BanglaConverter::bn2en($tl_capital);

    $tl_vill = !empty($main_data->custom_village_name_en) ? $main_data->custom_village_name_en : (!empty($main_data->custom_village_name) ? $main_data->custom_village_name : '');
    $tl_ward = !empty($main_data->ver_per_info_ward_no) ? BanglaConverter::bn2en($main_data->ver_per_info_ward_no) : '01';
    $tl_address = !empty($tl_vill) 
        ? ($tl_vill . ', Ward No- ' . $tl_ward . ', ' . $up_name . (!empty($upazila_name) ? (', ' . $upazila_name) : '') . (!empty($dist_name) ? (', ' . $dist_name) : '') . '.') 
        : ($up_name . ', ' . $upazila_name . ', ' . $dist_name . '.');
} else {
    $tl_address = !empty($main_data->custom_village_name) ? ($main_data->custom_village_name . ', ওয়ার্ড নং- ' . BanglaConverter::en2bn($main_data->ver_per_info_ward_no ?? '০১') . ', ' . $up_name . (!empty($upazila_name) ? (', ' . $upazila_name) : '') . (!empty($dist_name) ? (', ' . $dist_name) : '') . '।') : ($up_name . ', ' . $upazila_name . ', ' . $dist_name . '।');
}
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
        .tl-grid {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            line-height: 1.5;
        }
        .tl-grid td {
            padding: 1.5px 3px;
            vertical-align: top;
        }
        .tl-grid .lbl {
            width: 180px;
            font-weight: 600;
            color: #334155;
            white-space: nowrap;
        }
        .tl-grid .sep {
            width: 10px;
            text-align: center;
            font-weight: 700;
            color: #475569;
        }
        .tl-grid .val {
            font-weight: 600;
            color: #0f172a;
        }
        .tl-fee-heading {
            font-size: 13.5px;
            font-weight: 700;
            color: var(--cert-primary);
            border-bottom: 1px dashed #cbd5e1;
            padding-bottom: 2px;
            margin: 5px 0 3px 0;
        }
        .cert-number-label {
            margin-right: 14px !important;
        }
        .digit-box {
            margin: 0 1.5px !important;
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
        
        <?php if (!$is_pad_mode): ?>
            <?php if (($theme_config['design_mode'] ?? 'digital') === 'custom_upload' && !empty($theme_config['custom_pad_path'])): ?>
                <div class="cert-frame-overlay custom-pad-overlay" style="background-image: url('<?= media_url($theme_config['custom_pad_path']); ?>'); background-size: 100% 100%; background-repeat: no-repeat; opacity: <?= (float)($theme_config['custom_pad_opacity'] ?? 1.0); ?>;"></div>
            <?php else: ?>
                <?php if (!empty($border_frame_url)): ?>
                    <div class="cert-frame-overlay" style="background-image: url('<?= $border_frame_url; ?>');"></div>
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>

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
                            <span class="pad-meta-label"><?= $is_en ? 'License / Memo No:' : 'লাইসেন্স / স্মারক নং:'; ?></span>
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
                            <img src="<?= $applicant_photo_url; ?>" alt="Proprietor Photo">
                        </div>
                    <?php endif; ?>
                </div>

                <!-- 3. Business Details & Fee Grid -->
                <div class="tl-fee-heading"><?= $is_en ? 'Business & Personal Information' : 'ব্যবসায়িক ও ব্যক্তিগত তথ্যাবলী'; ?></div>
                <table class="tl-grid" style="margin-top: 4px;">
                    <?php if (!empty($tl_serial_no)): ?>
                        <tr>
                            <td class="lbl"><?= $is_en ? 'Trade License No.' : 'ট্রেড লাইসেন্স নং'; ?></td>
                            <td class="sep">:</td>
                            <td class="val"><?= $is_en ? htmlspecialchars($tl_serial_no) : BanglaConverter::en2bn($tl_serial_no); ?></td>
                        </tr>
                    <?php endif; ?>
                    <tr>
                        <td class="lbl"><?= $is_en ? 'Name of Enterprise' : 'ব্যবসা প্রতিষ্ঠানের নাম'; ?></td>
                        <td class="sep">:</td>
                        <td class="val"><strong style="color: var(--cert-primary); font-size: 15px; font-weight: 800; letter-spacing: 0.3px;"><?= htmlspecialchars($tl_biz_name ?: ($is_en ? 'Farabi Enterprise' : 'ফারাবি এন্টারপ্রাইজ')); ?></strong></td>
                    </tr>
                    <tr>
                        <td class="lbl"><?= $is_en ? 'Proprietor / Owner Name' : 'নাম'; ?></td>
                        <td class="sep">:</td>
                        <td class="val"><?= htmlspecialchars($tl_proprietor ?: '—'); ?></td>
                    </tr>
                    <?php if (!empty($tl_father)): ?>
                        <tr>
                            <td class="lbl"><?= $is_en ? "Father's / Husband's Name" : 'পিতা/স্বামীর নাম'; ?></td>
                            <td class="sep">:</td>
                            <td class="val"><?= htmlspecialchars($tl_father); ?></td>
                        </tr>
                    <?php endif; ?>
                    <?php if (!empty($tl_mother)): ?>
                        <tr>
                            <td class="lbl"><?= $is_en ? "Mother's Name" : 'মাতার নাম'; ?></td>
                            <td class="sep">:</td>
                            <td class="val"><?= htmlspecialchars($tl_mother); ?></td>
                        </tr>
                    <?php endif; ?>
                    <?php if (!empty($tl_mobile)): ?>
                        <tr>
                            <td class="lbl"><?= $is_en ? 'Mobile No.' : 'মোবাইল'; ?></td>
                            <td class="sep">:</td>
                            <td class="val"><?= $is_en ? htmlspecialchars($tl_mobile) : BanglaConverter::en2bn($tl_mobile); ?></td>
                        </tr>
                    <?php endif; ?>
                    <?php if (!empty($tl_nid)): ?>
                        <tr>
                            <td class="lbl"><?= $is_en ? 'National ID No.' : 'ন্যাশনাল আইডি নং'; ?></td>
                            <td class="sep">:</td>
                            <td class="val"><?= $is_en ? htmlspecialchars($tl_nid) : BanglaConverter::en2bn($tl_nid); ?></td>
                        </tr>
                    <?php endif; ?>
                    <?php if (!empty($tl_brn)): ?>
                        <tr>
                            <td class="lbl"><?= $is_en ? 'Birth Registration No.' : 'জন্ম নিবন্ধন নং'; ?></td>
                            <td class="sep">:</td>
                            <td class="val"><?= $is_en ? htmlspecialchars($tl_brn) : BanglaConverter::en2bn($tl_brn); ?></td>
                        </tr>
                    <?php endif; ?>
                    <?php if (!empty($tl_tin)): ?>
                        <tr>
                            <td class="lbl"><?= $is_en ? 'Tax ID (TIN)' : 'ট্যাক্স আইডি'; ?></td>
                            <td class="sep">:</td>
                            <td class="val"><?= $is_en ? htmlspecialchars($tl_tin) : BanglaConverter::en2bn($tl_tin); ?></td>
                        </tr>
                    <?php endif; ?>
                    <tr>
                        <td class="lbl"><?= $is_en ? 'Business Address' : 'ব্যবসা প্রতিষ্ঠানের ঠিকানা'; ?></td>
                        <td class="sep">:</td>
                        <td class="val"><?= htmlspecialchars($tl_address); ?></td>
                    </tr>
                    <tr>
                        <td class="lbl"><?= $is_en ? 'Type of Business' : 'ব্যবসার ধরণ'; ?></td>
                        <td class="sep">:</td>
                        <td class="val"><?= htmlspecialchars($tl_biz_type); ?></td>
                    </tr>
                    <?php if (!empty($tl_capital)): ?>
                        <tr>
                            <td class="lbl"><?= $is_en ? 'Capital' : 'মূলধন'; ?></td>
                            <td class="sep">:</td>
                            <td class="val"><?= $is_en ? (htmlspecialchars($tl_capital) . (str_contains((string)$tl_capital, '/-') ? '' : '/-')) : (BanglaConverter::en2bn($tl_capital) . (str_contains((string)$tl_capital, '/-') ? '' : '/-')); ?></td>
                        </tr>
                    <?php endif; ?>
                    <tr>
                        <td class="lbl"><?= $is_en ? 'Issue Date' : 'ইস্যুর তারিখ'; ?></td>
                        <td class="sep">:</td>
                        <td class="val"><?= $is_en ? htmlspecialchars($issue_date) : BanglaConverter::en2bn($issue_date); ?></td>
                    </tr>
                    <?php if (!empty($tl_renew_date)): ?>
                        <tr>
                            <td class="lbl"><?= $is_en ? 'Renewal Date' : 'নবায়ন তারিখ'; ?></td>
                            <td class="sep">:</td>
                            <td class="val"><?= $is_en ? htmlspecialchars($tl_renew_date) : BanglaConverter::en2bn($tl_renew_date); ?></td>
                        </tr>
                    <?php endif; ?>
                    <tr>
                        <td class="lbl"><?= $is_en ? 'Expiry Date' : 'মেয়াদ উত্তীর্ণ তারিখ'; ?></td>
                        <td class="sep">:</td>
                        <td class="val"><?= $is_en ? htmlspecialchars($tl_expire_date) : BanglaConverter::en2bn($tl_expire_date); ?></td>
                    </tr>
                </table>

                <!-- Fee and Tax Breakdown Section -->
                <div class="tl-fee-heading"><?= $is_en ? 'Fee & Tax Assessment' : 'ফি ও করের পরিমান'; ?></div>
                <table class="tl-grid" style="font-size: 13px;">
                    <tr>
                        <td class="lbl"><?= $is_en ? 'Trade License Fee (Annual)' : 'ট্রেড লাইসেন্স ফি (বার্ষিক)'; ?></td>
                        <td class="sep">:</td>
                        <td class="val"><?= $is_en ? htmlspecialchars($tl_fee_yearly) : BanglaConverter::en2bn($tl_fee_yearly); ?><?= (str_contains((string)$tl_fee_yearly, '/-') || empty($tl_fee_yearly)) ? '' : '/-'; ?></td>
                    </tr>
                    <tr>
                        <td class="lbl"><?= $is_en ? '15% VAT Deposit' : '১৫% ভ্যাট বাবদ জমা'; ?></td>
                        <td class="sep">:</td>
                        <td class="val"><?= $is_en ? htmlspecialchars($tl_vat) : BanglaConverter::en2bn($tl_vat); ?><?= (str_contains((string)$tl_vat, '/-') || empty($tl_vat)) ? '' : '/-'; ?></td>
                    </tr>
                    <tr>
                        <td class="lbl"><?= $is_en ? 'Tax on Profession, Trade & Calling' : 'পেশা ব্যবসা ও বৃত্তির উপর কর'; ?></td>
                        <td class="sep">:</td>
                        <td class="val"><?= !empty($tl_tax) ? (($is_en ? htmlspecialchars($tl_tax) : BanglaConverter::en2bn($tl_tax)) . (str_contains((string)$tl_tax, '/-') ? '' : '/-')) : ''; ?></td>
                    </tr>
                    <tr>
                        <td class="lbl"><?= $is_en ? 'Signboard / Advertisement Tax' : 'বিজ্ঞাপন কর (বার্ষিক)'; ?></td>
                        <td class="sep">:</td>
                        <td class="val"><?= !empty($tl_adds) ? (($is_en ? htmlspecialchars($tl_adds) : BanglaConverter::en2bn($tl_adds)) . (str_contains((string)$tl_adds, '/-') ? '' : '/-')) : ''; ?></td>
                    </tr>
                    <tr>
                        <td class="lbl"><?= $is_en ? 'Arrears (Annual)' : 'বকেয়া (বার্ষিক)'; ?></td>
                        <td class="sep">:</td>
                        <td class="val"><?= !empty($tl_due) ? (($is_en ? htmlspecialchars($tl_due) : BanglaConverter::en2bn($tl_due)) . (str_contains((string)$tl_due, '/-') ? '' : '/-')) : ''; ?></td>
                    </tr>
                    <tr>
                        <td class="lbl"><?= $is_en ? 'Service Charge (Annual)' : 'সার্ভিস চার্জ (বার্ষিক)'; ?></td>
                        <td class="sep">:</td>
                        <td class="val"><?= !empty($tl_service) ? (($is_en ? htmlspecialchars($tl_service) : BanglaConverter::en2bn($tl_service)) . (str_contains((string)$tl_service, '/-') ? '' : '/-')) : ''; ?></td>
                    </tr>
                    <tr>
                        <td class="lbl" style="font-weight: 700; color: var(--cert-primary);"><?= $is_en ? 'Total Amount' : 'সর্বমোট'; ?></td>
                        <td class="sep">:</td>
                        <td class="val" style="font-weight: 700; color: var(--cert-primary);"><?= $is_en ? htmlspecialchars($tl_total) : BanglaConverter::en2bn($tl_total); ?><?= (str_contains((string)$tl_total, '/-') || empty($tl_total)) ? '' : '/-'; ?></td>
                    </tr>
                </table>

                <!-- Trade Permit Statement -->
                <div class="cert-declaration-text" style="font-size: 13px; line-height: 1.6; margin-top: 5px; margin-bottom: 6px;">
                    <?php if ($is_en): ?>
                        License fee having been received in favor of the aforesaid establishment, permission is hereby granted to carry on the necessary trade/business for the fiscal year <?= htmlspecialchars($tl_fiscal_year); ?>. This license shall be considered valid until <?= htmlspecialchars($tl_expire_date); ?> and must be renewed annually.
                    <?php else: ?>
                        উল্লেখিত প্রতিষ্ঠানের অনুকূলে প্রদত্ত লাইসেন্স ফি গ্রহণ করিয়া <?= BanglaConverter::en2bn($tl_fiscal_year); ?> সালের জন্য আবশ্যকীয় বাণিজ্য চালাইয়া যাইবার অনুমতি দেওয়া হইল। <?= BanglaConverter::en2bn($tl_expire_date); ?> সন পর্যন্ত এই লাইসেন্স বৈধ বলিয়া বিবেচিত হইবে এবং প্রতি বছর নবায়ন করিতে হইবে।
                    <?php endif; ?>
                </div>

            </div>

            <!-- Bottom Block: 2-Column Signatures & Footer -->
            <div>
                <!-- 2 Signatories for Trade License (Secretary & Chairman) -->
                <table class="cert-signatures-table" style="margin-top: 5px; margin-bottom: 8px;">
                    <tr>
                        <!-- Col 1: UP Secretary / Administrative Officer -->
                        <td style="width: 50%; text-align: center;">
                            <?php if ($show_sec_sign): ?>
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
                            <?php endif; ?>
                        </td>

                        <!-- Col 2: Chairman / Assistant Commissioner / Administrator -->
                        <td style="width: 50%; text-align: center;">
                            <?php if ($show_chairman_sign): ?>
                                <div class="sign-box">
                                    <div class="sign-img-slot">
                                        <?php if (!empty($chairman_sign_url)): ?>
                                            <img src="<?= $chairman_sign_url; ?>" alt="Chairman Signature">
                                        <?php endif; ?>
                                    </div>
                                    <div class="sign-line">
                                        <div class="sign-name"><?= !empty($ins_info->cm_name) ? htmlspecialchars($ins_info->cm_name) : ($is_en ? 'Chairman / Administrator' : 'ইউপি চেয়ারম্যান / প্রশাসক'); ?></div>
                                        <div class="sign-designation"><?= $is_en ? 'Assistant Commissioner (Land) / UP Chairman' : 'সহকারী কমিশনার (ভূমি) / ইউপি চেয়ারম্যান'; ?></div>
                                        <div class="sign-location"><?= htmlspecialchars($upazila_name); ?><?= !empty($dist_name) ? ', ' . htmlspecialchars($dist_name) : ''; ?></div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Verification Footer Bar -->
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
