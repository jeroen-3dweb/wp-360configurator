<?php
/*
Plugin Name: 3DWeb Print Studio
Plugin URI: https://3dweb.io
Description: Configure your products in 3D
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
$dwebpsVersion = '1.0.0';
define('DWEBPS_VERSION', $dwebpsVersion);
define('DWEBPS_PATH', plugin_dir_path(__FILE__));
define('DWEBPS_MAIN_URL', __FILE__);
define('DWEBPS_DOMAIN', '3dweb-ps');

require plugin_dir_path(__FILE__) . 'includes/class-3dweb-ps.php';


function run_dwebps($version)
{
    (new DWeb_PS($version))->run();
}

run_dwebps($dwebpsVersion);