<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#6366f1">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Hay">
    <meta name="description" content="Hay API Gateway - Powerful AI API management platform with intelligent routing, rate limiting, and usage analytics.">
    <title><?php echo $pageTitle ?? 'Hay API Gateway'; ?></title>
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="/manifest.json">
    
    <!-- Apple Touch Icons -->
    <link rel="apple-touch-icon" href="/icons/icon-152x152.png">
    <link rel="apple-touch-icon" sizes="192x192" href="/icons/icon-192x192.png">
    <link rel="apple-touch-icon" sizes="512x512" href="/icons/icon-512x512.png">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="/icons/icon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/icons/icon-72x72.png">
    
    <!-- CSS Loading Order -->
    <link rel="stylesheet" href="/css/variables.css">
    <link rel="stylesheet" href="/css/themes/light.css">
    <link rel="stylesheet" href="/css/themes/dark.css">
    <link rel="stylesheet" href="/css/components.css">
    <link rel="stylesheet" href="/css/global.css">
    
    <!-- Dynamic page-specific CSS -->
    <?php if (!empty($pageCssFiles)): ?>
        <?php foreach ($pageCssFiles as $cssFile): ?>
            <link rel="stylesheet" href="/css/pages/<?php echo htmlspecialchars($cssFile); ?>.css">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Preload critical fonts -->
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
    <?php 
    if (isset($contentView) && file_exists($contentView)) {
        require $contentView;
    }
    ?>
    
    <!-- JavaScript -->
    <script src="/js/theme-switcher.js"></script>
    <script src="/js/pwa.js"></script>
    
    <!-- Dynamic page-specific JS -->
    <?php if (!empty($pageJsFiles)): ?>
        <?php foreach ($pageJsFiles as $jsFile): ?>
            <script src="/js/pages/<?php echo htmlspecialchars($jsFile); ?>.js"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
