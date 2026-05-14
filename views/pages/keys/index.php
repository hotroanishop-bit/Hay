<?php
/**
 * API Keys Index Page - Card Grid Layout
 * Variables: $pageTitle, $currentPage, $apiKeys, $providers
 */

$newApiKey = $_SESSION['new_api_key'] ?? null;
unset($_SESSION['new_api_key']);
?>

<div class="page-header page-header-flex">
    <div class="page-header-content">
        <h1 class="page-title">API Keys</h1>
        <p class="page-subtitle">Manage your API keys for accessing AI services</p>
    </div>
    <div class="page-header-actions">
        <div class="search-filter-group">
            <div class="search-input-wrapper">
                <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.3-4.3"></path></svg>
                <input type="text" id="keySearch" class="form-input search-input" placeholder="Search keys...">
            </div>
        </div>
        <a href="/keys/create" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
            Create New Key
        </a>
    </div>
</div>

<?php if ($newApiKey): ?>
<div class="alert alert-success alert-dismissible mb-6">
    <div class="alert-content">
        <div class="alert-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
        </div>
        <div>
            <strong>New API Key Created!</strong>
            <p class="mb-2">Make sure to copy your API key now. You won't be able to see it again.</p>
            <div class="api-key-display">
                <code id="new-api-key"><?= htmlspecialchars($newApiKey) ?></code>
                <button type="button" class="btn btn-sm btn-secondary" onclick="copyToClipboard('new-api-key', this)">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"></rect><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"></path></svg>
                    Copy
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($apiKeys)): ?>
<div class="keys-grid" id="keysGrid">
    <?php foreach ($apiKeys as $key): ?>
    <div class="api-key-card" data-name="<?= htmlspecialchars(strtolower($key['name'] ?? '')) ?>">
        <div class="key-card-header">
            <div class="key-info">
                <h3 class="key-name"><?= htmlspecialchars($key['name'] ?? 'Unnamed') ?></h3>
                <div class="key-meta">
                    <?php if (!empty($key['provider'])): ?>
                    <span class="badge badge-primary"><?= htmlspecialchars(ucfirst($key['provider'])) ?></span>
                    <?php else: ?>
                    <span class="badge badge-gray">All Providers</span>
                    <?php endif; ?>
                    <?php if (!empty($key['is_active'])): ?>
                    <span class="badge badge-success">Active</span>
                    <?php else: ?>
                    <span class="badge badge-error">Revoked</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="key-actions-dropdown">
                <button type="button" class="btn btn-ghost btn-icon" onclick="toggleKeyMenu(this)">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1"></circle><circle cx="12" cy="5" r="1"></circle><circle cx="12" cy="19" r="1"></circle></svg>
                </button>
                <div class="key-dropdown-menu">
                    <a href="/keys/<?= (int)$key['id'] ?>" class="dropdown-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        View Details
                    </a>
                    <?php if (!empty($key['is_active'])): ?>
                    <form action="/keys/<?= (int)$key['id'] ?>/rotate" method="POST" class="dropdown-form">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <button type="submit" class="dropdown-item" onclick="return confirm('Are you sure you want to rotate this key? The old key will be invalidated.')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-9-9c2.52 0 4.93 1 6.74 2.74L21 8"></path><path d="M21 3v5h-5"></path></svg>
                            Rotate Key
                        </button>
                    </form>
                    <form action="/keys/<?= (int)$key['id'] ?>/revoke" method="POST" class="dropdown-form">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <button type="submit" class="dropdown-item dropdown-item-danger" onclick="return confirm('Are you sure you want to revoke this key? This action cannot be undone.')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"></path><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path></svg>
                            Revoke Key
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="key-card-body">
            <div class="key-hash-display">
                <code class="key-preview"><?= htmlspecialchars(substr($key['key_hash'] ?? '', 0, 12)) ?>...****</code>
                <button type="button" class="btn btn-ghost btn-sm copy-key-btn" title="Copy key prefix" onclick="copyText('<?= htmlspecialchars(substr($key['key_hash'] ?? '', 0, 12)) ?>...', this)">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"></rect><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"></path></svg>
                </button>
            </div>
            
            <?php if (!empty($key['usage_limit'])): ?>
            <div class="key-usage">
                <div class="usage-header">
                    <span class="usage-label">Usage</span>
                    <span class="usage-value"><?= number_format($key['usage_count'] ?? 0) ?> / <?= number_format($key['usage_limit']) ?></span>
                </div>
                <?php 
                $usagePercent = $key['usage_limit'] > 0 ? min(100, ($key['usage_count'] ?? 0) / $key['usage_limit'] * 100) : 0;
                $progressClass = $usagePercent >= 90 ? 'progress-danger' : ($usagePercent >= 70 ? 'progress-warning' : 'progress-success');
                ?>
                <div class="progress-bar-wrapper">
                    <div class="progress-bar-track">
                        <div class="progress-bar-fill <?= $progressClass ?>" style="width: <?= $usagePercent ?>%"></div>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="key-usage">
                <div class="usage-header">
                    <span class="usage-label">Requests</span>
                    <span class="usage-value"><?= number_format($key['usage_count'] ?? 0) ?></span>
                </div>
                <span class="usage-unlimited">Unlimited</span>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="key-card-footer">
            <span class="key-created">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"></rect><line x1="16" x2="16" y1="2" y2="6"></line><line x1="8" x2="8" y1="2" y2="6"></line><line x1="3" x2="21" y1="10" y2="10"></line></svg>
                Created <?= htmlspecialchars(date('M d, Y', strtotime($key['created_at'] ?? 'now'))) ?>
            </span>
            <?php if (!empty($key['expires_at'])): ?>
            <span class="key-expires text-warning">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                Expires <?= htmlspecialchars(date('M d, Y', strtotime($key['expires_at']))) ?>
            </span>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php else: ?>
<div class="card">
    <div class="card-body">
        <div class="empty-state">
            <div class="empty-state-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15.5 7.5 2.3 2.3a1 1 0 0 0 1.4 0l2.1-2.1a1 1 0 0 0 0-1.4L19 4"></path><path d="m21 2-9.6 9.6"></path><circle cx="7.5" cy="15.5" r="5.5"></circle></svg>
            </div>
            <h3>No API Keys Yet</h3>
            <p>Create your first API key to start using our AI services.</p>
            <a href="/keys/create" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                Create Your First Key
            </a>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
/* Keys Grid Layout */
.keys-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: var(--space-6);
}

/* API Key Card */
.api-key-card {
    background: var(--surface-primary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    overflow: hidden;
    transition: box-shadow var(--transition-fast), transform var(--transition-fast);
}

.api-key-card:hover {
    box-shadow: var(--shadow-md);
    transform: translateY(-2px);
}

.key-card-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    padding: var(--space-4) var(--space-5);
    border-bottom: 1px solid var(--border-light);
}

.key-info {
    flex: 1;
    min-width: 0;
}

.key-name {
    font-size: var(--font-size-base);
    font-weight: var(--font-weight-semibold);
    color: var(--text-primary);
    margin: 0 0 var(--space-2) 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.key-meta {
    display: flex;
    flex-wrap: wrap;
    gap: var(--space-2);
}

/* Key Actions Dropdown */
.key-actions-dropdown {
    position: relative;
}

.btn-icon {
    padding: var(--space-2);
    border-radius: var(--radius-md);
}

.key-dropdown-menu {
    position: absolute;
    top: 100%;
    right: 0;
    margin-top: var(--space-1);
    background: var(--surface-primary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-lg);
    min-width: 160px;
    z-index: var(--z-dropdown);
    display: none;
}

.key-dropdown-menu.show {
    display: block;
}

.dropdown-form {
    margin: 0;
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    padding: var(--space-2) var(--space-3);
    font-size: var(--font-size-sm);
    color: var(--text-primary);
    background: none;
    border: none;
    width: 100%;
    text-align: left;
    cursor: pointer;
    transition: background-color var(--transition-fast);
}

.dropdown-item:hover {
    background-color: var(--bg-tertiary);
}

.dropdown-item-danger {
    color: var(--color-error);
}

.dropdown-item-danger:hover {
    background-color: var(--color-error-light);
}

/* Key Card Body */
.key-card-body {
    padding: var(--space-4) var(--space-5);
}

.key-hash-display {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: var(--space-3);
    background: var(--bg-tertiary);
    border-radius: var(--radius-md);
    margin-bottom: var(--space-4);
}

.key-preview {
    font-family: var(--font-mono);
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
    background: none;
    padding: 0;
}

.copy-key-btn {
    padding: var(--space-1);
}

/* Key Usage */
.key-usage {
    margin-top: var(--space-3);
}

.usage-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--space-2);
}

.usage-label {
    font-size: var(--font-size-xs);
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.usage-value {
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-medium);
    color: var(--text-primary);
}

.usage-unlimited {
    font-size: var(--font-size-xs);
    color: var(--text-muted);
}

.progress-bar-wrapper {
    margin-top: var(--space-2);
}

.progress-bar-track {
    height: 6px;
    background: var(--bg-tertiary);
    border-radius: var(--radius-full);
    overflow: hidden;
}

.progress-bar-fill {
    height: 100%;
    border-radius: var(--radius-full);
    transition: width var(--transition-normal);
}

.progress-success { background: var(--color-success); }
.progress-warning { background: var(--color-warning); }
.progress-danger { background: var(--color-error); }

/* Key Card Footer */
.key-card-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--space-3) var(--space-5);
    background: var(--bg-secondary);
    border-top: 1px solid var(--border-light);
}

.key-created,
.key-expires {
    display: flex;
    align-items: center;
    gap: var(--space-1);
    font-size: var(--font-size-xs);
    color: var(--text-muted);
}

/* Search Filter */
.search-filter-group {
    display: flex;
    gap: var(--space-3);
}

.search-input-wrapper {
    position: relative;
}

.search-icon {
    position: absolute;
    left: var(--space-3);
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
    pointer-events: none;
}

.search-input {
    padding-left: var(--space-10);
    min-width: 200px;
}

/* Alert Content */
.alert-content {
    display: flex;
    gap: var(--space-3);
}

.alert-icon {
    flex-shrink: 0;
    color: var(--color-success);
}

/* Responsive */
@media (max-width: 767px) {
    .keys-grid {
        grid-template-columns: 1fr;
    }
    
    .page-header-flex {
        flex-direction: column;
    }
    
    .page-header-actions {
        width: 100%;
        flex-direction: column;
    }
    
    .search-filter-group {
        width: 100%;
    }
    
    .search-input-wrapper {
        width: 100%;
    }
    
    .search-input {
        width: 100%;
    }
}
</style>

<script>
function toggleKeyMenu(btn) {
    // Close all other menus first
    document.querySelectorAll('.key-dropdown-menu.show').forEach(menu => {
        if (menu !== btn.nextElementSibling) {
            menu.classList.remove('show');
        }
    });
    btn.nextElementSibling.classList.toggle('show');
}

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.key-actions-dropdown')) {
        document.querySelectorAll('.key-dropdown-menu.show').forEach(menu => {
            menu.classList.remove('show');
        });
    }
});

function copyToClipboard(elementId, btn) {
    const text = document.getElementById(elementId).textContent;
    navigator.clipboard.writeText(text).then(() => {
        const originalText = btn.innerHTML;
        btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg> Copied!';
        setTimeout(() => { btn.innerHTML = originalText; }, 2000);
    });
}

function copyText(text, btn) {
    navigator.clipboard.writeText(text).then(() => {
        btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>';
        setTimeout(() => {
            btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"></rect><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"></path></svg>';
        }, 2000);
    });
}

// Search functionality
document.getElementById('keySearch')?.addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    document.querySelectorAll('.api-key-card').forEach(card => {
        const name = card.dataset.name || '';
        card.style.display = name.includes(searchTerm) ? '' : 'none';
    });
});
</script>
