<?php
if( ! class_exists('BeRocket_AAPF_compat_woocommerce_variation_functions') ) {
    class BeRocket_AAPF_compat_woocommerce_variation_functions {
        function __construct() {
            add_filter('berocket_filters_query_already_filtered', array(__CLASS__, 'out_of_stock_variable'), 10, 3);
            add_filter('berocket_filters_query_vars_already_filtered', array(__CLASS__, 'out_of_stock_variable'), 10, 3);
            add_filter('woocommerce_variation_price_custom_query', array(__CLASS__, 'price_custom_query'));
        }
        public static function out_of_stock_variable($query, $terms, $limits) {
            global $wpdb;
            $custom_query = self::get_query($terms, $limits);
            if( empty($custom_query) ) {
                return $query;
            }
            $variable_products = $wpdb->get_results( $custom_query, ARRAY_N );
            global $berocket_variable_to_variation_list;
            $berocket_variable_to_variation_list = array();
            if( is_array($variable_products) ) {
                foreach($variable_products as $variable_product) {
                    if( is_array($variable_product) && count($variable_product) >= 2 ) {
                        if( ! isset($berocket_variable_to_variation_list[$variable_product[1]]) || ! is_array($berocket_variable_to_variation_list[$variable_product[1]]) ) {
                            $berocket_variable_to_variation_list[$variable_product[1]] = array();
                        }
                        $berocket_variable_to_variation_list[$variable_product[1]][] = $variable_product[0];
                    }
                }
            }
            return $query;
        }
        public static function get_query($terms, $limits) {
            global $wpdb;
            $outofstock = get_term_by( 'slug', 'outofstock', 'product_visibility' );
            $current_terms = array();
            $current_attributes = array();
            if( is_array($terms) && count($terms) ) {
                foreach($terms as $term) {
                    if( substr( $term[0], 0, 3 ) == 'pa_' ) {
                        $current_attributes[] = sanitize_title('attribute_' . $term[0]);
                        $current_terms[] = urlencode(urldecode($term[3]));
                    }
                }
            }
            if( is_array($limits) && count($limits) ) {
                foreach($limits as $attr => $term_ids) {
                    if( substr( $attr, 0, 3 ) == 'pa_' ) {
                        $current_attributes[] = 'attribute_' . $attr;
                        foreach($term_ids as $term_id) {
                            $term = get_term($term_id);
                            if( ! empty($term) && ! is_wp_error($term) ) {
                                $current_terms[] = urlencode(urldecode($term->slug));
                            }
                        }
                    }
                }
            }
            $custom_query = '';
            if( count($current_terms) ) {
                $current_terms = array_unique($current_terms);
                $current_attributes = array_unique($current_attributes);
                $current_terms = implode('", "', $current_terms);
                $current_attributes = implode('", "', $current_attributes);
                $custom_query = sprintf( '
                    SELECT filtered_post.var_id, filtered_post.ID FROM
                        (SELECT %1$s.id as var_id, %1$s.post_parent as ID, COUNT(%1$s.id) as meta_count FROM %1$s
                        INNER JOIN %2$s AS pf1 ON (%1$s.ID = pf1.post_id)
                        WHERE %1$s.post_type = "product_variation"
                        AND %1$s.post_status != "trash"
                        '.(empty($current_attributes) ? '' : 'AND pf1.meta_key IN ("%3$s") AND pf1.meta_value IN ("%4$s")').'
                        GROUP BY %1$s.id) as filtered_post
                        INNER JOIN (SELECT ID, MAX(meta_count) as max_meta_count FROM (
                            SELECT %1$s.id as var_id, %1$s.post_parent as ID, COUNT(%1$s.id) as meta_count FROM %1$s
                            INNER JOIN %2$s AS pf1 ON (%1$s.ID = pf1.post_id)
                            WHERE %1$s.post_type = "product_variation"
                            '.(empty($current_attributes) ? '' : 'AND pf1.meta_key IN ("%3$s") AND pf1.meta_value IN ("%4$s")').'
                            GROUP BY %1$s.id
                        ) as max_filtered_post GROUP BY ID
                    ) as max_filtered_post ON max_filtered_post.ID = filtered_post.ID AND max_filtered_post.max_meta_count = filtered_post.meta_count
                ', $wpdb->posts, $wpdb->postmeta, $current_attributes, $current_terms );
                $custom_query = apply_filters( 'woocommerce_variation_price_custom_query', $custom_query, $current_attributes, $current_terms);
            }
            return $custom_query;
        }
        public static function price_custom_query($custom_query) {
            if ( ! empty($_POST['price_ranges']) || ! empty($_POST['price']) ) {
                global $wpdb;
                $custom_query .= " JOIN {$wpdb->postmeta} as br_price ON filtered_post.var_id = br_price.post_id 
                AND br_price.meta_key = '".apply_filters('berocket_price_filter_meta_key', '_price', 'variation_functions_79')."' 
                AND (";
                if ( ! empty($_POST['price']) ) {
                    $min = isset( $_POST['price'][0] ) ? floatval( $_POST['price'][0] ) : 0;
                    $max = isset( $_POST['price'][1] ) ? floatval( $_POST['price'][1] ) : 9999999999;
                    $custom_query .= "br_price.meta_value BETWEEN {$min} AND {$max}";
                } else {
                    $price_ranges = array();
                    foreach ( $_POST['price_ranges'] as $range ) {
                        $range = explode( '*', $range );
                        $min = isset( $range[0] ) ? floatval( ($range[0] - 1) ) : 0;
                        $max = isset( $range[1] ) ? floatval( $range[1] ) : 0;
                        $price_ranges[] = "( br_price.meta_value BETWEEN {$min} AND {$max} )";
                    }
                    $custom_query .= implode(' OR ', $price_ranges);
                }
                $custom_query .= ")";
            }
            return $custom_query;
        }
    }
    new BeRocket_AAPF_compat_woocommerce_variation_functions();
}
