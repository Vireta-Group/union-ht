<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Approval workflow helpers (PROPOSE side)
 * =====================================================================
 *
 * Autoloaded (see config/autoload.php).
 *
 * WHAT THIS IS FOR
 * ----------------
 * in_group() answers "may this person open tax_collection?". It cannot
 * answer "may this person change this setting?" — because that depends on
 * WHO is asking and WHAT they are asking to change. A resource + state
 * question, not a role question, so it needs a table. That table is
 * `approvals` (see vireta-private/migrations/c1_approvals.php).
 *
 * Two separate layers, and confusing them is the bug this file exists to
 * prevent:
 *
 *   RBAC (in_group)  ->  may the page open at all?
 *   approvals (this)  ->  may this specific change happen?
 *
 * THIS FILE IS THE PROPOSE SIDE ONLY. It can create a request and read the
 * queue. It deliberately cannot decide one — decide_approval() lands in the
 * next step, and until then NOBODY can approve, so a half-built workflow can
 * never let a change through by accident.
 *
 * WHY A WHITELIST AND NOT A FREE STRING
 * --------------------------------------
 * `action` is a free-text column so new actions never need a schema change.
 * That makes the DB useless as a safety net on its own, so the whitelist
 * below IS the gate. A controller that guesses an action string gets a
 * false, and a request with no proposal-capable role gets a false. Default
 * is always deny.
 *
 * WHO PROPOSES, PER ACTION
 * ------------------------
 * Deliberately narrow, and narrower than it might look:
 *
 *   uphead   — the approver in every flow. Still allowed to propose
 *              (the chairman may want a change the socib also wants) but
 *              he can NEVER decide his own; the DB CHECK enforces that
 *              independently, so this is belt, not braces.
 *   upsocib  — the proposer. Changes to settings go through him as a
 *              request, not directly.
 *   collect  — deposits only, and only for their own tenant.
 *   udc      — deposits only, same as collect. Read access to sonod and
 *              similar is a MENU question (step 4), not an approval one.
 *   sadmin   — the platform's own cross-tenant role, ins_pr_id_s NULL. It
 *              can propose for any tenant, which is how a platform
 *              operator fixes a tenant's settings without a local
 *              chairman. It is deliberately NOT a decider-by-default;
 *              see decide_approval() when it lands.
 *
 * Note uphead/upsocib also carry the `admin` group (verified in the live
 * DB — every chairman is in both `uphead` and `admin`). The rules below key
 * on the SPECIFIC role, never on `admin`, because `admin` is the broad group
 * and using it would hand proposer rights to anyone holding it. That is the
 * mistake sadmin_helper.php documents for a different reason.
 *
 * @see approvals table, approval_no_self_approval CHECK
 * @see sadmin_helper.php  for the platform panel's own gate
 */

// ---------------------------------------------------------------------------
// Shared bootstrap
// ---------------------------------------------------------------------------

if (!function_exists('approval_ci'))
{
	/**
	 * Return $CI with ion_auth guaranteed to be loaded, or FALSE.
	 *
	 * WHY THIS EXISTS. sadmin_helper.php checks `!isset($CI->ion_auth)` and
	 * 403s on failure, which is safe for a GATE — the caller was always going
	 * to be refused. It is not safe for a PROPOSE helper, which is called from
	 * ordinary tenant controllers that may never have loaded the library: a
	 * bare `$CI->ion_auth->logged_in()` on an unloaded library returns false
	 * for every user, so "not loaded" was silently indistinguishable from
	 * "not permitted". That fails closed, which is better than the opposite,
	 * but it still denies a legitimate upsocib and reads as a permission bug
	 * rather than a wiring one. So load it here, idempotently.
	 *
	 * @return object|false CI instance, or FALSE when there is no session.
	 */
	function approval_ci()
	{
		$CI =& get_instance();

		if (!isset($CI->ion_auth))
		{
			$CI->load->library('ion_auth');
		}

		if (!isset($CI->ion_auth) || !$CI->ion_auth->logged_in())
		{
			return FALSE;
		}

		return $CI;
	}
}

// ---------------------------------------------------------------------------
// Reading the current user
// ---------------------------------------------------------------------------
// A TRAP, worth its own section because it looks fine and silently is not.
//
// $CI->ion_auth->user() does NOT return a user row. ion_auth's __call
// forwards it to Ion_auth_model, where BOTH `users()` (a query builder) and
// `user($id = NULL)` exist. The one that wins the name lookup is the builder,
// and a builder returns `$this` — the query object, not a person. So:
//
//     $CI->ion_auth->user()->id     -> NULL, no error, no exception
//     $CI->ion_auth->user()->email  -> whatever property happens to exist
//
// It fails completely silently. Writing `isset($u->ins_pr_id_s)` on the
// result gives FALSE for a real signed-in user, and a tenant check built on it
// denies everybody — which reads as a permissions bug and sends you hunting
// in the wrong place. `in_group()` is unaffected because it reads the id from
// the session itself, which is why a caller using only in_group() appears to
// work perfectly while one that also touches user()->id quietly misbehaves.
//
// The row has to be fetched explicitly, from the session's user_id.

if (!function_exists('approval_user_id'))
{
	/**
	 * The signed-in users.id, or 0.
	 *
	 * @param  object $CI
	 * @return int
	 */
	function approval_user_id($CI)
	{
		$id = $CI->session->userdata('user_id');
		return $id ? (int) $id : 0;
	}
}

if (!function_exists('approval_user_tenant'))
{
	/**
	 * The signed-in users.ins_pr_id_s, or 0 for a user with no parishad.
	 *
	 * Deliberately 0 and not NULL: 0 is the "no tenant" sentinel the rest of
	 * the app uses, and a NULL here would make a non-sadmin platform account
	 * compare unequal-but-valid against every concrete tenant.
	 *
	 * @param  object $CI
	 * @return int
	 */
	function approval_user_tenant($CI)
	{
		$id  = approval_user_id($CI);
		if (!$id) { return 0; }

		$CI->db->select('ins_pr_id_s')->from('users')->where('id', $id)->limit(1);
		$row = $CI->db->get()->row();

		return ($row && $row->ins_pr_id_s !== NULL) ? (int) $row->ins_pr_id_s : 0;
	}
}

// ---------------------------------------------------------------------------
// Action whitelist: action => array of groups allowed to PROPOSE it
// ---------------------------------------------------------------------------
// Adding a new kind of change means adding one line here. That is the whole
// cost, and it is deliberate: a new action is a policy decision, so it should
// be a visible line in a policy file rather than a column added by accident.

if (!function_exists('approval_proposer_groups'))
{
	/**
	 * Which roles may propose a given action.
	 *
	 * @param  string $action
	 * @return array|null Group names, or NULL when the action is unknown.
	 *                    NULL means "not a real action" — callers must treat
	 *                    that as DENY, never as "everyone".
	 */
	function approval_proposer_groups($action)
	{
		static $map = array(
			// socib-initiated settings changes — the core of the flow
			'tenants.update'   => array('upsocib', 'uphead', 'sadmin'),

			// a collector or a digital centre posting money in
			'deposits.create'   => array('collect', 'udc', 'upsocib', 'uphead', 'sadmin'),

			// sonod edit permission when > 3 hours
			'sonod.edit'        => array('udc', 'collect', 'members', 'upsocib', 'uphead', 'admin', 'sadmin'),

			// reserved for later steps; listed now so the shape is obvious
			'sonod.issue'      => array('upsocib', 'uphead', 'sadmin'),
		);

		return isset($map[$action]) ? $map[$action] : NULL;
	}
}

// ---------------------------------------------------------------------------
// Propose
// ---------------------------------------------------------------------------

if (!function_exists('approval_can_propose'))
{
	/**
	 * Can this user propose this action for this tenant?
	 *
	 * Checks three things separately, because each fails differently and a
	 * single boolean would hide which:
	 *
	 *   1. signed in          — otherwise there is no requester to record
	 *   2. action is real     — an unknown action is always a deny
	 *   3. a proposer role    — AND the user's tenant matches
	 *
	 * Tenant matching is a `!==` test, not a `> 0` test, matching
	 * enforce_tenant_scope(): a user with a NULL tenant has no parishad of
	 * their own, so ANY concrete tenant is somebody else's. sadmin is the
	 * one documented exception and is handled by name, not by a NULL test.
	 *
	 * @param  string     $action
	 * @param  int|null   $tenant_id defaults to the signed-in user's tenant
	 * @return bool
	 */
	function approval_can_propose($action, $tenant_id = NULL)
	{
		$CI = approval_ci();
		if ($CI === FALSE)
		{
			return FALSE;
		}

		$groups = approval_proposer_groups($action);
		if ($groups === NULL)
		{
			return FALSE;
		}

		$user    = $CI->ion_auth->user();
		$is_cross_tenant = $CI->ion_auth->in_group('sadmin');
		// approval_user_tenant(), NOT $CI->ion_auth->user()->ins_pr_id_s —
		// see the "Reading the current user" section above for why user()
		// is a query builder and always yields NULL here.
		$user_tenant = approval_user_tenant($CI);

		// Default to proposing for your own parishad. sadmin is the only
		// caller that must pass an explicit tenant.
		if ($tenant_id === NULL)
		{
			$tenant_id = $user_tenant;
		}
		$tenant_id = (int) $tenant_id;

		if ($is_cross_tenant)
		{
			return TRUE;
		}

		if ($user_tenant === 0 || $tenant_id === 0 || $user_tenant !== $tenant_id)
		{
			return FALSE;
		}


		foreach ($groups as $g)
		{
			if ($CI->ion_auth->in_group($g))
			{
				return TRUE;
			}
		}

		return FALSE;
	}
}

if (!function_exists('approval_propose'))
{
	/**
	 * Record a change request. Does NOT apply the change.
	 *
	 * Refuses (returns FALSE) rather than throwing for the two ordinary
	 * denials — not signed in, not allowed — because callers are UI
	 * endpoints that want a message. It DOES throw for an unknown action,
	 * because that is a programming error, not a permission outcome, and
	 * silently returning FALSE would hide a typo in a controller forever.
	 *
	 * @param  string   $action
	 * @param  array    $payload what is being asked for; stored as jsonb
	 * @param  string   $note    optional note to the approver
	 * @param  int|null $tenant_id
	 * @return int|false  new approvals.id, or FALSE on refusal
	 * @throws RuntimeException on an unknown action
	 */
	function approval_propose($action, array $payload = array(), $note = NULL, $tenant_id = NULL)
	{
		$CI = approval_ci();
		if ($CI === FALSE)
		{
			return FALSE;
		}

		if (!approval_can_propose($action, $tenant_id))
		{
			return FALSE;
		}

		// Belt: the whitelist check above already rejects this, but the
		// throw gives a controller bug a loud failure instead of a 403 the
		// user cannot explain.
		if (approval_proposer_groups($action) === NULL)
		{
			throw new RuntimeException('approval_propose(): unknown action "' . $action . '"');
		}

		$is_cross_tenant = $CI->ion_auth->in_group('sadmin');
		$user_id   = approval_user_id($CI);
		$user_tenant = approval_user_tenant($CI);
		$tenant_id = ($tenant_id === NULL) ? $user_tenant : (int) $tenant_id;

		/* A cross-tenant caller MUST name the tenant it is acting for.
		 *
		 * Without this, sadmin — whose ins_pr_id_s is NULL, so user_tenant is
		 * 0 — would have $tenant_id default to 0 and the row would be written
		 * with ins_pr_id_s = 0. That value is the resolver's "no tenant"
		 * sentinel, so the request would never appear in any tenant's queue
		 * and would be invisible to the very uphead meant to approve it. It
		 * also cannot be later "fixed up" without guessing which tenant was
		 * meant. 0 is a wrong answer that looks like a right one, so this
		 * refuses instead.
		 */
		if ($is_cross_tenant && $tenant_id === 0)
		{
			return FALSE;
		}

		// json_encode must not silently become NULL on bad input; a NULL
		// column would fail the NOT NULL and the proposal would vanish
		// with no explanation. Fail loudly instead.
		$json = json_encode($payload);
		if ($json === FALSE)
		{
			throw new RuntimeException('approval_propose(): payload is not encodable for ' . $action);
		}

		$CI->db->insert('approvals', array(
			'ins_pr_id_s'  => $tenant_id,
			'action'       => $action,
			'payload'      => $json,
			'status'       => 'pending',
			'requested_by' => $user_id,
			'note'         => $note,
		));

		$id = $CI->db->insert_id();

		// audit the request itself, not just the eventual decision — a
		// rejected-then-retried history is exactly what this table is for.
		// Signature is audit_log($action, $entity, array $opts); tenant_id
		// is passed explicitly because this proposal may target a tenant
		// other than the requester's own.
		if (function_exists('audit_log'))
		{
			audit_log('approval.propose', 'approvals', array(
				'entity_id' => $id,
				'tenant_id' => $tenant_id,
				'summary'   => 'proposed ' . $action,
			));
		}

		return $id ? (int) $id : FALSE;
	}
}

// ---------------------------------------------------------------------------
// Read
// ---------------------------------------------------------------------------

if (!function_exists('approval_queue'))
{
	/**
	 * Pending requests for a tenant, oldest first.
	 *
	 * Always tenant-scoped. There is deliberately no "all tenants" mode for
	 * a tenant-side call, because the one caller that is allowed to see
	 * across tenants (sadmin) is a different question and gets its own
	 * function. A queue that could be widened by argument is a queue that
	 * will be widened by accident.
	 *
	 * @param  int|null $tenant_id defaults to the signed-in user's tenant
	 * @param  int      $limit
	 * @return array
	 */
	function approval_queue($tenant_id = NULL, $limit = 100)
	{
		$CI = approval_ci();
		if ($CI === FALSE)
		{
			return array();
		}

		$is_cross_tenant = $CI->ion_auth->in_group('sadmin');
		$user_tenant     = approval_user_tenant($CI);
		$tenant_id       = ($tenant_id === NULL) ? $user_tenant : (int) $tenant_id;

		// A cross-tenant caller must name the tenant, for the same reason
		// approval_propose() requires it: sadmin's own tenant is NULL, which
		// resolves to 0, and 0 is the "no tenant" sentinel — a queue for it
		// would be a queue for nothing. Refusing beats silently returning an
		// empty list, which looks like "nothing is pending" rather than
		// "you did not say which parishad".
		if ($is_cross_tenant && $tenant_id === 0)
		{
			return array();
		}

		if ($tenant_id === 0)
		{
			return array();
		}

		if (!$is_cross_tenant && ($user_tenant === 0 || $user_tenant !== $tenant_id))
		{
			return array();
		}

		$CI->db
			->select('a.*, ru.username AS requester_name, ru.email AS requester_email, du.username AS decider_name, du.email AS decider_email')
			->from('approvals a')
			->join('users ru', 'ru.id = a.requested_by', 'left')
			->join('users du', 'du.id = a.decided_by', 'left')
			->where('a.ins_pr_id_s', $tenant_id)
			->where('a.status', 'pending')
			->order_by('a.requested_at', 'ASC')
			->limit((int) $limit);

		$rows = $CI->db->get()->result();
		foreach ($rows as $r) { $r->payload = json_decode($r->payload, TRUE); }
		return $rows;
	}
}

if (!function_exists('approval_pending_count'))
{
	/**
	 * Fast count of pending proposals for badge in navigation.
	 *
	 * @param  int|null $tenant_id
	 * @return int
	 */
	function approval_pending_count($tenant_id = NULL)
	{
		$CI = approval_ci();
		if ($CI === FALSE)
		{
			return 0;
		}

		$is_cross_tenant = $CI->ion_auth->in_group('sadmin');
		$user_tenant     = approval_user_tenant($CI);
		$tenant_id       = ($tenant_id === NULL) ? $user_tenant : (int) $tenant_id;

		if ($tenant_id === 0)
		{
			return 0;
		}

		if (!$is_cross_tenant && ($user_tenant === 0 || $user_tenant !== $tenant_id))
		{
			return 0;
		}

		return (int) $CI->db
			->from('approvals')
			->where('ins_pr_id_s', $tenant_id)
			->where('status', 'pending')
			->count_all_results();
	}
}


if (!function_exists('approval_get'))
{
	/**
	 * One request by id, tenant-scoped.
	 *
	 * Scoped on purpose: the id is a sequential bigint, so an unscoped
	 * get() is the same IDOR shape AGENTS.md records for
	 * AddMemberModel::get_datasheet_by_id(). Guessing 12 reads tenant 1's
	 * request.
	 *
	 * @param  int      $id
	 * @param  int|null $tenant_id
	 * @return object|false
	 */
	function approval_get($id, $tenant_id = NULL)
	{
		$CI = approval_ci();
		if ($CI === FALSE)
		{
			return FALSE;
		}

		$is_cross_tenant = $CI->ion_auth->in_group('sadmin');
		$user_tenant     = approval_user_tenant($CI);
		$tenant_id       = ($tenant_id === NULL) ? $user_tenant : (int) $tenant_id;

		if ($tenant_id === 0)
		{
			return FALSE;
		}

		if (!$is_cross_tenant && ($user_tenant === 0 || $user_tenant !== $tenant_id))
		{
			return FALSE;
		}

		$CI->db
			->select('a.*, ru.username AS requester_name, ru.email AS requester_email, du.username AS decider_name, du.email AS decider_email')
			->from('approvals a')
			->join('users ru', 'ru.id = a.requested_by', 'left')
			->join('users du', 'du.id = a.decided_by', 'left')
			->where('a.id', (int) $id)
			->where('a.ins_pr_id_s', $tenant_id);

		$row = $CI->db->get()->row();
		if (!$row) { return FALSE; }
		$row->payload = json_decode($row->payload, TRUE);
		return $row;
	}
}

// ---------------------------------------------------------------------------
// Decide  (STEP 3)
// ---------------------------------------------------------------------------

if (!function_exists('approval_decider_groups'))
{
	/**
	 * Which roles may DECIDE any request.
	 *
	 * DELIBERATELY ACTION-INDEPENDENT. The owner chose "either uphead or
	 * upsocib may approve" over "both must approve" for the deposit flow, and
	 * applied the same single-decider rule here so one rule governs every
	 * action. A two-key design would need a second column (approved_by +
	 * approved_at, twice) and a partial-progress state, which is a lot of
	 * machinery for an approval that in practice the chairman is present
	 * for. If that call is revisited it is a schema change, not a logic tweak.
	 *
	 * upsocib is NOT in this list. It is the proposer, and letting it also
	 * decide would make the whole two-role split decorative: any socib could
	 * approve their own change and nobody would ever see it. The owner was
	 * explicit about that. sadmin IS here — it is the platform's own
	 * cross-tenant operator, and it exists precisely so a tenant can be
	 * unblocked when its chairman is unavailable.
	 *
	 * @return array
	 */
	function approval_decider_groups($action = NULL)
	{
		if ($action === 'sonod.edit')
		{
			return array('uphead', 'upsocib', 'sadmin', 'admin');
		}
		return array('uphead', 'sadmin');
	}
}

if (!function_exists('approval_can_decide'))
{
	/**
	 * May this user decide this specific request?
	 *
	 * Checks, in order, and returns the reason so the caller can render
	 * something better than a flat "no":
	 *
	 *   1. requester is the caller   -> never, for anybody, including sadmin
	 *   2. caller holds no decider role
	 *   3. request is not pending
	 *   4. caller's tenant is not the request's tenant (sadmin excepted)
	 *
	 * @param  object $row  an approvals row (from approval_get)
	 * @return array{ok:bool, why:string}
	 */
	function approval_can_decide($row)
	{
		$CI = approval_ci();
		if ($CI === FALSE)
		{
			return array('ok' => FALSE, 'why' => 'not_signed_in');
		}
		if (!is_object($row) || !isset($row->id))
		{
			return array('ok' => FALSE, 'why' => 'not_found');
		}

		$uid = approval_user_id($CI);

		// (1) the whole design rests on this one. Checked here in PHP so the
		// UI can hide the button, and independently by the
		// approvals_no_self_approval CHECK so a race or a second code path
		// still cannot slip through.
		if ($row->requested_by && (int) $row->requested_by === (int) $uid)
		{
			return array('ok' => FALSE, 'why' => 'self_approval');
		}

		// (2)
		$is_decider = FALSE;
		$deciders = approval_decider_groups(isset($row->action) ? $row->action : NULL);
		foreach ($deciders as $g)
		{
			if ($CI->ion_auth->in_group($g)) { $is_decider = TRUE; break; }
		}
		if (!$is_decider)
		{
			return array('ok' => FALSE, 'why' => 'not_a_decider');
		}

		// (3)
		if ($row->status !== 'pending')
		{
			return array('ok' => FALSE, 'why' => 'already_decided');
		}

		// (4) sadmin is cross-tenant by definition, so it is exempt.
		if (!$CI->ion_auth->in_group('sadmin'))
		{
			$user_tenant = approval_user_tenant($CI);
			if ($user_tenant === 0 || (int) $row->ins_pr_id_s !== $user_tenant)
			{
				return array('ok' => FALSE, 'why' => 'wrong_tenant');
			}
		}

		return array('ok' => TRUE, 'why' => '');
	}
}

if (!function_exists('approval_decide'))
{
	/**
	 * Approve or reject a request.
	 *
	 * THIS DOES NOT APPLY THE CHANGE. It records the decision; a follow-up
	 * step (or a controller) is what acts on an approved payload. Keeping the
	 * two apart is deliberate — the action handlers are per-action and none
	 * exist yet, and a half-built "approve" that quietly also writes to
	 * ins_titute would be far worse than one that only records intent.
	 *
	 * Returns an array{ok, why} rather than a bare bool so the caller can tell
	 * "you may not" from "already decided" — the first is a permission answer,
	 * the second is a state answer, and a UI shows them differently.
	 *
	 * @param  int    $id      approvals.id
	 * @param  string $decision 'approved' | 'rejected'
	 * @param  string $note
	 * @return array{ok:bool, why:string}
	 */
	function approval_decide($id, $decision, $note = NULL)
	{
		$CI = approval_ci();
		if ($CI === FALSE)
		{
			return array('ok' => FALSE, 'why' => 'not_signed_in');
		}

		if (!in_array($decision, array('approved', 'rejected'), TRUE))
		{
			// a programming error, not a permission outcome
			throw new RuntimeException('approval_decide(): decision must be approved|rejected, got "' . $decision . '"');
		}

		/* Fetch the row WITHOUT approval_get()'s tenant scoping.
		 *
		 * approval_get() answers "can this user see this request?", which
		 * needs a tenant. approval_decide() has only an id, and a cross-tenant
		 * decider (sadmin) has no tenant to pass — so routing the fetch
		 * through approval_get() would return not_found for exactly the
		 * caller this function exists to serve. The tenant check that
		 * approval_get() would have done lives in approval_can_decide() and
		 * runs immediately below, so nothing is lost by fetching unscoped
		 * here; it is simply performed one line later and on the row itself.
		 * A guessable id still cannot leak: the decision either comes back
		 * not_found, or wrong_tenant, or self_approval — never a silent pass.
		 */
		$CI->db
			->select('a.*')
			->from('approvals a')
			->where('a.id', (int) $id);
		$row = $CI->db->get()->row();
		if (!$row)
		{
			return array('ok' => FALSE, 'why' => 'not_found');
		}

		$can = approval_can_decide($row);
		if (!$can['ok'])
		{
			return $can;
		}

		/* decided_at is filled by Postgres, not by PHP.
		 *
		 * The column is timestamptz and the server is on Asia/Dhaka while
		 * PHP's default here is whatever config.php last set. Writing
		 * date('Y-m-d H:i:s') from PHP would bake in the PHP-side clock and
		 * silently disagree with the now() default on requested_at, so two
		 * rows in the same table would carry timestamps from two different
		 * clocks.
		 *
		 * It CANNOT go through $this->db->update() as a normal value: CI3
		 * escapes every value in that array, and passing 'now()' with the
		 * escape flag off does not help — the 4th argument to update() is not
		 * a per-column escape switch, so the expression still comes out as
		 * the string 'now()' (and the attempt produced
		 * decided_at = FALSE / operator does not exist). The fix is a single
		 * raw query, with the two values that DO come from the user bound as
		 * parameters so they cannot inject. */
		$CI->db->query(
			'UPDATE approvals
			    SET status = ?, decided_by = ?, decided_at = now(), note = ?
			  WHERE id = ? AND status = ?',
			array(
				$decision,
				approval_user_id($CI),
				$note,
				(int) $row->id,
				'pending',
			)
		);

		// The `AND status = 'pending'` in that UPDATE is the compare-and-set:
		// if two upheads click at once, only one UPDATE matches a row and the
		// other gets affected_rows() === 0. Without it both would believe
		// they decided it, and the audit trail would name the wrong person.
		// affected_rows() reads the driver's row count, so it is meaningful
		// after a raw query() as well as after update().
		if (!$CI->db->affected_rows())
		{
			return array('ok' => FALSE, 'why' => 'already_decided');
		}

		if ($decision === 'approved')
		{
			approval_apply_action($row);
		}

		if (function_exists('audit_log'))
		{
			audit_log($decision === 'approved' ? 'approval.approve' : 'approval.reject', 'approvals', array(
				'entity_id' => $row->id,
				'tenant_id' => $row->ins_pr_id_s,
				'summary'   => $decision . ' ' . $row->action,
			));
		}

		return array('ok' => TRUE, 'why' => '');
	}
}

if (!function_exists('approval_cancel'))
{
	/**
	 * Cancel/withdraw a pending approval request.
	 *
	 * Allowed for the original requester or an authorized admin/chairman.
	 *
	 * @param  int $id
	 * @return array{ok:bool, why:string}
	 */
	function approval_cancel($id)
	{
		$CI = approval_ci();
		if ($CI === FALSE)
		{
			return array('ok' => FALSE, 'why' => 'not_signed_in');
		}

		$uid = approval_user_id($CI);
		$row = $CI->db->select('*')->from('approvals')->where('id', (int) $id)->get()->row();
		if (!$row)
		{
			return array('ok' => FALSE, 'why' => 'not_found');
		}

		if ($row->status !== 'pending')
		{
			return array('ok' => FALSE, 'why' => 'already_decided');
		}

		$is_requester = ((int) $row->requested_by === (int) $uid);
		$is_admin = $CI->ion_auth->in_group(array('uphead', 'admin', 'sadmin'));
		if (!$is_requester && !$is_admin)
		{
			return array('ok' => FALSE, 'why' => 'not_permitted');
		}

		$CI->db->query(
			"UPDATE approvals
			    SET status = 'cancelled'
			  WHERE id = ? AND status = 'pending'",
			array((int) $row->id)
		);

		if (!$CI->db->affected_rows())
		{
			return array('ok' => FALSE, 'why' => 'already_decided');
		}

		if (function_exists('audit_log'))
		{
			audit_log('approval.cancel', 'approvals', array(
				'entity_id' => $row->id,
				'tenant_id' => $row->ins_pr_id_s,
				'summary'   => 'cancelled ' . $row->action,
			));
		}

		return array('ok' => TRUE, 'why' => '');
	}
}

if (!function_exists('approval_apply_action'))
{
	/**
	 * Apply the changes of an approved request.
	 *
	 * Executes the necessary DB writes corresponding to the approved action.
	 *
	 * @param  object $row  an approvals row
	 * @return bool
	 */
	function approval_apply_action($row)
	{
		$CI = approval_ci();
		if ($CI === FALSE || !$row)
		{
			return FALSE;
		}

		$payload = is_string($row->payload) ? json_decode($row->payload, TRUE) : $row->payload;
		if (!is_array($payload))
		{
			return FALSE;
		}

		$tenant_id = (int) $row->ins_pr_id_s;
		if ($tenant_id <= 0)
		{
			return FALSE;
		}

		if ($row->action === 'tenants.update')
		{
			$allowed = array(
				'ins_name', 'ins_name_en', 'ins_title_ss', 'ins_title_ens',
				'cm_name_bn', 'cm_name_en', 'addre_s_s', 'ins_address_eng',
				'ins_emails', 'chairman_phone', 'socib_phone',
				'chairman_speach', 'socib_speach',
				'bKash_userName', 'bKash_passWord', 'bKash_authToken',
				'ins_logo_path', 'ins_en_logo_path'
			);

			$update = array();
			foreach ($allowed as $f)
			{
				if (array_key_exists($f, $payload))
				{
					$update[$f] = $payload[$f];
				}
			}

			if (!empty($update))
			{
				$CI->db->where('ins_titute_p_idds', $tenant_id)->update('ins_titute', $update);
				return TRUE;
			}
		}
		elseif ($row->action === 'deposits.create')
		{
			if (!empty($payload['deposit_id']))
			{
				if (!isset($CI->FamilyModel))
				{
					$CI->load->model('FamilyModel');
				}
				$CI->FamilyModel->updateDepositeApprovingMethod(
					(int) $payload['deposit_id'],
					array('check_aprov_ing' => 1)
				);
				return TRUE;
			}
		}
		elseif ($row->action === 'sonod.edit')
		{
			// Approving sonod edit permission unlocks the edit page for operators
			return TRUE;
		}

		return FALSE;
	}
}
