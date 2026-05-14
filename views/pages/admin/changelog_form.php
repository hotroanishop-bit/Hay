<?php
/**
 * Admin Changelog Form Page
 * Variables: $pageTitle, $currentPage, $changelog, $isEdit, $versions
 */
?>

<div class="page-header">
    <div class="page-header-content">
        <a href="/admin/changelogs" class="back-link">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            Back to Changelogs
        </a>
        <h1><?= $isEdit ? 'Edit Changelog Entry' : 'Add Changelog Entry' ?></h1>
        <p><?= $isEdit ? 'Update changelog entry details' : 'Create a new changelog entry for version updates' ?></p>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="<?= $isEdit ? '/admin/changelogs/' . (int)$changelog['id'] . '/update' : '/admin/changelogs' ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="version">Version <span class="text-danger">*</span></label>
                    <input type="text" id="version" name="version" class="form-control" required
                           value="<?= htmlspecialchars($changelog['version'] ?? '') ?>"
                           placeholder="e.g., 1.0.0, 1.2.1, 2.0.0"
                           pattern="[0-9]+\.[0-9]+\.[0-9]+"
                           maxlength="20"
                           list="version-suggestions">
                    <datalist id="version-suggestions">
                        <?php if (!empty($versions)): ?>
                            <?php foreach ($versions as $ver): ?>
                            <option value="<?= htmlspecialchars($ver) ?>">
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </datalist>
                    <small class="form-text text-muted">Use semantic versioning (e.g., 1.0.0). Select existing version or enter a new one.</small>
                </div>
                <div class="form-group col-md-6">
                    <label for="type">Type <span class="text-danger">*</span></label>
                    <select id="type" name="type" class="form-control" required>
                        <option value="feature" <?= ($changelog['type'] ?? '') === 'feature' ? 'selected' : '' ?>>Feature - New functionality</option>
                        <option value="fix" <?= ($changelog['type'] ?? '') === 'fix' ? 'selected' : '' ?>>Fix - Bug fix</option>
                        <option value="improvement" <?= ($changelog['type'] ?? '') === 'improvement' ? 'selected' : '' ?>>Improvement - Enhancement</option>
                        <option value="security" <?= ($changelog['type'] ?? '') === 'security' ? 'selected' : '' ?>>Security - Security update</option>
                    </select>
                    <small class="form-text text-muted">Choose the type of change this entry represents.</small>
                </div>
            </div>

            <div class="form-group">
                <label for="title">Title <span class="text-danger">*</span></label>
                <input type="text" id="title" name="title" class="form-control" required
                       value="<?= htmlspecialchars($changelog['title'] ?? '') ?>"
                       placeholder="e.g., New referral system, Fixed login issue"
                       maxlength="255">
                <small class="form-text text-muted">A short, descriptive title for this change.</small>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="4"
                          placeholder="Provide more details about this change..."><?= htmlspecialchars($changelog['description'] ?? '') ?></textarea>
                <small class="form-text text-muted">Optional detailed description of the change. Supports plain text.</small>
            </div>

            <div class="form-group">
                <label for="published_at">Publish Date</label>
                <input type="datetime-local" id="published_at" name="published_at" class="form-control"
                       value="<?= !empty($changelog['published_at']) ? date('Y-m-d\TH:i', strtotime($changelog['published_at'])) : '' ?>">
                <small class="form-text text-muted">
                    <strong>Leave empty:</strong> Save as draft (not visible to users)<br>
                    <strong>Past date:</strong> Publish immediately<br>
                    <strong>Future date:</strong> Schedule for later
                </small>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                    <?= $isEdit ? 'Update Entry' : 'Create Entry' ?>
                </button>
                <?php if (!$isEdit): ?>
                <button type="submit" name="publish_now" value="1" class="btn btn-success">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    Create &amp; Publish Now
                </button>
                <?php endif; ?>
                <a href="/admin/changelogs" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<!-- Preview Card -->
<div class="card mt-4">
    <div class="card-header">
        <h3>Preview</h3>
    </div>
    <div class="card-body">
        <div class="preview-entry">
            <span id="preview-type" class="preview-badge badge-feature">Feature</span>
            <div class="preview-content">
                <h4 id="preview-title">Entry Title</h4>
                <p id="preview-description" class="text-muted">Description will appear here...</p>
            </div>
        </div>
    </div>
</div>

<style>
.back-link {
    display: inline-flex;
    align-items: center;
    gap: var(--space-1);
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
    margin-bottom: var(--space-2);
    transition: color var(--transition-fast);
}

.back-link:hover {
    color: var(--color-primary);
}

.form-actions {
    display: flex;
    gap: var(--space-3);
    margin-top: var(--space-6);
    padding-top: var(--space-4);
    border-top: 1px solid var(--border-color);
}

.preview-entry {
    display: flex;
    gap: var(--space-4);
    padding: var(--space-4);
    background: var(--bg-tertiary);
    border-radius: var(--radius-lg);
}

.preview-badge {
    display: inline-flex;
    align-items: center;
    padding: var(--space-1) var(--space-2);
    font-size: var(--font-size-xs);
    font-weight: var(--font-weight-semibold);
    text-transform: uppercase;
    border-radius: var(--radius-sm);
    height: fit-content;
}

.badge-feature {
    background: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
}

.badge-fix {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}

.badge-improvement {
    background: rgba(34, 197, 94, 0.1);
    color: #22c55e;
}

.badge-security {
    background: rgba(249, 115, 22, 0.1);
    color: #f97316;
}

.preview-content h4 {
    margin: 0 0 var(--space-1);
    font-size: var(--font-size-base);
    color: var(--text-primary);
}

.preview-content p {
    margin: 0;
    font-size: var(--font-size-sm);
}
</style>

<script>
// Live preview
var titleInput = document.getElementById('title');
var descInput = document.getElementById('description');
var typeSelect = document.getElementById('type');
var previewTitle = document.getElementById('preview-title');
var previewDesc = document.getElementById('preview-description');
var previewType = document.getElementById('preview-type');

function updatePreview() {
    previewTitle.textContent = titleInput.value || 'Entry Title';
    previewDesc.textContent = descInput.value || 'Description will appear here...';
    
    var type = typeSelect.value;
    var typeLabels = {
        'feature': 'Feature',
        'fix': 'Fix',
        'improvement': 'Improvement',
        'security': 'Security'
    };
    
    previewType.textContent = typeLabels[type] || 'Feature';
    previewType.className = 'preview-badge badge-' + type;
}

titleInput.addEventListener('input', updatePreview);
descInput.addEventListener('input', updatePreview);
typeSelect.addEventListener('change', updatePreview);

// Initialize preview
updatePreview();
</script>
