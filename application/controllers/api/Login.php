<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller {

	 public function __construct()
	 {
		parent::__construct();
		$this->load->library('template');

		$this->load->library('template');
        $this->load->library('Ion_auth');
        $this->load->library('session');
        $this->load->library('form_validation');
        $this->load->library('upload');
        $this->load->library('user_agent');
        // $this->load->library('pdf');
        $this->load->library('curl');
        $this->load->helper('url');

        $this->load->model('ion_auth_model');
        $this->load->model('UserModel', 'usermodel');
        $this->load->model('SettingModel');
		$this->load->database();
		$this->load->library('user_agent');
		$this->load->library(['ion_auth', 'form_validation']);
		$this->load->helper(['url', 'language']);

		$this->form_validation->set_error_delimiters($this->config->item('error_start_delimiter', 'ion_auth'), $this->config->item('error_end_delimiter', 'ion_auth'));

		$this->lang->load('auth');

		$this->data = [];

	 }

	public function index()
	{
        if ($this->ion_auth->logged_in()) {
            redirect('apps_api', 'refresh');
        }else {
			$this->template->front('api/login', $this->data);
		}
	}

    
	/**
	 * Log the user in
	 */
	public function login()
	{
		$this->data['title'] = $this->lang->line('login_heading');

		
        if ($this->ion_auth->logged_in()) {
            redirect('apps_api', 'refresh');
        }

		// validate form input
		$this->form_validation->set_rules('identity', str_replace(':', '', $this->lang->line('login_identity_label')), 'required');
		$this->form_validation->set_rules('password', str_replace(':', '', $this->lang->line('login_password_label')), 'required');

		if ($this->form_validation->run() === TRUE)
		{
			// check to see if the user is logging in
			// check for "remember me"
			$remember = (bool)$this->input->post('remember');

			if ($this->ion_auth->login($this->input->post('identity'), $this->input->post('password'), $remember))
			{

				// if the login is successful    

				$url_parts = parse_url(current_url());
				$login_attemps = array(
							'ip_address'		=> $this->input->ip_address(), 
							'time' 				=> time(), 
							'device' 			=> $this->agent->platform, 
							'use_r_s_id' 		=> $this->ion_auth->user()->row()->user_full_tbl_id, 
							'browser_infos' 	=> $this->agent->browser.' || '.$this->agent->version, 
							'loca_tion' 		=> '', 
							'doma_ins' 			=> str_replace('www.', '', $url_parts['host']), 
						);
				$this->usermodel->insert_login_info($login_attemps);

				//redirect them back to the Dashboard
				$this->session->set_flashdata('message', $this->ion_auth->messages());
				redirect('apps_api', 'refresh');
			}
			else
			{
				// if the login was un-successful
				// redirect them back to the login page
				$this->session->set_flashdata('message', $this->ion_auth->errors());
				redirect('login/login', 'refresh');
			}
		}
		else
		{
			// the user is not logging in so display the login page
			// set the flash data error message if there is one
			$this->data['message'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');

			$this->data['identity'] = [
				'name' => 'identity',
				'id' => 'identity',
				'type' => 'text',
				'value' => $this->form_validation->set_value('identity'),
			];

			$this->data['password'] = [
				'name' => 'password',
				'id' => 'password',
				'type' => 'password',
			];

			$this->_render_page('login/login', $this->data);
		}
	}


	/**
	 * Log the user out
	 */
	public function logout()
	{
		$this->data['title'] = "Logout";

		// log the user out
		$this->ion_auth->logout();

		// redirect them to the login page
		redirect('apps_login', 'refresh');
	}


}