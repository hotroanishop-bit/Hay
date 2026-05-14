<?php
/**
 * Ticket Controller
 * Handles support ticket management
 */

class TicketController extends BaseController
{
    private AuthService $authService;
    private AuditService $auditService;
    private Ticket $ticketModel;

    public function __construct()
    {
        $sessionService = new SessionService();
        $userModel = new User();
        $this->authService = new AuthService($sessionService, $userModel);
        
        $this->ticketModel = new Ticket();
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

        $tickets = $this->ticketModel->findByUser($user['id']);
        $statusCounts = $this->ticketModel->getStatusCounts($user['id']);

        $this->currentPage = 'tickets';
        $this->render('tickets/index', [
            'pageTitle' => 'Support Tickets',
            'currentPage' => $this->currentPage,
            'tickets' => $tickets,
            'statusCounts' => $statusCounts
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
            'priorities' => [
                Ticket::PRIORITY_LOW => 'Low',
                Ticket::PRIORITY_MEDIUM => 'Medium',
                Ticket::PRIORITY_HIGH => 'High'
            ]
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
        $priority = $_POST['priority'] ?? Ticket::PRIORITY_MEDIUM;

        // Validation
        $errors = [];
        
        if (empty($subject)) {
            $errors[] = 'Subject is required';
        }
        
        if (empty($message)) {
            $errors[] = 'Message is required';
        }

        if (!empty($errors)) {
            $this->setFlash('error', implode('. ', $errors));
            $this->redirect('/tickets/create');
            return;
        }

        try {
            $ticketId = $this->ticketModel->createTicket($user['id'], [
                'subject' => $subject,
                'message' => $message,
                'priority' => $priority
            ]);

            $this->auditService->log($user['id'], 'ticket_created', [
                'ticket_id' => $ticketId,
                'subject' => $subject
            ]);

            $this->setFlash('success', 'Ticket created successfully');
            $this->redirect('/tickets/' . $ticketId);
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to create ticket: ' . $e->getMessage());
            $this->redirect('/tickets/create');
        }
    }

    /**
     * Show a specific ticket
     */
    public function show(int $id): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        $ticket = $this->ticketModel->find($id);

        // Check ownership
        if (!$ticket || $ticket['user_id'] !== $user['id']) {
            $this->setFlash('error', 'Ticket not found');
            $this->redirect('/tickets');
            return;
        }

        $this->currentPage = 'tickets';
        $this->render('tickets/show', [
            'pageTitle' => 'Ticket #' . $id,
            'currentPage' => $this->currentPage,
            'ticket' => $ticket
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

        $ticket = $this->ticketModel->find($id);

        // Check ownership
        if (!$ticket || $ticket['user_id'] !== $user['id']) {
            $this->setFlash('error', 'Ticket not found');
            $this->redirect('/tickets');
            return;
        }

        $reply = trim($_POST['reply'] ?? '');

        if (empty($reply)) {
            $this->setFlash('error', 'Reply message is required');
            $this->redirect('/tickets/' . $id);
            return;
        }

        try {
            // Append reply to existing message
            $newMessage = $ticket['message'] . "\n\n---\n**User Reply (" . date('Y-m-d H:i') . "):**\n" . $reply;
            
            $this->ticketModel->update($id, [
                'message' => $newMessage,
                'status' => Ticket::STATUS_OPEN,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $this->auditService->log($user['id'], 'ticket_reply', [
                'ticket_id' => $id
            ]);

            $this->setFlash('success', 'Reply added successfully');
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to add reply: ' . $e->getMessage());
        }

        $this->redirect('/tickets/' . $id);
    }
}
