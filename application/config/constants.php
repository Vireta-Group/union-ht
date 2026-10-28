<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// ---------------------------------------------------------------------------
// TENANT RESOLUTION NO LONGER LIVES HERE
// ---------------------------------------------------------------------------
// This file used to hardcode a single institute for the entire install:
//
//     define('union_code', '1');
//
// Every query in the app compared that one literal, so the whole system was
// structurally single-tenant: 46 call sites across 6 files plus 1 in
// Ion_auth_model::set_session().
//
// That constant is GONE. There is no hardcoded tenant id anywhere in this
// application now. The current tenant is resolved per request by
// application/libraries/Tenant_resolver.php and read with tenant_code()
// (application/helpers/tenant_helper.php) or $this->tenant_resolver->id().
//
// Do not reintroduce a tenant constant in this file. If a query needs the
// current tenant, call tenant_code().
//
// ('union_code' survives only as a SESSION KEY NAME in Ion_auth_model and in
//  config/config.php's $config['tenant']['session_key'] — that is a key, not
//  a tenant id.)
// ---------------------------------------------------------------------------


defined('SHOW_DEBUG_BACKTRACE') OR define('SHOW_DEBUG_BACKTRACE', TRUE);

defined('FILE_READ_MODE')  OR define('FILE_READ_MODE', 0644);
defined('FILE_WRITE_MODE') OR define('FILE_WRITE_MODE', 0666);
defined('DIR_READ_MODE')   OR define('DIR_READ_MODE', 0755);
defined('DIR_WRITE_MODE')  OR define('DIR_WRITE_MODE', 0755);

defined('FOPEN_READ')                           OR define('FOPEN_READ', 'rb');
defined('FOPEN_READ_WRITE')                     OR define('FOPEN_READ_WRITE', 'r+b');
defined('FOPEN_WRITE_CREATE_DESTRUCTIVE')       OR define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb'); 
defined('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE')  OR define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); 
defined('FOPEN_WRITE_CREATE')                   OR define('FOPEN_WRITE_CREATE', 'ab');
defined('FOPEN_READ_WRITE_CREATE')              OR define('FOPEN_READ_WRITE_CREATE', 'a+b');
defined('FOPEN_WRITE_CREATE_STRICT')            OR define('FOPEN_WRITE_CREATE_STRICT', 'xb');
defined('FOPEN_READ_WRITE_CREATE_STRICT')       OR define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');

defined('EXIT_SUCCESS')        OR define('EXIT_SUCCESS', 0); 
defined('EXIT_ERROR')          OR define('EXIT_ERROR', 1); 
defined('EXIT_CONFIG')         OR define('EXIT_CONFIG', 3); 
defined('EXIT_UNKNOWN_FILE')   OR define('EXIT_UNKNOWN_FILE', 4); 
defined('EXIT_UNKNOWN_CLASS')  OR define('EXIT_UNKNOWN_CLASS', 5);
defined('EXIT_UNKNOWN_METHOD') OR define('EXIT_UNKNOWN_METHOD', 6);
defined('EXIT_USER_INPUT')     OR define('EXIT_USER_INPUT', 7);
defined('EXIT_DATABASE')       OR define('EXIT_DATABASE', 8);
defined('EXIT__AUTO_MIN')      OR define('EXIT__AUTO_MIN', 9);
defined('EXIT__AUTO_MAX')      OR define('EXIT__AUTO_MAX', 125);
