<?php

namespace Itgalaxy\PluginCommon;

class PluginRequest
{
    private $pluginID;

    private $pluginVersion;

    private $option;

    public function __construct($pluginID, $pluginVersion, $option)
    {
        $this->pluginID = $pluginID;
        $this->pluginVersion = $pluginVersion;
        $this->option = $option;
    }

    public function code($action, $code)
    {
        $code = trim(str_replace(' ', '', $code));

        $response = $this->call($action, $code);

        if (\is_wp_error($response)) {
            // fix network connection problems
            if ($response->get_error_code() === 'http_request_failed') {
                if ($action === 'code_activate') {
                    $messageContent = 'Success verify.';
                    \update_site_option($this->option, $code);
                } else {
                    $messageContent = 'Success unverify.';
                    \update_site_option($this->option, '');
                }

                return [
                    'message' => $messageContent,
                    'state' => 'successCheck',
                ];
            }

            return [
                'message' => '(Code - ' . $response->get_error_code() . ') ' . $response->get_error_message(),
                'state' => 'failedCheck',
            ];
        }

        if ($response->status === 'successCheck' && $action === 'code_activate') {
            \update_site_option($this->option, $code);
        } else {
            \update_site_option($this->option, '');
        }

        return [
            'message' => $response->message,
            'state' => $response->status,
        ];
    }

    public function call($action, $code = '')
    {
        if (empty($code)) {
            $code = \get_site_option($this->option, '');
        }

        $response = \wp_remote_post(
            'https://envato.itgalaxy.company/envato/plugin-request',
            [
                'body' => [
                    'purchaseCode' => $code,
                    'itemID' => $this->pluginID,
                    'version' => $this->pluginVersion,
                    'action' => $action,
                    'domain' => \network_site_url(),
                ],
                'sslverify' => false,
                'data_format' => 'body',
                'timeout' => 30,
            ]
        );

        if (!\is_wp_error($response)) {
            // hosting block
            if ((int) \wp_remote_retrieve_response_code($response) === 405) {
                return new \WP_Error('http_request_failed', 'Not Allowed');
            }

            $response = json_decode(\wp_remote_retrieve_body($response));

            if (isset($response->status) && $response->status === 'stop') {
                \update_site_option($this->option, '');
            }
        }

        return $response;
    }
}
