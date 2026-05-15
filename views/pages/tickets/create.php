<?php
/**
 * Create Ticket Page
 * Variables: $pageTitle, $currentPage, $priorities, $categories
 */
?>

<div class="page-header">
    <div class="page-header-content">
        <a href="/tickets" class="back-link">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            Back to Tickets
        </a>
        <h1 class="page-title">Create Support Ticket</h1>
        <p class="page-subtitle">Submit a new support request</p>
    </div>
</div>

<div class="ticket-create-layout">
    <div class="ticket-create-main">
        <div class="card">
            <div class="card-body">
                <form action="/tickets" method="POST" class="ticket-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    
                    <div class="form-group">
                        <label for="subject" class="form-label">Subject <span class="required">*</span></label>
                        <input type="text" 
                               id="subject" 
                               name="subject" 
                               class="form-input" 
                               placeholder="Brief description of your issue"
                               maxlength="255"
                               required>
                        <p class="form-hint">Summarize your issue in a few words</p>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="category" class="form-label">Category</label>
                            <select id="category" name="category" class="form-select">
                                <?php foreach ($categories ?? [] as $value => $label): ?>
                                <option value="<?= htmlspecialchars($value) ?>"><?= htmlspecialchars($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="priority" class="form-label">Priority</label>
                            <select id="priority" name="priority" class="form-select">
                                <?php foreach ($priorities ?? [] as $value => $label): ?>
                                <option value="<?= htmlspecialchars($value) ?>" <?= $value === 'medium' ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($label) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="form-hint">Select urgent only for critical issues</p>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="message" class="form-label">Message <span class="required">*</span></label>
                        <textarea id="message" 
                                  name="message" 
                                  class="form-textarea" 
                                  rows="8" 
                                  placeholder="Describe your issue in detail. Include any relevant information such as error messages, steps to reproduce, etc."
                                  required></textarea>
                        <p class="form-hint">Provide as much detail as possible to help us assist you faster</p>
                    </div>
                    
                    <div class="form-actions">
                        <a href="/tickets" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m22 2-7 20-4-9-9-4Z"></path><path d="M22 2 11 13"></path></svg>
                            Submit Ticket
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="ticket-create-sidebar">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tips for Faster Support</h3>
            </div>
            <div class="card-body">
                <ul class="tips-list">
                    <li>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                        <span>Include specific error messages if any</span>
                    </li>
                    <li>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                        <span>Describe steps to reproduce the issue</span>
                    </li>
                    <li>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                        <span>Mention which API or feature is affected</span>
                    </li>
                    <li>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                        <span>Include timestamps when the issue occurred</span>
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h3 class="card-title">Quick Links</h3>
            </div>
            <div class="card-body">
                <div class="quick-links">
                    <a href="/docs" class="quick-link">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                        Documentation
                    </a>
                    <a href="/changelog" class="quick-link">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                        Changelog
                    </a>
                    <a href="/status" class="quick-link">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        System Status
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Back Link */
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

/* Layout */
.ticket-create-layout {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: var(--space-6);
    align-items: start;
}

/* Form */
.ticket-form .form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--space-4);
}

.ticket-form .form-actions {
    display: flex;
    justify-content: flex-end;
    gap: var(--space-3);
    padding-top: var(--space-4);
    border-top: 1px solid var(--border-color);
    margin-top: var(--space-4);
}

.ticket-form .form-textarea {
    min-height: 200px;
    resize: vertical;
}

.required {
    color: var(--color-error);
}

/* Tips List */
.tips-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: var(--space-3);
}

.tips-list li {
    display: flex;
    align-items: flex-start;
    gap: var(--space-2);
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
}

.tips-list svg {
    flex-shrink: 0;
    color: var(--color-success);
    margin-top: 2px;
}

/* Quick Links */
.quick-links {
    display: flex;
    flex-direction: column;
    gap: var(--space-2);
}

.quick-link {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    padding: var(--space-2) var(--space-3);
    border-radius: var(--radius-md);
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
    transition: all var(--transition-fast);
}

.quick-link:hover {
    background: var(--bg-tertiary);
    color: var(--text-primary);
}

/* Responsive */
@media (max-width: 1023px) {
    .ticket-create-layout {
        grid-template-columns: 1fr;
    }
    
    .ticket-form .form-row {
        grid-template-columns: 1fr;
    }
}
</style>
