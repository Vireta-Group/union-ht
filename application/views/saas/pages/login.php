<?php
/**
 * Product-site login page.
 *
 * Ported from resources/js/pages/LoginPage.jsx (294 lines).
 *
 * The form is a real <form method="post" action="api_login"> with the field
 * names the endpoint already reads — identity, password, remember — rather
 * than a div that happens to look like a form. saas.js intercepts submit and
 * posts JSON, because Front::api_login() answers with JSON in every case and
 * expects the client to perform the redirect. That endpoint was left exactly
 * as it was: it is auth code, and changing it needs explicit sign-off.
 *
 * Consequence, stated plainly: login needs JavaScript. That is not a
 * regression — the React version could not log in without JavaScript either —
 * but a no-JS form that lands on a raw JSON body is worse than an honest
 * dependency. The proper fix is for api_login to content-negotiate and
 * redirect() on a non-AJAX POST, which is a change to the auth path and
 * therefore not something to do unasked.
 *
 * Two smaller changes:
 *
 *   - "১-ক্লিকে লগইন" filled the fields and submitted from JavaScript. It now
 *     prefills the fields and leaves the submit button to the user, so the
 *     credentials never travel in a URL (which would put them in browser
 *     history, the server access log and any Referer header).
 *   - The success/error banners are rendered here and revealed by saas.js,
 *     instead of being built in JavaScript.
 *
 * @var string $saas_path  Always '/login'.
 */
?>

        <main class="grow">
        <div class="min-h-[82vh] py-16 lg:py-24 flex items-center">
            <div class="mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">

                <div class="flex flex-col lg:flex-row items-center justify-between" style="gap: 4rem;">

                    <!-- Left Column: Platform Highlights & Trust -->
                    <div class="w-full space-y-6" style="flex: 1 1 55%; max-width: 580px;">
                        <div class="inline-flex items-center gap-2 rounded-full border border-indigo-100 bg-indigo-50/80 px-3.5 py-1.5 text-xs font-semibold text-indigo-700 shadow-xs">
                            <?= icon('shield-check', 16, 'fill', 'text-indigo-600') ?>
                            <span>নিরাপদ ক্লাউড মাল্টি-টেন্যান্ট প্ল্যাটফর্ম</span>
                        </div>

                        <div class="space-y-3">
                            <h1 class="text-3xl font-black tracking-tight text-neutral-900 sm:text-4xl lg:text-5xl leading-tight">
                                ইউনিয়ন পরিষদ প্রশাসনিক <span class="text-indigo-600">ডিজিটাল ড্যাশবোর্ড</span>
                            </h1>
                            <p class="text-sm text-neutral-600 sm:text-base leading-relaxed">
                                চেয়ারম্যান, সচিব, ইউডিসি উদ্যোক্তা ও কর আদায়কারীদের জন্য একক ক্লাউড প্রবেশদ্বার। দ্রুত নাগরিক সেবা ও স্বচ্ছ হিসাব ব্যবস্থাপনা এখন হাতের মুঠোয়।
                            </p>
                        </div>

                        <!-- Highlights Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                            <div class="ht-card neu-glass-card p-4 flex items-start gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-700">
                                    <?= icon('certificate', 22, 'bold') ?>
                                </div>
                                <div>
                                    <h2 class="text-sm font-bold text-neutral-900">স্মার্ট নাগরিক সেবা</h2>
                                    <p class="mt-0.5 text-xs text-neutral-500 leading-normal">ওয়ারিশান, ট্রেড লাইসেন্স ও চারিত্রিক সনদপত্র কিউআর কোডসহ যাচাই ও প্রিন্ট।</p>
                                </div>
                            </div>

                            <div class="ht-card neu-glass-card p-4 flex items-start gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700">
                                    <?= icon('receipt', 22, 'bold') ?>
                                </div>
                                <div>
                                    <h2 class="text-sm font-bold text-neutral-900">ডিজিটাল হোল্ডিং ট্যাক্স</h2>
                                    <p class="mt-0.5 text-xs text-neutral-500 leading-normal">খানাভিত্তিক মূল্যায়ন, অনলাইন কর আদায় এবং ক্যাশলেস মানি রিসিট।</p>
                                </div>
                            </div>

                            <div class="ht-card neu-glass-card p-4 flex items-start gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-700">
                                    <?= icon('database', 22, 'bold') ?>
                                </div>
                                <div>
                                    <h2 class="text-sm font-bold text-neutral-900">সুরক্ষিত ক্লাউড ডাটাবেস</h2>
                                    <p class="mt-0.5 text-xs text-neutral-500 leading-normal">প্রতিটি পরিষদের জন্য পৃথক ডাটাবেস আইসোলেশন ও ব্যাকআপ নিরাপত্তা।</p>
                                </div>
                            </div>

                            <div class="ht-card neu-glass-card p-4 flex items-start gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-purple-50 text-purple-700">
                                    <?= icon('headset', 22, 'bold') ?>
                                </div>
                                <div>
                                    <h2 class="text-sm font-bold text-neutral-900">২৪/৭ কারিগরি সহায়তা</h2>
                                    <p class="mt-0.5 text-xs text-neutral-500 leading-normal">পরিষদের দৈনন্দিন যেকোনো কারিগরি প্রয়োজনে সার্বক্ষণিক ডেডিকেটেড সাপোর্ট।</p>
                                </div>
                            </div>
                        </div>

                        <!-- Security Metatags -->
                        <div class="flex flex-wrap items-center gap-6 border-t border-neutral-200 pt-5 text-xs text-neutral-500">
                            <div class="flex items-center gap-2">
                                <span class="flex h-2 w-2 rounded-full bg-emerald-500"></span>
                                <span><strong>৯৯.৯%</strong> ক্লাউড আপটাইম নিশ্চয়তা</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <?= icon('shield-check', 16, 'bold', 'text-indigo-600') ?>
                                <span><strong>২৫৬-বিট</strong> ব্যাংক-গ্রেড SSL এনক্রিপশন</span>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Login Card -->
                    <div class="w-full shrink-0" style="flex: 0 0 420px; max-width: 440px;">
                        <div class="ht-card neu-glass-card p-8 sm:p-9">

                            <!-- Header Branding -->
                            <div class="mb-6">
                                <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-700 text-xl font-black text-white shadow-xs">
                                    V
                                </div>
                                <h2 class="text-xl font-black tracking-tight text-neutral-900 sm:text-2xl">
                                    অ্যাকাউন্টে প্রবেশ করুন
                                </h2>
                                <p class="mt-1 text-xs text-neutral-500">
                                    আপনার ইউনিয়ন বা কর্মকর্তা ক্রেডেনশিয়াল প্রদান করে সাইন ইন করুন
                                </p>
                            </div>

                            <!-- Success Banner. Hidden until saas.js fills it in. -->
                            <div data-saas-alert="success" role="status"
                                 class="mb-6 hidden items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-xs text-emerald-800 sm:text-sm">
                                <?= icon('check-circle', 20, 'fill', 'mt-0.5 shrink-0 text-emerald-600') ?>
                                <div>
                                    <strong data-saas-alert-text></strong>
                                </div>
                            </div>

                            <!-- Error Banner -->
                            <div data-saas-alert="error" role="alert"
                                 class="mb-6 hidden items-start gap-3 rounded-xl border border-red-200 bg-red-50 p-4 text-xs text-red-700 sm:text-sm">
                                <?= icon('warning-circle', 20, 'bold', 'mt-0.5 shrink-0 text-red-600') ?>
                                <div>
                                    <strong>লগইন ব্যর্থ:</strong> <span data-saas-alert-text></span>
                                </div>
                            </div>

                            <form method="post" action="<?= site_url('api_login') ?>" data-saas-form="login" class="space-y-4">

                                <!-- Identity Field -->
                                <div>
                                    <label for="login-identity" class="mb-1.5 block text-xs font-bold text-neutral-700">
                                        অফিশিয়াল ইমেইল / ইউজারনেম
                                    </label>
                                    <div class="relative flex items-center">
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-neutral-400">
                                            <?= icon('envelope-simple', 18, 'bold') ?>
                                        </div>
                                        <input id="login-identity" name="identity" type="text" required autocomplete="username"
                                               value="<?= html_escape($this->input->get('identity'), ENT_QUOTES, 'UTF-8') ?>"
                                               placeholder="admin@union.gov.bd বা ইউজারনেম"
                                               class="ht-field w-full text-sm text-neutral-900 transition-all focus:border-indigo-600 focus:outline-none"
                                               style="height: 46px; padding-left: 44px; padding-right: 14px;">
                                    </div>
                                    <p class="mt-1.5 text-[11px] text-neutral-400">
                                        টিপ: রেজিস্টার্ড ইমেইল, ইউজারনেম বা ইউনিয়ন সাবডোমেন দিয়ে লগইন করা যায়
                                    </p>
                                </div>

                                <!-- Password Field -->
                                <div>
                                    <div class="mb-1.5 flex items-center justify-between">
                                        <label for="login-password" class="block text-xs font-bold text-neutral-700">
                                            পাসওয়ার্ড
                                        </label>
                                        <a href="<?= site_url('forgot_password') ?>"
                                           class="cursor-pointer text-[11px] font-semibold text-indigo-700 hover:text-indigo-800 hover:underline">
                                            পাসওয়ার্ড ভুলে গেছেন?
                                        </a>
                                    </div>
                                    <div class="relative flex items-center">
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-neutral-400">
                                            <?= icon('lock-key', 18, 'bold') ?>
                                        </div>
                                        <input id="login-password" name="password" type="password" required autocomplete="current-password"
                                               placeholder="আপনার পাসওয়ার্ড লিখুন"
                                               class="ht-field w-full text-sm text-neutral-900 transition-all focus:border-indigo-600 focus:outline-none"
                                               style="height: 46px; padding-left: 44px; padding-right: 44px;"
                                               data-saas-password>
                                        <button type="button" data-saas-toggle-password
                                                class="absolute inset-y-0 right-0 flex items-center pr-3.5 cursor-pointer text-neutral-400 hover:text-neutral-700 transition-colors"
                                                style="background: transparent; border: none; outline: none;"
                                                aria-label="পাসওয়ার্ড দেখান">
                                            <span data-saas-eye="show"><?= icon('eye', 18, 'bold') ?></span>
                                            <span data-saas-eye="hide" class="hidden"><?= icon('eye-slash', 18, 'bold') ?></span>
                                        </button>
                                    </div>
                                </div>

                                <!-- Remember Me -->
                                <div class="flex items-center justify-between pt-1">
                                    <label class="flex cursor-pointer items-center gap-2 select-none">
                                        <input type="checkbox" name="remember" value="1">
                                        <span class="text-xs font-medium text-neutral-600">মনে রাখুন (Remember me)</span>
                                    </label>
                                </div>

                                <!-- Submit Button -->
                                <div class="pt-2">
                                <button type="submit"
                                        class="w-full cursor-pointer rounded-xl bg-indigo-700 py-3.5 text-sm font-bold text-white shadow-sm transition-all hover:bg-indigo-800 active:scale-98 disabled:opacity-60"
                                        data-saas-submit
                                        data-saas-busy-text="যাচাই করা হচ্ছে...">
                                    <span data-saas-submit-idle class="flex items-center justify-center gap-2">
                                        <?= icon('key', 18, 'bold') ?>
                                        <span>লগইন করুন</span>
                                        <?= icon('arrow-right', 18, 'bold') ?>
                                    </span>
                                    <span data-saas-submit-busy class="hidden items-center justify-center gap-2">
                                        <?= icon('spinner-gap', 18, 'bold', 'animate-spin') ?>
                                        <span>যাচাই করা হচ্ছে...</span>
                                    </span>
                                </button>
                                </div>

                            </form>

                            <!-- Register Prompt -->
                            <div class="mt-6 border-t border-neutral-100 pt-5 text-center">
                                <p class="text-xs text-neutral-600">
                                    আপনার ইউনিয়ন পরিষদ কি এখনও নিবন্ধিত নয়?
                                    <a href="<?= site_url('register') ?>" class="font-bold text-indigo-700 hover:underline">প্যাকেজ নির্বাচন ও নিবন্ধন করুন</a>
                                </p>
                            </div>

                        </div>
                    </div>

                </div>

            </div>
        </div>
        </main>
