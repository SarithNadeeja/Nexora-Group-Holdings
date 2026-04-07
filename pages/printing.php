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
                                <p class="print-price">LKR <?php echo htmlspecialchars(number_format($price, 2)); ?></p>
                                <div class="print-doc-actions">
                                    <?php if ($pdfPath !== '' && isset($doc['id'])): ?>
                                        <button type="button" class="btn-print-secondary print-pdf-preview-btn" data-pdf-preview-url="<?php echo htmlspecialchars(BASE_URL . '/pages/pdf-view.php?id=' . (int) $doc['id'], ENT_QUOTES, 'UTF-8'); ?>">Preview PDF</button>
                                    <?php else: ?>
                                        <button type="button" class="btn-print-secondary" disabled>Preview PDF</button>
                                    <?php endif; ?>
                                    <button
                                        type="button"
                                        class="btn-primary print-order-open-btn"
                                        data-print-order-id="<?php echo (int) $doc['id']; ?>"
                                        data-print-order-price="<?php echo htmlspecialchars(number_format($price, 2, '.', '')); ?>"
                                        data-print-order-preview-url="<?php echo htmlspecialchars(BASE_URL . '/pages/pdf-view.php?id=' . (int) $doc['id'], ENT_QUOTES, 'UTF-8'); ?>"
                                    >Order Print</button>
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
                <button type="button" class="btn-cta print-custom-order-open-btn">Contact Us for Custom Printout</button>
            </div>
        </div>
    </section>

    <div class="print-pdf-modal" id="printPdfModal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="printPdfModalTitle">
        <div class="print-pdf-modal-backdrop" data-print-pdf-close tabindex="-1"></div>
        <div class="print-pdf-modal-panel">
            <div class="print-pdf-modal-head">
                <h2 id="printPdfModalTitle">Document preview</h2>
                <button type="button" class="print-pdf-modal-x" data-print-pdf-close aria-label="Close preview">&times;</button>
            </div>
            <p class="print-pdf-modal-note">Preview only. Use <strong>Order Print</strong> to request a copy. Your browser may still allow saving or printing from its PDF viewer.</p>
            <div class="print-pdf-modal-frame-wrap">
                <iframe class="print-pdf-modal-iframe" id="printPdfIframe" title="PDF preview"></iframe>
            </div>
        </div>
    </div>

    <script type="application/json" id="printOrderPayload"><?php
        echo json_encode([
            'submitUrl' => BASE_URL . '/pages/printing-order-submit.php',
            'customSubmitUrl' => BASE_URL . '/pages/printing-custom-order-submit.php',
        ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    ?></script>

    <div class="agro-order-modal" id="printOrderModal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="printOrderModalTitle">
        <div class="agro-order-modal-backdrop" data-print-order-close tabindex="-1"></div>
        <div class="agro-order-modal-panel">
            <button type="button" class="agro-order-modal-x" data-print-order-close aria-label="Close">&times;</button>
            <h2 id="printOrderModalTitle">Order Print on WhatsApp</h2>
            <p class="agro-order-modal-lead">Review your PDF and enter details. We save the order first, then open WhatsApp with your order message.</p>

            <div style="border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;margin-bottom:14px;background:#f8fafc;">
                <iframe id="printOrderPreviewIframe" title="Selected PDF preview" style="width:100%;height:230px;border:0;display:block;"></iframe>
            </div>

            <div class="agro-order-modal-error" id="printOrderModalError" hidden></div>

            <form class="agro-order-modal-form" id="printOrderWaForm" novalidate>
                <input type="hidden" id="print_order_document_id" value="">
                <input type="hidden" id="print_order_document_price" value="">

                <label for="print_ord_name">Full name</label>
                <input type="text" id="print_ord_name" name="customer_name" required maxlength="120" autocomplete="name">

                <label for="print_ord_phone">Contact number</label>
                <input type="tel" id="print_ord_phone" name="customer_phone" required maxlength="30" autocomplete="tel">

                <label for="print_ord_email">Email</label>
                <input type="email" id="print_ord_email" name="customer_email" required maxlength="180" autocomplete="email">

                <label for="print_ord_addr1">Address line 1</label>
                <input type="text" id="print_ord_addr1" name="address_line1" required maxlength="200" autocomplete="address-line1">

                <label for="print_ord_addr2">Address line 2 <span class="agro-order-optional">(optional)</span></label>
                <input type="text" id="print_ord_addr2" name="address_line2" maxlength="200" autocomplete="address-line2">

                <label for="print_ord_city">City</label>
                <input type="text" id="print_ord_city" name="city" required maxlength="100" autocomplete="address-level2">

                <label for="print_ord_province">Province</label>
                <input type="text" id="print_ord_province" name="province" required maxlength="100">

                <button type="submit" class="agro-wa-btn agro-order-modal-submit">Order on WhatsApp</button>
            </form>
        </div>
    </div>

    <div class="agro-order-modal" id="printCustomOrderModal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="printCustomOrderModalTitle">
        <div class="agro-order-modal-backdrop" data-print-custom-order-close tabindex="-1"></div>
        <div class="agro-order-modal-panel">
            <button type="button" class="agro-order-modal-x" data-print-custom-order-close aria-label="Close">&times;</button>
            <h2 id="printCustomOrderModalTitle">Custom Printout Request</h2>
            <p class="agro-order-modal-lead">Tell us what you need printed and add your details. We save your request, then open WhatsApp.</p>
            <div class="agro-order-modal-error" id="printCustomOrderModalError" hidden></div>

            <form class="agro-order-modal-form" id="printCustomOrderWaForm" novalidate>
                <label for="print_custom_request">Custom print details</label>
                <textarea id="print_custom_request" name="custom_request" required maxlength="4000" placeholder="Example: 200 A4 color flyers, double-sided, matte finish"></textarea>

                <label for="print_custom_name">Full name</label>
                <input type="text" id="print_custom_name" name="customer_name" required maxlength="120" autocomplete="name">

                <label for="print_custom_phone">Contact number</label>
                <input type="tel" id="print_custom_phone" name="customer_phone" required maxlength="30" autocomplete="tel">

                <label for="print_custom_email">Email</label>
                <input type="email" id="print_custom_email" name="customer_email" required maxlength="180" autocomplete="email">

                <label for="print_custom_addr1">Address line 1</label>
                <input type="text" id="print_custom_addr1" name="address_line1" required maxlength="200" autocomplete="address-line1">

                <label for="print_custom_addr2">Address line 2 <span class="agro-order-optional">(optional)</span></label>
                <input type="text" id="print_custom_addr2" name="address_line2" maxlength="200" autocomplete="address-line2">

                <label for="print_custom_city">City</label>
                <input type="text" id="print_custom_city" name="city" required maxlength="100" autocomplete="address-level2">

                <label for="print_custom_province">Province</label>
                <input type="text" id="print_custom_province" name="province" required maxlength="100">

                <button type="submit" class="agro-wa-btn agro-order-modal-submit">Order on WhatsApp</button>
            </form>
        </div>
    </div>
</main>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>

