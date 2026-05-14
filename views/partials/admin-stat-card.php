<?php
/**
 * Admin Stat Card Partial
 * Reusable stat card component for admin dashboard
 * 
 * Variables:
 * @param string $title - Card title
 * @param string|int $value - Main value to display
 * @param string $icon - Icon class (e.g., 'icon-users')
 * @param string $color - Color variant: primary, success, warning, info, error, purple
 * @param string|null $trend - Trend text (e.g., '+5 this week')
 * @param bool $trendUp - Whether trend is positive
 * @param string|null $link - Optional link URL
 * @param string|null $linkText - Optional link text
 */
$color = $color ?? 'primary';
$trend = $trend ?? null;
$trendUp = $trendUp ?? true;
$link = $link ?? null;
$linkText = $linkText ?? 'View';
?>
<div class="stat-card<?= $link ? ' stat-card-clickable' : '' ?>"<?= $link ? ' onclick="window.location.href=\'' . htmlspecialchars($link) . '\'"' : '' ?>>
    <div class="stat-card-header">
        <div class="stat-icon stat-icon-<?= htmlspecialchars($color) ?>">
            <i class="<?= htmlspecialchars($icon) ?>"></i>
        </div>
        <?php if ($link): ?>
        <a href="<?= htmlspecialchars($link) ?>" class="stat-card-link"><?= htmlspecialchars($linkText) ?></a>
        <?php endif; ?>
    </div>
    <div class="stat-content">
        <span class="stat-value"><?= $value ?></span>
        <span class="stat-label"><?= htmlspecialchars($title) ?></span>
        <?php if ($trend): ?>
        <span class="stat-trend <?= $trendUp ? 'trend-up' : 'trend-down' ?>">
            <i class="<?= $trendUp ? 'icon-trending-up' : 'icon-trending-down' ?>"></i>
            <?= htmlspecialchars($trend) ?>
        </span>
        <?php endif; ?>
    </div>
</div>
