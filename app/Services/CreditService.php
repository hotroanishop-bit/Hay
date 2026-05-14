<?php
/**
 * Credit Service
 * Handles user credit balance operations
 */

class CreditService
{
    private User $userModel;
    private Transaction $transactionModel;

    public function __construct(User $userModel, Transaction $transactionModel)
    {
        $this->userModel = $userModel;
        $this->transactionModel = $transactionModel;
    }

    /**
     * Add credits to a user's account
     */
    public function addCredits(int $userId, float $amount, string $description): int
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Credit amount must be positive');
        }

        // Create transaction record
        $transactionId = $this->transactionModel->createCredit($userId, $amount, $description);

        // Update user balance
        $this->userModel->updateBalance($userId, $amount);

        return $transactionId;
    }

    /**
     * Deduct credits from a user's account
     */
    public function deductCredits(int $userId, float $amount, string $description): int
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Deduct amount must be positive');
        }

        // Check sufficient balance
        if (!$this->checkSufficientBalance($userId, $amount)) {
            throw new Exception('Insufficient balance');
        }

        // Create transaction record
        $transactionId = $this->transactionModel->createDebit($userId, $amount, $description);

        // Update user balance
        $this->userModel->updateBalance($userId, -$amount);

        return $transactionId;
    }

    /**
     * Get a user's current balance
     */
    public function getBalance(int $userId): float
    {
        return $this->userModel->getBalance($userId);
    }

    /**
     * Check if user has sufficient balance for an amount
     */
    public function checkSufficientBalance(int $userId, float $amount): bool
    {
        $balance = $this->getBalance($userId);
        return $balance >= $amount;
    }

    /**
     * Transfer credits between users
     */
    public function transferCredits(int $fromId, int $toId, float $amount): array
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Transfer amount must be positive');
        }

        if ($fromId === $toId) {
            throw new InvalidArgumentException('Cannot transfer to same user');
        }

        // Check sufficient balance
        if (!$this->checkSufficientBalance($fromId, $amount)) {
            throw new Exception('Insufficient balance for transfer');
        }

        // Use transaction for atomicity
        $this->userModel->beginTransaction();

        try {
            // Deduct from sender
            $debitId = $this->transactionModel->createDebit(
                $fromId,
                $amount,
                'Transfer to user #' . $toId
            );
            $this->userModel->updateBalance($fromId, -$amount);

            // Add to receiver
            $creditId = $this->transactionModel->createCredit(
                $toId,
                $amount,
                'Transfer from user #' . $fromId
            );
            $this->userModel->updateBalance($toId, $amount);

            $this->userModel->commit();

            return [
                'debit_transaction_id' => $debitId,
                'credit_transaction_id' => $creditId,
                'amount' => $amount,
                'from_user_id' => $fromId,
                'to_user_id' => $toId,
            ];
        } catch (Exception $e) {
            $this->userModel->rollback();
            throw $e;
        }
    }
}
