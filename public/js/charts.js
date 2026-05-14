/**
 * Simple Chart Components
 * Renders bar and line charts using CSS and SVG (no external dependencies)
 */
const ChartManager = (function() {
    'use strict';

    // Color palette for charts
    const colors = {
        primary: 'var(--color-primary, #3b82f6)',
        secondary: 'var(--color-secondary, #6b7280)',
        success: 'var(--color-success, #22c55e)',
        warning: 'var(--color-warning, #f59e0b)',
        error: 'var(--color-error, #ef4444)',
        info: 'var(--color-info, #3b82f6)'
    };

    /**
     * Render a vertical bar chart
     * @param {string} containerId - ID of container element
     * @param {Array} data - Array of {label, value} objects
     * @param {Object} options - Chart options
     */
    function renderBarChart(containerId, data, options) {
        options = options || {};
        const container = document.getElementById(containerId);
        if (!container) {
            console.warn('Chart container not found:', containerId);
            return;
        }

        if (!data || data.length === 0) {
            container.innerHTML = '<div class="chart-empty">No data available</div>';
            return;
        }

        const maxValue = Math.max.apply(null, data.map(function(d) { return d.value; }));
        const color = options.color || colors.primary;
        const height = options.height || 200;
        const showLabels = options.showLabels !== false;
        const showValues = options.showValues !== false;

        let html = '<div class="bar-chart" style="height: ' + height + 'px;">';
        html += '<div class="bar-chart-bars">';

        data.forEach(function(item, index) {
            const barHeight = maxValue > 0 ? (item.value / maxValue) * 100 : 0;
            const barColor = item.color || color;
            
            html += '<div class="bar-chart-bar-wrapper" style="width: ' + (100 / data.length) + '%;">';
            html += '<div class="bar-chart-bar" style="height: ' + barHeight + '%; background-color: ' + barColor + ';" title="' + escapeHtml(item.label) + ': ' + formatValue(item.value, options) + '">';
            
            if (showValues && barHeight > 15) {
                html += '<span class="bar-chart-value">' + formatValue(item.value, options) + '</span>';
            }
            
            html += '</div>';
            
            if (showLabels) {
                html += '<div class="bar-chart-label" title="' + escapeHtml(item.label) + '">' + escapeHtml(item.label) + '</div>';
            }
            
            html += '</div>';
        });

        html += '</div></div>';
        container.innerHTML = html;
    }

    /**
     * Render an SVG line chart
     * @param {string} containerId - ID of container element
     * @param {Array} data - Array of {label, value} objects
     * @param {Object} options - Chart options
     */
    function renderLineChart(containerId, data, options) {
        options = options || {};
        const container = document.getElementById(containerId);
        if (!container) {
            console.warn('Chart container not found:', containerId);
            return;
        }

        if (!data || data.length === 0) {
            container.innerHTML = '<div class="chart-empty">No data available</div>';
            return;
        }

        const width = options.width || container.offsetWidth || 400;
        const height = options.height || 200;
        const padding = options.padding || 40;
        const color = options.color || colors.primary;
        const showDots = options.showDots !== false;
        const showGrid = options.showGrid !== false;
        const showLabels = options.showLabels !== false;
        const filled = options.filled || false;

        const maxValue = Math.max.apply(null, data.map(function(d) { return d.value; }));
        const minValue = options.minValue !== undefined ? options.minValue : 0;
        const range = maxValue - minValue || 1;

        const chartWidth = width - padding * 2;
        const chartHeight = height - padding * 2;
        const stepX = chartWidth / (data.length - 1 || 1);

        // Build points
        const points = data.map(function(item, index) {
            const x = padding + index * stepX;
            const y = padding + chartHeight - ((item.value - minValue) / range) * chartHeight;
            return { x: x, y: y, value: item.value, label: item.label };
        });

        // Build path
        let pathD = 'M ' + points[0].x + ' ' + points[0].y;
        for (let i = 1; i < points.length; i++) {
            pathD += ' L ' + points[i].x + ' ' + points[i].y;
        }

        // Build filled area path
        let areaD = '';
        if (filled) {
            areaD = pathD + ' L ' + points[points.length - 1].x + ' ' + (padding + chartHeight);
            areaD += ' L ' + points[0].x + ' ' + (padding + chartHeight) + ' Z';
        }

        // Build SVG
        let svg = '<svg class="line-chart-svg" width="' + width + '" height="' + height + '" viewBox="0 0 ' + width + ' ' + height + '">';

        // Grid lines
        if (showGrid) {
            svg += '<g class="line-chart-grid">';
            for (let i = 0; i <= 4; i++) {
                const y = padding + (chartHeight / 4) * i;
                svg += '<line x1="' + padding + '" y1="' + y + '" x2="' + (width - padding) + '" y2="' + y + '" stroke="var(--border-color, #e5e7eb)" stroke-dasharray="4,4"/>';
            }
            svg += '</g>';
        }

        // Filled area
        if (filled) {
            svg += '<path class="line-chart-area" d="' + areaD + '" fill="' + color + '" fill-opacity="0.1"/>';
        }

        // Line
        svg += '<path class="line-chart-line" d="' + pathD + '" fill="none" stroke="' + color + '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>';

        // Dots
        if (showDots) {
            svg += '<g class="line-chart-dots">';
            points.forEach(function(point) {
                svg += '<circle cx="' + point.x + '" cy="' + point.y + '" r="4" fill="' + color + '" stroke="var(--surface-primary, #fff)" stroke-width="2">';
                svg += '<title>' + escapeHtml(point.label) + ': ' + formatValue(point.value, options) + '</title>';
                svg += '</circle>';
            });
            svg += '</g>';
        }

        svg += '</svg>';

        // Labels
        if (showLabels && data.length <= 12) {
            svg += '<div class="line-chart-labels">';
            points.forEach(function(point, index) {
                const labelWidth = 100 / data.length;
                svg += '<span class="line-chart-label" style="width: ' + labelWidth + '%;">' + escapeHtml(point.label) + '</span>';
            });
            svg += '</div>';
        }

        container.innerHTML = '<div class="line-chart">' + svg + '</div>';
    }

    /**
     * Render a donut/pie chart
     * @param {string} containerId - ID of container element
     * @param {Array} data - Array of {label, value, color} objects
     * @param {Object} options - Chart options
     */
    function renderDonutChart(containerId, data, options) {
        options = options || {};
        const container = document.getElementById(containerId);
        if (!container) {
            console.warn('Chart container not found:', containerId);
            return;
        }

        if (!data || data.length === 0) {
            container.innerHTML = '<div class="chart-empty">No data available</div>';
            return;
        }

        const size = options.size || 150;
        const strokeWidth = options.strokeWidth || 24;
        const radius = (size - strokeWidth) / 2;
        const circumference = 2 * Math.PI * radius;
        const centerX = size / 2;
        const centerY = size / 2;

        const total = data.reduce(function(sum, item) { return sum + item.value; }, 0);
        if (total === 0) {
            container.innerHTML = '<div class="chart-empty">No data available</div>';
            return;
        }

        const defaultColors = [colors.primary, colors.success, colors.warning, colors.error, colors.info, colors.secondary];
        let currentOffset = 0;

        let svg = '<svg class="donut-chart-svg" width="' + size + '" height="' + size + '" viewBox="0 0 ' + size + ' ' + size + '">';

        // Background circle
        svg += '<circle cx="' + centerX + '" cy="' + centerY + '" r="' + radius + '" fill="none" stroke="var(--border-color, #e5e7eb)" stroke-width="' + strokeWidth + '"/>';

        // Data segments
        data.forEach(function(item, index) {
            const percentage = item.value / total;
            const segmentLength = percentage * circumference;
            const segmentColor = item.color || defaultColors[index % defaultColors.length];

            svg += '<circle class="donut-segment" cx="' + centerX + '" cy="' + centerY + '" r="' + radius + '" fill="none" stroke="' + segmentColor + '" stroke-width="' + strokeWidth + '" stroke-dasharray="' + segmentLength + ' ' + circumference + '" stroke-dashoffset="' + (-currentOffset) + '" transform="rotate(-90 ' + centerX + ' ' + centerY + ')">';
            svg += '<title>' + escapeHtml(item.label) + ': ' + formatValue(item.value, options) + ' (' + Math.round(percentage * 100) + '%)</title>';
            svg += '</circle>';

            currentOffset += segmentLength;
        });

        // Center text
        if (options.centerText) {
            svg += '<text x="' + centerX + '" y="' + centerY + '" text-anchor="middle" dominant-baseline="middle" class="donut-center-text">' + escapeHtml(options.centerText) + '</text>';
        }

        svg += '</svg>';

        // Legend
        if (options.showLegend !== false) {
            svg += '<div class="donut-chart-legend">';
            data.forEach(function(item, index) {
                const segmentColor = item.color || defaultColors[index % defaultColors.length];
                const percentage = Math.round((item.value / total) * 100);
                svg += '<div class="donut-legend-item">';
                svg += '<span class="donut-legend-color" style="background-color: ' + segmentColor + ';"></span>';
                svg += '<span class="donut-legend-label">' + escapeHtml(item.label) + '</span>';
                svg += '<span class="donut-legend-value">' + percentage + '%</span>';
                svg += '</div>';
            });
            svg += '</div>';
        }

        container.innerHTML = '<div class="donut-chart">' + svg + '</div>';
    }

    /**
     * Render a horizontal progress bar
     * @param {string} containerId - ID of container element
     * @param {number} value - Current value
     * @param {number} max - Maximum value
     * @param {Object} options - Options
     */
    function renderProgressBar(containerId, value, max, options) {
        options = options || {};
        const container = document.getElementById(containerId);
        if (!container) return;

        const percentage = max > 0 ? Math.min((value / max) * 100, 100) : 0;
        const color = options.color || colors.primary;
        const showLabel = options.showLabel !== false;
        const label = options.label || Math.round(percentage) + '%';

        let html = '<div class="progress-bar-container">';
        html += '<div class="progress-bar-track">';
        html += '<div class="progress-bar-fill" style="width: ' + percentage + '%; background-color: ' + color + ';"></div>';
        html += '</div>';
        
        if (showLabel) {
            html += '<span class="progress-bar-label">' + escapeHtml(label) + '</span>';
        }
        
        html += '</div>';
        container.innerHTML = html;
    }

    /**
     * Format value for display
     * @param {number} value
     * @param {Object} options
     * @returns {string}
     */
    function formatValue(value, options) {
        if (options && options.formatValue) {
            return options.formatValue(value);
        }
        if (options && options.currency) {
            return new Intl.NumberFormat('en-US', { style: 'currency', currency: options.currency }).format(value);
        }
        if (options && options.percent) {
            return value.toFixed(1) + '%';
        }
        // Default: format with commas
        if (value >= 1000000) {
            return (value / 1000000).toFixed(1) + 'M';
        }
        if (value >= 1000) {
            return (value / 1000).toFixed(1) + 'K';
        }
        return value.toLocaleString();
    }

    /**
     * Escape HTML
     * @param {string} str
     * @returns {string}
     */
    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        const div = document.createElement('div');
        div.textContent = String(str);
        return div.innerHTML;
    }

    // Public API
    return {
        renderBarChart: renderBarChart,
        renderLineChart: renderLineChart,
        renderDonutChart: renderDonutChart,
        renderProgressBar: renderProgressBar,
        colors: colors
    };
})();
