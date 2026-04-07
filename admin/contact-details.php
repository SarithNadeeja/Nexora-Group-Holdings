<?php
require_once __DIR__ . '/includes/auth.php';
requireAdminAuth();
require_once __DIR__ . '/includes/db.php';

$adminPageTitle = 'Contact Details by Division';
$success = '';
$error = '';

$divisions = [
    'digital' => 'Nexora Digital',
    'agro' => 'Nexora Agro',
    'printing' => 'Nexora Printing',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_division_contacts'])) {
    $ok = true;
    $updates = [];

    foreach (array_keys($divisions) as $key) {
        $phone = isset($_POST['phone_' . $key]) ? trim((string) $_POST['phone_' . $key]) : '';
        $email = isset($_POST['email_' . $key]) ? trim((string) $_POST['email_' . $key]) : '';

        if (mb_strlen($phone) > 80) {
            $error = 'Phone for ' . $divisions[$key] . ' is too long (max 80 characters).';
            $ok = false;
            break;
        }
        if (mb_strlen($email) > 180) {
            $error = 'Email for ' . $divisions[$key] . ' is too long (max 180 characters).';
            $ok = false;
            break;
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email for ' . $divisions[$key] . '.';
            $ok = false;
            break;
        }
        $updates[$key] = ['phone' => $phone, 'email' => $email];
    }

    if ($ok) {
        $stmt = $pdo->prepare(
            'INSERT INTO division_contact_settings (division, phone, email, updated_at)
             VALUES (?, ?, ?, CURRENT_TIMESTAMP)
             ON CONFLICT (division) DO UPDATE SET
                phone = EXCLUDED.phone,
                email = EXCLUDED.email,
                updated_at = CURRENT_TIMESTAMP'
        );
        foreach ($updates as $div => $data) {
            $stmt->execute([$div, $data['phone'], $data['email']]);
        }
        $success = 'Contact details saved. They appear on the Contact page and in the site footer.';
    }
}

$rows = [];
try {
    $rows = $pdo->query('SELECT division, phone, email FROM division_contact_settings ORDER BY division')->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $rows = [];
}
$byDiv = [];
foreach ($rows as $r) {
    $byDiv[$r['division']] = $r;
}

include __DIR__ . '/includes/header.php';
?>
<div class="admin-layout">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    <main class="admin-main">
        <div class="admin-heading">
            <h1>Contact Details by Division</h1>
            <p>Set phone and email for Digital, Agro, and Printing. These values are shown on the public Contact page and in the footer.</p>
        </div>

        <section class="admin-card">
            <?php if ($success !== ''): ?>
                <div class="alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <?php if ($error !== ''): ?>
                <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form class="admin-form" method="post">
                <?php foreach ($divisions as $key => $label): ?>
                    <?php
                    $row = $byDiv[$key] ?? null;
                    $phoneVal = $row ? (string) $row['phone'] : '';
                    $emailVal = $row ? (string) $row['email'] : '';
                    ?>
                    <fieldset style="border:1px solid var(--border);border-radius:12px;padding:16px;margin-bottom:16px;">
                        <legend style="font-weight:700;padding:0 8px;"><?php echo htmlspecialchars($label); ?></legend>

                        <label for="phone_<?php echo htmlspecialchars($key); ?>">Phone</label>
                        <input type="text" id="phone_<?php echo htmlspecialchars($key); ?>" name="phone_<?php echo htmlspecialchars($key); ?>" maxlength="80" value="<?php echo htmlspecialchars($phoneVal); ?>" placeholder="+94 77 123 4567">

                        <label for="email_<?php echo htmlspecialchars($key); ?>">Email</label>
                        <input type="email" id="email_<?php echo htmlspecialchars($key); ?>" name="email_<?php echo htmlspecialchars($key); ?>" maxlength="180" value="<?php echo htmlspecialchars($emailVal); ?>" placeholder="name@example.com">
                    </fieldset>
                <?php endforeach; ?>

                <button type="submit" name="save_division_contacts" value="1" class="btn-primary">Save all contact details</button>
            </form>
        </section>
    </main>
</div>
</body>
</html>
