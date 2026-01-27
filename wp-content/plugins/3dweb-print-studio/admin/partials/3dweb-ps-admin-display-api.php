<?php

/** @var string $license */

/** @var string $source */


include('header.php');
include('3dweb-ps-settings-helper.php');

?>
    <div class="3dweb-ps__settings">
        <h2>API Credentials</h2>
        <p>Enter your API credentials to enable the plugin.
        </p>

        <form method='post' data-source="<?php echo esc_attr(DWeb_PS_ADMIN_API::PATH); ?>">

            <div class="3dweb-ps__settings__table">
                <?php echo dwebps_setting_create_row(
                        'Token',
                        '',
                        DWeb_PS_ADMIN_API::TOKEN,
                        get_option(DWeb_PS_ADMIN_API::TOKEN, ''),
                        'text'
                );
                ?> <?php echo dwebps_setting_create_row(
                        'Configurator Host',
                        '',
                        DWeb_PS_ADMIN_API::CONFIGURATOR_HOST,
                        get_option(DWeb_PS_ADMIN_API::CONFIGURATOR_HOST, ''),
                        'text'
                ); ?>
                <?php echo dwebps_setting_create_select(
                        'API Version',
                        '',
                        DWeb_PS_ADMIN_API::CONFIGURATOR_HOST_VERSION,
                        get_option(DWeb_PS_ADMIN_API::CONFIGURATOR_HOST_VERSION, ''),
                        [
                                ['label' => 'v1', 'value' => 'v1'],
                        ]
                );
                ?>

                <div class="3dweb-ps__settings__row">
                    <div class="3dweb-ps__settings__label">
                        <a id="3dweb-ps-test-auth" class="3dweb-ps__button 3dweb-ps__button--normal">Test credentials</a>
                    </div>
                    <div class="3dweb-ps__settings-holder">
                        <small id="3dweb-ps__check-auth-result" class=""></small>
                    </div>
                </div>
            </div>
        </form>
        <?php
        include('button.php'); ?>

    </div>
<?php
include('footer.php'); ?>