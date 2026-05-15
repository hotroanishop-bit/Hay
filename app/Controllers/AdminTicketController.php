<?php
/**
 * Admin Ticket Controller
 * Handles admin support ticket management
 */

class AdminTicketController extends BaseController
{
    private AuthService $authService;
    private AuditService $auditService;
    private TicketService $ticketService;

    public function __construct()
    {
        $sessionService = new SessionService();
        $userModel = new User();
        $this->authService = new AuthService($sessionService, $userModel);
        
        $this->ticketService = new TicketService();
        $this->auditService = new AuditService();
    }

    /**
     * List all tickets (admin view)
     */
    public function index(): void
    {
        $user = $this->authService->user();
        
        if (!$user || !($user['is_admin'] ?? false)) {
            $this->redirect('/login');
            return;
        }

        $filters = [
            'status' => $_GET['status'] ?? null,
            'priority' => $_GET['priority'] ?? null,
            'category' => $_GET['category'] ?? null,
            'assigned_to' => $_GET['assigned_to'] ?? null,
            'search' => $_GET['search'] ?? null
        ];
        
        // Remove null values
        $filters = array_filter($filters);
        
        $page = (int)($_GET['page'] ?? 1);
        $perPage = 20;
        
        $result = $this->ticketService->getAllTickets($filters, $page, $perPage);
        $stats = $this->ticketService->getAdminStats();
        
        // Get all admins for assignment dropdown
        $userModel = new User();
        $admins = $userModel->findAll(['is_admin' => 1]);

        $this->currentPage = 'admin-tickets';
        $this->render('admin/tickets/index', [
            'pageTitle' => 'Support Tickets',
            'currentPage' => $this->currentPage,
            'tickets' => $result['tickets'],
            'total' => $result['total'],
            'page' => $result['page'],
            'totalPages' => $result['total_pages'],
            'filters' => $filters,
            'stats' => $stats,
            'admins' => $admins,
            'statuses' => SupportTicket::getStatuses(),
            'priorities' => SupportTicket::getPriorities(),
            'categories' => SupportTicket::getCategories()
        ]);
    }

    /**
     * Show ticket detail (admin view)
     */
    public function show(int $id): void
    {
        $user = $this->authService->user();
        
        if (!$user || !($user['is_admin'] ?? false)) {
            $this->redirect('/login');
            return;
        }

        $ticket = $this->ticketService->getTicketWithMessages($id, true);
        
        if (!$ticket) {
            $this->setFlash('error', 'Ticket not found');
            $this->redirect('/admin/tickets');
            return;
        }

        // Get all admins for assignment
        $userModel = new User();
        $admins = $userModel->findAll(['is_admin' => 1]);

        $this->currentPage = 'admin-tickets';
        $this->render('admin/tickets/view', [
            'pageTitle' => 'Ticket #' . $ticket['ticket_number'],
            'currentPage' => $this->currentPage,
            'ticket' => $ticket,
            'messages' => $ticket['messages'] ?? [],
            'admins' => $admins,
            'statuses' => SupportTicket::getStatuses(),
            'priorities' => SupportTicket::getPriorities(),
            'admin' => $user
        ]);
    }

    /**
     * Reply to ticket (admin reply)
     */
    public function reply(int $id): void
    {
        $user = $this->authService->user();
        
        if (!$user || !($user['is_admin'] ?? false)) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $message = trim($_POST['message'] ?? '');
        $isInternal = isset($_POST['is_internal']) && $_POST['is_internal'] === '1';

        if (empty($message)) {
            $this->setFlash('error', 'Reply message is required');
            $this->redirect('/admin/tickets/' . $id);
            return;
        }

        try {
            $this->ticketService->addAdminReply($id, $user['id'], $message, [], $isInternal);

            $this->auditService->log($user['id'], 'admin_ticket_reply', [
                'ticket_id' => $id,
                'is_internal' => $isInternal
            ]);

            $this->setFlash('success', $isInternal ? 'Internal note added' : 'Reply sent to user');
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to add reply: ' . $e->getMessage());
        }

        $this->redirect('/admin/tickets/' . $id);
    }

    /**
     * Update ticket status
     */
    public function updateStatus(int $id): void
    {
        $user = $this->authService->user();
        
        if (!$user || !($user['is_admin'] ?? false)) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $status = $_POST['status'] ?? '';

        if (!in_array($status, array_keys(SupportTicket::getStatuses()))) {
            $this->setFlash('error', 'Invalid status');
            $this->redirect('/admin/tickets/' . $id);
            return;
        }

        try {
            $this->ticketService->updateStatus($id, $status);

            $this->auditService->log($user['id'], 'admin_ticket_status_update', [
                'ticket_id' => $id,
                'status' => $status
            ]);

            $this->setFlash('success', 'Ticket status updated');
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to update status: ' . $e->getMessage());
        }

        $this->redirect('/admin/tickets/' . $id);
    }

    /**
     * Assign ticket to admin
     */
    public function assign(int $id): void
    {
        $user = $this->authService->user();
        
        if (!$user || !($user['is_admin'] ?? false)) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $assignTo = $_POST['assigned_to'] ?? null;
        $assignTo = $assignTo === '' ? null : (int)$assignTo;

        try {
            $this->ticketService->assignTicket($id, $assignTo);

            $this->auditService->log($user['id'], 'admin_ticket_assign', [
                'ticket_id' => $id,
                'assigned_to' => $assignTo
            ]);

            $this->setFlash('success', $assignTo ? 'Ticket assigned' : 'Ticket unassigned');
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to assign ticket: ' . $e->getMessage());
        }

        $this->redirect('/admin/tickets/' . $id);
    }

    /**
     * Close ticket (admin)
     */
    public function close(int $id): void
    {
        $user = $this->authService->user();
        
        if (!$user || !($user['is_admin'] ?? false)) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        try {
            $this->ticketService->closeTicket($id, $user['id']);

            $this->auditService->log($user['id'], 'admin_ticket_closed', [
                'ticket_id' => $id
            ]);

            $this->setFlash('success', 'Ticket closed');
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to close ticket: ' . $e->getMessage());
        }

        $this->redirect('/admin/tickets/' . $id);
    }

    /**
     * Reopen ticket (admin)
     */
    public function reopen(int $id): void
    {
        $user = $this->authService->user();
        
        if (!$user || !($user['is_admin'] ?? false)) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        try {
            $this->ticketService->reopenTicket($id);

            $this->auditService->log($user['id'], 'admin_ticket_reopened', [
                'ticket_id' => $id
            ]);

            $this->setFlash('success', 'Ticket reopened');
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to reopen ticket: ' . $e->getMessage());
        }

        $this->redirect('/admin/tickets/' . $id);
    }

    /**
     * Bulk action on tickets
     */
    public function bulkAction(): void
    {
        $user = $this->authService->user();
        
        if (!$user || !($user['is_admin'] ?? false)) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $action = $_POST['action'] ?? '';
        $ticketIds = $_POST['ticket_ids'] ?? [];

        if (empty($ticketIds) || !is_array($ticketIds)) {
            $this->setFlash('error', 'No tickets selected');
            $this->redirect('/admin/tickets');
            return;
        }

        $count = 0;
        foreach ($ticketIds as $ticketId) {
            try {
                switch ($action) {
                    case 'close':
                        $this->ticketService->closeTicket((int)$ticketId, $user['id']);
                        $count++;
                        break;
                    
                    case 'resolve':
                        $this->ticketService->updateStatus((int)$ticketId, SupportTicket::STATUS_RESOLVED);
                        $count++;
                        break;
                    
                    case 'assign_to_me':
                        $this->ticketService->assignTicket((int)$ticketId, $user['id']);
                        $count++;
                        break;
                }
            } catch (Exception $e) {
                // Continue with next ticket
            }
        }

        $this->auditService->log($user['id'], 'admin_tickets_bulk_action', [
            'action' => $action,
            'count' => $count
        ]);

        $this->setFlash('success', "Action applied to {$count} ticket(s)");
        $this->redirect('/admin/tickets');
    }
}
