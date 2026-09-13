<?php
/**
 * Staging mail guard.
 *
 * This install is a test copy. Without a guard it can email real customers -
 * lead notifications, password resets, WordPress admin mail. When the option
 * das_test_mail_to holds an address, every outgoing message is redirected
 * there instead, with the intended recipient preserved in the body.
 *
 * With no option set the filter does nothing at all.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_filter( 'wp_mail', function ( $args ) {
	$to = trim( (string) get_option( 'das_test_mail_to', '' ) );
	if ( '' === $to || ! is_email( $to ) ) { return $args; }

	$intended = $args['to'];
	if ( is_array( $intended ) ) { $intended = implode( ', ', $intended ); }

	$note  = "----- STAGING COPY -----\n";
	$note .= "This message was NOT delivered to its real recipient.\n";
	$note .= 'Intended for: ' . $intended . "\n";
	$note .= 'Sent from:    ' . home_url() . "\n";
	$note .= "------------------------\n\n";

	$args['to']      = $to;
	$args['subject'] = '[TEST] ' . ( $args['subject'] ?? '' );
	$args['message'] = $note . ( $args['message'] ?? '' );

	if ( ! empty( $args['headers'] ) ) {
		$headers = is_array( $args['headers'] ) ? $args['headers'] : explode( "\n", str_replace( "\r\n", "\n", $args['headers'] ) );
		$kept = array();
		foreach ( $headers as $h ) {
			if ( preg_match( '/^[ \t]*(cc|bcc)[ \t]*:/i', $h ) ) { continue; }
			$kept[] = $h;
		}
		$args['headers'] = $kept;
	}

	return $args;
} );

/** Visible reminder in wp-admin while the guard is armed. */
add_action( 'admin_notices', function () {
	$to = trim( (string) get_option( 'das_test_mail_to', '' ) );
	if ( '' === $to ) { return; }
	printf(
		'<div class="notice notice-warning"><p><strong>Staging mail guard is on.</strong> All outgoing email goes to %s and never reaches real recipients.</p></div>',
		esc_html( $to )
	);
} );

/**
 * From address, owned by the theme rather than by a plugin.
 *
 * On a Plesk host with a local mailbox, wp_mail() delivers through the
 * server's own MTA and no SMTP plugin is needed - but WordPress then
 * defaults to wordpress@domain, which fails SPF and looks wrong. These two
 * filters fix that. Inert until das_mail_from holds an address.
 */
add_filter( 'wp_mail_from', function ( $email ) {
	$from = trim( (string) get_option( 'das_mail_from', '' ) );
	return is_email( $from ) ? $from : $email;
}, 5 );

add_filter( 'wp_mail_from_name', function ( $name ) {
	$n = trim( (string) get_option( 'das_mail_from_name', '' ) );
	return '' !== $n ? $n : $name;
}, 5 );
