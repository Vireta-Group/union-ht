<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * single_holding_person_view_file — rebuilt 2026-09-29 onto the shared portal partials.
 *
 * This page used to ship its own <head>, its own hand-rolled navbar and the
 * legacy orange footer (footer-section / footer-cta / single-cta / cta-text /
 * copyright-area, all from legacy-profile.css, with #ff5e14 and #eb4027 written
 * inline), which is why it did not look like the rest of the site. It now loads
 * front/partials/header.php, /navbar.php and /footer.php, exactly as
 * front/home.php does, and legacy-profile.css is no longer referenced.
 *
 * The class moves that came with it: .card -> .ht-card, .scrollable ->
 * max-h-64 overflow-y-auto. The nav markup that used to carry .nav-item and
 * .nav-active belongs to the shared navbar now.
 *
 * The two .scrollable panes (lines ~216 and ~243) are now max-h-64 overflow-y-auto; nothing else in the body moved.
 */


// $page_title / $page_desc let front/partials/header.php name this page
// instead of falling back to the bare tenant name. They are assigned
// BEFORE $view_data is built: that array is what the partial receives,
// and when the controller has already populated $this->data,
// get_defined_vars() is never called, so anything set afterwards would
// simply not be in it.
$page_title = 'হোল্ডিং মালিকের তথ্য';
$page_desc  = 'ইউনিয়ন পরিষদ হোল্ডিং মালিকের বিস্তারিত তথ্য';
$view_data  = ($this->data ?? get_defined_vars());
$view_data['page_title'] = $page_title;
$view_data['page_desc']  = $page_desc;

$this->load->view('front/partials/header', $view_data);
$this->load->view('front/partials/navbar', $view_data);
?>







     <main>
        <div class="max-w-6xl mx-auto px-4 py-12">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <div class="lg:col-span-4">
                    <div class="ht-card mb-6">
                        <div class="p-5 text-center">

                            <?php if (empty($holding_info->profile_image)) { ?>
                                <img src="inc/assets/himg/home_p.png" alt="avatar" class="rounded-full max-w-full h-auto" style="width: 150px;">
                            <?php }else { ?>
                                <img src="<?= media_url($holding_info->profile_image, 'image/png') ?>" alt="avatar" class="rounded-full max-w-full h-auto" style="width: 150px;">
                            <?php } ?>

                            <h5 class="my-1"><?= $holding_info->family_person_name_head; ?></h5>
                            <p class="text-neutral-900 mb-1 " style="font-size: 20px; font-weight: bold; "> #<?= $holding_info->holding_number; ?></p>
                            <p class="text-neutral-500 mb-6"><?= $holding_info->house_na_me; ?></p>
                            <!-- <div class="flex justify-center gap-1 mb-2">
                                <button type="button" class="bg-indigo-600 text-white px-3 py-1.5 rounded">Follow</button>
                                <button type="button" class="border border-indigo-600 text-indigo-600 px-3 py-1.5 rounded">Message</button>
                            </div> -->
                        </div>
                    </div>
                    <div class="ht-card mb-6 lg:mb-0" style="padding: 10px;">
                        <div class="p-5 w-full">
                            <p class="mb-1" style="font-size: 18px; font-weight: bold;">
                                হোল্ডিং বিস্তারিত
                            </p>
                            <hr style="height: 2px; border-width: 0; background-color: #eb4027;">
                            <ul class="flex flex-col divide-y divide-neutral-200 rounded-lg overflow-hidden">
                                <li class="px-3 py-2 text-left">
                                    <div class="text-center">
                                        <p class="mb-0">হোল্ডিং রেট</p>
                                        <div style="border-left: 2px solid #eb4027; border-right: 2px solid #eb4027;">
                                            <p class="mb-0" style="font-weight: bold;"><?= $holding_info->holding_rates_yearly; ?>/=</p>
                                        </div>
                                    </div>
                                </li>
                                <!--
                                <li class="px-3 py-2 text-left">
                                    <div class="text-center">
                                        <p class="mb-0">Holding Start Year</p>
                                        <div style="border-left: 2px solid #eb4027; border-right: 2px solid #eb4027;">
                                            <p class="mb-0" style="font-weight: bold;">2020</p>
                                        </div>
                                    </div>
                                </li>

                                <li class="px-3 py-2 text-left">
                                    <div class="text-center">
                                        <p class="mb-0">Last Paid Year</p>
                                        <div style="border-left: 2px solid #eb4027; border-right: 2px solid #eb4027;">
                                            <p class="mb-0" style="font-weight: bold;">2021</p>
                                        </div>
                                    </div>
                                </li>
                                -->

                                <li class="px-3 py-2 text-left">
                                    <div class="text-center">
                                        <p class="mb-0">বকেয়া টাকা</p>
                                        <div style="border-left: 2px solid #eb4027; border-right: 2px solid #eb4027;">
                                            <p class="mb-0" style="font-weight: bold;"><?= $total_unpaid; ?>/-</p>
                                        </div>
                                    </div>
                                </li>
                                <!-- 
                                <li class="px-3 py-2 text-left">
                                    <div class="text-center">
                                        <p class="mb-0">Last Paid Amount</p>
                                        <div style="border-left: 2px solid #eb4027; border-right: 2px solid #eb4027;">
                                            <p class="mb-0" style="font-weight: bold;">100/=</p>
                                        </div>
                                    </div>
                                </li> -->
                            </ul>
                        </div>
                    </div>
                </div>


                <div class="lg:col-span-8">
                    <div class="ht-card mb-6" style="align-items: normal; padding-top: 20px;">
                        <div class="p-5 w-full">
                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-y-2">
                                <div class="sm:col-span-3">
                                    <p class="mb-0 text-left font-bold">বাবার নাম</p>
                                </div>
                                <div class="sm:col-span-9">
                                    <p class="text-neutral-500 mb-0 text-left">
                                        <?= $holding_info->pason_father_name; ?>
                                    </p>
                                </div>
                            </div>
                            <hr style="height: 1px; border-width: 0; background-color: #eb4027;">
                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-y-2">
                                <div class="sm:col-span-3">
                                    <p class="mb-0 text-left font-bold">মায়ের নাম</p>
                                </div>
                                <div class="sm:col-span-9">
                                    <p class="text-neutral-500 mb-0 text-left">
                                        <?= $holding_info->parson_mother_name; ?>
                                    </p>
                                </div>
                            </div>
                            <hr style="height: 1px; border-width: 0; background-color: #eb4027;">
                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-y-2">
                                <div class="sm:col-span-3">
                                    <p class="mb-0 text-left font-bold">ওয়ার্ড নং</p>
                                </div>
                                <div class="sm:col-span-9">
                                    <p class="text-neutral-500 mb-0 text-left">
                                        <?= $holding_info->word_number; ?>
                                    </p>
                                </div>
                            </div>
                            <hr style="height: 1px; border-width: 0; background-color: #eb4027;">
                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-y-2">
                                <div class="sm:col-span-3">
                                    <p class="mb-0 text-left font-bold">গ্রামের নাম</p>
                                </div>
                                <div class="sm:col-span-9">
                                    <p class="text-neutral-500 mb-0 text-left">
                                        <?= $holding_info->bn_vill_names; ?>
                                    </p>
                                </div>
                            </div>
                            <hr style="height: 1px; border-width: 0; background-color: #eb4027;">
                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-y-2">
                                <div class="sm:col-span-3">
                                    <p class="mb-0 text-left font-bold">হোল্ডিং ধরণ </p>
                                </div>
                                <div class="sm:col-span-9">
                                    <p class="text-neutral-500 mb-0 text-left">
                                        <?= $holding_info->holding_condition; ?>
                                    </p>
                                </div>
                            </div>
                            <hr style="height: 1px; border-width: 0; background-color: #eb4027;">

                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <div class="ht-card mb-6 md:mb-0" style="padding: 10px;">
                                <div class="p-5 w-full">
                                    <p class="mb-2" style="font-size: 18px; font-weight: bold;">পরিশোধিত বছর</p>
                                        <hr style="height: 2px; border-width: 0; background-color: #eb4027;">
                                    <div class="max-h-64 overflow-y-auto">
                                        <ul class="flex flex-col divide-y divide-neutral-200 rounded-lg overflow-hidden">
                                            <?php if (empty($paid_amount)) {
                                                echo 'কোনো তথ্য নেই। ';
                                            }else { 
                                                foreach ($paid_amount as $amnt) { ?>
                                                <li class="px-3 py-2 text-left">
                                                    <div class="text-center">
                                                        <p class="mb-0"><?= $amnt->paidable_year; ?></p>
                                                        <div style="border-left: 2px solid #eb4027; border-right: 2px solid #eb4027;">
                                                            <p class="mb-0" style="font-weight: bold;"><?= $amnt->paid_amount; ?>/=</p>
                                                        </div>
                                                    </div>
                                                </li>
                                            <?php } } ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <div class="ht-card mb-6 md:mb-0" style="padding: 10px;">
                                <div class="p-5 w-full">
                                    <p class="mb-2" style="font-size: 18px; font-weight: bold;">
                                        অপরিশোধিত (বকেয়া) বছর 
                                    </p>
                                    <hr style="height: 2px; border-width: 0; background-color: #eb4027;">
                                    <div class="max-h-64 overflow-y-auto">
                                        <ul class="flex flex-col divide-y divide-neutral-200 rounded-lg overflow-hidden">
                                            <?php if (empty($c_year_s)) { 
                                                echo 'সব পরিশোধ করা হয়েছে, কোনো বকেয়া নেই। '; 
                                            }else {
                                                foreach ($c_year_s as $unp) { ?> 
                                                    <li class="px-3 py-2 text-left">
                                                        <div class="text-center">
                                                            <p class="mb-0"><b><?= $unp; ?></b></p>
                                                            <div style="border-left: 2px solid #eb4027; border-right: 2px solid #eb4027;">
                                                                <p class="mb-0" style="font-weight: bold;">
                                                                    <?= $holding_info->holding_rates_yearly; ?>/=
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </li>
                                            
                                            <?php } } ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    








        <!-- A5 Script -->
        <script src="inc/assets/js/a5_script.js"></script>

<?php
// Footer last: front/partials/footer.php closes </body></html> itself, so
// this file must not repeat them. Same order as front/home.php.
$this->load->view('front/partials/footer', $view_data);
