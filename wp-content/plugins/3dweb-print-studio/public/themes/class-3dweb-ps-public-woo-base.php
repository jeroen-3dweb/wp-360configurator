<?php

class DWeb_PS_Public_Woo_Base
{
    const THEME_NAME = 'base';
    const TEAM_SESSION_REFERENCE = 'teamSessionReference';

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    protected $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    protected $version;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of the plugin.
     * @param string $version The version of this plugin.
     * @since    1.0.0
     */

    private $mainImages = [];

    private $sessionImagesLoaded = false;
    private $sessionAssetsCache = [];

    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        add_action('wp_ajax_3dweb_ps_action_get_endpoint', [DWeb_PS_Public_Woo_Base::class, 'handle_get_endpoint_request']);
        add_action('wp_ajax_nopriv_3dweb_ps_action_get_endpoint', [DWeb_PS_Public_Woo_Base::class, 'handle_get_endpoint_request']);

    }

    protected function generateId()
    {
        $permitted_chars = implode('', range('a', 'z'));
        return 'cnf-' . '-' . substr(str_shuffle($permitted_chars), 0, 10);
    }

	protected function loadExtraScripts(){
	}

	protected function loadExtraStyles(){
	}

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.5.0
     */
    protected function loadScripts()
    {
        wp_enqueue_script(
            $this->plugin_name . '_public_core',
            plugin_dir_url(dirname(__FILE__)) . 'js/3dweb-ps-public.js',
            array('jquery'),
            $this->version,
            true
        );

        wp_enqueue_script(
            $this->plugin_name . '_public_flexslider',
            plugin_dir_url(dirname(__FILE__)) . 'js/sliders/3dweb-ps-flexslider.js',
            array('jquery'),
            $this->version,
            true
        );

		$this->loadExtraScripts();
    }

    public function enqueue_styles()
    {
	    $this->loadExtraStyles();
    }

//    public function getProductId()
//    {
//        global $post;
//        return sanitize_meta(DWeb_PS_WOO_METABOX::FIELD_PRODUCT_ID, get_post_meta($post->ID, DWeb_PS_WOO_METABOX::FIELD_PRODUCT_ID, true), 'post');
//    }

    public function enqueue_scripts()
    {
        global $post;
        $this->loadScripts();

        $productID = sanitize_meta(DWeb_PS_WOO_METABOX::FIELD_PRODUCT_ID, get_post_meta($post->ID, DWeb_PS_WOO_METABOX::FIELD_PRODUCT_ID, true), 'post');

        $assets = [];
        $teamReference = $this->getTeamReference();
        if ($teamReference) {
            $response = (new DWeb_PS_API())->getSessionAssets($teamReference);
            if ($response && !isset($response['error']) && !isset($response['errors']) && isset($response['data'])) {
                $assets = $response['data'];
            }
        }
        $threeSixtyConfig = [];
        if (DWeb_PS_JAVASCRIPTVIEWER::javascriptviewerIsActive()) {
            $threeSixtyConfig = [
                'license' => get_option(JSV_360_ADMIN_LICENSE::NOTIFIER_LICENSE, null),
                'autoRotate' => get_option(JSV_360_ADMIN_AUTOROTATE::AUTOROTATE, null)
            ];
        }

        // hook for the endpoint request
        wp_localize_script($this->plugin_name . '_woo_js', 'cnf3Dweb', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'security' => wp_create_nonce('3dweb_ps-nonce'),
            'action' => '3dweb_ps_action_get_endpoint',
            'product_id' => $productID,
            'assets' => $assets,
            'team_reference' => $this->getTeamReference(),
            'team_reference_key' => self::TEAM_SESSION_REFERENCE,
            'useThreeSixtyView' => get_option(DWeb_PS_ADMIN_OPTIONS::CONFIGURATOR_USE_THREESIXTY, false),
            'debug' => get_option(DWeb_PS_ADMIN_OPTIONS::CONFIGURATOR_DEBUG, false),
            'threeSixtyConfig' => $threeSixtyConfig,
	        'translations' => array(
		        'startConfiguration' => get_option(DWeb_PS_ADMIN_OPTIONS::CONFIGURATOR_START_BUTTON_TEXT, __('Start configuration', '3dweb-print-studio')),
		        'loading' => __('Loading ...', '3dweb-print-studio'),
		        'sessionClosed' => get_option(DWeb_PS_ADMIN_OPTIONS::CONFIGURATOR_SESSION_CLOSED_TEXT, 'Design: {reference}'),
	        )
        ));
    }


    private static function getSession()
    {
        $productId = sanitize_text_field($_POST['product_id']);
        $callbackUrl = sanitize_text_field($_POST['post_url']);

        return (new DWeb_PS_API())->createNewSession($productId, $callbackUrl);
    }

    public static function handle_get_endpoint_request()
    {
        check_ajax_referer('3dweb_ps-nonce', 'security');

        $method = sanitize_text_field($_POST['method']);

        switch ($method) {

            case 'get_session':
                $response = self::getSession();
                break;

            case 'get_assets':
                $self = new self('3dweb_ps', '1.0.0');
                $self->loadSessionUrls();
                $response = $self->mainImages;
                break;

            default:
                $response = false;
                break;
        }


        if ($response === false || isset($response['error']) || isset($response['errors'])) {
            $error_message = 'Something went wrong';
            if (is_array($response)) {
                if (isset($response['message'])) {
                    $error_message = $response['message'];
                } elseif (isset($response['error'])) {
                    $error_message = $response['error'];
                } elseif (isset($response['errors'])) {
                    // Handle multiple errors if returned by API
                    if (is_array($response['errors'])) {
                        $error_message = implode(', ', array_map(function($err) {
                            return is_array($err) ? implode(': ', $err) : $err;
                        }, $response['errors']));
                    } else {
                        $error_message = $response['errors'];
                    }
                }
            }
            wp_send_json_error(['message' => $error_message]);
        }

        wp_send_json_success($response);
    }

    private function getTeamReference()
    {
        return $_GET[self::TEAM_SESSION_REFERENCE] ?? null;
    }


    public function handleAddCustomHiddenField()
    {
        if (isset($_GET[self::TEAM_SESSION_REFERENCE])) {
            echo '<input type="hidden" name="' . self::TEAM_SESSION_REFERENCE . '" value="' . esc_attr($_GET[self::TEAM_SESSION_REFERENCE]) . '">';
        }
    }

    public function handleAddToCartItem($cart_item_data, $productID)
    {
        if (isset($_POST[self::TEAM_SESSION_REFERENCE]) && $productID) {
            $cart_item_data[self::TEAM_SESSION_REFERENCE] = $_POST[self::TEAM_SESSION_REFERENCE];
        }
        return $cart_item_data;
    }


    public function handleGetItemData($item_data, $cart_item)
    {
        if (isset($cart_item[self::TEAM_SESSION_REFERENCE])) {
            $item_data[] = array(
                'key' => 'reference',
                'value' => wc_clean($cart_item[self::TEAM_SESSION_REFERENCE])
            );
        }
        return $item_data;
    }

    public function handleCreateOrderLineItem($item, $cart_item_key, $values, $order)
    {
        if (isset($values[self::TEAM_SESSION_REFERENCE])) {
            $teamReference = sanitize_text_field($values[self::TEAM_SESSION_REFERENCE]);
            $item->add_meta_data(self::TEAM_SESSION_REFERENCE, $teamReference, true);

            $designUrl = $this->getSessionDesignUrl($teamReference);
            if ($designUrl) {
                $item->add_meta_data('design', $designUrl, true);
            }
        }
        return $item;
    }

    public function handleChangeCartImage($image, $cart_item, $cart_item_key)
    {
        $customImageUrl = $this->getCartItemCustomImageUrl($cart_item);
        if (!$customImageUrl) {
            return $image;
        }

        $altText = '';
        if (isset($cart_item['data']) && $cart_item['data'] instanceof WC_Product) {
            $altText = $cart_item['data']->get_name();
        }

        return sprintf(
            '<img src="%s" alt="%s" class="attachment-woocommerce_thumbnail size-woocommerce_thumbnail" loading="lazy" decoding="async" />',
            esc_url($customImageUrl),
            esc_attr($altText)
        );
    }

    public function handleStoreApiCartItemImages($product_images, $cart_item, $cart_item_key)
    {
        $customImageUrl = $this->getCartItemCustomImageUrl($cart_item);
        if (!$customImageUrl) {
            return $product_images;
        }

        $baseImage = null;
        if (is_array($product_images) && !empty($product_images) && is_object($product_images[0])) {
            $baseImage = $product_images[0];
        }

        $image = (object) [
            'id'        => isset($baseImage->id) ? (int) $baseImage->id : (int) ($cart_item['product_id'] ?? 0),
            'src'       => $customImageUrl,
            'thumbnail' => $customImageUrl,
            'srcset'    => isset($baseImage->srcset) ? (string) $baseImage->srcset : '',
            'sizes'     => isset($baseImage->sizes) ? (string) $baseImage->sizes : '',
            'name'      => isset($baseImage->name) ? (string) $baseImage->name : '',
            'alt'       => isset($baseImage->alt) ? (string) $baseImage->alt : '',
        ];

        return [$image];
    }

    public function handleAdminOrderItemThumbnail($thumbnail, $item_id, $item)
    {
        if (!$item || !is_a($item, 'WC_Order_Item_Product')) {
            return $thumbnail;
        }

        $teamReference = $item->get_meta(self::TEAM_SESSION_REFERENCE, true);
        if (!$teamReference) {
            return $thumbnail;
        }

        $customImageUrl = $this->getSessionMainImageUrl($teamReference);
        if (!$customImageUrl) {
            return $thumbnail;
        }

        $altText = $item->get_name();

        return sprintf(
            '<img src="%s" class="attachment-thumbnail size-thumbnail" alt="%s" title="" loading="lazy" style="width:100%%;height:100%%;max-width:none;max-height:none;object-fit:contain;display:block;margin:0;padding:0;" />',
            esc_url($customImageUrl),
            esc_attr($altText)
        );
    }

    public function handleOrderItemDisplayMetaValue($displayValue, $meta, $item)
    {
        if (!is_admin() || !is_object($meta) || !isset($meta->key) || $meta->key !== 'design') {
            return $displayValue;
        }

        if (!$item || !is_a($item, 'WC_Order_Item_Product')) {
            return $displayValue;
        }

        if (!is_string($displayValue) || !filter_var($displayValue, FILTER_VALIDATE_URL)) {
            return $displayValue;
        }

        return $this->buildDesignActionsHtml($displayValue);
    }

    public function handleOrderItemFormattedMetaData($formattedMeta, $item)
    {
        if (!is_admin() || !$item || !is_a($item, 'WC_Order_Item_Product')) {
            return $formattedMeta;
        }

        $teamReference = $item->get_meta(self::TEAM_SESSION_REFERENCE, true);
        if (!$teamReference) {
            return $formattedMeta;
        }

        foreach ($formattedMeta as $meta) {
            if (isset($meta->key) && $meta->key === 'design') {
                return $formattedMeta;
            }
        }

        $designUrl = $item->get_meta('design', true);
        if (!$designUrl) {
            $designUrl = $this->getSessionDesignUrl($teamReference);
        }

        if (!$designUrl) {
            return $formattedMeta;
        }

        $displayValue = $this->buildDesignActionsHtml($designUrl);

        $formattedMeta['cnf_design'] = (object) [
            'key'           => 'design',
            'value'         => $designUrl,
            'display_key'   => 'design',
            'display_value' => $displayValue,
        ];

        return $formattedMeta;
    }

    public function handleAdminDesignDownload()
    {
        if (!current_user_can('edit_shop_orders')) {
            wp_die('Forbidden', 403);
        }

        $nonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';
        if (!wp_verify_nonce($nonce, '3dweb_ps_download_design')) {
            wp_die('Invalid nonce', 403);
        }

        $rawUrl = isset($_GET['url']) ? wp_unslash($_GET['url']) : '';
        $designUrl = esc_url_raw($rawUrl);

        if (
            !$designUrl ||
            !wp_http_validate_url($designUrl) ||
            !$this->isAllowedDesignDownloadUrl($designUrl)
        ) {
            wp_die('Invalid design URL', 400);
        }

        $response = wp_safe_remote_get($designUrl, [
            'timeout' => 30,
            'redirection' => 5,
        ]);

        if (is_wp_error($response)) {
            wp_die('Download failed', 502);
        }

        $statusCode = wp_remote_retrieve_response_code($response);
        if ($statusCode < 200 || $statusCode >= 300) {
            wp_die('Design unavailable', 502);
        }

        $body = wp_remote_retrieve_body($response);
        if ($body === '') {
            wp_die('Empty design file', 502);
        }

        $path = parse_url($designUrl, PHP_URL_PATH);
        $filename = $path ? wp_basename($path) : 'design.png';
        if (!$filename || strpos($filename, '.') === false) {
            $filename = 'design.png';
        }
        $filename = sanitize_file_name($filename);

        $contentType = wp_remote_retrieve_header($response, 'content-type');
        if (!$contentType) {
            $contentType = 'application/octet-stream';
        }

        nocache_headers();
        header('Content-Description: File Transfer');
        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($body));
        echo $body;
        exit;
    }

    private function isAllowedDesignDownloadUrl($url)
    {
        $host = wp_parse_url($url, PHP_URL_HOST);
        if (!$host) {
            return false;
        }
        $host = strtolower($host);

        $allowedHosts = [];

        $configuratorHost = (new DWeb_PS_API())->getConfiguratorHost();
        $configuratorParsedHost = wp_parse_url($configuratorHost, PHP_URL_HOST);
        if ($configuratorParsedHost) {
            $allowedHosts[] = strtolower($configuratorParsedHost);
        }

        $allowedSuffixes = [
            '.3dweb.io',
            '.b-cdn.net',
            '.gumlet.io',
        ];

        if (in_array($host, $allowedHosts, true)) {
            return true;
        }

        foreach ($allowedSuffixes as $suffix) {
            if (substr($host, -strlen($suffix)) === $suffix) {
                return true;
            }
        }

        return false;
    }

    private function getCartItemCustomImageUrl($cart_item)
    {
        if (!is_array($cart_item) || empty($cart_item[self::TEAM_SESSION_REFERENCE])) {
            return null;
        }

        $teamReference = sanitize_text_field($cart_item[self::TEAM_SESSION_REFERENCE]);
        if (!$teamReference) {
            return null;
        }

        return $this->getSessionMainImageUrl($teamReference);
    }

    private function getSessionAssetsByReference($teamReference)
    {
        if (!$teamReference) {
            return null;
        }

        if (isset($this->sessionAssetsCache[$teamReference])) {
            return $this->sessionAssetsCache[$teamReference];
        }

        $response = (new DWeb_PS_API())->getSessionAssets($teamReference);
        if (!$response || isset($response['error']) || isset($response['errors']) || !isset($response['data']) || !is_array($response['data'])) {
            $this->sessionAssetsCache[$teamReference] = null;
            return null;
        }

        $this->sessionAssetsCache[$teamReference] = $response['data'];
        return $this->sessionAssetsCache[$teamReference];
    }

    private function getSessionMainImageUrl($teamReference)
    {
        $assets = $this->getSessionAssetsByReference($teamReference);
        if (!$assets || !isset($assets['main_0'])) {
            return null;
        }

        $mainImage = $assets['main_0'];

        if (is_array($mainImage) && !empty($mainImage['url'])) {
            return esc_url_raw($mainImage['url']);
        }

        if (is_string($mainImage) && filter_var($mainImage, FILTER_VALIDATE_URL)) {
            return esc_url_raw($mainImage);
        }

        return null;
    }

    private function getSessionDesignUrl($teamReference)
    {
        $assets = $this->getSessionAssetsByReference($teamReference);
        if (!$assets || !isset($assets['design']) || !is_array($assets['design']) || empty($assets['design'])) {
            return null;
        }

        $firstDesign = reset($assets['design']);

        if (is_string($firstDesign) && filter_var($firstDesign, FILTER_VALIDATE_URL)) {
            return esc_url_raw($firstDesign);
        }

        if (is_array($firstDesign) && !empty($firstDesign['url']) && filter_var($firstDesign['url'], FILTER_VALIDATE_URL)) {
            return esc_url_raw($firstDesign['url']);
        }

        return null;
    }

    private function buildDesignActionsHtml($url)
    {
        $safeUrl = esc_url($url);
        $downloadUrl = add_query_arg(
            [
                'action' => '3dweb_ps_download_design',
                'url' => $url,
                '_wpnonce' => wp_create_nonce('3dweb_ps_download_design'),
            ],
            admin_url('admin-ajax.php')
        );

        return sprintf(
            '<a href="%1$s" target="_blank" rel="noopener noreferrer">Open design <span class="dashicons dashicons-external" style="font-size:11px;width:11px;height:11px;line-height:11px;vertical-align:middle;margin-left:2px;"></span></a> / <a href="%2$s">Download</a>',
            $safeUrl,
            esc_url($downloadUrl)
        );
    }


    private  function loadSessionUrls()
    {
        if (isset($_GET[self::TEAM_SESSION_REFERENCE]) && !$this->sessionImagesLoaded) {
            $teamReference = sanitize_text_field($_GET[self::TEAM_SESSION_REFERENCE]);
            $response = (new DWeb_PS_API())->getSessionAssets($teamReference);
            if (!$response || isset($response['error']) || isset($response['errors'])) {
                return;
            }
            $this->sessionImagesLoaded = true;

            $this->mainImages = array_map(
                function($key) use ($response) {
                    return $response['data'][$key];
                },
                ['main_0', 'main_90', 'main_180', 'main_270']
            );
        }
    }

    public function handleImageParams($params, $attachment_id)
    {
        $product = wc_get_product();
//        var_dump($params);
//        die('test');
        if (!$product) {
            return $params;
        }

        if (!is_array($params)) {
            return $params;
        }

        $this->loadSessionUrls();
        if (empty($this->mainImages)) {
            return $params;
        }

        $gallery = $product->get_gallery_image_ids();

        // Ensure the product ID matches the current attachment's product
        if ($product->get_id() !== get_post_meta($attachment_id, '_product_id', true) || $product->get_id() !== get_the_ID()) {
            return $params;
        }

//       var_dump($product->get_id(), get_post_meta($attachment_id, '_product_id', true), get_the_ID());

        // add main product id to the gallery
        array_unshift($gallery, $product->get_image_id());

        // Find match by checking if filename exists in the string
        $index = array_search($attachment_id, $gallery);

        if ($index === false) {
            return $params;
        }

        $oldUrl = $params['data-src'];

        // make transparent 1 px imaged

        $polledUrl = null;

        if ($index !== false && isset($this->mainImages[$index])) {
            $polledUrl = $this->mainImages[$index];
        }

        $url = 'https://bordex.gumlet.io/wait.png?attachment_id=' . $attachment_id . '&product_id=' . $product->get_id();

        // Replace the src with your custom URL
        $params['src'] = $this->addHeightToImageUrl($url, 302);

        // If you want to replace srcset and other attributes
        $params['srcset'] = $this->addHeightToImageUrl($url, 600, 600);

        // change thumbnail src
        $params['data-src'] = $this->addHeightToImageUrl($url, 210);
        $params['data-large_image'] = $url;

        $params['data-cnf3dweb_url'] = $polledUrl;
        $params['data-cnf3dweb_url_old'] = $oldUrl;

        return $params;
    }


    public function handleImageParamsThumb($image, $attachment_id, $size, $icon)
    {
        if (!$this->getTeamReference()) {
            return $image;
        }

        $product = wc_get_product();
        if (!$product) {
            return $image;
        }

//        // Ensure the product ID matches the current attachment's product
        if ($product->get_id() !== get_post_meta($attachment_id, '_product_id', true)) {
            return $image;
        }

        $gallery = $product->get_gallery_image_ids();

        // add main product id to the gallery
        array_unshift($gallery, $product->get_image_id());

        // Find match by checking if filename exists in the string
        $index = array_search($attachment_id, $gallery);

        if ($index === false) {
            return $image;
        }

        $image[0] = 'https://bordex.gumlet.io/wait.gif?attachment_id=' . $attachment_id;

        return $image;
    }


    private function addHeightToImageUrl($url, $height, $width = null)
    {
        if ($width) {
            return add_query_arg([
                'h' => $height,
                'w' => $width,
                'mode' => 'fill'
            ], $url);
        }

        return add_query_arg([
            'h' => $height
        ], $url);
    }

}
