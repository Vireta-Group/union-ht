<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class FamilyModel extends CI_Model {

    public function __construct() 
    {
        parent::__construct();
        $this->load->database();
    }

	public function get_all_family_dataSheet()
    {
        $this->db->where('ins_aut_iiid', tenant_code());
        $sql = $this->db->get('family_member');
		return $sql->row();
    }
    
    public function get_all_family_member_dataSheet($id)
    {
        $this->db->where('family_datasid', $id);
        $sql = $this->db->get('family_member');
		return $sql->result();
    }

    public function serach_data_by_nid_num($id)
    {
        $this->db->where('ins_aut_iiid', tenant_code());
        $this->db->where('nid_number', $id);
        $sql = $this->db->get('family_data_sheet');
        return $sql->row();
    }

    public function search_member_by_nid_bdris($number)
    {
        $this->db->where('in_s_pr_id_dd', tenant_code());
        $this->db->where('nid_bdris', $number);
        return $this->db->get('family_member')->row();
    }

    /**
     * Resolve a holding search query to a single family_data_sheet row.
     * Supports Bengali numerals (০-৯ -> 0-9), formatted holdings (02-01, 2-1),
     * bare plate numbers (02, 2, 5045), uniqID, mobile number, and name fallback.
     */
    private function _query_family_by_holding($id, $select = null, $with_joins = false)
    {
        $raw = trim((string)$id);
        if ($raw === '') {
            return null;
        }

        $ascii = function_exists('platform_ascii_digits') ? platform_ascii_digits($raw) : $raw;
        $ascii = trim($ascii);
        $normalized = preg_replace('/\s*[-\x{2013}\x{2014}\/]\s*/u', '-', $ascii);
        $normalized = trim($normalized);

        $candidates = [];
        if ($raw !== '') $candidates[] = $raw;
        if ($ascii !== '') $candidates[] = $ascii;
        if ($normalized !== '') $candidates[] = $normalized;

        if (strpos($normalized, '-') !== false) {
            $parts = explode('-', $normalized, 2);
            $plate = trim($parts[0]);
            $ward  = trim($parts[1]);
            if (is_numeric($plate) && is_numeric($ward)) {
                $p_int = (int)$plate;
                $w_int = (int)$ward;
                $candidates[] = sprintf('%02d-%02d', $p_int, $w_int);
                $candidates[] = sprintf('%d-%02d', $p_int, $w_int);
                $candidates[] = sprintf('%02d-%d', $p_int, $w_int);
                $candidates[] = sprintf('%d-%d', $p_int, $w_int);
                $candidates[] = $plate . '-' . sprintf('%02d', $w_int);
                $candidates[] = $plate . '-' . $w_int;
            }
        }
        $candidates = array_values(array_unique(array_filter($candidates)));

        $build_base = function() use ($select, $with_joins) {
            if ($select) {
                $this->db->select($select);
            }
            $this->db->where('family_data_sheet.ins_aut_iiid', tenant_code());
            if ($with_joins) {
                $this->db->join('villages_of_union', 'villages_of_union.villages_of_union_a_iidd = family_data_sheet.vill_pr_iddsid', 'left');
                $this->db->join('house_home_name_etc', 'house_home_name_etc.house_home_name_etc_iddiddd = family_data_sheet.house_etc_a_iddd', 'left');
            }
        };

        // 1. Exact match against candidate holding numbers
        $build_base();
        $this->db->where_in('family_data_sheet.holding_number', $candidates);
        $this->db->limit(1);
        $res = $this->db->get('family_data_sheet')->row();
        if ($res) {
            return $res;
        }

        // 2. Prefix match if user supplied a bare holding plate without hyphen (e.g. 02, 2, 5045)
        if (strpos($normalized, '-') === false && $normalized !== '') {
            $prefixes = [$normalized . '-'];
            if (is_numeric($normalized)) {
                $n_int = (int)$normalized;
                $prefixes[] = sprintf('%02d-', $n_int);
                $prefixes[] = $n_int . '-';
            }
            $prefixes = array_values(array_unique($prefixes));

            $build_base();
            $this->db->group_start();
            foreach ($prefixes as $pfx) {
                $this->db->or_like('family_data_sheet.holding_number', $pfx, 'after');
            }
            $this->db->group_end();
            $this->db->order_by('family_data_sheet.holding_number', 'ASC');
            $this->db->limit(1);
            $res = $this->db->get('family_data_sheet')->row();
            if ($res) {
                return $res;
            }
        }

        // 3. Fallback: match by uniqID
        $build_base();
        $this->db->where_in('family_data_sheet.uniqID', $candidates);
        $this->db->limit(1);
        $res = $this->db->get('family_data_sheet')->row();
        if ($res) {
            return $res;
        }

        // 4. Fallback: mobile number (if 10 or 11 digits)
        if (preg_match('/^01\d{8,9}$/', $ascii)) {
            $build_base();
            $this->db->group_start();
            $this->db->where('family_data_sheet.mobile_number_one', $ascii);
            $this->db->or_where('family_data_sheet.mobile_number_two', $ascii);
            $this->db->group_end();
            $this->db->limit(1);
            $res = $this->db->get('family_data_sheet')->row();
            if ($res) {
                return $res;
            }
        }

        // 5. Fallback: person name if 3+ characters and not pure digits
        if (!is_numeric($ascii) && mb_strlen($raw) >= 3) {
            $build_base();
            $this->db->like('family_data_sheet.family_person_name_head', $raw);
            $this->db->limit(1);
            $res = $this->db->get('family_data_sheet')->row();
            if ($res) {
                return $res;
            }
        }

        return null;
    }

    public function serach_data_by_holding_num($id)
    {
        return $this->_query_family_by_holding($id, null, true);
    }

    public function serach_data_by_uniq_num($id)
    {
        $this->db->where('ins_aut_iiid', tenant_code());
        $this->db->where('uniqID', $id);
        $this->db->join('villages_of_union', 'villages_of_union.villages_of_union_a_iidd = family_data_sheet.vill_pr_iddsid', 'left');
        $this->db->join('house_home_name_etc', 'house_home_name_etc.house_home_name_etc_iddiddd = family_data_sheet.house_etc_a_iddd', 'left');

        $sql = $this->db->get('family_data_sheet');
        return $sql->row();
    }

    public function get_paid_amount_by_mainID($id)
    {
        $this->db->where('in_s_titute_pr_idd', tenant_code());
        $this->db->select('paidable_year, paid_amount, entry_date, voucher');
        $this->db->where('data_sheet_p_iiid', $id);
        $sql = $this->db->get('amount_paid');
        return $sql->result();
    }

    public function serachings_by_holding_num($id)
    {
        $select = 'data_id, family_person_name_head, mobile_number_one, mobile_number_two, word_number, holding_starting_date, holding_condition, holding_rates_yearly, uniqID, holding_number, holding_starting_years';        
        return $this->_query_family_by_holding($id, $select, false);
    }

    public function get_main_person_data_by_uniq($id)
    {
        $this->db->where('data_id', $id);
        $sql = $this->db->get('family_data_sheet');
        return $sql->row();
    }

    public function search_holding_rat_es($id)
    {
        $this->db->where('usr_ide_id', $id);
        $sql = $this->db->get('holding_rates_yearlys');
        return $sql->result();
    }

    public function insert_invoice_ss($arry)
    {
        $this->db->insert('collect_amnt_invoice', $arry);
        return $this->db->insert_id();
    }

    public function insertAmnts($arrs)
    {
        $this->db->insert_batch('amount_paid', $arrs);
    }

    public function get_paidAmntByVocherNo($unq)
    {
        $this->db->where('voucher', $unq);
        $sql = $this->db->get('amount_paid');
        return $sql->result();
    }

    public function get_invoiceByVocherNo($unq)
    {
        // discount_amounts / discount_refs_name are here so the printed
        // money receipt can show the gross, the discount and who granted it.
        // amnt_taka is the NET, so gross = amnt_taka + discount_amounts.
        $this->db->select('data_id,family_person_name_head,pason_father_name,parson_mother_name,mobile_number_one,mobile_number_two,holding_number,word_number,village_name,profile_image,holding_condition,holding_rates_yearly,holding_starting_years,uniqID,bn_vill_names,en_vill_name,house_na_me,user_person_name,amnt_taka,invoice_no_s,time_s_s,discount_amounts,discount_refs_name');

        $this->db->where('invoice_no_s', $unq);

        $this->db->join('family_data_sheet', 'family_data_sheet.data_id = collect_amnt_invoice.family_data_id_id_dis', 'left');

        $this->db->join('customer_full_info', 'customer_full_info.udc_list_auto_p_iidd = collect_amnt_invoice.user_iddii', 'left');

        $this->db->join('villages_of_union', 'villages_of_union.villages_of_union_a_iidd = family_data_sheet.vill_pr_iddsid', 'left');
        
        $this->db->join('house_home_name_etc', 'house_home_name_etc.house_home_name_etc_iddiddd = family_data_sheet.house_etc_a_iddd', 'left');
        $sql = $this->db->get('collect_amnt_invoice');
        return $sql->row();
    }

    public function update_mybasic_profile($data, $id)
    {
        $this->db->where('udc_list_auto_p_iidd', $id);
        $this->db->update('customer_full_info', $data);
    }

	/**
	 * Return the newest paidable_year for each given family data id.
	 * Result is keyed by data_sheet_p_iiid so the controller can map it easily.
	 *
	 * @param array $ids List of family_data_sheet.data_id values
	 * @return array Associative array of data_id => last paidable_year
	 */
	public function get_last_paid_years_by_ids($ids)
	{
		if (empty($ids)) {
			return array();
		}

		$this->db->select('data_sheet_p_iiid, MAX(paidable_year) AS last_paid_year');
		$this->db->where('in_s_titute_pr_idd', tenant_code());
		$this->db->where_in('data_sheet_p_iiid', $ids);
		$this->db->group_by('data_sheet_p_iiid');

		$sql = $this->db->get('amount_paid');

		$result = array();
		foreach ($sql->result() as $row) {
			$result[$row->data_sheet_p_iiid] = $row->last_paid_year;
		}
		return $result;
	}

	public function get_all_dataSheet_by_ward($wards)
    {
        $this->db->select('data_id,family_person_name_head,pason_father_name,parson_mother_name,mobile_number_one,mobile_number_two,holding_number,word_number,village_name,profile_image,holding_condition,holding_rates_yearly,holding_starting_years,uniqID,bn_vill_names,en_vill_name,house_na_me');

        $this->db->where('ins_aut_iiid', tenant_code());
        $this->db->where('word_number', $wards);

        $this->db->join('villages_of_union', 'villages_of_union.villages_of_union_a_iidd = family_data_sheet.vill_pr_iddsid', 'left');

        $this->db->join('house_home_name_etc', 'house_home_name_etc.house_home_name_etc_iddiddd = family_data_sheet.house_etc_a_iddd', 'left');
        // $this->db->order_by("CAST(SUBSTRING_INDEX(holding_number, '-', 1) AS UNSIGNED) ASC");
        $this->db->order_by("split_part(holding_number, '-', 1)::INTEGER", "", FALSE);

        $sql = $this->db->get('family_data_sheet');
		return $sql->result();
    }

	public function getAllUniqsDataByWord($ward)
    {
        $this->db->select('uniqID,family_person_name_head,holding_number');
        
        $this->db->where('ins_aut_iiid', tenant_code());
        $this->db->where('word_number', $ward);

        $sql = $this->db->get('family_data_sheet');
		return $sql->result();
    }

	public function get_holdings_infos_byhouse_no($h_no)
    {
        $this->db->select('data_id,family_person_name_head,pason_father_name,parson_mother_name,mobile_number_one,mobile_number_two,holding_number,word_number,village_name,profile_image,holding_condition,holding_rates_yearly,holding_starting_years,uniqID,bn_vill_names,en_vill_name');
        
        $this->db->join('villages_of_union', 'villages_of_union.villages_of_union_a_iidd = family_data_sheet.vill_pr_iddsid', 'left');

        $this->db->where('ins_aut_iiid', tenant_code());
        $this->db->where('house_etc_a_iddd', $h_no);
        $sql = $this->db->get('family_data_sheet');
		return $sql->result();
    }

    public function get_invoiceByPersonID($id)
    {
        // discount_amounts / discount_refs_name are selected so the receipt
        // list can show what was forgiven on a discounted bill. They are
        // NULL on all 1659 pre-existing rows, and the view treats NULL as
        // "no discount" rather than printing an empty line.
        $this->db->select('data_id,family_person_name_head,holding_number,uniqID,bn_vill_names,en_vill_name,amnt_taka,date_s_today_s,time_s_s,invoice_no_s,user_person_name,discount_amounts,discount_refs_name');

        $this->db->where('family_data_id_id_dis', $id);
        
        $this->db->join('family_data_sheet', 'family_data_sheet.data_id = collect_amnt_invoice.family_data_id_id_dis', 'left');

        $this->db->join('customer_full_info', 'customer_full_info.udc_list_auto_p_iidd = collect_amnt_invoice.user_iddii', 'left');

        $this->db->join('villages_of_union', 'villages_of_union.villages_of_union_a_iidd = family_data_sheet.vill_pr_iddsid', 'left');
        
        $this->db->join('house_home_name_etc', 'house_home_name_etc.house_home_name_etc_iddiddd = family_data_sheet.house_etc_a_iddd', 'left');

        $sql = $this->db->get('collect_amnt_invoice');
        return $sql->result();
    }

	public function get_single_dataSheet_by_id($id)
    {
        $this->db->select('data_id,family_person_name_head,pason_father_name,parson_mother_name,parson_paranent_address,parson_parmanent_address,nid_number,date_of_birth,mobile_number_one,mobile_number_two,holding_number,word_number,village_name,profile_image,holding_condition,holding_rates_yearly,home_image,holding_starting_years');
        $this->db->where('data_id', $id);
        $sql = $this->db->get('family_data_sheet');
		return $sql->row();
    }

    public function update_ins_infos($data)
    {
        $this->db->where('ins_titute_p_idds', tenant_code());
        $this->db->update('ins_titute', $data);
    }

	public function get_invoice_date_to_date($s, $e)
    {
        $this->db->where('ins_ti_tute_pridd', tenant_code());
        $this->db->select('family_person_name_head, word_number, village_name, amnt_taka, time_s_s, invoice_no_s');
        $this->db->where('date_s_today_s >=', $s);
        $this->db->where('date_s_today_s <=', $e);
        $this->db->join('family_data_sheet', 'family_data_sheet.data_id = collect_amnt_invoice.family_data_id_id_dis', 'left');
        $query = $this->db->get('collect_amnt_invoice');
        return $query->result();
    }

	public function get_users_infos()
    {
        $tenant_id = (int) tenant_code();
        $this->db->order_by('users.id', 'asc'); 
        $this->db->select('users.username, users.id, customer_full_info.user_person_name, customer_full_info.udc_list_auto_p_iidd, customer_full_info.user_phone_no, customer_full_info.address_full, users.active, customer_full_info.user_img_base64, customer_full_info.percentager, users.user_full_tbl_id, customer_full_info.user_uniq_qq_reails');
        $this->db->where('users.ins_pr_id_s', $tenant_id);
        $this->db->where("users.username NOT LIKE 'support_%'");
        $this->db->where("users.email NOT LIKE '%@support.viretadev.local'");
        $this->db->join('customer_full_info', 'customer_full_info.udc_list_auto_p_iidd = users.user_full_tbl_id', 'left');
        $users = $this->db->get('users')->result();

        if (empty($users)) {
            return array();
        }

        $user_ids = array_map(function($u) { return (int)$u->id; }, $users);
        $this->db->select('ug.user_id, g.id AS group_id, g.name AS group_name, g.description AS group_desc')
            ->from('users_groups ug')
            ->join('groups g', 'g.id = ug.group_id')
            ->where_in('ug.user_id', $user_ids);
        $groups_rows = $this->db->get()->result();

        $user_groups = array();
        foreach ($groups_rows as $gr) {
            $user_groups[$gr->user_id][] = $gr;
        }

        foreach ($users as $u) {
            $u->groups = isset($user_groups[$u->id]) ? $user_groups[$u->id] : array();
            $primary_role = 'collect';
            $primary_role_id = 7;
            $group_names = array();
            foreach ($u->groups as $g) {
                $group_names[] = $g->group_name;
                if (in_array($g->group_name, array('uphead', 'upsocib', 'udc', 'collect'), TRUE)) {
                    $primary_role = $g->group_name;
                    $primary_role_id = (int)$g->group_id;
                }
            }
            $u->primary_role = $primary_role;
            $u->primary_role_id = $primary_role_id;
            $u->group_names = implode(', ', $group_names);
        }

        return $users;
    }

	public function get_invoice_date_to_date_nd_user($s, $e, $usr)
    {
        $this->db->where('ins_ti_tute_pridd', tenant_code());
        $this->db->select('family_person_name_head, word_number, village_name, amnt_taka, time_s_s, invoice_no_s, user_person_name');
        $this->db->where('date_s_today_s >=', $s);
        $this->db->where('date_s_today_s <=', $e);
        $this->db->where('user_iddii', $usr);
        $this->db->join('family_data_sheet', 'family_data_sheet.data_id = collect_amnt_invoice.family_data_id_id_dis', 'left');
        $this->db->join('customer_full_info', 'customer_full_info.udc_list_auto_p_iidd = collect_amnt_invoice.user_iddii', 'left');
        $query = $this->db->get('collect_amnt_invoice');
        return $query->result();
    }

	public function serach_up_member($ward)
    {
        $this->db->where('ins_atou_iddd_s', tenant_code());
        $this->db->where('ward_no_sq', $ward);
        $query = $this->db->get('up_member_s');
        return $query->row();
    }

    public function get_all_houses_infos_byward($ward)
    {
        $this->db->where('ins_id_pr_idautoss', tenant_code());
        $this->db->where('wrds_no_s', $ward);
        $this->db->join('villages_of_union', 'villages_of_union.villages_of_union_a_iidd = house_home_name_etc.vill_au_iddsss', 'left');
        $query = $this->db->get('house_home_name_etc');
        return $query->result();
    }

    public function get_villages_infos_byward($ward)
    {
        $this->db->where('ins_idiidd_for_primary_ss', tenant_code());
        $this->db->where('ward_noss', $ward);
        $query = $this->db->get('villages_of_union');
        return $query->result();
    }

    public function get_houses_infos_byvillage_no($vill_no)
    {
        $this->db->where('ins_id_pr_idautoss', tenant_code());
        $this->db->where('vill_au_iddsss', $vill_no);
        $query = $this->db->get('house_home_name_etc');
        return $query->result();
    }

    public function insert_new_home_name($arrData)
    {
        $this->db->insert('house_home_name_etc', $arrData);
    }

    public function edit_home_names_by_p_idd($arr, $id)
    {
        $this->db->where('house_home_name_etc_iddiddd', $id);
        $this->db->update('house_home_name_etc', $arr);
    }

    public function updates_data_sheetinfos($_id, $arr)
    {
        $this->db->where('data_id', $_id);
        $this->db->update('family_data_sheet', $arr);
    }

    public function insert_new_user_this_ins($userArr)
    {
        $this->db->insert('customer_full_info', $userArr);
        return $this->db->insert_id();
    }

    public function insert_new_user_s_infos($userData)
    {
        $this->db->insert('users', $userData);
        return $this->db->insert_id();
    }

    public function insert_new_user_group_infos($datas)
    {
        $this->db->insert('users_groups', $datas);
        return $this->db->insert_id();
    }

    public function insert_this_institute_new_employee($empData)
    {
        $this->db->insert('employee_data_ins', $empData);
        return $this->db->insert_id();
    }

    public function updateUserInfo($usrArr, $id)
    {
        $this->db->where('id', $id);
        $this->db->update('users', $usrArr);
    }

    public function userInfos_update_by_cusID($usrArr, $id)
    {
        $this->db->where('union_unq_idd_iii', $id);
        $this->db->update('users', $usrArr);
    }

    public function getUserSingleInfo($id)
    {
        $this->db->where('id', $id);
        return $this->db->get('users')->row();
    }   

    public function get_this_institute_employee_by_id($id)
    {
        $this->db->where('employee_data_ins_di', $id);
        return $this->db->get('employee_data_ins')->row();
    }

    public function get_this_institute_employees()
    {
        $this->db->where('emp_ins_code_id', tenant_code());
        return $this->db->get('employee_data_ins')->result();
    }

    public function get_user_info_by_uniqID($uniq)
    {
        $this->db->where('user_uniq_qq_reails', $uniq); 
        $this->db->join('users', 'users.user_full_tbl_id = customer_full_info.udc_list_auto_p_iidd', 'left');

        $this->db->join('users_groups', 'users_groups.user_id = users.id', 'left'); 
        $this->db->join('groups', 'groups.id = users_groups.group_id', 'left'); 

        return $this->db->get('customer_full_info')->row(); 
    }

    public function get_user_collect_amount($cust_id)
    {
        $this->db->select_sum('paid_amount');
        $this->db->where('create_user_id', $cust_id);
        $query = $this->db->get('amount_paid');
        return $query->row()->paid_amount;
    }

    public function get_user_Allcollect_amount($cust_id)
    {
        $this->db->where('create_user_id', $cust_id);
        return $this->db->get('amount_paid')->result();
    }

    public function get_user_AllCutting_amount($cust_id)
    {
        $this->db->where('usr_idisidsid', $cust_id);
        $this->db->where('check_aprov_ing', 1);
        return $this->db->get('usr_bal_cutting')->result();
    }

    public function get_user_cutting_amount($cust_id)
    {
        $this->db->select_sum('usr_amnt_cuttt');
        $this->db->where('usr_idisidsid', $cust_id);   
        $this->db->where('check_aprov_ing', 1); 
        $query = $this->db->get('usr_bal_cutting');
        return $query->row()->usr_amnt_cuttt;
    }

    public function get_userInactiveCutting_amount($cust_id)
    {
        $this->db->select_sum('usr_amnt_cuttt');
        $this->db->where('usr_idisidsid', $cust_id);   
        $this->db->where('check_aprov_ing', 0); 
        $query = $this->db->get('usr_bal_cutting');
        return $query->row()->usr_amnt_cuttt;
    }   

    public function getAll_userInactiveCutting_amount($cust_id)
    {
        $this->db->where('usr_idisidsid', $cust_id);   
        $this->db->where('check_aprov_ing', 0); 
        return $this->db->get('usr_bal_cutting')->result();
    }    

    public function getAll_InactiveCutting_amounts()
    {
        $this->db->where('insprsssid', tenant_code());
        $this->db->select('usr_amnt_cuttt, timesingstime, dateingsdatesss, user_person_name, user_phone_no, percentager, usr_bal_cutting_idiiis');
        $this->db->where('check_aprov_ing', 0); 
        $this->db->join('customer_full_info', 'customer_full_info.udc_list_auto_p_iidd = usr_bal_cutting.usr_idisidsid', 'left');
        return $this->db->get('usr_bal_cutting')->result();
    }    

    public function updateDepositeApprovingMethod($id, $dataAry)
    {
        $this->db->where('usr_bal_cutting_idiiis', $id); 
        $this->db->update('usr_bal_cutting', $dataAry);
    }

    public function delete_this_institute_employee_by_id($id)
    {
        $this->db->where('employee_data_ins_di', $id);
        $this->db->delete('employee_data_ins');
    }

    public function get_this_ins_up_members()
    {
        $this->db->where('ins_atou_iddd_s', tenant_code());
        $this->db->order_by('ward_no_sq', 'asc');
        return $this->db->get('up_member_s')->result();
    }

    public function insert_this_institute_new_member_up($empData)
    {
        $this->db->insert('up_member_s', $empData);
        return $this->db->insert_id();
    }

    public function get_this_institute_members_by_id($id)
    {
        $this->db->where('up_member_s', $id);
        return $this->db->get('up_member_s')->row();
    }

    public function update_this_institute_editing_member_up($data, $id)
    {
        $this->db->where('up_member_s', $id);
        $this->db->update('up_member_s', $data);
    }

    public function delete_this_institute_upMember_by_id($id)
    {
        $this->db->where('up_member_s', $id);
        $this->db->delete('up_member_s');
    }

    function edit_user_info_by_uniq_id($arry, $uniq)
    {
        $this->db->where('user_uniq_qq_reails', $uniq);
        $this->db->update('customer_full_info', $arry);
    }

    public function insertCollectAmounts($collectData)
    {
        $this->db->insert('usr_bal_cutting', $collectData);
        return $this->db->insert_id();
    }

    public function user_collect_history_date_to_date($startDate, $endDate, $userID)
    {
        $this->db->where('date_s_today_s >=', $startDate);
        $this->db->where('date_s_today_s <=', $endDate);
        $this->db->where('user_iddii', $userID);
        $this->db->join('family_data_sheet', 'family_data_sheet.data_id = collect_amnt_invoice.family_data_id_id_dis', 'left');
        $this->db->join('customer_full_info', 'customer_full_info.udc_list_auto_p_iidd = collect_amnt_invoice.user_iddii', 'left');
        return $this->db->get('collect_amnt_invoice')->result();
    }


    public function user_paid_info_date_to_date($startDate, $endDate, $userID)
    {
        $this->db->where('dateingsdatesss >=', $startDate);
        $this->db->where('dateingsdatesss <=', $endDate);
        $this->db->where('check_aprov_ing', 1); 
        $this->db->where('usr_idisidsid', $userID);   
        return $this->db->get('usr_bal_cutting')->result();
    }

    public function all_collect_history_date_to_date($startDate, $endDate)
    {
        $this->db->where('date_s_today_s >=', $startDate);
        $this->db->where('date_s_today_s <=', $endDate);
        $this->db->join('family_data_sheet', 'family_data_sheet.data_id = collect_amnt_invoice.family_data_id_id_dis', 'left');
        $this->db->join('customer_full_info', 'customer_full_info.udc_list_auto_p_iidd = collect_amnt_invoice.user_iddii', 'left');
        return $this->db->get('collect_amnt_invoice')->result();
    }      

    public function users_all_paid_history_date_to_date($startDate, $endDate)
    {
        $this->db->where('dateingsdatesss >=', $startDate);
        $this->db->where('dateingsdatesss <=', $endDate);
        $this->db->join('customer_full_info', 'customer_full_info.udc_list_auto_p_iidd = usr_bal_cutting.usr_idisidsid', 'left');
        return $this->db->get('usr_bal_cutting')->result();
    }

    public function get_all_users_infos()
    {
        $this->db->where('ins_pr_iddds', tenant_code()); 
        return $this->db->get('customer_full_info')->result(); 
    }

    public function insert_this_institute_new_sliderss($insertData)
    {
        $this->db->insert('home_slider', $insertData);
        return $this->db->insert_id();
    }

    public function delete_this_institute_slider_by_id($id)
    {
        $this->db->where('home_slider_id_un_p', $id); 
        $this->db->delete('home_slider'); 
    }

    public function get_single_member_by_uniqs_id($id)
    {
        $this->db->where('up_member_s', $id);
        return $this->db->get('up_member_s')->row();
    }

    public function get_single_employee_by_uniqs_id($id)
    {
        $this->db->where('employee_data_ins_di', $id);
        return $this->db->get('employee_data_ins')->row();
    }

    public function get_this_institute_added_balances($ins_id)
    {
        $this->db->select_sum('amo_un_ttk');
        $this->db->where('ins_idprid', $ins_id);
        $query = $this->db->get('ins_bal_adds');
        return $query->row()->amo_un_ttk;
    }

    public function get_total_cutting_amnt($ins_id)
    {
        $this->db->select_sum('cuttingsssss');
        $this->db->where('insprdiidiiisssssss', $ins_id);
        $query = $this->db->get('ins_bal_cutting');
        return $query->row()->cuttingsssss;            
    }



    /*
        $array = array('MONTH(income_date)' => $month, 'YEAR(income_date)' => $year);
        $this->db->where($array);
        $query = $this->db->get('income');
    */
}