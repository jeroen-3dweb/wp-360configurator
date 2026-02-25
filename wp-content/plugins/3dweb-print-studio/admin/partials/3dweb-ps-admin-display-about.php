<?php

/** @var string $license */

/** @var string $source */

include('header.php');

?>
    <div class="dweb_ps__settings">
        <div class="dweb_ps__settings__holder">
            <a href="<?php echo esc_url(admin_url('admin.php?page=3dweb-ps-api-settings')); ?>" class="dweb_ps__button dweb_ps__button--normal" style="background-color: #a5c100; color: white; font-weight: 600;">API Settings</a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=3dweb-ps-options')); ?>" class="dweb_ps__button dweb_ps__button--normal" style="background-color: #a5c100; color: white; font-weight: 600;">Options</a>
        </div>

        <h2>3DWeb Print Studio</h2>
        <p>
            3DWeb Print Studio turns print customization into a real-time 3D experience — fully brandable, easy to integrate, and built for modern B2B and B2C workflows.<br>
            <a href="https://3dweb.io" target="_blank">Learn more</a>
        </p>

        <hr>

        <h3>Basic</h3>
        <ul>
            <li>Version: <?php echo esc_html(DWEB_PS_VERSION); ?></li>
        </ul>

        <hr>

        <h3>E-Commerce</h3>
        <ul>
            <li>WooCommerce (<?php echo DWeb_PS_WOO::woocommerceIsActive() ? 'active' : "not active, it won't work without it"; ?>)</li>
        </ul>

        <hr>

        <h3>Extra</h3>
        <ul>
            <li>360 Javascript Viewer <small>(used for 360 result of model with print)</small> (<?php echo DWeb_PS_JAVASCRIPTVIEWER::javascriptviewerIsActive() ? 'active' : 'not active'; ?>)</li>
        </ul>

        <hr>

        <h3>Current Theme</h3>
        <ul>
            <li><?php echo esc_html(wp_get_theme()->get('Name')); ?></li>
        </ul>

    </div>

<?php
include('footer.php'); ?>