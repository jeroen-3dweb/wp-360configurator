(function ($) {
    'use strict';

    $(document).ready(function () {
        if (typeof cnf3Dweb === 'undefined') return;

        const themeHooks = {
            initStartButton: function () {
                const $cart = $('.single_add_to_cart_button');
                if ($cart.length) {
                    $cart.parent().append(`<a id="3dweb_ps-start-configuration" class="button" href="javascript:void(0)">Start configuratie</a>`);
                    $cart.remove();
                }
                return '#3dweb_ps-start-configuration';
            },

            onSessionLoading: function (isLoading, teamReference) {
                const $button = $('#3dweb_ps-start-configuration');
                if (isLoading) {
                    $button.html('Loading...');
                } else {
                    if (teamReference) {
                        $button.html(`Start ${teamReference}`);
                    } else {
                        $button.html('Start configuratie');
                    }
                }
            },

            onSessionStarted: function (teamReference) {
                $('.single_add_to_cart_button').parent().append(`<div style="padding: 5px; display: inline-flex;"><span>Ontwerp: ${teamReference}</span></div>`);
            },

            replaceImageWith360: function (mainImageUrl, callback) {
                const $gallery = $('.woocommerce-product-gallery');
                $gallery.empty(); // Clear any existing content
                $gallery.append(`<div id="cnf-jsv-holder" style="width: 100%; height: 100%;"></div>`);
                $('#cnf-jsv-holder').append(`<img id="cnf-jsv-image" src="${mainImageUrl}" style="width: unset; height: 100%; object-fit: contain;" alt="Product Image"/>`);
                
                callback('cnf-jsv-holder', 'cnf-jsv-image');
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
