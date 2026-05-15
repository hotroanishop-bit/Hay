<?php
/**
 * Admin Feedback Controller
 * Manages user feedback from admin side
 */

class AdminFeedbackController extends BaseController
{
    private AuthService $authService;

    public function __construct()
    {
        $sessionService = new SessionService();
        $userModel = new User();
        $this->authService = new AuthService($sessionService, $userModel);
    }

    /**
     * GET /admin/feedback - List all feedback
     */
    public function index(): void
    {
        $user = $this->authService->user();
        
        if (!$user || empty($user['is_admin'])) {
            $this->redirect('/login');
            return;
        }

        $db = Database::getInstance();
        
        // Filters
        $category = $_GET['category'] ?? '';
        $rating = $_GET['rating'] ?? '';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        $where = [];
        $params = [];
        
        if ($category) {
            $where[] = "uf.category = ?";
            $params[] = $category;
        }
        
        if ($rating) {
            $where[] = "uf.rating = ?";
            $params[] = (int)$rating;
        }
        
        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM user_feedback uf $whereClause";
        $countStmt = $db->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Get feedback list
        $sql = "SELECT uf.*, u.username, u.email
                FROM user_feedback uf
                LEFT JOIN users u ON uf.user_id = u.id
                $whereClause
                ORDER BY uf.created_at DESC
                LIMIT $perPage OFFSET $offset";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get statistics
        $statsSql = "SELECT 
                        COUNT(*) as total_feedback,
                        AVG(rating) as avg_rating,
                        SUM(CASE WHEN rating >= 4 THEN 1 ELSE 0 END) as positive_count,
                        SUM(CASE WHEN rating <= 2 THEN 1 ELSE 0 END) as negative_count
                     FROM user_feedback";
        $statsStmt = $db->query($statsSql);
        $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
        
        // Get category breakdown
        $categorySql = "SELECT category, COUNT(*) as count, AVG(rating) as avg_rating
                        FROM user_feedback GROUP BY category";
        $categoryStmt = $db->query($categorySql);
        $categoryStats = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

        $categories = [
            'api_quality' => 'Chat luong API',
            'speed' => 'Toc do',
            'support' => 'Ho tro',
            'overall' => 'Tong the',
            'other' => 'Khac'
        ];

        $this->currentPage = 'admin-feedback';
        $this->render('admin/feedback/index', [
            'pageTitle' => 'User Feedback',
            'currentPage' => $this->currentPage,
            'feedbacks' => $feedbacks,
            'stats' => $stats,
            'categoryStats' => $categoryStats,
            'categories' => $categories,
            'filters' => [
                'category' => $category,
                'rating' => $rating
            ],
            'pagination' => [
                'page' => $page,
                'perPage' => $perPage,
                'total' => $total,
                'totalPages' => ceil($total / $perPage)
            ]
        ], ['admin-feedback'], ['admin-feedback']);
    }

    /**
     * DELETE /admin/feedback/{id} - Delete feedback
     */
    public function delete(int $id): void
    {
        $user = $this->authService->user();
        
        if (!$user || empty($user['is_admin'])) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $db = Database::getInstance();
        $sql = "DELETE FROM user_feedback WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
        
        $this->json(['success' => true]);
    }
}
