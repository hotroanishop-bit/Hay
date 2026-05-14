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
