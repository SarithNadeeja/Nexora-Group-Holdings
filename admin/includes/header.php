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
        .admin-main { padding: 28px; min-width: 0; }
        .admin-card {
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 14px;
            box-shadow: 0 10px 26px rgba(15, 23, 42, 0.05);
            padding: 22px;
        }
        .admin-card + .admin-card,
        .admin-card-spaced { margin-top: 14px; }

        .admin-heading { margin-bottom: 18px; }
        .admin-heading h1 { margin-bottom: 6px; font-size: clamp(1.35rem, 4vw, 1.75rem); line-height: 1.2; }
        .admin-heading p { color: var(--muted); font-size: 0.95rem; }
        .admin-section-title { font-size: 1.1rem; margin-bottom: 12px; }

        .admin-form { display: grid; gap: 12px; }
        .admin-form label { font-weight: 600; }
        .admin-form input,
        .admin-form textarea,
        .admin-form select {
            width: 100%;
            max-width: 100%;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 11px 12px;
            font: inherit;
            background: #fcfdff;
        }
        .admin-form input[type="file"] {
            padding: 8px;
            background: #fff;
        }
        .admin-form textarea {
            min-height: 120px;
            resize: vertical;
        }
        .admin-form-compact { gap: 8px; min-width: 0; }

        .btn-primary {
            display: inline-block;
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 10px 16px;
            cursor: pointer;
            font-weight: 600;
            text-align: center;
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

        .admin-table-wrap {
            overflow-x: auto;
            margin-top: 12px;
            -webkit-overflow-scrolling: touch;
        }
        .admin-table {
            width: 100%;
            min-width: 640px;
            border-collapse: collapse;
            font-size: 0.95rem;
        }
        .admin-table th,
        .admin-table td {
            border: 1px solid var(--border);
            padding: 10px 12px;
            text-align: left;
            vertical-align: top;
        }
        .admin-table th { background: #f8fafc; color: var(--muted); font-weight: 600; }
        .admin-table tr:nth-child(even) td { background: #fafbfc; }

        .admin-product-grid,
        .admin-media-grid {
            display: grid;
            gap: 14px;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        }
        .admin-product-card,
        .admin-media-card {
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
            background: #fff;
        }
        .admin-product-card img,
        .admin-media-card img {
            width: 100%;
            height: 140px;
            object-fit: cover;
            display: block;
        }
        .admin-product-card-body,
        .admin-media-card-body { padding: 12px; }
        .admin-card-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

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

        /* Sidebar */
        .admin-menu-toggle,
        .admin-sidebar-overlay,
        .admin-sidebar-close { display: none; }

        .admin-sidebar {
            background: var(--sidebar);
            color: #e5e7eb;
            padding: 24px 16px;
        }
        .admin-sidebar-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 22px;
        }
        .admin-sidebar-title {
            font-size: 1.05rem;
            margin: 0;
        }
        .admin-sidebar-nav {
            display: grid;
            gap: 8px;
        }
        .admin-sidebar-nav a {
            color: #e5e7eb;
            padding: 10px 12px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.04);
        }
        .admin-sidebar-nav a:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        .admin-sidebar-nav .admin-nav-logout { color: #fecaca; }

        body.admin-nav-open { overflow: hidden; }

        @media (max-width: 900px) {
            .admin-layout { display: block; }
            .admin-main { padding: 68px 14px 20px; }

            .admin-menu-toggle {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                position: fixed;
                top: 12px;
                left: 12px;
                z-index: 130;
                min-height: 44px;
                min-width: 72px;
                padding: 10px 14px;
                border: 1px solid var(--border);
                border-radius: 10px;
                background: #fff;
                color: var(--text);
                font: inherit;
                font-weight: 600;
                cursor: pointer;
                box-shadow: 0 4px 14px rgba(15, 23, 42, 0.12);
            }

            .admin-sidebar-overlay {
                display: block;
                position: fixed;
                inset: 0;
                background: rgba(15, 23, 42, 0.45);
                z-index: 140;
                opacity: 0;
                pointer-events: none;
                transition: opacity 0.2s ease;
            }
            .admin-sidebar-overlay.is-visible {
                opacity: 1;
                pointer-events: auto;
            }

            .admin-sidebar {
                position: fixed;
                top: 0;
                left: 0;
                width: min(300px, 88vw);
                height: 100vh;
                z-index: 150;
                padding: 18px 14px;
                overflow-y: auto;
                transform: translateX(-105%);
                transition: transform 0.24s ease;
                box-shadow: 8px 0 24px rgba(0, 0, 0, 0.2);
            }
            .admin-sidebar.is-open { transform: translateX(0); }

            .admin-sidebar-close {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 40px;
                height: 40px;
                border: 0;
                border-radius: 8px;
                background: rgba(255, 255, 255, 0.08);
                color: #fff;
                font-size: 1.6rem;
                line-height: 1;
                cursor: pointer;
            }

            .admin-card { padding: 16px; border-radius: 12px; }
            .stats-grid,
            .admin-product-grid,
            .admin-media-grid { grid-template-columns: 1fr; }
            .admin-table { font-size: 0.88rem; }
            .btn-primary,
            .btn-danger { min-height: 44px; }
        }
    </style>
</head>
<body>

