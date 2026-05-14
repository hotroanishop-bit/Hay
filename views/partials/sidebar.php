<aside class="sidebar" id="sidebar">
    <nav class="sidebar-nav">
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="/dashboard" class="nav-link <?php echo ($currentPage === 'dashboard') ? 'active' : ''; ?>">
                    <span class="nav-icon">&#128200;</span>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="/keys" class="nav-link <?php echo ($currentPage === 'keys') ? 'active' : ''; ?>">
                    <span class="nav-icon">&#128273;</span>
                    <span class="nav-text">API Keys</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="/billing" class="nav-link <?php echo ($currentPage === 'billing') ? 'active' : ''; ?>">
                    <span class="nav-icon">&#128179;</span>
                    <span class="nav-text">Billing</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="/analytics" class="nav-link <?php echo ($currentPage === 'analytics') ? 'active' : ''; ?>">
                    <span class="nav-icon">&#128202;</span>
                    <span class="nav-text">Analytics</span>
                </a>
            </li>
            
            <?php if (isset($_SESSION['user']['is_admin']) && $_SESSION['user']['is_admin']): ?>
            <li class="nav-section">Admin</li>
            
            <li class="nav-item">
                <a href="/admin" class="nav-link <?php echo ($currentPage === 'admin') ? 'active' : ''; ?>">
                    <span class="nav-icon">&#9881;</span>
                    <span class="nav-text">Admin Panel</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="/admin/users" class="nav-link <?php echo ($currentPage === 'admin-users') ? 'active' : ''; ?>">
                    <span class="nav-icon">&#128101;</span>
                    <span class="nav-text">Users</span>
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </nav>
</aside>
