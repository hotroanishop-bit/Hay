<?php
/**
 * Search Controller
 * Handles quick search / command palette functionality
 */

class SearchController extends BaseController
{
    private AuthService $authService;

    public function __construct()
    {
        $sessionService = new SessionService();
        $userModel = new User();
        $this->authService = new AuthService($sessionService, $userModel);
    }

    /**
     * GET /api/search - Global search
     */
    public function search(): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $query = trim($_GET['q'] ?? '');
        
        if (strlen($query) < 2) {
            $this->json(['results' => [], 'message' => 'Query too short']);
            return;
        }

        $results = [
            'pages' => $this->searchPages($query, $user),
            'api_keys' => $this->searchApiKeys($query, $user['id']),
            'transactions' => $this->searchTransactions($query, $user['id']),
            'tickets' => $this->searchTickets($query, $user['id'])
        ];

        // Save search history
        $this->saveSearchHistory($user['id'], $query);

        $this->json([
            'success' => true,
            'results' => $results
        ]);
    }

    /**
     * GET /api/search/recent - Get recent searches
     */
    public function recent(): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $db = Database::getInstance();
        $sql = "SELECT query, MAX(created_at) as last_searched
                FROM search_history 
                WHERE user_id = ?
                GROUP BY query
                ORDER BY last_searched DESC
                LIMIT 10";
        $stmt = $db->prepare($sql);
        $stmt->execute([$user['id']]);
        $recentSearches = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->json([
            'success' => true,
            'recent' => $recentSearches
        ]);
    }

    /**
     * DELETE /api/search/history - Clear search history
     */
    public function clearHistory(): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $db = Database::getInstance();
        $sql = "DELETE FROM search_history WHERE user_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$user['id']]);

        $this->json(['success' => true]);
    }

    /**
     * Search pages/navigation items
     */
    private function searchPages(string $query, array $user): array
    {
        $pages = [
            ['name' => 'Dashboard', 'url' => '/dashboard', 'icon' => 'home', 'keywords' => 'dashboard trang chu home'],
            ['name' => 'API Keys', 'url' => '/keys', 'icon' => 'key', 'keywords' => 'api keys khoa key'],
            ['name' => 'Tao API Key', 'url' => '/keys/create', 'icon' => 'plus', 'keywords' => 'create new api key tao moi'],
            ['name' => 'Billing', 'url' => '/billing', 'icon' => 'credit-card', 'keywords' => 'billing thanh toan'],
            ['name' => 'Nap tien', 'url' => '/billing/deposit', 'icon' => 'dollar-sign', 'keywords' => 'deposit nap tien'],
            ['name' => 'Analytics', 'url' => '/analytics', 'icon' => 'bar-chart', 'keywords' => 'analytics thong ke'],
            ['name' => 'Playground', 'url' => '/playground', 'icon' => 'terminal', 'keywords' => 'playground test api'],
            ['name' => 'Profile', 'url' => '/profile', 'icon' => 'user', 'keywords' => 'profile ho so'],
            ['name' => 'Tickets', 'url' => '/tickets', 'icon' => 'message-square', 'keywords' => 'tickets support ho tro'],
            ['name' => 'Referral', 'url' => '/referral', 'icon' => 'users', 'keywords' => 'referral gioi thieu'],
            ['name' => 'Webhooks', 'url' => '/webhooks', 'icon' => 'link', 'keywords' => 'webhooks'],
            ['name' => 'Check-in', 'url' => '/checkin', 'icon' => 'calendar', 'keywords' => 'checkin diem danh'],
            ['name' => 'Achievements', 'url' => '/achievements', 'icon' => 'award', 'keywords' => 'achievements thanh tuu'],
            ['name' => 'Leaderboard', 'url' => '/leaderboard', 'icon' => 'trophy', 'keywords' => 'leaderboard bang xep hang'],
            ['name' => 'Feedback', 'url' => '/feedback', 'icon' => 'star', 'keywords' => 'feedback danh gia'],
            ['name' => 'Settings', 'url' => '/settings', 'icon' => 'settings', 'keywords' => 'settings cai dat'],
        ];
        
        // Add admin pages if user is admin
        if (!empty($user['is_admin'])) {
            $pages[] = ['name' => 'Admin Dashboard', 'url' => '/admin', 'icon' => 'shield', 'keywords' => 'admin dashboard'];
            $pages[] = ['name' => 'Admin Users', 'url' => '/admin/users', 'icon' => 'users', 'keywords' => 'admin users quan ly'];
            $pages[] = ['name' => 'Admin Deposits', 'url' => '/admin/deposits', 'icon' => 'credit-card', 'keywords' => 'admin deposits nap tien'];
            $pages[] = ['name' => 'Admin Tickets', 'url' => '/admin/tickets', 'icon' => 'message-square', 'keywords' => 'admin tickets'];
            $pages[] = ['name' => 'Admin Chat', 'url' => '/admin/chat', 'icon' => 'message-circle', 'keywords' => 'admin chat'];
        }

        $queryLower = mb_strtolower($query);
        $results = [];
        
        foreach ($pages as $page) {
            $searchText = mb_strtolower($page['name'] . ' ' . $page['keywords']);
            if (mb_strpos($searchText, $queryLower) !== false) {
                $results[] = [
                    'type' => 'page',
                    'name' => $page['name'],
                    'url' => $page['url'],
                    'icon' => $page['icon']
                ];
            }
        }

        return array_slice($results, 0, 5);
    }

    /**
     * Search API keys
     */
    private function searchApiKeys(string $query, int $userId): array
    {
        $db = Database::getInstance();
        $searchTerm = '%' . $query . '%';
        
        $sql = "SELECT id, name, CONCAT('sk-...', RIGHT(api_key, 4)) as key_hint, status
                FROM api_keys 
                WHERE user_id = ? AND (name LIKE ? OR api_key LIKE ?)
                ORDER BY created_at DESC
                LIMIT 5";
        $stmt = $db->prepare($sql);
        $stmt->execute([$userId, $searchTerm, $searchTerm]);
        $keys = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(function($key) {
            return [
                'type' => 'api_key',
                'id' => $key['id'],
                'name' => $key['name'],
                'hint' => $key['key_hint'],
                'status' => $key['status'],
                'url' => '/keys/' . $key['id']
            ];
        }, $keys);
    }

    /**
     * Search transactions
     */
    private function searchTransactions(string $query, int $userId): array
    {
        $db = Database::getInstance();
        $searchTerm = '%' . $query . '%';
        
        $sql = "SELECT id, type, amount, description, created_at
                FROM transactions 
                WHERE user_id = ? AND (description LIKE ? OR CAST(amount AS CHAR) LIKE ?)
                ORDER BY created_at DESC
                LIMIT 5";
        $stmt = $db->prepare($sql);
        $stmt->execute([$userId, $searchTerm, $searchTerm]);
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(function($tx) {
            return [
                'type' => 'transaction',
                'id' => $tx['id'],
                'tx_type' => $tx['type'],
                'amount' => number_format($tx['amount'], 2),
                'description' => mb_substr($tx['description'] ?? '', 0, 50),
                'date' => $tx['created_at'],
                'url' => '/billing/history'
            ];
        }, $transactions);
    }

    /**
     * Search tickets
     */
    private function searchTickets(string $query, int $userId): array
    {
        $db = Database::getInstance();
        $searchTerm = '%' . $query . '%';
        
        // Check both old tickets table and new support_tickets table
        $sql = "SELECT id, subject, status, created_at
                FROM tickets 
                WHERE user_id = ? AND subject LIKE ?
                ORDER BY created_at DESC
                LIMIT 5";
        
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([$userId, $searchTerm]);
            $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Try support_tickets table if tickets doesn't exist
            $sql = "SELECT id, subject, status, created_at
                    FROM support_tickets 
                    WHERE user_id = ? AND subject LIKE ?
                    ORDER BY created_at DESC
                    LIMIT 5";
            $stmt = $db->prepare($sql);
            $stmt->execute([$userId, $searchTerm]);
            $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return array_map(function($ticket) {
            return [
                'type' => 'ticket',
                'id' => $ticket['id'],
                'subject' => mb_substr($ticket['subject'], 0, 50),
                'status' => $ticket['status'],
                'date' => $ticket['created_at'],
                'url' => '/tickets/' . $ticket['id']
            ];
        }, $tickets);
    }

    /**
     * Save search to history
     */
    private function saveSearchHistory(int $userId, string $query): void
    {
        $db = Database::getInstance();
        
        try {
            $sql = "INSERT INTO search_history (user_id, query, created_at) VALUES (?, ?, NOW())";
            $stmt = $db->prepare($sql);
            $stmt->execute([$userId, $query]);
            
            // Keep only last 100 searches per user
            $cleanupSql = "DELETE FROM search_history WHERE user_id = ? AND id NOT IN (
                SELECT id FROM (SELECT id FROM search_history WHERE user_id = ? ORDER BY created_at DESC LIMIT 100) AS t
            )";
            $cleanupStmt = $db->prepare($cleanupSql);
            $cleanupStmt->execute([$userId, $userId]);
        } catch (PDOException $e) {
            // Silently fail if table doesn't exist yet
        }
    }
}
