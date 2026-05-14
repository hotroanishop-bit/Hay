<?php
/**
 * Public Status Page
 * Variables: $pageTitle, $overallStatus, $components, $activeIncidents, $recentIncidents, $uptime, $lastUpdated
 */
?>

<div class="status-page">
    <!-- Header -->
    <header class="status-header">
        <div class="status-container">
            <a href="/" class="status-logo">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"></path><path d="M2 17l10 5 10-5"></path><path d="M2 12l10 5 10-5"></path></svg>
                <span>Hay API Gateway</span>
            </a>
            <nav class="status-nav">
                <a href="/">Home</a>
                <a href="/docs">Docs</a>
                <a href="/login">Login</a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="status-main">
        <div class="status-container">
            <!-- Overall Status Banner -->
            <div class="status-banner status-<?= htmlspecialchars($overallStatus) ?>">
                <div class="status-indicator">
                    <?php if ($overallStatus === 'operational'): ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    <?php elseif ($overallStatus === 'degraded'): ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                    <?php else: ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                    <?php endif; ?>
                </div>
                <div class="status-text">
                    <h1>
                        <?php 
                        switch ($overallStatus) {
                            case 'operational':
                                echo 'All Systems Operational';
                                break;
                            case 'degraded':
                                echo 'Partial System Outage';
                                break;
                            case 'outage':
                                echo 'Major System Outage';
                                break;
                        }
                        ?>
                    </h1>
                    <p>Last updated: <?= htmlspecialchars($lastUpdated) ?></p>
                </div>
            </div>

            <!-- Uptime Stats -->
            <div class="uptime-card">
                <div class="uptime-value"><?= number_format($uptime, 2) ?>%</div>
                <div class="uptime-label">Uptime over the past 30 days</div>
            </div>

            <!-- Active Incidents -->
            <?php if (!empty($activeIncidents)): ?>
            <section class="incidents-section active-incidents">
                <h2>Active Incidents</h2>
                <?php foreach ($activeIncidents as $incident): ?>
                <div class="incident-card incident-<?= htmlspecialchars($incident['severity']) ?>">
                    <div class="incident-header">
                        <span class="incident-badge badge-<?= htmlspecialchars($incident['severity']) ?>">
                            <?= ucfirst(htmlspecialchars($incident['severity'])) ?>
                        </span>
                        <span class="incident-status">
                            <?= ucfirst(htmlspecialchars($incident['status'])) ?>
                        </span>
                    </div>
                    <h3><?= htmlspecialchars($incident['title']) ?></h3>
                    <p class="incident-description"><?= nl2br(htmlspecialchars($incident['description'] ?? '')) ?></p>
                    <p class="incident-time">Started: <?= date('M d, Y H:i', strtotime($incident['started_at'])) ?></p>
                    
                    <?php if (!empty($incident['updates'])): ?>
                    <div class="incident-updates">
                        <h4>Updates</h4>
                        <?php foreach ($incident['updates'] as $update): ?>
                        <div class="incident-update">
                            <span class="update-status badge-<?= htmlspecialchars($update['status']) ?>"><?= ucfirst(htmlspecialchars($update['status'])) ?></span>
                            <span class="update-time"><?= date('M d, H:i', strtotime($update['created_at'])) ?></span>
                            <p><?= nl2br(htmlspecialchars($update['message'])) ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </section>
            <?php endif; ?>

            <!-- Component Status -->
            <section class="components-section">
                <h2>System Components</h2>
                <div class="components-list">
                    <?php foreach ($components as $key => $component): ?>
                    <div class="component-item">
                        <div class="component-info">
                            <h3><?= htmlspecialchars($component['name']) ?></h3>
                            <p><?= htmlspecialchars($component['description']) ?></p>
                        </div>
                        <div class="component-status status-<?= htmlspecialchars($component['status']) ?>">
                            <?php if ($component['status'] === 'operational'): ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                            <span>Operational</span>
                            <?php elseif ($component['status'] === 'degraded'): ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                            <span>Degraded</span>
                            <?php else: ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                            <span>Outage</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- Incident History -->
            <?php if (!empty($recentIncidents)): ?>
            <section class="incidents-section history-section">
                <h2>Past Incidents</h2>
                <div class="incidents-timeline">
                    <?php 
                    $currentDate = '';
                    foreach ($recentIncidents as $incident): 
                        $incidentDate = date('F j, Y', strtotime($incident['resolved_at'] ?? $incident['started_at']));
                        if ($incidentDate !== $currentDate):
                            $currentDate = $incidentDate;
                    ?>
                    <div class="timeline-date"><?= htmlspecialchars($currentDate) ?></div>
                    <?php endif; ?>
                    
                    <div class="timeline-item">
                        <div class="timeline-marker severity-<?= htmlspecialchars($incident['severity']) ?>"></div>
                        <div class="timeline-content">
                            <h4><?= htmlspecialchars($incident['title']) ?></h4>
                            <p class="timeline-meta">
                                <span class="badge badge-resolved">Resolved</span>
                                <span class="timeline-duration">
                                    <?php
                                    $start = strtotime($incident['started_at']);
                                    $end = strtotime($incident['resolved_at']);
                                    $duration = $end - $start;
                                    if ($duration < 3600) {
                                        echo round($duration / 60) . ' minutes';
                                    } elseif ($duration < 86400) {
                                        echo round($duration / 3600, 1) . ' hours';
                                    } else {
                                        echo round($duration / 86400, 1) . ' days';
                                    }
                                    ?>
                                </span>
                            </p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php else: ?>
            <section class="incidents-section history-section">
                <h2>Past Incidents</h2>
                <div class="no-incidents">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    <p>No incidents recorded in the past 30 days.</p>
                </div>
            </section>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer -->
    <footer class="status-footer">
        <div class="status-container">
            <p>&copy; <?= date('Y') ?> Hay API Gateway. All rights reserved.</p>
            <div class="footer-links">
                <a href="/">Home</a>
                <a href="/docs">Documentation</a>
                <a href="/changelog">Changelog</a>
            </div>
        </div>
    </footer>
</div>

<link rel="stylesheet" href="/css/pages/status.css">
