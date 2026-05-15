<?php
/**
 * Admin Tickets Index Page
 * Variables: $pageTitle, $currentPage, $tickets, $total, $page, $totalPages, 
 *            $filters, $stats, $admins, $statuses, $priorities, $categories
 */
?>

<div class="page-header page-header-flex">
    <div class="page-header-content">
        <h1 class="page-title">Support Tickets</h1>
        <p class="page-subtitle">Manage customer support tickets</p>
    </div>
</div>

<!-- Stats Cards -->
<div class="stats-grid mb-6">
    <div class="stat-card">
        <div class="stat-icon stat-icon-warning">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 8v4"></path><path d="M12 16h.01"></path></svg>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?= (int)($stats['open'] ?? 0) ?></span>
            <span class="stat-label">Open Tickets</span>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon stat-icon-info">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?= (int)($stats['in_progress'] ?? 0) ?></span>
            <span class="stat-label">In Progress</span>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon stat-icon-primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?= (int)($stats['waiting_reply'] ?? 0) ?></span>
            <span class="stat-label">Waiting Reply</span>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon stat-icon-error">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path><path d="M12 9v4"></path><path d="M12 17h.01"></path></svg>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?= (int)($stats['urgent'] ?? 0) ?></span>
            <span class="stat-label">Urgent</span>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-6">
    <div class="card-body">
        <form action="/admin/tickets" method="GET" class="filters-form">
            <div class="filters-row">
                <div class="filter-group">
                    <label class="filter-label">Search</label>
                    <input type="text" name="search" class="form-input" placeholder="Ticket #, subject, user..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                </div>
                
                <div class="filter-group">
                    <label class="filter-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <?php foreach ($statuses ?? [] as $value => $label): ?>
                        <option value="<?= htmlspecialchars($value) ?>" <?= ($filters['status'] ?? '') === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label class="filter-label">Priority</label>
                    <select name="priority" class="form-select">
                        <option value="">All Priorities</option>
                        <?php foreach ($priorities ?? [] as $value => $label): ?>
                        <option value="<?= htmlspecialchars($value) ?>" <?= ($filters['priority'] ?? '') === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label class="filter-label">Category</label>
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        <?php foreach ($categories ?? [] as $value => $label): ?>
                        <option value="<?= htmlspecialchars($value) ?>" <?= ($filters['category'] ?? '') === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label class="filter-label">Assigned To</label>
                    <select name="assigned_to" class="form-select">
                        <option value="">All Admins</option>
                        <?php foreach ($admins ?? [] as $admin): ?>
                        <option value="<?= (int)$admin['id'] ?>" <?= ($filters['assigned_to'] ?? '') == $admin['id'] ? 'selected' : '' ?>><?= htmlspecialchars($admin['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        Filter
                    </button>
                    <a href="/admin/tickets" class="btn btn-secondary">Reset</a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Tickets Table -->
<div class="card">
    <div class="card-body p-0">
        <?php if (!empty($tickets)): ?>
        <form id="bulkForm" action="/admin/tickets/bulk" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            
            <div class="table-actions">
                <div class="bulk-actions">
                    <label class="checkbox-label">
                        <input type="checkbox" id="selectAll">
                        <span>Select All</span>
                    </label>
                    <select name="action" class="form-select form-select-sm">
                        <option value="">Bulk Actions</option>
                        <option value="close">Close Selected</option>
                        <option value="resolve">Mark Resolved</option>
                        <option value="assign_to_me">Assign to Me</option>
                    </select>
                    <button type="submit" class="btn btn-sm btn-secondary">Apply</button>
                </div>
                <div class="table-info">
                    Showing <?= count($tickets) ?> of <?= (int)$total ?> tickets
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="th-checkbox"><input type="checkbox" id="headerSelectAll"></th>
                            <th>Ticket</th>
                            <th>User</th>
                            <th>Category</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Assigned</th>
                            <th>Last Update</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tickets as $ticket): ?>
                        <tr class="<?= ($ticket['priority'] ?? '') === 'urgent' ? 'row-urgent' : '' ?>">
                            <td><input type="checkbox" name="ticket_ids[]" value="<?= (int)$ticket['id'] ?>" class="ticket-checkbox"></td>
                            <td>
                                <div class="ticket-info">
                                    <a href="/admin/tickets/<?= (int)$ticket['id'] ?>" class="ticket-number"><?= htmlspecialchars($ticket['ticket_number'] ?? '#' . $ticket['id']) ?></a>
                                    <span class="ticket-subject"><?= htmlspecialchars(mb_substr($ticket['subject'] ?? '', 0, 50)) ?><?= mb_strlen($ticket['subject'] ?? '') > 50 ? '...' : '' ?></span>
                                </div>
                            </td>
                            <td>
                                <div class="user-info-cell">
                                    <span class="user-name"><?= htmlspecialchars($ticket['user_name'] ?? 'N/A') ?></span>
                                    <span class="user-email"><?= htmlspecialchars($ticket['user_email'] ?? '') ?></span>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-outline"><?= ucfirst(htmlspecialchars($ticket['category'] ?? 'other')) ?></span>
                            </td>
                            <td>
                                <?php
                                $priorityClass = match($ticket['priority'] ?? 'medium') {
                                    'urgent' => 'badge-error',
                                    'high' => 'badge-warning',
                                    'medium' => 'badge-info',
                                    'low' => 'badge-gray',
                                    default => 'badge-gray'
                                };
                                ?>
                                <span class="badge <?= $priorityClass ?>"><?= ucfirst(htmlspecialchars($ticket['priority'] ?? 'medium')) ?></span>
                            </td>
                            <td>
                                <?php
                                $statusClass = match($ticket['status'] ?? 'open') {
                                    'open' => 'badge-warning',
                                    'in_progress' => 'badge-info',
                                    'waiting_reply' => 'badge-primary',
                                    'resolved' => 'badge-success',
                                    'closed' => 'badge-gray',
                                    default => 'badge-gray'
                                };
                                ?>
                                <span class="badge <?= $statusClass ?>"><?= ucfirst(str_replace('_', ' ', htmlspecialchars($ticket['status'] ?? 'open'))) ?></span>
                            </td>
                            <td>
                                <?php if (!empty($ticket['assigned_name'])): ?>
                                <span class="assigned-badge"><?= htmlspecialchars($ticket['assigned_name']) ?></span>
                                <?php else: ?>
                                <span class="text-muted">Unassigned</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="date-cell" title="<?= htmlspecialchars($ticket['updated_at'] ?? '') ?>">
                                    <?= htmlspecialchars(date('M d, H:i', strtotime($ticket['updated_at'] ?? 'now'))) ?>
                                </span>
                            </td>
                            <td>
                                <a href="/admin/tickets/<?= (int)$ticket['id'] ?>" class="btn btn-sm btn-ghost">View</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </form>
        
        <!-- Pagination -->
        <?php if (($totalPages ?? 1) > 1): ?>
        <div class="pagination-wrapper">
            <nav class="pagination">
                <?php
                $queryParams = $filters;
                $buildUrl = function($p) use ($queryParams) {
                    $queryParams['page'] = $p;
                    return '/admin/tickets?' . http_build_query(array_filter($queryParams));
                };
                ?>
                
                <?php if ($page > 1): ?>
                <a href="<?= $buildUrl(1) ?>" class="pagination-link">&laquo;</a>
                <a href="<?= $buildUrl($page - 1) ?>" class="pagination-link">&lsaquo;</a>
                <?php endif; ?>
                
                <?php
                $start = max(1, $page - 2);
                $end = min($totalPages, $page + 2);
                for ($i = $start; $i <= $end; $i++):
                ?>
                <a href="<?= $buildUrl($i) ?>" class="pagination-link <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                <a href="<?= $buildUrl($page + 1) ?>" class="pagination-link">&rsaquo;</a>
                <a href="<?= $buildUrl($totalPages) ?>" class="pagination-link">&raquo;</a>
                <?php endif; ?>
            </nav>
        </div>
        <?php endif; ?>
        
        <?php else: ?>
        <div class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
            </svg>
            <h3>No tickets found</h3>
            <p>There are no tickets matching your filters.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: var(--space-4);
}

.stat-card {
    display: flex;
    align-items: center;
    gap: var(--space-4);
    padding: var(--space-4);
    background: var(--surface-primary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
}

.stat-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: var(--radius-md);
}

.stat-icon-warning { background: var(--color-warning-light); color: var(--color-warning); }
.stat-icon-info { background: var(--color-info-light); color: var(--color-info); }
.stat-icon-primary { background: var(--color-primary-light); color: var(--color-primary); }
.stat-icon-error { background: var(--color-error-light); color: var(--color-error); }

.stat-content {
    display: flex;
    flex-direction: column;
}

.stat-value {
    font-size: var(--font-size-2xl);
    font-weight: var(--font-weight-bold);
    color: var(--text-primary);
}

.stat-label {
    font-size: var(--font-size-sm);
    color: var(--text-muted);
}

/* Filters */
.filters-row {
    display: flex;
    flex-wrap: wrap;
    gap: var(--space-4);
    align-items: flex-end;
}

.filter-group {
    flex: 1;
    min-width: 150px;
}

.filter-label {
    display: block;
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-medium);
    color: var(--text-secondary);
    margin-bottom: var(--space-1);
}

.filter-actions {
    display: flex;
    gap: var(--space-2);
}

/* Table Actions */
.table-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--space-3) var(--space-4);
    background: var(--bg-secondary);
    border-bottom: 1px solid var(--border-color);
}

.bulk-actions {
    display: flex;
    align-items: center;
    gap: var(--space-3);
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    font-size: var(--font-size-sm);
    cursor: pointer;
}

.table-info {
    font-size: var(--font-size-sm);
    color: var(--text-muted);
}

/* Table */
.th-checkbox {
    width: 40px;
}

.ticket-info {
    display: flex;
    flex-direction: column;
    gap: var(--space-1);
}

.ticket-number {
    font-family: var(--font-mono);
    font-weight: var(--font-weight-semibold);
    color: var(--color-primary);
}

.ticket-subject {
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
}

.user-info-cell {
    display: flex;
    flex-direction: column;
}

.user-name {
    font-weight: var(--font-weight-medium);
}

.user-email {
    font-size: var(--font-size-xs);
    color: var(--text-muted);
}

.assigned-badge {
    display: inline-flex;
    align-items: center;
    padding: var(--space-1) var(--space-2);
    background: var(--color-primary-light);
    color: var(--color-primary);
    font-size: var(--font-size-xs);
    font-weight: var(--font-weight-medium);
    border-radius: var(--radius-full);
}

.date-cell {
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
}

.row-urgent {
    background: var(--color-error-light);
}

/* Pagination */
.pagination-wrapper {
    padding: var(--space-4);
    display: flex;
    justify-content: center;
    border-top: 1px solid var(--border-color);
}

.pagination {
    display: flex;
    gap: var(--space-1);
}

.pagination-link {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 32px;
    height: 32px;
    padding: 0 var(--space-2);
    font-size: var(--font-size-sm);
    border-radius: var(--radius-md);
    border: 1px solid var(--border-color);
    color: var(--text-secondary);
    transition: all var(--transition-fast);
}

.pagination-link:hover {
    border-color: var(--color-primary);
    color: var(--color-primary);
}

.pagination-link.active {
    background: var(--color-primary);
    border-color: var(--color-primary);
    color: white;
}

/* Empty State */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: var(--space-12);
    text-align: center;
    color: var(--text-muted);
}

.empty-state svg {
    margin-bottom: var(--space-4);
}

.empty-state h3 {
    margin: 0 0 var(--space-2);
    color: var(--text-primary);
}

.empty-state p {
    margin: 0;
}

/* Responsive */
@media (max-width: 1023px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .filters-row {
        flex-direction: column;
    }
    
    .filter-group {
        width: 100%;
    }
}

@media (max-width: 639px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .table-actions {
        flex-direction: column;
        gap: var(--space-3);
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select all checkbox
    const selectAll = document.getElementById('selectAll');
    const headerSelectAll = document.getElementById('headerSelectAll');
    const checkboxes = document.querySelectorAll('.ticket-checkbox');
    
    function toggleAll(checked) {
        checkboxes.forEach(cb => cb.checked = checked);
    }
    
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            toggleAll(this.checked);
            if (headerSelectAll) headerSelectAll.checked = this.checked;
        });
    }
    
    if (headerSelectAll) {
        headerSelectAll.addEventListener('change', function() {
            toggleAll(this.checked);
            if (selectAll) selectAll.checked = this.checked;
        });
    }
    
    // Bulk form validation
    const bulkForm = document.getElementById('bulkForm');
    if (bulkForm) {
        bulkForm.addEventListener('submit', function(e) {
            const action = this.querySelector('select[name="action"]').value;
            const checked = document.querySelectorAll('.ticket-checkbox:checked');
            
            if (!action) {
                e.preventDefault();
                alert('Please select an action');
                return;
            }
            
            if (checked.length === 0) {
                e.preventDefault();
                alert('Please select at least one ticket');
                return;
            }
            
            if (!confirm(`Apply "${action}" to ${checked.length} ticket(s)?`)) {
                e.preventDefault();
            }
        });
    }
});
</script>
