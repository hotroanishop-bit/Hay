<?php
/**
 * Admin Changelogs Page
 * Variables: $pageTitle, $currentPage, $changelogs, $stats
 */
?>

<div class="page-header">
    <div class="page-header-content">
        <h1>Changelog Management</h1>
        <p>Manage product updates, release notes, and version history</p>
    </div>
    <div class="page-header-actions">
        <a href="/changelog" target="_blank" class="btn btn-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
            View Public Page
        </a>
        <a href="/admin/changelogs/create" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Add Entry
        </a>
    </div>
</div>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon bg-primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
        </div>
        <div class="stat-content">
            <span class="stat-label">Total Entries</span>
            <span class="stat-value"><?= number_format($stats['total'] ?? 0) ?></span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-success">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
        </div>
        <div class="stat-content">
            <span class="stat-label">Published</span>
            <span class="stat-value"><?= number_format($stats['published'] ?? 0) ?></span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-warning">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
        </div>
        <div class="stat-content">
            <span class="stat-label">Draft/Scheduled</span>
            <span class="stat-value"><?= number_format($stats['draft'] ?? 0) ?></span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-info">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h.01"></path><path d="M7 20h-.01"></path><path d="M17 20h.01"></path><path d="M12 15v-3"></path><path d="M12 8V6"></path></svg>
        </div>
        <div class="stat-content">
            <span class="stat-label">Versions</span>
            <span class="stat-value"><?= number_format($stats['versions'] ?? 0) ?></span>
        </div>
    </div>
</div>

<!-- Type Breakdown -->
<div class="stats-grid mt-4">
    <div class="stat-card stat-card-sm">
        <span class="badge badge-feature">Features</span>
        <span class="stat-mini-value"><?= number_format($stats['features'] ?? 0) ?></span>
    </div>
    <div class="stat-card stat-card-sm">
        <span class="badge badge-fix">Fixes</span>
        <span class="stat-mini-value"><?= number_format($stats['fixes'] ?? 0) ?></span>
    </div>
    <div class="stat-card stat-card-sm">
        <span class="badge badge-improvement">Improvements</span>
        <span class="stat-mini-value"><?= number_format($stats['improvements'] ?? 0) ?></span>
    </div>
    <div class="stat-card stat-card-sm">
        <span class="badge badge-security">Security</span>
        <span class="stat-mini-value"><?= number_format($stats['security'] ?? 0) ?></span>
    </div>
</div>

<!-- Changelogs Table -->
<div class="card mt-6">
    <div class="card-header">
        <h3>All Changelog Entries</h3>
    </div>
    <div class="card-body">
        <?php if (!empty($changelogs)): ?>
        <div class="table-responsive">
            <table class="table table-hover admin-table">
                <thead>
                    <tr>
                        <th>Version</th>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($changelogs as $log): ?>
                    <tr>
                        <td>
                            <span class="version-tag">v<?= htmlspecialchars($log['version'] ?? '') ?></span>
                        </td>
                        <td>
                            <div class="changelog-title-cell">
                                <strong><?= htmlspecialchars($log['title'] ?? '') ?></strong>
                                <?php if (!empty($log['description'])): ?>
                                <small class="text-muted d-block"><?= htmlspecialchars(substr($log['description'], 0, 60)) ?><?= strlen($log['description']) > 60 ? '...' : '' ?></small>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <?php 
                            $typeBadges = [
                                'feature' => '<span class="badge badge-feature">Feature</span>',
                                'fix' => '<span class="badge badge-fix">Fix</span>',
                                'improvement' => '<span class="badge badge-improvement">Improvement</span>',
                                'security' => '<span class="badge badge-security">Security</span>'
                            ];
                            echo $typeBadges[$log['type'] ?? ''] ?? '<span class="badge">Unknown</span>';
                            ?>
                        </td>
                        <td>
                            <?php 
                            $isPublished = !empty($log['published_at']) && strtotime($log['published_at']) <= time();
                            $isScheduled = !empty($log['published_at']) && strtotime($log['published_at']) > time();
                            ?>
                            <?php if ($isPublished): ?>
                            <span class="badge badge-success">Published</span>
                            <small class="text-muted d-block"><?= date('M d, Y', strtotime($log['published_at'])) ?></small>
                            <?php elseif ($isScheduled): ?>
                            <span class="badge badge-warning">Scheduled</span>
                            <small class="text-muted d-block"><?= date('M d, Y H:i', strtotime($log['published_at'])) ?></small>
                            <?php else: ?>
                            <span class="badge badge-secondary">Draft</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <small class="text-muted"><?= date('M d, Y', strtotime($log['created_at'])) ?></small>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="/admin/changelogs/<?= (int)$log['id'] ?>/edit" class="btn btn-sm btn-secondary" title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                </a>
                                <form action="/admin/changelogs/<?= (int)$log['id'] ?>/delete" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this changelog entry?');">
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
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
            <h3>No changelog entries</h3>
            <p>Create your first changelog entry to inform users about product updates.</p>
            <a href="/admin/changelogs/create" class="btn btn-primary">Add First Entry</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
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
.stat-icon.bg-info { background: var(--color-info); }
.stat-icon.bg-warning { background: var(--color-warning); }

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

.version-tag {
    display: inline-block;
    padding: var(--space-1) var(--space-2);
    background: var(--bg-tertiary);
    color: var(--text-primary);
    font-family: var(--font-mono);
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-semibold);
    border-radius: var(--radius-sm);
}

.changelog-title-cell {
    display: flex;
    flex-direction: column;
    gap: var(--space-1);
}

.badge-feature {
    background: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
}

.badge-fix {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}

.badge-improvement {
    background: rgba(34, 197, 94, 0.1);
    color: #22c55e;
}

.badge-security {
    background: rgba(249, 115, 22, 0.1);
    color: #f97316;
}

.empty-state {
    text-align: center;
    padding: var(--space-10);
    color: var(--text-muted);
}

.empty-state svg {
    margin-bottom: var(--space-4);
    opacity: 0.5;
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
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 639px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>
