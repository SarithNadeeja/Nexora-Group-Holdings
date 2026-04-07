<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/database.php';

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Method not allowed.']);
    exit;
}

$input = json_decode(file_get_contents('php://input') ?: '', true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Invalid request payload.']);
    exit;
}

$productId = isset($input['productId']) ? (int) $input['productId'] : 0;
$productPrice = isset($input['productPrice']) ? (float) $input['productPrice'] : -1;
$customerName = trim((string) ($input['customerName'] ?? ''));
$customerPhone = trim((string) ($input['customerPhone'] ?? ''));
$customerEmail = trim((string) ($input['customerEmail'] ?? ''));
$address1 = trim((string) ($input['addressLine1'] ?? ''));
$address2 = trim((string) ($input['addressLine2'] ?? ''));
$city = trim((string) ($input['city'] ?? ''));
$province = trim((string) ($input['province'] ?? ''));

if (
    $productId <= 0 ||
    $productPrice < 0 ||
    $customerName === '' ||
    $customerPhone === '' ||
    $customerEmail === '' ||
    $address1 === '' ||
    $city === '' ||
    $province === ''
) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Please fill all required fields.']);
    exit;
}

if (!filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Please enter a valid email address.']);
    exit;
}

$pdo = nexora_db_connect();
if (!$pdo) {
    http_response_code(503);
    echo json_encode(['ok' => false, 'message' => 'Database unavailable. Please try again later.']);
    exit;
}

try {
    nexora_agro_orders_ensure_table($pdo);

    $ins = $pdo->prepare(
        'INSERT INTO agro_orders
            (product_id, product_price, customer_name, customer_phone, customer_email, address_line1, address_line2, city, province, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?) RETURNING id'
    );
    $ins->execute([
        $productId,
        $productPrice,
        $customerName,
        $customerPhone,
        $customerEmail,
        $address1,
        $address2 === '' ? null : $address2,
        $city,
        $province,
        'new',
    ]);
    $row = $ins->fetch(PDO::FETCH_ASSOC);
    $orderId = isset($row['id']) ? (int) $row['id'] : 0;

    $digits = nexora_whatsapp_agro_order_digits();
    if ($digits === '') {
        http_response_code(422);
        echo json_encode(['ok' => false, 'message' => 'WhatsApp number is not configured.']);
        exit;
    }

    $lines = [
        '*Nexora Agro - Order request*',
        '',
        '*Order*',
        'Order ID: #' . $orderId,
        'Product ID: #' . $productId,
        'Price: LKR ' . number_format($productPrice, 2, '.', ''),
        '',
        '*Customer*',
        'Name: ' . $customerName,
        'Phone: ' . $customerPhone,
        'Email: ' . $customerEmail,
        'Address line 1: ' . $address1,
        'Address line 2: ' . ($address2 !== '' ? $address2 : '-'),
        'City: ' . $city,
        'Province: ' . $province,
        '',
        '_Message generated from Nexora website_',
    ];

    $waUrl = 'https://wa.me/' . preg_replace('/\D+/', '', $digits) . '?text=' . rawurlencode(implode("\n", $lines));
    echo json_encode(['ok' => true, 'waUrl' => $waUrl, 'orderId' => $orderId]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Could not submit order right now.']);
}
