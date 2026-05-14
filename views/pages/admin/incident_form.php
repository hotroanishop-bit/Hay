<?php
/**
 * Admin Incident Form
 * Variables: $pageTitle, $currentPage, $incident (optional for edit)
 */
$isEdit = isset($incident) && $incident;
?>

<div class="page-header">
    <div class="page-header-content">
        <h1><?= $isEdit ? 'Edit Incident' : 'Report New Incident' ?></h1>
        <p><?= $isEdit ? 'Update incident details and status' : 'Create a new incident report for the status page' ?></p>
    </div>
    <div class="page-header-actions">
        <a href="/admin/incidents" class="btn btn-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            Back to Incidents
        </a>
    </div>
</div>

<form action="<?= $isEdit ? '/admin/incidents/' . (int)$incident['id'] . '/update' : '/admin/incidents' ?>" method="POST">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
    
    <div class="card">
        <div class="card-header">
            <h3>Incident Details</h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label for="title">Title *</label>
                <input type="text" id="title" name="title" value="<?= htmlspecialchars($incident['title'] ?? '') ?>" class="form-control" required placeholder="e.g., API Gateway Degraded Performance">
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="4" placeholder="Describe the incident and its impact..."><?= htmlspecialchars($incident['description'] ?? '') ?></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group form-col">
                    <label for="severity">Severity *</label>
                    <select id="severity" name="severity" class="form-control" required>
                        <option value="minor" <?= ($incident['severity'] ?? '') === 'minor' ? 'selected' : '' ?>>Minor - Limited impact</option>
                        <option value="major" <?= ($incident['severity'] ?? '') === 'major' ? 'selected' : '' ?>>Major - Significant impact</option>
                        <option value="critical" <?= ($incident['severity'] ?? '') === 'critical' ? 'selected' : '' ?>>Critical - Full outage</option>
                    </select>
                </div>
                
                <div class="form-group form-col">
                    <label for="status">Status *</label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="investigating" <?= ($incident['status'] ?? 'investigating') === 'investigating' ? 'selected' : '' ?>>Investigating</option>
                        <option value="identified" <?= ($incident['status'] ?? '') === 'identified' ? 'selected' : '' ?>>Identified</option>
                        <option value="monitoring" <?= ($incident['status'] ?? '') === 'monitoring' ? 'selected' : '' ?>>Monitoring</option>
                        <option value="resolved" <?= ($incident['status'] ?? '') === 'resolved' ? 'selected' : '' ?>>Resolved</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label>Affected Components</label>
                <div class="checkbox-group">
                    <?php 
                    $affectedComponents = [];
                    if ($isEdit && !empty($incident['affected_components'])) {
                        $affectedComponents = json_decode($incident['affected_components'], true) ?: [];
                    }
                    ?>
                    <label class="checkbox-label">
                        <input type="checkbox" name="affected_components[]" value="website" <?= in_array('website', $affectedComponents) ? 'checked' : '' ?>>
                        <span>Website</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="affected_components[]" value="api" <?= in_array('api', $affectedComponents) ? 'checked' : '' ?>>
                        <span>API Gateway</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="affected_components[]" value="database" <?= in_array('database', $affectedComponents) ? 'checked' : '' ?>>
                        <span>Database</span>
                    </label>
                </div>
            </div>
            
            <?php if (!$isEdit): ?>
            <div class="form-group">
                <label for="started_at">Started At</label>
                <input type="datetime-local" id="started_at" name="started_at" class="form-control" value="<?= date('Y-m-d\TH:i') ?>">
                <small class="form-hint">Leave blank to use current time</small>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if ($isEdit): ?>
    <div class="card mt-4">
        <div class="card-header">
            <h3>Add Update</h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label for="update_message">Update Message</label>
                <textarea id="update_message" name="update_message" class="form-control" rows="3" placeholder="Provide an update on the incident..."></textarea>
                <small class="form-hint">Optional: Add a new update to the incident timeline</small>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="form-actions mt-4">
        <button type="submit" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
            <?= $isEdit ? 'Update Incident' : 'Create Incident' ?>
        </button>
        <a href="/admin/incidents" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<style>
.form-row {
    display: flex;
    gap: var(--space-4);
}

.form-col {
    flex: 1;
}

.checkbox-group {
    display: flex;
    flex-wrap: wrap;
    gap: var(--space-4);
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    cursor: pointer;
}

.checkbox-label input[type="checkbox"] {
    width: 18px;
    height: 18px;
}

.form-actions {
    display: flex;
    gap: var(--space-3);
}

@media (max-width: 640px) {
    .form-row {
        flex-direction: column;
    }
    
    .checkbox-group {
        flex-direction: column;
        gap: var(--space-2);
    }
}
</style>
