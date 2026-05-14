<?php
/**
 * Login Page - Modern Centered Card Design
 * Variables: $pageTitle, $currentPage
 */
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-logo">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15.5 7.5 2.3 2.3a1 1 0 0 0 1.4 0l2.1-2.1a1 1 0 0 0 0-1.4L19 4"></path><path d="m21 2-9.6 9.6"></path><circle cx="7.5" cy="15.5" r="5.5"></circle></svg>
        </div>
        
        <div class="auth-header">
            <h1>Welcome Back</h1>
            <p>Sign in to your account to continue</p>
        </div>

        <form action="/login" method="POST" class="auth-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <div class="input-icon-wrapper">
                    <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"></rect><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path></svg>
                    <input type="email" id="email" name="email" class="form-input form-input-icon" placeholder="Enter your email" required autofocus>
                </div>
            </div>

            <div class="form-group">
                <div class="label-row">
                    <label for="password" class="form-label">Password</label>
                    <a href="/forgot-password" class="forgot-link">Forgot password?</a>
                </div>
                <div class="input-icon-wrapper">
                    <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                    <input type="password" id="password" name="password" class="form-input form-input-icon" placeholder="Enter your password" required>
                    <button type="button" class="password-toggle" onclick="togglePassword('password', this)" aria-label="Toggle password visibility">
                        <svg class="eye-open" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        <svg class="eye-closed" style="display:none" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"></path><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"></path><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"></path><line x1="2" x2="22" y1="2" y2="22"></line></svg>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label class="checkbox-wrapper">
                    <input type="checkbox" name="remember" class="checkbox-input">
                    <span class="checkbox-custom"></span>
                    <span class="checkbox-label">Remember me for 30 days</span>
                </label>
            </div>

            <button type="submit" class="btn btn-primary btn-lg w-full" id="submitBtn">
                <span class="btn-text">Sign In</span>
                <span class="btn-loader" style="display:none">
                    <span class="spinner"></span>
                    Signing in...
                </span>
            </button>
        </form>

        <div class="auth-footer">
            <p>Don't have an account? <a href="/register">Create one</a></p>
        </div>
    </div>
</div>

<style>
/* Auth Container */
.auth-container {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: calc(100vh - var(--topbar-height) - var(--space-12));
    padding: var(--space-6);
}

/* Auth Card */
.auth-card {
    width: 100%;
    max-width: 420px;
    background: var(--surface-primary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-lg);
    padding: var(--space-8);
}

/* Auth Logo */
.auth-logo {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 64px;
    height: 64px;
    margin: 0 auto var(--space-6);
    background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
    border-radius: var(--radius-lg);
    color: var(--color-white);
}

/* Auth Header */
.auth-header {
    text-align: center;
    margin-bottom: var(--space-8);
}

.auth-header h1 {
    font-size: var(--font-size-2xl);
    font-weight: var(--font-weight-bold);
    color: var(--text-primary);
    margin: 0 0 var(--space-2) 0;
}

.auth-header p {
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
    margin: 0;
}

/* Auth Form */
.auth-form .form-group {
    margin-bottom: var(--space-5);
}

/* Label Row */
.label-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--space-2);
}

.forgot-link {
    font-size: var(--font-size-sm);
    color: var(--color-primary);
}

.forgot-link:hover {
    text-decoration: underline;
}

/* Input with Icon */
.input-icon-wrapper {
    position: relative;
}

.input-icon {
    position: absolute;
    left: var(--space-3);
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
    pointer-events: none;
}

.form-input-icon {
    padding-left: var(--space-10);
}

/* Password Toggle */
.password-toggle {
    position: absolute;
    right: var(--space-3);
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: var(--text-muted);
    cursor: pointer;
    padding: var(--space-1);
    transition: color var(--transition-fast);
}

.password-toggle:hover {
    color: var(--text-primary);
}

/* Custom Checkbox */
.checkbox-wrapper {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    cursor: pointer;
}

.checkbox-input {
    display: none;
}

.checkbox-custom {
    width: 18px;
    height: 18px;
    border: 2px solid var(--border-color);
    border-radius: var(--radius-sm);
    transition: all var(--transition-fast);
    position: relative;
}

.checkbox-input:checked + .checkbox-custom {
    background: var(--color-primary);
    border-color: var(--color-primary);
}

.checkbox-input:checked + .checkbox-custom::after {
    content: '';
    position: absolute;
    left: 5px;
    top: 2px;
    width: 5px;
    height: 9px;
    border: solid white;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
}

.checkbox-label {
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
}

/* Button States */
.btn-text,
.btn-loader {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--space-2);
}

/* Auth Footer */
.auth-footer {
    margin-top: var(--space-6);
    padding-top: var(--space-6);
    border-top: 1px solid var(--border-light);
    text-align: center;
}

.auth-footer p {
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
    margin: 0;
}

.auth-footer a {
    color: var(--color-primary);
    font-weight: var(--font-weight-medium);
}

.auth-footer a:hover {
    text-decoration: underline;
}

/* Responsive */
@media (max-width: 480px) {
    .auth-container {
        padding: var(--space-4);
    }

    .auth-card {
        padding: var(--space-6);
    }
}
</style>

<script>
function togglePassword(inputId, btn) {
    const input = document.getElementById(inputId);
    const eyeOpen = btn.querySelector('.eye-open');
    const eyeClosed = btn.querySelector('.eye-closed');
    
    if (input.type === 'password') {
        input.type = 'text';
        eyeOpen.style.display = 'none';
        eyeClosed.style.display = 'block';
    } else {
        input.type = 'password';
        eyeOpen.style.display = 'block';
        eyeClosed.style.display = 'none';
    }
}

// Form submission with loading state
document.querySelector('.auth-form').addEventListener('submit', function(e) {
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.querySelector('.btn-text').style.display = 'none';
    btn.querySelector('.btn-loader').style.display = 'flex';
});
</script>
