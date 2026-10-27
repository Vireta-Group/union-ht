<?php
    $invoice_no = htmlspecialchars($payment->invoice_no ?? '', ENT_QUOTES, 'UTF-8');
    $paid_date_raw = !empty($payment->paid_at) ? $payment->paid_at : date('Y-m-d', strtotime($payment->created_at));
    $paid_date_bn = bn_num(date('d F, Y', strtotime($paid_date_raw)));
    
    // Month translation
    $en_m = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    $bn_m = ['জানুয়ারি','ফেব্রুয়ারি','মার্চ','এপ্রিল','মে','জুন','জুলাই','আগস্ট','সেপ্টেম্বর','অক্টোবর','নভেম্বর','ডিসেম্বর'];
    $paid_date_bn = str_replace($en_m, $bn_m, $paid_date_bn);

    $start_date_bn = !empty($payment->start_date) ? str_replace($en_m, $bn_m, bn_num(date('d F, Y', strtotime($payment->start_date)))) : '—';
    $end_date_bn   = !empty($payment->end_date) ? str_replace($en_m, $bn_m, bn_num(date('d F, Y', strtotime($payment->end_date)))) : '—';

    $pkg_name_bn = $payment->pkg_name_bn ?? 'স্ট্যান্ডার্ড';
    $pkg_name_en = $payment->pkg_name_en ?? 'Standard';
    $billing_cycle_bn = ($payment->billing_cycle === 'monthly') ? 'মাসিক' : 'বাৎসরিক';

    $method_map = [
        'bkash'  => 'বিকাশ (bKash)',
        'nagad'  => 'নগদ (Nagad)',
        'bank'   => 'ব্যাংক ডিপোজিট / ট্রান্সফার',
        'cash'   => 'অফিস ক্যাশ পেমেন্ট',
        'cheque' => 'ব্যাংক চেক',
        'other'  => 'অন্যান্য'
    ];
    $method_label = $method_map[$payment->payment_method] ?? strtoupper($payment->payment_method);
    $amount_formatted = bn_num(number_format((float)$payment->amount));
    $amount_raw = (int)$payment->amount;

    function num_to_bn_words_calc($number) {
        $n = (int)$number;
        if ($n === 0) return 'শূন্য';
        if ($n === 40000) return 'চল্লিশ হাজার';
        if ($n === 25000) return 'পঁচিশ হাজার';
        if ($n === 30000) return 'ত্রিশ হাজার';
        if ($n === 50000) return 'পঞ্চাশ হাজার';
        if ($n === 2400) return 'দুই হাজার চারশত';
        if ($n === 3800) return 'তিন হাজার আটশত';
        return bn_num(number_format($n));
    }
    $words_bn = num_to_bn_words_calc($amount_raw);
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <base href="<?php echo base_url(); ?>">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>ইনভয়েস #<?= $invoice_no ?> — <?= htmlspecialchars($payment->ins_name ?? 'ইউনিয়ন পরিষদ', ENT_QUOTES, 'UTF-8'); ?></title>

    <link rel="icon" type="image/x-icon" href="<?= media_url($payment->ins_logo_path ?? '', 'image/png') ?>" />

    <!-- Fonts: Local Adorsho Lipi, Hind Siliguri, Inter -->
    <link rel="stylesheet" href="inc/assets/vendor/fonts/adorsho-lipi.css">
    <link rel="stylesheet" href="inc/assets/vendor/fonts/hind-siliguri.css">
    <link rel="stylesheet" href="inc/assets/vendor/fonts/inter.css">

    <!-- Phosphor Icons -->
    <link rel="stylesheet" type="text/css" href="inc/assets/vendor/phosphor/regular/style.css"/>
    <link rel="stylesheet" type="text/css" href="inc/assets/vendor/phosphor/bold/style.css"/>
    <link rel="stylesheet" type="text/css" href="inc/assets/vendor/phosphor/fill/style.css"/>

    <style>
        /* Standalone, 100% reliable A4 styling without external dependencies */
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'AdorshoLipi', 'Hind Siliguri', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #f1f5f9;
            color: #0f172a;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
            padding: 24px 16px;
        }

        .no-print-bar {
            max-width: 820px;
            margin: 0 auto 16px auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.15s ease-in-out;
            border: none;
            font-family: inherit;
        }

        .btn-back {
            background-color: #ffffff;
            color: #334155;
            border: 1px solid #cbd5e1;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }
        .btn-back:hover {
            background-color: #f8fafc;
            color: #0f172a;
            border-color: #94a3b8;
        }

        .btn-print {
            background-color: #4f46e5;
            color: #ffffff;
            box-shadow: 0 2px 4px rgba(79, 70, 229, 0.25);
        }
        .btn-print:hover {
            background-color: #4338ca;
        }

        /* The Invoice Sheet (Calibrated for Standard A4) */
        .invoice-sheet {
            width: 100%;
            max-width: 820px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05), 0 8px 10px -6px rgba(0,0,0,0.02);
            padding: 36px 40px;
            position: relative;
        }

        .top-stripe {
            height: 6px;
            background: linear-gradient(90deg, #4338ca 0%, #4f46e5 50%, #312e81 100%);
            border-radius: 16px 16px 0 0;
            margin: -36px -40px 28px -40px;
        }

        /* Header */
        .inv-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 22px;
            border-bottom: 2px solid #e2e8f0;
            gap: 20px;
        }

        .platform-brand {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .brand-icon {
            width: 48px;
            height: 48px;
            background: #4f46e5;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 26px;
            flex-shrink: 0;
        }

        .brand-title {
            font-size: 22px;
            font-weight: 900;
            color: #0f172a;
            letter-spacing: -0.5px;
            line-height: 1.1;
        }
        .brand-title span {
            color: #4f46e5;
        }

        .brand-sub {
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: block;
            margin-top: 2px;
        }

        .brand-meta {
            font-size: 11px;
            color: #64748b;
            margin-top: 8px;
            line-height: 1.6;
        }
        .brand-meta p {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .inv-title-block {
            text-align: right;
            min-width: 200px;
        }
        .inv-receipt-label {
            font-size: 11px;
            font-weight: 800;
            color: #4f46e5;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            display: block;
        }
        .inv-main-heading {
            font-size: 28px;
            font-weight: 900;
            color: #0f172a;
            line-height: 1;
            margin-top: 2px;
            letter-spacing: 0.5px;
        }
        .inv-no-badge {
            font-family: 'Courier New', Courier, monospace;
            font-size: 13px;
            font-weight: 800;
            background: #f1f5f9;
            color: #0f172a;
            padding: 4px 10px;
            border-radius: 6px;
            display: inline-block;
            margin-top: 6px;
            border: 1px solid #e2e8f0;
        }
        .inv-date-text {
            font-size: 11.5px;
            color: #64748b;
            margin-top: 4px;
        }
        .paid-tag {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
            font-size: 11px;
            font-weight: 800;
            padding: 3px 10px;
            border-radius: 20px;
            margin-top: 8px;
        }

        /* 2-Column Info Grid */
        .info-grid {
            display: flex;
            gap: 20px;
            margin: 22px 0;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 22px;
        }

        .info-col {
            flex: 1;
            min-width: 0;
        }

        .info-section-title {
            font-size: 11px;
            font-weight: 800;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
            display: block;
        }

        .info-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px 16px;
            font-size: 12px;
            color: #334155;
            line-height: 1.5;
            height: calc(100% - 24px);
        }

        .info-box-name {
            font-size: 15px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .info-meta-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 4px 0;
            font-size: 12px;
        }
        .info-meta-row:not(:last-child) {
            border-bottom: 1px dashed #e2e8f0;
        }
        .info-meta-label {
            color: #64748b;
        }
        .info-meta-val {
            font-weight: 700;
            color: #0f172a;
        }

        /* Itemized Services Table */
        .inv-table-wrap {
            margin: 16px 0 20px 0;
        }

        .inv-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12.5px;
        }

        .inv-table th {
            background: #f8fafc;
            color: #334155;
            font-weight: 800;
            padding: 10px 14px;
            border-top: 2px solid #cbd5e1;
            border-bottom: 2px solid #cbd5e1;
            text-align: left;
        }

        .inv-table td {
            padding: 14px 14px;
            border-bottom: 1px solid #e2e8f0;
            color: #1e293b;
            vertical-align: top;
        }

        .item-main-title {
            font-size: 13.5px;
            font-weight: 800;
            color: #0f172a;
            display: block;
            margin-bottom: 4px;
        }

        .item-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: #eef2ff;
            color: #4338ca;
            border: 1px solid #c7d2fe;
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .item-desc {
            font-size: 11.5px;
            color: #64748b;
            line-height: 1.5;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .nowrap { white-space: nowrap; }

        /* Summary & Totals Layout (Flex Two Columns) */
        .summary-container {
            display: flex;
            justify-content: space-between;
            gap: 28px;
            margin-top: 10px;
            padding-top: 16px;
            border-top: 2px solid #e2e8f0;
        }

        .summary-left {
            flex: 1.1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .words-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px 16px;
        }
        .words-label {
            font-size: 11px;
            font-weight: 800;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: block;
            margin-bottom: 2px;
        }
        .words-val {
            font-size: 13.5px;
            font-weight: 800;
            color: #1e1b4b;
        }

        .note-card {
            background: #ffffff;
            border: 1px dashed #cbd5e1;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 11.5px;
            color: #475569;
            line-height: 1.5;
        }

        .summary-right {
            flex: 0.9;
            min-width: 250px;
            max-width: 320px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 5px 0;
            font-size: 12.5px;
            color: #475569;
        }
        .total-row-val {
            font-weight: 700;
            color: #0f172a;
        }

        .total-row.grand {
            border-top: 2px solid #0f172a;
            border-bottom: 2px solid #0f172a;
            padding: 8px 0;
            margin: 6px 0;
            font-size: 14.5px;
            font-weight: 900;
            color: #0f172a;
        }
        .total-row.grand .total-row-val {
            font-size: 15px;
            font-weight: 900;
            color: #0f172a;
        }

        .total-row.paid {
            color: #065f46;
            font-weight: 700;
        }
        .total-row.paid .total-row-val {
            color: #065f46;
            font-weight: 800;
        }

        .total-row.due {
            color: #64748b;
        }

        /* Footer Authentication & Signature */
        .inv-footer {
            margin-top: 32px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 20px;
        }

        .seal-block {
            display: flex;
            align-items: center;
            gap: 12px;
            max-width: 440px;
        }
        .seal-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #059669;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }
        .seal-text {
            font-size: 11px;
            color: #64748b;
            line-height: 1.45;
        }
        .seal-title {
            font-size: 11.5px;
            font-weight: 800;
            color: #065f46;
            display: block;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .sig-block {
            text-align: center;
            min-width: 180px;
        }
        .sig-line {
            border-top: 1px solid #94a3b8;
            padding-top: 6px;
        }
        .sig-title {
            font-size: 11.5px;
            font-weight: 800;
            color: #0f172a;
            display: block;
        }
        .sig-dept {
            font-size: 10px;
            color: #64748b;
            display: block;
        }

        /* Precise A4 Print Formatting */
        @page {
            size: A4 portrait;
            margin: 10mm 12mm;
        }

        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
                color: #000000 !important;
            }
            .no-print, .no-print-bar {
                display: none !important;
            }
            .invoice-sheet {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                max-width: 100% !important;
                width: 100% !important;
                page-break-inside: avoid;
            }
            .top-stripe {
                display: none !important;
            }
            .inv-header {
                padding-bottom: 16px;
            }
            .info-box, .words-card {
                background: #ffffff !important;
                border: 1px solid #cbd5e1 !important;
            }
            .inv-table th {
                background: #f1f5f9 !important;
                color: #000000 !important;
            }
            .btn {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    <!-- Top Action Buttons (Hidden when printing) -->
    <div class="no-print-bar no-print">
        <a href="subscription" class="btn btn-back">
            <i class="ph-bold ph-arrow-left"></i>
            <span>সাবস্ক্রিপশন পেইজে ফিরে যান</span>
        </a>

        <button type="button" onclick="window.print()" class="btn btn-print">
            <i class="ph-bold ph-printer"></i>
            <span>প্রিন্ট / PDF সেভ করুন (A4)</span>
        </button>
    </div>

    <!-- Official Invoice Document Sheet -->
    <div class="invoice-sheet">
        <div class="top-stripe"></div>

        <!-- 1. Header: Platform Identity & Invoice Meta -->
        <div class="inv-header">
            <div class="platform-brand">
                <div class="brand-icon">
                    <i class="ph-bold ph-buildings"></i>
                </div>
                <div>
                    <h1 class="brand-title">ViretaDev <span>SaaS</span></h1>
                    <span class="brand-sub">HT Technologies Limited</span>
                    <div class="brand-meta">
                        <p><i class="ph-bold ph-map-pin"></i> বনানী, ঢাকা-১২১৩, বাংলাদেশ</p>
                        <p><i class="ph-bold ph-envelope-simple"></i> billing@viretadev.com &nbsp;|&nbsp; +৮৮০ ১৭০০-০০০০০০</p>
                        <p><i class="ph-bold ph-shield-check"></i> ই-গভর্ন্যান্স সার্ভিসেস ও ডিজিটাল বাতায়ন প্ল্যাটফর্ম</p>
                    </div>
                </div>
            </div>

            <div class="inv-title-block">
                <span class="inv-receipt-label">অফিসিয়াল মানি রসিদ</span>
                <div class="inv-main-heading">INVOICE</div>
                <div class="inv-no-badge">#<?= $invoice_no ?></div>
                <div class="inv-date-text">ইস্যুর তারিখ: <strong><?= $paid_date_bn ?></strong></div>
                <div>
                    <span class="paid-tag">
                        <i class="ph-bold ph-check-circle"></i>
                        পরিশোধিত (PAID)
                    </span>
                </div>
            </div>
        </div>

        <!-- 2. Customer & Payment Terms (2 Columns) -->
        <div class="info-grid">
            <!-- Left: Billed To -->
            <div class="info-col">
                <span class="info-section-title">গ্রাহকের বিবরণ / বিল প্রাপক:</span>
                <div class="info-box">
                    <div class="info-box-name">
                        <i class="ph-bold ph-seal-check" style="color: #4f46e5;"></i>
                        <?= htmlspecialchars($payment->ins_name ?? 'ইউনিয়ন পরিষদ', ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                    <div style="color: #475569; margin-bottom: 8px;">
                        <p style="margin-bottom: 2px;">
                            <i class="ph-bold ph-map-pin" style="color: #94a3b8;"></i>
                            <?= htmlspecialchars($payment->addre_s_s ?: 'ইউনিয়ন পরিষদ কার্যালয়', ENT_QUOTES, 'UTF-8'); ?>
                        </p>
                        <p>
                            <i class="ph-bold ph-globe" style="color: #94a3b8;"></i>
                            <?= htmlspecialchars($payment->domain_names ?: ($payment->sub_domains . '.viretadev.com'), ENT_QUOTES, 'UTF-8'); ?>
                        </p>
                    </div>
                    <div style="border-top: 1px dashed #cbd5e1; padding-top: 6px; display: flex; justify-content: space-between; font-size: 11.5px;">
                        <span>পরিষদ কোড: <strong><?= bn_num($payment->ins_titute_p_id); ?></strong></span>
                        <span>মোবাইল: <strong><?= htmlspecialchars($payment->socib_phone ?: ($payment->chairman_phone ?: '—')); ?></strong></span>
                    </div>
                </div>
            </div>

            <!-- Right: Payment & License Terms -->
            <div class="info-col">
                <span class="info-section-title">পেমেন্ট ও লাইসেন্স টার্মস:</span>
                <div class="info-box">
                    <div class="info-meta-row">
                        <span class="info-meta-label">পেমেন্ট মাধ্যম:</span>
                        <span class="info-meta-val" style="text-transform: uppercase;"><?= $method_label ?></span>
                    </div>
                    <div class="info-meta-row">
                        <span class="info-meta-label">TrxID / রেফারেন্স:</span>
                        <span class="info-meta-val" style="font-family: monospace; font-size: 12.5px;"><?= htmlspecialchars($payment->reference ?? '—', ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                    <div class="info-meta-row">
                        <span class="info-meta-label">লাইসেন্স মেয়াদকাল:</span>
                        <span class="info-meta-val"><?= $start_date_bn ?> থেকে <?= $end_date_bn ?></span>
                    </div>
                    <div class="info-meta-row">
                        <span class="info-meta-label">বিলিং চক্র:</span>
                        <span class="info-meta-val" style="color: #4f46e5;"><?= $billing_cycle_bn ?> লাইসেন্স</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Itemized Services Table -->
        <div class="inv-table-wrap">
            <table class="inv-table">
                <thead>
                    <tr>
                        <th style="width: 44px; text-align: center;">নং</th>
                        <th>আইটেম ও সেবার বিবরণ</th>
                        <th style="width: 90px; text-align: center;">মেয়াদ</th>
                        <th style="width: 120px; text-align: right;">একক মূল্য</th>
                        <th style="width: 130px; text-align: right;">মোট (টাকা)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-center" style="font-weight: 700; color: #94a3b8;">১</td>
                        <td>
                            <strong class="item-main-title">
                                ডিজিটাল ইউনিয়ন পরিষদ ম্যানেজমেন্ট সফটওয়্যার লাইসেন্স ও ক্লাউড সাবস্ক্রিপশন
                            </strong>
                            <div class="item-badge">
                                <i class="ph-bold ph-crown"></i>
                                প্যাকেজ: <?= htmlspecialchars($pkg_name_bn, ENT_QUOTES, 'UTF-8'); ?> (<?= htmlspecialchars($pkg_name_en, ENT_QUOTES, 'UTF-8'); ?>)
                            </div>
                            <p class="item-desc">
                                হোল্ডিং ট্যাক্স অটোমেশন ও অ্যাসেসমেন্ট, ডিজিটাল নাগরিক সনদপত্র ব্যবস্থাপনা ও যাচাইকরণ, অনলাইন আবেদন গ্রহণ, এসএমএস নোটিফিকেশন গেটওয়ে এবং ক্লাউড হোস্টিং সেবা।
                            </p>
                        </td>
                        <td class="text-center nowrap">
                            <strong><?= $billing_cycle_bn ?></strong>
                            <span style="display: block; font-size: 10px; color: #94a3b8;">১ বৎসর</span>
                        </td>
                        <td class="text-right nowrap" style="font-weight: 700;">
                            ৳ <?= $amount_formatted ?>
                        </td>
                        <td class="text-right nowrap" style="font-weight: 900; font-size: 14px;">
                            ৳ <?= $amount_formatted ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- 4. Financial Summary & Calculations (2 Balanced Columns) -->
        <div class="summary-container">
            <!-- Left: In Words and Optional Note -->
            <div class="summary-left">
                <div class="words-card">
                    <span class="words-label">কথায় (টাকা):</span>
                    <div class="words-val">
                        <?= $words_bn ?> টাকা মাত্র।
                    </div>
                </div>

                <?php if (!empty($payment->note)): ?>
                <div class="note-card">
                    <strong style="color: #0f172a;">নোট / মন্তব্য:</strong>
                    <span><?= htmlspecialchars($payment->note, ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
                <?php endif; ?>
            </div>

            <!-- Right: Calculations Breakdown -->
            <div class="summary-right">
                <div class="total-row">
                    <span>উপ-মোট (Subtotal):</span>
                    <span class="total-row-val nowrap">৳ <?= $amount_formatted ?></span>
                </div>
                <div class="total-row">
                    <span>ছাড় (Discount):</span>
                    <span class="total-row-val nowrap">৳ ০</span>
                </div>
                <div class="total-row">
                    <span>ভ্যাট / ট্যাক্স (০%):</span>
                    <span class="total-row-val nowrap">৳ ০</span>
                </div>

                <div class="total-row grand">
                    <span>সর্বমোট প্রদেয়:</span>
                    <span class="total-row-val nowrap">৳ <?= $amount_formatted ?></span>
                </div>

                <div class="total-row paid">
                    <span>পরিশোধিত অর্থ (Paid):</span>
                    <span class="total-row-val nowrap">৳ <?= $amount_formatted ?></span>
                </div>

                <div class="total-row due">
                    <span>অবশিষ্ট বকেয়া (Due):</span>
                    <span class="total-row-val nowrap">৳ ০</span>
                </div>
            </div>
        </div>

        <!-- 5. Verification Seal & Authorized Signatory -->
        <div class="inv-footer">
            <div class="seal-block">
                <div class="seal-icon">
                    <i class="ph-bold ph-seal-check"></i>
                </div>
                <div class="seal-text">
                    <strong class="seal-title">ডিজিটাল ভেরিফায়েড ইনভয়েস</strong>
                    <span>এটি সিস্টেম-কর্তৃক প্রস্তুতকৃত অফিসিয়াল রসিদ। কোনো ম্যানুয়াল স্বাক্ষরের প্রয়োজন নেই। তথ্যের সত্যতা যাচাইয়ে ইনভয়েস নং <strong>#<?= $invoice_no ?></strong> ব্যবহার করুন।</span>
                </div>
            </div>

            <div class="sig-block">
                <div class="sig-line">
                    <span class="sig-title">কর্তৃপক্ষের স্বাক্ষর ও সিল</span>
                    <span class="sig-dept">হিসাব ও অর্থ বিভাগ, ViretaDev Platform</span>
                </div>
            </div>
        </div>

    </div>

    <!-- Keyboard Shortcut (Ctrl+P / Cmd+P) -->
    <script>
        window.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                e.preventDefault();
                window.print();
            }
        });
    </script>
</body>
</html>
