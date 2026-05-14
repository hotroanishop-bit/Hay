/**
 * Analytics Page JavaScript
 * Handles chart initialization, date range pickers, data export
 */

(function() {
    'use strict';

    // Analytics module
    var Analytics = {
        // Configuration
        config: {
            chartColors: {
                primary: '#4f46e5',
                primaryLight: '#818cf8',
                success: '#10b981',
                warning: '#f59e0b',
                info: '#3b82f6',
                gray: '#9ca3af',
                grayLight: '#e5e7eb'
            }
        },

        // Initialize analytics
        init: function() {
            this.initDailyUsageChart();
            this.initHourlyChart();
            this.initDateRangePicker();
            this.initExportButtons();
        },

        // Initialize daily usage chart
        initDailyUsageChart: function() {
            var chartContainer = document.getElementById('daily-usage-chart');
            if (!chartContainer) return;

            var statsData = chartContainer.getAttribute('data-stats');
            if (!statsData) return;

            try {
                var stats = JSON.parse(statsData);
                this.renderDailyChart(chartContainer, stats);
            } catch (e) {
                console.error('Failed to parse daily chart data:', e);
                chartContainer.innerHTML = '<p class="text-muted">Failed to load chart data</p>';
            }
        },

        // Render daily usage chart
        renderDailyChart: function(container, stats) {
            if (!stats || stats.length === 0) {
                container.innerHTML = '<div class="chart-placeholder"><p class="text-muted">No usage data available for the selected period</p></div>';
                return;
            }

            var maxValue = Math.max.apply(null, stats.map(function(s) { 
                return s.requests || s.total_requests || 0; 
            }));
            
            if (maxValue === 0) maxValue = 1;

            var chartHtml = '<div class="line-chart">';
            chartHtml += '<div class="chart-y-axis">';
            chartHtml += '<span>' + this.formatNumber(maxValue) + '</span>';
            chartHtml += '<span>' + this.formatNumber(Math.round(maxValue / 2)) + '</span>';
            chartHtml += '<span>0</span>';
            chartHtml += '</div>';
            chartHtml += '<div class="chart-area">';
            chartHtml += '<div class="chart-bars">';
            
            var self = this;
            stats.forEach(function(stat, index) {
                var value = stat.requests || stat.total_requests || 0;
                var percentage = (value / maxValue) * 100;
                var label = stat.date || '';
                
                // Format date label
                if (label) {
                    var date = new Date(label);
                    label = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                }
                
                chartHtml += '<div class="chart-bar-col" title="' + label + ': ' + self.formatNumber(value) + ' requests">';
                chartHtml += '<div class="chart-bar" style="height: ' + percentage + '%"></div>';
                if (index % 5 === 0 || index === stats.length - 1) {
                    chartHtml += '<span class="chart-x-label">' + label + '</span>';
                }
                chartHtml += '</div>';
            });
            
            chartHtml += '</div>';
            chartHtml += '</div>';
            chartHtml += '</div>';
            
            // Add chart styles
            chartHtml += this.getDailyChartStyles();
            
            container.innerHTML = chartHtml;
        },

        // Get daily chart styles
        getDailyChartStyles: function() {
            return '<style>' +
                '.line-chart { display: flex; height: 280px; padding: 16px 0; }' +
                '.chart-y-axis { display: flex; flex-direction: column; justify-content: space-between; padding-right: 12px; font-size: 11px; color: #6b7280; text-align: right; min-width: 50px; }' +
                '.chart-area { flex: 1; display: flex; flex-direction: column; border-left: 1px solid #e5e7eb; border-bottom: 1px solid #e5e7eb; }' +
                '.chart-bars { flex: 1; display: flex; align-items: flex-end; gap: 2px; padding: 0 8px; }' +
                '.chart-bar-col { flex: 1; display: flex; flex-direction: column; align-items: center; height: 100%; position: relative; min-width: 0; }' +
                '.chart-bar-col .chart-bar { width: 100%; max-width: 24px; background: linear-gradient(to top, #4f46e5, #818cf8); border-radius: 3px 3px 0 0; min-height: 2px; transition: height 0.3s ease; }' +
                '.chart-bar-col:hover .chart-bar { background: linear-gradient(to top, #4338ca, #6366f1); }' +
                '.chart-x-label { position: absolute; bottom: -20px; font-size: 10px; color: #6b7280; white-space: nowrap; }' +
                '</style>';
        },

        // Initialize hourly distribution chart
        initHourlyChart: function() {
            var chartContainer = document.getElementById('hourly-chart');
            if (!chartContainer) return;

            var statsData = chartContainer.getAttribute('data-stats');
            if (!statsData) return;

            try {
                var stats = JSON.parse(statsData);
                this.renderHourlyChart(chartContainer, stats);
            } catch (e) {
                console.error('Failed to parse hourly chart data:', e);
                chartContainer.innerHTML = '<p class="text-muted">Failed to load chart data</p>';
            }
        },

        // Render hourly distribution chart
        renderHourlyChart: function(container, stats) {
            if (!stats || stats.length === 0) {
                container.innerHTML = '<div class="chart-placeholder"><p class="text-muted">No hourly data available</p></div>';
                return;
            }

            // Create 24-hour distribution
            var hourlyData = new Array(24).fill(0);
            stats.forEach(function(stat) {
                var hour = parseInt(stat.hour, 10);
                if (hour >= 0 && hour < 24) {
                    hourlyData[hour] = stat.requests || stat.total_requests || 0;
                }
            });

            var maxValue = Math.max.apply(null, hourlyData);
            if (maxValue === 0) maxValue = 1;

            var chartHtml = '<div class="hourly-chart">';
            
            for (var i = 0; i < 24; i++) {
                var value = hourlyData[i];
                var percentage = (value / maxValue) * 100;
                var label = i.toString().padStart(2, '0') + ':00';
                
                chartHtml += '<div class="hour-bar" title="' + label + ': ' + this.formatNumber(value) + ' requests">';
                chartHtml += '<div class="hour-fill" style="height: ' + percentage + '%"></div>';
                chartHtml += '</div>';
            }
            
            chartHtml += '</div>';
            chartHtml += '<div class="hourly-labels"><span>00:00</span><span>12:00</span><span>23:00</span></div>';
            
            // Add chart styles
            chartHtml += '<style>' +
                '.hourly-chart { display: flex; align-items: flex-end; gap: 2px; height: 150px; padding: 8px 0; border-bottom: 1px solid #e5e7eb; }' +
                '.hour-bar { flex: 1; height: 100%; display: flex; flex-direction: column; justify-content: flex-end; }' +
                '.hour-fill { width: 100%; background: #10b981; border-radius: 2px 2px 0 0; min-height: 2px; transition: height 0.3s ease; }' +
                '.hour-bar:hover .hour-fill { background: #059669; }' +
                '.hourly-labels { display: flex; justify-content: space-between; font-size: 10px; color: #6b7280; margin-top: 4px; }' +
                '</style>';
            
            container.innerHTML = chartHtml;
        },

        // Initialize date range picker
        initDateRangePicker: function() {
            var startDateInput = document.getElementById('start_date');
            var endDateInput = document.getElementById('end_date');
            
            if (!startDateInput || !endDateInput) return;

            // Set max date to today
            var today = new Date().toISOString().split('T')[0];
            startDateInput.setAttribute('max', today);
            endDateInput.setAttribute('max', today);

            // Validate date range on change
            startDateInput.addEventListener('change', function() {
                endDateInput.setAttribute('min', this.value);
                if (endDateInput.value && endDateInput.value < this.value) {
                    endDateInput.value = this.value;
                }
            });

            endDateInput.addEventListener('change', function() {
                startDateInput.setAttribute('max', this.value);
                if (startDateInput.value && startDateInput.value > this.value) {
                    startDateInput.value = this.value;
                }
            });
        },

        // Initialize export buttons
        initExportButtons: function() {
            var self = this;
            
            // Handle export link clicks
            document.querySelectorAll('a[href*="/analytics/export"]').forEach(function(link) {
                link.addEventListener('click', function(e) {
                    // Add loading state
                    var originalText = this.innerHTML;
                    this.innerHTML = '<span class="spinner"></span> Exporting...';
                    this.classList.add('disabled');
                    
                    // Reset after download starts (browser handles the file)
                    setTimeout(function() {
                        link.innerHTML = originalText;
                        link.classList.remove('disabled');
                    }, 2000);
                });
            });
        },

        // Format large numbers
        formatNumber: function(num) {
            if (num >= 1000000) {
                return (num / 1000000).toFixed(1) + 'M';
            }
            if (num >= 1000) {
                return (num / 1000).toFixed(1) + 'K';
            }
            return num.toString();
        },

        // Refresh analytics data
        refreshData: function() {
            var self = this;
            
            if (typeof fetch !== 'function') return;
            
            fetch('/api/analytics/stats', {
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
                // Update charts with new data
                if (data.dailyStats) {
                    var dailyContainer = document.getElementById('daily-usage-chart');
                    if (dailyContainer) {
                        self.renderDailyChart(dailyContainer, data.dailyStats);
                    }
                }
                if (data.hourlyStats) {
                    var hourlyContainer = document.getElementById('hourly-chart');
                    if (hourlyContainer) {
                        self.renderHourlyChart(hourlyContainer, data.hourlyStats);
                    }
                }
            })
            .catch(function(error) {
                console.log('Analytics refresh skipped:', error.message);
            });
        }
    };

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            Analytics.init();
        });
    } else {
        Analytics.init();
    }

    // Expose for external use if needed
    window.Analytics = Analytics;

})();
