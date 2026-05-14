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
            <input type="text" class="form-control form-control-sm" placeholder="Search users..." id="user-search">
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
                        </td>
                        <td><?= htmlspecialchars($user['email'] ?? '') ?></td>
                        <td>$<?= number_format($user['balance'] ?? 0, 2) ?></td>
                        <td>
                            <?php if (!empty($user['is_active'])): ?>
                            <span class="badge badge-success">Active</span>
                            <?php else: ?>
                            <span class="badge badge-danger">Inactive</span>
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
                                <form action="/admin/users/<?= (int)$user['id'] ?>/toggle" method="POST" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                    <button type="submit" class="btn btn-sm <?= !empty($user['is_active']) ? 'btn-warning' : 'btn-success' ?>" title="<?= !empty($user['is_active']) ? 'Deactivate' : 'Activate' ?>">
                                        <i class="icon-<?= !empty($user['is_active']) ? 'ban' : 'check' ?>"></i>
                                    </button>
                                </form>
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
                <?php if ($pagination['current_page'] > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="/admin/users?page=<?= $pagination['current_page'] - 1 ?>">Previous</a>
                </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= min(5, $pagination['total_pages']); $i++): ?>
                <li class="page-item <?= $i === $pagination['current_page'] ? 'active' : '' ?>">
                    <a class="page-link" href="/admin/users?page=<?= $i ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>

                <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                <li class="page-item">
                    <a class="page-link" href="/admin/users?page=<?= $pagination['current_page'] + 1 ?>">Next</a>
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
