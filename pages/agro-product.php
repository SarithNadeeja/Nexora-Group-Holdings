<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/database.php';

$root = dirname(__DIR__);
$productId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$product = null;
$similarItems = [];

$pdo = nexora_db_connect();
if ($pdo && $productId > 0) {
    try {
        nexora_agro_shop_items_ensure_table($pdo);
        $st = $pdo->prepare('SELECT * FROM agro_shop_items WHERE id = ?');
        $st->execute([$productId]);
        $product = $st->fetch(PDO::FETCH_ASSOC) ?: null;

        if ($product !== null) {
            $sim = $pdo->prepare(
                'SELECT id, name, price, stock_status, image_main FROM agro_shop_items WHERE id <> ? ORDER BY RANDOM() LIMIT 8'
            );
            $sim->execute([$productId]);
            $similarItems = $sim->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        $product = null;
        $similarItems = [];
    }
}

if ($product === null) {
    http_response_code(404);
    $pageTitle = 'Product not found';
    include dirname(__DIR__) . '/includes/header.php';
    include dirname(__DIR__) . '/includes/navbar.php';
    ?>
    <main class="page-main agro-pdp-page">
        <section class="agro-pdp-missing">
            <div class="container">
                <h1>Product not found</h1>
                <p>This item may have been removed or the link is incorrect.</p>
                <a class="agro-btn agro-btn-primary" href="<?php echo BASE_URL; ?>/pages/agro.php">Back to Agro shop</a>
            </div>
        </section>
    </main>
    <?php include dirname(__DIR__) . '/includes/footer.php'; ?>
    <?php
    exit;
}

$pageTitle = $product['name'];

$mainRel = (string) ($product['image_main'] ?? '');
$mainAbs = $mainRel !== '' ? $root . '/' . ltrim($mainRel, '/') : '';
$mainUrl = $mainRel !== '' ? BASE_URL . '/' . ltrim($mainRel, '/') : '';

$waDigits = nexora_whatsapp_agro_order_digits();
$canonicalProductUrl = nexora_site_absolute_url('pages/agro-product.php?id=' . $productId);
$absoluteImageUrl = ($mainRel !== '' && is_file($mainAbs)) ? nexora_site_absolute_url(ltrim($mainRel, '/')) : '';

$description = isset($product['description']) ? trim((string) $product['description']) : '';

$metaDescription = 'Order ' . $product['name'] . ' from Nexora Agro — LKR ' . number_format((float) $product['price'], 2) . '.';
$metaOgUrl = $canonicalProductUrl;
$metaOgTitle = $product['name'] . ' | Nexora Agro';
$metaOgDescription = $description !== ''
    ? mb_substr(preg_replace('/\s+/', ' ', strip_tags($description)), 0, 200)
    : $metaDescription;
$metaOgImage = $absoluteImageUrl !== '' ? $absoluteImageUrl : null;

$galleryRels = array_filter([
    $product['image_gallery_1'] ?? '',
    $product['image_gallery_2'] ?? '',
    $product['image_gallery_3'] ?? '',
    $product['image_gallery_4'] ?? '',
], static function ($p) {
    return $p !== null && $p !== '';
});

$stock = $product['stock_status'] ?? 'in_stock';
$stockClass = 'agro-stock-in';
$stockLabel = 'In stock';
if ($stock === 'pre_order') {
    $stockClass = 'agro-stock-pre';
    $stockLabel = 'Pre-order';
} elseif ($stock === 'out_of_stock') {
    $stockClass = 'agro-stock-out';
    $stockLabel = 'Out of stock';
}

include dirname(__DIR__) . '/includes/header.php';
include dirname(__DIR__) . '/includes/navbar.php';
?>

<main class="page-main agro-pdp-page">
    <section class="agro-pdp-hero-bar">
        <div class="container">
            <nav class="agro-pdp-breadcrumb" aria-label="Breadcrumb">
                <a href="<?php echo BASE_URL; ?>/index.php">Home</a>
                <span aria-hidden="true">/</span>
                <a href="<?php echo BASE_URL; ?>/pages/agro.php">Nexora Agro</a>
                <span aria-hidden="true">/</span>
                <span class="agro-pdp-breadcrumb-current"><?php echo htmlspecialchars($product['name']); ?></span>
            </nav>
        </div>
    </section>

    <section class="agro-pdp-main">
        <div class="container">
            <div class="agro-pdp-grid">
                <div class="agro-pdp-media agro-gallery-swap reveal-on-scroll">
                    <div class="agro-pdp-main-visual">
                        <?php if ($mainRel !== '' && is_file($mainAbs)): ?>
                            <img class="agro-product-main-img agro-pdp-main-img" src="<?php echo htmlspecialchars($mainUrl); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <?php else: ?>
                            <div class="agro-product-placeholder agro-pdp-placeholder"><?php echo htmlspecialchars($product['name']); ?></div>
                        <?php endif; ?>
                    </div>
                    <?php if (count($galleryRels) > 0): ?>
                        <div class="agro-product-thumbs agro-pdp-thumbs" role="group" aria-label="Product gallery">
                            <?php if ($mainRel !== '' && is_file($mainAbs)): ?>
                                <button type="button" class="agro-product-thumb agro-pdp-thumb-active" data-src="<?php echo htmlspecialchars($mainUrl); ?>" aria-label="Main photo">
                                    <img src="<?php echo htmlspecialchars($mainUrl); ?>" alt="">
                                </button>
                            <?php endif; ?>
                            <?php foreach ($galleryRels as $gRel): ?>
                                <?php
                                $gUrl = BASE_URL . '/' . ltrim((string) $gRel, '/');
                                $gAbs = $root . '/' . ltrim((string) $gRel, '/');
                                ?>
                                <?php if (is_file($gAbs)): ?>
                                    <button type="button" class="agro-product-thumb" data-src="<?php echo htmlspecialchars($gUrl); ?>" aria-label="Gallery photo">
                                        <img src="<?php echo htmlspecialchars($gUrl); ?>" alt="">
                                    </button>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="agro-pdp-buybox reveal-on-scroll">
                    <span class="agro-stock-badge <?php echo htmlspecialchars($stockClass); ?>"><?php echo htmlspecialchars($stockLabel); ?></span>
                    <h1 class="agro-pdp-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                    <p class="agro-pdp-price">LKR <?php echo htmlspecialchars(number_format((float) $product['price'], 2)); ?></p>
                    <p class="agro-pdp-sku">Item #<?php echo (int) $product['id']; ?></p>

                    <?php if ($stock !== 'out_of_stock'): ?>
                        <button type="button" class="agro-wa-btn" id="agroWaOrderOpen"><?php echo $stock === 'pre_order' ? 'Pre-order on WhatsApp' : 'Order on WhatsApp'; ?></button>
                    <?php else: ?>
                        <div class="agro-wa-placeholder agro-wa-placeholder-muted">
                            <strong>Currently unavailable</strong>
                            <p>This item is out of stock. Browse similar products below or <a href="<?php echo htmlspecialchars(nexora_contact_href('agro')); ?>" target="_blank" rel="noopener noreferrer">contact us</a> for restock updates.</p>
                        </div>
                    <?php endif; ?>

                    <a class="agro-pdp-back-link" href="<?php echo BASE_URL; ?>/pages/agro.php">&larr; Back to all products</a>
                </div>
            </div>

            <div class="agro-pdp-description-block reveal-on-scroll">
                <h2>Product details</h2>
                <?php if ($description !== ''): ?>
                    <div class="agro-pdp-description"><?php echo nl2br(htmlspecialchars($description, ENT_QUOTES, 'UTF-8')); ?></div>
                <?php else: ?>
                    <p class="agro-pdp-description-empty">A detailed description will be added soon. Questions? <a href="<?php echo htmlspecialchars(nexora_contact_href('agro')); ?>" target="_blank" rel="noopener noreferrer">Get in touch</a> and we will help right away.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php if (count($similarItems) > 0): ?>
        <section class="agro-similar-section">
            <div class="container">
                <header class="agro-similar-header reveal-on-scroll">
                    <h2>Recommended for you</h2>
                    <p>Explore more from our agro catalog &mdash; quality picks customers often view together with this item.</p>
                </header>
                <div class="agro-similar-grid">
                    <?php foreach (array_slice($similarItems, 0, 6) as $s): ?>
                        <?php
                        $sMain = (string) ($s['image_main'] ?? '');
                        $sAbs = $sMain !== '' ? $root . '/' . ltrim($sMain, '/') : '';
                        $sUrl = $sMain !== '' ? BASE_URL . '/' . ltrim($sMain, '/') : '';
                        $sPdp = BASE_URL . '/pages/agro-product.php?id=' . (int) $s['id'];
                        ?>
                        <a class="agro-similar-card reveal-on-scroll" href="<?php echo htmlspecialchars($sPdp); ?>">
                            <div class="agro-similar-card-media">
                                <?php if ($sMain !== '' && is_file($sAbs)): ?>
                                    <img src="<?php echo htmlspecialchars($sUrl); ?>" alt="<?php echo htmlspecialchars($s['name']); ?>">
                                <?php else: ?>
                                    <div class="agro-similar-card-fallback"><?php echo htmlspecialchars(mb_substr($s['name'], 0, 40)); ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="agro-similar-card-body">
                                <h3><?php echo htmlspecialchars($s['name']); ?></h3>
                                <p class="agro-similar-card-price">LKR <?php echo htmlspecialchars(number_format((float) $s['price'], 2)); ?></p>
                                <span class="agro-similar-card-cta">View product</span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($stock !== 'out_of_stock'): ?>
        <script type="application/json" id="agroWaOrderPayload"><?php
            echo json_encode([
                'waDigits' => $waDigits,
                'productId' => $productId,
                'productPrice' => number_format((float) $product['price'], 2),
                'isPreOrder' => $stock === 'pre_order',
                'submitUrl' => BASE_URL . '/pages/agro-order-submit.php',
            ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        ?></script>

        <div class="agro-order-modal" id="agroOrderModal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="agroOrderModalTitle">
            <div class="agro-order-modal-backdrop" data-agro-modal-close tabindex="-1"></div>
            <div class="agro-order-modal-panel">
                <button type="button" class="agro-order-modal-x" data-agro-modal-close aria-label="Close">&times;</button>
                <h2 id="agroOrderModalTitle">Order on WhatsApp</h2>
                <p class="agro-order-modal-lead">Enter your details and click Order. We will save your order, then open WhatsApp with your order message.</p>

                <div class="agro-order-modal-error" id="agroOrderModalError" hidden></div>

                <form class="agro-order-modal-form" id="agroOrderWaForm" novalidate>
                    <label for="agro_ord_name">Full name</label>
                    <input type="text" id="agro_ord_name" name="customer_name" required maxlength="120" autocomplete="name">

                    <label for="agro_ord_phone">Contact number</label>
                    <input type="tel" id="agro_ord_phone" name="customer_phone" required maxlength="30" autocomplete="tel">

                    <label for="agro_ord_email">Email</label>
                    <input type="email" id="agro_ord_email" name="customer_email" required maxlength="180" autocomplete="email">

                    <label for="agro_ord_addr1">Address line 1</label>
                    <input type="text" id="agro_ord_addr1" name="address_line1" required maxlength="200" autocomplete="address-line1">

                    <label for="agro_ord_addr2">Address line 2 <span class="agro-order-optional">(optional)</span></label>
                    <input type="text" id="agro_ord_addr2" name="address_line2" maxlength="200" autocomplete="address-line2">

                    <label for="agro_ord_city">City</label>
                    <input type="text" id="agro_ord_city" name="city" required maxlength="100" autocomplete="address-level2">

                    <label for="agro_ord_province">Province</label>
                    <input type="text" id="agro_ord_province" name="province" required maxlength="100">

                    <button type="submit" class="agro-wa-btn agro-order-modal-submit">Order on WhatsApp</button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</main>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
