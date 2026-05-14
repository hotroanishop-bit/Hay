<div class="page-container">
    <div class="page-header">
        <h1 class="page-title">
            <span class="page-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 12 20 22 4 22 4 12"></polyline>
                    <rect x="2" y="7" width="20" height="5"></rect>
                    <line x1="12" y1="22" x2="12" y2="7"></line>
                    <path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"></path>
                    <path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"></path>
                </svg>
            </span>
            <?php echo __('admin.giftcodes.title', 'Quan ly Gift Codes'); ?>
        </h1>
        <div class="page-actions">
            <a href="/admin/giftcodes/create" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <?php echo __('admin.giftcodes.create', 'Tao Gift Code'); ?>
            </a>
        </div>
    </div>

    <!-- Statistics -->
    <div class="stats-grid mb-4">
        <div class="stat-card">
            <div class="stat-icon bg-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 12 20 22 4 22 4 12"></polyline>
                    <rect x="2" y="7" width="20" height="5"></rect>
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?php echo number_format($stats['total_codes'] ?? 0); ?></div>
                <div class="stat-label"><?php echo __('admin.giftcodes.total', 'Tong so codes'); ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-success">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?php echo number_format($stats['active_codes'] ?? 0); ?></div>
                <div class="stat-label"><?php echo __('admin.giftcodes.active', 'Codes hoat dong'); ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-info">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?php echo number_format($stats['total_redemptions'] ?? 0); ?></div>
                <div class="stat-label"><?php echo __('admin.giftcodes.redemptions', 'Luot doi'); ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-warning">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="1" x2="12" y2="23"></line>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?php echo number_format($stats['tokens_given'] ?? 0); ?></div>
                <div class="stat-label"><?php echo __('admin.giftcodes.tokens_given', 'Tokens da phat'); ?></div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="filter-form">
                <div class="filter-row">
                    <div class="filter-group">
                        <input type="text" name="search" class="form-control" placeholder="Tim kiem code..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                    </div>
                    <div class="filter-group">
                        <select name="type" class="form-control">
                            <option value="">Tat ca loai</option>
                            <option value="tokens" <?php echo ($_GET['type'] ?? '') === 'tokens' ? 'selected' : ''; ?>>Tokens</option>
                            <option value="credits" <?php echo ($_GET['type'] ?? '') === 'credits' ? 'selected' : ''; ?>>Credits</option>
                            <option value="plan" <?php echo ($_GET['type'] ?? '') === 'plan' ? 'selected' : ''; ?>>Plan</option>
                            <option value="vip_days" <?php echo ($_GET['type'] ?? '') === 'vip_days' ? 'selected' : ''; ?>>VIP Days</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <select name="is_active" class="form-control">
                            <option value="">Tat ca trang thai</option>
                            <option value="1" <?php echo ($_GET['is_active'] ?? '') === '1' ? 'selected' : ''; ?>>Hoat dong</option>
                            <option value="0" <?php echo ($_GET['is_active'] ?? '') === '0' ? 'selected' : ''; ?>>Da tat</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        Loc
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Gift Codes Table -->
    <div class="card">
        <div class="card-body">
            <?php if (empty($giftcodes['data'])): ?>
                <div class="empty-state">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                        <polyline points="20 12 20 22 4 22 4 12"></polyline>
                        <rect x="2" y="7" width="20" height="5"></rect>
                    </svg>
                    <p>Chua co gift code nao</p>
                    <a href="/admin/giftcodes/create" class="btn btn-primary">Tao Gift Code</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Loai</th>
                                <th>Gia tri</th>
                                <th>Su dung</th>
                                <th>Het han</th>
                                <th>Trang thai</th>
                                <th>Nguoi tao</th>
                                <th>Hanh dong</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($giftcodes['data'] as $code): ?>
                                <tr>
                                    <td>
                                        <code class="code-badge"><?php echo htmlspecialchars($code['code']); ?></code>
                                        <button class="btn-copy" onclick="copyCode('<?php echo htmlspecialchars($code['code']); ?>')" title="Copy">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                                            </svg>
                                        </button>
                                    </td>
                                    <td>
                                        <?php
                                        $typeLabels = [
                                            'tokens' => '<span class="badge badge-primary">Tokens</span>',
                                            'credits' => '<span class="badge badge-success">Credits</span>',
                                            'plan' => '<span class="badge badge-info">Plan</span>',
                                            'vip_days' => '<span class="badge badge-warning">VIP Days</span>'
                                        ];
                                        echo $typeLabels[$code['type']] ?? $code['type'];
                                        ?>
                                    </td>
                                    <td><strong><?php echo number_format($code['value'], 2); ?></strong></td>
                                    <td>
                                        <?php echo $code['used_count']; ?>/<?php echo $code['max_uses'] > 0 ? $code['max_uses'] : 'Unlimited'; ?>
                                    </td>
                                    <td>
                                        <?php if ($code['expires_at']): ?>
                                            <?php 
                                            $expired = strtotime($code['expires_at']) < time();
                                            ?>
                                            <span class="<?php echo $expired ? 'text-danger' : 'text-muted'; ?>">
                                                <?php echo date('d/m/Y', strtotime($code['expires_at'])); ?>
                                                <?php if ($expired): ?>(Het han)<?php endif; ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">Khong gioi han</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($code['is_active']): ?>
                                            <span class="status-badge status-active">Hoat dong</span>
                                        <?php else: ?>
                                            <span class="status-badge status-inactive">Da tat</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="text-muted"><?php echo htmlspecialchars($code['creator_name'] ?? 'System'); ?></span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="/admin/giftcodes/<?php echo $code['id']; ?>" class="btn btn-sm btn-outline" title="Chi tiet">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                    <circle cx="12" cy="12" r="3"></circle>
                                                </svg>
                                            </a>
                                            <form method="POST" action="/admin/giftcodes/<?php echo $code['id']; ?>/toggle" style="display: inline;">
                                                <button type="submit" class="btn btn-sm <?php echo $code['is_active'] ? 'btn-warning' : 'btn-success'; ?>" title="<?php echo $code['is_active'] ? 'Vo hieu hoa' : 'Kich hoat'; ?>">
                                                    <?php if ($code['is_active']): ?>
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                                        </svg>
                                                    <?php else: ?>
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                                            <path d="M7 11V7a5 5 0 0 1 9.9-1"></path>
                                                        </svg>
                                                    <?php endif; ?>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($giftcodes['total_pages'] > 1): ?>
                    <div class="pagination-container">
                        <nav class="pagination">
                            <?php for ($i = 1; $i <= $giftcodes['total_pages']; $i++): ?>
                                <a href="?page=<?php echo $i; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['type']) ? '&type=' . urlencode($_GET['type']) : ''; ?><?php echo isset($_GET['is_active']) ? '&is_active=' . urlencode($_GET['is_active']) : ''; ?>" 
                                   class="page-link <?php echo $i === $giftcodes['page'] ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                        </nav>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.stat-card {
    background: var(--bg-secondary);
    border-radius: var(--border-radius);
    padding: 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.stat-icon.bg-primary { background: var(--primary-color); }
.stat-icon.bg-success { background: #10b981; }
.stat-icon.bg-info { background: #3b82f6; }
.stat-icon.bg-warning { background: #f59e0b; }

.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
}

.stat-label {
    color: var(--text-secondary);
    font-size: 0.875rem;
}

.filter-form .filter-row {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.filter-group {
    flex: 1;
    min-width: 150px;
}

.code-badge {
    background: var(--bg-tertiary);
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-family: monospace;
    font-size: 0.875rem;
}

.btn-copy {
    background: none;
    border: none;
    cursor: pointer;
    padding: 0.25rem;
    margin-left: 0.5rem;
    color: var(--text-secondary);
    vertical-align: middle;
}

.btn-copy:hover {
    color: var(--primary-color);
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}

.status-active {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
}

.status-inactive {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}

.btn-group {
    display: flex;
    gap: 0.5rem;
}

.empty-state {
    text-align: center;
    padding: 3rem;
    color: var(--text-secondary);
}

.pagination-container {
    display: flex;
    justify-content: center;
    margin-top: 1.5rem;
}

.pagination {
    display: flex;
    gap: 0.5rem;
}

.page-link {
    padding: 0.5rem 1rem;
    border-radius: var(--border-radius);
    background: var(--bg-secondary);
    color: var(--text-primary);
    text-decoration: none;
}

.page-link:hover,
.page-link.active {
    background: var(--primary-color);
    color: white;
}
</style>

<script>
function copyCode(code) {
    navigator.clipboard.writeText(code).then(() => {
        // Show toast notification
        const toast = document.createElement('div');
        toast.className = 'toast-notification';
        toast.textContent = 'Da copy: ' + code;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 2000);
    });
}
</script>
