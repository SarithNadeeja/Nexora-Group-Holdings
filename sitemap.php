<?php
/**
 * XML sitemap for search engines (includes SEO blog posts, no public blog index page).
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/blog.php';

header('Content-Type: application/xml; charset=UTF-8');

$pdo = nexora_db_connect();

$staticPaths = [
    'index.php',
    'about.php',
    'contact.php',
    'pages/digital.php',
    'pages/agro.php',
    'pages/printing.php',
];

$urls = [];
foreach ($staticPaths as $path) {
    $urls[] = [
        'loc' => nexora_site_absolute_url($path),
        'lastmod' => null,
    ];
}

if ($pdo) {
    nexora_blog_posts_ensure_table($pdo);
    foreach (nexora_blog_published_posts($pdo) as $post) {
        $urls[] = [
            'loc' => nexora_blog_post_public_url((string) $post['slug']),
            'lastmod' => date('Y-m-d', strtotime((string) $post['updated_at'])),
        ];
    }
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($urls as $u): ?>
    <url>
        <loc><?php echo htmlspecialchars($u['loc'], ENT_XML1); ?></loc>
        <?php if (!empty($u['lastmod'])): ?>
        <lastmod><?php echo htmlspecialchars($u['lastmod'], ENT_XML1); ?></lastmod>
        <?php endif; ?>
    </url>
<?php endforeach; ?>
</urlset>
