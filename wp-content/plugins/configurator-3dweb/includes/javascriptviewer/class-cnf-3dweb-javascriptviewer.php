<?php

class CNF_3DWeb_JAVASCRIPTVIEWER
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
     * CNF constructor.
     *
     * @param $version
     * @param $pluginName
     * @param CNF_3Dweb_Loader $loader
     * @since 1.0.0
     */
    public function __construct($version, $pluginName, CNF_3Dweb_Loader $loader)
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