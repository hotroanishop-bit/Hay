/**
 * Notification Manager
 * Handles notification bell, badge, panel, and AJAX operations
 */
const NotificationManager = (function() {
    'use strict';

    var badgeEl = null;
    var panelEl = null;
    var bellEl = null;
    var listEl = null;
    var pollInterval = null;
    var isInitialized = false;

    // Configuration
    var config = {
        pollIntervalMs: 60000, // Poll every 60 seconds
        unreadCountUrl: '/api/notifications/unread-count',
        recentUrl: '/api/notifications/recent',
        markReadUrl: '/notifications/{id}/read',
        markAllReadUrl: '/notifications/read-all'
    };

    /**
     * Initialize the notification manager
     * @param {Object} options - Configuration options
     */
    function init(options) {
        if (isInitialized) return;

        // Merge options
        if (options) {
            Object.keys(options).forEach(function(key) {
                if (config.hasOwnProperty(key)) {
                    config[key] = options[key];
                }
            });
        }

        // Get DOM elements
        badgeEl = document.querySelector('.notification-badge');
        panelEl = document.querySelector('.notification-panel');
        bellEl = document.querySelector('.notification-bell');
        listEl = panelEl ? panelEl.querySelector('.notification-panel-body, .notification-list') : null;

        if (!bellEl) {
            return; // No notification bell on this page
        }

        bindEvents();
        fetchUnreadCount();
        startPolling();
        isInitialized = true;
    }

    /**
     * Bind all event listeners
     */
    function bindEvents() {
        // Toggle panel on bell click
        bellEl.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            togglePanel();
        });

        // Close panel when clicking outside
        document.addEventListener('click', function(e) {
            if (panelEl && !panelEl.contains(e.target) && !bellEl.contains(e.target)) {
                closePanel();
            }
        });

        // Handle clicks inside panel
        if (panelEl) {
            panelEl.addEventListener('click', function(e) {
                // Mark single notification as read
                var markReadBtn = e.target.closest('[data-mark-read]');
                if (markReadBtn) {
                    e.preventDefault();
                    var id = markReadBtn.dataset.markRead;
                    markAsRead(id);
                    return;
                }

                // Mark all as read
                var markAllBtn = e.target.closest('[data-mark-all-read]');
                if (markAllBtn) {
                    e.preventDefault();
                    markAllRead();
                    return;
                }

                // Click on notification item
                var notificationItem = e.target.closest('.notification-item');
                if (notificationItem && notificationItem.classList.contains('unread')) {
                    var itemId = notificationItem.dataset.id;
                    if (itemId) {
                        markAsRead(itemId);
                    }
                }
            });
        }

        // Escape key to close
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && isPanelOpen()) {
                closePanel();
            }
        });
    }

    /**
     * Fetch unread notification count
     */
    function fetchUnreadCount() {
        fetch(config.unreadCountUrl, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
        .then(function(res) {
            if (!res.ok) throw new Error('Failed to fetch');
            return res.json();
        })
        .then(function(data) {
            var count = data.count || data.unread_count || 0;
            updateBadge(count);
        })
        .catch(function(e) {
            console.error('Failed to fetch notification count:', e);
        });
    }

    /**
     * Update the notification badge count
     * @param {number} count
     */
    function updateBadge(count) {
        if (!badgeEl) return;

        count = parseInt(count, 10) || 0;
        
        if (count > 0) {
            badgeEl.textContent = count > 99 ? '99+' : count;
            badgeEl.style.display = 'flex';
            bellEl.classList.add('has-notifications');
            
            // Add pulse animation for new notifications
            badgeEl.classList.add('pulse');
            setTimeout(function() {
                badgeEl.classList.remove('pulse');
            }, 3000);
        } else {
            badgeEl.textContent = '';
            badgeEl.style.display = 'none';
            bellEl.classList.remove('has-notifications');
        }
    }

    /**
     * Open the notification panel
     */
    function openPanel() {
        if (!panelEl) return;
        
        panelEl.classList.add('open');
        bellEl.setAttribute('aria-expanded', 'true');
        fetchNotifications();
    }

    /**
     * Close the notification panel
     */
    function closePanel() {
        if (!panelEl) return;
        
        panelEl.classList.remove('open');
        bellEl.setAttribute('aria-expanded', 'false');
    }

    /**
     * Toggle the notification panel
     */
    function togglePanel() {
        if (isPanelOpen()) {
            closePanel();
        } else {
            openPanel();
        }
    }

    /**
     * Check if panel is open
     * @returns {boolean}
     */
    function isPanelOpen() {
        return panelEl && panelEl.classList.contains('open');
    }

    /**
     * Fetch recent notifications
     */
    function fetchNotifications() {
        if (!listEl) return;

        // Show loading state
        listEl.innerHTML = '<div class="notification-loading" style="padding: 20px; text-align: center;">Loading...</div>';

        fetch(config.recentUrl, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
        .then(function(res) {
            if (!res.ok) throw new Error('Failed to fetch');
            return res.json();
        })
        .then(function(data) {
            var notifications = data.notifications || data.data || [];
            renderNotifications(notifications);
        })
        .catch(function(e) {
            listEl.innerHTML = '<div class="notification-empty"><div class="notification-empty-text">Failed to load notifications</div></div>';
        });
    }

    /**
     * Render notifications to the panel
     * @param {Array} notifications
     */
    function renderNotifications(notifications) {
        if (!listEl) return;

        if (!notifications || notifications.length === 0) {
            listEl.innerHTML = '<div class="notification-empty">' +
                '<div class="notification-empty-icon">&#x1F514;</div>' +
                '<div class="notification-empty-text">No notifications</div>' +
                '</div>';
            return;
        }

        var html = notifications.map(function(n) {
            var iconClass = 'notification-item-icon notification-item-icon-' + (n.type || 'default');
            var icon = getNotificationIcon(n.type);
            var unreadClass = n.is_read ? '' : ' unread';
            
            return '<div class="notification-item' + unreadClass + '" data-id="' + escapeHtml(n.id) + '">' +
                '<div class="' + iconClass + '">' + icon + '</div>' +
                '<div class="notification-item-content">' +
                    '<div class="notification-item-title">' + escapeHtml(n.title) + '</div>' +
                    '<div class="notification-item-message">' + escapeHtml(n.message || n.body || '') + '</div>' +
                    '<div class="notification-item-time">' + formatTime(n.created_at) + '</div>' +
                '</div>' +
                (!n.is_read ? '<div class="notification-item-actions"><button class="notification-item-dismiss" data-mark-read="' + escapeHtml(n.id) + '" title="Mark as read">&times;</button></div>' : '') +
            '</div>';
        }).join('');

        listEl.innerHTML = html;
    }

    /**
     * Get icon for notification type
     * @param {string} type
     * @returns {string}
     */
    function getNotificationIcon(type) {
        var icons = {
            success: '&#x2714;',
            error: '&#x2716;',
            warning: '&#x26A0;',
            info: '&#x2139;',
            default: '&#x1F514;'
        };
        return icons[type] || icons.default;
    }

    /**
     * Mark a notification as read
     * @param {string|number} id
     */
    function markAsRead(id) {
        var url = config.markReadUrl.replace('{id}', id);
        
        fetch(url, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(function(res) {
            if (!res.ok) throw new Error('Failed to mark as read');
            fetchUnreadCount();
            
            // Update UI immediately
            var item = listEl ? listEl.querySelector('[data-id="' + id + '"]') : null;
            if (item) {
                item.classList.remove('unread');
                var btn = item.querySelector('.notification-item-dismiss');
                if (btn) btn.remove();
            }
        })
        .catch(function(e) {
            console.error('Failed to mark notification as read:', e);
        });
    }

    /**
     * Mark all notifications as read
     */
    function markAllRead() {
        fetch(config.markAllReadUrl, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(function(res) {
            if (!res.ok) throw new Error('Failed to mark all as read');
            fetchUnreadCount();
            fetchNotifications();
        })
        .catch(function(e) {
            console.error('Failed to mark all as read:', e);
        });
    }

    /**
     * Start polling for new notifications
     */
    function startPolling() {
        if (pollInterval) {
            clearInterval(pollInterval);
        }
        pollInterval = setInterval(fetchUnreadCount, config.pollIntervalMs);
    }

    /**
     * Stop polling
     */
    function stopPolling() {
        if (pollInterval) {
            clearInterval(pollInterval);
            pollInterval = null;
        }
    }

    /**
     * Escape HTML to prevent XSS
     * @param {string} str
     * @returns {string}
     */
    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        var div = document.createElement('div');
        div.textContent = String(str);
        return div.innerHTML;
    }

    /**
     * Format timestamp to relative time
     * @param {string} timestamp
     * @returns {string}
     */
    function formatTime(timestamp) {
        if (!timestamp) return '';
        
        var date = new Date(timestamp);
        var now = new Date();
        var diff = (now - date) / 1000; // seconds

        if (diff < 60) return 'just now';
        if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
        if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
        if (diff < 604800) return Math.floor(diff / 86400) + 'd ago';
        
        // Older than a week, show date
        return date.toLocaleDateString();
    }

    // Public API
    return {
        init: init,
        fetchUnreadCount: fetchUnreadCount,
        updateBadge: updateBadge,
        openPanel: openPanel,
        closePanel: closePanel,
        togglePanel: togglePanel,
        markAsRead: markAsRead,
        markAllRead: markAllRead,
        startPolling: startPolling,
        stopPolling: stopPolling
    };
})();
