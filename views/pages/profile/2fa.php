<?php
/**
 * Two-Factor Authentication Settings Page
 * Variables: $pageTitle, $currentPage, $user, $secret, $qrCodeUrl
 */
?>

<div class="profile-container">
    <div class="profile-header">
        <a href="/profile" class="back-link">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
            Back to Profile
        </a>
        <h1>Two-Factor Authentication</h1>
        <p>Add an extra layer of security to your account</p>
    </div>

    <div class="profile-content">
        <div class="profile-section">
            <div class="twofa-status">
                <div class="status-indicator <?= !empty($user['two_factor_enabled']) ? 'status-enabled' : 'status-disabled' ?>">
                    <div class="status-icon">
                        <?php if (!empty($user['two_factor_enabled'])): ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><polyline points="9 12 12 15 16 10"></polyline></svg>
                        <?php else: ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                        <?php endif; ?>
                    </div>
                    <div class="status-text">
                        <h3>Two-Factor Authentication is <?= !empty($user['two_factor_enabled']) ? 'Enabled' : 'Disabled' ?></h3>
                        <p>
                            <?php if (!empty($user['two_factor_enabled'])): ?>
                                Your account is protected with two-factor authentication.
                            <?php else: ?>
                                Protect your account by enabling two-factor authentication.
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <?php if (empty($user['two_factor_enabled'])): ?>
        <!-- Enable 2FA Section -->
        <div class="profile-section">
            <h2>Set Up Two-Factor Authentication</h2>
            
            <div class="twofa-setup">
                <div class="setup-step">
                    <span class="step-number">1</span>
                    <div class="step-content">
                        <h4>Install an Authenticator App</h4>
                        <p>Download an authenticator app like Google Authenticator, Authy, or Microsoft Authenticator on your phone.</p>
                    </div>
                </div>

                <div class="setup-step">
                    <span class="step-number">2</span>
                    <div class="step-content">
                        <h4>Scan the QR Code</h4>
                        <p>Open your authenticator app and scan the QR code below, or manually enter the secret key.</p>
                        
                        <?php if (!empty($qrCodeUrl)): ?>
                        <div class="qr-code-section">
                            <div class="qr-code">
                                <!-- QR Code placeholder - in production, use a QR code library -->
                                <div class="qr-placeholder">
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?= urlencode($qrCodeUrl) ?>" alt="2FA QR Code" width="200" height="200">
                                </div>
                            </div>
                            <div class="manual-key">
                                <label>Manual Entry Key:</label>
                                <code class="secret-key"><?= htmlspecialchars($secret ?? '') ?></code>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="setup-step">
                    <span class="step-number">3</span>
                    <div class="step-content">
                        <h4>Enter Verification Code</h4>
                        <p>Enter the 6-digit code from your authenticator app to verify the setup.</p>
                        
                        <form action="/profile/2fa/enable" method="POST" class="verify-form">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                            <div class="form-group">
                                <input type="text" name="code" class="form-control form-control-lg text-center" placeholder="000000" maxlength="6" pattern="[0-9]{6}" required autocomplete="one-time-code">
                            </div>
                            <button type="submit" class="btn btn-primary">Enable Two-Factor Authentication</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <?php else: ?>
        <!-- Disable 2FA Section -->
        <div class="profile-section">
            <h2>Disable Two-Factor Authentication</h2>
            <p class="section-description">If you want to disable two-factor authentication, enter your current verification code below. Note that this will make your account less secure.</p>
            
            <form action="/profile/2fa/disable" method="POST" class="disable-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <div class="form-group">
                    <label for="code">Verification Code</label>
                    <input type="text" id="code" name="code" class="form-control form-control-lg text-center" placeholder="000000" maxlength="6" pattern="[0-9]{6}" required autocomplete="one-time-code">
                    <small class="form-text">Enter the 6-digit code from your authenticator app</small>
                </div>
                <button type="submit" class="btn btn-danger">Disable Two-Factor Authentication</button>
            </form>
        </div>
        <?php endif; ?>

        <!-- Benefits Section -->
        <div class="profile-section">
            <h2>Why Use Two-Factor Authentication?</h2>
            <div class="benefits-list">
                <div class="benefit-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    <span>Protects your account even if your password is compromised</span>
                </div>
                <div class="benefit-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    <span>Prevents unauthorized access from unknown devices</span>
                </div>
                <div class="benefit-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    <span>Industry-standard security used by banks and major tech companies</span>
                </div>
            </div>
        </div>
    </div>
</div>
