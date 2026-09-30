<?php
/**
 * Demo-Fahrzeuge (erfundene Testdaten) laden und wieder loeschen.
 *
 * Laeuft ueber denselben AutoScout24-Import wie spaeter die echten Daten,
 * nur mit der Datei demo/as24-demo.json. Alles wird als «Demo»
 * markiert und auf der Seite sichtbar so beschriftet.
 * Werkzeuge → BIT Demo-Daten, oder «wp bit demo load|remove».
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function bit_demo_file() {
	return BIT_DIR . '/demo/as24-demo.json';
}

/** Testdaten aus der Datei lesen. */
function bit_demo_listings() {
	$json = json_decode( (string) file_get_contents( bit_demo_file() ), true );
	return bit_as24_extract_items( $json );
}

/** Demo-Fahrzeuge anlegen. */
function bit_demo_load() {
	$listings = bit_demo_listings();
	$report   = bit_as24_import(
		$listings,
		array(
			'demo'         => true,
			'mark_missing' => false,
		)
	);
	// Reihenfolge wie in der Datei: das erste Fahrzeug ist das neueste.
	// Die Markierung auf dem Foto («Neu») setzt Sabit von Hand – hier fuer zwei Beispiele.
	$now = time();
	foreach ( $listings as $i => $l ) {
		$pid = bit_as24_find_post( $l['id'] );
		if ( $pid ) {
			$date = wp_date( 'Y-m-d H:i:s', $now - $i * 3600 );
			wp_update_post( array( 'ID' => $pid, 'post_date' => $date, 'post_date_gmt' => get_gmt_from_date( $date ) ) );
		}
		if ( ! empty( $l['_bitFlag'] ) ) {
			if ( $pid ) {
				update_post_meta( $pid, '_bit_flag', sanitize_text_field( $l['_bitFlag'] ) );
			}
		}
	}
	return $report;
}

/** Alle Demo-Fahrzeuge und ihre Bilder endgueltig loeschen. */
function bit_demo_remove() {
	$n = 0;
	foreach ( array( 'fahrzeug', 'attachment' ) as $type ) {
		$ids = get_posts(
			array(
				'post_type'      => $type,
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'meta_key'       => '_bit_demo', // phpcs:ignore WordPress.DB.SlowDBQuery
				'meta_value'     => '1', // phpcs:ignore WordPress.DB.SlowDBQuery
			)
		);
		foreach ( $ids as $id ) {
			'attachment' === $type ? wp_delete_attachment( $id, true ) : wp_delete_post( $id, true );
			if ( 'fahrzeug' === $type ) {
				++$n;
			}
		}
	}
	// Leere Marken aufraeumen.
	foreach ( get_terms( array( 'taxonomy' => 'marke', 'hide_empty' => false ) ) as $term ) {
		if ( 0 === (int) $term->count ) {
			wp_delete_term( $term->term_id, 'marke' );
		}
	}
	return $n;
}

add_action(
	'admin_menu',
	function () {
		add_management_page( 'BIT Demo-Daten', 'BIT Demo-Daten', 'manage_options', 'bit-demo', 'bit_demo_page' );
	}
);

function bit_demo_page() {
	$count = count(
		get_posts(
			array(
				'post_type'      => 'fahrzeug',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'meta_key'       => '_bit_demo', // phpcs:ignore WordPress.DB.SlowDBQuery
				'meta_value'     => '1', // phpcs:ignore WordPress.DB.SlowDBQuery
			)
		)
	);
	?>
	<div class="wrap">
		<h1>BIT Demo-Daten</h1>
		<p>Erfundene Beispielfahrzeuge zum Testen – sie laufen über denselben AutoScout24-Import wie später die echten Inserate. Auf der Webseite sind sie mit «Demo · Testdaten» beschriftet.</p>
		<p><strong><?php echo (int) $count; ?></strong> Demo-Fahrzeuge sind im Moment angelegt.</p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline-block;margin-right:8px">
			<input type="hidden" name="action" value="bit_demo">
			<input type="hidden" name="do" value="load">
			<?php wp_nonce_field( 'bit_demo' ); ?>
			<?php submit_button( 'Demo-Fahrzeuge laden', 'secondary', 'submit', false ); ?>
		</form>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline-block" onsubmit="return confirm('Alle Demo-Fahrzeuge und ihre Bilder löschen?')">
			<input type="hidden" name="action" value="bit_demo">
			<input type="hidden" name="do" value="remove">
			<?php wp_nonce_field( 'bit_demo' ); ?>
			<?php submit_button( 'Alle Demo-Fahrzeuge löschen', 'delete', 'submit', false ); ?>
		</form>
	</div>
	<?php
}

add_action(
	'admin_post_bit_demo',
	function () {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Keine Berechtigung.' );
		}
		check_admin_referer( 'bit_demo' );
		if ( 'remove' === ( $_POST['do'] ?? '' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			bit_demo_remove();
		} else {
			bit_demo_load();
		}
		wp_safe_redirect( admin_url( 'tools.php?page=bit-demo' ) );
		exit;
	}
);
