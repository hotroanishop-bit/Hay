<?php
/**
 * Billing Controller
 * Handles payments, credits, plans, and transaction history
 */

class BillingController extends BaseController
{
    private AuthService $authService;
    private PaymentService $paymentService;
    private CreditService $creditService;
    private AuditService $auditService;
    private PlanSubscriptionService $planSubscriptionService;
    private Transaction $transactionModel;
    private Plan $planModel;
    private User $userModel;

    public function __construct()
    {
        $sessionService = new SessionService();
        $this->userModel = new User();
        $this->authService = new AuthService($sessionService, $this->userModel);
        
        $this->transactionModel = new Transaction();
        $this->paymentService = new PaymentService($this->transactionModel, $this->userModel);
        $this->creditService = new CreditService($this->userModel, $this->transactionModel);
        $this->auditService = new AuditService();
        
        $this->planModel = new Plan();
        $userPlanModel = new UserPlan();
        $this->planSubscriptionService = new PlanSubscriptionService($this->userModel, $this->planModel, $userPlanModel);
    }

    /**
     * Show billing overview with dual balance display
     */
    public function index(): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        $userId = $user['id'];

        // Get current balances (both PAYG and Plan)
        $balances = $this->planSubscriptionService->getBothBalances($userId);
        
        // Get legacy balance for backward compatibility
        $balance = $this->creditService->getBalance($userId);

        // Get payment methods
        $paymentMethods = $this->paymentService->getPaymentMethods();

        // Get recent transactions
        $recentTransactions = $this->transactionModel->getRecent($userId, 10);

        // Get totals
        $totalCredits = $this->transactionModel->getTotalCredits($userId);
        $totalDebits = $this->transactionModel->getTotalDebits($userId);

        // Get current plan details
        $currentPlan = null;
        if ($balances['current_plan_id']) {
            $currentPlan = $this->planModel->find($balances['current_plan_id']);
        }

        // Get available plans
        $availablePlans = $this->planSubscriptionService->getAvailablePlans();

        $this->currentPage = 'billing';
        $this->render('billing/index', [
            'pageTitle' => 'Billing',
            'currentPage' => $this->currentPage,
            'balance' => $balance,
            'balances' => $balances,
            'currentPlan' => $currentPlan,
            'availablePlans' => $availablePlans,
            'paymentMethods' => $paymentMethods,
            'recentTransactions' => $recentTransactions,
            'totalCredits' => $totalCredits,
            'totalDebits' => $totalDebits
        ], ['billing'], ['billing']);
    }

    /**
     * Show available plans page
     */
    public function plans(): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        $userId = $user['id'];

        // Get current balances
        $balances = $this->planSubscriptionService->getBothBalances($userId);

        // Get current plan
        $currentPlan = null;
        if ($balances['current_plan_id']) {
            $currentPlan = $this->planModel->find($balances['current_plan_id']);
        }

        // Get all available plans
        $availablePlans = $this->planSubscriptionService->getAvailablePlans();

        $this->currentPage = 'billing';
        $this->render('billing/plans', [
            'pageTitle' => 'Subscription Plans',
            'currentPage' => $this->currentPage,
            'balances' => $balances,
            'currentPlan' => $currentPlan,
            'availablePlans' => $availablePlans
        ], ['billing'], ['billing']);
    }

    /**
     * Switch preferred billing type
     */
    public function switchBillingType(): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        $billingType = $_POST['billing_type'] ?? '';
        
        if (!in_array($billingType, ['payg', 'plan'])) {
            $this->setFlash('error', 'Invalid billing type selected');
            $this->redirect('/billing');
            return;
        }

        $success = $this->planSubscriptionService->updatePreferredBillingType($user['id'], $billingType);

        if ($success) {
            $this->auditService->log($user['id'], 'billing_type_changed', [
                'new_type' => $billingType
            ]);
            $this->setFlash('success', 'Billing preference updated to ' . strtoupper($billingType));
        } else {
            $this->setFlash('error', 'Failed to update billing preference');
        }

        $this->redirect('/billing');
    }

    /**
     * Subscribe to a plan
     */
    public function subscribePlan(int $id): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        $plan = $this->planModel->find($id);
        
        if (!$plan) {
            $this->setFlash('error', 'Plan not found');
            $this->redirect('/billing/plans');
            return;
        }

        if (!($plan['is_active'] ?? false)) {
            $this->setFlash('error', 'This plan is not available');
            $this->redirect('/billing/plans');
            return;
        }

        // Check if it's a paid plan - require sufficient balance
        $planPrice = (float) ($plan['price_monthly'] ?? 0);
        if ($planPrice > 0) {
            $balance = $this->creditService->getBalance($user['id']);
            if ($balance < $planPrice) {
                $this->setFlash('error', 'Insufficient balance. Please add credits first.');
                $this->redirect('/billing/add-credits');
                return;
            }
            
            // Deduct plan price from balance
            $this->userModel->updateBalance($user['id'], -$planPrice);
            
            // Create transaction record
            $this->transactionModel->create([
                'user_id' => $user['id'],
                'type' => 'debit',
                'amount' => $planPrice,
                'description' => 'Subscription to ' . ($plan['name'] ?? 'Plan'),
                'status' => 'completed',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }

        try {
            $subscriptionId = $this->planSubscriptionService->subscribeToPlan($user['id'], $id);
            
            $this->auditService->log($user['id'], 'plan_subscribed', [
                'plan_id' => $id,
                'plan_name' => $plan['name'] ?? 'Unknown',
                'subscription_id' => $subscriptionId
            ]);
            
            $this->setFlash('success', 'Successfully subscribed to ' . ($plan['name'] ?? 'the plan'));
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to subscribe to plan: ' . $e->getMessage());
        }

        $this->redirect('/billing');
    }

    /**
     * Cancel current plan subscription
     */
    public function cancelPlan(): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        $balances = $this->planSubscriptionService->getBothBalances($user['id']);
        
        if (!$balances['current_plan_id']) {
            $this->setFlash('error', 'No active plan to cancel');
            $this->redirect('/billing');
            return;
        }

        $plan = $this->planModel->find($balances['current_plan_id']);

        $success = $this->planSubscriptionService->cancelPlan($user['id']);

        if ($success) {
            $this->auditService->log($user['id'], 'plan_cancelled', [
                'plan_id' => $balances['current_plan_id'],
                'plan_name' => $plan['name'] ?? 'Unknown',
                'tokens_remaining' => $balances['plan_tokens']
            ]);
            
            $this->setFlash('success', 'Plan subscription cancelled. Remaining tokens have been forfeited.');
        } else {
            $this->setFlash('error', 'Failed to cancel plan');
        }

        $this->redirect('/billing');
    }

    /**
     * Show transaction history with pagination
     */
    public function history(): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        $userId = $user['id'];
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 15;

        // Get paginated transactions
        $result = $this->transactionModel->getHistory($userId, $page, $perPage);

        $this->currentPage = 'billing';
        $this->render('billing/history', [
            'pageTitle' => 'Transaction History',
            'currentPage' => $this->currentPage,
            'transactions' => $result['data'],
            'pagination' => [
                'current_page' => $result['page'],
                'total_pages' => $result['total_pages'],
                'total' => $result['total'],
                'per_page' => $result['per_page']
            ]
        ], ['billing'], ['billing']);
    }

    /**
     * Show add credits form
     */
    public function addCredits(): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        // Get payment methods
        $paymentMethods = $this->paymentService->getPaymentMethods();

        $this->currentPage = 'billing';
        $this->render('billing/add_credits', [
            'pageTitle' => 'Add Credits',
            'currentPage' => $this->currentPage,
            'paymentMethods' => $paymentMethods
        ], ['billing'], ['billing']);
    }

    /**
     * Process payment
     */
    public function processPayment(): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        $amount = isset($_POST['amount']) ? (float)$_POST['amount'] : 0;
        $method = $_POST['payment_method'] ?? '';

        // Validate amount
        if ($amount <= 0) {
            $this->setFlash('error', 'Please enter a valid amount');
            $this->redirect('/billing/add-credits');
            return;
        }

        // Validate payment method
        $validMethods = array_column($this->paymentService->getPaymentMethods(), 'id');
        if (!in_array($method, $validMethods)) {
            $this->setFlash('error', 'Invalid payment method');
            $this->redirect('/billing/add-credits');
            return;
        }

        try {
            $result = $this->paymentService->createPayment($user['id'], $amount, $method);

            $this->auditService->log($user['id'], 'payment_initiated', [
                'amount' => $amount,
                'method' => $method,
                'reference_id' => $result['reference_id']
            ]);

            // In a real app, this would redirect to the payment provider
            // For now, we simulate a successful payment
            $this->setFlash('success', 'Payment of $' . number_format($amount, 2) . ' initiated. Reference: ' . $result['reference_id']);
            $this->redirect('/billing');
        } catch (Exception $e) {
            $this->setFlash('error', 'Payment failed: ' . $e->getMessage());
            $this->redirect('/billing/add-credits');
        }
    }
}
