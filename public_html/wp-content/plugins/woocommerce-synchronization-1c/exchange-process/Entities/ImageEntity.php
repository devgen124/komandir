<?php

namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\Entities;

use Itgalaxy\Wc\Exchange1c\Includes\Logger;
use Itgalaxy\Wc\Exchange1c\Includes\SettingsHelper;

class ImageEntity
{
    /**
     * @param string $image
     * @param string $imageSrcPath
     * @param int    $parentPostId
     *
     * @see https://developer.wordpress.org/reference/functions/media_handle_sideload/
     *
     * @return false|int
     */
    public static function insert($image, $imageSrcPath, $parentPostId)
    {
        Logger::log(
            '(image) adding image for ID - ' . $parentPostId,
            [
                'size' => round((float) filesize($imageSrcPath) / 1024, 2) . ' KB',
                'name' => basename($imageSrcPath),
            ]
        );

        $imageSha = sha1_file($imageSrcPath);
        $wpFileType = \wp_check_filetype(basename($imageSrcPath), null);

        /**
         * Fires before the image is added to the site.
         *
         * @since 1.102.0
         *
         * @param string $imageSrcPath
         * @param int    $productID
         *
         * @see MaxImageSize
         */
        \do_action('itglx/wc1c/before-add-image-to-site', $imageSrcPath, $parentPostId);

        $postData = [];

        if (!SettingsHelper::isEmpty('write_product_name_to_attachment_title')) {
            $postData['post_title'] = \get_post_field('post_title', $parentPostId);
        }

        $attachID = \media_handle_sideload(
            [
                'name' => trim(str_replace(' ', '', basename($imageSrcPath))),
                'type' => $wpFileType['type'],
                'tmp_name' => $imageSrcPath,
                'error' => 0,
                'size' => filesize($imageSrcPath),
            ],
            $parentPostId,
            null,
            $postData
        );

        if (!$attachID || \is_wp_error($attachID)) {
            return false;
        }

        if (!SettingsHelper::isEmpty('write_product_name_to_attachment_attribute_alt')) {
            \update_post_meta($attachID, '_wp_attachment_image_alt', \get_post_field('post_title', $parentPostId));
        }

        \update_post_meta($attachID, '_1c_image_path', $image);
        \update_post_meta($attachID, '_1c_image_sha_hash', $imageSha);

        Logger::log('(image) added image for ID - ' . $parentPostId, [$attachID]);

        return $attachID;
    }

    /**
     * @param int $attachID
     * @param int $parentPostId
     *
     * @see https://developer.wordpress.org/reference/functions/wp_delete_attachment/
     *
     * @return void
     */
    public static function remove($attachID, $parentPostId)
    {
        \wp_delete_attachment($attachID, true);

        Logger::log('(image) removed image for ID - ' . $parentPostId, [$attachID]);
    }

    /**
     * @param string $value
     * @param string $key   Default: '_1c_image_path'.
     *
     * @return null|string
     */
    public static function findByMeta($value, $key = '_1c_image_path')
    {
        global $wpdb;

        $attachID = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT `meta`.`post_id` FROM `{$wpdb->postmeta}` as `meta`
                 INNER JOIN `{$wpdb->posts}` as `posts` ON `meta`.`post_id` = `posts`.`ID`
                 WHERE `meta`.`meta_value` = %s AND `meta`.`meta_key` = %s",
                (string) $value,
                (string) $key
            )
        );

        return $attachID;
    }

    /**
     * @param string            $rootImagePath
     * @param string            $imageRelativePath
     * @param int               $parentPostID
     * @param \SimpleXMLElement $element
     *
     * @return string
     */
    public static function srcResultPath($rootImagePath, $imageRelativePath, $parentPostID, $element)
    {
        $imageSrcPath = $rootImagePath;

        /**
         * Filters the path value that was obtained from XML.
         *
         * @since 1.78.3
         * @since 1.79.3 The `$parentPostID` parameter was added.
         * @since 1.79.3 The `$element` parameter was added.
         *
         * @param string            $imageRelativePath
         * @param int               $parentPostID
         * @param \SimpleXMLElement $element
         */
        $imageSrcPath .= \apply_filters('itglx_wc1c_image_path_from_xml', $imageRelativePath, $parentPostID, $element);

        return str_replace('//', '/', $imageSrcPath);
    }
}
