<?php
/**
 * Webhook Controller
 * Handles webhook management for users
 */

class WebhookController extends BaseController
{
    private AuthService $authService;
    private Webhook $webhookModel;
    private WebhookLog $webhookLogModel;
    private WebhookService $webhookService;
    private AuditService $auditService;

    public function __construct()
    {
        $sessionService = new SessionService();
        $userModel = new User();
        $this->authService = new AuthService($sessionService, $userModel);
        
        $this->webhookModel = new Webhook();
        $this->webhookLogModel = new WebhookLog();
        $this->webhookService = new WebhookService();
        $this->auditService = new AuditService();
    }

    /**
     * List user's webhooks
     * GET /webhooks
     */
    public function index(): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        $webhooks = $this->webhookModel->getByUser($user['id']);
        
        // Get stats for each webhook
        foreach ($webhooks as &$webhook) {
            $webhook['stats'] = $this->webhookLogModel->getStats($webhook['id']);
        }

        $this->currentPage = 'webhooks';
        $this->render('webhooks/index', [
            'pageTitle' => 'Webhooks',
            'currentPage' => $this->currentPage,
            'webhooks' => $webhooks,
            'availableEvents' => Webhook::EVENTS
        ], ['webhooks'], ['webhooks']);
    }

    /**
     * Show create webhook form
     * GET /webhooks/create
     */
    public function create(): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        $this->currentPage = 'webhooks';
        $this->render('webhooks/form', [
            'pageTitle' => 'Create Webhook',
            'currentPage' => $this->currentPage,
            'webhook' => null,
            'availableEvents' => Webhook::EVENTS,
            'generatedSecret' => Webhook::generateSecret(),
            'isEdit' => false
        ], ['webhooks'], ['webhooks']);
    }

    /**
     * Store new webhook
     * POST /webhooks
     */
    public function store(): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        // Validate input
        $url = trim($_POST['url'] ?? '');
        $secret = trim($_POST['secret'] ?? '');
        $events = $_POST['events'] ?? [];
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        // Validation
        if (empty($url)) {
            $this->setFlash('error', 'Webhook URL is required');
            $this->redirect('/webhooks/create');
            return;
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $this->setFlash('error', 'Invalid webhook URL format');
            $this->redirect('/webhooks/create');
            return;
        }

        // Only allow HTTPS URLs for security
        if (strpos($url, 'https://') !== 0) {
            $this->setFlash('error', 'Webhook URL must use HTTPS');
            $this->redirect('/webhooks/create');
            return;
        }

        if (empty($secret) || strlen($secret) < 16) {
            $this->setFlash('error', 'Secret key must be at least 16 characters');
            $this->redirect('/webhooks/create');
            return;
        }

        if (strlen($secret) > 64) {
            $this->setFlash('error', 'Secret key cannot exceed 64 characters');
            $this->redirect('/webhooks/create');
            return;
        }

        if (empty($events) || !is_array($events)) {
            $this->setFlash('error', 'Please select at least one event');
            $this->redirect('/webhooks/create');
            return;
        }

        // Filter valid events only
        $validEvents = array_keys(Webhook::EVENTS);
        $events = array_values(array_intersect($events, $validEvents));

        if (empty($events)) {
            $this->setFlash('error', 'Please select at least one valid event');
            $this->redirect('/webhooks/create');
            return;
        }

        // Limit webhooks per user
        $webhookCount = $this->webhookModel->countByUser($user['id']);
        if ($webhookCount >= 10) {
            $this->setFlash('error', 'Maximum of 10 webhooks allowed per user');
            $this->redirect('/webhooks');
            return;
        }

        // Create webhook
        $webhookId = $this->webhookModel->createWebhook([
            'user_id' => $user['id'],
            'url' => $url,
            'secret' => $secret,
            'events' => $events,
            'is_active' => $isActive
        ]);

        // Log audit
        $this->auditService->log($user['id'], 'webhook_created', [
            'webhook_id' => $webhookId,
            'url' => $url,
            'events' => $events
        ]);

        $this->setFlash('success', 'Webhook created successfully');
        $this->redirect('/webhooks');
    }

    /**
     * Show edit webhook form
     * GET /webhooks/{id}/edit
     */
    public function edit(int $id): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        $webhook = $this->webhookModel->findWithEvents($id);

        if (!$webhook) {
            $this->setFlash('error', 'Webhook not found');
            $this->redirect('/webhooks');
            return;
        }

        // Verify ownership
        if ($webhook['user_id'] != $user['id']) {
            $this->setFlash('error', 'Access denied');
            $this->redirect('/webhooks');
            return;
        }

        $this->currentPage = 'webhooks';
        $this->render('webhooks/form', [
            'pageTitle' => 'Edit Webhook',
            'currentPage' => $this->currentPage,
            'webhook' => $webhook,
            'availableEvents' => Webhook::EVENTS,
            'generatedSecret' => null,
            'isEdit' => true
        ], ['webhooks'], ['webhooks']);
    }

    /**
     * Update webhook
     * POST /webhooks/{id}
     */
    public function update(int $id): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        $webhook = $this->webhookModel->find($id);

        if (!$webhook) {
            $this->setFlash('error', 'Webhook not found');
            $this->redirect('/webhooks');
            return;
        }

        // Verify ownership
        if ($webhook['user_id'] != $user['id']) {
            $this->setFlash('error', 'Access denied');
            $this->redirect('/webhooks');
            return;
        }

        // Validate input
        $url = trim($_POST['url'] ?? '');
        $secret = trim($_POST['secret'] ?? '');
        $events = $_POST['events'] ?? [];
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        // Validation
        if (empty($url)) {
            $this->setFlash('error', 'Webhook URL is required');
            $this->redirect('/webhooks/' . $id . '/edit');
            return;
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $this->setFlash('error', 'Invalid webhook URL format');
            $this->redirect('/webhooks/' . $id . '/edit');
            return;
        }

        if (strpos($url, 'https://') !== 0) {
            $this->setFlash('error', 'Webhook URL must use HTTPS');
            $this->redirect('/webhooks/' . $id . '/edit');
            return;
        }

        if (empty($secret) || strlen($secret) < 16) {
            $this->setFlash('error', 'Secret key must be at least 16 characters');
            $this->redirect('/webhooks/' . $id . '/edit');
            return;
        }

        if (strlen($secret) > 64) {
            $this->setFlash('error', 'Secret key cannot exceed 64 characters');
            $this->redirect('/webhooks/' . $id . '/edit');
            return;
        }

        if (empty($events) || !is_array($events)) {
            $this->setFlash('error', 'Please select at least one event');
            $this->redirect('/webhooks/' . $id . '/edit');
            return;
        }

        // Filter valid events only
        $validEvents = array_keys(Webhook::EVENTS);
        $events = array_values(array_intersect($events, $validEvents));

        if (empty($events)) {
            $this->setFlash('error', 'Please select at least one valid event');
            $this->redirect('/webhooks/' . $id . '/edit');
            return;
        }

        // Update webhook
        $this->webhookModel->updateWebhook($id, [
            'url' => $url,
            'secret' => $secret,
            'events' => $events,
            'is_active' => $isActive
        ]);

        // Log audit
        $this->auditService->log($user['id'], 'webhook_updated', [
            'webhook_id' => $id,
            'url' => $url,
            'events' => $events
        ]);

        $this->setFlash('success', 'Webhook updated successfully');
        $this->redirect('/webhooks');
    }

    /**
     * Delete webhook
     * POST /webhooks/{id}/delete
     */
    public function delete(int $id): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        $webhook = $this->webhookModel->find($id);

        if (!$webhook) {
            $this->setFlash('error', 'Webhook not found');
            $this->redirect('/webhooks');
            return;
        }

        // Verify ownership
        if ($webhook['user_id'] != $user['id']) {
            $this->setFlash('error', 'Access denied');
            $this->redirect('/webhooks');
            return;
        }

        // Delete webhook (cascade deletes logs)
        $this->webhookModel->deleteWebhook($id);

        // Log audit
        $this->auditService->log($user['id'], 'webhook_deleted', [
            'webhook_id' => $id,
            'url' => $webhook['url']
        ]);

        $this->setFlash('success', 'Webhook deleted successfully');
        $this->redirect('/webhooks');
    }

    /**
     * View webhook logs
     * GET /webhooks/{id}/logs
     */
    public function logs(int $id): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        $webhook = $this->webhookModel->findWithEvents($id);

        if (!$webhook) {
            $this->setFlash('error', 'Webhook not found');
            $this->redirect('/webhooks');
            return;
        }

        // Verify ownership
        if ($webhook['user_id'] != $user['id']) {
            $this->setFlash('error', 'Access denied');
            $this->redirect('/webhooks');
            return;
        }

        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $logsData = $this->webhookLogModel->getLogsPaginated($id, $page, 20);
        $stats = $this->webhookLogModel->getStats($id);
        $eventCounts = $this->webhookLogModel->getEventCounts($id);

        $this->currentPage = 'webhooks';
        $this->render('webhooks/logs', [
            'pageTitle' => 'Webhook Logs',
            'currentPage' => $this->currentPage,
            'webhook' => $webhook,
            'logs' => $logsData['logs'],
            'pagination' => [
                'total' => $logsData['total'],
                'page' => $logsData['page'],
                'per_page' => $logsData['per_page'],
                'has_more' => $logsData['has_more']
            ],
            'stats' => $stats,
            'eventCounts' => $eventCounts,
            'availableEvents' => Webhook::EVENTS
        ], ['webhooks'], ['webhooks']);
    }

    /**
     * Send test webhook
     * POST /webhooks/{id}/test
     */
    public function test(int $id): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $webhook = $this->webhookModel->findWithEvents($id);

        if (!$webhook) {
            $this->json(['error' => 'Webhook not found'], 404);
            return;
        }

        // Verify ownership
        if ($webhook['user_id'] != $user['id']) {
            $this->json(['error' => 'Access denied'], 403);
            return;
        }

        // Send test webhook
        $result = $this->webhookService->sendTestWebhook($webhook);

        // Log audit
        $this->auditService->log($user['id'], 'webhook_tested', [
            'webhook_id' => $id,
            'success' => $result['success'],
            'response_code' => $result['response_code']
        ]);

        $this->json([
            'success' => $result['success'],
            'response_code' => $result['response_code'],
            'response_body' => $result['response_body'],
            'attempts' => $result['attempts'],
            'message' => $result['success'] 
                ? 'Test webhook sent successfully' 
                : 'Test webhook failed: ' . ($result['response_body'] ?? 'Unknown error')
        ]);
    }

    /**
     * Toggle webhook active status
     * POST /webhooks/{id}/toggle
     */
    public function toggle(int $id): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $webhook = $this->webhookModel->find($id);

        if (!$webhook) {
            $this->json(['error' => 'Webhook not found'], 404);
            return;
        }

        // Verify ownership
        if ($webhook['user_id'] != $user['id']) {
            $this->json(['error' => 'Access denied'], 403);
            return;
        }

        $this->webhookModel->toggleActive($id);
        
        $newStatus = !$webhook['is_active'];

        // Log audit
        $this->auditService->log($user['id'], 'webhook_toggled', [
            'webhook_id' => $id,
            'is_active' => $newStatus
        ]);

        $this->json([
            'success' => true,
            'is_active' => $newStatus,
            'message' => $newStatus ? 'Webhook activated' : 'Webhook deactivated'
        ]);
    }
}
