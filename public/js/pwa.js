/**
 * PWA Support - Service Worker Registration and Install Prompt
 */

(function() {
    'use strict';

    // Store install prompt event
    let deferredPrompt = null;
    let isAppInstalled = false;

    // Check if app is already installed
    if (window.matchMedia('(display-mode: standalone)').matches) {
        isAppInstalled = true;
        console.log('[PWA] App is running in standalone mode');
    }

    // Register service worker
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', async () => {
            try {
                const registration = await navigator.serviceWorker.register('/sw.js', {
                    scope: '/'
                });
                
                console.log('[PWA] Service Worker registered:', registration.scope);
                
                // Check for updates
                registration.addEventListener('updatefound', () => {
                    const newWorker = registration.installing;
                    
                    newWorker.addEventListener('statechange', () => {
                        if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                            // New version available
                            showUpdateAvailable();
                        }
                    });
                });
                
                // Listen for messages from SW
                navigator.serviceWorker.addEventListener('message', (event) => {
                    if (event.data && event.data.type === 'SW_UPDATED') {
                        window.location.reload();
                    }
                });
                
            } catch (error) {
                console.error('[PWA] Service Worker registration failed:', error);
            }
        });
    }

    // Capture install prompt
    window.addEventListener('beforeinstallprompt', (e) => {
        console.log('[PWA] beforeinstallprompt fired');
        
        // Prevent default mini-infobar
        e.preventDefault();
        
        // Store the event for later use
        deferredPrompt = e;
        
        // Show install prompt UI
        showInstallPrompt();
    });

    // Listen for successful install
    window.addEventListener('appinstalled', () => {
        console.log('[PWA] App was installed');
        isAppInstalled = true;
        deferredPrompt = null;
        hideInstallPrompt();
    });

    // Show install prompt UI
    function showInstallPrompt() {
        if (isAppInstalled) return;
        
        const prompt = document.getElementById('pwa-install-prompt');
        if (prompt) {
            prompt.classList.add('visible');
        }
    }

    // Hide install prompt UI
    function hideInstallPrompt() {
        const prompt = document.getElementById('pwa-install-prompt');
        if (prompt) {
            prompt.classList.remove('visible');
        }
    }

    // Trigger install
    window.installPWA = async function() {
        if (!deferredPrompt) {
            console.log('[PWA] No install prompt available');
            return;
        }
        
        // Show install prompt
        deferredPrompt.prompt();
        
        // Wait for user response
        const { outcome } = await deferredPrompt.userChoice;
        console.log('[PWA] Install prompt outcome:', outcome);
        
        // Clear the prompt
        deferredPrompt = null;
        
        if (outcome === 'accepted') {
            hideInstallPrompt();
        }
    };

    // Dismiss install prompt
    window.dismissInstallPrompt = function() {
        hideInstallPrompt();
        
        // Store dismissal in localStorage
        localStorage.setItem('pwa-prompt-dismissed', Date.now().toString());
    };

    // Show update available notification
    function showUpdateAvailable() {
        const notification = document.createElement('div');
        notification.className = 'pwa-update-notification';
        notification.innerHTML = `
            <div class="pwa-update-content">
                <span>A new version is available!</span>
                <button onclick="updateApp()" class="btn btn-sm btn-primary">Update</button>
                <button onclick="this.parentElement.parentElement.remove()" class="btn btn-sm btn-secondary">Later</button>
            </div>
        `;
        document.body.appendChild(notification);
    }

    // Update app
    window.updateApp = function() {
        if ('serviceWorker' in navigator && navigator.serviceWorker.controller) {
            navigator.serviceWorker.controller.postMessage({ type: 'SKIP_WAITING' });
        }
        window.location.reload();
    };

    // Check if should show prompt (not dismissed recently)
    function shouldShowPrompt() {
        const dismissed = localStorage.getItem('pwa-prompt-dismissed');
        if (!dismissed) return true;
        
        const dismissedTime = parseInt(dismissed, 10);
        const weekAgo = Date.now() - (7 * 24 * 60 * 60 * 1000);
        
        return dismissedTime < weekAgo;
    }

    // Initialize on DOM ready
    document.addEventListener('DOMContentLoaded', () => {
        // Check if we should show prompt based on previous dismissal
        if (!shouldShowPrompt()) {
            return;
        }
        
        // If we already have a deferred prompt, show it
        if (deferredPrompt && !isAppInstalled) {
            showInstallPrompt();
        }
    });
})();
