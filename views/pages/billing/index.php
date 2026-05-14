<?php
/**
 * Billing Index Page - Dual Billing System
 * Variables: $pageTitle, $currentPage, $balance, $balances, $currentPlan, $availablePlans,
 *            $paymentMethods, $recentTransactions, $totalCredits, $totalDebits
 */

// Helper function to format token numbers
function formatTokens($tokens) {
    if ($tokens >= 1000000) {
        return number_format($tokens / 1000000, 1) . 'M';
    } elseif ($tokens >= 1000) {
        return number_format($tokens / 1000, 1) . 'K';
    }
    return number_format($tokens);
}

// Calculate days until plan expiry
$daysUntilExpiry = null;
if (!empty($balances['plan_tokens_expires_at'])) {
    $expiryTime = strtotime($balances['plan_tokens_expires_at']);
    if ($expiryTime > time()) {
        $daysUntilExpiry = ceil(($expiryTime - time()) / 86400);
    }
}
?>

<div class="page-header">
    <div class="page-header-content">
        <h1>Billing</h1>
        <p>Manage your account balance, plans, and payments</p>
    </div>
    <div class="page-header-actions">
        <a href="/billing/plans" class="btn btn-secondary">
            <i class="icon-list"></i> View Plans
        </a>
        <a href="/billing/add-credits" class="btn btn-primary">
            <i class="icon-plus"></i> Add Credits
        </a>
    </div>
</div>

<!-- Dual Balance Cards -->
<div class="stats-grid stats-grid-4">
    <!-- PAYG Balance Card -->
    <div class="stat-card stat-card-large <?= ($balances['preferred_billing_type'] ?? 'payg') === 'payg' ? 'stat-card-active' : '' ?>">
        <div class="stat-icon stat-icon-primary">
            <i class="icon-dollar"></i>
        </div>
        <div class="stat-content">
            <span class="stat-value">$<?= number_format($balances['payg_balance'] ?? 0, 2) ?></span>
            <span class="stat-label">PAYG Balance</span>
            <?php if (($balances['preferred_billing_type'] ?? 'payg') === 'payg'): ?>
            <span class="badge badge-primary badge-sm">Active</span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Plan Tokens Card -->
    <div class="stat-card stat-card-large <?= ($balances['preferred_billing_type'] ?? 'payg') === 'plan' ? 'stat-card-active' : '' ?>">
        <div class="stat-icon stat-icon-success">
            <i class="icon-cpu"></i>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?= formatTokens($balances['plan_tokens'] ?? 0) ?></span>
            <span class="stat-label">Plan Tokens</span>
            <?php if ($daysUntilExpiry !== null): ?>
            <span class="text-muted text-sm"><?= $daysUntilExpiry ?> days remaining</span>
            <?php endif; ?>
            <?php if (($balances['preferred_billing_type'] ?? 'payg') === 'plan'): ?>
            <span class="badge badge-success badge-sm">Active</span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Total Deposited -->
    <div class="stat-card">
        <div class="stat-icon stat-icon-info">
            <i class="icon-arrow-up"></i>
        </div>
        <div class="stat-content">
            <span class="stat-value">$<?= number_format($totalCredits ?? 0, 2) ?></span>
            <span class="stat-label">Total Deposited</span>
        </div>
    </div>

    <!-- Total Spent -->
    <div class="stat-card">
        <div class="stat-icon stat-icon-warning">
            <i class="icon-arrow-down"></i>
        </div>
        <div class="stat-content">
            <span class="stat-value">$<?= number_format($totalDebits ?? 0, 2) ?></span>
            <span class="stat-label">Total Spent</span>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Billing Type Preference -->
        <div class="card mb-4">
            <div class="card-header">
                <h3>Billing Preference</h3>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">Choose your preferred billing method for API requests. You can also specify billing type per request using the <code>X-Billing-Type</code> header.</p>
                
                <form action="/billing/switch-type" method="POST" class="billing-type-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    
                    <div class="billing-type-options">
                        <label class="billing-type-option <?= ($balances['preferred_billing_type'] ?? 'payg') === 'payg' ? 'active' : '' ?>">
                            <input type="radio" name="billing_type" value="payg" <?= ($balances['preferred_billing_type'] ?? 'payg') === 'payg' ? 'checked' : '' ?>>
                            <div class="option-content">
                                <div class="option-icon">
                                    <i class="icon-dollar"></i>
                                </div>
                                <div class="option-details">
                                    <strong>Pay-As-You-Go (PAYG)</strong>
                                    <span class="text-muted">Deduct from your credit balance</span>
                                </div>
                            </div>
                        </label>
                        
                        <label class="billing-type-option <?= ($balances['preferred_billing_type'] ?? 'payg') === 'plan' ? 'active' : '' ?>">
                            <input type="radio" name="billing_type" value="plan" <?= ($balances['preferred_billing_type'] ?? 'payg') === 'plan' ? 'checked' : '' ?>>
                            <div class="option-content">
                                <div class="option-icon">
                                    <i class="icon-cpu"></i>
                                </div>
                                <div class="option-details">
                                    <strong>Plan Tokens</strong>
                                    <span class="text-muted">Use tokens from your subscription</span>
                                </div>
                            </div>
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary mt-3">Save Preference</button>
                </form>
            </div>
        </div>

        <!-- Current Plan -->
        <?php if ($currentPlan): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h3>Current Plan</h3>
            </div>
            <div class="card-body">
                <div class="plan-info">
                    <div class="plan-header">
                        <h4><?= htmlspecialchars($currentPlan['name'] ?? 'Unknown Plan') ?></h4>
                        <?php if (!empty($currentPlan['is_free'])): ?>
                        <span class="badge badge-info">Free</span>
                        <?php else: ?>
                        <span class="badge badge-primary"><?= number_format($currentPlan['price_monthly'] ?? 0) ?> VND/month</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="plan-stats">
                        <div class="plan-stat">
                            <span class="plan-stat-value"><?= formatTokens($balances['plan_tokens'] ?? 0) ?></span>
                            <span class="plan-stat-label">Tokens Remaining</span>
                        </div>
                        <div class="plan-stat">
                            <span class="plan-stat-value"><?= formatTokens($currentPlan['token_quota'] ?? 0) ?></span>
                            <span class="plan-stat-label">Total Quota</span>
                        </div>
                        <?php if ($daysUntilExpiry !== null): ?>
                        <div class="plan-stat">
                            <span class="plan-stat-value"><?= $daysUntilExpiry ?></span>
                            <span class="plan-stat-label">Days Left</span>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($currentPlan['daily_token_limit'])): ?>
                        <div class="plan-stat">
                            <span class="plan-stat-value"><?= formatTokens($balances['daily_tokens_used'] ?? 0) ?> / <?= formatTokens($currentPlan['daily_token_limit']) ?></span>
                            <span class="plan-stat-label">Daily Usage</span>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="plan-actions">
                        <a href="/billing/plans" class="btn btn-secondary">Upgrade Plan</a>
                        <form action="/billing/cancel-plan" method="POST" style="display: inline;">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                            <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to cancel your plan? Remaining tokens will be forfeited.')">Cancel Plan</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="card mb-4">
            <div class="card-header">
                <h3>No Active Plan</h3>
            </div>
            <div class="card-body">
                <p class="text-muted">You don't have an active subscription plan. Subscribe to a plan to get token quotas at discounted rates.</p>
                <a href="/billing/plans" class="btn btn-primary">View Available Plans</a>
            </div>
        </div>
        <?php endif; ?>

        <!-- Recent Transactions -->
        <div class="card">
            <div class="card-header">
                <h3>Recent Transactions</h3>
                <a href="/billing/history" class="btn btn-sm btn-link">View All</a>
            </div>
            <div class="card-body">
                <?php if (!empty($recentTransactions)): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentTransactions as $transaction): ?>
                            <tr>
                                <td><?= htmlspecialchars(date('M d, Y H:i', strtotime($transaction['created_at'] ?? ''))) ?></td>
                                <td>
                                    <span class="badge badge-<?= ($transaction['type'] ?? '') === 'credit' ? 'success' : 'warning' ?>">
                                        <?= htmlspecialchars(ucfirst($transaction['type'] ?? '')) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($transaction['description'] ?? '') ?></td>
                                <td class="<?= ($transaction['type'] ?? '') === 'credit' ? 'text-success' : 'text-danger' ?>">
                                    <?= ($transaction['type'] ?? '') === 'credit' ? '+' : '-' ?>$<?= number_format($transaction['amount'] ?? 0, 2) ?>
                                </td>
                                <td>
                                    <span class="badge badge-<?= ($transaction['status'] ?? '') === 'completed' ? 'success' : (($transaction['status'] ?? '') === 'pending' ? 'warning' : 'danger') ?>">
                                        <?= htmlspecialchars(ucfirst($transaction['status'] ?? '')) ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-muted">No transactions yet</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Quick Top-up -->
        <div class="card">
            <div class="card-header">
                <h3>Add Credits</h3>
            </div>
            <div class="card-body">
                <form action="/billing/process-payment" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                    <div class="form-group">
                        <label for="amount">Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" id="amount" name="amount" class="form-control" min="5" max="1000" step="0.01" placeholder="10.00" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Quick Select</label>
                        <div class="btn-group-amounts">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setAmount(10)">$10</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setAmount(25)">$25</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setAmount(50)">$50</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setAmount(100)">$100</button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="payment_method">Payment Method</label>
                        <select id="payment_method" name="payment_method" class="form-control" required>
                            <?php foreach ($paymentMethods ?? [] as $method): ?>
                            <option value="<?= htmlspecialchars($method['id'] ?? '') ?>">
                                <?= htmlspecialchars($method['name'] ?? '') ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="icon-credit-card"></i> Pay Now
                    </button>
                </form>
            </div>
        </div>

        <!-- Available Plans Quick View -->
        <?php if (!empty($availablePlans)): ?>
        <div class="card mt-4">
            <div class="card-header">
                <h3>Available Plans</h3>
            </div>
            <div class="card-body">
                <?php foreach (array_slice($availablePlans, 0, 3) as $plan): ?>
                <div class="plan-mini-card <?= ($currentPlan && $currentPlan['id'] == $plan['id']) ? 'current' : '' ?>">
                    <div class="plan-mini-info">
                        <strong><?= htmlspecialchars($plan['name'] ?? '') ?></strong>
                        <span class="text-muted"><?= formatTokens($plan['token_quota'] ?? 0) ?> tokens</span>
                    </div>
                    <div class="plan-mini-price">
                        <?php if (!empty($plan['is_free'])): ?>
                        <span class="text-success">Free</span>
                        <?php else: ?>
                        <span><?= number_format($plan['price_monthly'] ?? 0) ?> VND</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <a href="/billing/plans" class="btn btn-secondary btn-block mt-3">View All Plans</a>
            </div>
        </div>
        <?php endif; ?>

        <!-- Help Card -->
        <div class="card mt-4">
            <div class="card-header">
                <h3>Auto Top-Up</h3>
            </div>
            <div class="card-body">
                <p class="text-muted">Never run out of credits. Set up automatic deposit requests when your balance is low.</p>
                <a href="/billing/auto-topup" class="btn btn-secondary btn-block">Configure Auto Top-Up</a>
            </div>
        </div>

        <!-- Support Card -->
        <div class="card mt-4">
            <div class="card-header">
                <h3>Need Help?</h3>
            </div>
            <div class="card-body">
                <p class="text-muted">If you have any billing questions or issues, please contact our support team.</p>
                <a href="/tickets/create" class="btn btn-secondary btn-block">Contact Support</a>
            </div>
        </div>
    </div>
</div>

<script>
function setAmount(amount) {
    document.getElementById('amount').value = amount;
}

// Billing type toggle
document.querySelectorAll('.billing-type-option input').forEach(input => {
    input.addEventListener('change', function() {
        document.querySelectorAll('.billing-type-option').forEach(opt => opt.classList.remove('active'));
        this.closest('.billing-type-option').classList.add('active');
    });
});
</script>

<style>
.stat-card-active {
    border: 2px solid var(--primary-color, #007bff);
}

.billing-type-options {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.billing-type-option {
    flex: 1;
    min-width: 200px;
    padding: 1rem;
    border: 2px solid var(--border-color, #e9ecef);
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
}

.billing-type-option:hover {
    border-color: var(--primary-color, #007bff);
}

.billing-type-option.active {
    border-color: var(--primary-color, #007bff);
    background: rgba(0, 123, 255, 0.05);
}

.billing-type-option input {
    display: none;
}

.option-content {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.option-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--bg-secondary, #f8f9fa);
    border-radius: 8px;
    font-size: 1.5rem;
}

.option-details {
    display: flex;
    flex-direction: column;
}

.option-details strong {
    font-size: 1rem;
}

.option-details .text-muted {
    font-size: 0.85rem;
}

.plan-info {
    padding: 1rem 0;
}

.plan-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.plan-header h4 {
    margin: 0;
}

.plan-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.plan-stat {
    text-align: center;
    padding: 1rem;
    background: var(--bg-secondary, #f8f9fa);
    border-radius: 8px;
}

.plan-stat-value {
    display: block;
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--text-primary, #212529);
}

.plan-stat-label {
    display: block;
    font-size: 0.85rem;
    color: var(--text-muted, #6c757d);
}

.plan-actions {
    display: flex;
    gap: 1rem;
}

.plan-mini-card {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem;
    border: 1px solid var(--border-color, #e9ecef);
    border-radius: 6px;
    margin-bottom: 0.5rem;
}

.plan-mini-card.current {
    border-color: var(--success-color, #28a745);
    background: rgba(40, 167, 69, 0.05);
}

.plan-mini-info {
    display: flex;
    flex-direction: column;
}

.plan-mini-info strong {
    font-size: 0.9rem;
}

.plan-mini-info .text-muted {
    font-size: 0.8rem;
}

.plan-mini-price {
    font-weight: 600;
}

.btn-group-amounts {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.stats-grid-4 {
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
}
</style>
