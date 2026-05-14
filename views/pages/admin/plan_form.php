<?php
/**
 * Admin Plan Form Page
 * Variables: $pageTitle, $currentPage, $plan, $isEdit
 */
?>

<div class="page-header">
    <div class="page-header-content">
        <h1><?= $isEdit ? 'Edit Plan' : 'Create Plan' ?></h1>
        <p><?= $isEdit ? 'Update plan details' : 'Create a new subscription plan' ?></p>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="<?= $isEdit ? '/admin/plans/' . (int)$plan['id'] . '/update' : '/admin/plans' ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

            <div class="form-group">
                <label for="name">Plan Name <span class="text-danger">*</span></label>
                <input type="text" id="name" name="name" class="form-control" required
                       value="<?= htmlspecialchars($plan['name'] ?? '') ?>"
                       placeholder="e.g., Basic, Pro, Enterprise">
            </div>

            <div class="form-group">
                <label for="price_monthly">Monthly Price ($)</label>
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" id="price_monthly" name="price_monthly" class="form-control"
                           step="0.01" min="0" value="<?= number_format((float)($plan['price_monthly'] ?? 0), 2, '.', '') ?>">
                </div>
                <small class="form-text text-muted">Set to 0 for free plans</small>
            </div>

            <div class="form-group">
                <label for="rate_limit_per_minute">Rate Limit (requests/minute)</label>
                <input type="number" id="rate_limit_per_minute" name="rate_limit_per_minute" class="form-control"
                       min="1" max="10000" value="<?= (int)($plan['rate_limit_per_minute'] ?? 60) ?>">
                <small class="form-text text-muted">Maximum API requests allowed per minute</small>
            </div>

            <div class="form-group">
                <label for="daily_token_limit">Daily Token Limit</label>
                <input type="number" id="daily_token_limit" name="daily_token_limit" class="form-control"
                       min="0" value="<?= (int)($plan['daily_token_limit'] ?? 100000) ?>">
                <small class="form-text text-muted">Maximum tokens allowed per day (0 for unlimited)</small>
            </div>

            <div class="form-group">
                <label for="price_multiplier">Price Multiplier</label>
                <input type="number" id="price_multiplier" name="price_multiplier" class="form-control"
                       step="0.01" min="0.1" max="10" value="<?= number_format((float)($plan['price_multiplier'] ?? 1.0), 2, '.', '') ?>">
                <small class="form-text text-muted">Multiplier applied to base model pricing (1.0 = no markup)</small>
            </div>

            <div class="form-group">
                <div class="form-check">
                    <input type="checkbox" id="is_active" name="is_active" class="form-check-input"
                           <?= (!$isEdit || !empty($plan['is_active'])) ? 'checked' : '' ?>>
                    <label for="is_active" class="form-check-label">Active</label>
                    <small class="form-text text-muted">Inactive plans are hidden from users</small>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="icon-save"></i> <?= $isEdit ? 'Update Plan' : 'Create Plan' ?>
                </button>
                <a href="/admin/plans" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
