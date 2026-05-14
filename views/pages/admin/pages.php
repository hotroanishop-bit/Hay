<?php
/**
 * Admin Custom Pages Page
 * Variables: $pageTitle, $currentPage, $pages
 */
?>

<div class="page-header">
    <div class="page-header-content">
        <h1>Custom Pages</h1>
        <p>Manage CMS pages with SEO support</p>
    </div>
    <div class="page-header-actions">
        <a href="/admin/pages/create" class="btn btn-primary">
            <i class="icon-plus"></i> Create Page
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>All Pages</h3>
    </div>
    <div class="card-body">
        <?php if (!empty($pages)): ?>
        <div class="table-responsive">
            <table class="table table-hover admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Slug</th>
                        <th>Status</th>
                        <th>Menu</th>
                        <th>Order</th>
                        <th>Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pages as $page): ?>
                    <tr>
                        <td><?= (int)($page['id'] ?? 0) ?></td>
                        <td><strong><?= htmlspecialchars($page['title'] ?? '') ?></strong></td>
                        <td><code>/page/<?= htmlspecialchars($page['slug'] ?? '') ?></code></td>
                        <td>
                            <?php if (!empty($page['is_published'])): ?>
                            <span class="badge badge-success">Published</span>
                            <?php else: ?>
                            <span class="badge badge-warning">Draft</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($page['show_in_menu'])): ?>
                            <span class="badge badge-info">In Menu</span>
                            <?php else: ?>
                            <span class="badge badge-secondary">Hidden</span>
                            <?php endif; ?>
                        </td>
                        <td><?= (int)($page['menu_order'] ?? 0) ?></td>
                        <td><?= htmlspecialchars(date('M d, Y', strtotime($page['updated_at'] ?? $page['created_at'] ?? 'now'))) ?></td>
                        <td>
                            <div class="btn-group">
                                <form action="/admin/pages/<?= (int)$page['id'] ?>/toggle-publish" method="POST" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                    <button type="submit" class="btn btn-sm <?= !empty($page['is_published']) ? 'btn-warning' : 'btn-success' ?>" 
                                            title="<?= !empty($page['is_published']) ? 'Unpublish' : 'Publish' ?>">
                                        <i class="icon-<?= !empty($page['is_published']) ? 'eye-off' : 'eye' ?>"></i>
                                    </button>
                                </form>
                                <a href="/page/<?= htmlspecialchars($page['slug'] ?? '') ?>" class="btn btn-sm btn-info" title="View" target="_blank">
                                    <i class="icon-external-link"></i>
                                </a>
                                <a href="/admin/pages/<?= (int)$page['id'] ?>/edit" class="btn btn-sm btn-secondary" title="Edit">
                                    <i class="icon-edit"></i>
                                </a>
                                <form action="/admin/pages/<?= (int)$page['id'] ?>/delete" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this page?');">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete">
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
        <p class="text-muted">No custom pages found. <a href="/admin/pages/create">Create your first page</a>.</p>
        <?php endif; ?>
    </div>
</div>
