<?php
/**
 * Impersonation Banner Partial
 * Shows when admin is impersonating a user
 */

// Check if impersonation is active
$sessionService = new SessionService();
$userModel = new User();
$authService = new AuthService($sessionService, $userModel);

if ($authService->isImpersonating()):
    $impersonationInfo = $authService->getImpersonationInfo();
    $targetUser = $impersonationInfo['target_user'] ?? null;
    $adminName = $impersonationInfo['admin_name'] ?? 'Admin';
    $startedAt = $impersonationInfo['started_at'] ?? time();
    $duration = time() - $startedAt;
?>
<div class="impersonation-banner" id="impersonation-banner">
    <div class="impersonation-banner-content">
        <div class="impersonation-info">
            <span class="impersonation-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
            </span>
            <span class="impersonation-text">
                <strong>Viewing as:</strong> 
                <?= htmlspecialchars($targetUser['name'] ?? $targetUser['email'] ?? 'Unknown User') ?>
                <span class="impersonation-duration" data-started="<?= $startedAt ?>">(<?= gmdate('i:s', $duration) ?>)</span>
            </span>
        </div>
        <a href="/admin/exit-impersonation" class="btn btn-sm btn-impersonate-exit">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                <polyline points="16 17 21 12 16 7"></polyline>
                <line x1="21" y1="12" x2="9" y2="12"></line>
            </svg>
            Exit Impersonation
        </a>
    </div>
</div>

<style>
.impersonation-banner {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: white;
    z-index: 10000;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
}

.impersonation-banner-content {
    max-width: 1200px;
    margin: 0 auto;
    padding: 10px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.impersonation-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.impersonation-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
}

.impersonation-text {
    font-size: 14px;
}

.impersonation-text strong {
    font-weight: 600;
}

.impersonation-duration {
    opacity: 0.8;
    font-size: 12px;
    margin-left: 4px;
}

.btn-impersonate-exit {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(255, 255, 255, 0.2);
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.3);
    padding: 6px 14px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s;
}

.btn-impersonate-exit:hover {
    background: rgba(255, 255, 255, 0.3);
    color: white;
    text-decoration: none;
}

/* Adjust body padding when banner is visible */
body.has-impersonation-banner {
    padding-top: 52px;
}

body.has-impersonation-banner .header {
    top: 52px;
}

body.has-impersonation-banner .sidebar {
    top: calc(60px + 52px);
}

@media (max-width: 768px) {
    .impersonation-banner-content {
        flex-direction: column;
        gap: 10px;
        text-align: center;
    }
    
    .impersonation-info {
        flex-direction: column;
        gap: 8px;
    }
}
</style>

<script>
// Add class to body
document.body.classList.add('has-impersonation-banner');

// Update duration counter
(function() {
    const durationEl = document.querySelector('.impersonation-duration');
    if (!durationEl) return;
    
    const startedAt = parseInt(durationEl.dataset.started, 10);
    
    function updateDuration() {
        const now = Math.floor(Date.now() / 1000);
        const duration = now - startedAt;
        const minutes = Math.floor(duration / 60);
        const seconds = duration % 60;
        durationEl.textContent = '(' + String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0') + ')';
    }
    
    updateDuration();
    setInterval(updateDuration, 1000);
})();
</script>
<?php endif; ?>
