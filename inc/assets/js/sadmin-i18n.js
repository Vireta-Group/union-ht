/**
 * ViretaDev Super Admin Bilingual Translation Engine (বাংলা ⇋ English)
 * 
 * Provides instantaneous, full-page translation for the SaaS Central Control Panel.
 * Translates text nodes, headings, buttons, badges, tables, and numerals (০-৯ <-> 0-9).
 */
(function(window, document) {
    'use strict';

    var BN_DIGITS = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
    var EN_DIGITS = ['0','1','2','3','4','5','6','7','8','9'];

    var BN_TO_EN_MAP = {};
    var EN_TO_BN_MAP = {};
    for (var i = 0; i < 10; i++) {
        BN_TO_EN_MAP[BN_DIGITS[i]] = EN_DIGITS[i];
        EN_TO_BN_MAP[EN_DIGITS[i]] = BN_DIGITS[i];
    }

    function toEnDigits(text) {
        if (!text) return text;
        return text.replace(/[০-৯]/g, function(d) {
            return BN_TO_EN_MAP[d] || d;
        });
    }

    function toBnDigits(text) {
        if (!text) return text;
        return text.replace(/[0-9]/g, function(d) {
            return EN_TO_BN_MAP[d] || d;
        });
    }

    // Comprehensive Dictionary of Phrases: Bengali -> English
    var DICTIONARY = [
        // Sentences and Descriptions
        ["সেন্ট্রাল মাল্টি-টেন্যান্ট গভর্ন্যান্স", "Central Multi-Tenant Governance"],
        ["সেন্ট্রাল প্ল্যাটফর্মের সার্বিক অবস্থা এক নজরে।", "Central platform overview at a glance."],
        ["সকল ইউনিয়ন পরিষদ তালিকা ও ম্যানেজমেন্ট", "Union Parishads Directory & Management"],
        ["যেকোনো ইউনিয়নের ড্যাশবোর্ডে সরাসরি ১-ক্লিকে প্রবেশ করুন বা ডোমেন কনফিগার করুন", "1-click jump into any parishad dashboard or configure custom domains"],
        ["ইউনিয়ন পরিষদগুলোর সাবস্ক্রিপশন প্ল্যান, পেমেন্ট ও নবায়ন পরিচালনা করুন।", "Manage subscription plans, billing, and renewals for union parishads."],
        ["সকল ইউনিয়ন পরিষদের অ্যাডমিন ও সেন্ট্রাল অ্যাডমিনিস্ট্রেটরদের তালিকা", "Directory of union parishad admins and central administrators"],
        ["ইউনিয়ন পরিষদ থেকে প্রেরিত সকল সাপোর্ট রিকোয়েস্ট ও সমস্যার সমাধান", "Resolve support requests and issues sent by union parishads"],
        ["সেন্ট্রাল সিস্টেম কনফিগারেশন, ব্র্যান্ডিং ও সাপোর্ট হেল্পলাইন", "Central system configuration, branding, and support helpline"],
        ["সেন্ট্রাল প্ল্যাটফর্মের সকল পরিবর্তন ও সিকিউরিটি ইভেন্টের রেকর্ড", "Audit trail of all administrative actions and security events"],
        ["আগামী ৩০ দিনে কোনো সাবস্ক্রিপশনের মেয়াদ শেষ হচ্ছে না।", "No subscriptions expiring in the next 30 days."],
        ["কোনো জরুরি টিকিট নেই।", "No urgent tickets found."],
        ["এখনও কোনো কার্যক্রম রেকর্ড হয়নি।", "No activities recorded yet."],
        ["এখনো কোনো টিকিট আসেনি। ইউনিয়ন পরিষদ সাপোর্ট ফর্ম থেকে পাঠালে এখানে দেখা যাবে।", "No support tickets received yet. Tickets submitted from union parishads will appear here."],
        ["এই ফিল্টারে কোনো ইউনিয়ন নেই।", "No tenants match this filter."],
        ["পুরো তালিকা দেখতে চওড়ায় স্ক্রল করুন", "Scroll horizontally to view entire table"],
        ["অডিট লগ ২০২৬-০৯-৩০ তারিখে চালু হয়েছে। এর আগের কোনো কার্যক্রমের রেকর্ড নেই এবং পূরণ করা হয়নি।", "Audit logging enabled on 2026-09-30. Prior activities were not recorded."],
        ["নতুন ডোমেন দিলে স্বয়ংক্রিয়ভাবে ডিএনএস রাউটিং তালিকায় যুক্ত হবে", "Adding a domain automatically provisions DNS routing."],
        ["পেমেন্ট রেকর্ড করলে মেয়াদ নিজে থেকে বাড়ে না", "Recording a payment does not automatically extend subscription validity."],
        ["সক্রিয় সাবস্ক্রিপশনের বার্ষিক মূল্যের যোগ", "Sum of annual values of active subscriptions."],
        ["এটি সরাসরি তারিখ ও অবস্থা সম্পাদনা। নিচের প্রিসেট দিয়ে দিন যোগ করতে পারেন।", "Direct date and status editing. Use duration presets below to add days."],
        ["এটি ইউনিয়ন পরিষদের পরিষেবা পুনরায় সক্রিয় করবে।", "This will reactivate the union parishad portal services."],
        ["আপনি কি নিশ্চিত যে সাবস্ক্রিপশন বাতিল করবেন?", "Are you sure you want to cancel this subscription?"],
        ["সাবস্ক্রিপশন বাতিল ও পেমেন্ট রেকর্ড আর্কাইভ", "Cancel subscription and archive payment records"],

        // Metric Card Labels & Subtexts
        ["মোট ইউনিয়ন", "Total Tenants"],
        ["সবগুলো সক্রিয়", "All active"],
        ["টি স্থগিত", " suspended"],
        ["ডোমেন", "Domains"],
        ["ডেভ হোস্টসহ", "Incl. dev hosts"],
        ["লাইভ হোস্টসহ", "Live hosts only"],
        ["হোল্ডিং", "Holdings"],
        ["সব ইউনিয়ন মিলিয়ে", "Across all tenants"],
        ["ইউজার", "Users"],
        ["ডিজিটাল সনদ", "Digital Certificates"],
        ["মেয়াদ শেষ হওয়ার কাছাকাছি", "Expiring Soon"],
        ["মেয়াদ শেষের কাছাকাছি", "Expiring Soon"],
        ["৩০ দিনের মধ্যে", "Within 30 days"],
        ["কোনোটিই নেই", "None"],
        ["খোলা সাপোর্ট টিকিট", "Open Support Tickets"],
        ["জরুরি সাপোর্ট টিকিট", "Urgent Support Tickets"],
        ["টি জরুরি", " urgent"],
        ["জরুরি কোনোটি নেই", "No urgent tickets"],
        ["মেয়াদোত্তীর্ণ সাবস্ক্রিপশন", "Expired Subscriptions"],
        ["টি মোট", " total"],
        ["কোনো সাবস্ক্রিপশন নেই", "No subscriptions"],
        ["টি দেখানো হচ্ছে", " showing"],

        // Navigation & Menu
        ["প্ল্যাটফর্ম", "Platform"],
        ["ওভারভিউ", "Overview"],
        ["ইউনিয়ন পরিষদ", "Tenants"],
        ["সাবস্ক্রিপশন", "Subscriptions"],
        ["ইউজার ম্যানেজার", "Users"],
        ["সাপোর্ট টিকিট", "Support"],
        ["সেটিংস", "Settings"],
        ["অডিট লগ", "Audit Logs"],
        ["লগআউট", "Logout"],
        ["মেনু", "Menu"],

        // Buttons & Actions
        ["নতুন ইউনিয়ন অনবোর্ড করুন", "Onboard New Tenant"],
        ["নতুন ইউনিয়ন অনবোর্ড", "Onboard New Tenant"],
        ["নতুন ইউপি", "Onboard"],
        ["নতুন ইউজার যোগ করুন", "Add New User"],
        ["সব দেখুন", "View All"],
        ["পূর্ণ অডিট লগ", "Full Audit Log"],
        ["১-ক্লিকে প্রবেশ", "1-Click Access"],
        ["১-ক্লিক প্রবেশ", "1-Click Access"],
        ["ড্যাশবোর্ডে যান", "Open Dashboard"],
        ["প্রবেশ করুন", "Access"],
        ["সম্পাদনা", "Edit"],
        ["সম্পাদনা করুন", "Edit"],
        ["স্থগিত করুন", "Suspend"],
        ["সক্রিয় করুন", "Activate"],
        ["ডিলিট", "Delete"],
        ["মুছে ফেলুন", "Delete"],
        ["সংরক্ষণ করুন", "Save Changes"],
        ["সেটিংস সংরক্ষণ", "Save Settings"],
        ["বাতিল করুন", "Cancel"],
        ["বাতিল", "Cancel"],
        ["বন্ধ করুন", "Close"],
        ["যুক্ত করুন", "Add"],
        ["যোগ করুন", "Add"],
        ["আপডেট করুন", "Update"],
        ["নিশ্চিত করুন", "Confirm"],
        ["পেমেন্ট যোগ করুন", "Add Payment"],
        ["পেমেন্ট যোগ", "Add Payment"],
        ["মেয়াদ পরিবর্তন", "Edit Validity"],
        ["মেয়াদ সম্পাদনা", "Edit Duration"],
        ["ডোমেন যোগ করুন", "Add Domain"],
        ["ডোমেন যোগ", "Add Domain"],
        ["ডোমেন যাচাই", "Verify Domain"],
        ["পাসওয়ার্ড পরিবর্তন", "Change Password"],
        ["পাসওয়ার্ড রিসেট", "Reset Password"],
        ["লগইন হিসেবে প্রবেশ", "Impersonate"],
        ["উত্তর দিন", "Reply"],
        ["টিকিট বন্ধ করুন", "Close Ticket"],
        ["অনবোর্ড সম্পন্ন করুন", "Complete Onboarding"],

        // Filter Bar & Statuses
        ["সবাই", "All"],
        ["সক্রিয়", "Active"],
        ["৩০ দিনে মেয়াদ শেষ", "Expiring in 30d"],
        ["মেয়াদ শেষ / বাতিল", "Expired / Cancelled"],
        ["প্যাকেজ নেই", "Unassigned"],
        ["খোলা", "Open"],
        ["অপেক্ষমান", "Pending"],
        ["মেয়াদ শেষ", "Expired"],
        ["মেয়াদোত্তীর্ণ", "Expired"],
        ["সম্পন্ন", "Resolved"],
        ["জরুরি", "Urgent"],
        ["উচ্চ", "High"],
        ["মাঝারি", "Medium"],
        ["সাধারণ", "Normal"],
        ["কম", "Low"],

        // Table Headers and Fields
        ["টেন্যান্ট ID", "Tenant ID"],
        ["ইউনিয়ন পরিষদের নাম", "Union Parishad Name"],
        ["হোস্ট ও সাবডোমেন", "Subdomain Host"],
        ["সরকারি ডোমেন (.gov.bd)", "Gov Domain (.gov.bd)"],
        ["সরকারি ডোমেন", "Gov Domain"],
        ["অ্যাডমিন ও যোগাযোগ", "Admin & Contact"],
        ["প্যাকেজ ও মেয়াদ", "Plan & Validity"],
        ["প্যাকেজ", "Package"],
        ["মেয়াদ", "Validity"],
        ["অবস্থা", "Status"],
        ["অ্যাকশন", "Actions"],
        ["পেমেন্ট", "Payments"],
        ["ইউনিয়ন ও তাদের সাবস্ক্রিপশন", "Tenants & Subscriptions"],
        ["ইউনিয়নের নাম (বাংলা)", "Union Name (Bangla)"],
        ["ইউনিয়নের নাম (ইংরেজি)", "Union Name (English)"],
        ["সাবডোমেন স্লাগ", "Subdomain Slug"],
        ["অ্যাডমিন নাম", "Admin Name"],
        ["অ্যাডমিন ইমেইল", "Admin Email"],
        ["অ্যাডমিন মোবাইল", "Admin Mobile"],
        ["পাসওয়ার্ড", "Password"],
        ["সাবস্ক্রিপশন প্ল্যান", "Subscription Plan"],
        ["মেয়াদকাল ও দিন", "Duration & Validity"],
        ["কাস্টম দিন (Custom Days)", "Custom Days"],
        ["১ মাস (৩০ দিন)", "1 Month (30 Days)"],
        ["৩ মাস (৯০ দিন)", "3 Months (90 Days)"],
        ["৬ মাস (১৮০ দিন)", "6 Months (180 Days)"],
        ["১ বছর (৩৬৫ দিন)", "1 Year (365 Days)"],
        ["শুরু তারিখ", "Start Date"],
        ["মেয়াদ শেষ তারিখ", "Expiry Date"],
        ["পেমেন্ট হিস্ট্রি", "Payment History"],
        ["টাকা পরিশোধ", "Amount Paid"],
        ["ট্রানজেকশন আইডি", "Transaction ID"],
        ["পেমেন্ট মাধ্যম", "Payment Method"],
        ["নোট / বিবরণ", "Notes / Remarks"],
        ["ইউজারনেম", "Username"],
        ["ইমেইল ও মোবাইল", "Email & Mobile"],
        ["ভূমিকা / রোল", "Role"],
        ["তৈরির তারিখ", "Created Date"],
        ["তৈরির সময়", "Created At"],
        ["সর্বশেষ আপডেট", "Last Updated"],
        ["টিকিট আইডি", "Ticket ID"],
        ["বিষয় ও বার্তা", "Subject & Message"],
        ["টিকেট নম্বর", "Ticket No"],
        ["অগ্রাধিকার", "Priority"],
        ["বিষয়", "Subject"],
        ["বার্তা", "Message"],
        ["সাধারণ সেটিংস", "General Settings"],
        ["সাপোর্ট ও হেল্পলাইন", "Support & Helpline"],
        ["হেল্পলাইন নম্বর", "Helpline Phone"],
        ["হোয়াটসঅ্যাপ নম্বর", "WhatsApp Number"],
        ["সাপোর্ট ইমেইল", "Support Email"],
        ["অফিস ঠিকানা", "Office Address"],
        ["ইভেন্ট ফিল্টার", "Filter Events"],
        ["তারিখ থেকে", "Date From"],
        ["তারিখ পর্যন্ত", "Date To"],
        ["অ্যাকশন টাইপ", "Action Type"],
        ["সময়", "Time"],
        ["অ্যাক্টর", "Actor"],
        ["বিবরণ", "Summary"],
        ["আইপি অ্যাড্রেস", "IP Address"],
        ["আইপি", "IP Address"],
        ["তারিখ", "Date"],
        ["টাকা", "BDT"],
        ["মাসিক", "Monthly"],
        ["বাৎসরিক", "Yearly"],
        ["লাইফটাইম", "Lifetime"],
        ["ট্রায়াল", "Trial"],
        ["প্যাকেজহীন", "No Plan"],
        ["কোনো সরকারি ডোমেন নেই", "No gov domain assigned"],
        ["সাম্প্রতিক কার্যক্রম", "Recent Activities"],

        // Relative time & dynamic chunks
        ["দিন বাকি", "days left"],
        ["আজ মেয়াদ শেষ", "Expires today"],
        ["শেষ ", "Ends "]
    ];

    // Placeholder mappings
    var PLACEHOLDERS = [
        ["ইউনিয়ন বা ডোমেন সার্চ করুন...", "Search union or domain..."],
        ["ইউজার সার্চ করুন...", "Search users..."],
        ["খুঁজুন...", "Search..."],
        ["যেমন: কাঞ্চনপুর ইউনিয়ন পরিষদ", "e.g. Kanchanpur Union Parishad"],
        ["যেমন: kanchanpur", "e.g. kanchanpur"],
        ["ইউজারনেম বা নাম", "Username or name"],
        ["পাসওয়ার্ড লিখুন", "Enter password"]
    ];

    function translateText(text) {
        if (!text || !text.trim()) return text;
        var s = text;
        for (var i = 0; i < DICTIONARY.length; i++) {
            var pair = DICTIONARY[i];
            if (s.indexOf(pair[0]) !== -1) {
                s = s.split(pair[0]).join(pair[1]);
            }
        }
        // Also convert any Bengali digits left in the text to English digits
        s = toEnDigits(s);
        return s;
    }

    function translateNode(node, lang) {
        if (node.nodeType === 3) { // Text node
            var val = node.nodeValue;
            if (!val || !val.trim()) return;

            if (lang === 'en') {
                if (typeof node._orig_bn === 'undefined') {
                    node._orig_bn = val;
                }
                var translated = translateText(node._orig_bn);
                if (translated !== val) {
                    node.nodeValue = translated;
                }
            } else {
                if (typeof node._orig_bn !== 'undefined') {
                    node.nodeValue = node._orig_bn;
                }
            }
        } else if (node.nodeType === 1) { // Element node
            var tag = node.tagName.toLowerCase();
            if (tag === 'script' || tag === 'style' || tag === 'svg' || tag === 'code') return;
            if (node.classList && (node.classList.contains('swal2-container') || node.classList.contains('swal2-popup') || node.classList.contains('ui-datepicker'))) return;
            if (node.id === 'ui-datepicker-div') return;

            // Check placeholders
            if (tag === 'input' || tag === 'textarea') {
                if (node.placeholder) {
                    if (lang === 'en') {
                        if (typeof node._orig_ph === 'undefined') {
                            node._orig_ph = node.placeholder;
                        }
                        for (var p = 0; p < PLACEHOLDERS.length; p++) {
                            if (node._orig_ph.indexOf(PLACEHOLDERS[p][0]) !== -1) {
                                node.placeholder = node._orig_ph.replace(PLACEHOLDERS[p][0], PLACEHOLDERS[p][1]);
                                break;
                            }
                        }
                    } else {
                        if (typeof node._orig_ph !== 'undefined') {
                            node.placeholder = node._orig_ph;
                        }
                    }
                }
            }

            // Check titles / tooltips
            if (node.title) {
                if (lang === 'en') {
                    if (typeof node._orig_title === 'undefined') {
                        node._orig_title = node.title;
                    }
                    node.title = translateText(node._orig_title);
                } else {
                    if (typeof node._orig_title !== 'undefined') {
                        node.title = node._orig_title;
                    }
                }
            }

            // Recurse children
            var child = node.firstChild;
            while (child) {
                var next = child.nextSibling;
                translateNode(child, lang);
                child = next;
            }
        }
    }

    var isTranslating = false;

    function applySaLanguage(lang) {
        if (lang !== 'bn' && lang !== 'en') lang = 'bn';
        
        try {
            localStorage.setItem('vireta_sa_lang', lang);
            document.cookie = 'vireta_sa_lang=' + lang + ';path=/;max-age=31536000';
        } catch(e) {}

        document.documentElement.setAttribute('data-sa-lang', lang);
        document.documentElement.setAttribute('lang', lang);

        // Update button states
        var btnBn = document.getElementById('saLangBtnBn');
        var btnEn = document.getElementById('saLangBtnEn');
        if (btnBn && btnEn) {
            if (lang === 'en') {
                btnEn.className = 'px-2.5 py-1 rounded-lg text-xs font-bold transition-all text-white bg-indigo-600 shadow-xs cursor-pointer flex items-center gap-1';
                btnBn.className = 'px-2.5 py-1 rounded-lg text-xs font-semibold transition-all text-neutral-400 hover:text-white cursor-pointer flex items-center gap-1';
            } else {
                btnBn.className = 'px-2.5 py-1 rounded-lg text-xs font-bold transition-all text-white bg-indigo-600 shadow-xs cursor-pointer flex items-center gap-1';
                btnEn.className = 'px-2.5 py-1 rounded-lg text-xs font-semibold transition-all text-neutral-400 hover:text-white cursor-pointer flex items-center gap-1';
            }
        }

        // Translate the whole DOM
        isTranslating = true;
        if (document.body) {
            translateNode(document.body, lang);
        }
        isTranslating = false;

        try {
            window.dispatchEvent(new CustomEvent('sa-language-changed', { detail: { lang: lang } }));
        } catch(e) {}
    }

    // Expose globally
    window.setSaLanguage = applySaLanguage;
    window.applySaLanguage = applySaLanguage;

    // Run on DOM load
    document.addEventListener('DOMContentLoaded', function() {
        var current = localStorage.getItem('vireta_sa_lang') || 'bn';
        applySaLanguage(current);

        // Observer for dynamically added nodes (modals, AJAX inserts, tables)
        if (typeof MutationObserver !== 'undefined') {
            var observer = new MutationObserver(function(mutations) {
                if (isTranslating) return;
                var currentLang = document.documentElement.getAttribute('data-sa-lang') || 'bn';
                if (currentLang !== 'en') return;

                isTranslating = true;
                for (var m = 0; m < mutations.length; m++) {
                    var added = mutations[m].addedNodes;
                    for (var a = 0; a < added.length; a++) {
                        var node = added[a];
                        // Skip scripts, styles, datepicker container, and swal2 popups
                        if (node.nodeType === 1 && (node.id === 'ui-datepicker-div' || node.classList.contains('ui-datepicker') || node.classList.contains('swal2-container') || node.classList.contains('swal2-popup'))) continue;
                        translateNode(node, 'en');
                    }
                }
                isTranslating = false;
            });
            observer.observe(document.body, { childList: true, subtree: true });
        }
    });

})(window, document);
