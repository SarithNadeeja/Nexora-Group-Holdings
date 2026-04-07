<?php
if (!isset($adminPageTitle)) {
    $adminPageTitle = 'Admin Panel';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($adminPageTitle); ?> | Nexora Admin</title>
    <style>
        :root {
            --primary: #2563eb;
            --text: #1f2937;
            --muted: #6b7280;
            --bg: #f5f7fa;
            --panel: #ffffff;
            --sidebar: #111827;
            --border: #e5e7eb;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: "Segoe UI", Arial, sans-serif; color: var(--text); background: var(--bg); }
        a { text-decoration: none; }

        .admin-layout { display: grid; grid-template-columns: 250px 1fr; min-height: 100vh; }
        .admin-main { padding: 28px; }
        .admin-card {
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 14px;
            box-shadow: 0 10px 26px rgba(15, 23, 42, 0.05);
            padding: 22px;
        }

        .admin-heading { margin-bottom: 18px; }
        .admin-heading h1 { margin-bottom: 6px; }
        .admin-heading p { color: var(--muted); }

        .admin-form { display: grid; gap: 12px; }
        .admin-form label { font-weight: 600; }
        .admin-form input,
        .admin-form textarea {
            width: 100%;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 11px 12px;
            font: inherit;
            background: #fcfdff;
        }
        .admin-form textarea {
            min-height: 120px;
            resize: vertical;
        }
        .btn-primary {
            display: inline-block;
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 10px 16px;
            cursor: pointer;
            font-weight: 600;
        }
        .btn-primary:hover { background: #1d4ed8; }

        .btn-danger {
            display: inline-block;
            background: #dc2626;
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 8px 14px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.9rem;
        }
        .btn-danger:hover { background: #b91c1c; }

        .admin-table-wrap { overflow-x: auto; margin-top: 12px; }
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.95rem;
        }
        .admin-table th,
        .admin-table td {
            border: 1px solid var(--border);
            padding: 10px 12px;
            text-align: left;
        }
        .admin-table th { background: #f8fafc; color: var(--muted); font-weight: 600; }
        .admin-table tr:nth-child(even) td { background: #fafbfc; }

        .admin-form-hint { font-size: 0.85rem; color: var(--muted); margin-top: -6px; }
        fieldset.admin-fieldset {
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 20px;
        }
        fieldset.admin-fieldset legend {
            font-weight: 700;
            padding: 0 8px;
        }

        .alert-success {
            border: 1px solid #bbf7d0;
            background: #f0fdf4;
            color: #166534;
            padding: 10px 12px;
            border-radius: 10px;
            margin-bottom: 12px;
        }
        .alert-error {
            border: 1px solid #fecaca;
            background: #fef2f2;
            color: #991b1b;
            padding: 10px 12px;
            border-radius: 10px;
            margin-bottom: 12px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin-top: 12px;
        }
        .stat-box {
            background: #f8fafc;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 14px;
        }
        .stat-box h3 { font-size: 0.9rem; color: var(--muted); margin-bottom: 6px; }
        .stat-box p { font-size: 1.5rem; font-weight: 700; }

        @media (max-width: 900px) {
            .admin-layout { grid-template-columns: 1fr; }
            .admin-main { padding: 16px; }
            .stats-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

