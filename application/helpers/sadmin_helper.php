<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Super-admin panel helpers
 * =====================================================================
 *
 * Autoloaded (see config/autoload.php).
 *
 * WHY THE GATE IS A FUNCTION AND NOT A BASE CONTROLLER
 * ---------------------------------------------------
 * This panel is going to be six controllers, not one. CI3 has no base
 * controller in this project, and the alternative — pasting the
 * in_group('sadmin') check into six constructors — is precisely the
 * mistake AGENTS.md records for enforce_tenant_scope(): "Dashboard had
 * the idea and Sonod and Download did not". The fifth copy would be
 * the one that forgets, and a forgotten check on a platform panel is
 * not a cosmetic bug.
 *
 * The one thing to know before using it: require_sadmin() HALTS on
 * failure. It calls show_error() for an anonymous caller and for a
 * signed-in non-sadmin, and the difference matters when reading a
 * response:
 *
 *   - not logged in           -> show_error(403) with the 403 title
 *   - logged in, not sadmin   -> show_error(403) with the same title
 *
 * Both are a 403, deliberately. Distinguishing "you are not signed in"
 * from "you are signed in as the wrong role" in the body of a 403 is an
 * account-existence oracle, and a platform panel is exactly the place
 * where somebody would go looking for one.
 *
 * Note this is NOT the same gate as enforce_tenant_scope(), and the two
 * are not interchangeable:
 *
 *   enforce_tenant_scope()  asks "is this user reading their own
 *                           parishad's data?" and RETURNS QUIETLY for an
 *                           anonymous caller, because the tenant
 *                           controllers redirect instead.
 *   require_sadmin()        asks "may this caller reach the platform
 *                           panel at all?" and STOPS the request. There
 *                           is no such thing as an anonymous super
 *                           admin, so returning quietly would mean
 *                           continuing into the panel.
 *
 * A sadmin is allowed through enforce_tenant_scope() on purpose — that
 * is what keeps the panel's ?tenant=N switch working — so a sadmin
 * reaching a tenant controller is expected, not a hole.
 */

/**
 * Gate for every super-admin controller constructor.
 *
 * Call it as the LAST statement of the constructor, after any
 * $this->load->* that the controller needs in order to be useful. This
 * function does not return on failure, so nothing after it runs.
 *
 * @return void
 */
function require_sadmin()
{
	$CI =& get_instance();

	if (!isset($CI->ion_auth) || !$CI->ion_auth->logged_in())
	{
		// show_error() halts. This is the same 'refresh' redirect shape the
		// tenant controllers use for a 200-with-empty-body, but a 403 page
		// is the right answer here: there is no parishad to redirect to.
		show_error(
			'আপনার সুপার অ্যাডমিন প্যানেলে প্রবেশের অনুমতি নেই। শুধুমাত্র সেন্ট্রাল প্ল্যাটফর্ম সুপার অ্যাডমিনরাই এখানে প্রবেশ করতে পারেন।',
			403,
			'অনুমোদনহীন প্রবেশাধিকার'
		);
		return;
	}

	// 'sadmin' is the platform's own cross-tenant role. 'admin' is NOT it —
	// 'admin' is the group every parishad administrator holds, and
	// treating the two as one is how a tenant admin ends up in a panel
	// that lists every other tenant's holdings.
	if (!$CI->ion_auth->in_group('sadmin'))
	{
		show_error(
			'আপনার সুপার অ্যাডমিন প্যানেলে প্রবেশের অনুমতি নেই। শুধুমাত্র সেন্ট্রাল প্ল্যাটফর্ম সুপার অ্যাডমিনরাই এখানে প্রবেশ করতে পারেন।',
			403,
			'অনুমোদনহীন প্রবেশাধিকার'
		);
	}
}

/**
 * The panel's navigation, as a single source of truth.
 *
 * Returning it from a function rather than hardcoding the links in the
 * nav view is what keeps the sidebar honest: a new module is one array
 * entry, and the "am I on this page" highlight in sadmin_nav() is
 * derived from the same entry, so a link cannot point somewhere the
 * highlight disagrees with.
 *
 * @param  string $active  The first URI segment of the current request,
 *                        i.e. CI_URI::segment(1).
 * @return array           [ [segment, label, icon, badge], ... ]
 */
function sadmin_nav_items($active = '')
{
	return [
		['super_admin',          'ওভারভিউ',           'Overview',      'ph-gauge',                  NULL],
		['sadmin_tenants',       'ইউনিয়ন পরিষদ',     'Tenants',       'ph-buildings',              NULL],
		['sadmin_subscriptions', 'সাবস্ক্রিপশন',      'Subscriptions', 'ph-credit-card',          NULL],
		['sadmin_sonod',         'সনদ ম্যানেজার',    'Sonod Manager', 'ph-certificate',          NULL],
		['sadmin_users',         'ইউজার ম্যানেজার',  'Users',         'ph-users-three',            NULL],
		['sadmin_support',       'সাপোর্ট টিকিট',     'Support',       'ph-lifebuoy',              'tickets'],
		['sadmin_settings',      'সেটিংস',             'Settings',      'ph-gear-six',               NULL],
		['sadmin_audit',         'অডিট লগ',           'Audit Logs',    'ph-scroll',                 NULL],
	];
}

/**
 * The count badges shown next to the sidebar links.
 *
 * Open support tickets and subscriptions inside 30 days of expiry are
 * the two things a platform operator must not have to go looking for.
 * Both are counted here rather than in the nav view so the nav carries
 * no SQL — the view is loaded on every panel page and should not issue
 * the queries, and a count that can fail should not break navigation.
 *
 * @return array  ['tickets' => int, 'expiring' => int]
 */
function sadmin_nav_badges()
{
	$badges = ['tickets' => 0, 'expiring' => 0];

	$CI =& get_instance();
	if (!isset($CI->db))
	{
		return $badges;
	}

	// reset_query() first: a caller that arrived here on a half-built
	// query builder would otherwise have this count ANDed into it.
	$CI->db->reset_query();

	// "not yet closed" is an OR, and CI3's where() ANDs. The grouping is
	// required here: without it this counts nothing at all, which looks
	// exactly like "no tickets" on a panel that has plenty of them.
	$CI->db->group_start();
	$CI->db->where('status', 'open');
	$CI->db->or_where('status', 'pending');
	$CI->db->group_end();
	$badges['tickets'] = (int) $CI->db->count_all_results('support_tickets');

	// Expiry warnings are for a 30-day window. current_date, not
	// CURRENT_DATE: this codebase runs on CI3's query builder, and
	// the raw string keeps it out of hand-written SQL.
	$badges['expiring'] = (int) $CI->db->where('status', 'active')
		->where('end_date <=', date('Y-m-d', strtotime('+30 days')))
		->where('end_date >=', date('Y-m-d'))
		->count_all_results('subscriptions');

	$CI->db->reset_query();

	return $badges;
}

/**
 * Format a money amount in Bangladeshi taka with Bengali digits.
 *
 * The panel shows a lot of prices and dates, and the rest of the
 * project renders numbers in Bengali digits (portal.css sets
 * font-variant-numeric: tabular-nums and every public price is
 * ৳২৫,০০০). This is the one place that converts, so a price in the
 * panel and the same price on the pricing page are written the same way.
 *
 * @param  int|null $amount
 * @return string   ৳২৫,০০০ or ৳০ for a null amount.
 */
function taka($amount)
{
	$amount = (int) $amount;
	if ($amount === 0)
	{
		return '৳০';
	}
	return '৳' . bn_number(number_format($amount));
}

/**
 * Bengali digits for a numeric string, leaving every other character
 * alone.
 *
 * number_format() runs first so the thousands separators are ASCII and
 * this only has to handle 0-9 — a single pass, no locale map, and no
 * risk of mangling the separator.
 */
function bn_number($str)
{
	$map = ['0'=>'০','1'=>'১','2'=>'২','3'=>'৩','4'=>'৪','5'=>'৫','6'=>'৬','7'=>'৭','8'=>'৮','9'=>'৯'];
	return strtr((string) $str, $map);
}

/**
 * A human label for a subscription or ticket status, and the colour.
 *
 * Returned as a pair so the view never hardcodes a class for a status
 * and never invents a label — there is one mapping, and a new status
 * shows as its raw key rather than as an empty chip.
 *
 * @return array  [label, badge_class]
 */
function status_chip($status)
{
	$map = [
		'active'    => ['সক্রিয়',     'bg-emerald-50 text-emerald-700 border-emerald-200'],
		'expired'   => ['মেয়াদোত্তীর্ণ', 'bg-rose-50 text-rose-700 border-rose-200'],
		'cancelled' => ['বাতিল',      'bg-neutral-100 text-neutral-600 border-neutral-200'],
		'pending'   => ['অপেক্ষমাণ',    'bg-amber-50 text-amber-700 border-amber-200'],
		'open'      => ['খোলা',       'bg-sky-50 text-sky-700 border-sky-200'],
		'resolved'  => ['সমাধান হয়েছে', 'bg-emerald-50 text-emerald-700 border-emerald-200'],
		'closed'    => ['বন্ধ',       'bg-neutral-100 text-neutral-600 border-neutral-200'],
		// Column headers, not statuses. A total of 0 is worth noticing.
		'low'       => ['নিম্ন',       'bg-neutral-100 text-neutral-600 border-neutral-200'],
		'normal'    => ['সাধারণ',     'bg-neutral-100 text-neutral-600 border-neutral-200'],
		'high'      => ['উচ্চ',       'bg-amber-50 text-amber-700 border-amber-200'],
		'urgent'    => ['জরুরি',      'bg-rose-50 text-rose-700 border-rose-200'],
	];

	if (!isset($map[$status]))
	{
		return [$status, 'bg-neutral-100 text-neutral-600 border-neutral-200'];
	}

	return $map[$status];
}

/**
 * Read a request body as an array, whichever way it arrived.
 *
 * The panel's APIs are called two ways from the same view file: fetch()
 * with a JSON body, and a plain form POST from the same page's fallback
 * markup. Accepting both here means no endpoint has to choose and no
 * caller has to be told which one it is using.
 *
 * ORDER MATTERS. The raw stream is tried first and only if it does not
 * decode is $this->input->post() consulted. Once PHP has read
 * php://input it cannot be read again, so a method that checks post()
 * first and then falls back to the stream gets an empty stream — the
 * JSON body arrives as an empty array and the field is silently missing
 * rather than reported as absent.
 *
 * @return array
 */
function sadmin_api_input()
{
	$CI =& get_instance();

	$raw = $CI->input->raw_input_stream;
	if (is_string($raw) && $raw !== '')
	{
		$decoded = json_decode($raw, TRUE);
		if (is_array($decoded))
		{
			return $decoded;
		}
	}

	return (array) $CI->input->post(NULL, TRUE);
}

/**
 * Answer a panel API call and stop.
 *
 * Always halts. Returning to the controller after a JSON response would
 * let a method append HTML after the closing brace, and the caller would
 * have to defend against a half-JSON body by checking the first byte.
 *
 * @param array $payload  ['success' => bool, 'message' => string, ...]
 * @param int   $code     HTTP status.
 */
function sadmin_json(array $payload, $code = 200)
{
	$CI =& get_instance();
	$CI->output
		->set_status_header($code)
		->set_content_type('application/json', 'utf-8')
		->set_output(json_encode($payload, JSON_UNESCAPED_UNICODE));
	$CI->output->_display();
	exit;
}

/**
 * The standard refusal. Every panel API uses this shape so a caller can
 * branch on `success` without inspecting the status code.
 */
function sadmin_fail($message, $code = 400)
{
	sadmin_json(['success' => false, 'message' => $message], $code);
}

/**
 * Run a panel write, and audit it either way.
 *
 * Every mutating endpoint in this panel follows the same three steps —
 * validate, write, record — and the recording step is the one that gets
 * forgotten. Doing it here makes "did we log this" a property of the
 * call rather than a thing to remember, and it guarantees the audit row
 * and the write share one code path.
 *
 * The audit row is written AFTER the write, not before. A row recording
 * an action that then failed is worse than no row: an audit log with
 * entries for changes that never happened is not evidence of anything.
 *
 * @param  callable $work      Returns the success payload, or FALSE on
 *                             failure (having already emitted a refusal).
 * @param  string   $action    audit action
 * @param  string   $entity    audit entity
 * @param  array    $audit     extra audit_log() opts
 * @return void
 */
function sadmin_write(callable $work, $action, $entity, array $audit = [])
{
	$result = $work();
	if ($result === FALSE)
	{
		// $work() has already answered. Nothing is written, so nothing is
		// logged — see the note above about logging attempts.
		return;
	}

	$payload = is_array($result) ? $result : ['success' => true];
	if (!isset($payload['audit']))
	{
		$payload['audit'] = $audit;
	}

	audit_log($action, $entity, $payload['audit']);

	sadmin_json($payload);
}

/**
 * Days remaining on a subscription, signed.
 *
 * @return int  negative once expired.
 */
function subscription_days_left($end_date)
{
	if (empty($end_date))
	{
		return 0;
	}
	$end = strtotime($end_date);
	if ($end === false)
	{
		return 0;
	}
	// A calendar-day difference, not a division of raw timestamps: a
	// tenant that expires at midnight tonight should read as 0 days
	// today, not as a fraction of a day that rounds down to 0 and also
	// reads as 0 tomorrow.
	$today = new DateTime('today');
	$endd  = (new DateTime($end_date))->setTime(0, 0);
	return (int) $today->diff($endd)->format('%r%a');
}

/**
 * Cleanup expired or revoked temporary tenant admin accounts.
 * Automatically deletes or disables user rows whose time limit has passed.
 */
function cleanup_expired_temp_admins()
{
	$CI =& get_instance();
	if (!isset($CI->db)) return;

	$expired = $CI->db->select('t.id, t.user_id, t.tenant_id, t.username')
		->from('tenant_temp_admins t')
		->where('t.expires_at <= NOW()')
		->or_where('t.is_revoked', TRUE)
		->get()->result();

	if (empty($expired)) return;

	foreach ($expired as $row) {
		$user_id = (int)$row->user_id;

		// 1. Delete customer_full_info row if present
		$user_row = $CI->db->where('id', $user_id)->get('users')->row();
		if ($user_row && !empty($user_row->user_full_tbl_id)) {
			$CI->db->where('udc_list_auto_p_iidd', $user_row->user_full_tbl_id)->delete('customer_full_info');
		}

		// 2. Delete users_groups
		$CI->db->where('user_id', $user_id)->delete('users_groups');

		// 3. Try to delete user row; if referenced by foreign keys (e.g. approvals), deactivate
		try {
			$CI->db->where('id', $user_id)->delete('users');
		} catch (Exception $e) {
			$CI->db->where('id', $user_id)->update('users', [
				'active' => 0,
				'password' => '$2y$10$EXPIRED.ACCOUNT.INVALIDATED.' . bin2hex(random_bytes(10))
			]);
		}

		// 4. Delete from tenant_temp_admins table
		$CI->db->where('id', $row->id)->delete('tenant_temp_admins');
	}
}

/**
 * Creates an auto-expiring temporary support administrator for a tenant (1 to 72 hours).
 *
 * @param int $tenant_id
 * @param int $duration_hours (1 to 72)
 * @param string $role_key ('uphead' or 'upsocib')
 * @param string $reason
 * @param int|null $created_by
 * @return array
 */
function create_temp_tenant_admin($tenant_id, $duration_hours = 24, $role_key = 'uphead', $reason = '', $created_by = null)
{
	$CI =& get_instance();
	if (!isset($CI->db)) return ['success' => false, 'error' => 'Database not loaded'];

	// 1. Enforce duration: 1 to 72 hours
	$duration_hours = max(1, min(72, (int)$duration_hours));

	// 2. Validate tenant
	$tenant = $CI->db->where('ins_titute_p_idds', (int)$tenant_id)->get('ins_titute')->row();
	if (!$tenant) {
		return ['success' => false, 'error' => 'ইউনিয়ন পরিষদ পাওয়া যায়নি'];
	}

	// 3. Clean up any existing expired temp admins first
	cleanup_expired_temp_admins();

	// 4. Generate random readable credentials
	$random_slug = substr(bin2hex(random_bytes(4)), 0, 6);
	$username = 'support_' . $tenant_id . '_' . $random_slug;
	$plain_password = 'Vireta#' . substr(bin2hex(random_bytes(4)), 0, 8);
	$email = $username . '@support.viretadev.local';

	// 5. Create customer_full_info
	$cust_data = [
		'ins_pr_iddds'        => (int)$tenant_id,
		'user_person_name'    => 'কারিগরি সহায়তা অ্যাডমিন (Support)',
		'user_email_no'       => $email,
		'user_phone_no'       => '01700000000',
		'user_uniq_qq_reails' => uniqid('sup_'),
		'activity'            => 1,
		'create_times_stamp'  => time(),
		'today_dates_updating'=> date('Y-m-d'),
		'address_full'        => 'ViretaDev Central Support'
	];
	$CI->db->insert('customer_full_info', $cust_data);
	$cust_id = $CI->db->insert_id();

	// 6. Hash password with bcrypt
	$password_hash = password_hash($plain_password, PASSWORD_BCRYPT);

	// 7. Insert into users
	$user_data = [
		'username'         => $username,
		'password'         => $password_hash,
		'email'            => $email,
		'created_on'       => time(),
		'active'           => 1,
		'ins_pr_id_s'      => (int)$tenant_id,
		'user_full_tbl_id' => $cust_id,
		'u_type'           => $role_key,
		'union_unq_idd_iii'=> $tenant->un_iddd_iiid ?? null
	];
	$CI->db->insert('users', $user_data);
	$user_id = $CI->db->insert_id();

	// 8. Assign roles in users_groups:
	// Admin group (1) + Selected Role (4 for uphead or 5 for upsocib)
	$group_id = ($role_key === 'upsocib') ? 5 : 4;
	$CI->db->insert('users_groups', ['user_id' => $user_id, 'group_id' => 1]);
	$CI->db->insert('users_groups', ['user_id' => $user_id, 'group_id' => $group_id]);

	// 9. Calculate expiration
	$expires_at = date('Y-m-d H:i:s', time() + ($duration_hours * 3600));

	// 10. Record in tenant_temp_admins
	$temp_record = [
		'tenant_id'      => (int)$tenant_id,
		'user_id'        => $user_id,
		'username'       => $username,
		'plain_password' => $plain_password,
		'role_key'       => $role_key,
		'duration_hours' => $duration_hours,
		'expires_at'     => $expires_at,
		'reason'         => mb_substr(trim($reason ?: 'কারিগরি সহায়তা'), 0, 250, 'UTF-8'),
		'created_by'     => $created_by ? (int)$created_by : ($CI->ion_auth->get_user_id() ?? null),
		'is_revoked'     => FALSE
	];
	$CI->db->insert('tenant_temp_admins', $temp_record);
	$temp_id = $CI->db->insert_id();

	// Determine login URL on tenant domain
	$domain_row = $CI->db->where('ins_titute_p_id', (int)$tenant_id)
		->where('is_verified', TRUE)
		->order_by('is_primary', 'DESC')
		->get('tenant_domains')->row();

	$ht_scheme = is_https() ? 'https://' : 'http://';
	$ht_reqhost = (string)($_SERVER['HTTP_HOST'] ?? '');
	$ht_port = '';
	if (($c = strrpos($ht_reqhost, ':')) !== FALSE && ctype_digit(substr($ht_reqhost, $c + 1))) {
		$ht_port = ':' . substr($ht_reqhost, $c + 1);
	}
	$host = $domain_row ? $domain_row->hostname : 'localhost';
	$login_url = $ht_scheme . $host . $ht_port . '/login';

	return [
		'success'        => true,
		'temp_id'        => $temp_id,
		'tenant_id'      => (int)$tenant_id,
		'tenant_name'    => $tenant->ins_name,
		'username'       => $username,
		'plain_password' => $plain_password,
		'role_key'       => $role_key,
		'role_title'     => ($role_key === 'upsocib' ? 'ইউপি সচিব' : 'ইউপি চেয়ারম্যান'),
		'duration_hours' => $duration_hours,
		'expires_at'     => $expires_at,
		'login_url'      => $login_url
	];
}

/**
 * Immediately revokes and purges a temporary tenant admin account.
 */
function revoke_temp_tenant_admin($temp_id)
{
	$CI =& get_instance();
	if (!isset($CI->db)) return false;

	$rec = $CI->db->where('id', (int)$temp_id)->get('tenant_temp_admins')->row();
	if (!$rec) return false;

	$user_id = (int)$rec->user_id;

	// Remove customer_full_info
	$user_row = $CI->db->where('id', $user_id)->get('users')->row();
	if ($user_row && !empty($user_row->user_full_tbl_id)) {
		$CI->db->where('udc_list_auto_p_iidd', $user_row->user_full_tbl_id)->delete('customer_full_info');
	}

	// Delete users_groups
	$CI->db->where('user_id', $user_id)->delete('users_groups');

	// Delete from users or deactivate
	try {
		$CI->db->where('id', $user_id)->delete('users');
	} catch (Exception $e) {
		$CI->db->where('id', $user_id)->update('users', [
			'active' => 0,
			'password' => '$2y$10$REVOKED.ACCOUNT.INVALIDATED.' . bin2hex(random_bytes(10))
		]);
	}

	// Delete from tenant_temp_admins
	$CI->db->where('id', (int)$temp_id)->delete('tenant_temp_admins');
	return true;
}

