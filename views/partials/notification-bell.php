<div class="notification-wrapper">
    <button class="notification-bell" id="notificationBell" aria-label="Notifications" aria-expanded="false">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
        </svg>
        <span class="notification-badge" id="notificationBadge" style="display: none;">0</span>
    </button>
    <div class="notification-panel" id="notificationPanel" aria-hidden="true">
        <div class="notification-panel-header">
            <h4>Notifications</h4>
            <button data-mark-all-read class="btn btn-sm btn-ghost">Mark all read</button>
        </div>
        <div class="notification-panel-body notification-list" id="notificationList">
            <!-- Populated by NotificationManager JS -->
            <div class="notification-empty">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                </svg>
                <p>No new notifications</p>
            </div>
        </div>
        <div class="notification-panel-footer">
            <a href="/notifications">View All Notifications</a>
        </div>
    </div>
</div>
