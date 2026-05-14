<?php
/**
 * Auto Top-Up Settings Page
 * Variables: $pageTitle, $currentPage, $user, $settings, $isOnCooldown, $cooldownRemaining
 */

$isActive = !empty($settings['is_active']);
$threshold = $settings['threshold'] ?? 10;
$amount = $settings['amount'] ?? 50;
$cooldownHours = $settings['cooldown_hours'] ?? 24;
$lastTriggered = $settings['last_triggered_at'] ?? null;
?>

<div class="page-header">
    <div class="page-header-content">
        <h1>Auto Top-Up</h1>
        <p>Automatically create a deposit request when your balance is low</p>
    </div>
    <div class="page-header-actions">
        <a href="/billing" class="btn btn-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Back to Billing
        </a>
    </div>
</div>

<?php if (!empty($flash)): ?>
<div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
    <?= htmlspecialchars($flash['message']) ?>
</div>
<?php endif; ?>

<div class="row">
    <div class="col-md-8">
        <!-- Status Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h3>Auto Top-Up Status</h3>
            </div>
            <div class="card-body">
                <div class="auto-topup-status <?= $isActive ? 'status-active' : 'status-inactive' ?>">
                    <div class="status-indicator">
                        <?php if ($isActive): ?>
                        <span class="status-icon status-icon-active">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                        </span>
                        <span class="status-text">Auto Top-Up is <strong>Active</strong></span>
                        <?php else: ?>
                        <span class="status-icon status-icon-inactive">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line>
                            </svg>
                        </span>
                        <span class="status-text">Auto Top-Up is <strong>Disabled</strong></span>
                        <?php endif; ?>
                    </div>
                    
                    <form action="/billing/auto-topup/toggle" method="POST" class="status-toggle">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <button type="submit" class="btn <?= $isActive ? 'btn-danger' : 'btn-success' ?>">
                            <?= $isActive ? 'Disable' : 'Enable' ?> Auto Top-Up
                        </button>
                    </form>
                </div>
                
                <?php if ($isOnCooldown): ?>
                <div class="alert alert-info mt-3">
                    <strong>Cooldown Active:</strong> Auto top-up was triggered recently.
                    Next trigger available in <?= $cooldownRemaining ?> hours.
                </div>
                <?php endif; ?>
                
                <?php if ($lastTriggered): ?>
                <div class="text-muted mt-2">
                    <small>Last triggered: <?= htmlspecialchars(date('M d, Y H:i', strtotime($lastTriggered))) ?></small>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Settings Card -->
        <div class="card">
            <div class="card-header">
                <h3>Settings</h3>
            </div>
            <div class="card-body">
                <form action="/billing/auto-topup" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    
                    <div class="form-group">
                        <label for="threshold">Threshold Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" id="threshold" name="threshold" class="form-control" 
                                   value="<?= htmlspecialchars($threshold) ?>" 
                                   min="1" max="1000" step="0.01" required>
                        </div>
                        <small class="form-text text-muted">
                            Trigger auto top-up when your balance falls below this amount
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label for="amount">Top-Up Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" id="amount" name="amount" class="form-control" 
                                   value="<?= htmlspecialchars($amount) ?>" 
                                   min="5" max="5000" step="0.01" required>
                        </div>
                        <small class="form-text text-muted">
                            Amount to request when auto top-up triggers
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label for="cooldown_hours">Cooldown Period</label>
                        <div class="input-group">
                            <input type="number" id="cooldown_hours" name="cooldown_hours" class="form-control" 
                                   value="<?= htmlspecialchars($cooldownHours) ?>" 
                                   min="1" max="168" required>
                            <span class="input-group-text">hours</span>
                        </div>
                        <small class="form-text text-muted">
                            Minimum time between auto top-up triggers (prevents duplicate requests)
                        </small>
                    </div>
                    
                    <div class="form-check mb-4">
                        <input type="checkbox" id="is_active" name="is_active" class="form-check-input" 
                               value="1" <?= $isActive ? 'checked' : '' ?>>
                        <label for="is_active" class="form-check-label">
                            Enable auto top-up
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Save Settings</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Info Card -->
        <div class="card">
            <div class="card-header">
                <h3>How It Works</h3>
            </div>
            <div class="card-body">
                <div class="how-it-works">
                    <div class="step">
                        <span class="step-number">1</span>
                        <span class="step-text">Your balance drops below the threshold</span>
                    </div>
                    <div class="step">
                        <span class="step-number">2</span>
                        <span class="step-text">System creates a deposit request automatically</span>
                    </div>
                    <div class="step">
                        <span class="step-number">3</span>
                        <span class="step-text">You receive a notification</span>
                    </div>
                    <div class="step">
                        <span class="step-number">4</span>
                        <span class="step-text">Complete the payment to restore balance</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Current Balance Card -->
        <div class="card mt-4">
            <div class="card-header">
                <h3>Current Balance</h3>
            </div>
            <div class="card-body text-center">
                <div class="balance-display">
                    <span class="balance-amount">$<?= number_format($user['balance'] ?? 0, 2) ?></span>
                </div>
                <?php if ($isActive && ($user['balance'] ?? 0) < $threshold): ?>
                <div class="alert alert-warning mt-3 mb-0">
                    <small>Your balance is below the threshold. Auto top-up will trigger on next API call.</small>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Help Card -->
        <div class="card mt-4">
            <div class="card-header">
                <h3>Tips</h3>
            </div>
            <div class="card-body">
                <ul class="tips-list">
                    <li>Set the threshold slightly above your typical daily usage</li>
                    <li>Choose a top-up amount that covers several days of usage</li>
                    <li>Use the cooldown to prevent multiple requests in a short time</li>
                    <li>You still need to complete the payment manually</li>
                </ul>
            </div>
        </div>
    </div>
</div>
