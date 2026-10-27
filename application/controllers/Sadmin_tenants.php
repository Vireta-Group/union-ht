<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Sadmin_tenants — the tenant directory
 * =====================================================================
 *
 * This is the module that used to BE the whole super-admin panel: the
 * directory table, the onboard modal and the three tenant APIs all lived
 * in Super_admin. They are here now so that Super_admin can be the
 * overview and this can grow its own concerns — plan assignment, the
 * suspension switch, per-tenant social settings — without a controller
 * named "the panel" accumulating them.
 *
 * The three APIs keep answering on their old super_admin/api_* URLs. See
 * the route table for why.
 *
 * The auth gate is require_sadmin() from the autoloaded sadmin helper,
 * not an inline in_group() check. Six panel controllers each pasting the
 * check is how the fifth one ends up forgetting it.
 */
class Sadmin_tenants extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->library(['ion_auth', 'session']);

		// The gate. It halts on failure — see sadmin_helper.php for why
		// anonymous and wrong-role both answer 403 rather than one of them
		// being distinguishable.
		require_sadmin();
	}

	/**
	 * The tenant directory table.
	 *
	 * Everything here is a CROSS-tenant read, so nothing is scoped to
	 * tenant_code(). That is the point of this controller and also why it
	 * must never be reachable without require_sadmin(): a missing gate on
	 * this page is the whole parishad list.
	 */
	public function index()
	{
		$tenants = $this->db->order_by('ins_titute_p_idds', 'asc')->get('ins_titute')->result();

		/*
		 * The is_dev_only filter is the same one Tenant_resolver::find_by_host()
		 * applies, and it is load-bearing here for the same reason: a row marked
		 * dev-only has to stop being advertised the moment APP_ENV leaves
		 * development. Without it this panel lists kanchan.localhost and
		 * baghmara.localhost as live sites in production, and the view turns each
		 * one into a clickable link.
		 *
		 * tenant_dev_domains_allowed() is the public face of the resolver's own
		 * protected allows_dev_domains(), so there is one rule and not two.
		 */
		$allow_dev = function_exists('tenant_dev_domains_allowed')
			? tenant_dev_domains_allowed()
			: (bool) $this->config->item('is_development');

		$this->db->group_start();
		$this->db->where('is_dev_only', false);
		if ($allow_dev) {
			$this->db->or_where('is_dev_only', true);
		}
		$this->db->group_end();

		$all_domains = $this->db->order_by('ins_titute_p_id', 'asc')
		                              ->get('tenant_domains')
		                              ->result();

		// The panel needs to know which hostnames were dev-only even when the
		// filter hid them, so a row that was filtered out reads as "not a real
		// site" in the view rather than as a typo.
		$data['dev_hosts_visible'] = $allow_dev;

		$domains_by_tenant = [];
		$total_custom_domains = 0;
		$total_subdomains = 0;

		foreach ($all_domains as $d) {
			$domains_by_tenant[$d->ins_titute_p_id][] = $d;
			if (str_ends_with($d->hostname, '.gov.bd') || (!str_ends_with($d->hostname, '.localhost') && !str_ends_with($d->hostname, '.local') && !str_contains($d->hostname, 'viretaup.com') && $d->hostname !== 'localhost' && $d->hostname !== '127.0.0.1')) {
				$total_custom_domains++;
			} else {
				$total_subdomains++;
			}
		}

		// 3. Fetch all admin users per tenant
		$all_users = $this->db->select('id, username, email, phone, ins_pr_id_s, active, last_login')
		                      ->get('users')
		                      ->result();
		$users_by_tenant = [];
		foreach ($all_users as $u) {
			if (!empty($u->ins_pr_id_s)) {
				$users_by_tenant[$u->ins_pr_id_s][] = $u;
			}
		}

		// 4. Holdings count per tenant
		$holdings_counts = [];
		$holding_rows = $this->db->select('ins_aut_iiid, count(*) as total')
		                         ->group_by('ins_aut_iiid')
		                         ->get('family_data_sheet')
		                         ->result();
		$total_all_holdings = 0;
		foreach ($holding_rows as $hr) {
			$holdings_counts[$hr->ins_aut_iiid] = (int)$hr->total;
			$total_all_holdings += (int)$hr->total;
		}

		// Attach data to each tenant
		/*
		 * package_key and name_bn live on packages, not on subscriptions.
		 * They are folded onto the subscription object here so that both the
		 * table cell in this view and the edit modal's JSON blob can read
		 * sub.package_key / sub.name_bn. The modal is handed
		 * json_encode($t), so anything not attached here simply is not there
		 * for the JavaScript, and the plan select would open blank.
		 *
		 * The join is also the reason $plan_keys (which the old code built
		 * and then never used) is gone: it was a second full read of
		 * packages to get a list of keys this already carries.
		 */
		$subs = [];
		$sub_rows = $this->db->select('s.*, p.package_key, p.name_bn, p.price_yearly, p.price_monthly')
			->from('subscriptions s')
			->join('packages p', 'p.package_id = s.package_id')
			->get()
			->result();
		foreach ($sub_rows as $sub) {
			$subs[$sub->ins_titute_p_id] = $sub;
		}

		$pending_count = 0;
		foreach ($tenants as &$t) {
			$tid = $t->ins_titute_p_idds;
			$t->domains = $domains_by_tenant[$tid] ?? [];
			$t->users = $users_by_tenant[$tid] ?? [];
			$t->holdings_count = $holdings_counts[$tid] ?? 0;
			$t->subscription = $subs[$tid] ?? NULL;
			$t->is_pending_approval = (!$t->is_active && $t->subscription && $t->subscription->status === 'pending');
			if ($t->is_pending_approval) {
				$pending_count++;
			}

			// Primary domain / preview URL
			$preview_host = null;
			foreach ($t->domains as $dm) {
				if (str_ends_with($dm->hostname, '.localhost') || str_ends_with($dm->hostname, '.local')) {
					$preview_host = $dm->hostname;
					break;
				}
			}
			if (!$preview_host && !empty($t->domains)) {
				$preview_host = $t->domains[0]->hostname;
			}
			$t->preview_host = $preview_host ?: 'localhost';
		}
		unset($t);

		$data += [
			'tenants'              => $tenants,
			'total_tenants'        => count($tenants),
			'pending_count'        => $pending_count,
			'total_custom_domains' => $total_custom_domains,
			'total_subdomains'     => $total_subdomains,
			'total_holdings'       => $total_all_holdings,
			'plans'                => $this->db->order_by('sort_order')->get('packages')->result(),
			'sa_title'             => 'ইউনিয়ন পরিষদ',
			'sa_desc'              => 'সব ইউনিয়নের তালিকা, ডোমেন ব্যবস্থাপনা ও নতুন ইউনিয়ন অনবোর্ডিং।',
		];

		$this->template->sadmin('super_admin/tenants', $data);
	}

	/**
	 * API: Update Tenant Details, Subdomain, Custom Domain, Package, Expiry
	 *
	 * CHANGED 2026-09-30 versus the version in Super_admin:
	 *
	 *   - writes package_key, and not just the legacy theme_ss. The plan was
	 *     previously editable from the panel's edit modal and then went
	 *     nowhere, because the modal had no plan field at all and
	 *     api_create_tenant read `package` and discarded it.
	 *   - every field is diffed against the row as it stands and only real
	 *     changes are written, so the audit trail records what moved instead
	 *     of a copy of the whole tenant submitted back to itself. The
	 *     original built $update_data with `if ($field)`, which also meant a
	 *     field the sadmin deliberately blanked was silently kept.
	 *   - the response reports which fields changed, so the view can say so.
	 */
	public function api_update_tenant()
	{
		$input = sadmin_api_input();

		$tenant_id = (int)($input['tenant_id'] ?? 0);
		if ($tenant_id <= 0) {
			sadmin_fail('অবৈধ টেন্যান্ট আইডি।');
		}

		$tenant = $this->db->where('ins_titute_p_idds', $tenant_id)->get('ins_titute')->row();
		if (!$tenant) {
			sadmin_fail('টেন্যান্ট পাওয়া যায়নি।', 404);
		}

		/*
		 * Blank-able fields. These go through the isset() test, not a truth
		 * test, so clearing a field works — a sadmin removing a custom
		 * subdomain needs that to take effect.
		 *
		 * package_key is NOT in this list any more. It is resolved once,
		 * further down, together with the subscriptions row it has to agree
		 * with — see the note there.
		 */
		$nullable = [
			'ins_name'          => 'ins_name',
			'ins_name_en'       => 'ins_name_en',
			'ins_emails'        => 'ins_emails',
			'socib_phone'       => 'socib_phone',
			'chairman_phone'    => 'chairman_phone',
			'addre_s_s'         => 'addre_s_s',
		];
		$trimmed = [
			'custom_domain' => 'custom_domain',
		];

		$before = [];
		$after  = [];
		$update_data = [];

		foreach ($nullable as $post_key => $column) {
			if (!array_key_exists($post_key, $input)) {
				continue; // field absent from the request, not blanked
			}
			$new = trim((string) $input[$post_key]);
			if ((string) $tenant->$column !== $new) {
				$update_data[$column] = $new === '' ? NULL : $new;
				$before[$column] = $tenant->$column;
				$after[$column]  = $update_data[$column];
			}
		}

		foreach ($trimmed as $post_key => $column) {
			if (!array_key_exists($post_key, $input)) {
				continue;
			}
			$new = trim((string) $input[$post_key]);

			if ($post_key === 'custom_domain') {
				$new = strtolower(preg_replace('#^https?://#i', '', $new));
				$new = trim(rtrim($new, '/'));
			}

			if ((string) $tenant->$column !== $new) {
				$update_data[$column] = $new === '' ? NULL : $new;
				$before[$column] = $tenant->$column;
				$after[$column]  = $update_data[$column];
			}
		}

		/*
		 * The plan, resolved ONCE, for both ins_titute.package_key and the
		 * subscriptions row.
		 *
		 * The two are written together on purpose. A package_key on the
		 * tenant with a subscriptions row still pointing at the old plan is
		 * a state the panel has no way to display — the directory cell reads
		 * the subscription, not the column — and the only way it would ever
		 * be reconciled is by hand.
		 *
		 * An unknown key is refused rather than written. ins_titute.
		 * package_key has no foreign key: the column was added additively to
		 * a legacy table, so nothing else would stop a typo from becoming a
		 * tenant pointing at a plan that does not exist.
		 *
		 * Note the test is array_key_exists, not isset. "— প্যাকেজ নেই —" in
		 * the select posts an empty string, and the resulting NULL is a real
		 * change the sadmin asked for and must be written.
		 */
		$plan_change = NULL;
		if (array_key_exists('package_key', $input)) {
			$pk = trim((string) $input['package_key']);
			$pk = ($pk === '') ? NULL : $pk;

			$plan_row = NULL;
			if ($pk !== NULL) {
				$plan_row = $this->db->where('package_key', $pk)->get('packages')->row();
				if (!$plan_row) {
					sadmin_fail('এই প্যাকেজটি সিস্টেমে নেই। সাবস্ক্রিপশন মডিউল থেকে বৈধ প্যাকেজ দেখুন।');
				}
			}

			if ($pk !== $tenant->package_key) {
				$plan_change = [
					'package' => $plan_row,
					'from'    => $tenant->package_key,
					'to'      => $pk,
				];
				$update_data['package_key'] = $pk;
				$before['package_key'] = $tenant->package_key;
				$after['package_key']  = $pk;
				if ($pk !== NULL) {
					// theme_ss is what the public side still reads. Kept in
					// step, or the plan would differ between the panel and the
					// parishad's own portal.
					$update_data['theme_ss'] = $pk;
					$after['theme_ss'] = $pk;
				}
			}
		}

		/*
		 * end_date, and which column it means.
		 *
		 * The modal used to edit ins_titute.expire_times — a varchar(180)
		 * holding three formats at once. Tenant 1 has a unix timestamp in it
		 * (1735667965), tenant 10 has a YYYY-MM-DD date, and three tenants
		 * have nothing. An <input type="date"> cannot render a unix
		 * timestamp, so tenant 1's expiry opened blank, and submitting the
		 * modal with one unrelated field changed wrote an empty string over
		 * the only recorded expiry on that account.
		 *
		 * subscriptions.end_date is a DATE with a CHECK against start_date
		 * and is what the panel's "expiring soon" badge counts from. That is
		 * what this writes. The legacy column is then mirrored from it, in
		 * the one format it is supposed to hold, so anything still reading
		 * expire_times sees a value rather than having to guess.
		 */
		$end_date = NULL;
		if (array_key_exists('end_date', $input)) {
			$ed = trim((string) $input['end_date']);
			if ($ed !== '') {
				// strtotime() returns false for a date that does not exist and
				// for one that does not parse at all. Both are refused here
				// rather than reaching the DATE column, where Postgres would
				// raise and take the whole request down.
				if (strtotime($ed) === false) {
					sadmin_fail('মেয়াদের তারিখ বোঝা যায়নি। YYYY-MM-DD ফরম্যাটে দিন।');
				}
				$end_date = date('Y-m-d', strtotime($ed));
			}
		}

		sadmin_write(function () use ($tenant_id, $tenant, $update_data, $before, $after, $input, $plan_change, $end_date) {
			// Custom domain is not an ins_titute column update — it is a
			// tenant_domains row, with its own uniqueness rules. Handled after
			// the plain field update so a rejected domain does not leave half
			// the form applied.
			$domain_msg = NULL;
			$custom_domain = strtolower(trim($input['custom_domain'] ?? ''));
			if ($custom_domain !== '') {
				$domain_msg = $this->apply_custom_domain($tenant_id, $tenant, $custom_domain, $before, $after);
				if ($domain_msg === FALSE) {
					return FALSE;
				}
			}

			/*
			 * One transaction for the tenant row, the plan and the expiry.
			 * They are three statements describing one edit, and half of one
			 * — a tenant with a new plan and no subscription row, or a
			 * subscription moved to a plan the tenant does not name — is
			 * worse than the edit not having happened.
			 */
			$this->db->trans_start();

			if (!empty($update_data)) {
				$this->db->where('ins_titute_p_idds', $tenant_id)->update('ins_titute', $update_data);
			}

			$sub_note   = NULL;
			$expire_note = NULL;
			if ($plan_change !== NULL) {
				$sub_note = $this->write_subscription_plan($tenant_id, $plan_change['package'], $plan_change['from'], $plan_change['to']);
			}
			if ($end_date !== NULL) {
				$expire_note = $this->write_subscription_end_date($tenant_id, $end_date);
			}

			if ($this->db->trans_status() === FALSE) {
				$this->db->trans_rollback();
				sadmin_fail('তথ্য সংরক্ষণ করা যায়নি। কোনো পরিবর্তন সংরক্ষিত হয়নি।', 500);
			}
			$this->db->trans_commit();

			$notes = array_filter([$domain_msg, $sub_note, $expire_note]);

			return [
				'success'     => true,
				'message'     => 'ইউনিয়ন পরিষদের তথ্য সফলভাবে আপডেট হয়েছে!'
					. ($notes ? ' (' . implode('; ', $notes) . ')' : ''),
				'changed'     => array_keys($after),
				'domain_note' => $domain_msg,
				'audit'       => [
					'entity_id' => (string) $tenant_id,
					'tenant_id' => $tenant_id,
					'summary'   => $after
						? ('ইউনিয়ন তথ্য আপডেট: ' . implode(', ', array_keys($after)))
						: 'ইউনিয়ন তথ্যে কোনো পরিবর্তন ছিল না',
					'before'    => $before,
					'after'     => $after,
				],
			];
		}, 'update', 'tenant');
	}

	/**
	 * Point this tenant's subscription at a package, creating the row if
	 * there is not one yet.
	 *
	 * A tenant with no subscriptions row is a real state, not a mistake: the
	 * migration seeds one row per existing tenant, so from 2026-09-30 a new
	 * one only appears if a row was deleted by hand, or if a tenant was
	 * created by a path that skipped Tenant_creator. The INSERT is written
	 * for that case rather than refusing, because a sadmin assigning a plan
	 * to a tenant that lacks a row is asking for a plan to be recorded, and
	 * "you already have a package on file, go and fix it elsewhere" is not
	 * an answer to that.
	 *
	 * Clearing the plan is different. package_id is NOT NULL with a foreign
	 * key, so there is no way to store "no plan" in a row that exists — the
	 * row is deleted instead, and ins_titute.package_key is left NULL. That
	 * is the only honest representation available, and the directory cell
	 * already has a rendering for it.
	 *
	 * @param  object|null $package  The packages row, or NULL to clear.
	 * @param  string|null $from     Previous key, for the note.
	 * @param  string|null $to       New key, for the note.
	 * @return string                Note for the operator.
	 */
	private function write_subscription_plan($tenant_id, $package, $from, $to)
	{
		if ($package === NULL) {
			$this->db->where('ins_titute_p_id', $tenant_id)->delete('subscriptions');
			return 'প্যাকেজ বাতিল — সাবস্ক্রিপশন রোক মুছে ফেলা হয়েছে';
		}

		$sub = $this->db->where('ins_titute_p_id', $tenant_id)->get('subscriptions')->row();

		if ($sub) {
			// Re-pointing an existing subscription. start_date is left alone:
			// the paid period began then, and moving it would restate when
			// the tenant bought what. end_date is not touched either — a
			// plan change is not a renewal, and the two are separate edits.
			$this->db->where('subscription_id', $sub->subscription_id)
			         ->update('subscriptions', ['package_id' => $package->package_id]);
		} else {
			$this->db->insert('subscriptions', [
				'ins_titute_p_id' => $tenant_id,
				'package_id'      => $package->package_id,
				'billing_cycle'   => 'yearly',
				'start_date'      => date('Y-m-d'),
				'end_date'        => date('Y-m-d', strtotime('+1 year')),
				'status'          => 'active',
				'note'            => 'সুপার অ্যাডমিন প্যানেল থেকে প্যাকেজ নিয়োগ',
			]);
			// The mirrored legacy column, or a tenant that had no expiry
			// would still read NULL from expire_times.
			$this->db->where('ins_titute_p_idds', $tenant_id)
			         ->update('ins_titute', ['expire_times' => date('Y-m-d', strtotime('+1 year'))]);
		}

		return 'প্যাকেজ ' . ($from !== NULL ? $from : '—') . ' → ' . $to . ' সেট হয়েছে';
	}

	/**
	 * Set the subscription's expiry, and mirror it into the legacy column.
	 *
	 * Two writes, one meaning. ins_titute.expire_times is varchar(180) and is
	 * the reason this helper exists: it held a unix timestamp on one tenant, a
	 * date on another and nothing on three more, and every read of it had to
	 * guess. It is not read for the panel's own figures anywhere, so writing
	 * a single canonical YYYY-MM-DD into it is enough to stop the guessing —
	 * and the migration deliberately did not retype the column, so this stays
	 * a string.
	 *
	 * A tenant with no subscriptions row gets one, because there is nowhere
	 * else to record an expiry. Standard plan, one year, today — the same
	 * default Tenant_creator uses, so two paths cannot invent two different
	 * starting terms.
	 *
	 * @return string  Note for the operator.
	 */
	private function write_subscription_end_date($tenant_id, $end_date)
	{
		$sub = $this->db->where('ins_titute_p_id', $tenant_id)->get('subscriptions')->row();

		if (!$sub) {
			$plan = $this->db->where('package_key', 'standard')->get('packages')->row();
			if (!$plan) {
				sadmin_fail('মেয়াদ রাখা যায়নি — সিস্টেমে কোনো প্যাকেজ নেই। সাবস্ক্রিপশন মডিউল থেকে প্যাকেজ তৈরি করুন।', 500);
			}
			$this->db->insert('subscriptions', [
				'ins_titute_p_id' => $tenant_id,
				'package_id'      => $plan->package_id,
				'billing_cycle'   => 'yearly',
				// start_date is min()'d against the end so a past expiry does
				// not trip subscriptions_exp_check on the INSERT below.
				'start_date'      => min(date('Y-m-d'), $end_date),
				'end_date'        => $end_date,
				'status'          => 'active',
				'note'            => 'সুপার অ্যাডমিন প্যানেল থেকে মেয়াদ নির্ধারণ',
			]);
		} else {
			// subscriptions_exp_check is CHECK (end_date >= start_date), so a
			// date before the start is refused by the database. Catching it
			// here turns a failed statement inside a transaction into a
			// message naming the actual problem.
			if ($end_date < $sub->start_date) {
				sadmin_fail('মেয়াদের তারিখ সাবস্ক্রিপশন শুরুর তারিখের (' . $sub->start_date . ') আগে হতে পারে না।');
			}
			$this->db->where('subscription_id', $sub->subscription_id)
			         ->update('subscriptions', ['end_date' => $end_date]);
		}

		$this->db->where('ins_titute_p_idds', $tenant_id)
		         ->update('ins_titute', ['expire_times' => $end_date]);

		return 'মেয়াদ ' . $end_date . ' নির্ধারিত হয়েছে';
	}

	/**
	 * Attach or move a custom domain, and make it this tenant's primary host.
	 *
	 * @param  array $before  Audit before-map, by reference: the hostname row
	 *                        that was there before is recorded here so a
	 *                        domain swap is visible as a swap, not as an add.
	 * @return string|false    A note for the operator, or FALSE on refusal.
	 */
	private function apply_custom_domain($tenant_id, $tenant, $custom_domain, array &$before, array &$after)
	{
		$existing = $this->db->where('hostname', $custom_domain)->get('tenant_domains')->row();
		if ($existing && $existing->ins_titute_p_id != $tenant_id) {
			// Refuses by hostname, cross-tenant, and the answer does not say
			// whose it is. Telling the requester which parishad owns a domain
			// is a free tenant-enumeration oracle.
			sadmin_fail('এই ডোমেনটি ইতিমধ্যে অন্য একটি ইউনিয়নে নিবন্ধিত আছে।');
			return FALSE;
		}

		if (!$existing) {
			$this->db->insert('tenant_domains', [
				'ins_titute_p_id'  => $tenant_id,
				'ins_titute_un_id' => $tenant->un_iddd_iiid,
				'hostname'         => $custom_domain,
				// A custom domain becomes primary because it is the name the
				// parishad's own visitors type. primary_tenant() reads one row,
				// so any other primary this tenant had is cleared first —
				// otherwise there are two and the fallback is insert order.
				'is_primary'       => true,
				'is_verified'      => true,
				'is_dev_only'      => false
			]);
			$before['custom_domain'] = NULL;
			$after['custom_domain']  = $custom_domain;
		} else {
			$this->db->where('id', $existing->id)->update('tenant_domains', [
				'is_verified' => true,
				'is_primary'  => true
			]);
			if (! $existing->is_primary) {
				$before['custom_domain'] = $existing->hostname;
				$after['custom_domain']  = $custom_domain;
			}
		}

		// Clear this tenant's other primary rows so exactly one remains.
		$this->db->where('ins_titute_p_id', $tenant_id)
		         ->where('is_primary', true)
		         ->where('hostname !=', $custom_domain)
		         ->update('tenant_domains', ['is_primary' => false]);

		$this->db->where('ins_titute_p_idds', $tenant_id)
		         ->update('ins_titute', ['domain_names' => $custom_domain]);

		return 'কাস্টম ডোমেন যুক্ত হয়েছে: ' . $custom_domain;
	}

	/**
	 * API: Fast Provision New Tenant directly from Super Admin
	 *
	 * Now a delegate to Tenant_creator::create() — the same code the public
	 * signup runs. The copy that used to live here hardcoded '.localhost'
	 * and '.viretaup.com' instead of reading the configured apex, wrote no
	 * bare-slug row, could leave two is_primary rows behind, and took
	 * insert_id() from a failed insert. Full rationale in the model.
	 */
	public function api_create_tenant()
	{
		$this->load->model('Tenant_creator');
		$result = $this->Tenant_creator->create(sadmin_api_input());

		if (empty($result['success'])) {
			sadmin_fail($result['message'] ?? 'ইউনিয়ন তৈরি করা যায়নি।');
		}

		// The model already audit_logged the create, including the admin email
		// and the plan. It does not record that a platform operator did it
		// rather than a visitor, so that is added here as a separate row
		// rather than a second write over the first.
		audit_log('create', 'tenant', [
			'entity_id' => (string) $result['tenant_id'],
			'tenant_id' => $result['tenant_id'],
			'summary'   => 'সুপার অ্যাডমিন প্যানেল থেকে অনবোর্ড: ' . ($result['subdomain'] ?? ''),
		]);

		$result['message'] = "নতুন ইউনিয়ন পরিষদ সফলভাবে অনবোর্ড করা হয়েছে!";
		sadmin_json($result);
	}

	/**
	 * API: Toggle domain verification status
	 *
	 * A verified hostname is one the resolver will serve. Toggling it off
	 * does not delete the row, so the domain is not handed to another
	 * tenant while it is being fixed — which is what deleting would do,
	 * since hostname carries a UNIQUE constraint.
	 */
	public function api_toggle_domain()
	{
		$input = sadmin_api_input();

		$domain_id = (int)($input['domain_id'] ?? 0);
		$domain = $this->db->where('id', $domain_id)->get('tenant_domains')->row();

		if (!$domain) {
			sadmin_fail('ডোমেন পাওয়া যায়নি।', 404);
		}

		$new_status = !$domain->is_verified;
		$this->db->where('id', $domain_id)->update('tenant_domains', ['is_verified' => $new_status]);

		$tenant_name = $this->db->where('ins_titute_p_idds', $domain->ins_titute_p_id)
			->get('ins_titute')->row();

		sadmin_write(function () use ($domain, $new_status, $domain_id) {
			return [
				'success'     => true,
				'is_verified' => $new_status,
				'message'     => $new_status
					? 'ডোমেন ভেরিফিকেশন চালু হয়েছে।'
					: 'ডোমেন ভেরিফিকেশন বন্ধ করা হয়েছে।',
				'audit'       => [
					'entity_id' => (string) $domain_id,
					'tenant_id' => (int) $domain->ins_titute_p_id,
					'summary'   => $domain->hostname . ' — ভেরিফিকেশন ' . ($new_status ? 'চালু' : 'বন্ধ'),
					'before'    => ['is_verified' => (bool) $domain->is_verified],
					'after'     => ['is_verified' => $new_status],
				],
			];
		}, 'toggle', 'domain');
	}

	/**
	 * API: suspend or reactivate a tenant.
	 *
	 * WHAT is_active DOES NOT DO, and this is the important part:
	 *
	 * It gates NOTHING yet. It is a flag the panel records and the
	 * directory displays; no controller reads it, so a suspended tenant's
	 * portal keeps serving exactly as before. It is the data half of a
	 * feature whose enforcement half is deliberately a separate change,
	 * because switching a parishad's public site off is a bigger decision
	 * than adding a column, and it wants doing with the resolver's own
	 * is_dev_only path to think about rather than as a side effect of a
	 * migration.
	 *
	 * A tenant with subscriptions and holdings is NOT blocked from being
	 * suspended — that is the operator's call, not the panel's — but the
	 * view states the counts so the click is an informed one.
	 */
	public function api_toggle_tenant_active()
	{
		$input = sadmin_api_input();

		$tenant_id = (int)($input['tenant_id'] ?? 0);
		$tenant = $this->db->where('ins_titute_p_idds', $tenant_id)->get('ins_titute')->row();
		if (!$tenant) {
			sadmin_fail('টেন্যান্ট পাওয়া যায়নি।', 404);
		}

		$new_status = ! $tenant->is_active;
		$this->db->where('ins_titute_p_idds', $tenant_id)->update('ins_titute', ['is_active' => $new_status]);

		sadmin_write(function () use ($tenant, $tenant_id, $new_status) {
			return [
				'success' => true,
				'is_active' => $new_status,
				'message' => $new_status
					? '"' . $tenant->ins_name . '" আবার সক্রিয় করা হয়েছে।'
					: '"' . $tenant->ins_name . '" সাময়িকভাবে স্থগিত করা হয়েছে।',
				'audit' => [
					'entity_id' => (string) $tenant_id,
					'tenant_id' => $tenant_id,
					'summary'   => $tenant->ins_name . ' — ' . ($new_status ? 'সক্রিয়' : 'স্থগিত'),
					'before'    => ['is_active' => (bool) $tenant->is_active],
					'after'     => ['is_active' => $new_status],
				],
			];
		}, 'toggle', 'tenant');
	}

	/**
	 * Creates a temporary auto-expiring tenant support admin (1 to 72 hours).
	 */
	public function api_create_temp_admin()
	{
		$input = sadmin_api_input();
		$tenant_id = (int)($input['tenant_id'] ?? 0);
		$duration = (int)($input['duration_hours'] ?? 24);
		$role_key = in_array(($input['role_key'] ?? ''), ['uphead', 'upsocib'], true) ? $input['role_key'] : 'uphead';
		$reason = trim($input['reason'] ?? '');

		$res = create_temp_tenant_admin($tenant_id, $duration, $role_key, $reason, $this->ion_auth->get_user_id());
		if (!$res['success']) {
			sadmin_fail($res['error'] ?? 'অস্থায়ী অ্যাকাউন্ট তৈরিতে ব্যর্থতা।', 400);
		}

		sadmin_write(function () use ($res) {
			return [
				'success' => true,
				'data'    => $res,
				'message' => 'অস্থায়ী সাপোর্ট অ্যাডমিন তৈরি সম্পন্ন হয়েছে। নির্ধারিত মেয়াদ: ' . $res['duration_hours'] . ' ঘণ্টা।',
				'audit'   => [
					'entity_id' => (string)$res['temp_id'],
					'tenant_id' => $res['tenant_id'],
					'summary'   => 'অস্থায়ী সাপোর্ট অ্যাডমিন (' . $res['username'] . ') তৈরি — ' . $res['duration_hours'] . ' ঘণ্টা',
					'after'     => $res
				]
			];
		}, 'create', 'temp_admin');
	}

	/**
	 * Immediately revokes and purges a temporary support admin.
	 */
	public function api_revoke_temp_admin()
	{
		$input = sadmin_api_input();
		$temp_id = (int)($input['temp_id'] ?? 0);

		$ok = revoke_temp_tenant_admin($temp_id);
		if (!$ok) {
			sadmin_fail('অ্যাকাউন্ট বাতিল করা সম্ভব হয়নি বা পাওয়া যায়নি।', 400);
		}

		sadmin_write(function () use ($temp_id) {
			return [
				'success' => true,
				'message' => 'অস্থায়ী অ্যাক্সেস তাৎক্ষণিকভাবে বাতিল ও ডিলিট করা হয়েছে।',
				'audit'   => [
					'entity_id' => (string)$temp_id,
					'summary'   => 'অস্থায়ী সাপোর্ট অ্যাকাউন্ট #' . $temp_id . ' বাতিল করা হয়েছে'
				]
			];
		}, 'revoke', 'temp_admin');
	}

	/**
	 * Lists active temporary admins.
	 */
	public function api_list_temp_admins()
	{
		cleanup_expired_temp_admins();

		$tenant_id = (int)$this->input->get('tenant_id');
		$this->db->select('t.*, i.ins_name')
			->from('tenant_temp_admins t')
			->join('ins_titute i', 'i.ins_titute_p_idds = t.tenant_id')
			->where('t.expires_at > NOW()')
			->where('t.is_revoked', FALSE)
			->order_by('t.id', 'DESC');

		if ($tenant_id > 0) {
			$this->db->where('t.tenant_id', $tenant_id);
		}

		$rows = $this->db->get()->result();
		foreach ($rows as $r) {
			$rem = strtotime($r->expires_at) - time();
			$r->remaining_seconds = max(0, $rem);
			$r->hours_left = floor($rem / 3600);
			$r->minutes_left = floor(($rem % 3600) / 60);
			$r->role_title = ($r->role_key === 'upsocib') ? 'ইউপি সচিব' : 'ইউপি চেয়ারম্যান';
		}

		sadmin_json(['success' => true, 'data' => $rows]);
	}

	/**
	 * API: Approve a pending tenant and activate 4-day free trial.
	 */
	public function api_approve_tenant()
	{
		$input = sadmin_api_input();
		$tenant_id = (int)($input['tenant_id'] ?? 0);
		$tenant = $this->db->where('ins_titute_p_idds', $tenant_id)->get('ins_titute')->row();
		if (!$tenant) {
			sadmin_fail('ইউনিয়ন পরিষদ পাওয়া যায়নি।', 404);
		}

		$sub = $this->db->where('ins_titute_p_id', $tenant_id)->get('subscriptions')->row();
		$trial_end = date('Y-m-d', strtotime('+4 days'));

		$this->db->trans_start();

		// 1. Activate tenant
		$this->db->where('ins_titute_p_idds', $tenant_id)->update('ins_titute', [
			'is_active'    => TRUE,
			'expire_times' => $trial_end
		]);

		// 2. Activate subscription with 4-day trial
		if ($sub) {
			$this->db->where('subscription_id', $sub->subscription_id)->update('subscriptions', [
				'status'     => 'active',
				'start_date' => date('Y-m-d'),
				'end_date'   => $trial_end,
				'note'       => 'অনুমোদিত — ৪ দিনের ফ্রি ট্রায়াল সক্রিয়'
			]);
		}

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			sadmin_fail('অনুমোদন ব্যর্থ হয়েছে। অনুগ্রহ করে আবার চেষ্টা করুন।');
		}

		sadmin_write(function () use ($tenant, $tenant_id, $trial_end) {
			return [
				'success' => true,
				'message' => '"' . $tenant->ins_name . '" সফলভাবে অনুমোদন করা হয়েছে এবং ৪ দিনের ফ্রি ট্রায়াল সক্রিয় করা হয়েছে (মেয়াদ: ' . $trial_end . ')।',
				'audit'   => [
					'entity_id' => (string) $tenant_id,
					'tenant_id' => $tenant_id,
					'summary'   => $tenant->ins_name . ' — অনুমোদন ও ৪ দিনের ট্রায়াল চালু',
					'before'    => ['is_active' => false, 'status' => 'pending'],
					'after'     => ['is_active' => true, 'status' => 'active', 'end_date' => $trial_end]
				]
			];
		}, 'update', 'tenant');
	}

	/**
	 * API: Reject and delete a pending tenant application.
	 */
	public function api_reject_tenant()
	{
		$input = sadmin_api_input();
		$tenant_id = (int)($input['tenant_id'] ?? 0);
		$tenant = $this->db->where('ins_titute_p_idds', $tenant_id)->get('ins_titute')->row();
		if (!$tenant) {
			sadmin_fail('ইউনিয়ন পরিষদ পাওয়া যায়নি।', 404);
		}

		if ($tenant_id === 1) {
			sadmin_fail('মূল সিস্টেম টেন্যান্ট মুছে ফেলা সম্ভব নয়।', 400);
		}

		$this->db->trans_start();

		// Delete associated users
		$this->db->where('ins_pr_id_s', $tenant_id)->delete('users');

		// Delete tenant (cascades tenant_domains, subscriptions)
		$this->db->where('ins_titute_p_idds', $tenant_id)->delete('ins_titute');

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			sadmin_fail('প্রত্যাখ্যান ও মুছে ফেলা ব্যর্থ হয়েছে।');
		}

		sadmin_write(function () use ($tenant, $tenant_id) {
			return [
				'success' => true,
				'message' => '"' . $tenant->ins_name . '"-এর আবেদন সফলভাবে প্রত্যাখ্যান ও মুছে ফেলা হয়েছে।',
				'audit'   => [
					'entity_id' => (string) $tenant_id,
					'tenant_id' => $tenant_id,
					'summary'   => $tenant->ins_name . ' — আবেদন প্রত্যাখ্যান ও মুছে ফেলা'
				]
			];
		}, 'delete', 'tenant');
	}
}

