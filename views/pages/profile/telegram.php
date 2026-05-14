<?php
/**
 * Telegram Integration Page
 * Variables: $pageTitle, $currentPage, $user, $isLinked, $linkedAt, $isConfigured
 */
?>

<div class="page-header">
    <div class="page-header-content">
        <h1>Telegram Notifications</h1>
        <p>Link your Telegram account to receive instant notifications</p>
    </div>
    <div class="page-header-actions">
        <a href="/profile" class="btn btn-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Back to Profile
        </a>
    </div>
</div>

<?php if (!empty($flash)): ?>
<div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
    <?= htmlspecialchars($flash['message']) ?>
</div>
<?php endif; ?>

<?php if (!$isConfigured): ?>
<div class="card">
    <div class="card-body">
        <div class="empty-state">
            <div class="empty-state-icon text-warning">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                    <line x1="12" y1="9" x2="12" y2="13"></line>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
            </div>
            <h3>Telegram Not Configured</h3>
            <p>The administrator has not configured Telegram integration yet. Please check back later.</p>
        </div>
    </div>
</div>
<?php else: ?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3>Telegram Account</h3>
            </div>
            <div class="card-body">
                <?php if ($isLinked): ?>
                <!-- Linked State -->
                <div class="telegram-status telegram-linked">
                    <div class="status-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                    </div>
                    <h4>Telegram Connected</h4>
                    <p class="text-muted">
                        Your Telegram account is linked and receiving notifications.
                        <?php if ($linkedAt): ?>
                        <br>
                        <small>Linked on <?= htmlspecialchars(date('M d, Y H:i', strtotime($linkedAt))) ?></small>
                        <?php endif; ?>
                    </p>
                    
                    <form action="/telegram/unlink" method="POST" class="mt-4">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to unlink your Telegram account? You will stop receiving notifications.')">
                            Unlink Telegram
                        </button>
                    </form>
                </div>
                <?php else: ?>
                <!-- Not Linked State -->
                <div class="telegram-status telegram-not-linked">
                    <div class="status-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                        </svg>
                    </div>
                    <h4>Link Your Telegram Account</h4>
                    <p class="text-muted">Connect your Telegram account to receive instant notifications about deposits, security alerts, and more.</p>
                    
                    <div id="linkSection">
                        <button type="button" id="generateLinkBtn" class="btn btn-primary btn-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                            </svg>
                            Link Telegram Account
                        </button>
                    </div>
                    
                    <div id="linkResult" style="display: none;" class="mt-4">
                        <div class="alert alert-info">
                            <strong>Step 1:</strong> Click the button below to open Telegram<br>
                            <strong>Step 2:</strong> Start the bot by pressing "Start"<br>
                            <strong>Step 3:</strong> Your account will be linked automatically
                        </div>
                        <a id="telegramLink" href="#" target="_blank" class="btn btn-success btn-lg">
                            Open Telegram Bot
                        </a>
                        <p class="text-muted mt-2">
                            <small>Link expires in 30 minutes. Generate a new one if needed.</small>
                        </p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Info Card -->
        <div class="card">
            <div class="card-header">
                <h3>Notification Types</h3>
            </div>
            <div class="card-body">
                <ul class="notification-types-list">
                    <li>
                        <span class="notification-icon text-success">&#9989;</span>
                        <span>Deposit Approved</span>
                    </li>
                    <li>
                        <span class="notification-icon text-danger">&#10060;</span>
                        <span>Deposit Rejected</span>
                    </li>
                    <li>
                        <span class="notification-icon text-warning">&#9888;&#65039;</span>
                        <span>Low Balance Warning</span>
                    </li>
                    <li>
                        <span class="notification-icon text-info">&#128273;</span>
                        <span>API Key Activity</span>
                    </li>
                    <li>
                        <span class="notification-icon text-danger">&#128680;</span>
                        <span>Security Alerts</span>
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- Help Card -->
        <div class="card mt-4">
            <div class="card-header">
                <h3>Help</h3>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    <strong>How it works:</strong><br>
                    When you link your Telegram account, our bot will send you real-time notifications directly to your Telegram app.
                </p>
                <p class="text-muted">
                    <strong>Privacy:</strong><br>
                    We only use your Telegram chat ID to send notifications. We don't access your messages or contacts.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const generateBtn = document.getElementById('generateLinkBtn');
    const linkSection = document.getElementById('linkSection');
    const linkResult = document.getElementById('linkResult');
    const telegramLink = document.getElementById('telegramLink');
    
    if (generateBtn) {
        generateBtn.addEventListener('click', function() {
            generateBtn.disabled = true;
            generateBtn.innerHTML = 'Generating link...';
            
            fetch('/telegram/link', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    csrf_token: '<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.link_url) {
                    telegramLink.href = data.link_url;
                    linkSection.style.display = 'none';
                    linkResult.style.display = 'block';
                } else {
                    alert(data.error || 'Failed to generate link. Please try again.');
                    generateBtn.disabled = false;
                    generateBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg> Link Telegram Account';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
                generateBtn.disabled = false;
            });
        });
    }
});
</script>

<?php endif; ?>
