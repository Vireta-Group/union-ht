/**
 * password-toggle.js — hand-written, dependency-free.
 *
 * Generic eye on/off toggle for password fields on pages that do NOT load
 * saas.js (admin back pages, the legacy front/api login pages). It uses the
 * exact same HTML contract as saas.js's initPasswordToggles(), so markup is
 * portable between the two:
 *
 *   <div class="relative">
 *     <input type="password" data-saas-password class="... pr-10">
 *     <button type="button" data-saas-toggle-password class="..."
 *             aria-label="পাসওয়ার্ড দেখান">
 *       <span data-saas-eye="show">eye icon</span>
 *       <span data-saas-eye="hide" class="hidden">eye-slash icon</span>
 *     </button>
 *   </div>
 *
 * The button and its toggled input must share one parent element, matching
 * how saas.js looks the field up: btn.parentNode.querySelector(...).
 */
(function () {
    'use strict';

    var doc = document;

    function initToggles() {
        var buttons = doc.querySelectorAll('[data-saas-toggle-password]');
        for (var i = 0; i < buttons.length; i++) {
            (function (btn) {
                var field = btn.parentNode.querySelector('[data-saas-password]');
                if (!field) return;
                // Guard: if saas.js ever loads on the same page, one of the two
                // bindings has to step aside or the toggle would flip twice.
                if (btn.dataset.htBound) return;
                btn.dataset.htBound = '1';

                btn.addEventListener('click', function () {
                    var show = field.type === 'password';
                    field.type = show ? 'text' : 'password';
                    btn.setAttribute('aria-label', show ? 'পাসওয়ার্ড লুকান' : 'পাসওয়ার্ড দেখান');

                    var on = btn.querySelector('[data-saas-eye="show"]');
                    var off = btn.querySelector('[data-saas-eye="hide"]');
                    if (on) on.classList.toggle('hidden', show);
                    if (off) off.classList.toggle('hidden', !show);
                });
            })(buttons[i]);
        }
    }

    if (doc.readyState === 'loading') {
        doc.addEventListener('DOMContentLoaded', initToggles);
    } else {
        initToggles();
    }
})();