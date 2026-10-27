<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$ticket_list   = $tickets ?? array();
$total_count   = $count_total ?? count($ticket_list);
$open_count    = $count_open ?? 0;
$pending_count = $count_pending ?? 0;
$resolved_count= $count_resolved ?? 0;
$closed_count  = $count_closed ?? 0;
$current_user  = $me ?? null;

function format_ticket_category($cat) {
    switch ($cat) {
        case 'technical':
            return array('label' => 'কারিগরি সমস্যা', 'class' => 'bg-indigo-50 text-indigo-700 border-indigo-200');
        case 'billing':
            return array('label' => 'বিলিং ও পেমেন্ট', 'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200');
        case 'domain':
            return array('label' => 'ডোমেইন ও নেটওয়ার্ক', 'class' => 'bg-sky-50 text-sky-700 border-sky-200');
        case 'training':
            return array('label' => 'ব্যবহার ও প্রশিক্ষণ', 'class' => 'bg-amber-50 text-amber-700 border-amber-200');
        case 'other':
            return array('label' => 'অন্যান্য', 'class' => 'bg-neutral-100 text-neutral-700 border-neutral-200');
        default:
            return array('label' => 'সাধারণ অনুসন্ধান', 'class' => 'bg-neutral-50 text-neutral-700 border-neutral-200');
    }
}

function format_ticket_status($st) {
    switch ($st) {
        case 'open':
            return array('label' => 'উন্মুক্ত', 'class' => 'bg-rose-50 text-rose-700 border-rose-200', 'dot' => 'bg-rose-500');
        case 'pending':
            return array('label' => 'অপেক্ষমাণ', 'class' => 'bg-amber-50 text-amber-700 border-amber-200', 'dot' => 'bg-amber-500');
        case 'resolved':
            return array('label' => 'সমাধানকৃত', 'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'dot' => 'bg-emerald-500');
        case 'closed':
            return array('label' => 'বন্ধ', 'class' => 'bg-neutral-100 text-neutral-600 border-neutral-200', 'dot' => 'bg-neutral-400');
        default:
            return array('label' => htmlspecialchars($st, ENT_QUOTES, 'UTF-8'), 'class' => 'bg-neutral-50 text-neutral-700 border-neutral-200', 'dot' => 'bg-neutral-400');
    }
}

function format_ticket_priority($p) {
    switch ($p) {
        case 'urgent':
            return array('label' => 'অতি জরুরি', 'class' => 'text-rose-600 font-bold');
        case 'high':
            return array('label' => 'জরুরি', 'class' => 'text-amber-600 font-bold');
        case 'low':
            return array('label' => 'সাধারণ (কম)', 'class' => 'text-neutral-500 font-medium');
        default:
            return array('label' => 'সাধারণ', 'class' => 'text-neutral-600 font-medium');
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
                <span class="text-neutral-800 font-medium">সাপোর্ট ও সহায়তা</span>
            </nav>
            <h1 class="text-xl sm:text-2xl font-bold text-neutral-900 tracking-tight flex items-center gap-2">
                <span class="w-2.5 h-6 bg-indigo-600 rounded-full inline-block"></span>
                সাপোর্ট ও কারিগরি সহায়তা
            </h1>
            <p class="text-xs sm:text-sm text-neutral-500 mt-0.5">
                আপনার ইউনিয়নের যেকোনো সমস্যা, বিলিং বা কারিগরি বিষয়ে সরাসরি কেন্দ্রীয় সাপোর্ট টিমের সাথে যোগাযোগ করুন
            </p>
        </div>

        <div>
            <button type="button" data-toggle="modal" data-target="#newTicketModal" class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white rounded-xl text-xs sm:text-sm font-bold shadow-sm hover:shadow transition-all duration-150">
                <i class="ph-bold ph-plus-circle text-base"></i>
                <span>নতুন সাপোর্ট টিকিট</span>
            </button>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Tickets -->
        <div class="bg-white p-4 rounded-2xl border border-neutral-200 shadow-2xs flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-lg shrink-0">
                <i class="ph-bold ph-ticket"></i>
            </div>
            <div>
                <span class="text-xs font-semibold text-neutral-500">মোট টিকিট</span>
                <strong class="block text-xl font-bold text-neutral-900 leading-tight"><?= bn_num($total_count); ?> টি</strong>
            </div>
        </div>

        <!-- Open Tickets -->
        <div class="bg-white p-4 rounded-2xl border border-neutral-200 shadow-2xs flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold text-lg shrink-0">
                <i class="ph-bold ph-clock-countdown"></i>
            </div>
            <div>
                <span class="text-xs font-semibold text-neutral-500">উন্মুক্ত টিকিট</span>
                <strong class="block text-xl font-bold text-rose-600 leading-tight"><?= bn_num($open_count); ?> টি</strong>
            </div>
        </div>

        <!-- Pending Tickets -->
        <div class="bg-white p-4 rounded-2xl border border-neutral-200 shadow-2xs flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-lg shrink-0">
                <i class="ph-bold ph-hourglass-medium"></i>
            </div>
            <div>
                <span class="text-xs font-semibold text-neutral-500">অপেক্ষমাণ</span>
                <strong class="block text-xl font-bold text-amber-600 leading-tight"><?= bn_num($pending_count); ?> টি</strong>
            </div>
        </div>

        <!-- Resolved Tickets -->
        <div class="bg-white p-4 rounded-2xl border border-neutral-200 shadow-2xs flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-lg shrink-0">
                <i class="ph-bold ph-check-circle"></i>
            </div>
            <div>
                <span class="text-xs font-semibold text-neutral-500">সমাধানকৃত</span>
                <strong class="block text-xl font-bold text-emerald-600 leading-tight"><?= bn_num($resolved_count); ?> টি</strong>
            </div>
        </div>
    </div>

    <!-- Main Ticket Table / Container -->
    <div class="bg-white rounded-2xl border border-neutral-200 shadow-2xs overflow-hidden">
        
        <!-- Controls & Filters -->
        <div class="p-4 sm:p-5 border-b border-neutral-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-neutral-50/50">
            <!-- Tabs -->
            <div class="inline-flex p-1 bg-neutral-200/70 rounded-xl gap-1 text-xs font-bold text-neutral-600 self-start sm:self-auto" id="ticketTabs">
                <button type="button" class="tab-btn active px-3.5 py-1.5 rounded-lg transition-all bg-white text-indigo-600 shadow-xs" data-filter="all">
                    সকল টিকিট (<?= bn_num($total_count); ?>)
                </button>
                <button type="button" class="tab-btn px-3.5 py-1.5 rounded-lg transition-all hover:text-neutral-900" data-filter="open">
                    চলমান (<?= bn_num($open_count + $pending_count); ?>)
                </button>
                <button type="button" class="tab-btn px-3.5 py-1.5 rounded-lg transition-all hover:text-neutral-900" data-filter="resolved">
                    সমাধানকৃত (<?= bn_num($resolved_count + $closed_count); ?>)
                </button>
            </div>

            <!-- Search box -->
            <div class="relative w-full sm:w-64">
                <i class="ph ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400 text-sm"></i>
                <input type="text" id="ticketSearchInput" placeholder="টিকিট বা বিষয় খুঁজুন..." class="w-full pl-9 pr-3 py-1.5 bg-white border border-neutral-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
            </div>
        </div>

        <!-- Ticket Listing Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-neutral-600">
                <thead class="bg-neutral-50/75 border-b border-neutral-200 text-[11px] font-bold uppercase tracking-wider text-neutral-500">
                    <tr>
                        <th class="px-5 py-3.5">টিকিট নম্বর</th>
                        <th class="px-5 py-3.5">বিষয় ও বিবরণ</th>
                        <th class="px-5 py-3.5">বিভাগ</th>
                        <th class="px-5 py-3.5">অগ্রাধিকার</th>
                        <th class="px-5 py-3.5">স্ট্যাটাস</th>
                        <th class="px-5 py-3.5">তারিখ</th>
                        <th class="px-5 py-3.5 text-right">পদক্ষেপ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100" id="ticketTableBody">
                    <?php if (empty($ticket_list)): ?>
                        <tr id="emptyRow">
                            <td colspan="7" class="px-5 py-12 text-center">
                                <div class="w-12 h-12 rounded-2xl bg-neutral-100 text-neutral-400 flex items-center justify-center mx-auto mb-3 text-xl">
                                    <i class="ph-bold ph-chats-circle"></i>
                                </div>
                                <h3 class="text-sm font-bold text-neutral-800">কোনো সাপোর্ট টিকিট নেই</h3>
                                <p class="text-xs text-neutral-500 mt-1 max-w-sm mx-auto">
                                    আপনার ইউনিয়নের কোনো বিষয়ে সাহায্য বা অনুসন্ধানের প্রয়োজন হলে নতুন টিকিট তৈরি করুন।
                                </p>
                                <button type="button" data-toggle="modal" data-target="#newTicketModal" class="inline-flex items-center gap-1.5 px-3.5 py-2 mt-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition">
                                    <i class="ph-bold ph-plus-circle"></i>
                                    <span>নতুন টিকিট তৈরি করুন</span>
                                </button>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($ticket_list as $row): 
                            $cat_info = format_ticket_category($row->category);
                            $st_info  = format_ticket_status($row->status);
                            $pr_info  = format_ticket_priority($row->priority);
                            $rep_num  = (int) ($row->reply_count ?? 0);
                        ?>
                            <tr class="ticket-row hover:bg-neutral-50/50 transition-colors" data-status="<?= htmlspecialchars($row->status, ENT_QUOTES, 'UTF-8'); ?>" data-search="<?= htmlspecialchars(strtolower($row->ticket_no . ' ' . $row->subject), ENT_QUOTES, 'UTF-8'); ?>">
                                <td class="px-5 py-4 whitespace-nowrap font-mono font-bold text-neutral-900">
                                    <span class="px-2 py-1 bg-neutral-100 border border-neutral-200/80 rounded-lg text-xs">
                                        <?= htmlspecialchars($row->ticket_no, ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </td>
                                <td class="px-5 py-4 min-w-[220px]">
                                    <div class="font-bold text-neutral-900 hover:text-indigo-600 transition cursor-pointer" onclick="openTicketDetails(<?= (int) $row->ticket_id; ?>)">
                                        <?= htmlspecialchars($row->subject, ENT_QUOTES, 'UTF-8'); ?>
                                    </div>
                                    <div class="text-[11px] text-neutral-400 mt-0.5 line-clamp-1">
                                        <?= htmlspecialchars(mb_substr($row->message, 0, 70), ENT_QUOTES, 'UTF-8'); ?>
                                    </div>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-semibold border <?= $cat_info['class']; ?>">
                                        <?= $cat_info['label']; ?>
                                    </span>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span class="text-xs <?= $pr_info['class']; ?>">
                                        <?= $pr_info['label']; ?>
                                    </span>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-semibold border <?= $st_info['class']; ?>">
                                        <span class="w-1.5 h-1.5 rounded-full <?= $st_info['dot']; ?>"></span>
                                        <?= $st_info['label']; ?>
                                    </span>
                                    <?php if ($rep_num > 0): ?>
                                        <span class="ml-1.5 inline-flex items-center gap-1 text-[10px] font-bold text-neutral-500 bg-neutral-100 px-1.5 py-0.5 rounded-md" title="উত্তরের সংখ্যা">
                                            <i class="ph-bold ph-chats text-xs text-neutral-400"></i>
                                            <?= bn_num($rep_num); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-neutral-500 text-[11px]">
                                    <?= bn_num(date('d M, Y', strtotime($row->created_at))); ?><br>
                                    <span class="text-neutral-400"><?= bn_num(date('h:i A', strtotime($row->created_at))); ?></span>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-right">
                                    <button type="button" onclick="openTicketDetails(<?= (int) $row->ticket_id; ?>)" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-neutral-100 hover:bg-indigo-50 hover:text-indigo-600 text-neutral-700 rounded-xl text-xs font-bold transition">
                                        <i class="ph-bold ph-chats-circle text-sm text-indigo-500"></i>
                                        <span>বিস্তারিত ও বার্তালাপ</span>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<!-- Modal 1: Create New Support Ticket -->
<div id="newTicketModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered max-w-xl mx-auto my-4" role="document">
        <div class="modal-content bg-white rounded-2xl shadow-2xl border border-neutral-200 overflow-hidden w-full flex flex-col max-h-[calc(100vh-3.5rem)]">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-100 shrink-0 bg-white">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-base">
                        <i class="ph-bold ph-ticket"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-neutral-900 leading-tight">নতুন সাপোর্ট টিকিট তৈরি করুন</h3>
                        <p class="text-[11px] text-neutral-500">কেন্দ্রীয় প্রযুক্তি টিমের সাথে সরাসরি যোগাযোগ করুন</p>
                    </div>
                </div>
                <button type="button" class="w-7 h-7 rounded-xl text-neutral-400 hover:text-neutral-700 hover:bg-neutral-100 flex items-center justify-center transition" aria-label="বন্ধ করুন" title="বন্ধ করুন" data-dismiss="modal">
                    <i class="ph ph-x text-base"></i>
                </button>
            </div>

            <!-- Modal Body (Scrollable) -->
            <div class="p-6 overflow-y-auto space-y-4 flex-1 min-h-0 bg-white">
                
                <div id="newTicketAlert" class="hidden p-3 rounded-xl text-xs font-medium border"></div>

                <form id="newTicketForm" class="space-y-4">
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-neutral-700 mb-1.5">
                                সমস্যা বা সেবার বিভাগ <span class="text-rose-500 font-bold">*</span>
                            </label>
                            <select name="category" class="w-full text-xs px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                                <option value="technical">কারিগরি সমস্যা (সফটওয়্যার / এরর)</option>
                                <option value="billing">বিলিং ও সাবস্ক্রিপশন</option>
                                <option value="domain">ডোমেইন ও সংযোগ</option>
                                <option value="training">ব্যবহার ও প্রশিক্ষণ সহায়তা</option>
                                <option value="general">সাধারণ অনুসন্ধান</option>
                                <option value="other">অন্যান্য</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-neutral-700 mb-1.5">
                                অগ্রাধিকার মাত্রা <span class="text-rose-500 font-bold">*</span>
                            </label>
                            <select name="priority" class="w-full text-xs px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                                <option value="normal" selected>সাধারণ (Normal)</option>
                                <option value="high">জরুরি (High)</option>
                                <option value="urgent">অতি জরুরি (Urgent)</option>
                                <option value="low">কম অগ্রাধিকার (Low)</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-neutral-700 mb-1.5">
                            টিকিটের বিষয় / সারসংক্ষেপ <span class="text-rose-500 font-bold">*</span>
                        </label>
                        <input type="text" name="subject" required class="w-full text-xs px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition" placeholder="উদাঃ রশিদ প্রিন্ট করার সময় বাংলা ফন্ট ভেঙে যাচ্ছে">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-neutral-700 mb-1.5">
                            যোগাযোগের মোবাইল নম্বর
                        </label>
                        <input type="text" name="phone" value="<?= htmlspecialchars($current_user->phone ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="w-full text-xs px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition" placeholder="উদাঃ 017xxxxxxxx">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-neutral-700 mb-1.5">
                            সমস্যার বিস্তারিত বিবরণ <span class="text-rose-500 font-bold">*</span>
                        </label>
                        <textarea name="message" rows="4" required class="w-full text-xs px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition leading-relaxed" placeholder="সমস্যার সুনির্দিষ্ট বিবরণ, কোন পেইজে সমস্যা হচ্ছে এবং কীভাবে ঘটেছে বিস্তারিত লিখুন..."></textarea>
                    </div>

                </form>

            </div>

            <!-- Modal Footer -->
            <div class="bg-neutral-50 px-6 py-3.5 border-t border-neutral-100 flex items-center justify-end gap-2.5 shrink-0">
                <button type="button" class="px-4 py-2 rounded-xl text-xs font-semibold text-neutral-600 hover:text-neutral-900 hover:bg-neutral-200/50 transition" data-dismiss="modal">
                    বাতিল
                </button>
                <button type="button" id="submitTicketBtn" class="inline-flex items-center gap-1.5 px-5 py-2 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white rounded-xl text-xs font-bold shadow-sm transition">
                    <i class="ph-bold ph-paper-plane-tilt text-sm"></i>
                    <span>টিকিট জমা দিন</span>
                </button>
            </div>

        </div>
    </div>
</div>

<!-- Modal 2: Ticket Details & Conversation Thread -->
<div id="ticketDetailsModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered max-w-2xl mx-auto my-4" role="document">
        <div class="modal-content bg-white rounded-2xl shadow-2xl border border-neutral-200 overflow-hidden w-full flex flex-col max-h-[calc(100vh-3.5rem)]">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-100 shrink-0 bg-neutral-50/75">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-sm">
                        <i class="ph-bold ph-chats-circle"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span id="detailTicketNo" class="font-mono font-bold text-xs bg-white px-2 py-0.5 rounded border border-neutral-200 text-neutral-900">...</span>
                            <span id="detailTicketStatus" class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border">...</span>
                        </div>
                        <h3 id="detailTicketSubject" class="text-sm font-bold text-neutral-900 mt-0.5">...</h3>
                    </div>
                </div>
                <button type="button" class="w-7 h-7 rounded-xl text-neutral-400 hover:text-neutral-700 hover:bg-neutral-100 flex items-center justify-center transition" aria-label="বন্ধ করুন" title="বন্ধ করুন" data-dismiss="modal">
                    <i class="ph ph-x text-base"></i>
                </button>
            </div>

            <!-- Modal Body: Conversation Thread (Scrollable) -->
            <div class="p-6 overflow-y-auto space-y-4 flex-1 min-h-0 bg-slate-50" id="conversationContainer">
                
                <!-- Initial Ticket Original Message Box -->
                <div class="bg-white rounded-2xl p-4 border border-neutral-200 shadow-2xs space-y-2">
                    <div class="flex items-center justify-between text-[11px] pb-2 border-b border-neutral-100">
                        <div class="flex items-center gap-1.5 font-bold text-neutral-800">
                            <i class="ph-bold ph-user-circle text-sm text-neutral-500"></i>
                            <span id="detailSenderName">ইউজার</span>
                            <span class="text-neutral-400 font-normal">(আবেদনকারী)</span>
                        </div>
                        <span id="detailCreatedAt" class="text-neutral-400 font-mono text-[10px]">...</span>
                    </div>
                    <div id="detailMessage" class="text-xs text-neutral-700 leading-relaxed whitespace-pre-wrap">...</div>
                </div>

                <!-- Replies Container -->
                <div id="repliesList" class="space-y-3">
                    <!-- Dynamic replies injected here -->
                </div>

            </div>

            <!-- Modal Footer: Quick Reply Box -->
            <div class="bg-white p-4 border-t border-neutral-200 shrink-0">
                <form id="replyForm" class="space-y-2">
                    <input type="hidden" name="ticket_id" id="replyTicketId" value="">
                    
                    <div class="flex items-start gap-2">
                        <textarea name="message" id="replyMessageInput" rows="2" required placeholder="আপনার উত্তর বা অতিরিক্ত তথ্য এখানে লিখুন..." class="flex-1 text-xs px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition leading-relaxed resize-none"></textarea>
                        
                        <button type="submit" id="sendReplyBtn" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white rounded-xl text-xs font-bold shadow-sm transition shrink-0 self-end">
                            <i class="ph-bold ph-paper-plane-tilt text-sm"></i>
                            <span>উত্তর পাঠান</span>
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<script>
    // Tab filtering
    $(document).on('click', '.tab-btn', function() {
        $('.tab-btn').removeClass('active bg-white text-indigo-600 shadow-xs').addClass('hover:text-neutral-900');
        $(this).addClass('active bg-white text-indigo-600 shadow-xs').removeClass('hover:text-neutral-900');
        
        let filter = $(this).data('filter');
        applyTicketFilters(filter, $('#ticketSearchInput').val());
    });

    // Search filter
    $('#ticketSearchInput').on('input', function() {
        let activeFilter = $('.tab-btn.active').data('filter') || 'all';
        applyTicketFilters(activeFilter, $(this).val());
    });

    function applyTicketFilters(statusFilter, searchText) {
        let query = (searchText || '').toLowerCase().trim();
        let visibleCount = 0;

        $('.ticket-row').each(function() {
            let rowStatus = $(this).data('status');
            let searchContent = $(this).data('search');

            let matchesStatus = true;
            if (statusFilter === 'open') {
                matchesStatus = (rowStatus === 'open' || rowStatus === 'pending');
            } else if (statusFilter === 'resolved') {
                matchesStatus = (rowStatus === 'resolved' || rowStatus === 'closed');
            }

            let matchesSearch = true;
            if (query !== '') {
                matchesSearch = searchContent.indexOf(query) !== -1;
            }

            if (matchesStatus && matchesSearch) {
                $(this).show();
                visibleCount++;
            } else {
                $(this).hide();
            }
        });
    }

    // Submit New Ticket
    $('#submitTicketBtn').on('click', function() {
        let $btn = $(this);
        let $form = $('#newTicketForm');
        let $alert = $('#newTicketAlert');

        let subject = $form.find('input[name="subject"]').val().trim();
        let message = $form.find('textarea[name="message"]').val().trim();

        if (!subject || !message) {
            $alert.removeClass('hidden bg-emerald-50 text-emerald-800 border-emerald-200')
                  .addClass('bg-rose-50 text-rose-800 border-rose-200')
                  .text('অনুগ্রহ করে বিষয় এবং বিস্তারিত বিবরণ পূরণ করুন।');
            return;
        }

        $btn.prop('disabled', true).html('<i class="ph-bold ph-spinner animate-spin text-sm"></i> জমা হচ্ছে...');

        $.ajax({
            type: 'POST',
            url: 'support/create',
            data: $form.serialize(),
            dataType: 'json',
            success: function(res) {
                $btn.prop('disabled', false).html('<i class="ph-bold ph-paper-plane-tilt text-sm"></i> <span>টিকিট জমা দিন</span>');
                if (res.ok) {
                    $alert.removeClass('hidden bg-rose-50 text-rose-800 border-rose-200')
                          .addClass('bg-emerald-50 text-emerald-800 border-emerald-200')
                          .text(res.msg);
                    setTimeout(function() {
                        location.reload();
                    }, 1200);
                } else {
                    $alert.removeClass('hidden bg-emerald-50 text-emerald-800 border-emerald-200')
                          .addClass('bg-rose-50 text-rose-800 border-rose-200')
                          .text(res.msg || 'সমস্যা হয়েছে। আবার চেষ্টা করুন।');
                }
            },
            error: function() {
                $btn.prop('disabled', false).html('<i class="ph-bold ph-paper-plane-tilt text-sm"></i> <span>টিকিট জমা দিন</span>');
                $alert.removeClass('hidden bg-emerald-50 text-emerald-800 border-emerald-200')
                      .addClass('bg-rose-50 text-rose-800 border-rose-200')
                      .text('সার্ভারের সাথে যোগাযোগে ত্রুটি ঘটেছে।');
            }
        });
    });

    // Open Ticket Details Modal
    window.openTicketDetails = function(ticketId) {
        $('#detailTicketNo').text('লোড হচ্ছে...');
        $('#detailTicketSubject').text('অনুগ্রহ করে অপেক্ষা করুন...');
        $('#detailSenderName').text('');
        $('#detailCreatedAt').text('');
        $('#detailMessage').text('');
        $('#repliesList').empty();
        $('#replyTicketId').val(ticketId);
        $('#replyMessageInput').val('');

        $('#ticketDetailsModal').modal('show');

        $.ajax({
            type: 'GET',
            url: 'support/details/' + ticketId,
            dataType: 'json',
            success: function(res) {
                if (!res.ok) {
                    alert(res.msg || 'টিকিট লোড করতে সমস্যা হয়েছে।');
                    $('#ticketDetailsModal').modal('hide');
                    return;
                }

                let t = res.ticket;
                $('#detailTicketNo').text(t.ticket_no);
                $('#detailTicketSubject').text(t.subject);
                $('#detailSenderName').text(t.user_name || 'ইউজার');
                $('#detailCreatedAt').text(t.created_at);
                $('#detailMessage').text(t.message);

                let statusBadge = '';
                if (t.status === 'open') {
                    statusBadge = '<span class="bg-rose-50 text-rose-700 border border-rose-200 px-2 py-0.5 rounded text-[10px] font-bold">উন্মুক্ত</span>';
                } else if (t.status === 'pending') {
                    statusBadge = '<span class="bg-amber-50 text-amber-700 border border-amber-200 px-2 py-0.5 rounded text-[10px] font-bold">অপেক্ষমাণ</span>';
                } else if (t.status === 'resolved') {
                    statusBadge = '<span class="bg-emerald-50 text-emerald-700 border border-emerald-200 px-2 py-0.5 rounded text-[10px] font-bold">সমাধানকৃত</span>';
                } else {
                    statusBadge = '<span class="bg-neutral-100 text-neutral-600 border border-neutral-200 px-2 py-0.5 rounded text-[10px] font-bold">বন্ধ</span>';
                }
                $('#detailTicketStatus').html(statusBadge);

                // Render replies
                let repliesHtml = '';
                if (Array.isArray(res.replies) && res.replies.length > 0) {
                    res.replies.forEach(function(r) {
                        let isStaff = (r.sender_role === 'staff');
                        if (isStaff) {
                            repliesHtml += `
                                <div class="bg-indigo-50/70 rounded-2xl p-4 border border-indigo-100 shadow-2xs space-y-2">
                                    <div class="flex items-center justify-between text-[11px] pb-2 border-b border-indigo-100/80">
                                        <div class="flex items-center gap-1.5 font-bold text-indigo-900">
                                            <div class="w-5 h-5 rounded bg-indigo-600 text-white flex items-center justify-center text-[10px]">
                                                <i class="ph-bold ph-shield-check"></i>
                                            </div>
                                            <span>${escapeHtml(r.sender_name || 'সাপোর্ট স্টাফ')}</span>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-600 text-white">সাপোর্ট টিম</span>
                                        </div>
                                        <span class="text-indigo-400 font-mono text-[10px]">${escapeHtml(r.created_at)}</span>
                                    </div>
                                    <div class="text-xs text-indigo-950 leading-relaxed whitespace-pre-wrap">${escapeHtml(r.message)}</div>
                                </div>
                            `;
                        } else {
                            repliesHtml += `
                                <div class="bg-white rounded-2xl p-4 border border-neutral-200 shadow-2xs space-y-2">
                                    <div class="flex items-center justify-between text-[11px] pb-2 border-b border-neutral-100">
                                        <div class="flex items-center gap-1.5 font-bold text-neutral-800">
                                            <i class="ph-bold ph-user-circle text-sm text-neutral-500"></i>
                                            <span>${escapeHtml(r.sender_name || 'ইউজার')}</span>
                                        </div>
                                        <span class="text-neutral-400 font-mono text-[10px]">${escapeHtml(r.created_at)}</span>
                                    </div>
                                    <div class="text-xs text-neutral-700 leading-relaxed whitespace-pre-wrap">${escapeHtml(r.message)}</div>
                                </div>
                            `;
                        }
                    });
                }
                $('#repliesList').html(repliesHtml);

                // Scroll to bottom of conversation
                setTimeout(function() {
                    let container = document.getElementById('conversationContainer');
                    if (container) {
                        container.scrollTop = container.scrollHeight;
                    }
                }, 100);
            },
            error: function() {
                alert('সার্ভার থেকে তথ্য আনা যায়নি।');
            }
        });
    };

    // Reply Form Submit
    $('#replyForm').on('submit', function(e) {
        e.preventDefault();
        let $btn = $('#sendReplyBtn');
        let $msgInput = $('#replyMessageInput');
        let msg = $msgInput.val().trim();
        let ticketId = $('#replyTicketId').val();

        if (!msg || !ticketId) {
            return;
        }

        $btn.prop('disabled', true).html('<i class="ph-bold ph-spinner animate-spin text-sm"></i>');

        $.ajax({
            type: 'POST',
            url: 'support/reply',
            data: {
                ticket_id: ticketId,
                message: msg
            },
            dataType: 'json',
            success: function(res) {
                $btn.prop('disabled', false).html('<i class="ph-bold ph-paper-plane-tilt text-sm"></i> <span>উত্তর পাঠান</span>');
                if (res.ok) {
                    $msgInput.val('');
                    // Append reply directly
                    let newReplyHtml = `
                        <div class="bg-white rounded-2xl p-4 border border-neutral-200 shadow-2xs space-y-2 animate-in fade-in duration-200">
                            <div class="flex items-center justify-between text-[11px] pb-2 border-b border-neutral-100">
                                <div class="flex items-center gap-1.5 font-bold text-neutral-800">
                                    <i class="ph-bold ph-user-circle text-sm text-neutral-500"></i>
                                    <span>${escapeHtml(res.sender_name || 'ইউজার')}</span>
                                </div>
                                <span class="text-neutral-400 font-mono text-[10px]">${escapeHtml(res.created_at)}</span>
                            </div>
                            <div class="text-xs text-neutral-700 leading-relaxed whitespace-pre-wrap">${escapeHtml(msg)}</div>
                        </div>
                    `;
                    $('#repliesList').append(newReplyHtml);

                    let container = document.getElementById('conversationContainer');
                    if (container) {
                        container.scrollTop = container.scrollHeight;
                    }
                } else {
                    alert(res.msg || 'উত্তর পাঠাতে ব্যর্থ হয়েছে।');
                }
            },
            error: function() {
                $btn.prop('disabled', false).html('<i class="ph-bold ph-paper-plane-tilt text-sm"></i> <span>উত্তর পাঠান</span>');
                alert('সার্ভারে যোগাযোগ করা যায়নি।');
            }
        });
    });

    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
</script>
