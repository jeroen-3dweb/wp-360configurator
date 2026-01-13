window.onload = function () {
    // Immediately invoked function expression (IIFE) to allow use of async/await
    (function () {
        // Helper function to poll an asset until it returns a 200 response
        async function waitForAsset(url, maxRetries = 60, interval = 1000) {
            let attempts = 0;
            while (attempts < maxRetries) {
                try {
                    const urlObj = new URL(url);
                    urlObj.searchParams.set('cb', Date.now());
                    const response = await fetch(urlObj.toString(), { method: 'GET' });
                    console.log(`Polling URL: ${urlObj.toString()} - Status: ${response.status}`);
                    if (response.status === 200) {
                        return true;
                    }
                } catch (error) {
                    console.error(`Error fetching ${url}:`, error);
                }
                attempts++;
                await new Promise(resolve => setTimeout(resolve, interval));
            }
            throw new Error(`Asset did not load after ${maxRetries} attempts: ${url}`);
        }

        window.cnf3DWeb = {
            isListening: false,

            listenTo3DWebConfigurator: function ($, config) {
                if (this.isListening) {
                    return;
                }
                $('#cnf-3dweb-start-configuration').on('click', function (el) {
                    if (window.cnf3DWeb.isListening) return;
                    window.cnf3DWeb.isListening = true;

                    const $button = $(el.currentTarget);
                    const originalHtml = $button.html();
                    $button.html('Loading...');

                    console.log('3DWeb: Starting session request...');

                    $.ajax({
                        url: config.ajax_url,
                        type: 'POST',
                        data: {
                            method: 'get_session',
                            action: config.action,
                            security: config.security,
                            product_id: config.product_id,
                            post_url: window.location.href,
                        },
                        success: function (response) {
                            console.log('3DWeb: AJAX success response:', response);
                            try {
                                const { data, success } = response;
                                if (success && data && data.url) {
                                    $button.html(`Start ${data.teamSessionReference || ''}`);
                                    setTimeout(() => {
                                        window.open(data.url);
                                    }, 0);
                                } else {
                                    console.error('3DWeb: Server returned failure:', response);
                                    $button.html('Start configuratie');
                                    const errorMessage = data && data.message ? data.message : 'Er is een fout opgetreden bij het laden van de configurator.';
                                    window.cnf3DWeb.showError($, $button, errorMessage);
                                }
                            } catch (e) {
                                console.error('3DWeb: Error processing success response:', e);
                                $button.html('Start configuratie');
                                window.cnf3DWeb.showError($, $button, 'Fout bij verwerken van server respons.');
                            } finally {
                                window.cnf3DWeb.isListening = false;
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error('3DWeb: AJAX error:', { status, error, responseText: xhr.responseText });
                            $button.html('Start configuratie');
                            let errorMessage = 'Er is een kritieke fout opgetreden.';
                            try {
                                if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                                    errorMessage = xhr.responseJSON.data.message;
                                } else if (xhr.responseText && xhr.responseText.indexOf('kritieke fout') !== -1) {
                                    errorMessage = 'Er heeft zich een kritieke fout voorgedaan op de server.';
                                }
                            } catch (e) {
                                console.error('3DWeb: Error parsing error response:', e);
                            }
                            window.cnf3DWeb.showError($, $button, errorMessage);
                            window.cnf3DWeb.isListening = false;
                        }
                    });
                });
            },

            showError: function($, $button, message) {
                if ($('#cnf-3dweb-error').length === 0) {
                    $button.after(`<div id="cnf-3dweb-error" style="color: red; margin-top: 10px; font-weight: bold;">${message}</div>`);
                } else {
                    $('#cnf-3dweb-error').text(message).show();
                }
            },

            addHeightToUrl: function (url, height) {
                const urlObj = new URL(url);
                urlObj.searchParams.set('h', height);
                return urlObj.toString();
            },

            appendThreeSixtyView: function ($, mainImageUrl) {
                console.log('Appending 360 view:', mainImageUrl);
                const $gallery = $('.woocommerce-product-gallery');
                $gallery.empty(); // Clear any existing content

                // Create the 360 view container and insert the main image
                $gallery.append(`<div id="cnf-jsv-holder" style="width: 100%; height: 100%;"></div>`);
                $('#cnf-jsv-holder').append(`<img id="cnf-jsv-image" src="${mainImageUrl}" style="width: unset; height: 100%; object-fit: contain;" alt="Product Image"/>`);
            },

            loadThreeSixtyView: function ($, config) {

                console.log('Loading 360 view:', config);
                const viewer = new JavascriptViewer({
                    mainHolderId: 'cnf-jsv-holder',
                    mainImageId: 'cnf-jsv-image',
                    imageUrls: config.assets['360'].map(asset => this.addHeightToUrl(asset.url, 600)),
                    speed: 70,
                    zoom: true,
                    defaultProgressBar: true,
                    autoRotate: config.threeSixtyConfig.autoRotate,
                    autoCDNResizer: true,
                    autoCDNResizerConfig: {
                        useHeight: true,
                        extraParams: {
                            t: Date.now()
                        }
                    },
                    extraImageClass: 'cnf-jsv-image',
                    license: config.threeSixtyConfig.license,
                });

                viewer.start()
                    .then(() => {
                        // Continue with additional intro logic if needed
                    })
                    .catch((error) => {
                        console.error('Error initializing 360 view:', error);
                    });
            },

            createStartButton: function ($) {
                $('.single_add_to_cart_button').parent().append(`<a id="cnf-3dweb-start-configuration" class="button" href="javascript:void(0)">Start configuratie</a>`);
                $('.single_add_to_cart_button').remove();
            },

            // Async initialization to await asset loading before proceeding
            initialize: async function ($, config) {
                console.log('3DWeb configurator started', config);
                if (config.team_reference) {
                    $('.single_add_to_cart_button').parent().append(`<div style="padding: 5px; display: inline-flex;"><span>Ontwerp: ${config.team_reference}</span></div>`);
                    if (config.useThreeSixtyView && config.assets !== null) {
                        try {
                            // Ensure the main image is loaded before appending
                            const mainImageUrl = this.addHeightToUrl(config.assets.main_0.url, 600);
                            await waitForAsset(mainImageUrl);
                            this.appendThreeSixtyView($, mainImageUrl);

                            // Wait for the last image in the 360 asset list before initializing the viewer
                            const lastUrl = config.assets['360'][config.assets['360'].length - 1]['url'];
                            await waitForAsset(lastUrl);
                            this.loadThreeSixtyView($, config);
                        } catch (error) {
                            console.error('Error loading assets:', error);
                        }
                    } else {
                        console.error('3DWeb other view not implemented yet');
                    }
                } else {
                    this.createStartButton($);
                    this.listenTo3DWebConfigurator($, config);
                }
            }
        };

        jQuery(document).ready(function ($) {
            if (typeof cnf3Dweb !== 'undefined') {
                window.cnf3DWeb.initialize($, cnf3Dweb);
            }
        });
    })();
};
