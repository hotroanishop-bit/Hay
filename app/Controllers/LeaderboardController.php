<?php
/**
 * Leaderboard Controller
 * Displays user rankings for various metrics
 */

class LeaderboardController extends BaseController
{
    private AuthService $authService;
    private User $userModel;

    public function __construct()
    {
        $sessionService = new SessionService();
        $userModel = new User();
        $this->authService = new AuthService($sessionService, $userModel);
        $this->userModel = $userModel;
    }

    /**
     * GET /leaderboard - Show leaderboard page
     */
    public function index(): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        $this->currentPage = 'leaderboard';
        $this->render('leaderboard/index', [
            'pageTitle' => 'Leaderboard',
            'currentPage' => $this->currentPage,
            'userId' => $user['id']
        ], ['leaderboard'], ['leaderboard']);
    }

    /**
     * GET /api/leaderboard/{type} - Get leaderboard data
     */
    public function getData(string $type): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $period = $_GET['period'] ?? 'all_time';
        $validTypes = ['api_calls', 'spending', 'referrals', 'checkin_streak'];
        $validPeriods = ['weekly', 'monthly', 'all_time'];

        if (!in_array($type, $validTypes)) {
            $this->json(['error' => 'Invalid leaderboard type'], 400);
            return;
        }

        if (!in_array($period, $validPeriods)) {
            $this->json(['error' => 'Invalid period'], 400);
            return;
        }

        $leaderboard = $this->getLeaderboardData($type, $period, 10);
        $userRank = $this->getUserRank($user['id'], $type, $period);

        $this->json([
            'success' => true,
            'leaderboard' => $leaderboard,
            'user_rank' => $userRank
        ]);
    }

    /**
     * Get leaderboard data from database
     */
    private function getLeaderboardData(string $type, string $period, int $limit): array
    {
        $db = Database::getInstance();
        $dateCondition = $this->getDateCondition($period);

        switch ($type) {
            case 'api_calls':
                $sql = "SELECT u.id, u.username, u.avatar,
                        COUNT(ul.id) as score
                        FROM users u
                        LEFT JOIN usage_logs ul ON u.id = ul.user_id {$dateCondition}
                        WHERE u.is_banned = 0
                        GROUP BY u.id
                        ORDER BY score DESC
                        LIMIT ?";
                break;
                
            case 'spending':
                $sql = "SELECT u.id, u.username, u.avatar,
                        COALESCE(SUM(ul.cost), 0) as score
                        FROM users u
                        LEFT JOIN usage_logs ul ON u.id = ul.user_id {$dateCondition}
                        WHERE u.is_banned = 0
                        GROUP BY u.id
                        ORDER BY score DESC
                        LIMIT ?";
                break;
                
            case 'referrals':
                $sql = "SELECT u.id, u.username, u.avatar,
                        COUNT(r.id) as score
                        FROM users u
                        LEFT JOIN referrals r ON u.id = r.referrer_id {$dateCondition}
                        WHERE u.is_banned = 0
                        GROUP BY u.id
                        ORDER BY score DESC
                        LIMIT ?";
                break;
                
            case 'checkin_streak':
                $sql = "SELECT u.id, u.username, u.avatar,
                        COALESCE(MAX(dc.streak_count), 0) as score
                        FROM users u
                        LEFT JOIN daily_checkins dc ON u.id = dc.user_id {$dateCondition}
                        WHERE u.is_banned = 0
                        GROUP BY u.id
                        ORDER BY score DESC
                        LIMIT ?";
                break;
                
            default:
                return [];
        }

        $stmt = $db->prepare($sql);
        $stmt->execute([$limit]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Add rank and anonymize usernames
        $rank = 1;
        foreach ($results as &$row) {
            $row['rank'] = $rank++;
            $row['display_name'] = $this->anonymizeUsername($row['username']);
            $row['score'] = number_format((float)$row['score'], $type === 'spending' ? 2 : 0);
            unset($row['username']); // Remove real username
        }

        return $results;
    }

    /**
     * Get user's own rank
     */
    private function getUserRank(int $userId, string $type, string $period): array
    {
        $db = Database::getInstance();
        $dateCondition = $this->getDateCondition($period);

        switch ($type) {
            case 'api_calls':
                $scoreSql = "SELECT COUNT(ul.id) as score FROM usage_logs ul WHERE ul.user_id = ? " . str_replace('AND', 'AND ul.created_at', $dateCondition);
                $rankSql = "SELECT COUNT(DISTINCT u.id) + 1 as rank FROM users u
                            LEFT JOIN usage_logs ul ON u.id = ul.user_id {$dateCondition}
                            WHERE u.is_banned = 0
                            GROUP BY u.id
                            HAVING COUNT(ul.id) > (
                                SELECT COUNT(*) FROM usage_logs WHERE user_id = ? " . str_replace('AND', 'AND created_at', $dateCondition) . "
                            )";
                break;
                
            case 'spending':
                $scoreSql = "SELECT COALESCE(SUM(cost), 0) as score FROM usage_logs WHERE user_id = ? " . str_replace('AND', 'AND created_at', $dateCondition);
                break;
                
            case 'referrals':
                $scoreSql = "SELECT COUNT(*) as score FROM referrals WHERE referrer_id = ? " . str_replace('AND', 'AND created_at', $dateCondition);
                break;
                
            case 'checkin_streak':
                $scoreSql = "SELECT COALESCE(MAX(streak_count), 0) as score FROM daily_checkins WHERE user_id = ? " . str_replace('AND', 'AND checkin_date', $dateCondition);
                break;
                
            default:
                return ['rank' => 0, 'score' => 0];
        }

        $stmt = $db->prepare($scoreSql);
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'score' => number_format((float)($result['score'] ?? 0), $type === 'spending' ? 2 : 0)
        ];
    }

    /**
     * Get date condition for SQL
     */
    private function getDateCondition(string $period): string
    {
        switch ($period) {
            case 'weekly':
                return "AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            case 'monthly':
                return "AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            default:
                return "";
        }
    }

    /**
     * Anonymize username for privacy
     */
    private function anonymizeUsername(string $username): string
    {
        if (strlen($username) <= 3) {
            return $username[0] . '***';
        }
        return substr($username, 0, 2) . str_repeat('*', strlen($username) - 3) . substr($username, -1);
    }
}
