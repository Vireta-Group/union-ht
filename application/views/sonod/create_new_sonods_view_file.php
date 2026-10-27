<!-- SweetAlert2 & Select2 -->
<link rel="stylesheet" href="inc/assets/vendor/sweetalert2/dist/sweetalert2.min.css">
<link type="text/css" href="inc/assets/css/vendor-select2.css" rel="stylesheet">
<link type="text/css" href="inc/assets/vendor/select2/select2.min.css" rel="stylesheet">

<!-- Breadcrumb & Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-neutral-200">
    <div>
        <nav class="flex items-center gap-2 text-xs text-neutral-500 mb-1">
            <a href="dashboard" class="hover:text-indigo-600 transition flex items-center gap-1">
                <i class="ph ph-house text-sm"></i> ড্যাশবোর্ড
            </a>
            <i class="ph ph-caret-right text-[10px] text-neutral-400"></i>
            <span class="text-neutral-500">সনদ ব্যবস্থাপনা</span>
            <i class="ph ph-caret-right text-[10px] text-neutral-400"></i>
            <span class="text-neutral-800 font-medium">নতুন সনদ তৈরি</span>
        </nav>
        <h1 class="text-xl sm:text-2xl font-bold text-neutral-900 tracking-tight flex items-center gap-2">
            <span class="w-2.5 h-6 bg-indigo-600 rounded-full inline-block"></span>
            নতুন ডিজিটাল সনদ প্রদান
        </h1>
        <p class="text-xs sm:text-sm text-neutral-500 mt-0.5">নাগরিকের জাতীয় পরিচয়পত্র বা জন্ম নিবন্ধন যাচাই করে তাৎক্ষণিক সনদ তৈরি করুন</p>
    </div>
</div>

<!-- Alert Messages -->
<div class="successSonodMsg"></div>

<!--
    SONOD WIZARD — four steps, one panel at a time, no scrolling down.

    WHY THIS IS A SCOPED <style> BLOCK AND NOT TAILWIND
    There is no package.json in this repo, so `npm run build` cannot run and
    inc/assets/css/portal.css is committed build output. Tailwind v4 emits only
    the utilities the view tree used at build time, so a NEW utility silently
    does nothing — not an error, the element just renders unstyled. Verified:
    portal.css contains `sm:grid-cols-3` but NOT the bare `grid-cols-3`.
    Everything wizard-specific therefore lives here under an `snw-` prefix,
    where a CSS rebuild cannot drop it and it cannot silently no-op. Cosmetics
    reuse classes already present in portal.css.
-->
<style>
    /* ---- horizontal step bar ------------------------------------------
       EVERY .snw-* SELECTOR BELOW IS [dir=ltr]-SCOPED ON PURPOSE.
       This view renders inside p/mdk_open.php's <div dir="ltr">, and
       app.css (MDK, 3.1 MB, loaded behind $ht_shell) contains literally:

           [dir=ltr] article, ..., [dir=ltr] nav, [dir=ltr] section {
               display: block; }

       The step bar is a <nav> and every panel is a <section>, so that rule
       forced display:block and the "horizontal" bar rendered as a vertical
       stack. [dir=ltr] nav is specificity (0,1,1); a bare .snw-bar is (0,1,0)
       and loses. Prefixing the wizard's own selectors with [dir=ltr] makes
       them (0,2,0) and they win. display:flex on the bar additionally
       carries !important because "did it become a horizontal bar" is not a
       thing worth debugging again — nothing here should need to override it.
       Do NOT "clean up" the [dir=ltr] prefixes; they are load-bearing. */
    [dir=ltr] .snw-bar {
        display: flex !important;
        align-items: stretch;
        background: rgba(255, 255, 255, 0.88) !important;
        backdrop-filter: blur(14px) saturate(180%) !important;
        -webkit-backdrop-filter: blur(14px) saturate(180%) !important;
        border: 1px solid rgba(226, 232, 240, 0.85) !important;
        border-radius: 1.25rem !important;
        padding: .625rem !important;
        margin-bottom: 1.25rem !important;
        box-shadow: 
            0 1px 2px rgba(0, 0, 0, 0.02),
            0 8px 24px -4px rgba(79, 70, 229, 0.06),
            inset 0 1px 0 rgba(255, 255, 255, 0.95) !important;
        overflow-x: auto;
    }
    [dir=ltr] .snw-step {
        display: flex;
        align-items: center;
        gap: .55rem;
        flex: 1 1 0;
        min-width: 0;
        padding: .55rem .6rem;
        border: 0;
        background: transparent;
        border-radius: .85rem;
        cursor: pointer;
        text-align: left;
        font-family: inherit;
        transition: background-color .18s ease, transform .15s ease;
    }
    [dir=ltr] .snw-step:hover:not(:disabled) { 
        background: rgba(241, 245, 249, 0.8); 
    }
    [dir=ltr] .snw-step:disabled { cursor: default; }
    /* The connector lives on the STEP rather than between the dot and the
       label, so a long label truncating on a narrow screen cannot shift it. */
    [dir=ltr] .snw-step + .snw-step { position: relative; }
    [dir=ltr] .snw-step + .snw-step::before {
        content: '';
        position: absolute;
        left: -.5rem;
        top: 50%;
        width: 1rem;
        height: 2px;
        background: #e2e8f0;
        transform: translateY(-50%);
        transition: background-color .2s ease;
    }
    [dir=ltr] .snw-step.is-done + .snw-step::before,
    [dir=ltr] .snw-step.is-done + .snw-step.is-current::before { background: #6366f1; }

    [dir=ltr] .snw-dot {
        flex: 0 0 auto;
        width: 1.85rem;
        height: 1.85rem;
        border-radius: 9999px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .75rem;
        font-weight: 700;
        background: #f8fafc;
        color: #94a3b8;
        border: 1px solid #e2e8f0;
        box-shadow: inset 0 1px 0 #fff;
        transition: all .2s cubic-bezier(0.16, 1, 0.3, 1);
    }
    [dir=ltr] .snw-step.is-current .snw-dot { 
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%) !important; 
        border-color: #4338ca !important; 
        color: #fff !important; 
        box-shadow: 0 0 0 3.5px rgba(99, 102, 241, 0.22), 0 4px 12px rgba(79, 70, 229, 0.4) !important;
    }
    [dir=ltr] .snw-step.is-done .snw-dot { 
        background: #eef2ff !important; 
        border-color: #c7d2fe !important; 
        color: #4f46e5 !important; 
        box-shadow: 0 2px 6px rgba(79, 70, 229, 0.12) !important;
    }

    [dir=ltr] .snw-label { min-width: 0; }
    [dir=ltr] .snw-label b {
        display: block;
        font-size: .75rem;
        font-weight: 700;
        color: #171717;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    [dir=ltr] .snw-step:not(.is-current):not(.is-done) .snw-label b { color: #94a3b8; }
    [dir=ltr] .snw-label span {
        display: block;
        font-size: .625rem;
        color: #94a3b8;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    [dir=ltr] .snw-step.is-current .snw-label span { color: #4f46e5; font-weight: 600; }

    /* ---- panels: exactly one open at a time --------------------------- */
    [dir=ltr] .snw-panel {
        display: none;
        background: #ffffff !important;
        border: 1px solid rgba(226, 232, 240, 0.85) !important;
        border-radius: 1.25rem !important;
        box-shadow: 
            0 1px 3px rgba(15, 23, 42, 0.02),
            0 12px 32px -6px rgba(15, 23, 42, 0.06),
            inset 0 1px 0 rgba(255, 255, 255, 0.95) !important;
        margin-bottom: 1.25rem !important;
        overflow: hidden;
    }
    [dir=ltr] .snw-panel.is-active { display: block; }
    [dir=ltr] .snw-head {
        display: flex;
        align-items: center;
        gap: .85rem;
        padding: 1.15rem 1.35rem;
        border-bottom: 1px solid rgba(241, 245, 249, 0.9);
        background: linear-gradient(180deg, rgba(248, 250, 252, 0.75) 0%, rgba(255, 255, 255, 0) 100%);
    }
    [dir=ltr] .snw-head-icon {
        width: 2.25rem;
        height: 2.25rem;
        border-radius: .625rem;
        background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
        border: 1px solid rgba(199, 210, 254, 0.6);
        color: #4f46e5;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        box-shadow: 0 2px 6px rgba(79, 70, 229, 0.12), inset 0 1px 0 #fff;
    }
    [dir=ltr] .snw-body { padding: 1.35rem; }

    /* ---- verify row: full-width inputs ---------------------------------
       The NID/BRN row is a sm:grid-cols-12 and needs each field to span the
       whole grid. sm:col-span-12 is NOT in inc/assets/css/portal.css — this
       repo has no package.json, so `npm run build` cannot run and Tailwind v4
       only ever emitted the utilities the view tree happened to use. Verified
       present: sm:col-span-3, sm:grid-cols-12. Absent: sm:col-span-1/2/4/6/10/12.
       An unknown class is not a CSS error, it just does nothing — which is why
       the NID box used to collapse to a single cell and a 10-17 digit number
       had nowhere to show. Hence an explicit grid-column instead of a utility.
       Do NOT swap this back for sm:col-span-12 without checking portal.css. */
    /* A full-row cell. No panel uses it right now — step 1's number/DOB pair sits
       on one row via .snw-verify-row instead — but it is the escape hatch when
       a later step needs one field to own the whole width, and it is the same
       guarantee sm:col-span-12 cannot give from this repo. */
    [dir=ltr] .snw-full { grid-column: 1 / -1 !important; }

    /* ---- step 1 verify row: number + DOB side by side ------------------
       The number field and the date field sit on ONE row on desktop, with the
       number getting the larger share so a 17-digit value is readable without
       the box scrolling its own text. Below 640px everything stacks.

       grid-template-columns is written out here rather than left to Tailwind
       because this repo cannot run its Tailwind build — see the note above
       .snw-full. 1.75fr / 1fr is roughly what a 17-digit number needs next to
       a YYYY-MM-DD date plus its calendar icon.

       align-items: start is deliberate: the number cell carries the digit
       counter underneath it, so it is taller than the DOB cell. Aligning to
       the top keeps both inputs on the same baseline instead of lifting the
       DOB input to match the counter. */
    [dir=ltr] .snw-verify-row {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: .75rem;
        align-items: start;
    }
    @media (min-width: 640px) {
        [dir=ltr] .snw-verify-row {
            grid-template-columns: minmax(0, 1.75fr) minmax(0, 1fr);
        }
        /* the verify button is a full row of its own, right-aligned so it does
            not sit under the number field where the eye already is */
        [dir=ltr] .snw-verify-row > .snw-verify-btn-cell {
            grid-column: 1 / -1;
            justify-self: end;
        }
        [dir=ltr] .snw-verify-btn { width: auto; }
    }
    /* A finished step collapses to this single line. This is what stops the
       page growing downwards as the user advances — the point of the wizard. */
    [dir=ltr] .snw-summary {
        display: flex;
        align-items: center;
        gap: .75rem;
        padding: .875rem 1.25rem;
        font-size: .75rem;
        color: #404040;
    }
    [dir=ltr] .snw-summary b { font-weight: 600; color: #171717; }
    [dir=ltr] .snw-sum-text { flex: 1 1 auto; min-width: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    [dir=ltr] .snw-edit {
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        border: 1px solid #cbd5e1;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        color: #334155;
        font-size: .6875rem;
        font-weight: 600;
        padding: .35rem .75rem;
        border-radius: .625rem;
        cursor: pointer;
        font-family: inherit;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04), inset 0 1px 0 #fff;
        transition: all .18s cubic-bezier(0.16, 1, 0.3, 1);
    }
    [dir=ltr] .snw-edit:hover { 
        background: #ffffff; 
        border-color: #94a3b8; 
        color: #0f172a;
        transform: translateY(-1px);
        box-shadow: 0 3px 8px rgba(15, 23, 42, 0.08), inset 0 1px 0 #fff;
    }
    [dir=ltr] .snw-edit:active {
        transform: translateY(1px) scale(0.98);
    }

    @media (max-width: 640px) {
        [dir=ltr] .snw-step { flex: 0 0 auto; padding: .5rem .35rem; }
        [dir=ltr] .snw-label { display: none; }
        [dir=ltr] .snw-step + .snw-step::before { left: -.35rem; width: .7rem; }
        [dir=ltr] .snw-body { padding: 1rem; }
        [dir=ltr] .snw-head { padding: .875rem 1rem; }
        [dir=ltr] .snw-summary { padding: .75rem 1rem; }
    }

    /* ---- Modern Select2 Custom Theme ---- */
    [dir=ltr] .select2-container .select2-selection--single {
        height: 46px !important;
        background: #ffffff !important;
        border: 1.5px solid #e2e8f0 !important;
        border-radius: 0.75rem !important;
        padding: 0.6rem 1rem !important;
        display: flex !important;
        align-items: center !important;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04) !important;
    }
    [dir=ltr] .select2-container--bootstrap4.select2-container--focus .select2-selection--single,
    [dir=ltr] .select2-container--open .select2-selection--single {
        border-color: #6366f1 !important;
        background-color: #ffffff !important;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15) !important;
        outline: none !important;
    }
    [dir=ltr] .select2-container .select2-selection--single .select2-selection__rendered {
        color: #0f172a !important;
        font-size: 0.875rem !important;
        font-weight: 600 !important;
        line-height: normal !important;
        padding: 0 !important;
    }
    [dir=ltr] .select2-container .select2-selection--single .select2-selection__placeholder {
        color: #94a3b8 !important;
        font-weight: 500 !important;
    }
    [dir=ltr] .select2-selection__arrow {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        height: 100% !important;
        width: 36px !important;
        right: 4px !important;
        top: 0 !important;
        position: absolute !important;
    }
    [dir=ltr] .select2-selection__arrow b {
        border-color: #64748b transparent transparent transparent !important;
        border-width: 5px 4.5px 0 4.5px !important;
        transition: transform 0.2s ease !important;
    }
    [dir=ltr] .select2-container--open .select2-selection__arrow b {
        border-color: transparent transparent #6366f1 transparent !important;
        border-width: 0 4.5px 5px 4.5px !important;
    }
    [dir=ltr] .select2-dropdown {
        border: 1px solid #e2e8f0 !important;
        border-radius: 0.875rem !important;
        background: #ffffff !important;
        box-shadow: 0 12px 30px -4px rgba(15, 23, 42, 0.12), 0 4px 12px -2px rgba(15, 23, 42, 0.08) !important;
        overflow: hidden !important;
        padding: 0.4rem !important;
        margin-top: 5px !important;
        z-index: 99999 !important;
    }
    [dir=ltr] .select2-search--dropdown {
        padding: 0.35rem 0.35rem 0.6rem !important;
        border-bottom: 1px solid #f1f5f9 !important;
        position: relative !important;
    }
    [dir=ltr] .select2-search--dropdown .select2-search__field {
        height: 40px !important;
        border: 1.5px solid #cbd5e1 !important;
        border-radius: 0.625rem !important;
        background: #f8fafc !important;
        padding: 0.45rem 0.75rem 0.45rem 2.15rem !important;
        font-size: 0.8125rem !important;
        color: #0f172a !important;
        font-family: inherit !important;
        transition: all 0.15s ease !important;
    }
    [dir=ltr] .select2-search--dropdown .select2-search__field:focus {
        border-color: #6366f1 !important;
        background: #ffffff !important;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12) !important;
        outline: none !important;
    }
    [dir=ltr] .select2-results__options {
        max-height: 260px !important;
        overflow-y: auto !important;
        padding: 0.25rem 0 !important;
    }
    [dir=ltr] .select2-results__option {
        padding: 0.65rem 0.85rem !important;
        font-size: 0.8125rem !important;
        font-weight: 500 !important;
        color: #334155 !important;
        border-radius: 0.5rem !important;
        margin-bottom: 2px !important;
        transition: all 0.15s ease !important;
        cursor: pointer !important;
    }
    [dir=ltr] .select2-results__option--highlighted {
        background: #eef2ff !important;
        color: #4338ca !important;
        font-weight: 600 !important;
    }
    [dir=ltr] .select2-results__option[aria-selected="true"] {
        background: #4f46e5 !important;
        color: #ffffff !important;
        font-weight: 600 !important;
    }
</style>



<!-- ============ STEP BAR ============ -->
<nav class="snw-bar" id="snwBar" aria-label="সনদ তৈরির ধাপ">
    <button type="button" class="snw-step is-current" data-goto="1" aria-current="step">
        <span class="snw-dot">১</span>
        <span class="snw-label"><b>পরিচয় যাচাই</b><span>যাচাই পদ্ধতি ও নম্বর</span></span>
    </button>
    <button type="button" class="snw-step" data-goto="2" disabled>
        <span class="snw-dot">২</span>
        <span class="snw-label"><b>নাগরিকের তথা</b><span>নাম, পিতা-মাতা, ঠিকানা</span></span>
    </button>
    <button type="button" class="snw-step" data-goto="3" disabled>
        <span class="snw-dot">৩</span>
        <span class="snw-label"><b>সনদের ধরন</b><span>ভাষা ও সনদ নির্বাচন</span></span>
    </button>
    <button type="button" class="snw-step" data-goto="4" disabled>
        <span class="snw-dot">৪</span>
        <span class="snw-label"><b>বিবরণ ও প্রকাশ</b><span>সনদের বিবরণ, তৈরি করুন</span></span>
    </button>
</nav>

<!-- Step 1: Verification Options Card -->
<section class="snw-panel is-active" data-step="1">
    <div class="snw-head">
        <span class="snw-head-icon"><i class="ph-bold ph-identification-card text-base"></i></span>
        <span>
            <b class="block text-xs font-bold text-neutral-900">নাগরিক পরিচয় যাচাইকরণ পদ্ধতি</b>
            <span class="block text-[11px] text-neutral-500">যাচাইকরণের মাধ্যম নির্বাচন করে প্রয়োজনীয় নম্বর ও জন্ম তারিখ প্রদান করুন</span>
        </span>
    </div>
    <div class="snw-summary" hidden></div>

    <div class="snw-body">
        <!-- Verification Option Radio Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-xl">
            <label class="verifyOptionSection flex items-center gap-3 p-3.5 rounded-xl border-2 border-neutral-200 hover:border-indigo-400 cursor-pointer transition-all has-checked:border-indigo-600 has-checked:bg-indigo-50/40">
                <input class="selectingRadioCheck w-4 h-4 text-indigo-600 focus:ring-indigo-500 cursor-pointer" type="radio" name="verifyOptionSelect" id="inlineRadio1" value="nid" checked>
                <div class="flex items-center gap-2.5">
                    <i class="ph-bold ph-identification-card text-lg text-indigo-600"></i>
                    <div>
                        <span class="block text-xs font-bold text-neutral-900">জাতীয় পরিচয় পত্র (NID)</span>
                        <span class="text-[10px] text-neutral-400">১০ বা ১৭ ডিজিটের এনআইডি</span>
                    </div>
                </div>
            </label>

            <label class="verifyOptionSection flex items-center gap-3 p-3.5 rounded-xl border-2 border-neutral-200 hover:border-indigo-400 cursor-pointer transition-all has-checked:border-indigo-600 has-checked:bg-indigo-50/40">
                <input class="selectingRadioCheck w-4 h-4 text-indigo-600 focus:ring-indigo-500 cursor-pointer" type="radio" name="verifyOptionSelect" id="inlineRadio2" value="birth">
                <div class="flex items-center gap-2.5">
                    <i class="ph-bold ph-baby text-lg text-indigo-600"></i>
                    <div>
                        <span class="block text-xs font-bold text-neutral-900">জন্ম নিবন্ধন সনদ (BRN)</span>
                        <span class="text-[10px] text-neutral-400">১৭ ডিজিটের জন্ম নিবন্ধন নম্বর</span>
                    </div>
                </div>
            </label>
        </div>

        <!-- Dynamic Form Fields Assigned Here -->
        <div class="verifyFormAssign">
            <div class="p-4 bg-neutral-50 rounded-xl border border-neutral-200 space-y-3 mt-4">
                <div class="snw-verify-row">
                    <div>
                        <label class="block text-xs font-semibold text-neutral-700 mb-1">জাতীয় পরিচয়পত্র নম্বর <span class="text-rose-500">*</span></label>
                        <input type="text" inputmode="numeric" maxlength="17" autocomplete="off" class="w-full text-base px-3.5 py-2.5 bg-white border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition nid_number" placeholder="১০ বা ১৩ বা ১৭ সংখ্যার NID নম্বর">
                        <p class="text-[10px] text-neutral-400 mt-1.5 flex items-center justify-between gap-2"><span>নম্বরটি ১০ থেকে ১৭ সংখ্যার হতে পারে।</span><span class="font-semibold text-neutral-500 nid_counter">0 / 17</span></p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-neutral-700 mb-1">জন্ম তারিখ (YYYY-MM-DD) <span class="text-rose-500">*</span></label>
                        <input type="text" class="w-full text-base px-3.5 py-2.5 bg-white border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition datePicks personDOB" placeholder="YYYY-MM-DD" value="" autocomplete="off">
                    </div>
                    <div class="snw-verify-btn-cell">
                        <button class="snw-verify-btn w-full inline-flex items-center justify-center gap-1.5 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold shadow-2xs transition vSysBtn nid_verify cursor-pointer" vid="" vtype="" type="button">
                            <i class="ph-bold ph-magnifying-glass text-xs"></i>
                            <span>যাচাই</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Step 2: Person Information Card -->
<section class="snw-panel" data-step="2">
    <div class="snw-head">
        <span class="snw-head-icon"><i class="ph-bold ph-user-check text-base"></i></span>
        <span>
            <b class="block text-xs font-bold text-neutral-900">নাগরিকের তথাবলী</b>
            <span class="block text-[11px] text-neutral-500">নাম, পিতা-মাতা ও ঠিকানা — যাচাই করা তথ্য এখানে আসবে</span>
        </span>
    </div>
    <div class="snw-summary" hidden></div>
    <div class="snw-body">
        <div class="prsonInfoAssign"></div>
    </div>
</section>

<!-- Step 3: Language & Sonod Type Selection -->
<section class="snw-panel" data-step="3">
    <div class="snw-head">
        <span class="snw-head-icon"><i class="ph-bold ph-certificate text-base"></i></span>
        <span>
            <b class="block text-xs font-bold text-neutral-900">সনদের ভাষা ও ধরন নির্বাচন করুন</b>
            <span class="block text-[11px] text-neutral-500">প্রথমে ভাষা বেছে নিন, তারপর সনদের ধরন</span>
        </span>
    </div>
    <div class="snw-summary" hidden></div>
    <div class="snw-body">
        <div class="btnSelectEnBn flex items-center justify-center gap-3"></div>
        <div class="selectSonodTypes"></div>
    </div>
</section>

<!-- Step 4: Details & Publish -->
<section class="snw-panel" data-step="4">
    <div class="snw-head">
        <span class="snw-head-icon"><i class="ph-bold ph-pencil-simple text-base"></i></span>
        <span>
            <b class="block text-xs font-bold text-neutral-900">সনদের বিবরণ ও প্রকাশ</b>
            <span class="block text-[11px] text-neutral-500">শিরোনাম ও মূল বয়ান দিয়ে সনদ তৈরি করুন</span>
        </span>
    </div>
    <div class="snw-summary" hidden></div>
    <div class="snw-body">
        <!-- Dynamic Sonod Details (e.g. Warish / Death Certificate) -->
        <div class="sonod_details_input_boxes"></div>

        <!-- Rich Text Editor Box -->
        <div class="sonodDetailsTexBox bg-white rounded-2xl border border-neutral-200/80 shadow-xs p-6 space-y-4" style="display: none;">
    <div>
        <label for="sonodTitleLabel" class="block text-xs font-bold text-neutral-700 mb-1.5">সনদের শিরোনাম / হেডিং</label>
        <input class="w-full text-sm px-3.5 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition font-bold text-neutral-900 sonodTitle" type="text" id="sonodTitleLabel" placeholder="সনদের নাম লিখুন...">
    </div>

    <div>
        <label class="block text-xs font-bold text-neutral-700 mb-1.5">সনদের বিস্তারিত বিবরণ</label>
        <div class="rounded-xl overflow-hidden border border-neutral-200">
            <textarea id="rich_editor"></textarea>
        </div>
    </div>

    <div class="text-center pt-2">
        <div class="create_new_sonod_btn inline-block"></div>
    </div>
        </div>
    </div>
</section>

<!-- sweetalert2 -->
<script src="inc/assets/vendor/sweetalert2/dist/sweetalert2.all.min.js"></script>
<script src="inc/assets/vendor/tinymce/js/tinymce/tinymce.js"></script>
<!-- Select2 -->
<script src="inc/assets/vendor/select2/select2.min.js"></script>
<script src="inc/assets/js/select2.js"></script>

<script>
    /*
     * NID / BRN INPUT RULES
     * ---------------------
     * These two fields used to carry onkeypress="return charCode >= 48...".
     * That only filters keystrokes. It does NOT fire for paste, drag-drop or
     * browser autofill, so a 17-digit BRN could be pasted into a field meant
     * for a shorter number, and a non-digit could arrive the same way.
     *
     * The cap is therefore applied on `input`, which every one of those paths
     * goes through, and it strips rather than merely rejects: pasting
     * "1234-567" yields "1234567" instead of silently failing. Stripping
     * non-digits also means the value reaching nid_v is always numeric, so no
     * server-side filter has to catch a stray dash.
     *
     * NID is capped at 17 rather than exactly 10 on purpose: a Bangladesh NID
     * is issued as 10, 13 or 17 digits and all three are in circulation. The
     * label says 10 or 17, and rejecting a 13-digit NID would lock out real
     * citizens. BRN is a fixed 17 and is enforced exactly.
     *
     * Nothing here submits anything. It only shapes what can be typed, and
     * the verify button is still the only thing that calls nid_v — which is
     * also why BRN has never actually been checked against anything server
     * side (see .birth_verifing).
     */
    (function () {
        function updateCounter(inputEl, counterSel, max, exact) {
            if (!inputEl) return;
            var v = (inputEl.value || '').replace(/[^0-9]/g, '');
            if (v.length > max) v = v.slice(0, max);
            if (v !== inputEl.value) inputEl.value = v;
            var len = v.length;
            var $c = window.jQuery(counterSel);
            if ($c.length) {
                $c.text(len + ' / ' + max);
                if (exact) {
                    $c.toggleClass('text-emerald-600', len === max)
                      .toggleClass('text-neutral-500', len !== max);
                } else {
                    $c.toggleClass('text-emerald-600', len >= 10)
                      .toggleClass('text-neutral-500', len < 10);
                }
            }
        }

        // Delegated input listeners so dynamically inserted verification fields always update counter
        window.jQuery(document).on('input paste drop keyup change propertychange', '.nid_number', function () {
            updateCounter(this, '.nid_counter', 17, false);
        });

        window.jQuery(document).on('input paste drop keyup change propertychange', '.birthNumber', function () {
            updateCounter(this, '.birth_counter', 17, true);
        });

        // Enter key in Step 1 verification inputs triggers verification button
        window.jQuery(document).on('keydown', '.nid_number, .birthNumber, .personDOB', function (e) {
            if (e.key === 'Enter' || e.keyCode === 13) {
                e.preventDefault();
                window.jQuery('.vSysBtn:visible').first().trigger('click');
            }
        });

        // Enter key in Step 2 person info inputs triggers next step
        window.jQuery(document).on('keydown', '.nameBN, .nameEN, .fatherName_s_bn, .fatherName_en_s, .motherNameBn_s, .motherName_s_en, .customVillageInput, .customHouseInput', function (e) {
            if (e.key === 'Enter' || e.keyCode === 13) {
                e.preventDefault();
                window.jQuery('.afterVerifyBtnNext:visible').first().trigger('click');
            }
        });

        /* Verify is blocked until the number is well formed, because nid_v
           returns nothing useful for a bad one and the user then gets an
           empty person form with no idea why. This is exposed as a helper the
           verify handlers call, NOT as a second delegated click handler: two
           handlers on the same selector both run, and which one wins depends
           on registration order — so the guard would silently stop working
           the moment the file was edited. */
        window.snwNumberOk = function (isNid) {
            var $ = window.jQuery;
            var $f = $(isNid ? '.nid_number' : '.birthNumber');
            var v = ($f.val() || '').replace(/[^0-9]/g, '');
            var ok = isNid ? (v.length >= 10 && v.length <= 17) : (v.length === 17);
            if (ok) return true;

            window.Swal.fire({
                title: '<strong>যাচাই সংখ্যা ভুল</strong>',
                icon: 'warning',
                html: isNid
                    ? 'জাতীয় পরিচয়পত্র নম্বর <b>১০ থেকে ১৭ সংখ্যার</b> হতে পারে।'
                    : 'জন্ম নিবন্ধন নম্বর <b>ঠিক ১৭ সংখ্যার</b> হতে হবে।',
                confirmButtonText: 'ঠিক আছে'
            });
            return false;
        };
    })();

    /*
     * SONOD WIZARD ENGINE
     * -------------------
     * Four panels, exactly one open at a time. Every panel below the current
     * one collapses to a one-line summary, which is what stops the page from
     * growing downwards as the user advances — the whole point of the change.
     *
     * It is deliberately additive: it only toggles panels and the step bar.
     * It does NOT own the flow. The existing handlers below still decide when
     * a step is reachable, they just call snwAdvance()/snwGoTo() when they do.
     * That matters because the gating logic (an empty vid, an unknown sonod
     * type) lives in those handlers and duplicating it here would let the two
     * disagree.
     *
     * A step may be OPENED only when every step before it has been reached, so
     * the bar cannot be used to skip the identity check.
     */
    var SNW = (function () {
        var LAST = 4;
        var unlocked = 1;          // highest step the user may open
        var current = 1;

        function panels() { return document.querySelectorAll('.snw-panel'); }
        function barSteps() { return document.querySelectorAll('#snwBar .snw-step'); }
        function panelOf(n) { return document.querySelector('.snw-panel[data-step="' + n + '"]'); }

        /* One-line summary of what the user already supplied. Used for the
           collapsed row so the value stays visible without opening the form. */
        function summaryFor(n) {
            var $ = window.jQuery;
            var t;
            if (n === 1) {
                var method = ($('input[name="verifyOptionSelect"]:checked').val() === 'birth')
                    ? 'জন্ম নিবন্ধন নম্বর (BRN)' : 'জাতীয় পরিচয়পত্র (NID)';
                t = $('.birthNumber').val() || $('.nid_number').val();
                return method + (t ? ' — ' + t : '');
            }
            if (n === 2) {
                var nm = $('.nameBN').val() || $('.nameEN').val();
                var ward = $('.selectingWard_no option:selected').val();
                var vilVal = $('.selectingVillage option:selected').val();
                var vil = (vilVal === 'other') ? ($('.customVillageInput').val() || 'অন্যান্য গ্রাম') : $('.selectingVillage option:selected').text();
                var bits = [];
                if (nm) bits.push(nm);
                if (ward) bits.push('ওয়ার্ড ' + ward);
                if (vil && vil.trim() && vil !== 'গ্রাম সিলেক্ট করুন') bits.push(vil.trim());
                return bits.join(', ');
            }
            if (n === 3) {
                var lang = $('.banglaSonodBtnBN').hasClass('shadow-2xs') ? 'বাংলা' : 'English';
                var opt = $('.sonod_selecting_s option:selected');
                var title = opt.length ? opt.text() : '';
                return (title ? lang + ' — ' + title : lang);
            }
            if (n === 4) {
                return $('.sonodTitle').val() || '';
            }
            return '';
        }

        function paint() {
            barSteps().forEach(function (btn, i) {
                var n = i + 1;
                btn.classList.toggle('is-current', n === current);
                btn.classList.toggle('is-done', n < current);
                btn.disabled = n > unlocked;
                if (n === current) btn.setAttribute('aria-current', 'step');
                else btn.removeAttribute('aria-current');
            });

            panels().forEach(function (p) {
                var n = parseInt(p.getAttribute('data-step'), 10);
                var isCurrent = n === current;
                p.classList.toggle('is-active', isCurrent);

                var body = p.querySelector('.snw-body');
                var sum = p.querySelector('.snw-summary');
                if (!body || !sum) return;

                if (isCurrent) {
                    body.hidden = false;
                    sum.hidden = true;
                } else if (n < current) {
                    // finished: collapse to the summary line
                    body.hidden = true;
                    sum.hidden = false;
                    sum.innerHTML = '<i class="ph-bold ph-check-circle text-emerald-600 text-sm"></i>' +
                        '<span class="snw-sum-text">' +
                        (summaryFor(n) ? '<b>' + summaryFor(n) + '</b>' : '') +
                        '</span>' +
                        '<button type="button" class="snw-edit" data-edit="' + n + '">' +
                        '<i class="ph-bold ph-pencil-simple text-xs"></i>সম্পাদনা</button>';
                } else {
                    body.hidden = true;
                    sum.hidden = true;
                }
            });
        }

        function goTo(n, opts) {
            opts = opts || {};
            n = Math.min(Math.max(parseInt(n, 10) || 1, 1), LAST);
            /* Only back-navigation is allowed to be blocked. advance() is the
               flow reporting that a step was just completed, so the lock check
               must not run on that path or no step could ever open. */
            if (opts.respectLock && n > unlocked) return false;
            if (!opts.keepUnlocked) unlocked = Math.max(unlocked, n);
            current = n;
            paint();
            // Only scroll if the panel is genuinely off-screen — normally the
            // page does not grow, so this is a safety net for short viewports.
            if (!opts.noScroll) {
                var p = panelOf(n);
                if (p) {
                    var top = p.getBoundingClientRect().top;
                    if (top < 0 || top > (window.innerHeight || 0) * 0.6) {
                        p.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }
            }
            return true;
        }

        return {
            /* called by the flow when a step has just been completed */
            advance: function (to) {
                var res = goTo(to);
                if (typeof SNW_DRAFT !== 'undefined') SNW_DRAFT.scheduleSave();
                return res;
            },
            /* called by the step bar / the edit button — may only revisit an
               already-reached step, never skip ahead */
            goTo: function (n) { return goTo(n, { keepUnlocked: true, respectLock: true }); },
            /* the sonod type select can land on a type with no form behind it,
               so the caller resets the wizard after a successful POST */
            reset: function () {
                unlocked = 1; current = 1; paint();
                renderVerificationMethod('nid');
            },
            repaint: paint,
            isOpen: function (n) { return current === n; }
        };
    })();

    /* Step bar: only ever moves to a step that is already unlocked. */
    $(document).on('click', '#snwBar .snw-step', function () {
        if ($(this).is(':disabled')) return;
        SNW.goTo($(this).data('goto'));
    });

    /* The edit button inside a collapsed summary row. */
    $(document).on('click', '.snw-edit', function (e) {
        e.preventDefault();
        SNW.goTo($(this).data('edit'));
    });

    tinymce.init({
        selector: '#rich_editor',                         
        menubar: '',
        toolbar: "undo redo | accordion accordionremove | blocks fontfamily fontsize | bold italic underline | align numlist bullist | link image | table media | forecolor backcolor | charmap emoticons | code fullscreen pagebreak anchor codesample | ltr rtl",
        height: 320,
        quickbars_selection_toolbar: 'bold italic | quicklink h2 h3 blockquote quickimage quicktable', 
    });

    function renderVerificationMethod(method) {
        if (!method) {
            method = $('input[name="verifyOptionSelect"]:checked').val() || 'nid';
        }
        $('input[name="verifyOptionSelect"][value="' + method + '"]').prop('checked', true);

        var html = '';
        if (method === 'nid') {
            html = `
                <div class="p-4 bg-neutral-50 rounded-xl border border-neutral-200 space-y-3 mt-4">
                    <div class="snw-verify-row">
                        <div>
                            <label class="block text-xs font-semibold text-neutral-700 mb-1">জাতীয় পরিচয়পত্র নম্বর <span class="text-rose-500">*</span></label>
                            <input type="text" inputmode="numeric" maxlength="17" autocomplete="off" class="w-full text-base px-3.5 py-2.5 bg-white border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition nid_number" placeholder="১০ বা ১৩ বা ১৭ সংখ্যার NID নম্বর">
                            <p class="text-[10px] text-neutral-400 mt-1.5 flex items-center justify-between gap-2"><span>নম্বরটি ১০ থেকে ১৭ সংখ্যার হতে পারে।</span><span class="font-semibold text-neutral-500 nid_counter">0 / 17</span></p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-neutral-700 mb-1">জন্ম তারিখ (YYYY-MM-DD) <span class="text-rose-500">*</span></label>
                            <input type="text" class="w-full text-base px-3.5 py-2.5 bg-white border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition datePicks personDOB" placeholder="YYYY-MM-DD" value="" autocomplete="off">
                        </div>
                        <div class="snw-verify-btn-cell">
                            <button class="snw-verify-btn w-full inline-flex items-center justify-center gap-1.5 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold shadow-2xs transition vSysBtn nid_verify cursor-pointer" vid="" vtype="" type="button">
                                <i class="ph-bold ph-magnifying-glass text-xs"></i>
                                <span>যাচাই</span>
                            </button>
                        </div>
                    </div>
                </div>`;
        } else if (method === 'birth') {
            html = `
                <div class="p-4 bg-neutral-50 rounded-xl border border-neutral-200 space-y-3 mt-4">
                    <div class="snw-verify-row">
                        <div>
                            <label class="block text-xs font-semibold text-neutral-700 mb-1">জন্ম নিবন্ধন নম্বর <span class="text-rose-500">*</span></label>
                            <input type="text" inputmode="numeric" maxlength="17" autocomplete="off" class="w-full text-base px-3.5 py-2.5 bg-white border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition birthNumber" placeholder="১৭ সংখ্যার জন্ম নিবন্ধন নম্বর">
                            <p class="text-[10px] text-neutral-400 mt-1.5 flex items-center justify-between gap-2"><span>জন্ম নিবন্ধন নম্বরটি সর্বোচ্চ ১৭ সংখ্যার হতে পারে।</span><span class="font-semibold text-neutral-500 birth_counter">0 / 17</span></p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-neutral-700 mb-1">জন্ম তারিখ (YYYY-MM-DD) <span class="text-rose-500">*</span></label>
                            <input type="text" class="w-full text-base px-3.5 py-2.5 bg-white border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition datePicks personDOB" placeholder="YYYY-MM-DD" value="" autocomplete="off">
                        </div>
                        <div class="snw-verify-btn-cell">
                            <button class="snw-verify-btn w-full inline-flex items-center justify-center gap-1.5 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold shadow-2xs transition vSysBtn birth_verifing cursor-pointer" vid="" vtype="" type="button">
                                <i class="ph-bold ph-magnifying-glass text-xs"></i>
                                <span>যাচাই</span>
                            </button>
                        </div>
                    </div>
                </div>`;
        }

        $('.verifyFormAssign').stop(true, true).html(html).css('opacity', 1);

        if (typeof $.fn.datepicker !== 'undefined') {
            $('.verifyFormAssign .datePicks').datepicker({
                dateFormat: 'yy-mm-dd',
                changeMonth: true,
                changeYear: true,
                yearRange: "1920:+10",
                showAnim: "fadeIn"
            });
        }
    }

    $(document).on('change', 'input[name="verifyOptionSelect"]', function () {
        renderVerificationMethod($(this).val());
    });

    $(document).on('click', '.verifyOptionSection', function () {
        var $r = $(this).find('input[name="verifyOptionSelect"]');
        if (!$r.is(':checked')) {
            $r.prop('checked', true).trigger('change');
        } else if (!$('.verifyFormAssign').children().length) {
            renderVerificationMethod($r.val());
        }
    });

    function personFormTemplate(data) {
        return `
            <div class="bg-white rounded-2xl border border-neutral-200/80 shadow-xs p-6 space-y-5">
                <div class="flex items-center justify-between pb-3 border-b border-neutral-100">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <i class="ph-bold ph-user-check text-base"></i>
                        </div>
                        <h3 class="text-sm font-bold text-neutral-900">নাগরিকের তথ্যাবলী</h3>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-center gap-5 p-4 rounded-xl bg-neutral-50 border border-neutral-200/60">
                    <img src="inc/assets/himg/user_iconss.png" alt="ছবি" class="w-20 h-20 rounded-2xl object-cover border-2 border-white shadow-sm person_image_views">
                    <div class="flex-1 space-y-1 text-center sm:text-left">
                        <label class="block text-xs font-bold text-neutral-700">নাগরিকের ছবি আপলোড</label>
                        <input type="file" class="text-xs text-neutral-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition cursor-pointer person_verify_sonod_pic">
                        <p class="text-[10px] text-neutral-400">পাসপোর্ট সাইজের ছবি (PNG বা JPG)</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-neutral-700 mb-1">নাম (বাংলায়) <span class="text-rose-500">*</span></label>
                        <input type="text" class="w-full text-xs px-3.5 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition nameBN" placeholder="বাংলায় নাম লিখুন" value="${data.nameBn || ''}">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-neutral-700 mb-1">Name (English)</label>
                        <input type="text" class="w-full text-xs px-3.5 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition nameEN" placeholder="Name in English" value="${data.nameEn || ''}">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-neutral-700 mb-1">পিতার নাম (বাংলায়) <span class="text-rose-500">*</span></label>
                        <input type="text" class="w-full text-xs px-3.5 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition fatherName_s_bn" placeholder="পিতার নাম (বাংলা)" value="${data.fatheBn || ''}">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-neutral-700 mb-1">Father's Name (English)</label>
                        <input type="text" class="w-full text-xs px-3.5 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition fatherName_en_s" placeholder="Father Name (English)" value="${data.fatherEN || ''}">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-neutral-700 mb-1">মাতার নাম (বাংলায়) <span class="text-rose-500">*</span></label>
                        <input type="text" class="w-full text-xs px-3.5 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition motherNameBn_s" placeholder="মাতার নাম (বাংলা)" value="${data.motherBn || ''}">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-neutral-700 mb-1">Mother's Name (English)</label>
                        <input type="text" class="w-full text-xs px-3.5 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition motherName_s_en" placeholder="Mother Name (English)" value="${data.motherEN || ''}">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-semibold text-neutral-700">ওয়ার্ড নির্বাচন <span class="text-rose-500">*</span></label>
                        </div>
                        <select class="w-full text-xs px-3 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition selectingWard_no">
                            <option value="">ওয়ার্ড নং সিলেক্ট করুন</option>
                            <option value="01">ওয়ার্ড নং ০১</option>
                            <option value="02">ওয়ার্ড নং ০২</option>
                            <option value="03">ওয়ার্ড নং ০৩</option>
                            <option value="04">ওয়ার্ড নং ০৪</option>
                            <option value="05">ওয়ার্ড নং ০৫</option>
                            <option value="06">ওয়ার্ড নং ০৬</option>
                            <option value="07">ওয়ার্ড নং ০৭</option>
                            <option value="08">ওয়ার্ড নং ০৮</option>
                            <option value="09">ওয়ার্ড নং ০৯</option>
                        </select>
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-semibold text-neutral-700">গ্রাম নির্বাচন <span class="text-rose-500">*</span></label>
                            <button type="button" class="text-[11px] font-semibold text-indigo-600 hover:text-indigo-800 transition toggleCustomVillageBtn cursor-pointer flex items-center gap-1"><i class="ph-bold ph-pencil-simple text-xs"></i> <span>অন্যান্য গ্রাম</span></button>
                        </div>
                        <select class="w-full text-xs px-3 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition selectingVillage">
                            <option value="">গ্রাম সিলেক্ট করুন</option>
                            <option value="other">অন্যান্য (ম্যানুয়াল লিখুন)</option>
                        </select>
                        <div class="customVillageWrap mt-2 hidden">
                            <input type="text" class="w-full text-xs px-3 py-2 bg-white border border-indigo-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition customVillageInput" placeholder="ম্যানুয়াল গ্রামের নাম লিখুন...">
                            <span class="text-[10px] text-neutral-400 mt-0.5 block">এই সনদে নির্দিষ্ট কাস্টম গ্রাম হিসেবে যুক্ত হবে</span>
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-semibold text-neutral-700">বাড়ি নির্বাচন</label>
                            <button type="button" class="text-[11px] font-semibold text-indigo-600 hover:text-indigo-800 transition toggleCustomHouseBtn cursor-pointer flex items-center gap-1"><i class="ph-bold ph-pencil-simple text-xs"></i> <span>অন্যান্য বাড়ি</span></button>
                        </div>
                        <select class="w-full text-xs px-3 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition selectingHouse">
                            <option value="">বাড়ি সিলেক্ট করুন</option>
                            <option value="other">অন্যান্য (ম্যানুয়াল লিখুন)</option>
                        </select>
                        <div class="customHouseWrap mt-2 hidden">
                            <input type="text" class="w-full text-xs px-3 py-2 bg-white border border-indigo-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition customHouseInput" placeholder="ম্যানুয়াল বাড়ির নাম লিখুন...">
                            <span class="text-[10px] text-neutral-400 mt-0.5 block">এই সনদে নির্দিষ্ট কাস্টম বাড়ি হিসেবে যুক্ত হবে</span>
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-t border-neutral-100 flex items-center justify-end">
                    <button type="button" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white rounded-xl text-xs font-semibold shadow-2xs transition click_nextBtn afterVerifyBtnNext cursor-pointer">
                        <span>পরবর্তী ধাপ</span>
                        <i class="ph-bold ph-arrow-right text-xs"></i>
                    </button>
                </div>
            </div>
        `;
    }

    $(document).on('click', '.birth_verifing', function () {
        /* 17-digit rule, enforced before anything else happens. */
        if (!window.snwNumberOk(false)) return;
        var brn = $('.birthNumber').val();
        var dob = $.trim($('.personDOB').val());
        if (!dob) {
            Swal.fire({
                title: '<strong>জন্ম তারিখ আবশ্যক</strong>',
                icon: 'warning',
                text: 'অনুগ্রহ করে নাগরিকের জন্ম তারিখ (YYYY-MM-DD) নির্বাচন বা লিখুন।',
                confirmButtonText: 'ঠিক আছে'
            });
            $('.personDOB').focus();
            return;
        }
        var $btn = $(this);
        var origHtml = $btn.html();
        $btn.prop('disabled', true).html('<i class="ph-bold ph-spinner animate-spin"></i> যাচাই হচ্ছে...');

        $.ajax({
            type: "post",
            url: "nid_v",
            data: { nid: brn, dob: dob },
            success: function (r) {
                $btn.prop('disabled', false).html(origHtml);
                institute_present_balance();
                var rs = {};
                try { rs = (typeof r === 'object') ? r : JSON.parse(r); } catch (e) { rs = {}; }
                var vid = (rs && rs.lastID) ? rs.lastID : 1;
                $('.birth_verifing, .vSysBtn, .nid_verify').attr('vtype', 'birth').attr('vid', vid);
                $('.prsonInfoAssign').html(personFormTemplate(rs));
                SNW.advance(2);
            },
            error: function () {
                $btn.prop('disabled', false).html(origHtml);
                $('.birth_verifing, .vSysBtn, .nid_verify').attr('vtype', 'birth').attr('vid', 1);
                $('.prsonInfoAssign').html(personFormTemplate({}));
                SNW.advance(2);
            }
        });
    });

    $(document).on('click', '.nid_verify', function () {
        /* 10-to-17-digit rule, enforced before the nid_v call so a bad
           number cannot produce an empty person form with no explanation. */
        if (!window.snwNumberOk(true)) return;
        var nid = $('.nid_number').val();
        var dob = $.trim($('.personDOB').val());
        if (!dob) {
            Swal.fire({
                title: '<strong>জন্ম তারিখ আবশ্যক</strong>',
                icon: 'warning',
                text: 'অনুগ্রহ করে নাগরিকের জন্ম তারিখ (YYYY-MM-DD) নির্বাচন বা লিখুন।',
                confirmButtonText: 'ঠিক আছে'
            });
            $('.personDOB').focus();
            return;
        }
        var $btn = $(this);
        var origHtml = $btn.html();
        $btn.prop('disabled', true).html('<i class="ph-bold ph-spinner animate-spin"></i> যাচাই হচ্ছে...');

        $.ajax({
            type: "post",
            url: "nid_v",
            data: { nid: nid, dob: dob },
            success: function (r) {
                $btn.prop('disabled', false).html(origHtml);
                institute_present_balance();
                var rs = {};
                try { rs = (typeof r === 'object') ? r : JSON.parse(r); } catch (e) { rs = {}; }
                var vid = (rs && rs.lastID) ? rs.lastID : 1;
                $('.nid_verify, .vSysBtn, .birth_verifing').attr('vtype', 'nid').attr('vid', vid);
                $('.prsonInfoAssign').html(personFormTemplate(rs));
                SNW.advance(2);
            },
            error: function () {
                $btn.prop('disabled', false).html(origHtml);
                $('.nid_verify, .vSysBtn, .birth_verifing').attr('vtype', 'nid').attr('vid', 1);
                $('.prsonInfoAssign').html(personFormTemplate({}));
                SNW.advance(2);
            }
        });
    });

    $(document).on('change', '.selectingWard_no2', function () {
        let thisWard_val = $(this).val();
        $.ajax({
            type: "post",
            url: "frontGetVillByWard",
            data: { ward_n: thisWard_val },
            success: function (res) {
                let h_data = '';
                for (let l = 0; l < res.length; l++) {
                    h_data += `<option value="${res[l].villages_of_union_a_iidd}"> ${res[l].bn_vill_names} </option>`; 
                }
                $('.selectingVillage2').html(`<option value=""> গ্রাম সিলেক্ট করুন </option>${h_data}`);
            }
        });
    });

    $(document).on('change', '.selectingWard_no', function () {
        let thisWard_val = $(this).val();
        $.ajax({
            type: "post",
            url: "frontGetVillByWard",
            data: { ward_n: thisWard_val },
            success: function (res) {
                let h_data = '';
                for (let l = 0; l < res.length; l++) {
                    h_data += `<option value="${res[l].villages_of_union_a_iidd}"> ${res[l].bn_vill_names} </option>`; 
                }
                $('.selectingVillage').html(`<option value=""> গ্রাম সিলেক্ট করুন </option>${h_data}<option value="other">অন্যান্য (ম্যানুয়াল লিখুন)</option>`);
                $('.customVillageWrap').addClass('hidden');
                $('.customVillageInput').val('');
                $('.selectingHouse').html(`<option value=""> বাড়ি সিলেক্ট করুন </option><option value="other">অন্যান্য (ম্যানুয়াল লিখুন)</option>`);
                $('.customHouseWrap').addClass('hidden');
                $('.customHouseInput').val('');
            }
        });
    });

    $(document).on('change', '.selectingVillage2', function () {
        let thisVillage_val = $(this).val();
        get_homes_infos2(thisVillage_val);
    });

    function get_homes_infos2(vil_id) {
        $.ajax({
            type: "post",
            url: "getHomeByVill",
            data: { vill_nos: vil_id },
            success: function (res) {
                let h_data = '';
                for (let l = 0; l < res.length; l++) {
                    h_data += `<option value="${res[l].house_home_name_etc_iddiddd}"> ${res[l].house_na_me} </option>`; 
                }
                $('.selectingHouse2').html(`<option value=""> বাড়ি সিলেক্ট করুন </option>${h_data}`);
            }
        });
    }

    $(document).on('change', '.selectingVillage', function () {
        let thisVillage_val = $(this).val();
        if (thisVillage_val === 'other') {
            $('.customVillageWrap').removeClass('hidden');
            $('.customVillageInput').focus();
            $('.selectingHouse').html(`<option value=""> বাড়ি সিলেক্ট করুন </option><option value="other">অন্যান্য (ম্যানুয়াল লিখুন)</option>`);
            return;
        } else {
            $('.customVillageWrap').addClass('hidden');
            $('.customVillageInput').val('');
        }
        get_homes_infos(thisVillage_val);
    });

    function get_homes_infos(vil_id) {
        if (!vil_id || vil_id === 'other') {
            $('.selectingHouse').html(`<option value=""> বাড়ি সিলেক্ট করুন </option><option value="other">অন্যান্য (ম্যানুয়াল লিখুন)</option>`);
            return;
        }
        $.ajax({
            type: "post",
            url: "getHomeByVill",
            data: { vill_nos: vil_id },
            success: function (res) {
                let h_data = '';
                for (let l = 0; l < res.length; l++) {
                    h_data += `<option value="${res[l].house_home_name_etc_iddiddd}"> ${res[l].house_na_me} </option>`; 
                }
                $('.selectingHouse').html(`<option value=""> বাড়ি সিলেক্ট করুন </option>${h_data}<option value="other">অন্যান্য (ম্যানুয়াল লিখুন)</option>`);
            }
        });
    }

    $(document).on('change', '.selectingHouse', function () {
        let thisHouse_val = $(this).val();
        if (thisHouse_val === 'other') {
            $('.customHouseWrap').removeClass('hidden');
            $('.customHouseInput').focus();
        } else {
            $('.customHouseWrap').addClass('hidden');
            $('.customHouseInput').val('');
        }
    });

    $(document).on('click', '.toggleCustomVillageBtn', function (e) {
        e.preventDefault();
        var $sel = $('.selectingVillage');
        var isOther = $sel.val() === 'other';
        if (isOther) {
            $sel.val('').trigger('change');
            $('.customVillageWrap').addClass('hidden');
            $('.customVillageInput').val('');
        } else {
            $sel.val('other').trigger('change');
            $('.customVillageWrap').removeClass('hidden');
            $('.customVillageInput').focus();
        }
    });

    $(document).on('click', '.toggleCustomHouseBtn', function (e) {
        e.preventDefault();
        var $sel = $('.selectingHouse');
        var isOther = $sel.val() === 'other';
        if (isOther) {
            $sel.val('').trigger('change');
            $('.customHouseWrap').addClass('hidden');
            $('.customHouseInput').val('');
        } else {
            $sel.val('other').trigger('change');
            $('.customHouseWrap').removeClass('hidden');
            $('.customHouseInput').focus();
        }
    });

    $(document).on('click', '.afterVerifyBtnNext', function () {
        var nameBn = $.trim($('.nameBN').val());
        if (!nameBn) {
            Swal.fire({
                title: '<strong>নাম (বাংলায়) আবশ্যক</strong>',
                icon: 'warning',
                text: 'অনুগ্রহ করে নাগরিকের বাংলায় নাম প্রদান করুন।',
                confirmButtonText: 'ঠিক আছে'
            });
            $('.nameBN').focus();
            return;
        }

        var fatherBn = $.trim($('.fatherName_s_bn').val());
        if (!fatherBn) {
            Swal.fire({
                title: '<strong>পিতার নাম আবশ্যক</strong>',
                icon: 'warning',
                text: 'অনুগ্রহ করে নাগরিকের পিতার নাম বাংলায় প্রদান করুন।',
                confirmButtonText: 'ঠিক আছে'
            });
            $('.fatherName_s_bn').focus();
            return;
        }

        var motherBn = $.trim($('.motherNameBn_s').val());
        if (!motherBn) {
            Swal.fire({
                title: '<strong>মাতার নাম আবশ্যক</strong>',
                icon: 'warning',
                text: 'অনুগ্রহ করে নাগরিকের মাতার নাম বাংলায় প্রদান করুন।',
                confirmButtonText: 'ঠিক আছে'
            });
            $('.motherNameBn_s').focus();
            return;
        }

        var wardNo = $('.selectingWard_no').val();
        if (!wardNo) {
            Swal.fire({
                title: '<strong>ওয়ার্ড নির্বাচন আবশ্যক</strong>',
                icon: 'warning',
                text: 'অনুগ্রহ করে ওয়ার্ড নং সিলেক্ট করুন।',
                confirmButtonText: 'ঠিক আছে'
            });
            $('.selectingWard_no').focus();
            return;
        }

        var vVal = $('.selectingVillage').val();
        if (!vVal) {
            Swal.fire({
                title: '<strong>গ্রাম নির্বাচন আবশ্যক</strong>',
                icon: 'warning',
                text: 'অনুগ্রহ করে নাগরিকের গ্রাম সিলেক্ট করুন।',
                confirmButtonText: 'ঠিক আছে'
            });
            $('.selectingVillage').focus();
            return;
        }
        if (vVal === 'other' && !$.trim($('.customVillageInput').val())) {
            Swal.fire({
                title: '<strong>গ্রামের নাম লিখুন</strong>',
                icon: 'warning',
                text: 'আপনি অন্যান্য গ্রাম নির্বাচন করেছেন। অনুগ্রহ করে গ্রামের নাম লিখুন।',
                confirmButtonText: 'ঠিক আছে'
            });
            $('.customVillageInput').focus();
            return;
        }

        if (!$('.btnSelectEnBn').children().length) {
            $('.btnSelectEnBn').html(
                `<div class="bg-neutral-100 p-1 rounded-xl inline-flex items-center gap-1 border border-neutral-200">
                    <button type="button" class="banglaSonodBtnBN px-4 py-2 rounded-xl text-xs font-bold bg-white text-indigo-700 shadow-2xs cursor-pointer flex items-center gap-1.5 transition">
                        <i class="ph-bold ph-translate"></i>
                        <span>বাংলা সনদ</span>
                    </button>
                    <button type="button" class="englishSonodBtnEN px-4 py-2 rounded-xl text-xs font-bold text-neutral-600 hover:text-neutral-900 cursor-pointer flex items-center gap-1.5 transition">
                        <i class="ph-bold ph-globe"></i>
                        <span>English Sonod</span>
                    </button>
                </div>`
            );
        }
        SNW.advance(3);
        if (!$('.selectSonodTypes').children().length) {
            $('.banglaSonodBtnBN').trigger('click');
        }
    });

    $(document).on('click', '.editThisForm', function () {
        $('.click_nextBtn').html('<span>পরবর্তী ধাপ</span> <i class="ph-bold ph-arrow-right text-xs"></i>').addClass('afterVerifyBtnNext').removeClass('editThisForm');
        SNW.goTo(2);
    });

    $(document).on('select2:open', function () {
        setTimeout(function () {
            var sf = document.querySelector('.select2-search__field');
            if (sf) {
                sf.setAttribute('placeholder', 'সনদের নাম দিয়ে অনুসন্ধান করুন...');
            }
        }, 10);
    });

    $(document).on('click', '.englishSonodBtnEN', function () {
        $('.englishSonodBtnEN').addClass('bg-white text-indigo-700 shadow-2xs').removeClass('text-neutral-600');
        $('.banglaSonodBtnBN').removeClass('bg-white text-indigo-700 shadow-2xs').addClass('text-neutral-600');

        $('.selectSonodTypes').html(
            `<div class="bg-white rounded-2xl border border-neutral-200/80 shadow-xs p-5 max-w-xl mx-auto space-y-2">
                <label for="selectSonodLabel" class="block text-xs font-bold text-neutral-700">Select Certificate Type</label>
                <select class="w-full text-xs px-3 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition sonod_selecting_s single_select2" id="selectSonodLabel">
                    <option value="">Select Certificate</option>
                    <?php if (!empty($custom_sonods)): ?>
                        <?php foreach ($custom_sonods as $csonod): ?>
                            <?php if (($csonod->en_bn_type ?? 1) == 2): ?>
                                <option value="custom_<?= $csonod->certifys_default_ins_ss_iiidddd; ?>" data-custom-id="<?= $csonod->certifys_default_ins_ss_iiidddd; ?>">
                                    [Union Custom] <?= htmlspecialchars($csonod->cert_title_ss, ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <?php if (!empty($all_sonod)): ?>
                        <?php foreach ($all_sonod as $sonod): ?>
                            <?php if($sonod->en_bn_type == 2): ?>
                                <option value="<?= $sonod->deft_cer_all_au_iddd; ?>"><?= htmlspecialchars($sonod->certify_titles, ENT_QUOTES, 'UTF-8'); ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>`
        );
        tinymce.get("rich_editor").setContent('');
        $('.sonodTitle').val('');
        $('.single_select2').select2({ width: '100%', placeholder: 'Select Certificate' });
    });

    $(document).on('click', '.banglaSonodBtnBN', function () {
        $('.banglaSonodBtnBN').addClass('bg-white text-indigo-700 shadow-2xs').removeClass('text-neutral-600');
        $('.englishSonodBtnEN').removeClass('bg-white text-indigo-700 shadow-2xs').addClass('text-neutral-600');

        $('.selectSonodTypes').html(
            `<div class="bg-white rounded-2xl border border-neutral-200/80 shadow-xs p-5 max-w-xl mx-auto space-y-2">
                <label for="selectSonodLabel" class="block text-xs font-bold text-neutral-700">সনদের ধরন নির্বাচন করুন</label>
                <select class="w-full text-xs px-3 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition sonod_selecting_s single_select2" id="selectSonodLabel">
                    <option value="">সনদ নির্বাচন করুন</option>
                    <?php if (!empty($custom_sonods)): ?>
                        <?php foreach ($custom_sonods as $csonod): ?>
                            <?php if (($csonod->en_bn_type ?? 1) == 1): ?>
                                <option value="custom_<?= $csonod->certifys_default_ins_ss_iiidddd; ?>" data-custom-id="<?= $csonod->certifys_default_ins_ss_iiidddd; ?>">
                                    [ইউনিয়ন কাস্টম] <?= htmlspecialchars($csonod->cert_title_ss, ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <?php if (!empty($all_sonod)): ?>
                        <?php foreach ($all_sonod as $sonod): ?>
                            <?php if($sonod->en_bn_type == 1): ?>
                                <option value="<?= $sonod->deft_cer_all_au_iddd; ?>"><?= htmlspecialchars($sonod->certify_titles, ENT_QUOTES, 'UTF-8'); ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>`
        );
        tinymce.get("rich_editor").setContent('');
        $('.sonodTitle').val('');
        $('.single_select2').select2({ width: '100%', placeholder: 'সনদ নির্বাচন করুন' });
    });

    $(document).on('click', '.editThisForm', function () {
        $('.nameBN, .nameEN, .motherName_s_en, .fatherName_en_s, .motherNameBn_s, .fatherName_s_bn, .selectingWard_no, .selectingVillage, .selectingHouse, .customVillageInput, .customHouseInput').removeAttr('readonly');
        $('.click_nextBtn').html('<span>পরবর্তী ধাপ</span> <i class="ph-bold ph-arrow-right text-xs"></i>').addClass('afterVerifyBtnNext').removeClass('editThisForm');
        tinymce.get("rich_editor").setContent('');
        $('.sonodDetailsTexBox').css('display', 'none');
        $('.selectSonodTypes').html('');
        $('.sonodTitle').val('');
        /* Editing the person details means going back to step 2. Without this
           the button unlocked fields inside a panel the wizard had already
           collapsed to a summary row, so the click appeared to do nothing. */
        SNW.goTo(2);
    });

    $(document).on('change', '.sonod_selecting_s', function () {
        var selected_sonod_idd = $(this).val();
        if (!selected_sonod_idd) {
            $('.sonodDetailsTexBox').css('display', 'none');
            return;
        }

        $.ajax({
            type: "post",
            url: "getSingleSonod",
            data: { sonod_id: selected_sonod_idd },
            success: function (r) {
                $('.sonodDetailsTexBox').css('display', 'block');
                $('.sonodTitle').val((r && r.pi) ? (r.pi.certify_title_text_s || '') : '');

                if (selected_sonod_idd == 2) {
                    $('.create_new_sonod_btn').html('');
                    $('.sonod_details_input_boxes').html(`
                        <div class="bg-white rounded-2xl border border-neutral-200/80 shadow-xs p-6 space-y-4">
                            <div class="flex items-center gap-2 pb-3 border-b border-neutral-100">
                                <div class="w-7 h-7 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center">
                                    <i class="ph-bold ph-user-minus text-base"></i>
                                </div>
                                <h3 class="text-sm font-bold text-neutral-900">মৃত ব্যক্তির তথ্য বিবরণী</h3>
                            </div>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-neutral-700 mb-1">মৃত ব্যক্তির এনআইডি নং</label>
                                    <input type="text" name="death_nid" class="w-full text-xs px-3 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition dead_person_nid" onkeypress="return (event.charCode !=8 && event.charCode ==0 || (event.charCode >= 48 && event.charCode <= 57))" autocomplete="off">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-neutral-700 mb-1">মৃত্যু নিবন্ধন নং</label>
                                    <input type="text" name="death_nid" class="w-full text-xs px-3 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition dead_person_dead_certify_no" onkeypress="return (event.charCode !=8 && event.charCode ==0 || (event.charCode >= 48 && event.charCode <= 57))" autocomplete="off">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-neutral-700 mb-1">জন্ম তারিখ</label>
                                    <input type="text" name="dob" class="w-full text-xs px-3 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition datePicks dead_person_nid_dob" autocomplete="off">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-neutral-700 mb-1">মৃত ব্যক্তির নাম (বাংলায়) <span class="text-rose-500">*</span></label>
                                    <input type="text" name="d_name" class="w-full text-xs px-3 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition dead_person_name_bn" required autocomplete="off">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-neutral-700 mb-1">মৃত্যুর তারিখ <span class="text-rose-500">*</span></label>
                                    <input type="text" name="" class="w-full text-xs px-3 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition datePicks dead_person_deaded_date" required autocomplete="off">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-neutral-700 mb-1">পিতা / স্বামীর নাম (বাংলায়) <span class="text-rose-500">*</span></label>
                                    <input type="text" name="d_fname" class="w-full text-xs px-3 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition dead_person_father_hus_name_bn" autocomplete="off" required>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-neutral-700 mb-1">মাতার নাম (বাংলায়)</label>
                                    <input type="text" name="d_mname" class="w-full text-xs px-3 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition dead_person_mothers_name_bn" autocomplete="off">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-neutral-700 mb-1">ওয়ার্ড নং</label>
                                    <select class="w-full text-xs px-3 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition selectingWard_no2">
                                        <option value="">ওয়ার্ড নং সিলেক্ট করুন</option>
                                        <option value="01">ওয়ার্ড নং ০১</option>
                                        <option value="02">ওয়ার্ড নং ০২</option>
                                        <option value="03">ওয়ার্ড নং ০৩</option>
                                        <option value="04">ওয়ার্ড নং ০৪</option>
                                        <option value="05">ওয়ার্ড নং ০৫</option>
                                        <option value="06">ওয়ার্ড নং ০৬</option>
                                        <option value="07">ওয়ার্ড নং ০৭</option>
                                        <option value="08">ওয়ার্ড নং ০৮</option>
                                        <option value="09">ওয়ার্ড নং ০৯</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-neutral-700 mb-1">গ্রামের নাম</label>
                                    <select class="w-full text-xs px-3 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition selectingVillage2">
                                        <option value="">গ্রাম সিলেক্ট করুন</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-neutral-700 mb-1">বাড়ির নাম</label>
                                    <select class="w-full text-xs px-3 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition selectingHouse2">
                                        <option value="">বাড়ি সিলেক্ট করুন</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-neutral-700 mb-1">লিঙ্গ <span class="text-rose-500">*</span></label>
                                    <select class="w-full text-xs px-3 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition dead_person_gender_choose" name="dgender" required>
                                        <option value="">বাছাই করুন</option>
                                        <option value="পুরুষ">পুরুষ</option>
                                        <option value="মহিলা">মহিলা</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-neutral-700 mb-1">বৈবাহিক সম্পর্ক <span class="text-rose-500">*</span></label>
                                    <select class="w-full text-xs px-3 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition dead_person_relation_status" name="relation" required>
                                        <option value="">বাছাই করুন</option>
                                        <option value="বিবাহিত">বিবাহিত</option>
                                        <option value="অবিবাহিত">অবিবাহিত</option>
                                        <option value="তালাক প্রাপ্ত">তালাক প্রাপ্ত</option>
                                        <option value="বিধবা">বিধবা</option>
                                        <option value="অন্যান্য">অন্যান্য</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-neutral-700 mb-1">পেশা</label>
                                    <input type="text" name="d_career" class="w-full text-xs px-3 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition dead_person_career_option" autocomplete="off">
                                </div>
                            </div>

                            <div class="pt-4 border-t border-neutral-100 space-y-3">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2 border-b border-neutral-100">
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-xs font-bold text-neutral-800 flex items-center gap-1.5">
                                            <i class="ph-bold ph-users text-sm text-indigo-600"></i>
                                            <span>ওয়ারিশগণের তালিকা (ছেলে, মেয়ে, স্ত্রী ও অন্যান্য সদস্য)</span>
                                        </h4>
                                        <span class="warish_total_count text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-indigo-100 text-indigo-800">মোট ওয়ারিশ: ০ জন</span>
                                    </div>
                                    <div class="flex items-center flex-wrap gap-1.5">
                                        <button type="button" class="quick-add-warish px-2.5 py-1 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-lg text-[11px] font-bold transition cursor-pointer" data-rel="পুত্র">
                                            <i class="ph-bold ph-plus"></i> পুত্র (ছেলে)
                                        </button>
                                        <button type="button" class="quick-add-warish px-2.5 py-1 bg-pink-50 text-pink-700 hover:bg-pink-100 rounded-lg text-[11px] font-bold transition cursor-pointer" data-rel="কন্যা">
                                            <i class="ph-bold ph-plus"></i> কন্যা (মেয়ে)
                                        </button>
                                        <button type="button" class="quick-add-warish px-2.5 py-1 bg-purple-50 text-purple-700 hover:bg-purple-100 rounded-lg text-[11px] font-bold transition cursor-pointer" data-rel="স্ত্রী">
                                            <i class="ph-bold ph-plus"></i> স্ত্রী
                                        </button>
                                        <button type="button" class="newwars inline-flex items-center gap-1 px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition cursor-pointer">
                                            <i class="ph-bold ph-plus"></i>
                                            <span>অন্য সদস্য যোগ করুন</span>
                                        </button>
                                    </div>
                                </div>
                                <div class="warish_form_appends space-y-2.5 mt-2">
                                    <!-- Dynamic Warish Rows Go Here -->
                                </div>
                            </div>
                        </div>
                    `);
                    
                    $('.create_new_sonod_btn').html(`
                        <button type="button" class="create_new_warish_sonod_btn inline-flex items-center gap-1.5 px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white rounded-xl text-xs font-semibold shadow-2xs transition cursor-pointer">
                            <i class="ph-bold ph-check-circle text-xs"></i>
                            <span>ওয়ারিশ সনদ তৈরি করুন</span>
                        </button>
                    `);
                    if (tinymce.get("rich_editor")) {
                        tinymce.get("rich_editor").setContent((r && r.pi) ? (r.pi.certify_detail_ss || '') : '');
                    }
                } else if (selected_sonod_idd == 3 || selected_sonod_idd == 1230) {
                    $('.create_new_sonod_btn').html('');
                    $('.sonod_details_input_boxes').html(`
                        <div class="bg-white rounded-2xl border border-neutral-200/80 shadow-xs p-6 space-y-5">
                            <div class="flex items-center justify-between pb-3 border-b border-neutral-100">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                                        <i class="ph-bold ph-storefront text-lg"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-bold text-neutral-900">ব্যবসা প্রতিষ্ঠান ও লাইসেন্স তথ্যাবলী</h3>
                                        <p class="text-[11px] text-neutral-500">ট্রেড লাইসেন্স ইস্যুর জন্য প্রতিষ্ঠানের নাম, ধরণ ও ফির হিসাব পূরণ করুন</p>
                                    </div>
                                </div>
                                <span class="px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 text-xs font-bold">ট্রেড লাইসেন্স ফরম</span>
                            </div>

                            <!-- প্রতিষ্ঠানের সাধারণ তথ্য -->
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-semibold text-neutral-700 mb-1">ব্যবসা প্রতিষ্ঠানের নাম (বাংলায়) <span class="text-rose-500">*</span></label>
                                    <input type="text" name="tl_business_name_bn" class="w-full text-xs px-3.5 py-2.5 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition font-bold text-neutral-900 tl_business_name_bn" placeholder="উদা: মেসার্স রহিম এন্টারপ্রাইজ / ভাই ভাই স্টোর" required autocomplete="off">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-neutral-700 mb-1">ব্যবসা প্রতিষ্ঠানের নাম (ইংরেজিতে)</label>
                                    <input type="text" name="tl_business_name_en" class="w-full text-xs px-3.5 py-2.5 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition text-neutral-800 tl_business_name_en" placeholder="e.g. M/S Rahim Enterprise" autocomplete="off">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-neutral-700 mb-1">ব্যবসার ধরণ / প্রকৃতি <span class="text-rose-500">*</span></label>
                                    <select class="w-full text-xs px-3.5 py-2.5 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 tl_business_type_preset font-medium">
                                        <option value="মুদি ও ডিপার্টমেন্টাল স্টোর">মুদি ও ডিপার্টমেন্টাল স্টোর</option>
                                        <option value="ফার্মেসি ও ঔষধ বিক্রয়">ফার্মেসি ও ঔষধ বিক্রয়</option>
                                        <option value="ইলেকট্রনিক্স ও হার্ডওয়্যার">ইলেকট্রনিক্স ও হার্ডওয়্যার</option>
                                        <option value="রেস্টুরেন্ট ও মিষ্টি বিপণি">রেস্টুরেন্ট ও মিষ্টি বিপণি</option>
                                        <option value="বস্ত্র ও তৈরি পোশাক">বস্ত্র ও তৈরি পোশাক (গার্মেন্টস)</option>
                                        <option value="যানবাহন ও গ্যারেজ / অটো পার্টস">যানবাহন ও গ্যারেজ / অটো পার্টস</option>
                                        <option value="রড, সিমেন্ট ও কন্সট্রাকশন সামগ্রী">রড, সিমেন্ট ও কন্সট্রাকশন সামগ্রী</option>
                                        <option value="পোল্ট্রি, ডেইরি ও মৎস্য খামার">পোল্ট্রি, ডেইরি ও মৎস্য খামার</option>
                                        <option value="কম্পিউটার ও ডিজিটাল সেবা কেন্দ্র">কম্পিউটার ও ডিজিটাল সেবা কেন্দ্র</option>
                                        <option value="কসমেটিকস ও স্টেশনারি">কসমেটিকস ও স্টেশনারি</option>
                                        <option value="ফল ও শাক-সবজি বিক্রয়">ফল ও শাক-সবজি বিক্রয়</option>
                                        <option value="other">অন্যান্য ব্যবসা (নিচে লিখুন)</option>
                                    </select>
                                    <input type="text" name="tl_business_type_custom" class="w-full text-xs px-3 py-2 mt-1.5 bg-white border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 tl_business_type_custom hidden" placeholder="নির্দিষ্ট ব্যবসার ধরণ লিখুন...">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-neutral-700 mb-1">ব্যবসার মূলধন (টাকায়)</label>
                                    <input type="text" name="tl_capital" class="w-full text-xs px-3.5 py-2.5 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 tl_capital" placeholder="উদা: ১,০০,০০০/- বা ৫,০০,০০০/-" value="১,০০,০০০/-" autocomplete="off">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-neutral-700 mb-1">চলতি অর্থবছর <span class="text-rose-500">*</span></label>
                                    <select class="w-full text-xs px-3.5 py-2.5 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 tl_fiscal_year font-bold">
                                        <option value="২০২৬-২০২৭" selected>২০২৬-২০২৭</option>
                                        <option value="২০২৫-২০২৬">২০২৫-২০২৬</option>
                                        <option value="২০২৭-২০২৮">২০২৭-২০২৮</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-neutral-700 mb-1">দোকান / হোল্ডিং নং</label>
                                    <input type="text" name="tl_holding_no" class="w-full text-xs px-3.5 py-2.5 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 tl_holding_no" placeholder="উদা: ১২/এ বা ৩" autocomplete="off">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-neutral-700 mb-1">বাজার / রাস্তা / এলাকা</label>
                                    <input type="text" name="tl_market_road" class="w-full text-xs px-3.5 py-2.5 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 tl_market_road" placeholder="উদা: শান্তি বাজার / প্রধান সড়ক" autocomplete="off">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-neutral-700 mb-1">ট্যাক্স আইডি (TIN / ই-টিন)</label>
                                    <input type="text" name="tl_tin_no" class="w-full text-xs px-3.5 py-2.5 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 tl_tin_no font-mono" placeholder="ঐচ্ছিক (১২ ডিজিট)" autocomplete="off">
                                </div>
                            </div>

                            <!-- লাইসেন্স ফি ও ট্যাক্স হিসাব -->
                            <div class="pt-4 border-t border-neutral-100">
                                <h4 class="text-xs font-bold text-neutral-800 flex items-center gap-1.5 mb-3">
                                    <i class="ph-bold ph-receipt text-sm text-emerald-600"></i>
                                    <span>ট্রেড লাইসেন্স ফি ও করের হিসাব-নিকাশ</span>
                                </h4>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-neutral-50/70 p-4 rounded-xl border border-neutral-200/60">
                                    <div>
                                        <label class="block text-[11px] font-semibold text-neutral-600 mb-1">বাৎসরিক লাইসেন্স ফি</label>
                                        <input type="text" class="w-full text-xs px-3 py-2 bg-white border border-neutral-200 rounded-lg tl_calc_fee tl_fee_yearly font-bold" value="২০০/-" data-val="200">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-neutral-600 mb-1">১৫% ভ্যাট</label>
                                        <input type="text" class="w-full text-xs px-3 py-2 bg-white border border-neutral-200 rounded-lg tl_calc_fee tl_fee_vat font-bold" value="৩০/-" data-val="30">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-neutral-600 mb-1">পেশা ও ব্যবসা কর</label>
                                        <input type="text" class="w-full text-xs px-3 py-2 bg-white border border-neutral-200 rounded-lg tl_calc_fee tl_fee_tax font-bold" value="৭৭০/-" data-val="770">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-emerald-800 mb-1 font-bold">মোট ধার্যকৃত ফি</label>
                                        <input type="text" class="w-full text-xs px-3 py-2 bg-emerald-50 border border-emerald-300 rounded-lg tl_total_charge font-black text-emerald-900" value="১০০০/-" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `);
                    $('.create_new_sonod_btn').html(`
                        <button type="button" class="create_new_trade_license_btn inline-flex items-center gap-1.5 px-5 py-2 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white rounded-xl text-xs font-semibold shadow-2xs transition cursor-pointer">
                            <i class="ph-bold ph-check-circle text-xs"></i>
                            <span>ট্রেড লাইসেন্স সনদ তৈরি করুন</span>
                        </button>
                    `);
                    if (tinymce.get("rich_editor")) {
                        tinymce.get("rich_editor").setContent((r && r.pi) ? (r.pi.certify_detail_ss || '') : '');
                    }
                } else {
                    $('.sonod_details_input_boxes').html('');
                    $('.create_new_sonod_btn').html(`
                        <button type="button" class="createNewSonod inline-flex items-center gap-1.5 px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white rounded-xl text-xs font-semibold shadow-2xs transition cursor-pointer">
                            <i class="ph-bold ph-check-circle text-xs"></i>
                            <span>সনদ তৈরি করুন</span>
                        </button>
                    `);
                    if (tinymce.get("rich_editor")) {
                        tinymce.get("rich_editor").setContent((r && r.pi) ? (r.pi.certify_detail_ss || '') : '');
                    }
                }

                SNW.advance(4);
            }
        });
    });

    function getWarishRowHtml(relPreset) {
        relPreset = relPreset || '';
        return `
            <div class="pwar grid grid-cols-1 sm:grid-cols-12 gap-2.5 p-3.5 bg-neutral-50 rounded-xl border border-neutral-200/70 items-end shadow-2xs transition-all hover:bg-neutral-100/50">
                <div class="sm:col-span-3">
                    <label class="block text-[11px] font-semibold text-neutral-700 mb-1">ওয়ারিশের নাম <span class="text-rose-500">*</span></label>
                    <input type="text" class="w_name w-full text-xs px-3 py-2 bg-white border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 font-medium" name="w_name[]" placeholder="পূর্ণ নাম লিখুন" autocomplete="off" required>
                </div>
                <div class="sm:col-span-3">
                    <label class="block text-[11px] font-semibold text-neutral-700 mb-1">সম্পর্ক <span class="text-rose-500">*</span></label>
                    <div class="flex gap-1.5">
                        <select class="w_relation_preset w-1/2 text-xs px-2 py-2 bg-white border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                            <option value="">বাছাই</option>
                            <option value="পুত্র" ${relPreset === 'পুত্র' ? 'selected' : ''}>পুত্র</option>
                            <option value="কন্যা" ${relPreset === 'কন্যা' ? 'selected' : ''}>কন্যা</option>
                            <option value="স্ত্রী" ${relPreset === 'স্ত্রী' ? 'selected' : ''}>স্ত্রী</option>
                            <option value="স্বামী" ${relPreset === 'স্বামী' ? 'selected' : ''}>স্বামী</option>
                            <option value="পিতা">পিতা</option>
                            <option value="মাতা">মাতা</option>
                            <option value="ভাই">ভাই</option>
                            <option value="বোন">বোন</option>
                            <option value="অন্যান্য">অন্যান্য</option>
                        </select>
                        <input type="text" class="w_relation w-1/2 text-xs px-2.5 py-2 bg-white border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20" name="w_relation[]" value="${relPreset}" placeholder="সম্পর্ক" autocomplete="off" required>
                    </div>
                </div>
                <div class="sm:col-span-3">
                    <label class="block text-[11px] font-semibold text-neutral-700 mb-1">এনআইডি / জন্ম নিবন্ধন নং</label>
                    <input type="text" class="w_nidbrn w-full text-xs px-3 py-2 bg-white border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 font-mono" name="w_nidbrn[]" placeholder="১০/১৭ বা ১৭ ডিজিট" autocomplete="off">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-[11px] font-semibold text-neutral-700 mb-1">বয়স / জন্ম তারিখ <span class="text-rose-500">*</span></label>
                    <input type="text" class="w_age_date w-full text-xs px-3 py-2 bg-white border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20" name="w_age_date[]" placeholder="উদা: ৩৫ বা 12-05-1990" autocomplete="off" required>
                </div>
                <div class="sm:col-span-1 flex justify-end">
                    <button type="button" class="remove_this_warish_form w-9 h-9 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-100 hover:text-rose-700 flex items-center justify-center transition cursor-pointer" title="মুছে ফেলুন">
                        <i class="ph-bold ph-trash text-sm"></i>
                    </button>
                </div>
            </div>`;
    }

    function updateWarishStats() {
        const total = $('.warish_form_appends .pwar').length;
        let sons = 0, daughters = 0, wives = 0, others = 0;
        $('.warish_form_appends .pwar').each(function() {
            const rel = $.trim($(this).find('.w_relation').val());
            if (rel === 'পুত্র' || rel.indexOf('পুত্র') !== -1 || rel.indexOf('ছেলে') !== -1) sons++;
            else if (rel === 'কন্যা' || rel.indexOf('কন্যা') !== -1 || rel.indexOf('মেয়ে') !== -1) daughters++;
            else if (rel === 'স্ত্রী' || rel.indexOf('স্ত্রী') !== -1) wives++;
            else if (rel !== '') others++;
        });
        let summary = `মোট ওয়ারিশ: ${total} জন`;
        let parts = [];
        if (sons > 0) parts.push(`পুত্র: ${sons}`);
        if (daughters > 0) parts.push(`কন্যা: ${daughters}`);
        if (wives > 0) parts.push(`স্ত্রী: ${wives}`);
        if (others > 0) parts.push(`অন্যান্য: ${others}`);
        if (parts.length > 0) summary += ` (${parts.join(', ')})`;
        $('.warish_total_count').text(summary);
    }

    $(document).on('click', '.newwars', function () {
        $('.warish_form_appends').append(getWarishRowHtml(''));
        updateWarishStats();
    });

    $(document).on('click', '.quick-add-warish', function () {
        const rel = $(this).attr('data-rel') || '';
        $('.warish_form_appends').append(getWarishRowHtml(rel));
        updateWarishStats();
    });

    $(document).on('change', '.w_relation_preset', function () {
        const val = $(this).val();
        if (val && val !== 'অন্যান্য') {
            $(this).closest('.pwar').find('.w_relation').val(val);
        }
        updateWarishStats();
    });

    $(document).on('input', '.w_relation', function () {
        updateWarishStats();
    });

    $(document).on('click', '.remove_this_warish_form', function () {
        $(this).closest('.pwar').remove();
        updateWarishStats();
    });

    // Trade license business type change
    $(document).on('change', '.tl_business_type_preset', function() {
        if ($(this).val() === 'other') {
            $('.tl_business_type_custom').removeClass('hidden').focus();
        } else {
            $('.tl_business_type_custom').addClass('hidden').val('');
        }
    });

    // Trade license fee calculation
    $(document).on('input change', '.tl_calc_fee', function() {
        function parseBnNum(val) {
            val = (val || '').toString().replace(/[^0-9০-৯]/g, '');
            const bn = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
            let enStr = '';
            for (let i = 0; i < val.length; i++) {
                const idx = bn.indexOf(val[i]);
                enStr += (idx !== -1) ? idx : val[i];
            }
            return parseFloat(enStr) || 0;
        }
        function toBnNum(num) {
            const bn = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
            return num.toString().split('').map(d => (d >= '0' && d <= '9') ? bn[parseInt(d)] : d).join('');
        }

        const fee = parseBnNum($('.tl_fee_yearly').val());
        const vat = parseBnNum($('.tl_fee_vat').val());
        const tax = parseBnNum($('.tl_fee_tax').val());
        const total = fee + vat + tax;
        $('.tl_total_charge').val(toBnNum(total) + '/-');
    });

    $(document).on('click', '.create_new_warish_sonod_btn', function () {
        $.ajax({
            type: "post",
            url: "new_warish_sonod_create",
            data: {
                'data':           tinymce.get("rich_editor").getContent(''), 
                'title':          $(".sonodTitle").val(),
                'namebn':         $('.nameBN').val(),
                'nameen':         $('.nameEN').val(),
                'fatherbn':       $('.fatherName_s_bn').val(),
                'fatheren':       $('.fatherName_en_s').val(),
                'motherbn':       $('.motherNameBn_s').val(),
                'motheren':       $('.motherName_s_en').val(),
                'wardno':         $(".selectingWard_no option:selected").val(),
                'villid':         ($(".selectingVillage option:selected").val() === 'other' ? 0 : ($(".selectingVillage option:selected").val() || 0)),
                'homeid':         ($(".selectingHouse option:selected").val() === 'other' ? 0 : ($(".selectingHouse option:selected").val() || 0)),
                'custom_village': ($(".selectingVillage option:selected").val() === 'other' ? $.trim($('.customVillageInput').val()) : ''),
                'custom_house':   ($(".selectingHouse option:selected").val() === 'other' ? $.trim($('.customHouseInput').val()) : ''),
                'vID':            ($(".vSysBtn").attr('vid') || $('.nid_verify').attr('vid') || 1),
                'vType':          ($(".vSysBtn").attr('vtype') || $('input[name="verifyOptionSelect"]:checked').val() || 'nid'),
                'id_number':      ($('.nid_number').val() || $('.birthNumber').val() || ''),
                'GID':            $(".sonod_selecting_s option:selected").val(),
                'deadnid':        $(".dead_person_nid").val(),
                'deaddob':        $('.dead_person_nid_dob').val(),
                'deadnamebn':     $('.dead_person_name_bn').val(),
                'deaddate':       $('.dead_person_deaded_date').val(),
                'deadgender':     $('.dead_person_gender_choose').val(),
                'deadrelation':   $('.dead_person_relation_status').val(),
                'deadcareer':     $('.dead_person_career_option').val(),
                'deadfatherbn':   $('.dead_person_father_hus_name_bn').val(),
                'deadmotherbn':   $('.dead_person_mothers_name_bn').val(),
                'deadwardno':     $(".selectingWard_no2 option:selected").val(),
                'deadvillid':     $(".selectingVillage2 option:selected").val(),
                'deadhomeid':     $(".selectingHouse2 option:selected").val(),
                'deadcertifyno':  $('.dead_person_dead_certify_no').val(),
                'w_name':         $('.w_name').map(function() {return this.value;}).get(),
                'w_relation':     $('.w_relation').map(function() {return this.value;}).get(),
                'w_nidbrn':       $('.w_nidbrn').map(function() {return this.value;}).get(),
                'w_age_date':     $('.w_age_date').map(function() {return this.value;}).get()
            },
            success: function (r) {
                if (parseInt(r) == 0) {
                    Swal.fire({
                        title: '<strong>সনদ তৈরি হয়নি</strong>',
                        icon: 'error',
                        html: `দয়া করে সকল তথ্য সঠিকভাবে পূরণ করুন`,
                        showConfirmButton: true,
                    });
                } else {
                    var cName = $('.nameBN').val() || '';
                    var sTitle = $('.sonodTitle').val() || '';
                    renderSonodSuccess(r, cName, sTitle);

                    tinymce.get("rich_editor").setContent('');
                    $('.sonodDetailsTexBox').css('display', 'none');
                    $('.selectSonodTypes').html('');
                    $('.btnSelectEnBn').html('');
                    $('.sonodTitle').val('');
                    $('.prsonInfoAssign').html('');
                    $('.verifyFormAssign').html('');
                    $('.create_new_sonod_btn').html('');
                    $('.sonod_details_input_boxes').html('');
                    if (typeof SNW_DRAFT !== 'undefined') {
                        SNW_DRAFT.clearLocal();
                        if (SNW_DRAFT.getServerDraftId()) {
                            $.post('deleteSonodDraft', { id: SNW_DRAFT.getServerDraftId() });
                        }
                    }
                    SNW.reset();
                }
            }
        });
    });

    $(document).on('click', '.create_new_trade_license_btn', function () {
        var bizNameBn = $.trim($('.tl_business_name_bn').val());
        if (!bizNameBn) {
            Swal.fire({
                title: '<strong>ব্যবসা প্রতিষ্ঠানের নাম আবশ্যক</strong>',
                icon: 'warning',
                text: 'দয়া করে ব্যবসা প্রতিষ্ঠানের নাম বাংলায় প্রদান করুন।',
            });
            $('.tl_business_name_bn').focus();
            return;
        }

        var bizType = ($('.tl_business_type_preset').val() === 'other') 
            ? $.trim($('.tl_business_type_custom').val()) 
            : $('.tl_business_type_preset').val();

        $.ajax({
            type: "post",
            url: "new_trade_license_create",
            data: {
                'data':                 tinymce.get("rich_editor").getContent(''), 
                'title':                $(".sonodTitle").val() || 'ট্রেড লাইসেন্স সনদ',
                'namebn':               $('.nameBN').val(),
                'nameen':               $('.nameEN').val(),
                'fatherbn':             $('.fatherName_s_bn').val(),
                'fatheren':             $('.fatherName_en_s').val(),
                'motherbn':             $('.motherNameBn_s').val(),
                'motheren':             $('.motherName_s_en').val(),
                'wardno':               $(".selectingWard_no option:selected").val(),
                'villid':               ($(".selectingVillage option:selected").val() === 'other' ? 0 : ($(".selectingVillage option:selected").val() || 0)),
                'homeid':               ($(".selectingHouse option:selected").val() === 'other' ? 0 : ($(".selectingHouse option:selected").val() || 0)),
                'custom_village':       ($(".selectingVillage option:selected").val() === 'other' ? $.trim($('.customVillageInput').val()) : ''),
                'custom_house':         ($(".selectingHouse option:selected").val() === 'other' ? $.trim($('.customHouseInput').val()) : ''),
                'vID':                  ($(".vSysBtn").attr('vid') || $('.nid_verify').attr('vid') || 1),
                'vType':                ($(".vSysBtn").attr('vtype') || $('input[name="verifyOptionSelect"]:checked').val() || 'nid'),
                'id_number':            ($('.nid_number').val() || $('.birthNumber').val() || ''),
                'GID':                  $(".sonod_selecting_s option:selected").val(),
                'business_ins_name':    bizNameBn,
                'business_ins_name_en': $.trim($('.tl_business_name_en').val()),
                'business_types':       bizType,
                'muldhon':              $.trim($('.tl_capital').val()),
                'license_yearss':       $('.tl_fiscal_year').val(),
                'license_fee_yearly':   $.trim($('.tl_fee_yearly').val()),
                'license_fee_vats':     $.trim($('.tl_fee_vat').val()),
                'pesha_bebsa_o_kor':    $.trim($('.tl_fee_tax').val()),
                'total_charge':         $.trim($('.tl_total_charge').val()),
                'tax_id_no':            $.trim($('.tl_tin_no').val()),
                'person_mobile_no':     $.trim($('.mobileNumber').val()),
                'tl_holding_no':        $.trim($('.tl_holding_no').val()),
                'tl_market_road':       $.trim($('.tl_market_road').val()),
            },
            success: function (r) {
                if (parseInt(r) == 0) {
                    Swal.fire({
                        title: '<strong>ট্রেড লাইসেন্স তৈরি হয়নি</strong>',
                        icon: 'error',
                        text: 'দয়া করে সকল তথ্য সঠিকভাবে পূরণ করুন।',
                    });
                } else {
                    var cName = $('.nameBN').val() || bizNameBn;
                    var sTitle = 'ট্রেড লাইসেন্স সনদ';
                    renderSonodSuccess(r, cName, sTitle);

                    tinymce.get("rich_editor").setContent('');
                    $('.sonodDetailsTexBox').css('display', 'none');
                    $('.selectSonodTypes').html('');
                    $('.btnSelectEnBn').html('');
                    $('.sonodTitle').val('');
                    $('.prsonInfoAssign').html('');
                    $('.verifyFormAssign').html('');
                    $('.create_new_sonod_btn').html('');
                    $('.sonod_details_input_boxes').html('');
                    if (typeof SNW_DRAFT !== 'undefined') {
                        SNW_DRAFT.clearLocal();
                        if (SNW_DRAFT.getServerDraftId()) {
                            $.post('deleteSonodDraft', { id: SNW_DRAFT.getServerDraftId() });
                        }
                    }
                    SNW.reset();
                }
            }
        });
    });

    function renderSonodSuccess(uniqId, citizenName, sonodType) {
        Swal.fire({
            title: '<strong style="color:#0f172a; font-size:18px;">ডিজিটাল সনদ সফলভাবে তৈরি হয়েছে!</strong>',
            icon: 'success',
            html: `
                <div style="padding: 10px 0; text-align: center;">
                    <p style="font-size: 13px; color: #475569; margin-bottom: 16px;">
                        নাগরিকের সনদ প্রস্তুত সম্পন্ন হয়েছে। নিচের বাটন থেকে সনদ প্রিন্ট অথবা ডাউনলোড করুন:
                    </p>
                    <div style="display: flex; flex-direction: column; gap: 8px; max-width: 320px; margin: 0 auto;">
                        <a href="editIssuedSonod?id=${uniqId}" 
                           style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 10px 16px; border-radius: 12px; background: #d97706; color: #ffffff; font-weight: 700; font-size: 13px; text-decoration: none; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                            <i class="ph-bold ph-pencil-simple"></i>
                            <span>সনদ সম্পাদনা করুন (৩ ঘন্টার মধ্যে সরাসরি)</span>
                        </a>
                        <a href="psonod?id=${uniqId}&download=1" 
                           style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 10px 16px; border-radius: 12px; background: #0284c7; color: #ffffff; font-weight: 700; font-size: 13px; text-decoration: none; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                            <i class="ph-bold ph-download-simple"></i>
                            <span>সরাসরি PDF ডাউনলোড</span>
                        </a>
                        <a href="psonod?id=${uniqId}&pad=1" target="_blank" 
                           style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 10px 16px; border-radius: 12px; background: #059669; color: #ffffff; font-weight: 700; font-size: 13px; text-decoration: none; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                            <i class="ph-bold ph-printer"></i>
                            <span>অফিসিয়াল প্যাডসহ প্রিন্ট / প্রিভিউ</span>
                        </a>
                        <a href="nsonod?id=${uniqId}" target="_blank" 
                           style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 10px 16px; border-radius: 12px; background: #4f46e5; color: #ffffff; font-weight: 700; font-size: 13px; text-decoration: none;">
                            <i class="ph-bold ph-file-text"></i>
                            <span>প্যাড ছাড়া প্রিন্ট (সাদা কাগজে)</span>
                        </a>
                        <a href="sonod_list" 
                           style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 9px 16px; border-radius: 12px; background: #f1f5f9; color: #334155; font-weight: 600; font-size: 12px; text-decoration: none; border: 1px solid #cbd5e1;">
                            <i class="ph-bold ph-list-dashes"></i>
                            <span>ইস্যুকৃত সনদের তালিকা দেখুন</span>
                        </a>
                    </div>
                </div>
            `,
            showConfirmButton: false,
            showCloseButton: true
        });

        $('.successSonodMsg').html(
            `<div class="bg-white border-2 border-emerald-500 rounded-2xl p-6 shadow-sm mb-6 text-center space-y-4">
                <div class="w-14 h-14 rounded-full bg-emerald-100 text-emerald-600 mx-auto flex items-center justify-center text-3xl">
                    <i class="ph-bold ph-seal-check"></i>
                </div>
                <div>
                    <h3 class="text-lg font-extrabold text-neutral-900">ডিজিটাল সনদ সফলভাবে প্রদান করা হয়েছে!</h3>
                    <p class="text-xs text-neutral-500 mt-1">
                        সনদটি সিস্টেমের কেন্দ্রীয় ডাটাবেসে সংরক্ষিত হয়েছে। নিচের অপশনগুলো থেকে প্রিন্ট, ডাউনলোড অথবা সম্পাদনা করুন।
                    </p>
                    ${citizenName ? `<div class="mt-2 text-xs font-semibold text-neutral-700 bg-neutral-50 py-1 px-3 rounded-lg inline-block">নাগরিক: ${citizenName}</div>` : ''}
                </div>
                <div class="flex flex-wrap items-center justify-center gap-3 pt-2">
                    <a href="editIssuedSonod?id=${uniqId}" 
                       style="background-color: #d97706; color: #ffffff;"
                       class="px-4 py-2.5 rounded-xl font-bold text-xs inline-flex items-center gap-2 shadow-sm hover:opacity-90 transition">
                        <i class="ph-bold ph-pencil-simple text-sm"></i>
                        <span>তথ্য সম্পাদনা (৩ ঘণ্টার গ্রেস)</span>
                    </a>
                    <a href="psonod?id=${uniqId}&download=1" 
                       style="background-color: #0284c7; color: #ffffff;"
                       class="px-4 py-2.5 rounded-xl font-bold text-xs inline-flex items-center gap-2 shadow-sm hover:opacity-90 transition">
                        <i class="ph-bold ph-download-simple text-sm"></i>
                        <span>সরাসরি PDF ডাউনলোড</span>
                    </a>
                    <a href="psonod?id=${uniqId}&pad=1" target="_blank" 
                       style="background-color: #059669; color: #ffffff;"
                       class="px-4 py-2.5 rounded-xl font-bold text-xs inline-flex items-center gap-2 shadow-sm hover:opacity-90 transition">
                        <i class="ph-bold ph-printer text-sm"></i>
                        <span>অফিসিয়াল প্যাডসহ প্রিন্ট / প্রিভিউ</span>
                    </a>
                    <a href="nsonod?id=${uniqId}" target="_blank" 
                       style="background-color: #4f46e5; color: #ffffff;"
                       class="px-4 py-2.5 rounded-xl font-bold text-xs inline-flex items-center gap-2 shadow-sm hover:opacity-90 transition">
                        <i class="ph-bold ph-file-text text-sm"></i>
                        <span>প্যাড ছাড়া প্রিন্ট (সাদা কাগজে)</span>
                    </a>
                    <a href="sonod_list" 
                       style="background-color: #f1f5f9; color: #1e293b; border: 1px solid #cbd5e1;"
                       class="px-4 py-2.5 rounded-xl font-bold text-xs inline-flex items-center gap-2 hover:bg-neutral-200 transition">
                        <i class="ph-bold ph-list-dashes text-sm text-indigo-600"></i>
                        <span>ইস্যুকৃত সকল সনদের তালিকা</span>
                    </a>
                    <button type="button" onclick="location.reload();" 
                       style="background-color: #ffffff; color: #64748b; border: 1px solid #e2e8f0;"
                       class="px-3.5 py-2.5 rounded-xl font-semibold text-xs inline-flex items-center gap-1.5 hover:bg-neutral-50 transition cursor-pointer">
                        <i class="ph-bold ph-arrows-clockwise text-sm"></i>
                        <span>নতুন আরেকটি সনদ</span>
                    </button>
                </div>
            </div>`
        );
    }

    $(document).on('click', '.createNewSonod', function () {         
        $.ajax({
            type: "post",
            url: "insertNewSond",
            data: {
                data:      tinymce.get("rich_editor").getContent(''), 
                title:     $(".sonodTitle").val(),
                namebn:    $('.nameBN').val(),
                nameen:    $('.nameEN').val(),
                fatherbn:  $('.fatherName_s_bn').val(),
                fatheren:  $('.fatherName_en_s').val(),
                motherbn:  $('.motherNameBn_s').val(),
                motheren:  $('.motherName_s_en').val(),
                wardno:         $(".selectingWard_no option:selected").val(),
                villid:         ($(".selectingVillage option:selected").val() === 'other' ? 0 : ($(".selectingVillage option:selected").val() || 0)),
                homeid:         ($(".selectingHouse option:selected").val() === 'other' ? 0 : ($(".selectingHouse option:selected").val() || 0)),
                custom_village: ($(".selectingVillage option:selected").val() === 'other' ? $.trim($('.customVillageInput').val()) : ''),
                custom_house:   ($(".selectingHouse option:selected").val() === 'other' ? $.trim($('.customHouseInput').val()) : ''),
                vID:            ($(".vSysBtn").attr('vid') || $('.nid_verify').attr('vid') || 1),
                vType:          ($(".vSysBtn").attr('vtype') || $('input[name="verifyOptionSelect"]:checked').val() || 'nid'),
                id_number:      ($('.nid_number').val() || $('.birthNumber').val() || ''),
                GID:       $(".sonod_selecting_s option:selected").val(),
                custom_id: $(".sonod_selecting_s option:selected").data('custom-id') || '',
                langType:  $('.englishSonodBtnEN').hasClass('bg-white') ? 2 : 1,
                person_photo: ($('.person_image_views').attr('src') && $('.person_image_views').attr('src').indexOf('data:image') === 0) ? $('.person_image_views').attr('src') : ''
            },
            success: function (r) { 
                if (parseInt(r) == 0) {
                    Swal.fire({
                        title: '<strong>সনদ তৈরি হয়নি</strong>',
                        icon: 'error',
                        html: `দয়া করে সকল তথ্য পূরণ করুন`,
                        showConfirmButton: true,
                    });
                } else {
                    var cName = $('.nameBN').val() || '';
                    var sTitle = $('.sonodTitle').val() || '';
                    renderSonodSuccess(r, cName, sTitle);

                    tinymce.get("rich_editor").setContent('');
                    $('.sonodDetailsTexBox').css('display', 'none');
                    $('.selectSonodTypes').html('');
                    $('.btnSelectEnBn').html('');
                    $('.sonodTitle').val('');
                    $('.prsonInfoAssign').html('');
                    $('.verifyFormAssign').html('');
                    if (typeof SNW_DRAFT !== 'undefined') {
                        SNW_DRAFT.clearLocal();
                        if (SNW_DRAFT.getServerDraftId()) {
                            $.post('deleteSonodDraft', { id: SNW_DRAFT.getServerDraftId() });
                        }
                    }
                    SNW.reset();
                }
            }
        });
    });

    function showUploadedImage(input, targetSelector) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $(targetSelector).attr('src', e.target.result);
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    $(document).on('change', '.person_verify_sonod_pic', function () {
        showUploadedImage(this, '.person_image_views');
    });

    /*
     * DRAFT & RELOAD PROTECTION ENGINE (SNW_DRAFT)
     */
    var SNW_DRAFT = (function () {
        var TENANT_ID = '<?= (int)tenant_code(); ?>';
        var STORAGE_KEY = 'vireta_sonod_draft_t_' + TENANT_ID;
        var serverDraftId = null;
        var saveTimer = null;

        function collectData() {
            var step = 1;
            if (SNW.isOpen(4)) step = 4;
            else if (SNW.isOpen(3)) step = 3;
            else if (SNW.isOpen(2)) step = 2;
            else step = 1;

            var vOption = $('input[name="verifyOptionSelect"]:checked').val() || 'nid';
            var nidVal = $('.nid_number').val() || '';
            var brnVal = $('.birthNumber').val() || '';
            var idNum = (vOption === 'birth') ? brnVal : nidVal;

            var d = {
                tenantId: TENANT_ID,
                serverDraftId: serverDraftId,
                step: step,
                verifyOption: vOption,
                nidNumber: nidVal,
                birthNumber: brnVal,
                idNumber: idNum,
                dob: $('.personDOB').val() || '',
                vId: $('.nid_verify').attr('vid') || $('.vSysBtn').attr('vid') || '',
                vType: $('.nid_verify').attr('vtype') || $('.vSysBtn').attr('vtype') || vOption,
                person: {
                    nameBn: $('.nameBN').val() || '',
                    nameEn: $('.nameEN').val() || '',
                    fatherBn: $('.fatherName_s_bn').val() || '',
                    fatherEn: $('.fatherName_en_s').val() || '',
                    motherBn: $('.motherNameBn_s').val() || '',
                    motherEn: $('.motherName_s_en').val() || '',
                    ward: $('.selectingWard_no').val() || '',
                    village: $('.selectingVillage').val() || '',
                    customVillage: $('.customVillageInput').val() || '',
                    house: $('.selectingHouse').val() || '',
                    customHouse: $('.customHouseInput').val() || ''
                },
                sonod: {
                    lang: $('.englishSonodBtnEN').hasClass('bg-white') ? 2 : 1,
                    gid: $('.sonod_selecting_s').val() || '',
                    customId: $('.sonod_selecting_s option:selected').data('custom-id') || ''
                },
                details: {
                    title: $('.sonodTitle').val() || '',
                    content: (typeof tinymce !== 'undefined' && tinymce.get("rich_editor")) ? tinymce.get("rich_editor").getContent() : '',
                    isWarish: ($('.sonod_selecting_s').val() == '2'),
                    deadNid: $('.dead_person_nid').val() || '',
                    deadCertNo: $('.dead_person_dead_certify_no').val() || '',
                    deadDob: $('.dead_person_nid_dob').val() || '',
                    deadName: $('.dead_person_name_bn').val() || '',
                    deadDate: $('.dead_person_deaded_date').val() || '',
                    deadFather: $('.dead_person_father_hus_name_bn').val() || '',
                    deadMother: $('.dead_person_mothers_name_bn').val() || '',
                    deadWard: $('.selectingWard_no2').val() || '',
                    deadVillage: $('.selectingVillage2').val() || '',
                    deadHouse: $('.selectingHouse2').val() || '',
                    deadGender: $('.dead_person_gender_choose').val() || '',
                    deadRelation: $('.dead_person_relation_status').val() || '',
                    deadCareer: $('.dead_person_career_option').val() || '',
                    warishList: []
                },
                updatedAt: Date.now()
            };

            if (d.details.isWarish) {
                $('.pwar').each(function () {
                    d.details.warishList.push({
                        name: $(this).find('.w_name').val() || '',
                        relation: $(this).find('.w_relation').val() || '',
                        nidbrn: $(this).find('.w_nidbrn').val() || '',
                        agedate: $(this).find('.w_age_date').val() || ''
                    });
                });
            }

            return d;
        }

        function saveLocal() {
            var data = collectData();
            if (data.step >= 2 || (data.idNumber && data.idNumber.length >= 5)) {
                try {
                    localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
                    showSaveStatus();
                } catch (e) {}
            }
        }

        function scheduleSave() {
            clearTimeout(saveTimer);
            saveTimer = setTimeout(saveLocal, 800);
        }

        function showSaveStatus() {
            var $badge = $('#draftSaveStatusBadge');
            $badge.stop(true, true).fadeIn(200);
            setTimeout(function () {
                $badge.fadeOut(600);
            }, 2500);
            if (SNW.isOpen(2) || SNW.isOpen(3) || SNW.isOpen(4)) {
                $('#btnSaveDraftCurrentStep').removeClass('hidden');
            }
        }

        function clearLocal() {
            try {
                localStorage.removeItem(STORAGE_KEY);
            } catch (e) {}
            $('#draftRecoveryBanner').slideUp(200);
            $('#draftSaveStatusBadge').hide();
        }

        function saveServer(silent) {
            var data = collectData();
            var title = data.details.title || $('.sonod_selecting_s option:selected').text() || 'খসড়া সনদ';
            var name = data.person.nameBn || data.person.nameEn || '';
            var nid = data.idNumber || '';
            var gid = data.sonod.gid || '';

            return $.ajax({
                type: "POST",
                url: "saveSonodDraft",
                data: {
                    id: serverDraftId || '',
                    step_number: data.step,
                    draft_title: title.trim(),
                    applicant_name: name.trim(),
                    applicant_nid_brn: nid.trim(),
                    sonod_type_id: gid,
                    payload: JSON.stringify(data)
                },
                success: function (res) {
                    if (res && res.status === 'success') {
                        serverDraftId = res.draft_id;
                        data.serverDraftId = serverDraftId;
                        localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
                        if (!silent) {
                            Swal.fire({
                                icon: 'success',
                                title: 'খসড়া সংরক্ষিত হয়েছে',
                                text: 'আপনার খসড়াটি সার্ভারে সুরক্ষিতভাবে সংরক্ষণ করা হয়েছে।',
                                timer: 1800,
                                showConfirmButton: false
                            });
                        }
                    }
                }
            });
        }

        function restore(data, callback) {
            if (!data) return;
            if (data.serverDraftId) serverDraftId = data.serverDraftId;

            var vType = data.verifyOption || 'nid';
            var $radio = $('input[name="verifyOptionSelect"][value="' + vType + '"]');
            $radio.prop('checked', true).closest('.verifyOptionSection').trigger('click');

            setTimeout(function () {
                if (vType === 'birth') {
                    $('.birthNumber').val(data.birthNumber || data.idNumber || '').trigger('input');
                } else {
                    $('.nid_number').val(data.nidNumber || data.idNumber || '').trigger('input');
                }
                if (data.dob) $('.personDOB').val(data.dob);
                $('.nid_verify, .vSysBtn').attr('vid', data.vId || '').attr('vtype', data.vType || vType);

                if (data.step >= 2 && data.person) {
                    $('.prsonInfoAssign').html(personFormTemplate(data.person));
                    
                    if (data.person.nameBn) $('.nameBN').val(data.person.nameBn);
                    if (data.person.nameEn) $('.nameEN').val(data.person.nameEn);
                    if (data.person.fatherBn) $('.fatherName_s_bn').val(data.person.fatherBn);
                    if (data.person.fatherEn) $('.fatherName_en_s').val(data.person.fatherEn);
                    if (data.person.motherBn) $('.motherNameBn_s').val(data.person.motherBn);
                    if (data.person.motherEn) $('.motherName_s_en').val(data.person.motherEn);

                    if (data.person.ward) {
                        $('.selectingWard_no').val(data.person.ward);
                        $.ajax({
                            type: "post",
                            url: "frontGetVillByWard",
                            data: { ward_n: data.person.ward },
                            success: function (res) {
                                var h_data = '';
                                for (var l = 0; l < res.length; l++) {
                                    h_data += `<option value="${res[l].villages_of_union_a_iidd}"> ${res[l].bn_vill_names} </option>`; 
                                }
                                $('.selectingVillage').html(`<option value=""> গ্রাম সিলেক্ট করুন </option>${h_data}<option value="other">অন্যান্য (ম্যানুয়াল লিখুন)</option>`);
                                
                                if (data.person.village) {
                                    $('.selectingVillage').val(data.person.village);
                                    if (data.person.village === 'other') {
                                        $('.customVillageWrap').removeClass('hidden');
                                        $('.customVillageInput').val(data.person.customVillage || '');
                                        $('.selectingHouse').html(`<option value=""> বাড়ি সিলেক্ট করুন </option><option value="other">অন্যান্য (ম্যানুয়াল লিখুন)</option>`);
                                        if (data.person.house === 'other') {
                                            $('.selectingHouse').val('other');
                                            $('.customHouseWrap').removeClass('hidden');
                                            $('.customHouseInput').val(data.person.customHouse || '');
                                        }
                                    } else {
                                        $.ajax({
                                            type: "post",
                                            url: "getHomeByVill",
                                            data: { vill_nos: data.person.village },
                                            success: function (hres) {
                                                var hh_data = '';
                                                for (var hl = 0; hl < hres.length; hl++) {
                                                    hh_data += `<option value="${hres[hl].house_home_name_etc_iddiddd}"> ${hres[hl].house_na_me} </option>`; 
                                                }
                                                $('.selectingHouse').html(`<option value=""> বাড়ি সিলেক্ট করুন </option>${hh_data}<option value="other">অন্যান্য (ম্যানুয়াল লিখুন)</option>`);
                                                if (data.person.house) {
                                                    $('.selectingHouse').val(data.person.house);
                                                    if (data.person.house === 'other') {
                                                        $('.customHouseWrap').removeClass('hidden');
                                                        $('.customHouseInput').val(data.person.customHouse || '');
                                                    }
                                                }
                                            }
                                        });
                                    }
                                }
                            }
                        });
                    }

                    SNW.advance(2);

                    if (data.step >= 3) {
                        $('.afterVerifyBtnNext').trigger('click');
                        setTimeout(function () {
                            if (data.sonod && data.sonod.lang == 2) {
                                $('.englishSonodBtnEN').trigger('click');
                            } else {
                                $('.banglaSonodBtnBN').trigger('click');
                            }

                            if (data.sonod && data.sonod.gid) {
                                $('.sonod_selecting_s').val(data.sonod.gid).trigger('change');

                                if (data.step >= 4 && data.details) {
                                    setTimeout(function () {
                                        if (data.details.title) $('.sonodTitle').val(data.details.title);
                                        if (data.details.content && typeof tinymce !== 'undefined' && tinymce.get("rich_editor")) {
                                            tinymce.get("rich_editor").setContent(data.details.content);
                                        }

                                        if (data.details.isWarish) {
                                            if (data.details.deadNid) $('.dead_person_nid').val(data.details.deadNid);
                                            if (data.details.deadCertNo) $('.dead_person_dead_certify_no').val(data.details.deadCertNo);
                                            if (data.details.deadDob) $('.dead_person_nid_dob').val(data.details.deadDob);
                                            if (data.details.deadName) $('.dead_person_name_bn').val(data.details.deadName);
                                            if (data.details.deadDate) $('.dead_person_deaded_date').val(data.details.deadDate);
                                            if (data.details.deadFather) $('.dead_person_father_hus_name_bn').val(data.details.deadFather);
                                            if (data.details.deadMother) $('.dead_person_mothers_name_bn').val(data.details.deadMother);
                                            if (data.details.deadGender) $('.dead_person_gender_choose').val(data.details.deadGender);
                                            if (data.details.deadRelation) $('.dead_person_relation_status').val(data.details.deadRelation);
                                            if (data.details.deadCareer) $('.dead_person_career_option').val(data.details.deadCareer);

                                            if (data.details.warishList && data.details.warishList.length) {
                                                for (var wi = 0; wi < data.details.warishList.length; wi++) {
                                                    $('.newwars').trigger('click');
                                                    var $lastRow = $('.pwar').last();
                                                    var wItem = data.details.warishList[wi];
                                                    $lastRow.find('.w_name').val(wItem.name || '');
                                                    $lastRow.find('.w_relation').val(wItem.relation || '');
                                                    $lastRow.find('.w_nidbrn').val(wItem.nidbrn || '');
                                                    $lastRow.find('.w_age_date').val(wItem.agedate || '');
                                                }
                                            }
                                        }

                                        SNW.advance(4);
                                    }, 400);
                                }
                            }
                        }, 250);
                    }
                }

                $('#draftRecoveryBanner').slideUp(200);
                Swal.fire({
                    icon: 'success',
                    title: 'খসড়া পুনরুদ্ধার হয়েছে',
                    text: 'আপনার পূর্ববর্তী তথ্য সফলভাবে রিস্টোর করা হয়েছে।',
                    timer: 1600,
                    showConfirmButton: false
                });

                if (typeof callback === 'function') callback();
            }, 250);
        }

        function checkInitDraft() {
            var urlParams = new URLSearchParams(window.location.search);
            var queryDraftId = urlParams.get('draft_id');
            if (queryDraftId) {
                $.ajax({
                    type: "GET",
                    url: "getSonodDraft",
                    data: { id: queryDraftId },
                    success: function (res) {
                        if (res && res.status === 'success' && res.draft) {
                            try {
                                var payload = JSON.parse(res.draft.payload_json);
                                payload.serverDraftId = res.draft.id;
                                restore(payload);
                            } catch (e) {}
                        }
                    }
                });
                return;
            }

            try {
                var raw = localStorage.getItem(STORAGE_KEY);
                if (raw) {
                    var parsed = JSON.parse(raw);
                    if (parsed && (parsed.step >= 2 || (parsed.idNumber && parsed.idNumber.length >= 8))) {
                        restore(parsed);
                    }
                }
            } catch (e) {}
        }

        return {
            scheduleSave: scheduleSave,
            saveLocal: saveLocal,
            saveServer: saveServer,
            clearLocal: clearLocal,
            restore: restore,
            checkInitDraft: checkInitDraft,
            getServerDraftId: function () { return serverDraftId; }
        };
    })();

    $(document).on('input change', '.nameBN, .nameEN, .fatherName_s_bn, .fatherName_en_s, .motherNameBn_s, .motherName_s_en, .selectingWard_no, .selectingVillage, .customVillageInput, .selectingHouse, .customHouseInput, .sonodTitle, .sonod_selecting_s', function () {
        if (typeof SNW_DRAFT !== 'undefined') {
            SNW_DRAFT.scheduleSave();
        }
    });

    $(function () {
        var curMethod = $('input[name="verifyOptionSelect"]:checked').val() || 'nid';
        if (!$('.verifyFormAssign').children().length) {
            renderVerificationMethod(curMethod);
        } else if (typeof $.fn.datepicker !== 'undefined') {
            $('.verifyFormAssign .datePicks').not('.hasDatepicker').datepicker({
                dateFormat: 'yy-mm-dd',
                changeMonth: true,
                changeYear: true,
                yearRange: "1920:+10",
                showAnim: "fadeIn"
            });
        }
        if (typeof SNW_DRAFT !== 'undefined') {
            SNW_DRAFT.checkInitDraft();
        }
    });
</script>
