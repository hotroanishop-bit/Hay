<?php
/**
 * Deposit Page - Make a Deposit
 * Variables: $pageTitle, $currentPage, $bankName, $bankAccountNumber, $accountHolderName, $minDeposit, $maxDeposit, $bankList
 */
?>

<div class="page-header">
    <div class="page-header-content">
        <h1>Make a Deposit</h1>
        <p>Transfer funds using VietQR for instant payment</p>
    </div>
    <div class="page-header-actions">
        <a href="/billing/pending" class="btn btn-secondary">
            <i class="icon-list"></i> Pending Deposits
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3>Deposit Amount</h3>
            </div>
            <div class="card-body">
                <form action="/billing/deposit" method="POST" id="depositForm">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                    <div class="form-group">
                        <label for="amount">Amount (VND)</label>
                        <div class="input-group">
                            <input type="number" 
                                   id="amount" 
                                   name="amount" 
                                   class="form-control form-control-lg" 
                                   min="<?= htmlspecialchars($minDeposit ?? 10000) ?>" 
                                   max="<?= htmlspecialchars($maxDeposit ?? 50000000) ?>" 
                                   step="1000"
                                   placeholder="Enter amount"
                                   required>
                            <span class="input-group-text">VND</span>
                        </div>
                        <small class="form-text text-muted">
                            Min: <?= number_format($minDeposit ?? 10000) ?> VND - Max: <?= number_format($maxDeposit ?? 50000000) ?> VND
                        </small>
                    </div>

                    <div class="form-group">
                        <label>Quick Select</label>
                        <div class="btn-group-amounts">
                            <button type="button" class="btn btn-outline-secondary" onclick="setAmount(100000)">100,000 VND</button>
                            <button type="button" class="btn btn-outline-secondary" onclick="setAmount(200000)">200,000 VND</button>
                            <button type="button" class="btn btn-outline-secondary" onclick="setAmount(500000)">500,000 VND</button>
                            <button type="button" class="btn btn-outline-secondary" onclick="setAmount(1000000)">1,000,000 VND</button>
                        </div>
                    </div>

                    <div class="form-group mt-4">
                        <button type="submit" class="btn btn-primary btn-lg btn-block">
                            <i class="icon-qr-code"></i> Generate Payment QR
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h3>How It Works</h3>
            </div>
            <div class="card-body">
                <div class="process-steps">
                    <div class="step">
                        <span class="step-number">1</span>
                        <div class="step-content">
                            <strong>Enter Amount</strong>
                            <p>Enter the amount you want to deposit (in VND)</p>
                        </div>
                    </div>
                    <div class="step">
                        <span class="step-number">2</span>
                        <div class="step-content">
                            <strong>Generate QR Code</strong>
                            <p>Click the button to generate a VietQR payment code</p>
                        </div>
                    </div>
                    <div class="step">
                        <span class="step-number">3</span>
                        <div class="step-content">
                            <strong>Scan & Pay</strong>
                            <p>Use your banking app to scan the QR code and complete the transfer</p>
                        </div>
                    </div>
                    <div class="step">
                        <span class="step-number">4</span>
                        <div class="step-content">
                            <strong>Wait for Confirmation</strong>
                            <p>Your deposit will be credited after admin verification</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3>Bank Information</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($bankName) && !empty($bankAccountNumber)): ?>
                <div class="bank-info">
                    <div class="bank-info-item">
                        <label>Bank Name</label>
                        <span><?= htmlspecialchars($bankName ?? '') ?></span>
                    </div>
                    <div class="bank-info-item">
                        <label>Account Number</label>
                        <span class="account-number"><?= htmlspecialchars($bankAccountNumber ?? '') ?></span>
                    </div>
                    <div class="bank-info-item">
                        <label>Account Holder</label>
                        <span><?= htmlspecialchars($accountHolderName ?? '') ?></span>
                    </div>
                </div>
                <?php else: ?>
                <div class="alert alert-warning">
                    <i class="icon-warning"></i>
                    Bank information has not been configured. Please contact administrator.
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h3>Important Notes</h3>
            </div>
            <div class="card-body">
                <ul class="notes-list">
                    <li>Always include the reference code in your transfer note</li>
                    <li>Transfer the exact amount shown</li>
                    <li>Deposits are processed within 24 hours</li>
                    <li>Contact support if your deposit is not credited after 24 hours</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
function setAmount(amount) {
    document.getElementById('amount').value = amount;
}

// Format input with thousand separators on blur
document.getElementById('amount').addEventListener('blur', function(e) {
    // Validate min/max
    var min = <?= $minDeposit ?? 10000 ?>;
    var max = <?= $maxDeposit ?? 50000000 ?>;
    var value = parseInt(this.value) || 0;
    
    if (value < min) {
        this.value = min;
    } else if (value > max) {
        this.value = max;
    }
});
</script>

<style>
.btn-group-amounts {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.btn-group-amounts .btn {
    flex: 1;
    min-width: 120px;
}

.process-steps {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.step {
    display: flex;
    align-items: flex-start;
    gap: 15px;
}

.step-number {
    width: 32px;
    height: 32px;
    background: var(--primary-color, #007bff);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    flex-shrink: 0;
}

.step-content p {
    margin: 5px 0 0;
    color: #6c757d;
}

.bank-info {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.bank-info-item {
    display: flex;
    flex-direction: column;
}

.bank-info-item label {
    font-size: 0.85rem;
    color: #6c757d;
    margin-bottom: 3px;
}

.bank-info-item span {
    font-weight: 600;
}

.account-number {
    font-family: monospace;
    font-size: 1.1rem;
}

.notes-list {
    padding-left: 20px;
    margin: 0;
}

.notes-list li {
    margin-bottom: 10px;
    color: #6c757d;
}

.notes-list li:last-child {
    margin-bottom: 0;
}
</style>
