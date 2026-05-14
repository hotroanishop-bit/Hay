<?php
/**
 * Analytics Index Page - Modern Dashboard
 * Variables: $pageTitle, $currentPage, $overallStats, $dailyStats, $endpointStats,
 *            $hourlyStats, $apiKeys, $keyStats, $startDate, $endDate, $modelStats
 */

// Helper function for formatting large numbers
function formatAnalyticsNumber($num) {
    if ($num >= 1000000) {
        return number_format($num / 1000000, 1) . 'M';
    } elseif ($num >= 1000) {
        return number_format($num / 1000, 1) . 'K';
    }
    return number_format($num);
}
?>

<div class="page-header page-header-flex">
    <div class="page-header-content">
        <h1 class="page-title">Analytics</h1>
        <p class="page-subtitle">Monitor your API usage and performance</p>
    </div>
    <div class="page-header-actions">
        <a href="/analytics/export?type=daily&start_date=<?= htmlspecialchars($startDate ?? '') ?>&end_date=<?= htmlspecialchars($endDate ?? '') ?>" class="btn btn-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" x2="12" y1="15" y2="3"></line></svg>
            Export CSV
        </a>
    </div>
</div>

<!-- Date Range Filter -->
<div class="card mb-6">
    <div class="card-body py-4">
        <form action="/analytics" method="GET" class="filters-form">
            <div class="filter-presets">
                <button type="button" class="filter-preset <?= empty($startDate) ? 'active' : '' ?>" onclick="setDateRange(7)">Last 7 days</button>
                <button type="button" class="filter-preset" onclick="setDateRange(14)">Last 14 days</button>
                <button type="button" class="filter-preset" onclick="setDateRange(30)">Last 30 days</button>
                <button type="button" class="filter-preset" onclick="setDateRange(90)">Last 90 days</button>
            </div>
            <div class="filter-custom">
                <div class="filter-group">
                    <label for="start_date" class="filter-label">From</label>
                    <input type="date" id="start_date" name="start_date" class="form-input" value="<?= htmlspecialchars($startDate ?? '') ?>">
                </div>
                <div class="filter-group">
                    <label for="end_date" class="filter-label">To</label>
                    <input type="date" id="end_date" name="end_date" class="form-input" value="<?= htmlspecialchars($endDate ?? '') ?>">
                </div>
                <button type="submit" class="btn btn-primary">Apply</button>
            </div>
        </form>
    </div>
</div>

<!-- Stats Grid -->
<div class="stats-grid mb-6">
    <div class="stat-card">
        <div class="stat-icon-wrapper stat-icon-primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m22 2-7 20-4-9-9-4Z"></path><path d="M22 2 11 13"></path></svg>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?= formatAnalyticsNumber($overallStats['total_requests'] ?? 0) ?></span>
            <span class="stat-label">Total Requests</span>
        </div>
        <div class="stat-trend stat-trend-up">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 7-7 7 7"></path><path d="M12 19V5"></path></svg>
            <span>Active</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon-wrapper stat-icon-success">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?= formatAnalyticsNumber($overallStats['successful_requests'] ?? 0) ?></span>
            <span class="stat-label">Successful Requests</span>
        </div>
        <?php 
        $successRate = ($overallStats['total_requests'] ?? 0) > 0 
            ? round(($overallStats['successful_requests'] ?? 0) / $overallStats['total_requests'] * 100, 1) 
            : 0;
        ?>
        <div class="stat-trend stat-trend-up">
            <span><?= $successRate ?>% success</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon-wrapper stat-icon-info">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M2 12h20"></path></svg>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?= formatAnalyticsNumber($overallStats['total_tokens'] ?? 0) ?></span>
            <span class="stat-label">Tokens Used</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon-wrapper stat-icon-warning">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"></path><path d="M12 18V6"></path></svg>
        </div>
        <div class="stat-content">
            <span class="stat-value">$<?= number_format($overallStats['total_cost'] ?? 0, 2) ?></span>
            <span class="stat-label">Total Cost</span>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="grid grid-2 gap-6 mb-6">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daily Usage</h3>
            <div class="card-actions">
                <button type="button" class="btn btn-ghost btn-sm chart-toggle active" data-chart="requests">Requests</button>
                <button type="button" class="btn btn-ghost btn-sm chart-toggle" data-chart="tokens">Tokens</button>
            </div>
        </div>
        <div class="card-body">
            <div class="chart-container" id="daily-usage-chart" data-stats='<?= htmlspecialchars(json_encode($dailyStats ?? [])) ?>'>
                <div class="chart-skeleton">
                    <div class="skeleton-bar" style="height: 60%"></div>
                    <div class="skeleton-bar" style="height: 80%"></div>
                    <div class="skeleton-bar" style="height: 45%"></div>
                    <div class="skeleton-bar" style="height: 90%"></div>
                    <div class="skeleton-bar" style="height: 70%"></div>
                    <div class="skeleton-bar" style="height: 55%"></div>
                    <div class="skeleton-bar" style="height: 75%"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Hourly Distribution</h3>
        </div>
        <div class="card-body">
            <div class="chart-container" id="hourly-chart" data-stats='<?= htmlspecialchars(json_encode($hourlyStats ?? [])) ?>'>
                <div class="chart-skeleton chart-skeleton-line">
                    <svg viewBox="0 0 100 40" preserveAspectRatio="none">
                        <path d="M0,30 Q10,25 20,28 T40,20 T60,25 T80,15 T100,22" fill="none" stroke="var(--border-color)" stroke-width="2"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tables Row -->
<div class="grid grid-2 gap-6 mb-6">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Top Endpoints</h3>
        </div>
        <div class="card-body p-0">
            <?php if (!empty($endpointStats)): ?>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Endpoint</th>
                            <th class="text-right">Requests</th>
                            <th class="text-right">Tokens</th>
                            <th class="text-right">Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($endpointStats, 0, 5) as $endpoint): ?>
                        <tr>
                            <td><code class="code-inline"><?= htmlspecialchars($endpoint['endpoint'] ?? '') ?></code></td>
                            <td class="text-right"><?= formatAnalyticsNumber($endpoint['requests'] ?? 0) ?></td>
                            <td class="text-right"><?= formatAnalyticsNumber($endpoint['total_tokens'] ?? 0) ?></td>
                            <td class="text-right">$<?= number_format($endpoint['total_cost'] ?? 0, 4) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="empty-state-sm">
                <p class="text-muted">No endpoint data available</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Usage by API Key</h3>
        </div>
        <div class="card-body p-0">
            <?php if (!empty($keyStats)): ?>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Key Name</th>
                            <th class="text-right">Requests</th>
                            <th class="text-right">Tokens</th>
                            <th class="text-right">Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($keyStats, 0, 5) as $keyId => $stats): ?>
                        <tr>
                            <td><?= htmlspecialchars($stats['name'] ?? 'Unknown') ?></td>
                            <td class="text-right"><?= formatAnalyticsNumber($stats['total_requests'] ?? 0) ?></td>
                            <td class="text-right"><?= formatAnalyticsNumber($stats['total_tokens'] ?? 0) ?></td>
                            <td class="text-right">$<?= number_format($stats['total_cost'] ?? 0, 4) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="empty-state-sm">
                <p class="text-muted">No API key usage data available</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Model Usage Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">API Usage by Model</h3>
    </div>
    <div class="card-body p-0">
        <?php if (!empty($modelStats)): ?>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Model</th>
                        <th class="text-right">Requests</th>
                        <th class="text-right">Input Tokens</th>
                        <th class="text-right">Output Tokens</th>
                        <th class="text-right">Total Cost</th>
                        <th class="text-right">Avg Response</th>
                        <th>Distribution</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $totalModelRequests = array_sum(array_column($modelStats, 'total_requests'));
                    $totalModelCost = array_sum(array_column($modelStats, 'total_cost'));
                    foreach ($modelStats as $model): 
                    $costPercent = $totalModelCost > 0 ? ($model['total_cost'] / $totalModelCost) * 100 : 0;
                    ?>
                    <tr>
                        <td><code class="code-inline"><?= htmlspecialchars($model['model'] ?? 'unknown') ?></code></td>
                        <td class="text-right"><?= formatAnalyticsNumber($model['total_requests'] ?? 0) ?></td>
                        <td class="text-right"><?= formatAnalyticsNumber($model['total_input_tokens'] ?? 0) ?></td>
                        <td class="text-right"><?= formatAnalyticsNumber($model['total_output_tokens'] ?? 0) ?></td>
                        <td class="text-right">$<?= number_format($model['total_cost'] ?? 0, 4) ?></td>
                        <td class="text-right"><?= number_format($model['avg_response_time_ms'] ?? 0, 0) ?>ms</td>
                        <td>
                            <div class="mini-progress">
                                <div class="mini-progress-bar" style="width: <?= $costPercent ?>%"></div>
                            </div>
                            <span class="mini-progress-label"><?= number_format($costPercent, 1) ?>%</span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="table-footer-row">
                        <td><strong>Total</strong></td>
                        <td class="text-right"><strong><?= formatAnalyticsNumber($totalModelRequests) ?></strong></td>
                        <td class="text-right"><strong><?= formatAnalyticsNumber(array_sum(array_column($modelStats, 'total_input_tokens'))) ?></strong></td>
                        <td class="text-right"><strong><?= formatAnalyticsNumber(array_sum(array_column($modelStats, 'total_output_tokens'))) ?></strong></td>
                        <td class="text-right"><strong>$<?= number_format($totalModelCost, 4) ?></strong></td>
                        <td class="text-right">-</td>
                        <td>-</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state-sm">
            <p class="text-muted">No model usage data available</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: var(--space-5);
}

.stat-card {
    background: var(--surface-primary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: var(--space-5);
    display: flex;
    flex-direction: column;
    gap: var(--space-3);
}

.stat-icon-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    border-radius: var(--radius-lg);
}

.stat-icon-primary { background: var(--color-primary-light); color: var(--color-primary); }
.stat-icon-success { background: var(--color-success-light); color: var(--color-success); }
.stat-icon-info { background: var(--color-info-light); color: var(--color-info); }
.stat-icon-warning { background: var(--color-warning-light); color: var(--color-warning); }

.stat-content {
    display: flex;
    flex-direction: column;
}

.stat-value {
    font-size: var(--font-size-2xl);
    font-weight: var(--font-weight-bold);
    color: var(--text-primary);
    line-height: 1.2;
}

.stat-label {
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
}

.stat-trend {
    display: flex;
    align-items: center;
    gap: var(--space-1);
    font-size: var(--font-size-xs);
    font-weight: var(--font-weight-medium);
}

.stat-trend-up { color: var(--color-success); }
.stat-trend-down { color: var(--color-error); }

/* Filters Form */
.filters-form {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: var(--space-4);
    flex-wrap: wrap;
}

.filter-presets {
    display: flex;
    gap: var(--space-2);
}

.filter-preset {
    padding: var(--space-2) var(--space-3);
    background: transparent;
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
    cursor: pointer;
    transition: all var(--transition-fast);
}

.filter-preset:hover {
    background: var(--bg-tertiary);
    color: var(--text-primary);
}

.filter-preset.active {
    background: var(--color-primary);
    border-color: var(--color-primary);
    color: var(--color-white);
}

.filter-custom {
    display: flex;
    align-items: flex-end;
    gap: var(--space-3);
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: var(--space-1);
}

.filter-label {
    font-size: var(--font-size-xs);
    color: var(--text-muted);
}

.filter-group .form-input {
    width: 140px;
}

/* Chart Container */
.chart-container {
    height: 240px;
    position: relative;
}

.chart-skeleton {
    display: flex;
    align-items: flex-end;
    justify-content: space-around;
    height: 100%;
    padding: var(--space-4);
    gap: var(--space-2);
}

.skeleton-bar {
    flex: 1;
    background: linear-gradient(90deg, var(--bg-tertiary) 25%, var(--bg-secondary) 50%, var(--bg-tertiary) 75%);
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
    border-radius: var(--radius-sm) var(--radius-sm) 0 0;
}

.chart-skeleton-line {
    display: flex;
    align-items: center;
    justify-content: center;
}

.chart-skeleton-line svg {
    width: 100%;
    height: 60%;
}

@keyframes shimmer {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

/* Card Actions */
.card-actions {
    display: flex;
    gap: var(--space-1);
}

.chart-toggle {
    font-size: var(--font-size-xs);
}

.chart-toggle.active {
    background: var(--bg-tertiary);
    color: var(--text-primary);
}

/* Code Inline */
.code-inline {
    font-family: var(--font-mono);
    font-size: var(--font-size-xs);
    background: var(--bg-tertiary);
    padding: var(--space-1) var(--space-2);
    border-radius: var(--radius-sm);
    color: var(--text-primary);
}

/* Mini Progress */
.mini-progress {
    width: 60px;
    height: 6px;
    background: var(--bg-tertiary);
    border-radius: var(--radius-full);
    overflow: hidden;
    display: inline-block;
    vertical-align: middle;
}

.mini-progress-bar {
    height: 100%;
    background: var(--color-primary);
    border-radius: var(--radius-full);
}

.mini-progress-label {
    font-size: var(--font-size-xs);
    color: var(--text-muted);
    margin-left: var(--space-2);
}

/* Empty State Small */
.empty-state-sm {
    padding: var(--space-8);
    text-align: center;
}

/* Table Footer */
.table-footer-row {
    background: var(--bg-secondary);
}

.table-footer-row td {
    border-bottom: none;
}

/* Responsive */
@media (max-width: 1279px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 767px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .grid-2 {
        grid-template-columns: 1fr;
    }
    
    .filters-form {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filter-presets {
        flex-wrap: wrap;
    }
    
    .filter-custom {
        flex-wrap: wrap;
    }
    
    .filter-group .form-input {
        width: 100%;
    }
}
</style>

<script>
function setDateRange(days) {
    const endDate = new Date();
    const startDate = new Date();
    startDate.setDate(startDate.getDate() - days);
    
    document.getElementById('start_date').value = startDate.toISOString().split('T')[0];
    document.getElementById('end_date').value = endDate.toISOString().split('T')[0];
    
    // Update active state
    document.querySelectorAll('.filter-preset').forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
}

// Chart toggle functionality
document.querySelectorAll('.chart-toggle').forEach(btn => {
    btn.addEventListener('click', function() {
        this.closest('.card-actions').querySelectorAll('.chart-toggle').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        // Chart switching logic would go here if ChartManager is available
    });
});
</script>
