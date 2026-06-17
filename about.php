<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'About Us';

$aboutImagePath = __DIR__ . '/assets/images/about.jpg';

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<main class="page-main about-page">
    <!-- Hero -->
    <section class="about-page-hero">
        <div class="container about-hero-inner reveal-on-scroll">
            <p class="about-hero-label">NEXORA GROUP HOLDINGS</p>
            <h1>About Nexora</h1>
            <h2 class="about-hero-subtitle">Building Innovation Across Industries</h2>
            <p class="about-hero-desc">Nexora Group Holdings is a multi-sector company delivering excellence in digital services, printing solutions, and agriculture.</p>
        </div>
    </section>

    <!-- Company overview -->
    <section class="about-overview-section">
        <div class="container about-overview-grid">
            <div class="about-overview-content reveal-on-scroll reveal-left">
                <h2>Who We Are</h2>
                <p>Founded in 2026, Nexora Group Holdings is dedicated to delivering high-quality solutions across multiple industries. With a strong focus on innovation and customer satisfaction, we continue to grow and expand our services to meet modern business needs.</p>
            </div>
            <div class="about-overview-media reveal-on-scroll reveal-right">
                <?php if (file_exists($aboutImagePath)): ?>
                    <img src="<?php echo BASE_URL; ?>/assets/images/about.jpg" alt="Nexora Group Holdings team and workspace">
                <?php else: ?>
                    <div class="about-image-fallback">Add assets/images/about.jpg</div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Company details -->
    <section class="about-details-section">
        <div class="container">
            <div class="about-details-grid">
                <article class="about-detail-card reveal-on-scroll" style="--delay: 0s;">
                    <h3>Founded</h3>
                    <p class="about-detail-value">2026</p>
                </article>
                <article class="about-detail-card reveal-on-scroll" style="--delay: 0.08s;">
                    <h3>Founder</h3>
                    <p class="about-detail-value">Amila S Wijethunga</p>
                </article>
                <article class="about-detail-card about-detail-card-wide reveal-on-scroll" style="--delay: 0.16s;">
                    <h3>Services</h3>
                    <ul class="about-detail-list">
                        <li>Nexora Digital</li>
                        <li>Nexora Printing Solutions</li>
                        <li>Nexora Agro</li>
                    </ul>
                </article>
            </div>
        </div>
    </section>

    <!-- Mission & Vision -->
    <section class="about-mission-section">
        <div class="container about-mission-grid">
            <article class="about-mv-card reveal-on-scroll reveal-left" style="--delay: 0.05s;">
                <h2>Our Mission</h2>
                <p>To provide innovative, reliable, and high-quality solutions that empower businesses and individuals across digital, printing, and agricultural sectors.</p>
            </article>
            <article class="about-mv-card reveal-on-scroll reveal-right" style="--delay: 0.12s;">
                <h2>Our Vision</h2>
                <p>To become a leading multi-industry brand recognized for excellence, innovation, and customer satisfaction.</p>
            </article>
        </div>
    </section>

    <!-- Why Choose (same card style as homepage) -->
    <section class="why-choose-section about-why-section">
        <div class="container">
            <div class="section-heading why-choose-heading reveal-on-scroll">
                <h2>Why Choose Nexora</h2>
                <p>The principles that guide how we work with every client and partner.</p>
            </div>

            <div class="why-choose-grid">
                <article class="why-card reveal-on-scroll" style="--delay: 0s;">
                    <div class="why-icon" aria-hidden="true"></div>
                    <h3>High Quality Services</h3>
                    <p>We hold every division to rigorous standards so you receive work you can trust.</p>
                </article>

                <article class="why-card reveal-on-scroll" style="--delay: 0.12s;">
                    <div class="why-icon" aria-hidden="true"></div>
                    <h3>Reliable Delivery</h3>
                    <p>Clear timelines and dependable execution across digital, print, and agro offerings.</p>
                </article>

                <article class="why-card reveal-on-scroll" style="--delay: 0.24s;">
                    <div class="why-icon" aria-hidden="true"></div>
                    <h3>Customer-Centered Approach</h3>
                    <p>Your goals shape our process — we listen, adapt, and follow through.</p>
                </article>

                <article class="why-card reveal-on-scroll" style="--delay: 0.36s;">
                    <div class="why-icon" aria-hidden="true"></div>
                    <h3>Innovative Solutions</h3>
                    <p>We embrace modern tools and ideas to keep your business moving forward.</p>
                </article>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="about-page-cta">
        <div class="container about-cta-inner reveal-on-scroll">
            <h2>Let&rsquo;s Work Together</h2>
            <p>Partner with Nexora to bring your ideas to life.</p>
            <a class="btn-primary about-cta-btn" href="<?php echo htmlspecialchars(nexora_contact_href()); ?>" target="_blank" rel="noopener noreferrer">Contact Us</a>
        </div>
    </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
