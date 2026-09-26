<?php
/**
 * Theme-Grundlagen: Unterstuetzung, Menues, Skripte, Aufraeumen.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'after_setup_theme',
	function () {
		load_theme_textdomain( 'bit', BIT_DIR . '/languages' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
		add_theme_support( 'responsive-embeds' );
		add_image_size( 'bit-card', 800, 600, true );
		add_image_size( 'bit-large', 1600, 1200, true );
		register_nav_menus(
			array(
				'primary' => 'Hauptnavigation (Kopf)',
				'footer'  => 'Fusszeile «Seite»',
			)
		);
	}
);

add_action(
	'wp_enqueue_scripts',
	function () {
		$ver = BIT_VERSION . '.' . filemtime( BIT_DIR . '/assets/css/site.css' );
		wp_enqueue_style( 'bit-ds', BIT_URI . '/assets/css/bit.css', array(), $ver );
		wp_enqueue_style( 'bit-site', BIT_URI . '/assets/css/site.css', array( 'bit-ds' ), $ver );
		wp_enqueue_script( 'bit-site', BIT_URI . '/assets/js/site.js', array(), $ver, array( 'strategy' => 'defer', 'in_footer' => true ) );
		// Keine Blockbibliothek-Styles, die Seite nutzt keinen Block-Look.
		wp_dequeue_style( 'wp-block-library' );
		wp_dequeue_style( 'wp-block-library-theme' );
		wp_dequeue_style( 'global-styles' );
		wp_dequeue_style( 'classic-theme-styles' );
	},
	20
);

/** Schriften vorladen, damit der erste Bildschirm nicht springt. */
add_action(
	'wp_head',
	function () {
		foreach ( array( 'archivo-latin', 'archivo-narrow-latin' ) as $font ) {
			printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url( BIT_URI . '/assets/fonts/' . $font . '.woff2' ) );
		}
		echo '<meta name="theme-color" content="#0C0E10">' . "\n";
	},
	1
);

/** Symbole einmal pro Seite einbetten, dann per <use href="#i-…"> verwenden. */
add_action(
	'wp_body_open',
	function () {
		$sprite = BIT_DIR . '/assets/icons/sprite.svg';
		if ( is_readable( $sprite ) ) {
			echo file_get_contents( $sprite ); // phpcs:ignore WordPress.Security.EscapeOutput -- eigene Datei im Theme.
		}
	}
);

/* Emoji-Skripte und Kommentare braucht die Seite nicht. */
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'wp_head', 'wp_generator' );
add_filter( 'comments_open', '__return_false', 20 );
add_filter( 'pings_open', '__return_false', 20 );
add_filter( 'comments_array', '__return_empty_array', 10 );

/** Titel: «Seite – BIT Automobile». */
add_filter(
	'document_title_separator',
	function () {
		return '–';
	}
);

/** Beschreibung fuer Suchmaschinen und geteilte Links. */
add_action(
	'wp_head',
	function () {
		if ( is_singular( 'fahrzeug' ) ) {
			$desc = bit_vehicle_summary( get_the_ID() );
		} else {
			$desc = 'BIT Automobile in Oberwangen b. Bern: Occasionen geprüft und ab MFK, Ankauf, Eintausch, Aufbereitung und Schätzung.';
		}
		printf( '<meta name="description" content="%s">' . "\n", esc_attr( $desc ) );
	},
	2
);
