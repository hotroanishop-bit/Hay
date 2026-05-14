<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'API Keys Dashboard'; ?></title>
    
    <!-- Tiered CSS Loading: variables -> global -> components -> page-specific -->
    <link rel="stylesheet" href="/css/variables.css">
    <link rel="stylesheet" href="/css/global.css">
    <link rel="stylesheet" href="/css/components.css">
    
    <!-- Dynamic page-specific CSS -->
    <?php if (!empty($pageCssFiles)): ?>
        <?php foreach ($pageCssFiles as $cssFile): ?>
            <link rel="stylesheet" href="/css/pages/<?php echo htmlspecialchars($cssFile); ?>.css">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
