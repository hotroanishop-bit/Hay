<div class="page-header">
    <div class="page-header-content">
        <h1 class="page-title"><?php echo __('webhooks.delivery_logs', 'Delivery Logs'); ?></h1>
        <p class="page-description">
            <?php echo __('webhooks.logs_for', 'Logs for'); ?>: 
            <code class="inline-code"><?php echo htmlspecialchars($webhook['url']); ?></code>
        </p>
    </div>
    <div class="page-actions">
        <a href="/webhooks" class="btn btn-ghost">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            <?php echo __('common.back', 'Back'); ?>
        </a>
        <a href="/webhooks/<?php echo $webhook['id']; ?>/edit" class="btn btn-outline">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
            </svg>
            <?php echo __('webhooks.edit_webhook', 'Edit Webhook'); ?>
        </a>
    </div>
</div>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card card">
        <div class="stat-card-icon total">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
            </svg>
        </div>
        <div class="stat-card-content">
            <span class="stat-card-value"><?php echo number_format($stats['total']); ?></span>
            <span class="stat-card-label"><?php echo __('webhooks.total_deliveries', 'Total Deliveries'); ?></span>
        </div>
    </div>
    
    <div class="stat-card card">
        <div class="stat-card-icon success">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
        </div>
        <div class="stat-card-content">
            <span class="stat-card-value"><?php echo number_format($stats['success']); ?></span>
            <span class="stat-card-label"><?php echo __('webhooks.successful', 'Successful'); ?></span>
        </div>
    </div>
    
    <div class="stat-card card">
        <div class="stat-card-icon error">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="15" y1="9" x2="9" y2="15"></line>
                <line x1="9" y1="9" x2="15" y2="15"></line>
            </svg>
        </div>
        <div class="stat-card-content">
            <span class="stat-card-value"><?php echo number_format($stats['failed']); ?></span>
            <span class="stat-card-label"><?php echo __('webhooks.failed', 'Failed'); ?></span>
        </div>
    </div>
    
    <div class="stat-card card">
        <div class="stat-card-icon info">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="23 4 23 10 17 10"></polyline>
                <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
            </svg>
        </div>
        <div class="stat-card-content">
            <span class="stat-card-value"><?php echo $stats['avg_attempts']; ?></span>
            <span class="stat-card-label"><?php echo __('webhooks.avg_attempts', 'Avg Attempts'); ?></span>
        </div>
    </div>
</div>

<!-- Subscribed Events -->
<div class="subscribed-events card">
    <h3 class="section-title"><?php echo __('webhooks.subscribed_events', 'Subscribed Events'); ?></h3>
    <div class="events-list">
        <?php foreach ($webhook['events'] as $event): ?>
        <div class="event-item">
            <span class="event-name"><?php echo htmlspecialchars($availableEvents[$event] ?? $event); ?></span>
            <span class="event-count"><?php echo isset($eventCounts[$event]) ? number_format($eventCounts[$event]) : 0; ?> <?php echo __('webhooks.calls', 'calls'); ?></span>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Logs Table -->
<div class="logs-container card">
    <h3 class="section-title"><?php echo __('webhooks.recent_deliveries', 'Recent Deliveries'); ?></h3>
    
    <?php if (empty($logs)): ?>
    <div class="empty-state">
        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
            <polyline points="14 2 14 8 20 8"></polyline>
            <line x1="16" y1="13" x2="8" y2="13"></line>
            <line x1="16" y1="17" x2="8" y2="17"></line>
        </svg>
        <h4><?php echo __('webhooks.no_logs', 'No Delivery Logs'); ?></h4>
        <p><?php echo __('webhooks.no_logs_text', 'Webhook delivery logs will appear here once events are triggered.'); ?></p>
    </div>
    <?php else: ?>
    <div class="logs-list">
        <?php foreach ($logs as $log): ?>
        <div class="log-item" data-log-id="<?php echo $log['id']; ?>">
            <div class="log-item-header">
                <div class="log-status <?php echo ($log['response_code'] >= 200 && $log['response_code'] < 300) ? 'success' : 'error'; ?>">
                    <?php if ($log['response_code'] >= 200 && $log['response_code'] < 300): ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <?php else: ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                    </svg>
                    <?php endif; ?>
                    <span class="status-code"><?php echo $log['response_code'] ?? 'N/A'; ?></span>
                </div>
                <div class="log-event">
                    <span class="event-badge"><?php echo htmlspecialchars($log['event']); ?></span>
                </div>
                <div class="log-attempts">
                    <?php echo $log['attempts']; ?> <?php echo __('webhooks.attempts', 'attempts'); ?>
                </div>
                <div class="log-time">
                    <?php echo date('M d, Y H:i:s', strtotime($log['created_at'])); ?>
                </div>
                <button type="button" class="btn btn-ghost btn-icon toggle-details-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </button>
            </div>
            <div class="log-item-details" style="display: none;">
                <div class="details-section">
                    <h5><?php echo __('webhooks.request_payload', 'Request Payload'); ?></h5>
                    <pre class="code-block"><code><?php echo htmlspecialchars(json_encode($log['payload'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)); ?></code></pre>
                </div>
                <?php if (!empty($log['response_body'])): ?>
                <div class="details-section">
                    <h5><?php echo __('webhooks.response_body', 'Response Body'); ?></h5>
                    <pre class="code-block response"><code><?php echo htmlspecialchars(substr($log['response_body'], 0, 1000)); ?><?php echo strlen($log['response_body']) > 1000 ? '...' : ''; ?></code></pre>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Pagination -->
    <?php if ($pagination['total'] > $pagination['per_page']): ?>
    <div class="pagination">
        <?php 
        $totalPages = ceil($pagination['total'] / $pagination['per_page']);
        $currentPage = $pagination['page'];
        ?>
        
        <?php if ($currentPage > 1): ?>
        <a href="/webhooks/<?php echo $webhook['id']; ?>/logs?page=<?php echo $currentPage - 1; ?>" class="btn btn-ghost btn-sm">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="15 18 9 12 15 6"></polyline>
            </svg>
            <?php echo __('common.previous', 'Previous'); ?>
        </a>
        <?php endif; ?>
        
        <span class="pagination-info">
            <?php echo __('common.page', 'Page'); ?> <?php echo $currentPage; ?> <?php echo __('common.of', 'of'); ?> <?php echo $totalPages; ?>
        </span>
        
        <?php if ($pagination['has_more']): ?>
        <a href="/webhooks/<?php echo $webhook['id']; ?>/logs?page=<?php echo $currentPage + 1; ?>" class="btn btn-ghost btn-sm">
            <?php echo __('common.next', 'Next'); ?>
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="9 18 15 12 9 6"></polyline>
            </svg>
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle log details
    document.querySelectorAll('.toggle-details-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const logItem = this.closest('.log-item');
            const details = logItem.querySelector('.log-item-details');
            const isOpen = details.style.display !== 'none';
            
            details.style.display = isOpen ? 'none' : 'block';
            this.classList.toggle('rotated', !isOpen);
        });
    });
});
</script>
