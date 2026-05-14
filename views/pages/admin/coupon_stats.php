<?php
/**
 * Admin Coupon Stats Page
 * Variables: $pageTitle, $currentPage, $coupon, $stats, $usages
 */
?>

<div class="page-header">
    <div class="page-header-content">
        <a href="/admin/coupons" class="back-link">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            Back to Coupons
        </a>
        <h1>Coupon Statistics</h1>
        <p>Usage statistics for coupon <code><?= htmlspecialchars($coupon['code'] ?? '') ?></code></p>
    </div>
    <div class="page-header-actions">
        <a href="/admin/coupons/<?= (int)$coupon['id'] ?>/edit" class="btn btn-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
            Edit Coupon
        </a>
    </div>
</div>

<!-- Coupon Info -->
<div class="card">
    <div class="card-header">
        <h3>Coupon Details</h3>
    </div>
    <div class="card-body">
        <div class="coupon-details-grid">
            <div class="detail-item">
                <span class="detail-label">Code</span>
                <code class="detail-value coupon-code"><?= htmlspecialchars($coupon['code'] ?? '') ?></code>
            </div>
            <div class="detail-item">
                <span class="detail-label">Type</span>
                <span class="detail-value">
                    <?php 
                    $typeLabels = [
                        'percentage' => 'Percentage Discount',
                        'fixed' => 'Fixed Amount Discount',
                        'bonus' => 'Bonus Credits'
                    ];
                    echo $typeLabels[$coupon['type'] ?? ''] ?? 'Unknown';
                    ?>
                </span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Value</span>
                <span class="detail-value">
                    <?php 
                    $type = $coupon['type'] ?? 'percentage';
                    $value = (float)($coupon['value'] ?? 0);
                    if ($type === 'percentage') {
                        echo number_format($value, 0) . '%';
                    } else {
                        echo number_format($value) . ' VND';
                    }
                    ?>
                </span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Status</span>
                <span class="detail-value">
                    <?php if (!empty($coupon['is_active'])): ?>
                    <span class="badge badge-success">Active</span>
                    <?php else: ?>
                    <span class="badge badge-danger">Inactive</span>
                    <?php endif; ?>
                </span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Created</span>
                <span class="detail-value"><?= !empty($coupon['created_at']) ? date('M d, Y H:i', strtotime($coupon['created_at'])) : '-' ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Expires</span>
                <span class="detail-value">
                    <?= !empty($coupon['expires_at']) ? date('M d, Y H:i', strtotime($coupon['expires_at'])) : 'Never' ?>
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="stats-grid mt-4">
    <div class="stat-card">
        <div class="stat-icon bg-primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
        </div>
        <div class="stat-content">
            <span class="stat-label">Total Uses</span>
            <span class="stat-value"><?= number_format((int)($stats['total_uses'] ?? 0)) ?></span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-info">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
        </div>
        <div class="stat-content">
            <span class="stat-label">Unique Users</span>
            <span class="stat-value"><?= number_format((int)($stats['unique_users'] ?? 0)) ?></span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-success">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"></path><path d="M12 18V6"></path></svg>
        </div>
        <div class="stat-content">
            <span class="stat-label">Total Savings</span>
            <span class="stat-value"><?= number_format((float)($stats['total_savings'] ?? 0)) ?> VND</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-warning">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
        </div>
        <div class="stat-content">
            <span class="stat-label">Last Used</span>
            <span class="stat-value" style="font-size: var(--font-size-base);"><?= !empty($stats['last_used']) ? date('M d, Y', strtotime($stats['last_used'])) : 'Never' ?></span>
        </div>
    </div>
</div>

<!-- Usage History -->
<div class="card mt-6">
    <div class="card-header">
        <h3>Usage History</h3>
    </div>
    <div class="card-body">
        <?php if (!empty($usages)): ?>
        <div class="table-responsive">
            <table class="table table-hover admin-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Deposit Ref</th>
                        <th>Amount Saved</th>
                        <th>Used At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usages as $usage): ?>
                    <tr>
                        <td>
                            <div class="user-cell">
                                <strong><?= htmlspecialchars($usage['user_name'] ?? 'Unknown') ?></strong>
                                <small class="text-muted d-block"><?= htmlspecialchars($usage['user_email'] ?? '') ?></small>
                            </div>
                        </td>
                        <td>
                            <?php if (!empty($usage['deposit_ref'])): ?>
                            <code><?= htmlspecialchars($usage['deposit_ref']) ?></code>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="text-success"><?= number_format((float)($usage['amount_saved'] ?? 0)) ?> VND</span>
                        </td>
                        <td><?= !empty($usage['used_at']) ? date('M d, Y H:i', strtotime($usage['used_at'])) : '-' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
            <h3>No usages yet</h3>
            <p>This coupon has not been used by any customers.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.back-link {
    display: inline-flex;
    align-items: center;
    gap: var(--space-1);
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
    margin-bottom: var(--space-2);
    transition: color var(--transition-fast);
}

.back-link:hover {
    color: var(--color-primary);
}

.coupon-details-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: var(--space-4);
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: var(--space-1);
}

.detail-label {
    font-size: var(--font-size-sm);
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.detail-value {
    font-size: var(--font-size-base);
    font-weight: var(--font-weight-semibold);
    color: var(--text-primary);
}

.coupon-code {
    font-family: var(--font-mono);
    font-size: var(--font-size-lg);
    background: var(--bg-tertiary);
    padding: var(--space-1) var(--space-2);
    border-radius: var(--radius-sm);
}

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

.user-cell {
    display: flex;
    flex-direction: column;
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
    margin: 0;
}

@media (max-width: 1023px) {
    .coupon-details-grid, .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 639px) {
    .coupon-details-grid, .stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>
