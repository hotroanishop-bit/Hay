<?php
/**
 * Admin Tickets Page
 * Variables: $pageTitle, $currentPage, $tickets, $statusFilter, $statusCounts, $pagination
 */
?>

<div class="page-header">
    <div class="page-header-content">
        <h1>Ticket Management</h1>
        <p>Manage support tickets</p>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="filter-tabs">
            <a href="/admin/tickets" class="filter-tab <?= empty($statusFilter) ? 'active' : '' ?>">
                All <span class="badge"><?= number_format(($statusCounts['open'] ?? 0) + ($statusCounts['pending'] ?? 0) + ($statusCounts['closed'] ?? 0)) ?></span>
            </a>
            <a href="/admin/tickets?status=open" class="filter-tab <?= $statusFilter === 'open' ? 'active' : '' ?>">
                Open <span class="badge badge-danger"><?= number_format($statusCounts['open'] ?? 0) ?></span>
            </a>
            <a href="/admin/tickets?status=pending" class="filter-tab <?= $statusFilter === 'pending' ? 'active' : '' ?>">
                Pending <span class="badge badge-warning"><?= number_format($statusCounts['pending'] ?? 0) ?></span>
            </a>
            <a href="/admin/tickets?status=closed" class="filter-tab <?= $statusFilter === 'closed' ? 'active' : '' ?>">
                Closed <span class="badge badge-success"><?= number_format($statusCounts['closed'] ?? 0) ?></span>
            </a>
        </div>
    </div>
    <div class="card-body">
        <?php if (!empty($tickets)): ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Subject</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tickets as $ticket): ?>
                    <tr>
                        <td>#<?= (int)($ticket['id'] ?? 0) ?></td>
                        <td>
                            <div class="user-info">
                                <strong><?= htmlspecialchars($ticket['user_name'] ?? 'Unknown') ?></strong>
                                <small class="text-muted d-block"><?= htmlspecialchars($ticket['user_email'] ?? '') ?></small>
                            </div>
                        </td>
                        <td>
                            <a href="/admin/tickets/<?= (int)$ticket['id'] ?>" class="ticket-subject">
                                <?= htmlspecialchars($ticket['subject'] ?? '') ?>
                            </a>
                        </td>
                        <td>
                            <?php
                            $priorityClass = 'secondary';
                            switch ($ticket['priority'] ?? '') {
                                case 'high': $priorityClass = 'danger'; break;
                                case 'medium': $priorityClass = 'warning'; break;
                                case 'low': $priorityClass = 'info'; break;
                            }
                            ?>
                            <span class="badge badge-<?= $priorityClass ?>"><?= ucfirst(htmlspecialchars($ticket['priority'] ?? 'medium')) ?></span>
                        </td>
                        <td>
                            <?php
                            $statusClass = 'secondary';
                            switch ($ticket['status'] ?? '') {
                                case 'open': $statusClass = 'danger'; break;
                                case 'pending': $statusClass = 'warning'; break;
                                case 'closed': $statusClass = 'success'; break;
                            }
                            ?>
                            <span class="badge badge-<?= $statusClass ?>"><?= ucfirst(htmlspecialchars($ticket['status'] ?? '')) ?></span>
                        </td>
                        <td><?= htmlspecialchars(date('M d, Y H:i', strtotime($ticket['created_at'] ?? 'now'))) ?></td>
                        <td>
                            <div class="btn-group">
                                <a href="/admin/tickets/<?= (int)$ticket['id'] ?>" class="btn btn-sm btn-primary" title="View & Reply">
                                    <i class="icon-message-square"></i>
                                </a>
                                <?php if (($ticket['status'] ?? '') !== 'closed'): ?>
                                <form action="/admin/tickets/<?= (int)$ticket['id'] ?>/close" method="POST" class="d-inline" onsubmit="return confirm('Close this ticket?');">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                    <button type="submit" class="btn btn-sm btn-secondary" title="Close">
                                        <i class="icon-check"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
        <nav class="pagination-wrapper">
            <ul class="pagination">
                <?php 
                $queryParams = $statusFilter ? '&status=' . urlencode($statusFilter) : '';
                ?>
                <?php if ($pagination['current_page'] > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="/admin/tickets?page=<?= $pagination['current_page'] - 1 ?><?= $queryParams ?>">Previous</a>
                </li>
                <?php endif; ?>

                <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['total_pages'], $pagination['current_page'] + 2); $i++): ?>
                <li class="page-item <?= $i === $pagination['current_page'] ? 'active' : '' ?>">
                    <a class="page-link" href="/admin/tickets?page=<?= $i ?><?= $queryParams ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>

                <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                <li class="page-item">
                    <a class="page-link" href="/admin/tickets?page=<?= $pagination['current_page'] + 1 ?><?= $queryParams ?>">Next</a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>

        <?php else: ?>
        <p class="text-muted text-center py-4">No tickets found</p>
        <?php endif; ?>
    </div>
</div>
