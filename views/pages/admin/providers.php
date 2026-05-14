<?php
/**
 * Admin Providers Page
 * Variables: $pageTitle, $currentPage, $providers
 */
?>

<div class="page-header">
    <div class="page-header-content">
        <h1>AI Providers</h1>
        <p>Manage upstream API providers</p>
    </div>
    <div class="page-header-actions">
        <a href="/admin/providers/create" class="btn btn-primary">
            <i class="icon-plus"></i> Add Provider
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>All Providers</h3>
    </div>
    <div class="card-body">
        <?php if (!empty($providers)): ?>
        <div class="table-responsive">
            <table class="table table-hover admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Base URL</th>
                        <th>API Key</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($providers as $provider): ?>
                    <tr>
                        <td><?= (int)($provider['id'] ?? 0) ?></td>
                        <td><strong><?= htmlspecialchars($provider['name'] ?? '') ?></strong></td>
                        <td>
                            <code class="url-code"><?= htmlspecialchars($provider['base_url'] ?? '') ?></code>
                        </td>
                        <td>
                            <code class="api-key-masked"><?= htmlspecialchars($provider['api_key_masked'] ?? 'Not set') ?></code>
                        </td>
                        <td>
                            <?php if (!empty($provider['is_active'])): ?>
                            <span class="badge badge-success">Active</span>
                            <?php else: ?>
                            <span class="badge badge-danger">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars(date('M d, Y', strtotime($provider['created_at'] ?? 'now'))) ?></td>
                        <td>
                            <div class="btn-group">
                                <a href="/admin/providers/<?= (int)$provider['id'] ?>/edit" class="btn btn-sm btn-secondary" title="Edit">
                                    <i class="icon-edit"></i>
                                </a>
                                <form action="/admin/providers/<?= (int)$provider['id'] ?>/delete" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to deactivate this provider?');">
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
        <p class="text-muted">No providers found. <a href="/admin/providers/create">Add your first provider</a>.</p>
        <?php endif; ?>
    </div>
</div>
