    <?php
        $string_for_barcode = 'Holding No: '.$invoices->holding_number.', Name: '.$invoices->family_person_name_head.', View Details: '.base_url().'/pub_view/'.$invoices->uniqID;
			// barcode Content
		
        $barcodeobj = new TCPDF2DBarcode($string_for_barcode, 'QRCODE,H');

        // output the barcode as PNG image
        $qr_code = $barcodeobj->getBarcodePNG(6, 6, array(0,0,0));
        // Genarate Barcode 
    ?>







<!DOCTYPE html>
<html lang="zxx" dir="ltr">

<!-- Mirrored from up2client.com/envato/digital-invoice-2.0/main-file/Light-Invoice/hospital_invoice.html by HTTrack Website Copier/3.x [XR&CO'2014], Tue, 10 Oct 2023 04:24:47 GMT -->
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Recipt Print</title>
    <link rel="icon" type="image/x-icon" href="<?= media_url($ins_info->ins_logo_path, 'image/png') ?>" />
	<link rel="stylesheet" href="inc/assets/vendor/fonts/adorsho-lipi.css">
	<link rel="stylesheet" type="text/css" href="inc/recept_assets/css/custom.css">
	<link rel="stylesheet" type="text/css" href="inc/recept_assets/css/media-query.css">
</head>
<body>
	<!--Invoice Wrap Start here -->
	<div class="invoice_wrap" style="margin-top: -50px; ">
		<div class="invoice-container">
			<div class="invoice-content-wrap" id="download_section">
                
				<!--Invoice content start here -->
				<section class="hospital-service-content" id="hospital_invoice">
					<div class="container">
						<!--hospital back image -->
						<div class="hosp-back-img-one">
							<img src="inc/recept_assets/images/hospital/hospital-back.png" alt="this is a back image">
						</div>
						<!--invoice owner name content -->
						<div class="invoice-owner-conte-wrap">
							<div class="invo-to-wrap">
								<div class="invoice-to-content">
									<h1 class="invo-to-owner inter-700 md-lg-font" style="margin-top: 25px; color: black; font-size: 18px; " >
                                        <span style="margin-top: 20px; color: black; font-size: 25px; " ><?= $invoices->family_person_name_head; ?></span>
                                    </h1>
									<p class="invo-owner-address medium-font inter-400 mtb-0">
                                        পিতার নামঃ  <?= $invoices->pason_father_name; ?>
                                    </p>
									<p class="invo-owner-address medium-font inter-400 mtb-0">
                                        বাড়ীর নামঃ <?= $invoices->house_na_me; ?>
                                    </p>
									<p class="invo-owner-address medium-font inter-400 mtb-0">
                                        গ্রামঃ <?= $invoices->bn_vill_names; ?>
                                    </p>
									<p class="invo-owner-address medium-font inter-400 mtb-0">
                                        ওয়ার্ড নং <?= BanglaConverter::en2bn($invoices->word_number); ?>
                                    </p>
                                    <p class="invo-owner-address medium-font inter-400 mtb-0" style="">
                                        হোল্ডিং নংঃ  <?= BanglaConverter::en2bn($invoices->holding_number); ?>
                                    </p> 
								</div>
							</div>
                            <div style="text-align: center;" >
                                <img src="<?= media_url($ins_info->ins_logo_path, 'image/png') ?>" alt="" width="120px" height="110px" >
                                <div style="font-family: 'AdarshaLipi', 'AdorshoLipi', 'Adarsha Lipi', 'Adorsho Lipi', solaimanlipi; font-size: 20px; font-weight: bold; border: 1px solid black; width: 220px; color: white; background: black; border-radius: 15px;   ">
                                    ট্যাক্স আদায়ের রশিদ 
                                </div> 


                                <p class="" style="color: black; margin: 0; padding:0; " >রশিদ নং  #<?= BanglaConverter::en2bn($invoices->invoice_no_s); ?> </p>
                                <p class="" style="color: black; margin: 0; padding:0; " >তারিখ: <?= BanglaConverter::en2bn(date('d-m-Y', $invoices->time_s_s)); ?></p>


                            </div>
							<div class="invo-pay-to-wrap">
								<div class="invoice-pay-content">
									<h2 class="invo-to-owner inter-700 md-lg-font" style="color: black; font-size: 25px; "><?= $ins_info->ins_name; ?></h2>
									<p class="invo-owner-address medium-font inter-400 mtb-0">
                                        উপজেলাঃ <?= $ins_info->up_bn_name; ?>, জেলাঃ <?= $ins_info->dist_bn_name; ?><br> <?= BanglaConverter::en2bn($ins_info->socib_phone); ?></p>
                                        <p style="" >
                                            <img src="data:image/png;base64, <?php echo $qr_code; ?>" alt="" width="120px" height="120px" >
                                        </p>
								</div>
							</div>
						</div>
						<!--Patient Report Info Start Here -->
						<div class="table-wrapper" style="margin-top: 0px; padding: 0px; ">
							<table class="invoice-table hospital-table">
								<thead>
									<tr class="invo-tb-header">
										<th class="invo-table-title sno-wid inter-700 medium-font">
                                            # 
                                        </th>
										<th class="invo-table-title re-price-wid rate-title inter-700 medium-font"> অর্থ বছর</th>
										<th class="invo-table-title re-qty-wid rate-title inter-700 medium-font">টাকার পরিমাণ</th>
									</tr>
								</thead>
								<tbody class="invo-tb-body">

                                    <?php $sl = 1; foreach ($paid_amnt_ as $sngl) { ?>
                                        <tr class="invo-tb-row " >
                                            <td class="invo-tb-data" style="color: black; font-size: 18px; " > <?= BanglaConverter::en2bn($sl); ?> </td>
                                            <td class="invo-tb-data rate-data" style="color: black; font-size: 18px; " ><?= BanglaConverter::en2bn($sngl->paidable_year); ?></td>
                                            <td class="invo-tb-data total-data" align="right" style="color: black; font-size: 18px; " ><?= BanglaConverter::en2bn($sngl->paid_amount); ?>/-</td>
                                        </tr>
                                    <?php $sl++; } ?>

								</tbody>
							</table>
						</div>
						<!--Patient Report Info End Here -->

						<!--Invoice additional info start here -->
						<div class="invo-addition-wrap">
							<div class="invo-add-info-content bus-term-cond-content">
<?php
/*
   ছাড় — a printed money receipt is the document a citizen keeps, so the
   discount has to be on it, not only on the screen. Without this the paper
   says "সর্বমোট ৫০০" for a bill where only ৪০০ was taken, and the citizen
   has no record that ১০০ was forgiven or why.

   amnt_taka is the NET, so the gross is amnt_taka + discount_amounts. The
   (int) cast matters — the column is varchar, and under PHP 8 a non-numeric
   value would make the arithmetic 0 rather than quietly adding nothing.

   Rendered on the left side with bullet points for clean, readable layout.
*/
$pt_discount = (int) ($invoices->discount_amounts ?? 0);
if ($pt_discount > 0) :
	$pt_net    = (float) ($invoices->amnt_taka ?? 0);
	$pt_gross  = $pt_net + $pt_discount;
	$pt_reason = trim((string) ($invoices->discount_refs_name ?? ''));
?>
								<div class="term-condi-list" style="padding-top: 6px;">
									<ul class="term-con-list" style="list-style-type: disc; margin: 0; padding-left: 20px;">
										<li class="term-cond-item" style="font-size: 14px; color: #374151; margin-bottom: 3px;">
											মূল পরিমাণ: <b style="color: #111827;"><?= BanglaConverter::en2bn(number_format($pt_gross)); ?>/-</b>
										</li>
										<li class="term-cond-item" style="font-size: 14px; color: #374151; margin-bottom: 3px;">
											ছাড়: <b style="color: #b91c1c;">− <?= BanglaConverter::en2bn(number_format($pt_discount)); ?>/-</b>
										</li>
<?php if ($pt_reason !== '') : ?>
										<li class="term-cond-item" style="font-size: 14px; color: #374151;">
											ছাড়ের কারণ: <b style="color: #111827;"><?= htmlspecialchars($pt_reason, ENT_QUOTES, 'UTF-8'); ?></b>
										</li>
<?php endif; ?>
									</ul>
								</div>
<?php endif; ?>
							</div>
							<div class="invo-bill-total bus-invo-total">
								<table class="invo-total-table">
									<tbody>
										<tr>
											<td class="inter-700 medium-font b-text hotel-sub">সর্বমোট </td>
											<td class="sm-text b-text invo-total-price" style="font-size: 22px; "><b><?= BanglaConverter::en2bn($invoices->amnt_taka); ?>/-</b></td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>

                        <div style="margin: 80px 55px 0 45px; color: black; ">
                            <div style="float: left; " ><?= $invoices->user_person_name; ?><br>আদায়কারী  </div>
                            <div style="float: right; " > ইউপি সচিবের স্বাক্ষর </div>
                        </div>

						<!--Invoice additional info end here -->
						<div class="hosp-back-img-two">
							<img src="inc/recept_assets/images/hospital/hospital-back-two.png" alt="this is back image two">
						</div>
					</div>
				</section>
				<!--Invoice content end here -->
			</div>

			<!--bottom content start here -->
			<section class="agency-bottom-content d-print-none" id="agency_bottom">
				<div class="container">
					<!--print-download content start here -->
					<div class="invo-buttons-wrap">
						<div class="invo-print-btn invo-btns">
							<a href="javascript:window.print()" class="print-btn">
								<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0_5_313)">
									<path d="M14 3V7C14 7.26522 14.1054 7.51957 14.2929 7.70711C14.4804 7.89464 14.7348 8 15 8H19" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M17 21H7C6.46957 21 5.96086 20.7893 5.58579 20.4142C5.21071 20.0391 5 19.5304 5 19V5C5 4.46957 5.21071 3.96086 5.58579 3.58579C5.96086 3.21071 6.46957 3 7 3H14L19 8V19C19 19.5304 18.7893 20.0391 18.4142 20.4142C18.0391 20.7893 17.5304 21 17 21Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M9 7H10" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M9 13H15" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M13 17H15" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></g><defs><clipPath id="clip0_5_313"><rect width="24" height="24" fill="white"/>
									</clipPath></defs></svg>
									<span class="inter-700 medium-font">Print</span>
								</a>
							</div>
							<div class="invo-down-btn invo-btns">
								<a class="download-btn" id="generatePDF">
									<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0_5_246)">
										<path d="M4 17V19C4 19.5304 4.21071 20.0391 4.58579 20.4142C4.96086 20.7893 5.46957 21 6 21H18C18.5304 21 19.0391 20.7893 19.4142 20.4142C19.7893 20.0391 20 19.5304 20 19V17" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M7 11L12 16L17 11" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 4V16" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></g><defs><clipPath id="clip0_5_246"><rect width="24" height="24" fill="white"/></clipPath></defs></svg>
										<span class="inter-700 medium-font">Download</span>
									</a>
								</div>
							</div>
							<!--print-download content end here -->
							<!--Note content -->
							<div class="invo-note-wrap">
								<div class="note-title">
									<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0_8_240)"><path d="M14 3V7C14 7.26522 14.1054 7.51957 14.2929 7.70711C14.4804 7.89464 14.7348 8 15 8H19" stroke="#00BAFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M17 21H7C6.46957 21 5.96086 20.7893 5.58579 20.4142C5.21071 20.0391 5 19.5304 5 19V5C5 4.46957 5.21071 3.96086 5.58579 3.58579C5.96086 3.21071 6.46957 3 7 3H14L19 8V19C19 19.5304 18.7893 20.0391 18.4142 20.4142C18.0391 20.7893 17.5304 21 17 21Z" stroke="#00BAFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M9 7H10" stroke="#00BAFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M9 13H15" stroke="#00BAFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M13 17H15" stroke="#00BAFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></g><defs><clipPath id="clip0_8_240"><rect width="24" height="24" fill="white"/>
									</clipPath></defs></svg>
									<span class="inter-700 medium-font">Note:</span>
								</div>
								<h3 class="inter-400 medium-font third-color note-desc mtb-0">
                                    This is computer generated receipt and require UP Authorized signature.
                                </h3>
							</div>
						</div>
					</section> 
					<!--bottom content end here -->
				</div>
			</div>
			<script src="inc/recept_assets/js/jquery.min.js"></script>
			<script src="inc/recept_assets/js/jspdf.min.js"></script>
			<script src="inc/recept_assets/js/html2canvas.min.js"></script>
			<script src="inc/recept_assets/js/custom.js"></script>
		</body>
		
<!-- Mirrored from up2client.com/envato/digital-invoice-2.0/main-file/Light-Invoice/hospital_invoice.html by HTTrack Website Copier/3.x [XR&CO'2014], Tue, 10 Oct 2023 04:24:53 GMT -->
</html>	




