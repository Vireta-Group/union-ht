<?php
defined('BASEPATH') OR exit('No direct script access needed');

/**
 * The shared error page. Every errors/html/error_*.php is a three-line include of
 * this file, so 403, 404, a PHP fatal, an uncaught exception and a DB failure
 * all look the same.
 *
 * CI3 routes them to DIFFERENT template names — show_error() to
 * error_general.php, show_404() to error_404.php, the fatal handler to
 * error_php.php, and so on — and it only ever sets the HTTP status header, never
 * a variable saying which one it is. So this file branches on
 * http_response_code() instead, which every one of those paths has already set by
 * the time the template is included.
 *
 * REBUILT 2026-09-29. What it was: CodeIgniter 3's shipped default — English
 * ("404 Page Not Found", "The page you requested was not found."), 13px
 * Helvetica, a #E13300 ::selection rule, no tenant, no branding. A parishad's
 * chairman who landed on it had no way to tell a wrong address from a refused
 * login, and nothing on the page looked like the site he was on.
 *
 * WHY IT IS SELF-CONTAINED
 * An error page must render when the rest of the app cannot. It cannot assume
 * $ht_shell, the tenant shell, the Tailwind build, or that a resolver run
 * succeeded — an error is very often why it did not. So: inline CSS, no build
 * step, the icon font is inlined as an SVG rather than linked, and the tenant
 * lookup is wrapped so a failure degrades to "no logo" instead of a second
 * error. Only base_url() is assumed, which CI3 has available here.
 *
 * The two variables CI3 hands over are $heading and $message; $message already
 * has <p> wrapped around it.
 *
 * Motion is opt-out: everything animated is inside a
 * @media (prefers-reduced-motion: no-preference) block, and there is nothing
 * that moves for a reader who asked for stillness — the page is complete
 * without it.
 */

$heading = isset($heading) ? $heading : '';
$message = isset($message) ? $message : '';

$code = 0;
if (function_exists('http_response_code')) {
    $code = (int) http_response_code();
}
if ($code < 400 || $code > 599) {
    $code = 500;
}

/*
 * Tenant branding, defensively. tenant_row() goes through Tenant_resolver, which
 * is exactly the thing that may have failed. Anything thrown or missing here
 * just means the page shows no logo.
 */
$ht_name = '';
$ht_logo = '';
try {
    if (function_exists('tenant_row')) {
        $row = tenant_row();
        if ($row) {
            $ht_name = (string) ($row->ins_name ?? '');
            $path    = (string) ($row->ins_logo_path ?? '');
            if ($path !== '' && function_exists('media_url')) {
                $ht_logo = media_url($path, 'image/png');
            }
        }
    }
} catch (Throwable $e) {
    $ht_name = '';
    $ht_logo = '';
}

$base = function_exists('base_url') ? base_url() : '/';

/*
 * Per-status content. $message from the caller always wins when it carries real
 * text — the 403 in enforce_tenant_scope() has a full Bangla explanation, and
 * that sentence is the point of the page. The $why below is the *extra* context:
 * what this class of failure means, and what to do next.
 */
switch ($code) {
	case 403:
		$ht_title   = 'অনুমোদনহীন প্রবেশাধিকার';
		$ht_lead    = 'এই তথ্যটি আপনার দেখার অধিকারের মধ্যে পড়ে না';
		$ht_why     = 'প্রতিটি ইউনিয়ন পরিষদের তথ্য আলাদা আলাদা রক্ষিত। আপনি যে পরিষদের '
			. 'অ্যাডমিন, সেই পরিষদের তথ্য আপনি দেখতে ও পরিবর্তন করতে পারবেন — অন্য কোনো '
			. 'পরিষদের তথ্য নয়।';
		$ht_tip     = 'আপনি যদি ভুল পরিষদের সাইটে থাকেন, তাহলে নিজের পরিষদের ঠিকানায় গিয়ে '
			. 'লগইন করুন।';
		$ht_alert   = 'এই অনুরোধটি প্রত্যাখ্যান করা হয়েছে এবং সার্ভারে লিখা হয়েছে।';
		$ht_icon    = 'lock';
		$ht_actions = array(
			array('label' => 'নিজের পরিষদে লগইন', 'href' => $base . 'login', 'primary' => true),
			array('label' => 'হোমে ফিরে যান',       'href' => $base,           'primary' => false),
		);
		break;

	case 404:
		$ht_title   = 'পাতাটি খুঁজে পাওয়া যায়নি';
		$ht_lead    = 'আপনি যে ঠিকানায় যেতে চাইছেন সেটি এখানে নেই';
		$ht_why     = 'হয়তো ঠিকানায় ভুল লেখা হয়েছে, বা পাতাটি সরিয়ে ফেলা হয়েছে। '
			. 'লিংকটি আবার চেষ্টা করলে সমস্যাটি থাকলে জানাবেন।';
		$ht_tip     = 'উপরের ঠিকানা বারের লিংকটি কপি করে সরাসরি খুঁজে দেখুন।';
		$ht_alert   = '';
		$ht_icon    = 'compass';
		$ht_actions = array(
			array('label' => 'হোমে ফিরে যান', 'href' => $base, 'primary' => true),
		);
		break;

	case 401:
		$ht_title   = 'লগইন প্রয়োজন';
		$ht_lead    = 'এই পাতাটি দেখতে আপনাকে লগইন করতে হবে';
		$ht_why     = 'আপনার সেশন শেষ হয়ে গেছে অথবা কখনো শুরু হয়নি।';
		$ht_tip     = 'লগইন করলে যে পাতায় যেতে চাচ্ছিলেন সেখানেই ফিরে যাবেন।';
		$ht_alert   = '';
		$ht_icon    = 'sign-in';
		$ht_actions = array(
			array('label' => 'লগইন করুন', 'href' => $base . 'login', 'primary' => true),
		);
		break;

	default:
		$ht_title   = 'কিছু একটা ভুল হয়েছে';
		$ht_lead    = 'সার্ভার এই পাতাটি দেখাতে পারছে না';
		$ht_why     = 'সমস্যাটি নিজে থেকে ঠিক হয়ে যেতে পারে। না হলে অনুগ্রহ করে আবার চেষ্টা '
			. 'করুন, অথবা পরিষদের প্রশাসককে জানান।';
		$ht_tip     = 'আপনার কাজের যে কোনো তথ্য সংরক্ষিত হয়নি — নিরাপদে আবার চেষ্টা করতে পারেন।';
		$ht_alert   = 'এই ঘটনাটি সার্ভারের লগে লিখা হয়েছে।';
		$ht_icon    = 'warning';
		$ht_actions = array(
			array('label' => 'হোমে ফিরে যান', 'href' => $base, 'primary' => true),
		);
		break;
}

$e = static function ($s) {
	return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
};
?>
<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $e($ht_title) ?> — <?= $e($ht_name ?: 'ইউনিয়ন পরিষদ') ?></title>
<base href="<?= $e($base) ?>">
<link rel="stylesheet" href="inc/assets/vendor/fonts/adorsho-lipi.css">
<link rel="stylesheet" href="inc/assets/vendor/fonts/hind-siliguri.css">
<style>
/* ── tokens ───────────────────────────────────────────────────────────────
   Copied from resources/css/tokens.css rather than linked: portal.css is a
   build artefact, and this page has to render when the build is stale, when the
   tenant is unresolved, and when the failure is in the shell itself. If the
   palette ever changes, change it here too — that is the price of a page that
   cannot depend on anything. */
:root{
  --indigo-50:#eef2ff; --indigo-100:#e0e7ff; --indigo-200:#c7d2fe;
  --indigo-500:#6366f1; --indigo-600:#4f46e5; --indigo-700:#4338ca;
  --indigo-900:#312e81;
  --rose-50:#fff1f2; --rose-200:#fecdd3; --rose-500:#f43f5e; --rose-600:#e11d48; --rose-700:#be123c;
  --amber-50:#fffbeb; --amber-200:#fde68a; --amber-600:#d97706; --amber-700:#b45309;
  --neutral-50:#fafaf9; --neutral-100:#f5f5f4; --neutral-200:#e7e5e4;
  --neutral-400:#a8a29e; --neutral-500:#78716c; --neutral-600:#57534e;
  --neutral-700:#44403c; --neutral-900:#1c1917;
  --radius-lg:.5rem; --radius-xl:.75rem; --radius-2xl:1rem;
}

*{box-sizing:border-box}
html,body{height:100%}
body{
  margin:0;
  font-family:'AdarshaLipi', 'Adarsha Lipi', 'AdorshoLipi', 'Adorsho Lipi','Hind Siliguri',system-ui,-apple-system,'Segoe UI',sans-serif;
  background:var(--neutral-50);
  color:var(--neutral-900);
  -webkit-font-smoothing:antialiased;
  display:flex; align-items:center; justify-content:center;
  padding:2rem 1rem;
}
/* one very slow, very soft wash behind everything — enough to feel alive,
   not enough to notice while reading */
body::before{
  content:''; position:fixed; inset:-30%;
  background:
    radial-gradient(38rem 38rem at 22% 18%, var(--indigo-100), transparent 62%),
    radial-gradient(34rem 34rem at 82% 78%, var(--rose-50),   transparent 60%);
  opacity:.75; pointer-events:none;
}

.wrap{position:relative; width:100%; max-width:34rem; text-align:center}

/* ── the mark ─────────────────────────────────────────────────────────── */
.mark{
  position:relative; width:6.5rem; height:6.5rem; margin:0 auto 1.75rem;
  display:grid; place-items:center;
  border-radius:var(--radius-2xl);
  background:#fff; border:1px solid var(--neutral-200);
  box-shadow:0 1px 2px rgba(28,25,23,.06), 0 12px 32px -12px rgba(28,25,23,.18);
}
.mark svg{width:2.75rem; height:2.75rem; color:var(--indigo-600)}
/* a ring that expands and fades — the only continuous motion on the page */
.mark::after{
  content:''; position:absolute; inset:-1px; border-radius:inherit;
  border:1.5px solid var(--indigo-200); opacity:0;
}

.code{
  font-size:.75rem; font-weight:700; letter-spacing:.22em;
  color:var(--indigo-600); margin:0 0 .5rem;
}

h1{
  font-size:1.5rem; line-height:1.3; font-weight:700; margin:0 0 .625rem;
  color:var(--neutral-900);
}
.lead{ font-size:.9375rem; color:var(--neutral-600); margin:0 auto 1.25rem; max-width:28rem; }

.why{
  text-align:left; font-size:.875rem; line-height:1.75; color:var(--neutral-700);
  background:#fff; border:1px solid var(--neutral-200); border-left:3px solid var(--indigo-500);
  border-radius:var(--radius-lg); padding:1rem 1.125rem; margin:0 0 1rem;
}
.why p{ margin:0 0 .5rem }
.why p:last-child{ margin:0 }
.why b{ color:var(--neutral-900) }

.tip{ font-size:.8125rem; color:var(--neutral-500); margin:0 0 1.25rem }

/* the warning the owner asked for: quiet, but unmistakably a warning */
.alert{
  display:flex; gap:.625rem; align-items:flex-start; text-align:left;
  font-size:.8125rem; line-height:1.6; color:var(--amber-700);
  background:var(--amber-50); border:1px solid var(--amber-200);
  border-radius:var(--radius-lg); padding:.75rem .875rem; margin:0 0 1.5rem;
}
.alert svg{ width:1.125rem; height:1.125rem; flex:none; margin-top:.125rem; color:var(--amber-600) }

.actions{ display:flex; flex-wrap:wrap; gap:.625rem; justify-content:center }
.btn{
  display:inline-flex; align-items:center; gap:.5rem;
  font-size:.875rem; font-weight:600; text-decoration:none;
  padding:.625rem 1.125rem; border-radius:var(--radius-lg);
  border:1px solid transparent; transition:background-color .15s ease, border-color .15s ease, transform .15s ease;
}
.btn svg{ width:1rem; height:1rem }
.btn-primary{ background:var(--indigo-600); color:#fff }
.btn-primary:hover{ background:var(--indigo-700) }
.btn-ghost{ background:#fff; color:var(--neutral-700); border-color:var(--neutral-200) }
.btn-ghost:hover{ background:var(--neutral-100) }
.btn:active{ transform:translateY(1px) }
.btn:focus-visible{ outline:2px solid var(--indigo-500); outline-offset:2px }

.brand{
  display:flex; align-items:center; justify-content:center; gap:.625rem;
  margin:2rem 0 0; padding-top:1.25rem; border-top:1px solid var(--neutral-200);
  font-size:.75rem; color:var(--neutral-400);
}
.brand img{ width:1.75rem; height:1.75rem; object-fit:contain }
.brand b{ color:var(--neutral-600); font-weight:600 }

/* ── motion: only for readers who want it ─────────────────────────────── */
@media (prefers-reduced-motion: no-preference){
  .mark, .code, h1, .lead, .why, .tip, .alert, .actions, .brand{ animation:rise .55s cubic-bezier(.16,.84,.44,1) both }
  .code { animation-delay:.04s } h1{ animation-delay:.08s } .lead{ animation-delay:.14s }
  .why { animation-delay:.20s } .tip{ animation-delay:.26s }
  .alert{ animation-delay:.30s } .actions{ animation-delay:.36s } .brand{ animation-delay:.42s }
  .mark{ animation-delay:0s }
  .mark::after{ animation:ring 2.8s ease-out infinite }
  .mark svg{ animation:breathe 3.4s ease-in-out infinite }

  .btn:hover svg{ transform:translateX(2px) }
  .btn svg{ transition:transform .18s ease }

  @keyframes rise{ from{ opacity:0; transform:translateY(10px) } to{ opacity:1; transform:none } }
  @keyframes ring{ 0%{ transform:scale(1);    opacity:.55 } 70%,100%{ transform:scale(1.28); opacity:0 } }
  @keyframes breathe{ 0%,100%{ transform:scale(1) } 50%{ transform:scale(1.06) } }
}

@media (max-width:30rem){
  .actions{ flex-direction:column; align-items:stretch }
  .btn{ justify-content:center }
}
</style>
</head>
<body>

<main class="wrap">

    <div class="mark" aria-hidden="true">
        <?php if ($ht_icon === 'lock'): ?>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                <rect x="4" y="10.5" width="16" height="10.5" rx="2.5"/><path d="M8 10.5V7a4 4 0 0 1 8 0v3.5"/><circle cx="12" cy="15.5" r="1.2" fill="currentColor" stroke="none"/>
            </svg>
        <?php elseif ($ht_icon === 'compass'): ?>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="9"/><path d="m15.5 8.5-2 5-5 2 2-5z"/>
            </svg>
        <?php elseif ($ht_icon === 'sign-in'): ?>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 4h4a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-4"/><path d="M10 16.5 14.5 12 10 7.5"/><path d="M14.5 12H3.5"/>
            </svg>
        <?php else: ?>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                <path d="M10.3 3.9 2.4 17.5A2 2 0 0 0 4.1 20.5h15.8a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/><path d="M12 9v4.5"/><circle cx="12" cy="17" r=".9" fill="currentColor" stroke="none"/>
            </svg>
        <?php endif; ?>
    </div>

    <p class="code"><?= $e($code) ?></p>
    <h1><?= $e($ht_title) ?></h1>
    <p class="lead"><?= $e($ht_lead) ?></p>

    <div class="why">
        <?php if (trim(strip_tags($message)) !== ''): ?>
            <p><b>কী ঘটেছে:</b> <?= $message ?></p>
        <?php endif; ?>
        <p><?= $e($ht_why) ?></p>
        <?php if ($ht_tip !== ''): ?>
            <p><b>করণীয়:</b> <?= $e($ht_tip) ?></p>
        <?php endif; ?>
    </div>

    <?php if ($ht_alert !== ''): ?>
        <div class="alert" role="alert">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="9"/><path d="M12 7.5v5.5"/><circle cx="12" cy="16.3" r=".9" fill="currentColor" stroke="none"/>
            </svg>
            <span><?= $e($ht_alert) ?></span>
        </div>
    <?php endif; ?>

    <div class="actions">
        <?php foreach ($ht_actions as $a): ?>
            <a class="btn <?= $a['primary'] ? 'btn-primary' : 'btn-ghost' ?>" href="<?= $e($a['href']) ?>">
                <?php if ($a['primary']): ?>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M5 12h14"/><path d="m13 6 6 6-6 6"/>
                    </svg>
                <?php endif; ?>
                <span><?= $e($a['label']) ?></span>
            </a>
        <?php endforeach; ?>
    </div>

    <p class="brand">
        <?php if ($ht_logo !== ''): ?>
            <img src="<?= $e($ht_logo) ?>" alt="">
        <?php endif; ?>
        <span>সেবা প্রদানকারী: <b>ViretaDev</b> স্মার্ট ইউনিয়ন গভর্ন্যান্স প্ল্যাটফর্ম</span>
    </p>

</main>

</body>
</html>
