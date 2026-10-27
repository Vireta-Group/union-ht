<?php
/**
 * Registration confirmation screen.
 *
 * The `if (registeredData) return (...)` branch at the top of
 * resources/js/pages/RegisterPage.jsx. It was unreachable without JavaScript,
 * because registeredData was set by a fetch and read by a re-render. Front::register()
 * now picks this view over register.php when create_tenant() succeeds, so the
 * same screen renders on a plain form submission.
 *
 * Everything here comes from $saas_done, which is create_tenant()'s return
 * value plus the three values the visitor typed (the endpoint cannot know the
 * password, and the React version read it back out of formData).
 *
 * The one-click login button was a fetch to api_login from a click handler. It
 * is now a real form POST with the credentials as hidden fields, which works
 * because api_login() content-negotiates: a non-AJAX POST gets a 302 to the
 * dashboard. A password in a hidden field is no worse than the one on line ~110
 * of this file, which React already printed to the page, and it is a POST rather
 * than a URL.
 *
 * @var string $saas_plan  Selected package key.
 * @var array  $saas_done  create_tenant()'s result plus union_name,
 *                         admin_email and admin_password.
 */
defined('BASEPATH') OR exit('No direct script access allowed');

$saas_done = $saas_done ?? array();

$saas_pkg_names = array(
    'standard' => 'স্ট্যান্ডার্ড',
    'premium'  => 'প্রিমিয়াম',
    'cluster'  => 'ক্লাস্টার',
);
$saas_pkg_name = $saas_pkg_names[$saas_plan] ?? $saas_pkg_names['standard'];

$saas_subdomain    = $saas_done['subdomain'] ?? '';
$saas_custom_domain = $saas_done['custom_domain'] ?? '';
$saas_tenant_id    = $saas_done['tenant_id'] ?? '';
?>
        <main class="grow">
        <div class="py-16 min-h-[80vh]">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="ht-card neu-glass-card border-2 border-emerald-500/80 rounded-3xl p-8 sm:p-12 text-center">
                    <div class="w-20 h-20 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-6">
                        <?= icon('check-circle', 48, 'fill') ?>
                    </div>

                    <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 mb-3">
                        সফলভাবে অ্যাক্টিভেট হয়েছে
                    </span>
                    <h1 class="text-2xl sm:text-4xl font-black text-neutral-900 tracking-tight">
                        অভিনন্দন! আপনার ইউনিয়ন পোর্টাল প্রস্তুত
                    </h1>
                    <p class="mt-3 text-neutral-600 text-sm sm:text-base max-w-xl mx-auto">
                        <strong><?= html_escape($saas_done['union_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong> এর জন্য স্বয়ংক্রিয় ক্লাউড ইনস্ট্যান্স ও ডাটাবেজ তৈরি সম্পন্ন হয়েছে।
                    </p>

                    <!-- Generated Credentials & Links Box -->
                    <div class="mt-8 neu-glass-inset rounded-2xl p-6 text-left space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-neutral-200 gap-2">
                            <span class="text-xs font-medium text-neutral-500">নির্বাচিত প্যাকেজ</span>
                            <span class="text-sm font-bold text-indigo-700 bg-indigo-50 px-2.5 py-0.5 rounded-md border border-indigo-200">
                                <?= $saas_pkg_name ?> প্যাকেজ
                            </span>
                        </div>

                        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-neutral-200 gap-2">
                            <span class="text-xs font-medium text-neutral-500">ইউনিয়ন পাবলিক পোর্টাল URL</span>
                            <div class="flex items-center gap-2">
                                <?php /* The React version built this href from
                                         registeredData.tenant_url but printed
                                         window.location.origin + '/?tenant=' + id
                                         as the text. create_tenant() sets
                                         tenant_url to base_url('?tenant=N'), which
                                         is the same URL, so the two are now one
                                         value. The dev ?tenant= override only
                                         works on localhost/IP hosts — see
                                         Tenant_resolver step 1 — so on a
                                         production platform host this link needs
                                         the subdomain below instead. */ ?>
                                <a href="<?= html_escape($saas_done['tenant_url'] ?? ('?tenant=' . $saas_tenant_id), ENT_QUOTES, 'UTF-8') ?>"
                                   target="_blank" rel="noreferrer"
                                   class="text-sm font-bold text-indigo-700 hover:underline flex items-center gap-1">
                                    <span><?= html_escape($saas_done['tenant_url'] ?? ('?tenant=' . $saas_tenant_id), ENT_QUOTES, 'UTF-8') ?></span>
                                    <?= icon('arrow-square-out', 16, 'bold') ?>
                                </a>
                            </div>
                        </div>

                        <?php if (!empty($saas_custom_domain)): ?>
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-neutral-200 gap-2">
                                <span class="text-xs font-medium text-neutral-500">কাস্টম সরকারি ডোমেন (.gov.bd)</span>
                                <div class="flex items-center gap-1.5">
                                    <span class="text-sm font-bold text-indigo-700 font-mono">
                                        https://<?= html_escape($saas_custom_domain, ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">
                                        DNS CNAME পেন্ডিং
                                    </span>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-neutral-200 gap-2">
                            <span class="text-xs font-medium text-neutral-500">নির্ধারিত ক্লাউড সাবডোমেন</span>
                            <span class="text-sm font-mono font-bold text-neutral-800">
                                <?= html_escape($saas_subdomain, ENT_QUOTES, 'UTF-8') ?>.viretaup.com
                            </span>
                        </div>

                        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-neutral-200 gap-2">
                            <span class="text-xs font-medium text-neutral-500">লগইন আইডি / ইমেইল</span>
                            <span class="text-sm font-bold text-neutral-900 font-mono">
                                <?= html_escape($saas_done['admin_email'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </div>

                        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-neutral-200 gap-2 bg-indigo-50/60 p-2.5 rounded-xl">
                            <span class="text-xs font-bold text-indigo-950 flex items-center gap-1">
                                <?= icon('lock-key', 14, 'bold', 'text-indigo-600') ?>
                                <span>অফিশিয়াল লগইন পাসওয়ার্ড</span>
                            </span>
                            <span class="text-sm font-bold text-indigo-900 font-mono tracking-wider">
                                <?= html_escape($saas_done['admin_password'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </div>

                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <span class="text-xs font-medium text-neutral-500">অ্যাডমিন প্রিভিলেজ</span>
                            <span class="text-xs font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded">
                                পূর্ণ নিয়ন্ত্রণ ও কাস্টমাইজেশন পারমিশন (Admin &amp; UP Head)
                            </span>
                        </div>
                    </div>

                    <!-- DNS Guidance Box if Custom Domain registered -->
                    <?php if (!empty($saas_custom_domain)): ?>
                        <div class="mt-5 p-4 rounded-xl bg-amber-50 border border-amber-300 text-left text-xs text-amber-950 space-y-2">
                            <div class="font-bold flex items-center gap-1.5 text-amber-900">
                                <?= icon('globe', 16, 'fill') ?>
                                <span>কাস্টম ডোমেন সক্রিয় করতে আপনার DNS ম্যানেজারে নিচের রেকর্ডটি যোগ করুন:</span>
                            </div>
                            <div class="bg-white p-2.5 rounded-lg border border-amber-300 font-mono text-[11px] text-neutral-900 flex flex-wrap justify-between gap-2">
                                <span><strong>Host:</strong> @ অথবা www</span>
                                <span><strong>Type:</strong> CNAME</span>
                                <span><strong>Value:</strong> cname.viretaup.com</span>
                            </div>
                            <p class="text-[11px] text-amber-800">
                                ডিএনএস প্রপাগেশন হতে সাধারণত ৫ থেকে ৩০ মিনিট সময় লাগতে পারে। তবে আপনার ক্লাউড সাবডোমেন দিয়ে এখনই কাজ শুরু করতে পারবেন।
                            </p>
                        </div>
                    <?php endif; ?>

                    <!-- Customization Callout -->
                    <div class="mt-6 p-4 rounded-xl bg-indigo-50 border border-indigo-200 text-left flex items-start gap-3">
                        <?= icon('sliders-horizontal', 24, 'bold', 'text-indigo-700 shrink-0 mt-0.5') ?>
                        <div class="text-xs text-indigo-950 leading-relaxed">
                            <strong>প্রতিটি ইউনিয়নের জন্য শতভাগ কাস্টমাইজেশন:</strong><br />
                            লগইন করার পর ড্যাশবোর্ড থেকে আপনার ইউনিয়নের চেয়ারম্যানের ছবি, বাণী, মেম্বারদের তালিকা, নোটিশ বোর্ড, ইউনিয়নের নিজস্ব লোগো ও স্লাইডার ব্যানার এক ক্লিকে কাস্টমাইজ করতে পারবেন।
                        </div>
                    </div>

                    <!-- CTAs -->
                    <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">

                        <?php /* Was a fetch to api_login from a click handler. Now a
                                 real POST: api_login() redirects a non-AJAX caller
                                 to the dashboard. The React version fell back to
                                 onNavigate('/login') on failure, which is what the
                                 server does on its own. */ ?>
                        <form method="post" action="<?= site_url('api_login') ?>" class="w-full sm:w-auto">
                            <input type="hidden" name="identity" value="<?= html_escape($saas_done['admin_email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="password" value="<?= html_escape($saas_done['admin_password'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            <button type="submit"
                                    class="w-full sm:w-auto px-7 py-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:scale-98 text-white font-black text-sm shadow-sm flex items-center justify-center gap-2 cursor-pointer transition-all">
                                <?= icon('lock-key', 18, 'bold') ?>
                                <span>সরাসরি ড্যাশবোর্ডে প্রবেশ করুন (১-ক্লিক লগইন)</span>
                                <?= icon('arrow-right', 18, 'bold') ?>
                            </button>
                        </form>

                        <a href="<?= html_escape($saas_done['tenant_url'] ?? ('?tenant=' . $saas_tenant_id), ENT_QUOTES, 'UTF-8') ?>"
                           target="_blank" rel="noreferrer"
                           class="w-full sm:w-auto px-5 py-3.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-900 font-bold text-sm border border-indigo-200 flex items-center justify-center gap-2 cursor-pointer transition-colors">
                            <span>পাবলিক পোর্টাল দেখুন</span>
                            <?= icon('arrow-square-out', 16, 'bold') ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        </main>
