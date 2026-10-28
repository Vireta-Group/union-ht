<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Support event doorbell — writer and reader, no database involved.
 * =====================================================================
 *
 * WHY THIS IS A HELPER AND NOT PART OF THE SSE CONTROLLER
 * ------------------------------------------------------
 * It has to be. The writers are Support::submit(), Sadmin_support::api_reply()
 * and Dashboard::api_reply_support_ticket() — three other controllers, none
 * of which loads the SSE controller. With the functions declared inside that
 * controller file, every caller's function_exists() guard returned FALSE,
 * the notification was silently skipped, and the feature looked switched on
 * while doing nothing. Autoloading this is the fix; the guard stays in the
 * callers only so a stripped-down deployment cannot fatal.
 *
 * WHY NOT POSTGRES LISTEN / NOTIFY
 * --------------------------------
 * Because it is an extension, and we do not know it is installed on the
 * VPS in deploy/ or on a developer's laptop. Postgres remains the source of
 * truth; this file is only a doorbell.
 *
 * WHY NOT POLLING
 * ---------------
 * Polling means a request every few seconds forever, from every open panel,
 * for a feature idle almost all the time. The file is read only while a
 * connection is actually open.
 *
 * @see controllers/Support_sse.php  the reader
 * @see controllers/Support.php     fires 'ticket'
 * @see controllers/Sadmin_support.php  fires 'reply' (staff)
 * @see controllers/Dashboard.php   fires 'reply' (tenant)
 */

// ---------------------------------------------------------------------------
// The doorbell. Writer and reader, no database involved.
// ---------------------------------------------------------------------------

if (!function_exists('support_event_file'))
{
	/**
	 * Path to the append-only event file.
	 *
	 * sys_get_temp_dir() rather than a path inside the project: this is
	 * runtime state, not source, and writing into the webroot would put a
	 * growing file somewhere a backup or a deploy might pick up.
	 */
	function support_event_file()
	{
		return rtrim(sys_get_temp_dir(), '/') . DIRECTORY_SEPARATOR . 'vireta_support_events.log';
	}
}

if (!function_exists('support_event'))
{
	/**
	 * Record that something happened. Safe to call from anywhere.
	 *
	 * Never throws and never returns false to matter: a support ticket that
	 * saved must not fail because a notification could not be written. The
	 * panel still has the ticket in its list; the manager just has to
	 * refresh, which is exactly the behaviour this feature replaces rather
	 * than one it gates.
	 *
	 * @param  string $type short machine name, e.g. 'ticket' or 'reply'
	 * @param  array  $data payload the client will receive
	 * @return bool
	 */
	function support_event($type, array $data = array())
	{
		$line = json_encode(array(
			'type' => (string) $type,
			'at'   => time(),
			'data' => $data,
		), JSON_UNESCAPED_UNICODE);

		if ($line === FALSE) {
			return FALSE;
		}

		// LOCK_EX because two requests can land on two php-fpm workers at
		// the same instant, and a partially written line would break
		// json_decode on the reader for good — a torn write is not
		// self-healing, it poisons the file until someone truncates it.
		$ok = @file_put_contents(support_event_file(), $line . "\n", FILE_APPEND | LOCK_EX);

		return $ok !== FALSE;
	}
}

if (!function_exists('support_event_read'))
{
	/**
	 * Read events newer than $after_id.
	 *
	 * Ids are derived from the line offset, not a counter, so two
	 * concurrent writers cannot be handed the same id and a reader can
	 * always resume by remembering the last one it saw. A gap in the middle
	 * (file rotated underneath us) is not an error: the reader simply moves
	 * on, and the client reloads its list on reconnect.
	 *
	 * @param  int $after_id
	 * @return array list of {id, type, data}
	 */
	function support_event_read($after_id = 0)
	{
		$path = support_event_file();
		if (!is_readable($path)) {
			return array();
		}

		$lines = @file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		if (!$lines) {
			return array();
		}

		$out  = array();
		$next = 0;
		foreach ($lines as $i => $line) {
			$next = $i + 1;
			if ($next <= $after_id) {
				continue;
			}
			$decoded = json_decode($line, TRUE);
			if (!is_array($decoded) || !isset($decoded['type'])) {
				// A torn or truncated line: skip it rather than abort the
				// whole read, so one bad line does not silence the feed.
				continue;
			}
			$out[] = array(
				'id'   => $next,
				'type' => $decoded['type'],
				'data' => isset($decoded['data']) && is_array($decoded['data']) ? $decoded['data'] : array(),
			);
		}

		return $out;
	}
}

if (!function_exists('support_event_reset'))
{
	/**
	 * Truncate the file. Test-support only — nothing in the app calls it.
	 *
	 * @return void
	 */
	function support_event_reset()
	{
		$path = support_event_file();
		if (is_file($path)) {
			@unlink($path);
		}
	}
}
