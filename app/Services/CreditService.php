<?php
/**
 * Credit Service
 * Handles user credit balance operations
 */

class CreditService
{
    private User $userModel;
    private Transaction $transactionModel;
    private ?ModelPricing $modelPricingModel = null;

    public function __construct(User $userModel, Transaction $transactionModel, ?ModelPricing $modelPricingModel = null)
    {
        $this->userModel = $userModel;
        $this->transactionModel = $transactionModel;
        $this->modelPricingModel = $modelPricingModel;
    }

    /**
     * Set the ModelPricing model (for dependency injection)
     */
    public function setModelPricing(ModelPricing $modelPricingModel): void
    {
        $this->modelPricingModel = $modelPricingModel;
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

    /**
     * Deduct credits for API usage based on model pricing
     * 
     * @param int $userId The user ID
     * @param string $model The model name
     * @param int $inputTokens Number of input tokens used
     * @param int $outputTokens Number of output tokens used
     * @param float $priceMultiplier Plan-based price multiplier (default 1.0)
     * @return int Transaction ID
     * @throws Exception If insufficient balance or ModelPricing not configured
     */
    public function deductForApiUsage(int $userId, string $model, int $inputTokens, int $outputTokens, float $priceMultiplier = 1.0): int
    {
        if (!$this->modelPricingModel) {
            throw new Exception('ModelPricing model not configured');
        }

        $cost = $this->estimateCost($model, $inputTokens, $outputTokens, $priceMultiplier);
        
        if ($cost <= 0) {
            // No cost, no deduction needed
            return 0;
        }

        $description = sprintf(
            'API usage: %s (%d input, %d output tokens)',
            $model,
            $inputTokens,
            $outputTokens
        );

        return $this->deductCredits($userId, $cost, $description);
    }

    /**
     * Estimate cost for API usage without deducting
     * 
     * @param string $model The model name
     * @param int $inputTokens Number of input tokens
     * @param int $outputTokens Number of output tokens
     * @param float $multiplier Plan-based price multiplier (default 1.0)
     * @return float Estimated cost
     */
    public function estimateCost(string $model, int $inputTokens, int $outputTokens, float $multiplier = 1.0): float
    {
        if (!$this->modelPricingModel) {
            return 0.0;
        }

        $baseCost = $this->modelPricingModel->calculateCost($model, $inputTokens, $outputTokens);
        
        return $baseCost * $multiplier;
    }

    /**
     * Pre-flight check if user has sufficient balance for an API request
     * 
     * @param int $userId The user ID
     * @param string $model The model name
     * @param int $estimatedInputTokens Estimated input tokens
     * @param int $estimatedOutputTokens Estimated output tokens (default 0)
     * @return bool True if user has sufficient balance
     */
    public function checkSufficientBalanceForRequest(int $userId, string $model, int $estimatedInputTokens, int $estimatedOutputTokens = 0): bool
    {
        // Get the estimated cost (using default multiplier for conservative estimate)
        $estimatedCost = $this->estimateCost($model, $estimatedInputTokens, $estimatedOutputTokens, 1.0);
        
        if ($estimatedCost <= 0) {
            // No cost means sufficient balance
            return true;
        }

        return $this->checkSufficientBalance($userId, $estimatedCost);
    }
}
