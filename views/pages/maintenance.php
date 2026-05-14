<?php
/**
 * Maintenance Page
 * Displayed when the site is under scheduled maintenance
 * Variables: $siteName, $maintenance
 */

$title = $maintenance['title'] ?? 'Scheduled Maintenance';
$message = $maintenance['message'] ?? 'We are currently performing scheduled maintenance. Please check back soon.';
$endsAt = $maintenance['ends_at'] ?? null;
$showCountdown = $maintenance['show_countdown'] ?? true;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="60">
    <title><?= htmlspecialchars($title) ?> - <?= htmlspecialchars($siteName ?? 'API Gateway') ?></title>
    <link rel="stylesheet" href="/css/pages/maintenance.css">
</head>
<body>
    <div class="maintenance-container">
        <div class="maintenance-content">
            <div class="maintenance-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
                </svg>
            </div>
            
            <h1 class="maintenance-title"><?= htmlspecialchars($title) ?></h1>
            
            <p class="maintenance-message"><?= nl2br(htmlspecialchars($message)) ?></p>
            
            <?php if ($showCountdown && $endsAt): ?>
            <div class="countdown-section">
                <p class="countdown-label">Expected to be back in:</p>
                <div class="countdown" id="countdown" data-end-time="<?= htmlspecialchars($endsAt) ?>">
                    <div class="countdown-item">
                        <span class="countdown-value" id="hours">00</span>
                        <span class="countdown-unit">Hours</span>
                    </div>
                    <div class="countdown-separator">:</div>
                    <div class="countdown-item">
                        <span class="countdown-value" id="minutes">00</span>
                        <span class="countdown-unit">Minutes</span>
                    </div>
                    <div class="countdown-separator">:</div>
                    <div class="countdown-item">
                        <span class="countdown-value" id="seconds">00</span>
                        <span class="countdown-unit">Seconds</span>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="maintenance-footer">
                <p>We apologize for any inconvenience.</p>
                <p class="site-name"><?= htmlspecialchars($siteName ?? 'API Gateway') ?></p>
            </div>
        </div>
        
        <div class="maintenance-animation">
            <div class="gear gear-1"></div>
            <div class="gear gear-2"></div>
        </div>
    </div>
    
    <script src="/js/pages/maintenance.js"></script>
</body>
</html>
