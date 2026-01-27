<?php

/** @var string $license */

/** @var string $source */

include('header.php');

?>
    <div class="3dweb-ps__settings">
        <h2>3D Print Studio</h2>
        <p>
            Configure your products in 3D
        </p>

        <h4>basic</h4>
        <ul>
            <li>Version: <?php echo esc_html(DWEBPS_VERSION); ?></li>
            <li>License: <?php echo esc_html(get_option(DWeb_PS_ADMIN_API::TOKEN, 'Free version')); ?></li>
        </ul>
        <h4>plugins</h4>
        <ul>
            <li>WooCommerce (<?php echo DWeb_PS_WOO::woocommerceIsActive() ? 'active' : "not active, it won't work without it"; ?>)</li>
            <li>360 Javascript Viewer (<?php echo DWeb_PS_JAVASCRIPTVIEWER::javascriptviewerIsActive() ? 'active' : 'not active'; ?>)</li>
        </ul>
        <h4>Theme</h4>
        <ul>
            <li><?php echo esc_html(wp_get_theme()->get('Name')); ?></li>
        </ul>
    </div>

<?php
include('footer.php'); ?>