<?php
/**
 * Reusable document header include.
 */
if (!defined('SITE_NAME')) {
    require_once __DIR__ . '/config.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo isset($metaDescription) ? htmlspecialchars($metaDescription) : 'Nexora Group Holdings - Modern corporate services across digital, agro, and printing solutions.'; ?>">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | ' . SITE_NAME : SITE_NAME; ?></title>
    <link rel="icon" href="<?php echo nexora_url('assets/images/logos/main.jpeg'); ?>" type="image/jpeg">
    <?php if (!empty($metaOgUrl)): ?>
        <meta property="og:type" content="product">
        <meta property="og:url" content="<?php echo htmlspecialchars($metaOgUrl); ?>">
        <meta property="og:title" content="<?php echo htmlspecialchars(isset($metaOgTitle) ? $metaOgTitle : (isset($pageTitle) ? $pageTitle : SITE_NAME)); ?>">
        <meta property="og:description" content="<?php echo htmlspecialchars(isset($metaOgDescription) ? $metaOgDescription : ''); ?>">
        <?php if (!empty($metaOgImage)): ?>
            <meta property="og:image" content="<?php echo htmlspecialchars($metaOgImage); ?>">
            <meta property="og:image:alt" content="<?php echo htmlspecialchars($metaOgTitle ?? 'Product'); ?>">
        <?php endif; ?>
        <meta name="twitter:card" content="summary_large_image">
    <?php endif; ?>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
</head>
<body>

