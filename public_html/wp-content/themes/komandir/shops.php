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
        <h2><?php the_title(); ?></h2>
        <h3 class="shops-title">ТД «Арбат» — г. Лесосибирск, ул. Победы, д. 25</h3>
        <p class="shops-text">Время работы с 10:00 до 19:00 (без обеда и выходных)<br>+7-923-299-96-88 (WhatsApp и Viber)</p>
        <h3 class="shops-title">ТЦ «20-й» — г. Лесосибирск, ул. Горького, д. 84а</h3>
        <p class="shops-text">Время работы с 10:00 до 19:00 (без обеда и выходных)<br>+7 (39145) 4-11-59</p>
        <p class="shops-after-text">Эл. почта: komandir124@mail.ru<br>доставка и сервисное обслуживание: +7-950-990-49-00 (WhatsApp и Viber)</p>
      </div>
      <div id="map" class="map-container"></div>
      <script>
        ymaps.ready(
          function() {
            var myMap = new ymaps.Map("map", {
              center: [58.209538, 92.503860],
              zoom: 12
            });

            var markerSetup = {
              iconLayout: 'default#image',
              iconImageHref: '<?php echo get_template_directory_uri() . '/assets/images/marker.svg';?>',
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
