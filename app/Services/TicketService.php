<?php
/**
 * Ticket Service
 * Handles support ticket operations with notifications
 */

class TicketService
{
    private SupportTicket $ticketModel;
    private TicketMessage $messageModel;
    private NotificationService $notificationService;

    public function __construct()
    {
        $this->ticketModel = new SupportTicket();
        $this->messageModel = new TicketMessage();
        $this->notificationService = new NotificationService(new Notification());
    }

    /**
     * Create a new ticket with initial message
     */
    public function createTicket(int $userId, array $data): array
    {
        // Create the ticket
        $ticketId = $this->ticketModel->createTicket($userId, [
            'subject' => $data['subject'],
            'category' => $data['category'] ?? SupportTicket::CATEGORY_OTHER,
            'priority' => $data['priority'] ?? SupportTicket::PRIORITY_MEDIUM
        ]);
        
        // Create the first message
        if (!empty($data['message'])) {
            $this->messageModel->createMessage(
                $ticketId,
                $userId,
                TicketMessage::SENDER_USER,
                $data['message'],
                $data['attachments'] ?? []
            );
        }
        
        // Get the created ticket
        $ticket = $this->ticketModel->find($ticketId);
        
        // Notify admins about new ticket (can be implemented with admin notification system)
        $this->notifyAdminsNewTicket($ticket);
        
        return $ticket;
    }

    /**
     * Add user reply to ticket
     */
    public function addUserReply(int $ticketId, int $userId, string $message, array $attachments = []): bool
    {
        // Create the message
        $this->messageModel->createMessage(
            $ticketId,
            $userId,
            TicketMessage::SENDER_USER,
            $message,
            $attachments
        );
        
        // Update ticket status to open if it was waiting reply
        $ticket = $this->ticketModel->find($ticketId);
        if ($ticket['status'] === SupportTicket::STATUS_WAITING_REPLY) {
            $this->ticketModel->updateStatus($ticketId, SupportTicket::STATUS_OPEN);
        }
        
        // Update ticket timestamp
        $this->ticketModel->update($ticketId, [
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        return true;
    }

    /**
     * Add admin reply to ticket
     */
    public function addAdminReply(int $ticketId, int $adminId, string $message, array $attachments = [], bool $isInternal = false): bool
    {
        // Create the message
        $this->messageModel->createMessage(
            $ticketId,
            $adminId,
            TicketMessage::SENDER_ADMIN,
            $message,
            $attachments,
            $isInternal
        );
        
        // Get ticket info
        $ticket = $this->ticketModel->find($ticketId);
        
        if (!$isInternal) {
            // Update ticket status to waiting reply
            $this->ticketModel->updateStatus($ticketId, SupportTicket::STATUS_WAITING_REPLY);
            
            // Notify user about admin reply
            $this->notificationService->send(
                $ticket['user_id'],
                'New Reply on Ticket #' . $ticket['ticket_number'],
                'An admin has replied to your support ticket: ' . $ticket['subject'],
                'info'
            );
        }
        
        // Update ticket timestamp
        $this->ticketModel->update($ticketId, [
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        return true;
    }

    /**
     * Get ticket with all messages
     */
    public function getTicketWithMessages(int $ticketId, bool $isAdmin = false): ?array
    {
        $ticket = $this->ticketModel->findWithUser($ticketId);
        
        if (!$ticket) {
            return null;
        }
        
        $ticket['messages'] = $this->messageModel->getByTicket($ticketId, $isAdmin);
        $ticket['message_count'] = count($ticket['messages']);
        
        return $ticket;
    }

    /**
     * Get user's tickets
     */
    public function getUserTickets(int $userId, ?string $status = null, int $page = 1, int $perPage = 20): array
    {
        return $this->ticketModel->findByUser($userId, $status, $page, $perPage);
    }

    /**
     * Get all tickets for admin
     */
    public function getAllTickets(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $tickets = $this->ticketModel->getAllTickets($filters, $page, $perPage);
        $total = $this->ticketModel->countAllTickets($filters);
        
        return [
            'tickets' => $tickets,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage)
        ];
    }

    /**
     * Close ticket
     */
    public function closeTicket(int $ticketId, ?int $closedBy = null): bool
    {
        $result = $this->ticketModel->close($ticketId);
        
        if ($result) {
            $ticket = $this->ticketModel->find($ticketId);
            
            // Notify user that ticket was closed
            $this->notificationService->send(
                $ticket['user_id'],
                'Ticket Closed #' . $ticket['ticket_number'],
                'Your support ticket has been closed. If you need further assistance, you can reopen it or create a new ticket.',
                'info'
            );
        }
        
        return $result;
    }

    /**
     * Reopen ticket
     */
    public function reopenTicket(int $ticketId): bool
    {
        return $this->ticketModel->reopen($ticketId);
    }

    /**
     * Assign ticket to admin
     */
    public function assignTicket(int $ticketId, ?int $adminId): bool
    {
        return $this->ticketModel->assign($ticketId, $adminId);
    }

    /**
     * Update ticket status
     */
    public function updateStatus(int $ticketId, string $status): bool
    {
        return $this->ticketModel->updateStatus($ticketId, $status);
    }

    /**
     * Get status counts
     */
    public function getStatusCounts(?int $userId = null): array
    {
        return $this->ticketModel->getStatusCounts($userId);
    }

    /**
     * Get admin statistics
     */
    public function getAdminStats(): array
    {
        return $this->ticketModel->getAdminStats();
    }

    /**
     * Check if user owns ticket
     */
    public function userOwnsTicket(int $ticketId, int $userId): bool
    {
        $ticket = $this->ticketModel->find($ticketId);
        return $ticket && $ticket['user_id'] === $userId;
    }

    /**
     * Notify admins about new ticket
     */
    private function notifyAdminsNewTicket(array $ticket): void
    {
        // This could be expanded to notify specific admins via email or Telegram
        // For now, it's a placeholder for future implementation
    }

    /**
     * Helper method to notify user about ticket events
     */
    public static function notifyUser(int $userId, string $type, array $data): void
    {
        $notificationService = new NotificationService(new Notification());
        
        switch ($type) {
            case 'ticket_reply':
                $notificationService->send(
                    $userId,
                    'New Reply on Ticket',
                    'You have a new reply on your support ticket: ' . ($data['subject'] ?? ''),
                    'info'
                );
                break;
            
            case 'ticket_resolved':
                $notificationService->send(
                    $userId,
                    'Ticket Resolved',
                    'Your support ticket has been marked as resolved.',
                    'success'
                );
                break;
            
            case 'ticket_closed':
                $notificationService->send(
                    $userId,
                    'Ticket Closed',
                    'Your support ticket has been closed.',
                    'info'
                );
                break;
        }
    }
}
