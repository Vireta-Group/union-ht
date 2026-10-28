<?php
defined('BASEPATH') OR exit('No direct script access allowed');
 
class Sample extends CI_Controller {

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
		$this->load->helper('url');

		$this->load->model('ion_auth_model');
		$this->load->model('UserModel');
		$this->load->model('SettingModel');
		$this->load->model('AddMemberModel');
		$this->load->model('FamilyModel');

		if (!$this->ion_auth->logged_in()) {
			redirect('ap_logout', 'refresh');
		}

		$this->user_id = $this->ion_auth->user()->row()->user_full_tbl_id;

		if (empty($this->user_id)) {
			redirect('ap_logout', 'refresh');
		}

		$this_user_info = $this->UserModel->getUserInfo($this->user_id);
		$this->data['setting_info'] = $this->SettingModel->getThisInsName();

		$this->data['loggedUser'] = array(
									'userName' 				=> $this_user_info->user_person_name, 
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
									'active' 				=> $this_user_info->active, 
									'userGroupName' 		=> $this_user_info->name, 
									'userGroupDescription' 	=> $this_user_info->description
					);
        // if (!$this->ion_auth->in_group(array('sadmin'))) {
        //     $this->session->set_flashdata('error', lang('access_denied'));
        //     redirect("home/permission");
        // }

	} 

    public function apps_collection()
    {
        $this->template->front('api/collect_sam', $this->data);
    }

	
	public function print_slip()
	{
		$unq_id = $this->input->get('unqs');
        $this->data['paid_amnt_'] = $this->FamilyModel->get_paidAmntByVocherNo($unq_id);
        $this->data['invoices'] = $this->FamilyModel->get_invoiceByVocherNo($unq_id);
		$this->load->view('api/slip', $this->data);
	}



}