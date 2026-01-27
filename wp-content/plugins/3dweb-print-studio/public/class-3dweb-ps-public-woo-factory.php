<?php

class DWeb_PS_Public_Woo_Factory
{
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of the plugin.
     * @param string $version The version of this plugin.
     * @since    1.0.0
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function create($theme)
    {
        require_once dirname(__FILE__) . '/themes/class-3dweb-ps-public-woo-base.php';

        switch ($theme) {
            case 'astra':
                require_once dirname(__FILE__) . '/themes/astra/class-3dweb-ps-public-woo-astra.php';
                return new DWeb_PS_Public_Woo_Astra($this->plugin_name, $this->version);
            default:
                require_once dirname(__FILE__) . '/themes/astra/class-3dweb-ps-public-woo-astra.php';
                return new DWeb_PS_Public_Woo_Astra($this->plugin_name, $this->version);
        }
    }
}