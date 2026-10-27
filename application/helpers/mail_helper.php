<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Vireta Mailer Helper
 *
 * Sends emails using Brevo SMTP with seamless Brevo HTTP API fallback.
 */

if (!function_exists('send_vireta_email'))
{
    /**
     * Send an HTML email via Brevo SMTP or Brevo API fallback.
     *
     * @param string $to_email
     * @param string $subject
     * @param string $html_body
     * @param string $to_name
     * @return array ['success' => bool, 'message' => string, 'method' => string]
     */
    function send_vireta_email($to_email, $subject, $html_body, $to_name = '')
    {
        $to_email = trim($to_email);
        if (!filter_var($to_email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'অকার্যকর ইমেইল ঠিকানা।', 'method' => 'none'];
        }

        $from_address = env('MAIL_FROM_ADDRESS', 'contact.viretadev@gmail.com');
        $from_name    = env('MAIL_FROM_NAME', 'Vireta Dev');
        $api_key      = env('BREVO_API_KEY', '');

        // 1. Try CodeIgniter Email (Brevo SMTP)
        $ci =& get_instance();
        try {
            if (!isset($ci->email)) {
                $ci->load->library('email');
            }

            $ci->email->clear(TRUE);
            $ci->email->from($from_address, $from_name);
            $ci->email->to($to_email, $to_name);
            $ci->email->subject($subject);
            $ci->email->message($html_body);

            if ($ci->email->send()) {
                return ['success' => true, 'message' => 'ইমেইল সফলভাবে পাঠানো হয়েছে (SMTP)।', 'method' => 'smtp'];
            }
        } catch (Throwable $e) {
            log_message('error', 'Brevo SMTP Exception: ' . $e->getMessage());
        }

        // 2. Fallback to Brevo REST API v3
        if (!empty($api_key)) {
            $ch = curl_init('https://api.brevo.com/v3/smtp/email');
            $recipient = ['email' => $to_email];
            if (!empty($to_name)) {
                $recipient['name'] = $to_name;
            }

            $payload = [
                'sender'      => ['name' => $from_name, 'email' => $from_address],
                'to'          => [$recipient],
                'subject'     => $subject,
                'htmlContent' => $html_body,
            ];

            curl_setopt_array($ch, [
                CURLOPT_HTTPHEADER     => [
                    'api-key: ' . $api_key,
                    'Content-Type: application/json',
                    'Accept: application/json',
                ],
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_POST           => TRUE,
                CURLOPT_POSTFIELDS     => json_encode($payload),
                CURLOPT_TIMEOUT        => 10,
            ]);

            $response  = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err       = curl_error($ch);
            curl_close($ch);

            if ($http_code >= 200 && $http_code < 300) {
                return ['success' => true, 'message' => 'ইমেইল সফলভাবে পাঠানো হয়েছে (API)।', 'method' => 'api'];
            }

            log_message('error', 'Brevo API Fallback Error: ' . $response . ' ' . $err);
        }

        return ['success' => false, 'message' => 'ইমেইল পাঠানো সম্ভব হয়নি। অনুগ্রহ করে পরে চেষ্টা করুন।', 'method' => 'failed'];
    }
}

if (!function_exists('render_password_reset_email'))
{
    /**
     * Render modern Bengali HTML email template for password reset.
     *
     * @param string $recipient_name
     * @param string $reset_url
     * @param int $expiry_minutes
     * @return string
     */
    function render_password_reset_email($recipient_name, $reset_url, $expiry_minutes = 30)
    {
        $safe_name = htmlspecialchars($recipient_name ?: 'সম্মানিত ব্যবহারকারী', ENT_QUOTES, 'UTF-8');
        $safe_url  = htmlspecialchars($reset_url, ENT_QUOTES, 'UTF-8');

        return '<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>পাসওয়ার্ড রিসেট</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f5f7; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, \'Helvetica Neue\', Arial, sans-serif; color: #1e293b;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f4f5f7; padding: 32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width: 560px; background-color: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #4338ca 0%, #312e81 100%); padding: 32px 36px; text-align: center;">
                            <div style="display: inline-block; background-color: rgba(255, 255, 255, 0.15); border-radius: 12px; padding: 10px 18px; margin-bottom: 12px;">
                                <span style="font-size: 22px; font-weight: 800; color: #ffffff; letter-spacing: -0.5px;">Vireta Dev</span>
                            </div>
                            <h1 style="margin: 0; color: #ffffff; font-size: 20px; font-weight: 700; line-height: 1.4;">
                                পাসওয়ার্ড রিসেটের আবেদন
                            </h1>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 36px 36px 24px 36px;">
                            <p style="margin: 0 0 16px 0; font-size: 15px; line-height: 1.6; color: #334155;">
                                প্রিয় <strong>' . $safe_name . '</strong>,
                            </p>
                            <p style="margin: 0 0 20px 0; font-size: 14px; line-height: 1.6; color: #475569;">
                                আপনার ViretaDev ডিজিটাল পরিষদ ড্যাশবোর্ড অ্যাকাউন্টের পাসওয়ার্ড রিসেট করার একটি অনুরোধ পাওয়া গেছে। পাসওয়ার্ড পরিবর্তন করতে নিচের বাটনে ক্লিক করুন:
                            </p>

                            <!-- CTA Button -->
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin: 28px 0; width: 100%;">
                                <tr>
                                    <td align="center">
                                        <a href="' . $safe_url . '" target="_blank" style="display: inline-block; background-color: #4338ca; color: #ffffff; font-size: 14px; font-weight: 600; text-decoration: none; padding: 13px 32px; border-radius: 10px; box-shadow: 0 2px 4px rgba(67, 56, 202, 0.25);">
                                            পাসওয়ার্ড রিসেট করুন
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 0 0 12px 0; font-size: 12px; line-height: 1.5; color: #64748b;">
                                ⚠️ এই রিসেট লিংকটি আগামী <strong>' . (int)$expiry_minutes . ' মিনিট</strong> পর্যন্ত কার্যকর থাকবে।
                            </p>
                            <p style="margin: 0 0 20px 0; font-size: 12px; line-height: 1.5; color: #64748b;">
                                যদি বাটন কাজ না করে, নিচের লিংকটি কপি করে আপনার ব্রাউজারে পেস্ট করুন:<br>
                                <a href="' . $safe_url . '" style="color: #4338ca; word-break: break-all; text-decoration: underline;">' . $safe_url . '</a>
                            </p>

                            <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 24px 0;">

                            <p style="margin: 0; font-size: 12px; line-height: 1.5; color: #94a3b8;">
                                যদি আপনি এই অনুরোধটি না করে থাকেন, তবে এই ইমেইলটি উপেক্ষা করুন। আপনার পাসওয়ার্ড পরিবর্তন হবে না।
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8fafc; padding: 16px 36px; border-top: 1px solid #f1f5f9; text-align: center;">
                            <p style="margin: 0; font-size: 11px; color: #94a3b8;">
                                © ' . date('Y') . ' Vireta Dev — ডিজিটাল ইউনিয়ন পরিষদ সেবা ও অটোমেশন পোর্টাল।
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>';
    }
}
