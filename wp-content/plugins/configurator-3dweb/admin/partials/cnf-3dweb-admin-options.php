<?php

/** @var string $license */

/** @var string $source */


include('header.php');
include('cnf-3dweb-settings-helper.php');

?>
    <div class="cnf-3dweb__settings">
        <h2>Options</h2>
        <p>Options how to handle the 3D configurator.
        </p>

        <form method='post' data-source="<?php echo esc_attr(CNF_3DWeb_ADMIN_OPTIONS::PATH); ?>">

            <div class="cnf-3dweb__settings__table">
                <?php
                $label = CNF_3DWeb_JAVASCRIPTVIEWER::javascriptviewerIsActive() ? 'Use 360 viewer' : 'Use 360 viewer (requires 360 Javascript Viewer!)';

                echo cnf_3dweb_setting_create_row(
                    $label,
                    '',
                    CNF_3DWeb_ADMIN_OPTIONS::CONFIGURATOR_USE_THREESIXTY,
                    get_option(CNF_3DWeb_ADMIN_OPTIONS::CONFIGURATOR_USE_THREESIXTY, false),
                    'checkbox'
                );
                ?>
            </div>
        </form>
        <?php
        include('button.php'); ?>
    </div>
<?php
include('footer.php'); ?>