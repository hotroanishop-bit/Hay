<?php
/**
 * Create API Key Page
 * Variables: $pageTitle, $currentPage, $providers
 */
?>

<div class="page-header">
    <div class="page-header-content">
        <h1>Create API Key</h1>
        <p>Generate a new API key for accessing AI services</p>
    </div>
    <div class="page-header-actions">
        <a href="/keys" class="btn btn-secondary">
            <i class="icon-arrow-left"></i> Back to Keys
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="/keys" method="POST" class="form-horizontal">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

            <div class="form-group">
                <label for="name">Key Name <span class="text-danger">*</span></label>
                <input type="text" id="name" name="name" class="form-control" placeholder="e.g., Production API Key" required>
                <small class="form-text text-muted">A descriptive name to identify this key</small>
            </div>

            <div class="form-group">
                <label for="provider">Provider</label>
                <select id="provider" name="provider" class="form-control">
                    <option value="">All Providers</option>
                    <?php foreach ($providers ?? [] as $provider): ?>
                    <option value="<?= htmlspecialchars($provider['id'] ?? '') ?>">
                        <?= htmlspecialchars($provider['name'] ?? '') ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <small class="form-text text-muted">Restrict this key to a specific AI provider (optional)</small>
            </div>

            <div class="form-group">
                <label for="model">Model</label>
                <select id="model" name="model" class="form-control">
                    <option value="">All Models</option>
                </select>
                <small class="form-text text-muted">Restrict this key to a specific model (optional)</small>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="rate_limit">Rate Limit (requests/minute)</label>
                    <input type="number" id="rate_limit" name="rate_limit" class="form-control" min="1" max="10000" placeholder="60">
                    <small class="form-text text-muted">Maximum requests per minute (leave empty for no limit)</small>
                </div>

                <div class="form-group col-md-6">
                    <label for="usage_limit">Usage Limit (total requests)</label>
                    <input type="number" id="usage_limit" name="usage_limit" class="form-control" min="1" placeholder="10000">
                    <small class="form-text text-muted">Maximum total requests (leave empty for unlimited)</small>
                </div>
            </div>

            <div class="form-group">
                <label for="expires_at">Expiration Date</label>
                <input type="date" id="expires_at" name="expires_at" class="form-control" min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                <small class="form-text text-muted">Key will automatically be deactivated after this date (optional)</small>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="icon-plus"></i> Create API Key
                </button>
                <a href="/keys" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <h3>Security Tips</h3>
    </div>
    <div class="card-body">
        <ul class="list-unstyled">
            <li><i class="icon-check text-success"></i> Never share your API keys publicly or commit them to version control</li>
            <li><i class="icon-check text-success"></i> Use environment variables to store keys in your applications</li>
            <li><i class="icon-check text-success"></i> Set appropriate rate limits to prevent abuse</li>
            <li><i class="icon-check text-success"></i> Rotate keys regularly for enhanced security</li>
            <li><i class="icon-check text-success"></i> Create separate keys for different environments (dev, staging, production)</li>
        </ul>
    </div>
</div>
