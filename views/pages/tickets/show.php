<?php
/**
 * Single Ticket View
 * Variables: $pageTitle, $currentPage, $ticket, $replies, $user
 */
?>

<div class="page-header">
    <div class="page-header-content">
        <a href="/tickets" class="back-link">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            Back to Tickets
        </a>
        <h1 class="page-title"><?= htmlspecialchars($ticket['subject'] ?? 'Ticket') ?></h1>
        <div class="ticket-badges">
            <?php
            $statusClass = match($ticket['status'] ?? 'open') {
                'open' => 'badge-success',
                'pending' => 'badge-warning',
                'closed' => 'badge-gray',
                default => 'badge-gray'
            };
            ?>
            <span class="badge <?= $statusClass ?>"><?= ucfirst(htmlspecialchars($ticket['status'] ?? 'open')) ?></span>
            
            <?php if (!empty($ticket['priority'])): ?>
            <?php
            $priorityClass = match($ticket['priority']) {
                'high' => 'badge-error',
                'medium' => 'badge-warning',
                'low' => 'badge-gray',
                default => 'badge-gray'
            };
            ?>
            <span class="badge <?= $priorityClass ?>"><?= ucfirst(htmlspecialchars($ticket['priority'])) ?> Priority</span>
            <?php endif; ?>
            
            <span class="ticket-id-badge">#<?= (int)$ticket['id'] ?></span>
        </div>
    </div>
    <?php if (($ticket['status'] ?? '') !== 'closed'): ?>
    <div class="page-header-actions">
        <form action="/tickets/<?= (int)$ticket['id'] ?>/close" method="POST" class="inline-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <button type="submit" class="btn btn-secondary" onclick="return confirm('Are you sure you want to close this ticket?')">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="m15 9-6 6"></path><path d="m9 9 6 6"></path></svg>
                Close Ticket
            </button>
        </form>
    </div>
    <?php endif; ?>
</div>

<div class="ticket-layout">
    <div class="ticket-main">
        <!-- Original Message -->
        <div class="message-card message-original">
            <div class="message-header">
                <div class="message-author">
                    <div class="author-avatar">
                        <?= htmlspecialchars(strtoupper(substr($ticket['user_name'] ?? 'U', 0, 1))) ?>
                    </div>
                    <div class="author-info">
                        <span class="author-name"><?= htmlspecialchars($ticket['user_name'] ?? 'You') ?></span>
                        <span class="message-date"><?= htmlspecialchars(date('M d, Y \a\t g:i A', strtotime($ticket['created_at'] ?? 'now'))) ?></span>
                    </div>
                </div>
                <span class="badge badge-primary badge-sm">Original</span>
            </div>
            <div class="message-body">
                <?= nl2br(htmlspecialchars($ticket['message'] ?? '')) ?>
            </div>
        </div>

        <!-- Replies Thread -->
        <?php if (!empty($replies)): ?>
        <div class="thread-divider">
            <span>Replies</span>
        </div>
        
        <?php foreach ($replies as $reply): ?>
        <div class="message-card <?= !empty($reply['is_admin']) ? 'message-admin' : 'message-user' ?>">
            <div class="message-header">
                <div class="message-author">
                    <div class="author-avatar <?= !empty($reply['is_admin']) ? 'avatar-admin' : '' ?>">
                        <?php if (!empty($reply['is_admin'])): ?>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        <?php else: ?>
                        <?= htmlspecialchars(strtoupper(substr($reply['author_name'] ?? 'U', 0, 1))) ?>
                        <?php endif; ?>
                    </div>
                    <div class="author-info">
                        <span class="author-name">
                            <?= htmlspecialchars($reply['author_name'] ?? 'Unknown') ?>
                            <?php if (!empty($reply['is_admin'])): ?>
                            <span class="badge badge-primary badge-sm">Support</span>
                            <?php endif; ?>
                        </span>
                        <span class="message-date"><?= htmlspecialchars(date('M d, Y \a\t g:i A', strtotime($reply['created_at'] ?? 'now'))) ?></span>
                    </div>
                </div>
            </div>
            <div class="message-body">
                <?= nl2br(htmlspecialchars($reply['message'] ?? '')) ?>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>

        <!-- Reply Form -->
        <?php if (($ticket['status'] ?? '') !== 'closed'): ?>
        <div class="reply-form-card">
            <div class="reply-header">
                <h3>Reply to this ticket</h3>
            </div>
            <form action="/tickets/<?= (int)$ticket['id'] ?>/reply" method="POST" class="reply-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                
                <div class="form-group">
                    <textarea name="message" class="form-textarea" rows="5" placeholder="Type your message here..." required></textarea>
                </div>
                
                <div class="form-actions">
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
                <p>If you need further assistance, please <a href="/tickets/create">create a new ticket</a>.</p>
            </div>
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
                        <span class="badge <?= $statusClass ?>"><?= ucfirst(htmlspecialchars($ticket['status'] ?? 'open')) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Priority</span>
                        <span><?= ucfirst(htmlspecialchars($ticket['priority'] ?? 'Normal')) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Created</span>
                        <span><?= htmlspecialchars(date('M d, Y', strtotime($ticket['created_at'] ?? 'now'))) ?></span>
                    </div>
                    <?php if (!empty($ticket['updated_at'])): ?>
                    <div class="info-item">
                        <span class="info-label">Last Updated</span>
                        <span><?= htmlspecialchars(date('M d, Y', strtotime($ticket['updated_at']))) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="info-item">
                        <span class="info-label">Replies</span>
                        <span><?= count($replies ?? []) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card mt-4">
            <div class="card-header">
                <h3 class="card-title">Quick Actions</h3>
            </div>
            <div class="card-body">
                <div class="quick-actions-list">
                    <a href="/tickets/create" class="quick-action">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                        Create New Ticket
                    </a>
                    <a href="/docs" class="quick-action">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"></path></svg>
                        View Documentation
                    </a>
                </div>
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

/* Ticket Badges */
.ticket-badges {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    margin-top: var(--space-2);
}

.ticket-id-badge {
    font-family: var(--font-mono);
    font-size: var(--font-size-sm);
    color: var(--text-muted);
}

/* Ticket Layout */
.ticket-layout {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: var(--space-6);
    align-items: start;
}

.ticket-sidebar {
    position: sticky;
    top: calc(var(--topbar-height) + var(--space-6));
}

/* Message Cards */
.message-card {
    background: var(--surface-primary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    margin-bottom: var(--space-4);
    overflow: hidden;
}

.message-card.message-admin {
    border-left: 3px solid var(--color-primary);
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

/* Thread Divider */
.thread-divider {
    display: flex;
    align-items: center;
    gap: var(--space-4);
    margin: var(--space-6) 0;
}

.thread-divider::before,
.thread-divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--border-color);
}

.thread-divider span {
    font-size: var(--font-size-sm);
    color: var(--text-muted);
    font-weight: var(--font-weight-medium);
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
    justify-content: flex-end;
    margin-top: var(--space-4);
}

/* Ticket Closed Notice */
.ticket-closed-notice {
    display: flex;
    align-items: flex-start;
    gap: var(--space-3);
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

.info-item span:last-child {
    font-weight: var(--font-weight-medium);
    color: var(--text-primary);
}

/* Quick Actions List */
.quick-actions-list {
    display: flex;
    flex-direction: column;
    gap: var(--space-2);
}

.quick-action {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    padding: var(--space-3);
    border-radius: var(--radius-md);
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
    transition: all var(--transition-fast);
}

.quick-action:hover {
    background: var(--bg-tertiary);
    color: var(--text-primary);
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
