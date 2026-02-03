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

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.5.0
     */
    protected function enqueue_scripts_based_on_theme($theme)
    {
        $theme_js_path = sprintf('%s/%s/woo_%s.js', plugin_dir_url(__FILE__), $theme, $theme);
        $theme_js_file = sprintf('%s/%s/woo_%s.js', plugin_dir_path(__FILE__), $theme, $theme);

        // Fallback to default theme if file doesn't exist
        if (!file_exists($theme_js_file)) {
            $theme = 'default';
            $theme_js_path = sprintf('%s/%s/woo_%s.js', plugin_dir_url(__FILE__), $theme, $theme);
        }

        // Enqueue core JS first
        wp_enqueue_script(
            $this->plugin_name . '_public_core',
            plugin_dir_url(dirname(__FILE__)) . 'js/3dweb-ps-public.js',
            array('jquery', 'javascriptviewer'),
            $this->version,
            true
        );

        // Enqueue theme-specific JS
        wp_enqueue_script(
            $this->plugin_name . '_woo_js',
            $theme_js_path,
            array($this->plugin_name . '_public_core'),
            $this->version,
            true
        );
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.5.0
     */
    public function enqueue_styles_based_on_theme($theme)
    {
        $theme_css_path = sprintf('%s/%s/woo_%s.css', plugin_dir_url(__FILE__), $theme, $theme);
        $theme_css_file = sprintf('%s/%s/woo_%s.css', plugin_dir_path(__FILE__), $theme, $theme);

        // Fallback to default theme if file doesn't exist
        if (!file_exists($theme_css_file)) {
            $theme = 'default';
            $theme_css_path = sprintf('%s/%s/woo_%s.css', plugin_dir_url(__FILE__), $theme, $theme);
        }

        wp_enqueue_style(
            $this->plugin_name . '_woo_css',
            $theme_css_path,
            array(),
            $this->version,
            'all'
        );
    }

    public function enqueue_styles()
    {
        $this->enqueue_styles_based_on_theme(static::THEME_NAME);;
    }

    public function getProductId()
    {
        global $post;
        return sanitize_meta(DWeb_PS_WOO_METABOX::FIELD_PRODUCT_ID, get_post_meta($post->ID, DWeb_PS_WOO_METABOX::FIELD_PRODUCT_ID, true), 'post');
    }

    public function enqueue_scripts()
    {
        global $post;
        $this->enqueue_scripts_based_on_theme(static::THEME_NAME);
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
		        'startConfiguration' => __('Start configuration', '3dweb-print-studio'),
		        'loading' => __('Loading 3...', '3dweb-print-studio'),
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
            $item->add_meta_data(self::TEAM_SESSION_REFERENCE, $values[self::TEAM_SESSION_REFERENCE], true);
        }
        return $item;
    }

    public function handleChangeCartImage($image, $cart_item, $cart_item_key)
    {
//
//                $this->pretty_var_dump($image);
//                $this->pretty_var_dump($cart_item);
        if (isset($cart_item[self::TEAM_SESSION_REFERENCE]) && !empty($cart_item[self::TEAM_SESSION_REFERENCE])) {

        }
        return $image;
    }


    private function loadSessionUrls()
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