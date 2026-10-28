<?php
    defined('BASEPATH') OR exit('No direct script access allowed');

    // Generate QR Code if uniqID is available
    $qr_base64 = '';
    if (!empty($invoices->uniqID)) {
        try {
            $string_for_barcode = 'Holding: ' . ($invoices->holding_number ?? '') . ', Name: ' . ($invoices->family_person_name_head ?? '') . ', View: ' . base_url('pub_view/' . $invoices->uniqID);
            $barcodeobj = new TCPDF2DBarcode($string_for_barcode, 'QRCODE,L');
            $qr_base64 = $barcodeobj->getBarcodePNG(3, 3, array(0, 0, 0));
        } catch (\Throwable $e) {
            $qr_base64 = '';
        }
    }
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ট্যাক্স রসিদ স্লিপ #<?= htmlspecialchars($invoices->invoice_no_s ?? '', ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="icon" type="image/x-icon" href="<?= media_url($setting_info->ins_logo_path ?? '', 'image/png'); ?>">
    <link rel="stylesheet" href="<?= base_url('inc/assets/vendor/fonts/adorsho-lipi.css'); ?>">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'AdarshaLipi', 'AdorshoLipi', 'SolaimanLipi', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: #0f172a;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        @media screen {
            body {
                background: #0f172a;
                padding: 24px 16px 48px;
                min-height: 100vh;
                display: flex;
                flex-direction: column;
                align-items: center;
            }
            .screen-actions {
                width: 320px;
                max-width: 100%;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 8px;
                margin-bottom: 16px;
            }
            .btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 6px;
                padding: 8px 12px;
                border-radius: 8px;
                font-size: 12px;
                font-weight: 600;
                text-decoration: none;
                cursor: pointer;
                border: none;
                transition: all 0.15s ease;
            }
            .btn-primary {
                background: #4f46e5;
                color: #ffffff;
            }
            .btn-primary:hover {
                background: #4338ca;
            }
            .btn-secondary {
                background: #334155;
                color: #f1f5f9;
            }
            .btn-secondary:hover {
                background: #475569;
            }
            .btn-close {
                background: #dc2626;
                color: #ffffff;
            }
            .btn-close:hover {
                background: #b91c1c;
            }
            .slip-card {
                width: 320px;
                max-width: 100%;
                background: #ffffff;
                border-radius: 12px;
                padding: 18px 16px;
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.4), 0 8px 10px -6px rgba(0, 0, 0, 0.4);
            }
        }
        @media print {
            @page {
                size: 80mm auto;
                margin: 2mm 3mm;
            }
            body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .slip-card {
                width: 100% !important;
                max-width: 76mm !important;
                margin: 0 auto !important;
                padding: 0 !important;
                box-shadow: none !important;
                border-radius: 0 !important;
            }
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        
        .header-logo {
            width: 48px;
            height: 48px;
            object-fit: contain;
            margin: 0 auto 6px;
            display: block;
        }
        .header-title {
            font-size: 15px;
            font-weight: 800;
            line-height: 1.25;
            color: #000;
        }
        .header-sub {
            font-size: 11px;
            color: #334155;
            margin-top: 2px;
            line-height: 1.3;
        }
        .header-contact {
            font-size: 10.5px;
            color: #475569;
            margin-top: 2px;
        }

        .badge-wrap {
            margin: 10px 0 8px;
            text-align: center;
        }
        .badge {
            display: inline-block;
            background: #000;
            color: #fff;
            padding: 3px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .sub-badge {
            font-size: 9.5px;
            color: #64748b;
            margin-top: 3px;
        }

        .divider {
            border-top: 1px dashed #64748b;
            margin: 8px 0;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            line-height: 1.45;
        }
        .info-table td {
            padding: 1.5px 0;
            vertical-align: top;
        }
        .info-label {
            color: #475569;
            width: 38%;
            white-space: nowrap;
        }
        .info-value {
            color: #0f172a;
            font-weight: 600;
            width: 62%;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin: 4px 0;
        }
        .data-table th {
            border-bottom: 1px solid #000;
            padding: 4px 0;
            font-weight: 700;
            color: #000;
        }
        .data-table td {
            padding: 4px 0;
            border-bottom: 1px dashed #cbd5e1;
            color: #0f172a;
        }
        .data-table tr.total-row td {
            border-top: 1px solid #000;
            border-bottom: 2px solid #000;
            padding: 6px 0;
            font-size: 12px;
            font-weight: 800;
            color: #000;
        }

        .qr-wrap {
            text-align: center;
            margin: 8px 0 4px;
        }
        .qr-img {
            width: 90px;
            height: 90px;
            display: block;
            margin: 0 auto;
        }
        .qr-text {
            font-size: 9.5px;
            color: #64748b;
            margin-top: 3px;
        }

        .footer-note {
            text-align: center;
            font-size: 10px;
            color: #475569;
            line-height: 1.4;
            margin-top: 8px;
        }
        .signature-line {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 18px;
            padding-top: 4px;
            font-size: 10px;
        }
        .sig-block {
            text-align: center;
            border-top: 1px dotted #94a3b8;
            padding-top: 3px;
            min-width: 90px;
        }
    </style>
</head>
<body>

    <!-- On-screen Action Bar (Hidden on print) -->
    <div class="screen-actions no-print">
        <button type="button" class="btn btn-primary" onclick="window.print();">
            <span>প্রিন্ট স্লিপ</span>
        </button>
        <a href="<?= base_url('recipt_print?uniqs=' . urlencode($invoices->invoice_no_s ?? '')); ?>" target="_blank" class="btn btn-secondary">
            <span>ফুল পেজ রসিদ</span>
        </a>
        <button type="button" class="btn btn-close" onclick="window.close();">
            <span>বন্ধ করুন</span>
        </button>
    </div>

    <!-- Thermal Slip Card -->
    <div class="slip-card">
        <!-- Union Header -->
        <div class="text-center">
            <?php if (!empty($setting_info->ins_logo_path)): ?>
                <img src="<?= media_url($setting_info->ins_logo_path, 'image/png'); ?>" alt="Logo" class="header-logo">
            <?php endif; ?>
            <div class="header-title"><?= htmlspecialchars($setting_info->ins_name ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
            <div class="header-sub"><?= htmlspecialchars($setting_info->addre_s_s ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
            <?php 
                $helpline = $setting_info->socib_phone ?: ($setting_info->chairman_phone ?? '');
                if (!empty($helpline)):
            ?>
                <div class="header-contact">যোগাযোগঃ <?= bn_number($helpline); ?></div>
            <?php endif; ?>
        </div>

        <div class="badge-wrap">
            <span class="badge">ট্যাক্স আদায়ের রশিদ</span>
            <div class="sub-badge">[ পিওএস / থার্মাল স্লিপ ]</div>
        </div>

        <div class="divider"></div>

        <!-- Receipt & Citizen Details -->
        <table class="info-table">
            <tr>
                <td class="info-label">রশিদ নম্বর</td>
                <td class="info-value">: #<?= bn_number($invoices->invoice_no_s ?? ''); ?></td>
            </tr>
            <tr>
                <td class="info-label">তারিখ ও সময়</td>
                <td class="info-value">: <?= bn_number(date('d-m-Y, h:i A', $invoices->time_s_s ?? time())); ?></td>
            </tr>
            <tr>
                <td class="info-label">করদাতার নাম</td>
                <td class="info-value">: <?= htmlspecialchars($invoices->family_person_name_head ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
            </tr>
            <?php if (!empty($invoices->pason_father_name)): ?>
            <tr>
                <td class="info-label">পিতা/স্বামী</td>
                <td class="info-value">: <?= htmlspecialchars($invoices->pason_father_name, ENT_QUOTES, 'UTF-8'); ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td class="info-label">হোল্ডিং নং</td>
                <td class="info-value">: <?= bn_number($invoices->holding_number ?? '—'); ?></td>
            </tr>
            <tr>
                <td class="info-label">ওয়ার্ড নং</td>
                <td class="info-value">: <?= bn_number($invoices->word_number ?? '—'); ?></td>
            </tr>
            <?php if (!empty($invoices->bn_vill_names)): ?>
            <tr>
                <td class="info-label">গ্রাম</td>
                <td class="info-value">: <?= htmlspecialchars($invoices->bn_vill_names, ENT_QUOTES, 'UTF-8'); ?></td>
            </tr>
            <?php endif; ?>
            <?php if (!empty($invoices->house_na_me)): ?>
            <tr>
                <td class="info-label">বাড়ির নাম</td>
                <td class="info-value">: <?= htmlspecialchars($invoices->house_na_me, ENT_QUOTES, 'UTF-8'); ?></td>
            </tr>
            <?php endif; ?>
        </table>

        <div class="divider"></div>

        <!-- Payment Breakdown -->
        <table class="data-table">
            <thead>
                <tr>
                    <th class="text-left">অর্থ বছর</th>
                    <th class="text-right">টাকার পরিমাণ</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($paid_amnt_) && is_array($paid_amnt_)): ?>
                    <?php foreach ($paid_amnt_ as $sngl): ?>
                    <tr>
                        <td class="text-left"><?= htmlspecialchars($sngl->paidable_year ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="text-right">৳ <?= bn_number(number_format((float)($sngl->paid_amount ?? 0))); ?>/-</td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                <?php
                /*
                   ছাড় on the thermal/POS slip. Same reasoning as the A4 money
                   receipt: the citizen's paper must show the gross and the
                   discount, or the slip reads "সর্বমোট ৪০০" for a bill that was
                   ৫০০ with ১০০ forgiven. Rendered only when a discount exists —
                   every pre-existing receipt has NULL, and a "ছাড় ৳0" line on
                   all of them would be noise on a 58mm slip where every line
                   costs vertical space.
                */
                $sl_discount = (int) ($invoices->discount_amounts ?? 0);
                if ($sl_discount > 0) :
                    $sl_net   = (float) ($invoices->amnt_taka ?? 0);
                    $sl_gross = $sl_net + $sl_discount;
                ?>
                <tr>
                    <td class="text-left">মূল পরিমাণ:</td>
                    <td class="text-right">৳ <?= bn_number(number_format($sl_gross)); ?>/-</td>
                </tr>
                <tr>
                    <td class="text-left" style="color:#b91c1c;">ছাড়:</td>
                    <td class="text-right" style="color:#b91c1c;">− ৳ <?= bn_number(number_format($sl_discount)); ?>/-</td>
                </tr>
                <?php 
                $sl_reason = trim((string) ($invoices->discount_refs_name ?? ''));
                if ($sl_reason !== '') :
                ?>
                <tr>
                    <td class="text-left" style="color:#475569; font-size:11px;">ছাড়ের কারণ:</td>
                    <td class="text-right" style="color:#475569; font-size:11px; word-break: break-word;"><?= htmlspecialchars($sl_reason, ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
                <?php endif; ?>
                <?php endif; ?>
                <tr class="total-row">
                    <td class="text-left font-bold">সর্বমোট টাকা:</td>
                    <td class="text-right font-bold">৳ <?= bn_number(number_format((float)($invoices->amnt_taka ?? 0))); ?>/-</td>
                </tr>
            </tbody>
        </table>

        <!-- QR Verification -->
        <?php if (!empty($qr_base64)): ?>
            <div class="qr-wrap">
                <img src="data:image/png;base64,<?= $qr_base64; ?>" alt="QR Code" class="qr-img">
                <div class="qr-text">অনলাইন ভেরিফিকেশন কোড</div>
            </div>
        <?php endif; ?>

        <!-- Signatures & Footer -->
        <div class="signature-line">
            <div class="sig-block">
                আদায়কারী<br>
                <strong><?= htmlspecialchars($invoices->user_person_name ?? 'অফিস', ENT_QUOTES, 'UTF-8'); ?></strong>
            </div>
            <div class="sig-block">
                ইউপি কর্তৃপক্ষ<br>
                স্বাক্ষর ও সিল
            </div>
        </div>

        <div class="footer-note">
            <div>এটি কম্পিউটার জেনারেটেড ডিজিটাল রসিদ।</div>
            <div>ডিজিটাল ইউনিয়ন পরিষদ সেবা | ধন্যবাদ</div>
        </div>
    </div>

    <script type="text/javascript">
        window.addEventListener('load', function() {
            setTimeout(function() {
                window.focus();
                window.print();
            }, 300);
        });
    </script>
</body>
</html>
