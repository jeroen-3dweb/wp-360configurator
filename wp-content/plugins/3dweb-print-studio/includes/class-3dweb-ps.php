<?php

class DWeb_PS
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
     * @since 1.0.0
     */
    public function __construct($version)
    {
        $this->version = $version;

        $this->pluginName = '3DWeb Print Studio';

        $this->loadDependencies();
        $this->definePublicHooks();
        $this->define_admin_hooks();

        $this->loadPluginHooks();
    }

    /**
     * Load dependencies
     *
     * @since    1.0.0
     * @access   private
     */
    private function loadDependencies()
    {
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-3dweb-ps-loader.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-3dweb-ps-public.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-3dweb-ps-admin.php';

        //  Plugins
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/woo/class-3dweb-ps-woo.php'; //woo
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/configurator/class-3dweb-ps-api.php'; //API Configurator
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/javascriptviewer/class-3dweb-ps-javascriptviewer.php'; //Javascript Viewer

        $this->loader = new DWeb_PS_Loader();
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
        $plugin_admin = new DWeb_PS_Admin($this->pluginName, $this->version);

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

        $this->loader->add_action('admin_menu', $plugin_admin, 'loadPageMenu');

        register_activation_hook(DWEBPS_MAIN_URL, [$plugin_admin, 'activation']);
        register_deactivation_hook(DWEBPS_MAIN_URL, [$plugin_admin, 'de_activation']);

        $this->loader->add_action('admin_init', $plugin_admin, 'load_startup');
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
        $pluginPublic = new DWeb_PS_Public($this->pluginName, $this->version);

        $this->loader->add_action('wp_enqueue_scripts', $pluginPublic, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $pluginPublic, 'enqueue_scripts');

    }

    /**
     * @since 1.0.0
     */
    public function run()
    {
        $this->loader->run();
    }

    private function loadPluginHooks()
    {
        // Woocommerce
        if (DWeb_PS_WOO::woocommerceIsActive()) {
            (new DWeb_PS_WOO($this->version, $this->pluginName, $this->loader))->run();
        }
    }
}