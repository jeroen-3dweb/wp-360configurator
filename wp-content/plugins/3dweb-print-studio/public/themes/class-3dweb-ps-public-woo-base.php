<?php

class DWeb_PS_Public_Woo_Base
{
    const THEME_NAME = 'base';

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

        add_action('wp_ajax_cf3dweb_action_get_endpoint', [CNF_3DWeb_Public_Woo_Base::class, 'handle_get_endpoint_request']);
        add_action('wp_ajax_nopriv_cf3dweb_action_get_endpoint', [CNF_3DWeb_Public_Woo_Base::class, 'handle_get_endpoint_request']);

    }

    protected function generateId()
    {
        $permitted_chars = implode('', range('a', 'z'));
        return 'cnf-' . '-' . substr(str_shuffle($permitted_chars), 0, 10);
    }

    protected function getBBCode()
    {
        /** @var WC_Product $product */
        global $product;
        return $product ? $product->get_meta(CNF_3DWeb_WOO_METABOX::FIELD_PRODUCT_ID) ?: null : null;
    }


    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.5.0
     */
    protected function enqueue_scripts_based_on_theme($theme)
    {
        $path = sprintf('%s/%s/woo_%s.js', plugin_dir_url(__FILE__), $theme, $theme);

        wp_enqueue_script(
            $this->plugin_name . '_woo_js',
            $path,
            array('javascriptviewer'),
            $this->version
        );
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.5.0
     */
    public function enqueue_styles_based_on_theme($theme)
    {
        $path = sprintf('%s/%s/woo_%s.css', plugin_dir_url(__FILE__), $theme, $theme);
        wp_enqueue_style(
            $this->plugin_name . '_woo_css',
            $path,
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
        return sanitize_meta(CNF_3DWeb_WOO_METABOX::FIELD_PRODUCT_ID, get_post_meta($post->ID, CNF_3DWeb_WOO_METABOX::FIELD_PRODUCT_ID, true), 'post');
    }

    public function enqueue_scripts()
    {
        global $post;
        $this->enqueue_scripts_based_on_theme(static::THEME_NAME);
        $productID = sanitize_meta(CNF_3DWeb_WOO_METABOX::FIELD_PRODUCT_ID, get_post_meta($post->ID, CNF_3DWeb_WOO_METABOX::FIELD_PRODUCT_ID, true), 'post');

        $assets = [];
        $teamReference = $this->getTeamReference();
        if ($teamReference) {
            $response = (new CNF_3DWeb_API())->getSessionAssets($teamReference);
            $assets = $response['data'];
        }
        $threeSixtyConfig = [];
        if (CNF_3DWeb_JAVASCRIPTVIEWER::javascriptviewerIsActive()) {
            $threeSixtyConfig = [
                'license' => get_option(JSV_360_ADMIN_LICENSE::NOTIFIER_LICENSE, null),
                'autoRotate' => get_option(JSV_360_ADMIN_AUTOROTATE::AUTOROTATE, null)
            ];
        }

        // hook for the endpoint request
        wp_localize_script($this->plugin_name . '_woo_js', 'cnf3Dweb', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'security' => wp_create_nonce('cnf-3dweb-nonce'),
            'action' => 'cf3dweb_action_get_endpoint',
            'product_id' => $productID,
            'assets' => $assets,
            'team_reference' => $this->getTeamReference(),
            'useThreeSixtyView' => get_option(CNF_3DWeb_ADMIN_OPTIONS::CONFIGURATOR_USE_THREESIXTY, false),
            'threeSixtyConfig' => $threeSixtyConfig
        ));
    }


    private function getSession()
    {
        $productId = sanitize_text_field($_POST['product_id']);
        $callbackUrl = sanitize_text_field($_POST['post_url']);

        return (new CNF_3DWeb_API())->createNewSession($productId, $callbackUrl);
    }

    public function handle_get_endpoint_request()
    {
        check_ajax_referer('cnf-3dweb-nonce', 'security');

        $method = sanitize_text_field($_POST['method']);

        switch ($method) {

            case 'get_session':
                $response = $this->getSession();
                break;

            case 'get_assets':
                $self = new self('cnf-3dweb', '1.0.0');
                $self->loadSessionUrls();
                $response = $self->mainImages;
                break;

            default:
                $response = false;
                break;
        }


        if ($response === false || is_wp_error($response)) {
            $error_message = 'Something went wrong';
            if (is_wp_error($response)) {
                $error_data = $response->get_error_data();
                if (is_array($error_data) && isset($error_data['message'])) {
                    $error_message = $error_data['message'];
                } elseif (is_array($error_data) && isset($error_data['error'])) {
                    $error_message = $error_data['error'];
                } else {
                    $error_message = $response->get_error_message();
                }
            }
            wp_send_json_error(['message' => $error_message]);
        }

        wp_send_json_success($response);
    }

    private function getTeamReference()
    {
        return $_GET['teamSessionReference'] ?? null;
    }


    private function pretty_var_dump($data)
    {
        echo '<pre>';
        var_dump($data);
        echo '</pre>';
    }

    public function handleAddCustomHiddenField()
    {
        if (isset($_GET['teamSessionReference'])) {
            echo '<input type="hidden" name="teamSessionReference" value="' . esc_attr($_GET['teamSessionReference']) . '">';
        }
    }

    public function handleAddToCartItem($cart_item_data, $productID)
    {
        $hasTeamSessionReference = isset($_POST['teamSessionReference']);

        if ($hasTeamSessionReference && $productID) {
            $cart_item_data['teamSessionReference'] = $_POST['teamSessionReference'];
        }
        return $cart_item_data;
    }


    public function handleGetItemData($item_data, $cart_item)
    {
//        $this->pretty_var_dump($cart_item);
//        $this->pretty_var_dump( $item_data);
        if (isset($cart_item['teamSessionReference'])) {
            $item_data[] = array(
                'key' => 'reference',
                'value' => wc_clean($cart_item['teamSessionReference'])
            );
//
//            $item_data[] = array(
//                'key' => 'test',
//                'value' => '<img src="https://bordex.gumlet.io/organisations/01939c63-53a6-f69d-3f3b-080ed0aaffdd/sessions/67d2e255c77d2/generated/360/67d2e255c77d2_16.png?height=400&width=480&t=1741879316293"/>'
//            );
        }
        return $item_data;
    }

    public function handleCreateOrderLineItem($item, $cart_item_key, $values, $order)
    {
        if (isset($values['teamSessionReference'])) {
            $item->add_meta_data('teamSessionReference', $values['teamSessionReference'], true);
        }
        return $item;
    }

    public function handleChangeCartImage($image, $cart_item, $cart_item_key)
    {
//
//                $this->pretty_var_dump($image);
//                $this->pretty_var_dump($cart_item);
        if (isset($cart_item['teamSessionReference']) && !empty($cart_item['teamSessionReference'])) {

        }
        return $image;
    }


    private function loadSessionUrls()
    {
        $hasTeamSessionReference = isset($_GET['teamSessionReference']);
        if ($hasTeamSessionReference && !$this->sessionImagesLoaded) {
            $teamReference = sanitize_text_field($_GET['teamSessionReference']);
            $response = (new CNF_3DWeb_API())->getSessionAssets($teamReference);
            if (!$response) {
                return;
            }
            $this->sessionImagesLoaded = true;

            $this->mainImages = array_map(
                fn($key) => $response['data'][$key],
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