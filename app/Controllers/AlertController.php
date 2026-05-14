<?php
/**
 * Alert Controller
 * Handles usage alert settings
 */
class AlertController extends BaseController
{
    public function __construct()
    {
        $this->currentPage = 'settings-alerts';
    }

    /**
     * Show alerts settings page
     */
    public function index(): void
    {
        $userId = $_SESSION['user_id'] ?? 0;
        $db = (new User())->db();

        // Get user alerts
        $sql = "SELECT * FROM usage_alerts WHERE user_id = :user_id";
        $stmt = $db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        $alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Convert to associative array by type
        $alertsByType = [];
        foreach ($alerts as $alert) {
            $alertsByType[$alert['alert_type']] = $alert;
        }

        // Default alert types
        $alertTypes = [
            'low_balance' => [
                'label' => 'So du thap',
                'description' => 'Canh bao khi so du duoi nguong',
                'default_threshold' => 10
            ],
            'daily_limit' => [
                'label' => 'Gioi han ngay',
                'description' => 'Canh bao khi chi tieu ngay vuot nguong',
                'default_threshold' => 50
            ],
            'monthly_limit' => [
                'label' => 'Gioi han thang',
                'description' => 'Canh bao khi chi tieu thang vuot nguong',
                'default_threshold' => 500
            ]
        ];

        $this->render('settings/alerts', [
            'pageTitle' => __('alerts.title', 'Canh bao su dung'),
            'alertsByType' => $alertsByType,
            'alertTypes' => $alertTypes
        ], ['pages/settings'], ['pages/settings']);
    }

    /**
     * Save alert settings
     */
    public function save(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/settings/alerts');
            return;
        }

        $userId = $_SESSION['user_id'] ?? 0;
        $db = (new User())->db();

        $alertTypes = ['low_balance', 'daily_limit', 'monthly_limit'];

        foreach ($alertTypes as $type) {
            $isEnabled = isset($_POST["enabled_{$type}"]) ? 1 : 0;
            $threshold = floatval($_POST["threshold_{$type}"] ?? 0);
            $notifyEmail = isset($_POST["notify_email_{$type}"]) ? 1 : 0;
            $notifyTelegram = isset($_POST["notify_telegram_{$type}"]) ? 1 : 0;

            $sql = "INSERT INTO usage_alerts (user_id, alert_type, threshold, is_enabled, notify_email, notify_telegram, created_at)
                    VALUES (:user_id, :alert_type, :threshold, :is_enabled, :notify_email, :notify_telegram, NOW())
                    ON DUPLICATE KEY UPDATE 
                    threshold = :threshold,
                    is_enabled = :is_enabled,
                    notify_email = :notify_email,
                    notify_telegram = :notify_telegram,
                    updated_at = NOW()";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'user_id' => $userId,
                'alert_type' => $type,
                'threshold' => $threshold,
                'is_enabled' => $isEnabled,
                'notify_email' => $notifyEmail,
                'notify_telegram' => $notifyTelegram
            ]);
        }

        $this->setFlash('success', 'Luu cai dat canh bao thanh cong');
        $this->redirect('/settings/alerts');
    }
}
