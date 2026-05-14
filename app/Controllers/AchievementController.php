<?php
/**
 * Achievement Controller
 * Handles user achievements/badges display
 */
class AchievementController extends BaseController
{
    private AchievementService $achievementService;

    public function __construct()
    {
        $this->currentPage = 'achievements';
        $this->achievementService = new AchievementService();
    }

    /**
     * Show achievements page
     */
    public function index(): void
    {
        $userId = $_SESSION['user_id'] ?? 0;

        $stats = $this->achievementService->getUserStats($userId);
        $categories = $this->achievementService->getCategorizedAchievements($userId);

        $this->render('achievements/index', [
            'pageTitle' => __('achievements.title', 'Thanh tuu'),
            'stats' => $stats,
            'categories' => $categories
        ], ['pages/achievements'], ['pages/achievements']);
    }
}
