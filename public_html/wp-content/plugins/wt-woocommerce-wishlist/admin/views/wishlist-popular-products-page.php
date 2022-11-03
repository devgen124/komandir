
<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

global $wpdb;
$table1 = $wpdb->prefix . 'wt_wishlists';
$table2 = $wpdb->prefix . 'wt_guest_wishlists';

$products = $wpdb->get_results("SELECT `product_id` FROM $table1 UNION SELECT `product_id` FROM $table2 ", ARRAY_A);
 
if ($products) {

?>
    <div class="wt_mainform">
        <h2 class="settings_title"><?php _e('Popular wishlisted products', 'wt-woocommerce-wishlist'); ?> </h2>
        <h3 class="wt_sub_title"><?php _e('Listed below are the most popular products from your store based on user/customer choices.', 'wt-woocommerce-wishlist'); ?> </h3>
        <table class="wt_popular_products_table" >
            <tr>
                <th><?php _e('Product', 'wt-woocommerce-wishlist'); ?></th> 
                <th><?php _e('Product Category', 'wt-woocommerce-wishlist'); ?></th> 
                <th><?php _e('Current wishlist count', 'wt-woocommerce-wishlist'); ?></th> 
            </tr>
            <?php
           foreach ($products as $product) {
                
            $product_data = wc_get_product($product['product_id']);
            if ($product_data) {
                ?>
                <tr>
                <td> <a href="<?php echo $product_data->get_permalink(); ?>"><?php echo $product_data->get_title();  ?></a>

                </td>
                <td> <?php echo strip_tags(wc_get_product_category_list( $product_data->get_id()));  ?>

                </td>
                
                <td> 
                <?php 
                $product_id = $product['product_id'];
                $total_count = $wpdb->get_results(" SELECT (SELECT COUNT(*) FROM $table1 where `product_id` = $product_id ) + (SELECT COUNT(*) FROM $table2 where `product_id` = $product_id ) AS total FROM dual", ARRAY_A);
                echo $total_count[0]['total'];  ?>
                </td>
                <tr>
            <?php } ?>  
            <?php } ?>            
           
        </table>
    </div>

<?php } else { ?>
    <h3 style="text-align: center"><?php _e('No products wishlisted yet!', 'wt-woocommerce-wishlist'); ?></h3>
<?php } ?>