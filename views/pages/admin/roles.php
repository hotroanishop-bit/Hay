<?php
/**
 * Admin Roles Management Page
 * List all admin roles with permissions
 * 
 * Variables: $pageTitle, $currentPage, $roles, $userCounts
 */
?>

<div class="page-header">
    <div class="page-header-content">
        <div>
            <h1>Admin Roles</h1>
            <p class="text-muted">Manage admin roles and permissions</p>
        </div>
        <div class="page-header-actions">
            <a href="/admin/roles/create" class="btn btn-primary">
                <i class="icon-plus"></i> Create Role
            </a>
        </div>
    </div>
</div>

<!-- Roles Grid -->
<div class="roles-grid">
    <?php if (!empty($roles)): ?>
        <?php foreach ($roles as $role): ?>
        <div class="card role-card">
            <div class="card-header">
                <div class="role-header">
                    <div>
                        <h3 class="role-name"><?= htmlspecialchars($role['name']) ?></h3>
                        <?php if (!empty($role['is_system'])): ?>
                        <span class="badge badge-info">System Role</span>
                        <?php endif; ?>
                    </div>
                    <div class="role-stats">
                        <span class="stat-badge">
                            <i class="icon-users"></i>
                            <?= number_format($userCounts[$role['id']] ?? 0) ?> users
                        </span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <?php if (!empty($role['description'])): ?>
                <p class="role-description"><?= htmlspecialchars($role['description']) ?></p>
                <?php endif; ?>
                
                <?php 
                $permissions = json_decode($role['permissions'] ?? '[]', true) ?: [];
                $permissionGroups = [];
                foreach ($permissions as $perm) {
                    $parts = explode('.', $perm);
                    $group = $parts[0];
                    $permissionGroups[$group][] = $perm;
                }
                ?>
                
                <div class="permission-summary">
                    <div class="permission-tags">
                        <?php if (count($permissions) > 0): ?>
                            <?php foreach (array_slice($permissions, 0, 6) as $perm): ?>
                            <span class="permission-tag"><?= htmlspecialchars($perm) ?></span>
                            <?php endforeach; ?>
                            <?php if (count($permissions) > 6): ?>
                            <span class="permission-tag permission-more">+<?= count($permissions) - 6 ?> more</span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="text-muted">No permissions assigned</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="role-actions">
                    <a href="/admin/roles/<?= (int)$role['id'] ?>/edit" class="btn btn-sm btn-outline">
                        <i class="icon-edit"></i> Edit
                    </a>
                    <?php if (empty($role['is_system'])): ?>
                    <form action="/admin/roles/<?= (int)$role['id'] ?>/delete" method="POST" class="d-inline" 
                          onsubmit="return confirm('Are you sure you want to delete this role? Users with this role will have their role removed.');">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <button type="submit" class="btn btn-sm btn-outline btn-danger">
                            <i class="icon-trash"></i> Delete
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
    <div class="empty-state">
        <div class="empty-state-icon">
            <i class="icon-shield"></i>
        </div>
        <h3>No roles found</h3>
        <p>Create your first admin role to get started.</p>
        <a href="/admin/roles/create" class="btn btn-primary">Create Role</a>
    </div>
    <?php endif; ?>
</div>

<style>
.roles-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: var(--spacing-4);
}

.role-card {
    display: flex;
    flex-direction: column;
}

.role-card .card-header {
    border-bottom: 1px solid var(--color-gray-200);
    background: var(--color-gray-50);
}

.role-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.role-name {
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-semibold);
    margin: 0 0 var(--spacing-1) 0;
    text-transform: capitalize;
}

.role-stats {
    display: flex;
    gap: var(--spacing-2);
}

.stat-badge {
    display: inline-flex;
    align-items: center;
    gap: var(--spacing-1);
    font-size: var(--font-size-sm);
    color: var(--color-gray-500);
}

.role-description {
    color: var(--color-gray-600);
    font-size: var(--font-size-sm);
    margin-bottom: var(--spacing-3);
}

.permission-summary {
    margin-top: auto;
}

.permission-tags {
    display: flex;
    flex-wrap: wrap;
    gap: var(--spacing-1);
}

.permission-tag {
    display: inline-block;
    padding: var(--spacing-1) var(--spacing-2);
    background: var(--color-gray-100);
    color: var(--color-gray-700);
    font-size: var(--font-size-xs);
    border-radius: var(--radius-sm);
}

.permission-more {
    background: var(--color-primary);
    color: white;
}

.role-card .card-footer {
    border-top: 1px solid var(--color-gray-200);
    background: var(--color-gray-50);
    margin-top: auto;
}

.role-actions {
    display: flex;
    gap: var(--spacing-2);
    justify-content: flex-end;
}

.badge-info {
    background: var(--color-info);
    color: white;
}

@media (max-width: 768px) {
    .roles-grid {
        grid-template-columns: 1fr;
    }
}
</style>
