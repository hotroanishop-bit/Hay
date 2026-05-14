<?php
/**
 * Admin Plans Page
 * Variables: $pageTitle, $currentPage, $plans
 */
?>

<div class="page-header">
    <div class="page-header-content">
        <h1>Subscription Plans</h1>
        <p>Manage pricing plans for API access</p>
    </div>
    <div class="page-header-actions">
        <a href="/admin/plans/create" class="btn btn-primary">
            <i class="icon-plus"></i> Create Plan
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>All Plans</h3>
    </div>
    <div class="card-body">
        <?php if (!empty($plans)): ?>
        <div class="table-responsive">
            <table class="table table-hover admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Monthly Price</th>
                        <th>Rate Limit</th>
                        <th>Daily Token Limit</th>
                        <th>Price Multiplier</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($plans as $plan): ?>
                    <tr>
                        <td><?= (int)($plan['id'] ?? 0) ?></td>
                        <td><strong><?= htmlspecialchars($plan['name'] ?? '') ?></strong></td>
                        <td>$<?= number_format((float)($plan['price_monthly'] ?? 0), 2) ?></td>
                        <td><?= number_format((int)($plan['rate_limit_per_minute'] ?? 0)) ?>/min</td>
                        <td><?= number_format((int)($plan['daily_token_limit'] ?? 0)) ?></td>
                        <td><?= number_format((float)($plan['price_multiplier'] ?? 1.0), 2) ?>x</td>
                        <td>
                            <?php if (!empty($plan['is_active'])): ?>
                            <span class="badge badge-success">Active</span>
                            <?php else: ?>
                            <span class="badge badge-danger">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="/admin/plans/<?= (int)$plan['id'] ?>/edit" class="btn btn-sm btn-secondary" title="Edit">
                                    <i class="icon-edit"></i>
                                </a>
                                <form action="/admin/plans/<?= (int)$plan['id'] ?>/delete" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to deactivate this plan?');">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" title="Deactivate">
                                        <i class="icon-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <p class="text-muted">No plans found. <a href="/admin/plans/create">Create your first plan</a>.</p>
        <?php endif; ?>
    </div>
</div>
