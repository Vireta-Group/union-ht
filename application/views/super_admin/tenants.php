<?php
/**
 * Tenant directory.
 *
 * BODY OF THE OLD super_admin/index.php, 2026-09-30.
 *
 * The document, the header and the <main> wrapper are gone: the panel
 * grew a shell, and this is now a page inside Template::sadmin() rather
 * than a whole page. The header comment, the metrics, the search, the
 * table and both modals came across unchanged. What changed is described
 * at each edit below, not here, so the diff between this and the file it
 * replaced is readable in place.
 *
 * The old file is left on disk rather than deleted. There is no git here
 * (see AGENTS.md), so this is the only copy of the pre-shell page, and a
 * 500 that turns out to be a shell problem is much easier to diagnose
 * with it in the tree.
 */
defined('BASEPATH') OR exit('No direct script access allowed');
?>

    <!-- Welcome Banner & System Status -->
    <div class="bg-white border border-neutral-200 rounded-2xl sm:rounded-3xl p-4 sm:p-8 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4 sm:gap-6">
        <div>
            <span class="text-xs font-bold text-indigo-700 uppercase tracking-wider bg-indigo-50 px-2.5 py-1 rounded-md border border-indigo-100">
                সেন্ট্রাল অ্যাডমিনিস্ট্রেশন হাব
            </span>
            <p class="text-sm sm:text-base font-semibold text-neutral-700 mt-2.5">সকল ইউনিয়ন পরিষদ ও ডোমেন ব্যবস্থাপনা</p>
            <p class="text-xs sm:text-sm text-neutral-600 mt-1">
                এখান থেকে বাংলাদেশের প্রতিটি ইউনিয়নের ক্লাউড ইনস্ট্যান্স, সাবডোমেন, সরকারি ডোমেন ও অ্যাক্টিভেশন নিয়ন্ত্রণ করুন।
            </p>
        </div>
        <div class="flex items-center gap-2.5 bg-emerald-50 border border-emerald-200 text-emerald-800 px-3.5 py-2.5 sm:px-4 sm:py-3 rounded-xl sm:rounded-2xl w-full sm:w-auto">
            <i class="ph ph-check-circle text-emerald-600 text-2xl shrink-0" style="font-weight: bold;"></i>
            <div class="text-xs">
                <div class="font-bold text-emerald-950">মাল্টি-টেন্যান্ট আইসোলেশন সক্রিয়</div>
                <div class="text-emerald-700">ডিজিটাল সনদ, হোল্ডিং ও রাউটিং নিরাপদ</div>
            </div>
        </div>
    </div>

    <!-- Metrics Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6">
        
        <!-- Card 1 -->
        <div class="bg-white p-4 sm:p-6 rounded-2xl border border-neutral-200 shadow-xs">
            <div class="flex items-center justify-between text-neutral-500 mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-neutral-600">রেজিস্টার্ড ইউনিয়ন</span>
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-700 flex items-center justify-center text-xl">
                    <i class="ph ph-buildings"></i>
                </div>
            </div>
            <div class="text-3xl sm:text-4xl font-black text-neutral-900 bengali-num">
                <?= count($tenants); ?>
            </div>
            <p class="text-[11px] text-neutral-500 mt-1">সক্রিয় ক্লাউড ইনস্ট্যান্স</p>
        </div>

        <!-- Card 2 -->
        <div class="bg-white p-4 sm:p-6 rounded-2xl border border-neutral-200 shadow-xs">
            <div class="flex items-center justify-between text-neutral-500 mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-neutral-600">কাস্টম সরকারি ডোমেন</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center text-xl">
                    <i class="ph ph-globe"></i>
                </div>
            </div>
            <div class="text-3xl sm:text-4xl font-black text-neutral-900 bengali-num">
                <?= $total_custom_domains; ?>
            </div>
            <p class="text-[11px] text-neutral-500 mt-1">.gov.bd প্রাতিষ্ঠানিক ডোমেন</p>
        </div>

        <!-- Card 3 -->
        <div class="bg-white p-4 sm:p-6 rounded-2xl border border-neutral-200 shadow-xs">
            <div class="flex items-center justify-between text-neutral-500 mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-neutral-600">মোট করদাতা খানা</span>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center text-xl">
                    <i class="ph ph-house-line"></i>
                </div>
            </div>
            <div class="text-3xl sm:text-4xl font-black text-neutral-900 bengali-num">
                <?= number_format($total_holdings); ?>
            </div>
            <p class="text-[11px] text-neutral-500 mt-1">হোল্ডিং ডাটাবেজ এন্ট্রি</p>
        </div>

        <!-- Card 4 -->
        <div class="bg-white p-4 sm:p-6 rounded-2xl border border-neutral-200 shadow-xs">
            <div class="flex items-center justify-between text-neutral-500 mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-neutral-600">ইস্যুকৃত নাগরিক সনদ</span>
                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center text-xl">
                    <i class="ph ph-certificate"></i>
                </div>
            </div>
            <div class="text-3xl sm:text-4xl font-black text-neutral-900 bengali-num">
                <?= number_format($total_sonods); ?>
            </div>
            <p class="text-[11px] text-neutral-500 mt-1">কিউআর কোড ভেরিফাইড সনদ</p>
        </div>

    </div>

    <!-- Tenants Directory Table -->
    <div class="min-w-0 bg-white border border-neutral-200 rounded-3xl overflow-hidden shadow-xs">
        
        <div class="p-4 sm:p-6 border-b border-neutral-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-neutral-900 flex items-center gap-2">
                    <i class="ph ph-list-dashes text-indigo-600"></i>
                    <span class="lang-bn">সকল ইউনিয়ন পরিষদ তালিকা ও ম্যানেজমেন্ট</span>
                    <span class="lang-en">Union Parishads Directory & Management</span>
                </h2>
                <p class="text-xs text-neutral-500 mt-0.5">
                    <span class="lang-bn">যেকোনো ইউনিয়নের ড্যাশবোর্ডে সরাসরি ১-ক্লিকে প্রবেশ করুন বা ডোমেন কনফিগার করুন</span>
                    <span class="lang-en">1-click jump into any parishad dashboard or configure custom domains</span>
                </p>
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <div class="relative w-full sm:w-auto">
                    <i class="ph ph-magnifying-glass absolute left-3 top-2.5 text-neutral-400 text-sm"></i>
                    <input 
                        type="text" 
                        id="searchTenant" 
                        onkeyup="filterTenants()" 
                        placeholder="ইউনিয়ন বা ডোমেন সার্চ করুন..." 
                        class="pl-9 pr-3.5 py-1.5 text-xs rounded-xl border border-neutral-300 focus:outline-none focus:border-indigo-600 bg-neutral-50 w-full sm:w-64"
                    >
                </div>
            </div>
        </div>

        <!-- Table Container -->
        <!-- Mobile scroll hint. The table keeps a min-width so its columns
             stay readable, which means a narrow screen has to scroll it
             sideways. The scrollbar is the only cue and touch devices hide
             it until you drag, so the row reads as "clipped" rather than
             "scrollable". This says so explicitly, and is hidden at sm:
             where the table mostly fits. -->
        <p class="sm:hidden mt-2 flex items-center gap-1.5 text-[11px] text-neutral-400">
            <i class="ph ph-arrows-left-right text-neutral-300"></i>
            <span>পুরো তালিকা দেখতে চওড়ায় স্ক্রল করুন</span>
        </p>


        <div class="overflow-x-auto">
            <table class="w-full min-w-245 text-left text-xs" id="tenantsTable">
                <thead class="bg-neutral-50 text-neutral-600 uppercase font-bold text-[11px] tracking-wider border-b border-neutral-200">
                    <tr>
                        <th class="py-3.5 px-4 sm:px-6"><span class="lang-bn">টেন্যান্ট ID</span><span class="lang-en">Tenant ID</span></th>
                        <th class="py-3.5 px-4"><span class="lang-bn">ইউনিয়ন পরিষদের নাম</span><span class="lang-en">Union Parishad Name</span></th>
                        <th class="py-3.5 px-4"><span class="lang-bn">হোস্ট ও সাবডোমেন</span><span class="lang-en">Subdomain Host</span></th>
                        <th class="py-3.5 px-4"><span class="lang-bn">সরকারি ডোমেন (.gov.bd)</span><span class="lang-en">Gov Domain (.gov.bd)</span></th>
                        <th class="py-3.5 px-4"><span class="lang-bn">অ্যাডমিন ও যোগাযোগ</span><span class="lang-en">Admin & Contact</span></th>
                        <th class="py-3.5 px-4"><span class="lang-bn">হোল্ডিং</span><span class="lang-en">Holdings</span></th>
                        <th class="py-3.5 px-4"><span class="lang-bn">প্যাকেজ ও মেয়াদ</span><span class="lang-en">Plan & Validity</span></th>
                        <th class="py-3.5 px-4"><span class="lang-bn">অবস্থা</span><span class="lang-en">Status</span></th>
                        <th class="py-3.5 px-4 text-right pr-6"><span class="lang-bn">অ্যাকশন</span><span class="lang-en">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 font-medium">
                    <?php foreach ($tenants as $t): 
                        $custom_dom = null;
                        $subdom = null;
                        foreach ($t->domains as $d) {
                            if (str_ends_with($d->hostname, '.gov.bd') || (!str_ends_with($d->hostname, '.localhost') && !str_ends_with($d->hostname, '.local') && !str_contains($d->hostname, 'viretaup.com') && $d->hostname !== 'localhost' && $d->hostname !== '127.0.0.1')) {
                                $custom_dom = $d;
                            } else if (str_ends_with($d->hostname, '.localhost') || str_ends_with($d->hostname, '.local')) {
                                $subdom = $d->hostname;
                            }
                        }
                        /*
                         * Build the preview link for a tenant hostname.
                         *
                         * This used to be the literal string
                         * "http://<host>:8080" in both the href and the visible
                         * text. That is wrong twice over in production: the
                         * scheme is not http, and 8080 is the dev server's port,
                         * so a .gov.bd tenant was advertised as
                         * http://baghmaraup.gov.bd:8080 — a link that cannot
                         * resolve, presented as though it could.
                         *
                         * So the scheme comes from the request, and the port is
                         * carried over only when the request actually had one.
                         * In development that reproduces what the owner types by
                         * hand (http://kanchan.localhost:8080); behind a TLS
                         * terminator it yields https://baghmaraup.gov.bd with no
                         * port at all.
                         *
                         * The visible text is the bare hostname. The port is an
                         * artefact of how this box is being served, not part of
                         * the parishad's address, and printing it there reads
                         * as though the address included it.
                         */
                        $ht_scheme = is_https() ? 'https' : 'http';
                        $ht_reqhost = (string)($_SERVER['HTTP_HOST'] ?? '');
                        $ht_port   = '';
                        if (($c = strrpos($ht_reqhost, ':')) !== FALSE
                            && ctype_digit(substr($ht_reqhost, $c + 1))) {
                            $ht_port = substr($ht_reqhost, $c + 1);
                        }
                        $subdom_url = $ht_scheme . '://' . $subdom
                                    . ($ht_port !== '' ? ':' . $ht_port : '');
                    ?>
                    <tr class="hover:bg-neutral-50/80 transition-colors tenant-row">
                        <!-- Tenant ID -->
                        <td class="py-4 px-4 sm:px-6 font-mono font-bold text-neutral-900">
                            <span class="bg-neutral-100 text-neutral-800 px-2 py-0.5 rounded border border-neutral-300">
                                #<?= $t->ins_titute_p_idds; ?>
                            </span>
                        </td>

                        <!-- Union Name -->
                        <td class="py-4 px-4">
                            <div class="font-bold text-neutral-900 text-sm">
                                <?= htmlspecialchars($t->ins_name ?? 'নামবিহীন ইউনিয়ন', ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                            <div class="text-[11px] text-neutral-500 font-sans">
                                <?= htmlspecialchars($t->ins_name_en ?? $t->sub_domains ?? '', ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                        </td>

                        <!-- Subdomain Host -->
                        <td class="py-4 px-4 font-mono text-[11px]">
                            <?php if ($subdom): ?>
                                <a href="<?= htmlspecialchars($subdom_url, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener" class="text-indigo-700 font-semibold hover:underline flex items-center gap-1">
                                    <span><?= htmlspecialchars($subdom, ENT_QUOTES, 'UTF-8'); ?></span>
                                    <i class="ph ph-arrow-square-out text-xs"></i>
                                </a>
                            <?php else: ?>
                                <span class="text-neutral-400">ডোমেন যুক্ত নেই</span>
                            <?php endif; ?>
                        </td>

                        <!-- Custom Domain -->
                        <td class="py-4 px-4">
                            <?php if ($custom_dom): ?>
                                <div class="flex items-center gap-1.5 font-mono text-[11px]">
                                    <span class="font-bold text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                                        <?= htmlspecialchars($custom_dom->hostname, ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                    <?php if ($custom_dom->is_verified): ?>
                                        <span class="text-[10px] text-emerald-700 bg-emerald-100 px-1 rounded font-bold">সক্রিয়</span>
                                    <?php else: ?>
                                        <span class="text-[10px] text-amber-700 bg-amber-100 px-1 rounded font-bold">পেন্ডিং</span>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <button 
                                    onclick="openEditModal(<?= htmlspecialchars(json_encode($t), ENT_QUOTES, 'UTF-8'); ?>)"
                                    class="text-[11px] text-neutral-500 hover:text-indigo-700 flex items-center gap-1 cursor-pointer">
                                    <i class="ph ph-plus-circle"></i>
                                    <span>.gov.bd যোগ করুন</span>
                                </button>
                            <?php endif; ?>
                        </td>

                        <!-- Admin & Contact -->
                        <td class="py-4 px-4 text-[11px] text-neutral-600 space-y-0.5">
                            <?php if (!empty($t->ins_emails)): ?>
                                <div class="font-mono text-neutral-900 font-semibold flex items-center gap-1">
                                    <i class="ph ph-envelope text-neutral-400"></i>
                                    <span><?= htmlspecialchars($t->ins_emails, ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($t->socib_phone)): ?>
                                <div class="flex items-center gap-1">
                                    <i class="ph ph-phone text-neutral-400"></i>
                                    <span><?= htmlspecialchars($t->socib_phone, ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                            <?php endif; ?>
                        </td>

                        <!-- Holdings Count -->
                        <td class="py-4 px-4 font-mono font-bold text-neutral-700 bengali-num">
                            <?= number_format($t->holdings_count); ?> টি
                        </td>


                        <!-- Plan & subscription expiry -->
                        <td class="py-4 px-4 text-[11px]">
                            <?php
                            /*
                             * The plan, and whether it is paid up.
                             *
                             * subscriptions.end_date is the source of truth.
                             * ins_titute.expire_times is NOT read here even
                             * though the edit modal edits it, because that
                             * column holds three formats at once (tenant 1
                             * has a unix timestamp, tenant 10 a YYYY-MM-DD
                             * date) and nothing in the schema prevents a
                             * stale one. subscriptions is the table that
                             * says when a tenant lapses.
                             */
                            $ht_sub = $t->subscription;
                            $ht_left = $ht_sub ? subscription_days_left($ht_sub->end_date) : 0;
                            ?>
                            <?php if ($ht_sub): ?>
                                <div class="font-bold text-neutral-800"><?= htmlspecialchars($ht_sub->name_bn ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
                                <div class="mt-0.5 flex items-center gap-1">
                                    <?php
                                    list($ht_lbl, $ht_cls) = status_chip($ht_sub->status);
                                    if ($ht_sub->status === 'active' && $ht_left < 0) { $ht_lbl = 'মেয়াদোত্তীর্ণ'; $ht_cls = 'bg-rose-50 text-rose-700 border-rose-200'; }
                                    ?>
                                    <span class="px-1.5 py-0.5 rounded border font-bold <?= $ht_cls ?>"><?= $ht_lbl ?></span>
                                    <?php if ($ht_sub->status === 'active'): ?>
                                        <span class="text-neutral-500 bengali-num"><?= bn_number(abs($ht_left)); ?> দিন</span>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <span class="text-amber-600 font-bold">প্যাকেজ নেই</span>
                            <?php endif; ?>
                        </td>

                        <!-- Active / suspended -->
                        <td class="py-4 px-4">
                            <?php if ($t->is_active): ?>
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 text-[11px] font-bold">
                                    <i class="ph ph-check-circle"></i> সক্রিয় <span class="text-[9px] font-normal opacity-75">/ Active</span>
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-neutral-100 text-neutral-600 border border-neutral-300 text-[11px] font-bold">
                                    <i class="ph ph-pause-circle"></i> স্থগিত <span class="text-[9px] font-normal opacity-75">/ Suspended</span>
                                </span>
                            <?php endif; ?>
                        </td>

                        <!-- Actions -->
                        <td class="py-4 px-4 text-right pr-6 space-x-1.5 whitespace-nowrap">
                            
                            <!-- Temporary Delegated Support Access Button -->
                            <button 
                                type="button"
                                onclick="openTempAdminModal(<?= htmlspecialchars(json_encode($t), ENT_QUOTES, 'UTF-8'); ?>)"
                                title="সাময়িক সহায়তা এক্সেস তৈরি বা পরিচালনা করুন"
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-indigo-700 hover:bg-indigo-800 text-white font-bold text-xs shadow-xs transition-colors cursor-pointer">
                                <i class="ph ph-shield-check text-xs"></i>
                                <span>সহায়তা এক্সেস</span>
                            </button>

                            <!-- Edit Tenant Info -->
                            <button 
                                onclick="openEditModal(<?= htmlspecialchars(json_encode($t), ENT_QUOTES, 'UTF-8'); ?>)"
                                title="তথ্য ও ডোমেন সম্পাদনা"
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-neutral-100 hover:bg-neutral-200 text-neutral-800 font-bold text-xs border border-neutral-300 transition-colors cursor-pointer">
                                <i class="ph ph-pencil-simple text-xs"></i>
                                <span>এডিট</span>
                            </button>

                        <!--
                            Suspend / reactivate.

                            The button says "স্থগিত" and it does exactly that:
                            it flips ins_titute.is_active, which the panel
                            records and displays. It does NOT take the
                            parishad's website down — nothing reads
                            is_active yet. The tooltip says so, because a
                            button labelled "suspend" that leaves the site
                            serving is the kind of thing an operator
                            discovers during an incident rather than
                            before one.
                        -->
                        <?php
                            $active_btn_cls = $t->is_active 
                                ? 'bg-amber-50 hover:bg-amber-100 text-amber-800 border-amber-300' 
                                : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border-emerald-300';
                        ?>
                        <button
                            type="button"
                            onclick="toggleTenantActive(<?= (int) $t->ins_titute_p_idds; ?>, <?= $t->is_active ? 'true' : 'false' ?>, <?= htmlspecialchars(json_encode($t->ins_name), ENT_QUOTES, 'UTF-8'); ?>)"
                            title="<?= $t->is_active ? 'স্থগিত করুন' : 'সক্রিয় করুন' ?>"
                            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg <?= $active_btn_cls; ?> font-bold text-xs border transition-colors cursor-pointer">
                            <i class="ph <?= $t->is_active ? 'ph-pause' : 'ph-play' ?> text-xs"></i>
                        </button>

                            <!-- Public Portal View -->
                            <a 
                                href="<?= base_url('?tenant=' . $t->ins_titute_p_idds); ?>" 
                                target="_blank" 
                                title="পাবলিক পোর্টাল দেখুন"
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-neutral-100 hover:bg-neutral-200 text-neutral-700 font-bold text-xs border border-neutral-300 transition-colors">
                                <i class="ph ph-arrow-square-out text-xs"></i>
                            </a>

                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>


<!-- Modal: Onboard New Tenant -->
<div id="onboardModal" class="fixed inset-0 z-50 bg-neutral-900/60 backdrop-blur-xs hidden overflow-y-auto p-3 sm:p-6 justify-center items-start sm:items-center">
    <div class="relative bg-white rounded-3xl max-w-xl w-full my-auto shadow-2xl border border-neutral-200 flex flex-col max-h-[90vh] overflow-hidden">
        
        <!-- Pinned Header -->
        <div class="px-5 sm:px-7 py-4 border-b border-neutral-200 flex items-center justify-between shrink-0 bg-white z-20">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-indigo-50 border border-indigo-200 text-indigo-700 flex items-center justify-center font-bold text-lg shadow-xs">
                    <i class="ph ph-buildings"></i>
                </div>
                <div>
                    <h3 class="text-base sm:text-lg font-bold text-neutral-900 leading-tight">নতুন ইউনিয়ন পরিষদ অনবোর্ডিং</h3>
                    <p class="text-[11px] text-neutral-500 font-medium">Onboard New Tenant / Parishad Portal</p>
                </div>
            </div>
            <button type="button" onclick="closeOnboardModal()" class="w-8 h-8 rounded-full bg-neutral-100 hover:bg-neutral-200 text-neutral-500 hover:text-neutral-800 flex items-center justify-center text-lg transition-colors cursor-pointer" aria-label="বন্ধ করুন">
                <i class="ph ph-x"></i>
            </button>
        </div>

        <form id="onboardForm" onsubmit="submitOnboard(event)" class="flex flex-col flex-1 overflow-hidden min-h-0 m-0">
            <div class="p-5 sm:p-7 overflow-y-auto flex-1 space-y-4 text-xs">
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-neutral-700 mb-1">
                        ইউনিয়ন পরিষদের বাংলা নাম * <span class="text-[10px] font-normal text-neutral-400">/ Bengali Name</span>
                    </label>
                    <input type="text" id="ob_union_name" required placeholder="যেমন: কাঞ্চন ইউনিয়ন পরিষদ" class="w-full p-2.5 rounded-xl border border-neutral-300 text-xs focus:border-indigo-600 focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-neutral-700 mb-1">
                        ইউনিয়নের ইংরেজি নাম <span class="text-[10px] font-normal text-neutral-400">/ English Name</span>
                    </label>
                    <input type="text" id="ob_union_name_en" placeholder="e.g. Kanchan Union Parishad" class="w-full p-2.5 rounded-xl border border-neutral-300 text-xs focus:border-indigo-600 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-neutral-700 mb-1">
                        সাবডোমেন প্রিফিক্স * <span class="text-[10px] font-normal text-neutral-400">/ Subdomain Slug</span>
                    </label>
                    <div class="relative">
                        <input type="text" id="ob_subdomain" required placeholder="kanchan" class="w-full p-2.5 rounded-xl border border-neutral-300 text-xs font-mono focus:border-indigo-600 focus:outline-none">
                    </div>
                    <p class="text-[10px] text-neutral-500 mt-0.5">যেমন: kanchan.localhost ও kanchan.viretaup.com</p>
                </div>
                <div>
                    <label class="block font-bold text-neutral-700 mb-1">
                        কাস্টম সরকারি ডোমেন <span class="text-[10px] font-normal text-neutral-400">/ Gov Domain (.gov.bd)</span>
                    </label>
                    <input type="text" id="ob_custom_domain" placeholder="যেমন: kanchanup.gov.bd" class="w-full p-2.5 rounded-xl border border-neutral-300 text-xs font-mono focus:border-indigo-600 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-neutral-700 mb-1">
                        অফিশিয়াল অ্যাডমিন ইমেইল * <span class="text-[10px] font-normal text-neutral-400">/ Admin Email</span>
                    </label>
                    <input type="email" id="ob_admin_email" required placeholder="up.kanchan@gov.bd" class="w-full p-2.5 rounded-xl border border-neutral-300 text-xs focus:border-indigo-600 focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-neutral-700 mb-1">
                        সচিব / চেয়ারম্যানের ফোন <span class="text-[10px] font-normal text-neutral-400">/ Contact Phone</span>
                    </label>
                    <input type="text" id="ob_admin_phone" placeholder="০১৭xxxxxxxx" class="w-full p-2.5 rounded-xl border border-neutral-300 text-xs focus:border-indigo-600 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-neutral-700 mb-1">
                        অ্যাডমিনের নাম <span class="text-[10px] font-normal text-neutral-400">/ Admin Name</span>
                    </label>
                    <input type="text" id="ob_admin_name" placeholder="মো: আকরাম হোসেন" class="w-full p-2.5 rounded-xl border border-neutral-300 text-xs focus:border-indigo-600 focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-neutral-700 mb-1">
                        লগইন পাসওয়ার্ড * <span class="text-[10px] font-normal text-neutral-400">/ Password</span>
                    </label>
                    <input type="text" id="ob_admin_password" value="password123" required class="w-full p-2.5 rounded-xl border border-neutral-300 text-xs font-mono focus:border-indigo-600 focus:outline-none">
                </div>
            </div>

            <!-- Subscription Plan & Duration Presets -->
            <div class="pt-2 border-t border-neutral-100">
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block font-bold text-neutral-800">
                        সাবস্ক্রিপশন প্যাকেজ ও মেয়াদকাল <span class="text-[10px] font-normal text-neutral-400">/ Plan & Duration</span>
                    </label>
                    <span class="text-[10px] text-neutral-400">প্রিসেট বেছে নিন বা কাস্টম দিন</span>
                </div>

                <!-- Presets selector pills -->
                <div class="flex flex-wrap items-center gap-1.5 mb-2.5" id="ob_preset_group">
                    <button type="button" onclick="setObPresetDuration(30, '১ মাস / 30 Days', this)"
                            class="ob-preset-btn px-2.5 py-1 rounded-lg border border-neutral-200 bg-neutral-50 hover:bg-indigo-50 hover:border-indigo-300 text-neutral-700 font-semibold transition-all">
                        ১ মাস (৩০ দিন)
                    </button>
                    <button type="button" onclick="setObPresetDuration(90, '৩ মাস / 90 Days', this)"
                            class="ob-preset-btn px-2.5 py-1 rounded-lg border border-neutral-200 bg-neutral-50 hover:bg-indigo-50 hover:border-indigo-300 text-neutral-700 font-semibold transition-all">
                        ৩ মাস (৯০ দিন)
                    </button>
                    <button type="button" onclick="setObPresetDuration(180, '৬ মাস / 180 Days', this)"
                            class="ob-preset-btn px-2.5 py-1 rounded-lg border border-neutral-200 bg-neutral-50 hover:bg-indigo-50 hover:border-indigo-300 text-neutral-700 font-semibold transition-all">
                        ৬ মাস (১৮০ দিন)
                    </button>
                    <button type="button" onclick="setObPresetDuration(365, '১ বছর / 365 Days', this)"
                            class="ob-preset-btn active px-2.5 py-1 rounded-lg border border-indigo-600 bg-indigo-50 text-indigo-700 font-bold transition-all">
                        ১ বছর (৩৬৫ দিন)
                    </button>
                    <button type="button" onclick="toggleObCustomDays(this)"
                            class="ob-preset-btn px-2.5 py-1 rounded-lg border border-neutral-200 bg-neutral-50 hover:bg-indigo-50 hover:border-indigo-300 text-neutral-700 font-semibold transition-all">
                        কাস্টম দিন...
                    </button>
                </div>

                <!-- Custom days inline field -->
                <div id="ob_custom_days_box" class="mb-3 p-2.5 rounded-xl bg-indigo-50/60 border border-indigo-100 items-center gap-2" style="display:none">
                    <span class="text-[11px] font-semibold text-neutral-700">কাস্টম দিনের সংখ্যা:</span>
                    <input type="number" id="ob_custom_days_val" min="1" max="1825" placeholder="যেমন: 45 বা 120"
                           oninput="onObCustomDaysInput(this.value)"
                           class="w-24 p-1.5 rounded-lg border border-neutral-300 text-xs font-mono focus:border-indigo-600 focus:outline-none bg-white">
                    <span class="text-[11px] text-neutral-500">দিন (আজ থেকে হিসাব)</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-neutral-700 mb-1" for="ob_package">
                            সাবস্ক্রিপশন প্যাকেজ * <span class="text-[10px] font-normal text-neutral-400">/ Package</span>
                        </label>
                        <select id="ob_package" required class="w-full p-2.5 rounded-xl border border-neutral-300 text-xs focus:border-indigo-600 focus:outline-none bg-white">
                            <?php foreach ($plans as $p): ?>
                                <option value="<?= htmlspecialchars($p->package_key, ENT_QUOTES, 'UTF-8'); ?>" <?= $p->package_key === 'premium' ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p->name_bn, ENT_QUOTES, 'UTF-8'); ?> — <?= htmlspecialchars(taka($p->price_yearly), ENT_QUOTES, 'UTF-8'); ?>/বছর
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-neutral-700 mb-1" for="ob_end_date">
                            মেয়াদ শেষ হওয়ার নির্দিষ্ট তারিখ * <span class="text-[10px] font-normal text-neutral-400">/ End Date</span>
                        </label>
                        <input type="date" id="ob_end_date"
                               value="<?= date('Y-m-d', strtotime('+1 year')); ?>"
                               min="<?= date('Y-m-d'); ?>"
                               onchange="onObEndDateChange()"
                               class="w-full p-2.5 rounded-xl border border-neutral-300 text-xs font-mono focus:border-indigo-600 focus:outline-none">
                        <p id="ob_duration_summary" class="text-[10px] text-indigo-700 font-semibold mt-1">
                            আজ থেকে ৩৬৫ দিন (মেয়াদ: <?= date('d/m/Y', strtotime('+1 year')); ?>)
                        </p>
                    </div>
                </div>
            </div>

            <div id="onboardError" class="p-3 rounded-xl bg-red-50 text-red-700 border border-red-200 hidden"></div>
            <div id="onboardSuccess" class="p-3 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 hidden"></div>
        </div>

        <!-- Pinned Footer -->
        <div class="px-5 sm:px-7 py-3.5 border-t border-neutral-200 flex justify-end gap-2 shrink-0 bg-neutral-50/90 z-20">
            <button type="button" onclick="closeOnboardModal()" class="px-4 py-2 rounded-xl bg-neutral-100 hover:bg-neutral-200 text-neutral-700 font-bold cursor-pointer transition-colors">
                বাতিল / Cancel
            </button>
            <button type="submit" id="onboardSubmitBtn" class="px-5 py-2 rounded-xl bg-indigo-700 hover:bg-indigo-800 text-white font-bold cursor-pointer shadow-xs transition-colors">
                অনবোর্ড সম্পন্ন করুন / Onboard Tenant
            </button>
        </div>
    </form>
</div>
</div>

<!-- Modal: Edit Tenant Info & Domain -->
<div id="editModal" class="fixed inset-0 z-50 bg-neutral-900/60 backdrop-blur-xs hidden overflow-y-auto p-3 sm:p-6 justify-center items-start sm:items-center">
    <div class="relative bg-white rounded-3xl max-w-lg w-full my-auto shadow-2xl border border-neutral-200 flex flex-col max-h-[90vh] overflow-hidden">
        
        <!-- Pinned Header -->
        <div class="px-5 sm:px-7 py-4 border-b border-neutral-200 flex items-center justify-between shrink-0 bg-white z-20">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-indigo-50 border border-indigo-200 text-indigo-700 flex items-center justify-center font-bold text-lg shadow-xs">
                    <i class="ph ph-pencil-simple"></i>
                </div>
                <div>
                    <h3 class="text-base sm:text-lg font-bold text-neutral-900 leading-tight">ইউনিয়ন তথ্য ও ডোমেন সম্পাদনা</h3>
                    <p class="text-[11px] text-neutral-500 font-medium">Edit Tenant Details & Subscriptions</p>
                </div>
            </div>
            <button type="button" onclick="closeEditModal()" class="w-8 h-8 rounded-full bg-neutral-100 hover:bg-neutral-200 text-neutral-500 hover:text-neutral-800 flex items-center justify-center text-lg transition-colors cursor-pointer" aria-label="বন্ধ করুন">
                <i class="ph ph-x"></i>
            </button>
        </div>

        <form id="editForm" onsubmit="submitEdit(event)" class="flex flex-col flex-1 overflow-hidden min-h-0 m-0">
            <div class="p-5 sm:p-7 overflow-y-auto flex-1 space-y-4 text-xs">
            <input type="hidden" id="ed_tenant_id">

            <div>
                <label class="block font-bold text-neutral-700 mb-1">
                    ইউনিয়ন পরিষদের নাম <span class="text-[10px] font-normal text-neutral-400">/ Union Name</span>
                </label>
                <input type="text" id="ed_ins_name" required class="w-full p-2.5 rounded-xl border border-neutral-300 text-xs focus:border-indigo-600 focus:outline-none">
            </div>

            <div>
                <label class="block font-bold text-neutral-700 mb-1">
                    কাস্টম সরকারি ডোমেন (.gov.bd) <span class="text-[10px] font-normal text-neutral-400">/ Custom Gov Domain</span>
                </label>
                <input type="text" id="ed_custom_domain" placeholder="যেমন: baghmaraup.gov.bd" class="w-full p-2.5 rounded-xl border border-neutral-300 text-xs font-mono focus:border-indigo-600 focus:outline-none">
                <p class="text-[10px] text-neutral-500 mt-0.5">নতুন ডোমেন দিলে স্বয়ংক্রিয়ভাবে ডিএনএস রাউটিং তালিকায় যুক্ত হবে</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-neutral-700 mb-1">
                        অফিশিয়াল ইমেইল <span class="text-[10px] font-normal text-neutral-400">/ Official Email</span>
                    </label>
                    <input type="email" id="ed_ins_emails" class="w-full p-2.5 rounded-xl border border-neutral-300 text-xs focus:border-indigo-600 focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-neutral-700 mb-1">
                        সচিবের ফোন নম্বর <span class="text-[10px] font-normal text-neutral-400">/ Contact Phone</span>
                    </label>
                    <input type="text" id="ed_socib_phone" class="w-full p-2.5 rounded-xl border border-neutral-300 text-xs focus:border-indigo-600 focus:outline-none">
                </div>
            </div>

            <div class="pt-2 border-t border-neutral-100">
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block font-bold text-neutral-800">
                        সাবস্ক্রিপশন প্যাকেজ ও মেয়াদ <span class="text-[10px] font-normal text-neutral-400">/ Plan & Expiration</span>
                    </label>
                    <span class="text-[10px] text-neutral-400">তারিখ দ্রুত বৃদ্ধি করুন</span>
                </div>

                <!-- Quick add days buttons -->
                <div class="flex flex-wrap gap-1 mb-2">
                    <button type="button" onclick="addEdDays(30)" class="px-2 py-0.5 rounded border border-neutral-200 bg-neutral-50 hover:bg-indigo-50 text-[10px] font-medium text-neutral-700">
                        +৩০ দিন (1M)
                    </button>
                    <button type="button" onclick="addEdDays(90)" class="px-2 py-0.5 rounded border border-neutral-200 bg-neutral-50 hover:bg-indigo-50 text-[10px] font-medium text-neutral-700">
                        +৯০ দিন (3M)
                    </button>
                    <button type="button" onclick="addEdDays(180)" class="px-2 py-0.5 rounded border border-neutral-200 bg-neutral-50 hover:bg-indigo-50 text-[10px] font-medium text-neutral-700">
                        +১৮০ দিন (6M)
                    </button>
                    <button type="button" onclick="addEdDays(365)" class="px-2 py-0.5 rounded border border-neutral-200 bg-neutral-50 hover:bg-indigo-50 text-[10px] font-medium text-neutral-700">
                        +৩৬৫ দিন (1Y)
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-neutral-700 mb-1" for="ed_package">সাবস্ক্রিপশন প্যাকেজ</label>
                        <select id="ed_package" class="w-full p-2.5 rounded-xl border border-neutral-300 text-xs focus:border-indigo-600 focus:outline-none bg-white">
                            <option value="">— প্যাকেজ নেই —</option>
                            <?php foreach ($plans as $p): ?>
                                <option value="<?= htmlspecialchars($p->package_key, ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($p->name_bn, ENT_QUOTES, 'UTF-8'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-neutral-700 mb-1" for="ed_end_date">মেয়াদ শেষ হওয়ার তারিখ</label>
                        <input type="date" id="ed_end_date"
                               min="<?= date('Y-m-d', strtotime('-10 years')); ?>"
                               class="w-full p-2.5 rounded-xl border border-neutral-300 text-xs font-mono focus:border-indigo-600 focus:outline-none">
                    </div>
                </div>
            </div>

            <div id="editError" class="p-3 rounded-xl bg-red-50 text-red-700 border border-red-200 hidden"></div>
            <div id="editSuccess" class="p-3 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 hidden"></div>
        </div>

        <!-- Pinned Footer -->
        <div class="px-5 sm:px-7 py-3.5 border-t border-neutral-200 flex justify-end gap-2 shrink-0 bg-neutral-50/90 z-20">
            <button type="button" onclick="closeEditModal()" class="px-4 py-2 rounded-xl bg-neutral-100 hover:bg-neutral-200 text-neutral-700 font-bold cursor-pointer transition-colors">
                বাতিল / Cancel
            </button>
            <button type="submit" id="editSubmitBtn" class="px-5 py-2 rounded-xl bg-indigo-700 hover:bg-indigo-800 text-white font-bold cursor-pointer shadow-xs transition-colors">
                পরিবর্তন সংরক্ষণ করুন / Save Changes
            </button>
        </div>
    </form>
</div>
</div>

<!-- Modal: Temporary Delegated Support Admin (1 to 72 Hours) -->
<div id="tempAdminModal" class="fixed inset-0 z-50 bg-neutral-900/60 backdrop-blur-xs hidden overflow-y-auto p-3 sm:p-6 justify-center items-start sm:items-center">
    <div class="relative bg-white rounded-3xl max-w-2xl w-full my-auto shadow-2xl border border-neutral-200 flex flex-col max-h-[90vh] overflow-hidden">
        
        <!-- Fixed Header (Always visible, never cut off) -->
        <div class="px-5 sm:px-8 py-4 sm:py-5 border-b border-neutral-200 flex items-center justify-between shrink-0 bg-white z-20">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-200 text-indigo-700 flex items-center justify-center text-xl shadow-xs">
                    <i class="ph-bold ph-shield-check"></i>
                </div>
                <div>
                    <h3 class="text-base sm:text-lg font-bold text-neutral-900 leading-tight">সাময়িক সহায়তা এক্সেস</h3>
                    <p class="text-[11px] text-neutral-500 font-medium">Temporary Support Admin Delegation (১–৭২ ঘণ্টা)</p>
                </div>
            </div>
            <button type="button" onclick="closeTempAdminModal()" class="w-8 h-8 rounded-full bg-neutral-100 hover:bg-neutral-200 text-neutral-500 hover:text-neutral-800 flex items-center justify-center text-lg transition-colors cursor-pointer" aria-label="বন্ধ করুন">
                <i class="ph ph-x"></i>
            </button>
        </div>

        <!-- Scrollable Content Body -->
        <div class="p-5 sm:p-8 overflow-y-auto flex-1 space-y-4 text-xs">
            
            <!-- Tenant Identification Badge -->
            <div class="p-3 rounded-2xl bg-neutral-50 border border-neutral-200/80 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-xs font-bold text-neutral-800" id="tempModalTenantName">ইউনিয়ন পরিষদ</span>
                </div>
                <span class="font-mono text-[11px] font-bold px-2 py-0.5 rounded-md bg-white border border-neutral-300 text-neutral-700" id="tempModalTenantId">#0</span>
            </div>

            <!-- Privacy & Isolation Notice -->
            <div class="p-3 rounded-2xl bg-amber-50/70 border border-amber-200 text-[11px] text-amber-900 leading-relaxed flex items-start gap-2.5">
                <i class="ph-fill ph-warning-circle text-amber-600 text-base shrink-0 mt-0.5"></i>
                <div>
                    <strong>আইসোলেশন ও নিরাপত্তা নীতি:</strong> সুপার অ্যাডমিন হিসেবে আপনি সরাসরি কোনো টেন্যান্টের ড্যাশবোর্ডে প্রবেশ করতে পারেন না। কারিগরি সহায়তার জন্য এই সাময়িক এক্সেস তৈরি করা হচ্ছে। নির্দিষ্ট মেয়াদ শেষ হওয়ার সাথে সাথে একাউন্ট ও পাসওয়ার্ড <strong>স্বয়ংক্রিয়ভাবে পার্মানেন্ট ডিলিট</strong> হয়ে যাবে।
                </div>
            </div>

            <!-- Form: Create Temp Admin -->
            <form id="tempAdminForm" onsubmit="submitCreateTempAdmin(event)" class="space-y-4 text-xs">
                <input type="hidden" id="temp_tenant_id">

                <!-- Role Selection -->
                <div>
                    <label class="block font-bold text-neutral-700 mb-1.5">
                        অস্থায়ী রোল নির্বাচন করুন <span class="text-[10px] font-normal text-neutral-400">/ Delegated Role</span>
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="relative flex items-center gap-2.5 p-3 rounded-xl border border-neutral-300 bg-white hover:border-indigo-600 cursor-pointer transition-all has-checked:border-indigo-600 has-checked:bg-indigo-50/50 has-checked:text-indigo-900 shadow-2xs">
                            <input type="radio" name="temp_role" value="uphead" checked class="text-indigo-600 focus:ring-indigo-500">
                            <div>
                                <div class="font-bold text-xs text-neutral-800">ইউপি চেয়ারম্যান</div>
                                <div class="text-[10px] text-neutral-500">পূর্ণ প্রশাসনিক ও সনদ স্বাক্ষর ক্ষমতা</div>
                            </div>
                        </label>
                        <label class="relative flex items-center gap-2.5 p-3 rounded-xl border border-neutral-300 bg-white hover:border-indigo-600 cursor-pointer transition-all has-checked:border-indigo-600 has-checked:bg-indigo-50/50 has-checked:text-indigo-900 shadow-2xs">
                            <input type="radio" name="temp_role" value="upsocib" class="text-indigo-600 focus:ring-indigo-500">
                            <div>
                                <div class="font-bold text-xs text-neutral-800">ইউপি সচিব</div>
                                <div class="text-[10px] text-neutral-500">দাপ্তরিক, হোল্ডিং ও ক্যাশ ব্যবস্থাপনা</div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Duration Selector (1h to 72h) -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block font-bold text-neutral-700">
                            সময়সীমা / মেয়াদকাল (১ থেকে ৭২ ঘণ্টা) <span class="text-[10px] font-normal text-neutral-400">/ Access Duration</span>
                        </label>
                        <span id="tempDurationLabel" class="text-[11px] font-bold text-indigo-700 font-mono">২৪ ঘণ্টা</span>
                    </div>
                    <div class="grid grid-cols-3 sm:grid-cols-6 gap-1.5" id="temp_duration_presets">
                        <button type="button" onclick="setTempDuration(1, this)" class="temp-dur-btn py-1.5 rounded-lg border border-neutral-200 bg-neutral-50 hover:bg-indigo-50 hover:border-indigo-300 font-bold text-neutral-700 transition-all">১ ঘণ্টা</button>
                        <button type="button" onclick="setTempDuration(4, this)" class="temp-dur-btn py-1.5 rounded-lg border border-neutral-200 bg-neutral-50 hover:bg-indigo-50 hover:border-indigo-300 font-bold text-neutral-700 transition-all">৪ ঘণ্টা</button>
                        <button type="button" onclick="setTempDuration(12, this)" class="temp-dur-btn py-1.5 rounded-lg border border-neutral-200 bg-neutral-50 hover:bg-indigo-50 hover:border-indigo-300 font-bold text-neutral-700 transition-all">১২ ঘণ্টা</button>
                        <button type="button" onclick="setTempDuration(24, this)" class="temp-dur-btn active py-1.5 rounded-lg border border-indigo-600 bg-indigo-50 text-indigo-700 font-bold transition-all">২৪ ঘণ্টা</button>
                        <button type="button" onclick="setTempDuration(48, this)" class="temp-dur-btn py-1.5 rounded-lg border border-neutral-200 bg-neutral-50 hover:bg-indigo-50 hover:border-indigo-300 font-bold text-neutral-700 transition-all">৪৮ ঘণ্টা</button>
                        <button type="button" onclick="setTempDuration(72, this)" class="temp-dur-btn py-1.5 rounded-lg border border-neutral-200 bg-neutral-50 hover:bg-indigo-50 hover:border-indigo-300 font-bold text-neutral-700 transition-all">৭২ ঘণ্টা</button>
                    </div>
                    <input type="hidden" id="temp_duration_hours" value="24">
                </div>

                <!-- Reason / Note -->
                <div>
                    <label class="block font-bold text-neutral-700 mb-1">
                        সহায়তার কারণ / সাপোর্ট রেফারেন্স <span class="text-[10px] font-normal text-neutral-400">/ Purpose or Ticket Note</span>
                    </label>
                    <input type="text" id="temp_reason" required placeholder="যেমন: হোল্ডিং ট্যাক্স ডাটা সংশোধন ও ইউজার ট্রাবলশুটিং" class="w-full p-2.5 rounded-xl border border-neutral-300 text-xs focus:border-indigo-600 focus:outline-none">
                </div>

                <div id="tempAdminError" class="p-3 rounded-xl bg-red-50 text-red-700 border border-red-200 hidden"></div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeTempAdminModal()" class="px-4 py-2 rounded-xl bg-neutral-100 hover:bg-neutral-200 text-neutral-700 font-bold cursor-pointer transition-colors">
                        বন্ধ করুন
                    </button>
                    <button type="submit" id="tempCreateBtn" class="inline-flex items-center gap-1.5 px-5 py-2 rounded-xl bg-indigo-700 hover:bg-indigo-800 text-white font-bold cursor-pointer shadow-xs transition-colors">
                        <i class="ph-bold ph-key"></i>
                        <span>অস্থায়ী এক্সেস তৈরি করুন</span>
                    </button>
                </div>
            </form>

            <!-- Result Box: Newly Created Credentials -->
            <div id="tempResultBox" class="p-4 rounded-2xl bg-emerald-50/70 border border-emerald-300 hidden space-y-3">
                <div class="flex items-center justify-between border-b border-emerald-200/80 pb-2">
                    <div class="flex items-center gap-1.5 text-emerald-800 font-bold text-xs">
                        <i class="ph-fill ph-check-circle text-emerald-600 text-sm"></i>
                        <span>অস্থায়ী ক্রেডেনশিয়াল প্রস্তুত হয়েছে!</span>
                    </div>
                    <span class="text-[10px] text-emerald-700 font-mono bg-emerald-100 px-2 py-0.5 rounded font-bold" id="resDurationBadge">২৪ ঘণ্টা বৈধ</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                    <!-- Username -->
                    <div class="p-2.5 bg-white rounded-xl border border-emerald-200 flex items-center justify-between">
                        <div>
                            <div class="text-[10px] text-neutral-500 font-bold uppercase tracking-wider">ইউজারনেম</div>
                            <div class="font-mono font-bold text-neutral-900 select-all" id="resUsername">-</div>
                        </div>
                        <button type="button" onclick="copyValue('resUsername', this)" class="p-1.5 text-neutral-500 hover:text-emerald-700 rounded hover:bg-emerald-50 cursor-pointer" title="কপি করুন">
                            <i class="ph ph-copy text-sm"></i>
                        </button>
                    </div>

                    <!-- Password -->
                    <div class="p-2.5 bg-white rounded-xl border border-emerald-200 flex items-center justify-between">
                        <div>
                            <div class="text-[10px] text-neutral-500 font-bold uppercase tracking-wider">পাসওয়ার্ড</div>
                            <div class="font-mono font-bold text-neutral-900 select-all" id="resPassword">-</div>
                        </div>
                        <button type="button" onclick="copyValue('resPassword', this)" class="p-1.5 text-neutral-500 hover:text-emerald-700 rounded hover:bg-emerald-50 cursor-pointer" title="কপি করুন">
                            <i class="ph ph-copy text-sm"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between text-[11px] text-neutral-600 pt-1">
                    <div>মেয়াদ শেষ: <strong class="font-mono text-neutral-900" id="resExpiresAt">-</strong></div>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="copyAllCreds()" class="text-indigo-700 hover:underline font-bold cursor-pointer">
                            উভয় তথ্য কপি করুন
                        </button>
                        <a id="resLoginLink" href="#" target="_blank" rel="noopener" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs shadow-2xs transition-colors">
                            <span>লগইন পেজে যান</span>
                            <i class="ph ph-arrow-square-out text-xs"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Active Sessions Section -->
            <div class="pt-4 border-t border-neutral-200">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="font-bold text-neutral-800 text-xs flex items-center gap-1.5">
                        <i class="ph-bold ph-hourglass-high text-indigo-600"></i>
                        <span>চলমান অস্থায়ী সাপোর্ট সেশনসমূহ</span>
                    </h4>
                    <button type="button" onclick="refreshTempSessions()" class="text-[11px] text-indigo-700 hover:underline font-semibold flex items-center gap-1 cursor-pointer">
                        <i class="ph ph-arrows-clockwise text-xs"></i> রিফ্রেশ
                    </button>
                </div>

                <div id="activeSessionsContainer" class="space-y-2 text-xs">
                    <div class="text-neutral-400 text-center py-3 text-[11px]">লোড হচ্ছে...</div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- JavaScript Actions -->
<script>
    function filterTenants() {
        const query = document.getElementById('searchTenant').value.toLowerCase();
        const rows = document.querySelectorAll('.tenant-row');
        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(query) ? '' : 'none';
        });
    }

    // Helper: format YYYY-MM-DD
    function formatDateYMD(d) {
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    // Helper: format DD/MM/YYYY
    function formatDateDMY(d) {
        const day = String(d.getDate()).padStart(2, '0');
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const year = d.getFullYear();
        return `${day}/${month}/${year}`;
    }

    function setObPresetDuration(days, label, btn) {
        // Toggle active button style
        document.querySelectorAll('#ob_preset_group .ob-preset-btn').forEach(b => {
            b.classList.remove('active', 'border-indigo-600', 'bg-indigo-50', 'text-indigo-700', 'font-bold');
            b.classList.add('border-neutral-200', 'bg-neutral-50', 'text-neutral-700');
        });
        if (btn) {
            btn.classList.add('active', 'border-indigo-600', 'bg-indigo-50', 'text-indigo-700', 'font-bold');
            btn.classList.remove('border-neutral-200', 'bg-neutral-50', 'text-neutral-700');
        }

        document.getElementById('ob_custom_days_box').style.display = 'none';

        const target = new Date();
        target.setDate(target.getDate() + parseInt(days, 10));
        document.getElementById('ob_end_date').value = formatDateYMD(target);
        document.getElementById('ob_duration_summary').innerText = `আজ থেকে ${days} দিন (${label} — মেয়াদ: ${formatDateDMY(target)})`;
    }

    function toggleObCustomDays(btn) {
        document.querySelectorAll('#ob_preset_group .ob-preset-btn').forEach(b => {
            b.classList.remove('active', 'border-indigo-600', 'bg-indigo-50', 'text-indigo-700', 'font-bold');
            b.classList.add('border-neutral-200', 'bg-neutral-50', 'text-neutral-700');
        });
        if (btn) {
            btn.classList.add('active', 'border-indigo-600', 'bg-indigo-50', 'text-indigo-700', 'font-bold');
            btn.classList.remove('border-neutral-200', 'bg-neutral-50', 'text-neutral-700');
        }

        const box = document.getElementById('ob_custom_days_box');
        box.style.display = 'flex';
        const inp = document.getElementById('ob_custom_days_val');
        inp.focus();
        if (inp.value) {
            onObCustomDaysInput(inp.value);
        }
    }

    function onObCustomDaysInput(val) {
        const days = parseInt(val, 10);
        if (isNaN(days) || days <= 0) return;
        const target = new Date();
        target.setDate(target.getDate() + days);
        document.getElementById('ob_end_date').value = formatDateYMD(target);
        document.getElementById('ob_duration_summary').innerText = `আজ থেকে ${days} দিন (মেয়াদ: ${formatDateDMY(target)})`;
    }

    function onObEndDateChange() {
        const val = document.getElementById('ob_end_date').value;
        if (!val) return;
        const target = new Date(val);
        const today = new Date();
        today.setHours(0,0,0,0);
        const diffMs = target.getTime() - today.getTime();
        const diffDays = Math.round(diffMs / (1000 * 60 * 60 * 24));
        if (diffDays >= 0) {
            document.getElementById('ob_duration_summary').innerText = `আজ থেকে ${diffDays} দিন (মেয়াদ: ${formatDateDMY(target)})`;
        } else {
            document.getElementById('ob_duration_summary').innerText = `পূর্ববর্তী তারিখ নির্বাচন করা হয়েছে (${formatDateDMY(target)})`;
        }
    }

    function addEdDays(days) {
        const inp = document.getElementById('ed_end_date');
        let current = inp.value ? new Date(inp.value) : new Date();
        if (isNaN(current.getTime())) current = new Date();
        current.setDate(current.getDate() + parseInt(days, 10));
        inp.value = formatDateYMD(current);
    }

    function openOnboardModal() {
        document.getElementById('onboardError').classList.add('hidden');
        document.getElementById('onboardSuccess').classList.add('hidden');
        document.getElementById('onboardModal').classList.remove('hidden');
        document.getElementById('onboardModal').classList.add('flex');
    }

    function closeOnboardModal() {
        document.getElementById('onboardModal').classList.add('hidden');
        document.getElementById('onboardModal').classList.remove('flex');
    }

    function openEditModal(tenant) {
        document.getElementById('ed_tenant_id').value = tenant.ins_titute_p_idds;
        document.getElementById('ed_ins_name').value = tenant.ins_name || '';
        document.getElementById('ed_ins_emails').value = tenant.ins_emails || '';
        document.getElementById('ed_socib_phone').value = tenant.socib_phone || '';

        const sub = tenant.subscription || null;
        document.getElementById('ed_package').value  = sub ? (sub.package_key || '') : '';
        document.getElementById('ed_end_date').value = sub ? (sub.end_date || '') : (tenant.expire_times || '');
        
        // Find custom domain if any
        let customDom = '';
        if (tenant.domains && tenant.domains.length) {
            tenant.domains.forEach(d => {
                if (d.hostname.endsWith('.gov.bd') || (!d.hostname.endsWith('.localhost') && !d.hostname.endsWith('.local') && !d.hostname.includes('viretaup.com') && d.hostname !== 'localhost')) {
                    customDom = d.hostname;
                }
            });
        }
        document.getElementById('ed_custom_domain').value = customDom;

        document.getElementById('editError').classList.add('hidden');
        document.getElementById('editSuccess').classList.add('hidden');
        document.getElementById('editModal').classList.remove('hidden');
        document.getElementById('editModal').classList.add('flex');
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
        document.getElementById('editModal').classList.remove('flex');
    }

    /**
     * Suspend / reactivate a tenant.
     */
    async function toggleTenantActive(tenantId, currentlyActive, name) {
        const title = currentlyActive ? 'ইউনিয়ন স্থগিতকরণ' : 'ইউনিয়ন সক্রিয়করণ';
        const msg = currentlyActive
            ? `আপনি কি নিশ্চিত যে "${name}"-কে সাময়িকভাবে স্থগিত করতে চান?`
            : `আপনি কি নিশ্চিত যে "${name}"-কে পুনরায় সক্রিয় করতে চান?`;
        const confirmed = await vConfirm(title, msg, { danger: !!currentlyActive });
        if (!confirmed) return;

        try {
            const resp = await fetch('<?= base_url('sadmin_tenants/api_toggle_active'); ?>', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ tenant_id: tenantId })
            });
            const res = await resp.json();
            if (res.success) {
                window.location.reload();
            } else {
                alert(res.message || 'ত্রুটি ঘটেছে।');
            }
        } catch (e) {
            alert('সার্ভারে সংযোগ স্থাপন করা যায়নি।');
        }
    }

    async function submitOnboard(e) {
        e.preventDefault();
        const btn = document.getElementById('onboardSubmitBtn');
        const errBox = document.getElementById('onboardError');
        const succBox = document.getElementById('onboardSuccess');

        btn.disabled = true;
        btn.innerText = 'অনবোর্ডিং হচ্ছে...';
        errBox.classList.add('hidden');
        succBox.classList.add('hidden');

        const payload = {
            union_name: document.getElementById('ob_union_name').value,
            union_name_en: document.getElementById('ob_union_name_en').value,
            subdomain: document.getElementById('ob_subdomain').value,
            custom_domain: document.getElementById('ob_custom_domain').value,
            admin_email: document.getElementById('ob_admin_email').value,
            admin_phone: document.getElementById('ob_admin_phone').value,
            admin_name: document.getElementById('ob_admin_name').value,
            admin_password: document.getElementById('ob_admin_password').value,
            package: document.getElementById('ob_package').value,
            end_date: document.getElementById('ob_end_date').value
        };

        try {
            const resp = await fetch('<?= base_url('sadmin_tenants/api_create_tenant'); ?>', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(payload)
            });
            const res = await resp.json();
            if (res.success) {
                succBox.innerText = res.message;
                succBox.classList.remove('hidden');
                setTimeout(() => window.location.reload(), 1200);
            } else {
                errBox.innerText = res.message || 'ত্রুটি ঘটেছে।';
                errBox.classList.remove('hidden');
            }
        } catch(e) {
            errBox.innerText = 'সার্ভারে সংযোগ স্থাপন করা যায়নি।';
            errBox.classList.remove('hidden');
        } finally {
            btn.disabled = false;
            btn.innerText = 'অনবোর্ড সম্পন্ন করুন / Onboard Tenant';
        }
    }

    async function submitEdit(e) {
        e.preventDefault();
        const btn = document.getElementById('editSubmitBtn');
        const errBox = document.getElementById('editError');
        const succBox = document.getElementById('editSuccess');

        btn.disabled = true;
        btn.innerText = 'সংরক্ষণ হচ্ছে...';
        errBox.classList.add('hidden');
        succBox.classList.add('hidden');

        const payload = {
            tenant_id: document.getElementById('ed_tenant_id').value,
            ins_name: document.getElementById('ed_ins_name').value,
            ins_emails: document.getElementById('ed_ins_emails').value,
            socib_phone: document.getElementById('ed_socib_phone').value,
            custom_domain: document.getElementById('ed_custom_domain').value,
            package_key: document.getElementById('ed_package').value,
            end_date: document.getElementById('ed_end_date').value
        };

        try {
            const resp = await fetch('<?= base_url('sadmin_tenants/api_update_tenant'); ?>', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(payload)
            });
            const res = await resp.json();
            if (res.success) {
                succBox.innerText = res.message;
                succBox.classList.remove('hidden');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                errBox.innerText = res.message || 'ত্রুটি ঘটেছে।';
                errBox.classList.remove('hidden');
            }
        } catch(e) {
            errBox.innerText = 'সার্ভারে সংযোগ স্থাপন করা যায়নি।';
            errBox.classList.remove('hidden');
        } finally {
            btn.disabled = false;
            btn.innerText = 'পরিবর্তন সংরক্ষণ করুন / Save Changes';
        }
    }

    /* =========================================================================
     * Temporary Support Admin Management (1 to 72 Hours Delegated Access)
     * ========================================================================= */
    let currentTempTenant = null;

    function openTempAdminModal(tenant) {
        currentTempTenant = tenant;
        document.getElementById('temp_tenant_id').value = tenant.ins_titute_p_idds;
        document.getElementById('tempModalTenantName').innerText = tenant.ins_name || 'ইউনিয়ন পরিষদ';
        document.getElementById('tempModalTenantId').innerText = '#' + tenant.ins_titute_p_idds;
        document.getElementById('tempAdminError').classList.add('hidden');
        document.getElementById('tempResultBox').classList.add('hidden');
        document.getElementById('temp_reason').value = '';

        // Reset duration to 24h default
        setTempDuration(24, document.querySelector('#temp_duration_presets button.active'));

        document.getElementById('tempAdminModal').classList.remove('hidden');
        document.getElementById('tempAdminModal').classList.add('flex');

        loadTenantTempAdmins(tenant.ins_titute_p_idds);
    }

    function closeTempAdminModal() {
        document.getElementById('tempAdminModal').classList.add('hidden');
        document.getElementById('tempAdminModal').classList.remove('flex');
    }

    function setTempDuration(hours, btn) {
        document.getElementById('temp_duration_hours').value = hours;
        document.getElementById('tempDurationLabel').innerText = hours + ' ঘণ্টা';

        document.querySelectorAll('#temp_duration_presets .temp-dur-btn').forEach(b => {
            b.classList.remove('active', 'border-indigo-600', 'bg-indigo-50', 'text-indigo-700', 'font-bold');
            b.classList.add('border-neutral-200', 'bg-neutral-50', 'text-neutral-700');
        });
        if (btn) {
            btn.classList.add('active', 'border-indigo-600', 'bg-indigo-50', 'text-indigo-700', 'font-bold');
            btn.classList.remove('border-neutral-200', 'bg-neutral-50', 'text-neutral-700');
        }
    }

    async function submitCreateTempAdmin(e) {
        e.preventDefault();
        const btn = document.getElementById('tempCreateBtn');
        const errBox = document.getElementById('tempAdminError');
        const resBox = document.getElementById('tempResultBox');

        btn.disabled = true;
        btn.innerHTML = '<i class="ph ph-spinner animate-spin"></i> এক্সেস তৈরি হচ্ছে...';
        errBox.classList.add('hidden');
        resBox.classList.add('hidden');

        const roleKey = document.querySelector('input[name="temp_role"]:checked')?.value || 'uphead';
        const payload = {
            tenant_id: parseInt(document.getElementById('temp_tenant_id').value, 10),
            duration_hours: parseInt(document.getElementById('temp_duration_hours').value, 10),
            role_key: roleKey,
            reason: document.getElementById('temp_reason').value
        };

        try {
            const resp = await fetch('<?= base_url('sadmin_tenants/api_create_temp_admin'); ?>', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(payload)
            });
            const res = await resp.json();
            if (res.success && res.data) {
                const creds = res.data;
                document.getElementById('resUsername').innerText = creds.username;
                document.getElementById('resPassword').innerText = creds.plain_password;
                document.getElementById('resDurationBadge').innerText = creds.duration_hours + ' ঘণ্টা বৈধ (' + creds.role_title + ')';
                document.getElementById('resExpiresAt').innerText = creds.expires_at;
                document.getElementById('resLoginLink').href = creds.login_url;

                resBox.classList.remove('hidden');
                loadTenantTempAdmins(payload.tenant_id);
            } else {
                errBox.innerText = res.message || 'অস্থায়ী এক্সেস তৈরিতে ত্রুটি হয়েছে।';
                errBox.classList.remove('hidden');
            }
        } catch (err) {
            errBox.innerText = 'সার্ভারে সংযোগ স্থাপন করা যায়নি।';
            errBox.classList.remove('hidden');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="ph-bold ph-key"></i> <span>অস্থায়ী এক্সেস তৈরি করুন</span>';
        }
    }

    async function loadTenantTempAdmins(tenantId) {
        const container = document.getElementById('activeSessionsContainer');
        container.innerHTML = '<div class="text-neutral-400 text-center py-2 text-[11px]"><i class="ph ph-spinner animate-spin"></i> সেশন লোড হচ্ছে...</div>';

        try {
            const resp = await fetch('<?= base_url('sadmin_tenants/api_list_temp_admins?tenant_id='); ?>' + tenantId);
            const res = await resp.json();
            if (res.success && res.data && res.data.length > 0) {
                let html = '';
                res.data.forEach(item => {
                    const timeLeft = `${item.hours_left} ঘণ্টা ${item.minutes_left} মিনিট বাকি`;
                    html += `
                        <div class="p-3 bg-neutral-50 rounded-xl border border-neutral-200 flex items-center justify-between gap-2">
                            <div>
                                <div class="flex items-center gap-1.5 font-bold text-neutral-800">
                                    <span class="font-mono text-indigo-700">${escapeHtml(item.username)}</span>
                                    <span class="px-1.5 py-0.5 rounded bg-indigo-100 text-indigo-800 text-[10px] font-bold">${escapeHtml(item.role_title)}</span>
                                </div>
                                <div class="text-[10px] text-neutral-500 mt-0.5 flex items-center gap-2">
                                    <span>মেয়াদ: ${escapeHtml(item.expires_at)}</span>
                                    <span class="text-emerald-700 font-bold bg-emerald-50 px-1 rounded border border-emerald-200">${timeLeft}</span>
                                </div>
                                ${item.reason ? `<div class="text-[10px] text-neutral-600 mt-1 italic">কারণ: ${escapeHtml(item.reason)}</div>` : ''}
                            </div>
                            <button type="button" onclick="revokeTempAdmin(${item.id}, '${escapeHtml(item.username)}')" class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold text-[11px] shrink-0 cursor-pointer transition-colors" title="তাৎক্ষণিকভাবে সেশন বাতিল ও ইউজার ডিলিট করুন">
                                <i class="ph ph-trash"></i> বাতিল
                            </button>
                        </div>
                    `;
                });
                container.innerHTML = html;
            } else {
                container.innerHTML = '<div class="text-neutral-500 text-center py-3 text-[11px] bg-neutral-50 rounded-xl border border-dashed border-neutral-200">বর্তমানে কোনো চলমান সাময়িক সাপোর্ট সেশন নেই।</div>';
            }
        } catch (e) {
            container.innerHTML = '<div class="text-rose-500 text-center py-2 text-[11px]">সেশন লোড করা সম্ভব হয়নি।</div>';
        }
    }

    function refreshTempSessions() {
        if (currentTempTenant && currentTempTenant.ins_titute_p_idds) {
            loadTenantTempAdmins(currentTempTenant.ins_titute_p_idds);
        }
    }

    async function revokeTempAdmin(tempId, username) {
        const confirmed = await vConfirm('অস্থায়ী এক্সেস বাতিল', `"${username}" এর সাময়িক এক্সেস কি এখনই বাতিল ও পার্মানেন্টলি ডিলিট করতে চান?`, { danger: true });
        if (!confirmed) return;

        try {
            const resp = await fetch('<?= base_url('sadmin_tenants/api_revoke_temp_admin'); ?>', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ temp_id: tempId })
            });
            const res = await resp.json();
            if (res.success) {
                if (currentTempTenant) {
                    loadTenantTempAdmins(currentTempTenant.ins_titute_p_idds);
                }
            } else {
                alert(res.message || 'বাতিল করা যায়নি।');
            }
        } catch (e) {
            alert('সার্ভারে যোগাযোগ করা সম্ভব হয়নি।');
        }
    }

    function copyValue(elementId, btn) {
        const text = document.getElementById(elementId).innerText.trim();
        navigator.clipboard.writeText(text).then(() => {
            const originalIcon = btn.innerHTML;
            btn.innerHTML = '<i class="ph-bold ph-check text-emerald-600 text-sm"></i>';
            setTimeout(() => btn.innerHTML = originalIcon, 1500);
        });
    }

    function copyAllCreds() {
        const u = document.getElementById('resUsername').innerText.trim();
        const p = document.getElementById('resPassword').innerText.trim();
        const link = document.getElementById('resLoginLink').href;
        const text = `অস্থায়ী সহায়তা এক্সেস:\nইউজারনেম: ${u}\nপাসওয়ার্ড: ${p}\nলগইন ইউআরএল: ${link}`;
        navigator.clipboard.writeText(text).then(() => {
            alert('ক্রেডেনশিয়াল সফলভাবে ক্লিপবোর্ডে কপি করা হয়েছে!');
        });
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
</script>
