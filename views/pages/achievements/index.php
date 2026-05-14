<div class="page-container">
    <div class="page-header">
        <h1 class="page-title">
            <span class="page-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="8" r="7"></circle>
                    <polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"></polyline>
                </svg>
            </span>
            <?php echo __('achievements.title', 'Thanh tuu'); ?>
        </h1>
        <p class="page-description"><?php echo __('achievements.description', 'Hoan thanh cac muc tieu de nhan phan thuong!'); ?></p>
    </div>

    <!-- Progress Overview -->
    <div class="achievement-overview">
        <div class="overview-card main-progress">
            <div class="progress-circle">
                <svg viewBox="0 0 36 36" class="circular-chart">
                    <path class="circle-bg" d="M18 2.0845
                        a 15.9155 15.9155 0 0 1 0 31.831
                        a 15.9155 15.9155 0 0 1 0 -31.831"/>
                    <path class="circle-progress" stroke-dasharray="<?php echo $stats['percentage']; ?>, 100" d="M18 2.0845
                        a 15.9155 15.9155 0 0 1 0 31.831
                        a 15.9155 15.9155 0 0 1 0 -31.831"/>
                    <text x="18" y="20.35" class="percentage"><?php echo $stats['percentage']; ?>%</text>
                </svg>
            </div>
            <div class="progress-info">
                <h3><?php echo $stats['unlocked']; ?> / <?php echo $stats['total']; ?></h3>
                <p><?php echo __('achievements.unlocked', 'Thanh tuu da mo khoa'); ?></p>
            </div>
        </div>
        <div class="overview-card">
            <div class="stat-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="1" x2="12" y2="23"></line>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                </svg>
            </div>
            <div class="stat-value"><?php echo number_format($stats['total_rewards'], 2); ?></div>
            <div class="stat-label"><?php echo __('achievements.rewards_earned', 'Tokens da nhan'); ?></div>
        </div>
        <div class="overview-card recent-unlocks">
            <h4><?php echo __('achievements.recent', 'Mo khoa gan day'); ?></h4>
            <?php if (!empty($stats['recent'])): ?>
                <div class="recent-list">
                    <?php foreach ($stats['recent'] as $recent): ?>
                        <div class="recent-item">
                            <span class="recent-icon"><?php echo $this->getAchievementIcon($recent['icon']); ?></span>
                            <span class="recent-name"><?php echo htmlspecialchars($recent['name']); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="empty-text"><?php echo __('achievements.no_recent', 'Chua co thanh tuu nao'); ?></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Achievement Categories -->
    <?php foreach ($categories as $categoryKey => $category): ?>
        <?php if (!empty($category['items'])): ?>
            <div class="achievement-category">
                <h2 class="category-title"><?php echo $category['label']; ?></h2>
                <div class="achievements-grid">
                    <?php foreach ($category['items'] as $achievement): ?>
                        <div class="achievement-card <?php echo $achievement['is_unlocked'] ? 'unlocked' : 'locked'; ?>">
                            <div class="achievement-icon">
                                <?php echo $this->getAchievementIcon($achievement['icon']); ?>
                                <?php if ($achievement['is_unlocked']): ?>
                                    <span class="unlocked-badge">&#10003;</span>
                                <?php endif; ?>
                            </div>
                            <div class="achievement-content">
                                <h3 class="achievement-name"><?php echo htmlspecialchars($achievement['name']); ?></h3>
                                <p class="achievement-desc"><?php echo htmlspecialchars($achievement['description'] ?? ''); ?></p>
                                
                                <?php if (!$achievement['is_unlocked']): ?>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?php echo $achievement['progress']['percentage']; ?>%"></div>
                                    </div>
                                    <div class="progress-text">
                                        <?php echo number_format($achievement['progress']['current']); ?> / <?php echo number_format($achievement['progress']['target']); ?>
                                    </div>
                                <?php else: ?>
                                    <div class="unlocked-date">
                                        Mo khoa: <?php echo date('d/m/Y', strtotime($achievement['unlocked_at'])); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="achievement-reward">
                                <?php if ($achievement['reward_tokens'] > 0): ?>
                                    <span class="reward-badge">+<?php echo $achievement['reward_tokens']; ?> tokens</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>

<?php
// Helper function for icons
function getAchievementIcon($icon) {
    $icons = [
        'trophy' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"></path><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"></path><path d="M4 22h16"></path><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"></path><path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"></path><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"></path></svg>',
        'credit-card' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>',
        'code' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>',
        'award' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="7"></circle><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"></polyline></svg>',
        'users' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>',
        'calendar' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>',
        'dollar-sign' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>',
        'key' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"></path></svg>'
    ];
    return $icons[$icon] ?? $icons['trophy'];
}
$this->getAchievementIcon = 'getAchievementIcon';
?>

<style>
.achievement-overview {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.overview-card {
    background: var(--bg-secondary);
    border-radius: var(--border-radius);
    padding: 1.5rem;
    text-align: center;
}

.overview-card.main-progress {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    text-align: left;
}

.progress-circle {
    width: 100px;
    height: 100px;
    flex-shrink: 0;
}

.circular-chart {
    display: block;
    max-width: 100%;
}

.circle-bg {
    fill: none;
    stroke: var(--bg-tertiary);
    stroke-width: 3;
}

.circle-progress {
    fill: none;
    stroke: var(--primary-color);
    stroke-width: 3;
    stroke-linecap: round;
    transform: rotate(-90deg);
    transform-origin: 50% 50%;
    animation: progress 1s ease-out forwards;
}

@keyframes progress {
    0% { stroke-dasharray: 0, 100; }
}

.percentage {
    fill: var(--text-primary);
    font-size: 0.5em;
    font-weight: 700;
    text-anchor: middle;
}

.progress-info h3 {
    font-size: 1.5rem;
    margin-bottom: 0.25rem;
}

.progress-info p {
    color: var(--text-secondary);
    font-size: 0.875rem;
}

.stat-icon {
    color: var(--primary-color);
    margin-bottom: 0.5rem;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
}

.stat-label {
    color: var(--text-secondary);
    font-size: 0.875rem;
}

.recent-unlocks h4 {
    margin-bottom: 1rem;
    font-size: 0.875rem;
    color: var(--text-secondary);
}

.recent-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.recent-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
}

.recent-icon svg {
    width: 16px;
    height: 16px;
}

.empty-text {
    color: var(--text-secondary);
    font-size: 0.875rem;
}

.achievement-category {
    margin-bottom: 2rem;
}

.category-title {
    font-size: 1.25rem;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid var(--border-color);
}

.achievements-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1rem;
}

.achievement-card {
    background: var(--bg-secondary);
    border-radius: var(--border-radius);
    padding: 1.25rem;
    display: flex;
    gap: 1rem;
    position: relative;
    transition: transform 0.2s, box-shadow 0.2s;
}

.achievement-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.achievement-card.locked {
    opacity: 0.7;
}

.achievement-card.unlocked {
    border-left: 3px solid #10b981;
}

.achievement-icon {
    position: relative;
    width: 48px;
    height: 48px;
    background: var(--bg-tertiary);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.achievement-card.unlocked .achievement-icon {
    background: linear-gradient(135deg, var(--primary-color), #10b981);
    color: white;
}

.unlocked-badge {
    position: absolute;
    top: -4px;
    right: -4px;
    width: 18px;
    height: 18px;
    background: #10b981;
    color: white;
    border-radius: 50%;
    font-size: 0.7rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.achievement-content {
    flex: 1;
    min-width: 0;
}

.achievement-name {
    font-size: 1rem;
    margin-bottom: 0.25rem;
}

.achievement-desc {
    font-size: 0.8rem;
    color: var(--text-secondary);
    margin-bottom: 0.75rem;
}

.progress-bar {
    height: 6px;
    background: var(--bg-tertiary);
    border-radius: 3px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--primary-color), #10b981);
    border-radius: 3px;
    transition: width 0.5s ease;
}

.progress-text {
    font-size: 0.75rem;
    color: var(--text-secondary);
    margin-top: 0.25rem;
}

.unlocked-date {
    font-size: 0.75rem;
    color: #10b981;
}

.achievement-reward {
    position: absolute;
    top: 0.75rem;
    right: 0.75rem;
}

.reward-badge {
    background: linear-gradient(135deg, #f59e0b, #f97316);
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    font-size: 0.7rem;
    font-weight: 600;
}

@media (max-width: 768px) {
    .overview-card.main-progress {
        flex-direction: column;
        text-align: center;
    }
}
</style>
