<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/database.php';
$pageTitle = 'Nexora Agro';

$root = dirname(__DIR__);
$agroImagePath = $root . '/assets/images/agro.jpg';

$agroShopItems = [];
$pdoAgro = nexora_db_connect();
if ($pdoAgro) {
    try {
        nexora_agro_shop_items_ensure_table($pdoAgro);
        $agroShopItems = $pdoAgro->query(
            'SELECT id, name, price, stock_status, image_main, image_gallery_1, image_gallery_2, image_gallery_3, image_gallery_4 FROM agro_shop_items ORDER BY id DESC'
        )->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $agroShopItems = [];
    }
}

$agroCategories = [
    ['title' => 'Plant Covers', 'blurb' => 'Protect crops and garden beds with durable covers.'],
    ['title' => 'Fertilizers & Plant Protection Products', 'blurb' => 'Nutrition and care products for healthy growth.'],
    ['title' => 'Indoor & Decorative Plants', 'blurb' => 'Bring life to homes and workspaces.'],
    ['title' => 'Flower Pots & Planters', 'blurb' => 'Quality containers for every setting.'],
    ['title' => 'Seeds for Cultivation', 'blurb' => 'Trusted varieties for home and farm.'],
];

include dirname(__DIR__) . '/includes/header.php';
include dirname(__DIR__) . '/includes/navbar.php';
?>

<main class="page-main agro-page">
    <!-- Hero -->
    <section class="digital-page-hero agro-page-hero">
        <div class="container digital-page-hero-inner reveal-on-scroll">
            <div class="digital-page-hero-text">
                <p class="digital-page-label">NEXORA AGRO</p>
                <h1>Nexora Agro</h1>
                <h2>Fresh Agricultural Products &amp; Growing Essentials</h2>
                <p>We provide high-quality agricultural products including plants, seeds, and essential growing materials for both home and commercial use.</p>
                <a class="agro-btn agro-btn-primary" href="<?php echo BASE_URL; ?>/contact.php">Get in Touch</a>
            </div>
            <div class="digital-page-hero-media">
                <?php
                $agroHeroLogo = $root . '/assets/images/logos/agro.jpeg';
                ?>
                <?php if (file_exists($agroHeroLogo)): ?>
                    <img src="<?php echo BASE_URL; ?>/assets/images/logos/agro.jpeg" alt="Nexora Agro hero visual">
                <?php else: ?>
                    <div class="dp-image-fallback">Add assets/images/logos/agro.jpeg</div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- About -->
    <section class="agro-about-section">
        <div class="container agro-about-grid">
            <div class="agro-about-content reveal-on-scroll reveal-left">
                <h2>Trusted Agro Solutions</h2>
                <p>Our agro division focuses on delivering fresh, reliable, and sustainable agricultural products sourced from trusted suppliers.</p>
            </div>
            <div class="agro-about-media reveal-on-scroll reveal-right">
                <?php if (file_exists($agroImagePath)): ?>
                    <img src="<?php echo BASE_URL; ?>/assets/images/agro.jpg" alt="Nexora Agro — agricultural products and plants">
                <?php else: ?>
                    <div class="agro-image-fallback">Add assets/images/agro.jpg</div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Categories -->
    <section class="agro-categories-section">
        <div class="container">
            <div class="section-heading agro-section-heading reveal-on-scroll">
                <h2>Product Categories</h2>
                <p>Everything you need to grow with confidence — from seed to harvest and display.</p>
            </div>
            <div class="agro-categories-grid">
                <?php foreach ($agroCategories as $i => $cat): ?>
                    <article class="agro-category-card reveal-on-scroll" style="--delay: <?php echo $i * 0.06; ?>s;">
                        <div class="agro-category-icon" aria-hidden="true"></div>
                        <h3><?php echo htmlspecialchars($cat['title']); ?></h3>
                        <p><?php echo htmlspecialchars($cat['blurb']); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Shop products (managed in Admin → Agro Shop Items) -->
    <section class="agro-products-section">
        <div class="container">
            <div class="section-heading agro-section-heading reveal-on-scroll">
                <h2>Shop Products</h2>
                <p>Browse our catalog — order by contacting our team for availability and delivery.</p>
            </div>
            <?php if (count($agroShopItems) === 0): ?>
                <p class="agro-shop-empty reveal-on-scroll">Products will appear here once they are added in the admin panel. <a href="<?php echo BASE_URL; ?>/contact.php">Contact us</a> for current availability.</p>
            <?php else: ?>
                <div class="agro-products-grid">
                    <?php foreach ($agroShopItems as $i => $prod): ?>
                        <?php
                        $mainRel = isset($prod['image_main']) ? (string) $prod['image_main'] : '';
                        $mainAbs = $mainRel !== '' ? $root . '/' . ltrim($mainRel, '/') : '';
                        $mainUrl = $mainRel !== '' ? BASE_URL . '/' . ltrim($mainRel, '/') : '';
                        $galleryRels = array_filter([
                            $prod['image_gallery_1'] ?? '',
                            $prod['image_gallery_2'] ?? '',
                            $prod['image_gallery_3'] ?? '',
                            $prod['image_gallery_4'] ?? '',
                        ], static function ($p) {
                            return $p !== null && $p !== '';
                        });
                        $stock = $prod['stock_status'] ?? 'in_stock';
                        $stockClass = 'agro-stock-in';
                        $stockLabel = 'In stock';
                        if ($stock === 'pre_order') {
                            $stockClass = 'agro-stock-pre';
                            $stockLabel = 'Pre-order';
                        } elseif ($stock === 'out_of_stock') {
                            $stockClass = 'agro-stock-out';
                            $stockLabel = 'Out of stock';
                        }
                        ?>
                        <article class="agro-product-card reveal-on-scroll" style="--delay: <?php echo $i * 0.07; ?>s;">
                            <div class="agro-product-visual">
                                <?php if ($mainRel !== '' && is_file($mainAbs)): ?>
                                    <img class="agro-product-main-img" src="<?php echo htmlspecialchars($mainUrl); ?>" alt="<?php echo htmlspecialchars($prod['name']); ?>">
                                <?php else: ?>
                                    <div class="agro-product-placeholder"><?php echo htmlspecialchars($prod['name']); ?></div>
                                <?php endif; ?>
                            </div>
                            <?php if (count($galleryRels) > 0): ?>
                                <div class="agro-product-thumbs" role="group" aria-label="More photos">
                                    <?php foreach ($galleryRels as $gRel): ?>
                                        <?php
                                        $gUrl = BASE_URL . '/' . ltrim((string) $gRel, '/');
                                        $gAbs = $root . '/' . ltrim((string) $gRel, '/');
                                        ?>
                                        <?php if (is_file($gAbs)): ?>
                                            <button type="button" class="agro-product-thumb" data-src="<?php echo htmlspecialchars($gUrl); ?>" aria-label="View photo">
                                                <img src="<?php echo htmlspecialchars($gUrl); ?>" alt="">
                                            </button>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <div class="agro-product-body">
                                <span class="agro-stock-badge <?php echo htmlspecialchars($stockClass); ?>"><?php echo htmlspecialchars($stockLabel); ?></span>
                                <h3><?php echo htmlspecialchars($prod['name']); ?></h3>
                                <p class="agro-product-price">LKR <?php echo htmlspecialchars(number_format((float) $prod['price'], 2)); ?></p>
                                <?php if ($stock === 'out_of_stock'): ?>
                                    <span class="agro-btn agro-btn-outline agro-btn-disabled" aria-disabled="true">Unavailable</span>
                                <?php elseif ($stock === 'pre_order'): ?>
                                    <a class="agro-btn agro-btn-outline" href="<?php echo BASE_URL; ?>/contact.php">Pre-order</a>
                                <?php else: ?>
                                    <a class="agro-btn agro-btn-outline" href="<?php echo BASE_URL; ?>/contact.php">Order Now</a>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- How it works -->
    <section class="agro-process-section">
        <div class="container">
            <div class="section-heading agro-section-heading reveal-on-scroll">
                <h2>How It Works</h2>
                <p>Simple steps from discovery to delivery.</p>
            </div>
            <div class="agro-process-grid">
                <article class="agro-process-step reveal-on-scroll" style="--delay: 0s;">
                    <span class="agro-step-num">01</span>
                    <h3>Browse Products</h3>
                    <p>Explore our categories and featured items to find what fits your needs.</p>
                </article>
                <article class="agro-process-step reveal-on-scroll" style="--delay: 0.1s;">
                    <span class="agro-step-num">02</span>
                    <h3>Place Your Order</h3>
                    <p>Reach out with quantities and specifications — we confirm availability quickly.</p>
                </article>
                <article class="agro-process-step reveal-on-scroll" style="--delay: 0.2s;">
                    <span class="agro-step-num">03</span>
                    <h3>Delivery to Your Location</h3>
                    <p>We arrange delivery so your supplies arrive when you need them.</p>
                </article>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="agro-page-cta">
        <div class="container agro-cta-inner reveal-on-scroll">
            <h2>Need Bulk Agro Supplies?</h2>
            <p>Contact us for large-scale orders and custom requirements.</p>
            <a class="agro-btn agro-btn-primary agro-cta-btn" href="<?php echo BASE_URL; ?>/contact.php">Contact Us</a>
        </div>
    </section>
</main>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
