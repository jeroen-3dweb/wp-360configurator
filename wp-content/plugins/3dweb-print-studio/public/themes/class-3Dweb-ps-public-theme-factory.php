<?php

class DWeb_PS_Public_Theme_Factory
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

	    $this->loadBaseWooFile();
    }

	private function loadWooTheme($theme)
	{
		$theme = strtolower($theme);
		$theme_class_file = dirname(__FILE__) . '/' . $theme . '/woo/class-3dweb-ps-public-woo-' . $theme . '.php';

		if (file_exists($theme_class_file)) {
			require_once $theme_class_file;
			return true;
		}
		return false;
	}

    public function createWooThemeClass($theme)
    {

		if($this->loadWooTheme($theme)){
			$class_name = 'DWeb_PS_Public_Woo_' . ucfirst(strtolower($theme));
			if (class_exists($class_name)) {
				return new $class_name($this->plugin_name, $this->version);
			}
		}

	    $this->loadWooTheme('default');
	    return new DWeb_PS_Public_Woo_Default($this->plugin_name, $this->version);
    }

	/**
	 * @return void
	 */
	public function loadBaseWooFile() {
		require_once dirname( __FILE__ ) . '/class-3dweb-ps-public-woo-base.php';
	}
}