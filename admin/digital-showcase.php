<?php
require_once __DIR__ . '/includes/auth.php';
requireAdminAuth();
require_once __DIR__ . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/upload-cleanup.php';

$adminPageTitle = 'Digital Showcase Images';
$success = '';
$error = '';
$maxImages = 10;
$root = dirname(__DIR__);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_id'])) {
        $deleteId = (int) $_POST['delete_id'];
        $stmt = $pdo->prepare('SELECT image_path FROM digital_featured_images WHERE id = ?');
        $stmt->execute([$deleteId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $pdo->prepare('DELETE FROM digital_featured_images WHERE id = ?')->execute([$deleteId]);
            nexora_delete_showcase_image_file($root, $row['image_path'] ?? '');
            $success = 'Image removed successfully.';
        } else {
            $error = 'Image not found.';
        }
    } elseif (isset($_POST['upload_image'])) {
        $count = (int) $pdo->query('SELECT COUNT(*) FROM digital_featured_images')->fetchColumn();
        if ($count >= $maxImages) {
            $error = 'Maximum 10 images allowed. Remove an image before uploading new ones.';
        } elseif (!isset($_FILES['showcase_image']) || $_FILES['showcase_image']['error'] !== UPLOAD_ERR_OK) {
            $error = 'Please choose a valid image file.';
        } else {
            $file = $_FILES['showcase_image'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            if (!in_array($ext, $allowed, true)) {
                $error = 'Allowed image types: JPG, JPEG, PNG, WEBP.';
            } else {
                $uploadDir = dirname(__DIR__) . '/assets/uploads/digital-featured';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0775, true);
                }

                $filename = 'digital-show-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
                $target = $uploadDir . '/' . $filename;
                if (!move_uploaded_file($file['tmp_name'], $target)) {
                    $error = 'Failed to store uploaded image.';
                } else {
                    $relativePath = 'assets/uploads/digital-featured/' . $filename;
                    $stmt = $pdo->prepare('INSERT INTO digital_featured_images (image_path) VALUES (?)');
                    $stmt->execute([$relativePath]);
                    $success = 'Image added to digital showcase.';
                }
            }
        }
    }
}

$images = $pdo->query('SELECT id, image_path, created_at FROM digital_featured_images ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<div class="admin-layout">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    <main class="admin-main">
        <div class="admin-heading">
            <h1>Digital Showcase Images</h1>
            <p>Manage homepage Digital showcase images (max 10).</p>
        </div>

        <section class="admin-card">
            <?php if ($success !== ''): ?>
                <div class="alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <?php if ($error !== ''): ?>
                <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <p style="margin-bottom:10px;color:#6b7280;">
                Uploaded: <strong><?php echo count($images); ?></strong> / <?php echo $maxImages; ?>
            </p>

            <?php if (count($images) < $maxImages): ?>
                <form class="admin-form" method="post" enctype="multipart/form-data">
                    <label for="showcase_image">Add Showcase Image</label>
                    <input type="file" id="showcase_image" name="showcase_image" accept=".jpg,.jpeg,.png,.webp" required>
                    <button type="submit" name="upload_image" value="1" class="btn-primary">Upload Image</button>
                </form>
            <?php else: ?>
                <div class="alert-error">Maximum 10 images reached. Remove one to upload another.</div>
            <?php endif; ?>
        </section>

        <section class="admin-card" style="margin-top:14px;">
            <h2 style="font-size:1.1rem;margin-bottom:10px;">Current Showcase Images</h2>
            <?php if (count($images) === 0): ?>
                <p style="color:#6b7280;">No showcase images uploaded yet.</p>
            <?php else: ?>
                <div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;">
                    <?php foreach ($images as $image): ?>
                        <article style="border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;background:#fff;">
                            <img src="<?php echo BASE_URL . '/' . ltrim($image['image_path'], '/'); ?>" alt="Showcase image" style="width:100%;height:140px;object-fit:cover;display:block;">
                            <div style="padding:10px;">
                                <small style="color:#6b7280;display:block;margin-bottom:8px;"><?php echo htmlspecialchars($image['created_at']); ?></small>
                                <form method="post" onsubmit="return confirm('Remove this image?');">
                                    <input type="hidden" name="delete_id" value="<?php echo (int) $image['id']; ?>">
                                    <button type="submit" class="btn-primary" style="background:#dc2626;">Remove</button>
                                </form>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>
</div>
</body>
</html>

