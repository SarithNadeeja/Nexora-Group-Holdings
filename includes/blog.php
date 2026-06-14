<?php
/**
 * SEO blog posts (admin-managed, not linked from public site navigation).
 */

require_once __DIR__ . '/config.php';

/**
 * URL-safe slug from a title.
 */
function nexora_blog_slugify(string $title): string
{
    $slug = strtolower(trim($title));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    $slug = trim($slug, '-');
    return $slug !== '' ? $slug : 'post';
}

/**
 * Ensure slug is unique in blog_posts.
 */
function nexora_blog_unique_slug(PDO $pdo, string $baseSlug, ?int $excludeId = null): string
{
    $slug = nexora_blog_slugify($baseSlug);
    $candidate = $slug;
    $n = 2;

    while (true) {
        if ($excludeId !== null && $excludeId > 0) {
            $st = $pdo->prepare('SELECT id FROM blog_posts WHERE slug = ? AND id <> ? LIMIT 1');
            $st->execute([$candidate, $excludeId]);
        } else {
            $st = $pdo->prepare('SELECT id FROM blog_posts WHERE slug = ? LIMIT 1');
            $st->execute([$candidate]);
        }
        if (!$st->fetch(PDO::FETCH_ASSOC)) {
            return $candidate;
        }
        $candidate = $slug . '-' . $n;
        $n++;
    }
}

/**
 * Public crawlable URL for a published post (not shown in site nav).
 */
function nexora_blog_post_public_url(string $slug): string
{
    return nexora_site_absolute_url('pages/blog-post.php?slug=' . rawurlencode($slug));
}

/**
 * @return array<string, mixed>|null
 */
function nexora_blog_post_by_slug(PDO $pdo, string $slug, bool $publishedOnly = true): ?array
{
    $slug = trim($slug);
    if ($slug === '') {
        return null;
    }
    $sql = 'SELECT * FROM blog_posts WHERE slug = ?';
    if ($publishedOnly) {
        $sql .= " AND status = 'published'";
    }
    $sql .= ' LIMIT 1';
    $st = $pdo->prepare($sql);
    $st->execute([$slug]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

/**
 * @return array<int, array<string, mixed>>
 */
function nexora_blog_published_posts(PDO $pdo): array
{
    $st = $pdo->query(
        "SELECT id, title, slug, meta_description, updated_at, created_at
         FROM blog_posts WHERE status = 'published' ORDER BY updated_at DESC, id DESC"
    );
    return $st ? $st->fetchAll(PDO::FETCH_ASSOC) : [];
}

/**
 * Allow basic HTML for SEO article body.
 */
function nexora_blog_render_content(string $html): string
{
    return strip_tags($html, '<p><h2><h3><h4><a><ul><ol><li><strong><em><br><blockquote>');
}
