<!DOCTYPE html>
<html lang="bn" class="h-full bg-neutral-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($this_ins_name->ins_name ?? 'ইউনিয়ন পরিষদ', ENT_QUOTES, 'UTF-8'); ?> — সেবা সাময়িক স্থগিত</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Hind Siliguri"', '"Plus Jakarta Sans"', 'sans-serif'],
                        mono: ['monospace'],
                    },
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            600: '#4f46e5',
                            700: '#4338ca',
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body class="h-full flex flex-col justify-between font-sans text-neutral-800 antialiased selection:bg-rose-100 selection:text-rose-900">

    <!-- Header Bar -->
    <header class="w-full bg-white border-b border-neutral-200/80 py-4 px-4 sm:px-8">
        <div class="max-w-4xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <?php 
                    $logo = !empty($this_ins_name->ins_logo_path) ? media_url($this_ins_name->ins_logo_path, 'image/png') : '';
                    if ($logo):
                ?>
                    <img src="<?= htmlspecialchars($logo, ENT_QUOTES, 'UTF-8'); ?>" alt="Logo" class="w-10 h-10 object-contain rounded-full shadow-2xs">
                <?php else: ?>
                    <div class="w-10 h-10 rounded-full bg-neutral-100 border border-neutral-200 flex items-center justify-center text-neutral-500 font-bold text-lg">
                        <i class="ph ph-buildings"></i>
                    </div>
                <?php endif; ?>
                <div>
                    <h1 class="text-base sm:text-lg font-bold text-neutral-900 leading-tight">
                        <?= htmlspecialchars($this_ins_name->ins_name ?? 'ইউনিয়ন পরিষদ', ENT_QUOTES, 'UTF-8'); ?>
                    </h1>
                    <p class="text-xs text-neutral-500">
                        <?= htmlspecialchars($this_ins_name->ins_name_en ?? 'Union Parishad Portal', ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                </div>
            </div>
            
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-50 text-amber-800 border border-amber-200 text-xs font-bold">
                <i class="ph ph-warning-circle text-amber-600"></i>
                <span>স্থগিত / Suspended</span>
            </span>
        </div>
    </header>

    <!-- Main Notice Card -->
    <main class="flex-1 flex items-center justify-center p-4 sm:p-6 my-8">
        <div class="max-w-xl w-full bg-white rounded-3xl border border-neutral-200/90 shadow-sm p-6 sm:p-10 text-center">
            
            <div class="w-16 h-16 rounded-2xl bg-amber-50 text-amber-600 border border-amber-200/70 flex items-center justify-center text-3xl mx-auto mb-5 shadow-2xs">
                <i class="ph ph-pause-circle"></i>
            </div>

            <h2 class="text-xl sm:text-2xl font-black text-neutral-900 tracking-tight mb-2">
                পোর্টাল সেবা সাময়িকভাবে স্থগিত
            </h2>
            <p class="text-xs sm:text-sm font-semibold text-neutral-500 mb-6">
                Portal Services Temporarily Suspended
            </p>

            <p class="text-sm text-neutral-600 leading-relaxed text-justify sm:text-center mb-8">
                সম্মানিত নাগরিকবৃন্দ, এই ইউনিয়ন পরিষদের অনলাইন নাগরিক সেবা বাতায়নটি প্রশাসনিক বা সাবস্ক্রিপশন সংক্রান্ত কারণে প্ল্যাটফর্ম কর্তৃপক্ষ কর্তৃক সাময়িকভাবে স্থগিত রাখা হয়েছে। অনলাইন নাগরিক সনদ আবেদন, হোল্ডিং ট্যাক্স যাচাই ও ই-সেবাসমূহ পুনরায় চালু না হওয়া পর্যন্ত সরাসরি ইউনিয়ন পরিষদ কার্যালয়ে যোগাযোগের অনুরোধ করা হচ্ছে।
            </p>

            <!-- Office Contact Info Box -->
            <div class="bg-neutral-50 border border-neutral-200/80 rounded-2xl p-4 sm:p-5 text-left text-xs space-y-3 mb-6">
                <div class="font-bold text-neutral-800 pb-2 border-b border-neutral-200 flex items-center gap-2">
                    <i class="ph ph-address-book text-indigo-600 text-base"></i>
                    <span>সরাসরি যোগাযোগের ঠিকানা ও বিবরণ:</span>
                </div>

                <div class="flex items-start gap-2.5 text-neutral-700">
                    <i class="ph ph-map-pin text-neutral-400 text-base shrink-0 mt-0.5"></i>
                    <span><?= htmlspecialchars($this_ins_name->addre_s_s ?? 'ইউনিয়ন পরিষদ কার্যালয়', ENT_QUOTES, 'UTF-8'); ?></span>
                </div>

                <?php if (!empty($this_ins_name->socib_phone) || !empty($this_ins_name->chairman_phone)): ?>
                <div class="flex items-center gap-2.5 text-neutral-700">
                    <i class="ph ph-phone text-neutral-400 text-base shrink-0"></i>
                    <span>ফোন / হেল্পলাইন: <?= htmlspecialchars($this_ins_name->socib_phone ?: $this_ins_name->chairman_phone, ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
                <?php endif; ?>

                <?php if (!empty($this_ins_name->ins_emails)): ?>
                <div class="flex items-center gap-2.5 text-neutral-700">
                    <i class="ph ph-envelope text-neutral-400 text-base shrink-0"></i>
                    <span>ইমেইল: <?= htmlspecialchars($this_ins_name->ins_emails, ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
                <?php endif; ?>
            </div>

            <!-- Platform Support Note -->
            <div class="text-[11px] text-neutral-400 pt-3 border-t border-neutral-100 flex items-center justify-center gap-2">
                <i class="ph ph-shield-check text-neutral-400"></i>
                <span>প্ল্যাটফর্ম সাপোর্ট ও সমাধান: <strong class="text-neutral-600">support@viretadev.com</strong></span>
            </div>

        </div>
    </main>

    <!-- Footer -->
    <footer class="w-full bg-white border-t border-neutral-200/80 py-4 px-4 text-center text-xs text-neutral-500">
        <p>&copy; <?= date('Y'); ?> <?= htmlspecialchars($this_ins_name->ins_name ?? 'ইউনিয়ন পরিষদ', ENT_QUOTES, 'UTF-8'); ?> | সর্বস্বত্ব সংরক্ষিত</p>
    </footer>

</body>
</html>
