<?php
/*
Plugin Name: 3DWeb Print Studio
Plugin URI: https://3dweb.io
Description: Design your print in real-time 3D. Visualize, customize and approve print designs instantly — fully branded and easy to integrate.
Author: Jeroen Termaat
Author URI: https://3dweb.nl
Developer: Jeroen Termaat
Developer URI: https://3dweb.nl
Version: 1.0.0
Last Modified: 2026-01-27
License: GPLv2
*/
if (!defined('ABSPATH')) {
    exit;
}
$dweb_PSVersion = '1.0.0';
define('DWEB_PS_VERSION', $dweb_PSVersion);
define('DWEB_PS_PATH', plugin_dir_path(__FILE__));
define('DWEB_PS_MAIN_URL', __FILE__);
define('DWEB_PS_DOMAIN', '3dweb-ps');

require plugin_dir_path(__FILE__) . 'includes/class-3dweb-ps.php';


function run_dweb_PS($version)
{
    (new DWeb_PS($version))->run();
}

run_dweb_PS($dweb_PSVersion);