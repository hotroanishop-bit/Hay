<div class="page-header">
    <div class="page-header-content">
        <h1 class="page-title"><?php echo __('webhooks.title', 'Webhooks'); ?></h1>
        <p class="page-description"><?php echo __('webhooks.description', 'Configure webhook endpoints to receive real-time event notifications'); ?></p>
    </div>
    <div class="page-actions">
        <a href="/webhooks/create" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <?php echo __('webhooks.create', 'Create Webhook'); ?>
        </a>
    </div>
</div>

<!-- Info Card -->
<div class="info-card card">
    <div class="info-card-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="16" x2="12" y2="12"></line>
            <line x1="12" y1="8" x2="12.01" y2="8"></line>
        </svg>
    </div>
    <div class="info-card-content">
        <h4><?php echo __('webhooks.info_title', 'What are Webhooks?'); ?></h4>
        <p><?php echo __('webhooks.info_text', 'Webhooks allow you to receive HTTP POST notifications when events occur in your account. Each webhook request includes an HMAC signature for verification.'); ?></p>
    </div>
</div>

<!-- Webhooks List -->
<?php if (empty($webhooks)): ?>
<div class="empty-state card">
    <div class="empty-state-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
            <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
        </svg>
    </div>
    <h3><?php echo __('webhooks.empty_title', 'No Webhooks Yet'); ?></h3>
    <p><?php echo __('webhooks.empty_text', 'Create your first webhook to receive real-time event notifications.'); ?></p>
    <a href="/webhooks/create" class="btn btn-primary">
        <?php echo __('webhooks.create_first', 'Create First Webhook'); ?>
    </a>
</div>
<?php else: ?>
<div class="webhooks-grid">
    <?php foreach ($webhooks as $webhook): ?>
    <div class="webhook-card card" data-webhook-id="<?php echo $webhook['id']; ?>">
        <div class="webhook-card-header">
            <div class="webhook-status <?php echo $webhook['is_active'] ? 'active' : 'inactive'; ?>">
                <span class="status-dot"></span>
                <?php echo $webhook['is_active'] ? __('webhooks.active', 'Active') : __('webhooks.inactive', 'Inactive'); ?>
            </div>
            <div class="webhook-actions-menu">
                <button type="button" class="btn btn-ghost btn-icon webhook-menu-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="1"></circle>
                        <circle cx="19" cy="12" r="1"></circle>
                        <circle cx="5" cy="12" r="1"></circle>
                    </svg>
                </button>
                <div class="webhook-dropdown">
                    <a href="/webhooks/<?php echo $webhook['id']; ?>/edit" class="dropdown-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                        <?php echo __('common.edit', 'Edit'); ?>
                    </a>
                    <a href="/webhooks/<?php echo $webhook['id']; ?>/logs" class="dropdown-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                        </svg>
                        <?php echo __('webhooks.view_logs', 'View Logs'); ?>
                    </a>
                    <button type="button" class="dropdown-item test-webhook-btn" data-id="<?php echo $webhook['id']; ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="5 3 19 12 5 21 5 3"></polygon>
                        </svg>
                        <?php echo __('webhooks.send_test', 'Send Test'); ?>
                    </button>
                    <hr class="dropdown-divider">
                    <form action="/webhooks/<?php echo $webhook['id']; ?>/delete" method="POST" class="delete-webhook-form">
                        <button type="submit" class="dropdown-item danger">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            </svg>
                            <?php echo __('common.delete', 'Delete'); ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="webhook-card-body">
            <div class="webhook-url">
                <span class="url-label"><?php echo __('webhooks.endpoint', 'Endpoint'); ?></span>
                <code class="url-value"><?php echo htmlspecialchars($webhook['url']); ?></code>
            </div>
            
            <div class="webhook-events">
                <span class="events-label"><?php echo __('webhooks.events', 'Events'); ?></span>
                <div class="events-tags">
                    <?php foreach ($webhook['events'] as $event): ?>
                    <span class="event-tag"><?php echo htmlspecialchars($availableEvents[$event] ?? $event); ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <div class="webhook-card-footer">
            <div class="webhook-stats">
                <div class="stat">
                    <span class="stat-value"><?php echo $webhook['stats']['total']; ?></span>
                    <span class="stat-label"><?php echo __('webhooks.total_calls', 'Total'); ?></span>
                </div>
                <div class="stat success">
                    <span class="stat-value"><?php echo $webhook['stats']['success']; ?></span>
                    <span class="stat-label"><?php echo __('webhooks.successful', 'Success'); ?></span>
                </div>
                <div class="stat error">
                    <span class="stat-value"><?php echo $webhook['stats']['failed']; ?></span>
                    <span class="stat-label"><?php echo __('webhooks.failed', 'Failed'); ?></span>
                </div>
            </div>
            <div class="webhook-created">
                <?php echo __('webhooks.created', 'Created'); ?>: <?php echo date('M d, Y', strtotime($webhook['created_at'])); ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Test Result Modal -->
<div class="modal" id="testResultModal">
    <div class="modal-backdrop"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3><?php echo __('webhooks.test_result', 'Test Result'); ?></h3>
            <button type="button" class="btn btn-ghost btn-icon close-modal">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="modal-body" id="testResultBody">
            <!-- Results will be injected here -->
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-ghost close-modal"><?php echo __('common.close', 'Close'); ?></button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Dropdown menu handling
    document.querySelectorAll('.webhook-menu-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const dropdown = this.nextElementSibling;
            const isOpen = dropdown.classList.contains('show');
            
            // Close all dropdowns
            document.querySelectorAll('.webhook-dropdown.show').forEach(d => d.classList.remove('show'));
            
            if (!isOpen) {
                dropdown.classList.add('show');
            }
        });
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function() {
        document.querySelectorAll('.webhook-dropdown.show').forEach(d => d.classList.remove('show'));
    });
    
    // Delete confirmation
    document.querySelectorAll('.delete-webhook-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm('<?php echo __('webhooks.delete_confirm', 'Are you sure you want to delete this webhook? All delivery logs will also be deleted.'); ?>')) {
                e.preventDefault();
            }
        });
    });
    
    // Test webhook
    document.querySelectorAll('.test-webhook-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const webhookId = this.dataset.id;
            const modal = document.getElementById('testResultModal');
            const resultBody = document.getElementById('testResultBody');
            
            resultBody.innerHTML = '<div class="test-loading"><div class="spinner"></div><p>Sending test webhook...</p></div>';
            modal.classList.add('show');
            
            try {
                const response = await fetch('/webhooks/' + webhookId + '/test', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                let statusClass = data.success ? 'success' : 'error';
                let statusIcon = data.success 
                    ? '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>'
                    : '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>';
                
                resultBody.innerHTML = `
                    <div class="test-result ${statusClass}">
                        <div class="test-result-icon">${statusIcon}</div>
                        <div class="test-result-status">${data.success ? 'Success' : 'Failed'}</div>
                        <div class="test-result-message">${escapeHtml(data.message)}</div>
                        <div class="test-result-details">
                            <div class="detail-row">
                                <span class="detail-label">Response Code:</span>
                                <span class="detail-value">${data.response_code || 'N/A'}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Attempts:</span>
                                <span class="detail-value">${data.attempts}</span>
                            </div>
                            ${data.response_body ? `
                                <div class="detail-row full">
                                    <span class="detail-label">Response Body:</span>
                                    <pre class="detail-code">${escapeHtml(data.response_body.substring(0, 500))}</pre>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                `;
            } catch (error) {
                resultBody.innerHTML = `
                    <div class="test-result error">
                        <div class="test-result-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                        </div>
                        <div class="test-result-status">Error</div>
                        <div class="test-result-message">Failed to send test webhook: ${escapeHtml(error.message)}</div>
                    </div>
                `;
            }
        });
    });
    
    // Modal handling
    document.querySelectorAll('.close-modal, .modal-backdrop').forEach(el => {
        el.addEventListener('click', function() {
            document.querySelectorAll('.modal.show').forEach(m => m.classList.remove('show'));
        });
    });
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
</script>
