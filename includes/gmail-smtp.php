<?php
/**
 * Send email via Gmail SMTP (App Password).
 */
require_once __DIR__ . '/config.php';

/**
 * @return resource|null
 */
function nexora_gmail_smtp_connect(string $host, int $port, bool $ssl = false)
{
    $target = ($ssl ? 'ssl://' : 'tcp://') . $host . ':' . $port;
    $errno = 0;
    $errstr = '';
    $socket = @stream_socket_client($target, $errno, $errstr, 30);
    return $socket !== false ? $socket : null;
}

/**
 * @param resource $socket
 */
function nexora_gmail_smtp_expect($socket, array $okCodes): bool
{
    $response = '';
    while ($line = fgets($socket, 515)) {
        $response .= $line;
        if (isset($line[3]) && $line[3] === ' ') {
            break;
        }
    }
    $code = (int) substr($response, 0, 3);
    return in_array($code, $okCodes, true);
}

/**
 * @param resource $socket
 */
function nexora_gmail_smtp_cmd($socket, string $command, array $okCodes): bool
{
    fwrite($socket, $command . "\r\n");
    return nexora_gmail_smtp_expect($socket, $okCodes);
}

function nexora_gmail_send(string $toEmail, string $subject, string $bodyPlain, string $fromName = 'Nexora'): bool
{
    $toEmail = trim($toEmail);
    $from = trim((string) NEXORA_GMAIL_FROM);
    $password = preg_replace('/\s+/', '', trim((string) NEXORA_GMAIL_APP_PASSWORD));

    if ($toEmail === '' || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    if ($from === '' || !filter_var($from, FILTER_VALIDATE_EMAIL) || $password === '') {
        return false;
    }

    $socket = nexora_gmail_smtp_connect('smtp.gmail.com', 587);
    if (!$socket) {
        return false;
    }

    stream_set_timeout($socket, 30);

    if (!nexora_gmail_smtp_expect($socket, [220])) {
        fclose($socket);
        return false;
    }
    if (!nexora_gmail_smtp_cmd($socket, 'EHLO ' . ($_SERVER['SERVER_NAME'] ?? 'localhost'), [250])) {
        fclose($socket);
        return false;
    }
    if (!nexora_gmail_smtp_cmd($socket, 'STARTTLS', [220])) {
        fclose($socket);
        return false;
    }
    if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
        fclose($socket);
        return false;
    }
    if (!nexora_gmail_smtp_cmd($socket, 'EHLO ' . ($_SERVER['SERVER_NAME'] ?? 'localhost'), [250])) {
        fclose($socket);
        return false;
    }
    if (!nexora_gmail_smtp_cmd($socket, 'AUTH LOGIN', [334])) {
        fclose($socket);
        return false;
    }
    if (!nexora_gmail_smtp_cmd($socket, base64_encode($from), [334])) {
        fclose($socket);
        return false;
    }
    if (!nexora_gmail_smtp_cmd($socket, base64_encode($password), [235])) {
        fclose($socket);
        return false;
    }
    if (!nexora_gmail_smtp_cmd($socket, 'MAIL FROM:<' . $from . '>', [250])) {
        fclose($socket);
        return false;
    }
    if (!nexora_gmail_smtp_cmd($socket, 'RCPT TO:<' . $toEmail . '>', [250, 251])) {
        fclose($socket);
        return false;
    }
    if (!nexora_gmail_smtp_cmd($socket, 'DATA', [354])) {
        fclose($socket);
        return false;
    }

    $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $headers = [
        'From: ' . $fromName . ' <' . $from . '>',
        'To: ' . $toEmail,
        'Subject: ' . $encodedSubject,
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'Content-Transfer-Encoding: 8bit',
        'Date: ' . date('r'),
    ];

    $message = implode("\r\n", $headers) . "\r\n\r\n" . str_replace(["\r\n", "\r", "\n"], "\r\n", $bodyPlain);
    $message = preg_replace('/^\./m', '..', $message);

    fwrite($socket, $message . "\r\n.\r\n");
    $sent = nexora_gmail_smtp_expect($socket, [250]);
    nexora_gmail_smtp_cmd($socket, 'QUIT', [221]);
    fclose($socket);

    return $sent;
}
