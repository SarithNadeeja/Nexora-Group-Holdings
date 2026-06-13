<?php
require_once __DIR__ . '/includes/auth.php';
requireAdminAuth();
require_once __DIR__ . '/includes/db.php';

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/upload-helpers.php';

$adminPageTitle = 'Agro Shop Items';
$success = '';
$error = '';

$root = dirname(__DIR__);
$agroItemsUploadRoot = $root . '/assets/uploads/agro/items';
if (!nexora_ensure_upload_dir($agroItemsUploadRoot)) {
    $error = 'Upload folder is not writable. On the server, set permissions on assets/uploads/agro/ (chmod 775 or 777).';
}

$uploadMaxLabel = ini_get('upload_max_filesize') ?: '2M';

if (isset($_GET['saved']) && $_GET['saved'] === '1') {
    $success = 'Product updated.';
}

$allowedExt = ['jpg', 'jpeg', 'png', 'webp'];

$validStock = ['pre_order', 'out_of_stock', 'in_stock'];

function agro_shop_validate_image(array $file, array $allowedExt, bool $required, ?string &$err): ?string
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

function agro_shop_unlink_stored_path(string $root, ?string $relative): void
{
    if ($relative === null || $relative === '') {
        return;
    }
    $abs = $root . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($relative, '/\\'));
    $real = realpath($abs);
    $agroUploadRoot = realpath($root . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'agro');
    if ($real === false || $agroUploadRoot === false || !is_file($real)) {
        return;
    }
    if (strpos($real, $agroUploadRoot) !== 0) {
        return;
    }
    @unlink($real);
}

/**
 * Recursively remove assets/uploads/agro/items/{id} (all files and the folder).
 */
function agro_shop_remove_item_directory(string $root, int $id): void
{
    $dir = $root . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'agro' . DIRECTORY_SEPARATOR . 'items' . DIRECTORY_SEPARATOR . $id;
    if (!is_dir($dir)) {
        return;
    }

    try {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($iterator as $item) {
            $path = $item->getRealPath();
            if ($path === false) {
                continue;
            }
            if ($item->isDir()) {
                @rmdir($path);
            } else {
                @unlink($path);
            }
        }
    } catch (Throwable $e) {
        foreach (glob($dir . DIRECTORY_SEPARATOR . '*') ?: [] as $f) {
            if (is_file($f)) {
                @unlink($f);
            }
        }
    }
    @rmdir($dir);
}

/**
 * Remove all images for a shop item: delete each file from DB paths, then remove items/{id} entirely.
 */
function agro_shop_delete_item_assets(string $root, array $row, int $id): void
{
    $cols = ['image_main', 'image_gallery_1', 'image_gallery_2', 'image_gallery_3', 'image_gallery_4'];
    foreach ($cols as $col) {
        if (!empty($row[$col])) {
            agro_shop_unlink_stored_path($root, (string) $row[$col]);
        }
    }
    agro_shop_remove_item_directory($root, $id);
}

function agro_shop_save_image(string $tmp, string $destPath, string $ext): bool
{
    return nexora_save_uploaded_image($tmp, $destPath, $ext);
}

$editId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
$editRow = null;
if ($editId > 0) {
    $st = $pdo->prepare('SELECT * FROM agro_shop_items WHERE id = ?');
    $st->execute([$editId]);
    $editRow = $st->fetch(PDO::FETCH_ASSOC) ?: null;
    if (!$editRow) {
        $editId = 0;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_id'])) {
        $delId = (int) $_POST['delete_id'];
        $st = $pdo->prepare('SELECT * FROM agro_shop_items WHERE id = ?');
        $st->execute([$delId]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            agro_shop_delete_item_assets($root, $row, $delId);
            $pdo->prepare('DELETE FROM agro_shop_items WHERE id = ?')->execute([$delId]);
            $success = 'Product removed.';
            if ($editId === $delId) {
                header('Location: agro-shop.php');
                exit;
            }
        } else {
            $error = 'Product not found.';
        }
    } elseif (isset($_POST['add_item'])) {
        $name = isset($_POST['name']) ? trim((string) $_POST['name']) : '';
        $price = isset($_POST['price']) ? (float) $_POST['price'] : 0;
        $stock = isset($_POST['stock_status']) ? (string) $_POST['stock_status'] : '';
        $description = isset($_POST['description']) ? trim((string) $_POST['description']) : '';

        if ($name === '' || mb_strlen($name) > 255) {
            $error = 'Please enter a valid name (max 255 characters).';
        } elseif (mb_strlen($description) > 10000) {
            $error = 'Description is too long (max 10,000 characters).';
        } elseif ($price < 0 || $price > 99999999.99) {
            $error = 'Please enter a valid price.';
        } elseif (!in_array($stock, $validStock, true)) {
            $error = 'Please select a valid stock status.';
        } else {
            $errMain = null;
            $mainExt = agro_shop_validate_image($_FILES['image_main'] ?? ['error' => UPLOAD_ERR_NO_FILE], $allowedExt, true, $errMain);
            if ($errMain !== null) {
                $error = $errMain;
            } elseif ($mainExt === null) {
                $error = 'Main photo is required.';
            } else {
                $galleryPaths = [null, null, null, null];
                $galleryExts = [];
                $galErr = null;
                for ($i = 1; $i <= 4; $i++) {
                    $field = 'image_gallery_' . $i;
                    $f = $_FILES[$field] ?? ['error' => UPLOAD_ERR_NO_FILE];
                    $req = false;
                    $e = null;
                    $ext = agro_shop_validate_image($f, $allowedExt, $req, $e);
                    if ($e !== null) {
                        $galErr = $e;
                        break;
                    }
                    if ($ext === '__skip__') {
                        $galleryExts[$i] = null;
                    } else {
                        $galleryExts[$i] = $ext;
                    }
                }
                if ($galErr !== null) {
                    $error = $galErr;
                } else {
                    $pdo->beginTransaction();
                    try {
                        $ins = $pdo->prepare(
                            'INSERT INTO agro_shop_items (name, price, stock_status, description) VALUES (?, ?, ?, ?) RETURNING id'
                        );
                        $ins->execute([$name, $price, $stock, $description === '' ? null : $description]);
                        $idRow = $ins->fetch(PDO::FETCH_ASSOC);
                        if (!$idRow || empty($idRow['id'])) {
                            throw new RuntimeException('Failed to create product record.');
                        }
                        $newId = (int) $idRow['id'];

                        $itemDir = $root . '/assets/uploads/agro/items/' . $newId;
                        if (!is_dir($itemDir) && !mkdir($itemDir, 0775, true)) {
                            throw new RuntimeException('Could not create upload folder.');
                        }

                        $mainName = 'main.' . $mainExt;
                        $mainAbs = $itemDir . '/' . $mainName;
                        if (!agro_shop_save_image($_FILES['image_main']['tmp_name'], $mainAbs, $mainExt)) {
                            throw new RuntimeException('Failed to save main image. Check that assets/uploads/agro/ is writable on the server.');
                        }
                        $mainRel = 'assets/uploads/agro/items/' . $newId . '/' . $mainName;

                        for ($i = 1; $i <= 4; $i++) {
                            if (!empty($galleryExts[$i])) {
                                $gName = 'gallery_' . $i . '.' . $galleryExts[$i];
                                $gAbs = $itemDir . '/' . $gName;
                                if (!agro_shop_save_image($_FILES['image_gallery_' . $i]['tmp_name'], $gAbs, $galleryExts[$i])) {
                                    throw new RuntimeException('Failed to save gallery image ' . $i . '.');
                                }
                                $galleryPaths[$i - 1] = 'assets/uploads/agro/items/' . $newId . '/' . $gName;
                            }
                        }

                        $pdo->prepare(
                            'UPDATE agro_shop_items SET image_main = ?, image_gallery_1 = ?, image_gallery_2 = ?, image_gallery_3 = ?, image_gallery_4 = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?'
                        )->execute([
                            $mainRel,
                            $galleryPaths[0],
                            $galleryPaths[1],
                            $galleryPaths[2],
                            $galleryPaths[3],
                            $newId,
                        ]);
                        $pdo->commit();
                        $success = 'Product added successfully.';
                    } catch (Throwable $e) {
                        $pdo->rollBack();
                        if (isset($newId)) {
                            agro_shop_remove_item_directory($root, $newId);
                        }
                        $error = $e instanceof RuntimeException ? $e->getMessage() : 'Could not save product.';
                    }
                }
            }
        }
    } elseif (isset($_POST['update_item'])) {
        $uid = isset($_POST['item_id']) ? (int) $_POST['item_id'] : 0;
        $name = isset($_POST['name']) ? trim((string) $_POST['name']) : '';
        $price = isset($_POST['price']) ? (float) $_POST['price'] : 0;
        $stock = isset($_POST['stock_status']) ? (string) $_POST['stock_status'] : '';
        $description = isset($_POST['description']) ? trim((string) $_POST['description']) : '';

        $st = $pdo->prepare('SELECT * FROM agro_shop_items WHERE id = ?');
        $st->execute([$uid]);
        $row = $st->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            $error = 'Product not found.';
        } elseif ($name === '' || mb_strlen($name) > 255) {
            $error = 'Please enter a valid name.';
        } elseif (mb_strlen($description) > 10000) {
            $error = 'Description is too long (max 10,000 characters).';
        } elseif ($price < 0) {
            $error = 'Please enter a valid price.';
        } elseif (!in_array($stock, $validStock, true)) {
            $error = 'Invalid stock status.';
        } else {
            $error = '';
            $itemDir = $root . '/assets/uploads/agro/items/' . $uid;
            if (!is_dir($itemDir)) {
                mkdir($itemDir, 0775, true);
            }

            $mainRel = $row['image_main'];
            $g = [
                $row['image_gallery_1'],
                $row['image_gallery_2'],
                $row['image_gallery_3'],
                $row['image_gallery_4'],
            ];

            $errMain = null;
            if (isset($_FILES['image_main']) && $_FILES['image_main']['error'] !== UPLOAD_ERR_NO_FILE) {
                $mainExt = agro_shop_validate_image($_FILES['image_main'], $allowedExt, false, $errMain);
                if ($errMain !== null) {
                    $error = $errMain;
                } elseif ($mainExt !== null && $mainExt !== '__skip__') {
                    $mainName = 'main.' . $mainExt;
                    $mainAbs = $itemDir . '/' . $mainName;
                    if (!empty($row['image_main'])) {
                        $oldAbs = $root . '/' . ltrim((string) $row['image_main'], '/');
                        if (is_file($oldAbs)) {
                            @unlink($oldAbs);
                        }
                    }
                    if (agro_shop_save_image($_FILES['image_main']['tmp_name'], $mainAbs, $mainExt)) {
                        $mainRel = 'assets/uploads/agro/items/' . $uid . '/' . $mainName;
                    } else {
                        $error = 'Failed to replace main image.';
                    }
                }
            }

            if ($error === '') {
                for ($i = 1; $i <= 4; $i++) {
                    $field = 'image_gallery_' . $i;
                    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
                        continue;
                    }
                    $ge = null;
                    $gext = agro_shop_validate_image($_FILES[$field], $allowedExt, false, $ge);
                    if ($ge !== null) {
                        $error = $ge;
                        break;
                    }
                    if ($gext !== null && $gext !== '__skip__') {
                        $gName = 'gallery_' . $i . '.' . $gext;
                        $gAbs = $itemDir . '/' . $gName;
                        $idx = $i - 1;
                        if (!empty($g[$idx])) {
                            $oldG = $root . '/' . ltrim((string) $g[$idx], '/');
                            if (is_file($oldG)) {
                                @unlink($oldG);
                            }
                        }
                        if (agro_shop_save_image($_FILES[$field]['tmp_name'], $gAbs, $gext)) {
                            $g[$idx] = 'assets/uploads/agro/items/' . $uid . '/' . $gName;
                        } else {
                            $error = 'Failed to save gallery image ' . $i . '.';
                            break;
                        }
                    }
                }
            }

            if ($error === '') {
                $pdo->prepare(
                    'UPDATE agro_shop_items SET name = ?, price = ?, stock_status = ?, description = ?, image_main = ?, image_gallery_1 = ?, image_gallery_2 = ?, image_gallery_3 = ?, image_gallery_4 = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?'
                )->execute([$name, $price, $stock, $description === '' ? null : $description, $mainRel, $g[0], $g[1], $g[2], $g[3], $uid]);
                header('Location: agro-shop.php?edit=' . $uid . '&saved=1');
                exit;
            }
        }
    }
}

$items = $pdo->query('SELECT id, name, price, stock_status, image_main, created_at FROM agro_shop_items ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/includes/header.php';
?>
<div class="admin-layout">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    <main class="admin-main">
        <div class="admin-heading">
            <h1>Agro Shop Items</h1>
            <p>Add products shown on the public Agro page. Images are stored under <code>assets/uploads/agro/items/{id}/</code>. Max upload size per photo: <strong><?php echo htmlspecialchars($uploadMaxLabel); ?></strong> (JPG, PNG, WEBP).</p>
        </div>

        <?php if ($success !== ''): ?>
            <div class="alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <?php if ($error !== ''): ?>
            <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($editRow): ?>
            <section class="admin-card" style="margin-bottom:14px;">
                <h2 style="font-size:1.1rem;margin-bottom:12px;">Edit product #<?php echo (int) $editRow['id']; ?></h2>
                <p style="color:var(--muted);margin-bottom:14px;"><a href="agro-shop.php">&larr; Back to list</a></p>
                <form class="admin-form" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="item_id" value="<?php echo (int) $editRow['id']; ?>">

                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" required maxlength="255" value="<?php echo htmlspecialchars($editRow['name']); ?>">

                    <label for="price">Price (LKR)</label>
                    <input type="number" id="price" name="price" min="0" step="0.01" required value="<?php echo htmlspecialchars((string) $editRow['price']); ?>">

                    <label for="stock_status">Stock</label>
                    <select id="stock_status" name="stock_status" required style="width:100%;padding:11px 12px;border-radius:10px;border:1px solid var(--border);">
                        <option value="in_stock" <?php echo $editRow['stock_status'] === 'in_stock' ? 'selected' : ''; ?>>In stock</option>
                        <option value="pre_order" <?php echo $editRow['stock_status'] === 'pre_order' ? 'selected' : ''; ?>>Pre-order</option>
                        <option value="out_of_stock" <?php echo $editRow['stock_status'] === 'out_of_stock' ? 'selected' : ''; ?>>Out of stock</option>
                    </select>

                    <label for="description">Description</label>
                    <textarea id="description" name="description" maxlength="10000" placeholder="Product details for the public product page"><?php echo htmlspecialchars((string) ($editRow['description'] ?? '')); ?></textarea>

                    <label for="image_main">Main photo (leave empty to keep current)</label>
                    <?php if (!empty($editRow['image_main'])): ?>
                        <div style="margin-bottom:8px;">
                            <img src="<?php echo BASE_URL . '/' . ltrim($editRow['image_main'], '/'); ?>" alt="" style="max-height:120px;border-radius:8px;border:1px solid var(--border);">
                        </div>
                    <?php endif; ?>
                    <input type="file" id="image_main" name="image_main" accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp" data-max-bytes="<?php echo (int) nexora_upload_max_bytes(); ?>">

                    <?php for ($i = 1; $i <= 4; $i++): ?>
                        <?php $col = 'image_gallery_' . $i; ?>
                        <label for="image_gallery_<?php echo $i; ?>">Gallery photo <?php echo $i; ?> (optional, replaces current if uploaded)</label>
                        <?php if (!empty($editRow[$col])): ?>
                            <div style="margin-bottom:8px;">
                                <img src="<?php echo BASE_URL . '/' . ltrim($editRow[$col], '/'); ?>" alt="" style="max-height:80px;border-radius:8px;border:1px solid var(--border);">
                            </div>
                        <?php endif; ?>
                        <input type="file" id="image_gallery_<?php echo $i; ?>" name="image_gallery_<?php echo $i; ?>" accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp" data-max-bytes="<?php echo (int) nexora_upload_max_bytes(); ?>">
                    <?php endfor; ?>

                    <button type="submit" name="update_item" value="1" class="btn-primary">Save changes</button>
                </form>
            </section>
        <?php else: ?>
            <section class="admin-card" style="margin-bottom:14px;">
                <h2 style="font-size:1.1rem;margin-bottom:12px;">Add product</h2>
                <form class="admin-form" method="post" enctype="multipart/form-data">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" required maxlength="255">

                    <label for="price">Price (LKR)</label>
                    <input type="number" id="price" name="price" min="0" step="0.01" required>

                    <label for="stock_status">Stock</label>
                    <select id="stock_status" name="stock_status" required style="width:100%;padding:11px 12px;border-radius:10px;border:1px solid var(--border);">
                        <option value="in_stock">In stock</option>
                        <option value="pre_order">Pre-order</option>
                        <option value="out_of_stock">Out of stock</option>
                    </select>

                    <label for="description">Description</label>
                    <textarea id="description" name="description" maxlength="10000" placeholder="Shown on the public product page"></textarea>

                    <label for="image_main">Main photo</label>
                    <p class="admin-form-hint">Phone photos are OK; large images are resized automatically. If upload fails, try a smaller JPG under <?php echo htmlspecialchars($uploadMaxLabel); ?>.</p>
                    <input type="file" id="image_main" name="image_main" accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp" required data-max-bytes="<?php echo (int) nexora_upload_max_bytes(); ?>">

                    <?php for ($i = 1; $i <= 4; $i++): ?>
                        <label for="image_gallery_<?php echo $i; ?>">Gallery photo <?php echo $i; ?> (optional)</label>
                        <input type="file" id="image_gallery_<?php echo $i; ?>" name="image_gallery_<?php echo $i; ?>" accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp" data-max-bytes="<?php echo (int) nexora_upload_max_bytes(); ?>">
                    <?php endfor; ?>

                    <button type="submit" name="add_item" value="1" class="btn-primary">Add product</button>
                </form>
            </section>
        <?php endif; ?>

        <section class="admin-card">
            <h2 style="font-size:1.1rem;margin-bottom:12px;">All products</h2>
            <?php if (count($items) === 0): ?>
                <p style="color:var(--muted);">No products yet. Add one above.</p>
            <?php else: ?>
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:14px;">
                    <?php foreach ($items as $it): ?>
                        <article style="border:1px solid var(--border);border-radius:12px;overflow:hidden;background:#fff;">
                            <?php if (!empty($it['image_main'])): ?>
                                <img src="<?php echo BASE_URL . '/' . ltrim($it['image_main'], '/'); ?>" alt="" style="width:100%;height:140px;object-fit:cover;">
                            <?php endif; ?>
                            <div style="padding:12px;">
                                <strong style="display:block;margin-bottom:6px;"><?php echo htmlspecialchars($it['name']); ?></strong>
                                <small style="color:var(--muted);display:block;margin-bottom:6px;"><?php echo htmlspecialchars($it['stock_status']); ?> &middot; LKR <?php echo htmlspecialchars(number_format((float) $it['price'], 2)); ?></small>
                                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                                    <a href="agro-shop.php?edit=<?php echo (int) $it['id']; ?>" class="btn-primary" style="display:inline-block;text-align:center;padding:8px 12px;font-size:0.9rem;">Edit</a>
                                    <form method="post" style="display:inline;" onsubmit="return confirm('Delete this product and all its images?');">
                                        <input type="hidden" name="delete_id" value="<?php echo (int) $it['id']; ?>">
                                        <button type="submit" class="btn-primary" style="background:#dc2626;padding:8px 12px;font-size:0.9rem;">Delete</button>
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
                alert('This photo is too large (' + Math.round(file.size / (1024 * 1024)) + ' MB). Maximum is ' + maxHint + '. Choose a smaller image or reduce camera quality in phone settings.');
                input.value = '';
            }
        });
    });
})();
</script>
</body>
</html>
