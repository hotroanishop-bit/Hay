<?php
/**
 * Analytics Dashboard with Charts
 * Variables: $pageTitle, $currentPage, $summaryStats, $dailyStats, $modelStats,
 *            $hourlyStats, $endpointStats, $apiKeys, $keyStats, $startDate, $endDate, $days
 */

function formatNum($num) {
    if ($num >= 1000000) return number_format($num / 1000000, 1) . 'M';
    if ($num >= 1000) return number_format($num / 1000, 1) . 'K';
    return number_format($num);
}
?>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="page-header page-header-flex">
    <div class="page-header-content">
        <h1 class="page-title">Analytics Dashboard</h1>
        <p class="page-subtitle">Monitor your API usage, costs, and performance</p>
    </div>
    <div class="page-header-actions">
        <button type="button" class="btn btn-secondary" onclick="refreshCharts()">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 21h5v-5"/></svg>
            Refresh
        </button>
        <div class="dropdown">
            <button type="button" class="btn btn-secondary dropdown-toggle">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                Export
            </button>
            <div class="dropdown-menu">
                <a href="/analytics/export?type=daily&days=<?= $days ?>" class="dropdown-item">Daily Usage CSV</a>
                <a href="/analytics/export?type=models&days=<?= $days ?>" class="dropdown-item">Model Usage CSV</a>
                <a href="/analytics/export?type=hourly&days=<?= $days ?>" class="dropdown-item">Hourly CSV</a>
                <a href="/analytics/export?type=logs" class="dropdown-item">Full Logs CSV</a>
            </div>
        </div>
    </div>
</div>

<!-- Date Range Selector -->
<div class="card mb-6">
    <div class="card-body py-4">
        <div class="filters-form">
            <div class="filter-presets">
                <button type="button" class="filter-preset <?= $days == 7 ? 'active' : '' ?>" onclick="changeDays(7)">7 days</button>
                <button type="button" class="filter-preset <?= $days == 30 ? 'active' : '' ?>" onclick="changeDays(30)">30 days</button>
                <button type="button" class="filter-preset <?= $days == 90 ? 'active' : '' ?>" onclick="changeDays(90)">90 days</button>
            </div>
            <div class="filter-info">
                <span class="text-muted">Showing data from <?= date('M j, Y', strtotime($startDate)) ?> to <?= date('M j, Y', strtotime($endDate)) ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Stats Cards Row -->
<div class="stats-grid mb-6">
    <div class="stat-card">
        <div class="stat-icon-wrapper stat-icon-primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
        </div>
        <div class="stat-content">
            <span class="stat-value" id="stat-api-calls"><?= formatNum($summaryStats['total_api_calls'] ?? 0) ?></span>
            <span class="stat-label">Total API Calls</span>
        </div>
        <div class="stat-trend stat-trend-up">
            <span>All time</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon-wrapper stat-icon-success">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M2 12h20"/></svg>
        </div>
        <div class="stat-content">
            <span class="stat-value" id="stat-tokens"><?= formatNum($summaryStats['total_tokens_used'] ?? 0) ?></span>
            <span class="stat-label">Tokens Used</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon-wrapper stat-icon-warning">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 18V6"/></svg>
        </div>
        <div class="stat-content">
            <span class="stat-value" id="stat-spent">$<?= number_format($summaryStats['total_spent'] ?? 0, 2) ?></span>
            <span class="stat-label">Total Spent</span>
        </div>
        <?php $trend = $summaryStats['cost_trend'] ?? 'stable'; ?>
        <div class="stat-trend stat-trend-<?= $trend == 'up' ? 'down' : ($trend == 'down' ? 'up' : 'neutral') ?>">
            <?php if ($trend == 'up'): ?>
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m5 12 7-7 7 7"/></svg>
            <?php elseif ($trend == 'down'): ?>
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m19 12-7 7-7-7"/></svg>
            <?php endif; ?>
            <span><?= abs($summaryStats['cost_trend_percent'] ?? 0) ?>% vs last week</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon-wrapper stat-icon-info">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
        </div>
        <div class="stat-content">
            <span class="stat-value" id="stat-avg-daily"><?= number_format($summaryStats['avg_daily_calls'] ?? 0, 0) ?></span>
            <span class="stat-label">Avg Daily Calls</span>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="grid grid-2 gap-6 mb-6">
    <!-- Line Chart: API Calls & Tokens -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">API Calls & Tokens Over Time</h3>
        </div>
        <div class="card-body">
            <div class="chart-container">
                <canvas id="usageChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Doughnut Chart: Model Usage -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Model Usage Breakdown</h3>
        </div>
        <div class="card-body">
            <div class="chart-container chart-container-pie">
                <canvas id="modelChart"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-2 gap-6 mb-6">
    <!-- Bar Chart: Daily Spending -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daily Spending</h3>
        </div>
        <div class="card-body">
            <div class="chart-container">
                <canvas id="costChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Bar Chart: Hourly Distribution -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Activity by Hour</h3>
        </div>
        <div class="card-body">
            <div class="chart-container">
                <canvas id="hourlyChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Additional Stats -->
<div class="grid grid-3 gap-6 mb-6">
    <div class="card">
        <div class="card-body">
            <div class="info-item">
                <span class="info-label">Most Used Model</span>
                <span class="info-value"><?= htmlspecialchars($summaryStats['most_used_model'] ?? 'N/A') ?></span>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="info-item">
                <span class="info-label">Peak Usage Hour</span>
                <span class="info-value"><?= htmlspecialchars($summaryStats['peak_hour'] ?? 'N/A') ?></span>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="info-item">
                <span class="info-label">Success Rate</span>
                <span class="info-value"><?= $summaryStats['success_rate'] ?? 100 ?>%</span>
            </div>
        </div>
    </div>
</div>

<!-- Endpoint Table -->
<div class="card mb-6">
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
                        <th class="text-right">Avg Response</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($endpointStats, 0, 5) as $endpoint): ?>
                    <tr>
                        <td><code class="code-inline"><?= htmlspecialchars($endpoint['endpoint'] ?? '') ?></code></td>
                        <td class="text-right"><?= formatNum($endpoint['count'] ?? 0) ?></td>
                        <td class="text-right"><?= formatNum($endpoint['tokens'] ?? 0) ?></td>
                        <td class="text-right">$<?= number_format($endpoint['cost'] ?? 0, 4) ?></td>
                        <td class="text-right"><?= number_format($endpoint['avg_response_time'] ?? 0, 0) ?>ms</td>
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

<!-- API Key Usage Table -->
<?php if (!empty($keyStats)): ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Usage by API Key</h3>
    </div>
    <div class="card-body p-0">
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
                    <?php foreach ($keyStats as $keyId => $stats): ?>
                    <tr>
                        <td><?= htmlspecialchars($stats['name'] ?? 'Unknown') ?></td>
                        <td class="text-right"><?= formatNum($stats['total_requests'] ?? 0) ?></td>
                        <td class="text-right"><?= formatNum($stats['total_tokens'] ?? 0) ?></td>
                        <td class="text-right">$<?= number_format($stats['total_cost'] ?? 0, 4) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
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
.stat-content { display: flex; flex-direction: column; }
.stat-value {
    font-size: var(--font-size-2xl);
    font-weight: var(--font-weight-bold);
    color: var(--text-primary);
    line-height: 1.2;
}
.stat-label { font-size: var(--font-size-sm); color: var(--text-secondary); }
.stat-trend {
    display: flex;
    align-items: center;
    gap: var(--space-1);
    font-size: var(--font-size-xs);
    font-weight: var(--font-weight-medium);
}
.stat-trend-up { color: var(--color-success); }
.stat-trend-down { color: var(--color-error); }
.stat-trend-neutral { color: var(--text-muted); }

.filters-form {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: var(--space-4);
    flex-wrap: wrap;
}
.filter-presets { display: flex; gap: var(--space-2); }
.filter-preset {
    padding: var(--space-2) var(--space-4);
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

.chart-container { height: 280px; position: relative; }
.chart-container-pie { height: 300px; }
.chart-container canvas { width: 100% !important; height: 100% !important; }

.info-item { text-align: center; }
.info-label { display: block; font-size: var(--font-size-sm); color: var(--text-muted); margin-bottom: var(--space-2); }
.info-value { display: block; font-size: var(--font-size-xl); font-weight: var(--font-weight-bold); color: var(--text-primary); }

.code-inline {
    font-family: var(--font-mono);
    font-size: var(--font-size-xs);
    background: var(--bg-tertiary);
    padding: var(--space-1) var(--space-2);
    border-radius: var(--radius-sm);
}

.empty-state-sm { padding: var(--space-8); text-align: center; }

.dropdown { position: relative; display: inline-block; }
.dropdown-toggle::after { content: ''; display: none; }
.dropdown-menu {
    display: none;
    position: absolute;
    right: 0;
    top: 100%;
    min-width: 160px;
    background: var(--surface-primary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-lg);
    z-index: 100;
    margin-top: var(--space-1);
}
.dropdown:hover .dropdown-menu { display: block; }
.dropdown-item {
    display: block;
    padding: var(--space-2) var(--space-4);
    color: var(--text-primary);
    text-decoration: none;
    font-size: var(--font-size-sm);
}
.dropdown-item:hover { background: var(--bg-tertiary); }

.grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: var(--space-5); }

@media (max-width: 1279px) {
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
    .grid-3 { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 767px) {
    .stats-grid { grid-template-columns: 1fr; }
    .grid-2 { grid-template-columns: 1fr; }
    .grid-3 { grid-template-columns: 1fr; }
    .filters-form { flex-direction: column; align-items: stretch; }
    .filter-presets { justify-content: center; }
}
</style>

<script>
// Chart configurations
let usageChart, modelChart, costChart, hourlyChart;
let currentDays = <?= $days ?>;

// Initial data from PHP
const initialDailyStats = <?= json_encode($dailyStats ?? []) ?>;
const initialModelStats = <?= json_encode($modelStats ?? []) ?>;
const initialHourlyStats = <?= json_encode($hourlyStats ?? []) ?>;

// Chart colors
const chartColors = {
    primary: '#3b82f6',
    success: '#10b981',
    warning: '#f59e0b',
    danger: '#ef4444',
    purple: '#8b5cf6',
    pink: '#ec4899'
};

// Initialize charts when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
});

function initializeCharts() {
    // Usage Line Chart
    const usageCtx = document.getElementById('usageChart').getContext('2d');
    usageChart = new Chart(usageCtx, {
        type: 'line',
        data: {
            labels: initialDailyStats.map(d => formatDate(d.date)),
            datasets: [
                {
                    label: 'API Calls',
                    data: initialDailyStats.map(d => d.api_calls),
                    borderColor: chartColors.primary,
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true,
                    tension: 0.4,
                    yAxisID: 'y'
                },
                {
                    label: 'Tokens Used',
                    data: initialDailyStats.map(d => d.tokens_used),
                    borderColor: chartColors.success,
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: true,
                    tension: 0.4,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'top' }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: { display: true, text: 'API Calls' }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: { display: true, text: 'Tokens' },
                    grid: { drawOnChartArea: false }
                }
            }
        }
    });

    // Model Doughnut Chart
    const modelCtx = document.getElementById('modelChart').getContext('2d');
    const modelLabels = initialModelStats.map(m => m.model);
    const modelData = initialModelStats.map(m => m.count);
    const colors = [chartColors.primary, chartColors.success, chartColors.warning, chartColors.danger, chartColors.purple, chartColors.pink];
    
    modelChart = new Chart(modelCtx, {
        type: 'doughnut',
        data: {
            labels: modelLabels,
            datasets: [{
                data: modelData,
                backgroundColor: colors.slice(0, modelData.length),
                borderWidth: 2,
                borderColor: getComputedStyle(document.documentElement).getPropertyValue('--surface-primary').trim() || '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'right' }
            }
        }
    });

    // Cost Bar Chart
    const costCtx = document.getElementById('costChart').getContext('2d');
    costChart = new Chart(costCtx, {
        type: 'bar',
        data: {
            labels: initialDailyStats.map(d => formatDate(d.date)),
            datasets: [{
                label: 'Daily Cost ($)',
                data: initialDailyStats.map(d => parseFloat(d.cost)),
                backgroundColor: chartColors.warning,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) { return '$' + value.toFixed(4); }
                    }
                }
            }
        }
    });

    // Hourly Bar Chart
    const hourlyCtx = document.getElementById('hourlyChart').getContext('2d');
    const hourlyLabels = [];
    for (let i = 0; i < 24; i++) {
        hourlyLabels.push(i.toString().padStart(2, '0') + ':00');
    }
    
    hourlyChart = new Chart(hourlyCtx, {
        type: 'bar',
        data: {
            labels: hourlyLabels,
            datasets: [{
                label: 'Requests',
                data: initialHourlyStats.map(h => h.count),
                backgroundColor: chartColors.purple,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
}

function formatDate(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
}

function changeDays(days) {
    currentDays = days;
    window.location.href = '/analytics?days=' + days;
}

async function refreshCharts() {
    try {
        // Fetch all data in parallel
        const [usageRes, modelRes, costRes, hourlyRes, statsRes] = await Promise.all([
            fetch('/api/analytics/usage?days=' + currentDays),
            fetch('/api/analytics/models?days=' + currentDays),
            fetch('/api/analytics/costs?days=' + currentDays),
            fetch('/api/analytics/hourly?days=' + currentDays),
            fetch('/api/analytics/stats')
        ]);

        const usageData = await usageRes.json();
        const modelData = await modelRes.json();
        const costData = await costRes.json();
        const hourlyData = await hourlyRes.json();
        const statsData = await statsRes.json();

        // Update usage chart
        usageChart.data.labels = usageData.labels.map(d => formatDate(d));
        usageChart.data.datasets[0].data = usageData.datasets[0].data;
        usageChart.data.datasets[1].data = usageData.datasets[1].data;
        usageChart.update();

        // Update model chart
        modelChart.data.labels = modelData.labels;
        modelChart.data.datasets[0].data = modelData.data;
        modelChart.update();

        // Update cost chart
        costChart.data.labels = costData.labels.map(d => formatDate(d));
        costChart.data.datasets[0].data = costData.data;
        costChart.update();

        // Update hourly chart
        hourlyChart.data.datasets[0].data = hourlyData.data;
        hourlyChart.update();

        // Update stats
        document.getElementById('stat-api-calls').textContent = formatNumber(statsData.total_api_calls);
        document.getElementById('stat-tokens').textContent = formatNumber(statsData.total_tokens_used);
        document.getElementById('stat-spent').textContent = '$' + statsData.total_spent.toFixed(2);
        document.getElementById('stat-avg-daily').textContent = Math.round(statsData.avg_daily_calls);

    } catch (error) {
        console.error('Error refreshing charts:', error);
    }
}

function formatNumber(num) {
    if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
    if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
    return num.toLocaleString();
}
</script>
