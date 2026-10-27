<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| Credentials are read from the .env file in the project root (see the
| loader in config.php). There is deliberately no hardcoded password
| here any more -- the previous one was committed to this file and is
| now rotated. If .env is missing the connection fails loudly with an
| empty host/user rather than silently trying a stale credential.
*/

$active_group = 'default';
$query_builder = TRUE;

/*
| CI3's PDO driver derives its subdriver by parsing the DSN string
| (see system/database/drivers/pdo/pdo_driver.php), so the DSN must be
| supplied even when the individual parts come from .env.
*/
$db_host = env('DB_HOST', 'localhost');
$db_port = env('DB_PORT', '5432');
$db_name = env('DB_NAME', '');

$db['default'] = array(
	'dsn'	=> 'pgsql:host=' . $db_host . ';port=' . $db_port . ';dbname=' . $db_name . ';',
	'hostname' => $db_host,
	'port'     => $db_port,
	'database' => $db_name,
	'username' => env('DB_USER', ''),
	'password' => env('DB_PASSWORD', ''),
	'dbdriver' => 'pdo',
	'dbprefix' => '',
	'pconnect' => FALSE,
	'db_debug' => (ENVIRONMENT !== 'production'),
	'cache_on' => FALSE,
	'cachedir' => '',
	'char_set' => 'utf8',
	'dbcollat' => 'utf8_general_ci',
	'swap_pre' => '',
	'encrypt' => FALSE,
	'compress' => FALSE,
	'stricton' => FALSE,
	'failover' => array(),
	'save_queries' => TRUE
);
