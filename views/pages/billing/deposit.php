<?php
/**
 * Deposit Page - Make a Deposit
 * Variables: $pageTitle, $currentPage, $bankName, $bankAccountNumber, $accountHolderName, $minDeposit, $maxDeposit, $bankList
 */
?>

<div class="page-header">
    <div class="page-header-content">
        <a href="/billing" class="back-link">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            Back to Billing
        </a>
        <h1 class="page-title">Make a Deposit</h1>
        <p class="page-subtitle">Transfer funds using VietQR for instant payment</p>
    </div>
    <div class="page-header-actions">
        <a href="/billing/pending" class="btn btn-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"></rect><line x1="16" x2="16" y1="2" y2="6"></line><line x1="8" x2="8" y1="2" y2="6"></line><line x1="3" x2="21" y1="10" y2="10"></line></svg>
            Pending Deposits
        </a>
    </div>
</div>

<div class="deposit-layout">
    <div class="deposit-main">
        <!-- Amount Input Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"></path><path d="M12 18V6"></path></svg>
                    Deposit Amount
                </h3>
            </div>
            <div class="card-body">
                <form action="/billing/deposit" method="POST" id="depositForm">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                    <div class="form-group">
                        <label for="amount" class="form-label">Amount (VND)</label>
                        <div class="amount-input-wrapper">
                            <span class="amount-currency">VND</span>
                            <input type="number" 
                                   id="amount" 
                                   name="amount" 
                                   class="form-input amount-input" 
                                   min="<?= htmlspecialchars($minDeposit ?? 10000) ?>" 
                                   max="<?= htmlspecialchars($maxDeposit ?? 50000000) ?>" 
                                   step="1000"
                                   placeholder="Enter amount"
                                   required>
                        </div>
                        <p class="form-help">
                            Min: <?= number_format($minDeposit ?? 10000) ?> VND - Max: <?= number_format($maxDeposit ?? 50000000) ?> VND
                        </p>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Quick Select</label>
                        <div class="amount-presets">
                            <button type="button" class="preset-btn" onclick="setAmount(50000)">
                                <span class="preset-amount">50K</span>
                                <span class="preset-label">VND</span>
                            </button>
                            <button type="button" class="preset-btn" onclick="setAmount(100000)">
                                <span class="preset-amount">100K</span>
                                <span class="preset-label">VND</span>
                            </button>
                            <button type="button" class="preset-btn" onclick="setAmount(200000)">
                                <span class="preset-amount">200K</span>
                                <span class="preset-label">VND</span>
                            </button>
                            <button type="button" class="preset-btn" onclick="setAmount(500000)">
                                <span class="preset-amount">500K</span>
                                <span class="preset-label">VND</span>
                            </button>
                            <button type="button" class="preset-btn preset-highlight" onclick="setAmount(1000000)">
                                <span class="preset-amount">1M</span>
                                <span class="preset-label">VND</span>
                            </button>
                            <button type="button" class="preset-btn" onclick="setAmount(2000000)">
                                <span class="preset-amount">2M</span>
                                <span class="preset-label">VND</span>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-lg w-full" id="submitBtn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="5" height="5" x="3" y="3" rx="1"></rect><rect width="5" height="5" x="16" y="3" rx="1"></rect><rect width="5" height="5" x="3" y="16" rx="1"></rect><path d="M21 16h-3a2 2 0 0 0-2 2v3"></path><path d="M21 21v.01"></path><path d="M12 7v3a2 2 0 0 1-2 2H7"></path><path d="M3 12h.01"></path><path d="M12 3h.01"></path><path d="M12 16v.01"></path><path d="M16 12h1"></path><path d="M21 12v.01"></path><path d="M12 21v-1"></path></svg>
                            Generate Payment QR
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- How It Works Card -->
        <div class="card mt-6">
            <div class="card-header">
                <h3 class="card-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><path d="M12 17h.01"></path></svg>
                    How It Works
                </h3>
            </div>
            <div class="card-body">
                <div class="process-steps">
                    <div class="step">
                        <div class="step-indicator">
                            <span class="step-number">1</span>
                            <div class="step-line"></div>
                        </div>
                        <div class="step-content">
                            <h4>Enter Amount</h4>
                            <p>Enter the amount you want to deposit (in VND)</p>
                        </div>
                    </div>
                    <div class="step">
                        <div class="step-indicator">
                            <span class="step-number">2</span>
                            <div class="step-line"></div>
                        </div>
                        <div class="step-content">
                            <h4>Generate QR Code</h4>
                            <p>Click the button to generate a VietQR payment code</p>
                        </div>
                    </div>
                    <div class="step">
                        <div class="step-indicator">
                            <span class="step-number">3</span>
                            <div class="step-line"></div>
                        </div>
                        <div class="step-content">
                            <h4>Scan & Pay</h4>
                            <p>Use your banking app to scan the QR code and complete the transfer</p>
                        </div>
                    </div>
                    <div class="step">
                        <div class="step-indicator">
                            <span class="step-number">4</span>
                        </div>
                        <div class="step-content">
                            <h4>Wait for Confirmation</h4>
                            <p>Your deposit will be credited after admin verification</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="deposit-sidebar">
        <!-- Bank Information Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" x2="21" y1="22" y2="22"></line><line x1="6" x2="6" y1="18" y2="11"></line><line x1="10" x2="10" y1="18" y2="11"></line><line x1="14" x2="14" y1="18" y2="11"></line><line x1="18" x2="18" y1="18" y2="11"></line><polygon points="12 2 20 7 4 7"></polygon></svg>
                    Bank Information
                </h3>
            </div>
            <div class="card-body">
                <?php if (!empty($bankName) && !empty($bankAccountNumber)): ?>
                <div class="bank-info-list">
                    <div class="bank-info-item">
                        <span class="info-label">Bank Name</span>
                        <span class="info-value"><?= htmlspecialchars($bankName ?? '') ?></span>
                    </div>
                    <div class="bank-info-item">
                        <span class="info-label">Account Number</span>
                        <div class="info-value-copy">
                            <code class="account-number"><?= htmlspecialchars($bankAccountNumber ?? '') ?></code>
                            <button type="button" class="btn btn-ghost btn-sm" onclick="copyText('<?= htmlspecialchars($bankAccountNumber ?? '') ?>', this)" title="Copy">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"></rect><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"></path></svg>
                            </button>
                        </div>
                    </div>
                    <div class="bank-info-item">
                        <span class="info-label">Account Holder</span>
                        <span class="info-value"><?= htmlspecialchars($accountHolderName ?? '') ?></span>
                    </div>
                </div>
                <?php else: ?>
                <div class="alert alert-warning mb-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path><path d="M12 9v4"></path><path d="M12 17h.01"></path></svg>
                    <span>Bank information has not been configured. Please contact administrator.</span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Important Notes Card -->
        <div class="card mt-4">
            <div class="card-header">
                <h3 class="card-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" x2="12" y1="8" y2="12"></line><line x1="12" x2="12.01" y1="16" y2="16"></line></svg>
                    Important Notes
                </h3>
            </div>
            <div class="card-body">
                <ul class="notes-list">
                    <li>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        <span>Always include the reference code in your transfer note</span>
                    </li>
                    <li>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        <span>Transfer the exact amount shown</span>
                    </li>
                    <li>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        <span>Deposits are processed within 24 hours</span>
                    </li>
                    <li>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        <span>Contact support if your deposit is not credited after 24 hours</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Status Indicator -->
        <div class="status-card mt-4">
            <div class="status-icon pending">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            </div>
            <div class="status-content">
                <span class="status-title">Processing Time</span>
                <span class="status-text">Up to 24 hours</span>
            </div>
        </div>
    </div>
</div>

<style>
/* Back Link */
.back-link {
    display: inline-flex;
    align-items: center;
    gap: var(--space-1);
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
    margin-bottom: var(--space-2);
    transition: color var(--transition-fast);
}

.back-link:hover {
    color: var(--color-primary);
}

/* Deposit Layout */
.deposit-layout {
    display: grid;
    grid-template-columns: 1fr 360px;
    gap: var(--space-6);
    align-items: start;
}

.deposit-sidebar {
    position: sticky;
    top: calc(var(--topbar-height) + var(--space-6));
}

/* Amount Input */
.amount-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.amount-currency {
    position: absolute;
    left: var(--space-4);
    font-weight: var(--font-weight-semibold);
    color: var(--text-muted);
}

.amount-input {
    padding-left: var(--space-14);
    font-size: var(--font-size-xl);
    font-weight: var(--font-weight-semibold);
    height: var(--input-height-lg);
}

/* Amount Presets */
.amount-presets {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: var(--space-3);
}

.preset-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: var(--space-4);
    background: var(--surface-primary);
    border: 2px solid var(--border-color);
    border-radius: var(--radius-lg);
    cursor: pointer;
    transition: all var(--transition-fast);
}

.preset-btn:hover {
    border-color: var(--color-primary);
    background: var(--color-primary-light);
}

.preset-btn.active {
    border-color: var(--color-primary);
    background: var(--color-primary-light);
}

.preset-btn.preset-highlight {
    border-color: var(--color-primary);
    background: linear-gradient(135deg, var(--color-primary-light), transparent);
}

.preset-amount {
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-bold);
    color: var(--text-primary);
}

.preset-label {
    font-size: var(--font-size-xs);
    color: var(--text-muted);
    margin-top: var(--space-1);
}

/* Process Steps */
.process-steps {
    display: flex;
    flex-direction: column;
}

.step {
    display: flex;
    gap: var(--space-4);
}

.step-indicator {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 32px;
}

.step-number {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    background: var(--color-primary);
    color: var(--color-white);
    border-radius: var(--radius-full);
    font-weight: var(--font-weight-bold);
    font-size: var(--font-size-sm);
    flex-shrink: 0;
}

.step-line {
    width: 2px;
    flex: 1;
    background: var(--border-color);
    margin: var(--space-2) 0;
    min-height: 20px;
}

.step-content {
    flex: 1;
    padding-bottom: var(--space-5);
}

.step:last-child .step-content {
    padding-bottom: 0;
}

.step-content h4 {
    font-size: var(--font-size-base);
    font-weight: var(--font-weight-semibold);
    color: var(--text-primary);
    margin: 0 0 var(--space-1) 0;
}

.step-content p {
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
    margin: 0;
}

/* Bank Info List */
.bank-info-list {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
}

.bank-info-item {
    display: flex;
    flex-direction: column;
    gap: var(--space-1);
}

.info-label {
    font-size: var(--font-size-xs);
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.info-value {
    font-size: var(--font-size-base);
    font-weight: var(--font-weight-semibold);
    color: var(--text-primary);
}

.info-value-copy {
    display: flex;
    align-items: center;
    gap: var(--space-2);
}

.account-number {
    font-family: var(--font-mono);
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-bold);
    color: var(--text-primary);
    background: none;
    padding: 0;
}

/* Notes List */
.notes-list {
    display: flex;
    flex-direction: column;
    gap: var(--space-3);
}

.notes-list li {
    display: flex;
    align-items: flex-start;
    gap: var(--space-2);
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
}

.notes-list li svg {
    flex-shrink: 0;
    color: var(--color-warning);
    margin-top: 2px;
}

/* Status Card */
.status-card {
    display: flex;
    align-items: center;
    gap: var(--space-4);
    padding: var(--space-4);
    background: var(--bg-tertiary);
    border-radius: var(--radius-lg);
}

.status-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    border-radius: var(--radius-full);
}

.status-icon.pending {
    background: var(--color-warning-light);
    color: var(--color-warning);
}

.status-content {
    display: flex;
    flex-direction: column;
}

.status-title {
    font-size: var(--font-size-xs);
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.status-text {
    font-size: var(--font-size-base);
    font-weight: var(--font-weight-semibold);
    color: var(--text-primary);
}

/* Card Title */
.card-title {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    font-size: var(--font-size-base);
    font-weight: var(--font-weight-semibold);
    margin: 0;
}

/* Responsive */
@media (max-width: 1023px) {
    .deposit-layout {
        grid-template-columns: 1fr;
    }
    
    .deposit-sidebar {
        position: static;
    }
}

@media (max-width: 639px) {
    .amount-presets {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>

<script>
function setAmount(amount) {
    document.getElementById('amount').value = amount;
    // Remove active class from all buttons
    document.querySelectorAll('.preset-btn').forEach(btn => btn.classList.remove('active'));
    // Add active class to clicked button
    event.target.closest('.preset-btn').classList.add('active');
}

function copyText(text, btn) {
    navigator.clipboard.writeText(text).then(() => {
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>';
        setTimeout(() => { btn.innerHTML = originalHTML; }, 2000);
    });
}

// Format input with thousand separators on blur
document.getElementById('amount').addEventListener('blur', function(e) {
    var min = <?= $minDeposit ?? 10000 ?>;
    var max = <?= $maxDeposit ?? 50000000 ?>;
    var value = parseInt(this.value) || 0;
    
    if (value < min) {
        this.value = min;
    } else if (value > max) {
        this.value = max;
    }
});

// Form submission with loading state
document.getElementById('depositForm').addEventListener('submit', function(e) {
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span> Generating QR...';
});
</script>
