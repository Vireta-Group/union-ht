/**
 * ViretaDev Platform - Production Security Guard
 * -----------------------------------------------------------------------------
 * 1. Disables DevTools shortcuts (F12, Ctrl+Shift+I, Ctrl+Shift+J, Ctrl+Shift+C, Ctrl+U, Ctrl+S)
 * 2. Disables context menu (Right Click Inspect)
 * 3. Displays prominent developer security warning banner in DevTools console
 * 4. Suppresses console methods (log, info, warn, debug, error, dir, table)
 * 5. Periodically clears console to prevent memory inspection
 * -----------------------------------------------------------------------------
 */
(function (window, document) {
    'use strict';

    // Guard: Prevent double initialization
    if (window.__VIRETA_PROD_SEC_INITIALIZED__) {
        return;
    }
    window.__VIRETA_PROD_SEC_INITIALIZED__ = true;

    // 1. Show Security Warning Banner in Console before suppression
    try {
        var titleStyle = [
            'color: #ef4444',
            'font-size: 34px',
            'font-weight: 900',
            'padding: 8px 0',
            'text-shadow: 1px 1px 2px rgba(0,0,0,0.4)'
        ].join(';');

        var boxStyle = [
            'background: #111827',
            'color: #f59e0b',
            'font-size: 14px',
            'font-weight: 600',
            'line-height: 1.6',
            'padding: 12px 18px',
            'border-radius: 8px',
            'border-left: 6px solid #ef4444',
            'margin: 6px 0'
        ].join(';');

        var brandStyle = [
            'color: #6366f1',
            'font-size: 13px',
            'font-weight: 700',
            'padding: 6px 0'
        ].join(';');

        if (window.console && typeof window.console.log === 'function') {
            console.clear();
            console.log('%c⚠️ সতর্কবার্তা / WARNING! ⚠️', titleStyle);
            console.log(
                '%c🛑 থামুন! এই ব্রাউজার কনসোলটি শুধুমাত্র সিস্টেম ডেভেলপারদের অভ্যন্তরীণ কারিগরি পরীক্ষার জন্য সংরক্ষিত।\n' +
                'এখানে অপরিচিত কোনো স্ক্রিপ্ট বা কোড পেস্ট করলে আপনার একাউন্ট, ইউনিয়ন তথ্য বা নাগরিক সনদপত্র বেদখল হতে পারে (Self-XSS Attack)।\n\n' +
                '🛑 STOP! This browser feature is intended exclusively for authorized developers.\n' +
                'Do NOT paste or run any code here. Doing so may compromise your session and sensitive parishad records.',
                boxStyle
            );
            console.log('%c🛡️ ViretaDev Multi-Tenant SaaS Portal • Production Security Active', brandStyle);
        }
    } catch (err) {
        // Fail silently
    }

    // 2. Suppress console output methods to block reverse-engineering or data inspection
    try {
        var noop = function () {};
        var methods = ['log', 'debug', 'info', 'warn', 'error', 'table', 'trace', 'dir', 'dirxml', 'group', 'groupCollapsed', 'groupEnd'];
        if (window.console) {
            for (var i = 0; i < methods.length; i++) {
                var m = methods[i];
                window.console[m] = noop;
            }
        }
    } catch (err) {
        // Fail silently
    }

    // 3. Block Right-Click Context Menu
    document.addEventListener('contextmenu', function (e) {
        // Allow right click if user is selecting text inside a normal editable input/textarea
        var target = e.target;
        var tag = target && target.tagName ? target.tagName.toUpperCase() : '';
        if (tag === 'INPUT' || tag === 'TEXTAREA' || (target && target.isContentEditable)) {
            return true;
        }
        e.preventDefault();
        e.stopPropagation();
        return false;
    }, true);

    // 4. Block Developer Tools & Source Inspection Keyboard Shortcuts
    document.addEventListener('keydown', function (e) {
        var code = e.keyCode || e.which;
        var key = e.key || '';
        var isCtrlOrMeta = e.ctrlKey || e.metaKey; // Windows Ctrl or macOS Cmd
        var isShift = e.shiftKey;
        var isAlt = e.altKey;

        // F12 (DevTools)
        if (code === 123 || key === 'F12') {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }

        // Ctrl + Shift + I (Inspect) or Cmd + Alt + I (macOS)
        if ((isCtrlOrMeta && isShift && (code === 73 || key === 'I' || key === 'i')) ||
            (e.metaKey && isAlt && (code === 73 || key === 'I' || key === 'i'))) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }

        // Ctrl + Shift + J (Console) or Cmd + Alt + J (macOS)
        if ((isCtrlOrMeta && isShift && (code === 74 || key === 'J' || key === 'j')) ||
            (e.metaKey && isAlt && (code === 74 || key === 'J' || key === 'j'))) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }

        // Ctrl + Shift + C (Element Inspector) or Cmd + Alt + C (macOS)
        if ((isCtrlOrMeta && isShift && (code === 67 || key === 'C' || key === 'c')) ||
            (e.metaKey && isAlt && (code === 67 || key === 'C' || key === 'c'))) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }

        // Ctrl + U (View Source)
        if (isCtrlOrMeta && !isShift && !isAlt && (code === 85 || key === 'U' || key === 'u')) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }

        // Ctrl + S (Save Page Offline)
        if (isCtrlOrMeta && !isShift && !isAlt && (code === 83 || key === 'S' || key === 's')) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
    }, true);

    // 5. Periodic Console Clear to sweep DevTools history
    setInterval(function () {
        try {
            if (window.console && typeof window.console.clear === 'function') {
                window.console.clear();
            }
        } catch (e) {}
    }, 3500);

})(window, document);
