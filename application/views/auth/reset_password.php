<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$page_title = 'নতুন পাসওয়ার্ড নির্ধারণ';
$page_desc  = ($setting->ins_title_ss ?? 'ইউনিয়ন পরিষদ') . ' — নতুন পাসওয়ার্ড সেট করুন';
$view_data  = ($this->data ?? get_defined_vars());
$view_data['page_title'] = $page_title;
$view_data['page_desc']  = $page_desc;

$this->load->view('front/partials/header', $view_data);

$ht_error = $error ?? $this->session->flashdata('error');
$ht_msg   = $message ?? $this->session->flashdata('message');
?>

<main class="grow">
    <div class="max-w-md mx-auto px-4 sm:px-6 py-12 lg:py-20">

        <!-- Branding Header -->
        <div class="text-center mb-8">
            <a href="<?= base_url(); ?>" class="inline-flex flex-col items-center gap-3 group">
                <span class="w-16 h-16 rounded-2xl bg-white border border-neutral-200 shadow-xs
                             flex items-center justify-center p-1.5 group-hover:scale-105 transition-transform">
                    <?php if (!empty($setting->ins_logo_path)): ?>
                        <img src="<?= media_url($setting->ins_logo_path, 'image/png'); ?>"
                             alt="<?= htmlspecialchars($setting->ins_name ?? 'লোগো', ENT_QUOTES, 'UTF-8'); ?>"
                             class="max-w-full max-h-full object-contain">
                    <?php else: ?>
                        <span class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold text-xl">V</span>
                    <?php endif; ?>
                </span>
                <span>
                    <span class="block text-lg font-bold text-neutral-900 group-hover:text-indigo-600 transition-colors">
                        <?= htmlspecialchars($setting->ins_name ?? 'Vireta Dev', ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                    <span class="block text-xs text-neutral-500 mt-0.5">
                        <?= htmlspecialchars($setting->ins_title_ss ?? 'ডিজিটাল নাগরিক সেবা কেন্দ্র', ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                </span>
            </a>
        </div>

        <!-- Main Card -->
        <div class="ht-card">
            <div class="p-6 sm:p-8">

                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl shrink-0">
                        <i class="ph ph-key"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-neutral-900">নতুন পাসওয়ার্ড সেট করুন</h1>
                        <p class="text-xs text-neutral-500">আপনার অ্যাকাউন্টের জন্য নতুন গোপন পাসওয়ার্ড দিন</p>
                    </div>
                </div>

                <p class="text-xs text-neutral-600 mt-2 mb-6 leading-relaxed">
                    নিরাপত্তার স্বার্থে পাসওয়ার্ডটি কমপক্ষে ৮ অক্ষরের রাখুন এবং এতে সংখ্যা ও চিহ্নের মিশ্রণ ব্যবহার করুন।
                </p>

                <?php if (!empty($ht_error)): ?>
                    <div class="ht-badge ht-badge-danger w-full items-start gap-2 p-3 mb-5 text-left" role="alert">
                        <i class="ph ph-warning-circle text-base mt-px shrink-0"></i>
                        <span><?= htmlspecialchars($ht_error, ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($ht_msg)): ?>
                    <div class="ht-badge ht-badge-success w-full items-start gap-2 p-3 mb-5 text-left" role="status">
                        <i class="ph ph-check-circle text-base mt-px shrink-0"></i>
                        <span><?= htmlspecialchars($ht_msg, ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                <?php endif; ?>

                <form method="post" action="<?= site_url('reset_password/' . htmlspecialchars($code, ENT_QUOTES, 'UTF-8')); ?>" class="space-y-4">
                    <div>
                        <label class="ht-label" for="new_password">নতুন পাসওয়ার্ড</label>
                        <div class="relative">
                            <input type="password" id="new_password" name="new_password" required autofocus
                                   autocomplete="new-password" minlength="8" data-saas-password
                                   class="ht-field pr-11" placeholder="কমপক্ষে ৮ অক্ষর">
                            <button type="button" data-saas-toggle-password
                                    class="absolute right-3 top-1/2 -translate-y-1/2 p-1 text-neutral-400 hover:text-neutral-700 focus:outline-none"
                                    aria-label="পাসওয়ার্ড দেখান">
                                <i class="ph ph-eye text-lg" data-saas-eye="show"></i>
                                <i class="ph ph-eye-slash text-lg hidden" data-saas-eye="hide"></i>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="ht-label" for="confirm_password">নতুন পাসওয়ার্ড নিশ্চিত করুন</label>
                        <div class="relative">
                            <input type="password" id="confirm_password" name="confirm_password" required
                                   autocomplete="new-password" minlength="8" data-saas-password
                                   class="ht-field pr-11" placeholder="পুনরায় পাসওয়ার্ডটি লিখুন">
                            <button type="button" data-saas-toggle-password
                                    class="absolute right-3 top-1/2 -translate-y-1/2 p-1 text-neutral-400 hover:text-neutral-700 focus:outline-none"
                                    aria-label="পাসওয়ার্ড দেখান">
                                <i class="ph ph-eye text-lg" data-saas-eye="show"></i>
                                <i class="ph ph-eye-slash text-lg hidden" data-saas-eye="hide"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="ht-btn ht-btn-primary w-full justify-center py-2.5 mt-2">
                        <i class="ph ph-check text-base"></i>
                        <span>পাসওয়ার্ড পরিবর্তন সম্পন্ন করুন</span>
                    </button>
                </form>

                <div class="mt-6 pt-5 border-t border-neutral-100 flex items-center justify-between text-xs">
                    <a href="<?= site_url('login'); ?>" class="inline-flex items-center gap-1.5 text-neutral-600 hover:text-indigo-600 font-medium transition-colors">
                        <i class="ph ph-arrow-left"></i>
                        <span>লগইন পাতায় ফিরে যান</span>
                    </a>
                </div>

            </div>
        </div>

    </div>
</main>

<script src="inc/assets/js/password-toggle.js"></script>
<?php
$this->load->view('front/partials/footer', $view_data);
