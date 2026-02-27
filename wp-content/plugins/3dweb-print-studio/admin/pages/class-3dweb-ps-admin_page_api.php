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

    const TOKEN = 'DWEB_PS_token';

    const CONFIGURATOR_HOST = 'DWEB_PS_configurator_host';

    const CONFIGURATOR_HOST_VERSION = 'DWEB_PS_configurator_host_version';

    protected $fields = [
        self::TOKEN,
        self::CONFIGURATOR_HOST,
        self::CONFIGURATOR_HOST_VERSION
    ];

    protected function normalizeFieldValue($key, $value)
    {
        if ($key === self::CONFIGURATOR_HOST && $value === '') {
            return DWeb_PS_API::DEFAULT_CONFIGURATOR_HOST;
        }
        if ($key === self::CONFIGURATOR_HOST_VERSION && $value === '') {
            return DWeb_PS_API::DEFAULT_API_VERSION;
        }

        return $value;
    }

    /**
     * AJAX handler to test credentials against /check-auth endpoint
     */
    public function ajax_check_auth()
    {
        check_ajax_referer('jsv_save_setting');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'You do not have permission to perform this action.']);
        }

        // Use current form values first (even when not saved), fallback to options.
        $token = isset($_REQUEST[self::TOKEN])
            ? sanitize_text_field(wp_unslash($_REQUEST[self::TOKEN]))
            : get_option(self::TOKEN, '');
        $host = isset($_REQUEST[self::CONFIGURATOR_HOST])
            ? sanitize_text_field(wp_unslash($_REQUEST[self::CONFIGURATOR_HOST]))
            : get_option(self::CONFIGURATOR_HOST, DWeb_PS_API::DEFAULT_CONFIGURATOR_HOST);
        $ver = isset($_REQUEST[self::CONFIGURATOR_HOST_VERSION])
            ? sanitize_text_field(wp_unslash($_REQUEST[self::CONFIGURATOR_HOST_VERSION]))
            : get_option(self::CONFIGURATOR_HOST_VERSION, DWeb_PS_API::DEFAULT_API_VERSION);

        if (empty($host)) {
            $host = DWeb_PS_API::DEFAULT_CONFIGURATOR_HOST;
        }
        if (empty($ver)) {
            $ver = DWeb_PS_API::DEFAULT_API_VERSION;
        }
        $missing = [];
        if (empty($token)) $missing[] = 'Token';
        if (!empty($missing)) {
            wp_send_json_error([
                'message' => 'Please fill in the following fields first: ' . implode(', ', $missing) . '.',
            ]);
        }

        $api = (new DWeb_PS_API())->withRuntimeConfig([
            'token' => $token,
            'host' => $host,
            'version' => $ver,
        ]);
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
