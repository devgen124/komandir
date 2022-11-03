<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
global $wt_wishlist_table_settings_options;
$wishlist_text = apply_filters('wishlist_table_heading', 'Products added in my wishlist');
?>
<h4><?php _e($wishlist_text, 'wt-woocommerce-wishlist'); ?></h4>
<?php if ($products) { ?>
    <form class="wishlist-form" action="">
        <?php if (isset($wt_wishlist_table_settings_options['add_all_to_cart']) == 1) {
            $redirect_to_cart = isset($wt_wishlist_table_settings_options['redirect_to_cart']) ? $wt_wishlist_table_settings_options['redirect_to_cart'] : '';
        ?>
            <aside class="wishlist-aside">
                <button id="bulk-add-to-cart" data-redirect_to_cart="<?php echo $redirect_to_cart; ?>" class="primary-btn">Купить все товары</button>
                <button id="bulk-clear-wishlist" class="primary-btn">Очистить список</button>
            </aside>
        <?php } ?>
        <ul class="wishlist-list">
            <?php
            foreach ($products as $product) {
                $product_data = wc_get_product($product['product_id']);
                if ($product_data) {
            ?>
                    <li class="wishlist-item">
                        <button class="wishlist-remove remove_wishlist_single" data-product_id="<?php echo $product['product_id']; ?>" data-variation_id="<?php echo $product['variation_id']; ?>" data-product_type="<?php echo $product_data->is_type('variable'); ?>" aria-label="Удалить из списка"></button>
                        <a href="<?php echo $product_data->get_permalink(); ?>">
                            <?php
                            if ($product_data->is_type('variable')) {
                                if ($product['variation_id'] != 0) {
                                    $product_data = wc_get_product($product['variation_id']);
                                }
                            }
                            echo $product_data->get_image('woocommerce_thumbnail');
                            ?>
                            <h3><?php echo $product_data->get_title(); ?></h3>
                        </a>
                        <?php
                        if ((isset($wt_wishlist_table_settings_options['wt_enable_product_variation_column']) == 1) && $product_data->is_type('variation')) {
                            echo wc_get_formatted_variation($product_data);
                        }
                        if (isset($wt_wishlist_table_settings_options['wt_enable_unit_price_column']) == 1) {
                            $base_price = $product_data->is_type('variable') ? $product_data->get_variation_regular_price('max') : $product_data->get_price();
                            if ($base_price) {
                                $sale_price = $product_data->get_sale_price();
                                $regular_price = $product_data->get_regular_price();
                        ?>
                                <div class="custom-price">
                                    <?php if ($product_data->is_on_sale()) : ?>
                                        <div class="custom-price-row">
                                            <span class="custom-crossed-price">
                                                <?php echo wc_price($regular_price); ?>
                                            </span>
                                            <?php if ($product_data->is_type('simple')) : ?>
                                                <span class="custom-diff">
                                                    <?php echo wc_price($sale_price - $regular_price); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <span class="custom-main-price">
                                            <?php echo wc_price($sale_price); ?>
                                        </span>
                                    <?php else : ?>
                                        <span class="custom-main-price">
                                            <?php echo wc_price($regular_price); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            <?php
                            }
                        }
                        if (isset($wt_wishlist_table_settings_options['wt_enable_stock_status_column']) == 1) { ?>
                            <div class="wishlist-stock"><?php
                                                        if ($product_data->is_in_stock() == 1) { ?>
                                    <span class="instock"><?php echo __('In Stock', 'wt-woocommerce-wishlist'); ?></span>
                                <?php
                                                        } else { ?>
                                    <span class="outstock"><?php echo __('Out of Stock', 'wt-woocommerce-wishlist'); ?></span>
                                <?php } ?>
                            </div>
                        <?php } ?>
                        <?php if (isset($wt_wishlist_table_settings_options['wt_enable_add_to_cart_option_column']) == 1) {
                            $id = ($product_data->is_type('variation')) ? $product['variation_id'] : $product['product_id'];
                            $redirect_to_cart = isset($wt_wishlist_table_settings_options['redirect_to_cart']) ? $wt_wishlist_table_settings_options['redirect_to_cart'] : '';

                        if ($product_data->is_in_stock()) {?>
                            <button class="button single-add-to-cart" data-product_id="<?php echo $id; ?>" data-redirect_to_cart="<?php echo $redirect_to_cart; ?>">В корзину</button>
                        <?php }
                        } ?>
                    </li>
            <?php
                }
            }
            ?>
        </ul>
    </form>
<?php } else { ?>
    <h3>Список избранного пуст</h3>
<?php } ?>