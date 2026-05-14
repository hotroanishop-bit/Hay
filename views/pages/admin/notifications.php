<?php
/**
 * Admin Notifications Page
 * Variables: $pageTitle, $currentPage, $notifications, $pagination
 */
?>

<div class="page-header">
    <div class="page-header-content">
        <h1>Notifications</h1>
        <p>Manage and send user notifications</p>
    </div>
    <div class="page-header-actions">
        <a href="/admin/notifications/send" class="btn btn-primary">
            <i class="icon-send"></i> Send Notification
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Sent Notifications</h3>
    </div>
    <div class="card-body">
        <?php if (!empty($notifications)): ?>
        <div class="table-responsive">
            <table class="table table-hover admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Recipient</th>
                        <th>Read</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($notifications as $notification): ?>
                    <tr>
                        <td><?= (int)($notification['id'] ?? 0) ?></td>
                        <td>
                            <strong><?= htmlspecialchars($notification['title'] ?? '') ?></strong>
                            <br>
                            <small class="text-muted"><?= htmlspecialchars(mb_substr($notification['message'] ?? '', 0, 50)) ?><?= strlen($notification['message'] ?? '') > 50 ? '...' : '' ?></small>
                        </td>
                        <td>
                            <?php 
                            $type = $notification['type'] ?? 'info';
                            $typeClass = [
                                'info' => 'badge-info',
                                'success' => 'badge-success',
                                'warning' => 'badge-warning',
                                'error' => 'badge-danger'
                            ][$type] ?? 'badge-secondary';
                            ?>
                            <span class="badge <?= $typeClass ?>"><?= htmlspecialchars(ucfirst($type)) ?></span>
                        </td>
                        <td>
                            <?php if (empty($notification['user_id'])): ?>
                            <span class="badge badge-primary">Broadcast (All Users)</span>
                            <?php else: ?>
                            <span><?= htmlspecialchars($notification['user_name'] ?? 'User #' . $notification['user_id']) ?></span>
                            <br>
                            <small class="text-muted"><?= htmlspecialchars($notification['user_email'] ?? '') ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($notification['is_read'])): ?>
                            <span class="badge badge-success">Read</span>
                            <?php else: ?>
                            <span class="badge badge-secondary">Unread</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars(date('M d, Y H:i', strtotime($notification['created_at'] ?? 'now'))) ?></td>
                        <td>
                            <form action="/admin/notifications/<?= (int)$notification['id'] ?>/delete" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this notification?');">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                    <i class="icon-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($pagination['total_pages'] > 1): ?>
        <nav class="pagination-wrapper">
            <ul class="pagination">
                <?php if ($pagination['current_page'] > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $pagination['current_page'] - 1 ?>">Previous</a>
                </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                <li class="page-item <?= $i === $pagination['current_page'] ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>

                <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $pagination['current_page'] + 1 ?>">Next</a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>

        <?php else: ?>
        <p class="text-muted">No notifications found. <a href="/admin/notifications/send">Send your first notification</a>.</p>
        <?php endif; ?>
    </div>
</div>
