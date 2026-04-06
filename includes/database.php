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
            image_main VARCHAR(500),
            image_gallery_1 VARCHAR(500),
            image_gallery_2 VARCHAR(500),
            image_gallery_3 VARCHAR(500),
            image_gallery_4 VARCHAR(500),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
}
