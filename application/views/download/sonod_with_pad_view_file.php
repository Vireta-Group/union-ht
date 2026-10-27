<?php
$up_name = !empty($ins_info->ins_name) ? $ins_info->ins_name : (!empty($ins_info->un_bn_name) ? ($ins_info->un_bn_name . ' ইউনিয়ন পরিষদ') : 'ইউনিয়ন পরিষদ');
$upazila_bn = !empty($ins_info->up_bn_name) ? $ins_info->up_bn_name : '';
$dist_bn = !empty($ins_info->dist_bn_name) ? $ins_info->dist_bn_name : '';

$up_en_name = !empty($ins_info->ins_name_en) ? $ins_info->ins_name_en : (!empty($ins_info->ins_en_name) ? $ins_info->ins_en_name : (!empty($ins_info->un_name) ? ($ins_info->un_name . ' Union Parishad') : 'Union Parishad'));
$upazila_en = !empty($ins_info->up_name) ? $ins_info->up_name : (!empty($ins_info->upazilla_name) ? $ins_info->upazilla_name : '');
$dist_en = !empty($ins_info->dist_name) ? $ins_info->dist_name : (!empty($ins_info->district_name) ? $ins_info->district_name : '');

$is_en = (($get_data->en_bn_type ?? 1) == 2);
$sonod_num = $get_data->cgp_pr_idiidid_pr ?? '';
$sonod_title = !empty($get_data->certificate_title_head_text) ? $get_data->certificate_title_head_text : ($is_en ? 'Citizenship Certificate' : 'নাগরিকত্ব সনদপত্র');
$issue_date = !empty($get_data->timeing_stmpsss) ? date('d-m-Y', $get_data->timeing_stmpsss) : date('d-m-Y');

// Signatures
$chairman_sign_path = '';
if (!empty($ins_info->cm_sign_s64)) {
    $raw_sign = FCPATH . 'uploads/' . $ins_info->cm_sign_s64;
    if (file_exists($raw_sign)) {
        $chairman_sign_path = $raw_sign;
    }
}
$sec_sign_path = '';
if (!empty($ins_info->socib_pic_base64s)) {
    $raw_sec = FCPATH . 'uploads/' . $ins_info->socib_pic_base64s;
    if (file_exists($raw_sec)) {
        $sec_sign_path = $raw_sec;
    }
}

// Citizen Data
$citizen_name = !empty($get_data->ver_per_info_name_bn) ? $get_data->ver_per_info_name_bn : (!empty($get_data->per_namess) ? $get_data->per_namess : (!empty($get_data->name_bn) ? $get_data->name_bn : ''));
$father_name  = !empty($get_data->ver_per_info_father_name_bn) ? $get_data->ver_per_info_father_name_bn : (!empty($get_data->per_f_namesss) ? $get_data->per_f_namesss : (!empty($get_data->f_name_bn) ? $get_data->f_name_bn : ''));
$mother_name  = !empty($get_data->ver_per_info_mother_name_bn) ? $get_data->ver_per_info_mother_name_bn : (!empty($get_data->per_m_namess) ? $get_data->per_m_namess : (!empty($get_data->m_name_bn) ? $get_data->m_name_bn : ''));
$nid_no       = !empty($get_data->nid_no17) ? $get_data->nid_no17 : (!empty($get_data->nid_num10) ? $get_data->nid_num10 : (!empty($get_data->nid_number) ? $get_data->nid_number : (!empty($get_data->ver_per_nid_autopr_ididi) && strlen((string)$get_data->ver_per_nid_autopr_ididi) > 5 ? $get_data->ver_per_nid_autopr_ididi : '')));
$ward_no      = !empty($get_data->ver_per_info_ward_no) ? $get_data->ver_per_info_ward_no : (!empty($get_data->ins_wardno) ? $get_data->ins_wardno : '');
$clean_ward   = trim(preg_replace('/[^০-৯0-9]/u', '', (string)$ward_no));
$village      = !empty($get_data->custom_village_name) ? $get_data->custom_village_name : (!empty($get_data->village_name) ? $get_data->village_name : '');
$holding_no   = !empty($get_data->custom_house_name) ? $get_data->custom_house_name : (!empty($get_data->holding_no) ? $get_data->holding_no : '');
$mobile       = !empty($get_data->mobile_number) ? $get_data->mobile_number : (!empty($get_data->user_phone) ? $get_data->user_phone : '');

if ($is_en) {
    if (!empty($get_data->ver_per_info_name_en) || !empty($get_data->name_en) || !empty($get_data->applicant_name_en)) {
        $citizen_name = !empty($get_data->ver_per_info_name_en) ? $get_data->ver_per_info_name_en : (!empty($get_data->name_en) ? $get_data->name_en : $get_data->applicant_name_en);
    }
    if (!empty($get_data->ver_per_info_father_name_en) || !empty($get_data->father_name_en) || !empty($get_data->f_name_en)) {
        $father_name  = !empty($get_data->ver_per_info_father_name_en) ? $get_data->ver_per_info_father_name_en : (!empty($get_data->father_name_en) ? $get_data->father_name_en : $get_data->f_name_en);
    }
    if (!empty($get_data->ver_per_info_mother_name_en) || !empty($get_data->mother_name_en) || !empty($get_data->m_name_en)) {
        $mother_name  = !empty($get_data->ver_per_info_mother_name_en) ? $get_data->ver_per_info_mother_name_en : (!empty($get_data->mother_name_en) ? $get_data->mother_name_en : $get_data->m_name_en);
    }
    if (!empty($get_data->custom_village_name_en) || !empty($get_data->village_en)) {
        $village      = !empty($get_data->custom_village_name_en) ? $get_data->custom_village_name_en : $get_data->village_en;
    }
}

$is_warish = (($get_data->deft_cer_aididid ?? 0) == 2);
$is_trade = (($get_data->deft_cer_aididid ?? 0) == 3 || ($get_data->deft_cer_aididid ?? 0) == 1230);

// Trade License specific variables
$tl_biz_name   = $trade_data->business_ins_name ?? ($trade_data->organization_name ?? ($get_data->business_name ?? ($get_data->company_name ?? '')));
$tl_biz_type   = $trade_data->business_types ?? ($trade_data->business_type ?? ($get_data->business_type ?? ($is_en ? 'Business / Trade' : 'ব্যবসা')));
$tl_serial_no  = !empty($trade_data->sonod_no_gens) ? $trade_data->sonod_no_gens : (!empty($trade_data->license_no) ? $trade_data->license_no : '');
$tl_brn        = !empty($get_data->birth_registration_num) ? $get_data->birth_registration_num : (!empty($get_data->brn_no) ? $get_data->brn_no : '');
$tl_tin        = !empty($trade_data->tax_id_no) ? $trade_data->tax_id_no : (!empty($get_data->tin_number) ? $get_data->tin_number : '');
$tl_renew_date = !empty($trade_data->renewal_date) ? $trade_data->renewal_date : (!empty($get_data->renewal_date) ? $get_data->renewal_date : '');
$tl_capital    = $trade_data->muldhon ?? ($get_data->capital_amount ?? '');
$tl_fee_yearly = $trade_data->license_fee_yearly ?? ($is_en ? '200/-' : '২০০/-');
$tl_vat        = $trade_data->license_fee_vats ?? ($is_en ? '30/-' : '৩০/-');
$tl_tax        = $trade_data->pesha_bebsa_o_kor ?? ($is_en ? '770/-' : '৭৭০/-');
$tl_adds       = $trade_data->adds_texts ?? '';
$tl_due        = $trade_data->bokeya ?? '';
$tl_service    = $trade_data->services_charge ?? '';
$tl_total      = $trade_data->total_charge ?? ($is_en ? '1000/-' : '১০০০/-');
$tl_expire_date= $trade_data->license_expires_date ?? ($is_en ? ('30-06-' . (date('Y') + 1)) : ('৩০-০৬-' . (date('Y') + 1)));
$tl_issue_date = $trade_data->license_issue_date ?? ($dating ?? date('d-m-Y'));
$tl_fiscal_year= $trade_data->license_yearss ?? ($trade_data->fiscal_year ?? (date('Y') . '-' . (date('Y') + 1)));

if ($is_en) {
    if (empty($tl_biz_name) || $tl_biz_name === 'ফারাবি এন্টারপ্রাইজ') {
        $tl_biz_name = 'Farabi Enterprise';
    }
}

// Citizen / Applicant Photo Check (local filesystem path for reliable mPDF embedding)
$applicant_photo_url = '';
$tenant_id = (int)($get_data->insididid_pridididid ?? tenant_code());
$sonod_uniq = (string)($get_data->sonod_uniqs ?? '');
$disk_photo = FCPATH . 'uploads/tenant-' . $tenant_id . '/citizen-' . $sonod_uniq . '.png';

if (file_exists($disk_photo)) {
    $applicant_photo_url = $disk_photo;
} elseif (!empty($get_data->applicant_photo) && file_exists(FCPATH . 'uploads/' . $get_data->applicant_photo)) {
    $applicant_photo_url = FCPATH . 'uploads/' . $get_data->applicant_photo;
} elseif (!empty($get_data->user_picture) && file_exists(FCPATH . 'uploads/' . $get_data->user_picture)) {
    $applicant_photo_url = FCPATH . 'uploads/' . $get_data->user_picture;
} elseif (!empty($get_data->verify_person_pic_base64)) {
    $applicant_photo_url = $get_data->verify_person_pic_base64;
} elseif (!empty($get_data->user_img_base64)) {
    $applicant_photo_url = $get_data->user_img_base64;
} elseif (!empty($get_data->profile_image) && file_exists(FCPATH . 'uploads/' . $get_data->profile_image)) {
    $applicant_photo_url = FCPATH . 'uploads/' . $get_data->profile_image;
}
// NO DEMO FALLBACK: Only authentic citizen photos from DB or uploads are displayed.

$photo_pos = 'right';

$is_pad_mode = !empty($is_pad_mode) || (isset($_GET['pad']) && $_GET['pad'] == '1') || !empty($pad_mode);

if (empty($theme_config) || !is_array($theme_config)) {
    $theme_config = function_exists('get_instance') ? get_instance()->SettingModel->getTenantSonodThemeConfig($tenant_id ?? tenant_code()) : [];
}

// Monogram & Logos for Digital Mode (UP Logo LEFT, Govt Monogram RIGHT)
$top_monogram = $theme_config['top_left_monogram'] ?? 'bd_gov';
if ($top_monogram === 'nojrul') {
    $top_monogram = 'bd_gov';
}
$right_logo_path = FCPATH . 'inc/assets/sonod_img/bd_gov.png';
if ($top_monogram === 'bangabandhu') {
    $right_logo_path = FCPATH . 'inc/assets/sonod_img/bangabandhu.png';
} elseif ($top_monogram === 'bd_gov') {
    $right_logo_path = FCPATH . 'inc/assets/sonod_img/bd_gov.png';
} elseif ($top_monogram === 'none') {
    $right_logo_path = '';
}

$tenant_logo_path = '';
if (!empty($ins_info->ins_logo_path) && file_exists(FCPATH . 'uploads/' . $ins_info->ins_logo_path)) {
    $tenant_logo_path = FCPATH . 'uploads/' . $ins_info->ins_logo_path;
}

// Dynamic official certificate border (border_1 to border_15)
$selected_border = $theme_config['border_design'] ?? 'border_1';
$border_frame_path = '';
if ($selected_border !== 'none') {
    $border_candidate = FCPATH . 'inc/assets/sonod_img/borders/' . $selected_border . '.png';
    if (file_exists($border_candidate)) {
        $border_frame_path = $border_candidate;
    } else {
        $border_frame_path = FCPATH . 'inc/assets/sonod_img/borders/border_1.png';
    }
}
$selected_text_layout = $theme_config['text_layout'] ?? 'layout_grid_classic';

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
$custom_footer_slogan = (!empty($theme_config['footer_text']) && $theme_config['footer_text'] !== 'ডিজিটাল ইউনিয়ন পরিষদ সেবা') ? $theme_config['footer_text'] : '';

// Resolve full statement text if incomplete or placeholder
$body_html = trim($get_data->sonod_details_html ?? '');
if (empty($body_html) || $body_html === 'এই মর্মে প্রত্যয়ন করা যাইতেছে যে...' || $body_html === 'এই মর্মে প্রত্যয়ন করা যাচ্ছে যে...' || mb_strlen(strip_tags($body_html)) < 40) {
    if (!empty($get_data->certify_detail_ss)) {
        $body_html = $get_data->certify_detail_ss;
        $body_html = "তাহাকে আমি ব্যক্তিগতভাবে চিনি ও জানি। তিনি " . htmlspecialchars($up_name) . "-এর একজন স্থায়ী বাসিন্দা ও জন্মগতভাবে বাংলাদেশী নাগরিক। আমার জানামতে তাহার স্বভাব ও নৈতিক চরিত্র ভালো এবং তিনি সমাজ বা আইনবিরোধী কোনো কাজের সহিত জড়িত নন।<br><br><strong>আমি তাহার সর্বাঙ্গীণ মঙ্গল ও উজ্জ্বল ভবিষ্যৎ কামনা করি।</strong>";
    }
}

// Signatory Mode (3-Person vs 2-Person vs 1-Person)
$show_member_sign = !empty($theme_config['show_member_sign']);
$signatory_mode   = $theme_config['signatory_count'] ?? ($show_member_sign ? '3_person' : '2_person');
$is_2_person_mode = ($signatory_mode === '2_person') || !$show_member_sign || $is_trade;
$is_1_person_mode = ($signatory_mode === '1_person');
?>
<!DOCTYPE html>
<html lang="<?= $is_en ? 'en' : 'bn'; ?>">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($sonod_title); ?></title>
    <style>
        <?php if (!$is_pad_mode && !empty($border_frame_path)): ?>
        @page {
            background-image: url('<?= $border_frame_path; ?>');
            background-image-resize: 6;
            margin-top: <?= $is_trade ? '10mm' : '14mm'; ?>;
            margin-bottom: <?= $is_trade ? '18mm' : '24mm'; ?>;
            margin-left: <?= $is_trade ? '12mm' : '14mm'; ?>;
            margin-right: <?= $is_trade ? '12mm' : '14mm'; ?>;
        }
        <?php else: ?>
        @page {
            margin-top: <?= $is_pad_mode ? '5mm' : ($is_trade ? '10mm' : '14mm'); ?>;
            margin-bottom: <?= $is_pad_mode ? '22mm' : ($is_trade ? '18mm' : '24mm'); ?>;
            margin-left: <?= $is_pad_mode ? '12mm' : ($is_trade ? '12mm' : '14mm'); ?>;
            margin-right: <?= $is_pad_mode ? '12mm' : ($is_trade ? '12mm' : '14mm'); ?>;
        }
        <?php endif; ?>
        body {
            font-family: 'solaimanlipi', 'adorsholipi', 'kalpurush', sans-serif;
            color: #0f172a;
            font-size: <?= $is_trade ? '14px' : '15px'; ?>;
            line-height: <?= $is_trade ? '1.55' : '1.65'; ?>;
        }

        .pad-spacer {
            height: 145px; /* Clean physical clearance below UP pre-printed letterhead header */
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 5px;
        }

        .title-box {
            text-align: center;
            margin-top: <?= $is_trade ? '2px' : '4px'; ?>;
            margin-bottom: <?= $is_trade ? '6px' : '12px'; ?>;
        }

        .title-text {
            font-size: <?= $is_trade ? '25px' : '26px'; ?>;
            font-weight: bold;
            color: #1e3a8a;
            letter-spacing: 0.5px;
        }

        .number-box-container {
            text-align: center;
            margin-top: <?= $is_trade ? '6px' : '14px'; ?>;
            margin-bottom: <?= $is_trade ? '10px' : '22px'; ?>;
        }

        .intro-text {
            font-size: 15.5px;
            font-weight: bold;
            color: #1e293b;
            margin-bottom: 10px;
        }

        /* Clean Borderless Citizen Detail Table */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        .info-table td {
            padding: 5px 0;
            font-size: 15px;
            vertical-align: top;
        }

        .info-table .lbl {
            font-weight: bold;
            color: #475569;
        }

        .info-table .val {
            font-weight: bold;
            color: #0f172a;
        }

        /* Clean Borderless Address Grid */
        .address-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 22px;
        }

        .address-table td {
            padding: 6px 0;
            vertical-align: top;
            font-size: 14.5px;
            line-height: 1.6;
        }

        .address-title {
            font-weight: bold;
            color: #1e3a8a;
            font-size: 15px;
            margin-bottom: 3px;
        }

        .declaration-text {
            font-size: 15.5px;
            line-height: 1.95;
            text-align: justify;
            text-indent: 40px;
            color: #1e293b;
            margin-bottom: 30px;
        }

        .tl-grid {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            line-height: 1.5;
            margin-bottom: 4px;
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
            color: #1e3a8a;
            border-bottom: 1px dashed #cbd5e1;
            padding-bottom: 2px;
            margin: 5px 0 3px 0;
        }

        /* 2-Row Signature Architecture to prevent mPDF border leaking */
        .signatures-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: <?= $is_trade ? '14px' : '55px'; ?>;
        }

        .signatures-table td {
            text-align: center;
        }

        .sign-slot {
            height: <?= $is_trade ? '42px' : '50px'; ?>;
            text-align: center;
            vertical-align: bottom;
        }

        .sign-slot img {
            max-height: <?= $is_trade ? '40px' : '44px'; ?>;
            max-width: 130px;
        }

        .sign-line-rule {
            border-bottom: 1px solid #475569;
            width: 80%;
            margin: 0 auto;
        }

        .sign-name {
            font-size: 14.5px;
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 2px;
        }

        .sign-desig {
            font-size: 13px;
            font-weight: bold;
            color: #334155;
            margin-bottom: 2px;
        }

        .sign-loc {
            font-size: 12px;
            color: #64748b;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
            border-top: 1px solid #e2e8f0;
            padding-top: 6px;
        }

        .footer-table td {
            vertical-align: middle;
        }

        /* Warish table */
        .warish-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
            font-size: 14px;
        }
        .warish-grid th {
            background-color: #f1f5f9;
            color: #1e3a8a;
            font-weight: bold;
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            text-align: center;
        }
        .warish-grid td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            text-align: center;
            color: #0f172a;
        }
    </style>
</head>
<body>

    <!-- Anchored Page Bottom Footer (mPDF Native htmlpagefooter) -->
    <htmlpagefooter name="sonodFooter">
        <table class="footer-table">
            <tr>
                <td style="text-align: right; padding-right: 12px; font-size: 10.5px; color: #475569; line-height: 1.45; vertical-align: middle;">
                    <div style="margin-bottom: 2px;">
                        <strong style="color: #0f172a;"><?= $is_en ? 'Instructions: ' : 'নির্দেশাবলী: '; ?></strong><?= $is_en ? ('Scan the QR code on the right to verify online or visit ' . htmlspecialchars($portal_domain)) : ('সার্টিফিকেটটি যাচাই করতে ডানপাশের QR Code স্ক্যান করুন অথবা <strong>' . htmlspecialchars($portal_domain) . '</strong> ওয়েবসাইট ভিজিট করুন।'); ?>
                    </div>
                    <div style="font-size: 10px; color: #64748b;">
                        <strong style="color: #1e293b;"><?= $is_en ? 'Parishad Info: ' : 'পরিষদের তথ্য: '; ?></strong><?= htmlspecialchars($is_en ? $up_en_name : $up_name); ?><?= !empty($contact_phones) ? (' | ' . ($is_en ? 'Helpline: ' : 'হেল্পলাইন: ') . $contact_phones) : ''; ?><?= !empty($contact_email) ? (' | ' . ($is_en ? 'Email: ' : 'ইমেইল: ') . htmlspecialchars($contact_email)) : ''; ?><?= !empty($custom_footer_slogan) ? (' | ' . htmlspecialchars($custom_footer_slogan)) : ''; ?>
                    </div>
                </td>
                <td style="width: 62px; text-align: right; vertical-align: middle;">
                    <?php if (!empty($qr_code)): ?>
                        <img src="data:image/png;base64,<?= $qr_code; ?>" width="56" height="56" style="border: 1px solid #cbd5e1; padding: 2px; background: #ffffff;">
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    </htmlpagefooter>
    <sethtmlpagefooter name="sonodFooter" value="on" />

    <?php if ($is_pad_mode): ?>
        <!-- 1. Physical Letterhead Clearance Spacer (Pre-printed Pad Mode) -->
        <div class="pad-spacer"></div>
    <?php else: ?>
        <!-- 1. Official 3-Column Header Table (UP Logo LEFT, Govt Emblem RIGHT) -->
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 8px;">
            <tr>
                <td style="width: 95px; text-align: left; vertical-align: middle;">
                    <?php if (!empty($tenant_logo_path) && file_exists($tenant_logo_path)): ?>
                        <img src="<?= $tenant_logo_path; ?>" style="max-height: 85px; max-width: 85px; height: auto;" alt="Union Parishad Seal" />
                    <?php endif; ?>
                </td>
                <td style="text-align: center; vertical-align: middle;">
                    <div style="font-size: 14px; font-weight: bold; color: #1e3a8a; letter-spacing: 0.3px;">
                        <?= $is_en ? "Government of the People's Republic of Bangladesh" : "গণপ্রজাতন্ত্রী বাংলাদেশ সরকার"; ?>
                    </div>
                    <div style="font-size: 22px; font-weight: bold; color: #065f46; margin: 2px 0; letter-spacing: 0.5px;">
                        <?= htmlspecialchars($is_en ? $up_en_name : $up_name); ?>
                    </div>
                    <div style="font-size: 12.5px; color: #334155; font-weight: 600;">
                        <?php if ($is_en): ?>
                            <?= !empty($upazila_en) ? ('Upazila: ' . htmlspecialchars($upazila_en) . ', ') : ''; ?>
                            <?= !empty($dist_en) ? ('District: ' . htmlspecialchars($dist_en) . '.') : ''; ?>
                        <?php else: ?>
                            <?= !empty($upazila_bn) ? ('উপজেলা: ' . htmlspecialchars($upazila_bn) . ', ') : ''; ?>
                            <?= !empty($dist_bn) ? ('জেলা: ' . htmlspecialchars($dist_bn) . '।') : ''; ?>
                        <?php endif; ?>
                    </div>
                    <div style="font-size: 11px; color: #64748b; margin-top: 1px;"><?= htmlspecialchars($portal_domain); ?></div>
                </td>
                <td style="width: 95px; text-align: right; vertical-align: middle;">
                    <?php if (!empty($right_logo_path) && file_exists($right_logo_path)): ?>
                        <img src="<?= $right_logo_path; ?>" style="max-height: 85px; max-width: 85px; height: auto;" alt="Bangladesh Government Emblem" />
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    <?php endif; ?>

    <!-- 2. Official Pad Meta Row (Date Only - Sarok No Removed) -->
    <table class="meta-table">
        <tr>
            <td style="text-align: right; font-size: 14px; color: #1e3a8a; font-weight: bold;">
                <?= $is_en ? 'Date:' : 'তারিখ:'; ?> 
                <span style="color: #0f172a;"><?= $is_en ? htmlspecialchars($issue_date) : (BanglaConverter::en2bn($issue_date) . ' ইং'); ?></span>
            </td>
        </tr>
    </table>

    <!-- 3. Certificate Title, Photo Positioning & Discrete Digit Number Box -->
    <?php if (!empty($applicant_photo_url) && $photo_pos === 'left'): ?>
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 12px;">
            <tr>
                <td style="width: 80px; vertical-align: middle; text-align: left;">
                    <img src="<?= $applicant_photo_url; ?>" style="max-width: 75px; max-height: 90px; width: auto; height: auto; border-radius: 3px;" alt="নাগরিকের ছবি" />
                </td>
                <td style="vertical-align: middle; text-align: center;">
                    <div class="title-box">
                        <span class="title-text"><?= htmlspecialchars($sonod_title); ?></span>
                    </div>
                    <div class="number-box-container" style="margin-top: <?= $is_trade ? '6px' : '14px'; ?>; margin-bottom: 2px;">
                        <table border="0" cellspacing="3" cellpadding="0" style="margin: 0 auto; border-collapse: separate;">
                            <tr>
                                <td style="font-size: 14px; font-weight: bold; color: #475569; padding-right: 14px; vertical-align: middle; border: none; background: transparent;">
                                    <?= htmlspecialchars($sonod_title); ?> <?= $is_en ? 'No:' : 'নং:'; ?>
                                </td>
                                <?php foreach (str_split($sonod_num) as $digit): ?>
                                    <td style="width: 20px; height: 20px; border: 1px solid #94a3b8; background-color: #f8fafc; color: #1e3a8a; font-weight: bold; font-size: 14px; text-align: center; vertical-align: middle; line-height: 20px; border-radius: 2px; padding: 0;">
                                        <?= $is_en ? htmlspecialchars($digit) : BanglaConverter::en2bn($digit); ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        </table>
                    </div>
                </td>
                <td style="width: 80px; vertical-align: middle; text-align: right;">
                    <!-- Spacer for symmetry -->
                </td>
            </tr>
        </table>
    <?php elseif (!empty($applicant_photo_url) && $photo_pos === 'right'): ?>
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 12px;">
            <tr>
                <td style="width: 80px; vertical-align: middle; text-align: left;">
                    <!-- Spacer for symmetry -->
                </td>
                <td style="vertical-align: middle; text-align: center;">
                    <div class="title-box">
                        <span class="title-text"><?= htmlspecialchars($sonod_title); ?></span>
                    </div>
                    <div class="number-box-container" style="margin-top: <?= $is_trade ? '6px' : '14px'; ?>; margin-bottom: 2px;">
                        <table border="0" cellspacing="3" cellpadding="0" style="margin: 0 auto; border-collapse: separate;">
                            <tr>
                                <td style="font-size: 14px; font-weight: bold; color: #475569; padding-right: 14px; vertical-align: middle; border: none; background: transparent;">
                                    <?= htmlspecialchars($sonod_title); ?> <?= $is_en ? 'No:' : 'নং:'; ?>
                                </td>
                                <?php foreach (str_split($sonod_num) as $digit): ?>
                                    <td style="width: 20px; height: 20px; border: 1px solid #94a3b8; background-color: #f8fafc; color: #1e3a8a; font-weight: bold; font-size: 14px; text-align: center; vertical-align: middle; line-height: 20px; border-radius: 2px; padding: 0;">
                                        <?= $is_en ? htmlspecialchars($digit) : BanglaConverter::en2bn($digit); ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        </table>
                    </div>
                </td>
                <td style="width: 80px; vertical-align: middle; text-align: right;">
                    <img src="<?= $applicant_photo_url; ?>" style="max-width: 75px; max-height: 90px; width: auto; height: auto; border-radius: 3px;" alt="<?= $is_en ? 'Citizen Photo' : 'নাগরিকের ছবি'; ?>" />
                </td>
            </tr>
        </table>
    <?php else: ?>
        <div class="title-box">
            <span class="title-text"><?= htmlspecialchars($sonod_title); ?></span>
        </div>

        <div class="number-box-container" style="margin-top: <?= $is_trade ? '6px' : '14px'; ?>; margin-bottom: <?= $is_trade ? '10px' : '22px'; ?>;">
            <table border="0" cellspacing="3" cellpadding="0" style="margin: 0 auto; border-collapse: separate;">
                <tr>
                    <td style="font-size: 14px; font-weight: bold; color: #475569; padding-right: 14px; vertical-align: middle; border: none; background: transparent;">
                        <?= htmlspecialchars($sonod_title); ?> <?= $is_en ? 'No:' : 'নং:'; ?>
                    </td>
                    <?php foreach (str_split($sonod_num) as $digit): ?>
                        <td style="width: 20px; height: 20px; border: 1px solid #94a3b8; background-color: #f8fafc; color: #1e3a8a; font-weight: bold; font-size: 14px; text-align: center; vertical-align: middle; line-height: 20px; border-radius: 2px; padding: 0;">
                            <?= $is_en ? htmlspecialchars($digit) : BanglaConverter::en2bn($digit); ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            </table>
        </div>

        <?php if (!empty($applicant_photo_url) && $photo_pos === 'under_title'): ?>
            <table style="margin: 6px auto 14px auto; border-collapse: collapse;">
                <tr>
                    <td align="center" style="border: none; padding: 0;">
                        <table style="width: 72px; height: 108px; border: 1px solid #94a3b8; border-radius: 4px; background: #ffffff; border-collapse: collapse;">
                            <tr>
                                <td align="center" valign="middle" style="padding: 2px; border: none; text-align: center; vertical-align: middle;">
                                    <img src="<?= $applicant_photo_url; ?>" style="max-width: 68px; max-height: 104px; width: auto; height: auto; border-radius: 2px;" alt="নাগরিকের ছবি" />
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($is_warish): ?>
        <!-- ================= WARISH SONOD PAD LAYOUT ================= -->
        <?php
        $dp_name = $dead_per->d_name ?? '';
        $dp_father = $dead_per->d_f_name ?? '';
        $dp_mother = $dead_per->d_m_name ?? '';
        $dp_village = $dead_per->d_village ?? $village;
        $dp_ward = $dead_per->d_ward ?? $clean_ward;
        ?>
        <div class="intro-text">
            <?= $is_en ? 'This is to certify that:' : 'এই মর্মে প্রত্যয়ন করা যাচ্ছে যে,'; ?>
        </div>
        <div style="font-size: 15px; line-height: 1.85; margin-bottom: 18px; text-align: justify; text-indent: 35px; color: #1e293b;">
            <?php if ($is_en): ?>
                Late <strong><?= htmlspecialchars($dp_name); ?></strong>, Father/Husband: <strong><?= htmlspecialchars($dp_father); ?></strong>, Mother: <strong><?= htmlspecialchars($dp_mother); ?></strong>, Address: Village: <?= htmlspecialchars($dp_village ?: '-'); ?>, Ward No: <?= htmlspecialchars($dp_ward); ?>, Union: <?= htmlspecialchars($up_en_name); ?>, Upazila: <?= htmlspecialchars($upazila_en); ?>, District: <?= htmlspecialchars($dist_en); ?> was a permanent resident of this Union. At the time of demise, the deceased left behind the following legal heir(s):
            <?php else: ?>
                মরহুম/মরহুমা <strong><?= htmlspecialchars($dp_name); ?></strong>, পিতা/স্বামীর নাম: <strong><?= htmlspecialchars($dp_father); ?></strong>, মাতা: <strong><?= htmlspecialchars($dp_mother); ?></strong>, ঠিকানা: গ্রাম: <?= htmlspecialchars($dp_village ?: '-'); ?>, ওয়ার্ড নং: <?= BanglaConverter::en2bn($dp_ward); ?>, ইউনিয়ন: <?= htmlspecialchars($up_name); ?>, উপজেলা: <?= htmlspecialchars($upazila_bn); ?>, জেলা: <?= htmlspecialchars($dist_bn); ?>। তিনি অত্র ইউনিয়নের একজন স্থায়ী বাসিন্দা ছিলেন। মৃত্যুকালীন সময়ে তিনি নিম্নলিখিত ওয়ারিশগণ কে রেখে মৃত্যুবরণ করেন:
            <?php endif; ?>
        </div>

        <table class="warish-grid">
            <thead>
                <tr>
                    <th style="width: 8%;"><?= $is_en ? 'SL' : 'নং'; ?></th>
                    <th style="width: 34%;"><?= $is_en ? 'Name of Heir' : 'ওয়ারিশের নাম'; ?></th>
                    <th style="width: 18%;"><?= $is_en ? 'Relationship' : 'সম্পর্ক'; ?></th>
                    <th style="width: 18%;"><?= $is_en ? 'Age / Date of Birth' : 'বয়স / জন্ম তারিখ'; ?></th>
                    <th style="width: 22%;"><?= $is_en ? 'NID / Birth Reg. No.' : 'এনআইডি / জন্ম নিবন্ধন'; ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($warish_gons) && is_array($warish_gons)): ?>
                    <?php $sl = 1; foreach ($warish_gons as $w): ?>
                        <tr>
                            <td><?= $is_en ? ($sl++) : BanglaConverter::en2bn($sl++); ?></td>
                            <td style="font-weight: bold; text-align: left; padding-left: 10px;"><?= htmlspecialchars($w->w_name ?? '-'); ?></td>
                            <td><?= htmlspecialchars($w->w_relation ?? '-'); ?></td>
                            <td><?= !empty($w->w_age) ? ($is_en ? BanglaConverter::bn2en($w->w_age) : BanglaConverter::en2bn($w->w_age)) : '-'; ?></td>
                            <td><?= !empty($w->w_nid) ? ($is_en ? BanglaConverter::bn2en($w->w_nid) : BanglaConverter::en2bn($w->w_nid)) : '-'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="color: #64748b; padding: 12px;"><?= $is_en ? 'No legal heir information found' : 'কোনো ওয়ারিশের তথ্য পাওয়া যায়নি'; ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div style="font-size: 13.5px; color: #475569; margin-bottom: 24px; line-height: 1.6;">
            <?= $is_en ? 'According to the application and field inquiry by the respective ward member, the deceased has no other legal heirs except those listed above.' : 'আবেদনকারীর আবেদন ও সংশ্লিষ্ট ওয়ার্ডের ইউপি সদস্যের তদন্তমতে উক্ত ওয়ারিশগণ ব্যতিত মৃত ব্যক্তির আর কোন ওয়ারিশ নাই।'; ?><br>
            <span style="font-size: 12px; color: #64748b; font-style: italic;"><?= $is_en ? 'Note: If this certificate is obtained by providing false information or concealing facts through forgery, it shall be deemed cancelled.' : 'বিঃদ্রঃ কোনো প্রকার ভুল তথ্য কিংবা জালিয়াতির মাধ্যমে তথ্য গোপন করে সনদ গ্রহণ করলে উক্ত ওয়ারিশ সনদ বাতিল বলে গণ্য হবে।'; ?></span>
        </div>

    <?php elseif ($is_trade): ?>
        <!-- ================= TRADE LICENSE PAD LAYOUT ================= -->
        <div class="tl-fee-heading"><?= $is_en ? 'Business & Personal Information' : 'ব্যবসায়িক ও ব্যক্তিগত তথ্যাবলী'; ?></div>
        <table class="tl-grid" style="margin-top: 4px;">
            <?php if (!empty($tl_serial_no)): ?>
            <tr>
                <td class="lbl"><?= $is_en ? 'Trade License No.' : 'ট্রেড লাইসেন্স নং'; ?></td>
                <td class="sep">:</td>
                <td class="val"><?= $is_en ? BanglaConverter::bn2en($tl_serial_no) : BanglaConverter::en2bn($tl_serial_no); ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td class="lbl"><?= $is_en ? 'Name of Enterprise' : 'ব্যবসা প্রতিষ্ঠানের নাম'; ?></td>
                <td class="sep">:</td>
                <td class="val"><strong style="color: #1e3a8a; font-size: 15px; font-weight: 800; letter-spacing: 0.3px;"><?= htmlspecialchars($tl_biz_name ?: ($is_en ? 'Farabi Enterprise' : 'ফারাবি এন্টারপ্রাইজ')); ?></strong></td>
            </tr>
            <tr>
                <td class="lbl"><?= $is_en ? 'Proprietor / Owner Name' : 'নাম'; ?></td>
                <td class="sep">:</td>
                <td class="val"><?= htmlspecialchars($citizen_name ?: '—'); ?></td>
            </tr>
            <?php if (!empty($father_name)): ?>
            <tr>
                <td class="lbl"><?= $is_en ? "Father's / Husband's Name" : 'পিতা/স্বামীর নাম'; ?></td>
                <td class="sep">:</td>
                <td class="val"><?= htmlspecialchars($father_name); ?></td>
            </tr>
            <?php endif; ?>
            <?php if (!empty($mother_name)): ?>
            <tr>
                <td class="lbl"><?= $is_en ? "Mother's Name" : 'মাতার নাম'; ?></td>
                <td class="sep">:</td>
                <td class="val"><?= htmlspecialchars($mother_name); ?></td>
            </tr>
            <?php endif; ?>
            <?php if (!empty($mobile)): ?>
            <tr>
                <td class="lbl"><?= $is_en ? 'Mobile No.' : 'মোবাইল'; ?></td>
                <td class="sep">:</td>
                <td class="val"><?= $is_en ? BanglaConverter::bn2en($mobile) : BanglaConverter::en2bn($mobile); ?></td>
            </tr>
            <?php endif; ?>
            <?php if (!empty($nid_no)): ?>
            <tr>
                <td class="lbl"><?= $is_en ? 'National ID No.' : 'ন্যাশনাল আইডি নং'; ?></td>
                <td class="sep">:</td>
                <td class="val"><?= $is_en ? BanglaConverter::bn2en($nid_no) : BanglaConverter::en2bn($nid_no); ?></td>
            </tr>
            <?php endif; ?>
            <?php if (!empty($tl_brn)): ?>
            <tr>
                <td class="lbl"><?= $is_en ? 'Birth Registration No.' : 'জন্ম নিবন্ধন নং'; ?></td>
                <td class="sep">:</td>
                <td class="val"><?= $is_en ? BanglaConverter::bn2en($tl_brn) : BanglaConverter::en2bn($tl_brn); ?></td>
            </tr>
            <?php endif; ?>
            <?php if (!empty($tl_tin)): ?>
            <tr>
                <td class="lbl"><?= $is_en ? 'Tax ID (TIN)' : 'ট্যাক্স আইডি'; ?></td>
                <td class="sep">:</td>
                <td class="val"><?= $is_en ? BanglaConverter::bn2en($tl_tin) : BanglaConverter::en2bn($tl_tin); ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td class="lbl"><?= $is_en ? 'Business Address' : 'ব্যবসা প্রতিষ্ঠানের ঠিকানা'; ?></td>
                <td class="sep">:</td>
                <td class="val"><?php
                    if ($is_en) {
                        echo !empty($village) 
                            ? (htmlspecialchars($village) . ', Ward No- ' . ($clean_ward ? BanglaConverter::bn2en($clean_ward) : '01') . ', ' . htmlspecialchars($up_en_name) . (!empty($upazila_en) ? (', ' . htmlspecialchars($upazila_en)) : '') . (!empty($dist_en) ? (', ' . htmlspecialchars($dist_en)) : '') . '.')
                            : (htmlspecialchars($up_en_name) . ', ' . htmlspecialchars($upazila_en) . ', ' . htmlspecialchars($dist_en) . '.');
                    } else {
                        echo !empty($village) 
                            ? (htmlspecialchars($village) . ', ওয়ার্ড নং- ' . BanglaConverter::en2bn($clean_ward ?: '০১') . ', ' . htmlspecialchars($up_name) . (!empty($upazila_bn) ? (', ' . htmlspecialchars($upazila_bn)) : '') . (!empty($dist_bn) ? (', ' . htmlspecialchars($dist_bn)) : '') . '।')
                            : (htmlspecialchars($up_name) . ', ' . htmlspecialchars($upazila_bn) . ', ' . htmlspecialchars($dist_bn) . '।');
                    }
                ?></td>
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
                <td class="val"><?= $is_en ? (BanglaConverter::bn2en($tl_capital) . (str_contains((string)$tl_capital, '/-') ? '' : '/-')) : (BanglaConverter::en2bn($tl_capital) . (str_contains((string)$tl_capital, '/-') ? '' : '/-')); ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td class="lbl"><?= $is_en ? 'Issue Date' : 'ইস্যুর তারিখ'; ?></td>
                <td class="sep">:</td>
                <td class="val"><?= $is_en ? BanglaConverter::bn2en($tl_issue_date) : BanglaConverter::en2bn($tl_issue_date); ?></td>
            </tr>
            <?php if (!empty($tl_renew_date)): ?>
            <tr>
                <td class="lbl"><?= $is_en ? 'Renewal Date' : 'নবায়ন তারিখ'; ?></td>
                <td class="sep">:</td>
                <td class="val"><?= $is_en ? BanglaConverter::bn2en($tl_renew_date) : BanglaConverter::en2bn($tl_renew_date); ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td class="lbl"><?= $is_en ? 'Expiry Date' : 'মেয়াদ উত্তীর্ণ তারিখ'; ?></td>
                <td class="sep">:</td>
                <td class="val"><?= $is_en ? BanglaConverter::bn2en($tl_expire_date) : BanglaConverter::en2bn($tl_expire_date); ?></td>
            </tr>
        </table>

        <!-- Fee and Tax Breakdown Section (হিসাব-নিকাশ) -->
        <div class="tl-fee-heading"><?= $is_en ? 'Fee & Tax Assessment' : 'ফি ও করের পরিমান'; ?></div>
        <table class="tl-grid" style="font-size: 13px; margin-bottom: 8px;">
            <tr>
                <td class="lbl"><?= $is_en ? 'Trade License Fee (Annual)' : 'ট্রেড লাইসেন্স ফি (বার্ষিক)'; ?></td>
                <td class="sep">:</td>
                <td class="val"><?= $is_en ? BanglaConverter::bn2en($tl_fee_yearly) : BanglaConverter::en2bn($tl_fee_yearly); ?><?= (str_contains((string)$tl_fee_yearly, '/-') || empty($tl_fee_yearly)) ? '' : '/-'; ?></td>
            </tr>
            <tr>
                <td class="lbl"><?= $is_en ? '15% VAT Deposit' : '১৫% ভ্যাট বাবদ জমা'; ?></td>
                <td class="sep">:</td>
                <td class="val"><?= $is_en ? BanglaConverter::bn2en($tl_vat) : BanglaConverter::en2bn($tl_vat); ?><?= (str_contains((string)$tl_vat, '/-') || empty($tl_vat)) ? '' : '/-'; ?></td>
            </tr>
            <tr>
                <td class="lbl"><?= $is_en ? 'Tax on Profession, Trade & Calling' : 'পেশা ব্যবসা ও বৃত্তির উপর কর'; ?></td>
                <td class="sep">:</td>
                <td class="val"><?= !empty($tl_tax) ? (($is_en ? BanglaConverter::bn2en($tl_tax) : BanglaConverter::en2bn($tl_tax)) . (str_contains((string)$tl_tax, '/-') ? '' : '/-')) : ''; ?></td>
            </tr>
            <tr>
                <td class="lbl"><?= $is_en ? 'Signboard / Advertisement Tax' : 'বিজ্ঞাপন কর (বার্ষিক)'; ?></td>
                <td class="sep">:</td>
                <td class="val"><?= !empty($tl_adds) ? (($is_en ? BanglaConverter::bn2en($tl_adds) : BanglaConverter::en2bn($tl_adds)) . (str_contains((string)$tl_adds, '/-') ? '' : '/-')) : ''; ?></td>
            </tr>
            <tr>
                <td class="lbl"><?= $is_en ? 'Arrears (Annual)' : 'বকেয়া (বার্ষিক)'; ?></td>
                <td class="sep">:</td>
                <td class="val"><?= !empty($tl_due) ? (($is_en ? BanglaConverter::bn2en($tl_due) : BanglaConverter::en2bn($tl_due)) . (str_contains((string)$tl_due, '/-') ? '' : '/-')) : ''; ?></td>
            </tr>
            <tr>
                <td class="lbl"><?= $is_en ? 'Service Charge (Annual)' : 'সার্ভিস চার্জ (বার্ষিক)'; ?></td>
                <td class="sep">:</td>
                <td class="val"><?= !empty($tl_service) ? (($is_en ? BanglaConverter::bn2en($tl_service) : BanglaConverter::en2bn($tl_service)) . (str_contains((string)$tl_service, '/-') ? '' : '/-')) : ''; ?></td>
            </tr>
            <tr>
                <td class="lbl" style="font-weight: 700; color: #1e3a8a;"><?= $is_en ? 'Total Amount' : 'সর্বমোট'; ?></td>
                <td class="sep">:</td>
                <td class="val" style="font-weight: 700; color: #1e3a8a;"><?= $is_en ? BanglaConverter::bn2en($tl_total) : BanglaConverter::en2bn($tl_total); ?><?= (str_contains((string)$tl_total, '/-') || empty($tl_total)) ? '' : '/-'; ?></td>
            </tr>
        </table>

        <!-- Trade Permit Declaration Statement -->
        <div style="font-size: 13px; line-height: 1.6; text-align: justify; color: #1e293b; margin-top: 5px; margin-bottom: 6px;">
            <?php if ($is_en): ?>
                License fee having been received in favor of the aforesaid establishment, permission is hereby granted to carry on the necessary trade/business for the fiscal year <?= BanglaConverter::bn2en($tl_fiscal_year); ?>. This license shall be considered valid until <?= BanglaConverter::bn2en($tl_expire_date); ?> and must be renewed annually.
            <?php else: ?>
                উল্লেখিত প্রতিষ্ঠানের অনুকূলে প্রদত্ত লাইসেন্স ফি গ্রহণ করিয়া <?= BanglaConverter::en2bn($tl_fiscal_year); ?> সালের জন্য আবশ্যকীয় বাণিজ্য চালাইয়া যাইবার অনুমতি দেওয়া হইল। <?= BanglaConverter::en2bn($tl_expire_date); ?> সন পর্যন্ত এই লাইসেন্স বৈধ বলিয়া বিবেচিত হইবে এবং প্রতি বছর নবায়ন করিতে হইবে।
            <?php endif; ?>
        </div>

    <?php else: ?>
        <!-- ================= GENERAL / NAGORIK / PROTTAYON PAD LAYOUT ================= -->
        <?php if ($selected_text_layout === 'layout_narrative'): ?>
            <!-- 1. Narrative Prose Layout -->
            <div class="intro-text">
                <?= $is_en ? 'This is to certify that:' : 'এই মর্মে প্রত্যয়ন / সনদপত্র প্রদান করা যাইতেছে যে,'; ?>
            </div>
            <div style="font-size: 15.5px; line-height: 2.05; text-align: justify; text-indent: 35px; color: #1e293b; margin-bottom: 24px;">
                <?= $is_en ? 'Mr./Ms.' : 'জনাব/বেগম'; ?> <strong style="color: #0f172a; font-size: 16px;"><?= htmlspecialchars($citizen_name ?: '-'); ?></strong>, <?= $is_en ? 'Father/Husband:' : 'পিতা/স্বামী:'; ?> <strong><?= htmlspecialchars($father_name ?: '-'); ?></strong>, <?= $is_en ? 'Mother:' : 'মাতা:'; ?> <strong><?= htmlspecialchars($mother_name ?: '-'); ?></strong><?= !empty($nid_no) ? (', ' . ($is_en ? 'National ID / BRN:' : 'জাতীয় পরিচয়পত্র/জন্ম নিবন্ধন নম্বর:') . ' <strong>' . ($is_en ? htmlspecialchars($nid_no) : BanglaConverter::en2bn($nid_no)) . '</strong>') : ''; ?>। 
                <?= $is_en ? 'His/Her present address is' : 'তাঁহার বর্তমান ঠিকানা:'; ?> <?= $is_en ? 'Village:' : 'গ্রাম:'; ?> <strong><?= htmlspecialchars($village ?: '-'); ?></strong>, <?= $is_en ? 'Ward No:' : 'ওয়ার্ড নং:'; ?> <strong><?= !empty($clean_ward) ? ($is_en ? htmlspecialchars($clean_ward) : BanglaConverter::en2bn($clean_ward)) : '-'; ?></strong>, <?= $is_en ? 'Union:' : 'ইউনিয়ন:'; ?> <strong><?= htmlspecialchars($is_en ? $up_en_name : $up_name); ?></strong>, <?= $is_en ? 'Upazila:' : 'উপজেলা:'; ?> <strong><?= htmlspecialchars($is_en ? $upazila_en : $upazila_bn); ?></strong>, <?= $is_en ? 'District:' : 'জেলা:'; ?> <strong><?= htmlspecialchars($is_en ? $dist_en : $dist_bn); ?></strong><?= $is_en ? ' and ' : ' এবং '; ?><?= $is_en ? 'Permanent address is' : 'স্থায়ী ঠিকানা:'; ?> <?= $is_en ? 'Village:' : 'গ্রাম:'; ?> <strong><?= htmlspecialchars($village ?: '-'); ?></strong>, <?= $is_en ? 'Ward No:' : 'ওয়ার্ড নং:'; ?> <strong><?= !empty($clean_ward) ? ($is_en ? htmlspecialchars($clean_ward) : BanglaConverter::en2bn($clean_ward)) : '-'; ?></strong>, <?= $is_en ? 'Union:' : 'ইউনিয়ন:'; ?> <strong><?= htmlspecialchars($is_en ? $up_en_name : $up_name); ?></strong>, <?= $is_en ? 'Upazila:' : 'উপজেলা:'; ?> <strong><?= htmlspecialchars($is_en ? $upazila_en : $upazila_bn); ?></strong>, <?= $is_en ? 'District:' : 'জেলা:'; ?> <strong><?= htmlspecialchars($is_en ? $dist_en : $dist_bn); ?></strong>।
            </div>
            <div class="declaration-text">
                <?= $body_html; ?>
            </div>

        <?php elseif ($selected_text_layout === 'layout_modern_cards'): ?>
            <!-- 2. Modern Shaded Cards Layout -->
            <div class="intro-text" style="margin-bottom: 6px;">
                <?= $is_en ? 'Citizen Profile & Verification:' : 'নাগরিক প্রোফাইল ও পরিচিতি বিবরণ:'; ?>
            </div>
            <div style="background-color: #f8fafc; border: 1px solid #cbd5e1; border-radius: 4px; padding: 7px 10px; margin-bottom: 8px;">
                <table class="info-table" style="margin-bottom: 0;">
                    <tr>
                        <td class="lbl" style="width: 20%;"><?= $is_en ? 'Citizen Name' : 'নাগরিকের নাম'; ?></td>
                        <td class="val" style="width: 30%;">: <?= htmlspecialchars($citizen_name ?: '-'); ?></td>
                        <td class="lbl" style="width: 22%;"><?= $is_en ? 'Father/Husband' : 'পিতা/স্বামীর নাম'; ?></td>
                        <td class="val" style="width: 28%;">: <?= htmlspecialchars($father_name ?: '-'); ?></td>
                    </tr>
                    <tr>
                        <td class="lbl"><?= $is_en ? 'Mother\'s Name' : 'মাতার নাম'; ?></td>
                        <td class="val">: <?= htmlspecialchars($mother_name ?: '-'); ?></td>
                        <td class="lbl"><?= $is_en ? 'National ID / BRN' : 'ন্যাশনাল আইডি / জন্ম নিবন্ধন'; ?></td>
                        <td class="val">: <?= !empty($nid_no) ? ($is_en ? htmlspecialchars($nid_no) : BanglaConverter::en2bn($nid_no)) : '-'; ?></td>
                    </tr>
                </table>
            </div>
            <div style="background-color: #f8fafc; border: 1px solid #cbd5e1; border-radius: 4px; padding: 7px 10px; margin-bottom: 12px;">
                <table class="address-table" style="margin-bottom: 0;">
                    <tr>
                        <td style="width: 50%; padding-right: 10px;">
                            <div class="address-title" style="color: #065f46;"><?= $is_en ? 'Present Address:' : 'বর্তমান ঠিকানা:'; ?></div>
                            <div>
                                <?= $is_en ? 'Holding No:' : 'হোল্ডিং নং:'; ?> <strong><?= !empty($holding_no) ? ($is_en ? htmlspecialchars($holding_no) : BanglaConverter::en2bn($holding_no)) : '-'; ?></strong>, 
                                <?= $is_en ? 'Village:' : 'গ্রাম:'; ?> <strong><?= htmlspecialchars($village ?: '-'); ?></strong><br>
                                <?= $is_en ? 'Ward No:' : 'ওয়ার্ড নং:'; ?> <strong><?= !empty($clean_ward) ? ($is_en ? htmlspecialchars($clean_ward) : BanglaConverter::en2bn($clean_ward)) : '-'; ?></strong>, 
                                <?= $is_en ? 'Union:' : 'ইউনিয়ন:'; ?> <strong><?= htmlspecialchars($is_en ? $up_en_name : $up_name); ?></strong><br>
                                <?= $is_en ? 'Upazila:' : 'উপজেলা:'; ?> <strong><?= htmlspecialchars($is_en ? $upazila_en : $upazila_bn); ?></strong>, 
                                <?= $is_en ? 'District:' : 'জেলা:'; ?> <strong><?= htmlspecialchars($is_en ? $dist_en : $dist_bn); ?></strong>
                            </div>
                        </td>
                        <td style="width: 50%; padding-left: 10px; border-left: 1px solid #e2e8f0;">
                            <div class="address-title" style="color: #065f46;"><?= $is_en ? 'Permanent Address:' : 'স্থায়ী ঠিকানা:'; ?></div>
                            <div>
                                <?= $is_en ? 'Holding No:' : 'হোল্ডিং নং:'; ?> <strong><?= !empty($holding_no) ? ($is_en ? htmlspecialchars($holding_no) : BanglaConverter::en2bn($holding_no)) : '-'; ?></strong>, 
                                <?= $is_en ? 'Village:' : 'গ্রাম:'; ?> <strong><?= htmlspecialchars($village ?: '-'); ?></strong><br>
                                <?= $is_en ? 'Ward No:' : 'ওয়ার্ড নং:'; ?> <strong><?= !empty($clean_ward) ? ($is_en ? htmlspecialchars($clean_ward) : BanglaConverter::en2bn($clean_ward)) : '-'; ?></strong>, 
                                <?= $is_en ? 'Union:' : 'ইউনিয়ন:'; ?> <strong><?= htmlspecialchars($is_en ? $up_en_name : $up_name); ?></strong><br>
                                <?= $is_en ? 'Upazila:' : 'উপজেলা:'; ?> <strong><?= htmlspecialchars($is_en ? $upazila_en : $upazila_bn); ?></strong>, 
                                <?= $is_en ? 'District:' : 'জেলা:'; ?> <strong><?= htmlspecialchars($is_en ? $dist_en : $dist_bn); ?></strong>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="declaration-text" style="background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 4px; padding: 10px 14px; text-indent: 0;">
                <?= $body_html; ?>
            </div>

        <?php elseif ($selected_text_layout === 'layout_bullet_secretariat'): ?>
            <!-- 3. Secretariat Numbered Points Layout -->
            <div class="intro-text" style="margin-bottom: 8px;">
                <?= $is_en ? 'Following particulars of the citizen are verified:' : 'নাগরিকের তথ্যাবলী ও প্রত্যয়ন বিবরণী ক্রমানুসারে প্রদত্ত হইলো:'; ?>
            </div>
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 15px; line-height: 1.7;">
                <tr>
                    <td style="width: 32px; font-weight: bold; color: #1e3a8a; vertical-align: top;"><?= $is_en ? '(1)' : '(১)'; ?></td>
                    <td style="width: 180px; font-weight: bold; color: #475569; vertical-align: top;"><?= $is_en ? 'Citizen Name' : 'নাগরিকের নাম'; ?></td>
                    <td style="vertical-align: top; font-weight: bold; color: #0f172a;">: <?= htmlspecialchars($citizen_name ?: '-'); ?></td>
                </tr>
                <tr>
                    <td style="font-weight: bold; color: #1e3a8a; vertical-align: top;"><?= $is_en ? '(2)' : '(২)'; ?></td>
                    <td style="font-weight: bold; color: #475569; vertical-align: top;"><?= $is_en ? 'Father/Husband\'s Name' : 'পিতা/স্বামীর নাম'; ?></td>
                    <td style="vertical-align: top; font-weight: bold; color: #0f172a;">: <?= htmlspecialchars($father_name ?: '-'); ?></td>
                </tr>
                <tr>
                    <td style="font-weight: bold; color: #1e3a8a; vertical-align: top;"><?= $is_en ? '(3)' : '(৩)'; ?></td>
                    <td style="font-weight: bold; color: #475569; vertical-align: top;"><?= $is_en ? 'Mother\'s Name' : 'মাতার নাম'; ?></td>
                    <td style="vertical-align: top; font-weight: bold; color: #0f172a;">: <?= htmlspecialchars($mother_name ?: '-'); ?></td>
                </tr>
                <tr>
                    <td style="font-weight: bold; color: #1e3a8a; vertical-align: top;"><?= $is_en ? '(4)' : '(৪)'; ?></td>
                    <td style="font-weight: bold; color: #475569; vertical-align: top;"><?= $is_en ? 'National ID / BRN' : 'জাতীয় পরিচয়পত্র / জন্ম নিবন্ধন'; ?></td>
                    <td style="vertical-align: top; font-weight: bold; color: #0f172a;">: <?= !empty($nid_no) ? ($is_en ? htmlspecialchars($nid_no) : BanglaConverter::en2bn($nid_no)) : '-'; ?></td>
                </tr>
                <tr>
                    <td style="font-weight: bold; color: #1e3a8a; vertical-align: top;"><?= $is_en ? '(5)' : '(৫)'; ?></td>
                    <td style="font-weight: bold; color: #475569; vertical-align: top;"><?= $is_en ? 'Present Address' : 'বর্তমান ঠিকানা'; ?></td>
                    <td style="vertical-align: top;">: <?= $is_en ? 'Village:' : 'গ্রাম:'; ?> <strong><?= htmlspecialchars($village ?: '-'); ?></strong>, <?= $is_en ? 'Ward No:' : 'ওয়ার্ড নং:'; ?> <strong><?= !empty($clean_ward) ? ($is_en ? htmlspecialchars($clean_ward) : BanglaConverter::en2bn($clean_ward)) : '-'; ?></strong>, <?= $is_en ? 'Upazila:' : 'উপজেলা:'; ?> <strong><?= htmlspecialchars($is_en ? $upazila_en : $upazila_bn); ?></strong>, <?= $is_en ? 'District:' : 'জেলা:'; ?> <strong><?= htmlspecialchars($is_en ? $dist_en : $dist_bn); ?></strong></td>
                </tr>
                <tr>
                    <td style="font-weight: bold; color: #1e3a8a; vertical-align: top;"><?= $is_en ? '(6)' : '(৬)'; ?></td>
                    <td style="font-weight: bold; color: #475569; vertical-align: top;"><?= $is_en ? 'Permanent Address' : 'স্থায়ী ঠিকানা'; ?></td>
                    <td style="vertical-align: top;">: <?= $is_en ? 'Village:' : 'গ্রাম:'; ?> <strong><?= htmlspecialchars($village ?: '-'); ?></strong>, <?= $is_en ? 'Ward No:' : 'ওয়ার্ড নং:'; ?> <strong><?= !empty($clean_ward) ? ($is_en ? htmlspecialchars($clean_ward) : BanglaConverter::en2bn($clean_ward)) : '-'; ?></strong>, <?= $is_en ? 'Upazila:' : 'উপজেলা:'; ?> <strong><?= htmlspecialchars($is_en ? $upazila_en : $upazila_bn); ?></strong>, <?= $is_en ? 'District:' : 'জেলা:'; ?> <strong><?= htmlspecialchars($is_en ? $dist_en : $dist_bn); ?></strong></td>
                </tr>
                <tr>
                    <td style="font-weight: bold; color: #1e3a8a; vertical-align: top;"><?= $is_en ? '(7)' : '(৭)'; ?></td>
                    <td style="font-weight: bold; color: #475569; vertical-align: top;"><?= $is_en ? 'Certification' : 'প্রত্যয়ন ও বয়ান'; ?></td>
                    <td style="vertical-align: top; text-align: justify; line-height: 1.85;">: <?= $body_html; ?></td>
                </tr>
            </table>

        <?php elseif ($selected_text_layout === 'layout_bordered_table'): ?>
            <!-- 4. Official Bordered Grid Table Layout -->
            <div class="intro-text">
                <?= $is_en ? 'Official Citizen Record Table:' : 'অফিসিয়াল নাগরিক তথ্য ও বিবরণী ছক:'; ?>
            </div>
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 14px; font-size: 14.5px;">
                <tr style="background-color: #f1f5f9;">
                    <td style="border: 1px solid #94a3b8; padding: 6px 10px; width: 22%; font-weight: bold; color: #1e3a8a;"><?= $is_en ? 'Citizen Name' : 'নাগরিকের নাম'; ?></td>
                    <td style="border: 1px solid #94a3b8; padding: 6px 10px; width: 28%; font-weight: bold; color: #0f172a;"><?= htmlspecialchars($citizen_name ?: '-'); ?></td>
                    <td style="border: 1px solid #94a3b8; padding: 6px 10px; width: 22%; font-weight: bold; color: #1e3a8a;"><?= $is_en ? 'Father/Husband' : 'পিতা/স্বামীর নাম'; ?></td>
                    <td style="border: 1px solid #94a3b8; padding: 6px 10px; width: 28%; font-weight: bold; color: #0f172a;"><?= htmlspecialchars($father_name ?: '-'); ?></td>
                </tr>
                <tr>
                    <td style="border: 1px solid #94a3b8; padding: 6px 10px; font-weight: bold; color: #1e3a8a;"><?= $is_en ? 'Mother\'s Name' : 'মাতার নাম'; ?></td>
                    <td style="border: 1px solid #94a3b8; padding: 6px 10px; font-weight: bold; color: #0f172a;"><?= htmlspecialchars($mother_name ?: '-'); ?></td>
                    <td style="border: 1px solid #94a3b8; padding: 6px 10px; font-weight: bold; color: #1e3a8a;"><?= $is_en ? 'National ID / BRN' : 'এনআইডি / জন্ম নিবন্ধন'; ?></td>
                    <td style="border: 1px solid #94a3b8; padding: 6px 10px; font-weight: bold; color: #0f172a;"><?= !empty($nid_no) ? ($is_en ? htmlspecialchars($nid_no) : BanglaConverter::en2bn($nid_no)) : '-'; ?></td>
                </tr>
                <tr style="background-color: #f8fafc;">
                    <td style="border: 1px solid #94a3b8; padding: 6px 10px; font-weight: bold; color: #1e3a8a;"><?= $is_en ? 'Present Address' : 'বর্তমান ঠিকানা'; ?></td>
                    <td colspan="3" style="border: 1px solid #94a3b8; padding: 6px 10px;"><?= $is_en ? 'Village:' : 'গ্রাম:'; ?> <strong><?= htmlspecialchars($village ?: '-'); ?></strong>, <?= $is_en ? 'Ward No:' : 'ওয়ার্ড নং:'; ?> <strong><?= !empty($clean_ward) ? ($is_en ? htmlspecialchars($clean_ward) : BanglaConverter::en2bn($clean_ward)) : '-'; ?></strong>, <?= $is_en ? 'Upazila:' : 'উপজেলা:'; ?> <strong><?= htmlspecialchars($is_en ? $upazila_en : $upazila_bn); ?></strong>, <?= $is_en ? 'District:' : 'জেলা:'; ?> <strong><?= htmlspecialchars($is_en ? $dist_en : $dist_bn); ?></strong></td>
                </tr>
                <tr>
                    <td style="border: 1px solid #94a3b8; padding: 6px 10px; font-weight: bold; color: #1e3a8a;"><?= $is_en ? 'Permanent Address' : 'স্থায়ী ঠিকানা'; ?></td>
                    <td colspan="3" style="border: 1px solid #94a3b8; padding: 6px 10px;"><?= $is_en ? 'Village:' : 'গ্রাম:'; ?> <strong><?= htmlspecialchars($village ?: '-'); ?></strong>, <?= $is_en ? 'Ward No:' : 'ওয়ার্ড নং:'; ?> <strong><?= !empty($clean_ward) ? ($is_en ? htmlspecialchars($clean_ward) : BanglaConverter::en2bn($clean_ward)) : '-'; ?></strong>, <?= $is_en ? 'Upazila:' : 'উপজেলা:'; ?> <strong><?= htmlspecialchars($is_en ? $upazila_en : $upazila_bn); ?></strong>, <?= $is_en ? 'District:' : 'জেলা:'; ?> <strong><?= htmlspecialchars($is_en ? $dist_en : $dist_bn); ?></strong></td>
                </tr>
            </table>
            <div class="declaration-text">
                <?= $body_html; ?>
            </div>

        <?php elseif ($selected_text_layout === 'layout_centered_royal'): ?>
            <!-- 5. Centered Royal Typography Layout -->
            <div style="text-align: center; font-size: 15px; color: #475569; margin-bottom: 2px;">
                <?= $is_en ? 'This is solemnly certified that' : 'এই মর্মে অত্যন্ত আনন্দের সাথে প্রত্যয়ন করা যাইতেছে যে,'; ?>
            </div>
            <div style="text-align: center; font-size: 21px; font-weight: bold; color: #1e3a8a; margin: 4px 0 6px 0; letter-spacing: 0.5px;">
                <?= htmlspecialchars($citizen_name ?: '-'); ?>
            </div>
            <div style="text-align: center; font-size: 14.5px; color: #334155; margin-bottom: 8px;">
                <?= $is_en ? 'Father/Husband:' : 'পিতা/স্বামী:'; ?> <strong><?= htmlspecialchars($father_name ?: '-'); ?></strong> &nbsp;|&nbsp; 
                <?= $is_en ? 'Mother:' : 'মাতা:'; ?> <strong><?= htmlspecialchars($mother_name ?: '-'); ?></strong>
                <?php if (!empty($nid_no)): ?>
                    &nbsp;|&nbsp; <?= $is_en ? 'NID/BRN:' : 'এনআইডি:'; ?> <strong><?= $is_en ? htmlspecialchars($nid_no) : BanglaConverter::en2bn($nid_no); ?></strong>
                <?php endif; ?>
            </div>
            <div style="text-align: center; font-size: 14px; color: #475569; border-top: 1px solid #cbd5e1; border-bottom: 1px solid #cbd5e1; padding: 5px 0; margin-bottom: 16px;">
                <?= $is_en ? 'Village:' : 'গ্রাম:'; ?> <strong><?= htmlspecialchars($village ?: '-'); ?></strong>, <?= $is_en ? 'Ward No:' : 'ওয়ার্ড নং:'; ?> <strong><?= !empty($clean_ward) ? ($is_en ? htmlspecialchars($clean_ward) : BanglaConverter::en2bn($clean_ward)) : '-'; ?></strong>, <?= $is_en ? 'Upazila:' : 'উপজেলা:'; ?> <strong><?= htmlspecialchars($is_en ? $upazila_en : $upazila_bn); ?></strong>, <?= $is_en ? 'District:' : 'জেলা:'; ?> <strong><?= htmlspecialchars($is_en ? $dist_en : $dist_bn); ?></strong>
            </div>
            <div class="declaration-text" style="text-align: center; text-indent: 0; line-height: 2.05;">
                <?= $body_html; ?>
            </div>

        <?php elseif ($selected_text_layout === 'layout_legal_banner'): ?>
            <!-- 6. Legal Accent Banner Layout -->
            <div style="background-color: #f0fdf4; border: 1px solid #86efac; border-left: 6px solid #065f46; border-radius: 4px; padding: 9px 14px; margin-bottom: 14px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="font-size: 16.5px; font-weight: bold; color: #065f46;">
                            <?= htmlspecialchars($citizen_name ?: '-'); ?>
                        </td>
                        <td style="text-align: right; font-size: 13.5px; color: #1e3a8a; font-weight: bold;">
                            <?= !empty($nid_no) ? (($is_en ? 'ID: ' : 'পরিচয়পত্র নং: ') . ($is_en ? htmlspecialchars($nid_no) : BanglaConverter::en2bn($nid_no))) : ''; ?>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" style="font-size: 14px; color: #334155; padding-top: 3px;">
                            <?= $is_en ? 'Father/Husband:' : 'পিতা/স্বামী:'; ?> <strong><?= htmlspecialchars($father_name ?: '-'); ?></strong> &nbsp;•&nbsp; 
                            <?= $is_en ? 'Mother:' : 'মাতা:'; ?> <strong><?= htmlspecialchars($mother_name ?: '-'); ?></strong>
                        </td>
                    </tr>
                </table>
            </div>
            <table class="address-table" style="margin-bottom: 14px;">
                <tr>
                    <td style="width: 50%; padding-right: 15px;">
                        <div class="address-title"><?= $is_en ? 'Present Address:' : 'বর্তমান ঠিকানা:'; ?></div>
                        <div>
                            <?= !empty($holding_no) ? (($is_en ? 'Holding No: ' : 'হোল্ডিং নং: ') . '<strong>' . ($is_en ? htmlspecialchars($holding_no) : BanglaConverter::en2bn($holding_no)) . '</strong>, ') : ''; ?>
                            <?= $is_en ? 'Village:' : 'গ্রাম:'; ?> <strong><?= htmlspecialchars($village ?: '-'); ?></strong>, 
                            <?= $is_en ? 'Ward No:' : 'ওয়ার্ড নং:'; ?> <strong><?= !empty($clean_ward) ? ($is_en ? htmlspecialchars($clean_ward) : BanglaConverter::en2bn($clean_ward)) : '-'; ?></strong><br>
                            <?= $is_en ? 'Union:' : 'ইউনিয়ন:'; ?> <strong><?= htmlspecialchars($is_en ? $up_en_name : $up_name); ?></strong>, 
                            <?= $is_en ? 'Upazila:' : 'উপজেলা:'; ?> <strong><?= htmlspecialchars($is_en ? $upazila_en : $upazila_bn); ?></strong>, 
                            <?= $is_en ? 'District:' : 'জেলা:'; ?> <strong><?= htmlspecialchars($is_en ? $dist_en : $dist_bn); ?></strong>
                        </div>
                    </td>
                    <td style="width: 50%; padding-left: 15px;">
                        <div class="address-title"><?= $is_en ? 'Permanent Address:' : 'স্থায়ী ঠিকানা:'; ?></div>
                        <div>
                            <?= !empty($holding_no) ? (($is_en ? 'Holding No: ' : 'হোল্ডিং নং: ') . '<strong>' . ($is_en ? htmlspecialchars($holding_no) : BanglaConverter::en2bn($holding_no)) . '</strong>, ') : ''; ?>
                            <?= $is_en ? 'Village:' : 'গ্রাম:'; ?> <strong><?= htmlspecialchars($village ?: '-'); ?></strong>, 
                            <?= $is_en ? 'Ward No:' : 'ওয়ার্ড নং:'; ?> <strong><?= !empty($clean_ward) ? ($is_en ? htmlspecialchars($clean_ward) : BanglaConverter::en2bn($clean_ward)) : '-'; ?></strong><br>
                            <?= $is_en ? 'Union:' : 'ইউনিয়ন:'; ?> <strong><?= htmlspecialchars($is_en ? $up_en_name : $up_name); ?></strong>, 
                            <?= $is_en ? 'Upazila:' : 'উপজেলা:'; ?> <strong><?= htmlspecialchars($is_en ? $upazila_en : $upazila_bn); ?></strong>, 
                            <?= $is_en ? 'District:' : 'জেলা:'; ?> <strong><?= htmlspecialchars($is_en ? $dist_en : $dist_bn); ?></strong>
                        </div>
                    </td>
                </tr>
            </table>
            <div class="declaration-text" style="background-color: #fafaf9; border-left: 4px solid #1e3a8a; padding: 10px 14px; border-radius: 2px;">
                <?= $body_html; ?>
            </div>

        <?php elseif ($selected_text_layout === 'layout_minimal_dots'): ?>
            <!-- 7. Minimal Deed Dotted Fill-in Layout -->
            <div class="intro-text">
                <?= $is_en ? 'Deed & Record of Certification:' : 'এই মর্মে প্রত্যয়ন / ঘোষণাপত্র প্রদান করা যাইতেছে যে,'; ?>
            </div>
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 14px; font-size: 15px;">
                <tr>
                    <td style="width: 20%; padding: 6px 0; font-weight: bold; color: #475569;"><?= $is_en ? 'Citizen Name' : 'নাগরিকের নাম'; ?></td>
                    <td style="width: 30%; padding: 6px 0;"><span style="border-bottom: 1px dotted #475569; padding-bottom: 1px; font-weight: bold; color: #0f172a;"><?= htmlspecialchars($citizen_name ?: '-'); ?></span></td>
                    <td style="width: 22%; padding: 6px 0; font-weight: bold; color: #475569;"><?= $is_en ? 'Father/Husband' : 'পিতা/স্বামীর নাম'; ?></td>
                    <td style="width: 28%; padding: 6px 0;"><span style="border-bottom: 1px dotted #475569; padding-bottom: 1px; font-weight: bold; color: #0f172a;"><?= htmlspecialchars($father_name ?: '-'); ?></span></td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; font-weight: bold; color: #475569;"><?= $is_en ? 'Mother\'s Name' : 'মাতার নাম'; ?></td>
                    <td style="padding: 6px 0;"><span style="border-bottom: 1px dotted #475569; padding-bottom: 1px; font-weight: bold; color: #0f172a;"><?= htmlspecialchars($mother_name ?: '-'); ?></span></td>
                    <td style="padding: 6px 0; font-weight: bold; color: #475569;"><?= $is_en ? 'National ID / BRN' : 'এনআইডি / জন্ম নিবন্ধন'; ?></td>
                    <td style="padding: 6px 0;"><span style="border-bottom: 1px dotted #475569; padding-bottom: 1px; font-weight: bold; color: #0f172a;"><?= !empty($nid_no) ? ($is_en ? htmlspecialchars($nid_no) : BanglaConverter::en2bn($nid_no)) : '-'; ?></span></td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; font-weight: bold; color: #475569;"><?= $is_en ? 'Present Address' : 'বর্তমান ঠিকানা'; ?></td>
                    <td colspan="3" style="padding: 6px 0;"><span style="border-bottom: 1px dotted #475569; padding-bottom: 1px;"><?= $is_en ? 'Village:' : 'গ্রাম:'; ?> <strong><?= htmlspecialchars($village ?: '-'); ?></strong>, <?= $is_en ? 'Ward No:' : 'ওয়ার্ড নং:'; ?> <strong><?= !empty($clean_ward) ? ($is_en ? htmlspecialchars($clean_ward) : BanglaConverter::en2bn($clean_ward)) : '-'; ?></strong>, <?= $is_en ? 'Upazila:' : 'উপজেলা:'; ?> <strong><?= htmlspecialchars($is_en ? $upazila_en : $upazila_bn); ?></strong>, <?= $is_en ? 'District:' : 'জেলা:'; ?> <strong><?= htmlspecialchars($is_en ? $dist_en : $dist_bn); ?></strong></span></td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; font-weight: bold; color: #475569;"><?= $is_en ? 'Permanent Address' : 'স্থায়ী ঠিকানা'; ?></td>
                    <td colspan="3" style="padding: 6px 0;"><span style="border-bottom: 1px dotted #475569; padding-bottom: 1px;"><?= $is_en ? 'Village:' : 'গ্রাম:'; ?> <strong><?= htmlspecialchars($village ?: '-'); ?></strong>, <?= $is_en ? 'Ward No:' : 'ওয়ার্ড নং:'; ?> <strong><?= !empty($clean_ward) ? ($is_en ? htmlspecialchars($clean_ward) : BanglaConverter::en2bn($clean_ward)) : '-'; ?></strong>, <?= $is_en ? 'Upazila:' : 'উপজেলা:'; ?> <strong><?= htmlspecialchars($is_en ? $upazila_en : $upazila_bn); ?></strong>, <?= $is_en ? 'District:' : 'জেলা:'; ?> <strong><?= htmlspecialchars($is_en ? $dist_en : $dist_bn); ?></strong></span></td>
                </tr>
            </table>
            <div class="declaration-text">
                <?= $body_html; ?>
            </div>

        <?php else: ?>
            <!-- 8. Classic Grid (Default) -->
            <div class="intro-text">
                <?= $is_en ? 'This is to certify that:' : 'এই মর্মে প্রত্যয়ন / সনদপত্র প্রদান করা যাইতেছে যে,'; ?>
            </div>
            <table class="info-table" style="margin-bottom: 14px;">
                <tr>
                    <td class="lbl" style="width: 20%;"><?= $is_en ? 'Citizen Name' : 'নাগরিকের নাম'; ?></td>
                    <td class="val" style="width: 30%;">: <?= htmlspecialchars($citizen_name ?: '-'); ?></td>
                    <td class="lbl" style="width: 22%;"><?= $is_en ? 'Father/Husband' : 'পিতা/স্বামীর নাম'; ?></td>
                    <td class="val" style="width: 28%;">: <?= htmlspecialchars($father_name ?: '-'); ?></td>
                </tr>
                <tr>
                    <td class="lbl"><?= $is_en ? 'Mother\'s Name' : 'মাতার নাম'; ?></td>
                    <td class="val">: <?= htmlspecialchars($mother_name ?: '-'); ?></td>
                    <td class="lbl"><?= $is_en ? 'National ID / BRN' : 'ন্যাশনাল আইডি / জন্ম নিবন্ধন'; ?></td>
                    <td class="val">: <?= !empty($nid_no) ? ($is_en ? htmlspecialchars($nid_no) : BanglaConverter::en2bn($nid_no)) : '-'; ?></td>
                </tr>
            </table>
            <table class="address-table">
                <tr>
                    <td style="width: 50%; padding-right: 15px;">
                        <div class="address-title"><?= $is_en ? 'Present Address:' : 'বর্তমান ঠিকানা:'; ?></div>
                        <div>
                            <?= $is_en ? 'Holding No:' : 'হোল্ডিং নং:'; ?> <strong><?= !empty($holding_no) ? ($is_en ? htmlspecialchars($holding_no) : BanglaConverter::en2bn($holding_no)) : '-'; ?></strong>, 
                            <?= $is_en ? 'Village:' : 'গ্রাম:'; ?> <strong><?= htmlspecialchars($village ?: '-'); ?></strong><br>
                            <?= $is_en ? 'Ward No:' : 'ওয়ার্ড নং:'; ?> <strong><?= !empty($clean_ward) ? ($is_en ? htmlspecialchars($clean_ward) : BanglaConverter::en2bn($clean_ward)) : '-'; ?></strong>, 
                            <?= $is_en ? 'Union:' : 'ইউনিয়ন:'; ?> <strong><?= htmlspecialchars($is_en ? $up_en_name : $up_name); ?></strong><br>
                            <?= $is_en ? 'Upazila:' : 'উপজেলা:'; ?> <strong><?= htmlspecialchars($is_en ? $upazila_en : $upazila_bn); ?></strong>, 
                            <?= $is_en ? 'District:' : 'জেলা:'; ?> <strong><?= htmlspecialchars($is_en ? $dist_en : $dist_bn); ?></strong>
                        </div>
                    </td>
                    <td style="width: 50%; padding-left: 15px;">
                        <div class="address-title"><?= $is_en ? 'Permanent Address:' : 'স্থায়ী ঠিকানা:'; ?></div>
                        <div>
                            <?= $is_en ? 'Holding No:' : 'হোল্ডিং নং:'; ?> <strong><?= !empty($holding_no) ? ($is_en ? htmlspecialchars($holding_no) : BanglaConverter::en2bn($holding_no)) : '-'; ?></strong>, 
                            <?= $is_en ? 'Village:' : 'গ্রাম:'; ?> <strong><?= htmlspecialchars($village ?: '-'); ?></strong><br>
                            <?= $is_en ? 'Ward No:' : 'ওয়ার্ড নং:'; ?> <strong><?= !empty($clean_ward) ? ($is_en ? htmlspecialchars($clean_ward) : BanglaConverter::en2bn($clean_ward)) : '-'; ?></strong>, 
                            <?= $is_en ? 'Union:' : 'ইউনিয়ন:'; ?> <strong><?= htmlspecialchars($is_en ? $up_en_name : $up_name); ?></strong><br>
                            <?= $is_en ? 'Upazila:' : 'উপজেলা:'; ?> <strong><?= htmlspecialchars($is_en ? $upazila_en : $upazila_bn); ?></strong>, 
                            <?= $is_en ? 'District:' : 'জেলা:'; ?> <strong><?= htmlspecialchars($is_en ? $dist_en : $dist_bn); ?></strong>
                        </div>
                    </td>
                </tr>
            </table>
            <div class="declaration-text">
                <?= $body_html; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- 4. Dynamic Signatures Block (3-Person vs 2-Person vs 1-Person) -->
    <table class="signatures-table">
        <!-- Row 1: Signature Slots and Clean Line -->
        <tr>
            <?php if (!$is_2_person_mode && !$is_1_person_mode): ?>
                <td style="width: 33.33%; vertical-align: bottom; height: 50px; padding: 0 10px;">
                    <div class="sign-slot" style="height: 46px; line-height: 46px; vertical-align: bottom;">&nbsp;</div>
                    <div class="sign-line-rule"></div>
                </td>
            <?php endif; ?>

            <?php if (!$is_1_person_mode): ?>
            <td style="<?= ($is_2_person_mode) ? 'width: 50%;' : 'width: 33.33%;'; ?> vertical-align: bottom; height: <?= $is_trade ? '36px' : '50px'; ?>; padding: 0 10px;">
                <div class="sign-slot" style="height: <?= $is_trade ? '34px' : '46px'; ?>; line-height: <?= $is_trade ? '34px' : '46px'; ?>; vertical-align: bottom;">
                    <?php if (!empty($sec_sign_path)): ?>
                        <img src="<?= $sec_sign_path; ?>" height="<?= $is_trade ? '32' : '42'; ?>" style="max-height: <?= $is_trade ? '32px' : '42px'; ?>; vertical-align: bottom;">
                    <?php else: ?>
                        &nbsp;
                    <?php endif; ?>
                </div>
                <div class="sign-line-rule"></div>
            </td>
            <?php endif; ?>

            <td style="<?= ($is_1_person_mode) ? 'width: 100%; text-align: center;' : (($is_2_person_mode) ? 'width: 50%;' : 'width: 33.33%;'); ?> vertical-align: bottom; height: <?= $is_trade ? '36px' : '50px'; ?>; padding: 0 10px;">
                <div class="sign-slot" style="height: <?= $is_trade ? '34px' : '46px'; ?>; line-height: <?= $is_trade ? '34px' : '46px'; ?>; vertical-align: bottom;">
                    <?php if (!empty($chairman_sign_path)): ?>
                        <img src="<?= $chairman_sign_path; ?>" height="<?= $is_trade ? '32' : '42'; ?>" style="max-height: <?= $is_trade ? '32px' : '42px'; ?>; vertical-align: bottom;">
                    <?php else: ?>
                        &nbsp;
                    <?php endif; ?>
                </div>
                <div class="sign-line-rule"></div>
            </td>
        </tr>

        <!-- Row 2: Signatory Names & Designations -->
        <tr>
            <?php if (!$is_2_person_mode && !$is_1_person_mode): ?>
                <td style="vertical-align: top; padding-top: <?= $is_trade ? '3px' : '6px'; ?>; padding-left: 10px; padding-right: 10px;">
                    <div class="sign-name">
                        <?= !empty($clean_ward) ? ($is_en ? ('Ward Member, Ward No: ' . htmlspecialchars($clean_ward)) : (BanglaConverter::en2bn($clean_ward) . ' নং ওয়ার্ড সদস্য')) : ($is_en ? 'Ward Member' : 'সংশ্লিষ্ট ওয়ার্ড সদস্য'); ?>
                    </div>
                    <div class="sign-loc"><?= htmlspecialchars($is_en ? $up_en_name : $up_name); ?></div>
                </td>
            <?php endif; ?>

            <?php if (!$is_1_person_mode): ?>
            <td style="vertical-align: top; padding-top: <?= $is_trade ? '3px' : '6px'; ?>; padding-left: 10px; padding-right: 10px;">
                <div class="sign-name"><?= $is_en ? 'Administrative Officer' : 'প্রশাসনিক কর্মকর্তা'; ?></div>
                <div class="sign-loc"><?= htmlspecialchars($is_en ? $up_en_name : $up_name); ?></div>
            </td>
            <?php endif; ?>

            <td style="vertical-align: top; padding-top: <?= $is_trade ? '3px' : '6px'; ?>; padding-left: 10px; padding-right: 10px; <?= $is_1_person_mode ? 'text-align: center;' : ''; ?>">
                <div class="sign-name"><?= $is_en ? 'Chairman' : 'চেয়ারম্যান'; ?></div>
                <div class="sign-loc"><?= htmlspecialchars($is_en ? $up_en_name : $up_name); ?></div>
            </td>
        </tr>
    </table>

</body>
</html>