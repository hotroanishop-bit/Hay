<!DOCTYPE html>
<html lang="en">
<?php require VIEWS_PATH . '/partials/head.php'; ?>
<body>
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
    </div>
    
    <?php require VIEWS_PATH . '/partials/footer.php'; ?>
</body>
</html>
