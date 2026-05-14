<?php
/**
 * Telegram Controller
 * Handles Telegram bot integration for user account linking
 */

class TelegramController extends BaseController
{
    private AuthService $authService;
    private TelegramService $telegramService;

    public function __construct()
    {
        $sessionService = new SessionService();
        $userModel = new User();
        $this->authService = new AuthService($sessionService, $userModel);
        $this->telegramService = new TelegramService();
    }

    /**
     * Show Telegram linking page
     */
    public function showLink(): void
    {
        $user = $this->authService->user();
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        // Check if user already has Telegram linked
        $isLinked = !empty($user['telegram_chat_id']);
        $linkedAt = $user['telegram_linked_at'] ?? null;

        // Check if Telegram is configured
        $isConfigured = $this->telegramService->isConfigured();

        $this->currentPage = 'profile-telegram';
        $this->render('profile/telegram', [
            'pageTitle' => 'Telegram Notifications',
            'currentPage' => $this->currentPage,
            'user' => $user,
            'isLinked' => $isLinked,
            'linkedAt' => $linkedAt,
            'isConfigured' => $isConfigured,
            'flash' => $_SESSION['flash'] ?? null
        ], ['telegram'], []);

        unset($_SESSION['flash']);
    }

    /**
     * Generate a link token and return the link URL (AJAX)
     */
    public function generateLink(): void
    {
        $user = $this->authService->user();
        if (!$user) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        if (!$this->telegramService->isConfigured()) {
            $this->json(['error' => 'Telegram integration is not configured'], 400);
            return;
        }

        // Check if already linked
        if (!empty($user['telegram_chat_id'])) {
            $this->json(['error' => 'Telegram is already linked to your account'], 400);
            return;
        }

        // Generate link token
        $token = $this->telegramService->generateLinkToken($user['id']);
        if (!$token) {
            $this->json(['error' => 'Failed to generate link token'], 500);
            return;
        }

        // Get the link URL
        $linkUrl = $this->telegramService->getLinkUrl($token);

        $this->json([
            'success' => true,
            'link_url' => $linkUrl,
            'token' => $token,
            'expires_in' => 30 // minutes
        ]);
    }

    /**
     * Handle Telegram webhook (POST from Telegram servers)
     */
    public function webhook(): void
    {
        // Get the raw POST data
        $input = file_get_contents('php://input');
        $update = json_decode($input, true);

        if (!$update) {
            http_response_code(400);
            exit;
        }

        // Log incoming webhook for debugging
        error_log('Telegram webhook received: ' . $input);

        // Handle different update types
        if (isset($update['message'])) {
            $this->handleMessage($update['message']);
        }

        // Respond with 200 OK
        http_response_code(200);
        echo 'OK';
        exit;
    }

    /**
     * Unlink Telegram account
     */
    public function unlink(): void
    {
        $user = $this->authService->user();
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            $this->setFlash('error', 'Invalid request. Please try again.');
            $this->redirect('/profile/telegram');
            return;
        }

        if (empty($user['telegram_chat_id'])) {
            $this->setFlash('error', 'Telegram is not linked to your account.');
            $this->redirect('/profile/telegram');
            return;
        }

        // Send goodbye message before unlinking
        $this->telegramService->sendMessage(
            $user['telegram_chat_id'],
            "Your Telegram account has been unlinked from your API Gateway account.\n\nYou will no longer receive notifications here."
        );

        // Unlink the account
        if ($this->telegramService->unlinkUser($user['id'])) {
            // Update session
            $_SESSION['user']['telegram_chat_id'] = null;
            $_SESSION['user']['telegram_linked_at'] = null;

            $this->setFlash('success', 'Telegram account unlinked successfully.');
        } else {
            $this->setFlash('error', 'Failed to unlink Telegram account. Please try again.');
        }

        $this->redirect('/profile/telegram');
    }

    /**
     * Handle incoming Telegram message
     */
    private function handleMessage(array $message): void
    {
        $chatId = (string) ($message['chat']['id'] ?? '');
        $text = trim($message['text'] ?? '');

        if (empty($chatId) || empty($text)) {
            return;
        }

        // Handle /start command with link token
        if (strpos($text, '/start ') === 0) {
            $token = substr($text, 7);
            $this->handleLinkCommand($chatId, $token, $message);
            return;
        }

        // Handle other commands
        switch ($text) {
            case '/start':
                $this->handleStartCommand($chatId);
                break;
            case '/status':
                $this->handleStatusCommand($chatId);
                break;
            case '/help':
                $this->handleHelpCommand($chatId);
                break;
            default:
                $this->handleUnknownCommand($chatId);
                break;
        }
    }

    /**
     * Handle /start command (without token)
     */
    private function handleStartCommand(string $chatId): void
    {
        $user = $this->telegramService->getUserByChatId($chatId);

        if ($user) {
            $message = "&#128075; <b>Welcome back, " . htmlspecialchars($user['name']) . "!</b>\n\n"
                     . "Your Telegram is linked to your API Gateway account.\n\n"
                     . "Commands:\n"
                     . "/status - Check your account status\n"
                     . "/help - Show help information";
        } else {
            $message = "&#128075; <b>Welcome to API Gateway Bot!</b>\n\n"
                     . "To link your account:\n"
                     . "1. Log in to your API Gateway account\n"
                     . "2. Go to Profile > Telegram\n"
                     . "3. Click 'Link Telegram Account'\n"
                     . "4. Click the link provided\n\n"
                     . "Once linked, you'll receive notifications for deposits, security alerts, and more!";
        }

        $this->telegramService->sendMessage($chatId, $message);
    }

    /**
     * Handle link command with token
     */
    private function handleLinkCommand(string $chatId, string $token, array $message): void
    {
        // Check if this chat is already linked
        $existingUser = $this->telegramService->getUserByChatId($chatId);
        if ($existingUser) {
            $this->telegramService->sendMessage(
                $chatId,
                "&#9888;&#65039; This Telegram account is already linked to " . htmlspecialchars($existingUser['email']) . ".\n\n"
                . "Please unlink it first from Profile > Telegram before linking to a new account."
            );
            return;
        }

        // Verify the token
        $userId = $this->telegramService->verifyLinkToken($token);
        if (!$userId) {
            $this->telegramService->sendMessage(
                $chatId,
                "&#10060; <b>Link Failed</b>\n\n"
                . "The link token is invalid or has expired.\n\n"
                . "Please generate a new link from your API Gateway profile."
            );
            return;
        }

        // Link the user
        if ($this->telegramService->linkUser($userId, $chatId)) {
            // Send welcome notification
            $this->telegramService->sendNotification($userId, 'welcome', []);

            // Get user name for the message
            $userModel = new User();
            $user = $userModel->find($userId);

            $this->telegramService->sendMessage(
                $chatId,
                $this->telegramService->formatNotification('welcome', [
                    'name' => $user['name'] ?? 'User'
                ])
            );
        } else {
            $this->telegramService->sendMessage(
                $chatId,
                "&#10060; <b>Link Failed</b>\n\n"
                . "An error occurred while linking your account. Please try again."
            );
        }
    }

    /**
     * Handle /status command
     */
    private function handleStatusCommand(string $chatId): void
    {
        $user = $this->telegramService->getUserByChatId($chatId);

        if (!$user) {
            $this->telegramService->sendMessage(
                $chatId,
                "&#10060; Your Telegram is not linked to any account.\n\n"
                . "Link it from your API Gateway profile."
            );
            return;
        }

        $balance = number_format($user['balance'] ?? 0, 2);
        $message = "&#128200; <b>Account Status</b>\n\n"
                 . "Name: " . htmlspecialchars($user['name']) . "\n"
                 . "Email: " . htmlspecialchars($user['email']) . "\n"
                 . "Balance: <b>\${$balance}</b>\n"
                 . "Status: " . ($user['is_banned'] ? '&#10060; Banned' : '&#9989; Active');

        $this->telegramService->sendMessage($chatId, $message);
    }

    /**
     * Handle /help command
     */
    private function handleHelpCommand(string $chatId): void
    {
        $message = "&#128218; <b>Help</b>\n\n"
                 . "<b>Available Commands:</b>\n"
                 . "/start - Start the bot / Show welcome\n"
                 . "/status - Check your account status\n"
                 . "/help - Show this help message\n\n"
                 . "<b>Notifications:</b>\n"
                 . "You will receive notifications for:\n"
                 . "- Deposit approvals/rejections\n"
                 . "- Low balance warnings\n"
                 . "- Security alerts\n"
                 . "- API key activities\n\n"
                 . "<b>Need Support?</b>\n"
                 . "Visit your API Gateway dashboard to create a support ticket.";

        $this->telegramService->sendMessage($chatId, $message);
    }

    /**
     * Handle unknown command
     */
    private function handleUnknownCommand(string $chatId): void
    {
        $this->telegramService->sendMessage(
            $chatId,
            "&#129300; I don't understand that command.\n\n"
            . "Type /help to see available commands."
        );
    }
}
