<?php

class DWeb_PS_JAVASCRIPTVIEWER
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
     *  constructor.
     *
     * @param $version
     * @param $pluginName
     * @param DWeb_PS_Loader $loader
     * @since 1.0.0
     */
    public function __construct($version, $pluginName, DWeb_PS_Loader $loader)
    {
        $this->version    = $version;
        $this->pluginName = $pluginName;

        $this->loader = $loader;
    }

    /**
     * @return bool
     */
    public static function javascriptviewerIsActive()
    {
        return in_array('360deg-javascript-viewer/360-jsv.php', apply_filters('active_plugins', get_option('active_plugins')));
    }
}