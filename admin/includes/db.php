<?php
/**
 * Admin DB: PostgreSQL via shared helper.
 */
require_once dirname(__DIR__, 2) . '/includes/database.php';

$pdo = nexora_db_connect();
if (!$pdo) {
    die('Database connection failed. Enable pdo_pgsql in PHP and check PostgreSQL host, database "nexora", user, and password (default password 1234, user postgres).');
}

nexora_print_documents_ensure_table($pdo);
nexora_digital_featured_images_ensure_table($pdo);
nexora_digital_client_comments_ensure_table($pdo);
nexora_agro_shop_items_ensure_table($pdo);
nexora_division_contacts_ensure_table($pdo);
nexora_admin_users_ensure_table($pdo);
nexora_agro_orders_ensure_table($pdo);
nexora_printing_orders_ensure_table($pdo);
nexora_printing_custom_orders_ensure_table($pdo);
