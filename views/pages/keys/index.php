<?php
/**
 * API Keys Index Page
 * Variables: $pageTitle, $currentPage, $apiKeys, $providers
 */

$newApiKey = $_SESSION['new_api_key'] ?? null;
unset($_SESSION['new_api_key']);
?>

<div class="page-header">
    <div class="page-header-content">
        <h1>API Keys</h1>
        <p>Manage your API keys for accessing AI services</p>
    </div>
    <div class="page-header-actions">
        <a href="/keys/create" class="btn btn-primary">
            <i class="icon-plus"></i> Create New Key
        </a>
    </div>
</div>

<?php if ($newApiKey): ?>
<div class="alert alert-success alert-dismissible">
    <strong>New API Key Created!</strong>
    <p>Make sure to copy your API key now. You won't be able to see it again.</p>
    <div class="api-key-display">
        <code id="new-api-key"><?= htmlspecialchars($newApiKey) ?></code>
        <button type="button" class="btn btn-sm btn-secondary" onclick="copyToClipboard('new-api-key')">
            <i class="icon-copy"></i> Copy
        </button>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <?php if (!empty($apiKeys)): ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Key</th>
                        <th>Provider</th>
                        <th>Status</th>
                        <th>Usage</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($apiKeys as $key): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($key['name'] ?? 'Unnamed') ?></strong>
                        </td>
                        <td>
                            <code class="key-preview"><?= htmlspecialchars(substr($key['key_hash'] ?? '', 0, 8)) ?>...****</code>
                        </td>
                        <td>
                            <?php if (!empty($key['provider'])): ?>
                            <span class="badge badge-secondary"><?= htmlspecialchars(ucfirst($key['provider'])) ?></span>
                            <?php else: ?>
                            <span class="text-muted">All</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($key['is_active'])): ?>
                            <span class="badge badge-success">Active</span>
                            <?php else: ?>
                            <span class="badge badge-danger">Revoked</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= number_format($key['usage_count'] ?? 0) ?> requests
                            <?php if (!empty($key['usage_limit'])): ?>
                            <small class="text-muted">/ <?= number_format($key['usage_limit']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= htmlspecialchars(date('M d, Y', strtotime($key['created_at'] ?? 'now'))) ?>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="/keys/<?= (int)$key['id'] ?>" class="btn btn-sm btn-secondary" title="View Details">
                                    <i class="icon-eye"></i>
                                </a>
                                <?php if (!empty($key['is_active'])): ?>
                                <form action="/keys/<?= (int)$key['id'] ?>/rotate" method="POST" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                    <button type="submit" class="btn btn-sm btn-warning" title="Rotate Key" onclick="return confirm('Are you sure you want to rotate this key? The old key will be invalidated.')">
                                        <i class="icon-refresh"></i>
                                    </button>
                                </form>
                                <form action="/keys/<?= (int)$key['id'] ?>/revoke" method="POST" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" title="Revoke Key" onclick="return confirm('Are you sure you want to revoke this key? This action cannot be undone.')">
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
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="icon-key"></i>
            </div>
            <h3>No API Keys Yet</h3>
            <p>Create your first API key to start using our AI services.</p>
            <a href="/keys/create" class="btn btn-primary">Create Your First Key</a>
        </div>
        <?php endif; ?>
    </div>
</div>
