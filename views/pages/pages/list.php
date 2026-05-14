<div class="page-header">
    <h1 class="page-title">Pages</h1>
</div>

<?php if (empty($pages)): ?>
<div class="card">
    <div class="empty-state">
        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path>
            <polyline points="14 2 14 8 20 8"></polyline>
        </svg>
        <h3>No pages available</h3>
        <p>There are no published pages at this time.</p>
    </div>
</div>
<?php else: ?>
<div class="pages-grid">
    <?php foreach ($pages as $page): ?>
    <a href="/page/<?= htmlspecialchars($page['slug']) ?>" class="card page-card">
        <div class="page-card-content">
            <h3 class="page-card-title"><?= htmlspecialchars($page['title']) ?></h3>
            <?php if (!empty($page['meta_description'])): ?>
            <p class="page-card-description"><?= htmlspecialchars($page['meta_description']) ?></p>
            <?php endif; ?>
        </div>
        <div class="page-card-arrow">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="5" y1="12" x2="19" y2="12"></line>
                <polyline points="12 5 19 12 12 19"></polyline>
            </svg>
        </div>
    </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<style>
.page-header {
    margin-bottom: 1.5rem;
}

.page-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0;
}

.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem 1.5rem;
    text-align: center;
    color: var(--text-secondary);
}

.empty-state svg {
    color: var(--text-tertiary);
    margin-bottom: 1rem;
}

.empty-state h3 {
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0 0 0.5rem;
}

.empty-state p {
    margin: 0;
    font-size: 0.875rem;
}

.pages-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1rem;
}

.page-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.25rem;
    text-decoration: none;
    transition: all 0.2s;
}

.page-card:hover {
    box-shadow: var(--shadow-md);
    transform: translateY(-2px);
}

.page-card-content {
    flex: 1;
    min-width: 0;
}

.page-card-title {
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0 0 0.25rem;
}

.page-card-description {
    font-size: 0.875rem;
    color: var(--text-secondary);
    margin: 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.page-card-arrow {
    flex-shrink: 0;
    color: var(--text-tertiary);
    margin-left: 1rem;
    transition: transform 0.2s, color 0.2s;
}

.page-card:hover .page-card-arrow {
    transform: translateX(4px);
    color: var(--primary-color);
}
</style>
