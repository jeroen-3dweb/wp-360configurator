<?php

class DWeb_PS_API
{
    /**
     * @var string
     */
    private $version;

    /**
     * @var string
     */
    private $pluginName;


    /**
     * JSV constructor.
     *
     * @param $version
     * @param $pluginName
     * @since 1.0.0
     */
    public function construct($version, $pluginName)
    {
        $this->version = $version;
        $this->pluginName = $pluginName;
    }

    public function createNewSession($productId, $callbackUrl)
    {
        $endPoint = 'sessions/' . $productId;
        return $this->performPost($endPoint, [
            'callback_url' => $callbackUrl,
        ]);
    }

    public function getSessionAssets($sessionId)
    {
        $endPoint = 'sessions/' . $sessionId . '/assets';
        return $this->performGet($endPoint);
    }

    public function searchProducts($search = '')
    {
        $endPoint = 'products';
        if (!empty($search)) {
            $endPoint .= '?search=' . urlencode($search);
        }
        return $this->performGet($endPoint);
    }

    public function performGet($endPoint)
    {
        $version = get_option(DWeb_PS_ADMIN_API::CONFIGURATOR_HOST_VERSION);
        $configuratorHost = $this->getConfiguratorHost();
        $url = sprintf('%s/%s/%s', $configuratorHost, $version, $endPoint);

        $args = $this->getArgs();

        $response = wp_remote_get($url, $args);
        if (is_wp_error($response)) {
            return [
                'error' => $response->get_error_message()
            ];
        }

        $body = wp_remote_retrieve_body($response);
        $code = wp_remote_retrieve_response_code($response);

        if ($code != 200) {
            $data = json_decode($body, true);
            if (!is_array($data)) {
                $data = [
                    'error' => 'API returned code ' . $code,
                    'body' => $body
                ];
            }
            return $data;
        }

        return json_decode($body, true);
    }

    public function performPost($endPoint, $data)
    {
        $version = get_option(DWeb_PS_ADMIN_API::CONFIGURATOR_HOST_VERSION);
        $configuratorHost = $this->getConfiguratorHost();
        $url = sprintf('%s/%s/%s', $configuratorHost, $version, $endPoint);

        $args = $this->getArgs($data);

        $response = wp_remote_post($url, $args);

        if (is_wp_error($response)) {
            return [
                'error' => $response->get_error_message()
            ];
        }

        $body = wp_remote_retrieve_body($response);
        $code = wp_remote_retrieve_response_code($response);

        if ($code != 200) {
            $data = json_decode($body, true);
            if (!is_array($data)) {
                $data = [
                    'error' => 'API returned code ' . $code,
                    'body' => $body
                ];
            }
            return $data;
        }

        return json_decode($body, true);
    }

    public function getArgs($data = null): array
    {
        $token = get_option(DWeb_PS_ADMIN_API::TOKEN);
        $args = array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ),
            'timeout' => 60,
        );

        if ($data) {
            $args['body'] = json_encode($data);
        }
        return $args;
    }

    public function getConfiguratorHost(): string
    {
        $configuratorHost = get_option(DWeb_PS_ADMIN_API::CONFIGURATOR_HOST);
        return rtrim($configuratorHost, '/');
    }
}