<?php
/**
 * Admin System Settings Page
 * Comprehensive settings management with tabs by group
 * 
 * Variables: $pageTitle, $currentPage, $settingsGrouped, $groupConfig, $activeGroup, $plans, $bankList
 */

$groups = array_keys($groupConfig);
$activeGroupSettings = $settingsGrouped[$activeGroup] ?? [];
?>

<div class="page-header">
    <div class="page-header-content">
        <div>
            <h1><i class="icon-settings"></i> System Settings</h1>
            <p class="text-muted">Manage all application settings from one place</p>
        </div>
    </div>
</div>

<div class="settings-layout">
    <!-- Sidebar Tabs -->
    <div class="settings-sidebar">
        <nav class="settings-nav">
            <?php foreach ($groups as $groupKey): ?>
                <?php 
                $config = $groupConfig[$groupKey] ?? [];
                $isActive = $activeGroup === $groupKey;
                $settingsCount = count($settingsGrouped[$groupKey] ?? []);
                ?>
                <a href="/admin/settings?group=<?= htmlspecialchars($groupKey) ?>" 
                   class="settings-nav-item <?= $isActive ? 'active' : '' ?>">
                    <i class="<?= htmlspecialchars($config['icon'] ?? 'icon-settings') ?>"></i>
                    <span class="settings-nav-label"><?= htmlspecialchars($config['title'] ?? ucfirst($groupKey)) ?></span>
                    <span class="settings-nav-count"><?= $settingsCount ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="settings-content">
        <?php 
        $activeConfig = $groupConfig[$activeGroup] ?? [];
        $isReadOnly = $activeGroup === 'database';
        ?>
        
        <div class="settings-group-header">
            <div class="settings-group-icon">
                <i class="<?= htmlspecialchars($activeConfig['icon'] ?? 'icon-settings') ?>"></i>
            </div>
            <div class="settings-group-info">
                <h2><?= htmlspecialchars($activeConfig['title'] ?? ucfirst($activeGroup)) ?></h2>
                <p><?= htmlspecialchars($activeConfig['description'] ?? '') ?></p>
            </div>
            <?php if (!$isReadOnly && !empty($activeGroupSettings)): ?>
            <div class="settings-group-actions">
                <button type="button" class="btn btn-outline btn-sm" onclick="resetGroupSettings()">
                    <i class="icon-refresh-cw"></i> Reset
                </button>
            </div>
            <?php endif; ?>
        </div>

        <?php if (empty($activeGroupSettings)): ?>
            <div class="empty-state">
                <i class="icon-inbox"></i>
                <h3>No Settings</h3>
                <p>No settings found for this group.</p>
            </div>
        <?php else: ?>
            <form id="settingsForm" action="/admin/system-settings" method="POST" class="settings-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <input type="hidden" name="setting_group" value="<?= htmlspecialchars($activeGroup) ?>">
                
                <div class="settings-list">
                    <?php foreach ($activeGroupSettings as $setting): ?>
                        <?php
                        $key = $setting['setting_key'];
                        $value = $setting['setting_value'] ?? '';
                        $type = $setting['setting_type'] ?? 'string';
                        $label = $setting['label'] ?? ucfirst(str_replace('_', ' ', $key));
                        $description = $setting['description'] ?? '';
                        $isSensitive = (bool) ($setting['is_sensitive'] ?? false);
                        $inputId = 'setting_' . $key;
                        ?>
                        
                        <div class="setting-item">
                            <div class="setting-info">
                                <label for="<?= htmlspecialchars($inputId) ?>" class="setting-label">
                                    <?= htmlspecialchars($label) ?>
                                    <?php if ($isSensitive): ?>
                                        <span class="badge badge-warning badge-sm">Sensitive</span>
                                    <?php endif; ?>
                                </label>
                                <?php if ($description): ?>
                                    <p class="setting-description"><?= htmlspecialchars($description) ?></p>
                                <?php endif; ?>
                                <code class="setting-key"><?= htmlspecialchars($key) ?></code>
                            </div>
                            
                            <div class="setting-input">
                                <?php if ($type === 'boolean'): ?>
                                    <!-- Toggle Switch for Boolean -->
                                    <label class="toggle-switch" <?= $isReadOnly ? 'style="pointer-events: none; opacity: 0.6;"' : '' ?>>
                                        <input type="checkbox" 
                                               id="<?= htmlspecialchars($inputId) ?>" 
                                               name="<?= htmlspecialchars($key) ?>"
                                               <?= filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' ?>
                                               <?= $isReadOnly ? 'disabled' : '' ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                
                                <?php elseif ($type === 'text'): ?>
                                    <!-- Textarea for Text -->
                                    <textarea id="<?= htmlspecialchars($inputId) ?>"
                                              name="<?= htmlspecialchars($key) ?>"
                                              class="form-input form-textarea"
                                              rows="3"
                                              <?= $isReadOnly ? 'readonly' : '' ?>
                                              placeholder="Enter <?= htmlspecialchars(strtolower($label)) ?>"><?= htmlspecialchars($value) ?></textarea>
                                
                                <?php elseif ($type === 'number'): ?>
                                    <!-- Number Input -->
                                    <input type="number" 
                                           id="<?= htmlspecialchars($inputId) ?>"
                                           name="<?= htmlspecialchars($key) ?>"
                                           class="form-input"
                                           value="<?= htmlspecialchars($value) ?>"
                                           <?= $isReadOnly ? 'readonly' : '' ?>
                                           placeholder="0">
                                
                                <?php elseif ($isSensitive): ?>
                                    <!-- Password Input with Toggle -->
                                    <div class="input-group">
                                        <input type="password" 
                                               id="<?= htmlspecialchars($inputId) ?>"
                                               name="<?= htmlspecialchars($key) ?>"
                                               class="form-input"
                                               <?= $isReadOnly ? 'readonly' : '' ?>
                                               placeholder="<?= $value ? 'Leave empty to keep existing' : 'Enter value' ?>"
                                               autocomplete="new-password">
                                        <button type="button" class="btn btn-secondary btn-input-addon" onclick="togglePassword('<?= htmlspecialchars($inputId) ?>')">
                                            <i class="icon-eye" id="<?= htmlspecialchars($inputId) ?>_icon"></i>
                                        </button>
                                    </div>
                                    <?php if ($value): ?>
                                        <small class="form-help text-success"><i class="icon-check"></i> Value is set</small>
                                    <?php endif; ?>
                                
                                <?php elseif ($key === 'vietqr_bank_id' || $key === 'bank_name'): ?>
                                    <!-- Bank Dropdown -->
                                    <select id="<?= htmlspecialchars($inputId) ?>"
                                            name="<?= htmlspecialchars($key) ?>"
                                            class="form-input"
                                            <?= $isReadOnly ? 'disabled' : '' ?>>
                                        <option value="">-- Select Bank --</option>
                                        <?php foreach ($bankList as $bank): ?>
                                            <option value="<?= htmlspecialchars($bank['id']) ?>"
                                                    <?= $value === $bank['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($bank['name']) ?> (<?= htmlspecialchars($bank['id']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                
                                <?php elseif ($key === 'default_plan_id'): ?>
                                    <!-- Plan Dropdown -->
                                    <select id="<?= htmlspecialchars($inputId) ?>"
                                            name="<?= htmlspecialchars($key) ?>"
                                            class="form-input"
                                            <?= $isReadOnly ? 'disabled' : '' ?>>
                                        <option value="0">-- No Default Plan --</option>
                                        <?php foreach ($plans as $plan): ?>
                                            <option value="<?= (int)$plan['id'] ?>"
                                                    <?= (int)$value === (int)$plan['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($plan['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                
                                <?php elseif ($key === 'mail_encryption' || $key === 'smtp_encryption'): ?>
                                    <!-- Encryption Dropdown -->
                                    <select id="<?= htmlspecialchars($inputId) ?>"
                                            name="<?= htmlspecialchars($key) ?>"
                                            class="form-input"
                                            <?= $isReadOnly ? 'disabled' : '' ?>>
                                        <option value="tls" <?= $value === 'tls' ? 'selected' : '' ?>>TLS</option>
                                        <option value="ssl" <?= $value === 'ssl' ? 'selected' : '' ?>>SSL</option>
                                        <option value="none" <?= $value === 'none' ? 'selected' : '' ?>>None</option>
                                    </select>
                                
                                <?php else: ?>
                                    <!-- Default Text Input -->
                                    <input type="text" 
                                           id="<?= htmlspecialchars($inputId) ?>"
                                           name="<?= htmlspecialchars($key) ?>"
                                           class="form-input"
                                           value="<?= htmlspecialchars($value) ?>"
                                           <?= $isReadOnly ? 'readonly' : '' ?>
                                           placeholder="Enter <?= htmlspecialchars(strtolower($label)) ?>">
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Test Buttons for specific groups -->
                <?php if ($activeGroup === 'mail'): ?>
                    <div class="settings-actions-inline">
                        <button type="button" class="btn btn-secondary" onclick="testEmailConnection()">
                            <i class="icon-send"></i> Test Email Connection
                        </button>
                    </div>
                <?php elseif ($activeGroup === 'telegram'): ?>
                    <div class="settings-actions-inline">
                        <button type="button" class="btn btn-secondary" onclick="testTelegramConnection()">
                            <i class="icon-message-circle"></i> Test Telegram Connection
                        </button>
                    </div>
                <?php endif; ?>

                <!-- Form Actions -->
                <?php if (!$isReadOnly): ?>
                <div class="settings-footer">
                    <div class="settings-footer-content">
                        <div class="unsaved-indicator" id="unsavedIndicator" style="display: none;">
                            <span class="unsaved-dot"></span>
                            <span>You have unsaved changes</span>
                        </div>
                        <div class="settings-footer-actions">
                            <button type="reset" class="btn btn-secondary" onclick="return confirmReset()">
                                <i class="icon-x"></i> Discard
                            </button>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="icon-save"></i> Save Settings
                            </button>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </form>
        <?php endif; ?>
    </div>
</div>

<style>
.settings-layout {
    display: flex;
    gap: var(--spacing-6);
    min-height: calc(100vh - 200px);
}

.settings-sidebar {
    width: 260px;
    flex-shrink: 0;
}

.settings-nav {
    background: var(--color-white);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
    position: sticky;
    top: var(--spacing-4);
}

.settings-nav-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-3);
    padding: var(--spacing-3) var(--spacing-4);
    color: var(--color-gray-600);
    text-decoration: none;
    border-left: 3px solid transparent;
    transition: all var(--transition-fast);
}

.settings-nav-item:hover {
    background: var(--color-gray-50);
    color: var(--color-gray-900);
}

.settings-nav-item.active {
    background: rgba(79, 70, 229, 0.05);
    color: var(--color-primary);
    border-left-color: var(--color-primary);
}

.settings-nav-item i {
    font-size: var(--font-size-lg);
    width: 24px;
    text-align: center;
}

.settings-nav-label {
    flex: 1;
    font-weight: var(--font-weight-medium);
    font-size: var(--font-size-sm);
}

.settings-nav-count {
    font-size: var(--font-size-xs);
    background: var(--color-gray-100);
    color: var(--color-gray-500);
    padding: 2px 8px;
    border-radius: var(--radius-full);
}

.settings-nav-item.active .settings-nav-count {
    background: rgba(79, 70, 229, 0.1);
    color: var(--color-primary);
}

.settings-content {
    flex: 1;
    min-width: 0;
}

.settings-group-header {
    display: flex;
    align-items: center;
    gap: var(--spacing-4);
    padding: var(--spacing-5);
    background: var(--color-white);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
    margin-bottom: var(--spacing-4);
}

.settings-group-icon {
    width: 56px;
    height: 56px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(79, 70, 229, 0.1);
    color: var(--color-primary);
    border-radius: var(--radius-lg);
    font-size: var(--font-size-2xl);
    flex-shrink: 0;
}

.settings-group-info {
    flex: 1;
}

.settings-group-info h2 {
    margin: 0 0 var(--spacing-1) 0;
    font-size: var(--font-size-xl);
    font-weight: var(--font-weight-semibold);
    color: var(--color-gray-900);
}

.settings-group-info p {
    margin: 0;
    color: var(--color-gray-500);
    font-size: var(--font-size-sm);
}

.settings-form {
    background: var(--color-white);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
}

.settings-list {
    padding: var(--spacing-2);
}

.setting-item {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: var(--spacing-6);
    padding: var(--spacing-4) var(--spacing-4);
    border-bottom: 1px solid var(--color-gray-100);
}

.setting-item:last-child {
    border-bottom: none;
}

.setting-item:hover {
    background: var(--color-gray-50);
}

.setting-info {
    flex: 1;
    min-width: 0;
}

.setting-label {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    font-weight: var(--font-weight-medium);
    color: var(--color-gray-900);
    margin-bottom: var(--spacing-1);
}

.setting-description {
    margin: 0 0 var(--spacing-2) 0;
    color: var(--color-gray-500);
    font-size: var(--font-size-sm);
    line-height: 1.5;
}

.setting-key {
    font-size: var(--font-size-xs);
    background: var(--color-gray-100);
    color: var(--color-gray-500);
    padding: 2px 6px;
    border-radius: var(--radius-sm);
    font-family: monospace;
}

.setting-input {
    width: 320px;
    flex-shrink: 0;
}

.setting-input .form-input {
    width: 100%;
}

.setting-input .form-textarea {
    resize: vertical;
    min-height: 80px;
}

/* Toggle Switch */
.toggle-switch {
    position: relative;
    display: inline-block;
    width: 52px;
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
    transform: translateX(24px);
}

/* Input Group */
.input-group {
    display: flex;
}

.input-group .form-input {
    border-radius: var(--radius-md) 0 0 var(--radius-md);
    border-right: none;
}

.btn-input-addon {
    border-radius: 0 var(--radius-md) var(--radius-md) 0;
    border-left: none;
}

/* Test Actions */
.settings-actions-inline {
    padding: var(--spacing-4);
    border-top: 1px solid var(--color-gray-100);
    background: var(--color-gray-50);
}

/* Footer */
.settings-footer {
    position: sticky;
    bottom: 0;
    background: var(--color-white);
    border-top: 1px solid var(--color-gray-200);
    box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.1);
    z-index: 10;
}

.settings-footer-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--spacing-4) var(--spacing-5);
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

/* Badge */
.badge-sm {
    font-size: var(--font-size-xs);
    padding: 2px 6px;
}

.badge-warning {
    background: rgba(245, 158, 11, 0.1);
    color: var(--color-warning);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: var(--spacing-12) var(--spacing-6);
    background: var(--color-white);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
}

.empty-state i {
    font-size: 48px;
    color: var(--color-gray-300);
    margin-bottom: var(--spacing-4);
}

.empty-state h3 {
    margin: 0 0 var(--spacing-2) 0;
    color: var(--color-gray-600);
}

.empty-state p {
    margin: 0;
    color: var(--color-gray-400);
}

/* Responsive */
@media (max-width: 1024px) {
    .settings-layout {
        flex-direction: column;
    }
    
    .settings-sidebar {
        width: 100%;
    }
    
    .settings-nav {
        display: flex;
        overflow-x: auto;
        position: static;
    }
    
    .settings-nav-item {
        flex-direction: column;
        padding: var(--spacing-3);
        min-width: 80px;
        text-align: center;
        border-left: none;
        border-bottom: 3px solid transparent;
    }
    
    .settings-nav-item.active {
        border-bottom-color: var(--color-primary);
    }
    
    .settings-nav-label {
        font-size: var(--font-size-xs);
    }
    
    .setting-item {
        flex-direction: column;
        gap: var(--spacing-3);
    }
    
    .setting-input {
        width: 100%;
    }
}

@media (max-width: 640px) {
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
const form = document.getElementById('settingsForm');

if (form) {
    form.addEventListener('input', function() {
        formChanged = true;
        document.getElementById('unsavedIndicator').style.display = 'flex';
    });
    
    form.addEventListener('change', function() {
        formChanged = true;
        document.getElementById('unsavedIndicator').style.display = 'flex';
    });
    
    form.addEventListener('submit', function() {
        formChanged = false;
    });
}

// Warn before leaving with unsaved changes
window.addEventListener('beforeunload', function(e) {
    if (formChanged) {
        e.preventDefault();
        e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
        return e.returnValue;
    }
});

// Toggle password visibility
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '_icon');
    
    if (!field || !icon) return;
    
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

// Confirm reset
function confirmReset() {
    if (confirm('Are you sure you want to discard all changes?')) {
        formChanged = false;
        document.getElementById('unsavedIndicator').style.display = 'none';
        return true;
    }
    return false;
}

// Reset group settings
function resetGroupSettings() {
    if (confirm('This will reload the current settings. Are you sure?')) {
        window.location.reload();
    }
}

// Test email connection
function testEmailConnection() {
    const btn = event.target.closest('button');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="icon-loader spin"></i> Testing...';
    
    fetch('/admin/settings/test-email', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('[name="csrf_token"]').value
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Success: ' + data.message);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error testing connection: ' + error.message);
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

// Test Telegram connection
function testTelegramConnection() {
    const btn = event.target.closest('button');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="icon-loader spin"></i> Testing...';
    
    fetch('/admin/settings/test-telegram', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('[name="csrf_token"]').value
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Success: ' + data.message);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error testing connection: ' + error.message);
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

// Spinner animation
const style = document.createElement('style');
style.textContent = `
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    .spin {
        animation: spin 1s linear infinite;
    }
`;
document.head.appendChild(style);
</script>
