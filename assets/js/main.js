/**
 * Nexora starter interactions.
 * Keeps JavaScript minimal and framework-free.
 */
(function () {
    var siteHeader = document.querySelector('.site-header');
    var homeHero = document.getElementById('homeHero');
    var heroVideo = document.querySelector('.hero-video');
    var menuToggle = document.getElementById('menuToggle');
    var mainNav = document.getElementById('mainNav');
    var heroSlides = document.querySelectorAll('#heroSlides .hero-slide');
    var revealElements = document.querySelectorAll('.reveal-on-scroll');
    var serviceCards = document.querySelectorAll('.services-grid .service-card, .digital-services-grid .digital-service-card, .print-doc-grid .print-doc-card, .agro-category-card, .why-card');
    var showcaseTrack = document.querySelector('.digital-showcase-track[data-auto-scroll="true"]');
    var testimonialCarousel = document.getElementById('digitalTestimonialsCarousel');
    var contactPageForm = document.querySelector('.contact-page-form');
    var agroGallerySwaps = document.querySelectorAll('.agro-gallery-swap');
    var slideIndex = 0;

    function showHeroVideoFallback() {
        if (homeHero) {
            homeHero.classList.add('hero-video-unavailable');
        }
    }

    if (heroVideo && homeHero) {
        heroVideo.addEventListener('error', showHeroVideoFallback);
        heroVideo.addEventListener('stalled', showHeroVideoFallback);

        var playPromise = heroVideo.play();
        if (playPromise && typeof playPromise.catch === 'function') {
            playPromise.catch(showHeroVideoFallback);
        }

        window.setTimeout(function () {
            if (heroVideo.readyState === 0 && heroVideo.networkState === 3) {
                showHeroVideoFallback();
            }
        }, 10000);
    } else if (homeHero && !heroVideo) {
        showHeroVideoFallback();
    }

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

    agroGallerySwaps.forEach(function (card) {
        var mainImg = card.querySelector('.agro-product-main-img');
        if (!mainImg) {
            return;
        }
        card.querySelectorAll('.agro-product-thumb').forEach(function (thumb) {
            thumb.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var src = thumb.getAttribute('data-src');
                if (src) {
                    mainImg.src = src;
                }
            });
        });
    });

    var agroOrderModal = document.getElementById('agroOrderModal');
    var agroWaPayloadEl = document.getElementById('agroWaOrderPayload');
    var agroWaOpenBtn = document.getElementById('agroWaOrderOpen');
    var agroWaForm = document.getElementById('agroOrderWaForm');
    var agroOrderData = null;

    if (agroWaPayloadEl) {
        try {
            agroOrderData = JSON.parse(agroWaPayloadEl.textContent.trim());
        } catch (ignore) {
            agroOrderData = null;
        }
    }

    function agroOrderModalShow() {
        if (!agroOrderModal) {
            return;
        }
        agroOrderModal.classList.add('is-open');
        agroOrderModal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        var errEl = document.getElementById('agroOrderModalError');
        if (errEl) {
            errEl.hidden = true;
            errEl.textContent = '';
        }
        var firstInput = agroOrderModal.querySelector('input');
        if (firstInput) {
            firstInput.focus();
        }
    }

    function agroOrderModalHide() {
        if (!agroOrderModal) {
            return;
        }
        agroOrderModal.classList.remove('is-open');
        agroOrderModal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    if (agroWaOpenBtn && agroOrderModal) {
        agroWaOpenBtn.addEventListener('click', agroOrderModalShow);
    }

    if (agroOrderModal) {
        agroOrderModal.querySelectorAll('[data-agro-modal-close]').forEach(function (el) {
            el.addEventListener('click', agroOrderModalHide);
        });
    }

    document.addEventListener('keydown', function (ev) {
        if (ev.key === 'Escape' && agroOrderModal && agroOrderModal.classList.contains('is-open')) {
            agroOrderModalHide();
        }
    });

    var printPdfModal = document.getElementById('printPdfModal');
    var printPdfIframe = document.getElementById('printPdfIframe');
    var printPdfPreviewBtns = document.querySelectorAll('.print-pdf-preview-btn');

    function printPdfModalShow(url) {
        if (!printPdfModal || !printPdfIframe) {
            return;
        }
        printPdfIframe.src = url;
        printPdfModal.classList.add('is-open');
        printPdfModal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function printPdfModalHide() {
        if (!printPdfModal || !printPdfIframe) {
            return;
        }
        printPdfModal.classList.remove('is-open');
        printPdfModal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        printPdfIframe.src = 'about:blank';
    }

    printPdfPreviewBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var url = btn.getAttribute('data-pdf-preview-url');
            if (url) {
                printPdfModalShow(url);
            }
        });
    });

    if (printPdfModal) {
        printPdfModal.querySelectorAll('[data-print-pdf-close]').forEach(function (el) {
            el.addEventListener('click', printPdfModalHide);
        });
    }

    document.addEventListener('keydown', function (ev) {
        if (ev.key === 'Escape' && printPdfModal && printPdfModal.classList.contains('is-open')) {
            printPdfModalHide();
        }
    });

    var printOrderModal = document.getElementById('printOrderModal');
    var printOrderPayloadEl = document.getElementById('printOrderPayload');
    var printOrderForm = document.getElementById('printOrderWaForm');
    var printOrderPreviewIframe = document.getElementById('printOrderPreviewIframe');
    var printOrderOpenBtns = document.querySelectorAll('.print-order-open-btn');
    var printOrderData = null;

    if (printOrderPayloadEl) {
        try {
            printOrderData = JSON.parse(printOrderPayloadEl.textContent.trim());
        } catch (ignorePrintOrderPayload) {
            printOrderData = null;
        }
    }

    function printOrderModalShow(documentId, documentPrice, previewUrl) {
        if (!printOrderModal) {
            return;
        }
        var idField = document.getElementById('print_order_document_id');
        var priceField = document.getElementById('print_order_document_price');
        if (idField) {
            idField.value = String(documentId || '');
        }
        if (priceField) {
            priceField.value = String(documentPrice || '');
        }
        if (printOrderPreviewIframe) {
            printOrderPreviewIframe.src = previewUrl || 'about:blank';
        }
        var errEl = document.getElementById('printOrderModalError');
        if (errEl) {
            errEl.hidden = true;
            errEl.textContent = '';
        }
        printOrderModal.classList.add('is-open');
        printOrderModal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function printOrderModalHide() {
        if (!printOrderModal) {
            return;
        }
        printOrderModal.classList.remove('is-open');
        printOrderModal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        if (printOrderPreviewIframe) {
            printOrderPreviewIframe.src = 'about:blank';
        }
    }

    printOrderOpenBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var documentId = Number(btn.getAttribute('data-print-order-id') || 0);
            var documentPrice = Number(btn.getAttribute('data-print-order-price') || 0);
            var previewUrl = btn.getAttribute('data-print-order-preview-url') || '';
            if (documentId > 0) {
                printOrderModalShow(documentId, documentPrice, previewUrl);
            }
        });
    });

    if (printOrderModal) {
        printOrderModal.querySelectorAll('[data-print-order-close]').forEach(function (el) {
            el.addEventListener('click', printOrderModalHide);
        });
    }

    document.addEventListener('keydown', function (ev) {
        if (ev.key === 'Escape' && printOrderModal && printOrderModal.classList.contains('is-open')) {
            printOrderModalHide();
        }
    });

    if (agroWaForm && agroOrderData) {
        agroWaForm.addEventListener('submit', function (ev) {
            ev.preventDefault();
            var errBox = document.getElementById('agroOrderModalError');
            var digits = String(agroOrderData.waDigits || '').replace(/\D/g, '');
            var submitUrl = String(agroOrderData.submitUrl || '');
            if (!digits || digits.length < 8) {
                if (errBox) {
                    errBox.textContent = 'WhatsApp is not configured yet. Please use the Contact page, or ask your administrator to set NEXORA_WHATSAPP_ORDER_NUMBER in config (or the Nexora Agro phone in Admin → Contact Details).';
                    errBox.hidden = false;
                }
                return;
            }
            if (!submitUrl) {
                if (errBox) {
                    errBox.textContent = 'Order endpoint is not configured.';
                    errBox.hidden = false;
                }
                return;
            }

            var cName = (document.getElementById('agro_ord_name').value || '').trim();
            var cPhone = (document.getElementById('agro_ord_phone').value || '').trim();
            var cEmail = (document.getElementById('agro_ord_email').value || '').trim();
            var addr1 = (document.getElementById('agro_ord_addr1').value || '').trim();
            var addr2 = (document.getElementById('agro_ord_addr2').value || '').trim();
            var city = (document.getElementById('agro_ord_city').value || '').trim();
            var province = (document.getElementById('agro_ord_province').value || '').trim();

            if (!cName || !cPhone || !cEmail || !addr1 || !city || !province) {
                if (errBox) {
                    errBox.textContent = 'Please fill in all required fields.';
                    errBox.hidden = false;
                }
                agroWaForm.reportValidity();
                return;
            }
            var submitBtn = agroWaForm.querySelector('button[type="submit"]');
            var originalBtnText = submitBtn ? submitBtn.textContent : '';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Submitting...';
            }
            if (errBox) {
                errBox.hidden = true;
                errBox.textContent = '';
            }

            fetch(submitUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    productId: Number(agroOrderData.productId || 0),
                    productPrice: Number(agroOrderData.productPrice || 0),
                    customerName: cName,
                    customerPhone: cPhone,
                    customerEmail: cEmail,
                    addressLine1: addr1,
                    addressLine2: addr2,
                    city: city,
                    province: province
                })
            }).then(function (res) {
                return res.json().catch(function () {
                    return { ok: false, message: 'Invalid server response.' };
                });
            }).then(function (data) {
                if (!data || !data.ok || !data.waUrl) {
                    throw new Error((data && data.message) ? data.message : 'Could not create order.');
                }
                window.open(data.waUrl, '_blank', 'noopener,noreferrer');
                agroOrderModalHide();
            }).catch(function (err) {
                if (errBox) {
                    errBox.textContent = err && err.message ? err.message : 'Could not submit order right now.';
                    errBox.hidden = false;
                }
            }).finally(function () {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalBtnText;
                }
            });
        });
    }

    if (printOrderForm && printOrderData) {
        printOrderForm.addEventListener('submit', function (ev) {
            ev.preventDefault();
            var errBox = document.getElementById('printOrderModalError');
            var submitUrl = String(printOrderData.submitUrl || '');
            if (!submitUrl) {
                if (errBox) {
                    errBox.textContent = 'Order endpoint is not configured.';
                    errBox.hidden = false;
                }
                return;
            }

            var documentId = Number((document.getElementById('print_order_document_id').value || '').trim());
            var documentPrice = Number((document.getElementById('print_order_document_price').value || '').trim());
            var cName = (document.getElementById('print_ord_name').value || '').trim();
            var cPhone = (document.getElementById('print_ord_phone').value || '').trim();
            var cEmail = (document.getElementById('print_ord_email').value || '').trim();
            var addr1 = (document.getElementById('print_ord_addr1').value || '').trim();
            var addr2 = (document.getElementById('print_ord_addr2').value || '').trim();
            var city = (document.getElementById('print_ord_city').value || '').trim();
            var province = (document.getElementById('print_ord_province').value || '').trim();

            if (!documentId || !cName || !cPhone || !cEmail || !addr1 || !city || !province) {
                if (errBox) {
                    errBox.textContent = 'Please fill in all required fields.';
                    errBox.hidden = false;
                }
                printOrderForm.reportValidity();
                return;
            }

            var submitBtn = printOrderForm.querySelector('button[type="submit"]');
            var originalBtnText = submitBtn ? submitBtn.textContent : '';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Submitting...';
            }
            if (errBox) {
                errBox.hidden = true;
                errBox.textContent = '';
            }

            fetch(submitUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    documentId: documentId,
                    documentPrice: documentPrice,
                    customerName: cName,
                    customerPhone: cPhone,
                    customerEmail: cEmail,
                    addressLine1: addr1,
                    addressLine2: addr2,
                    city: city,
                    province: province
                })
            }).then(function (res) {
                return res.json().catch(function () {
                    return { ok: false, message: 'Invalid server response.' };
                });
            }).then(function (data) {
                if (!data || !data.ok || !data.waUrl) {
                    throw new Error((data && data.message) ? data.message : 'Could not create order.');
                }
                window.open(data.waUrl, '_blank', 'noopener,noreferrer');
                printOrderModalHide();
            }).catch(function (err) {
                if (errBox) {
                    errBox.textContent = err && err.message ? err.message : 'Could not submit order right now.';
                    errBox.hidden = false;
                }
            }).finally(function () {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalBtnText;
                }
            });
        });
    }

    var printCustomOrderModal = document.getElementById('printCustomOrderModal');
    var printCustomOrderForm = document.getElementById('printCustomOrderWaForm');
    var printCustomOrderOpenBtns = document.querySelectorAll('.print-custom-order-open-btn');

    function printCustomOrderModalShow() {
        if (!printCustomOrderModal) {
            return;
        }
        var errEl = document.getElementById('printCustomOrderModalError');
        if (errEl) {
            errEl.hidden = true;
            errEl.textContent = '';
        }
        printCustomOrderModal.classList.add('is-open');
        printCustomOrderModal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function printCustomOrderModalHide() {
        if (!printCustomOrderModal) {
            return;
        }
        printCustomOrderModal.classList.remove('is-open');
        printCustomOrderModal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    printCustomOrderOpenBtns.forEach(function (btn) {
        btn.addEventListener('click', printCustomOrderModalShow);
    });

    if (printCustomOrderModal) {
        printCustomOrderModal.querySelectorAll('[data-print-custom-order-close]').forEach(function (el) {
            el.addEventListener('click', printCustomOrderModalHide);
        });
    }

    document.addEventListener('keydown', function (ev) {
        if (ev.key === 'Escape' && printCustomOrderModal && printCustomOrderModal.classList.contains('is-open')) {
            printCustomOrderModalHide();
        }
    });

    if (printCustomOrderForm && printOrderData) {
        printCustomOrderForm.addEventListener('submit', function (ev) {
            ev.preventDefault();
            var errBox = document.getElementById('printCustomOrderModalError');
            var submitUrl = String(printOrderData.customSubmitUrl || '');
            if (!submitUrl) {
                if (errBox) {
                    errBox.textContent = 'Custom order endpoint is not configured.';
                    errBox.hidden = false;
                }
                return;
            }

            var customRequest = (document.getElementById('print_custom_request').value || '').trim();
            var cName = (document.getElementById('print_custom_name').value || '').trim();
            var cPhone = (document.getElementById('print_custom_phone').value || '').trim();
            var cEmail = (document.getElementById('print_custom_email').value || '').trim();
            var addr1 = (document.getElementById('print_custom_addr1').value || '').trim();
            var addr2 = (document.getElementById('print_custom_addr2').value || '').trim();
            var city = (document.getElementById('print_custom_city').value || '').trim();
            var province = (document.getElementById('print_custom_province').value || '').trim();

            if (!customRequest || !cName || !cPhone || !cEmail || !addr1 || !city || !province) {
                if (errBox) {
                    errBox.textContent = 'Please fill in all required fields.';
                    errBox.hidden = false;
                }
                printCustomOrderForm.reportValidity();
                return;
            }

            var submitBtn = printCustomOrderForm.querySelector('button[type="submit"]');
            var originalBtnText = submitBtn ? submitBtn.textContent : '';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Submitting...';
            }
            if (errBox) {
                errBox.hidden = true;
                errBox.textContent = '';
            }

            fetch(submitUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    customRequest: customRequest,
                    customerName: cName,
                    customerPhone: cPhone,
                    customerEmail: cEmail,
                    addressLine1: addr1,
                    addressLine2: addr2,
                    city: city,
                    province: province
                })
            }).then(function (res) {
                return res.json().catch(function () {
                    return { ok: false, message: 'Invalid server response.' };
                });
            }).then(function (data) {
                if (!data || !data.ok || !data.waUrl) {
                    throw new Error((data && data.message) ? data.message : 'Could not create order.');
                }
                window.open(data.waUrl, '_blank', 'noopener,noreferrer');
                printCustomOrderModalHide();
            }).catch(function (err) {
                if (errBox) {
                    errBox.textContent = err && err.message ? err.message : 'Could not submit order right now.';
                    errBox.hidden = false;
                }
            }).finally(function () {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalBtnText;
                }
            });
        });
    }

    var digitalGalleryLightbox = document.getElementById('digitalGalleryLightbox');
    var digitalGalleryItems = document.querySelectorAll('.digital-gallery-item[data-gallery-src]');
    if (digitalGalleryLightbox && digitalGalleryItems.length) {
        var galleryLightboxImg = document.getElementById('digitalGalleryLightboxImg');
        var galleryLightboxCaption = document.getElementById('digitalGalleryLightboxCaption');
        var gallerySources = [];
        var galleryActiveIndex = 0;
        var galleryLastFocus = null;

        digitalGalleryItems.forEach(function (item) {
            gallerySources.push(item.getAttribute('data-gallery-src') || '');
        });

        function galleryShowAt(index) {
            if (!gallerySources.length || !galleryLightboxImg) {
                return;
            }
            galleryActiveIndex = (index + gallerySources.length) % gallerySources.length;
            galleryLightboxImg.src = gallerySources[galleryActiveIndex];
            galleryLightboxImg.alt = 'Nexora Digital gallery image ' + (galleryActiveIndex + 1);
            if (galleryLightboxCaption) {
                galleryLightboxCaption.textContent = (galleryActiveIndex + 1) + ' / ' + gallerySources.length;
            }
        }

        function galleryOpen(index) {
            galleryLastFocus = document.activeElement;
            galleryShowAt(index);
            digitalGalleryLightbox.classList.add('is-open');
            digitalGalleryLightbox.setAttribute('aria-hidden', 'false');
            document.body.classList.add('digital-gallery-open');
            var closeBtn = digitalGalleryLightbox.querySelector('.digital-gallery-lightbox-close');
            if (closeBtn) {
                closeBtn.focus();
            }
        }

        function galleryClose() {
            digitalGalleryLightbox.classList.remove('is-open');
            digitalGalleryLightbox.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('digital-gallery-open');
            if (galleryLightboxImg) {
                galleryLightboxImg.removeAttribute('src');
            }
            if (galleryLastFocus && typeof galleryLastFocus.focus === 'function') {
                galleryLastFocus.focus();
            }
        }

        digitalGalleryItems.forEach(function (item) {
            item.addEventListener('click', function () {
                var index = parseInt(item.getAttribute('data-gallery-index') || '0', 10);
                galleryOpen(index);
            });
        });

        digitalGalleryLightbox.querySelectorAll('[data-gallery-close]').forEach(function (el) {
            el.addEventListener('click', galleryClose);
        });

        var galleryPrev = digitalGalleryLightbox.querySelector('[data-gallery-prev]');
        var galleryNext = digitalGalleryLightbox.querySelector('[data-gallery-next]');
        if (galleryPrev) {
            galleryPrev.addEventListener('click', function () {
                galleryShowAt(galleryActiveIndex - 1);
            });
        }
        if (galleryNext) {
            galleryNext.addEventListener('click', function () {
                galleryShowAt(galleryActiveIndex + 1);
            });
        }

        document.addEventListener('keydown', function (e) {
            if (!digitalGalleryLightbox.classList.contains('is-open')) {
                return;
            }
            if (e.key === 'Escape') {
                galleryClose();
            } else if (e.key === 'ArrowLeft') {
                galleryShowAt(galleryActiveIndex - 1);
            } else if (e.key === 'ArrowRight') {
                galleryShowAt(galleryActiveIndex + 1);
            }
        });

        var galleryTouchStartX = 0;
        digitalGalleryLightbox.addEventListener('touchstart', function (e) {
            if (e.changedTouches && e.changedTouches[0]) {
                galleryTouchStartX = e.changedTouches[0].clientX;
            }
        }, { passive: true });

        digitalGalleryLightbox.addEventListener('touchend', function (e) {
            if (!e.changedTouches || !e.changedTouches[0]) {
                return;
            }
            var deltaX = e.changedTouches[0].clientX - galleryTouchStartX;
            if (Math.abs(deltaX) < 40) {
                return;
            }
            if (deltaX < 0) {
                galleryShowAt(galleryActiveIndex + 1);
            } else {
                galleryShowAt(galleryActiveIndex - 1);
            }
        }, { passive: true });
    }
})();

