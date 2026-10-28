<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Sadmin_support — the helpdesk
 * =====================================================================
 *
 * WHAT EXISTED BEFORE THIS
 * A help button. The product site loads one support modal on every page
 * (saas/partials/support_modal.php) and saas/pages/contact.php has a form,
 * and neither posted anywhere: there was no endpoint behind them, no table
 * to land in, and no way for anybody to see what had been sent. A visitor
 * who wrote to "support" wrote into the void.
 *
 * The tables exist (support_tickets, support_replies — migration
 * 20260930_sadmin_panel.sql). The intake endpoint is Support::submit(),
 * which is NOT in this controller and carries no sadmin gate: a parishad
 * asking for help is by definition not a platform super admin. See the
 * route table.
 *
 * WHAT "STATUS" MEANS HERE, PRECISELY
 * Four values, and the two that matter are not the two an operator
 * expects:
 *
 *   open      nobody has looked at it yet
 *   pending   a staff member is waiting on the requester
 *   resolved  answered; closed_at is stamped
 *   closed    abandoned or answered and filed; closed_at is stamped
 *
 * "pending" is staff-waiting-on-customer, not customer-waiting-on-staff.
 * A reply from a customer moves it back to open; a reply from staff
 * leaves it where it was. That asymmetry is the only thing that makes the
 * column worth reading, and it is why a bare "status" column on a helpdesk
 * is usually noise.
 *
 * closed_at is the time the ticket entered a terminal state (resolved or
 * closed) and is CLEARED if it is reopened. It is not "last touched" and
 * it is not "first opened" — the first opened time is created_at, which
 * nobody overwrites.
 *
 * ASSIGNMENT IS A USERS.id REFERENCE AND NOTHING MORE
 * assigned_to points at the users table, so it is a platform-level
 * reference: a sadmin, i.e. whoever is answering from the vendor side.
 * It is deliberately NOT scoped to a tenant and there is no code path
 * that treats it as a tenant employee. Nothing reads it for any
 * authorisation decision — it is a "who is on this" label.
 *
 * WHAT IS NOT HERE
 * No SLA clock, no canned-response library, no email notification. A
 * ticket that nobody is told about by mail is only noticed by opening this
 * page, and that is a real limitation — it is recorded in
 * docs/SADMIN-PANEL.md rather than hidden behind a tick.
 */
class Sadmin_support extends CI_Controller
{
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
	 * The ticket list.
	 *
	 * Filters are applied in SQL for the same reason the subscriptions
	 * module does it: the summary cards above the table are counted over
	 * the whole table, and a filter applied in the view would make those
	 * cards report the size of the filtered set while the operator reads
	 * them as the size of the backlog.
	 *
	 * The default ordering is NOT "newest first". A helpdesk is worked
	 * oldest-first, and a list sorted newest-first puts the ticket that has
	 * been waiting three days below the one filed two minutes ago. Urgent
	 * rows float to the top of their group instead, because priority is
	 * the one field that should override age.
	 */
	public function index()
	{
		$filter_status   = (string) ($this->input->get('status') ?: '');
		$filter_priority = (string) ($this->input->get('priority') ?: '');
		$filter_category = (string) ($this->input->get('category') ?: '');
		$filter_tenant   = (int) ($this->input->get('tenant_id') ?: 0);
		$filter_mine     = (string) ($this->input->get('mine') ?: '');
		$q               = trim((string) ($this->input->get('q') ?: ''));

		$me = $this->ion_auth->user()->row();

		$this->db->select('k.*, t.ins_name, t.sub_domains, a.username AS assigned_name')
			->from('support_tickets k')
			->join('ins_titute t', 't.ins_titute_p_idds = k.tenant_id', 'left')
			->join('users a', 'a.id = k.assigned_to', 'left');

		if (in_array($filter_status, ['open', 'pending', 'resolved', 'closed'], TRUE)) {
			$this->db->where('k.status', $filter_status);
		}
		if (in_array($filter_priority, ['low', 'normal', 'high', 'urgent'], TRUE)) {
			$this->db->where('k.priority', $filter_priority);
		}
		if (in_array($filter_category, ['general', 'technical', 'billing', 'domain', 'training', 'other'], TRUE)) {
			$this->db->where('k.category', $filter_category);
		}
		if ($filter_tenant > 0) {
			$this->db->where('k.tenant_id', $filter_tenant);
		}
		if ($filter_mine === '1' && $me) {
			$this->db->where('k.assigned_to', $me->id);
		}

		/*
		 * Search. group_start()/or_where()/group_end() is not optional
		 * here — CI3's where() ANDs, so five bare like() calls with no
		 * grouping produce "subject LIKE a AND ticket_no LIKE a AND …",
		 * which matches nothing, and a helpdesk search that silently
		 * returns zero rows on a table with tickets in it is the exact
		 * failure AGENTS.md records having already been made twice.
		 *
		 * BOTH SIDES ARE UNICODE-NORMALISED. Bengali য় is one visible
		 * letter with two code point sequences — precomposed U+09DF, or
		 * the pair U+09AF U+09BC — and which one you get depends on the
		 * input method. Under a deterministic collation those are
		 * different strings, so a requester typing চেয়ারম্যান cannot
		 * find a ticket whose user_name holds the other form.
		 *
		 * support_tickets.user_name and .message are the two columns in
		 * this database that store the NFD form right now, so this
		 * search working is the NFD half; the NFC half is a chairman
		 * typing their own name into the same box. The page search on
		 * /sadmin_users was found by the same reasoning — see
		 * docs/SADMIN-PANEL.md section 7.7, which also records what is
		 * still NOT normalised: the public portal's searches and every
		 * equality comparison.
		 *
		 * The LIKE is hand-built rather than using $this->db->like(),
		 * because like() cannot wrap the field in a function call and
		 * cannot normalise the needle. escape_like_str() then
		 * escape() is CI3's own escaping, and the explicit ESCAPE '!'
		 * is the clause CI3's like() would have appended.
		 */
		if ($q !== '') {
			$needle = $this->db->escape('%' . $this->db->escape_like_str($q) . '%');

			$this->db->group_start();
			foreach (['subject', 'ticket_no', 'user_name', 'user_email', 'message'] as $col) {
				$this->db->or_where(
					'normalize(k.' . $col . ', NFC) LIKE normalize(' . $needle . ", NFC) ESCAPE '!'",
					NULL,
					FALSE
				);
			}
			$this->db->group_end();
		}

		// Urgent first inside any ordering, then oldest first.
		$this->db->order_by(
			"CASE WHEN k.priority = 'urgent' THEN 0 WHEN k.priority = 'high' THEN 1 ELSE 2 END",
			'ASC',
			FALSE
		);
		$this->db->order_by('k.created_at', 'ASC');

		$rows = $this->db->limit(300)->get()->result();

		/*
		 * Reply counts for the whole page in one query. Same reasoning as
		 * the subscriptions payment history: a loop issuing one COUNT per
		 * row is the shape that makes a page take four seconds.
		 */
		$ids = array_column($rows, 'ticket_id');
		$data['reply_counts'] = [];
		if ($ids) {
			foreach ($this->db->select('ticket_id, COUNT(*) AS c')
				->where_in('ticket_id', $ids)
				->group_by('ticket_id')
				->get('support_replies')
				->result() as $r) {
				$data['reply_counts'][(int) $r->ticket_id] = (int) $r->c;
			}
		}

		// Counts, always over the whole table.
		$data['tk_total'] = (int) $this->db->count_all('support_tickets');
		$this->db->reset_query();

		foreach (['open', 'pending', 'resolved', 'closed'] as $st) {
			$data['tk_' . $st] = (int) $this->db->where('status', $st)->count_all_results('support_tickets');
			$this->db->reset_query();
		}

		// "Not yet closed" is an OR. Same grouping requirement as the nav
		// badge, for the same reason.
		$this->db->group_start();
		$this->db->where('status', 'open');
		$this->db->or_where('status', 'pending');
		$this->db->group_end();
		$data['tk_backlog'] = (int) $this->db->count_all_results('support_tickets');
		$this->db->reset_query();

		$data['tk_urgent'] = (int) $this->db
			->where('priority', 'urgent')
			->group_start()
			->where('status', 'open')
			->or_where('status', 'pending')
			->group_end()
			->count_all_results('support_tickets');
		$this->db->reset_query();

		// assigned_to IS NULL, written as a raw fragment: CI3's third
		// where() argument is $escape, not an operator.
		$data['tk_unassigned'] = (int) $this->db
			->where('assigned_to IS NULL', NULL, FALSE)
			->group_start()
			->where('status', 'open')
			->or_where('status', 'pending')
			->group_end()
			->count_all_results('support_tickets');
		$this->db->reset_query();

		// Oldest ticket still waiting, as a date. Read straight off the
		// same ordering the list uses, so the card and the top row agree.
		$data['tk_oldest_open'] = $this->db
			->select('MIN(created_at) AS oldest')
			->where('status', 'open')
			->get('support_tickets')
			->row('oldest');

		// Who can be assigned. Staff only: users in the sadmin or admin
		// groups. A parishad's own admin is not a support agent for the
		// platform, and letting them be assigned would put the vendor's
		// helpdesk in the hands of the customers.
		/*
		 * g.name is NOT selected here even though the join is what proves
		 * the user is staff. Selecting it while grouping by u.id alone is
		 * exactly the 42803 Postgres rejects — and the column is not used
		 * by the list view, so the ticket page's copy of this query, which
		 * does not select it, is the one to copy.
		 */
		$data['assignees'] = $this->db
			->select('u.id, u.username, u.email, u.first_name')
			->from('users u')
			->join('users_groups ug', 'ug.user_id = u.id')
			->join('groups g', 'g.id = ug.group_id')
			->group_start()
			->where('g.name', 'sadmin')
			->or_where('g.name', 'admin')
			->group_end()
			->group_by('u.id')
			->order_by('u.id', 'ASC')
			->get()
			->result();

		$data['tenants'] = $this->db
			->select('ins_titute_p_idds, ins_name')
			->where('ins_titute_p_idds >', 0)
			->order_by('ins_titute_p_idds', 'ASC')
			->get('ins_titute')
			->result();

		$data['rows'] = $rows;

		$data['filter_status']   = $filter_status;
		$data['filter_priority'] = $filter_priority;
		$data['filter_category'] = $filter_category;
		$data['filter_tenant']   = $filter_tenant;
		$data['filter_mine']     = $filter_mine;
		$data['q']               = $q;
		$data['me_id']           = $me ? (int) $me->id : 0;

		$data['statuses'] = [
			'open'     => 'নতুন',
			'pending'  => 'অপেক্ষমাণ',
			'resolved' => 'সমাধান হয়েছে',
			'closed'   => 'বন্ধ',
		];
		$data['priorities'] = [
			'low'    => 'নিম্ন',
			'normal' => 'সাধারণ',
			'high'   => 'উচ্চ',
			'urgent' => 'জরুরি',
		];
		$data['categories'] = [
			'general'   => 'সাধারণ',
			'technical' => 'টেকনিক্যাল',
			'billing'   => 'বিলিং',
			'domain'    => 'ডোমেইন',
			'training'  => 'প্রশিক্ষণ',
			'other'     => 'অন্যান্য',
		];

		$data['sa_title'] = 'সাপোর্ট';
		$data['sa_desc']  = 'টিকিট, উত্তর ও অগ্রাধিকার।';

		$this->template->sadmin('super_admin/support', $data);
	}

	/**
	 * One ticket, with its thread.
	 *
	 * A separate page rather than a modal because a support conversation
	 * is read, scrolled and often linked to in an answer to somebody else
	 * — a modal cannot be linked to and collapses on a long thread.
	 *
	 * tenant_id is looked up with a LEFT JOIN because it is a nullable FK
	 * that goes to NULL when a parishad row is deleted, and a ticket from
	 * an anonymous visitor has never had one. The tenant name column is
	 * therefore allowed to be empty and the view says "unassigned /
	 * product site" rather than printing a broken cell.
	 */
	public function ticket($id = 0)
	{
		$ticket = $this->db
			->select('k.*, t.ins_name, t.sub_domains, a.username AS assigned_name, a.email AS assigned_email')
			->from('support_tickets k')
			->join('ins_titute t', 't.ins_titute_p_idds = k.tenant_id', 'left')
			->join('users a', 'a.id = k.assigned_to', 'left')
			->where('k.ticket_id', (int) $id)
			->get()
			->row();

		if (!$ticket) {
			show_error('টিকিট পাওয়া যায়নি।', 404);
		}

		$data['ticket']       = $ticket;
		$data['ticket_token'] = hash_hmac('sha256', $ticket->ticket_id . ':' . $ticket->ticket_no . ':' . $ticket->created_at, $this->config->item('encryption_key') ?: 'vireta_support_hmac_secret_2026');

		$data['replies'] = $this->db
			->select('r.*, u.username AS sender_username')
			->from('support_replies r')
			->join('users u', 'u.id = r.sender_id', 'left')
			->where('r.ticket_id', (int) $id)
			->order_by('r.created_at', 'ASC')
			->order_by('r.reply_id', 'ASC')
			->get()
			->result();

		$data['assignees'] = $this->db
			->select('u.id, u.username, u.email, u.first_name')
			->from('users u')
			->join('users_groups ug', 'ug.user_id = u.id')
			->join('groups g', 'g.id = ug.group_id')
			->group_start()
			->where('g.name', 'sadmin')
			->or_where('g.name', 'admin')
			->group_end()
			->group_by('u.id')
			->order_by('u.id', 'ASC')
			->get()
			->result();

		$data['statuses'] = [
			'open'     => 'নতুন',
			'pending'  => 'অপেক্ষমাণ',
			'resolved' => 'সমাধান হয়েছে',
			'closed'   => 'বন্ধ',
		];
		$data['priorities'] = [
			'low'    => 'নিম্ন',
			'normal' => 'সাধারণ',
			'high'   => 'উচ্চ',
			'urgent' => 'জরুরি',
		];
		// The category map is sent to both pages. status_chip() has an
		// entry per status and per priority but not per category, and
		// calling it for a category returns the raw English key — so the
		// labels are declared once, next to the values they label.
		$data['categories'] = [
			'general'   => 'সাধারণ',
			'technical' => 'টেকনিক্যাল',
			'billing'   => 'বিলিং',
			'domain'    => 'ডোমেইন',
			'training'  => 'প্রশিক্ষণ',
			'other'     => 'অন্যান্য',
		];

		$data['sa_title'] = 'টিকিট ' . $ticket->ticket_no;
		$data['sa_desc']  = $ticket->subject;

		$this->template->sadmin('super_admin/support_ticket', $data);
	}

	/* =====================================================================
	 * APIs
	 * ================================================================== */

	/**
	 * API: post a staff reply.
	 *
	 * The status side effect is the whole reason this is not a plain
	 * insert. A ticket sitting in 'pending' means WE are waiting on the
	 * customer; a staff reply does not resolve that, so it is left alone.
	 * A ticket sitting in 'resolved' that gets a further staff reply is
	 * being reopened — it goes back to 'open', and closed_at is cleared
	 * because it is no longer in a terminal state.
	 *
	 * 'closed' is never auto-reopened by a reply. Somebody closed it on
	 * purpose; a later reply is a question, not a resurrection.
	 *
	 * sender_id is the acting sadmin's users.id, taken from the session
	 * rather than the payload: a client that can choose who a reply is
	 * attributed to is a client that can impersonate a colleague in the
	 * audit log.
	 */
	public function api_reply()
	{
		$input   = sadmin_api_input();
		$ticket_id = (int) ($input['ticket_id'] ?? 0);
		$message = trim((string) ($input['message'] ?? ''));

		if ($ticket_id <= 0) {
			sadmin_fail('টিকিট নির্বাচন করা হয়নি।');
		}
		if ($message === '') {
			sadmin_fail('উত্তর লিখুন।');
		}
		if (mb_strlen($message) > 5000) {
			sadmin_fail('উত্তর ৫০০০ অক্ষরের বেশি হতে পারবে না।');
		}

		$ticket = $this->db->where('ticket_id', $ticket_id)->get('support_tickets')->row();
		if (!$ticket) {
			sadmin_fail('টিকিট পাওয়া যায়নি।', 404);
		}

		$me = $this->ion_auth->user()->row();

		sadmin_write(function () use ($ticket, $message, $me) {
			$this->db->insert('support_replies', [
				'ticket_id'    => $ticket->ticket_id,
				'sender_id'    => $me ? (int) $me->id : NULL,
				'sender_name'  => $me ? (trim($me->first_name ?? '') ?: $me->username) : NULL,
				'sender_role'  => 'staff',
				'message'      => $message,
			]);

			$reopened = FALSE;
			if ($ticket->status === 'resolved') {
				$reopened = TRUE;
			}

			$update = ['updated_at' => date('Y-m-d H:i:s')];
			if ($reopened) {
				$update['status']    = 'open';
				$update['closed_at'] = NULL;
			}

			$this->db->where('ticket_id', $ticket->ticket_id)->update('support_tickets', $update);

			$reply_id    = (int) $this->db->insert_id();
			$sender_name = $me ? (trim($me->first_name ?? '') ?: $me->username) : 'সাপোর্ট টিম';

			// Ring the doorbell. A client with the chat open sees the answer arrive live,
			// and any open manager panels see the reply event.
			if (function_exists('support_event'))
			{
				support_event('reply', [
					'ticket_id'   => (int) $ticket->ticket_id,
					'ticket_no'   => $ticket->ticket_no,
					'reply_id'    => $reply_id,
					'by'          => 'staff',
					'sender_name' => $sender_name,
					'message'     => $message,
					'status'      => $update['status'] ?? $ticket->status,
					'reopened'    => $reopened,
					'created_at'  => date('Y-m-d H:i:s'),
				]);
			}

			return [
				'success' => true,
				'message' => $reopened
					? 'উত্তর যোগ হয়েছে এবং টিকিটটি আবার "নতুন" অবস্থায় ফিরেছে।'
					: 'উত্তর যোগ হয়েছে।',
				'reply'   => [
					'reply_id'    => $reply_id,
					'sender_role' => 'staff',
					'sender_name' => $sender_name,
					'message'     => $message,
					'created_at'  => date('d M Y, h:i A'),
				],
				'audit'   => [
					'entity_id' => (string) $ticket->ticket_id,
					'tenant_id' => $ticket->tenant_id,
					'summary'   => 'টিকিট ' . $ticket->ticket_no . ' — উত্তর যোগ'
						. ($reopened ? ' (স্ট্যাটাস আবার open)' : ''),
					'after'     => ['reply' => mb_substr($message, 0, 200), 'status' => $update['status'] ?? $ticket->status],
				],
			];
		}, 'send', 'support_ticket');
	}

	/**
	 * API: change status, priority or assignee.
	 *
	 * Three columns, one endpoint, because they are three edits to the
	 * same row that a support agent changes together ("mark urgent and
	 * assign to me"), and three endpoints would mean three audit rows for
	 * one action and three round trips for what is one click.
	 *
	 * closed_at is derived, never sent. The client cannot stamp a
	 * resolution date in the past, which is exactly the sort of field that
	 * ends up with a 2019 close date on a ticket filed yesterday.
	 */
	public function api_set_status()
	{
		$input = sadmin_api_input();
		$ticket_id = (int) ($input['ticket_id'] ?? 0);

		if ($ticket_id <= 0) {
			sadmin_fail('টিকিট নির্বাচন করা হয়নি।');
		}

		$ticket = $this->db->where('ticket_id', $ticket_id)->get('support_tickets')->row();
		if (!$ticket) {
			sadmin_fail('টিকিট পাওয়া যায়নি।', 404);
		}

		$update = ['updated_at' => date('Y-m-d H:i:s')];
		$changed = [];

		if (isset($input['status'])) {
			$status = (string) $input['status'];
			if (!in_array($status, ['open', 'pending', 'resolved', 'closed'], TRUE)) {
				sadmin_fail('অবস্থাটি বোঝা যায়নি।');
			}
			if ($status !== $ticket->status) {
				$update['status'] = $status;
				$changed['status'] = $ticket->status . ' → ' . $status;

				/*
				 * Terminal state stamps closed_at; leaving it clears it.
				 * Without the clear, a reopened ticket keeps an old close
				 * date and every "time to resolve" figure computed from
				 * it is nonsense.
				 */
				if ($status === 'resolved' || $status === 'closed') {
					$update['closed_at'] = date('Y-m-d H:i:s');
				} else {
					$update['closed_at'] = NULL;
				}
			}
		}

		if (isset($input['priority'])) {
			$priority = (string) $input['priority'];
			if (!in_array($priority, ['low', 'normal', 'high', 'urgent'], TRUE)) {
				sadmin_fail('অগ্রাধিকার বোঝা যায়নি।');
			}
			if ($priority !== $ticket->priority) {
				$update['priority'] = $priority;
				$changed['priority'] = $ticket->priority . ' → ' . $priority;
			}
		}

		/*
		 * Assignment, with an explicit "unassign" path. assigned_to = 0
		 * is read as "clear it" rather than as a user id, because there
		 * is no id 0 and because a select whose first option is a real
		 * user is otherwise impossible to return to unassigned from.
		 */
		if (array_key_exists('assigned_to', $input)) {
			$assigned = (int) $input['assigned_to'];
			if ($assigned === 0) {
				if ($ticket->assigned_to !== NULL) {
					$update['assigned_to'] = NULL;
					$changed['assigned_to'] = (string) $ticket->assigned_to . ' → (none)';
				}
			} else {
				$is_staff = $this->db
					->select('u.id')
					->from('users u')
					->join('users_groups ug', 'ug.user_id = u.id')
					->join('groups g', 'g.id = ug.group_id')
					->where('u.id', $assigned)
					->group_start()
					->where('g.name', 'sadmin')
					->or_where('g.name', 'admin')
					->group_end()
					->limit(1)
					->get()
					->num_rows() > 0;
				if (!$is_staff) {
					sadmin_fail('এই ব্যক্তি স্টাফ নন — টিকিট শুধু স্টাফকে দেওয়া যায়।');
				}
				if ((int) $ticket->assigned_to !== $assigned) {
					$update['assigned_to'] = $assigned;
					$changed['assigned_to'] = (string) ($ticket->assigned_to ?: '—') . ' → ' . $assigned;
				}
			}
		}

		if (!$changed) {
			sadmin_json(['success' => TRUE, 'message' => 'কিছুই বদলায়নি।']);
		}

		sadmin_write(function () use ($ticket, $update, $changed) {
			$this->db->where('ticket_id', $ticket->ticket_id)->update('support_tickets', $update);

			return [
				'success' => true,
				'message' => 'টিকিট ' . $ticket->ticket_no . ' আপডেট হয়েছে।',
				'audit'   => [
					'entity_id' => (string) $ticket->ticket_id,
					'tenant_id' => $ticket->tenant_id,
					'summary'   => 'টিকিট ' . $ticket->ticket_no . ' — ' . implode(', ', $changed),
					'before'    => array_intersect_key((array) $ticket, ['status' => 1, 'priority' => 1, 'assigned_to' => 1]),
					'after'     => array_intersect_key($update, ['status' => 1, 'priority' => 1, 'assigned_to' => 1]),
				],
			];
		}, 'update', 'support_ticket');
	}

	/**
	 * API: delete a ticket.
	 *
	 * It exists for spam and for tickets raised by mistake, and those are
	 * the only reasons it should ever be used. The reply thread goes with
	 * it — support_replies.ticket_id is ON DELETE CASCADE, so there is no
	 * way to keep the replies, and pretending otherwise by archiving would
	 * be a second table with no way to find things in it.
	 *
	 * Because the cascade takes rows nobody can get back, the endpoint
	 * refuses outright when the ticket has any replies and asks the
	 * operator to close it instead. Deleting a ticket with a conversation
	 * in it is never "cleaning up spam".
	 */
	public function api_delete_ticket()
	{
		$input = sadmin_api_input();
		$ticket_id = (int) ($input['ticket_id'] ?? 0);

		if ($ticket_id <= 0) {
			sadmin_fail('টিকিট নির্বাচন করা হয়নি।');
		}

		$ticket = $this->db->where('ticket_id', $ticket_id)->get('support_tickets')->row();
		if (!$ticket) {
			sadmin_fail('টিকিট পাওয়া যায়নি।', 404);
		}

		$reply_count = (int) $this->db->where('ticket_id', $ticket_id)
			->count_all_results('support_replies');
		if ($reply_count > 0) {
			sadmin_fail(
				'এই টিকিটে ' . $reply_count . 'টি উত্তর আছে। উত্তরসহ টিকিট মোছা যায় না — '
				. 'বদলে স্ট্যাটাস "বন্ধ" করে দিন।',
				409
			);
		}

		sadmin_write(function () use ($ticket) {
			$this->db->where('ticket_id', $ticket->ticket_id)->delete('support_tickets');

			return [
				'success' => true,
				'message' => 'টিকিট ' . $ticket->ticket_no . ' মোছা হয়েছে।',
				'audit'   => [
					'entity_id' => (string) $ticket->ticket_id,
					'tenant_id' => $ticket->tenant_id,
					'summary'   => 'টিকিট মোছা হয়েছে: ' . $ticket->ticket_no . ' — ' . $ticket->subject,
					'before'    => [
						'subject'  => $ticket->subject,
						'status'   => $ticket->status,
						'priority' => $ticket->priority,
						'message'  => mb_substr((string) $ticket->message, 0, 400),
					],
				],
			];
		}, 'delete', 'support_ticket');
	}
}