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
    public function send(int $userId, string $title, string $message, string $type = 'info', array $data = [], string $link = ''): int
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
            'data' => !empty($data) ? json_encode($data) : null,
            'link' => $link ?: null,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Create notification using static helper (convenience method)
     */
    public static function create(int $userId, string $type, string $title, string $message = '', array $data = [], string $link = ''): int
    {
        $service = new self(new Notification());
        return $service->send($userId, $title, $message, $type, $data, $link);
    }

    /**
     * Helper to create common notifications by event type
     */
    public static function notify(int $userId, string $eventType, array $eventData = []): int
    {
        $service = new self(new Notification());
        
        switch ($eventType) {
            case 'deposit_approved':
                return $service->send(
                    $userId,
                    'Deposit Approved',
                    'Your deposit of $' . number_format($eventData['amount'] ?? 0, 2) . ' has been approved.',
                    'success',
                    $eventData,
                    '/billing/history'
                );
            
            case 'deposit_rejected':
                return $service->send(
                    $userId,
                    'Deposit Rejected',
                    'Your deposit request has been rejected. ' . ($eventData['reason'] ?? ''),
                    'error',
                    $eventData,
                    '/billing/history'
                );
            
            case 'ticket_reply':
                return $service->send(
                    $userId,
                    'New Reply on Ticket',
                    'You have a new reply on ticket: ' . ($eventData['subject'] ?? ''),
                    'info',
                    $eventData,
                    '/tickets/' . ($eventData['ticket_id'] ?? '')
                );
            
            case 'ticket_resolved':
                return $service->send(
                    $userId,
                    'Ticket Resolved',
                    'Your support ticket has been marked as resolved.',
                    'success',
                    $eventData,
                    '/tickets/' . ($eventData['ticket_id'] ?? '')
                );
            
            case 'ticket_closed':
                return $service->send(
                    $userId,
                    'Ticket Closed',
                    'Your support ticket has been closed.',
                    'info',
                    $eventData,
                    '/tickets/' . ($eventData['ticket_id'] ?? '')
                );
            
            case 'api_key_created':
                return $service->send(
                    $userId,
                    'API Key Created',
                    'A new API key has been created: ' . ($eventData['name'] ?? ''),
                    'success',
                    $eventData,
                    '/keys'
                );
            
            case 'balance_low':
                return $service->send(
                    $userId,
                    'Low Balance Warning',
                    'Your account balance is running low. Current balance: $' . number_format($eventData['balance'] ?? 0, 2),
                    'warning',
                    $eventData,
                    '/billing/deposit'
                );
            
            case 'plan_upgraded':
                return $service->send(
                    $userId,
                    'Plan Upgraded',
                    'Your account has been upgraded to ' . ($eventData['plan_name'] ?? 'a new plan'),
                    'success',
                    $eventData,
                    '/billing'
                );
            
            case 'referral_commission':
                return $service->send(
                    $userId,
                    'Referral Commission Received',
                    'You earned $' . number_format($eventData['amount'] ?? 0, 2) . ' from a referral.',
                    'success',
                    $eventData,
                    '/referral'
                );
            
            case 'achievement_unlocked':
                return $service->send(
                    $userId,
                    'Achievement Unlocked!',
                    'You unlocked: ' . ($eventData['achievement_name'] ?? 'an achievement'),
                    'success',
                    $eventData,
                    '/achievements'
                );
            
            case 'giftcode_redeemed':
                return $service->send(
                    $userId,
                    'Gift Code Redeemed',
                    'Gift code redeemed successfully! Added $' . number_format($eventData['amount'] ?? 0, 2) . ' to your balance.',
                    'success',
                    $eventData,
                    '/billing'
                );
            
            default:
                return $service->send(
                    $userId,
                    $eventData['title'] ?? 'Notification',
                    $eventData['message'] ?? '',
                    'info',
                    $eventData
                );
        }
    }

    /**
     * Get unread count for user (static helper)
     */
    public static function getUnreadCountForUser(int $userId): int
    {
        $service = new self(new Notification());
        return $service->getUnreadCount($userId);
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

    /**
     * Get paginated notifications for a user with filter
     */
    public function getForUser(int $userId, int $page = 1, int $perPage = 20, string $filter = 'all'): array
    {
        return $this->notificationModel->getForUserPaginated($userId, $page, $perPage, $filter);
    }

    /**
     * Get notification counts by type for a user
     */
    public function getCounts(int $userId): array
    {
        return $this->notificationModel->getCounts($userId);
    }

    /**
     * Delete a notification
     */
    public function delete(int $notificationId): bool
    {
        return $this->notificationModel->delete($notificationId);
    }

    /**
     * Delete all read notifications for a user
     */
    public function deleteRead(int $userId): bool
    {
        return $this->notificationModel->deleteRead($userId);
    }
}
