<?php
/**
 * CAPTCHA Partial Component
 * Simple math-based CAPTCHA for security
 * 
 * Variables:
 * - $captcha: array with 'question' and 'id' keys
 * - $showCaptcha: bool (optional) - whether to show the captcha
 */

// Only render if captcha data is provided
if (isset($captcha) && is_array($captcha)): 
?>
<div class="captcha-container" id="captcha-container">
    <div class="captcha-box">
        <div class="captcha-header">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
            <span>Security Check</span>
        </div>
        <div class="captcha-question">
            <label for="captcha_answer" class="form-label"><?= htmlspecialchars($captcha['question'] ?? 'Complete the math problem') ?></label>
        </div>
        <div class="captcha-input-wrapper">
            <input type="hidden" name="captcha_id" value="<?= htmlspecialchars($captcha['id'] ?? '') ?>">
            <input 
                type="text" 
                id="captcha_answer" 
                name="captcha_answer" 
                class="form-input captcha-input" 
                placeholder="Enter your answer"
                required
                autocomplete="off"
                inputmode="numeric"
                pattern="[0-9-]*"
            >
            <button type="button" class="captcha-refresh" onclick="refreshCaptcha()" title="Get new question">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
            </button>
        </div>
        <p class="captcha-help">Please solve the math problem above to continue.</p>
    </div>
</div>

<style>
.captcha-container {
    margin-bottom: var(--space-5);
}

.captcha-box {
    padding: var(--space-4);
    background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--surface-primary) 100%);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
}

.captcha-header {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    margin-bottom: var(--space-3);
    font-weight: var(--font-weight-semibold);
    color: var(--text-primary);
}

.captcha-header svg {
    color: var(--color-primary);
}

.captcha-question {
    margin-bottom: var(--space-3);
}

.captcha-question .form-label {
    font-size: var(--font-size-lg);
    color: var(--text-primary);
    font-weight: var(--font-weight-medium);
}

.captcha-input-wrapper {
    display: flex;
    gap: var(--space-2);
    margin-bottom: var(--space-2);
}

.captcha-input {
    flex: 1;
    font-size: var(--font-size-lg);
    text-align: center;
    letter-spacing: 0.1em;
}

.captcha-refresh {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    background: var(--bg-tertiary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    color: var(--text-secondary);
    cursor: pointer;
    transition: all var(--transition-fast);
}

.captcha-refresh:hover {
    background: var(--bg-secondary);
    color: var(--color-primary);
    border-color: var(--color-primary);
}

.captcha-refresh:active {
    transform: scale(0.95);
}

.captcha-help {
    font-size: var(--font-size-xs);
    color: var(--text-muted);
    margin: 0;
}

/* Error state */
.captcha-input.error {
    border-color: var(--color-error);
}

.captcha-error {
    color: var(--color-error);
    font-size: var(--font-size-sm);
    margin-top: var(--space-2);
}
</style>

<script>
function refreshCaptcha() {
    const container = document.getElementById('captcha-container');
    const refreshBtn = container.querySelector('.captcha-refresh');
    
    // Add spinning animation
    refreshBtn.style.transform = 'rotate(360deg)';
    refreshBtn.style.transition = 'transform 0.5s ease';
    
    // Request new captcha via AJAX or form reload
    fetch('/api/captcha/refresh', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('input[name="csrf_token"]')?.value || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.captcha) {
            container.querySelector('.captcha-question .form-label').textContent = data.captcha.question;
            container.querySelector('input[name="captcha_id"]').value = data.captcha.id;
            container.querySelector('#captcha_answer').value = '';
        }
    })
    .catch(() => {
        // Fallback: reload page
        window.location.reload();
    })
    .finally(() => {
        setTimeout(() => {
            refreshBtn.style.transform = '';
            refreshBtn.style.transition = '';
        }, 500);
    });
}
</script>
<?php endif; ?>
