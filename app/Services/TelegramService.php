<?php
/**
 * Telegram Service
 * Handles Telegram bot integration for notifications
 */

class TelegramService
{
    private array $config;
    private string $apiBaseUrl;

    public function __construct()
    {
        $this->config = require CONFIG_PATH . '/telegram.php';
        $this->apiBaseUrl = $this->config['api_base_url'] . ($this->config['bot_token'] ?? '');
    }

    /**
     * Check if Telegram integration is configured and enabled
     */
    public function isConfigured(): bool
    {
        return !empty($this->config['bot_token']) && ($this->config['enabled'] ?? false);
    }

    /**
     * Send a message to a Telegram chat
     */
    public function sendMessage(string $chatId, string $text, array $options = []): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }

        try {
            $params = [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => $options['parse_mode'] ?? 'HTML',
                'disable_web_page_preview' => $options['disable_preview'] ?? true,
            ];

            $response = $this->apiRequest('sendMessage', $params);
            return $response !== false && isset($response['ok']) && $response['ok'] === true;
        } catch (Exception $e) {
            error_log('Telegram sendMessage error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate a unique link token for account linking
     */
    public function generateLinkToken(int $userId): ?string
    {
        try {
            // Generate secure random token
            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+' . ($this->config['link_token_expiry'] ?? 30) . ' minutes'));

            // Clean up old tokens for this user
            $this->cleanupExpiredTokens($userId);

            // Store token in database
            $sql = "INSERT INTO telegram_link_tokens (user_id, token, expires_at) VALUES (:user_id, :token, :expires_at)";
            $pdo = $this->getDb();
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'user_id' => $userId,
                'token' => $token,
                'expires_at' => $expiresAt
            ]);

            return $token;
        } catch (Exception $e) {
            error_log('Telegram generateLinkToken error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Verify a link token and return the user ID
     */
    public function verifyLinkToken(string $token): ?int
    {
        try {
            $sql = "SELECT user_id FROM telegram_link_tokens WHERE token = :token AND expires_at > NOW() LIMIT 1";
            $pdo = $this->getDb();
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['token' => $token]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                // Delete the used token
                $this->deleteToken($token);
                return (int) $result['user_id'];
            }

            return null;
        } catch (Exception $e) {
            error_log('Telegram verifyLinkToken error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Link a Telegram chat ID to a user
     */
    public function linkUser(int $userId, string $chatId): bool
    {
        try {
            $sql = "UPDATE users SET telegram_chat_id = :chat_id, telegram_linked_at = NOW() WHERE id = :user_id";
            $pdo = $this->getDb();
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([
                'chat_id' => $chatId,
                'user_id' => $userId
            ]);
        } catch (Exception $e) {
            error_log('Telegram linkUser error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Unlink a user's Telegram account
     */
    public function unlinkUser(int $userId): bool
    {
        try {
            $sql = "UPDATE users SET telegram_chat_id = NULL, telegram_linked_at = NULL WHERE id = :user_id";
            $pdo = $this->getDb();
            $stmt = $pdo->prepare($sql);
            return $stmt->execute(['user_id' => $userId]);
        } catch (Exception $e) {
            error_log('Telegram unlinkUser error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get a user by their Telegram chat ID
     */
    public function getUserByChatId(string $chatId): ?array
    {
        try {
            $sql = "SELECT * FROM users WHERE telegram_chat_id = :chat_id LIMIT 1";
            $pdo = $this->getDb();
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['chat_id' => $chatId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (Exception $e) {
            error_log('Telegram getUserByChatId error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Format a notification message with emojis
     */
    public function formatNotification(string $type, array $data): string
    {
        switch ($type) {
            case 'deposit_approved':
                return $this->formatDepositApproved($data);
            case 'deposit_rejected':
                return $this->formatDepositRejected($data);
            case 'low_balance':
                return $this->formatLowBalance($data);
            case 'api_key_created':
                return $this->formatApiKeyCreated($data);
            case 'security_alert':
                return $this->formatSecurityAlert($data);
            case 'welcome':
                return $this->formatWelcome($data);
            case 'auto_topup':
                return $this->formatAutoTopup($data);
            default:
                return $this->formatGeneric($data);
        }
    }

    /**
     * Send a notification to a user if they have Telegram linked
     */
    public function sendNotification(int $userId, string $type, array $data): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }

        // Check if this notification type is enabled
        if (!($this->config['notifications'][$type] ?? true)) {
            return false;
        }

        try {
            $userModel = new User();
            $user = $userModel->find($userId);

            if (!$user || empty($user['telegram_chat_id'])) {
                return false;
            }

            $message = $this->formatNotification($type, $data);
            return $this->sendMessage($user['telegram_chat_id'], $message);
        } catch (Exception $e) {
            error_log('Telegram sendNotification error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Broadcast a message to all users with linked Telegram accounts
     */
    public function broadcast(string $message): int
    {
        if (!$this->isConfigured()) {
            return 0;
        }

        try {
            $sql = "SELECT telegram_chat_id FROM users WHERE telegram_chat_id IS NOT NULL";
            $pdo = $this->getDb();
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $sent = 0;
            foreach ($users as $chatId) {
                if ($this->sendMessage($chatId, $message)) {
                    $sent++;
                }
                // Small delay to avoid rate limiting
                usleep(50000); // 50ms
            }

            return $sent;
        } catch (Exception $e) {
            error_log('Telegram broadcast error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get the bot info
     */
    public function getBotInfo(): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        $response = $this->apiRequest('getMe');
        if ($response && isset($response['ok']) && $response['ok']) {
            return $response['result'];
        }
        return null;
    }

    /**
     * Get the deep link URL for linking an account
     */
    public function getLinkUrl(string $token): string
    {
        $botUsername = $this->config['bot_username'] ?? '';
        return "https://t.me/{$botUsername}?start={$token}";
    }

    // =====================
    // Private Methods
    // =====================

    /**
     * Make an API request to Telegram
     */
    private function apiRequest(string $method, array $params = []): mixed
    {
        $url = $this->apiBaseUrl . '/' . $method;

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($params),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            error_log('Telegram API curl error: ' . $error);
            return false;
        }

        return json_decode($response, true);
    }

    /**
     * Get database connection
     */
    private function getDb(): PDO
    {
        $config = require CONFIG_PATH . '/database.php';
        
        $dsn = sprintf(
            '%s:host=%s;port=%s;dbname=%s;charset=%s',
            $config['driver'],
            $config['host'],
            $config['port'],
            $config['database'],
            $config['charset']
        );
        
        return new PDO($dsn, $config['username'], $config['password'], $config['options']);
    }

    /**
     * Clean up expired tokens for a user
     */
    private function cleanupExpiredTokens(int $userId): void
    {
        $sql = "DELETE FROM telegram_link_tokens WHERE user_id = :user_id OR expires_at < NOW()";
        $pdo = $this->getDb();
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
    }

    /**
     * Delete a specific token
     */
    private function deleteToken(string $token): void
    {
        $sql = "DELETE FROM telegram_link_tokens WHERE token = :token";
        $pdo = $this->getDb();
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['token' => $token]);
    }

    // =====================
    // Message Formatters
    // =====================

    private function formatDepositApproved(array $data): string
    {
        $amount = number_format($data['amount'] ?? 0, 2);
        return "&#9989; <b>Deposit Approved!</b>\n\n"
             . "Your deposit of <b>\${$amount}</b> has been approved and added to your account.\n\n"
             . "Reference: <code>" . ($data['reference'] ?? 'N/A') . "</code>\n"
             . "New Balance: <b>\$" . number_format($data['new_balance'] ?? 0, 2) . "</b>";
    }

    private function formatDepositRejected(array $data): string
    {
        $amount = number_format($data['amount'] ?? 0, 2);
        return "&#10060; <b>Deposit Rejected</b>\n\n"
             . "Your deposit of <b>\${$amount}</b> has been rejected.\n\n"
             . "Reason: " . ($data['reason'] ?? 'Not specified') . "\n\n"
             . "Please contact support if you believe this is an error.";
    }

    private function formatLowBalance(array $data): string
    {
        return "&#9888;&#65039; <b>Low Balance Warning</b>\n\n"
             . "Your balance is running low: <b>\$" . number_format($data['balance'] ?? 0, 2) . "</b>\n\n"
             . "Consider topping up to avoid service interruption.";
    }

    private function formatApiKeyCreated(array $data): string
    {
        return "&#128273; <b>New API Key Created</b>\n\n"
             . "A new API key has been created:\n"
             . "Name: <b>" . ($data['name'] ?? 'Unnamed') . "</b>\n\n"
             . "If you did not create this key, please secure your account immediately.";
    }

    private function formatSecurityAlert(array $data): string
    {
        return "&#128680; <b>Security Alert</b>\n\n"
             . ($data['message'] ?? 'A security event was detected on your account.') . "\n\n"
             . "If this was not you, please change your password immediately.";
    }

    private function formatWelcome(array $data): string
    {
        return "&#127881; <b>Account Linked Successfully!</b>\n\n"
             . "Your Telegram account is now linked to your API Gateway account.\n\n"
             . "You will receive notifications for:\n"
             . "- Deposit approvals/rejections\n"
             . "- Low balance warnings\n"
             . "- Security alerts\n\n"
             . "To unlink, visit your profile settings.";
    }

    private function formatAutoTopup(array $data): string
    {
        $amount = number_format($data['amount'] ?? 0, 2);
        return "&#128176; <b>Auto Top-Up Triggered</b>\n\n"
             . "Your balance dropped below the threshold.\n"
             . "A deposit request for <b>\${$amount}</b> has been created automatically.\n\n"
             . "Please complete the payment to restore your balance.";
    }

    private function formatGeneric(array $data): string
    {
        return "&#128172; <b>" . ($data['title'] ?? 'Notification') . "</b>\n\n"
             . ($data['message'] ?? '');
    }
}
