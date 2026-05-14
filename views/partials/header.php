<header class="topbar">
    <?php
    // Check for upcoming maintenance to show warning banner
    $upcomingMaintenance = null;
    try {
        $maintenanceModel = new ScheduledMaintenance();
        $upcomingMaintenance = $maintenanceModel->getNextUpcoming();
    } catch (Exception $e) {
        // Table might not exist yet
    }
    ?>
    
    <?php if ($upcomingMaintenance): ?>
    <div class="maintenance-warning-banner">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
            <line x1="12" y1="9" x2="12" y2="13"></line>
            <line x1="12" y1="17" x2="12.01" y2="17"></line>
        </svg>
        <span>
            <strong>Scheduled Maintenance:</strong> 
            <?= htmlspecialchars($upcomingMaintenance['title']) ?> - 
            <?= htmlspecialchars(date('M d, Y H:i', strtotime($upcomingMaintenance['starts_at']))) ?>
        </span>
    </div>
    <?php endif; ?>
    
    <div class="topbar-left">
        <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="3" y1="12" x2="21" y2="12"></line>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
        </button>
        <a href="/" class="logo">
            <span class="logo-text">API Keys</span>
        </a>
    </div>
    
    <div class="topbar-right">
        <?php if (isset($_SESSION['user'])): ?>
            <!-- Language Switcher -->
            <?php require VIEWS_PATH . '/partials/language-switcher.php'; ?>
            
            <!-- Theme Toggle -->
            <?php require VIEWS_PATH . '/partials/theme-toggle.php'; ?>
            
            <!-- Notification Bell -->
            <?php require VIEWS_PATH . '/partials/notification-bell.php'; ?>
            
            <!-- User Info & Dropdown -->
            <div class="user-info">
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['user']['name'] ?? 'User'); ?></span>
                <span class="user-balance">$<?php echo number_format($_SESSION['user']['balance'] ?? 0, 2); ?></span>
            </div>
            
            <div class="user-dropdown">
                <button class="user-avatar-btn" id="userDropdownToggle" aria-label="User menu" aria-expanded="false">
                    <?php if (!empty($_SESSION['user']['avatar_url'])): ?>
                        <img src="<?php echo htmlspecialchars($_SESSION['user']['avatar_url']); ?>" 
                             alt="<?php echo htmlspecialchars($_SESSION['user']['name'] ?? 'User'); ?>" 
                             class="avatar-image">
                    <?php else: ?>
                        <span class="avatar-initials">
                            <?php 
                            $name = $_SESSION['user']['name'] ?? 'U';
                            echo strtoupper(substr($name, 0, 1)); 
                            ?>
                        </span>
                    <?php endif; ?>
                </button>
                <div class="dropdown-menu" id="userDropdownMenu" aria-hidden="true">
                    <div class="dropdown-header">
                        <div class="dropdown-user-info">
                            <?php if (!empty($_SESSION['user']['avatar_url'])): ?>
                                <img src="<?php echo htmlspecialchars($_SESSION['user']['avatar_url']); ?>" 
                                     alt="" class="dropdown-avatar">
                            <?php else: ?>
                                <span class="dropdown-avatar-initials">
                                    <?php echo strtoupper(substr($_SESSION['user']['name'] ?? 'U', 0, 1)); ?>
                                </span>
                            <?php endif; ?>
                            <div class="dropdown-user-details">
                                <span class="dropdown-user-name"><?php echo htmlspecialchars($_SESSION['user']['name'] ?? 'User'); ?></span>
                                <span class="dropdown-user-email"><?php echo htmlspecialchars($_SESSION['user']['email'] ?? ''); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="dropdown-divider"></div>
                    <a href="/profile" class="dropdown-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        <span>Profile</span>
                    </a>
                    <a href="/billing" class="dropdown-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                            <line x1="1" y1="10" x2="23" y2="10"></line>
                        </svg>
                        <span>Billing</span>
                    </a>
                    <a href="/notifications" class="dropdown-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                        </svg>
                        <span>Notifications</span>
                    </a>
                    <?php if (isset($_SESSION['user']['is_admin']) && $_SESSION['user']['is_admin']): ?>
                    <a href="/admin/settings" class="dropdown-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="3"></circle>
                            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                        </svg>
                        <span>Settings</span>
                    </a>
                    <?php endif; ?>
                    <div class="dropdown-divider"></div>
                    <a href="/logout" class="dropdown-item dropdown-item-danger">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        <?php else: ?>
            <!-- Language Switcher for unauthenticated users -->
            <?php require VIEWS_PATH . '/partials/language-switcher.php'; ?>
            
            <!-- Theme Toggle for unauthenticated users -->
            <?php require VIEWS_PATH . '/partials/theme-toggle.php'; ?>
            
            <div class="auth-buttons">
                <a href="/login" class="btn btn-outline btn-sm">Login</a>
                <a href="/register" class="btn btn-primary btn-sm">Register</a>
            </div>
        <?php endif; ?>
    </div>
</header>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropdownToggle = document.getElementById('userDropdownToggle');
    const dropdownMenu = document.getElementById('userDropdownMenu');
    
    if (dropdownToggle && dropdownMenu) {
        dropdownToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            const isExpanded = dropdownMenu.classList.toggle('show');
            dropdownToggle.setAttribute('aria-expanded', isExpanded);
            dropdownMenu.setAttribute('aria-hidden', !isExpanded);
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!dropdownToggle.contains(e.target) && !dropdownMenu.contains(e.target)) {
                dropdownMenu.classList.remove('show');
                dropdownToggle.setAttribute('aria-expanded', 'false');
                dropdownMenu.setAttribute('aria-hidden', 'true');
            }
        });
        
        // Close dropdown on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                dropdownMenu.classList.remove('show');
                dropdownToggle.setAttribute('aria-expanded', 'false');
                dropdownMenu.setAttribute('aria-hidden', 'true');
            }
        });
    }
    
    // Sidebar toggle
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('sidebar-open');
            document.body.classList.toggle('sidebar-expanded');
        });
    }
});
</script>
