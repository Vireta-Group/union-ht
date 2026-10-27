<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$page_title = 'পাসওয়ার্ড পুনরুদ্ধার';
$page_desc  = ($setting->ins_title_ss ?? 'ইউনিয়ন পরিষদ') . ' — পাসওয়ার্ড রিসেট করুন';
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
                        <i class="ph ph-lock-key-open"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-neutral-900">পাসওয়ার্ড পুনরুদ্ধার</h1>
                        <p class="text-xs text-neutral-500">আপনার রেজিস্টার্ড অ্যাকাউন্টের তথ্য দিন</p>
                    </div>
                </div>

                <p class="text-xs text-neutral-600 mt-2 mb-6 leading-relaxed">
                    আপনার অ্যাকাউন্টের রেজিস্টার্ড ইমেইল বা ইউজারনেমটি লিখুন। আমরা উক্ত ইমেইলে একটি নিরাপদ পাসওয়ার্ড রিসেট লিংক পাঠিয়ে দিব।
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

                <form method="post" action="<?= site_url('forgot_password'); ?>" class="space-y-4">
                    <div>
                        <label class="ht-label" for="identity">ইমেইল বা ইউজারনেম</label>
                        <div class="relative">
                            <input type="text" id="identity" name="identity" required autofocus
                                   autocomplete="username"
                                   class="ht-field pl-10" placeholder="admin@union.gov.bd বা ইউজারনেম"
                                   value="<?= htmlspecialchars($this->input->post('identity') ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400 text-lg pointer-events-none">
                                <i class="ph ph-envelope-simple"></i>
                            </span>
                        </div>
                    </div>

                    <button type="submit" class="ht-btn ht-btn-primary w-full justify-center py-2.5">
                        <i class="ph ph-paper-plane-tilt text-base"></i>
                        <span>রিসেট লিংক পাঠান</span>
                    </button>
                </form>

                <div class="mt-6 pt-5 border-t border-neutral-100 flex items-center justify-between text-xs">
                    <a href="<?= site_url('login'); ?>" class="inline-flex items-center gap-1.5 text-neutral-600 hover:text-indigo-600 font-medium transition-colors">
                        <i class="ph ph-arrow-left"></i>
                        <span>লগইন পাতায় ফিরে যান</span>
                    </a>
                    <a href="<?= base_url(); ?>" class="text-neutral-500 hover:text-neutral-800 transition-colors">
                        হোমপেজ
                    </a>
                </div>

            </div>
        </div>

    </div>
</main>

<?php
$this->load->view('front/partials/footer', $view_data);
