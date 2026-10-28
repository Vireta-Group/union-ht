<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller
{
	public $data = [];

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->library('user_agent');
		$this->load->library(['ion_auth', 'form_validation']);
		$this->load->helper(['url', 'language']);
		$this->load->model('UserModel', 'usermodel');

		$this->form_validation->set_error_delimiters($this->config->item('error_start_delimiter', 'ion_auth'), $this->config->item('error_end_delimiter', 'ion_auth'));

		$this->lang->load('auth');

	}

	public function index()
	{
		if (!$this->ion_auth->logged_in())
		{
			// redirect them to the login page
			redirect('login', 'refresh');
		}
		else
		{
			redirect('logout', 'refresh');
		}
	}

	/**
	 * Log the user in
	 */
	public function login()
	{
		$this->data['title'] = $this->lang->line('login_heading');

		
        if ($this->ion_auth->logged_in()) {
            redirect('dashboard', 'refresh');
        }

		// validate form input
		$this->form_validation->set_rules('identity', str_replace(':', '', $this->lang->line('login_identity_label')), 'required');
		$this->form_validation->set_rules('password', str_replace(':', '', $this->lang->line('login_password_label')), 'required');

		if ($this->form_validation->run() === TRUE)
		{
			// check to see if the user is logging in
			// check for "remember me"
			$remember = (bool)$this->input->post('remember');

			if (function_exists('cleanup_expired_temp_admins')) {
				cleanup_expired_temp_admins();
			}

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
				if ($this->ion_auth->in_group('sadmin')) {
					redirect('super_admin', 'refresh');
				} elseif ($this->ion_auth->in_group('collect')) {
					redirect('taxCollection', 'refresh');
				} else {
					$u_row = $this->ion_auth->user()->row();
					$tenant_id = (int)($u_row->ins_pr_id_s ?? 0);
					if ($tenant_id > 0) {
						$t_check = $this->db->where('ins_titute_p_idds', $tenant_id)->get('ins_titute')->row();
						if ($t_check && isset($t_check->is_active) && ! $t_check->is_active) {
							$sub_chk = $this->db->where('ins_titute_p_id', $tenant_id)->get('subscriptions')->row();
							$this->ion_auth->logout();
							if ($sub_chk && $sub_chk->status === 'pending') {
								$this->session->set_flashdata('message', 'আপনার পরিষদটি এখনও প্ল্যাটফর্ম সুপার অ্যাডমিনের অনুমোদনের অপেক্ষায় রয়েছে। অনুমোদন সম্পন্ন হলে আপনার ৪ দিনের ফ্রি ট্রায়াল সক্রিয় হবে এবং আপনি লগইন করতে পারবেন।');
							} else {
								$this->session->set_flashdata('message', 'আপনার ইউনিয়ন পরিষদের কার্যক্রম প্ল্যাটফর্ম কর্তৃপক্ষ কর্তৃক সাময়িকভাবে স্থগিত (Suspended) রাখা হয়েছে। সহায়তার জন্য সেন্ট্রাল সাপোর্টে যোগাযোগ করুন।');
							}
							redirect('login', 'refresh');
							return;
						}
					}

					// Send them to a host belonging to THEIR parishad, not
					// whichever parishad's site they typed the password into.
					// This used to be a bare redirect('dashboard'), i.e.
					// same-host, which is the same half of the bug that
					// Front::api_login() had. tenant_login_url() is shared with
					// api_login() so the two routes cannot drift apart again;
					// enforce_tenant_scope() is the backstop if it ever has to.
					redirect(tenant_login_url($u_row), 'refresh');
				}
			}
			else
			{
				// if the login was un-successful
				// redirect them back to the login page
				$this->session->set_flashdata('message', $this->ion_auth->errors());
				redirect('login', 'refresh');
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

			$this->_render_page('login', $this->data);
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
		redirect('/', 'refresh');
	}

	/**
	 * Change password
	 */
	public function change_password()
	{
		$this->form_validation->set_rules('old', $this->lang->line('change_password_validation_old_password_label'), 'required');
		$this->form_validation->set_rules('new', $this->lang->line('change_password_validation_new_password_label'), 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|matches[new_confirm]');
		$this->form_validation->set_rules('new_confirm', $this->lang->line('change_password_validation_new_password_confirm_label'), 'required');

		if (!$this->ion_auth->logged_in())
		{
			redirect('login', 'refresh');
		}

		$user = $this->ion_auth->user()->row();

		if ($this->form_validation->run() === FALSE)
		{
			// display the form
			// set the flash data error message if there is one
			$this->data['message'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');

			$this->data['min_password_length'] = $this->config->item('min_password_length', 'ion_auth');
			$this->data['old_password'] = [
				'name' => 'old',
				'id' => 'old',
				'type' => 'password',
			];
			$this->data['new_password'] = [
				'name' => 'new',
				'id' => 'new',
				'type' => 'password',
				'pattern' => '^.{' . $this->data['min_password_length'] . '}.*$',
			];
			$this->data['new_password_confirm'] = [
				'name' => 'new_confirm',
				'id' => 'new_confirm',
				'type' => 'password',
				'pattern' => '^.{' . $this->data['min_password_length'] . '}.*$',
			];
			$this->data['user_id'] = [
				'name' => 'user_id',
				'id' => 'user_id',
				'type' => 'hidden',
				'value' => $user->id,
			];

			// render
			$this->_render_page('auth' . DIRECTORY_SEPARATOR . 'change_password', $this->data);
		}
		else
		{
			$identity = $this->session->userdata('identity');

			$change = $this->ion_auth->change_password($identity, $this->input->post('old'), $this->input->post('new'));

			if ($change)
			{
				//if the password was successfully changed
				$this->session->set_flashdata('message', $this->ion_auth->messages());
				$this->logout();
			}
			else
			{
				$this->session->set_flashdata('message', $this->ion_auth->errors());
				redirect('auth/change_password', 'refresh');
			}
		}
	}

	/**
	 * Forgot password
	 */
	public function forgot_password()
	{
		$setting = function_exists('tenant_row') ? tenant_row() : null;
		if (empty($setting)) {
			$setting = (object)[
				'ins_name'      => 'Vireta Dev',
				'ins_title_ss'  => 'ডিজিটাল নাগরিক সেবা ও অটোমেশন পোর্টাল',
				'ins_logo_path' => '',
				'addre_s_s'     => 'ঢাকা, বাংলাদেশ',
				'ins_emails'    => env('MAIL_FROM_ADDRESS', 'contact.viretadev@gmail.com'),
				'socib_phone'   => '+880 1800-000000'
			];
		}
		$this->data['setting'] = $setting;

		if ($this->input->method() === 'post') {
			$identity = trim($this->input->post('identity') ?? '');

			if (empty($identity)) {
				$this->session->set_flashdata('error', 'অনুগ্রহ করে আপনার রেজিস্টার্ড ইমেইল বা ইউজারনেম প্রদান করুন।');
				redirect('forgot_password');
				return;
			}

			// Lookup user by email, username, or u_type
			$user = $this->db->group_start()
			                 ->where('email', $identity)
			                 ->or_where('username', $identity)
			                 ->or_where('u_type', $identity)
			                 ->group_end()
			                 ->where('active', 1)
			                 ->get('users')
			                 ->row();

			if (!$user || empty($user->email)) {
				$this->session->set_flashdata('error', 'প্রদত্ত তথ্য অনুযায়ী কোনো সক্রিয় অ্যাকাউন্ট পাওয়া যায়নি।');
				redirect('forgot_password');
				return;
			}

			// Generate secure selector & validator couple (OWASP recommended pattern)
			$selector = bin2hex(random_bytes(16));
			$validator = bin2hex(random_bytes(32));
			$cost = max(12, (int) env('APP_BCRYPT_COST', 12));
			$validator_hashed = password_hash($validator, PASSWORD_BCRYPT, ['cost' => $cost]);
			$code = $selector . '.' . $validator;

			$this->db->where('id', $user->id)->update('users', [
				'forgotten_password_selector' => $selector,
				'forgotten_password_code'     => $validator_hashed,
				'forgotten_password_time'     => time(),
			]);

			$reset_url = site_url('reset_password/' . $code);
			$recipient_name = (!empty($user->first_name) ? ($user->first_name . ' ' . $user->last_name) : ($user->username ?? ''));
			$html_email = render_password_reset_email($recipient_name, $reset_url, 30);

			$subject = 'ViretaDev — পাসওয়ার্ড রিসেটের আবেদন';
			$mail_res = send_vireta_email($user->email, $subject, $html_email, $recipient_name);

			if ($mail_res['success']) {
				$this->session->set_flashdata('message', 'আপনার ইমেইলে (' . htmlspecialchars($user->email, ENT_QUOTES, 'UTF-8') . ') পাসওয়ার্ড রিসেট লিংক পাঠানো হয়েছে। ইনবক্স অথবা স্প্যাম ফোল্ডার চেক করুন।');
			} else {
				$this->session->set_flashdata('error', 'ইমেইল পাঠাতে সমস্যা হয়েছে: ' . $mail_res['message']);
			}
			redirect('forgot_password');
			return;
		}

		$this->data['title']   = 'পাসওয়ার্ড পুনরুদ্ধার';
		$this->data['error']   = $this->session->flashdata('error');
		$this->data['message'] = $this->session->flashdata('message');

		$this->load->view('auth/forgot_password', $this->data);
	}

	/**
	 * Reset password - final step for forgotten password
	 *
	 * @param string|null $code The reset code
	 */
	public function reset_password($code = NULL)
	{
		if (!$code) {
			redirect('forgot_password');
			return;
		}

		$setting = function_exists('tenant_row') ? tenant_row() : null;
		if (empty($setting)) {
			$setting = (object)[
				'ins_name'      => 'Vireta Dev',
				'ins_title_ss'  => 'ডিজিটাল নাগরিক সেবা ও অটোমেশন পোর্টাল',
				'ins_logo_path' => '',
				'addre_s_s'     => 'ঢাকা, বাংলাদেশ',
				'ins_emails'    => env('MAIL_FROM_ADDRESS', 'contact.viretadev@gmail.com'),
				'socib_phone'   => '+880 1800-000000'
			];
		}
		$this->data['setting'] = $setting;
		$this->data['code']    = $code;

		// Verify token validity
		$user = $this->ion_auth->forgotten_password_check($code);

		if (!$user) {
			$this->session->set_flashdata('error', 'পাসওয়ার্ড রিসেট লিংকটি অবৈধ অথবা এর মেয়াদ (৩০ মিনিট) শেষ হয়ে গেছে। অনুগ্রহ করে আবার আবেদন করুন।');
			redirect('forgot_password');
			return;
		}

		if ($this->input->method() === 'post') {
			$new_pass     = $this->input->post('new_password');
			$confirm_pass = $this->input->post('confirm_password');

			if (empty($new_pass) || strlen($new_pass) < 8) {
				$this->data['error'] = 'পাসওয়ার্ড অবশ্যই কমপক্ষে ৮ অক্ষরের হতে হবে।';
				$this->load->view('auth/reset_password', $this->data);
				return;
			}

			if ($new_pass !== $confirm_pass) {
				$this->data['error'] = 'পাসওয়ার্ড এবং নিশ্চিতকরণ পাসওয়ার্ড মেলেনি।';
				$this->load->view('auth/reset_password', $this->data);
				return;
			}

			// Update password with bcrypt cost
			$cost = max(12, (int) env('APP_BCRYPT_COST', 12));
			$password_hash = password_hash($new_pass, PASSWORD_BCRYPT, ['cost' => $cost]);

			$this->db->where('id', $user->id)->update('users', [
				'password'                    => $password_hash,
				'forgotten_password_selector' => NULL,
				'forgotten_password_code'     => NULL,
				'forgotten_password_time'     => NULL,
				'remember_code'               => NULL,
			]);

			$this->session->set_flashdata('message', 'আপনার পাসওয়ার্ড সফলভাবে পরিবর্তন করা হয়েছে। এখন নতুন পাসওয়ার্ড দিয়ে লগইন করুন।');
			redirect('login');
			return;
		}

		$this->data['title']   = 'নতুন পাসওয়ার্ড সেট করুন';
		$this->data['error']   = $this->session->flashdata('error');
		$this->data['message'] = $this->session->flashdata('message');

		$this->load->view('auth/reset_password', $this->data);
	}


	/**
	 * Activate the user
	 *
	 * @param int         $id   The user ID
	 * @param string|bool $code The activation code
	 */
	public function activate($id, $code = FALSE)
	{
		$activation = FALSE;

		if ($code !== FALSE)
		{
			$activation = $this->ion_auth->activate($id, $code);
		}
		else if ($this->ion_auth->is_admin())
		{
			$activation = $this->ion_auth->activate($id);
		}

		if ($activation)
		{
			// redirect them to the auth page
			$this->session->set_flashdata('message', $this->ion_auth->messages());
			redirect("auth", 'refresh');
		}
		else
		{
			// redirect them to the forgot password page
			$this->session->set_flashdata('message', $this->ion_auth->errors());
			redirect("auth/forgot_password", 'refresh');
		}
	}

	/**
	 * Deactivate the user
	 *
	 * @param int|string|null $id The user ID
	 */
	public function deactivate($id = NULL)
	{
		if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_admin())
		{
			// redirect them to the home page because they must be an administrator to view this
			show_error('You must be an administrator to view this page.');
		}

		$id = (int)$id;

		$this->load->library('form_validation');
		$this->form_validation->set_rules('confirm', $this->lang->line('deactivate_validation_confirm_label'), 'required');
		$this->form_validation->set_rules('id', $this->lang->line('deactivate_validation_user_id_label'), 'required|alpha_numeric');

		if ($this->form_validation->run() === FALSE)
		{
			// insert csrf check
			$this->data['csrf'] = $this->_get_csrf_nonce();
			$this->data['user'] = $this->ion_auth->user($id)->row();
			$this->data['identity'] = $this->config->item('identity', 'ion_auth');

			$this->_render_page('auth' . DIRECTORY_SEPARATOR . 'deactivate_user', $this->data);
		}
		else
		{
			// do we really want to deactivate?
			if ($this->input->post('confirm') == 'yes')
			{
				// do we have a valid request?
				if ($this->_valid_csrf_nonce() === FALSE || $id != $this->input->post('id'))
				{
					show_error($this->lang->line('error_csrf'));
				}

				// do we have the right userlevel?
				if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin())
				{
					$this->ion_auth->deactivate($id);
				}
			}

			// redirect them back to the auth page
			redirect('auth', 'refresh');
		}
	}

	/**
	 * Create a new user
	 */
	public function create_user()
	{
		$this->data['title'] = $this->lang->line('create_user_heading');

		if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_admin())
		{
			redirect('auth', 'refresh');
		}

		$tables = $this->config->item('tables', 'ion_auth');
		$identity_column = $this->config->item('identity', 'ion_auth');
		$this->data['identity_column'] = $identity_column;

		// validate form input
		$this->form_validation->set_rules('first_name', $this->lang->line('create_user_validation_fname_label'), 'trim|required');
		$this->form_validation->set_rules('last_name', $this->lang->line('create_user_validation_lname_label'), 'trim|required');
		if ($identity_column !== 'email')
		{
			$this->form_validation->set_rules('identity', $this->lang->line('create_user_validation_identity_label'), 'trim|required|is_unique[' . $tables['users'] . '.' . $identity_column . ']');
			$this->form_validation->set_rules('email', $this->lang->line('create_user_validation_email_label'), 'trim|required|valid_email');
		}
		else
		{
			$this->form_validation->set_rules('email', $this->lang->line('create_user_validation_email_label'), 'trim|required|valid_email|is_unique[' . $tables['users'] . '.email]');
		}
		$this->form_validation->set_rules('phone', $this->lang->line('create_user_validation_phone_label'), 'trim');
		$this->form_validation->set_rules('company', $this->lang->line('create_user_validation_company_label'), 'trim');
		$this->form_validation->set_rules('password', $this->lang->line('create_user_validation_password_label'), 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|matches[password_confirm]');
		$this->form_validation->set_rules('password_confirm', $this->lang->line('create_user_validation_password_confirm_label'), 'required');

		if ($this->form_validation->run() === TRUE)
		{
			$email = strtolower($this->input->post('email'));
			$identity = ($identity_column === 'email') ? $email : $this->input->post('identity');
			$password = $this->input->post('password');

			$additional_data = [
				'first_name' => $this->input->post('first_name'),
				'last_name' => $this->input->post('last_name'),
				'company' => $this->input->post('company'),
				'phone' => $this->input->post('phone'),
			];
		}
		if ($this->form_validation->run() === TRUE && $this->ion_auth->register($identity, $password, $email, $additional_data))
		{
			// check to see if we are creating the user
			// redirect them back to the admin page
			$this->session->set_flashdata('message', $this->ion_auth->messages());
			redirect("auth", 'refresh');
		}
		else
		{
			// display the create user form
			// set the flash data error message if there is one
			$this->data['message'] = (validation_errors() ? validation_errors() : ($this->ion_auth->errors() ? $this->ion_auth->errors() : $this->session->flashdata('message')));

			$this->data['first_name'] = [
				'name' => 'first_name',
				'id' => 'first_name',
				'type' => 'text',
				'value' => $this->form_validation->set_value('first_name'),
			];
			$this->data['last_name'] = [
				'name' => 'last_name',
				'id' => 'last_name',
				'type' => 'text',
				'value' => $this->form_validation->set_value('last_name'),
			];
			$this->data['identity'] = [
				'name' => 'identity',
				'id' => 'identity',
				'type' => 'text',
				'value' => $this->form_validation->set_value('identity'),
			];
			$this->data['email'] = [
				'name' => 'email',
				'id' => 'email',
				'type' => 'text',
				'value' => $this->form_validation->set_value('email'),
			];
			$this->data['company'] = [
				'name' => 'company',
				'id' => 'company',
				'type' => 'text',
				'value' => $this->form_validation->set_value('company'),
			];
			$this->data['phone'] = [
				'name' => 'phone',
				'id' => 'phone',
				'type' => 'text',
				'value' => $this->form_validation->set_value('phone'),
			];
			$this->data['password'] = [
				'name' => 'password',
				'id' => 'password',
				'type' => 'password',
				'value' => $this->form_validation->set_value('password'),
			];
			$this->data['password_confirm'] = [
				'name' => 'password_confirm',
				'id' => 'password_confirm',
				'type' => 'password',
				'value' => $this->form_validation->set_value('password_confirm'),
			];

			$this->_render_page('auth' . DIRECTORY_SEPARATOR . 'create_user', $this->data);
		}
	}
	/**
	* Redirect a user checking if is admin
	*/
	public function redirectUser(){
		if ($this->ion_auth->is_admin()){
			redirect('auth', 'refresh');
		}
		redirect('/', 'refresh');
	}

	/**
	 * Edit a user
	 *
	 * @param int|string $id
	 */
	public function edit_user($id)
	{
		$this->data['title'] = $this->lang->line('edit_user_heading');

		if (!$this->ion_auth->logged_in() || (!$this->ion_auth->is_admin() && !($this->ion_auth->user()->row()->id == $id)))
		{
			redirect('auth', 'refresh');
		}

		$user = $this->ion_auth->user($id)->row();
		$groups = $this->ion_auth->groups()->result_array();
		$currentGroups = $this->ion_auth->get_users_groups($id)->result_array();
			
		//USAGE NOTE - you can do more complicated queries like this
		//$groups = $this->ion_auth->where(['field' => 'value'])->groups()->result_array();
	

		// validate form input
		$this->form_validation->set_rules('first_name', $this->lang->line('edit_user_validation_fname_label'), 'trim|required');
		$this->form_validation->set_rules('last_name', $this->lang->line('edit_user_validation_lname_label'), 'trim|required');
		$this->form_validation->set_rules('phone', $this->lang->line('edit_user_validation_phone_label'), 'trim');
		$this->form_validation->set_rules('company', $this->lang->line('edit_user_validation_company_label'), 'trim');

		if (isset($_POST) && !empty($_POST))
		{
			// do we have a valid request?
			if ($this->_valid_csrf_nonce() === FALSE || $id != $this->input->post('id'))
			{
				show_error($this->lang->line('error_csrf'));
			}

			// update the password if it was posted
			if ($this->input->post('password'))
			{
				$this->form_validation->set_rules('password', $this->lang->line('edit_user_validation_password_label'), 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|matches[password_confirm]');
				$this->form_validation->set_rules('password_confirm', $this->lang->line('edit_user_validation_password_confirm_label'), 'required');
			}

			if ($this->form_validation->run() === TRUE)
			{
				$data = [
					'first_name' => $this->input->post('first_name'),
					'last_name' => $this->input->post('last_name'),
					'company' => $this->input->post('company'),
					'phone' => $this->input->post('phone'),
				];

				// update the password if it was posted
				if ($this->input->post('password'))
				{
					$data['password'] = $this->input->post('password');
				}

				// Only allow updating groups if user is admin
				if ($this->ion_auth->is_admin())
				{
					// Update the groups user belongs to
					$this->ion_auth->remove_from_group('', $id);
					
					$groupData = $this->input->post('groups');
					if (isset($groupData) && !empty($groupData))
					{
						foreach ($groupData as $grp)
						{
							$this->ion_auth->add_to_group($grp, $id);
						}

					}
				}

				// check to see if we are updating the user
				if ($this->ion_auth->update($user->id, $data))
				{
					// redirect them back to the admin page if admin, or to the base url if non admin
					$this->session->set_flashdata('message', $this->ion_auth->messages());
					$this->redirectUser();

				}
				else
				{
					// redirect them back to the admin page if admin, or to the base url if non admin
					$this->session->set_flashdata('message', $this->ion_auth->errors());
					$this->redirectUser();

				}

			}
		}

		// display the edit user form
		$this->data['csrf'] = $this->_get_csrf_nonce();

		// set the flash data error message if there is one
		$this->data['message'] = (validation_errors() ? validation_errors() : ($this->ion_auth->errors() ? $this->ion_auth->errors() : $this->session->flashdata('message')));

		// pass the user to the view
		$this->data['user'] = $user;
		$this->data['groups'] = $groups;
		$this->data['currentGroups'] = $currentGroups;

		$this->data['first_name'] = [
			'name'  => 'first_name',
			'id'    => 'first_name',
			'type'  => 'text',
			'value' => $this->form_validation->set_value('first_name', $user->first_name),
		];
		$this->data['last_name'] = [
			'name'  => 'last_name',
			'id'    => 'last_name',
			'type'  => 'text',
			'value' => $this->form_validation->set_value('last_name', $user->last_name),
		];
		$this->data['company'] = [
			'name'  => 'company',
			'id'    => 'company',
			'type'  => 'text',
			'value' => $this->form_validation->set_value('company', $user->company),
		];
		$this->data['phone'] = [
			'name'  => 'phone',
			'id'    => 'phone',
			'type'  => 'text',
			'value' => $this->form_validation->set_value('phone', $user->phone),
		];
		$this->data['password'] = [
			'name' => 'password',
			'id'   => 'password',
			'type' => 'password'
		];
		$this->data['password_confirm'] = [
			'name' => 'password_confirm',
			'id'   => 'password_confirm',
			'type' => 'password'
		];

		$this->_render_page('auth/edit_user', $this->data);
	}

	/**
	 * Create a new group
	 */
	public function create_group()
	{
		$this->data['title'] = $this->lang->line('create_group_title');

		if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_admin())
		{
			redirect('auth', 'refresh');
		}

		// validate form input
		$this->form_validation->set_rules('group_name', $this->lang->line('create_group_validation_name_label'), 'trim|required|alpha_dash');

		if ($this->form_validation->run() === TRUE)
		{
			$new_group_id = $this->ion_auth->create_group($this->input->post('group_name'), $this->input->post('description'));
			if ($new_group_id)
			{
				// check to see if we are creating the group
				// redirect them back to the admin page
				$this->session->set_flashdata('message', $this->ion_auth->messages());
				redirect("auth", 'refresh');
			}
			else
            		{
				$this->session->set_flashdata('message', $this->ion_auth->errors());
            		}			
		}
			
		// display the create group form
		// set the flash data error message if there is one
		$this->data['message'] = (validation_errors() ? validation_errors() : ($this->ion_auth->errors() ? $this->ion_auth->errors() : $this->session->flashdata('message')));

		$this->data['group_name'] = [
			'name'  => 'group_name',
			'id'    => 'group_name',
			'type'  => 'text',
			'value' => $this->form_validation->set_value('group_name'),
		];
		$this->data['description'] = [
			'name'  => 'description',
			'id'    => 'description',
			'type'  => 'text',
			'value' => $this->form_validation->set_value('description'),
		];

		$this->_render_page('auth/create_group', $this->data);
		
	}

	/**
	 * Edit a group
	 *
	 * @param int|string $id
	 */
	public function edit_group($id)
	{
		// bail if no group id given
		if (!$id || empty($id))
		{
			redirect('auth', 'refresh');
		}

		$this->data['title'] = $this->lang->line('edit_group_title');

		if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_admin())
		{
			redirect('auth', 'refresh');
		}

		$group = $this->ion_auth->group($id)->row();

		// validate form input
		$this->form_validation->set_rules('group_name', $this->lang->line('edit_group_validation_name_label'), 'trim|required|alpha_dash');

		if (isset($_POST) && !empty($_POST))
		{
			if ($this->form_validation->run() === TRUE)
			{
				$group_update = $this->ion_auth->update_group($id, $_POST['group_name'], array(
					'description' => $_POST['group_description']
				));

				if ($group_update)
				{
					$this->session->set_flashdata('message', $this->lang->line('edit_group_saved'));
					redirect("auth", 'refresh');
				}
				else
				{
					$this->session->set_flashdata('message', $this->ion_auth->errors());
				}				
			}
		}

		// set the flash data error message if there is one
		$this->data['message'] = (validation_errors() ? validation_errors() : ($this->ion_auth->errors() ? $this->ion_auth->errors() : $this->session->flashdata('message')));

		// pass the user to the view
		$this->data['group'] = $group;

		$this->data['group_name'] = [
			'name'    => 'group_name',
			'id'      => 'group_name',
			'type'    => 'text',
			'value'   => $this->form_validation->set_value('group_name', $group->name),
		];
		if ($this->config->item('admin_group', 'ion_auth') === $group->name) {
			$this->data['group_name']['readonly'] = 'readonly';
		}
		
		$this->data['group_description'] = [
			'name'  => 'group_description',
			'id'    => 'group_description',
			'type'  => 'text',
			'value' => $this->form_validation->set_value('group_description', $group->description),
		];

		$this->_render_page('auth' . DIRECTORY_SEPARATOR . 'edit_group', $this->data);
	}

	/**
	 * @return array A CSRF key-value pair
	 */
	public function _get_csrf_nonce()
	{
		$this->load->helper('string');
		$key = random_string('alnum', 8);
		$value = random_string('alnum', 20);
		$this->session->set_flashdata('csrfkey', $key);
		$this->session->set_flashdata('csrfvalue', $value);

		return [$key => $value];
	}

	/**
	 * @return bool Whether the posted CSRF token matches
	 */
	public function _valid_csrf_nonce(){
		$csrfkey = $this->input->post($this->session->flashdata('csrfkey'));
		if ($csrfkey && $csrfkey === $this->session->flashdata('csrfvalue'))
		{
			return TRUE;
		}
			return FALSE;
	}

	/**
	 * @param string     $view
	 * @param array|null $data
	 * @param bool       $returnhtml
	 *
	 * @return mixed
	 */
	public function _render_page($view, $data = NULL, $returnhtml = FALSE)//I think this makes more sense
	{

		$viewdata = (empty($data)) ? $this->data : $data;

		$view_html = $this->load->view($view, $viewdata, $returnhtml);

		// This will return html on 3rd argument being true
		if ($returnhtml)
		{
			return $view_html;
		}
	}

}
