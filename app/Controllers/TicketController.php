<?php
/**
 * Ticket Controller
 * Handles support ticket management with enhanced features
 */

class TicketController extends BaseController
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
     * List all tickets for the user
     */
    public function index(): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        $status = $_GET['status'] ?? null;
        $page = (int)($_GET['page'] ?? 1);
        
        $tickets = $this->ticketService->getUserTickets($user['id'], $status, $page);
        $statusCounts = $this->ticketService->getStatusCounts($user['id']);

        $this->currentPage = 'tickets';
        $this->render('tickets/index', [
            'pageTitle' => 'Support Tickets',
            'currentPage' => $this->currentPage,
            'tickets' => $tickets,
            'statusCounts' => $statusCounts,
            'statusFilter' => $status
        ], ['tickets'], ['tickets']);
    }

    /**
     * Show create ticket form
     */
    public function create(): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        $this->currentPage = 'tickets';
        $this->render('tickets/create', [
            'pageTitle' => 'Create Ticket',
            'currentPage' => $this->currentPage,
            'priorities' => SupportTicket::getPriorities(),
            'categories' => SupportTicket::getCategories()
        ], ['tickets'], ['tickets']);
    }

    /**
     * Store a new ticket
     */
    public function store(): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $priority = $_POST['priority'] ?? SupportTicket::PRIORITY_MEDIUM;
        $category = $_POST['category'] ?? SupportTicket::CATEGORY_OTHER;

        // Validation
        $errors = [];
        
        if (empty($subject)) {
            $errors[] = 'Subject is required';
        }
        
        if (strlen($subject) > 255) {
            $errors[] = 'Subject must be less than 255 characters';
        }
        
        if (empty($message)) {
            $errors[] = 'Message is required';
        }

        if (!in_array($priority, array_keys(SupportTicket::getPriorities()))) {
            $priority = SupportTicket::PRIORITY_MEDIUM;
        }

        if (!in_array($category, array_keys(SupportTicket::getCategories()))) {
            $category = SupportTicket::CATEGORY_OTHER;
        }

        if (!empty($errors)) {
            $this->setFlash('error', implode('. ', $errors));
            $this->redirect('/tickets/create');
            return;
        }

        try {
            $ticket = $this->ticketService->createTicket($user['id'], [
                'subject' => $subject,
                'message' => $message,
                'priority' => $priority,
                'category' => $category
            ]);

            $this->auditService->log($user['id'], 'ticket_created', [
                'ticket_id' => $ticket['id'],
                'ticket_number' => $ticket['ticket_number'],
                'subject' => $subject
            ]);

            $this->setFlash('success', 'Ticket created successfully! Ticket #' . $ticket['ticket_number']);
            $this->redirect('/tickets/' . $ticket['id']);
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to create ticket: ' . $e->getMessage());
            $this->redirect('/tickets/create');
        }
    }

    /**
     * Show a specific ticket with conversation
     */
    public function show(int $id): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        // Check ownership
        if (!$this->ticketService->userOwnsTicket($id, $user['id'])) {
            $this->setFlash('error', 'Ticket not found');
            $this->redirect('/tickets');
            return;
        }

        $ticket = $this->ticketService->getTicketWithMessages($id, false);
        
        if (!$ticket) {
            $this->setFlash('error', 'Ticket not found');
            $this->redirect('/tickets');
            return;
        }

        $this->currentPage = 'tickets';
        $this->render('tickets/show', [
            'pageTitle' => 'Ticket #' . $ticket['ticket_number'],
            'currentPage' => $this->currentPage,
            'ticket' => $ticket,
            'replies' => $ticket['messages'] ?? [],
            'user' => $user
        ], ['tickets'], ['tickets']);
    }

    /**
     * Reply to a ticket (user reply)
     */
    public function reply(int $id): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        // Check ownership
        if (!$this->ticketService->userOwnsTicket($id, $user['id'])) {
            $this->setFlash('error', 'Ticket not found');
            $this->redirect('/tickets');
            return;
        }

        $message = trim($_POST['message'] ?? $_POST['reply'] ?? '');

        if (empty($message)) {
            $this->setFlash('error', 'Reply message is required');
            $this->redirect('/tickets/' . $id);
            return;
        }

        try {
            $this->ticketService->addUserReply($id, $user['id'], $message);

            $this->auditService->log($user['id'], 'ticket_reply', [
                'ticket_id' => $id
            ]);

            $this->setFlash('success', 'Reply added successfully');
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to add reply: ' . $e->getMessage());
        }

        $this->redirect('/tickets/' . $id);
    }

    /**
     * Close a ticket (user can close their own tickets)
     */
    public function close(int $id): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        // Check ownership
        if (!$this->ticketService->userOwnsTicket($id, $user['id'])) {
            $this->setFlash('error', 'Ticket not found');
            $this->redirect('/tickets');
            return;
        }

        try {
            $this->ticketService->closeTicket($id, $user['id']);

            $this->auditService->log($user['id'], 'ticket_closed', [
                'ticket_id' => $id
            ]);

            $this->setFlash('success', 'Ticket closed successfully');
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to close ticket: ' . $e->getMessage());
        }

        $this->redirect('/tickets/' . $id);
    }
}
