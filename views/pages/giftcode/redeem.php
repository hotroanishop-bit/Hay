<div class="page-container">
    <div class="page-header">
        <h1 class="page-title">
            <span class="page-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 12 20 22 4 22 4 12"></polyline>
                    <rect x="2" y="7" width="20" height="5"></rect>
                    <line x1="12" y1="22" x2="12" y2="7"></line>
                    <path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"></path>
                    <path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"></path>
                </svg>
            </span>
            <?php echo __('giftcode.title', 'Nhap Gift Code'); ?>
        </h1>
        <p class="page-description"><?php echo __('giftcode.description', 'Nhap ma gift code de nhan token va cac phan thuong khac'); ?></p>
    </div>

    <!-- Redeem Form -->
    <div class="card giftcode-card">
        <div class="card-body">
            <div class="giftcode-input-container">
                <div class="giftcode-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 12 20 22 4 22 4 12"></polyline>
                        <rect x="2" y="7" width="20" height="5"></rect>
                        <line x1="12" y1="22" x2="12" y2="7"></line>
                        <path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"></path>
                        <path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"></path>
                    </svg>
                </div>
                
                <form id="redeemForm" class="giftcode-form">
                    <div class="form-group">
                        <label for="giftcode" class="form-label"><?php echo __('giftcode.enter_code', 'Nhap ma Gift Code'); ?></label>
                        <div class="input-group">
                            <input type="text" 
                                   id="giftcode" 
                                   name="code" 
                                   class="form-control giftcode-input" 
                                   placeholder="GIFT12345678"
                                   autocomplete="off"
                                   maxlength="50"
                                   required>
                            <button type="submit" class="btn btn-primary btn-redeem" id="redeemBtn">
                                <span class="btn-text"><?php echo __('giftcode.redeem', 'Doi Code'); ?></span>
                                <span class="btn-loading" style="display: none;">
                                    <svg class="spinner" width="20" height="20" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-dasharray="32" stroke-linecap="round">
                                            <animate attributeName="stroke-dashoffset" dur="1s" repeatCount="indefinite" from="0" to="64"/>
                                        </circle>
                                    </svg>
                                </span>
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Result Message -->
                <div id="resultMessage" class="result-message" style="display: none;">
                    <div class="result-icon"></div>
                    <div class="result-text"></div>
                </div>

                <!-- Success Animation -->
                <div id="successAnimation" class="success-animation" style="display: none;">
                    <div class="confetti"></div>
                    <div class="reward-display">
                        <span class="reward-icon">+</span>
                        <span class="reward-amount"></span>
                        <span class="reward-type"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- History Section -->
    <div class="card mt-4">
        <div class="card-header">
            <h3 class="card-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                <?php echo __('giftcode.history', 'Lich su doi code'); ?>
            </h3>
        </div>
        <div class="card-body">
            <?php if (empty($history)): ?>
                <div class="empty-state">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="empty-icon">
                        <polyline points="20 12 20 22 4 22 4 12"></polyline>
                        <rect x="2" y="7" width="20" height="5"></rect>
                        <line x1="12" y1="22" x2="12" y2="7"></line>
                    </svg>
                    <p><?php echo __('giftcode.no_history', 'Ban chua doi gift code nao'); ?></p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th><?php echo __('giftcode.code', 'Code'); ?></th>
                                <th><?php echo __('giftcode.type', 'Loai'); ?></th>
                                <th><?php echo __('giftcode.value_received', 'Nhan duoc'); ?></th>
                                <th><?php echo __('giftcode.redeemed_at', 'Thoi gian'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($history as $item): ?>
                                <tr>
                                    <td>
                                        <code class="code-badge"><?php echo htmlspecialchars($item['code']); ?></code>
                                    </td>
                                    <td>
                                        <?php
                                        $typeLabels = [
                                            'tokens' => '<span class="badge badge-primary">Tokens</span>',
                                            'credits' => '<span class="badge badge-success">Credits</span>',
                                            'plan' => '<span class="badge badge-info">Plan</span>',
                                            'vip_days' => '<span class="badge badge-warning">VIP Days</span>'
                                        ];
                                        echo $typeLabels[$item['type']] ?? '<span class="badge">' . $item['type'] . '</span>';
                                        ?>
                                    </td>
                                    <td>
                                        <strong class="text-success">+<?php echo number_format($item['value_received'], 2); ?></strong>
                                    </td>
                                    <td>
                                        <span class="text-muted"><?php echo date('d/m/Y H:i', strtotime($item['redeemed_at'])); ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.giftcode-card {
    max-width: 600px;
    margin: 0 auto;
}

.giftcode-input-container {
    text-align: center;
    padding: 2rem 1rem;
}

.giftcode-icon {
    margin-bottom: 1.5rem;
    color: var(--primary-color);
}

.giftcode-icon svg {
    animation: bounce 2s ease infinite;
}

@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

.giftcode-form {
    max-width: 400px;
    margin: 0 auto;
}

.giftcode-input {
    text-transform: uppercase;
    letter-spacing: 2px;
    font-weight: 600;
    font-size: 1.1rem;
    text-align: center;
    padding: 1rem;
}

.input-group {
    display: flex;
    gap: 0.5rem;
}

.input-group .form-control {
    flex: 1;
}

.btn-redeem {
    min-width: 120px;
    padding: 0.75rem 1.5rem;
}

.result-message {
    margin-top: 1.5rem;
    padding: 1rem;
    border-radius: var(--border-radius);
    display: flex;
    align-items: center;
    gap: 0.75rem;
    justify-content: center;
}

.result-message.success {
    background: rgba(16, 185, 129, 0.1);
    border: 1px solid rgba(16, 185, 129, 0.3);
    color: #10b981;
}

.result-message.error {
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.3);
    color: #ef4444;
}

.success-animation {
    margin-top: 2rem;
    padding: 2rem;
    text-align: center;
}

.reward-display {
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary-color);
    animation: pop 0.5s ease;
}

@keyframes pop {
    0% { transform: scale(0); opacity: 0; }
    50% { transform: scale(1.2); }
    100% { transform: scale(1); opacity: 1; }
}

.reward-icon {
    color: #10b981;
}

.code-badge {
    background: var(--bg-tertiary);
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.85rem;
}

.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    color: var(--text-secondary);
}

.empty-icon {
    opacity: 0.5;
    margin-bottom: 1rem;
}

.badge {
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
}

.badge-primary { background: var(--primary-color); color: white; }
.badge-success { background: #10b981; color: white; }
.badge-info { background: #3b82f6; color: white; }
.badge-warning { background: #f59e0b; color: white; }

/* Confetti animation */
.confetti {
    position: absolute;
    width: 100%;
    height: 100%;
    pointer-events: none;
}

@media (max-width: 576px) {
    .input-group {
        flex-direction: column;
    }
    
    .btn-redeem {
        width: 100%;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('redeemForm');
    const input = document.getElementById('giftcode');
    const btn = document.getElementById('redeemBtn');
    const resultMsg = document.getElementById('resultMessage');
    const successAnim = document.getElementById('successAnimation');

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const code = input.value.trim();
        if (!code) return;

        // Show loading
        btn.querySelector('.btn-text').style.display = 'none';
        btn.querySelector('.btn-loading').style.display = 'inline-flex';
        btn.disabled = true;
        resultMsg.style.display = 'none';
        successAnim.style.display = 'none';

        try {
            const formData = new FormData();
            formData.append('code', code);

            const response = await fetch('/giftcode/redeem', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                // Show success animation
                resultMsg.className = 'result-message success';
                resultMsg.innerHTML = `
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    <span>${data.message}</span>
                `;
                resultMsg.style.display = 'flex';

                // Show reward animation
                if (data.value) {
                    successAnim.innerHTML = `
                        <div class="reward-display">
                            <span class="reward-icon">+</span>
                            <span class="reward-amount">${parseFloat(data.value).toLocaleString()}</span>
                            <span class="reward-type">${data.type || 'credits'}</span>
                        </div>
                    `;
                    successAnim.style.display = 'block';
                }

                // Clear input
                input.value = '';

                // Update balance in header if exists
                if (data.new_balance !== undefined) {
                    const balanceEl = document.querySelector('.user-balance');
                    if (balanceEl) {
                        balanceEl.textContent = parseFloat(data.new_balance).toLocaleString() + ' credits';
                    }
                }

                // Reload after 2s to update history
                setTimeout(() => location.reload(), 2000);

            } else {
                resultMsg.className = 'result-message error';
                resultMsg.innerHTML = `
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                    </svg>
                    <span>${data.message}</span>
                `;
                resultMsg.style.display = 'flex';
            }

        } catch (error) {
            resultMsg.className = 'result-message error';
            resultMsg.innerHTML = `
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="15" y1="9" x2="9" y2="15"></line>
                    <line x1="9" y1="9" x2="15" y2="15"></line>
                </svg>
                <span>Loi ket noi, vui long thu lai</span>
            `;
            resultMsg.style.display = 'flex';
        } finally {
            btn.querySelector('.btn-text').style.display = 'inline';
            btn.querySelector('.btn-loading').style.display = 'none';
            btn.disabled = false;
        }
    });

    // Auto uppercase input
    input.addEventListener('input', function() {
        this.value = this.value.toUpperCase();
    });
});
</script>
