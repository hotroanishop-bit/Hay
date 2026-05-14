<?php
/**
 * Pending Deposits Page
 * Variables: $pageTitle, $currentPage, $deposits
 */
?>

<div class="page-header">
    <div class="page-header-content">
        <h1>Pending Deposits</h1>
        <p>View and manage your pending deposit requests</p>
    </div>
    <div class="page-header-actions">
        <a href="/billing/deposit" class="btn btn-primary">
            <i class="icon-plus"></i> New Deposit
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Your Pending Deposits</h3>
    </div>
    <div class="card-body">
        <?php if (!empty($deposits)): ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($deposits as $deposit): ?>
                    <tr>
                        <td>
                            <code class="reference-code"><?= htmlspecialchars($deposit['reference_code'] ?? '') ?></code>
                        </td>
                        <td>
                            <span class="amount"><?= number_format($deposit['amount'] ?? 0) ?> VND</span>
                        </td>
                        <td>
                            <span class="badge badge-<?= getDepositStatusClass($deposit['status'] ?? 'pending') ?>">
                                <?= htmlspecialchars(ucfirst($deposit['status'] ?? 'pending')) ?>
                            </span>
                        </td>
                        <td>
                            <?= htmlspecialchars(date('M d, Y H:i', strtotime($deposit['created_at'] ?? ''))) ?>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="/billing/deposit/<?= htmlspecialchars($deposit['id'] ?? '') ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="icon-eye"></i> View
                                </a>
                                <?php if (($deposit['status'] ?? '') === 'pending'): ?>
                                <form action="/billing/deposit/<?= htmlspecialchars($deposit['id'] ?? '') ?>/cancel" method="POST" class="d-inline" onsubmit="return confirm('Cancel this deposit?');">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="icon-x"></i> Cancel
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="icon-inbox"></i>
            </div>
            <h4>No Pending Deposits</h4>
            <p class="text-muted">You don't have any pending deposits at the moment.</p>
            <a href="/billing/deposit" class="btn btn-primary">
                <i class="icon-plus"></i> Create New Deposit
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <h3>Quick Links</h3>
    </div>
    <div class="card-body">
        <div class="quick-links">
            <a href="/billing" class="quick-link">
                <i class="icon-dollar"></i>
                <span>Billing Overview</span>
            </a>
            <a href="/billing/history" class="quick-link">
                <i class="icon-list"></i>
                <span>Transaction History</span>
            </a>
            <a href="/tickets/create" class="quick-link">
                <i class="icon-help-circle"></i>
                <span>Contact Support</span>
            </a>
        </div>
    </div>
</div>

<?php
function getDepositStatusClass(string $status): string {
    return match($status) {
        'pending' => 'warning',
        'approved' => 'success',
        'rejected' => 'danger',
        'expired' => 'secondary',
        default => 'secondary'
    };
}
?>

<style>
.reference-code {
    background: #f8f9fa;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.9rem;
}

.amount {
    font-weight: 600;
    color: #28a745;
}

.btn-group {
    display: flex;
    gap: 5px;
}

.d-inline {
    display: inline;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
}

.empty-state-icon {
    font-size: 4rem;
    color: #dee2e6;
    margin-bottom: 20px;
}

.empty-state h4 {
    margin-bottom: 10px;
}

.empty-state p {
    margin-bottom: 20px;
}

.quick-links {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

.quick-link {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 15px 20px;
    background: #f8f9fa;
    border-radius: 8px;
    text-decoration: none;
    color: inherit;
    transition: all 0.2s;
}

.quick-link:hover {
    background: #e9ecef;
    text-decoration: none;
}

.quick-link i {
    font-size: 1.5rem;
    color: var(--primary-color, #007bff);
}
</style>
