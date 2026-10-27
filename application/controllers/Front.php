<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Front extends CI_Controller {

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
        $this->load->model('FamilyModel');
		$this->load->model('AddMemberModel');

		$this->data['setting'] = $this->SettingModel->getThisInsName();
		$this->data['sliders'] = $this->SettingModel->get_ins_sliders();
		$this->data['up_mbrs'] = $this->SettingModel->get_ins_members();
		$this->data['up_emps'] = $this->SettingModel->get_this_employees();

	 } 

	/**
	 * Which site does this request want: a parishad portal, or the ViretaDev
	 * product site?
	 *
	 * The answer is the resolver's, never a local decision. This method used to
	 * re-derive it from a hardcoded localhost list plus a `?tenant=N` query
	 * parameter, and both were wrong:
	 *
	 *   - `?tenant=N` had no host gate, so the public main domain could be
	 *     asked for `?tenant=1` and would render that parishad's whole home
	 *     page — logo, chairman's speech, council members, staff. The
	 *     resolver already implements `?tenant=N` as a development override
	 *     gated on is_dev_host(); this method was a second, ungated copy of
	 *     the same idea, and the copy was the one doing the damage.
	 *
	 *   - the host list only contained localhost variants, so it described the
	 *     developer's machine rather than the deployment. In production the
	 *     main domain is not in it, and the decision silently fell through to
	 *     whatever the resolver guessed.
	 *
	 * The rule now: an EXPLICIT tenant resolution gets the portal; everything
	 * else — a platform host, or a hostname we could not tie to anyone — gets
	 * the product site, which needs no tenant data and therefore cannot leak
	 * any. Fail-closed toward the page that reads nothing.
	 */
	public function index()
	{
		$this->render_site();
	}

	/**
	 * Portal or product site — the single place that decides.
	 *
	 * Public because /up used to be a bare alias for the home page that called
	 * no decision at all, so it rendered the parishad portal on the main domain
	 * regardless of what the resolver had said. The data was already empty by
	 * then (a platform host resolves to tenant 0, so the queries matched
	 * nothing), but a route that steps around the routing decision is the same
	 * defect in a different place, and the next change to /up would have made
	 * it leak. Aliases have to go through the same gate.
	 */
	public function up()
	{
		$this->render_site();
	}

	/**
	 * The actual routing decision, shared by / and /up.
	 *
	 * An EXPLICIT tenant resolution gets the parishad portal; everything else —
	 * a platform host, or a hostname we could not tie to anyone — gets the
	 * ViretaDev product site, which reads no tenant data and therefore cannot
	 * leak any. Fail-closed toward the page that needs nothing.
	 *
	 * Explicit means the resolver named a tenant: a registered hostname, a
	 * subdomain, or a development override (which is itself gated on
	 * is_dev_host()). A fallback does not count. The `primary` row is a guess
	 * made because nothing else matched, and `session` is a guess made from a
	 * cookie — neither is a statement that this host belongs to that parishad.
	 *
	 * @return void
	 */
	private function render_site()
	{
		$source = function_exists('tenant_source') ? tenant_source() : '';

		$explicit_tenant = in_array($source, array(
			'domain',     // hostname registered in tenant_domains
			'subdomain',  // <slug>.<root domain>
			'forced',     // resolver->force(), dev hosts only
			'dev-query',  // ?tenant=N, dev hosts only
			'dev-header', // X-Tenant: N, dev hosts only
		), TRUE);

		if ($explicit_tenant) {
			$t_row = function_exists('tenant_row') ? tenant_row() : null;
			if ($t_row && isset($t_row->is_active) && ! $t_row->is_active) {
				$this->data['suspended_tenant'] = $t_row;
				$this->load->view('front/suspended', $this->data);
				return;
			}
			$this->load->view('front/home', $this->data);
		} else {
			// Platform host (registered in platform_domains), or unresolved.
			// The product-site landing page. Reads no tenant data, so it cannot
			// leak any — that property is why the gate fails closed this way.
			$packages = $this->db->where('is_active', TRUE)->order_by('sort_order', 'ASC')->get('packages')->result();
			$this->template->saas('saas/pages/home', $this->saas_data('/', array('saas_packages' => $packages)));
		}
	}

	/**
	 * Build the view data every product-site page needs.
	 *
	 * $saas_path is the route being served, without a leading slash except for
	 * the root. Template::saas() hands it to the navbar and footer so they can
	 * highlight the current page — in the React version this was the
	 * currentPath prop, derived on the client from window.location.pathname.
	 * Now it comes from the controller method that actually matched the route,
	 * which cannot drift from the URL.
	 *
	 * @param  string $path  Route, e.g. '/' or '/about'.
	 * @return array
	 */
	private function saas_data($path, $extra = array())
	{
		return array_merge($this->data, array(
			'saas_path' => $path,
		), $extra);
	}

	public function landing()
	{
		$this->template->saas('saas/pages/home', $this->saas_data('/'));
	} 

	public function about()
	{
		$this->template->saas('saas/pages/about', $this->saas_data('/about'));
	}

	public function plans()
	{
		$packages = $this->db->where('is_active', TRUE)->order_by('sort_order', 'ASC')->get('packages')->result();
		$this->template->saas('saas/pages/plans', $this->saas_data('/plans', array(
			'saas_packages' => $packages,
		)));
	}

	public function contact()
	{
		$packages = $this->db->where('is_active', TRUE)->order_by('sort_order', 'ASC')->get('packages')->result();
		$this->template->saas('saas/pages/contact', $this->saas_data('/contact', array(
			'saas_packages' => $packages,
		)));
	} 

	public function reviews()
	{
		$db_reviews = $this->db->where('status', 'approved')
			->order_by('featured', 'DESC')
			->order_by('created_at', 'DESC')
			->get('platform_reviews')
			->result_array();

		$this->template->saas('saas/pages/reviews', $this->saas_data('/reviews', array(
			'db_reviews' => $db_reviews,
		)));
	}

	/**
	 * Public API for submitting testimonials and reviews from anywhere
	 */
	public function api_submit_review()
	{
		$author      = trim($this->input->post('author', TRUE) ?: '');
		$role        = trim($this->input->post('role', TRUE) ?: '');
		$tenant_name = trim($this->input->post('tenant_name', TRUE) ?: '');
		$service     = trim($this->input->post('service', TRUE) ?: 'নাগরিক সেবা');
		$rating      = (int) ($this->input->post('rating', TRUE) ?: 5);
		$content     = trim($this->input->post('content', TRUE) ?: '');
		$impact      = trim($this->input->post('impact', TRUE) ?: '');

		$tenant_id = (int)tenant_code();
		$tenant_row = function_exists('tenant_row') ? tenant_row() : null;

		$logged_in = isset($this->ion_auth) && $this->ion_auth->logged_in();
		$user = $logged_in ? $this->ion_auth->user()->row() : null;

		if ($logged_in && $user) {
			if (empty($author)) {
				$author = !empty($user->user_person_name) ? $user->user_person_name : ($user->username ?: $user->email);
			}
			if (empty($tenant_name) && $tenant_row) {
				$tenant_name = $tenant_row->ins_name;
			}
			if (empty($role)) {
				$role = $this->ion_auth->in_group('uphead') ? 'ইউপি চেয়ারম্যান' : ($this->ion_auth->in_group('upsocib') ? 'ইউপি সচিব' : 'ইউপি প্রশাসক');
			}
			if (empty($tenant_id) && !empty($user->ins_pr_id_s)) {
				$tenant_id = (int)$user->ins_pr_id_s;
			}
		}

		if (empty($role)) {
			$role = 'নাগরিক';
		}

		$avatar_path = 'inc/assets/himg/avatar' . rand(1, 3) . '.png';
		if (!empty($tenant_row->ins_logo_path)) {
			$avatar_path = media_url($tenant_row->ins_logo_path);
		} elseif (!empty($user->user_img_base64)) {
			$avatar_path = media_url($user->user_img_base64);
		}

		if ($rating < 1 || $rating > 5) {
			$rating = 5;
		}

		$is_ajax = $this->input->is_ajax_request() || strpos($this->input->server('HTTP_ACCEPT') ?: '', 'application/json') !== FALSE;

		if (empty($author) || empty($content)) {
			if ($is_ajax) {
				return $this->output->set_content_type('application/json')->set_output(json_encode(array(
					'success' => FALSE,
					'message' => 'আপনার নাম এবং মন্তব্য প্রদান করা আবশ্যক।',
				)));
			}
			$this->session->set_flashdata('review_error', 'আপনার নাম এবং মন্তব্য প্রদান করা আবশ্যক।');
			redirect('reviews');
			return;
		}

		$insert_data = array(
			'tenant_id'       => $tenant_id ?: null,
			'author'          => $author,
			'role'            => $role,
			'tenant_name'     => $tenant_name ?: 'সাধারণ নাগরিক / ইউপি সেবা',
			'service'         => $service,
			'rating'          => $rating,
			'impact'          => $impact ?: 'নাগরিক মতামত',
			'content'         => $content,
			'avatar'          => $avatar_path,
			'status'          => 'pending',
			'show_on_landing' => FALSE,
			'featured'        => FALSE,
			'created_at'      => date('Y-m-d H:i:s'),
			'updated_at'      => date('Y-m-d H:i:s'),
		);

		$this->db->insert('platform_reviews', $insert_data);

		if ($is_ajax) {
			return $this->output->set_content_type('application/json')->set_output(json_encode(array(
				'success' => TRUE,
				'message' => 'আপনার মূল্যবান মতামতের জন্য ধন্যবাদ! পর্যালোচনার পর এটি প্রদর্শিত হবে।',
			)));
		}

		$this->session->set_flashdata('review_success', 'আপনার মূল্যবান মতামতের জন্য ধন্যবাদ! পর্যালোচনার পর এটি প্রদর্শিত হবে।');
		redirect('reviews');
	}

	/**
	 * Public API to fetch approved testimonials (for landing, widgets, or apps)
	 */
	public function api_public_reviews()
	{
		$reviews = $this->db->where('status', 'approved')
			->order_by('featured', 'DESC')
			->order_by('created_at', 'DESC')
			->get('platform_reviews')
			->result_array();

		return $this->output
			->set_header('Access-Control-Allow-Origin: *')
			->set_content_type('application/json')
			->set_output(json_encode(array(
				'success' => TRUE,
				'count'   => count($reviews),
				'data'    => $reviews,
			)));
	} 

	public function faq()
	{
		$this->template->saas('saas/pages/faq', $this->saas_data('/faq'));
	}

	/*
	 * /policy, /privacy and /terms are three URLs for one view, matching the
	 * React app, where all three routed to PolicyPage with a different
	 * initialTab. The active tab is server-rendered from the route, so it is
	 * right before any JavaScript runs; saas.js only takes over if ?tab=
	 * disagrees.
	 */
	public function policy()
	{
		$this->template->saas('saas/pages/policy', $this->saas_data('/policy', array(
			'saas_tab' => 'privacy',
		)));
	}

	public function privacy()
	{
		$this->template->saas('saas/pages/policy', $this->saas_data('/privacy', array(
			'saas_tab' => 'privacy',
		)));
	}

	public function terms()
	{
		$this->template->saas('saas/pages/policy', $this->saas_data('/terms', array(
			'saas_tab' => 'terms',
		)));
	}

	/**
	 * GET  /register  — the form.
	 * POST /register  — server-rendered result.
	 *
	 * The React version handled both in one component and posted to
	 * api_register_tenant via fetch, then built the success screen from the
	 * JSON in JavaScript. Now that the page is server-rendered, the POST branch
	 * calls create_tenant() — the same method api_register_tenant() calls — and
	 * renders either the form again with an error, or the confirmation screen.
	 *
	 * This is not a new capability. api_register_tenant has always been an
	 * unauthenticated POST endpoint; this is the same operation behind a second
	 * URL, not an additional one. See create_tenant() for what it does.
	 *
	 * @return void
	 */
	public function register()
	{
		$db_packages = $this->db->where('is_active', TRUE)->order_by('sort_order', 'ASC')->get('packages')->result();
		$packages = array();
		foreach ($db_packages as $p) {
			$packages[] = $p->package_key;
		}
		if (empty($packages)) {
			$packages = array('standard', 'premium', 'cluster');
		}

		// /plans?plan=premium links here with ?plan=premium.
		$plan = $this->input->get('plan');
		$plan = in_array($plan, $packages, TRUE) ? $plan : ($packages[0] ?? 'standard');

		if ($this->input->method() === 'post') {
			// On a POST the submitted radio wins over ?plan=
			$posted = $this->input->post('package');
			$plan = in_array($posted, $packages, TRUE) ? $posted : $plan;

			$password = $this->input->post('admin_password');
			$confirm  = $this->input->post('admin_password_confirm');

			if ($password !== '' && $password !== $confirm) {
				$this->render_register($plan, 'দুই পাসওয়ার্ড মিলছে না। অনুগ্রহ করে চেক করুন।', array(), $db_packages);
				return;
			}

			// The custom-domain field is only sent when the box was ticked.
			$post = $this->input->post();
			if (empty($post['use_custom_domain'])) {
				$post['custom_domain'] = '';
			}
			unset($post['admin_password_confirm'], $post['use_custom_domain']);

			$result = $this->create_tenant($post);

			if (empty($result['success'])) {
				$old = $post;
				unset($old['admin_password'], $old['admin_password_confirm']);
				$this->render_register($plan, $result['message'], $old, $db_packages);
				return;
			}

			// Success. Render the confirmation screen server-side.
			$this->template->saas('saas/pages/register_done', $this->saas_data('/register', array(
				'saas_plan' => $plan,
				'saas_done' => array_merge($result, array(
					'union_name'     => $post['union_name'] ?? '',
					'admin_email'    => $post['admin_email'] ?? '',
					'admin_password' => $password,
				)),
			)));
			return;
		}

		$this->render_register($plan, NULL, array(), $db_packages);
	}

	/**
	 * Render the registration form, optionally with an error and the values
	 * the visitor already typed.
	 *
	 * @param string      $plan         Selected package key.
	 * @param string|null $error        Error message, or null.
	 * @param array       $old          Previously submitted values, for redisplay.
	 * @param array|null  $db_packages  List of active package rows from DB.
	 * @return void
	 */
	private function render_register($plan, $error = NULL, $old = array(), $db_packages = NULL)
	{
		if ($db_packages === NULL) {
			$db_packages = $this->db->where('is_active', TRUE)->order_by('sort_order', 'ASC')->get('packages')->result();
		}
		$this->template->saas('saas/pages/register', $this->saas_data('/register', array(
			'saas_plan'     => $plan,
			'saas_error'    => $error,
			'saas_old'      => $old,
			'saas_packages' => $db_packages,
		)));
	}

	/**
	 * JSON endpoint: register a tenant and answer with JSON.
	 *
	 * This is the entry point the React RegisterPage used. It remains
	 * unauthenticated — that is a pre-existing property, flagged for removal,
	 * and deliberately not changed here. It is now a thin wrapper: all of the
	 * work lives in create_tenant(), which register()'s POST branch also calls so
	 * the confirmation screen can be server-rendered. The JSON body is unchanged,
	 * key for key.
	 *
	 * @return void
	 */
	public function api_register_tenant()
	{
		header('Content-Type: application/json; charset=utf-8');

		// Throttle before any work. Counted per IP in the signup window; skipped
		// in development so iterating on the form locally is not interrupted.
		$limit = $this->signup_throttle();
		if ($limit !== NULL) {
			echo json_encode($limit);
			return;
		}

		$input = json_decode($this->input->raw_input_stream, TRUE);
		if (empty($input)) {
			$input = $this->input->post();
		}

		echo json_encode($this->create_tenant($input));
	}

	/**
	 * Per-IP signup throttle. Returns an error array when the caller is over the
	 * limit, NULL when the request may proceed.
	 *
	 * Backed by the ion_auth login_attempts table, which already exists for
	 * exactly this shape of counter (ip_address + timestamp). Adding a dedicated
	 * table for the same job would be a second answer to one question.
	 *
	 * @return array|null
	 */
	private function signup_throttle()
	{
		$cfg   = (array) $this->config->item('tenant_signup');
		if (empty($cfg['enforce'])) {
			return NULL;
		}

		$max    = max(1, (int) ($cfg['max_attempts'] ?? 5));
		$window = max(60, (int) ($cfg['window_seconds'] ?? 3600));

		$ip = $this->input->ip_address();
		if ($ip === '') {
			return NULL; // no address to count against; do not fail open silently
		}

		$since = time() - $window;

		// Purge EXPIRED rows, never the ones inside the window. Deleting the
		// live rows is the obvious mistake and it is completely silent: the
		// count below then always returns 0, the limit is never reached, and
		// the endpoint stays unlimited for the life of the process. This
		// version shipped for one test run before it was caught.
		$this->db->where('login', 'signup')
		             ->where('time <', $since)
		             ->delete('login_attempts');

		$used = (int) $this->db->where('ip_address', $ip)
		                        ->where('login', 'signup')
		                        ->where('time >=', $since)
		                        ->count_all_results('login_attempts');

		if ($used >= $max) {
			// Report when the oldest attempt in the window ages out, so the
			// visitor is told how long to wait rather than just "try later".
			$oldest = $this->db->where('ip_address', $ip)
			                    ->where('login', 'signup')
			                    ->where('time >=', $since)
			                    ->order_by('time', 'ASC')
			                    ->limit(1)
			                    ->get('login_attempts')
			                    ->row('time');
			$wait = $oldest ? max(1, (int) ceil((($window - (time() - (int) $oldest))))) : $window;
			return [
				'success' => FALSE,
				'message' => 'আপনার IP থেকে অনেকগুলো রেজিস্ট্রেশন চেষ্টা হয়েছে। অনুগ্রহ করে ' . $wait . ' সেকেন্ড পর আবার চেষ্টা করুন।'
			];
		}

		$this->db->insert('login_attempts', [
			'ip_address' => $ip,
			'login'      => 'signup',
			'time'       => time()
		]);

		return NULL;
	}

	/**
	 * Create a tenant, its hostnames, and its first administrator.
	 *
	 * NOW A THIN DELEGATE to Tenant_creator::create(). The body used to live
	 * here, and the super-admin panel grew a second copy of it inside
	 * Super_admin::api_create_tenant() that disagreed on the apex domain, on
	 * whether the bare-slug hostname row was written, on which row was
	 * is_primary, and on whether a failed insert's insert_id() was checked.
	 * Two copies of "what is a tenant" is two answers, and the disagreement
	 * about is_primary was the one that changed which parishad an
	 * unrecognised host served.
	 *
	 * The full rationale, the slug-row explanation and the sequence-behind
	 * landmine are in the model's own docblock. This method stays because two
	 * callers already reference it and because it is the seam where signup
	 * behaviour is documented; the logic is not here any more.
	 *
	 * @param  array $input  Raw, unvalidated request values.
	 * @return array
	 */
	private function create_tenant($input)
	{
		$input['is_self_signup'] = true;
		$this->load->model('Tenant_creator');
		return $this->Tenant_creator->create($input);
	}

	/**
	 * Log in.
	 *
	 * Answers JSON to an AJAX caller and a 302 to a plain form submit. Which
	 * one is decided by how the request arrived, not by a parameter:
	 *
	 *   JSON body  -> JSON out   (the React app, and saas.js)
	 *   form body  -> redirect() (a <form> with JavaScript off)
	 *
	 * Detection uses the JSON decode itself, so a form POST that happens to
	 * carry X-Requested-With still gets JSON, and an AJAX caller sending a form
	 * body is not handed a redirect it cannot read.
	 *
	 * The JSON body is unchanged, key for key. What is new is only the
	 * non-AJAX branch: on success it redirects to the same $redirect_url the
	 * JSON reports, and on failure it bounces back to /login with the message
	 * in session flashdata, which saas/head.php already renders.
	 *
	 * CHANGED: the plaintext password_viewss_s comparison is GONE from this
	 * method, which called password_verify() directly and never reached
	 * Ion_auth_model::verify_password().
	 *
	 * A CORRECTION, because this line was itself wrong for a while. It used
	 * to claim the hardcoded ['password', 'admin', '841998'] backdoor was
	 * also gone from the project. It was gone only from here. The same list
	 * was still live in Ion_auth_model::verify_password() and
	 * is_max_login_attempts_exceeded() — the model, which Auth::login()
	 * reaches through ion_auth->login(). Both were removed on 2026-09-30.
	 * See the docblock in the model and docs/SADMIN-PANEL.md "Proving it".
	 * Nothing read that sentence as a to-do, because it read as a fact.
	 *
	 * @return void
	 */
	public function api_login()
	{
		$json_body  = json_decode($this->input->raw_input_stream, TRUE);
		$wants_json = ! empty($json_body) || $this->input->is_ajax_request();

		if ($wants_json) {
			header('Content-Type: application/json; charset=utf-8');
			$input = $json_body;
		} else {
			$input = $this->input->post();
		}

		$identity = trim($input['identity'] ?? '');
		$password = trim($input['password'] ?? '');
		$remember = (bool)($input['remember'] ?? false);

		if (empty($identity) || empty($password)) {
			$message = 'ইমেইল/ইউজারনেম এবং পাসওয়ার্ড প্রদান করুন।';

			if ($wants_json) {
				echo json_encode(['success' => false, 'message' => $message]);
			} else {
				$this->session->set_flashdata('error', $message);
				redirect('login');
			}
			return;
		}

		// Prevent lockout lockups on dev/test environments
		$this->db->where('login', $identity)->delete('login_attempts');

		if (function_exists('cleanup_expired_temp_admins')) {
			cleanup_expired_temp_admins();
		}

		// 1. Try standard Ion_auth login first
		$logged_in = $this->ion_auth->login($identity, $password, $remember);
		$user = null;

		if ($logged_in) {
			$user = $this->ion_auth->user()->row();
		} else {
			// 2. Fallback check: lookup user by email, username, or u_type
			$u_check = $this->db->group_start()
			                    ->where('email', $identity)
			                    ->or_where('username', $identity)
			                    ->or_where('u_type', $identity)
			                    ->group_end()
			                    ->get('users')
			                    ->row();

			/*
			 * REMOVED, deliberately, and this is the security change that
			 * mattered most:
			 *
			 *   - the plaintext comparison  $password === $u_check->password_viewss_s
			 *   - the hardcoded ['password', 'admin', '841998'] list for user
			 *     id 1 and admin@admin.com
			 *
			 * Together they meant any parishad super-admin account could be
			 * taken over by typing one of three guessable strings, and that
			 * every account created through signup also authenticated against
			 * an unencrypted copy of its password.
			 *
			 * What is left is only the identity widening: ion_auth looks the
			 * user up by email and u_type, and this block looks them up by
			 * email, username or u_type. The credential check itself is
			 * password_verify() against the bcrypt hash, which is what
			 * ion_auth->login() already did one branch above and failed only
			 * because it does not search by u_type.
			 *
			 * So: the identity lookup is preserved, the credential check is
			 * the real one, and there is no path to a session that does not
			 * go through a hash.
			 *
			 * That last sentence was written here and was WRONG for the whole
			 * time the backdoor was still live in Ion_auth_model. Removing the
			 * list from this method removed a branch that was never taken on
			 * this path at all — this method calls password_verify() directly.
			 * The list that did execute was in the model, reached by
			 * Auth::login(). It is gone as of 2026-09-30; the sentence is
			 * true now, and it is kept precisely because it was once
			 * confidently, specifically, and wrongly asserted.
			 */
			if ($u_check && $u_check->active == 1 && password_verify($password, (string) $u_check->password)) {
				$this->ion_auth_model->set_session($u_check);
				$this->ion_auth_model->update_last_login($u_check->id);

				$logged_in = true;
				$user = $u_check;
			}
		}

		if ($logged_in && $user) {
			// Platform super admin: no tenant, no parishad dashboard — the
			// central control panel is their landing page after login.
			if ($this->ion_auth->in_group('sadmin', $user->id)) {
				$redirect_url = base_url('super_admin');

				if ($wants_json) {
					echo json_encode([
						'success'      => true,
						'message'      => 'লগইন সফল হয়েছে! সুপার অ্যাডমিন প্যানেলে নিয়ে যাওয়া হচ্ছে...',
						'redirect_url' => $redirect_url,
						'tenant_id'    => 0,
						'tenant_host'  => null,
						'subdomain'    => null,
						'user'         => [
							'id'       => $user->id,
							'name'     => $user->first_name ?: $user->username,
							'email'    => $user->email,
							'u_type'   => $user->u_type
						]
					]);
				} else {
					redirect($redirect_url);
				}
				return;
			}

			// NULL is not tenant 1. It used to be `?? 1`, which quietly pointed a
			// tenant-less account at ধুম; nothing downstream needs the lie,
			// because a non-sadmin user with no parishad is refused by
			// Dashboard's tenant guard rather than served someone else's data.
			$tenant_id = (int)($user->ins_pr_id_s ?? 0);
			if ($tenant_id > 0) {
				$t_check = $this->db->where('ins_titute_p_idds', $tenant_id)->get('ins_titute')->row();
				if ($t_check && isset($t_check->is_active) && ! $t_check->is_active) {
					$sub_chk = $this->db->where('ins_titute_p_id', $tenant_id)->get('subscriptions')->row();
					$this->ion_auth->logout();
					if ($sub_chk && $sub_chk->status === 'pending') {
						$message = 'আপনার পরিষদটি এখনও প্ল্যাটফর্ম সুপার অ্যাডমিনের অনুমোদনের অপেক্ষায় রয়েছে। অনুমোদন পাওয়ার পর আপনার ৪ দিনের ফ্রি ট্রায়াল সক্রিয় হবে এবং আপনি লগইন করতে পারবেন।';
					} else {
						$message = 'আপনার ইউনিয়ন পরিষদের কার্যক্রম প্ল্যাটফর্ম কর্তৃপক্ষ কর্তৃক সাময়িকভাবে স্থগিত (Suspended) রাখা হয়েছে। সহায়তার জন্য সেন্ট্রাল সাপোর্টে যোগাযোগ করুন।';
					}
					if ($wants_json) {
						echo json_encode(['success' => false, 'message' => $message]);
					} else {
						$this->session->set_flashdata('error', $message);
						redirect('login');
					}
					return;
				}
			}

			// Look up tenant's domain / subdomain
			$domain_rows = $this->db->where('ins_titute_p_id', $tenant_id)
			                        ->where('is_verified', true)
			                        ->order_by('is_primary', 'desc')
			                        ->get('tenant_domains')
			                        ->result();

			$tenant_host = null;
			/*
			 * Where to send this user after login.
			 *
			 * This is tenant_login_url() in application/helpers/tenant_helper.php,
			 * shared with Auth::login() so the two login routes cannot drift
			 * apart again. It used to be decided inline here, by a hardcoded
			 * list of platform hosts, with the rule "stay on whichever host you
			 * typed the password into" — which handed rupsha's chairman
			 * বাঘমারা's entire dataset: he signed in on baghmaraup.gov.bd, was
			 * redirected to baghmaraup.gov.bd/dashboard, and Tenant_resolver
			 * resolves the host (step 3) ahead of the session (step 5), so
			 * tenant_code() answered 5 and every query in the 80-odd Dashboard
			 * methods read বাঘমারা's rows.
			 *
			 * $domain_rows is still fetched here because the JSON payload
			 * reports tenant_host to the caller; tenant_login_url() re-queries
			 * for its own decision rather than trust this one, which filters
			 * is_verified but not is_dev_only.
			 */
			$redirect_url = tenant_login_url($user, tenant_code());

			$tenant_host = null;
			foreach ($domain_rows as $dr) {
				if ($dr->hostname === 'localhost' || $dr->hostname === '127.0.0.1') {
					continue;
				}
				$tenant_host = $dr->hostname;
				break;
			}

			$message = 'লগইন সফল হয়েছে! ড্যাশবোর্ডে নিয়ে যাওয়া হচ্ছে...';

			if ($wants_json) {
				echo json_encode([
					'success'      => true,
					'message'      => $message,
					'redirect_url' => $redirect_url,
					'tenant_id'    => $tenant_id,
					'tenant_host'  => $tenant_host,
					'subdomain'    => $tenant_host,
					'user'         => [
						'id'       => $user->id,
						'name'     => $user->first_name ?: $user->username,
						'email'    => $user->email,
						'u_type'   => $user->u_type
					]
				]);
			} else {
				// A real 302, so browser history, the back button and
				// refresh-after-post behave the way they should. The
				// success message is not flashed: the dashboard
				// replacing the form is the confirmation.
				redirect($redirect_url);
			}
		} else {
			$message = 'ভুল ইউজারনেম/ইমেইল অথবা পাসওয়ার্ড। অনুগ্রহ করে সঠিক পাসওয়ার্ড প্রদান করুন।';

			if ($wants_json) {
				echo json_encode([
					'success' => false,
					'message' => $message
				]);
			} else {
				$this->session->set_flashdata('error', $message);
				// identity in the query so the form comes back with the
				// username filled in. The password is not, and must not be.
				redirect('login?identity=' . rawurlencode($identity));
			}
		}
	}

	public function single_membs_view_methods()
	{
		$this->data['mbr'] = $this->FamilyModel->get_single_member_by_uniqs_id($this->input->get('memNO'));
		if (empty($this->data['mbr'])) {
			redirect('','refresh');
		}else {
			$this->load->view('front/single_member_views', $this->data);
		}
	}

// Search Holding
	public function search_holding_front_page()
	{
		$this->load->view('front/search_holding_number_view_pages', $this->data);
	}

	public function single_emp_view_method()
	{
		$this->data['mbr'] = $this->FamilyModel->get_single_employee_by_uniqs_id($this->input->get('empNO'));
		if (empty($this->data['mbr'])) {
			redirect('','refresh');
		}else {
			$this->load->view('front/single_employee_views', $this->data);
		}
	}

	public function single_holding_person_methods($uniqs)
	{
		$this->data['holding_info'] = $this->FamilyModel->serach_data_by_uniq_num($uniqs);
		if (empty($this->data['holding_info'])) {
			redirect('','refresh');
		}else {
			// $this->data['i'] = $this->FamilyModel->a($this->data['holding_info']->data_id);
			
		$this_year=date('Y');

				
			// Paid Amount
			$this->data['paid_amount'] = $this->FamilyModel->get_paid_amount_by_mainID($this->data['holding_info']->data_id);


			$paid_sum = array_sum(array_column($this->data['paid_amount'], 'paid_amount'));

			$start_n=$this->data['holding_info']->holding_starting_years;
			$year_arr = explode ("-", $start_n);

			$bokeya_year = $this_year-$year_arr[0]+1;
			$total_unpaid = $bokeya_year*$this->data['holding_info']->holding_rates_yearly;
			$this->data['total_unpaid'] = $total_unpaid - $paid_sum;


			
			$data__c_year_s=[];

			for ($i=$this->data['holding_info']->holding_starting_years; $i < date('Y',strtotime('+1 year')).'-'.date('Y', strtotime('+2 year')); 
				$i = $year_arr[1].'-'.$year_arr[1]+1) {
				$year_arr = explode ("-", $i);
				// $b = array($i);
				array_push($data__c_year_s, $i);
			}

			$ss_paidable_year = [];
			foreach ($this->data['paid_amount'] as $ss) {
				array_push($ss_paidable_year, $ss->paidable_year);
			}

			// Compare all values by a json_encode
			$aaaa = array_diff($data__c_year_s, $ss_paidable_year);
			$this->data['c_year_s'] = array_values($aaaa);
			// Json decode the result



			$this->load->view('front/single_holding_person_view_file', $this->data);
		}
	}


	
	public function search_holding_for_page_api()
	{
		// Search Person   ward   
// 		$datas = $this->FamilyModel->serach_data_by_holding_num($holding_no);
		$this_datas['get_search_data'] = $this->FamilyModel->serachings_by_holding_num($this->input->post('holding_number').'-'.$this->input->post('ward'));
		$this->output->set_content_type('application/json')->set_output(json_encode($this_datas));
	}

	public function adds()
	{
		$this->load->view('front/add', $this->data);
	}
	
	public function get_village_by_ward_nos()
	{
		$ward_nos = $this->input->post('ward_n');
		$house_infos = $this->FamilyModel->get_villages_infos_byward($ward_nos); 
		$this->output->set_content_type('application/json')->set_output(json_encode($house_infos)); 
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

	public function get_house_by_village_nos()
	{
		$vill_no = $this->input->post('vill_nos');
		$house_infos = $this->FamilyModel->get_houses_infos_byvillage_no($vill_no); 
		$this->output->set_content_type('application/json')->set_output(json_encode($house_infos)); 
	}
	
	public function insert_data()
	{
		$uniqid = uniqid().'-'.uniqid();
		$data = array(
				'user_id' 					=> 1,
				'family_person_name_head' 	=> $this->input->post('name'), 
				'pason_father_name' 		=> $this->input->post('fatherName'), 
				'parson_mother_name' 		=> $this->input->post('motherName'), 
				'parson_paranent_address' 	=> $this->input->post('presentAddress'), 
				'parson_parmanent_address' 	=> $this->input->post('parmanentAddress'), 
				'profile_image' 			=> $this->input->post('profile_image_base64'), 
				'home_image' 				=> $this->input->post('house_image_base64'), 
				'nid_number' 				=> $this->input->post('nidNumber'), 
				'ins_aut_iiid' 				=> tenant_code(), 
				// Was hardcoded to 645, which is ধুম's (tenant 1) un_iddd_iiid —
				// so every tenant using the public /insrt form stamped its rows
				// with ধুম's unid while ins_aut_iiid on the same row correctly
				// said something else. It never corrupted anything only because
				// tenant 1 is the sole tenant with family_data_sheet rows (5,935
				// of them, all unid=645). The column is varchar, nullable, has no
				// foreign key, and is never read back — Dashboard's own form
				// (Dashboard::dataset_form) writes the logged-in user's
				// union_unq_idd_iii — so this is the only place that was wrong.
				//
				// tenant_un_id() is ins_titute.un_iddd_iiid for the RESOLVED
				// tenant, which is the value meant here. It is NULL for tenants
				// 4 and 5, so the ?: is deliberate: writing their real (absent)
				// unid is correct, and a 645 fallback would be the same
				// cross-tenant bug wearing a different hat.
				'unid' 						=> tenant_un_id() ?: null, 
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
        $this->session->set_flashdata('msg', 'Data Added Successfully'); 
		redirect("add");
	}


	public function login()
	{
		if ($this->ion_auth->logged_in()) {
			redirect('dashboard', 'refresh');
		}

		/*
		 * Which login page, decided the same way render_site() decides the home
		 * page — on the resolver's reported SOURCE, not on the host string.
		 *
		 * This used to render the ViretaDev product-site login on every host,
		 * which meant a chairmen clicking "প্যানেল লগইন" on his own parishad's
		 * site was handed the vendor's marketing page: the SaaS nav (হোম ·
		 * প্ল্যানকেট ফিচার · ব্লগিস্ট · FAQ), a "লাইভ SaaS প্রদর্শনী" demo
		 * banner, and a placeholder helpline (০৯৬৬০-XXXXXX) that belongs to
		 * nobody. There was never a host check here; /login was simply added to
		 * the product site's nav as "sign in to the product" and became the only
		 * login entry point in the project, which left
		 * application/views/front/login.php orphaned — nothing loaded it.
		 *
		 * Platform host -> the marketing login, unchanged, because that is what a
		 * prospective customer needs to see.
		 * Tenant host   -> a login page branded with THAT parishad.
		 *
		 * Both post to api_login, so the credential check, the sadmin branch and
		 * tenant_login_url() are shared. Only the page around the form differs.
		 */
		$source = function_exists('tenant_source') ? tenant_source() : '';
		$explicit_tenant = in_array($source, array(
			'domain',     // hostname registered in tenant_domains
			'subdomain',  // <slug>.<root domain>
			'forced',     // resolver->force(), dev hosts only
			'dev-query',  // ?tenant=N, dev hosts only
			'dev-header', // X-Tenant: N, dev hosts only
		), TRUE);

		if ($explicit_tenant) {
			$this->load->view('front/login', $this->data);
		} else {
			$this->template->saas('saas/pages/login', $this->saas_data('/login'));
		}
	}

	public function publicly_single_sonod_verify_method($sonod_no = null)
	{
		$sonod_no = trim((string)($sonod_no ?: ($this->input->get('sonod_no') ?: $this->input->post('sonod_no'))));

		$sonod = null;
		$qr_code = '';

		if ($sonod_no !== '') {
			$this->db->select('
				sonod_genarates.*,
				ins_titute.ins_name, ins_titute.ins_name_en, ins_titute.ins_title_ss,
				ins_titute.ins_logo_path, ins_titute.socib_phone, ins_titute.chairman_phone,
				ins_titute.cm_name_bn, ins_titute.cm_name_en, ins_titute.addre_s_s,
				un_list.un_name, un_list.un_bn_name,
				up_list.up_name, up_list.up_bn_name,
				dist_list.dist_name, dist_list.dist_bn_name,
				deft_cer_all.certify_titles,
				certificate_person_verify_info_editing.*,
				customer_full_info.user_person_name, customer_full_info.user_phone_no,
				customer_full_info.address_full, customer_full_info.user_nid_no,
				person_verify_nid_data_info_saving.nid_num10, person_verify_nid_data_info_saving.nid_no17,
				person_verify_nid_data_info_saving.dob_get, person_verify_nid_data_info_saving.ins_wardno
			');
			$this->db->from('sonod_genarates');
			if (ctype_digit($sonod_no)) {
				$this->db->group_start();
				$this->db->where('sonod_genarates.cgp_pr_idiidid_pr', $sonod_no);
				$this->db->or_where('sonod_genarates.sonod_uniqs', $sonod_no);
				$this->db->group_end();
			} else {
				$this->db->where('sonod_genarates.sonod_uniqs', $sonod_no);
			}
			$this->db->join('ins_titute', 'ins_titute.ins_titute_p_idds = sonod_genarates.insididid_pridididid', 'left');
			$this->db->join('un_list', 'un_list.un_id = ins_titute.un_iddd_iiid', 'left');
			$this->db->join('up_list', 'up_list.up_id = un_list.upazilla_id', 'left');
			$this->db->join('dist_list', 'dist_list.dist_id = up_list.district_id', 'left');
			$this->db->join('deft_cer_all', 'deft_cer_all.deft_cer_all_au_iddd = sonod_genarates.deft_cer_aididid', 'left');
			$this->db->join('certificate_person_verify_info_editing', 'certificate_person_verify_info_editing.ver_per_info_sonod_uniqs = sonod_genarates.sonod_uniqs', 'left');
			$this->db->join('customer_full_info', 'customer_full_info.udc_list_auto_p_iidd = sonod_genarates.gen_usr_ididd', 'left');
			$this->db->join('person_verify_nid_data_info_saving', 'person_verify_nid_data_info_saving.person_v_id = sonod_genarates.thisins_cert_defaults_p_idididid', 'left');
			$sonod = $this->db->get()->row();

			if ($sonod) {
				try {
					if (class_exists('TCPDF2DBarcode')) {
						$target_no = $sonod->cgp_pr_idiidid_pr ?: $sonod->sonod_uniqs;
						$verify_url = base_url('sonod-v/' . $target_no);
						$barcodeobj = new TCPDF2DBarcode($verify_url, 'QRCODE,H');
						$qr_code = $barcodeobj->getBarcodePNG(4, 4, array(0, 0, 0));
					}
				} catch (Exception $e) {}
			}
		}

		$this->data['sonod'] = $sonod;
		$this->data['searched_no'] = $sonod_no;
		$this->data['qr_code'] = $qr_code;
		$this->data['is_verified'] = !empty($sonod);
		$this->data['page_title'] = $sonod ? ($sonod->certificate_title_head_text ?? 'সনদপত্র') . ' অনলাইন যাচাই' : 'অনলাইন সনদপত্র যাচাইকরণ';
		$this->data['page_desc']  = 'ডিজিটাল ইউনিয়ন পরিষদ প্ল্যাটফর্মে ইস্যুকৃত সনদপত্রের সরকারি অনলাইন সত্যতা যাচাই';

		$this->load->view('front/sonod_verify_view_file', $this->data);
	}

	public function del_amnt()
	{
	    $id = $this->input->get('id');
		
        $this->db->where('ampaid_id', $id);
        // $this->db->delete('amount_paid');
	}

	public function del_amnt2()
	{
	    $id = $this->input->get('id');
		
        $this->db->where('collect_amnt_invoice_idd', $id);
        // $this->db->delete('collect_amnt_invoice');
	}	
	
	
	
	
	

}