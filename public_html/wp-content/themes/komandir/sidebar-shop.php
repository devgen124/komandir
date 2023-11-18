<?php
/**
 * The sidebar containing the main widget area
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package komandir
 */

?>

<?php
$filter_ids = [
	14508,
	14536,
	14540,
	14828,
	15420,
	15447,
	15469,
	15485,
	20096,
	61298,
	61319,
	61332,
	61340,
	61571,
	61583,
	61593,
	61610,
	61626,
	61678,
	61690
];

?>

<?php if ( ( is_product_category() || is_tax( 'promotion' ) || isset( $_GET['s'] ) ) && ( ! isset( $GLOBALS['hide_filters'] ) || $GLOBALS['hide_filters'] === false ) ): ?>

	<aside id="woo-sidebar" class="widget-area filters-sidebar">
		<h2>Фильтры</h2>
		<button class="filters-close" aria-label="Закрыть"></button>
		<div class="filters-sidebar-inner">
			<?php foreach ( $filter_ids as $id ) {
				echo do_shortcode( "[br_filters_group group_id=$id]" );
			} ?>
		</div>
	</aside>

<?php endif; ?>
