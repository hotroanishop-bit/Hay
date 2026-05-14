<?php
/**
 * Active Sessions Page
 * Variables: $pageTitle, $currentPage, $sessions, $currentToken
 */
?>

<div class="page-header page-header-flex">
    <div class="page-header-content">
        <h1 class="page-title">Active Sessions</h1>
        <p class="page-subtitle">Manage your active login sessions across devices</p>
    </div>
    <div class="page-header-actions">
        <a href="/security/login-history" class="btn btn-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            Login History
        </a>
        <?php if (count($sessions ?? []) > 1): ?>
        <form action="/security/sessions/terminate-all" method="POST" class="inline-form" onsubmit="return confirm('Are you sure you want to terminate all other sessions?');">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <button type="submit" class="btn btn-danger">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                Logout All Others
            </button>
        </form>
        <?php endif; ?>
    </div>
</div>

<div class="sessions-grid">
    <?php if (!empty($sessions)): ?>
        <?php foreach ($sessions as $session): ?>
        <?php 
        $deviceInfo = $session['device_info'] ?? ['device' => 'Unknown', 'browser' => 'Unknown', 'os' => 'Unknown'];
        $isCurrent = ($session['token'] ?? '') === ($currentToken ?? '');
        ?>
        <div class="session-card <?= $isCurrent ? 'session-current' : '' ?>">
            <div class="session-icon">
                <?php if ($deviceInfo['device'] === 'Mobile'): ?>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>
                <?php elseif ($deviceInfo['device'] === 'Tablet'): ?>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>
                <?php else: ?>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
                <?php endif; ?>
            </div>
            
            <div class="session-details">
                <div class="session-header">
                    <h3 class="session-title">
                        <?= htmlspecialchars($deviceInfo['browser']) ?> on <?= htmlspecialchars($deviceInfo['os']) ?>
                        <?php if ($isCurrent): ?>
                        <span class="badge badge-success">Current</span>
                        <?php endif; ?>
                    </h3>
                </div>
                
                <div class="session-meta">
                    <div class="session-meta-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                        <span><?= htmlspecialchars($session['ip_address'] ?? 'Unknown IP') ?></span>
                    </div>
                    <div class="session-meta-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        <span>Last active: <?= htmlspecialchars(date('M d, Y H:i', strtotime($session['last_active'] ?? 'now'))) ?></span>
                    </div>
                    <div class="session-meta-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                        <span>Created: <?= htmlspecialchars(date('M d, Y H:i', strtotime($session['created_at'] ?? 'now'))) ?></span>
                    </div>
                </div>
            </div>
            
            <?php if (!$isCurrent): ?>
            <div class="session-actions">
                <form action="/security/sessions/<?= $session['id'] ?>/terminate" method="POST" onsubmit="return confirm('Terminate this session?');">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <button type="submit" class="btn btn-ghost btn-danger-ghost" title="Terminate Session">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                        Terminate
                    </button>
                </form>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
    <div class="empty-state">
        <div class="empty-state-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
        </div>
        <h3>No Active Sessions</h3>
        <p>You don't have any active sessions recorded.</p>
    </div>
    <?php endif; ?>
</div>

<style>
.inline-form {
    display: inline-block;
}

.sessions-grid {
    display: grid;
    gap: var(--space-4);
}

.session-card {
    display: flex;
    align-items: flex-start;
    gap: var(--space-4);
    padding: var(--space-5);
    background: var(--surface-primary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    transition: border-color var(--transition-fast);
}

.session-card:hover {
    border-color: var(--border-hover);
}

.session-current {
    border-color: var(--color-success);
    background: linear-gradient(135deg, rgba(40, 167, 69, 0.05) 0%, transparent 100%);
}

.session-icon {
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    background: var(--bg-tertiary);
    border-radius: var(--radius-lg);
    color: var(--text-secondary);
}

.session-current .session-icon {
    background: var(--color-success-light);
    color: var(--color-success);
}

.session-details {
    flex: 1;
    min-width: 0;
}

.session-header {
    margin-bottom: var(--space-2);
}

.session-title {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    font-size: var(--font-size-base);
    font-weight: var(--font-weight-semibold);
    color: var(--text-primary);
    margin: 0;
}

.session-meta {
    display: flex;
    flex-wrap: wrap;
    gap: var(--space-3);
}

.session-meta-item {
    display: flex;
    align-items: center;
    gap: var(--space-1);
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
}

.session-meta-item svg {
    color: var(--text-muted);
}

.session-actions {
    flex-shrink: 0;
}

.btn-danger-ghost {
    color: var(--color-error);
}

.btn-danger-ghost:hover {
    background: rgba(220, 53, 69, 0.1);
}

.empty-state {
    grid-column: 1 / -1;
    padding: var(--space-12);
    text-align: center;
    background: var(--surface-primary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
}

.empty-state-icon {
    color: var(--text-muted);
    margin-bottom: var(--space-4);
}

.empty-state h3 {
    margin-bottom: var(--space-2);
}

.empty-state p {
    color: var(--text-secondary);
}

@media (max-width: 640px) {
    .session-card {
        flex-direction: column;
    }
    
    .session-actions {
        width: 100%;
    }
    
    .session-actions .btn {
        width: 100%;
    }
    
    .page-header-actions {
        flex-direction: column;
        gap: var(--space-2);
    }
    
    .session-meta {
        flex-direction: column;
        gap: var(--space-1);
    }
}
</style>
