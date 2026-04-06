<?php
/**
 * Global configuration for Nexora Group Holdings starter site.
 * Keep this lightweight for cPanel compatibility.
 */

define('SITE_NAME', 'Nexora Group Holdings');

/**
 * Auto-detect project base URL from DOCUMENT_ROOT for local/XAMPP/cPanel.
 * Example detected value: /nexora/nexora-website
 */
$projectRoot = str_replace('\\', '/', realpath(dirname(__DIR__)));
$documentRoot = isset($_SERVER['DOCUMENT_ROOT'])
    ? str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']))
    : '';

$baseUrl = '/nexora-website';
if (!empty($documentRoot) && strpos($projectRoot, $documentRoot) === 0) {
    $relativePath = substr($projectRoot, strlen($documentRoot));
    $relativePath = str_replace('//', '/', $relativePath);
    $baseUrl = $relativePath !== '' ? $relativePath : '/';
}

$normalizedBaseUrl = rtrim($baseUrl, '/');
if ($normalizedBaseUrl === '') {
    $normalizedBaseUrl = '/';
}

define('BASE_URL', $normalizedBaseUrl);

