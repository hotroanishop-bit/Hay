<?php
/**
 * Referral Controller
 * Handles referral dashboard, code generation, and earnings withdrawal
 */

class ReferralController extends BaseController
{
    private AuthService $authService;
    private ReferralService $referralService;
    private Referral $referralModel;
    private User $userModel;
    private AuditService $auditService;

    public function __construct()
    {
        $sessionService = new SessionService();
        $this->userModel = new User();
        $this->authService = new AuthService($sessionService, $this->userModel);
        $this->referralService = new ReferralService();
        $this->referralModel = new Referral();
        $this->auditService = new AuditService();
    }

    /**
     * Show referral dashboard
     */
    public function index(): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        $userId = (int) $user['id'];

        // Get or generate referral code
        $referralCode = $this->referralService->getOrGenerateReferralCode($userId);
        $referralLink = $this->referralService->getReferralLink($userId);

        // Get stats
        $stats = $this->referralService->getReferralStats($userId);

        // Get referrals with pagination
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $referrals = $this->referralModel->getPaginatedReferrals($userId, $page, 15);

        $this->currentPage = 'referral';
        $this->render('referral/index', [
            'pageTitle' => __('referral.title', 'Referral Program'),
            'currentPage' => $this->currentPage,
            'user' => $user,
            'referralCode' => $referralCode,
            'referralLink' => $referralLink,
            'stats' => $stats,
            'referrals' => $referrals['data'],
            'pagination' => [
                'total' => $referrals['total'],
                'page' => $referrals['page'],
                'per_page' => $referrals['per_page'],
                'total_pages' => $referrals['total_pages']
            ]
        ], ['referral'], ['referral']);
    }

    /**
     * Regenerate referral code
     */
    public function generateCode(): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        $userId = (int) $user['id'];

        // Generate new code
        $newCode = $this->referralService->generateReferralCode($userId);

        // Log the action
        $this->auditService->log($userId, 'referral_code_regenerated', [
            'new_code' => $newCode
        ]);

        $this->setFlash('success', __('referral.code_regenerated', 'Referral code regenerated successfully'));
        $this->redirect('/referral');
    }

    /**
     * Withdraw earnings to main balance
     */
    public function withdraw(): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        $userId = (int) $user['id'];

        // Process withdrawal
        $result = $this->referralService->withdrawToBalance($userId);

        if ($result['success']) {
            // Log the action
            $this->auditService->log($userId, 'referral_earnings_withdrawn', [
                'amount' => $result['amount']
            ]);

            $this->setFlash('success', __('referral.withdrawal_success', 'Earnings withdrawn successfully') . ' ($' . number_format($result['amount'], 2) . ')');
        } else {
            $this->setFlash('error', $result['message']);
        }

        $this->redirect('/referral');
    }
}
