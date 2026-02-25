<?php

/** @var string $license */

/** @var string $source */


include('header.php');
include('3dweb-ps-settings-helper.php');

?>
    <div class="dweb_ps__settings">
        <h2>Options</h2>
        <p>Options how to handle the 3D configurator.
        </p>

        <form method='post' data-source="<?php echo esc_attr(DWeb_PS_ADMIN_OPTIONS::PATH); ?>">

            <div class="dweb_ps__settings__table">
                <?php
                $is_active = DWeb_PS_JAVASCRIPTVIEWER::javascriptviewerIsActive();
                $label = $is_active ? 'Use 360 viewer' : 'Use 360 viewer';
                $description = $is_active ? 'Use 360 viewer to show 360 result of the model with print.' : 'You must activate the 360 viewer plugin to use this feature.';

                echo dwebps_setting_create_row(
                    $label,
                    $description,
                    DWeb_PS_ADMIN_OPTIONS::CONFIGURATOR_USE_THREESIXTY,
                    get_option(DWeb_PS_ADMIN_OPTIONS::CONFIGURATOR_USE_THREESIXTY, false),
                    'checkbox',
                    !$is_active
                );

                echo dwebps_setting_create_row(
                    'Debug mode',
                    'Show debug information in the console.',
                    DWeb_PS_ADMIN_OPTIONS::CONFIGURATOR_DEBUG,
                    get_option(DWeb_PS_ADMIN_OPTIONS::CONFIGURATOR_DEBUG, false),
                    'checkbox'
                );

                echo dwebps_setting_create_row(
                    'Start button text',
                    'Text on the button when the product is configurable',
                    DWeb_PS_ADMIN_OPTIONS::CONFIGURATOR_START_BUTTON_TEXT,
                    get_option(DWeb_PS_ADMIN_OPTIONS::CONFIGURATOR_START_BUTTON_TEXT, 'Start configuration'),
                    'text'
                );

                echo dwebps_setting_create_row(
                    'Session closed text',
                    'Text shown below the button after the session is closed',
                    DWeb_PS_ADMIN_OPTIONS::CONFIGURATOR_SESSION_CLOSED_TEXT,
                    get_option(DWeb_PS_ADMIN_OPTIONS::CONFIGURATOR_SESSION_CLOSED_TEXT, 'Design: {reference}'),
                    'text'
                );
                ?>
            </div>
        </form>
        <?php
        include('button.php'); ?>
    </div>
<?php
include('footer.php'); ?>