<?php
/*
Template Name: Акции
*/
?>

<?php get_header();?>

<main id="primary" class="site-main">

	<div class="container">
        <?php 
        $tags = get_terms( 'product_tag' );
        foreach ($tags as $tag) :?>
            <section class="promo-tag">
                <h2><?= $tag->name; ?></h2>
                <?php if (!empty($tag->description)): ?>
                    <p><?= $tag->description; ?></p>
                <?php endif; ?>
                <?= do_shortcode("[products tag=\"{$tag->slug}\"]");?>
            </section>
        <?php endforeach; ?>
	</div>

</main><!-- #main -->

<?php
get_footer();