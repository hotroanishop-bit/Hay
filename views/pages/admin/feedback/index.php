<div class="page-header">
    <div class="page-header-content">
        <h1 class="page-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
            </svg>
            User Feedback
        </h1>
        <p class="page-description">Xem danh gia tu nguoi dung</p>
    </div>
</div>

<!-- Stats Cards -->
<div class="stats-row">
    <div class="stat-card">
        <div class="stat-icon primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
            </svg>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?php echo number_format($stats['total_feedback'] ?? 0); ?></span>
            <span class="stat-label">Tong feedback</span>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon warning">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
            </svg>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?php echo number_format($stats['avg_rating'] ?? 0, 1); ?>/5</span>
            <span class="stat-label">Trung binh</span>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon success">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"></path>
            </svg>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?php echo number_format($stats['positive_count'] ?? 0); ?></span>
            <span class="stat-label">Tich cuc (4-5 sao)</span>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon danger">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M10 15v4a3 3 0 0 0 3 3l4-9V2H5.72a2 2 0 0 0-2 1.7l-1.38 9a2 2 0 0 0 2 2.3zm7-13h2.67A2.31 2.31 0 0 1 22 4v7a2.31 2.31 0 0 1-2.33 2H17"></path>
            </svg>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?php echo number_format($stats['negative_count'] ?? 0); ?></span>
            <span class="stat-label">Tieu cuc (1-2 sao)</span>
        </div>
    </div>
</div>

<!-- Category Breakdown -->
<div class="category-stats-card">
    <h3>Danh gia theo danh muc</h3>
    <div class="category-bars">
        <?php foreach ($categoryStats as $cat): ?>
            <div class="category-bar-item">
                <div class="cat-header">
                    <span class="cat-name"><?php echo htmlspecialchars($categories[$cat['category']] ?? $cat['category']); ?></span>
                    <span class="cat-avg"><?php echo number_format($cat['avg_rating'], 1); ?> (<?php echo $cat['count']; ?>)</span>
                </div>
                <div class="cat-bar-bg">
                    <div class="cat-bar-fill" style="width: <?php echo ($cat['avg_rating'] / 5) * 100; ?>%"></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Filters -->
<div class="filters-card">
    <form method="GET" class="filters-form">
        <div class="filter-group">
            <label>Danh muc</label>
            <select name="category">
                <option value="">Tat ca</option>
                <?php foreach ($categories as $value => $label): ?>
                    <option value="<?php echo $value; ?>" <?php echo $filters['category'] === $value ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($label); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="filter-group">
            <label>Rating</label>
            <select name="rating">
                <option value="">Tat ca</option>
                <?php for ($i = 5; $i >= 1; $i--): ?>
                    <option value="<?php echo $i; ?>" <?php echo $filters['rating'] == $i ? 'selected' : ''; ?>>
                        <?php echo $i; ?> sao
                    </option>
                <?php endfor; ?>
            </select>
        </div>
        
        <button type="submit" class="btn btn-primary">Loc</button>
    </form>
</div>

<!-- Feedback List -->
<div class="feedback-list-card">
    <?php if (empty($feedbacks)): ?>
        <div class="empty-state">
            <p>Khong co feedback nao</p>
        </div>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Danh muc</th>
                    <th>Rating</th>
                    <th>Nhan xet</th>
                    <th>Ngay</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($feedbacks as $fb): ?>
                    <tr>
                        <td>
                            <div class="user-cell">
                                <strong><?php echo htmlspecialchars($fb['username']); ?></strong>
                                <small><?php echo htmlspecialchars($fb['email']); ?></small>
                            </div>
                        </td>
                        <td>
                            <span class="category-badge"><?php echo htmlspecialchars($categories[$fb['category']] ?? $fb['category']); ?></span>
                        </td>
                        <td>
                            <div class="rating-stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" 
                                         fill="<?php echo $i <= $fb['rating'] ? 'var(--warning-color)' : 'var(--text-muted)'; ?>" 
                                         stroke="none">
                                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                    </svg>
                                <?php endfor; ?>
                            </div>
                        </td>
                        <td class="comment-cell">
                            <?php if (!empty($fb['comment'])): ?>
                                <span title="<?php echo htmlspecialchars($fb['comment']); ?>">
                                    <?php echo htmlspecialchars(mb_substr($fb['comment'], 0, 50)); ?>
                                    <?php if (strlen($fb['comment']) > 50): ?>...<?php endif; ?>
                                </span>
                            <?php else: ?>
                                <em class="text-muted">Khong co</em>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('d/m/Y H:i', strtotime($fb['created_at'])); ?></td>
                        <td>
                            <button class="btn btn-sm btn-danger" onclick="deleteFeedback(<?php echo $fb['id']; ?>)">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                </svg>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <!-- Pagination -->
        <?php if ($pagination['totalPages'] > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $pagination['totalPages']; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&category=<?php echo urlencode($filters['category']); ?>&rating=<?php echo urlencode($filters['rating']); ?>" 
                       class="page-link <?php echo $i === $pagination['page'] ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
.stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: var(--space-4);
    margin-bottom: var(--space-4);
}

.stat-card {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-xl);
    padding: var(--space-4);
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: var(--radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;
}

.stat-icon.primary { background: rgba(var(--primary-rgb), 0.1); color: var(--primary-color); }
.stat-icon.warning { background: rgba(var(--warning-rgb), 0.1); color: var(--warning-color); }
.stat-icon.success { background: rgba(var(--success-rgb), 0.1); color: var(--success-color); }
.stat-icon.danger { background: rgba(var(--danger-rgb), 0.1); color: var(--danger-color); }

.stat-content {
    display: flex;
    flex-direction: column;
}

.stat-value {
    font-size: var(--text-2xl);
    font-weight: 700;
    color: var(--text-primary);
}

.stat-label {
    font-size: var(--text-sm);
    color: var(--text-secondary);
}

.category-stats-card {
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-xl);
    padding: var(--space-4);
    margin-bottom: var(--space-4);
}

.category-stats-card h3 {
    margin: 0 0 var(--space-4) 0;
    font-size: var(--text-lg);
}

.category-bars {
    display: flex;
    flex-direction: column;
    gap: var(--space-3);
}

.category-bar-item {
    width: 100%;
}

.cat-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: var(--space-1);
    font-size: var(--text-sm);
}

.cat-name {
    color: var(--text-primary);
    font-weight: 500;
}

.cat-avg {
    color: var(--text-secondary);
}

.cat-bar-bg {
    height: 8px;
    background: var(--bg-secondary);
    border-radius: 4px;
    overflow: hidden;
}

.cat-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--warning-color), var(--success-color));
    border-radius: 4px;
}

.filters-card {
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-xl);
    padding: var(--space-4);
    margin-bottom: var(--space-4);
}

.filters-form {
    display: flex;
    gap: var(--space-3);
    align-items: flex-end;
    flex-wrap: wrap;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: var(--space-1);
}

.filter-group label {
    font-size: var(--text-sm);
    color: var(--text-secondary);
}

.filter-group select {
    padding: var(--space-2) var(--space-3);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    background: var(--bg-secondary);
    color: var(--text-primary);
}

.feedback-list-card {
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-xl);
    overflow: hidden;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th,
.data-table td {
    padding: var(--space-3);
    text-align: left;
    border-bottom: 1px solid var(--border-color);
}

.data-table th {
    background: var(--bg-secondary);
    font-weight: 600;
    font-size: var(--text-sm);
    color: var(--text-secondary);
}

.user-cell {
    display: flex;
    flex-direction: column;
}

.user-cell small {
    color: var(--text-muted);
    font-size: var(--text-xs);
}

.category-badge {
    display: inline-block;
    padding: var(--space-1) var(--space-2);
    background: var(--bg-secondary);
    border-radius: var(--radius-sm);
    font-size: var(--text-xs);
    font-weight: 500;
}

.rating-stars {
    display: flex;
    gap: 2px;
}

.comment-cell {
    max-width: 200px;
}

.pagination {
    display: flex;
    gap: var(--space-1);
    padding: var(--space-4);
    justify-content: center;
}

.page-link {
    padding: var(--space-2) var(--space-3);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    color: var(--text-primary);
    text-decoration: none;
}

.page-link:hover {
    background: var(--bg-secondary);
}

.page-link.active {
    background: var(--primary-color);
    border-color: var(--primary-color);
    color: white;
}

.empty-state {
    text-align: center;
    padding: var(--space-8);
    color: var(--text-secondary);
}
</style>

<script>
function deleteFeedback(id) {
    if (!confirm('Xoa feedback nay?')) return;
    
    fetch('/admin/feedback/' + id, {
        method: 'DELETE'
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Co loi xay ra');
        }
    });
}
</script>
