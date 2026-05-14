<footer class="app-footer">
    <div class="footer-content">
        <p>&copy; <?php echo date('Y'); ?> API Keys Platform. All rights reserved.</p>
    </div>
</footer>

<!-- Tiered JS Loading: global -> page-specific -->
<script src="/js/global.js"></script>

<!-- Dynamic page-specific JS -->
<?php if (!empty($pageJsFiles)): ?>
    <?php foreach ($pageJsFiles as $jsFile): ?>
        <script src="/js/pages/<?php echo htmlspecialchars($jsFile); ?>.js"></script>
    <?php endforeach; ?>
<?php endif; ?>
