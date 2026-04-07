<?php
/**
 * Reusable footer include.
 */
require_once __DIR__ . '/division_contacts.php';
$footerDivisionContacts = nexora_division_contacts_all();
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
                <h4>Contact</h4>
                <p class="footer-contact-address">Colombo, Sri Lanka</p>
                <ul class="footer-contact footer-contact-divisions">
                    <li class="footer-contact-division">
                        <strong>Digital</strong>
                        <?php if (trim($footerDivisionContacts['digital']['phone']) !== ''): ?>
                            <a href="<?php echo htmlspecialchars(nexora_phone_tel_href($footerDivisionContacts['digital']['phone'])); ?>"><?php echo htmlspecialchars($footerDivisionContacts['digital']['phone']); ?></a>
                        <?php else: ?>
                            <span class="footer-contact-missing">&mdash;</span>
                        <?php endif; ?>
                        <span class="footer-contact-sep">&middot;</span>
                        <?php if (trim($footerDivisionContacts['digital']['email']) !== ''): ?>
                            <a href="mailto:<?php echo htmlspecialchars($footerDivisionContacts['digital']['email'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($footerDivisionContacts['digital']['email']); ?></a>
                        <?php else: ?>
                            <span class="footer-contact-missing">&mdash;</span>
                        <?php endif; ?>
                    </li>
                    <li class="footer-contact-division">
                        <strong>Agro</strong>
                        <?php if (trim($footerDivisionContacts['agro']['phone']) !== ''): ?>
                            <a href="<?php echo htmlspecialchars(nexora_phone_tel_href($footerDivisionContacts['agro']['phone'])); ?>"><?php echo htmlspecialchars($footerDivisionContacts['agro']['phone']); ?></a>
                        <?php else: ?>
                            <span class="footer-contact-missing">&mdash;</span>
                        <?php endif; ?>
                        <span class="footer-contact-sep">&middot;</span>
                        <?php if (trim($footerDivisionContacts['agro']['email']) !== ''): ?>
                            <a href="mailto:<?php echo htmlspecialchars($footerDivisionContacts['agro']['email'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($footerDivisionContacts['agro']['email']); ?></a>
                        <?php else: ?>
                            <span class="footer-contact-missing">&mdash;</span>
                        <?php endif; ?>
                    </li>
                    <li class="footer-contact-division">
                        <strong>Printing</strong>
                        <?php if (trim($footerDivisionContacts['printing']['phone']) !== ''): ?>
                            <a href="<?php echo htmlspecialchars(nexora_phone_tel_href($footerDivisionContacts['printing']['phone'])); ?>"><?php echo htmlspecialchars($footerDivisionContacts['printing']['phone']); ?></a>
                        <?php else: ?>
                            <span class="footer-contact-missing">&mdash;</span>
                        <?php endif; ?>
                        <span class="footer-contact-sep">&middot;</span>
                        <?php if (trim($footerDivisionContacts['printing']['email']) !== ''): ?>
                            <a href="mailto:<?php echo htmlspecialchars($footerDivisionContacts['printing']['email'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($footerDivisionContacts['printing']['email']); ?></a>
                        <?php else: ?>
                            <span class="footer-contact-missing">&mdash;</span>
                        <?php endif; ?>
                    </li>
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

