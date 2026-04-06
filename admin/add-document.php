<?php
require_once __DIR__ . '/includes/auth.php';
requireAdminAuth();
require_once __DIR__ . '/includes/db.php';

$adminPageTitle = 'Add Document';
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $pages = isset($_POST['pages']) ? (int) $_POST['pages'] : 0;
    $price = isset($_POST['price']) ? (float) $_POST['price'] : 0;

    if ($name === '' || $pages <= 0 || $price < 0) {
        $error = 'Please provide valid name, pages, and price.';
    } elseif (!isset($_FILES['image_file']) || !isset($_FILES['pdf_file'])) {
        $error = 'Please upload both image and PDF files.';
    } else {
        $imageFile = $_FILES['image_file'];
        $pdfFile = $_FILES['pdf_file'];

        if ($imageFile['error'] !== UPLOAD_ERR_OK || $pdfFile['error'] !== UPLOAD_ERR_OK) {
            $error = 'File upload failed. Please try again.';
        } else {
            $baseUploadDir = dirname(__DIR__) . '/assets/uploads';
            $imageDir = $baseUploadDir . '/images';
            $pdfDir = $baseUploadDir . '/pdfs';

            if (!is_dir($imageDir)) {
                mkdir($imageDir, 0775, true);
            }
            if (!is_dir($pdfDir)) {
                mkdir($pdfDir, 0775, true);
            }

            $imageExt = strtolower(pathinfo($imageFile['name'], PATHINFO_EXTENSION));
            $pdfExt = strtolower(pathinfo($pdfFile['name'], PATHINFO_EXTENSION));

            $allowedImage = ['jpg', 'jpeg', 'png', 'webp'];
            if (!in_array($imageExt, $allowedImage, true)) {
                $error = 'Image must be JPG, JPEG, PNG, or WEBP.';
            } elseif ($pdfExt !== 'pdf') {
                $error = 'PDF file must have .pdf extension.';
            } else {
                $safeBase = preg_replace('/[^a-zA-Z0-9_-]/', '-', strtolower($name));
                $unique = date('YmdHis') . '-' . bin2hex(random_bytes(4));

                $imageName = $safeBase . '-' . $unique . '.' . $imageExt;
                $pdfName = $safeBase . '-' . $unique . '.pdf';

                $imageTarget = $imageDir . '/' . $imageName;
                $pdfTarget = $pdfDir . '/' . $pdfName;

                if (!move_uploaded_file($imageFile['tmp_name'], $imageTarget)) {
                    $error = 'Failed to save image file.';
                } elseif (!move_uploaded_file($pdfFile['tmp_name'], $pdfTarget)) {
                    $error = 'Failed to save PDF file.';
                } else {
                    $imagePath = 'assets/uploads/images/' . $imageName;
                    $pdfPath = 'assets/uploads/pdfs/' . $pdfName;

                    $stmt = $pdo->prepare(
                        'INSERT INTO print_documents (name, pages, image_path, pdf_path, price) VALUES (?, ?, ?, ?, ?)'
                    );

                    if ($stmt->execute([$name, $pages, $imagePath, $pdfPath, $price])) {
                        $success = 'Document added successfully.';
                    } else {
                        $error = 'Database insert failed.';
                    }
                }
            }
        }
    }
}

include __DIR__ . '/includes/header.php';
?>
<div class="admin-layout">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    <main class="admin-main">
        <div class="admin-heading">
            <h1>Add New Document</h1>
            <p>Upload image and PDF files for Nexora Printing page.</p>
        </div>

        <section class="admin-card">
            <?php if ($success !== ''): ?>
                <div class="alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <?php if ($error !== ''): ?>
                <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form class="admin-form" method="post" action="" enctype="multipart/form-data">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" required>

                <label for="pages">Pages</label>
                <input type="number" id="pages" name="pages" min="1" required>

                <label for="price">Price</label>
                <input type="number" id="price" name="price" min="0" step="0.01" required>

                <label for="image_file">Upload Image</label>
                <input type="file" id="image_file" name="image_file" accept=".jpg,.jpeg,.png,.webp" required>

                <label for="pdf_file">Upload PDF</label>
                <input type="file" id="pdf_file" name="pdf_file" accept=".pdf" required>

                <button type="submit" class="btn-primary">Save Document</button>
            </form>
        </section>
    </main>
</div>
</body>
</html>

