<?php
/**
 * Admin Tickets Page - Enhanced
 * Modern ticket management with priority indicators and quick actions
 * 
 * Variables: $pageTitle, $currentPage, $tickets, $statusFilter, $statusCounts, $pagination
 */
?>

<div class="page-header">
    <div class="page-header-content">
        <div>
            <h1>Ticket Management</h1>
            <p class="text-muted">Manage and respond to support tickets</p>
        </div>
        <div class="page-header-stats">
            <?php if (($statusCounts['open'] ?? 0) > 0): ?>
            <div class="header-stat header-stat-danger">
                <i class="icon-alert-circle"></i>
                <span><?= number_format($statusCounts['open']) ?> open</span>
            </div>
            <?php endif; ?>
            <?php if (($statusCounts['pending'] ?? 0) > 0): ?>
            <div class="header-stat header-stat-warning">
                <i class="icon-clock"></i>
                <span><?= number_format($statusCounts['pending']) ?> pending</span>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Filter Tabs -->
<div class="filter-toolbar">
    <div class="filter-tabs">
        <a href="/admin/tickets" class="filter-tab <?= empty($statusFilter) ? 'active' : '' ?>">
            <i class="icon-inbox"></i>
            All
            <span class="tab-badge"><?= number_format(($statusCounts['open'] ?? 0) + ($statusCounts['pending'] ?? 0) + ($statusCounts['closed'] ?? 0)) ?></span>
        </a>
        <a href="/admin/tickets?status=open" class="filter-tab filter-tab-danger <?= $statusFilter === 'open' ? 'active' : '' ?>">
            <i class="icon-alert-circle"></i>
            Open
            <span class="tab-badge badge-danger"><?= number_format($statusCounts['open'] ?? 0) ?></span>
        </a>
        <a href="/admin/tickets?status=pending" class="filter-tab filter-tab-warning <?= $statusFilter === 'pending' ? 'active' : '' ?>">
            <i class="icon-clock"></i>
            Pending
            <span class="tab-badge badge-warning"><?= number_format($statusCounts['pending'] ?? 0) ?></span>
        </a>
        <a href="/admin/tickets?status=closed" class="filter-tab filter-tab-success <?= $statusFilter === 'closed' ? 'active' : '' ?>">
            <i class="icon-check-circle"></i>
            Closed
            <span class="tab-badge badge-success"><?= number_format($statusCounts['closed'] ?? 0) ?></span>
        </a>
    </div>
    
    <div class="filter-actions">
        <select class="form-input form-input-sm" onchange="filterByPriority(this.value)">
            <option value="">All Priorities</option>
            <option value="high" <?= ($_GET['priority'] ?? '') === 'high' ? 'selected' : '' ?>>High Priority</option>
            <option value="medium" <?= ($_GET['priority'] ?? '') === 'medium' ? 'selected' : '' ?>>Medium Priority</option>
            <option value="low" <?= ($_GET['priority'] ?? '') === 'low' ? 'selected' : '' ?>>Low Priority</option>
        </select>
    </div>
</div>

<!-- Tickets List -->
<div class="tickets-container">
    <?php if (!empty($tickets)): ?>
    
    <div class="tickets-list">
        <?php foreach ($tickets as $ticket): ?>
        <?php
        $statusClass = 'gray';
        $statusIcon = 'icon-circle';
        switch ($ticket['status'] ?? '') {
            case 'open': 
                $statusClass = 'danger'; 
                $statusIcon = 'icon-alert-circle';
                break;
            case 'pending': 
                $statusClass = 'warning'; 
                $statusIcon = 'icon-clock';
                break;
            case 'closed': 
                $statusClass = 'success'; 
                $statusIcon = 'icon-check-circle';
                break;
        }
        
        $priorityClass = 'low';
        switch ($ticket['priority'] ?? 'medium') {
            case 'high': $priorityClass = 'high'; break;
            case 'medium': $priorityClass = 'medium'; break;
            case 'low': $priorityClass = 'low'; break;
        }
        ?>
        <div class="ticket-card priority-<?= $priorityClass ?>">
            <div class="ticket-priority-indicator"></div>
            
            <div class="ticket-content">
                <div class="ticket-header">
                    <div class="ticket-info">
                        <span class="ticket-id">#<?= (int)($ticket['id'] ?? 0) ?></span>
                        <h3 class="ticket-subject">
                            <a href="/admin/tickets/<?= (int)$ticket['id'] ?>"><?= htmlspecialchars($ticket['subject'] ?? 'No Subject') ?></a>
                        </h3>
                    </div>
                    <div class="ticket-badges">
                        <span class="badge badge-priority-<?= $priorityClass ?>">
                            <?= ucfirst($priorityClass) ?>
                        </span>
                        <span class="badge badge-<?= $statusClass ?>">
                            <i class="<?= $statusIcon ?>"></i>
                            <?= ucfirst(htmlspecialchars($ticket['status'] ?? 'unknown')) ?>
                        </span>
                    </div>
                </div>
                
                <div class="ticket-body">
                    <div class="ticket-user">
                        <div class="user-avatar-sm">
                            <?php if (!empty($ticket['user_avatar'])): ?>
                            <img src="<?= htmlspecialchars($ticket['user_avatar']) ?>" alt="">
                            <?php else: ?>
                            <?= strtoupper(substr($ticket['user_name'] ?? 'U', 0, 1)) ?>
                            <?php endif; ?>
                        </div>
                        <div class="user-info">
                            <span class="user-name"><?= htmlspecialchars($ticket['user_name'] ?? 'Unknown') ?></span>
                            <span class="user-email"><?= htmlspecialchars($ticket['user_email'] ?? '') ?></span>
                        </div>
                    </div>
                    
                    <div class="ticket-meta">
                        <span class="meta-item" title="Created">
                            <i class="icon-calendar"></i>
                            <?= date('M d, Y', strtotime($ticket['created_at'] ?? 'now')) ?>
                        </span>
                        <span class="meta-item" title="Time">
                            <i class="icon-clock"></i>
                            <?= date('H:i', strtotime($ticket['created_at'] ?? 'now')) ?>
                        </span>
                    </div>
                </div>
                
                <div class="ticket-actions">
                    <a href="/admin/tickets/<?= (int)$ticket['id'] ?>" class="btn btn-sm btn-primary">
                        <i class="icon-message-square"></i> View & Reply
                    </a>
                    <?php if (($ticket['status'] ?? '') !== 'closed'): ?>
                    <form action="/admin/tickets/<?= (int)$ticket['id'] ?>/close" method="POST" class="d-inline" 
                          onsubmit="return confirm('Close this ticket?');">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <button type="submit" class="btn btn-sm btn-secondary">
                            <i class="icon-check"></i> Close
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
    <div class="pagination-wrapper">
        <div class="pagination-info">
            Showing <?= (($pagination['current_page'] - 1) * $pagination['per_page']) + 1 ?> 
            to <?= min($pagination['current_page'] * $pagination['per_page'], $pagination['total']) ?> 
            of <?= number_format($pagination['total']) ?> tickets
        </div>
        <nav class="pagination-nav">
            <ul class="pagination">
                <?php 
                $queryParams = $statusFilter ? '&status=' . urlencode($statusFilter) : '';
                ?>
                <?php if ($pagination['current_page'] > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="/admin/tickets?page=<?= $pagination['current_page'] - 1 ?><?= $queryParams ?>">
                        <i class="icon-chevron-left"></i>
                    </a>
                </li>
                <?php endif; ?>

                <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['total_pages'], $pagination['current_page'] + 2); $i++): ?>
                <li class="page-item <?= $i === $pagination['current_page'] ? 'active' : '' ?>">
                    <a class="page-link" href="/admin/tickets?page=<?= $i ?><?= $queryParams ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>

                <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                <li class="page-item">
                    <a class="page-link" href="/admin/tickets?page=<?= $pagination['current_page'] + 1 ?><?= $queryParams ?>">
                        <i class="icon-chevron-right"></i>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <div class="empty-state">
        <div class="empty-state-icon">
            <i class="icon-message-square"></i>
        </div>
        <h3>No tickets found</h3>
        <p>There are no tickets matching your current filter.</p>
        <?php if ($statusFilter): ?>
        <a href="/admin/tickets" class="btn btn-primary">View All Tickets</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<style>
.page-header-stats {
    display: flex;
    gap: var(--spacing-3);
}

.header-stat {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    padding: var(--spacing-2) var(--spacing-3);
    border-radius: var(--radius-full);
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-medium);
}

.header-stat-danger {
    background: rgba(239, 68, 68, 0.1);
    color: var(--color-error);
}

.header-stat-warning {
    background: rgba(245, 158, 11, 0.1);
    color: var(--color-warning);
}

.filter-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-6);
    flex-wrap: wrap;
    gap: var(--spacing-3);
}

.filter-tabs {
    display: flex;
    gap: var(--spacing-2);
    background: var(--color-gray-100);
    padding: var(--spacing-1);
    border-radius: var(--radius-lg);
}

.filter-tab {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    padding: var(--spacing-2) var(--spacing-4);
    font-size: var(--font-size-sm);
    color: var(--color-gray-600);
    text-decoration: none;
    border-radius: var(--radius-md);
    transition: all var(--transition-fast);
}

.filter-tab:hover {
    color: var(--color-gray-900);
    background: var(--color-white);
}

.filter-tab.active {
    background: var(--color-white);
    color: var(--color-primary);
    box-shadow: var(--shadow-sm);
}

.filter-tab-danger.active {
    color: var(--color-error);
}

.filter-tab-warning.active {
    color: #b45309;
}

.filter-tab-success.active {
    color: #059669;
}

.tab-badge {
    font-size: var(--font-size-xs);
    padding: 2px 8px;
    background: var(--color-gray-200);
    border-radius: var(--radius-full);
}

.filter-tab.active .tab-badge {
    background: currentColor;
    color: var(--color-white);
}

.form-input-sm {
    padding: var(--spacing-2) var(--spacing-3);
    font-size: var(--font-size-sm);
    min-width: 150px;
}

.tickets-list {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-3);
}

.ticket-card {
    display: flex;
    background: var(--color-white);
    border: 1px solid var(--color-gray-200);
    border-radius: var(--radius-lg);
    overflow: hidden;
    transition: all var(--transition-fast);
}

.ticket-card:hover {
    box-shadow: var(--shadow-md);
}

.ticket-priority-indicator {
    width: 4px;
    flex-shrink: 0;
}

.priority-high .ticket-priority-indicator {
    background: var(--color-error);
}

.priority-medium .ticket-priority-indicator {
    background: var(--color-warning);
}

.priority-low .ticket-priority-indicator {
    background: var(--color-gray-300);
}

.ticket-content {
    flex: 1;
    padding: var(--spacing-4);
    display: flex;
    flex-direction: column;
    gap: var(--spacing-3);
}

.ticket-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: var(--spacing-3);
}

.ticket-info {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-1);
    min-width: 0;
}

.ticket-id {
    font-size: var(--font-size-xs);
    color: var(--color-gray-400);
    font-weight: var(--font-weight-medium);
}

.ticket-subject {
    margin: 0;
    font-size: var(--font-size-base);
    font-weight: var(--font-weight-semibold);
}

.ticket-subject a {
    color: var(--color-gray-900);
    text-decoration: none;
}

.ticket-subject a:hover {
    color: var(--color-primary);
}

.ticket-badges {
    display: flex;
    gap: var(--spacing-2);
    flex-shrink: 0;
}

.badge-priority-high {
    background: rgba(239, 68, 68, 0.1);
    color: var(--color-error);
}

.badge-priority-medium {
    background: rgba(245, 158, 11, 0.1);
    color: #b45309;
}

.badge-priority-low {
    background: var(--color-gray-100);
    color: var(--color-gray-600);
}

.ticket-body {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: var(--spacing-4);
}

.ticket-user {
    display: flex;
    align-items: center;
    gap: var(--spacing-3);
}

.user-avatar-sm {
    width: 36px;
    height: 36px;
    border-radius: var(--radius-full);
    background: var(--color-primary);
    color: var(--color-white);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: var(--font-weight-semibold);
    font-size: var(--font-size-sm);
    overflow: hidden;
}

.user-avatar-sm img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.ticket-user .user-info {
    display: flex;
    flex-direction: column;
}

.ticket-user .user-name {
    font-weight: var(--font-weight-medium);
    color: var(--color-gray-900);
    font-size: var(--font-size-sm);
}

.ticket-user .user-email {
    font-size: var(--font-size-xs);
    color: var(--color-gray-500);
}

.ticket-meta {
    display: flex;
    gap: var(--spacing-4);
}

.meta-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-1);
    font-size: var(--font-size-xs);
    color: var(--color-gray-400);
}

.ticket-actions {
    display: flex;
    gap: var(--spacing-2);
    padding-top: var(--spacing-3);
    border-top: 1px solid var(--color-gray-100);
}

.pagination-wrapper {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: var(--spacing-6);
    padding: var(--spacing-4);
    background: var(--color-white);
    border-radius: var(--radius-lg);
    border: 1px solid var(--color-gray-200);
}

.pagination-info {
    font-size: var(--font-size-sm);
    color: var(--color-gray-500);
}

@media (max-width: 768px) {
    .filter-toolbar {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filter-tabs {
        overflow-x: auto;
        justify-content: flex-start;
    }
    
    .ticket-header {
        flex-direction: column;
    }
    
    .ticket-badges {
        align-self: flex-start;
    }
    
    .ticket-body {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .ticket-actions {
        width: 100%;
        flex-wrap: wrap;
    }
}
</style>

<script>
function filterByPriority(priority) {
    var url = new URL(window.location.href);
    if (priority) {
        url.searchParams.set('priority', priority);
    } else {
        url.searchParams.delete('priority');
    }
    window.location.href = url.toString();
}
</script>
