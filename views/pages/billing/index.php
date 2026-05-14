<?php
/**
 * Billing Index Page
 * Variables: $pageTitle, $currentPage, $balance, $paymentMethods, $recentTransactions, $totalCredits, $totalDebits
 */
?>

<div class="page-header">
    <div class="page-header-content">
        <h1>Billing</h1>
        <p>Manage your account balance and payments</p>
    </div>
    <div class="page-header-actions">
        <a href="/billing/add-credits" class="btn btn-primary">
            <i class="icon-plus"></i> Add Credits
        </a>
    </div>
</div>

<div class="stats-grid stats-grid-3">
    <div class="stat-card stat-card-large">
        <div class="stat-icon stat-icon-primary">
            <i class="icon-dollar"></i>
        </div>
        <div class="stat-content">
            <span class="stat-value">$<?= number_format($balance ?? 0, 2) ?></span>
            <span class="stat-label">Current Balance</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon stat-icon-success">
            <i class="icon-arrow-up"></i>
        </div>
        <div class="stat-content">
            <span class="stat-value">$<?= number_format($totalCredits ?? 0, 2) ?></span>
            <span class="stat-label">Total Deposited</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon stat-icon-warning">
            <i class="icon-arrow-down"></i>
        </div>
        <div class="stat-content">
            <span class="stat-value">$<?= number_format($totalDebits ?? 0, 2) ?></span>
            <span class="stat-label">Total Spent</span>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3>Recent Transactions</h3>
                <a href="/billing/history" class="btn btn-sm btn-link">View All</a>
            </div>
            <div class="card-body">
                <?php if (!empty($recentTransactions)): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentTransactions as $transaction): ?>
                            <tr>
                                <td><?= htmlspecialchars(date('M d, Y H:i', strtotime($transaction['created_at'] ?? ''))) ?></td>
                                <td>
                                    <span class="badge badge-<?= ($transaction['type'] ?? '') === 'credit' ? 'success' : 'warning' ?>">
                                        <?= htmlspecialchars(ucfirst($transaction['type'] ?? '')) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($transaction['description'] ?? '') ?></td>
                                <td class="<?= ($transaction['type'] ?? '') === 'credit' ? 'text-success' : 'text-danger' ?>">
                                    <?= ($transaction['type'] ?? '') === 'credit' ? '+' : '-' ?>$<?= number_format($transaction['amount'] ?? 0, 2) ?>
                                </td>
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
                <?php else: ?>
                <p class="text-muted">No transactions yet</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3>Add Credits</h3>
            </div>
            <div class="card-body">
                <form action="/billing/pay" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                    <div class="form-group">
                        <label for="amount">Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" id="amount" name="amount" class="form-control" min="5" max="1000" step="0.01" placeholder="10.00" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Quick Select</label>
                        <div class="btn-group-amounts">
                            <button type="button" class="btn btn-outline-secondary" onclick="setAmount(10)">$10</button>
                            <button type="button" class="btn btn-outline-secondary" onclick="setAmount(25)">$25</button>
                            <button type="button" class="btn btn-outline-secondary" onclick="setAmount(50)">$50</button>
                            <button type="button" class="btn btn-outline-secondary" onclick="setAmount(100)">$100</button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="payment_method">Payment Method</label>
                        <select id="payment_method" name="payment_method" class="form-control" required>
                            <?php foreach ($paymentMethods ?? [] as $method): ?>
                            <option value="<?= htmlspecialchars($method['id'] ?? '') ?>">
                                <?= htmlspecialchars($method['name'] ?? '') ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="icon-credit-card"></i> Pay Now
                    </button>
                </form>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h3>Need Help?</h3>
            </div>
            <div class="card-body">
                <p class="text-muted">If you have any billing questions or issues, please contact our support team.</p>
                <a href="/support" class="btn btn-secondary btn-block">Contact Support</a>
            </div>
        </div>
    </div>
</div>
