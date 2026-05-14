<div class="page-header">
    <h1 class="page-title">Notifications</h1>
    <div class="page-actions">
        <button type="button" class="btn btn-ghost btn-sm" id="markAllReadBtn">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="9 11 12 14 22 4"></polyline>
                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
            </svg>
            Mark All Read
        </button>
        <button type="button" class="btn btn-ghost btn-sm" id="deleteAllReadBtn">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="3 6 5 6 21 6"></polyline>
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
            </svg>
            Delete Read
        </button>
    </div>
</div>

<!-- Notification Filters -->
<div class="notification-filters card">
    <div class="filter-tabs">
        <button type="button" class="filter-tab active" data-filter="all">
            All
            <span class="filter-count" id="allCount">0</span>
        </button>
        <button type="button" class="filter-tab" data-filter="unread">
            Unread
            <span class="filter-count" id="unreadCount">0</span>
        </button>
        <button type="button" class="filter-tab" data-filter="info">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="16" x2="12" y2="12"></line>
                <line x1="12" y1="8" x2="12.01" y2="8"></line>
            </svg>
            Info
        </button>
        <button type="button" class="filter-tab" data-filter="success">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            Success
        </button>
        <button type="button" class="filter-tab" data-filter="warning">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                <line x1="12" y1="9" x2="12" y2="13"></line>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
            Warning
        </button>
        <button type="button" class="filter-tab" data-filter="error">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="15" y1="9" x2="9" y2="15"></line>
                <line x1="9" y1="9" x2="15" y2="15"></line>
            </svg>
            Error
        </button>
    </div>
</div>

<!-- Notifications List -->
<div class="notifications-page-list card" id="notificationsPageList">
    <!-- Loading State -->
    <div class="notifications-loading" id="notificationsLoading">
        <div class="spinner"></div>
        <p>Loading notifications...</p>
    </div>
    
    <!-- Empty State -->
    <div class="notifications-empty" id="notificationsEmpty" style="display: none;">
        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
        </svg>
        <h3>No notifications</h3>
        <p>You're all caught up! Check back later for updates.</p>
    </div>
    
    <!-- Notifications Container -->
    <div class="notifications-container" id="notificationsContainer">
        <!-- Notifications will be loaded here by JavaScript -->
    </div>
    
    <!-- Load More Button -->
    <div class="notifications-load-more" id="loadMoreContainer" style="display: none;">
        <button type="button" class="btn btn-outline" id="loadMoreBtn">Load More</button>
    </div>
</div>

<style>
/* Notifications Page Styles */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.page-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0;
}

.page-actions {
    display: flex;
    gap: 0.5rem;
}

.notification-filters {
    margin-bottom: 1rem;
    padding: 0;
}

.filter-tabs {
    display: flex;
    gap: 0.25rem;
    padding: 0.5rem;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
}

.filter-tabs::-webkit-scrollbar {
    display: none;
}

.filter-tab {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.5rem 0.75rem;
    border: none;
    background: transparent;
    color: var(--text-secondary);
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    border-radius: 0.375rem;
    transition: all 0.2s;
    white-space: nowrap;
}

.filter-tab:hover {
    background: var(--bg-hover);
    color: var(--text-primary);
}

.filter-tab.active {
    background: var(--primary-color);
    color: white;
}

.filter-tab.active .filter-count {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

.filter-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 1.25rem;
    height: 1.25rem;
    padding: 0 0.375rem;
    font-size: 0.75rem;
    font-weight: 600;
    background: var(--bg-secondary);
    border-radius: 9999px;
}

.notifications-page-list {
    padding: 0;
}

.notifications-loading,
.notifications-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem 1.5rem;
    text-align: center;
    color: var(--text-secondary);
}

.notifications-loading .spinner {
    width: 2rem;
    height: 2rem;
    border: 3px solid var(--border-color);
    border-top-color: var(--primary-color);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.notifications-empty svg {
    color: var(--text-tertiary);
    margin-bottom: 1rem;
}

.notifications-empty h3 {
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0 0 0.5rem;
}

.notifications-empty p {
    margin: 0;
    font-size: 0.875rem;
}

.notifications-container {
    display: flex;
    flex-direction: column;
}

.notification-page-item {
    display: flex;
    gap: 1rem;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--border-color);
    transition: background-color 0.15s;
}

.notification-page-item:last-child {
    border-bottom: none;
}

.notification-page-item:hover {
    background: var(--bg-hover);
}

.notification-page-item.unread {
    background: var(--bg-highlight);
}

.notification-page-item.unread:hover {
    background: var(--bg-highlight-hover, var(--bg-hover));
}

.notification-icon {
    flex-shrink: 0;
    width: 2.5rem;
    height: 2.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: var(--bg-secondary);
}

.notification-icon.info {
    background: var(--info-bg, #e0f2fe);
    color: var(--info-color, #0284c7);
}

.notification-icon.success {
    background: var(--success-bg, #dcfce7);
    color: var(--success-color, #16a34a);
}

.notification-icon.warning {
    background: var(--warning-bg, #fef3c7);
    color: var(--warning-color, #d97706);
}

.notification-icon.error {
    background: var(--error-bg, #fee2e2);
    color: var(--error-color, #dc2626);
}

.notification-content {
    flex: 1;
    min-width: 0;
}

.notification-title {
    font-weight: 500;
    color: var(--text-primary);
    margin-bottom: 0.25rem;
}

.notification-message {
    font-size: 0.875rem;
    color: var(--text-secondary);
    margin-bottom: 0.5rem;
    line-height: 1.5;
}

.notification-meta {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 0.75rem;
    color: var(--text-tertiary);
}

.notification-actions {
    display: flex;
    align-items: flex-start;
    gap: 0.25rem;
}

.notification-action-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2rem;
    height: 2rem;
    border: none;
    background: transparent;
    color: var(--text-secondary);
    cursor: pointer;
    border-radius: 0.375rem;
    transition: all 0.15s;
}

.notification-action-btn:hover {
    background: var(--bg-hover);
    color: var(--text-primary);
}

.notification-action-btn.delete:hover {
    background: var(--error-bg, #fee2e2);
    color: var(--error-color, #dc2626);
}

.notifications-load-more {
    padding: 1rem;
    text-align: center;
    border-top: 1px solid var(--border-color);
}

/* Responsive */
@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .page-actions {
        width: 100%;
    }
    
    .page-actions .btn {
        flex: 1;
    }
    
    .notification-page-item {
        padding: 0.875rem 1rem;
    }
    
    .notification-actions {
        flex-direction: column;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const notificationsContainer = document.getElementById('notificationsContainer');
    const notificationsLoading = document.getElementById('notificationsLoading');
    const notificationsEmpty = document.getElementById('notificationsEmpty');
    const loadMoreContainer = document.getElementById('loadMoreContainer');
    const loadMoreBtn = document.getElementById('loadMoreBtn');
    const markAllReadBtn = document.getElementById('markAllReadBtn');
    const deleteAllReadBtn = document.getElementById('deleteAllReadBtn');
    const filterTabs = document.querySelectorAll('.filter-tab');
    
    let currentFilter = 'all';
    let currentPage = 1;
    let hasMore = true;
    const perPage = 20;
    
    // Initialize
    loadNotifications();
    loadCounts();
    
    // Filter tabs
    filterTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            filterTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            currentFilter = this.dataset.filter;
            currentPage = 1;
            notificationsContainer.innerHTML = '';
            loadNotifications();
        });
    });
    
    // Load more
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function() {
            currentPage++;
            loadNotifications(true);
        });
    }
    
    // Mark all read
    if (markAllReadBtn) {
        markAllReadBtn.addEventListener('click', async function() {
            try {
                const response = await fetch('/api/notifications/mark-all-read', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });
                
                if (response.ok) {
                    document.querySelectorAll('.notification-page-item.unread').forEach(item => {
                        item.classList.remove('unread');
                    });
                    loadCounts();
                    
                    // Update notification badge in header
                    if (window.NotificationManager) {
                        window.NotificationManager.updateBadge(0);
                    }
                }
            } catch (error) {
                console.error('Failed to mark all as read:', error);
            }
        });
    }
    
    // Delete all read
    if (deleteAllReadBtn) {
        deleteAllReadBtn.addEventListener('click', async function() {
            if (!confirm('Are you sure you want to delete all read notifications?')) {
                return;
            }
            
            try {
                const response = await fetch('/api/notifications/delete-read', {
                    method: 'DELETE'
                });
                
                if (response.ok) {
                    document.querySelectorAll('.notification-page-item:not(.unread)').forEach(item => {
                        item.remove();
                    });
                    loadCounts();
                    checkEmpty();
                }
            } catch (error) {
                console.error('Failed to delete read notifications:', error);
            }
        });
    }
    
    async function loadNotifications(append = false) {
        if (!append) {
            notificationsLoading.style.display = 'flex';
            notificationsEmpty.style.display = 'none';
        }
        loadMoreContainer.style.display = 'none';
        
        try {
            let url = `/api/notifications?page=${currentPage}&per_page=${perPage}`;
            if (currentFilter !== 'all') {
                url += `&filter=${currentFilter}`;
            }
            
            const response = await fetch(url);
            const data = await response.json();
            
            notificationsLoading.style.display = 'none';
            
            if (data.notifications && data.notifications.length > 0) {
                data.notifications.forEach(notification => {
                    notificationsContainer.appendChild(createNotificationElement(notification));
                });
                
                hasMore = data.has_more || false;
                loadMoreContainer.style.display = hasMore ? 'block' : 'none';
            } else if (!append) {
                notificationsEmpty.style.display = 'flex';
            }
        } catch (error) {
            console.error('Failed to load notifications:', error);
            notificationsLoading.style.display = 'none';
            notificationsEmpty.style.display = 'flex';
        }
    }
    
    async function loadCounts() {
        try {
            const response = await fetch('/api/notifications/counts');
            const data = await response.json();
            
            document.getElementById('allCount').textContent = data.total || 0;
            document.getElementById('unreadCount').textContent = data.unread || 0;
        } catch (error) {
            console.error('Failed to load counts:', error);
        }
    }
    
    function createNotificationElement(notification) {
        const div = document.createElement('div');
        div.className = `notification-page-item ${notification.read_at ? '' : 'unread'}`;
        div.dataset.id = notification.id;
        
        const iconSvg = getIconForType(notification.type);
        const timeAgo = formatTimeAgo(notification.created_at);
        
        div.innerHTML = `
            <div class="notification-icon ${notification.type}">
                ${iconSvg}
            </div>
            <div class="notification-content">
                <div class="notification-title">${escapeHtml(notification.title)}</div>
                <div class="notification-message">${escapeHtml(notification.message)}</div>
                <div class="notification-meta">
                    <span>${timeAgo}</span>
                    ${notification.action_url ? `<a href="${escapeHtml(notification.action_url)}">View details</a>` : ''}
                </div>
            </div>
            <div class="notification-actions">
                ${!notification.read_at ? `
                    <button type="button" class="notification-action-btn mark-read" title="Mark as read" data-id="${notification.id}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </button>
                ` : ''}
                <button type="button" class="notification-action-btn delete" title="Delete" data-id="${notification.id}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    </svg>
                </button>
            </div>
        `;
        
        // Bind action handlers
        const markReadBtn = div.querySelector('.mark-read');
        if (markReadBtn) {
            markReadBtn.addEventListener('click', () => markAsRead(notification.id, div));
        }
        
        const deleteBtn = div.querySelector('.delete');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', () => deleteNotification(notification.id, div));
        }
        
        return div;
    }
    
    async function markAsRead(id, element) {
        try {
            const response = await fetch(`/api/notifications/${id}/read`, {
                method: 'POST'
            });
            
            if (response.ok) {
                element.classList.remove('unread');
                const markReadBtn = element.querySelector('.mark-read');
                if (markReadBtn) {
                    markReadBtn.remove();
                }
                loadCounts();
            }
        } catch (error) {
            console.error('Failed to mark as read:', error);
        }
    }
    
    async function deleteNotification(id, element) {
        try {
            const response = await fetch(`/api/notifications/${id}`, {
                method: 'DELETE'
            });
            
            if (response.ok) {
                element.remove();
                loadCounts();
                checkEmpty();
            }
        } catch (error) {
            console.error('Failed to delete notification:', error);
        }
    }
    
    function checkEmpty() {
        if (notificationsContainer.children.length === 0) {
            notificationsEmpty.style.display = 'flex';
        }
    }
    
    function getIconForType(type) {
        switch (type) {
            case 'success':
                return '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>';
            case 'warning':
                return '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>';
            case 'error':
                return '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>';
            default:
                return '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>';
        }
    }
    
    function formatTimeAgo(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const seconds = Math.floor((now - date) / 1000);
        
        if (seconds < 60) return 'Just now';
        if (seconds < 3600) return Math.floor(seconds / 60) + ' min ago';
        if (seconds < 86400) return Math.floor(seconds / 3600) + ' hours ago';
        if (seconds < 604800) return Math.floor(seconds / 86400) + ' days ago';
        
        return date.toLocaleDateString();
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
</script>
