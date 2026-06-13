<?php
/**
 * Delete uploaded files under assets/uploads/ safely.
 */

/**
 * Resolve a stored relative path to an absolute path inside assets/uploads/.
 */
function nexora_uploads_absolute_path(string $root, string $relativePath, string $requiredPrefix): ?string
{
    $relativePath = ltrim(str_replace('\\', '/', trim($relativePath)), '/');
    $requiredPrefix = rtrim(str_replace('\\', '/', $requiredPrefix), '/') . '/';

    if ($relativePath === '' || strpos($relativePath, '..') !== false) {
        return null;
    }
    if (strpos($relativePath, $requiredPrefix) !== 0) {
        return null;
    }

    $uploadsRoot = str_replace('\\', '/', $root) . '/assets/uploads';
    $absolute = str_replace('\\', '/', $root) . '/' . $relativePath;

    if (strpos($absolute, $uploadsRoot) !== 0) {
        return null;
    }

    return $absolute;
}

/**
 * Delete a single file stored under assets/uploads/.
 */
function nexora_delete_upload_file(string $root, ?string $relativePath, string $requiredPrefix): bool
{
    if ($relativePath === null || trim($relativePath) === '') {
        return false;
    }

    $absolute = nexora_uploads_absolute_path($root, $relativePath, $requiredPrefix);
    if ($absolute === null || !is_file($absolute)) {
        return false;
    }

    return @unlink($absolute);
}

/**
 * Delete print document cover image and PDF.
 */
function nexora_delete_print_document_files(string $root, array $row): void
{
    nexora_delete_upload_file($root, isset($row['image_path']) ? (string) $row['image_path'] : '', 'assets/uploads/images');
    nexora_delete_upload_file($root, isset($row['pdf_path']) ? (string) $row['pdf_path'] : '', 'assets/uploads/pdfs');
}

/**
 * Delete digital showcase image file.
 */
function nexora_delete_showcase_image_file(string $root, ?string $relativePath): void
{
    nexora_delete_upload_file($root, $relativePath, 'assets/uploads/digital-featured');
}

/**
 * Recursively remove assets/uploads/agro/items/{id}/ and all contents.
 */
function nexora_delete_agro_item_directory(string $root, int $id): void
{
    if ($id <= 0) {
        return;
    }

    $dir = str_replace('\\', '/', $root) . '/assets/uploads/agro/items/' . $id;
    if (!is_dir($dir)) {
        return;
    }

    try {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($iterator as $item) {
            $path = $item->getPathname();
            if ($item->isDir()) {
                @rmdir($path);
            } else {
                @unlink($path);
            }
        }
    } catch (Throwable $e) {
        foreach (glob($dir . '/*') ?: [] as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
    }

    @rmdir($dir);
}

/**
 * Delete all agro product images and its upload folder.
 */
function nexora_delete_agro_item_files(string $root, array $row, int $id): void
{
    $cols = ['image_main', 'image_gallery_1', 'image_gallery_2', 'image_gallery_3', 'image_gallery_4'];
    foreach ($cols as $col) {
        if (!empty($row[$col])) {
            nexora_delete_upload_file($root, (string) $row[$col], 'assets/uploads/agro');
        }
    }
    nexora_delete_agro_item_directory($root, $id);
}
