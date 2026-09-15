<?php
/**
 * DAS V4 — theme bootstrap
 * Data model comes from plugin tmm_theme_features (CPT car, tax carproducer, car_* meta).
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

// Bumped for the design pass of 15 September 2026: style.css and site.js both changed, and
// this string is what cache-busts them for a visitor who already holds the old files.
define( 'DAS_V4_VERSION', '1.3.1' );

function das_info( $key = null ) {
	$i = array(
		'name'    => 'Downtown Auto Sales',
		'legal'   => 'Downtown Auto Sales LLC',
		'street'  => '636 Ingra Street',
		'city'    => 'Anchorage',
		'state'   => 'AK',
		'zip'     => '99501',
		'phone1'  => '907-280-8181',
		'phone2'  => '907-750-5513',
		'email'   => 'info@downtownautosale.com',
		// The second address is kept in the declaration and left empty on purpose.
		// Gianni, 29 August 2026: info@ is the official address and the old outlook.com
		// one is only alive for customers who already have it — so it must stop being
		// handed to new ones. A site that keeps advertising an address is a site that
		// keeps it alive. One string here brings it back if that is ever wrong.
		'email2'  => '',
		'hours'   => 'Mon-Sat 10:30 AM - 7:00 PM',
		'founded' => '2016',
	);
	$i['tel1'] = '+1' . preg_replace( '/\D/', '', $i['phone1'] );
	$i['tel2'] = '+1' . preg_replace( '/\D/', '', $i['phone2'] );
	$i['addr'] = $i['street'] . ', ' . $i['city'] . ', ' . $i['state'] . ' ' . $i['zip'];
	return $key ? ( $i[ $key ] ?? '' ) : $i;
}

/**
 * The dealer's initials, derived from their name.
 *
 * The theme's own admin menus say whose content they hold - "Vehicles" reads
 * "Vehicles-DAS" - so nobody confuses them with WordPress's own. The suffix is
 * derived rather than typed, because §5 sells this same theme to the next
 * dealership, where it has to read their initials and not this one's.
 */
function das_initials( $fallback = '' ) {
	$name = trim( (string) das_info( 'name' ) );
	if ( '' === $name ) { return $fallback; }
	$skip = array( 'and', 'of', 'the', 'llc', 'inc' );
	$out  = '';
	foreach ( preg_split( '/[\s\-]+/', $name ) as $word ) {
		$word = preg_replace( '/[^A-Za-z0-9]/', '', $word );
		if ( '' === $word || in_array( strtolower( $word ), $skip, true ) ) { continue; }
		$out .= strtoupper( $word[0] );
		if ( strlen( $out ) >= 4 ) { break; }
	}
	return '' !== $out ? $out : $fallback;
}

/** "Vehicles" -> "Vehicles-DAS", or the bare label when no dealer name is set yet. */
function das_menu_label( $label ) {
	$initials = das_initials();
	return '' !== $initials ? $label . '-' . $initials : $label;
}

add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'custom-logo', array( 'height' => 96, 'width' => 178, 'flex-height' => true, 'flex-width' => true ) );
	add_theme_support( 'automatic-feed-links' );
	load_theme_textdomain( 'das-v4', get_template_directory() . '/languages' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'responsive-embeds' );
	add_image_size( 'das_card', 720, 480, true );
	add_image_size( 'das_hero', 1200, 800, true );
	register_nav_menus( array( 'primary' => __( 'Primary menu', 'das-v4' ) ) );
} );

add_action( 'after_setup_theme', function () { $GLOBALS['content_width'] = 1180; } );

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'das-fonts', 'https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600&family=Barlow+Condensed:wght@600;700;800&display=swap', array(), null );
	wp_enqueue_style( 'das-v4', get_stylesheet_uri(), array( 'das-fonts' ), DAS_V4_VERSION );
	wp_enqueue_script( 'das-v4', get_template_directory_uri() . '/assets/js/site.js', array(), DAS_V4_VERSION, true );
	wp_localize_script( 'das-v4', 'DAS', array(
		'cars'  => das_cars_for_js(),
		'phone' => das_info( 'tel1' ),
	) );
} );

/**
 * The three look-and-feel decisions from the design session of 15 September 2026, each
 * behind its own filter so any of them can go back to the theme's older behaviour with one
 * line in a child theme — no CSS editing, no fork.
 *
 *   das_look         'refined' (default) draws the capsule controls, the gradient fills and
 *                    the translucent header from §26. 'theme' is the flat original.
 *   das_motion_level 'calm' (default) keeps the entrances and drops the ambient loops.
 *                    'alive' runs everything in §24; 'still' runs nothing but the hover lift.
 *   das_shelf        'roomy' (default) 330px cards with a 30px gap — three across on a
 *                    desktop. 'regular' is the theme's own 268/20; 'dense' is 236/14.
 */
function das_shelf_sizes() {
	$shelves = array(
		'dense'   => array( '236px', '14px' ),
		'regular' => array( '268px', '20px' ),
		'roomy'   => array( '330px', '30px' ),
	);
	$key = (string) apply_filters( 'das_shelf', 'roomy' );
	return $shelves[ $key ] ?? $shelves['roomy'];
}

add_filter( 'body_class', function ( $classes ) {
	if ( 'refined' === apply_filters( 'das_look', 'refined' ) ) { $classes[] = 'look-refined'; }
	$motion = (string) apply_filters( 'das_motion_level', 'calm' );
	if ( in_array( $motion, array( 'still', 'calm' ), true ) ) { $classes[] = 'motion-' . $motion; }
	return $classes;
} );

// The shelf is two numbers, so it rides on the stylesheet rather than earning a file of its
// own — §9 allows the theme exactly one stylesheet and one script.
add_action( 'wp_enqueue_scripts', function () {
	list( $min, $gap ) = das_shelf_sizes();
	wp_add_inline_style( 'das-v4', sprintf( ':root{--card-min:%s;--gap-grid:%s}', $min, $gap ) );
}, 20 );

add_action( 'init', function () {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'rsd_link' );
} );

require_once get_template_directory() . '/inc/post-types.php';
require_once get_template_directory() . '/inc/template-tags.php';
require_once get_template_directory() . '/inc/cars.php';
require get_template_directory() . '/inc/watermark.php';
require_once get_template_directory() . '/inc/privacy-boundary.php';
require_once get_template_directory() . '/inc/meta-box.php';
require_once get_template_directory() . '/inc/leads.php';
require_once get_template_directory() . '/inc/mail-guard.php';
require_once get_template_directory() . '/inc/traffic.php';
require_once get_template_directory() . '/inc/analytics.php';
require_once get_template_directory() . '/inc/leads-api.php';
require_once get_template_directory() . '/inc/vehicle-photos-api.php';
require_once get_template_directory() . '/inc/seo.php';

add_action( 'pre_get_posts', function ( $q ) {
	if ( is_admin() || ! $q->is_main_query() ) { return; }
	if ( $q->is_post_type_archive( 'car' ) || $q->is_tax( 'carproducer' ) ) {
		$q->set( 'posts_per_page', 24 );
		$q->set( 'orderby', array( 'meta_value_num' => 'DESC', 'date' => 'DESC' ) );
		$q->set( 'meta_key', 'car_price' );
	}
} );

add_action( 'init', function () {
	foreach ( das_car_meta_keys() as $key => $type ) {
		register_post_meta( 'car', $key, array(
			'type'          => $type,
			'single'        => true,
			'show_in_rest'  => true,
			'auth_callback' => function () { return current_user_can( 'edit_posts' ); },
		) );
	}
}, 20 );

add_filter( 'register_post_type_args', function ( $args, $name ) {
	if ( 'car' === $name ) {
		$args['show_in_rest'] = true;
		$args['rest_base']    = 'cars';
		$args['supports']     = array_unique( array_merge( (array) ( $args['supports'] ?? array() ), array( 'title', 'editor', 'thumbnail', 'custom-fields', 'excerpt' ) ) );
	}
	return $args;
}, 10, 2 );

add_filter( 'register_taxonomy_args', function ( $args, $name ) {
	if ( 'carproducer' === $name ) { $args['show_in_rest'] = true; }
	return $args;
}, 10, 2 );