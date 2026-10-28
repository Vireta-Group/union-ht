<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Tenant_creator — the one place a parishad gets created
 * =====================================================================
 *
 * This method used to be a private method on the Front controller, and
 * the super-admin panel's "নতুন ইউনিয়ন অনবোর্ড" button had its own copy of
 * it. Two copies of tenant creation is two different answers to "what is a
 * tenant", and they already disagreed:
 *
 *   Front::create_tenant()            Super_admin::api_create_tenant()
 *   -------------------------------   ---------------------------------
 *   apex from config (APP_TENANT_    literal '.localhost' and
 *   APEX)                             literal '.viretaup.com'
 *   writes the bare SLUG row          does not
 *   exactly one is_primary row        the apex row and the custom-domain
 *                                     row can BOTH be is_primary
 *   checks the insert, rolls back     takes insert_id() from a failed
 *                                     insert, which is the last id the
 *                                     connection saw
 *   bcrypt only                       bcrypt only
 *   one year of service               one year of service
 *
 * The is_primary disagreement is the dangerous one. primary_tenant() is
 * `WHERE is_primary = TRUE ... ORDER BY d.id LIMIT 1`, so two primaries
 * means the fallback tenant for every unknown host depends on insert
 * order, and flipping an unrelated row changes which parishad an
 * unrecognised host serves.
 *
 * So there is one copy now, and both callers use it. It lives in a model
 * rather than staying a private controller method because the second
 * caller is a different controller: making Front::create_tenant() public
 * and instantiating Front from the panel would run Front::__construct(),
 * which issues four tenant-scoped queries (getThisInsName, sliders,
 * members, employees) on every onboard click. All four return NULL on a
 * platform host, so it would work, and it would be doing four pointless
 * queries to get there.
 *
 * WHAT THIS DOES NOT DO
 * It does not verify that the caller should be allowed to create a
 * tenant. Signup calls it from an unauthenticated request, throttled per
 * IP and off in development; the panel calls it from a sadmin-gated
 * controller. Both callers own that decision, and it is deliberately not
 * owned here.
 */
class Tenant_creator extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('ion_auth_model');
	}

	/**
	 * Create a tenant, its hostnames, and its first administrator.
	 *
	 * Returns an array instead of echoing, so the caller decides what to do
	 * with the result. Every refusal branch is `['success' => false, ...]`;
	 * nothing here halts the request.
	 *
	 * CHANGED, deliberately, versus the version this was moved from:
	 *
	 *   - the apex comes from config (APP_TENANT_APEX), not a literal
	 *     'viretaup.com' baked into this method;
	 *   - the bare SLUG is stored as a hostname row, which is what
	 *     Tenant_resolver::match_subdomain() looks up after it strips the
	 *     apex. Without that row a subdomain resolves in DNS and 404s in
	 *     the app;
	 *   - exactly one row is is_primary, where before the apex row and the
	 *     custom-domain row could both be TRUE and the fallback tenant
	 *     depended on which row the query reached first;
	 *   - the password is only ever stored hashed; password_viewss_s is no
	 *     longer written, so nothing on disk holds a plaintext credential;
	 *   - returned URLs are https and carry no dev-server port.
	 *
	 * ADDED 2026-09-30, when the super-admin panel arrived:
	 *
	 *   - package_key is written beside the legacy theme_ss, and a
	 *     subscriptions row is created for the chosen plan. Before this,
	 *     the panel's onboard API read `package` off the request and then
	 *     never used it — the plan a sadmin picked while onboarding was
	 *     silently dropped, and the new tenant appeared on the standard
	 *     plan with no subscription row at all.
	 *   - the created tenant, user and plan are audit_logged. Onboarding is
	 *     the single most consequential write this platform performs.
	 *
	 * @param  array $input  Raw, unvalidated request values. union_name,
	 *                       union_name_en, subdomain, custom_domain,
	 *                       location, admin_name, admin_phone, admin_email,
	 *                       admin_password, package.
	 * @return array
	 */
	public function create(array $input)
	{
		$package        = trim($input['package'] ?? 'premium');
		$union_name     = trim($input['union_name'] ?? '');
		$union_name_en  = trim($input['union_name_en'] ?? '');
		$subdomain      = strtolower(preg_replace('/[^a-zA-Z0-9-]/', '', trim($input['subdomain'] ?? '')));
		$custom_domain  = strtolower(trim($input['custom_domain'] ?? ''));
		$custom_domain  = preg_replace('#^https?://#i', '', $custom_domain);
		$custom_domain  = trim(rtrim($custom_domain, '/'));
		$location       = trim($input['location'] ?? '');
		$admin_name     = trim($input['admin_name'] ?? '');
		$admin_phone    = trim($input['admin_phone'] ?? '');
		$admin_email    = trim($input['admin_email'] ?? '');
		$admin_password = trim($input['admin_password'] ?? '');

		$apex = strtolower(trim((string) ($this->config->item('tenant')['apex_domain'] ?? '')));

		if (empty($union_name) || empty($subdomain) || empty($admin_email) || empty($admin_password)) {
			return [
				'success' => false,
				'message' => 'অনুগ্রহ করে সকল আবশ্যকীয় তথ্য (ইউনিয়ন নাম, সাবডোমেন, ইমেইল ও পাসওয়ার্ড) পূরণ করুন।'
			];
		}

		if (strlen($admin_password) < 6) {
			return [
				'success' => false,
				'message' => 'পাসওয়ার্ড কমপক্ষে ৬ অক্ষরের হতে হবে।'
			];
		}

		// Slug hygiene. The sanitiser above already strips everything outside
		// [a-z0-9-]; what is left is still worth bounding, because a leading or
		// trailing hyphen is not a valid DNS label and a 1-character slug would
		// claim a one-letter subdomain.
		$subdomain = trim($subdomain, '-');
		if ($subdomain === '' || ! preg_match('/^[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?$/', $subdomain)) {
			return [
				'success' => false,
				'message' => 'সাবডোমেনটি সঠিক নয়। কমপক্ষে ২ অক্ষরের হতে হবে এবং বানান হিসেবে অক্ষর, সংখ্যা ও হাইফেন (-) ব্যবহার করুন।'
			];
		}

		// Signup needs a real apex to hand out a hostname. Where APP_TENANT_APEX
		// is unset there is nothing to hand out, and silently minting
		// '<slug>.localhost' would create a tenant nobody could ever reach.
		if ($apex === '') {
			return [
				'success' => false,
				'message' => 'সাবডোমেন সেবা এখনো কনফিগার করা হয়নি। দয়া করে পরে আবার চেষ্টা করুন।'
			];
		}

		// The slug row is what match_subdomain() finds after stripping the apex,
		// so it is the row that decides reachability. Check the slug, not the
		// full hostname: two parishes asking for the same slug would otherwise
		// both be admitted and then fight over who resolves.
		$exists = $this->db->where('hostname', $subdomain)
		                   ->get('tenant_domains')
		                   ->num_rows();

		if ($exists > 0) {
			return [
				'success' => false,
				'message' => 'দুঃখিত, এই সাবডোমেনটি (' . $subdomain . ') ইতিমধ্যে অন্য একটি ইউনিয়ন দ্বারা ব্যবহৃত। অনুগ্রহ করে অন্য সাবডোমেন বেছে নিন।'
			];
		}

		// Check if custom domain already exists
		if (!empty($custom_domain)) {
			$cd_exists = $this->db->where('hostname', $custom_domain)
			                      ->get('tenant_domains')
			                      ->num_rows();
			if ($cd_exists > 0) {
				return [
					'success' => false,
					'message' => 'দুঃখিত, এই কাস্টম ডোমেনটি (' . $custom_domain . ') ইতিমধ্যে অন্য একটি অ্যাকাউন্টে সংযুক্ত।'
				];
			}

			// A custom domain must not be a bare slug, or the platform domain,
			// or anything ending in the apex. Either would make a later signup
			// resolve to the wrong parishad: find_by_host() is an exact match, so
			// claiming 'viretaup.com' as a tenant hostname would outrank step 2
			// for every real platform visit.
			if ($custom_domain === $subdomain
				|| $custom_domain === $apex
				|| $custom_domain === 'localhost'
				|| substr($custom_domain, -1 * (strlen($apex) + 1)) === '.' . $apex)
			{
				return [
					'success' => false,
					'message' => 'দুঃখিত, এই কাস্টম ডোমেনটি ব্যবহার করা যাবে না। অন্য একটি ডোমেন ব্যবহার করুন।'
				];
			}
		}

		// Check if email already exists in users
		$email_exists = $this->db->where('email', $admin_email)->get('users')->num_rows();
		if ($email_exists > 0) {
			return [
				'success' => false,
				'message' => 'দুঃখিত, এই ইমেইলটি (' . $admin_email . ') ইতিমধ্যে নিবন্ধিত। অন্য ইমেইল ব্যবহার করুন অথবা লগইন করুন।'
			];
		}

		// The plan has to exist before the tenant row is written, because
		// package_key and the subscriptions row both reference it. An unknown
		// key falls back to standard rather than failing the signup: a parishad
		// should not be refused a portal over a plan name, and the operator
		// can reassign the plan afterwards from the panel.
		$plan = $this->db->where('package_key', $package)->get('packages')->row();
		if (!$plan) {
			$plan = $this->db->where('package_key', 'standard')->get('packages')->row();
		}
		if (!$plan) {
			// No packages table row at all. Better to refuse than to create a
			// tenant with a subscription pointing at nothing.
			return [
				'success' => false,
				'message' => 'সাবস্ক্রিপশন প্যাকেজ কনফিগার করা হয়নি। দয়া করে সুপার অ্যাডমিনের সাথে যোগাযোগ করুন।'
			];
		}

		$is_self_signup = !empty($input['is_self_signup']);

		$ins_data = [
			'ins_name'        => $union_name,
			'ins_name_en'     => !empty($union_name_en) ? $union_name_en : $union_name,
			'ins_title_ss'    => 'ডিজিটাল নাগরিক সেবা ও হোল্ডিং ট্যাক্স বাতায়ন',
			'ins_emails'      => $admin_email,
			'socib_phone'     => $admin_phone,
			'chairman_phone'  => $admin_phone,
			'sub_domains'     => $subdomain,
			'domain_names'    => !empty($custom_domain) ? $custom_domain : $subdomain . '.' . $apex,
			'addre_s_s'       => !empty($location) ? $location : 'ইউনিয়ন পরিষদ কার্যালয়',
			'chairman_speach' => 'জনগণের সেবা নিশ্চিতকরণ এবং ডিজিটাল বাংলাদেশ গড়ার প্রত্যয়ে আমাদের ইউনিয়ন পরিষদ নিরলসভাবে কাজ করে যাচ্ছে।',
			'cm_name_bn'      => $admin_name,
			// theme_ss is the legacy home of the plan key. Still written, because
			// nothing reads package_key yet on the public side and removing the
			// write would blank the plan for any code still asking theme_ss.
			'theme_ss'        => $plan->package_key,
			'package_key'     => $plan->package_key,
			'start_year_s'    => date('Y'),
			'is_active'       => $is_self_signup ? false : true,
		];

		/*
		 * 1. The tenant row itself.
		 *
		 * The insert result is CHECKED, and the reason is not paranoia.
		 *
		 * ins_titute.ins_titute_p_idds is a serial column, and its sequence
		 * is not guaranteed to be ahead of the ids already in the table — rows
		 * imported from a dump, or created by hand, can leave the sequence
		 * behind. When that happens the INSERT raises a duplicate-key error
		 * and the whole signup FAILS.
		 *
		 * It used to fail silently. The caller took $this->db->insert_id()
		 * regardless, which after a failed insert is not a new id at all but
		 * the last id the connection saw — an id that belongs to somebody
		 * else. Everything below then attached the new parishad's hostnames
		 * and its admin account to that existing tenant. A signup that
		 * appears to succeed and quietly hands the new admin a group on
		 * another parishad's portal, and adds a hostname to it.
		 *
		 * So: no row, no signup. Wrapped in a transaction because the
		 * hostname, subscription and admin rows below are meaningless without it.
		 */
		$this->db->trans_start();

		$this->db->insert('ins_titute', $ins_data);

		if ($this->db->trans_status() === FALSE OR ! $this->db->affected_rows())
		{
			$this->db->trans_rollback();
			$this->db->error(); // lets CI log the real cause; do not surface it

			return [
				'success' => false,
				'message' => 'দুঃখিত, ইউনিয়ন পরিষদটি তৈরি করা যায়নি। অনুগ্রহ করে কিছুক্ষণ পর আবার চেষ্টা করুন।'
			];
		}

		$new_tenant_id = (int) $this->db->insert_id();

		if ($new_tenant_id <= 0)
		{
			$this->db->trans_rollback();

			return [
				'success' => false,
				'message' => 'দুঃখিত, ইউনিয়ন পরিষদটি তৈরি করা যায়নি। অনুগ্রহ করে কিছুক্ষণ পর আবার চেষ্টা করুন।'
			];
		}

		/*
		 * 1b. The subscription row.
		 *
		 * Inside the transaction and not optional. subscriptions has a NOT NULL
		 * FK to packages and a UNIQUE on ins_titute_p_id, so a tenant with no
		 * row is invisible to the subscriptions module, the expiry badge and
		 * the renewal flow all at once — it would look like an unassigned
		 * tenant rather than a missing row, and nothing in the panel could
		 * tell the two apart.
		 *
		 * Self-signups receive a 4-day trial marked pending approval;
		 * platform operator onboardings are activated immediately.
		 */
		if ($is_self_signup) {
			$billing_cycle = 'monthly';
			$start_date    = date('Y-m-d');
			$end_date      = date('Y-m-d', strtotime('+4 days'));
			$sub_status    = 'pending';
			$sub_note      = 'পাবলিক রেজিস্ট্রেশন — ৪ দিনের ট্রায়াল (অনুমোদনের অপেক্ষায়)';
		} else {
			$billing_cycle = ($input['cycle'] ?? $input['billing_cycle'] ?? 'yearly') === 'monthly' ? 'monthly' : 'yearly';
			$end_date = !empty($input['end_date']) ? trim($input['end_date']) : '';
			if (empty($end_date)) {
				$end_date = ($billing_cycle === 'monthly')
					? date('Y-m-d', strtotime('+1 month'))
					: date('Y-m-d', strtotime('+1 year'));
			}
			$start_date = min(date('Y-m-d'), $end_date);
			$sub_status = 'active';
			$sub_note   = 'স্বয়ংক্রিয় অনবোর্ডিং (' . ($billing_cycle === 'monthly' ? 'মাসিক' : 'বাৎসরিক') . ')';
		}

		$this->db->insert('subscriptions', [
			'ins_titute_p_id' => $new_tenant_id,
			'package_id'      => $plan->package_id,
			'billing_cycle'   => $billing_cycle,
			// min() against start_date, because subscriptions_exp_check is
			// CHECK (end_date >= start_date)
			'start_date'      => $start_date,
			'end_date'        => $end_date,
			'status'          => $sub_status,
			'note'            => $sub_note,
		]);

		// The legacy column the rest of the app still reads. Kept in step with
		// subscriptions.end_date so the two can never disagree about when a
		// tenant lapses. See docs/SADMIN-PANEL.md for why this is not the
		// source of truth and what still reads it.
		$this->db->where('ins_titute_p_idds', $new_tenant_id)
		         ->update('ins_titute', [
			'expire_times' => $end_date
		]);

		/*
		 * 2. tenant_domains — three rows, and each answers a different question.
		 *
		 *   a) the bare SLUG. match_subdomain() strips the apex off the Host
		 *      header and looks up whatever is left, so THIS is the row that
		 *      makes <slug>.<apex> reach this tenant. Missing it means the
		 *      subdomain resolves in DNS and 404s in the app.
		 *   b) the full hostname. Not used for subdomain matching; it exists so
		 *      the admin UI and the login redirect can show and use a real DNS
		 *      name, and so the platform can answer "which hosts does this
		 *      parishad own?".
		 *   c) the custom domain, when the parishad brought its own.
		 *
		 * is_primary is the resolver's step-6 fallback, and it is deliberately
		 * set on exactly ONE row. Two primaries is not a tie the resolver can
		 * break sensibly: primary_tenant() is ORDER BY d.id LIMIT 1, so the
		 * fallback tenant would be whichever row happened to be inserted first,
		 * silently, and changing an unrelated row would change it. A parishad
		 * with a custom domain gets that as primary, because it is the name
		 * its own visitors actually type.
		 */
		$this->db->insert('tenant_domains', [
			'ins_titute_p_id' => $new_tenant_id,
			'hostname'        => $subdomain,
			'is_primary'      => false,
			'is_verified'     => true,
			'is_dev_only'     => false
		]);

		$this->db->insert('tenant_domains', [
			'ins_titute_p_id' => $new_tenant_id,
			'hostname'        => $subdomain . '.' . $apex,
			'is_primary'      => empty($custom_domain),
			'is_verified'     => true,
			'is_dev_only'     => false
		]);

		if (!empty($custom_domain)) {
			$this->db->insert('tenant_domains', [
				'ins_titute_p_id' => $new_tenant_id,
				'hostname'        => $custom_domain,
				'is_primary'      => true,
				'is_verified'     => true,
				'is_dev_only'     => false
			]);
		}

		/*
		 * 3. Admin user.
		 *
		 * The password is stored as a bcrypt hash and nothing else. This used to
		 * also write password_viewss_s with the raw password, so every account
		 * made through signup had a second, unencrypted copy of its own
		 * credential in the database. A database backup -- or anyone with plain
		 * SELECT on users -- held every parishad admin's password in the clear.
		 *
		 * The column itself stays: it is legacy, and dropping it is a schema
		 * migration with its own approval. It is simply no longer written, and
		 * api_login() no longer reads it, so this removes the plaintext copy
		 * without locking anybody out.
		 */
		$user_data = [
			'username'          => $admin_name,
			'password'          => $this->ion_auth_model->hash_password($admin_password),
			'email'             => $admin_email,
			'phone'             => $admin_phone,
			'created_on'        => time(),
			'active'            => 1,
			'first_name'        => $admin_name,
			'ins_pr_id_s'       => $new_tenant_id,
			'u_type'            => $subdomain,
		];
		$this->db->insert('users', $user_data);
		$new_user_id = $this->db->insert_id();

		/*
		 * 4. What the visitor is told to visit.
		 *
		 * Both URLs used to be built for a developer running php -S: one
		 * carried ?tenant=<id>, a development override that does not exist in
		 * production, and the other hardcoded :8080. Both were wrong the moment
		 * this ran on a real host.
		 *
		 * The scheme is derived from the request rather than hardcoded, so the
		 * confirmation page links to exactly the protocol the visitor arrived
		 * on -- https behind the production TLS terminator, http on localhost.
		 */
		$scheme       = (is_https() ? 'https://' : 'http://');
		$config_is_dev = (bool) $this->config->item('is_development');

		// 5. Assign Admin group (1) & Chairman group (4)
		$this->db->insert('users_groups', [
			'user_id'  => $new_user_id,
			'group_id' => 1
		]);
		$this->db->insert('users_groups', [
			'user_id'  => $new_user_id,
			'group_id' => 4
		]);

		/*
		 * 6. Commit — or roll the whole signup back.
		 *
		 * A tenant row with no hostnames, or an admin with no group, is a
		 * parishad that exists in the database and cannot be reached or
		 * logged into. Partial signup is worse than a failed one, because the
		 * visitor is told it succeeded.
		 *
		 * The user insert is checked for the same reason the tenant one is:
		 * an unchecked insert_id() is how a signup ended up attached to
		 * somebody else's tenant in the first place.
		 */
		if ($new_user_id <= 0 OR $this->db->trans_status() === FALSE)
		{
			$this->db->trans_rollback();

			return [
				'success' => false,
				'message' => 'দুঃখিত, ইউনিয়ন পরিষদটি তৈরি করা যায়নি। অনুগ্রহ করে কিছুক্ষণ পর আবার চেষ্টা করুন।'
			];
		}

		$this->db->trans_commit();

		/*
		 * 7. The audit row, after the commit and outside the transaction.
		 *
		 * Written here rather than at the call sites so an unauthenticated
		 * signup and a sadmin's onboard button produce the same evidence. The
		 * password is not in any of it: the after-data carries the tenant id,
		 * the plan key and the admin's email, which is enough to answer "who
		 * was given access" without the trail becoming a place credentials
		 * leak to.
		 */
		audit_log('create', 'tenant', [
			'entity_id' => (string) $new_tenant_id,
			'tenant_id' => $new_tenant_id,
			'summary'   => 'নতুন ইউনিয়ন অনবোর্ড: ' . $union_name . ' (' . $subdomain . '.' . $apex . '), প্যাকেজ ' . $plan->package_key,
			'after'     => [
				'ins_titute_p_idds' => $new_tenant_id,
				'ins_name'          => $union_name,
				'sub_domains'       => $subdomain,
				'package_key'       => $plan->package_key,
				'custom_domain'     => $custom_domain,
				'admin_email'       => $admin_email,
			],
		]);

		$success_msg = $is_self_signup
			? 'আপনার পরিষদ রেজিস্ট্রেশনের আবেদনটি সফলভাবে জমা হয়েছে। প্ল্যাটফর্ম অ্যাডমিন তথ্য যাচাই করে অনুমোদন দেওয়া মাত্রই আপনার ৪ দিনের ফ্রি ট্রায়াল সক্রিয় হবে।'
			: 'অভিনন্দন! আপনার ইউনিয়ন পরিষদ সফলভাবে সক্রিয় হয়েছে।';

		return [
			'success'             => true,
			'message'             => $success_msg,
			'tenant_id'           => $new_tenant_id,
			'subdomain'           => $subdomain,
			'custom_domain'       => $custom_domain,
			'package_key'         => $plan->package_key,
			'end_date'            => $end_date,
			'is_pending_approval' => $is_self_signup,
			'tenant_url'          => $config_is_dev
				? base_url('?tenant=' . $new_tenant_id)
				: $scheme . (!empty($custom_domain) ? $custom_domain : $subdomain . '.' . $apex) . '/',
			'subdomain_url'       => $scheme . $subdomain . '.' . $apex . '/',
			'identity'            => $admin_email
		];
	}
}
