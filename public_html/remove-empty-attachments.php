<?php
if ( file_exists( __DIR__ . '/wp-load.php' ) ) {
	include_once __DIR__ . '/wp-load.php';
} else {
	return;
}

$products = get_posts( [
	'post_type'      => 'product',
	'posts_per_page' => - 1,
	'tax_query'      => [
		[
			'taxonomy' => 'product_cat',
			'field'    => 'term_id',
			'terms'    => 9401,
			'operator' => 'IN'
		]
	]
] );

foreach ( $products as $product ) {
	$wc_product     = wc_get_product( $product->ID );
	$attachment_ids = $wc_product->get_gallery_image_ids();
	$image_id       = $wc_product->get_image_id();
	if ( $image_id ) {
		$attachment_ids[] = $image_id;
	}
	if ( $attachment_ids ) {
		$attachment_ids[] = $wc_product->get_image_id();
		foreach ( $attachment_ids as $attachment_id ) {
			$image_url = wp_get_attachment_image_url( $attachment_id );
			$ch        = curl_init( $image_url );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_exec( $ch );

			if ( curl_getinfo( $ch, CURLINFO_HTTP_CODE ) == 404 ) {
				wp_delete_post( $attachment_id );
			}
		}
	}
}
