<?php
/**
 * Support Tickets Index Page
 * Variables: $pageTitle, $currentPage, $tickets, $statusFilter
 */
?>

<div class="page-header page-header-flex">
    <div class="page-header-content">
        <h1 class="page-title">Support Tickets</h1>
        <p class="page-subtitle">Get help from our support team</p>
    </div>
    <div class="page-header-actions">
        <a href="/tickets/create" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
            New Ticket
        </a>
    </div>
</div>

<!-- Status Filter Tabs -->
<div class="status-tabs mb-6">
    <a href="/tickets" class="status-tab <?= empty($statusFilter) ? 'active' : '' ?>">
        <span class="tab-label">All Tickets</span>
        <span class="tab-count"><?= count($tickets ?? []) ?></span>
    </a>
    <a href="/tickets?status=open" class="status-tab <?= ($statusFilter ?? '') === 'open' ? 'active' : '' ?>">
        <span class="status-dot status-open"></span>
        <span class="tab-label">Open</span>
    </a>
    <a href="/tickets?status=pending" class="status-tab <?= ($statusFilter ?? '') === 'pending' ? 'active' : '' ?>">
        <span class="status-dot status-pending"></span>
        <span class="tab-label">Pending</span>
    </a>
    <a href="/tickets?status=closed" class="status-tab <?= ($statusFilter ?? '') === 'closed' ? 'active' : '' ?>">
        <span class="status-dot status-closed"></span>
        <span class="tab-label">Closed</span>
    </a>
</div>

<?php if (!empty($tickets)): ?>
<div class="tickets-list">
    <?php foreach ($tickets as $ticket): ?>
    <a href="/tickets/<?= (int)$ticket['id'] ?>" class="ticket-card">
        <div class="ticket-header">
            <div class="ticket-status-priority">
                <?php
                $statusClass = match($ticket['status'] ?? 'open') {
                    'open' => 'badge-success',
                    'pending' => 'badge-warning',
                    'closed' => 'badge-gray',
                    default => 'badge-gray'
                };
                ?>
                <span class="badge <?= $statusClass ?>"><?= ucfirst(htmlspecialchars($ticket['status'] ?? 'open')) ?></span>
                
                <?php if (!empty($ticket['priority']) && $ticket['priority'] === 'high'): ?>
                <span class="priority-indicator priority-high" title="High Priority">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path><path d="M12 9v4"></path><path d="M12 17h.01"></path></svg>
                    High Priority
                </span>
                <?php endif; ?>
            </div>
            <span class="ticket-id">#<?= (int)$ticket['id'] ?></span>
        </div>
        
        <div class="ticket-body">
            <h3 class="ticket-subject"><?= htmlspecialchars($ticket['subject'] ?? 'No Subject') ?></h3>
            <p class="ticket-preview"><?= htmlspecialchars(mb_substr($ticket['message'] ?? '', 0, 120)) ?><?= mb_strlen($ticket['message'] ?? '') > 120 ? '...' : '' ?></p>
        </div>
        
        <div class="ticket-footer">
            <div class="ticket-meta">
                <span class="ticket-date">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"></rect><line x1="16" x2="16" y1="2" y2="6"></line><line x1="8" x2="8" y1="2" y2="6"></line><line x1="3" x2="21" y1="10" y2="10"></line></svg>
                    Created <?= htmlspecialchars(date('M d, Y', strtotime($ticket['created_at'] ?? 'now'))) ?>
                </span>
                <?php if (!empty($ticket['last_reply_at'])): ?>
                <span class="ticket-reply">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                    Last reply <?= htmlspecialchars(date('M d', strtotime($ticket['last_reply_at']))) ?>
                </span>
                <?php endif; ?>
            </div>
            <div class="ticket-arrow">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
            </div>
        </div>
    </a>
    <?php endforeach; ?>
</div>
<?php else: ?>
<div class="card">
    <div class="card-body">
        <div class="empty-state">
            <div class="empty-state-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
            </div>
            <h3>No Support Tickets</h3>
            <p>You haven't created any support tickets yet. Need help? Create a ticket and our team will assist you.</p>
            <a href="/tickets/create" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                Create Your First Ticket
            </a>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
/* Status Tabs */
.status-tabs {
    display: flex;
    gap: var(--space-2);
    border-bottom: 1px solid var(--border-color);
    padding-bottom: var(--space-1);
    overflow-x: auto;
}

.status-tab {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    padding: var(--space-2) var(--space-4);
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-medium);
    color: var(--text-secondary);
    border-radius: var(--radius-md) var(--radius-md) 0 0;
    white-space: nowrap;
    transition: all var(--transition-fast);
}

.status-tab:hover {
    color: var(--text-primary);
    background: var(--bg-tertiary);
}

.status-tab.active {
    color: var(--color-primary);
    background: var(--color-primary-light);
    border-bottom: 2px solid var(--color-primary);
    margin-bottom: -1px;
}

.status-dot {
    width: 8px;
    height: 8px;
    border-radius: var(--radius-full);
}

.status-dot.status-open { background: var(--color-success); }
.status-dot.status-pending { background: var(--color-warning); }
.status-dot.status-closed { background: var(--color-gray-400); }

.tab-count {
    background: var(--bg-tertiary);
    padding: var(--space-1) var(--space-2);
    border-radius: var(--radius-full);
    font-size: var(--font-size-xs);
}

/* Tickets List */
.tickets-list {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
}

/* Ticket Card */
.ticket-card {
    display: block;
    background: var(--surface-primary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: var(--space-5);
    transition: all var(--transition-fast);
}

.ticket-card:hover {
    border-color: var(--color-primary-light);
    box-shadow: var(--shadow-md);
    transform: translateY(-2px);
}

.ticket-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: var(--space-3);
}

.ticket-status-priority {
    display: flex;
    align-items: center;
    gap: var(--space-2);
}

.priority-indicator {
    display: flex;
    align-items: center;
    gap: var(--space-1);
    font-size: var(--font-size-xs);
    font-weight: var(--font-weight-medium);
}

.priority-indicator.priority-high {
    color: var(--color-error);
}

.ticket-id {
    font-size: var(--font-size-sm);
    color: var(--text-muted);
    font-family: var(--font-mono);
}

.ticket-body {
    margin-bottom: var(--space-4);
}

.ticket-subject {
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-semibold);
    color: var(--text-primary);
    margin: 0 0 var(--space-2) 0;
}

.ticket-preview {
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
    margin: 0;
    line-height: var(--line-height-relaxed);
}

.ticket-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: var(--space-4);
    border-top: 1px solid var(--border-light);
}

.ticket-meta {
    display: flex;
    align-items: center;
    gap: var(--space-4);
}

.ticket-date,
.ticket-reply {
    display: flex;
    align-items: center;
    gap: var(--space-1);
    font-size: var(--font-size-xs);
    color: var(--text-muted);
}

.ticket-arrow {
    color: var(--text-muted);
    transition: transform var(--transition-fast);
}

.ticket-card:hover .ticket-arrow {
    transform: translateX(4px);
    color: var(--color-primary);
}

/* Empty State */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: var(--space-12) var(--space-6);
    text-align: center;
}

.empty-state-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 80px;
    height: 80px;
    margin-bottom: var(--space-6);
    background-color: var(--bg-tertiary);
    border-radius: var(--radius-full);
    color: var(--text-muted);
}

.empty-state h3 {
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-semibold);
    color: var(--text-primary);
    margin: 0 0 var(--space-2) 0;
}

.empty-state p {
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
    margin: 0 0 var(--space-6) 0;
    max-width: 400px;
}

/* Responsive */
@media (max-width: 639px) {
    .status-tabs {
        gap: 0;
    }
    
    .status-tab {
        padding: var(--space-2) var(--space-3);
    }
    
    .tab-label {
        display: none;
    }
    
    .ticket-meta {
        flex-direction: column;
        align-items: flex-start;
        gap: var(--space-1);
    }
}
</style>
