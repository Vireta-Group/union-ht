<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$pending_list   = $pending_queue ?? array();
$history_list   = $recent_decisions ?? array();
$pending_count  = count($pending_list);
$history_count  = count($history_list);
$can_decide     = !empty($can_decide_role);
$my_id          = $current_auth_id ?? 0;

function format_action_title($action) {
    switch ($action) {
        case 'tenants.update':
            return 'সিস্টেম সেটিংস পরিবর্তন';
        case 'deposits.create':
            return 'ক্যাশ ডিপোজিট জমা';
        case 'sonod.edit':
            return 'সনদ সম্পাদনা অনুমোদন';
        default:
            return htmlspecialchars($action, ENT_QUOTES, 'UTF-8');
    }
}
?>

<!-- Header & Breadcrumb -->
<div class="space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-neutral-200">
        <div>
            <nav class="flex items-center gap-2 text-xs text-neutral-500 mb-1">
                <a href="dashboard" class="hover:text-indigo-600 transition flex items-center gap-1">
                    <i class="ph ph-house text-sm"></i> ড্যাশবোর্ড
                </a>
                <i class="ph ph-caret-right text-[10px] text-neutral-400"></i>
                <span class="text-neutral-800 font-medium">অনুমোদন ব্যবস্থাপনা</span>
            </nav>
            <h1 class="text-xl sm:text-2xl font-bold text-neutral-900 tracking-tight flex items-center gap-2">
                <span class="w-2.5 h-6 bg-indigo-600 rounded-full inline-block"></span>
                অনুমোদন ও পর্যালোচনা ব্যবস্থাপনা
            </h1>
            <p class="text-xs sm:text-sm text-neutral-500 mt-0.5">
                সচিব বা আদায়কারী কর্তৃক প্রস্তাবিত সিস্টেম সেটিংস এবং ক্যাশ ডিপোজিট পর্যালোচনা ও অনুমোদন করুন
            </p>
        </div>

        <!-- Quick Stats Badges -->
        <div class="flex items-center gap-3">
            <div class="px-4 py-2 rounded-2xl bg-amber-50 border border-amber-200/80 flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-amber-500 text-white flex items-center justify-center font-bold text-sm shadow-xs">
                    <i class="ph-bold ph-hourglass-medium"></i>
                </div>
                <div>
                    <span class="block text-[11px] font-semibold text-amber-700 uppercase tracking-wider">অপেক্ষমান</span>
                    <strong class="text-base font-extrabold text-amber-900 leading-tight" id="headerPendingCount">
                        <?= bn_num($pending_count); ?> টি
                    </strong>
                </div>
            </div>

            <div class="px-4 py-2 rounded-2xl bg-emerald-50 border border-emerald-200/80 flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold text-sm shadow-xs">
                    <i class="ph-bold ph-check-circle"></i>
                </div>
                <div>
                    <span class="block text-[11px] font-semibold text-emerald-700 uppercase tracking-wider">ইতিহাস</span>
                    <strong class="text-base font-extrabold text-emerald-900 leading-tight">
                        <?= bn_num($history_count); ?> টি
                    </strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Notice Card -->
    <?php if (!$can_decide): ?>
    <div class="p-4 rounded-2xl bg-sky-50 border border-sky-200 text-sky-900 text-xs sm:text-sm flex items-start gap-3">
        <i class="ph-bold ph-info text-sky-600 text-lg mt-0.5 shrink-0"></i>
        <div>
            <strong class="font-bold">সচেতনতা বার্তা:</strong> 
            আপনি প্রস্তাবকারীর ভূমিকায় রয়েছেন। নতুন সেটিংস পরিবর্তন বা ক্যাশ ডিপোজিটের প্রস্তাব জমা দিলে তা চেয়ারম্যান মহোদয়ের অনুমোদনের জন্য এখানে তালিকাভুক্ত হবে।
        </div>
    </div>
    <?php endif; ?>

    <!-- Tabs Navigation -->
    <div class="flex items-center gap-2 border-b border-neutral-200 text-sm">
        <button type="button" onclick="switchApprovalTab('pending')" id="tabBtnPending" class="px-4 py-2.5 font-bold text-indigo-600 border-b-2 border-indigo-600 flex items-center gap-2 transition-colors cursor-pointer">
            <i class="ph-bold ph-clock text-base"></i>
            <span>অপেক্ষমান প্রস্তাবসমূহ</span>
            <span class="px-2 py-0.5 rounded-full text-xs font-extrabold bg-amber-500 text-white" id="tabBadgePending">
                <?= bn_num($pending_count); ?>
            </span>
        </button>
        <button type="button" onclick="switchApprovalTab('history')" id="tabBtnHistory" class="px-4 py-2.5 font-medium text-neutral-500 hover:text-neutral-800 flex items-center gap-2 transition-colors cursor-pointer">
            <i class="ph-bold ph-clock-counter-clockwise text-base"></i>
            <span>বিগত সিদ্ধান্তের রেকর্ড</span>
            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-neutral-200 text-neutral-700">
                <?= bn_num($history_count); ?>
            </span>
        </button>
    </div>

    <!-- TAB 1: PENDING PROPOSALS -->
    <div id="tabContentPending" class="space-y-4">
        <?php if (empty($pending_list)): ?>
            <div class="bg-white rounded-2xl border border-neutral-200/80 shadow-xs p-12 text-center space-y-3" id="emptyPendingState">
                <div class="w-16 h-16 mx-auto rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-3xl">
                    <i class="ph-bold ph-seal-check"></i>
                </div>
                <h3 class="text-base font-bold text-neutral-900">বর্তমানে কোনো অপেক্ষমান অনুমোদনের আবেদন নেই</h3>
                <p class="text-xs sm:text-sm text-neutral-500 max-w-md mx-auto">
                    সকল প্রস্তাব ও আবেদনের সিদ্ধান্ত গ্রহণ সম্পন্ন হয়েছে। নতুন কোনো প্রস্তাব এলে তা এখানে প্রদর্শিত হবে।
                </p>
            </div>
        <?php else: ?>
            <div class="space-y-3" id="pendingProposalsList">
                <?php foreach ($pending_list as $item): ?>
                    <?php 
                        $is_self = ((int)$item->requested_by === (int)$my_id);
                        $payload = is_string($item->payload) ? json_decode($item->payload, TRUE) : (array)$item->payload;
                    ?>
                    <div class="bg-white rounded-2xl border border-neutral-200/80 shadow-xs p-5 transition hover:border-neutral-300" id="approval-card-<?= (int)$item->id; ?>">
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                            
                            <!-- Left Details -->
                            <div class="space-y-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold <?= $item->action === 'deposits.create' ? 'bg-emerald-100 text-emerald-800' : 'bg-indigo-100 text-indigo-800'; ?>">
                                        <i class="ph-bold <?= $item->action === 'deposits.create' ? 'ph-bank' : 'ph-sliders-horizontal'; ?> mr-1"></i>
                                        <?= format_action_title($item->action); ?>
                                    </span>
                                    <span class="px-2 py-0.5 rounded-md bg-neutral-100 text-neutral-600 text-xs font-medium">
                                        আইডি: #<?= (int)$item->id; ?>
                                    </span>
                                    <span class="text-xs text-neutral-400 flex items-center gap-1">
                                        <i class="ph ph-calendar"></i>
                                        <?= bn_num(date('d M Y, h:i A', strtotime($item->requested_at))); ?>
                                    </span>
                                </div>

                                <div class="flex items-center gap-2 text-xs sm:text-sm text-neutral-700">
                                    <span class="text-neutral-400">প্রস্তাবক:</span>
                                    <strong class="font-semibold text-neutral-900"><?= htmlspecialchars($item->requester_name ?? 'ইউজার', ENT_QUOTES, 'UTF-8'); ?></strong>
                                    <?php if (!empty($item->requester_email)): ?>
                                        <span class="text-neutral-400">(<?= htmlspecialchars($item->requester_email, ENT_QUOTES, 'UTF-8'); ?>)</span>
                                    <?php endif; ?>
                                </div>

                                <?php if (!empty($item->note)): ?>
                                    <div class="text-xs bg-neutral-50 rounded-xl p-2.5 border border-neutral-200/60 text-neutral-700">
                                        <span class="font-bold text-neutral-900">প্রস্তাবকের নোট:</span>
                                        <?= htmlspecialchars($item->note, ENT_QUOTES, 'UTF-8'); ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Payload Preview -->
                                <div class="mt-2">
                                    <details class="text-xs text-neutral-600 group">
                                        <summary class="cursor-pointer font-semibold text-indigo-600 hover:text-indigo-800 select-none flex items-center gap-1">
                                            <i class="ph-bold ph-caret-right group-open:rotate-90 transition-transform"></i>
                                            <span>প্রস্তাবিত তথ্যের বিস্তারিত দেখুন</span>
                                        </summary>
                                        <div class="mt-2 p-3 bg-neutral-50 rounded-xl border border-neutral-200/80 space-y-1.5 overflow-x-auto max-w-full">
                                            <?php if ($item->action === 'deposits.create'): ?>
                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                                                    <div><strong>ডিপোজিট আইডি:</strong> #<?= htmlspecialchars((string)($payload['deposit_id'] ?? '—'), ENT_QUOTES, 'UTF-8'); ?></div>
                                                    <div><strong>জমার পরিমাণ:</strong> &#2547; <?= bn_num(number_format((float)($payload['amount'] ?? 0))); ?>/-</div>
                                                    <div><strong>ব্যাংক / মেথড:</strong> <?= htmlspecialchars($payload['method'] ?? 'ক্যাশ', ENT_QUOTES, 'UTF-8'); ?></div>
                                                    <div><strong>তারিখ:</strong> <?= htmlspecialchars($payload['date'] ?? date('Y-m-d'), ENT_QUOTES, 'UTF-8'); ?></div>
                                                </div>
                                            <?php elseif ($item->action === 'sonod.edit'): ?>
                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                                                    <div><strong>সনদ আইডি:</strong> <span class="font-mono text-indigo-700">#<?= htmlspecialchars((string)($payload['sonod_no'] ?? $payload['sonod_uniqs'] ?? '—'), ENT_QUOTES, 'UTF-8'); ?></span></div>
                                                    <div><strong>সনদের শিরোনাম:</strong> <?= htmlspecialchars((string)($payload['sonod_title'] ?? '—'), ENT_QUOTES, 'UTF-8'); ?></div>
                                                    <div><strong>নাগরিকের নাম:</strong> <?= htmlspecialchars((string)($payload['citizen_name'] ?? '—'), ENT_QUOTES, 'UTF-8'); ?></div>
                                                    <div><strong>আবেদনের কারণ:</strong> <?= htmlspecialchars((string)($payload['reason'] ?? $item->note ?? 'তথ্য সংশোধন প্রয়োজন'), ENT_QUOTES, 'UTF-8'); ?></div>
                                                </div>
                                            <?php elseif ($item->action === 'tenants.update'): ?>
                                                <div class="space-y-1 text-xs">
                                                    <?php foreach ($payload as $fKey => $fVal): ?>
                                                        <div class="flex items-start gap-2 py-0.5 border-b border-neutral-200/40">
                                                            <span class="font-semibold text-neutral-700 min-w-32"><?= htmlspecialchars((string)$fKey, ENT_QUOTES, 'UTF-8'); ?>:</span>
                                                            <span class="text-neutral-900 break-all"><?= htmlspecialchars((string)$fVal, ENT_QUOTES, 'UTF-8'); ?></span>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php else: ?>
                                                <pre class="text-[11px] font-mono text-neutral-800 whitespace-pre-wrap"><?= htmlspecialchars(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?></pre>
                                            <?php endif; ?>
                                        </div>
                                    </details>
                                </div>
                            </div>

                            <!-- Right: Decision Actions -->
                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 lg:shrink-0 pt-2 lg:pt-0 border-t lg:border-t-0 border-neutral-100">
                                <?php if ($can_decide): ?>
                                    <?php if ($is_self): ?>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <div class="px-2.5 py-1.5 rounded-xl bg-amber-50 text-amber-800 text-xs font-semibold border border-amber-200 text-center flex items-center justify-center gap-1" title="নিরাপত্তা বিধিমতে নিজের তৈরি প্রস্তাব নিজে অনুমোদন করা যায় না">
                                                <i class="ph-bold ph-shield-warning text-sm"></i>
                                                <span>স্ব-অনুমোদন নিষিদ্ধ</span>
                                            </div>
                                            <button type="button" onclick="openDecisionModal(<?= (int)$item->id; ?>, 'cancelled', '<?= htmlspecialchars(format_action_title($item->action), ENT_QUOTES, 'UTF-8'); ?>')" class="px-3.5 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs border border-rose-200 flex items-center justify-center gap-1 transition cursor-pointer shadow-2xs" title="নিজের তৈরি প্রস্তাবটি প্রত্যাহার বা বাতিল করুন">
                                                <i class="ph-bold ph-trash text-sm"></i>
                                                <span>প্রস্তাব বাতিল / মুছুন</span>
                                            </button>
                                        </div>
                                    <?php else: ?>
                                        <button type="button" onclick="openDecisionModal(<?= (int)$item->id; ?>, 'approved', '<?= htmlspecialchars(format_action_title($item->action), ENT_QUOTES, 'UTF-8'); ?>')" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs sm:text-sm flex items-center justify-center gap-1.5 shadow-xs transition-colors cursor-pointer">
                                            <i class="ph-bold ph-check text-base"></i>
                                            <span>অনুমোদন করুন</span>
                                        </button>
                                        <button type="button" onclick="openDecisionModal(<?= (int)$item->id; ?>, 'rejected', '<?= htmlspecialchars(format_action_title($item->action), ENT_QUOTES, 'UTF-8'); ?>')" class="px-3.5 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs sm:text-sm border border-rose-200 flex items-center justify-center gap-1.5 transition-colors cursor-pointer">
                                            <i class="ph-bold ph-x text-base"></i>
                                            <span>বাতিল করুন</span>
                                        </button>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="px-3 py-1.5 rounded-xl bg-neutral-100 text-neutral-600 text-xs font-semibold flex items-center justify-center gap-1.5">
                                        <i class="ph-bold ph-hourglass text-sm"></i>
                                        <span>চেয়ারম্যান মহোদয়ের অনুমোদনের অপেক্ষায়</span>
                                    </div>
                                <?php endif; ?>
                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- TAB 2: DECISION HISTORY -->
    <div id="tabContentHistory" class="hidden space-y-4">
        <?php if (empty($history_list)): ?>
            <div class="bg-white rounded-2xl border border-neutral-200/80 shadow-xs p-12 text-center space-y-2">
                <i class="ph-bold ph-archive text-3xl text-neutral-400"></i>
                <h3 class="text-base font-bold text-neutral-700">কোনো পূর্ববর্তী রেকর্ড পাওয়া যায়নি</h3>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-2xl border border-neutral-200/80 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs sm:text-sm">
                        <thead class="bg-neutral-50 text-neutral-600 border-b border-neutral-200 font-semibold uppercase text-[11px] tracking-wider">
                            <tr>
                                <th class="px-4 py-3">আইডি</th>
                                <th class="px-4 py-3">কাজের বিষয়</th>
                                <th class="px-4 py-3">প্রস্তাবক</th>
                                <th class="px-4 py-3">সিদ্ধান্ত গ্রহণকারী</th>
                                <th class="px-4 py-3">সিদ্ধান্তের তারিখ</th>
                                <th class="px-4 py-3">অবস্থা</th>
                                <th class="px-4 py-3">মন্তব্য</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100">
                            <?php foreach ($history_list as $h): ?>
                                <tr class="hover:bg-neutral-50/60 transition-colors">
                                    <td class="px-4 py-3 font-semibold text-neutral-900">#<?= (int)$h->id; ?></td>
                                    <td class="px-4 py-3 font-medium text-neutral-800"><?= format_action_title($h->action); ?></td>
                                    <td class="px-4 py-3 text-neutral-700">
                                        <?= htmlspecialchars($h->requester_name ?? '—', ENT_QUOTES, 'UTF-8'); ?>
                                    </td>
                                    <td class="px-4 py-3 text-neutral-700">
                                        <?= htmlspecialchars($h->decider_name ?? '—', ENT_QUOTES, 'UTF-8'); ?>
                                    </td>
                                    <td class="px-4 py-3 text-neutral-500 text-xs">
                                        <?= $h->decided_at ? bn_num(date('d M Y, h:i A', strtotime($h->decided_at))) : '—'; ?>
                                    </td>
                                    <td class="px-4 py-3">
                                        <?php if ($h->status === 'approved'): ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                                <i class="ph-bold ph-check text-xs"></i> অনুমোদিত
                                            </span>
                                        <?php elseif ($h->status === 'rejected'): ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800">
                                                <i class="ph-bold ph-x text-xs"></i> বাতিলকৃত
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-neutral-100 text-neutral-700">
                                                <?= htmlspecialchars($h->status, ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-neutral-600 max-w-xs truncate" title="<?= htmlspecialchars($h->note ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                        <?= !empty($h->note) ? htmlspecialchars($h->note, ENT_QUOTES, 'UTF-8') : '—'; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- CUSTOM TOAST NOTIFICATION -->
<div id="customToast" style="display: none;" class="fixed top-6 right-6 z-100 max-w-sm w-full bg-white rounded-2xl shadow-2xl border p-4 items-start gap-3 transition-all duration-300">
    <div id="toastIconContainer" class="p-2 rounded-xl shrink-0 flex items-center justify-center"></div>
    <div class="flex-1 min-w-0 pt-0.5">
        <h4 id="toastTitle" class="text-xs font-bold text-neutral-900"></h4>
        <p id="toastMessage" class="text-xs text-neutral-600 mt-0.5 leading-relaxed"></p>
    </div>
    <button type="button" onclick="hideToast()" class="text-neutral-400 hover:text-neutral-600 p-1 rounded-lg transition-colors cursor-pointer">
        <i class="ph-bold ph-x text-sm"></i>
    </button>
</div>

<!-- DECISION CONFIRMATION MODAL -->
<div id="decisionModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto bg-neutral-900/60 backdrop-blur-xs items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 space-y-4 shadow-2xl border border-neutral-200 transform transition-all">
        
        <div class="flex items-center justify-between border-b border-neutral-100 pb-3">
            <h3 class="text-base font-bold text-neutral-900 flex items-center gap-2" id="modalTitle">
                <i class="ph-bold ph-seal-check text-indigo-600 text-lg"></i>
                <span>সিদ্ধান্ত নিশ্চিতকরণ</span>
            </h3>
            <button type="button" onclick="closeDecisionModal()" class="text-neutral-400 hover:text-neutral-700 p-1 rounded-lg">
                <i class="ph-bold ph-x text-base"></i>
            </button>
        </div>

        <div class="space-y-3">
            <p class="text-xs sm:text-sm text-neutral-600 leading-relaxed" id="modalDescription">
                আপনি কি এই প্রস্তাবটিতে আপনার চূড়ান্ত সিদ্ধান্ত প্রদান করতে চান?
            </p>

            <div id="modalNoteFieldContainer">
                <label class="block text-xs font-bold text-neutral-700 mb-1">
                    মন্তব্য বা কারণ <span class="font-normal text-neutral-400">(ঐচ্ছিক)</span>
                </label>
                <textarea id="modalDecisionNote" rows="3" class="w-full px-3 py-2 rounded-xl border border-neutral-300 text-xs sm:text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-hidden" placeholder="প্রয়োজনে কারণ বা মন্তব্য লিখুন..."></textarea>
            </div>
        </div>

        <div class="flex items-center justify-end gap-2 pt-2 border-t border-neutral-100">
            <button type="button" onclick="closeDecisionModal()" class="px-4 py-2 rounded-xl bg-neutral-100 hover:bg-neutral-200 text-neutral-700 font-semibold text-xs sm:text-sm transition-colors cursor-pointer">
                বন্ধ করুন
            </button>
            <button type="button" id="btnConfirmDecision" onclick="submitDecision()" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs sm:text-sm shadow-xs transition-colors cursor-pointer flex items-center gap-1.5">
                <i class="ph-bold ph-check text-sm"></i>
                <span id="btnConfirmText">নিশ্চিত করুন</span>
            </button>
        </div>

    </div>
</div>

<script>
let currentTargetId = null;
let currentTargetDecision = null;
let toastTimer = null;

function showToast(message, type = 'success', title = '') {
    const toast = document.getElementById('customToast');
    const toastTitle = document.getElementById('toastTitle');
    const toastMessage = document.getElementById('toastMessage');
    const iconContainer = document.getElementById('toastIconContainer');

    if (!toast) return;

    if (toastTimer) clearTimeout(toastTimer);

    if (type === 'success') {
        toast.className = 'fixed top-6 right-6 z-100 max-w-sm w-full bg-white rounded-2xl shadow-2xl border border-emerald-200 p-4 flex items-start gap-3 transition-all duration-300';
        iconContainer.className = 'p-2 rounded-xl shrink-0 flex items-center justify-center bg-emerald-50 text-emerald-600';
        iconContainer.innerHTML = '<i class="ph-bold ph-check-circle text-lg"></i>';
        toastTitle.innerText = title || 'সফল হয়েছে!';
        toastTitle.className = 'text-xs font-bold text-emerald-950';
    } else {
        toast.className = 'fixed top-6 right-6 z-100 max-w-sm w-full bg-white rounded-2xl shadow-2xl border border-rose-200 p-4 flex items-start gap-3 transition-all duration-300';
        iconContainer.className = 'p-2 rounded-xl shrink-0 flex items-center justify-center bg-rose-50 text-rose-600';
        iconContainer.innerHTML = '<i class="ph-bold ph-warning-circle text-lg"></i>';
        toastTitle.innerText = title || 'ত্রুটি ঘটেছে!';
        toastTitle.className = 'text-xs font-bold text-rose-950';
    }

    toastMessage.innerText = message;
    toast.style.display = 'flex';

    toastTimer = setTimeout(() => {
        hideToast();
    }, 4000);
}

function hideToast() {
    const toast = document.getElementById('customToast');
    if (toast) {
        toast.style.display = 'none';
    }
}

function switchApprovalTab(tab) {
    const btnPending = document.getElementById('tabBtnPending');
    const btnHistory = document.getElementById('tabBtnHistory');
    const contentPending = document.getElementById('tabContentPending');
    const contentHistory = document.getElementById('tabContentHistory');

    if (tab === 'pending') {
        btnPending.className = 'px-4 py-2.5 font-bold text-indigo-600 border-b-2 border-indigo-600 flex items-center gap-2 transition-colors cursor-pointer';
        btnHistory.className = 'px-4 py-2.5 font-medium text-neutral-500 hover:text-neutral-800 flex items-center gap-2 transition-colors cursor-pointer';
        contentPending.classList.remove('hidden');
        contentHistory.classList.add('hidden');
    } else {
        btnHistory.className = 'px-4 py-2.5 font-bold text-indigo-600 border-b-2 border-indigo-600 flex items-center gap-2 transition-colors cursor-pointer';
        btnPending.className = 'px-4 py-2.5 font-medium text-neutral-500 hover:text-neutral-800 flex items-center gap-2 transition-colors cursor-pointer';
        contentHistory.classList.remove('hidden');
        contentPending.classList.add('hidden');
    }
}

function openDecisionModal(id, decision, actionTitle) {
    currentTargetId = id;
    currentTargetDecision = decision;

    const modal = document.getElementById('decisionModal');
    const title = document.getElementById('modalTitle');
    const desc = document.getElementById('modalDescription');
    const btn = document.getElementById('btnConfirmDecision');
    const btnText = document.getElementById('btnConfirmText');
    const note = document.getElementById('modalDecisionNote');
    note.value = '';

    if (decision === 'approved') {
        title.innerHTML = '<i class="ph-bold ph-check-circle text-emerald-600 text-lg"></i> <span>প্রস্তাব অনুমোদন নিশ্চিতকরণ</span>';
        desc.innerHTML = `আপনি কি <strong>"${actionTitle}"</strong> (আইডি #${id}) প্রস্তাবটি <strong>অনুমোদন</strong> করতে চান? এটি সাথে সাথে ডাটাবেজে কার্যকর হবে।`;
        btn.className = 'px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs sm:text-sm shadow-xs transition-colors cursor-pointer flex items-center gap-1.5';
        btnText.innerText = 'অনুমোদন নিশ্চিত করুন';
    } else if (decision === 'rejected') {
        title.innerHTML = '<i class="ph-bold ph-x-circle text-rose-600 text-lg"></i> <span>প্রস্তাব বাতিল নিশ্চিতকরণ</span>';
        desc.innerHTML = `আপনি কি <strong>"${actionTitle}"</strong> (আইডি #${id}) প্রস্তাবটি <strong>বাতিল</strong> করতে চান?`;
        btn.className = 'px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs sm:text-sm shadow-xs transition-colors cursor-pointer flex items-center gap-1.5';
        btnText.innerText = 'বাতিল নিশ্চিত করুন';
    } else if (decision === 'cancelled') {
        title.innerHTML = '<i class="ph-bold ph-trash text-rose-600 text-lg"></i> <span>প্রস্তাব প্রত্যাহার / মুছে ফেলা</span>';
        desc.innerHTML = `আপনি কি নিশ্চিত যে নিজের তৈরি <strong>"${actionTitle}"</strong> (আইডি #${id}) প্রস্তাবটি <strong>প্রত্যাহার বা বাতিল</strong> করতে চান? এটি অপেক্ষমান তালিকা থেকে অপসারিত হবে।`;
        btn.className = 'px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs sm:text-sm shadow-xs transition-colors cursor-pointer flex items-center gap-1.5';
        btnText.innerText = 'হ্যাঁ, প্রত্যাহার করুন';
    }

    modal.style.display = 'flex';
}

function closeDecisionModal() {
    const modal = document.getElementById('decisionModal');
    if (modal) {
        modal.style.display = 'none';
    }
    currentTargetId = null;
    currentTargetDecision = null;
}

function submitDecision() {
    if (!currentTargetId || !currentTargetDecision) return;

    const note = document.getElementById('modalDecisionNote').value.trim();
    const btn = document.getElementById('btnConfirmDecision');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="ph-bold ph-spinner animate-spin"></i> প্রক্রিয়াধীন...';

    const formData = new FormData();
    formData.append('id', currentTargetId);
    formData.append('decision', currentTargetDecision);
    formData.append('note', note || (currentTargetDecision === 'cancelled' ? 'প্রস্তাবক কর্তৃক প্রত্যাহারকৃত' : ''));

    fetch('approval_action', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        closeDecisionModal();

        if (res.ok) {
            showToast(res.msg, 'success', 'সফল হয়েছে');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showToast(res.msg || 'অনুমোদনের সময় ত্রুটি ঘটেছে।', 'error', 'ব্যর্থ হয়েছে');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        closeDecisionModal();
        showToast('সার্ভারের সাথে সংযোগ বিচ্ছিন্ন হয়েছে। আবার চেষ্টা করুন।', 'error', 'সংযোগ ত্রুটি');
    });
}
</script>
