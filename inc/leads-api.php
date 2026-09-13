<?php
/**
 * das/v1/leads - the one thing that flows downward.
 *
 * Vehicles go up to the website, leads come down to the office, and nothing goes both
 * ways (FAZA2 3). This route is the website half of CLAUDE.md 20.2; the desktop app's
 * WordPressClient::fetchLeads() is the only thing that calls it.
 *
 * It is the theme's own route, not core WordPress, so a site whose theme is not active
 * answers `rest_no_route` here while everything else still works - which is exactly what
 * the app reports as NO_LEAD_ROUTE rather than as a broken connection.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'rest_api_init', function () {
	register_rest_route( 'das/v1', '/leads', array(
		'methods'  => WP_REST_Server::READABLE,
		'callback' => 'das_rest_leads',
		/**
		 * Never public. Leads are customers' names and phone numbers, so this needs the
		 * same das-sync application password the vehicle push uses - an Editor, which
		 * `edit_posts` is true for. Returning true here would put the dealership's entire
		 * enquiry list on the open internet.
		 */
		'permission_callback' => function () {
			return current_user_can( 'edit_posts' );
		},
		'args' => array(
			'after'    => array( 'type' => 'string',  'required' => false ),
			'page'     => array( 'type' => 'integer', 'default'  => 1 ),
			'per_page' => array( 'type' => 'integer', 'default'  => 100 ),
		),
	) );
} );

function das_rest_leads( WP_REST_Request $request ) {
	$per_page = (int) $request->get_param( 'per_page' );
	$per_page = max( 1, min( 200, $per_page ?: 100 ) );
	$page     = max( 1, (int) $request->get_param( 'page' ) );
	$after    = trim( (string) $request->get_param( 'after' ) );

	$args = array(
		'post_type'      => 'das_lead',
		'post_status'    => 'publish',
		'posts_per_page' => $per_page,
		'paged'          => $page,
		// Oldest first, because the app keeps a watermark of the newest thing it has seen
		// and walks forward. Newest-first would make page 1 the end of the story.
		'orderby'        => 'date',
		'order'          => 'ASC',
	);

	// Compared in GMT on purpose: the app sends `new Date(...).toISOString()`, which is
	// always UTC, and post_date is whatever timezone this site is set to. Comparing the
	// two directly would drop or repeat leads by the size of the offset - eight hours in
	// Anchorage, which is most of a working day.
	if ( '' !== $after ) {
		$ts = strtotime( $after );
		if ( $ts ) {
			$args['date_query'] = array(
				array(
					'column'    => 'post_date_gmt',
					'after'     => gmdate( 'Y-m-d H:i:s', $ts ),
					'inclusive' => false,
				),
			);
		}
	}

	$query = new WP_Query( $args );
	$leads = array();
	foreach ( $query->posts as $post ) {
		$leads[] = das_rest_lead_shape( $post );
	}

	return rest_ensure_response( array(
		'leads'    => $leads,
		'has_more' => $query->max_num_pages > $page,
		'total'    => (int) $query->found_posts,
	) );
}

/**
 * One lead, in the shape 20.1 names - and nothing else.
 *
 * The same whitelist argument as the outbound payload, pointed the other way: a meta key
 * added to leads later cannot start travelling to the office because nobody remembered
 * to exclude it. `lead_ip` and `lead_ua` are deliberately not here; they are spam
 * forensics for this end, not something the dealership's database needs.
 *
 * **There is no `vehicle_id`, and that is deliberate.** The app resolves that field
 * against its own local vehicle ids, and the only id this side knows is the WordPress
 * post id. Sending it would risk attaching a lead to whichever unrelated local vehicle
 * happened to share the number. The VIN is the key both halves already agree on (8),
 * so the vehicle travels as `vehicle_vin` with the post id beside it for reference.
 */
function das_rest_lead_shape( $post ) {
	$id = (int) $post->ID;
	$g  = function ( $key ) use ( $id ) {
		$value = get_post_meta( $id, $key, true );
		return '' === $value || null === $value ? null : (string) $value;
	};

	$car_id = (int) get_post_meta( $id, 'lead_car_id', true );
	$vin    = $car_id && 'car' === get_post_type( $car_id )
		? trim( (string) get_post_meta( $car_id, 'car_vin', true ) )
		: '';

	$employed = $g( 'lead_employed' );
	$down     = $g( 'lead_down_payment' );

	return array(
		'id'            => $id,
		'name'          => $g( 'lead_name' ),
		'phone'         => $g( 'lead_phone' ),
		'email'         => $g( 'lead_email' ),
		'message'       => $g( 'lead_message' ),
		'type'          => $g( 'lead_type' ) ?: 'inquiry',
		'source'        => $g( 'lead_source' ) ?: 'website',
		'created_at'    => gmdate( 'c', strtotime( $post->post_date_gmt . ' UTC' ) ),
		'vehicle_vin'   => '' !== $vin ? $vin : null,
		'vehicle_wp_id' => $car_id ?: null,
		'down_payment'  => null === $down ? null : (int) $down,
		'income_band'   => $g( 'lead_income_band' ),
		// 1, 0 or null - "did not say" is a real answer and is not the same as "no".
		'employed'      => null === $employed ? null : ( '1' === $employed ? 1 : 0 ),
	);
}
