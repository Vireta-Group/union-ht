<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * /add — নতুন করদাতা এন্ট্রি ফর্ম
 *
 * REBUILT 2026-09-29, twice over.
 *
 * 1. Onto the shared portal partials. It used to ship its own <head>, its own
 *    hand-rolled navbar and the legacy orange footer (footer-section /
 *    footer-cta / single-cta / cta-text / copyright-area, from
 *    legacy-profile.css, with #ff5e14 inline), which is why it did not look like
 *    the rest of the site. Now: front/partials/header.php, /navbar.php and
 *    /footer.php, exactly as front/home.php does. legacy-profile.css is gone.
 *
 * 2. The form itself. Every label was in English, contradicting the project's
 *    "all user-facing text in Bangla" rule, and the field markup repeated a
 *    60-character Tailwind class string on every single input. Labels are now
 *    Bengali and the inputs use .ht-field / .ht-label from portal.css, so a
 *    change to the design system reaches this form for free.
 *
 * WHAT WAS REMOVED AND WHY — read before re-adding
 *
 *   The NID auto-fill. The script block called .search_nid_datas and read
 *   .type_nid_num / .type_dob_s, none of which existed in the markup, so it
 *   could never fire. It is not restored, because the endpoint could not
 *   serve this page even if the inputs came back: $route['nid_v'] is declared
 *   twice in routes.php and the later declaration wins, so it resolves to
 *   Sonod::verify_this_sonod_person_nid_infos — behind the Sonod auth gate —
 *   which proxies to a third-party service (Getapi posts to
 *   http://darkboy/api/nidVerify) using the logged-in admin's userUniqs as a
 *   credential. A public form cannot use it. NID and date of birth are
 *   therefore ordinary inputs now.
 *
 *   That also fixes a real bug: dateOfBirth was `readonly` and nothing could
 *   write to it, so every submitted row had an empty date of birth.
 *
 *   The commented-out MDK `<!--begin::Row-->` block, the
 *   id="kt_ecommerce_settings_general_form" leftover, and the inline
 *   style="align-items: normal; text-align: left; padding: 2rem" on the card.
 *
 *   Six copies of the same oninput numeric-strip, replaced by one delegated
 *   handler on .js-numeric.
 *
 * WHAT MUST NOT CHANGE
 *   Every `name` attribute is read by Front::insert_data(); note that
 *   parmanentAddress is misspelled in the controller and the form together, and
 *   has to stay that way. Same for wordNumber, which is the WARD, not a word
 *   count. The AJAX hooks — .selectingWard_no, .selectingVillage,
 *   .selectingHouse, .starting_year_firsts, .starting_year_secs,
 *   .home_name_typess, .home_name_add_btn — and the #kt_modal_add_user modal id
 *   are all load-bearing; the scripts below are what drive them.
 *
 *   The ward list is still hardcoded 01-09. That is pre-existing and was left
 *   alone: whether it should come from a table is a separate question.
 */

// $page_title / $page_desc let front/partials/header.php name this page
// instead of falling back to the bare tenant name. They are assigned
// BEFORE $view_data is built: that array is what the partial receives,
// and when the controller has already populated $this->data,
// get_defined_vars() is never called, so anything set afterwards would
// simply not be in it.
$page_title = 'নতুন করদাতা যোগ করুন';
$page_desc  = 'ইউনিয়ন পরিষদে নতুন হোল্ডিং করদাতার তথ্য এন্ট্রি করুন';
$view_data  = ($this->data ?? get_defined_vars());
$view_data['page_title'] = $page_title;
$view_data['page_desc']  = $page_desc;

$this->load->view('front/partials/header', $view_data);
$this->load->view('front/partials/navbar', $view_data);
?>

<main class="grow">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-14">

        <div class="text-center mb-8">
            <h1 class="text-2xl sm:text-3xl font-bold text-neutral-900">নতুন করদাতা যোগ করুন</h1>
            <p class="mt-2 text-neutral-600">তারপরের ধাপে হোল্ডিং ট্যাক্স বিবর্তী ও রেসিপ পাওয়া যাবে</p>
        </div>

        <?php if ($this->session->flashdata('msg')): ?>
            <div class="ht-badge ht-badge-success w-full justify-start gap-2 p-3 mb-6 flashmessage" role="status">
                <i class="ph ph-check-circle text-base"></i>
                <span><?= htmlspecialchars($this->session->flashdata('msg'), ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
        <?php endif; ?>

        <form action="insrt" method="post" class="ht-card">
            <div class="p-6 sm:p-8 space-y-8">

                <!-- ── ব্যক্তিগত তথ্য ─────────────────────────────────── -->
                <fieldset>
                    <legend class="text-base font-bold text-neutral-900 pb-3 mb-1 border-b border-neutral-200 w-full">
                        ব্যক্তিগত তথ্য
                    </legend>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label class="ht-label" for="f-name">নাম <span class="text-red-600">*</span></label>
                            <input type="text" id="f-name" name="name" required
                                   class="ht-field fullNameAssgn" placeholder="পূর্ণ নাম">
                        </div>
                        <div>
                            <label class="ht-label" for="f-father">পিতার নাম</label>
                            <input type="text" id="f-father" name="fatherName"
                                   class="ht-field fatheNames" placeholder="পিতার নাম">
                        </div>
                        <div>
                            <label class="ht-label" for="f-mother">মাতার নাম</label>
                            <input type="text" id="f-mother" name="motherName"
                                   class="ht-field mothersNames" placeholder="মাতার নাম">
                        </div>
                        <div>
                            <label class="ht-label" for="f-nid">জাতীয় পরিচয়পত্র নম্বর</label>
                            <input type="text" id="f-nid" name="nidNumber" inputmode="numeric"
                                   class="ht-field js-numeric searchingNIDno" placeholder="১০ অঙ্কের নম্বর">
                        </div>
                        <div>
                            <label class="ht-label" for="f-dob">জন্ম তারিখ</label>
                            <input type="text" id="f-dob" name="dateOfBirth" autocomplete="off"
                                   class="ht-field searchingDOBs datePicks" placeholder="YYYY-MM-DD">
                        </div>
                    </div>
                </fieldset>

                <!-- ── ঠিকানা ──────────────────────────────────────────── -->
                <fieldset>
                    <legend class="text-base font-bold text-neutral-900 pb-3 mb-1 border-b border-neutral-200 w-full">
                        ঠিকানা
                    </legend>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label class="ht-label" for="f-present">বর্তমান ঠিকানা</label>
                            <input type="text" id="f-present" name="presentAddress"
                                   class="ht-field prsntAddrsAssgn" placeholder="বর্তমান ঠিকানা">
                        </div>
                        <div>
                            <!-- name is misspelled in the controller too -->
                            <label class="ht-label" for="f-permanent">স্থায়ী ঠিকানা</label>
                            <input type="text" id="f-permanent" name="parmanentAddress"
                                   class="ht-field prmntAddrsShow" placeholder="স্থায়ী ঠিকানা">
                        </div>
                    </div>
                </fieldset>

                <!-- ── যোগাযোগ ────────────────────────────────────────── -->
                <fieldset>
                    <legend class="text-base font-bold text-neutral-900 pb-3 mb-1 border-b border-neutral-200 w-full">
                        যোগাযোগ
                    </legend>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label class="ht-label" for="f-mobile">মোবাইল নম্বর</label>
                            <input type="tel" id="f-mobile" name="mobileNumber" inputmode="numeric"
                                   class="ht-field js-numeric" placeholder="01xxxxxxxxx">
                        </div>
                        <div>
                            <label class="ht-label" for="f-mobile2">দ্বিতীয় মোবাইল নম্বর</label>
                            <input type="tel" id="f-mobile2" name="secondaryMobileNumber" inputmode="numeric"
                                   class="ht-field js-numeric" placeholder="বাধ্যতামূলক নয়">
                        </div>
                    </div>
                </fieldset>

                <!-- ── হোল্ডিং তথ্য ────────────────────────────────────── -->
                <fieldset>
                    <legend class="text-base font-bold text-neutral-900 pb-3 mb-1 border-b border-neutral-200 w-full">
                        হোল্ডিং তথ্য
                    </legend>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-4">
                        <div>
                            <label class="ht-label" for="f-holding">হোল্ডিং নম্বর <span class="text-red-600">*</span></label>
                            <input type="text" id="f-holding" name="holdingNumber" required
                                   inputmode="numeric" maxlength="4" class="ht-field js-numeric"
                                   placeholder="যেমন ০১">
                        </div>
                        <div>
                            <label class="ht-label" for="f-ward">ওয়ার্ড <span class="text-red-600">*</span></label>
                            <select id="f-ward" name="wordNumber" required class="ht-field selectingWard_no">
                                <option value="">ওয়ার্ড নির্বাচন করুন</option>
                                <?php for ($w = 1; $w <= 9; $w++): ?>
                                    <option value="<?= str_pad($w, 2, '0', STR_PAD_LEFT) ?>">
                                        ওয়ার্ড নং <?= str_pad($w, 2, '0', STR_PAD_LEFT) ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div>
                            <label class="ht-label" for="f-village">গ্রাম <span class="text-red-600">*</span></label>
                            <select id="f-village" name="villageName" required class="ht-field selectingVillage">
                                <option value="">গ্রাম নির্বাচন করুন</option>
                            </select>
                        </div>
                        <div>
                            <label class="ht-label" for="f-house">বাড়ি <span class="text-red-600">*</span></label>
                            <select id="f-house" name="houseName" required class="ht-field selectingHouse">
                                <option value="">বাড়ি নির্বাচন করুন</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mt-4">
                        <div>
                            <label class="ht-label" for="f-year-from">শুরুর বছর <span class="text-red-600">*</span></label>
                            <div class="flex items-center gap-2">
                                <input type="text" id="f-year-from" name="holdingStartingDateFirst"
                                       required maxlength="4" inputmode="numeric"
                                       class="ht-field js-numeric starting_year_firsts" placeholder="2024">
                                <span class="text-neutral-500 select-none" aria-hidden="true">–</span>
                                <input type="text" name="holdingStartingDateSecs" readonly
                                       class="ht-field starting_year_secs bg-neutral-100" placeholder="2025"
                                       aria-label="শেষ বছর">
                            </div>
                            <p class="text-xs text-neutral-500 mt-1">শেষ বছরটি প্রথমটির এক বছর পরে নিজে থেকেই বসবে</p>
                        </div>
                        <div>
                            <label class="ht-label" for="f-condition">জমির ধরণ <span class="text-red-600">*</span></label>
                            <input type="text" id="f-condition" name="holdingCondition" required
                                   class="ht-field" placeholder="যেমন: কাচা, পাকা, মাটি">
                        </div>
                        <div>
                            <label class="ht-label" for="f-rate">বার্ষিক হার <span class="text-red-600">*</span></label>
                            <div class="relative">
                                <input type="text" id="f-rate" name="holdingRatesYearly" required
                                       inputmode="numeric" class="ht-field js-numeric pr-10" placeholder="200">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-neutral-500 text-sm pointer-events-none">টাকা</span>
                            </div>
                        </div>
                    </div>
                </fieldset>
            </div>

            <!-- ── actions ──────────────────────────────────────────── -->
            <div class="px-6 sm:px-8 py-5 bg-neutral-50 border-t border-neutral-200 rounded-b-lg
                        flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                <button type="reset" class="ht-btn ht-btn-secondary justify-center">ফর্ম পরিষ্কার করুন</button>
                <button type="submit" class="ht-btn ht-btn-primary justify-center">
                    <i class="ph ph-floppy-disk text-sm"></i>
                    <span>সংরক্ষণ করুন</span>
                </button>
            </div>
        </form>
    </div>
</main>

<!-- Add Home modal. Opened by window.htModal, which front/partials/header.php
     already loads; the data-toggle/data-target attributes are kept so the
     trigger inside get_homes_infos() below keeps working. -->
<div class="modal fade" id="kt_modal_add_user" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="flex items-center justify-between px-5 py-4 border-b border-neutral-200">
                <h2 class="text-lg font-semibold" id="staticBackdropLabel">নতুন বাড়ি যোগ করুন</h2>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="বন্ধ করুন">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="p-5">
                <label class="ht-label" for="f-home-name">বাড়ির নাম <span class="text-red-600">*</span></label>
                <input type="text" id="f-home-name" name="home_name" class="ht-field home_name_typess"
                       placeholder="যেমন: নবীন বাড়ি">
                <p class="text-xs text-neutral-500 mt-2">বাড়িটি আপনার নির্বাচিত গ্রামের অধীনে যুক্ত হবে।</p>
                <div class="text-center pt-5">
                    <button type="button" class="ht-btn ht-btn-primary mx-auto home_name_add_btn">
                        <i class="ph ph-plus text-sm"></i>
                        <span>যোগ করুন</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
/* One delegated handler instead of six copies of an inline oninput.
   Keeps digits and a single decimal point. */
$(document).on('input', '.js-numeric', function () {
    this.value = this.value
        .replace(/[^0-9.]/g, '')
        .replace(/(\..*?)\..*/g, '$1');
});

/* ওয়ার্ড → গ্রাম */
$(document).on('change', '.selectingWard_no', function () {
    const ward = $(this).val();
    $('.selectingVillage').html('<option value="">গ্রাম লোড হচ্ছে…</option>');
    $('.selectingHouse').html('<option value="">বাড়ি নির্বাচন করুন</option>');
    if (!ward) return;

    $.ajax({
        type: "post",
        url: "frontGetVillByWard",
        data: { ward_n: ward },
        success: function (res) {
            let options = '';
            for (let l = 0; l < res.length; l++) {
                options += `<option value="${res[l].villages_of_union_a_iidd}">${res[l].bn_vill_names}</option>`;
            }
            $('.selectingVillage').html(`<option value="">গ্রাম নির্বাচন করুন</option>${options}`);
        }
    });
});

/* গ্রাম → বাড়ি */
$(document).on('change', '.selectingVillage', function () {
    const village = $(this).val();
    $('.selectingHouse').html('<option value="">বাড়ি লোড হচ্ছে…</option>');
    if (!village) return;
    get_homes_infos(village);
});

function get_homes_infos(vil_id) {
    $.ajax({
        type: "post",
        url: "getHomeByVill",
        data: { vill_nos: vil_id },
        success: function (res) {
            let options = '';
            for (let l = 0; l < res.length; l++) {
                options += `<option value="${res[l].house_home_name_etc_iddiddd}">${res[l].house_na_me}</option>`;
            }
            // the last option opens the Add Home modal
            $('.selectingHouse').html(
                `<option value="">বাড়ি নির্বাচন করুন</option>${options}` +
                '<option value="nwad" class="add_home_modal" data-toggle="modal" data-target="#kt_modal_add_user">+ নতুন বাড়ি যোগ করুন</option>'
            );
        }
    });
}

/* নতুন বাড়ি */
$(document).on('change', '.selectingHouse', function () {
    if ($(this).val() === 'nwad') {
        window.htModal.open(document.getElementById('kt_modal_add_user'));
    }
});

$(document).on('click', '.home_name_add_btn', function () {
    const house_name = $('.home_name_typess').val();
    const vill_no    = $('.selectingVillage').val();
    const ward_no    = $('.selectingWard_no').val();

    if (house_name === '') {
        $('.home_name_typess').trigger('focus');
        return;
    }
    if (!vill_no) {
        Swal.fire({
            title: 'গ্রাম নির্বাচন করুন',
            text: 'অনুগ্রহ করে প্রথমে গ্রাম নির্বাচন করুন।',
            icon: 'warning',
            confirmButtonText: 'ঠিক আছে',
            confirmButtonColor: '#4f46e5'
        });
        return;
    }

    $.ajax({
        type: "post",
        url: "addNewHomeFront",
        data: { ward_no: ward_no, vill_no: vill_no, homes_text: house_name },
        success: function () {
            window.htModal.close(document.getElementById('kt_modal_add_user'));
            $('.home_name_typess').val('');
            get_homes_infos(vill_no);
        }
    });
});

/* শুরুর বছর → শেষ বছর (এক বছর পরে) */
$(document).on('keyup', '.starting_year_firsts', function () {
    const first = parseInt($(this).val(), 10);
    $('.starting_year_secs').val(isNaN(first) ? '' : first + 1);
});

$(document).ready(function () {
    $(".flashmessage").delay(3000).fadeOut(400);

    if (typeof $.fn.datepicker !== 'undefined') {
        $(".datePicks").datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true,
            yearRange: "1920:+10",
            showAnim: "fadeIn"
        });
    }
});
</script>

<?php
// Footer last: front/partials/footer.php closes </body></html> itself, so this
// file must not repeat them. Same order as front/home.php.
$this->load->view('front/partials/footer', $view_data);
