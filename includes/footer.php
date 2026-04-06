<?php
/**
 * Reusable footer include.
 */
?>
<footer class="site-footer">
    <div class="container footer-main">
        <div class="footer-grid">
            <div class="footer-col">
                <a class="footer-brand" href="<?php echo BASE_URL; ?>/index.php">
                    <?php
                    $logoFile = dirname(__DIR__) . '/assets/images/logos/main.jpeg';
                    ?>
                    <?php if (file_exists($logoFile)): ?>
                        <img src="<?php echo BASE_URL; ?>/assets/images/logos/main.jpeg" alt="Nexora Group Holdings logo">
                    <?php else: ?>
                        <span class="footer-logo-fallback">N</span>
                    <?php endif; ?>
                    <span>Nexora Group Holdings</span>
                </a>
                <p class="footer-description">Nexora Group Holdings delivers innovative solutions across digital services, printing, and agriculture.</p>
            </div>

            <div class="footer-col">
                <h4>Quick Links</h4>
                <ul class="footer-links">
                    <li><a href="<?php echo BASE_URL; ?>/index.php">Home</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/pages/digital.php">Nexora Digital</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/pages/agro.php">Nexora Agro</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/pages/printing.php">Nexora Printing</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/contact.php">Contact</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Contact Details</h4>
                <ul class="footer-contact">
                    <li>Colombo, Sri Lanka</li>
                    <li><a href="tel:+94771234567">+94 77 123 4567</a></li>
                    <li><a href="mailto:info@nexora.lk">info@nexora.lk</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Social Media</h4>
                <div class="footer-social">
                    <a href="#" aria-label="Facebook"><span>f</span> Facebook</a>
                    <a href="#" aria-label="Instagram"><span>ig</span> Instagram</a>
                    <a href="#" aria-label="TikTok"><span>tt</span> TikTok</a>
                    <a href="#" aria-label="YouTube"><span>yt</span> YouTube</a>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; 2026 Nexora Group Holdings. All rights reserved.</p>
            <p>Website powered by <a href="https://infersioai.com" target="_blank" rel="noopener noreferrer">infersioai.com</a></p>
        </div>
    </div>
</footer>

<script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>
</body>
</html>

