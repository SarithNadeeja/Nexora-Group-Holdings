<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/database.php';
$pageTitle = 'Nexora Digital';

$showcaseImages = [];
$galleryImages = [];
$testimonials = [];
$commentFlash = null;

$pdo = nexora_db_connect();
if ($pdo) {
    try {
        nexora_digital_featured_images_ensure_table($pdo);
        nexora_digital_gallery_images_ensure_table($pdo);
        nexora_digital_client_comments_ensure_table($pdo);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['digital_comment_submit'])) {
            $clientName = isset($_POST['client_name']) ? trim((string) $_POST['client_name']) : '';
            $clientComment = isset($_POST['client_comment']) ? trim((string) $_POST['client_comment']) : '';

            if ($clientName === '' || $clientComment === '') {
                $commentFlash = ['type' => 'error', 'message' => 'Please enter both your name and comment.'];
            } elseif (mb_strlen($clientName) > 120) {
                $commentFlash = ['type' => 'error', 'message' => 'Name is too long (max 120 characters).'];
            } elseif (mb_strlen($clientComment) > 2000) {
                $commentFlash = ['type' => 'error', 'message' => 'Comment is too long (max 2000 characters).'];
            } else {
                $ins = $pdo->prepare('INSERT INTO digital_client_comments (client_name, comment) VALUES (?, ?)');
                $ins->execute([$clientName, $clientComment]);
                header('Location: ' . BASE_URL . '/pages/digital.php?thanks=1#share-feedback');
                exit;
            }
        }

        if (isset($_GET['thanks']) && $_GET['thanks'] === '1') {
            $commentFlash = ['type' => 'success', 'message' => 'Thank you. Your comment has been added.'];
        }

        $stmt = $pdo->query('SELECT image_path FROM digital_featured_images ORDER BY id DESC LIMIT 10');
        $showcaseImages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmtGallery = $pdo->query('SELECT id, image_path FROM digital_gallery_images ORDER BY sort_order DESC, id DESC LIMIT 20');
        $galleryImages = $stmtGallery->fetchAll(PDO::FETCH_ASSOC);

        $stmtT = $pdo->query('SELECT id, client_name, comment, created_at FROM digital_client_comments ORDER BY id DESC LIMIT 50');
        $testimonials = $stmtT->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $showcaseImages = [];
        $galleryImages = [];
        $testimonials = [];
        if ($commentFlash === null) {
            $commentFlash = ['type' => 'error', 'message' => 'Comments are temporarily unavailable.'];
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['digital_comment_submit'])) {
    $commentFlash = ['type' => 'error', 'message' => 'Database is not available. Please try again later.'];
}

include dirname(__DIR__) . '/includes/header.php';
include dirname(__DIR__) . '/includes/navbar.php';
?>

<main class="page-main">
    <!-- Digital Hero -->
    <section class="digital-page-hero">
        <div class="container digital-page-hero-inner reveal-on-scroll">
            <div class="digital-page-hero-text">
                <p class="digital-page-label">NEXORA DIGITAL</p>
                <h1>Nexora Digital</h1>
                <h2>Creative Digital Solutions to Elevate Your Brand</h2>
                <p>We provide photography, videography, content creation, and digital marketing services to help businesses grow and stand out.</p>
            </div>
            <div class="digital-page-hero-media">
                <?php
                $digitalHeroLogo = dirname(__DIR__) . '/assets/images/logos/digital.jpeg';
                ?>
                <?php if (file_exists($digitalHeroLogo)): ?>
                    <img src="<?php echo BASE_URL; ?>/assets/images/logos/digital.jpeg" alt="Nexora Digital hero visual">
                <?php else: ?>
                    <div class="dp-image-fallback">Add assets/images/logos/digital.jpeg</div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- About Digital Services -->
    <section class="digital-about-section">
        <div class="container digital-about-grid">
            <div class="digital-about-content reveal-on-scroll reveal-left">
                <h2>Your Creative Digital Partner</h2>
                <p>We combine creativity and strategy to deliver impactful digital experiences. From capturing stunning visuals to managing your brand presence online, our services are designed to drive results.</p>
            </div>
            <div class="digital-about-media reveal-on-scroll reveal-right">
                <?php
                $digitalImage = dirname(__DIR__) . '/assets/images/digital.jpg';
                ?>
                <?php if (file_exists($digitalImage)): ?>
                    <img src="<?php echo BASE_URL; ?>/assets/images/digital.jpg" alt="Nexora Digital service showcase">
                <?php else: ?>
                    <div class="dp-image-fallback">Add assets/images/digital.jpg</div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Services -->
    <section class="digital-services-section">
        <div class="container">
            <div class="section-heading">
                <h2>Our Digital Services</h2>
                <p>Focused creative services designed for measurable brand growth.</p>
            </div>

            <div class="digital-testimonials-label reveal-on-scroll">
                <h3>What Our Clients Say</h3>
                <p>Real feedback from businesses we have worked with.</p>
            </div>
            <div class="digital-testimonials-carousel reveal-on-scroll" id="digitalTestimonialsCarousel" data-auto-rotate="true" aria-live="polite">
                <?php if (count($testimonials) === 0): ?>
                    <blockquote class="digital-testimonial-slide active">
                        <p class="digital-testimonial-text">Be the first to share your experience with Nexora Digital.</p>
                        <footer class="digital-testimonial-author">&mdash; Your team</footer>
                    </blockquote>
                <?php else: ?>
                    <?php foreach ($testimonials as $ti => $row): ?>
                        <blockquote class="digital-testimonial-slide<?php echo $ti === 0 ? ' active' : ''; ?>">
                            <p class="digital-testimonial-text">&ldquo;<?php echo htmlspecialchars($row['comment']); ?>&rdquo;</p>
                            <footer class="digital-testimonial-author">&mdash; <?php echo htmlspecialchars($row['client_name']); ?></footer>
                        </blockquote>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="digital-services-grid">
                <article class="digital-service-card reveal-on-scroll" style="--delay: 0s;">
                    <div class="digital-service-icon"></div>
                    <h3>Photography</h3>
                    <p>Professional visual storytelling for products, teams, and campaigns.</p>
                    <a class="service-contact-btn" href="<?php echo htmlspecialchars(nexora_contact_href('digital')); ?>" target="_blank" rel="noopener noreferrer">Contact Us</a>
                </article>
                <article class="digital-service-card reveal-on-scroll" style="--delay: 0.08s;">
                    <div class="digital-service-icon"></div>
                    <h3>Videography</h3>
                    <p>High-quality video coverage tailored for modern business communication.</p>
                    <a class="service-contact-btn" href="<?php echo htmlspecialchars(nexora_contact_href('digital')); ?>" target="_blank" rel="noopener noreferrer">Contact Us</a>
                </article>
                <article class="digital-service-card reveal-on-scroll" style="--delay: 0.16s;">
                    <div class="digital-service-icon"></div>
                    <h3>Video Creation</h3>
                    <p>Creative edits and branded content that connect with your audience.</p>
                    <a class="service-contact-btn" href="<?php echo htmlspecialchars(nexora_contact_href('digital')); ?>" target="_blank" rel="noopener noreferrer">Contact Us</a>
                </article>
                <article class="digital-service-card reveal-on-scroll" style="--delay: 0.24s;">
                    <div class="digital-service-icon"></div>
                    <h3>Graphic Design</h3>
                    <p>Clean and impactful designs for digital and print marketing assets.</p>
                    <a class="service-contact-btn" href="<?php echo htmlspecialchars(nexora_contact_href('digital')); ?>" target="_blank" rel="noopener noreferrer">Contact Us</a>
                </article>
                <article class="digital-service-card reveal-on-scroll" style="--delay: 0.32s;">
                    <div class="digital-service-icon"></div>
                    <h3>Social Media Management</h3>
                    <p>Structured content planning, posting, and growth-focused engagement.</p>
                    <a class="service-contact-btn" href="<?php echo htmlspecialchars(nexora_contact_href('digital')); ?>" target="_blank" rel="noopener noreferrer">Contact Us</a>
                </article>
                <article class="digital-service-card reveal-on-scroll" style="--delay: 0.4s;">
                    <div class="digital-service-icon"></div>
                    <h3>Brand Promotion</h3>
                    <p>Strategic campaigns that increase visibility and strengthen trust.</p>
                    <a class="service-contact-btn" href="<?php echo htmlspecialchars(nexora_contact_href('digital')); ?>" target="_blank" rel="noopener noreferrer">Contact Us</a>
                </article>
            </div>
        </div>
    </section>

    <!-- Digital Showcase -->
    <section class="digital-showcase-section">
        <div class="container">
            <div class="digital-showcase reveal-on-scroll">
                <div class="digital-showcase-track" data-auto-scroll="true">
                    <?php if (count($showcaseImages) > 0): ?>
                        <?php foreach ($showcaseImages as $image): ?>
                            <div class="digital-showcase-item">
                                <img src="<?php echo BASE_URL . '/' . ltrim($image['image_path'], '/'); ?>" alt="Digital showcase image">
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="digital-showcase-item">
                            <div class="dp-image-fallback">Add images from Admin > Digital Showcase Images</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Digital Gallery -->
    <section class="digital-gallery-section" id="digital-gallery">
        <div class="container">
            <div class="section-heading digital-gallery-heading reveal-on-scroll">
                <p class="digital-page-label">PORTFOLIO</p>
                <h2>Digital Gallery</h2>
                <p>A curated look at our photography, videography, and creative work.</p>
            </div>

            <?php if (count($galleryImages) > 0): ?>
                <div class="digital-gallery-grid reveal-on-scroll" id="digitalGalleryGrid">
                    <?php $galleryDisplayIndex = 0; ?>
                    <?php foreach ($galleryImages as $image): ?>
                        <?php
                        $imagePath = isset($image['image_path']) ? trim((string) $image['image_path']) : '';
                        if ($imagePath === '' || $imagePath === 'pending') {
                            continue;
                        }
                        $imageUrl = BASE_URL . '/' . ltrim($imagePath, '/');
                        $delay = ($galleryDisplayIndex % 8) * 0.05;
                        $wideClass = ($galleryDisplayIndex % 7 === 0) ? ' digital-gallery-item-wide' : '';
                        ?>
                        <button
                            type="button"
                            class="digital-gallery-item<?php echo $wideClass; ?> reveal-on-scroll"
                            style="--delay: <?php echo htmlspecialchars((string) $delay); ?>s;"
                            data-gallery-index="<?php echo (int) $galleryDisplayIndex; ?>"
                            data-gallery-src="<?php echo htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8'); ?>"
                            aria-label="View gallery image <?php echo (int) ($galleryDisplayIndex + 1); ?>"
                        >
                            <img src="<?php echo htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="Nexora Digital gallery image <?php echo (int) ($galleryDisplayIndex + 1); ?>" loading="lazy">
                            <span class="digital-gallery-item-overlay" aria-hidden="true">
                                <span class="digital-gallery-item-icon">+</span>
                            </span>
                        </button>
                        <?php $galleryDisplayIndex++; ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="digital-gallery-empty reveal-on-scroll">
                    <p>Gallery photos will appear here once uploaded from Admin &gt; Digital Gallery.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php if (count($galleryImages) > 0): ?>
    <div class="digital-gallery-lightbox" id="digitalGalleryLightbox" aria-hidden="true" role="dialog" aria-modal="true" aria-label="Gallery image viewer">
        <div class="digital-gallery-lightbox-backdrop" data-gallery-close tabindex="-1"></div>
        <div class="digital-gallery-lightbox-panel">
            <button type="button" class="digital-gallery-lightbox-close" data-gallery-close aria-label="Close gallery">&times;</button>
            <button type="button" class="digital-gallery-lightbox-nav digital-gallery-lightbox-prev" data-gallery-prev aria-label="Previous image">&#10094;</button>
            <figure class="digital-gallery-lightbox-figure">
                <img class="digital-gallery-lightbox-img" id="digitalGalleryLightboxImg" src="" alt="">
                <figcaption class="digital-gallery-lightbox-caption" id="digitalGalleryLightboxCaption"></figcaption>
            </figure>
            <button type="button" class="digital-gallery-lightbox-nav digital-gallery-lightbox-next" data-gallery-next aria-label="Next image">&#10095;</button>
        </div>
    </div>
    <?php endif; ?>

    <!-- Process -->
    <section class="digital-process-section">
        <div class="container">
            <div class="section-heading">
                <h2>How We Work</h2>
                <p>A simple and effective process focused on outcomes.</p>
            </div>
            <div class="digital-process-grid">
                <article class="process-step reveal-on-scroll" style="--delay: 0s;">
                    <span class="process-number">01</span>
                    <h3>Understanding Your Needs</h3>
                </article>
                <article class="process-step reveal-on-scroll" style="--delay: 0.1s;">
                    <span class="process-number">02</span>
                    <h3>Creative Planning &amp; Execution</h3>
                </article>
                <article class="process-step reveal-on-scroll" style="--delay: 0.2s;">
                    <span class="process-number">03</span>
                    <h3>Delivery &amp; Optimization</h3>
                </article>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="digital-page-cta">
        <div class="container cta-inner">
            <div class="cta-content reveal-on-scroll">
                <h2>Let&rsquo;s Build Your Brand Together</h2>
                <a class="btn-cta" href="<?php echo htmlspecialchars(nexora_contact_href('digital')); ?>" target="_blank" rel="noopener noreferrer">Contact Us</a>
            </div>
        </div>
    </section>

    <!-- Share your experience (above footer) -->
    <section class="digital-comment-form-section" id="share-feedback">
        <div class="container">
            <div class="digital-comment-form-card reveal-on-scroll">
                <h2>Share Your Experience</h2>
                <p class="digital-comment-form-intro">Tell others what you think about Nexora Digital. Your name and comment may appear in &ldquo;What Our Clients Say&rdquo; above.</p>

                <?php if ($commentFlash !== null): ?>
                    <div class="digital-comment-flash digital-comment-flash-<?php echo htmlspecialchars($commentFlash['type']); ?>">
                        <?php echo htmlspecialchars($commentFlash['message']); ?>
                    </div>
                <?php endif; ?>

                <?php if ($pdo): ?>
                    <form class="digital-comment-form" method="post" action="<?php echo BASE_URL; ?>/pages/digital.php#share-feedback">
                        <label for="client_name">Name</label>
                        <input type="text" id="client_name" name="client_name" maxlength="120" required value="<?php echo isset($_POST['client_name']) ? htmlspecialchars((string) $_POST['client_name']) : ''; ?>">

                        <label for="client_comment">Comment</label>
                        <textarea id="client_comment" name="client_comment" rows="4" maxlength="2000" required><?php echo isset($_POST['client_comment']) ? htmlspecialchars((string) $_POST['client_comment']) : ''; ?></textarea>

                        <button type="submit" name="digital_comment_submit" value="1" class="btn-primary">Submit Comment</button>
                    </form>
                <?php else: ?>
                    <p class="digital-comment-unavailable">Comment submission is unavailable while the database is offline.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>

