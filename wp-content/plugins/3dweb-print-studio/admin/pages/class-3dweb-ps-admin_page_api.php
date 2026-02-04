<?php

class DWeb_PS_ADMIN_API extends DWeb_PS_ADMIN_PAGE_ABSTRACT
{
    const PATH = '3dweb-ps-api-settings';

    protected $pageTitle = 'API Settings';
    protected $menuTitle = 'API Settings';
    protected $template = '3dweb-ps-admin-display-api';
    
    // Register custom AJAX hooks for this page
    protected $customAjaxHooks = [
        'dweb_ps-check-auth' => 'ajax_check_auth',
    ];

    const TOKEN = 'DWEBPS_token';

    const CONFIGURATOR_HOST = 'DWEBPS_configurator_host';

    const CONFIGURATOR_HOST_VERSION = 'DWEBPS_configurator_host_version';

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
            wp_send_json_error(['message' => 'You do not have permission to perform this action.']);
        }

        // Ensure required options are set
        $token = get_option(self::TOKEN, '');
        $host  = get_option(self::CONFIGURATOR_HOST, '');
        $ver   = get_option(self::CONFIGURATOR_HOST_VERSION, '');
        $missing = [];
        if (empty($token)) $missing[] = 'Token';
        if (empty($host)) $missing[] = 'Configurator Host';
        if (empty($ver)) $missing[] = 'API Version';
        if (!empty($missing)) {
            wp_send_json_error([
                'message' => 'Please fill in and save the following fields first: ' . implode(', ', $missing) . '.',
            ]);
        }

        $api = new DWeb_PS_API();
        $result = $api->performGet('check-auth');

        if (is_wp_error($result)) {
            wp_send_json_error([
                'message' => 'Authentication failed: ' . $result->get_error_message(),
                'data'    => $result->get_error_data(),
            ]);
        }

        if ($result === false) {
            wp_send_json_error([
                'message' => 'Authentication failed or the server returned an error.',
                'data'    => $result,
            ]);
        }

		// test if data contains an error
		if (isset($result['error'])) {
			wp_send_json_error([
    'message' => 'Authentication failed: ' . $result['error'],
				'data'    => $result,
			]);
		}

		if($result === null){
			wp_send_json_error([
    'message' => 'Authentication failed: Unknown error',
				'data'    => $result,
			]);
		}

        wp_send_json_success($result);
    }
}