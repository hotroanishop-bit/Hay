<?php
/**
 * Change Password Page
 * Variables: $pageTitle, $currentPage, $user
 */
?>

<div class="profile-container">
    <div class="profile-header">
        <a href="/profile" class="back-link">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
            Back to Profile
        </a>
        <h1>Change Password</h1>
        <p>Update your account password to keep your account secure</p>
    </div>

    <div class="profile-content">
        <div class="profile-section">
            <form action="/profile/password" method="POST" class="profile-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" class="form-control" required autocomplete="current-password">
                </div>

                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" class="form-control" required minlength="8" autocomplete="new-password">
                    <small class="form-text">Password must be at least 8 characters</small>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required autocomplete="new-password">
                </div>

                <div class="form-actions">
                    <a href="/profile" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Change Password</button>
                </div>
            </form>
        </div>
    </div>
</div>
