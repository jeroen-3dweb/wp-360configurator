<?php

/** @var string $license */

/** @var string $source */


include('header.php');
include('3dweb-ps-settings-helper.php');

?>
    <div class="dweb_ps__settings">
        <h2>API Credentials</h2>
        <p class="dweb_ps__settings__intro">
            Enter your API credentials to connect the plugin with your 3DWeb environment.
            Need credentials? Get them from
            <span class="external-link"><a href="https://3dweb.io" target="_blank" rel="noopener noreferrer">3dweb.io</a></span>.
        </p>

        <form method='post' data-source="<?php echo esc_attr(DWeb_PS_ADMIN_API::PATH); ?>">

            <div class="dweb_ps__settings__table">
                <?php echo dwebps_setting_create_row(
                        'Token',
                        'Paste your API token.',
                        DWeb_PS_ADMIN_API::TOKEN,
                        get_option(DWeb_PS_ADMIN_API::TOKEN, ''),
                        'text'
                );
                ?> <?php echo dwebps_setting_create_row(
                        'Configurator Host',
                        'Base URL of your configurator API host.',
                        DWeb_PS_ADMIN_API::CONFIGURATOR_HOST,
                        get_option(DWeb_PS_ADMIN_API::CONFIGURATOR_HOST, DWeb_PS_API::DEFAULT_CONFIGURATOR_HOST),
                        'text'
                ); ?>
                <?php echo dwebps_setting_create_select(
                        'API Version',
                        'Version used for API requests.',
                        DWeb_PS_ADMIN_API::CONFIGURATOR_HOST_VERSION,
                        get_option(DWeb_PS_ADMIN_API::CONFIGURATOR_HOST_VERSION, DWeb_PS_API::DEFAULT_API_VERSION),
                        [
                                ['label' => 'v1', 'value' => 'v1'],
                        ]
                );
                ?>
            </div>

                <div class="dweb_ps__settings__row dweb_ps__settings__row--actions">
                    <div class="dweb_ps__settings__meta">
                        <div class="dweb_ps__settings__label">Connection test</div>
                        <small class="dweb_ps__settings-holder__description">Verify if the current credentials can authenticate.</small>
                    </div>
                    <div class="dweb_ps__settings-holder">
                        <div class="dweb_ps__settings__actions">
                            <a id="dweb_ps-test-auth" class="dweb_ps__button dweb_ps__button--normal">Test credentials</a>
                            <small id="dweb_ps__check-auth-result" class="dweb_ps__auth-result"></small>
                        </div>
                    </div>
                </div>
        </form>

        <?php
        include('button.php'); ?>

    </div>
<?php
include('footer.php'); ?>
