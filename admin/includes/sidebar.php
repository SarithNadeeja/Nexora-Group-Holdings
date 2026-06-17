<button type="button" class="admin-menu-toggle" id="adminMenuToggle" aria-controls="adminSidebar" aria-expanded="false" aria-label="Open admin menu">Menu</button>
<div class="admin-sidebar-overlay" id="adminSidebarOverlay" aria-hidden="true"></div>
<aside class="admin-sidebar" id="adminSidebar">
    <div class="admin-sidebar-head">
        <h2 class="admin-sidebar-title">Nexora Admin</h2>
        <button type="button" class="admin-sidebar-close" id="adminSidebarClose" aria-label="Close menu">&times;</button>
    </div>
    <nav class="admin-sidebar-nav">
        <a href="dashboard.php">Dashboard</a>
        <a href="add-document.php">Add New Document</a>
        <a href="printing-samples.php">Print Samples</a>
        <a href="digital-showcase.php">Digital Showcase Images</a>
        <a href="digital-gallery.php">Digital Gallery</a>
        <a href="agro-shop.php">Agro Shop Items</a>
        <a href="agro-orders.php">Agro Orders</a>
        <a href="printing-orders.php">Printing Orders</a>
        <a href="contact-details.php">Contact Details</a>
        <a href="blog-posts.php">SEO Blog Posts</a>
        <a href="admin-accounts.php">Admin accounts</a>
        <a href="logout.php" class="admin-nav-logout">Logout</a>
    </nav>
</aside>
<script>
(function () {
    var toggle = document.getElementById('adminMenuToggle');
    var closeBtn = document.getElementById('adminSidebarClose');
    var sidebar = document.getElementById('adminSidebar');
    var overlay = document.getElementById('adminSidebarOverlay');
    if (!toggle || !sidebar || !overlay) {
        return;
    }

    function setOpen(open) {
        sidebar.classList.toggle('is-open', open);
        overlay.classList.toggle('is-visible', open);
        document.body.classList.toggle('admin-nav-open', open);
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        overlay.setAttribute('aria-hidden', open ? 'false' : 'true');
    }

    toggle.addEventListener('click', function () {
        setOpen(!sidebar.classList.contains('is-open'));
    });
    if (closeBtn) {
        closeBtn.addEventListener('click', function () {
            setOpen(false);
        });
    }
    overlay.addEventListener('click', function () {
        setOpen(false);
    });
    sidebar.querySelectorAll('a').forEach(function (link) {
        link.addEventListener('click', function () {
            if (window.matchMedia('(max-width: 900px)').matches) {
                setOpen(false);
            }
        });
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            setOpen(false);
        }
    });
})();
</script>
