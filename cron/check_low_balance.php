<?php
/**
 * Low Balance Webhook Cron Job
 * 
 * Checks for users with low balance and triggers webhooks.
 * Run this periodically (e.g., every hour) via cron:
 * 0 * * * * php /path/to/cron/check_low_balance.php
 * 
 * Configure LOW_BALANCE_THRESHOLD in config/app.php or set directly below.
 */

// Bootstrap application
define('BASE_PATH', dirname(__DIR__));
define('CONFIG_PATH', BASE_PATH . '/config');
define('APP_PATH', BASE_PATH . '/app');
define('VIEWS_PATH', BASE_PATH . '/views');

// Load config
$config = require CONFIG_PATH . '/app.php';

// Low balance threshold (in credits/dollars)
$threshold = $config['low_balance_threshold'] ?? 10.00;

// Auto-loading function
spl_autoload_register(function ($class) {
    $paths = [
        APP_PATH . '/Controllers/' . $class . '.php',
        APP_PATH . '/Models/' . $class . '.php',
        APP_PATH . '/Services/' . $class . '.php',
        APP_PATH . '/Middleware/' . $class . '.php',
        APP_PATH . '/Helpers/' . $class . '.php',
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// Initialize services
try {
    $userModel = new User();
    $webhookModel = new Webhook();
    $webhookService = new WebhookService();
    
    echo "[" . date('Y-m-d H:i:s') . "] Starting low balance check (threshold: $" . number_format($threshold, 2) . ")\n";
    
    // Get all users with low balance who have active webhooks for this event
    $db = $userModel->db();
    
    // Find users with balance below threshold
    $sql = "SELECT DISTINCT u.id, u.balance, u.email, u.name
            FROM users u
            INNER JOIN webhooks w ON w.user_id = u.id
            WHERE u.balance < :threshold
            AND u.balance > 0
            AND w.is_active = 1
            AND JSON_CONTAINS(w.events, '\"low_balance\"')";
    
    $stmt = $db->prepare($sql);
    $stmt->execute(['threshold' => $threshold]);
    $lowBalanceUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "[" . date('Y-m-d H:i:s') . "] Found " . count($lowBalanceUsers) . " users with low balance\n";
    
    // Track notifications to avoid spamming
    $notifiedFile = BASE_PATH . '/storage/low_balance_notified.json';
    $notifiedUsers = [];
    
    if (file_exists($notifiedFile)) {
        $notifiedData = json_decode(file_get_contents($notifiedFile), true);
        if (is_array($notifiedData)) {
            // Clean up old entries (older than 24 hours)
            $cutoff = time() - 86400;
            foreach ($notifiedData as $userId => $timestamp) {
                if ($timestamp > $cutoff) {
                    $notifiedUsers[$userId] = $timestamp;
                }
            }
        }
    }
    
    $webhooksSent = 0;
    
    foreach ($lowBalanceUsers as $user) {
        // Skip if already notified in last 24 hours
        if (isset($notifiedUsers[$user['id']])) {
            echo "[" . date('Y-m-d H:i:s') . "] Skipping user {$user['id']} (already notified recently)\n";
            continue;
        }
        
        try {
            $results = $webhookService->triggerLowBalance(
                $user['id'],
                (float) $user['balance'],
                (float) $threshold
            );
            
            foreach ($results as $result) {
                if ($result['success']) {
                    $webhooksSent++;
                    echo "[" . date('Y-m-d H:i:s') . "] Webhook sent to user {$user['id']}: {$result['url']}\n";
                } else {
                    echo "[" . date('Y-m-d H:i:s') . "] Webhook failed for user {$user['id']}: HTTP {$result['response_code']}\n";
                }
            }
            
            // Mark as notified
            $notifiedUsers[$user['id']] = time();
            
        } catch (Exception $e) {
            echo "[" . date('Y-m-d H:i:s') . "] Error processing user {$user['id']}: {$e->getMessage()}\n";
        }
    }
    
    // Save notified users
    $storageDir = dirname($notifiedFile);
    if (!is_dir($storageDir)) {
        mkdir($storageDir, 0755, true);
    }
    file_put_contents($notifiedFile, json_encode($notifiedUsers));
    
    echo "[" . date('Y-m-d H:i:s') . "] Completed. Sent {$webhooksSent} webhooks.\n";
    
} catch (Exception $e) {
    echo "[" . date('Y-m-d H:i:s') . "] Fatal error: {$e->getMessage()}\n";
    exit(1);
}
