<?php
defined('BASEPATH') OR exit('No direct script access allowed');
 
class Sonod extends CI_Controller {

    public function __construct() 
	{
		parent::__construct();

		$this->load->library('template');
		$this->load->library('Ion_auth');
		$this->load->library('session');
		$this->load->library('form_validation');
		$this->load->library('upload');
		$this->load->library('user_agent');
		$this->load->library('curl');
		$this->load->library('getapi');

		$this->load->helper('url');

		$this->load->model('ion_auth_model');
		$this->load->model('UserModel');
		$this->load->model('SettingModel');
		$this->load->model('AddMemberModel');
		$this->load->model('FamilyModel');
		$this->load->model('SonodCrudModel', 'sonodM');

		if (!$this->ion_auth->logged_in()) {
			redirect('logout', 'refresh');
		}

		$this->user_id = $this->ion_auth->user()->row()->user_full_tbl_id;

		if (empty($this->user_id)) {
			redirect('logout', 'refresh');
		}

		$this_user_info = $this->UserModel->getUserInfo($this->user_id);  
		$this->dtInternal['this_user_info'] = $this_user_info;
		$this->data['setting_info'] = $this->SettingModel->getThisInsName();

		$user_role_info = get_user_tenant_role($this->user_id);

		$this->data['loggedUser'] = array(
									'userName' 				=> $this_user_info->user_person_name, 
									'userParcent' 			=> $this_user_info->percentager, 
									'userAvatar' 			=> $this_user_info->user_img_base64, 
									'userInstitute' 		=> $this_user_info->institute_name, 
									'userPhone' 			=> $this_user_info->user_phone_no, 
									'userEmail' 			=> $this_user_info->user_email_no, 
									'userAddress' 			=> $this_user_info->address_full, 
									'creatDate' 			=> $this_user_info->today_dates_updating, 
									'divEn' 				=> $this_user_info->div_name, 
									'divBn' 				=> $this_user_info->div_bn_name, 
									'disEn' 				=> $this_user_info->dist_name, 
									'disBn' 				=> $this_user_info->dist_bn_name, 
									'upEN' 					=> $this_user_info->up_name, 
									'upBn' 					=> $this_user_info->up_bn_name, 
									'unEn' 					=> $this_user_info->un_name, 
									'unBn' 					=> $this_user_info->un_bn_name, 
									'username' 				=> $this_user_info->username, 
									'user_u_type' 			=> $this_user_info->u_type, 
									'active' 				=> $this_user_info->active, 
									'userGroupName' 		=> $user_role_info['title'], 
									'userRoleKey' 			=> $user_role_info['key'], 
									'userUniqs' 			=> $this_user_info->user_uniq_qq_reails, 
									'userGroupDescription' 	=> $user_role_info['title']
					);
		// Tenant guard — see enforce_tenant_scope(). Sonod renders through the
		// same template->sonod() path as Dashboard and serves parishad records
		// (issued certificates, NID verifications, death registrations), but had
		// no equivalent check: only logged_in(). A rupsha admin signed in on
		// baghmaraup.gov.bd read and wrote বাঘমারা's sonods freely.
		enforce_tenant_scope();
		enforce_tenant_status();

		// Platform super admin has no parishad — their home is /super_admin
		if ($this->ion_auth->in_group('sadmin')) {
			redirect('super_admin', 'refresh');
		}

	} 

	public function index()
	{
		$this->template->sonod('sonod/index', $this->data); 
	}

	public function edit_global_sonods_view_func_opt()
	{
		redirect('addMySonod');
	}

    public function insert_global_sonods()
    {
		if (!empty($this->input->post('head'))) {
			if (empty($this->sonodM->getGlobalSonodByHead($this->input->post('head')))) {
				$this->sonodM->insertGlobalSonod(array(
					"certify_titles"    		=> $this->input->post('head'),
					"en_bn_type"    			=> $this->input->post('langType'),
					"certify_title_text_s"    	=> $this->input->post('title'),
					"certify_detail_ss" 		=> $this->input->post('data'),
				)); 
			}
		}
    }

	public function add_sonod_self_union_ins_()
	{
		$this->data['all_sonod'] = $this->sonodM->getAllGlobalSonod();
		$this->data['custom_sonods'] = $this->sonodM->getTenantCustomSonods(tenant_code());
		$this->template->sonod('sonod/add_sonod_self_ins_view_file', $this->data); 
	}

	public function save_tenant_custom_sonod()
	{
		$custom_id = (int)$this->input->post('custom_id');
		$deft_cer_id = !empty($this->input->post('deft_cer_id')) ? (int)$this->input->post('deft_cer_id') : null;
		$title = trim($this->input->post('title') ?? '');
		$head = trim($this->input->post('head') ?? '');
		$data = $this->input->post('data');
		$langType = (int)($this->input->post('langType') ?: 1);

		if (empty($title) || empty($head) || empty($data)) {
			$this->output->set_content_type('application/json')->set_output(json_encode([
				'status' => 'error',
				'message' => 'সকল তথ্য সঠিকভাবে পূরণ করুন।'
			]));
			return;
		}

		$payload = array(
			'inst_pr_aidddd_id'   => tenant_code(),
			'cert_title_ss'       => $title,
			'certf_head_texts'    => $head,
			'certf_detail_s_sett' => $data,
			'deft_cer_all_id'     => $deft_cer_id,
			'en_bn_type'          => $langType
		);

		if ($custom_id > 0) {
			$existing = $this->sonodM->getTenantCustomSonodById($custom_id, tenant_code());
			if ($existing) {
				$this->sonodM->updateTenantCustomSonod($custom_id, tenant_code(), $payload);
				$this->output->set_content_type('application/json')->set_output(json_encode([
					'status' => 'success',
					'message' => 'ইউনিয়ন সনদ সফলভাবে আপডেট করা হয়েছে।',
					'id' => $custom_id
				]));
				return;
			}
		}

		if ($deft_cer_id > 0) {
			$existing_global_override = $this->sonodM->getTenantCustomSonodByGlobalId($deft_cer_id, tenant_code());
			if ($existing_global_override) {
				$this->sonodM->updateTenantCustomSonod($existing_global_override->certifys_default_ins_ss_iiidddd, tenant_code(), $payload);
				$this->output->set_content_type('application/json')->set_output(json_encode([
					'status' => 'success',
					'message' => 'গ্লোবাল সনদের কাস্টমাইজেশন সফলভাবে সংরক্ষিত হয়েছে।',
					'id' => $existing_global_override->certifys_default_ins_ss_iiidddd
				]));
				return;
			}
		}

		$new_id = $this->sonodM->insertTenantCustomSonod($payload);
		$this->output->set_content_type('application/json')->set_output(json_encode([
			'status' => 'success',
			'message' => 'নতুন সনদ সফলভাবে যুক্ত করা হয়েছে।',
			'id' => $new_id
		]));
	}

	public function delete_tenant_custom_sonod()
	{
		$custom_id = (int)$this->input->post('custom_id');
		if ($custom_id > 0) {
			$this->sonodM->deleteTenantCustomSonod($custom_id, tenant_code());
			if (function_exists('audit_log')) {
				audit_log('delete', 'custom_sonod', array(
					'entity_id' => (string)$custom_id,
					'tenant_id' => (int)tenant_code(),
					'summary'   => 'কাস্টম সনদ টেমপ্লেট মুছে ফেলা হয়েছে (ID: #' . $custom_id . ')'
				));
			}
			$this->output->set_content_type('application/json')->set_output(json_encode([
				'status' => 'success',
				'message' => 'সনদ সফলভাবে মুছে ফেলা হয়েছে।'
			]));
			return;
		}
		$this->output->set_content_type('application/json')->set_output(json_encode([
			'status' => 'error',
			'message' => 'অবৈধ অনুরোধ।'
		]));
	}

	public function get_tenant_single_custom_sonod()
	{
		$custom_id = (int)$this->input->post('custom_id');
		$data = $this->sonodM->getTenantCustomSonodById($custom_id, tenant_code());
		$this->output->set_content_type('application/json')->set_output(json_encode([
			'status' => !empty($data) ? 'success' : 'error',
			'data' => $data
		]));
	}

	public function sonod_theme_settings_view()
	{
		$tenant_id = (int) tenant_code();
		$this->data['theme_config'] = $this->SettingModel->getTenantSonodThemeConfig($tenant_id);
		$this->data['ins_info'] = $this->SettingModel->getThisInsName();
		$this->template->sonod('sonod/theme_settings_view_file', $this->data);
	}

	public function save_sonod_theme_settings()
	{
		$tenant_id = (int) tenant_code();
		$existing_config = $this->SettingModel->getTenantSonodThemeConfig($tenant_id);
		$is_true = function($val) {
			return $val === '1' || $val === 'true' || $val === 'yes' || $val === true || $val === 1;
		};

		// Design mode: digital system theme vs custom uploaded pad design
		$design_mode = in_array($this->input->post('design_mode'), ['digital', 'custom_upload']) ? $this->input->post('design_mode') : 'digital';

		// Handle custom pad / design file upload
		$custom_pad_path = $this->input->post('custom_pad_path') ?: ($existing_config['custom_pad_path'] ?? '');
		if ($this->input->post('remove_custom_pad') === '1') {
			$custom_pad_path = '';
		} elseif (!empty($_FILES['custom_pad_file']['tmp_name']) && is_uploaded_file($_FILES['custom_pad_file']['tmp_name'])) {
			$tenant_dir = FCPATH . 'uploads/tenant-' . $tenant_id . '/';
			if (!is_dir($tenant_dir)) {
				@mkdir($tenant_dir, 0755, true);
			}
			$ext = strtolower(pathinfo($_FILES['custom_pad_file']['name'], PATHINFO_EXTENSION));
			if (!in_array($ext, ['png', 'jpg', 'jpeg', 'webp'])) {
				$ext = 'png';
			}
			$file_name = 'custom_pad_design_' . time() . '.' . $ext;
			$dest = $tenant_dir . $file_name;
			if (move_uploaded_file($_FILES['custom_pad_file']['tmp_name'], $dest)) {
				$custom_pad_path = 'tenant-' . $tenant_id . '/' . $file_name;
				$design_mode = 'custom_upload';
			}
		}

		$theme_color = in_array($this->input->post('theme_color'), ['emerald', 'indigo', 'slate', 'crimson', 'custom']) ? $this->input->post('theme_color') : 'emerald';

		// Official frame borders (1-55 or none)
		$allowed_borders = [
			'border_1', 'border_2', 'border_3', 'border_4', 'border_5',
			'border_6', 'border_7', 'border_8', 'border_9', 'border_10',
			'border_11', 'border_12', 'border_13', 'border_14', 'border_15',
			'border_16', 'border_17', 'border_18', 'border_19', 'border_20',
			'border_21', 'border_22', 'border_23', 'border_24', 'border_25',
			'border_26', 'border_27', 'border_28', 'border_29', 'border_30',
			'border_31', 'border_32', 'border_33', 'border_34', 'border_35',
			'border_36', 'border_37', 'border_38', 'border_39', 'border_40',
			'border_41', 'border_42', 'border_43', 'border_44', 'border_45',
			'border_46', 'border_47', 'border_48', 'border_49', 'border_50',
			'border_51', 'border_52', 'border_53', 'border_54', 'border_55',
			'none'
		];
		$posted_border = $this->input->post('border_design');
		$border_design = in_array($posted_border, $allowed_borders) ? $posted_border : ($existing_config['border_design'] ?? 'border_1');

		// 8 distinct text layout styles
		$allowed_layouts = [
			'layout_grid_classic',
			'layout_narrative',
			'layout_modern_cards',
			'layout_bullet_secretariat',
			'layout_bordered_table',
			'layout_centered_royal',
			'layout_legal_banner',
			'layout_minimal_dots'
		];
		$posted_layout = $this->input->post('text_layout');
		$text_layout = in_array($posted_layout, $allowed_layouts) ? $posted_layout : ($existing_config['text_layout'] ?? 'layout_grid_classic');

		// Monogram selection
		$allowed_monograms = ['nojrul', 'bangabandhu', 'bd_gov', 'none'];
		$posted_monogram = $this->input->post('top_left_monogram');
		$top_left_monogram = in_array($posted_monogram, $allowed_monograms) ? $posted_monogram : ($existing_config['top_left_monogram'] ?? 'bd_gov');

		$config = array(
			'design_mode'             => $design_mode,
			'custom_pad_path'         => $custom_pad_path,
			'custom_pad_opacity'      => max(0.1, min(1.0, (float)($this->input->post('custom_pad_opacity') ?: 1.0))),
			'custom_pad_fit'          => in_array($this->input->post('custom_pad_fit'), ['contain', 'cover', 'fill']) ? $this->input->post('custom_pad_fit') : 'cover',
			'theme_color'             => $theme_color,
			'palette'                 => $theme_color,
			'border_style'            => in_array($this->input->post('border_style'), ['double', 'classic', 'modern', 'none']) ? $this->input->post('border_style') : 'double',
			'border_design'           => $border_design,
			'text_layout'             => $text_layout,
			'top_left_monogram'       => $top_left_monogram,
			'font_family'             => in_array($this->input->post('font_family'), ['tiro_bangla', 'kalpurush', 'solaiman_lipi', 'system']) ? $this->input->post('font_family') : ($existing_config['font_family'] ?? 'tiro_bangla'),
			'show_govt_logo'          => $is_true($this->input->post('show_govt_logo')),
			'show_up_logo'            => $is_true($this->input->post('show_up_logo')),
			'up_logo_position'        => in_array($this->input->post('up_logo_position'), ['right', 'left']) ? $this->input->post('up_logo_position') : 'right',
			'watermark_type'          => in_array($this->input->post('watermark_type'), ['bd_gov', 'up_logo']) ? $this->input->post('watermark_type') : 'bd_gov',
			'watermark_visible'       => $is_true($this->input->post('watermark_visible')),
			'watermark_opacity'       => max(0.02, min(0.25, (float)($this->input->post('watermark_opacity') ?: 0.06))),
			'watermark_size'          => max(200, min(500, (int)($this->input->post('watermark_size') ?: 380))),
			'show_barcode'            => $is_true($this->input->post('show_barcode')),
			'show_qr_code'            => $is_true($this->input->post('show_qr_code')),
			'show_member_sign'        => $is_true($this->input->post('show_member_sign')),
			'show_sec_sign'           => $is_true($this->input->post('show_sec_sign')),
			'show_chairman_sign'      => $is_true($this->input->post('show_chairman_sign')),
			'signatory_count'         => in_array($this->input->post('signatory_count'), ['1_person', '2_person', '3_person', 'auto']) ? $this->input->post('signatory_count') : 'auto',
			'header_contact_visible'  => $is_true($this->input->post('header_contact_visible')),
			'citizen_photo_position'  => in_array($this->input->post('citizen_photo_position'), ['under_title', 'left', 'right', 'none']) ? $this->input->post('citizen_photo_position') : 'under_title',
			'footer_text'             => trim($this->input->post('footer_text') ?: 'ডিজিটাল ইউনিয়ন পরিষদ সেবা')
		);

		if ($theme_color === 'indigo') {
			$config['primary_color'] = '#1e3a8a';
			$config['border_color'] = '#1e3a8a';
		} elseif ($theme_color === 'slate') {
			$config['primary_color'] = '#1e293b';
			$config['border_color'] = '#334155';
		} elseif ($theme_color === 'crimson') {
			$config['primary_color'] = '#881337';
			$config['border_color'] = '#881337';
		} elseif ($theme_color === 'custom') {
			$custom_primary = trim($this->input->post('primary_color') ?: '#2563eb');
			$custom_border = trim($this->input->post('border_color') ?: $custom_primary);
			if (!preg_match('/^#[a-fA-F0-9]{3,8}$/', $custom_primary)) {
				$custom_primary = '#2563eb';
			}
			if (!preg_match('/^#[a-fA-F0-9]{3,8}$/', $custom_border)) {
				$custom_border = $custom_primary;
			}
			$config['primary_color'] = $custom_primary;
			$config['border_color'] = $custom_border;
		} else { // emerald
			$config['primary_color'] = '#065f46';
			$config['border_color'] = '#065f46';
		}

		$this->SettingModel->updateTenantSonodThemeConfig($tenant_id, $config);

		$custom_pad_url = !empty($custom_pad_path) ? media_url($custom_pad_path) : '';

		$this->output->set_content_type('application/json')->set_output(json_encode([
			'status'         => 'success',
			'message'        => 'সনদ থিম ও কাস্টম ডিজাইন সেটিংস সফলভাবে সংরক্ষিত হয়েছে।',
			'config'         => $config,
			'custom_pad_url' => $custom_pad_url
		]));
	}

	public function get_global_single_sonod_by_id()
	{
		$sonod_id = $this->input->post('sonod_id');

		if (strpos((string)$sonod_id, 'custom_') === 0) {
			$custom_id = (int)substr($sonod_id, 7);
			$custom = $this->sonodM->getTenantCustomSonodById($custom_id, tenant_code());
			if ($custom) {
				$this->data['pi'] = (object) array(
					'deft_cer_all_au_iddd' => $custom->deft_cer_all_id ?: 1,
					'certify_titles'       => $custom->certf_head_texts,
					'certify_title_text_s' => $custom->cert_title_ss,
					'certify_detail_ss'    => $custom->certf_detail_s_sett,
					'en_bn_type'           => $custom->en_bn_type ?: 1,
					'is_custom'            => 1,
					'custom_id'            => $custom->certifys_default_ins_ss_iiidddd
				);
				$this->output->set_content_type('application/json')->set_output(json_encode($this->data));
				return;
			}
		}

		$gid = (int)$sonod_id;
		$override = $this->sonodM->getTenantCustomSonodByGlobalId($gid, tenant_code());
		if ($override) {
			$this->data['pi'] = (object) array(
				'deft_cer_all_au_iddd' => $gid,
				'certify_titles'       => $override->certf_head_texts,
				'certify_title_text_s' => $override->cert_title_ss,
				'certify_detail_ss'    => $override->certf_detail_s_sett,
				'en_bn_type'           => $override->en_bn_type ?: 1,
				'is_custom'            => 1,
				'custom_id'            => $override->certifys_default_ins_ss_iiidddd
			);
			$this->output->set_content_type('application/json')->set_output(json_encode($this->data));
			return;
		}

		$this->data['pi'] = $this->sonodM->get_global_single_sonod_by_sonod_id($gid);
		$this->output->set_content_type('application/json')->set_output(json_encode($this->data));
	}

	public function sonod_genaret_method_view()
	{
		$this->data['all_sonod'] = $this->sonodM->getAllGlobalSonod();
		$this->data['custom_sonods'] = $this->sonodM->getTenantCustomSonods(tenant_code());
		$this->template->sonod('sonod/create_new_sonods_view_file', $this->data); 
	}

	public function verify_this_sonod_person_nid_infos()
	{
		$dta = $this->getapi->m_api_data($this->input->post('nid'), $this->input->post('dob'), $this->data['loggedUser']['userUniqs']); 
		echo $dta;
	}

	public function insert_new_sonod_details_persons()
	{ 
		$raw_gid = $this->input->post('GID');
		if (empty($raw_gid)) { 
			echo 0;
			return;
		}

		$raw_vtype = $this->input->post('vType');
		$raw_vid   = $this->input->post('vID');
		$vType = !empty($raw_vtype) ? $raw_vtype : 'nid';
		$vID   = ($raw_vid !== NULL && $raw_vid !== '') ? (int)$raw_vid : 0;
		$id_number = trim((string)($this->input->post('id_number') ?: ($this->input->post('nid_number') ?: '')));

		// Update or ensure person_verify_nid_data_info_saving row
		$v_payload = array(
			'name_bn'        => (string)$this->input->post('namebn'),
			'name_en'        => (string)$this->input->post('nameen'),
			'f_name_bn'      => (string)$this->input->post('fatherbn'),
			'f_name_en'      => (string)$this->input->post('fatheren'),
			'm_name_bn'      => (string)$this->input->post('motherbn'),
			'm_name_en'      => (string)$this->input->post('motheren'),
			'ins_wardno'     => (string)($this->input->post('wardno') ?: ''),
			'verify_ins_ids' => (string)tenant_code(),
			'verify_thro'    => $vType,
			'verify_times'   => (string)time(),
			'verify_dates'   => date('Y-m-d'),
			'verify_user_id' => (int)($this->user_id ?: 1)
		);
		if ($id_number !== '') {
			if (strlen($id_number) == 10) {
				$v_payload['nid_num10'] = $id_number;
			} else {
				$v_payload['nid_no17']  = $id_number;
			}
		}

		if ($vID > 0 && $this->db->where('person_v_id', $vID)->count_all_results('person_verify_nid_data_info_saving') > 0) {
			$this->db->where('person_v_id', $vID)->update('person_verify_nid_data_info_saving', $v_payload);
		} else {
			$this->db->insert('person_verify_nid_data_info_saving', $v_payload);
			$vID = (int)$this->db->insert_id();
		}

		$dt = $this->sonodM->get_sonod_by_todat(date('Y-m-d')); 
		if (empty($dt)) {
			$sonod_id = date('Ymd').tenant_code().'00001';
		}else {
			$sonod_id = $dt->cgp_pr_idiidid_pr + 1;
		}
		$uniq = uniqid();
		$this->sonodM->insert_new_sonod_person_info(array(
			"ver_per_info_name_bn"				=> $this->input->post('namebn'),
			"ver_per_info_name_en"				=> $this->input->post('nameen'),
			"ver_per_info_father_name_en"		=> $this->input->post('fatheren'),
			"ver_per_info_father_name_bn"		=> $this->input->post('fatherbn'),
			"ver_per_info_mother_name_en"		=> $this->input->post('motheren'),
			"ver_per_info_mother_name_bn"		=> $this->input->post('motherbn'),
			"ver_per_nid_autopr_ididi"			=> $vID,
			"ver_per_info_home_id"				=> (int)$this->input->post('homeid'),
			"ver_per_info_villag_pr_auto_idid"	=> (int)$this->input->post('villid'),
			"ver_per_info_ward_no"				=> (int)$this->input->post('wardno'),
			"ver_per_info_sonod_uniqs"			=> $uniq,
			"tim_es_tmp"						=> time(),
			"ver_per_info_verify_type"			=> $vType,
			"custom_village_name"               => $this->input->post('custom_village') ?: null,
			"custom_house_name"                 => $this->input->post('custom_house') ?: null,
		));

		$custom_id = (int)$this->input->post('custom_id');
		$gid = 1;
		if (strpos((string)$raw_gid, 'custom_') === 0) {
			$custom_id = (int)substr($raw_gid, 7);
			$gid = 1;
		} else {
			$gid = (int)$raw_gid;
		}
		$lang_type = (int)($this->input->post('langType') ?: 1);

		$this->sonodM->insert_new_sonod_gen(array(
			"cgp_pr_idiidid_pr" 				=> $sonod_id,
			"deft_cer_aididid"    				=> $gid,
			"certificate_title_head_text"    	=> $this->input->post('title'),
			"sonod_details_html" 				=> $this->input->post('data'),
			"sonod_global_verify_uids" 			=> $vID,
			"s_person_verify_types" 			=> $vType,
			"insididid_pridididid" 				=> tenant_code(),
			"gen_usr_ididd" 					=> $this->user_id,
			"timeing_stmpsss" 					=> time(),
			"dating_stampingsss" 				=> date('Y-m-d'),
			"sonod_uniqs" 						=> $uniq,
			"thisins_cert_defaults_p_idididid"  => $custom_id ?: null,
			"en_bn_type"                        => $lang_type
		)); 

			// Save uploaded citizen photo if provided
			$raw_photo = (string)$this->input->post('person_photo');
			if (!empty($raw_photo) && strpos($raw_photo, 'data:image/') === 0) {
				$tenant_dir = FCPATH . 'uploads/tenant-' . (int)tenant_code() . '/';
				if (!is_dir($tenant_dir)) {
					@mkdir($tenant_dir, 0755, true);
				}
				$comma_pos = strpos($raw_photo, ',');
				if ($comma_pos !== false) {
					$img_data = base64_decode(substr($raw_photo, $comma_pos + 1));
					if ($img_data !== false && strlen($img_data) > 100) {
						@file_put_contents($tenant_dir . 'citizen-' . $uniq . '.png', $img_data);
					}
				}
			}

			if (function_exists('audit_log')) {
				audit_log('create', 'sonod', array(
					'entity_id' => (string)$uniq,
					'tenant_id' => (int)tenant_code(),
					'summary'   => 'নতুন সনদপত্র ইস্যু করা হয়েছে: ' . ($this->input->post('title') ?: 'সাধারণ সনদ') . ' — আবেদনকারী: ' . ($this->input->post('namebn') ?: $this->input->post('nameen')) . ' (সনদ নং: ' . $sonod_id . ')',
					'after'     => array(
						'sonod_no'    => $sonod_id,
						'tracking_id' => $uniq,
						'title'       => (string)$this->input->post('title'),
						'name_bn'     => (string)$this->input->post('namebn'),
						'name_en'     => (string)$this->input->post('nameen'),
						'father_bn'   => (string)$this->input->post('fatherbn'),
						'mother_bn'   => (string)$this->input->post('motherbn'),
						'ward_no'     => (string)$this->input->post('wardno'),
						'village'     => (string)($this->input->post('custom_village') ?: ''),
						'house'       => (string)($this->input->post('custom_house') ?: ''),
						'verify_type' => $vType,
						'id_number'   => $id_number
					)
				));
			}

			echo $uniq;
	}

	public function insert_new_warish_sonod_created_func()
	{
		/*
		 	data
			title
			namebn
			nameen
			fatherbn
			fatheren
			motherbn
			motheren
			wardno
			villid
			homeid
			vID
			vType
			GID
			deadnid
			deaddob
			deadnamebn
			deaddate
			deadgender
			deadrelation
			deadcareer
			deadfatherbn
			deadmotherbn
			w_name[]
			w_relation[]
			w_nidbrn[]
			w_age_date[] 
		 */
		$raw_gid = $this->input->post('GID');
		if (empty($raw_gid) || empty($this->input->post('deadnamebn')) || empty($this->input->post('deaddate'))) { 
			echo 0;
			return;
		}

		$raw_vtype = $this->input->post('vType');
		$raw_vid   = $this->input->post('vID');
		$vType = !empty($raw_vtype) ? $raw_vtype : 'nid';
		$vID   = ($raw_vid !== NULL && $raw_vid !== '') ? (int)$raw_vid : 0;
		$id_number = trim((string)($this->input->post('id_number') ?: ($this->input->post('nid_number') ?: '')));

		// Update or ensure person_verify_nid_data_info_saving row
		$v_payload = array(
			'name_bn'        => (string)$this->input->post('namebn'),
			'name_en'        => (string)$this->input->post('nameen'),
			'f_name_bn'      => (string)$this->input->post('fatherbn'),
			'f_name_en'      => (string)$this->input->post('fatheren'),
			'm_name_bn'      => (string)$this->input->post('motherbn'),
			'm_name_en'      => (string)$this->input->post('motheren'),
			'ins_wardno'     => (string)($this->input->post('wardno') ?: ''),
			'verify_ins_ids' => (string)tenant_code(),
			'verify_thro'    => $vType,
			'verify_times'   => (string)time(),
			'verify_dates'   => date('Y-m-d'),
			'verify_user_id' => (int)($this->user_id ?: 1)
		);
		if ($id_number !== '') {
			if (strlen($id_number) == 10) {
				$v_payload['nid_num10'] = $id_number;
			} else {
				$v_payload['nid_no17']  = $id_number;
			}
		}

		if ($vID > 0 && $this->db->where('person_v_id', $vID)->count_all_results('person_verify_nid_data_info_saving') > 0) {
			$this->db->where('person_v_id', $vID)->update('person_verify_nid_data_info_saving', $v_payload);
		} else {
			$this->db->insert('person_verify_nid_data_info_saving', $v_payload);
			$vID = (int)$this->db->insert_id();
		}

		$dt = $this->sonodM->get_sonod_by_todat(date('Y-m-d')); 
		if (empty($dt)) {
			$sonod_id = date('Ymd').tenant_code().'00001';
		} else {
			$sonod_id = (string)((int)$dt->cgp_pr_idiidid_pr + 1);
		}
		$uniq = uniqid();
		$this->sonodM->insert_new_sonod_person_info(array(
			"ver_per_info_name_bn"				=> $this->input->post('namebn'),
			"ver_per_info_name_en"				=> $this->input->post('nameen'),
			"ver_per_info_father_name_en"		=> $this->input->post('fatheren'),
			"ver_per_info_father_name_bn"		=> $this->input->post('fatherbn'),
			"ver_per_info_mother_name_en"		=> $this->input->post('motheren'),
			"ver_per_info_mother_name_bn"		=> $this->input->post('motherbn'),
			"ver_per_nid_autopr_ididi"			=> $vID,
			"ver_per_info_home_id"				=> (int)$this->input->post('homeid'),
			"ver_per_info_villag_pr_auto_idid"	=> (int)$this->input->post('villid'),
			"ver_per_info_ward_no"				=> (int)$this->input->post('wardno'),
			"ver_per_info_sonod_uniqs"			=> $uniq,
			"tim_es_tmp"						=> time(),
			"ver_per_info_verify_type"			=> $vType,
			"custom_village_name"               => $this->input->post('custom_village') ?: null,
			"custom_house_name"                 => $this->input->post('custom_house') ?: null,
		));
		$last_insert_no = $this->sonodM->insert_new_sonod_gen(array(
			"cgp_pr_idiidid_pr" 				=> $sonod_id,
			"deft_cer_aididid"    				=> $this->input->post('GID'),
			"certificate_title_head_text"    	=> $this->input->post('title'),
			"sonod_details_html" 				=> $this->input->post('data'),
			"sonod_global_verify_uids" 			=> $vID,
			"s_person_verify_types" 			=> $vType,
			"insididid_pridididid" 				=> tenant_code(),
			"gen_usr_ididd" 					=> $this->user_id,
			"timeing_stmpsss" 					=> time(),
			"dating_stampingsss" 				=> date('Y-m-d'),
			"sonod_uniqs" 						=> $uniq
		)); 
			$this->sonodM->insert_new_warish_person_information(array(
				"warish_person_bn_name" 			=> $this->input->post('deadnamebn'),
				"warish_father_bn" 					=> $this->input->post('deadfatherbn'),
				"warish_mother_bn" 					=> $this->input->post('deadmotherbn'),
				"warish_nid_no" 					=> $this->input->post('deadnid'),
				"warish_dob" 						=> $this->input->post('deaddob'),
				"warish_dead_date" 					=> $this->input->post('deaddate'),
				"warish_gander" 					=> $this->input->post('deadgender'),
				"warish_rel_status" 				=> $this->input->post('deadrelation'),
				"warish_careers" 					=> $this->input->post('deadcareer'),
				"ward_noo" 							=> $this->input->post('deadwardno'),
				"gramer_namer_id" 					=> (int)$this->input->post('deadvillid'),
				"barir_namer_id" 					=> (int)$this->input->post('deadhomeid'),
				"warish_dead_certify_no" 			=> $this->input->post('deadcertifyno'),
				"time_sstmpss" 						=> time(),
				"sonod_gns_id" 						=> $last_insert_no,
				"sonodss_uniqss" 					=> $uniq,
				"time_date_entrys_nowss" 			=> date('Y-m-d')
			)); 

			
		$raw_w_name     = $this->input->post('w_name');
		$raw_w_relation = $this->input->post('w_relation');
		$raw_w_nidbrn   = $this->input->post('w_nidbrn');
		$raw_w_age_date = $this->input->post('w_age_date');

		// Flatten array in case submitted as array of array
		$w_name = is_array($raw_w_name) ? (is_array(reset($raw_w_name)) ? reset($raw_w_name) : $raw_w_name) : array();
		$w_relation = is_array($raw_w_relation) ? (is_array(reset($raw_w_relation)) ? reset($raw_w_relation) : $raw_w_relation) : array();
		$w_nidbrn = is_array($raw_w_nidbrn) ? (is_array(reset($raw_w_nidbrn)) ? reset($raw_w_nidbrn) : $raw_w_nidbrn) : array();
		$w_age_date = is_array($raw_w_age_date) ? (is_array(reset($raw_w_age_date)) ? reset($raw_w_age_date) : $raw_w_age_date) : array();

		$data = [];
		for ($i = 0; $i < count($w_name); $i++) {
			$name = trim((string)($w_name[$i] ?? ''));
			if ($name !== '') {
				$data[] = [
					'names_per'            => $name,
					'relation_pers'        => trim((string)($w_relation[$i] ?? '')),
					'nid_pers'             => trim((string)($w_nidbrn[$i] ?? '')),
					'dob_pers'             => trim((string)($w_age_date[$i] ?? '')),
					'gen_sonod_pr_idids'   => $last_insert_no,
					'tim_ess_t_mp'         => time(),
					'sonod_uniq_id_gensss' => $uniq,
				];
			}
		}

		if (!empty($data)) {
			$this->sonodM->insert_warish_rel_information_new_per($data);
		}

		// Save uploaded citizen photo if provided
		$raw_photo = (string)$this->input->post('person_photo');
		if (!empty($raw_photo) && strpos($raw_photo, 'data:image/') === 0) {
			$tenant_dir = FCPATH . 'uploads/tenant-' . (int)tenant_code() . '/';
			if (!is_dir($tenant_dir)) {
				@mkdir($tenant_dir, 0755, true);
			}
			$comma_pos = strpos($raw_photo, ',');
			if ($comma_pos !== false) {
				$img_data = base64_decode(substr($raw_photo, $comma_pos + 1));
				if ($img_data !== false && strlen($img_data) > 100) {
					@file_put_contents($tenant_dir . 'citizen-' . $uniq . '.png', $img_data);
				}
			}
		}

		if (function_exists('audit_log')) {
			audit_log('create', 'warish_sonod', array(
				'entity_id' => (string)$uniq,
				'tenant_id' => (int)tenant_code(),
				'summary'   => 'নতুন ওয়ারিশ সনদ ইস্যু করা হয়েছে — মৃত ব্যক্তি: ' . $this->input->post('deadnamebn') . ' (ওয়ারিশ সংখ্যা: ' . count($data) . ' জন, সনদ নং: ' . $sonod_id . ')',
				'after'     => array(
					'sonod_no'        => $sonod_id,
					'tracking_id'     => $uniq,
					'dead_name_bn'    => (string)$this->input->post('deadnamebn'),
					'dead_father_bn'  => (string)$this->input->post('deadfatherbn'),
					'dead_mother_bn'  => (string)$this->input->post('deadmotherbn'),
					'dead_nid'        => (string)$this->input->post('deadnid'),
					'dead_date'       => (string)$this->input->post('deaddate'),
					'ward_no'         => (string)$this->input->post('deadwardno'),
					'applicant_name'  => (string)$this->input->post('namebn'),
					'warish_count'    => count($data),
					'warish_members'  => $data
				)
			));
		}

		echo $uniq;
	}

	public function insert_new_trade_license_created_func()
	{
		$raw_gid = $this->input->post('GID');
		$gid = (!empty($raw_gid) && (int)$raw_gid > 0) ? (int)$raw_gid : 3;

		$raw_vtype = $this->input->post('vType');
		$raw_vid   = $this->input->post('vID');
		$vType = !empty($raw_vtype) ? $raw_vtype : 'nid';
		$vID   = ($raw_vid !== NULL && $raw_vid !== '') ? (int)$raw_vid : 0;
		$id_number = trim((string)($this->input->post('id_number') ?: ($this->input->post('nid_number') ?: '')));

		// 1. Update or ensure person_verify_nid_data_info_saving row
		$v_payload = array(
			'name_bn'        => (string)$this->input->post('namebn'),
			'name_en'        => (string)$this->input->post('nameen'),
			'f_name_bn'      => (string)$this->input->post('fatherbn'),
			'f_name_en'      => (string)$this->input->post('fatheren'),
			'm_name_bn'      => (string)$this->input->post('motherbn'),
			'm_name_en'      => (string)$this->input->post('motheren'),
			'ins_wardno'     => (string)($this->input->post('wardno') ?: ''),
			'verify_ins_ids' => (string)tenant_code(),
			'verify_thro'    => $vType,
			'verify_times'   => (string)time(),
			'verify_dates'   => date('Y-m-d'),
			'verify_user_id' => (int)($this->user_id ?: 1)
		);
		if ($id_number !== '') {
			if (strlen($id_number) == 10) {
				$v_payload['nid_num10'] = $id_number;
			} else {
				$v_payload['nid_no17']  = $id_number;
			}
		}

		if ($vID > 0 && $this->db->where('person_v_id', $vID)->count_all_results('person_verify_nid_data_info_saving') > 0) {
			$this->db->where('person_v_id', $vID)->update('person_verify_nid_data_info_saving', $v_payload);
		} else {
			$this->db->insert('person_verify_nid_data_info_saving', $v_payload);
			$vID = (int)$this->db->insert_id();
		}

		// 2. Generate unique sonod numbers
		$dt = $this->sonodM->get_sonod_by_todat(date('Y-m-d')); 
		if (empty($dt)) {
			$sonod_id = date('Ymd').tenant_code().'00001';
		} else {
			$sonod_id = (string)((int)$dt->cgp_pr_idiidid_pr + 1);
		}
		$uniq = uniqid();

		// 3. Insert person info
		$this->sonodM->insert_new_sonod_person_info(array(
			"ver_per_info_name_bn"              => $this->input->post('namebn'),
			"ver_per_info_name_en"              => $this->input->post('nameen'),
			"ver_per_info_father_name_bn"       => $this->input->post('fatherbn'),
			"ver_per_info_father_name_en"       => $this->input->post('fatheren'),
			"ver_per_info_mother_name_bn"       => $this->input->post('motherbn'),
			"ver_per_info_mother_name_en"       => $this->input->post('motheren'),
			"ver_per_nid_autopr_ididi"          => $vID,
			"ver_per_info_home_id"              => (int)$this->input->post('homeid'),
			"ver_per_info_villag_pr_auto_idid"  => (int)$this->input->post('villid'),
			"ver_per_info_ward_no"              => (int)$this->input->post('wardno'),
			"ver_per_info_sonod_uniqs"          => $uniq,
			"tim_es_tmp"                        => time(),
			"ver_per_info_verify_type"          => $vType,
			"custom_village_name"               => $this->input->post('custom_village') ?: null,
			"custom_house_name"                 => $this->input->post('custom_house') ?: null,
		));

		// 4. Insert into sonod_genarates
		$lang_type = ($gid == 1230 || (int)$this->input->post('langType') == 2) ? 2 : 1;
		$title = trim((string)$this->input->post('title'));
		if (empty($title)) {
			$title = ($lang_type == 2) ? 'Trade License Certificate' : 'ট্রেড লাইসেন্স সনদ';
		}

		$last_insert_no = $this->sonodM->insert_new_sonod_gen(array(
			"cgp_pr_idiidid_pr"           => $sonod_id,
			"deft_cer_aididid"            => $gid,
			"certificate_title_head_text" => $title,
			"sonod_details_html"          => $this->input->post('data') ?: '',
			"sonod_global_verify_uids"    => $vID,
			"s_person_verify_types"       => $vType,
			"insididid_pridididid"        => tenant_code(),
			"gen_usr_ididd"               => $this->user_id,
			"timeing_stmpsss"             => time(),
			"dating_stampingsss"          => date('Y-m-d'),
			"sonod_uniqs"                 => $uniq,
			"en_bn_type"                  => $lang_type
		));

		// 5. Insert into tread_license_tbl_data
		$biz_name       = trim((string)$this->input->post('business_ins_name'));
		$biz_type       = trim((string)$this->input->post('business_types'));
		$muldhon        = trim((string)$this->input->post('muldhon'));
		$fiscal_year    = trim((string)$this->input->post('license_yearss')) ?: (date('Y') . '-' . (date('Y') + 1));
		$fee_yearly     = trim((string)$this->input->post('license_fee_yearly')) ?: '২০০/-';
		$fee_vat        = trim((string)$this->input->post('license_fee_vats')) ?: '৩০/-';
		$fee_tax        = trim((string)$this->input->post('pesha_bebsa_o_kor')) ?: '৭৭০/-';
		$adds_texts     = trim((string)$this->input->post('adds_texts')) ?: '';
		$bokeya         = trim((string)$this->input->post('bokeya')) ?: '';
		$services       = trim((string)$this->input->post('services_charge')) ?: '';
		$total_charge   = trim((string)$this->input->post('total_charge')) ?: '১০০০/-';
		$issue_date     = trim((string)$this->input->post('license_issue_date')) ?: date('d-m-Y');
		$expire_date    = trim((string)$this->input->post('license_expires_date')) ?: ('30-06-' . (date('Y') + 1));
		$person_mobile  = trim((string)$this->input->post('person_mobile_no')) ?: trim((string)$this->input->post('mobile'));

		$trade_payload = array(
			'sonod_no_gens'         => $sonod_id,
			'id_institute_pr'       => (int)tenant_code(),
			'business_ins_name'     => $biz_name,
			'person_mobile_no'      => $person_mobile,
			'business_types'        => $biz_type,
			'muldhon'               => $muldhon,
			'license_issue_date'    => $issue_date,
			'license_expires_date'  => $expire_date,
			'license_text_business' => $biz_type,
			'license_fee_yearly'    => $fee_yearly,
			'license_fee_vats'      => $fee_vat,
			'pesha_bebsa_o_kor'     => $fee_tax,
			'adds_texts'            => $adds_texts,
			'bokeya'                => $bokeya,
			'services_charge'       => $services,
			'total_charge'          => $total_charge,
			'license_yearss'        => $fiscal_year,
			'sonod_uniq_id'         => $uniq,
			'sonod_gen_primary_id'  => $last_insert_no,
			'times'                 => (string)time(),
			'inserted_datesss'      => date('Y-m-d'),
			'insertdd_userssss'     => (int)$this->user_id
		);

		$this->sonodM->insert_trade_license_data($trade_payload);

		// Save uploaded citizen photo if provided
		$raw_photo = (string)$this->input->post('person_photo');
		if (!empty($raw_photo) && strpos($raw_photo, 'data:image/') === 0) {
			$tenant_dir = FCPATH . 'uploads/tenant-' . (int)tenant_code() . '/';
			if (!is_dir($tenant_dir)) {
				@mkdir($tenant_dir, 0755, true);
			}
			$comma_pos = strpos($raw_photo, ',');
			if ($comma_pos !== false) {
				$img_data = base64_decode(substr($raw_photo, $comma_pos + 1));
				if ($img_data !== false && strlen($img_data) > 100) {
					@file_put_contents($tenant_dir . 'citizen-' . $uniq . '.png', $img_data);
				}
			}
		}

		if (function_exists('audit_log')) {
			audit_log('create', 'trade_license', array(
				'entity_id' => (string)$uniq,
				'tenant_id' => (int)tenant_code(),
				'summary'   => 'নতুন ট্রেড লাইসেন্স ইস্যু করা হয়েছে — প্রতিষ্ঠান: ' . $biz_name . ' (মালিক: ' . $this->input->post('namebn') . ', লাইসেন্স নং: ' . $sonod_id . ')',
				'after'     => array(
					'sonod_no'       => $sonod_id,
					'tracking_id'    => $uniq,
					'business_name'  => $biz_name,
					'owner_name_bn'  => (string)$this->input->post('namebn'),
					'owner_name_en'  => (string)$this->input->post('nameen'),
					'business_type'  => $biz_type,
					'capital'        => $muldhon,
					'fiscal_year'    => $fiscal_year,
					'total_fee'      => $total_charge,
					'mobile'         => $person_mobile
				)
			));
		}

		echo $uniq;
	}

	public function sonod_list_view()
	{
		$tenant_id = (int) tenant_code();
		$this->data['sonod_list'] = $this->sonodM->get_all_generated_sonods_by_tenant($tenant_id);
		$this->data['total_count'] = $this->sonodM->count_all_generated_sonods_by_tenant($tenant_id);
		$this->template->back('sonod/sonod_list_view_file', $this->data);
	}

	public function save_server_draft()
	{
		$tenant_id = (int) tenant_code();
		$payload_raw = $this->input->post('payload');
		$data = array(
			'id'                => (int)$this->input->post('id'),
			'step_number'       => (int)($this->input->post('step_number') ?: 1),
			'draft_title'       => trim($this->input->post('draft_title') ?: 'খসড়া সনদ'),
			'applicant_name'    => trim($this->input->post('applicant_name') ?: ''),
			'applicant_nid_brn' => trim($this->input->post('applicant_nid_brn') ?: ''),
			'sonod_type_id'     => trim($this->input->post('sonod_type_id') ?: ''),
			'payload_json'      => $payload_raw ?: '{}',
		);

		$draft_id = $this->sonodM->save_draft($tenant_id, $this->user_id, $data);
		$this->output->set_content_type('application/json')->set_output(json_encode([
			'status'   => 'success',
			'message'  => 'খসড়া সফলভাবে সার্ভারে সংরক্ষিত হয়েছে।',
			'draft_id' => $draft_id,
			'saved_at' => date('d M Y, h:i A')
		]));
	}

	public function get_server_draft($id = null)
	{
		$tenant_id = (int) tenant_code();
		$draft_id = (int)($id ?: $this->input->get_post('id'));
		$draft = $this->sonodM->get_draft_by_id($draft_id, $tenant_id);
		if (!$draft) {
			$this->output->set_content_type('application/json')->set_output(json_encode([
				'status'  => 'error',
				'message' => 'খসড়া পাওয়া যায়নি।'
			]));
			return;
		}

		$this->output->set_content_type('application/json')->set_output(json_encode([
			'status' => 'success',
			'draft'  => $draft
		]));
	}

	public function delete_server_draft()
	{
		$tenant_id = (int) tenant_code();
		$draft_id = (int)$this->input->post('id');
		if ($draft_id > 0) {
			$this->sonodM->delete_draft($draft_id, $tenant_id);
			if (function_exists('audit_log')) {
				audit_log('delete', 'sonod_draft', array(
					'entity_id' => (string)$draft_id,
					'tenant_id' => $tenant_id,
					'summary'   => 'সনদ ড্রাফট মুছে ফেলা হয়েছে (ড্রাফট ID: #' . $draft_id . ')'
				));
			}
			$this->output->set_content_type('application/json')->set_output(json_encode([
				'status'  => 'success',
				'message' => 'খসড়া সফলভাবে মুছে ফেলা হয়েছে।'
			]));
			return;
		}
		$this->output->set_content_type('application/json')->set_output(json_encode([
			'status'  => 'error',
			'message' => 'অবৈধ অনুরোধ।'
		]));
	}

	public function drafts_list_view()
	{
		$tenant_id = (int) tenant_code();
		$this->data['drafts_list'] = $this->sonodM->get_tenant_drafts($tenant_id);
		$this->template->back('sonod/drafts_list_view_file', $this->data);
	}

	/**
	 * Edit Issued Sonod View & Permission Gate
	 */
	public function edit_issued_sonod()
	{
		$tenant_id = (int) tenant_code();
		$id = $this->input->get('id') ?: $this->input->post('id');

		if (empty($id)) {
			$this->session->set_flashdata('error', 'কোনো সনদ নির্বাচন করা হয়নি।');
			redirect('sonod_list');
			return;
		}

		$sonod = $this->sonodM->get_issued_sonod_for_edit($id, $tenant_id);
		if (!$sonod) {
			$this->session->set_flashdata('error', 'সনদটি খুঁজে পাওয়া যায়নি বা আপনার ইউনিয়নে অনুমোদিত নয়।');
			redirect('sonod_list');
			return;
		}

		// Role-based authorization filter (No uphead/upsocib approval required)
		$allowed_edit_roles = array('admin', 'uphead', 'upsocib', 'udc', 'sadmin');
		$can_edit = $this->ion_auth->in_group($allowed_edit_roles);

		// Resolve human-readable role name for UI badge
		$user_role_label = 'ইউজার';
		if ($this->ion_auth->in_group('sadmin')) {
			$user_role_label = 'সুপার অ্যাডমিন';
		} elseif ($this->ion_auth->in_group('uphead')) {
			$user_role_label = 'ইউপি চেয়ারম্যান';
		} elseif ($this->ion_auth->in_group('upsocib')) {
			$user_role_label = 'ইউপি সচিব';
		} elseif ($this->ion_auth->in_group('admin')) {
			$user_role_label = 'ইউপি অ্যাডমিনিস্ট্রেটর';
		} elseif ($this->ion_auth->in_group('udc')) {
			$user_role_label = 'ইউনিয়ন ডিজিটাল সেন্টার (UDC)';
		} elseif ($this->ion_auth->in_group('collect')) {
			$user_role_label = 'ট্যাক্স সংগ্রাহক';
		}

		// Fetch warish list if exists
		$warish_list = $this->sonodM->get_warish_persons_by_sonod_uniq($sonod->sonod_uniqs);
		$trade_data  = $this->sonodM->get_trade_license_data($sonod->sonod_uniqs);

		$this->data['sonod']                   = $sonod;
		$this->data['can_edit']                = $can_edit;
		$this->data['user_role_label']         = $user_role_label;
		$this->data['allowed_edit_roles']      = $allowed_edit_roles;
		$this->data['warish_list']             = $warish_list;
		$this->data['trade_data']              = $trade_data;

		$this->template->back('sonod/edit_issued_sonod_view_file', $this->data);
	}

	/**
	 * Request edit permission for an issued sonod (creates pending row in approvals)
	 */
	public function request_edit_permission()
	{
		$tenant_id = (int) tenant_code();
		$id = trim((string)$this->input->post('id'));
		$reason = trim((string)$this->input->post('reason'));

		if (empty($reason)) {
			$reason = 'সনদের প্রয়োজনীয় তথ্য সংশোধন ও পুনঃসম্পাদনা';
		}

		if (empty($id)) {
			$this->output->set_content_type('application/json')->set_output(json_encode([
				'ok'  => FALSE,
				'msg' => 'সনদ আইডি পাওয়া যায়নি।'
			]));
			return;
		}

		$sonod = $this->sonodM->get_issued_sonod_for_edit($id, $tenant_id);
		if (!$sonod) {
			$this->output->set_content_type('application/json')->set_output(json_encode([
				'ok'  => FALSE,
				'msg' => 'সনদটি খুঁজে পাওয়া যায়নি।'
			]));
			return;
		}

		// Check if already approved
		$has_approved = $this->sonodM->check_approved_sonod_edit_permission($sonod->sonod_gen_ididid, $sonod->sonod_uniqs, $tenant_id);
		if ($has_approved) {
			$this->output->set_content_type('application/json')->set_output(json_encode([
				'ok'  => TRUE,
				'msg' => 'এই সনদের তথ্য সম্পাদনার অনুমতি ইতোমধ্যে অনুমোদিত রয়েছে।'
			]));
			return;
		}

		// Check if already pending
		$pending = $this->sonodM->get_pending_sonod_edit_permission($sonod->sonod_gen_ididid, $sonod->sonod_uniqs, $tenant_id);
		if ($pending) {
			$this->output->set_content_type('application/json')->set_output(json_encode([
				'ok'  => TRUE,
				'msg' => 'এই সনদের জন্য ইতোমধ্যে একটি আবেদন অনুমোদনের অপেক্ষায় রয়েছে।'
			]));
			return;
		}

		$payload = array(
			'sonod_id'     => (string)$sonod->sonod_gen_ididid,
			'sonod_uniqs'  => (string)$sonod->sonod_uniqs,
			'sonod_no'     => (string)($sonod->cgp_pr_idiidid_pr ?? ''),
			'sonod_title'  => (string)($sonod->certificate_title_head_text ?? 'সনদপত্র'),
			'citizen_name' => (string)($sonod->ver_per_info_name_bn ?? ''),
			'reason'       => $reason,
		);

		$res = approval_propose('sonod.edit', $payload, $reason, $tenant_id);
		if ($res) {
			$this->output->set_content_type('application/json')->set_output(json_encode([
				'ok'  => TRUE,
				'msg' => 'চেয়ারম্যান বা সচিব মহোদয়ের অনুমোদনের জন্য সফলভাবে আবেদন পাঠানো হয়েছে।'
			]));
		} else {
			$this->output->set_content_type('application/json')->set_output(json_encode([
				'ok'  => FALSE,
				'msg' => 'আবেদন পাঠানোর সময় ত্রুটি ঘটেছে বা আপনার আবেদন করার অনুমতি নেই।'
			]));
		}
	}

	/**
	 * Instant inline approval for Chairman/Secretary/Admin on edit page
	 */
	public function approve_edit_permission_inline()
	{
		$tenant_id = (int) tenant_code();
		$id = trim((string)$this->input->post('id'));
		$is_authority = $this->ion_auth->in_group(array('uphead', 'upsocib', 'admin', 'sadmin'));

		if (!$is_authority) {
			$this->output->set_content_type('application/json')->set_output(json_encode([
				'ok'  => FALSE,
				'msg' => 'আপনার এই আবেদন অনুমোদন করার কর্তৃত্ব নেই।'
			]));
			return;
		}

		$sonod = $this->sonodM->get_issued_sonod_for_edit($id, $tenant_id);
		if (!$sonod) {
			$this->output->set_content_type('application/json')->set_output(json_encode([
				'ok'  => FALSE,
				'msg' => 'সনদটি খুঁজে পাওয়া যায়নি।'
			]));
			return;
		}

		$pending = $this->sonodM->get_pending_sonod_edit_permission($sonod->sonod_gen_ididid, $sonod->sonod_uniqs, $tenant_id);
		if (!$pending) {
			$this->output->set_content_type('application/json')->set_output(json_encode([
				'ok'  => FALSE,
				'msg' => 'কোনো অপেক্ষমান আবেদন পাওয়া যায়নি।'
			]));
			return;
		}

		$res = approval_decide((int)$pending->id, 'approved', 'ইউপি চেয়ারম্যান/সচিব কর্তৃক সরাসরি অনুমোদন');
		if ($res['ok']) {
			$this->output->set_content_type('application/json')->set_output(json_encode([
				'ok'  => TRUE,
				'msg' => 'সনদ সম্পাদনার অনুমতি সফলভাবে অনুমোদন করা হয়েছে।'
			]));
		} else {
			$this->output->set_content_type('application/json')->set_output(json_encode([
				'ok'  => FALSE,
				'msg' => 'অনুমোদন কার্যকর করার সময় সমস্যা হয়েছে (' . ($res['why'] ?? 'অজ্ঞাত') . ')'
			]));
		}
	}

	/**
	 * Save/Update edited issued sonod details
	 */
	public function update_issued_sonod()
	{
		$tenant_id = (int) tenant_code();
		$sonod_uniq = trim((string)$this->input->post('sonod_uniqs'));

		if (empty($sonod_uniq)) {
			$this->session->set_flashdata('error', 'অবৈধ সনদ রেফারেন্স।');
			redirect('sonod_list');
			return;
		}

		$sonod = $this->sonodM->get_issued_sonod_for_edit($sonod_uniq, $tenant_id);
		if (!$sonod) {
			$this->session->set_flashdata('error', 'সনদটি পাওয়া যায়নি বা আপনার ইউনিয়নের নয়।');
			redirect('sonod_list');
			return;
		}

		// Role-based authorization filter
		$allowed_edit_roles = array('admin', 'uphead', 'upsocib', 'udc', 'sadmin');
		if (!$this->ion_auth->in_group($allowed_edit_roles)) {
			$this->session->set_flashdata('error', 'আপনার ইউজার রোল অনুযায়ী এই সনদ সম্পাদনা করার অনুমতি নেই।');
			redirect('sonod_list');
			return;
		}

		// Update sonod_genarates
		$gen_data = array(
			'certificate_title_head_text' => trim((string)$this->input->post('certificate_title_head_text') ?: $sonod->certificate_title_head_text),
			'sonod_details_html'          => (string)$this->input->post('sonod_details_html'),
			'dating_stampingsss'          => trim((string)$this->input->post('dating_stampingsss') ?: $sonod->dating_stampingsss),
			'en_bn_type'                  => (int)($this->input->post('en_bn_type') ?: 1),
		);
		$this->sonodM->update_issued_sonod_gen($sonod->sonod_uniqs, $tenant_id, $gen_data);

		// Update certificate_person_verify_info_editing
		$person_data = array(
			'ver_per_info_name_bn'        => trim((string)$this->input->post('ver_per_info_name_bn')),
			'ver_per_info_name_en'        => trim((string)$this->input->post('ver_per_info_name_en')),
			'ver_per_info_father_name_bn' => trim((string)$this->input->post('ver_per_info_father_name_bn')),
			'ver_per_info_father_name_en' => trim((string)$this->input->post('ver_per_info_father_name_en')),
			'ver_per_info_mother_name_bn' => trim((string)$this->input->post('ver_per_info_mother_name_bn')),
			'ver_per_info_mother_name_en' => trim((string)$this->input->post('ver_per_info_mother_name_en')),
			'ver_per_info_ward_no'        => (int)$this->input->post('ver_per_info_ward_no'),
			'custom_village_name'         => trim((string)$this->input->post('custom_village_name')),
			'custom_house_name'           => trim((string)$this->input->post('custom_house_name')),
		);
		$this->sonodM->update_issued_sonod_person_info($sonod->sonod_uniqs, $person_data);

		// Update warish if submitted
		$warish_names = $this->input->post('warish_name');
		if (is_array($warish_names)) {
			$warish_fathers   = $this->input->post('warish_father');
			$warish_relations = $this->input->post('warish_relation');
			$warish_nids      = $this->input->post('warish_nid');
			$warish_dobs      = $this->input->post('warish_dob');
			$warish_rows = array();

			for ($i = 0; $i < count($warish_names); $i++) {
				$w_name = trim((string)($warish_names[$i] ?? ''));
				if ($w_name !== '') {
					$warish_rows[] = array(
						'names_per'            => $w_name,
						'father_pers'          => trim((string)($warish_fathers[$i] ?? '')),
						'relation_pers'        => trim((string)($warish_relations[$i] ?? '')),
						'nid_pers'             => trim((string)($warish_nids[$i] ?? '')),
						'dob_pers'             => trim((string)($warish_dobs[$i] ?? '')),
						'sonod_uniq_id_gensss' => $sonod->sonod_uniqs,
						'gen_sonod_pr_idids'   => (int)$sonod->sonod_gen_ididid,
						'tim_ess_t_mp'         => time(),
					);
				}
			}
			$this->sonodM->update_warish_somod_persons($sonod->sonod_uniqs, $warish_rows);
		}

		// Update trade license data if submitted or if this is a trade license
		if ($this->input->post('business_ins_name') !== NULL || (int)($sonod->deft_cer_aididid ?? 0) === 3 || (int)($sonod->deft_cer_aididid ?? 0) === 1230) {
			$trade_edit_payload = array(
				'business_ins_name'     => trim((string)$this->input->post('business_ins_name')),
				'person_mobile_no'      => trim((string)$this->input->post('person_mobile_no')),
				'business_types'        => trim((string)$this->input->post('business_types')),
				'muldhon'               => trim((string)$this->input->post('muldhon')),
				'license_issue_date'    => trim((string)$this->input->post('license_issue_date')),
				'license_expires_date'  => trim((string)$this->input->post('license_expires_date')),
				'license_text_business' => trim((string)$this->input->post('business_types')),
				'license_fee_yearly'    => trim((string)$this->input->post('license_fee_yearly')),
				'license_fee_vats'      => trim((string)$this->input->post('license_fee_vats')),
				'pesha_bebsa_o_kor'     => trim((string)$this->input->post('pesha_bebsa_o_kor')),
				'adds_texts'            => trim((string)$this->input->post('adds_texts')),
				'bokeya'                => trim((string)$this->input->post('bokeya')),
				'services_charge'       => trim((string)$this->input->post('services_charge')),
				'total_charge'          => trim((string)$this->input->post('total_charge')),
				'license_yearss'        => trim((string)$this->input->post('license_yearss')),
				'id_institute_pr'       => (int)$tenant_id,
			);
			$this->sonodM->update_trade_license_data($sonod->sonod_uniqs, $trade_edit_payload);
		}

		// Process citizen photo file upload if provided
		if (!empty($_FILES['citizen_photo_file']['tmp_name']) && is_uploaded_file($_FILES['citizen_photo_file']['tmp_name'])) {
			$tenant_dir = FCPATH . 'uploads/tenant-' . $tenant_id . '/';
			if (!is_dir($tenant_dir)) {
				@mkdir($tenant_dir, 0755, true);
			}
			$dest = $tenant_dir . 'citizen-' . $sonod->sonod_uniqs . '.png';
			move_uploaded_file($_FILES['citizen_photo_file']['tmp_name'], $dest);
		}

		if (function_exists('audit_log')) {
			$before_data = array(
				'title'       => (string)($sonod->certificate_title_head_text ?? ''),
				'name_bn'     => (string)($sonod->ver_per_info_name_bn ?? ''),
				'name_en'     => (string)($sonod->ver_per_info_name_en ?? ''),
				'father_bn'   => (string)($sonod->ver_per_info_father_name_bn ?? ''),
				'father_en'   => (string)($sonod->ver_per_info_father_name_en ?? ''),
				'mother_bn'   => (string)($sonod->ver_per_info_mother_name_bn ?? ''),
				'mother_en'   => (string)($sonod->ver_per_info_mother_name_en ?? ''),
				'ward_no'     => (string)($sonod->ver_per_info_ward_no ?? ''),
				'village'     => (string)($sonod->custom_village_name ?? ''),
				'house'       => (string)($sonod->custom_house_name ?? ''),
				'issue_date'  => (string)($sonod->dating_stampingsss ?? ''),
			);

			$after_data = array(
				'title'       => (string)$gen_data['certificate_title_head_text'],
				'name_bn'     => (string)$person_data['ver_per_info_name_bn'],
				'name_en'     => (string)$person_data['ver_per_info_name_en'],
				'father_bn'   => (string)$person_data['ver_per_info_father_name_bn'],
				'father_en'   => (string)$person_data['ver_per_info_father_name_en'],
				'mother_bn'   => (string)$person_data['ver_per_info_mother_name_bn'],
				'mother_en'   => (string)$person_data['ver_per_info_mother_name_en'],
				'ward_no'     => (string)$person_data['ver_per_info_ward_no'],
				'village'     => (string)$person_data['custom_village_name'],
				'house'       => (string)$person_data['custom_house_name'],
				'issue_date'  => (string)$gen_data['dating_stampingsss'],
			);

			$changed_fields = array();
			foreach ($after_data as $k => $val) {
				if (isset($before_data[$k]) && $before_data[$k] !== $val) {
					$changed_fields[] = $k;
				}
			}
			$chg_desc = !empty($changed_fields) ? 'সংশোধিত ফিল্ড: ' . implode(', ', $changed_fields) : 'সনদ তথ্য পুনঃসংরক্ষণ';

			audit_log('update', 'sonod', array(
				'entity_id' => (string)$sonod->sonod_uniqs,
				'tenant_id' => $tenant_id,
				'summary'   => 'ইস্যুকৃত সনদ #' . $sonod->cgp_pr_idiidid_pr . ' তথ্য সংশোধন করা হয়েছে (' . $chg_desc . ')',
				'before'    => $before_data,
				'after'     => $after_data
			));
		}

		$this->session->set_flashdata('success', 'সনদ #' . ($sonod->cgp_pr_idiidid_pr ?? '') . ' এর তথ্য সফলভাবে হালনাগাদ ও সংরক্ষিত হয়েছে।');
		redirect('sonod_list');
	}

	/**
	 * Sonod Audit Trail and Activity Log View for Parishad Admin
	 */
	public function audit_logs_view()
	{
		$tenant_id     = (int) tenant_code();
		$action_filter = trim((string)$this->input->get('action'));
		$entity_filter = trim((string)$this->input->get('entity'));
		$search_query  = trim((string)$this->input->get('q'));

		$this->db->from('audit_logs')
			->where('tenant_id', $tenant_id)
			->where_in('entity', array('sonod', 'warish_sonod', 'trade_license', 'approvals', 'custom_sonod', 'sonod_draft'));

		if (!empty($action_filter) && $action_filter !== 'all') {
			$this->db->where('action', $action_filter);
		}
		if (!empty($entity_filter) && $entity_filter !== 'all') {
			$this->db->where('entity', $entity_filter);
		}
		if (!empty($search_query)) {
			$this->db->group_start()
				->like('summary', $search_query)
				->or_like('entity_id', $search_query)
				->or_like('actor_name', $search_query)
				->or_like('ip_address', $search_query)
				->group_end();
		}

		$this->db->order_by('created_at', 'DESC')
			->limit(100);

		$logs = $this->db->get()->result();

		// Statistics
		$this->db->where('tenant_id', $tenant_id)->where_in('entity', array('sonod', 'warish_sonod', 'trade_license', 'approvals', 'custom_sonod', 'sonod_draft'));
		$total_logs = $this->db->count_all_results('audit_logs');

		$this->db->where('tenant_id', $tenant_id)->where('action', 'create')->where_in('entity', array('sonod', 'warish_sonod', 'trade_license'));
		$create_logs = $this->db->count_all_results('audit_logs');

		$this->db->where('tenant_id', $tenant_id)->where('action', 'update')->where_in('entity', array('sonod', 'warish_sonod', 'trade_license'));
		$update_logs = $this->db->count_all_results('audit_logs');

		$this->db->where('tenant_id', $tenant_id)->like('action', 'approval.')->where_in('entity', array('sonod', 'approvals'));
		$approval_logs = $this->db->count_all_results('audit_logs');

		$this->data['audit_logs']    = $logs;
		$this->data['total_logs']    = $total_logs;
		$this->data['create_logs']   = $create_logs;
		$this->data['update_logs']   = $update_logs;
		$this->data['approval_logs'] = $approval_logs;
		$this->data['action_filter'] = $action_filter;
		$this->data['entity_filter'] = $entity_filter;
		$this->data['search_query']  = $search_query;

		$this->template->sonod('sonod/sonod_audit_view_file', $this->data);
	}

	/**
	 * JSON API to fetch single audit log details & diff
	 */
	public function audit_log_details_api($audit_id = 0)
	{
		$tenant_id = (int) tenant_code();
		$audit_id  = (int) $audit_id;

		$log = $this->db->where('audit_id', $audit_id)
			->where('tenant_id', $tenant_id)
			->get('audit_logs')
			->row();

		if (!$log) {
			$this->output->set_content_type('application/json')->set_output(json_encode(array(
				'ok'  => FALSE,
				'msg' => 'অডিট রেকর্ড পাওয়া যায়নি।'
			)));
			return;
		}

		$before = !empty($log->before_data) ? json_decode($log->before_data, true) : null;
		$after  = !empty($log->after_data) ? json_decode($log->after_data, true) : null;

		$this->output->set_content_type('application/json')->set_output(json_encode(array(
			'ok'          => TRUE,
			'audit_id'    => (int)$log->audit_id,
			'action'      => $log->action,
			'entity'      => $log->entity,
			'entity_id'   => $log->entity_id,
			'actor_name'  => $log->actor_name,
			'actor_group' => $log->actor_group,
			'summary'     => $log->summary,
			'ip_address'  => $log->ip_address,
			'created_at'  => date('d M, Y h:i A', strtotime($log->created_at)),
			'before'      => $before,
			'after'       => $after
		)));
	}





















}

