<?php
/**
 * Admin Settings Page - Enhanced
 * Variables: $pageTitle, $currentPage, $settings, $plans, $bankList
 */
?>

<div class="page-header">
    <div class="page-header-content">
        <h1>System Settings</h1>
        <p>Configure all application settings from the database</p>
    </div>
</div>

<form id="settingsForm" action="/admin/settings" method="POST">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

    <!-- General Settings -->
    <div class="settings-card card mb-4">
        <div class="card-header collapsible" onclick="toggleSection('general')">
            <h3><span class="toggle-icon" id="icon-general">-</span> General Settings</h3>
        </div>
        <div class="card-body section-content" id="section-general">
            <div class="form-group">
                <label for="site_name">Site Name *</label>
                <input type="text" id="site_name" name="site_name" class="form-control" required
                       value="<?= htmlspecialchars($settings['site_name'] ?? 'Hay API Platform') ?>"
                       placeholder="Your Site Name">
                <small class="form-text text-muted">The name displayed in the header and browser title</small>
            </div>

            <div class="form-group">
                <label for="site_url">Site URL *</label>
                <input type="url" id="site_url" name="site_url" class="form-control" required
                       value="<?= htmlspecialchars($settings['site_url'] ?? 'http://localhost') ?>"
                       placeholder="https://yourdomain.com">
                <small class="form-text text-muted">The base URL of your application</small>
            </div>

            <div class="form-group">
                <label for="logo_url">Logo URL</label>
                <input type="url" id="logo_url" name="logo_url" class="form-control"
                       value="<?= htmlspecialchars($settings['logo_url'] ?? '') ?>"
                       placeholder="https://yourdomain.com/logo.png">
                <small class="form-text text-muted">URL to your site logo image</small>
            </div>

            <div class="form-group">
                <label for="favicon_url">Favicon URL</label>
                <input type="url" id="favicon_url" name="favicon_url" class="form-control"
                       value="<?= htmlspecialchars($settings['favicon_url'] ?? '') ?>"
                       placeholder="https://yourdomain.com/favicon.ico">
                <small class="form-text text-muted">URL to your site favicon</small>
            </div>
        </div>
    </div>

    <!-- Maintenance Settings -->
    <div class="settings-card card mb-4">
        <div class="card-header collapsible" onclick="toggleSection('maintenance')">
            <h3><span class="toggle-icon" id="icon-maintenance">-</span> Maintenance Settings</h3>
        </div>
        <div class="card-body section-content" id="section-maintenance">
            <div class="form-group">
                <div class="form-check form-switch">
                    <input type="checkbox" id="maintenance_mode" name="maintenance_mode" class="form-check-input"
                           <?= !empty($settings['maintenance_mode']) ? 'checked' : '' ?>
                           onchange="toggleMaintenanceWarning(this)">
                    <label for="maintenance_mode" class="form-check-label">Enable Maintenance Mode</label>
                </div>
                <div id="maintenance-warning" class="alert alert-warning mt-2" style="display: <?= !empty($settings['maintenance_mode']) ? 'block' : 'none' ?>;">
                    <strong>Warning!</strong> When maintenance mode is enabled, only administrators can access the site. All regular users will see the maintenance message.
                </div>
            </div>

            <div class="form-group">
                <label for="maintenance_message">Maintenance Message</label>
                <textarea id="maintenance_message" name="maintenance_message" class="form-control" rows="3"
                          placeholder="We are currently performing scheduled maintenance. Please check back soon."><?= htmlspecialchars($settings['maintenance_message'] ?? 'We are currently performing scheduled maintenance. Please check back soon.') ?></textarea>
                <small class="form-text text-muted">Message displayed to users when maintenance mode is enabled</small>
            </div>
        </div>
    </div>

    <!-- Payment Settings (VietQR) -->
    <div class="settings-card card mb-4">
        <div class="card-header collapsible" onclick="toggleSection('payment')">
            <h3><span class="toggle-icon" id="icon-payment">-</span> Payment Settings (VietQR)</h3>
        </div>
        <div class="card-body section-content" id="section-payment">
            <div class="alert alert-info">
                <i class="icon-info"></i> This bank information will be used for VietQR deposits. Customers will scan the QR code to transfer money to this account.
            </div>

            <div class="form-group">
                <label for="bank_name">Bank Name *</label>
                <select id="bank_name" name="bank_name" class="form-control" required>
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
                <label for="bank_account_number">Bank Account Number *</label>
                <input type="text" id="bank_account_number" name="bank_account_number" class="form-control" required
                       value="<?= htmlspecialchars($settings['bank_account_number'] ?? '') ?>"
                       placeholder="0123456789">
                <small class="form-text text-muted">Your bank account number for receiving deposits</small>
            </div>

            <div class="form-group">
                <label for="account_holder_name">Account Holder Name *</label>
                <input type="text" id="account_holder_name" name="account_holder_name" class="form-control" required
                       value="<?= htmlspecialchars($settings['account_holder_name'] ?? '') ?>"
                       placeholder="NGUYEN VAN A">
                <small class="form-text text-muted">Name of the bank account holder (in uppercase)</small>
            </div>
        </div>
    </div>

    <!-- Email Settings (SMTP) -->
    <div class="settings-card card mb-4">
        <div class="card-header collapsible" onclick="toggleSection('email')">
            <h3><span class="toggle-icon" id="icon-email">-</span> Email Settings (SMTP)</h3>
        </div>
        <div class="card-body section-content" id="section-email">
            <div class="form-group">
                <label for="smtp_host">SMTP Host</label>
                <input type="text" id="smtp_host" name="smtp_host" class="form-control"
                       value="<?= htmlspecialchars($settings['smtp_host'] ?? 'smtp.gmail.com') ?>"
                       placeholder="smtp.gmail.com">
            </div>

            <div class="form-group">
                <label for="smtp_port">SMTP Port</label>
                <input type="number" id="smtp_port" name="smtp_port" class="form-control"
                       value="<?= (int)($settings['smtp_port'] ?? 587) ?>"
                       min="1" max="65535" placeholder="587">
            </div>

            <div class="form-group">
                <label for="smtp_username">SMTP Username</label>
                <input type="text" id="smtp_username" name="smtp_username" class="form-control"
                       value="<?= htmlspecialchars($settings['smtp_username'] ?? '') ?>"
                       placeholder="your-email@gmail.com">
            </div>

            <div class="form-group">
                <label for="smtp_password">SMTP Password</label>
                <div class="input-group">
                    <input type="password" id="smtp_password" name="smtp_password" class="form-control"
                           placeholder="Leave empty to keep existing password">
                    <button type="button" class="btn btn-secondary" onclick="togglePassword('smtp_password')">
                        <span id="smtp_password_icon">Show</span>
                    </button>
                </div>
                <small class="form-text text-muted">Leave empty to keep the existing password</small>
            </div>

            <div class="form-group">
                <label for="smtp_encryption">SMTP Encryption</label>
                <select id="smtp_encryption" name="smtp_encryption" class="form-control">
                    <option value="tls" <?= ($settings['smtp_encryption'] ?? 'tls') === 'tls' ? 'selected' : '' ?>>TLS</option>
                    <option value="ssl" <?= ($settings['smtp_encryption'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                    <option value="none" <?= ($settings['smtp_encryption'] ?? '') === 'none' ? 'selected' : '' ?>>None</option>
                </select>
            </div>

            <div class="form-group">
                <button type="button" class="btn btn-secondary" disabled title="Test connection is available in production environment only">
                    <i class="icon-mail"></i> Test Connection
                </button>
                <small class="form-text text-muted">Test connection is disabled in development mode</small>
            </div>
        </div>
    </div>

    <!-- Limits Settings -->
    <div class="settings-card card mb-4">
        <div class="card-header collapsible" onclick="toggleSection('limits')">
            <h3><span class="toggle-icon" id="icon-limits">-</span> Limits Settings</h3>
        </div>
        <div class="card-body section-content" id="section-limits">
            <div class="form-group">
                <label for="default_plan_id">Default Plan for New Users</label>
                <select id="default_plan_id" name="default_plan_id" class="form-control">
                    <option value="">-- No Default Plan --</option>
                    <?php foreach ($plans as $plan): ?>
                        <option value="<?= (int)$plan['id'] ?>"
                                <?= (int)($settings['default_plan_id'] ?? 0) === (int)$plan['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($plan['name']) ?> 
                            (<?= $plan['price_monthly'] > 0 ? '$' . number_format($plan['price_monthly'], 2) . '/month' : 'Free' ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="form-text text-muted">The plan automatically assigned to new users upon registration</small>
            </div>

            <div class="form-group">
                <label for="min_deposit">Minimum Deposit Amount (VND)</label>
                <div class="input-group">
                    <input type="text" id="min_deposit" name="min_deposit" class="form-control vnd-input"
                           value="<?= number_format((int)($settings['min_deposit'] ?? 10000), 0, ',', '.') ?>"
                           data-raw-value="<?= (int)($settings['min_deposit'] ?? 10000) ?>"
                           placeholder="10,000">
                    <span class="input-group-text">VND</span>
                </div>
                <small class="form-text text-muted">Minimum amount users can deposit</small>
            </div>

            <div class="form-group">
                <label for="max_deposit">Maximum Deposit Amount (VND)</label>
                <div class="input-group">
                    <input type="text" id="max_deposit" name="max_deposit" class="form-control vnd-input"
                           value="<?= number_format((int)($settings['max_deposit'] ?? 50000000), 0, ',', '.') ?>"
                           data-raw-value="<?= (int)($settings['max_deposit'] ?? 50000000) ?>"
                           placeholder="50,000,000">
                    <span class="input-group-text">VND</span>
                </div>
                <small class="form-text text-muted">Maximum amount users can deposit per transaction</small>
            </div>
        </div>
    </div>

    <!-- Form Actions -->
    <div class="form-actions sticky-bottom">
        <div class="unsaved-indicator" id="unsavedIndicator" style="display: none;">
            <span class="text-warning"><i class="icon-warning"></i> You have unsaved changes</span>
        </div>
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="icon-save"></i> Save All Settings
        </button>
        <button type="reset" class="btn btn-secondary" onclick="resetForm()">Reset</button>
    </div>
</form>

<style>
.settings-card .card-header.collapsible {
    cursor: pointer;
    user-select: none;
    transition: background-color 0.2s;
}

.settings-card .card-header.collapsible:hover {
    background-color: #f0f0f0;
}

.settings-card .card-header h3 {
    margin: 0;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.toggle-icon {
    font-weight: bold;
    width: 20px;
    text-align: center;
    transition: transform 0.2s;
}

.section-content.collapsed {
    display: none;
}

.form-switch {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-switch .form-check-input {
    width: 2.5em;
    height: 1.25em;
    cursor: pointer;
}

.form-actions {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
}

.form-actions.sticky-bottom {
    position: sticky;
    bottom: 1rem;
    z-index: 100;
}

.unsaved-indicator {
    margin-right: auto;
}

.alert-info {
    background-color: #e7f3ff;
    border-color: #b6d4fe;
    color: #084298;
    padding: 0.75rem 1rem;
    border-radius: 4px;
    margin-bottom: 1rem;
}

.alert-warning {
    background-color: #fff3cd;
    border-color: #ffecb5;
    color: #856404;
    padding: 0.75rem 1rem;
    border-radius: 4px;
}

.input-group {
    display: flex;
}

.input-group .form-control {
    flex: 1;
}

.input-group .btn,
.input-group .input-group-text {
    border-radius: 0 4px 4px 0;
    margin-left: -1px;
}

.input-group .form-control {
    border-radius: 4px 0 0 4px;
}
</style>

<script>
// Track form changes
let formChanged = false;
const originalFormData = new FormData(document.getElementById('settingsForm'));

document.getElementById('settingsForm').addEventListener('input', function() {
    formChanged = true;
    document.getElementById('unsavedIndicator').style.display = 'block';
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

// Toggle section collapse/expand
function toggleSection(sectionId) {
    const section = document.getElementById('section-' + sectionId);
    const icon = document.getElementById('icon-' + sectionId);
    
    if (section.classList.contains('collapsed')) {
        section.classList.remove('collapsed');
        icon.textContent = '-';
    } else {
        section.classList.add('collapsed');
        icon.textContent = '+';
    }
}

// Toggle password visibility
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '_icon');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.textContent = 'Hide';
    } else {
        field.type = 'password';
        icon.textContent = 'Show';
    }
}

// Toggle maintenance warning
function toggleMaintenanceWarning(checkbox) {
    const warning = document.getElementById('maintenance-warning');
    warning.style.display = checkbox.checked ? 'block' : 'none';
}

// VND currency formatting
const vndInputs = document.querySelectorAll('.vnd-input');
vndInputs.forEach(function(input) {
    input.addEventListener('input', function(e) {
        // Remove all non-numeric characters
        let value = e.target.value.replace(/[^\d]/g, '');
        
        // Store raw value
        e.target.dataset.rawValue = value;
        
        // Format with thousand separators
        if (value) {
            e.target.value = parseInt(value).toLocaleString('vi-VN');
        }
    });
    
    input.addEventListener('blur', function(e) {
        // Ensure the value is formatted on blur
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
});

// Reset form
function resetForm() {
    if (confirm('Are you sure you want to reset all changes?')) {
        location.reload();
    }
    return false;
}

// Form validation before submit
document.getElementById('settingsForm').addEventListener('submit', function(e) {
    // Validate URLs
    const urlFields = ['site_url', 'logo_url', 'favicon_url'];
    for (const field of urlFields) {
        const input = document.getElementById(field);
        if (input.value && !isValidUrl(input.value)) {
            e.preventDefault();
            alert('Please enter a valid URL for ' + field.replace('_', ' '));
            input.focus();
            return false;
        }
    }
    
    // Validate deposit amounts
    const minDeposit = parseInt(document.getElementById('min_deposit').dataset.rawValue || 0);
    const maxDeposit = parseInt(document.getElementById('max_deposit').dataset.rawValue || 0);
    
    if (minDeposit > maxDeposit) {
        e.preventDefault();
        alert('Minimum deposit cannot be greater than maximum deposit');
        document.getElementById('min_deposit').focus();
        return false;
    }
    
    if (minDeposit < 1000) {
        e.preventDefault();
        alert('Minimum deposit must be at least 1,000 VND');
        document.getElementById('min_deposit').focus();
        return false;
    }
});

function isValidUrl(string) {
    try {
        new URL(string);
        return true;
    } catch (_) {
        return false;
    }
}
</script>
