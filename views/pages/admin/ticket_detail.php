<?php
/**
 * Admin Ticket Detail Page
 * Variables: $pageTitle, $currentPage, $ticket
 */
?>

<div class="page-header">
    <div class="page-header-content">
        <div class="page-header-left">
            <a href="/admin/tickets" class="btn btn-sm btn-secondary mb-2">
                <i class="icon-arrow-left"></i> Back to Tickets
            </a>
            <h1>Ticket #<?= (int)($ticket['id'] ?? 0) ?></h1>
            <p><?= htmlspecialchars($ticket['subject'] ?? '') ?></p>
        </div>
    </div>
</div>

<div class="detail-grid">
    <div class="detail-main">
        <div class="card">
            <div class="card-header">
                <h3>Original Message</h3>
                <span class="text-muted"><?= htmlspecialchars(date('M d, Y H:i', strtotime($ticket['created_at'] ?? 'now'))) ?></span>
            </div>
            <div class="card-body">
                <div class="ticket-message">
                    <div class="message-author">
                        <div class="author-avatar">
                            <?php if (!empty($ticket['user_avatar'])): ?>
                            <img src="<?= htmlspecialchars($ticket['user_avatar']) ?>" alt="Avatar">
                            <?php else: ?>
                            <span class="avatar-placeholder"><?= strtoupper(substr($ticket['user_name'] ?? 'U', 0, 1)) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="author-info">
                            <strong><?= htmlspecialchars($ticket['user_name'] ?? 'Unknown') ?></strong>
                            <small><?= htmlspecialchars($ticket['user_email'] ?? '') ?></small>
                        </div>
                    </div>
                    <div class="message-content">
                        <?= nl2br(htmlspecialchars($ticket['message'] ?? '')) ?>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($ticket['admin_reply'])): ?>
        <div class="card mt-4">
            <div class="card-header">
                <h3>Admin Reply</h3>
                <span class="text-muted"><?= htmlspecialchars(date('M d, Y H:i', strtotime($ticket['updated_at'] ?? 'now'))) ?></span>
            </div>
            <div class="card-body">
                <div class="ticket-message admin-reply">
                    <div class="message-author">
                        <div class="author-avatar admin-avatar">
                            <span class="avatar-placeholder">A</span>
                        </div>
                        <div class="author-info">
                            <strong>Admin</strong>
                            <small>Support Team</small>
                        </div>
                    </div>
                    <div class="message-content">
                        <?= nl2br(htmlspecialchars($ticket['admin_reply'])) ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (($ticket['status'] ?? '') !== 'closed'): ?>
        <div class="card mt-4">
            <div class="card-header">
                <h3>Reply to Ticket</h3>
            </div>
            <div class="card-body">
                <form action="/admin/tickets/<?= (int)$ticket['id'] ?>/reply" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <div class="form-group">
                        <label for="reply">Your Reply</label>
                        <textarea name="reply" id="reply" class="form-control" rows="5" required placeholder="Type your response here..."></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="icon-send"></i> Send Reply
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="detail-sidebar">
        <div class="card">
            <div class="card-header">
                <h3>Ticket Info</h3>
            </div>
            <div class="card-body">
                <div class="info-list">
                    <div class="info-item">
                        <span class="info-label">Status</span>
                        <span class="info-value">
                            <?php
                            $statusClass = 'secondary';
                            switch ($ticket['status'] ?? '') {
                                case 'open': $statusClass = 'danger'; break;
                                case 'pending': $statusClass = 'warning'; break;
                                case 'closed': $statusClass = 'success'; break;
                            }
                            ?>
                            <span class="badge badge-<?= $statusClass ?>"><?= ucfirst(htmlspecialchars($ticket['status'] ?? '')) ?></span>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Priority</span>
                        <span class="info-value">
                            <?php
                            $priorityClass = 'secondary';
                            switch ($ticket['priority'] ?? '') {
                                case 'high': $priorityClass = 'danger'; break;
                                case 'medium': $priorityClass = 'warning'; break;
                                case 'low': $priorityClass = 'info'; break;
                            }
                            ?>
                            <span class="badge badge-<?= $priorityClass ?>"><?= ucfirst(htmlspecialchars($ticket['priority'] ?? 'medium')) ?></span>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Created</span>
                        <span class="info-value"><?= htmlspecialchars(date('M d, Y', strtotime($ticket['created_at'] ?? 'now'))) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Last Updated</span>
                        <span class="info-value"><?= htmlspecialchars(date('M d, Y', strtotime($ticket['updated_at'] ?? $ticket['created_at'] ?? 'now'))) ?></span>
                    </div>
                </div>

                <?php if (($ticket['status'] ?? '') !== 'closed'): ?>
                <hr class="my-3">
                <form action="/admin/tickets/<?= (int)$ticket['id'] ?>/close" method="POST" onsubmit="return confirm('Are you sure you want to close this ticket?');">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <button type="submit" class="btn btn-secondary btn-block">
                        <i class="icon-check"></i> Close Ticket
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h3>User</h3>
            </div>
            <div class="card-body">
                <div class="user-profile-card">
                    <div class="user-avatar">
                        <?php if (!empty($ticket['user_avatar'])): ?>
                        <img src="<?= htmlspecialchars($ticket['user_avatar']) ?>" alt="Avatar">
                        <?php else: ?>
                        <span class="avatar-placeholder"><?= strtoupper(substr($ticket['user_name'] ?? 'U', 0, 1)) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="user-info">
                        <h4><?= htmlspecialchars($ticket['user_name'] ?? 'Unknown') ?></h4>
                        <p><?= htmlspecialchars($ticket['user_email'] ?? '') ?></p>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="/admin/users/<?= (int)$ticket['user_id'] ?>" class="btn btn-sm btn-secondary btn-block">
                        View User Profile
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
