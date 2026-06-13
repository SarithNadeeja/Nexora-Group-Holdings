<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Home';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<main>
    <!-- Cinematic Hero Section -->
    <section class="hero-cinematic" id="homeHero">
        <?php
        $bannerVideoPath = __DIR__ . '/assets/videos/banner.mp4';
        $bannerPosterPath = __DIR__ . '/assets/images/hero-poster.jpg';
        $hasBannerVideo = is_file($bannerVideoPath);
        $bannerPosterUrl = is_file($bannerPosterPath) ? nexora_url('assets/images/hero-poster.jpg') : '';
        ?>
        <div class="hero-video-wrap" aria-hidden="true">
            <?php if ($hasBannerVideo): ?>
            <video class="hero-video" autoplay loop muted playsinline preload="metadata"<?php echo $bannerPosterUrl !== '' ? ' poster="' . htmlspecialchars($bannerPosterUrl, ENT_QUOTES, 'UTF-8') . '"' : ''; ?>>
                <source src="<?php echo nexora_url('assets/videos/banner.mp4'); ?>" type="video/mp4">
            </video>
            <?php endif; ?>
            <div class="hero-overlay"></div>
        </div>

        <div class="hero-content">
            <div class="hero-slides" id="heroSlides">
                <article class="hero-slide active">
                    <p class="hero-label">NEXORA DIGITAL</p>
                    <h1>Transforming Brands in the Digital World</h1>
                    <p class="hero-description">From photography and videography to social media management and brand promotion, we create powerful digital experiences.</p>
                    <a class="btn-primary" href="<?php echo BASE_URL; ?>/pages/digital.php">Explore Services</a>
                </article>

                <article class="hero-slide">
                    <p class="hero-label">NEXORA PRINTING</p>
                    <h1>Premium Print Quality</h1>
                    <p class="hero-description">High-quality document, book, and custom printing solutions with fast turnaround and attention to detail.</p>
                    <a class="btn-primary" href="<?php echo BASE_URL; ?>/pages/printing.php">Explore Services</a>
                </article>

                <article class="hero-slide">
                    <p class="hero-label">NEXORA AGRO</p>
                    <h1>Fresh Agro Products from Trusted Sources</h1>
                    <p class="hero-description">Providing fresh, reliable, and sustainable agricultural products directly from farms to your needs.</p>
                    <a class="btn-primary" href="<?php echo BASE_URL; ?>/pages/agro.php">Explore Services</a>
                </article>
            </div>
        </div>

        <a class="scroll-indicator" href="#servicesSection" aria-label="Scroll down">
            <span></span>
        </a>
    </section>

    <!-- Our Services Section -->
    <section class="services-section" id="servicesSection">
        <div class="container">
            <div class="section-heading">
                <h2>Our Services</h2>
                <p>Delivering excellence across digital, printing, and agricultural solutions</p>
            </div>

            <div class="services-grid">
                <article class="service-card reveal-on-scroll" style="--delay: 0s;">
                    <div class="card-accent"></div>
                    <h3>Nexora Digital</h3>
                    <p class="service-description">Creative and strategic digital services to grow your brand presence.</p>
                    <ul class="service-list">
                        <li>Photography</li>
                        <li>Videography</li>
                        <li>Video Creation</li>
                        <li>Graphic Design</li>
                        <li>Social Media Management</li>
                        <li>Brand Promotion</li>
                    </ul>
                </article>

                <article class="service-card reveal-on-scroll" style="--delay: 0.15s;">
                    <div class="card-accent"></div>
                    <h3>Nexora Printing</h3>
                    <p class="service-description">Professional printing solutions with precision, quality, and reliability.</p>
                    <ul class="service-list">
                        <li>Document Printing</li>
                        <li>Book Printing</li>
                        <li>Binding Services</li>
                        <li>Bulk Printing</li>
                        <li>Custom Print Orders</li>
                    </ul>
                </article>

                <article class="service-card service-card-agro reveal-on-scroll" style="--delay: 0.3s;">
                    <div class="card-accent"></div>
                    <h3>Nexora Agro</h3>
                    <p class="service-description">Providing fresh and high-quality agricultural products with trusted sourcing.</p>
                    <ul class="service-list">
                        <li>Fresh Vegetables</li>
                        <li>Fruits</li>
                        <li>Organic Products</li>
                        <li>Bulk Supply</li>
                        <li>Farm Distribution</li>
                    </ul>
                </article>
            </div>

        </div>
    </section>

    <!-- Nexora Digital Detailed Section -->
    <section class="digital-detail-section" id="digitalDetail">
        <div class="container">
            <div class="digital-detail-grid">
                <div class="digital-media reveal-on-scroll reveal-left" style="--delay: 0.05s;">
                    <?php
                    $digitalImage = __DIR__ . '/assets/images/digital.jpg';
                    ?>
                    <?php if (file_exists($digitalImage)): ?>
                        <img src="<?php echo BASE_URL; ?>/assets/images/digital.jpg" alt="Nexora Digital services preview">
                    <?php else: ?>
                        <div class="digital-image-fallback">Add assets/images/digital.jpg</div>
                    <?php endif; ?>
                </div>

                <div class="digital-content reveal-on-scroll reveal-right" style="--delay: 0.15s;">
                    <p class="digital-label">NEXORA DIGITAL</p>
                    <h2>Elevate Your Brand with Digital Excellence</h2>
                    <p class="digital-description">We provide creative and strategic digital solutions to help your brand stand out in a competitive market. From content creation to full-scale brand promotion, we deliver impactful results.</p>

                    <ul class="digital-service-chips">
                        <li>Photography</li>
                        <li>Videography</li>
                        <li>Video Creation</li>
                        <li>Graphic Design</li>
                        <li>Social Media Management</li>
                        <li>Brand Promotion</li>
                    </ul>

                    <a class="btn-primary" href="<?php echo BASE_URL; ?>/pages/digital.php">Explore Digital Services</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Nexora Printing Detailed Section -->
    <section class="printing-detail-section" id="printingDetail">
        <div class="container">
            <div class="printing-detail-grid">
                <div class="printing-content reveal-on-scroll reveal-left" style="--delay: 0.05s;">
                    <p class="digital-label">NEXORA PRINTING</p>
                    <h2>Professional Printing with Precision and Quality</h2>
                    <p class="digital-description">We offer high-quality printing solutions tailored to your needs, ensuring sharp results, durable materials, and timely delivery for every order.</p>

                    <ul class="digital-service-chips">
                        <li>Document Printing</li>
                        <li>Book Printing</li>
                        <li>Binding Services</li>
                        <li>Bulk Printing</li>
                        <li>Custom Print Orders</li>
                    </ul>

                    <a class="btn-primary" href="<?php echo BASE_URL; ?>/pages/printing.php">Explore Printing Services</a>
                </div>

                <div class="printing-media reveal-on-scroll reveal-right" style="--delay: 0.15s;">
                    <?php
                    $printingImage = __DIR__ . '/assets/images/printing.jpg';
                    ?>
                    <?php if (file_exists($printingImage)): ?>
                        <img src="<?php echo BASE_URL; ?>/assets/images/printing.jpg" alt="Nexora Printing services preview">
                    <?php else: ?>
                        <div class="digital-image-fallback">Add assets/images/printing.jpg</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Nexora Agro Detailed Product Section -->
    <section class="agro-detail-section" id="agroDetail">
        <div class="container">
            <div class="printing-detail-grid">
                <div class="printing-media reveal-on-scroll reveal-left" style="--delay: 0.05s;">
                    <?php
                    $agroImage = __DIR__ . '/assets/images/agro.jpg';
                    ?>
                    <?php if (file_exists($agroImage)): ?>
                        <img src="<?php echo BASE_URL; ?>/assets/images/agro.jpg" alt="Nexora Agro product showcase">
                    <?php else: ?>
                        <div class="digital-image-fallback">Add assets/images/agro.jpg</div>
                    <?php endif; ?>
                </div>

                <div class="printing-content agro-content reveal-on-scroll reveal-right" style="--delay: 0.15s;">
                    <p class="digital-label agro-label">NEXORA AGRO</p>
                    <h2>Fresh Agricultural Products &amp; Growing Essentials</h2>
                    <p class="digital-description">We provide a wide range of agricultural products including plants, seeds, and essential growing materials to support both home gardening and large-scale cultivation.</p>

                    <ul class="digital-service-chips agro-service-chips">
                        <li>Plant Covers</li>
                        <li>Fertilizers &amp; Plant Protection Products</li>
                        <li>Indoor &amp; Decorative Plants</li>
                        <li>Flower Pots &amp; Planters</li>
                        <li>Seeds for Cultivation</li>
                    </ul>

                    <a class="btn-primary" href="<?php echo BASE_URL; ?>/pages/agro.php">Explore Agro Products</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Premium Call To Action Section -->
    <section class="cta-section" id="contactCta">
        <div class="cta-watermark" aria-hidden="true">NEXORA</div>
        <div class="container cta-inner">
            <div class="cta-content reveal-on-scroll" style="--delay: 0.05s;">
                <h2>Ready to Get Started with Nexora?</h2>
                <p>Let&rsquo;s bring your ideas to life with our digital, printing, and agro solutions.</p>
                <a class="btn-cta" href="<?php echo htmlspecialchars(nexora_contact_href()); ?>" target="_blank" rel="noopener noreferrer">Contact Us</a>
            </div>
        </div>
    </section>

    <!-- Why Choose Us Section -->
    <section class="why-choose-section" id="whyChooseSection">
        <div class="container">
            <div class="section-heading why-choose-heading">
                <h2>Why Choose Nexora</h2>
                <p>Delivering quality, reliability, and innovation across every service we provide.</p>
            </div>

            <div class="why-choose-grid">
                <article class="why-card reveal-on-scroll" style="--delay: 0s;">
                    <div class="why-icon" aria-hidden="true"></div>
                    <h3>High Quality Standards</h3>
                    <p>We ensure top-tier quality in every project, from digital services to printing and agro products.</p>
                </article>

                <article class="why-card reveal-on-scroll" style="--delay: 0.12s;">
                    <div class="why-icon" aria-hidden="true"></div>
                    <h3>Fast &amp; Reliable Delivery</h3>
                    <p>Our streamlined processes ensure your orders are completed and delivered on time.</p>
                </article>

                <article class="why-card reveal-on-scroll" style="--delay: 0.24s;">
                    <div class="why-icon" aria-hidden="true"></div>
                    <h3>Affordable Pricing</h3>
                    <p>We provide competitive pricing without compromising on quality and service.</p>
                </article>

                <article class="why-card reveal-on-scroll" style="--delay: 0.36s;">
                    <div class="why-icon" aria-hidden="true"></div>
                    <h3>Dedicated Support</h3>
                    <p>Our team is always ready to assist you with personalized support and guidance.</p>
                </article>
            </div>
        </div>
    </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>

