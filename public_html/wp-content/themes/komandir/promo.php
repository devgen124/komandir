<?php
/*
Template Name: Акции
*/
?>

<?php
global $svg; 
?>

<?php get_header();?>

<main id="primary" class="site-main">

	<div class="container">
        <div class="promo-tag-wrapper">
        <?php 
        $tags = get_terms( 'product_tag' );
        foreach ($tags as $tag) :?>
            <section class="promo-tag">
                <?php
                $image = get_field( 'minor-pic', $tag );

                if( !empty( $image ) ): ?>
                    <img class="promo-tag-image" src="<?= esc_url($image['url']); ?>" width="<?= $image['width']; ?>" height="<?= $image['height']; ?>" alt="<?= isset($image['alt']) ? esc_attr($image['alt']) : 'Акция "' . $tag->name . '"'; ?>"/>
                    <?php 
                endif; ?>
                <div class="product-tag-content">
                    <a href="<?= add_query_arg( ['preview' => true], get_tag_link( $tag->term_id )); ?>">
                        <h2><?= $tag->name; ?></h2>
                    </a>
                    <?php
                    $time = get_field( 'term', $tag );
                    if( !empty( $time ) ): ?>
                        <p><?= $time; ?></p>
                    <?php 
                    endif;
                    ?>
                </div>
                <?php
                $products_query = new WP_Query([
                    'post_type' => 'product',
                    'product_tag' => $tag->slug
                ]);
                $first_product = $products_query->posts[0];
                ?>
                <div class="product-tag-product">
                    <?php
                    $image_src = get_the_post_thumbnail_url( $first_product->ID );
                    $image_src = $image_src ? $image_src : wc_placeholder_img_src();
                    ?>
                    <a class="product-tag-img-link" href="<?= $first_product->guid; ?>">
                        <img src="<?= $image_src ?>" alt="<?= $first_product->post_title; ?>"/>
                    </a>
                    <a class="product-tag-title-link" href="<?= $first_product->guid; ?>">
                        <h3><?= $first_product->post_title; ?></h3>
                    </a>
                    <a class="product-tag-shop-link" href="<?= get_tag_link( $tag->term_id ); ?>">
                        Ещё <?= format_quantity_ending($products_query->post_count, ['товар', 'товара', 'товаров']); ?> →
                    </a>
                </div>
            </section>
        <?php endforeach; ?>
        </div>
	</div>

</main><!-- #main -->

<?php
get_footer();