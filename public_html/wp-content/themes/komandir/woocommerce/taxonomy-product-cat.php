<?php
/**
 * The Template for displaying products in a product category. Simply includes the archive template
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/taxonomy-product-cat.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     4.7.0
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

global $wp_query;

$cat_children = get_terms( [
	'taxonomy' => 'product_cat',
	'parent'   => $wp_query->queried_object->term_id
] );

$cat_children = array_filter( $cat_children, function ( $cat ) {
	$products = get_posts( [
		'post_type'      => 'product',
		'posts_per_page' => - 1,
		'tax_query'      => [
			[
				'taxonomy' => 'product_cat',
				'terms'    => $cat->term_id,
				'operator' => 'IN'
			]
		],
		'meta_query'     => [
			[
				'key'   => '_stock_status',
				'value' => 'instock'
			]
		]
	] );

	if ( ! empty( $products ) ) {
		return $cat;
	}
} );

if ( ! empty( $cat_children ) ) {
	?>

	<main id="primary" class="site-main">
		<div class="container">
			<?php woocommerce_breadcrumb(); ?>
			<div class="row">

				<?php foreach ( $cat_children as $cat ) :

					$thumbnail_id = get_term_meta( $cat->term_id, 'thumbnail_id', true );

					$attachment_image = $thumbnail_id ? wp_get_attachment_image( $thumbnail_id, 'medium' ) : '<img src="' . get_template_directory_uri() . '/assets/images/placeholder.svg" width="600" height="600" alt="Изображение отсутствует">';

					?>

					<div class="col-12 col-sm-6 col-md-4 col-xl-3 cat-wrapper">
						<a href="<?= get_term_link( $cat->term_id, 'product_cat' ) ?>">
							<?= $attachment_image; ?>

							<h3>
								<?= $cat->name; ?>
							</h3>
						</a>
					</div>

				<?php endforeach; ?>

			</div>
		</div>
	</main>

	<?php

} else {

	/**
	 * Hook: woocommerce_before_main_content.
	 *
	 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
	 * @hooked woocommerce_breadcrumb - 20
	 * @hooked WC_Structured_Data::generate_website_data() - 30
	 */
	do_action( 'woocommerce_before_main_content' );

	?>
	<header class="woocommerce-products-header">
		<?php if ( apply_filters( 'woocommerce_show_page_title', true ) ) : ?>
			<h1 class="woocommerce-products-header__title page-title">
				<?php woocommerce_page_title(); ?>
			</h1>
		<?php endif; ?>

		<?php
		/**
		 * Hook: woocommerce_archive_description.
		 *
		 * @hooked woocommerce_taxonomy_archive_description - 10
		 * @hooked woocommerce_product_archive_description - 10
		 */
		do_action( 'woocommerce_archive_description' );
		?>
	</header>
	<?php
	if ( woocommerce_product_loop() ) {

		/**
		 * Hook: woocommerce_before_shop_loop.
		 *
		 * @hooked woocommerce_output_all_notices - 10
		 * @hooked woocommerce_result_count - 20
		 * @hooked woocommerce_catalog_ordering - 30
		 */
		do_action( 'woocommerce_before_shop_loop' );

		do_action( 'woocommerce_products_wrapper_start' );

		woocommerce_product_loop_start();

		if ( wc_get_loop_prop( 'total' ) ) {
			while ( have_posts() ) {
				the_post();

				/**
				 * Hook: woocommerce_shop_loop.
				 */
				do_action( 'woocommerce_shop_loop' );

				wc_get_template_part( 'content', 'product' );
			}
		}

		woocommerce_product_loop_end();

		/**
		 * Hook: woocommerce_after_shop_loop.
		 *
		 * @hooked woocommerce_pagination - 10
		 */
		do_action( 'woocommerce_after_shop_loop' );

		do_action( 'woocommerce_products_wrapper_end' );

	} else {
		/**
		 * Hook: woocommerce_no_products_found.
		 *
		 * @hooked wc_no_products_found - 10
		 */
		do_action( 'woocommerce_no_products_found' );

		// флаг для сайдбара с фильтром
		$GLOBALS['hide_filters'] = true;
	}

	/**
	 * Hook: woocommerce_sidebar.
	 *
	 * @hooked woocommerce_get_sidebar - 10
	 */
	do_action( 'woocommerce_sidebar' );

	/**
	 * Hook: woocommerce_after_main_content.
	 *
	 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
	 */
	do_action( 'woocommerce_after_main_content' );

}

get_footer( 'shop' );
