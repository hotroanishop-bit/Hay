<?php
/**
 * Coupon Service
 * Business logic for coupon validation and application
 */

class CouponService
{
    private Coupon $couponModel;
    private CouponUsage $couponUsageModel;

    public function __construct()
    {
        $this->couponModel = new Coupon();
        $this->couponUsageModel = new CouponUsage();
    }

    /**
     * Validate a coupon code
     * Returns array with validation result and coupon details
     */
    public function validateCoupon(string $code, int $userId, float $depositAmount): array
    {
        // Find coupon
        $coupon = $this->couponModel->findByCode($code);
        
        // Check validity
        $validation = $this->couponModel->isValid($coupon, $userId, $depositAmount);
        
        if (!$validation['valid']) {
            return [
                'valid' => false,
                'message' => $validation['message'],
                'coupon' => null,
                'discount' => 0,
                'bonus' => 0,
                'final_amount' => $depositAmount
            ];
        }

        // Calculate discount/bonus
        $calculation = $this->calculateDiscount($coupon, $depositAmount);

        return [
            'valid' => true,
            'message' => $this->getSuccessMessage($coupon, $calculation),
            'coupon' => $coupon,
            'discount' => $calculation['discount'],
            'bonus' => $calculation['bonus'],
            'final_amount' => $calculation['final_amount'],
            'type' => $coupon['type'],
            'value' => (float)$coupon['value']
        ];
    }

    /**
     * Calculate discount or bonus based on coupon type
     */
    public function calculateDiscount(array $coupon, float $depositAmount): array
    {
        $discount = 0;
        $bonus = 0;
        $finalAmount = $depositAmount;
        $type = $coupon['type'] ?? 'percentage';
        $value = (float)($coupon['value'] ?? 0);

        switch ($type) {
            case 'percentage':
                // Percentage discount off the deposit amount
                $discount = $depositAmount * ($value / 100);
                // User pays less
                $finalAmount = $depositAmount - $discount;
                break;

            case 'fixed':
                // Fixed discount amount
                $discount = min($value, $depositAmount);
                // User pays less
                $finalAmount = $depositAmount - $discount;
                break;

            case 'bonus':
                // Bonus credits added to balance
                // User pays full amount but gets extra credits
                $bonus = $value;
                $finalAmount = $depositAmount;
                break;
        }

        return [
            'discount' => round($discount, 2),
            'bonus' => round($bonus, 2),
            'final_amount' => round($finalAmount, 2),
            'savings' => round($discount + $bonus, 2)
        ];
    }

    /**
     * Apply coupon and record usage
     */
    public function applyCoupon(int $couponId, int $userId, ?int $depositId, float $amountSaved): bool
    {
        // Increment usage count
        $this->couponModel->incrementUsage($couponId);

        // Record usage
        $this->couponUsageModel->recordUsage($couponId, $userId, $depositId, $amountSaved);

        return true;
    }

    /**
     * Get coupon statistics
     */
    public function getCouponStats(int $couponId): array
    {
        return $this->couponModel->getStats($couponId);
    }

    /**
     * Get user's coupon usage history
     */
    public function getUserCouponHistory(int $userId): array
    {
        return $this->couponUsageModel->getByUser($userId);
    }

    /**
     * Get all active coupons
     */
    public function getActiveCoupons(): array
    {
        return $this->couponModel->getActive();
    }

    /**
     * Generate success message based on coupon type
     */
    private function getSuccessMessage(array $coupon, array $calculation): string
    {
        $type = $coupon['type'] ?? 'percentage';
        $value = (float)($coupon['value'] ?? 0);

        switch ($type) {
            case 'percentage':
                return sprintf(
                    'Coupon applied! You get %d%% off (-%s VND)',
                    (int)$value,
                    number_format($calculation['discount'])
                );

            case 'fixed':
                return sprintf(
                    'Coupon applied! You save %s VND',
                    number_format($calculation['discount'])
                );

            case 'bonus':
                return sprintf(
                    'Coupon applied! You will receive %s VND bonus credits',
                    number_format($calculation['bonus'])
                );

            default:
                return 'Coupon applied successfully!';
        }
    }

    /**
     * Validate coupon for AJAX request
     * Returns JSON-friendly array
     */
    public function validateForAjax(string $code, int $userId, float $depositAmount): array
    {
        $result = $this->validateCoupon($code, $userId, $depositAmount);

        return [
            'success' => $result['valid'],
            'message' => $result['message'],
            'data' => $result['valid'] ? [
                'coupon_id' => $result['coupon']['id'] ?? null,
                'code' => $result['coupon']['code'] ?? '',
                'type' => $result['type'] ?? '',
                'value' => $result['value'] ?? 0,
                'discount' => $result['discount'],
                'bonus' => $result['bonus'],
                'final_amount' => $result['final_amount'],
                'description' => $result['coupon']['description'] ?? ''
            ] : null
        ];
    }
}
