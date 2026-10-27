<?php 

defined('BASEPATH') OR exit('No direct script access allowed');

class UserModel extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }
	
	public function getAllUsersInfo()
	{
        $this->db->where('ins_pr_id_s', tenant_code());
        $this->db->where("users.username NOT LIKE 'support_%'");
        $this->db->where("users.email NOT LIKE '%@support.viretadev.local'");
        $this->db->join('customer_full_info', 'customer_full_info.udc_list_auto_p_iidd = users.user_full_tbl_id', 'left');
        $this->db->join('users_groups', 'users_groups.user_id = users.id', 'left'); 
        $this->db->join('groups', 'groups.id = users_groups.group_id', 'left'); 
        return $this->db->get('users')->result();
	}

	public function getUserInfo($id)
	{
		$this->db->where('udc_list_auto_p_iidd', $id);
		$this->db->join('ins_titute', 'ins_titute.ins_titute_p_idds = customer_full_info.ins_pr_iddds', 'left');
		// join Divition, Distric, Upazilla, Union 
		$this->db->join('div_list', 'customer_full_info.div_a_iddd = div_list.div_id', 'left');
		$this->db->join('dist_list', 'customer_full_info.dist_a_iddd = dist_list.dist_id', 'left');
		$this->db->join('up_list', 'customer_full_info.up_a_iddd = up_list.up_id', 'left');
		$this->db->join('un_list', 'customer_full_info.un_a_iddd = un_list.un_id', 'left');
		// join Divition, Distric, Upazilla, Union
		$this->db->join('users', 'customer_full_info.udc_list_auto_p_iidd = users.user_full_tbl_id', 'left');		
		$this->db->join('users_groups', 'users_groups.user_id = users.id', 'left');
		$this->db->join('groups', 'users_groups.group_id = groups.id', 'left');
		$sql = $this->db->get('customer_full_info');
		return $sql->row();
	}

	public function insert_login_info($d)
	{
		$this->db->insert('login_attempts', $d);		
	}


}