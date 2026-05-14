<header class="topbar">
    <div class="topbar-left">
        <button class="sidebar-toggle" id="sidebarToggle">
            <span class="hamburger-icon"></span>
        </button>
        <a href="/" class="logo">
            <span class="logo-text">API Keys</span>
        </a>
    </div>
    
    <div class="topbar-right">
        <?php if (isset($_SESSION['user'])): ?>
            <div class="user-info">
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['user']['name'] ?? 'User'); ?></span>
                <span class="user-balance">$<?php echo number_format($_SESSION['user']['balance'] ?? 0, 2); ?></span>
            </div>
            
            <div class="user-dropdown">
                <button class="user-avatar-btn" id="userDropdownToggle" aria-label="User menu">
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
                <div class="dropdown-menu" id="userDropdownMenu">
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
            <div class="auth-buttons">
                <a href="/login" class="btn btn-outline btn-sm">Login</a>
                <a href="/register" class="btn btn-primary btn-sm">Register</a>
            </div>
        <?php endif; ?>
    </div>
</header>

<style>
/* Header User Dropdown Styles */
.topbar-right {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.user-info {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    margin-right: 0.5rem;
}

.user-name {
    font-weight: 500;
    font-size: 0.875rem;
    color: var(--text-primary, #1a1a2e);
}

.user-balance {
    font-size: 0.75rem;
    color: var(--text-secondary, #6b7280);
}

.user-dropdown {
    position: relative;
}

.user-avatar-btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 2px solid var(--border-color, #e5e7eb);
    background: var(--primary-color, #6366f1);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.user-avatar-btn:hover {
    border-color: var(--primary-color, #6366f1);
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.avatar-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-initials {
    color: white;
    font-weight: 600;
    font-size: 1rem;
}

.dropdown-menu {
    position: absolute;
    top: calc(100% + 8px);
    right: 0;
    min-width: 240px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    border: 1px solid var(--border-color, #e5e7eb);
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: opacity 0.2s, transform 0.2s, visibility 0.2s;
    z-index: 1000;
}

.dropdown-menu.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.dropdown-header {
    padding: 1rem;
}

.dropdown-user-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.dropdown-avatar,
.dropdown-avatar-initials {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    flex-shrink: 0;
}

.dropdown-avatar {
    object-fit: cover;
}

.dropdown-avatar-initials {
    background: var(--primary-color, #6366f1);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}

.dropdown-user-details {
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.dropdown-user-name {
    font-weight: 600;
    font-size: 0.875rem;
    color: var(--text-primary, #1a1a2e);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.dropdown-user-email {
    font-size: 0.75rem;
    color: var(--text-secondary, #6b7280);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.dropdown-divider {
    height: 1px;
    background: var(--border-color, #e5e7eb);
    margin: 0;
    border: none;
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    color: var(--text-primary, #1a1a2e);
    text-decoration: none;
    font-size: 0.875rem;
    transition: background-color 0.15s;
}

.dropdown-item:hover {
    background-color: var(--bg-hover, #f3f4f6);
}

.dropdown-item svg {
    color: var(--text-secondary, #6b7280);
    flex-shrink: 0;
}

.dropdown-item-danger {
    color: #dc2626;
}

.dropdown-item-danger svg {
    color: #dc2626;
}

.dropdown-item-danger:hover {
    background-color: #fef2f2;
}

.auth-buttons {
    display: flex;
    gap: 0.5rem;
}

.btn-outline {
    background: transparent;
    border: 1px solid var(--border-color, #e5e7eb);
    color: var(--text-primary, #1a1a2e);
}

.btn-outline:hover {
    background: var(--bg-hover, #f3f4f6);
}

/* Responsive */
@media (max-width: 768px) {
    .user-info {
        display: none;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropdownToggle = document.getElementById('userDropdownToggle');
    const dropdownMenu = document.getElementById('userDropdownMenu');
    
    if (dropdownToggle && dropdownMenu) {
        dropdownToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdownMenu.classList.toggle('show');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!dropdownToggle.contains(e.target) && !dropdownMenu.contains(e.target)) {
                dropdownMenu.classList.remove('show');
            }
        });
        
        // Close dropdown on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                dropdownMenu.classList.remove('show');
            }
        });
    }
});
</script>
