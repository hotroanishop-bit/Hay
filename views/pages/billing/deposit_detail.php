<?php
/**
 * Deposit Detail Page
 * Variables: $pageTitle, $currentPage, $deposit, $bankName, $bankAccountNumber, $accountHolderName
 */
?>

<div class="page-header">
    <div class="page-header-content">
        <h1>Deposit Details</h1>
        <p>Reference: <strong><?= htmlspecialchars($deposit['reference_code'] ?? '') ?></strong></p>
    </div>
    <div class="page-header-actions">
        <a href="/billing/pending" class="btn btn-secondary">
            <i class="icon-arrow-left"></i> Back to Pending
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3>QR Code</h3>
            </div>
            <div class="card-body text-center">
                <?php if (!empty($deposit['qr_data'])): ?>
                <div class="qr-code-container">
                    <img src="<?= htmlspecialchars($deposit['qr_data']) ?>" alt="VietQR Payment Code" class="qr-code-image">
                </div>
                <p class="text-muted mt-3">Scan this QR code with your banking app</p>
                <?php else: ?>
                <div class="alert alert-warning">
                    QR code not available
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h3>Payment Instructions</h3>
            </div>
            <div class="card-body">
                <ol class="instructions-list">
                    <li>Open your banking app</li>
                    <li>Scan QR or transfer to the account below</li>
                    <li>Enter <strong>EXACTLY</strong> the reference code in the transfer note</li>
                    <li>Wait for admin to confirm your payment</li>
                </ol>
                <div class="alert alert-info mt-3">
                    <i class="icon-info"></i>
                    <strong>Important:</strong> The reference code must be entered exactly as shown for automatic matching.
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3>Deposit Information</h3>
            </div>
            <div class="card-body">
                <div class="deposit-info">
                    <div class="info-row">
                        <label>Status</label>
                        <span class="badge badge-<?= getStatusBadgeClass($deposit['status'] ?? 'pending') ?> badge-lg">
                            <?= htmlspecialchars(ucfirst($deposit['status'] ?? 'pending')) ?>
                        </span>
                    </div>
                    <div class="info-row amount-row">
                        <label>Amount</label>
                        <span class="amount"><?= number_format($deposit['amount'] ?? 0) ?> VND</span>
                    </div>
                    <div class="info-row reference-row">
                        <label>Reference Code</label>
                        <div class="copyable-field">
                            <code id="referenceCode"><?= htmlspecialchars($deposit['reference_code'] ?? '') ?></code>
                            <button type="button" class="btn btn-sm btn-outline-secondary copy-btn" onclick="copyToClipboard('referenceCode')">
                                <i class="icon-copy"></i> Copy
                            </button>
                        </div>
                    </div>
                    <div class="info-row">
                        <label>Created</label>
                        <span><?= htmlspecialchars(date('M d, Y H:i', strtotime($deposit['created_at'] ?? ''))) ?></span>
                    </div>
                    <?php if (!empty($deposit['processed_at'])): ?>
                    <div class="info-row">
                        <label>Processed</label>
                        <span><?= htmlspecialchars(date('M d, Y H:i', strtotime($deposit['processed_at']))) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h3>Bank Information</h3>
            </div>
            <div class="card-body">
                <div class="bank-info">
                    <div class="info-row">
                        <label>Bank Name</label>
                        <span><?= htmlspecialchars($bankName ?? '') ?></span>
                    </div>
                    <div class="info-row">
                        <label>Account Number</label>
                        <div class="copyable-field">
                            <code id="accountNumber"><?= htmlspecialchars($bankAccountNumber ?? '') ?></code>
                            <button type="button" class="btn btn-sm btn-outline-secondary copy-btn" onclick="copyToClipboard('accountNumber')">
                                <i class="icon-copy"></i> Copy
                            </button>
                        </div>
                    </div>
                    <div class="info-row">
                        <label>Account Holder</label>
                        <span><?= htmlspecialchars($accountHolderName ?? '') ?></span>
                    </div>
                </div>
            </div>
        </div>

        <?php if (($deposit['status'] ?? '') === 'pending'): ?>
        <div class="card mt-4">
            <div class="card-header">
                <h3>Actions</h3>
            </div>
            <div class="card-body">
                <form action="/billing/deposit/<?= htmlspecialchars($deposit['id'] ?? '') ?>/cancel" method="POST" onsubmit="return confirm('Are you sure you want to cancel this deposit?');">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <button type="submit" class="btn btn-danger btn-block">
                        <i class="icon-x"></i> Cancel Deposit
                    </button>
                </form>
                <p class="text-muted text-center mt-2">
                    <small>Cancelled deposits cannot be recovered</small>
                </p>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (($deposit['status'] ?? '') === 'approved'): ?>
        <div class="card mt-4">
            <div class="card-header">
                <h3>Invoice</h3>
            </div>
            <div class="card-body text-center">
                <p class="text-muted mb-3">Download your invoice for this deposit.</p>
                <a href="/invoice/deposit/<?= (int)$deposit['id'] ?>" class="btn btn-primary btn-lg" target="_blank">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                    </svg>
                    View Invoice
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
function getStatusBadgeClass(string $status): string {
    return match($status) {
        'pending' => 'warning',
        'approved' => 'success',
        'rejected' => 'danger',
        'expired' => 'secondary',
        default => 'secondary'
    };
}
?>

<script>
function copyToClipboard(elementId) {
    var text = document.getElementById(elementId).innerText;
    navigator.clipboard.writeText(text).then(function() {
        // Show feedback
        var btn = event.target.closest('.copy-btn');
        var originalText = btn.innerHTML;
        btn.innerHTML = '<i class="icon-check"></i> Copied!';
        btn.classList.add('btn-success');
        btn.classList.remove('btn-outline-secondary');
        
        setTimeout(function() {
            btn.innerHTML = originalText;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-outline-secondary');
        }, 2000);
    }).catch(function(err) {
        console.error('Failed to copy: ', err);
        alert('Failed to copy to clipboard');
    });
}
</script>

<style>
.qr-code-container {
    padding: 20px;
    background: white;
    border-radius: 10px;
    display: inline-block;
    border: 2px solid #e9ecef;
}

.qr-code-image {
    max-width: 280px;
    height: auto;
}

.instructions-list {
    padding-left: 20px;
    margin: 0;
}

.instructions-list li {
    margin-bottom: 12px;
    line-height: 1.5;
}

.instructions-list li:last-child {
    margin-bottom: 0;
}

.deposit-info, .bank-info {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #e9ecef;
}

.info-row:last-child {
    border-bottom: none;
}

.info-row label {
    color: #6c757d;
    font-weight: 500;
    margin: 0;
}

.amount-row .amount {
    font-size: 1.5rem;
    font-weight: bold;
    color: #28a745;
}

.reference-row code {
    font-size: 1.1rem;
    background: #fff3cd;
    padding: 5px 10px;
    border-radius: 4px;
    color: #856404;
}

.copyable-field {
    display: flex;
    align-items: center;
    gap: 10px;
}

.copy-btn {
    padding: 4px 10px;
    font-size: 0.85rem;
}

.badge-lg {
    padding: 8px 16px;
    font-size: 0.9rem;
}
</style>
