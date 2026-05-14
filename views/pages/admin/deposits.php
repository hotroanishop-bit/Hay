<?php
/**
 * Admin Deposits Page
 * Variables: $pageTitle, $currentPage, $deposits, $statusFilter, $statusCounts, $pagination
 */
?>

<div class="page-header">
    <div class="page-header-content">
        <h1>Deposit Management</h1>
        <p>Manage user deposit requests</p>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="filter-tabs">
            <a href="/admin/deposits" class="filter-tab <?= empty($statusFilter) ? 'active' : '' ?>">
                All <span class="badge"><?= number_format($statusCounts['all'] ?? 0) ?></span>
            </a>
            <a href="/admin/deposits?status=pending" class="filter-tab <?= $statusFilter === 'pending' ? 'active' : '' ?>">
                Pending <span class="badge badge-warning"><?= number_format($statusCounts['pending'] ?? 0) ?></span>
            </a>
            <a href="/admin/deposits?status=approved" class="filter-tab <?= $statusFilter === 'approved' ? 'active' : '' ?>">
                Approved <span class="badge badge-success"><?= number_format($statusCounts['approved'] ?? 0) ?></span>
            </a>
            <a href="/admin/deposits?status=rejected" class="filter-tab <?= $statusFilter === 'rejected' ? 'active' : '' ?>">
                Rejected <span class="badge badge-danger"><?= number_format($statusCounts['rejected'] ?? 0) ?></span>
            </a>
            <a href="/admin/deposits?status=expired" class="filter-tab <?= $statusFilter === 'expired' ? 'active' : '' ?>">
                Expired <span class="badge badge-secondary"><?= number_format($statusCounts['expired'] ?? 0) ?></span>
            </a>
        </div>
    </div>
    <div class="card-body">
        <?php if (!empty($deposits)): ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Amount</th>
                        <th>Reference</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($deposits as $deposit): ?>
                    <tr>
                        <td>#<?= (int)($deposit['id'] ?? 0) ?></td>
                        <td>
                            <div class="user-info">
                                <strong><?= htmlspecialchars($deposit['user_name'] ?? 'Unknown') ?></strong>
                                <small class="text-muted d-block"><?= htmlspecialchars($deposit['user_email'] ?? '') ?></small>
                            </div>
                        </td>
                        <td><strong>$<?= number_format($deposit['amount'] ?? 0, 2) ?></strong></td>
                        <td><code><?= htmlspecialchars($deposit['reference_code'] ?? '') ?></code></td>
                        <td>
                            <?php
                            $statusClass = 'secondary';
                            switch ($deposit['status'] ?? '') {
                                case 'pending': $statusClass = 'warning'; break;
                                case 'approved': $statusClass = 'success'; break;
                                case 'rejected': $statusClass = 'danger'; break;
                                case 'expired': $statusClass = 'secondary'; break;
                            }
                            ?>
                            <span class="badge badge-<?= $statusClass ?>"><?= ucfirst(htmlspecialchars($deposit['status'] ?? '')) ?></span>
                        </td>
                        <td><?= htmlspecialchars(date('M d, Y H:i', strtotime($deposit['created_at'] ?? 'now'))) ?></td>
                        <td>
                            <div class="btn-group">
                                <a href="/admin/deposits/<?= (int)$deposit['id'] ?>" class="btn btn-sm btn-secondary" title="View">
                                    <i class="icon-eye"></i>
                                </a>
                                <?php if (($deposit['status'] ?? '') === 'pending'): ?>
                                <form action="/admin/deposits/<?= (int)$deposit['id'] ?>/approve" method="POST" class="d-inline" onsubmit="return confirm('Approve this deposit?');">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                    <button type="submit" class="btn btn-sm btn-success" title="Approve">
                                        <i class="icon-check"></i>
                                    </button>
                                </form>
                                <button type="button" class="btn btn-sm btn-danger" title="Reject" onclick="showRejectModal(<?= (int)$deposit['id'] ?>)">
                                    <i class="icon-x"></i>
                                </button>
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
                    <a class="page-link" href="/admin/deposits?page=<?= $pagination['current_page'] - 1 ?><?= $queryParams ?>">Previous</a>
                </li>
                <?php endif; ?>

                <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['total_pages'], $pagination['current_page'] + 2); $i++): ?>
                <li class="page-item <?= $i === $pagination['current_page'] ? 'active' : '' ?>">
                    <a class="page-link" href="/admin/deposits?page=<?= $i ?><?= $queryParams ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>

                <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                <li class="page-item">
                    <a class="page-link" href="/admin/deposits?page=<?= $pagination['current_page'] + 1 ?><?= $queryParams ?>">Next</a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>

        <?php else: ?>
        <p class="text-muted text-center py-4">No deposits found</p>
        <?php endif; ?>
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="modal" style="display: none;">
    <div class="modal-overlay" onclick="hideRejectModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h4>Reject Deposit</h4>
            <button type="button" class="modal-close" onclick="hideRejectModal()">&times;</button>
        </div>
        <form id="rejectForm" method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <div class="modal-body">
                <div class="form-group">
                    <label for="reason">Rejection Reason (optional)</label>
                    <textarea name="reason" id="reason" class="form-control" rows="3" placeholder="Enter reason for rejection..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="hideRejectModal()">Cancel</button>
                <button type="submit" class="btn btn-danger">Reject Deposit</button>
            </div>
        </form>
    </div>
</div>

<script>
function showRejectModal(depositId) {
    document.getElementById('rejectForm').action = '/admin/deposits/' + depositId + '/reject';
    document.getElementById('rejectModal').style.display = 'flex';
}

function hideRejectModal() {
    document.getElementById('rejectModal').style.display = 'none';
}
</script>
