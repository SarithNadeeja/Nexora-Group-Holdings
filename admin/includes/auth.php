<?php
/**
 * Admin auth helpers.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isAdminAuthenticated()
{
    return !empty($_SESSION['is_admin']) && $_SESSION['is_admin'] === true
        && !empty($_SESSION['admin_user_id']);
}

function nexora_admin_session_user_id(): int
{
    return isset($_SESSION['admin_user_id']) ? (int) $_SESSION['admin_user_id'] : 0;
}

function nexora_admin_session_username(): string
{
    return isset($_SESSION['admin_username']) ? (string) $_SESSION['admin_username'] : '';
}

function requireAdminAuth()
{
    if (!isAdminAuthenticated()) {
        header('Location: login.php');
        exit;
    }
}

