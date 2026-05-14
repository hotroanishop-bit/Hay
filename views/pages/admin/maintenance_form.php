<?php
/**
 * Admin Scheduled Maintenance Form (Create/Edit)
 * Variables: $pageTitle, $currentPage, $maintenance (optional for edit), $isEdit
 */

$isEdit = isset($maintenance) && $maintenance;
$formAction = $isEdit ? "/admin/maintenance/{$maintenance['id']}/update" : "/admin/maintenance";
$formTitle = $isEdit ? "Edit Maintenance Window" : "Schedule New Maintenance";

// Default values
$title = $maintenance['title'] ?? '';
$message = $maintenance['message'] ?? 'We are performing scheduled maintenance to improve our services. Please check back soon.';
$startsAt = $maintenance['starts_at'] ?? date('Y-m-d\TH:i', strtotime('+1 hour'));
$endsAt = $maintenance['ends_at'] ?? date('Y-m-d\TH:i', strtotime('+2 hours'));
$isActive = isset($maintenance['is_active']) ? $maintenance['is_active'] : 1;
$showCountdown = isset($maintenance['show_countdown']) ? $maintenance['show_countdown'] : 1;
?>

<div class="page-header">
    <div class="page-header-content">
        <h1><?= htmlspecialchars($formTitle) ?></h1>
        <p><?= $isEdit ? 'Update the maintenance window details' : 'Schedule a new maintenance window' ?></p>
    </div>
    <div class="page-header-actions">
        <a href="/admin/maintenance" class="btn btn-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Back to List
        </a>
    </div>
</div>

<?php if (!empty($flash)): ?>
<div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
    <?= htmlspecialchars($flash['message']) ?>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3><?= $isEdit ? 'Edit' : 'Create' ?> Maintenance Window</h3>
    </div>
    <div class="card-body">
        <form action="<?= htmlspecialchars($formAction) ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            
            <div class="form-group">
                <label for="title">Title <span class="text-danger">*</span></label>
                <input type="text" id="title" name="title" class="form-control" 
                       value="<?= htmlspecialchars($title) ?>" 
                       placeholder="e.g., Scheduled System Maintenance" required>
                <small class="form-text text-muted">A brief title for the maintenance window</small>
            </div>
            
            <div class="form-group">
                <label for="message">Message <span class="text-danger">*</span></label>
                <textarea id="message" name="message" class="form-control" rows="4" 
                          placeholder="Describe what maintenance is being performed..." required><?= htmlspecialchars($message) ?></textarea>
                <small class="form-text text-muted">This message will be shown to users during maintenance</small>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="starts_at">Starts At <span class="text-danger">*</span></label>
                        <input type="datetime-local" id="starts_at" name="starts_at" class="form-control" 
                               value="<?= htmlspecialchars(date('Y-m-d\TH:i', strtotime($startsAt))) ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="ends_at">Ends At <span class="text-danger">*</span></label>
                        <input type="datetime-local" id="ends_at" name="ends_at" class="form-control" 
                               value="<?= htmlspecialchars(date('Y-m-d\TH:i', strtotime($endsAt))) ?>" required>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <div class="form-check">
                            <input type="checkbox" id="is_active" name="is_active" class="form-check-input" 
                                   value="1" <?= $isActive ? 'checked' : '' ?>>
                            <label for="is_active" class="form-check-label">
                                Active (maintenance will trigger when time comes)
                            </label>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">Display Options</label>
                        <div class="form-check">
                            <input type="checkbox" id="show_countdown" name="show_countdown" class="form-check-input" 
                                   value="1" <?= $showCountdown ? 'checked' : '' ?>>
                            <label for="show_countdown" class="form-check-label">
                                Show countdown timer on maintenance page
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <?= $isEdit ? 'Update Maintenance' : 'Schedule Maintenance' ?>
                </button>
                <a href="/admin/maintenance" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
// Validate end time is after start time
document.querySelector('form').addEventListener('submit', function(e) {
    const startsAt = new Date(document.getElementById('starts_at').value);
    const endsAt = new Date(document.getElementById('ends_at').value);
    
    if (endsAt <= startsAt) {
        e.preventDefault();
        alert('End time must be after start time');
    }
});
</script>
