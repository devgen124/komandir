<?php

add_action( 'after_setup_theme', function () {
	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
} );

// woocommerce archive and single page wrappers

remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );

add_action( 'woocommerce_before_main_content', function () {
	?>
	<main id="primary" class="site-main">
		<div class="container">
			<?php
}, 10 );

add_action( 'woocommerce_after_main_content', function () {
	?>
		</div>
	</main><!-- #main -->
	<?php
}, 20 );

add_filter( 'woocommerce_add_to_cart_message', fn () => 'success' );

//ajax add to cart

add_filter( 'woocommerce_add_to_cart_fragments', 'komandir_header_add_to_cart_fragment' );

function komandir_header_add_to_cart_fragment( $fragments ) {
	global $woocommerce;

	ob_start();

	?>
	<a class="cart-header-link" href="<?php echo get_permalink( get_page_by_path( 'cart' ) ); ?>"
		title="<?php _e( 'Показать корзину' ); ?>">
		<svg width="21" height="20">
			<use xlink:href="<?= get_template_directory_uri() ?>/assets/images/sprite.svg#cart"></use>
		</svg>
		<?php
		$cart_count = WC()->cart->get_cart_contents_count();

		if ( $cart_count ) { ?>
			<span class="cart-count">
				<?php echo $cart_count; ?>
			</span>
		<?php }
		echo get_the_title( get_page_by_path( 'cart' ) ); ?>
	</a>
	<?php
	$fragments['a.cart-header-link'] = ob_get_clean();
	return $fragments;
}

remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );

add_action( 'woocommerce_after_shop_loop_item_title', 'komandir_woocommerce_template_rating', 5 );
add_action( 'woocommerce_single_product_summary', 'komandir_woocommerce_template_rating', 10 );

function komandir_woocommerce_template_rating() {
	global $product;

	// if ( ! wc_review_ratings_enabled() ) {
	// 	return;
	// }

	$html = komandir_get_rating_html( $product->get_average_rating() );

	$reviews = get_reviews_count( $product->get_review_count() );

	$html .= <<<HTML
		<div class="custom-reviews">
			$reviews
		</div>
	</div>
	HTML;

	echo $html;
}

function komandir_get_rating_html( $rating, $count = 0 ) {
	$html = '';
	$rating = round( $rating, 1 );

	$label = esc_attr( sprintf( __( 'Rated %s out of 5', 'woocommerce' ), $rating ) );
	// $html  = '<div class="custom-star-rating" role="img" aria-label="' . esc_attr( $label ) . '">' . komandir_wc_get_star_rating_html( $rating, $count ) . '</div>';
	$html = <<<HTML
	<div class="custom-rating">
		<div class="custom-star-rating" role="img" aria-label="$label">
			$rating
		</div>
	HTML;

	return apply_filters( 'woocommerce_product_get_rating_html', $html, $rating, $count );
}

// function komandir_wc_get_star_rating_html( $rating, $count = 0 ) {
// 	$html = '<span style="width:' . ( ( $rating / 5 ) * 100 ) . '%">';

// 	if ( 0 < $count ) {
// 		/* translators: 1: rating 2: rating count */
// 		$html .= sprintf( _n( 'Rated %1$s out of 5 based on %2$s customer rating', 'Rated %1$s out of 5 based on %2$s customer ratings', $count, 'woocommerce' ), '<strong class="rating">' . esc_html( $rating ) . '</strong>', '<span class="rating">' . esc_html( $count ) . '</span>' );
// 	} else {
// 		/* translators: %s: rating */
// 		$html .= sprintf( esc_html__( 'Rated %s out of 5', 'woocommerce' ), '<strong class="rating">' . esc_html( $rating ) . '</strong>' );
// 	}

// 	$html .= '</span>';

// 	return apply_filters( 'woocommerce_get_star_rating_html', $html, $rating, $count );
// }

function get_reviews_count( $count ) {
	$word = ' ';

	if ( $count == 1 ) {
		$word .= 'отзыв';
	} elseif ( in_array( $count, [ 2, 3, 4 ] ) ) {
		$word .= 'отзыва';
	} else {
		$word .= 'отзывов';
	}

	return $count . $word;
}

remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );

add_action( 'woocommerce_after_shop_loop_item_title', 'komandir_get_template_price', 15 );
add_action( 'woocommerce_single_product_summary', 'komandir_get_template_price', 15 );

function komandir_get_template_price() {
	global $product;
	$sale_price = $product->get_sale_price();
	$regular_price = $product->get_regular_price();

	if ( $product->get_price() ) : ?>
		<div class="custom-price">
			<?php if ( $product->is_on_sale() ) : ?>
				<div class="custom-price-row">
					<span class="custom-crossed-price">
						<?php echo wc_price( $regular_price ); ?>
					</span>
					<?php if ( $product->is_type( 'simple' ) ) : ?>
						<span class="custom-diff">
							<?php echo wc_price( $sale_price - $regular_price ); ?>
						</span>
					<?php endif; ?>
				</div>
				<span class="custom-main-price">
					<?php echo wc_price( $sale_price ); ?>
				</span>
			<?php else : ?>
				<span class="custom-main-price">
					<?php echo wc_price( $regular_price ); ?>
				</span>
			<?php endif; ?>
		</div>
	<?php endif;
}

remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );

add_filter( 'woocommerce_sale_flash', '__return_false' );


remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );

add_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );

// add_action( 'komandir_get_wishlist_count', 'get_wishlist_count' );

add_action( 'wp_ajax_update_wishlist_count', 'print_wishlist_count' );
add_action( 'wp_ajax_nopriv_update_wishlist_count', 'print_wishlist_count' );

function print_wishlist_count() {
	echo get_wishlist_count();
	wp_die();
}

function get_wishlist_count() {
	global $wpdb;

	$user = get_current_user_id();
	if ( $user != 0 ) {
		$table_name = $wpdb->prefix . 'wt_wishlists';
		$count = $wpdb->get_var( "SELECT COUNT(*) FROM `$table_name` where `user_id` = '$user'" );
	} else {

		$table_name = $wpdb->prefix . 'wt_guest_wishlists';
		$session_id = WC()->session->get( 'sessionid' );
		$count = $wpdb->get_var( "SELECT COUNT(*) FROM `$table_name` where `session_id` = '$session_id'" );
	}

	return $count;
}

//change the breadcrumb delimeter from '/' to '>'

add_filter( 'woocommerce_breadcrumb_defaults', function ( $defaults ) {
	$defaults['delimiter'] = ' &gt; ';
	return $defaults;
} );

remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );

add_filter( 'woocommerce_get_image_size_gallery_thumbnail', function ($size) {
	return array(
		'width' => 150,
		'height' => 150,
		'crop' => 1,
	);
} );

add_filter( 'woocommerce_reviews_title', '__return_empty_string' );

add_filter( 'wishlist_table_heading', '__return_null' );

// removes my account menu items

add_filter( 'woocommerce_account_menu_items', function ( $items ) {
	return array_filter( $items, fn($k) => ! in_array( $k, [ 'dashboard', 'downloads', 'edit-address' ] ), ARRAY_FILTER_USE_KEY );
} );

// redirect default account page to my orders

add_action( 'template_redirect', function () {
	if( is_account_page() && empty( WC()->query->get_current_endpoint() ) ){
		wp_safe_redirect( wc_get_account_endpoint_url( 'orders' ) );
		exit;
	}
} );

// mobile catalog ajax

add_action( 'wp_ajax_get_children_cats', 'render_children_cat_list' );
add_action( 'wp_ajax_nopriv_get_children_cats', 'render_children_cat_list' );

function render_children_cat_list() {
	$cat_id = $_POST['cat_id'];

	$category = get_term( $cat_id, 'product_cat' );

	$child_categories = get_categories( [
		'taxonomy' => 'product_cat',
		'parent' => $cat_id
	] );
	?>

	<div class="container">

		<a class="catalog-slide-backlink">
			<?= $category->name; ?>
		</a>

		<ul class="catalog-slide-list">
			<?php foreach ( $child_categories as $child ) :
				$grandchild_categories = get_categories( [
					'taxonomy' => 'product_cat',
					'parent' => $child->term_id
				] );

				$this_cat_id = $grandchild_categories ? $child->term_id : '';
				?>
				<li class="catalog-slide-item">
					<a href="<?= get_term_link( $child->slug, 'product_cat' ) ?>" data-product-cat="<?= $this_cat_id; ?>">
						<?= $child->name; ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>

	</div>

	<?php
	wp_die();
}

// custom sorting

add_filter( 'woocommerce_catalog_orderby', function () {
	return array(
		'menu_order' => __( 'Default sorting', 'woocommerce' ),
		'popularity' => __( 'Sort by popularity', 'woocommerce' ),
		'rating' => __( 'Sort by average rating', 'woocommerce' ),
		'price' => __( 'Sort by price: low to high', 'woocommerce' ),
		'price-desc' => __( 'Sort by price: high to low', 'woocommerce' ),
	);
} );

// remove products count

remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );

// add catalog to breadcrumbs on second position

add_filter( 'woocommerce_get_breadcrumb', function ( $crumbs ) {
	if ( ! empty( $crumbs ) ) {
		if ( is_product_tag() ) {
			global $wp_query;
			$this_query = $wp_query->query;
			$product_tag = get_term_by( 'slug', $this_query['product_tag'], 'product_tag' );
			if ( isset( $_GET['preview'] ) ) {
				$crumbs[1][0] = 'Акция "' . $product_tag->name . '"';
				$crumbs = array_merge(
					array_slice( $crumbs, 0, -1 ),
					[ [ 'Акции', get_permalink( get_page_by_path( 'promotion' ) ) ] ],
					[ $crumbs[1] ]
				);
			} else {
				$crumbs[1][0] = 'Товары по акции "' . $product_tag->name . '"';
			}
		}

		if ( ! isset( $_GET['s'] ) ) {
			$catalog_page_id = wc_get_page_id( 'shop' );
			$catalog_page = get_post( $catalog_page_id );
			$catalog = array( get_the_title( $catalog_page ), get_permalink( $catalog_page ) );
			array_splice( $crumbs, 1, 0, array( $catalog ) );
		}

		return $crumbs;
	}
}, 20 );

// hide page title in archive page

add_filter( 'woocommerce_show_page_title', '__return_false' );

// add filters mobile button

add_action( 'woocommerce_before_shop_loop', function () {
	global $svg;
	?>
	<div class="filters-mobile">
		<button class="filters-btn">
			<?= $svg->view_from_sprite( [
				'title' => 'filters',
				'width' => 18,
				'height' => 18
			] ); ?>
		</button>
	</div>
	<?php
}, 39 );

// add view grid switcher

add_action( 'woocommerce_before_shop_loop', function () {
	global $svg;
	?>
	<div class="grid-switcher">
		<button class="grid-view grid-view--list" data-grid="list">
			<?= $svg->view_from_sprite( [
				'title' => 'view-list',
				'width' => 18,
				'height' => 14
			] ); ?>
		</button>
		<button class="grid-view grid-view--grid grid-view--active" data-grid="grid">
			<?= $svg->view_from_sprite( [
				'title' => 'view-grid',
				'width' => 18,
				'height' => 18
			] ); ?>
		</button>
	</div>
	<?php
}, 40 );

// woocommerce top wrapper (ordering + grid switcher)

add_action( 'woocommerce_before_shop_loop', function () {
	echo '<div class="woocommerce-top-controls">';
}, 25 );

add_action( 'woocommerce_before_shop_loop', function () {
	echo '</div>';
}, 45 );

// woocommerce columns layout (aside filters + products)

add_action('woocommerce_before_shop_loop', function () {
	echo '<div class="woocommerce-main-wrapper">';
}, 50 );

add_action('woocommerce_after_main_content', function () {
	echo '</div>';
}, 10 );

add_action( 'widgets_init', function () {
	register_sidebar(
		array(
			'name' => esc_html__( 'Woo Sidebar', 'komandir' ),
			'id' => 'shop',
			'description' => 'Sidebar for WC filters',
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget' => '</section>',
			'before_title' => '<h2 class="widget-title">',
			'after_title' => '</h2>',
		)
	);
} );

// products + pagination wrapper

add_action( 'woocommerce_products_wrapper_start', function () {
	echo '<div class="products-wrapper">';
} );

add_action('woocommerce_products_wrapper_end', function () {
	echo '</div>';
} );

//related products columns count

add_filter( 'woocommerce_output_related_products_args', function ( $args ) {
	$args['columns'] = 5;
	return $args;
}, 20 );

add_action( 'woocommerce_after_shop_loop_item', 'komandir_archive_additional_info', 20 );

function komandir_archive_additional_info() {
	global $product;

	$product_attributes = array();

	// Display weight and dimensions before attribute list.
	$display_dimensions = apply_filters( 'wc_product_enable_dimensions_display', $product->has_weight() || $product->has_dimensions() );

	if ( $display_dimensions && $product->has_weight() ) {
		$product_attributes['weight'] = array(
			'label' => __( 'Weight', 'woocommerce' ),
			'value' => wc_format_weight( $product->get_weight() ),
		);
	}

	if ( $display_dimensions && $product->has_dimensions() ) {
		$product_attributes['dimensions'] = array(
			'label' => __( 'Dimensions', 'woocommerce' ),
			'value' => wc_format_dimensions( $product->get_dimensions( false ) ),
		);
	}

	// Add product attributes to list.
	$attributes = array_filter( $product->get_attributes(), 'wc_attributes_array_filter_visible' );
	$attributes = array_slice( $attributes, 0, 6 );

	foreach ( $attributes as $i => $attribute ) {
		$values = array();

		if ( $attribute->is_taxonomy() ) {
			$attribute_taxonomy = $attribute->get_taxonomy_object();
			$attribute_values = wc_get_product_terms( $product->get_id(), $attribute->get_name(), array( 'fields' => 'all' ) );

			foreach ( $attribute_values as $attribute_value ) {
				$value_name = esc_html( $attribute_value->name );

				$values[] = $value_name;
			}
		} else {
			$values = $attribute->get_options();

			foreach ( $values as &$value ) {
				$value = make_clickable( esc_html( $value ) );
			}
		}

		$product_attributes[ 'attribute_' . sanitize_title_with_dashes( $attribute->get_name() ) ] = array(
			'label' => wc_attribute_label( $attribute->get_name() ),
			'value' => apply_filters( 'woocommerce_attribute', wpautop( wptexturize( implode( ', ', $values ) ) ), $attribute, $values ),
		);
	}

	$product_attributes = apply_filters( 'woocommerce_display_product_attributes', $product_attributes, $product );

	if ( ! $product_attributes ) {
		return;
	}
	?>
	<div class="woo-attributes">
		<a class="woo-attributes-heading">Основные характеристики</a>
		<div class="woo-attributes-inner">
			<?php foreach ( $product_attributes as $product_attribute_key => $product_attribute ) : ?>
				<div class="woo-attributes-item woo-attributes-item--<?php echo esc_attr( $product_attribute_key ); ?>">
					<div class="woo-attributes-item__label">
						<?php echo wp_strip_all_tags( $product_attribute['label'] ); ?>
					</div>
					<div class="woo-attributes-item__value">
						<?php echo wp_strip_all_tags( $product_attribute['value'] ); ?>
					</div>
				</div>
			<?php endforeach; ?>

			<a class="woo-attributes-show-link" href="<?php echo $product->get_permalink(); ?>">Посмотреть все
				характеристики</a>
			<a class="woo-attributes-hide">Скрыть</a>
		</div>
	</div>

	<?php
}

add_action('woocommerce_after_shop_loop_item', function () {
	echo '<div class="woocommerce-add-cart-wrapper">';
}, 7 );
add_action('woocommerce_after_shop_loop_item', function () {
	echo '</div>';
}, 15 );

// customize single product tabs

add_filter( 'woocommerce_product_tabs', function ( $tabs ) {
	if ( isset( $tabs['description'] ) )
		$tabs['description']['title'] = 'О товаре';
	if ( isset( $tabs['additional_information'] ) )
		$tabs['additional_information']['title'] = 'Характеристики';

	return $tabs;
} );

// remove additional information heading

add_filter( 'woocommerce_product_additional_information_heading', '__return_false' );

// redirect to home page after logout

add_action( 'wp_logout', function () {
	wp_safe_redirect( home_url() );
	exit;
} );

// save phone in account details

add_action( 'woocommerce_save_account_details', function ( $user_id ) {
	if ( isset( $_POST['account_phone'] ) ) {
		update_user_meta( $user_id, 'phone_number', sanitize_text_field( $_POST['account_phone'] ) );
	}
}, 12, 1 );

add_action( 'woocommerce_save_account_details', function () {
	wp_safe_redirect( wc_get_endpoint_url( 'edit-account' ) );
	exit();
}, 90, 1 );

// custom my address formatted address

// add_filter('woocommerce_my_account_my_address_formatted_address', 'komandir_my_account_my_address_formatted_address', 10, 3);

// function komandir_my_account_my_address_formatted_address($address, $customer_id, $address_type) {
//     $address['phone'] = get_user_meta($customer_id, 'phone_number', true);
//     return $address;
// };

// custom edit address in myaccount

// add_filter('woocommerce_my_account_get_addresses', 'komandir_my_account_get_addresses');

// function komandir_my_account_get_addresses($array) {
//     $array['billing'] = 'Адрес';
//     return $array;
// }

// add_filter('woocommerce_localisation_address_formats', 'komandir_localisation_address_formats');

// function komandir_localisation_address_formats($array) {
//     $array['default'] = "<b>Имя:</b> {name}\n<b>Телефон:</b> {phone}\n<b>Адрес:</b> {address_1}, {city}, {state}, {country}";

//     return $array;
// }

add_filter( 'woocommerce_formatted_address_replacements', function ( $replacements, $args ) {
	$replacements['{phone}'] = $args['phone'];
	return $replacements;
}, 10, 2 );

// phone_number to billing_phone

add_filter( 'woocommerce_my_account_edit_address_field_value', function ( $value, $key, $load_address ) {
	if ( $key === 'billing_phone' ) {
		$value = get_user_meta( get_current_user_id(), 'phone_number', true );
	}
	return $value;
}, 10, 3 );

// change product placeholder

add_filter( 'woocommerce_placeholder_img_src', fn ( $src ) => get_template_directory_uri() . '/assets/images/placeholder.svg' );

// add placeholder in aws results output

add_filter( 'aws_search_pre_filter_single_product', function ( $result ) {
	if ( ! $result['image'] ) {
		$result['image'] = wc_placeholder_img_src();
	}

	return $result;
}, 10 );

// add shipping warning

add_action( 'woocommerce_checkout_order_review', function () {
	?>
	<p><b>По вопросу получения товара мы с Вами свяжемся после оформления заказа. С условиями доставки можно ознакомиться на странице <a href="/shipping">Доставка</a></b></p>
	<?php
} );

// remove product attributes links

// add_filter( 'woocommerce_display_product_attributes', 'komandir_remove_product_attributes_links' );

// function komandir_remove_product_attributes_links($product_attributes) {
//     var_dump($product_attributes);
//     return $product_attributes;
// }

// DEBUG LOG FUNCTION

function log_error( $var ) {
	ob_start();
	var_dump( $var );
	$res = ob_get_contents();
	ob_end_clean();
	error_log( 'KOMANDIR LOG ERROR: ' . $res );
}

add_action( 'woocommerce_new_order', function ( $order_id, $order ) {
	$user = $order->get_user();
	$phone = get_user_meta( $order->get_user_id(), 'billing_phone', true );
	update_post_meta( $order_id, '_billing_phone', $phone );
	update_post_meta( $order_id, '_billing_first_name', $user->first_name );
	update_post_meta( $order_id, '_billing_last_name', $user->last_name );
}, 10, 2 );

// добавление суммы отрицательных сборов как скидки при обмене с 1С

// add_filter( 'itglx/wc/1c/sale/query/order-discount-list', function ( $list, $order ) {
// 	$total_fees = array_reduce(
// 		$order->get_fees(),
// 		function ($total, $fee) {
// 			$amount = $fee->get_amount();
// 			return $amount < 0 ? ( $total + $amount ) : $total;
// 		}
// 	);

// 	if ( $total_fees ) {
// 		$list[] = [
// 			'Наименование' => 'Скидка',
// 			'Сумма' => -$total_fees,
// 			'УчтеноВСумме' => 'true',
// 		];
// 	}

// 	return $list;

// }, 10, 2 );



// убирает display name из обязательных полей в личном кабинете

// add_filter('woocommerce_save_account_details_required_fields', function ($array) {

// 	if (isset($array['account_display_name'])) {
// 		unset($array['account_display_name']);
// 	}

// 	return $array;
// });

// убирает кнопку ссылку "Убрать" у "псевдокупона" (скидка из плагина Woo Discounts)

add_filter('woocommerce_cart_totals_coupon_html', function ($coupon_html, $coupon, $discount_amount_html) {
	if ( is_null( $coupon->get_status() ) ) {
		$coupon_html = $discount_amount_html;
	}
	return $coupon_html;
}, 10, 3 );

// добавляет в обмен subtotal вместо total для совместимости с 1с

// add_filter( 'itglx_wc1c_xml_order_product_row_params', function ($product, $order) {
// 	foreach ( $order->get_items as $item ) {
// 		if ( $product['id'] == $item['product_id'] ) {
// 			$product['lineTotal'] = round((float) $item->get_subtotal(), wc_get_price_decimals());
// 		}
// 	}
// 	return $product;
// }, 10, 2 );

// таксономия "Акции" для товаров

add_action( 'init', function () {
	register_taxonomy( 'promotion', 'product', [
		'labels'                => [
			'name'              => 'Акции',
			'singular_name'     => 'Акция',
			'search_items'      => 'Найти акции',
			'all_items'         => 'Все акции',
			'view_item '        => 'Посмотреть акцию',
			'edit_item'         => 'Редактировать акцию',
			'update_item'       => 'Обновить акцию',
			'add_new_item'      => 'Добавить новую акцию',
			'new_item_name'     => 'Название новой акции',
			'back_to_items'     => '← Перейти к акциям',
		],
		'description'           => 'Промоакции для товаров', // описание таксономии
		'public'                => true,
		// 'publicly_queryable'    => null, // равен аргументу public
		// 'show_in_nav_menus'     => true, // равен аргументу public
		// 'show_ui'               => true, // равен аргументу public
		// 'show_in_menu'          => true, // равен аргументу show_ui
		// 'show_tagcloud'         => true, // равен аргументу show_ui
		// 'show_in_quick_edit'    => null, // равен аргументу show_ui
		'hierarchical'          => false,

		'rewrite'               => true,
		//'query_var'             => $taxonomy, // название параметра запроса
		'capabilities'          => array(),
		'meta_box_cb'           => null, // html метабокса. callback: `post_categories_meta_box` или `post_tags_meta_box`. false — метабокс отключен.
		'show_admin_column'     => false, // авто-создание колонки таксы в таблице ассоциированного типа записи. (с версии 3.5)
		'show_in_rest'          => true, // добавить в REST API
		'rest_base'             => null, // $taxonomy
		// '_builtin'              => false,
		//'update_count_callback' => '_update_post_term_count',
	] );
} );
