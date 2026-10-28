<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Sadmin_subscriptions — packages, subscriptions and payments
 * =====================================================================
 *
 * WHAT EXISTED BEFORE THIS
 * Nothing. There was no packages table, no subscriptions table and no
 * record of money received. A tenant's plan lived in a column named
 * theme_ss ("theme") that the public signup wrote and nothing read
 * back, and its expiry lived in ins_titute.expire_times, a varchar(180)
 * holding a unix timestamp on one tenant, a YYYY-MM-DD date on another
 * and nothing on three more. Prices were hardcoded in
 * saas/pages/plans.php as ৳25,000 / ৳40,000 / "custom". The plan a
 * sadmin picked while onboarding was read off the request and dropped
 * on the floor.
 *
 * WHAT THIS MODULE DOES AND DOES NOT ENFORCE
 * It RECORDS what a tenant is on and when it runs out. It does not
 * enforce expiry: no controller reads subscriptions.status to decide
 * whether to serve a parishad's site, and none should until someone
 * decides what a lapsed tenant's visitors actually see — a redirect to
 * a renewal page, a read-only portal, or nothing at all are three
 * different products, and picking one is not a migration. The
 * subscriptions module shows status; it is not an access-control list.
 * Do not read "expired" here as "switched off".
 *
 * PAYMENTS ARE MANUAL, AND THE COLUMN NAMES SAY SO
 * payment_method is a label somebody typed, not a gateway. There is no
 * bKash/Stripe integration in this codebase and none was added: the
 * panel records what a person confirms they received, which is a
 * bookkeeping task, and a payment gateway is a product decision with
 * webhook verification, refund semantics and reconciliation behind it.
 * The `reference` column is free text for a TrxID, a cheque number or
 * a note. Anyone reading subscription_payments as a ledger of verified
 * transactions is reading it wrong.
 */
class Sadmin_subscriptions extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->library(['ion_auth', 'session']);

		// The gate. Halts on failure.
		require_sadmin();
	}

	/**
	 * The module: every tenant's plan, expiry and payment history.
	 *
	 * One page rather than a list plus per-tenant pages, because the
	 * operator's actual question is comparative — "who is on what, who
	 * owes what, who has lapsed" — and answering it means seeing all
	 * tenants at once. The payments for one tenant are expandable
	 * inline rather than behind a link, so the answer and the evidence
	 * for it are on the same screen.
	 */
	public function index()
	{
		$filter = (string) ($this->input->get('status') ?: '');

		// A tenant with no subscriptions row is still shown. The LEFT JOIN
		// is the point: hiding them would make "unassigned" invisible, and
		// an unassigned tenant is exactly what the operator is looking for.
		$this->db->select('t.ins_titute_p_idds, t.ins_name, t.sub_domains, t.domain_names,
		                   t.is_active, t.package_key, t.expire_times,
		                   s.subscription_id, s.billing_cycle, s.start_date, s.end_date,
		                   s.status, s.is_auto_renew, s.note,
		                   p.package_key AS sub_package_key, p.name_bn, p.price_yearly')
			->from('ins_titute t')
			->join('subscriptions s', 's.ins_titute_p_id = t.ins_titute_p_idds', 'left')
			->join('packages p', 'p.package_id = s.package_id', 'left')
			->where('t.ins_titute_p_idds >', 0)
			->order_by('t.ins_titute_p_idds', 'ASC');

		/*
		 * The filter is applied in SQL, not by hiding rows in the view.
		 * Two reasons. A view-side filter cannot answer "how many are
		 * expiring" for the counts in the summary — the summary is built
		 * from the same query, so it would be counting the filtered set
		 * while the operator believes it is counting the platform. And
		 * `expiring` is not a status, it is a date comparison, which is
		 * a query rather than a string match.
		 */
		if ($filter === 'expiring') {
			$this->db->where('s.status', 'active')
				->where('s.end_date <=', date('Y-m-d', strtotime('+30 days')))
				->where('s.end_date >=', date('Y-m-d'));
		} elseif ($filter === 'lapsed') {
			// Two ways to be lapsed, and the panel shows both as one thing
			// because from the outside they are: the status says expired, or
			// the status says active and the date says otherwise. The second
			// is possible because nothing in the schema stops it, and
			// filtering on status alone would quietly drop those tenants.
			$this->db->group_start();
			$this->db->where('s.status', 'expired');
			$this->db->or_where('s.status', 'cancelled');
			$this->db->or_group_start();
			$this->db->where('s.status', 'active');
			$this->db->where('s.end_date <', date('Y-m-d'));
			$this->db->or_where('s.subscription_id IS NULL', NULL, FALSE);
			$this->db->group_end();
			$this->db->group_end();
		} elseif (in_array($filter, ['active', 'expired', 'cancelled', 'pending'], TRUE)) {
			$this->db->where('s.status', $filter);
		} elseif ($filter === 'unassigned') {
			$this->db->where('s.subscription_id IS NULL', NULL, FALSE);
		}

		$rows = $this->db->get()->result();

		/*
		 * Totals. Counted over the WHOLE table, never over $rows.
		 *
		 * This is the mistake worth naming: computing the summary from the
		 * filtered result means the "মোট" on a page that is showing only
		 * lapsed tenants reports the number of lapsed tenants, and the
		 * operator reads it as the size of the platform. Four separate
		 * counts for the price of not reusing a variable.
		 */
		$data['subs_total'] = (int) $this->db->count_all('subscriptions');
		$this->db->reset_query();

		$this->db->where('status', 'active');
		$data['subs_active'] = (int) $this->db->count_all_results('subscriptions');
		$this->db->reset_query();

		$this->db->group_start();
		$this->db->where('status', 'expired');
		$this->db->or_where('status', 'cancelled');
		$this->db->group_end();
		$data['subs_lapsed'] = (int) $this->db->count_all_results('subscriptions');
		$this->db->reset_query();

		// Tenants with no row at all — not the same as lapsed.
		$data['subs_unassigned'] = (int) $this->db
			->where('ins_titute_p_idds >', 0)
			->where('ins_titute_p_idds NOT IN (SELECT ins_titute_p_id FROM subscriptions)', NULL, FALSE)
			->count_all_results('ins_titute');
		$this->db->reset_query();

		/*
		 * Money. Two numbers, and the distinction is the whole reason they
		 * are not one: `collected` is what somebody typed into the payments
		 * table, `outstanding` is what the current terms imply should have
		 * been paid. Neither is a bank figure, because there is nothing here
		 * that talks to a bank.
		 */
		$collected_row = $this->db->select('COALESCE(SUM(amount), 0) AS total, COUNT(*) AS cnt')
			->where('paid_at IS NOT NULL', NULL, FALSE)
			->get('subscription_payments')
			->row();

		/*
		 * Outstanding is computed from terms, not from a stored balance, and
		 * only for tenants whose paid period is the one being asked about.
		 *
		 * The simplification is deliberate and visible: a tenant who paid
		 * two years up front has their full current term counted as
		 * outstanding. The alternative is a payments-vs-terms reconciliation
		 * with a ledger table, a payment-allocation rule and credit
		 * handling — that is a real piece of work and pretending to have
		 * done it here would be worse than a number that is obviously
		 * "price of the current term".
		 */
		$outstanding_row = $this->db->select('
				COALESCE(SUM(p.price_yearly), 0) AS total
			')
			->from('subscriptions s')
			->join('packages p', 'p.package_id = s.package_id')
			->where('s.status', 'active')
			->where('s.end_date >=', date('Y-m-d'))
			->get()
			->row();

		$data['collected']        = (int) $collected_row->total;
		$data['collected_count']  = (int) $collected_row->cnt;
		$data['outstanding']      = (int) $outstanding_row->total;

		/*
		 * The payment history for every tenant on the page, in one query.
		 * One query rather than N because N is a loop doing a query per
		 * row, which is the shape that makes a page with 200 tenants take
		 * four seconds and look like the database is slow.
		 */
		$subs = array_column($rows, 'subscription_id');
		$subs = array_filter($subs);
		$data['payments_by_sub'] = [];
		if ($subs) {
			foreach ($this->db->where_in('subscription_id', $subs)
				->order_by('paid_at', 'DESC NULLS LAST')
				->order_by('payment_id', 'DESC')
				->get('subscription_payments')
				->result() as $pay) {
				$data['payments_by_sub'][$pay->subscription_id][] = $pay;
			}
		}

		$data['rows']         = $rows;
		$data['filter']       = $filter;
		$data['packages']     = $this->db->order_by('sort_order', 'ASC')->get('packages')->result();
		$data['payment_methods'] = [
			'cash'   => 'নগদ',
			'bkash'  => 'বিকাশ',
			'nagad'  => 'নগদ (Nagad)',
			'bank'   => 'ব্যাংক ট্রান্সফার',
			'cheque' => 'চেক',
			'other'  => 'অন্যান্য',
		];
		$data['sa_title'] = 'সাবস্ক্রিপশন';
		$data['sa_desc']  = 'প্যাকেজ, মেয়াদ ও পেমেন্ট রেকর্ড ব্যবস্থাপনা।';

		$this->template->sadmin('super_admin/subscriptions', $data);
	}

	/**
	 * API: create or edit a package.
	 *
	 * The three seeded packages came from the prices hardcoded in
	 * saas/pages/plans.php, so that page and this table start out in
	 * agreement. They will drift the moment a price is edited here and
	 * not there — see docs/SADMIN-PANEL.md, which records that
	 * plans.php still hardcodes its own numbers and is the next thing
	 * to fix.
	 *
	 * Deactivating a package is allowed and does not touch existing
	 * subscribers: subscriptions.package_id is a foreign key and the
	 * row is read by name on the list, so a tenant on a retired plan
	 * keeps its plan and shows it. Deleting the package row instead
	 * would either fail the FK or cascade, and neither is a thing to
	 * do to a tenant that is mid-contract.
	 */
	public function api_save_package()
	{
		$input = sadmin_api_input();

		$name_bn = trim((string) ($input['name_bn'] ?? ''));
		if ($name_bn === '') {
			sadmin_fail('প্যাকেজের নাম দিন।');
		}

		$price_monthly = max(0, (int) ($input['price_monthly'] ?? 0));
		$price_yearly  = max(0, (int) ($input['price_yearly'] ?? 0));

		/*
		 * The empty-string test, not a null test. The form sends "" for an
		 * empty limit field, and `NULL` here means unlimited. A zero would
		 * be stored as a real zero and then read as "this package allows
		 * no holdings at all" — the opposite of what the operator meant.
		 */
		$max_holdings = (trim((string) ($input['max_holdings'] ?? '')) === '') ? NULL : max(0, (int) $input['max_holdings']);
		$max_users    = (trim((string) ($input['max_users'] ?? '')) === '') ? NULL : max(0, (int) $input['max_users']);

		$package_id = (int) ($input['package_id'] ?? 0);
		$is_active  = !empty($input['is_active']) ? TRUE : (array_key_exists('is_active', $input) ? FALSE : TRUE);

		sadmin_write(function () use ($package_id, $name_bn, $input, $price_monthly, $price_yearly, $max_holdings, $max_users, $is_active) {
			$data = [
				'name_bn'        => $name_bn,
				'name_en'        => trim((string) ($input['name_en'] ?? '')) ?: NULL,
				'price_monthly'  => $price_monthly,
				'price_yearly'   => $price_yearly,
				'max_holdings'   => $max_holdings,
				'max_users'      => $max_users,
				'features'       => trim((string) ($input['features'] ?? '')) ?: NULL,
				'is_active'      => $is_active,
			];

			if ($package_id > 0) {
				$before = $this->db->where('package_id', $package_id)->get('packages')->row();
				if (!$before) {
					sadmin_fail('প্যাকেজ পাওয়া যায়নি।', 404);
				}
				$data['sort_order'] = (int) ($input['sort_order'] ?? $before->sort_order);
				$this->db->where('package_id', $package_id)->update('packages', $data);

				$changed = [];
				foreach ($data as $k => $v) {
					if ((string) $before->$k !== (string) ($v ?? '')) {
						$changed[$k] = $v;
					}
				}

				return [
					'success' => true,
					'message' => 'প্যাকেজ আপডেট হয়েছে: ' . $name_bn,
					'audit'   => [
						'entity_id' => (string) $package_id,
						'tenant_id' => NULL,
						'summary'   => 'প্যাকেজ আপডেট: ' . $name_bn
							. ($changed ? ' (' . implode(', ', array_keys($changed)) . ')' : ''),
						'before'    => (array) $before,
						'after'     => $data,
					],
				];
			}

			/*
			 * package_key is the identifier the public side already knows:
			 * Front::create_tenant() and saas/pages/plans.php both use the
			 * three literal keys, and the pricing page builds its links from
			 * them. So a new package gets a key derived from its name, and
			 * if that is taken the operator is told to pick a different name
			 * rather than being handed a suffixed key that no public page
			 * will ever link to.
			 */
			$key = strtolower(trim((string) ($input['package_key'] ?? '')));
			if ($key === '') {
				$key = strtolower(preg_replace('/[^a-z0-9]+/', '_', $input['name_en'] ?: $name_bn));
				$key = trim($key, '_');
			}
			if ($key === '') {
				sadmin_fail('প্যাকেজ কী (key) বের করা গেল না। ইংরেজি নাম দিন।');
			}
			if ($this->db->where('package_key', $key)->get('packages')->num_rows() > 0) {
				sadmin_fail('"' . $key . '" নামে প্যাকেজ আগে থেকেই আছে। অনুন্নত নাম বা অন্য ইংরেজি নাম দিন।');
			}

			$data['package_key'] = $key;
			$data['sort_order']  = (int) ($input['sort_order'] ?? 99);
			$this->db->insert('packages', $data);

			return [
				'success' => true,
				'message' => 'নতুন প্যাকেজ তৈরি হয়েছে: ' . $name_bn,
				'audit'   => [
					'entity_id' => (string) $this->db->insert_id(),
					'tenant_id' => NULL,
					'summary'   => 'নতুন প্যাকেজ: ' . $name_bn . ' (' . $key . ')',
					'after'     => $data,
				],
			];
		}, 'update', 'package');
	}

	/**
	 * API: delete an unused package.
	 *
	 * A package can only be deleted if no subscriptions reference it.
	 * If existing subscriptions reference it, it must be deactivated instead
	 * to preserve billing and historical records.
	 */
	public function api_delete_package()
	{
		$input = sadmin_api_input();
		$package_id = (int) ($input['package_id'] ?? 0);

		if ($package_id <= 0) {
			sadmin_fail('প্যাকেজ আইডি প্রদান করা হয়নি।', 400);
		}

		$pkg = $this->db->where('package_id', $package_id)->get('packages')->row();
		if (!$pkg) {
			sadmin_fail('প্যাকেজটি খুঁজে পাওয়া যায়নি।', 404);
		}

		// Check if any subscriptions reference this package
		$sub_count = $this->db->where('package_id', $package_id)->count_all_results('subscriptions');
		if ($sub_count > 0) {
			sadmin_fail('এই প্যাকেজে ' . bn_number($sub_count) . 'টি সাবস্ক্রিপশন সক্রিয়/যুক্ত আছে। তাই এটি মুছে ফেলা সম্ভব নয়। চাইলে প্যাকেজটি "নিষ্ক্রিয়" (Deactivate) করে রাখতে পারেন।', 400);
		}

		sadmin_write(function () use ($package_id, $pkg) {
			$this->db->where('package_id', $package_id)->delete('packages');

			return [
				'success' => true,
				'message' => 'প্যাকেজটি সফলভাবে মুছে ফেলা হয়েছে: ' . $pkg->name_bn,
				'audit'   => [
					'entity_id' => (string) $package_id,
					'tenant_id' => NULL,
					'summary'   => 'প্যাকেজ মুছে ফেলা হয়েছে: ' . $pkg->name_bn . ' (' . $pkg->package_key . ')',
					'before'    => (array) $pkg,
				],
			];
		}, 'delete', 'package');
	}

	/**
	 * API: assign a plan to a tenant.
	 *
	 * The panel's own plan change, as a first-class operation with its own
	 * audit row and its own validation. The same effect is reachable from
	 * the tenant directory's edit modal, which now delegates here — see
	 * Sadmin_tenants::write_subscription_plan(), which is the same logic,
	 * and this method is the shell around it. Two entry points is a choice:
	 * the directory is where a sadmin is when they notice a tenant is on
	 * the wrong plan, and the subscriptions module is where they go to
	 * look at plans. Neither should be the only way.
	 */
	public function api_assign()
	{
		$input = sadmin_api_input();

		$tenant_id = (int) ($input['tenant_id'] ?? 0);
		$sub_id    = (int) ($input['subscription_id'] ?? 0);

		if ($sub_id <= 0) {
			sadmin_fail('সাবস্ক্রিপশন নির্বাচন করা হয়নি।');
		}

		$sub = $this->db->where('subscription_id', $sub_id)->get('subscriptions')->row();
		if (!$sub) {
			sadmin_fail('সাবস্ক্রিপশন পাওয়া যায়নি।', 404);
		}
		$tenant_id = (int) $sub->ins_titute_p_id;

		$package_id = (int) ($input['package_id'] ?? 0);
		$plan = $this->db->where('package_id', $package_id)->get('packages')->row();
		if (!$plan) {
			sadmin_fail('প্যাকেজ পাওয়া যায়নি।', 404);
		}

		$old_plan = $this->db->where('package_id', $sub->package_id)->get('packages')->row('package_key');

		sadmin_write(function () use ($sub, $plan, $tenant_id, $old_plan) {
			$this->db->trans_start();

			$this->db->where('subscription_id', $sub->subscription_id)
			         ->update('subscriptions', ['package_id' => $plan->package_id]);

			/*
			 * ins_titute.package_key and theme_ss are BOTH written, and the
			 * reason is that they are read by two different things.
			 * theme_ss is the legacy home of the plan key and is what the
			 * public side has always read; package_key is the column added
			 * on 2026-09-30 and is what the tenant directory reads. Writing
			 * one and not the other is how the plan ends up reading
			 * differently in the panel and on the parishad's own site.
			 */
			$this->db->where('ins_titute_p_idds', $tenant_id)
			         ->update('ins_titute', [
					'package_key' => $plan->package_key,
					'theme_ss'    => $plan->package_key,
				]);

			if ($this->db->trans_status() === FALSE) {
				$this->db->trans_rollback();
				sadmin_fail('প্যাকেজ পরিবর্তন করা যায়নি।', 500);
			}
			$this->db->trans_commit();

			$name = $this->db->where('ins_titute_p_idds', $tenant_id)->get('ins_titute')->row('ins_name');

			return [
				'success' => true,
				'message' => ($name ?: 'টেন্যান্ট') . ' — প্যাকেজ এখন ' . $plan->name_bn,
				'audit'   => [
					'entity_id' => (string) $sub->subscription_id,
					'tenant_id' => $tenant_id,
					'summary'   => ($name ?: 'টেন্যান্ট') . ' — প্যাকেজ ' . ($old_plan ?: '—') . ' → ' . $plan->package_key,
					'before'    => ['package_id' => $sub->package_id, 'package_key' => $old_plan],
					'after'     => ['package_id' => $plan->package_id, 'package_key' => $plan->package_key],
				],
			];
		}, 'update', 'subscription');
	}

	/**
	 * API: renew — push end_date forward by the subscription's cycle.
	 *
	 * "Forward", not "set". The operator clicks renew; they do not type a
	 * date. A renewal that set end_date to today would be a no-op, and one
	 * that set it to a hand-typed date is a different operation that
	 * already exists (api_set_dates) and has its own confirmation. Two
	 * operations that both look like "renew" and mean different things is
	 * how a tenant gets a second year they had not paid for.
	 *
	 * From today when already lapsed, from the current end date when not.
	 * A lapsed tenant renewing does not get credit for the months they
	 * were down, and a current tenant does not lose the days remaining.
	 */
	public function api_renew()
	{
		$input = sadmin_api_input();
		$sub_id = (int) ($input['subscription_id'] ?? 0);

		$sub = $this->db->select('s.*, p.package_key, p.name_bn, p.price_yearly, p.price_monthly, t.ins_name')
			->from('subscriptions s')
			->join('packages p', 'p.package_id = s.package_id')
			->join('ins_titute t', 't.ins_titute_p_idds = s.ins_titute_p_id')
			->where('s.subscription_id', $sub_id)
			->get()
			->row();

		if (!$sub) {
			sadmin_fail('সাবস্ক্রিপশন পাওয়া যায়নি।', 404);
		}

		$cycle = ($sub->billing_cycle === 'monthly') ? '+1 month' : '+1 year';
		$base  = ($sub->end_date >= date('Y-m-d')) ? $sub->end_date : date('Y-m-d');
		$new_end = date('Y-m-d', strtotime($base . ' ' . $cycle));

		$bill = ((int) $sub->price_yearly > 0 && $sub->billing_cycle === 'yearly')
			? (int) $sub->price_yearly
			: (int) $sub->price_monthly;

		sadmin_write(function () use ($sub, $new_end, $cycle) {
			$this->db->trans_start();

			$this->db->where('subscription_id', $sub->subscription_id)->update('subscriptions', [
				'end_date'  => $new_end,
				// Renewing a lapsed subscription brings it back. Leaving
				// status='expired' would make the panel show a paid tenant as
				// expired, and nothing would correct it until the next
				// manual status edit.
				'status'    => 'active',
			]);

			// The legacy mirror. Same column, same reason as everywhere else:
			// it is varchar(180) and it is not the source of truth, but
			// anything still reading it should read the right date.
			$this->db->where('ins_titute_p_idds', $sub->ins_titute_p_id)
			         ->update('ins_titute', ['expire_times' => $new_end]);

			if ($this->db->trans_status() === FALSE) {
				$this->db->trans_rollback();
				sadmin_fail('মেয়াদ নবায়ন করা যায়নি।', 500);
			}
			$this->db->trans_commit();

			return [
				'success' => true,
				'message' => $sub->ins_name . ' — মেয়াদ এখন ' . $new_end . ' পর্যন্ত বাড়ানো হয়েছে',
				'end_date' => $new_end,
				'audit'   => [
					'entity_id' => (string) $sub->subscription_id,
					'tenant_id' => (int) $sub->ins_titute_p_id,
					'summary'   => $sub->ins_name . ' — মেয়াদ নবায়ন: ' . $sub->end_date . ' → ' . $new_end,
					'before'    => ['end_date' => $sub->end_date, 'status' => $sub->status],
					'after'     => ['end_date' => $new_end, 'status' => 'active'],
				],
			];
		}, 'update', 'subscription');
	}

	/**
	 * API: set the dates by hand, and the status.
	 *
	 * Separate from renew() on purpose. This is the "the contract actually
	 * says this" path — a signed agreement with an odd start or an
	 * end-of-year date, or a cancellation with a reason. Renew is the
	 * "another year, same terms" path.
	 *
	 * The two statuses that mean "not being served" are refused here
	 * rather than accepted and quietly ignored: a sadmin who clicks
	 * "expired" and sees a success message is entitled to believe
	 * something happened, and nothing does.
	 */
	public function api_set_dates()
	{
		$input = sadmin_api_input();
		$sub_id = (int) ($input['subscription_id'] ?? 0);

		$sub = $this->db->select('s.*, t.ins_name')
			->from('subscriptions s')
			->join('ins_titute t', 't.ins_titute_p_idds = s.ins_titute_p_id')
			->where('s.subscription_id', $sub_id)
			->get()
			->row();

		if (!$sub) {
			sadmin_fail('সাবস্ক্রিপশন পাওয়া যায়নি।', 404);
		}

		$new_start = trim((string) ($input['start_date'] ?? $sub->start_date));
		$new_end   = trim((string) ($input['end_date']   ?? $sub->end_date));
		$new_status= trim((string) ($input['status']     ?? $sub->status));

		foreach (['start_date' => $new_start, 'end_date' => $new_end] as $lbl => $v) {
			if (strtotime($v) === false) {
				sadmin_fail('তারিখ বোঝা যায়নি। YYYY-MM-DD ফরম্যাটে দিন।');
			}
		}
		$new_start = date('Y-m-d', strtotime($new_start));
		$new_end   = date('Y-m-d', strtotime($new_end));

		if ($new_end < $new_start) {
			sadmin_fail('মেয়াদ শেষের তারিখ শুরুর তারিখের আগে হতে পারে না।');
		}

		if (!in_array($new_status, ['active', 'expired', 'cancelled', 'pending'], TRUE)) {
			sadmin_fail('অবৈধ অবস্থা।');
		}

		sadmin_write(function () use ($sub, $new_start, $new_end, $new_status) {
			$this->db->trans_start();
			$this->db->where('subscription_id', $sub->subscription_id)->update('subscriptions', [
				'start_date' => $new_start,
				'end_date'   => $new_end,
				'status'     => $new_status,
				'note'       => trim((string) ($this->input->post('note') ?? '')) ?: $sub->note,
			]);
			$this->db->where('ins_titute_p_idds', $sub->ins_titute_p_id)
			         ->update('ins_titute', ['expire_times' => $new_end]);

			if ($this->db->trans_status() === FALSE) {
				$this->db->trans_rollback();
				sadmin_fail('তারিখ সংরক্ষণ করা যায়নি।', 500);
			}
			$this->db->trans_commit();

			return [
				'success' => true,
				'message' => $sub->ins_name . ' — সাবস্ক্রিপশনের তথ্য আপডেট হয়েছে',
				'audit'   => [
					'entity_id' => (string) $sub->subscription_id,
					'tenant_id' => (int) $sub->ins_titute_p_id,
					'summary'   => $sub->ins_name . ' — সাবস্ক্রিপশন সম্পাদনা (অবস্থা: ' . $sub->status . ' → ' . $new_status . ')',
					'before'    => ['start_date' => $sub->start_date, 'end_date' => $sub->end_date, 'status' => $sub->status],
					'after'     => ['start_date' => $new_start, 'end_date' => $new_end, 'status' => $new_status],
				],
			];
		}, 'update', 'subscription');
	}

	/**
	 * API: cancel a subscription.
	 *
	 * Deletes the subscriptions row rather than setting status='cancelled'.
	 *
	 * That is a real choice and it is worth being explicit about. package_id
	 * is NOT NULL, so a cancelled row still has to name a plan, and a
	 * cancelled row with a plan looks exactly like an active one in any
	 * query that forgets to check status — including the panel's own "what
	 * is this tenant on" cell. Removing the row leaves a single honest
	 * state: this tenant has no subscription. ins_titute.package_key is
	 * cleared at the same time so the tenant does not keep advertising a
	 * plan it is not paying for.
	 *
	 * The payment history is NOT deleted. subscription_payments cascades
	 * with the subscription, so a naive DELETE here would erase the record
	 * of money received. That is the single most important line in this
	 * method, and it is why payments are copied out to a tenant-level
	 * history before the row goes.
	 */
	public function api_cancel()
	{
		$input = sadmin_api_input();
		$sub_id = (int) ($input['subscription_id'] ?? 0);

		$sub = $this->db->select('s.*, t.ins_name')
			->from('subscriptions s')
			->join('ins_titute t', 't.ins_titute_p_idds = s.ins_titute_p_id')
			->where('s.subscription_id', $sub_id)
			->get()
			->row();

		if (!$sub) {
			sadmin_fail('সাবস্ক্রিপশন পাওয়া যায়নি।', 404);
		}

		$reason = trim((string) ($input['reason'] ?? ''));
		$pays   = $this->db->where('subscription_id', $sub_id)->count_all_results('subscription_payments');
		$this->db->reset_query();

		sadmin_write(function () use ($sub, $reason, $pays) {
			$this->db->trans_start();

			/*
			 * A cancelled subscription with payments against it is a
			 * bookkeeping problem, not a reason to refuse: the money came in
			 * and the service is stopping, and both facts need to survive.
			 * The payments are copied onto a tenant-level record first,
			 * because subscription_payments has ON DELETE CASCADE and
			 * deleting the subscription would take them with it.
			 *
			 * This is the only place in the panel that writes to
			 * tenant_payment_history, and it exists for exactly this.
			 */
			if ($pays > 0) {
				foreach ($this->db->select('*')
					->where('subscription_id', $sub_id)
					->get('subscription_payments')
					->result() as $p) {
					$this->db->insert('tenant_payment_history', [
						'ins_titute_p_id' => (int) $sub->ins_titute_p_id,
						'payment_id'      => $p->payment_id,
						'invoice_no'      => $p->invoice_no,
						'amount'          => $p->amount,
						'payment_method'  => $p->payment_method,
						'reference'       => $p->reference,
						'paid_at'         => $p->paid_at,
						'note'            => $p->note,
						'archived_at'     => date('Y-m-d H:i:s'),
					]);
				}
			}

			$this->db->where('subscription_id', $sub_id)->delete('subscriptions');

			$this->db->where('ins_titute_p_idds', $sub->ins_titute_p_id)
			         ->update('ins_titute', [
					'package_key'  => NULL,
					'theme_ss'     => NULL,
					'expire_times' => NULL,
				]);

			if ($this->db->trans_status() === FALSE) {
				$this->db->trans_rollback();
				sadmin_fail('সাবস্ক্রিপশন বাতিল করা যায়নি। কোনো পরিবর্তন হয়নি।', 500);
			}
			$this->db->trans_commit();

			return [
				'success' => true,
				'message' => $sub->ins_name . ' — সাবস্ক্রিপশন বাতিল করা হয়েছে'
					. ($pays > 0 ? '। ' . $pays . 'টি পেমেন্ট রেকর্ড আর্কাইভে রাখা হয়েছে।' : ''),
				'audit'   => [
					'entity_id' => (string) $sub_id,
					'tenant_id' => (int) $sub->ins_titute_p_id,
					'summary'   => $sub->ins_name . ' — সাবস্ক্রিপশন বাতিল'
						. ($reason !== '' ? ' (' . $reason . ')' : '')
						. ($pays > 0 ? ' · ' . $pays . 'টি পেমেন্ট আর্কাইভ' : ''),
					'before'    => [
						'package_id' => $sub->package_id,
						'end_date'   => $sub->end_date,
						'status'     => $sub->status,
						'payments'   => $pays,
					],
					'after'     => ['subscription' => 'deleted'],
				],
			];
		}, 'delete', 'subscription');
	}

	/**
	 * API: record a payment.
	 *
	 * invoice_no is generated here, never supplied. A caller-chosen
	 * invoice number is either a duplicate (UNIQUE, and the error would be
	 * an opaque 500) or a gap in the sequence, and an invoice sequence
	 * with gaps is an invoice sequence nobody can reconcile.
	 *
	 * The format is TKT-<year>-<six digits>, zero padded and continuing
	 * from the highest existing number for the year — NOT from
	 * COUNT(*)+1, which collides the moment a payment is ever deleted.
	 */
	public function api_add_payment()
	{
		$input = sadmin_api_input();
		$sub_id = (int) ($input['subscription_id'] ?? 0);

		$sub = $this->db->select('s.subscription_id, s.ins_titute_p_id, s.end_date, s.start_date, s.status, t.ins_name')
			->from('subscriptions s')
			->join('ins_titute t', 't.ins_titute_p_idds = s.ins_titute_p_id')
			->where('s.subscription_id', $sub_id)
			->get()
			->row();

		if (!$sub) {
			sadmin_fail('সাবস্ক্রিপশন পাওয়া যায়নি।', 404);
		}

		$amount = (int) ($input['amount'] ?? 0);
		if ($amount < 0) {
			sadmin_fail('পেমেন্টের পরিমাণ শূন্যের চেয়ে কম হতে পারে না।');
		}
		if ($amount === 0) {
			// Not defaulted to the plan price. A payment of 0 is a real
			// statement — "nothing was collected" — and silently recording
			// the list price instead would make the books wrong in the
			// direction that looks like revenue.
			sadmin_fail('পেমেন্টের পরিমাণ লিখুন।');
		}

		$method = trim((string) ($input['payment_method'] ?? 'cash'));
		if (!in_array($method, ['cash', 'bkash', 'nagad', 'bank', 'cheque', 'other'], TRUE)) {
			sadmin_fail('অবৈধ পেমেন্ট পদ্ধতি।');
		}

		$paid_at = trim((string) ($input['paid_at'] ?? ''));
		if ($paid_at === '') {
			$paid_at = date('Y-m-d');
		}
		if (strtotime($paid_at) === false) {
			sadmin_fail('পেমেন্টের তারিখ বোঝা যায়নি। YYYY-MM-DD ফরম্যাটে দিন।');
		}
		$paid_at = date('Y-m-d', strtotime($paid_at));

		sadmin_write(function () use ($sub, $sub_id, $amount, $method, $paid_at, $input) {
			$invoice_no = $this->next_invoice_no($paid_at);

			$this->db->insert('subscription_payments', [
				'subscription_id' => $sub_id,
				'invoice_no'      => $invoice_no,
				'amount'          => $amount,
				'payment_method'  => $method,
				'reference'       => trim((string) ($input['reference'] ?? '')) ?: NULL,
				'paid_at'         => $paid_at,
				'note'            => trim((string) ($input['note'] ?? '')) ?: NULL,
				'created_by'      => $this->session->userdata('user_id'),
			]);

			/*
			 * A payment does NOT extend the subscription.
			 *
			 * That is deliberate and it is the most likely thing to get
			 * "fixed" wrongly. Recording money received and granting the
			 * period it pays for are two decisions: somebody confirms the
			 * money at the counter, and then somebody decides the terms.
			 * auto-linking them means a mistyped amount is also a
			 * mistyped expiry, discovered at renewal time a year later.
			 * api_renew() is the deliberate version of the second step.
			 */
			return [
				'success'    => true,
				'invoice_no' => $invoice_no,
				'message'    => 'পেমেন্ট রেকর্ড হয়েছে — ইনভয়েস ' . $invoice_no . ', ' . taka($amount),
				'audit'      => [
					'entity_id' => $invoice_no,
					'tenant_id' => (int) $sub->ins_titute_p_id,
					'summary'   => $sub->ins_name . ' — পেমেন্ট ' . taka($amount) . ' (' . $method . '), ইনভয়েস ' . $invoice_no,
					'after'     => [
						'invoice_no'     => $invoice_no,
						'amount'         => $amount,
						'payment_method' => $method,
						'paid_at'        => $paid_at,
					],
				],
			];
		}, 'create', 'payment');
	}

	/**
	 * API: delete a payment record.
	 *
	 * This is a correction, not a refund. A refund is money going out and
	 * is a separate record with a negative-signed convention this schema
	 * does not have — subscription_payments_amount_chk is CHECK (amount >=
	 * 0), deliberately, so a refund cannot be typed as a negative payment
	 * and silently subtracted from the collected total.
	 *
	 * So if a payment was recorded against the wrong parishad, the answer
	 * is to delete it and re-record it correctly, and the audit row records
	 * what was removed. If the money genuinely came in and is being given
	 * back, that needs a decision about how refunds are represented, which
	 * is a product question this module does not answer on its own.
	 */
	public function api_delete_payment()
	{
		$input = sadmin_api_input();
		$pay_id = (int) ($input['payment_id'] ?? 0);

		$pay = $this->db->select('pay.*, t.ins_name')
			->from('subscription_payments pay')
			->join('subscriptions s', 's.subscription_id = pay.subscription_id')
			->join('ins_titute t', 't.ins_titute_p_idds = s.ins_titute_p_id')
			->where('pay.payment_id', $pay_id)
			->get()
			->row();

		if (!$pay) {
			sadmin_fail('পেমেন্ট রেকর্ড পাওয়া যায়নি।', 404);
		}

		sadmin_write(function () use ($pay, $pay_id) {
			$this->db->where('payment_id', $pay_id)->delete('subscription_payments');

			$kept = $this->db->where('subscription_id', $pay->subscription_id)
				->where('paid_at IS NOT NULL', NULL, FALSE)
				->get('subscription_payments')
				->row();

			/*
			 * A deleted payment re-introduces the row into
			 * tenant_payment_history only if it was archived when the
			 * subscription was cancelled. If the subscription is live there
			 * is nothing to undo: the cascade has not fired, and the
			 * archive was never written.
			 */
			$archived = $this->db->where('payment_id', $pay_id)
				->get('tenant_payment_history')
				->num_rows();
			if ($archived > 0) {
				$this->db->where('payment_id', $pay_id)->delete('tenant_payment_history');
			}

			return [
				'success' => true,
				'message' => 'পেমেন্ট রেকর্ড মুছে ফেলা হয়েছে: ' . $pay->invoice_no,
				'audit'   => [
					'entity_id' => (string) $pay_id,
					'tenant_id' => (int) $pay->ins_titute_p_id,
					'summary'   => $pay->ins_name . ' — পেমেন্ট রেকর্ড মোছা হয়েছে: ' . $pay->invoice_no . ' (' . taka($pay->amount) . ')',
					'before'    => [
						'invoice_no'     => $pay->invoice_no,
						'amount'         => (int) $pay->amount,
						'payment_method' => $pay->payment_method,
						'paid_at'        => $pay->paid_at,
					],
				],
			];
		}, 'delete', 'payment');
	}

	/**
	 * The next invoice number for a year.
	 *
	 * Derived from MAX(), not from a count and not from a bare sequence.
	 * MAX() + 1 survives a deletion; COUNT() + 1 re-issues a number that
	 * has already been issued, which the UNIQUE constraint then refuses
	 * with an opaque database error on the second delete of the year.
	 *
	 * The scan is on a UNIQUE-indexed varchar with a fixed prefix, so it
	 * is an index range scan, and invoice_payments are a handful of rows
	 * per year at this platform's size.
	 */
	private function next_invoice_no($paid_at)
	{
		$year  = date('Y', strtotime($paid_at));
		$prefix = 'TKT-' . $year . '-';

		$last = $this->db->select('invoice_no')
			->where('invoice_no LIKE', $prefix . '%')
			->order_by('invoice_no', 'DESC')
			->limit(1)
			->get('subscription_payments')
			->row();

		$seq = 1;
		if ($last) {
			$seq = ((int) substr($last->invoice_no, strlen($prefix))) + 1;
		}

		return $prefix . str_pad((string) $seq, 6, '0', STR_PAD_LEFT);
	}
}
