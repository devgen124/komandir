<?php
/*
Template Name: Акции
*/
?>

<?php
global $svg;
?>

<?php get_header(); ?>

	<main id="primary" class="site-main">

		<div class="container">
			<?php
			$promotions = get_terms( [
				'taxonomy'   => 'promotion',
				'orderby'    => 'id',
				'order'      => 'ASC',
				'hide_empty' => false,
				'meta_query' => [
					[
						'key'     => 'active',
						'value'   => 1,
						'compare' => '==',
					]
				],
			] );
			if ( $promotions ) : ?>
				<ul class="promo-list">
					<?php
					foreach ( $promotions as $promo ) : ?>
						<li class="promo-item">
							<div class="promo-item-wrapper">
								<?php
								$image = get_field( 'pic', $promo );

								if ( ! empty( $image ) ) : ?>
									<img class="promo-image" src="<?= esc_url( $image['url'] ); ?>"
										 width="<?= $image['width']; ?>"
										 height="<?= $image['height']; ?>"
										 alt="<?= isset( $image['alt'] ) ? esc_attr( $image['alt'] ) : 'Акция "' . $promo->name . '"'; ?>"/>
								<?php
								endif; ?>
								<div class="promo-content">
										<h2>
											<?= $promo->name; ?>
										</h2>
									<div class="promo-text">
										<?= get_field( 'text', $promo ) ?>
									</div>
								</div>
							</div>
							<a href="<?= $promo->count ? get_term_link( $promo->term_id, 'promotion' )  : get_permalink( wc_get_page_id( 'shop' ) )?>" class="promo-shop-link">
								Смотреть товары
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>

	</main>

<?php
get_footer();
