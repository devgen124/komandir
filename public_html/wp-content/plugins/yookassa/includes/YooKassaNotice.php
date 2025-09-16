<?php

class YooKassaNotice
{
    /**
     * Отображает сообщение для покупателя в магазине
     *
     * @param string $message Сообщение
     * @param string $type Тип
     * @return void
     */
    private static function front_notice($message, $type)
    {
        try {
            $message = function_exists('esc_html')
                ? esc_html($message)
                : htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

            if (did_action('woocommerce_init') && function_exists('wc_add_notice')) {
                wc_add_notice($message, $type);
                return;
            }

            add_action('woocommerce_init', function() use ($message, $type) {
                if (function_exists('wc_add_notice')) {
                    wc_add_notice($message, $type);
                }
            });
        } catch (Exception $e) {
            YooKassaLogger::error('Failed to add WooCommerce notice. Message: "' . $message . '". Type: "' . $type . '". Error: ' . $e->getMessage());
        }
    }

    /**
     * @param string $message
     * @return void
     */
    public static function front_notice_error($message)
    {
        self::front_notice($message, 'error');
    }

    /**
     * @param string $message
     * @return void
     */
    public static function front_notice_warning($message)
    {
        self::front_notice($message, 'warning');
    }

    /**
     * @param string $message
     * @return void
     */
    public static function front_notice_success($message)
    {
        self::front_notice($message, 'success');
    }

    /**
     * @param string $message
     * @return void
     */
    public static function front_notice_info($message)
    {
        self::front_notice($message, 'info');
    }

    /**
     * Отображает сообщение в админке
     *
     * @param string $message Сообщение
     * @param string $type Тип
     * @return void
     */
    private static function admin_notice($message, $type)
    {
        try {
            if (!is_admin()) {
                return;
            }

            $message = function_exists('esc_html')
                ? esc_html($message)
                : htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

            if (function_exists('wp_admin_notice')) {
                wp_admin_notice($message, array('type' => $type));
                return;
            }

            echo '<div class="notice notice-' . $type . '"><p>' . $message . '</p></div>';
        } catch (Exception $e) {
            YooKassaLogger::error('Failed to display admin notice. Message: "' . $message . '". Type: "' . $type . '". Error: ' . $e->getMessage());
        }
    }

    /**
     * @param string $message
     * @return void
     */
    public static function admin_notice_error($message)
    {
        self::admin_notice($message, 'error');
    }

    /**
     * @param string $message
     * @return void
     */
    public static function admin_notice_warning($message)
    {
        self::admin_notice($message, 'warning');
    }

    /**
     * @param string $message
     * @return void
     */
    public static function admin_notice_success($message)
    {
        self::admin_notice($message, 'success');
    }

    /**
     * @param string $message
     * @return void
     */
    public static function admin_notice_info($message)
    {
        self::admin_notice($message, 'info');
    }
}
