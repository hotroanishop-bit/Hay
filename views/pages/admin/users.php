<?php
/**
 * Admin Users Page
 * Variables: $pageTitle, $currentPage, $users, $pagination, $ticketCounts
 */
?>

<div class="page-header">
    <div class="page-header-content">
        <h1>User Management</h1>
        <p>Manage all registered users</p>
    </div>
</div>

<div class="stats-grid stats-grid-4">
    <div class="stat-card">
        <div class="stat-icon stat-icon-primary">
            <i class="icon-users"></i>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?= number_format($pagination['total'] ?? 0) ?></span>
            <span class="stat-label">Total Users</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon stat-icon-warning">
            <i class="icon-ticket"></i>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?= number_format($ticketCounts['open'] ?? 0) ?></span>
            <span class="stat-label">Open Tickets</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon stat-icon-info">
            <i class="icon-ticket"></i>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?= number_format($ticketCounts['pending'] ?? 0) ?></span>
            <span class="stat-label">Pending Tickets</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon stat-icon-success">
            <i class="icon-ticket"></i>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?= number_format($ticketCounts['closed'] ?? 0) ?></span>
            <span class="stat-label">Closed Tickets</span>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>All Users</h3>
        <div class="card-actions">
            <form action="/admin/users" method="GET" class="search-form">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search users..." 
                       value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" id="user-search">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="icon-search"></i>
                </button>
            </form>
        </div>
    </div>
    <div class="card-body">
        <?php if (!empty($users)): ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th>2FA</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= (int)($user['id'] ?? 0) ?></td>
                        <td>
                            <strong><?= htmlspecialchars($user['name'] ?? '') ?></strong>
                            <?php if (!empty($user['is_admin'])): ?>
                            <span class="badge badge-primary">Admin</span>
                            <?php endif; ?>
                            <?php if (!empty($user['is_banned'])): ?>
                            <span class="badge badge-danger">Banned</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($user['email'] ?? '') ?></td>
                        <td>$<?= number_format($user['balance'] ?? 0, 2) ?></td>
                        <td>
                            <?php if (!empty($user['is_banned'])): ?>
                            <span class="badge badge-danger">Banned</span>
                            <?php elseif (!empty($user['email_verified_at'])): ?>
                            <span class="badge badge-success">Verified</span>
                            <?php else: ?>
                            <span class="badge badge-warning">Unverified</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($user['two_factor_enabled'])): ?>
                            <span class="badge badge-success">Enabled</span>
                            <?php else: ?>
                            <span class="badge badge-secondary">Disabled</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars(date('M d, Y', strtotime($user['created_at'] ?? 'now'))) ?></td>
                        <td>
                            <div class="btn-group">
                                <a href="/admin/users/<?= (int)$user['id'] ?>" class="btn btn-sm btn-secondary" title="View Details">
                                    <i class="icon-eye"></i>
                                </a>
                                <?php if (empty($user['is_admin'])): ?>
                                    <?php if (!empty($user['is_banned'])): ?>
                                    <form action="/admin/users/<?= (int)$user['id'] ?>/unban" method="POST" class="d-inline" onsubmit="return confirm('Unban this user?');">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                        <button type="submit" class="btn btn-sm btn-success" title="Unban">
                                            <i class="icon-check"></i>
                                        </button>
                                    </form>
                                    <?php else: ?>
                                    <button type="button" class="btn btn-sm btn-danger" title="Ban" onclick="showBanModal(<?= (int)$user['id'] ?>, '<?= htmlspecialchars(addslashes($user['name'] ?? '')) ?>')">
                                        <i class="icon-ban"></i>
                                    </button>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <button type="button" class="btn btn-sm btn-info" title="Edit Balance" onclick="showBalanceModal(<?= (int)$user['id'] ?>, '<?= htmlspecialchars(addslashes($user['name'] ?? '')) ?>', <?= (float)($user['balance'] ?? 0) ?>)">
                                    <i class="icon-dollar-sign"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
        <nav class="pagination-wrapper">
            <ul class="pagination">
                <?php 
                $searchParam = !empty($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '';
                ?>
                <?php if ($pagination['current_page'] > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="/admin/users?page=<?= $pagination['current_page'] - 1 ?><?= $searchParam ?>">Previous</a>
                </li>
                <?php endif; ?>

                <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['total_pages'], $pagination['current_page'] + 2); $i++): ?>
                <li class="page-item <?= $i === $pagination['current_page'] ? 'active' : '' ?>">
                    <a class="page-link" href="/admin/users?page=<?= $i ?><?= $searchParam ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>

                <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                <li class="page-item">
                    <a class="page-link" href="/admin/users?page=<?= $pagination['current_page'] + 1 ?><?= $searchParam ?>">Next</a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>

        <?php else: ?>
        <p class="text-muted">No users found</p>
        <?php endif; ?>
    </div>
</div>

<!-- Ban Modal -->
<div id="banModal" class="modal" style="display: none;">
    <div class="modal-overlay" onclick="hideBanModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h4>Ban User: <span id="banUserName"></span></h4>
            <button type="button" class="modal-close" onclick="hideBanModal()">&times;</button>
        </div>
        <form id="banForm" method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <div class="modal-body">
                <div class="form-group">
                    <label for="banReason">Ban Reason (optional)</label>
                    <textarea name="reason" id="banReason" class="form-control" rows="3" placeholder="Enter reason for banning this user..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="hideBanModal()">Cancel</button>
                <button type="submit" class="btn btn-danger">Ban User</button>
            </div>
        </form>
    </div>
</div>

<!-- Balance Modal -->
<div id="balanceModal" class="modal" style="display: none;">
    <div class="modal-overlay" onclick="hideBalanceModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h4>Edit Balance: <span id="balanceUserName"></span></h4>
            <button type="button" class="modal-close" onclick="hideBalanceModal()">&times;</button>
        </div>
        <form id="balanceForm" method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <div class="modal-body">
                <div class="form-group">
                    <label>Current Balance</label>
                    <p class="form-control-static" id="currentBalance">$0.00</p>
                </div>
                <div class="form-group">
                    <label for="amount">Adjustment Amount</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" name="amount" id="amount" class="form-control" step="0.01" required placeholder="Enter amount (use negative to deduct)">
                    </div>
                    <small class="form-text text-muted">Enter a positive number to add credits, negative to deduct</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="hideBalanceModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Balance</button>
            </div>
        </form>
    </div>
</div>

<script>
function showBanModal(userId, userName) {
    document.getElementById('banForm').action = '/admin/users/' + userId + '/ban';
    document.getElementById('banUserName').textContent = userName;
    document.getElementById('banModal').style.display = 'flex';
}

function hideBanModal() {
    document.getElementById('banModal').style.display = 'none';
}

function showBalanceModal(userId, userName, currentBalance) {
    document.getElementById('balanceForm').action = '/admin/users/' + userId + '/balance';
    document.getElementById('balanceUserName').textContent = userName;
    document.getElementById('currentBalance').textContent = '$' + currentBalance.toFixed(2);
    document.getElementById('amount').value = '';
    document.getElementById('balanceModal').style.display = 'flex';
}

function hideBalanceModal() {
    document.getElementById('balanceModal').style.display = 'none';
}
</script>
