<?php
require_once __DIR__ . '/includes/auth.php';
requireAdminAuth();
require_once __DIR__ . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/blog.php';

$adminPageTitle = 'SEO Blog Posts';
$success = '';
$error = '';

$validStatus = ['published', 'draft'];

$editId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
$editRow = null;
if ($editId > 0) {
    $st = $pdo->prepare('SELECT * FROM blog_posts WHERE id = ?');
    $st->execute([$editId]);
    $editRow = $st->fetch(PDO::FETCH_ASSOC) ?: null;
    if (!$editRow) {
        $editId = 0;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_id'])) {
        $delId = (int) $_POST['delete_id'];
        $pdo->prepare('DELETE FROM blog_posts WHERE id = ?')->execute([$delId]);
        $success = 'Blog post deleted.';
        if ($editId === $delId) {
            header('Location: blog-posts.php');
            exit;
        }
    } else {
        $title = isset($_POST['title']) ? trim((string) $_POST['title']) : '';
        $slugInput = isset($_POST['slug']) ? trim((string) $_POST['slug']) : '';
        $metaDescription = isset($_POST['meta_description']) ? trim((string) $_POST['meta_description']) : '';
        $metaKeywords = isset($_POST['meta_keywords']) ? trim((string) $_POST['meta_keywords']) : '';
        $content = isset($_POST['content']) ? trim((string) $_POST['content']) : '';
        $status = isset($_POST['status']) ? (string) $_POST['status'] : 'published';

        if ($title === '' || mb_strlen($title) > 255) {
            $error = 'Please enter a title (max 255 characters).';
        } elseif ($content === '') {
            $error = 'Post content is required.';
        } elseif (mb_strlen($metaDescription) > 320) {
            $error = 'Meta description is too long (max 320 characters).';
        } elseif (mb_strlen($metaKeywords) > 500) {
            $error = 'Meta keywords are too long (max 500 characters).';
        } elseif (!in_array($status, $validStatus, true)) {
            $error = 'Invalid status.';
        } else {
            $slugBase = $slugInput !== '' ? $slugInput : $title;
            $isUpdate = isset($_POST['update_post']);
            $postId = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;

            if ($isUpdate && $postId > 0) {
                $slug = nexora_blog_unique_slug($pdo, $slugBase, $postId);
                $pdo->prepare(
                    'UPDATE blog_posts SET title = ?, slug = ?, meta_description = ?, meta_keywords = ?, content = ?, status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?'
                )->execute([$title, $slug, $metaDescription, $metaKeywords, $content, $status, $postId]);
                header('Location: blog-posts.php?edit=' . $postId . '&saved=1');
                exit;
            }

            if (isset($_POST['add_post'])) {
                $slug = nexora_blog_unique_slug($pdo, $slugBase, null);
                $pdo->prepare(
                    'INSERT INTO blog_posts (title, slug, meta_description, meta_keywords, content, status) VALUES (?, ?, ?, ?, ?, ?)'
                )->execute([$title, $slug, $metaDescription, $metaKeywords, $content, $status]);
                $success = 'Blog post created. It is included in sitemap.php when published (not shown on the public site menu).';
            }
        }
    }
}

if (isset($_GET['saved']) && $_GET['saved'] === '1') {
    $success = 'Blog post saved.';
}

$posts = $pdo->query('SELECT id, title, slug, status, updated_at, created_at FROM blog_posts ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/includes/header.php';
?>
<div class="admin-layout">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    <main class="admin-main">
        <div class="admin-heading">
            <h1>SEO Blog Posts</h1>
            <p>Create articles for search engines. Posts are <strong>not</strong> linked from the public website menu, but published posts appear in <code>sitemap.php</code> and are reachable by direct URL.</p>
        </div>

        <?php if ($success !== ''): ?>
            <div class="alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <?php if ($error !== ''): ?>
            <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($editRow): ?>
            <section class="admin-card admin-card-spaced">
                <h2 class="admin-section-title">Edit post #<?php echo (int) $editRow['id']; ?></h2>
                <p class="admin-form-hint" style="margin-bottom:12px;"><a href="blog-posts.php">&larr; Back to list</a></p>
                <?php if ($editRow['status'] === 'published'): ?>
                    <p class="admin-form-hint" style="margin-bottom:12px;">Public URL: <a href="<?php echo htmlspecialchars(nexora_blog_post_public_url((string) $editRow['slug'])); ?>" target="_blank" rel="noopener noreferrer"><?php echo htmlspecialchars(nexora_blog_post_public_url((string) $editRow['slug'])); ?></a></p>
                <?php endif; ?>
                <form class="admin-form" method="post">
                    <input type="hidden" name="post_id" value="<?php echo (int) $editRow['id']; ?>">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" required maxlength="255" value="<?php echo htmlspecialchars((string) $editRow['title']); ?>">

                    <label for="slug">URL slug (optional — auto from title if empty)</label>
                    <input type="text" id="slug" name="slug" maxlength="255" pattern="[a-z0-9\-]+" value="<?php echo htmlspecialchars((string) $editRow['slug']); ?>" placeholder="nexora-digital-services-colombo">

                    <label for="meta_description">Meta description (SEO, max 320 chars)</label>
                    <textarea id="meta_description" name="meta_description" maxlength="320" rows="3"><?php echo htmlspecialchars((string) $editRow['meta_description']); ?></textarea>

                    <label for="meta_keywords">Meta keywords (comma-separated, optional)</label>
                    <input type="text" id="meta_keywords" name="meta_keywords" maxlength="500" value="<?php echo htmlspecialchars((string) $editRow['meta_keywords']); ?>" placeholder="printing, digital marketing, agro, Sri Lanka">

                    <label for="status">Status</label>
                    <select id="status" name="status" required>
                        <option value="published" <?php echo $editRow['status'] === 'published' ? 'selected' : ''; ?>>Published (in sitemap, crawlable)</option>
                        <option value="draft" <?php echo $editRow['status'] === 'draft' ? 'selected' : ''; ?>>Draft (hidden from sitemap)</option>
                    </select>

                    <label for="content">Content (basic HTML allowed: p, h2, h3, a, ul, li, strong)</label>
                    <textarea id="content" name="content" required rows="14"><?php echo htmlspecialchars((string) $editRow['content']); ?></textarea>

                    <button type="submit" name="update_post" value="1" class="btn-primary">Save changes</button>
                </form>
            </section>
        <?php else: ?>
            <section class="admin-card admin-card-spaced">
                <h2 class="admin-section-title">Add SEO post</h2>
                <form class="admin-form" method="post">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" required maxlength="255">

                    <label for="slug">URL slug (optional)</label>
                    <input type="text" id="slug" name="slug" maxlength="255" pattern="[a-z0-9\-]+" placeholder="nexora-printing-services">

                    <label for="meta_description">Meta description</label>
                    <textarea id="meta_description" name="meta_description" maxlength="320" rows="3"></textarea>

                    <label for="meta_keywords">Meta keywords (optional)</label>
                    <input type="text" id="meta_keywords" name="meta_keywords" maxlength="500">

                    <label for="status">Status</label>
                    <select id="status" name="status" required>
                        <option value="published">Published</option>
                        <option value="draft">Draft</option>
                    </select>

                    <label for="content">Content</label>
                    <textarea id="content" name="content" required rows="14" placeholder="<p>Your SEO article...</p>"></textarea>

                    <button type="submit" name="add_post" value="1" class="btn-primary">Create post</button>
                </form>
            </section>
        <?php endif; ?>

        <section class="admin-card admin-card-spaced">
            <h2 class="admin-section-title">All posts</h2>
            <?php if (count($posts) === 0): ?>
                <p style="color:var(--muted);">No blog posts yet.</p>
            <?php else: ?>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Status</th>
                                <th>Updated</th>
                                <th>SEO URL</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($posts as $post): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars((string) $post['title']); ?></strong></td>
                                    <td><?php echo htmlspecialchars((string) $post['status']); ?></td>
                                    <td style="font-size:0.9rem;color:var(--muted);"><?php echo htmlspecialchars((string) $post['updated_at']); ?></td>
                                    <td style="font-size:0.85rem;">
                                        <?php if ($post['status'] === 'published'): ?>
                                            <a href="<?php echo htmlspecialchars(nexora_blog_post_public_url((string) $post['slug'])); ?>" target="_blank" rel="noopener noreferrer">View</a>
                                        <?php else: ?>
                                            <span style="color:var(--muted);">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="admin-card-actions">
                                            <a href="blog-posts.php?edit=<?php echo (int) $post['id']; ?>" class="btn-primary" style="padding:8px 12px;font-size:0.9rem;">Edit</a>
                                            <form method="post" style="display:inline;" onsubmit="return confirm('Delete this blog post?');">
                                                <input type="hidden" name="delete_id" value="<?php echo (int) $post['id']; ?>">
                                                <button type="submit" class="btn-danger" style="padding:8px 12px;font-size:0.9rem;">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </main>
</div>
</body>
</html>
