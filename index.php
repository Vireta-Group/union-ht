<?php

// Conceal server technology fingerprint and apply security headers
@ini_set('expose_php', '0');
if (function_exists('header_remove')) {
	header_remove('X-Powered-By');
}
if (!headers_sent()) {
	header('X-Content-Type-Options: nosniff');
	header('X-Frame-Options: SAMEORIGIN');
	header('X-XSS-Protection: 1; mode=block');
	header('Referrer-Policy: strict-origin-when-cross-origin');
	header("Content-Security-Policy: default-src 'self' data: blob:; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://unpkg.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net https://unpkg.com; font-src 'self' data: https://fonts.gstatic.com https://cdn.jsdelivr.net https://unpkg.com; img-src 'self' data: blob: https:; connect-src 'self' https:; frame-ancestors 'self';");
}

// Activate Application Security Shield (WAF, Rate Limiting, IP Ban)
require_once __DIR__ . '/application/libraries/Vireta_security_shield.php';
Vireta_security_shield::protect();

	if (empty($_SERVER['CI_ENV']) && file_exists(__DIR__ . '/.env')) {
		$__env_txt = file_get_contents(__DIR__ . '/.env');
		if (preg_match('/^\s*APP_ENV\s*=\s*([^\s#]+)/m', $__env_txt, $__m)) {
			$_SERVER['CI_ENV'] = trim($__m[1], "\"'");
		}
		unset($__env_txt, $__m);
	}
	define('ENVIRONMENT', isset($_SERVER['CI_ENV']) ? $_SERVER['CI_ENV'] : 'production');

	if (ENVIRONMENT === 'production')
	{
		$__preflight = array();

		$__env_key = '';
		if (file_exists(__DIR__ . '/.env') && is_readable(__DIR__ . '/.env'))
		{
			if (preg_match('/^\s*APP_ENCRYPTION_KEY\s*=\s*([^\s#]+)/m', file_get_contents(__DIR__ . '/.env'), $__m))
			{
				$__env_key = trim($__m[1], "\"'");
			}
			unset($__m);
		}

		if ($__env_key === '' || $__env_key === 'allion5' || strlen($__env_key) < 32)
		{
			$__preflight[] = 'APP_ENCRYPTION_KEY is missing, still the published default, or shorter than 32 chars. Generate one with: php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"';
		}

		$__apex = '';
		if (file_exists(__DIR__ . '/.env') && is_readable(__DIR__ . '/.env'))
		{
			if (preg_match('/^\s*APP_TENANT_APEX\s*=\s*([^\s#]+)/m', file_get_contents(__DIR__ . '/.env'), $__m))
			{
				$__apex = trim($__m[1], "\"'");
			}
			unset($__m);
		}

		if ($__apex === '')
		{
			$__preflight[] = 'APP_TENANT_APEX is not set, so signup cannot mint a subdomain and Tenant_resolver subdomain matching is disabled.';
		}

		unset($__env_key, $__apex);

		if ($__preflight)
		{
			http_response_code(503);
			header('Content-Type: text/plain; charset=utf-8');
			header('Retry-After: 3600');
			echo "Service unavailable: production configuration incomplete.\n\n";
			echo "Fix the following in .env and reload:\n";
			foreach ($__preflight as $__problem) {
				echo '  - ' . $__problem . "\n";
			}
			exit(1);
		}

		unset($__preflight);
	}


switch (ENVIRONMENT)
{
	case 'development':
	    @error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT & ~E_NOTICE);
	    ini_set('display_errors', '1');
	    ini_set('display_startup_errors', '1');
	break;

	case 'testing':
	case 'production':
		ini_set('display_errors', 0);
		if (version_compare(PHP_VERSION, '5.3', '>='))
		{
			error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
		}
		else
		{
			error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE);
		}
	break;

	default:
		header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
		echo 'The application environment is not set correctly.';
		exit(1); // EXIT_ERROR
}

	$system_path = 'system';

	$application_folder = 'application';

	$view_folder = '';


	// Set the current directory correctly for CLI requests
	if (defined('STDIN'))
	{
		chdir(dirname(__FILE__));
	}

	if (($_temp = realpath($system_path)) !== FALSE)
	{
		$system_path = $_temp.DIRECTORY_SEPARATOR;
	}
	else
	{
		// Ensure there's a trailing slash
		$system_path = strtr(
			rtrim($system_path, '/\\'),
			'/\\',
			DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
		).DIRECTORY_SEPARATOR;
	}

	// Is the system path correct?
	if ( ! is_dir($system_path))
	{
		header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
		echo 'Your system folder path does not appear to be set correctly. Please open the following file and correct this: '.pathinfo(__FILE__, PATHINFO_BASENAME);
		exit(3); // EXIT_CONFIG
	}

/*
 * -------------------------------------------------------------------
 *  Now that we know the path, set the main path constants
 * -------------------------------------------------------------------
 */
	// The name of THIS file
	define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));

	// Path to the system directory
	define('BASEPATH', $system_path);

	// Path to the front controller (this file) directory
	define('FCPATH', dirname(__FILE__).DIRECTORY_SEPARATOR);

	// Name of the "system" directory
	define('SYSDIR', basename(BASEPATH));

	// The path to the "application" directory
	if (is_dir($application_folder))
	{
		if (($_temp = realpath($application_folder)) !== FALSE)
		{
			$application_folder = $_temp;
		}
		else
		{
			$application_folder = strtr(
				rtrim($application_folder, '/\\'),
				'/\\',
				DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
			);
		}
	}
	elseif (is_dir(BASEPATH.$application_folder.DIRECTORY_SEPARATOR))
	{
		$application_folder = BASEPATH.strtr(
			trim($application_folder, '/\\'),
			'/\\',
			DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
		);
	}
	else
	{
		header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
		echo 'Your application folder path does not appear to be set correctly. Please open the following file and correct this: '.SELF;
		exit(3); // EXIT_CONFIG
	}

	define('APPPATH', $application_folder.DIRECTORY_SEPARATOR);

	// The path to the "views" directory
	if ( ! isset($view_folder[0]) && is_dir(APPPATH.'views'.DIRECTORY_SEPARATOR))
	{
		$view_folder = APPPATH.'views';
	}
	elseif (is_dir($view_folder))
	{
		if (($_temp = realpath($view_folder)) !== FALSE)
		{
			$view_folder = $_temp;
		}
		else
		{
			$view_folder = strtr(
				rtrim($view_folder, '/\\'),
				'/\\',
				DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
			);
		}
	}
	elseif (is_dir(APPPATH.$view_folder.DIRECTORY_SEPARATOR))
	{
		$view_folder = APPPATH.strtr(
			trim($view_folder, '/\\'),
			'/\\',
			DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
		);
	}
	else
	{
		header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
		echo 'Your view folder path does not appear to be set correctly. Please open the following file and correct this: '.SELF;
		exit(3); // EXIT_CONFIG
	}

	define('VIEWPATH', $view_folder.DIRECTORY_SEPARATOR);

/*
 * --------------------------------------------------------------------
 * LOAD THE BOOTSTRAP FILE
 * --------------------------------------------------------------------
 *
 * And away we go...
 */
require_once BASEPATH.'core/CodeIgniter.php';
