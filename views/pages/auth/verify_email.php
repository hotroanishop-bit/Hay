<?php
/**
 * Email Verification Page
 * Variables: $pageTitle, $currentPage, $user
 */
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <div class="email-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
            </div>
            <h1>Verify Your Email</h1>
            <p>We've sent a verification link to your email address</p>
        </div>

        <div class="verification-info">
            <?php if (!empty($user['email'])): ?>
                <p class="email-sent-to">
                    A verification email has been sent to:<br>
                    <strong><?= htmlspecialchars($user['email']) ?></strong>
                </p>
            <?php else: ?>
                <p>Please check your inbox and click the verification link to activate your account.</p>
            <?php endif; ?>

            <div class="verification-steps">
                <div class="step">
                    <span class="step-icon">1</span>
                    <span class="step-text">Check your email inbox</span>
                </div>
                <div class="step">
                    <span class="step-icon">2</span>
                    <span class="step-text">Click the verification link</span>
                </div>
                <div class="step">
                    <span class="step-icon">3</span>
                    <span class="step-text">Start using your account</span>
                </div>
            </div>
        </div>

        <div class="resend-section">
            <p>Didn't receive the email?</p>
            
            <?php if (!empty($user)): ?>
                <form action="/resend-verification" method="POST" class="resend-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <button type="submit" class="btn btn-secondary btn-block">Resend Verification Email</button>
                </form>
            <?php else: ?>
                <form action="/resend-verification" method="POST" class="resend-form auth-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <div class="form-group">
                        <label for="resend_email">Enter your email address</label>
                        <input type="email" id="resend_email" name="email" class="form-control" placeholder="your@email.com" required>
                    </div>
                    <button type="submit" class="btn btn-secondary btn-block">Resend Verification Email</button>
                </form>
            <?php endif; ?>
        </div>

        <div class="auth-footer">
            <p>Already verified? <a href="/login">Sign in</a></p>
            <p>Need help? <a href="/tickets/create">Contact Support</a></p>
        </div>
    </div>
</div>

<style>
.email-icon {
    display: flex;
    justify-content: center;
    margin-bottom: var(--spacing-4);
    color: var(--color-primary);
}

.verification-info {
    text-align: center;
    margin-bottom: var(--spacing-6);
}

.email-sent-to {
    background-color: var(--color-gray-50);
    padding: var(--spacing-4);
    border-radius: var(--radius-md);
    margin-bottom: var(--spacing-6);
}

.verification-steps {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-3);
    text-align: left;
}

.step {
    display: flex;
    align-items: center;
    gap: var(--spacing-3);
}

.step-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    background-color: var(--color-primary);
    color: white;
    border-radius: 50%;
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-medium);
    flex-shrink: 0;
}

.step-text {
    color: var(--color-gray-700);
}

.resend-section {
    text-align: center;
    padding-top: var(--spacing-4);
    border-top: 1px solid var(--color-gray-200);
}

.resend-section > p {
    color: var(--color-gray-500);
    margin-bottom: var(--spacing-3);
}

.resend-form {
    margin-top: var(--spacing-3);
}
</style>
