<?php
/**
 * Referral Service
 * Handles referral code generation, tracking, and commission calculations
 */

class ReferralService
{
    private User $userModel;
    private Referral $referralModel;
    private Transaction $transactionModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->referralModel = new Referral();
        $this->transactionModel = new Transaction();
    }

    /**
     * Generate a unique referral code for a user
     */
    public function generateReferralCode(int $userId): string
    {
        // Generate 8-character alphanumeric code
        $code = $this->createUniqueCode();
        
        // Update user's referral code
        $this->updateUserReferralCode($userId, $code);
        
        return $code;
    }

    /**
     * Create a unique 8-character code
     */
    private function createUniqueCode(): string
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $maxAttempts = 10;
        
        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            $code = '';
            for ($i = 0; $i < 8; $i++) {
                $code .= $characters[random_int(0, strlen($characters) - 1)];
            }
            
            // Check if code is unique
            if (!$this->referralCodeExists($code)) {
                return $code;
            }
        }
        
        // Fallback: include timestamp-based suffix
        return strtoupper(substr(md5(uniqid((string)mt_rand(), true)), 0, 8));
    }

    /**
     * Check if a referral code already exists
     */
    private function referralCodeExists(string $code): bool
    {
        $sql = "SELECT COUNT(*) as count FROM users WHERE referral_code = :code";
        $result = $this->userModel->query($sql, ['code' => $code]);
        return (int)($result[0]['count'] ?? 0) > 0;
    }

    /**
     * Update user's referral code in database
     */
    private function updateUserReferralCode(int $userId, string $code): bool
    {
        $sql = "UPDATE users SET referral_code = :code, updated_at = NOW() WHERE id = :id";
        return $this->userModel->execute($sql, ['code' => $code, 'id' => $userId]);
    }

    /**
     * Get or generate referral code for a user
     */
    public function getOrGenerateReferralCode(int $userId): string
    {
        $user = $this->userModel->find($userId);
        
        if ($user && !empty($user['referral_code'])) {
            return $user['referral_code'];
        }
        
        return $this->generateReferralCode($userId);
    }

    /**
     * Get the full referral link for a user
     */
    public function getReferralLink(int $userId): string
    {
        $code = $this->getOrGenerateReferralCode($userId);
        
        // Get base URL from config
        $config = require CONFIG_PATH . '/app.php';
        $baseUrl = rtrim($config['url'] ?? 'http://localhost', '/');
        
        return $baseUrl . '/register?ref=' . $code;
    }

    /**
     * Track a referral when a new user registers
     */
    public function trackReferral(int $referredUserId, string $referralCode): bool
    {
        // Find the referrer by code
        $sql = "SELECT id FROM users WHERE referral_code = :code";
        $result = $this->userModel->query($sql, ['code' => $referralCode]);
        
        if (empty($result)) {
            return false;
        }
        
        $referrerId = (int) $result[0]['id'];
        
        // Don't allow self-referral
        if ($referrerId === $referredUserId) {
            return false;
        }
        
        // Check if referral already exists
        $existingReferral = $this->referralModel->findByReferredUser($referredUserId);
        if ($existingReferral) {
            return false;
        }
        
        // Create referral record
        $this->referralModel->createReferral($referrerId, $referredUserId);
        
        // Update referred_by field on user
        $sql = "UPDATE users SET referred_by = :referrer_id, updated_at = NOW() WHERE id = :id";
        $this->userModel->execute($sql, [
            'referrer_id' => $referrerId,
            'id' => $referredUserId
        ]);
        
        return true;
    }

    /**
     * Get commission rate from settings
     */
    public function getCommissionRate(): float
    {
        $sql = "SELECT setting_value FROM settings WHERE setting_key = 'referral_commission_rate'";
        $result = $this->userModel->query($sql);
        
        if (!empty($result)) {
            return (float) $result[0]['setting_value'];
        }
        
        return 0.05; // Default 5%
    }

    /**
     * Calculate commission for a deposit amount
     */
    public function calculateCommission(float $depositAmount): float
    {
        $rate = $this->getCommissionRate();
        return round($depositAmount * $rate, 2);
    }

    /**
     * Credit commission to referrer when referred user makes a deposit
     */
    public function creditCommission(int $referrerId, float $amount, int $referralId): bool
    {
        if ($amount <= 0) {
            return false;
        }

        // Update referral commission
        $this->referralModel->updateCommission($referralId, $amount);
        
        // Auto-approve the referral if it was pending
        $referral = $this->referralModel->find($referralId);
        if ($referral && $referral['status'] === Referral::STATUS_PENDING) {
            $this->referralModel->approve($referralId);
        }
        
        // Update user's total referral earnings
        $sql = "UPDATE users SET total_referral_earnings = total_referral_earnings + :amount, updated_at = NOW() WHERE id = :id";
        $this->userModel->execute($sql, [
            'amount' => $amount,
            'id' => $referrerId
        ]);
        
        return true;
    }

    /**
     * Process referral commission for a deposit
     */
    public function processDepositCommission(int $depositUserId, float $depositAmount): bool
    {
        // Find if the depositing user was referred
        $referral = $this->referralModel->findByReferredUser($depositUserId);
        
        if (!$referral) {
            return false;
        }
        
        $commission = $this->calculateCommission($depositAmount);
        
        if ($commission <= 0) {
            return false;
        }
        
        return $this->creditCommission(
            (int) $referral['referrer_id'], 
            $commission, 
            (int) $referral['id']
        );
    }

    /**
     * Withdraw referral earnings to main balance
     */
    public function withdrawToBalance(int $userId): array
    {
        // Get available earnings (approved referrals)
        $availableEarnings = $this->referralModel->getPendingEarnings($userId);
        
        if ($availableEarnings <= 0) {
            return [
                'success' => false,
                'message' => 'No earnings available to withdraw'
            ];
        }
        
        try {
            // Begin transaction
            $this->userModel->beginTransaction();
            
            // Add to user balance
            $this->userModel->updateBalance($userId, $availableEarnings);
            
            // Create transaction record
            $this->transactionModel->createCredit(
                $userId,
                $availableEarnings,
                'Referral earnings withdrawal'
            );
            
            // Mark all approved referrals as paid
            $this->referralModel->markAllApprovedAsPaid($userId);
            
            $this->userModel->commit();
            
            return [
                'success' => true,
                'message' => 'Earnings withdrawn successfully',
                'amount' => $availableEarnings
            ];
        } catch (Exception $e) {
            $this->userModel->rollback();
            return [
                'success' => false,
                'message' => 'Failed to withdraw earnings: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get referral statistics for a user
     */
    public function getReferralStats(int $userId): array
    {
        $stats = $this->referralModel->getStats($userId);
        
        return [
            'total_referrals' => (int) ($stats['total_referrals'] ?? 0),
            'pending' => (int) ($stats['pending_count'] ?? 0),
            'approved' => (int) ($stats['approved_count'] ?? 0),
            'paid' => (int) ($stats['paid_count'] ?? 0),
            'total_earned' => (float) ($stats['total_earned'] ?? 0),
            'available_to_withdraw' => (float) ($stats['available_to_withdraw'] ?? 0),
            'commission_rate' => $this->getCommissionRate() * 100
        ];
    }

    /**
     * Get referral by code
     */
    public function getUserByReferralCode(string $code): ?array
    {
        $sql = "SELECT id, name, email, referral_code FROM users WHERE referral_code = :code";
        $result = $this->userModel->query($sql, ['code' => $code]);
        
        return $result[0] ?? null;
    }

    /**
     * Validate referral code
     */
    public function validateReferralCode(string $code): bool
    {
        if (empty($code) || strlen($code) < 6) {
            return false;
        }
        
        return $this->getUserByReferralCode($code) !== null;
    }
}
