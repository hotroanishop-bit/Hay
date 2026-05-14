/**
 * Admin Dashboard JavaScript
 * Dashboard-specific functionality and interactions
 */
(function() {
    'use strict';

    // Initialize dashboard when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        initializeDashboard();
    });

    /**
     * Initialize dashboard components
     */
    function initializeDashboard() {
        // Initialize tooltips
        initTooltips();
        
        // Initialize stat card animations
        animateStatCards();
        
        // Initialize auto-refresh for real-time data
        initAutoRefresh();
        
        // Initialize date range selector if present
        initDateRangeSelector();
    }

    /**
     * Initialize tooltips for elements with title attribute
     */
    function initTooltips() {
        var elements = document.querySelectorAll('[data-tooltip]');
        elements.forEach(function(el) {
            el.addEventListener('mouseenter', showTooltip);
            el.addEventListener('mouseleave', hideTooltip);
        });
    }

    function showTooltip(e) {
        var tooltip = document.createElement('div');
        tooltip.className = 'tooltip';
        tooltip.textContent = e.target.getAttribute('data-tooltip');
        document.body.appendChild(tooltip);
        
        var rect = e.target.getBoundingClientRect();
        tooltip.style.top = (rect.top - tooltip.offsetHeight - 8) + 'px';
        tooltip.style.left = (rect.left + rect.width / 2 - tooltip.offsetWidth / 2) + 'px';
    }

    function hideTooltip() {
        var tooltip = document.querySelector('.tooltip');
        if (tooltip) {
            tooltip.remove();
        }
    }

    /**
     * Animate stat cards on page load
     */
    function animateStatCards() {
        var statValues = document.querySelectorAll('.stat-value');
        
        statValues.forEach(function(el, index) {
            // Add staggered animation delay
            el.style.opacity = '0';
            el.style.transform = 'translateY(10px)';
            
            setTimeout(function() {
                el.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                el.style.opacity = '1';
                el.style.transform = 'translateY(0)';
            }, index * 50);
        });
    }

    /**
     * Initialize auto-refresh for dashboard data
     */
    function initAutoRefresh() {
        // Refresh dashboard every 5 minutes
        var refreshInterval = 5 * 60 * 1000;
        
        // Check if auto-refresh is enabled
        var autoRefreshEnabled = localStorage.getItem('dashboard_auto_refresh') !== 'false';
        
        if (autoRefreshEnabled) {
            setInterval(function() {
                // Only refresh if page is visible
                if (!document.hidden) {
                    refreshDashboardData();
                }
            }, refreshInterval);
        }
    }

    /**
     * Refresh dashboard data via AJAX
     */
    function refreshDashboardData() {
        // Show loading indicator
        showRefreshIndicator();
        
        fetch('/admin/dashboard/data', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function(response) {
            if (response.ok) {
                return response.json();
            }
            throw new Error('Network response was not ok');
        })
        .then(function(data) {
            updateDashboardStats(data);
            hideRefreshIndicator();
        })
        .catch(function(error) {
            console.error('Error refreshing dashboard:', error);
            hideRefreshIndicator();
        });
    }

    /**
     * Update dashboard stats with new data
     */
    function updateDashboardStats(data) {
        // Update stat values if data is provided
        if (data.totalUsers !== undefined) {
            updateStatValue('totalUsers', data.totalUsers);
        }
        if (data.activeUsersToday !== undefined) {
            updateStatValue('activeUsersToday', data.activeUsersToday);
        }
        if (data.apiCallsToday !== undefined) {
            updateStatValue('apiCallsToday', data.apiCallsToday);
        }
        if (data.pendingDeposits !== undefined) {
            updateStatValue('pendingDeposits', data.pendingDeposits);
        }
        if (data.pendingTickets !== undefined) {
            updateStatValue('pendingTickets', data.pendingTickets);
        }
    }

    /**
     * Update a single stat value with animation
     */
    function updateStatValue(statId, newValue) {
        var element = document.querySelector('[data-stat="' + statId + '"] .stat-value');
        if (!element) return;
        
        var currentValue = parseInt(element.textContent.replace(/[^0-9]/g, ''), 10) || 0;
        
        if (currentValue !== newValue) {
            // Add pulse animation
            element.classList.add('pulse');
            
            // Animate the number change
            animateNumber(element, currentValue, newValue, 500);
            
            // Remove pulse after animation
            setTimeout(function() {
                element.classList.remove('pulse');
            }, 600);
        }
    }

    /**
     * Animate a number from start to end
     */
    function animateNumber(element, start, end, duration) {
        var startTime = null;
        
        function animate(currentTime) {
            if (!startTime) startTime = currentTime;
            var progress = Math.min((currentTime - startTime) / duration, 1);
            
            var currentValue = Math.floor(progress * (end - start) + start);
            element.textContent = currentValue.toLocaleString();
            
            if (progress < 1) {
                requestAnimationFrame(animate);
            }
        }
        
        requestAnimationFrame(animate);
    }

    /**
     * Show refresh indicator
     */
    function showRefreshIndicator() {
        var indicator = document.getElementById('refreshIndicator');
        if (indicator) {
            indicator.style.display = 'flex';
        }
    }

    /**
     * Hide refresh indicator
     */
    function hideRefreshIndicator() {
        var indicator = document.getElementById('refreshIndicator');
        if (indicator) {
            indicator.style.display = 'none';
        }
    }

    /**
     * Initialize date range selector
     */
    function initDateRangeSelector() {
        var selector = document.getElementById('dateRangeSelector');
        if (!selector) return;
        
        selector.addEventListener('change', function(e) {
            var range = e.target.value;
            
            // Reload dashboard with new date range
            var url = new URL(window.location.href);
            url.searchParams.set('range', range);
            window.location.href = url.toString();
        });
    }

    /**
     * Export dashboard data to CSV
     */
    window.exportDashboardReport = function(type) {
        var url = '/admin/export/' + type + '?format=csv';
        
        // Add date range if specified
        var dateRange = document.getElementById('dateRangeSelector');
        if (dateRange) {
            url += '&range=' + dateRange.value;
        }
        
        window.location.href = url;
    };

    /**
     * Toggle stat card details
     */
    window.toggleStatDetails = function(cardId) {
        var details = document.getElementById(cardId + '-details');
        if (details) {
            details.classList.toggle('expanded');
        }
    };

})();
