<?php
require_once __DIR__ . '/includes/auth.php';
requireAdminAuth();
require_once __DIR__ . '/includes/db.php';

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/upload-helpers.php';
require_once dirname(__DIR__) . '/includes/upload-cleanup.php';

$adminPageTitle = 'Print Samples';
$success = '';
$error = '';
$maxSamples = 30;

$root = nexora_project_root();
$uploadDir = nexora_fs_path($root, 'assets', 'uploads', 'printing-samples');
$uploadDirError = nexora_ensure_upload_dirs([
    nexora_fs_path($root, 'assets', 'uploads'),
    $uploadDir,
]);
if ($uploadDirError !== null) {
    $error = $uploadDirError . ' Set owner to the web server user (e.g. www-data) and chmod 775.';
}

$uploadMaxLabel = ini_get('upload_max_filesize') ?: '2M';
$allowedExt = ['jpg', 'jpeg', 'png', 'webp'];

if (isset($_GET['saved']) && $_GET['saved'] === '1') {
    $success = 'Sample updated.';
}

function printing_samples_validate_image(array $file, array $allowedExt, bool $required, ?string &$err): ?string
{
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        if ($required) {
            $err = 'Image is required.';
            return null;
        }
        return '__skip__';
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $err = nexora_upload_error_message((int) $file['error']);
        return null;
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt, true)) {
        $err = 'Allowed image types: JPG, JPEG, PNG, WEBP.';
        return null;
    }
    return $ext;
}

$editId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
$editRow = null;
if ($editId > 0) {
    $st = $pdo->prepare('SELECT * FROM printing_samples WHERE id = ?');
    $st->execute([$editId]);
    $editRow = $st->fetch(PDO::FETCH_ASSOC) ?: null;
    if (!$editRow) {
        $editId = 0;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_id'])) {
        $delId = (int) $_POST['delete_id'];
        $st = $pdo->prepare('SELECT * FROM printing_samples WHERE id = ?');
        $st->execute([$delId]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            nexora_delete_printing_sample_file($root, $row['image_path'] ?? '');
            $pdo->prepare('DELETE FROM printing_samples WHERE id = ?')->execute([$delId]);
            $success = 'Sample removed.';
            if ($editId === $delId) {
                header('Location: printing-samples.php');
                exit;
            }
        } else {
            $error = 'Sample not found.';
        }
    } elseif (isset($_POST['add_sample'])) {
        $description = isset($_POST['description']) ? trim((string) $_POST['description']) : '';
        $sortOrder = isset($_POST['sort_order']) ? (int) $_POST['sort_order'] : 0;

        $count = (int) $pdo->query('SELECT COUNT(*) FROM printing_samples')->fetchColumn();
        if ($count >= $maxSamples) {
            $error = 'Maximum ' . $maxSamples . ' samples allowed. Remove one before adding another.';
        } elseif ($description === '') {
            $error = 'Description is required.';
        } elseif (mb_strlen($description) > 2000) {
            $error = 'Description is too long (max 2,000 characters).';
        } else {
            $errImg = null;
            $ext = printing_samples_validate_image($_FILES['sample_image'] ?? ['error' => UPLOAD_ERR_NO_FILE], $allowedExt, true, $errImg);
            if ($errImg !== null) {
                $error = $errImg;
            } elseif ($ext === null) {
                $error = 'Image is required.';
            } else {
                $pdo->beginTransaction();
                try {
                    $ins = $pdo->prepare(
                        'INSERT INTO printing_samples (description, image_path, sort_order) VALUES (?, ?, ?) RETURNING id'
                    );
                    $ins->execute([$description, 'pending', $sortOrder]);
                    $idRow = $ins->fetch(PDO::FETCH_ASSOC);
                    if (!$idRow || empty($idRow['id'])) {
                        throw new RuntimeException('Failed to create sample record.');
                    }
                    $newId = (int) $idRow['id'];

                    $filename = 'print-sample-' . $newId . '.' . $ext;
                    $absPath = $uploadDir . '/' . $filename;
                    if (!nexora_save_uploaded_image($_FILES['sample_image']['tmp_name'], $absPath, $ext)) {
                        throw new RuntimeException('Failed to save image.');
                    }

                    $relPath = 'assets/uploads/printing-samples/' . $filename;
                    $pdo->prepare(
                        'UPDATE printing_samples SET image_path = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?'
                    )->execute([$relPath, $newId]);
                    $pdo->commit();
                    $success = 'Sample added successfully.';
                } catch (Throwable $e) {
                    $pdo->rollBack();
                    if (isset($newId)) {
                        nexora_delete_printing_sample_file($root, 'assets/uploads/printing-samples/print-sample-' . $newId . '.' . $ext);
                    }
                    $error = $e instanceof RuntimeException ? $e->getMessage() : 'Could not save sample.';
                }
            }
        }
    } elseif (isset($_POST['update_sample'])) {
        $uid = isset($_POST['sample_id']) ? (int) $_POST['sample_id'] : 0;
        $description = isset($_POST['description']) ? trim((string) $_POST['description']) : '';
        $sortOrder = isset($_POST['sort_order']) ? (int) $_POST['sort_order'] : 0;

        $st = $pdo->prepare('SELECT * FROM printing_samples WHERE id = ?');
        $st->execute([$uid]);
        $row = $st->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            $error = 'Sample not found.';
        } elseif ($description === '') {
            $error = 'Description is required.';
        } elseif (mb_strlen($description) > 2000) {
            $error = 'Description is too long (max 2,000 characters).';
        } else {
            $imagePath = $row['image_path'];
            $errImg = null;
            if (isset($_FILES['sample_image']) && $_FILES['sample_image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $ext = printing_samples_validate_image($_FILES['sample_image'], $allowedExt, false, $errImg);
                if ($errImg !== null) {
                    $error = $errImg;
                } elseif ($ext !== null && $ext !== '__skip__') {
                    nexora_delete_printing_sample_file($root, $row['image_path'] ?? '');
                    $filename = 'print-sample-' . $uid . '.' . $ext;
                    $absPath = $uploadDir . '/' . $filename;
                    if (!nexora_save_uploaded_image($_FILES['sample_image']['tmp_name'], $absPath, $ext)) {
                        $error = 'Failed to replace image.';
                    } else {
                        $imagePath = 'assets/uploads/printing-samples/' . $filename;
                    }
                }
            }

            if ($error === '') {
                $pdo->prepare(
                    'UPDATE printing_samples SET description = ?, image_path = ?, sort_order = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?'
                )->execute([$description, $imagePath, $sortOrder, $uid]);
                header('Location: printing-samples.php?edit=' . $uid . '&saved=1');
                exit;
            }
        }
    }
}

$samples = $pdo->query('SELECT id, description, image_path, sort_order, created_at FROM printing_samples ORDER BY sort_order DESC, id DESC')->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/includes/header.php';
?>
<div class="admin-layout">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    <main class="admin-main">
        <div class="admin-heading">
            <h1>Print Samples</h1>
            <p>Manage sample images shown on the public Printing page. Each sample includes an image and description. Max <?php echo $maxSamples; ?> samples. Upload limit: <strong><?php echo htmlspecialchars($uploadMaxLabel); ?></strong> (JPG, PNG, WEBP).</p>
        </div>

        <?php if ($success !== ''): ?>
            <div class="alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <?php if ($error !== ''): ?>
            <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($editRow): ?>
            <section class="admin-card" style="margin-bottom:14px;">
                <h2 style="font-size:1.1rem;margin-bottom:12px;">Edit sample #<?php echo (int) $editRow['id']; ?></h2>
                <p style="color:var(--muted);margin-bottom:14px;"><a href="printing-samples.php">&larr; Back to list</a></p>
                <form class="admin-form" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="sample_id" value="<?php echo (int) $editRow['id']; ?>">

                    <label for="description">Description</label>
                    <textarea id="description" name="description" required maxlength="2000" rows="4"><?php echo htmlspecialchars((string) $editRow['description']); ?></textarea>

                    <label for="sort_order">Sort order (higher appears first)</label>
                    <input type="number" id="sort_order" name="sort_order" value="<?php echo (int) $editRow['sort_order']; ?>">

                    <label for="sample_image">Image (leave empty to keep current)</label>
                    <?php if (!empty($editRow['image_path']) && $editRow['image_path'] !== 'pending'): ?>
                        <div style="margin-bottom:8px;">
                            <img src="<?php echo BASE_URL . '/' . ltrim($editRow['image_path'], '/'); ?>" alt="" style="max-height:160px;border-radius:8px;border:1px solid var(--border);">
                        </div>
                    <?php endif; ?>
                    <input type="file" id="sample_image" name="sample_image" accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp" data-max-bytes="<?php echo (int) nexora_upload_max_bytes(); ?>">

                    <button type="submit" name="update_sample" value="1" class="btn-primary">Save changes</button>
                </form>
            </section>
        <?php else: ?>
            <section class="admin-card" style="margin-bottom:14px;">
                <h2 style="font-size:1.1rem;margin-bottom:12px;">Add sample</h2>
                <p style="margin-bottom:10px;color:#6b7280;">
                    Uploaded: <strong><?php echo count($samples); ?></strong> / <?php echo $maxSamples; ?>
                </p>
                <?php if (count($samples) < $maxSamples): ?>
                    <form class="admin-form" method="post" enctype="multipart/form-data">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" required maxlength="2000" rows="4" placeholder="Describe this print sample (e.g. A4 booklet, matte finish, full color)"></textarea>

                        <label for="sort_order">Sort order (higher appears first)</label>
                        <input type="number" id="sort_order" name="sort_order" value="0">

                        <label for="sample_image">Image</label>
                        <p class="admin-form-hint">Large images are resized automatically. Max <?php echo htmlspecialchars($uploadMaxLabel); ?> per file.</p>
                        <input type="file" id="sample_image" name="sample_image" accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp" required data-max-bytes="<?php echo (int) nexora_upload_max_bytes(); ?>">

                        <button type="submit" name="add_sample" value="1" class="btn-primary">Add sample</button>
                    </form>
                <?php else: ?>
                    <div class="alert-error">Maximum <?php echo $maxSamples; ?> samples reached. Remove one to add another.</div>
                <?php endif; ?>
            </section>
        <?php endif; ?>

        <section class="admin-card">
            <h2 style="font-size:1.1rem;margin-bottom:12px;">All samples</h2>
            <?php if (count($samples) === 0): ?>
                <p style="color:var(--muted);">No samples yet. Add one above.</p>
            <?php else: ?>
                <div class="admin-media-grid">
                    <?php foreach ($samples as $sample): ?>
                        <article class="admin-media-card">
                            <?php if (!empty($sample['image_path']) && $sample['image_path'] !== 'pending'): ?>
                                <img src="<?php echo BASE_URL . '/' . ltrim($sample['image_path'], '/'); ?>" alt="Print sample">
                            <?php endif; ?>
                            <div class="admin-media-card-body">
                                <p style="margin-bottom:8px;font-size:0.92rem;line-height:1.45;"><?php echo htmlspecialchars($sample['description']); ?></p>
                                <small style="color:#6b7280;display:block;margin-bottom:8px;">Order: <?php echo (int) $sample['sort_order']; ?></small>
                                <div class="admin-card-actions">
                                    <a href="printing-samples.php?edit=<?php echo (int) $sample['id']; ?>" class="btn-primary" style="padding:8px 12px;font-size:0.9rem;">Edit</a>
                                    <form method="post" style="display:inline;" onsubmit="return confirm('Remove this sample?');">
                                        <input type="hidden" name="delete_id" value="<?php echo (int) $sample['id']; ?>">
                                        <button type="submit" class="btn-danger" style="padding:8px 12px;font-size:0.9rem;">Delete</button>
                                    </form>
                                </div>
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
