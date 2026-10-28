<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$autoload['packages'] = array();

/*
 * 'template' is here for the super-admin panel and is the odd one out:
 * every other controller in this project loads it by hand
 * ($this->load->library('template')), and doing that in six panel
 * controllers means a sixth constructor to forget.
 *
 * The failure it causes is the worst kind this codebase has. A missing
 * library is not a blank partial, it is
 * "Call to a member function sadmin() on null" — a PHP Error, which CI3
 * routes to _exception_handler(), which sets HTTP 500 and exits WITHOUT
 * a body because index.php sets display_errors=0 even in development and
 * log_threshold is 0. Every panel page answered "500, zero bytes" and
 * nothing in application/logs/ said why.
 *
 * Template is a bag of static methods with no constructor and no side
 * effects, so autoloading it costs one class include per request.
 */
$autoload['libraries'] = array('database', 'email', 'session', 'curl', 'ion_auth', 'tenant_resolver', 'tenant_media', 'template');
$autoload['drivers'] = array();
// 'audit' and 'sadmin' are autoloaded rather than loaded per controller
// on purpose: a call site that forgets a $this->load->helper('audit') is a
// silently missing audit row, and a call site that forgets
// $this->load->helper('sadmin') is a super-admin panel with no auth gate.
// Neither is worth a line of boilerplate in six constructors.
//
// 'platform' is the same argument one level over. platform_contact() reads
// the product site's own contact channels out of `satting_infos`, and its
// three consumers are all VIEWS — saas/partials/navbar.php,
// saas/partials/support_modal.php and saas/pages/contact.php. A view cannot
// load a helper, so there is no call site to forget; it can only be
// autoloaded or it is simply absent, and "absent" here means every contact
// number on the public site silently reverts to a hardcoded fallback.
$autoload['helper'] = array('url', 'file', 'tenant', 'text', 'media', 'icon', 'audit', 'sadmin', 'platform', 'mail', 'approval', 'support_event');
$autoload['config'] = array();
$autoload['language'] = array();
$autoload['model'] = array();
