<?php
/**
 * Reset Password Page
 * Variables: $pageTitle, $currentPage, $token, $email
 */
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1>Reset Password</h1>
            <p>Enter your new password below</p>
        </div>

        <form action="/reset-password" method="POST" class="auth-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">
            <input type="hidden" name="email" value="<?= htmlspecialchars($email ?? '') ?>">

            <div class="form-group">
                <label for="email_display">Email Address</label>
                <input type="email" id="email_display" class="form-control" value="<?= htmlspecialchars($email ?? '') ?>" readonly disabled>
            </div>

            <div class="form-group">
                <label for="password">New Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="At least 8 characters" required minlength="8" autocomplete="new-password">
            </div>

            <div class="form-group">
                <label for="password_confirm">Confirm New Password</label>
                <input type="password" id="password_confirm" name="password_confirm" class="form-control" placeholder="Confirm your new password" required autocomplete="new-password">
            </div>

            <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
        </form>

        <div class="auth-footer">
            <p>Remember your password? <a href="/login">Sign in</a></p>
        </div>
    </div>
</div>
