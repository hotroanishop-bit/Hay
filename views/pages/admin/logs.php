<?php
/**
 * Admin Audit Logs Page
 * Variables: $pageTitle, $currentPage, $logs, $admins, $actionTypes, $filters, $pagination
 */
?>

<div class="page-header">
    <div class="page-header-content">
        <h1>Audit Logs</h1>
        <p>View admin activity and system logs</p>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h3>Filters</h3>
    </div>
    <div class="card-body">
        <form action="/admin/logs" method="GET" class="filter-form">
            <div class="filter-row">
                <div class="form-group">
                    <label for="admin_id">Admin</label>
                    <select name="admin_id" id="admin_id" class="form-control">
                        <option value="">All Admins</option>
                        <?php foreach ($admins ?? [] as $admin): ?>
                        <option value="<?= (int)$admin['id'] ?>" <?= ($filters['admin_id'] ?? '') == $admin['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($admin['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="action">Action Type</label>
                    <select name="action" id="action" class="form-control">
                        <option value="">All Actions</option>
                        <?php foreach ($actionTypes ?? [] as $actionType): ?>
                        <option value="<?= htmlspecialchars($actionType) ?>" <?= ($filters['action'] ?? '') == $actionType ? 'selected' : '' ?>>
                            <?= htmlspecialchars($actionType) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="date_from">From Date</label>
                    <input type="date" name="date_from" id="date_from" class="form-control" value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="date_to">To Date</label>
                    <input type="date" name="date_to" id="date_to" class="form-control" value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>">
                </div>
                <div class="form-group form-group-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="icon-filter"></i> Filter
                    </button>
                    <a href="/admin/logs" class="btn btn-secondary">Clear</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Logs</h3>
        <span class="text-muted"><?= number_format($pagination['total'] ?? 0) ?> entries</span>
    </div>
    <div class="card-body">
        <?php if (!empty($logs)): ?>
        <div class="table-responsive">
            <table class="table table-hover logs-table">
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>Admin</th>
                        <th>Action</th>
                        <th>Target</th>
                        <th>Details</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                    <tr class="log-row" onclick="toggleLogDetails(this)">
                        <td>
                            <span class="log-timestamp"><?= htmlspecialchars(date('M d, Y H:i:s', strtotime($log['created_at'] ?? 'now'))) ?></span>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($log['admin_name'] ?? 'System') ?></strong>
                        </td>
                        <td>
                            <code class="log-action"><?= htmlspecialchars($log['action'] ?? '') ?></code>
                        </td>
                        <td>
                            <?php if (!empty($log['target_type'])): ?>
                            <span class="badge badge-secondary"><?= htmlspecialchars($log['target_type']) ?></span>
                            #<?= (int)($log['target_id'] ?? 0) ?>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($log['new_value'])): ?>
                            <span class="log-details-preview" title="Click to expand">
                                <?php 
                                $details = is_array($log['new_value']) ? json_encode($log['new_value']) : $log['new_value'];
                                echo htmlspecialchars(strlen($details) > 50 ? substr($details, 0, 50) . '...' : $details);
                                ?>
                            </span>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <code class="log-ip"><?= htmlspecialchars($log['ip_address'] ?? 'unknown') ?></code>
                        </td>
                    </tr>
                    <?php if (!empty($log['old_value']) || !empty($log['new_value'])): ?>
                    <tr class="log-details-row" style="display: none;">
                        <td colspan="6">
                            <div class="log-details-content">
                                <?php if (!empty($log['old_value'])): ?>
                                <div class="detail-section">
                                    <strong>Old Value:</strong>
                                    <pre><?= htmlspecialchars(is_array($log['old_value']) ? json_encode($log['old_value'], JSON_PRETTY_PRINT) : $log['old_value']) ?></pre>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($log['new_value'])): ?>
                                <div class="detail-section">
                                    <strong>New Value:</strong>
                                    <pre><?= htmlspecialchars(is_array($log['new_value']) ? json_encode($log['new_value'], JSON_PRETTY_PRINT) : $log['new_value']) ?></pre>
                                </div>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
        <nav class="pagination-wrapper">
            <ul class="pagination">
                <?php 
                $queryParams = http_build_query(array_filter($filters ?? []));
                $queryParams = $queryParams ? '&' . $queryParams : '';
                ?>
                <?php if ($pagination['current_page'] > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="/admin/logs?page=<?= $pagination['current_page'] - 1 ?><?= $queryParams ?>">Previous</a>
                </li>
                <?php endif; ?>

                <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['total_pages'], $pagination['current_page'] + 2); $i++): ?>
                <li class="page-item <?= $i === $pagination['current_page'] ? 'active' : '' ?>">
                    <a class="page-link" href="/admin/logs?page=<?= $i ?><?= $queryParams ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>

                <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                <li class="page-item">
                    <a class="page-link" href="/admin/logs?page=<?= $pagination['current_page'] + 1 ?><?= $queryParams ?>">Next</a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>

        <?php else: ?>
        <p class="text-muted text-center py-4">No audit logs found</p>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleLogDetails(row) {
    var detailsRow = row.nextElementSibling;
    if (detailsRow && detailsRow.classList.contains('log-details-row')) {
        detailsRow.style.display = detailsRow.style.display === 'none' ? 'table-row' : 'none';
    }
}
</script>
