(function ($) {
    'use strict';

    $(document).ready(function () {
        if (typeof cnf3Dweb === 'undefined') return;

        const core = window.cnf3DWebCore;
        if (!core) return;

        const themeHooks = {

            buttonSelector: '.single_add_to_cart_button',

            initStartButton: function () {
                const selector = this.buttonSelector;
                core.logDebug('Button selector found:', selector);
                core.changeTextOnButton(selector, core.config.translations.startConfiguration);

                $(document).on('click', selector, (e) => {
                    e.preventDefault();
                    core.logDebug('Button clicked');
                    core.changeTextOnButton(selector, core.config.translations.loading);
                    core.startNewSession(selector);
                });
            },

            onSessionLoading: function (isLoading, config) {
                const $button = $(this.buttonSelector);
                if (isLoading) {
                    $button.html(config.translations.loading);
                } else {
                    if (config.teamReference) {
                        $button.html(`Start ${config.teamReference}`);
                    } else {
                        $button.html(config.translations.startConfiguration);
                    }
                }
            },
            getGalleryBaselineHeight(galleryEl) {
            // 1) Als we hem al hebben opgeslagen: gebruik die
            const existing = galleryEl.dataset.cfgBaselineH;
            if (existing) return parseFloat(existing);

            // 2) Anders: bepaal baseline uit de eerste "normale" slide (bijv. de originele WP afbeelding)
            const firstImg = galleryEl.querySelector('.woocommerce-product-gallery__image img');
            const slide = galleryEl.querySelector('.woocommerce-product-gallery__image');
            const slideW = slide?.getBoundingClientRect().width || 0;

            // Probeer ratio uit width/height attrs (meestal 600x600 => 1:1)
            const w = parseFloat(firstImg?.getAttribute('width')) || 0;
            const h = parseFloat(firstImg?.getAttribute('height')) || 0;

            let baselineH = 0;

            if (slideW && w && h) {
                baselineH = slideW * (h / w);
            } else {
                // fallback: clamp op huidige viewport maar nooit extremer dan bv 900px
                const vp = galleryEl.querySelector('.flex-viewport');
                baselineH = Math.min(vp?.getBoundingClientRect().height || 600, 900);
            }

            // 3) Opslaan zodat latere updates niet “meemeten” met kapotte heights
            galleryEl.dataset.cfgBaselineH = String(Math.round(baselineH));
            return baselineH;
        },
            async updateWooGalleryImage (index, newUrl, thumbUrl) {
                const i = index - 1;
                const gallery = document.querySelector('.woocommerce-product-gallery');
                if (!gallery) return console.warn('Gallery not found');

                const wrapper = gallery.querySelector('.woocommerce-product-gallery__wrapper');
                const slides = wrapper?.querySelectorAll('.woocommerce-product-gallery__image');
                const slide = slides?.[i];
                if (!slide) return console.warn(`Slide ${index} not found`);

                const img = slide.querySelector('img');
                const a = slide.querySelector('a');
                const viewport = gallery.querySelector('.flex-viewport');
                if (!img) return;

                // thumbUrl: als je geen aparte thumb hebt, gebruik dezelfde
                const tUrl = thumbUrl || newUrl;

                // ---- baseline hoogte (jouw A-variant) ----
                const baselineH = parseFloat(gallery.dataset.cfgBaselineH || '0');
                if (baselineH && viewport) {
                    viewport.style.height = `${Math.round(baselineH)}px`;
                    viewport.style.overflow = 'hidden';
                }

                // ---- main image vervangen ----
                img.src = newUrl;
                img.setAttribute('data-src', newUrl);
                img.setAttribute('data-large_image', newUrl);
                img.removeAttribute('srcset');
                img.removeAttribute('sizes');
                if (a) a.href = newUrl;

                // vaste box + contain
                if (baselineH) {
                    img.style.width = '100%';
                    img.style.height = `${Math.round(baselineH)}px`;
                    img.style.objectFit = 'contain';
                    img.style.display = 'block';
                }

                // ---- BELANGRIJK: slide data-thumb bijwerken ----
                slide.setAttribute('data-thumb', tUrl);
                slide.setAttribute('data-thumb-srcset', ''); // of een echte srcset string
                slide.setAttribute('data-thumb-sizes', '(max-width: 100px) 100vw, 100px');

                // ---- thumbnail in <ol> bijwerken ----
                const thumbs = gallery.querySelectorAll('.flex-control-thumbs img');
                const thumbImg = thumbs?.[i];
                if (thumbImg) {
                    thumbImg.src = tUrl;
                    thumbImg.removeAttribute('srcset');
                    thumbImg.removeAttribute('sizes');

                    // voorkom dat onload de layout “opblaast” met naturalWidth/naturalHeight
                    thumbImg.removeAttribute('onload');

                    // maak hem echt thumb-sized (alleen dit element)
                    thumbImg.style.width = '61px';
                    thumbImg.style.height = '61px';
                    thumbImg.style.objectFit = 'cover';

                    // reset attrs die door die onload gezet kunnen zijn
                    thumbImg.removeAttribute('width');
                    thumbImg.removeAttribute('height');
                }

                // wacht op load zodat je geen “oude” thumb cached ziet
                await new Promise((resolve) => {
                    if (img.complete) return resolve();
                    img.addEventListener('load', resolve, { once: true });
                    img.addEventListener('error', resolve, { once: true });
                });

                // Flexslider/woo herberekenen
                if (window.jQuery) window.jQuery(window).trigger('resize');
                core.logDebug(`WooCommerce image ${index} updated`);
            }
            ,

            onBackFromSession: function (config) {
                const $button = $(this.buttonSelector);
                if ($button.length) {
                    const sessionClosedText = config.translations.sessionClosed.replace('{reference}', config.team_reference);
                    $button.parent().append(`<div style="padding: 5px; display: inline-flex;"><span>${sessionClosedText}</span></div>`);
                }

                if (config.assets && config.assets.main_0 && config.assets.main_0.url) {
                    const mainImageUrl = core.addHeightToUrl(config.assets.main_0.url, 600);
                    core.logDebug('Replacing main image with:', mainImageUrl);

                    core.checkIfReady(mainImageUrl)
                        .then(() => {
                            core.logDebug('Main image ready');
                            this.replaceMainImage(mainImageUrl);
                        })
                        .catch((error) => {
                            core.handleError(error);
                        });
                } else {
                    core.logDebug('No imageUrl found in config. Skipping');
                }
            },

            replaceMainImage: function (imageUrl) {
                this.updateWooGalleryImage(1, imageUrl);
                jQuery('.woocommerce-product-gallery').wc_product_gallery();
            },
        };

        window.cnf3DWebCore.init(cnf3Dweb, themeHooks);
    });

})(jQuery);
