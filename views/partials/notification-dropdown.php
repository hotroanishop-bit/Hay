<?php
/**
 * Notification Dropdown Partial
 * Enhanced notification dropdown with bell icon and unread badge
 * Include this in the header for logged-in users
 */
?>
<div class="notification-dropdown-wrapper" id="notificationDropdownWrapper">
    <button class="notification-bell-btn" id="notificationDropdownTrigger" aria-label="Notifications" aria-expanded="false">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
        </svg>
        <span class="notification-badge-count" id="notificationBadgeCount" style="display: none;">0</span>
    </button>
    
    <div class="notification-dropdown-panel" id="notificationDropdownPanel" aria-hidden="true">
        <div class="notification-dropdown-header">
            <h4>Notifications</h4>
            <button type="button" class="btn btn-sm btn-ghost" id="markAllReadDropdown">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                Mark all read
            </button>
        </div>
        
        <div class="notification-dropdown-body" id="notificationDropdownBody">
            <div class="notification-loading" id="notificationDropdownLoading">
                <div class="spinner-sm"></div>
            </div>
            
            <div class="notification-empty-dropdown" id="notificationDropdownEmpty" style="display: none;">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                </svg>
                <p>No new notifications</p>
            </div>
            
            <div class="notification-dropdown-list" id="notificationDropdownList">
                <!-- Notifications populated by JavaScript -->
            </div>
        </div>
        
        <div class="notification-dropdown-footer">
            <a href="/notifications" class="view-all-link">View All Notifications</a>
        </div>
    </div>
</div>

<style>
/* Notification Dropdown Styles */
.notification-dropdown-wrapper {
    position: relative;
}

.notification-bell-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border: none;
    background: transparent;
    color: var(--text-secondary);
    cursor: pointer;
    border-radius: var(--radius-md);
    transition: all var(--transition-fast);
    position: relative;
}

.notification-bell-btn:hover {
    background: var(--bg-hover);
    color: var(--text-primary);
}

.notification-badge-count {
    position: absolute;
    top: 4px;
    right: 4px;
    min-width: 18px;
    height: 18px;
    padding: 0 5px;
    font-size: 11px;
    font-weight: 600;
    line-height: 18px;
    text-align: center;
    color: white;
    background: var(--color-error);
    border-radius: 9px;
    border: 2px solid var(--surface-primary);
}

.notification-dropdown-panel {
    position: absolute;
    top: 100%;
    right: 0;
    width: 380px;
    max-height: 480px;
    background: var(--surface-primary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-lg);
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all var(--transition-fast);
    z-index: 1000;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.notification-dropdown-panel.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.notification-dropdown-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: var(--space-3) var(--space-4);
    border-bottom: 1px solid var(--border-color);
    flex-shrink: 0;
}

.notification-dropdown-header h4 {
    margin: 0;
    font-size: var(--font-size-base);
    font-weight: var(--font-weight-semibold);
}

.notification-dropdown-body {
    flex: 1;
    overflow-y: auto;
    max-height: 360px;
}

.notification-loading {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: var(--space-6);
}

.spinner-sm {
    width: 24px;
    height: 24px;
    border: 2px solid var(--border-color);
    border-top-color: var(--color-primary);
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.notification-empty-dropdown {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: var(--space-8);
    text-align: center;
    color: var(--text-muted);
}

.notification-empty-dropdown svg {
    margin-bottom: var(--space-3);
    opacity: 0.5;
}

.notification-empty-dropdown p {
    margin: 0;
    font-size: var(--font-size-sm);
}

.notification-dropdown-list {
    padding: var(--space-2) 0;
}

.notification-dropdown-item {
    display: flex;
    gap: var(--space-3);
    padding: var(--space-3) var(--space-4);
    border-bottom: 1px solid var(--border-light);
    transition: background var(--transition-fast);
    cursor: pointer;
    text-decoration: none;
    color: inherit;
}

.notification-dropdown-item:hover {
    background: var(--bg-hover);
}

.notification-dropdown-item:last-child {
    border-bottom: none;
}

.notification-dropdown-item.unread {
    background: var(--color-primary-light);
}

.notification-dropdown-item.unread:hover {
    background: var(--bg-hover);
}

.notification-item-icon {
    flex-shrink: 0;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: var(--bg-tertiary);
}

.notification-item-icon.icon-info { background: var(--color-info-light); color: var(--color-info); }
.notification-item-icon.icon-success { background: var(--color-success-light); color: var(--color-success); }
.notification-item-icon.icon-warning { background: var(--color-warning-light); color: var(--color-warning); }
.notification-item-icon.icon-error { background: var(--color-error-light); color: var(--color-error); }

.notification-item-content {
    flex: 1;
    min-width: 0;
}

.notification-item-title {
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-medium);
    color: var(--text-primary);
    margin-bottom: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.notification-item-message {
    font-size: var(--font-size-xs);
    color: var(--text-secondary);
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.notification-item-time {
    font-size: var(--font-size-xs);
    color: var(--text-muted);
    margin-top: 4px;
}

.notification-dropdown-footer {
    padding: var(--space-3) var(--space-4);
    border-top: 1px solid var(--border-color);
    text-align: center;
    flex-shrink: 0;
}

.view-all-link {
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-medium);
    color: var(--color-primary);
}

.view-all-link:hover {
    text-decoration: underline;
}

/* Responsive */
@media (max-width: 639px) {
    .notification-dropdown-panel {
        width: 100vw;
        right: -60px;
        border-radius: 0;
        border-left: none;
        border-right: none;
    }
}
</style>

<script>
(function() {
    const wrapper = document.getElementById('notificationDropdownWrapper');
    const trigger = document.getElementById('notificationDropdownTrigger');
    const panel = document.getElementById('notificationDropdownPanel');
    const badge = document.getElementById('notificationBadgeCount');
    const loading = document.getElementById('notificationDropdownLoading');
    const empty = document.getElementById('notificationDropdownEmpty');
    const list = document.getElementById('notificationDropdownList');
    const markAllBtn = document.getElementById('markAllReadDropdown');
    
    let isLoaded = false;
    
    // Toggle dropdown
    if (trigger && panel) {
        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            const isOpen = panel.classList.toggle('show');
            trigger.setAttribute('aria-expanded', isOpen);
            panel.setAttribute('aria-hidden', !isOpen);
            
            if (isOpen && !isLoaded) {
                loadNotifications();
            }
        });
    }
    
    // Close on click outside
    document.addEventListener('click', function(e) {
        if (panel && !wrapper.contains(e.target)) {
            panel.classList.remove('show');
            trigger.setAttribute('aria-expanded', 'false');
            panel.setAttribute('aria-hidden', 'true');
        }
    });
    
    // Close on escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && panel) {
            panel.classList.remove('show');
            trigger.setAttribute('aria-expanded', 'false');
            panel.setAttribute('aria-hidden', 'true');
        }
    });
    
    // Load notifications
    async function loadNotifications() {
        if (loading) loading.style.display = 'flex';
        if (empty) empty.style.display = 'none';
        if (list) list.innerHTML = '';
        
        try {
            const response = await fetch('/api/notifications/recent');
            const data = await response.json();
            
            if (loading) loading.style.display = 'none';
            isLoaded = true;
            
            if (data.notifications && data.notifications.length > 0) {
                renderNotifications(data.notifications);
            } else {
                if (empty) empty.style.display = 'flex';
            }
        } catch (error) {
            console.error('Failed to load notifications:', error);
            if (loading) loading.style.display = 'none';
            if (empty) empty.style.display = 'flex';
        }
    }
    
    // Render notifications
    function renderNotifications(notifications) {
        if (!list) return;
        
        list.innerHTML = notifications.slice(0, 5).map(n => {
            const iconClass = 'icon-' + (n.type || 'info');
            const unreadClass = n.read_at ? '' : 'unread';
            const link = n.link || '/notifications';
            
            return `
                <a href="${escapeHtml(link)}" class="notification-dropdown-item ${unreadClass}" data-id="${n.id}">
                    <div class="notification-item-icon ${iconClass}">
                        ${getIcon(n.type)}
                    </div>
                    <div class="notification-item-content">
                        <div class="notification-item-title">${escapeHtml(n.title)}</div>
                        <div class="notification-item-message">${escapeHtml(n.message || '')}</div>
                        <div class="notification-item-time">${formatTime(n.created_at)}</div>
                    </div>
                </a>
            `;
        }).join('');
    }
    
    // Update badge count
    async function updateBadgeCount() {
        try {
            const response = await fetch('/api/notifications/unread-count');
            const data = await response.json();
            
            if (badge) {
                const count = data.count || 0;
                badge.textContent = count > 99 ? '99+' : count;
                badge.style.display = count > 0 ? 'block' : 'none';
            }
        } catch (error) {
            console.error('Failed to get unread count:', error);
        }
    }
    
    // Mark all as read
    if (markAllBtn) {
        markAllBtn.addEventListener('click', async function(e) {
            e.stopPropagation();
            try {
                await fetch('/notifications/read-all', { method: 'POST' });
                if (badge) {
                    badge.textContent = '0';
                    badge.style.display = 'none';
                }
                document.querySelectorAll('.notification-dropdown-item.unread').forEach(el => {
                    el.classList.remove('unread');
                });
            } catch (error) {
                console.error('Failed to mark all as read:', error);
            }
        });
    }
    
    // Helper functions
    function getIcon(type) {
        switch (type) {
            case 'success':
                return '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>';
            case 'warning':
                return '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>';
            case 'error':
                return '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>';
            default:
                return '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>';
        }
    }
    
    function formatTime(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const seconds = Math.floor((now - date) / 1000);
        
        if (seconds < 60) return 'Just now';
        if (seconds < 3600) return Math.floor(seconds / 60) + 'm ago';
        if (seconds < 86400) return Math.floor(seconds / 3600) + 'h ago';
        if (seconds < 604800) return Math.floor(seconds / 86400) + 'd ago';
        
        return date.toLocaleDateString();
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text || '';
        return div.innerHTML;
    }
    
    // Initialize
    updateBadgeCount();
    
    // Refresh count periodically
    setInterval(updateBadgeCount, 60000); // Every minute
    
    // Expose globally for other scripts
    window.NotificationDropdown = {
        updateBadge: updateBadgeCount,
        reload: loadNotifications
    };
})();
</script>
