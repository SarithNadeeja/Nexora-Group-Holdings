<?php
/**
 * Shared upload helpers for admin file handling.
 */

/**
 * Human-readable message for PHP upload error codes (for non-technical users).
 */
function nexora_upload_error_message(int $code): string
{
    $maxUpload = ini_get('upload_max_filesize') ?: 'unknown';
    $maxPost = ini_get('post_max_size') ?: 'unknown';

    switch ($code) {
        case UPLOAD_ERR_INI_SIZE:
            return 'Photo is too large for the server (limit ' . $maxUpload . '). '
                . 'On a phone, choose a smaller image, email it to yourself and save a compressed copy, or use a photo editor to reduce size before uploading.';
        case UPLOAD_ERR_FORM_SIZE:
            return 'Photo is too large (form limit). Maximum allowed is about ' . $maxUpload . '.';
        case UPLOAD_ERR_PARTIAL:
            return 'Upload was interrupted. Check your internet connection and try again.';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'Server upload folder is missing. Ask your host to enable PHP file uploads.';
        case UPLOAD_ERR_CANT_WRITE:
            return 'Server could not save the file. The uploads folder may need write permission (assets/uploads/).';
        case UPLOAD_ERR_EXTENSION:
            return 'Upload blocked by a server security rule. Contact your hosting provider.';
        case UPLOAD_ERR_NO_FILE:
            return 'No file was received. If you selected a photo on mobile, it may be too large (max ' . $maxUpload . ', total form ' . $maxPost . ').';
        default:
            return 'Image upload failed (server code ' . $code . '). Try a smaller JPG or PNG under ' . $maxUpload . '.';
    }
}

/**
 * Normalize and resolve an absolute filesystem path under the project.
 */
function nexora_fs_path(string $baseDir, string ...$parts): string
{
    $path = str_replace('\\', '/', $baseDir);
    $resolved = realpath($path);
    if ($resolved !== false) {
        $path = str_replace('\\', '/', $resolved);
    } else {
        $path = rtrim($path, '/');
    }

    foreach ($parts as $part) {
        $part = trim(str_replace('\\', '/', $part), '/');
        if ($part !== '') {
            $path .= '/' . $part;
        }
    }

    return $path;
}

/**
 * Project root directory (parent of includes/, admin/, assets/, etc.).
 */
function nexora_project_root(): string
{
    return nexora_fs_path(dirname(__DIR__));
}

/**
 * URL/DB-relative prefix for uploaded files (public paths stay assets/uploads/...).
 */
function nexora_uploads_url_prefix(): string
{
    return 'assets/uploads';
}

/**
 * Default external upload storage on Ubuntu production (outside Git).
 */
function nexora_uploads_external_default(): string
{
    return '/var/www/nexora-uploads';
}

/**
 * Absolute filesystem directory for uploads (follows assets/uploads symlink).
 * Override with env NEXORA_UPLOADS_PATH for custom locations.
 */
function nexora_uploads_absolute_dir(?string $root = null): string
{
    if ($root === null) {
        $root = nexora_project_root();
    }

    $env = getenv('NEXORA_UPLOADS_PATH');
    if ($env !== false && trim($env) !== '') {
        return rtrim(str_replace('\\', '/', trim($env)), '/');
    }

    $linkPath = nexora_fs_path($root, 'assets', 'uploads');
    $resolved = realpath($linkPath);
    if ($resolved !== false) {
        return str_replace('\\', '/', $resolved);
    }

    return str_replace('\\', '/', $linkPath);
}

/**
 * Build a public/DB-relative path under assets/uploads/.
 */
function nexora_uploads_public_path(string ...$parts): string
{
    $segments = [nexora_uploads_url_prefix()];
    foreach ($parts as $part) {
        $part = trim(str_replace('\\', '/', $part), '/');
        if ($part !== '') {
            $segments[] = $part;
        }
    }

    return implode('/', $segments);
}

/**
 * Build an absolute filesystem path inside the upload store.
 */
function nexora_uploads_fs_path(?string $root, string ...$parts): string
{
    $path = nexora_uploads_absolute_dir($root);
    foreach ($parts as $part) {
        $part = trim(str_replace('\\', '/', $part), '/');
        if ($part !== '') {
            $path .= '/' . $part;
        }
    }

    return $path;
}

/**
 * True if PHP can write a file into this directory (more reliable than is_writable() alone).
 */
function nexora_dir_is_writable(string $dir): bool
{
    if (!is_dir($dir)) {
        return false;
    }
    if (is_writable($dir)) {
        return true;
    }
    $probe = $dir . '/.nexora_write_' . bin2hex(random_bytes(4));
    $ok = @file_put_contents($probe, 'ok') !== false;
    if ($ok) {
        @unlink($probe);
    }
    return $ok;
}

/**
 * Ensure a writable directory exists (recursive).
 */
function nexora_ensure_upload_dir(string $absolutePath, int $mode = 0775): bool
{
    $absolutePath = str_replace('\\', '/', $absolutePath);

    if (!is_dir($absolutePath)) {
        @mkdir($absolutePath, $mode, true);
        if (!is_dir($absolutePath)) {
            return false;
        }
        @chmod($absolutePath, $mode);
    }

    return nexora_dir_is_writable($absolutePath);
}

/**
 * Ensure several upload directories exist. Returns an error message or null on success.
 */
function nexora_ensure_upload_dirs(array $absolutePaths, int $mode = 0775): ?string
{
    foreach ($absolutePaths as $path) {
        $path = str_replace('\\', '/', $path);
        if (!nexora_ensure_upload_dir($path, $mode)) {
            if (is_dir($path)) {
                return 'Upload folder exists but is not writable by PHP: ' . $path;
            }
            return 'Upload folder could not be created: ' . $path;
        }
    }
    return null;
}

/**
 * Create upload folder structure on first run (not stored in git — server-only data).
 */
function nexora_bootstrap_upload_dirs(string $root): void
{
    $base = nexora_uploads_absolute_dir($root);
    $dirs = [
        $base,
        $base . '/images',
        $base . '/pdfs',
        $base . '/digital-featured',
        $base . '/digital-gallery',
        $base . '/printing-samples',
        $base . '/agro',
        $base . '/agro/items',
    ];

    foreach ($dirs as $dir) {
        nexora_ensure_upload_dir($dir);
    }

    $pdfHtaccess = $base . '/pdfs/.htaccess';
    if (!is_file($pdfHtaccess)) {
        $rules = <<<'HTACCESS'
# PDFs are served only via pages/pdf-view.php (inline preview). Prevents direct URL downloads from this folder.
<IfModule mod_authz_core.c>
    Require all denied
</IfModule>
<IfModule !mod_authz_core.c>
    Order Deny,Allow
    Deny from all
</IfModule>
HTACCESS;
        @file_put_contents($pdfHtaccess, $rules);
    }
}

/**
 * Save and optionally downscale an uploaded image (helps large phone camera photos).
 */
function nexora_save_uploaded_image(string $tmpPath, string $destPath, string $ext, int $maxWidth = 2000, int $jpegQuality = 85): bool
{
    if (!is_uploaded_file($tmpPath) && !is_file($tmpPath)) {
        return false;
    }

    $ext = strtolower($ext);
    if (!function_exists('imagecreatetruecolor')) {
        return move_uploaded_file($tmpPath, $destPath);
    }

    $source = nexora_image_create_from_file($tmpPath, $ext);
    if ($source === null) {
        return move_uploaded_file($tmpPath, $destPath);
    }

    $width = imagesx($source);
    $height = imagesy($source);
    if ($width <= 0 || $height <= 0) {
        imagedestroy($source);
        return move_uploaded_file($tmpPath, $destPath);
    }

    $targetWidth = $width;
    $targetHeight = $height;
    if ($width > $maxWidth) {
        $targetWidth = $maxWidth;
        $targetHeight = (int) round($height * ($maxWidth / $width));
    }

    $canvas = imagecreatetruecolor($targetWidth, $targetHeight);
    if ($canvas === false) {
        imagedestroy($source);
        return move_uploaded_file($tmpPath, $destPath);
    }

    if (in_array($ext, ['png', 'webp'], true)) {
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
    }

    imagecopyresampled($canvas, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
    imagedestroy($source);

    $saved = nexora_image_save_to_file($canvas, $destPath, $ext, $jpegQuality);
    imagedestroy($canvas);

    if ($saved) {
        @unlink($tmpPath);
        return true;
    }

    return move_uploaded_file($tmpPath, $destPath);
}

/**
 * @return resource|null
 */
function nexora_image_create_from_file(string $path, string $ext)
{
    switch ($ext) {
        case 'jpg':
        case 'jpeg':
            return function_exists('imagecreatefromjpeg') ? @imagecreatefromjpeg($path) : null;
        case 'png':
            return function_exists('imagecreatefrompng') ? @imagecreatefrompng($path) : null;
        case 'webp':
            return function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : null;
        default:
            return null;
    }
}

/**
 * @param resource $image
 */
function nexora_image_save_to_file($image, string $path, string $ext, int $jpegQuality): bool
{
    switch ($ext) {
        case 'jpg':
        case 'jpeg':
            return function_exists('imagejpeg') ? @imagejpeg($image, $path, $jpegQuality) : false;
        case 'png':
            return function_exists('imagepng') ? @imagepng($image, $path, 6) : false;
        case 'webp':
            return function_exists('imagewebp') ? @imagewebp($image, $path, $jpegQuality) : false;
        default:
            return false;
    }
}

/**
 * Max upload size in bytes from php.ini (best effort).
 */
function nexora_upload_max_bytes(): int
{
    return nexora_ini_size_to_bytes(ini_get('upload_max_filesize') ?: '2M');
}

function nexora_ini_size_to_bytes(string $value): int
{
    $value = trim($value);
    if ($value === '') {
        return 2 * 1024 * 1024;
    }
    $unit = strtolower(substr($value, -1));
    $number = (float) $value;
    switch ($unit) {
        case 'g':
            return (int) ($number * 1024 * 1024 * 1024);
        case 'm':
            return (int) ($number * 1024 * 1024);
        case 'k':
            return (int) ($number * 1024);
        default:
            return (int) $number;
    }
}
