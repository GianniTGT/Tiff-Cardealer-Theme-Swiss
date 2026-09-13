<?php
/**
 * das/v1/vehicle-photos - where one vehicle's photographs actually live.
 *
 * Instagram will not take bytes. POST /{ig-user-id}/media accepts image_url - a
 * publicly reachable HTTPS address - and nothing else for a photograph, where the
 * Facebook route is handed the file itself. So a vehicle reaches Instagram only
 * after it is on the website, and the desktop app has to be told the addresses.
 *
 * It knows only wp_media_id, and /wp/v2/media/{id} answers with the PLAIN
 * source_url. What belongs on a public advert is the watermarked copy this theme
 * serves - das_watermark_url(), built at das_hero, 1200x800 and written as JPEG.
 * Working that address out from the app's side is exactly what this route exists
 * to prevent: guessing somebody else's URL has already cost this project three
 * attempts at one valuation address.
 *
 * Keyed on the VIN, which is the one thing both halves agree about (8). A post id
 * is accepted only when no VIN is given, and never as a fallback for a VIN that
 * matched nothing - an unknown VIN means the car is not here, not try the number.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'rest_api_init', function () {
	register_rest_route( 'das/v1', '/vehicle-photos', array(
		'methods'  => WP_REST_Server::READABLE,
		'callback' => 'das_rest_vehicle_photos',
		/**
		 * The same das-sync application password the vehicle push and das/v1/leads
		 * use. The image files themselves are public - they are meant to be - but
		 * which vehicle owns which picture, and which vehicles are still drafts, are
		 * not things to hand to anybody who asks.
		 */
		'permission_callback' => function () {
			return current_user_can( 'edit_posts' );
		},
		'args' => array(
			'vin'   => array( 'type' => 'string',  'required' => false ),
			'wp_id' => array( 'type' => 'integer', 'required' => false ),
		),
	) );
} );

function das_rest_vehicle_photos( WP_REST_Request $request ) {
	$vin   = strtoupper( trim( (string) $request->get_param( 'vin' ) ) );
	$wp_id = (int) $request->get_param( 'wp_id' );

	if ( '' === $vin && ! $wp_id ) {
		return new WP_Error( 'das_no_vehicle', 'Name a vin or a wp_id.', array( 'status' => 400 ) );
	}

	if ( '' !== $vin ) {
		$found = get_posts( array(
			'post_type'      => 'car',
			'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'future' ),
			'posts_per_page' => 5,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'meta_query'     => array(
				array( 'key' => 'car_vin', 'value' => $vin, 'compare' => '=' ),
			),
		) );

		// Two vehicles under one VIN is a data error, and choosing one of them puts one
		// car's photographs on another car's advert. Refused, never resolved by taking
		// the first - the rule the app's folder import already applies to a name it
		// cannot pin to exactly one vehicle.
		if ( count( $found ) > 1 ) {
			return new WP_Error( 'das_vin_ambiguous', 'More than one vehicle carries that VIN.', array( 'status' => 409 ) );
		}
		if ( ! $found ) {
			return new WP_Error( 'das_vehicle_not_found', 'No vehicle here carries that VIN.', array( 'status' => 404 ) );
		}
		$wp_id = (int) $found[0];
	}

	$post = get_post( $wp_id );
	if ( ! $post || 'car' !== $post->post_type ) {
		return new WP_Error( 'das_vehicle_not_found', 'No vehicle with that id.', array( 'status' => 404 ) );
	}

	$photos = array();
	foreach ( (array) das_car_photos( $post->ID ) as $photo ) {
		$url = isset( $photo['full'] ) ? (string) $photo['full'] : '';
		if ( '' !== $url ) {
			$photos[] = das_rest_photo_shape( $url );
		}
	}

	return rest_ensure_response( array(
		'vin'    => trim( (string) get_post_meta( $post->ID, 'car_vin', true ) ),
		'wp_id'  => (int) $post->ID,
		'status' => get_post_status( $post ),
		'link'   => (string) get_permalink( $post ),
		'photos' => $photos,
	) );
}

/**
 * One photograph, described rather than merely named.
 *
 * The far end wants JPEG at a size it will accept, and the app must not have to
 * infer either from the end of a URL: das_watermark_url() falls back to the plain
 * attachment whenever the copy cannot be built, and that original may well be the
 * webp Instagram refuses. Measured here, where the file is, so a picture that
 * cannot be published is named as such rather than failing at Meta.
 */
function das_rest_photo_shape( $url ) {
	$shape = array(
		'url'         => $url,
		'mime'        => null,
		'width'       => null,
		'height'      => null,
		'bytes'       => null,
		'watermarked' => false !== strpos( $url, '/das-wm/' ),
	);

	$up   = wp_upload_dir();
	$base = trailingslashit( $up['baseurl'] );
	if ( 0 !== strpos( $url, $base ) ) { return $shape; }

	$rel = rawurldecode( substr( $url, strlen( $base ) ) );
	if ( false !== strpos( $rel, '..' ) ) { return $shape; }

	$path = trailingslashit( $up['basedir'] ) . $rel;
	if ( ! file_exists( $path ) ) { return $shape; }

	$shape['bytes'] = (int) filesize( $path );
	$info = @getimagesize( $path );
	if ( $info ) {
		$shape['width']  = (int) $info[0];
		$shape['height'] = (int) $info[1];
		$shape['mime']   = isset( $info['mime'] ) ? (string) $info['mime'] : null;
	}
	return $shape;
}