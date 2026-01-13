<?php
/*
Plugin Name: 3DWeb Configurator
Plugin URI: https://3dweb.io
Description: Configure your products in 3D
Author: Jeroen Termaat
Author URI: https://3dweb.nl
Developer: Jeroen Termaat
Developer URI: https://3dweb.nl
Version: 1.0.0
Last Modified: 2024-13-01
License: GPLv2
*/
if (!defined('ABSPATH')) {
    exit;
}
$cnf3dwebVersion = '1.0.0';
define('CNF3DWEB_VERSION', $cnf3dwebVersion);
define('CNF3DWEB_PATH', plugin_dir_path(__FILE__));
define('CNF3DWEB_MAIN_URL', __FILE__);
define('CNF3DWEB_DOMAIN', 'cnf-3dweb');

require plugin_dir_path(__FILE__) . 'includes/class-cnf-3dweb.php';


function run_cnf3dweb($version)
{
    (new CNF_3Dweb($version))->run();
}

run_cnf3dweb($cnf3dwebVersion);