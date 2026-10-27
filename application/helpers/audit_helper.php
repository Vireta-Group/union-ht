<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * audit_log() — cross-tenant action trail
 * =====================================================================
 *
 * Autoloaded (see config/autoload.php), so it is callable from any
 * controller or model without a $this->load->helper() line.
 *
 * WHY A FUNCTION AND NOT A MODEL
 * ------------------------------
 * The same reasoning as enforce_tenant_scope() in tenant_helper.php:
 * this codebase has no base controller, so a model would have to be
 * loaded by hand at every call site, and a call site that forgets is a
 * silent hole. A function in an autoloaded helper cannot be forgotten.
 *
 * NEVER FATAL — AND WHY THAT HAD TO BE PROVEN, NOT ASSUMED
 * ----------------------------------------------------------
 * An audit write that throws turns a successful admin action into a
 * blank page — and this project runs with log_threshold = 0, so there
 * would be nothing in application/logs/ to explain it. Every failure
 * path here returns FALSE and lets the caller carry on. The caller's
 * real work is never blocked by the trail meant to record it.
 *
 * "Never fatal" was a claim, not a fact, until the group lookup in
 * audit_actor_from_session() was fixed. That function ran before the
 * ->insert() this file checks, and its query died at COMPILE time — so
 * the guard was downstream of the thing it was supposed to guard. The
 * symptom was worse than a blank page and worth recording here, because
 * the natural reaction to it is the wrong one:
 *
 *   The panel write COMMITTED and the request still returned HTTP 500.
 *
 * A ticket was deleted, the row was gone, and the operator was shown an
 * error and would reasonably try again. A support reply was stored twice,
 * because the retry re-posted it. "It said 500 so probably nothing
 * happened" is the one assumption a 500 must never invite, and it is
 * exactly what this bug taught the opposite of.
 *
 * So the rule for anyone extending this file: a query here must not be
 * able to reach DB_driver::display_error(). Two ways to do that are in
 * use — check the return value, as the insert below does, or set
 * $CI->db->db_debug = FALSE around it and restore it on the next line, as
 * audit_actor_from_session() does. A SELECT that cannot fail is not a
 * thing to assume; a missing column is how the first version of that
 * query was wrong.
 *
 * WHAT IS NOT RECORDED
 * --------------------
 * Nothing before this table existed. There is no backfill and none is
 * possible: a migration can invent a plausible history, which is worse
 * than no history, because a fabricated audit trail reads exactly like
 * a real one. The first row is the first thing that happened after the
 * migration.
 *
 *   login_attempts predates this and is a separate thing — it is
 *   written by Auth::login() only, not by api_login(), and it records
 *   transport detail (user agent, browser, location) rather than
 *   "who changed what". Both are left alone.
 *
 * @param  string      $action  One of the values the CHECK constraint
 *                             allows: create, update, delete, login,
 *                             login_failed, logout, toggle, send, read.
 *                             A value outside that list makes the
 *                             INSERT fail — which is deliberate, see
 *                             not_fatal() below.
 * @param  string      $entity  The table or subsystem, e.g. tenant,
 *                             user, domain, payment, setting, ticket.
 * @param  array       $opts    Optional:
 *                             - entity_id  (string)   the row's key
 *                             - tenant_id  (int)      NULL = platform-level
 *                             - summary    (string)   human sentence for the list view
 *                             - before     (array)    JSONB, pre-change row
 *                             - after      (array)    JSONB, post-change row
 *                             - actor_id / actor_name / actor_group
 *                                           override the session-derived actor
 * @return bool                FALSE if it could not be written.
 */
function audit_log($action, $entity, array $opts = [])
{
	$CI =& get_instance();

	if (!isset($CI->db))
	{
		return FALSE;
	}

	$session_user = audit_actor_from_session();
	$tenant_id    = array_key_exists('tenant_id', $opts)
		? $opts['tenant_id']
		: (function_exists('tenant_code') ? (int) tenant_code() : 0);

	$row = [
		'actor_id'    => $opts['actor_id']   ?? $session_user['id'],
		'actor_name'  => $opts['actor_name'] ?? $session_user['name'],
		'actor_group' => $opts['actor_group'] ?? $session_user['group'],
		// NULL means "not on behalf of any parishad". It is NOT 0: 0 is the
		// resolver's "no tenant" sentinel, and reusing it here would make a
		// platform-level row indistinguishable from a tenant-0 row.
		'tenant_id'   => $tenant_id > 0 ? (int) $tenant_id : NULL,
		'action'      => (string) $action,
		'entity'      => (string) $entity,
		'entity_id'   => isset($opts['entity_id']) ? (string) $opts['entity_id'] : NULL,
		'summary'     => isset($opts['summary']) ? (string) $opts['summary'] : NULL,
		'ip_address'  => audit_client_ip(),
		'user_agent'  => audit_user_agent(),
	];

	// json_encode on an array, then hand the JSON to CI's escaping. Two
	// escaping passes would be wrong, so the column is written as a
	// literal cast and must not go through ->insert()'s escaping.
	foreach (['before' => 'before_data', 'after' => 'after_data'] as $key => $col)
	{
		if (array_key_exists($key, $opts) && is_array($opts[$key]))
		{
			$row[$col] = json_encode($opts[$key], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		}
	}

	// insert(), not replace(). replace() makes CI3 emit
	// INSERT ... ON CONFLICT on the table's primary key, and audit_id is
	// a sequence that is not in $row — there is no conflict target to
	// name, and a malformed ON CONFLICT is a 500 on every audit write.
	$db_debug = $CI->db->db_debug;
	$CI->db->db_debug = FALSE;
	$inserted = $CI->db->insert('audit_logs', $row);
	$CI->db->db_debug = $db_debug;

	if ($inserted === FALSE)
	{
		$CI->db->reset_query();
		return audit_not_fatal('insert rejected — action="'.$action.'", entity="'.$entity.'"');
	}

	return TRUE;
}

/**
 * The signed-in user, as far as the session records them.
 *
 * ion_auth's set_session() writes identity, email, user_id,
 * union_code and ins_pr_id_s — and no group. The group is needed for
 * "was this the platform role or a parishad admin", so it is read once
 * per request from users_groups and cached in a static: the audit log
 * is written several times per page and a query per row is not worth it.
 *
 * @return array  id, name, group
 */
function audit_actor_from_session()
{
	static $group_cache = NULL;

	$out = ['id' => NULL, 'name' => NULL, 'group' => NULL];

	if (!function_exists('get_instance'))
	{
		return $out;
	}
	$CI =& get_instance();
	if (!isset($CI->session))
	{
		return $out;
	}

	$id = $CI->session->userdata('user_id');
	if (empty($id))
	{
		return $out;
	}
	$out['id']   = (int) $id;
	$out['name'] = $CI->session->userdata('identity');

	if ($group_cache === NULL)
	{
		$group_cache = [];

		/*
		 * Most-privileged group wins if the row is in several. sadmin is
		 * the platform role, so it has to sort ABOVE the parishad roles or
		 * a sadmin who also holds 'admin' is logged as a tenant admin.
		 *
		 * The CASE is the whole reason this is not order_by('groups.id').
		 * The ids happen to be admin=1, members=2, sadmin=3, and ordering
		 * by id — which is what this query used to do — therefore picked
		 * 'admin' for exactly the account it needed to distinguish. Ids are
		 * an accident of the seed data; privilege order is a statement about
		 * the product, so it is written as one.
		 *
		 * Second bug in the same statement, and the reason this was a 500 on
		 * every audit write: the select and order_by named an alias `g` that
		 * the join never declared. The join reads ->join('groups', ...), so
		 * the table is in the query as `groups`, and Postgres rejected
		 * 'g.name' with 42P01 missing FROM-clause entry. The failure happened
		 * while COMPILING the query, which is before the ->insert() that
		 * audit_log() checks — so the "never fatal" guarantee below did not
		 * hold for it and a completed panel action came back as HTTP 500.
		 */
		$order = "CASE \"groups\".\"name\""
			. " WHEN 'sadmin' THEN 0"
			. " WHEN 'admin' THEN 1"
			. " ELSE 2 END";

		/*
		 * db_debug is switched off for the duration of this one lookup.
		 * That is what actually delivers the "NEVER FATAL" promise at the
		 * top of this file: db_debug = TRUE routes a query failure to
		 * DB_driver::display_error(), which echoes a page and exits, and
		 * that happens while the query is COMPILED — before the
		 * ->insert() that audit_log() checks, and before any caller can
		 * decide to carry on. Restored immediately, on the line after, so
		 * nothing downstream inherits the setting.
		 */
		$db_debug = $CI->db->db_debug;
		$CI->db->db_debug = FALSE;

		$row = $CI->db->select('groups.name')
			->from('users_groups')
			->join('groups', 'groups.id = users_groups.group_id')
			->where('users_groups.user_id', (int) $id)
			->order_by($order, 'ASC', FALSE)
			->order_by('groups.id', 'ASC')
			->limit(1)
			->get()
			->row();

		$CI->db->db_debug = $db_debug;

		// A false row here means no group, not an error: a user with no
		// users_groups row is a legitimate state, and audit_log() records
		// actor_group = NULL for them rather than guessing.
		$group_cache = $row ? [$row->name] : [];
	}
	$out['group'] = $group_cache ? $group_cache[0] : NULL;

	return $out;
}

/**
 * Client IP, read from the headers a proxy may set and falling back to
 * REMOTE_ADDR.
 *
 * X-Forwarded-For is client-supplied, so the left-most entry is the
 * easiest value in the whole request to forge. It is recorded because an
 * audit row without an IP is much less useful during an incident, and
 * because this is a record of a claim, not an enforcement decision —
 * nothing in the app grants access on the strength of this value.
 */
function audit_client_ip()
{
	$CI =& get_instance();
	if (!isset($CI->input))
	{
		return NULL;
	}

	$ip = $CI->input->server('HTTP_X_FORWARDED_FOR');
	if ($ip)
	{
		$parts = explode(',', $ip);
		$ip    = trim($parts[0]);
	}
	if (!$ip)
	{
		$ip = $CI->input->server('REMOTE_ADDR');
	}

	return $ip ? substr($ip, 0, 45) : NULL;
}

/** User agent, truncated to the column width. */
function audit_user_agent()
{
	$CI =& get_instance();
	if (!isset($CI->input))
	{
		return NULL;
	}
	$ua = $CI->input->user_agent();
	return $ua ? substr($ua, 0, 255) : NULL;
}

/**
 * A failed audit write must not take the request down, and must not be
 * silent either — log_threshold is 0 in this project, so nothing lands
 * in application/logs/ on its own.
 *
 * @return bool  Always FALSE, so the caller can just `return` it.
 */
function audit_not_fatal($why)
{
	$CI =& get_instance();

	// error_log() writes to stderr, which PHP's dev server and php-fpm
	// both surface. It is the only channel left with log_threshold = 0.
	error_log('[audit_log] ' . $why);

	return FALSE;
}
