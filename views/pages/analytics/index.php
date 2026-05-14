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

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3>API Usage by Model</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($modelStats)): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Model</th>
                                <th>Total Requests</th>
                                <th>Input Tokens</th>
                                <th>Output Tokens</th>
                                <th>Total Tokens</th>
                                <th>Total Cost</th>
                                <th>Avg Tokens/Request</th>
                                <th>Avg Response Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($modelStats as $model): ?>
                            <tr>
                                <td><code><?= htmlspecialchars($model['model'] ?? 'unknown') ?></code></td>
                                <td><?= number_format($model['total_requests'] ?? 0) ?></td>
                                <td><?= number_format($model['total_input_tokens'] ?? 0) ?></td>
                                <td><?= number_format($model['total_output_tokens'] ?? 0) ?></td>
                                <td><?= number_format($model['total_tokens'] ?? 0) ?></td>
                                <td>$<?= number_format($model['total_cost'] ?? 0, 4) ?></td>
                                <td><?= number_format($model['avg_tokens_per_request'] ?? 0, 1) ?></td>
                                <td><?= number_format($model['avg_response_time_ms'] ?? 0, 0) ?>ms</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <?php
                            $totalModelRequests = array_sum(array_column($modelStats, 'total_requests'));
                            $totalModelInputTokens = array_sum(array_column($modelStats, 'total_input_tokens'));
                            $totalModelOutputTokens = array_sum(array_column($modelStats, 'total_output_tokens'));
                            $totalModelTokens = array_sum(array_column($modelStats, 'total_tokens'));
                            $totalModelCost = array_sum(array_column($modelStats, 'total_cost'));
                            ?>
                            <tr class="table-footer-row">
                                <td><strong>Total</strong></td>
                                <td><strong><?= number_format($totalModelRequests) ?></strong></td>
                                <td><strong><?= number_format($totalModelInputTokens) ?></strong></td>
                                <td><strong><?= number_format($totalModelOutputTokens) ?></strong></td>
                                <td><strong><?= number_format($totalModelTokens) ?></strong></td>
                                <td><strong>$<?= number_format($totalModelCost, 4) ?></strong></td>
                                <td>-</td>
                                <td>-</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <!-- Cost Breakdown by Model -->
                <div class="mt-4">
                    <h4>Cost Breakdown by Model</h4>
                    <div class="cost-breakdown">
                        <?php if ($totalModelCost > 0): ?>
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Model</th>
                                    <th>Cost</th>
                                    <th>% of Total</th>
                                    <th>Breakdown</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($modelStats as $model): ?>
                                <?php 
                                $costPercentage = $totalModelCost > 0 ? ($model['total_cost'] / $totalModelCost) * 100 : 0;
                                ?>
                                <tr>
                                    <td><code><?= htmlspecialchars($model['model'] ?? 'unknown') ?></code></td>
                                    <td>$<?= number_format($model['total_cost'] ?? 0, 4) ?></td>
                                    <td><?= number_format($costPercentage, 1) ?>%</td>
                                    <td>
                                        <div class="progress" style="height: 20px; min-width: 100px;">
                                            <div class="progress-bar" role="progressbar" style="width: <?= $costPercentage ?>%;" aria-valuenow="<?= $costPercentage ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                        <p class="text-muted">No cost data available</p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php else: ?>
                <p class="text-muted">No model usage data available</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
