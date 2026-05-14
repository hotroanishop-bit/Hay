<?php
/**
 * Admin Transactions Page
 * Variables: $pageTitle, $currentPage, $transactions, $pagination
 */
?>

<div class="page-header">
    <div class="page-header-content">
        <h1>All Transactions</h1>
        <p>View and manage all system transactions</p>
    </div>
    <div class="page-header-actions">
        <a href="/admin/transactions/export" class="btn btn-secondary">
            <i class="icon-download"></i> Export CSV
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form action="/admin/transactions" method="GET" class="form-inline">
            <div class="form-group mr-3">
                <label for="type" class="mr-2">Type:</label>
                <select id="type" name="type" class="form-control">
                    <option value="">All Types</option>
                    <option value="credit" <?= ($_GET['type'] ?? '') === 'credit' ? 'selected' : '' ?>>Credit</option>
                    <option value="debit" <?= ($_GET['type'] ?? '') === 'debit' ? 'selected' : '' ?>>Debit</option>
                </select>
            </div>
            <div class="form-group mr-3">
                <label for="status" class="mr-2">Status:</label>
                <select id="status" name="status" class="form-control">
                    <option value="">All Statuses</option>
                    <option value="completed" <?= ($_GET['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                    <option value="pending" <?= ($_GET['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="failed" <?= ($_GET['status'] ?? '') === 'failed' ? 'selected' : '' ?>>Failed</option>
                </select>
            </div>
            <div class="form-group mr-3">
                <label for="user_id" class="mr-2">User ID:</label>
                <input type="number" id="user_id" name="user_id" class="form-control" placeholder="User ID" value="<?= htmlspecialchars($_GET['user_id'] ?? '') ?>">
            </div>
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="/admin/transactions" class="btn btn-secondary ml-2">Reset</a>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (!empty($transactions)): ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Description</th>
                        <th>Reference</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $transaction): ?>
                    <tr>
                        <td><?= (int)($transaction['id'] ?? 0) ?></td>
                        <td>
                            <a href="/admin/users/<?= (int)($transaction['user_id'] ?? 0) ?>">
                                User #<?= (int)($transaction['user_id'] ?? 0) ?>
                            </a>
                        </td>
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
                        <td><?= htmlspecialchars(date('M d, Y H:i', strtotime($transaction['created_at'] ?? 'now'))) ?></td>
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
                    <a class="page-link" href="/admin/transactions?page=<?= $pagination['current_page'] - 1 ?>">Previous</a>
                </li>
                <?php endif; ?>

                <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['total_pages'], $pagination['current_page'] + 2); $i++): ?>
                <li class="page-item <?= $i === $pagination['current_page'] ? 'active' : '' ?>">
                    <a class="page-link" href="/admin/transactions?page=<?= $i ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>

                <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                <li class="page-item">
                    <a class="page-link" href="/admin/transactions?page=<?= $pagination['current_page'] + 1 ?>">Next</a>
                </li>
                <?php endif; ?>
            </ul>
            <p class="pagination-info">
                Showing page <?= $pagination['current_page'] ?> of <?= $pagination['total_pages'] ?>
                (<?= number_format($pagination['total']) ?> total transactions)
            </p>
        </nav>
        <?php endif; ?>

        <?php else: ?>
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="icon-history"></i>
            </div>
            <h3>No Transactions Found</h3>
            <p>No transactions match your current filters.</p>
        </div>
        <?php endif; ?>
    </div>
</div>
