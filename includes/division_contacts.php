<?php
/**
 * Load division-specific phone & email for Digital, Agro, Printing.
 */
require_once __DIR__ . '/database.php';

/**
 * @return array<string, array{phone: string, email: string}>
 */
function nexora_division_contact_defaults(): array
{
    return [
        'digital' => ['phone' => '+94 77 123 4567', 'email' => 'digital@nexora.lk'],
        'agro' => ['phone' => '+94 77 123 4567', 'email' => 'agro@nexora.lk'],
        'printing' => ['phone' => '+94 77 123 4567', 'email' => 'printing@nexora.lk'],
    ];
}

/**
 * @return array<string, array{phone: string, email: string}>
 */
function nexora_division_contacts_all(): array
{
    $out = nexora_division_contact_defaults();
    $pdo = nexora_db_connect();
    if (!$pdo) {
        return $out;
    }
    try {
        nexora_division_contacts_ensure_table($pdo);
        $rows = $pdo->query('SELECT division, phone, email FROM division_contact_settings')->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $d = (string) ($row['division'] ?? '');
            if ($d !== '' && isset($out[$d])) {
                $out[$d] = [
                    'phone' => trim((string) ($row['phone'] ?? '')),
                    'email' => trim((string) ($row['email'] ?? '')),
                ];
            }
        }
    } catch (Throwable $e) {
        // use defaults
    }
    return $out;
}

function nexora_phone_tel_href(string $phone): string
{
    $t = trim($phone);
    if ($t === '') {
        return '#';
    }
    $clean = preg_replace('/\s+/', '', $t);
    return 'tel:' . $clean;
}
