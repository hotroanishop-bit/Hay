<?php
/**
 * Admin Coupon Form Page
 * Variables: $pageTitle, $currentPage, $coupon, $isEdit
 */
?>

<div class="page-header">
    <div class="page-header-content">
        <a href="/admin/coupons" class="back-link">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            Back to Coupons
        </a>
        <h1><?= $isEdit ? 'Edit Coupon' : 'Create Coupon' ?></h1>
        <p><?= $isEdit ? 'Update coupon details' : 'Create a new promotional coupon code' ?></p>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="<?= $isEdit ? '/admin/coupons/' . (int)$coupon['id'] . '/update' : '/admin/coupons' ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="code">Coupon Code <span class="text-danger">*</span></label>
                    <input type="text" id="code" name="code" class="form-control" required
                           value="<?= htmlspecialchars($coupon['code'] ?? '') ?>"
                           placeholder="e.g., SUMMER20, WELCOME10"
                           maxlength="50"
                           style="text-transform: uppercase;">
                    <small class="form-text text-muted">Unique code users will enter. Will be converted to uppercase.</small>
                </div>
                <div class="form-group col-md-6">
                    <label for="type">Coupon Type <span class="text-danger">*</span></label>
                    <select id="type" name="type" class="form-control" required>
                        <option value="percentage" <?= ($coupon['type'] ?? '') === 'percentage' ? 'selected' : '' ?>>Percentage Discount</option>
                        <option value="fixed" <?= ($coupon['type'] ?? '') === 'fixed' ? 'selected' : '' ?>>Fixed Amount Discount</option>
                        <option value="bonus" <?= ($coupon['type'] ?? '') === 'bonus' ? 'selected' : '' ?>>Bonus Credits</option>
                    </select>
                    <small class="form-text text-muted">
                        <strong>Percentage:</strong> Discount % off deposit amount<br>
                        <strong>Fixed:</strong> Fixed VND discount off deposit<br>
                        <strong>Bonus:</strong> Extra credits added to balance
                    </small>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="value">Value <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" id="value" name="value" class="form-control" required
                               step="0.01" min="0.01"
                               value="<?= number_format((float)($coupon['value'] ?? 0), 2, '.', '') ?>">
                        <span class="input-group-text" id="valueUnit">%</span>
                    </div>
                    <small class="form-text text-muted" id="valueHelp">Percentage discount (e.g., 10 = 10% off)</small>
                </div>
                <div class="form-group col-md-6">
                    <label for="min_amount">Minimum Deposit Amount (VND)</label>
                    <input type="number" id="min_amount" name="min_amount" class="form-control"
                           step="1000" min="0"
                           value="<?= (int)($coupon['min_amount'] ?? 0) ?>">
                    <small class="form-text text-muted">Minimum deposit required to use this coupon. 0 = no minimum.</small>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="max_uses">Total Usage Limit</label>
                    <input type="number" id="max_uses" name="max_uses" class="form-control"
                           min="0" value="<?= (int)($coupon['max_uses'] ?? 0) ?>">
                    <small class="form-text text-muted">Maximum total uses. 0 = unlimited.</small>
                </div>
                <div class="form-group col-md-4">
                    <label for="max_uses_per_user">Per User Limit</label>
                    <input type="number" id="max_uses_per_user" name="max_uses_per_user" class="form-control"
                           min="0" value="<?= (int)($coupon['max_uses_per_user'] ?? 1) ?>">
                    <small class="form-text text-muted">Maximum uses per user. 0 = unlimited.</small>
                </div>
                <div class="form-group col-md-4">
                    <label for="expires_at">Expiration Date</label>
                    <input type="datetime-local" id="expires_at" name="expires_at" class="form-control"
                           value="<?= !empty($coupon['expires_at']) ? date('Y-m-d\TH:i', strtotime($coupon['expires_at'])) : '' ?>">
                    <small class="form-text text-muted">Leave empty for no expiration.</small>
                </div>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="3"
                          placeholder="Internal description or terms for this coupon"><?= htmlspecialchars($coupon['description'] ?? '') ?></textarea>
                <small class="form-text text-muted">Internal notes about this coupon (not shown to users).</small>
            </div>

            <div class="form-group">
                <div class="form-check">
                    <input type="checkbox" id="is_active" name="is_active" class="form-check-input"
                           <?= (!$isEdit || !empty($coupon['is_active'])) ? 'checked' : '' ?>>
                    <label for="is_active" class="form-check-label"><strong>Active</strong></label>
                    <small class="form-text text-muted">Inactive coupons cannot be used by customers</small>
                </div>
            </div>

            <?php if ($isEdit && !empty($coupon['used_count'])): ?>
            <div class="alert alert-info">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                <span>This coupon has been used <?= number_format((int)$coupon['used_count']) ?> times. Changes will only affect future uses.</span>
            </div>
            <?php endif; ?>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                    <?= $isEdit ? 'Update Coupon' : 'Create Coupon' ?>
                </button>
                <a href="/admin/coupons" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<style>
.back-link {
    display: inline-flex;
    align-items: center;
    gap: var(--space-1);
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
    margin-bottom: var(--space-2);
    transition: color var(--transition-fast);
}

.back-link:hover {
    color: var(--color-primary);
}

.form-actions {
    display: flex;
    gap: var(--space-3);
    margin-top: var(--space-6);
    padding-top: var(--space-4);
    border-top: 1px solid var(--border-color);
}

.alert {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    padding: var(--space-3) var(--space-4);
    border-radius: var(--radius-lg);
    margin-bottom: var(--space-4);
}

.alert-info {
    background: var(--color-info-light);
    color: var(--color-info-dark);
    border: 1px solid var(--color-info);
}
</style>

<script>
// Update value unit and help text based on coupon type
var typeSelect = document.getElementById('type');
var valueUnit = document.getElementById('valueUnit');
var valueHelp = document.getElementById('valueHelp');
var valueInput = document.getElementById('value');

typeSelect.addEventListener('change', updateValueLabels);

function updateValueLabels() {
    var type = typeSelect.value;
    
    switch (type) {
        case 'percentage':
            valueUnit.textContent = '%';
            valueHelp.textContent = 'Percentage discount (e.g., 10 = 10% off deposit amount)';
            valueInput.max = 100;
            break;
        case 'fixed':
            valueUnit.textContent = 'VND';
            valueHelp.textContent = 'Fixed discount amount in VND';
            valueInput.removeAttribute('max');
            break;
        case 'bonus':
            valueUnit.textContent = 'VND';
            valueHelp.textContent = 'Bonus credits added to user balance after deposit';
            valueInput.removeAttribute('max');
            break;
    }
}

// Initialize on page load
updateValueLabels();

// Auto-uppercase coupon code
document.getElementById('code').addEventListener('input', function() {
    this.value = this.value.toUpperCase();
});
</script>
