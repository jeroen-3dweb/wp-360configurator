<?php

/** @var string $license */

/** @var string $source */


include('header.php');
include('cnf-3dweb-settings-helper.php');

?>
    <div class="cnf-3dweb__settings">
        <h2>API Credentials</h2>
        <p>Enter your API credentials to enable the plugin.
        </p>

        <form method='post' data-source="<?php echo esc_attr(CNF_3DWeb_ADMIN_API::PATH); ?>">

            <div class="cnf-3dweb__settings__table">
                <?php echo cnf_3dweb_setting_create_row(
                        'Token',
                        '',
                        CNF_3DWeb_ADMIN_API::TOKEN,
                        get_option(CNF_3DWeb_ADMIN_API::TOKEN, ''),
                        'text'
                );
                ?> <?php echo cnf_3dweb_setting_create_row(
                        'Configurator Host',
                        '',
                        CNF_3DWeb_ADMIN_API::CONFIGURATOR_HOST,
                        get_option(CNF_3DWeb_ADMIN_API::CONFIGURATOR_HOST, ''),
                        'text'
                ); ?>
                <?php echo cnf_3dweb_setting_create_select(
                        'API Version',
                        '',
                        CNF_3DWeb_ADMIN_API::CONFIGURATOR_HOST_VERSION,
                        get_option(CNF_3DWeb_ADMIN_API::CONFIGURATOR_HOST_VERSION, ''),
                        [
                                ['label' => 'v1', 'value' => 'v1'],
                        ]
                );
                ?>

                <div class="cnf-3dweb__settings__row">
                    <div class="cnf-3dweb__settings__label">
                        <a id="cnf-test-auth" class="cnf-3dweb__button cnf-3dweb__button--normal">Test credentials</a>
                    </div>
                    <div class="cnf-3dweb__settings-holder">
                        <small id="cnf-3dweb__check-auth-result" class=""></small>
                    </div>
                </div>
            </div>
        </form>
        <?php
        include('button.php'); ?>

    </div>
<?php
include('footer.php'); ?>