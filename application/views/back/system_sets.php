<!-- Breadcrumb & Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-neutral-200">
    <div>
        <nav class="flex items-center gap-2 text-xs text-neutral-500 mb-1">
            <a href="dashboard" class="hover:text-indigo-600 transition flex items-center gap-1">
                <i class="ph ph-house text-sm"></i> ড্যাশবোর্ড
            </a>
            <i class="ph ph-caret-right text-[10px] text-neutral-400"></i>
            <span class="text-neutral-800 font-medium">সিস্টেম সেটিংস</span>
        </nav>
        <h1 class="text-xl sm:text-2xl font-bold text-neutral-900 tracking-tight flex items-center gap-2">
            <span class="w-2.5 h-6 bg-indigo-600 rounded-full inline-block"></span>
            ইউনিয়ন পরিষদের সিস্টেম সেটিংস
        </h1>
        <p class="text-xs sm:text-sm text-neutral-500 mt-0.5">ইউনিয়নের নাম, লোগো, চেয়ারম্যান তথ্য ও অন্যান্য কনফিগারেশন</p>
    </div>
</div>

<form action="update_sets" enctype="multipart/form-data" method="post" class="space-y-6">

    <!-- Logo & Branding Section -->
    <div class="bg-white rounded-2xl border border-neutral-200/80 shadow-sm p-5 sm:p-6 space-y-5">
        <div class="flex items-center gap-2 pb-3 border-b border-neutral-100">
            <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                <i class="ph-bold ph-image-square text-base"></i>
            </div>
            <h3 class="text-sm font-bold text-neutral-900">লোগো ও পরিচয়</h3>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div class="space-y-3">
                <div class="flex items-center gap-4">
                    <img src="<?= media_url($setting_info->ins_logo_path, 'image/png') ?>" alt="বাংলা লোগো" class="w-16 h-16 rounded-xl object-contain border border-neutral-200 bg-neutral-50 unionLogo_bn_show">
                    <div class="flex-1">
                        <label class="block text-xs font-semibold text-neutral-700 mb-1.5">ইউনিয়নের লোগো (বাংলায়)</label>
                        <input type="file" class="block w-full text-xs text-neutral-700 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer unionLogo_bn" name="union_logo_bn" accept="image/*">
                    </div>
                </div>
            </div>

            <div class="space-y-3">
                <div class="flex items-center gap-4">
                    <?php if (empty($setting_info->ins_en_logo_path)) { ?>
                        <img src="<?= media_url($setting_info->ins_logo_path, 'image/png') ?>" alt="English Logo" class="w-16 h-16 rounded-xl object-contain border border-neutral-200 bg-neutral-50 unionLogo_eng_show">
                    <?php } else { ?>
                        <img src="<?= media_url($setting_info->ins_en_logo_path, 'image/png') ?>" alt="English Logo" class="w-16 h-16 rounded-xl object-contain border border-neutral-200 bg-neutral-50 unionLogo_eng_show">
                    <?php } ?>
                    <div class="flex-1">
                        <label class="block text-xs font-semibold text-neutral-700 mb-1.5">ইউনিয়নের লোগো (English)</label>
                        <input type="file" class="block w-full text-xs text-neutral-700 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-neutral-100 file:text-neutral-700 hover:file:bg-neutral-200 cursor-pointer unionLogo_eng_file" name="union_logo_eng" accept="image/*">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Union Name & Title Section -->
    <div class="bg-white rounded-2xl border border-neutral-200/80 shadow-sm p-5 sm:p-6 space-y-5">
        <div class="flex items-center gap-2 pb-3 border-b border-neutral-100">
            <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                <i class="ph-bold ph-buildings text-base"></i>
            </div>
            <h3 class="text-sm font-bold text-neutral-900">ইউনিয়ন পরিচয়</h3>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-neutral-700 mb-1.5">ইউনিয়নের নাম (বাংলায়)</label>
                <input type="text" name="ins_name" class="w-full text-xs px-3.5 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition" value="<?= htmlspecialchars($setting_info->ins_name ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div>
                <label class="block text-xs font-semibold text-neutral-700 mb-1.5">Union Name (English)</label>
                <input type="text" name="ins_name_eng" class="w-full text-xs px-3.5 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition" value="<?= htmlspecialchars($setting_info->ins_name_en ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div>
                <label class="block text-xs font-semibold text-neutral-700 mb-1.5">ইউনিয়নের টাইটেল (বাংলায়)</label>
                <input type="text" name="ins_title" class="w-full text-xs px-3.5 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition" value="<?= htmlspecialchars($setting_info->ins_title_ss ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div>
                <label class="block text-xs font-semibold text-neutral-700 mb-1.5">Union Title (English)</label>
                <input type="text" name="ins_title_eng" class="w-full text-xs px-3.5 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition" value="<?= htmlspecialchars($setting_info->ins_title_ens ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div>
                <label class="block text-xs font-semibold text-neutral-700 mb-1.5">ইউনিয়নের ঠিকানা (বাংলায়)</label>
                <input type="text" name="ins_address" class="w-full text-xs px-3.5 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition" value="<?= htmlspecialchars($setting_info->addre_s_s ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div>
                <label class="block text-xs font-semibold text-neutral-700 mb-1.5">Union Address (English)</label>
                <input type="text" name="ins_address_eng" class="w-full text-xs px-3.5 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition" value="<?= htmlspecialchars($setting_info->ins_address_eng ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div>
                <label class="block text-xs font-semibold text-neutral-700 mb-1.5">ইউনিয়নের ইমেইল</label>
                <input type="text" name="ins_email" class="w-full text-xs px-3.5 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition" value="<?= htmlspecialchars($setting_info->ins_emails ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            </div>
        </div>
    </div>

    <!-- Chairman & Socib Info Section -->
    <div class="bg-white rounded-2xl border border-neutral-200/80 shadow-sm p-5 sm:p-6 space-y-5">
        <div class="flex items-center gap-2 pb-3 border-b border-neutral-100">
            <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                <i class="ph-bold ph-user-gear text-base"></i>
            </div>
            <h3 class="text-sm font-bold text-neutral-900">চেয়ারম্যান ও সচিব তথ্য</h3>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-neutral-700 mb-1.5">চেয়ারম্যানের নাম (বাংলায়)</label>
                <input type="text" name="chair_name_bn" class="w-full text-xs px-3.5 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition" value="<?= htmlspecialchars($setting_info->cm_name_bn ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div>
                <label class="block text-xs font-semibold text-neutral-700 mb-1.5">Chairman Name (English)</label>
                <input type="text" name="chair_name_eng" class="w-full text-xs px-3.5 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition" value="<?= htmlspecialchars($setting_info->cm_name_en ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div>
                <label class="block text-xs font-semibold text-neutral-700 mb-1.5">Chairman Mobile</label>
                <input type="text" name="ins_cm_phone" class="w-full text-xs px-3.5 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition" value="<?= htmlspecialchars($setting_info->chairman_phone ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div>
                <label class="block text-xs font-semibold text-neutral-700 mb-1.5">সচিবের মোবাইল (Socib Phone)</label>
                <input type="text" name="ins_socib_phone" class="w-full text-xs px-3.5 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition" value="<?= htmlspecialchars($setting_info->socib_phone ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-neutral-700 mb-1.5">চেয়ারম্যানের বক্তব্য (Chairman Speach)</label>
                <textarea name="cm_speach" rows="6" class="w-full text-xs px-3.5 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition resize-none"><?= htmlspecialchars($setting_info->chairman_speach ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-neutral-700 mb-1.5">সচিবের বক্তব্য (Socib Speach)</label>
                <textarea name="socib_speach" rows="6" class="w-full text-xs px-3.5 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition resize-none"><?= htmlspecialchars($setting_info->socib_speach ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>
        </div>
    </div>

    <!-- bKash Payment Config Section -->
    <div class="bg-white rounded-2xl border border-neutral-200/80 shadow-sm p-5 sm:p-6 space-y-5">
        <div class="flex items-center gap-2 pb-3 border-b border-neutral-100">
            <div class="w-7 h-7 rounded-lg bg-pink-50 text-pink-600 flex items-center justify-center">
                <i class="ph-bold ph-device-mobile-camera text-base"></i>
            </div>
            <h3 class="text-sm font-bold text-neutral-900">bKash পেমেন্ট কনফিগারেশন</h3>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold text-neutral-700 mb-1.5">bKash Username</label>
                <input type="text" name="bkash_user_name" class="w-full text-xs px-3.5 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-pink-500/20 focus:border-pink-500 transition" value="<?= htmlspecialchars($setting_info->bKash_userName ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div>
                <label class="block text-xs font-semibold text-neutral-700 mb-1.5">bKash Password</label>
                <input type="text" name="bkash_pass_word" class="w-full text-xs px-3.5 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-pink-500/20 focus:border-pink-500 transition" value="<?= htmlspecialchars($setting_info->bKash_passWord ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div>
                <label class="block text-xs font-semibold text-neutral-700 mb-1.5">bKash Auth Token</label>
                <input type="text" name="bkash_auth" class="w-full text-xs px-3.5 py-2 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-pink-500/20 focus:border-pink-500 transition" value="<?= htmlspecialchars($setting_info->bKash_authToken ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            </div>
        </div>
    </div>

    <!-- Save Button -->
    <div class="flex justify-end">
        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 shadow-sm transition">
            <i class="ph-bold ph-floppy-disk text-base"></i>
            <span>সকল পরিবর্তন সংরক্ষণ করুন</span>
        </button>
    </div>
</form>

<script>
    $(document).on('change', '.unionLogo_bn', function () {
        showUploadedImage(this, '.unionLogo_bn_show');
    });
    $(document).on('change', '.unionLogo_eng_file', function () {
        showUploadedImage(this, '.unionLogo_eng_show');
    });

    function showUploadedImage(input, targetSelector) {
        if (input.files && input.files[0]) {
            let reader = new FileReader();
            reader.onload = function(e) {
                $(targetSelector).attr('src', e.target.result);
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
