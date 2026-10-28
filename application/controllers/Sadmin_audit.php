<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Sadmin_audit — the platform action trail
 * =====================================================================
 *
 * WHAT THIS IS, AND WHAT IT IS NOT
 * audit_logs records what PLATFORM STAFF did, since the moment the table
 * was created. It is not a login log, not a request log, and not a record
 * of the parishads' own activity:
 *
 *   - login_attempts predates this and is a different thing. Auth::login()
 *     writes it, api_login() does not, and it records transport detail
 *     (user agent, browser) rather than "who changed what".
 *   - A parishad administrator editing their own holdings generates
 *     NOTHING here. This is a cross-tenant control, so its scope is the
 *     platform panel — a row appears when a sadmin does something, or
 *     when audit_log() is called explicitly by something else.
 *   - A public support ticket gets no row, on purpose. The ticket IS the
 *     record; two tables holding the same fact means one of them
 *     outlives the other and the audit log ends up counting tickets that
 *     have been deleted. See Support::submit().
 *
 * THERE IS NO HISTORY BEFORE THE FIRST ROW
 * The first row is the first thing that happened after the migration, and
 * that is not a gap to be filled. A migration can invent a plausible
 * history, which is worse than no history, because a fabricated audit
 * trail reads exactly like a real one. Nothing backfills this table.
 *
 * WHAT IS INVISIBLE HERE
 * A row records the actor, the action and the diff. It does NOT record
 * the SQL, and it does not record a failed write — sadmin_write() audits
 * after the write, deliberately, because an entry for a change that never
 * happened is not evidence of anything. So a gap in this log is not proof
 * that nothing happened; it is proof that nothing SUCCEEDED. The
 * difference matters when reading it, and it is stated on the page.
 *
 * THE RETENTION PURGE, AND WHY IT HAS A FLOOR
 * api_purge_before() deletes rows older than a cutoff, which is the one
 * thing an audit log exists to make hard. Three things make it safe
 * enough to ship, and all three are in the method:
 *
 *   1. A retention FLOOR of 90 days. A request older than that is
 *      refused outright, so "delete everything" is not expressible. The
 *      oldest row in this table is days old, so on a young install the
 *      purge cannot do anything at all — which is the correct behaviour
 *      and not a bug to be worked around.
 *   2. The purge writes its OWN row, and cannot avoid it: it deletes rows
 *      OLDER than the cutoff and then records itself, so the record of
 *      the deletion is the newest thing in the table. The one operation
 *      that removes history is itself the best-preserved entry in it.
 *   3. The count is shown before the delete, and the operator confirms
 *      with a typed word rather than a dialog.
 *
 * There is no soft delete and no archive table. A retained copy of rows
 * somebody was allowed to delete is a second audit log with weaker
 * guarantees, and it would be the one that got read.
 */
class Sadmin_audit extends CI_Controller
{
	/**
	 * The action values the CHECK constraint allows, with their Bangla
	 * label and their chip classes.
	 *
	 * Declared here so the filter dropdown cannot offer a value the insert
	 * would reject, and so the label and the colour cannot drift apart —
	 * they are one entry, not a list and a map that have to be kept in
	 * step. A value added to the constraint is a one-line change here.
	 *
	 * The class strings are the same visual vocabulary as
	 * sadmin_helper::status_chip(): green for a thing that now exists,
	 * amber for a switch, rose for a removal or a failure, neutral for
	 * bookkeeping.
	 */
	const ACTIONS = [
		'create'       => ['তৈরি',         'bg-emerald-50 text-emerald-700 border-emerald-200'],
		'update'       => ['পরিবর্তন',     'bg-sky-50 text-sky-700 border-sky-200'],
		'delete'       => ['মোছা',         'bg-rose-50 text-rose-700 border-rose-200'],
		'toggle'       => ['চালু/বন্ধ',     'bg-amber-50 text-amber-700 border-amber-200'],
		'send'         => ['পাঠানো',       'bg-indigo-50 text-indigo-700 border-indigo-200'],
		'read'         => ['পঠন',          'bg-neutral-100 text-neutral-600 border-neutral-200'],
		'login'        => ['লগইন',         'bg-neutral-100 text-neutral-600 border-neutral-200'],
		'login_failed' => ['ব্যর্থ লগইন', 'bg-rose-50 text-rose-700 border-rose-200'],
		'logout'       => ['লগআউট',       'bg-neutral-100 text-neutral-600 border-neutral-200'],
	];

	/** Rows per page. */
	const PER_PAGE = 50;

	/**
	 * Oldest cutoff a purge may be given, in days.
	 *
	 * Ninety is arbitrary but it is not adjustable, and that is the point:
	 * a retention floor that can be edited from the panel is not a floor.
	 */
	const RETENTION_FLOOR_DAYS = 90;

	public function __construct()
	{
		parent::__construct();
		$this->load->library(['ion_auth', 'session']);

		// The gate. Halts on failure.
		require_sadmin();
	}

	/* =====================================================================
	 * Pages
	 * ================================================================== */

	/**
	 * The audit trail: newest first, filtered, with the diff expandable.
	 *
	 * Filters are query parameters, like every other list page in this
	 * panel, for the same reason: an audit entry that cannot be linked to
	 * cannot be quoted to the person who needs to read it.
	 */
	public function index()
	{
		$f = $this->filters();

		$page  = max(1, (int) ($this->input->get('page') ?: 1));
		$total = (int) $this->filtered_query($f)->count_all_results('audit_logs');

		/*
		 * Clamp the page, because a URL can say otherwise.
		 *
		 * Somebody who searched for something and got one page of results
		 * then adds &page=2, or a filter is widened and the URL keeps the
		 * page it had. Either way the raw page number points past the end
		 * of the set, and without this the page renders "কোনো লগ পাওয়া যায়নি"
		 * above a total of 13 — which reads as "the log is empty" and is
		 * the one conclusion nobody should draw from a page about a
		 * record of everything that happened.
		 */
		$pages = (int) ceil($total / self::PER_PAGE);
		if ($pages > 0 && $page > $pages) {
			$page = $pages;
		}

		$rows = $this->filtered_query($f)
			->order_by('created_at', 'DESC')
			->order_by('audit_id', 'DESC')
			->limit(self::PER_PAGE, ($page - 1) * self::PER_PAGE)
			->get('audit_logs')
			->result();

		/*
		 * Totals, counted over the WHOLE table and never over the filtered
		 * page. Same rule as the other modules, and the same reason: a
		 * "মোট" that reports the size of the current filter is a number
		 * that lies whenever a filter is on, which is most of the time
		 * somebody is reading an audit log.
		 */
		$summary = [
			['label' => 'মোট',       'value' => $this->count_where(NULL), 'icon' => 'ph-scroll',            'cls' => 'text-neutral-900'],
			['label' => 'আজ',        'value' => $this->count_since('today'), 'icon' => 'ph-calendar-blank',  'cls' => 'text-indigo-600'],
			['label' => '৭ দিনে',    'value' => $this->count_since('-7 days'), 'icon' => 'ph-calendar-check', 'cls' => 'text-neutral-700'],
			['label' => 'প্ল্যাটফর্ম', 'value' => $this->count_platform(), 'icon' => 'ph-globe',            'cls' => 'text-emerald-600'],
		];

		// The distinct actors and entities in the table, for the dropdowns.
		// Read from the data rather than a hardcoded list so a module that
		// has never run before is still filterable.
		$actors = $this->db->select('actor_id, actor_name, actor_group, count(*) AS n')
			->where('actor_id IS NOT NULL', NULL, FALSE)
			->group_by('actor_id, actor_name, actor_group')
			->order_by('n', 'DESC')
			->get('audit_logs')
			->result();

		$entities = $this->db->select('entity, count(*) AS n')
			->group_by('entity')
			->order_by('entity', 'ASC')
			->get('audit_logs')
			->result();

		// Purge eligibility, computed so the page can refuse in the UI
		// rather than only on submit.
		$floor = date('Y-m-d', strtotime('-' . self::RETENTION_FLOOR_DAYS . ' days'));
		$purgeable = (int) $this->db->where('created_at <', $floor . ' 00:00:00')
			->count_all_results('audit_logs');

		$this->template->sadmin('super_admin/audit', [
			'rows'         => $rows,
			'summary'      => $summary,
			'filters'      => $f,
			'actors'       => $actors,
			'entities'     => $entities,
			'page'         => $page,
			'total'        => $total,
			'per_page'     => self::PER_PAGE,
			'pages'        => $pages,
			'oldest'       => $this->oldest(),
			'purge_floor'  => $floor,
			'purgeable'    => $purgeable,
			'sa_title'     => 'অডিট লগ',
		]);
	}

	/**
	 * The same filtered set, as CSV.
	 *
	 * The reason to keep an audit log past its usefulness on screen. It is
	 * deliberately NOT audited: reading is not a change, and a read that
	 * writes a row turns "show me what changed" into "show me what has
	 * been looked at", which is a different and much larger product.
	 *
	 * Capped, because an unbounded export is a memory problem: the rows are
	 * materialised one at a time by the query builder, but the output is
	 * buffered by CI_Output and a full-table CSV of a busy platform would
	 * exhaust it. The cap is stated in the response so a truncated export
	 * is not mistaken for a complete one.
	 */
	public function export()
	{
		$f    = $this->filters();
		$cap  = 20000;
		$rows = $this->filtered_query($f)
			->order_by('created_at', 'DESC')
			->order_by('audit_id', 'DESC')
			->limit($cap)
			->get('audit_logs')
			->result();

		/*
		 * php://temp, not php://output. Writing straight to stdout means
		 * the headers below have to be sent with the native header()
		 * function, which is too late by the time the first fputcsv has
		 * flushed — CI3's output class buffers and then adds its own
		 * Content-Type, and a browser that has already seen bytes cannot
		 * be told to download instead of render.
		 *
		 * php://temp spills to disk past 2 MB, so a large export does not
		 * have to fit in memory_limit either.
		 */
		$out = fopen('php://temp', 'r+');

		// BOM, because Excel on Windows reads a UTF-8 CSV without one as
		// Latin-1, and this file is Bangla. Open it with an import
		// encoding set and the BOM is redundant and harmless.
		fwrite($out, "\xEF\xBB\xBF");

		fputcsv($out, [
			'time', 'actor', 'group', 'action', 'entity', 'entity_id',
			'tenant_id', 'summary', 'before', 'after', 'ip', 'user_agent',
		]);

		$written = 0;
		foreach ($rows as $r) {
			fputcsv($out, [
				$r->created_at,
				$r->actor_name,
				$r->actor_group,
				$r->action,
				$r->entity,
				$r->entity_id,
				$r->tenant_id,
				$r->summary,
				$this->diff_json($r->before_data),
				$this->diff_json($r->after_data),
				$r->ip_address,
				$r->user_agent,
			]);
			$written++;
		}

		// A truncated export is worse than a refused one, so it says so
		// in the file itself rather than only in the page that linked it.
		if ($written === $cap) {
			fputcsv($out, [
				'', '', '', '', '', '', '',
				'NOTE: this export stopped at the ' . number_format($cap)
					. '-row cap. Narrow the filters to get the rest.',
				'', '', '', '',
			]);
		}

		rewind($out);
		$csv = stream_get_contents($out);
		fclose($out);

		$this->output
			->set_content_type('text/csv', 'UTF-8')
			->set_header('Content-Disposition: attachment; filename="audit-'
				. date('Y-m-d') . '.csv"')
			->set_output($csv);

		/*
		 * _display() then exit, in that order, and both are needed.
		 *
		 * set_output() only parks the body in a buffer — it is
		 * index.php that normally flushes it, and index.php is never
		 * reached again after this returns. So without the explicit
		 * _display() the response is 200 with a 0-byte body and a
		 * text/csv header, which a browser renders as an empty file and
		 * a curl shows as nothing at all. This is the same shape
		 * sadmin_json() uses for the same reason.
		 */
		$this->output->_display();
		exit;
	}

	/* =====================================================================
	 * APIs
	 * ================================================================== */

	/**
	 * Delete audit rows older than a cutoff.
	 *
	 * The three guards are in the docblock. In order of what they stop:
	 * the floor stops "delete everything", the age check stops a
	 * mistyped date in the future wiping the log, and the typed
	 * confirmation stops a click doing it.
	 *
	 * @return void  always halts.
	 */
	public function api_purge_before()
	{
		$input = sadmin_api_input();

		$before = trim((string) ($input['before'] ?? ''));
		$confirm = trim((string) ($input['confirm'] ?? ''));

		$floor = date('Y-m-d', strtotime('-' . self::RETENTION_FLOOR_DAYS . ' days'));

		$ts = strtotime($before);
		if ($ts === FALSE) {
			sadmin_fail('তারিখটি বোঝা গেল না। YYYY-MM-DD ফরম্যাটে দিন।');
		}

		// Guard 1: the retention floor. Not adjustable from here.
		if ($ts < strtotime($floor)) {
			sadmin_fail(
				'এত পুরনো লগ মোছা যাবে না। সর্বনিম্ন তারিখ ' . $floor
				. ' — অর্থাৎ গত ' . bn_number(self::RETENTION_FLOOR_DAYS) . ' দিনের ভেতরের কোনো লগ মোছা যাবে না।'
			);
		}

		// Guard 2: a cutoff in the future. $before is a DATE column in the
		// filter UI and "2030-01-01" passes strtotime happily.
		if ($ts > time()) {
			sadmin_fail('ভবিষ্যতের তারিখ দেওয়া যাবে না।');
		}

		$cutoff = date('Y-m-d', $ts);

		// How many rows this would actually remove, so the number the
		// operator confirms is the real one rather than the one they were
		// shown a minute ago on a page that may since have changed.
		$count = (int) $this->db->where('created_at <', $cutoff . ' 00:00:00')
			->count_all_results('audit_logs');

		if ($count === 0) {
			sadmin_json([
				'success' => FALSE,
				'message' => $cutoff . ' তারিখের আগে কোনো লগ নেই। কিছু মোছার নেই।',
			]);
		}

		// Guard 3: a typed word, not a dialog. See the method docblock.
		if ($confirm !== 'মুছুন') {
			sadmin_fail(
				bn_number(number_format($count)) . ' টি লগ মুছে যাবে। নিশ্চিত করতে বাক্সে '
				. 'মুছুন লিখে আবার চাপুন।',
				409
			);
		}

		$before_ids = $this->db->select('audit_id')
			->where('created_at <', $cutoff . ' 00:00:00')
			->get('audit_logs')
			->result_array();
		$ids = array_column($before_ids, 'audit_id');

		sadmin_write(
			function () use ($ids, $cutoff, $count) {
				if ( ! $this->db->where_in('audit_id', $ids)->delete('audit_logs')) {
					sadmin_fail('লগ মোছা গেল না। কোনো কিছু বদলায়নি।', 500);
				}

				// The purge records itself. It cannot do otherwise: it has
				// just removed everything older than $cutoff, and this row
				// is newer. So the one operation that destroys history is
				// guaranteed to be the most recent thing in the table.
				//
				// The ids are the first 50, not all of them, for the same
				// reason the export is capped: this column is read on a
				// list page and a million-character cell helps nobody.
				$sample = array_slice($ids, 0, 50);

				return [
					'success' => TRUE,
					'message' => bn_number(number_format($count)) . ' টি লগ মুছে ফেলা হয়েছে।',
					'audit'   => [
						'entity'    => 'audit_log',
						'entity_id' => NULL,
						// Platform-level, NOT the tenant of whoever is signed
						// in. Same reasoning as Sadmin_settings: the panel is
						// served on a parishad host in development, and
						// filing a log purge under ধুম because that is
						// where the admin happened to be is exactly the
						// kind of attribution error this table exists to
						// prevent.
						'tenant_id' => NULL,
						'summary'   => $cutoff . ' তারিখের আগের অডিট লগ মোছা হয়েছে — '
							. bn_number(number_format($count)) . ' টি রো',
						'before'    => [
							'cutoff' => $cutoff,
							'rows'   => $count,
							'ids'    => $sample,
						],
						'after'     => [],
					],
				];
			},
			'delete',
			'audit_log',
			[
				'tenant_id' => NULL,
				'summary'   => 'অডিট লগ মোছা',
			]
		);
	}

	/* =====================================================================
	 * Internals
	 * ================================================================== */

	/**
	 * The current filter set, validated.
	 *
	 * Every value is checked against something before it reaches the query
	 * builder. `action` and `entity` are the two that matter: they are
	 * both text columns and a wrong value in a filter is a filter that
	 * silently returns nothing, which on an audit log looks exactly like
	 * "nothing happened".
	 *
	 * @return array
	 */
	private function filters()
	{
		$q      = trim((string) ($this->input->get('q') ?: ''));
		$action = trim((string) ($this->input->get('action') ?: ''));
		$entity = trim((string) ($this->input->get('entity') ?: ''));
		$actor  = (int) ($this->input->get('actor_id') ?: 0);
		$tenant = trim((string) ($this->input->get('tenant_id') ?: ''));
		$from   = trim((string) ($this->input->get('from') ?: ''));
		$to     = trim((string) ($this->input->get('to') ?: ''));

		$date = function ($v) {
			/*
			 * A real calendar date, not just a well-shaped string.
			 *
			 * A regex alone is not enough: "2026-09-31" matches
			 * ^\d{4}-\d{2}-\d{2}$ and is not a date that exists, and it
			 * reached Postgres as a literal, which answered 22008
			 * "date/time field value out of range" and turned a filter
			 * into a 500. createFromFormat() plus a format round-trip is
			 * the check that actually rejects it — and rejects 2026-02-30
			 * and 2026-13-01 the same way, all three of which pass a
			 * regex and all three of which are 500s.
			 *
			 * strtotime() is deliberately not used for the same reason it
			 * is not used above: it accepts "last tuesday", so a filter
			 * URL means something different next week.
			 */
			$d = DateTime::createFromFormat('!Y-m-d', $v);
			if ($d === FALSE || $d->format('Y-m-d') !== $v) {
				return '';
			}

			/*
			 * Bounded, and the lower bound is not arbitrary.
			 *
			 * PHP's proleptic Gregorian calendar has a year zero and
			 * happily round-trips "0000-01-01", but Postgres has no year
			 * zero at all — the string reached the database as a literal
			 * and came back as 22008, so a filter box that accepts a
			 * year with three zeroes in it is a 500. 1970 is where the
			 * filter floor belongs anyway: it is the epoch of the
			 * timestamptz column this filters, so a date before it can
			 * never match a row and is a typo either way.
			 */
			$year = (int) $d->format('Y');
			if ($year < 1970 || $year > 9999) {
				return '';
			}

			return $v;
		};

		// 'platform' is a UI value, not a column value. tenant_id NULL means
		// a platform-level action, and there is no way to express that in a
		// query string without a sentinel — this is that sentinel.
		if ($tenant !== '' && $tenant !== 'platform' && !ctype_digit($tenant)) {
			$tenant = '';
		}

		return [
			'q'         => mb_substr($q, 0, 100),
			'action'    => isset(self::ACTIONS[$action]) ? $action : '',
			'entity'    => mb_substr($entity, 0, 40),
			'actor_id'  => $actor > 0 ? $actor : 0,
			'tenant_id' => $tenant,
			'from'      => $date($from),
			'to'        => $date($to),
		];
	}

	/**
	 * A query builder with the filters applied, for the caller to finish.
	 *
	 * Returned un-run so the same filter set can be counted, then paged,
	 * then exported — three calls that must agree, and which cannot agree
	 * if each builds its own WHERE.
	 *
	 * Note reset_query() first. A CI query builder is stateful, and a
	 * helper called after some other helper has started a query would AND
	 * itself into it.
	 */
	private function filtered_query(array $f)
	{
		$this->db->reset_query();

		$this->db->select('audit_logs.*');

		if ($f['q'] !== '') {
			// summary, actor_name and ip_address. Not before_data or
			// after_data: those are JSONB, and a LIKE across a JSONB
			// column is both slow and a way to find a value that is
			// visible in a diff but not in the search box.
			$this->db->group_start();
			$this->db->like('summary', $f['q']);
			$this->db->or_like('actor_name', $f['q']);
			$this->db->or_like('ip_address', $f['q']);
			$this->db->or_like('entity', $f['q']);
			$this->db->group_end();
		}

		if ($f['action'] !== '') {
			$this->db->where('action', $f['action']);
		}
		if ($f['entity'] !== '') {
			$this->db->where('entity', $f['entity']);
		}
		if ($f['actor_id'] > 0) {
			$this->db->where('actor_id', $f['actor_id']);
		}

		if ($f['tenant_id'] === 'platform') {
			// Raw fragment, unescaped: CI3 has no ->where_is_null(), and
			// ->where('tenant_id', NULL) becomes "IS NULL" only by
			// accident of the driver's escaping.
			$this->db->where('tenant_id IS NULL', NULL, FALSE);
		} elseif ($f['tenant_id'] !== '' && ctype_digit($f['tenant_id'])) {
			$this->db->where('tenant_id', (int) $f['tenant_id']);
		}

		if ($f['from'] !== '') {
			$this->db->where('created_at >=', $f['from'] . ' 00:00:00');
		}
		if ($f['to'] !== '') {
			// Inclusive of the whole end day. ->where('created_at <=',
			// '2026-09-30') would cut the day off at midnight and hide
			// every entry written after breakfast, which is a filter that
			// makes the log look emptier than it is at exactly the moment
			// somebody is looking for something that happened today.
			$this->db->where('created_at <', date('Y-m-d', strtotime($f['to'] . ' +1 day')));
		}

		return $this->db;
	}

	/**
	 * A summary count. NULL for $where means "the whole table".
	 *
	 * Named count_where() rather than count_all() because the summary
	 * cards need several different counts and one unfiltered total, and a
	 * reader should be able to see which is which.
	 */
	private function count_where($where)
	{
		$this->db->reset_query();
		if ($where !== NULL) {
			$this->db->where($where, NULL, FALSE);
		}
		return (int) $this->db->count_all_results('audit_logs');
	}

	/** Rows created since a strtotime() expression, e.g. 'today'. */
	private function count_since($expr)
	{
		$this->db->reset_query();
		return (int) $this->db->where('created_at >=', date('Y-m-d 00:00:00', strtotime($expr)))
			->count_all_results('audit_logs');
	}

	/** Rows with no tenant — the platform's own actions. */
	private function count_platform()
	{
		$this->db->reset_query();
		return (int) $this->db->where('tenant_id IS NULL', NULL, FALSE)
			->count_all_results('audit_logs');
	}

	/** The oldest row's timestamp, or NULL when the table is empty. */
	private function oldest()
	{
		$this->db->reset_query();
		return $this->db->order_by('created_at', 'ASC')
			->limit(1)
			->get('audit_logs')
			->row('created_at');
	}

	/**
	 * One side of a diff, as readable JSON.
	 *
	 * json_encode with pretty print and unescaped unicode, because this
	 * text is read by a person looking at a change. A diff rendered as a
	 * single line of escaped Bangla is technically correct and practically
	 * unreadable.
	 */
	private function diff_json($jsonb)
	{
		if ($jsonb === NULL || $jsonb === '') {
			return '';
		}
		$decoded = json_decode($jsonb, TRUE);
		if ($decoded === NULL) {
			return (string) $jsonb;   // not JSON somehow; show it raw
		}
		if ($decoded === [] || $decoded === NULL) {
			return '';
		}
		// Pretty printed, unescaped unicode, and UNESCAPED_SLASHES: the
		// default escapes every "/" to "\/", which turns a stored URL in a
		// settings diff into https:\/\/… in both the CSV and the panel.
		// Readable by a person, which is the only reason to print it.
		return json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	}
}
