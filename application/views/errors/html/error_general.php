<?php
defined('BASEPATH') OR exit('No direct script access needed');

/*
 * One of the five names CI3 dispatches an error to. It renders nothing itself —
 * errors/html/_ht_error.php does, branching on http_response_code() so that 403,
 * 404, a PHP fatal, an uncaught exception and a DB failure all come out looking
 * the same instead of four different shades of CodeIgniter grey.
 *
 * CI3 always puts $heading and $message in scope before including this file.
 */
include __DIR__ . '/_ht_error.php';
