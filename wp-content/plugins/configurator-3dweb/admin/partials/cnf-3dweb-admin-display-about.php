<?php

/** @var string $license */

/** @var string $source */

include('header.php');

?>
    <div class="cnf-3dweb__settings">
        <h2>3D Configurator</h2>
        <p>
            Configure your products in 3D
        </p>

        <h4>basic</h4>
        <ul>
            <li>Version: <?php echo esc_html(CNF3DWEB_VERSION); ?></li>
            <li>License: <?php echo esc_html(get_option(CNF_3DWeb_ADMIN_API::TOKEN, 'Free version')); ?></li>
        </ul>
        <h4>plugins</h4>
        <ul>
            <li>WooCommerce (<?php echo CNF_3DWeb_WOO::woocommerceIsActive() ? 'active' : "not active, it won't work without it"; ?>)</li>
            <li>360 Javascript Viewer (<?php echo CNF_3DWeb_JAVASCRIPTVIEWER::javascriptviewerIsActive() ? 'active' : 'not active'; ?>)</li>
        </ul>
        <h4>Theme</h4>
        <ul>
            <li><?php echo esc_html(wp_get_theme()->get('Name')); ?></li>
        </ul>
    </div>

<?php
include('footer.php'); ?>