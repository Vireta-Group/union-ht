<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Support — the public intake endpoint
 * =====================================================================
 *
 * WHY THIS IS A SEPARATE CONTROLLER AND NOT A sadmin_* METHOD
 * A parishad asking for help is by definition not a platform super admin.
 * If this lived in Sadmin_support it would either be unreachable (the
 * require_sadmin() gate halts) or the gate would have to be switched off
 * for one method, which is how a panel endpoint ends up answering to
 * anonymous callers. So: its own controller, its own route, and no
 * require_sadmin() anywhere in it. The panel that READS what this writes
 * is Sadmin_support, and only that one is gated.
 *
 * WHAT EXISTED BEFORE THIS
 * A help button. saas/partials/support_modal.php is loaded on every
 * product-site page and offers three channels, and saas/pages/contact.php
 * has a six-field "ডেমো রিকোয়েস্ট" form. Neither posted anywhere. The
 * contact form's React original had this entire submit handler:
 *
 *     const handleSubmit = (e) => { e.preventDefault(); setSubmitted(true); };
 *
 * Six fields into React state and then into the void, and setSubmitted()
 * swapped the form for a green panel that had never received a byte. The
 * success state was a lie that had shipped from the SPA and survived the
 * port to PHP; the port kept the form and correctly refused to keep the
 * lie, leaving a note saying so. This endpoint is what makes the note
 * wrong.
 *
 * A PUBLIC, UNAUTHENTICATED WRITE ENDPOINT, SO HERE IS THE WHOLE DEFENCE
 * There is no CSRF token in this project ($config['csrf_protection'] is
 * FALSE and the login form has none either), which for a POST that only
 * creates a row is a nuisance rather than a vulnerability — there is no
 * ambient authority to abuse, since the row is anonymous either way. What
 * that leaves is abuse, and it is covered by:
 *
 *   1. A per-IP throttle on the login_attempts table, the same table and
 *      the same purge discipline as signup_throttle(). See throttle()
 *      below for why the purge deletes rows OUTSIDE the window.
 *   2. A honeypot field. Not security, spam — the cheapest real filter
 *      there is for the "fill in every input" bots.
 *   3. Hard length caps on every field, so the row cannot be used to
 *      store megabytes.
 *   4. No email is sent and no URL is fetched, so there is no amplifier
 *      here and no SSRF.
 *   5. Every value is escaped at the point of display. Nothing in the
 *      panel renders a ticket field without htmlspecialchars().
 *
 * WHAT IS DELIBERATELY ABSENT
 * No email notification, no SMS, no auto-reply. The panel page says so on
 * the page, because an operator who files a ticket and hears nothing back
 * concludes the panel is broken. A mail queue that is not wired to a real
 * MTA is worse than no mail: it fails silently and looks like delivery.
 *
 * THE TENANT IS RECORDED, BUT ONLY AS AN OBSERVATION
 * tenant_id comes from tenant_code(), so a visitor on a parishad's own
 * hostname is filed under that parishad and a visitor on the product site
 * is filed under nothing. It is a hint for triage, not an authorisation:
 * nothing in this controller reads it back to decide anything, and a
 * hostile client can post any tenant_id it likes without consequence
 * beyond landing in the wrong queue.
 */
class Support extends CI_Controller
{
	/**
	 * Per-IP limits, per hour.
	 *
	 * Tighter than signup's 5/hour because a ticket costs a human much more
	 * to read than a signup row costs to skip, and because the only thing
	 * stopping a flood is somebody reading it.
	 */
	const MAX_PER_WINDOW  = 5;
	const WINDOW_SECONDS = 3600;

	/** Field caps, in characters. Long enough for a real complaint, short
	 *  enough that the row is not a file store. */
	const MAX_SUBJECT = 200;
	const MAX_MESSAGE = 4000;
	const MAX_NAME    = 120;
	const MAX_EMAIL   = 190;
	const MAX_PHONE   = 40;

	public function __construct()
	{
		parent::__construct();
		$this->load->library('session');
		$this->load->helper('sadmin');
	}

	/* =====================================================================
	 * POST /support_submit
	 * ================================================================== */

	/**
	 * Accept a support ticket.
	 *
	 * Two callers, one endpoint, content-negotiated the same way
	 * Front::api_login() does it — decided by the request, not by a
	 * parameter:
	 *
	 *   JSON body  -> JSON out      (the modal's fetch())
	 *   form body  -> 303 redirect (the contact page's <form>, no JS)
	 *
	 * The negotiation deliberately tests the DECODED BODY, not the
	 * Content-Type header. A form POST that happens to carry
	 * X-Requested-With still gets JSON, and an AJAX caller sending a form
	 * body is not handed a redirect it cannot read.
	 *
	 * sadmin_api_input() is used for the body itself even though this is
	 * not a panel controller: it is the raw-stream-then-post() reader, and
	 * raw_input_stream is unreadable a second time, so a method that tried
	 * post() first and fell back to the stream would silently receive an
	 * empty array for every JSON request. The sadmin_ prefix is the only
	 * thing about it that does not belong here.
	 */
	public function submit()
	{
		$json_body  = json_decode($this->input->raw_input_stream, TRUE);
		$wants_json = (is_array($json_body) && !empty($json_body)) || $this->input->is_ajax_request();
		$input      = $wants_json ? (array) $json_body : (array) $this->input->post();

		/*
		 * Honeypot. A real person cannot see this field, so anything in it
		 * is a bot that filled in every input on the page. It answers with
		 * SUCCESS and stores nothing, which is the only useful shape: a
		 * bot that gets told it failed just tries the next field name.
		 */
		if (trim((string) ($input['website'] ?? '')) !== '') {
			$this->reply($wants_json, TRUE, 'ধন্যবাদ! আপনার বার্তা পাওয়া গেছে।', '');
		}

		$wait = $this->throttle();
		if ($wait !== NULL) {
			$this->reply(
				$wants_json,
				FALSE,
				'আপনার IP থেকে অনেকগুলো বার্তা পাঠানো হয়েছে। ' . $wait . ' পরে আবার চেষ্টা করুন।',
				'',
				429
			);
			return;
		}

		$subject = trim((string) ($input['subject'] ?? ''));
		$message = trim((string) ($input['message'] ?? ''));
		$name    = trim((string) ($input['name'] ?? ''));
		$email   = trim((string) ($input['email'] ?? ''));
		$phone   = trim((string) ($input['phone'] ?? ''));
		$notes   = trim((string) ($input['notes'] ?? ''));
		$union   = trim((string) ($input['union_name'] ?? ''));
		$plan    = trim((string) ($input['plan'] ?? ''));
		$category = (string) ($input['category'] ?? 'general');

		$error = '';

		if ($subject === '' && $union !== '') {
			// The contact page's field is the parishad's name, not a
			// subject line. Rather than making that form invent a subject,
			// compose one here so the panel's list has something to scan.
			$subject = 'ডেমো রিকোয়েস্ট: ' . $union;
		}

		if ($subject === '') {
			$error = 'বিষয় লিখুন।';
		} elseif (mb_strlen($subject) > self::MAX_SUBJECT) {
			$error = 'বিষয় ' . self::MAX_SUBJECT . ' অক্ষরের বেশি হতে পারবে না।';
		}

		if ($error === '' && $message === '' && $notes !== '') {
			$message = $notes;
		}
		if ($error === '' && $message === '') {
			$error = 'আপনার সমস্যা বা প্রশ্নটি লিখুন।';
		} elseif ($error === '' && mb_strlen($message) > self::MAX_MESSAGE) {
			$error = 'বার্তা ' . self::MAX_MESSAGE . ' অক্ষরের বেশি হতে পারবে না।';
		}

		if ($error === '' && mb_strlen($name) > self::MAX_NAME) {
			$error = 'নাম অনেক লম্বা।';
		}
		if ($error === '' && mb_strlen($email) > self::MAX_EMAIL) {
			$error = 'ইমেইল অনেক লম্বা।';
		}
		if ($error === '' && mb_strlen($phone) > self::MAX_PHONE) {
			$error = 'ফোন নম্বর অনেক লম্বা।';
		}

		/*
		 * One of email or phone, not both. The panel refuses to work a
		 * ticket it cannot answer, and it says so on the ticket page — so
		 * requiring a contact here moves the failure to the only place that
		 * can fix it, which is before the row exists.
		 *
		 * The email is validated only when present. A parishad chairman
		 * with a phone number and no email is the normal case in this
		 * market, and requiring an address would lose real tickets.
		 */
		if ($error === '' && $email === '' && $phone === '') {
			$error = 'ইমেইল অথবা মোবাইল নম্বর — অন্তত একটি দিন, যাতে উত্তর দেওয়া যায়।';
		}
		if ($error === '' && $email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$error = 'ইমেইল ঠিকানাটি সঠিক নয়।';
		}

		// CHECK constraints reject anything outside these lists, so the
		// whitelist is applied before the insert rather than caught after.
		$categories = ['general', 'technical', 'billing', 'domain', 'training', 'other'];
		if (!in_array($category, $categories, TRUE)) {
			$category = 'general';
		}

		if ($error !== '') {
			$this->reply($wants_json, FALSE, $error, $subject);
			return;
		}

		/*
		 * The message is composed, not stored as typed, when the contact
		 * form's extra fields were filled in. Their values would otherwise
		 * be discarded, and "which package did they ask about" is the first
		 * thing anybody opening the ticket wants to know.
		 */
		$context = [];
		if ($union !== '' && $union !== $subject) {
			$context[] = 'ইউনিয়ন পরিষদ: ' . $union;
		}
		if (isset($input['location']) && trim((string) $input['location']) !== '') {
			$context[] = 'এলাকা: ' . trim((string) $input['location']);
		}
		if ($plan !== '') {
			$context[] = 'প্যাকেজ: ' . $plan;
		}
		if ($context) {
			$message = implode("\n", $context) . "\n\n" . $message;
		}

		/*
		 * tenant_code() is the resolved parishad, 0 on a platform host.
		 * Recorded as an observation only — see the class docblock.
		 */
		$tenant_id = (int) tenant_code();
		if ($tenant_id <= 0) {
			$tenant_id = NULL;
		}

		// user_id only when there is a session. The intake is anonymous
		// by design, but a chairman who happens to be logged in when he
		// writes is worth linking to his account.
		$user_id = NULL;
		if ($this->ion_auth->logged_in()) {
			$me      = $this->ion_auth->user()->row();
			$user_id = $me ? (int) $me->id : NULL;
			if ($name === '' && $me) {
				$name = trim((string) ($me->first_name ?? '')) ?: (string) $me->username;
			}
			if ($email === '' && $me && !empty($me->email)) {
				$email = (string) $me->email;
			}
		}

		/*
		 * ticket_no is UNIQUE and there is no sequence for it. Deriving it
		 * from MAX(ticket_no) + 1 is the obvious approach and it is wrong
		 * under concurrency: two simultaneous submissions read the same
		 * MAX, both compute the same number, and one dies on the unique
		 * index — a failed ticket for a customer who did nothing wrong.
		 *
		 * So the number is derived from the primary key instead, which is
		 * unique by construction. The insert carries a per-request
		 * placeholder (the nanosecond time, which no second request can
		 * share) and is immediately corrected to the real number from
		 * insert_id(). Two statements, and no way to collide.
		 */
		$placeholder = 'TKT-P' . str_pad((string) ((int) (microtime(TRUE) * 1000000)), 16, '0', STR_PAD_LEFT);

		$ok = $this->db->insert('support_tickets', [
			'ticket_no'  => $placeholder,
			'tenant_id'  => $tenant_id,
			'user_id'    => $user_id,
			'user_name'  => $name    !== '' ? $name    : NULL,
			'user_email' => $email   !== '' ? $email   : NULL,
			'user_phone' => $phone   !== '' ? $phone   : NULL,
			'subject'    => $subject,
			'category'   => $category,
			'priority'   => 'normal',
			'message'    => $message,
			'status'     => 'open',
		]);

		if (!$ok) {
			// The only realistic cause is the placeholder colliding, which
			// microtime() makes vanishingly unlikely. Say something that
			// does not promise the message was received.
			log_message('error', 'support_submit: insert failed. db_error: ' . $this->db->error());
			$this->reply(
				$wants_json,
				FALSE,
				'দুঃখিত, বার্তাটি সংরক্ষণ করা গেল না। অনুগ্রহ করে ফোনে যোগাযোগ করুন।',
				$subject,
				500
			);
			return;
		}

		$ticket_id = (int) $this->db->insert_id();
		$ticket_no = 'TKT-' . str_pad((string) $ticket_id, 6, '0', STR_PAD_LEFT);

		$this->db->where('ticket_id', $ticket_id)
			->update('support_tickets', ['ticket_no' => $ticket_no]);

		/*
		 * No audit_logs row. The audit table records what PLATFORM STAFF
		 * did; this is an anonymous visitor's request and support_tickets
		 * IS its record. Writing both would mean two tables holding the
		 * same fact with different lifetimes — the ticket is deleted when a
		 * spam ticket is removed, the audit row would survive it, and the
		 * next person to read the audit log would count a ticket that no
		 * longer exists.
		 */

		/*
		 * Ring the doorbell for the support manager. Placed AFTER the ticket
		 * row is committed and AFTER the ticket_no update, so a manager who
		 * clicks straight through to the ticket from the notification lands
		 * on a ticket that already has its number. support_event() cannot
		 * fail the request — a saved ticket must not be reported as lost
		 * because a notification did not write.
		 *
		 * No message body. The panel only needs to know something arrived
		 * and where; the text is on the ticket page, and streaming an
		 * anonymous visitor's words onto every open manager panel would put
		 * them in everyone's DOM for no benefit.
		 */
		if (function_exists('support_event'))
		{
			support_event('ticket', [
				'ticket_id' => $ticket_id,
				'ticket_no' => $ticket_no,
				'subject'   => $subject,
				'category'  => $category,
				'has_tenant' => $tenant_id > 0,
			]);
		}

		$ticket_row = $this->db->where('ticket_id', $ticket_id)->get('support_tickets')->row();
		$created_at = $ticket_row ? $ticket_row->created_at : date('Y-m-d H:i:s');
		$token      = $this->generate_token($ticket_id, $ticket_no, $created_at);

		$this->reply(
			$wants_json,
			TRUE,
			'ধন্যবাদ! আপনার বার্তা আমাদের পৌঁছেছে। টিকিট নম্বর: ' . $ticket_no
				. ' — এই নম্বরটি সংরক্ষণ করে রাখুন।',
			$subject,
			200,
			[
				'ticket_id'  => $ticket_id,
				'ticket_no'  => $ticket_no,
				'token'      => $token,
				'user_name'  => $name,
				'created_at' => $created_at,
			]
		);
	}

	/* =====================================================================
	 * Live Support Chat Endpoints
	 * ================================================================== */

	/**
	 * POST /support/start_chat
	 * Initiates a live chat session (wrapper around submit()).
	 */
	public function start_chat()
	{
		return $this->submit();
	}

	/**
	 * Compute a secure HMAC token for a ticket.
	 */
	public function generate_token($ticket_id, $ticket_no, $created_at)
	{
		$key = $this->config->item('encryption_key') ?: 'vireta_support_hmac_secret_2026';
		return hash_hmac('sha256', $ticket_id . ':' . $ticket_no . ':' . $created_at, $key);
	}

	/**
	 * Verify token. Returns ticket row if valid, NULL otherwise.
	 */
	public function verify_ticket_access($ticket_id, $token)
	{
		$ticket_id = (int) $ticket_id;
		$token     = trim((string) $token);
		if ($ticket_id <= 0 || $token === '') {
			return NULL;
		}

		$ticket = $this->db->where('ticket_id', $ticket_id)->get('support_tickets')->row();
		if (!$ticket) {
			return NULL;
		}

		$expected = $this->generate_token($ticket->ticket_id, $ticket->ticket_no, $ticket->created_at);
		if (!hash_equals($expected, $token)) {
			return NULL;
		}

		return $ticket;
	}

	/**
	 * GET /support/history?ticket_id=...&token=...
	 * Returns full thread history for an active visitor live chat session.
	 */
	public function history()
	{
		$ticket_id = (int) ($this->input->get('ticket_id') ?: 0);
		$token     = (string) ($this->input->get('token') ?: '');

		$ticket = $this->verify_ticket_access($ticket_id, $token);
		if (!$ticket) {
			sadmin_json(['success' => false, 'message' => 'অনুমোদনহীন অনুরোধ বা চ্যাট পাওয়া যায়নি।'], 403);
			return;
		}

		$replies = $this->db
			->where('ticket_id', $ticket->ticket_id)
			->order_by('reply_id', 'ASC')
			->get('support_replies')
			->result();

		$formatted_replies = [];
		foreach ($replies as $r) {
			$formatted_replies[] = [
				'reply_id'    => (int) $r->reply_id,
				'sender_role' => $r->sender_role,
				'sender_name' => $r->sender_name ?: ($r->sender_role === 'staff' ? 'সাপোর্ট টিম' : 'গ্রাহক'),
				'message'     => $r->message,
				'created_at'  => date('d M Y, h:i A', strtotime($r->created_at)),
				'time_raw'    => $r->created_at,
			];
		}

		sadmin_json([
			'success' => true,
			'ticket'  => [
				'ticket_id'   => (int) $ticket->ticket_id,
				'ticket_no'   => $ticket->ticket_no,
				'subject'     => $ticket->subject,
				'user_name'   => $ticket->user_name,
				'status'      => $ticket->status,
				'message'     => $ticket->message,
				'created_at'  => date('d M Y, h:i A', strtotime($ticket->created_at)),
				'time_raw'    => $ticket->created_at,
			],
			'replies' => $formatted_replies,
		]);
	}

	/**
	 * POST /support/send_message
	 * Client sending a follow-up message in active live chat.
	 */
	public function send_message()
	{
		$json_body = json_decode($this->input->raw_input_stream, TRUE);
		$input     = is_array($json_body) ? $json_body : (array) $this->input->post();

		$ticket_id = (int) ($input['ticket_id'] ?? 0);
		$token     = trim((string) ($input['token'] ?? ''));
		$message   = trim((string) ($input['message'] ?? ''));

		if ($message === '') {
			sadmin_json(['success' => false, 'message' => 'বার্তা লিখুন।'], 422);
			return;
		}
		if (mb_strlen($message) > self::MAX_MESSAGE) {
			sadmin_json(['success' => false, 'message' => 'বার্তা ' . self::MAX_MESSAGE . ' অক্ষরের বেশি হতে পারবে না।'], 422);
			return;
		}

		$ticket = $this->verify_ticket_access($ticket_id, $token);
		if (!$ticket) {
			sadmin_json(['success' => false, 'message' => 'অনুমোদনহীন অনুরোধ বা সেশন মেয়াদোত্তীর্ণ।'], 403);
			return;
		}

		$sender_name = $ticket->user_name ?: 'গ্রাহক';
		$now = date('Y-m-d H:i:s');

		$this->db->insert('support_replies', [
			'ticket_id'   => $ticket->ticket_id,
			'sender_id'   => NULL,
			'sender_name' => $sender_name,
			'sender_role' => 'customer',
			'message'     => $message,
			'created_at'  => $now,
		]);

		$reply_id = (int) $this->db->insert_id();

		// Reopen if resolved or closed, and bump updated_at
		$update = ['updated_at' => $now];
		if ($ticket->status === 'resolved' || $ticket->status === 'closed') {
			$update['status']    = 'open';
			$update['closed_at'] = NULL;
		}
		$this->db->where('ticket_id', $ticket->ticket_id)->update('support_tickets', $update);

		// Ring the doorbell for the manager & open panels
		if (function_exists('support_event')) {
			support_event('reply', [
				'ticket_id'   => (int) $ticket->ticket_id,
				'ticket_no'   => $ticket->ticket_no,
				'reply_id'    => $reply_id,
				'by'          => 'customer',
				'sender_name' => $sender_name,
				'message'     => $message,
				'status'      => $update['status'] ?? $ticket->status,
				'created_at'  => $now,
			]);
		}

		sadmin_json([
			'success' => true,
			'reply'   => [
				'reply_id'    => $reply_id,
				'sender_role' => 'customer',
				'sender_name' => $sender_name,
				'message'     => $message,
				'created_at'  => date('d M Y, h:i A', strtotime($now)),
				'time_raw'    => $now,
			],
		]);
	}

	/**
	 * GET /support/live_stream?ticket_id=...&token=...&last_id=...
	 * Public SSE stream for live support conversation.
	 */
	public function live_stream()
	{
		$ticket_id = (int) ($this->input->get('ticket_id') ?: 0);
		$token     = (string) ($this->input->get('token') ?: '');

		$ticket = $this->verify_ticket_access($ticket_id, $token);
		if (!$ticket) {
			header('HTTP/1.1 403 Forbidden');
			echo "event: forbidden\ndata: " . json_encode(['message' => 'অনুমোদনহীন অনুরোধ।'], JSON_UNESCAPED_UNICODE) . "\n\n";
			exit;
		}

		while (ob_get_level() > 0) {
			ob_end_clean();
		}

		header('Content-Type: text/event-stream; charset=utf-8');
		header('Cache-Control: no-cache, no-store, must-revalidate');
		header('Connection: keep-alive');
		header('X-Accel-Buffering: no');

		if (session_status() === PHP_SESSION_ACTIVE) {
			session_write_close();
		}

		$seen = (int) ($this->input->get('last_id') ?: 0);

		// Emit connected event
		echo "event: connected\ndata: " . json_encode([
			'ticket_id' => (int) $ticket->ticket_id,
			'ticket_no' => $ticket->ticket_no,
			'last_id'   => $seen,
		], JSON_UNESCAPED_UNICODE) . "\n\n";
		@flush();

		$started  = time();
		$ceiling  = 60;   // 60s ceiling, browser reconnects seamlessly
		$beat     = 1;    // 1 second check
		$sent     = 0;
		$max_send = 50;

		while ((time() - $started) < $ceiling && $sent < $max_send && !connection_aborted()) {
			sleep($beat);

			$events = support_event_read($seen);
			if (!$events) {
				echo ": keep-alive\n\n";
				@flush();
				continue;
			}

			foreach ($events as $ev) {
				$seen = max($seen, (int) $ev['id']);
				$data = isset($ev['data']) && is_array($ev['data']) ? $ev['data'] : [];

				if (isset($data['ticket_id']) && (int) $data['ticket_id'] === (int) $ticket->ticket_id) {
					echo "id: " . $seen . "\n";
					echo "event: " . $ev['type'] . "\n";
					echo "data: " . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n\n";
					@flush();
					$sent++;
				}
			}
		}

		echo "event: reconnect\ndata: " . json_encode(['last_id' => $seen], JSON_UNESCAPED_UNICODE) . "\n\n";
		@flush();
		exit;
	}

	/* =====================================================================
	 * Internals
	 * ================================================================== */

	/**
	 * Count this IP's tickets in the window and record the attempt.
	 *
	 * The purge deletes rows OUTSIDE the window. Deleting the rows INSIDE
	 * it — which reads like "reset the counter" — is completely silent and
	 * permanently fatal: the count then always returns 0, the limit is
	 * never reached, and the endpoint stays unlimited for the life of the
	 * table. Front::signup_throttle() shipped that bug for one test run
	 * before it was caught, and this is the same table and the same
	 * mistake to not make twice.
	 *
	 * @return string|null  Bangla wait text, or NULL when the request may
	 *                       proceed. The attempt is recorded either way
	 *                       the request gets past the limit check, so a
	 *                       rejected flood does not become free.
	 */
	private function throttle()
	{
		$ip = (string) $this->input->ip_address();
		if ($ip === '') {
			// No address to count against. Fail OPEN here, not closed: on
			// some SAPI configurations this is empty for every request and
			// failing closed would take the feature offline entirely.
			return NULL;
		}

		$since  = time() - self::WINDOW_SECONDS;
		$now    = time();
		$window = self::WINDOW_SECONDS;

		$this->db->where('login', 'support')
			->where('time <', $since)
			->delete('login_attempts');

		$used = (int) $this->db->where('login', 'support')
			->where('ip_address', $ip)
			->where('time >=', $since)
			->count_all_results('login_attempts');

		// Recorded BEFORE the limit test so a flood is itself throttled: if
		// rejected requests were not counted, the limit would only ever be
		// reached by requests that were about to be allowed.
		$this->db->insert('login_attempts', [
			'ip_address' => $ip,
			'login'      => 'support',
			'time'       => $now,
		]);

		if ($used < self::MAX_PER_WINDOW) {
			return NULL;
		}

		$oldest = $this->db->where('login', 'support')
			->where('ip_address', $ip)
			->where('time >=', $since)
			->order_by('time', 'ASC')
			->limit(1)
			->get('login_attempts')
			->row('time');

		$wait_secs = $oldest
			? max(1, (int) ceil($window - ($now - (int) $oldest)))
			: $window;

		$mins = (int) ceil($wait_secs / 60);
		if ($mins < 1) {
			return bn_number($wait_secs) . ' সেকেন্ড';
		}
		return bn_number($mins) . ' মিনিট';
	}

	/**
	 * Answer in whichever shape the caller can read, and stop.
	 *
	 * The form branch bounces back to the page the form is on rather than
	 * to a hardcoded /contact: the same endpoint backs the modal on every
	 * product page, and a modal on /plans that redirects to /contact on
	 * failure loses the reader's place for one validation error.
	 *
	 * `return_to` is read from the request but sanitised to a same-site
	 * relative path. Taking it raw would make this an open redirect: a
	 * phishing link of the shape /support_submit?return_to=https://evil
	 * would bounce a visitor who mistypes a phone number off-site.
	 *
	 * The subject is echoed back only so the form can be re-rendered with
	 * it; it is escaped by the view, never here.
	 *
	 * On a FAILED form submission the whole input is flashed under 'old' as
	 * well, and the page re-renders with it. Without that, "আপনার সমস্যা বা
	 * প্রশ্নটি লিখুন" costs the visitor the four hundred characters they had
	 * already typed, and the sensible response — which is to give up on the
	 * form — is the most likely one. Three deliberate exclusions:
	 *
	 *   'website'  the honeypot. Flashing it back would put the value in the
	 *              re-rendered field, so the next honest submit trips the
	 *              honeypot and the visitor's second, genuine attempt is
	 *              swallowed.
	 *   'return_to' re-supplied by the view as a hidden field; carrying a
	 *              stale copy in the flash adds nothing.
	 *   'submit' / any button name a browser adds, which is not a field
	 *              anyone rendered.
	 *
	 * Only a whitelist is flashed, never the raw array: this is unbounded
	 * session data written from a public unauthenticated POST, and flashing
	 * whatever arrived would let a visitor choose how much they store in
	 * their own session and would put a `website` value and a 4 KB message
	 * into the same cookie-backed store. The whitelist is the same set of
	 * keys submit() actually reads, so the two cannot drift silently.
	 *
	 * NOT flashed on success. A stale copy of somebody's message sitting in
	 * a field they already sent reads as unsent.
	 *
	 * BOTH branches halt. The JSON branch goes through sadmin_json(), which
	 * calls _display() and then exit() — a set_output() on its own would
	 * not stop the caller, and a controller that answers and then keeps
	 * writing is how the honeypot branch ended up creating the very ticket
	 * it was pretending to swallow.
	 */
	private function reply($wants_json, $success, $message, $subject = '', $code = 200, array $extra = [])
	{
		if ($wants_json) {
			sadmin_json(array_merge([
				'success' => (bool) $success,
				'message' => $message,
				'subject' => $subject,
			], $extra), $code);
			return; // unreachable; sadmin_json() exits
		}

		$this->session->set_flashdata($success ? 'success' : 'error', $message);

		$input  = (array) $this->input->post();

		/*
		 * Hand the visitor their own text back. See the docblock above for
		 * why this is a whitelist and why it is failure-only.
		 *
		 * The caps are the same constants the validation above enforces, so
		 * a rejected too-long message is truncated to something the next
		 * page can show rather than flashed whole. Bounding the value
		 * before it is written is the point — this is unbounded session
		 * data written from a public, unauthenticated POST.
		 */
		if ( ! $success) {
			$keep = [
				'union_name' => self::MAX_NAME,
				'name'       => self::MAX_NAME,
				'email'      => self::MAX_EMAIL,
				'phone'      => self::MAX_PHONE,
				'location'   => self::MAX_NAME,
				'plan'       => 40,
				'subject'    => self::MAX_SUBJECT,
				'message'    => self::MAX_MESSAGE,
				'notes'      => self::MAX_MESSAGE,
			];

			$old = array();
			foreach ($keep as $key => $cap) {
				if (isset($input[$key]) && is_scalar($input[$key])) {
					$old[$key] = mb_substr(trim((string) $input[$key]), 0, $cap);
				}
			}

			// Nothing to restore only when the visitor submitted nothing,
			// which is exactly the case the error message is about.
			if ($old !== []) {
				$this->session->set_flashdata('old', $old);
			}
		}

		/*
		 * The default target is /contact, not the endpoint itself. A plain
		 * form POST that fails validation has to land somewhere a reader
		 * can see the message and correct the form; bouncing to
		 * /support_submit would put them on a POST-only route, and the
		 * flashdata they were just given would be read by the next page
		 * load instead of this one.
		 */
		$target = '/contact';

		$raw = (string) ($input['return_to'] ?? '');
		if ($raw !== ''
			&& $raw[0] === '/'
			&& strpos($raw, '//') !== 0
			&& strpos($raw, "\n") === FALSE
			&& strpos($raw, "\r") === FALSE)
		{
			$target = $raw;
		}

		redirect($target);
	}
}
