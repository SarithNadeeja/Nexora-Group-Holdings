<?php
require_once __DIR__ . '/includes/auth.php';
requireAdminAuth();
require_once __DIR__ . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/order-status-mailer.php';

$adminPageTitle = 'Agro Orders';
$success = '';
$error = '';

$statusOptions = [
    'processing' => 'Order processing',
    'shipped' => 'Shipped',
    'delivered' => 'Delivered',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order_status'])) {
    $orderId = isset($_POST['order_id']) ? (int) $_POST['order_id'] : 0;
    $newStatus = isset($_POST['status']) ? (string) $_POST['status'] : '';

    if ($orderId <= 0) {
        $error = 'Invalid order.';
    } elseif (!isset($statusOptions[$newStatus])) {
        $error = 'Invalid status.';
    } else {
        try {
            $st = $pdo->prepare(
                'SELECT id, product_id, customer_name, customer_email, status
                 FROM agro_orders WHERE id = ?'
            );
            $st->execute([$orderId]);
            $order = $st->fetch(PDO::FETCH_ASSOC);

            if (!$order) {
                $error = 'Order not found.';
            } elseif ((string) $order['status'] === $newStatus) {
                $success = 'Order already marked as ' . $statusOptions[$newStatus] . '.';
            } else {
                $upd = $pdo->prepare('UPDATE agro_orders SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
                $upd->execute([$newStatus, $orderId]);

                $sent = nexora_send_agro_order_status_email(
                    (string) $order['customer_email'],
                    (string) $order['customer_name'],
                    (int) $order['id'],
                    (int) $order['product_id'],
                    $newStatus
                );

                $success = 'Order status updated to ' . $statusOptions[$newStatus] . '.';
                if (!$sent) {
                    $success .= ' Email notification was not sent (check mail setup).';
                }
            }
        } catch (Throwable $e) {
            $error = 'Could not update order status.';
        }
    }
}

$orders = [];
try {
    $orders = $pdo->query(
        'SELECT id, product_id, product_price, customer_name, customer_phone, customer_email,
                address_line1, address_line2, city, province, status, created_at, updated_at
         FROM agro_orders
         ORDER BY id DESC'
    )->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $orders = [];
}

include __DIR__ . '/includes/header.php';
?>
<div class="admin-layout">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    <main class="admin-main">
        <div class="admin-heading">
            <h1>Agro Orders</h1>
            <p>Review customer orders submitted from the Agro product page and update delivery stages.</p>
        </div>

        <?php if ($success !== ''): ?>
            <div class="alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <?php if ($error !== ''): ?>
            <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <section class="admin-card">
            <?php if (count($orders) === 0): ?>
                <p style="color:var(--muted);">No agro orders yet.</p>
            <?php else: ?>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Product</th>
                                <th>Customer details</th>
                                <th>Address</th>
                                <th>Status</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $o): ?>
                                <?php
                                $status = (string) $o['status'];
                                $statusLabel = $status === 'new'
                                    ? 'New'
                                    : ($statusOptions[$status] ?? ucfirst($status));
                                ?>
                                <tr>
                                    <td>
                                        <strong>#<?php echo (int) $o['id']; ?></strong><br>
                                        <span style="color:var(--muted);font-size:0.85rem;">Updated: <?php echo htmlspecialchars((string) $o['updated_at']); ?></span>
                                    </td>
                                    <td>
                                        ID: #<?php echo (int) $o['product_id']; ?><br>
                                        Price: LKR <?php echo htmlspecialchars(number_format((float) $o['product_price'], 2)); ?>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars((string) $o['customer_name']); ?></strong><br>
                                        <?php echo htmlspecialchars((string) $o['customer_phone']); ?><br>
                                        <?php echo htmlspecialchars((string) $o['customer_email']); ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars((string) $o['address_line1']); ?><br>
                                        <?php if (!empty($o['address_line2'])): ?>
                                            <?php echo htmlspecialchars((string) $o['address_line2']); ?><br>
                                        <?php endif; ?>
                                        <?php echo htmlspecialchars((string) $o['city']); ?>, <?php echo htmlspecialchars((string) $o['province']); ?>
                                    </td>
                                    <td>
                                        <div style="margin-bottom:8px;font-weight:600;"><?php echo htmlspecialchars($statusLabel); ?></div>
                                        <form method="post" class="admin-form admin-form-compact">
                                            <input type="hidden" name="order_id" value="<?php echo (int) $o['id']; ?>">
                                            <select name="status" required style="width:100%;padding:10px 11px;border-radius:10px;border:1px solid var(--border);">
                                                <option value="processing" <?php echo $status === 'processing' ? 'selected' : ''; ?>>Order processing</option>
                                                <option value="shipped" <?php echo $status === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                                <option value="delivered" <?php echo $status === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                            </select>
                                            <button type="submit" name="update_order_status" value="1" class="btn-primary" style="padding:8px 12px;">Update</button>
                                        </form>
                                    </td>
                                    <td><?php echo htmlspecialchars((string) $o['created_at']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </main>
</div>
</body>
</html>
