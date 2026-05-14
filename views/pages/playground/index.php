<div class="content-wrapper">
    <div class="page-header">
        <div>
            <h1><?php echo __('playground.title', 'API Playground'); ?></h1>
            <p><?php echo __('playground.subtitle', 'Test your API keys with interactive requests'); ?></p>
        </div>
        <div class="balance-display">
            <span class="balance-label"><?php echo __('playground.balance', 'Balance'); ?>:</span>
            <span class="balance-value" id="user-balance">$<?php echo number_format($balance, 4); ?></span>
        </div>
    </div>

    <div class="playground-container">
        <!-- Left Panel: Input -->
        <div class="playground-panel playground-input">
            <div class="panel-header">
                <h3><?php echo __('playground.request', 'Request'); ?></h3>
            </div>
            
            <form id="playground-form" class="playground-form">
                <!-- API Key Selector -->
                <div class="form-group">
                    <label for="api-key-select"><?php echo __('playground.api_key', 'API Key'); ?></label>
                    <select id="api-key-select" name="api_key_id" required class="form-control">
                        <option value=""><?php echo __('playground.select_key', 'Select an API key...'); ?></option>
                        <?php foreach ($apiKeys as $key): ?>
                        <option value="<?php echo htmlspecialchars($key['id']); ?>">
                            <?php echo htmlspecialchars($key['name'] ?: 'Key #' . $key['id']); ?>
                            <?php if ($key['allowed_models']): ?>
                                (<?php echo __('playground.restricted', 'restricted'); ?>)
                            <?php endif; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (empty($apiKeys)): ?>
                    <p class="form-hint text-warning">
                        <?php echo __('playground.no_keys', 'No active API keys found.'); ?>
                        <a href="/keys/create"><?php echo __('playground.create_key', 'Create one'); ?></a>
                    </p>
                    <?php endif; ?>
                </div>

                <!-- Model Selector -->
                <div class="form-group">
                    <label for="model-select"><?php echo __('playground.model', 'Model'); ?></label>
                    <select id="model-select" name="model" required class="form-control">
                        <option value=""><?php echo __('playground.select_model', 'Select a model...'); ?></option>
                        <?php foreach ($models as $model): ?>
                        <option value="<?php echo htmlspecialchars($model); ?>" 
                                data-input-price="<?php echo htmlspecialchars($modelPricing[$model]['input_price_per_1k'] ?? 0); ?>"
                                data-output-price="<?php echo htmlspecialchars($modelPricing[$model]['output_price_per_1k'] ?? 0); ?>">
                            <?php echo htmlspecialchars($model); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- System Prompt -->
                <div class="form-group">
                    <label for="system-prompt">
                        <?php echo __('playground.system_prompt', 'System Prompt'); ?>
                        <span class="optional">(<?php echo __('common.optional', 'optional'); ?>)</span>
                    </label>
                    <textarea id="system-prompt" name="system_prompt" class="form-control code-textarea" rows="3"
                              placeholder="<?php echo __('playground.system_prompt_placeholder', 'You are a helpful assistant...'); ?>"></textarea>
                </div>

                <!-- User Message -->
                <div class="form-group">
                    <label for="user-message"><?php echo __('playground.user_message', 'User Message'); ?></label>
                    <textarea id="user-message" name="user_message" class="form-control code-textarea" rows="5" required
                              placeholder="<?php echo __('playground.user_message_placeholder', 'Enter your message here...'); ?>"></textarea>
                    <div class="form-hint">
                        <span id="char-count">0</span> <?php echo __('playground.characters', 'characters'); ?> |
                        <span id="token-estimate">~0</span> <?php echo __('playground.tokens_estimate', 'tokens (estimated)'); ?>
                    </div>
                </div>

                <!-- Parameters -->
                <div class="form-group parameters-group">
                    <label><?php echo __('playground.parameters', 'Parameters'); ?></label>
                    
                    <div class="parameter-row">
                        <div class="parameter-item">
                            <label for="temperature" class="param-label">
                                <?php echo __('playground.temperature', 'Temperature'); ?>
                                <span class="param-value" id="temp-value">1.0</span>
                            </label>
                            <input type="range" id="temperature" name="temperature" 
                                   min="0" max="2" step="0.1" value="1" class="slider">
                            <div class="slider-labels">
                                <span><?php echo __('playground.precise', 'Precise'); ?></span>
                                <span><?php echo __('playground.creative', 'Creative'); ?></span>
                            </div>
                        </div>
                        
                        <div class="parameter-item">
                            <label for="max-tokens" class="param-label">
                                <?php echo __('playground.max_tokens', 'Max Tokens'); ?>
                                <span class="param-value" id="tokens-value">1024</span>
                            </label>
                            <input type="range" id="max-tokens" name="max_tokens" 
                                   min="1" max="4096" step="1" value="1024" class="slider">
                            <div class="slider-labels">
                                <span>1</span>
                                <span>4096</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cost Estimate -->
                <div class="cost-estimate">
                    <div class="cost-row">
                        <span><?php echo __('playground.estimated_cost', 'Estimated Cost'); ?>:</span>
                        <span id="cost-estimate" class="cost-value">$0.0000</span>
                    </div>
                    <p class="cost-hint"><?php echo __('playground.cost_hint', 'Based on input tokens and max output tokens'); ?></p>
                </div>

                <!-- Submit Button -->
                <div class="form-actions">
                    <button type="submit" id="send-btn" class="btn btn-primary btn-block" <?php echo empty($apiKeys) ? 'disabled' : ''; ?>>
                        <span class="btn-text">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="22" y1="2" x2="11" y2="13"></line>
                                <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                            </svg>
                            <?php echo __('playground.send', 'Send Request'); ?>
                        </span>
                        <span class="btn-loading" style="display: none;">
                            <svg class="spinner" viewBox="0 0 24 24" width="18" height="18">
                                <circle class="spinner-circle" cx="12" cy="12" r="10" fill="none" stroke-width="3"></circle>
                            </svg>
                            <?php echo __('playground.sending', 'Sending...'); ?>
                        </span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Right Panel: Output -->
        <div class="playground-panel playground-output">
            <div class="panel-header">
                <h3><?php echo __('playground.response', 'Response'); ?></h3>
                <div class="panel-actions">
                    <button type="button" id="copy-response" class="btn btn-sm btn-outline" disabled>
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                        </svg>
                        <?php echo __('playground.copy', 'Copy'); ?>
                    </button>
                    <button type="button" id="toggle-raw" class="btn btn-sm btn-outline" disabled>
                        <?php echo __('playground.toggle_raw', 'Raw JSON'); ?>
                    </button>
                </div>
            </div>
            
            <!-- Response Display -->
            <div id="response-container" class="response-container">
                <div id="response-placeholder" class="response-placeholder">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    </svg>
                    <p><?php echo __('playground.waiting', 'Send a request to see the response here'); ?></p>
                </div>
                <div id="response-text" class="response-text" style="display: none;"></div>
                <pre id="response-raw" class="response-raw" style="display: none;"><code></code></pre>
            </div>

            <!-- Response Stats -->
            <div id="response-stats" class="response-stats" style="display: none;">
                <div class="stat-item">
                    <span class="stat-label"><?php echo __('playground.input_tokens', 'Input Tokens'); ?></span>
                    <span class="stat-value" id="stat-input-tokens">0</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label"><?php echo __('playground.output_tokens', 'Output Tokens'); ?></span>
                    <span class="stat-value" id="stat-output-tokens">0</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label"><?php echo __('playground.total_tokens', 'Total Tokens'); ?></span>
                    <span class="stat-value" id="stat-total-tokens">0</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label"><?php echo __('playground.cost', 'Cost'); ?></span>
                    <span class="stat-value" id="stat-cost">$0.0000</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label"><?php echo __('playground.latency', 'Latency'); ?></span>
                    <span class="stat-value" id="stat-latency">0ms</span>
                </div>
            </div>

            <!-- Error Display -->
            <div id="response-error" class="response-error" style="display: none;">
                <div class="error-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                </div>
                <div class="error-content">
                    <h4><?php echo __('playground.error', 'Error'); ?></h4>
                    <p id="error-message"></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Model pricing data for JavaScript -->
<script>
    window.playgroundConfig = {
        modelPricing: <?php echo json_encode($modelPricing); ?>,
        userBalance: <?php echo json_encode((float)$balance); ?>
    };
</script>
