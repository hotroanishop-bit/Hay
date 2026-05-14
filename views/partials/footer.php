<footer class="app-footer">
    <div class="footer-content">
        <div class="footer-links">
            <a href="/status">Status</a>
            <a href="/changelog">Changelog</a>
            <a href="/docs">Documentation</a>
        </div>
        <p>&copy; <?php echo date('Y'); ?> API Keys Platform. All rights reserved.</p>
    </div>
</footer>

<style>
.footer-links {
    display: flex;
    gap: 20px;
    justify-content: center;
    margin-bottom: 10px;
}
.footer-links a {
    color: var(--text-muted, #6b7280);
    text-decoration: none;
    font-size: 14px;
}
.footer-links a:hover {
    color: var(--primary, #6366f1);
}
</style>

<!-- Tiered JS Loading: global -> page-specific -->
<script src="/js/global.js"></script>

<!-- Dynamic page-specific JS -->
<?php if (!empty($pageJsFiles)): ?>
    <?php foreach ($pageJsFiles as $jsFile): ?>
        <script src="/js/pages/<?php echo htmlspecialchars($jsFile); ?>.js"></script>
    <?php endforeach; ?>
<?php endif; ?>
