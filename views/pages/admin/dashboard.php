<?php
/**
 * Enhanced Admin Dashboard Page
 * Professional admin dashboard with comprehensive statistics and charts
 * 
 * Variables: $pageTitle, $currentPage, $adminName, $totalUsers, $activeUsersToday,
 *            $newUsersThisWeek, $bannedUsers, $totalRevenue, $revenueThisMonth,
 *            $revenueToday, $apiCallsToday, $apiCallsThisWeek, $tokensUsedToday,
 *            $pendingDeposits, $pendingTickets, $topUsers, $modelUsage,
 *            $revenueLast30Days, $signupsLast30Days, $apiCallsLast7Days, $recentActivity
 */

// Helper function for formatting large numbers
function formatNumber($num) {
    if ($num >= 1000000) {
        return number_format($num / 1000000, 1) . 'M';
    } elseif ($num >= 1000) {
        return number_format($num / 1000, 1) . 'K';
    }
    return number_format($num);
}

// Helper function for VND formatting
function formatVND($amount) {
    return number_format($amount, 0, ',', '.') . ' VND';
}
?>

<!-- Dashboard Header -->
<div class="dashboard-header">
    <div class="dashboard-header-content">
        <div class="welcome-section">
            <h1>Welcome back, <?= htmlspecialchars($adminName ?? 'Admin') ?></h1>
            <p class="text-muted">Here's what's happening with your platform today.</p>
        </div>
        <div class="dashboard-actions">
            <div class="date-display">
                <i class="icon-calendar"></i>
                <span><?= date('l, F j, Y') ?></span>
            </div>
            <div class="quick-actions">
                <a href="/admin/deposits?status=pending" class="btn btn-primary btn-sm">
                    <i class="icon-clock"></i> 
                    Pending Deposits
                    <?php if ($pendingDeposits > 0): ?>
                    <span class="btn-badge"><?= number_format($pendingDeposits) ?></span>
                    <?php endif; ?>
                </a>
                <a href="/admin/tickets?status=open" class="btn btn-outline btn-sm">
                    <i class="icon-message-square"></i> 
                    Open Tickets
                    <?php if ($pendingTickets > 0): ?>
                    <span class="btn-badge"><?= number_format($pendingTickets) ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Stats Grid Row 1 -->
<div class="stats-grid stats-grid-4">
    <div class="stat-card">
        <div class="stat-card-header">
            <div class="stat-icon stat-icon-primary">
                <i class="icon-users"></i>
            </div>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?= number_format($totalUsers ?? 0) ?></span>
            <span class="stat-label">Total Users</span>
            <?php if (($newUsersThisWeek ?? 0) > 0): ?>
            <span class="stat-trend trend-up">
                <i class="icon-trending-up"></i>
                +<?= number_format($newUsersThisWeek) ?> this week
            </span>
            <?php endif; ?>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-card-header">
            <div class="stat-icon stat-icon-success">
                <i class="icon-activity"></i>
            </div>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?= number_format($activeUsersToday ?? 0) ?></span>
            <span class="stat-label">Active Today</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-card-header">
            <div class="stat-icon stat-icon-info">
                <i class="icon-dollar-sign"></i>
            </div>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?= formatVND($totalRevenue ?? 0) ?></span>
            <span class="stat-label">Total Revenue</span>
            <span class="stat-sub"><?= formatVND($revenueThisMonth ?? 0) ?> this month</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-card-header">
            <div class="stat-icon stat-icon-purple">
                <i class="icon-zap"></i>
            </div>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?= formatNumber($apiCallsToday ?? 0) ?></span>
            <span class="stat-label">API Calls Today</span>
            <span class="stat-sub"><?= formatNumber($apiCallsThisWeek ?? 0) ?> this week</span>
        </div>
    </div>
</div>

<!-- Stats Grid Row 2 -->
<div class="stats-grid stats-grid-4">
    <div class="stat-card stat-card-clickable" onclick="window.location.href='/admin/deposits?status=pending'">
        <div class="stat-card-header">
            <div class="stat-icon stat-icon-warning">
                <i class="icon-clock"></i>
            </div>
            <a href="/admin/deposits?status=pending" class="stat-card-link">View</a>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?= number_format($pendingDeposits ?? 0) ?></span>
            <span class="stat-label">Pending Deposits</span>
        </div>
    </div>

    <div class="stat-card stat-card-clickable" onclick="window.location.href='/admin/tickets?status=open'">
        <div class="stat-card-header">
            <div class="stat-icon stat-icon-error">
                <i class="icon-message-square"></i>
            </div>
            <a href="/admin/tickets?status=open" class="stat-card-link">View</a>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?= number_format($pendingTickets ?? 0) ?></span>
            <span class="stat-label">Open Tickets</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-card-header">
            <div class="stat-icon stat-icon-info">
                <i class="icon-cpu"></i>
            </div>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?= formatNumber($tokensUsedToday ?? 0) ?></span>
            <span class="stat-label">Tokens Used Today</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-card-header">
            <div class="stat-icon stat-icon-success">
                <i class="icon-user-plus"></i>
            </div>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?= number_format($newUsersThisWeek ?? 0) ?></span>
            <span class="stat-label">New Signups This Week</span>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="charts-grid">
    <div class="card chart-card">
        <div class="card-header">
            <h3>Revenue (Last 30 Days)</h3>
            <div class="chart-legend">
                <span class="legend-item"><span class="legend-dot primary"></span>Revenue</span>
            </div>
        </div>
        <div class="card-body">
            <div id="revenueChart" class="chart-container"></div>
        </div>
    </div>

    <div class="card chart-card">
        <div class="card-header">
            <h3>User Signups (Last 30 Days)</h3>
            <div class="chart-legend">
                <span class="legend-item"><span class="legend-dot success"></span>Signups</span>
            </div>
        </div>
        <div class="card-body">
            <div id="signupsChart" class="chart-container"></div>
        </div>
    </div>
</div>

<!-- Additional Charts Row -->
<div class="charts-grid">
    <div class="card chart-card">
        <div class="card-header">
            <h3>API Usage by Model</h3>
        </div>
        <div class="card-body">
            <div id="modelUsageChart" class="chart-container-donut"></div>
        </div>
    </div>

    <div class="card chart-card">
        <div class="card-header">
            <h3>Daily API Calls (Last 7 Days)</h3>
        </div>
        <div class="card-body">
            <div id="apiCallsChart" class="chart-container"></div>
        </div>
    </div>
</div>

<!-- Tables Section -->
<div class="tables-grid">
    <!-- Top Users Table -->
    <div class="card">
        <div class="card-header">
            <h3>Top Users by Token Usage (This Month)</h3>
            <div class="card-actions">
                <a href="/admin/users" class="btn btn-sm btn-secondary">View All Users</a>
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($topUsers)): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Tokens Used</th>
                            <th>Cost</th>
                            <th>Last Active</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topUsers as $user): ?>
                        <tr>
                            <td>
                                <a href="/admin/users/<?= (int)$user['id'] ?>" class="user-link">
                                    <strong><?= htmlspecialchars($user['name'] ?? 'Unknown') ?></strong>
                                </a>
                            </td>
                            <td class="text-muted"><?= htmlspecialchars($user['email'] ?? '') ?></td>
                            <td><span class="badge badge-info"><?= formatNumber($user['tokens_used'] ?? 0) ?></span></td>
                            <td><?= formatVND($user['cost'] ?? 0) ?></td>
                            <td class="text-muted">
                                <?php if (!empty($user['last_login_at'])): ?>
                                    <?= date('M d, H:i', strtotime($user['last_login_at'])) ?>
                                <?php else: ?>
                                    Never
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p class="text-muted text-center py-4">No usage data available for this month</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="card">
        <div class="card-header">
            <h3>Recent Activity</h3>
            <div class="card-actions">
                <a href="/admin/logs" class="btn btn-sm btn-secondary">View All Logs</a>
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
                        $action = $activity['action'] ?? '';
                        if (strpos($action, 'ban') !== false) {
                            $iconClass = 'icon-ban';
                            $iconColor = 'danger';
                        } elseif (strpos($action, 'approved') !== false) {
                            $iconClass = 'icon-check';
                            $iconColor = 'success';
                        } elseif (strpos($action, 'rejected') !== false) {
                            $iconClass = 'icon-x';
                            $iconColor = 'warning';
                        } elseif (strpos($action, 'ticket') !== false) {
                            $iconClass = 'icon-message-square';
                            $iconColor = 'info';
                        } elseif (strpos($action, 'user') !== false) {
                            $iconClass = 'icon-user';
                            $iconColor = 'primary';
                        } elseif (strpos($action, 'settings') !== false) {
                            $iconClass = 'icon-settings';
                            $iconColor = 'purple';
                        }
                        ?>
                        <span class="activity-icon-circle activity-icon-<?= $iconColor ?>">
                            <i class="<?= $iconClass ?>"></i>
                        </span>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title">
                            <strong><?= htmlspecialchars($activity['admin_name'] ?? 'System') ?></strong>
                            <span class="activity-action"><?= htmlspecialchars(str_replace('_', ' ', $action)) ?></span>
                            <?php if (!empty($activity['target_type'])): ?>
                            <span class="activity-target">on <?= htmlspecialchars($activity['target_type']) ?> #<?= (int)$activity['target_id'] ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="activity-meta">
                            <span class="activity-time">
                                <i class="icon-clock"></i>
                                <?= htmlspecialchars(date('M d, Y H:i', strtotime($activity['created_at']))) ?>
                            </span>
                            <?php if (!empty($activity['ip_address'])): ?>
                            <span class="activity-ip">
                                <i class="icon-globe"></i>
                                <?= htmlspecialchars($activity['ip_address']) ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p class="text-muted text-center py-4">No recent activity</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Revenue Chart Data
    var revenueData = <?= json_encode(array_map(function($item) {
        return ['label' => $item['label'], 'value' => $item['value']];
    }, $revenueLast30Days ?? [])) ?>;
    
    // Signups Chart Data
    var signupsData = <?= json_encode(array_map(function($item) {
        return ['label' => $item['label'], 'value' => $item['value']];
    }, $signupsLast30Days ?? [])) ?>;
    
    // API Calls Chart Data
    var apiCallsData = <?= json_encode(array_map(function($item) {
        return ['label' => $item['label'], 'value' => $item['value']];
    }, $apiCallsLast7Days ?? [])) ?>;
    
    // Model Usage Data
    var modelUsageData = <?= json_encode(array_map(function($item) {
        return ['label' => $item['model'] ?? 'Unknown', 'value' => (int)($item['call_count'] ?? 0)];
    }, $modelUsage ?? [])) ?>;

    // Render charts using ChartManager
    if (typeof ChartManager !== 'undefined') {
        // Revenue line chart
        ChartManager.renderLineChart('revenueChart', revenueData, {
            height: 250,
            color: 'var(--color-primary)',
            filled: true,
            showDots: false,
            showLabels: false,
            formatValue: function(v) { return v.toLocaleString('vi-VN') + ' VND'; }
        });
        
        // Signups bar chart
        ChartManager.renderBarChart('signupsChart', signupsData, {
            height: 250,
            color: 'var(--color-success)',
            showLabels: false,
            showValues: false
        });
        
        // API Calls bar chart
        ChartManager.renderBarChart('apiCallsChart', apiCallsData, {
            height: 250,
            color: 'var(--color-info)',
            showLabels: true,
            showValues: true
        });
        
        // Model usage donut chart
        if (modelUsageData.length > 0) {
            ChartManager.renderDonutChart('modelUsageChart', modelUsageData, {
                size: 200,
                strokeWidth: 30,
                showLegend: true,
                centerText: modelUsageData.reduce(function(sum, item) { return sum + item.value; }, 0).toLocaleString()
            });
        } else {
            document.getElementById('modelUsageChart').innerHTML = '<div class="chart-empty">No model usage data</div>';
        }
    }
});
</script>
