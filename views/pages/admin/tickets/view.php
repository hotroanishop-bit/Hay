<?php
/**
 * Admin Ticket View Page
 * Variables: $pageTitle, $currentPage, $ticket, $messages, $admins, $statuses, $priorities, $admin
 */
?>

<div class="page-header">
    <div class="page-header-content">
        <a href="/admin/tickets" class="back-link">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            Back to Tickets
        </a>
        <div class="ticket-header-info">
            <h1 class="page-title">
                <span class="ticket-number"><?= htmlspecialchars($ticket['ticket_number'] ?? '#' . $ticket['id']) ?></span>
                <?= htmlspecialchars($ticket['subject'] ?? 'Ticket') ?>
            </h1>
            <div class="ticket-badges">
                <?php
                $statusClass = match($ticket['status'] ?? 'open') {
                    'open' => 'badge-warning',
                    'in_progress' => 'badge-info',
                    'waiting_reply' => 'badge-primary',
                    'resolved' => 'badge-success',
                    'closed' => 'badge-gray',
                    default => 'badge-gray'
                };
                $priorityClass = match($ticket['priority'] ?? 'medium') {
                    'urgent' => 'badge-error',
                    'high' => 'badge-warning',
                    'medium' => 'badge-info',
                    'low' => 'badge-gray',
                    default => 'badge-gray'
                };
                ?>
                <span class="badge <?= $statusClass ?>"><?= ucfirst(str_replace('_', ' ', htmlspecialchars($ticket['status'] ?? 'open'))) ?></span>
                <span class="badge <?= $priorityClass ?>"><?= ucfirst(htmlspecialchars($ticket['priority'] ?? 'medium')) ?> Priority</span>
                <span class="badge badge-outline"><?= ucfirst(htmlspecialchars($ticket['category'] ?? 'other')) ?></span>
            </div>
        </div>
    </div>
</div>

<div class="ticket-layout">
    <div class="ticket-main">
        <!-- Conversation -->
        <div class="conversation-container">
            <?php if (!empty($messages)): ?>
            <?php foreach ($messages as $index => $msg): ?>
            <div class="message-card <?= $msg['sender_type'] === 'admin' ? 'message-admin' : 'message-user' ?> <?= ($msg['is_internal'] ?? false) ? 'message-internal' : '' ?>">
                <div class="message-header">
                    <div class="message-author">
                        <div class="author-avatar <?= $msg['sender_type'] === 'admin' ? 'avatar-admin' : '' ?>">
                            <?php if ($msg['sender_type'] === 'admin'): ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                            <?php else: ?>
                            <?= htmlspecialchars(strtoupper(substr($msg['sender_name'] ?? 'U', 0, 1))) ?>
                            <?php endif; ?>
                        </div>
                        <div class="author-info">
                            <span class="author-name">
                                <?= htmlspecialchars($msg['sender_name'] ?? 'Unknown') ?>
                                <?php if ($msg['sender_type'] === 'admin'): ?>
                                <span class="badge badge-primary badge-sm">Admin</span>
                                <?php endif; ?>
                                <?php if ($msg['is_internal'] ?? false): ?>
                                <span class="badge badge-warning badge-sm">Internal Note</span>
                                <?php endif; ?>
                            </span>
                            <span class="message-date"><?= htmlspecialchars(date('M d, Y \a\t g:i A', strtotime($msg['created_at'] ?? 'now'))) ?></span>
                        </div>
                    </div>
                    <?php if ($index === 0): ?>
                    <span class="badge badge-outline badge-sm">Original</span>
                    <?php endif; ?>
                </div>
                <div class="message-body">
                    <?= nl2br(htmlspecialchars($msg['message'] ?? '')) ?>
                </div>
                <?php if (!empty($msg['attachments'])): ?>
                <div class="message-attachments">
                    <strong>Attachments:</strong>
                    <?php foreach ($msg['attachments'] as $attachment): ?>
                    <a href="<?= htmlspecialchars($attachment) ?>" target="_blank" class="attachment-link"><?= htmlspecialchars(basename($attachment)) ?></a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <?php else: ?>
            <div class="empty-conversation">
                <p>No messages in this ticket yet.</p>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Reply Form -->
        <?php if (($ticket['status'] ?? '') !== 'closed'): ?>
        <div class="reply-form-card">
            <div class="reply-header">
                <h3>Reply to Ticket</h3>
            </div>
            <form action="/admin/tickets/<?= (int)$ticket['id'] ?>/reply" method="POST" class="reply-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                
                <div class="form-group">
                    <textarea name="message" class="form-textarea" rows="5" placeholder="Type your reply here..." required></textarea>
                </div>
                
                <div class="form-actions">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_internal" value="1">
                        <span>Internal note (only visible to admins)</span>
                    </label>
                    <button type="submit" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m22 2-7 20-4-9-9-4Z"></path><path d="M22 2 11 13"></path></svg>
                        Send Reply
                    </button>
                </div>
            </form>
        </div>
        <?php else: ?>
        <div class="ticket-closed-notice">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="m15 9-6 6"></path><path d="m9 9 6 6"></path></svg>
            <div>
                <strong>This ticket is closed</strong>
                <p>You can reopen it if further action is needed.</p>
            </div>
            <form action="/admin/tickets/<?= (int)$ticket['id'] ?>/reopen" method="POST" class="inline-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <button type="submit" class="btn btn-secondary">Reopen Ticket</button>
            </form>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="ticket-sidebar">
        <!-- Ticket Info -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Ticket Information</h3>
            </div>
            <div class="card-body">
                <div class="info-list">
                    <div class="info-item">
                        <span class="info-label">Status</span>
                        <span class="badge <?= $statusClass ?>"><?= ucfirst(str_replace('_', ' ', htmlspecialchars($ticket['status'] ?? 'open'))) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Priority</span>
                        <span class="badge <?= $priorityClass ?>"><?= ucfirst(htmlspecialchars($ticket['priority'] ?? 'medium')) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Category</span>
                        <span><?= ucfirst(htmlspecialchars($ticket['category'] ?? 'other')) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Created</span>
                        <span><?= htmlspecialchars(date('M d, Y H:i', strtotime($ticket['created_at'] ?? 'now'))) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Last Updated</span>
                        <span><?= htmlspecialchars(date('M d, Y H:i', strtotime($ticket['updated_at'] ?? 'now'))) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Messages</span>
                        <span><?= count($messages ?? []) ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- User Info -->
        <div class="card mt-4">
            <div class="card-header">
                <h3 class="card-title">User Information</h3>
            </div>
            <div class="card-body">
                <div class="user-card">
                    <div class="user-avatar">
                        <?= htmlspecialchars(strtoupper(substr($ticket['user_name'] ?? 'U', 0, 1))) ?>
                    </div>
                    <div class="user-details">
                        <span class="user-name"><?= htmlspecialchars($ticket['user_name'] ?? 'Unknown') ?></span>
                        <span class="user-email"><?= htmlspecialchars($ticket['user_email'] ?? '') ?></span>
                        <a href="/admin/users/<?= (int)$ticket['user_id'] ?>" class="user-link">View Profile</a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Actions -->
        <div class="card mt-4">
            <div class="card-header">
                <h3 class="card-title">Actions</h3>
            </div>
            <div class="card-body">
                <!-- Status Update -->
                <form action="/admin/tickets/<?= (int)$ticket['id'] ?>/status" method="POST" class="action-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <label class="form-label">Change Status</label>
                    <div class="action-row">
                        <select name="status" class="form-select form-select-sm">
                            <?php foreach ($statuses ?? [] as $value => $label): ?>
                            <option value="<?= htmlspecialchars($value) ?>" <?= ($ticket['status'] ?? '') === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-sm btn-secondary">Update</button>
                    </div>
                </form>
                
                <!-- Assignment -->
                <form action="/admin/tickets/<?= (int)$ticket['id'] ?>/assign" method="POST" class="action-form mt-4">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <label class="form-label">Assign To</label>
                    <div class="action-row">
                        <select name="assigned_to" class="form-select form-select-sm">
                            <option value="">Unassigned</option>
                            <?php foreach ($admins ?? [] as $adminUser): ?>
                            <option value="<?= (int)$adminUser['id'] ?>" <?= ($ticket['assigned_to'] ?? '') == $adminUser['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($adminUser['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-sm btn-secondary">Assign</button>
                    </div>
                </form>
                
                <hr class="my-4">
                
                <?php if (($ticket['status'] ?? '') !== 'closed'): ?>
                <form action="/admin/tickets/<?= (int)$ticket['id'] ?>/close" method="POST" class="action-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <button type="submit" class="btn btn-block btn-error" onclick="return confirm('Are you sure you want to close this ticket?')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="m15 9-6 6"></path><path d="m9 9 6 6"></path></svg>
                        Close Ticket
                    </button>
                </form>
                <?php endif; ?>
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

/* Ticket Header */
.ticket-header-info .ticket-number {
    color: var(--color-primary);
    font-family: var(--font-mono);
    margin-right: var(--space-2);
}

.ticket-badges {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    margin-top: var(--space-2);
}

/* Layout */
.ticket-layout {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: var(--space-6);
    align-items: start;
}

.ticket-sidebar {
    position: sticky;
    top: calc(var(--topbar-height) + var(--space-6));
}

/* Conversation */
.conversation-container {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
    margin-bottom: var(--space-6);
}

.message-card {
    background: var(--surface-primary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    overflow: hidden;
}

.message-card.message-admin {
    border-left: 3px solid var(--color-primary);
}

.message-card.message-internal {
    border-left: 3px solid var(--color-warning);
    background: var(--color-warning-light);
}

.message-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: var(--space-4);
    background: var(--bg-secondary);
    border-bottom: 1px solid var(--border-light);
}

.message-author {
    display: flex;
    align-items: center;
    gap: var(--space-3);
}

.author-avatar {
    width: 40px;
    height: 40px;
    border-radius: var(--radius-full);
    background: var(--color-gray-200);
    color: var(--color-gray-600);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: var(--font-weight-semibold);
    font-size: var(--font-size-sm);
}

.author-avatar.avatar-admin {
    background: var(--color-primary);
    color: var(--color-white);
}

.author-info {
    display: flex;
    flex-direction: column;
}

.author-name {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    font-weight: var(--font-weight-semibold);
    color: var(--text-primary);
}

.message-date {
    font-size: var(--font-size-xs);
    color: var(--text-muted);
}

.message-body {
    padding: var(--space-5);
    font-size: var(--font-size-base);
    line-height: var(--line-height-relaxed);
    color: var(--text-primary);
}

.message-attachments {
    padding: var(--space-3) var(--space-5);
    background: var(--bg-tertiary);
    border-top: 1px solid var(--border-light);
}

.attachment-link {
    display: inline-flex;
    align-items: center;
    margin-left: var(--space-2);
    color: var(--color-primary);
}

/* Reply Form */
.reply-form-card {
    background: var(--surface-primary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    overflow: hidden;
}

.reply-header {
    padding: var(--space-4);
    background: var(--bg-secondary);
    border-bottom: 1px solid var(--border-light);
}

.reply-header h3 {
    margin: 0;
    font-size: var(--font-size-base);
    font-weight: var(--font-weight-semibold);
}

.reply-form {
    padding: var(--space-4);
}

.reply-form .form-textarea {
    min-height: 120px;
}

.reply-form .form-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: var(--space-4);
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
    cursor: pointer;
}

/* Closed Notice */
.ticket-closed-notice {
    display: flex;
    align-items: center;
    gap: var(--space-4);
    padding: var(--space-4);
    background: var(--bg-tertiary);
    border-radius: var(--radius-lg);
    color: var(--text-secondary);
}

.ticket-closed-notice svg {
    flex-shrink: 0;
    color: var(--text-muted);
}

.ticket-closed-notice strong {
    display: block;
    color: var(--text-primary);
    margin-bottom: var(--space-1);
}

.ticket-closed-notice p {
    margin: 0;
    font-size: var(--font-size-sm);
}

/* Info List */
.info-list {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.info-label {
    font-size: var(--font-size-sm);
    color: var(--text-muted);
}

/* User Card */
.user-card {
    display: flex;
    align-items: center;
    gap: var(--space-3);
}

.user-avatar {
    width: 48px;
    height: 48px;
    border-radius: var(--radius-full);
    background: var(--color-primary-light);
    color: var(--color-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: var(--font-weight-bold);
    font-size: var(--font-size-lg);
}

.user-details {
    display: flex;
    flex-direction: column;
}

.user-details .user-name {
    font-weight: var(--font-weight-semibold);
    color: var(--text-primary);
}

.user-details .user-email {
    font-size: var(--font-size-sm);
    color: var(--text-muted);
}

.user-link {
    font-size: var(--font-size-sm);
    color: var(--color-primary);
    margin-top: var(--space-1);
}

/* Action Forms */
.action-form .form-label {
    display: block;
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-medium);
    color: var(--text-secondary);
    margin-bottom: var(--space-2);
}

.action-row {
    display: flex;
    gap: var(--space-2);
}

.action-row .form-select {
    flex: 1;
}

/* Responsive */
@media (max-width: 1023px) {
    .ticket-layout {
        grid-template-columns: 1fr;
    }
    
    .ticket-sidebar {
        position: static;
    }
}
</style>
