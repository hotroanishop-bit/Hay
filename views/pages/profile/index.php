<?php
/**
 * Profile Settings Page
 * Variables: $pageTitle, $currentPage, $user
 */
?>

<div class="profile-container">
    <div class="profile-header">
        <h1>Profile Settings</h1>
        <p>Manage your account information and preferences</p>
    </div>

    <div class="profile-content">
        <!-- Avatar Section -->
        <div class="profile-section">
            <h2>Profile Picture</h2>
            <div class="avatar-section">
                <div class="avatar-preview">
                    <?php if (!empty($user['avatar_url'])): ?>
                        <img src="<?= htmlspecialchars($user['avatar_url']) ?>" alt="Profile Avatar" class="avatar-image">
                    <?php else: ?>
                        <div class="avatar-placeholder">
                            <span><?= htmlspecialchars(strtoupper(substr($user['name'] ?? 'U', 0, 1))) ?></span>
                        </div>
                    <?php endif; ?>
                </div>
                <form action="/profile/avatar" method="POST" enctype="multipart/form-data" class="avatar-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <div class="avatar-upload">
                        <label for="avatar" class="btn btn-secondary">Choose Image</label>
                        <input type="file" id="avatar" name="avatar" accept="image/png,image/jpeg,image/jpg,image/gif" class="file-input">
                        <span class="file-name">No file chosen</span>
                    </div>
                    <p class="form-text">Allowed: PNG, JPG, JPEG, GIF. Max size: 2MB</p>
                    <button type="submit" class="btn btn-primary">Upload Avatar</button>
                </form>
            </div>
        </div>

        <!-- Profile Information Section -->
        <div class="profile-section">
            <h2>Personal Information</h2>
            <form action="/profile/update" method="POST" class="profile-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                    <?php if (empty($user['email_verified_at'])): ?>
                        <span class="badge badge-warning">Not Verified</span>
                    <?php else: ?>
                        <span class="badge badge-success">Verified</span>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
        </div>

        <!-- Quick Links Section -->
        <div class="profile-section">
            <h2>Account Security</h2>
            <div class="quick-links">
                <a href="/profile/password" class="quick-link">
                    <div class="quick-link-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                    </div>
                    <div class="quick-link-content">
                        <h3>Change Password</h3>
                        <p>Update your account password</p>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </a>

                <a href="/profile/2fa" class="quick-link">
                    <div class="quick-link-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                    </div>
                    <div class="quick-link-content">
                        <h3>Two-Factor Authentication</h3>
                        <p>
                            <?php if (!empty($user['two_factor_enabled'])): ?>
                                <span class="status-enabled">Enabled</span>
                            <?php else: ?>
                                <span class="status-disabled">Not configured</span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </a>
            </div>
        </div>

        <!-- Danger Zone -->
        <div class="profile-section danger-zone">
            <h2>Danger Zone</h2>
            <div class="danger-content">
                <div class="danger-info">
                    <h3>Delete Account</h3>
                    <p>Permanently delete your account and all associated data. This action cannot be undone.</p>
                </div>
                <button type="button" class="btn btn-danger" onclick="document.getElementById('deleteModal').style.display='flex'">Delete Account</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Account Modal -->
<div id="deleteModal" class="modal" style="display:none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Delete Account</h2>
            <button type="button" class="modal-close" onclick="document.getElementById('deleteModal').style.display='none'">&times;</button>
        </div>
        <div class="modal-body">
            <p class="warning-text">Are you sure you want to delete your account? This action is permanent and cannot be undone.</p>
            <form action="/profile/delete" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <div class="form-group">
                    <label for="delete_password">Enter your password to confirm</label>
                    <input type="password" id="delete_password" name="password" class="form-control" required>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('deleteModal').style.display='none'">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete My Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// File input display
document.getElementById('avatar').addEventListener('change', function() {
    var fileName = this.files[0] ? this.files[0].name : 'No file chosen';
    document.querySelector('.file-name').textContent = fileName;
});
</script>
