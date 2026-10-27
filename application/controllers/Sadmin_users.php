<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Sadmin_users — the platform's account directory, READ-ONLY
 * =====================================================================
 *
 * WHY THIS IS HALF A MODULE
 * Everything interesting this page could do — create an account, change a
 * role, reset a password, deactivate somebody, delete a user — is a write
 * to `users` or `users_groups`, and AGENTS.md requires explicit approval
 * before touching either table. That approval has not been given, so the
 * write half is not written. The routes for all five api_* endpoints are
 * declared in config/routes.php and every one of them 404s; see the
 * comment there and docs/SADMIN-PANEL.md section 6.
 *
 * The read half needs no approval, so this is it, and it is worth having on
 * its own: it is where the platform's own account state is legible, and it
 * already found a live credential sitting in the database (below).
 *
 * NO PASSWORD IS EVER RENDERED
 * Not the bcrypt hash, and not `password_viewss_s`. The page reports
 * whether that column is set and refuses to say what is in it. A panel
 * that can display a password is a panel that will display it in a
 * screenshot, and "the super admin can already reset it" makes showing it
 * worthless anyway.
 *
 * `active` IS AN INTEGER, AND "INACTIVE" IS EXACTLY ZERO
 * The column is `integer` with `CHECK (active >= 0)`, not a boolean, and
 * Ion_auth_model::login() tests `if ($user->active == 0)`. So:
 *
 *   0            login is refused ("login_unsuccessful_not_active")
 *   1            active — the only value in use
 *   2, 3, 5 …    NOT refused. `== 0` is false, so it logs in.
 *
 * Every live row is 1. A value above 1 would be meaningless data rather
 * than a locked account, and this page says so instead of colouring it
 * red — a row that reads "বন্ধ" for a number that does not lock the
 * account is a page that lies about the one thing it exists to report.
 *
 * `created_on` AND `last_login` ARE UNIX SECONDS, AND THE SENTINELS ARE
 * NOT ZERO
 * Both are `integer` holding a unix timestamp. The live values include
 * `created_on = 1` and `last_login = -1`, which are not real times: they
 * are what a create script writes when it has no value. Left alone,
 * to_timestamp(1) renders "1970-01-01", which is a confident, wrong
 * statement about when the account was made. Anything <= 1 is therefore
 * shown as "—" and the row is flagged, not dated.
 *
 * A USER WITH NO GROUP CAN STILL LOG IN
 * Ion_auth_model::login() never touches users_groups. What a groupless
 * account lacks is a role: every in_group() is false, so the sidebar
 * renders empty and no admin route accepts it. It is not a locked
 * account, and this page must not imply it is — it is an account with no
 * permissions, which is a different problem with a different fix.
 *
 * `ins_pr_id_s IS NULL` IS NOT MISSING DATA
 * It is how `enforce_tenant_scope()` recognises the platform's own
 * cross-tenant role, and the panel's own admin account is one. Labelling
 * it "—" would hide the exact distinction that guard is built on, so it is
 * labelled প্ল্যাটফর্ম.
 *
 * `u_type` HAS ALREADY DRIFTED FROM `ins_pr_id_s`
 * User 6 carries u_type 'bolai' with ins_pr_id_s = 1, and tenant 1 is
 * ধুম, whose slug is 'dhum-union'. The numeric id is the authority
 * (AGENTS.md: two tenant ids, do not confuse them); the slug is a label
 * that has drifted and is shown as one, with the mismatch counted.
 */
class Sadmin_users extends CI_Controller
{
	/**
	 * The per-page row count. 50, like the audit log.
	 */
	const PER_PAGE = 50;

	public function __construct()
	{
		parent::__construct();
		$this->load->library(['ion_auth', 'session']);

		// The gate. Halts on failure — see sadmin_helper.php.
		require_sadmin();
	}

	/* =====================================================================
	 * Pages
	 * ================================================================== */

	/**
	 * The account directory.
	 *
	 * Filters are applied in SQL, for the same reason every other module
	 * does it: the cards above the table are counted over the whole table,
	 * and a filter applied in the view would make them report the size of
	 * the filtered set while the operator reads them as the size of the
	 * fleet.
	 */
	public function index()
	{
		$f = $this->filters();

		/*
		 * count_all_results() with NO argument, and that is not laziness.
		 *
		 * CI3 builds "SELECT COUNT(*) FROM <argument>" and the argument
		 * is a table name to append, not the one already in the query.
		 * filtered_query() has already called ->from('users u') with a
		 * join, so passing 'users u' makes the FROM read
		 * `"users" "u", "users" "u" LEFT JOIN ...` and Postgres answers
		 * 42712 "table name u specified more than once".
		 *
		 * With no argument CI3 falls back to qb_from[0], which is the
		 * aliased FROM the builder already has — so the count runs
		 * against exactly the query the rows come from. Getting this
		 * wrong on a JOIN is a 500, not a wrong number.
		 */
		$page  = max(1, (int) ($this->input->get('page') ?: 1));
		$total = (int) $this->filtered_query($f)->count_all_results();

		/*
		 * Clamp the page, for the same reason the audit list does: a URL
		 * can point past the end of the set, and "কোনো ব্যবহারকারী নেই"
		 * above a total of 9 is the one conclusion nobody should draw
		 * from a page about the platform's accounts.
		 */
		$pages = (int) ceil($total / self::PER_PAGE);
		if ($pages > 0 && $page > $pages) {
			$page = $pages;
		}

		$rows = $this->filtered_query($f)
			->order_by('u.id', 'ASC')
			->limit(self::PER_PAGE, ($page - 1) * self::PER_PAGE)
			->get()
			->result();

		/*
		 * Groups for the page's users in ONE query, not a join with a
		 * GROUP BY.
		 *
		 * The join shape was rejected deliberately. `users` and `groups`
		 * are one-to-many through users_groups, so selecting g.name
		 * means grouping by u.id AND g.name, and a user holding three
		 * groups comes back as three rows — which would break the row
		 * count, the pagination and the "has no group" detection all at
		 * once. Grouping on a selected non-aggregate column instead is
		 * the 42803 Postgres raises, which audit_helper.php already
		 * documents having hit.
		 *
		 * Two queries with the ids in between is the idiom the
		 * subscriptions and support modules already use for the same
		 * reason.
		 */
		$data['groups_of'] = [];
		$ids = array_column($rows, 'id');
		if ($ids) {
			foreach ($this->db->select('ug.user_id, g.id AS gid, g.name, g.description')
				->from('users_groups ug')
				->join('groups g', 'g.id = ug.group_id', 'inner')
				->where_in('ug.user_id', $ids)
				->order_by('g.id', 'ASC')
				->get()
				->result() as $r) {

				$data['groups_of'][(int) $r->user_id][] = [
					'id'    => (int) $r->gid,
					'name'  => $r->name,
					'label' => $r->description,
				];
			}
		}

		$data['rows']       = $rows;
		$data['filters']    = $f;
		$data['has_filter'] = $f['q'] !== ''
			|| $f['group'] !== ''
			|| $f['tenant_id'] !== ''
			|| $f['active'] !== '';
		$data['page']       = $page;
		$data['total']      = $total;
		$data['per_page']   = self::PER_PAGE;
		$data['pages']      = $pages;

		// Totals, always over the whole table.
		$data['u_total']     = (int) $this->db->count_all('users');
		$this->db->reset_query();

		// active = 0 is the only value that refuses a login; see the
		// class docblock. The count is of that value, NOT of "not 1".
		$data['u_active']    = (int) $this->db
			->where('active', 0)
			->count_all_results('users');
		$this->db->reset_query();

		$data['u_weird_active'] = (int) $this->db
			->where('active >', 1)
			->count_all_results('users');
		$this->db->reset_query();

		// No group at all. Logs in fine, has no permissions — see the
		// class docblock. Counted with NOT EXISTS rather than a LEFT JOIN
		// + IS NULL, which reads as "no rows came back" rather than
		// "this user is not in the table", and cannot accidentally
		// count the users_groups rows.
		$data['u_groupless'] = (int) $this->db
			->where('NOT EXISTS (SELECT 1 FROM users_groups ug WHERE ug.user_id = users.id)', NULL, FALSE)
			->count_all_results('users');
		$this->db->reset_query();

		// ins_pr_id_s IS NULL: the platform's own role. Raw fragment for
		// the same reason the support module uses one.
		$data['u_platform'] = (int) $this->db
			->where('ins_pr_id_s IS NULL', NULL, FALSE)
			->count_all_results('users');
		$this->db->reset_query();

		/*
		 * THE FINDING THIS PAGE EXISTS TO SURFACE.
		 *
		 * password_viewss_s held a recoverable password. AGENTS.md
		 * records that the login backdoor that read it is gone and that
		 * nothing reads the column now, and gives the one-line fix. Four
		 * of the six live accounts still have a value in it, which means
		 * a live credential sits in the database that no code path
		 * needs and a database dump would hand over.
		 *
		 * It is COUNTED, never printed. See the class docblock.
		 */
		$data['u_legacy_pw'] = (int) $this->db
			->where('password_viewss_s IS NOT NULL', NULL, FALSE)
			->where('password_viewss_s <>', '')
			->count_all_results('users');
		$this->db->reset_query();

		/*
		 * u_type against the tenant's own slug, counted as THREE states
		 * and not as one number, because "differs" is not one thing.
		 *
		 * sub_domains is the slug Tenant_creator writes at signup. Two of
		 * the five live tenants predate the multi-tenant conversion and
		 * have no slug at all, so for their users the question is
		 * unanswerable rather than answered wrongly — and a single
		 * "mismatch" count would report them as mismatches, which is a
		 * finding about a user that is really a finding about a tenant.
		 *
		 *   match       both set and equal          — nothing to say
		 *   mismatch    both set and different       — u_type is stale
		 *   unknown     the tenant has no slug      — cannot be checked
		 *
		 * A platform user (ins_pr_id_s IS NULL) is in none of the three:
		 * it has no tenant to have a slug.
		 */
		$slug_rows = $this->db
			->select('u.u_type, t.sub_domains')
			->from('users u')
			->join('ins_titute t', 't.ins_titute_p_idds = u.ins_pr_id_s', 'inner')
			->where('u.u_type IS NOT NULL', NULL, FALSE)
			->where('u.u_type <>', '')
			->get()
			->result();

		$data['u_slug_match']    = 0;
		$data['u_slug_mismatch'] = 0;
		$data['u_slug_unknown']  = 0;

		foreach ($slug_rows as $sr) {
			if ($sr->sub_domains === NULL || $sr->sub_domains === '') {
				$data['u_slug_unknown']++;
			} elseif ($sr->sub_domains === $sr->u_type) {
				$data['u_slug_match']++;
			} else {
				$data['u_slug_mismatch']++;
			}
		}

		// Filter dropdown sources.
		$data['all_groups'] = $this->db->order_by('id', 'ASC')->get('groups')->result();
		$data['all_tenants'] = $this->db
			->select('ins_titute_p_idds, ins_name')
			->order_by('ins_titute_p_idds', 'ASC')
			->get('ins_titute')
			->result();

		// The list of group names in use, so the filter cannot offer a
		// value no user has.
		$data['group_names'] = array_column($this->db
			->select('g.name')
			->distinct()
			->from('users_groups ug')
			->join('groups g', 'g.id = ug.group_id', 'inner')
			->get()
			->result(), 'name');

		$data['sa_title'] = 'ইউজার ম্যানেজার';
		$data['sa_desc']  = 'প্ল্যাটফর্মের সব অ্যাকাউন্ট — শুধু দেখার জন্য, কোনো পরিবর্তন এখানে নেই';

		$this->template->sadmin('super_admin/users', $data);
	}

	/* =====================================================================
	 * Internals
	 * ================================================================== */

	/**
	 * The current filter set, validated.
	 *
	 * @return array
	 */
	private function filters()
	{
		/*
		 * NO `?:` ON ANYTHING THAT CAN LEGALLY BE THE STRING "0".
		 *
		 * `?:` is a falsy test, and in PHP the string "0" is falsy. So
		 * `$this->input->get('active') ?: ''` turns ?active=0 into '',
		 * which drops the filter — and the symptom is not an error, it
		 * is a page showing every account when the operator asked for
		 * the ones that are switched off, which is the exact question
		 * they opened the page to answer.
		 *
		 * isset() + an explicit NULL test is the replacement. The other
		 * three are safe on their own and are left readable.
		 */
		$raw_q      = $this->input->get('q');
		$raw_group  = $this->input->get('group');
		$raw_tenant = $this->input->get('tenant_id');
		$raw_active = $this->input->get('active');

		$q     = trim((string) ($raw_q      !== NULL ? $raw_q      : ''));
		$group = trim((string) ($raw_group  !== NULL ? $raw_group  : ''));
		$tenant = (string) ($raw_tenant !== NULL ? $raw_tenant : '');
		$active = (string) ($raw_active !== NULL ? $raw_active : '');

		/*
		 * tenant_id carries a UI sentinel, 'platform', that is not a
		 * column value — it means ins_pr_id_s IS NULL. Written as '' so
		 * the query layer sees "no filter" and a blank select means
		 * "everyone", the same trick the audit list uses.
		 */
		if ($tenant !== 'platform') {
			$tenant = ($tenant !== '' && ctype_digit($tenant)) ? (int) $tenant : '';
		}

		// 1 and 0 are the only two that exist. Anything else is a
		// mistyped URL and is dropped rather than coerced, because
		// coercing it would silently show the wrong accounts.
		if ($active !== '1' && $active !== '0') {
			$active = '';
		}

		// The group filter is a name, checked against a fixed list of the
		// seven groups in this table. A LIKE against the column would
		// also match 'admin' inside 'upadmin' the day such a group is
		// created, and this is a cross-tenant list.
		$known = array_column($this->db->get('groups')->result(), 'name');
		if ($group !== '' && !in_array($group, $known, TRUE)) {
			$group = '';
		}

		return [
			'q'         => $q,
			'group'     => $group,
			'tenant_id' => $tenant,
			'active'    => $active,
		];
	}

	/**
	 * The filtered user query, ready for ->get() or ->count_all_results().
	 *
	 * The tenant name is joined in rather than looked up per row, so the
	 * table does not issue a query per line. sub_domains is selected too
	 * because it is what u_type is supposed to equal — the mismatch count
	 * and the row's own mismatch badge read the same value, so they
	 * cannot disagree.
	 *
	 * @return CI_DB_query_builder
	 */
	private function filtered_query(array $f)
	{
		$this->db->select('u.*, t.ins_name, t.sub_domains')
			->from('users u')
			->join('ins_titute t', 't.ins_titute_p_idds = u.ins_pr_id_s', 'left');

		if ($f['q'] !== '') {
			/*
			 * group_start() is not optional. CI3's where() ANDs, so six
			 * bare like() calls produce "email LIKE a AND username LIKE
			 * a AND …", which matches nothing — the exact silent-nothing
			 * failure AGENTS.md records having already made twice.
			 *
			 * THE SEARCH IS UNICODE-NORMALISED, AND IT HAS TO BE.
			 *
			 * Bengali য় exists as two different code point sequences that
			 * render as the identical letter: the precomposed U+09DF, and
			 * the pair U+09AF U+09BC. Which one arrives depends on the
			 * input method, and this database contains BOTH:
			 *
			 *   users.username          2 rows NFC, 0 NFD
			 *   ins_titute.ins_name     4 rows NFC, 1 NFD
			 *   ins_titute.ins_title_ss 3 rows NFC, 1 NFD
			 *   support_tickets.message 0 NFC,      1 NFD
			 *
			 * So `চেয়ারম্যান` typed in a browser is U+09AF U+09BC, and the
			 * stored `চেয়ারম্যান` is U+09DF — the same eleven visible
			 * characters, and LIKE finds nothing. Verified on this page:
			 * ?q=বাঘমারা returns the account, ?q=চেয়ারম্যান returns zero.
			 * Postgres compares bytes under a deterministic collation, so
			 * normalisation is not optional and UNICODE_NORMALIZE is not a
			 * server setting here.
			 *
			 * normalize(x, NFC) is Postgres 13+ and is present. Applying it
			 * to BOTH sides of the comparison fixes the search without
			 * touching a single row of data, which is the only version of
			 * this fix that can be shipped without a migration: a data
			 * migration would fix today's rows and not tomorrow's, because
			 * the next keystroke can be either form again. See
			 * docs/SADMIN-PANEL.md section 7.7 for the write-side half,
			 * which is the owner's decision and not this page's.
			 *
			 * The cost is that normalize() is not indexable, so this is a
			 * sequential scan. On `users` that is nothing.
			 */
			$this->db->group_start();
			foreach (['email', 'username', 'first_name', 'last_name', 'phone', 'u_type'] as $col) {
				$this->db->or_where(
					'normalize(u.' . $col . ", NFC) LIKE normalize("
					. $this->db->escape('%' . $this->db->escape_like_str($f['q']) . '%')
					. ", NFC) ESCAPE '!'",
					NULL,
					FALSE
				);
			}
			$this->db->group_end();
		}

		if ($f['group'] !== '') {
			/*
			 * EXISTS rather than a join. A join would multiply the rows
			 * — a user in two groups appears twice, which corrupts both
			 * the page count and the pagination — and would need a
			 * DISTINCT to fix, which is how a pagination bug hides.
			 *
			 * The value is inlined through $this->db->escape(), NOT
			 * passed as the second argument and NOT a `?` placeholder.
			 * CodeIgniter 3 has no placeholder syntax: where($key, $value)
			 * concatenates the key and the escaped value, so a fragment
			 * ending in `= ?` comes out as
			 *     ... g."name" = "?" 'admin'
			 * which is 42601 syntax error — a 500 on a filter, not a
			 * filter that returns nothing. AGENTS.md's rule is the same
			 * one from the other side: the operator goes in the KEY, and
			 * a value in a raw fragment is escaped where it is written.
			 *
			 * escape() is the escaping CI3 itself would have applied,
			 * so this is no less safe than the normal path — and the
			 * value is already whitelisted against the `groups` table
			 * in filters() before it reaches here, so it cannot carry a
			 * quote even if escape() were removed.
			 */
			$this->db->where(
				'EXISTS (SELECT 1 FROM users_groups ug'
				. ' JOIN groups g ON g.id = ug.group_id'
				. ' WHERE ug.user_id = u.id AND g.name = '
				. $this->db->escape($f['group']) . ')',
				NULL, FALSE
			);
		}

		if ($f['tenant_id'] === 'platform') {
			$this->db->where('u.ins_pr_id_s IS NULL', NULL, FALSE);
		} elseif ($f['tenant_id'] !== '') {
			$this->db->where('u.ins_pr_id_s', (int) $f['tenant_id']);
		}

		if ($f['active'] === '1') {
			$this->db->where('u.active >', 0);
		} elseif ($f['active'] === '0') {
			$this->db->where('u.active', 0);
		}

		return $this->db;
	}

	/**
	 * Reset a user's password directly from super admin.
	 *
	 * Explicitly approved by administrator to support account recovery
	 * when SMTP or mail services are unavailable or failing.
	 *
	 * Writes hashed password via bcrypt, clears legacy password_viewss_s,
	 * and records in audit trail.
	 */
	public function api_reset_password()
	{
		header('Content-Type: application/json; charset=utf-8');

		$user_id = (int) $this->input->post('user_id');
		$new_password = trim((string) $this->input->post('new_password'));

		if ($user_id <= 0) {
			echo json_encode(['success' => false, 'message' => 'অবৈধ ইউজার আইডি।']);
			return;
		}

		if (strlen($new_password) < 6) {
			echo json_encode(['success' => false, 'message' => 'পাসওয়ার্ড কমপক্ষে ৬ অক্ষরের হতে হবে।']);
			return;
		}

		$user = $this->db->where('id', $user_id)->get('users')->row();
		if (!$user) {
			echo json_encode(['success' => false, 'message' => 'ইউজার অ্যাকাউন্টটি পাওয়া যায়নি।']);
			return;
		}

		$this->load->model('ion_auth_model');
		$hashed = $this->ion_auth_model->hash_password($new_password);

		$update_data = [
			'password'          => $hashed,
			'password_viewss_s' => NULL, // Always ensure plaintext copy is wiped
		];

		$this->db->where('id', $user_id)->update('users', $update_data);

		if (function_exists('audit_log')) {
			audit_log('reset_password', 'user', [
				'entity_id' => (string) $user_id,
				'tenant_id' => $user->ins_pr_id_s ? (int) $user->ins_pr_id_s : NULL,
				'summary'   => 'পাসওয়ার্ড পরিবর্তন: ' . $user->email . ' (' . ($user->username ?: 'ইউজার') . ')',
				'after'     => [
					'user_id'   => $user_id,
					'email'     => $user->email,
					'by_sadmin' => true,
				],
			]);
		}

		echo json_encode([
			'success' => true,
			'message' => 'পাসওয়ার্ড সফলভাবে পরিবর্তন করা হয়েছে।',
			'email'   => $user->email,
		]);
	}
}
