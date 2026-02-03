(function ($) {
    'use strict';

    $(document).ready(function () {
        if (typeof cnf3Dweb === 'undefined') return;

        const themeHooks = {
            initStartButton: function (config) {
                return '.single_add_to_cart_button';
            },

            onSessionLoading: function (isLoading, config) {
                const $button = $(config.selector);
                if (isLoading) {
                    $button.html(config.translations.loading);
                } else {
                    if (config.teamReference) {
                        $button.html(`Start ${config.teamReference}`);
                    } else {
                        $button.html('Start configuratie');
                    }
                }
            },

            onSessionStarted: function (teamReference) {
                const $button = $('#3dweb_ps-start-configuration');
                if ($button.length) {
                    $button.parent().append(`<div style="padding: 5px; display: inline-flex;"><span>Ontwerp: ${teamReference}</span></div>`);
                }
            },

            replaceImageWith360: function (mainImageUrl, callback) {
                // Default fallback for WooCommerce product gallery
                const $gallery = $('.woocommerce-product-gallery');
                if ($gallery.length) {
                    $gallery.empty();
                    $gallery.append(`<div id="cnf-jsv-holder" style="width: 100%; height: 500px;"></div>`);
                    $('#cnf-jsv-holder').append(`<img id="cnf-jsv-image" src="${mainImageUrl}" style="width: 100%; height: 100%; object-fit: contain;" alt="Product Image"/>`);
                    callback('cnf-jsv-holder', 'cnf-jsv-image');
                }
            },

            showError: function (message) {
                const $button = $('#3dweb_ps-start-configuration');
                if ($('#3dweb_ps-error').length === 0) {
                    $button.after(`<div id="3dweb_ps-error" style="color: red; margin-top: 10px; font-weight: bold;">${message}</div>`);
                } else {
                    $('#3dweb_ps-error').text(message).show();
                }
            }
        };

        window.cnf3DWebCore.init(cnf3Dweb, themeHooks);
    });

})(jQuery);
