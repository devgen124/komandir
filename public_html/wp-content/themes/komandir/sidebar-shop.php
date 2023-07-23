<?php
/**
 * The sidebar containing the main widget area
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package komandir
 */

?>

<?php if ( is_product_category() || is_tax( 'promotion' ) || isset( $_GET['s'] ) ): ?>

	<aside id="woo-sidebar" class="widget-area filters-sidebar">
		<h2>Фильтры</h2>
		<button class="filters-close" aria-label="Закрыть"></button>
		<div class="filters-sidebar-inner">
			<?= do_shortcode( '[br_filters_group group_id=14536]' ); ?>
			<?= do_shortcode( '[br_filters_group group_id=14540]' ); ?>
			<?= do_shortcode( '[br_filters_group group_id=14508]' ); ?>
			<?= do_shortcode( '[br_filters_group group_id=20096]' ); ?>
		</div>
	</aside>

<?php endif; ?>

