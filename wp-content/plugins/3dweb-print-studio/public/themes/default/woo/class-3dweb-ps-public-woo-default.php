<?php

class DWeb_PS_Public_Woo_Default extends DWeb_PS_Public_Woo_Base
{
    const THEME_NAME = 'default';
	
	public function __construct( $plugin_name, $version ) {
		parent::__construct( $plugin_name, $version );
	}
	
	public function loadExtraStyles() {
		wp_enqueue_style(
			$this->plugin_name . '_woo_css',
			plugin_dir_url(__FILE__) . 'woo_default.css',
			array(),
			$this->version,
			'all'
		);
	}

	public function loadExtraScripts()
	{
		wp_enqueue_script(
			$this->plugin_name . '_woo_js',
			plugin_dir_url(__FILE__) . 'woo_default.js',
			array($this->plugin_name . '_public_core'),
			$this->version,
			true
		);

	}
}