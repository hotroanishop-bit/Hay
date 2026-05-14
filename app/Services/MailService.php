<?php
/**
 * Mail Service
 * Handles email sending and templates
 */

class MailService
{
    private array $config;

    public function __construct()
    {
        $this->config = require CONFIG_PATH . '/mail.php';
    }

    /**
     * Send an email using a template
     */
    public function send(string $to, string $subject, string $template, array $data = []): bool
    {
        // Load template
        $body = $this->renderTemplate($template, $data);

        // Build headers
        $headers = $this->buildHeaders();

        // Send based on configured driver
        switch ($this->config['driver']) {
            case 'smtp':
                return $this->sendViaSMTP($to, $subject, $body, $headers);
            case 'sendmail':
                return $this->sendViaSendmail($to, $subject, $body, $headers);
            case 'mail':
            default:
                return mail($to, $subject, $body, $headers);
        }
    }

    /**
     * Send verification email to user
     */
    public function sendVerificationEmail(array $user): bool
    {
        $token = bin2hex(random_bytes(32));
        $config = require CONFIG_PATH . '/app.php';
        $verifyUrl = ($config['url'] ?? '') . '/verify-email?token=' . $token . '&email=' . urlencode($user['email']);

        return $this->send(
            $user['email'],
            'Verify your email address',
            'verify-email',
            [
                'name' => $user['name'] ?? 'User',
                'verify_url' => $verifyUrl,
            ]
        );
    }

    /**
     * Send password reset email
     */
    public function sendPasswordReset(array $user, string $token): bool
    {
        $config = require CONFIG_PATH . '/app.php';
        $resetUrl = ($config['url'] ?? '') . '/reset-password?token=' . $token . '&email=' . urlencode($user['email']);

        return $this->send(
            $user['email'],
            'Reset your password',
            'password-reset',
            [
                'name' => $user['name'] ?? 'User',
                'reset_url' => $resetUrl,
            ]
        );
    }

    /**
     * Send payment receipt email
     */
    public function sendReceipt(array $user, array $transaction): bool
    {
        return $this->send(
            $user['email'],
            'Payment Receipt - ' . ($transaction['reference_id'] ?? 'N/A'),
            'receipt',
            [
                'name' => $user['name'] ?? 'User',
                'amount' => $transaction['amount'] ?? 0,
                'reference_id' => $transaction['reference_id'] ?? 'N/A',
                'date' => $transaction['created_at'] ?? date('Y-m-d H:i:s'),
                'payment_method' => $transaction['payment_method'] ?? 'N/A',
            ]
        );
    }

    /**
     * Send API key expiry warning email
     */
    public function sendKeyExpiry(array $user, array $key): bool
    {
        return $this->send(
            $user['email'],
            'Your API key is expiring soon',
            'key-expiry',
            [
                'name' => $user['name'] ?? 'User',
                'key_name' => $key['name'] ?? 'Unnamed Key',
                'expires_at' => $key['expires_at'] ?? 'N/A',
            ]
        );
    }

    /**
     * Render an email template with data
     */
    private function renderTemplate(string $template, array $data): string
    {
        $templatePath = ($this->config['templates_path'] ?? VIEWS_PATH . '/emails') . '/' . $template . '.php';

        // Check if template exists
        if (!file_exists($templatePath)) {
            // Return plain text fallback
            return $this->renderPlainText($template, $data);
        }

        // Extract data to variables
        extract($data);

        // Buffer output
        ob_start();
        include $templatePath;
        return ob_get_clean();
    }

    /**
     * Render plain text fallback when template not found
     */
    private function renderPlainText(string $template, array $data): string
    {
        $text = "Email: {$template}\n\n";
        foreach ($data as $key => $value) {
            $text .= ucfirst(str_replace('_', ' ', $key)) . ": {$value}\n";
        }
        return $text;
    }

    /**
     * Build email headers
     */
    private function buildHeaders(): string
    {
        $from = $this->config['from'] ?? [];
        $replyTo = $this->config['reply_to'] ?? [];

        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . ($from['name'] ?? 'System') . ' <' . ($from['address'] ?? 'noreply@example.com') . '>',
        ];

        if (!empty($replyTo['address'])) {
            $headers[] = 'Reply-To: ' . ($replyTo['name'] ?? '') . ' <' . $replyTo['address'] . '>';
        }

        return implode("\r\n", $headers);
    }

    /**
     * Send email via SMTP
     */
    private function sendViaSMTP(string $to, string $subject, string $body, string $headers): bool
    {
        $host = $this->config['host'] ?? 'localhost';
        $port = $this->config['port'] ?? 587;
        $username = $this->config['username'] ?? '';
        $password = $this->config['password'] ?? '';
        $encryption = $this->config['encryption'] ?? 'tls';

        // Open socket connection
        $socket = @fsockopen(
            ($encryption === 'ssl' ? 'ssl://' : '') . $host,
            $port,
            $errno,
            $errstr,
            30
        );

        if (!$socket) {
            error_log("SMTP connection failed: {$errstr} ({$errno})");
            return false;
        }

        // Read greeting
        fgets($socket);

        // Send EHLO
        $this->smtpCommand($socket, 'EHLO ' . gethostname());

        // STARTTLS if needed
        if ($encryption === 'tls') {
            $this->smtpCommand($socket, 'STARTTLS');
            stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            $this->smtpCommand($socket, 'EHLO ' . gethostname());
        }

        // Authenticate if credentials provided
        if ($username && $password) {
            $this->smtpCommand($socket, 'AUTH LOGIN');
            $this->smtpCommand($socket, base64_encode($username));
            $this->smtpCommand($socket, base64_encode($password));
        }

        // Send email
        $from = $this->config['from']['address'] ?? 'noreply@example.com';
        $this->smtpCommand($socket, 'MAIL FROM:<' . $from . '>');
        $this->smtpCommand($socket, 'RCPT TO:<' . $to . '>');
        $this->smtpCommand($socket, 'DATA');

        // Send headers and body
        fputs($socket, $headers . "\r\n");
        fputs($socket, 'Subject: ' . $subject . "\r\n\r\n");
        fputs($socket, $body . "\r\n.\r\n");
        fgets($socket);

        // Quit
        $this->smtpCommand($socket, 'QUIT');
        fclose($socket);

        return true;
    }

    /**
     * Send SMTP command and read response
     */
    private function smtpCommand($socket, string $command): string
    {
        fputs($socket, $command . "\r\n");
        return fgets($socket);
    }

    /**
     * Send email via sendmail
     */
    private function sendViaSendmail(string $to, string $subject, string $body, string $headers): bool
    {
        $sendmail = popen('/usr/sbin/sendmail -t', 'w');
        if (!$sendmail) {
            return false;
        }

        fputs($sendmail, 'To: ' . $to . "\r\n");
        fputs($sendmail, 'Subject: ' . $subject . "\r\n");
        fputs($sendmail, $headers . "\r\n\r\n");
        fputs($sendmail, $body);

        return pclose($sendmail) === 0;
    }
}
