<?php
/**
 * Admin Settings Page - Enhanced Professional UI
 * Grouped settings in cards with toggle switches and validation
 * 
 * Variables: $pageTitle, $currentPage, $settings, $plans, $bankList
 */
?>

<div class="page-header">
    <div class="page-header-content">
        <div>
            <h1>System Settings</h1>
            <p class="text-muted">Configure all application settings</p>
        </div>
        <div class="page-header-actions">
            <button type="button" class="btn btn-outline btn-sm" onclick="resetAllSettings()">
                <i class="icon-refresh-cw"></i> Reset to Defaults
            </button>
        </div>
    </div>
</div>

<form id="settingsForm" action="/admin/settings" method="POST">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

    <div class="settings-grid">
        <!-- Left Column -->
        <div class="settings-column">
            
            <!-- Site Settings Card -->
            <div class="settings-card card">
                <div class="settings-card-header">
                    <div class="settings-card-icon">
                        <i class="icon-globe"></i>
                    </div>
                    <div class="settings-card-title">
                        <h3>Site Settings</h3>
                        <p>Basic site configuration</p>
                    </div>
                </div>
                <div class="settings-card-body">
                    <div class="form-group">
                        <label class="form-label required" for="site_name">Site Name</label>
                        <input type="text" id="site_name" name="site_name" class="form-input" required
                               value="<?= htmlspecialchars($settings['site_name'] ?? 'Hay API Platform') ?>"
                               placeholder="Your Site Name">
                    </div>

                    <div class="form-group">
                        <label class="form-label required" for="site_url">Site URL</label>
                        <input type="url" id="site_url" name="site_url" class="form-input" required
                               value="<?= htmlspecialchars($settings['site_url'] ?? 'http://localhost') ?>"
                               placeholder="https://yourdomain.com">
                        <small class="form-help">The base URL of your application (no trailing slash)</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="logo_url">Logo URL</label>
                        <input type="url" id="logo_url" name="logo_url" class="form-input"
                               value="<?= htmlspecialchars($settings['logo_url'] ?? '') ?>"
                               placeholder="https://yourdomain.com/logo.png">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="favicon_url">Favicon URL</label>
                        <input type="url" id="favicon_url" name="favicon_url" class="form-input"
                               value="<?= htmlspecialchars($settings['favicon_url'] ?? '') ?>"
                               placeholder="https://yourdomain.com/favicon.ico">
                    </div>
                </div>
            </div>

            <!-- Payment Settings Card -->
            <div class="settings-card card">
                <div class="settings-card-header">
                    <div class="settings-card-icon settings-card-icon-success">
                        <i class="icon-credit-card"></i>
                    </div>
                    <div class="settings-card-title">
                        <h3>Payment Settings</h3>
                        <p>VietQR bank transfer configuration</p>
                    </div>
                </div>
                <div class="settings-card-body">
                    <div class="info-banner info-banner-info">
                        <i class="icon-info"></i>
                        <span>This bank information will be used for VietQR deposits. Customers will scan the QR code to transfer money to this account.</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label required" for="bank_name">Bank Name</label>
                        <select id="bank_name" name="bank_name" class="form-input" required>
                            <option value="">-- Select Bank --</option>
                            <?php foreach ($bankList as $bank): ?>
                                <option value="<?= htmlspecialchars($bank['id']) ?>"
                                        <?= ($settings['bank_name'] ?? '') === $bank['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($bank['name']) ?> (<?= htmlspecialchars($bank['id']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label required" for="bank_account_number">Account Number</label>
                        <input type="text" id="bank_account_number" name="bank_account_number" class="form-input" required
                               value="<?= htmlspecialchars($settings['bank_account_number'] ?? '') ?>"
                               placeholder="0123456789">
                    </div>

                    <div class="form-group">
                        <label class="form-label required" for="account_holder_name">Account Holder Name</label>
                        <input type="text" id="account_holder_name" name="account_holder_name" class="form-input" required
                               value="<?= htmlspecialchars($settings['account_holder_name'] ?? '') ?>"
                               placeholder="NGUYEN VAN A" style="text-transform: uppercase;">
                        <small class="form-help">Name as it appears on the bank account (uppercase)</small>
                    </div>
                </div>
            </div>

            <!-- Limits Settings Card -->
            <div class="settings-card card">
                <div class="settings-card-header">
                    <div class="settings-card-icon settings-card-icon-warning">
                        <i class="icon-sliders"></i>
                    </div>
                    <div class="settings-card-title">
                        <h3>Limits Settings</h3>
                        <p>Default plans and deposit limits</p>
                    </div>
                </div>
                <div class="settings-card-body">
                    <div class="form-group">
                        <label class="form-label" for="default_plan_id">Default Plan for New Users</label>
                        <select id="default_plan_id" name="default_plan_id" class="form-input">
                            <option value="">-- No Default Plan --</option>
                            <?php foreach ($plans as $plan): ?>
                                <option value="<?= (int)$plan['id'] ?>"
                                        <?= (int)($settings['default_plan_id'] ?? 0) === (int)$plan['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($plan['name']) ?> 
                                    (<?= $plan['price_monthly'] > 0 ? number_format($plan['price_monthly'], 0, ',', '.') . ' VND/month' : 'Free' ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-help">Plan assigned to new users upon registration</small>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="min_deposit">Minimum Deposit</label>
                            <div class="input-group">
                                <input type="text" id="min_deposit" name="min_deposit" class="form-input vnd-input"
                                       value="<?= number_format((int)($settings['min_deposit'] ?? 10000), 0, ',', '.') ?>"
                                       data-raw-value="<?= (int)($settings['min_deposit'] ?? 10000) ?>"
                                       placeholder="10,000">
                                <span class="input-group-text">VND</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="max_deposit">Maximum Deposit</label>
                            <div class="input-group">
                                <input type="text" id="max_deposit" name="max_deposit" class="form-input vnd-input"
                                       value="<?= number_format((int)($settings['max_deposit'] ?? 50000000), 0, ',', '.') ?>"
                                       data-raw-value="<?= (int)($settings['max_deposit'] ?? 50000000) ?>"
                                       placeholder="50,000,000">
                                <span class="input-group-text">VND</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Column -->
        <div class="settings-column">

            <!-- API / Maintenance Settings Card -->
            <div class="settings-card card">
                <div class="settings-card-header">
                    <div class="settings-card-icon settings-card-icon-danger">
                        <i class="icon-tool"></i>
                    </div>
                    <div class="settings-card-title">
                        <h3>API Settings</h3>
                        <p>Maintenance mode and API configuration</p>
                    </div>
                </div>
                <div class="settings-card-body">
                    <div class="toggle-setting">
                        <div class="toggle-info">
                            <label class="toggle-label" for="maintenance_mode">Maintenance Mode</label>
                            <p class="toggle-description">When enabled, only admins can access the site</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" id="maintenance_mode" name="maintenance_mode"
                                   <?= !empty($settings['maintenance_mode']) ? 'checked' : '' ?>
                                   onchange="toggleMaintenanceWarning(this)">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                    <div id="maintenance-warning" class="info-banner info-banner-warning" 
                         style="display: <?= !empty($settings['maintenance_mode']) ? 'flex' : 'none' ?>;">
                        <i class="icon-alert-triangle"></i>
                        <span>All regular users will see the maintenance message below.</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="maintenance_message">Maintenance Message</label>
                        <textarea id="maintenance_message" name="maintenance_message" class="form-input form-textarea" rows="3"
                                  placeholder="We are currently performing scheduled maintenance..."><?= htmlspecialchars($settings['maintenance_message'] ?? 'We are currently performing scheduled maintenance. Please check back soon.') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Email Settings Card -->
            <div class="settings-card card">
                <div class="settings-card-header">
                    <div class="settings-card-icon settings-card-icon-info">
                        <i class="icon-mail"></i>
                    </div>
                    <div class="settings-card-title">
                        <h3>Email Settings</h3>
                        <p>SMTP configuration for sending emails</p>
                    </div>
                </div>
                <div class="settings-card-body">
                    <div class="form-row">
                        <div class="form-group" style="flex: 2;">
                            <label class="form-label" for="smtp_host">SMTP Host</label>
                            <input type="text" id="smtp_host" name="smtp_host" class="form-input"
                                   value="<?= htmlspecialchars($settings['smtp_host'] ?? 'smtp.gmail.com') ?>"
                                   placeholder="smtp.gmail.com">
                        </div>

                        <div class="form-group" style="flex: 1;">
                            <label class="form-label" for="smtp_port">Port</label>
                            <input type="number" id="smtp_port" name="smtp_port" class="form-input"
                                   value="<?= (int)($settings['smtp_port'] ?? 587) ?>"
                                   min="1" max="65535" placeholder="587">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="smtp_username">SMTP Username</label>
                        <input type="text" id="smtp_username" name="smtp_username" class="form-input"
                               value="<?= htmlspecialchars($settings['smtp_username'] ?? '') ?>"
                               placeholder="your-email@gmail.com">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="smtp_password">SMTP Password</label>
                        <div class="input-group">
                            <input type="password" id="smtp_password" name="smtp_password" class="form-input"
                                   placeholder="Leave empty to keep existing">
                            <button type="button" class="btn btn-secondary btn-input-addon" onclick="togglePassword('smtp_password')">
                                <i class="icon-eye" id="smtp_password_icon"></i>
                            </button>
                        </div>
                        <small class="form-help">Leave empty to keep the existing password</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="smtp_encryption">Encryption</label>
                        <div class="radio-group">
                            <label class="radio-option">
                                <input type="radio" name="smtp_encryption" value="tls" 
                                       <?= ($settings['smtp_encryption'] ?? 'tls') === 'tls' ? 'checked' : '' ?>>
                                <span class="radio-label">TLS</span>
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="smtp_encryption" value="ssl"
                                       <?= ($settings['smtp_encryption'] ?? '') === 'ssl' ? 'checked' : '' ?>>
                                <span class="radio-label">SSL</span>
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="smtp_encryption" value="none"
                                       <?= ($settings['smtp_encryption'] ?? '') === 'none' ? 'checked' : '' ?>>
                                <span class="radio-label">None</span>
                            </label>
                        </div>
                    </div>

                    <button type="button" class="btn btn-secondary btn-sm" disabled>
                        <i class="icon-send"></i> Test Connection
                    </button>
                </div>
            </div>

        </div>
    </div>

    <!-- Form Actions (Sticky Footer) -->
    <div class="settings-footer">
        <div class="settings-footer-content">
            <div class="unsaved-indicator" id="unsavedIndicator" style="display: none;">
                <span class="unsaved-dot"></span>
                <span>You have unsaved changes</span>
            </div>
            <div class="settings-footer-actions">
                <button type="reset" class="btn btn-secondary" onclick="return confirmReset()">
                    <i class="icon-x"></i> Discard Changes
                </button>
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="icon-save"></i> Save All Settings
                </button>
            </div>
        </div>
    </div>
</form>

<style>
.settings-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-6);
    margin-bottom: 100px;
}

.settings-column {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-6);
}

.settings-card {
    overflow: hidden;
}

.settings-card-header {
    display: flex;
    gap: var(--spacing-4);
    padding: var(--spacing-5);
    background: var(--color-gray-50);
    border-bottom: 1px solid var(--color-gray-200);
}

.settings-card-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(79, 70, 229, 0.1);
    color: var(--color-primary);
    border-radius: var(--radius-lg);
    font-size: var(--font-size-xl);
    flex-shrink: 0;
}

.settings-card-icon-success { background: rgba(34, 197, 94, 0.1); color: var(--color-success); }
.settings-card-icon-warning { background: rgba(245, 158, 11, 0.1); color: var(--color-warning); }
.settings-card-icon-danger { background: rgba(239, 68, 68, 0.1); color: var(--color-error); }
.settings-card-icon-info { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }

.settings-card-title h3 {
    margin: 0 0 var(--spacing-1) 0;
    font-size: var(--font-size-base);
    font-weight: var(--font-weight-semibold);
    color: var(--color-gray-900);
}

.settings-card-title p {
    margin: 0;
    font-size: var(--font-size-sm);
    color: var(--color-gray-500);
}

.settings-card-body {
    padding: var(--spacing-5);
}

.form-row {
    display: flex;
    gap: var(--spacing-4);
}

.form-row .form-group {
    flex: 1;
}

.info-banner {
    display: flex;
    align-items: flex-start;
    gap: var(--spacing-3);
    padding: var(--spacing-3) var(--spacing-4);
    border-radius: var(--radius-md);
    font-size: var(--font-size-sm);
    margin-bottom: var(--spacing-4);
}

.info-banner i {
    flex-shrink: 0;
    margin-top: 2px;
}

.info-banner-info {
    background: rgba(59, 130, 246, 0.1);
    color: #1e40af;
}

.info-banner-warning {
    background: rgba(245, 158, 11, 0.1);
    color: #b45309;
}

.toggle-setting {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--spacing-4);
    background: var(--color-gray-50);
    border-radius: var(--radius-md);
    margin-bottom: var(--spacing-4);
}

.toggle-info {
    flex: 1;
}

.toggle-label {
    display: block;
    font-weight: var(--font-weight-medium);
    color: var(--color-gray-900);
    margin-bottom: var(--spacing-1);
}

.toggle-description {
    margin: 0;
    font-size: var(--font-size-sm);
    color: var(--color-gray-500);
}

.toggle-switch {
    position: relative;
    display: inline-block;
    width: 48px;
    height: 28px;
}

.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: var(--color-gray-300);
    transition: all var(--transition-fast);
    border-radius: 28px;
}

.toggle-slider:before {
    position: absolute;
    content: "";
    height: 22px;
    width: 22px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: all var(--transition-fast);
    border-radius: 50%;
    box-shadow: var(--shadow-sm);
}

.toggle-switch input:checked + .toggle-slider {
    background-color: var(--color-primary);
}

.toggle-switch input:checked + .toggle-slider:before {
    transform: translateX(20px);
}

.radio-group {
    display: flex;
    gap: var(--spacing-4);
}

.radio-option {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    cursor: pointer;
}

.radio-option input[type="radio"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.radio-label {
    font-size: var(--font-size-sm);
    color: var(--color-gray-700);
}

.input-group {
    display: flex;
}

.input-group .form-input {
    border-radius: var(--radius-md) 0 0 var(--radius-md);
    border-right: none;
}

.input-group-text {
    padding: var(--spacing-2) var(--spacing-3);
    background: var(--color-gray-100);
    border: 1px solid var(--color-gray-300);
    border-radius: 0 var(--radius-md) var(--radius-md) 0;
    font-size: var(--font-size-sm);
    color: var(--color-gray-600);
    display: flex;
    align-items: center;
}

.btn-input-addon {
    border-radius: 0 var(--radius-md) var(--radius-md) 0;
    border-left: none;
}

.settings-footer {
    position: fixed;
    bottom: 0;
    left: 250px;
    right: 0;
    background: var(--color-white);
    border-top: 1px solid var(--color-gray-200);
    box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.1);
    z-index: 100;
}

.settings-footer-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--spacing-4) var(--spacing-6);
    max-width: 1400px;
    margin: 0 auto;
}

.unsaved-indicator {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    font-size: var(--font-size-sm);
    color: var(--color-warning);
}

.unsaved-dot {
    width: 8px;
    height: 8px;
    background: var(--color-warning);
    border-radius: var(--radius-full);
    animation: pulse 1.5s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.4; }
}

.settings-footer-actions {
    display: flex;
    gap: var(--spacing-3);
}

@media (max-width: 1024px) {
    .settings-grid {
        grid-template-columns: 1fr;
    }
    
    .settings-footer {
        left: 0;
    }
}

@media (max-width: 768px) {
    .form-row {
        flex-direction: column;
    }
    
    .settings-footer-content {
        flex-direction: column;
        gap: var(--spacing-3);
    }
    
    .settings-footer-actions {
        width: 100%;
        justify-content: flex-end;
    }
}
</style>

<script>
// Track form changes
let formChanged = false;

document.getElementById('settingsForm').addEventListener('input', function() {
    formChanged = true;
    document.getElementById('unsavedIndicator').style.display = 'flex';
});

// Warn before leaving with unsaved changes
window.addEventListener('beforeunload', function(e) {
    if (formChanged) {
        e.preventDefault();
        e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
        return e.returnValue;
    }
});

// Reset the unsaved indicator on form submit
document.getElementById('settingsForm').addEventListener('submit', function() {
    formChanged = false;
});

// Toggle password visibility
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '_icon');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('icon-eye');
        icon.classList.add('icon-eye-off');
    } else {
        field.type = 'password';
        icon.classList.remove('icon-eye-off');
        icon.classList.add('icon-eye');
    }
}

// Toggle maintenance warning
function toggleMaintenanceWarning(checkbox) {
    const warning = document.getElementById('maintenance-warning');
    warning.style.display = checkbox.checked ? 'flex' : 'none';
}

// VND currency formatting
const vndInputs = document.querySelectorAll('.vnd-input');
vndInputs.forEach(function(input) {
    input.addEventListener('input', function(e) {
        let value = e.target.value.replace(/[^\d]/g, '');
        e.target.dataset.rawValue = value;
        if (value) {
            e.target.value = parseInt(value).toLocaleString('vi-VN');
        }
    });
    
    input.addEventListener('blur', function(e) {
        let value = e.target.dataset.rawValue || e.target.value.replace(/[^\d]/g, '');
        if (value) {
            e.target.value = parseInt(value).toLocaleString('vi-VN');
            e.target.dataset.rawValue = value;
        }
    });
});

// Form submission - convert VND inputs to raw values
document.getElementById('settingsForm').addEventListener('submit', function(e) {
    vndInputs.forEach(function(input) {
        input.value = input.dataset.rawValue || input.value.replace(/[^\d]/g, '');
    });
    
    // Validate min/max deposit
    const minDeposit = parseInt(document.getElementById('min_deposit').value || 0);
    const maxDeposit = parseInt(document.getElementById('max_deposit').value || 0);
    
    if (minDeposit > maxDeposit) {
        e.preventDefault();
        alert('Minimum deposit cannot be greater than maximum deposit');
        return false;
    }
});

// Confirm reset
function confirmReset() {
    if (confirm('Are you sure you want to discard all changes?')) {
        formChanged = false;
        return true;
    }
    return false;
}

function resetAllSettings() {
    if (confirm('This will reset all settings to their default values. Are you sure?')) {
        // Would call an API endpoint to reset settings
        alert('Feature not yet implemented');
    }
}
</script>
