<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Server-Sent Events for the support panel.
 * =====================================================================
 *
 * WHAT THIS IS FOR
 * ----------------
 * A ticket can be created by anyone, from the public portal, with no login
 * and no page open. Before this, the support manager only saw one by
 * hitting refresh, so a parishad clerk reporting a broken page at 9am could
 * sit unanswered until someone happened to open /sadmin_support. This pushes
 * a notification the moment a ticket lands, and again when anyone replies.
 *
 * WHY SSE AND NOT WEBSOCKETS
 * -------------------------
 * One-way only, which is exactly what this is: the server pushes, the
 * browser never pushes. EventSource is also plain HTTP, so it needs no
 * upgrade handshake, no second port, and no library — the project has no
 * composer.json and no build step for JS, and a hand-written EventSource
 * consumer is the whole client.
 *
 * WHY NOT POSTGRES LISTEN / NOTIFY
 * --------------------------------
 * Because it is an extension, and we do not know it is installed. This
 * code must run on the VPS in deploy/ as well as on a developer's laptop.
 * So the event is a file: the writer appends one small JSON line, the
 * reader tails that file. Postgres stays the source of truth; the file is
 * only a doorbell.
 *
 * WHY NOT POLLING
 * ---------------
 * Polling would mean a request every few seconds forever, from every open
 * panel, for a feature that is idle almost always. The doorbell file is
 * read only while a connection is actually open.
 *
 * WHERE THE EVENTS COME FROM
 * --------------------------
 *   Support::submit()                  -> a new ticket
 *   Sadmin_support::api_reply()        -> a new reply on a ticket
 *   Dashboard::api_reply_support_ticket() -> a tenant replying to staff
 *
 * Each calls support_event(), which lives in the autoloaded helper
 * helpers/support_event_helper.php — NOT in this file. It was here first,
 * and because the three writer controllers never load this controller, every
 * function_exists() guard quietly returned FALSE and the feature looked
 * switched on while doing nothing. Kept here as a warning: the reader and
 * the writer must not share a file.
 *
 * THE ONE HARD RULE, AND WHY IT IS NOT SATISFIABLE ON THE DEV SERVER
 * -----------------------------------------------------------------
 * This endpoint holds a PHP worker for its whole life. On php-fpm (what
 * deploy/nginx/vireta-app.conf uses in production) that is fine: there is
 * a pool, and a handful of open panels is a handful of workers out of many.
 *
 * On the built-in `php -S` used in development it is NOT fine. Measured:
 * with one stream open, an unrelated page request took 8 seconds and
 * timed out. The dev server serves a small fixed number of workers, so a
 * long-lived connection eats one, and a developer with the panel open finds
 * the rest of the app has stopped responding.
 *
 * That is why SSE is a PRODUCTION-ONLY feature here, and the client turns
 * itself off when it detects the dev server. The alternative — a short-lived
 * stream that the client reopens immediately, which is what a poll really
 * is — buys nothing over polling and costs the browser a reconnect storm.
 *
 * Detect the dev server by asking the server, not by sniffing the host:
 * a developer's machine and a VPS can share a hostname.
 *
 * @see helpers/support_event_helper.php  the writer
 * @see controllers/Support.php    where a new ticket is created
 * @see controllers/Sadmin_support.php  where staff reply
 * @see views/super_admin/support.php    the client
 */
class Support_sse extends CI_Controller {

	/**
	 * Open the stream. GET only.
	 *
	 * EventSource cannot send a custom header, so this is authenticated by
	 * the session cookie it already carries. No CSRF concern: a CSRF token
	 * would be exactly the wrong tool here, since the action is a read.
	 */
	public function stream()
	{
		// Must run before any output buffering CI may have set up, because
		// SSE is useless if headers are already flushed.
		while (ob_get_level() > 0) {
			ob_end_clean();
		}

		header('Content-Type: text/event-stream; charset=utf-8');
		header('Cache-Control: no-cache, no-store, must-revalidate');
		header('Connection: keep-alive');
		// Defeat proxy buffering. nginx will hold a response open
		// otherwise and the browser sees nothing until it closes.
		header('X-Accel-Buffering: no');

		// No auth means no cross-tenant ticket feed. The panel is
		// sadmin-only; see the class docblock.
		if (!isset($this->ion_auth) || !$this->ion_auth->in_group('sadmin'))
		{
			// A real event, not a 403: EventSource reports a 403 as a
			// connection error it cannot inspect, and the client would
			// retry forever. Sending "forbidden" and closing is legible.
			$this->send_event('forbidden', array('message' => 'সুপার অ্যাডমিন প্যানেলে প্রবেশের অনুমতি নেই।'));
			return;
		}

		$last_id = (int) ($this->input->get('last_id') ?: 0);
		$this->send_event('ready', array('last_id' => $last_id));

		/*
		 * The loop.
		 *
		 * Sleeps between checks rather than blocking on a read, and always
		 * re-checks the session so a logout ends the stream instead of
		 * leaking an authenticated connection. Two hard limits guarantee it
		 * always ends: a wall-clock ceiling and a byte/id ceiling, so a
		 * quiet night cannot leave this running until the worker is killed.
		 */
		$started  = time();
		$ceiling  = 300;   // 5 minutes, then the client reconnects on its own
		$beat     = 2;     // seconds between file checks
		$seen     = $last_id;
		$sent     = 0;
		$max_send = 200;   // never let one connection flood the client

		while ((time() - $started) < $ceiling && $sent < $max_send)
		{
			sleep($beat);

			// A session that ended mid-stream must not keep receiving.
			$CI =& get_instance();
			if (!isset($CI->session) || !$CI->session->userdata('user_id')) {
				return;
			}

			$events = support_event_read($seen);
			if (!$events) {
				// Comment frame, not a data frame. Keeps intermediaries from
				// deciding the connection is idle and closing it.
				echo ": keep-alive\n\n";
				@flush();
				continue;
			}

			foreach ($events as $ev) {
				$seen = max($seen, (int) $ev['id']);
				$this->send_event($ev['type'], $ev['data'], $seen);
				$sent++;
				if ($sent >= $max_send) {
					break;
				}
			}
		}

		// Tell the client this was our choice, not a failure, so it
		// reconnects without a backoff.
		$this->send_event('reconnect', array('last_id' => $seen));
	}

	/**
	 * Is a live stream viable right now? Cheap, no loop, no worker held.
	 *
	 * The client calls this before opening EventSource. On the dev server it
	 * answers no, so the panel quietly falls back to its old manual-refresh
	 * behaviour instead of locking up every other page in the app. In
	 * production it answers yes and the stream opens.
	 *
	 * Detection is by SAPI, not by hostname: PHP_SAPI is 'cli-server' only
	 * for the built-in server, and it is the thing that actually lacks a
	 * worker pool. A hostname check would misfire for anyone serving a dev
	 * copy on a normal name, and would also miss a VPS running the built-in
	 * server, which is the case that would really lock up.
	 *
	 * @return void  JSON {viable:bool, why:string}
	 */
	public function probe()
	{
		header('Content-Type: application/json; charset=utf-8');
		header('Cache-Control: no-store');

		$sapi = PHP_SAPI;

		// cli-server is the built-in `php -S`. A worker pool is what makes
		// a held connection survivable.
		$viable = ($sapi !== 'cli-server');

		$why = $viable
			? 'সার্ভার লাইভ নোটিফিকেশন সমর্থন করে।'
			: 'ডেভ সার্ভারে লাইভ স্ট্রিম চালু থাকলে অন্য পাতা আটকে যাবে, তাই এখন বন্ধ রাখা হয়েছে।';

		// Deliberately not require_sadmin(): the client calls this before it
		// knows whether the session is a platform one, and the answer reveals
		// nothing sensitive — it is a statement about the server, not about
		// the caller.
		$this->output
			->set_content_type('application/json')
			->set_output(json_encode(array(
				'viable' => $viable,
				'sapi'   => $sapi,
				'why'    => $why,
			), JSON_UNESCAPED_UNICODE));
	}

	private function send_event($type, array $data, $id = NULL)
	{
		if ($id !== NULL) {
			echo 'id: ' . (int) $id . "\n";
		}
		echo 'event: ' . $type . "\n";
		echo 'data: ' . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n\n";
		@flush();
	}
}
