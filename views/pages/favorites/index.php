<div class="page-container">
    <div class="page-header">
        <h1 class="page-title">
            <span class="page-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                </svg>
            </span>
            <?php echo __('favorites.title', 'Model yeu thich'); ?>
        </h1>
        <p class="page-description"><?php echo __('favorites.description', 'Quan ly cac AI model yeu thich de truy cap nhanh'); ?></p>
    </div>

    <!-- Favorites List -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?php echo __('favorites.my_favorites', 'Model da luu'); ?></h3>
        </div>
        <div class="card-body">
            <?php if (empty($favorites)): ?>
                <div class="empty-state">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                    </svg>
                    <p><?php echo __('favorites.empty', 'Chua co model yeu thich nao'); ?></p>
                    <p class="text-muted"><?php echo __('favorites.empty_hint', 'Nhan vao icon sao de them model vao danh sach'); ?></p>
                </div>
            <?php else: ?>
                <div class="favorites-grid">
                    <?php foreach ($favorites as $fav): ?>
                        <div class="favorite-item" data-model-id="<?php echo htmlspecialchars($fav['model_id']); ?>">
                            <div class="favorite-info">
                                <span class="model-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                                        <path d="M2 17l10 5 10-5M2 12l10 5 10-5"></path>
                                    </svg>
                                </span>
                                <span class="model-name"><?php echo htmlspecialchars($fav['model_id']); ?></span>
                            </div>
                            <button class="btn-favorite active" onclick="toggleFavorite('<?php echo htmlspecialchars($fav['model_id']); ?>', this)" title="Xoa khoi yeu thich">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                </svg>
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Available Models -->
    <div class="card mt-4">
        <div class="card-header">
            <h3 class="card-title"><?php echo __('favorites.available_models', 'Model kha dung'); ?></h3>
        </div>
        <div class="card-body">
            <?php if (empty($models)): ?>
                <p class="text-muted"><?php echo __('favorites.no_models', 'Khong co model nao'); ?></p>
            <?php else: ?>
                <div class="models-grid">
                    <?php 
                    $favModelIds = array_column($favorites, 'model_id');
                    foreach ($models as $model): 
                        $isFavorite = in_array($model['model_id'], $favModelIds);
                    ?>
                        <div class="model-item">
                            <div class="model-info">
                                <span class="model-name"><?php echo htmlspecialchars($model['display_name'] ?? $model['model_id']); ?></span>
                                <span class="model-id text-muted"><?php echo htmlspecialchars($model['model_id']); ?></span>
                            </div>
                            <button class="btn-favorite <?php echo $isFavorite ? 'active' : ''; ?>" 
                                    onclick="toggleFavorite('<?php echo htmlspecialchars($model['model_id']); ?>', this)"
                                    title="<?php echo $isFavorite ? 'Xoa khoi yeu thich' : 'Them vao yeu thich'; ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="<?php echo $isFavorite ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="2">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                </svg>
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.empty-state {
    text-align: center;
    padding: 3rem;
    color: var(--text-secondary);
}

.empty-state svg {
    opacity: 0.5;
    margin-bottom: 1rem;
}

.favorites-grid, .models-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1rem;
}

.favorite-item, .model-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem;
    background: var(--bg-tertiary);
    border-radius: var(--border-radius);
    transition: transform 0.2s;
}

.favorite-item:hover, .model-item:hover {
    transform: translateX(4px);
}

.favorite-info, .model-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.model-info {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.25rem;
}

.model-icon {
    width: 36px;
    height: 36px;
    background: var(--primary-color);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.model-name {
    font-weight: 500;
}

.model-id {
    font-size: 0.8rem;
    font-family: monospace;
}

.btn-favorite {
    background: none;
    border: none;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 50%;
    color: var(--text-secondary);
    transition: all 0.2s;
}

.btn-favorite:hover {
    background: var(--bg-secondary);
    color: #f59e0b;
}

.btn-favorite.active {
    color: #f59e0b;
}

.btn-favorite.active svg {
    fill: currentColor;
}
</style>

<script>
async function toggleFavorite(modelId, btn) {
    btn.disabled = true;
    
    try {
        const formData = new FormData();
        formData.append('model_id', modelId);
        
        const response = await fetch('/favorites/toggle', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Toggle visual state
            if (data.action === 'added') {
                btn.classList.add('active');
                btn.querySelector('svg').setAttribute('fill', 'currentColor');
            } else {
                btn.classList.remove('active');
                btn.querySelector('svg').setAttribute('fill', 'none');
            }
            
            // Show toast
            showToast(data.message);
            
            // If removed from favorites list, remove the item
            if (data.action === 'removed') {
                const favItem = btn.closest('.favorite-item');
                if (favItem) {
                    favItem.style.opacity = '0';
                    setTimeout(() => favItem.remove(), 300);
                }
            }
        } else {
            showToast(data.message || 'Loi, vui long thu lai', 'error');
        }
    } catch (error) {
        showToast('Loi ket noi', 'error');
    } finally {
        btn.disabled = false;
    }
}

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = 'toast-notification ' + type;
    toast.textContent = message;
    toast.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        padding: 12px 24px;
        background: ${type === 'success' ? '#10b981' : '#ef4444'};
        color: white;
        border-radius: 8px;
        z-index: 9999;
        animation: fadeIn 0.3s;
    `;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 2000);
}
</script>
