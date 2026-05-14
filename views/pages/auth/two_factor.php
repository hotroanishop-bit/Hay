<?php
/**
 * Two-Factor Authentication Page
 * Variables: $pageTitle, $currentPage
 */
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1>Two-Factor Authentication</h1>
            <p>Enter the verification code from your authenticator app</p>
        </div>

        <form action="/2fa" method="POST" class="auth-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

            <div class="form-group">
                <label for="code">Verification Code</label>
                <input type="text" id="code" name="code" class="form-control form-control-lg text-center" placeholder="000000" maxlength="6" pattern="[0-9]{6}" required autofocus autocomplete="one-time-code">
                <small class="form-text">Enter the 6-digit code from your authenticator app</small>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Verify</button>
        </form>

        <div class="auth-footer">
            <p>Having trouble? <a href="/2fa/recovery">Use a recovery code</a></p>
            <p><a href="/logout">Cancel and sign out</a></p>
        </div>
    </div>
</div>
