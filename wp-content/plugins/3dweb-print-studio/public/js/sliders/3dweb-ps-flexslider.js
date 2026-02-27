(function (window) {
    'use strict';

    function getGalleryBaselineHeight(galleryEl) {
        const existing = galleryEl.dataset.cfgBaselineH;
        if (existing) {
            return parseFloat(existing);
        }

        const firstImg = galleryEl.querySelector('.woocommerce-product-gallery__image img');
        const slide = galleryEl.querySelector('.woocommerce-product-gallery__image');
        const slideWidth = slide ? slide.getBoundingClientRect().width : 0;

        const widthAttr = parseFloat(firstImg ? firstImg.getAttribute('width') : 0) || 0;
        const heightAttr = parseFloat(firstImg ? firstImg.getAttribute('height') : 0) || 0;

        let baselineHeight = 0;
        if (slideWidth && widthAttr && heightAttr) {
            baselineHeight = slideWidth * (heightAttr / widthAttr);
        } else {
            const viewport = galleryEl.querySelector('.flex-viewport');
            baselineHeight = Math.min((viewport ? viewport.getBoundingClientRect().height : 600), 900);
        }

        galleryEl.dataset.cfgBaselineH = String(Math.round(baselineHeight));
        return baselineHeight;
    }

    async function updateWooGalleryImage(index, newUrl, thumbUrl, options) {
        const settings = options || {};
        const gallerySelector = settings.gallerySelector || '.woocommerce-product-gallery';
        const logger = settings.logger || null;

        const i = index - 1;
        const gallery = document.querySelector(gallerySelector);
        if (!gallery) {
            console.warn('Gallery not found');
            return;
        }

        const wrapper = gallery.querySelector('.woocommerce-product-gallery__wrapper');
        const slides = wrapper ? wrapper.querySelectorAll('.woocommerce-product-gallery__image') : null;
        const slide = slides ? slides[i] : null;
        if (!slide) {
            console.warn('Slide ' + index + ' not found');
            return;
        }

        const img = slide.querySelector('img');
        const anchor = slide.querySelector('a');
        const viewport = gallery.querySelector('.flex-viewport');
        if (!img) {
            return;
        }

        const tUrl = thumbUrl || newUrl;
        const baselineHeight = parseFloat(gallery.dataset.cfgBaselineH || '0') || getGalleryBaselineHeight(gallery);

        if (baselineHeight && viewport) {
            viewport.style.height = Math.round(baselineHeight) + 'px';
            viewport.style.overflow = 'hidden';
        }

        img.src = newUrl;
        img.setAttribute('data-src', newUrl);
        img.setAttribute('data-large_image', newUrl);
        img.removeAttribute('srcset');
        img.removeAttribute('sizes');

        if (anchor) {
            anchor.href = newUrl;
        }

        if (baselineHeight) {
            img.style.width = '100%';
            img.style.height = Math.round(baselineHeight) + 'px';
            img.style.objectFit = 'contain';
            img.style.display = 'block';
        }

        slide.setAttribute('data-thumb', tUrl);
        slide.setAttribute('data-thumb-srcset', '');
        slide.setAttribute('data-thumb-sizes', '(max-width: 100px) 100vw, 100px');

        const thumbs = gallery.querySelectorAll('.flex-control-thumbs img');
        const thumbImg = thumbs ? thumbs[i] : null;
        if (thumbImg) {
            const thumbItem = thumbImg.closest('li');
            thumbImg.src = tUrl;
            thumbImg.removeAttribute('srcset');
            thumbImg.removeAttribute('sizes');
            thumbImg.removeAttribute('onload');
            if (thumbItem) {
                thumbItem.style.aspectRatio = '1 / 1';
                thumbItem.style.overflow = 'hidden';
            }
            thumbImg.style.width = '100%';
            thumbImg.style.height = '100%';
            thumbImg.style.objectFit = 'contain';
            thumbImg.style.objectPosition = 'center center';
            thumbImg.removeAttribute('width');
            thumbImg.removeAttribute('height');
        }

        await new Promise(function (resolve) {
            if (img.complete) {
                resolve();
                return;
            }
            img.addEventListener('load', resolve, { once: true });
            img.addEventListener('error', resolve, { once: true });
        });

        const naturalWidth = parseInt(img.naturalWidth, 10) || 0;
        const naturalHeight = parseInt(img.naturalHeight, 10) || 0;
        if (naturalWidth > 0 && naturalHeight > 0) {
            // Keep WooCommerce lightbox dimensions in sync with the replaced image.
            img.setAttribute('data-large_image_width', String(naturalWidth));
            img.setAttribute('data-large_image_height', String(naturalHeight));
            img.setAttribute('width', String(naturalWidth));
            img.setAttribute('height', String(naturalHeight));

            if (anchor) {
                // Support both WooCommerce/PhotoSwipe attribute styles.
                anchor.setAttribute('data-size', naturalWidth + 'x' + naturalHeight);
                anchor.setAttribute('data-pswp-width', String(naturalWidth));
                anchor.setAttribute('data-pswp-height', String(naturalHeight));
            }
        }

        if (window.jQuery) {
            window.jQuery(window).trigger('resize');
        }

        if (typeof logger === 'function') {
            logger('WooCommerce image ' + index + ' updated');
        }
    }

    window.cnf3DWebFlexslider = {
        getGalleryBaselineHeight: getGalleryBaselineHeight,
        updateWooGalleryImage: updateWooGalleryImage
    };
})(window);
