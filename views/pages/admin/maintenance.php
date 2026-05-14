<?php
/**
 * Admin Scheduled Maintenance Management
 * Variables: $pageTitle, $currentPage, $maintenanceWindows, $pagination
 */
?>

<div class="page-header">
    <div class="page-header-content">
        <h1>Scheduled Maintenance</h1>
        <p>Manage scheduled maintenance windows</p>
    </div>
    <div class="page-header-actions">
        <a href="/admin/maintenance/create" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Schedule Maintenance
        </a>
    </div>
</div>

<?php if (!empty($flash)): ?>
<div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
    <?= htmlspecialchars($flash['message']) ?>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3>Maintenance Windows</h3>
    </div>
    <div class="card-body">
        <?php if (!empty($maintenanceWindows['items'])): ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Starts At</th>
                        <th>Ends At</th>
                        <th>Status</th>
                        <th>Countdown</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $now = time();
                    foreach ($maintenanceWindows['items'] as $window): 
                        $startsAt = strtotime($window['starts_at']);
                        $endsAt = strtotime($window['ends_at']);
                        
                        // Determine status
                        if ($now >= $startsAt && $now <= $endsAt && $window['is_active']) {
                            $status = 'active';
                            $statusLabel = 'Active Now';
                            $statusClass = 'danger';
                        } elseif ($now < $startsAt && $window['is_active']) {
                            $status = 'upcoming';
                            $statusLabel = 'Upcoming';
                            $statusClass = 'warning';
                        } elseif ($now > $endsAt) {
                            $status = 'past';
                            $statusLabel = 'Completed';
                            $statusClass = 'secondary';
                        } else {
                            $status = 'disabled';
                            $statusLabel = 'Disabled';
                            $statusClass = 'secondary';
                        }
                    ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($window['title']) ?></strong>
                            <br>
                            <small class="text-muted"><?= htmlspecialchars(substr($window['message'], 0, 50)) ?>...</small>
                        </td>
                        <td><?= htmlspecialchars(date('M d, Y H:i', $startsAt)) ?></td>
                        <td><?= htmlspecialchars(date('M d, Y H:i', $endsAt)) ?></td>
                        <td>
                            <span class="badge badge-<?= $statusClass ?>">
                                <?= $statusLabel ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($window['show_countdown']): ?>
                                <span class="badge badge-info">Enabled</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Disabled</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="/admin/maintenance/<?= $window['id'] ?>/edit" class="btn btn-sm btn-secondary">
                                    Edit
                                </a>
                                <form action="/admin/maintenance/<?= $window['id'] ?>/toggle" method="POST" style="display: inline;">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                    <button type="submit" class="btn btn-sm btn-<?= $window['is_active'] ? 'warning' : 'success' ?>">
                                        <?= $window['is_active'] ? 'Disable' : 'Enable' ?>
                                    </button>
                                </form>
                                <form action="/admin/maintenance/<?= $window['id'] ?>/delete" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this maintenance window?')">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($maintenanceWindows['total_pages'] > 1): ?>
        <div class="pagination-wrapper">
            <nav class="pagination">
                <?php for ($i = 1; $i <= $maintenanceWindows['total_pages']; $i++): ?>
                <a href="/admin/maintenance?page=<?= $i ?>" class="pagination-item <?= $i == $maintenanceWindows['page'] ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
                <?php endfor; ?>
            </nav>
        </div>
        <?php endif; ?>
        
        <?php else: ?>
        <div class="empty-state">
            <div class="empty-state-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
                </svg>
            </div>
            <h3>No Maintenance Windows</h3>
            <p>You haven't scheduled any maintenance windows yet.</p>
            <a href="/admin/maintenance/create" class="btn btn-primary">Schedule Maintenance</a>
        </div>
        <?php endif; ?>
    </div>
</div>
