<?php

namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\Filters;

use Itgalaxy\Wc\Exchange1c\Includes\SettingsHelper;

class FindAttributeValueTermId
{
    private static $instance = false;

    private function __construct()
    {
        if (!SettingsHelper::isEmpty('find_exists_attribute_value_by_name')) {
            \add_filter('itglx_wc1c_find_exists_product_attribute_value_term_id', [$this, 'findByName'], 10, 3);
        }
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function findByName($termID, $element, $taxonomy)
    {
        if ((int) $termID) {
            return $termID;
        }

        if (empty($element->Значение)) {
            return $termID;
        }

        $terms = \get_terms(
            [
                'taxonomy' => $taxonomy,
                'parent' => 0,
                'name' => \wp_slash(trim(\wp_strip_all_tags((string) $element->Значение))),
                'hide_empty' => false,
                'orderby' => 'name',
                'fields' => 'ids',
                // find only terms without guid
                'meta_query' => [
                    [
                        'key' => '_id_1c',
                        'compare' => 'NOT EXISTS',
                    ],
                ],
            ]
        );

        if (\is_wp_error($terms) || !$terms) {
            return $termID;
        }

        return $terms[0];
    }
}
