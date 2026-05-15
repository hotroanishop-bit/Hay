<!DOCTYPE html>
<html lang="<?php echo htmlLang(); ?>" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#6366f1">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Hay">
    <title><?php echo $pageTitle ?? 'API Keys Dashboard'; ?></title>
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="/manifest.json">
    
    <!-- Apple Touch Icons -->
    <link rel="apple-touch-icon" href="/icons/icon-152x152.png">
    <link rel="apple-touch-icon" sizes="192x192" href="/icons/icon-192x192.png">
    <link rel="apple-touch-icon" sizes="512x512" href="/icons/icon-512x512.png">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="/icons/icon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/icons/icon-72x72.png">
    
    <!-- CSS Loading Order: variables -> themes -> components -> global -> layout -> page-specific -->
    <link rel="stylesheet" href="/css/variables.css">
    <link rel="stylesheet" href="/css/themes/light.css">
    <link rel="stylesheet" href="/css/themes/dark.css">
    <link rel="stylesheet" href="/css/components.css">
    <link rel="stylesheet" href="/css/global.css">
    <link rel="stylesheet" href="/css/layout.css">
    
    <!-- Dynamic page-specific CSS -->
    <?php if (!empty($pageCssFiles)): ?>
        <?php foreach ($pageCssFiles as $cssFile): ?>
            <link rel="stylesheet" href="/css/pages/<?php echo htmlspecialchars($cssFile); ?>.css">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Preload critical fonts if any -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    
    <script>
    // Apply saved theme immediately to prevent flash
    (function() {
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    })();
    </script>
</head>
<body data-theme="light">
    <?php require VIEWS_PATH . '/partials/impersonation-banner.php'; ?>
    <div class="app-container">
        <?php require VIEWS_PATH . '/partials/header.php'; ?>
        
        <div class="main-wrapper">
            <?php require VIEWS_PATH . '/partials/sidebar.php'; ?>
            
            <main class="content-area">
                <?php require VIEWS_PATH . '/partials/alerts.php'; ?>
                
                <?php 
                if (isset($contentView) && file_exists($contentView)) {
                    require $contentView;
                }
                ?>
            </main>
        </div>
        
        <!-- Bottom Navigation for Mobile -->
        <?php if (isset($_SESSION['user'])): ?>
            <?php require VIEWS_PATH . '/partials/bottom-nav.php'; ?>
        <?php endif; ?>
        
        <!-- Bottom Sheet Menu -->
        <?php if (isset($_SESSION['user'])): ?>
            <?php require VIEWS_PATH . '/partials/bottom-sheet.php'; ?>
        <?php endif; ?>
    </div>
    
    <?php require VIEWS_PATH . '/partials/footer.php'; ?>
    
    <!-- Chat Widget -->
    <?php require VIEWS_PATH . '/partials/chat-widget.php'; ?>
    
    <!-- Search Modal (Command Palette) -->
    <?php require VIEWS_PATH . '/partials/search-modal.php'; ?>
    
    <!-- PWA Install Prompt -->
    <?php require VIEWS_PATH . '/partials/pwa-install-prompt.php'; ?>
    
    <!-- JavaScript Loading Order: theme-switcher (must be first) -> bottom-sheet -> notifications -> charts -> global -> pwa -> shortcuts -> page-specific -->
    <script src="/js/theme-switcher.js"></script>
    <script src="/js/bottom-sheet.js"></script>
    <script src="/js/notifications.js"></script>
    <script src="/js/charts.js"></script>
    <script src="/js/global.js"></script>
    <script src="/js/pwa.js"></script>
    <script src="/js/shortcuts.js"></script>
    
    <!-- Dynamic page-specific JS -->
    <?php if (!empty($pageJsFiles)): ?>
        <?php foreach ($pageJsFiles as $jsFile): ?>
            <script src="/js/pages/<?php echo htmlspecialchars($jsFile); ?>.js"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
