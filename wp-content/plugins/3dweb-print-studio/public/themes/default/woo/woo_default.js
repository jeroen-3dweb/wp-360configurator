(function ($) {
    'use strict';

    $(document).ready(function () {
        if (typeof cnf3Dweb === 'undefined') return;

        const core = window.cnf3DWebCore;
        if (!core) return;

        const themeHooks = {

            buttonSelector: '.single_add_to_cart_button',
            gallerySelector: '.woocommerce-product-gallery',

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

            onBackFromSession: function (config) {
                const $button = $(this.buttonSelector);
                if ($button.length) {
                    const sessionClosedText = config.translations.sessionClosed.replace('{reference}', config.team_reference);
                    $button.parent().append(`<div style="padding: 5px; display: inline-flex;"><span>${sessionClosedText}</span></div>`);
                }

                const applyMainImage = (assets) => {
                    const mainImageUrl = core.addHeightToUrl(assets.main_0.url, 600);
                    const cacheBustedMainImageUrl = mainImageUrl + (mainImageUrl.includes('?') ? '&' : '?') + '_cb=' + Date.now();
                    core.logDebug('Replacing main image with:', mainImageUrl);

                    return core.checkIfReady(mainImageUrl)
                        .then(() => {
                            core.logDebug('Main image ready');
                            return this.replaceMainImage(cacheBustedMainImageUrl);
                        });
                };

                this.showGalleryLoader();

                if (config.assets && config.assets.main_0 && config.assets.main_0.url) {
                    applyMainImage(config.assets)
                        .catch((error) => {
                            core.handleError(error);
                        })
                        .finally(() => {
                            this.hideGalleryLoader();
                        });
                } else {
                    core.logDebug('No imageUrl found in config. Polling session assets.');
                    core.waitForSessionAssets()
                        .then((assets) => {
                            core.config.assets = assets;
                            return applyMainImage(assets);
                        })
                        .catch((error) => {
                            core.handleError(error);
                        })
                        .finally(() => {
                            this.hideGalleryLoader();
                        });
                }
            },

            showGalleryLoader: function () {
                const gallery = document.querySelector(this.gallerySelector);
                if (!gallery) {
                    return;
                }

                const target = gallery.querySelector('.woocommerce-product-gallery__wrapper') || gallery;
                const targetStyle = window.getComputedStyle(target);
                if (targetStyle.position === 'static') {
                    target.dataset.cnf3dwebLoaderPosition = 'static';
                    target.style.position = 'relative';
                }

                target.dataset.cnf3dwebLoaderOpacity = target.style.opacity || '';
                target.style.opacity = '0.45';

                if (!target.querySelector('.cnf3dweb-gallery-loader')) {
                    const loader = document.createElement('div');
                    loader.className = 'cnf3dweb-gallery-loader';
                    loader.setAttribute('aria-hidden', 'true');
                    loader.style.position = 'absolute';
                    loader.style.top = '50%';
                    loader.style.left = '50%';
                    loader.style.width = '42px';
                    loader.style.height = '42px';
                    loader.style.marginTop = '-21px';
                    loader.style.marginLeft = '-21px';
                    loader.style.border = '3px solid rgba(0,0,0,0.2)';
                    loader.style.borderTopColor = 'rgba(0,0,0,0.75)';
                    loader.style.borderRadius = '50%';
                    loader.style.zIndex = '20';
                    loader.style.pointerEvents = 'none';

                    if (typeof loader.animate === 'function') {
                        loader.animate(
                            [
                                { transform: 'rotate(0deg)' },
                                { transform: 'rotate(360deg)' }
                            ],
                            { duration: 700, iterations: Infinity }
                        );
                    }

                    target.appendChild(loader);
                }
            },

            hideGalleryLoader: function () {
                const gallery = document.querySelector(this.gallerySelector);
                if (!gallery) {
                    return;
                }

                const target = gallery.querySelector('.woocommerce-product-gallery__wrapper') || gallery;
                target.style.opacity = target.dataset.cnf3dwebLoaderOpacity || '';
                delete target.dataset.cnf3dwebLoaderOpacity;

                if (target.dataset.cnf3dwebLoaderPosition === 'static') {
                    target.style.position = '';
                    delete target.dataset.cnf3dwebLoaderPosition;
                }

                const loader = target.querySelector('.cnf3dweb-gallery-loader');
                if (loader) {
                    loader.remove();
                }
            },

            replaceMainImage: function (imageUrl) {
                if (!window.cnf3DWebFlexslider || !window.cnf3DWebFlexslider.updateWooGalleryImage) {
                    return Promise.reject(new Error('Flexslider helper not loaded'));
                }

                return window.cnf3DWebFlexslider.updateWooGalleryImage(
                    1,
                    imageUrl,
                    null,
                    {
                        logger: (message) => core.logDebug(message),
                    }
                );
            },
        };

        window.cnf3DWebCore.init(cnf3Dweb, themeHooks);
    });

})(jQuery);
