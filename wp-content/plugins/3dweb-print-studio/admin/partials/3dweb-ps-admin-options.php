<?php

/** @var string $license */

/** @var string $source */


include('header.php');
include('3dweb-ps-settings-helper.php');

?>
    <div class="3dweb-ps__settings">
        <h2>Options</h2>
        <p>Options how to handle the 3D configurator.
        </p>

        <form method='post' data-source="<?php echo esc_attr(DWeb_PS_ADMIN_OPTIONS::PATH); ?>">

            <div class="3dweb-ps__settings__table">
                <?php
                $label = DWeb_PS_JAVASCRIPTVIEWER::javascriptviewerIsActive() ? 'Use 360 viewer' : 'Use 360 viewer (requires 360 Javascript Viewer!)';

                echo dwebps_setting_create_row(
                    $label,
                    '',
                    DWeb_PS_ADMIN_OPTIONS::CONFIGURATOR_USE_THREESIXTY,
                    get_option(DWeb_PS_ADMIN_OPTIONS::CONFIGURATOR_USE_THREESIXTY, false),
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