<?php
/**
 * Receipt Template for Purchases/Transactions
 * Variables: $receipt (array with receipt data)
 */
$company = $receipt['company'] ?? [];
$user = $receipt['user'] ?? [];
$items = $receipt['items'] ?? [];
?>

<div class="receipt">
    <!-- Header -->
    <div class="receipt-header">
        <div class="receipt-brand">
            <h1 class="company-name"><?php echo htmlspecialchars($company['name'] ?? 'Hay API Gateway'); ?></h1>
            <?php if (!empty($company['email'])): ?>
                <p class="company-contact"><?php echo htmlspecialchars($company['email']); ?></p>
            <?php endif; ?>
            <?php if (!empty($company['website'])): ?>
                <p class="company-website"><?php echo htmlspecialchars($company['website']); ?></p>
            <?php endif; ?>
        </div>
        <div class="receipt-info">
            <h2 class="receipt-title">RECEIPT</h2>
            <table class="receipt-meta">
                <tr>
                    <td class="label">Receipt No:</td>
                    <td class="value"><?php echo htmlspecialchars($receipt['invoice_number'] ?? ''); ?></td>
                </tr>
                <tr>
                    <td class="label">Date:</td>
                    <td class="value"><?php echo date('F j, Y', strtotime($receipt['date'] ?? 'now')); ?></td>
                </tr>
                <tr>
                    <td class="label">Time:</td>
                    <td class="value"><?php echo date('g:i A', strtotime($receipt['date'] ?? 'now')); ?></td>
                </tr>
                <tr>
                    <td class="label">Type:</td>
                    <td class="value type-<?php echo strtolower($receipt['transaction_type'] ?? 'credit'); ?>">
                        <?php echo htmlspecialchars($receipt['transaction_type'] ?? 'Credit'); ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Customer Info -->
    <div class="receipt-customer">
        <div class="customer-info">
            <h3>Customer:</h3>
            <p class="customer-name"><?php echo htmlspecialchars($user['name'] ?? 'Customer'); ?></p>
            <p class="customer-email"><?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
        </div>
        <div class="payment-method">
            <h3>Payment:</h3>
            <p><?php echo htmlspecialchars($receipt['payment_method'] ?? 'Account Balance'); ?></p>
            <p class="status status-<?php echo strtolower($receipt['status'] ?? 'completed'); ?>">
                <?php echo htmlspecialchars($receipt['status'] ?? 'Completed'); ?>
            </p>
        </div>
    </div>

    <!-- Transaction Details -->
    <div class="receipt-details">
        <table class="receipt-items">
            <thead>
                <tr>
                    <th class="col-description">Description</th>
                    <th class="col-reference">Reference</th>
                    <th class="col-amount">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td class="col-description"><?php echo htmlspecialchars($item['description'] ?? ''); ?></td>
                    <td class="col-reference"><code><?php echo htmlspecialchars($item['reference'] ?? '-'); ?></code></td>
                    <td class="col-amount amount-<?php echo ($receipt['transaction_type'] ?? 'credit') === 'Credit' ? 'credit' : 'debit'; ?>">
                        <?php echo ($receipt['transaction_type'] ?? 'credit') === 'Credit' ? '+' : '-'; ?>$<?php echo number_format($item['amount'] ?? 0, 2); ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Total -->
    <div class="receipt-total">
        <div class="total-box">
            <span class="total-label">Total:</span>
            <span class="total-amount amount-<?php echo ($receipt['transaction_type'] ?? 'credit') === 'Credit' ? 'credit' : 'debit'; ?>">
                <?php echo ($receipt['transaction_type'] ?? 'credit') === 'Credit' ? '+' : '-'; ?>$<?php echo number_format($receipt['total'] ?? 0, 2); ?>
            </span>
        </div>
    </div>

    <!-- Notes -->
    <?php if (!empty($receipt['notes'])): ?>
    <div class="receipt-notes">
        <p><?php echo htmlspecialchars($receipt['notes']); ?></p>
    </div>
    <?php endif; ?>

    <!-- Footer -->
    <div class="receipt-footer">
        <p>Thank you for using <?php echo htmlspecialchars($company['name'] ?? 'Hay API Gateway'); ?>!</p>
        <p class="receipt-id">Receipt ID: <?php echo htmlspecialchars($receipt['invoice_number'] ?? ''); ?></p>
    </div>
</div>

<style>
.receipt {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    max-width: 100%;
    color: #1a1a1a;
}

.receipt-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #e5e7eb;
}

.receipt-brand .company-name {
    font-size: 22px;
    font-weight: 700;
    color: #6366f1;
    margin: 0 0 8px 0;
}

.receipt-brand p {
    margin: 4px 0;
    color: #6b7280;
    font-size: 13px;
}

.receipt-info {
    text-align: right;
}

.receipt-title {
    font-size: 28px;
    font-weight: 700;
    color: #1a1a1a;
    margin: 0 0 12px 0;
    letter-spacing: 2px;
}

.receipt-meta {
    text-align: left;
}

.receipt-meta td {
    padding: 3px 0;
    font-size: 13px;
}

.receipt-meta .label {
    color: #6b7280;
    padding-right: 12px;
}

.receipt-meta .value {
    font-weight: 600;
    color: #1a1a1a;
}

.type-credit {
    color: #059669 !important;
}

.type-debit {
    color: #dc2626 !important;
}

.receipt-customer {
    display: flex;
    justify-content: space-between;
    margin-bottom: 30px;
    padding: 20px;
    background: #f9fafb;
    border-radius: 8px;
}

.receipt-customer h3 {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #6b7280;
    margin: 0 0 8px 0;
}

.customer-info .customer-name {
    font-size: 15px;
    font-weight: 600;
    margin: 0 0 4px 0;
}

.customer-info .customer-email {
    font-size: 13px;
    color: #6b7280;
    margin: 0;
}

.payment-method {
    text-align: right;
}

.payment-method p {
    font-size: 14px;
    margin: 4px 0;
}

.status {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.status-completed {
    background: #d1fae5;
    color: #059669;
}

.status-pending {
    background: #fef3c7;
    color: #d97706;
}

.receipt-items {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}

.receipt-items th {
    background: #f3f4f6;
    padding: 10px 12px;
    text-align: left;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6b7280;
    border-bottom: 2px solid #e5e7eb;
}

.receipt-items td {
    padding: 12px;
    border-bottom: 1px solid #e5e7eb;
    font-size: 14px;
}

.receipt-items .col-amount {
    text-align: right;
    font-weight: 600;
}

.receipt-items th.col-amount {
    text-align: right;
}

.receipt-items code {
    background: #f3f4f6;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 11px;
}

.amount-credit {
    color: #059669;
}

.amount-debit {
    color: #dc2626;
}

.receipt-total {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 30px;
}

.total-box {
    background: #f3f4f6;
    padding: 15px 25px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 20px;
}

.total-label {
    font-size: 14px;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.total-amount {
    font-size: 24px;
    font-weight: 700;
}

.receipt-notes {
    background: #eff6ff;
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 30px;
    border-left: 4px solid #6366f1;
}

.receipt-notes p {
    margin: 0;
    font-size: 14px;
    color: #4b5563;
}

.receipt-footer {
    text-align: center;
    padding-top: 25px;
    border-top: 1px dashed #d1d5db;
    color: #6b7280;
}

.receipt-footer p {
    margin: 5px 0;
    font-size: 14px;
}

.receipt-footer .receipt-id {
    font-size: 11px;
    font-family: monospace;
    color: #9ca3af;
}

@media print {
    .receipt {
        padding: 0;
    }
    
    .receipt-brand .company-name {
        color: #000;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    
    .receipt-customer {
        background: #f9fafb;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    
    .status-completed {
        background: #d1fae5;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
}
</style>
