<?php
/**
 * PostgreSQL connection for Nexora (printing documents, admin).
 *
 * Defaults match local dev; override with environment variables on cPanel:
 * DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS
 *
 * Requires PHP extension: pdo_pgsql
 */
require_once __DIR__ . '/config.php';

/**
 * @return PDO|null Returns null if extension missing or connection fails.
 */
function nexora_db_connect(): ?PDO
{
    static $pdo = null;
    static $tried = false;

    if ($tried) {
        return $pdo;
    }
    $tried = true;

    if (!extension_loaded('pdo_pgsql')) {
        return null;
    }

    $dbHost = getenv('DB_HOST') ?: '127.0.0.1';
    $dbPort = (int) (getenv('DB_PORT') ?: 5432);
    $dbName = getenv('DB_NAME') ?: 'nexora';
    $dbUser = getenv('DB_USER') ?: 'postgres';
    $dbPass = getenv('DB_PASS') ?: '1234';

    $dsn = sprintf('pgsql:host=%s;port=%d;dbname=%s', $dbHost, $dbPort, $dbName);

    try {
        $pdo = new PDO($dsn, $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        $pdo = null;
        return null;
    }
}

/**
 * Create print_documents table if it does not exist (PostgreSQL).
 */
function nexora_print_documents_ensure_table(PDO $pdo): void
{
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS print_documents (
            id SERIAL PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            pages INTEGER NOT NULL DEFAULT 0,
            image_path VARCHAR(255) NOT NULL,
            pdf_path VARCHAR(255) NOT NULL,
            price DECIMAL(10,2) NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
}

/**
 * Create digital_featured_images table if it does not exist (PostgreSQL).
 */
function nexora_digital_featured_images_ensure_table(PDO $pdo): void
{
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS digital_featured_images (
            id SERIAL PRIMARY KEY,
            image_path VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
}

/**
 * Client testimonials / comments for Digital page (PostgreSQL).
 */
function nexora_digital_client_comments_ensure_table(PDO $pdo): void
{
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS digital_client_comments (
            id SERIAL PRIMARY KEY,
            client_name VARCHAR(120) NOT NULL,
            comment TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
}

/**
 * Agro shop products (PostgreSQL). Images live under assets/uploads/agro/items/{id}/.
 */
function nexora_agro_shop_items_ensure_table(PDO $pdo): void
{
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS agro_shop_items (
            id SERIAL PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            price DECIMAL(12, 2) NOT NULL DEFAULT 0,
            stock_status VARCHAR(20) NOT NULL DEFAULT 'in_stock',
            description TEXT,
            image_main VARCHAR(500),
            image_gallery_1 VARCHAR(500),
            image_gallery_2 VARCHAR(500),
            image_gallery_3 VARCHAR(500),
            image_gallery_4 VARCHAR(500),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    $check = $pdo->query("
        SELECT 1 FROM information_schema.columns
        WHERE table_schema = 'public' AND table_name = 'agro_shop_items' AND column_name = 'description'
    ");
    if ($check && $check->fetchColumn() === false) {
        $pdo->exec('ALTER TABLE agro_shop_items ADD COLUMN description TEXT');
    }
}

/**
 * Phone & email per division for public contact areas (PostgreSQL).
 */
function nexora_division_contacts_ensure_table(PDO $pdo): void
{
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS division_contact_settings (
            division VARCHAR(20) PRIMARY KEY,
            phone VARCHAR(80) NOT NULL DEFAULT '',
            email VARCHAR(180) NOT NULL DEFAULT '',
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    $seed = [
        ['digital', '+94 77 123 4567', 'digital@nexora.lk'],
        ['agro', '+94 77 123 4567', 'agro@nexora.lk'],
        ['printing', '+94 77 123 4567', 'printing@nexora.lk'],
    ];
    $ins = $pdo->prepare(
        'INSERT INTO division_contact_settings (division, phone, email) VALUES (?, ?, ?) ON CONFLICT (division) DO NOTHING'
    );
    foreach ($seed as $row) {
        $ins->execute($row);
    }
}

/**
 * Admin panel users (PostgreSQL). Passwords stored with password_hash().
 * Seeds default admin/admin123 when the table is empty (change after first login).
 */
function nexora_admin_users_ensure_table(PDO $pdo): void
{
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS admin_users (
            id SERIAL PRIMARY KEY,
            username VARCHAR(64) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $count = (int) $pdo->query('SELECT COUNT(*) FROM admin_users')->fetchColumn();
    if ($count === 0) {
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->prepare('INSERT INTO admin_users (username, password_hash) VALUES (?, ?)')->execute(['admin', $hash]);
    }
}

/**
 * Agro order requests captured from the product page modal.
 */
function nexora_agro_orders_ensure_table(PDO $pdo): void
{
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS agro_orders (
            id SERIAL PRIMARY KEY,
            product_id INTEGER NOT NULL,
            product_price DECIMAL(12, 2) NOT NULL DEFAULT 0,
            customer_name VARCHAR(120) NOT NULL,
            customer_phone VARCHAR(40) NOT NULL,
            customer_email VARCHAR(180) NOT NULL,
            address_line1 VARCHAR(220) NOT NULL,
            address_line2 VARCHAR(220),
            city VARCHAR(120) NOT NULL,
            province VARCHAR(120) NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'new',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
}

/**
 * Printing order requests captured from the printing page modal.
 */
function nexora_printing_orders_ensure_table(PDO $pdo): void
{
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS printing_orders (
            id SERIAL PRIMARY KEY,
            document_id INTEGER NOT NULL,
            document_price DECIMAL(12, 2) NOT NULL DEFAULT 0,
            customer_name VARCHAR(120) NOT NULL,
            customer_phone VARCHAR(40) NOT NULL,
            customer_email VARCHAR(180) NOT NULL,
            address_line1 VARCHAR(220) NOT NULL,
            address_line2 VARCHAR(220),
            city VARCHAR(120) NOT NULL,
            province VARCHAR(120) NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'new',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
}

/**
 * Printing custom order requests from "Contact us for custom printout".
 */
function nexora_printing_custom_orders_ensure_table(PDO $pdo): void
{
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS printing_custom_orders (
            id SERIAL PRIMARY KEY,
            custom_request TEXT NOT NULL,
            customer_name VARCHAR(120) NOT NULL,
            customer_phone VARCHAR(40) NOT NULL,
            customer_email VARCHAR(180) NOT NULL,
            address_line1 VARCHAR(220) NOT NULL,
            address_line2 VARCHAR(220),
            city VARCHAR(120) NOT NULL,
            province VARCHAR(120) NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'new',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
}
