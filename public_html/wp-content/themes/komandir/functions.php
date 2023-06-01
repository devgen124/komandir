<?php
/**
 * komandir functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package komandir
 */

if ( ! defined( '_S_VERSION' ) ) {
	// Replace the version number of the theme on each release.
	define( '_S_VERSION', '1.0.0' );
}

if ( ! function_exists( 'komandir_setup' ) ) :
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	function komandir_setup() {
		/*
		 * Make theme available for translation.
		 * Translations can be filed in the /languages/ directory.
		 * If you're building a theme based on komandir, use a find and replace
		 * to change 'komandir' to the name of your theme in all the template files.
		 */
		load_theme_textdomain( 'komandir', get_template_directory() . '/languages' );

		// Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support( 'title-tag' );

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		 */
		add_theme_support( 'post-thumbnails' );

		// This theme uses wp_nav_menu() in one location.
		register_nav_menus(
			array(
				'menu-1' => esc_html__( 'Primary', 'komandir' ),
			)
		);

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support(
			'html5',
			array(
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
				'style',
				'script',
			)
		);

		// Set up the WordPress core custom background feature.
		add_theme_support(
			'custom-background',
			apply_filters(
				'komandir_custom_background_args',
				array(
					'default-color' => 'ffffff',
					'default-image' => '',
				)
			)
		);

		// Add theme support for selective refresh for widgets.
		add_theme_support( 'customize-selective-refresh-widgets' );

		/**
		 * Add support for core custom logo.
		 *
		 * @link https://codex.wordpress.org/Theme_Logo
		 */
		add_theme_support(
			'custom-logo',
			array(
				'height'      => 250,
				'width'       => 250,
				'flex-width'  => true,
				'flex-height' => true,
			)
		);
	}
endif;
add_action( 'after_setup_theme', 'komandir_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function komandir_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'komandir_content_width', 640 );
}
add_action( 'after_setup_theme', 'komandir_content_width', 0 );

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function komandir_widgets_init() {
	register_sidebar(
		array(
			'name'          => esc_html__( 'Sidebar', 'komandir' ),
			'id'            => 'sidebar-1',
			'description'   => esc_html__( 'Add widgets here.', 'komandir' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'komandir_widgets_init' );

/**
 * Enqueue scripts and styles.
 */
function komandir_scripts() {
	wp_enqueue_style( 'komandir-style', get_stylesheet_uri(), array(), _S_VERSION );
	wp_style_add_data( 'komandir-style', 'rtl', 'replace' );
	wp_enqueue_style( 'komandir-build-style', get_stylesheet_uri(), array(), _S_VERSION );

	wp_enqueue_style( 'komandir-custom-style', get_template_directory_uri() . '/assets/css/style.css', _S_VERSION );

	// jQuery
	wp_deregister_script('jquery');
	wp_enqueue_script('jquery', get_template_directory_uri() . '/assets/vendor/js/jquery-3.6.0.min.js', array(), '3.6.0');

  // magnific popup

  wp_enqueue_style( 'magnific-popup', get_template_directory_uri() . '/assets/vendor/css/magnific-popup.css', _S_VERSION );
  wp_enqueue_script('magnific-popup', get_template_directory_uri() . '/assets/vendor/js/jquery.magnific-popup.min.js', array( 'jquery' ), _S_VERSION, true);

	// custom script

	wp_enqueue_script('komandir-script', get_template_directory_uri() . '/assets/js/script.js', array( 'jquery' ), _S_VERSION, true);
	wp_localize_script( 'komandir-script', 'dataObj', [
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
		'nonce' => wp_create_nonce( 'komandir-nonce' )
	]);

	// wp_enqueue_script( 'komandir-navigation', get_template_directory_uri() . '/js/navigation.js', array(), _S_VERSION, true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

	// яндекс карты

	if (is_page_template('shops.php')) {
		wp_enqueue_script( 'yandex-maps', 'https://api-maps.yandex.ru/2.1/?apikey=83d10a3a-1811-4704-889b-1cfaf129615f&load=Map,Placemark&lang=ru_RU', array( 'jquery' ), _S_VERSION);
	}
}
add_action( 'wp_enqueue_scripts', 'komandir_scripts', 100 );

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
if ( defined( 'JETPACK__VERSION' ) ) {
	require get_template_directory() . '/inc/jetpack.php';
}

/**
 * Woocommerce Addition.
 */

if ( class_exists( 'WooCommerce' ) ) {
	require_once(get_template_directory() . '/woo-functions.php');
    require_once(get_template_directory() . '/popup.php');
}

/**
 * Mailpoet Customization
 */

add_filter( 'mailpoet_manage_subscription_page_form_fields', 'komandir_remove_manage_fields', 10);
function komandir_remove_manage_fields( $form ) {

	unset($form[0]); // First Name
	unset($form[1]); // Last Name
    unset($form[3]); // Subscribe List

	return $form;
}

require 'helpers/svg-helper.php';

$svg = new SVGHelper();
$svg->init_sprite(get_template_directory_uri().'/assets/images/sprite.svg');

// функция подбора окончания для количественного существительного

function format_quantity_ending ($number, $nouns_array) {
	$remainder = $number % 10;
	if ($remainder === 1) {
		return "$number $nouns_array[0]";
	} elseif ($remainder > 1 && $remainder < 5) {
		return "$number $nouns_array[1]";
	} else {
		return "$number $nouns_array[2]";
	}
}

add_filter( 'wpcf7_spam', function( $spam ) {
	if ( $spam ) {
	  return $spam;
	}

	if ( isset( $_POST['consult-phone'] ) && ! preg_match( '/\+7 \d{3} \d{3} \d{2} \d{2}/', $_POST['consult-phone'] ) ) {
	  $spam = true;
	}

	return $spam;
}, 10, 1 );
