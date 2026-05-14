<?php
/**
 * Invoice Service
 * Handles generation of invoices and receipts for deposits and purchases
 */

class InvoiceService
{
    private Deposit $depositModel;
    private Transaction $transactionModel;
    private User $userModel;

    public function __construct()
    {
        $this->depositModel = new Deposit();
        $this->transactionModel = new Transaction();
        $this->userModel = new User();
    }

    /**
     * Generate invoice data for a deposit
     *
     * @param int $depositId Deposit ID
     * @param int $userId User ID for verification
     * @return array|null Invoice data or null if not found/unauthorized
     */
    public function generateDepositInvoice(int $depositId, int $userId): ?array
    {
        $deposit = $this->depositModel->find($depositId);
        
        if (!$deposit || $deposit['user_id'] != $userId) {
            return null;
        }

        // Only generate invoice for approved deposits
        if ($deposit['status'] !== 'approved') {
            return null;
        }

        $user = $this->userModel->find($userId);
        if (!$user) {
            return null;
        }

        return [
            'type' => 'deposit',
            'invoice_number' => $this->getInvoiceNumber('D', $depositId),
            'date' => $deposit['processed_at'] ?? $deposit['created_at'],
            'user' => [
                'name' => $user['name'] ?? 'N/A',
                'email' => $user['email'],
            ],
            'company' => $this->getCompanyInfo(),
            'items' => [
                [
                    'description' => 'Account Deposit via Bank Transfer',
                    'reference' => $deposit['reference_code'] ?? 'N/A',
                    'quantity' => 1,
                    'amount' => (float) $deposit['amount'],
                ]
            ],
            'subtotal' => (float) $deposit['amount'],
            'tax' => 0,
            'total' => (float) $deposit['amount'],
            'payment_method' => 'Bank Transfer',
            'bank_account' => $deposit['bank_account'] ?? 'N/A',
            'status' => 'Paid',
            'notes' => 'Thank you for your deposit. Funds have been added to your account.',
        ];
    }

    /**
     * Generate receipt data for a purchase/transaction
     *
     * @param int $transactionId Transaction ID
     * @param int $userId User ID for verification
     * @return array|null Receipt data or null if not found/unauthorized
     */
    public function generatePurchaseReceipt(int $transactionId, int $userId): ?array
    {
        $transaction = $this->transactionModel->find($transactionId);
        
        if (!$transaction || $transaction['user_id'] != $userId) {
            return null;
        }

        // Only generate receipt for completed transactions
        if ($transaction['status'] !== 'completed') {
            return null;
        }

        $user = $this->userModel->find($userId);
        if (!$user) {
            return null;
        }

        $isCredit = $transaction['type'] === 'credit';
        $description = $transaction['description'] ?? ($isCredit ? 'Credit Addition' : 'Purchase');

        return [
            'type' => 'purchase',
            'invoice_number' => $this->getInvoiceNumber('P', $transactionId),
            'date' => $transaction['created_at'],
            'user' => [
                'name' => $user['name'] ?? 'N/A',
                'email' => $user['email'],
            ],
            'company' => $this->getCompanyInfo(),
            'items' => [
                [
                    'description' => $description,
                    'reference' => $transaction['reference_id'] ?? 'N/A',
                    'quantity' => 1,
                    'amount' => (float) $transaction['amount'],
                ]
            ],
            'subtotal' => (float) $transaction['amount'],
            'tax' => 0,
            'total' => (float) $transaction['amount'],
            'payment_method' => $transaction['payment_method'] ?? 'Account Balance',
            'transaction_type' => $isCredit ? 'Credit' : 'Debit',
            'status' => ucfirst($transaction['status']),
            'notes' => $isCredit 
                ? 'Funds have been added to your account.' 
                : 'Thank you for your purchase.',
        ];
    }

    /**
     * Generate unique invoice number
     *
     * @param string $type Type prefix (D for deposit, P for purchase)
     * @param int $id Record ID
     * @return string Invoice number
     */
    public function getInvoiceNumber(string $type, int $id): string
    {
        $year = date('Y');
        $paddedId = str_pad($id, 6, '0', STR_PAD_LEFT);
        return "INV-{$type}-{$year}-{$paddedId}";
    }

    /**
     * Get company information from settings or defaults
     *
     * @return array Company information
     */
    public function getCompanyInfo(): array
    {
        // In production, this could be loaded from settings table
        return [
            'name' => defined('APP_NAME') ? APP_NAME : 'Hay API Gateway',
            'address' => defined('COMPANY_ADDRESS') ? COMPANY_ADDRESS : '',
            'email' => defined('COMPANY_EMAIL') ? COMPANY_EMAIL : 'support@example.com',
            'phone' => defined('COMPANY_PHONE') ? COMPANY_PHONE : '',
            'website' => defined('APP_URL') ? APP_URL : '',
            'tax_id' => defined('COMPANY_TAX_ID') ? COMPANY_TAX_ID : '',
        ];
    }
}
