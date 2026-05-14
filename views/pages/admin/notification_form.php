<?php
/**
 * Admin Send Notification Form Page
 * Variables: $pageTitle, $currentPage, $users
 */
?>

<div class="page-header">
    <div class="page-header-content">
        <h1>Send Notification</h1>
        <p>Send a notification to a specific user or broadcast to all users</p>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="/admin/notifications" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

            <div class="form-group">
                <label for="title">Title <span class="text-danger">*</span></label>
                <input type="text" id="title" name="title" class="form-control" required
                       placeholder="Notification title">
            </div>

            <div class="form-group">
                <label for="message">Message <span class="text-danger">*</span></label>
                <textarea id="message" name="message" class="form-control" rows="5" required
                          placeholder="Notification message content"></textarea>
            </div>

            <div class="form-group">
                <label for="type">Type</label>
                <select id="type" name="type" class="form-control">
                    <option value="info">Info (Blue)</option>
                    <option value="success">Success (Green)</option>
                    <option value="warning">Warning (Yellow)</option>
                    <option value="error">Error (Red)</option>
                </select>
            </div>

            <div class="form-group">
                <div class="form-check">
                    <input type="checkbox" id="is_broadcast" name="is_broadcast" class="form-check-input">
                    <label for="is_broadcast" class="form-check-label">
                        <strong>Broadcast to All Users</strong>
                    </label>
                    <small class="form-text text-muted">Send this notification to all users at once</small>
                </div>
            </div>

            <div class="form-group" id="user-select-group">
                <label for="user_id">Select User</label>
                <select id="user_id" name="user_id" class="form-control">
                    <option value="">-- Select a user --</option>
                    <?php foreach ($users as $user): ?>
                    <option value="<?= (int)$user['id'] ?>">
                        <?= htmlspecialchars($user['name'] ?? 'User #' . $user['id']) ?> 
                        (<?= htmlspecialchars($user['email'] ?? '') ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
                <small class="form-text text-muted">Choose a specific user to receive this notification</small>
            </div>

            <div class="alert alert-info" id="broadcast-info" style="display: none;">
                <i class="icon-info"></i>
                <strong>Broadcast Mode:</strong> This notification will be sent to all users.
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="icon-send"></i> Send Notification
                </button>
                <a href="/admin/notifications" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
// Toggle user selection based on broadcast checkbox
var broadcastCheckbox = document.getElementById('is_broadcast');
var userSelectGroup = document.getElementById('user-select-group');
var userSelect = document.getElementById('user_id');
var broadcastInfo = document.getElementById('broadcast-info');

broadcastCheckbox.addEventListener('change', function() {
    if (this.checked) {
        userSelectGroup.style.display = 'none';
        userSelect.value = '';
        broadcastInfo.style.display = 'block';
    } else {
        userSelectGroup.style.display = 'block';
        broadcastInfo.style.display = 'none';
    }
});

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    if (!broadcastCheckbox.checked && !userSelect.value) {
        e.preventDefault();
        alert('Please select a user or enable broadcast mode.');
        userSelect.focus();
    }
});
</script>
