<?php
/**
 * Coupon Controller
 * Handles coupon validation API endpoints
 */

class CouponController extends BaseController
{
    private AuthService $authService;
    private CouponService $couponService;

    public function __construct()
    {
        $sessionService = new SessionService();
        $userModel = new User();
        $this->authService = new AuthService($sessionService, $userModel);
        $this->couponService = new CouponService();
    }

    /**
     * Validate coupon code via AJAX
     * POST /coupon/validate
     */
    public function validateCode(): void
    {
        // Ensure user is logged in
        $user = $this->authService->user();
        
        if (!$user) {
            $this->json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
            return;
        }

        // Get POST data
        $code = trim($_POST['code'] ?? '');
        $amount = (float)($_POST['amount'] ?? 0);

        // Validate input
        if (empty($code)) {
            $this->json([
                'success' => false,
                'message' => 'Please enter a coupon code'
            ]);
            return;
        }

        if ($amount <= 0) {
            $this->json([
                'success' => false,
                'message' => 'Please enter a valid deposit amount'
            ]);
            return;
        }

        // Validate coupon
        $result = $this->couponService->validateForAjax($code, $user['id'], $amount);

        $this->json($result);
    }

    /**
     * Get user's coupon usage history
     * GET /coupon/history
     */
    public function history(): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        $history = $this->couponService->getUserCouponHistory($user['id']);

        $this->currentPage = 'billing';
        $this->render('billing/coupon_history', [
            'pageTitle' => 'Coupon History',
            'currentPage' => $this->currentPage,
            'history' => $history
        ], ['billing'], ['billing']);
    }
}
