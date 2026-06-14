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
// Empty string = site at document root. Do NOT use '/' here: templates use
// BASE_URL . '/assets/...' which would become '//assets/...' (protocol-relative
// URL to host "assets") and cause net::ERR_NAME_NOT_RESOLVED in production.
define('BASE_URL', $normalizedBaseUrl);

/**
 * Build a root-relative path for links and static assets (always starts with /).
 */
function nexora_url(string $path = ''): string
{
    $path = ltrim(str_replace('\\', '/', $path), '/');
    $base = BASE_URL;
    if ($base === '') {
        return $path === '' ? '/' : '/' . $path;
    }
    return $path === '' ? $base : rtrim($base, '/') . '/' . $path;
}

/**
 * Build an absolute URL for the current site (for sharing / WhatsApp messages).
 * Uses the current request host; ensure DNS and HTTPS match production for correct links.
 */
function nexora_site_absolute_url(string $path = ''): string
{
    $path = ltrim(str_replace('\\', '/', $path), '/');
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = isset($_SERVER['HTTP_HOST']) ? (string) $_SERVER['HTTP_HOST'] : 'localhost';
    $base = BASE_URL;
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
 * Gmail SMTP for order status emails.
 * Credentials: includes/gmail.local.php (local) or env NEXORA_GMAIL_FROM / NEXORA_GMAIL_APP_PASSWORD.
 */
$gmailLocalFile = __DIR__ . '/gmail.local.php';
if (is_file($gmailLocalFile)) {
    require_once $gmailLocalFile;
}
if (!defined('NEXORA_GMAIL_FROM')) {
    $gmailFrom = getenv('NEXORA_GMAIL_FROM');
    define('NEXORA_GMAIL_FROM', $gmailFrom !== false ? trim($gmailFrom) : '');
}
if (!defined('NEXORA_GMAIL_APP_PASSWORD')) {
    $gmailPass = getenv('NEXORA_GMAIL_APP_PASSWORD');
    define('NEXORA_GMAIL_APP_PASSWORD', $gmailPass !== false ? trim($gmailPass) : '');
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

/**
 * Digits for general "Contact Us" WhatsApp (env first, then division phone, then any division).
 */
function nexora_whatsapp_contact_digits(?string $division = null): string
{
    $num = preg_replace('/\D+/', '', (string) NEXORA_WHATSAPP_ORDER_NUMBER);
    if ($num !== '') {
        return $num;
    }
    require_once __DIR__ . '/division_contacts.php';
    $contacts = nexora_division_contacts_all();
    if ($division !== null && isset($contacts[$division])) {
        $digits = preg_replace('/\D+/', '', (string) ($contacts[$division]['phone'] ?? ''));
        if ($digits !== '') {
            return $digits;
        }
    }
    foreach (['digital', 'agro', 'printing'] as $div) {
        $digits = preg_replace('/\D+/', '', (string) ($contacts[$div]['phone'] ?? ''));
        if ($digits !== '') {
            return $digits;
        }
    }
    return '';
}

/**
 * @return string|null wa.me URL for Contact Us buttons, or null if not configured
 */
function nexora_whatsapp_contact_url(?string $division = null, ?string $message = null): ?string
{
    $num = nexora_whatsapp_contact_digits($division);
    if ($num === '') {
        return null;
    }
    if ($message === null) {
        $labels = [
            'digital' => 'Nexora Digital',
            'agro' => 'Nexora Agro',
            'printing' => 'Nexora Printing',
        ];
        $name = ($division !== null && isset($labels[$division])) ? $labels[$division] : 'Nexora Group Holdings';
        $message = "Hello {$name},\n\nI'd like to get in touch.\n\nThank you.";
    }
    return 'https://wa.me/' . $num . '?text=' . rawurlencode($message);
}

/**
 * href for Contact Us CTAs: WhatsApp when configured, otherwise contact page.
 */
function nexora_contact_href(?string $division = null, ?string $message = null): string
{
    $wa = nexora_whatsapp_contact_url($division, $message);
    return $wa ?? nexora_url('contact.php');
}

/**
 * Public social profile URLs (override with NEXORA_FACEBOOK_URL / NEXORA_TIKTOK_URL env vars).
 *
 * @return array<int, array{label: string, url: string, icon: string, aria: string}>
 */
function nexora_social_links(): array
{
    $facebook = getenv('NEXORA_FACEBOOK_URL');
    if ($facebook === false || trim($facebook) === '') {
        $facebook = 'https://www.facebook.com/share/1Gf2BfwVUZ/?mibextid=wwXIfr';
    }
    $tiktok = getenv('NEXORA_TIKTOK_URL');
    if ($tiktok === false || trim($tiktok) === '') {
        $tiktok = 'https://www.tiktok.com/@nexora.group.hold';
    }

    $links = [];
    $facebook = trim($facebook);
    $tiktok = trim($tiktok);

    if ($facebook !== '') {
        $links[] = [
            'label' => 'Facebook',
            'url' => $facebook,
            'icon' => 'f',
            'aria' => 'Nexora Group Holdings on Facebook',
        ];
    }
    if ($tiktok !== '') {
        $links[] = [
            'label' => 'TikTok',
            'url' => $tiktok,
            'icon' => 'tt',
            'aria' => 'Nexora Group Holdings on TikTok',
        ];
    }

    return $links;
}
