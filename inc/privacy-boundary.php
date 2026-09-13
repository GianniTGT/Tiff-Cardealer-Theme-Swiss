<?php
/**
 * The privacy boundary, WordPress side.
 *
 * DAS Manager keeps purchase price, parts, labour, profit and margin on the
 * office machine and strips them before syncing. That is the first wall, and
 * it lives in code we control. This file is the second wall, on the receiving
 * side: even if the sync one day sends a cost field by mistake, WordPress
 * refuses to store it.
 *
 * Two rules:
 *   1. Only the 16 public vehicle fields are exposed to the REST API.
 *   2. Any meta key that looks financial is blocked from being written to a
 *      car at all, by any route - REST, admin, plugin or direct call.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Fields that may never be stored on a vehicle. */
function das_forbidden_meta_patterns() {
	return array(
		'purchase', 'parts', 'labor', 'labour', 'profit', 'margin',
		'sold_price', 'cost', 'acquisition', 'markup', 'net_', 'gross_',
		'invoice', 'auction', 'reconditioning', 'floorplan',
	);
}

/** True when a meta key looks like financial data. */
function das_meta_is_forbidden( $key ) {
	$key = strtolower( (string) $key );
	if ( in_array( $key, array_keys( das_car_meta_keys() ), true ) ) { return false; }
	foreach ( das_forbidden_meta_patterns() as $needle ) {
		if ( false !== strpos( $key, $needle ) ) { return true; }
	}
	return false;
}

/* Wall 2a - block the write, whatever calls it. */
foreach ( array( 'add_post_metadata', 'update_post_metadata' ) as $hook ) {
	add_filter( $hook, function ( $check, $object_id, $meta_key ) {
		if ( 'car' !== get_post_type( $object_id ) ) { return $check; }
		if ( ! das_meta_is_forbidden( $meta_key ) ) { return $check; }
		do_action( 'das_privacy_violation', $object_id, $meta_key );
		return false;   // short-circuits the write, nothing is stored
	}, 10, 3 );
}

/* Wall 2b - never let such a key leak out through REST, even if one exists. */
add_filter( 'rest_prepare_car', function ( $response ) {
	$data = $response->get_data();
	if ( ! empty( $data['meta'] ) && is_array( $data['meta'] ) ) {
		foreach ( array_keys( $data['meta'] ) as $k ) {
			if ( das_meta_is_forbidden( $k ) ) { unset( $data['meta'][ $k ] ); }
		}
		$response->set_data( $data );
	}
	return $response;
} );

/* Record any attempt, so a broken sync is visible instead of silent. */
add_action( 'das_privacy_violation', function ( $post_id, $key ) {
	$log = get_option( 'das_privacy_log', array() );
	if ( ! is_array( $log ) ) { $log = array(); }
	array_unshift( $log, array(
		'time' => current_time( 'mysql' ),
		'post' => (int) $post_id,
		'key'  => (string) $key,
	) );
	update_option( 'das_privacy_log', array_slice( $log, 0, 50 ), false );
}, 10, 2 );

/* Wall 1 on this side - only the public fields reach the REST API. */
add_action( 'init', function () {
	foreach ( das_car_meta_keys() as $key => $type ) {
		register_post_meta( 'car', $key, array(
			'type'              => 'string',
			'single'            => true,
			'show_in_rest'      => true,
			'sanitize_callback' => 'sanitize_text_field',
			'auth_callback'     => function () { return current_user_can( 'edit_posts' ); },
		) );
	}
}, 20 );
