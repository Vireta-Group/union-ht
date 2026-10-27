<?php foreach ($main_datas as $main_data) { ?>
 
    <?php
        $string_for_barcode = 'Holding No: '.$main_data->holding_number.', Name: '.$main_data->family_person_name_head.', View Details: '.base_url().'/pub_view/'.$main_data->uniqID;
			// barcode Content
		
        $barcodeobj = new TCPDF2DBarcode($string_for_barcode, 'QRCODE,H');

        // output the barcode as PNG image
        $qr_code = $barcodeobj->getBarcodePNG(6, 6, array(0,0,0));
        // Genarate Barcode 
    ?>

<div>

    <div style=" background: url('inc/mpdf_inc/01sL.svg'); background-position: 50% 50%; background-repeat: no-repeat; background-image-opacity: 0.2; border: 8px solid #bd0404; height: 100%; width: 100%; ">
        <div style="">
            <p style="background-color: #181e4f; color: white; font-weight: bold; font-size: 30px; margin: 4px 4px 0 4px; text-align: center; ">শেখ হাসিনার মূল নীতি, গ্রাম শহরের উন্নতি</p>


            <div style="float: left; margin: -4px 0 0 2px; width: 7%; " >
                <table >
                    <tr >
                        <td style="background-color: #181e4f; color: white; font-weight: bold; font-size: 35px;  text-rotate: 90; text-align: center; padding: 18px; "> জন্ম ও মৃত্যুর ৪৫দিনের মধ্যে বিনা খরচে নিবন্ধন করুন </td>
                    </tr>
                </table>
            </div>


            
            <div style="width: 954px; height: 85%; float: left; margin: 8px 2px 0 -20px; border: 8px solid #bd0404; ">
            
                <img width="150px" style="float: left; margin: 10px 0 0 10px;" src="<?= base_url(); ?>/inc/assets/images/logos/logo01.png" alt=""> 



                <div>
                    <p style="font-size: 40px; color: #1f0907; font-weight: bold; text-align: center; margin: 10px 0 0 -160px; ">
                        <?= $ins_info->ins_name; ?> <br>
                    <span style="font-size: 30px; color: #1f0907; font-weight: bold;  text-align: center; ">
                        উপজেলাঃ <?= $ins_info->up_bn_name; ?>, জেলাঃ <?= $ins_info->dist_bn_name; ?>।
                    </span>
                    </p>
                </div>

                <img width="150px" style="float: right; margin: -140px 10px 0 0 ;" src="data:image/png;base64, <?php echo $qr_code; ?>" alt=""> 

                <div style="width: 350px; border: 4px dashed #f0024a; border-radius: 2%; position: absolute; margin: 0 auto;  " >
                    <table style="font-family: 'AdarshaLipi', 'AdorshoLipi', 'times new roman', serif; margin: 0 auto; margin-bottom: -70px;  padding: 0;  font-size: 60px; font-weight: bold; " >
                        <tr style="text-align: center;">
                            <td style="text-align: center;">
                                <?= $main_data->holding_number; ?>
                            </td>
                        </tr>
                    </table>
                </div>

                <div style="margin: 0 0 0 20px; " >
                    <p style="margin: 20px 0 5px 0 ; font-size: 38px; font-weight: bold; color: red; " >
                        গ্রাহকের নামঃ <?= $main_data->family_person_name_head; ?>
                    </p>
                    <p style="margin: 0 0 3px 0; font-size: 34px; font-weight: bold;" >
                        বাড়ির নামঃ <?= $main_data->house_na_me; ?> 
                    </p>
                    <p style="margin: 0 0 3px 0; font-size: 34px; font-weight: bold;" >
                        ওয়ার্ডঃ <?= BanglaConverter::en2bn($main_data->word_number); ?> 
                    </p>
                    <p style="margin: 0 0 3px 0; font-size: 34px; font-weight: bold;" >
                        গ্রামঃ <?= $main_data->bn_vill_names; ?> 
                    </p>
                </div>

                <div style="margin: 50px 0 0 15px; " >
                    <div style="width: 350px; float: left; border: 1px solid black;  " >
                        <p  style="font-size: 18px; margin: 0; padding: 6px; font-weight: bold; border-bottom: 1px solid black; " ><?php if (!empty($member_s->up_mem_s_name)) { echo $member_s->up_mem_s_name; } ?></p>
                        <p  style="font-size: 18px; margin: 0; padding: 6px; font-weight: bold; border-bottom: 1px solid black; " >ওয়ার্ড মেম্বার: <?php if (!empty($member_s->mobile_s_no_phone)) { echo BanglaConverter::en2bn($member_s->mobile_s_no_phone); } ?></p>
                    </div>


                    <div style="width: 500px; float: right; margin: -100px 0 0 0" >
                        <div style=" text-align: center; margin: 0 0 5px 0 ;">
                            <?php if (!empty($ins_info->cm_sign_s64)) { ?>
                                <img width="250px" height="100px"  src="<?= media_url($ins_info->cm_sign_s64, 'image/png'); ?>" alt="">
                            <?php }else { ?>
                                <div style="margin: 60px 0 0 0 " ></div>
                            <?php } ?>
                        </div>
                        <p  style="font-size: 20px; text-align: center; margin: 0; font-weight: bold;" ><?= $ins_info->cm_name_bn; ?></p>
                        <p  style="font-size: 22px; text-align: center; margin: 0; font-weight: bold;" >চেয়ারম্যান</p>
                        <p  style="font-size: 18px; text-align: center; margin: 0; font-weight: bold;" ><?= $ins_info->ins_name; ?> </p>
                        <p  style="font-size: 18px; text-align: center; margin: 0; font-weight: bold;" ><?= $ins_info->up_bn_name; ?>, <?= $ins_info->dist_bn_name; ?></p>
                    </div>
                </div>
            </div>


            
            <table style="float: right; width: 7%; margin: -18px 0 0 1030.7px;">
                <tr >
                    <td style="background-color: #181e4f; color: white; font-weight: bold; font-size: 40px; text-rotate: -90; text-align: center; padding: 122px 20px 160px 20px; ">আপনার শিশুকে স্কুলে পাঠান</td>
                </tr>
            </table>

            <p style="background-color: #181e4f; color: white; font-weight: bold; font-size: 30px; margin: -2px 4px 4px 4px; text-align: center; ">সময়মতো কর পরিশোধ করুন, ইউনিয়ন উন্নয়নে সহায়তা করুন</p>
        </div>
    </div>

</div>

<?php } ?>