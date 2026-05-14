<?php
/**
 * Favorite Controller
 * Handles user favorite models
 */
class FavoriteController extends BaseController
{
    public function __construct()
    {
        $this->currentPage = 'favorites';
    }

    /**
     * Show favorites page
     */
    public function index(): void
    {
        $userId = $_SESSION['user_id'] ?? 0;
        
        // Get user favorites
        $db = (new User())->db();
        $sql = "SELECT * FROM user_favorites WHERE user_id = :user_id ORDER BY sort_order ASC, created_at DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        $favorites = $stmt->fetchAll();

        // Get available models from model_pricing
        $modelsSql = "SELECT * FROM model_pricing WHERE is_active = 1 ORDER BY display_name ASC";
        $modelsStmt = $db->prepare($modelsSql);
        $modelsStmt->execute();
        $models = $modelsStmt->fetchAll();

        $this->render('favorites/index', [
            'pageTitle' => __('favorites.title', 'Model yeu thich'),
            'favorites' => $favorites,
            'models' => $models
        ], ['pages/favorites'], ['pages/favorites']);
    }

    /**
     * Toggle favorite (AJAX)
     */
    public function toggle(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }

        $userId = $_SESSION['user_id'] ?? 0;
        $modelId = $_POST['model_id'] ?? '';

        if (!$userId || empty($modelId)) {
            $this->json(['success' => false, 'message' => 'Invalid request'], 400);
            return;
        }

        $db = (new User())->db();
        
        // Check if already favorited
        $checkSql = "SELECT id FROM user_favorites WHERE user_id = :user_id AND model_id = :model_id";
        $checkStmt = $db->prepare($checkSql);
        $checkStmt->execute(['user_id' => $userId, 'model_id' => $modelId]);
        $existing = $checkStmt->fetch();

        if ($existing) {
            // Remove favorite
            $deleteSql = "DELETE FROM user_favorites WHERE id = :id";
            $deleteStmt = $db->prepare($deleteSql);
            $deleteStmt->execute(['id' => $existing['id']]);
            
            $this->json([
                'success' => true,
                'action' => 'removed',
                'message' => 'Da xoa khoi danh sach yeu thich'
            ]);
        } else {
            // Add favorite
            $insertSql = "INSERT INTO user_favorites (user_id, model_id, created_at) VALUES (:user_id, :model_id, NOW())";
            $insertStmt = $db->prepare($insertSql);
            $insertStmt->execute(['user_id' => $userId, 'model_id' => $modelId]);
            
            $this->json([
                'success' => true,
                'action' => 'added',
                'message' => 'Da them vao danh sach yeu thich'
            ]);
        }
    }
}
