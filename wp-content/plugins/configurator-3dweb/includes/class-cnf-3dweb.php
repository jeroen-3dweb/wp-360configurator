<?php

class CNF_3Dweb
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
     * @var CNF_3Dweb_Loader
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

        $this->pluginName = '3dweb configurator';

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
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-cnf-3dweb-loader.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-cnf-3dweb-public.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-cnf-3dweb-admin.php';

        //  Plugins
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/woo/class-cnf-3dweb-woo.php'; //woo
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/configurator/class-cnf-3dweb-api.php'; //API Configurator
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/javascriptviewer/class-cnf-3dweb-javascriptviewer.php'; //Javascript Viewer

        $this->loader = new CNF_3Dweb_Loader();
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
        $plugin_admin = new CNF_3DWeb_Admin($this->pluginName, $this->version);

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

        $this->loader->add_action('admin_menu', $plugin_admin, 'loadPageMenu');

        register_activation_hook(CNF3DWEB_MAIN_URL, [$plugin_admin, 'activation']);
        register_deactivation_hook(CNF3DWEB_MAIN_URL, [$plugin_admin, 'de_activation']);

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
        $pluginPublic = new CNF_3DWeb_Public($this->pluginName, $this->version);

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
        if (CNF_3DWeb_WOO::woocommerceIsActive()) {
            (new CNF_3DWeb_WOO($this->version, $this->pluginName, $this->loader))->run();
        }
    }
}