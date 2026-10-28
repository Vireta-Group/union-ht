<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
| Email Configuration for Brevo SMTP
| -------------------------------------------------------------------
| Reads credentials dynamically from .env via env() helper.
*/

$config['protocol']     = 'smtp';
$config['smtp_host']    = env('MAIL_HOST', 'smtp-relay.brevo.com');
$config['smtp_port']    = (int) env('MAIL_PORT', 587);
$config['smtp_user']    = env('MAIL_USERNAME', 'bb0bac001@smtp-brevo.com');
$config['smtp_pass']    = env('MAIL_PASSWORD', '');
$config['smtp_crypto']  = env('MAIL_ENCRYPTION', 'tls');
$config['mailtype']     = 'html';
$config['charset']      = 'utf-8';
$config['newline']      = "\r\n";
$config['crlf']         = "\r\n";
$config['wordwrap']     = TRUE;
$config['smtp_timeout'] = 10;
