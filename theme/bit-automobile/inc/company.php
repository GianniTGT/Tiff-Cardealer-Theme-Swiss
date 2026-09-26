<?php
/**
 * Firmendaten an einer Stelle.
 *
 * Standardwerte aus dem Handelsregister-Auszug und der Visitenkarte.
 * Aenderbar im Customizer unter «BIT Automobile – Firmendaten», ohne Code.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Standardwerte. Die MWST-Nr. bleibt leer, bis sie bestaetigt ist. */
function bit_info_defaults() {
	return array(
		'brand'      => 'BIT Automobile',
		'legal'      => 'ImmoBit AG',
		'legal_form' => 'Aktiengesellschaft',
		'uid'        => 'CHE-345.577.846',
		'vat'        => '',
		'seat'       => 'Köniz',
		'street'     => 'Herzwilstrasse 262',
		'zip'        => '3173',
		'city'       => 'Oberwangen',
		'city_long'  => 'Oberwangen b. Bern',
		'phone1'     => '031 552 00 02',
		'phone2'     => '078 868 87 97',
		'email'      => 'info@bit-automobile.ch',
		'hours_week' => 'Mo–Fr 08:00–18:00',
		'hours_sat'  => 'Sa 09:00–14:00',
		'signatory'  => 'Sabit Kadriu',
		'hr_date'    => '05.12.2022',
		'statutes'   => '01.12.2022',
		'capital'    => 'CHF 100’000, voll liberiert',
		'shares'     => '1’000 Namenaktien à CHF 100, vinkuliert',
		'founded'    => '2022',
		'maps_url'   => 'https://www.google.com/maps/search/?api=1&query=Herzwilstrasse+262+3173+Oberwangen',
	);
}

/** Ein Firmenwert, Customizer vor Standard. */
function bit_info( $key ) {
	$defaults = bit_info_defaults();
	$value    = get_theme_mod( 'bit_' . $key, null );
	if ( null === $value || '' === $value ) {
		$value = $defaults[ $key ] ?? '';
	}
	return apply_filters( 'bit_info', $value, $key );
}

/** Telefonnummer im Format fuer tel:-Links, z.B. 031 552 00 02 → +41315520002. */
function bit_tel( $phone ) {
	$digits = preg_replace( '/\D+/', '', (string) $phone );
	if ( str_starts_with( $digits, '0041' ) ) {
		$digits = substr( $digits, 4 );
	} elseif ( str_starts_with( $digits, '41' ) && strlen( $digits ) === 11 ) {
		$digits = substr( $digits, 2 );
	}
	return '+41' . ltrim( $digits, '0' );
}

/** Customizer: Firmendaten aenderbar ohne Code. */
add_action(
	'customize_register',
	function ( WP_Customize_Manager $wp_customize ) {
		$wp_customize->add_section(
			'bit_company',
			array(
				'title'    => 'BIT Automobile – Firmendaten',
				'priority' => 30,
			)
		);
		$labels = array(
			'phone1'       => 'Telefon Festnetz',
			'phone2'       => 'Telefon Mobil',
			'email'        => 'E-Mail',
			'street'       => 'Strasse',
			'zip'          => 'PLZ',
			'city'         => 'Ort',
			'city_long'    => 'Ort (lang, Fusszeile)',
			'hours_week'   => 'Öffnungszeiten Mo–Fr',
			'hours_sat'    => 'Öffnungszeiten Sa',
			'legal'        => 'Firma (Handelsregister)',
			'uid'          => 'UID',
			'vat'          => 'MWST-Nr. (leer = nicht anzeigen)',
			'signatory'    => 'Zeichnungsberechtigt',
		);
		$defaults = bit_info_defaults();
		foreach ( $labels as $key => $label ) {
			$wp_customize->add_setting(
				'bit_' . $key,
				array(
					'default'           => $defaults[ $key ],
					'sanitize_callback' => 'email' === $key ? 'sanitize_email' : 'sanitize_text_field',
				)
			);
			$wp_customize->add_control(
				'bit_' . $key,
				array(
					'label'   => $label,
					'section' => 'bit_company',
					'type'    => 'email' === $key ? 'email' : 'text',
				)
			);
		}
	}
);

/**
 * Kurzcodes fuer Rechtstexte, damit Telefon & Co. nur an einer Stelle stehen:
 * [bit key="uid"] → CHE-345.577.846, [bit key="vat" before=", MWST-Nr. "] → nur wenn gesetzt, [bit_tel key="phone1"] → Link, [bit_mail] → Link.
 */
add_shortcode(
	'bit',
	function ( $atts ) {
		$atts  = shortcode_atts( array( 'key' => 'brand', 'before' => '', 'after' => '' ), $atts );
		$value = bit_info( sanitize_key( $atts['key'] ) );
		// Leere Werte (z.B. MWST-Nr. noch nicht bestaetigt) samt Vor-/Nachtext weglassen.
		return '' === (string) $value ? '' : esc_html( $atts['before'] . $value . $atts['after'] );
	}
);
add_shortcode(
	'bit_tel',
	function ( $atts ) {
		$atts  = shortcode_atts( array( 'key' => 'phone1' ), $atts );
		$phone = bit_info( sanitize_key( $atts['key'] ) );
		return sprintf( '<a href="tel:%s">%s</a>', esc_attr( bit_tel( $phone ) ), esc_html( $phone ) );
	}
);
add_shortcode(
	'bit_mail',
	function () {
		$mail = bit_info( 'email' );
		return sprintf( '<a href="mailto:%1$s">%1$s</a>', esc_html( $mail ) );
	}
);
