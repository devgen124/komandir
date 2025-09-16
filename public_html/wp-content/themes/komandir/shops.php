<?php
/*
Template Name: Магазины
*/
?>

<?php
get_header();
?>

	<main id="primary" class="site-main shops-page">
		<div class="container">
			<div class="shops-page-inner">
				<div class="shops-content">
					<h2>
						<?php the_title(); ?>
					</h2>
					<?php the_content(); ?>
				</div>
				<div id="map" class="map-container"></div>
				<script>
					ymaps.ready(
						function () {
							var myMap = new ymaps.Map("map", {
								center: [58.209538, 92.503860],
								zoom: 12
							});

							var markerSetup = {
								iconLayout: 'default#image',
								iconImageHref: '<?php echo get_template_directory_uri() . '/assets/images/marker.svg'; ?>',
								iconImageSize: [28.5, 42],
								iconImageOffset: [-10, -42]
							};

							myMap.geoObjects.add(new ymaps.Placemark([58.187814, 92.529608], {}, markerSetup));
							myMap.geoObjects.add(new ymaps.Placemark([58.232271, 92.497326], {}, markerSetup));
						}
					);
				</script>
			</div>
		</div>

	</main><!-- #main -->

<?php
get_footer();
