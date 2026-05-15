/**
 * Keyboard Shortcuts
 * Global keyboard shortcuts for quick navigation
 */

(function() {
    'use strict';
    
    // Shortcut definitions
    const shortcuts = {
        'ctrl+k': { action: openSearch, description: 'Tim kiem nhanh' },
        'ctrl+n': { action: () => window.location.href = '/keys/create', description: 'Tao API key moi' },
        'ctrl+d': { action: () => window.location.href = '/dashboard', description: 'Di den Dashboard' },
        'ctrl+/': { action: showShortcutsHelp, description: 'Hien thi danh sach phim tat' },
        'escape': { action: closeAllModals, description: 'Dong tat ca modal' },
        'ctrl+shift+p': { action: () => window.location.href = '/playground', description: 'Mo API Playground' },
        'ctrl+shift+b': { action: () => window.location.href = '/billing', description: 'Mo Billing' },
        'ctrl+shift+s': { action: () => window.location.href = '/tickets/create', description: 'Tao ticket ho tro' },
    };
    
    // Check if user is logged in (search modal exists)
    const isLoggedIn = document.getElementById('search-modal') !== null;
    
    // Listen for keyboard events
    document.addEventListener('keydown', function(e) {
        // Don't trigger shortcuts when typing in input fields
        if (isTypingInInput(e.target)) {
            // Only allow Escape in inputs
            if (e.key === 'Escape') {
                closeAllModals();
            }
            return;
        }
        
        const shortcut = getShortcutKey(e);
        
        if (shortcuts[shortcut]) {
            e.preventDefault();
            shortcuts[shortcut].action();
        }
    });
    
    /**
     * Check if currently typing in an input field
     */
    function isTypingInInput(target) {
        const tagName = target.tagName.toLowerCase();
        const isEditable = target.isContentEditable;
        return tagName === 'input' || tagName === 'textarea' || tagName === 'select' || isEditable;
    }
    
    /**
     * Get shortcut key combination string
     */
    function getShortcutKey(e) {
        const parts = [];
        
        if (e.ctrlKey || e.metaKey) parts.push('ctrl');
        if (e.shiftKey) parts.push('shift');
        if (e.altKey) parts.push('alt');
        
        const key = e.key.toLowerCase();
        if (!['control', 'shift', 'alt', 'meta'].includes(key)) {
            parts.push(key);
        }
        
        return parts.join('+');
    }
    
    /**
     * Open search modal
     */
    function openSearch() {
        if (!isLoggedIn) return;
        
        if (typeof window.openSearch === 'function') {
            window.openSearch();
        }
    }
    
    /**
     * Close all open modals
     */
    function closeAllModals() {
        // Close search modal
        const searchModal = document.getElementById('search-modal');
        if (searchModal && searchModal.style.display !== 'none') {
            searchModal.style.display = 'none';
            return;
        }
        
        // Close chat widget
        const chatWindow = document.getElementById('chat-window');
        if (chatWindow && chatWindow.style.display !== 'none') {
            chatWindow.style.display = 'none';
            const chatIcon = document.querySelector('.chat-toggle-btn .chat-icon');
            const closeIcon = document.querySelector('.chat-toggle-btn .close-icon');
            if (chatIcon) chatIcon.style.display = 'block';
            if (closeIcon) closeIcon.style.display = 'none';
            return;
        }
        
        // Close any modal overlays
        const modals = document.querySelectorAll('.modal-overlay[style*="display: flex"], .modal-overlay:not([style*="display: none"])');
        modals.forEach(modal => {
            modal.style.display = 'none';
        });
        
        // Close bottom sheet on mobile
        const bottomSheet = document.getElementById('bottom-sheet');
        if (bottomSheet && bottomSheet.classList.contains('open')) {
            bottomSheet.classList.remove('open');
        }
    }
    
    /**
     * Show shortcuts help modal
     */
    function showShortcutsHelp() {
        // Check if modal already exists
        let modal = document.getElementById('shortcuts-help-modal');
        
        if (!modal) {
            // Create modal
            modal = document.createElement('div');
            modal.id = 'shortcuts-help-modal';
            modal.className = 'modal-overlay';
            modal.innerHTML = `
                <div class="modal-content shortcuts-modal">
                    <div class="modal-header">
                        <h3>Phim tat</h3>
                        <button class="modal-close" onclick="document.getElementById('shortcuts-help-modal').style.display='none'">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="shortcuts-list">
                            ${Object.entries(shortcuts).map(([key, config]) => `
                                <div class="shortcut-item">
                                    <span class="shortcut-keys">
                                        ${formatShortcutKey(key)}
                                    </span>
                                    <span class="shortcut-desc">${config.description}</span>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
            
            // Add styles if not already present
            if (!document.getElementById('shortcuts-styles')) {
                const style = document.createElement('style');
                style.id = 'shortcuts-styles';
                style.textContent = `
                    .shortcuts-modal {
                        max-width: 450px;
                    }
                    .shortcuts-list {
                        display: flex;
                        flex-direction: column;
                        gap: var(--space-2);
                    }
                    .shortcut-item {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        padding: var(--space-2) 0;
                        border-bottom: 1px solid var(--border-color);
                    }
                    .shortcut-item:last-child {
                        border-bottom: none;
                    }
                    .shortcut-keys {
                        display: flex;
                        gap: var(--space-1);
                    }
                    .shortcut-keys kbd {
                        padding: var(--space-1) var(--space-2);
                        background: var(--bg-secondary);
                        border: 1px solid var(--border-color);
                        border-radius: var(--radius-sm);
                        font-size: var(--text-xs);
                        font-family: monospace;
                    }
                    .shortcut-desc {
                        color: var(--text-secondary);
                        font-size: var(--text-sm);
                    }
                `;
                document.head.appendChild(style);
            }
            
            // Close on overlay click
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.style.display = 'none';
                }
            });
        }
        
        modal.style.display = 'flex';
    }
    
    /**
     * Format shortcut key for display
     */
    function formatShortcutKey(key) {
        const parts = key.split('+');
        return parts.map(part => {
            const label = part.charAt(0).toUpperCase() + part.slice(1);
            return `<kbd>${label}</kbd>`;
        }).join(' + ');
    }
    
    // Expose to window for external use
    window.keyboardShortcuts = {
        shortcuts: shortcuts,
        showHelp: showShortcutsHelp,
        closeModals: closeAllModals
    };
    
    // Log info on load
    console.log('%c[Shortcuts] Keyboard shortcuts loaded. Press Ctrl+/ for help.', 'color: #888');
})();
