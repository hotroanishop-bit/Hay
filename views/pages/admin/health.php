<?php
/**
 * Admin System Health Page
 * Shows system health metrics with visual gauges
 * 
 * Variables: $pageTitle, $currentPage, $metrics
 */

$disk = $metrics['disk'] ?? [];
$memory = $metrics['memory'] ?? [];
$database = $metrics['database'] ?? [];
$upstream = $metrics['upstream'] ?? [];
$recentErrors = $metrics['recent_errors'] ?? [];
$components = $metrics['components'] ?? [];
$overallStatus = $metrics['overall_status'] ?? 'unknown';
$uptime = $metrics['uptime'] ?? 99.99;
?>

<div class="page-header">
    <div class="page-header-content">
        <div>
            <h1>System Health</h1>
            <p class="text-muted">Monitor system resources and performance</p>
        </div>
        <div class="page-header-actions">
            <label class="auto-refresh-toggle">
                <input type="checkbox" id="autoRefresh" checked>
                <span>Auto-refresh</span>
            </label>
            <button type="button" class="btn btn-outline" id="refreshBtn" onclick="refreshMetrics()">
                <i class="icon-refresh-cw"></i> Refresh
            </button>
        </div>
    </div>
</div>

<!-- Overall Status -->
<div class="health-status-banner status-<?= htmlspecialchars($overallStatus) ?>">
    <div class="status-indicator"></div>
    <div class="status-content">
        <span class="status-label">System Status:</span>
        <span class="status-value"><?= ucfirst(htmlspecialchars($overallStatus)) ?></span>
    </div>
    <div class="status-uptime">
        <span class="uptime-value"><?= number_format($uptime, 2) ?>%</span>
        <span class="uptime-label">Uptime (30d)</span>
    </div>
    <div class="status-time">
        Last updated: <span id="lastUpdated"><?= date('H:i:s') ?></span>
    </div>
</div>

<!-- Component Status Cards -->
<div class="stats-grid stats-grid-3 mb-4">
    <?php foreach ($components as $key => $component): ?>
    <div class="stat-card component-status status-<?= htmlspecialchars($component['status'] ?? 'unknown') ?>">
        <div class="stat-icon stat-icon-<?= ($component['status'] ?? '') === 'operational' ? 'success' : (($component['status'] ?? '') === 'degraded' ? 'warning' : 'error') ?>">
            <?php if ($key === 'database'): ?>
            <i class="icon-database"></i>
            <?php elseif ($key === 'api'): ?>
            <i class="icon-server"></i>
            <?php else: ?>
            <i class="icon-globe"></i>
            <?php endif; ?>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?= htmlspecialchars($component['name'] ?? ucfirst($key)) ?></span>
            <span class="stat-label">
                <?php if (!empty($component['latency_ms'])): ?>
                <?= number_format($component['latency_ms'], 1) ?>ms
                <?php else: ?>
                <?= ucfirst($component['status'] ?? 'Unknown') ?>
                <?php endif; ?>
            </span>
        </div>
        <div class="component-badge badge-<?= ($component['status'] ?? '') === 'operational' ? 'success' : (($component['status'] ?? '') === 'degraded' ? 'warning' : 'error') ?>">
            <?= ucfirst($component['status'] ?? 'Unknown') ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Resource Gauges -->
<div class="gauges-grid mb-4">
    <!-- Disk Space Gauge -->
    <div class="card gauge-card">
        <div class="card-header">
            <h3><i class="icon-hard-drive"></i> Disk Space</h3>
        </div>
        <div class="card-body">
            <div class="gauge" data-value="<?= $disk['percent'] ?? 0 ?>">
                <div class="gauge-ring">
                    <svg viewBox="0 0 100 100">
                        <circle class="gauge-bg" cx="50" cy="50" r="45" />
                        <circle class="gauge-fill disk-gauge" cx="50" cy="50" r="45" 
                                style="--percent: <?= $disk['percent'] ?? 0 ?>;" />
                    </svg>
                    <div class="gauge-center">
                        <span class="gauge-value" id="diskPercent"><?= $disk['percent'] ?? 0 ?>%</span>
                        <span class="gauge-label">Used</span>
                    </div>
                </div>
            </div>
            <div class="gauge-details">
                <div class="gauge-detail">
                    <span class="detail-label">Used</span>
                    <span class="detail-value" id="diskUsed"><?= htmlspecialchars($disk['used_formatted'] ?? 'N/A') ?></span>
                </div>
                <div class="gauge-detail">
                    <span class="detail-label">Free</span>
                    <span class="detail-value" id="diskFree"><?= htmlspecialchars($disk['free_formatted'] ?? 'N/A') ?></span>
                </div>
                <div class="gauge-detail">
                    <span class="detail-label">Total</span>
                    <span class="detail-value" id="diskTotal"><?= htmlspecialchars($disk['total_formatted'] ?? 'N/A') ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Memory Usage Gauge -->
    <div class="card gauge-card">
        <div class="card-header">
            <h3><i class="icon-cpu"></i> Memory Usage</h3>
        </div>
        <div class="card-body">
            <div class="gauge" data-value="<?= $memory['percent'] ?? 0 ?>">
                <div class="gauge-ring">
                    <svg viewBox="0 0 100 100">
                        <circle class="gauge-bg" cx="50" cy="50" r="45" />
                        <circle class="gauge-fill memory-gauge" cx="50" cy="50" r="45" 
                                style="--percent: <?= $memory['percent'] ?? 0 ?>;" />
                    </svg>
                    <div class="gauge-center">
                        <span class="gauge-value" id="memoryPercent"><?= $memory['percent'] ?? 0 ?>%</span>
                        <span class="gauge-label">Used</span>
                    </div>
                </div>
            </div>
            <div class="gauge-details">
                <div class="gauge-detail">
                    <span class="detail-label">Current</span>
                    <span class="detail-value" id="memoryCurrent"><?= htmlspecialchars($memory['current_formatted'] ?? 'N/A') ?></span>
                </div>
                <div class="gauge-detail">
                    <span class="detail-label">Peak</span>
                    <span class="detail-value" id="memoryPeak"><?= htmlspecialchars($memory['peak_formatted'] ?? 'N/A') ?></span>
                </div>
                <div class="gauge-detail">
                    <span class="detail-label">Limit</span>
                    <span class="detail-value" id="memoryLimit"><?= htmlspecialchars($memory['limit_formatted'] ?? 'N/A') ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Database Status -->
    <div class="card gauge-card">
        <div class="card-header">
            <h3><i class="icon-database"></i> Database</h3>
        </div>
        <div class="card-body">
            <div class="db-status">
                <div class="db-indicator <?= ($database['connected'] ?? false) ? 'connected' : 'disconnected' ?>">
                    <span class="indicator-dot"></span>
                    <span id="dbStatus"><?= ($database['connected'] ?? false) ? 'Connected' : 'Disconnected' ?></span>
                </div>
                <div class="db-latency">
                    <span class="latency-value" id="dbLatency"><?= number_format($database['latency_ms'] ?? 0, 1) ?></span>
                    <span class="latency-unit">ms</span>
                </div>
            </div>
            <div class="gauge-details">
                <div class="gauge-detail">
                    <span class="detail-label">Host</span>
                    <span class="detail-value" id="dbHost"><?= htmlspecialchars($database['host'] ?? 'N/A') ?></span>
                </div>
                <div class="gauge-detail">
                    <span class="detail-label">Database</span>
                    <span class="detail-value" id="dbName"><?= htmlspecialchars($database['database'] ?? 'N/A') ?></span>
                </div>
                <div class="gauge-detail">
                    <span class="detail-label">Size</span>
                    <span class="detail-value" id="dbSize"><?= htmlspecialchars($database['size_formatted'] ?? 'N/A') ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- System Info -->
<div class="cards-grid mb-4">
    <div class="card">
        <div class="card-header">
            <h3><i class="icon-info"></i> System Information</h3>
        </div>
        <div class="card-body">
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">PHP Version</span>
                    <span class="info-value"><?= htmlspecialchars($metrics['php_version'] ?? 'Unknown') ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Server Time</span>
                    <span class="info-value" id="serverTime"><?= htmlspecialchars($metrics['server_time'] ?? date('Y-m-d H:i:s')) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Memory Limit</span>
                    <span class="info-value"><?= htmlspecialchars(ini_get('memory_limit')) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Max Execution Time</span>
                    <span class="info-value"><?= ini_get('max_execution_time') ?>s</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Upstream Status -->
    <div class="card">
        <div class="card-header">
            <h3><i class="icon-cloud"></i> Upstream Providers</h3>
        </div>
        <div class="card-body p-0">
            <?php if (!empty($upstream)): ?>
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Provider</th>
                        <th>Status</th>
                        <th>Endpoint</th>
                    </tr>
                </thead>
                <tbody id="upstreamTable">
                    <?php foreach ($upstream as $name => $provider): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($provider['name'] ?? $name) ?></strong></td>
                        <td>
                            <span class="badge badge-<?= ($provider['status'] ?? '') === 'configured' ? 'success' : (($provider['status'] ?? '') === 'error' ? 'error' : 'gray') ?>">
                                <?= ucfirst($provider['status'] ?? 'Unknown') ?>
                            </span>
                        </td>
                        <td class="text-muted"><?= htmlspecialchars($provider['base_url'] ?? $provider['message'] ?? 'N/A') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state-sm">
                <p>No upstream providers configured</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Recent Errors -->
<div class="card">
    <div class="card-header">
        <div class="card-header-content">
            <h3><i class="icon-alert-triangle"></i> Recent Errors</h3>
            <span class="badge badge-gray" id="errorCount"><?= count($recentErrors['errors'] ?? []) ?> errors</span>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (!empty($recentErrors['errors'])): ?>
        <div class="error-log" id="errorLog">
            <?php foreach (array_reverse($recentErrors['errors'] ?? []) as $error): ?>
            <div class="error-entry level-<?= strtolower($error['level'] ?? 'unknown') ?>">
                <div class="error-meta">
                    <span class="error-level"><?= htmlspecialchars($error['level'] ?? 'UNKNOWN') ?></span>
                    <span class="error-time"><?= htmlspecialchars($error['timestamp'] ?? '') ?></span>
                </div>
                <div class="error-message"><?= htmlspecialchars(substr($error['message'] ?? '', 0, 500)) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-state-sm">
            <i class="icon-check-circle"></i>
            <p>No recent errors found</p>
            <?php if (!empty($recentErrors['message'])): ?>
            <small class="text-muted"><?= htmlspecialchars($recentErrors['message']) ?></small>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
