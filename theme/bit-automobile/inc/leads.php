<?php
/**
 * Anfragen aus dem Kontaktformular.
 *
 * Jede Anfrage wird im Admin unter «Anfragen» gespeichert und per E-Mail
 * an die Firmenadresse geschickt. Schutz: Nonce, Honigtopf-Feld,
 * Mindestzeit, Begrenzung pro Stunde. Nach 12 Monaten automatisch geloescht
 * (so steht es im Datenschutz).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'init',
	function () {
		register_post_type(
			'bit_anfrage',
			array(
				'labels'          => array(
					'name'          => 'Anfragen',
					'singular_name' => 'Anfrage',
					'all_items'     => 'Alle Anfragen',
					'edit_item'     => 'Anfrage',
					'not_found'     => 'Noch keine Anfragen',
					'menu_name'     => 'Anfragen',
				),
				'public'          => false,
				'show_ui'         => true,
				'show_in_menu'    => true,
				'menu_icon'       => 'dashicons-email-alt',
				'menu_position'   => 5,
				'supports'        => array( 'title', 'editor' ),
				'capability_type' => 'post',
				'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
				'map_meta_cap'    => true,
			)
		);
	}
);

add_action( 'admin_post_nopriv_bit_lead', 'bit_handle_lead' );
add_action( 'admin_post_bit_lead', 'bit_handle_lead' );

/** Formular verarbeiten. */
function bit_handle_lead() {
	$back = bit_page_url( 'kontakt' );
	$go   = function ( $state ) use ( $back ) {
		wp_safe_redirect( add_query_arg( 'gesendet', $state, $back ) . '#formular' );
		exit;
	};

	// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	if ( ! isset( $_POST['bit_lead_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['bit_lead_nonce'] ), 'bit_lead' ) ) {
		$go( 'fehler' );
	}
	// Honigtopf: Menschen sehen das Feld nicht.
	if ( ! empty( $_POST['firma'] ) ) {
		$go( 'ok' );
	}
	// Schneller als 3 Sekunden ausgefuellt = Maschine.
	$t = isset( $_POST['t'] ) ? absint( $_POST['t'] ) : 0;
	if ( $t && time() - $t < 3 ) {
		$go( 'ok' );
	}

	$name = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
	$tel  = sanitize_text_field( wp_unslash( $_POST['tel'] ?? '' ) );
	$mail = sanitize_email( wp_unslash( $_POST['mail'] ?? '' ) );
	$msg  = sanitize_textarea_field( wp_unslash( $_POST['msg'] ?? '' ) );
	$ok   = ! empty( $_POST['ok'] );
	$car  = absint( $_POST['fz'] ?? 0 );
	// phpcs:enable

	if ( '' === $name || ( '' === $tel && '' === $mail ) || ! $ok ) {
		$go( 'fehlt' );
	}

	// Hoechstens 5 Anfragen pro Stunde von derselben Adresse.
	$ip_key = 'bit_lead_' . md5( ( $_SERVER['REMOTE_ADDR'] ?? '' ) . wp_salt() ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	$hits   = (int) get_transient( $ip_key );
	if ( $hits >= 5 ) {
		$go( 'fehler' );
	}
	set_transient( $ip_key, $hits + 1, HOUR_IN_SECONDS );

	$car_title = ( $car && 'fahrzeug' === get_post_type( $car ) ) ? get_the_title( $car ) : '';
	$title     = $name . ( $car_title ? ' – ' . $car_title : '' );

	$id = wp_insert_post(
		array(
			'post_type'    => 'bit_anfrage',
			'post_status'  => 'private',
			'post_title'   => $title,
			'post_content' => $msg,
		),
		true
	);
	if ( is_wp_error( $id ) ) {
		$go( 'fehler' );
	}
	update_post_meta( $id, '_bit_name', $name );
	update_post_meta( $id, '_bit_tel', $tel );
	update_post_meta( $id, '_bit_mail', $mail );
	update_post_meta( $id, '_bit_car', $car_title ? $car : 0 );

	$body  = "Neue Anfrage über bit-automobile.ch\n\n";
	$body .= "Name:     {$name}\n";
	$body .= "Telefon:  {$tel}\n";
	$body .= "E-Mail:   {$mail}\n";
	if ( $car_title ) {
		$body .= "Fahrzeug: {$car_title} – " . get_permalink( $car ) . "\n";
	}
	$body .= "\n{$msg}\n\n— Gespeichert unter Anfragen: " . admin_url( 'post.php?post=' . $id . '&action=edit' ) . "\n";

	$headers = array( 'Content-Type: text/plain; charset=UTF-8' );
	if ( is_email( $mail ) ) {
		$headers[] = 'Reply-To: ' . $name . ' <' . $mail . '>';
	}
	$to = apply_filters( 'bit_lead_recipient', bit_info( 'email' ) );
	wp_mail( $to, 'Anfrage: ' . $title, $body, $headers );

	do_action( 'bit_lead_saved', $id );
	$go( 'ok' );
}

/* ---------- Anzeige im Admin ---------- */
add_filter(
	'manage_bit_anfrage_posts_columns',
	function () {
		return array(
			'cb'       => '<input type="checkbox">',
			'title'    => 'Anfrage',
			'bit_tel'  => 'Telefon',
			'bit_mail' => 'E-Mail',
			'date'     => 'Eingang',
		);
	}
);
add_action(
	'manage_bit_anfrage_posts_custom_column',
	function ( $col, $id ) {
		if ( 'bit_tel' === $col ) {
			$tel = get_post_meta( $id, '_bit_tel', true );
			echo $tel ? '<a href="tel:' . esc_attr( bit_tel( $tel ) ) . '">' . esc_html( $tel ) . '</a>' : '–';
		}
		if ( 'bit_mail' === $col ) {
			$mail = get_post_meta( $id, '_bit_mail', true );
			echo $mail ? '<a href="mailto:' . esc_attr( $mail ) . '">' . esc_html( $mail ) . '</a>' : '–';
		}
	},
	10,
	2
);
add_action(
	'add_meta_boxes_bit_anfrage',
	function () {
		add_meta_box(
			'bit_lead_contact',
			'Kontakt',
			function ( $post ) {
				$car = (int) get_post_meta( $post->ID, '_bit_car', true );
				echo '<p><strong>' . esc_html( get_post_meta( $post->ID, '_bit_name', true ) ) . '</strong></p>';
				$tel  = get_post_meta( $post->ID, '_bit_tel', true );
				$mail = get_post_meta( $post->ID, '_bit_mail', true );
				if ( $tel ) {
					echo '<p><a class="button button-primary" href="tel:' . esc_attr( bit_tel( $tel ) ) . '">Anrufen: ' . esc_html( $tel ) . '</a></p>';
				}
				if ( $mail ) {
					echo '<p><a class="button" href="mailto:' . esc_attr( $mail ) . '">E-Mail an ' . esc_html( $mail ) . '</a></p>';
				}
				if ( $car ) {
					echo '<p>Fahrzeug: <a href="' . esc_url( get_permalink( $car ) ) . '" target="_blank">' . esc_html( get_the_title( $car ) ) . '</a></p>';
				}
			},
			'bit_anfrage',
			'side',
			'high'
		);
	}
);

/* ---------- Nach 12 Monaten loeschen ---------- */
add_action(
	'init',
	function () {
		if ( ! wp_next_scheduled( 'bit_purge_leads' ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'bit_purge_leads' );
		}
	}
);
add_action( 'bit_purge_leads', 'bit_purge_leads' );

/** Anfragen aelter als 12 Monate endgueltig loeschen. Gibt die Anzahl zurueck. */
function bit_purge_leads() {
	$old = get_posts(
		array(
			'post_type'      => 'bit_anfrage',
			'post_status'    => 'any',
			'posts_per_page' => 200,
			'fields'         => 'ids',
			'date_query'     => array( array( 'before' => '12 months ago' ) ),
		)
	);
	foreach ( $old as $id ) {
		wp_delete_post( $id, true );
	}
	return count( $old );
}
