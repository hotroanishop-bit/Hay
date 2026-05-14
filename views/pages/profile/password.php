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
            <form action="/profile/password" method="POST" class="profile-form" id="passwordForm">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" class="form-control" required autocomplete="current-password">
                </div>

                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" class="form-control" required minlength="8" autocomplete="new-password" data-strength>
                    <div class="password-strength-meter visible" id="passwordStrength">
                        <div class="strength-bars">
                            <span class="strength-bar"></span>
                            <span class="strength-bar"></span>
                            <span class="strength-bar"></span>
                            <span class="strength-bar"></span>
                        </div>
                        <span class="strength-text">Password strength</span>
                    </div>
                    <div class="password-requirements" id="passwordRequirements">
                        <div class="requirement" data-req="length">
                            <span class="req-icon"></span>
                            <span>At least 8 characters</span>
                        </div>
                        <div class="requirement" data-req="lowercase">
                            <span class="req-icon"></span>
                            <span>Lowercase letter</span>
                        </div>
                        <div class="requirement" data-req="uppercase">
                            <span class="req-icon"></span>
                            <span>Uppercase letter</span>
                        </div>
                        <div class="requirement" data-req="number">
                            <span class="req-icon"></span>
                            <span>Number</span>
                        </div>
                        <div class="requirement" data-req="special">
                            <span class="req-icon"></span>
                            <span>Special character</span>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required autocomplete="new-password">
                    <p class="form-error" id="passwordMatchError" style="display:none">Passwords do not match</p>
                </div>

                <div class="form-actions">
                    <a href="/profile" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary" id="submitBtn">Change Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Password Strength Styles */
.password-strength-meter {
    margin-top: var(--space-2);
}

.strength-bars {
    display: flex;
    gap: var(--space-1);
    margin-bottom: var(--space-1);
}

.strength-bar {
    flex: 1;
    height: 4px;
    background: var(--bg-tertiary);
    border-radius: var(--radius-full);
    transition: background-color var(--transition-fast);
}

.strength-bar.weak { background-color: var(--color-error); }
.strength-bar.fair { background-color: var(--color-warning); }
.strength-bar.good { background-color: var(--color-info); }
.strength-bar.strong { background-color: var(--color-success); }

.strength-text {
    font-size: var(--font-size-xs);
    color: var(--text-muted);
}

.strength-text.strength-weak { color: var(--color-error); }
.strength-text.strength-fair { color: var(--color-warning); }
.strength-text.strength-good { color: var(--color-info); }
.strength-text.strength-strong { color: var(--color-success); }

.password-requirements {
    margin-top: var(--space-3);
    display: flex;
    flex-direction: column;
    gap: var(--space-1);
}

.requirement {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    font-size: var(--font-size-sm);
    color: var(--text-muted);
}

.requirement .req-icon::before {
    content: '\2715';
    color: var(--color-error);
}

.requirement.met {
    color: var(--color-success);
}

.requirement.met .req-icon::before {
    content: '\2713';
    color: var(--color-success);
}

.form-error {
    font-size: var(--font-size-sm);
    color: var(--color-error);
    margin-top: var(--space-2);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('new_password');
    const confirmInput = document.getElementById('confirm_password');
    const bars = document.querySelectorAll('.strength-bar');
    const strengthText = document.querySelector('.strength-text');
    const requirements = document.querySelectorAll('.requirement');
    const matchError = document.getElementById('passwordMatchError');
    const form = document.getElementById('passwordForm');

    // Password strength checker
    passwordInput.addEventListener('input', function(e) {
        const password = e.target.value;
        const checks = analyzePassword(password);
        const strength = calculateStrength(checks);
        
        updateMeter(strength);
        updateRequirements(checks);
    });

    // Password confirmation check
    confirmInput.addEventListener('input', function(e) {
        const password = passwordInput.value;
        const confirm = e.target.value;
        
        if (confirm && password !== confirm) {
            matchError.style.display = 'block';
            e.target.classList.add('error');
        } else {
            matchError.style.display = 'none';
            e.target.classList.remove('error');
        }
    });

    // Form validation
    form.addEventListener('submit', function(e) {
        if (passwordInput.value !== confirmInput.value) {
            e.preventDefault();
            matchError.style.display = 'block';
            confirmInput.classList.add('error');
            return;
        }
    });

    function analyzePassword(password) {
        return {
            length: password.length >= 8,
            lowercase: /[a-z]/.test(password),
            uppercase: /[A-Z]/.test(password),
            number: /\d/.test(password),
            special: /[^a-zA-Z0-9]/.test(password)
        };
    }

    function calculateStrength(checks) {
        let score = 0;
        if (checks.length) score++;
        if (checks.lowercase && checks.uppercase) score++;
        if (checks.number) score++;
        if (checks.special) score++;

        const levels = ['', 'weak', 'fair', 'good', 'strong'];
        const labels = ['Password strength', 'Weak', 'Fair', 'Good', 'Strong'];

        return {
            score: score,
            level: levels[score],
            label: labels[score]
        };
    }

    function updateMeter(strength) {
        bars.forEach((bar, index) => {
            bar.className = 'strength-bar';
            if (index < strength.score) {
                bar.classList.add(strength.level);
            }
        });

        strengthText.textContent = strength.label;
        strengthText.className = 'strength-text';
        if (strength.level) {
            strengthText.classList.add('strength-' + strength.level);
        }
    }

    function updateRequirements(checks) {
        requirements.forEach(req => {
            const type = req.dataset.req;
            if (checks[type]) {
                req.classList.add('met');
            } else {
                req.classList.remove('met');
            }
        });
    }
});
</script>
