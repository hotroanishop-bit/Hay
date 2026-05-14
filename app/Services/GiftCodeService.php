<?php
/**
 * Gift Code Service
 * Business logic for gift code redemption
 */
class GiftCodeService
{
    private GiftCode $giftCodeModel;
    private User $userModel;

    public function __construct()
    {
        $this->giftCodeModel = new GiftCode();
        $this->userModel = new User();
    }

    /**
     * Redeem a gift code for user
     */
    public function redeem(string $code, int $userId): array
    {
        // Find the gift code
        $giftCode = $this->giftCodeModel->findByCode($code);
        
        if (!$giftCode) {
            return [
                'success' => false,
                'message' => 'Gift code khong ton tai'
            ];
        }

        // Validate
        $validation = $this->giftCodeModel->isValidForRedemption($giftCode, $userId);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'message' => $validation['error']
            ];
        }

        // Get user
        $user = $this->userModel->find($userId);
        if (!$user) {
            return [
                'success' => false,
                'message' => 'User khong ton tai'
            ];
        }

        // Process based on type
        $result = $this->processRedemption($giftCode, $user);
        
        if ($result['success']) {
            // Record redemption
            $this->giftCodeModel->recordRedemption($giftCode['id'], $userId, $result['value']);
            
            // Increment used count
            $this->giftCodeModel->incrementUsedCount($giftCode['id']);

            // Create notification
            $this->createRedemptionNotification($userId, $giftCode, $result['value']);
        }

        return $result;
    }

    /**
     * Process redemption based on gift code type
     */
    private function processRedemption(array $giftCode, array $user): array
    {
        switch ($giftCode['type']) {
            case 'tokens':
            case 'credits':
                return $this->addCredits($user['id'], $giftCode['value']);

            case 'plan':
                return $this->applyPlan($user['id'], (int) $giftCode['value']);

            case 'vip_days':
                return $this->addVipDays($user['id'], (int) $giftCode['value']);

            default:
                return [
                    'success' => false,
                    'message' => 'Loai gift code khong ho tro'
                ];
        }
    }

    /**
     * Add credits/tokens to user balance
     */
    private function addCredits(int $userId, float $amount): array
    {
        try {
            // Update user balance
            $sql = "UPDATE users SET balance = balance + :amount WHERE id = :user_id";
            $stmt = $this->userModel->db()->prepare($sql);
            $stmt->execute([
                'amount' => $amount,
                'user_id' => $userId
            ]);

            // Record transaction
            $transactionSql = "INSERT INTO transactions (user_id, type, amount, description, status, created_at)
                               VALUES (:user_id, 'credit', :amount, :description, 'completed', NOW())";
            $stmt = $this->userModel->db()->prepare($transactionSql);
            $stmt->execute([
                'user_id' => $userId,
                'amount' => $amount,
                'description' => 'Gift code redemption'
            ]);

            return [
                'success' => true,
                'message' => 'Ban da nhan duoc ' . number_format($amount, 2) . ' credits!',
                'value' => $amount,
                'type' => 'credits'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Loi khi cong credits: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Apply a plan to user
     */
    private function applyPlan(int $userId, int $planId): array
    {
        try {
            // Check if plan exists
            $planModel = new Plan();
            $plan = $planModel->find($planId);
            
            if (!$plan) {
                return [
                    'success' => false,
                    'message' => 'Goi cuoc khong ton tai'
                ];
            }

            // Calculate expiry date
            $expiresAt = date('Y-m-d H:i:s', strtotime('+1 month'));

            // Update or insert user plan
            $sql = "INSERT INTO user_plans (user_id, plan_id, starts_at, expires_at, is_active)
                    VALUES (:user_id, :plan_id, NOW(), :expires_at, 1)
                    ON DUPLICATE KEY UPDATE 
                    plan_id = :plan_id, 
                    expires_at = DATE_ADD(IFNULL(expires_at, NOW()), INTERVAL 1 MONTH),
                    is_active = 1";
            $stmt = $this->userModel->db()->prepare($sql);
            $stmt->execute([
                'user_id' => $userId,
                'plan_id' => $planId,
                'expires_at' => $expiresAt
            ]);

            return [
                'success' => true,
                'message' => 'Ban da nhan duoc goi ' . $plan['name'] . '!',
                'value' => $planId,
                'type' => 'plan'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Loi khi ap dung goi cuoc: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Add VIP days to user
     */
    private function addVipDays(int $userId, int $days): array
    {
        try {
            // Get current user plan or create new one
            $sql = "UPDATE user_plans SET 
                    expires_at = DATE_ADD(IFNULL(expires_at, NOW()), INTERVAL :days DAY)
                    WHERE user_id = :user_id AND is_active = 1";
            $stmt = $this->userModel->db()->prepare($sql);
            $stmt->execute([
                'days' => $days,
                'user_id' => $userId
            ]);

            return [
                'success' => true,
                'message' => 'Ban da nhan duoc ' . $days . ' ngay VIP!',
                'value' => $days,
                'type' => 'vip_days'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Loi khi them ngay VIP: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create notification for redemption
     */
    private function createRedemptionNotification(int $userId, array $giftCode, $value): void
    {
        try {
            $typeLabels = [
                'tokens' => 'tokens',
                'credits' => 'credits',
                'plan' => 'goi cuoc',
                'vip_days' => 'ngay VIP'
            ];

            $sql = "INSERT INTO user_notifications (user_id, type, title, message, created_at)
                    VALUES (:user_id, 'success', :title, :message, NOW())";
            $stmt = $this->userModel->db()->prepare($sql);
            $stmt->execute([
                'user_id' => $userId,
                'title' => 'Gift Code Thanh Cong!',
                'message' => 'Ban da nhan duoc ' . $value . ' ' . ($typeLabels[$giftCode['type']] ?? 'phan thuong') . ' tu gift code ' . $giftCode['code']
            ]);
        } catch (\Exception $e) {
            // Silent fail for notification
        }
    }

    /**
     * Get user redemption history
     */
    public function getUserHistory(int $userId, int $limit = 20): array
    {
        return $this->giftCodeModel->getUserRedemptions($userId, $limit);
    }

    /**
     * Admin: Create single gift code
     */
    public function createCode(array $data, int $adminId): array
    {
        try {
            $code = !empty($data['code']) 
                ? strtoupper(trim($data['code']))
                : $this->giftCodeModel->generateUniqueCode($data['prefix'] ?? 'GIFT');

            // Check if code already exists
            if ($this->giftCodeModel->findByCode($code)) {
                return [
                    'success' => false,
                    'message' => 'Gift code da ton tai'
                ];
            }

            $id = $this->giftCodeModel->create([
                'code' => $code,
                'type' => $data['type'] ?? 'tokens',
                'value' => $data['value'] ?? 0,
                'max_uses' => $data['max_uses'] ?? 1,
                'expires_at' => !empty($data['expires_at']) ? $data['expires_at'] : null,
                'is_active' => 1,
                'created_by' => $adminId
            ]);

            return [
                'success' => true,
                'message' => 'Tao gift code thanh cong',
                'code' => $code,
                'id' => $id
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Loi khi tao gift code: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Admin: Bulk create gift codes
     */
    public function bulkCreate(int $count, array $data, int $adminId): array
    {
        if ($count < 1 || $count > 100) {
            return [
                'success' => false,
                'message' => 'So luong gift code phai tu 1-100'
            ];
        }

        return $this->giftCodeModel->bulkCreate($count, $data, $adminId);
    }

    /**
     * Admin: Get all codes with pagination
     */
    public function getAll(int $page = 1, int $perPage = 20, array $filters = []): array
    {
        return $this->giftCodeModel->getAllWithPagination($page, $perPage, $filters);
    }

    /**
     * Admin: Toggle code status
     */
    public function toggleStatus(int $id): bool
    {
        return $this->giftCodeModel->toggleActive($id);
    }

    /**
     * Admin: Get code details with redemptions
     */
    public function getCodeDetails(int $id): ?array
    {
        $code = $this->giftCodeModel->find($id);
        if (!$code) {
            return null;
        }

        $code['redemptions'] = $this->giftCodeModel->getRedemptions($id);
        return $code;
    }

    /**
     * Admin: Get statistics
     */
    public function getStats(): array
    {
        return $this->giftCodeModel->getStats();
    }
}
