<?php
/**
 * Deposit Controller
 * Handles VietQR-based deposits with QR code generation and management
 */

class DepositController extends BaseController
{
    private AuthService $authService;
    private Deposit $depositModel;
    private VietQRService $vietQRService;
    private SettingsService $settingsService;
    private AuditService $auditService;

    public function __construct()
    {
        $sessionService = new SessionService();
        $userModel = new User();
        $this->authService = new AuthService($sessionService, $userModel);
        
        $this->depositModel = new Deposit();
        $this->vietQRService = new VietQRService();
        $this->settingsService = new SettingsService();
        $this->auditService = new AuditService();
    }

    /**
     * Show deposit form
     * GET /billing/deposit
     */
    public function showDeposit(): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        // Get bank info and deposit limits from settings
        $paymentSettings = $this->settingsService->getPaymentSettings();
        $bankList = $this->settingsService->getBankList();

        $this->currentPage = 'billing';
        $this->render('billing/deposit', [
            'pageTitle' => 'Make a Deposit',
            'currentPage' => $this->currentPage,
            'bankName' => $paymentSettings['bank_name'],
            'bankAccountNumber' => $paymentSettings['bank_account_number'],
            'accountHolderName' => $paymentSettings['account_holder_name'],
            'minDeposit' => $paymentSettings['min_deposit'],
            'maxDeposit' => $paymentSettings['max_deposit'],
            'bankList' => $bankList
        ], ['billing'], ['billing']);
    }

    /**
     * Process deposit creation
     * POST /billing/deposit
     */
    public function createDeposit(): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        // Get amount from POST
        $amount = isset($_POST['amount']) ? (int)$_POST['amount'] : 0;

        // Get payment settings for validation
        $paymentSettings = $this->settingsService->getPaymentSettings();
        $minDeposit = (int)$paymentSettings['min_deposit'];
        $maxDeposit = (int)$paymentSettings['max_deposit'];

        // Validate amount
        if ($amount < $minDeposit || $amount > $maxDeposit) {
            $this->setFlash('error', 'Amount must be between ' . number_format($minDeposit) . ' VND and ' . number_format($maxDeposit) . ' VND');
            $this->redirect('/billing/deposit');
            return;
        }

        // Get bank info from settings
        $bankName = $paymentSettings['bank_name'];
        $bankAccountNumber = $paymentSettings['bank_account_number'];
        $accountHolderName = $paymentSettings['account_holder_name'];

        // Validate bank info is configured
        if (empty($bankName) || empty($bankAccountNumber)) {
            $this->setFlash('error', 'Bank information is not configured. Please contact administrator.');
            $this->redirect('/billing/deposit');
            return;
        }

        // Get bank ID from bank name
        $bankId = $this->getBankIdFromName($bankName);

        // Generate unique reference code
        $referenceCode = $this->vietQRService->generateReferenceCode();

        // Generate QR data URL
        $qrData = $this->vietQRService->generateQR(
            $bankId,
            $bankAccountNumber,
            $amount,
            $referenceCode,
            $accountHolderName
        );

        // Create deposit record
        $depositId = $this->depositModel->createDeposit([
            'user_id' => $user['id'],
            'amount' => $amount,
            'reference_code' => $referenceCode,
            'bank_account' => $bankAccountNumber,
            'status' => 'pending',
            'qr_data' => $qrData
        ]);

        // Log audit
        $this->auditService->log($user['id'], 'deposit_created', [
            'deposit_id' => $depositId,
            'amount' => $amount,
            'reference_code' => $referenceCode
        ]);

        $this->setFlash('success', 'Deposit request created successfully. Please complete the payment.');
        $this->redirect('/billing/deposit/' . $depositId);
    }

    /**
     * Show deposit detail
     * GET /billing/deposit/{id}
     */
    public function showDepositDetail(int $id): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        // Find deposit
        $deposit = $this->depositModel->find($id);

        if (!$deposit) {
            $this->setFlash('error', 'Deposit not found');
            $this->redirect('/billing/pending');
            return;
        }

        // Verify ownership
        if ($deposit['user_id'] != $user['id']) {
            $this->setFlash('error', 'Access denied');
            $this->redirect('/billing/pending');
            return;
        }

        // Get bank info from settings
        $paymentSettings = $this->settingsService->getPaymentSettings();

        $this->currentPage = 'billing';
        $this->render('billing/deposit_detail', [
            'pageTitle' => 'Deposit Details',
            'currentPage' => $this->currentPage,
            'deposit' => $deposit,
            'bankName' => $paymentSettings['bank_name'],
            'bankAccountNumber' => $paymentSettings['bank_account_number'],
            'accountHolderName' => $paymentSettings['account_holder_name']
        ], ['billing'], ['billing']);
    }

    /**
     * Show pending deposits list
     * GET /billing/pending
     */
    public function pendingDeposits(): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        // Get user's deposits filtered to pending
        $allDeposits = $this->depositModel->findByUser($user['id']);
        $pendingDeposits = array_filter($allDeposits, function($deposit) {
            return $deposit['status'] === 'pending';
        });

        $this->currentPage = 'billing';
        $this->render('billing/pending', [
            'pageTitle' => 'Pending Deposits',
            'currentPage' => $this->currentPage,
            'deposits' => array_values($pendingDeposits)
        ], ['billing'], ['billing']);
    }

    /**
     * Cancel a pending deposit
     * POST /billing/deposit/{id}/cancel
     */
    public function cancelDeposit(int $id): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        // Find deposit
        $deposit = $this->depositModel->find($id);

        if (!$deposit) {
            $this->setFlash('error', 'Deposit not found');
            $this->redirect('/billing/pending');
            return;
        }

        // Verify ownership
        if ($deposit['user_id'] != $user['id']) {
            $this->setFlash('error', 'Access denied');
            $this->redirect('/billing/pending');
            return;
        }

        // Verify status is pending
        if ($deposit['status'] !== 'pending') {
            $this->setFlash('error', 'Only pending deposits can be cancelled');
            $this->redirect('/billing/deposit/' . $id);
            return;
        }

        // Update status to expired
        $this->depositModel->updateStatus($id, 'expired');

        // Log audit
        $this->auditService->log($user['id'], 'deposit_cancelled', [
            'deposit_id' => $id,
            'reference_code' => $deposit['reference_code']
        ]);

        $this->setFlash('success', 'Deposit has been cancelled');
        $this->redirect('/billing/pending');
    }

    /**
     * Get bank ID from bank name
     */
    private function getBankIdFromName(string $bankName): string
    {
        $bankList = $this->vietQRService->getBankList();
        
        foreach ($bankList as $bank) {
            if (stripos($bankName, $bank['name']) !== false || 
                stripos($bank['name'], $bankName) !== false ||
                strtoupper($bankName) === $bank['id']) {
                return $bank['id'];
            }
        }

        // Default to MB Bank if not found
        return 'MB';
    }
}
