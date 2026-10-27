<?php
defined('BASEPATH') OR exit('No direct script access allowed');
 
class Dashboard extends CI_Controller {

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

		if (!$this->ion_auth->logged_in()) {
			redirect('logout', 'refresh');
		}

		$current_user = $this->ion_auth->user()->row();
		$this->user_id = $current_user->user_full_tbl_id ?? null;

		if (empty($this->user_id)) {
			// Auto-provision customer_full_info if missing.
			//
			// This used to fall back to tenant 1, so a user with no tenant at all
			// got a customer_full_info row pointing at ধুম. It is not
			// hypothetical: users.id 7 (test2@test.local, tenant 3) has a NULL
			// user_full_tbl_id and would have been written into tenant 1. 0 is
			// the honest value — ins_pr_iddds is a nullable integer and nothing
			// in the tree assumes it is a real tenant — and the tenant guard
			// below refuses the request for anyone in this state anyway, so the
			// row is never actually reached with a fabricated tenant.
			$this->db->insert('customer_full_info', [
				'ins_pr_iddds'        => (int)($current_user->ins_pr_id_s ?? 0),
				'user_person_name'    => $current_user->first_name ?: $current_user->username,
				'user_email_no'       => $current_user->email,
				'user_uniq_qq_reails' => uniqid(),
				'activity'            => 1
			]);
			$this->user_id = $this->db->insert_id();
			$this->db->where('id', $current_user->id)->update('users', [
				'user_full_tbl_id' => $this->user_id
			]);
		}

		/*
		 * Tenant guard — refuses the request when the signed-in user does not
		 * belong to the parishad this host resolved to.
		 *
		 * This is enforce_tenant_scope(), shared with Sonod and Download, which
		 * serve tenant data through the same template->back() path and were
		 * missing the check entirely. The full reasoning — why the host beats
		 * the session, why `sadmin` is exempt, why a NULL ins_pr_id_s is refused
		 * — lives on the helper.
		 */
		enforce_tenant_scope();
		enforce_tenant_status();

		// Platform super admin has no parishad — their home is the
		// control panel (/super_admin), not a parishad dashboard.
		if ($this->ion_auth->in_group('sadmin')) {
			redirect('super_admin', 'refresh');
		}

		// Check if logged in user is a temporary support admin
		$temp_admin_rec = $this->db->where('user_id', (int)$current_user->id)->get('tenant_temp_admins')->row();
		if ($temp_admin_rec) {
			if ($temp_admin_rec->is_revoked || strtotime($temp_admin_rec->expires_at) <= time()) {
				if (function_exists('cleanup_expired_temp_admins')) {
					cleanup_expired_temp_admins();
				}
				$this->ion_auth->logout();
				$this->session->set_flashdata('message', 'অস্থায়ী সহায়তা অ্যাকাউন্টের মেয়াদ শেষ হয়ে গেছে।');
				redirect('login', 'refresh');
			} else {
				$remaining = strtotime($temp_admin_rec->expires_at) - time();
				$this->data['temp_support_info'] = [
					'id'                => $temp_admin_rec->id,
					'remaining_seconds' => $remaining,
					'expires_at'        => $temp_admin_rec->expires_at,
					'hours_left'        => floor($remaining / 3600),
					'minutes_left'      => floor(($remaining % 3600) / 60)
				];
			}
		}

		// Ensure tenant is resolved for logged-in user if accessed on platform host (e.g. bare localhost)
		$user_tenant_id = (int)($current_user->ins_pr_id_s ?? 0);
		if (tenant_code() === 0) {
			if ($user_tenant_id > 0) {
				redirect('dashboard?tenant=' . $user_tenant_id, 'refresh');
			}
		}

		$this_user_info = $this->UserModel->getUserInfo($this->user_id);  
		$this->data['setting_info'] = $this->SettingModel->getThisInsName();

		$user_role_info = get_user_tenant_role($current_user->id);

		$this->data['loggedUser'] = array(
			'userName'              => $this_user_info->user_person_name ?? $current_user->username,
			'userParcent'           => $this_user_info->percentager ?? 0,
			'userAvatar'            => $this_user_info->user_img_base64 ?? '',
			'userInstitute'         => $this_user_info->institute_name ?? ($this->data['setting_info']->ins_name ?? ''),
			'userPhone'             => $this_user_info->user_phone_no ?? '',
			'userEmail'             => $this_user_info->user_email_no ?? $current_user->email,
			'userAddress'           => $this_user_info->address_full ?? '',
			'creatDate'             => $this_user_info->today_dates_updating ?? date('Y-m-d'),
			'divEn'                 => $this_user_info->div_name ?? '',
			'divBn'                 => $this_user_info->div_bn_name ?? '',
			'disEn'                 => $this_user_info->dist_name ?? '',
			'disBn'                 => $this_user_info->dist_bn_name ?? '',
			'upEN'                  => $this_user_info->up_name ?? '',
			'upBn'                  => $this_user_info->up_bn_name ?? '',
			'unEn'                  => $this_user_info->un_name ?? '',
			'unBn'                  => $this_user_info->un_bn_name ?? '',
			'username'              => $current_user->username,
			'user_u_type'           => $current_user->u_type,
			'active'                => $current_user->active,
			'userGroupName'         => $user_role_info['title'],
			'userRoleKey'           => $user_role_info['key'],
			'userUniqs'             => $this_user_info->user_uniq_qq_reails ?? '',
			'userGroupDescription'  => $user_role_info['title']
		);
        // if (!$this->ion_auth->in_group(array('sadmin'))) {
        //     $this->session->set_flashdata('error', lang('access_denied'));
        //     redirect("home/permission");
        // }

	} 

	public function index()
	{
		$tenant_id = tenant_code();

		// 1. Total holdings count
		$this->db->where('ins_aut_iiid', $tenant_id);
		$this->data['total_holdings'] = (int)$this->db->count_all_results('family_data_sheet');

		// 2. Total tax collected & discounts forgiven (per docs/DISCOUNT.md)
		$this->db->select("
			COALESCE(SUM(NULLIF(regexp_replace(amnt_taka::text, '[^0-9.]', '', 'g'), '')::numeric), 0) as total_tax,
			COALESCE(SUM(NULLIF(regexp_replace(discount_amounts::text, '[^0-9.]', '', 'g'), '')::numeric), 0) as total_discount
		");
		$this->db->where('ins_ti_tute_pridd', $tenant_id);
		$tax_row = $this->db->get('collect_amnt_invoice')->row();
		$this->data['total_tax'] = (float)($tax_row->total_tax ?? 0);
		$this->data['total_discount'] = (float)($tax_row->total_discount ?? 0);
		$this->data['total_gross_tax'] = $this->data['total_tax'] + $this->data['total_discount'];

		// 3. Today's tax collected
		$this->db->select("COALESCE(SUM(NULLIF(regexp_replace(amnt_taka::text, '[^0-9.]', '', 'g'), '')::numeric), 0) as today_tax");
		$this->db->where('ins_ti_tute_pridd', $tenant_id);
		$this->db->where('date_s_today_s', date('Y-m-d'));
		$today_row = $this->db->get('collect_amnt_invoice')->row();
		$this->data['today_tax'] = (float)($today_row->today_tax ?? 0);

		// 4. Total sonod issued
		$this->db->where('insididid_pridididid', $tenant_id);
		$this->data['total_sonod'] = (int)$this->db->count_all_results('sonod_genarates');

		// 5. Total active staff / collectors
		$this->db->where('ins_pr_iddds', $tenant_id);
		$this->db->where('activity', 1);
		$this->db->where("user_email_no NOT LIKE '%@support.viretadev.local'");
		$this->data['total_users_count'] = (int)$this->db->count_all_results('customer_full_info');

		// 6. Recent tax collection records
		$this->db->select('c.collect_amnt_invoice_idd, c.amnt_taka, c.date_s_today_s, c.invoice_no_s, f.family_person_name_head, f.holding_number, f.word_number');
		$this->db->from('collect_amnt_invoice c');
		$this->db->join('family_data_sheet f', 'f.data_id = c.family_data_id_id_dis', 'left');
		$this->db->where('c.ins_ti_tute_pridd', $tenant_id);
		$this->db->order_by('c.collect_amnt_invoice_idd', 'DESC');
		$this->db->limit(6);
		$this->data['recent_collections'] = $this->db->get()->result();

		$this->data['ins_info'] = $this->SettingModel->getThisInsName();
		$this->data['user_infos'] = $this->UserModel->getAllUsersInfo(); 

		// 7. Total cash held with UDC & Tax Collectors (collected - deposited - commission)
		$total_collector_due_sum = 0;
		$total_collector_collected_sum = 0;
		if (!empty($this->data['user_infos'])) {
			foreach ($this->data['user_infos'] as $c_user) {
				$u_cid = (int)($c_user->udc_list_auto_p_iidd ?? 0);
				if ($u_cid <= 0) continue;
				$u_coll = (float)$this->FamilyModel->get_user_collect_amount($u_cid);
				$u_cut  = (float)$this->FamilyModel->get_user_cutting_amount($u_cid);
				$u_pct  = (float)($c_user->percentager ?? 0);
				$u_comm = ($u_coll * $u_pct / 100);
				$u_net_due = ($u_coll - $u_cut) - $u_comm;
				if ($u_net_due > 0) {
					$total_collector_due_sum += $u_net_due;
				}
				$total_collector_collected_sum += $u_coll;
			}
		}
		$this->data['total_collector_due'] = $total_collector_due_sum;
		$this->data['total_collector_collected'] = $total_collector_collected_sum;

		$this->template->back('back/fdash', $this->data); 
	}

	public function self_pass_verify_()
	{
		if (password_verify($this->input->post('pass'), $this->ion_auth->user()->row()->password)) 
		{
			echo 1;
		}else {
			echo 0;
		}
	}

	public function mdash()
	{
		$this->index();
	}
	
	public function dataset_form()
	{
		$this->template->back('back/entry_data_form', $this->data);
	}

	public function institute_present_this_balance()
	{
		$tenant_id = tenant_code() ?: ($this->data['setting_info']->ins_titute_p_idds ?? 0);
		$get_added_balance = $this->FamilyModel->get_this_institute_added_balances($tenant_id);
		$get_cutted_balance = $this->FamilyModel->get_total_cutting_amnt($tenant_id);
		$net_balance = (float)($get_added_balance ?? 0) - (float)($get_cutted_balance ?? 0);
		$this->output->set_content_type('application/json')->set_output(json_encode(array(
			"total_add"  => (float)($get_added_balance ?? 0),
			"total_cut"  => (float)($get_cutted_balance ?? 0),
			"balance"    => $net_balance,
			"balance_bn" => bn_num(number_format($net_balance, 0, '.', ''))
		)));
	}
	
	public function insert_data_sheet_form_datas()
	{
		$uniqid = uniqid().'-'.uniqid();
		$data = array(
				'user_id' 					=> $this->user_id,
				'family_person_name_head' 	=> $this->input->post('name'), 
				'pason_father_name' 		=> $this->input->post('fatherName'), 
				'parson_mother_name' 		=> $this->input->post('motherName'), 
				'parson_paranent_address' 	=> $this->input->post('presentAddress'), 
				'parson_parmanent_address' 	=> $this->input->post('parmanentAddress'), 
				'profile_image' 			=> $this->input->post('profile_image_base64'), 
				'home_image' 				=> $this->input->post('house_image_base64'), 
				'nid_number' 				=> $this->input->post('nidNumber'), 
				'ins_aut_iiid' 				=> tenant_code(), 
				'unid' 						=> $this->ion_auth->user()->row()->union_unq_idd_iii, 
				'date_of_birth' 			=> $this->input->post('dateOfBirth'), 
				'mobile_number_one' 		=> $this->input->post('mobileNumber'), 
				'mobile_number_two' 		=> $this->input->post('secondaryMobileNumber'), 
				'holding_number' 			=> $this->input->post('holdingNumber').'-'.$this->input->post('wordNumber'), 
				'word_number' 				=> $this->input->post('wordNumber'), 
				'house_etc_a_iddd' 			=> $this->input->post('houseName'), 
				'vill_pr_iddsid' 			=> $this->input->post('villageName'), 
				'holding_starting_years' 	=> $this->input->post('holdingStartingDateFirst').'-'.$this->input->post('holdingStartingDateSecs'),
				'holding_condition' 		=> $this->input->post('holdingCondition'), 
				'holding_rates_yearly' 		=> $this->input->post('holdingRatesYearly'), 
				'uniqID' 					=> $uniqid,
				'create_date' 				=> date('Y-m-d', time()),
				'create_time_stamp' 		=> time()
			);

		$this->AddMemberModel->insert_data_sheet_form_data($data);
		redirect("dataEntrySuccess?uniq_id=".$uniqid);
	}

	public function success_entry_data_sheets()
	{
		$this->template->back('back/data_entry_success', $this->data);
	}

	public function nid_verify_getting_datas()
	{
		$n = $this->input->post('n');
		$d = $this->input->post('d');

		$this->output->set_content_type('application/json')->set_output(json_encode($this->getapi->m_api_data($n, $d)));
	}

	public function tax_collections_view()
	{
		$this->template->back('back/tax_collection', $this->data);
	}

	public function search_for_tax_collect()
	{
		// echo $this_year=date('Y', strtotime('-1 year'));
		$this_year=date('Y');

		// Holding Number
		$get_user_id = $this->input->post('input_val');

		// Search Person  
		$data['get_search_data'] = $this->FamilyModel->serachings_by_holding_num($get_user_id);

		// 
		if (!empty($data['get_search_data'])) {
				
			// Paid Amount
			$data['paid_amount'] = $this->FamilyModel->get_paid_amount_by_mainID($data['get_search_data']->data_id);


			$paid_sum = array_sum(array_column($data['paid_amount'], 'paid_amount'));

			$start_n=$data['get_search_data']->holding_starting_years;
			$year_arr = explode ("-", $start_n);

			$bokeya_year = $this_year-$year_arr[0]+1;
			$total_unpaid = $bokeya_year*$data['get_search_data']->holding_rates_yearly;
			$data['total_unpaid'] = $total_unpaid - $paid_sum;

			$data__c_year_s=[];

			for ($i=$data['get_search_data']->holding_starting_years; $i < date('Y',strtotime('+1 year')).'-'.date('Y', strtotime('+2 year')); 
				$i = $year_arr[1].'-'.$year_arr[1]+1) {
				$year_arr = explode ("-", $i);
				// $b = array($i);
				array_push($data__c_year_s, $i);
			}

			$ss_paidable_year = [];
			foreach ($data['paid_amount'] as $ss) {
				array_push($ss_paidable_year, $ss->paidable_year);
			}

			// Compare all values by a json_encode
			$aaaa = array_diff($data__c_year_s, $ss_paidable_year);
			$data['c_year_s'] = array_values($aaaa);
			// Json decode the result

			$this->output->set_content_type('application/json')->set_output(json_encode($data));
		}else {
			echo 0;
		}
	} 

	public function print_invoice_slip()
	{
		$unq_id = $this->input->get('unqs');
        $this->data['paid_amnt_'] = $this->FamilyModel->get_paidAmntByVocherNo($unq_id);
        $this->data['invoices'] = $this->FamilyModel->get_invoiceByVocherNo($unq_id);
		$this->load->view('back/print_slip', $this->data);
	}

	public function single_entry_view()
	{
		$search_number = $this->input->get('uniq_id');
		$this->data['main_data'] = $this->FamilyModel->serach_data_by_uniq_num($search_number); 

		$this->data['holding_rates'] = $this->FamilyModel->search_holding_rat_es($this->data['main_data']->data_id);

		$this->data['invoces'] = $this->FamilyModel->get_invoiceByPersonID($this->data['main_data']->data_id);

		// echo $this_year=date('Y', strtotime('-1 year'));
		$this_year=date('Y');

		$this->data['paid_amount'] = $this->FamilyModel->get_paid_amount_by_mainID($this->data['main_data']->data_id);

		// Calculate year-by-year tax assessment & payments timeline
		$start_n = trim((string)($this->data['main_data']->holding_starting_years ?? ''));
		$year_parts = explode('-', $start_n);
		$start_y = (!empty($year_parts[0]) && is_numeric($year_parts[0])) ? (int)$year_parts[0] : (int)date('Y');

		$cur_m = (int)date('n');
		$cur_y = (int)date('Y');
		$cur_fiscal_start = ($cur_m >= 7) ? $cur_y : ($cur_y - 1);

		$yearly_rate = (float)($this->data['main_data']->holding_rates_yearly ?? 0);

		// Map payments by fiscal year and by voucher
		$paid_by_year = [];
		$invoice_years_map = [];
		$first_payment_date = null;
		$last_payment_date = null;

		foreach ($this->data['paid_amount'] as $p) {
			$yr = trim((string)($p->paidable_year ?? ''));
			if ($yr !== '') {
				$paid_by_year[$yr][] = $p;
			}
			$vouch = trim((string)($p->voucher ?? ''));
			if ($vouch !== '' && $yr !== '') {
				if (!isset($invoice_years_map[$vouch])) {
					$invoice_years_map[$vouch] = [];
				}
				if (!in_array($yr, $invoice_years_map[$vouch], true)) {
					$invoice_years_map[$vouch][] = $yr;
				}
			}
			if (!empty($p->entry_date)) {
				if ($first_payment_date === null || $p->entry_date < $first_payment_date) {
					$first_payment_date = $p->entry_date;
				}
				if ($last_payment_date === null || $p->entry_date > $last_payment_date) {
					$last_payment_date = $p->entry_date;
				}
			}
		}

		// Collect all relevant fiscal years
		$all_fiscal_years = [];
		for ($y = $start_y; $y <= $cur_fiscal_start; $y++) {
			$all_fiscal_years[$y . '-' . ($y + 1)] = true;
		}
		foreach (array_keys($paid_by_year) as $py) {
			$all_fiscal_years[$py] = true;
		}
		ksort($all_fiscal_years);

		$tax_timeline = [];
		$paid_years_count = 0;
		$unpaid_years_count = 0;
		$total_calculated_due = 0;
		$unpaid_years_list = [];

		foreach (array_keys($all_fiscal_years) as $fyr) {
			$pmts = $paid_by_year[$fyr] ?? [];
			$yr_paid = array_sum(array_column($pmts, 'paid_amount'));
			$yr_due = max(0, $yearly_rate - $yr_paid);

			if ($yr_paid >= $yearly_rate && $yearly_rate > 0) {
				$status = 'paid';
				$paid_years_count++;
			} elseif ($yr_paid > 0) {
				$status = 'partial';
				$unpaid_years_count++;
				$total_calculated_due += $yr_due;
				$unpaid_years_list[] = $fyr;
			} else {
				$status = 'unpaid';
				$unpaid_years_count++;
				$total_calculated_due += $yearly_rate;
				$unpaid_years_list[] = $fyr;
			}

			$tax_timeline[] = [
				'year'       => $fyr,
				'rate'       => $yearly_rate,
				'paid'       => $yr_paid,
				'due'        => $yr_due,
				'status'     => $status,
				'payments'   => $pmts,
			];
		}

		$this->data['tax_timeline']        = $tax_timeline;
		$this->data['invoice_years_map']   = $invoice_years_map;
		$this->data['first_payment_date']  = $first_payment_date;
		$this->data['last_payment_date']   = $last_payment_date;
		$this->data['paid_years_count']    = $paid_years_count;
		$this->data['unpaid_years_count']  = $unpaid_years_count;
		$this->data['unpaid_years_list']   = $unpaid_years_list;
		$this->data['total_unpaid']        = max(0, $total_calculated_due);

		if (empty($this->data['main_data'])) {
			redirect('dashboard','refresh');
		}else{
			$this->data['family_data'] = $this->FamilyModel->get_all_family_member_dataSheet($this->data['main_data']->data_id);
			$this->template->back('back/single_view', $this->data);
		}
	}
	
	public function update_data_sheet_by_id()
	{
		$data_id = $this->input->post('data_id');
		$uniqID = $this->input->post('uniqID');
		

		$dataArry = array(
			'nid_number' 				=> $this->input->post('nid_number'),
			'date_of_birth' 			=> $this->input->post('date_of_birth'),
			'family_person_name_head' 	=> $this->input->post('per_name'),
			'pason_father_name' 		=> $this->input->post('f_name'),
			'parson_mother_name' 		=> $this->input->post('m_name'),
			'parson_parmanent_address' 	=> $this->input->post('prmnt_address'),
			'parson_paranent_address' 	=> $this->input->post('prs_address'),
			'mobile_number_one' 		=> $this->input->post('mobile_no1s'),
			'mobile_number_two' 		=> $this->input->post('mobile_2no'),
			'holding_number' 			=> $this->input->post('holding_number').'-'.$this->input->post('wordNumber'),
			'word_number' 				=> $this->input->post('wordNumber'),
			'vill_pr_iddsid' 			=> $this->input->post('villageName'),
			'house_etc_a_iddd' 			=> $this->input->post('houseName'),
			'holding_starting_years' 	=> $this->input->post('holdingStartingDateFirst').'-'.$this->input->post('holdingStartingDateSecs'),
			'holding_condition' 		=> $this->input->post('holdingCondition'),
			'holding_rates_yearly' 		=> $this->input->post('holdingRatesYearly'),
		);
		$this->FamilyModel->updates_data_sheetinfos($data_id, $dataArry); 
		
		redirect('view?uniq_id='.$uniqID, 'refresh');
	}

	public function search_holdings_view_file_ss()
	{
		$this->template->back('back/search_holding_info_view', $this->data);
	}

	public function view_full_data_sheets_doc()
	{
		$this->template->back('back/view_full_data_doc', $this->data);
	}

	public function view_full_data_sheetss()
	{
		$this->template->back('back/view_full_data', $this->data);
	}

	public function get_data_by_ward_json_encode()
	{
		$ward_nos = $this->input->post('wards');
		if (empty($ward_nos)) {
			$ward_nos = '01';
		}
		$ward_data = $this->FamilyModel->get_all_dataSheet_by_ward($ward_nos);
		$this->output->set_content_type('application/json')->set_output(json_encode($ward_data));
	}

	public function download_ward_doc()
	{
		$ward_nos = $this->input->get('ward');
		if (empty($ward_nos)) {
			$ward_nos = '01';
		}

		$ward_data = $this->FamilyModel->get_all_dataSheet_by_ward($ward_nos);
		$filename = 'ward_' . $ward_nos . '_data.xlsx';

		$headers = array(
			'SL',
			'Holding No',
			'Name',
			'Father Name',
			'Mother Name',
			'Mobile 1',
			'Mobile 2',
			'Home Name',
			'Village Name',
			'Type',
			'Rate',
			'Last Paid Year',
		);

		// Fetch the newest paid year for every holding in this ward in one query.
		$data_ids = array();
		foreach ($ward_data as $row) {
			$data_ids[] = $row->data_id;
		}
		$last_paid_years = $this->FamilyModel->get_last_paid_years_by_ids($data_ids);

		$rows = array();
		foreach ($ward_data as $index => $row) {
			$rows[] = array(
				$index + 1,
				$row->holding_number,
				$row->family_person_name_head,
				$row->pason_father_name,
				$row->parson_mother_name,
				$row->mobile_number_one,
				$row->mobile_number_two,
				$row->house_na_me,
				$row->bn_vill_names,
				$row->holding_condition,
				$row->holding_rates_yearly,
				isset($last_paid_years[$row->data_id]) ? $last_paid_years[$row->data_id] : '',
			);
		}

		$this->send_xlsx($filename, 'Ward ' . $ward_nos . ' Data', $headers, $rows);
	}

	/**
	 * Build a real .xlsx workbook (no external library needed) and force it as a download.
	 * Uses only PHP's built-in ZipArchive extension.
	 *
	 * @param string $filename   Filename for the downloaded file (must end with .xlsx)
	 * @param string $sheet_name Sheet/tab name shown in Excel
	 * @param array  $headers    Column header strings
	 * @param array  $rows       Rows of cell values (arrays)
	 */
	private function send_xlsx($filename, $sheet_name, $headers, $rows)
	{
		if (!class_exists('ZipArchive')) {
			show_error('Excel export requires the PHP ZipArchive extension.');
		}

		$col_letter = function ($index) {
			$letters = '';
			$index++;
			while ($index > 0) {
				$mod = ($index - 1) % 26;
				$letters = chr(65 + $mod) . $letters;
				$index = intdiv($index - 1, 26);
			}
			return $letters;
		};

		$esc = function ($value) {
			return htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
		};

		// ---- Build the worksheet (inline strings keep it dependency-free) ----
		$sheet_rows = '';
		$rownum = 1;

		$sheet_rows .= '<row r="' . $rownum . '">';
		foreach ($headers as $ci => $h) {
			$sheet_rows .= '<c r="' . $col_letter($ci) . $rownum . '" t="inlineStr"><is><t xml:space="preserve">'
				. $esc($h) . '</t></is></c>';
		}
		$sheet_rows .= '</row>';
		$rownum++;

		foreach ($rows as $row) {
			$sheet_rows .= '<row r="' . $rownum . '">';
			$ci = 0;
			foreach ($row as $val) {
				$v = (string) $val;
				// Render plain numbers as numbers (except SL) so Excel can sort/filter them.
				if ($ci > 0 && $v !== '' && is_numeric($v)) {
					$sheet_rows .= '<c r="' . $col_letter($ci) . $rownum . '"><v>' . $v . '</v></c>';
				} else {
					$sheet_rows .= '<c r="' . $col_letter($ci) . $rownum . '" t="inlineStr"><is><t xml:space="preserve">'
						. $esc($v) . '</t></is></c>';
				}
				$ci++;
			}
			$sheet_rows .= '</row>';
			$rownum++;
		}

		$sheet_xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
			. '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
			. '<sheetData>' . $sheet_rows . '</sheetData>'
			. '</worksheet>';

		$content_types = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
			. '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
			. '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
			. '<Default Extension="xml" ContentType="application/xml"/>'
			. '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
			. '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
			. '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
			. '</Types>';

		$root_rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
			. '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
			. '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
			. '</Relationships>';

		$workbook_xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
			. '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
			. '<sheets><sheet name="' . $esc($sheet_name) . '" sheetId="1" r:id="rId1"/></sheets>'
			. '</workbook>';

		$workbook_rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
			. '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
			. '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
			. '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
			. '</Relationships>';

		$styles_xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
			. '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
			. '<fonts count="1"><font><sz val="11"/><name val="Calibri"/></font></fonts>'
			. '<fills count="1"><fill><patternFill patternType="none"/></fill></fills>'
			. '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
			. '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
			. '<cellXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/></cellXfs>'
			. '</styleSheet>';

		$tmp = tempnam(sys_get_temp_dir(), 'xlsx');
		$zip = new ZipArchive();
		if ($zip->open($tmp, ZipArchive::OVERWRITE) !== true) {
			show_error('Could not create Excel file.');
		}
		$zip->addFromString('[Content_Types].xml', $content_types);
		$zip->addFromString('_rels/.rels', $root_rels);
		$zip->addFromString('xl/workbook.xml', $workbook_xml);
		$zip->addFromString('xl/_rels/workbook.xml.rels', $workbook_rels);
		$zip->addFromString('xl/styles.xml', $styles_xml);
		$zip->addFromString('xl/worksheets/sheet1.xml', $sheet_xml);
		$zip->close();

		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		header('Content-Length: ' . filesize($tmp));
		readfile($tmp);
		unlink($tmp);
		exit;
	}

	public function get_holding_by_noss()
	{
		$holding_no = $this->input->post('holding_no');	
			// Holding Number
		$datas = $this->FamilyModel->serach_data_by_holding_num($holding_no);

		if (!empty($datas)) {
			$this_datas_infos = array(
								'unqID'		=> $datas->uniqID,
								'pName'		=> $datas->family_person_name_head,
								'holdNo'	=> $datas->holding_number,
								'fName'		=> $datas->pason_father_name,
								'mName'		=> $datas->parson_mother_name,
								'mobile1'	=> $datas->mobile_number_one,
								'phone2'	=> $datas->mobile_number_two,
								'villName'	=> $datas->bn_vill_names,
								'homName'	=> $datas->house_na_me,
								'start'		=> $datas->holding_starting_years,
								'rates'		=> $datas->holding_rates_yearly,
							);
			$this->output->set_content_type('application/json')->set_output(json_encode($this_datas_infos));
		}
	}

	public function house_name_view()
	{
		$this->template->back('back/house_views', $this->data);
	}

	public function get_homess_show_json_byward()
	{
		$ward_nos = $this->input->post('wards');
		if (empty($ward_nos)) {
			$ward_nos = '01';
		}

		$house_infos = $this->FamilyModel->get_all_houses_infos_byward($ward_nos); 
		$this->output->set_content_type('application/json')->set_output(json_encode($house_infos)); 
	}

	public function get_village_by_ward_nos()
	{
		$ward_nos = $this->input->post('ward_n');

		$house_infos = $this->FamilyModel->get_villages_infos_byward($ward_nos); 
		$this->output->set_content_type('application/json')->set_output(json_encode($house_infos)); 
	}

	public function get_house_by_village_nos()
	{
		$vill_no = $this->input->post('vill_nos');

		$house_infos = $this->FamilyModel->get_houses_infos_byvillage_no($vill_no); 
		$this->output->set_content_type('application/json')->set_output(json_encode($house_infos)); 
	}

	public function get_holding_info_by_house_no()
	{
		$house_no = $this->input->post('house_no');

		$holding_infos = $this->FamilyModel->get_holdings_infos_byhouse_no($house_no); 
		$this->output->set_content_type('application/json')->set_output(json_encode($holding_infos)); 
	}

	public function add_new_housess()
	{
		$houseNameArr = array(
			'house_na_me'			=> $this->input->post('homes_text'),
			'wrds_no_s'				=> $this->input->post('ward_no'),
			'vill_au_iddsss'		=> $this->input->post('vill_no'),
			'ins_id_pr_idautoss'	=> tenant_code(),
			'un_auto_code_prsss'	=> $this->ion_auth->user()->row()->union_unq_idd_iii,
			'ti_me_s'				=> time(),
		);
		$this->FamilyModel->insert_new_home_name($houseNameArr); 
	}

	public function edit_home_name_by_a_idd()
	{
		$id = $this->input->post('id');
		$houseNameArr = array(
			'house_na_me'			=> $this->input->post('name'),
		);
		$this->FamilyModel->edit_home_names_by_p_idd($houseNameArr, $id); 
	}

	public function search_invoice_receipt_shows()
	{
		$this->template->back('back/show_receiptss', $this->data);
	}

	public function search_show_receipt_s()
	{
		$type_num = $this->input->post('invc_');

		$info['invoice'] = $this->FamilyModel->get_invoiceByVocherNo($type_num); 
		if (!empty($info['invoice'])) {
			$info['paid_year'] = $this->FamilyModel->get_paidAmntByVocherNo($type_num); 
			$this->output->set_content_type('application/json')->set_output(json_encode($info)); 
		}else {
			echo 0;
		}
	}

	public function search_show_receipt_byHoldings()
	{
		$type_num = $this->input->post('invc_');
		// Search Person 
		$person = $this->FamilyModel->serachings_by_holding_num($type_num);
		if (!empty($person)) {
			$datass['invoice'] = $this->FamilyModel->get_invoiceByPersonID($person->data_id); 
			$datass['paid_year'] = $this->FamilyModel->get_paid_amount_by_mainID($person->data_id); 
			$this->output->set_content_type('application/json')->set_output(json_encode($datass)); 
		}else {
			echo 0;
		}
	}

	public function user_lists_view()
	{
		$this->template->back('back/user_view', $this->data);
	}

	public function get_this_ins_users_list()
	{
		$user_infos = $this->FamilyModel->get_users_infos(); 
		$this->output->set_content_type('application/json')->set_output(json_encode($user_infos)); 
	}

	public function change_users_activity()
	{
		$user_id = (int)$this->input->post('cus_id');
		$userInfoByID = $this->FamilyModel->getUserSingleInfo($user_id); 
		if (!$userInfoByID) {
			$this->output->set_content_type('application/json')
						 ->set_output(json_encode(['status' => 'error', 'message' => 'ব্যবহারকারী পাওয়া যায়নি।']));
			return;
		}

		// Support user protection
		if (strpos($userInfoByID->username ?? '', 'support_') === 0 || strpos($userInfoByID->email ?? '', '@support.viretadev.local') !== false) {
			$this->output->set_content_type('application/json')
						 ->set_output(json_encode(['status' => 'error', 'message' => 'অনুমোদনহীন অ্যাক্সেস।']));
			return;
		}

		// Tenant boundary guard: ensure user belongs to current tenant unless sadmin
		if ($userInfoByID->ins_pr_id_s != tenant_code() && !in_group('sadmin')) {
			$this->output->set_content_type('application/json')
						 ->set_output(json_encode(['status' => 'error', 'message' => 'অনুমোদনহীন অ্যাক্সেস।']));
			return;
		}

		$new_active = ($userInfoByID->active == 1) ? 0 : 1;

		// Guard: If attempting to deactivate, check active users count for this tenant
		if ($new_active == 0) {
			$tenant_id = (int)($userInfoByID->ins_pr_id_s ?: tenant_code());
			$active_count = $this->db->where('ins_pr_id_s', $tenant_id)
									 ->where('active', 1)
									 ->count_all_results('users');
			if ($active_count <= 1) {
				$this->output->set_content_type('application/json')
							 ->set_output(json_encode([
								 'status' => 'error',
								 'message' => 'একমাত্র সক্রিয় ব্যবহারকারীকে নিষ্ক্রিয় করা যাবে না।'
							 ]));
				return;
			}
		}

		$this->FamilyModel->updateUserInfo(['active' => $new_active], $user_id);

		if (!empty($userInfoByID->user_full_tbl_id)) {
			$this->db->where('udc_list_auto_p_iidd', $userInfoByID->user_full_tbl_id)
					 ->update('customer_full_info', ['activity' => $new_active]);
		}

		$this->output->set_content_type('application/json')
					 ->set_output(json_encode([
						 'status' => 'success',
						 'active' => $new_active
					 ]));
	}

	public function entry_new_user_for_this_ins()
	{
		$userArr = array(
			'user_person_name' 		=> $this->input->post('names'),
			'user_phone_no' 		=> $this->input->post('phone'),
			'user_email_no' 		=> $this->input->post('email'),
			'address_full' 			=> $this->input->post('addrs'),
			'percentager' 			=> $this->input->post('cprcntg'),
			'div_a_iddd' 			=> $this->data['setting_info']->div_id,
			'dist_a_iddd' 			=> $this->data['setting_info']->dist_id,
			'up_a_iddd' 			=> $this->data['setting_info']->up_id,
			'un_a_iddd' 			=> $this->data['setting_info']->un_id,
			'institute_name' 		=> $this->data['setting_info']->ins_name,
			'ins_pr_iddds' 			=> tenant_code(),
			'activity' 				=> 1,
			'create_times_stamp' 	=> time(),
			'today_dates_updating' 	=> date('Y-m-d'),
			'user_img_base64' 		=> '',
			'user_uniq_qq_reails' 	=> uniqid(),
		);
		$last_usr_info_id = $this->FamilyModel->insert_new_user_this_ins($userArr); 

		$arrUsr = array(
			'username' 				=> $this->input->post('names'),
			'password' 				=> $this->ion_auth_model->hash_password($this->input->post('upass')),
			'email' 				=> $this->input->post('email'),
			'created_on' 			=> time(),
			'active' 				=> 1,
			'phone' 				=> $this->input->post('phone'),
			'ins_pr_id_s' 			=> tenant_code(),
			'user_full_tbl_id' 		=> $last_usr_info_id,
			'u_type' 				=> $this->input->post('uname'),
			'union_unq_idd_iii' 	=> $this->data['setting_info']->un_id,
		);
		$last_users_id = $this->FamilyModel->insert_new_user_s_infos($arrUsr); 

		$role_id = (int)$this->input->post('role_group_id');
		$allowed_roles = array(4, 5, 6, 7); // 4: uphead, 5: upsocib, 6: udc, 7: collect
		if (!in_array($role_id, $allowed_roles, TRUE)) {
			$role_id = 7;
		}

		$usrGrpArr = array(
			'user_id' 	=> $last_users_id, 
			'group_id' 	=> $role_id,
		); 
		$this->FamilyModel->insert_new_user_group_infos($usrGrpArr);

		// If chairman (4) or socib (5), also ensure they have admin group for general administrative privileges
		if (in_array($role_id, array(4, 5), TRUE)) {
			$this->FamilyModel->insert_new_user_group_infos(array(
				'user_id'  => $last_users_id,
				'group_id' => 1 // admin
			));
		}
	} 

	public function change_user_role()
	{
		if (!$this->ion_auth->in_group(array('admin', 'uphead', 'sadmin'))) {
			$this->output->set_content_type('application/json')->set_output(json_encode(['status' => 'error', 'message' => 'অনুমোদনহীন অ্যাক্সেস।']));
			return;
		}

		$user_id = (int) $this->input->post('user_id');
		$new_role_id = (int) $this->input->post('role_id');
		$allowed_roles = array(
			4 => 'uphead',
			5 => 'upsocib',
			6 => 'udc',
			7 => 'collect'
		);

		if (!isset($allowed_roles[$new_role_id])) {
			$this->output->set_content_type('application/json')->set_output(json_encode(['status' => 'error', 'message' => 'অকার্যকর ভূমিকা (Role)।']));
			return;
		}

		$user = $this->db->where('id', $user_id)->get('users')->row();
		if (!$user) {
			$this->output->set_content_type('application/json')->set_output(json_encode(['status' => 'error', 'message' => 'ব্যবহারকারী পাওয়া যায়নি।']));
			return;
		}

		// Support user protection
		if (strpos($user->username ?? '', 'support_') === 0 || strpos($user->email ?? '', '@support.viretadev.local') !== false) {
			$this->output->set_content_type('application/json')->set_output(json_encode(['status' => 'error', 'message' => 'অনুমোদনহীন অ্যাক্সেস।']));
			return;
		}

		// Tenant isolation check
		if ($user->ins_pr_id_s != tenant_code() && !$this->ion_auth->in_group('sadmin')) {
			$this->output->set_content_type('application/json')->set_output(json_encode(['status' => 'error', 'message' => 'অন্য ইউনিয়নের ব্যবহারকারীর রোল পরিবর্তন সম্ভব নয়।']));
			return;
		}

		// Delete existing functional roles (4, 5, 6, 7)
		$this->db->where('user_id', $user_id)
				 ->where_in('group_id', array(4, 5, 6, 7))
				 ->delete('users_groups');

		// Insert new role
		$this->db->insert('users_groups', [
			'user_id'  => $user_id,
			'group_id' => $new_role_id
		]);

		// If chairman (4) or socib (5), ensure admin group is also present
		if (in_array($new_role_id, array(4, 5), TRUE)) {
			$has_admin = $this->db->where('user_id', $user_id)->where('group_id', 1)->count_all_results('users_groups');
			if ($has_admin == 0) {
				$this->db->insert('users_groups', ['user_id' => $user_id, 'group_id' => 1]);
			}
		}

		$role_bn = [
			4 => 'ইউপি চেয়ারম্যান',
			5 => 'ইউপি সচিব',
			6 => 'ইউডিসি উদ্যোক্তা',
			7 => 'ট্যাক্স আদায়কারী'
		];

		$this->output->set_content_type('application/json')->set_output(json_encode([
			'status'  => 'success',
			'message' => 'ব্যবহারকারীর ভূমিকা সফলভাবে "' . $role_bn[$new_role_id] . '" হিসেবে পরিবর্তন করা হয়েছে।'
		]));
	} 
	
	public function update_plate_set()
	{
		$insDatasssssss = array(
			'plae_up_text' 				    => $this->input->post('up_text'),
			'plae_down_text' 				=> $this->input->post('down_text'),
			'plae_right_text' 				=> $this->input->post('danp_text'),
			'plae_left_text' 				=> $this->input->post('bamp_text'),
		);
		$this->FamilyModel->update_ins_infos($insDatasssssss);
		redirect('plates_set','refresh');
	}

	public function check_user_profile()
	{
		$uniqs = $this->input->get('check'); 
		if (empty($uniqs)) {
			redirect('userManagement', 'refresh');
			return;
		}

		$user_info = $this->FamilyModel->get_user_info_by_uniqID($uniqs);
		if (empty($user_info) && is_numeric($uniqs)) {
			$this->db->where('customer_full_info.udc_list_auto_p_iidd', (int)$uniqs);
			$this->db->or_where('users.id', (int)$uniqs);
			$this->db->join('users', 'users.user_full_tbl_id = customer_full_info.udc_list_auto_p_iidd', 'left');
			$this->db->join('users_groups', 'users_groups.user_id = users.id', 'left');
			$user_info = $this->db->get('customer_full_info')->row();
		}

		if (empty($user_info)) {
			redirect('userManagement', 'refresh');
			return;
		}

		$this->data['user_infos'] = $user_info;
		$user_tbl_id = $user_info->udc_list_auto_p_iidd ?? 0;
		$this->data['allInactiveCut'] = $this->FamilyModel->getAll_userInactiveCutting_amount($user_tbl_id);
		$this->data['totalCollects'] = $this->FamilyModel->get_user_collect_amount($user_tbl_id);
		$this->data['totalPaids'] = $this->FamilyModel->get_user_cutting_amount($user_tbl_id);

		$this->template->back('back/profile_info', $this->data);
	}
	
	public function admin_change_user_password()
	{
		$id = $this->input->post('id'); 

		$usrArr = array(
			
			'password' 				=> $this->ion_auth_model->hash_password($this->input->post('pass')),
		);
		$this->FamilyModel->updateUserInfo($usrArr, $this->input->post('id')); 
	}
	
	public function user_profile_view()
	{
		$this->template->back('back/view_profile', $this->data);
	}

	public function system_settings_fun()
	{
		$this->template->back('back/system_sets', $this->data);
	}

	public function plate_system_settings_fun()
	{
		$this->template->back('back/plates_system_sets', $this->data);
	}

	public function update_system_setting()
	{
		// The text fields were identical in all four former branches, so they
		// are built once here.
		$insDatas = array(
			'ins_name' 			=> $this->input->post('ins_name'),
			'ins_name_en' 		=> $this->input->post('ins_name_eng'),
			'ins_title_ss' 		=> $this->input->post('ins_title'),
			'ins_title_ens' 	=> $this->input->post('ins_title_eng'),
			'cm_name_bn' 		=> $this->input->post('chair_name_bn'),
			'cm_name_en' 		=> $this->input->post('chair_name_eng'),
			'addre_s_s' 		=> $this->input->post('ins_address'),
			'ins_address_eng' 	=> $this->input->post('ins_address_eng'),
			'ins_emails' 		=> $this->input->post('ins_email'),
			'chairman_phone' 	=> $this->input->post('ins_cm_phone'),
			'socib_phone' 		=> $this->input->post('ins_socib_phone'),
			'chairman_speach' 	=> $this->input->post('cm_speach'),
			'socib_speach' 		=> $this->input->post('socib_speach'),
			'bKash_userName' 	=> $this->input->post('bkash_user_name'),
			'bKash_passWord' 	=> $this->input->post('bkash_pass_word'),
			'bKash_authToken' 	=> $this->input->post('bkash_auth')
		);

		// Logos now go to uploads/tenant-<id>/ rather than into the database.
		// Replaced base64_encode(file_get_contents($_FILES[...])) calls, which
		// accepted any file type at all.
		//
		// A file that is not a real, permitted image is refused by
		// media_save(), and a refused upload is simply left out of the update
		// so the existing logo is preserved — writing NULL here would blank it.
		$logos = array(
			'union_logo_bn'  => array('ins_logo_path',    'logo'),
			'union_logo_eng' => array('ins_en_logo_path', 'logo-en'),
		);
		foreach ($logos as $field => $spec)
		{
			if (empty($_FILES[$field]['tmp_name']))
			{
				continue;
			}
			$path = media_save($_FILES[$field], $spec[1]);
			if ($path !== NULL)
			{
				$insDatas[$spec[0]] = $path;
			}
		}

		// If user is upsocib (Secretary / Deputy) and not uphead/sadmin:
		if ($this->ion_auth->in_group('upsocib') && !$this->ion_auth->in_group('uphead') && !$this->ion_auth->in_group('sadmin'))
		{
			$tenant_id = (int) tenant_code();
			if (function_exists('approval_propose'))
			{
				approval_propose('tenants.update', $insDatas, 'সচিব কর্তৃক সিস্টেম সেটিংস পরিবর্তনের প্রস্তাব', $tenant_id);
			}
			$this->session->set_flashdata('success', 'আপনার সেটিংস পরিবর্তনের প্রস্তাবটি চেয়ারম্যান মহোদয়ের অনুমোদনের জন্য প্রেরণ করা হয়েছে।');
			redirect('sys_set', 'refresh');
			return;
		}

		$this->FamilyModel->update_ins_infos($insDatas);
		$this->session->set_flashdata('success', 'সিস্টেম সেটিংস সফলভাবে আপডেট করা হয়েছে।');
		redirect('sys_set','refresh');
	}

	public function update_user_profile()
	{
		$data = array(
				'user_person_name' 	=> $this->input->post('full_name'),
				'user_phone_no' 	=> $this->input->post('phone_no'),
				'user_email_no' 	=> $this->input->post('e_mail_no'),
				'address_full' 		=> $this->input->post('f_addr_ss'),
			);
		$this->FamilyModel->update_mybasic_profile($data, $this->user_id); 

		$dataArr = array(
				'first_name' 			=> $this->input->post('full_name'),
				'phone' 				=> $this->input->post('phone_no'),
				'email' 				=> $this->input->post('e_mail_no'),
				'u_type' 				=> $this->input->post('iden_type'),
			);
		$this->FamilyModel->userInfos_update_by_cusID($dataArr, $this->user_id); 
		redirect('my_profile','refresh');
	}

	public function change_user_self_passwords()
	{
		$old_pass 		= $this->input->post('old');
		$new_pass 		= $this->input->post('new');
		$confirm_pass 	= $this->input->post('cnfrm');

		if (password_verify($old_pass, $this->ion_auth->user()->row()->password)) 
		{
			if ($new_pass == $confirm_pass) {
				$usrArr = array(
					'password' 				=> $this->ion_auth_model->hash_password($new_pass),
				);
				$this->FamilyModel->updateUserInfo($usrArr, $this->ion_auth->user()->row()->id); 
				$js_on = array(
					'msg'	=> 'Password Change Success.',
					'icon'	=> 'success'
				);
				$this->output->set_content_type('application/json')->set_output(json_encode($js_on));
			} else {
				$js_on = array(
					'msg'	=> 'Both password not match.',
					'icon'	=> 'warning'
				);
				$this->output->set_content_type('application/json')->set_output(json_encode($js_on));
			}
		} else {
			$js_on = array(
				'msg'	=> 'Old password not match.',
				'icon'	=> 'warning'
			);
			$this->output->set_content_type('application/json')->set_output(json_encode($js_on));
		}
	}

	public function change_chair_man_sign()
	{
        if ($this->ion_auth->in_group(array('uphead', 'upsocib', 'admin'))) {
			$this->template->back('back/change_cm_sign', $this->data);
		}else {
			redirect('logout', 'refresh');
		}
	}

	public function update_chairMan_signs()
	{
		// pass
		// $_FILES['sign_file']['tmp_name']
        if ($this->ion_auth->in_group(array('uphead', 'upsocib', 'admin'))) {
			if (password_verify($this->input->post('pass'), $this->ion_auth->user()->row()->password)) 
			{
				$path = media_save($_FILES['sign'], 'signature');
				if ($path === NULL)
				{
					echo 0;   // not a valid image — leave the stored one alone
					return;
				}
				$insDatas = array(
					'cm_sign_s64'	=> $path,
				);
				$this->FamilyModel->update_ins_infos($insDatas);
				echo 1;
			} else {
				echo 0;
			}
		} else {
			echo 0; 
		}
	}

	public function update_chairMan_pic_tures()
	{
		if ($this->ion_auth->in_group(array('uphead', 'upsocib', 'admin'))) {
			$path = media_save($_FILES['pics'], 'chairman');
			if ($path === NULL)
			{
				echo 0;
				return;
			}
			$insDatas = array(
				'cm_pic_base64'	=> $path,
			);
			$this->FamilyModel->update_ins_infos($insDatas);
			echo 1;
		} else {
			echo 0; 
		}
	}

	public function employee_manages_view_func()
	{
		$this->template->back('back/employee_view_file', $this->data);
	} 

	public function get_this_institute_employee()
	{
		$emp_infos = $this->FamilyModel->get_this_institute_employees(); 
		$this->output->set_content_type('application/json')->set_output(json_encode($emp_infos));
	}

	public function insert_this_institute_new_employee()
	{
		
		if (empty($_FILES['pic_file']['tmp_name'])) { 
			$dataArr = array(
				'emp_name' 				=> $this->input->post('names'),
				'emp_designation' 		=> $this->input->post('desig'),
				'emp_address' 			=> $this->input->post('addrs'),
				'emp_phone' 			=> $this->input->post('phone'),
				'emp_ins_code_id' 		=> tenant_code(),
				'emp_uniqs' 			=> uniqid(),
			);
			$this->FamilyModel->insert_this_institute_new_employee($dataArr);  
		}else {  
			$pic_s_file = base64_encode(file_get_contents( $_FILES['pic_file']['tmp_name'])); 
			$dataArr = array(
				'emp_pic_base64' 		=> $pic_s_file,
				'emp_name' 				=> $this->input->post('names'),
				'emp_designation' 		=> $this->input->post('desig'),
				'emp_address' 			=> $this->input->post('addrs'),
				'emp_phone' 			=> $this->input->post('phone'),
				'emp_ins_code_id' 		=> tenant_code(),
				'emp_uniqs' 			=> uniqid(),
			);
			$this->FamilyModel->insert_this_institute_new_employee($dataArr); 
		}   
	} 

	public function get_single_employee_by_id()
	{
		$empID = $this->input->post('emp_id'); 
		$emp_infos = $this->FamilyModel->get_this_institute_employee_by_id($empID); 
		$this->output->set_content_type('application/json')->set_output(json_encode($emp_infos));
	}     

	public function delete_single_employee_by_id()
	{
		$empID = $this->input->post('emp_id'); 
		$this->FamilyModel->delete_this_institute_employee_by_id($empID); 
	}

	public function up_members_view()
	{
		$this->template->back('back/up_members_view_files', $this->data);
	}  

	public function get_this_ins_up_members()
	{
		$emp_infos = $this->FamilyModel->get_this_ins_up_members(); 
		$this->output->set_content_type('application/json')->set_output(json_encode($emp_infos));
	}  

	public function insert_this_institute_new_up_member()
	{
		if (empty($_FILES['pic_file']['tmp_name'])) { 
			$dataArr = array(
				'up_mem_s_name' 			=> $this->input->post('names'),
				'ward_no_sq' 				=> $this->input->post('ward_no'),
				'mobile_s_no_phone' 		=> $this->input->post('phone'),
				'ins_atou_iddd_s' 			=> tenant_code(),
			);
			$this->FamilyModel->insert_this_institute_new_member_up($dataArr);  
		}else {  
			$pic_s_file = base64_encode(file_get_contents( $_FILES['pic_file']['tmp_name'])); 
			$dataArr = array(
				'up_mem_pic_base64s' 		=> $pic_s_file,
				'up_mem_s_name' 			=> $this->input->post('names'),
				'ward_no_sq' 				=> $this->input->post('ward_no'),
				'mobile_s_no_phone' 		=> $this->input->post('phone'),
				'ins_atou_iddd_s' 			=> tenant_code(),
			);
			$this->FamilyModel->insert_this_institute_new_member_up($dataArr); 
		}
	}   

	public function get_single_member_by_id()
	{
		$memID = $this->input->post('mem_id'); 
		$emp_infos = $this->FamilyModel->get_this_institute_members_by_id($memID); 
		$this->output->set_content_type('application/json')->set_output(json_encode($emp_infos));
	}   

	public function update_this_institute_edit_up_member()
	{  
		if (empty($_FILES['pic_file']['tmp_name'])) { 
			$dataArr = array(
				'up_mem_s_name' 			=> $this->input->post('names'),
				'ward_no_sq' 				=> $this->input->post('ward_no'),
				'mobile_s_no_phone' 		=> $this->input->post('phone')
			);
			$this->FamilyModel->update_this_institute_editing_member_up($dataArr, $this->input->post('memID'));  
		}else {  
			$pic_s_file = base64_encode(file_get_contents( $_FILES['pic_file']['tmp_name'])); 
			$dataArr = array(
				'up_mem_pic_base64s' 		=> $pic_s_file,
				'up_mem_s_name' 			=> $this->input->post('names'),
				'ward_no_sq' 				=> $this->input->post('ward_no'),
				'mobile_s_no_phone' 		=> $this->input->post('phone')
			);
			$this->FamilyModel->update_this_institute_editing_member_up($dataArr, $this->input->post('memID')); 
		}
	}   

	public function delete_single_member_by_id()
	{
		$memID = $this->input->post('memID'); 
		$this->FamilyModel->delete_this_institute_upMember_by_id($memID); 
	}    

	public function deposite_user_cash_view_method()
	{
		$this->data['totalCollect'] = $this->FamilyModel->get_user_collect_amount($this->user_id);  
		$this->data['totalCutting'] = $this->FamilyModel->get_user_cutting_amount($this->user_id);  

		$this->data['totalInactiveCut'] = $this->FamilyModel->get_userInactiveCutting_amount($this->user_id);  

		$this->data['allInactiveCut'] = $this->FamilyModel->getAll_userInactiveCutting_amount($this->user_id);  
		$this->data['allCollects'] = $this->FamilyModel->get_user_Allcollect_amount($this->user_id);  
		$this->data['allCutting'] = $this->FamilyModel->get_user_AllCutting_amount($this->user_id);  

		$this->template->back('back/deposite_user_cash_view_file', $this->data);  
	}   

	public function editingThisUserByUniqID()
	{
		$this->FamilyModel->edit_user_info_by_uniq_id(
			array(
				"user_person_name" 	=> $this->input->post('name'),
				"user_phone_no" 	=> $this->input->post('phone'),
				"user_email_no" 	=> $this->input->post('email'),
				"address_full" 		=> $this->input->post('address'),
				"percentager" 		=> $this->input->post('percent')
			), 
			$this->input->post('checking')
		); 
	}   

	public function collect_from_user_duess()
	{
		$userUniq = $this->input->post('userUniq');
		$amnt = $this->input->post('amnt');

		$this->data['user_infos'] = $this->FamilyModel->get_user_info_by_uniqID($userUniq); 

		$this->data['totalCollects'] = $this->FamilyModel->get_user_collect_amount($this->data['user_infos']->udc_list_auto_p_iidd);
		$this->data['totalPaids'] = $this->FamilyModel->get_user_cutting_amount($this->data['user_infos']->udc_list_auto_p_iidd);

		$totalDues = ($this->data['totalCollects'] - $this->data['totalPaids']) - ($this->data['totalCollects']*$this->data['user_infos']->percentager/100);

		if ($amnt > $totalDues) {
			echo 0;
		}else {
			$this->FamilyModel->insertCollectAmounts(
				array(
					"usr_amnt_cuttt" 	=> $amnt,
					"usr_idisidsid" 	=> $this->data['user_infos']->udc_list_auto_p_iidd,
					"insprsssid" 		=> tenant_code(),
					"timesingstime" 	=> time(),
					"dateingsdatesss" 	=> date('Y-m-d', time()),
					"check_aprov_ing" 	=> 1
				)
			); 
			echo 1;
		}
	}    

	public function deposite_self_cash_without_commission()
	{
		$userUniq = $this->input->post('userUniq');
		$amnt = $this->input->post('amnt');

		$this->data['user_infos'] = $this->FamilyModel->get_user_info_by_uniqID($userUniq); 

		$this->data['totalCollects'] = $this->FamilyModel->get_user_collect_amount($this->user_id);
		$this->data['totalPaids'] = $this->FamilyModel->get_user_cutting_amount($this->user_id);

		$totalDues = ($this->data['totalCollects'] - $this->data['totalPaids']) - ($this->data['totalCollects']*$this->data['user_infos']->percentager/100);

		if ($amnt > $totalDues) {
			echo 0;
		}else {
			$deposit_id = $this->FamilyModel->insertCollectAmounts(
				array(
					"usr_amnt_cuttt" 	=> $amnt,
					"usr_idisidsid" 	=> $this->user_id,
					"insprsssid" 		=> tenant_code(),
					"timesingstime" 	=> time(),
					"dateingsdatesss" 	=> date('Y-m-d', time()),
					"check_aprov_ing" 	=> 0
				)
			);

			$tenant_id = (int) tenant_code();
			if (function_exists('approval_propose')) {
				approval_propose('deposits.create', array(
					'deposit_id' => $deposit_id,
					'amount'     => $amnt,
					'collector'  => $this->data['user_infos']->user_person_name ?? 'কর আদায়কারী'
				), 'ক্যাশ ডিপোজিট অনুমোদন (৳' . $amnt . ')', $tenant_id);
			}

			echo 1;
		}
	}

	public function user_collected_history_by_date_to_date()
	{
		$startDate = $this->input->get('sDate');
		$endDate = $this->input->get('eDates');
		$userUniq = $this->input->get('check');
		$this->data['user_infos'] = $this->FamilyModel->get_user_info_by_uniqID($userUniq);
		if (!empty($this->data['user_infos'])) {
			$this->data['user_collect_historyDate'] = $this->FamilyModel->user_collect_history_date_to_date($startDate, $endDate, $this->data['user_infos']->udc_list_auto_p_iidd);
			$this->load->view('back/collected_historydate_viewFile', $this->data); 
		}else {
			echo "<script>window.close();</script>";
		}
	}	

	public function user_paid_infos_by_datetodate()
	{
		$startDate = $this->input->get('sDate');
		$endDate = $this->input->get('eDates');
		$userUniq = $this->input->get('check');
		$this->data['user_infos'] = $this->FamilyModel->get_user_info_by_uniqID($userUniq);
		if (!empty($this->data['user_infos'])) {
			$this->data['userPaidInfoDate'] = $this->FamilyModel->user_paid_info_date_to_date($startDate, $endDate, $this->data['user_infos']->udc_list_auto_p_iidd);
			$this->load->view('back/paid_infodate_viewFile', $this->data); 
		}else {
			echo "<script>window.close();</script>";
		}
	}  

	public function approve_deposite_by_id()
	{
		$this->FamilyModel->updateDepositeApprovingMethod(
			$this->input->post('id'), 
			$SDarry = array(
				'check_aprov_ing'	=> 1
			)
		);
		echo 1;
	}

	public function all_deposite_list()
	{
		$this->template->back('back/all_deposite_list_view_page', $this->data); 
	}

	public function all_pending_deposite_list()
	{
		$this->output->set_content_type('application/json')->set_output(json_encode($this->FamilyModel->getAll_InactiveCutting_amounts()));
	}

	public function report_method()
	{
		$this->data['users_info'] = $this->FamilyModel->get_all_users_infos();
		$this->template->back('back/reporting_view_page', $this->data); 
	}

	public function get_all_collect_date_to_date()
	{
		$sDate 		= date('Y-m-d', strtotime($this->input->get('sDate')));
		$eDates 	= date('Y-m-d', strtotime($this->input->get('eDates')));
		$this->data['colelcts_info'] = $this->FamilyModel->all_collect_history_date_to_date($sDate, $eDates);
		$this->load->view('back/all_collect_reports', $this->data); 
	}    

	public function get_all_paid_date_to_date()
	{
		$sDate 		= date('Y-m-d', strtotime($this->input->get('sDate'))); 
		$eDates 	= date('Y-m-d', strtotime($this->input->get('eDates'))); 
		$this->data['paids_info'] = $this->FamilyModel->users_all_paid_history_date_to_date($sDate, $eDates); 
		$this->load->view('back/users_all_paid_reports', $this->data); 
	}

	public function slider_manages_methods()
	{
		$this->data['slider'] = $this->SettingModel->get_ins_sliders();
		$this->template->back('back/slider_management_view_file', $this->data); 
	}

	public function get_all_slider_json_methods()
	{
		$this->output->set_content_type('application/json')->set_output(json_encode($this->SettingModel->get_ins_sliders()));
	}

	public function insert_slider_manage()
	{
		// Sliders are append-only, so each upload gets a unique name. That also
		// means replacing a slider cannot be served from a stale browser cache.
		$path = media_save($_FILES['pics'], 'slider-' . uniqid());
		if ($path === NULL)
		{
			return;   // not a valid image — do not create an empty slider row
		}
		$this->FamilyModel->insert_this_institute_new_sliderss(
			array(
				'sliderbase64' => $path,
				'insidpris_id' => tenant_code(),
			)); 
	}

	public function deleted_this_slider_ss()
	{
		$this->FamilyModel->delete_this_institute_slider_by_id($this->input->post('id')); 
	}
	// asadsdff 000077777000077777000077777 

















































	public function get_data_by_id()
	{
		$id = $this->input->post('familyID');
		$single_datas = $this->FamilyModel->get_single_dataSheet_by_id($id);
		$this->output->set_content_type('application/json')->set_output(json_encode($single_datas));
	}

	// public function family_dataset_form2()
	// {
	// 	$this->template->back('family_data_form', $this->data);
	// }

	public function tax_recipt()
	{
		// Was 'tax_collection', which CI3 resolves to application/views/
		// tax_collection.php — a file that does not exist. The view is
		// back/tax_collection.php, and two other methods (tax_collections_view
		// and tax_collection_form) already pass exactly that name. Missing
		// back/ prefix, nothing more; this route used to answer HTTP 500.
		$this->template->back('back/tax_collection', $this->data);
	}

	


	public function dashboard_b()
	{
		// Was 'dashboard' — CI3 looks for application/views/dashboard.php, which
		// does not exist, so this answered HTTP 500. Note that it was NOT
		// reaching back/dashboard.php either: a view name only matches the file
		// of the same name, and that 988-line file is an orphan (no other method
		// loads it) — see the archive note on it. index() serves the real
		// dashboard, back/fdash.php, so point at the same thing rather than
		// inventing a second dashboard or reviving dead markup.
		$this->template->back('back/fdash', $this->data);
	}




	public function search_all_data_searching()
	{
		$get_data = $this->input->post('input_val');

		if (strlen($get_data) == 10 || strlen($get_data) == 17)
		{
			// NID Number
			$data['get_search_data'] = $this->FamilyModel->serach_data_by_nid_num($get_data);
			$data['family_data'] = $this->FamilyModel->get_all_family_member_dataSheet($data['get_search_data']->datas_id);
			$this->output->set_content_type('application/json')->set_output(json_encode($data));
		}
		
		else
		{
			// Holding Number
			$data['get_search_data'] = $this->FamilyModel->serach_data_by_holding_num($get_data);
			$data['family_data'] = $this->FamilyModel->get_all_family_member_dataSheet($data['get_search_data']->data_id);
			$this->output->set_content_type('application/json')->set_output(json_encode($data));	
		}
	}

	public function insert_family_data_sheet_form_datas()
	{
		$data = array(
				'family_datasid' 	=> $this->input->post('holding_member_id'),
				'entry_usersIiD' 	=> $this->user_id,
				'in_s_pr_id_dd' 	=> tenant_code(),
				'member_name' 		=> $this->input->post('full_names'),
				'nid_bdris' 		=> $this->input->post('nid_nos'),
				'date_of_birth' 	=> $this->input->post('dob_nos'),
				'relation' 			=> $this->input->post('relations'),
				'profile_image' 	=> $this->input->post('members_photos'),
				'father_name' 		=> $this->input->post('fr_names'),
				'mother_name' 		=> $this->input->post('mr_names'),
				'create_date' 		=> date('Y-m-d', time()),
				'create_time_stamp' => time()
			);
		$this->AddMemberModel->insert_family_member_data_sheet_form_data($data); 
	}

	function get_family_member()
	{
		$main_id = $this->input->post('main_id');
		$member_data = $this->AddMemberModel->get_family_members_by_main_id($main_id);
		$this->output->set_content_type('application/json')->set_output(json_encode($member_data));
	}
	
	function delete_member_by_id()
	{
		$member_id = $this->input->post('member_id');
		$this->AddMemberModel->delete_member($member_id);
	}

	public function upload()
	{
		if ( ! empty($_FILES) ) {
			$config["upload_path"] = "./upload";
			$config["allowed_types"] = "jpeg|png|jpg";

			$this->load->library('upload', $config);
			
		} 
	}

	public function get_all_family_sheet_data()
	{
		
	}

	public function get_csrf_tocken()
	{
		echo $this->security->get_csrf_hash();
	}

	public function tax_collection_form()
	{
		$this->template->back('back/tax_collection', $this->data);
	}


	public function fun()
	{
		$n='2020-2021';

		$s = explode ("-", $n); 
		echo($s[0]);
		// echo date('Y', strtotime($s[0]));
	}

	public function insert_tax_paids()
	{
		$prsn_id = $this->input->post('person');
		$tk_amount = $this->input->post('amounts');
		
        $entry_sals   = array($this->input->post('entry_sals'));
        $entry_stk     = array($this->input->post('entry_stk'));

		$voic = time();

		$main_data = $this->FamilyModel->get_main_person_data_by_uniq($prsn_id);

		/*
		 * ছাড় (discount), per-invoice.
		 *
		 * Validated HERE and not only in the browser. The client strips the
		 * amount to digits and refuses a discount larger than the bill, but
		 * that is a courtesy to whoever is at the counter — it is not a
		 * gate. This endpoint is reachable by anything that can POST to
		 * /taxs_paid, and a discount larger than the bill would make
		 * payAmount negative and invert the meaning of the receipt.
		 *
		 * So the same three rules are enforced before the insert:
		 *   - amount must be a plain non-negative integer
		 *   - it must be strictly less than the bill
		 *   - a reason is required whenever there is a discount, capped to
		 *     the column's own 200 characters
		 *
		 * On any of these, return before insert_invoice_ss(). A rejected
		 * discount must not leave a half-written receipt behind.
		 *
		 * No role gate, deliberately: whoever can already press
		 * "আদায় করুন" in this parishad may grant the discount, per the
		 * owner. This is the same permission wearing a different hat, not a
		 * new one — and gating it would mean the collector could take money
		 * but not forgive any of it, which is not the rule.
		 *
		 * KNOWN CONSEQUENCE, CHOSEN ON PURPOSE — READ THIS BEFORE CHANGING IT
		 * ----------------------------------------------------------------
		 * This is a per-invoice discount, NOT a change to the holding's tax
		 * rate. Nothing here touches holding_rates_yearlys. The deliberate
		 * consequence is that the arrears figure does NOT fall:
		 *
		 *     total_unpaid = (bokeya_year * holding_rates_yearly) - paid_sum
		 *     and paid_sum comes from amount_paid.paid_amount, which is
		 *     inserted from the FORM'S entry_stk, untouched by the discount.
		 *
		 * So a ৳600 bill discounted to ৳500 records ৳500 on the receipt, and
		 * the holding still shows ৳600 outstanding. The citizen was relieved
		 * of ৳100 once; they were not granted a permanent reduction. The
		 * owner chose this explicitly.
		 *
		 * The two numbers are reconcilable and nothing is lost: gross =
		 * amnt_taka + discount_amounts, and total forgiven is a SUM over
		 * discount_amounts. What you do NOT get is an arrears figure reduced
		 * by past discounts, and if someone later reads a discounted holding
		 * as "they are behind by ৳600" that is the intended reading.
		 *
		 * To change this properly it needs a per-holding, per-year discount
		 * ledger — a new table, not a column on the invoice — because the
		 * arrears calculation would have to subtract forgiven amounts for
		 * the years they were granted, not for whichever invoice happens to
		 * be in front of it. That is a separate piece of work; do not
		 * approximate it by editing paid_amount here.
		 */
		$discount_input  = $this->input->post('discount_amount');
		$discount_reason = trim((string) $this->input->post('discount_reason'));

		$bill   = (int) $tk_amount;
		$disc   = ($discount_input === NULL || $discount_input === '') ? 0 : (int) $discount_input;
		$reason = mb_substr($discount_reason, 0, 200);

		$discount_error = '';
		if ($disc < 0) {
			$discount_error = 'ছাড়ের পরিমাণ ঋণাত্মক হতে পারে না।';
		} elseif ($disc > 0 && $bill > 0 && $disc >= $bill) {
			$discount_error = 'ছাড়ের পরিমাণ মোট টাকার সমান বা বেশি হতে পারবে না।';
		} elseif ($disc > 0 && $reason === '') {
			$discount_error = 'ছাড় দিলে কারণ লিখতে হবে।';
		}

		if ($discount_error !== '') {
			log_message('error', 'taxs_paid: discount rejected — ' . $discount_error
				. ' (bill=' . $bill . ' disc=' . $disc . ' user=' . (int) $this->user_id . ')');
			$this->output->set_content_type('application/json')
				->set_output(json_encode(array(
					'ok'  => false,
					'msg' => $discount_error,
				), JSON_UNESCAPED_UNICODE));
			return;
		}

		/*
		 * The NET is computed HERE, on the server, from the gross the
		 * browser sent. The form subtracts before posting, so a normal
		 * submission would otherwise send amounts = (gross - discount) AND
		 * discount_amount = discount — and the discount would be applied
		 * twice, under-charging the parishad.
		 *
		 * So the browser is authoritative for the GROSS and this endpoint
		 * derives the net itself. One rule, applied once, and it cannot
		 * drift from what the receipt will say.
		 */
		$net = $bill - $disc;

		$invoiceArryInsert = array(
			'amnt_taka' 			=> $net,
			'date_s_today_s' 		=> date('Y-m-d'),
			'time_s_s' 				=> time(),
			'user_iddii' 			=> $this->user_id,
			'family_data_id_id_dis' => $main_data->data_id,
			'invoice_no_s' 			=> $voic,
			'ins_ti_tute_pridd' 	=> tenant_code(),
			// amnt_taka is the NET — what was actually taken, which is what
			// the receipt says. The gross stays recoverable as
			// amnt_taka + discount_amounts, so no extra column is needed and
			// "how much was forgiven in total" is a SUM over the discount
			// column rather than a guess.
			'discount_amounts' 	=> $disc > 0 ? (string) $disc : NULL,
			'discount_refs_name'	=> $disc > 0 ? $reason : NULL,
		);
		$lasts_id = $this->FamilyModel->insert_invoice_ss($invoiceArryInsert);

        $eData = [];
        foreach ($entry_stk as $key => $value) {
            foreach ($value as $key1 => $value1) {
                $eData[] = [
                    'paidable_year'        	=>  $entry_sals[$key][$key1],
                    // From the FORM, not from $net. The form posts gross per
                    // year and the discount is a single amount on the
                    // invoice, so there is nothing here to discount per year.
                    //
                    // CONSEQUENCE, matching the decision documented in
                    // insert_tax_paids() above: paid_amount records the FULL
                    // gross, so a discounted year still counts as fully paid
                    // and total_unpaid does not fall. If you ever want
                    // arrears to fall, do NOT "fix" it by writing $net here —
                    // the net covers several years at once and would be
                    // subtracted from all of them. It needs a per-year
                    // discount ledger, which is separate work.
                    'paid_amount'        	=>  $entry_stk[$key][$key1],
                    'create_user_id'     	=>  $this->user_id,
                    'entry_date'     		=>  date('Y-m-d'),
                    'voucher'     			=>  $voic,
                    'holding_plate_number'  =>  $main_data->holding_number,
                    'create_date'     		=>  date('Y-m-d'),
                    'data_sheet_p_iiid'     =>  $main_data->data_id,
                    'in_s_titute_pr_idd'    =>  tenant_code(),
                    'invoice_au_idddd'     	=>  $lasts_id
                ];
            }
        }
        $this->FamilyModel->insertAmnts($eData);
		echo $voic;
	}

	public function approvals_view()
	{
		if (!$this->ion_auth->in_group(array('uphead', 'sadmin', 'admin', 'upsocib'))) {
			redirect('dashboard', 'refresh');
			return;
		}

		$tenant_id = (int) tenant_code();
		$this->data['current_auth_id'] = (int) $this->ion_auth->get_user_id();
		$this->data['can_decide_role'] = $this->ion_auth->in_group(array('uphead', 'upsocib', 'admin', 'sadmin'));
		$this->data['pending_queue'] = function_exists('approval_queue') ? approval_queue($tenant_id) : array();

		$this->db->select('a.*, ru.username AS requester_name, ru.email AS requester_email, du.username AS decider_name, du.email AS decider_email')
			->from('approvals a')
			->join('users ru', 'ru.id = a.requested_by', 'left')
			->join('users du', 'du.id = a.decided_by', 'left')
			->where('a.ins_pr_id_s', $tenant_id)
			->where('a.status !=', 'pending')
			->order_by('a.decided_at', 'DESC')
			->limit(30);
		$this->data['recent_decisions'] = $this->db->get()->result();

		foreach ($this->data['recent_decisions'] as $r) {
			$r->payload = is_string($r->payload) ? json_decode($r->payload, TRUE) : $r->payload;
		}

		$this->template->back('back/approvals_view_page', $this->data);
	}

	public function handle_approval_action()
	{
		$id = (int) $this->input->post('id');
		$decision = $this->input->post('decision');
		$note = trim((string) $this->input->post('note'));

		if (!$id || !in_array($decision, array('approved', 'rejected', 'cancelled'), TRUE)) {
			$this->output->set_content_type('application/json')->set_output(json_encode(array('ok' => FALSE, 'msg' => 'অকার্যকর অনুরোধ।')));
			return;
		}

		if ($decision === 'cancelled') {
			$res = approval_cancel($id);
			if ($res['ok']) {
				$this->output->set_content_type('application/json')->set_output(json_encode(array('ok' => TRUE, 'msg' => 'প্রস্তাবটি সফলভাবে প্রত্যাহার / বাতিল করা হয়েছে।')));
			} else {
				$this->output->set_content_type('application/json')->set_output(json_encode(array('ok' => FALSE, 'msg' => 'প্রস্তাবটি বাতিল করা সম্ভব হয়নি (' . ($res['why'] ?? 'ত্রুটি') . ')')));
			}
			return;
		}

		$res = approval_decide($id, $decision, $note ?: NULL);
		if ($res['ok']) {
			$msg = $decision === 'approved' ? 'অনুরোধটি সফলভাবে অনুমোদন করা হয়েছে এবং কার্যকর হয়েছে।' : 'অনুরোধটি সফলভাবে বাতিল করা হয়েছে।';
			$this->output->set_content_type('application/json')->set_output(json_encode(array('ok' => TRUE, 'msg' => $msg)));
		} else {
			$err_map = array(
				'self_approval'   => 'আপনি নিজের তৈরি করা প্রস্তাব নিজে অনুমোদন করতে পারবেন না।',
				'not_a_decider'   => 'আপনার অনুমোদন করার অনুমতি নেই। (শুধুমাত্র চেয়ারম্যান/সচিব/এডমিন অনুমোদন করতে পারবেন)',
				'already_decided' => 'এই প্রস্তাবটিতে ইতোমধ্যে সিদ্ধান্ত নেওয়া হয়েছে।',
				'wrong_tenant'    => 'ভিন্ন ইউনিয়নের প্রস্তাব অনুমোদন করা সম্ভব নয়।'
			);
			$msg = isset($err_map[$res['why']]) ? $err_map[$res['why']] : 'অনুমোদনের সময় ত্রুটি ঘটেছে।';
			$this->output->set_content_type('application/json')->set_output(json_encode(array('ok' => FALSE, 'msg' => $msg)));
		}
	}

	/**
	 * Tenant Support View
	 * Displays support tickets for the current tenant, allows creating new tickets and viewing conversations.
	 */
	public function support_view()
	{
		$tenant_id = (int) tenant_code();
		if ($tenant_id <= 0) {
			redirect('dashboard', 'refresh');
			return;
		}

		$current_user = $this->ion_auth->user()->row();
		$this->data['me'] = $current_user;

		// Summary counts
		$this->data['count_total'] = (int) $this->db->where('tenant_id', $tenant_id)->count_all_results('support_tickets');
		$this->data['count_open'] = (int) $this->db->where('tenant_id', $tenant_id)->where('status', 'open')->count_all_results('support_tickets');
		$this->data['count_pending'] = (int) $this->db->where('tenant_id', $tenant_id)->where('status', 'pending')->count_all_results('support_tickets');
		$this->data['count_resolved'] = (int) $this->db->where('tenant_id', $tenant_id)->where('status', 'resolved')->count_all_results('support_tickets');
		$this->data['count_closed'] = (int) $this->db->where('tenant_id', $tenant_id)->where('status', 'closed')->count_all_results('support_tickets');

		// Fetch all tickets for this tenant with reply counts
		$this->data['tickets'] = $this->db->select('t.*, COUNT(r.reply_id) AS reply_count')
			->from('support_tickets t')
			->join('support_replies r', 'r.ticket_id = t.ticket_id', 'left')
			->where('t.tenant_id', $tenant_id)
			->group_by('t.ticket_id')
			->order_by('t.created_at', 'DESC')
			->get()
			->result();

		$this->template->back('back/support_view_page', $this->data);
	}

	/**
	 * AJAX endpoint to fetch a single ticket and all its replies
	 */
	public function api_get_ticket_details($ticket_id)
	{
		$tenant_id = (int) tenant_code();
		$ticket_id = (int) $ticket_id;

		$ticket = $this->db->where('ticket_id', $ticket_id)
			->where('tenant_id', $tenant_id)
			->get('support_tickets')
			->row();

		if (!$ticket) {
			$this->output->set_status_header(404)
				->set_content_type('application/json')
				->set_output(json_encode(array('ok' => FALSE, 'msg' => 'টিকিট পাওয়া যায়নি।')));
			return;
		}

		$replies = $this->db->where('ticket_id', $ticket->ticket_id)
			->order_by('created_at', 'ASC')
			->get('support_replies')
			->result();

		$this->output->set_content_type('application/json')
			->set_output(json_encode(array(
				'ok' => TRUE,
				'ticket' => $ticket,
				'replies' => $replies
			)));
	}

	/**
	 * AJAX endpoint to create a new support ticket
	 */
	public function api_create_support_ticket()
	{
		$tenant_id = (int) tenant_code();
		if ($tenant_id <= 0) {
			$this->output->set_status_header(403)
				->set_content_type('application/json')
				->set_output(json_encode(array('ok' => FALSE, 'msg' => 'অনুমোদনহীন অনুরোধ।')));
			return;
		}

		$subject  = trim((string) $this->input->post('subject'));
		$category = trim((string) $this->input->post('category'));
		$priority = trim((string) $this->input->post('priority'));
		$phone    = trim((string) $this->input->post('phone'));
		$message  = trim((string) $this->input->post('message'));

		if ($subject === '') {
			$this->output->set_content_type('application/json')
				->set_output(json_encode(array('ok' => FALSE, 'msg' => 'টিকিটের বিষয় প্রদান করুন।')));
			return;
		}

		if (mb_strlen($subject) > 200) {
			$this->output->set_content_type('application/json')
				->set_output(json_encode(array('ok' => FALSE, 'msg' => 'বিষয় ২০০ অক্ষরের বেশি হতে পারবে না।')));
			return;
		}

		if ($message === '') {
			$this->output->set_content_type('application/json')
				->set_output(json_encode(array('ok' => FALSE, 'msg' => 'আপনার সমস্যা বা প্রশ্নটির বিস্তারিত লিখুন।')));
			return;
		}

		if (mb_strlen($message) > 4000) {
			$this->output->set_content_type('application/json')
				->set_output(json_encode(array('ok' => FALSE, 'msg' => 'বার্তা ৪০০০ অক্ষরের বেশি হতে পারবে না।')));
			return;
		}

		$valid_categories = array('general', 'technical', 'billing', 'domain', 'training', 'other');
		if (!in_array($category, $valid_categories, TRUE)) {
			$category = 'general';
		}

		$valid_priorities = array('low', 'normal', 'high', 'urgent');
		if (!in_array($priority, $valid_priorities, TRUE)) {
			$priority = 'normal';
		}

		$current_user = $this->ion_auth->user()->row();
		$user_id   = $current_user ? (int) $current_user->id : NULL;
		$user_name = $current_user ? (trim($current_user->first_name ?? '') ?: $current_user->username) : 'ইউজার';
		$user_email = $current_user ? $current_user->email : NULL;
		$user_phone = $phone !== '' ? $phone : ($current_user->phone ?? NULL);

		$placeholder = 'TKT-P' . str_pad((string) ((int) (microtime(TRUE) * 1000000)), 16, '0', STR_PAD_LEFT);

		$ok = $this->db->insert('support_tickets', array(
			'ticket_no'  => $placeholder,
			'tenant_id'  => $tenant_id,
			'user_id'    => $user_id,
			'user_name'  => $user_name,
			'user_email' => $user_email,
			'user_phone' => $user_phone,
			'subject'    => $subject,
			'category'   => $category,
			'priority'   => $priority,
			'message'    => $message,
			'status'     => 'open',
		));

		if (!$ok) {
			$this->output->set_status_header(500)
				->set_content_type('application/json')
				->set_output(json_encode(array('ok' => FALSE, 'msg' => 'টিকিট সংরক্ষণ করতে সমস্যা হয়েছে। আবার চেষ্টা করুন।')));
			return;
		}

		$new_ticket_id = (int) $this->db->insert_id();
		$ticket_no = 'TKT-' . str_pad((string) $new_ticket_id, 6, '0', STR_PAD_LEFT);

		$this->db->where('ticket_id', $new_ticket_id)->update('support_tickets', array('ticket_no' => $ticket_no));

		$this->output->set_content_type('application/json')
			->set_output(json_encode(array(
				'ok' => TRUE,
				'msg' => 'সাপোর্ট টিকিট সফলভাবে তৈরি হয়েছে। আমাদের টেকনিক্যাল টিম দ্রুত প্রতিক্রিয়া জানাবে।',
				'ticket_no' => $ticket_no
			)));
	}

	/**
	 * AJAX endpoint to reply to an existing support ticket
	 */
	public function api_reply_support_ticket()
	{
		$tenant_id = (int) tenant_code();
		$ticket_id = (int) $this->input->post('ticket_id');
		$message   = trim((string) $this->input->post('message'));

		if ($tenant_id <= 0 || !$ticket_id) {
			$this->output->set_status_header(400)
				->set_content_type('application/json')
				->set_output(json_encode(array('ok' => FALSE, 'msg' => 'অকার্যকর অনুরোধ।')));
			return;
		}

		if ($message === '') {
			$this->output->set_content_type('application/json')
				->set_output(json_encode(array('ok' => FALSE, 'msg' => 'বার্তার বিবরণ লিখুন।')));
			return;
		}

		if (mb_strlen($message) > 4000) {
			$this->output->set_content_type('application/json')
				->set_output(json_encode(array('ok' => FALSE, 'msg' => 'বার্তা ৪০০০ অক্ষরের বেশি হতে পারবে না।')));
			return;
		}

		$ticket = $this->db->where('ticket_id', $ticket_id)
			->where('tenant_id', $tenant_id)
			->get('support_tickets')
			->row();

		if (!$ticket) {
			$this->output->set_status_header(404)
				->set_content_type('application/json')
				->set_output(json_encode(array('ok' => FALSE, 'msg' => 'টিকিট পাওয়া যায়নি বা এই ইউনিয়নের নয়।')));
			return;
		}

		$current_user = $this->ion_auth->user()->row();
		$user_id   = $current_user ? (int) $current_user->id : NULL;
		$user_name = $current_user ? (trim($current_user->first_name ?? '') ?: $current_user->username) : 'ইউজার';

		$this->db->insert('support_replies', array(
			'ticket_id'   => $ticket->ticket_id,
			'sender_id'   => $user_id,
			'sender_name' => $user_name,
			'sender_role' => 'customer',
			'message'     => $message,
		));

		$update = array('updated_at' => date('Y-m-d H:i:s'));
		if ($ticket->status === 'resolved' || $ticket->status === 'closed') {
			$update['status']    = 'open';
			$update['closed_at'] = NULL;
		}

		$this->db->where('ticket_id', $ticket->ticket_id)->update('support_tickets', $update);

		/*
		 * Ring the doorbell for the support manager, so a tenant replying at
		 * 9pm does not wait for someone to reload /sadmin_support in the
		 * morning. Fired only after the reply row and the status update are
		 * both through, and guarded by function_exists because this
		 * controller predates the SSE helper and must not fatal if it is
		 * ever missing. Message text is not sent — the manager reads it on
		 * the ticket page.
		 */
		if (function_exists('support_event'))
		{
			support_event('reply', array(
				'ticket_id' => (int) $ticket->ticket_id,
				'ticket_no' => $ticket->ticket_no,
				'by'        => 'customer',
				'status'    => isset($update['status']) ? $update['status'] : $ticket->status,
				'reopened'  => isset($update['status']),
			));
		}

		$this->output->set_content_type('application/json')
			->set_output(json_encode(array(
				'ok' => TRUE,
				'msg' => 'উত্তর সফলভাবে যোগ হয়েছে।',
				'sender_name' => $user_name,
				'created_at' => date('d M, Y h:i A')
			)));
	}

	/**
	 * Subscription and Plan Renewal Page for Tenant
	 */
	public function subscription_view()
	{
		$tenant_id = (int) tenant_code();
		$sub = function_exists('tenant_subscription') ? tenant_subscription($tenant_id) : null;
		$is_expired = function_exists('is_tenant_subscription_expired') ? is_tenant_subscription_expired($tenant_id) : false;

		// Calculate remaining days
		$days_left = 0;
		if ($sub && !empty($sub->end_date)) {
			try {
				$today_dt = new DateTime(date('Y-m-d'));
				$end_dt = new DateTime($sub->end_date);
				$diff_dt = $today_dt->diff($end_dt);
				$days_left = $diff_dt->invert ? 0 : (int)$diff_dt->days;
			} catch (Exception $e) {
				$days_left = 0;
			}
		}

		// Available packages
		$packages = $this->db->where('is_active', true)->order_by('sort_order', 'ASC')->get('packages')->result();

		// Payment records for this subscription
		$payments = [];
		if ($sub && !empty($sub->subscription_id)) {
			$payments = $this->db->where('subscription_id', $sub->subscription_id)
				->order_by('paid_at', 'DESC')
				->order_by('payment_id', 'DESC')
				->get('subscription_payments')
				->result();
		}

		$this->data['subscription'] = $sub;
		$this->data['is_expired']   = $is_expired;
		$this->data['days_left']    = $days_left;
		$this->data['packages']     = $packages;
		$this->data['payments']     = $payments;

		$this->template->back('back/subscription_view', $this->data);
	}

	/**
	 * Submit renewal payment details from tenant admin (supports package upgrade/downgrade)
	 */
	public function submit_subscription_payment()
	{
		$tenant_id = (int) tenant_code();
		$sub = function_exists('tenant_subscription') ? tenant_subscription($tenant_id) : null;

		if (!$sub || empty($sub->subscription_id)) {
			$this->session->set_flashdata('error', 'কোনো সক্রিয় সাবস্ক্রিপশন রেকর্ড পাওয়া যায়নি। অনুগ্রহ করে সাপোর্টে যোগাযোগ করুন।');
			redirect('subscription', 'refresh');
			return;
		}

		$amount = (int) $this->input->post('amount');
		$method = trim($this->input->post('payment_method') ?? 'bkash');
		$reference = trim($this->input->post('reference') ?? '');
		$note = trim($this->input->post('note') ?? '');
		$package_id = (int) $this->input->post('package_id');

		$allowed_methods = ['bkash', 'nagad', 'bank', 'cash', 'cheque', 'other'];
		if (!in_array($method, $allowed_methods, true)) {
			$method = 'bkash';
		}

		if ($amount <= 0) {
			$this->session->set_flashdata('error', 'অনুগ্রহ করে সঠিক টাকার পরিমাণ লিখুন।');
			redirect('subscription', 'refresh');
			return;
		}

		if (empty($reference)) {
			$this->session->set_flashdata('error', 'পেমেন্ট রেফারেন্স বা TrxID নম্বর দেওয়া আবশ্যক।');
			redirect('subscription', 'refresh');
			return;
		}

		// Handle package upgrade or downgrade if a new package was selected
		$plan_name_bn = '';
		if ($package_id > 0) {
			$new_pkg = $this->db->where('package_id', $package_id)->where('is_active', true)->get('packages')->row();
			if ($new_pkg && (int)$sub->package_id !== (int)$new_pkg->package_id) {
				$this->db->where('subscription_id', $sub->subscription_id)->update('subscriptions', ['package_id' => $new_pkg->package_id]);
				$this->db->where('ins_titute_p_idds', $tenant_id)->update('ins_titute', [
					'package_key' => $new_pkg->package_key,
					'theme_ss'    => $new_pkg->package_key,
				]);
				$plan_name_bn = $new_pkg->name_bn;
			}
		}

		$invoice_no = 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), 8, 4));

		$insert_data = array(
			'subscription_id' => $sub->subscription_id,
			'invoice_no'      => $invoice_no,
			'amount'          => $amount,
			'payment_method'  => $method,
			'reference'       => $reference,
			'paid_at'         => date('Y-m-d'),
			'note'            => $note ?: ($plan_name_bn ? 'প্যাকেজ পরিবর্তন (' . $plan_name_bn . ') ও নবায়ন পেমেন্ট তথ্য।' : 'টেন্যান্ট পোর্টাল থেকে নবায়ন পেমেন্ট তথ্য জমা দেওয়া হয়েছে।'),
			'created_by'      => $this->user_id,
			'created_at'      => date('Y-m-d H:i:s')
		);

		$this->db->insert('subscription_payments', $insert_data);

		$msg = 'আপনার সাবস্ক্রিপশন নবায়ন পেমেন্ট রসিদ (ইনভয়েস #' . $invoice_no . ') সফলভাবে জমা হয়েছে।';
		if (!empty($plan_name_bn)) {
			$msg .= ' আপনার নির্বাচিত প্যাকেজ (' . $plan_name_bn . ') সক্রিয় করা হয়েছে।';
		}
		$this->session->set_flashdata('success', $msg);
		redirect('subscription', 'refresh');
	}

	/**
	 * Instant package upgrade / downgrade
	 */
	public function change_subscription_package()
	{
		$tenant_id = (int) tenant_code();
		$package_id = (int) $this->input->post('package_id');
		$sub = function_exists('tenant_subscription') ? tenant_subscription($tenant_id) : null;

		if (!$sub || empty($sub->subscription_id)) {
			$this->session->set_flashdata('error', 'কোনো সাবস্ক্রিপশন রেকর্ড পাওয়া যায়নি।');
			redirect('subscription', 'refresh');
			return;
		}

		$new_pkg = $this->db->where('package_id', $package_id)->where('is_active', true)->get('packages')->row();
		if (!$new_pkg) {
			$this->session->set_flashdata('error', 'নির্বাচিত প্যাকেজটি পাওয়া যায়নি।');
			redirect('subscription', 'refresh');
			return;
		}

		if ((int)$sub->package_id === (int)$new_pkg->package_id) {
			$this->session->set_flashdata('error', 'আপনার পরিষদ ইতিমধ্যে ' . $new_pkg->name_bn . ' প্যাকেজে সক্রিয় রয়েছে।');
			redirect('subscription', 'refresh');
			return;
		}

		$this->db->trans_start();
		$this->db->where('subscription_id', $sub->subscription_id)->update('subscriptions', ['package_id' => $new_pkg->package_id]);
		$this->db->where('ins_titute_p_idds', $tenant_id)->update('ins_titute', [
			'package_key' => $new_pkg->package_key,
			'theme_ss'    => $new_pkg->package_key,
		]);
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			$this->session->set_flashdata('error', 'প্যাকেজ পরিবর্তন করতে সমস্যা হয়েছে। অনুগ্রহ করে আবার চেষ্টা করুন।');
		} else {
			$this->session->set_flashdata('success', 'প্যাকেজ সফলভাবে পরিবর্তন করা হয়েছে! বর্তমান প্যাকেজ: ' . $new_pkg->name_bn . '।');
		}

		redirect('subscription', 'refresh');
	}

	/**
	 * View and Print Subscription Invoice
	 */
	public function subscription_invoice($invoice_no = '')
	{
		$tenant_id = (int) tenant_code();
		$invoice_no = trim($invoice_no);
		if (empty($invoice_no)) {
			show_404();
			return;
		}

		$this->db->select('sp.*, s.ins_titute_p_id, s.billing_cycle, s.start_date, s.end_date, s.status as sub_status,
		                   p.name_bn as pkg_name_bn, p.name_en as pkg_name_en, p.price_yearly, p.features,
		                   i.ins_name, i.ins_logo_path, i.cm_name_bn, i.cm_name_en, i.socib_phone, i.chairman_phone,
		                   i.sub_domains, i.domain_names, i.addre_s_s, i.ins_emails');
		$this->db->from('subscription_payments sp');
		$this->db->join('subscriptions s', 's.subscription_id = sp.subscription_id', 'inner');
		$this->db->join('packages p', 'p.package_id = s.package_id', 'left');
		$this->db->join('ins_titute i', 'i.ins_titute_p_idds = s.ins_titute_p_id', 'left');
		$this->db->where('sp.invoice_no', $invoice_no);

		// IDOR check: Tenant only accesses their own invoice (sadmin exempt)
		if (!$this->ion_auth->in_group('sadmin')) {
			$this->db->where('s.ins_titute_p_id', $tenant_id);
		}

		$payment = $this->db->get()->row();
		if (!$payment) {
			show_error('অনুরোধকৃত ইনভয়েস পাওয়া যায়নি অথবা দেখার অনুমতি নেই।', 404, 'ইনভয়েস পাওয়া যায়নি');
			return;
		}

		$this->data['payment']    = $payment;
		$this->data['invoice_no'] = $invoice_no;

		// Load clean, printable invoice view
		$this->load->view('back/subscription_invoice_view', $this->data);
	}

}