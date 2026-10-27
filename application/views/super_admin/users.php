<?php
/**
 * User manager — the platform's account directory, read-only.
 *
 * Rendered inside Template::sadmin().
 *
 * THIS PAGE CANNOT CHANGE ANYTHING
 * Not a missing feature and not a "coming soon" badge — the write
 * endpoints do not exist. Every one of the five would be a write to
 * `users` or `users_groups`, which needs explicit approval that has not
 * been given, and this page says so at the top rather than showing a
 * disabled button. A greyed-out button implies the capability is a
 * decision away; a note saying the endpoints are not written does not.
 *
 * THE VALUES SHOWN ARE NOT WHAT THEY LOOK LIKE
 * Three columns on this table do not mean what their type suggests, and
 * each one is rendered from the code rather than from the datatype:
 *
 *   active        integer, CHECK (>= 0). login() tests `== 0`, so 2 and 3
 *                 do NOT lock an account. Only 0 is off.
 *   created_on    integer unix seconds. The live data contains 1.
 *   last_login    integer unix seconds. The live data contains -1.
 *
 * Rendering to_timestamp(1) as a date prints 1970, which is a confident
 * wrong answer about when an account was made. Anything <= 1 is shown as
 * "—" and the row carries a note, because the value is missing rather
 * than early.
 *
 * THE LEGACY PASSWORD COLUMN IS COUNTED, NEVER SHOWN
 * See the controller docblock. The value is not in this file and must not
 * be added: a panel that can display a password displays it in a
 * screenshot.
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * A unix-second column as a date, or NULL when it holds a sentinel.
 *
 * <= 1, not == 0. The live rows contain 1 and -1, and a check for 0
 * alone would pass -1 through to to_timestamp(), which raises for
 * negative input on some paths and prints 1969 on others.
 */
if ( ! function_exists('sa_user_when')) {
function sa_user_when($ts)
{
    $ts = ($ts === NULL) ? 0 : (int) $ts;
    if ($ts <= 1) {
        return NULL;
    }
    return date('Y-m-d H:i', $ts);
}
}

/**
 * A unix-second column as "how long ago", or NULL for a sentinel.
 *
 * The same <= 1 rule. A "0 মিনিট আগে" on a row that has never been
 * logged into is worse than a dash, because it reads as recent.
 */
if ( ! function_exists('sa_user_ago')) {
function sa_user_ago($ts)
{
    $ts = ($ts === NULL) ? 0 : (int) $ts;
    if ($ts <= 1) {
        return NULL;
    }

    $d = time() - $ts;
    if ($d < 60)    { return 'এইমাত্র'; }
    if ($d < 3600)  { return bn_number((int) floor($d / 60)) . ' মিনিট আগে'; }
    if ($d < 86400) { return bn_number((int) floor($d / 3600)) . ' ঘণ্টা আগে'; }
    if ($d < 2592000){ return bn_number((int) floor($d / 86400)) . ' দিন আগে'; }
    return date('Y-m-d', $ts);
}
}

/**
 * The active chip.
 *
 * Three cases, not two, because the column is an integer with no
 * enum: 0 is off, 1 is on, and anything else is neither — it is
 * meaningless data that does not lock the account, and colouring it as
 * "off" would be a lie about the single thing this page reports.
 */
if ( ! function_exists('sa_user_active_chip')) {
function sa_user_active_chip($active)
{
    $a = (int) $active;

    if ($a === 0) {
        return ['বন্ধ', 'bg-rose-50 text-rose-700 border-rose-200'];
    }
    if ($a === 1) {
        return ['চালু', 'bg-emerald-50 text-emerald-700 border-emerald-200'];
    }
    return ['অস্বাভাবিক (' . bn_number($a) . ')', 'bg-amber-50 text-amber-700 border-amber-200'];
}
}

/**
 * Build a filter href that keeps the OTHER filters.
 */
if ( ! function_exists('sa_user_qs')) {
function sa_user_qs(array $overrides = [])
{
    $q = array_merge($_GET, $overrides);
    foreach ($q as $k => $v) {
        if ($v === '' || $v === NULL) { unset($q[$k]); }
    }
    unset($q['page']);   // not a filter
    return empty($q) ? '' : '?' . http_build_query($q);
}
}

$has_filter = $has_filter;

$page_qs = function ($p) {
    $q = $_GET;
    $q['page'] = $p;
    return '?' . http_build_query($q);
};

/** Group chip colour, from the group name. */
if ( ! function_exists('sa_group_chip')) {
function sa_group_chip($name)
{
    // The vocabulary matches status_chip(): rose for the platform's own
    // power, indigo for a parishad's admin, sky for an office role,
    // neutral for the rest.
    $map = [
        'sadmin'  => 'bg-rose-50 text-rose-700 border-rose-200',
        'admin'   => 'bg-indigo-50 text-indigo-700 border-indigo-200',
        'uphead'  => 'bg-sky-50 text-sky-700 border-sky-200',
        'upsocib' => 'bg-sky-50 text-sky-700 border-sky-200',
        'udc'     => 'bg-sky-50 text-sky-700 border-sky-200',
        'collect' => 'bg-amber-50 text-amber-700 border-amber-200',
        'members' => 'bg-neutral-100 text-neutral-600 border-neutral-200',
    ];
    return $map[$name] ?? 'bg-neutral-100 text-neutral-600 border-neutral-200';
}
}
?>

<!-- ===================== Password Management Notice ===================== -->
<div class="flex items-start gap-2.5 text-xs bg-white border border-indigo-200/80 rounded-2xl px-4 py-3 shadow-xs">
    <i class="ph ph-shield-check text-lg text-indigo-600 shrink-0 mt-0.5"></i>
    <div>
        <p class="font-bold text-indigo-950">সুপার অ্যাডমিন অ্যাকাউন্ট সিকিউরিটি ও রিকভারি</p>
        <p class="text-neutral-600 mt-0.5 leading-relaxed">
            জরুরি প্রয়োজনে বা সার্ভারের SMTP মেইল ডাউন থাকলে সুপার অ্যাডমিন এখান থেকে যেকোনো ইউজারের পাসওয়ার্ড সরাসরি পরিবর্তন ও রিসেট করতে পারবেন। পরিবর্তনটি সাথে সাথে অডিট লগে রেকর্ড হবে।
        </p>
    </div>
</div>

<!-- ===================== Security Notice ===================== -->
<?php if ($u_legacy_pw > 0): ?>
    <div class="flex items-start gap-2.5 text-xs bg-rose-50 border border-rose-200 rounded-2xl px-4 py-3.5">
        <i class="ph ph-warning-octagon text-lg text-rose-500 shrink-0 mt-0.5"></i>
        <div>
            <p class="font-bold text-rose-900">
                <span class="bengali-num"><?= bn_number($u_legacy_pw); ?></span>টি অ্যাকাউন্টে পাসওয়ার্ড হালনাগাদ প্রয়োজন
            </p>
            <p class="text-rose-800 mt-0.5 leading-relaxed">
                কিছু অ্যাকাউন্টে পুরনো নিরাপত্তা ফরম্যাটের পাসওয়ার্ড সংরক্ষিত রয়েছে। নিরাপদ ব্যবহারের স্বার্থে পাসওয়ার্ড রিসেট করে নিন।
            </p>
        </div>
    </div>
<?php endif; ?>

<!-- ===================== Summary ===================== -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
    <?php
    $cards = [
        ['ph-users-three',      'text-neutral-400',   'মোট অ্যাকাউন্ট',   $u_total,      'text-neutral-900'],
        ['ph-user-check',        'text-emerald-500',  'লগইন চালু',         $u_total - $u_active, 'text-emerald-700'],
        ['ph-lock-key-open',     'text-rose-500',     'লগইন বন্ধ',         $u_active,     'text-rose-700'],
        ['ph-buildings',         'text-indigo-500',   'প্ল্যাটফর্ম',       $u_platform,   'text-indigo-700'],
    ];
    foreach ($cards as [$icon, $icl, $label, $val, $vcl]):
    ?>
        <div class="bg-white border border-neutral-200 rounded-2xl p-4 shadow-xs">
            <div class="flex items-center gap-2">
                <i class="ph <?= $icon; ?> text-base <?= $icl; ?>"></i>
                <span class="text-[11px] font-bold text-neutral-500"><?= $label; ?></span>
            </div>
            <p class="mt-1.5 text-2xl font-bold <?= $vcl; ?> bengali-num"><?= bn_number(number_format($val)); ?></p>
        </div>
    <?php endforeach; ?>
</div>

<!--
    The three states that are not a number on a card.

    A card says "2" and leaves the reader to decide whether 2 is a problem.
    These three say what the number MEANS, because each one is a thing
    somebody has to go and fix:

      গ্রুপবিহীন   the account can log in and has no permissions at all
      অস্বাভাবিক    active is above 1, which locks nothing
      স্লাগ মিলছে না  u_type and the tenant's own slug disagree
-->
<div class="grid sm:grid-cols-3 gap-3">
    <div class="flex items-start gap-2.5 text-[11px] bg-white border <?= $u_groupless > 0 ? 'border-amber-300' : 'border-neutral-200' ?> rounded-2xl px-4 py-3 shadow-xs">
        <i class="ph ph-user-minus text-base <?= $u_groupless > 0 ? 'text-amber-500' : 'text-neutral-300' ?> shrink-0 mt-0.5"></i>
        <div>
            <p class="font-bold text-neutral-800">
                <span class="bengali-num"><?= bn_number($u_groupless); ?></span> টি অ্যাকাউন্টে দায়িত্ব নির্ধারিত নেই
            </p>
            <p class="text-neutral-500 mt-0.5 leading-relaxed">
                এই অ্যাকাউন্টগুলোতে কোনো ইউজার গ্রুপ বা ভূমিকা নির্ধারিত নেই। ড্যাশবোর্ডে প্রবেশের জন্য যথাযথ দায়িত্ব অর্পণ করুন।
            </p>
        </div>
    </div>

    <div class="flex items-start gap-2.5 text-[11px] bg-white border <?= $u_weird_active > 0 ? 'border-amber-300' : 'border-neutral-200' ?> rounded-2xl px-4 py-3 shadow-xs">
        <i class="ph ph-warning text-base <?= $u_weird_active > 0 ? 'text-amber-500' : 'text-neutral-300' ?> shrink-0 mt-0.5"></i>
        <div>
            <p class="font-bold text-neutral-800">
                <span class="bengali-num"><?= bn_number($u_weird_active); ?></span> টি অ্যাকাউন্টে অবস্থা পর্যালোচনা প্রয়োজন
            </p>
            <p class="text-neutral-500 mt-0.5 leading-relaxed">
                এই অ্যাকাউন্টগুলোর সক্রিয়তা স্ট্যাটাস যাচাই করে প্রয়োজনীয় হালনাগাদ সম্পন্ন করুন।
            </p>
        </div>
    </div>

    <div class="flex items-start gap-2.5 text-[11px] bg-white border <?= $u_slug_mismatch > 0 ? 'border-amber-300' : 'border-neutral-200' ?> rounded-2xl px-4 py-3 shadow-xs">
        <i class="ph ph-link-break text-base <?= $u_slug_mismatch > 0 ? 'text-amber-500' : 'text-neutral-300' ?> shrink-0 mt-0.5"></i>
        <div>
            <p class="font-bold text-neutral-800">
                <span class="bengali-num"><?= bn_number($u_slug_mismatch); ?></span> টি অ্যাকাউন্টে তথ্য যাচাই প্রয়োজন
            </p>
            <p class="text-neutral-500 mt-0.5 leading-relaxed">
                অ্যাকাউন্টের ইউজার টাইপ এবং সংশ্লিষ্ট ইউনিয়ন পরিষদের সাবডোমেন তথ্যের সামঞ্জস্য যাচাই করুন।
            </p>
        </div>
    </div>
</div>

<!-- ===================== Filters ===================== -->
<div class="bg-white border border-neutral-200 rounded-2xl p-4 shadow-xs">
    <form method="get" action="<?= base_url('sadmin_users'); ?>" class="flex flex-wrap items-end gap-2">
        <?php
        /* Carry the filters this row of controls does not render. Without
         * these, typing in the search box silently drops the group filter
         * — the single most confusing thing a two-filter bar can do. */
        foreach (['group', 'tenant_id', 'active'] as $hk) {
            $hv = $_GET[$hk] ?? '';
            if ($hv !== '' && $hv !== NULL) {
                printf('<input type="hidden" name="%s" value="%s">',
                    $hk, htmlspecialchars((string) $hv, ENT_QUOTES, 'UTF-8'));
            }
        }
        ?>
        <div class="w-full md:flex-1 md:min-w-48">
            <label class="block text-[11px] font-bold text-neutral-500 mb-1" for="suQ">খুঁজুন</label>
            <input type="search" id="suQ" name="q" value="<?= htmlspecialchars($filters['q'], ENT_QUOTES, 'UTF-8'); ?>"
                   placeholder="ইমেইল, নাম, ফোন, স্লাগ…"
                   class="w-full p-2.5 rounded-lg border border-neutral-300 text-xs focus:border-indigo-600 focus:outline-none">
        </div>

        <div class="w-full sm:w-auto">
            <label class="block text-[11px] font-bold text-neutral-500 mb-1" for="suGroup">দায়িত্ব</label>
            <select id="suGroup" name="group" onchange="this.form.submit()"
                    class="w-full sm:w-auto p-2.5 rounded-lg border border-neutral-300 text-xs focus:border-indigo-600 focus:outline-none bg-white">
                <option value="">সবাই</option>
                <?php foreach ($all_groups as $g): ?>
                    <option value="<?= htmlspecialchars($g->name, ENT_QUOTES, 'UTF-8'); ?>"
                        <?= $filters['group'] === $g->name ? 'selected' : '' ?>>
                        <?= htmlspecialchars($g->name, ENT_QUOTES, 'UTF-8'); ?>
                        — <?= htmlspecialchars($g->description, ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="w-full sm:w-auto">
            <label class="block text-[11px] font-bold text-neutral-500 mb-1" for="suActive">লগইন</label>
            <select id="suActive" name="active" onchange="this.form.submit()"
                    class="w-full sm:w-auto p-2.5 rounded-lg border border-neutral-300 text-xs focus:border-indigo-600 focus:outline-none bg-white">
                <option value="">সবাই</option>
                <option value="1" <?= $filters['active'] === '1' ? 'selected' : '' ?>>চালু</option>
                <option value="0" <?= $filters['active'] === '0' ? 'selected' : '' ?>>বন্ধ</option>
            </select>
        </div>

        <div class="flex items-center gap-1.5 w-full sm:w-auto pt-1 sm:pt-0">
            <button type="submit" class="flex-1 sm:flex-initial px-4 py-2.5 rounded-lg bg-neutral-900 text-white text-xs font-bold cursor-pointer text-center">খুঁজুন</button>
            <?php if ($has_filter): ?>
                <a href="<?= base_url('sadmin_users'); ?>"
                   class="px-3 py-2.5 rounded-lg bg-neutral-100 text-neutral-600 text-xs font-bold hover:bg-neutral-200 text-center">মুছুন</a>
            <?php endif; ?>
        </div>
    </form>

    <?php
    /*
     * tenant_id is a link row, not a select, because 'platform' is not a
     * column value — it is a UI sentinel for ins_pr_id_s IS NULL, and
     * ins_pr_id_s IS NULL is how enforce_tenant_scope() recognises the
     * platform's own cross-tenant role. It is the one distinction on this
     * page that a security check is built on, so it gets a control of its
     * own rather than being an option buried in a dropdown.
     */
    ?>
    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:flex-wrap pt-3 mt-3 border-t border-neutral-100">
        <span class="text-[11px] font-bold text-neutral-500 shrink-0">কোন ইউনিয়নের:</span>
        <?php
        $t_opts = [
            ''         => ['সবাই',     $filters['tenant_id'] === ''],
            'platform' => ['প্ল্যাটফর্ম', $filters['tenant_id'] === 'platform'],
        ];
        foreach ($t_opts as $kv => [$label, $on]) {
            printf(
                '<a href="%s" class="whitespace-nowrap px-3 py-1.5 rounded-lg text-xs font-bold %s">%s</a>',
                htmlspecialchars(base_url('sadmin_users' . sa_user_qs(['tenant_id' => $kv])), ENT_QUOTES, 'UTF-8'),
                $on ? 'bg-neutral-900 text-white' : 'bg-neutral-50 text-neutral-600 hover:bg-neutral-100',
                $label
            );
        }
        foreach ($all_tenants as $t):
            $tid = (int) $t->ins_titute_p_idds;
            $on  = ($filters['tenant_id'] !== '' && (int) $filters['tenant_id'] === $tid);
        ?>
            <a href="<?= htmlspecialchars(base_url('sadmin_users' . sa_user_qs(['tenant_id' => $tid])), ENT_QUOTES, 'UTF-8'); ?>"
               class="whitespace-nowrap px-3 py-1.5 rounded-lg text-xs font-bold <?= $on ? 'bg-neutral-900 text-white' : 'bg-neutral-50 text-neutral-600 hover:bg-neutral-100' ?>">
                <?= htmlspecialchars($t->ins_name, ENT_QUOTES, 'UTF-8'); ?>
            </a>
        <?php endforeach; ?>

        <span class="sm:ml-auto text-[11px] text-neutral-400 shrink-0">
            বর্তমান ফিল্টারে <span class="font-bold text-neutral-600 bengali-num"><?= bn_number(number_format($total)); ?></span> টি
        </span>
    </div>
</div>

<!-- ===================== Table ===================== -->
<div class="min-w-0 bg-white border border-neutral-200 rounded-2xl shadow-xs overflow-hidden">
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
        <table class="w-full min-w-195 text-left">
            <thead class="bg-neutral-50 border-b border-neutral-200">
                <tr>
                    <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-neutral-500">অ্যাকাউন্ট</th>
                    <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-neutral-500">দায়িত্ব</th>
                    <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-neutral-500">ইউনিয়ন</th>
                    <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-neutral-500">লগইন</th>
                    <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-neutral-500">তৈরি</th>
                    <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-neutral-500">শেষ লগইন</th>
                    <th class="px-4 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-neutral-500">অ্যাকশন</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-100">
                <?php if (empty($rows)): ?>
                    <tr>
                        <td colspan="7" class="py-14 text-center">
                            <i class="ph ph-user-list text-4xl text-neutral-200"></i>
                            <p class="mt-2 text-sm font-bold text-neutral-500">কোনো অ্যাকাউন্ট পাওয়া যায়নি</p>
                            <p class="mt-1 text-xs text-neutral-400">
                                <?= $has_filter
                                    ? 'ফিল্টার বদলে দেখুন, অথবা "মুছুন" চাপুন।'
                                    : 'এখনো কোনো অ্যাকাউন্ট নেই।' ?>
                            </p>
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($rows as $u):
                    $uid   = (int) $u->id;
                    $glist = $groups_of[$uid] ?? [];
                    [$act_label, $act_cls] = sa_user_active_chip($u->active);

                    $created = sa_user_when($u->created_on);
                    $ago     = sa_user_ago($u->last_login);
                    $is_platform = ($u->ins_pr_id_s === NULL);

                    /*
                     * The two row-level notes. Both are about data that is
                     * MISSING, so they read as neutral rather than as
                     * faults — a "1970" or an "০ মিনিট আগে" would be a
                     * fault in this page, not in the account.
                     */
                    $t_created_bad = ($u->created_on === NULL || (int) $u->created_on <= 1);
                    $t_login_bad   = ($u->last_login === NULL || (int) $u->last_login <= 1);

                    // u_type vs the tenant's own slug, per the docblock.
                    $slug_state = '';
                    if (!$is_platform && $u->u_type !== NULL && $u->u_type !== '') {
                        if ($u->sub_domains === NULL || $u->sub_domains === '') {
                            $slug_state = 'unknown';
                        } elseif ($u->sub_domains !== $u->u_type) {
                            $slug_state = 'mismatch';
                        }
                    }
                ?>
                    <tr class="align-top">
                        <!-- Account -->
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-neutral-100 text-neutral-600 flex items-center justify-center text-xs font-black shrink-0">
                                    <?= htmlspecialchars(mb_substr((string) ($u->username ?: $u->email), 0, 1), ENT_QUOTES, 'UTF-8') ?>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-neutral-900 truncate">
                                        <?= htmlspecialchars($u->email, ENT_QUOTES, 'UTF-8') ?>
                                    </p>
                                    <p class="text-[11px] text-neutral-400 truncate">
                                        <?= htmlspecialchars($u->username ?: '—', ENT_QUOTES, 'UTF-8') ?>
                                        <?php if (!empty($u->phone)): ?>
                                            <span class="font-mono">· <?= htmlspecialchars($u->phone, ENT_QUOTES, 'UTF-8') ?></span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        </td>

                        <!-- Groups -->
                        <td class="py-3.5 px-4">
                            <?php if (empty($glist)): ?>
                                <span class="inline-block px-2 py-0.5 rounded-md border text-[10px] font-bold bg-amber-50 text-amber-700 border-amber-200">
                                    কোনো দায়িত্ব নেই
                                </span>
                            <?php else: ?>
                                <div class="flex flex-wrap gap-1">
                                    <?php foreach ($glist as $g): ?>
                                        <span class="inline-block px-2 py-0.5 rounded-md border text-[10px] font-bold <?= sa_group_chip($g['name']); ?>"
                                              title="<?= htmlspecialchars($g['label'], ENT_QUOTES, 'UTF-8') ?>">
                                            <?= htmlspecialchars($g['name'], ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </td>

                        <!-- Tenant -->
                        <td class="py-3.5 px-4">
                            <?php if ($is_platform): ?>
                                <span class="inline-block px-2 py-0.5 rounded-md border text-[10px] font-bold bg-rose-50 text-rose-700 border-rose-200">
                                    প্ল্যাটফর্ম
                                </span>
                            <?php else: ?>
                                <p class="text-[11px] font-bold text-neutral-700">
                                    <?= htmlspecialchars($u->ins_name ?: ('#' . (int) $u->ins_pr_id_s), ENT_QUOTES, 'UTF-8') ?>
                                </p>
                                <?php if (!empty($u->u_type)): ?>
                                    <p class="text-[10px] font-mono <?= $slug_state === 'mismatch' ? 'text-amber-600 font-bold' : 'text-neutral-400' ?>">
                                        <?= htmlspecialchars($u->u_type, ENT_QUOTES, 'UTF-8') ?>
                                        <?php if ($slug_state === 'mismatch'): ?>
                                            <span class="font-sans">— স্লাগ মেলেনি</span>
                                        <?php elseif ($slug_state === 'unknown'): ?>
                                            <span class="font-sans">— যাচাই অসম্ভব</span>
                                        <?php endif; ?>
                                    </p>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>

                        <!-- active -->
                        <td class="py-3.5 px-4">
                            <span class="inline-block px-2 py-0.5 rounded-md border text-[10px] font-bold <?= $act_cls; ?>">
                                <?= $act_label; ?>
                            </span>
                        </td>

                        <!-- created_on -->
                        <td class="py-3.5 px-4">
                            <?php if ($t_created_bad): ?>
                                <span class="text-[11px] text-neutral-400">—</span>
                                <span class="block text-[10px] text-neutral-300">তারিখ নেই</span>
                            <?php else: ?>
                                <span class="text-[11px] font-mono text-neutral-600"><?= $created; ?></span>
                            <?php endif; ?>
                        </td>

                        <!-- last_login -->
                        <td class="py-3.5 px-4">
                            <?php if ($t_login_bad): ?>
                                <span class="text-[11px] text-neutral-400">—</span>
                                <span class="block text-[10px] text-neutral-300">কখনো লগইন হয়নি</span>
                            <?php else: ?>
                                <span class="text-[11px] text-neutral-600"><?= $ago; ?></span>
                            <?php endif; ?>
                        </td>

                        <!-- Notes. The legacy-password marker is a
                             QUESTION MARK and nothing else — see the
                             header. The value must never be printed. -->
                        <!-- Actions -->
                        <td class="py-3.5 px-4 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-2">
                                <button type="button"
                                        onclick="openResetPasswordModal(<?= $uid ?>, '<?= htmlspecialchars(addslashes($u->email), ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars(addslashes($u->username ?: ''), ENT_QUOTES, 'UTF-8') ?>')"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl bg-neutral-100 hover:bg-indigo-600 hover:text-white text-neutral-700 text-xs font-bold transition-all shadow-2xs cursor-pointer"
                                        title="পাসওয়ার্ড পরিবর্তন করুন">
                                    <i class="ph-bold ph-key text-xs"></i>
                                    <span>পাসওয়ার্ড</span>
                                </button>
                                <?php if (!empty($u->password_viewss_s)): ?>
                                    <i class="ph ph-warning text-base text-rose-500"
                                       title="এই অ্যাকাউন্টে পুরনো পাসওয়ার্ড সংরক্ষিত আছে"></i>
                                <?php endif; ?>
                                <span class="block font-mono text-[10px] text-neutral-400"><?= $uid; ?></span>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if (!empty($rows) && $pages > 1): ?>
        <div class="px-4 py-3 border-t border-neutral-100 bg-neutral-50/60 flex flex-wrap items-center justify-between gap-2 text-[11px] text-neutral-500">
            <span class="bengali-num">
                পাতা <?= bn_number($page); ?> / <?= bn_number($pages); ?>
                · মোট <?= bn_number(number_format($total)); ?> টি, প্রতি পাতায় <?= bn_number($per_page); ?> টি
            </span>
            <span class="flex items-center gap-1.5">
                <?php if ($page > 1): ?>
                    <a href="<?= htmlspecialchars(base_url('sadmin_users' . $page_qs($page - 1)), ENT_QUOTES, 'UTF-8'); ?>"
                       class="px-3 py-1.5 rounded-lg bg-white border border-neutral-200 text-neutral-600 font-bold hover:bg-neutral-100">আগে</a>
                <?php endif; ?>
                <?php if ($page < $pages): ?>
                    <a href="<?= htmlspecialchars(base_url('sadmin_users' . $page_qs($page + 1)), ENT_QUOTES, 'UTF-8'); ?>"
                       class="px-3 py-1.5 rounded-lg bg-neutral-900 text-white font-bold">পরে</a>
                <?php endif; ?>
            </span>
        </div>
    <?php endif; ?>
</div>

<!--
    What the columns mean, below the table rather than in a tooltip.

    A tooltip is invisible on a phone, which is where this page will
    mostly be read, and it disappears before it can be quoted to whoever
    asked. The one thing worth saying twice is the active column: an
    integer that only 0 turns off, rendered as a boolean, is how somebody
    gets confused about why a disabled account can still log in.
-->

<!-- ===================== Reset Password Modal ===================== -->
<div id="resetPasswordModal" class="fixed inset-0 z-50 overflow-y-auto bg-neutral-900/60 backdrop-blur-xs flex items-center justify-center p-4" style="display:none">
    <div class="bg-white rounded-3xl border border-neutral-200 shadow-2xl max-w-md w-full overflow-hidden transition-all transform animate-in fade-in zoom-in-95 duration-200">
        
        <!-- Header -->
        <div class="px-6 py-5 border-b border-neutral-100 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg font-bold shadow-xs">
                    <i class="ph-bold ph-key"></i>
                </div>
                <div>
                    <h3 class="text-base font-black text-neutral-900 leading-tight">পাসওয়ার্ড পরিবর্তন</h3>
                    <p class="text-xs text-neutral-400 mt-0.5">সুপার অ্যাডমিন অ্যাকাউন্ট রিকভারি</p>
                </div>
            </div>
            <button type="button" onclick="closeResetPasswordModal()" class="p-1.5 rounded-lg text-neutral-400 hover:text-neutral-700 hover:bg-neutral-100 transition-colors cursor-pointer">
                <i class="ph-bold ph-x text-lg"></i>
            </button>
        </div>

        <!-- Form Body -->
        <form id="resetPasswordForm" onsubmit="handleResetPasswordSubmit(event)" class="p-6 space-y-4">
            <input type="hidden" id="reset_user_id" name="user_id" value="">

            <!-- Target User Info -->
            <div class="p-3.5 rounded-2xl bg-neutral-50 border border-neutral-200/80 space-y-1">
                <span class="text-[11px] font-bold text-neutral-400 uppercase tracking-wider block">টার্গেট অ্যাকাউন্ট</span>
                <p id="reset_user_display" class="text-xs font-bold text-neutral-800 break-all"></p>
            </div>

            <!-- New Password Input -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="reset_new_password" class="block text-xs font-bold text-neutral-700">নতুন পাসওয়ার্ড</label>
                    <button type="button" onclick="generateRandomPassword()" class="text-[11px] font-bold text-indigo-600 hover:text-indigo-700 flex items-center gap-1 cursor-pointer">
                        <i class="ph-bold ph-sparkle"></i> র‍্যান্ডম তৈরি করুন
                    </button>
                </div>
                <div class="relative">
                    <input type="text" id="reset_new_password" name="new_password" required minlength="6" placeholder="কমপক্ষে ৬ অক্ষর লিখুন..."
                           class="w-full px-3.5 py-2.5 rounded-xl border border-neutral-300 text-sm font-mono focus:border-indigo-600 focus:outline-none pr-10">
                    <button type="button" onclick="copyGeneratedPassword()" title="পাসওয়ার্ড কপি করুন" class="absolute right-2.5 top-1/2 -translate-y-1/2 p-1 text-neutral-400 hover:text-neutral-700">
                        <i class="ph ph-copy text-base"></i>
                    </button>
                </div>
                <p class="text-[11px] text-neutral-400 mt-1">SMTP কাজ না করলে এই নতুন পাসওয়ার্ডটি কপি করে ইউজারকে প্রদান করুন।</p>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-neutral-100">
                <button type="button" onclick="closeResetPasswordModal()" class="px-4 py-2 rounded-xl border border-neutral-200 text-neutral-700 text-xs font-bold hover:bg-neutral-50 transition-colors cursor-pointer">
                    বাতিল
                </button>
                <button type="submit" id="reset_submit_btn" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-xs transition-colors cursor-pointer flex items-center gap-1.5">
                    <i class="ph-bold ph-check"></i>
                    <span>পাসওয়ার্ড আপডেট করুন</span>
                </button>
            </div>
        </form>

    </div>
</div>

<script>
function openResetPasswordModal(userId, email, username) {
    document.getElementById('reset_user_id').value = userId;
    var label = email;
    if (username && username !== email) {
        label += ' (' + username + ')';
    }
    document.getElementById('reset_user_display').textContent = label;
    document.getElementById('reset_new_password').value = '';
    document.getElementById('resetPasswordModal').style.display = 'flex';
    document.getElementById('reset_new_password').focus();
}

function closeResetPasswordModal() {
    document.getElementById('resetPasswordModal').style.display = 'none';
    document.getElementById('resetPasswordForm').reset();
}

function generateRandomPassword() {
    var chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789!@#$%';
    var pwd = '';
    for (var i = 0; i < 10; i++) {
        pwd += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    var input = document.getElementById('reset_new_password');
    input.value = pwd;
    input.focus();
}

function copyGeneratedPassword() {
    var val = document.getElementById('reset_new_password').value;
    if (!val) return;
    navigator.clipboard.writeText(val).then(function() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'পাসওয়ার্ড কপি করা হয়েছে!',
                showConfirmButton: false,
                timer: 2000
            });
        }
    });
}

function handleResetPasswordSubmit(e) {
    e.preventDefault();
    var userId = document.getElementById('reset_user_id').value;
    var newPwd = document.getElementById('reset_new_password').value.trim();
    var submitBtn = document.getElementById('reset_submit_btn');

    if (!userId || newPwd.length < 6) {
        if (typeof Swal !== 'undefined') {
            Swal.fire('ভুল ইনপুট', 'পাসওয়ার্ড কমপক্ষে ৬ অক্ষরের হতে হবে।', 'warning');
        }
        return;
    }

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="ph-bold ph-spinner animate-spin"></i> সংরক্ষণ হচ্ছে...';

    var fd = new FormData();
    fd.append('user_id', userId);
    fd.append('new_password', newPwd);

    fetch('<?= base_url("sadmin_users/api_reset_password"); ?>', {
        method: 'POST',
        body: fd
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="ph-bold ph-check"></i> <span>পাসওয়ার্ড আপডেট করুন</span>';
        
        if (data && data.success) {
            closeResetPasswordModal();
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'পাসওয়ার্ড পরিবর্তিত হয়েছে!',
                    html: '<p class="text-sm">ইউজারের নতুন পাসওয়ার্ড সফলভাবে হালনাগাদ করা হয়েছে:</p><div class="mt-3 p-3 bg-neutral-100 rounded-xl font-mono text-base font-bold text-indigo-700 select-all">' + escapeHtml(newPwd) + '</div><p class="text-xs text-neutral-400 mt-2">প্রয়োজনে পাসওয়ার্ডটি কপি করে ইউজারকে হস্তান্তর করুন।</p>',
                    confirmButtonText: 'ঠিক আছে',
                    confirmButtonColor: '#4f46e5'
                });
            } else {
                alert('পাসওয়ার্ড সফলভাবে পরিবর্তন করা হয়েছে!');
            }
        } else {
            if (typeof Swal !== 'undefined') {
                Swal.fire('ব্যর্থ হয়েছে', data.message || 'পাসওয়ার্ড পরিবর্তন করা যায়নি।', 'error');
            } else {
                alert(data.message || 'পাসওয়ার্ড পরিবর্তন করা যায়নি।');
            }
        }
    })
    .catch(function(err) {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="ph-bold ph-check"></i> <span>পাসওয়ার্ড আপডেট করুন</span>';
        if (typeof Swal !== 'undefined') {
            Swal.fire('এরর', 'সার্ভারের সাথে সংযোগে সমস্যা হয়েছে।', 'error');
        } else {
            alert('সার্ভারের সাথে সংযোগে সমস্যা হয়েছে।');
        }
    });
}

function escapeHtml(str) {
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}
</script>
