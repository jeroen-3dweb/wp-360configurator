<?php

class DWeb_PS_Admin
{
    const REDIRECT_OPTION_NAME = 'DWEB_PS_do_activation_redirect';

    const PLUGIN_MENU_SLUG = '3dweb-ps-main-settings';

    private $pluginName;

    private $version;
    private $pages = [];


    /**
     * DWeb_PS_Admin constructor.
     * @param $pluginName
     * @param $version
     */
    public function __construct($pluginName, $version)
    {
        $this->pluginName = $pluginName;
        $this->version    = $version;
        $this->loadPages();
        $this->loadHooks();
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
        wp_enqueue_style(
            $this->pluginName,
            plugin_dir_url(__FILE__) . 'scss/3dweb-ps-admin.css',
            array(),
            $this->version,
            'all'
        );
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts($hook)
    {
        if (!did_action('wp_enqueue_media')) {
            wp_enqueue_media();
        }
        wp_enqueue_script('3dweb-ps-admin', plugin_dir_url(__FILE__) . 'js/admin.js', array('jquery'), $this->version);

        if(strpos($hook, 'page_3dweb-ps-') === false && $hook !== 'post.php' && $hook !== 'post-new.php'){
            return;
        }

        wp_localize_script(
            '3dweb-ps-admin',
            'jsvUpload',
            [
                'ajaxUrl'  => admin_url('admin-ajax.php'),
                'security' => wp_create_nonce('jsv_save_setting'),
            ]
        );
    }


    public function loadPageMenu()
    {
        /** @var DWeb_PS_ADMIN_PAGE_ABSTRACT $page */
        foreach ($this->pages as $page) {
            $page->loadMenuItem(self::PLUGIN_MENU_SLUG);
        }
    }
    public function loadHooks()
    {
        /** @var DWeb_PS_ADMIN_PAGE_ABSTRACT $page */
        foreach ($this->pages as $page) {
            $page->loadHooks();
        }
    }

    public function load_startup()
    {
        if (get_option(self::REDIRECT_OPTION_NAME, false)) {
            delete_option(self::REDIRECT_OPTION_NAME);
            if (!isset($_GET['activate-multi'])) {
                wp_redirect("admin.php?page=3dweb-ps-main-settings");
            }
        }
    }

    public function activation()
    {
        add_option(self::REDIRECT_OPTION_NAME, true);
    }

    public function de_activation()
    {
        delete_option(self::REDIRECT_OPTION_NAME);
    }

    /**
     * Load pages for the admin menu
     */
    private function loadPages()
    {
        $path = plugin_dir_path(__FILE__) . 'pages/';
        require_once $path . 'class-3dweb-ps-admin_page_abstract.php';
        require_once $path . 'class-3dweb-ps-admin_page_about.php';
        require_once $path . 'class-3dweb-ps-admin_page_API.php';
        require_once $path . 'class-3dweb-ps-admin_page_options.php';

        $this->pages = [
            new DWeb_PS_ADMIN_ABOUT(),
            new DWeb_PS_ADMIN_API(),
            new DWeb_PS_ADMIN_OPTIONS(),
        ];
    }
}