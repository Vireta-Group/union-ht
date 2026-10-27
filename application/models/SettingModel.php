<?php 

defined('BASEPATH') OR exit('No direct script access allowed');

class SettingModel extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

	public function getThisSetting()
	{
		$this->db->where('sattings_infos_id', 1);
		$sql = $this->db->get('satting_infos');
		return $sql->row();
	} 

	public function getThisInsName($tenant_id = null)
	{
		$tid = !empty($tenant_id) ? (int)$tenant_id : tenant_code();
		$this->db->where('ins_titute_p_idds', $tid);
		$this->db->join('un_list', 'un_list.un_id = ins_titute.un_iddd_iiid', 'left');
		$this->db->join('up_list', 'up_list.up_id = un_list.upazilla_id', 'left');
		$this->db->join('dist_list', 'dist_list.dist_id = up_list.district_id', 'left');
		$this->db->join('div_list', 'div_list.div_id = dist_list.division_id', 'left');
		$sql = $this->db->get('ins_titute');
		return $sql->row();
	}

	public function get_ins_sliders()
	{
		$this->db->where('insidpris_id', tenant_code());
		$sql = $this->db->get('home_slider');
		return $sql->result();
	}

	public function get_ins_members()
	{
		$this->db->order_by('ward_no_sq', 'asc');
		$this->db->where('ins_atou_iddd_s', tenant_code());
		$sql = $this->db->get('up_member_s');
		return $sql->result();
	}

	public function get_this_employees()
	{
		$this->db->where('emp_ins_code_id', tenant_code());
		$sql = $this->db->get('employee_data_ins');
		return $sql->result();
	}

	public function getTenantSonodThemeConfig($tenant_id)
	{
		$row = $this->db
					->select('sonod_theme_config, ins_logo_path, ins_name, ins_name_en, cm_sign_s64')
					->where('ins_titute_p_idds', (int)$tenant_id)
					->get('ins_titute')
					->row();

		$defaults = array(
			'design_mode'             => 'digital',
			'custom_pad_path'         => '',
			'custom_pad_opacity'      => 1.0,
			'custom_pad_fit'          => 'cover',
			'theme_color'             => 'emerald',
			'primary_color'           => '#065f46',
			'border_color'            => '#065f46',
			'border_style'            => 'double',
			'show_govt_logo'          => true,
			'show_up_logo'            => true,
			'up_logo_position'        => 'right',
			'watermark_type'          => 'bd_gov',
			'watermark_visible'       => true,
			'watermark_opacity'       => 0.06,
			'watermark_size'          => 380,
			'show_barcode'            => true,
			'show_qr_code'            => true,
			'show_member_sign'        => true,
			'show_chairman_sign'      => true,
			'show_sec_sign'           => true,
			'top_left_monogram'       => 'bd_gov',
			'monogram_custom_url'     => '',
			'border_type'             => 'ornate_frame',
			'border_design'           => 'border_1',
			'text_layout'             => 'layout_grid_classic',
			'signatory_count'         => 'auto',
			'font_family'             => 'tiro_bangla',
			'header_contact_visible'  => true,
			'citizen_photo_position'  => 'under_title',
			'footer_text'             => 'ডিজিটাল ইউনিয়ন পরিষদ সেবা'
		);

		if (!empty($row->sonod_theme_config)) {
			$saved = is_string($row->sonod_theme_config) ? json_decode($row->sonod_theme_config, true) : (array)$row->sonod_theme_config;
			if (is_array($saved)) {
				return array_merge($defaults, $saved);
			}
		}

		return $defaults;
	}

	public function updateTenantSonodThemeConfig($tenant_id, $config_array)
	{
		$json_str = json_encode($config_array, JSON_UNESCAPED_UNICODE);
		return $this->db
					->where('ins_titute_p_idds', (int)$tenant_id)
					->update('ins_titute', array(
						'sonod_theme_config' => $json_str
					));
	}




}



