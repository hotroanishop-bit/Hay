<?php
/**
 * Security Controller
 * Handles login history and session management
 */

class SecurityController extends BaseController
{
    private SessionManagementService $sessionService;
    private AuthService $authService;

    public function __construct()
    {
        $sessionServicePHP = new SessionService();
        $userModel = new User();
        $this->authService = new AuthService($sessionServicePHP, $userModel);
        $this->sessionService = new SessionManagementService();
    }

    /**
     * Show login history page
     */
    public function loginHistory(): void
    {
        $user = $this->authService->user();
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;

        $history = $this->sessionService->getLoginHistory($user['id'], $page, $perPage);
        $totalCount = $this->sessionService->countLoginHistory($user['id']);
        $totalPages = ceil($totalCount / $perPage);

        $this->currentPage = 'security-login-history';
        $this->render('security/login_history', [
            'pageTitle' => 'Login History',
            'currentPage' => $this->currentPage,
            'history' => $history,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total' => $totalCount,
                'per_page' => $perPage
            ]
        ], ['security']);
    }

    /**
     * Show active sessions page
     */
    public function sessions(): void
    {
        $user = $this->authService->user();
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        $sessions = $this->sessionService->getActiveSessions($user['id']);
        $currentToken = $_SESSION['session_token'] ?? null;

        $this->currentPage = 'security-sessions';
        $this->render('security/sessions', [
            'pageTitle' => 'Active Sessions',
            'currentPage' => $this->currentPage,
            'sessions' => $sessions,
            'currentToken' => $currentToken
        ], ['security']);
    }

    /**
     * Terminate a specific session
     */
    public function terminateSession(int $id): void
    {
        $user = $this->authService->user();
        if (!$user) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        // Verify CSRF token
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrfToken)) {
            $this->setFlash('error', 'Invalid request');
            $this->redirect('/security/sessions');
            return;
        }

        $result = $this->sessionService->terminateSession($id, $user['id']);

        if ($result) {
            $this->setFlash('success', 'Session terminated successfully');
        } else {
            $this->setFlash('error', 'Failed to terminate session');
        }

        $this->redirect('/security/sessions');
    }

    /**
     * Terminate all sessions except current
     */
    public function terminateAllSessions(): void
    {
        $user = $this->authService->user();
        if (!$user) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        // Verify CSRF token
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrfToken)) {
            $this->setFlash('error', 'Invalid request');
            $this->redirect('/security/sessions');
            return;
        }

        $currentToken = $_SESSION['session_token'] ?? '';
        $count = $this->sessionService->terminateAllOtherSessions($user['id'], $currentToken);

        if ($count > 0) {
            $this->setFlash('success', "Terminated {$count} other session(s)");
        } else {
            $this->setFlash('info', 'No other sessions to terminate');
        }

        $this->redirect('/security/sessions');
    }
}
