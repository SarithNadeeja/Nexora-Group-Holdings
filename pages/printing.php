<?php
require_once dirname(__DIR__) . '/includes/config.php';
$pageTitle = 'Nexora Printing';

/**
 * Dynamic print document loading (PostgreSQL).
 * Credentials: includes/database.php (or DB_* environment variables).
 */
require_once dirname(__DIR__) . '/includes/database.php';

$documents = [];
$dbError = null;

$pdo = nexora_db_connect();
if ($pdo) {
    try {
        nexora_print_documents_ensure_table($pdo);
        $stmt = $pdo->query('SELECT id, name, pages, image_path, pdf_path, price FROM print_documents ORDER BY id DESC');
        $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $dbError = 'No print documents found right now.';
    }
} else {
    $dbError = 'Document listing is temporarily unavailable.';
}

include dirname(__DIR__) . '/includes/header.php';
include dirname(__DIR__) . '/includes/navbar.php';
?>

<main class="page-main">
    <!-- Printing Hero -->
    <section class="digital-page-hero">
        <div class="container digital-page-hero-inner reveal-on-scroll">
            <div class="digital-page-hero-text">
                <p class="digital-page-label">NEXORA PRINTING</p>
                <h1>Professional Printing Solutions</h1>
                <h2>High-quality document and book printing with fast delivery.</h2>
                <p>Choose from our available print-ready documents or upload your own for custom printing.</p>
            </div>
            <div class="digital-page-hero-media">
                <?php
                $printingHeroLogo = dirname(__DIR__) . '/assets/images/logos/printing.jpeg';
                ?>
                <?php if (file_exists($printingHeroLogo)): ?>
                    <img src="<?php echo BASE_URL; ?>/assets/images/logos/printing.jpeg" alt="Nexora Printing hero visual">
                <?php else: ?>
                    <div class="dp-image-fallback">Add assets/images/logos/printing.jpeg</div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Print Method -->
    <section class="print-method-section">
        <div class="container">
            <div class="section-heading">
                <h2>How Printing Works</h2>
            </div>
            <div class="print-steps-grid">
                <article class="print-step reveal-on-scroll" style="--delay: 0s;">
                    <span class="process-number">01</span>
                    <h3>Select a Document or Upload Your Own PDF</h3>
                </article>
                <article class="print-step reveal-on-scroll" style="--delay: 0.1s;">
                    <span class="process-number">02</span>
                    <h3>Customize Print Options</h3>
                </article>
                <article class="print-step reveal-on-scroll" style="--delay: 0.2s;">
                    <span class="process-number">03</span>
                    <h3>Place Order &amp; Get It Delivered</h3>
                </article>
            </div>
        </div>
    </section>

    <!-- Dynamic Print Documents -->
    <section class="print-documents-section">
        <div class="container">
            <div class="section-heading">
                <h2>Available Print Documents</h2>
                <p>Browse ready-to-print files and place your order quickly.</p>
            </div>

            <?php if ($dbError !== null && count($documents) === 0): ?>
                <div class="content-card">
                    <p><?php echo htmlspecialchars($dbError); ?></p>
                </div>
            <?php endif; ?>

            <?php if (count($documents) > 0): ?>
                <div class="print-doc-grid">
                    <?php foreach ($documents as $index => $doc): ?>
                        <?php
                        $name = isset($doc['name']) ? $doc['name'] : 'Untitled Document';
                        $pages = isset($doc['pages']) ? (int) $doc['pages'] : 0;
                        $price = isset($doc['price']) ? (float) $doc['price'] : 0;
                        $imagePath = isset($doc['image_path']) ? trim((string) $doc['image_path']) : '';
                        $pdfPath = isset($doc['pdf_path']) ? trim((string) $doc['pdf_path']) : '';
                        $delay = ($index % 6) * 0.08;
                        ?>
                        <article class="print-doc-card reveal-on-scroll" style="--delay: <?php echo htmlspecialchars((string) $delay); ?>s;">
                            <div class="print-doc-media">
                                <?php if ($imagePath !== ''): ?>
                                    <img src="<?php echo BASE_URL . '/' . ltrim(htmlspecialchars($imagePath), '/'); ?>" alt="<?php echo htmlspecialchars($name); ?>">
                                <?php else: ?>
                                    <div class="dp-image-fallback">No Preview</div>
                                <?php endif; ?>
                            </div>
                            <div class="print-doc-body">
                                <h3><?php echo htmlspecialchars($name); ?></h3>
                                <p><?php echo $pages; ?> pages</p>
                                <p class="print-price">$<?php echo number_format($price, 2); ?></p>
                                <div class="print-doc-actions">
                                    <?php if ($pdfPath !== ''): ?>
                                        <a class="btn-print-secondary" href="<?php echo BASE_URL . '/' . ltrim(htmlspecialchars($pdfPath), '/'); ?>" target="_blank" rel="noopener noreferrer">View PDF</a>
                                    <?php else: ?>
                                        <button type="button" class="btn-print-secondary" disabled>View PDF</button>
                                    <?php endif; ?>
                                    <button type="button" class="btn-primary">Order Print</button>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Custom Print CTA -->
    <section class="printing-custom-cta">
        <div class="container cta-inner">
            <div class="cta-content reveal-on-scroll">
                <h2>Have Your Own Document to Print?</h2>
                <p>Upload your own PDF and get it printed with our high-quality service.</p>
                <a class="btn-cta" href="<?php echo BASE_URL; ?>/contact.php">Contact Us</a>
            </div>
        </div>
    </section>
</main>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>

