<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Sadmin_settings — platform settings and per-tenant contact details
 * =====================================================================
 *
 * WHAT EXISTED BEFORE THIS
 * satting_infos. One row, id 1, written once by the original single-
 * institute install and never read again except for one column:
 *
 *   $config['gl_info'] = $CI->SettingModel->getThisSetting()   (Template.php)
 *   the only use of $gl_info in any view:
 *       back/entry_data_form.php:27  ->  $gl_info->nid_search_activity
 *
 * Everything else in that table is inert. The row still holds the
 * installer's test data — vandor_name 'a', vphone1 '0', vmail 'a@a.com',
 * vaddrs 'aaa' — and the product site does not show any of it. The three
 * support channels a visitor actually sees are HARDCODED in
 * saas/partials/support_modal.php and saas/pages/footer.php, and one of
 * them is visibly wrong: the phone link is tel:01813152799 while the text
 * beside it reads "০১৭১৩-XXXXXX".
 *
 * WHAT THIS MODULE IS FOR
 * Two tables, because there are two scopes and they were being conflated:
 *
 *   satting_infos   the ViretaDev platform's own identity, contact
 *                   channels, social links and support copy. One row.
 *   ins_titute      a parishad's own contact channels and social links.
 *                   Per tenant, so the columns were added there
 *                   additively (no migration needed — they are nullable).
 *
 * A parishad's chairman's phone number is the parishad's business. The
 * support desk's phone number is ours. Putting both in satting_infos means
 * the only way to record the second is to overwrite the first, which is
 * why the per-tenant columns went on ins_titute instead.
 *
 * WHY THE FIELD LIST LIVES HERE AND NOT IN THE VIEW
 * The view needs a label, a type and a hint for each field; the API needs
 * a whitelist and a validator. Those are the same list, and two lists drift
 * — the classic drift being a field that renders in the panel and is then
 * silently dropped by the endpoint, which reads as "saving does nothing".
 * So field_specs() is the single source and both sides read it.
 *
 * WHAT THIS MODULE DOES NOT DO
 * As of 2026-09-30 it no longer has this excuse: the product site reads
 * these values through platform_contact() in
 * application/helpers/platform_helper.php, and saas/partials/navbar.php,
 * saas/partials/support_modal.php and saas/pages/contact.php all call it.
 * Until that wiring landed the module was write-only by choice, and the
 * record of why it was built that way is docs/SADMIN-PANEL.md 7.1.
 *
 * What it still does not do: it does not own the display formatting. The
 * helper builds the tel: / wa.me / mailto: schemes and drops a malformed
 * value, so an operator who types a broken URL here gets a channel
 * omitted rather than a dead link. The rule lives with the reader, which
 * is the only place that can build a URL safely.
 *
 * THE ASSET FIELDS
 * vLogoPath and vFaviconPath currently hold "assets/images/logo.png" and
 * "assets/images/favicon.png" — paths to files that do not exist in this
 * repository. They also fail Tenant_media::is_path(), which requires
 * exactly one slash, so media_url() would classify them as a legacy
 * base64 payload and emit
 *     <img src="data:image/png;base64,assets/images/logo.png">
 * i.e. a broken image with a correct-looking src. Anything rendering
 * these must go through media_url(), and the upload below is what puts a
 * real value in the column. The stale value is left in place rather than
 * blanked: it is the only record of what the original install intended,
 * and an operator can see it in the field before overwriting it.
 */
class Sadmin_settings extends CI_Controller
{
	/**
	 * The one settings row. There is only ever one — SettingModel reads
	 * sattings_infos_id = 1 unconditionally, so creating a second row
	 * would not make it configurable, it would just be invisible.
	 */
	const PLATFORM_ROW = 1;

	/** Hard cap for a `text` column, which Postgres reports as having no
	 *  maximum length. Generous for a support note, small enough that the
	 *  row is not a document store. */
	const TEXT_CAP = 4000;

	public function __construct()
	{
		parent::__construct();
		$this->load->library(['ion_auth', 'session']);
		$this->load->library('tenant_media');

		// The gate. Halts on failure.
		require_sadmin();
	}

	/* =====================================================================
	 * Field specifications
	 * ================================================================== */

	/**
	 * satting_infos, grouped into the four panels the page renders.
	 *
	 * Each entry is:
	 *   key      the POST field name, which is also the column name
	 *   label    what the operator is being asked for
	 *   type     text | email | url | phone | textarea | int
	 *   hint     what actually happens with the value. A field with no
	 *            consumer says so here, on the field, rather than in a
	 *            changelog nobody reads.
	 *   blank    whether an empty value is stored as NULL (all of them,
	 *            but stated so it is one place to change).
	 *
	 * @return array
	 */
	public function platform_specs()
	{
		return [
			'identity' => [
				'title' => 'প্ল্যাটফর্ম পরিচিতি',
				'note'  => 'ViretaDev-এর নিজের নাম ও লোগো। কোনো ইউনিয়ন পরিষদের এগুলো নয় — ইউনিয়নের নিজস্ব তথ্য নিচের "ইউনিয়ন পরিষদ" ট্যাবে।',
				'fields' => [
					[
						'key'   => 'vandor_name',
						'label' => 'ব্র্যান্ডের নাম',
						'type'  => 'text',
						'hint'  => 'গবেষণা করে বের হয়েছে: এখন কোথাও পড়া হয় না।',
					],
					[
						'key'   => 'vandor_title',
						'label' => 'ট্যাগলাইন',
						'type'  => 'text',
						'hint'  => 'গবেষণা করে বের হয়েছে: এখন কোথাও পড়া হয় না।',
					],
					[
						'key'   => 'vabout_short',
						'label' => 'সংক্ষিপ্ত পরিচিতি',
						'type'  => 'textarea',
						'hint'  => 'গবেষণা করে বের হয়েছে: এখন কোথাও পড়া হয় না।',
					],
					[
						'key'   => 'vLogoPath',
						'label' => 'লোগো',
						'type'  => 'asset',
						'hint'  => 'PNG/JPG/GIF/WebP, ৫০০ KB পর্যন্ত। SVG নেওয়া হয় না — একটি SVG স্ক্রিপ্ট ধারণ করতে পারে, আর uploads/ ডিরেক্টরিতে কোনো PHP চলার নিয়ম নেই।',
					],
					[
						'key'   => 'vFaviconPath',
						'label' => 'ফেভিকন',
						'type'  => 'asset',
						'hint'  => 'বর্গাকার ছবি, ৫০০ KB পর্যন্ত। ব্রাউজারের ট্যাবে বসে।',
					],
				],
			],

			'contact' => [
				'title' => 'যোগাযোগ ও হেল্পডেস্ক',
				'note'  => 'এই মাধ্যমগুলো প্রোডাক্ট সাইটের নেভিগেশন বার, সাপোর্ট মডাল ও কন্টাক্ট পেজে দেখানো হয় (platform_contact() দিয়ে)। ফিল্ড খালি রাখলে সেই মাধ্যমটি ওই পেজে আর দেখাবে না। ফোন নম্বরে বাংলা বা ইংরেজি — যেকোনো অঙ্ক লেখা যাবে, ফরম্যাটটা নিজে থেকেই ঠিক হবে।',
				'fields' => [
					[
						'key'   => 'vphone1',
						'label' => 'হেল্পডেস্ক ফোন ১',
						'type'  => 'phone',
						'hint'  => 'সাপোর্ট মডালে এখন ০১৮১৩১৫২৭৯৯ হার্ডকোড করা।',
					],
					[
						'key'   => 'vphone2',
						'label' => 'হেল্পডেস্ক ফোন ২',
						'type'  => 'phone',
						'hint'  => 'বিকল্প নম্বর। খালি রাখলে মডালে দেখানো হবে না।',
					],
					[
						'key'   => 'vmail',
						'label' => 'হেল্পডেস্ক ইমেইল',
						'type'  => 'email',
						'hint'  => 'সাপোর্ট মডালে এখন hello@viretadev.com হার্ডকোড করা।',
					],
					[
						'key'   => 'vwhatsapp',
						'label' => 'হোয়াটসঅ্যাপ',
						'type'  => 'phone',
						'hint'  => 'দেশের কোডসহ, যেমন 8801xxxxxxxx। wa.me লিংকে এই মানটিই যায়।',
					],
					[
						'key'   => 'vtelegram',
						'label' => 'টেলিগ্রাম',
						'type'  => 'url',
						'hint'  => 'http বা https দিয়ে শুরু, উদাহরণ: https://t.me/viretadev',
					],
					[
						'key'   => 'vaddrs',
						'label' => 'ঠিকানা',
						'type'  => 'textarea',
						'hint'  => 'অফিসের ঠিকানা, ফুটারে দেখানোর জন্য।',
					],
					[
						'key'   => 'vsupport_hours',
						'label' => 'সাপোর্টের সময়',
						'type'  => 'text',
						'hint'  => 'যেমন: শনি–বৃহস্পতি, সকাল ৯টা – সন্ধ্যা ৬টা',
					],
					[
						'key'   => 'vsupport_note',
						'label' => 'সাপোর্ট নোট',
						'type'  => 'textarea',
						'hint'  => 'সাপোর্ট মডালের নিচে দেখানো বার্তা। বড় লেখা নয়।',
					],
				],
			],

			'social' => [
				'title' => 'সোশ্যাল লিংক',
				'note'  => 'সবগুলো http:// বা https:// দিয়ে শুরু হতে হবে। javascript: বা data: স্কিম লেখা যাবে না — এই লিংকগুলো একদিন <a href> হিসেবে রেন্ডার হবে।',
				'fields' => [
					['key' => 'fb_link',       'label' => 'ফেসবুক',      'type' => 'url', 'hint' => ''],
					['key' => 'twitlink',      'label' => 'এক্স / টুইটার', 'type' => 'url', 'hint' => ''],
					['key' => 'youtubeLink',   'label' => 'ইউটিউব',      'type' => 'url', 'hint' => ''],
					['key' => 'googleLink',    'label' => 'গুগল প্লাস',  'type' => 'url', 'hint' => ''],
					['key' => 'linkedIN',      'label' => 'লিংকডইন',     'type' => 'url', 'hint' => ''],
					['key' => 'instagramLink', 'label' => 'ইনস্টাগ্রাম',  'type' => 'url', 'hint' => ''],
				],
			],

			/*
			 * Read-only. These are inherited from the original
			 * single-institute install and they are verification RATES
			 * for one NID-checking workflow that this platform has no
			 * equivalent of. They are shown because an operator looking
			 * at a settings table needs to know they exist, and they are
			 * not editable because there is no honest way to present
			 * "change the price of NID verification for a tenant that
			 * does not exist" as a setting.
			 *
			 * nid_search_activity is the exception and the reason this
			 * block is here at all: it is 0 today and back/entry_data_form.php
			 * hides its NID search box when it is 0. Toggling it is a
			 * real decision with a real effect, so it gets an edit
			 * control. Every other column in this group is inert.
			 */
			'legacy' => [
				'title' => 'লিগ্যাসি ভেরিফিকেশন সেটিংস',
				'note'  => 'মূল এক-ইনস্টিটিউট অ্যাপ থেকে আসা কলাম। "NID সার্চ দেখান" ছাড়া বাকি সবগুলোর কোথাও পড়া হয় না — নিচে যা লেখা আছে সেটাই গ্রেপ দিয়ে যাচাই করা।',
				'fields' => [
					[
						'key'   => 'nid_search_activity',
						'label' => 'NID সার্চ দেখান',
						'type'  => 'int',
						'hint'  => '১ হলে back/entry_data_form.php তে NID সার্চের বাক্স দেখায়, ০ হলে লুকায়। এটিই এই গ্রুপের একমাত্র কার্যকর ফিল্ড।',
						'edit'  => TRUE,
					],
					['key' => 'nid_check_acts',         'label' => 'NID চেক সক্রিয়',      'type' => 'int',      'hint' => 'পড়া হয় না।', 'edit' => FALSE],
					['key' => 'nid_verify_rate_new',    'label' => 'নতুন ভেরিফিকেশন রেট', 'type' => 'text',     'hint' => 'পড়া হয় না।', 'edit' => FALSE],
					['key' => 'old_verify_rate_old',    'label' => 'পুরনো ভেরিফিকেশন রেট', 'type' => 'text',     'hint' => 'পড়া হয় না।', 'edit' => FALSE],
					['key' => 'sonod_create_rates',     'label' => 'সনদ তৈরির রেট',       'type' => 'text',     'hint' => 'পড়া হয় না।', 'edit' => FALSE],
					['key' => 'vmap_embed',             'label' => 'ম্যাপ এমবেড কোড',     'type' => 'textarea', 'hint' => 'কোথাও রেন্ডার হয় না, তাই কোড হিসেবে সংরক্ষণ করা হলেও কেউ দেখবে না।', 'edit' => FALSE],
				],
			],
		];
	}

	/**
	 * ins_titute — one parishad's own contact and social details.
	 *
	 * These are the ins_titute columns the panel migration added. There was
	 * no editor for them anywhere, so until this module existed a tenant
	 * could be onboarded with a hostname and a plan and could not be given
	 * a support number at all.
	 *
	 * Two of them are named without the `v` prefix the satting_infos
	 * columns use (youtube_link, not vyoutubeLink) because the columns were
	 * written from the tenant's point of view rather than copied from the
	 * platform table. Nothing normalises the two conventions; the mapping
	 * is here, once, so the two tables are not confused.
	 *
	 * @return array  flat list, the same entry shape as platform_specs()
	 */
	public function tenant_specs()
	{
		return [
			['key' => 'support_phone',   'label' => 'হেল্পডেস্ক ফোন',   'type' => 'phone',    'hint' => 'এই ইউনিয়নের নিজের সাপোর্ট নম্বর — প্ল্যাটফর্মের নয়।'],
			['key' => 'support_email',   'label' => 'হেল্পডেস্ক ইমেইল', 'type' => 'email',    'hint' => ''],
			['key' => 'support_hours',   'label' => 'সাপোর্টের সময়',    'type' => 'text',     'hint' => 'যেমন: শনি–বৃহস্পতি, সকাল ৯টা – সন্ধ্যা ৬টা'],
			['key' => 'whatsapp_number', 'label' => 'হোয়াটসঅ্যাপ',     'type' => 'phone',    'hint' => 'দেশের কোডসহ।'],
			['key' => 'fb_link',         'label' => 'ফেসবুক',          'type' => 'url',      'hint' => ''],
			['key' => 'twitlink',        'label' => 'এক্স / টুইটার',   'type' => 'url',      'hint' => ''],
			['key' => 'youtube_link',    'label' => 'ইউটিউব',          'type' => 'url',      'hint' => ''],
			['key' => 'instagram_link',  'label' => 'ইনস্টাগ্রাম',      'type' => 'url',      'hint' => ''],
			['key' => 'linkedin_link',   'label' => 'লিংকডইন',         'type' => 'url',      'hint' => ''],
			['key' => 'tiktok_link',     'label' => 'টিকটক',           'type' => 'url',      'hint' => ''],
			['key' => 'telegram_link',   'label' => 'টেলিগ্রাম',       'type' => 'url',      'hint' => ''],
			['key' => 'map_embed',       'label' => 'ম্যাপ এমবেড কোড', 'type' => 'textarea', 'hint' => 'কোথাও রেন্ডার হয় না।'],
		];
	}

	/* =====================================================================
	 * Pages
	 * ================================================================== */

	/**
	 * The settings page: four platform panels plus a per-tenant editor.
	 *
	 * The tenant editor is a modal rather than a list, because there is one
	 * setting per tenant and the operator arrives with a tenant already in
	 * mind — from the subscriptions page, or from the URL. Hence
	 * ?tenant=N preselects it.
	 */
	public function index()
	{
		$row = $this->db->where('sattings_infos_id', self::PLATFORM_ROW)
			->get('satting_infos')
			->row();

		if ( ! $row) {
			// SettingModel reads sattings_infos_id = 1 unconditionally, so
			// a missing row is not something the app has ever handled. Create
			// it rather than showing an editor with nothing in it — and log
			// it, because a settings row that vanished is a database
			// problem, not a first-run state.
			$this->db->insert('satting_infos', ['sattings_infos_id' => self::PLATFORM_ROW]);
			log_message('error', 'sadmin_settings: satting_infos row 1 was missing and has been recreated empty.');
			$row = $this->db->where('sattings_infos_id', self::PLATFORM_ROW)
				->get('satting_infos')
				->row();
		}

		$tenants = $this->db
			->select('ins_titute_p_idds, ins_name, sub_domains')
			->from('ins_titute')
			->where('ins_titute_p_idds >', 0)
			->order_by('ins_titute_p_idds', 'ASC')
			->get()
			->result();

		$selected = (int) ($this->input->get('tenant') ?: 0);
		$selected_row = NULL;
		if ($selected > 0) {
			foreach ($tenants as $t) {
				if ((int) $t->ins_titute_p_idds === $selected) {
					$selected_row = $t;
					break;
				}
			}
		}

		$groups = $this->platform_specs();

		/*
		 * Asset state, resolved here rather than in the view.
		 *
		 * The point is the `ok` flag. The shipped values are
		 * "assets/images/logo.png" and "assets/images/favicon.png" —
		 * two slashes, so Tenant_media::is_path() rejects them, and
		 * media_url() would fall through to its legacy-base64 branch and
		 * emit src="data:image/png;base64,assets/images/logo.png", a
		 * broken image behind a src that looks fine in the markup. The
		 * honest thing is to say the file is not there, so the panel
		 * shows the raw string plus a warning instead of a preview, and
		 * does not quietly render an <img> that cannot load.
		 */
		$assets = [];
		foreach ($groups as $g) {
			foreach ($g['fields'] as $f) {
				if ($f['type'] !== 'asset') {
					continue;
				}
				$raw = (string) ($row->{$f['key']} ?? '');
				$ok  = $this->tenant_media->is_path($raw);
				$assets[$f['key']] = [
					'raw' => $raw,
					'ok'  => $ok,
					'url' => $ok ? media_url($raw) : '',
				];
			}
		}

		$data = [
			'row'          => $row,
			'groups'       => $groups,
			'caps'         => $this->column_caps('satting_infos'),
			'updated_at'   => $row->updated_at ?? NULL,
			'assets'       => $assets,
			'tenant_specs' => $this->tenant_specs(),
			'tenant_caps'  => $this->column_caps('ins_titute'),
			'tenants'      => $tenants,
			'selected'     => $selected_row,
			'sa_title'     => 'সেটিংস',
		];

		$this->template->sadmin('super_admin/settings', $data);
	}

	/* =====================================================================
	 * APIs
	 * ================================================================== */

	/**
	 * Save one group of platform settings.
	 *
	 * One endpoint for four panels rather than four endpoints, because the
	 * audit row is per call and an operator saving two fields in the social
	 * panel should not produce two rows saying the same thing.
	 *
	 * Only fields in the named group are considered. A key the caller sends
	 * that is not in the group is IGNORED, not rejected: the browser form
	 * and the fetch() path are two different callers of one endpoint, and
	 * one of them being out of date should not turn into an error the
	 * operator cannot act on.
	 */
	public function api_save_platform()
	{
		$input = sadmin_api_input();

		$group = (string) ($input['group'] ?? '');
		$groups = $this->platform_specs();
		if ( ! isset($groups[$group])) {
			sadmin_fail('অজানা সেটিংস গ্রুপ।');
		}

		$row = $this->db->where('sattings_infos_id', self::PLATFORM_ROW)
			->get('satting_infos')
			->row();
		if ( ! $row) {
			sadmin_fail('সেটিংস রো পাওয়া যায়নি। পেজটি রিফ্রেশ করে আবার চেষ্টা করুন।', 404);
		}

		$caps     = $this->column_caps('satting_infos');
		$update   = [];
		$before   = [];
		$after    = [];
		$rejected = [];

		foreach ($groups[$group]['fields'] as $field) {
			if ( ! array_key_exists($field['key'], $input)) {
				continue;
			}

			$key = $field['key'];

			// An asset is uploaded through api_upload_asset, not typed here.
			if ($field['type'] === 'asset') {
				continue;
			}

			// nid_search_activity is the one legacy field with a control,
			// and it is an integer switch, not free text.
			if ($field['type'] === 'int') {
				$new = (string) (int) ($input[$key] ?? 0);
			} else {
				$new = trim((string) $input[$key]);
			}

			$check = $this->validate($field['type'], $new, $caps[$key] ?? self::TEXT_CAP);
			if ($check !== TRUE) {
				$rejected[$field['label']] = $check;
				continue;
			}

			if ((string) $row->$key !== $new) {
				$update[$key] = $new === '' ? NULL : $new;
				$before[$key] = $row->$key;
				// $after is deliberately NOT filled here. It is read back
				// from the row after the update, below, so the audit diff
				// records what Postgres actually stored rather than the
				// string PHP intended to hand it. The difference is
				// visible: nid_search_activity arrives as "1", so an
				// integer column's diff would otherwise read 0 -> "1" and
				// an auditor would have to guess whether the quotes mean
				// something.
			}
		}

		if ($rejected) {
			// ALL OR NOTHING, and that is the deliberate choice.
			//
			// The alternative — write the fields that validated and report
			// only the ones that did not — is the friendlier-looking design
			// and the wrong one here, because the operator pressed ONE
			// "সংরক্ষণ করুন" button for the whole panel. A panel that saves
			// three of four fields and then shows an error for the fourth
			// leaves them unable to tell what is now stored, and pressing
			// the button again does not help, because the fourth field still
			// fails. Refusing the entire save means nothing changed, which
			// is a state a person can reason about.
			sadmin_fail('কিছু ফিল্ড সংরক্ষণ হয়নি: ' . implode('; ', array_map(
				function ($k, $v) { return $k . ' — ' . $v; },
				array_keys($rejected),
				$rejected
			)));
		}

		if ( ! $update) {
			sadmin_json([
				'success' => TRUE,
				'message' => 'কোনো পরিবর্তন ছিল না।',
			]);
		}

		// updated_at is bookkeeping, not a setting. Leaving it out of the
		// summary keeps the audit line readable — "প্ল্যাটফর্ম সেটিংস:
		// fb_link, twitlink" instead of naming the timestamp column beside
		// every real change.
		$changed = array_values(array_diff(array_keys($update), ['updated_at']));

		sadmin_write(
			function () use ($update, $before, $changed) {
				$update['updated_at'] = date('Y-m-d H:i:s');

				if ( ! $this->db->where('sattings_infos_id', self::PLATFORM_ROW)->update('satting_infos', $update)) {
					// affected_rows() is 0 when the values written are the
					// values already there. $update only holds real changes,
					// so 0 here means something else went wrong.
					sadmin_fail('সেটিংস সংরক্ষণ করা গেল না।', 500);
				}

				// Read the post-update state rather than trusting the
				// values we meant to write — see the note on $after above.
				$now = $this->db->where('sattings_infos_id', self::PLATFORM_ROW)
					->get('satting_infos')
					->row();
				$after = [];
				foreach ($changed as $k) {
					$after[$k] = $now->$k;
				}

				return [
					'success' => TRUE,
					'message' => 'সেটিংস সংরক্ষণ হয়েছে।',
					'audit'   => [
						'entity'    => 'platform_setting',
						'entity_id' => (string) self::PLATFORM_ROW,
						// NULL, and it has to be said out loud. audit_log()
						// falls back to tenant_code() when the key is
						// ABSENT, and this module is reachable on a parishad
						// host — the panel is served on dhum.local in
						// development and will be on the apex in
						// production. Without this, a change to the
						// PLATFORM's support phone number gets filed under
						// whichever parishad the admin happened to be
						// visiting, and the audit log's per-tenant filter
						// then reports it as that parishad's change.
						'tenant_id' => NULL,
						'summary'   => 'প্ল্যাটফর্ম সেটিংস: ' . implode(', ', $changed),
						'before'    => $before,
						'after'     => $after,
					],
				];
			},
			'update',
			'platform_setting',
			[
				'entity_id' => (string) self::PLATFORM_ROW,
				'tenant_id' => NULL,
				'summary'   => 'প্ল্যাটফর্ম সেটিংস',
			]
		);
	}

	/**
	 * Read one tenant's contact and social columns.
	 *
	 * A separate read endpoint rather than inlining the row into the page,
	 * so the per-tenant editor is a real form with a real save rather than
	 * a <select> that rewrites the whole page.
	 */
	public function api_get_tenant()
	{
		$id = (int) sadmin_api_input()['tenant_id'];
		$tenant = $this->tenant_or_fail($id);

		$out = [
			'tenant_id' => $id,
			'full_name' => $tenant->ins_name,
			'fields'    => [],
		];

		$caps = $this->column_caps('ins_titute');
		foreach ($this->tenant_specs() as $field) {
			$out['fields'][$field['key']] = [
				'label' => $field['label'],
				'type'  => $field['type'],
				'hint'  => $field['hint'],
				'max'   => $caps[$field['key']] ?? self::TEXT_CAP,
				'value' => (string) ($tenant->{$field['key']} ?? ''),
			];
		}

		sadmin_json(['success' => TRUE, 'tenant' => $out]);
	}

	/**
	 * Save one tenant's contact and social columns.
	 *
	 * No enforce_tenant_scope() here, and that is deliberate rather than an
	 * oversight. That guard asks "is this user reading their OWN parishad's
	 * data", and its own docblock notes a sadmin is waved through on
	 * purpose so the ?tenant=N switch keeps working. This module is the
	 * platform's view of every tenant, so the only question that applies is
	 * the constructor's require_sadmin().
	 */
	public function api_save_tenant()
	{
		$input = sadmin_api_input();
		$id    = (int) ($input['tenant_id'] ?? 0);
		$tenant = $this->tenant_or_fail($id);

		$caps     = $this->column_caps('ins_titute');
		$update   = [];
		$before   = [];
		$rejected = [];

		foreach ($this->tenant_specs() as $field) {
			if ( ! array_key_exists($field['key'], $input)) {
				continue;
			}

			$new = trim((string) $input[$field['key']]);
			$check = $this->validate($field['type'], $new, $caps[$field['key']] ?? self::TEXT_CAP);
			if ($check !== TRUE) {
				$rejected[$field['label']] = $check;
				continue;
			}

			if ((string) ($tenant->{$field['key']} ?? '') !== $new) {
				$update[$field['key']] = $new === '' ? NULL : $new;
				$before[$field['key']] = $tenant->{$field['key']} ?? NULL;
			}
		}

		// All or nothing, for the same reason as api_save_platform() — one
		// button, one outcome, and a partly-saved contact form is a state
		// nobody can reason about.
		if ($rejected) {
			sadmin_fail('কিছু ফিল্ড সংরক্ষণ হয়নি: ' . implode('; ', array_map(
				function ($k, $v) { return $k . ' — ' . $v; },
				array_keys($rejected),
				$rejected
			)));
		}

		if ( ! $update) {
			sadmin_json(['success' => TRUE, 'message' => 'কোনো পরিবর্তন ছিল না।']);
		}

		sadmin_write(
			function () use ($update, $before, $id, $tenant) {
				if ( ! $this->db->where('ins_titute_p_idds', $id)->update('ins_titute', $update)) {
					sadmin_fail('তথ্য সংরক্ষণ করা গেল না।', 500);
				}

				// Read back rather than trusting the values we meant to
				// write — see api_save_platform() for the reasoning.
				$now  = $this->db->where('ins_titute_p_idds', $id)->get('ins_titute')->row();
				$after = [];
				foreach (array_keys($update) as $k) {
					$after[$k] = $now->$k;
				}

				return [
					'success' => TRUE,
					'message' => $tenant->ins_name . ' — যোগাযোগের তথ্য সংরক্ষণ হয়েছে।',
					'audit'   => [
						'entity'     => 'tenant_contact',
						'entity_id'  => (string) $id,
						// A sadmin editing tenant 5's phone number is
						// recorded against tenant 5, not as a
						// cross-tenant write. tenant_id is what makes the
						// audit log filterable per tenant later.
						'tenant_id'  => $id,
						'summary'    => $tenant->ins_name . ' — ' . implode(', ', array_keys($update)),
						'before'     => $before,
						'after'      => $after,
					],
				];
			},
			'update',
			'tenant_contact',
			['tenant_id' => $id, 'summary' => 'ইউনিয়নের যোগাযোগের তথ্য']
		);
	}

	/**
	 * Upload the platform logo or favicon.
	 *
	 * A multipart POST, not JSON — a file cannot be sent in a JSON body
	 * without base64 inlining it, and inlining a 300 KB PNG into a string
	 * that then goes through sadmin_api_input() to reach the filesystem is
	 * three chances to corrupt it. The other APIs in this panel are JSON;
	 * this one is not, and that asymmetry is deliberate.
	 *
	 * `clear=1` with no file removes the value. Clearing is separate from
	 * uploading because an empty file input and "no file was sent" are the
	 * same thing on the wire, and an operator who wants the logo gone
	 * should not have to work out how.
	 */
	public function api_upload_asset()
	{
		$key   = (string) ($this->input->post('key') ?: '');
		$clear = $this->input->post('clear') === '1';

		$assets = [
			'vLogoPath'     => 'logo',
			'vFaviconPath'  => 'favicon',
		];
		if ( ! isset($assets[$key])) {
			sadmin_fail('অজানা অ্যাসেট।');
		}
		$slug = $assets[$key];

		$row = $this->db->where('sattings_infos_id', self::PLATFORM_ROW)
			->get('satting_infos')
			->row();
		if ( ! $row) {
			sadmin_fail('সেটিংস রো পাওয়া যায়নি।', 404);
		}

		if ($clear) {
			$old = (string) $row->$key;

			sadmin_write(
				function () use ($key, $old) {
					$this->db->where('sattings_infos_id', self::PLATFORM_ROW)
						->update('satting_infos', [$key => NULL, 'updated_at' => date('Y-m-d H:i:s')]);

					// The file is unlinked AFTER the column is cleared, so a
					// failed write leaves a path that still resolves. The
					// reverse order could leave a row pointing at a file
					// that is gone — a broken image that cannot be fixed by
					// re-saving the form.
					if ($old !== '') {
						$this->tenant_media->delete($old);
					}

					return [
						'success' => TRUE,
						'message' => 'অ্যাসেটটি মুছে ফেলা হয়েছে।',
						'audit'   => [
							'entity'    => 'platform_setting',
							'entity_id' => (string) self::PLATFORM_ROW,
							'tenant_id' => NULL,
							'summary'   => $key . ' মুছে ফেলা হয়েছে',
							'before'    => [$key => $old],
							'after'     => [$key => NULL],
						],
					];
				},
				'delete',
				'platform_asset',
				['entity_id' => (string) self::PLATFORM_ROW, 'tenant_id' => NULL, 'summary' => $key . ' মোছা']
			);
		}

		$file = $_FILES['file'] ?? NULL;
		if ( ! is_array($file) OR empty($file['tmp_name'])) {
			sadmin_fail('কোনো ফাইল পাওয়া যায়নি।');
		}

		// Own the upload errors rather than showing PHP's ini jargon.
		if ($file['error'] !== UPLOAD_ERR_OK) {
			$map = [
				UPLOAD_ERR_INI_SIZE   => 'ফাইলটি সার্ভারের সর্বোচ্চ আপলোড সাইজের চেয়ে বড়।',
				UPLOAD_ERR_FORM_SIZE  => 'ফাইলটি বড়।',
				UPLOAD_ERR_PARTIAL    => 'ফাইলটি সম্পূর্ণ আপলোড হয়নি। আবার চেষ্টা করুন।',
				UPLOAD_ERR_NO_FILE    => 'কোনো ফাইল নির্বাচন করা হয়নি।',
				UPLOAD_ERR_NO_TMP_DIR => 'সার্ভারে অস্থায়ী ফোল্ডার নেই।',
				UPLOAD_ERR_CANT_WRITE => 'সার্ভারে ফাইলটি লেখা যায়নি।',
				UPLOAD_ERR_EXTENSION  => 'একটি এক্সটেনশন ফাইল আপলোড বন্ধ করেছে।',
			];
			sadmin_fail($map[$file['error']] ?? 'ফাইলটি আপলোড হয়নি।');
		}

		// 500 KB. Not a guess: a logo above that is a photograph, and the
		// column is rendered on every product-site page, so its weight is
		// paid by every visitor. The check is on the bytes, not on the
		// extension, and the sniffed type below decides what is allowed at
		// all.
		if ((int) $file['size'] > 512000) {
			sadmin_fail('ফাইলটি ৫০০ KB-এর বেশি। ছোট করে আবার দিন।');
		}

		$path = $this->tenant_media->save_platform_upload($file, $slug);
		if ($path === NULL) {
			sadmin_fail('ফাইলটি গ্রহণ করা হয়নি। শুধু PNG, JPG, GIF বা WebP দেওয়া যাবে। SVG দেওয়া যাবে না।');
		}

		$old = (string) $row->$key;

		sadmin_write(
			function () use ($key, $path, $old) {
				$this->db->where('sattings_infos_id', self::PLATFORM_ROW)
					->update('satting_infos', [$key => $path, 'updated_at' => date('Y-m-d H:i:s')]);

				// The previous file goes only if it is one of ours. The
				// pre-migration value is "assets/images/logo.png", a path
				// in this repository that does not exist; unlinking it is
				// harmless today and catastrophic the day somebody adds a
				// real file there.
				if ($old !== '' && $this->tenant_media->is_path($old) && strpos($old, 'platform/') === 0) {
					$this->tenant_media->delete($old);
				}

				return [
					'success' => TRUE,
					'message' => 'অ্যাসেট সংরক্ষণ হয়েছে।',
					'url'     => $this->tenant_media->url($path),
					'audit'   => [
						'entity'    => 'platform_setting',
						'entity_id' => (string) self::PLATFORM_ROW,
						'tenant_id' => NULL,
						'summary'   => $key . ' আপলোড হয়েছে',
						'before'    => [$key => $old],
						'after'     => [$key => $path],
					],
				];
			},
			'update',
			'platform_asset',
			['entity_id' => (string) self::PLATFORM_ROW, 'tenant_id' => NULL, 'summary' => $key . ' আপলোড']
		);
	}

	/* =====================================================================
	 * Internals
	 * ================================================================== */

	/**
	 * Look up a tenant or answer with a refusal. Halts.
	 *
	 * @return object
	 */
	private function tenant_or_fail($id)
	{
		if ($id <= 0) {
			sadmin_fail('অবৈধ টেন্যান্ট আইডি।');
		}

		$tenant = $this->db->where('ins_titute_p_idds', $id)->get('ins_titute')->row();
		if ( ! $tenant) {
			sadmin_fail('টেন্যান্ট পাওয়া যায়নি।', 404);
		}

		return $tenant;
	}

	/**
	 * Maximum length for each column of a table, read from the schema.
	 *
	 * The cap a field enforces comes from the column, not from a number
	 * written next to the field. A varchar(30) and a text column need
	 * different limits, and a limit written by hand is wrong the day
	 * somebody alters a column — and when it is wrong in the direction of
	 * "longer than the column", Postgres raises 22001 and the panel answers
	 * 500 on a save the operator did correctly.
	 *
	 * Cached per table for the request, because it is called once per
	 * validated field and information_schema does not need to be consulted
	 * that many times.
	 *
	 * @return array  column name => int, or self::TEXT_CAP where the column
	 *                has no declared maximum
	 */
	private function column_caps($table)
	{
		static $cache = [];

		if (isset($cache[$table])) {
			return $cache[$table];
		}

		$caps = [];
		$rows = $this->db->query(
			'SELECT column_name, character_maximum_length
			   FROM information_schema.columns
			  WHERE table_name = ? AND table_schema = current_schema()',
			[$table]
		)->result();

		foreach ($rows as $r) {
			$caps[$r->column_name] = $r->character_maximum_length === NULL
				? self::TEXT_CAP
				: (int) $r->character_maximum_length;
		}

		$cache[$table] = $caps;
		return $caps;
	}

	/**
	 * Validate one value against one field type.
	 *
	 * Returns TRUE or a Bangla sentence saying what is wrong with it. The
	 * sentence goes straight into the operator's face, so it names the
	 * field's own label rather than a column name.
	 *
	 * The url check is the one that matters. Every link in this table ends
	 * up in an href on the product site eventually, and a value of
	 * "javascript:alert(1)" is a stored XSS that a super admin can plant.
	 * Requiring an explicit http/https scheme also rejects "//evil.example",
	 * which is protocol-relative and therefore also an escape.
	 *
	 * @return TRUE|string
	 */
	private function validate($type, $value, $cap)
	{
		if ($value === '') {
			return TRUE;   // blank is valid for every type here
		}

		$len = mb_strlen($value);
		if ($len > $cap) {
			return 'সর্বোচ্চ ' . bn_number($cap) . ' অক্ষরের মধ্যে লিখুন (এখন ' . bn_number($len) . ' অক্ষর)।';
		}

		switch ($type) {
			case 'url':
				if ( ! preg_match('#^https?://#i', $value) || preg_match('/[\s<>"]/', $value)) {
					return 'http:// বা https:// দিয়ে শুরু করতে হবে।';
				}
				if ( ! filter_var($value, FILTER_VALIDATE_URL)) {
					return 'ঠিকানাটি বৈধ নয়।';
				}
				return TRUE;

			case 'email':
				if ( ! filter_var($value, FILTER_VALIDATE_EMAIL)) {
					return 'ইমেইল ঠিকানাটি সঠিক নয়।';
				}
				return TRUE;

			case 'phone':
				// Deliberately loose. Bangladeshi numbers arrive as
				// 01813152799, +8801813152799, 01813-152799 and
				// "(01813) 152799" depending on who typed them, and a
				// settings form that rejects two of those four is a form
				// operators learn to work around rather than fill in.
				// What it must not accept is a URL or a tag, because these
				// end up inside a tel: and wa.me href.
				if ( ! preg_match('#^[0-9+()\-.\s]{6,30}$#', $value)) {
					return 'নম্বরে শুধু অঙ্ক, + ও - থাকতে পারে।';
				}
				return TRUE;

			case 'int':
				return ctype_digit($value)
					? TRUE
					: 'শুধু সংখ্যা লিখুন (০ অথবা ১)।';

			case 'textarea':
			case 'text':
			default:
				// Control characters are refused on every text type: they
				// are invisible in a field and ruin a page that prints one
				// into a mail template later.
				if (preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', $value)) {
					return 'অদৃশ্য কন্ট্রোল অক্ষর আছে।';
				}
				return TRUE;
		}
	}
}
