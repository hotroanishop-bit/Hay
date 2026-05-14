/**
 * Health Dashboard JavaScript
 * Auto-refresh functionality for system health metrics
 */

(function() {
    'use strict';

    let autoRefreshInterval = null;
    const REFRESH_INTERVAL = 30000; // 30 seconds

    /**
     * Initialize health dashboard
     */
    function init() {
        const autoRefreshCheckbox = document.getElementById('autoRefresh');
        if (autoRefreshCheckbox) {
            autoRefreshCheckbox.addEventListener('change', toggleAutoRefresh);
            if (autoRefreshCheckbox.checked) {
                startAutoRefresh();
            }
        }
    }

    /**
     * Toggle auto-refresh
     */
    function toggleAutoRefresh(e) {
        if (e.target.checked) {
            startAutoRefresh();
        } else {
            stopAutoRefresh();
        }
    }

    /**
     * Start auto-refresh
     */
    function startAutoRefresh() {
        stopAutoRefresh();
        autoRefreshInterval = setInterval(refreshMetrics, REFRESH_INTERVAL);
    }

    /**
     * Stop auto-refresh
     */
    function stopAutoRefresh() {
        if (autoRefreshInterval) {
            clearInterval(autoRefreshInterval);
            autoRefreshInterval = null;
        }
    }

    /**
     * Refresh health metrics via AJAX
     */
    window.refreshMetrics = function() {
        const refreshBtn = document.getElementById('refreshBtn');
        if (refreshBtn) {
            refreshBtn.disabled = true;
            refreshBtn.innerHTML = '<i class="icon-refresh-cw spinning"></i> Refreshing...';
        }

        fetch('/admin/health/refresh', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.metrics) {
                updateMetrics(data.metrics);
            }
        })
        .catch(error => {
            console.error('Failed to refresh metrics:', error);
        })
        .finally(() => {
            if (refreshBtn) {
                refreshBtn.disabled = false;
                refreshBtn.innerHTML = '<i class="icon-refresh-cw"></i> Refresh';
            }
        });
    };

    /**
     * Update displayed metrics
     */
    function updateMetrics(metrics) {
        // Update last updated time
        const lastUpdated = document.getElementById('lastUpdated');
        if (lastUpdated) {
            lastUpdated.textContent = new Date().toLocaleTimeString();
        }

        // Update disk metrics
        if (metrics.disk) {
            updateGauge('disk', metrics.disk.percent);
            updateElement('diskPercent', metrics.disk.percent + '%');
            updateElement('diskUsed', metrics.disk.used_formatted);
            updateElement('diskFree', metrics.disk.free_formatted);
            updateElement('diskTotal', metrics.disk.total_formatted);
        }

        // Update memory metrics
        if (metrics.memory) {
            updateGauge('memory', metrics.memory.percent);
            updateElement('memoryPercent', metrics.memory.percent + '%');
            updateElement('memoryCurrent', metrics.memory.current_formatted);
            updateElement('memoryPeak', metrics.memory.peak_formatted);
            updateElement('memoryLimit', metrics.memory.limit_formatted);
        }

        // Update database metrics
        if (metrics.database) {
            updateElement('dbStatus', metrics.database.connected ? 'Connected' : 'Disconnected');
            updateElement('dbLatency', parseFloat(metrics.database.latency_ms || 0).toFixed(1));
            updateElement('dbHost', metrics.database.host);
            updateElement('dbName', metrics.database.database);
            updateElement('dbSize', metrics.database.size_formatted);
        }

        // Update server time
        if (metrics.server_time) {
            updateElement('serverTime', metrics.server_time);
        }

        // Update error log
        if (metrics.recent_errors && metrics.recent_errors.errors) {
            updateErrorLog(metrics.recent_errors.errors);
            updateElement('errorCount', metrics.recent_errors.errors.length + ' errors');
        }
    }

    /**
     * Update gauge visualization
     */
    function updateGauge(type, percent) {
        const gauge = document.querySelector('[data-value]');
        if (gauge) {
            gauge.setAttribute('data-value', percent);
        }

        const gaugeFill = document.querySelector('.' + type + '-gauge');
        if (gaugeFill) {
            gaugeFill.style.setProperty('--percent', percent);
        }
    }

    /**
     * Update element text content
     */
    function updateElement(id, value) {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = value;
        }
    }

    /**
     * Update error log display
     */
    function updateErrorLog(errors) {
        const errorLog = document.getElementById('errorLog');
        if (!errorLog) return;

        if (errors.length === 0) {
            errorLog.innerHTML = '<div class="empty-state-sm"><i class="icon-check-circle"></i><p>No recent errors found</p></div>';
            return;
        }

        let html = '';
        errors.reverse().forEach(error => {
            const level = (error.level || 'unknown').toLowerCase();
            const message = escapeHtml(error.message || '').substring(0, 500);
            const timestamp = escapeHtml(error.timestamp || '');
            
            html += '<div class="error-entry level-' + level + '">' +
                    '<div class="error-meta">' +
                    '<span class="error-level">' + escapeHtml(error.level || 'UNKNOWN') + '</span>' +
                    '<span class="error-time">' + timestamp + '</span>' +
                    '</div>' +
                    '<div class="error-message">' + message + '</div>' +
                    '</div>';
        });

        errorLog.innerHTML = html;
    }

    /**
     * Escape HTML entities
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
