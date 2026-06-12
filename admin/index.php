<?php
/**
 * Default entry for /admin/ — send visitors to login or dashboard.
 */
require_once __DIR__ . '/includes/auth.php';

if (isAdminAuthenticated()) {
    header('Location: dashboard.php');
    exit;
}

header('Location: login.php');
exit;
