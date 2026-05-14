<?php
/**
 * Admin Dashboard Page
 * Variables: $pageTitle, $currentPage, $totalUsers, $activeUsersToday, $totalRevenue,
 *            $apiCallsToday, $pendingDeposits, $pendingTickets, $revenueLast7Days, 
 *            $signupsLast7Days, $recentActivity
 */
?>

<div class="page-header">
    <div class="page-header-content">
        <h1>Admin Dashboard</h1>
        <p>Overview of your platform</p>
    </div>
</div>

<div class="stats-grid stats-grid-6">
    <div class="stat-card">
        <div class="stat-icon stat-icon-primary">
            <i class="icon-users"></i>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?= number_format($totalUsers ?? 0) ?></span>
            <span class="stat-label">Total Users</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon stat-icon-success">
            <i class="icon-activity"></i>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?= number_format($activeUsersToday ?? 0) ?></span>
            <span class="stat-label">Active Today</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon stat-icon-info">
            <i class="icon-dollar-sign"></i>
        </div>
        <div class="stat-content">
            <span class="stat-value">$<?= number_format($totalRevenue ?? 0, 2) ?></span>
            <span class="stat-label">Total Revenue</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon stat-icon-purple">
            <i class="icon-zap"></i>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?= number_format($apiCallsToday ?? 0) ?></span>
            <span class="stat-label">API Calls Today</span>
        </div>
    </div>

    <div class="stat-card stat-card-clickable" onclick="window.location.href='/admin/deposits?status=pending'">
        <div class="stat-icon stat-icon-warning">
            <i class="icon-clock"></i>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?= number_format($pendingDeposits ?? 0) ?></span>
            <span class="stat-label">Pending Deposits</span>
        </div>
    </div>

    <div class="stat-card stat-card-clickable" onclick="window.location.href='/admin/tickets?status=open'">
        <div class="stat-icon stat-icon-danger">
            <i class="icon-message-square"></i>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?= number_format($pendingTickets ?? 0) ?></span>
            <span class="stat-label">Open Tickets</span>
        </div>
    </div>
</div>

<div class="dashboard-charts">
    <div class="card chart-card">
        <div class="card-header">
            <h3>Revenue (Last 7 Days)</h3>
        </div>
        <div class="card-body">
            <div class="chart-container">
                <?php 
                $maxRevenue = max(array_column($revenueLast7Days ?? [], 'amount')) ?: 1;
                ?>
                <div class="bar-chart">
                    <?php foreach ($revenueLast7Days ?? [] as $day): ?>
                    <?php $height = ($day['amount'] / $maxRevenue) * 100; ?>
                    <div class="bar-wrapper">
                        <div class="bar-value">$<?= number_format($day['amount'], 0) ?></div>
                        <div class="bar" style="height: <?= max($height, 5) ?>%;" title="$<?= number_format($day['amount'], 2) ?>"></div>
                        <div class="bar-label"><?= htmlspecialchars($day['label']) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card chart-card">
        <div class="card-header">
            <h3>Signups (Last 7 Days)</h3>
        </div>
        <div class="card-body">
            <div class="chart-container">
                <?php 
                $maxSignups = max(array_column($signupsLast7Days ?? [], 'count')) ?: 1;
                ?>
                <div class="bar-chart">
                    <?php foreach ($signupsLast7Days ?? [] as $day): ?>
                    <?php $height = ($day['count'] / $maxSignups) * 100; ?>
                    <div class="bar-wrapper">
                        <div class="bar-value"><?= number_format($day['count']) ?></div>
                        <div class="bar bar-secondary" style="height: <?= max($height, 5) ?>%;" title="<?= number_format($day['count']) ?> signups"></div>
                        <div class="bar-label"><?= htmlspecialchars($day['label']) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Recent Activity</h3>
        <div class="card-actions">
            <a href="/admin/logs" class="btn btn-sm btn-secondary">View All</a>
        </div>
    </div>
    <div class="card-body">
        <?php if (!empty($recentActivity)): ?>
        <div class="activity-feed">
            <?php foreach ($recentActivity as $activity): ?>
            <div class="activity-item">
                <div class="activity-icon">
                    <?php
                    $iconClass = 'icon-activity';
                    $iconColor = 'primary';
                    if (strpos($activity['action'], 'ban') !== false) {
                        $iconClass = 'icon-ban';
                        $iconColor = 'danger';
                    } elseif (strpos($activity['action'], 'approved') !== false) {
                        $iconClass = 'icon-check';
                        $iconColor = 'success';
                    } elseif (strpos($activity['action'], 'rejected') !== false) {
                        $iconClass = 'icon-x';
                        $iconColor = 'warning';
                    } elseif (strpos($activity['action'], 'ticket') !== false) {
                        $iconClass = 'icon-message-square';
                        $iconColor = 'info';
                    } elseif (strpos($activity['action'], 'user') !== false) {
                        $iconClass = 'icon-user';
                        $iconColor = 'primary';
                    }
                    ?>
                    <span class="activity-icon-circle activity-icon-<?= $iconColor ?>">
                        <i class="<?= $iconClass ?>"></i>
                    </span>
                </div>
                <div class="activity-content">
                    <div class="activity-title">
                        <strong><?= htmlspecialchars($activity['admin_name'] ?? 'System') ?></strong>
                        performed <code><?= htmlspecialchars($activity['action']) ?></code>
                        <?php if (!empty($activity['target_type'])): ?>
                        on <?= htmlspecialchars($activity['target_type']) ?> #<?= (int)$activity['target_id'] ?>
                        <?php endif; ?>
                    </div>
                    <div class="activity-meta">
                        <span class="activity-time"><?= htmlspecialchars(date('M d, Y H:i', strtotime($activity['created_at']))) ?></span>
                        <span class="activity-ip"><?= htmlspecialchars($activity['ip_address'] ?? '') ?></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p class="text-muted">No recent activity</p>
        <?php endif; ?>
    </div>
</div>
