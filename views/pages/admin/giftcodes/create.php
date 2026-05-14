<div class="page-container">
    <div class="page-header">
        <h1 class="page-title">
            <a href="/admin/giftcodes" class="back-link">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
            </a>
            <?php echo __('admin.giftcodes.create_title', 'Tao Gift Code Moi'); ?>
        </h1>
    </div>

    <div class="row">
        <!-- Single Code Creation -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Tao 1 Code</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="/admin/giftcodes/store" id="singleCodeForm">
                        <div class="form-group">
                            <label class="form-label">Ma Code (de trong de tu tao)</label>
                            <input type="text" name="code" class="form-control" placeholder="VD: NEWYEAR2024" maxlength="50" style="text-transform: uppercase;">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Loai Gift Code <span class="text-danger">*</span></label>
                            <select name="type" class="form-control" required>
                                <option value="tokens">Tokens/Credits</option>
                                <option value="credits">Credits</option>
                                <option value="plan">Goi cuoc (Plan)</option>
                                <option value="vip_days">Ngay VIP</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Gia tri <span class="text-danger">*</span></label>
                            <input type="number" name="value" class="form-control" step="0.01" min="0" required placeholder="VD: 100">
                            <small class="form-text text-muted">Tokens/Credits: so luong | Plan: ID cua plan | VIP Days: so ngay</small>
                        </div>

                        <div class="form-group">
                            <label class="form-label">So lan su dung toi da</label>
                            <input type="number" name="max_uses" class="form-control" min="0" value="1" placeholder="0 = Khong gioi han">
                            <small class="form-text text-muted">Nhap 0 de khong gioi han</small>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Ngay het han</label>
                            <input type="datetime-local" name="expires_at" class="form-control">
                            <small class="form-text text-muted">De trong de khong gioi han thoi gian</small>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="20 12 20 22 4 22 4 12"></polyline>
                                    <rect x="2" y="7" width="20" height="5"></rect>
                                </svg>
                                Tao Gift Code
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Bulk Code Generation -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Tao Nhieu Codes</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="/admin/giftcodes/generate-bulk" id="bulkCodeForm">
                        <div class="form-group">
                            <label class="form-label">So luong codes <span class="text-danger">*</span></label>
                            <input type="number" name="count" class="form-control" min="1" max="100" required value="10" placeholder="1-100">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Prefix</label>
                            <input type="text" name="prefix" class="form-control" value="GIFT" maxlength="10" style="text-transform: uppercase;">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Do dai code (khong tinh prefix)</label>
                            <input type="number" name="length" class="form-control" min="4" max="20" value="8">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Loai Gift Code <span class="text-danger">*</span></label>
                            <select name="type" class="form-control" required>
                                <option value="tokens">Tokens/Credits</option>
                                <option value="credits">Credits</option>
                                <option value="plan">Goi cuoc (Plan)</option>
                                <option value="vip_days">Ngay VIP</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Gia tri moi code <span class="text-danger">*</span></label>
                            <input type="number" name="value" class="form-control" step="0.01" min="0" required placeholder="VD: 50">
                        </div>

                        <div class="form-group">
                            <label class="form-label">So lan su dung toi da (moi code)</label>
                            <input type="number" name="max_uses" class="form-control" min="0" value="1">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Ngay het han</label>
                            <input type="datetime-local" name="expires_at" class="form-control">
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-success">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                    <line x1="12" y1="8" x2="12" y2="16"></line>
                                    <line x1="8" y1="12" x2="16" y2="12"></line>
                                </svg>
                                Tao Hang Loat
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Generated Codes Result -->
    <div id="generatedCodesResult" class="card mt-4" style="display: none;">
        <div class="card-header">
            <h3 class="card-title">Codes Da Tao</h3>
            <button class="btn btn-sm btn-secondary" onclick="copyAllCodes()">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                </svg>
                Copy Tat Ca
            </button>
        </div>
        <div class="card-body">
            <div id="generatedCodesList" class="codes-grid"></div>
        </div>
    </div>
</div>

<style>
.row {
    display: flex;
    flex-wrap: wrap;
    margin: 0 -0.75rem;
}

.col-md-6 {
    flex: 0 0 50%;
    max-width: 50%;
    padding: 0 0.75rem;
}

@media (max-width: 768px) {
    .col-md-6 {
        flex: 0 0 100%;
        max-width: 100%;
        margin-bottom: 1rem;
    }
}

.back-link {
    color: var(--text-secondary);
    margin-right: 0.5rem;
}

.back-link:hover {
    color: var(--primary-color);
}

.form-group {
    margin-bottom: 1rem;
}

.form-label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
}

.form-text {
    font-size: 0.8rem;
    margin-top: 0.25rem;
}

.text-danger {
    color: #ef4444;
}

.text-muted {
    color: var(--text-secondary);
}

.form-actions {
    margin-top: 1.5rem;
    padding-top: 1rem;
    border-top: 1px solid var(--border-color);
}

.codes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 0.5rem;
}

.code-item {
    background: var(--bg-tertiary);
    padding: 0.5rem 0.75rem;
    border-radius: 4px;
    font-family: monospace;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.code-item button {
    background: none;
    border: none;
    cursor: pointer;
    color: var(--text-secondary);
    padding: 0.25rem;
}

.code-item button:hover {
    color: var(--primary-color);
}
</style>

<script>
document.getElementById('singleCodeForm').addEventListener('submit', function(e) {
    const codeInput = this.querySelector('input[name="code"]');
    if (codeInput.value) {
        codeInput.value = codeInput.value.toUpperCase();
    }
});

document.getElementById('bulkCodeForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = 'Dang tao...';

    try {
        const response = await fetch('/admin/giftcodes/generate-bulk', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success && data.codes) {
            const resultDiv = document.getElementById('generatedCodesResult');
            const codesList = document.getElementById('generatedCodesList');
            
            codesList.innerHTML = data.codes.map(item => `
                <div class="code-item">
                    <code>${item.code}</code>
                    <button onclick="copyCode('${item.code}')" title="Copy">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                        </svg>
                    </button>
                </div>
            `).join('');
            
            resultDiv.style.display = 'block';
            resultDiv.scrollIntoView({ behavior: 'smooth' });

            // Store codes for copy all
            window.generatedCodes = data.codes.map(item => item.code);
        } else {
            alert(data.message || 'Loi khi tao codes');
        }
    } catch (error) {
        alert('Loi ket noi');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="12" y1="8" x2="12" y2="16"></line>
                <line x1="8" y1="12" x2="16" y2="12"></line>
            </svg>
            Tao Hang Loat
        `;
    }
});

function copyCode(code) {
    navigator.clipboard.writeText(code).then(() => {
        showToast('Da copy: ' + code);
    });
}

function copyAllCodes() {
    if (window.generatedCodes && window.generatedCodes.length) {
        navigator.clipboard.writeText(window.generatedCodes.join('\n')).then(() => {
            showToast('Da copy ' + window.generatedCodes.length + ' codes');
        });
    }
}

function showToast(message) {
    const toast = document.createElement('div');
    toast.className = 'toast-notification';
    toast.textContent = message;
    toast.style.cssText = 'position: fixed; bottom: 20px; right: 20px; background: var(--primary-color); color: white; padding: 12px 24px; border-radius: 8px; z-index: 9999; animation: fadeIn 0.3s;';
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 2000);
}
</script>
