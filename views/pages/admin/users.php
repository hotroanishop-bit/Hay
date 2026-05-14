<?php
/**
 * Admin Users Page - Enhanced
 * Modern user management with cards and quick actions
 * 
 * Variables: $pageTitle, $currentPage, $users, $pagination, $ticketCounts
 */
?>

<div class="page-header">
    <div class="page-header-content">
        <div>
            <h1>User Management</h1>
            <p class="text-muted">Manage all registered users on your platform</p>
        </div>
        <div class="page-header-actions">
            <button type="button" class="btn btn-outline btn-sm" onclick="exportUsers()">
                <i class="icon-download"></i> Export CSV
            </button>
        </div>
    </div>
</div>

<!-- Quick Stats -->
<div class="stats-grid stats-grid-4 mb-4">
    <div class="stat-card stat-card-compact">
        <div class="stat-icon stat-icon-primary">
            <i class="icon-users"></i>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?= number_format($pagination['total'] ?? 0) ?></span>
            <span class="stat-label">Total Users</span>
        </div>
    </div>

    <div class="stat-card stat-card-compact stat-card-clickable" onclick="window.location.href='/admin/tickets?status=open'">
        <div class="stat-icon stat-icon-warning">
            <i class="icon-message-square"></i>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?= number_format($ticketCounts['open'] ?? 0) ?></span>
            <span class="stat-label">Open Tickets</span>
        </div>
    </div>

    <div class="stat-card stat-card-compact">
        <div class="stat-icon stat-icon-info">
            <i class="icon-clock"></i>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?= number_format($ticketCounts['pending'] ?? 0) ?></span>
            <span class="stat-label">Pending Tickets</span>
        </div>
    </div>

    <div class="stat-card stat-card-compact">
        <div class="stat-icon stat-icon-success">
            <i class="icon-check-circle"></i>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?= number_format($ticketCounts['closed'] ?? 0) ?></span>
            <span class="stat-label">Closed Tickets</span>
        </div>
    </div>
</div>

<!-- Filter Tabs -->
<div class="filter-toolbar">
    <div class="filter-tabs">
        <a href="/admin/users" class="filter-tab <?= empty($_GET['status']) ? 'active' : '' ?>">All Users</a>
        <a href="/admin/users?status=active" class="filter-tab <?= ($_GET['status'] ?? '') === 'active' ? 'active' : '' ?>">Active</a>
        <a href="/admin/users?status=banned" class="filter-tab <?= ($_GET['status'] ?? '') === 'banned' ? 'active' : '' ?>">Banned</a>
        <a href="/admin/users?status=unverified" class="filter-tab <?= ($_GET['status'] ?? '') === 'unverified' ? 'active' : '' ?>">Unverified</a>
    </div>
    <div class="filter-actions">
        <form action="/admin/users" method="GET" class="search-form">
            <?php if (!empty($_GET['status'])): ?>
            <input type="hidden" name="status" value="<?= htmlspecialchars($_GET['status']) ?>">
            <?php endif; ?>
            <div class="search-input-wrapper">
                <i class="icon-search"></i>
                <input type="text" name="search" class="form-input search-input" placeholder="Search by name or email..." 
                       value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            </div>
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
    </div>
</div>

<!-- Users List -->
<div class="card">
    <div class="card-body p-0">
        <?php if (!empty($users)): ?>
        <div class="users-table-wrapper">
            <table class="table table-hover users-table">
                <thead>
                    <tr>
                        <th class="th-user">User</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th>2FA</th>
                        <th>Joined</th>
                        <th class="th-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr class="user-row <?= !empty($user['is_banned']) ? 'user-banned' : '' ?>">
                        <td class="td-user">
                            <div class="user-cell">
                                <div class="user-avatar">
                                    <?php if (!empty($user['avatar_url'])): ?>
                                    <img src="<?= htmlspecialchars($user['avatar_url']) ?>" alt="<?= htmlspecialchars($user['name']) ?>">
                                    <?php else: ?>
                                    <span class="avatar-initials"><?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="user-info">
                                    <div class="user-name">
                                        <a href="/admin/users/<?= (int)$user['id'] ?>"><?= htmlspecialchars($user['name'] ?? 'Unknown') ?></a>
                                        <?php if (!empty($user['is_admin'])): ?>
                                        <span class="badge badge-primary badge-sm">Admin</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="user-email"><?= htmlspecialchars($user['email'] ?? '') ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="balance-value <?= ($user['balance'] ?? 0) > 0 ? 'balance-positive' : '' ?>">
                                <?= number_format($user['balance'] ?? 0, 0, ',', '.') ?> VND
                            </span>
                        </td>
                        <td>
                            <?php if (!empty($user['is_banned'])): ?>
                            <span class="badge badge-error">Banned</span>
                            <?php elseif (!empty($user['email_verified_at'])): ?>
                            <span class="badge badge-success">Verified</span>
                            <?php else: ?>
                            <span class="badge badge-warning">Unverified</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($user['two_factor_enabled'])): ?>
                            <span class="badge badge-success"><i class="icon-shield"></i> On</span>
                            <?php else: ?>
                            <span class="badge badge-gray">Off</span>
                            <?php endif; ?>
                        </td>
                        <td class="td-date">
                            <span title="<?= htmlspecialchars(date('Y-m-d H:i:s', strtotime($user['created_at'] ?? 'now'))) ?>">
                                <?= date('M d, Y', strtotime($user['created_at'] ?? 'now')) ?>
                            </span>
                        </td>
                        <td class="td-actions">
                            <div class="action-buttons">
                                <a href="/admin/users/<?= (int)$user['id'] ?>" class="btn btn-sm btn-ghost" title="View Details">
                                    <i class="icon-eye"></i>
                                </a>
                                <?php if (empty($user['is_admin'])): ?>
                                    <?php if (!empty($user['is_banned'])): ?>
                                    <form action="/admin/users/<?= (int)$user['id'] ?>/unban" method="POST" class="d-inline" onsubmit="return confirm('Unban this user?');">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                        <button type="submit" class="btn btn-sm btn-ghost btn-success" title="Unban">
                                            <i class="icon-check"></i>
                                        </button>
                                    </form>
                                    <?php else: ?>
                                    <button type="button" class="btn btn-sm btn-ghost btn-danger" title="Ban User" 
                                            onclick="showBanModal(<?= (int)$user['id'] ?>, '<?= htmlspecialchars(addslashes($user['name'] ?? '')) ?>')">
                                        <i class="icon-ban"></i>
                                    </button>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <button type="button" class="btn btn-sm btn-ghost btn-primary" title="Adjust Balance" 
                                        onclick="showBalanceModal(<?= (int)$user['id'] ?>, '<?= htmlspecialchars(addslashes($user['name'] ?? '')) ?>', <?= (float)($user['balance'] ?? 0) ?>)">
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
        <div class="card-footer">
            <div class="pagination-info">
                Showing <?= (($pagination['current_page'] - 1) * $pagination['per_page']) + 1 ?> 
                to <?= min($pagination['current_page'] * $pagination['per_page'], $pagination['total']) ?> 
                of <?= number_format($pagination['total']) ?> users
            </div>
            <nav class="pagination-nav">
                <ul class="pagination">
                    <?php 
                    $queryParams = [];
                    if (!empty($_GET['search'])) $queryParams['search'] = $_GET['search'];
                    if (!empty($_GET['status'])) $queryParams['status'] = $_GET['status'];
                    $queryString = http_build_query($queryParams);
                    $queryString = $queryString ? '&' . $queryString : '';
                    ?>
                    <?php if ($pagination['current_page'] > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="/admin/users?page=<?= $pagination['current_page'] - 1 ?><?= $queryString ?>">
                            <i class="icon-chevron-left"></i>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['total_pages'], $pagination['current_page'] + 2); $i++): ?>
                    <li class="page-item <?= $i === $pagination['current_page'] ? 'active' : '' ?>">
                        <a class="page-link" href="/admin/users?page=<?= $i ?><?= $queryString ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>

                    <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                    <li class="page-item">
                        <a class="page-link" href="/admin/users?page=<?= $pagination['current_page'] + 1 ?><?= $queryString ?>">
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
                <i class="icon-users"></i>
            </div>
            <h3>No users found</h3>
            <p>There are no users matching your search criteria.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Ban Modal -->
<div id="banModal" class="modal-overlay" style="display: none;">
    <div class="modal">
        <div class="modal-header">
            <h4 class="modal-title">Ban User: <span id="banUserName"></span></h4>
            <button type="button" class="modal-close" onclick="hideBanModal()">&times;</button>
        </div>
        <form id="banForm" method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="icon-alert-triangle"></i>
                    <span>This user will be immediately logged out and unable to access their account.</span>
                </div>
                <div class="form-group">
                    <label class="form-label" for="banReason">Ban Reason (optional)</label>
                    <textarea name="reason" id="banReason" class="form-input form-textarea" rows="3" 
                              placeholder="Enter reason for banning this user..."></textarea>
                    <small class="form-help">This reason will be logged for audit purposes.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="hideBanModal()">Cancel</button>
                <button type="submit" class="btn btn-danger">
                    <i class="icon-ban"></i> Ban User
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Balance Modal -->
<div id="balanceModal" class="modal-overlay" style="display: none;">
    <div class="modal">
        <div class="modal-header">
            <h4 class="modal-title">Adjust Balance: <span id="balanceUserName"></span></h4>
            <button type="button" class="modal-close" onclick="hideBalanceModal()">&times;</button>
        </div>
        <form id="balanceForm" method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Current Balance</label>
                    <div class="current-balance-display" id="currentBalance">0 VND</div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="amount">Adjustment Amount (VND)</label>
                    <div class="input-group">
                        <input type="number" name="amount" id="amount" class="form-input" step="1000" required 
                               placeholder="Enter amount">
                        <span class="input-group-text">VND</span>
                    </div>
                    <small class="form-help">
                        <strong>Positive number:</strong> Add credits | 
                        <strong>Negative number:</strong> Deduct credits
                    </small>
                </div>
                <div class="quick-amounts">
                    <span class="quick-amounts-label">Quick add:</span>
                    <button type="button" class="btn btn-sm btn-outline" onclick="setAmount(50000)">+50K</button>
                    <button type="button" class="btn btn-sm btn-outline" onclick="setAmount(100000)">+100K</button>
                    <button type="button" class="btn btn-sm btn-outline" onclick="setAmount(500000)">+500K</button>
                    <button type="button" class="btn btn-sm btn-outline" onclick="setAmount(1000000)">+1M</button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="hideBalanceModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="icon-save"></i> Update Balance
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.stat-card-compact {
    padding: var(--spacing-4);
}

.stat-card-compact .stat-icon {
    width: 40px;
    height: 40px;
    font-size: var(--font-size-lg);
}

.stat-card-compact .stat-value {
    font-size: var(--font-size-xl);
}

.filter-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-4);
    flex-wrap: wrap;
    gap: var(--spacing-3);
}

.filter-tabs {
    display: flex;
    gap: var(--spacing-1);
    background: var(--color-gray-100);
    padding: var(--spacing-1);
    border-radius: var(--radius-lg);
}

.filter-tab {
    padding: var(--spacing-2) var(--spacing-4);
    font-size: var(--font-size-sm);
    color: var(--color-gray-600);
    text-decoration: none;
    border-radius: var(--radius-md);
    transition: all var(--transition-fast);
}

.filter-tab:hover {
    color: var(--color-gray-900);
}

.filter-tab.active {
    background: var(--color-white);
    color: var(--color-primary);
    box-shadow: var(--shadow-sm);
}

.filter-actions {
    display: flex;
    gap: var(--spacing-2);
}

.search-form {
    display: flex;
    gap: var(--spacing-2);
}

.search-input-wrapper {
    position: relative;
}

.search-input-wrapper .icon-search {
    position: absolute;
    left: var(--spacing-3);
    top: 50%;
    transform: translateY(-50%);
    color: var(--color-gray-400);
}

.search-input {
    padding-left: calc(var(--spacing-3) * 2 + 16px);
    min-width: 250px;
}

.users-table-wrapper {
    overflow-x: auto;
}

.users-table {
    margin-bottom: 0;
}

.users-table th {
    white-space: nowrap;
}

.th-user {
    min-width: 250px;
}

.th-actions {
    width: 120px;
}

.user-row.user-banned {
    background-color: rgba(239, 68, 68, 0.05);
}

.user-cell {
    display: flex;
    align-items: center;
    gap: var(--spacing-3);
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: var(--radius-full);
    background: var(--color-gray-100);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    overflow: hidden;
}

.user-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-initials {
    font-size: var(--font-size-base);
    font-weight: var(--font-weight-semibold);
    color: var(--color-gray-500);
}

.user-info {
    min-width: 0;
}

.user-name {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
}

.user-name a {
    color: var(--color-gray-900);
    font-weight: var(--font-weight-medium);
    text-decoration: none;
}

.user-name a:hover {
    color: var(--color-primary);
}

.user-email {
    font-size: var(--font-size-sm);
    color: var(--color-gray-500);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.badge-sm {
    font-size: 10px;
    padding: 2px 6px;
}

.balance-value {
    font-weight: var(--font-weight-medium);
    color: var(--color-gray-700);
}

.balance-positive {
    color: var(--color-success);
}

.td-date {
    white-space: nowrap;
    color: var(--color-gray-500);
    font-size: var(--font-size-sm);
}

.td-actions {
    text-align: right;
}

.action-buttons {
    display: flex;
    gap: var(--spacing-1);
    justify-content: flex-end;
}

.btn-ghost {
    color: var(--color-gray-500);
}

.btn-ghost:hover {
    background: var(--color-gray-100);
    color: var(--color-gray-700);
}

.btn-ghost.btn-success:hover {
    background: rgba(34, 197, 94, 0.1);
    color: var(--color-success);
}

.btn-ghost.btn-danger:hover {
    background: rgba(239, 68, 68, 0.1);
    color: var(--color-error);
}

.btn-ghost.btn-primary:hover {
    background: rgba(79, 70, 229, 0.1);
    color: var(--color-primary);
}

.card-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--spacing-4) var(--spacing-6);
    border-top: 1px solid var(--color-gray-200);
    background: var(--color-gray-50);
}

.pagination-info {
    font-size: var(--font-size-sm);
    color: var(--color-gray-500);
}

.pagination-nav .pagination {
    margin: 0;
}

.current-balance-display {
    font-size: var(--font-size-2xl);
    font-weight: var(--font-weight-bold);
    color: var(--color-primary);
    padding: var(--spacing-3);
    background: var(--color-gray-50);
    border-radius: var(--radius-md);
}

.quick-amounts {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    margin-top: var(--spacing-3);
}

.quick-amounts-label {
    font-size: var(--font-size-sm);
    color: var(--color-gray-500);
}

.input-group {
    display: flex;
}

.input-group .form-input {
    border-radius: var(--radius-md) 0 0 var(--radius-md);
    border-right: none;
}

.input-group-text {
    padding: var(--spacing-2) var(--spacing-3);
    background: var(--color-gray-100);
    border: 1px solid var(--color-gray-300);
    border-radius: 0 var(--radius-md) var(--radius-md) 0;
    font-size: var(--font-size-sm);
    color: var(--color-gray-600);
}

@media (max-width: 768px) {
    .filter-toolbar {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filter-tabs {
        overflow-x: auto;
        justify-content: flex-start;
    }
    
    .search-form {
        flex-direction: column;
    }
    
    .search-input {
        min-width: 100%;
    }
}
</style>

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
    document.getElementById('currentBalance').textContent = currentBalance.toLocaleString('vi-VN') + ' VND';
    document.getElementById('amount').value = '';
    document.getElementById('balanceModal').style.display = 'flex';
}

function hideBalanceModal() {
    document.getElementById('balanceModal').style.display = 'none';
}

function setAmount(amount) {
    document.getElementById('amount').value = amount;
}

function exportUsers() {
    var params = new URLSearchParams(window.location.search);
    params.set('export', 'csv');
    window.location.href = '/admin/users?' + params.toString();
}

// Close modals on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        hideBanModal();
        hideBalanceModal();
    }
});

// Close modals when clicking overlay
document.querySelectorAll('.modal-overlay').forEach(function(overlay) {
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) {
            overlay.style.display = 'none';
        }
    });
});
</script>
