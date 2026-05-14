<?php
/**
 * Admin Email Templates Page
 * Variables: $pageTitle, $currentPage, $templates, $stats
 */
?>

<div class="page-header">
    <div class="page-header-content">
        <h1>Email Templates</h1>
        <p>Manage email notification templates with customizable content</p>
    </div>
</div>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon bg-primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
        </div>
        <div class="stat-content">
            <span class="stat-label">Total Templates</span>
            <span class="stat-value"><?= number_format($stats['total'] ?? 0) ?></span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-success">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
        </div>
        <div class="stat-content">
            <span class="stat-label">Active</span>
            <span class="stat-value"><?= number_format($stats['active'] ?? 0) ?></span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-warning">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line></svg>
        </div>
        <div class="stat-content">
            <span class="stat-label">Inactive</span>
            <span class="stat-value"><?= number_format($stats['inactive'] ?? 0) ?></span>
        </div>
    </div>
</div>

<!-- Templates Table -->
<div class="card mt-6">
    <div class="card-header">
        <h3>All Email Templates</h3>
    </div>
    <div class="card-body">
        <?php if (!empty($templates)): ?>
        <div class="table-responsive">
            <table class="table table-hover admin-table">
                <thead>
                    <tr>
                        <th>Template Name</th>
                        <th>Subject</th>
                        <th>Variables</th>
                        <th>Status</th>
                        <th>Last Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($templates as $template): ?>
                    <tr>
                        <td>
                            <div class="template-name">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                                <code><?= htmlspecialchars($template['name']) ?></code>
                            </div>
                        </td>
                        <td>
                            <span class="subject-preview"><?= htmlspecialchars(substr($template['subject'], 0, 50)) ?><?= strlen($template['subject']) > 50 ? '...' : '' ?></span>
                        </td>
                        <td>
                            <?php 
                            $variables = json_decode($template['variables'] ?? '[]', true);
                            if (!empty($variables)):
                                foreach (array_slice($variables, 0, 3) as $var): 
                            ?>
                            <span class="variable-badge">{{<?= htmlspecialchars($var) ?>}}</span>
                            <?php endforeach;
                                if (count($variables) > 3): ?>
                            <span class="variable-more">+<?= count($variables) - 3 ?></span>
                            <?php endif;
                            else: ?>
                            <span class="text-muted">None</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($template['is_active']): ?>
                            <span class="badge badge-success">Active</span>
                            <?php else: ?>
                            <span class="badge badge-secondary">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <small class="text-muted"><?= date('M d, Y', strtotime($template['updated_at'])) ?></small>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="/admin/email-templates/<?= (int)$template['id'] ?>/edit" class="btn btn-sm btn-secondary" title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                </a>
                                <form action="/admin/email-templates/<?= (int)$template['id'] ?>/reset" method="POST" class="d-inline" onsubmit="return confirm('Reset this template to default? All customizations will be lost.');">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                    <button type="submit" class="btn btn-sm btn-warning" title="Reset to Default">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"></path><path d="M21 3v5h-5"></path><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"></path><path d="M8 16H3v5"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
            <h3>No email templates</h3>
            <p>Run the migration to create default email templates.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Variable Reference -->
<div class="card mt-4">
    <div class="card-header">
        <h3>Variable Reference</h3>
    </div>
    <div class="card-body">
        <div class="variable-reference">
            <div class="variable-group">
                <h4>Common Variables</h4>
                <ul>
                    <li><code>{{user_name}}</code> - User's display name</li>
                    <li><code>{{user_email}}</code> - User's email address</li>
                </ul>
            </div>
            <div class="variable-group">
                <h4>Financial</h4>
                <ul>
                    <li><code>{{amount}}</code> - Transaction amount</li>
                    <li><code>{{reference_code}}</code> - Reference code</li>
                    <li><code>{{new_balance}}</code> - Updated balance</li>
                    <li><code>{{current_balance}}</code> - Current balance</li>
                </ul>
            </div>
            <div class="variable-group">
                <h4>Plan/Subscription</h4>
                <ul>
                    <li><code>{{plan_name}}</code> - Plan name</li>
                    <li><code>{{expiry_date}}</code> - Expiration date</li>
                    <li><code>{{days_remaining}}</code> - Days until expiry</li>
                </ul>
            </div>
            <div class="variable-group">
                <h4>Links</h4>
                <ul>
                    <li><code>{{verify_url}}</code> - Email verification URL</li>
                    <li><code>{{reset_url}}</code> - Password reset URL</li>
                    <li><code>{{deposit_url}}</code> - Deposit page URL</li>
                    <li><code>{{renewal_url}}</code> - Plan renewal URL</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: var(--space-4);
}

.stat-card {
    display: flex;
    align-items: center;
    gap: var(--space-4);
    padding: var(--space-4);
    background: var(--surface-primary);
    border-radius: var(--radius-lg);
    border: 1px solid var(--border-color);
}

.stat-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    border-radius: var(--radius-lg);
    color: var(--color-white);
}

.stat-icon.bg-primary { background: var(--color-primary); }
.stat-icon.bg-success { background: var(--color-success); }
.stat-icon.bg-warning { background: var(--color-warning); }

.stat-content {
    display: flex;
    flex-direction: column;
}

.stat-label {
    font-size: var(--font-size-sm);
    color: var(--text-muted);
}

.stat-value {
    font-size: var(--font-size-xl);
    font-weight: var(--font-weight-bold);
    color: var(--text-primary);
}

.template-name {
    display: flex;
    align-items: center;
    gap: var(--space-2);
}

.template-name svg {
    color: var(--text-muted);
}

.template-name code {
    font-size: var(--font-size-sm);
    background: var(--bg-tertiary);
    padding: var(--space-1) var(--space-2);
    border-radius: var(--radius-sm);
}

.subject-preview {
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
}

.variable-badge {
    display: inline-block;
    padding: 2px 6px;
    background: rgba(99, 102, 241, 0.1);
    color: var(--color-primary);
    font-size: 0.6875rem;
    font-family: var(--font-mono);
    border-radius: var(--radius-sm);
    margin-right: var(--space-1);
}

.variable-more {
    font-size: var(--font-size-xs);
    color: var(--text-muted);
}

.variable-reference {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: var(--space-6);
}

.variable-group h4 {
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-semibold);
    color: var(--text-primary);
    margin: 0 0 var(--space-2);
}

.variable-group ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.variable-group li {
    padding: var(--space-1) 0;
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
}

.variable-group code {
    background: var(--bg-tertiary);
    padding: 2px 6px;
    border-radius: var(--radius-sm);
    font-size: var(--font-size-xs);
}

.empty-state {
    text-align: center;
    padding: var(--space-10);
    color: var(--text-muted);
}

.empty-state svg {
    margin-bottom: var(--space-4);
    opacity: 0.5;
}

.empty-state h3 {
    margin: 0 0 var(--space-2);
    color: var(--text-primary);
}

.empty-state p {
    margin: 0;
}

@media (max-width: 1023px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .variable-reference {
        grid-template-columns: 1fr;
    }
}
</style>
