<?php
/**
 * Create API Key Page - Modern Form Design
 * Variables: $pageTitle, $currentPage, $providers
 */
?>

<div class="page-header">
    <div class="page-header-content">
        <a href="/keys" class="back-link">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            Back to Keys
        </a>
        <h1 class="page-title">Create API Key</h1>
        <p class="page-subtitle">Generate a new API key for accessing AI services</p>
    </div>
</div>

<div class="form-layout">
    <div class="form-main">
        <div class="card">
            <div class="card-body">
                <form action="/keys" method="POST" class="create-key-form" id="createKeyForm">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                    <div class="form-section">
                        <h3 class="form-section-title">Basic Information</h3>
                        
                        <div class="form-group">
                            <label for="name" class="form-label required">Key Name</label>
                            <input type="text" id="name" name="name" class="form-input" placeholder="e.g., Production API Key" required>
                            <p class="form-help">A descriptive name to identify this key</p>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="form-section-title">Access Configuration</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="provider" class="form-label">Provider</label>
                                <div class="select-wrapper">
                                    <select id="provider" name="provider" class="form-select">
                                        <option value="">All Providers</option>
                                        <?php foreach ($providers ?? [] as $provider): ?>
                                        <option value="<?= htmlspecialchars($provider['id'] ?? '') ?>">
                                            <?= htmlspecialchars($provider['name'] ?? '') ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <svg class="select-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                                </div>
                                <p class="form-help">Restrict this key to a specific AI provider (optional)</p>
                            </div>

                            <div class="form-group">
                                <label for="model" class="form-label">Model</label>
                                <div class="select-wrapper">
                                    <select id="model" name="model" class="form-select">
                                        <option value="">All Models</option>
                                    </select>
                                    <svg class="select-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                                </div>
                                <p class="form-help">Restrict this key to a specific model (optional)</p>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="form-section-title">Model Permissions</h3>
                        <p class="form-help" style="margin-bottom: var(--space-4);">Select which models this API key can access. Leave all unchecked to allow all models.</p>
                        
                        <div class="model-checkboxes">
                            <?php foreach ($availableModels ?? [] as $modelName): ?>
                            <label class="checkbox-card">
                                <input type="checkbox" name="allowed_models[]" value="<?= htmlspecialchars($modelName) ?>">
                                <span class="checkbox-card-content">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                                    <span class="model-name"><?= htmlspecialchars($modelName) ?></span>
                                </span>
                                <span class="checkbox-indicator">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                </span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="model-selection-actions">
                            <button type="button" class="btn btn-ghost btn-sm" onclick="selectAllModels()">Select All</button>
                            <button type="button" class="btn btn-ghost btn-sm" onclick="deselectAllModels()">Deselect All</button>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="form-section-title">IP Whitelist</h3>
                        
                        <div class="form-group">
                            <label for="allowed_ips" class="form-label">Allowed IP Addresses</label>
                            <textarea id="allowed_ips" name="allowed_ips" class="form-input form-textarea" rows="4" placeholder="192.168.1.100&#10;10.0.0.*&#10;172.16.0.0/16"></textarea>
                            <p class="form-help">Enter one IP address per line. Leave empty to allow all IPs.</p>
                            <div class="ip-help-text">
                                <strong>Supported formats:</strong>
                                <ul>
                                    <li>Exact IP: <code>192.168.1.100</code></li>
                                    <li>Wildcard: <code>192.168.1.*</code> or <code>10.*.*.*</code></li>
                                    <li>CIDR: <code>192.168.0.0/24</code></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="form-section-title">Rate Limits</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="rate_limit" class="form-label">Rate Limit (requests/minute)</label>
                                <div class="input-with-addon">
                                    <input type="number" id="rate_limit" name="rate_limit" class="form-input" min="1" max="10000" placeholder="60">
                                    <span class="input-addon">req/min</span>
                                </div>
                                <p class="form-help">Maximum requests per minute (leave empty for no limit)</p>
                            </div>

                            <div class="form-group">
                                <label for="usage_limit" class="form-label">Usage Limit (total requests)</label>
                                <div class="input-with-addon">
                                    <input type="number" id="usage_limit" name="usage_limit" class="form-input" min="1" placeholder="10000">
                                    <span class="input-addon">requests</span>
                                </div>
                                <p class="form-help">Maximum total requests (leave empty for unlimited)</p>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Quick Presets</label>
                            <div class="preset-buttons">
                                <button type="button" class="btn btn-outline btn-sm" onclick="setPreset(60, 10000)">Basic</button>
                                <button type="button" class="btn btn-outline btn-sm" onclick="setPreset(120, 50000)">Standard</button>
                                <button type="button" class="btn btn-outline btn-sm" onclick="setPreset(300, 100000)">Pro</button>
                                <button type="button" class="btn btn-outline btn-sm" onclick="setPreset('', '')">Unlimited</button>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="form-section-title">Expiration</h3>
                        
                        <div class="form-group">
                            <label for="expires_at" class="form-label">Expiration Date</label>
                            <input type="date" id="expires_at" name="expires_at" class="form-input" min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                            <p class="form-help">Key will automatically be deactivated after this date (optional)</p>
                        </div>
                        
                        <div class="expiry-shortcuts">
                            <button type="button" class="btn btn-ghost btn-sm" onclick="setExpiry(30)">30 days</button>
                            <button type="button" class="btn btn-ghost btn-sm" onclick="setExpiry(90)">90 days</button>
                            <button type="button" class="btn btn-ghost btn-sm" onclick="setExpiry(180)">6 months</button>
                            <button type="button" class="btn btn-ghost btn-sm" onclick="setExpiry(365)">1 year</button>
                            <button type="button" class="btn btn-ghost btn-sm" onclick="setExpiry(0)">Never</button>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="/keys" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                            Create API Key
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="form-sidebar">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    Security Tips
                </h3>
            </div>
            <div class="card-body">
                <ul class="tips-list">
                    <li>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>Never share your API keys publicly or commit them to version control</span>
                    </li>
                    <li>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>Use environment variables to store keys in your applications</span>
                    </li>
                    <li>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>Set appropriate rate limits to prevent abuse</span>
                    </li>
                    <li>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>Rotate keys regularly for enhanced security</span>
                    </li>
                    <li>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>Create separate keys for different environments (dev, staging, production)</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
/* Back Link */
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

/* Form Layout */
.form-layout {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: var(--space-6);
    align-items: start;
}

.form-main {
    min-width: 0;
}

.form-sidebar {
    position: sticky;
    top: calc(var(--topbar-height) + var(--space-6));
}

/* Form Sections */
.form-section {
    padding-bottom: var(--space-6);
    margin-bottom: var(--space-6);
    border-bottom: 1px solid var(--border-light);
}

.form-section:last-of-type {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.form-section-title {
    font-size: var(--font-size-base);
    font-weight: var(--font-weight-semibold);
    color: var(--text-primary);
    margin: 0 0 var(--space-4) 0;
}

/* Form Row */
.form-row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: var(--space-4);
}

/* Select Wrapper */
.select-wrapper {
    position: relative;
}

.form-select {
    appearance: none;
    padding-right: var(--space-10);
}

.select-icon {
    position: absolute;
    right: var(--space-3);
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
    pointer-events: none;
}

/* Input with Addon */
.input-with-addon {
    display: flex;
    align-items: stretch;
}

.input-with-addon .form-input {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
    flex: 1;
}

.input-addon {
    display: flex;
    align-items: center;
    padding: 0 var(--space-3);
    background: var(--bg-tertiary);
    border: 1px solid var(--border-color);
    border-left: none;
    border-radius: 0 var(--radius-md) var(--radius-md) 0;
    font-size: var(--font-size-sm);
    color: var(--text-muted);
}

/* Preset Buttons */
.preset-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: var(--space-2);
}

/* Expiry Shortcuts */
.expiry-shortcuts {
    display: flex;
    flex-wrap: wrap;
    gap: var(--space-2);
    margin-top: var(--space-3);
}

/* Form Actions */
.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: var(--space-3);
    padding-top: var(--space-6);
    margin-top: var(--space-6);
    border-top: 1px solid var(--border-color);
}

/* Model Checkboxes */
.model-checkboxes {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: var(--space-3);
}

.checkbox-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: var(--space-3) var(--space-4);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    cursor: pointer;
    transition: all var(--transition-fast);
    background: var(--bg-primary);
}

.checkbox-card:hover {
    border-color: var(--color-primary);
    background: var(--bg-secondary);
}

.checkbox-card input[type="checkbox"] {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}

.checkbox-card input[type="checkbox"]:checked + .checkbox-card-content + .checkbox-indicator {
    background: var(--color-primary);
    border-color: var(--color-primary);
}

.checkbox-card input[type="checkbox"]:checked + .checkbox-card-content + .checkbox-indicator svg {
    opacity: 1;
}

.checkbox-card-content {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    color: var(--text-primary);
}

.checkbox-card-content svg {
    color: var(--text-muted);
}

.model-name {
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-medium);
}

.checkbox-indicator {
    width: 20px;
    height: 20px;
    border: 2px solid var(--border-color);
    border-radius: var(--radius-sm);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all var(--transition-fast);
    flex-shrink: 0;
}

.checkbox-indicator svg {
    color: white;
    opacity: 0;
    transition: opacity var(--transition-fast);
}

.model-selection-actions {
    display: flex;
    gap: var(--space-2);
    margin-top: var(--space-3);
}

/* IP Whitelist */
.form-textarea {
    resize: vertical;
    min-height: 100px;
    font-family: var(--font-mono);
    font-size: var(--font-size-sm);
}

.ip-help-text {
    margin-top: var(--space-3);
    padding: var(--space-3);
    background: var(--bg-tertiary);
    border-radius: var(--radius-md);
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
}

.ip-help-text strong {
    display: block;
    margin-bottom: var(--space-2);
    color: var(--text-primary);
}

.ip-help-text ul {
    margin: 0;
    padding-left: var(--space-4);
}

.ip-help-text li {
    margin-bottom: var(--space-1);
}

.ip-help-text code {
    background: var(--bg-secondary);
    padding: 2px 6px;
    border-radius: var(--radius-sm);
    font-size: var(--font-size-xs);
}

/* Tips List */
.tips-list {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
}

.tips-list li {
    display: flex;
    align-items: flex-start;
    gap: var(--space-3);
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
    line-height: var(--line-height-relaxed);
}

.tips-list li svg {
    flex-shrink: 0;
    color: var(--color-success);
    margin-top: 2px;
}

.card-title {
    display: flex;
    align-items: center;
    gap: var(--space-2);
}

/* Responsive */
@media (max-width: 1023px) {
    .form-layout {
        grid-template-columns: 1fr;
    }
    
    .form-sidebar {
        position: static;
    }
}

@media (max-width: 639px) {
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .model-checkboxes {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .form-actions .btn {
        width: 100%;
    }
}
</style>

<script>
function setPreset(rateLimit, usageLimit) {
    document.getElementById('rate_limit').value = rateLimit;
    document.getElementById('usage_limit').value = usageLimit;
}

function setExpiry(days) {
    const input = document.getElementById('expires_at');
    if (days === 0) {
        input.value = '';
    } else {
        const date = new Date();
        date.setDate(date.getDate() + days);
        input.value = date.toISOString().split('T')[0];
    }
}

function selectAllModels() {
    document.querySelectorAll('.model-checkboxes input[type="checkbox"]').forEach(cb => {
        cb.checked = true;
    });
}

function deselectAllModels() {
    document.querySelectorAll('.model-checkboxes input[type="checkbox"]').forEach(cb => {
        cb.checked = false;
    });
}

// Form submission with loading state
document.getElementById('createKeyForm').addEventListener('submit', function(e) {
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span> Creating...';
});
</script>
