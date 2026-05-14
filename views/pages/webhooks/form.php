<div class="page-header">
    <div class="page-header-content">
        <h1 class="page-title">
            <?php echo $isEdit ? __('webhooks.edit_webhook', 'Edit Webhook') : __('webhooks.create_webhook', 'Create Webhook'); ?>
        </h1>
        <p class="page-description">
            <?php echo $isEdit 
                ? __('webhooks.edit_description', 'Update your webhook configuration') 
                : __('webhooks.create_description', 'Configure a new webhook endpoint to receive event notifications'); ?>
        </p>
    </div>
    <div class="page-actions">
        <a href="/webhooks" class="btn btn-ghost">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            <?php echo __('common.back', 'Back'); ?>
        </a>
    </div>
</div>

<div class="form-container">
    <form action="<?php echo $isEdit ? '/webhooks/' . $webhook['id'] : '/webhooks'; ?>" method="POST" class="webhook-form card">
        
        <!-- Webhook URL -->
        <div class="form-section">
            <h3 class="form-section-title"><?php echo __('webhooks.endpoint_config', 'Endpoint Configuration'); ?></h3>
            
            <div class="form-group">
                <label for="url" class="form-label required"><?php echo __('webhooks.webhook_url', 'Webhook URL'); ?></label>
                <input 
                    type="url" 
                    id="url" 
                    name="url" 
                    class="form-input" 
                    placeholder="https://your-domain.com/webhook"
                    value="<?php echo htmlspecialchars($webhook['url'] ?? ''); ?>"
                    required
                >
                <span class="form-hint">
                    <?php echo __('webhooks.url_hint', 'Must be an HTTPS URL that accepts POST requests with JSON payload'); ?>
                </span>
            </div>
            
            <div class="form-group">
                <label for="secret" class="form-label required"><?php echo __('webhooks.secret_key', 'Secret Key'); ?></label>
                <div class="input-with-button">
                    <input 
                        type="text" 
                        id="secret" 
                        name="secret" 
                        class="form-input" 
                        placeholder="Enter secret key for signature verification"
                        value="<?php echo htmlspecialchars($webhook['secret'] ?? $generatedSecret ?? ''); ?>"
                        minlength="16"
                        maxlength="64"
                        required
                    >
                    <button type="button" class="btn btn-ghost" id="generateSecretBtn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"></path>
                        </svg>
                        <?php echo __('webhooks.generate', 'Generate'); ?>
                    </button>
                </div>
                <span class="form-hint">
                    <?php echo __('webhooks.secret_hint', 'Used to sign webhook payloads. Keep this secret and use it to verify signatures.'); ?>
                </span>
            </div>
        </div>
        
        <!-- Events Selection -->
        <div class="form-section">
            <h3 class="form-section-title"><?php echo __('webhooks.event_selection', 'Event Selection'); ?></h3>
            <p class="form-section-description">
                <?php echo __('webhooks.event_selection_hint', 'Select which events should trigger this webhook'); ?>
            </p>
            
            <div class="events-grid">
                <?php foreach ($availableEvents as $eventKey => $eventLabel): ?>
                <label class="event-checkbox-card">
                    <input 
                        type="checkbox" 
                        name="events[]" 
                        value="<?php echo htmlspecialchars($eventKey); ?>"
                        <?php echo ($webhook && in_array($eventKey, $webhook['events'] ?? [])) ? 'checked' : ''; ?>
                    >
                    <div class="event-checkbox-content">
                        <div class="event-checkbox-icon">
                            <?php echo getEventIcon($eventKey); ?>
                        </div>
                        <div class="event-checkbox-info">
                            <span class="event-checkbox-label"><?php echo htmlspecialchars($eventLabel); ?></span>
                            <span class="event-checkbox-key"><?php echo htmlspecialchars($eventKey); ?></span>
                        </div>
                        <div class="event-checkbox-check">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </div>
                    </div>
                </label>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Status -->
        <div class="form-section">
            <h3 class="form-section-title"><?php echo __('webhooks.status', 'Status'); ?></h3>
            
            <label class="toggle-switch">
                <input 
                    type="checkbox" 
                    name="is_active" 
                    value="1"
                    <?php echo (!$isEdit || ($webhook['is_active'] ?? false)) ? 'checked' : ''; ?>
                >
                <span class="toggle-slider"></span>
                <span class="toggle-label"><?php echo __('webhooks.active_toggle', 'Webhook is active'); ?></span>
            </label>
            <span class="form-hint">
                <?php echo __('webhooks.active_hint', 'Inactive webhooks will not receive any notifications'); ?>
            </span>
        </div>
        
        <!-- Signature Verification Info -->
        <div class="form-section info-section">
            <h3 class="form-section-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
                <?php echo __('webhooks.signature_verification', 'Signature Verification'); ?>
            </h3>
            <p class="form-section-description">
                <?php echo __('webhooks.signature_info', 'Each webhook request includes an X-Webhook-Signature header. Verify it using this code:'); ?>
            </p>
            <pre class="code-block"><code>// PHP Example
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'] ?? '';

// Remove 'sha256=' prefix
$receivedSignature = str_replace('sha256=', '', $signature);

// Calculate expected signature
$expectedSignature = hash_hmac('sha256', $payload, $yourSecretKey);

// Verify
if (hash_equals($expectedSignature, $receivedSignature)) {
    // Signature valid
} else {
    // Invalid signature - reject request
}</code></pre>
        </div>
        
        <!-- Form Actions -->
        <div class="form-actions">
            <a href="/webhooks" class="btn btn-ghost"><?php echo __('common.cancel', 'Cancel'); ?></a>
            <button type="submit" class="btn btn-primary">
                <?php echo $isEdit ? __('webhooks.save_changes', 'Save Changes') : __('webhooks.create_webhook', 'Create Webhook'); ?>
            </button>
        </div>
    </form>
</div>

<?php
function getEventIcon($event) {
    $icons = [
        'deposit_approved' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>',
        'deposit_rejected' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>',
        'low_balance' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>',
        'key_quota_warning' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"></path></svg>',
        'plan_expired' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>'
    ];
    
    return $icons[$event] ?? '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>';
}
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Generate secret key
    document.getElementById('generateSecretBtn').addEventListener('click', function() {
        const secretInput = document.getElementById('secret');
        const array = new Uint8Array(32);
        crypto.getRandomValues(array);
        const secret = Array.from(array, byte => byte.toString(16).padStart(2, '0')).join('');
        secretInput.value = secret;
    });
    
    // Form validation
    document.querySelector('.webhook-form').addEventListener('submit', function(e) {
        const checkedEvents = document.querySelectorAll('input[name="events[]"]:checked');
        if (checkedEvents.length === 0) {
            e.preventDefault();
            alert('<?php echo __('webhooks.select_event_error', 'Please select at least one event'); ?>');
        }
    });
});
</script>
