<?php
/**
 * Stream a print document PDF for inline preview (printing page modal).
 * File must live under assets/uploads/pdfs/.
 */
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/database.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Not found.';
    exit;
}

$pdo = nexora_db_connect();
if (!$pdo) {
    http_response_code(503);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Unavailable.';
    exit;
}

try {
    nexora_print_documents_ensure_table($pdo);
    $stmt = $pdo->prepare('SELECT pdf_path FROM print_documents WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    http_response_code(500);
    exit;
}

if (!$row || empty($row['pdf_path'])) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Not found.';
    exit;
}

$root = dirname(__DIR__);
$rel = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim((string) $row['pdf_path'], '/\\'));
$abs = realpath($root . DIRECTORY_SEPARATOR . $rel);
$pdfsDir = realpath($root . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'pdfs');

if ($abs === false || $pdfsDir === false || strpos($abs, $pdfsDir) !== 0 || !is_file($abs)) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Not found.';
    exit;
}

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="preview.pdf"');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, max-age=300');
header('X-Robots-Tag: noindex, nofollow');

readfile($abs);
exit;
