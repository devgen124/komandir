<?php

namespace Itgalaxy\Wc\Exchange1c\Includes\Actions;

use Itgalaxy\Wc\Exchange1c\Includes\Logger;

class PreDeleteTerm
{
    private static $instance = false;

    private function __construct()
    {
        // https://developer.wordpress.org/reference/hooks/pre_delete_term/
        add_action('pre_delete_term', [$this, 'actionDeleteTermMeta'], 11, 1);
        add_action('pre_delete_term', [$this, 'actionDeleteTerm'], 10, 1);
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function actionDeleteTermMeta($termID)
    {
        if (empty($termID)) {
            return;
        }

        \delete_term_meta($termID, '_id_1c');
    }

    public function actionDeleteTerm($termID)
    {
        if (empty($termID)) {
            return;
        }

        global $wpdb;

        if (isset($_SESSION['IMPORT_1C'])) {
            Logger::log(
                '(hook - pre_delete_term) Removed term fired hook `term_id` - ' . $termID,
                [get_term_meta($termID, '_id_1c', true)]
            );
        }

        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM `{$wpdb->termmeta}` WHERE `term_id` = %d",
                (int) $termID
            )
        );
    }
}
