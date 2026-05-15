<div class="page-header">
    <div class="page-header-content">
        <h1 class="page-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
            </svg>
            Danh gia dich vu
        </h1>
        <p class="page-description">Giup chung toi cai thien dich vu tot hon</p>
    </div>
</div>

<div class="feedback-container">
    <div class="feedback-grid">
        <!-- Feedback Form -->
        <div class="feedback-form-card">
            <div class="card-header">
                <h3>Gui danh gia</h3>
            </div>
            <form id="feedback-form" class="feedback-form">
                <!-- Category Select -->
                <div class="form-group">
                    <label for="category">Danh muc</label>
                    <select id="category" name="category" required>
                        <?php foreach ($categories as $value => $label): ?>
                            <option value="<?php echo $value; ?>"><?php echo htmlspecialchars($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Star Rating -->
                <div class="form-group">
                    <label>Danh gia</label>
                    <div class="star-rating" id="star-rating">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <button type="button" class="star-btn <?php echo $i <= 5 ? 'active' : ''; ?>" data-rating="<?php echo $i; ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                </svg>
                            </button>
                        <?php endfor; ?>
                    </div>
                    <input type="hidden" name="rating" id="rating" value="5">
                </div>

                <!-- Comment -->
                <div class="form-group">
                    <label for="comment">Nhan xet (tuy chon)</label>
                    <textarea id="comment" name="comment" rows="4" placeholder="Chia se y kien cua ban..."></textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="22" y1="2" x2="11" y2="13"></line>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                    </svg>
                    Gui danh gia
                </button>
            </form>
        </div>

        <!-- Stats & History -->
        <div class="feedback-stats">
            <!-- Average Ratings -->
            <div class="stats-card">
                <h4>Danh gia trung binh</h4>
                <div class="avg-ratings">
                    <?php if (!empty($avgRatings)): ?>
                        <?php foreach ($avgRatings as $avg): ?>
                            <div class="avg-item">
                                <span class="avg-category"><?php echo htmlspecialchars($categories[$avg['category']] ?? $avg['category']); ?></span>
                                <div class="avg-bar-container">
                                    <div class="avg-bar" style="width: <?php echo ($avg['avg_rating'] / 5) * 100; ?>%"></div>
                                </div>
                                <span class="avg-value"><?php echo number_format($avg['avg_rating'], 1); ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-data">Chua co danh gia</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- My Feedback History -->
            <div class="history-card">
                <h4>Danh gia cua ban</h4>
                <?php if (!empty($myFeedback)): ?>
                    <div class="feedback-list">
                        <?php foreach ($myFeedback as $fb): ?>
                            <div class="feedback-item">
                                <div class="feedback-item-header">
                                    <span class="feedback-category"><?php echo htmlspecialchars($categories[$fb['category']] ?? $fb['category']); ?></span>
                                    <span class="feedback-date"><?php echo date('d/m/Y', strtotime($fb['created_at'])); ?></span>
                                </div>
                                <div class="feedback-stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" 
                                             fill="<?php echo $i <= $fb['rating'] ? 'var(--warning-color)' : 'var(--text-muted)'; ?>" 
                                             stroke="none">
                                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                        </svg>
                                    <?php endfor; ?>
                                </div>
                                <?php if (!empty($fb['comment'])): ?>
                                    <p class="feedback-comment"><?php echo htmlspecialchars($fb['comment']); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="no-data">Ban chua gui danh gia nao</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.feedback-container {
    max-width: 1000px;
    margin: 0 auto;
}

.feedback-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--space-4);
}

@media (max-width: 768px) {
    .feedback-grid {
        grid-template-columns: 1fr;
    }
}

.feedback-form-card,
.stats-card,
.history-card {
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-xl);
    padding: var(--space-4);
}

.card-header {
    margin-bottom: var(--space-4);
}

.card-header h3 {
    margin: 0;
    font-size: var(--text-lg);
}

.form-group {
    margin-bottom: var(--space-4);
}

.form-group label {
    display: block;
    margin-bottom: var(--space-2);
    font-weight: 500;
    color: var(--text-secondary);
}

.form-group select,
.form-group textarea {
    width: 100%;
    padding: var(--space-3);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    background: var(--bg-secondary);
    color: var(--text-primary);
    font-size: var(--text-base);
}

.form-group textarea {
    resize: vertical;
    min-height: 100px;
}

.star-rating {
    display: flex;
    gap: var(--space-1);
}

.star-btn {
    padding: 0;
    border: none;
    background: transparent;
    cursor: pointer;
    color: var(--text-muted);
    transition: all 0.2s;
}

.star-btn.active {
    color: var(--warning-color);
}

.star-btn:hover {
    transform: scale(1.1);
}

.btn-block {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--space-2);
}

/* Stats */
.feedback-stats {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
}

.stats-card h4,
.history-card h4 {
    margin: 0 0 var(--space-3) 0;
    font-size: var(--text-base);
    color: var(--text-primary);
}

.avg-ratings {
    display: flex;
    flex-direction: column;
    gap: var(--space-3);
}

.avg-item {
    display: flex;
    align-items: center;
    gap: var(--space-2);
}

.avg-category {
    width: 100px;
    font-size: var(--text-sm);
    color: var(--text-secondary);
}

.avg-bar-container {
    flex: 1;
    height: 8px;
    background: var(--bg-secondary);
    border-radius: 4px;
    overflow: hidden;
}

.avg-bar {
    height: 100%;
    background: linear-gradient(90deg, var(--warning-color), var(--success-color));
    border-radius: 4px;
    transition: width 0.3s;
}

.avg-value {
    width: 30px;
    text-align: right;
    font-weight: 600;
    font-size: var(--text-sm);
}

/* History */
.feedback-list {
    display: flex;
    flex-direction: column;
    gap: var(--space-3);
    max-height: 400px;
    overflow-y: auto;
}

.feedback-item {
    padding: var(--space-3);
    background: var(--bg-secondary);
    border-radius: var(--radius-md);
}

.feedback-item-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--space-2);
}

.feedback-category {
    font-size: var(--text-sm);
    font-weight: 500;
    color: var(--primary-color);
}

.feedback-date {
    font-size: var(--text-xs);
    color: var(--text-muted);
}

.feedback-stars {
    display: flex;
    gap: 2px;
    margin-bottom: var(--space-2);
}

.feedback-comment {
    margin: 0;
    font-size: var(--text-sm);
    color: var(--text-secondary);
    line-height: 1.5;
}

.no-data {
    color: var(--text-muted);
    font-size: var(--text-sm);
    text-align: center;
    padding: var(--space-4);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const starRating = document.getElementById('star-rating');
    const ratingInput = document.getElementById('rating');
    const stars = starRating.querySelectorAll('.star-btn');
    
    // Star rating interaction
    stars.forEach((star, index) => {
        star.addEventListener('click', function() {
            const rating = parseInt(this.dataset.rating);
            ratingInput.value = rating;
            
            stars.forEach((s, i) => {
                if (i < rating) {
                    s.classList.add('active');
                } else {
                    s.classList.remove('active');
                }
            });
        });
        
        star.addEventListener('mouseenter', function() {
            const rating = parseInt(this.dataset.rating);
            stars.forEach((s, i) => {
                if (i < rating) {
                    s.style.color = 'var(--warning-color)';
                } else {
                    s.style.color = 'var(--text-muted)';
                }
            });
        });
    });
    
    starRating.addEventListener('mouseleave', function() {
        const currentRating = parseInt(ratingInput.value);
        stars.forEach((s, i) => {
            s.style.color = '';
            if (i < currentRating) {
                s.classList.add('active');
            }
        });
    });

    // Form submission
    document.getElementById('feedback-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-sm"></span> Dang gui...';
        
        fetch('/feedback', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast('success', data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast('error', data.error || 'Co loi xay ra');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg> Gui danh gia';
            }
        })
        .catch(err => {
            console.error(err);
            showToast('error', 'Loi ket noi');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg> Gui danh gia';
        });
    });
    
    function showToast(type, message) {
        if (typeof window.showNotification === 'function') {
            window.showNotification(type, message);
        } else {
            alert(message);
        }
    }
});
</script>
