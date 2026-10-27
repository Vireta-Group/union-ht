<?php
$tenant_logo_url = !empty($ins_info->ins_logo_path) ? media_url($ins_info->ins_logo_path) : base_url('inc/assets/sonod_img/bd_gov.png');
$govt_logo_url   = base_url('inc/assets/sonod_img/bd_gov.png');
$has_up_logo     = !empty($ins_info->ins_logo_path);
$has_cm_sign     = !empty($ins_info->cm_sign_s64);
$has_sec_sign    = !empty($ins_info->socib_sign_s64 ?? '');
$up_name         = !empty($ins_info->ins_name) ? $ins_info->ins_name : 'ইউনিয়ন পরিষদ';
$upazila_name    = !empty($ins_info->up_bn_name) ? $ins_info->up_bn_name : '';
$dist_name       = !empty($ins_info->dist_bn_name) ? $ins_info->dist_bn_name : '';

$custom_pad_url  = !empty($theme_config['custom_pad_path']) ? media_url($theme_config['custom_pad_path']) : '';
$design_mode     = $theme_config['design_mode'] ?? 'digital';
$selected_border_design = $theme_config['border_design'] ?? 'border_1';
$selected_text_layout   = $theme_config['text_layout'] ?? 'layout_grid_classic';
$signatory_count        = $theme_config['signatory_count'] ?? '3_person';

$border_list = [
    'border_1'  => ['title' => '১. রয়্যাল ক্লাসিক গোল্ড', 'desc' => 'নেভি ব্লু ও গোল্ডেন মাল্টি-লাইন ফ্রেম', 'cat' => 'royal'],
    'border_2'  => ['title' => '২. বিডি গভর্নমেন্ট এমারেল্ড', 'desc' => 'সরকারি এমারেল্ড গ্রীন ও গোল্ডেন ফ্লেক্স', 'cat' => 'classic'],
    'border_3'  => ['title' => '৩. হেরিটেজ ক্রিমসন ব্রোঞ্জ', 'desc' => 'ঐতিহ্যবাহী গাঢ় লাল ও ব্রোঞ্জ অলঙ্করণ', 'cat' => 'royal'],
    'border_4'  => ['title' => '৪. মডার্ন নেভি ডাবল', 'desc' => 'আধুনিক দ্বৈত স্ট্রাইপ পরিপাটি ফ্রেম', 'cat' => 'slim'],
    'border_5'  => ['title' => '৫. গ্রীক কি কর্নার মেয়ান্ডার', 'desc' => 'ক্ল্যাসিক্যাল জ্যামিতিক কর্নার ডিজাইন', 'cat' => 'classic'],
    'border_6'  => ['title' => '৬. সিকিউরিটি গিলোচে ল্যাটিস', 'desc' => 'অফিসিয়াল নিরাপত্তা সনদ জাল প্যাটার্ন', 'cat' => 'classic'],
    'border_7'  => ['title' => '৭. রয়্যাল স্যাফায়ার সিলভার', 'desc' => 'গাঢ় নীল ও রূপালী মেডেলিয়ন কর্নার', 'cat' => 'royal'],
    'border_8'  => ['title' => '৮. স্লেট চারকোল এক্সিকিউটিভ', 'desc' => 'কর্পোরেট গাঢ় ধূসর ও স্বর্ণালী সীমানা', 'cat' => 'classic'],
    'border_9'  => ['title' => '৯. ইসলামিক জ্যামিতিক ৮-স্টার', 'desc' => 'আরব্য সুষম ৮-কোণা তারা ও সীমানা', 'cat' => 'pattern'],
    'border_10' => ['title' => '১০. গোল্ডেন বারোক ফিলিগ্রি', 'desc' => 'রাজকীয় কারুকার্যময় কর্নার ফিলিগ্রি', 'cat' => 'royal'],
    'border_11' => ['title' => '১১. মিনিমাল গোল্ড পিনস্ট্রাইপ', 'desc' => 'সূক্ষ্ম মার্জিত একক সোনালী রেখা', 'cat' => 'slim'],
    'border_12' => ['title' => '১২. ইউপি সিল প্যাটার্ন', 'desc' => 'ইউনিয়ন পরিষদ মনোগ্রাম সিল ফ্রেম', 'cat' => 'classic'],
    'border_13' => ['title' => '১৩. রয়্যাল পার্পল গোল্ড', 'desc' => 'অভিজাত পার্পল ও স্বর্ণালী নকশা', 'cat' => 'royal'],
    'border_14' => ['title' => '১৪. প্লাটিনাম মনোক্রোম ফাইন', 'desc' => 'সাদাকালো সূক্ষ্ম অফিসিয়াল রেখা', 'cat' => 'slim'],
    'border_15' => ['title' => '১৫. ডিপ ব্রোঞ্জ অ্যাম্বার', 'desc' => 'গাঢ় ব্রোঞ্জ ও অ্যাম্বার কারুকাজ', 'cat' => 'royal'],
    'border_16' => ['title' => '১৬. ব্যাংকনোট গিলোচে ওয়েভ', 'desc' => 'গাণিতিক সাইন তরঙ্গ ও সিকিউরিটি রোসেট', 'cat' => 'classic'],
    'border_17' => ['title' => '১৭. আর্ট ডেকো সানবার্স্ট', 'desc' => '১৯২০-এর ক্লাসিক শেভরন ও সানবার্স্ট ফ্রেম', 'cat' => 'pattern'],
    'border_18' => ['title' => '১৮. সেল্টিক নটওয়ার্ক ফিতা', 'desc' => 'ঐতিহাসিক ইন্টারলেস নট ও ত্রিকোণ বিনুনি', 'cat' => 'pattern'],
    'border_19' => ['title' => '১৯. রোমান লরেল পাতার মালা', 'desc' => 'সম্মানসূচক ক্লাসিক্যাল লরেল গারল্যান্ড', 'cat' => 'classic'],
    'border_20' => ['title' => '২০. মরোক্কান জেলিজ স্টার', 'desc' => 'ইসলামিক সুষম জ্যামিতিক তারকার ফ্রেম', 'cat' => 'pattern'],
    'border_21' => ['title' => '২১. ভিক্টোরিয়ান বোটানিক্যাল', 'desc' => 'অ্যাকান্থাস পাতার কারুকার্যময় স্ক্রোল', 'cat' => 'pattern'],
    'border_22' => ['title' => '২২. নটিক্যাল রোপ ক্যাবল', 'desc' => 'দ্বৈত প্যাঁচানো দড়ির নৌ-সীমানা ফ্রেম', 'cat' => 'pattern'],
    'border_23' => ['title' => '২৩. গথিক ক্যাথেড্রাল আর্চ', 'desc' => 'মধ্যযুগীয় ট্রেইফয়েল ও ক্যাটিনারি খিলান', 'cat' => 'pattern'],
    'border_24' => ['title' => '২৪. মডার্ন স্টুডিও মিনিমাল', 'desc' => 'আধুনিক আর্কিটেকচারাল কর্নার কিউব', 'cat' => 'slim'],
    'border_25' => ['title' => '২৫. জাতীয় শাপলা ও পাটপাতা', 'desc' => 'বাংলার জাতীয় প্রতীক শাপলা ও পাটপাতা মোটিফ', 'cat' => 'classic'],
    'border_26' => ['title' => '২৬. ভিন্টেজ গেজেট হ্যাচিং', 'desc' => 'অফিসিয়াল রাজকীয় গেজেট ক্রস-হ্যাচ ফ্রেম', 'cat' => 'classic'],
    'border_27' => ['title' => '২৭. অরিয়েন্টাল মেঘ ও প্যাগোডা', 'desc' => 'ঐতিহ্যবাহী মেঘের প্যাটার্ন ও ফ্রেটওয়ার্ক', 'cat' => 'pattern'],
    'border_28' => ['title' => '২৮. হেরাল্ডিক নাইট শিল্ড', 'desc' => 'শিল্ড মেডেলিয়ন ও মার্জিত সীমানা', 'cat' => 'pattern'],
    'border_29' => ['title' => '২৯. ডায়মন্ড রম্বস চেইন', 'desc' => 'হীরক জ্যামিতিক চেইনের সূক্ষ্ম ফ্রেম', 'cat' => 'pattern'],
    'border_30' => ['title' => '৩০. আর্ট নুভো হুইপল্যাশ', 'desc' => 'প্রাকৃতিক বাঁকানো রেখা ও লতাপাতা কারুকাজ', 'cat' => 'pattern'],
    'border_31' => ['title' => '৩১. সাইবার সিকিউরিটি ম্যাট্রিক্স', 'desc' => 'হাই-টেক ডিজিটাল নোড ও সার্কিট লাইন', 'cat' => 'pattern'],
    'border_32' => ['title' => '৩২. করিন্থিয়ান স্তম্ভ ও কার্নিশ', 'desc' => 'রোমান ক্লাসিক্যাল ফ্লুটেড পিলারের ফ্রেম', 'cat' => 'classic'],
    'border_33' => ['title' => '৩৩. সূক্ষ্ম লেইস ও স্ক্যালপ', 'desc' => 'ডয়লি লেইস ও খাঁজকাটা মার্জিত সীমানা', 'cat' => 'pattern'],
    'border_34' => ['title' => '৩৪. বাউহাউস কনস্ট্রাকটিভিজম', 'desc' => 'আধুনিক জ্যামিতিক ব্লক ও অ্যাবস্ট্রাক্ট ফ্রেম', 'cat' => 'pattern'],
    'border_35' => ['title' => '৩৫. রোকোকো শেল কারতুশ', 'desc' => 'বারোক কার্ভ, সি-স্ক্রোল ও কারতুশ ফ্রেম', 'cat' => 'pattern'],
    'border_36' => ['title' => '৩৬. কারেন্সি ওয়েভ রোসেট', 'desc' => 'ব্যাংকনোট কার্ভ ও জেনুইন সিকিউরিটি সিল', 'cat' => 'classic'],
    'border_37' => ['title' => '৩৭. পার্সিয়ান কল্কা মোটিফ', 'desc' => 'দেশীয় ঐতিহ্যবাহী পেসলি ও কল্কা নকশা', 'cat' => 'pattern'],
    'border_38' => ['title' => '৩৮. হানি-কম্ব হেক্সাগন', 'desc' => 'ষড়ভুজ মৌচাক জ্যামিতিক নেটওয়ার্ক ফ্রেম', 'cat' => 'pattern'],
    'border_39' => ['title' => '৩৯. নর্ডিক রুন ও উইভ', 'desc' => 'স্ক্যান্ডিনেভিয়ান রুনিক লুপ ও ডায়মন্ড উইভ', 'cat' => 'pattern'],
    'border_40' => ['title' => '৪০. ইম্পেরিয়াল রয়্যাল ফিলিগ্রি', 'desc' => 'রাজকীয় মুক্তা, ডায়মন্ড ফ্লেক্স ও ফিলিগ্রি', 'cat' => 'royal'],
    'border_41' => ['title' => '৪১. সুইস হেয়ারলাইন লাক্সারি', 'desc' => 'আল্ট্রা-স্লিম গোল্ড ও স্লেট লাইন, সর্বোচ্চ টেক্সট স্পেস', 'cat' => 'slim'],
    'border_42' => ['title' => '৪২. কার্টিয়ে রয়্যাল জুয়েলারি', 'desc' => 'কার্টিয়ে বেভেলড ডায়মন্ড কাট স্লিম ট্রিম', 'cat' => 'royal'],
    'border_43' => ['title' => '৪৩. মোনাকো ইয়ট ক্লাব এলিট', 'desc' => 'শ্যাম্পেন গোল্ড ও নেভি ব্লু স্লিম রোপ কর্নার', 'cat' => 'royal'],
    'border_44' => ['title' => '৪৪. টিফানি ফাইন এলিগেন্স', 'desc' => 'টিফানি ডাবল স্লিম পিনস্ট্রাইপ ও পার্ল অ্যাকসেন্ট', 'cat' => 'slim'],
    'border_45' => ['title' => '৪৫. দুবাই রয়েল বুর্জ স্লিম', 'desc' => '৮-কোণা গোল্ডেন অক্টাগন স্লিম জিওমেট্রি', 'cat' => 'royal'],
    'border_46' => ['title' => '৪৬. প্যারিসিয়ান ওত-কুত্যুর', 'desc' => 'ব্রোঞ্জ ও গোল্ডেন টুইন ওয়্যার, এল-ব্র্যাকেট কর্নার', 'cat' => 'royal'],
    'border_47' => ['title' => '৪৭. নর্ডিক কুয়েট লাক্সারি', 'desc' => 'আল্ট্রা-স্লিম ১২px স্লেট ও শ্যাম্পেন মিনিমালিস্ট ফ্রেম', 'cat' => 'slim'],
    'border_48' => ['title' => '৪৮. টোকিও নিও-মডার্ন গোল্ড', 'desc' => 'জাপানিজ ওরিগামি কাটের স্লিম মেটালিক ট্রিম', 'cat' => 'slim'],
    'border_49' => ['title' => '৪৯. মিলানিজ টেইলরড স্ট্রাইপ', 'desc' => 'ইতালিয়ান মার্বেল গ্রিন ও গোল্ডেন সেলাই নকশা', 'cat' => 'slim'],
    'border_50' => ['title' => '৫০. মেফেয়ার লন্ডন হেরিটেজ', 'desc' => 'অক্সফোর্ড ব্লু ও এন্টিক গোল্ড স্লিম ইন্টারলক', 'cat' => 'royal'],
    'border_51' => ['title' => '৫১. ভার্সাই প্লাটিনাম ফিলিগ্রি', 'desc' => 'ফ্রেঞ্চ রাজপ্রাসাদের অতি সূক্ষ্ম প্লাটিনাম ও আইভরি লেইস', 'cat' => 'royal'],
    'border_52' => ['title' => '৫২. সিল্ক রোড ফাইন ব্রোকেড', 'desc' => 'ক্রিমসন ও গোল্ডেন সূক্ষ্ম জরি সুতোর স্লিম ফ্রেম', 'cat' => 'royal'],
    'border_53' => ['title' => '৫৩. সেন্ট মরিটজ আলপাইন এলিট', 'desc' => 'আইস স্যাফায়ার ও শ্যাম্পেন ক্রিস্টাল স্টার', 'cat' => 'royal'],
    'border_54' => ['title' => '৫৪. ম্যানহাটন স্কাইলাইন স্লিম', 'desc' => 'অনলাইন অনিক্স ও ২৪k গোল্ড আর্ট ডেকো স্লিম লাইন', 'cat' => 'royal'],
    'border_55' => ['title' => '৫৫. ইম্পেরিয়াল গোল্ড ওয়্যার', 'desc' => '২৪k গোল্ডেন ওয়্যারফ্রেম ও এমেরাল্ড ডট, সর্বোচ্চ ভেতরের জায়গা', 'cat' => 'slim'],
    'none'      => ['title' => 'বর্ডার ছাড়া (প্লেইন / প্যাড)', 'desc' => 'ছাপানো অফিশিয়াল প্যাডে প্রিন্টের জন্য', 'cat' => 'plain']
];

$layout_list = [
    'layout_grid_classic' => [
        'title' => '১. ক্লাসিক গ্রিড ও বিবরণ (Classic Grid)',
        'desc'  => 'স্ট্যান্ডার্ড দুই-কলাম ছকে নাগরিকের তথ্য ও পাশাপাশি বর্তমান/স্থায়ী ঠিকানা',
        'badge' => 'জনপ্রিয়'
    ],
    'layout_narrative' => [
        'title' => '২. ধারাবাহিক পূর্ণাঙ্গ প্রশাসনিক বয়ান (Narrative)',
        'desc'  => 'ধারাবাহিক অফিসিয়াল অনুচ্ছেদ ফরম্যাট: "এই মর্মে প্রত্যয়ন করা যাইতেছে যে..."',
        'badge' => 'অফিসিয়াল'
    ],
    'layout_modern_cards' => [
        'title' => '৩. মডার্ন শেডেড কার্ড বক্স (Modern Cards)',
        'desc'  => 'পরিচিতি, ঠিকানা ও প্রত্যয়ন পৃথক রাউন্ডেড শেডেড কার্ড বাক্সে সুবিন্যস্ত',
        'badge' => 'মডার্ন'
    ],
    'layout_bullet_secretariat' => [
        'title' => '৪. সচিবালয় ও গেজেট ক্রমিক নম্বর (Secretariat)',
        'desc'  => '১. নাম, ২. পিতা, ৩. মাতা, ৪. এনআইডি ক্রমানুসারে সুবিন্যস্ত পয়েন্ট',
        'badge' => 'গেজেট'
    ],
    'layout_bordered_table' => [
        'title' => '৫. অফিসিয়াল গভমেন্ট গ্রিড টেবিল (Bordered Grid)',
        'desc'  => 'অল্টারনেটিং স্ট্রাইপযুক্ত সুসজ্জিত সরকারি ছক ও সুনির্দিষ্ট সেল',
        'badge' => 'ফরমাল'
    ],
    'layout_centered_royal' => [
        'title' => '৬. রয়্যাল সেন্টারড টাইপোগ্রাফি (Centered Royal)',
        'desc'  => 'কেন্দ্রে বড় অক্ষরে নাগরিকের নাম ও রাজকীয় ঘোষণামূলক সমাহার',
        'badge' => 'রাজকীয়'
    ],
    'layout_legal_banner' => [
        'title' => '৭. লিগ্যাল অ্যাকসেন্ট ব্যানার (Legal Accent)',
        'desc'  => 'নাগরিকত্বের বিশেষ আকর্ষণীয় হাইলাইট ব্যানার ও অ্যাকসেন্ট বক্স',
        'badge' => 'আইনি'
    ],
    'layout_minimal_dots' => [
        'title' => '৮. দলিল ও এফিডেভিট ডটেড ফিল-ইন (Deed Dotted)',
        'desc'  => 'সনাতন দলিল ও এফিডেভিট স্টাইলে প্রতিটি তথ্যের নিচে ডট ডট ফিল-ইন লাইন',
        'badge' => 'দলিল'
    ]
];
?>

<!-- Breadcrumb & Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-neutral-200">
    <div>
        <nav class="flex items-center gap-2 text-xs text-neutral-500 mb-1">
            <a href="dashboard" class="hover:text-indigo-600 transition flex items-center gap-1">
                <i class="ph ph-house text-sm"></i> ড্যাশবোর্ড
            </a>
            <i class="ph ph-caret-right text-[10px] text-neutral-400"></i>
            <span class="text-neutral-800 font-medium">সনদ ব্যবস্থাপনা</span>
            <i class="ph ph-caret-right text-[10px] text-neutral-400"></i>
            <span class="text-indigo-600 font-medium">থিম ও কাস্টম ডিজাইন বিল্ডার</span>
        </nav>
        <h1 class="text-xl sm:text-2xl font-bold text-neutral-900 tracking-tight flex items-center gap-2">
            <span class="w-2.5 h-6 bg-indigo-600 rounded-full inline-block"></span>
            সনদ থিম, নিজস্ব প্যাড ও ডিজাইন কাস্টমাইজেশন
        </h1>
        <p class="text-xs sm:text-sm text-neutral-500 mt-0.5">ডিজিটাল থিম নির্বাচন করুন অথবা আপনার ইউনিয়নের নিজস্ব ছাপানো প্যাড / আর্টওয়ার্ক আপলোড করুন</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="newSonod" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-xl text-xs font-semibold transition">
            <i class="ph-bold ph-certificate text-sm"></i>
            <span>সনদ ইস্যু</span>
        </a>
        <a href="addMySonod" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-neutral-100 hover:bg-neutral-200 text-neutral-700 rounded-xl text-xs font-semibold transition">
            <i class="ph-bold ph-sliders-horizontal text-sm"></i>
            <span>সনদ বয়ান সেটিংস</span>
        </a>
    </div>
</div>

<style>
    /* Robust Flexbox Stepper Bar (Never collapses into vertical stack) */
    .stepper-nav-bar {
        display: flex !important;
        flex-direction: row !important;
        align-items: stretch !important;
        justify-content: space-between !important;
        gap: 8px !important;
        width: 100% !important;
        overflow-x: auto !important;
    }
    .step-nav-btn {
        flex: 1 1 0% !important;
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px !important;
        padding: 10px 12px !important;
        border-radius: 12px !important;
        border: 1.5px solid transparent !important;
        background: #ffffff;
        color: #64748b;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer !important;
        white-space: nowrap !important;
    }
    .step-nav-btn:hover {
        background: #f8fafc !important;
    }
    .step-nav-btn.active-step {
        border-color: #6366f1 !important;
        background: #eef2ff !important;
        color: #4338ca !important;
        box-shadow: 0 1px 3px rgba(99, 102, 241, 0.15) !important;
    }
    .step-nav-btn.active-step .step-num {
        background: #4f46e5 !important;
        color: #ffffff !important;
    }

    /* Sub-tabs Pills for Step 2 */
    .substep-pill {
        transition: all 0.18s ease;
        cursor: pointer;
        border: 1.5px solid #e2e8f0;
        background: #ffffff;
        color: #475569;
    }
    .substep-pill:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
    }
    .substep-pill.is-active {
        background: #4f46e5 !important;
        color: #ffffff !important;
        border-color: #4f46e5 !important;
        box-shadow: 0 1px 3px rgba(79, 70, 229, 0.25) !important;
    }

    .theme-radio-card {
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        border: 2px solid #e2e8f0;
        background: #ffffff;
        position: relative;
    }
    .theme-radio-card:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
    }
    .theme-radio-card.active-emerald {
        border-color: #059669 !important;
        background: rgba(5, 150, 105, 0.08) !important;
        box-shadow: 0 0 0 1px #059669, 0 2px 4px rgba(5, 150, 105, 0.1);
    }
    .theme-radio-card.active-indigo {
        border-color: #4f46e5 !important;
        background: rgba(79, 70, 229, 0.08) !important;
        box-shadow: 0 0 0 1px #4f46e5, 0 2px 4px rgba(79, 70, 229, 0.1);
    }
    .theme-radio-card.active-slate {
        border-color: #1e293b !important;
        background: rgba(30, 41, 59, 0.08) !important;
        box-shadow: 0 0 0 1px #1e293b, 0 2px 4px rgba(30, 41, 59, 0.1);
    }
    .theme-radio-card.active-crimson {
        border-color: #be123c !important;
        background: rgba(190, 18, 60, 0.08) !important;
        box-shadow: 0 0 0 1px #be123c, 0 2px 4px rgba(190, 18, 60, 0.1);
    }
    .theme-radio-card.active-custom {
        border-color: #2563eb !important;
        background: rgba(37, 99, 235, 0.08) !important;
        box-shadow: 0 0 0 1px #2563eb, 0 2px 4px rgba(37, 99, 235, 0.1);
    }
    .theme-radio-card.active-mode {
        border-color: #4f46e5 !important;
        background: rgba(79, 70, 229, 0.08) !important;
        box-shadow: 0 0 0 1px #4f46e5, 0 2px 4px rgba(79, 70, 229, 0.1);
    }

    .border-select-card, .watermark-select-card {
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        border: 2px solid #e2e8f0;
        background: #ffffff;
        position: relative;
    }
    .border-select-card:hover, .watermark-select-card:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
    }
    .border-select-card.is-active, .watermark-select-card.is-active {
        border-color: #4f46e5 !important;
        background: #eef2ff !important;
        box-shadow: 0 0 0 1px #4f46e5, 0 1px 3px rgba(79, 70, 229, 0.15);
    }

    .border-design-card {
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        border: 2px solid #e2e8f0;
        background: #ffffff;
        position: relative;
    }
    .border-design-card:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
        transform: translateY(-1px);
    }
    .border-design-card.is-active {
        border-color: #4f46e5 !important;
        background: #f5f7ff !important;
        box-shadow: 0 0 0 1.5px #4f46e5, 0 2px 6px rgba(79, 70, 229, 0.2);
    }

    .layout-select-card {
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        border: 2px solid #e2e8f0;
        background: #ffffff;
        position: relative;
    }
    .layout-select-card:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
        transform: translateY(-1px);
    }
    .layout-select-card.is-active {
        border-color: #4f46e5 !important;
        background: #f5f7ff !important;
        box-shadow: 0 0 0 1.5px #4f46e5, 0 2px 6px rgba(79, 70, 229, 0.18);
    }

    .theme-toggle-label {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.65rem 0.85rem;
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-radius: 0.75rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .theme-toggle-label:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
    }
    .theme-toggle-label.is-checked {
        background: #eef2ff;
        border-color: #818cf8;
    }

    .theme-checkbox-box {
        width: 1.25rem;
        height: 1.25rem;
        border-radius: 0.375rem;
        border: 2px solid #cbd5e1;
        background: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.15s ease;
        flex-shrink: 0;
    }
    .theme-checkbox-box svg {
        display: none;
        width: 0.85rem;
        height: 0.85rem;
        stroke: #ffffff;
        stroke-width: 3;
        fill: none;
    }
    .theme-checkbox-box.is-checked {
        background: #4f46e5;
        border-color: #4f46e5;
    }
    .theme-checkbox-box.is-checked svg {
        display: block;
    }

    .radio-check-badge {
        position: absolute;
        top: 0.4rem;
        right: 0.4rem;
        width: 1.15rem;
        height: 1.15rem;
        border-radius: 9999px;
        display: none;
        align-items: center;
        justify-content: center;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.15);
    }
    .radio-check-badge.is-visible {
        display: flex;
    }

    /* Reliable Filtering Style Override */
    .border-item-wrap.is-filtered-out {
        display: none !important;
    }

    .border-cat-pill {
        transition: all 0.15s ease;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        color: #475569;
        cursor: pointer;
    }
    .border-cat-pill:hover {
        background: #f1f5f9;
    }
    .border-cat-pill.is-active {
        background: #4f46e5 !important;
        color: #ffffff !important;
        border-color: #4f46e5 !important;
        box-shadow: 0 1px 3px rgba(79, 70, 229, 0.3) !important;
    }

    /* Authentic A4 Certificate Mockup (Portrait Proportions) */
    .a4-portrait-mockup {
        width: 100%;
        max-width: 530px;
        margin: 0 auto;
        aspect-ratio: 1 / 1.414;
        min-height: 760px;
        background: #ffffff;
        box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.22), 0 0 0 1px rgba(226, 232, 240, 0.9);
        border-radius: 0.5rem;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .rubber-seal-stamp {
        width: 74px;
        height: 74px;
        border: 2px dashed #dc2626;
        border-radius: 9999px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: #dc2626;
        font-size: 6.5px;
        font-weight: 800;
        line-height: 1.1;
        opacity: 0.85;
        transform: rotate(-12deg);
        user-select: none;
        pointer-events: none;
    }
    .rubber-seal-stamp-inner {
        width: 62px;
        height: 62px;
        border: 1px solid #dc2626;
        border-radius: 9999px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 2px;
    }
</style>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mt-4 items-start">
    
    <!-- Controls Column (Left, Stepper Wizard) -->
    <div class="lg:col-span-6 space-y-4">

        <!-- STEPPER PROGRESS TABS HEADER (4 Sleek Horizontal Tabs) -->
        <div id="stepperNavHeader" class="bg-white rounded-2xl border border-neutral-200/90 p-2 shadow-2xs">
            <div class="stepper-nav-bar">
                
                <!-- Step 1 Tab -->
                <button type="button" onclick="goToStep(1)" id="stepTab1" class="step-nav-btn active-step">
                    <span class="step-num w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold shrink-0 bg-indigo-600 text-white">১</span>
                    <span class="step-title font-bold text-xs">ডিজাইন ও রঙ</span>
                </button>

                <!-- Step 2 Tab -->
                <button type="button" onclick="goToStep(2)" id="stepTab2" class="step-nav-btn">
                    <span class="step-num w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold shrink-0 bg-neutral-200 text-neutral-600">২</span>
                    <span class="step-title font-bold text-xs">বর্ডার ও লেআউট</span>
                </button>

                <!-- Step 3 Tab -->
                <button type="button" onclick="goToStep(3)" id="stepTab3" class="step-nav-btn">
                    <span class="step-num w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold shrink-0 bg-neutral-200 text-neutral-600">৩</span>
                    <span class="step-title font-bold text-xs">লোগো ও ছবি</span>
                </button>

                <!-- Step 4 Tab -->
                <button type="button" onclick="goToStep(4)" id="stepTab4" class="step-nav-btn">
                    <span class="step-num w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold shrink-0 bg-neutral-200 text-neutral-600">৪</span>
                    <span class="step-title font-bold text-xs">স্বাক্ষর ও সংরক্ষণ</span>
                </button>

            </div>
        </div>

        <!-- MAIN WIZARD FORM -->
        <form id="themeConfigForm" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="custom_pad_path" id="customPadPathInput" value="<?= htmlspecialchars($theme_config['custom_pad_path'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="remove_custom_pad" id="removeCustomPadInput" value="0">

            <!-- ========================================== -->
            <!-- STEP 1: ডিজাইন মোড ও কালার প্যালেট           -->
            <!-- ========================================== -->
            <div id="wizardStep1" class="wizard-step-section space-y-4">
                
                <!-- Card 1.1: Design Mode Choice (Digital vs Custom Pad Upload) -->
                <div class="bg-white rounded-2xl border border-neutral-200/80 shadow-xs p-5 space-y-4">
                    <div class="flex items-center gap-2 pb-2.5 border-b border-neutral-100">
                        <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                            <i class="ph-bold ph-paint-brush-broad text-base"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-neutral-900">সনদ ডিজাইন সিস্টেম মোড</h3>
                            <p class="text-[11px] text-neutral-500">ডিজিটাল বিল্ট-ইন ফ্রেম নাকি নিজস্ব আর্টওয়ার্ক বা ছাপানো প্যাড ব্যবহার করবেন?</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <!-- Mode A: Built-in Digital Theme -->
                        <label class="cursor-pointer">
                            <input type="radio" name="design_mode" value="digital" class="sr-only designModeRadio" <?= ($design_mode === 'digital') ? 'checked' : ''; ?>>
                            <div class="theme-radio-card p-3.5 rounded-xl flex items-start gap-3 h-full">
                                <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                                    <i class="ph-bold ph-magic-wand text-lg"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <strong class="text-xs font-bold text-neutral-900 block">ডিজিটাল সিস্টেম থিম</strong>
                                    <span class="text-[11px] text-neutral-500 block leading-tight mt-0.5">রেডিমেড ডিজিটাল ফ্রেম, বর্ডার ও সরকারি থিম কালার</span>
                                </div>
                                <span class="radio-check-badge text-emerald-600 bg-emerald-600"><i class="ph-bold ph-check text-white"></i></span>
                            </div>
                        </label>

                        <!-- Mode B: Custom Pad Upload -->
                        <label class="cursor-pointer">
                            <input type="radio" name="design_mode" value="custom_upload" class="sr-only designModeRadio" <?= ($design_mode === 'custom_upload') ? 'checked' : ''; ?>>
                            <div class="theme-radio-card p-3.5 rounded-xl flex items-start gap-3 h-full">
                                <div class="w-9 h-9 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center shrink-0">
                                    <i class="ph-bold ph-cloud-arrow-up text-lg"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <strong class="text-xs font-bold text-neutral-900 block">নিজস্ব ডিজাইন আপলোড</strong>
                                    <span class="text-[11px] text-neutral-500 block leading-tight mt-0.5">ইউনিয়নের নিজস্ব প্যাড বা ব্যাকগ্রাউন্ড ছবি আপলোড করুন</span>
                                </div>
                                <span class="radio-check-badge text-indigo-600 bg-indigo-600"><i class="ph-bold ph-check text-white"></i></span>
                            </div>
                        </label>
                    </div>

                    <!-- Custom Pad Upload Sub-Panel -->
                    <div id="customPadUploadBox" class="p-4 bg-indigo-50/50 rounded-xl border border-indigo-200/90 space-y-3.5 <?= ($design_mode === 'custom_upload') ? '' : 'hidden'; ?>">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h4 class="text-xs font-bold text-indigo-950 flex items-center gap-1.5">
                                    <i class="ph-bold ph-file-image text-indigo-600"></i>
                                    ইউনিয়নের নিজস্ব প্যাড / ব্যাকগ্রাউন্ড ডিজাইন ছবি
                                </h4>
                                <p class="text-[11px] text-indigo-800/80 mt-0.5">A4 পোর্ট্রেট সাইজের যেকোনো হাই-কোয়ালিটি আর্টওয়ার্ক বা প্যাড (PNG / JPG, সর্বোচ্চ 5MB)</p>
                            </div>
                            <span class="px-2 py-0.5 rounded-md bg-indigo-200 text-indigo-900 font-bold text-[10px] uppercase">Custom Pad</span>
                        </div>

                        <!-- Dropzone & File Input -->
                        <div class="border-2 border-dashed border-indigo-300 hover:border-indigo-500 rounded-xl p-4 bg-white text-center transition cursor-pointer" onclick="document.getElementById('customPadFileInput').click();">
                            <input type="file" name="custom_pad_file" id="customPadFileInput" accept="image/png,image/jpeg,image/webp" class="sr-only" onchange="previewCustomPadFile(this)">
                            
                            <div class="flex flex-col items-center justify-center gap-2">
                                <div class="w-10 h-10 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl shadow-2xs">
                                    <i class="ph-bold ph-arrow-up-tray"></i>
                                </div>
                                <div>
                                    <span class="text-xs font-bold text-indigo-700 hover:underline">ক্লিক করে আপনার নিজস্ব প্যাড নির্বাচন করুন</span>
                                    <span class="text-[11px] text-neutral-500 block">অথবা ফাইল ড্র্যাগ করে এখানে আনুন</span>
                                </div>
                            </div>
                        </div>

                        <!-- Uploaded Preview Status Card -->
                        <div id="customPadStatusCard" class="p-3 bg-white rounded-xl border border-neutral-200 flex items-center justify-between gap-3 <?= !empty($theme_config['custom_pad_path']) ? '' : 'hidden'; ?>">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <img id="customPadThumbnail" src="<?= $custom_pad_url ?: 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'; ?>" 
                                     alt="Custom Pad" class="w-10 h-14 object-cover rounded border border-neutral-300 shadow-2xs shrink-0 bg-neutral-100">
                                <div class="min-w-0">
                                    <strong id="customPadFilename" class="block text-xs font-bold text-neutral-900 truncate">
                                        <?= !empty($theme_config['custom_pad_path']) ? basename($theme_config['custom_pad_path']) : 'custom_pad.png'; ?>
                                    </strong>
                                    <span class="text-[11px] text-emerald-600 font-semibold flex items-center gap-1">
                                        <i class="ph-bold ph-check-circle"></i> কাস্টম ডিজাইন সংযুক্ত রয়েছে
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <button type="button" onclick="document.getElementById('customPadFileInput').click();" class="px-2.5 py-1.5 rounded-lg bg-neutral-100 hover:bg-neutral-200 text-neutral-700 text-xs font-bold transition">
                                    পরিবর্তন
                                </button>
                                <button type="button" onclick="removeCustomPadArtwork();" class="p-1.5 rounded-lg text-rose-600 hover:bg-rose-50 transition" title="ডিজাইন মুছুন">
                                    <i class="ph-bold ph-trash text-base"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Pad Opacity Setting -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label for="customPadOpacityInput" class="text-xs font-bold text-neutral-700">কাস্টম ডিজাইনের দৃশ্যমানতা / স্বচ্ছতা</label>
                                <span id="padOpacityBadge" class="text-xs font-bold px-2 py-0.5 rounded bg-indigo-100 text-indigo-800">
                                    <?= round(($theme_config['custom_pad_opacity'] ?? 1.0) * 100); ?>%
                                </span>
                            </div>
                            <input type="range" id="customPadOpacityInput" name="custom_pad_opacity" min="0.1" max="1.0" step="0.05" 
                                   value="<?= $theme_config['custom_pad_opacity'] ?? 1.0; ?>"
                                   class="w-full accent-indigo-600 cursor-pointer" oninput="updatePadOpacity(this.value)">
                            <div class="flex justify-between text-[10px] text-neutral-400 mt-1">
                                <span>১০% (জলছাপ স্টাইল)</span>
                                <span>৫০% (হালকা ব্যাকগ্রাউন্ড)</span>
                                <span>১০০% (পূর্ণাঙ্গ স্পষ্ট প্যাড)</span>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Card 1.2: Color Themes & Palette -->
                <div class="bg-white rounded-2xl border border-neutral-200/80 shadow-xs p-5 space-y-4">
                    <div class="flex items-center gap-2 pb-2.5 border-b border-neutral-100">
                        <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <i class="ph-bold ph-palette text-base"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-neutral-900">কালার প্যালেট ও থিম স্টাইল</h3>
                            <p class="text-[11px] text-neutral-500">সনদের বর্ডার, টাইটেল ব্যাজ এবং প্রধান রঙের স্কিম</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2.5">
                        <!-- Emerald -->
                        <label class="cursor-pointer">
                            <input type="radio" name="theme_color" value="emerald" class="sr-only themeColorRadio" <?= ($theme_config['theme_color'] === 'emerald') ? 'checked' : ''; ?>>
                            <div class="theme-radio-card p-3 rounded-xl flex items-center justify-between">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-5 h-5 rounded-full bg-emerald-600 shrink-0 shadow-2xs"></span>
                                    <div>
                                        <div class="text-xs font-bold text-neutral-900">Official Emerald</div>
                                        <div class="text-[10px] text-neutral-500">সরকারি সবুজ</div>
                                    </div>
                                </div>
                                <span class="radio-check-badge text-emerald-600 bg-emerald-600"><i class="ph-bold ph-check text-white"></i></span>
                            </div>
                        </label>

                        <!-- Indigo -->
                        <label class="cursor-pointer">
                            <input type="radio" name="theme_color" value="indigo" class="sr-only themeColorRadio" <?= ($theme_config['theme_color'] === 'indigo') ? 'checked' : ''; ?>>
                            <div class="theme-radio-card p-3 rounded-xl flex items-center justify-between">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-5 h-5 rounded-full bg-indigo-600 shrink-0 shadow-2xs"></span>
                                    <div>
                                        <div class="text-xs font-bold text-neutral-900">Royal Indigo</div>
                                        <div class="text-[10px] text-neutral-500">প্রিমিয়াম নীল</div>
                                    </div>
                                </div>
                                <span class="radio-check-badge text-indigo-600 bg-indigo-600"><i class="ph-bold ph-check text-white"></i></span>
                            </div>
                        </label>

                        <!-- Slate / Charcoal -->
                        <label class="cursor-pointer">
                            <input type="radio" name="theme_color" value="slate" class="sr-only themeColorRadio" <?= ($theme_config['theme_color'] === 'slate') ? 'checked' : ''; ?>>
                            <div class="theme-radio-card p-3 rounded-xl flex items-center justify-between">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-5 h-5 rounded-full bg-slate-800 shrink-0 shadow-2xs"></span>
                                    <div>
                                        <div class="text-xs font-bold text-neutral-900">Classic Slate</div>
                                        <div class="text-[10px] text-neutral-500">ফরম্যাল চারকোল</div>
                                    </div>
                                </div>
                                <span class="radio-check-badge text-slate-800 bg-slate-800"><i class="ph-bold ph-check text-white"></i></span>
                            </div>
                        </label>

                        <!-- Crimson -->
                        <label class="cursor-pointer">
                            <input type="radio" name="theme_color" value="crimson" class="sr-only themeColorRadio" <?= ($theme_config['theme_color'] === 'crimson') ? 'checked' : ''; ?>>
                            <div class="theme-radio-card p-3 rounded-xl flex items-center justify-between">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-5 h-5 rounded-full bg-rose-700 shrink-0 shadow-2xs"></span>
                                    <div>
                                        <div class="text-xs font-bold text-neutral-900">Imperial Crimson</div>
                                        <div class="text-[10px] text-neutral-500">ঐতিহ্যবাহী মেরুন</div>
                                    </div>
                                </div>
                                <span class="radio-check-badge text-rose-700 bg-rose-700"><i class="ph-bold ph-check text-white"></i></span>
                            </div>
                        </label>

                        <!-- Custom Color Option -->
                        <label class="cursor-pointer col-span-2">
                            <input type="radio" name="theme_color" value="custom" class="sr-only themeColorRadio" <?= ($theme_config['theme_color'] === 'custom') ? 'checked' : ''; ?>>
                            <div class="theme-radio-card p-3.5 rounded-xl flex items-center justify-between">
                                <div class="flex items-center gap-2.5">
                                    <span id="customColorDot" class="w-5 h-5 rounded-full shrink-0 shadow-2xs border border-neutral-300" style="background-color: <?= $theme_config['primary_color'] ?? '#2563eb'; ?>;"></span>
                                    <div>
                                        <div class="text-xs font-bold text-neutral-900 flex items-center gap-1.5">
                                            <span>কাস্টম কালার (Custom Color)</span>
                                            <span class="text-[9px] uppercase tracking-wider px-1.5 py-0.5 rounded bg-blue-100 text-blue-700 font-extrabold">কালার পিকার</span>
                                        </div>
                                        <div class="text-[10px] text-neutral-500">যেকোনো হেক্স কোড বা রঙের শেড নির্বাচন করুন</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span id="customColorBadge" class="font-mono text-xs font-bold px-2 py-0.5 rounded bg-neutral-100 text-neutral-700 border border-neutral-200">
                                        <?= strtoupper($theme_config['primary_color'] ?? '#2563EB'); ?>
                                    </span>
                                    <span class="radio-check-badge text-blue-600 bg-blue-600"><i class="ph-bold ph-check text-white"></i></span>
                                </div>
                            </div>
                        </label>
                    </div>

                    <!-- Custom Color Picker Box -->
                    <div id="customColorBox" class="p-4 bg-neutral-50 rounded-xl border border-neutral-200/90 space-y-3.5 <?= ($theme_config['theme_color'] === 'custom') ? '' : 'hidden'; ?>">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold text-neutral-700 mb-1.5">প্রধান রং (Primary Color)</label>
                                <div class="flex items-center gap-2">
                                    <input type="color" id="customColorPicker" name="primary_color" value="<?= $theme_config['primary_color'] ?? '#2563eb'; ?>" 
                                           class="w-9 h-9 rounded-lg border border-neutral-300 p-0.5 cursor-pointer bg-white shadow-2xs shrink-0">
                                    <input type="text" id="customColorHex" value="<?= $theme_config['primary_color'] ?? '#2563eb'; ?>" maxlength="7" 
                                           class="w-full text-xs font-mono font-bold uppercase px-2.5 py-2 bg-white border border-neutral-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                </div>
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-neutral-700 mb-1.5">বর্ডার রং (Border Color)</label>
                                <div class="flex items-center gap-2">
                                    <input type="color" id="customBorderPicker" name="border_color" value="<?= $theme_config['border_color'] ?? '#2563eb'; ?>" 
                                           class="w-9 h-9 rounded-lg border border-neutral-300 p-0.5 cursor-pointer bg-white shadow-2xs shrink-0">
                                    <input type="text" id="customBorderHex" value="<?= $theme_config['border_color'] ?? '#2563eb'; ?>" maxlength="7" 
                                           class="w-full text-xs font-mono font-bold uppercase px-2.5 py-2 bg-white border border-neutral-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                </div>
                            </div>
                        </div>

                        <!-- Quick Preset Swatches -->
                        <div>
                            <div class="text-[10px] font-bold text-neutral-500 uppercase tracking-wider mb-2">জনপ্রিয় রঙের সোয়াচ (Quick Swatches)</div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <button type="button" class="quick-swatch w-6 h-6 rounded-full bg-[#1e3a8a] border-2 border-white shadow-xs hover:scale-110 transition cursor-pointer" data-color="#1e3a8a" title="Navy"></button>
                                <button type="button" class="quick-swatch w-6 h-6 rounded-full bg-[#2563eb] border-2 border-white shadow-xs hover:scale-110 transition cursor-pointer" data-color="#2563eb" title="Royal Blue"></button>
                                <button type="button" class="quick-swatch w-6 h-6 rounded-full bg-[#0284c7] border-2 border-white shadow-xs hover:scale-110 transition cursor-pointer" data-color="#0284c7" title="Sky Blue"></button>
                                <button type="button" class="quick-swatch w-6 h-6 rounded-full bg-[#0f766e] border-2 border-white shadow-xs hover:scale-110 transition cursor-pointer" data-color="#0f766e" title="Forest Teal"></button>
                                <button type="button" class="quick-swatch w-6 h-6 rounded-full bg-[#059669] border-2 border-white shadow-xs hover:scale-110 transition cursor-pointer" data-color="#059669" title="Emerald"></button>
                                <button type="button" class="quick-swatch w-6 h-6 rounded-full bg-[#7c3aed] border-2 border-white shadow-xs hover:scale-110 transition cursor-pointer" data-color="#7c3aed" title="Purple"></button>
                                <button type="button" class="quick-swatch w-6 h-6 rounded-full bg-[#9f1239] border-2 border-white shadow-xs hover:scale-110 transition cursor-pointer" data-color="#9f1239" title="Wine"></button>
                                <button type="button" class="quick-swatch w-6 h-6 rounded-full bg-[#b45309] border-2 border-white shadow-xs hover:scale-110 transition cursor-pointer" data-color="#b45309" title="Amber"></button>
                                <button type="button" class="quick-swatch w-6 h-6 rounded-full bg-[#172554] border-2 border-white shadow-xs hover:scale-110 transition cursor-pointer" data-color="#172554" title="Dark Slate"></button>
                                <button type="button" class="quick-swatch w-6 h-6 rounded-full bg-[#09090b] border-2 border-white shadow-xs hover:scale-110 transition cursor-pointer" data-color="#09090b" title="Black"></button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 1 Navigation Buttons -->
                <div class="flex items-center justify-end pt-2">
                    <button type="button" onclick="goToStep(2)" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 shadow-sm transition cursor-pointer">
                        <span>পরবর্তী ধাপ: বর্ডার ও লেআউট</span>
                        <i class="ph-bold ph-arrow-right text-sm"></i>
                    </button>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- STEP 2: বর্ডার, লেআউট ও জলছাপ (Sub-Tabs)    -->
            <!-- ========================================== -->
            <div id="wizardStep2" class="wizard-step-section hidden space-y-4">
                
                <!-- Internal Sub-Tabs Switcher for Step 2 (Prevents Long Scrolling!) -->
                <div class="bg-neutral-100 p-1 rounded-xl flex items-center gap-1 text-xs font-bold border border-neutral-200">
                    <button type="button" onclick="switchStep2SubTab('borders', this)" id="subTabBorders" class="substep-pill is-active flex-1 py-2 px-2.5 rounded-lg text-center flex items-center justify-center gap-1.5">
                        <i class="ph-bold ph-frame-corners text-sm"></i>
                        <span>৫৫টি বর্ডার ফ্রেম</span>
                    </button>
                    <button type="button" onclick="switchStep2SubTab('layouts', this)" id="subTabLayouts" class="substep-pill flex-1 py-2 px-2.5 rounded-lg text-center flex items-center justify-center gap-1.5">
                        <i class="ph-bold ph-text-align-justify text-sm"></i>
                        <span>৮টি টেক্সট লেআউট</span>
                    </button>
                    <button type="button" onclick="switchStep2SubTab('watermark', this)" id="subTabWatermark" class="substep-pill flex-1 py-2 px-2.5 rounded-lg text-center flex items-center justify-center gap-1.5">
                        <i class="ph-bold ph-drop text-sm"></i>
                        <span>ওয়াটারমার্ক ও জলছাপ</span>
                    </button>
                </div>

                <!-- Sub-Panel 2.1: 55 Official Border Frames Gallery -->
                <div id="subPanelBorders" class="substep-panel bg-white rounded-2xl border border-neutral-200/80 shadow-xs p-5 space-y-3.5">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 pb-2.5 border-b border-neutral-100">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                                <i class="ph-bold ph-frame-corners text-base"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-neutral-900">৫৫টি রাজকীয় ও অফিসিয়াল বর্ডার ফ্রেম</h3>
                                <p class="text-[11px] text-neutral-500">আপনার সনদের জন্য যেকোনো একটি ফ্রেম ক্লিক করুন</p>
                            </div>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700 self-start sm:self-auto">৫৫টি ডিজাইন</span>
                    </div>

                    <!-- Category Filter Chips & Quick Search -->
                    <div class="space-y-2">
                        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs">
                            <button type="button" onclick="filterBorderCategory('all', this)" class="border-cat-pill is-active px-2.5 py-1 rounded-lg text-[11px] font-bold">
                                সব বর্ডার (৫৫)
                            </button>
                            <button type="button" onclick="filterBorderCategory('royal', this)" class="border-cat-pill px-2.5 py-1 rounded-lg text-[11px] font-bold">
                                রয়্যাল ও গোল্ড
                            </button>
                            <button type="button" onclick="filterBorderCategory('slim', this)" class="border-cat-pill px-2.5 py-1 rounded-lg text-[11px] font-bold">
                                স্লিম ও মিনিমাল
                            </button>
                            <button type="button" onclick="filterBorderCategory('classic', this)" class="border-cat-pill px-2.5 py-1 rounded-lg text-[11px] font-bold">
                                ক্লাসিক অফিসিয়াল
                            </button>
                            <button type="button" onclick="filterBorderCategory('pattern', this)" class="border-cat-pill px-2.5 py-1 rounded-lg text-[11px] font-bold">
                                প্যাটার্ন ও ইসলামিক
                            </button>
                            <button type="button" onclick="filterBorderCategory('plain', this)" class="border-cat-pill px-2.5 py-1 rounded-lg text-[11px] font-bold">
                                বর্ডার ছাড়া
                            </button>
                        </div>

                        <!-- Search Input -->
                        <div class="relative">
                            <i class="ph ph-magnifying-glass absolute left-3 top-2.5 text-neutral-400 text-sm"></i>
                            <input type="text" id="borderSearchInput" placeholder="বর্ডার খুঁজুন (উদাঃ ১, গোল্ড, স্লিম, সুইস...)" 
                                   class="w-full text-xs pl-8 pr-3 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition"
                                   oninput="searchBorderCards(this.value)">
                        </div>
                    </div>

                    <!-- Borders Grid (Clean Scroll Container) -->
                    <div id="borderCardsGrid" class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 max-h-95 overflow-y-auto pr-1" style="max-height: 380px;">
                        <?php foreach ($border_list as $b_key => $b_data): ?>
                            <label class="cursor-pointer border-item-wrap" data-cat="<?= $b_data['cat'] ?? 'all'; ?>" data-title="<?= htmlspecialchars(mb_strtolower($b_data['title'] . ' ' . $b_data['desc'] . ' ' . $b_key)); ?>">
                                <input type="radio" name="border_design" value="<?= $b_key; ?>" class="sr-only borderDesignRadio" <?= ($selected_border_design === $b_key) ? 'checked' : ''; ?>>
                                <div class="border-design-card p-2 rounded-xl text-center transition flex flex-col items-center justify-between h-34 group hover:border-indigo-400 <?= ($selected_border_design === $b_key) ? 'is-active' : ''; ?>">
                                    <div class="w-full h-20 rounded bg-neutral-50 flex items-center justify-center p-1 overflow-hidden border border-neutral-100 group-hover:scale-[1.02] transition-transform">
                                        <?php if ($b_key !== 'none'): ?>
                                            <img src="<?= base_url('inc/assets/sonod_img/borders/' . $b_key . '.png'); ?>" alt="<?= htmlspecialchars($b_data['title']); ?>" class="max-h-full max-w-full object-contain">
                                        <?php else: ?>
                                            <div class="border-2 border-dashed border-neutral-300 w-full h-full rounded flex items-center justify-center text-[10px] text-neutral-400 font-semibold">প্লেইন / প্যাড</div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="w-full text-center mt-1">
                                        <span class="text-[11px] font-bold text-neutral-800 block truncate" title="<?= htmlspecialchars($b_data['title']); ?>"><?= htmlspecialchars($b_data['title']); ?></span>
                                        <span class="text-[9px] text-neutral-400 block truncate"><?= htmlspecialchars($b_data['desc']); ?></span>
                                    </div>
                                    <span class="radio-check-badge text-indigo-600 bg-indigo-600 absolute top-1.5 right-1.5 <?= ($selected_border_design === $b_key) ? 'is-visible' : ''; ?>"><i class="ph-bold ph-check text-white text-[9px]"></i></span>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Sub-Panel 2.2: 8 Text Layout Styles Selection -->
                <div id="subPanelLayouts" class="substep-panel hidden bg-white rounded-2xl border border-neutral-200/80 shadow-xs p-5 space-y-4">
                    <div class="flex items-center justify-between pb-2.5 border-b border-neutral-100">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                                <i class="ph-bold ph-text-align-justify text-base"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-neutral-900">সনদ টেক্সট লেআউট ও বয়ান বিন্যাস স্টাইল</h3>
                                <p class="text-[11px] text-neutral-500">নাগরিকের তথ্য ও প্রত্যয়ন বয়ানের ৮টি স্বতন্ত্র ডিজাইন ফরম্যাট</p>
                            </div>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700">৮টি স্টাইল</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <?php foreach ($layout_list as $l_key => $l_data): ?>
                            <label class="cursor-pointer">
                                <input type="radio" name="text_layout" value="<?= $l_key; ?>" class="sr-only textLayoutRadio" <?= ($selected_text_layout === $l_key) ? 'checked' : ''; ?>>
                                <div class="layout-select-card p-3 rounded-xl transition flex items-start gap-2.5 hover:border-indigo-400 <?= ($selected_text_layout === $l_key) ? 'is-active' : ''; ?>">
                                    <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0 mt-0.5">
                                        <i class="ph-bold ph-newspaper-clipping text-sm"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between gap-1">
                                            <strong class="text-xs font-bold text-neutral-900 truncate"><?= htmlspecialchars($l_data['title']); ?></strong>
                                            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-indigo-100 text-indigo-700 shrink-0"><?= $l_data['badge']; ?></span>
                                        </div>
                                        <span class="text-[10px] text-neutral-500 block leading-tight mt-0.5"><?= htmlspecialchars($l_data['desc']); ?></span>
                                    </div>
                                    <span class="radio-check-badge text-indigo-600 bg-indigo-600 shrink-0 mt-0.5 <?= ($selected_text_layout === $l_key) ? 'is-visible' : ''; ?>"><i class="ph-bold ph-check text-white text-[9px]"></i></span>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Sub-Panel 2.3: Watermark Controls -->
                <div id="subPanelWatermark" class="substep-panel hidden bg-white rounded-2xl border border-neutral-200/80 shadow-xs p-5 space-y-4">
                    <div class="flex items-center gap-2 pb-2.5 border-b border-neutral-100">
                        <div class="w-7 h-7 rounded-lg bg-cyan-50 text-cyan-600 flex items-center justify-center">
                            <i class="ph-bold ph-drop text-base"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-neutral-900">ওয়াটারমার্ক ও স্বচ্ছতা (Opacity)</h3>
                            <p class="text-[11px] text-neutral-500">সনদের বডিতে ব্যাকগ্রাউন্ড লোগো ও ট্রান্সপারেন্সি</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <!-- Watermark Toggle -->
                        <label class="theme-toggle-label">
                            <div class="pr-3">
                                <span class="text-xs font-bold text-neutral-900 block">ওয়াটারমার্ক সক্রিয় রাখুন</span>
                                <span class="text-[11px] text-neutral-500">সনদের মাঝখানে ব্যাকগ্রাউন্ড জলছাপ দৃশ্যমান হবে</span>
                            </div>
                            <input type="checkbox" name="watermark_visible" id="watermarkVisibleCheck" class="sr-only theme-toggle-input" <?= !empty($theme_config['watermark_visible']) ? 'checked' : ''; ?>>
                            <div class="theme-checkbox-box">
                                <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            </div>
                        </label>

                        <!-- Watermark Source -->
                        <div>
                            <label class="block text-xs font-bold text-neutral-700 mb-1.5">ওয়াটারমার্ক ইমেজ সোর্স</label>
                            <div class="grid grid-cols-2 gap-2">
                                <label class="cursor-pointer">
                                    <input type="radio" name="watermark_type" value="bd_gov" class="sr-only watermarkTypeRadio" <?= ($theme_config['watermark_type'] === 'bd_gov') ? 'checked' : ''; ?>>
                                    <div class="watermark-select-card p-3 rounded-xl text-center transition flex items-center justify-center gap-2">
                                        <span class="text-xs font-bold text-neutral-800">সরকারি মনোগ্রাম</span>
                                        <span class="radio-check-badge text-indigo-600 bg-indigo-600"><i class="ph-bold ph-check text-white text-[9px]"></i></span>
                                    </div>
                                </label>
                                <label class="cursor-pointer <?= !$has_up_logo ? 'opacity-60 pointer-events-none' : ''; ?>">
                                    <input type="radio" name="watermark_type" value="up_logo" class="sr-only watermarkTypeRadio" <?= ($theme_config['watermark_type'] === 'up_logo') ? 'checked' : ''; ?> <?= !$has_up_logo ? 'disabled' : ''; ?>>
                                    <div class="watermark-select-card p-3 rounded-xl text-center transition flex items-center justify-center gap-2">
                                        <span class="text-xs font-bold text-neutral-800">ইউপি নিজস্ব লোগো</span>
                                        <span class="radio-check-badge text-indigo-600 bg-indigo-600"><i class="ph-bold ph-check text-white text-[9px]"></i></span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Watermark Opacity Slider -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label for="opacityRangeInput" class="text-xs font-bold text-neutral-700">ওয়াটারমার্ক স্বচ্ছতা (Opacity)</label>
                                <span id="opacityBadge" class="text-xs font-bold px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 border border-indigo-200">
                                    <?= round(($theme_config['watermark_opacity'] ?? 0.06) * 100); ?>%
                                </span>
                            </div>
                            <input type="range" id="opacityRangeInput" name="watermark_opacity" min="0.02" max="0.25" step="0.01" 
                                   value="<?= $theme_config['watermark_opacity'] ?? 0.06; ?>"
                                   class="w-full accent-indigo-600 cursor-pointer">
                            <div class="flex justify-between text-[10px] text-neutral-400 mt-1">
                                <span>২% (খুব হালকা)</span>
                                <span>১০% (স্ট্যান্ডার্ড)</span>
                                <span>২৫% (স্পষ্ট)</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2 Navigation Buttons -->
                <div class="flex items-center justify-between pt-2">
                    <button type="button" onclick="goToStep(1)" class="px-4 py-2.5 bg-neutral-100 hover:bg-neutral-200 text-neutral-700 rounded-xl text-xs font-bold flex items-center gap-1.5 transition cursor-pointer">
                        <i class="ph-bold ph-arrow-left text-sm"></i>
                        <span>পূর্ববর্তী ধাপ</span>
                    </button>
                    <button type="button" onclick="goToStep(3)" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 shadow-sm transition cursor-pointer">
                        <span>পরবর্তী ধাপ: লোগো ও ছবি</span>
                        <i class="ph-bold ph-arrow-right text-sm"></i>
                    </button>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- STEP 3: লোগো, ছবি ও উপাদান                  -->
            <!-- ========================================== -->
            <div id="wizardStep3" class="wizard-step-section hidden space-y-4">
                
                <!-- Card 3.1: Header & Logos -->
                <div class="bg-white rounded-2xl border border-neutral-200/80 shadow-xs p-5 space-y-4">
                    <div class="flex items-center gap-2 pb-2.5 border-b border-neutral-100">
                        <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                            <i class="ph-bold ph-shield-check text-base"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-neutral-900">লোগো ও হেডার এলিমেন্ট</h3>
                            <p class="text-[11px] text-neutral-500">হেডারে সরকারি ও ইউনিয়ন পরিষদের লোগো প্রদর্শন</p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <label class="theme-toggle-label">
                            <div class="pr-3">
                                <span class="text-xs font-bold text-neutral-900 block">সরকারি লোগো প্রদর্শন (বামে)</span>
                                <span class="text-[11px] text-neutral-500">হেডারের বামপাশে জাতীয় মনোগ্রাম</span>
                            </div>
                            <input type="checkbox" name="show_govt_logo" id="showGovtLogoCheck" class="sr-only theme-toggle-input" <?= !empty($theme_config['show_govt_logo']) ? 'checked' : ''; ?>>
                            <div class="theme-checkbox-box">
                                <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            </div>
                        </label>

                        <label class="theme-toggle-label <?= !$has_up_logo ? 'opacity-60' : ''; ?>">
                            <div class="pr-3">
                                <span class="text-xs font-bold text-neutral-900 block">ইউপি নিজস্ব লোগো প্রদর্শন</span>
                                <span class="text-[11px] text-neutral-500"><?= $has_up_logo ? 'ইউপি প্রোফাইলে আপলোডকৃত লোগো' : 'লোগো আপলোড করা নেই (ডিফল্ট লোগো)'; ?></span>
                            </div>
                            <input type="checkbox" name="show_up_logo" id="showUpLogoCheck" class="sr-only theme-toggle-input" <?= !empty($theme_config['show_up_logo']) ? 'checked' : ''; ?>>
                            <div class="theme-checkbox-box">
                                <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            </div>
                        </label>

                        <label class="theme-toggle-label">
                            <div class="pr-3">
                                <span class="text-xs font-bold text-neutral-900 block">হেডার ঠিকানা ও যোগাযোগ তথ্য</span>
                                <span class="text-[11px] text-neutral-500">ইউপি সচিব/অফিসের ফোন নম্বর ও ঠিকানা প্রদর্শন</span>
                            </div>
                            <input type="checkbox" name="header_contact_visible" id="showContactCheck" class="sr-only theme-toggle-input" <?= !empty($theme_config['header_contact_visible']) ? 'checked' : ''; ?>>
                            <div class="theme-checkbox-box">
                                <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Card 3.2: Citizen Photo & Security Badges -->
                <div class="bg-white rounded-2xl border border-neutral-200/80 shadow-xs p-5 space-y-4">
                    <div class="flex items-center gap-2 pb-2.5 border-b border-neutral-100">
                        <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                            <i class="ph-bold ph-user-focus text-base"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-neutral-900">নাগরিকের ছবি ও নিরাপত্তা উপাদান</h3>
                            <p class="text-[11px] text-neutral-500">নাগরিকের পাসপোর্ট সাইজ ছবি ও ট্র্যাকিং বারকোড</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-neutral-700 mb-1.5">নাগরিকের ছবির অবস্থান (Citizen Photo)</label>
                            <div class="grid grid-cols-2 gap-2">
                                <label class="cursor-pointer">
                                    <input type="radio" name="citizen_photo_position" value="under_title" class="sr-only photoPosRadio" <?= (($theme_config['citizen_photo_position'] ?? 'under_title') === 'under_title') ? 'checked' : ''; ?>>
                                    <div class="border-select-card p-3 rounded-xl text-center transition">
                                        <div class="text-xs font-bold text-neutral-800">টাইটেলের নিচে (মাঝে)</div>
                                        <div class="text-[10px] text-neutral-500 mt-0.5">ডিফল্ট আধুনিক লেআউট</div>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="citizen_photo_position" value="right" class="sr-only photoPosRadio" <?= (($theme_config['citizen_photo_position'] ?? '') === 'right') ? 'checked' : ''; ?>>
                                    <div class="border-select-card p-3 rounded-xl text-center transition">
                                        <div class="text-xs font-bold text-neutral-800">ডানপাশে (Right Top)</div>
                                        <div class="text-[10px] text-neutral-500 mt-0.5">ট্রেডিশনাল কর্নার</div>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="citizen_photo_position" value="left" class="sr-only photoPosRadio" <?= (($theme_config['citizen_photo_position'] ?? '') === 'left') ? 'checked' : ''; ?>>
                                    <div class="border-select-card p-3 rounded-xl text-center transition">
                                        <div class="text-xs font-bold text-neutral-800">বামপাশে (Left Top)</div>
                                        <div class="text-[10px] text-neutral-500 mt-0.5">বাম কর্নারে অবস্থান</div>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="citizen_photo_position" value="none" class="sr-only photoPosRadio" <?= (($theme_config['citizen_photo_position'] ?? '') === 'none') ? 'checked' : ''; ?>>
                                    <div class="border-select-card p-3 rounded-xl text-center transition">
                                        <div class="text-xs font-bold text-neutral-800">ছবি ছাড়া (No Photo)</div>
                                        <div class="text-[10px] text-neutral-500 mt-0.5">ছবি প্রদর্শন বন্ধ</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <label class="theme-toggle-label">
                            <div class="pr-3">
                                <span class="text-xs font-bold text-neutral-900 block">সনদ ট্র্যাকিং বারকোড (Barcode)</span>
                                <span class="text-[11px] text-neutral-500">হেডারে সনদের ডিজিটাল নম্বর বারকোড প্রদর্শন</span>
                            </div>
                            <input type="checkbox" name="show_barcode" id="showBarcodeCheck" class="sr-only theme-toggle-input" <?= !empty($theme_config['show_barcode']) ? 'checked' : ''; ?>>
                            <div class="theme-checkbox-box">
                                <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Step 3 Navigation Buttons -->
                <div class="flex items-center justify-between pt-2">
                    <button type="button" onclick="goToStep(2)" class="px-4 py-2.5 bg-neutral-100 hover:bg-neutral-200 text-neutral-700 rounded-xl text-xs font-bold flex items-center gap-1.5 transition cursor-pointer">
                        <i class="ph-bold ph-arrow-left text-sm"></i>
                        <span>পূর্ববর্তী ধাপ</span>
                    </button>
                    <button type="button" onclick="goToStep(4)" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 shadow-sm transition cursor-pointer">
                        <span>পরবর্তী ধাপ: স্বাক্ষর ও সংরক্ষণ</span>
                        <i class="ph-bold ph-arrow-right text-sm"></i>
                    </button>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- STEP 4: স্বাক্ষর বিন্যাস (২ জন বনাম ৩ জন)   -->
            <!-- ========================================== -->
            <div id="wizardStep4" class="wizard-step-section hidden space-y-4">
                
                <!-- Card 4.1: Signatory Count & Setup (2-Person vs 3-Person Option) -->
                <div class="bg-white rounded-2xl border border-neutral-200/80 shadow-xs p-5 space-y-4">
                    <div class="flex items-center justify-between pb-2.5 border-b border-neutral-100">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                                <i class="ph-bold ph-users-three text-base"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-neutral-900">স্বাক্ষরদাতার সংখ্যা ও বিন্যাস (Signatory Layout)</h3>
                                <p class="text-[11px] text-neutral-500">আপনার ইউনিয়নে সনদে ২ জনের নাকি ৩ জনের স্বাক্ষর ব্যবহৃত হবে?</p>
                            </div>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700">কাস্টমাইজেবল</span>
                    </div>

                    <!-- Signatory Layout Options -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                        <!-- Option 1: 3-Persons (Standard UP) -->
                        <label class="cursor-pointer">
                            <input type="radio" name="signatory_count" value="3_person" class="sr-only signatoryCountRadio" <?= ($signatory_count === '3_person' || $signatory_count === 'auto') ? 'checked' : ''; ?>>
                            <div class="border-select-card p-3 rounded-xl transition flex flex-col justify-between h-full <?= ($signatory_count === '3_person' || $signatory_count === 'auto') ? 'is-active' : ''; ?>">
                                <div>
                                    <div class="flex items-center justify-between gap-1 mb-1">
                                        <strong class="text-xs font-bold text-neutral-900">৩ জনের স্বাক্ষর</strong>
                                        <span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-indigo-100 text-indigo-700">স্ট্যান্ডার্ড</span>
                                    </div>
                                    <span class="text-[10px] text-neutral-500 block leading-tight">মেম্বার + সচিব + চেয়ারম্যান (৩টি কলাম)</span>
                                </div>
                                <div class="mt-2 text-[9px] text-indigo-600 font-semibold flex items-center gap-1">
                                    <i class="ph-bold ph-check"></i> সম্পূর্ণ অফিশিয়াল
                                </div>
                            </div>
                        </label>

                        <!-- Option 2: 2-Persons (Secretary + Chairman) -->
                        <label class="cursor-pointer">
                            <input type="radio" name="signatory_count" value="2_person" class="sr-only signatoryCountRadio" <?= ($signatory_count === '2_person') ? 'checked' : ''; ?>>
                            <div class="border-select-card p-3 rounded-xl transition flex flex-col justify-between h-full <?= ($signatory_count === '2_person') ? 'is-active' : ''; ?>">
                                <div>
                                    <div class="flex items-center justify-between gap-1 mb-1">
                                        <strong class="text-xs font-bold text-neutral-900">২ জনের স্বাক্ষর</strong>
                                        <span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-neutral-100 text-neutral-700">ট্রেড/সাধারণ</span>
                                    </div>
                                    <span class="text-[10px] text-neutral-500 block leading-tight">প্রশাসনিক কর্মকর্তা + চেয়ারম্যান (২টি কলাম)</span>
                                </div>
                                <div class="mt-2 text-[9px] text-indigo-600 font-semibold flex items-center gap-1">
                                    <i class="ph-bold ph-check"></i> ক্লিন ডাবল ব্লক
                                </div>
                            </div>
                        </label>

                        <!-- Option 3: 1-Person (Only Chairman) -->
                        <label class="cursor-pointer">
                            <input type="radio" name="signatory_count" value="1_person" class="sr-only signatoryCountRadio" <?= ($signatory_count === '1_person') ? 'checked' : ''; ?>>
                            <div class="border-select-card p-3 rounded-xl transition flex flex-col justify-between h-full <?= ($signatory_count === '1_person') ? 'is-active' : ''; ?>">
                                <div>
                                    <div class="flex items-center justify-between gap-1 mb-1">
                                        <strong class="text-xs font-bold text-neutral-900">১ জনের স্বাক্ষর</strong>
                                        <span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-neutral-100 text-neutral-700">একক</span>
                                    </div>
                                    <span class="text-[10px] text-neutral-500 block leading-tight">শুধুমাত্র চেয়ারম্যান (মাঝে)</span>
                                </div>
                                <div class="mt-2 text-[9px] text-indigo-600 font-semibold flex items-center gap-1">
                                    <i class="ph-bold ph-check"></i> একক স্বাক্ষর
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Card 4.2: Individual Signature Toggles & Footer -->
                <div class="bg-white rounded-2xl border border-neutral-200/80 shadow-xs p-5 space-y-4">
                    <div class="flex items-center gap-2 pb-2.5 border-b border-neutral-100">
                        <div class="w-7 h-7 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center">
                            <i class="ph-bold ph-signature text-base"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-neutral-900">স্বাক্ষরদাতা টগল ও ডিজিটাল সিলমোহর</h3>
                            <p class="text-[11px] text-neutral-500">নির্দিষ্ট ব্যক্তির স্বাক্ষর প্রদর্শন বা গোপন রাখুন</p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <!-- Chairman Sign Toggle -->
                        <label class="theme-toggle-label">
                            <div class="pr-3">
                                <span class="text-xs font-bold text-neutral-900 block">১. চেয়ারম্যান স্বাক্ষর ও লাল সিলমোহর</span>
                                <span class="text-[11px] text-neutral-500"><?= $has_cm_sign ? 'আপলোডকৃত ডিজিটাল স্বাক্ষর ও গোল্ডেন/লাল সিল' : 'চেয়ারম্যান স্বাক্ষর ব্লক সক্রিয় থাকবে'; ?></span>
                            </div>
                            <input type="checkbox" name="show_chairman_sign" id="showChairmanSignCheck" class="sr-only theme-toggle-input" <?= !empty($theme_config['show_chairman_sign']) ? 'checked' : ''; ?>>
                            <div class="theme-checkbox-box">
                                <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            </div>
                        </label>

                        <!-- Secretary Sign Toggle -->
                        <label class="theme-toggle-label">
                            <div class="pr-3">
                                <span class="text-xs font-bold text-neutral-900 block">২. প্রশাসনিক কর্মকর্তা স্বাক্ষর ব্লক</span>
                                <span class="text-[11px] text-neutral-500">প্রশাসনিক কর্মকর্তার স্বাক্ষর ব্লক</span>
                            </div>
                            <input type="checkbox" name="show_sec_sign" id="showSecSignCheck" class="sr-only theme-toggle-input" <?= (!isset($theme_config['show_sec_sign']) || !empty($theme_config['show_sec_sign'])) ? 'checked' : ''; ?>>
                            <div class="theme-checkbox-box">
                                <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            </div>
                        </label>

                        <!-- Member Sign Toggle -->
                        <label class="theme-toggle-label">
                            <div class="pr-3">
                                <span class="text-xs font-bold text-neutral-900 block">৩. সংশ্লিষ্ট ইউপি সদস্য (মেম্বার) স্বাক্ষর ব্লক</span>
                                <span class="text-[11px] text-neutral-500">ওয়ার্ড মেম্বারের ভেরিফিকেশন ও স্বাক্ষর ব্লক</span>
                            </div>
                            <input type="checkbox" name="show_member_sign" id="showMemberSignCheck" class="sr-only theme-toggle-input" <?= !empty($theme_config['show_member_sign']) ? 'checked' : ''; ?>>
                            <div class="theme-checkbox-box">
                                <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            </div>
                        </label>

                        <!-- QR Code Toggle -->
                        <label class="theme-toggle-label">
                            <div class="pr-3">
                                <span class="text-xs font-bold text-neutral-900 block">অনলাইন ভেরিফিকেশন QR কোড</span>
                                <span class="text-[11px] text-neutral-500">ফুটারের ডানে সরাসরি স্ক্যান করে যাচাই করার QR কোড</span>
                            </div>
                            <input type="checkbox" name="show_qr_code" id="showQrCodeCheck" class="sr-only theme-toggle-input" <?= !empty($theme_config['show_qr_code']) ? 'checked' : ''; ?>>
                            <div class="theme-checkbox-box">
                                <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            </div>
                        </label>

                        <div>
                            <label for="footerTextInput" class="block text-xs font-bold text-neutral-700 mb-1.5">ফুটার স্লোগান / ব্র্যান্ডিং নোট</label>
                            <input type="text" id="footerTextInput" name="footer_text" 
                                   value="<?= htmlspecialchars($theme_config['footer_text'] ?? 'ডিজিটাল ইউনিয়ন পরিষদ সেবা', ENT_QUOTES, 'UTF-8'); ?>"
                                   placeholder="উদাঃ ডিজিটাল ইউনিয়ন পরিষদ সেবা"
                                   class="w-full text-xs px-3.5 py-2.5 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                        </div>
                    </div>
                </div>

                <!-- Card 4.3: Summary & Quick Actions -->
                <div class="p-4 rounded-2xl bg-indigo-50/70 border border-indigo-200/80 flex items-center justify-between gap-3 text-xs text-indigo-950">
                    <div class="flex items-center gap-2">
                        <i class="ph-bold ph-sparkle text-indigo-600 text-lg"></i>
                        <span>সবগুলো ধাপ প্রস্তুত! পরিবর্তনসমূহ চূড়ান্ত করতে নিচের বাটনে চাপুন।</span>
                    </div>
                    <button type="button" id="resetDefaultsBtn" class="px-3 py-1.5 rounded-lg bg-white border border-indigo-200 hover:bg-indigo-100 text-indigo-700 font-bold transition shrink-0 cursor-pointer">
                        ডিফল্ট রিসেট
                    </button>
                </div>

                <!-- Step 4 Navigation Buttons -->
                <div class="flex items-center justify-between pt-2">
                    <button type="button" onclick="goToStep(3)" class="px-4 py-2.5 bg-neutral-100 hover:bg-neutral-200 text-neutral-700 rounded-xl text-xs font-bold flex items-center gap-1.5 transition cursor-pointer">
                        <i class="ph-bold ph-arrow-left text-sm"></i>
                        <span>পূর্ববর্তী ধাপ</span>
                    </button>
                    <button type="button" id="saveThemeBtn" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white rounded-xl text-xs font-bold shadow-md transition flex items-center gap-2 cursor-pointer">
                        <i class="ph-bold ph-floppy-disk text-base"></i>
                        <span>থিম সেটিংস সংরক্ষণ করুন</span>
                    </button>
                </div>

            </div>

        </form>
    </div>

    <!-- Live Preview Column (Right, Authentic Tall A4 Portrait Mockup) -->
    <div class="lg:col-span-6 sticky top-4 space-y-2.5">
        
        <!-- Preview Toolbar Header -->
        <div class="bg-neutral-900 text-white rounded-2xl p-3 shadow-lg flex items-center justify-between gap-2">
            <div class="flex items-center gap-2 min-w-0">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse shrink-0"></span>
                <span class="text-xs font-bold tracking-wide truncate">A4 সনদ লাইভ প্রিভিউ (Portrait View)</span>
            </div>
            <div class="flex items-center gap-1.5 shrink-0">
                <span id="prevModeBadge" class="text-[10px] px-2 py-0.5 rounded-full bg-neutral-800 text-indigo-300 font-semibold border border-neutral-700">
                    <?= ($design_mode === 'custom_upload') ? 'কাস্টম প্যাড মোড' : 'ডিজিটাল থিম মোড'; ?>
                </span>
                <div class="hidden sm:flex items-center gap-0.5 bg-neutral-800 rounded-lg p-0.5 border border-neutral-700 text-[10px]">
                    <button type="button" onclick="setPreviewZoom(0.85)" class="px-1.5 py-0.5 rounded hover:bg-neutral-700 text-neutral-300 transition" title="ছোট স্ক্রিন ফিট">৮৫%</button>
                    <button type="button" onclick="setPreviewZoom(1.0)" class="px-1.5 py-0.5 rounded bg-indigo-600 text-white font-bold transition" title="আসল মাপ">১০০%</button>
                </div>
            </div>
        </div>

        <!-- Zoomable Frame Wrapper -->
        <div id="previewZoomWrapper" class="transition-transform origin-top duration-200">
            <!-- Authentic A4 Portrait Certificate Mockup Frame -->
            <div id="previewCardFrame" class="a4-portrait-mockup font-sans">
                
                <!-- Custom Uploaded Pad Artwork Layer -->
                <div id="prevCustomPadLayer" class="absolute inset-0 pointer-events-none select-none transition-all duration-300 <?= ($design_mode === 'custom_upload' && !empty($custom_pad_url)) ? '' : 'hidden'; ?>"
                     style="background-image: url('<?= $custom_pad_url; ?>'); background-size: 100% 100%; background-repeat: no-repeat; opacity: <?= (float)($theme_config['custom_pad_opacity'] ?? 1.0); ?>;">
                </div>

                <!-- Official Certificate Border Frame Overlay Layer -->
                <div id="prevBorderFrameOverlay" class="absolute inset-0 pointer-events-none select-none transition-all duration-300 z-5 <?= (($theme_config['border_design'] ?? 'border_1') === 'none' || $design_mode === 'custom_upload') ? 'hidden' : ''; ?>"
                     style="background-image: url('<?= base_url('inc/assets/sonod_img/borders/' . ($theme_config['border_design'] ?? 'border_1') . '.png'); ?>'); background-size: 100% 100%; background-repeat: no-repeat;">
                </div>

                <!-- Dynamic Watermark Element (Centered) -->
                <img id="prevWatermarkImg" src="<?= ($theme_config['watermark_type'] === 'up_logo' && $has_up_logo) ? $tenant_logo_url : $govt_logo_url; ?>" 
                     alt="Watermark" 
                     class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 pointer-events-none select-none transition-opacity duration-200 z-4"
                     style="width: 270px; height: 270px; opacity: <?= $theme_config['watermark_opacity'] ?? 0.06; ?>; <?= empty($theme_config['watermark_visible']) ? 'display:none;' : ''; ?>">

                <!-- Dynamic Inner Border Wrapper -->
                <div id="prevBorderWrapper" class="relative z-10 m-4 p-5 rounded-lg transition-all duration-200 flex-1 flex flex-col justify-between" 
                     style="border: 2px solid #059669;">
                    
                    <!-- Top Section: Header + Meta + Title + Photo -->
                    <div>
                        <!-- Header -->
                        <div class="flex items-center justify-between gap-3 pb-3 border-b border-neutral-200/90">
                            <!-- Left: Govt Logo -->
                            <div id="prevGovtLogoCol" class="w-13 h-13 shrink-0 flex items-center justify-center">
                                <img src="<?= $govt_logo_url; ?>" alt="Govt Logo" class="w-12 h-12 object-contain drop-shadow-2xs">
                            </div>

                            <!-- Center: UP Title & Government line -->
                            <div class="text-center flex-1 px-1">
                                <div class="text-[11px] font-semibold text-neutral-600 tracking-wide">গণপ্রজাতন্ত্রী বাংলাদেশ সরকার</div>
                                <div id="prevUpName" class="text-base font-extrabold text-neutral-900 tracking-tight leading-tight mt-0.5 font-serif">
                                    <?= htmlspecialchars($up_name); ?>
                                </div>
                                <div class="text-[10px] text-neutral-600 font-medium">
                                    <?= !empty($upazila_name) ? ('উপজেলা: ' . htmlspecialchars($upazila_name) . ', ') : ''; ?>
                                    <?= !empty($dist_name) ? ('জেলা: ' . htmlspecialchars($dist_name) . '।') : ''; ?>
                                </div>
                                <div id="prevContactRow" class="text-[10px] text-neutral-500 font-medium mt-0.5">
                                    যোগাযোগ: <?= htmlspecialchars($ins_info->socib_phone ?? '০১৮১৩১৫২৭৯৯'); ?>
                                </div>
                            </div>

                            <!-- Right: Barcode & UP Logo -->
                            <div class="shrink-0 flex items-center gap-2 text-right">
                                <div id="prevBarcodeBox" class="space-y-0.5">
                                    <div class="bg-neutral-800 text-white font-mono text-[8px] px-1.5 py-0.5 rounded tracking-widest text-center shadow-2xs">||||||||||||||</div>
                                    <div class="text-[8.5px] text-neutral-500 font-medium">সনদ নং: ২০২৬০১০১</div>
                                </div>
                                <div id="prevUpLogoCol" class="w-12 h-12 shrink-0 flex items-center justify-center <?= empty($theme_config['show_up_logo']) ? 'hidden' : ''; ?>">
                                    <img src="<?= $tenant_logo_url; ?>" alt="UP Logo" class="w-11 h-11 object-contain rounded-full border border-neutral-200 shadow-2xs">
                                </div>
                            </div>
                        </div>

                        <!-- Meta Row -->
                        <div class="flex items-center justify-between py-2 text-[10px] font-semibold text-neutral-600 border-b border-neutral-100">
                            <span>ইস্যুর তারিখঃ ০৯/১০/২০২৬ ইং</span>
                            <div class="flex items-center gap-1">
                                <span>সনদ নংঃ</span>
                                <span class="inline-flex gap-0.5 font-mono font-bold text-neutral-900">
                                    <span class="px-1 border border-neutral-300 rounded text-[9px] bg-neutral-50">২</span>
                                    <span class="px-1 border border-neutral-300 rounded text-[9px] bg-neutral-50">০</span>
                                    <span class="px-1 border border-neutral-300 rounded text-[9px] bg-neutral-50">২</span>
                                    <span class="px-1 border border-neutral-300 rounded text-[9px] bg-neutral-50">৬</span>
                                    <span class="px-1 border border-neutral-300 rounded text-[9px] bg-neutral-50">০</span>
                                    <span class="px-1 border border-neutral-300 rounded text-[9px] bg-neutral-50">১</span>
                                </span>
                            </div>
                        </div>

                        <!-- Title Badge & Citizen Photo (Dynamic Positioning) -->
                        <div id="prevTitlePhotoContainer" class="my-3 flex flex-col items-center justify-center relative min-h-11.5" style="min-height: 46px;">
                            
                            <!-- Left Corner Photo Slot -->
                            <div id="prevPhotoLeftSlot" class="hidden absolute left-0 top-0">
                                <div class="w-16 h-20 rounded border border-neutral-300 bg-neutral-100 flex flex-col items-center justify-center text-[10px] text-neutral-500 font-bold shadow-xs p-1">
                                    <i class="ph ph-user text-lg text-neutral-400"></i>
                                    <span class="text-[9px]">নাগরিক ছবি</span>
                                </div>
                            </div>

                            <!-- Center Title Badge -->
                            <div class="text-center">
                                <span id="prevTitleBadge" class="inline-block px-6 py-1.5 rounded-full text-xs font-bold text-white shadow-xs transition-colors duration-200 border border-white/30"
                                      style="background-color: #059669;">
                                    নাগরিকত্ব সনদপত্র
                                </span>
                            </div>

                            <!-- Under Title Photo Slot (Default) -->
                            <div id="prevPhotoUnderSlot" class="mt-2.5">
                                <div class="w-16 h-20 rounded border border-neutral-300 bg-neutral-100 flex flex-col items-center justify-center text-[10px] text-neutral-500 font-bold shadow-xs p-1">
                                    <i class="ph ph-user text-lg text-neutral-400"></i>
                                    <span class="text-[9px]">নাগরিক ছবি</span>
                                </div>
                            </div>

                            <!-- Right Corner Photo Slot -->
                            <div id="prevPhotoRightSlot" class="hidden absolute right-0 top-0">
                                <div class="w-16 h-20 rounded border border-neutral-300 bg-neutral-100 flex flex-col items-center justify-center text-[10px] text-neutral-500 font-bold shadow-xs p-1">
                                    <i class="ph ph-user text-lg text-neutral-400"></i>
                                    <span class="text-[9px]">নাগরিক ছবি</span>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Middle Section: Citizen Details & Statement (Dynamic Layout Container) -->
                    <div id="prevCitizenBodyContainer" class="my-3 flex-1 flex flex-col justify-center">
                        <div class="bg-neutral-50/70 border border-neutral-200/60 rounded-lg p-3 my-2 text-[11px] space-y-1.5 shadow-2xs">
                            <div class="grid grid-cols-2 gap-2">
                                <div><strong class="text-neutral-700">নাগরিকের নাম:</strong> মোহাম্মদ আব্দুল্লাহ</div>
                                <div><strong class="text-neutral-700">পিতার নাম:</strong> মোঃ রফিকুল ইসলাম</div>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div><strong class="text-neutral-700">মাতার নাম:</strong> রহিমা বেগম</div>
                                <div><strong class="text-neutral-700">জাতীয় পরিচয়পত্র:</strong> ১৯৯২০০২৬০১০১০০১</div>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div><strong class="text-neutral-700">গ্রাম/মহল্লা:</strong> ধানমন্ডি</div>
                                <div><strong class="text-neutral-700">ওয়ার্ড নং:</strong> ০৩ নং ওয়ার্ড</div>
                            </div>
                        </div>
                        <div class="text-[11px] leading-relaxed text-neutral-700 text-justify indent-6 my-2">
                            অত্র ইউনিয়নের একজন স্থায়ী বাসিন্দা। তিনি জন্মগতভাবে বাংলাদেশী এবং আমার সুপরিচিত। তিনি রাষ্ট্রবিরোধী কোনো কাজের সাথে জড়িত নন। তাহার স্বভাব ও চরিত্র প্রশংসনীয়। আমি তাহার সর্বাঙ্গীণ মঙ্গল ও উন্নতি কামনা করি।
                        </div>
                    </div>

                    <!-- Bottom Section: Signatures & Seal (Dynamic 3 vs 2 vs 1 Column) -->
                    <div>
                        <!-- Signatures Dynamic Container -->
                        <div id="prevSignaturesGrid" class="grid grid-cols-3 gap-3 pt-4 mt-2 border-t border-neutral-200/90 items-end relative text-center">
                            
                            <!-- Member Sign Column -->
                            <div id="prevMemberSignCol" class="text-center">
                                <div class="border-b border-dashed border-neutral-400 w-24 mx-auto mb-1"></div>
                                <div class="text-[10px] font-bold text-neutral-800">সংশ্লিষ্ট ওয়ার্ড সদস্য</div>
                                <div class="text-[8.5px] text-neutral-500"><?= htmlspecialchars($up_name); ?></div>
                            </div>

                            <!-- Secretary Sign Column -->
                            <div id="prevSecSignCol" class="text-center">
                                <div class="border-b border-dashed border-neutral-400 w-24 mx-auto mb-1"></div>
                                <div class="text-[10px] font-bold text-neutral-800">প্রশাসনিক কর্মকর্তা</div>
                                <div class="text-[8.5px] text-neutral-500"><?= htmlspecialchars($up_name); ?></div>
                            </div>

                            <!-- Chairman Sign Column + Red Rubber Seal Stamp -->
                            <div id="prevChairmanSignCol" class="text-center relative">
                                <!-- Red Rubber Seal Stamp -->
                                <div class="rubber-seal-stamp absolute -top-8 right-1">
                                    <div class="rubber-seal-stamp-inner">
                                        <span>গণপ্রজাতন্ত্রী</span>
                                        <span>বাংলাদেশ সরকার</span>
                                        <span class="text-[5.5px] text-rose-700">★ চেয়ারম্যান ★</span>
                                    </div>
                                </div>

                                <?php if ($has_cm_sign): ?>
                                    <img src="<?= media_url($ins_info->cm_sign_s64); ?>" alt="Chairman Sign" class="h-8 mx-auto mb-1 object-contain">
                                <?php else: ?>
                                    <div class="border-b border-dashed border-neutral-400 w-24 mx-auto mb-1"></div>
                                <?php endif; ?>
                                <div class="text-[10px] font-bold text-neutral-800">চেয়ারম্যান</div>
                                <div class="text-[8.5px] text-neutral-500"><?= htmlspecialchars($up_name); ?></div>
                            </div>
                        </div>

                        <!-- Footer Row -->
                        <div class="flex items-center justify-between pt-3 mt-3 border-t border-neutral-100 text-[9px] text-neutral-500">
                            <div class="flex items-center gap-1 truncate">
                                <i class="ph ph-globe text-xs text-neutral-400"></i>
                                <span class="font-mono"><?= function_exists('tenant_hostname') ? tenant_hostname() : 'upsheba.com'; ?></span>
                            </div>
                            <div id="prevFooterText" class="font-medium text-center px-2 truncate">
                                <?= htmlspecialchars($theme_config['footer_text'] ?? 'ডিজিটাল ইউনিয়ন পরিষদ সেবা', ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                            <div id="prevQrBox" class="flex items-center gap-1 shrink-0">
                                <span class="text-[8.5px] font-medium">যাচাইকরণ</span>
                                <div class="w-6 h-6 border border-neutral-300 rounded bg-neutral-100 flex items-center justify-center font-mono text-[7px] font-bold text-neutral-700 shadow-2xs">QR</div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>

    </div>

</div>

<!-- SCRIPT LOGIC -->
<script>
    var currentStep = 1;

    function goToStep(step) {
        if (step < 1 || step > 4) return;
        currentStep = step;

        // Toggle step sections
        $('.wizard-step-section').addClass('hidden');
        $('#wizardStep' + step).removeClass('hidden');

        // Toggle nav button active state
        $('.step-nav-btn').removeClass('active-step');
        $('#stepTab' + step).addClass('active-step');

        // Toggle step numbers
        $('.step-num').removeClass('bg-indigo-600 text-white').addClass('bg-neutral-200 text-neutral-600');
        $('#stepTab' + step + ' .step-num').removeClass('bg-neutral-200 text-neutral-600').addClass('bg-indigo-600 text-white');

        // Smooth scroll to top of wizard on mobile
        if (window.innerWidth < 1024) {
            var el = document.getElementById('stepperNavHeader');
            if (el) el.scrollIntoView({ behavior: 'smooth' });
        }
    }

    // Step 2 Internal Sub-Tab Switcher
    function switchStep2SubTab(subTab, btn) {
        $('.substep-pill').removeClass('is-active');
        $(btn).addClass('is-active');

        $('.substep-panel').addClass('hidden');
        if (subTab === 'borders') {
            $('#subPanelBorders').removeClass('hidden');
        } else if (subTab === 'layouts') {
            $('#subPanelLayouts').removeClass('hidden');
        } else if (subTab === 'watermark') {
            $('#subPanelWatermark').removeClass('hidden');
        }
    }

    function setPreviewZoom(scale) {
        $('#previewZoomWrapper').css('transform', 'scale(' + scale + ')');
    }

    // Border category filter (Reliable with !important class)
    function filterBorderCategory(cat, btn) {
        $('.border-cat-pill').removeClass('is-active');
        $(btn).addClass('is-active');

        $('.border-item-wrap').each(function () {
            var itemCat = $(this).data('cat') || '';
            if (cat === 'all' || itemCat === cat) {
                $(this).removeClass('is-filtered-out');
            } else {
                $(this).addClass('is-filtered-out');
            }
        });
    }

    // Border search filter
    function searchBorderCards(query) {
        var q = query.toLowerCase().trim();
        $('.border-item-wrap').each(function () {
            var title = $(this).data('title') || '';
            if (!q || title.indexOf(q) !== -1) {
                $(this).removeClass('is-filtered-out');
            } else {
                $(this).addClass('is-filtered-out');
            }
        });
    }

    var defaultColors = {
        emerald: { primary: '#065f46', border: '#065f46' },
        indigo:  { primary: '#1e3a8a', border: '#1e3a8a' },
        slate:   { primary: '#1e293b', border: '#334155' },
        crimson: { primary: '#881337', border: '#881337' }
    };

    var logoUrls = {
        bd_gov: "<?= $govt_logo_url; ?>",
        up_logo: "<?= $tenant_logo_url; ?>"
    };

    function previewCustomPadFile(input) {
        if (input.files && input.files[0]) {
            var file = input.files[0];
            var reader = new FileReader();
            reader.onload = function(e) {
                var dataUrl = e.target.result;
                $('#customPadThumbnail').attr('src', dataUrl);
                $('#customPadFilename').text(file.name);
                $('#customPadStatusCard').removeClass('hidden');
                $('#removeCustomPadInput').val('0');

                $('#prevCustomPadLayer').css('background-image', 'url(' + dataUrl + ')').removeClass('hidden');
                $('#prevModeBadge').text('কাস্টম প্যাড মোড');
            };
            reader.readAsDataURL(file);
        }
    }

    function removeCustomPadArtwork() {
        $('#customPadFileInput').val('');
        $('#customPadPathInput').val('');
        $('#removeCustomPadInput').val('1');
        $('#customPadThumbnail').attr('src', 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
        $('#customPadStatusCard').addClass('hidden');
        $('#prevCustomPadLayer').addClass('hidden');
        $('#prevModeBadge').text('ডিজিটাল থিম মোড');
        $('input[name="design_mode"][value="digital"]').prop('checked', true).trigger('change');
    }

    function updatePadOpacity(val) {
        var pct = Math.round(val * 100);
        $('#padOpacityBadge').text(pct + '%');
        $('#prevCustomPadLayer').css('opacity', val);
    }

    function syncAllUIStates() {
        // Design Mode Radio
        var designMode = $('input[name="design_mode"]:checked').val() || 'digital';
        $('.designModeRadio').each(function () {
            var $card = $(this).siblings('.theme-radio-card');
            var $badge = $card.find('.radio-check-badge');
            if ($(this).is(':checked')) {
                $card.addClass('active-mode');
                $badge.addClass('is-visible');
            } else {
                $card.removeClass('active-mode');
                $badge.removeClass('is-visible');
            }
        });

        if (designMode === 'custom_upload') {
            $('#customPadUploadBox').removeClass('hidden');
            $('#prevModeBadge').text('কাস্টম প্যাড মোড');
            if ($('#customPadThumbnail').attr('src') && $('#customPadThumbnail').attr('src').indexOf('R0lGOD') === -1) {
                $('#prevCustomPadLayer').removeClass('hidden');
            }
        } else {
            $('#customPadUploadBox').addClass('hidden');
            $('#prevCustomPadLayer').addClass('hidden');
            $('#prevModeBadge').text('ডিজিটাল থিম মোড');
        }

        // Theme Color Radio
        var activeTheme = $('input[name="theme_color"]:checked').val() || 'emerald';
        $('.themeColorRadio').each(function () {
            var val = $(this).val();
            var $card = $(this).siblings('.theme-radio-card');
            var $badge = $card.find('.radio-check-badge');
            $card.removeClass('active-emerald active-indigo active-slate active-crimson active-custom');
            $badge.removeClass('is-visible');

            if ($(this).is(':checked')) {
                $card.addClass('active-' + val);
                $badge.addClass('is-visible');
            }
        });

        if (activeTheme === 'custom') {
            $('#customColorBox').removeClass('hidden');
        } else {
            $('#customColorBox').addClass('hidden');
        }

        // Watermark Source Radios
        $('.watermarkTypeRadio').each(function () {
            var $card = $(this).siblings('.watermark-select-card');
            var $badge = $card.find('.radio-check-badge');
            if ($(this).is(':checked')) {
                $card.addClass('is-active');
                $badge.addClass('is-visible');
            } else {
                $card.removeClass('is-active');
                $badge.removeClass('is-visible');
            }
        });

        // Signatory Count Radios
        $('.signatoryCountRadio').each(function () {
            var $card = $(this).siblings('.border-select-card');
            if ($(this).is(':checked')) {
                $card.addClass('is-active');
            } else {
                $card.removeClass('is-active');
            }
        });

        // Toggle Checkboxes styling
        $('.theme-toggle-input').each(function () {
            var $label = $(this).closest('.theme-toggle-label');
            var $box = $label.find('.theme-checkbox-box');
            if ($(this).is(':checked')) {
                $label.addClass('is-checked');
                $box.addClass('is-checked');
            } else {
                $label.removeClass('is-checked');
                $box.removeClass('is-checked');
            }
        });

        // Border Design Radios
        $('.borderDesignRadio').each(function () {
            var $card = $(this).siblings('.border-design-card');
            var $badge = $card.find('.radio-check-badge');
            if ($(this).is(':checked')) {
                $card.addClass('is-active');
                $badge.addClass('is-visible');
            } else {
                $card.removeClass('is-active');
                $badge.removeClass('is-visible');
            }
        });

        // Text Layout Radios
        $('.textLayoutRadio').each(function () {
            var $card = $(this).siblings('.layout-select-card');
            var $badge = $card.find('.radio-check-badge');
            if ($(this).is(':checked')) {
                $card.addClass('is-active');
                $badge.addClass('is-visible');
            } else {
                $card.removeClass('is-active');
                $badge.removeClass('is-visible');
            }
        });

        // Citizen Photo Position Radios
        $('.photoPosRadio').each(function () {
            var $card = $(this).siblings('.border-select-card');
            if ($(this).is(':checked')) {
                $card.addClass('is-active');
            } else {
                $card.removeClass('is-active');
            }
        });
    }

    function renderPreviewTextLayout(layout) {
        var html = '';
        if (layout === 'layout_narrative') {
            html = '<div class="text-[11px] leading-relaxed text-neutral-800 text-justify indent-4 my-2 p-2.5 bg-neutral-50/70 rounded border border-neutral-200/60 leading-[1.8] shadow-2xs">' +
                   '<strong>এই মর্মে প্রত্যয়ন / সনদপত্র প্রদান করা যাইতেছে যে,</strong> জনাব <strong>মোহাম্মদ আব্দুল্লাহ</strong>, পিতা: <strong>মোঃ রফিকুল ইসলাম</strong>, মাতা: <strong>রহিমা বেগম</strong>, জাতীয় পরিচয়পত্র নং: <strong>১৯৯২০০২৬০১০১০০১</strong>। তাঁহার বর্তমান ঠিকানা: গ্রাম: ধানমন্ডি, ওয়ার্ড নং: ০৩, উপজেলা: সদর, জেলা: ফেনী এবং স্থায়ী ঠিকানা: একই। তিনি অত্র ইউনিয়নের একজন স্থায়ী বাসিন্দা এবং সৎ ও চরিত্রবান নাগরিক।<br><br><strong>আমি তাহার সর্বাঙ্গীণ মঙ্গল ও উজ্জ্বল ভবিষ্যৎ কামনা করি।</strong>' +
                   '</div>';
        } else if (layout === 'layout_modern_cards') {
            html = '<div class="space-y-1.5 my-2">' +
                   '<div class="p-2.5 bg-indigo-50/40 rounded border border-indigo-100 text-[10.5px] shadow-2xs">' +
                   '<strong class="text-indigo-950 block font-bold mb-0.5">নাগরিক প্রোফাইল ও বিবরণ</strong>' +
                   '<div class="grid grid-cols-2 gap-1 text-neutral-700"><div>নাম: আব্দুল্লাহ</div><div>পিতা: রফিকুল ইসলাম</div><div>মাতা: রহিমা বেগম</div><div>এনআইডি: ১৯৯২০০২৬০১০১</div></div>' +
                   '</div>' +
                   '<div class="p-2 bg-neutral-50 rounded border border-neutral-200/70 text-[10px] text-neutral-600">' +
                   '<strong class="text-neutral-800 block mb-0.5">ঠিকানা</strong>গ্রাম: ধানমন্ডি, ওয়ার্ড: ০৩, উপজেলা: সদর, জেলা: ফেনী' +
                   '</div>' +
                   '<div class="p-2 bg-emerald-50/40 rounded border border-emerald-100 text-[10px] text-emerald-950 font-medium">' +
                   'তিনি অত্র ইউনিয়নের স্থায়ী বাসিন্দা ও জন্মসূত্রে বাংলাদেশী।' +
                   '</div>' +
                   '</div>';
        } else if (layout === 'layout_bullet_secretariat') {
            html = '<div class="text-[10.5px] space-y-1 my-2 p-2.5 bg-neutral-50/80 rounded border border-neutral-200/60 leading-relaxed shadow-2xs">' +
                   '<div><span class="font-bold text-indigo-700">(১) নাগরিকের নাম:</span> মোহাম্মদ আব্দুল্লাহ</div>' +
                   '<div><span class="font-bold text-indigo-700">(২) পিতা/স্বামীর নাম:</span> মোঃ রফিকুল ইসলাম</div>' +
                   '<div><span class="font-bold text-indigo-700">(৩) মাতার নাম:</span> রহিমা বেগম</div>' +
                   '<div><span class="font-bold text-indigo-700">(৪) এনআইডি / জন্ম নিবন্ধন:</span> ১৯৯২০০২৬০১০১০০১</div>' +
                   '<div><span class="font-bold text-indigo-700">(৫) বর্তমান ও স্থায়ী ঠিকানা:</span> গ্রাম: ধানমন্ডি, ওয়ার্ড: ০৩, ফেনী</div>' +
                   '<div><span class="font-bold text-indigo-700">(৬) প্রত্যয়ন ও চারিত্রিক সনদ:</span> স্বভাব চরিত্র প্রশংসনীয় ও সততা সর্বজনবিদিত।</div>' +
                   '</div>';
        } else if (layout === 'layout_bordered_table') {
            html = '<table class="w-full border-collapse border border-neutral-300 text-[10.5px] my-2 shadow-2xs">' +
                   '<tbody>' +
                   '<tr class="bg-neutral-100"><td class="border border-neutral-300 p-1 font-bold text-indigo-900 w-1/4">নাগরিকের নাম</td><td class="border border-neutral-300 p-1 w-1/4">আব্দুল্লাহ</td><td class="border border-neutral-300 p-1 font-bold text-indigo-900 w-1/4">পিতার নাম</td><td class="border border-neutral-300 p-1 w-1/4">রফিকুল ইসলাম</td></tr>' +
                   '<tr><td class="border border-neutral-300 p-1 font-bold text-indigo-900">মাতার নাম</td><td class="border border-neutral-300 p-1">রহিমা বেগম</td><td class="border border-neutral-300 p-1 font-bold text-indigo-900">এনআইডি</td><td class="border border-neutral-300 p-1">১৯৯২০০২৬০১০১</td></tr>' +
                   '<tr class="bg-neutral-50"><td class="border border-neutral-300 p-1 font-bold text-indigo-900">ঠিকানা</td><td colspan="3" class="border border-neutral-300 p-1">গ্রাম: ধানমন্ডি, ওয়ার্ড: ০৩, উপজেলা: সদর, জেলা: ফেনী</td></tr>' +
                   '</tbody>' +
                   '</table>' +
                   '<div class="text-[10.5px] text-neutral-600 my-1">তিনি অত্র ইউনিয়নের স্থায়ী বাসিন্দা ও জন্মগতভাবে বাংলাদেশী।</div>';
        } else if (layout === 'layout_centered_royal') {
            html = '<div class="text-center my-2 p-2.5 bg-neutral-50/50 rounded border border-neutral-200/50 shadow-2xs">' +
                   '<div class="text-[10px] text-neutral-500 font-semibold">এই মর্মে অত্যন্ত আনন্দের সাথে প্রত্যয়ন করা যাইতেছে যে,</div>' +
                   '<div class="text-sm font-bold text-indigo-900 my-1">মোহাম্মদ আব্দুল্লাহ</div>' +
                   '<div class="text-[10px] text-neutral-600">পিতা: রফিকুল ইসলাম &nbsp;|&nbsp; মাতা: রহিমা বেগম &nbsp;|&nbsp; এনআইডি: ১৯৯২০০২৬০১</div>' +
                   '<div class="text-[10px] text-neutral-500 border-t border-b border-neutral-200 my-1.5 py-0.5">গ্রাম: ধানমন্ডি, ওয়ার্ড নং: ০৩, সদর, ফেনী</div>' +
                   '<div class="text-[10.5px] text-neutral-700 leading-tight">তিনি অত্র ইউনিয়নের স্থায়ী বাসিন্দা ও জন্মগতভাবে বাংলাদেশী।</div>' +
                   '</div>';
        } else if (layout === 'layout_legal_banner') {
            html = '<div class="my-2 space-y-1.5">' +
                   '<div class="p-2.5 bg-emerald-50 rounded border-l-4 border-emerald-600 text-[10.5px] shadow-2xs">' +
                   '<div class="flex justify-between font-bold text-emerald-950"><span>মোহাম্মদ আব্দুল্লাহ</span><span class="text-indigo-800">এনআইডি: ১৯৯২০০২৬০১</span></div>' +
                   '<div class="text-neutral-600 text-[10px] mt-0.5">পিতা: মোঃ রফিকুল ইসলাম &bull; মাতা: রহিমা বেগম</div>' +
                   '</div>' +
                   '<div class="text-[10px] text-neutral-600 px-1">ঠিকানা: গ্রাম: ধানমন্ডি, ওয়ার্ড: ০৩, সদর, ফেনী</div>' +
                   '<div class="p-2 bg-neutral-50 rounded border-l-3 border-indigo-600 text-[10px] text-neutral-700 leading-tight">তিনি অত্র ইউনিয়নের স্থায়ী বাসিন্দা ও সুনামধন্য নাগরিক।</div>' +
                   '</div>';
        } else if (layout === 'layout_minimal_dots') {
            html = '<div class="text-[10.5px] space-y-1.5 my-2 p-2 shadow-2xs">' +
                   '<div class="flex gap-2"><span>নাম: <strong class="border-b border-dotted border-neutral-400">মোহাম্মদ আব্দুল্লাহ</strong></span><span>পিতা: <strong class="border-b border-dotted border-neutral-400">মোঃ রফিকুল ইসলাম</strong></span></div>' +
                   '<div class="flex gap-2"><span>মাতা: <strong class="border-b border-dotted border-neutral-400">রহিমা বেগম</strong></span><span>এনআইডি: <strong class="border-b border-dotted border-neutral-400">১৯৯২০০২৬০১০০১</strong></span></div>' +
                   '<div>ঠিকানা: <span class="border-b border-dotted border-neutral-400">গ্রাম: ধানমন্ডি, ওয়ার্ড: ০৩, সদর, ফেনী</span></div>' +
                   '<div class="text-[10px] text-neutral-700 pt-1">তিনি একজন স্থায়ী বাসিন্দা ও সৎ নাগরিক।</div>' +
                   '</div>';
        } else { // layout_grid_classic
            html = '<div class="bg-neutral-50/70 border border-neutral-200/60 rounded-lg p-3 my-2 text-[11px] space-y-1.5 shadow-2xs">' +
                   '<div class="grid grid-cols-2 gap-2"><div><strong class="text-neutral-700">নাগরিকের নাম:</strong> মোহাম্মদ আব্দুল্লাহ</div><div><strong class="text-neutral-700">পিতার নাম:</strong> মোঃ রফিকুল ইসলাম</div></div>' +
                   '<div class="grid grid-cols-2 gap-2"><div><strong class="text-neutral-700">মাতার নাম:</strong> রহিমা বেগম</div><div><strong class="text-neutral-700">জাতীয় পরিচয়পত্র:</strong> ১৯৯২০০২৬০১০১০০১</div></div>' +
                   '<div class="grid grid-cols-2 gap-2"><div><strong class="text-neutral-700">গ্রাম/মহল্লা:</strong> ধানমন্ডি</div><div><strong class="text-neutral-700">ওয়ার্ড নং:</strong> ০৩ নং ওয়ার্ড</div></div>' +
                   '</div>' +
                   '<div class="text-[11px] leading-relaxed text-neutral-700 text-justify indent-6 my-2">' +
                   'অত্র ইউনিয়নের একজন স্থায়ী বাসিন্দা। তিনি জন্মগতভাবে বাংলাদেশী এবং আমার সুপরিচিত। তিনি রাষ্ট্রবিরোধী কোনো কাজের সাথে জড়িত নন। তাহার স্বভাব ও চরিত্র প্রশংসনীয়। আমি তাহার সর্বাঙ্গীণ মঙ্গল ও উন্নতি কামনা করি।' +
                   '</div>';
        }
        $('#prevCitizenBodyContainer').html(html);
    }

    function updateLivePreview() {
        var themeColor = $('input[name="theme_color"]:checked').val() || 'emerald';
        var borderDesign = $('input[name="border_design"]:checked').val() || 'border_1';
        var textLayout   = $('input[name="text_layout"]:checked').val() || 'layout_grid_classic';
        var designMode   = $('input[name="design_mode"]:checked').val() || 'digital';
        var signatoryCount = $('input[name="signatory_count"]:checked').val() || '3_person';
        var watermarkVisible = $('#watermarkVisibleCheck').is(':checked');
        var watermarkType = $('input[name="watermark_type"]:checked').val() || 'bd_gov';
        var opacityVal = parseFloat($('#opacityRangeInput').val()) || 0.06;
        var showGovtLogo = $('#showGovtLogoCheck').is(':checked');
        var showUpLogo = $('#showUpLogoCheck').is(':checked');
        var showContact = $('#showContactCheck').is(':checked');
        var showBarcode = $('#showBarcodeCheck').is(':checked');
        var showChairmanSign = $('#showChairmanSignCheck').is(':checked');
        var showSecSign = $('#showSecSignCheck').is(':checked');
        var showMemberSign = $('#showMemberSignCheck').is(':checked');
        var showQrCode = $('#showQrCodeCheck').is(':checked');
        var footerText = $('#footerTextInput').val();
        var photoPos = $('input[name="citizen_photo_position"]:checked').val() || 'under_title';

        // Resolve colors
        var primaryCol = '#065f46';
        var borderCol  = '#065f46';

        if (themeColor === 'custom') {
            primaryCol = $('#customColorPicker').val() || '#2563eb';
            borderCol  = $('#customBorderPicker').val() || primaryCol;
            $('#customColorDot').css('background-color', primaryCol);
            $('#customColorBadge').text(primaryCol.toUpperCase());
        } else if (defaultColors[themeColor]) {
            primaryCol = defaultColors[themeColor].primary;
            borderCol  = defaultColors[themeColor].border;
        }

        // Apply primary color to title badge
        $('#prevTitleBadge').css('background-color', primaryCol);

        // Apply border frame overlay in preview
        if (designMode === 'digital' && borderDesign !== 'none') {
            var borderUrl = '<?= base_url("inc/assets/sonod_img/borders/"); ?>' + borderDesign + '.png';
            $('#prevBorderFrameOverlay').removeClass('hidden').css('background-image', 'url("' + borderUrl + '")');
            $('#prevBorderWrapper').css({
                'border-width': '1px',
                'border-style': 'solid',
                'border-color': 'rgba(203, 213, 225, 0.4)',
                'box-shadow': 'none'
            });
        } else {
            $('#prevBorderFrameOverlay').addClass('hidden');
            $('#prevBorderWrapper').css({
                'border-width': '2px',
                'border-style': 'solid',
                'border-color': borderCol,
                'box-shadow': 'none'
            });
        }

        // Render selected text layout in preview
        renderPreviewTextLayout(textLayout);

        // Watermark
        if (watermarkVisible) {
            $('#prevWatermarkImg').show().css('opacity', opacityVal);
            $('#opacityBadge').text(Math.round(opacityVal * 100) + '%');
            if (logoUrls[watermarkType]) {
                $('#prevWatermarkImg').attr('src', logoUrls[watermarkType]);
            }
        } else {
            $('#prevWatermarkImg').hide();
        }

        // Logos & header elements
        if (showGovtLogo) {
            $('#prevGovtLogoCol').show();
        } else {
            $('#prevGovtLogoCol').hide();
        }

        if (showUpLogo) {
            $('#prevUpLogoCol').show();
        } else {
            $('#prevUpLogoCol').hide();
        }

        if (showContact) {
            $('#prevContactRow').show();
        } else {
            $('#prevContactRow').hide();
        }

        if (showBarcode) {
            $('#prevBarcodeBox').show();
        } else {
            $('#prevBarcodeBox').hide();
        }

        // Photo positioning
        $('#prevPhotoLeftSlot, #prevPhotoUnderSlot, #prevPhotoRightSlot').addClass('hidden');
        if (photoPos === 'under_title') {
            $('#prevPhotoUnderSlot').removeClass('hidden');
        } else if (photoPos === 'left') {
            $('#prevPhotoLeftSlot').removeClass('hidden');
        } else if (photoPos === 'right') {
            $('#prevPhotoRightSlot').removeClass('hidden');
        }

        // Signatures Dynamic Layout (3-Person vs 2-Person vs 1-Person)
        var visibleSignsCount = 0;
        if (showChairmanSign) visibleSignsCount++;
        if (showSecSign && (signatoryCount === '3_person' || signatoryCount === '2_person' || signatoryCount === 'auto')) visibleSignsCount++;
        if (showMemberSign && (signatoryCount === '3_person' || signatoryCount === 'auto')) visibleSignsCount++;

        // Update Grid Columns Class & Inline Styles
        if (signatoryCount === '1_person' || visibleSignsCount === 1) {
            $('#prevSignaturesGrid').css('grid-template-columns', 'repeat(1, 1fr)');
            $('#prevMemberSignCol, #prevSecSignCol').hide();
            if (showChairmanSign) $('#prevChairmanSignCol').show(); else $('#prevChairmanSignCol').hide();
        } else if (signatoryCount === '2_person' || visibleSignsCount === 2) {
            $('#prevSignaturesGrid').css('grid-template-columns', 'repeat(2, 1fr)');
            $('#prevMemberSignCol').hide();
            if (showSecSign) $('#prevSecSignCol').show(); else $('#prevSecSignCol').hide();
            if (showChairmanSign) $('#prevChairmanSignCol').show(); else $('#prevChairmanSignCol').hide();
        } else { // 3-Person mode
            $('#prevSignaturesGrid').css('grid-template-columns', 'repeat(3, 1fr)');
            if (showMemberSign) $('#prevMemberSignCol').show(); else $('#prevMemberSignCol').hide();
            if (showSecSign) $('#prevSecSignCol').show(); else $('#prevSecSignCol').hide();
            if (showChairmanSign) $('#prevChairmanSignCol').show(); else $('#prevChairmanSignCol').hide();
        }

        if (showQrCode) {
            $('#prevQrBox').show();
        } else {
            $('#prevQrBox').hide();
        }

        $('#prevFooterText').text(footerText || 'ডিজিটাল ইউনিয়ন পরিষদ সেবা');
    }

    // Swatches click
    $(document).on('click', '.quick-swatch', function () {
        var col = $(this).data('color');
        $('#customColorPicker').val(col);
        $('#customColorHex').val(col);
        $('#customBorderPicker').val(col);
        $('#customBorderHex').val(col);
        $('input[name="theme_color"][value="custom"]').prop('checked', true);
        syncAllUIStates();
        updateLivePreview();
    });

    // Custom color picker inputs
    $('#customColorPicker').on('input change', function () {
        var col = $(this).val();
        $('#customColorHex').val(col);
        if ($('input[name="theme_color"]:checked').val() !== 'custom') {
            $('input[name="theme_color"][value="custom"]').prop('checked', true);
        }
        syncAllUIStates();
        updateLivePreview();
    });

    $('#customColorHex').on('input change', function () {
        var col = $(this).val();
        if (/^#[0-9A-Fa-f]{6}$/.test(col)) {
            $('#customColorPicker').val(col);
            syncAllUIStates();
            updateLivePreview();
        }
    });

    $('#customBorderPicker').on('input change', function () {
        var col = $(this).val();
        $('#customBorderHex').val(col);
        if ($('input[name="theme_color"]:checked').val() !== 'custom') {
            $('input[name="theme_color"][value="custom"]').prop('checked', true);
        }
        syncAllUIStates();
        updateLivePreview();
    });

    $('#customBorderHex').on('input change', function () {
        var col = $(this).val();
        if (/^#[0-9A-Fa-f]{6}$/.test(col)) {
            $('#customBorderPicker').val(col);
            syncAllUIStates();
            updateLivePreview();
        }
    });

    // Global change listeners
    $(document).on('change', '#themeConfigForm input[type="radio"], #themeConfigForm input[type="checkbox"]', function () {
        syncAllUIStates();
        updateLivePreview();
    });

    $(document).on('input', '#opacityRangeInput, #footerTextInput', function () {
        updateLivePreview();
    });

    // Reset Defaults
    $('#resetDefaultsBtn').on('click', function () {
        $('input[name="design_mode"][value="digital"]').prop('checked', true);
        $('input[name="theme_color"][value="emerald"]').prop('checked', true);
        $('input[name="border_style"][value="double"]').prop('checked', true);
        $('#watermarkVisibleCheck').prop('checked', true);
        $('input[name="watermark_type"][value="bd_gov"]').prop('checked', true);
        $('#opacityRangeInput').val(0.06);
        $('#showGovtLogoCheck').prop('checked', true);
        $('#showUpLogoCheck').prop('checked', true);
        $('#showContactCheck').prop('checked', true);
        $('#showBarcodeCheck').prop('checked', true);
        $('input[name="signatory_count"][value="3_person"]').prop('checked', true);
        $('#showChairmanSignCheck').prop('checked', true);
        $('#showSecSignCheck').prop('checked', true);
        $('#showMemberSignCheck').prop('checked', true);
        $('#showQrCodeCheck').prop('checked', true);
        $('input[name="border_design"][value="border_1"]').prop('checked', true);
        $('input[name="text_layout"][value="layout_grid_classic"]').prop('checked', true);
        $('input[name="citizen_photo_position"][value="under_title"]').prop('checked', true);
        $('#footerTextInput').val('ডিজিটাল ইউনিয়ন পরিষদ সেবা');
        syncAllUIStates();
        updateLivePreview();
    });

    // Save Theme Settings via AJAX FormData
    $('#saveThemeBtn').on('click', function () {
        var $btn = $(this);
        $btn.prop('disabled', true).addClass('opacity-70');

        var formElement = document.getElementById('themeConfigForm');
        var formData = new FormData(formElement);

        $.ajax({
            type: "POST",
            url: "saveSonodTheme",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (res) {
                $btn.prop('disabled', false).removeClass('opacity-70');
                if (res && res.status === 'success') {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'সফলভাবে সংরক্ষিত!',
                            text: res.message || 'সনদ থিম সেটিংস সফলভাবে আপডেট করা হয়েছে।',
                            confirmButtonColor: '#4f46e5'
                        });
                    } else {
                        alert(res.message || 'সনদ থিম সেটিংস সফলভাবে আপডেট করা হয়েছে।');
                    }
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'ব্যর্থ হয়েছে',
                            text: (res && res.message) ? res.message : 'সংরক্ষণ করা সম্ভব হয়নি।',
                            confirmButtonColor: '#4f46e5'
                        });
                    } else {
                        alert((res && res.message) ? res.message : 'সংরক্ষণ করা সম্ভব হয়নি।');
                    }
                }
            },
            error: function () {
                $btn.prop('disabled', false).removeClass('opacity-70');
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'সার্ভার এরর',
                        text: 'সার্ভারের সাথে যোগাযোগ করা সম্ভব হয়নি।',
                        confirmButtonColor: '#4f46e5'
                    });
                } else {
                    alert('সার্ভারের সাথে যোগাযোগ করা সম্ভব হয়নি।');
                }
            }
        });
    });

    // Initial Execution
    $(document).ready(function () {
        syncAllUIStates();
        updateLivePreview();
        goToStep(1);
    });
</script>
