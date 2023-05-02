<?php

/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package komandir
 */

?>

<?php

global $svg;

?>
<!doctype html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<base href="<?= get_home_url(); ?>/">
	</base>

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
	<?php wp_body_open(); ?>
	<!-- <a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'komandir' ); ?></a> -->

	<header id="masthead" class="site-header">
		<div class="mobile-side-menu">
			<button type="button" class="mobile-side-menu-close" aria-label="Закрыть меню"></button>
			<div class="mobile-side-logo">
				<?php the_custom_logo(); ?>
			</div>
			<a class="mobile-side-phone" href="tel:+79232999688" aria-label="Нажмите, чтобы позвонить нам"></a>
			<div class="mobile-side-menu-list-wrapper">
				<ul class="mobile-side-menu-list">
					<li class="mobile-side-menu-item">
						<a href="/promotion">Все акции</a>
					</li>
					<li class="mobile-side-menu-item">
						<a href="/shops">Магазины</a>
					</li>
					<li class="mobile-side-menu-item">
						<a href="/shipping">Доставка</a>
					</li>
					<li class="mobile-side-menu-item">
						<a href="/juridical">Юридическим лицам</a>
					</li>
					<li class="mobile-side-menu-item">
						<a href="/credit">Рассрочка и кредит</a>
					</li>
					<li class="mobile-side-menu-item">
						<a href="/refund">Как вернуть или обменять товар?</a>
					</li>
					<li class="mobile-side-menu-item">
						<a href="/guarantee">Гарантии Командира</a>
					</li>
					<li class="mobile-side-menu-item">
						<a href="/contacts">Контакты</a>
					</li>
					<li class="mobile-side-menu-item">
						<a href="privacy-policy">Политика конфиденциальности</a>
					</li>
					<li class="mobile-side-menu-item">
						<a href="/public-offer">Публичная оферта</a>
					</li>
				</ul>
			</div>
		</div>
		<div class="container">
			<nav class="row d-none d-lg-flex top-menu">
				<div class="col-auto top-menu-location">
					<a>
						<?= $svg->view_from_sprite( [
							'class' => 'top-menu-icon',
							'title' => 'geoloc',
							'width' => 16,
							'height' => 20
						] ); ?>
						Лесосибирск
					</a>
				</div>
				<ul class="col-auto d-flex flex-grow-1">
					<li class="top-menu-item">
						<a href="<?= get_permalink( get_page_by_path( 'shops' ) ) ?>">
							<?= $svg->view_from_sprite( [
								'class' => 'top-menu-icon',
								'title' => 'store',
								'width' => 18,
								'height' => 16
							] ); ?>
							<?= get_the_title( get_page_by_path( 'shops' ) ); ?>
						</a>
					</li>
					<li class="top-menu-item">
						<a href="<?= get_permalink( get_page_by_path( 'shipping' ) ) ?>">
							<?= $svg->view_from_sprite( [
								'class' => 'top-menu-icon',
								'title' => 'shipping',
								'width' => 22,
								'height' => 16
							] ); ?>
							<?= get_the_title( get_page_by_path( 'shipping' ) ); ?>
						</a>
					</li>
					<li class="top-menu-item">
						<a href="<?= get_permalink( get_page_by_path( 'juridical' ) ) ?>">
							<?= $svg->view_from_sprite( [
								'class' => 'top-menu-icon',
								'title' => 'business',
								'width' => 20,
								'height' => 16
							] ); ?>
							<?= get_the_title( get_page_by_path( 'juridical' ) ); ?>
						</a>
					</li>
				</ul>
				<a class="col-auto" href="tel:+79232999688" class="top-menu-phone">8 (923) 299-96-88</a>
			</nav>
			<nav
				class="d-flex align-items-center align-items-lg-stretch justify-content-between justify-content-lg-start middle-menu">
				<div class="order-2 order-lg-0 middle-menu-logo">
					<?php the_custom_logo(); ?>
				</div>
				<div class="order-3 order-lg-0 flex-lg-grow-1 middle-menu-livesearch">
					<button class="livesearch-mobile-btn">Открыть поиск</button>
					<div class="livesearch-wrapper">
						<button class="livesearch-mobile-back" type="button" aria-label="Закрыть поиск"></button>
						<?php aws_get_search_form( true ); ?>
					</div>
				</div>
				<div class="d-none d-lg-block middle-menu-item middle-menu-wishlist">
					<a href="<?= get_permalink( get_page_by_path( 'wishlist' ) ); ?>"
						title="<?php _e( 'Показать избранное' ); ?>">
						<?= $svg->view_from_sprite( [
							'title' => 'favorite',
							'width' => 21,
							'height' => 20
						] ); ?>
						<?php
						$wishlist_count = get_wishlist_count();
						?>
						<span class="wishlist-count <?php if ( 0 == $wishlist_count )
							echo 'wishlist-count-empty' ?>">
							<?= $wishlist_count; ?>
						</span>
						<?= get_the_title( get_page_by_path( 'wishlist' ) ); ?>
					</a>
				</div>
				<div class="d-none d-lg-block  middle-menu-item middle-menu-cart">

					<a class="cart-header-link" href="<?= get_permalink( get_page_by_path( 'cart' ) ); ?>"
						title="<?php _e( 'Показать корзину' ); ?>">
						<?= $svg->view_from_sprite( [
							'title' => 'cart',
							'width' => 21,
							'height' => 20
						] ); ?>
						<?php
						$cart_count = WC()->cart->get_cart_contents_count();

						if ( $cart_count ) { ?>
							<span class="cart-count">
								<?= $cart_count; ?>
							</span>
						<?php }
						echo get_the_title( get_page_by_path( 'cart' ) ); ?>
					</a>
				</div>
				<?php global $current_user; ?>
				<div class="d-none d-lg-block middle-menu-item middle-menu-account">
					<?php if ( $current_user->exists() ) : ?>

						<a href="<? echo wc_get_account_endpoint_url( 'edit-account' ); ?>" class="account-link logged-in">
							<?= $svg->view_from_sprite( [
								'title' => 'logged-in',
								'width' => 20,
								'height' => 20
							] ); ?> <span>
								<?= $current_user->user_firstname ??
									$current_user->user_login;
								?>
							</span>
						</a>

					<?php else : ?>

						<a href="#login-popup" class="account-link logged-out">
							<?= $svg->view_from_sprite( [
								'title' => 'logged-out',
								'width' => 20,
								'height' => 20
							] ); ?> <span>Войти</span>
						</a>

					<?php endif; ?>
				</div>
				<div class="order-1 d-lg-none middle-menu-mobile">
					<button type="button" class="middle-menu-mobile-btn">Открыть меню</button>
					<!-- <ul class="middle-menu-list"> -->
					<!-- <li></li> -->
					<!-- </ul> -->
				</div>
			</nav>

			<nav id="desktop-navigation" class="d-none d-lg-block main-navigation">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'menu-1',
						'menu_id' => 'primary-menu',
					)
				);
				?>
			</nav><!-- #desktop-navigation -->
		</div>
		<nav class="d-lg-none mobile-nav">
			<div class="container">
				<ul class="row align-items-stretch mobile-nav-list">
					<li class="col mobile-nav-item">
						<a href="<?= get_home_url(); ?>" class="<?= is_front_page() ? 'active' : '' ?>">
							<?= $svg->view_from_sprite( [
								'title' => 'home',
								'width' => 16,
								'height' => 18
							] ); ?>
							<?= get_the_title( get_page_by_path( 'main' ) ); ?>
						</a>
					</li>
					<li class="col mobile-nav-item">
						<a href="<?= get_permalink( get_page_by_path( 'catalog' ) ); ?>"
							class="<?= is_page( 'catalog' ) ? 'active' : '' ?>">
							<?= $svg->view_from_sprite( [
								'title' => 'catalog',
								'width' => 20,
								'height' => 13
							] ); ?>
							<?= get_the_title( get_page_by_path( 'catalog' ) ); ?>
						</a>
					</li>
					<li class="col mobile-nav-item">
						<a class="cart-header-link" href="<?php echo get_permalink( get_page_by_path( 'cart' ) ); ?>"
							title="<?php _e( 'Показать корзину' ); ?>">
							<?= $svg->view_from_sprite( [
								'title' => 'cart',
								'width' => 21,
								'height' => 20
							] ); ?>
							<?php
							$cart_count = WC()->cart->get_cart_contents_count();

							if ( $cart_count ) { ?>
								<span class="cart-count">
									<?php echo $cart_count; ?>
								</span>
							<?php }
							echo get_the_title( get_page_by_path( 'cart' ) ); ?>
						</a>
					</li>
					<li class="col mobile-nav-item">
						<a href="<?= get_permalink( get_page_by_path( 'wishlist' ) ); ?>"
							class="<?= is_page( 'wishlist' ) ? 'active' : '' ?>">
							<?= $svg->view_from_sprite( [
								'title' => 'favorite',
								'width' => 21,
								'height' => 20
							] ); ?>
							<span class="wishlist-count <?php if ( 0 == $wishlist_count )
								echo 'wishlist-count-empty' ?>">
								<?= $wishlist_count; ?>
							</span>
							<?= get_the_title( get_page_by_path( 'wishlist' ) ); ?>
						</a>
					</li>
					<li class="col mobile-nav-item">
						<?php if ( $current_user->exists() ) : ?>
							<a href="<?= wc_get_account_endpoint_url( 'edit-account' ); ?>"
								class="account-link logged-in <?= is_page( 'account' ) ? 'active' : '' ?>">
								<?= $svg->view_from_sprite( [
									'title' => 'logged-in',
									'width' => 20,
									'height' => 20
								] ); ?>
								<span>
									<?php
									if ( $current_user->exists() ) {
										echo $current_user->first_name ??
											$current_user->display_name ??
											$current_user->user_login ??
											$current_user->user_email;
									} else {
										echo 'Войти';
									}
									?>
								</span>
							</a>
						<?php else : ?>
							<a href="#login-popup"
								class="account-link logged-out <?= is_page( 'account' ) ? 'active' : '' ?>">
								<?= $svg->view_from_sprite( [
									'title' => 'logged-out',
									'width' => 20,
									'height' => 20
								] ); ?>
								<span>
									<?php
									if ( $current_user->exists() ) {
										echo $current_user->display_name ??
											$current_user->user_name ??
											$current_user->user_login ??
											$current_user->user_email;
									} else {
										echo 'Войти';
									}
									?>
								</span>
							</a>
						<?php endif; ?>

					</li>
				</ul>
			</div>
		</nav>
	</header><!-- #masthead -->
