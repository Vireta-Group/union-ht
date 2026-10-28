<?php
/**
 * 24/7 Support Hub modal.
 *
 * Ported from resources/js/components/SupportModal.jsx. The React version
 * mounted conditionally on an isOpen prop. This one is always in the DOM and
 * uses the same modal contract as the rest of the public site:
 *
 *   trigger:  <button data-toggle="modal" data-target="#supportModal">
 *   dismiss:  <button data-dismiss="modal">
 *
 * inc/assets/js/ht-modal.js handles open, close, the backdrop, Escape and
 * scroll locking. The .modal / .modal-dialog / .modal-content box CSS lives in
 * the @layer components block of resources/css/portal.css. Everything inside
 * .modal-content is Tailwind, exactly as it was in React.
 *
 * Because it is now real modal markup, it is keyboard-reachable and gets
 * aria-hidden management for free — which the React version did not have.
 *
 * TWO CHANGES SINCE THE PORT
 *
 * 1. The channels are no longer written into this file. They come from
 *    satting_infos via platform_contact(), the same array /contact uses.
 *    Before that, the WhatsApp number and the inbox address here disagreed
 *    with the settings, and the phone was DISPLAYED as the literal
 *    placeholder "০১৭১৩-XXXXXX" while the tel: link beside it dialled a real
 *    number. The header still says 24/7 because that is the product's
 *    pitch and changing marketing copy is not this file's business — but
 *    the hours line below now comes from settings, so the two cannot
 *    contradict each other.
 *
 * 2. There is a form. The endpoint, Support::submit(), existed with no
 *    caller anywhere on the public site; this is the caller, and the
 *    <noscript> block is the fallback for the visitor without JavaScript,
 *    because a modal that silently does nothing is worse than no modal.
 *
 * WHY fetch() AND NOT A <form>
 * A <form> would reload the page underneath the modal, destroying the
 * reader's place, and /contact's plain-POST redirect branch is a 303 to
 * the top of a page — correct there, wrong here. So this posts JSON and
 * renders the answer in place. /contact takes the other branch; both are
 * exercised in production, which is the reason for caring about the
 * endpoint's content negotiation at all.
 *
 * The success state is an inline line of text, not a green panel that
 * replaces the form. The visitor may have more to say, and losing what
 * they typed because it worked is its own kind of bug.
 */

/*
 * One read for the page. platform_contact() memoises, so calling it in
 * every consumer is safe, but the variable is named here once because this
 * file uses it in five places and a $ht_c shadowed by a foreach would be
 * the same mistake /contact nearly shipped.
 */
$ht_c = platform_contact();
?>
        <style>
        /* Scoped styles for support modal - Live Chat Floating Widget */
        #supportModal {
            padding: 0 !important;
            overflow: hidden !important;
        }
        #supportModal .modal-dialog {
            position: fixed !important;
            bottom: 5.25rem !important; /* Right above the floating button */
            right: 1.5rem !important;
            margin: 0 !important;
            width: 370px !important;
            max-width: calc(100vw - 2rem) !important;
            max-height: min(600px, calc(100vh - 6.5rem)) !important;
            height: auto !important;
            z-index: 1060 !important;
            pointer-events: auto !important;
            transform-origin: bottom right;
            transition: transform 0.22s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.2s ease-out;
        }
        #supportModal.fade:not(.show) .modal-dialog {
            transform: translateY(16px) scale(0.95);
            opacity: 0;
        }
        #supportModal.show .modal-dialog {
            transform: translateY(0) scale(1) !important;
            opacity: 1 !important;
        }

        #supportModal .modal-content {
            border-radius: 1.25rem !important;
            border: 1px solid rgba(226, 232, 240, 0.95) !important;
            box-shadow: 0 20px 40px -10px rgba(15, 23, 42, 0.28), 0 10px 20px -5px rgba(15, 23, 42, 0.12) !important;
            overflow: hidden !important;
            display: flex !important;
            flex-direction: column !important;
            max-height: min(600px, calc(100vh - 6.5rem)) !important;
        }

        /* Subtle backdrop so the site is still visible behind the chat widget */
        .modal-backdrop.ht-chat-backdrop,
        body:has(#supportModal.show) .modal-backdrop {
            background-color: rgba(15, 23, 42, 0.2) !important;
            backdrop-filter: blur(2px) !important;
            -webkit-backdrop-filter: blur(2px) !important;
        }

        @media (max-width: 639px) {
            #supportModal .modal-dialog {
                bottom: 5rem !important;
                right: 0.75rem !important;
                left: 0.75rem !important;
                width: auto !important;
                max-width: calc(100vw - 1.5rem) !important;
                max-height: calc(100vh - 6rem) !important;
            }
        }

        #supportModal .ht-modal-header {
            background: linear-gradient(135deg, #090d16 0%, #1e1b4b 60%, #312e81 100%) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.12) !important;
            color: #ffffff !important;
        }
        #supportModal .ht-hidden {
            display: none !important;
        }
        #supportModal .ht-tab-btn {
            display: flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.5rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 600;
            border-bottom: 2px solid transparent;
            color: #64748b;
            background: transparent;
            border-top-left-radius: 0.5rem;
            border-top-right-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.15s ease-in-out;
        }
        #supportModal .ht-tab-btn:hover {
            color: #0f172a;
        }
        #supportModal .ht-tab-btn.ht-tab-active {
            border-bottom-color: #4f46e5 !important;
            color: #4338ca !important;
            background-color: #ffffff !important;
            font-weight: 700 !important;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }

        /* Stable height container to eliminate height jumping/shaking */
        #supportModal .ht-panes-wrapper {
            position: relative;
            min-height: 350px;
            max-height: calc(100vh - 14rem);
            overflow-y: auto;
        }
        #supportModal .ht-tab-pane {
            opacity: 0;
            transform: translateY(4px);
            transition: opacity 0.18s cubic-bezier(0.16, 1, 0.3, 1), transform 0.18s cubic-bezier(0.16, 1, 0.3, 1);
            display: none;
        }
        #supportModal .ht-tab-pane.ht-pane-active {
            display: block;
            opacity: 1;
            transform: translateY(0);
        }
        </style>

        <div class="modal fade" id="supportModal" tabindex="-1" aria-labelledby="supportModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content overflow-hidden rounded-2xl border border-neutral-200 shadow-2xl bg-white">

                    <!-- Modal Header -->
                    <div class="relative ht-modal-header px-4 py-3 text-white" style="background: linear-gradient(135deg, #090d16 0%, #1e1b4b 60%, #312e81 100%); color: #ffffff;">
                        <button type="button" data-dismiss="modal" aria-label="Close"
                                class="absolute top-3.5 right-3.5 flex h-7 w-7 cursor-pointer items-center justify-center rounded-full bg-white/10 text-white transition-colors hover:bg-white/25">
                            <?= icon('x', 16, 'bold') ?>
                        </button>

                        <div class="flex items-center gap-2.5 pr-8">
                            <div class="flex h-9 w-9 items-center justify-center rounded-xl border border-white/20 bg-white/10 text-amber-400 shrink-0">
                                <?= icon('headset', 20, 'fill') ?>
                            </div>
                            <div class="min-w-0">
                                <h3 id="supportModalLabel" class="text-sm font-bold leading-tight flex items-center gap-1.5 text-white">
                                    <span class="truncate">ViretaDev লাইভ হেল্পডেস্ক</span>
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-500/20 px-2 py-0.2 text-[10px] font-bold text-emerald-300 border border-emerald-400/30 shrink-0">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                        অনলাইন
                                    </span>
                                </h3>
                                <p class="text-[10.5px] text-indigo-200 truncate mt-0.5">আমরা সার্বক্ষণিক গ্রাহক সেবায় নিয়োজিত</p>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation Tabs -->
                    <div class="flex items-center border-b border-neutral-200 bg-neutral-100/75 px-3 pt-1.5 gap-1.5">
                        <button type="button" id="htTabLiveChat" class="ht-tab-btn ht-tab-active">
                            <?= icon('chat-circle-dots', 15, 'bold') ?>
                            <span>লাইভ সাপোর্ট চ্যাট</span>
                            <span class="flex h-1.5 w-1.5 relative">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-emerald-500"></span>
                            </span>
                        </button>
                        <button type="button" id="htTabChannels" class="ht-tab-btn">
                            <?= icon('phone-call', 15, 'bold') ?>
                            <span>সরাসরি যোগাযোগ</span>
                        </button>
                    </div>

                    <!-- Tab Panes Container (stable height to eliminate height jumping/shaking) -->
                    <div class="ht-panes-wrapper flex-1 overflow-y-auto">

                        <!-- Pane 1: Live Chat -->
                        <div id="htPaneLiveChat" class="ht-tab-pane ht-pane-active p-3.5 space-y-3">

                        <!-- View A: Intake Form (when no active chat) -->
                        <div id="htChatIntakeView">
                            <div class="mb-2.5 rounded-xl bg-indigo-50/70 border border-indigo-100 p-2.5 flex items-start gap-2.5 text-xs text-indigo-950">
                                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-indigo-600 text-white font-bold text-xs">
                                    <?= icon('chat-teardrop-text', 15, 'fill') ?>
                                </span>
                                <div>
                                    <p class="font-bold text-neutral-900 text-xs">তাৎক্ষণিক লাইভ চ্যাট শুরু করুন</p>
                                    <p class="text-neutral-600 text-[11px] mt-0.5 leading-snug">নাম ও মোবাইল নম্বর দিয়ে বার্তা পাঠান। সাপোর্ট টিম লাইভে উত্তর দেবে।</p>
                                </div>
                            </div>

                            <form id="htChatIntakeForm" class="space-y-2.5" novalidate>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    <div>
                                        <label for="htIntakeName" class="block text-[11px] font-bold text-neutral-700 mb-0.5">আপনার নাম <span class="text-rose-600">*</span></label>
                                        <input type="text" id="htIntakeName" name="name" maxlength="120" required
                                               class="ht-field text-xs py-1.5 px-2.5" placeholder="যেমন: মোঃ রফিকুল ইসলাম">
                                    </div>
                                    <div>
                                        <label for="htIntakePhone" class="block text-[11px] font-bold text-neutral-700 mb-0.5">মোবাইল বা ইমেইল <span class="text-rose-600">*</span></label>
                                        <input type="text" id="htIntakePhone" name="phone" maxlength="100" required
                                               class="ht-field text-xs py-1.5 px-2.5" placeholder="যেমন: ০১৭১৩-XXXXXX">
                                    </div>
                                </div>

                                <div>
                                    <label for="htIntakeSubject" class="block text-[11px] font-bold text-neutral-700 mb-0.5">বিষয় (ঐচ্ছিক)</label>
                                    <input type="text" id="htIntakeSubject" name="subject" maxlength="200"
                                           class="ht-field text-xs py-1.5 px-2.5" placeholder="যেমন: ডোমেইন বা প্যাকেজ সংক্রান্ত">
                                </div>

                                <div>
                                    <label for="htIntakeMessage" class="block text-[11px] font-bold text-neutral-700 mb-0.5">বার্তা <span class="text-rose-600">*</span></label>
                                    <textarea id="htIntakeMessage" name="message" rows="2" maxlength="4000" required
                                              class="ht-field resize-none text-xs py-1.5 px-2.5"
                                              placeholder="আপনার সমস্যা বা প্রশ্নের বিষয়টি লিখুন…"></textarea>
                                </div>

                                <!-- Honeypot -->
                                <div aria-hidden="true" class="absolute left-[-9999px] top-0 h-0 w-0 overflow-hidden">
                                    <input type="text" id="htIntakeWebsite" name="website" tabindex="-1" autocomplete="off" value="">
                                </div>

                                <div class="pt-0.5">
                                    <button type="submit" id="htIntakeSubmitBtn"
                                            class="w-full ht-btn ht-btn-primary text-xs justify-center font-bold py-2">
                                        <?= icon('paper-plane-tilt', 14, 'bold') ?>
                                        <span>লাইভ চ্যাট শুরু করুন</span>
                                    </button>
                                </div>

                                <p id="htIntakeResult" role="status" aria-live="polite"
                                   class="ht-hidden rounded-xl px-2.5 py-1.5 text-xs leading-relaxed" style="display: none;"></p>
                            </form>
                        </div>

                        <!-- View B: Active Live Chat Window (loaded when chat started) -->
                        <div id="htChatActiveView" class="ht-hidden flex flex-col space-y-2.5" style="display: none;">
                            <!-- Chat Active Header -->
                            <div class="flex items-center justify-between border-b border-neutral-200 pb-2">
                                <div class="flex items-center gap-2">
                                    <div class="relative">
                                        <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-xs">
                                            <?= icon('headset', 16, 'bold') ?>
                                        </span>
                                        <span class="absolute -bottom-0.5 -right-0.5 flex h-2.5 w-2.5">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500 border border-white"></span>
                                        </span>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-neutral-900 flex items-center gap-1">
                                            <span>সাপোর্ট এক্সিকিউটিভ</span>
                                            <span id="htChatTicketBadge" class="font-mono text-[9px] text-indigo-700 font-semibold bg-indigo-50 border border-indigo-200 px-1 py-0.2 rounded">#TKT</span>
                                        </p>
                                        <p id="htChatSseStatus" class="text-[10px] text-emerald-600 font-medium">● লাইভ সংযুক্ত</p>
                                    </div>
                                </div>
                                <button type="button" id="htChatResetBtn"
                                        class="text-[10px] font-bold text-neutral-500 hover:text-rose-600 bg-neutral-100 hover:bg-rose-50 px-2 py-1 rounded-md border border-neutral-200 transition-colors cursor-pointer">
                                    নতুন চ্যাট
                                </button>
                            </div>

                            <!-- Chat Messages Stream -->
                            <div id="htChatMessagesList"
                                 class="space-y-2 p-2.5 bg-neutral-50/80 rounded-xl border border-neutral-200 overflow-y-auto"
                                 style="height: 220px; scroll-behavior: smooth;">
                                <!-- Bubbles will be injected here -->
                            </div>

                            <!-- Chat Reply Box -->
                            <form id="htChatReplyForm" class="flex items-end gap-1.5 pt-0.5" novalidate>
                                <div class="grow relative">
                                    <textarea id="htChatReplyInput" rows="1" maxlength="4000" required
                                              placeholder="বার্তা লিখুন… (Enter চাপুন)"
                                              class="ht-field text-xs resize-none py-2 px-2.5 leading-snug" style="height: 38px;"></textarea>
                                </div>
                                <button type="submit" id="htChatReplySendBtn"
                                        class="ht-btn ht-btn-primary text-xs shrink-0 px-3 h-[38px] justify-center cursor-pointer">
                                    <?= icon('paper-plane-tilt', 14, 'bold') ?>
                                    <span>পাঠান</span>
                                </button>
                            </form>
                            <p id="htChatReplyError" class="ht-hidden text-[10px] text-rose-600 font-bold px-1" style="display: none;"></p>
                        </div>

                    </div>

                    <!-- Pane 2: Direct Channels -->
                    <div id="htPaneChannels" class="ht-tab-pane p-3.5 space-y-2 text-xs" style="display: none;">
                        <?php
                        $ht_channels = array();

                        if ($ht_c['phone1_href'] !== '') {
                            $ht_channels[] = array(
                                'href'  => $ht_c['phone1_href'],
                                'chip'  => 'bg-indigo-600',
                                'hover' => 'hover:border-indigo-300 hover:bg-indigo-50',
                                'arrow' => 'group-hover:text-indigo-600',
                                'icon'  => 'phone',
                                'size'  => 18,
                                'wt'    => 'bold',
                                'title' => 'সরাসরি ফোন কল',
                                'sub'   => $ht_c['phone1_label'],
                                'ext'   => FALSE,
                            );
                        }

                        if ($ht_c['phone2_href'] !== '') {
                            $ht_channels[] = array(
                                'href'  => $ht_c['phone2_href'],
                                'chip'  => 'bg-indigo-600',
                                'hover' => 'hover:border-indigo-300 hover:bg-indigo-50',
                                'arrow' => 'group-hover:text-indigo-600',
                                'icon'  => 'phone',
                                'size'  => 18,
                                'wt'    => 'regular',
                                'title' => 'বিকল্প নম্বর',
                                'sub'   => $ht_c['phone2_label'],
                                'ext'   => FALSE,
                            );
                        }

                        if ($ht_c['whatsapp_href'] !== '') {
                            $ht_channels[] = array(
                                'href'  => $ht_c['whatsapp_href'],
                                'chip'  => 'bg-emerald-600',
                                'hover' => 'hover:border-emerald-300 hover:bg-emerald-50',
                                'arrow' => 'group-hover:text-emerald-700',
                                'icon'  => 'whatsapp-logo',
                                'size'  => 20,
                                'wt'    => 'fill',
                                'title' => 'WhatsApp চ্যাট সাপোর্ট',
                                'sub'   => 'তাত্ক্ষণিক মেসেজ ও স্ক্রিনশট সহায়তা',
                                'ext'   => TRUE,
                            );
                        }

                        if ($ht_c['mail_href'] !== '') {
                            $ht_channels[] = array(
                                'href'  => $ht_c['mail_href'],
                                'chip'  => 'bg-amber-600',
                                'hover' => 'hover:border-amber-300 hover:bg-amber-50',
                                'arrow' => 'group-hover:text-amber-800',
                                'icon'  => 'envelope',
                                'size'  => 18,
                                'wt'    => 'bold',
                                'title' => 'ইমেইল হেল্পডেস্ক',
                                'sub'   => $ht_c['mail'],
                                'ext'   => FALSE,
                            );
                        }
                        ?>

                        <?php if ($ht_channels === []): ?>
                        <div class="flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 p-2.5 text-xs text-amber-900">
                            <?= icon('warning-circle', 15, 'bold', 'mt-0.5 shrink-0 text-amber-700') ?>
                            <span>ফোন, হোয়াটসঅ্যাপ বা ইমেইল নম্বর সেট করা নেই।</span>
                        </div>
                        <?php endif; ?>

                        <?php foreach ($ht_channels as $ht_ch): ?>
                        <a href="<?= $ht_ch['href'] ?>"<?= $ht_ch['ext'] ? ' target="_blank" rel="noopener noreferrer"' : '' ?>
                           class="group flex items-center gap-3 rounded-xl border border-neutral-200 bg-neutral-50/80 p-2.5 transition-all <?= $ht_ch['hover'] ?>">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg <?= $ht_ch['chip'] ?> text-lg text-white shadow-xs">
                                <?= icon($ht_ch['icon'], $ht_ch['size'], $ht_ch['wt']) ?>
                            </div>
                            <div class="grow min-w-0">
                                <h4 class="font-bold text-xs text-neutral-900 transition-colors <?= $ht_ch['arrow'] ?>"><?= $ht_ch['title'] ?></h4>
                                <p class="text-[11px] font-medium text-neutral-500 truncate"><?= $ht_ch['sub'] ?></p>
                            </div>
                            <?= icon('caret-right', 15, 'regular', 'text-neutral-400 transition-transform group-hover:translate-x-0.5') ?>
                        </a>
                        <?php endforeach; ?>

                        <?php if ($ht_c['hours'] !== ''): ?>
                        <div class="flex items-center gap-1.5 rounded-xl border border-indigo-100 bg-indigo-50 p-2 text-[11px] text-indigo-900">
                            <?= icon('clock', 14, 'bold', 'shrink-0 text-indigo-600') ?>
                            <span class="font-medium">সময়: <?= $ht_c['hours'] ?></span>
                        </div>
                        <?php endif; ?>
                    </div>

                    </div> <!-- /.ht-panes-wrapper -->

                    <!-- Modal Footer -->
                    <div class="flex justify-between items-center border-t border-neutral-100 bg-neutral-50 px-4 py-2 mt-auto">
                        <span class="text-[10px] text-neutral-400 flex items-center gap-1.5">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                            ২৪/৭ লাইভ হেল্পডেস্ক
                        </span>
                        <button type="button" data-dismiss="modal" class="ht-btn ht-btn-secondary text-[11px] py-1 px-2.5">বন্ধ করুন</button>
                    </div>

                </div>
            </div>
        </div>

        <script>
        (function () {
            // Tab switching
            var tabLive      = document.getElementById('htTabLiveChat');
            var tabChannels  = document.getElementById('htTabChannels');
            var paneLive     = document.getElementById('htPaneLiveChat');
            var paneChannels = document.getElementById('htPaneChannels');

            function switchTab(target) {
                if (target === 'live') {
                    if (tabLive) tabLive.classList.add('ht-tab-active');
                    if (tabChannels) tabChannels.classList.remove('ht-tab-active');

                    if (paneChannels && paneChannels.classList.contains('ht-pane-active')) {
                        paneChannels.classList.remove('ht-pane-active');
                        setTimeout(function () {
                            paneChannels.style.display = 'none';
                            if (paneLive) {
                                paneLive.style.display = 'block';
                                void paneLive.offsetWidth;
                                paneLive.classList.add('ht-pane-active');
                            }
                        }, 120);
                    } else if (paneLive && !paneLive.classList.contains('ht-pane-active')) {
                        paneLive.style.display = 'block';
                        void paneLive.offsetWidth;
                        paneLive.classList.add('ht-pane-active');
                    }
                } else {
                    if (tabChannels) tabChannels.classList.add('ht-tab-active');
                    if (tabLive) tabLive.classList.remove('ht-tab-active');

                    if (paneLive && paneLive.classList.contains('ht-pane-active')) {
                        paneLive.classList.remove('ht-pane-active');
                        setTimeout(function () {
                            paneLive.style.display = 'none';
                            if (paneChannels) {
                                paneChannels.style.display = 'block';
                                void paneChannels.offsetWidth;
                                paneChannels.classList.add('ht-pane-active');
                            }
                        }, 120);
                    } else if (paneChannels && !paneChannels.classList.contains('ht-pane-active')) {
                        paneChannels.style.display = 'block';
                        void paneChannels.offsetWidth;
                        paneChannels.classList.add('ht-pane-active');
                    }
                }
            }

            if (tabLive) {
                tabLive.addEventListener('click', function () {
                    switchTab('live');
                });
            }
            if (tabChannels) {
                tabChannels.addEventListener('click', function () {
                    switchTab('channels');
                });
            }

            // Chat engine elements
            var intakeView = document.getElementById('htChatIntakeView');
            var activeView = document.getElementById('htChatActiveView');
            var intakeForm = document.getElementById('htChatIntakeForm');
            var intakeBtn  = document.getElementById('htIntakeSubmitBtn');
            var intakeRes  = document.getElementById('htIntakeResult');

            var badge      = document.getElementById('htChatTicketBadge');
            var sseStatus  = document.getElementById('htChatSseStatus');
            var resetBtn   = document.getElementById('htChatResetBtn');
            var list       = document.getElementById('htChatMessagesList');
            var replyForm  = document.getElementById('htChatReplyForm');
            var replyInput = document.getElementById('htChatReplyInput');
            var replyBtn   = document.getElementById('htChatReplySendBtn');
            var replyErr   = document.getElementById('htChatReplyError');

            var SESSION_KEY = 'vireta_support_live_session';
            var sseSource   = null;
            var seenReplyIds = {};

            function showIntakeView() {
                if (intakeView) {
                    intakeView.classList.remove('ht-hidden');
                    intakeView.style.display = 'block';
                }
                if (activeView) {
                    activeView.classList.add('ht-hidden');
                    activeView.style.display = 'none';
                }
            }

            function showActiveView() {
                if (intakeView) {
                    intakeView.classList.add('ht-hidden');
                    intakeView.style.display = 'none';
                }
                if (activeView) {
                    activeView.classList.remove('ht-hidden');
                    activeView.style.display = 'flex';
                }
            }

            function playCustomerChime() {
                try {
                    var ctx = new (window.AudioContext || window.webkitAudioContext)();
                    var osc = ctx.createOscillator();
                    var gain = ctx.createGain();
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(523.25, ctx.currentTime); // C5
                    osc.frequency.exponentialRampToValueAtTime(783.99, ctx.currentTime + 0.15); // G5
                    gain.gain.setValueAtTime(0.12, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.25);
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.start();
                    osc.stop(ctx.currentTime + 0.25);
                } catch (e) {}
            }

            function getSession() {
                try {
                    var s = localStorage.getItem(SESSION_KEY);
                    return s ? JSON.parse(s) : null;
                } catch (e) { return null; }
            }

            function setSession(session) {
                try {
                    localStorage.setItem(SESSION_KEY, JSON.stringify(session));
                } catch (e) {}
            }

            function clearSession() {
                try {
                    localStorage.removeItem(SESSION_KEY);
                } catch (e) {}
                if (sseSource) {
                    sseSource.close();
                    sseSource = null;
                }
                seenReplyIds = {};
                showIntakeView();
                if (intakeForm) intakeForm.reset();
            }

            if (resetBtn) {
                resetBtn.addEventListener('click', function () {
                    if (confirm('আপনি কি বর্তমান চ্যাট শেষ করে নতুন চ্যাট শুরু করতে চান?')) {
                        clearSession();
                    }
                });
            }

            function escapeHtml(str) {
                return (str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\n/g, '<br>');
            }

            function appendBubble(isStaff, senderName, message, timeText) {
                if (!list) return;

                var bubble = document.createElement('div');
                bubble.className = 'flex flex-col ' + (isStaff ? 'items-start' : 'items-end');

                var innerHtml = '';
                if (isStaff) {
                    innerHtml = '<div class="flex items-start gap-2 max-w-[85%]">' +
                        '<div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-indigo-600 text-white text-xs">' +
                        '<?= icon('headset', 14, 'bold') ?>' +
                        '</div>' +
                        '<div>' +
                        '<div class="rounded-2xl rounded-tl-sm bg-white border border-neutral-200 p-3 text-xs text-neutral-800 shadow-xs">' +
                        '<p class="text-[10px] font-bold text-indigo-700 mb-1">' + escapeHtml(senderName || 'সাপোর্ট প্রতিনিধি') + '</p>' +
                        '<div class="leading-relaxed">' + escapeHtml(message) + '</div>' +
                        '</div>' +
                        '<p class="text-[10px] text-neutral-400 mt-1 pl-1">' + (timeText || 'এইমাত্র') + '</p>' +
                        '</div>' +
                        '</div>';
                } else {
                    innerHtml = '<div class="max-w-[85%]">' +
                        '<div class="rounded-2xl rounded-tr-sm p-3 text-xs text-white shadow-xs" style="background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%); color: #ffffff;">' +
                        '<div class="leading-relaxed">' + escapeHtml(message) + '</div>' +
                        '</div>' +
                        '<p class="text-[10px] text-neutral-400 mt-1 text-right pr-1">' + (timeText || 'এইমাত্র') + '</p>' +
                        '</div>';
                }

                bubble.innerHTML = innerHtml;
                list.appendChild(bubble);
                list.scrollTop = list.scrollHeight;
            }

            function connectSse(session) {
                if (!session || !session.ticket_id || !session.token) return;
                if (typeof EventSource === 'undefined') return;

                if (sseSource) {
                    sseSource.close();
                }

                var url = '<?= base_url('support/live_stream'); ?>?ticket_id=' + session.ticket_id + '&token=' + encodeURIComponent(session.token);
                sseSource = new EventSource(url);

                sseSource.addEventListener('connected', function () {
                    if (sseStatus) sseStatus.textContent = '● লাইভ সংযুক্ত (Real-time)';
                });

                sseSource.addEventListener('reply', function (e) {
                    try {
                        var data = JSON.parse(e.data);
                        if (data && data.ticket_id == session.ticket_id) {
                            var rId = data.reply_id || data.created_at;
                            if (seenReplyIds[rId]) return;
                            seenReplyIds[rId] = true;

                            var isStaff = (data.by === 'staff' || data.sender_role === 'staff');
                            appendBubble(isStaff, data.sender_name, data.message, data.created_at ? 'এইমাত্র' : '');
                            if (isStaff) {
                                playCustomerChime();
                            }
                        }
                    } catch (err) {}
                });

                sseSource.onerror = function () {
                    if (sseStatus) sseStatus.textContent = '● সংযোগ পুনঃস্থাপিত হচ্ছে…';
                };
            }

            function renderChatSkeleton() {
                return '<div id="htChatSkeletonContainer" class="space-y-3 py-1">' +
                    '<div class="flex items-start gap-2 max-w-[85%]">' +
                        '<div class="ht-skeleton h-7 w-7 rounded-lg shrink-0"></div>' +
                        '<div class="space-y-1.5 grow">' +
                            '<div class="ht-skeleton h-2.5 w-20 rounded"></div>' +
                            '<div class="ht-skeleton h-10 w-48 rounded-xl"></div>' +
                        '</div>' +
                    '</div>' +
                    '<div class="flex justify-end">' +
                        '<div class="ht-skeleton h-8 w-36 rounded-xl"></div>' +
                    '</div>' +
                    '<div class="flex items-start gap-2 max-w-[85%]">' +
                        '<div class="ht-skeleton h-7 w-7 rounded-lg shrink-0"></div>' +
                        '<div class="space-y-1.5 grow">' +
                            '<div class="ht-skeleton h-2.5 w-16 rounded"></div>' +
                            '<div class="ht-skeleton h-10 w-44 rounded-xl"></div>' +
                        '</div>' +
                    '</div>' +
                '</div>';
            }

            function loadChatSession(session) {
                if (!session || !session.ticket_id || !session.token) return;

                showActiveView();

                if (badge) badge.textContent = session.ticket_no || ('#' + session.ticket_id);
                if (list) {
                    list.innerHTML = renderChatSkeleton();
                }

                fetch('<?= base_url('support/history'); ?>?ticket_id=' + session.ticket_id + '&token=' + encodeURIComponent(session.token))
                    .then(function (r) { return r.json(); })
                    .then(function (res) {
                        if (list) list.innerHTML = '';

                        // Add welcome agent message
                        appendBubble(true, 'ViretaDev হেল্পডেস্ক', 'আসসালামু আলাইকুম! আমাদের টিম আপনার সাথে শীঘ্রই যুক্ত হবে। আপনার কোনো প্রশ্ন বা বিস্তারিত জানার থাকলে নিচে লিখুন।', '');

                        if (res && res.success) {
                            // Initial ticket message from user
                            if (res.ticket && res.ticket.message) {
                                appendBubble(false, session.user_name || 'আমি', res.ticket.message, res.ticket.created_at);
                            }

                            // Follow-up replies
                            if (Array.isArray(res.replies)) {
                                res.replies.forEach(function (r) {
                                    seenReplyIds[r.reply_id] = true;
                                    var isStaff = (r.sender_role === 'staff');
                                    appendBubble(isStaff, r.sender_name, r.message, r.created_at);
                                });
                            }

                            connectSse(session);
                        } else {
                            clearSession();
                        }
                    })
                    .catch(function () {
                        if (list) list.innerHTML = '';
                        appendBubble(true, 'ViretaDev হেল্পডেস্ক', 'আসসালামু আলাইকুম! আমাদের টিম আপনার সাথে শীঘ্রই যুক্ত হবে। আপনার কোনো প্রশ্ন বা বিস্তারিত জানার থাকলে নিচে লিখুন।', '');
                        connectSse(session);
                    });
            }

            // Check if active session already exists on page load
            var currentSession = getSession();
            if (currentSession && currentSession.ticket_id && currentSession.token) {
                loadChatSession(currentSession);
            }

            // Handle Intake Form submission
            if (intakeForm) {
                intakeForm.addEventListener('submit', function (ev) {
                    ev.preventDefault();

                    var name    = (document.getElementById('htIntakeName').value || '').trim();
                    var phone   = (document.getElementById('htIntakePhone').value || '').trim();
                    var subject = (document.getElementById('htIntakeSubject').value || '').trim();
                    var message = (document.getElementById('htIntakeMessage').value || '').trim();
                    var website = (document.getElementById('htIntakeWebsite').value || '').trim();

                    if (!name) {
                        showIntakeResult('দয়া করে আপনার নাম লিখুন।', false);
                        return;
                    }
                    if (!phone) {
                        showIntakeResult('দয়া করে মোবাইল নম্বর অথবা ইমেইল দিন।', false);
                        return;
                    }
                    if (!message) {
                        showIntakeResult('আপনার বার্তাটি লিখুন।', false);
                        return;
                    }

                    intakeBtn.disabled = true;
                    showIntakeResult('লাইভ চ্যাট সংযুক্ত করা হচ্ছে…', 'info');

                    var payload = {
                        name: name,
                        phone: phone,
                        subject: subject || (name + ' এর সহায়তা অনুরোধ'),
                        message: message,
                        website: website
                    };

                    fetch('<?= base_url('support/start_chat'); ?>', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        body: JSON.stringify(payload)
                    })
                    .then(function (r) { return r.json(); })
                    .then(function (res) {
                        if (res && res.success && res.ticket_id && res.token) {
                            var newSession = {
                                ticket_id: res.ticket_id,
                                ticket_no: res.ticket_no,
                                token: res.token,
                                user_name: name,
                                initial_message: message
                            };
                            setSession(newSession);
                            loadChatSession(newSession);
                            if (intakeRes) {
                                intakeRes.classList.add('ht-hidden');
                                intakeRes.style.display = 'none';
                            }
                        } else {
                            showIntakeResult((res && res.message) ? res.message : 'চ্যাট শুরু করা যায়নি। অনুগ্রহ করে আবার চেষ্টা করুন।', false);
                        }
                    })
                    .catch(function () {
                        showIntakeResult('সার্ভারের সাথে সংযোগ স্থাপন করা যায়নি। ইন্টারনেট সংযোগ চেক করুন।', false);
                    })
                    .then(function () {
                        intakeBtn.disabled = false;
                    });
                });
            }

            function showIntakeResult(msg, kind) {
                if (!intakeRes) return;
                intakeRes.classList.remove('ht-hidden');
                intakeRes.style.display = 'block';
                if (kind === 'info') {
                    intakeRes.className = 'rounded-xl border border-indigo-200 bg-indigo-50 px-3 py-2 text-xs text-indigo-900';
                } else if (kind === true) {
                    intakeRes.className = 'rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-900';
                } else {
                    intakeRes.className = 'rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-900';
                }
                intakeRes.textContent = msg;
            }

            // Handle Reply Form submission in active chat
            if (replyForm) {
                // Submit on Enter (Shift+Enter for newline)
                replyInput.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        replyForm.dispatchEvent(new Event('submit'));
                    }
                });

                replyForm.addEventListener('submit', function (ev) {
                    ev.preventDefault();

                    var session = getSession();
                    if (!session || !session.ticket_id || !session.token) {
                        clearSession();
                        return;
                    }

                    var text = (replyInput.value || '').trim();
                    if (!text) return;

                    replyBtn.disabled = true;
                    if (replyErr) {
                        replyErr.classList.add('ht-hidden');
                        replyErr.style.display = 'none';
                    }

                    fetch('<?= base_url('support/send_message'); ?>', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        body: JSON.stringify({
                            ticket_id: session.ticket_id,
                            token: session.token,
                            message: text
                        })
                    })
                    .then(function (r) { return r.json(); })
                    .then(function (res) {
                        if (res && res.success && res.reply) {
                            seenReplyIds[res.reply.reply_id] = true;
                            appendBubble(false, session.user_name || 'আমি', text, 'এইমাত্র');
                            replyInput.value = '';
                        } else {
                            if (replyErr) {
                                replyErr.textContent = (res && res.message) ? res.message : 'বার্তা পাঠানো যায়নি।';
                                replyErr.classList.remove('ht-hidden');
                                replyErr.style.display = 'block';
                            }
                        }
                    })
                    .catch(function () {
                        if (replyErr) {
                            replyErr.textContent = 'নেটওয়ার্ক ত্রুটি। আবার চেষ্টা করুন।';
                            replyErr.classList.remove('ht-hidden');
                            replyErr.style.display = 'block';
                        }
                    })
                    .then(function () {
                        replyBtn.disabled = false;
                        replyInput.focus();
                    });
                });
            }

            // Apply soft translucent style to backdrop when support widget opens
            var modalEl = document.getElementById('supportModal');
            if (modalEl && window.MutationObserver) {
                var mo = new MutationObserver(function () {
                    if (modalEl.classList.contains('show') && modalEl._htBackdrop) {
                        modalEl._htBackdrop.classList.add('ht-chat-backdrop');
                    }
                });
                mo.observe(modalEl, { attributes: true, attributeFilter: ['class'] });
            }
        })();
        </script>
