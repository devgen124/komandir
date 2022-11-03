<?php

namespace Itgalaxy\Wc\Exchange1c\Includes;

class Helper
{
    public static function isUserCanWorkingWithExchange()
    {
        if (!is_user_logged_in() || !current_user_can('manage_woocommerce')) {
            return false;
        }

        return true;
    }

    public static function isExchangeRequest()
    {
        if (
            (!defined('_1C_IMPORT') || _1C_IMPORT !== true) // 1c exchange directly running
            && (!self::isUserCanWorkingWithExchange() || !isset($_GET['manual-1c-import'])) // admin exchange running
        ) {
            return false;
        }

        return true;
    }

    public static function getTempPath()
    {
        return Bootstrap::$pluginDir . 'files/site' . get_current_blog_id() . '/temp';
    }

    public static function getFileSizeLimit()
    {
        return (int) SettingsHelper::get('file_limit', 5000000);
    }

    public static function removeDir($dir)
    {
        $objects = glob($dir . '/*');

        if ($objects) {
            foreach ($objects as $object) {
                is_dir($object) ? self::removeDir($object) : unlink($object);
            }
        }

        rmdir($dir);
    }

    public static function scanDir($dir)
    {
        $ignored = ['.', '..', '.svn', '.htaccess'];
        $files = [];

        if (file_exists($dir)) {
            foreach (scandir($dir) as $file) {
                if (in_array($file, $ignored) || is_dir($dir . '/' . $file)) {
                    continue;
                }

                $files[$file] = filemtime($dir . '/' . $file);
            }

            arsort($files);
            $files = array_keys($files);
        }

        return count($files) > 0 ? $files : false;
    }

    public static function existOrCreateDir($dirName)
    {
        $color = 'color: green;';
        $status = true;

        if (file_exists($dirName) && !is_writable($dirName)) {
            $color = 'color: red;';
            $message = $dirName
                . ' |  '
                . esc_html__(
                    'Exists, but is not available for writing. Change permissions.',
                    'itgalaxy-woocommerce-1c'
                );
            $status = false;
        } else {
            if (!file_exists($dirName)) {
                if (mkdir($dirName, 0755, true)) {
                    $message = $dirName
                        . ' | '
                        . esc_html__('Created and available for writing.', 'itgalaxy-woocommerce-1c');
                } else {
                    $color = 'color: red;';
                    $message = $dirName
                        . ' | '
                        . esc_html__('Not exist and unavailable for writing..', 'itgalaxy-woocommerce-1c');
                    $status = false;
                }
            } else {
                $message = $dirName
                    . ' | '
                    . esc_html__('Exists and available for writing.', 'itgalaxy-woocommerce-1c');
            }
        }

        return [
            'color' => $color,
            'text' => $message,
            'status' => $status,
        ];
    }

    public static function isVerify()
    {
        $value = get_site_option(Bootstrap::PURCHASE_CODE_OPTIONS_KEY);

        if (!empty($value)) {
            return true;
        }

        return false;
    }

    /**
     * Clearing the product page cache if there are caching plugins.
     *
     * @param int $postID
     *
     * @return void
     */
    public static function clearCachePluginsPostCache($postID)
    {
        if (function_exists('wpfc_clear_post_cache_by_id')) {
            Logger::log('clear product page cache for `WP Fastest Cache` - ' . $postID);

            \wpfc_clear_post_cache_by_id($postID);
        }
    }

    /**
     * Clearing and converting the string to `float` value.
     *
     * Example:
     * <ЦенаЗаЕдиницу>6290 , 10 </ЦенаЗаЕдиницу>
     *
     * Result: 6290.10
     *
     * @param float|int|string $value
     *
     * @return float
     */
    public static function toFloat($value)
    {
        $value = str_replace(
            ["\xc2\xa0", "\xa0", ' ', ','],
            ['', '', '', '.'],
            (string) $value
        );

        return floatval($value);
    }
}
