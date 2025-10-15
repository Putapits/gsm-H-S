<?php
/**
 * Brevo Email Service
 * Helps send OTP and transactional emails using Brevo (Sendinblue) API.
 */

namespace App\Services;

use GuzzleHttp\Client;
use SendinBlue\Client\Api\TransactionalEmailsApi;
use SendinBlue\Client\Configuration;
use SendinBlue\Client\Model\SendSmtpEmail;

class BrevoEmailService
{
    private TransactionalEmailsApi $api;
    private string $senderEmail;
    private string $senderName;

    public function __construct(string $apiKey, string $senderEmail = 'noreply@goserveph.gov', string $senderName = 'GoServePH Services')
    {
        if (empty($apiKey)) {
            throw new \InvalidArgumentException('Brevo API key is missing.');
        }

        $config = Configuration::getDefaultConfiguration()->setApiKey('api-key', $apiKey);
        $this->api = new TransactionalEmailsApi(new Client(), $config);
        $this->senderEmail = $senderEmail;
        $this->senderName = $senderName;
    }

    public static function fromEnv(): self
    {
        $apiKey = getenv('BREVO_API_KEY') ?: ($_ENV['BREVO_API_KEY'] ?? '');
        $senderEmail = getenv('BREVO_SENDER_EMAIL') ?: ($_ENV['BREVO_SENDER_EMAIL'] ?? 'noreply@goserveph.gov');
        $senderName = getenv('BREVO_SENDER_NAME') ?: ($_ENV['BREVO_SENDER_NAME'] ?? 'GoServePH Services');

        return new self($apiKey, $senderEmail, $senderName);
    }

    public function sendOtp(string $email, string $name, string $otp): bool
    {
        $subject = 'Your GoServePH One-Time Password';
        $html = $this->buildOtpTemplate($name, $otp);

        return $this->sendEmail($email, $name, $subject, $html);
    }

    public function sendEmail(string $email, string $name, string $subject, string $htmlContent): bool
    {
        try {
            $emailPayload = new SendSmtpEmail([
                'sender' => [
                    'name' => $this->senderName,
                    'email' => $this->senderEmail,
                ],
                'to' => [[
                    'email' => $email,
                    'name' => $name,
                ]],
                'subject' => $subject,
                'htmlContent' => $htmlContent,
            ]);

            $this->api->sendTransacEmail($emailPayload);
            return true;
        } catch (\Throwable $e) {
            error_log('Brevo email error: ' . $e->getMessage());
            return false;
        }
    }

    private function buildOtpTemplate(string $name, string $otp): string
    {
        $safeName = htmlspecialchars($name ?: 'User', ENT_QUOTES, 'UTF-8');
        $safeOtp = htmlspecialchars($otp, ENT_QUOTES, 'UTF-8');
        $year = date('Y');

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your GoServePH OTP</title>
</head>
<body style="background-color:#f4f6f8;margin:0;padding:24px;font-family:Arial,sans-serif;">
    <table role="presentation" cellspacing="0" cellpadding="0" width="100%" style="max-width:640px;margin:0 auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 6px 24px rgba(15,23,42,0.12);">
        <tr>
            <td style="background:linear-gradient(135deg,#2563eb 0%,#1d4ed8 100%);padding:32px;text-align:center;color:#ffffff;">
                <h1 style="margin:0;font-size:28px;font-weight:700;">GoServePH</h1>
                <p style="margin:12px 0 0;font-size:14px;opacity:0.85;">Secure login verification</p>
            </td>
        </tr>
        <tr>
            <td style="padding:40px 36px 32px;color:#0f172a;">
                <p style="margin:0 0 16px;font-size:18px;font-weight:600;">Hello {$safeName},</p>
                <p style="margin:0 0 24px;font-size:15px;line-height:1.6;color:#334155;">
                    Use the one-time password below to finish signing in to your GoServePH account. This code is valid for the next <strong>10 minutes</strong>.
                </p>
                <div style="text-align:center;margin:24px 0;">
                    <div style="display:inline-block;padding:20px 32px;border:2px dashed #2563eb;border-radius:12px;background:#eff6ff;">
                        <span style="display:block;font-size:13px;text-transform:uppercase;letter-spacing:2px;color:#1d4ed8;margin-bottom:10px;">Your OTP Code</span>
                        <span style="display:block;font-size:36px;font-weight:700;letter-spacing:12px;font-family:'Courier New',monospace;color:#1d4ed8;">{$safeOtp}</span>
                    </div>
                </div>
                <p style="margin:0 0 12px;font-size:14px;line-height:1.6;color:#475569;">
                    For your safety:
                </p>
                <ul style="margin:0 0 24px 20px;font-size:14px;line-height:1.6;color:#475569;">
                    <li>Never share this code with anyone.</li>
                    <li>If you didn’t request the code, ignore this email.</li>
                </ul>
                <p style="margin:0;font-size:13px;color:#94a3b8;">This is an automated message. Replies to this email address are not monitored.</p>
            </td>
        </tr>
        <tr>
            <td style="background:#f8fafc;padding:18px;text-align:center;font-size:12px;color:#94a3b8;">
                © {$year} GoServePH Services. All rights reserved.
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }
}
