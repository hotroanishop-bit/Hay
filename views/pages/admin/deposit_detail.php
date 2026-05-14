<?php
/**
 * Admin Deposit Detail Page
 * Variables: $pageTitle, $currentPage, $deposit, $auditHistory
 */
?>

<div class="page-header">
    <div class="page-header-content">
        <div class="page-header-left">
            <a href="/admin/deposits" class="btn btn-sm btn-secondary mb-2">
                <i class="icon-arrow-left"></i> Back to Deposits
            </a>
            <h1>Deposit #<?= (int)($deposit['id'] ?? 0) ?></h1>
            <p>View deposit details and actions</p>
        </div>
        <div class="page-header-right">
            <?php
            $statusClass = 'secondary';
            switch ($deposit['status'] ?? '') {
                case 'pending': $statusClass = 'warning'; break;
                case 'approved': $statusClass = 'success'; break;
                case 'rejected': $statusClass = 'danger'; break;
                case 'expired': $statusClass = 'secondary'; break;
            }
            ?>
            <span class="badge badge-lg badge-<?= $statusClass ?>"><?= ucfirst(htmlspecialchars($deposit['status'] ?? '')) ?></span>
        </div>
    </div>
</div>

<div class="detail-grid">
    <div class="detail-main">
        <div class="card">
            <div class="card-header">
                <h3>Deposit Information</h3>
            </div>
            <div class="card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Amount</span>
                        <span class="info-value info-value-lg">$<?= number_format($deposit['amount'] ?? 0, 2) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Reference Code</span>
                        <span class="info-value"><code><?= htmlspecialchars($deposit['reference_code'] ?? '') ?></code></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Bank Account</span>
                        <span class="info-value"><?= htmlspecialchars($deposit['bank_account'] ?? 'N/A') ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Created</span>
                        <span class="info-value"><?= htmlspecialchars(date('M d, Y H:i:s', strtotime($deposit['created_at'] ?? 'now'))) ?></span>
                    </div>
                    <?php if (!empty($deposit['processed_at'])): ?>
                    <div class="info-item">
                        <span class="info-label">Processed</span>
                        <span class="info-value"><?= htmlspecialchars(date('M d, Y H:i:s', strtotime($deposit['processed_at']))) ?></span>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if (!empty($deposit['qr_data'])): ?>
                <div class="qr-section mt-4">
                    <h4>QR Code</h4>
                    <div class="qr-code">
                        <img src="<?= htmlspecialchars($deposit['qr_data']) ?>" alt="Payment QR Code" class="qr-image">
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (($deposit['status'] ?? '') === 'pending'): ?>
        <div class="card mt-4">
            <div class="card-header">
                <h3>Actions</h3>
            </div>
            <div class="card-body">
                <div class="action-buttons">
                    <form action="/admin/deposits/<?= (int)$deposit['id'] ?>/approve" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to approve this deposit? This will add $<?= number_format($deposit['amount'] ?? 0, 2) ?> to the user balance.');">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="icon-check"></i> Approve Deposit
                        </button>
                    </form>
                </div>

                <hr class="my-4">

                <form action="/admin/deposits/<?= (int)$deposit['id'] ?>/reject" method="POST" onsubmit="return confirm('Are you sure you want to reject this deposit?');">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <div class="form-group">
                        <label for="reason">Rejection Reason</label>
                        <textarea name="reason" id="reason" class="form-control" rows="3" placeholder="Enter reason for rejection (optional)..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger">
                        <i class="icon-x"></i> Reject Deposit
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($auditHistory)): ?>
        <div class="card mt-4">
            <div class="card-header">
                <h3>History</h3>
            </div>
            <div class="card-body">
                <div class="audit-history">
                    <?php foreach ($auditHistory as $entry): ?>
                    <div class="audit-item">
                        <div class="audit-icon">
                            <?php
                            $iconClass = 'icon-activity';
                            if (strpos($entry['action'], 'approved') !== false) {
                                $iconClass = 'icon-check';
                            } elseif (strpos($entry['action'], 'rejected') !== false) {
                                $iconClass = 'icon-x';
                            }
                            ?>
                            <i class="<?= $iconClass ?>"></i>
                        </div>
                        <div class="audit-content">
                            <div class="audit-title">
                                <strong><?= htmlspecialchars($entry['admin_name'] ?? 'System') ?></strong>
                                - <?= htmlspecialchars($entry['action']) ?>
                            </div>
                            <?php if (!empty($entry['new_value'])): ?>
                            <div class="audit-details">
                                <?php if (is_array($entry['new_value'])): ?>
                                    <?php foreach ($entry['new_value'] as $key => $value): ?>
                                        <span class="audit-detail"><?= htmlspecialchars($key) ?>: <?= htmlspecialchars(is_array($value) ? json_encode($value) : $value) ?></span>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                            <div class="audit-meta">
                                <span><?= htmlspecialchars(date('M d, Y H:i', strtotime($entry['created_at']))) ?></span>
                                <span>IP: <?= htmlspecialchars($entry['ip_address'] ?? 'unknown') ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="detail-sidebar">
        <div class="card">
            <div class="card-header">
                <h3>User Information</h3>
            </div>
            <div class="card-body">
                <div class="user-profile-card">
                    <div class="user-avatar">
                        <span class="avatar-placeholder"><?= strtoupper(substr($deposit['user_name'] ?? 'U', 0, 1)) ?></span>
                    </div>
                    <div class="user-info">
                        <h4><?= htmlspecialchars($deposit['user_name'] ?? 'Unknown') ?></h4>
                        <p><?= htmlspecialchars($deposit['user_email'] ?? '') ?></p>
                    </div>
                </div>
                <div class="info-item mt-3">
                    <span class="info-label">Current Balance</span>
                    <span class="info-value">$<?= number_format($deposit['user_balance'] ?? 0, 2) ?></span>
                </div>
                <div class="mt-3">
                    <a href="/admin/users/<?= (int)$deposit['user_id'] ?>" class="btn btn-sm btn-secondary btn-block">
                        View User Profile
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
