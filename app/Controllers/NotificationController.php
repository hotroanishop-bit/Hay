<?php
/**
 * Notification Controller
 * Handles user notifications display and management
 */

class NotificationController extends BaseController
{
    private NotificationService $notificationService;
    private AuthService $authService;

    public function __construct()
    {
        $sessionService = new SessionService();
        $userModel = new User();
        $this->authService = new AuthService($sessionService, $userModel);
        $this->notificationService = new NotificationService(new Notification());
    }

    /**
     * GET /notifications - Full notifications page
     */
    public function index(): void
    {
        $user = $this->authService->user();
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        $this->currentPage = 'notifications';
        $this->render('notifications/index', [
            'pageTitle' => 'Notifications',
            'currentPage' => $this->currentPage
        ]);
    }

    /**
     * GET /api/notifications/unread-count - JSON for AJAX
     */
    public function getUnreadCount(): void
    {
        $user = $this->authService->user();
        if (!$user) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $count = $this->notificationService->getUnreadCount($user['id']);
        $this->json(['count' => $count]);
    }

    /**
     * GET /api/notifications/recent - Recent notifications JSON
     */
    public function getRecent(): void
    {
        $user = $this->authService->user();
        if (!$user) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $notifications = $this->notificationService->getUnread($user['id']);
        $this->json(['notifications' => array_slice($notifications, 0, 10)]);
    }

    /**
     * GET /api/notifications - Paginated list
     */
    public function list(): void
    {
        $user = $this->authService->user();
        if (!$user) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $page = (int)($_GET['page'] ?? 1);
        $perPage = (int)($_GET['per_page'] ?? 20);
        $filter = $_GET['filter'] ?? 'all';

        // Get notifications with pagination and filter
        $result = $this->notificationService->getForUser($user['id'], $page, $perPage, $filter);
        $this->json($result);
    }

    /**
     * GET /api/notifications/counts - Get notification counts by type
     */
    public function getCounts(): void
    {
        $user = $this->authService->user();
        if (!$user) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $result = $this->notificationService->getCounts($user['id']);
        $this->json($result);
    }

    /**
     * POST /notifications/{id}/read - Mark single as read
     */
    public function markRead(int $id): void
    {
        $user = $this->authService->user();
        if (!$user) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $this->notificationService->markRead($id);
        $this->json(['success' => true]);
    }

    /**
     * POST /notifications/read-all - Mark all as read
     */
    public function markAllRead(): void
    {
        $user = $this->authService->user();
        if (!$user) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $this->notificationService->markAllRead($user['id']);
        $this->json(['success' => true]);
    }

    /**
     * DELETE /notifications/{id} or POST /notifications/{id}/delete
     */
    public function delete(int $id): void
    {
        $user = $this->authService->user();
        if (!$user) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $this->notificationService->delete($id);
        $this->json(['success' => true]);
    }

    /**
     * DELETE /api/notifications/delete-read - Delete all read notifications
     */
    public function deleteRead(): void
    {
        $user = $this->authService->user();
        if (!$user) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $this->notificationService->deleteRead($user['id']);
        $this->json(['success' => true]);
    }
}
