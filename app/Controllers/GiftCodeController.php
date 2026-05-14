<?php
/**
 * Gift Code Controller
 * Handles gift code redemption for users
 */
class GiftCodeController extends BaseController
{
    private GiftCodeService $giftCodeService;

    public function __construct()
    {
        $this->currentPage = 'giftcode';
        $this->giftCodeService = new GiftCodeService();
    }

    /**
     * Show gift code redemption page
     */
    public function index(): void
    {
        $userId = $_SESSION['user_id'] ?? 0;
        
        // Get user's redemption history
        $history = $this->giftCodeService->getUserHistory($userId);

        $this->render('giftcode/redeem', [
            'pageTitle' => __('giftcode.title', 'Nhap Gift Code'),
            'history' => $history
        ], ['pages/giftcode'], ['pages/giftcode']);
    }

    /**
     * Redeem a gift code (AJAX)
     */
    public function redeem(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }

        $userId = $_SESSION['user_id'] ?? 0;
        if (!$userId) {
            $this->json(['success' => false, 'message' => 'Vui long dang nhap'], 401);
            return;
        }

        $code = $_POST['code'] ?? '';
        if (empty($code)) {
            $this->json(['success' => false, 'message' => 'Vui long nhap gift code'], 400);
            return;
        }

        $result = $this->giftCodeService->redeem($code, $userId);

        if ($result['success']) {
            // Get updated balance
            $userModel = new User();
            $user = $userModel->find($userId);
            $result['new_balance'] = $user['balance'] ?? 0;
        }

        $this->json($result);
    }

    /**
     * Show redemption history
     */
    public function history(): void
    {
        $userId = $_SESSION['user_id'] ?? 0;
        $history = $this->giftCodeService->getUserHistory($userId, 50);

        $this->render('giftcode/history', [
            'pageTitle' => __('giftcode.history', 'Lich su Gift Code'),
            'history' => $history
        ], ['pages/giftcode'], ['pages/giftcode']);
    }
}
