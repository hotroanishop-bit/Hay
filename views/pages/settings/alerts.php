<div class="page-container">
    <div class="page-header">
        <h1 class="page-title">
            <span class="page-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                </svg>
            </span>
            <?php echo __('alerts.title', 'Canh bao su dung'); ?>
        </h1>
        <p class="page-description"><?php echo __('alerts.description', 'Cai dat canh bao khi so du thap hoac chi tieu vuot nguong'); ?></p>
    </div>

    <?php if (isset($_SESSION['flash'])): ?>
        <div class="alert alert-<?php echo $_SESSION['flash']['type']; ?>">
            <?php echo $_SESSION['flash']['message']; ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <form method="POST" action="/settings/alerts">
        <div class="card">
            <div class="card-body">
                <div class="alerts-list">
                    <?php foreach ($alertTypes as $type => $typeInfo): 
                        $alert = $alertsByType[$type] ?? null;
                        $isEnabled = $alert ? $alert['is_enabled'] : 0;
                        $threshold = $alert ? $alert['threshold'] : $typeInfo['default_threshold'];
                        $notifyEmail = $alert ? $alert['notify_email'] : 1;
                        $notifyTelegram = $alert ? $alert['notify_telegram'] : 0;
                    ?>
                        <div class="alert-item">
                            <div class="alert-header">
                                <div class="alert-info">
                                    <h3 class="alert-name"><?php echo $typeInfo['label']; ?></h3>
                                    <p class="alert-desc"><?php echo $typeInfo['description']; ?></p>
                                </div>
                                <label class="switch">
                                    <input type="checkbox" name="enabled_<?php echo $type; ?>" value="1" <?php echo $isEnabled ? 'checked' : ''; ?>>
                                    <span class="slider"></span>
                                </label>
                            </div>
                            
                            <div class="alert-settings">
                                <div class="form-group">
                                    <label class="form-label">Nguong (tokens/credits)</label>
                                    <input type="number" 
                                           name="threshold_<?php echo $type; ?>" 
                                           class="form-control" 
                                           value="<?php echo $threshold; ?>"
                                           step="0.01"
                                           min="0">
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Kenh thong bao</label>
                                    <div class="checkbox-group">
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="notify_email_<?php echo $type; ?>" value="1" <?php echo $notifyEmail ? 'checked' : ''; ?>>
                                            <span class="checkbox-label">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                                    <polyline points="22,6 12,13 2,6"></polyline>
                                                </svg>
                                                Email
                                            </span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="notify_telegram_<?php echo $type; ?>" value="1" <?php echo $notifyTelegram ? 'checked' : ''; ?>>
                                            <span class="checkbox-label">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                                                </svg>
                                                Telegram
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    <?php echo __('alerts.save', 'Luu cai dat'); ?>
                </button>
            </div>
        </div>
    </form>
</div>

<style>
.alerts-list {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.alert-item {
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    padding: 1.5rem;
}

.alert-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.alert-name {
    font-size: 1rem;
    margin-bottom: 0.25rem;
}

.alert-desc {
    font-size: 0.875rem;
    color: var(--text-secondary);
}

.alert-settings {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    padding-top: 1rem;
    border-top: 1px solid var(--border-color);
}

/* Toggle Switch */
.switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 26px;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: var(--bg-tertiary);
    transition: .4s;
    border-radius: 26px;
}

.slider:before {
    position: absolute;
    content: "";
    height: 20px;
    width: 20px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}

input:checked + .slider {
    background-color: var(--primary-color);
}

input:checked + .slider:before {
    transform: translateX(24px);
}

/* Checkbox Group */
.checkbox-group {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
}

.checkbox-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
}

.checkbox-item input {
    width: 18px;
    height: 18px;
    accent-color: var(--primary-color);
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.checkbox-label svg {
    color: var(--text-secondary);
}

.card-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid var(--border-color);
    background: var(--bg-tertiary);
}
</style>
