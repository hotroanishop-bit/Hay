<?php
/**
 * Transaction History Page
 * Variables: $pageTitle, $currentPage, $transactions, $pagination
 */
?>

<div class="page-header">
    <div class="page-header-content">
        <h1>Transaction History</h1>
        <p>View all your past transactions</p>
    </div>
    <div class="page-header-actions">
        <a href="/export/transactions" class="btn btn-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" x2="12" y1="15" y2="3"></line></svg>
            Export CSV
        </a>
        <a href="/billing" class="btn btn-secondary">
            <i class="icon-arrow-left"></i> Back to Billing
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (!empty($transactions)): ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Description</th>
                        <th>Reference</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $transaction): ?>
                    <tr>
                        <td><?= htmlspecialchars(date('M d, Y H:i', strtotime($transaction['created_at'] ?? ''))) ?></td>
                        <td>
                            <span class="badge badge-<?= ($transaction['type'] ?? '') === 'credit' ? 'success' : 'warning' ?>">
                                <?= htmlspecialchars(ucfirst($transaction['type'] ?? '')) ?>
                            </span>
                        </td>
                        <td class="<?= ($transaction['type'] ?? '') === 'credit' ? 'text-success' : 'text-danger' ?>">
                            <?= ($transaction['type'] ?? '') === 'credit' ? '+' : '-' ?>$<?= number_format($transaction['amount'] ?? 0, 2) ?>
                        </td>
                        <td><?= htmlspecialchars($transaction['description'] ?? '') ?></td>
                        <td><code><?= htmlspecialchars($transaction['reference_id'] ?? '-') ?></code></td>
                        <td>
                            <span class="badge badge-<?= ($transaction['status'] ?? '') === 'completed' ? 'success' : (($transaction['status'] ?? '') === 'pending' ? 'warning' : 'danger') ?>">
                                <?= htmlspecialchars(ucfirst($transaction['status'] ?? '')) ?>
                            </span>
                        </td>
                        <td>
                            <?php if (($transaction['status'] ?? '') === 'completed'): ?>
                            <a href="/invoice/purchase/<?= (int)$transaction['id'] ?>" class="btn btn-sm btn-secondary" title="View Receipt" target="_blank">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                    <line x1="16" y1="13" x2="8" y2="13"></line>
                                    <line x1="16" y1="17" x2="8" y2="17"></line>
                                </svg>
                                <span class="hide-mobile">Receipt</span>
                            </a>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
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
                    <a class="page-link" href="/billing/history?page=<?= $pagination['current_page'] - 1 ?>">Previous</a>
                </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                <li class="page-item <?= $i === $pagination['current_page'] ? 'active' : '' ?>">
                    <a class="page-link" href="/billing/history?page=<?= $i ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>

                <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                <li class="page-item">
                    <a class="page-link" href="/billing/history?page=<?= $pagination['current_page'] + 1 ?>">Next</a>
                </li>
                <?php endif; ?>
            </ul>
            <p class="pagination-info">
                Showing page <?= $pagination['current_page'] ?> of <?= $pagination['total_pages'] ?>
                (<?= $pagination['total'] ?> total transactions)
            </p>
        </nav>
        <?php endif; ?>

        <?php else: ?>
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="icon-history"></i>
            </div>
            <h3>No Transactions Yet</h3>
            <p>Your transaction history will appear here once you make a payment or use API credits.</p>
            <a href="/billing/add-credits" class="btn btn-primary">Add Credits</a>
        </div>
        <?php endif; ?>
    </div>
</div>
