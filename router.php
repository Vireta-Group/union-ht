<?php
/**
 * Local development router for PHP built-in web server.
 * Usage: php -S localhost:8080 router.php
 *
 * Note: the built-in server does not read .htaccess, so the deny rules
 * from /.htaccess are mirrored here. Without this, any file in the
 * project root -- including .env -- would be served as-is, exactly the
 * hole the .htaccess rules close on Apache.
 */

// Strip server tech fingerprint and enforce basic security headers
@ini_set('expose_php', '0');
if (function_exists('header_remove')) {
    header_remove('X-Powered-By');
}
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

/*
 * DEV REQUEST LOG
 * ---------------
 * `php -S` prints one line per request (method, path, status) but NOT the Host
 * header. In this app the Host *is* the tenant: dhum.local, localhost and
 * foo.local take different branches of Tenant_resolver and render different
 * data. A log without the host cannot answer "which tenant did that hit?", and
 * tenant hosts are exactly what you cannot type into a browser's address bar
 * on this machine — /etc/hosts needs sudo, so every check goes through
 * curl -H "Host: ...".
 *
 * PHP's own log cannot cover this either: config.php sets log_threshold = 0,
 * which is deliberate (a blank page is debugged from the response body, not a
 * log) but it also means there is no per-request record at all.
 *
 * SAFE BY CONSTRUCTION, not by configuration:
 *   - dev_log() returns immediately unless the SAPI is cli-server, so this
 *     only ever runs under `php -S`. Under Apache/nginx/PHP-FPM router.php is
 *     not the entry point, and the branch is skipped anyway. There is no env
 *     var to forget to unset and no production path that can reach it.
 *   - the file is a .log, which the blocked_ext rule below refuses to serve,
 *     so it cannot be fetched over HTTP.
 *
 * Called from all three exit points: the two denies and the normal path, so a
 * 404 from the deny rules is in the log just like a 404 from CI3.
 */
function dev_log($uri, $note = '')
{
    if (PHP_SAPI !== 'cli-server') {
        return;
    }

    $file = __DIR__ . '/application/logs/dev-request.log';
    $dir  = dirname($file);
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }

    // HTTP_HOST carries the port when the browser sent one (dhum.localhost:8080).
    // Strip it so the host column matches what Tenant_resolver sees — it does
    // its own stripping, but the log is for reading and the port is noise.
    $host = $_SERVER['HTTP_HOST'] ?? '-';
    if (strpos($host, ':') !== FALSE) {
        $host = preg_replace('/:\d+$/', '', $host);
    }

    @file_put_contents($file, sprintf(
        "[%s] %-24s %-6s %-40s %3d  ip=%s%s  ua=%s\n",
        date('Y-m-d H:i:s T'),
        $host,
        $_SERVER['REQUEST_METHOD'] ?? '-',
        substr($uri, 0, 40),
        http_response_code(),
        $_SERVER['REMOTE_ADDR']   ?? '-',
        $note ? '  ' . $note : '',
        substr($_SERVER['HTTP_USER_AGENT'] ?? '-', 0, 50)
    ), FILE_APPEND | LOCK_EX);
}

/**
 * DEV API LOG — Terminal console inspection
 * Prints API/POST/AJAX requests with host, status, payload, and response
 * directly to the terminal where `php -S` is running.
 */
function dev_api_log($uri, $resp = null)
{
    if (PHP_SAPI !== 'cli-server') {
        return;
    }

    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    $is_post = ($method === 'POST');
    $is_json = (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false)
            || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

    // Only log POST, AJAX, or JSON API requests so full HTML pages don't clutter the terminal
    if (!$is_post && !$is_ajax && !$is_json) {
        return;
    }

    $host = $_SERVER['HTTP_HOST'] ?? '-';
    if (strpos($host, ':') !== false) {
        $host = preg_replace('/:\d+$/', '', $host);
    }

    $status = http_response_code();
    $status_color = ($status >= 200 && $status < 300) ? "\033[1;32m" : (($status >= 400) ? "\033[1;31m" : "\033[1;33m");
    $reset   = "\033[0m";
    $bold    = "\033[1m";
    $cyan    = "\033[1;36m";
    $yellow  = "\033[1;33m";
    $magenta = "\033[1;35m";
    $red     = "\033[1;31m";
    $dim     = "\033[2m";

    // Request Payload
    $payload_str = '';
    if (!empty($_POST)) {
        $safe_post = $_POST;
        if (isset($safe_post['password'])) $safe_post['password'] = '******';
        $payload_str = json_encode($safe_post, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    } else {
        $raw = file_get_contents('php://input');
        if (!empty($raw)) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                if (isset($decoded['password'])) $decoded['password'] = '******';
                $payload_str = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            } else {
                $payload_str = (strlen($raw) > 500) ? substr($raw, 0, 500) . '...' : $raw;
            }
        }
    }

    // Response preview
    $resp_str = '';
    if ($resp !== null && $resp !== '') {
        $resp_trim = trim($resp);
        $json_resp = json_decode($resp_trim, true);
        if (is_array($json_resp)) {
            $resp_str = json_encode($json_resp, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } elseif (strlen($resp_trim) > 500) {
            if (preg_match('/<title>(.*?)<\/title>/si', $resp_trim, $m)) {
                $resp_str = "[HTML Title: " . trim(strip_tags($m[1])) . "]\n" . substr(strip_tags($resp_trim), 0, 300) . "...";
            } else {
                $resp_str = substr($resp_trim, 0, 500) . '... [truncated]';
            }
        } else {
            $resp_str = $resp_trim;
        }
    } else {
        $resp_str = '(empty)';
    }

    $out  = "\n" . $cyan . "┌── [API] " . $bold . $method . " " . $uri . $reset . " " . $dim . "(Host: " . $host . ")" . $reset . "\n";
    $out .= $cyan . "│ " . $dim . "Status:   " . $reset . $status_color . $status . $reset . "\n";
    if ($payload_str !== '') {
        $out .= $cyan . "│ " . $yellow . "Payload:  " . $reset . "\n" . preg_replace('/^/m', '│   ', $payload_str) . "\n";
    }
    $out .= $cyan . "│ " . $magenta . "Response: " . $reset . "\n" . preg_replace('/^/m', '│   ', $resp_str) . "\n";

    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR], true)) {
        $out .= $cyan . "│ " . $red . "Error:    " . $err['message'] . " in " . $err['file'] . ":" . $err['line'] . $reset . "\n";
    }

    $out .= $cyan . "└──────────────────────────────────────────────" . $reset . "\n";

    @file_put_contents('php://stderr', $out);
}

// 1. Never serve dotfiles (.env, .git, .htpasswd, ...) or sensitive file types
$blocked_ext = '/\.(sql|dump|bak|old|orig|save|swp|log|ini|conf|env|sh|tar|gz|zip|md|lock)$/i';
$is_dotfile  = (bool) preg_match('#(^|/)\.#', $uri);

if ($is_dotfile || preg_match($blocked_ext, $uri)) {
    http_response_code(404);
    header('Content-Type: text/plain');
    echo "404 Not Found\n";
    dev_log($uri, 'deny=dotfile-or-blocked-ext');
    return true;
}

// 2. Block internal directories completely — never leak application, system, vendor, docs, migrations, deploy, resources
if (preg_match('#^/(application|system|vendor|docs|migrations|deploy|resources)(/|$)#i', $uri)) {
    http_response_code(404);
    header('Content-Type: text/plain');
    echo "404 Not Found\n";
    dev_log($uri, 'deny=internal-directory');
    return true;
}

// 3. Block root metadata, repository rules, and project configuration files
if (preg_match('#^/(AGENTS\.md|README\.md|LICENSE|router\.php|dev\.sh|composer\.(json|lock)|package(-lock)?\.json)$#i', $uri)) {
    http_response_code(404);
    header('Content-Type: text/plain');
    echo "404 Not Found\n";
    dev_log($uri, 'deny=meta-file');
    return true;
}

// 4. uploads/ holds tenant images only — mirror uploads/.htaccess, which the
// built-in server never reads. A stored .php here would otherwise execute.
if (preg_match('#^/uploads/#', $uri)
    && preg_match('/\.(php[0-9]?|phtml|pht|phar|cgi|pl|py|sh|shtml|html?)$/i', $uri)) {
    http_response_code(403);
    header('Content-Type: text/plain');
    echo "403 Forbidden\n";
    dev_log($uri, 'deny=script-in-uploads');
    return true;
}

// 5. Prevent directory browsing/listing for any directory
if ($uri !== '/' && is_dir(__DIR__ . $uri)) {
    http_response_code(404);
    header('Content-Type: text/plain');
    echo "404 Not Found\n";
    dev_log($uri, 'deny=directory-browsing');
    return true;
}

// 6. Serve allowed static assets directly (inc/ and uploads/ only)
if ($uri !== '/' && preg_match('#^/(inc|uploads)/#', $uri) && file_exists(__DIR__ . $uri)) {
    return false;
}

// Forward all other requests to CodeIgniter entry point
$is_api_candidate = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'
    || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
    || (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false)
    || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

if ($is_api_candidate) {
    ob_start();
}

$shutdown_logged = false;
register_shutdown_function(function () use ($uri, $is_api_candidate, &$shutdown_logged) {
    if ($shutdown_logged) {
        return;
    }
    $shutdown_logged = true;
    $resp = null;
    if ($is_api_candidate && ob_get_level() > 0) {
        $resp = ob_get_contents();
    }
    dev_log($uri);
    if ($is_api_candidate) {
        dev_api_log($uri, $resp);
    }
});

require_once __DIR__ . '/index.php';
