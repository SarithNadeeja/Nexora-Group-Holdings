<?php
require_once __DIR__ . '/includes/auth.php';
requireAdminAuth();
require_once __DIR__ . '/includes/db.php';

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/upload-helpers.php';
require_once dirname(__DIR__) . '/includes/upload-cleanup.php';

$adminPageTitle = 'Digital Gallery';
$success = '';
$error = '';
$maxImages = 20;

$root = nexora_project_root();
$uploadDir = nexora_uploads_fs_path($root, 'digital-gallery');
$uploadDirError = nexora_ensure_upload_dirs([
    nexora_uploads_absolute_dir($root),
    $uploadDir,
]);
if ($uploadDirError !== null) {
    $error = $uploadDirError . ' Set owner to the web server user (e.g. www-data) and chmod 775.';
}

$uploadMaxLabel = ini_get('upload_max_filesize') ?: '2M';
$allowedExt = ['jpg', 'jpeg', 'png', 'webp'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_id'])) {
        $deleteId = (int) $_POST['delete_id'];
        $stmt = $pdo->prepare('SELECT image_path FROM digital_gallery_images WHERE id = ?');
        $stmt->execute([$deleteId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $pdo->prepare('DELETE FROM digital_gallery_images WHERE id = ?')->execute([$deleteId]);
            nexora_delete_digital_gallery_file($root, $row['image_path'] ?? '');
            $success = 'Gallery image removed.';
        } else {
            $error = 'Image not found.';
        }
    } elseif (isset($_POST['upload_image'])) {
        $count = (int) $pdo->query('SELECT COUNT(*) FROM digital_gallery_images')->fetchColumn();
        $sortOrder = isset($_POST['sort_order']) ? (int) $_POST['sort_order'] : 0;

        if ($count >= $maxImages) {
            $error = 'Maximum ' . $maxImages . ' images allowed. Remove one before uploading another.';
        } elseif (!isset($_FILES['gallery_image']) || $_FILES['gallery_image']['error'] !== UPLOAD_ERR_OK) {
            $error = isset($_FILES['gallery_image'])
                ? nexora_upload_error_message((int) $_FILES['gallery_image']['error'])
                : 'Please choose a valid image file.';
        } else {
            $file = $_FILES['gallery_image'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExt, true)) {
                $error = 'Allowed image types: JPG, JPEG, PNG, WEBP.';
            } else {
                $pdo->beginTransaction();
                try {
                    $ins = $pdo->prepare(
                        'INSERT INTO digital_gallery_images (image_path, sort_order) VALUES (?, ?) RETURNING id'
                    );
                    $ins->execute(['pending', $sortOrder]);
                    $idRow = $ins->fetch(PDO::FETCH_ASSOC);
                    if (!$idRow || empty($idRow['id'])) {
                        throw new RuntimeException('Failed to create gallery record.');
                    }
                    $newId = (int) $idRow['id'];

                    $filename = 'digital-gallery-' . $newId . '.' . $ext;
                    $absPath = $uploadDir . '/' . $filename;
                    if (!nexora_save_uploaded_image($file['tmp_name'], $absPath, $ext)) {
                        throw new RuntimeException('Failed to save image.');
                    }

                    $relPath = nexora_uploads_public_path('digital-gallery', $filename);
                    $pdo->prepare('UPDATE digital_gallery_images SET image_path = ? WHERE id = ?')->execute([$relPath, $newId]);
                    $pdo->commit();
                    $success = 'Image added to digital gallery.';
                } catch (Throwable $e) {
                    $pdo->rollBack();
                    if (isset($newId, $ext)) {
                        nexora_delete_digital_gallery_file($root, nexora_uploads_public_path('digital-gallery', 'digital-gallery-' . $newId . '.' . $ext));
                    }
                    $error = $e instanceof RuntimeException ? $e->getMessage() : 'Could not save image.';
                }
            }
        }
    }
}

$images = $pdo->query('SELECT id, image_path, sort_order, created_at FROM digital_gallery_images ORDER BY sort_order DESC, id DESC')->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/includes/header.php';
?>
<div class="admin-layout">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    <main class="admin-main">
        <div class="admin-heading">
            <h1>Digital Gallery</h1>
            <p>Manage the professional photo gallery on the public Digital page (max <?php echo $maxImages; ?> images). Higher sort order appears first.</p>
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
                    <label for="gallery_image">Add Gallery Photo</label>
                    <p class="admin-form-hint">Large images are resized automatically. Max <?php echo htmlspecialchars($uploadMaxLabel); ?> per file (JPG, PNG, WEBP).</p>
                    <input type="file" id="gallery_image" name="gallery_image" accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp" required data-max-bytes="<?php echo (int) nexora_upload_max_bytes(); ?>">

                    <label for="sort_order">Sort order (higher appears first)</label>
                    <input type="number" id="sort_order" name="sort_order" value="0">

                    <button type="submit" name="upload_image" value="1" class="btn-primary">Upload Image</button>
                </form>
            <?php else: ?>
                <div class="alert-error">Maximum <?php echo $maxImages; ?> images reached. Remove one to upload another.</div>
            <?php endif; ?>
        </section>

        <section class="admin-card" style="margin-top:14px;">
            <h2 style="font-size:1.1rem;margin-bottom:10px;">Gallery Images</h2>
            <?php if (count($images) === 0): ?>
                <p style="color:#6b7280;">No gallery images yet. Upload photos above.</p>
            <?php else: ?>
                <div class="admin-media-grid">
                    <?php foreach ($images as $image): ?>
                        <article class="admin-media-card">
                            <?php if (!empty($image['image_path']) && $image['image_path'] !== 'pending'): ?>
                                <img src="<?php echo BASE_URL . '/' . ltrim($image['image_path'], '/'); ?>" alt="Gallery image">
                            <?php endif; ?>
                            <div class="admin-media-card-body">
                                <small style="color:#6b7280;display:block;margin-bottom:4px;">Order: <?php echo (int) $image['sort_order']; ?></small>
                                <small style="color:#6b7280;display:block;margin-bottom:8px;"><?php echo htmlspecialchars($image['created_at']); ?></small>
                                <form method="post" onsubmit="return confirm('Remove this gallery image?');">
                                    <input type="hidden" name="delete_id" value="<?php echo (int) $image['id']; ?>">
                                    <button type="submit" class="btn-danger">Remove</button>
                                </form>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>
</div>
<script>
(function () {
    var maxHint = <?php echo json_encode($uploadMaxLabel); ?>;
    document.querySelectorAll('input[type="file"][data-max-bytes]').forEach(function (input) {
        input.addEventListener('change', function () {
            var file = input.files && input.files[0];
            if (!file) {
                return;
            }
            var max = parseInt(input.getAttribute('data-max-bytes') || '0', 10);
            if (max > 0 && file.size > max) {
                alert('This photo is too large (' + Math.round(file.size / (1024 * 1024)) + ' MB). Maximum is ' + maxHint + '.');
                input.value = '';
            }
        });
    });
})();
</script>
</body>
</html>
