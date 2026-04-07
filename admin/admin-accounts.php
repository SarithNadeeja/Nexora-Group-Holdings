<?php
require_once __DIR__ . '/includes/auth.php';
requireAdminAuth();
require_once __DIR__ . '/includes/db.php';

$adminPageTitle = 'Admin accounts';
$success = '';
$error = '';

$myId = nexora_admin_session_user_id();
$myUsername = nexora_admin_session_username();

/**
 * @return string|null Error message or null if valid.
 */
function nexora_admin_validate_username(string $u): ?string
{
    $len = mb_strlen($u);
    if ($len < 2 || $len > 64) {
        return 'Username must be between 2 and 64 characters.';
    }
    if (!preg_match('/^[a-zA-Z0-9_-]+$/', $u)) {
        return 'Username may only contain letters, numbers, underscores, and hyphens.';
    }

    return null;
}

/**
 * @return string|null Error message or null if valid.
 */
function nexora_admin_validate_new_password(string $p1, string $p2): ?string
{
    if (strlen($p1) < 8) {
        return 'New password must be at least 8 characters.';
    }
    if ($p1 !== $p2) {
        return 'New password and confirmation do not match.';
    }

    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['change_username'])) {
        $currentPassword = (string) ($_POST['current_password_u'] ?? '');
        $newUsername = isset($_POST['new_username']) ? trim((string) $_POST['new_username']) : '';

        $uErr = nexora_admin_validate_username($newUsername);
        if ($uErr !== null) {
            $error = $uErr;
        } elseif ($currentPassword === '') {
            $error = 'Enter your current password to change username.';
        } elseif (strcasecmp($newUsername, $myUsername) === 0) {
            $error = 'Choose a different username than your current one.';
        } else {
            try {
                $st = $pdo->prepare('SELECT password_hash FROM admin_users WHERE id = ?');
                $st->execute([$myId]);
                $row = $st->fetch(PDO::FETCH_ASSOC);
                if (!$row || !password_verify($currentPassword, (string) $row['password_hash'])) {
                    $error = 'Current password is incorrect.';
                } else {
                    $chk = $pdo->prepare('SELECT 1 FROM admin_users WHERE LOWER(username) = LOWER(?) AND id != ?');
                    $chk->execute([$newUsername, $myId]);
                    if ($chk->fetchColumn()) {
                        $error = 'That username is already taken.';
                    } else {
                        $pdo->prepare('UPDATE admin_users SET username = ? WHERE id = ?')->execute([$newUsername, $myId]);
                        $_SESSION['admin_username'] = $newUsername;
                        $myUsername = $newUsername;
                        $success = 'Your username has been updated.';
                    }
                }
            } catch (Throwable $e) {
                $error = 'Could not update username. Please try again.';
            }
        }
    } elseif (isset($_POST['change_password'])) {
        $currentPassword = (string) ($_POST['current_password_p'] ?? '');
        $newPassword = (string) ($_POST['new_password'] ?? '');
        $newPassword2 = (string) ($_POST['new_password_confirm'] ?? '');

        $pErr = nexora_admin_validate_new_password($newPassword, $newPassword2);
        if ($pErr !== null) {
            $error = $pErr;
        } elseif ($currentPassword === '') {
            $error = 'Enter your current password.';
        } else {
            try {
                $st = $pdo->prepare('SELECT password_hash FROM admin_users WHERE id = ?');
                $st->execute([$myId]);
                $row = $st->fetch(PDO::FETCH_ASSOC);
                if (!$row || !password_verify($currentPassword, (string) $row['password_hash'])) {
                    $error = 'Current password is incorrect.';
                } else {
                    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
                    $pdo->prepare('UPDATE admin_users SET password_hash = ? WHERE id = ?')->execute([$hash, $myId]);
                    $success = 'Your password has been updated.';
                }
            } catch (Throwable $e) {
                $error = 'Could not update password. Please try again.';
            }
        }
    } elseif (isset($_POST['add_admin'])) {
        $newUser = isset($_POST['add_username']) ? trim((string) $_POST['add_username']) : '';
        $newPass = (string) ($_POST['add_password'] ?? '');
        $newPass2 = (string) ($_POST['add_password_confirm'] ?? '');

        $uErr = nexora_admin_validate_username($newUser);
        if ($uErr !== null) {
            $error = $uErr;
        } else {
            $pErr = nexora_admin_validate_new_password($newPass, $newPass2);
            if ($pErr !== null) {
                $error = $pErr;
            } else {
                try {
                    $dup = $pdo->prepare('SELECT 1 FROM admin_users WHERE LOWER(username) = LOWER(?)');
                    $dup->execute([$newUser]);
                    if ($dup->fetchColumn()) {
                        $error = 'That username is already taken.';
                    } else {
                        $hash = password_hash($newPass, PASSWORD_DEFAULT);
                        $pdo->prepare('INSERT INTO admin_users (username, password_hash) VALUES (?, ?)')->execute([$newUser, $hash]);
                        $success = 'Administrator account created for "' . $newUser . '".';
                    }
                } catch (PDOException $e) {
                    $sqlState = $e->errorInfo[0] ?? '';
                    if ($sqlState === '23505') {
                        $error = 'That username is already taken.';
                    } else {
                        $error = 'Could not create administrator. Please try again.';
                    }
                }
            }
        }
    } elseif (isset($_POST['remove_admin'])) {
        $removeId = isset($_POST['remove_admin_id']) ? (int) $_POST['remove_admin_id'] : 0;
        if ($removeId <= 0) {
            $error = 'Invalid account.';
        } elseif ($removeId === $myId) {
            $error = 'You cannot remove your own account while logged in.';
        } else {
            try {
                $total = (int) $pdo->query('SELECT COUNT(*) FROM admin_users')->fetchColumn();
                if ($total <= 1) {
                    $error = 'Cannot remove the last administrator.';
                } else {
                    $del = $pdo->prepare('DELETE FROM admin_users WHERE id = ?');
                    $del->execute([$removeId]);
                    if ($del->rowCount() === 0) {
                        $error = 'That administrator was not found.';
                    } else {
                        $success = 'Administrator removed.';
                    }
                }
            } catch (Throwable $e) {
                $error = 'Could not remove administrator. Please try again.';
            }
        }
    }
}

$allAdmins = [];
try {
    $allAdmins = $pdo->query('SELECT id, username, created_at FROM admin_users ORDER BY id ASC')->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $allAdmins = [];
}

include __DIR__ . '/includes/header.php';
?>
<div class="admin-layout">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    <main class="admin-main">
        <div class="admin-heading">
            <h1>Admin accounts</h1>
            <p>Signed in as <strong><?php echo htmlspecialchars($myUsername); ?></strong>. Change your login, or manage other administrators.</p>
        </div>

        <?php if ($success !== ''): ?>
            <div class="alert-success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <?php if ($error !== ''): ?>
            <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <section class="admin-card" style="margin-bottom: 16px;">
            <h2 style="font-size:1.1rem;margin-bottom:12px;">Change username</h2>
            <p class="admin-form-hint" style="margin-bottom:10px;">Current username: <strong><?php echo htmlspecialchars($myUsername); ?></strong></p>
            <form class="admin-form" method="post" autocomplete="username">
                <label for="new_username">New username</label>
                <input type="text" id="new_username" name="new_username" required maxlength="64" pattern="[a-zA-Z0-9_-]+" placeholder="letters, numbers, _ or -">

                <label for="current_password_u">Current password</label>
                <input type="password" id="current_password_u" name="current_password_u" required autocomplete="current-password">

                <button type="submit" name="change_username" value="1" class="btn-primary">Update username</button>
            </form>
        </section>

        <section class="admin-card" style="margin-bottom: 16px;">
            <h2 style="font-size:1.1rem;margin-bottom:12px;">Change password</h2>
            <form class="admin-form" method="post" autocomplete="off">
                <label for="current_password_p">Current password</label>
                <input type="password" id="current_password_p" name="current_password_p" required autocomplete="current-password">

                <label for="new_password">New password</label>
                <input type="password" id="new_password" name="new_password" required minlength="8" autocomplete="new-password">
                <p class="admin-form-hint">At least 8 characters.</p>

                <label for="new_password_confirm">Confirm new password</label>
                <input type="password" id="new_password_confirm" name="new_password_confirm" required minlength="8" autocomplete="new-password">

                <button type="submit" name="change_password" value="1" class="btn-primary">Update password</button>
            </form>
        </section>

        <section class="admin-card" style="margin-bottom: 16px;">
            <h2 style="font-size:1.1rem;margin-bottom:12px;">Add administrator</h2>
            <form class="admin-form" method="post" autocomplete="off">
                <label for="add_username">Username</label>
                <input type="text" id="add_username" name="add_username" required maxlength="64" pattern="[a-zA-Z0-9_-]+">

                <label for="add_password">Password</label>
                <input type="password" id="add_password" name="add_password" required minlength="8" autocomplete="new-password">

                <label for="add_password_confirm">Confirm password</label>
                <input type="password" id="add_password_confirm" name="add_password_confirm" required minlength="8" autocomplete="new-password">

                <button type="submit" name="add_admin" value="1" class="btn-primary">Create administrator</button>
            </form>
        </section>

        <section class="admin-card">
            <h2 style="font-size:1.1rem;margin-bottom:4px;">Administrators</h2>
            <p class="admin-form-hint" style="margin-bottom:12px;">You cannot remove yourself or the only remaining admin.</p>
            <?php if (count($allAdmins) === 0): ?>
                <p style="color:var(--muted);">No accounts found.</p>
            <?php else: ?>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Created</th>
                                <th style="width:120px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allAdmins as $adm): ?>
                                <tr>
                                    <td>
                                        <?php echo htmlspecialchars((string) $adm['username']); ?>
                                        <?php if ((int) $adm['id'] === $myId): ?>
                                            <span style="color:var(--muted);font-size:0.85rem;"> (you)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars((string) $adm['created_at']); ?></td>
                                    <td>
                                        <?php if ((int) $adm['id'] !== $myId && count($allAdmins) > 1): ?>
                                            <form method="post" style="display:inline;" onsubmit="return confirm('Remove this administrator? They will no longer be able to sign in.');">
                                                <input type="hidden" name="remove_admin_id" value="<?php echo (int) $adm['id']; ?>">
                                                <button type="submit" name="remove_admin" value="1" class="btn-danger">Remove</button>
                                            </form>
                                        <?php else: ?>
                                            <span style="color:var(--muted);">—</span>
                                        <?php endif; ?>
                                    </td>
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
