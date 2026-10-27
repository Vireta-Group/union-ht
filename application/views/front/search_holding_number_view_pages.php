<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Holding-number search — /search_hold
 *
 * REBUILT 2026-09-29 on the shared portal partials.
 *
 * It used to carry its own <head>, its own hand-rolled navbar and the legacy
 * orange footer, which is why it looked like a different website from the home
 * page: `my-navbar`, `footer-section`, `footer-cta`, `single-cta`, `cta-text`,
 * `copyright-area` and `legacy-profile.css` are all gone, and the `<head>` now
 * comes from front/partials/header.php. That also fixes three real defects the
 * old markup had:
 *
 *   - `<html lang="en">` on a Bangla page
 *   - `href=""` on the brand and the Home link — an empty href reloads the
 *     current URL rather than going home
 *   - an `<img src="">` in the footer, i.e. a request for the page itself
 *
 * The card class moved from `.card` (legacy-profile.css, hardcoded #fff) to
 * `.ht-card`, which is the same idea written against the design tokens.
 * The red `<hr>` dividers were `#eb4027` inline; they are borders now.
 *
 * The AJAX contract is unchanged and must stay that way:
 *   .holding_no / .ward_no      the two inputs
 *   .holding_search_btn         the button
 *   .dataasign_here             the grid the result is injected into
 *   src_holding                 the POST endpoint
 */


// 1. <head> — lang="bn", SEO meta, portal.css, Phosphor, owl, ht-modal
// $page_title / $page_desc let front/partials/header.php name this page
// instead of falling back to the bare tenant name. They are assigned
// BEFORE $view_data is built: that array is what the partial receives,
// and when the controller has already populated $this->data,
// get_defined_vars() is never called, so anything set afterwards would
// simply not be in it.
$page_title = 'হোল্ডিং ট্যাক্স তথ্য যাচাই';
$page_desc  = 'হোল্ডিং ও ওয়ার্ড নম্বর দিয়ে ইউনিয়ন পরিষদের হোল্ডিং তথ্য খুঁজুন';
$view_data  = ($this->data ?? get_defined_vars());
$view_data['page_title'] = $page_title;
$view_data['page_desc']  = $page_desc;

$this->load->view('front/partials/header', $view_data);

// 2. The same sticky navbar the home page uses
$this->load->view('front/partials/navbar', $view_data);
?>

<main class="grow">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-14">

        <!-- Page heading -->
        <div class="text-center mb-8 lg:mb-10">
            <h1 class="text-2xl sm:text-3xl font-bold text-neutral-900">হোল্ডিং ট্যাক্স তথ্য যাচাই</h1>
            <p class="mt-2 text-neutral-600">হোল্ডিং নম্বর ও ওয়ার্ড নম্বর দিয়ে ধূম ইউনিয়ন পরিষদের তথ্য খুঁজুন</p>
        </div>

        <!-- Search form -->
        <div class="ht-card mb-8">
            <div class="p-6">
                <div class="flex flex-wrap items-center justify-center gap-3">
                    <input type="text"
                           class="ht-field w-44 sm:w-52 holding_no"
                           placeholder="হোল্ডিং নম্বর"
                           inputmode="numeric"
                           aria-label="হোল্ডিং নম্বর"
                           onkeypress="return (event.charCode !=8 && event.charCode ==0 || (event.charCode >= 48 && event.charCode <= 57))">
                    <span class="font-semibold text-neutral-500 select-none" aria-hidden="true">&ndash;</span>
                    <input type="text"
                           class="ht-field w-32 sm:w-40 ward_no"
                           placeholder="ওয়ার্ড"
                           inputmode="numeric"
                           aria-label="ওয়ার্ড নম্বর"
                           onkeypress="return (event.charCode !=8 && event.charCode ==0 || (event.charCode >= 48 && event.charCode <= 57))">
                    <button type="button" class="ht-btn ht-btn-primary holding_search_btn">
                        <i class="ph ph-magnifying-glass text-sm"></i>
                        <span>খুঁজুন</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Result lands here -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 dataasign_here"></div>

        <!-- Empty state, shown until a search returns something -->
        <div class="text-center py-10 text-neutral-500" data-empty-hint>
            <i class="ph ph-magnifying-glass text-4xl text-neutral-300"></i>
            <p class="mt-3 text-sm">উপরের ঘরে হোল্ডিং ও ওয়ার্ড নম্বর লিখে খুঁজুন</p>
        </div>

    </div>
</main>


<!-- A5 Script -->
<script src="inc/assets/js/a5_script.js"></script>
<script>
$(document).on('click', '.holding_search_btn', function () {
    let holding = $('.holding_no').val();
    let ward_no = $('.ward_no').val();

    $('[data-empty-hint]').addClass('hidden');

    $.ajax({
        type: "post",
        url: "src_holding",
        data: {
            holding_number: holding,
            ward: ward_no
        },
        success: function (res) {
            if (!res || !res.get_search_data) {
                $('.dataasign_here').html(`
                    <div class="lg:col-span-12">
                        <div class="ht-card p-6 text-center">
                            <i class="ph ph-magnifying-glass text-3xl text-neutral-300"></i>
                            <p class="mt-2 text-neutral-600">এই তথ্যে কোনো হোল্ডিং পাওয়া যায়নি। নম্বর আবার দেখুন।</p>
                        </div>
                    </div>`);
                return;
            }

            const d = res.get_search_data;

            // Only the columns FamilyModel::serachings_by_holding_num() actually
            // selects. It names them explicitly and leaves out pason_father_name
            // and vill_pr_iddsid on purpose — a public search is not the place for
            // a parent's name — so the old template's বাবার নাম / গ্রামের নাম
            // rows were permanently blank and had been commented out. Do not
            // re-add them here without changing that decision in the model.
            //
            // Rendered from one array so a new column is a one-line change rather
            // than another twelve-line block.
            const rows = [
                ['ওয়ার্ড নম্বর',        d.word_number],
                ['হোল্ডিং ধরণ',        d.holding_condition],
                ['হোল্ডিং শুরুর মূল্য',  d.holding_rates_yearly ? d.holding_rates_yearly + ' টাকা' : ''],
                ['হোল্ডিং চক্র',        d.holding_starting_years],
                ['মোবাইল নম্বর',        d.mobile_number_one]
            ];

            const detail = rows.map(function (r) {
                return `
                    <div class="flex flex-col sm:flex-row sm:items-start gap-1 sm:gap-4 py-3 border-b border-neutral-100 last:border-0">
                        <dt class="sm:w-40 shrink-0 font-semibold text-neutral-700">${r[0]}</dt>
                        <dd class="text-neutral-600">${r[1] ?? ''}</dd>
                    </div>`;
            }).join('');

            $('.dataasign_here').html(`
                <div class="lg:col-span-4">
                    <div class="ht-card h-full">
                        <div class="p-6 text-center">
                            <img src="inc/assets/himg/home_p.png" alt="" class="w-32 h-32 mx-auto rounded-full object-cover bg-neutral-100">
                            <h2 class="mt-3 text-lg font-bold text-neutral-900">${d.family_person_name_head ?? ''}</h2>
                            <p class="mt-1 text-xl font-bold text-indigo-600">#${d.holding_number ?? ''}</p>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-8">
                    <div class="ht-card h-full">
                        <div class="p-6">
                            <h3 class="text-base font-bold text-neutral-900 mb-1">হোল্ডিং তথ্য</h3>
                            <dl class="mt-2">${detail}</dl>
                        </div>
                    </div>
                </div>`);
        }
    });
});
</script>

<?php
// 3. Shared footer — replaces the legacy footer-section / single-cta markup.
//    Loaded LAST: front/partials/footer.php closes </body></html> itself, so
//    the page's own <script> blocks have to sit above this line.
$this->load->view('front/partials/footer', $view_data);
