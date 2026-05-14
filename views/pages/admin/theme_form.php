<?php
/**
 * Admin Theme Form Page
 * Variables: $pageTitle, $currentPage, $theme, $isEdit
 */

// Decode CSS variables for display
$cssVariables = '';
if ($isEdit && !empty($theme['css_variables'])) {
    $cssVariables = $theme['css_variables'];
    // Pretty print if valid JSON
    $decoded = json_decode($cssVariables, true);
    if ($decoded !== null) {
        $cssVariables = json_encode($decoded, JSON_PRETTY_PRINT);
    }
}
?>

<div class="page-header">
    <div class="page-header-content">
        <h1><?= $isEdit ? 'Edit Theme' : 'Create Theme' ?></h1>
        <p><?= $isEdit ? 'Update theme settings' : 'Create a new UI theme' ?></p>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="<?= $isEdit ? '/admin/themes/' . (int)$theme['id'] . '/update' : '/admin/themes' ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

            <div class="form-group">
                <label for="name">Theme Name <span class="text-danger">*</span></label>
                <input type="text" id="name" name="name" class="form-control" required
                       value="<?= htmlspecialchars($theme['name'] ?? '') ?>"
                       placeholder="e.g., Dark Mode, Light Mode, Blue Theme">
            </div>

            <div class="form-group">
                <label for="css_variables">CSS Variables (JSON)</label>
                <textarea id="css_variables" name="css_variables" class="form-control" rows="15"
                          placeholder='{"--primary-color": "#3498db", "--bg-color": "#ffffff"}'><?= htmlspecialchars($cssVariables) ?></textarea>
                <small class="form-text text-muted">
                    Enter CSS variables as JSON. Example:<br>
                    <code>{"--primary-color": "#3498db", "--secondary-color": "#2ecc71", "--bg-color": "#ffffff"}</code>
                </small>
            </div>

            <div class="form-group">
                <div class="form-check">
                    <input type="checkbox" id="is_default" name="is_default" class="form-check-input"
                           <?= (!empty($theme['is_default'])) ? 'checked' : '' ?>>
                    <label for="is_default" class="form-check-label">Set as Default Theme</label>
                    <small class="form-text text-muted">This theme will be applied to all users by default</small>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="icon-save"></i> <?= $isEdit ? 'Update Theme' : 'Create Theme' ?>
                </button>
                <a href="/admin/themes" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
// Simple JSON validation on form submit
document.querySelector('form').addEventListener('submit', function(e) {
    var textarea = document.getElementById('css_variables');
    var value = textarea.value.trim();
    
    if (value && value !== '{}') {
        try {
            JSON.parse(value);
        } catch (err) {
            e.preventDefault();
            alert('CSS Variables must be valid JSON. Error: ' + err.message);
            textarea.focus();
        }
    }
});
</script>
