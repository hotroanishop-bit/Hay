<?php
/**
 * Admin Role Form Page
 * Create/Edit admin role with permission checkboxes
 * 
 * Variables: $pageTitle, $currentPage, $role (optional), $availablePermissions, $isEdit
 */

$isEdit = !empty($role);
$rolePermissions = [];
if ($isEdit && !empty($role['permissions'])) {
    $rolePermissions = json_decode($role['permissions'], true) ?: [];
}
?>

<div class="page-header">
    <div class="page-header-content">
        <div>
            <nav class="breadcrumb">
                <a href="/admin/roles">Roles</a>
                <span class="separator">/</span>
                <span><?= $isEdit ? 'Edit Role' : 'Create Role' ?></span>
            </nav>
            <h1><?= $isEdit ? 'Edit Role: ' . htmlspecialchars($role['name']) : 'Create New Role' ?></h1>
            <p class="text-muted"><?= $isEdit ? 'Modify role settings and permissions' : 'Define a new admin role with specific permissions' ?></p>
        </div>
    </div>
</div>

<div class="card">
    <form action="<?= $isEdit ? '/admin/roles/' . (int)$role['id'] . '/update' : '/admin/roles' ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
        
        <div class="card-body">
            <!-- Basic Info -->
            <div class="form-section">
                <h3 class="form-section-title">Basic Information</h3>
                
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label class="form-label" for="name">Role Name <span class="required">*</span></label>
                        <input type="text" name="name" id="name" class="form-input" required
                               value="<?= htmlspecialchars($role['name'] ?? '') ?>"
                               placeholder="e.g., support_agent"
                               pattern="[a-z_]+" title="Lowercase letters and underscores only"
                               <?= ($isEdit && !empty($role['is_system'])) ? 'readonly' : '' ?>>
                        <small class="form-help">Lowercase letters and underscores only</small>
                    </div>
                    
                    <div class="form-group col-md-6">
                        <label class="form-label" for="description">Description</label>
                        <input type="text" name="description" id="description" class="form-input"
                               value="<?= htmlspecialchars($role['description'] ?? '') ?>"
                               placeholder="Brief description of this role">
                    </div>
                </div>
            </div>
            
            <!-- Permissions -->
            <div class="form-section">
                <h3 class="form-section-title">Permissions</h3>
                <p class="form-section-description">Select the permissions this role should have access to.</p>
                
                <div class="permissions-grid">
                    <?php foreach ($availablePermissions as $group => $permissions): ?>
                    <div class="permission-group">
                        <div class="permission-group-header">
                            <label class="checkbox-label group-toggle">
                                <input type="checkbox" class="group-checkbox" data-group="<?= htmlspecialchars($group) ?>">
                                <span class="checkbox-custom"></span>
                                <span class="group-name"><?= ucfirst(htmlspecialchars($group)) ?></span>
                            </label>
                        </div>
                        <div class="permission-group-body">
                            <?php foreach ($permissions as $key => $label): ?>
                            <label class="checkbox-label permission-item">
                                <input type="checkbox" name="permissions[]" value="<?= htmlspecialchars($key) ?>"
                                       class="permission-checkbox" data-group="<?= htmlspecialchars($group) ?>"
                                       <?= in_array($key, $rolePermissions) ? 'checked' : '' ?>>
                                <span class="checkbox-custom"></span>
                                <span class="permission-label">
                                    <span class="permission-key"><?= htmlspecialchars($key) ?></span>
                                    <span class="permission-desc"><?= htmlspecialchars($label) ?></span>
                                </span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <div class="card-footer">
            <div class="form-actions">
                <a href="/admin/roles" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="icon-save"></i> <?= $isEdit ? 'Update Role' : 'Create Role' ?>
                </button>
            </div>
        </div>
    </form>
</div>

<style>
.breadcrumb {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    font-size: var(--font-size-sm);
    color: var(--color-gray-500);
    margin-bottom: var(--spacing-2);
}

.breadcrumb a {
    color: var(--color-primary);
    text-decoration: none;
}

.breadcrumb a:hover {
    text-decoration: underline;
}

.breadcrumb .separator {
    color: var(--color-gray-400);
}

.form-section {
    margin-bottom: var(--spacing-6);
}

.form-section-title {
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-semibold);
    margin-bottom: var(--spacing-2);
    padding-bottom: var(--spacing-2);
    border-bottom: 1px solid var(--color-gray-200);
}

.form-section-description {
    color: var(--color-gray-600);
    font-size: var(--font-size-sm);
    margin-bottom: var(--spacing-4);
}

.form-row {
    display: flex;
    gap: var(--spacing-4);
    flex-wrap: wrap;
}

.form-row .form-group {
    flex: 1;
    min-width: 250px;
}

.col-md-6 {
    flex: 1;
}

.permissions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: var(--spacing-4);
}

.permission-group {
    border: 1px solid var(--color-gray-200);
    border-radius: var(--radius-md);
    overflow: hidden;
}

.permission-group-header {
    background: var(--color-gray-50);
    padding: var(--spacing-3);
    border-bottom: 1px solid var(--color-gray-200);
}

.group-name {
    font-weight: var(--font-weight-semibold);
    text-transform: capitalize;
}

.permission-group-body {
    padding: var(--spacing-3);
}

.checkbox-label {
    display: flex;
    align-items: flex-start;
    gap: var(--spacing-2);
    cursor: pointer;
    user-select: none;
}

.checkbox-label input[type="checkbox"] {
    display: none;
}

.checkbox-custom {
    width: 18px;
    height: 18px;
    border: 2px solid var(--color-gray-300);
    border-radius: var(--radius-sm);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: all var(--transition-fast);
    margin-top: 2px;
}

.checkbox-label input[type="checkbox"]:checked + .checkbox-custom {
    background: var(--color-primary);
    border-color: var(--color-primary);
}

.checkbox-label input[type="checkbox"]:checked + .checkbox-custom::after {
    content: '';
    width: 6px;
    height: 10px;
    border: solid white;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
    margin-bottom: 2px;
}

.permission-item {
    padding: var(--spacing-2) 0;
    border-bottom: 1px solid var(--color-gray-100);
}

.permission-item:last-child {
    border-bottom: none;
}

.permission-label {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.permission-key {
    font-family: monospace;
    font-size: var(--font-size-sm);
    color: var(--color-gray-700);
}

.permission-desc {
    font-size: var(--font-size-xs);
    color: var(--color-gray-500);
}

.form-actions {
    display: flex;
    gap: var(--spacing-3);
    justify-content: flex-end;
}

.required {
    color: var(--color-error);
}

@media (max-width: 768px) {
    .permissions-grid {
        grid-template-columns: 1fr;
    }
    
    .form-row {
        flex-direction: column;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Group checkbox toggle
    document.querySelectorAll('.group-checkbox').forEach(function(groupCheckbox) {
        const group = groupCheckbox.dataset.group;
        const permissionCheckboxes = document.querySelectorAll('.permission-checkbox[data-group="' + group + '"]');
        
        // Initial state
        updateGroupCheckbox(groupCheckbox, permissionCheckboxes);
        
        // Toggle all in group
        groupCheckbox.addEventListener('change', function() {
            permissionCheckboxes.forEach(function(cb) {
                cb.checked = groupCheckbox.checked;
            });
        });
        
        // Update group checkbox when individual changes
        permissionCheckboxes.forEach(function(cb) {
            cb.addEventListener('change', function() {
                updateGroupCheckbox(groupCheckbox, permissionCheckboxes);
            });
        });
    });
    
    function updateGroupCheckbox(groupCheckbox, permissionCheckboxes) {
        const total = permissionCheckboxes.length;
        const checked = Array.from(permissionCheckboxes).filter(cb => cb.checked).length;
        
        groupCheckbox.checked = checked === total;
        groupCheckbox.indeterminate = checked > 0 && checked < total;
    }
});
</script>
