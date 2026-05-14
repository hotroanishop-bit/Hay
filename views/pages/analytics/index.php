<?php
/**
 * Analytics Index Page
 * Variables: $pageTitle, $currentPage, $overallStats, $dailyStats, $endpointStats,
 *            $hourlyStats, $apiKeys, $keyStats, $startDate, $endDate
 */
?>

<div class="page-header">
    <div class="page-header-content">
        <h1>Analytics</h1>
        <p>Monitor your API usage and performance</p>
    </div>
    <div class="page-header-actions">
        <a href="/analytics/export?type=daily" class="btn btn-secondary">
            <i class="icon-download"></i> Export CSV
        </a>
    </div>
</div>

<div class="filters-card card mb-4">
    <div class="card-body">
        <form action="/analytics" method="GET" class="form-inline">
            <div class="form-group mr-3">
                <label for="start_date" class="mr-2">From:</label>
                <input type="date" id="start_date" name="start_date" class="form-control" value="<?= htmlspecialchars($startDate ?? '') ?>">
            </div>
            <div class="form-group mr-3">
                <label for="end_date" class="mr-2">To:</label>
                <input type="date" id="end_date" name="end_date" class="form-control" value="<?= htmlspecialchars($endDate ?? '') ?>">
            </div>
            <button type="submit" class="btn btn-primary">Apply Filter</button>
        </form>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon stat-icon-primary">
            <i class="icon-api"></i>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?= number_format($overallStats['total_requests'] ?? 0) ?></span>
            <span class="stat-label">Total Requests</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon stat-icon-success">
            <i class="icon-check"></i>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?= number_format($overallStats['successful_requests'] ?? 0) ?></span>
            <span class="stat-label">Successful Requests</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon stat-icon-info">
            <i class="icon-token"></i>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?= number_format($overallStats['total_tokens'] ?? 0) ?></span>
            <span class="stat-label">Tokens Used</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon stat-icon-warning">
            <i class="icon-dollar"></i>
        </div>
        <div class="stat-content">
            <span class="stat-value">$<?= number_format($overallStats['total_cost'] ?? 0, 4) ?></span>
            <span class="stat-label">Total Cost</span>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3>Daily Usage (Last 30 Days)</h3>
            </div>
            <div class="card-body">
                <div class="chart-container" id="daily-usage-chart" data-stats='<?= htmlspecialchars(json_encode($dailyStats ?? [])) ?>'>
                    <div class="chart-placeholder">
                        <p class="text-muted">Loading chart...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3>Hourly Distribution</h3>
            </div>
            <div class="card-body">
                <div class="chart-container" id="hourly-chart" data-stats='<?= htmlspecialchars(json_encode($hourlyStats ?? [])) ?>'>
                    <div class="chart-placeholder">
                        <p class="text-muted">Loading chart...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3>Top Endpoints</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($endpointStats)): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Endpoint</th>
                                <th>Requests</th>
                                <th>Tokens</th>
                                <th>Cost</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($endpointStats as $endpoint): ?>
                            <tr>
                                <td><code><?= htmlspecialchars($endpoint['endpoint'] ?? '') ?></code></td>
                                <td><?= number_format($endpoint['requests'] ?? 0) ?></td>
                                <td><?= number_format($endpoint['total_tokens'] ?? 0) ?></td>
                                <td>$<?= number_format($endpoint['total_cost'] ?? 0, 4) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-muted">No endpoint data available</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3>Usage by API Key</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($keyStats)): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Key Name</th>
                                <th>Requests</th>
                                <th>Tokens</th>
                                <th>Cost</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($keyStats as $keyId => $stats): ?>
                            <tr>
                                <td><?= htmlspecialchars($stats['name'] ?? 'Unknown') ?></td>
                                <td><?= number_format($stats['total_requests'] ?? 0) ?></td>
                                <td><?= number_format($stats['total_tokens'] ?? 0) ?></td>
                                <td>$<?= number_format($stats['total_cost'] ?? 0, 4) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-muted">No API key usage data available</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
