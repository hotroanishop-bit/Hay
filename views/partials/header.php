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
        <div class="user-info">
            <?php if (isset($_SESSION['user'])): ?>
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['user']['name'] ?? 'User'); ?></span>
                <span class="user-email"><?php echo htmlspecialchars($_SESSION['user']['email'] ?? ''); ?></span>
            <?php else: ?>
                <a href="/login" class="btn btn-primary btn-sm">Login</a>
            <?php endif; ?>
        </div>
        
        <?php if (isset($_SESSION['user'])): ?>
            <div class="user-dropdown">
                <button class="user-avatar" id="userDropdownToggle">
                    <span class="avatar-initials">
                        <?php 
                        $name = $_SESSION['user']['name'] ?? 'U';
                        echo strtoupper(substr($name, 0, 1)); 
                        ?>
                    </span>
                </button>
                <div class="dropdown-menu" id="userDropdownMenu">
                    <a href="/profile" class="dropdown-item">Profile</a>
                    <a href="/settings" class="dropdown-item">Settings</a>
                    <hr class="dropdown-divider">
                    <a href="/logout" class="dropdown-item">Logout</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</header>
