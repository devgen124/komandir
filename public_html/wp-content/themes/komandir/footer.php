<?php

/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package komandir
 */

?>

<footer id="colophon" class="site-footer">
	<div class="container">
		<div class="row footer-row">
			<div class="col-12 col-md-4 pb-5 pb-md-0">
				<ul class="footer-links-list">
					<li>
						<a href="/refund">Как вернуть или обменять товар?</a>
					</li>
					<li>
						<a href="/guarantee">Гарантии Командира</a>
					</li>
					<li>
						<a href="/contacts">Контакты</a>
					</li>
					<li>
						<a href="/privacy-policy">Политика конфиденциальности</a>
					</li>
					<li>
						<a href="/public-offer">Публичная оферта</a>
					</li>
				</ul>
			</div>
			<div class="col-12 col-md-4 pb-5 pb-md-0">
				<div class="footer-social">
					<h3>Мы в социальных сетях</h3>
					<ul class="footer-social-list">
						<?php
						$social_list = [
							[
								'title' => 'Мы в WhatsApp',
								'link' => '#',
								'icon' => 'whatsapp.svg'
							],
							[
								'title' => 'Мы в Telegram',
								'link' => '#',
								'icon' => 'telegram.svg'
							],
							[
								'title' => 'Мы в Viber',
								'link' => '#',
								'icon' => 'viber.svg'
							],
						];

						foreach ( $social_list as $item ) { ?>
							<li>
								<a href="<?= $item['link'] ?>" aria-label="<?= $item['title'] ?>">
									<img width="26" height="26"
										src="<?= get_template_directory_uri() ?>/assets/images/<?= $item['icon'] ?>"
										alt="<?= $item['title'] ?>">
								</a>
							</li>
						<?php }
						?>
					</ul>
				</div>
				<div class="footer-payment">
					<h3>Мы принимаем к оплате</h3>
					<ul class="footer-payment-list">
						<?php
						$social_list = [
							[
								'title' => 'Mastercard',
								'icon' => 'mastercard.svg',
								'size' => [
									'width' => 21,
									'height' => 21
								]
							],
							[
								'title' => 'Visa',
								'icon' => 'visa.svg',
								'size' => [
									'width' => 34,
									'height' => 11
								]
							],
							[
								'title' => 'Мир',
								'icon' => 'mir.svg',
								'size' => [
									'width' => 38,
									'height' => 11
								]
							],
						];
						foreach ( $social_list as $item ) { ?>

							<li>
								<a aria-label="<?= $item['title'] ?>">
									<img width="<?= $item['size']['width'] ?>" height="<?= $item['size']['height'] ?>"
										src="<?= get_template_directory_uri() ?>/assets/images/<?= $item['icon'] ?>"
										alt="<?= $item['title'] ?>">
								</a>
							</li>

						<?php }
						?>
					</ul>
				</div>
			</div>
			<div class="col-12 col-md-4 pb-5 pb-md-0">
				<div class="footer-subscribe">
					<?php echo do_shortcode( '[contact-form-7 id="80" title="Рассылка"]' ); ?>
				</div>
			</div>
		</div>
		<div class="row justify-content-between footer-row">
			<div class="col-auto">
				<div class="footer-copyright">© Командир 2023</div>
			</div>
			<div class="col-auto">
				<div class="footer-copyright">Разработка сайта Rustika</div>
			</div>
		</div>
	</div>
</footer>

<!-- <div id="location-popup" class="location-content">
	<p>Выберите город</p>
	<ul class="location-content-list">
		<li class="location-content-listitem"><a href="/?wt_city_by_default=Лесосибирск">Лесосибирск</a></li>
		<li class="location-content-listitem"><a href="/?wt_city_by_default=Енисейск">Енисейск</a></li>
		<li class="location-content-listitem"><a href="/?wt_city_by_default=Красноярск">Красноярск</a></li>
	</ul>
</div> -->

<?php get_template_part( 'template-parts/modal' ); ?>

<?php get_template_part( 'template-parts/login-popup' ); ?>

<?php wp_footer(); ?>

</body>

</html>
