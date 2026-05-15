<?php
/**
 * Chat Controller
 * Handles live chat functionality between users and admin
 */

class ChatController extends BaseController
{
    private AuthService $authService;

    public function __construct()
    {
        $sessionService = new SessionService();
        $userModel = new User();
        $this->authService = new AuthService($sessionService, $userModel);
    }

    /**
     * GET /api/chat/messages - Get chat messages for current user
     */
    public function getMessages(): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $db = Database::getInstance();
        $lastId = (int)($_GET['last_id'] ?? 0);
        
        $sql = "SELECT cm.*, u.username as admin_username
                FROM chat_messages cm
                LEFT JOIN users u ON cm.admin_id = u.id
                WHERE cm.user_id = ?";
        
        if ($lastId > 0) {
            $sql .= " AND cm.id > ?";
        }
        
        $sql .= " ORDER BY cm.created_at ASC LIMIT 100";
        
        $stmt = $db->prepare($sql);
        
        if ($lastId > 0) {
            $stmt->execute([$user['id'], $lastId]);
        } else {
            $stmt->execute([$user['id']]);
        }
        
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Mark messages as read
        $updateSql = "UPDATE chat_messages SET is_read = 1 
                      WHERE user_id = ? AND sender_type = 'admin' AND is_read = 0";
        $updateStmt = $db->prepare($updateSql);
        $updateStmt->execute([$user['id']]);
        
        $this->json([
            'success' => true,
            'messages' => $messages
        ]);
    }

    /**
     * POST /api/chat/send - Send a message
     */
    public function send(): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $message = trim($_POST['message'] ?? '');
        
        if (empty($message)) {
            $this->json(['error' => 'Message is required'], 400);
            return;
        }

        if (strlen($message) > 2000) {
            $this->json(['error' => 'Message too long (max 2000 characters)'], 400);
            return;
        }

        $db = Database::getInstance();
        
        $sql = "INSERT INTO chat_messages (user_id, message, sender_type, created_at) 
                VALUES (?, ?, 'user', NOW())";
        $stmt = $db->prepare($sql);
        $stmt->execute([$user['id'], $message]);
        
        $messageId = $db->lastInsertId();
        
        $this->json([
            'success' => true,
            'message_id' => $messageId,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * GET /api/chat/status - Get chat status (online/offline)
     */
    public function getStatus(): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        // Check if any admin was active in the last 5 minutes
        $db = Database::getInstance();
        $sql = "SELECT COUNT(*) as count FROM users 
                WHERE is_admin = 1 AND last_activity > DATE_SUB(NOW(), INTERVAL 5 MINUTE)";
        $stmt = $db->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $isOnline = ($result['count'] ?? 0) > 0;
        
        // Get unread count
        $unreadSql = "SELECT COUNT(*) as count FROM chat_messages 
                      WHERE user_id = ? AND sender_type = 'admin' AND is_read = 0";
        $unreadStmt = $db->prepare($unreadSql);
        $unreadStmt->execute([$user['id']]);
        $unreadResult = $unreadStmt->fetch(PDO::FETCH_ASSOC);
        
        $this->json([
            'success' => true,
            'is_online' => $isOnline,
            'unread_count' => (int)($unreadResult['count'] ?? 0)
        ]);
    }
}
