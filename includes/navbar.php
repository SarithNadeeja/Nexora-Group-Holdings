<?php
/**
 * Reusable navbar include.
 */
?>
<header class="site-header">
    <div class="container nav-wrap">
        <a class="brand" href="<?php echo BASE_URL; ?>/index.php">
            <?php
            $logoFile = dirname(__DIR__) . '/assets/images/logos/main.jpeg';
            ?>
            <?php if (file_exists($logoFile)): ?>
                <img src="<?php echo BASE_URL; ?>/assets/images/logos/main.jpeg" alt="Nexora Group Holdings logo">
            <?php else: ?>
                <span class="logo-fallback">N</span>
            <?php endif; ?>
            <span class="brand-text">
                <span class="brand-name">Nexora Group Holdings</span>
                <span class="brand-address"><?php echo htmlspecialchars(SITE_ADDRESS); ?></span>
            </span>
        </a>

        <button class="menu-toggle" id="menuToggle" aria-label="Toggle navigation">Menu</button>

        <?php
        $currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
        $basePath = BASE_URL;
        if ($basePath !== '' && strpos($currentPath, $basePath) === 0) {
            $currentPath = substr($currentPath, strlen($basePath));
        }
        $currentPath = '/' . ltrim((string) $currentPath, '/');

        $isActive = static function ($path) use ($currentPath) {
            return $currentPath === $path ? 'active' : '';
        };
        ?>
        <nav class="main-nav" id="mainNav">
            <a class="<?php echo $isActive('/index.php'); ?>" href="<?php echo BASE_URL; ?>/index.php">Home</a>
            <a class="<?php echo $isActive('/pages/digital.php'); ?>" href="<?php echo BASE_URL; ?>/pages/digital.php">Digital</a>
            <a class="<?php echo $isActive('/pages/agro.php'); ?>" href="<?php echo BASE_URL; ?>/pages/agro.php">Agro</a>
            <a class="<?php echo $isActive('/pages/printing.php'); ?>" href="<?php echo BASE_URL; ?>/pages/printing.php">Printing</a>
            <a class="<?php echo $isActive('/about.php'); ?>" href="<?php echo BASE_URL; ?>/about.php">About Us</a>
            <a class="<?php echo $isActive('/contact.php'); ?>" href="<?php echo BASE_URL; ?>/contact.php">Contact</a>
        </nav>
    </div>
</header>

