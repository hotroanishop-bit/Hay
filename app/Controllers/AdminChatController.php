<?php
/**
 * Admin Chat Controller
 * Handles admin side of live chat
 */

class AdminChatController extends BaseController
{
    private AuthService $authService;

    public function __construct()
    {
        $sessionService = new SessionService();
        $userModel = new User();
        $this->authService = new AuthService($sessionService, $userModel);
    }

    /**
     * GET /admin/chat - List all active chats
     */
    public function index(): void
    {
        $user = $this->authService->user();
        
        if (!$user || empty($user['is_admin'])) {
            $this->redirect('/login');
            return;
        }

        $db = Database::getInstance();
        
        // Get all users with chat messages
        $sql = "SELECT u.id, u.username, u.email, u.avatar,
                       MAX(cm.created_at) as last_message_at,
                       SUM(CASE WHEN cm.is_read = 0 AND cm.sender_type = 'user' THEN 1 ELSE 0 END) as unread_count,
                       (SELECT message FROM chat_messages WHERE user_id = u.id ORDER BY created_at DESC LIMIT 1) as last_message
                FROM users u
                INNER JOIN chat_messages cm ON u.id = cm.user_id
                GROUP BY u.id
                ORDER BY last_message_at DESC";
        
        $stmt = $db->query($sql);
        $conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->currentPage = 'admin-chat';
        $this->render('admin/chat/index', [
            'pageTitle' => 'Live Chat Management',
            'currentPage' => $this->currentPage,
            'conversations' => $conversations
        ], ['admin-chat'], ['admin-chat']);
    }

    /**
     * GET /admin/chat/{userId} - View conversation with specific user
     */
    public function conversation(int $userId): void
    {
        $user = $this->authService->user();
        
        if (!$user || empty($user['is_admin'])) {
            $this->redirect('/login');
            return;
        }

        $db = Database::getInstance();
        
        // Get user info
        $userSql = "SELECT id, username, email, avatar FROM users WHERE id = ?";
        $userStmt = $db->prepare($userSql);
        $userStmt->execute([$userId]);
        $chatUser = $userStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$chatUser) {
            $this->setFlash('error', 'User not found');
            $this->redirect('/admin/chat');
            return;
        }
        
        // Get messages
        $messagesSql = "SELECT cm.*, u.username as sender_name
                        FROM chat_messages cm
                        LEFT JOIN users u ON (cm.sender_type = 'admin' AND cm.admin_id = u.id) 
                                          OR (cm.sender_type = 'user' AND cm.user_id = u.id)
                        WHERE cm.user_id = ?
                        ORDER BY cm.created_at ASC";
        $messagesStmt = $db->prepare($messagesSql);
        $messagesStmt->execute([$userId]);
        $messages = $messagesStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Mark all user messages as read
        $updateSql = "UPDATE chat_messages SET is_read = 1 
                      WHERE user_id = ? AND sender_type = 'user' AND is_read = 0";
        $updateStmt = $db->prepare($updateSql);
        $updateStmt->execute([$userId]);

        $this->currentPage = 'admin-chat';
        $this->render('admin/chat/conversation', [
            'pageTitle' => 'Chat with ' . $chatUser['username'],
            'currentPage' => $this->currentPage,
            'chatUser' => $chatUser,
            'messages' => $messages
        ], ['admin-chat'], ['admin-chat']);
    }

    /**
     * POST /admin/chat/{userId}/send - Send admin reply
     */
    public function send(int $userId): void
    {
        $user = $this->authService->user();
        
        if (!$user || empty($user['is_admin'])) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $message = trim($_POST['message'] ?? '');
        
        if (empty($message)) {
            $this->json(['error' => 'Message is required'], 400);
            return;
        }

        $db = Database::getInstance();
        
        $sql = "INSERT INTO chat_messages (user_id, admin_id, message, sender_type, created_at) 
                VALUES (?, ?, ?, 'admin', NOW())";
        $stmt = $db->prepare($sql);
        $stmt->execute([$userId, $user['id'], $message]);
        
        $messageId = $db->lastInsertId();
        
        $this->json([
            'success' => true,
            'message_id' => $messageId,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * GET /api/admin/chat/unread - Get total unread count for admin
     */
    public function getUnreadCount(): void
    {
        $user = $this->authService->user();
        
        if (!$user || empty($user['is_admin'])) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $db = Database::getInstance();
        $sql = "SELECT COUNT(*) as count FROM chat_messages 
                WHERE sender_type = 'user' AND is_read = 0";
        $stmt = $db->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $this->json([
            'success' => true,
            'unread_count' => (int)($result['count'] ?? 0)
        ]);
    }
}
