<div class="space-y-6">

    <!-- Flash Messages -->
    <?php if ($this->session->flashdata('success')): ?>
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm font-semibold flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2.5">
                <i class="ph-bold ph-check-circle text-lg text-emerald-600"></i>
                <span><?= htmlspecialchars($this->session->flashdata('success'), ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 cursor-pointer text-base">&times;</button>
        </div>
    <?php endif; ?>

    <!-- Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-neutral-200/80">
        <div>
            <nav class="flex items-center gap-2 text-xs text-neutral-400 mb-1">
                <a href="dashboard" class="hover:text-indigo-600 transition-colors flex items-center gap-1">
                    <i class="ph-bold ph-gauge"></i> ড্যাশবোর্ড
                </a>
                <span>/</span>
                <a href="sonod_list" class="hover:text-indigo-600 transition-colors">সনদ ব্যবস্থাপনা</a>
                <span>/</span>
                <span class="text-indigo-600 font-semibold">সনদ অডিট লগ</span>
            </nav>
            <h1 class="text-xl sm:text-2xl font-black text-neutral-900 tracking-tight flex items-center gap-2">
                <span class="w-2.5 h-6 bg-amber-500 rounded-full inline-block"></span>
                সনদপত্র অডিট ও এক্টিভিটি লগ (Audit Trail)
            </h1>
            <p class="text-xs sm:text-sm text-neutral-500 mt-0.5">
                ইউনিয়ন পরিষদে সনদ তৈরি, সংশোধন, অনুমোদন ও অপসারণের অপরিবর্তনযোগ্য ইতিহাস ও নিরাপত্তা ট্র্যাকিং
            </p>
        </div>
        
        <div class="flex items-center gap-2">
            <a href="sonod_list" class="px-3.5 py-2 rounded-xl bg-white border border-neutral-300 hover:bg-neutral-50 text-neutral-700 font-bold text-xs flex items-center gap-1.5 shadow-2xs transition-all">
                <i class="ph-bold ph-list-dashes text-indigo-600"></i>
                <span>সনদ তালিকা</span>
            </a>
            <a href="newSonod" class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs flex items-center gap-1.5 shadow-2xs transition-all">
                <i class="ph-bold ph-plus-circle"></i>
                <span>নতুন সনদ</span>
            </a>
        </div>
    </div>

    <!-- 4 Stats / KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Logs -->
        <div class="p-4 sm:p-5 rounded-2xl bg-white border border-neutral-200/90 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-neutral-400 uppercase tracking-wider block">মোট অডিট রেকর্ড</span>
                <div class="text-2xl sm:text-3xl font-black text-neutral-900 mt-1 bengali-num">
                    <?= bn_num($total_logs); ?>
                </div>
                <span class="text-[11px] text-neutral-500 mt-0.5 block">সর্বমোট কার্যকলাপ লিপিবদ্ধ</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl shrink-0">
                <i class="ph-bold ph-clock-counter-clockwise"></i>
            </div>
        </div>

        <!-- Create Logs -->
        <div class="p-4 sm:p-5 rounded-2xl bg-white border border-neutral-200/90 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider block">নতুন সনদ তৈরি</span>
                <div class="text-2xl sm:text-3xl font-black text-neutral-900 mt-1 bengali-num">
                    <?= bn_num($create_logs); ?>
                </div>
                <span class="text-[11px] text-neutral-500 mt-0.5 block">সফলভাবে প্রস্তুতকৃত সনদ</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0">
                <i class="ph-bold ph-plus-circle"></i>
            </div>
        </div>

        <!-- Update Logs -->
        <div class="p-4 sm:p-5 rounded-2xl bg-white border border-neutral-200/90 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-blue-600 uppercase tracking-wider block">সনদ সংশোধন</span>
                <div class="text-2xl sm:text-3xl font-black text-neutral-900 mt-1 bengali-num">
                    <?= bn_num($update_logs); ?>
                </div>
                <span class="text-[11px] text-neutral-500 mt-0.5 block">তথ্য এডিট ও আপডেট রেকর্ড</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shrink-0">
                <i class="ph-bold ph-pencil-simple-line"></i>
            </div>
        </div>

        <!-- Approval & Other Logs -->
        <div class="p-4 sm:p-5 rounded-2xl bg-white border border-neutral-200/90 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-purple-600 uppercase tracking-wider block">অনুমোদন ও অন্যান্য</span>
                <div class="text-2xl sm:text-3xl font-black text-neutral-900 mt-1 bengali-num">
                    <?= bn_num($approval_logs); ?>
                </div>
                <span class="text-[11px] text-neutral-500 mt-0.5 block">প্রশাসনিক অনুমোদন কার্যক্রম</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl shrink-0">
                <i class="ph-bold ph-seal-check"></i>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white rounded-3xl border border-neutral-200/90 p-5 shadow-xs space-y-4">
        <form method="GET" action="sonod_audit" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
            <!-- Search Text -->
            <div class="sm:col-span-5">
                <label class="block text-xs font-bold text-neutral-700 mb-1.5">অনুসন্ধান (Search Keyword)</label>
                <div class="relative">
                    <i class="ph-bold ph-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-neutral-400 text-sm"></i>
                    <input type="text" name="q" value="<?= htmlspecialchars($search_query ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="সনদ নং, ট্র্যাকিং আইডি, আবেদনকারী বা কর্মকর্তার নাম..." class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-neutral-300 bg-white text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
            </div>

            <!-- Action Filter -->
            <div class="sm:col-span-3">
                <label class="block text-xs font-bold text-neutral-700 mb-1.5">অ্যাকশন টাইপ</label>
                <select name="action" class="w-full px-3 py-2.5 rounded-xl border border-neutral-300 bg-white text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <option value="all" <?= ($action_filter === 'all' || empty($action_filter)) ? 'selected' : ''; ?>>সকল অ্যাকশন</option>
                    <option value="create" <?= ($action_filter === 'create') ? 'selected' : ''; ?>>নতুন তৈরি (Create)</option>
                    <option value="update" <?= ($action_filter === 'update') ? 'selected' : ''; ?>>তথ্য সংশোধন (Update)</option>
                    <option value="approval.approve" <?= ($action_filter === 'approval.approve') ? 'selected' : ''; ?>>অনুমোদন (Approve)</option>
                    <option value="approval.propose" <?= ($action_filter === 'approval.propose') ? 'selected' : ''; ?>>অনুরোধ (Propose)</option>
                    <option value="delete" <?= ($action_filter === 'delete') ? 'selected' : ''; ?>>মুছে ফেলা (Delete)</option>
                </select>
            </div>

            <!-- Entity Filter -->
            <div class="sm:col-span-2">
                <label class="block text-xs font-bold text-neutral-700 mb-1.5">সনদের ধরন</label>
                <select name="entity" class="w-full px-3 py-2.5 rounded-xl border border-neutral-300 bg-white text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <option value="all" <?= ($entity_filter === 'all' || empty($entity_filter)) ? 'selected' : ''; ?>>সকল ধরন</option>
                    <option value="sonod" <?= ($entity_filter === 'sonod') ? 'selected' : ''; ?>>সাধারণ সনদ</option>
                    <option value="warish_sonod" <?= ($entity_filter === 'warish_sonod') ? 'selected' : ''; ?>>ওয়ারিশ সনদ</option>
                    <option value="trade_license" <?= ($entity_filter === 'trade_license') ? 'selected' : ''; ?>>ট্রেড লাইসেন্স</option>
                    <option value="approvals" <?= ($entity_filter === 'approvals') ? 'selected' : ''; ?>>অনুমোদন ফাইল</option>
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="sm:col-span-2 flex items-center gap-2">
                <button type="submit" class="flex-1 py-2.5 px-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs flex items-center justify-center gap-1.5 shadow-2xs transition cursor-pointer">
                    <i class="ph-bold ph-funnel"></i>
                    <span>ফিল্টার</span>
                </button>
                <a href="sonod_audit" class="p-2.5 rounded-xl bg-neutral-100 hover:bg-neutral-200 text-neutral-600 text-xs transition" title="ফিল্টার রিসেট করুন">
                    <i class="ph-bold ph-arrow-counter-clockwise"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Audit Log Records Table -->
    <div class="bg-white rounded-3xl border border-neutral-200/90 shadow-xs p-6 space-y-4 overflow-hidden">
        <div class="flex items-center justify-between pb-3 border-b border-neutral-100">
            <div class="flex items-center gap-2">
                <h3 class="text-base font-bold text-neutral-900 flex items-center gap-2">
                    <i class="ph-bold ph-list-bullets text-amber-500"></i>
                    অডিট ট্রেইল তালিকা
                </h3>
            </div>
            <span class="text-xs text-neutral-500 font-medium">
                সর্বশেষ <strong class="text-neutral-800 bengali-num"><?= bn_num(count($audit_logs)); ?></strong> টি রেকর্ড প্রদর্শিত
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left border-collapse">
                <thead>
                    <tr class="bg-neutral-50 text-neutral-600 border-y border-neutral-200/80 font-bold">
                        <th class="py-3 px-3.5 w-12 text-center">নং</th>
                        <th class="py-3 px-4 w-40">তারিখ ও সময়</th>
                        <th class="py-3 px-4 w-44">কর্মকর্তা / ইউজার</th>
                        <th class="py-3 px-3.5 w-32 text-center">অ্যাকশন</th>
                        <th class="py-3 px-4">কার্যক্রম ও সনদের বিবরণ</th>
                        <th class="py-3 px-3 text-center w-28">আইপি অ্যাড্রেস</th>
                        <th class="py-3 px-4 text-center w-28">বিস্তারিত / ডিফ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 text-neutral-700">
                    <?php if (!empty($audit_logs)): $sl = 1; foreach ($audit_logs as $log): 
                        // Action formatting
                        $action_badge = 'bg-neutral-100 text-neutral-700 border-neutral-200';
                        $action_label = strtoupper($log->action);
                        $action_icon  = 'ph-info';

                        if ($log->action === 'create') {
                            $action_badge = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                            $action_label = 'নতুন তৈরি';
                            $action_icon  = 'ph-plus-circle';
                        } elseif ($log->action === 'update') {
                            $action_badge = 'bg-blue-50 text-blue-700 border-blue-200';
                            $action_label = 'তথ্য সংশোধন';
                            $action_icon  = 'ph-pencil-simple-line';
                        } elseif (strpos($log->action, 'approval.') === 0) {
                            $action_badge = 'bg-purple-50 text-purple-700 border-purple-200';
                            $action_label = ($log->action === 'approval.approve') ? 'অনুমোদিত' : (($log->action === 'approval.reject') ? 'বাতিল' : 'অনুরোধ');
                            $action_icon  = 'ph-seal-check';
                        } elseif ($log->action === 'delete') {
                            $action_badge = 'bg-rose-50 text-rose-700 border-rose-200';
                            $action_label = 'মুছে ফেলা';
                            $action_icon  = 'ph-trash';
                        }

                        $has_diff = (!empty($log->before_data) || !empty($log->after_data));
                    ?>
                    <tr class="hover:bg-neutral-50/70 transition-colors">
                        <td class="py-3 px-3.5 text-center font-bold text-neutral-400 bengali-num">
                            <?= bn_num($sl++); ?>
                        </td>

                        <td class="py-3 px-4 whitespace-nowrap">
                            <span class="font-bold text-neutral-900 block bengali-num">
                                <?= bn_num(date('d/m/Y', strtotime($log->created_at))); ?>
                            </span>
                            <span class="text-[11px] text-neutral-400 block bengali-num">
                                <?= bn_num(date('h:i A', strtotime($log->created_at))); ?>
                            </span>
                        </td>

                        <td class="py-3 px-4">
                            <div class="font-bold text-neutral-900 flex items-center gap-1.5">
                                <i class="ph-bold ph-user-circle text-neutral-400 text-sm"></i>
                                <span><?= htmlspecialchars($log->actor_name ?: 'System', ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                            <?php if (!empty($log->actor_group)): ?>
                                <span class="inline-block mt-0.5 px-2 py-0.2 rounded text-[10px] font-mono font-bold uppercase bg-neutral-100 text-neutral-600 border border-neutral-200">
                                    <?= htmlspecialchars($log->actor_group, ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            <?php endif; ?>
                        </td>

                        <td class="py-3 px-3.5 text-center whitespace-nowrap">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold border <?= $action_badge; ?>">
                                <i class="ph-bold <?= $action_icon; ?>"></i>
                                <?= $action_label; ?>
                            </span>
                        </td>

                        <td class="py-3 px-4">
                            <div class="font-bold text-neutral-800 text-xs leading-snug">
                                <?= htmlspecialchars($log->summary ?? '—', ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                            <?php if (!empty($log->entity_id)): ?>
                                <div class="text-[11px] text-neutral-400 font-mono mt-0.5 flex items-center gap-1">
                                    <span>রেফারেন্স ID:</span>
                                    <strong class="text-indigo-600 font-semibold"><?= htmlspecialchars($log->entity_id, ENT_QUOTES, 'UTF-8'); ?></strong>
                                </div>
                            <?php endif; ?>
                        </td>

                        <td class="py-3 px-3 text-center font-mono text-[11px] text-neutral-500 whitespace-nowrap">
                            <?= htmlspecialchars($log->ip_address ?: '—', ENT_QUOTES, 'UTF-8'); ?>
                        </td>

                        <td class="py-3 px-4 text-center">
                            <?php if ($has_diff): ?>
                                <button type="button" onclick="openAuditDiffModal(<?= (int)$log->audit_id; ?>)" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs transition border border-indigo-200/80 cursor-pointer shadow-2xs">
                                    <i class="ph-bold ph-git-diff"></i>
                                    <span>ডিফ দেখুন</span>
                                </button>
                            <?php else: ?>
                                <button type="button" onclick="openAuditDiffModal(<?= (int)$log->audit_id; ?>)" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-neutral-100 hover:bg-neutral-200 text-neutral-600 font-semibold text-xs transition cursor-pointer">
                                    <i class="ph-bold ph-eye"></i>
                                    <span>তথ্য</span>
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr>
                        <td colspan="7" class="py-12 text-center text-neutral-400">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <i class="ph-bold ph-clock-counter-clockwise text-3xl text-neutral-300"></i>
                                <span class="font-bold">কোনো অডিট রেকর্ড পাওয়া যায়নি।</span>
                                <p class="text-xs text-neutral-400">নতুন সনদ তৈরি বা সংশোধন করা হলে স্বয়ংক্রিয়ভাবে অডিট ট্রেইল যুক্ত হবে।</p>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Modal CSS -->
<style>
#auditDetailModal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 99999;
    background-color: rgba(15, 23, 42, 0.7) !important;
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
    display: none; /* Must remain hidden on page load! */
    align-items: center;
    justify-content: center;
    padding: 1rem;
    overflow-y: auto;
}
.audit-modal-card {
    background-color: #ffffff !important;
    color: #0f172a !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 20px !important;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35) !important;
    width: 100%;
    max-width: 42rem;
    max-height: 88vh;
    overflow-y: auto;
    position: relative;
    padding: 1.5rem;
}
.audit-meta-box {
    background-color: #f8fafc !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 14px;
}
.audit-summary-box {
    background-color: #eef2ff !important;
    border: 1px solid #c7d2fe !important;
    color: #1e1b4b !important;
    border-radius: 14px;
}
</style>

<!-- Interactive Diff / Payload Modal -->
<div id="auditDetailModal" style="display: none;">
    <div class="audit-modal-card space-y-4">
        
        <!-- Modal Header -->
        <div class="flex items-center justify-between pb-3 border-b border-neutral-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl shrink-0">
                    <i class="ph-bold ph-shield-check"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-neutral-900" id="modalAuditTitle">অডিট রেকর্ড বিস্তারিত</h3>
                    <p class="text-[11px] text-neutral-400" id="modalAuditSubtitle">কার্যক্রম ও ডাটা পরিবর্তন বিবরণ</p>
                </div>
            </div>
            <button type="button" onclick="closeAuditModal()" class="w-8 h-8 rounded-xl bg-neutral-100 hover:bg-neutral-200 text-neutral-600 flex items-center justify-center text-lg cursor-pointer transition">
                &times;
            </button>
        </div>

        <!-- Meta Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs p-3.5 audit-meta-box">
            <div>
                <span class="text-neutral-400 block text-[10px] font-bold uppercase tracking-wider">অ্যাকশন</span>
                <span id="modalMetaAction" class="inline-block font-bold text-neutral-900 uppercase mt-0.5">—</span>
            </div>
            <div>
                <span class="text-neutral-400 block text-[10px] font-bold uppercase tracking-wider">ইউজার</span>
                <strong id="modalMetaActor" class="font-bold text-neutral-900 mt-0.5 block truncate">—</strong>
            </div>
            <div>
                <span class="text-neutral-400 block text-[10px] font-bold uppercase tracking-wider">আইপি অ্যাড্রেস</span>
                <strong id="modalMetaIP" class="font-mono text-neutral-900 mt-0.5 block">—</strong>
            </div>
            <div>
                <span class="text-neutral-400 block text-[10px] font-bold uppercase tracking-wider">তারিখ ও সময়</span>
                <strong id="modalMetaDate" class="font-bold text-neutral-900 mt-0.5 block whitespace-nowrap">—</strong>
            </div>
        </div>

        <!-- Summary -->
        <div class="p-3.5 audit-summary-box text-xs">
            <span class="text-[10px] font-bold text-indigo-600 uppercase tracking-wider block mb-0.5">কার্যক্রম বিবরণ:</span>
            <span id="modalSummaryText" class="font-semibold text-neutral-800 leading-relaxed block">—</span>
        </div>

        <!-- Dynamic Diff / Snapshot Content -->
        <div id="modalDiffContent" class="space-y-3 text-xs">
            <!-- Dynamically populated via JavaScript -->
        </div>

        <!-- Modal Footer -->
        <div class="flex justify-end pt-3 border-t border-neutral-100">
            <button type="button" onclick="closeAuditModal()" class="px-5 py-2 rounded-xl bg-neutral-100 hover:bg-neutral-200 text-neutral-700 font-bold text-xs transition cursor-pointer">
                বন্ধ করুন
            </button>
        </div>

    </div>
</div>

<?php
// Pre-map logs for instant client-side modal opening
$client_audit_store = array();
if (!empty($audit_logs)) {
    foreach ($audit_logs as $log_item) {
        $client_audit_store[$log_item->audit_id] = array(
            'audit_id'    => (int)$log_item->audit_id,
            'action'      => $log_item->action,
            'entity'      => $log_item->entity,
            'entity_id'   => $log_item->entity_id,
            'actor_name'  => $log_item->actor_name ?: 'System',
            'actor_group' => $log_item->actor_group ?: '',
            'summary'     => $log_item->summary ?: '—',
            'ip_address'  => $log_item->ip_address ?: '—',
            'created_at'  => date('d M, Y h:i A', strtotime($log_item->created_at)),
            'before'      => !empty($log_item->before_data) ? json_decode($log_item->before_data, true) : null,
            'after'       => !empty($log_item->after_data) ? json_decode($log_item->after_data, true) : null,
        );
    }
}
?>

<!-- Modal Controller Script -->
<script>
    const auditRecordsStore = <?= json_encode($client_audit_store, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE); ?>;

    function openAuditDiffModal(auditId) {
        const modal = document.getElementById('auditDetailModal');
        const diffContainer = document.getElementById('modalDiffContent');
        
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';

        const record = auditRecordsStore[auditId];
        if (record) {
            renderModalContent(record);
        } else {
            diffContainer.innerHTML = '<div class="py-8 text-center text-neutral-400"><i class="ph-bold ph-spinner animate-spin text-2xl"></i><span class="block text-xs mt-2">তথ্য লোড হচ্ছে...</span></div>';
            fetch('sonod_audit/details/' + auditId)
                .then(res => res.json())
                .then(data => {
                    if (!data.ok) {
                        diffContainer.innerHTML = '<div class="p-4 bg-rose-50 text-rose-700 rounded-xl text-xs font-bold">' + (data.msg || 'তথ্য পাওয়া যায়নি') + '</div>';
                        return;
                    }
                    renderModalContent(data);
                })
                .catch(err => {
                    diffContainer.innerHTML = '<div class="p-4 bg-rose-50 text-rose-700 rounded-xl text-xs font-bold">নেটওয়ার্ক বা সার্ভার ত্রুটি ঘটেছে।</div>';
                });
        }
    }

    function renderModalContent(data) {
        const diffContainer = document.getElementById('modalDiffContent');

        document.getElementById('modalAuditTitle').textContent = 'অডিট রেকর্ড #' + data.audit_id;
        
        // Action Label & Badge
        let actionBadge = '<span class="px-2 py-0.5 rounded text-[11px] font-bold bg-neutral-100 text-neutral-700 uppercase">' + data.action + '</span>';
        if (data.action === 'create') {
            actionBadge = '<span class="px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800">নতুন তৈরি</span>';
        } else if (data.action === 'update') {
            actionBadge = '<span class="px-2 py-0.5 rounded text-[11px] font-bold bg-blue-100 text-blue-800">তথ্য সংশোধন</span>';
        } else if (data.action === 'approval.approve') {
            actionBadge = '<span class="px-2 py-0.5 rounded text-[11px] font-bold bg-purple-100 text-purple-800">অনুমোদিত</span>';
        } else if (data.action === 'approval.reject') {
            actionBadge = '<span class="px-2 py-0.5 rounded text-[11px] font-bold bg-rose-100 text-rose-800">বাতিল</span>';
        } else if (data.action === 'approval.propose') {
            actionBadge = '<span class="px-2 py-0.5 rounded text-[11px] font-bold bg-purple-50 text-purple-700">অনুরোধ</span>';
        } else if (data.action === 'delete') {
            actionBadge = '<span class="px-2 py-0.5 rounded text-[11px] font-bold bg-rose-100 text-rose-800">মুছে ফেলা</span>';
        }
        document.getElementById('modalMetaAction').innerHTML = actionBadge;

        document.getElementById('modalMetaActor').textContent = data.actor_name + (data.actor_group ? ' (' + data.actor_group + ')' : '');
        document.getElementById('modalMetaIP').textContent = data.ip_address || '—';
        document.getElementById('modalMetaDate').textContent = data.created_at;
        document.getElementById('modalSummaryText').textContent = data.summary || '—';

        let html = '';

        // Case 1: Update action with Before vs After Diff
        if (data.action === 'update' && data.before && data.after) {
            html += '<div class="text-xs font-bold text-neutral-700 mb-1 flex items-center gap-1.5"><i class="ph-bold ph-git-diff text-indigo-600"></i> তথ্যের পরিবর্তনসমূহ (Before vs After Diff):</div>';
            html += '<div class="overflow-x-auto rounded-xl border border-neutral-200" style="background:#fff;"><table class="w-full text-xs text-left border-collapse">';
            html += '<thead><tr class="bg-neutral-100 text-neutral-700 font-bold border-b border-neutral-200"><th class="py-2.5 px-3">ফিল্ডের নাম</th><th class="py-2.5 px-3 bg-rose-50 text-rose-800">পূর্ববর্তী তথ্য (Before)</th><th class="py-2.5 px-3 bg-emerald-50 text-emerald-800">সংশোধিত তথ্য (After)</th></tr></thead>';
            html += '<tbody class="divide-y divide-neutral-200">';

            const fieldLabels = {
                'title': 'সনদের শিরোনাম',
                'name_bn': 'আবেদনকারীর নাম (বাংলা)',
                'name_en': 'আবেদনকারীর নাম (ইংরেজি)',
                'father_bn': 'পিতার নাম (বাংলা)',
                'father_en': 'পিতার নাম (ইংরেজি)',
                'mother_bn': 'মাতার নাম (বাংলা)',
                'mother_en': 'মাতার নাম (ইংরেজি)',
                'ward_no': 'ওয়ার্ড নম্বর',
                'village': 'গ্রাম',
                'house': 'বাড়ি / হোল্ডিং',
                'issue_date': 'সনদের তারিখ'
            };

            for (const key in data.after) {
                const beforeVal = (data.before && data.before[key]) ? data.before[key] : '';
                const afterVal = data.after[key] || '';
                const isChanged = (beforeVal !== afterVal);
                const label = fieldLabels[key] || key;

                html += `<tr class="${isChanged ? 'bg-amber-50/60' : ''}">
                    <td class="py-2 px-3 font-bold text-neutral-700 whitespace-nowrap">${label} ${isChanged ? '<span class="text-[9px] bg-amber-400 text-neutral-900 px-1 py-0.2 rounded font-black ml-1">পরিবর্তিত</span>' : ''}</td>
                    <td class="py-2 px-3 font-medium text-rose-700 bg-rose-50/40">${beforeVal || '<span class="text-neutral-400 italic">খালি</span>'}</td>
                    <td class="py-2 px-3 font-bold text-emerald-800 bg-emerald-50/40">${afterVal || '<span class="text-neutral-400 italic">খালি</span>'}</td>
                </tr>`;
            }

            html += '</tbody></table></div>';
        }
        // Case 2: Create action or Snapshot Payload
        else if (data.after && typeof data.after === 'object') {
            html += '<div class="text-xs font-bold text-neutral-700 mb-1 flex items-center gap-1.5"><i class="ph-bold ph-file-text text-emerald-600"></i> প্রস্তুতকৃত সনদের তথ্য স্ন্যাপশট:</div>';
            html += '<div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 p-3.5 rounded-xl bg-neutral-50 border border-neutral-200">';

            const createLabels = {
                'sonod_no': 'সনদ নম্বর',
                'tracking_id': 'ট্র্যাকিং আইডি',
                'title': 'সনদের ধরন',
                'name_bn': 'নাম (বাংলা)',
                'name_en': 'নাম (ইংরেজি)',
                'father_bn': 'পিতার নাম',
                'mother_bn': 'মাতার নাম',
                'ward_no': 'ওয়ার্ড নং',
                'village': 'গ্রাম',
                'house': 'বাড়ি',
                'dead_name_bn': 'মৃত ব্যক্তির নাম',
                'dead_father_bn': 'মৃত ব্যক্তির পিতা',
                'dead_mother_bn': 'মৃত ব্যক্তির মাতা',
                'dead_nid': 'মৃত ব্যক্তির NID/মৃত্যু সনদ নং',
                'dead_date': 'মৃত্যুর তারিখ',
                'applicant_name': 'আবেদনকারীর নাম',
                'warish_count': 'মোট ওয়ারিশ সংখ্যা',
                'business_name': 'ব্যবসা প্রতিষ্ঠানের নাম',
                'owner_name_bn': 'স্বত্বাধিকারীর নাম',
                'business_type': 'ব্যবসার ধরন',
                'fiscal_year': 'অর্থবছর',
                'total_fee': 'মোট ফি',
                'mobile': 'মোবাইল নম্বর'
            };

            for (const k in data.after) {
                if (k === 'warish_members' || typeof data.after[k] === 'object') continue;
                const val = data.after[k];
                const lbl = createLabels[k] || k;

                if (val) {
                    html += `<div class="p-2.5 rounded-lg bg-white border border-neutral-200/90 shadow-2xs">
                        <span class="text-[10px] text-neutral-400 font-bold uppercase block">${lbl}</span>
                        <strong class="text-xs text-neutral-800 font-bold block mt-0.5">${val}</strong>
                    </div>`;
                }
            }
            html += '</div>';

            // Warish members if present
            if (data.after.warish_members && Array.isArray(data.after.warish_members) && data.after.warish_members.length > 0) {
                html += '<div class="mt-3 text-xs font-bold text-neutral-700 mb-1 flex items-center gap-1.5"><i class="ph-bold ph-users text-indigo-600"></i> অন্তর্ভুক্ত ওয়ারিশদের তালিকা:</div>';
                html += '<div class="overflow-x-auto rounded-xl border border-neutral-200 bg-white"><table class="w-full text-xs text-left">';
                html += '<thead><tr class="bg-neutral-100 text-neutral-600 font-bold"><th class="py-2 px-3">নাম</th><th class="py-2 px-3">সম্পর্ক</th><th class="py-2 px-3">NID / জন্ম নিবন্ধন</th><th class="py-2 px-3">বয়স</th></tr></thead>';
                html += '<tbody class="divide-y divide-neutral-200">';
                data.after.warish_members.forEach(m => {
                    html += `<tr>
                        <td class="py-2 px-3 font-bold">${m.names_per || ''}</td>
                        <td class="py-2 px-3 text-neutral-600">${m.relation_pers || ''}</td>
                        <td class="py-2 px-3 font-mono text-[11px]">${m.nid_pers || '—'}</td>
                        <td class="py-2 px-3 text-neutral-600">${m.dob_pers || '—'}</td>
                    </tr>`;
                });
                html += '</tbody></table></div>';
            }
        }
        // Case 3: Approvals or records without extra payload
        else {
            html += `<div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200 text-center text-xs text-neutral-600 space-y-1">
                <i class="ph-bold ph-info text-indigo-500 text-xl block mb-1"></i>
                <div class="font-bold text-neutral-800">কার্যক্রমটি সফলভাবে সম্পন্ন ও নিবন্ধিত হয়েছে।</div>
                <div class="text-[11px] text-neutral-500">এই অ্যাকশনের জন্য আলাদা কোনো পরিবর্তনযোগ্য ফিল্ড পে-লোড প্রয়োজন হয়নি।</div>
            </div>`;
        }

        diffContainer.innerHTML = html;
    }

    function closeAuditModal() {
        const modal = document.getElementById('auditDetailModal');
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }

    // Close on backdrop click
    window.addEventListener('click', function(e) {
        const modal = document.getElementById('auditDetailModal');
        if (e.target === modal) {
            closeAuditModal();
        }
    });

    // Close on Escape key
    window.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAuditModal();
        }
    });
</script>
