<?php
/**
 * Profile Settings Page - Modern Design
 * Variables: $pageTitle, $currentPage, $user
 */
?>

<div class="page-header">
    <div class="page-header-content">
        <h1 class="page-title">Profile Settings</h1>
        <p class="page-subtitle">Manage your account information and preferences</p>
    </div>
</div>

<div class="profile-layout">
    <div class="profile-main">
        <!-- Avatar Section -->
        <div class="card mb-6">
            <div class="card-header">
                <h3 class="card-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="10" r="3"></circle><path d="M7 20.662V19a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v1.662"></path></svg>
                    Profile Picture
                </h3>
            </div>
            <div class="card-body">
                <div class="avatar-section">
                    <div class="avatar-preview-wrapper">
                        <?php if (!empty($user['avatar_url'])): ?>
                        <img src="<?= htmlspecialchars($user['avatar_url']) ?>" alt="Profile Avatar" class="avatar-image">
                        <?php else: ?>
                        <div class="avatar-placeholder">
                            <span><?= htmlspecialchars(strtoupper(substr($user['name'] ?? 'U', 0, 1))) ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="avatar-overlay">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" x2="12" y1="3" y2="15"></line></svg>
                            <span>Change</span>
                        </div>
                    </div>
                    <form action="/profile/avatar" method="POST" enctype="multipart/form-data" class="avatar-form">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <div class="avatar-upload-area">
                            <input type="file" id="avatar" name="avatar" accept="image/png,image/jpeg,image/jpg,image/gif" class="file-input">
                            <label for="avatar" class="upload-trigger">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" x2="12" y1="3" y2="15"></line></svg>
                                <span>Choose Image</span>
                            </label>
                            <span class="file-name" id="fileName">No file chosen</span>
                        </div>
                        <p class="form-help">Allowed: PNG, JPG, JPEG, GIF. Max size: 2MB</p>
                        <button type="submit" class="btn btn-primary btn-sm mt-3">Upload Avatar</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Personal Information Section -->
        <div class="card mb-6">
            <div class="card-header">
                <h3 class="card-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    Personal Information
                </h3>
            </div>
            <div class="card-body">
                <form action="/profile/update" method="POST" class="profile-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                    <div class="form-row">
                        <div class="form-group">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" id="name" name="name" class="form-input" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="email" class="form-label">Email Address</label>
                            <div class="input-with-status">
                                <input type="email" id="email" name="email" class="form-input" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                                <?php if (empty($user['email_verified_at'])): ?>
                                <span class="badge badge-warning">Not Verified</span>
                                <?php else: ?>
                                <span class="badge badge-success">Verified</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Preferences Section -->
        <div class="card mb-6">
            <div class="card-header">
                <h3 class="card-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    Preferences
                </h3>
            </div>
            <div class="card-body">
                <!-- Theme Preference -->
                <div class="preference-item">
                    <div class="preference-info">
                        <h4>Theme</h4>
                        <p>Choose your preferred color scheme</p>
                    </div>
                    <div class="theme-selector">
                        <button type="button" class="theme-btn <?= ($_COOKIE['theme'] ?? 'light') === 'light' ? 'active' : '' ?>" onclick="setTheme('light')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"></circle><path d="M12 2v2"></path><path d="M12 20v2"></path><path d="m4.93 4.93 1.41 1.41"></path><path d="m17.66 17.66 1.41 1.41"></path><path d="M2 12h2"></path><path d="M20 12h2"></path><path d="m6.34 17.66-1.41 1.41"></path><path d="m19.07 4.93-1.41 1.41"></path></svg>
                            Light
                        </button>
                        <button type="button" class="theme-btn <?= ($_COOKIE['theme'] ?? '') === 'dark' ? 'active' : '' ?>" onclick="setTheme('dark')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"></path></svg>
                            Dark
                        </button>
                    </div>
                </div>

                <!-- Billing Preference -->
                <div class="preference-item">
                    <div class="preference-info">
                        <h4>Default Billing Type</h4>
                        <p>Choose how you prefer to be billed</p>
                    </div>
                    <form action="/profile/billing-preference" method="POST" class="billing-selector">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <div class="toggle-group">
                            <input type="radio" name="billing_type" id="billing_payg" value="payg" <?= ($user['preferred_billing_type'] ?? 'payg') === 'payg' ? 'checked' : '' ?>>
                            <label for="billing_payg" class="toggle-option">PAYG</label>
                            <input type="radio" name="billing_type" id="billing_plan" value="plan" <?= ($user['preferred_billing_type'] ?? '') === 'plan' ? 'checked' : '' ?>>
                            <label for="billing_plan" class="toggle-option">Plan</label>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Account Security Section -->
        <div class="card mb-6">
            <div class="card-header">
                <h3 class="card-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                    Account Security
                </h3>
            </div>
            <div class="card-body p-0">
                <a href="/profile/password" class="security-link">
                    <div class="security-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                    </div>
                    <div class="security-info">
                        <h4>Change Password</h4>
                        <p>Update your account password</p>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"></path></svg>
                </a>

                <a href="/profile/2fa" class="security-link">
                    <div class="security-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                    </div>
                    <div class="security-info">
                        <h4>Two-Factor Authentication</h4>
                        <?php if (!empty($user['two_factor_enabled'])): ?>
                        <p class="text-success">Enabled - Your account is protected</p>
                        <?php else: ?>
                        <p class="text-warning">Not configured - Enable for extra security</p>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($user['two_factor_enabled'])): ?>
                    <span class="badge badge-success">Enabled</span>
                    <?php else: ?>
                    <span class="badge badge-warning">Disabled</span>
                    <?php endif; ?>
                </a>
            </div>
        </div>

        <!-- Danger Zone -->
        <div class="card card-danger">
            <div class="card-header">
                <h3 class="card-title text-error">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path><path d="M12 9v4"></path><path d="M12 17h.01"></path></svg>
                    Danger Zone
                </h3>
            </div>
            <div class="card-body">
                <div class="danger-item">
                    <div class="danger-info">
                        <h4>Delete Account</h4>
                        <p>Permanently delete your account and all associated data. This action cannot be undone.</p>
                    </div>
                    <button type="button" class="btn btn-danger" onclick="document.getElementById('deleteModal').style.display='flex'">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"></path><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path></svg>
                        Delete Account
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Account Modal -->
<div id="deleteModal" class="modal-overlay" style="display:none;">
    <div class="modal">
        <div class="modal-header">
            <h2 class="modal-title">Delete Account</h2>
            <button type="button" class="modal-close" onclick="document.getElementById('deleteModal').style.display='none'">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="alert alert-error mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path><path d="M12 9v4"></path><path d="M12 17h.01"></path></svg>
                <span>This action is permanent and cannot be undone.</span>
            </div>
            <form action="/profile/delete" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <div class="form-group">
                    <label for="delete_password" class="form-label">Enter your password to confirm</label>
                    <input type="password" id="delete_password" name="password" class="form-input" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('deleteModal').style.display='none'">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete My Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Profile Layout */
.profile-layout {
    max-width: 800px;
}

/* Avatar Section */
.avatar-section {
    display: flex;
    align-items: flex-start;
    gap: var(--space-6);
}

.avatar-preview-wrapper {
    position: relative;
    width: 100px;
    height: 100px;
    border-radius: var(--radius-full);
    overflow: hidden;
    flex-shrink: 0;
}

.avatar-image,
.avatar-placeholder {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
    color: var(--color-white);
    font-size: var(--font-size-3xl);
    font-weight: var(--font-weight-bold);
}

.avatar-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: var(--color-white);
    font-size: var(--font-size-xs);
    opacity: 0;
    transition: opacity var(--transition-fast);
    cursor: pointer;
}

.avatar-preview-wrapper:hover .avatar-overlay {
    opacity: 1;
}

.avatar-form {
    flex: 1;
}

.avatar-upload-area {
    display: flex;
    align-items: center;
    gap: var(--space-3);
}

.file-input {
    display: none;
}

.upload-trigger {
    display: inline-flex;
    align-items: center;
    gap: var(--space-2);
    padding: var(--space-2) var(--space-4);
    background: var(--bg-tertiary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
    cursor: pointer;
    transition: all var(--transition-fast);
}

.upload-trigger:hover {
    background: var(--color-primary-light);
    border-color: var(--color-primary);
    color: var(--color-primary);
}

.file-name {
    font-size: var(--font-size-sm);
    color: var(--text-muted);
}

/* Form Row */
.form-row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: var(--space-4);
}

/* Input with Status */
.input-with-status {
    display: flex;
    align-items: center;
    gap: var(--space-2);
}

.input-with-status .form-input {
    flex: 1;
}

/* Form Actions */
.form-actions {
    display: flex;
    justify-content: flex-end;
    margin-top: var(--space-6);
    padding-top: var(--space-6);
    border-top: 1px solid var(--border-light);
}

/* Preference Item */
.preference-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: var(--space-4) 0;
    border-bottom: 1px solid var(--border-light);
}

.preference-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.preference-info h4 {
    font-size: var(--font-size-base);
    font-weight: var(--font-weight-medium);
    color: var(--text-primary);
    margin: 0 0 var(--space-1) 0;
}

.preference-info p {
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
    margin: 0;
}

/* Theme Selector */
.theme-selector {
    display: flex;
    gap: var(--space-2);
}

.theme-btn {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    padding: var(--space-2) var(--space-4);
    background: var(--surface-primary);
    border: 2px solid var(--border-color);
    border-radius: var(--radius-md);
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
    cursor: pointer;
    transition: all var(--transition-fast);
}

.theme-btn:hover {
    border-color: var(--color-primary-light);
}

.theme-btn.active {
    border-color: var(--color-primary);
    background: var(--color-primary-light);
    color: var(--color-primary);
}

/* Toggle Group */
.toggle-group {
    display: flex;
    background: var(--bg-tertiary);
    border-radius: var(--radius-md);
    padding: var(--space-1);
}

.toggle-group input {
    display: none;
}

.toggle-option {
    padding: var(--space-2) var(--space-4);
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
    border-radius: var(--radius-sm);
    cursor: pointer;
    transition: all var(--transition-fast);
}

.toggle-group input:checked + .toggle-option {
    background: var(--surface-primary);
    color: var(--text-primary);
    box-shadow: var(--shadow-sm);
}

/* Security Link */
.security-link {
    display: flex;
    align-items: center;
    gap: var(--space-4);
    padding: var(--space-4) var(--space-5);
    border-bottom: 1px solid var(--border-light);
    transition: background-color var(--transition-fast);
}

.security-link:last-child {
    border-bottom: none;
}

.security-link:hover {
    background: var(--bg-secondary);
}

.security-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: var(--bg-tertiary);
    border-radius: var(--radius-md);
    color: var(--text-secondary);
}

.security-info {
    flex: 1;
}

.security-info h4 {
    font-size: var(--font-size-base);
    font-weight: var(--font-weight-medium);
    color: var(--text-primary);
    margin: 0 0 var(--space-1) 0;
}

.security-info p {
    font-size: var(--font-size-sm);
    margin: 0;
}

/* Card Danger */
.card-danger {
    border-color: var(--color-error-light);
}

.card-danger .card-header {
    background: var(--color-error-light);
}

/* Danger Item */
.danger-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: var(--space-4);
}

.danger-info h4 {
    font-size: var(--font-size-base);
    font-weight: var(--font-weight-medium);
    color: var(--text-primary);
    margin: 0 0 var(--space-1) 0;
}

.danger-info p {
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
    margin: 0;
}

/* Modal Footer */
.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: var(--space-3);
    margin-top: var(--space-6);
}

/* Card Title with Icon */
.card-title {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    font-size: var(--font-size-base);
    font-weight: var(--font-weight-semibold);
    margin: 0;
}

/* Responsive */
@media (max-width: 767px) {
    .avatar-section {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .preference-item {
        flex-direction: column;
        align-items: flex-start;
        gap: var(--space-3);
    }
    
    .danger-item {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .danger-item .btn {
        width: 100%;
    }
}
</style>

<script>
// File input display
document.getElementById('avatar').addEventListener('change', function() {
    var fileName = this.files[0] ? this.files[0].name : 'No file chosen';
    document.getElementById('fileName').textContent = fileName;
});

// Theme switching
function setTheme(theme) {
    document.cookie = `theme=${theme};path=/;max-age=31536000`;
    document.documentElement.setAttribute('data-theme', theme);
    
    // Update active states
    document.querySelectorAll('.theme-btn').forEach(btn => btn.classList.remove('active'));
    event.target.closest('.theme-btn').classList.add('active');
}

// Billing preference auto-submit
document.querySelectorAll('input[name="billing_type"]').forEach(input => {
    input.addEventListener('change', function() {
        this.closest('form').submit();
    });
});
</script>
