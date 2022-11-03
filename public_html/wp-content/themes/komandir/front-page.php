<?php

/**
 * The template for displaying all pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package komandir
 */

get_header();

require 'data/shop-icons.php';

// $supercats = array_slice($shop_icons, 1);
$promotion = array_shift($shop_icons);

global $svg;

$parent_cats = get_categories([
	'taxonomy' => 'product_cat',
	'parent' => 0
]);
?>

<main id="primary" class="site-main">
	<div class="container">
		<div class="row">
			<div class="col-12 d-lg-flex site-main-top">

				<aside class="d-none d-lg-block flex-shrink-0 supercategories-sidebar">
					<ul class="supercategories-sidebar-list">

							<li>
								<a href="<?= get_permalink( get_page_by_path( 'promotion' ) ); ?>">
									<span>
										<?= $svg->view_from_sprite([
												'title' => $promotion['icon']['title'],
												'width' => $promotion['icon']['width'],
												'height' => $promotion['icon']['height']
										]); ?>
									</span>
									Акции</a>
							</li>
						
						<?php foreach ($parent_cats as $cat):?>
							<?php foreach ($shop_icons as $item) : ?>
								<?php if ($item['name'] == $cat->cat_name):?>
									<li>
										<a href="<?= get_term_link($cat->slug, 'product_cat'); ?>">
											<span>
												<?= $svg->view_from_sprite([
														'title' => $item['icon']['title'],
														'width' => $item['icon']['width'],
														'height' => $item['icon']['height']
													]) ?>
											</span>
											<?= $cat->cat_name; ?></a>
									</li>
								<?php endif; ?>
							<?php endforeach; ?>
						<?php endforeach; ?>
					</ul>
				</aside>
				<section class="flex-grow-1 promo-slider">
					<?php masterslider(1); ?>
				</section>
			</div>
		</div>
	</div>
	<div class="container">
		<div class="row">
			<section class="col sales">
				<div class="sales-heading">
					<h2>Распродажа</h2>
					<p>товары со скидкой или акционные</p>
				</div>
				<div class="woocommerce-scroller">
					<?= do_shortcode('[products limit="12" category="televizory"]') ?>
				</div>
			</section>
		</div>
	</div>
</main><!-- #main -->

<?php
get_footer();
