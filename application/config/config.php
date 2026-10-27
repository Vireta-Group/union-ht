<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
| ENVIRONMENT LOADER
| -------------------------------------------------------------------
| Reads a flat KEY=VALUE .env file from the project root into $_ENV.
| Loaded here because config.php is read by CI before any other config
| file, which is early enough for database.php to use the values.
|
| Deliberately dependency-free: if .env is missing or unreadable the
| loader simply does nothing, and the fallback values in each config
| file keep the application bootable. That matters for deploys where
| the file has not been created yet.
*/
if (!function_exists('env'))
{
	function env($key, $default = NULL)
	{
		$val = getenv($key);
		if ($val === FALSE)
		{
			$val = (isset($_ENV[$key]) ? $_ENV[$key] : NULL);
		}
		return ($val === NULL || $val === '') ? $default : $val;
	}

	$__env_file = FCPATH . '.env';
	if (is_readable($__env_file))
	{
		$__env_lines = file($__env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		foreach ($__env_lines as $__env_line)
		{
			$__env_line = trim($__env_line);
			if ($__env_line === '' || $__env_line[0] === '#')
			{
				continue;
			}
			$__env_parts = explode('=', $__env_line, 2);
			if (count($__env_parts) !== 2)
			{
				continue;
			}
			$__env_key = trim($__env_parts[0]);
			$__env_val = trim($__env_parts[1]);
			// strip a single layer of matching quotes
			if (strlen($__env_val) > 1
				&& ($__env_val[0] === '"' || $__env_val[0] === "'")
				&& substr($__env_val, -1) === $__env_val[0])
			{
				$__env_val = substr($__env_val, 1, -1);
			}
			$_ENV[$__env_key] = $__env_val;
			putenv($__env_key . '=' . $__env_val);
		}
		unset($__env_file, $__env_lines, $__env_line, $__env_parts, $__env_key, $__env_val);
	}
}

// Set Timezone
date_default_timezone_set('Asia/Dhaka');


// base url
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
    $ht = "https://";
} else {
    $ht = "http://";
}
$config['base_url'] = $ht . $_SERVER['HTTP_HOST'];
$config['base_url'] .= preg_replace('@/+$@', '', dirname($_SERVER['SCRIPT_NAME'])) . '/';
// base url

$config['index_page']               = '';
$config['uri_protocol']	            = 'REQUEST_URI';
$config['url_suffix']               = '';
$config['language']	                = 'english';
$config['charset']                  = 'UTF-8';
$config['enable_hooks']             = FALSE;
$config['subclass_prefix']          = 'MY_';
$config['composer_autoload']        = 'vendor/autoload.php';
$config['permitted_uri_chars']      = 'a-z 0-9~%.:_\-';
$config['enable_query_strings']     = FALSE;
$config['controller_trigger']       = 'c';
$config['function_trigger']         = 'm';
$config['directory_trigger']        = 'd';
$config['allow_get_array']          = TRUE;
$config['log_threshold']            = 0;
$config['log_path']                 = '';
$config['log_file_extension']       = '';
$config['log_file_permissions']     = 0644;
$config['log_date_format']          = 'Y-m-d H:i:s';
$config['error_views_path']         = '';
$config['cache_path']               = '';
$config['cache_query_string']       = FALSE;
/*
| ENCRYPTION KEY
|
| Read from .env (APP_ENCRYPTION_KEY). It used to be the literal 'allion5',
| checked into the repository, which meant the key protecting every encrypted
| CI value was public knowledge.
|
| The default below is deliberately still that published value, and index.php
| refuses to boot in production while it is unchanged — so a deploy that
| forgets to set APP_ENCRYPTION_KEY fails loudly instead of quietly encrypting
| with a key an attacker already has.
|
| Rotating this invalidates existing sessions and any encrypted data, so
| generate it once per environment and keep it out of version control:
|     php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"
*/
$config['encryption_key']           = env('APP_ENCRYPTION_KEY', 'allion5');

// session
$config['sess_driver']              = 'files';
$config['sess_cookie_name']         = 'xrf_token';
$config['sess_expiration']          = 0;
$config['sess_expire_on_close']	    = TRUE;
$config['sess_encrypt_cookie']	    = FALSE;
$config['sess_save_path']           = env('APP_SESSION_PATH', sys_get_temp_dir());
$config['sess_match_ip']            = FALSE;
$config['sess_time_to_update']      = 300;
$config['sess_regenerate_destroy']  = FALSE;
$config['sess_match_useragent']     = FALSE;

// cookie
$config['cookie_prefix']	        = '';
$config['cookie_domain']	        = '';
$config['cookie_path']		        = '/';
// Secure/HttpOnly follow APP_ENV. Sending the session cookie over plain HTTP
// or letting script read it is fine on localhost and wrong in production, and
// the difference is exactly APP_ENV, so deriving it removes the switch nobody
// remembers to flip before a deploy.
$config['cookie_secure']	        = (strtolower((string) env('APP_ENV', '')) === 'production');
$config['cookie_httponly'] 	        = TRUE;
$config['cookie_samesite'] 	        = 'Lax';

$config['standardize_newlines']     = FALSE;
$config['global_xss_filtering']     = FALSE;

// csrf
$config['csrf_protection']          = FALSE;
$config['csrf_token_name']          = 'csrf_hash_name';
$config['csrf_cookie_name']         = 'csrf_cookie_name';
$config['csrf_expire']              = 7200;
$config['csrf_regenerate']          = TRUE;
$config['csrf_exclude_uris']        = array();

$config['compress_output']          = FALSE;
$config['time_reference']           = 'local';
$config['rewrite_short_tags']       = FALSE;
$config['proxy_ips']                = '';

/*
|--------------------------------------------------------------------------
| Multi-tenant resolution
|--------------------------------------------------------------------------
| Consumed by application/libraries/Tenant_resolver.php
|
| root_domains         apex domains a tenant may live under. For
|                      foo.example.com list 'example.com'. Empty in
|                      development — subdomain matching stays off until
|                      real domains are added here.
| allow_dev_override   permit ?tenant=N / X-Tenant: N. Gated twice: this
|                      flag must be TRUE, AND the resolver requires a host
|                      in dev_hosts (or a bare IP). It is forced FALSE when
|                      APP_ENV is not 'development', so publishing the app
|                      cannot leave the override switch on by accident.
| dev_hosts            the only hostnames that may reach the override and
|                      the only ones that may match an is_dev_only row in
|                      tenant_domains. *.local is mDNS-reserved, so these
|                      names can never collide with a real domain.
| session_key          session key written by Ion_auth_model::set_session().
|
| NOTE: there is deliberately no default_tenant_id. When a host matches
| nothing, the resolver falls back to the tenant_domains row flagged
| is_primary = TRUE. The fallback lives in the database, not in this file.
*/
$config['tenant_diag_any_host'] = FALSE; // keep FALSE outside local dev

// Everything dev-related derives from APP_ENV rather than from a literal
// TRUE. A published app that still had ?tenant=N available would let any
// visitor read any tenant, and a published app that still honoured
// dhum.local would serve a development hostname to the public. Deriving
// both removes the failure mode: there is no switch to forget to flip.
//
// To re-enable locally: APP_ENV=development in .env
$config['is_development'] = strtolower((string) env('APP_ENV', '')) === 'development';

// The apex a tenant may live under, e.g. 'viretaup.com'. It is read from .env
// rather than written here because the same codebase runs on localhost, on a
// staging host and on the production VPS, and the apex is the one value that
// differs between all three.
//
// It has two jobs, and confusing them is the easiest mistake to make here:
//
//   1. It is appended by create_tenant() when a new parishad registers, so
//      signup writes '<subdomain>.<apex>' into tenant_domains.
//   2. It is matched by Tenant_resolver::match_subdomain(), which strips the
//      suffix and looks the BARE SLUG up in tenant_domains. So the row signup
//      writes for subdomain matching is the slug itself ('dhum'), not the full
//      hostname. Both are written; the full one is what a DNS name needs, the
//      bare one is what the resolver reads.
//
// An empty apex (the local default) turns subdomain matching off entirely, which
// is the correct behaviour on a machine with no such domain.
$__ht_tenant_apex = strtolower(trim((string) env('APP_TENANT_APEX', '')));
$__ht_tenant_apex = trim($__ht_tenant_apex, " \t\n\r\0\x0B.");

$config['tenant'] = array(
	'apex_domain'        => $__ht_tenant_apex,

	// Deriving this from the apex means nobody has to remember to add the same
	// domain in two places, which is how the two drift apart in the first place.
	'root_domains'       => $__ht_tenant_apex === '' ? array() : array($__ht_tenant_apex),

	'allow_dev_override' => $config['is_development'],

	'dev_hosts'          => $config['is_development']
		? array(
			'localhost',
			'127.0.0.1',
			'::1',
			'dhum.local',      // tenant 1 — ৪নং ধুম ইউনিয়ন পরিষদ
			'test-2.local',    // tenant 3 — [TEST] দ্বিতীয় ইউনিয়ন পরিষদ
		)
		: array(),

	'session_key'        => 'union_code',
);

/*
| Signup throttle.
|
| api_register_tenant is an unauthenticated endpoint that inserts a tenant, a
| hostname and an admin account, and it used to be callable without limit — an
| open invitation to fill the platform and, worse, to claim the subdomain of
| somebody else's parishad. Counted per IP inside the signup window.
|
| Local dev is exempt: while iterating on the register form you will hit it
| repeatedly, and a throttle that fires in development teaches you to ignore it
| in production.
*/
$config['tenant_signup'] = array(
	'max_attempts'   => (int) env('APP_SIGNUP_MAX_ATTEMPTS', 5),
	'window_seconds' => (int) env('APP_SIGNUP_WINDOW', 3600),
	'enforce'        => ! $config['is_development'],
);
