<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Contact Us';

$errors = [];
$formData = [
    'full_name' => '',
    'email' => '',
    'phone' => '',
    'message' => '',
];
$successMessage = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
    $formData['full_name'] = isset($_POST['full_name']) ? trim((string) $_POST['full_name']) : '';
    $formData['email'] = isset($_POST['email']) ? trim((string) $_POST['email']) : '';
    $formData['phone'] = isset($_POST['phone']) ? trim((string) $_POST['phone']) : '';
    $formData['message'] = isset($_POST['message']) ? trim((string) $_POST['message']) : '';

    if ($formData['full_name'] === '' || mb_strlen($formData['full_name']) < 2) {
        $errors[] = 'Please enter your full name.';
    } elseif (mb_strlen($formData['full_name']) > 120) {
        $errors[] = 'Full name is too long (max 120 characters).';
    }

    if (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    } elseif (mb_strlen($formData['email']) > 180) {
        $errors[] = 'Email is too long.';
    }

    if ($formData['phone'] === '') {
        $errors[] = 'Please enter your phone number.';
    } elseif (!preg_match('/^[0-9+()\\-\\s]{7,30}$/', $formData['phone'])) {
        $errors[] = 'Please enter a valid phone number.';
    }

    if ($formData['message'] === '' || mb_strlen($formData['message']) < 10) {
        $errors[] = 'Please enter a message (at least 10 characters).';
    } elseif (mb_strlen($formData['message']) > 4000) {
        $errors[] = 'Message is too long (max 4000 characters).';
    }

    if (!$errors) {
        // Placeholder success flow. Replace with mail() or database storage when ready.
        header('Location: ' . BASE_URL . '/contact.php?sent=1#contactForm');
        exit;
    }
}

if (isset($_GET['sent']) && $_GET['sent'] === '1') {
    $successMessage = 'Thank you. Your message has been sent successfully.';
}

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<main class="page-main contact-page">
    <!-- Hero -->
    <section class="contact-page-hero">
        <div class="container contact-hero-inner reveal-on-scroll">
            <h1>Contact Us</h1>
            <h2>Get in touch with Nexora</h2>
            <p>We&rsquo;re here to help you with digital services, printing, and agro solutions.</p>
        </div>
    </section>

    <!-- Main contact section -->
    <section class="contact-main-section" id="contactForm">
        <div class="container contact-main-grid">
            <div class="contact-main-info reveal-on-scroll reveal-left">
                <h2>Get In Touch</h2>
                <p>Have a question or need a service? Reach out to us and our team will get back to you as soon as possible.</p>

                <ul class="contact-detail-list">
                    <li>
                        <span class="contact-detail-icon" aria-hidden="true">📍</span>
                        <div><strong>Address:</strong> Colombo, Sri Lanka</div>
                    </li>
                    <li>
                        <span class="contact-detail-icon" aria-hidden="true">📞</span>
                        <div><strong>Phone:</strong> <a href="tel:+94771234567">+94 77 123 4567</a></div>
                    </li>
                    <li>
                        <span class="contact-detail-icon" aria-hidden="true">✉️</span>
                        <div><strong>Email:</strong> <a href="mailto:info@nexora.lk">info@nexora.lk</a></div>
                    </li>
                </ul>
            </div>

            <div class="contact-main-form-wrap reveal-on-scroll reveal-right">
                <?php if ($successMessage !== null): ?>
                    <div class="contact-flash contact-flash-success"><?php echo htmlspecialchars($successMessage); ?></div>
                <?php endif; ?>

                <?php if ($errors): ?>
                    <div class="contact-flash contact-flash-error">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form class="contact-page-form" method="post" action="<?php echo BASE_URL; ?>/contact.php#contactForm">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" maxlength="120" required value="<?php echo htmlspecialchars($formData['full_name']); ?>">

                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" maxlength="180" required value="<?php echo htmlspecialchars($formData['email']); ?>">

                    <label for="phone">Phone Number</label>
                    <input type="text" id="phone" name="phone" maxlength="30" required value="<?php echo htmlspecialchars($formData['phone']); ?>">

                    <label for="message">Message</label>
                    <textarea id="message" name="message" rows="5" maxlength="4000" required><?php echo htmlspecialchars($formData['message']); ?></textarea>

                    <button type="submit" class="btn-primary" name="contact_submit" value="1">Send Message</button>
                </form>
            </div>
        </div>
    </section>

    <!-- Map -->
    <section class="contact-map-section">
        <div class="container reveal-on-scroll">
            <div class="contact-map-frame">
                <iframe
                    title="Nexora location map"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    src="https://www.google.com/maps?q=Colombo%2C%20Sri%20Lanka&output=embed"></iframe>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="contact-page-cta">
        <div class="container contact-cta-inner reveal-on-scroll">
            <h2>Ready to Start Your Project?</h2>
            <a class="btn-primary" href="<?php echo BASE_URL; ?>/contact.php#contactForm">Contact Us</a>
        </div>
    </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>

