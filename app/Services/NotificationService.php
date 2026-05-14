<?php
/**
 * Notification Service
 * Handles sending and managing user notifications
 */

class NotificationService
{
    private Notification $notificationModel;

    public function __construct(Notification $notificationModel)
    {
        $this->notificationModel = $notificationModel;
    }

    /**
     * Send a notification to a specific user
     */
    public function send(int $userId, string $title, string $message, string $type = 'info'): int
    {
        $validTypes = ['info', 'warning', 'success', 'error'];
        if (!in_array($type, $validTypes)) {
            $type = 'info';
        }
        
        return $this->notificationModel->create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Broadcast a notification to all users (user_id = NULL)
     */
    public function broadcast(string $title, string $message, string $type = 'info'): int
    {
        return $this->notificationModel->createBroadcast($title, $message, $type);
    }

    /**
     * Get all unread notifications for a user
     */
    public function getUnread(int $userId): array
    {
        return $this->notificationModel->findByUser($userId, true);
    }

    /**
     * Mark a notification as read
     */
    public function markRead(int $notificationId): bool
    {
        return $this->notificationModel->markAsRead($notificationId);
    }

    /**
     * Get count of unread notifications for a user
     */
    public function getUnreadCount(int $userId): int
    {
        return $this->notificationModel->getUnreadCount($userId);
    }

    /**
     * Mark all notifications as read for a user
     */
    public function markAllRead(int $userId): bool
    {
        return $this->notificationModel->markAllRead($userId);
    }
}
