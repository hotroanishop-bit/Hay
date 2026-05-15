<div class="page-header">
    <div class="page-header-content">
        <h1 class="page-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="3" y1="9" x2="21" y2="9"></line>
                <line x1="9" y1="21" x2="9" y2="9"></line>
            </svg>
            API Key Templates
        </h1>
        <p class="page-description">Luu cau hinh de tao key nhanh hon</p>
    </div>
    <div class="page-header-actions">
        <a href="/keys" class="btn btn-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Quay lai
        </a>
        <button class="btn btn-primary" onclick="openCreateModal()">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Tao Template
        </button>
    </div>
</div>

<div class="templates-container">
    <?php if (empty($templates)): ?>
        <div class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="3" y1="9" x2="21" y2="9"></line>
                <line x1="9" y1="21" x2="9" y2="9"></line>
            </svg>
            <h3>Chua co template nao</h3>
            <p>Tao template de luu cau hinh API key thuong dung</p>
            <button class="btn btn-primary" onclick="openCreateModal()">Tao Template dau tien</button>
        </div>
    <?php else: ?>
        <div class="templates-grid">
            <?php foreach ($templates as $template): ?>
                <div class="template-card <?php echo $template['is_default'] ? 'is-default' : ''; ?>">
                    <?php if ($template['is_default']): ?>
                        <span class="default-badge">Mac dinh</span>
                    <?php endif; ?>
                    
                    <div class="template-header">
                        <h3><?php echo htmlspecialchars($template['name']); ?></h3>
                    </div>
                    
                    <div class="template-settings">
                        <?php 
                        $settings = $template['settings'];
                        if (!empty($settings['rate_limit'])): ?>
                            <div class="setting-item">
                                <span class="setting-label">Rate Limit:</span>
                                <span class="setting-value"><?php echo htmlspecialchars($settings['rate_limit']); ?> req/min</span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($settings['usage_limit'])): ?>
                            <div class="setting-item">
                                <span class="setting-label">Usage Limit:</span>
                                <span class="setting-value"><?php echo number_format($settings['usage_limit']); ?> tokens</span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($settings['allowed_models'])): ?>
                            <div class="setting-item">
                                <span class="setting-label">Models:</span>
                                <span class="setting-value"><?php echo count($settings['allowed_models']); ?> models</span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($settings['ip_whitelist'])): ?>
                            <div class="setting-item">
                                <span class="setting-label">IP Whitelist:</span>
                                <span class="setting-value"><?php echo count($settings['ip_whitelist']); ?> IPs</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="template-meta">
                        <span class="meta-date">Tao: <?php echo date('d/m/Y', strtotime($template['created_at'])); ?></span>
                    </div>
                    
                    <div class="template-actions">
                        <button class="btn btn-sm btn-primary" onclick="applyTemplate(<?php echo $template['id']; ?>)">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 12h14"></path>
                                <path d="m12 5 7 7-7 7"></path>
                            </svg>
                            Ap dung
                        </button>
                        <?php if (!$template['is_default']): ?>
                            <button class="btn btn-sm btn-secondary" onclick="setDefault(<?php echo $template['id']; ?>)">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                </svg>
                            </button>
                        <?php endif; ?>
                        <button class="btn btn-sm btn-danger" onclick="deleteTemplate(<?php echo $template['id']; ?>)">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Create Template Modal -->
<div class="modal-overlay" id="create-modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Tao Template moi</h3>
            <button class="modal-close" onclick="closeCreateModal()">&times;</button>
        </div>
        <form id="template-form">
            <div class="modal-body">
                <div class="form-group">
                    <label for="template-name">Ten template</label>
                    <input type="text" id="template-name" name="name" required placeholder="VD: Production Key">
                </div>
                
                <div class="form-group">
                    <label for="rate-limit">Rate Limit (req/min)</label>
                    <input type="number" id="rate-limit" name="settings[rate_limit]" min="1" max="1000" value="60">
                </div>
                
                <div class="form-group">
                    <label for="usage-limit">Usage Limit (tokens, 0 = khong gioi han)</label>
                    <input type="number" id="usage-limit" name="settings[usage_limit]" min="0" value="0">
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_default" value="1">
                        Dat lam template mac dinh
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeCreateModal()">Huy</button>
                <button type="submit" class="btn btn-primary">Tao Template</button>
            </div>
        </form>
    </div>
</div>

<style>
.templates-container {
    max-width: 1200px;
    margin: 0 auto;
}

.templates-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: var(--space-4);
}

.template-card {
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-xl);
    padding: var(--space-4);
    position: relative;
    transition: all 0.2s;
}

.template-card:hover {
    border-color: var(--primary-color);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.template-card.is-default {
    border-color: var(--success-color);
}

.default-badge {
    position: absolute;
    top: var(--space-2);
    right: var(--space-2);
    padding: var(--space-1) var(--space-2);
    background: var(--success-color);
    color: white;
    font-size: var(--text-xs);
    font-weight: 600;
    border-radius: var(--radius-sm);
}

.template-header h3 {
    margin: 0 0 var(--space-3) 0;
    font-size: var(--text-lg);
    color: var(--text-primary);
}

.template-settings {
    margin-bottom: var(--space-3);
}

.setting-item {
    display: flex;
    justify-content: space-between;
    padding: var(--space-2) 0;
    border-bottom: 1px solid var(--border-color);
    font-size: var(--text-sm);
}

.setting-item:last-child {
    border-bottom: none;
}

.setting-label {
    color: var(--text-secondary);
}

.setting-value {
    font-weight: 500;
    color: var(--text-primary);
}

.template-meta {
    margin-bottom: var(--space-3);
}

.meta-date {
    font-size: var(--text-xs);
    color: var(--text-muted);
}

.template-actions {
    display: flex;
    gap: var(--space-2);
}

.btn-sm {
    padding: var(--space-2) var(--space-3);
    font-size: var(--text-sm);
}

.btn-sm svg {
    margin-right: var(--space-1);
}

.empty-state {
    text-align: center;
    padding: var(--space-12);
    background: var(--bg-secondary);
    border-radius: var(--radius-xl);
}

.empty-state svg {
    color: var(--text-muted);
    margin-bottom: var(--space-4);
}

.empty-state h3 {
    margin: 0 0 var(--space-2) 0;
    color: var(--text-primary);
}

.empty-state p {
    margin: 0 0 var(--space-4) 0;
    color: var(--text-secondary);
}

/* Modal */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-content {
    background: var(--bg-primary);
    border-radius: var(--radius-xl);
    width: 100%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--space-4);
    border-bottom: 1px solid var(--border-color);
}

.modal-header h3 {
    margin: 0;
}

.modal-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: var(--text-secondary);
}

.modal-body {
    padding: var(--space-4);
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: var(--space-2);
    padding: var(--space-4);
    border-top: 1px solid var(--border-color);
}

.form-group {
    margin-bottom: var(--space-4);
}

.form-group label {
    display: block;
    margin-bottom: var(--space-2);
    font-weight: 500;
    color: var(--text-secondary);
}

.form-group input[type="text"],
.form-group input[type="number"] {
    width: 100%;
    padding: var(--space-3);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    background: var(--bg-secondary);
    color: var(--text-primary);
}

.form-group input[type="checkbox"] {
    margin-right: var(--space-2);
}
</style>

<script>
function openCreateModal() {
    document.getElementById('create-modal').style.display = 'flex';
}

function closeCreateModal() {
    document.getElementById('create-modal').style.display = 'none';
}

document.getElementById('template-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('/api-keys/templates', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast('success', 'Template da duoc tao');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast('error', data.error || 'Co loi xay ra');
        }
    })
    .catch(err => {
        console.error(err);
        showToast('error', 'Loi ket noi');
    });
});

function applyTemplate(id) {
    fetch('/api-keys/templates/' + id + '/apply', {
        method: 'POST'
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Store settings and redirect to create key
            sessionStorage.setItem('keyTemplateSettings', JSON.stringify(data.settings));
            window.location.href = '/keys/create?template=' + id;
        } else {
            showToast('error', data.error || 'Co loi xay ra');
        }
    });
}

function setDefault(id) {
    fetch('/api-keys/templates/' + id + '/default', {
        method: 'POST'
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast('success', 'Da dat lam mac dinh');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast('error', data.error || 'Co loi xay ra');
        }
    });
}

function deleteTemplate(id) {
    if (!confirm('Ban co chac muon xoa template nay?')) return;
    
    fetch('/api-keys/templates/' + id, {
        method: 'DELETE'
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast('success', 'Template da duoc xoa');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast('error', data.error || 'Co loi xay ra');
        }
    });
}

function showToast(type, message) {
    if (typeof window.showNotification === 'function') {
        window.showNotification(type, message);
    } else {
        alert(message);
    }
}

// Close modal on overlay click
document.getElementById('create-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeCreateModal();
    }
});
</script>
