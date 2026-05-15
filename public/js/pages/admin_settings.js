/**
 * Admin Settings Page JavaScript
 * Handles form interactions, test connections, and validation
 */

document.addEventListener('DOMContentLoaded', function() {
    initSettingsPage();
});

function initSettingsPage() {
    // Initialize form change tracking
    initFormTracking();
    
    // Initialize keyboard shortcuts
    initKeyboardShortcuts();
    
    // Initialize search/filter
    initSettingsSearch();
}

/**
 * Track form changes and show unsaved indicator
 */
function initFormTracking() {
    const form = document.getElementById('settingsForm');
    const unsavedIndicator = document.getElementById('unsavedIndicator');
    
    if (!form || !unsavedIndicator) return;
    
    let formChanged = false;
    
    // Track all input changes
    form.querySelectorAll('input, select, textarea').forEach(function(element) {
        element.addEventListener('change', function() {
            formChanged = true;
            unsavedIndicator.style.display = 'flex';
        });
        
        if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA') {
            element.addEventListener('input', function() {
                formChanged = true;
                unsavedIndicator.style.display = 'flex';
            });
        }
    });
    
    // Reset flag on form submit
    form.addEventListener('submit', function() {
        formChanged = false;
    });
    
    // Warn before leaving
    window.addEventListener('beforeunload', function(e) {
        if (formChanged) {
            e.preventDefault();
            e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
            return e.returnValue;
        }
    });
}

/**
 * Keyboard shortcuts
 */
function initKeyboardShortcuts() {
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + S to save
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            const form = document.getElementById('settingsForm');
            if (form) {
                form.submit();
            }
        }
        
        // Escape to discard changes
        if (e.key === 'Escape') {
            const unsavedIndicator = document.getElementById('unsavedIndicator');
            if (unsavedIndicator && unsavedIndicator.style.display !== 'none') {
                if (confirm('Discard all changes?')) {
                    window.location.reload();
                }
            }
        }
    });
}

/**
 * Settings search/filter (future enhancement)
 */
function initSettingsSearch() {
    // Placeholder for future search functionality
    // Could filter settings list in real-time
}

/**
 * Toggle password field visibility
 */
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '_icon');
    
    if (!field || !icon) return;
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('icon-eye');
        icon.classList.add('icon-eye-off');
    } else {
        field.type = 'password';
        icon.classList.remove('icon-eye-off');
        icon.classList.add('icon-eye');
    }
}

/**
 * Confirm discard changes
 */
function confirmReset() {
    const unsavedIndicator = document.getElementById('unsavedIndicator');
    
    if (!unsavedIndicator || unsavedIndicator.style.display === 'none') {
        return true;
    }
    
    if (confirm('Are you sure you want to discard all changes?')) {
        unsavedIndicator.style.display = 'none';
        return true;
    }
    return false;
}

/**
 * Reset group settings to defaults
 */
function resetGroupSettings() {
    if (confirm('This will reload the current settings from the database. Continue?')) {
        window.location.reload();
    }
}

/**
 * Test email connection
 */
function testEmailConnection() {
    const btn = event.target.closest('button');
    const originalHTML = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<i class="icon-loader spin"></i> Testing...';
    
    const csrfToken = document.querySelector('[name="csrf_token"]');
    
    fetch('/admin/settings/test-email', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrfToken ? csrfToken.value : ''
        },
        credentials: 'same-origin'
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(data) {
        showNotification(data.success ? 'success' : 'error', data.message);
    })
    .catch(function(error) {
        showNotification('error', 'Failed to test connection: ' + error.message);
    })
    .finally(function() {
        btn.disabled = false;
        btn.innerHTML = originalHTML;
    });
}

/**
 * Test Telegram connection
 */
function testTelegramConnection() {
    const btn = event.target.closest('button');
    const originalHTML = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<i class="icon-loader spin"></i> Testing...';
    
    const csrfToken = document.querySelector('[name="csrf_token"]');
    
    fetch('/admin/settings/test-telegram', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrfToken ? csrfToken.value : ''
        },
        credentials: 'same-origin'
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(data) {
        showNotification(data.success ? 'success' : 'error', data.message);
    })
    .catch(function(error) {
        showNotification('error', 'Failed to test connection: ' + error.message);
    })
    .finally(function() {
        btn.disabled = false;
        btn.innerHTML = originalHTML;
    });
}

/**
 * Show notification toast
 */
function showNotification(type, message) {
    // Check if notification container exists
    let container = document.getElementById('notificationContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'notificationContainer';
        container.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; display: flex; flex-direction: column; gap: 10px;';
        document.body.appendChild(container);
    }
    
    const notification = document.createElement('div');
    notification.className = 'notification notification-' + type;
    notification.style.cssText = 'padding: 12px 20px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); display: flex; align-items: center; gap: 10px; min-width: 300px; animation: slideIn 0.3s ease;';
    
    if (type === 'success') {
        notification.style.background = '#10B981';
        notification.style.color = 'white';
    } else if (type === 'error') {
        notification.style.background = '#EF4444';
        notification.style.color = 'white';
    } else {
        notification.style.background = '#3B82F6';
        notification.style.color = 'white';
    }
    
    const icon = type === 'success' ? 'check-circle' : (type === 'error' ? 'x-circle' : 'info');
    notification.innerHTML = '<i class="icon-' + icon + '"></i><span>' + escapeHtml(message) + '</span>';
    
    container.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(function() {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(function() {
            notification.remove();
        }, 300);
    }, 5000);
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Add animation styles
const styleSheet = document.createElement('style');
styleSheet.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    
    .spin {
        display: inline-block;
        animation: spin 1s linear infinite;
    }
`;
document.head.appendChild(styleSheet);
