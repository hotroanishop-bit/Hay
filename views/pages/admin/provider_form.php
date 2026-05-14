<?php
/**
 * Admin Provider Form Page
 * Variables: $pageTitle, $currentPage, $provider, $isEdit
 */
?>

<div class="page-header">
    <div class="page-header-content">
        <h1><?= $isEdit ? 'Edit Provider' : 'Add Provider' ?></h1>
        <p><?= $isEdit ? 'Update provider details' : 'Add a new AI API provider' ?></p>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="<?= $isEdit ? '/admin/providers/' . (int)$provider['id'] . '/update' : '/admin/providers' ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

            <div class="form-group">
                <label for="name">Provider Name <span class="text-danger">*</span></label>
                <input type="text" id="name" name="name" class="form-control" required
                       value="<?= htmlspecialchars($provider['name'] ?? '') ?>"
                       placeholder="e.g., OpenAI, Anthropic, LoadIP">
            </div>

            <div class="form-group">
                <label for="base_url">Base URL <span class="text-danger">*</span></label>
                <input type="url" id="base_url" name="base_url" class="form-control" required
                       value="<?= htmlspecialchars($provider['base_url'] ?? '') ?>"
                       placeholder="e.g., https://api.openai.com/v1">
                <small class="form-text text-muted">The base URL for API requests (without trailing slash)</small>
            </div>

            <div class="form-group">
                <label for="api_key">API Key <?= $isEdit ? '' : '<span class="text-danger">*</span>' ?></label>
                <input type="password" id="api_key" name="api_key" class="form-control"
                       placeholder="<?= $isEdit ? 'Leave blank to keep current key' : 'Enter API key' ?>"
                       <?= $isEdit ? '' : 'required' ?>>
                <small class="form-text text-muted">
                    <?php if ($isEdit): ?>
                    Leave blank to keep the existing API key. Enter a new value to update it.
                    <?php else: ?>
                    The API key will be encrypted before storing.
                    <?php endif; ?>
                </small>
            </div>

            <div class="form-group">
                <div class="form-check">
                    <input type="checkbox" id="is_active" name="is_active" class="form-check-input"
                           <?= (!$isEdit || !empty($provider['is_active'])) ? 'checked' : '' ?>>
                    <label for="is_active" class="form-check-label">Active</label>
                    <small class="form-text text-muted">Inactive providers will not be used for API requests</small>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="icon-save"></i> <?= $isEdit ? 'Update Provider' : 'Add Provider' ?>
                </button>
                <a href="/admin/providers" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
