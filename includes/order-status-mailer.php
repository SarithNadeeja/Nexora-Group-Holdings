<?php
require_once __DIR__ . '/config.php';

/**
 * Send agro order status email notification.
 * Uses PHP mail() with Gmail-from placeholder values until SMTP is configured.
 */
function nexora_send_agro_order_status_email(string $toEmail, string $customerName, int $orderId, int $productId, string $status): bool
{
    $toEmail = trim($toEmail);
    if ($toEmail === '' || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $subject = 'Nexora Agro Order Update - Order #' . $orderId;
    $bodyLines = [
        'Hello ' . $customerName . ',',
        '',
        'Your Nexora Agro order status has been updated.',
        'Order ID: #' . $orderId,
        'Product ID: #' . $productId,
        'Current status: ' . strtoupper($status),
        '',
        'Thank you,',
        'Nexora Agro Team',
    ];
    $body = implode("\r\n", $bodyLines);

    $from = trim((string) NEXORA_GMAIL_FROM);
    if ($from === '' || !filter_var($from, FILTER_VALIDATE_EMAIL)) {
        $from = 'no-reply@nexora.local';
    }

    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'From: Nexora Agro <' . $from . '>',
        'Reply-To: ' . $from,
    ];

    return @mail($toEmail, $subject, $body, implode("\r\n", $headers));
}

/**
 * Send printing order status email notification.
 */
function nexora_send_printing_order_status_email(string $toEmail, string $customerName, int $orderId, int $documentId, string $status): bool
{
    $toEmail = trim($toEmail);
    if ($toEmail === '' || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $subject = 'Nexora Printing Order Update - Order #' . $orderId;
    $bodyLines = [
        'Hello ' . $customerName . ',',
        '',
        'Your Nexora Printing order status has been updated.',
        'Order ID: #' . $orderId,
        'Document ID: #' . $documentId,
        'Current status: ' . strtoupper($status),
        '',
        'Thank you,',
        'Nexora Printing Team',
    ];
    $body = implode("\r\n", $bodyLines);

    $from = trim((string) NEXORA_GMAIL_FROM);
    if ($from === '' || !filter_var($from, FILTER_VALIDATE_EMAIL)) {
        $from = 'no-reply@nexora.local';
    }

    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'From: Nexora Printing <' . $from . '>',
        'Reply-To: ' . $from,
    ];

    return @mail($toEmail, $subject, $body, implode("\r\n", $headers));
}

/**
 * Send custom printing order status email notification.
 */
function nexora_send_printing_custom_order_status_email(string $toEmail, string $customerName, int $orderId, string $status): bool
{
    $toEmail = trim($toEmail);
    if ($toEmail === '' || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    $subject = 'Nexora Printing Custom Order Update - Order #' . $orderId;
    $body = implode("\r\n", [
        'Hello ' . $customerName . ',',
        '',
        'Your Nexora Printing custom printout order status has been updated.',
        'Order ID: #' . $orderId,
        'Current status: ' . strtoupper($status),
        '',
        'Thank you,',
        'Nexora Printing Team',
    ]);
    $from = trim((string) NEXORA_GMAIL_FROM);
    if ($from === '' || !filter_var($from, FILTER_VALIDATE_EMAIL)) {
        $from = 'no-reply@nexora.local';
    }
    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'From: Nexora Printing <' . $from . '>',
        'Reply-To: ' . $from,
    ];
    return @mail($toEmail, $subject, $body, implode("\r\n", $headers));
}
