<?php
/**
 * Admin Deposits Page - Enhanced
 * Modern deposit management with status tabs and quick actions
 * 
 * Variables: $pageTitle, $currentPage, $deposits, $statusFilter, $statusCounts, $pagination
 */
?>

<div class="page-header">
    <div class="page-header-content">
        <div>
            <h1>Deposit Management</h1>
            <p class="text-muted">Review and process user deposit requests</p>
        </div>
        <div class="page-header-stats">
            <?php if (($statusCounts['pending'] ?? 0) > 0): ?>
            <div class="header-stat header-stat-warning">
                <i class="icon-clock"></i>
                <span><?= number_format($statusCounts['pending']) ?> pending</span>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Status Tabs -->
<div class="status-tabs-wrapper">
    <div class="status-tabs">
        <a href="/admin/deposits" class="status-tab <?= empty($statusFilter) ? 'active' : '' ?>">
            <span class="tab-label">All</span>
            <span class="tab-count"><?= number_format($statusCounts['all'] ?? 0) ?></span>
        </a>
        <a href="/admin/deposits?status=pending" class="status-tab status-tab-warning <?= $statusFilter === 'pending' ? 'active' : '' ?>">
            <span class="tab-label">Pending</span>
            <span class="tab-count"><?= number_format($statusCounts['pending'] ?? 0) ?></span>
        </a>
        <a href="/admin/deposits?status=approved" class="status-tab status-tab-success <?= $statusFilter === 'approved' ? 'active' : '' ?>">
            <span class="tab-label">Approved</span>
            <span class="tab-count"><?= number_format($statusCounts['approved'] ?? 0) ?></span>
        </a>
        <a href="/admin/deposits?status=rejected" class="status-tab status-tab-danger <?= $statusFilter === 'rejected' ? 'active' : '' ?>">
            <span class="tab-label">Rejected</span>
            <span class="tab-count"><?= number_format($statusCounts['rejected'] ?? 0) ?></span>
        </a>
        <a href="/admin/deposits?status=expired" class="status-tab status-tab-gray <?= $statusFilter === 'expired' ? 'active' : '' ?>">
            <span class="tab-label">Expired</span>
            <span class="tab-count"><?= number_format($statusCounts['expired'] ?? 0) ?></span>
        </a>
    </div>
    
    <div class="tab-actions">
        <form class="date-filter-form" method="GET" action="/admin/deposits">
            <?php if ($statusFilter): ?>
            <input type="hidden" name="status" value="<?= htmlspecialchars($statusFilter) ?>">
            <?php endif; ?>
            <input type="date" name="date_from" class="form-input form-input-sm" 
                   value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>" placeholder="From">
            <input type="date" name="date_to" class="form-input form-input-sm" 
                   value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>" placeholder="To">
            <button type="submit" class="btn btn-sm btn-secondary">Filter</button>
        </form>
    </div>
</div>

<!-- Deposits List -->
<div class="deposits-container">
    <?php if (!empty($deposits)): ?>
    
    <div class="deposits-grid">
        <?php foreach ($deposits as $deposit): ?>
        <?php
        $statusClass = 'secondary';
        $statusIcon = 'icon-clock';
        switch ($deposit['status'] ?? '') {
            case 'pending': 
                $statusClass = 'warning'; 
                $statusIcon = 'icon-clock';
                break;
            case 'approved': 
                $statusClass = 'success'; 
                $statusIcon = 'icon-check-circle';
                break;
            case 'rejected': 
                $statusClass = 'danger'; 
                $statusIcon = 'icon-x-circle';
                break;
            case 'expired': 
                $statusClass = 'gray'; 
                $statusIcon = 'icon-alert-circle';
                break;
        }
        ?>
        <div class="deposit-card deposit-card-<?= $statusClass ?>">
            <div class="deposit-card-header">
                <div class="deposit-id">
                    <span class="deposit-id-label">#<?= (int)($deposit['id'] ?? 0) ?></span>
                    <span class="deposit-status badge badge-<?= $statusClass ?>">
                        <i class="<?= $statusIcon ?>"></i>
                        <?= ucfirst(htmlspecialchars($deposit['status'] ?? 'unknown')) ?>
                    </span>
                </div>
                <div class="deposit-amount">
                    <?= number_format($deposit['amount'] ?? 0, 0, ',', '.') ?> VND
                </div>
            </div>
            
            <div class="deposit-card-body">
                <div class="deposit-user">
                    <div class="user-avatar-sm">
                        <?= strtoupper(substr($deposit['user_name'] ?? 'U', 0, 1)) ?>
                    </div>
                    <div class="user-details">
                        <div class="user-name"><?= htmlspecialchars($deposit['user_name'] ?? 'Unknown') ?></div>
                        <div class="user-email"><?= htmlspecialchars($deposit['user_email'] ?? '') ?></div>
                    </div>
                </div>
                
                <div class="deposit-info">
                    <div class="info-row">
                        <span class="info-label">Reference:</span>
                        <code class="info-value"><?= htmlspecialchars($deposit['reference_code'] ?? '') ?></code>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Created:</span>
                        <span class="info-value"><?= date('M d, Y H:i', strtotime($deposit['created_at'] ?? 'now')) ?></span>
                    </div>
                    <?php if (!empty($deposit['processed_at'])): ?>
                    <div class="info-row">
                        <span class="info-label">Processed:</span>
                        <span class="info-value"><?= date('M d, Y H:i', strtotime($deposit['processed_at'])) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="deposit-card-footer">
                <a href="/admin/deposits/<?= (int)$deposit['id'] ?>" class="btn btn-sm btn-secondary">
                    <i class="icon-eye"></i> View Details
                </a>
                
                <?php if (($deposit['status'] ?? '') === 'pending'): ?>
                <div class="action-buttons">
                    <form action="/admin/deposits/<?= (int)$deposit['id'] ?>/approve" method="POST" class="d-inline" 
                          onsubmit="return confirm('Approve this deposit of <?= number_format($deposit['amount'] ?? 0, 0, ',', '.') ?> VND?');">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <button type="submit" class="btn btn-sm btn-success">
                            <i class="icon-check"></i> Approve
                        </button>
                    </form>
                    <button type="button" class="btn btn-sm btn-danger" 
                            onclick="showRejectModal(<?= (int)$deposit['id'] ?>, '<?= number_format($deposit['amount'] ?? 0, 0, ',', '.') ?>')">
                        <i class="icon-x"></i> Reject
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
    <div class="pagination-wrapper">
        <div class="pagination-info">
            Showing <?= (($pagination['current_page'] - 1) * $pagination['per_page']) + 1 ?> 
            to <?= min($pagination['current_page'] * $pagination['per_page'], $pagination['total']) ?> 
            of <?= number_format($pagination['total']) ?> deposits
        </div>
        <nav class="pagination-nav">
            <ul class="pagination">
                <?php 
                $queryParams = $statusFilter ? '&status=' . urlencode($statusFilter) : '';
                ?>
                <?php if ($pagination['current_page'] > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="/admin/deposits?page=<?= $pagination['current_page'] - 1 ?><?= $queryParams ?>">
                        <i class="icon-chevron-left"></i>
                    </a>
                </li>
                <?php endif; ?>

                <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['total_pages'], $pagination['current_page'] + 2); $i++): ?>
                <li class="page-item <?= $i === $pagination['current_page'] ? 'active' : '' ?>">
                    <a class="page-link" href="/admin/deposits?page=<?= $i ?><?= $queryParams ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>

                <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                <li class="page-item">
                    <a class="page-link" href="/admin/deposits?page=<?= $pagination['current_page'] + 1 ?><?= $queryParams ?>">
                        <i class="icon-chevron-right"></i>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <div class="empty-state">
        <div class="empty-state-icon">
            <i class="icon-inbox"></i>
        </div>
        <h3>No deposits found</h3>
        <p>There are no deposits matching your current filter.</p>
        <?php if ($statusFilter): ?>
        <a href="/admin/deposits" class="btn btn-primary">View All Deposits</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="modal-overlay" style="display: none;">
    <div class="modal">
        <div class="modal-header">
            <h4 class="modal-title">Reject Deposit</h4>
            <button type="button" class="modal-close" onclick="hideRejectModal()">&times;</button>
        </div>
        <form id="rejectForm" method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <div class="modal-body">
                <div class="reject-amount-display">
                    <span class="label">Rejecting deposit of</span>
                    <span class="amount" id="rejectAmount">0 VND</span>
                </div>
                <div class="form-group">
                    <label class="form-label" for="reason">Rejection Reason (optional)</label>
                    <textarea name="reason" id="reason" class="form-input form-textarea" rows="3" 
                              placeholder="Enter reason for rejection..."></textarea>
                    <small class="form-help">This will be logged for audit purposes.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="hideRejectModal()">Cancel</button>
                <button type="submit" class="btn btn-danger">
                    <i class="icon-x"></i> Reject Deposit
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.page-header-stats {
    display: flex;
    gap: var(--spacing-3);
}

.header-stat {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    padding: var(--spacing-2) var(--spacing-3);
    border-radius: var(--radius-full);
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-medium);
}

.header-stat-warning {
    background: rgba(245, 158, 11, 0.1);
    color: var(--color-warning);
}

.status-tabs-wrapper {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-6);
    flex-wrap: wrap;
    gap: var(--spacing-3);
}

.status-tabs {
    display: flex;
    gap: var(--spacing-2);
    overflow-x: auto;
    padding-bottom: var(--spacing-1);
}

.status-tab {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    padding: var(--spacing-2) var(--spacing-4);
    background: var(--color-white);
    border: 1px solid var(--color-gray-200);
    border-radius: var(--radius-lg);
    text-decoration: none;
    color: var(--color-gray-600);
    font-size: var(--font-size-sm);
    transition: all var(--transition-fast);
    white-space: nowrap;
}

.status-tab:hover {
    border-color: var(--color-gray-300);
    background: var(--color-gray-50);
}

.status-tab.active {
    border-color: var(--color-primary);
    background: rgba(79, 70, 229, 0.05);
    color: var(--color-primary);
}

.status-tab-warning.active {
    border-color: var(--color-warning);
    background: rgba(245, 158, 11, 0.05);
    color: #b45309;
}

.status-tab-success.active {
    border-color: var(--color-success);
    background: rgba(34, 197, 94, 0.05);
    color: #059669;
}

.status-tab-danger.active {
    border-color: var(--color-error);
    background: rgba(239, 68, 68, 0.05);
    color: #dc2626;
}

.status-tab-gray.active {
    border-color: var(--color-gray-400);
    background: var(--color-gray-50);
    color: var(--color-gray-600);
}

.tab-label {
    font-weight: var(--font-weight-medium);
}

.tab-count {
    font-size: var(--font-size-xs);
    padding: 2px 8px;
    background: var(--color-gray-100);
    border-radius: var(--radius-full);
}

.status-tab.active .tab-count {
    background: currentColor;
    color: var(--color-white);
}

.tab-actions {
    display: flex;
    gap: var(--spacing-2);
}

.date-filter-form {
    display: flex;
    gap: var(--spacing-2);
    align-items: center;
}

.form-input-sm {
    padding: var(--spacing-1) var(--spacing-2);
    font-size: var(--font-size-sm);
    max-width: 140px;
}

.deposits-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: var(--spacing-4);
}

.deposit-card {
    background: var(--color-white);
    border: 1px solid var(--color-gray-200);
    border-radius: var(--radius-lg);
    overflow: hidden;
    transition: all var(--transition-fast);
}

.deposit-card:hover {
    box-shadow: var(--shadow-md);
}

.deposit-card-warning {
    border-left: 4px solid var(--color-warning);
}

.deposit-card-success {
    border-left: 4px solid var(--color-success);
}

.deposit-card-danger {
    border-left: 4px solid var(--color-error);
}

.deposit-card-gray {
    border-left: 4px solid var(--color-gray-400);
}

.deposit-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: var(--spacing-4);
    background: var(--color-gray-50);
    border-bottom: 1px solid var(--color-gray-100);
}

.deposit-id {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
}

.deposit-id-label {
    font-size: var(--font-size-sm);
    color: var(--color-gray-500);
}

.deposit-amount {
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-bold);
    color: var(--color-gray-900);
}

.deposit-card-body {
    padding: var(--spacing-4);
}

.deposit-user {
    display: flex;
    align-items: center;
    gap: var(--spacing-3);
    margin-bottom: var(--spacing-4);
    padding-bottom: var(--spacing-4);
    border-bottom: 1px solid var(--color-gray-100);
}

.user-avatar-sm {
    width: 36px;
    height: 36px;
    border-radius: var(--radius-full);
    background: var(--color-primary);
    color: var(--color-white);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: var(--font-weight-semibold);
    font-size: var(--font-size-sm);
}

.user-details .user-name {
    font-weight: var(--font-weight-medium);
    color: var(--color-gray-900);
}

.user-details .user-email {
    font-size: var(--font-size-sm);
    color: var(--color-gray-500);
}

.deposit-info {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-2);
}

.info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: var(--font-size-sm);
}

.info-label {
    color: var(--color-gray-500);
}

.info-value {
    color: var(--color-gray-700);
}

.info-value code {
    background: var(--color-gray-100);
    padding: 2px 6px;
    border-radius: var(--radius-sm);
    font-size: var(--font-size-xs);
}

.deposit-card-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--spacing-3) var(--spacing-4);
    background: var(--color-gray-50);
    border-top: 1px solid var(--color-gray-100);
}

.deposit-card-footer .action-buttons {
    display: flex;
    gap: var(--spacing-2);
}

.pagination-wrapper {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: var(--spacing-6);
    padding: var(--spacing-4);
    background: var(--color-white);
    border-radius: var(--radius-lg);
    border: 1px solid var(--color-gray-200);
}

.pagination-info {
    font-size: var(--font-size-sm);
    color: var(--color-gray-500);
}

.reject-amount-display {
    text-align: center;
    padding: var(--spacing-4);
    background: var(--color-gray-50);
    border-radius: var(--radius-md);
    margin-bottom: var(--spacing-4);
}

.reject-amount-display .label {
    display: block;
    font-size: var(--font-size-sm);
    color: var(--color-gray-500);
    margin-bottom: var(--spacing-1);
}

.reject-amount-display .amount {
    display: block;
    font-size: var(--font-size-xl);
    font-weight: var(--font-weight-bold);
    color: var(--color-error);
}

@media (max-width: 768px) {
    .status-tabs-wrapper {
        flex-direction: column;
        align-items: stretch;
    }
    
    .date-filter-form {
        flex-wrap: wrap;
    }
    
    .deposits-grid {
        grid-template-columns: 1fr;
    }
    
    .deposit-card-footer {
        flex-direction: column;
        gap: var(--spacing-2);
    }
    
    .deposit-card-footer .action-buttons {
        width: 100%;
        justify-content: flex-end;
    }
}
</style>

<script>
function showRejectModal(depositId, amount) {
    document.getElementById('rejectForm').action = '/admin/deposits/' + depositId + '/reject';
    document.getElementById('rejectAmount').textContent = amount + ' VND';
    document.getElementById('rejectModal').style.display = 'flex';
}

function hideRejectModal() {
    document.getElementById('rejectModal').style.display = 'none';
}

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        hideRejectModal();
    }
});

// Close modal when clicking overlay
document.getElementById('rejectModal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideRejectModal();
    }
});
</script>
