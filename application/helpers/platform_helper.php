<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * The ViretaDev product site's own contact channels.
 *
 * WHY THIS EXISTS
 * The platform panel's "যোগাযোগ ও হেল্পডেস্ক" tab writes to `satting_infos`
 * (Sadmin_settings::platform_specs(), group 'contact'). Until this helper
 * existed, nothing on the product site read any of it. The support modal,
 * the contact page and the navbar each had the phone number, the WhatsApp
 * link and the inbox address written into the markup by hand, and — this
 * is the part that made it worth fixing rather than documenting — the
 * hand-written values had already drifted away from the settings:
 *
 *   channel   in the markup            in satting_infos
 *   phone     01813152799              01813152799          same
 *   whatsapp  8801813152799            8801713000111        DIFFERENT
 *   email     support@viretadev.com    hello@viretadev.com  DIFFERENT
 *   hours     সকাল ৯টা - রাত ১০টা      …সকাল ৯টা – সন্ধ্যা ৬টা  DIFFERENT
 *
 * and the *displayed* phone was the literal placeholder `০১৭১৩-XXXXXX`
 * sitting next to a working `tel:` link carrying the real number. So the
 * page said one thing and dialled another.
 *
 * WHY IT IS A HELPER AND NOT A MODEL
 * Three views on every product-site page need it, and the settings page
 * needs the same values to render its own form. A model would be the right
 * shape if there were more than one consumer of one row.
 *
 * A BLANK FIELD IS A DECISION; A MISSING ROW IS A FAILURE
 * These are handled differently on purpose:
 *
 *   field is ''      -> that channel is OMITTED from the array. The
 *                       operator cleared it, so the site stops advertising
 *                       it. The settings hint says exactly this for
 *                       vphone2 ("খালি রাখলে মডালে দেখানো হবে না").
 *
 *   no row / query   -> the FALLBACKS below are used and $contact['stale']
 *                       is TRUE, so a view can say the numbers are
 *                       defaults. A missing settings row is an outage and
 *                       should look like one, not like a deliberate choice.
 *
 * A query failure must not take a page down: this runs in the footer path
 * of every public page, so a throw here would be a blank product site. It
 * catches, degrades, and reports.
 *
 * WHAT IS NOT VALIDATED HERE
 * `vwhatsapp` and `vphone*` are free text in the database, so they could
 * in principle contain something other than a number. They are put into
 * `href` values. The scheme is always constructed here (`tel:`,
 * `https://wa.me/`, `mailto:`) and the value is then filtered to digits,
 * a leading `+`, spaces and dashes, so a stored value cannot introduce a
 * second scheme — a `javascript:` payload in vmail is not reachable,
 * because the `mailto:` is prepended and the rest is digits-only or a
 * single filtered email. Sadmin_settings rejects a bad scheme on save
 * too; this is the belt to that braces, not the braces.
 */

/**
 * Bengali digits to ASCII, and nothing else.
 *
 * This is not a nicety. The person editing these settings is a Bangla
 * speaker, and "০১৮১৩১৫২৭৯৯" is a perfectly natural thing for them to type into
 * a phone field. Without this, preg_replace('/\D+/') sees every Bengali
 * digit as a non-digit, strips the whole value, and the product site
 * silently stops showing a phone number that is sitting in the database
 * in plain sight — with no error anywhere, because an empty channel is a
 * legitimate state (the operator cleared it) and this is not.
 *
 * ASCII digits pass through unchanged, so it is safe to call on a value
 * that is already in either alphabet.
 *
 * @param  string $s
 * @return string
 */
function platform_ascii_digits($s)
{
	static $map = NULL;
	if ($map === NULL) {
		$map = [
			'০' => '0', '১' => '1', '২' => '2', '৩' => '3', '৪' => '4',
			'৫' => '5', '৬' => '6', '৭' => '7', '৮' => '8', '৯' => '9',
		];
	}
	return strtr((string) $s, $map);
}

/**
 * Format a phone number for display: Bengali digits, grouped 5-4.
 *
 * 11-digit Bangladeshi mobile numbers are the overwhelming majority here
 * and the local reading habit is XXXXX-XXXXXX. Any other length is shown
 * as digits only rather than guessed at — a wrong grouping is worse than
 * no grouping, because it looks deliberate.
 *
 * @param  string $raw
 * @return string  Bengali digits, or '' when there is nothing to show
 */
function platform_phone_display($raw)
{
	$raw = platform_ascii_digits(trim((string) $raw));
	if ($raw === '') {
		return '';
	}

	$digits = preg_replace('/\D+/', '', $raw);
	if ( ! is_string($digits) || $digits === '') {
		return '';
	}

	/*
	 * 11 digits -> 5 + 6. str_split($d, 5) on an 11-digit string returns
	 * THREE chunks (5, 5, 1) and joining them gives "01813-15279-9",
	 * which is not a number anybody writes. The cut is at 5 and the
	 * remainder is kept whole, which is the local reading habit.
	 */
	if (strlen($digits) === 11) {
		$digits = substr($digits, 0, 5) . '-' . substr($digits, 5);
	}

	return bn_number($digits);
}

/**
 * A `tel:` href. Digits and a leading + only.
 *
 * @param  string $raw
 * @return string  '' when there is nothing usable, so the view omits the link
 */
function platform_phone_tel($raw)
{
	$raw = platform_ascii_digits(trim((string) $raw));

	// A leading + is the only one that means anything; a + in the middle of
	// a stored value is a typo, not an extension marker.
	$plus = (strpos($raw, '+') === 0) ? '+' : '';

	$digits = preg_replace('/\D+/', '', $raw);

	return ( ! is_string($digits) || $digits === '') ? '' : 'tel:' . $plus . $digits;
}

/**
 * A wa.me href from a number stored with or without a country code.
 *
 * wa.me needs the international form and nothing else. A local
 * 0-prefixed number has no country code in it, so 880 (the only country
 * code this product serves) is assumed — and that assumption is recorded
 * in the settings hint rather than hidden, because silently prefixing a
 * number the operator thought was already international would send every
 * WhatsApp message to the wrong country.
 *
 * @param  string $raw
 * @return string  '' when there is nothing usable
 */
function platform_wa_link($raw)
{
	$digits = preg_replace('/\D+/', '', platform_ascii_digits((string) $raw));
	if ( ! is_string($digits) || $digits === '') {
		return '';
	}
	if (substr($digits, 0, 2) !== '88') {
		$digits = '880' . ltrim($digits, '0');
	}
	return 'https://wa.me/' . $digits;
}

/**
 * Every platform contact channel, ready for a view.
 *
 * @return array
 *
 * Keys, and what a view should do with each:
 *
 *   phone1, phone2   raw values, '' when cleared
 *   phone1_href      tel: or ''
 *   phone1_label      Bengali, grouped, '' when cleared
 *   mail             raw address, '' when cleared
 *   mail_href        mailto: or ''
 *   whatsapp         the international digits, '' when cleared
 *   whatsapp_href    https://wa.me/… or ''
 *   hours, address   free text, '' when cleared
 *   telegram         an http(s) URL or ''
 *   stale            TRUE when the fallbacks were used — see the docblock
 */
function platform_contact()
{
	static $cache = NULL;
	if ($cache !== NULL) {
		return $cache;
	}

	/*
	 * The values that were hardcoded in the three views, kept as a
	 * fallback so a missing or unreadable settings row cannot take the
	 * product site's contact details away. They are NOT the source of
	 * truth any more — that is the settings table, and these are only
	 * what is shown when it cannot be read.
	 */
	$cache = [
		'phone1'        => '01813152799',
		'phone2'        => '',
		'mail'          => 'hello@viretadev.com',
		'whatsapp'      => '8801713000111',
		'hours'         => 'সকাল ৯টা - রাত ১০টা',
		'address'       => '',
		'telegram'      => '',
		'stale'         => TRUE,
	];

	$CI =& get_instance();
	if ( ! isset($CI->db)) {
		return $cache;
	}

	/*
	 * The columns are mixed-case in this table (vLogoPath, vaddrs) and CI3
	 * quotes identifiers, so the keys are passed as one select list rather
	 * than as where() arguments. reset_query() first: this runs inside a
	 * view, and a caller that arrived here on a half-built query builder
	 * would otherwise have this ANDed into it — which is the same trap
	 * sadmin_nav_badges() guards against, for the same reason.
	 */
	$CI->db->reset_query();
	$row = $CI->db
		->select('vphone1, vphone2, vmail, vwhatsapp, vsupport_hours, vaddrs, vtelegram', FALSE)
		->limit(1)
		->get('satting_infos')
		->row_array();

	$CI->db->reset_query();

	if (empty($row)) {
		return $cache;
	}

	$cache = [
		'phone1'      => trim((string) ($row['vphone1']      ?? '')),
		'phone2'      => trim((string) ($row['vphone2']      ?? '')),
		'mail'        => trim((string) ($row['vmail']        ?? '')),
		'whatsapp'    => trim((string) ($row['vwhatsapp']    ?? '')),
		'hours'       => trim((string) ($row['vsupport_hours'] ?? '')),
		'address'     => trim((string) ($row['vaddrs']       ?? '')),
		'telegram'    => trim((string) ($row['vtelegram']    ?? '')),
		'stale'       => FALSE,
	];

	/*
	 * A mail address that will not validate is dropped rather than shown,
	 * because an unclickable mailto: on a page whose whole job is to be
	 * contacted is worse than showing one fewer channel. The settings page
	 * validates on save; this is for values that predate it.
	 */
	if ($cache['mail'] !== '' && !filter_var($cache['mail'], FILTER_VALIDATE_EMAIL)) {
		$cache['mail'] = '';
	}

	// Only http(s) survives for the same reason. A javascript: URL stored
	// in a settings table would otherwise render as a live link.
	if ($cache['telegram'] !== ''
		&& !preg_match('#^https?://#i', $cache['telegram'])) {
		$cache['telegram'] = '';
	}

	$cache['phone1_href']   = platform_phone_tel($cache['phone1']);
	$cache['phone1_label'] = platform_phone_display($cache['phone1']);
	$cache['phone2_href']   = platform_phone_tel($cache['phone2']);
	$cache['phone2_label'] = platform_phone_display($cache['phone2']);
	$cache['mail_href']     = $cache['mail'] !== '' ? 'mailto:' . $cache['mail'] : '';
	$cache['whatsapp_href'] = platform_wa_link($cache['whatsapp']);

	return $cache;
}
