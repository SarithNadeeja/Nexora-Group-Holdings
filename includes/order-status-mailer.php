<?php
require_once __DIR__ . '/gmail-smtp.php';

/**
 * Send agro order status email notification via Gmail SMTP.
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

    return nexora_gmail_send($toEmail, $subject, implode("\n", $bodyLines), 'Nexora Agro');
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

    return nexora_gmail_send($toEmail, $subject, implode("\n", $bodyLines), 'Nexora Printing');
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
    $body = implode("\n", [
        'Hello ' . $customerName . ',',
        '',
        'Your Nexora Printing custom printout order status has been updated.',
        'Order ID: #' . $orderId,
        'Current status: ' . strtoupper($status),
        '',
        'Thank you,',
        'Nexora Printing Team',
    ]);

    return nexora_gmail_send($toEmail, $subject, $body, 'Nexora Printing');
}
