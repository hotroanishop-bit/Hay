<?php if (isset($_SESSION['flash'])): ?>
    <div class="alert alert-<?php echo htmlspecialchars($_SESSION['flash']['type']); ?>" role="alert">
        <span class="alert-message"><?php echo htmlspecialchars($_SESSION['flash']['message']); ?></span>
        <button type="button" class="alert-close" data-dismiss="alert">&times;</button>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>
