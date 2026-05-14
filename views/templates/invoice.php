<?php
/**
 * Invoice Template for Deposits
 * Variables: $invoice (array with invoice data)
 */
$company = $invoice['company'] ?? [];
$user = $invoice['user'] ?? [];
$items = $invoice['items'] ?? [];
?>

<div class="invoice">
    <!-- Header -->
    <div class="invoice-header">
        <div class="invoice-brand">
            <h1 class="company-name"><?php echo htmlspecialchars($company['name'] ?? 'Hay API Gateway'); ?></h1>
            <?php if (!empty($company['address'])): ?>
                <p class="company-address"><?php echo htmlspecialchars($company['address']); ?></p>
            <?php endif; ?>
            <?php if (!empty($company['email'])): ?>
                <p class="company-contact"><?php echo htmlspecialchars($company['email']); ?></p>
            <?php endif; ?>
            <?php if (!empty($company['website'])): ?>
                <p class="company-website"><?php echo htmlspecialchars($company['website']); ?></p>
            <?php endif; ?>
        </div>
        <div class="invoice-info">
            <h2 class="invoice-title">INVOICE</h2>
            <table class="invoice-meta">
                <tr>
                    <td class="label">Invoice No:</td>
                    <td class="value"><?php echo htmlspecialchars($invoice['invoice_number'] ?? ''); ?></td>
                </tr>
                <tr>
                    <td class="label">Date:</td>
                    <td class="value"><?php echo date('F j, Y', strtotime($invoice['date'] ?? 'now')); ?></td>
                </tr>
                <tr>
                    <td class="label">Status:</td>
                    <td class="value status-paid"><?php echo htmlspecialchars($invoice['status'] ?? 'Paid'); ?></td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Bill To -->
    <div class="invoice-parties">
        <div class="bill-to">
            <h3>Bill To:</h3>
            <p class="client-name"><?php echo htmlspecialchars($user['name'] ?? 'Customer'); ?></p>
            <p class="client-email"><?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
        </div>
        <div class="payment-info">
            <h3>Payment Method:</h3>
            <p><?php echo htmlspecialchars($invoice['payment_method'] ?? 'Bank Transfer'); ?></p>
            <?php if (!empty($invoice['bank_account'])): ?>
                <p class="bank-ref">Bank: <?php echo htmlspecialchars($invoice['bank_account']); ?></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Items Table -->
    <table class="invoice-items">
        <thead>
            <tr>
                <th class="col-description">Description</th>
                <th class="col-reference">Reference</th>
                <th class="col-qty">Qty</th>
                <th class="col-amount">Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
            <tr>
                <td class="col-description"><?php echo htmlspecialchars($item['description'] ?? ''); ?></td>
                <td class="col-reference"><code><?php echo htmlspecialchars($item['reference'] ?? '-'); ?></code></td>
                <td class="col-qty"><?php echo (int)($item['quantity'] ?? 1); ?></td>
                <td class="col-amount">$<?php echo number_format($item['amount'] ?? 0, 2); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Totals -->
    <div class="invoice-totals">
        <table>
            <tr>
                <td class="label">Subtotal:</td>
                <td class="value">$<?php echo number_format($invoice['subtotal'] ?? 0, 2); ?></td>
            </tr>
            <?php if (($invoice['tax'] ?? 0) > 0): ?>
            <tr>
                <td class="label">Tax:</td>
                <td class="value">$<?php echo number_format($invoice['tax'], 2); ?></td>
            </tr>
            <?php endif; ?>
            <tr class="total-row">
                <td class="label">Total:</td>
                <td class="value total">$<?php echo number_format($invoice['total'] ?? 0, 2); ?></td>
            </tr>
        </table>
    </div>

    <!-- Notes -->
    <?php if (!empty($invoice['notes'])): ?>
    <div class="invoice-notes">
        <h4>Notes:</h4>
        <p><?php echo htmlspecialchars($invoice['notes']); ?></p>
    </div>
    <?php endif; ?>

    <!-- Footer -->
    <div class="invoice-footer">
        <p>Thank you for your business!</p>
        <?php if (!empty($company['tax_id'])): ?>
            <p class="tax-id">Tax ID: <?php echo htmlspecialchars($company['tax_id']); ?></p>
        <?php endif; ?>
    </div>
</div>

<style>
.invoice {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    max-width: 100%;
    color: #1a1a1a;
}

.invoice-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 40px;
    padding-bottom: 20px;
    border-bottom: 2px solid #e5e7eb;
}

.invoice-brand .company-name {
    font-size: 24px;
    font-weight: 700;
    color: #6366f1;
    margin: 0 0 8px 0;
}

.invoice-brand p {
    margin: 4px 0;
    color: #6b7280;
    font-size: 14px;
}

.invoice-info {
    text-align: right;
}

.invoice-title {
    font-size: 32px;
    font-weight: 700;
    color: #1a1a1a;
    margin: 0 0 15px 0;
    letter-spacing: 2px;
}

.invoice-meta {
    text-align: left;
}

.invoice-meta td {
    padding: 4px 0;
    font-size: 14px;
}

.invoice-meta .label {
    color: #6b7280;
    padding-right: 15px;
}

.invoice-meta .value {
    font-weight: 600;
    color: #1a1a1a;
}

.status-paid {
    color: #059669 !important;
}

.invoice-parties {
    display: flex;
    justify-content: space-between;
    margin-bottom: 40px;
}

.invoice-parties h3 {
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #6b7280;
    margin: 0 0 10px 0;
}

.bill-to .client-name {
    font-size: 16px;
    font-weight: 600;
    margin: 0 0 4px 0;
}

.bill-to .client-email,
.payment-info p {
    font-size: 14px;
    color: #6b7280;
    margin: 4px 0;
}

.payment-info {
    text-align: right;
}

.invoice-items {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 30px;
}

.invoice-items th {
    background: #f3f4f6;
    padding: 12px 15px;
    text-align: left;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6b7280;
    border-bottom: 2px solid #e5e7eb;
}

.invoice-items td {
    padding: 15px;
    border-bottom: 1px solid #e5e7eb;
    font-size: 14px;
}

.invoice-items .col-qty,
.invoice-items .col-amount {
    text-align: right;
}

.invoice-items th.col-qty,
.invoice-items th.col-amount {
    text-align: right;
}

.invoice-items code {
    background: #f3f4f6;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 12px;
}

.invoice-totals {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 40px;
}

.invoice-totals table {
    min-width: 250px;
}

.invoice-totals td {
    padding: 8px 0;
    font-size: 14px;
}

.invoice-totals .label {
    color: #6b7280;
    padding-right: 30px;
}

.invoice-totals .value {
    text-align: right;
    font-weight: 500;
}

.invoice-totals .total-row td {
    border-top: 2px solid #e5e7eb;
    padding-top: 12px;
}

.invoice-totals .total {
    font-size: 20px;
    font-weight: 700;
    color: #6366f1;
}

.invoice-notes {
    background: #f9fafb;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 30px;
}

.invoice-notes h4 {
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #6b7280;
    margin: 0 0 10px 0;
}

.invoice-notes p {
    margin: 0;
    font-size: 14px;
    color: #4b5563;
}

.invoice-footer {
    text-align: center;
    padding-top: 30px;
    border-top: 1px solid #e5e7eb;
    color: #6b7280;
    font-size: 14px;
}

.invoice-footer .tax-id {
    font-size: 12px;
    margin-top: 8px;
}

@media print {
    .invoice {
        padding: 0;
    }
    
    .invoice-header {
        margin-bottom: 30px;
    }
    
    .invoice-brand .company-name {
        color: #000;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    
    .invoice-totals .total {
        color: #000;
    }
}
</style>
