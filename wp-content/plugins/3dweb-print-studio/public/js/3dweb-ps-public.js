(function ($) {
    'use strict';

    // Helper function to poll an asset until it returns a 200 response
    async function waitForAsset(url, maxRetries = 60, interval = 1000) {
        let attempts = 0;
        while (attempts < maxRetries) {
            try {
                const urlObj = new URL(url);
                urlObj.searchParams.set('cb', Date.now());
                const response = await fetch(urlObj.toString(), {method: 'GET'});
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

    window.cnf3DWebCore = {
        isListening: false,

        init: function (config, themeHooks) {
            this.config = config;
            this.themeHooks = themeHooks;
            this.logDebug('Core initialized', config);

            if (config.team_reference) {
                this.handleExistingSession();
            } else {
                this.handleNewSession();
            }
        },

        logDebug: function (message, ...argss) {
            if(this.config.debug) {
                message = `[3DWeb] ${message}`;
                console.log(message, argss, this.config)
            }
        },

        handleExistingSession: async function () {
            if (this.themeHooks.onSessionStarted) {
                this.themeHooks.onSessionStarted(this.config.team_reference);
            }

            if (this.config.useThreeSixtyView && this.config.assets !== null) {
                try {
                    const mainImageUrl = this.addHeightToUrl(this.config.assets.main_0.url, 600);
                    await waitForAsset(mainImageUrl);

                    if (this.themeHooks.replaceImageWith360) {
                        this.themeHooks.replaceImageWith360(mainImageUrl, (containerId, imageId) => {
                            this.loadThreeSixtyView(containerId, imageId);
                        });
                    }
                } catch (error) {
                    console.error('3DWeb: Error loading assets:', error);
                }
            }
        },

        changeTextOnButton: function (selector, text) {
            if (this.themeHooks.changeTextOnButton) {
                this.themeHooks.changeTextOnButton(this.config);
            } else {
                $(selector).text(text);
            }
        },

        handleNewSession: function () {
            if (this.themeHooks.initStartButton) {
                const selector = this.themeHooks.initStartButton();
                if (selector) {
                    this.config.selector = selector;
                    this.logDebug('Button selector found:');
                    this.changeTextOnButton(selector, this.config.translations.startConfiguration);

                    $(document).on('click', selector, (e) => {
                        e.preventDefault();
                        this.logDebug('Button clicked');
                        this.changeTextOnButton(selector, this.config.translations.loading);
                        this.startNewSession(selector);
                    });
                }
            }
        },

        startNewSession: function (selector) {
            if (this.isListening) return;
            this.isListening = true;
            this.logDebug('Starting new session');

            if (this.themeHooks.onSessionLoading) {
                this.themeHooks.onSessionLoading(true, this.config);
            }

            $.ajax({
                url: this.config.ajax_url,
                type: 'POST',
                data: {
                    method: 'get_session',
                    action: this.config.action,
                    security: this.config.security,
                    product_id: this.config.product_id,
                    post_url: window.location.href,
                },
                success: (response) => {
                    this.logDebug('Session response received', response);
                    try {
                        const {data, success} = response;
                        if (success && data && data.url) {

                            this.config.teamReference = data[this.config.team_reference_key];
                            if (this.themeHooks.onSessionLoading) {
                                this.themeHooks.onSessionLoading(false, this.config);
                            }
                            setTimeout(() => {
                                window.open(data.url);
                            }, 0);
                        } else {
                            const errorMsg = response.message ? response.message : (data && data.message ? data.message : 'Er is een fout opgetreden bij het laden van de configurator.');
                            this.handleError(errorMsg);
                        }
                    } catch (e) {
                        this.handleError('Fout bij verwerken van server respons.');
                    } finally {
                        this.isListening = false;
                    }
                },
                error: (xhr, status, error) => {
                    this.logDebug('AJAX Error:', status, error, xhr.responseText);
                    let errorMessage = 'Er is een kritieke fout opgetreden.';
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response && response.data && response.data.message) {
                            errorMessage = response.data.message;
                        }
                    } catch (e) {
                        if (error) {
                            errorMessage = `Server fout: ${error}`;
                        }
                    }
                    this.handleError(errorMessage);
                    this.isListening = false;
                }
            });
        },

        handleError: function (message) {
            this.logDebug('Error:', message);
            if (this.themeHooks.showError) {
                this.themeHooks.showError(message);
            }
            if (this.themeHooks.onSessionLoading) {
                this.themeHooks.onSessionLoading(false, this.config);
            }
        },

        addHeightToUrl: function (url, height) {
            const urlObj = new URL(url);
            urlObj.searchParams.set('h', height);
            return urlObj.toString();
        },

        loadThreeSixtyView: function (containerId, imageId) {
            if (typeof JavascriptViewer === 'undefined') {
                console.error('JavascriptViewer not loaded');
                return;
            }

            const viewer = new JavascriptViewer({
                mainHolderId: containerId,
                mainImageId: imageId,
                imageUrls: this.config.assets['360'].map(asset => this.addHeightToUrl(asset.url, 600)),
                speed: 70,
                zoom: true,
                defaultProgressBar: true,
                autoRotate: this.config.threeSixtyConfig.autoRotate,
                autoCDNResizer: true,
                autoCDNResizerConfig: {
                    useHeight: true,
                    extraParams: {
                        t: Date.now()
                    }
                },
                extraImageClass: 'cnf-jsv-image',
                license: this.config.threeSixtyConfig.license,
            });

            viewer.start().catch((error) => {
                console.error('Error initializing 360 view:', error);
            });
        }
    };

})(jQuery);
