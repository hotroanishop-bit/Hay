<?php
/**
 * PWA Install Prompt Partial
 * Displays a banner prompting users to install the app
 */
?>
<div id="pwa-install-prompt" class="pwa-install-prompt">
    <div class="pwa-install-content">
        <div class="pwa-install-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"></path><path d="M2 17l10 5 10-5"></path><path d="M2 12l10 5 10-5"></path></svg>
        </div>
        <div class="pwa-install-text">
            <strong>Install Hay App</strong>
            <span>Add to home screen for a better experience</span>
        </div>
        <div class="pwa-install-actions">
            <button onclick="installPWA()" class="btn btn-primary btn-sm">Install</button>
            <button onclick="dismissInstallPrompt()" class="btn btn-secondary btn-sm">Not now</button>
        </div>
    </div>
</div>

<style>
.pwa-install-prompt {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 9999;
    background: var(--surface-primary, #fff);
    border-top: 1px solid var(--border-color, #e5e7eb);
    box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.1);
    padding: 1rem;
    transform: translateY(100%);
    transition: transform 0.3s ease-out;
}

.pwa-install-prompt.visible {
    transform: translateY(0);
}

.pwa-install-content {
    max-width: 600px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.pwa-install-icon {
    flex-shrink: 0;
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    border-radius: 12px;
    color: white;
}

.pwa-install-text {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.pwa-install-text strong {
    color: var(--text-primary, #111827);
    font-size: 0.9375rem;
}

.pwa-install-text span {
    color: var(--text-secondary, #6b7280);
    font-size: 0.8125rem;
}

.pwa-install-actions {
    display: flex;
    gap: 0.5rem;
    flex-shrink: 0;
}

.pwa-update-notification {
    position: fixed;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 9999;
    background: var(--surface-primary, #fff);
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    padding: 1rem;
}

.pwa-update-content {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.pwa-update-content span {
    color: var(--text-primary, #111827);
    font-size: 0.875rem;
}

@media (max-width: 480px) {
    .pwa-install-content {
        flex-wrap: wrap;
    }
    
    .pwa-install-actions {
        width: 100%;
        justify-content: flex-end;
    }
}
</style>
