<?php
/**
 * Admin fuer Sabit: nur was er braucht.
 * - Uebersicht mit Kacheln: Fahrzeug erfassen, Fahrzeuge, Anfragen, Seiten.
 * - Beitraege und Kommentare ausgeblendet (die Seite hat keinen Blog).
 * - Fusszeile im Admin mit Kontakt fuer Support.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'admin_menu',
	function () {
		remove_menu_page( 'edit.php' );
		remove_menu_page( 'edit-comments.php' );
	},
	99
);

add_action(
	'admin_bar_menu',
	function ( WP_Admin_Bar $bar ) {
		$bar->remove_node( 'comments' );
		$bar->remove_node( 'new-post' );
		$bar->remove_node( 'wp-logo' );
	},
	99
);

add_action(
	'wp_dashboard_setup',
	function () {
		remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
		remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
		remove_meta_box( 'dashboard_site_health', 'dashboard', 'normal' );
		remove_meta_box( 'dashboard_activity', 'dashboard', 'normal' );
		wp_add_dashboard_widget( 'bit_welcome', 'BIT Automobile', 'bit_dashboard_widget' );
	}
);

function bit_dashboard_widget() {
	$cars   = wp_count_posts( 'fahrzeug' );
	$leads  = wp_count_posts( 'bit_anfrage' );
	$avail  = bit_vehicle_count();
	$tiles  = array(
		array( admin_url( 'post-new.php?post_type=fahrzeug' ), 'Fahrzeug erfassen', 'Neues Auto mit Fotos und Daten' ),
		array( admin_url( 'edit.php?post_type=fahrzeug' ), 'Fahrzeuge', $avail . ' auf dem Platz · ' . (int) $cars->publish . ' total' ),
		array( admin_url( 'edit.php?post_type=bit_anfrage' ), 'Anfragen', (int) $leads->private . ' gespeichert' ),
		array( admin_url( 'edit.php?post_type=page' ), 'Seiten', 'Impressum, Datenschutz, Texte' ),
		array( admin_url( 'customize.php?autofocus[section]=bit_company' ), 'Firmendaten', 'Telefon, Öffnungszeiten, E-Mail', 'customize' ),
		array( home_url( '/' ), 'Webseite ansehen', wp_parse_url( home_url(), PHP_URL_HOST ) ),
	);
	$tiles  = array_filter( $tiles, fn( $t ) => empty( $t[3] ) || current_user_can( $t[3] ) );
	echo '<style>.bit-tiles{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:10px}.bit-tiles a{display:block;padding:14px 16px;border:1px solid #dcdcde;border-radius:16px;text-decoration:none;background:#fff}.bit-tiles a:hover{border-color:#3C77B2}.bit-tiles b{display:block;font-size:14px;color:#1d2327}.bit-tiles span{color:#646970;font-size:12px}</style><div class="bit-tiles">';
	foreach ( $tiles as $t ) {
		printf( '<a href="%s"><b>%s</b><span>%s</span></a>', esc_url( $t[0] ), esc_html( $t[1] ), esc_html( $t[2] ) );
	}
	echo '</div>';
	if ( bit_has_demo_vehicles() ) {
		echo '<p style="margin-top:14px;padding:8px 12px;background:#fcf0dc;border-left:4px solid #dba617">Es sind noch <strong>Demo-Fahrzeuge</strong> (Testdaten) online. Vor dem Livegang löschen: Werkzeuge → BIT Demo-Daten.</p>';
	}
}

add_filter(
	'admin_footer_text',
	function () {
		return 'BIT Automobile · Webseite von TIFF Software Solutions';
	}
);

/** Farben im Admin passend zur Seite (dezent: nur Menue-Akzent). */
add_action(
	'admin_head',
	function () {
		echo '<style>#adminmenu li.current a.menu-top,#adminmenu .wp-has-current-submenu .wp-submenu-head,#adminmenu .wp-menu-arrow,#adminmenu .wp-menu-arrow div,#adminmenu li.wp-has-current-submenu a.wp-has-current-submenu{background:#3C77B2}</style>';
	}
);
