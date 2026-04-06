<?php
require_once __DIR__ . '/includes/auth.php';
requireAdminAuth();
require_once __DIR__ . '/includes/db.php';

$adminPageTitle = 'Dashboard';

$totalDocuments = 0;
$latestDocuments = [];

$totalDocuments = (int) $pdo->query('SELECT COUNT(*) FROM print_documents')->fetchColumn();

$stmt = $pdo->query('SELECT id, name, created_at FROM print_documents ORDER BY id DESC LIMIT 6');
$latestDocuments = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/includes/header.php';
?>
<div class="admin-layout">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    <main class="admin-main">
        <div class="admin-heading">
            <h1>Dashboard</h1>
            <p>Welcome back. Manage your print documents from here.</p>
        </div>

        <section class="admin-card">
            <div class="stats-grid">
                <div class="stat-box">
                    <h3>Total Documents</h3>
                    <p><?php echo $totalDocuments; ?></p>
                </div>
                <div class="stat-box">
                    <h3>Quick Action</h3>
                    <p><a href="add-document.php" style="font-size:1rem;color:#2563eb;">Add New Document</a></p>
                </div>
            </div>
        </section>

        <section class="admin-card" style="margin-top:14px;">
            <h2 style="font-size:1.1rem;margin-bottom:10px;">Recent Documents</h2>
            <?php if (count($latestDocuments) === 0): ?>
                <p style="color:#6b7280;">No documents uploaded yet.</p>
            <?php else: ?>
                <ul style="list-style:none;display:grid;gap:8px;">
                    <?php foreach ($latestDocuments as $doc): ?>
                        <li style="padding:10px 12px;border:1px solid #e5e7eb;border-radius:10px;background:#f8fafc;">
                            <strong><?php echo htmlspecialchars($doc['name']); ?></strong>
                            <span style="color:#6b7280;"> - <?php echo htmlspecialchars($doc['created_at']); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
    </main>
</div>
</body>
</html>

