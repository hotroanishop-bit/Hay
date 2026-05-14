<?php
/**
 * Admin Incidents Page
 * Variables: $pageTitle, $currentPage, $incidents, $stats
 */
?>

<div class="page-header">
    <div class="page-header-content">
        <h1>Incident Management</h1>
        <p>Manage system incidents and status page updates</p>
    </div>
    <div class="page-header-actions">
        <a href="/status" target="_blank" class="btn btn-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
            View Status Page
        </a>
        <a href="/admin/incidents/create" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Report Incident
        </a>
    </div>
</div>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon bg-primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
        </div>
        <div class="stat-content">
            <span class="stat-label">Total Incidents</span>
            <span class="stat-value"><?= number_format($stats['total'] ?? 0) ?></span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-danger">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
        </div>
        <div class="stat-content">
            <span class="stat-label">Active</span>
            <span class="stat-value"><?= number_format($stats['active'] ?? 0) ?></span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-success">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
        </div>
        <div class="stat-content">
            <span class="stat-label">Resolved</span>
            <span class="stat-value"><?= number_format($stats['resolved'] ?? 0) ?></span>
        </div>
    </div>
</div>

<!-- Severity Breakdown -->
<div class="stats-grid mt-4">
    <div class="stat-card stat-card-sm">
        <span class="badge badge-critical">Critical</span>
        <span class="stat-mini-value"><?= number_format($stats['critical'] ?? 0) ?></span>
    </div>
    <div class="stat-card stat-card-sm">
        <span class="badge badge-major">Major</span>
        <span class="stat-mini-value"><?= number_format($stats['major'] ?? 0) ?></span>
    </div>
    <div class="stat-card stat-card-sm">
        <span class="badge badge-minor">Minor</span>
        <span class="stat-mini-value"><?= number_format($stats['minor'] ?? 0) ?></span>
    </div>
</div>

<!-- Incidents Table -->
<div class="card mt-6">
    <div class="card-header">
        <h3>All Incidents</h3>
    </div>
    <div class="card-body">
        <?php if (!empty($incidents)): ?>
        <div class="table-responsive">
            <table class="table table-hover admin-table">
                <thead>
                    <tr>
                        <th>Incident</th>
                        <th>Severity</th>
                        <th>Status</th>
                        <th>Started</th>
                        <th>Duration</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($incidents as $incident): ?>
                    <tr>
                        <td>
                            <div class="incident-title">
                                <strong><?= htmlspecialchars($incident['title']) ?></strong>
                                <?php if (!empty($incident['description'])): ?>
                                <small class="text-muted d-block"><?= htmlspecialchars(substr($incident['description'], 0, 60)) ?><?= strlen($incident['description']) > 60 ? '...' : '' ?></small>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-<?= htmlspecialchars($incident['severity']) ?>">
                                <?= ucfirst(htmlspecialchars($incident['severity'])) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-status-<?= htmlspecialchars($incident['status']) ?>">
                                <?= ucfirst(htmlspecialchars($incident['status'])) ?>
                            </span>
                        </td>
                        <td>
                            <small><?= date('M d, Y H:i', strtotime($incident['started_at'])) ?></small>
                        </td>
                        <td>
                            <?php
                            $start = strtotime($incident['started_at']);
                            $end = $incident['resolved_at'] ? strtotime($incident['resolved_at']) : time();
                            $duration = $end - $start;
                            if ($duration < 3600) {
                                echo round($duration / 60) . 'm';
                            } elseif ($duration < 86400) {
                                echo round($duration / 3600, 1) . 'h';
                            } else {
                                echo round($duration / 86400, 1) . 'd';
                            }
                            ?>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="/admin/incidents/<?= (int)$incident['id'] ?>/edit" class="btn btn-sm btn-secondary" title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                </a>
                                <?php if ($incident['status'] !== 'resolved'): ?>
                                <form action="/admin/incidents/<?= (int)$incident['id'] ?>/resolve" method="POST" class="d-inline" onsubmit="return confirm('Mark this incident as resolved?');">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                    <button type="submit" class="btn btn-sm btn-success" title="Resolve">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    </button>
                                </form>
                                <?php endif; ?>
                                <form action="/admin/incidents/<?= (int)$incident['id'] ?>/delete" method="POST" class="d-inline" onsubmit="return confirm('Delete this incident?');">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            <h3>No incidents recorded</h3>
            <p>All systems are running smoothly. Report an incident when issues occur.</p>
            <a href="/admin/incidents/create" class="btn btn-primary">Report Incident</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: var(--space-4);
}

.stat-card {
    display: flex;
    align-items: center;
    gap: var(--space-4);
    padding: var(--space-4);
    background: var(--surface-primary);
    border-radius: var(--radius-lg);
    border: 1px solid var(--border-color);
}

.stat-card-sm {
    justify-content: space-between;
    padding: var(--space-3);
}

.stat-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    border-radius: var(--radius-lg);
    color: var(--color-white);
}

.stat-icon.bg-primary { background: var(--color-primary); }
.stat-icon.bg-success { background: var(--color-success); }
.stat-icon.bg-danger { background: var(--color-danger, #ef4444); }

.stat-content {
    display: flex;
    flex-direction: column;
}

.stat-label {
    font-size: var(--font-size-sm);
    color: var(--text-muted);
}

.stat-value {
    font-size: var(--font-size-xl);
    font-weight: var(--font-weight-bold);
    color: var(--text-primary);
}

.stat-mini-value {
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-bold);
    color: var(--text-primary);
}

.incident-title {
    display: flex;
    flex-direction: column;
    gap: var(--space-1);
}

.badge-critical {
    background: rgba(239, 68, 68, 0.1);
    color: #dc2626;
}

.badge-major {
    background: rgba(249, 115, 22, 0.1);
    color: #ea580c;
}

.badge-minor {
    background: rgba(245, 158, 11, 0.1);
    color: #d97706;
}

.badge-status-investigating {
    background: rgba(99, 102, 241, 0.1);
    color: #6366f1;
}

.badge-status-identified {
    background: rgba(249, 115, 22, 0.1);
    color: #ea580c;
}

.badge-status-monitoring {
    background: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
}

.badge-status-resolved {
    background: rgba(16, 185, 129, 0.1);
    color: #059669;
}

.empty-state {
    text-align: center;
    padding: var(--space-10);
    color: var(--text-muted);
}

.empty-state svg {
    margin-bottom: var(--space-4);
    color: var(--color-success);
}

.empty-state h3 {
    margin: 0 0 var(--space-2);
    color: var(--text-primary);
}

.empty-state p {
    margin: 0 0 var(--space-4);
}

@media (max-width: 1023px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>
