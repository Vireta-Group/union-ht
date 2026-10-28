<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Sadmin_sonod — Global Sonod Design & Text Template Manager
 * =====================================================================
 *
 * Super-admin platform module for central control over union parishad
 * certificates:
 *   1. 55+ SVG ornate frames, color palettes, monograms & pad clearances.
 *   2. Global Master Sonod Text Templates (deft_cer_all) that every new
 *      union inherits upon registration.
 *   3. Tenant-Specific Custom Overrides (certifys_default_ins_ss) allowing
 *      individual unions to customize their certificates without corrupting
 *      or affecting the global master.
 *
 * Auth Gate: require_sadmin() from sadmin_helper.php halts on unauthorized.
 */
class Sadmin_sonod extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->library(['ion_auth', 'session']);
		$this->load->model('SettingModel');
		$this->load->model('SonodCrudModel', 'sonodM');

		// Platform super-admin gate
		require_sadmin();
	}

	/**
	 * Main Sonod Manager Interface
	 */
	public function index()
	{
		$tenants = $this->db
			->select('ins_titute_p_idds, ins_name, ins_name_en, domain_names, is_active')
			->order_by('ins_titute_p_idds', 'asc')
			->get('ins_titute')
			->result();

		$default_tenant_id = !empty($tenants) ? (int)$tenants[0]->ins_titute_p_idds : 1;
		$tenant_id = (int)($this->input->get('tenant_id') ?: $default_tenant_id);

		$selected_tenant = $this->db
			->where('ins_titute_p_idds', $tenant_id)
			->get('ins_titute')
			->row();

		if (!$selected_tenant && !empty($tenants)) {
			$tenant_id = $default_tenant_id;
			$selected_tenant = $tenants[0];
		}

		$theme_config = $this->SettingModel->getTenantSonodThemeConfig($tenant_id);

		// Global master templates from deft_cer_all
		$global_sonods = $this->sonodM->getAllGlobalSonod();

		// Tenant-specific overrides from certifys_default_ins_ss
		$tenant_custom_sonods = $this->sonodM->getTenantCustomSonods($tenant_id);

		$tenant_override_map = [];
		foreach ($tenant_custom_sonods as $tcs) {
			if (!empty($tcs->deft_cer_all_id)) {
				$tenant_override_map[$tcs->deft_cer_all_id] = $tcs;
			}
		}

		$data = [
			'sa_title'              => 'সনদ ও টেমপ্লেট ম্যানেজার',
			'sa_seg'                => 'sadmin_sonod',
			'tenants'               => $tenants,
			'selected_tenant_id'    => $tenant_id,
			'selected_tenant'       => $selected_tenant,
			'theme_config'          => $theme_config,
			'border_list'           => $this->get_border_list(),
			'color_palettes'        => $this->get_color_palettes(),
			'global_sonods'         => $global_sonods,
			'tenant_custom_sonods'  => $tenant_custom_sonods,
			'tenant_override_map'   => $tenant_override_map,
		];

		Template::sadmin('super_admin/sonod_manager', $data);
	}

	/**
	 * Save Sonod Design Configuration (Borders, Colors, Watermark)
	 */
	public function save()
	{
		$tenant_id = (int)$this->input->post('tenant_id');
		if ($tenant_id <= 0) {
			$tenant_id = 1;
		}

		$apply_to_all = ($this->input->post('apply_to_all') === '1');
		$existing_config = $this->SettingModel->getTenantSonodThemeConfig($tenant_id);

		$config = [
			'design_mode'            => in_array($this->input->post('design_mode'), ['digital', 'custom_upload']) ? $this->input->post('design_mode') : 'digital',
			'theme_color'            => $this->input->post('theme_color') ?: ($existing_config['theme_color'] ?? 'emerald'),
			'primary_color'          => $this->input->post('primary_color') ?: ($existing_config['primary_color'] ?? '#065f46'),
			'border_color'           => $this->input->post('border_color') ?: ($existing_config['border_color'] ?? '#065f46'),
			'border_style'           => $this->input->post('border_style') ?: ($existing_config['border_style'] ?? 'double'),
			'border_design'          => $this->input->post('border_design') ?: ($existing_config['border_design'] ?? 'border_1'),
			'border_type'            => $this->input->post('border_type') ?: ($existing_config['border_type'] ?? 'ornate_frame'),
			'top_left_monogram'      => $this->input->post('top_left_monogram') ?: ($existing_config['top_left_monogram'] ?? 'bd_gov'),
			'monogram_custom_url'    => $this->input->post('monogram_custom_url') ?: ($existing_config['monogram_custom_url'] ?? ''),
			'watermark_type'         => $this->input->post('watermark_type') ?: ($existing_config['watermark_type'] ?? 'bd_gov'),
			'watermark_visible'      => ($this->input->post('watermark_visible') === '1'),
			'watermark_opacity'      => floatval($this->input->post('watermark_opacity') ?: 0.07),
			'watermark_size'         => intval($this->input->post('watermark_size') ?: 380),
			'show_govt_logo'         => ($this->input->post('show_govt_logo') === '1'),
			'show_up_logo'           => ($this->input->post('show_up_logo') === '1'),
			'up_logo_position'       => $this->input->post('up_logo_position') ?: 'right',
			'show_barcode'           => ($this->input->post('show_barcode') === '1'),
			'show_qr_code'           => ($this->input->post('show_qr_code') === '1'),
			'show_member_sign'       => ($this->input->post('show_member_sign') === '1'),
			'show_sec_sign'          => ($this->input->post('show_sec_sign') === '1'),
			'show_chairman_sign'     => ($this->input->post('show_chairman_sign') === '1'),
			'signatory_count'        => $this->input->post('signatory_count') ?: 'auto',
			'font_family'            => $this->input->post('font_family') ?: 'tiro_bangla',
			'pad_top_margin_mm'      => intval($this->input->post('pad_top_margin_mm') ?: 42),
			'citizen_photo_position' => $this->input->post('citizen_photo_position') ?: 'under_title',
			'footer_text'            => $this->input->post('footer_text') ?: 'ডিজিটাল ইউনিয়ন পরিষদ সেবা',
			'custom_pad_path'        => $this->input->post('custom_pad_path') ?: ($existing_config['custom_pad_path'] ?? ''),
			'custom_pad_opacity'     => floatval($this->input->post('custom_pad_opacity') ?: 1.0),
		];

		// Handle custom pad design upload
		if (!empty($_FILES['custom_pad_file']['tmp_name'])) {
			$clean_bytes = function_exists('validate_secure_image_upload') ? validate_secure_image_upload($_FILES['custom_pad_file']) : @file_get_contents($_FILES['custom_pad_file']['tmp_name']);
			if ($clean_bytes !== false) {
				$upload_dir = FCPATH . 'uploads/tenant-' . $tenant_id . '/';
				if (!is_dir($upload_dir)) {
					@mkdir($upload_dir, 0755, true);
				}
				$ext = 'png';
				$finfo = finfo_open(FILEINFO_MIME_TYPE);
				$mime = finfo_buffer($finfo, $clean_bytes);
				finfo_close($finfo);
				if ($mime === 'image/jpeg') $ext = 'jpg';
				elseif ($mime === 'image/webp') $ext = 'webp';

				$file_name = 'custom_pad_sadmin_' . bin2hex(random_bytes(8)) . '.' . $ext;
				$dest = $upload_dir . $file_name;
				if (@file_put_contents($dest, $clean_bytes) !== false) {
					@chmod($dest, 0644);
					$config['custom_pad_path'] = 'tenant-' . $tenant_id . '/' . $file_name;
					$config['design_mode'] = 'custom_upload';
				}
			}
		} elseif ($this->input->post('remove_custom_pad') === '1') {
			$config['custom_pad_path'] = '';
			$config['design_mode'] = 'digital';
		}

		if ($apply_to_all) {
			$all_tenants = $this->db->select('ins_titute_p_idds')->get('ins_titute')->result();
			foreach ($all_tenants as $t) {
				$this->SettingModel->updateTenantSonodThemeConfig($t->ins_titute_p_idds, $config);
			}
			$msg = 'সকল ইউনিয়ন পরিষদের সনদের গ্লোবাল ডিজাইন সফলভাবে আপডেট ও সিনক্রোনাইজ করা হয়েছে।';
		} else {
			$this->SettingModel->updateTenantSonodThemeConfig($tenant_id, $config);
			$msg = 'নির্বাচিত ইউনিয়ন পরিষদের সনদের ডিজাইন কনফিগারেশন সফলভাবে সংরক্ষিত হয়েছে।';
		}

		if ($this->input->is_ajax_request() || $this->input->get_request_header('X-Requested-With') === 'XMLHttpRequest') {
			$this->output
				->set_content_type('application/json')
				->set_output(json_encode([
					'status'  => 'success',
					'message' => $msg,
					'config'  => $config,
				], JSON_UNESCAPED_UNICODE));
			return;
		}

		$this->session->set_flashdata('sa_flash', [
			'type' => 'success',
			'msg'  => $msg
		]);

		redirect('sadmin_sonod?tenant_id=' . $tenant_id);
	}

	/**
	 * Save / Update Global Master Sonod Text Template (deft_cer_all)
	 * Changes apply to the platform master and all unions using default.
	 */
	public function save_global_sonod_text()
	{
		$sonod_id = (int)$this->input->post('sonod_id');
		$data = [
			'certify_titles'       => trim($this->input->post('certify_titles')),
			'certify_title_text_s' => trim($this->input->post('certify_title_text_s')),
			'certify_detail_ss'    => trim($this->input->post('certify_detail_ss')),
			'en_bn_type'           => (int)($this->input->post('en_bn_type') ?: 1),
		];

		if ($sonod_id > 0) {
			$this->sonodM->updateGlobalSonod($sonod_id, $data);
			$msg = 'গ্লোবাল মাস্টার সনদ টেমপ্লেট সফলভাবে আপডেট করা হয়েছে। সকল নতুন ও ডিফল্ট পরিষদ এটি দেখতে পাবে।';
		} else {
			$sonod_id = $this->sonodM->insertGlobalSonod($data);
			$msg = 'নতুন গ্লোবাল সনদপত্র সফলভাবে প্ল্যাটফর্মে যুক্ত হয়েছে।';
		}

		if ($this->input->is_ajax_request() || $this->input->get_request_header('X-Requested-With') === 'XMLHttpRequest') {
			$this->output
				->set_content_type('application/json')
				->set_output(json_encode([
					'status'   => 'success',
					'message'  => $msg,
					'sonod_id' => $sonod_id,
					'data'     => $data,
				], JSON_UNESCAPED_UNICODE));
			return;
		}

		$this->session->set_flashdata('sa_flash', [
			'type' => 'success',
			'msg'  => $msg
		]);

		$tenant_id = (int)$this->input->post('tenant_id') ?: 1;
		redirect('sadmin_sonod?tenant_id=' . $tenant_id . '#texts');
	}

	/**
	 * Save / Update Tenant-Specific Sonod Override (certifys_default_ins_ss)
	 * Saves specifically for inst_pr_aidddd_id; NEVER affects deft_cer_all.
	 */
	public function save_tenant_sonod_override()
	{
		$tenant_id = (int)$this->input->post('tenant_id');
		$custom_id = (int)$this->input->post('custom_id');
		$global_id = (int)$this->input->post('global_id');

		$data = [
			'inst_pr_aidddd_id'   => $tenant_id,
			'deft_cer_all_id'     => $global_id ?: NULL,
			'cert_title_ss'       => trim($this->input->post('cert_title_ss')),
			'certf_head_texts'    => trim($this->input->post('certf_head_texts')),
			'certf_detail_s_sett' => trim($this->input->post('certf_detail_s_sett')),
			'en_bn_type'          => (int)($this->input->post('en_bn_type') ?: 1),
		];

		if ($custom_id > 0) {
			$this->sonodM->updateTenantCustomSonod($custom_id, $tenant_id, $data);
			$msg = 'নির্বাচিত ইউনিয়নের জন্য কাস্টমাইজড সনদ সফলভাবে সংরক্ষিত হয়েছে (গ্লোবাল মাস্টার অপরিবর্তিত রয়েছে)।';
		} else {
			// Check if existing override exists for this global_id in this tenant
			$existing = $global_id > 0 ? $this->sonodM->getTenantCustomSonodByGlobalId($global_id, $tenant_id) : null;
			if ($existing) {
				$this->sonodM->updateTenantCustomSonod($existing->certifys_default_ins_ss_iiidddd, $tenant_id, $data);
				$custom_id = $existing->certifys_default_ins_ss_iiidddd;
			} else {
				$custom_id = $this->sonodM->insertTenantCustomSonod($data);
			}
			$msg = 'নির্বাচিত ইউনিয়নের জন্য আলাদা সনদ টেমপ্লেট সেভ করা হয়েছে।';
		}

		if ($this->input->is_ajax_request() || $this->input->get_request_header('X-Requested-With') === 'XMLHttpRequest') {
			$this->output
				->set_content_type('application/json')
				->set_output(json_encode([
					'status'    => 'success',
					'message'   => $msg,
					'custom_id' => $custom_id,
					'tenant_id' => $tenant_id,
					'data'      => $data,
				], JSON_UNESCAPED_UNICODE));
			return;
		}

		$this->session->set_flashdata('sa_flash', [
			'type' => 'success',
			'msg'  => $msg
		]);

		redirect('sadmin_sonod?tenant_id=' . $tenant_id . '#texts');
	}

	/**
	 * Reset Tenant Override Back to Global Master
	 * Deletes the tenant's row in certifys_default_ins_ss.
	 */
	public function reset_tenant_sonod_override()
	{
		$tenant_id = (int)$this->input->post('tenant_id');
		$custom_id = (int)$this->input->post('custom_id');

		if ($custom_id > 0 && $tenant_id > 0) {
			$this->sonodM->deleteTenantCustomSonod($custom_id, $tenant_id);
			$msg = 'ইউনিয়নের কাস্টমাইজেশন মুছে ফেলে গ্লোবাল ডিফল্ট টেমপ্লেটে সফলভাবে রিস্টোর করা হয়েছে।';
		} else {
			$msg = 'রিসেট সম্পন্ন হয়নি (তথ্য পাওয়া যায়নি)।';
		}

		if ($this->input->is_ajax_request() || $this->input->get_request_header('X-Requested-With') === 'XMLHttpRequest') {
			$this->output
				->set_content_type('application/json')
				->set_output(json_encode([
					'status'  => 'success',
					'message' => $msg,
				], JSON_UNESCAPED_UNICODE));
			return;
		}

		$this->session->set_flashdata('sa_flash', [
			'type' => 'success',
			'msg'  => $msg
		]);

		redirect('sadmin_sonod?tenant_id=' . $tenant_id . '#texts');
	}

	/**
	 * JSON API: Fetch All Sonods (Global + Tenant Overrides) for React / Async
	 */
	public function api_get_sonods()
	{
		$tenant_id = (int)($this->input->get('tenant_id') ?: 1);
		$globals = $this->sonodM->getAllGlobalSonod();
		$tenant_overrides = $this->sonodM->getTenantCustomSonods($tenant_id);

		$override_map = [];
		foreach ($tenant_overrides as $to) {
			if (!empty($to->deft_cer_all_id)) {
				$override_map[$to->deft_cer_all_id] = $to;
			}
		}

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode([
				'status'           => 'success',
				'tenant_id'        => $tenant_id,
				'global_sonods'    => $globals,
				'tenant_overrides' => $tenant_overrides,
				'override_map'     => $override_map,
			], JSON_UNESCAPED_UNICODE));
	}

	/**
	 * JSON API: Save Theme Config from React Panel or Fetch
	 */
	public function api_save_config()
	{
		$raw = file_get_contents('php://input');
		$payload = json_decode($raw, true) ?: $this->input->post();

		$tenant_id = (int)($payload['tenant_id'] ?? 1);
		$apply_to_all = !empty($payload['apply_to_all']);
		$config = $payload['config'] ?? [];

		if (empty($config) && is_array($payload)) {
			$config = $payload;
			unset($config['tenant_id'], $config['apply_to_all']);
		}

		$existing = $this->SettingModel->getTenantSonodThemeConfig($tenant_id);
		$merged = array_merge($existing, $config);

		if ($apply_to_all) {
			$all = $this->db->select('ins_titute_p_idds')->get('ins_titute')->result();
			foreach ($all as $t) {
				$this->SettingModel->updateTenantSonodThemeConfig($t->ins_titute_p_idds, $merged);
			}
			$msg = 'সকল ইউনিয়নের জন্য সনদ ডিজাইন সফলভাবে সংরক্ষিত হয়েছে।';
		} else {
			$this->SettingModel->updateTenantSonodThemeConfig($tenant_id, $merged);
			$msg = 'সনদ ডিজাইন সফলভাবে সংরক্ষিত হয়েছে।';
		}

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode([
				'status'  => 'success',
				'message' => $msg,
				'config'  => $merged,
			], JSON_UNESCAPED_UNICODE));
	}

	/**
	 * JSON API: Fetch Theme Config for React Panel or Async Preview
	 */
	public function api_get_config()
	{
		$tenant_id = (int)($this->input->get('tenant_id') ?: 1);
		$config = $this->SettingModel->getTenantSonodThemeConfig($tenant_id);
		$tenant = $this->db->where('ins_titute_p_idds', $tenant_id)->get('ins_titute')->row();

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode([
				'status'      => 'success',
				'tenant_id'   => $tenant_id,
				'tenant_name' => $tenant->ins_name ?? 'ইউনিয়ন পরিষদ',
				'config'      => $config,
				'borders'     => $this->get_border_list(),
			], JSON_UNESCAPED_UNICODE));
	}

	/**
	 * Border design definitions
	 */
	private function get_border_list()
	{
		return [
			'border_1'  => ['title' => '১. রয়্যাল ক্লাসিক গোল্ড', 'desc' => 'নেভি ব্লু ও গোল্ডেন মাল্টি-লাইন ফ্রেম', 'cat' => 'royal'],
			'border_2'  => ['title' => '২. বিডি গভর্নমেন্ট এমারেল্ড', 'desc' => 'সরকারি এমারেল্ড গ্রীন ও গোল্ডেন ফ্লেক্স', 'cat' => 'classic'],
			'border_3'  => ['title' => '৩. হেরিটেজ ক্রিমসন ব্রোঞ্জ', 'desc' => 'ঐতিহ্যবাহী গাঢ় লাল ও ব্রোঞ্জ অলঙ্করণ', 'cat' => 'royal'],
			'border_4'  => ['title' => '৪. মডার্ন নেভি ডাবল', 'desc' => 'আধুনিক দ্বৈত স্ট্রাইপ পরিপাটি ফ্রেম', 'cat' => 'slim'],
			'border_5'  => ['title' => '৫. গ্রীক কি কর্নার মেয়ান্ডার', 'desc' => 'ক্ল্যাসিক্যাল জ্যামিতিক কর্নার ডিজাইন', 'cat' => 'classic'],
			'border_6'  => ['title' => '৬. সিকিউরিটি গিলোচে ল্যাটিস', 'desc' => 'অফিসিয়াল নিরাপত্তা সনদ জাল প্যাটার্ন', 'cat' => 'classic'],
			'border_7'  => ['title' => '৭. রয়্যাল স্যাফায়ার সিলভার', 'desc' => 'গাঢ় নীল ও রূপালী মেডেলিয়ন কর্নার', 'cat' => 'royal'],
			'border_8'  => ['title' => '৮. স্লেট চারকোল এক্সিকিউটিভ', 'desc' => 'কর্পোরেট গাঢ় ধূসর ও স্বর্ণালী সীমানা', 'cat' => 'classic'],
			'border_9'  => ['title' => '৯. ইসলামিক জ্যামিতিক ৮-স্টার', 'desc' => 'আরব্য সুষম ৮-কোণা তারা ও সীমানা', 'cat' => 'pattern'],
			'border_10' => ['title' => '১০. গোল্ডেন বারোক ফিলিগ্রি', 'desc' => 'রাজকীয় কারুকার্যময় কর্নার ফিলিগ্রি', 'cat' => 'royal'],
			'border_11' => ['title' => '১১. মিনিমাল গোল্ড পিনস্ট্রাইপ', 'desc' => 'সূক্ষ্ম মার্জিত একক সোনালী রেখা', 'cat' => 'slim'],
			'border_12' => ['title' => '১২. ইউপি সিল প্যাটার্ন', 'desc' => 'ইউনিয়ন পরিষদ মনোগ্রাম সিল ফ্রেম', 'cat' => 'classic'],
			'border_13' => ['title' => '১৩. রয়্যাল পার্পল গোল্ড', 'desc' => 'অভিজাত পার্পল ও স্বর্ণালী নকশা', 'cat' => 'royal'],
			'border_14' => ['title' => '১৪. প্লাটিনাম মনোক্রোম ফাইন', 'desc' => 'সাদাকালো সূক্ষ্ম অফিসিয়াল রেখা', 'cat' => 'slim'],
			'border_15' => ['title' => '১৫. ডিপ ব্রোঞ্জ অ্যাম্বার', 'desc' => 'গাঢ় ব্রোঞ্জ ও অ্যাম্বার কারুকাজ', 'cat' => 'royal'],
			'border_16' => ['title' => '১৬. ব্যাংকনোট গিলোচে ওয়েভ', 'desc' => 'গাণিতিক সাইন তরঙ্গ ও সিকিউরিটি রোসেট', 'cat' => 'classic'],
			'border_17' => ['title' => '১৭. আর্ট ডেকো সানবার্স্ট', 'desc' => '১৯২০-এর ক্লাসিক শেভরন ও সানবার্স্ট ফ্রেম', 'cat' => 'pattern'],
			'border_18' => ['title' => '১৮. সেল্টিক নটওয়ার্ক ফিতা', 'desc' => 'ঐতিহাসিক ইন্টারলেস নট ও ত্রিকোণ বিনুনি', 'cat' => 'pattern'],
			'border_19' => ['title' => '১৯. রোমান লরেল পাতার মালা', 'desc' => 'সম্মানসূচক ক্লাসিক্যাল লরেল গারল্যান্ড', 'cat' => 'classic'],
			'border_20' => ['title' => '২০. মরোক্কান জেলিজ স্টার', 'desc' => 'ইসলামিক সুষম জ্যামিতিক তারকার ফ্রেম', 'cat' => 'pattern'],
			'border_21' => ['title' => '২১. ভিক্টোরিয়ান বোটানিক্যাল', 'desc' => 'অ্যাকান্থাস পাতার কারুকার্যময় স্ক্রোল', 'cat' => 'pattern'],
			'border_22' => ['title' => '২২. নটিক্যাল রোপ ক্যাবল', 'desc' => 'দ্বৈত প্যাঁচানো দড়ির নৌ-সীমানা ফ্রেম', 'cat' => 'pattern'],
			'border_23' => ['title' => '২৩. গথিক ক্যাথেড্রাল আর্চ', 'desc' => 'মধ্যযুগীয় ট্রেইফয়েল ও ক্যাটিনারি খিলান', 'cat' => 'pattern'],
			'border_24' => ['title' => '২৪. মডার্ন স্টুডিও মিনিমাল', 'desc' => 'আধুনিক আর্কিটেকচারাল কর্নার কিউব', 'cat' => 'slim'],
			'border_25' => ['title' => '২৫. জাতীয় শাপলা ও পাটপাতা', 'desc' => 'বাংলার জাতীয় প্রতীক শাপলা ও পাটপাতা মোটিফ', 'cat' => 'classic'],
			'border_26' => ['title' => '২৬. ভিন্টেজ গেজেট হ্যাচিং', 'desc' => 'অফিসিয়াল রাজকীয় গেজেট ক্রস-হ্যাচ ফ্রেম', 'cat' => 'classic'],
			'border_27' => ['title' => '২৭. অরিয়েন্টাল মেঘ ও প্যাগোডা', 'desc' => 'ঐতিহ্যবাহী মেঘের প্যাটার্ন ও ফ্রেটওয়ার্ক', 'cat' => 'pattern'],
			'border_28' => ['title' => '২৮. হেরাল্ডিক নাইট শিল্ড', 'desc' => 'শিল্ড মেডেলিয়ন ও মার্জিত সীমানা', 'cat' => 'pattern'],
			'border_29' => ['title' => '২৯. ডায়মন্ড রম্বস চেইন', 'desc' => 'হীরক জ্যামিতিক চেইনের সূক্ষ্ম ফ্রেম', 'cat' => 'pattern'],
			'border_30' => ['title' => '৩০. আর্ট নুভো হুইপল্যাশ', 'desc' => 'প্রাকৃতিক বাঁকানো রেখা ও লতাপাতা কারুকাজ', 'cat' => 'pattern'],
			'border_31' => ['title' => '৩১. সাইবার সিকিউরিটি ম্যাট্রিক্স', 'desc' => 'হাই-টেক ডিজিটাল নোড ও সার্কিট লাইন', 'cat' => 'pattern'],
			'border_32' => ['title' => '৩২. করিন্থিয়ান স্তম্ভ ও কার্নিশ', 'desc' => 'রোমান ক্লাসিক্যাল ফ্লুটেড পিলারের ফ্রেম', 'cat' => 'classic'],
			'border_33' => ['title' => '৩৩. সূক্ষ্ম লেইস ও স্ক্যালপ', 'desc' => 'ডয়লি লেইস ও খাঁজকাটা মার্জিত সীমানা', 'cat' => 'pattern'],
			'border_34' => ['title' => '৩৪. বাউহাউস কনস্ট্রাকটিভিজম', 'desc' => 'আধুনিক জ্যামিতিক ব্লক ও অ্যাবস্ট্রাক্ট ফ্রেম', 'cat' => 'pattern'],
			'border_35' => ['title' => '৩৫. রোকোকো শেল কারতুশ', 'desc' => 'বারোক কার্ভ, সি-স্ক্রোল ও কারতুশ ফ্রেম', 'cat' => 'pattern'],
			'border_36' => ['title' => '৩৬. কারেন্সি ওয়েভ রোসেট', 'desc' => 'ব্যাংকনোট কার্ভ ও জেনুইন সিকিউরিটি সিল', 'cat' => 'classic'],
			'border_37' => ['title' => '৩৭. পার্সিয়ান কল্কা মোটিফ', 'desc' => 'দেশীয় ঐতিহ্যবাহী পেসলি ও কল্কা নকশা', 'cat' => 'pattern'],
			'border_38' => ['title' => '৩৮. হানি-কম্ব হেক্সাগন', 'desc' => 'ষড়ভুজ মৌচাক জ্যামিতিক নেটওয়ার্ক ফ্রেম', 'cat' => 'pattern'],
			'border_39' => ['title' => '৩৯. নর্ডিক রুন ও উইভ', 'desc' => 'স্ক্যান্ডিনেভিয়ান রুনিক লুপ ও ডায়মন্ড উইভ', 'cat' => 'pattern'],
			'border_40' => ['title' => '৪০. ইম্পেরিয়াল রয়্যাল ফিলিগ্রি', 'desc' => 'রাজকীয় মুক্তা, ডায়মন্ড ফ্লেক্স ও ফিলিগ্রি', 'cat' => 'royal'],
			'border_41' => ['title' => '৪১. সুইস হেয়ারলাইন লাক্সারি', 'desc' => 'আল্ট্রা-স্লিম গোল্ড ও স্লেট লাইন, সর্বোচ্চ টেক্সট স্পেস', 'cat' => 'slim'],
			'border_42' => ['title' => '৪২. কার্টিয়ে রয়্যাল জুয়েলারি', 'desc' => 'কার্টিয়ে বেভেলড ডায়মন্ড কাট স্লিম ট্রিম', 'cat' => 'royal'],
			'border_43' => ['title' => '৪৩. মোনাকো ইয়ট ক্লাব এলিট', 'desc' => 'শ্যাম্পেন গোল্ড ও নেভি ব্লু স্লিম রোপ কর্নার', 'cat' => 'royal'],
			'border_44' => ['title' => '৪৪. জাপানিজ সাকুরা ও উইন্ডো', 'desc' => 'জাপানিজ মার্জিত ল্যাটিস ও সাকুরা কর্নার', 'cat' => 'pattern'],
			'border_45' => ['title' => '৪৫. অটোমান টিউলিপ ও অ্যারাবেস্ক', 'desc' => 'ঐতিহাসিক তুর্কি টিউলিপ ও অ্যারাবেস্ক বাঁক', 'cat' => 'pattern'],
			'border_46' => ['title' => '৪৬. ক্যামব্রিজ একাডেমিয়া গথিক', 'desc' => 'ঐতিহ্যবাহী বিশ্ববিদ্যালয় সনদ ডাবল লাইন ফ্রেম', 'cat' => 'classic'],
			'border_47' => ['title' => '৪৭. প্রেসিডেন্সিয়াল ঈগল ক্রেস্ট', 'desc' => 'রাষ্ট্রপতির কার্যালয় স্টাইল তারা ও মেডেলিয়ন', 'cat' => 'royal'],
			'border_48' => ['title' => '৪৮. সিল্ক রোড ফ্লোরাল ভাইন', 'desc' => 'রেশম পথ ফুলের লতা ও সূক্ষ্ম পাতার অলংকরণ', 'cat' => 'pattern'],
			'border_49' => ['title' => '৪৯. দুবাই ক্রাউন লাক্সারি', 'desc' => 'গাঢ় এমারেল্ড, গোল্ডেন কিউবিক নোড ও বেভেল', 'cat' => 'royal'],
			'border_50' => ['title' => '৫০. গোল্ডেন ক্রাউন এক্সিকিউটিভ', 'desc' => 'সোনায় মোড়ানো ট্রিপল লাইন ও রাজকীয় মুকুট কর্নার', 'cat' => 'royal'],
		];
	}

	/**
	 * Preset color palettes
	 */
	private function get_color_palettes()
	{
		return [
			'emerald' => ['name' => 'বিডি গভর্নমেন্ট এমারেল্ড', 'primary' => '#065f46', 'accent' => '#047857', 'badge' => 'bg-emerald-600'],
			'royal'   => ['name' => 'রয়্যাল স্যাফায়ার নেভি',    'primary' => '#1e3a8a', 'accent' => '#1d4ed8', 'badge' => 'bg-blue-800'],
			'crimson' => ['name' => 'হেরিটেজ ক্রিমসন ব্রোঞ্জ', 'primary' => '#881337', 'accent' => '#9f1239', 'badge' => 'bg-rose-800'],
			'slate'   => ['name' => 'এক্সিকিউটিভ স্লেট চারকোল', 'primary' => '#1e293b', 'accent' => '#334155', 'badge' => 'bg-slate-800'],
			'gold'    => ['name' => 'ইম্পেরিয়াল গোল্ডেন ব্রাউন', 'primary' => '#78350f', 'accent' => '#b45309', 'badge' => 'bg-amber-800'],
			'purple'  => ['name' => 'ম্যাজেস্টিক পার্পল ভায়োলেট', 'primary' => '#581c87', 'accent' => '#6b21a8', 'badge' => 'bg-purple-800'],
		];
	}
}
