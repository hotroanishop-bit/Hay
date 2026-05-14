<?php
/**
 * Public Changelog Page
 * Variables: $pageTitle, $changelogs, $recentEntries
 */
?>

<!-- Changelog Header -->
<header class="changelog-header">
    <div class="changelog-header-content">
        <a href="/" class="back-home-link">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            Back to Home
        </a>
        <h1>Changelog</h1>
        <p>Stay up to date with the latest features, improvements, and fixes.</p>
    </div>
</header>

<main class="changelog-main">
    <div class="changelog-container">
        <!-- Timeline -->
        <div class="changelog-timeline">
            <?php if (!empty($changelogs)): ?>
                <?php foreach ($changelogs as $versionGroup): ?>
                <div class="timeline-version">
                    <div class="version-header">
                        <span class="version-badge">v<?= htmlspecialchars($versionGroup['version']) ?></span>
                        <span class="version-date">
                            <?= date('F j, Y', strtotime($versionGroup['published_at'])) ?>
                        </span>
                    </div>
                    <div class="version-entries">
                        <?php foreach ($versionGroup['entries'] as $entry): ?>
                        <div class="changelog-entry">
                            <span class="entry-type entry-type-<?= htmlspecialchars($entry['type']) ?>">
                                <?php 
                                $typeIcons = [
                                    'feature' => '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>',
                                    'fix' => '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path></svg>',
                                    'improvement' => '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>',
                                    'security' => '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>'
                                ];
                                echo $typeIcons[$entry['type']] ?? '';
                                ?>
                                <?= ucfirst(htmlspecialchars($entry['type'])) ?>
                            </span>
                            <div class="entry-content">
                                <h4 class="entry-title"><?= htmlspecialchars($entry['title']) ?></h4>
                                <?php if (!empty($entry['description'])): ?>
                                <p class="entry-description"><?= nl2br(htmlspecialchars($entry['description'])) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
            <div class="changelog-empty">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><line x1="10" y1="9" x2="8" y2="9"></line></svg>
                <h3>No updates yet</h3>
                <p>Check back later for product updates and release notes.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<!-- Footer -->
<footer class="changelog-footer">
    <div class="footer-content">
        <p>&copy; <?= date('Y') ?> Hay API Gateway. All rights reserved.</p>
        <div class="footer-links">
            <a href="/">Home</a>
            <a href="/docs">Documentation</a>
            <a href="/login">Sign In</a>
        </div>
    </div>
</footer>
