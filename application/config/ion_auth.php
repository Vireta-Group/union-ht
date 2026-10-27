<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['database_group_name'] 				= '';
$config['tables']['users']           		= 'users';
$config['tables']['groups']          		= 'groups';
$config['tables']['users_groups']    		= 'users_groups';
$config['tables']['login_attempts']  		= 'login_attempts';
$config['join']['users']  					= 'user_id';
$config['join']['groups'] 					= 'group_id';

/*
| PASSWORD HASHING
|
| bcrypt cost is read from .env (APP_BCRYPT_COST) with a floor of 12.
|
| The default was 10, which is PHP's own default and is deliberately chosen to
| be cheap -- it is a floor for developers on fast hardware, not a target. On
| the production VPS a login is a remote round trip anyway, so the hashing
| itself is not what the user waits on, and 12 roughly doubles the work an
| offline cracker has to do per guess.
|
| Raising the cost does NOT invalidate existing hashes: a bcrypt hash records
| the cost it was made with, so password_verify() keeps checking old ones at
| their own cost and only NEW hashes use the new value. Accounts are rehashed
| transparently the next time their owner logs in successfully.
|
| Cost 4-31 is what bcrypt accepts; anything above 12 gets slower linearly, and
| above ~15 the login page starts to feel it. 12-13 is the useful band.
*/
$config['hash_method']						= 'bcrypt';
$config['bcrypt_default_cost']				= max(12, (int) env('APP_BCRYPT_COST', 12));
$config['argon2_default_params']	= [
	'memory_cost'	=> defined('PASSWORD_ARGON2_DEFAULT_MEMORY_COST') ? PASSWORD_ARGON2_DEFAULT_MEMORY_COST : 1 << 12,
	'time_cost'	=> defined('PASSWORD_ARGON2_DEFAULT_TIME_COST') ? PASSWORD_ARGON2_DEFAULT_TIME_COST : 2,
	'threads'	=> defined('PASSWORD_ARGON2_DEFAULT_THREADS') ? PASSWORD_ARGON2_DEFAULT_THREADS : 2
];

$config['site_title']                 = "Example.com";
$config['admin_email']                = "admin@example.com";
$config['default_group']              = 'members';
$config['admin_group']                = 'admin';
$config['identity']                   = 'u_type';


$config['min_password_length']        	= 8;
$config['email_activation']           	= FALSE;
$config['manual_activation']          	= FALSE;
$config['remember_users']             	= TRUE;
$config['user_expire']                	= 86500;
$config['user_extend_on_login']       	= FALSE;
$config['track_login_attempts']       	= TRUE;
$config['track_login_ip_address']     	= TRUE;
$config['maximum_login_attempts']     	= 3;
$config['lockout_time']               	= 600;
$config['forgot_password_expiration'] 	= 1800;
$config['recheck_timer']              	= 0;

$config['session_hash'] 				= '6583d6c4f205998ecacc9f51b68a2a2e44ea0006';
$config['remember_cookie_name'] 		= 'remember_code';
$config['use_ci_email'] = FALSE;
$config['email_config'] = [
	'mailtype' => 'html',
];

$config['email_templates'] 				= 'auth/email/';
$config['email_activate'] 				= 'activate.tpl.php';
$config['email_forgot_password'] 		= 'forgot_password.tpl.php';
$config['delimiters_source']       		= 'config';
$config['message_start_delimiter'] 		= '<p>';
$config['message_end_delimiter']   		= '</p>';
$config['error_start_delimiter']   		= '<p>';
$config['error_end_delimiter']     		= '</p>';
