<?php
/**
 * Admin Menu Item Form Page
 * Variables: $pageTitle, $currentPage, $menuItem, $isEdit
 */
?>

<div class="page-header">
    <div class="page-header-content">
        <h1><?= $isEdit ? 'Edit Menu Item' : 'Create Menu Item' ?></h1>
        <p><?= $isEdit ? 'Update menu item settings' : 'Add a new navigation menu item' ?></p>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="<?= $isEdit ? '/admin/menu/' . (int)$menuItem['id'] . '/update' : '/admin/menu' ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="label">Label <span class="text-danger">*</span></label>
                    <input type="text" id="label" name="label" class="form-control" required
                           value="<?= htmlspecialchars($menuItem['label'] ?? '') ?>"
                           placeholder="e.g., Dashboard, Settings">
                </div>
                <div class="form-group col-md-6">
                    <label for="icon">Icon Name</label>
                    <input type="text" id="icon" name="icon" class="form-control"
                           value="<?= htmlspecialchars($menuItem['icon'] ?? '') ?>"
                           placeholder="e.g., home, settings, user">
                    <small class="form-text text-muted">Icon class name (without prefix)</small>
                </div>
            </div>

            <div class="form-group">
                <label for="url">URL</label>
                <input type="text" id="url" name="url" class="form-control"
                       value="<?= htmlspecialchars($menuItem['url'] ?? '#') ?>"
                       placeholder="/dashboard or https://example.com">
                <small class="form-text text-muted">Internal path or external URL</small>
            </div>

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="sort_order">Sort Order</label>
                    <input type="number" id="sort_order" name="sort_order" class="form-control"
                           min="0" value="<?= (int)($menuItem['sort_order'] ?? 0) ?>">
                    <small class="form-text text-muted">Lower numbers appear first</small>
                </div>
            </div>

            <div class="form-group">
                <label>Display Locations</label>
                <div class="form-check">
                    <input type="checkbox" id="show_in_bottom_nav" name="show_in_bottom_nav" class="form-check-input"
                           <?= (!empty($menuItem['show_in_bottom_nav'])) ? 'checked' : '' ?>>
                    <label for="show_in_bottom_nav" class="form-check-label">Show in Bottom Navigation Bar</label>
                    <small class="form-text text-muted">Fixed navigation bar at bottom of mobile screen</small>
                </div>
                <div class="form-check mt-2">
                    <input type="checkbox" id="show_in_bottom_sheet" name="show_in_bottom_sheet" class="form-check-input"
                           <?= (!empty($menuItem['show_in_bottom_sheet'])) ? 'checked' : '' ?>>
                    <label for="show_in_bottom_sheet" class="form-check-label">Show in Bottom Sheet Menu</label>
                    <small class="form-text text-muted">Expandable menu that slides up from bottom</small>
                </div>
            </div>

            <div class="form-group">
                <div class="form-check">
                    <input type="checkbox" id="is_active" name="is_active" class="form-check-input"
                           <?= ($isEdit ? !empty($menuItem['is_active']) : true) ? 'checked' : '' ?>>
                    <label for="is_active" class="form-check-label">Active</label>
                    <small class="form-text text-muted">Inactive items are hidden from navigation</small>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="icon-save"></i> <?= $isEdit ? 'Update Menu Item' : 'Create Menu Item' ?>
                </button>
                <a href="/admin/menu" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
