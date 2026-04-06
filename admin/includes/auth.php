<?php
/**
 * Admin auth helpers.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isAdminAuthenticated()
{
    return !empty($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

function requireAdminAuth()
{
    if (!isAdminAuthenticated()) {
        header('Location: login.php');
        exit;
    }
}

