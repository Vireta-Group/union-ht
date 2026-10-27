/**
 * Modern Alerts & Modal Dialogs for ViretaDev
 * Replaces native browser alert/confirm with sleek, customized dialogs.
 */
(function() {
    'use strict';

    function detectTone(msg) {
        if (!msg) return { icon: 'info', title: 'বিজ্ঞপ্তি' };
        var text = String(msg);
        
        if (/দয়া করে|দয়া করে|অনুগ্রহ করে|লিখুন|বাছাই|নির্বাচন|প্রয়োজন|প্রয়োজন|সতর্ক|খালি রাখা|পূরণ/i.test(text)) {
            return { icon: 'warning', title: 'সতর্কতা' };
        }
        if (/সফল|সাফল্য|ধন্যবাদ|সফলভাবে|অনুমোদিত|সংরক্ষিত|সম্পন্ন/i.test(text)) {
            return { icon: 'success', title: 'সফল হয়েছে' };
        }
        if (/ভুল|ত্রুটি|ব্যর্থ|সমস্যা|পাওয়া যায়নি|পাওয়া যায়নি|পাওয়া গেল না|মুছে ফেলা|বাতিল/i.test(text)) {
            return { icon: 'error', title: 'ত্রুটি' };
        }
        return { icon: 'info', title: 'বিজ্ঞপ্তি' };
    }

    window.vAlert = function(msg, customTitle, customIcon, options) {
        options = options || {};
        var tone = detectTone(msg);
        var icon = customIcon || tone.icon;
        var title = customTitle || tone.title;

        if (typeof Swal !== 'undefined') {
            return Swal.fire(Object.assign({
                title: title,
                text: msg,
                icon: icon,
                confirmButtonText: 'ঠিক আছে',
                confirmButtonColor: '#4f46e5',
                buttonsStyling: true,
                customClass: {
                    popup: 'vireta-swal-popup',
                    confirmButton: 'vireta-swal-confirm'
                }
            }, options));
        } else {
            console.warn('[ViretaAlert]', msg);
            if (window._nativeAlert) {
                window._nativeAlert(msg);
            }
        }
    };

    window.vConfirm = function(title, message, options) {
        options = options || {};
        var isEn = (document.documentElement && document.documentElement.getAttribute('data-sa-lang') === 'en');
        var confirmText = options.confirmButtonText || (isEn ? 'Yes, Confirm' : 'হ্যাঁ, নিশ্চিত');
        var cancelText = options.cancelButtonText || (isEn ? 'Cancel' : 'বাতিল');
        var isDanger = options.danger || false;

        if (typeof Swal !== 'undefined') {
            return Swal.fire({
                title: title,
                text: message || '',
                icon: options.icon || (isDanger ? 'warning' : 'question'),
                showCancelButton: true,
                confirmButtonText: confirmText,
                cancelButtonText: cancelText,
                confirmButtonColor: isDanger ? '#dc2626' : '#4f46e5',
                cancelButtonColor: '#64748b',
                buttonsStyling: true,
                reverseButtons: true
            }).then(function(result) {
                return !!result.isConfirmed;
            });
        } else {
            if (window._nativeConfirm) {
                return Promise.resolve(window._nativeConfirm(message || title));
            }
            return Promise.resolve(false);
        }
    };

    window.vToast = function(msg, icon, duration) {
        icon = icon || 'success';
        duration = duration || 3000;

        if (typeof Swal !== 'undefined') {
            var Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: duration,
                timerProgressBar: true,
                didOpen: function(toast) {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });

            return Toast.fire({
                icon: icon,
                title: msg
            });
        }
    };

    // Override browser native alert globally so NO old browser popup ever appears
    if (typeof window.alert !== 'undefined') {
        window._nativeAlert = window.alert;
        window.alert = function(msg) {
            window.vAlert(msg);
        };
    }

    if (typeof window.confirm !== 'undefined') {
        window._nativeConfirm = window.confirm;
    }

})();
