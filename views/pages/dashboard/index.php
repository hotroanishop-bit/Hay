<?php
/**
 * Dashboard Index Page
 * Variables: $pageTitle, $currentPage, $user, $balance, $totalKeys, $activeKeys,
 *            $usageStats, $dailyStats, $recentTransactions, $recentUsage
 */
?>

<div class="page-header">
    <h1>Dashboard</h1>
    <p>Welcome back, <?= htmlspecialchars($user['name'] ?? 'User') ?></p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon stat-icon-primary">
            <i class="icon-key"></i>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?= number_format($totalKeys ?? 0) ?></span>
            <span class="stat-label">Total Keys</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon stat-icon-success">
            <i class="icon-check"></i>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?= number_format($activeKeys ?? 0) ?></span>
            <span class="stat-label">Active Keys</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon stat-icon-warning">
            <i class="icon-dollar"></i>
        </div>
        <div class="stat-content">
            <span class="stat-value">$<?= number_format($balance ?? 0, 2) ?></span>
            <span class="stat-label">Balance</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon stat-icon-info">
            <i class="icon-chart"></i>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?= number_format($usageStats['total_requests'] ?? 0) ?></span>
            <span class="stat-label">Total Requests</span>
        </div>
    </div>
</div>

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
                <a href="/analytics" class="btn btn-secondary">
                    <i class="icon-chart"></i> View Analytics
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
