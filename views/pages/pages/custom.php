<div class="page-header">
    <h1 class="page-title"><?= htmlspecialchars($page['title'] ?? 'Page') ?></h1>
</div>

<div class="card">
    <div class="card-body custom-page-content">
        <?= $page['content'] ?? '' ?>
    </div>
</div>

<?php if (!empty($page['meta_description'])): ?>
<script>
    // Update meta description for SEO
    document.querySelector('meta[name="description"]')?.setAttribute('content', <?= json_encode($page['meta_description']) ?>);
</script>
<?php endif; ?>

<style>
.custom-page-content {
    padding: 1.5rem;
    line-height: 1.7;
    color: var(--text-primary);
}

.custom-page-content h1,
.custom-page-content h2,
.custom-page-content h3,
.custom-page-content h4,
.custom-page-content h5,
.custom-page-content h6 {
    margin-top: 1.5rem;
    margin-bottom: 0.75rem;
    font-weight: 600;
    color: var(--text-primary);
}

.custom-page-content h1 { font-size: 1.875rem; }
.custom-page-content h2 { font-size: 1.5rem; }
.custom-page-content h3 { font-size: 1.25rem; }
.custom-page-content h4 { font-size: 1.125rem; }

.custom-page-content p {
    margin-bottom: 1rem;
}

.custom-page-content a {
    color: var(--primary-color);
    text-decoration: underline;
}

.custom-page-content a:hover {
    color: var(--primary-hover);
}

.custom-page-content ul,
.custom-page-content ol {
    margin-bottom: 1rem;
    padding-left: 1.5rem;
}

.custom-page-content li {
    margin-bottom: 0.5rem;
}

.custom-page-content blockquote {
    margin: 1rem 0;
    padding: 1rem 1.5rem;
    border-left: 4px solid var(--primary-color);
    background: var(--bg-secondary);
    border-radius: 0 0.375rem 0.375rem 0;
}

.custom-page-content code {
    padding: 0.125rem 0.375rem;
    background: var(--bg-secondary);
    border-radius: 0.25rem;
    font-family: monospace;
    font-size: 0.875em;
}

.custom-page-content pre {
    margin: 1rem 0;
    padding: 1rem;
    background: var(--bg-secondary);
    border-radius: 0.5rem;
    overflow-x: auto;
}

.custom-page-content pre code {
    padding: 0;
    background: transparent;
}

.custom-page-content img {
    max-width: 100%;
    height: auto;
    border-radius: 0.5rem;
    margin: 1rem 0;
}

.custom-page-content table {
    width: 100%;
    margin: 1rem 0;
    border-collapse: collapse;
}

.custom-page-content th,
.custom-page-content td {
    padding: 0.75rem;
    text-align: left;
    border-bottom: 1px solid var(--border-color);
}

.custom-page-content th {
    font-weight: 600;
    background: var(--bg-secondary);
}

.custom-page-content hr {
    margin: 2rem 0;
    border: none;
    border-top: 1px solid var(--border-color);
}
</style>
