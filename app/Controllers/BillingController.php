<?php
/**
 * Billing Controller
 * Handles payments, credits, and transaction history
 */

class BillingController extends BaseController
{
    private AuthService $authService;
    private PaymentService $paymentService;
    private CreditService $creditService;
    private AuditService $auditService;
    private Transaction $transactionModel;

    public function __construct()
    {
        $sessionService = new SessionService();
        $userModel = new User();
        $this->authService = new AuthService($sessionService, $userModel);
        
        $this->transactionModel = new Transaction();
        $this->paymentService = new PaymentService($this->transactionModel, $userModel);
        $this->creditService = new CreditService($userModel, $this->transactionModel);
        $this->auditService = new AuditService();
    }

    /**
     * Show billing overview
     */
    public function index(): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        $userId = $user['id'];

        // Get current balance
        $balance = $this->creditService->getBalance($userId);

        // Get payment methods
        $paymentMethods = $this->paymentService->getPaymentMethods();

        // Get recent transactions
        $recentTransactions = $this->transactionModel->getRecent($userId, 10);

        // Get totals
        $totalCredits = $this->transactionModel->getTotalCredits($userId);
        $totalDebits = $this->transactionModel->getTotalDebits($userId);

        $this->currentPage = 'billing';
        $this->render('billing/index', [
            'pageTitle' => 'Billing',
            'currentPage' => $this->currentPage,
            'balance' => $balance,
            'paymentMethods' => $paymentMethods,
            'recentTransactions' => $recentTransactions,
            'totalCredits' => $totalCredits,
            'totalDebits' => $totalDebits
        ], ['billing'], ['billing']);
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
