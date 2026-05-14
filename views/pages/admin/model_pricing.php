<?php
/**
 * Admin Model Pricing Page
 * Variables: $pageTitle, $currentPage, $pricing
 */
?>

<div class="page-header">
    <div class="page-header-content">
        <h1>Model Pricing</h1>
        <p>Manage pricing for AI models</p>
    </div>
    <div class="page-header-actions">
        <a href="/admin/model-pricing/create" class="btn btn-primary">
            <i class="icon-plus"></i> Add Model
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>All Model Pricing</h3>
    </div>
    <div class="card-body">
        <?php if (!empty($pricing)): ?>
        <div class="table-responsive">
            <table class="table table-hover admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Provider</th>
                        <th>Model Name</th>
                        <th>Input Price (per 1K tokens)</th>
                        <th>Output Price (per 1K tokens)</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pricing as $model): ?>
                    <tr>
                        <td><?= (int)($model['id'] ?? 0) ?></td>
                        <td><?= htmlspecialchars($model['provider_name'] ?? 'Unknown') ?></td>
                        <td><code><?= htmlspecialchars($model['model_name'] ?? '') ?></code></td>
                        <td>$<?= number_format((float)($model['input_price_per_1k'] ?? 0), 6) ?></td>
                        <td>$<?= number_format((float)($model['output_price_per_1k'] ?? 0), 6) ?></td>
                        <td>
                            <?php if (!empty($model['is_active'])): ?>
                            <span class="badge badge-success">Active</span>
                            <?php else: ?>
                            <span class="badge badge-danger">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="/admin/model-pricing/<?= (int)$model['id'] ?>/edit" class="btn btn-sm btn-secondary" title="Edit">
                                    <i class="icon-edit"></i>
                                </a>
                                <form action="/admin/model-pricing/<?= (int)$model['id'] ?>/delete" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to deactivate this model pricing?');">
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
        <p class="text-muted">No model pricing found. <a href="/admin/model-pricing/create">Add your first model</a>.</p>
        <?php endif; ?>
    </div>
</div>
