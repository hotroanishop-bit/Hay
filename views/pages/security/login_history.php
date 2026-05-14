<?php
/**
 * Login History Page
 * Variables: $pageTitle, $currentPage, $history, $pagination
 */
?>

<div class="page-header page-header-flex">
    <div class="page-header-content">
        <h1 class="page-title">Login History</h1>
        <p class="page-subtitle">View your recent login activity and security events</p>
    </div>
    <div class="page-header-actions">
        <a href="/security/sessions" class="btn btn-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
            Active Sessions
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <?php if (!empty($history)): ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Status</th>
                        <th>Device</th>
                        <th>Browser / OS</th>
                        <th>IP Address</th>
                        <th>Location</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $entry): ?>
                    <?php $deviceInfo = $entry['device_info'] ?? ['device' => 'Unknown', 'browser' => 'Unknown', 'os' => 'Unknown']; ?>
                    <tr>
                        <td>
                            <div class="text-primary"><?= htmlspecialchars(date('M d, Y', strtotime($entry['created_at'] ?? ''))) ?></div>
                            <div class="text-muted text-sm"><?= htmlspecialchars(date('H:i:s', strtotime($entry['created_at'] ?? ''))) ?></div>
                        </td>
                        <td>
                            <?php if ($entry['success'] ?? false): ?>
                            <span class="badge badge-success">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                Success
                            </span>
                            <?php else: ?>
                            <span class="badge badge-danger">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                Failed
                            </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="device-info">
                                <?php if ($deviceInfo['device'] === 'Mobile'): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>
                                <?php elseif ($deviceInfo['device'] === 'Tablet'): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>
                                <?php else: ?>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
                                <?php endif; ?>
                                <span><?= htmlspecialchars($deviceInfo['device']) ?></span>
                            </div>
                        </td>
                        <td>
                            <div><?= htmlspecialchars($deviceInfo['browser']) ?></div>
                            <div class="text-muted text-sm"><?= htmlspecialchars($deviceInfo['os']) ?></div>
                        </td>
                        <td>
                            <code class="code-inline"><?= htmlspecialchars($entry['ip_address'] ?? 'Unknown') ?></code>
                        </td>
                        <td>
                            <?= htmlspecialchars($entry['location'] ?? 'Unknown') ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
        <nav class="pagination-wrapper">
            <ul class="pagination">
                <?php if ($pagination['current_page'] > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="/security/login-history?page=<?= $pagination['current_page'] - 1 ?>">Previous</a>
                </li>
                <?php endif; ?>

                <?php 
                $startPage = max(1, $pagination['current_page'] - 2);
                $endPage = min($pagination['total_pages'], $pagination['current_page'] + 2);
                for ($i = $startPage; $i <= $endPage; $i++): 
                ?>
                <li class="page-item <?= $i === $pagination['current_page'] ? 'active' : '' ?>">
                    <a class="page-link" href="/security/login-history?page=<?= $i ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>

                <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                <li class="page-item">
                    <a class="page-link" href="/security/login-history?page=<?= $pagination['current_page'] + 1 ?>">Next</a>
                </li>
                <?php endif; ?>
            </ul>
            <p class="pagination-info">
                Showing page <?= $pagination['current_page'] ?> of <?= $pagination['total_pages'] ?>
                (<?= number_format($pagination['total']) ?> total entries)
            </p>
        </nav>
        <?php endif; ?>

        <?php else: ?>
        <div class="empty-state">
            <div class="empty-state-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
            </div>
            <h3>No Login History</h3>
            <p>Your login activity will appear here once you have login records.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.device-info {
    display: flex;
    align-items: center;
    gap: var(--space-2);
}

.device-info svg {
    color: var(--text-muted);
}

.text-sm {
    font-size: var(--font-size-sm);
}

.code-inline {
    font-family: var(--font-mono);
    font-size: var(--font-size-xs);
    background: var(--bg-tertiary);
    padding: var(--space-1) var(--space-2);
    border-radius: var(--radius-sm);
}

.badge svg {
    margin-right: var(--space-1);
}

.empty-state {
    padding: var(--space-12);
    text-align: center;
}

.empty-state-icon {
    color: var(--text-muted);
    margin-bottom: var(--space-4);
}

.empty-state h3 {
    margin-bottom: var(--space-2);
}

.empty-state p {
    color: var(--text-secondary);
}

.pagination-wrapper {
    padding: var(--space-4);
    border-top: 1px solid var(--border-color);
}

.pagination {
    display: flex;
    gap: var(--space-1);
    list-style: none;
    padding: 0;
    margin: 0 0 var(--space-2) 0;
}

.page-link {
    padding: var(--space-2) var(--space-3);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    color: var(--text-primary);
    text-decoration: none;
}

.page-link:hover {
    background: var(--bg-tertiary);
}

.page-item.active .page-link {
    background: var(--color-primary);
    border-color: var(--color-primary);
    color: var(--color-white);
}

.pagination-info {
    font-size: var(--font-size-sm);
    color: var(--text-muted);
    margin: 0;
}
</style>
