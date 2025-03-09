<?php


class YooKassaLogger
{
    const MESSAGE_TYPE = 3;

    const LEVEL_INFO = 'info';
    const LEVEL_WARNING = 'warning';
    const LEVEL_ERROR = 'error';
    const OAUTH_CMS_URL = 'https://yookassa.ru/integration/oauth-cms';

    public static function info($message)
    {
        self::log(self::LEVEL_INFO, $message);
    }

    public static function error($message)
    {
        self::log(self::LEVEL_ERROR, $message);
    }

    public static function warning($message)
    {
        self::log(self::LEVEL_ERROR, $message);
    }

    public static function log($level, $message)
    {
        $filePath = WP_CONTENT_DIR.'/yookassa-debug.log';
        $isDebugEnabled = get_option('yookassa_debug_enabled');
        if ($isDebugEnabled) {
            if ( ! file_exists($filePath)) {
                touch($filePath);
                chmod($filePath, 0644);
            }

            $messageFormatted = self::formatMessage($level, $message);
            error_log($messageFormatted, self::MESSAGE_TYPE, $filePath);
        }
    }

    private static function formatMessage($level, $message)
    {
        $date = date('Y-m-d H:i:s');

        return sprintf("[%s] [%s] Message: %s \r\n", $date, $level, $message);
    }

    /**
     * @param $data
     * @return void
     */
    public static function sendMetric($data)
    {
        $parameters = array(
            'cms' => 'woocommerce',
            'host' => $_SERVER['HTTP_HOST'],
            'shop_id' => get_option('yookassa_shop_id'),
        );

        wp_remote_post(self::OAUTH_CMS_URL . '/metric/woocommerce', array(
            'headers'     => array('Content-Type' => 'application/json; charset=utf-8'),
            'body'        => json_encode(array_merge($data, $parameters)),
            'method'      => 'POST',
            'data_format' => 'body',
        ));
    }

    public static function sendHeka($metrics)
    {
        self::sendMetric(array(
            'metric_heka' => $metrics
        ));
    }

    public static function sendBI($type, $metrics)
    {
        self::sendMetric(array(
            'metric_bi' => array(
                'type' => $type,
                'data' => $metrics
            )
        ));
    }

    public static function sendAlertLog($message, $context=array(), $metrics=array())
    {
        if (!empty($context['exception']) && $context['exception'] instanceof Exception) {
            $exception = $context['exception'];
            $context['exception'] = array(
                'code' => $exception->getCode(),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile() . ':' . $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            );
        }
        $data = array(
            'metric_app' => array(
                'level' => 'alert',
                'message' => $message,
                'context' => $context,
            )
        );
        if (!empty($metrics)) {
            $data['metric_heka'] = $metrics;
        }
        self::sendMetric($data);
    }
}
