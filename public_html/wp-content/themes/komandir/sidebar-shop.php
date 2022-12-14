<?php
/**
 * The sidebar containing the main widget area
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package komandir
 */

?>

<?php if (is_product_category()) :?>

<aside id="woo-sidebar" class="widget-area filters-sidebar">
    <div class="filters-sidebar-inner">
        <div class="filters-sidebar-header">
            <h2>Фильтры</h2>
            <?= do_shortcode( '[br_filter_single filter_id=14543]' ); ?>
            <button class="filters-close" aria-label="Закрыть"></button>
        </div>
        <?= do_shortcode( '[br_filters_group group_id=14536]' ); ?>
        <?= do_shortcode( '[br_filters_group group_id=14540]' ); ?>
        <?= do_shortcode( '[br_filters_group group_id=14508]' ); ?>
    </div>
</aside>

<?php endif;?>
