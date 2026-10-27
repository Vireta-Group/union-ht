<?php
/**
 * Production Security Guard View Partial
 * -----------------------------------------------------------------------------
 * Enforces production-only developer inspection blocking, right-click disabling,
 * keyboard shortcut blocking (F12, Ctrl+Shift+I/J/C, Ctrl+U), console method
 * suppression, and security warning banner.
 *
 * Rules:
 * - Active ONLY when APP_ENV=production or ENVIRONMENT=production.
 * - In development (APP_ENV=development), it remains disabled so developers
 *   and admins can inspect and debug freely.
 * - Can be test-driven in development with ?test_security=1.
 * -----------------------------------------------------------------------------
 */
$CI =& get_instance();
$is_dev  = (bool) $CI->config->item('is_development');
$is_prod = (ENVIRONMENT === 'production') || ! $is_dev || (isset($_GET['test_security']) && $_GET['test_security'] === '1');

if ($is_prod): ?>
<!-- Production Security Guard -->
<script src="inc/assets/js/prod-security.js"></script>
<?php endif; ?>
