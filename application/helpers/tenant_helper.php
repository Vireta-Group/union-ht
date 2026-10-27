<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Tenant helpers
 *
 * Thin wrappers around the Tenant_resolver library so models can write
 * `tenant_code()` instead of `$this->tenant_resolver->id()`.
 *
 * tenant_code() is the drop-in replacement for the old hardcoded
 * `union_code` constant.
 */

if ( ! function_exists('tenant_resolver'))
{
	/**
	 * @return Tenant_resolver
	 */
	function tenant_resolver()
	{
		$CI =& get_instance();
		if ( ! isset($CI->tenant_resolver))
		{
			$CI->load->library('Tenant_resolver');
		}
		return $CI->tenant_resolver;
	}
}

if ( ! function_exists('tenant_code'))
{
	/**
	 * The current tenant's ins_titute.ins_titute_p_idds — i.e. the value the
	 * legacy `union_code` constant used to hardcode.
	 *
	 * Returns 0 when the tenant could not be resolved. 0 matches no tenant
	 * column in the schema, so queries come back empty rather than leaking
	 * another tenant's rows. There is deliberately no hardcoded default.
	 *
	 * @return int
	 */
	function tenant_code()
	{
		return tenant_resolver()->id();
	}
}

if ( ! function_exists('tenant_un_id'))
{
	/**
	 * The current tenant's ins_titute.un_iddd_iiid.
	 *
	 * @return int|null
	 */
	function tenant_un_id()
	{
		return tenant_resolver()->un_id();
	}
}

if ( ! function_exists('tenant_row'))
{
	/**
	 * Full ins_titute row for the current tenant (branding, contact, geo codes).
	 *
	 * @return object|null
	 */
	function tenant_row()
	{
		return tenant_resolver()->row();
	}
}

if ( ! function_exists('tenant_hostname'))
{
	/**
	 * @return string
	 */
	function tenant_hostname()
	{
		return tenant_resolver()->hostname();
	}
}

if ( ! function_exists('tenant_source'))
{
	/**
	 * How the tenant was resolved: dev-query | dev-header | domain | subdomain | default
	 *
	 * @return string
	 */
	function tenant_source()
	{
		return tenant_resolver()->source();
	}
}

if ( ! function_exists('tenant_dev_domains_allowed'))
{
	/**
	 * May an `is_dev_only = TRUE` row in tenant_domains be treated as reachable
	 * right now?
	 *
	 * The public face of Tenant_resolver::allows_dev_domains(), which is
	 * protected. Same predicate, same order, same two branches — it has to be the
	 * same, because this is the question that decides whether a dev-only hostname
	 * is shown to anybody, and two copies of that rule is how a dev host ends up
	 * being advertised on a production panel.
	 *
	 * Why it is needed outside the resolver: `is_dev_only` has to appear on every
	 * query that reads the column, and the super-admin panel is such a query — it
	 * lists every tenant and every hostname, so without this filter it would
	 * print `kanchan.localhost` as a live site the moment APP_ENV left
	 * development.
	 *
	 * @return bool
	 */
	function tenant_dev_domains_allowed()
	{
		$host = tenant_hostname();
		if (in_array($host, (array) config_item('dev_hosts'), TRUE))
		{
			return TRUE;
		}
		if (ENVIRONMENT === 'development'
			&& (str_ends_with($host, '.localhost')
				|| str_ends_with($host, '.local')
				|| str_ends_with($host, '.test')))
		{
			return TRUE;
		}
		return FALSE;
	}
}

if ( ! function_exists('enforce_tenant_scope'))
{
	/**
	 * Refuse the request when the signed-in user does not belong to the
	 * parishad this host resolved to.
	 *
	 * WHY A SEPARATE LAYER FROM THE RESOLVER
	 * Tenant_resolver resolves the HOST before the session — an exact
	 * `tenant_domains` match is step 3, the authenticated user's own tenant is
	 * step 5 — and the host always wins. That is right for deciding which
	 * parishad's public site you are looking at, and wrong for deciding what a
	 * signed-in user may read and write. Without this, রূপসা's chairman
	 * (users.ins_pr_id_s = 10) who signed in on baghmaraup.gov.bd was served
	 * বাঘমারা's whole dataset, because every method scoped its queries with
	 * tenant_code() and tenant_code() was answering to the host.
	 *
	 * Call it from the constructor of any controller that serves tenant data.
	 * It is a function rather than a base controller because this codebase has
	 * no base controller, and it is a helper rather than inlined code because
	 * three controllers need it and a fourth copy is how the original hole
	 * happened: Dashboard had the idea and Sonod and Download did not.
	 *
	 * It returns quietly — never denies — in every case it has no opinion on:
	 *
	 *   - anonymous callers (the auth gate that runs first has already decided)
	 *   - the `sadmin` role, which is the platform's own cross-tenant role and
	 *     has users.ins_pr_id_s = NULL; this is what keeps the super-admin
	 *     panel's `?tenant=N` switch working
	 *   - tenant_code() === 0, i.e. a platform host, where there is no
	 *     parishad to be scoped to. Those controllers redirect instead.
	 *
	 * Note a non-sadmin user with a NULL ins_pr_id_s has no parishad of their
	 * own, so ANY tenant is somebody else's and they are refused. Testing
	 * "does the user have a tenant" first would wave them straight through.
	 *
	 * @return void Halts with a 403 via show_error() when it refuses.
	 */
	function enforce_tenant_scope()
	{
		$CI =& get_instance();

		if ( ! isset($CI->ion_auth))
		{
			return;
		}

		$user = $CI->ion_auth->user();
		if ( ! $user)
		{
			return;
		}
		$row = $user->row();
		if ( ! $row)
		{
			return;
		}

		if ($CI->ion_auth->in_group('sadmin'))
		{
			show_error(
				'সুপার অ্যাডমিন কোনো ইউনিয়ন পরিষদের অভ্যন্তরীণ ড্যাশবোর্ড বা ডেটা অ্যাক্সেস করতে পারবেন না। অনুগ্রহ করে প্ল্যাটফর্ম সুপার অ্যাডমিন প্যানেল (/super_admin) ব্যবহার করুন।',
				403,
				'অনুমোদনহীন প্রবেশাধিকার'
			);
		}

		$resolved = tenant_code();
		if ($resolved <= 0)
		{
			return;
		}

		$own = (int)($row->ins_pr_id_s ?? 0);
		if ($own === $resolved)
		{
			return;
		}

		log_message('error', 'Tenant guard refused: user ' . $row->id
			. ' belongs to tenant ' . $own . ' but host '
			. ($_SERVER['HTTP_HOST'] ?? '?') . ' resolves to tenant ' . $resolved
			. ' (route ' . ($CI->router->class . '/' . $CI->router->method) . ')');

		show_error(
			'আপনি এই ইউনিয়ন পরিষদের সদস্য নন। অন্য কোনো পরিষদের তথ্য দেখা বা '
			. 'পরিবর্তন করার অনুমতি আপনার নেই। আপনার নিজের পরিষদের সাইটে '
			. 'লগইন করুন।',
			403,
			'অনুমোদনহীন প্রবেশাধিকার'
		);
	}
}

if ( ! function_exists('enforce_tenant_status'))
{
	/**
	 * Enforce tenant active status and subscription expiration rules.
	 *
	 * 1. Suspended Tenants (ins_titute.is_active = FALSE):
	 *    Signed-in users of a suspended tenant are blocked with 403 (Account Suspended).
	 *
	 * 2. Expired Subscriptions (subscriptions.end_date < CURRENT_DATE or status = 'expired'/'cancelled'):
	 *    - Automatically synchronizes subscriptions.status = 'expired' in DB if it was 'active'.
	 *    - Write actions (POST requests) are blocked immediately with 402 Payment Required.
	 *    - Web requests are halted with a clear Subscription Expired notification
	 *      detailing the renewal procedure and platform billing helpline.
	 *
	 * Platform super admins (group 'sadmin') are completely exempt so they can
	 * inspect and manage accounts across all parishads.
	 */
	function enforce_tenant_status()
	{
		$CI =& get_instance();

		if ( ! isset($CI->ion_auth) || ! $CI->ion_auth->logged_in())
		{
			return;
		}

		$user = $CI->ion_auth->user();
		if ( ! $user)
		{
			return;
		}
		$row = $user->row();
		if ( ! $row)
		{
			return;
		}

		// Platform super admins are exempt from tenant status gates
		if ($CI->ion_auth->in_group('sadmin'))
		{
			return;
		}

		$tenant_id = tenant_code();
		if ($tenant_id <= 0)
		{
			return;
		}

		$tenant = tenant_row();
		if ( ! $tenant)
		{
			return;
		}

		// 1. Check if the tenant has been suspended by platform authorities
		if (isset($tenant->is_active) && ! $tenant->is_active)
		{
			log_message('error', 'Tenant status guard refused: tenant ' . $tenant_id . ' is SUSPENDED. User ' . $row->id);
			show_error(
				'<div style="text-align:center; padding: 20px 10px;">' .
				'<h2 style="color: #b91c1c; font-size: 20px; font-weight: bold; margin-bottom: 12px;">ইউনিয়ন পরিষদের কার্যক্রম সাময়িকভাবে স্থগিত (Suspended)</h2>' .
				'<p style="color: #4b5563; font-size: 14px; line-height: 1.6; margin-bottom: 16px;">' .
				'আপনার ইউনিয়ন পরিষদের অনলাইন বাতায়ন ও সেবা কার্যক্রম প্ল্যাটফর্ম কর্তৃপক্ষ কর্তৃক সাময়িকভাবে স্থগিত রাখা হয়েছে। ' .
				'পুনরায় সেবা চালুকরণ বা বিস্তারিত তথ্যের জন্য প্ল্যাটফর্ম সাপোর্টে যোগাযোগ করুন।' .
				'</p>' .
				'<div style="background: #f3f4f6; border-radius: 8px; padding: 12px; font-size: 13px; color: #374151; display: inline-block;">' .
				'সাপোর্ট হেল্পলাইন: <strong>+৮৮০ ১৭০০-০০০০০০</strong> &nbsp;|&nbsp; ইমেইল: <strong>support@viretadev.com</strong>' .
				'</div>' .
				'</div>',
				403,
				'অ্যাকাউন্ট স্থগিত / Account Suspended'
			);
		}

		// 2. Check subscription status & expiration
		$sub = $CI->db->where('ins_titute_p_id', $tenant_id)->get('subscriptions')->row();
		$today = date('Y-m-d');
		$end_date = $sub ? $sub->end_date : null;

		if (empty($end_date) && !empty($tenant->expire_times))
		{
			if (is_numeric($tenant->expire_times))
			{
				$end_date = date('Y-m-d', (int)$tenant->expire_times);
			}
			else
			{
				$end_date = substr($tenant->expire_times, 0, 10);
			}
		}

		$is_expired = false;
		if ($sub && in_array($sub->status, ['expired', 'cancelled'], true))
		{
			$is_expired = true;
		}
		elseif (!empty($end_date) && $end_date < $today)
		{
			$is_expired = true;
			// Synchronize status to expired if it was active
			if ($sub && $sub->status === 'active')
			{
				$CI->db->where('subscription_id', $sub->subscription_id)->update('subscriptions', ['status' => 'expired']);
			}
		}

		if ($is_expired)
		{
			// Flag subscription expired globally so views can render read-only notices
			$CI->is_subscription_expired = true;

			// Allow renewal payments and support tickets even when expired
			$current_route = strtolower(($CI->router->class ?? '') . '/' . ($CI->router->method ?? ''));
			$exempt_routes = [
				'dashboard/submit_subscription_payment',
				'dashboard/change_subscription_package',
				'dashboard/api_create_support_ticket',
				'dashboard/api_reply_support_ticket'
			];
			if (in_array($current_route, $exempt_routes, true))
			{
				return;
			}

			$is_post = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
			if ($is_post)
			{
				if ($CI->input->is_ajax_request() || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false))
				{
					$CI->output->set_status_header(402);
					$CI->output->set_content_type('application/json');
					$CI->output->set_output(json_encode([
						'success' => false,
						'status'  => 'subscription_expired',
						'message' => 'ইউনিয়ন পরিষদের সাবস্ক্রিপশন প্ল্যানের মেয়াদ উত্তীর্ণ হয়েছে। রিড-অনলি মোড সক্রিয়। নতুন তথ্য যোগ বা ডাটা পরিবর্তন বন্ধ রয়েছে। দয়া করে সাবস্ক্রিপশন নবায়ন করুন।'
					]));
					$CI->output->_display();
					exit;
				}
				else
				{
					if (isset($CI->session))
					{
						$CI->session->set_flashdata('error', 'আপনার সাবস্ক্রিপশনের মেয়াদ শেষ হয়েছে। রিড-অনলি মোডে নতুন তথ্য তৈরি বা আপডেট করা সম্ভব নয়। অনুগ্রহ করে সাবস্ক্রিপশন নবায়ন করুন।');
					}
					redirect($_SERVER['HTTP_REFERER'] ?? 'dashboard', 'refresh');
					exit;
				}
			}

			// On GET requests: Allow the tenant to log in and view their data in read-only mode.
		}
	}
}

if ( ! function_exists('tenant_login_url'))
{
	/**
	 * Where to send a user who has just authenticated: a URL on a host that
	 * belongs to THEIR parishad.
	 *
	 * Shared by Front::api_login() and Auth::login(), which are two routes to
	 * the same outcome and used to disagree — api_login() preferred the host
	 * they typed the password into, Auth::login() redirected relative to it.
	 * Both therefore parked a parishad admin on whichever parishad's site they
	 * happened to visit, which is what handed them that parishad's dashboard.
	 *
	 * Host preference, in order:
	 *   - a live (non dev-only) host. In development a dev host is preferred
	 *     instead, because `localhost` does not resolve for a parishad admin.
	 *   - `localhost` / `127.0.0.1` are never returned: those are the
	 *     platform's own names, not a parishad's.
	 *
	 * With no usable host it falls back to the `?tenant=` dev override, which
	 * only resolves on a dev host. enforce_tenant_scope() remains the backstop
	 * in that case — this function is about not sending anyone to the wrong
	 * parishad, the guard is about refusing when we end up there anyway.
	 *
	 * @param object $user    A row from `users`.
	 * @param int    $current The tenant this request resolved to, i.e. tenant_code().
	 * @return string Absolute URL.
	 */
	function tenant_login_url($user, $current = null)
	{
		$CI =& get_instance();
		$current = $current ?? tenant_code();

		// Platform super admin (sadmin) has no parishad — their home is the platform control panel.
		if ($CI->ion_auth->in_group('sadmin', $user->id) || ($user->u_type ?? '') === 'sadmin')
		{
			return base_url('super_admin');
		}

		$tenant_id = (int)($user->ins_pr_id_s ?? 0);
		if ($tenant_id > 0 && $current === $tenant_id)
		{
			// Already on our own parishad's site — no cross-domain hop.
			return base_url('dashboard');
		}

		$is_dev = (bool) $CI->config->item('is_development');

		// In development: when on a dev platform host (localhost, 127.0.0.1, ::1), stay on the
		// current host with ?tenant=N to preserve the dev server port (e.g. :8080), maintain
		// session cookies, and avoid broken cross-domain redirects to unreachable .local hostnames.
		if ($is_dev && $tenant_id > 0)
		{
			$current_host = '';
			if (isset($CI->tenant_resolver))
			{
				$current_host = $CI->tenant_resolver->hostname();
			}
			elseif (isset($_SERVER['HTTP_HOST']))
			{
				$current_host = strtolower(trim($_SERVER['HTTP_HOST']));
				if (($c = strrpos($current_host, ':')) !== false)
				{
					$current_host = substr($current_host, 0, $c);
				}
			}

			if ($current_host === 'localhost' || $current_host === '127.0.0.1' || $current_host === '::1')
			{
				return base_url('dashboard?tenant=' . $tenant_id);
			}
		}

		if ($tenant_id > 0)
		{
			$rows = $CI->db->where('ins_titute_p_id', $tenant_id)
			                ->where('is_verified', true)
			                ->order_by('is_primary', 'desc')
			                ->get('tenant_domains')
			                ->result();

			$dev_host  = null;
			$live_host = null;
			$any_host  = null;

			foreach ($rows as $r)
			{
				$h = $r->hostname;
				if ($h === 'localhost' || $h === '127.0.0.1')
				{
					continue;
				}
				if ($any_host === null)
				{
					$any_host = $h;
				}
				if (empty($r->is_dev_only) && $live_host === null)
				{
					$live_host = $h;
				}
				if ( ! empty($r->is_dev_only))
				{
					// Prefer *.localhost over *.local because modern browsers and OS resolvers
					// automatically resolve *.localhost without requiring /etc/hosts modification.
					if ($dev_host === null || str_ends_with($h, '.localhost'))
					{
						$dev_host = $h;
					}
				}
			}

			$host = $live_host;
			if ($is_dev && $dev_host !== null)
			{
				$host = $dev_host;
			}
			if ($host === null)
			{
				$host = $any_host;
			}

			if ($host)
			{
				$port = '';
				if ($is_dev && isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], ':') !== false)
				{
					$port = ':' . substr($_SERVER['HTTP_HOST'], strpos($_SERVER['HTTP_HOST'], ':') + 1);
				}
				return (is_https() ? 'https://' : 'http://') . $host . $port . '/dashboard';
			}
		}

		return base_url('dashboard?tenant=' . $tenant_id);
	}
}

if ( ! function_exists('bn_num'))
{
	/**
	 * Convert English digits to Bengali digits.
	 *
	 * @param string|int|float $number
	 * @return string
	 */
	function bn_num($number)
	{
		$en = ['0','1','2','3','4','5','6','7','8','9'];
		$bn = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
		return str_replace($en, $bn, (string)$number);
	}
}

if ( ! function_exists('get_user_tenant_role'))
{
	/**
	 * Returns the human-readable tenant role name and key for a user.
	 * Resolves among: uphead, upsocib, udc, collect (with fallback to sadmin/admin).
	 *
	 * @param int|null $user_id
	 * @return array ['key' => string, 'title' => string, 'role_id' => int]
	 */
	function get_user_tenant_role($user_id = null)
	{
		$CI =& get_instance();
		if (empty($user_id) && isset($CI->ion_auth) && $CI->ion_auth->logged_in()) {
			$user_id = $CI->ion_auth->get_user_id();
		}
		if (empty($user_id)) {
			return ['key' => 'collect', 'title' => 'ট্যাক্স আদায়কারী', 'role_id' => 7];
		}

		$roles = $CI->db->select('g.id, g.name')
			->from('users_groups ug')
			->join('groups g', 'g.id = ug.group_id')
			->where('ug.user_id', (int)$user_id)
			->get()->result();

		$group_names = [];
		foreach ($roles as $r) {
			$group_names[$r->name] = (int)$r->id;
		}

		if (isset($group_names['uphead'])) {
			return ['key' => 'uphead', 'title' => 'ইউপি চেয়ারম্যান', 'role_id' => 4];
		}
		if (isset($group_names['upsocib'])) {
			return ['key' => 'upsocib', 'title' => 'ইউপি সচিব', 'role_id' => 5];
		}
		if (isset($group_names['udc'])) {
			return ['key' => 'udc', 'title' => 'ইউডিসি উদ্যোক্তা', 'role_id' => 6];
		}
		if (isset($group_names['collect'])) {
			return ['key' => 'collect', 'title' => 'ট্যাক্স আদায়কারী', 'role_id' => 7];
		}
		if (isset($group_names['sadmin'])) {
			return ['key' => 'sadmin', 'title' => 'সুপার অ্যাডমিন', 'role_id' => 3];
		}
		if (isset($group_names['admin'])) {
			return ['key' => 'uphead', 'title' => 'ইউপি চেয়ারম্যান', 'role_id' => 4];
		}

		return ['key' => 'collect', 'title' => 'ট্যাক্স আদায়কারী', 'role_id' => 7];
	}
}

if ( ! function_exists('tenant_subscription'))
{
	/**
	 * Get the active subscription details and package information for a tenant.
	 *
	 * @param int|null $tenant_id
	 * @return object|null
	 */
	function tenant_subscription($tenant_id = null)
	{
		$CI =& get_instance();
		$tid = $tenant_id ? (int)$tenant_id : (int)tenant_code();
		if ($tid <= 0) return null;

		$CI->db->select('s.*, p.package_key, p.name_bn as pkg_name_bn, p.name_en as pkg_name_en, p.price_monthly, p.price_yearly, p.features');
		$CI->db->from('subscriptions s');
		$CI->db->join('packages p', 'p.package_id = s.package_id', 'left');
		$CI->db->where('s.ins_titute_p_id', $tid);
		$sub = $CI->db->get()->row();

		if (!$sub) {
			$t = tenant_row();
			if ($t) {
				return (object)[
					'subscription_id' => 0,
					'ins_titute_p_id' => $tid,
					'package_id'      => 0,
					'package_key'     => 'standard',
					'pkg_name_bn'     => 'স্ট্যান্ডার্ড',
					'pkg_name_en'     => 'Standard',
					'price_monthly'   => 2400,
					'price_yearly'    => 25000,
					'billing_cycle'   => 'yearly',
					'start_date'      => date('Y-m-d'),
					'end_date'        => !empty($t->expire_times) ? (is_numeric($t->expire_times) ? date('Y-m-d', (int)$t->expire_times) : substr($t->expire_times, 0, 10)) : date('Y-m-d', strtotime('+1 year')),
					'status'          => 'active',
					'is_auto_renew'   => false,
					'features'        => ''
				];
			}
			return null;
		}

		return $sub;
	}
}

if ( ! function_exists('is_tenant_subscription_expired'))
{
	/**
	 * Check whether the current tenant's subscription has expired.
	 *
	 * @param int|null $tenant_id
	 * @return bool
	 */
	function is_tenant_subscription_expired($tenant_id = null)
	{
		$sub = tenant_subscription($tenant_id);
		if (!$sub) return false;
		if (in_array($sub->status, ['expired', 'cancelled'], true)) return true;
		if (!empty($sub->end_date) && $sub->end_date < date('Y-m-d')) return true;
		return false;
	}
}

