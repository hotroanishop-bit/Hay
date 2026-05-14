<?php
/**
 * API Key Details Page
 * Variables: $pageTitle, $currentPage, $apiKey, $stats, $allowedModels, $allowedIPs, $availableModels
 */
?>

<div class="page-header">
    <div class="page-header-content">
        <a href="/keys" class="back-link">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            Back to Keys
        </a>
        <h1 class="page-title"><?= htmlspecialchars($apiKey['name'] ?? 'API Key Details') ?></h1>
        <p class="page-subtitle">View and manage your API key settings</p>
    </div>
    <div class="page-header-actions">
        <?php if ($apiKey['is_active']): ?>
        <form action="/keys/<?= $apiKey['id'] ?>/revoke" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to revoke this API key? This action cannot be undone.')">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <button type="submit" class="btn btn-danger">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                Revoke Key
            </button>
        </form>
        <?php endif; ?>
        <form action="/keys/<?= $apiKey['id'] ?>/rotate" method="POST" style="display: inline;" onsubmit="return confirm('This will generate a new key and invalidate the current one. Continue?')">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <button type="submit" class="btn btn-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2v6h-6"/><path d="M3 12a9 9 0 0 1 15-6.7L21 8"/><path d="M3 22v-6h6"/><path d="M21 12a9 9 0 0 1-15 6.7L3 16"/></svg>
                Rotate Key
            </button>
        </form>
    </div>
</div>

<div class="key-details-layout">
    <div class="key-details-main">
        <!-- Key Status Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    Key Status
                </h3>
            </div>
            <div class="card-body">
                <div class="status-grid">
                    <div class="status-item">
                        <span class="status-label">Status</span>
                        <span class="status-value">
                            <?php if ($apiKey['is_active']): ?>
                            <span class="badge badge-success">Active</span>
                            <?php else: ?>
                            <span class="badge badge-danger">Inactive</span>
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="status-item">
                        <span class="status-label">Created</span>
                        <span class="status-value"><?= date('M j, Y g:i A', strtotime($apiKey['created_at'])) ?></span>
                    </div>
                    <div class="status-item">
                        <span class="status-label">Expires</span>
                        <span class="status-value">
                            <?php if ($apiKey['expires_at']): ?>
                            <?= date('M j, Y', strtotime($apiKey['expires_at'])) ?>
                            <?php else: ?>
                            Never
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="status-item">
                        <span class="status-label">Last Used</span>
                        <span class="status-value">
                            <?php if (!empty($stats['last_used'])): ?>
                            <?= date('M j, Y g:i A', strtotime($stats['last_used'])) ?>
                            <?php else: ?>
                            Never
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Usage Statistics Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                    Usage Statistics
                </h3>
            </div>
            <div class="card-body">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-value"><?= number_format($apiKey['usage_count'] ?? 0) ?></div>
                        <div class="stat-label">Total Requests</div>
                        <?php if ($apiKey['usage_limit']): ?>
                        <div class="stat-limit">of <?= number_format($apiKey['usage_limit']) ?> limit</div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?= min(100, ($apiKey['usage_count'] / $apiKey['usage_limit']) * 100) ?>%"></div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?= number_format($stats['total_tokens'] ?? 0) ?></div>
                        <div class="stat-label">Tokens Used</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?= $apiKey['rate_limit'] ? number_format($apiKey['rate_limit']) . '/min' : 'Unlimited' ?></div>
                        <div class="stat-label">Rate Limit</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">$<?= number_format($stats['total_cost'] ?? 0, 4) ?></div>
                        <div class="stat-label">Total Cost</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Model Permissions Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                    Model Permissions
                </h3>
            </div>
            <div class="card-body">
                <?php if ($allowedModels === null || empty($allowedModels)): ?>
                <div class="permission-all">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    <span>All models allowed</span>
                </div>
                <p class="permission-help">This API key can access any available model.</p>
                <?php else: ?>
                <div class="model-list">
                    <?php foreach ($allowedModels as $model): ?>
                    <div class="model-badge">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                        <?= htmlspecialchars($model) ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <p class="permission-help">This API key can only access the models listed above.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- IP Whitelist Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    IP Whitelist
                </h3>
            </div>
            <div class="card-body">
                <?php if ($allowedIPs === null || empty($allowedIPs)): ?>
                <div class="permission-all">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    <span>All IP addresses allowed</span>
                </div>
                <p class="permission-help">This API key can be used from any IP address.</p>
                <?php else: ?>
                <div class="ip-list">
                    <?php foreach ($allowedIPs as $ip): ?>
                    <div class="ip-badge">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                        <code><?= htmlspecialchars($ip) ?></code>
                    </div>
                    <?php endforeach; ?>
                </div>
                <p class="permission-help">This API key can only be used from the IP addresses listed above.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="key-details-sidebar">
        <!-- Key Info Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                    Key Information
                </h3>
            </div>
            <div class="card-body">
                <dl class="info-list">
                    <div class="info-item">
                        <dt>Key ID</dt>
                        <dd><code><?= $apiKey['id'] ?></code></dd>
                    </div>
                    <div class="info-item">
                        <dt>Name</dt>
                        <dd><?= htmlspecialchars($apiKey['name']) ?></dd>
                    </div>
                    <div class="info-item">
                        <dt>Key Hash</dt>
                        <dd><code class="hash-preview"><?= substr($apiKey['key_hash'], 0, 12) ?>...</code></dd>
                    </div>
                    <?php if ($apiKey['provider']): ?>
                    <div class="info-item">
                        <dt>Provider</dt>
                        <dd><?= htmlspecialchars($apiKey['provider']) ?></dd>
                    </div>
                    <?php endif; ?>
                    <?php if ($apiKey['model']): ?>
                    <div class="info-item">
                        <dt>Default Model</dt>
                        <dd><?= htmlspecialchars($apiKey['model']) ?></dd>
                    </div>
                    <?php endif; ?>
                </dl>
            </div>
        </div>

        <!-- Security Tips -->
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
                        <span>Rotate keys regularly for security</span>
                    </li>
                    <li>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>Use IP whitelist for production</span>
                    </li>
                    <li>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>Limit models to only what you need</span>
                    </li>
                    <li>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>Monitor usage for anomalies</span>
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

/* Page Header Actions */
.page-header-actions {
    display: flex;
    gap: var(--space-3);
}

/* Layout */
.key-details-layout {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: var(--space-6);
    align-items: start;
}

.key-details-main {
    display: flex;
    flex-direction: column;
    gap: var(--space-6);
}

.key-details-sidebar {
    display: flex;
    flex-direction: column;
    gap: var(--space-6);
    position: sticky;
    top: calc(var(--topbar-height) + var(--space-6));
}

/* Card Title */
.card-title {
    display: flex;
    align-items: center;
    gap: var(--space-2);
}

/* Status Grid */
.status-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: var(--space-4);
}

.status-item {
    display: flex;
    flex-direction: column;
    gap: var(--space-1);
}

.status-label {
    font-size: var(--font-size-xs);
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.status-value {
    font-size: var(--font-size-base);
    color: var(--text-primary);
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: var(--space-4);
}

.stat-card {
    padding: var(--space-4);
    background: var(--bg-tertiary);
    border-radius: var(--radius-md);
    text-align: center;
}

.stat-value {
    font-size: var(--font-size-2xl);
    font-weight: var(--font-weight-bold);
    color: var(--text-primary);
}

.stat-label {
    font-size: var(--font-size-sm);
    color: var(--text-muted);
    margin-top: var(--space-1);
}

.stat-limit {
    font-size: var(--font-size-xs);
    color: var(--text-muted);
    margin-top: var(--space-2);
}

.progress-bar {
    height: 4px;
    background: var(--bg-secondary);
    border-radius: var(--radius-full);
    margin-top: var(--space-2);
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: var(--color-primary);
    border-radius: var(--radius-full);
    transition: width var(--transition-normal);
}

/* Permission All */
.permission-all {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    padding: var(--space-4);
    background: var(--color-success-light, rgba(16, 185, 129, 0.1));
    border-radius: var(--radius-md);
    color: var(--color-success);
    font-weight: var(--font-weight-medium);
}

.permission-help {
    font-size: var(--font-size-sm);
    color: var(--text-muted);
    margin-top: var(--space-3);
}

/* Model List */
.model-list {
    display: flex;
    flex-wrap: wrap;
    gap: var(--space-2);
}

.model-badge {
    display: inline-flex;
    align-items: center;
    gap: var(--space-2);
    padding: var(--space-2) var(--space-3);
    background: var(--bg-tertiary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    font-size: var(--font-size-sm);
    color: var(--text-primary);
}

.model-badge svg {
    color: var(--text-muted);
}

/* IP List */
.ip-list {
    display: flex;
    flex-direction: column;
    gap: var(--space-2);
}

.ip-badge {
    display: inline-flex;
    align-items: center;
    gap: var(--space-2);
    padding: var(--space-2) var(--space-3);
    background: var(--bg-tertiary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    font-size: var(--font-size-sm);
}

.ip-badge svg {
    color: var(--text-muted);
}

.ip-badge code {
    font-family: var(--font-mono);
    color: var(--text-primary);
}

/* Info List */
.info-list {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: var(--space-1);
}

.info-item dt {
    font-size: var(--font-size-xs);
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.info-item dd {
    font-size: var(--font-size-sm);
    color: var(--text-primary);
}

.info-item code {
    font-family: var(--font-mono);
    font-size: var(--font-size-xs);
    background: var(--bg-tertiary);
    padding: var(--space-1) var(--space-2);
    border-radius: var(--radius-sm);
}

.hash-preview {
    word-break: break-all;
}

/* Tips List */
.tips-list {
    display: flex;
    flex-direction: column;
    gap: var(--space-3);
}

.tips-list li {
    display: flex;
    align-items: flex-start;
    gap: var(--space-2);
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
}

.tips-list li svg {
    flex-shrink: 0;
    color: var(--color-success);
    margin-top: 2px;
}

/* Responsive */
@media (max-width: 1023px) {
    .key-details-layout {
        grid-template-columns: 1fr;
    }
    
    .key-details-sidebar {
        position: static;
    }
}

@media (max-width: 639px) {
    .page-header-actions {
        flex-direction: column;
        width: 100%;
    }
    
    .page-header-actions .btn {
        width: 100%;
    }
    
    .status-grid,
    .stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>
