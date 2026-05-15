<?php
/**
 * Feedback Controller
 * Handles user feedback and ratings
 */

class FeedbackController extends BaseController
{
    private AuthService $authService;

    public function __construct()
    {
        $sessionService = new SessionService();
        $userModel = new User();
        $this->authService = new AuthService($sessionService, $userModel);
    }

    /**
     * GET /feedback - Show feedback form
     */
    public function index(): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        $db = Database::getInstance();
        
        // Get user's previous feedback
        $sql = "SELECT * FROM user_feedback WHERE user_id = ? ORDER BY created_at DESC LIMIT 10";
        $stmt = $db->prepare($sql);
        $stmt->execute([$user['id']]);
        $myFeedback = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get average ratings
        $avgSql = "SELECT category, AVG(rating) as avg_rating, COUNT(*) as count 
                   FROM user_feedback 
                   GROUP BY category";
        $avgStmt = $db->query($avgSql);
        $avgRatings = $avgStmt->fetchAll(PDO::FETCH_ASSOC);

        $categories = [
            'api_quality' => 'Chat luong API',
            'speed' => 'Toc do',
            'support' => 'Ho tro',
            'overall' => 'Tong the',
            'other' => 'Khac'
        ];

        $this->currentPage = 'feedback';
        $this->render('feedback/index', [
            'pageTitle' => 'Feedback',
            'currentPage' => $this->currentPage,
            'myFeedback' => $myFeedback,
            'avgRatings' => $avgRatings,
            'categories' => $categories
        ], ['feedback'], ['feedback']);
    }

    /**
     * POST /feedback - Submit feedback
     */
    public function store(): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $rating = (int)($_POST['rating'] ?? 5);
        $category = $_POST['category'] ?? 'overall';
        $comment = trim($_POST['comment'] ?? '');

        // Validation
        if ($rating < 1 || $rating > 5) {
            $this->json(['error' => 'Rating must be between 1 and 5'], 400);
            return;
        }

        $validCategories = ['api_quality', 'speed', 'support', 'overall', 'other'];
        if (!in_array($category, $validCategories)) {
            $this->json(['error' => 'Invalid category'], 400);
            return;
        }

        if (strlen($comment) > 1000) {
            $this->json(['error' => 'Comment too long (max 1000 characters)'], 400);
            return;
        }

        $db = Database::getInstance();
        
        // Check rate limit (max 1 feedback per category per day)
        $checkSql = "SELECT COUNT(*) as count FROM user_feedback 
                     WHERE user_id = ? AND category = ? AND DATE(created_at) = CURDATE()";
        $checkStmt = $db->prepare($checkSql);
        $checkStmt->execute([$user['id'], $category]);
        $check = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if (($check['count'] ?? 0) > 0) {
            $this->json(['error' => 'Ban da gui feedback cho danh muc nay hom nay roi'], 400);
            return;
        }

        $sql = "INSERT INTO user_feedback (user_id, rating, category, comment, created_at) 
                VALUES (?, ?, ?, ?, NOW())";
        $stmt = $db->prepare($sql);
        $stmt->execute([$user['id'], $rating, $category, $comment]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            $this->json([
                'success' => true,
                'message' => 'Cam on ban da gui feedback!'
            ]);
        } else {
            $this->setFlash('success', 'Cam on ban da gui feedback!');
            $this->redirect('/feedback');
        }
    }
}
