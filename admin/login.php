<?php
require_once __DIR__ . '/includes/auth.php';

if (isAdminAuthenticated()) {
    header('Location: dashboard.php');
    exit;
}

require_once dirname(__DIR__) . '/includes/database.php';

$error = '';
$pdoLogin = nexora_db_connect();

if ($pdoLogin) {
    nexora_admin_users_ensure_table($pdoLogin);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? (string) ($_POST['password'] ?? '') : '';

    if (!$pdoLogin) {
        $error = 'Cannot sign in: database is not available. Check PostgreSQL and PHP pdo_pgsql.';
    } elseif ($username === '' || $password === '') {
        $error = 'Please enter username and password.';
    } else {
        try {
            $st = $pdoLogin->prepare('SELECT id, username, password_hash FROM admin_users WHERE LOWER(username) = LOWER(?)');
            $st->execute([$username]);
            $row = $st->fetch(PDO::FETCH_ASSOC);
            if ($row && password_verify($password, (string) $row['password_hash'])) {
                $_SESSION['is_admin'] = true;
                $_SESSION['admin_user_id'] = (int) $row['id'];
                $_SESSION['admin_username'] = (string) $row['username'];
                header('Location: dashboard.php');
                exit;
            }
            $error = 'Invalid username or password.';
        } catch (Throwable $e) {
            $error = 'Sign-in failed. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Nexora</title>
    <style>
        body {
            margin: 0;
            font-family: "Segoe UI", Arial, sans-serif;
            background: #f5f7fa;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 16px;
        }
        .login-card {
            width: min(430px, 100%);
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
            padding: 24px;
        }
        h1 { margin: 0 0 8px; }
        p { margin: 0 0 18px; color: #6b7280; }
        form { display: grid; gap: 12px; }
        label { font-weight: 600; }
        input {
            width: 100%;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 11px 12px;
            font: inherit;
        }
        button {
            border: 0;
            border-radius: 10px;
            padding: 11px 14px;
            background: #2563eb;
            color: #fff;
            font-weight: 600;
            cursor: pointer;
        }
        .error {
            border: 1px solid #fecaca;
            background: #fef2f2;
            color: #991b1b;
            border-radius: 10px;
            padding: 10px 12px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <h1>Admin Login</h1>
        <p>Sign in to manage Nexora printing documents.</p>
        <?php if ($error !== ''): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>

