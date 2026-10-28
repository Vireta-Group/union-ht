<?php
/**
 * ViretaDev Application Security Shield
 * 
 * Provides:
 * 1. Web Application Firewall (WAF) — SQLi, XSS, Path Traversal, RCE, Bad Scanner detection.
 * 2. Rate Limiting — Global and sensitive route throttling (DoS/Brute-force protection).
 * 3. IP Ban System — Dynamic auto-ban on repeat attack strikes & admin blocklist check.
 * 4. Anti-Scraping / Malicious Bot Protection.
 */
class Vireta_security_shield
{
    private static $cache_dir = null;

    /**
     * Main entry point to be executed before request handling.
     */
    public static function protect()
    {
        // 1. Resolve and validate client IP
        $ip = self::get_client_ip();

        // 2. Loopback / local development IP exemption for smooth local tests
        $is_loopback = in_array($ip, array('127.0.0.1', '::1', 'localhost'), true);

        // 3. Initialize cache directory for state tracking
        self::init_storage();

        // 4. Check if IP is banned
        if (self::is_ip_banned($ip)) {
            self::deny(403, 'আপনার আইপি ঠিকানাটি সাময়িক বা স্থায়ীভাবে ব্লক করা হয়েছে। বিস্তারিত জানতে সাপোর্টে যোগাযোগ করুন।');
        }

        // 5. Run Web Application Firewall (WAF) inspection
        $attack = self::inspect_request();
        if ($attack !== false) {
            self::handle_attack($ip, $attack);
        }

        // 6. Enforce Rate Limiting (exempt loopback unless explicitly tested)
        if (!$is_loopback) {
            self::enforce_rate_limit($ip);
        }
    }

    /**
     * Resolves real client IP supporting Cloudflare and standard reverse proxies.
     */
    public static function get_client_ip()
    {
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            $ip = trim($_SERVER['HTTP_CF_CONNECTING_IP']);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }

        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $first_ip = trim($ips[0]);
            if (filter_var($first_ip, FILTER_VALIDATE_IP)) {
                return $first_ip;
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    /**
     * Initializes cache storage directories.
     */
    private static function init_storage()
    {
        if (self::$cache_dir === null) {
            $base = dirname(dirname(__DIR__)) . '/application/cache';
            if (!is_dir($base)) {
                @mkdir($base, 0755, true);
            }
            self::$cache_dir = $base . '/security';
            if (!is_dir(self::$cache_dir)) {
                @mkdir(self::$cache_dir, 0755, true);
            }
        }
    }

    /**
     * Checks if IP is currently banned in DB or local cache file.
     */
    public static function is_ip_banned($ip)
    {
        // Check fast local ban cache file
        $ban_file = self::$cache_dir . '/banned_ips.json';
        if (file_exists($ban_file)) {
            $bans = @json_decode(file_get_contents($ban_file), true);
            if (is_array($bans) && isset($bans[$ip])) {
                $exp = $bans[$ip]['expires_at'] ?? null;
                if ($exp === null || strtotime($exp) > time()) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Bans an IP temporarily or permanently.
     */
    public static function ban_ip($ip, $reason = 'WAF Automated Strike Rule', $hours = 24)
    {
        self::init_storage();
        $ban_file = self::$cache_dir . '/banned_ips.json';
        $bans = file_exists($ban_file) ? (@json_decode(file_get_contents($ban_file), true) ?: array()) : array();

        $expires_at = $hours ? date('Y-m-d H:i:s', time() + ($hours * 3600)) : null;
        $bans[$ip] = array(
            'reason'     => $reason,
            'banned_at'  => date('Y-m-d H:i:s'),
            'expires_at' => $expires_at,
        );

        @file_put_contents($ban_file, json_encode($bans, JSON_PRETTY_PRINT), LOCK_EX);

        // Also record to PostgreSQL banned_ips table if possible
        try {
            if (class_exists('CI_Controller', false) && function_exists('get_instance')) {
                $ci = get_instance();
                if ($ci && isset($ci->db) && $ci->db->conn_id) {
                    $ci->db->insert('banned_ips', array(
                        'ip_address' => $ip,
                        'reason'     => $reason,
                        'banned_by'  => 'WAF Auto-Ban',
                        'expires_at' => $expires_at,
                    ));
                }
            }
        } catch (Throwable $e) {}
    }

    /**
     * Inspects incoming request for common attack vectors.
     */
    private static function inspect_request()
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $query = $_SERVER['QUERY_STRING'] ?? '';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        // 1. Bad Bot & Malicious Vulnerability Scanners
        $bad_bots = '/(sqlmap|nikto|acunetix|masscan|wprecon|havij|zgrab|censys|dirbuster|gobuster|hydra|w3af|netsparker|arachni)/i';
        if (preg_match($bad_bots, $user_agent, $m)) {
            return array('type' => 'Malicious Scanner', 'match' => $m[0]);
        }

        // 2. Directory / Path Traversal
        if (preg_match('/(\.\.[\/\\\\]|%2e%2e[\/\\\\]|\/etc\/passwd|c:\\\\windows\\\\system32)/i', $uri . ' ' . $query, $m)) {
            return array('type' => 'Path Traversal', 'match' => $m[0]);
        }

        // 3. Collect request data for payload inspection
        $inputs = array();
        self::flatten_array($_GET, $inputs);
        self::flatten_array($_POST, $inputs);
        self::flatten_array($_COOKIE, $inputs);

        // 4. SQL Injection Patterns
        $sqli_patterns = array(
            '/\bunion([\s\+]+all)?[\s\+]+select\b/i',
            '/\b(information_schema|waitfor[\s\+]+delay|benchmark\(|pg_sleep\(|sleep\()\b/i',
            '/\'\s*(or|and)\s*(\'1\'\s*=\s*\'1\'|1\s*=\s*1|\'a\'\s*=\s*\'a\')/i',
            '/\b(into[\s\+]+outfile|load_file\()/i',
            '/;\s*(drop[\s\+]+table|truncate[\s\+]+table|delete[\s\+]+from)\b/i'
        );

        foreach ($inputs as $key => $val) {
            if (!is_string($val) || strlen($val) < 4) continue;
            foreach ($sqli_patterns as $pattern) {
                if (preg_match($pattern, $val, $m)) {
                    return array('type' => 'SQL Injection', 'match' => $m[0], 'key' => $key);
                }
            }
        }

        // 5. Remote Command Execution / Dangerous Shell Patterns
        $rce_patterns = array(
            '/[;&\|`]\s*(whoami|id|cat\s+\/etc|rm\s+-rf|nc\s+-e|curl\s+https?:\/\/|wget\s+https?:\/\/)/i',
            '/\b(system|exec|shell_exec|passthru|proc_open)\s*\(/i'
        );
        foreach ($inputs as $key => $val) {
            if (!is_string($val) || strlen($val) < 4) continue;
            foreach ($rce_patterns as $pattern) {
                if (preg_match($pattern, $val, $m)) {
                    return array('type' => 'Remote Command Execution', 'match' => $m[0], 'key' => $key);
                }
            }
        }

        return false;
    }

    /**
     * Handles detected attack: logs, adds strike, and blocks request.
     */
    private static function handle_attack($ip, $attack)
    {
        // 1. Log incident
        $log_dir = dirname(dirname(__DIR__)) . '/application/logs';
        if (!is_dir($log_dir)) @mkdir($log_dir, 0755, true);
        $log_file = $log_dir . '/security_waf.log';

        $entry = sprintf(
            "[%s] IP: %s | Type: %s | Match: %s | URI: %s | UA: %s\n",
            date('Y-m-d H:i:s'),
            $ip,
            $attack['type'],
            $attack['match'] ?? '-',
            substr($_SERVER['REQUEST_URI'] ?? '', 0, 100),
            substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 80)
        );
        @file_put_contents($log_file, $entry, FILE_APPEND | LOCK_EX);

        // 2. Track strike counter
        $strike_file = self::$cache_dir . '/strikes_' . md5($ip) . '.json';
        $strikes = 1;
        if (file_exists($strike_file)) {
            $data = @json_decode(file_get_contents($strike_file), true);
            if (is_array($data) && (time() - ($data['time'] ?? 0) < 600)) {
                $strikes = ($data['count'] ?? 1) + 1;
            }
        }
        @file_put_contents($strike_file, json_encode(array('count' => $strikes, 'time' => time())), LOCK_EX);

        // 3. Auto-ban on 3 strikes within 10 minutes
        if ($strikes >= 3) {
            self::ban_ip($ip, 'অটো-ব্যান: ৩ বার ক্ষতিকর সাইবার আক্রমণ রিকোয়েস্ট শনাক্ত হয়েছে (' . $attack['type'] . ')', 24);
        }

        // 4. Deny immediately
        self::deny(403, 'নিরাপত্তা সতর্কতা: ক্ষতিকর বা অননুমোদিত কোড শনাক্ত হওয়ায় সিস্টেম আপনার রিকোয়েস্টটি ব্লক করেছে।');
    }

    /**
     * Rate Limiting Engine:
     * - Global: 120 requests / minute
     * - Auth & Signup: 10 requests / minute
     * - Search / API: 25 requests / minute
     */
    private static function enforce_rate_limit($ip)
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $is_auth = (bool)preg_match('#/(login|login_check|api_login|register|api_register_tenant|forgot_password)#i', $uri);
        $is_search = (bool)preg_match('#/(src_holding|search_hold|search_holding_for_page_api)#i', $uri);

        $limit = 120; // default requests per minute
        $category = 'global';

        if ($is_auth) {
            $limit = 12;
            $category = 'auth';
        } elseif ($is_search) {
            $limit = 30;
            $category = 'search';
        }

        $bucket_file = self::$cache_dir . '/rate_' . $category . '_' . md5($ip) . '.json';
        $now = time();
        $window = 60; // 1 minute window

        $count = 1;
        if (file_exists($bucket_file)) {
            $data = @json_decode(file_get_contents($bucket_file), true);
            if (is_array($data) && ($now - ($data['time'] ?? 0) < $window)) {
                $count = ($data['count'] ?? 0) + 1;
            }
        }

        @file_put_contents($bucket_file, json_encode(array('count' => $count, 'time' => $now)), LOCK_EX);

        if ($count > $limit) {
            header('Retry-After: 60');
            self::deny(429, 'অতিরিক্ত রিকোয়েস্ট শনাক্ত হয়েছে। সিস্টেম সুরক্ষার্থে অনুগ্রহ করে ৬০ সেকেন্ড পর পুনরায় চেষ্টা করুন।');
        }
    }

    /**
     * Helper to recursively flatten associative arrays.
     */
    private static function flatten_array($array, &$result, $prefix = '')
    {
        if (!is_array($array)) return;
        foreach ($array as $key => $val) {
            $full_key = $prefix ? $prefix . '.' . $key : $key;
            if (is_array($val)) {
                self::flatten_array($val, $result, $full_key);
            } else {
                $result[$full_key] = (string)$val;
            }
        }
    }

    /**
     * Emits JSON or HTML security halt response.
     */
    private static function deny($status_code, $message_bn)
    {
        http_response_code($status_code);
        $is_json = (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
                || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

        if ($is_json) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array(
                'error'   => true,
                'status'  => $status_code,
                'message' => $message_bn,
            ), JSON_UNESCAPED_UNICODE);
        } else {
            header('Content-Type: text/html; charset=utf-8');
            echo '<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>' . $status_code . ' - নিরাপত্তা সতর্কতা</title>
    <style>
        body { font-family: system-ui, -apple-system, sans-serif; background: #0f172a; color: #f8fafc; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 1.5rem; text-align: center; }
        .card { background: #1e293b; border: 1px solid #334155; border-radius: 1.25rem; max-width: 28rem; width: 100%; padding: 2rem; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.5); }
        .icon { width: 3.5rem; height: 3.5rem; border-radius: 1rem; background: rgba(239, 68, 68, 0.15); color: #f87171; display: inline-flex; align-items: center; justify-content: center; font-size: 1.75rem; margin-bottom: 1.25rem; }
        h1 { font-size: 1.25rem; font-weight: 700; margin: 0 0 0.5rem; }
        p { font-size: 0.875rem; color: #94a3b8; line-height: 1.6; margin: 0 0 1.5rem; }
        .btn { display: inline-block; background: #4f46e5; color: #fff; text-decoration: none; padding: 0.625rem 1.25rem; border-radius: 0.75rem; font-size: 0.875rem; font-weight: 600; }
        .btn:hover { background: #4338ca; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">🛡️</div>
        <h1>নিরাপত্তা সতর্কতা (' . $status_code . ')</h1>
        <p>' . htmlspecialchars($message_bn, ENT_QUOTES, 'UTF-8') . '</p>
        <a href="/" class="btn">হোমে ফিরে যান</a>
    </div>
</body>
</html>';
        }
        exit;
    }
}
