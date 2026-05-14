<?php
/**
 * Admin Email Template Edit Form
 * Variables: $pageTitle, $currentPage, $template
 */
$variables = json_decode($template['variables'] ?? '[]', true);
?>

<div class="page-header">
    <div class="page-header-content">
        <h1>Edit Email Template</h1>
        <p>Customize the <?= htmlspecialchars($template['name']) ?> email template</p>
    </div>
    <div class="page-header-actions">
        <a href="/admin/email-templates" class="btn btn-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            Back to Templates
        </a>
    </div>
</div>

<form action="/admin/email-templates/<?= (int)$template['id'] ?>/update" method="POST" class="template-form">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
    
    <div class="form-row">
        <div class="form-col-8">
            <!-- Main Form -->
            <div class="card">
                <div class="card-header">
                    <h3>Template Content</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="name">Template Name</label>
                        <input type="text" id="name" name="name" value="<?= htmlspecialchars($template['name']) ?>" class="form-control" disabled>
                        <small class="form-hint">Template names cannot be changed</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="subject">Email Subject *</label>
                        <input type="text" id="subject" name="subject" value="<?= htmlspecialchars($template['subject']) ?>" class="form-control" required>
                        <small class="form-hint">Use {{variable}} placeholders for dynamic content</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="body">Email Body (HTML) *</label>
                        <textarea id="body" name="body" class="form-control code-editor" rows="20" required><?= htmlspecialchars($template['body']) ?></textarea>
                        <small class="form-hint">HTML content with {{variable}} placeholders</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_active" value="1" <?= $template['is_active'] ? 'checked' : '' ?>>
                            <span>Template is active</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="form-col-4">
            <!-- Sidebar -->
            <div class="card">
                <div class="card-header">
                    <h3>Available Variables</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($variables)): ?>
                    <div class="variables-list">
                        <?php foreach ($variables as $var): ?>
                        <div class="variable-item" onclick="insertVariable('<?= htmlspecialchars($var) ?>')">
                            <code>{{<?= htmlspecialchars($var) ?>}}</code>
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <small class="form-hint mt-2">Click to insert into body</small>
                    <?php else: ?>
                    <p class="text-muted">No variables defined for this template.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header">
                    <h3>Preview</h3>
                </div>
                <div class="card-body">
                    <button type="button" class="btn btn-secondary btn-block" onclick="previewEmail()">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        Preview Email
                    </button>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header">
                    <h3>Actions</h3>
                </div>
                <div class="card-body">
                    <button type="submit" class="btn btn-primary btn-block">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                        Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Preview Modal -->
<div id="previewModal" class="modal" style="display: none;">
    <div class="modal-backdrop" onclick="closePreview()"></div>
    <div class="modal-content modal-lg">
        <div class="modal-header">
            <h3>Email Preview</h3>
            <button type="button" class="modal-close" onclick="closePreview()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="preview-subject">
                <strong>Subject:</strong> <span id="previewSubject"></span>
            </div>
            <div class="preview-body">
                <iframe id="previewFrame" style="width: 100%; height: 400px; border: 1px solid var(--border-color); border-radius: var(--radius-md);"></iframe>
            </div>
        </div>
    </div>
</div>

<style>
.form-row {
    display: flex;
    gap: var(--space-6);
}

.form-col-8 {
    flex: 2;
}

.form-col-4 {
    flex: 1;
    min-width: 280px;
}

.code-editor {
    font-family: var(--font-mono);
    font-size: var(--font-size-sm);
    line-height: 1.5;
    resize: vertical;
}

.variables-list {
    display: flex;
    flex-direction: column;
    gap: var(--space-2);
}

.variable-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--space-2) var(--space-3);
    background: var(--bg-tertiary);
    border-radius: var(--radius-md);
    cursor: pointer;
    transition: background 0.2s;
}

.variable-item:hover {
    background: var(--bg-secondary);
}

.variable-item code {
    font-size: var(--font-size-sm);
    color: var(--color-primary);
}

.variable-item svg {
    color: var(--text-muted);
    opacity: 0;
    transition: opacity 0.2s;
}

.variable-item:hover svg {
    opacity: 1;
}

.btn-block {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--space-2);
}

.modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-backdrop {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
}

.modal-content {
    position: relative;
    background: var(--surface-primary);
    border-radius: var(--radius-lg);
    max-width: 90%;
    max-height: 90%;
    overflow: auto;
}

.modal-content.modal-lg {
    width: 800px;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--space-4);
    border-bottom: 1px solid var(--border-color);
}

.modal-header h3 {
    margin: 0;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--text-muted);
}

.modal-body {
    padding: var(--space-4);
}

.preview-subject {
    padding: var(--space-3);
    background: var(--bg-tertiary);
    border-radius: var(--radius-md);
    margin-bottom: var(--space-4);
}

@media (max-width: 1023px) {
    .form-row {
        flex-direction: column;
    }
    
    .form-col-4 {
        min-width: 100%;
    }
}
</style>

<script>
function insertVariable(varName) {
    const textarea = document.getElementById('body');
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const text = textarea.value;
    const placeholder = '{{' + varName + '}}';
    
    textarea.value = text.substring(0, start) + placeholder + text.substring(end);
    textarea.selectionStart = textarea.selectionEnd = start + placeholder.length;
    textarea.focus();
}

function previewEmail() {
    const subject = document.getElementById('subject').value;
    const body = document.getElementById('body').value;
    
    // Replace placeholders with sample data
    const sampleData = {
        'user_name': 'John Doe',
        'user_email': 'john@example.com',
        'amount': '100.00',
        'reference_code': 'REF-123456',
        'new_balance': '500.00',
        'current_balance': '50.00',
        'threshold': '100.00',
        'plan_name': 'Pro Plan',
        'expiry_date': 'Jan 31, 2025',
        'days_remaining': '7',
        'verify_url': '#',
        'reset_url': '#',
        'deposit_url': '#',
        'renewal_url': '#',
        'reason': 'Invalid payment proof',
        'expiry_hours': '24'
    };
    
    let previewSubject = subject;
    let previewBody = body;
    
    for (const [key, value] of Object.entries(sampleData)) {
        const regex = new RegExp('\\{\\{' + key + '\\}\\}', 'g');
        previewSubject = previewSubject.replace(regex, value);
        previewBody = previewBody.replace(regex, value);
    }
    
    document.getElementById('previewSubject').textContent = previewSubject;
    
    const iframe = document.getElementById('previewFrame');
    const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
    iframeDoc.open();
    iframeDoc.write(previewBody);
    iframeDoc.close();
    
    document.getElementById('previewModal').style.display = 'flex';
}

function closePreview() {
    document.getElementById('previewModal').style.display = 'none';
}
</script>
