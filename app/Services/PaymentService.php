<?php
/**
 * Payment Service
 * Handles payment processing and webhook verification
 */

class PaymentService
{
    private Transaction $transactionModel;
    private User $userModel;

    public function __construct(Transaction $transactionModel, User $userModel)
    {
        $this->transactionModel = $transactionModel;
        $this->userModel = $userModel;
    }

    /**
     * Create a new payment
     */
    public function createPayment(int $userId, float $amount, string $method): array
    {
        // Validate amount
        if ($amount <= 0) {
            throw new InvalidArgumentException('Payment amount must be positive');
        }

        // Generate unique reference ID
        $referenceId = 'PAY_' . strtoupper(bin2hex(random_bytes(8))) . '_' . time();

        // Create pending transaction
        $transactionId = $this->transactionModel->create([
            'user_id' => $userId,
            'type' => Transaction::TYPE_CREDIT,
            'amount' => $amount,
            'description' => 'Credit purchase via ' . $method,
            'reference_id' => $referenceId,
            'payment_method' => $method,
            'status' => Transaction::STATUS_PENDING,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return [
            'transaction_id' => $transactionId,
            'reference_id' => $referenceId,
            'amount' => $amount,
            'method' => $method,
            'status' => Transaction::STATUS_PENDING,
            'checkout_url' => $this->generateCheckoutUrl($method, $referenceId, $amount),
        ];
    }

    /**
     * Process payment webhook from payment provider
     */
    public function processWebhook(array $payload): bool
    {
        $referenceId = $payload['reference_id'] ?? null;
        $status = $payload['status'] ?? null;

        if (!$referenceId || !$status) {
            return false;
        }

        $transaction = $this->transactionModel->findByReference($referenceId);

        if (!$transaction) {
            return false;
        }

        // Already processed
        if ($transaction['status'] !== Transaction::STATUS_PENDING) {
            return true;
        }

        // Update transaction status
        $newStatus = $this->mapWebhookStatus($status);
        $this->transactionModel->updateStatus($transaction['id'], $newStatus);

        // If payment completed, add credits to user
        if ($newStatus === Transaction::STATUS_COMPLETED) {
            $this->userModel->updateBalance($transaction['user_id'], $transaction['amount']);
        }

        return true;
    }

    /**
     * Verify a payment by reference ID
     */
    public function verifyPayment(string $reference): ?array
    {
        $transaction = $this->transactionModel->findByReference($reference);

        if (!$transaction) {
            return null;
        }

        return [
            'transaction_id' => $transaction['id'],
            'reference_id' => $transaction['reference_id'],
            'amount' => $transaction['amount'],
            'status' => $transaction['status'],
            'payment_method' => $transaction['payment_method'],
            'created_at' => $transaction['created_at'],
        ];
    }

    /**
     * Get available payment methods
     */
    public function getPaymentMethods(): array
    {
        return [
            [
                'id' => 'stripe',
                'name' => 'Credit Card (Stripe)',
                'icon' => 'credit-card',
                'min_amount' => 5.00,
                'max_amount' => 10000.00,
                'fee_percent' => 2.9,
                'fee_fixed' => 0.30,
            ],
            [
                'id' => 'paypal',
                'name' => 'PayPal',
                'icon' => 'paypal',
                'min_amount' => 5.00,
                'max_amount' => 10000.00,
                'fee_percent' => 3.49,
                'fee_fixed' => 0.49,
            ],
            [
                'id' => 'crypto',
                'name' => 'Cryptocurrency',
                'icon' => 'bitcoin',
                'min_amount' => 10.00,
                'max_amount' => 50000.00,
                'fee_percent' => 1.0,
                'fee_fixed' => 0.00,
            ],
            [
                'id' => 'bank_transfer',
                'name' => 'Bank Transfer',
                'icon' => 'bank',
                'min_amount' => 100.00,
                'max_amount' => 100000.00,
                'fee_percent' => 0.0,
                'fee_fixed' => 0.00,
            ],
        ];
    }

    /**
     * Process a refund for a transaction
     */
    public function refund(int $transactionId): ?array
    {
        $transaction = $this->transactionModel->find($transactionId);

        if (!$transaction) {
            return null;
        }

        // Can only refund completed credit transactions
        if ($transaction['type'] !== Transaction::TYPE_CREDIT ||
            $transaction['status'] !== Transaction::STATUS_COMPLETED) {
            return null;
        }

        // Check if user has enough balance
        $user = $this->userModel->find($transaction['user_id']);
        if (!$user || $user['balance'] < $transaction['amount']) {
            return null;
        }

        // Deduct balance
        $this->userModel->updateBalance($transaction['user_id'], -$transaction['amount']);

        // Update original transaction status
        $this->transactionModel->updateStatus($transactionId, Transaction::STATUS_REFUNDED);

        // Create refund transaction record
        $refundId = $this->transactionModel->create([
            'user_id' => $transaction['user_id'],
            'type' => Transaction::TYPE_DEBIT,
            'amount' => $transaction['amount'],
            'description' => 'Refund for transaction #' . $transactionId,
            'reference_id' => 'REF_' . $transaction['reference_id'],
            'payment_method' => $transaction['payment_method'],
            'status' => Transaction::STATUS_COMPLETED,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return [
            'refund_transaction_id' => $refundId,
            'original_transaction_id' => $transactionId,
            'amount' => $transaction['amount'],
            'status' => 'refunded',
        ];
    }

    /**
     * Generate checkout URL for payment provider
     */
    private function generateCheckoutUrl(string $method, string $referenceId, float $amount): string
    {
        $config = require CONFIG_PATH . '/app.php';
        $baseUrl = $config['url'] ?? 'http://localhost';

        // In production, this would integrate with actual payment providers
        return $baseUrl . '/payment/checkout?' . http_build_query([
            'method' => $method,
            'ref' => $referenceId,
            'amount' => $amount,
        ]);
    }

    /**
     * Map webhook status to internal status
     */
    private function mapWebhookStatus(string $webhookStatus): string
    {
        $statusMap = [
            'succeeded' => Transaction::STATUS_COMPLETED,
            'completed' => Transaction::STATUS_COMPLETED,
            'paid' => Transaction::STATUS_COMPLETED,
            'failed' => Transaction::STATUS_FAILED,
            'cancelled' => Transaction::STATUS_FAILED,
            'pending' => Transaction::STATUS_PENDING,
        ];

        return $statusMap[strtolower($webhookStatus)] ?? Transaction::STATUS_PENDING;
    }
}
