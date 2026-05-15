<?php

declare(strict_types=1);

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService
{
    private string $fromEmail;
    private string $fromName;
    private string $smtpHost;
    private int $smtpPort;
    private string $smtpUser;
    private string $smtpPass;
    private string $encryption;

    public function __construct()
    {
        $this->fromEmail = $_ENV['EMAIL_FROM'] ?? 'noreply@gainz-app.local';
        $this->fromName = $_ENV['EMAIL_FROM_NAME'] ?? 'GAINZ System';
        $this->smtpHost = $_ENV['EMAIL_SMTP_HOST'] ?? '127.0.0.1';
        $this->smtpPort = (int) ($_ENV['EMAIL_SMTP_PORT'] ?? 25);
        $this->smtpUser = $_ENV['EMAIL_SMTP_USER'] ?? '';
        $this->smtpPass = $_ENV['EMAIL_SMTP_PASS'] ?? '';
        $this->encryption = $_ENV['EMAIL_SMTP_ENCRYPTION'] ?? '';
    }

    /**
     * Send 2FA code via email
     */
    public function send2FACode(string $toEmail, string $toName, string $code): bool
    {
        $subject = 'Your GAINZ 2FA Verification Code';
        $body = $this->generate2FAEmailBody($toName, $code);
        
        return $this->sendEmail($toEmail, $toName, $subject, $body);
    }

    /**
     * Send email using PHPMailer with SMTP
     */
    private function sendEmail(string $to, string $toName, string $subject, string $body): bool
    {
        try {
            $mail = new PHPMailer(true);

            // SMTP configuration
            $mail->isSMTP();
            $mail->Host = $this->smtpHost;
            $mail->Port = $this->smtpPort;
            $mail->SMTPAuth = !empty($this->smtpUser) && !empty($this->smtpPass);
            if ($mail->SMTPAuth) {
                $mail->Username = $this->smtpUser;
                $mail->Password = $this->smtpPass;
            }

            if ($this->encryption === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->SMTPAutoTLS = false;
            } elseif ($this->encryption === 'tls') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } else {
                $mail->SMTPSecure = false;
                $mail->SMTPAutoTLS = false;
            }

            // Set timeout
            $mail->Timeout = 10;
            $mail->ConnectTimeout = 10;

            // Sender
            $mail->setFrom($this->fromEmail, $this->fromName);
            
            // Recipient
            $mail->addAddress($to, $toName);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;

            // Send
            $mail->send();
            
            // Log success
            $this->logEmail($to, $subject, true);
            return true;

        } catch (Exception $e) {
            // Log error
            $this->logEmail($to, $subject, false, $mail->ErrorInfo ?? $e->getMessage());
            return false;
        }
    }

    /**
     * Log email attempts
     */
    private function logEmail(string $to, string $subject, bool $success, string $error = ''): void
    {
        $logDir = __DIR__ . '/../../storage/logs';
        @mkdir($logDir, 0777, true);
        
        $timestamp = date('Y-m-d H:i:s');
        $status = $success ? 'SUCCESS' : 'FAILED';
        $errorMsg = $error ? " | Error: $error" : '';
        $logEntry = "[$timestamp] To: $to | Subject: $subject | Status: $status$errorMsg\n";
        
        @file_put_contents($logDir . '/emails.log', $logEntry, FILE_APPEND);
    }

    /**
     * Generate HTML email body for 2FA code
     */
    private function generate2FAEmailBody(string $userName, string $code): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Barlow Condensed', 'Segoe UI', sans-serif;
            background: #080808;
            color: #fff;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 500px;
            margin: 0 auto;
            background: #111111;
            border: 2px solid #c8ff00;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
        }
        .logo {
            font-size: 32px;
            font-weight: 800;
            color: #c8ff00;
            margin-bottom: 20px;
            letter-spacing: 2px;
        }
        .code-box {
            background: #c8ff00;
            color: #080808;
            font-size: 32px;
            font-weight: 800;
            padding: 20px;
            margin: 20px 0;
            border-radius: 6px;
            letter-spacing: 4px;
            font-family: monospace;
        }
        .message {
            color: #fff;
            margin: 20px 0;
            font-size: 14px;
            line-height: 1.6;
        }
        .expiry {
            color: #c8ff00;
            font-size: 12px;
            margin-top: 20px;
        }
        .footer {
            color: #666;
            font-size: 11px;
            margin-top: 30px;
            border-top: 1px solid #272727;
            padding-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">⚡ GAINZ</div>
        <h2 style="margin: 0 0 20px 0; font-size: 20px; letter-spacing: 1px;">VERIFICATION CODE</h2>
        
        <p class="message">Hey $userName,</p>
        <p class="message">You requested a verification code to log into your GAINZ account. Use the code below to complete your login:</p>
        
        <div class="code-box">$code</div>
        
        <p class="message">This code will expire in 10 minutes. Do not share this code with anyone.</p>
        
        <div class="expiry">⏱ Valid for 10 minutes</div>
        
        <div class="footer">
            <p>If you didn't request this code, please ignore this email.</p>
            <p>© 2024 GAINZ Industrial Athletics System</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
}


