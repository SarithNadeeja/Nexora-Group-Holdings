<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/database.php';
require_once dirname(__DIR__) . '/includes/blog.php';

$slug = isset($_GET['slug']) ? trim((string) $_GET['slug']) : '';
$pdo = nexora_db_connect();
$post = ($pdo && $slug !== '') ? nexora_blog_post_by_slug($pdo, $slug, true) : null;

if (!$post) {
    http_response_code(404);
    $pageTitle = 'Page Not Found';
    $metaDescription = 'The requested page could not be found.';
    include dirname(__DIR__) . '/includes/header.php';
    include dirname(__DIR__) . '/includes/navbar.php';
    echo '<main class="container" style="padding:80px 20px;text-align:center;"><h1>404</h1><p>Article not found.</p></main>';
    include dirname(__DIR__) . '/includes/footer.php';
    exit;
}

$pageTitle = (string) $post['title'];
$metaDescription = (string) $post['meta_description'];
$metaKeywords = (string) $post['meta_keywords'];
$canonicalUrl = nexora_blog_post_public_url((string) $post['slug']);
$metaOgUrl = $canonicalUrl;
$metaOgTitle = $pageTitle;
$metaOgDescription = $metaDescription;
$seoMinimalLayout = true;

$publishedIso = date('c', strtotime((string) $post['updated_at']));
$jsonLd = [
    '@context' => 'https://schema.org',
    '@type' => 'Article',
    'headline' => $pageTitle,
    'description' => $metaDescription,
    'datePublished' => date('c', strtotime((string) $post['created_at'])),
    'dateModified' => $publishedIso,
    'author' => [
        '@type' => 'Organization',
        'name' => SITE_NAME,
    ],
    'publisher' => [
        '@type' => 'Organization',
        'name' => SITE_NAME,
    ],
    'mainEntityOfPage' => $canonicalUrl,
];

include dirname(__DIR__) . '/includes/header.php';
?>
<main class="seo-article-page">
    <article class="seo-article container">
        <header class="seo-article-header">
            <p class="seo-article-brand"><a href="<?php echo nexora_url('index.php'); ?>"><?php echo htmlspecialchars(SITE_NAME); ?></a></p>
            <h1><?php echo htmlspecialchars($pageTitle); ?></h1>
            <time datetime="<?php echo htmlspecialchars($publishedIso); ?>" class="seo-article-date">
                Updated <?php echo htmlspecialchars(date('F j, Y', strtotime((string) $post['updated_at']))); ?>
            </time>
        </header>
        <div class="seo-article-body">
            <?php echo nexora_blog_render_content((string) $post['content']); ?>
        </div>
        <footer class="seo-article-footer">
            <a class="btn-primary" href="<?php echo htmlspecialchars(nexora_contact_href()); ?>" target="_blank" rel="noopener noreferrer">Contact Us on WhatsApp</a>
            <a class="seo-article-home" href="<?php echo nexora_url('index.php'); ?>">Back to <?php echo htmlspecialchars(SITE_NAME); ?></a>
        </footer>
    </article>
</main>
<script type="application/ld+json"><?php echo json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?></script>
<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
