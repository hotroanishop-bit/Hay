<?php
/**
 * Admin Themes Page
 * Variables: $pageTitle, $currentPage, $themes
 */
?>

<div class="page-header">
    <div class="page-header-content">
        <h1>Themes</h1>
        <p>Manage UI themes and CSS variables</p>
    </div>
    <div class="page-header-actions">
        <a href="/admin/themes/create" class="btn btn-primary">
            <i class="icon-plus"></i> Create Theme
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>All Themes</h3>
    </div>
    <div class="card-body">
        <?php if (!empty($themes)): ?>
        <div class="table-responsive">
            <table class="table table-hover admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Default</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($themes as $theme): ?>
                    <tr>
                        <td><?= (int)($theme['id'] ?? 0) ?></td>
                        <td><strong><?= htmlspecialchars($theme['name'] ?? '') ?></strong></td>
                        <td>
                            <?php if (!empty($theme['is_default'])): ?>
                            <span class="badge badge-success">Default</span>
                            <?php else: ?>
                            <span class="badge badge-secondary">-</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars(date('M d, Y', strtotime($theme['created_at'] ?? 'now'))) ?></td>
                        <td>
                            <div class="btn-group">
                                <?php if (empty($theme['is_default'])): ?>
                                <form action="/admin/themes/<?= (int)$theme['id'] ?>/default" method="POST" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                    <button type="submit" class="btn btn-sm btn-success" title="Set as Default">
                                        <i class="icon-check"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                                <a href="/admin/themes/<?= (int)$theme['id'] ?>/edit" class="btn btn-sm btn-secondary" title="Edit">
                                    <i class="icon-edit"></i>
                                </a>
                                <?php if (empty($theme['is_default'])): ?>
                                <form action="/admin/themes/<?= (int)$theme['id'] ?>/delete" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this theme?');">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                        <i class="icon-trash"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <p class="text-muted">No themes found. <a href="/admin/themes/create">Create your first theme</a>.</p>
        <?php endif; ?>
    </div>
</div>
