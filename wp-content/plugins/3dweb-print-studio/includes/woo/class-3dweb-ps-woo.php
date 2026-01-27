<?php

class DWeb_PS_WOO
{
    /**
     * @var string
     */
    private $version;

    /**
     * @var string
     */
    private $pluginName;

    /**
     * @var DWeb_PS_Loader
     */
    private $loader;

    /**
     * JSV constructor.
     *
     * @param $version
     * @param $pluginName
     * @param DWeb_PS_Loader $loader
     * @since 1.0.0
     */
    public function __construct($version, $pluginName, DWeb_PS_Loader $loader)
    {
        $this->version = $version;
        $this->pluginName = $pluginName;

        $this->loader = $loader;
    }

    /**
     * Load dependencies
     *
     * @since    1.0.0
     * @access   private
     */
    private function loadDependencies()
    {
        require_once plugin_dir_path(dirname(__FILE__)) . '/../public/class-3dweb-ps-public-woo-factory.php';
        require_once plugin_dir_path(dirname(__FILE__)) . '/woo/class-3dweb-ps-woo-metabox.php';
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks()
    {
        $metaBox = new DWeb_PS_WOO_METABOX($this->pluginName, $this->version);
        $this->loader->add_action('add_meta_boxes', $metaBox, 'addBoxes');
        $this->loader->add_action('save_post', $metaBox, 'saveBoxes');
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function definePublicHooks()
    {
        $currentTheme = wp_get_theme();
        $pluginPublicWoo = (new DWeb_PS_Public_Woo_Factory($this->pluginName, $this->version))->create($currentTheme->get('Name'));

        $this->loader->add_filter('woocommerce_cart_item_thumbnail', $pluginPublicWoo, 'handleChangeCartImage', 1, 3);
        $this->loader->add_filter('woocommerce_before_add_to_cart_button', $pluginPublicWoo, 'handleAddCustomHiddenField',10,0);
        $this->loader->add_filter('woocommerce_add_cart_item_data', $pluginPublicWoo, 'handleAddToCartItem',10,2);
        $this->loader->add_filter('woocommerce_checkout_create_order_line_item', $pluginPublicWoo, 'handleCreateOrderLineItem', 10, 4);
        $this->loader->add_filter('woocommerce_get_item_data', $pluginPublicWoo, 'handleGetItemData', 10, 2);

        $this->loader->add_filter('woocommerce_gallery_image_html_attachment_image_params', $pluginPublicWoo, 'handleImageParams',999,2);
        $this->loader->add_filter('wp_get_attachment_image_src', $pluginPublicWoo, 'handleImageParamsThumb',999,4);

        $this->loader->add_action('wp_enqueue_scripts', $pluginPublicWoo, 'enqueue_scripts');
        $this->loader->add_action('wp_enqueue_scripts', $pluginPublicWoo, 'enqueue_styles');
    }


    /**
     * @since 1.0.0
     */
    public function run()
    {
        $this->loadDependencies();
        $this->definePublicHooks();
        $this->define_admin_hooks();
    }

    /**
     * @return bool
     */
    public static function woocommerceIsActive()
    {
        return in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')));
    }
}