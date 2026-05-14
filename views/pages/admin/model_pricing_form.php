<?php
/**
 * Admin Model Pricing Form Page
 * Variables: $pageTitle, $currentPage, $modelPricing, $providers, $isEdit
 */
?>

<div class="page-header">
    <div class="page-header-content">
        <h1><?= $isEdit ? 'Edit Model Pricing' : 'Add Model Pricing' ?></h1>
        <p><?= $isEdit ? 'Update model pricing details' : 'Add pricing for a new AI model' ?></p>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="<?= $isEdit ? '/admin/model-pricing/' . (int)$modelPricing['id'] . '/update' : '/admin/model-pricing' ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

            <div class="form-group">
                <label for="provider_id">Provider <span class="text-danger">*</span></label>
                <select id="provider_id" name="provider_id" class="form-control" required>
                    <option value="">Select a provider</option>
                    <?php foreach ($providers as $provider): ?>
                    <option value="<?= (int)$provider['id'] ?>" 
                            <?= ($modelPricing['provider_id'] ?? '') == $provider['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($provider['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="model_name">Model Name <span class="text-danger">*</span></label>
                <input type="text" id="model_name" name="model_name" class="form-control" required
                       value="<?= htmlspecialchars($modelPricing['model_name'] ?? '') ?>"
                       placeholder="e.g., gpt-4, claude-3-opus, codex-5.4">
                <small class="form-text text-muted">The model identifier used in API requests</small>
            </div>

            <div class="form-group">
                <label for="input_price_per_1k">Input Price (per 1K tokens)</label>
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" id="input_price_per_1k" name="input_price_per_1k" class="form-control"
                           step="0.000001" min="0" value="<?= number_format((float)($modelPricing['input_price_per_1k'] ?? 0), 6, '.', '') ?>">
                </div>
                <small class="form-text text-muted">Cost per 1,000 input tokens</small>
            </div>

            <div class="form-group">
                <label for="output_price_per_1k">Output Price (per 1K tokens)</label>
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" id="output_price_per_1k" name="output_price_per_1k" class="form-control"
                           step="0.000001" min="0" value="<?= number_format((float)($modelPricing['output_price_per_1k'] ?? 0), 6, '.', '') ?>">
                </div>
                <small class="form-text text-muted">Cost per 1,000 output tokens</small>
            </div>

            <div class="form-group">
                <div class="form-check">
                    <input type="checkbox" id="is_active" name="is_active" class="form-check-input"
                           <?= (!$isEdit || !empty($modelPricing['is_active'])) ? 'checked' : '' ?>>
                    <label for="is_active" class="form-check-label">Active</label>
                    <small class="form-text text-muted">Inactive models will not be available for API requests</small>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="icon-save"></i> <?= $isEdit ? 'Update Pricing' : 'Add Model' ?>
                </button>
                <a href="/admin/model-pricing" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
