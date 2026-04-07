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

/**
 * Build an absolute URL for the current site (for sharing / WhatsApp messages).
 * Uses the current request host; ensure DNS and HTTPS match production for correct links.
 */
function nexora_site_absolute_url(string $path = ''): string
{
    $path = ltrim(str_replace('\\', '/', $path), '/');
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = isset($_SERVER['HTTP_HOST']) ? (string) $_SERVER['HTTP_HOST'] : 'localhost';
    $base = BASE_URL === '/' ? '' : BASE_URL;
    $prefix = rtrim($scheme . '://' . $host . $base, '/');
    return $path === '' ? $prefix : $prefix . '/' . $path;
}

/**
 * WhatsApp ordering for Agro (digits only, no + or spaces). Example: 94771234567
 * Also set env NEXORA_WHATSAPP_ORDER_NUMBER. If empty, Agro division phone from admin Contact Details is used.
 */
if (!defined('NEXORA_WHATSAPP_ORDER_NUMBER')) {
    $waEnv = getenv('NEXORA_WHATSAPP_ORDER_NUMBER');
    define('NEXORA_WHATSAPP_ORDER_NUMBER', $waEnv !== false ? trim($waEnv) : '');
}

/**
 * Gmail settings for order status updates (sample placeholders).
 * Replace with real values later, or set environment variables:
 * NEXORA_GMAIL_FROM, NEXORA_GMAIL_APP_PASSWORD
 */
if (!defined('NEXORA_GMAIL_FROM')) {
    $gmailFrom = getenv('NEXORA_GMAIL_FROM');
    define('NEXORA_GMAIL_FROM', $gmailFrom !== false ? trim($gmailFrom) : 'your-gmail@gmail.com');
}
if (!defined('NEXORA_GMAIL_APP_PASSWORD')) {
    $gmailPass = getenv('NEXORA_GMAIL_APP_PASSWORD');
    define('NEXORA_GMAIL_APP_PASSWORD', $gmailPass !== false ? trim($gmailPass) : 'sample-app-password-1234');
}

/**
 * Digits only for wa.me/{digits} — config first, then Nexora Agro division phone.
 */
function nexora_whatsapp_agro_order_digits(): string
{
    $num = preg_replace('/\D+/', '', (string) NEXORA_WHATSAPP_ORDER_NUMBER);
    if ($num !== '') {
        return $num;
    }
    require_once __DIR__ . '/division_contacts.php';
    $contacts = nexora_division_contacts_all();
    return preg_replace('/\D+/', '', (string) ($contacts['agro']['phone'] ?? ''));
}

/**
 * Digits only for printing order WhatsApp destination.
 * Uses env value first, then Printing division phone from admin Contact Details.
 */
function nexora_whatsapp_printing_order_digits(): string
{
    $num = preg_replace('/\D+/', '', (string) NEXORA_WHATSAPP_ORDER_NUMBER);
    if ($num !== '') {
        return $num;
    }
    require_once __DIR__ . '/division_contacts.php';
    $contacts = nexora_division_contacts_all();
    return preg_replace('/\D+/', '', (string) ($contacts['printing']['phone'] ?? ''));
}

/**
 * @return string|null wa.me URL or null if WhatsApp number is not configured
 */
function nexora_whatsapp_agro_order_url(string $productName, int $productId): ?string
{
    $num = nexora_whatsapp_agro_order_digits();
    if ($num === '') {
        return null;
    }
    $msg = "Hello Nexora Agro,\n\nI'd like to order or ask about:\n" . $productName . "\n(Ref: product #" . $productId . ")\n\nThank you.";
    return 'https://wa.me/' . $num . '?text=' . rawurlencode($msg);
}
