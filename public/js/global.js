/**
 * Global JavaScript Utilities
 * Common functionality used across the application
 */

(function() {
    'use strict';

    // DOM Ready helper
    function ready(fn) {
        if (document.readyState !== 'loading') {
            fn();
        } else {
            document.addEventListener('DOMContentLoaded', fn);
        }
    }

    // Sidebar Toggle
    function initSidebar() {
        const sidebarToggle = document.getElementById('sidebarToggle');
        const appContainer = document.querySelector('.app-container');
        const sidebar = document.getElementById('sidebar');

        if (sidebarToggle && appContainer) {
            sidebarToggle.addEventListener('click', function() {
                appContainer.classList.toggle('sidebar-collapsed');
                
                // Mobile: toggle sidebar open/close
                if (window.innerWidth <= 768 && sidebar) {
                    sidebar.classList.toggle('open');
                }
            });
        }
    }

    // User Dropdown
    function initUserDropdown() {
        const toggleBtn = document.getElementById('userDropdownToggle');
        const dropdownMenu = document.getElementById('userDropdownMenu');

        if (toggleBtn && dropdownMenu) {
            toggleBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                dropdownMenu.classList.toggle('show');
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!dropdownMenu.contains(e.target) && !toggleBtn.contains(e.target)) {
                    dropdownMenu.classList.remove('show');
                }
            });
        }
    }

    // Alert Dismissal
    function initAlerts() {
        document.querySelectorAll('.alert-close').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const alert = this.closest('.alert');
                if (alert) {
                    alert.style.opacity = '0';
                    setTimeout(function() {
                        alert.remove();
                    }, 200);
                }
            });
        });

        // Auto-dismiss alerts after 5 seconds
        document.querySelectorAll('.alert').forEach(function(alert) {
            setTimeout(function() {
                if (alert.parentNode) {
                    alert.style.opacity = '0';
                    setTimeout(function() {
                        alert.remove();
                    }, 200);
                }
            }, 5000);
        });
    }

    // Modal functionality
    function initModals() {
        // Open modal
        document.querySelectorAll('[data-modal-open]').forEach(function(trigger) {
            trigger.addEventListener('click', function(e) {
                e.preventDefault();
                const modalId = this.getAttribute('data-modal-open');
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.classList.add('show');
                    document.body.style.overflow = 'hidden';
                }
            });
        });

        // Close modal
        document.querySelectorAll('[data-modal-close]').forEach(function(trigger) {
            trigger.addEventListener('click', function() {
                const modal = this.closest('.modal-overlay');
                if (modal) {
                    modal.classList.remove('show');
                    document.body.style.overflow = '';
                }
            });
        });

        // Close modal on overlay click
        document.querySelectorAll('.modal-overlay').forEach(function(overlay) {
            overlay.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.remove('show');
                    document.body.style.overflow = '';
                }
            });
        });

        // Close modal on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const openModal = document.querySelector('.modal-overlay.show');
                if (openModal) {
                    openModal.classList.remove('show');
                    document.body.style.overflow = '';
                }
            }
        });
    }

    // Copy to clipboard utility
    window.copyToClipboard = function(text, callback) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function() {
                if (callback) callback(true);
            }).catch(function() {
                if (callback) callback(false);
            });
        } else {
            // Fallback for older browsers
            var textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            try {
                document.execCommand('copy');
                if (callback) callback(true);
            } catch (err) {
                if (callback) callback(false);
            }
            document.body.removeChild(textarea);
        }
    };

    // Format currency
    window.formatCurrency = function(amount, currency) {
        currency = currency || 'USD';
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: currency
        }).format(amount);
    };

    // Format date
    window.formatDate = function(dateString, options) {
        options = options || { year: 'numeric', month: 'short', day: 'numeric' };
        return new Date(dateString).toLocaleDateString('en-US', options);
    };

    // Show notification toast
    window.showToast = function(message, type) {
        type = type || 'info';
        var toast = document.createElement('div');
        toast.className = 'alert alert-' + type;
        toast.style.cssText = 'position: fixed; bottom: 20px; right: 20px; z-index: 500; min-width: 300px;';
        toast.innerHTML = '<span class="alert-message">' + escapeHtml(message) + '</span>' +
                         '<button type="button" class="alert-close">&times;</button>';
        document.body.appendChild(toast);

        toast.querySelector('.alert-close').addEventListener('click', function() {
            toast.remove();
        });

        setTimeout(function() {
            if (toast.parentNode) {
                toast.remove();
            }
        }, 5000);
    };

    // Escape HTML helper
    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Debounce function
    window.debounce = function(func, wait) {
        var timeout;
        return function() {
            var context = this;
            var args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                func.apply(context, args);
            }, wait);
        };
    };

    // Throttle function
    window.throttle = function(func, limit) {
        var inThrottle;
        return function() {
            var context = this;
            var args = arguments;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(function() {
                    inThrottle = false;
                }, limit);
            }
        };
    };

    // Initialize all global functionality
    ready(function() {
        initSidebar();
        initUserDropdown();
        initAlerts();
        initModals();
    });

})();
