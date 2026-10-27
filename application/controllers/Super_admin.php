<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Super_admin — the platform overview
 * =====================================================================
 *
 * WHAT MOVED OUT OF HERE, AND WHY
 * This controller used to be the entire panel: the tenant directory, the
 * three tenant APIs, and index(). The directory and the APIs are now
 * Sadmin_tenants, because a controller named "the panel" is where modules
 * accumulate until nobody can find the thing they want to change.
 *
 * What is left is the answer to "what state is the platform in", which is
 * the one question an operator opens a panel to ask. It answers it from
 * tables that did not exist when this file was written — subscriptions
 * and support_tickets — plus the counts that were already here.
 *
 * It is still sadmin-only, and the gate is still the same one. It now
 * calls require_sadmin() from the autoloaded helper instead of carrying
 * its own copy of the in_group() check, for the reason in that helper's
 * docblock: the copy that gets forgotten is the copy that leaks.
 *
 * CROSS-TENANT BY DESIGN
 * Every query below is deliberately unscoped. This is the one place in
 * the application that is supposed to read every parishad's rows, which
 * is exactly why require_sadmin() is the first statement in the
 * constructor and not something further down.
 */
class Super_admin extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->library(['ion_auth', 'session']);

		// The gate. Halts on failure.
		require_sadmin();
	}

	/**
	 * The overview.
	 *
	 * Every figure here is a COUNT, computed in SQL. The alternative —
	 * pulling all tenants and all holdings into PHP and counting — is what
	 * this method used to do for the directory, and it does not scale past
	 * a few parishes: family_data_sheet is the largest table in the
	 * database and it is counted per tenant with a GROUP BY rather than
	 * read in full.
	 *
	 * There is deliberately no "total revenue" figure. subscription_payments
	 * holds money somebody typed in, and summing a column of manual entries
	 * and putting the result on the front page of a panel lends it an
	 * authority it has not earned. The collected total lives in the
	 * subscriptions module, where the payments are listed and can be
	 * argued with.
	 */
	public function index()
	{
		$data = [];

		// Tenants, active and suspended counted separately so the panel can
		// show the number that is actually being served.
		$data['total_tenants']   = (int) $this->db->count_all('ins_titute');
		$data['suspended_count'] = (int) $this->db->where('is_active', false)
			->where('ins_titute_p_idds >', 0)
			->count_all_results('ins_titute');

		// Hostnames. Counted with the same is_dev_only reasoning the tenant
		// directory uses: a dev-only row is a development artefact and must
		// not inflate a production figure. tenant_dev_domains_allowed() is
		// the resolver's own rule, not a second copy of it.
		$allow_dev = function_exists('tenant_dev_domains_allowed')
			? tenant_dev_domains_allowed()
			: (bool) $this->config->item('is_development');

		$this->db->group_start();
		$this->db->where('is_dev_only', false);
		if ($allow_dev) {
			$this->db->or_where('is_dev_only', true);
		}
		$this->db->group_end();
		$data['total_domains'] = (int) $this->db->count_all_results('tenant_domains');
		$this->db->reset_query();

		// SaaS platform revenue & collections metrics (Super admin business view)
		$month_sales_row = $this->db->select('COALESCE(SUM(amount), 0) AS total')
			->where('paid_at >=', date('Y-m-01'))
			->get('subscription_payments')
			->row();
		$data['month_sales'] = (int) ($month_sales_row ? $month_sales_row->total : 0);

		$total_collected_row = $this->db->select('COALESCE(SUM(amount), 0) AS total, COUNT(*) AS count')
			->where('paid_at IS NOT NULL', NULL, FALSE)
			->get('subscription_payments')
			->row();
		$data['total_collected']       = (int) ($total_collected_row ? $total_collected_row->total : 0);
		$data['total_collected_count'] = (int) ($total_collected_row ? $total_collected_row->count : 0);

		// Contracted Annual Recurring Revenue (ARR) across active subscriptions
		$arr_row = $this->db->select('COALESCE(SUM(CASE WHEN s.billing_cycle = \'monthly\' THEN p.price_monthly * 12 ELSE p.price_yearly END), 0) AS total')
			->from('subscriptions s')
			->join('packages p', 'p.package_id = s.package_id')
			->where('s.status', 'active')
			->get()
			->row();
		$data['total_arr'] = (int) ($arr_row ? $arr_row->total : 0);

		$data['total_users']    = (int) $this->db->count_all('users');

		/*
		 * The subscription picture. Three numbers, because "how many tenants
		 * are on a paid plan" is the question and "how many rows are in the
		 * subscriptions table" is not an answer to it.
		 */
		$data['subs_total']    = (int) $this->db->count_all('subscriptions');
		$data['subs_expired']  = (int) $this->db->where('status', 'expired')->count_all_results('subscriptions');
		$this->db->reset_query();

		// Expiring inside 30 days, still active, and not already expired. The
		// lower bound is there because a row can carry status 'active' with a
		// date in the past — nothing in the schema prevents it, and an
		// "expiring soon" list that includes last March is not a warning.
		$data['subs_expiring'] = (int) $this->db
			->where('status', 'active')
			->where('end_date <=', date('Y-m-d', strtotime('+30 days')))
			->where('end_date >=', date('Y-m-d'))
			->count_all_results('subscriptions');
		$this->db->reset_query();

		/*
		 * Support. Open and pending are OR-ed inside a group: written as two
		 * separate where() calls they are AND-ed and the count is zero, which
		 * on a panel with tickets in it reads exactly like "no tickets".
		 */
		$this->db->group_start();
		$this->db->where('status', 'open');
		$this->db->or_where('status', 'pending');
		$this->db->group_end();
		$data['tickets_open'] = (int) $this->db->count_all_results('support_tickets');
		$this->db->reset_query();

		$data['tickets_urgent'] = (int) $this->db
			->where('priority', 'urgent')
			->group_start()
			->where('status', 'open')
			->or_where('status', 'pending')
			->group_end()
			->count_all_results('support_tickets');
		$this->db->reset_query();

		/*
		 * The two lists. Both are small and bounded — LIMIT 6, ordered by the
		 * thing the operator is looking for — because the overview's job is to
		 * point at the work, not to reproduce the module pages.
		 */
		$data['expiring_list'] = $this->db
			->select('s.ins_titute_p_id, s.end_date, p.package_key, p.name_bn, t.ins_name')
			->from('subscriptions s')
			->join('packages p', 'p.package_id = s.package_id')
			->join('ins_titute t', 't.ins_titute_p_idds = s.ins_titute_p_id')
			->where('s.status', 'active')
			->where('s.end_date <=', date('Y-m-d', strtotime('+30 days')))
			->where('s.end_date >=', date('Y-m-d'))
			->order_by('s.end_date', 'ASC')
			->limit(6)
			->get()
			->result();

		$data['urgent_tickets'] = $this->db
			->select('ticket_id, ticket_no, subject, priority, status, created_at, user_name')
			->from('support_tickets')
			->where('priority', 'urgent')
			->group_start()
			->where('status', 'open')
			->or_where('status', 'pending')
			->group_end()
			->order_by('created_at', 'DESC')
			->limit(6)
			->get()
			->result();

		/*
		 * Recent activity. Six rows, newest first. Read-only and unaudited:
		 * opening the overview is not an action worth a row, or the log would
		 * be mostly "someone looked at the log".
		 */
		$data['recent_audit'] = $this->db
			->select('audit_id, actor_name, action, entity, entity_id, summary, created_at')
			->order_by('audit_id', 'DESC')
			->limit(6)
			->get('audit_logs')
			->result();

		// The status banner on the overview reports whether isolation is
		// structurally intact — a claim the panel can actually make, unlike
		// "the system is healthy".
		$data['is_development'] = (bool) $this->config->item('is_development');

		$data['sa_title'] = 'ওভারভিউ';
		$data['sa_desc']  = 'সেন্ট্রাল প্ল্যাটফর্মের সার্বিক অবস্থা এক নজরে।';

		$this->template->sadmin('super_admin/overview', $data);
	}

	/**
	 * Instant 1-Click Switch: Jump into any tenant's dashboard
	 *
	 * Note this sets a SESSION key, which is step 5 of the resolver and not
	 * how the switch actually works any more: the redirect below carries
	 * ?tenant=N, and Tenant_resolver reads that as the development override
	 * at step 1, ahead of the host. The session write is left in place
	 * because Dashboard and its queries still read union_code, and
	 * removing it would change what data the operator sees on the far side.
	 *
	 * A gated panel means ?tenant=N is the operator's own privilege, and
	 * the override is a dev-host-only branch. On a production host the
	 * override is refused and the switch lands on the fallback tenant
	 * instead — see the resolver docblock. That is a real limitation of
	 * using a development override for a production feature, and it is why
	 * this is listed as such rather than presented as impersonation.
	 */
	public function switch_tenant($tenant_id)
	{
		$tenant_id = (int)$tenant_id;
		$tenant = $this->db->where('ins_titute_p_idds', $tenant_id)->get('ins_titute')->row();

		if (!$tenant) {
			show_error('ইউনিয়ন পরিষদ পাওয়া যায়নি।', 404);
		}

		// Set tenant in session for Ion_auth if needed
		$this->session->set_userdata('union_code', $tenant_id);

		audit_log('toggle', 'tenant', [
			'entity_id' => (string) $tenant_id,
			'tenant_id' => $tenant_id,
			'summary'   => 'সুপার অ্যাডমিন ' . $tenant->ins_name . '-এর ড্যাশবোর্ডে প্রবেশ করেছেন',
		]);

		redirect('dashboard?tenant=' . $tenant_id, 'refresh');
	}
}
