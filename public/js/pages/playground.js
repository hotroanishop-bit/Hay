/**
 * API Playground JavaScript
 * Handles AJAX form submission, response display, token counting, and cost calculation
 */

(function() {
    'use strict';

    var Playground = {
        // Configuration
        config: {
            modelPricing: window.playgroundConfig ? window.playgroundConfig.modelPricing : {},
            userBalance: window.playgroundConfig ? window.playgroundConfig.userBalance : 0
        },

        // State
        state: {
            isLoading: false,
            showRaw: false,
            lastResponse: null
        },

        // DOM Elements
        elements: {},

        // Initialize playground
        init: function() {
            this.cacheElements();
            this.bindEvents();
            this.updateTokenEstimate();
            this.updateCostEstimate();
        },

        // Cache DOM elements
        cacheElements: function() {
            this.elements = {
                form: document.getElementById('playground-form'),
                apiKeySelect: document.getElementById('api-key-select'),
                modelSelect: document.getElementById('model-select'),
                systemPrompt: document.getElementById('system-prompt'),
                userMessage: document.getElementById('user-message'),
                temperature: document.getElementById('temperature'),
                maxTokens: document.getElementById('max-tokens'),
                tempValue: document.getElementById('temp-value'),
                tokensValue: document.getElementById('tokens-value'),
                charCount: document.getElementById('char-count'),
                tokenEstimate: document.getElementById('token-estimate'),
                costEstimate: document.getElementById('cost-estimate'),
                sendBtn: document.getElementById('send-btn'),
                responsePlaceholder: document.getElementById('response-placeholder'),
                responseText: document.getElementById('response-text'),
                responseRaw: document.getElementById('response-raw'),
                responseError: document.getElementById('response-error'),
                responseStats: document.getElementById('response-stats'),
                errorMessage: document.getElementById('error-message'),
                copyResponse: document.getElementById('copy-response'),
                toggleRaw: document.getElementById('toggle-raw'),
                userBalance: document.getElementById('user-balance'),
                statInputTokens: document.getElementById('stat-input-tokens'),
                statOutputTokens: document.getElementById('stat-output-tokens'),
                statTotalTokens: document.getElementById('stat-total-tokens'),
                statCost: document.getElementById('stat-cost'),
                statLatency: document.getElementById('stat-latency')
            };
        },

        // Bind event handlers
        bindEvents: function() {
            var self = this;

            // Form submission
            if (this.elements.form) {
                this.elements.form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    self.executeRequest();
                });
            }

            // Temperature slider
            if (this.elements.temperature) {
                this.elements.temperature.addEventListener('input', function() {
                    self.elements.tempValue.textContent = parseFloat(this.value).toFixed(1);
                });
            }

            // Max tokens slider
            if (this.elements.maxTokens) {
                this.elements.maxTokens.addEventListener('input', function() {
                    self.elements.tokensValue.textContent = this.value;
                    self.updateCostEstimate();
                });
            }

            // User message input
            if (this.elements.userMessage) {
                this.elements.userMessage.addEventListener('input', function() {
                    self.updateTokenEstimate();
                    self.updateCostEstimate();
                });
            }

            // System prompt input
            if (this.elements.systemPrompt) {
                this.elements.systemPrompt.addEventListener('input', function() {
                    self.updateTokenEstimate();
                    self.updateCostEstimate();
                });
            }

            // Model select
            if (this.elements.modelSelect) {
                this.elements.modelSelect.addEventListener('change', function() {
                    self.updateCostEstimate();
                });
            }

            // Copy response button
            if (this.elements.copyResponse) {
                this.elements.copyResponse.addEventListener('click', function() {
                    self.copyResponse();
                });
            }

            // Toggle raw JSON button
            if (this.elements.toggleRaw) {
                this.elements.toggleRaw.addEventListener('click', function() {
                    self.toggleRawView();
                });
            }
        },

        // Estimate token count (roughly chars/4)
        estimateTokens: function(text) {
            if (!text) return 0;
            return Math.ceil(text.length / 4);
        },

        // Update token estimate display
        updateTokenEstimate: function() {
            var systemText = this.elements.systemPrompt ? this.elements.systemPrompt.value : '';
            var userText = this.elements.userMessage ? this.elements.userMessage.value : '';
            var totalText = systemText + userText;

            var charCount = totalText.length;
            var tokenEstimate = this.estimateTokens(totalText);

            if (this.elements.charCount) {
                this.elements.charCount.textContent = charCount.toLocaleString();
            }
            if (this.elements.tokenEstimate) {
                this.elements.tokenEstimate.textContent = '~' + tokenEstimate.toLocaleString();
            }
        },

        // Update cost estimate display
        updateCostEstimate: function() {
            var modelSelect = this.elements.modelSelect;
            if (!modelSelect || !modelSelect.value) {
                if (this.elements.costEstimate) {
                    this.elements.costEstimate.textContent = '$0.0000';
                }
                return;
            }

            var model = modelSelect.value;
            var pricing = this.config.modelPricing[model];

            if (!pricing) {
                if (this.elements.costEstimate) {
                    this.elements.costEstimate.textContent = '$0.0000';
                }
                return;
            }

            // Calculate estimated input tokens
            var systemText = this.elements.systemPrompt ? this.elements.systemPrompt.value : '';
            var userText = this.elements.userMessage ? this.elements.userMessage.value : '';
            var inputTokens = this.estimateTokens(systemText + userText);

            // Get max output tokens
            var maxTokens = this.elements.maxTokens ? parseInt(this.elements.maxTokens.value) : 1024;

            // Calculate costs (per 1k tokens)
            var inputCost = (inputTokens / 1000) * pricing.input_price_per_1k;
            var outputCost = (maxTokens / 1000) * pricing.output_price_per_1k;
            var totalCost = inputCost + outputCost;

            if (this.elements.costEstimate) {
                this.elements.costEstimate.textContent = '$' + totalCost.toFixed(4);
            }
        },

        // Execute the API request
        executeRequest: function() {
            var self = this;

            if (this.state.isLoading) return;

            // Validate form
            var apiKeyId = this.elements.apiKeySelect ? this.elements.apiKeySelect.value : '';
            var model = this.elements.modelSelect ? this.elements.modelSelect.value : '';
            var userMessage = this.elements.userMessage ? this.elements.userMessage.value.trim() : '';

            if (!apiKeyId) {
                this.showError('Please select an API key');
                return;
            }

            if (!model) {
                this.showError('Please select a model');
                return;
            }

            if (!userMessage) {
                this.showError('Please enter a message');
                return;
            }

            // Build request data
            var requestData = {
                api_key_id: apiKeyId,
                model: model,
                user_message: userMessage,
                system_prompt: this.elements.systemPrompt ? this.elements.systemPrompt.value.trim() : '',
                temperature: this.elements.temperature ? parseFloat(this.elements.temperature.value) : 1.0,
                max_tokens: this.elements.maxTokens ? parseInt(this.elements.maxTokens.value) : 1024
            };

            // Show loading state
            this.setLoading(true);
            this.hideAllResponses();

            // Make AJAX request
            fetch('/playground/execute', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: JSON.stringify(requestData)
            })
            .then(function(response) {
                return response.json().then(function(data) {
                    return { ok: response.ok, status: response.status, data: data };
                });
            })
            .then(function(result) {
                self.setLoading(false);

                if (result.data.success) {
                    self.showResponse(result.data);
                } else {
                    self.showError(result.data.error || 'An error occurred');
                }
            })
            .catch(function(error) {
                self.setLoading(false);
                self.showError('Network error. Please check your connection and try again.');
                console.error('Playground request error:', error);
            });
        },

        // Set loading state
        setLoading: function(loading) {
            this.state.isLoading = loading;

            if (this.elements.sendBtn) {
                var btnText = this.elements.sendBtn.querySelector('.btn-text');
                var btnLoading = this.elements.sendBtn.querySelector('.btn-loading');

                if (loading) {
                    this.elements.sendBtn.disabled = true;
                    if (btnText) btnText.style.display = 'none';
                    if (btnLoading) btnLoading.style.display = 'flex';
                } else {
                    this.elements.sendBtn.disabled = false;
                    if (btnText) btnText.style.display = 'flex';
                    if (btnLoading) btnLoading.style.display = 'none';
                }
            }
        },

        // Hide all response displays
        hideAllResponses: function() {
            if (this.elements.responsePlaceholder) this.elements.responsePlaceholder.style.display = 'none';
            if (this.elements.responseText) this.elements.responseText.style.display = 'none';
            if (this.elements.responseRaw) this.elements.responseRaw.style.display = 'none';
            if (this.elements.responseError) this.elements.responseError.style.display = 'none';
            if (this.elements.responseStats) this.elements.responseStats.style.display = 'none';
        },

        // Show successful response
        showResponse: function(data) {
            this.state.lastResponse = data;
            this.state.showRaw = false;

            // Enable action buttons
            if (this.elements.copyResponse) this.elements.copyResponse.disabled = false;
            if (this.elements.toggleRaw) {
                this.elements.toggleRaw.disabled = false;
                this.elements.toggleRaw.classList.remove('active');
            }

            // Display response text
            if (this.elements.responseText) {
                this.elements.responseText.textContent = data.response_text || '';
                this.elements.responseText.style.display = 'block';
            }

            // Prepare raw JSON display
            if (this.elements.responseRaw) {
                var code = this.elements.responseRaw.querySelector('code');
                if (code) {
                    code.innerHTML = this.formatJSON(data.response);
                }
            }

            // Update stats
            if (data.usage) {
                if (this.elements.statInputTokens) {
                    this.elements.statInputTokens.textContent = (data.usage.input_tokens || 0).toLocaleString();
                }
                if (this.elements.statOutputTokens) {
                    this.elements.statOutputTokens.textContent = (data.usage.output_tokens || 0).toLocaleString();
                }
                if (this.elements.statTotalTokens) {
                    this.elements.statTotalTokens.textContent = (data.usage.total_tokens || 0).toLocaleString();
                }
            }

            if (this.elements.statCost) {
                this.elements.statCost.textContent = '$' + (data.cost || 0).toFixed(4);
            }

            if (this.elements.statLatency) {
                this.elements.statLatency.textContent = (data.latency_ms || 0) + 'ms';
            }

            // Show stats
            if (this.elements.responseStats) {
                this.elements.responseStats.style.display = 'grid';
            }

            // Update user balance
            if (data.new_balance !== undefined && this.elements.userBalance) {
                this.elements.userBalance.textContent = '$' + parseFloat(data.new_balance).toFixed(4);
                this.config.userBalance = data.new_balance;
            }

            // Show toast notification
            if (typeof window.showToast === 'function') {
                window.showToast('Request completed successfully', 'success');
            }
        },

        // Show error message
        showError: function(message) {
            this.hideAllResponses();

            if (this.elements.errorMessage) {
                this.elements.errorMessage.textContent = message;
            }

            if (this.elements.responseError) {
                this.elements.responseError.style.display = 'flex';
            }

            // Disable action buttons
            if (this.elements.copyResponse) this.elements.copyResponse.disabled = true;
            if (this.elements.toggleRaw) this.elements.toggleRaw.disabled = true;

            // Show toast notification
            if (typeof window.showToast === 'function') {
                window.showToast(message, 'error');
            }
        },

        // Toggle between formatted and raw JSON view
        toggleRawView: function() {
            this.state.showRaw = !this.state.showRaw;

            if (this.state.showRaw) {
                if (this.elements.responseText) this.elements.responseText.style.display = 'none';
                if (this.elements.responseRaw) this.elements.responseRaw.style.display = 'block';
                if (this.elements.toggleRaw) {
                    this.elements.toggleRaw.classList.add('active');
                    this.elements.toggleRaw.textContent = 'Formatted';
                }
            } else {
                if (this.elements.responseText) this.elements.responseText.style.display = 'block';
                if (this.elements.responseRaw) this.elements.responseRaw.style.display = 'none';
                if (this.elements.toggleRaw) {
                    this.elements.toggleRaw.classList.remove('active');
                    this.elements.toggleRaw.textContent = 'Raw JSON';
                }
            }
        },

        // Copy response to clipboard
        copyResponse: function() {
            if (!this.state.lastResponse) return;

            var textToCopy = this.state.showRaw
                ? JSON.stringify(this.state.lastResponse.response, null, 2)
                : this.state.lastResponse.response_text || '';

            if (typeof window.copyToClipboard === 'function') {
                window.copyToClipboard(textToCopy, function(success) {
                    if (success && typeof window.showToast === 'function') {
                        window.showToast('Response copied to clipboard', 'success');
                    }
                });
            } else {
                // Fallback for older browsers
                var textarea = document.createElement('textarea');
                textarea.value = textToCopy;
                textarea.style.position = 'fixed';
                textarea.style.opacity = '0';
                document.body.appendChild(textarea);
                textarea.select();
                try {
                    document.execCommand('copy');
                    if (typeof window.showToast === 'function') {
                        window.showToast('Response copied to clipboard', 'success');
                    }
                } catch (err) {
                    console.error('Copy failed:', err);
                }
                document.body.removeChild(textarea);
            }
        },

        // Format JSON with syntax highlighting
        formatJSON: function(obj) {
            if (!obj) return '';

            var json = JSON.stringify(obj, null, 2);

            // Apply syntax highlighting
            return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function(match) {
                var cls = 'json-number';
                if (/^"/.test(match)) {
                    if (/:$/.test(match)) {
                        cls = 'json-key';
                        match = match.slice(0, -1);
                        return '<span class="' + cls + '">' + escapeHtml(match) + '</span>:';
                    } else {
                        cls = 'json-string';
                    }
                } else if (/true|false/.test(match)) {
                    cls = 'json-boolean';
                } else if (/null/.test(match)) {
                    cls = 'json-null';
                }
                return '<span class="' + cls + '">' + escapeHtml(match) + '</span>';
            });
        }
    };

    // Helper function to escape HTML
    function escapeHtml(str) {
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            Playground.init();
        });
    } else {
        Playground.init();
    }

    // Expose for external use
    window.Playground = Playground;

})();
