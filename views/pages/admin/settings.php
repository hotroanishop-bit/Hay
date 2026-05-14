<?php
/**
 * Admin Settings Page
 * Variables: $pageTitle, $currentPage, $config
 */
?>

<div class="page-header">
    <div class="page-header-content">
        <h1>System Settings</h1>
        <p>Configure application settings</p>
    </div>
</div>

<form action="/admin/settings" method="POST">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

    <div class="card mb-4">
        <div class="card-header">
            <h3>General Settings</h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label for="app_name">Application Name</label>
                <input type="text" id="app_name" name="app_name" class="form-control" 
                       value="<?= htmlspecialchars($config['app_name'] ?? 'API Keys Platform') ?>">
            </div>

            <div class="form-group">
                <div class="form-check">
                    <input type="checkbox" id="maintenance_mode" name="maintenance_mode" class="form-check-input"
                           <?= !empty($config['maintenance_mode']) ? 'checked' : '' ?>>
                    <label for="maintenance_mode" class="form-check-label">Maintenance Mode</label>
                    <small class="form-text text-muted">When enabled, only admins can access the site</small>
                </div>
            </div>

            <div class="form-group">
                <div class="form-check">
                    <input type="checkbox" id="registration_enabled" name="registration_enabled" class="form-check-input"
                           <?= ($config['registration_enabled'] ?? true) ? 'checked' : '' ?>>
                    <label for="registration_enabled" class="form-check-label">Allow User Registration</label>
                    <small class="form-text text-muted">When disabled, new users cannot register</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h3>Payment Settings</h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label for="default_credits">Default Credits for New Users</label>
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" id="default_credits" name="default_credits" class="form-control" 
                           step="0.01" min="0" value="<?= number_format($config['default_credits'] ?? 0, 2) ?>">
                </div>
                <small class="form-text text-muted">Initial credits given to new users upon registration</small>
            </div>

            <div class="form-group">
                <label for="min_payment">Minimum Payment Amount</label>
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" id="min_payment" name="min_payment" class="form-control" 
                           step="0.01" min="1" value="<?= number_format($config['min_payment'] ?? 5, 2) ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="max_payment">Maximum Payment Amount</label>
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" id="max_payment" name="max_payment" class="form-control" 
                           step="0.01" min="1" value="<?= number_format($config['max_payment'] ?? 1000, 2) ?>">
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h3>Rate Limiting</h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <div class="form-check">
                    <input type="checkbox" id="rate_limit_enabled" name="rate_limit_enabled" class="form-check-input"
                           <?= ($config['rate_limit_enabled'] ?? true) ? 'checked' : '' ?>>
                    <label for="rate_limit_enabled" class="form-check-label">Enable Rate Limiting</label>
                </div>
            </div>

            <div class="form-group">
                <label for="default_rate_limit">Default Rate Limit (requests/minute)</label>
                <input type="number" id="default_rate_limit" name="default_rate_limit" class="form-control" 
                       min="1" max="10000" value="<?= (int)($config['default_rate_limit'] ?? 60) ?>">
            </div>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">
            <i class="icon-save"></i> Save Settings
        </button>
        <button type="reset" class="btn btn-secondary">Reset</button>
    </div>
</form>
