<?php
/**
 * Dashboard Index Page - Dual Billing Display
 * Variables: $pageTitle, $currentPage, $user, $balance, $balances, $currentPlan,
 *            $totalKeys, $activeKeys, $usageStats, $dailyStats, $todayUsage,
 *            $recentTransactions, $recentUsage
 */

// Helper function to format token numbers
function formatDashboardTokens($tokens) {
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
    <h1>Dashboard</h1>
    <p>Welcome back, <?= htmlspecialchars($user['name'] ?? 'User') ?></p>
</div>

<!-- Billing Status Cards -->
<div class="stats-grid">
    <!-- PAYG Balance Card -->
    <div class="stat-card <?= ($balances['preferred_billing_type'] ?? 'payg') === 'payg' ? 'stat-card-highlight' : '' ?>">
        <div class="stat-icon stat-icon-primary">
            <i class="icon-dollar"></i>
        </div>
        <div class="stat-content">
            <span class="stat-value">$<?= number_format($balances['payg_balance'] ?? 0, 2) ?></span>
            <span class="stat-label">PAYG Balance</span>
            <?php if (($balances['preferred_billing_type'] ?? 'payg') === 'payg'): ?>
            <span class="badge badge-primary badge-sm mt-1">Active</span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Plan Tokens Card -->
    <div class="stat-card <?= ($balances['preferred_billing_type'] ?? 'payg') === 'plan' ? 'stat-card-highlight' : '' ?>">
        <div class="stat-icon stat-icon-success">
            <i class="icon-cpu"></i>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?= formatDashboardTokens($balances['plan_tokens'] ?? 0) ?></span>
            <span class="stat-label">Plan Tokens</span>
            <?php if ($daysUntilExpiry !== null): ?>
            <span class="text-muted text-xs"><?= $daysUntilExpiry ?> days left</span>
            <?php endif; ?>
            <?php if (($balances['preferred_billing_type'] ?? 'payg') === 'plan'): ?>
            <span class="badge badge-success badge-sm mt-1">Active</span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Active Keys Card -->
    <div class="stat-card">
        <div class="stat-icon stat-icon-info">
            <i class="icon-key"></i>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?= number_format($activeKeys ?? 0) ?></span>
            <span class="stat-label">Active Keys</span>
            <span class="text-muted text-xs"><?= number_format($totalKeys ?? 0) ?> total</span>
        </div>
    </div>

    <!-- Total Requests Card -->
    <div class="stat-card">
        <div class="stat-icon stat-icon-warning">
            <i class="icon-chart"></i>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?= number_format($usageStats['total_requests'] ?? 0) ?></span>
            <span class="stat-label">Total Requests</span>
        </div>
    </div>
</div>

<!-- Today's Usage Section -->
<?php if (!empty($todayUsage)): ?>
<div class="card mb-4">
    <div class="card-header">
        <h3>Today's Usage</h3>
        <span class="text-muted"><?= date('F j, Y') ?></span>
    </div>
    <div class="card-body">
        <div class="today-usage-grid">
            <div class="today-usage-item">
                <span class="usage-value"><?= number_format($todayUsage['requests'] ?? 0) ?></span>
                <span class="usage-label">Requests</span>
            </div>
            <div class="today-usage-item">
                <span class="usage-value"><?= formatDashboardTokens($todayUsage['tokens'] ?? 0) ?></span>
                <span class="usage-label">Tokens Used</span>
            </div>
            <div class="today-usage-item">
                <span class="usage-value">$<?= number_format($todayUsage['cost'] ?? 0, 4) ?></span>
                <span class="usage-label">Cost</span>
            </div>
            <?php if (!empty($currentPlan) && !empty($currentPlan['daily_token_limit'])): ?>
            <div class="today-usage-item">
                <span class="usage-value"><?= formatDashboardTokens($balances['daily_tokens_used'] ?? 0) ?> / <?= formatDashboardTokens($currentPlan['daily_token_limit']) ?></span>
                <span class="usage-label">Daily Limit</span>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Current Plan Info -->
<?php if ($currentPlan): ?>
<div class="card mb-4">
    <div class="card-header">
        <h3>Current Plan: <?= htmlspecialchars($currentPlan['name'] ?? 'Unknown') ?></h3>
        <a href="/billing/plans" class="btn btn-sm btn-link">Manage Plan</a>
    </div>
    <div class="card-body">
        <div class="plan-progress-bar">
            <?php 
            $totalQuota = (int)($currentPlan['token_quota'] ?? 1);
            $remaining = (int)($balances['plan_tokens'] ?? 0);
            $usedPercent = $totalQuota > 0 ? min(100, max(0, (($totalQuota - $remaining) / $totalQuota) * 100)) : 0;
            ?>
            <div class="progress">
                <div class="progress-bar" style="width: <?= $usedPercent ?>%"></div>
            </div>
            <div class="progress-info">
                <span><?= formatDashboardTokens($remaining) ?> of <?= formatDashboardTokens($totalQuota) ?> tokens remaining</span>
                <span><?= number_format($usedPercent, 1) ?>% used</span>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="dashboard-grid">
    <div class="card">
        <div class="card-header">
            <h3>Quick Actions</h3>
        </div>
        <div class="card-body">
            <div class="quick-actions">
                <a href="/keys/create" class="btn btn-primary">
                    <i class="icon-plus"></i> Create API Key
                </a>
                <a href="/billing/add-credits" class="btn btn-secondary">
                    <i class="icon-dollar"></i> Add Credits
                </a>
                <a href="/billing" class="btn btn-secondary">
                    <i class="icon-credit-card"></i> Billing
                </a>
                <a href="/analytics" class="btn btn-secondary">
                    <i class="icon-chart"></i> Analytics
                </a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Usage Overview (Last 7 Days)</h3>
        </div>
        <div class="card-body">
            <div class="chart-placeholder" id="usage-chart" data-stats='<?= htmlspecialchars(json_encode($dailyStats ?? [])) ?>'>
                <p class="text-muted">Loading chart...</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3>Recent Transactions</h3>
                <a href="/billing/history" class="btn btn-sm btn-link">View All</a>
            </div>
            <div class="card-body">
                <?php if (!empty($recentTransactions)): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentTransactions as $transaction): ?>
                        <tr>
                            <td><?= htmlspecialchars(date('M d', strtotime($transaction['created_at'] ?? ''))) ?></td>
                            <td><?= htmlspecialchars($transaction['description'] ?? '') ?></td>
                            <td class="<?= ($transaction['type'] ?? '') === 'credit' ? 'text-success' : 'text-danger' ?>">
                                <?= ($transaction['type'] ?? '') === 'credit' ? '+' : '-' ?>$<?= number_format($transaction['amount'] ?? 0, 2) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p class="text-muted">No recent transactions</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3>Recent API Activity</h3>
                <a href="/analytics" class="btn btn-sm btn-link">View All</a>
            </div>
            <div class="card-body">
                <?php if (!empty($recentUsage)): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Endpoint</th>
                            <th>Tokens</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($recentUsage, 0, 5) as $usage): ?>
                        <tr>
                            <td><?= htmlspecialchars(date('H:i', strtotime($usage['created_at'] ?? ''))) ?></td>
                            <td><code><?= htmlspecialchars($usage['endpoint'] ?? 'N/A') ?></code></td>
                            <td><?= number_format($usage['tokens_used'] ?? 0) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p class="text-muted">No recent activity</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.stat-card-highlight {
    border: 2px solid var(--primary-color, #007bff);
    box-shadow: 0 2px 8px rgba(0, 123, 255, 0.15);
}

.today-usage-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 1.5rem;
}

.today-usage-item {
    text-align: center;
    padding: 1rem;
    background: var(--bg-secondary, #f8f9fa);
    border-radius: 8px;
}

.usage-value {
    display: block;
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--text-primary, #212529);
}

.usage-label {
    display: block;
    font-size: 0.85rem;
    color: var(--text-muted, #6c757d);
    margin-top: 0.25rem;
}

.plan-progress-bar {
    padding: 0.5rem 0;
}

.progress {
    height: 12px;
    background: var(--bg-secondary, #e9ecef);
    border-radius: 6px;
    overflow: hidden;
    margin-bottom: 0.75rem;
}

.progress-bar {
    height: 100%;
    background: linear-gradient(90deg, var(--primary-color, #007bff), var(--info-color, #17a2b8));
    border-radius: 6px;
    transition: width 0.3s ease;
}

.progress-info {
    display: flex;
    justify-content: space-between;
    font-size: 0.85rem;
    color: var(--text-muted, #6c757d);
}

.badge-sm {
    font-size: 0.7rem;
    padding: 2px 6px;
}

.text-xs {
    font-size: 0.75rem;
}

.mt-1 {
    margin-top: 0.25rem;
}
</style>
