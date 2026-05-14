<?php
/**
 * Auto Top-Up Service
 * Handles automatic deposit creation when balance falls below threshold
 */

class AutoTopupService
{
    private AutoTopupSetting $settingModel;
    private Deposit $depositModel;
    private NotificationService $notificationService;
    private ?TelegramService $telegramService = null;

    public function __construct()
    {
        $this->settingModel = new AutoTopupSetting();
        $this->depositModel = new Deposit();
        $this->notificationService = new NotificationService(new Notification());
        
        // Try to load Telegram service (optional)
        try {
            $this->telegramService = new TelegramService();
        } catch (Exception $e) {
            // Telegram not available
        }
    }

    /**
     * Check if auto top-up should trigger for a user and create deposit if needed
     *
     * @param int $userId The user ID
     * @param float $currentBalance The user's current balance after deduction
     * @return array|null Returns deposit data if created, null otherwise
     */
    public function checkAndTrigger(int $userId, float $currentBalance): ?array
    {
        try {
            // Get user's auto top-up settings
            $setting = $this->settingModel->getByUser($userId);
            
            if (!$setting) {
                return null;
            }

            // Check if should trigger
            if (!$this->settingModel->shouldTrigger($setting, $currentBalance)) {
                return null;
            }

            // Check cooldown
            if ($this->isOnCooldown($setting)) {
                return null;
            }

            // Create the auto deposit
            $deposit = $this->createAutoDeposit($userId, (float) $setting['amount']);
            
            if ($deposit) {
                // Record that we triggered
                $this->settingModel->recordTrigger($setting['id']);

                // Send notifications
                $this->sendNotifications($userId, $deposit);

                return $deposit;
            }

            return null;
        } catch (Exception $e) {
            error_log('AutoTopupService error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create an automatic deposit request
     */
    public function createAutoDeposit(int $userId, float $amount): ?array
    {
        try {
            // Generate reference code
            $referenceCode = 'AUTO-' . strtoupper(bin2hex(random_bytes(4))) . '-' . time();

            // Create the deposit
            $depositId = $this->depositModel->create([
                'user_id' => $userId,
                'amount' => $amount,
                'reference_code' => $referenceCode,
                'status' => 'pending',
                'payment_method' => 'auto_topup',
                'notes' => 'Automatic top-up triggered due to low balance'
            ]);

            if ($depositId) {
                return $this->depositModel->find($depositId);
            }

            return null;
        } catch (Exception $e) {
            error_log('AutoTopupService createAutoDeposit error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if the user is still within the cooldown period
     */
    public function isOnCooldown(array $setting): bool
    {
        if (empty($setting['last_triggered_at'])) {
            return false;
        }

        $lastTriggered = strtotime($setting['last_triggered_at']);
        $cooldownSeconds = ((int) ($setting['cooldown_hours'] ?? 24)) * 3600;
        
        return time() < ($lastTriggered + $cooldownSeconds);
    }

    /**
     * Get cooldown remaining time in hours
     */
    public function getCooldownRemaining(array $setting): float
    {
        if (!$this->isOnCooldown($setting)) {
            return 0;
        }

        $lastTriggered = strtotime($setting['last_triggered_at']);
        $cooldownSeconds = ((int) ($setting['cooldown_hours'] ?? 24)) * 3600;
        $remainingSeconds = ($lastTriggered + $cooldownSeconds) - time();
        
        return round($remainingSeconds / 3600, 1);
    }

    /**
     * Send notifications about auto top-up trigger
     */
    private function sendNotifications(int $userId, array $deposit): void
    {
        $amount = number_format($deposit['amount'], 2);

        // Send in-app notification
        $this->notificationService->send(
            $userId,
            'Auto Top-Up Triggered',
            "Your balance dropped below the threshold. A deposit request for \${$amount} has been created automatically. Please complete the payment.",
            'warning'
        );

        // Send Telegram notification if available
        if ($this->telegramService && $this->telegramService->isConfigured()) {
            $this->telegramService->sendNotification($userId, 'auto_topup', [
                'amount' => $deposit['amount'],
                'reference' => $deposit['reference_code']
            ]);
        }
    }

    /**
     * Get settings for a user, creating default if not exists
     */
    public function getOrCreateSettings(int $userId): array
    {
        $setting = $this->settingModel->getByUser($userId);
        
        if ($setting) {
            return $setting;
        }

        // Create default settings (inactive by default)
        $id = $this->settingModel->create([
            'user_id' => $userId,
            'threshold' => 10.00,
            'amount' => 50.00,
            'is_active' => 0,
            'cooldown_hours' => 24
        ]);

        return $this->settingModel->find($id);
    }
}
