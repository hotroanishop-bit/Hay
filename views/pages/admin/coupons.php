<?php
/**
 * Admin Coupons Page
 * Variables: $pageTitle, $currentPage, $coupons, $stats
 */
?>

<div class="page-header">
    <div class="page-header-content">
        <h1>Coupon Management</h1>
        <p>Create and manage promotional coupon codes</p>
    </div>
    <div class="page-header-actions">
        <a href="/admin/coupons/create" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Create Coupon
        </a>
    </div>
</div>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon bg-primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 12V8H6a2 2 0 0 1-2-2c0-1.1.9-2 2-2h12v4"></path><path d="M4 6v12c0 1.1.9 2 2 2h14v-4"></path><path d="M18 12a2 2 0 0 0-2 2c0 1.1.9 2 2 2h4v-4h-4z"></path></svg>
        </div>
        <div class="stat-content">
            <span class="stat-label">Total Coupons</span>
            <span class="stat-value"><?= number_format($stats['total'] ?? 0) ?></span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-success">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
        </div>
        <div class="stat-content">
            <span class="stat-label">Active Coupons</span>
            <span class="stat-value"><?= number_format($stats['active'] ?? 0) ?></span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-info">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
        </div>
        <div class="stat-content">
            <span class="stat-label">Total Uses</span>
            <span class="stat-value"><?= number_format($stats['usages'] ?? 0) ?></span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-warning">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"></path><path d="M12 18V6"></path></svg>
        </div>
        <div class="stat-content">
            <span class="stat-label">Total Savings</span>
            <span class="stat-value"><?= number_format($stats['savings'] ?? 0) ?> VND</span>
        </div>
    </div>
</div>

<!-- Coupons Table -->
<div class="card mt-6">
    <div class="card-header">
        <h3>All Coupons</h3>
    </div>
    <div class="card-body">
        <?php if (!empty($coupons)): ?>
        <div class="table-responsive">
            <table class="table table-hover admin-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Type</th>
                        <th>Value</th>
                        <th>Min Amount</th>
                        <th>Usage</th>
                        <th>Expires</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($coupons as $coupon): ?>
                    <tr>
                        <td>
                            <div class="coupon-code-cell">
                                <code class="coupon-code"><?= htmlspecialchars($coupon['code'] ?? '') ?></code>
                                <?php if (!empty($coupon['description'])): ?>
                                <small class="text-muted d-block"><?= htmlspecialchars(substr($coupon['description'], 0, 50)) ?><?= strlen($coupon['description']) > 50 ? '...' : '' ?></small>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <?php 
                            $typeLabels = [
                                'percentage' => '<span class="badge badge-primary">Percentage</span>',
                                'fixed' => '<span class="badge badge-info">Fixed</span>',
                                'bonus' => '<span class="badge badge-success">Bonus</span>'
                            ];
                            echo $typeLabels[$coupon['type'] ?? ''] ?? '<span class="badge">Unknown</span>';
                            ?>
                        </td>
                        <td>
                            <?php 
                            $type = $coupon['type'] ?? 'percentage';
                            $value = (float)($coupon['value'] ?? 0);
                            if ($type === 'percentage') {
                                echo number_format($value, 0) . '%';
                            } else {
                                echo number_format($value) . ' VND';
                            }
                            ?>
                        </td>
                        <td>
                            <?php if (($coupon['min_amount'] ?? 0) > 0): ?>
                            <?= number_format((float)$coupon['min_amount']) ?> VND
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="usage-count">
                                <?= number_format((int)($coupon['used_count'] ?? 0)) ?>
                                <?php if (($coupon['max_uses'] ?? 0) > 0): ?>
                                / <?= number_format((int)$coupon['max_uses']) ?>
                                <?php endif; ?>
                            </span>
                            <small class="text-muted d-block"><?= number_format((float)($coupon['total_savings'] ?? 0)) ?> VND saved</small>
                        </td>
                        <td>
                            <?php if (!empty($coupon['expires_at'])): ?>
                                <?php 
                                $expiresAt = strtotime($coupon['expires_at']);
                                $isExpired = $expiresAt < time();
                                ?>
                                <span class="<?= $isExpired ? 'text-danger' : '' ?>">
                                    <?= date('M d, Y', $expiresAt) ?>
                                </span>
                                <?php if ($isExpired): ?>
                                <small class="text-danger d-block">Expired</small>
                                <?php endif; ?>
                            <?php else: ?>
                            <span class="text-muted">Never</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($coupon['is_active'])): ?>
                            <span class="badge badge-success">Active</span>
                            <?php else: ?>
                            <span class="badge badge-danger">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="/admin/coupons/<?= (int)$coupon['id'] ?>/stats" class="btn btn-sm btn-secondary" title="View Stats">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                                </a>
                                <a href="/admin/coupons/<?= (int)$coupon['id'] ?>/edit" class="btn btn-sm btn-secondary" title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                </a>
                                <form action="/admin/coupons/<?= (int)$coupon['id'] ?>/toggle" method="POST" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                    <button type="submit" class="btn btn-sm <?= !empty($coupon['is_active']) ? 'btn-warning' : 'btn-success' ?>" title="<?= !empty($coupon['is_active']) ? 'Deactivate' : 'Activate' ?>">
                                        <?php if (!empty($coupon['is_active'])): ?>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                        <?php else: ?>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 9.9-1"></path></svg>
                                        <?php endif; ?>
                                    </button>
                                </form>
                                <form action="/admin/coupons/<?= (int)$coupon['id'] ?>/delete" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to deactivate this coupon?');">
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
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 12V8H6a2 2 0 0 1-2-2c0-1.1.9-2 2-2h12v4"></path><path d="M4 6v12c0 1.1.9 2 2 2h14v-4"></path><path d="M18 12a2 2 0 0 0-2 2c0 1.1.9 2 2 2h4v-4h-4z"></path></svg>
            <h3>No coupons found</h3>
            <p>Create your first coupon to offer discounts to users.</p>
            <a href="/admin/coupons/create" class="btn btn-primary">Create Coupon</a>
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

.coupon-code-cell {
    display: flex;
    flex-direction: column;
    gap: var(--space-1);
}

.coupon-code {
    font-family: var(--font-mono);
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-bold);
    background: var(--bg-tertiary);
    padding: var(--space-1) var(--space-2);
    border-radius: var(--radius-sm);
}

.usage-count {
    font-weight: var(--font-weight-semibold);
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
