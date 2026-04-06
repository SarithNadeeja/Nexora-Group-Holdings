/**
 * Nexora starter interactions.
 * Keeps JavaScript minimal and framework-free.
 */
(function () {
    var siteHeader = document.querySelector('.site-header');
    var homeHero = document.getElementById('homeHero');
    var menuToggle = document.getElementById('menuToggle');
    var mainNav = document.getElementById('mainNav');
    var heroSlides = document.querySelectorAll('#heroSlides .hero-slide');
    var revealElements = document.querySelectorAll('.reveal-on-scroll');
    var serviceCards = document.querySelectorAll('.services-grid .service-card, .digital-services-grid .digital-service-card, .print-doc-grid .print-doc-card, .agro-category-card, .why-card');
    var showcaseTrack = document.querySelector('.digital-showcase-track[data-auto-scroll="true"]');
    var testimonialCarousel = document.getElementById('digitalTestimonialsCarousel');
    var contactPageForm = document.querySelector('.contact-page-form');
    var agroProductCards = document.querySelectorAll('.agro-product-card');
    var slideIndex = 0;

    function syncHeaderTheme() {
        if (!siteHeader || !homeHero) {
            return;
        }

        if (window.scrollY < 40) {
            siteHeader.classList.add('header-over-hero');
            siteHeader.classList.remove('header-scrolled');
        } else {
            siteHeader.classList.remove('header-over-hero');
            siteHeader.classList.add('header-scrolled');
        }
    }

    if (menuToggle && mainNav) {
        menuToggle.addEventListener('click', function () {
            mainNav.classList.toggle('active');
        });
    }

    if (siteHeader && homeHero) {
        syncHeaderTheme();
        window.addEventListener('scroll', syncHeaderTheme, { passive: true });
    }

    if (heroSlides.length > 1) {
        setInterval(function () {
            heroSlides[slideIndex].classList.remove('active');
            slideIndex = (slideIndex + 1) % heroSlides.length;
            heroSlides[slideIndex].classList.add('active');
        }, 4000);
    }

    if (testimonialCarousel && testimonialCarousel.getAttribute('data-auto-rotate') === 'true') {
        var testimonialSlides = testimonialCarousel.querySelectorAll('.digital-testimonial-slide');
        var testimonialIndex = 0;
        if (testimonialSlides.length > 1) {
            setInterval(function () {
                testimonialSlides[testimonialIndex].classList.remove('active');
                testimonialIndex = (testimonialIndex + 1) % testimonialSlides.length;
                testimonialSlides[testimonialIndex].classList.add('active');
            }, 5000);
        }
    }

    if (revealElements.length) {
        var revealObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('revealed');
                } else {
                    entry.target.classList.remove('revealed');
                }
            });
        }, {
            threshold: 0.16
        });

        revealElements.forEach(function (element) {
            revealObserver.observe(element);
        });
    }

    serviceCards.forEach(function (card) {
        card.addEventListener('mousemove', function (event) {
            var rect = card.getBoundingClientRect();
            var x = (event.clientX - rect.left) / rect.width - 0.5;
            var y = (event.clientY - rect.top) / rect.height - 0.5;
            card.style.transform = 'translateY(-10px) scale(1.01) rotateX(' + (-y * 2.2) + 'deg) rotateY(' + (x * 2.2) + 'deg)';
        });

        card.addEventListener('mouseleave', function () {
            card.style.transform = '';
        });
    });

    if (showcaseTrack) {
        var showcaseItems = showcaseTrack.querySelectorAll('.digital-showcase-item');
        var showcaseIndex = 0;
        var visibleCount = window.innerWidth <= 760 ? 1 : 3;

        function updateShowcasePosition() {
            if (!showcaseItems.length) {
                return;
            }
            var itemWidth = showcaseItems[0].getBoundingClientRect().width;
            showcaseTrack.style.transform = 'translateX(' + (-showcaseIndex * itemWidth) + 'px)';
        }

        function cycleShowcase() {
            visibleCount = window.innerWidth <= 760 ? 1 : 3;
            if (showcaseItems.length <= visibleCount) {
                showcaseIndex = 0;
                updateShowcasePosition();
                return;
            }
            showcaseIndex = (showcaseIndex + 1) % (showcaseItems.length - visibleCount + 1);
            updateShowcasePosition();
        }

        updateShowcasePosition();
        setInterval(cycleShowcase, 3000);
        window.addEventListener('resize', function () {
            visibleCount = window.innerWidth <= 760 ? 1 : 3;
            if (showcaseItems.length <= visibleCount) {
                showcaseIndex = 0;
            } else if (showcaseIndex > (showcaseItems.length - visibleCount)) {
                showcaseIndex = 0;
            }
            updateShowcasePosition();
        });
    }

    if (contactPageForm) {
        contactPageForm.addEventListener('submit', function () {
            var submitBtn = contactPageForm.querySelector('button[type="submit"]');
            if (!submitBtn) {
                return;
            }
            submitBtn.disabled = true;
            submitBtn.textContent = 'Sending...';
        });
    }

    agroProductCards.forEach(function (card) {
        var mainImg = card.querySelector('.agro-product-main-img');
        if (!mainImg) {
            return;
        }
        card.querySelectorAll('.agro-product-thumb').forEach(function (thumb) {
            thumb.addEventListener('click', function () {
                var src = thumb.getAttribute('data-src');
                if (src) {
                    mainImg.src = src;
                }
            });
        });
    });
})();

