<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Parishad-branded login — served only on a tenant host.
 *
 * Front::login() branches on tenant_source() to get here; the product site
 * keeps its own marketing login on a platform host. The same split
 * render_site() makes for the home page.
 *
 * This file used to be orphaned. /login had been built for the product site's
 * nav ("sign in to the product"), became the only login entry point in the
 * project, and nothing loaded this view any more — so a chairmen clicking
 * প্যানেল লগইন on his own parishad's site got ViretaDev's marketing page with
 * its SaaS nav, a demo banner and a placeholder helpline. The rewrite below
 * replaces what was here, which was a bare fragment (no doctype, no head, a
 * <body> halfway down) styled with hardcoded #1a4ec5 / #bc6202 / #db3e00 and a
 * fixed height:500px box — all outside the design tokens, and all English
 * labels.
 *
 * Deliberately does NOT load partials/navbar.php: that navbar's own primary
 * button is প্যানেল লগইন, which would link this page to itself. There is a
 * হোমে ফিরে যান link instead, and the footer carries the parishad's address and
 * helpline, which is what someone locked out actually needs.
 *
 * The form posts to api_login — the same endpoint the product-site login uses,
 * so the credential check, the sadmin branch and tenant_login_url() are shared
 * between the two. The password toggle is inc/assets/js/password-toggle.js,
 * driven by data-saas-password / data-saas-toggle-password.
 */

$page_title = 'প্যানেল লগইন';
$page_desc  = ($setting->ins_title_ss ?? 'ইউনিয়ন পরিষদ') . ' — অ্যাডমিন প্যানেলে প্রবেশ করুন';
$view_data  = ($this->data ?? get_defined_vars());
$view_data['page_title'] = $page_title;
$view_data['page_desc']  = $page_desc;

$this->load->view('front/partials/header', $view_data);

// Either path lands here on a failed login: api_login bounces to
// /login?identity=… with the reason in flashdata, and never puts the password
// in the query string.
$ht_error = $this->session->flashdata('error');
$ht_msg   = $this->session->flashdata('message');
$ht_ident = $this->input->get('identity');
?>

<main class="grow">
    <div class="max-w-md mx-auto px-4 sm:px-6 py-12 lg:py-20">

        <div class="text-center mb-8">
            <a href="<?= base_url(); ?>" class="inline-flex flex-col items-center gap-3 group">
                <span class="w-16 h-16 rounded-2xl bg-white border border-neutral-200 shadow-xs
                             flex items-center justify-center p-1.5 group-hover:scale-105 transition-transform">
                    <img src="<?= media_url($setting->ins_logo_path ?? '', 'image/png'); ?>"
                         alt="<?= htmlspecialchars($setting->ins_name ?? 'লোগো', ENT_QUOTES, 'UTF-8'); ?>"
                         class="max-w-full max-h-full object-contain">
                </span>
                <span>
                    <span class="block text-lg font-bold text-neutral-900 group-hover:text-indigo-600 transition-colors">
                        <?= htmlspecialchars($setting->ins_name ?? 'ইউনিয়ন পরিষদ', ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                    <span class="block text-xs text-neutral-500 mt-0.5">
                        <?= htmlspecialchars($setting->ins_title_ss ?? 'ডিজিটাল নাগরিক সেবা কেন্দ্র', ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                </span>
            </a>
        </div>

        <div class="ht-card">
            <div class="p-6 sm:p-8">

                <h1 class="text-xl font-bold text-neutral-900 mb-1">অ্যাডমিন প্যানেলে লগইন</h1>
                <p class="text-sm text-neutral-500 mb-6">
                    শুধুমাত্র পরিষদের কর্মকর্তা-কর্মিণীদের জন্য
                </p>

                <?php if ($ht_error): ?>
                    <div class="ht-badge ht-badge-danger w-full items-start gap-2 p-3 mb-5 text-left" role="alert">
                        <i class="ph ph-warning-circle text-base mt-px"></i>
                        <span><?= htmlspecialchars($ht_error, ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                <?php elseif ($ht_msg): ?>
                    <div class="ht-badge ht-badge-success w-full items-start gap-2 p-3 mb-5 text-left" role="status">
                        <i class="ph ph-check-circle text-base mt-px"></i>
                        <span><?= htmlspecialchars($ht_msg, ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                <?php endif; ?>

                <form method="post" action="<?= base_url('api_login'); ?>" class="space-y-4">
                    <div>
                        <label class="ht-label" for="logEmail">ইমেইল বা ইউজারনেম</label>
                        <input type="text" id="logEmail" name="identity" required autocomplete="username"
                               class="ht-field" placeholder="admin@union.gov.bd"
                               value="<?= htmlspecialchars($ht_ident ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="ht-label mb-0" for="logPassword">পাসওয়ার্ড</label>
                            <a href="<?= site_url('forgot_password'); ?>" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition-colors">
                                পাসওয়ার্ড ভুলে গেছেন?
                            </a>
                        </div>
                        <div class="relative">
                            <input type="password" id="logPassword" name="password" required
                                   autocomplete="current-password" data-saas-password
                                   class="ht-field pr-11" placeholder="••••••••">
                            <button type="button" data-saas-toggle-password
                                    class="absolute right-3 top-1/2 -translate-y-1/2 p-1 text-neutral-400
                                           hover:text-neutral-700 focus:outline-none"
                                    aria-label="পাসওয়ার্ড দেখান">
                                <i class="ph ph-eye text-lg" data-saas-eye="show"></i>
                                <i class="ph ph-eye-slash text-lg hidden" data-saas-eye="hide"></i>
                            </button>
                        </div>
                    </div>

                    <label class="flex items-center gap-2 text-sm text-neutral-600 cursor-pointer">
                        <input type="checkbox" name="remember" value="1"
                               class="h-4 w-4 rounded border-neutral-300 text-indigo-600 focus:ring-2 focus:ring-indigo-500">
                        <span>মনে রাখুন</span>
                    </label>

                    <button type="submit" class="ht-btn ht-btn-primary w-full justify-center">
                        <i class="ph ph-sign-in text-sm"></i>
                        <span>লগইন করুন</span>
                    </button>
                </form>
            </div>
        </div>

        <div class="text-center mt-6">
            <a href="<?= base_url(); ?>" class="inline-flex items-center gap-1.5 text-sm text-neutral-600 hover:text-indigo-600">
                <i class="ph ph-arrow-left"></i>
                <span>হোমে ফিরে যান</span>
            </a>
        </div>

    </div>
</main>

<script src="inc/assets/js/password-toggle.js"></script>
<?php
// Footer last: front/partials/footer.php closes </body></html> itself.
$this->load->view('front/partials/footer', $view_data);
