<?php
/**
 * Floating support hub button.
 *
 * Fixed bottom-right, present on every product page. Loaded by Template::saas()
 * immediately before the support modal it triggers.
 *
 * The React version mounted SupportModal conditionally on an isOpen prop. This
 * uses the same modal contract as the rest of the public site instead, so
 * inc/assets/js/ht-modal.js drives it — no React, no new JavaScript.
 */
?>
        <div class="fixed right-6 bottom-6 z-40">
            <button type="button"
                    class="group relative flex h-14 w-14 cursor-pointer items-center justify-center rounded-full bg-indigo-700 text-2xl text-white shadow-xl transition-all duration-200 hover:scale-110 hover:bg-indigo-800 active:scale-95"
                    data-toggle="modal" data-target="#supportModal"
                    title="২৪/৭ হেল্পডেস্ক ও সাপোর্ট">
                <?= icon('headset', 26, 'fill', 'transition-transform group-hover:rotate-12') ?>
                <span class="absolute -top-1 -right-1 h-3.5 w-3.5 rounded-full border-2 border-white bg-emerald-400"></span>
            </button>
        </div>
