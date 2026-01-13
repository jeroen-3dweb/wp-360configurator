<?php

class CNF_3DWeb_ADMIN_API extends CNF_3DWeb_ADMIN_PAGE_ABSTRACT
{
    const PATH = 'cnf-3dweb-api-settings';

    protected $pageTitle = 'API Settings';
    protected $menuTitle = 'API Settings';
    protected $template = 'cnf-3dweb-admin-display-api';
    
    // Register custom AJAX hooks for this page
    protected $customAjaxHooks = [
        'cnf-3dweb-check-auth' => 'ajax_check_auth',
    ];

    const TOKEN = 'CNF3DWEB_token';

    const CONFIGURATOR_HOST = 'CNF3DWEB_configurator_host';

    const CONFIGURATOR_HOST_VERSION = 'CNF3DWEB_configurator_host_version';

    protected $fields = [
        self::TOKEN,
        self::CONFIGURATOR_HOST,
        self::CONFIGURATOR_HOST_VERSION
    ];

    /**
     * AJAX handler to test credentials against /check-auth endpoint
     */
    public function ajax_check_auth()
    {
        check_ajax_referer('jsv_save_setting');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Geen rechten om deze actie uit te voeren.']);
        }

        // Ensure required options exist
        $token = get_option(self::TOKEN, '');
        $host  = get_option(self::CONFIGURATOR_HOST, '');
        $ver   = get_option(self::CONFIGURATOR_HOST_VERSION, '');
        if (empty($token) || empty($host) || empty($ver)) {
            wp_send_json_error([
                'message' => 'Vul eerst Token, Configurator Host en API Version in en sla op.',
            ]);
        }

        $api = new CNF_3DWeb_API();
        $result = $api->performGet('check-auth');

        if (is_wp_error($result)) {
            wp_send_json_error([
                'message' => 'Authenticatie mislukt: ' . $result->get_error_message(),
                'data'    => $result->get_error_data(),
            ]);
        }

        if ($result === false) {
            wp_send_json_error([
                'message' => 'Authenticatie mislukt of server gaf een fout terug.',
                'data'    => $result,
            ]);
        }

        wp_send_json_success([
            'message' => 'Authenticatie gelukt.',
            'data'    => $result,
        ]);
    }
}