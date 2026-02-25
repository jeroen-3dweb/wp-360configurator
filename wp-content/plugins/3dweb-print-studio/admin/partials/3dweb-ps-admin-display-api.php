<?php

/** @var string $license */

/** @var string $source */


include('header.php');
include('3dweb-ps-settings-helper.php');

?>
    <div class="dweb_ps__settings">
        <h2>API Credentials</h2>
        <p>
            Enter your API credentials to enable the plugin.<br>
            Don't have credentials yet?
            You can obtain your API credentials from <a href="https://3dweb.io" target="_blank" rel="noopener noreferrer">3dweb.io</a>.
        </p>

        <hr>

        <form method='post' data-source="<?php echo esc_attr(DWeb_PS_ADMIN_API::PATH); ?>">

            <div class="dweb_ps__settings__table">
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
            </div>

                <div class="dweb_ps__settings__row">
                    <div class="dweb_ps__settings__label">
                        <a id="dweb_ps-test-auth" class="dweb_ps__button dweb_ps__button--normal">Test credentials</a>
                    </div>
                    <div class="dweb_ps__settings-holder">
                        <small id="dweb_ps__check-auth-result" class=""></small>
                    </div>
                </div>
        </form>

        <hr style="margin: 30px 0;">

        <?php
        include('button.php'); ?>

    </div>
<?php
include('footer.php'); ?>