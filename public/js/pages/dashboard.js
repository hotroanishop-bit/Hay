/**
 * Dashboard Page JavaScript
 * Handles dashboard interactivity: stat updates, activity refresh, chart rendering
 */

(function() {
    'use strict';

    // Dashboard module
    var Dashboard = {
        // Configuration
        config: {
            refreshInterval: 60000, // 1 minute
            chartColors: {
                primary: '#4f46e5',
                success: '#10b981',
                warning: '#f59e0b',
                info: '#3b82f6',
                gray: '#9ca3af'
            }
        },

        // Initialize dashboard
        init: function() {
            this.initUsageChart();
            this.bindEvents();
            this.startAutoRefresh();
        },

        // Initialize the usage chart
        initUsageChart: function() {
            var chartContainer = document.getElementById('usage-chart');
            if (!chartContainer) return;

            var statsData = chartContainer.getAttribute('data-stats');
            if (!statsData) return;

            try {
                var stats = JSON.parse(statsData);
                this.renderUsageChart(chartContainer, stats);
            } catch (e) {
                console.error('Failed to parse chart data:', e);
            }
        },

        // Render usage chart (placeholder - can integrate with Chart.js later)
        renderUsageChart: function(container, stats) {
            if (!stats || stats.length === 0) {
                container.innerHTML = '<p class="text-muted">No usage data available</p>';
                return;
            }

            // Create simple bar chart visualization
            var maxValue = Math.max.apply(null, stats.map(function(s) { 
                return s.requests || s.total_requests || 0; 
            }));
            
            if (maxValue === 0) {
                container.innerHTML = '<p class="text-muted">No usage data available</p>';
                return;
            }

            var chartHtml = '<div class="simple-chart">';
            
            stats.forEach(function(stat) {
                var value = stat.requests || stat.total_requests || 0;
                var percentage = (value / maxValue) * 100;
                var label = stat.date || stat.day || '';
                
                // Format date label
                if (label) {
                    var date = new Date(label);
                    label = date.toLocaleDateString('en-US', { weekday: 'short' });
                }
                
                chartHtml += '<div class="chart-bar-wrapper">';
                chartHtml += '<div class="chart-bar" style="height: ' + percentage + '%"></div>';
                chartHtml += '<span class="chart-label">' + label + '</span>';
                chartHtml += '</div>';
            });
            
            chartHtml += '</div>';
            
            // Add chart styles
            chartHtml += '<style>';
            chartHtml += '.simple-chart { display: flex; align-items: flex-end; gap: 8px; height: 180px; padding-bottom: 24px; }';
            chartHtml += '.chart-bar-wrapper { flex: 1; display: flex; flex-direction: column; align-items: center; height: 100%; position: relative; }';
            chartHtml += '.chart-bar { width: 100%; max-width: 40px; background: linear-gradient(to top, #4f46e5, #818cf8); border-radius: 4px 4px 0 0; min-height: 4px; transition: height 0.3s ease; }';
            chartHtml += '.chart-label { position: absolute; bottom: -20px; font-size: 11px; color: #6b7280; }';
            chartHtml += '</style>';
            
            container.innerHTML = chartHtml;
        },

        // Bind event handlers
        bindEvents: function() {
            // Quick action buttons could have additional behavior
            var quickActions = document.querySelector('.quick-actions');
            if (quickActions) {
                quickActions.addEventListener('click', function(e) {
                    var link = e.target.closest('a');
                    if (link && link.classList.contains('btn')) {
                        // Track click or add loading state
                        link.classList.add('loading');
                    }
                });
            }
        },

        // Start auto-refresh for stats
        startAutoRefresh: function() {
            var self = this;
            
            // Only auto-refresh if user is actively viewing the page
            document.addEventListener('visibilitychange', function() {
                if (document.visibilityState === 'visible') {
                    self.refreshStats();
                }
            });
        },

        // Refresh dashboard stats via AJAX
        refreshStats: function() {
            var self = this;
            
            // Check if fetch is available
            if (typeof fetch !== 'function') return;
            
            fetch('/api/dashboard/stats', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
            .then(function(response) {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(function(data) {
                self.updateStats(data);
            })
            .catch(function(error) {
                console.log('Stats refresh skipped:', error.message);
            });
        },

        // Update stat cards with new data
        updateStats: function(data) {
            if (!data) return;

            // Update stat values with animation
            var statMappings = {
                'total-keys': data.totalKeys,
                'active-keys': data.activeKeys,
                'balance': data.balance,
                'total-requests': data.totalRequests
            };

            for (var key in statMappings) {
                if (statMappings.hasOwnProperty(key) && statMappings[key] !== undefined) {
                    this.animateStatValue(key, statMappings[key]);
                }
            }
        },

        // Animate stat value change
        animateStatValue: function(statId, newValue) {
            var element = document.querySelector('[data-stat="' + statId + '"] .stat-value');
            if (!element) return;

            element.classList.add('updating');
            
            // Format the value
            var formattedValue = newValue;
            if (statId === 'balance') {
                formattedValue = '$' + parseFloat(newValue).toFixed(2);
            } else if (typeof newValue === 'number') {
                formattedValue = newValue.toLocaleString();
            }

            setTimeout(function() {
                element.textContent = formattedValue;
                element.classList.remove('updating');
            }, 150);
        },

        // Copy API key to clipboard
        copyApiKey: function(elementId) {
            var element = document.getElementById(elementId);
            if (!element) return;

            var text = element.textContent || element.innerText;
            
            if (typeof copyToClipboard === 'function') {
                copyToClipboard(text, function(success) {
                    if (success) {
                        window.showToast && showToast('API key copied to clipboard!', 'success');
                    } else {
                        window.showToast && showToast('Failed to copy API key', 'error');
                    }
                });
            }
        }
    };

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            Dashboard.init();
        });
    } else {
        Dashboard.init();
    }

    // Expose for external use if needed
    window.Dashboard = Dashboard;

})();
