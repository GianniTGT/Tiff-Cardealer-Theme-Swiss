<?php
/**
 * Post types owned by the theme.
 *
 * These used to come from the tmm_theme_features plugin. That plugin is gone,
 * so the theme registers them itself - the 87 vehicles and their 1,867 car_*
 * meta rows were never touched, only the registration disappeared.
 *
 * Slugs must stay exactly as they were ('car', 'carproducer') so existing
 * permalinks and the /car/ archive keep working.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'init', function () {

	register_post_type( 'car', array(
		'labels' => array(
			'name'               => __( 'Vehicles', 'das-v4' ),
			'singular_name'      => __( 'Vehicle', 'das-v4' ),
			'menu_name'          => das_menu_label( __( 'Vehicles', 'das-v4' ) ),
			'add_new'            => __( 'Add vehicle', 'das-v4' ),
			'add_new_item'       => __( 'Add new vehicle', 'das-v4' ),
			'edit_item'          => __( 'Edit vehicle', 'das-v4' ),
			'new_item'           => __( 'New vehicle', 'das-v4' ),
			'view_item'          => __( 'View vehicle', 'das-v4' ),
			'search_items'       => __( 'Search vehicles', 'das-v4' ),
			'not_found'          => __( 'No vehicles found', 'das-v4' ),
			'not_found_in_trash' => __( 'No vehicles in the trash', 'das-v4' ),
			'all_items'          => __( 'All vehicles', 'das-v4' ),
			'featured_image'     => __( 'Main photo', 'das-v4' ),
			'set_featured_image' => __( 'Set main photo', 'das-v4' ),
		),
		'public'             => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'show_in_nav_menus'  => true,
		'show_in_rest'       => true,   // needed later for the DAS Manager sync
		'menu_position'      => 5,
		'menu_icon'          => 'dashicons-car',
		'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
		'has_archive'        => true,
		'rewrite'            => array( 'slug' => 'car', 'with_front' => true ),
		'capability_type'    => 'post',
		'map_meta_cap'       => true,
		'hierarchical'       => false,
	) );

	register_taxonomy( 'carproducer', array( 'car' ), array(
		'labels' => array(
			'name'          => __( 'Makes & models', 'das-v4' ),
			'singular_name' => __( 'Make', 'das-v4' ),
			'menu_name'     => __( 'Makes', 'das-v4' ),
			'search_items'  => __( 'Search makes', 'das-v4' ),
			'all_items'     => __( 'All makes', 'das-v4' ),
			'edit_item'     => __( 'Edit make', 'das-v4' ),
			'add_new_item'  => __( 'Add make', 'das-v4' ),
		),
		'public'            => true,
		'hierarchical'      => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'carproducer', 'with_front' => true ),
	) );

} );


/**
 * Staff records.
 *
 * Three of these already exist in the database (Tif, Oz, AJ) with phone numbers
 * on them, created in 2021 under the old plugin. Registering the type again
 * makes them visible and editable; the slug must stay 'staff-page'.
 *
 * Not public: there is no single-staff template, the roster is rendered by
 * das_team_section() / [das_team].
 */
add_action( 'init', function () {
	register_post_type( 'staff-page', array(
		'labels' => array(
			'name'          => __( 'Staff', 'das-v4' ),
			'singular_name' => __( 'Staff member', 'das-v4' ),
			'menu_name'     => das_menu_label( __( 'Staff', 'das-v4' ) ),
			'add_new_item'  => __( 'Add staff member', 'das-v4' ),
			'edit_item'     => __( 'Edit staff member', 'das-v4' ),
			'all_items'     => __( 'All staff', 'das-v4' ),
		),
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_rest'        => false,
		'menu_position'       => 6,
		'menu_icon'           => 'dashicons-groups',
		'supports'            => array( 'title', 'editor', 'thumbnail' ),
		'has_archive'         => false,
		'rewrite'             => false,
		'exclude_from_search' => true,
		'capability_type'     => 'post',
		'map_meta_cap'        => true,
	) );
} );

add_action( 'add_meta_boxes', function () {
	add_meta_box( 'das_staff', __( 'Staff details', 'das-v4' ), 'das_staff_box', 'staff-page', 'normal', 'high' );
} );

function das_staff_box( $post ) {
	wp_nonce_field( 'das_save_staff', 'das_staff_nonce' );
	$g = function ( $k ) use ( $post ) { return esc_attr( get_post_meta( $post->ID, $k, true ) ); };
	$fields = array(
		'staff_role'   => 'Role (e.g. Owner & Founder)',
		'office_phone' => 'Office phone',
		'mobile_phone' => 'Mobile phone',
		'staff_email'  => 'Email',
	);
	echo '<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:4px 24px">';
	foreach ( $fields as $k => $label ) {
		printf(
			'<p><label for="%1$s"><strong>%2$s</strong></label><br><input type="text" id="%1$s" name="%1$s" value="%3$s" style="width:100%%;max-width:320px"></p>',
			esc_attr( $k ), esc_html( $label ), $g( $k )
		);
	}
	echo '</div>';
	echo '<p><label for="staff_show"><input type="checkbox" id="staff_show" name="staff_show" value="1" ' . checked( get_post_meta( $post->ID, 'staff_show', true ), '1', false ) . '> <strong>Show on the website</strong></label></p>';
	echo '<p class="description">Photo = Featured image. The short bio goes in the main editor above.</p>';
}

add_action( 'save_post_staff-page', function ( $post_id ) {
	if ( ! isset( $_POST['das_staff_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['das_staff_nonce'] ), 'das_save_staff' ) ) { return; }
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }
	foreach ( array( 'staff_role', 'office_phone', 'mobile_phone', 'staff_email' ) as $k ) {
		if ( isset( $_POST[ $k ] ) ) {
			update_post_meta( $post_id, $k, sanitize_text_field( wp_unslash( $_POST[ $k ] ) ) );
		}
	}
	update_post_meta( $post_id, 'staff_show', isset( $_POST['staff_show'] ) ? '1' : '0' );
} );
/* Staff list columns, matching what the old plugin showed. */
add_filter( 'manage_staff-page_posts_columns', function ( $cols ) {
	return array(
		'cb'         => $cols['cb'] ?? '',
		'das_photo'  => 'Photo',
		'title'      => 'Name',
		'das_role'   => 'Role',
		'das_email'  => 'Email',
		'das_office' => 'Office phone',
		'das_mobile' => 'Mobile phone',
		'das_show'   => 'On website',
		'date'       => $cols['date'] ?? 'Date',
	);
} );

add_action( 'manage_staff-page_posts_custom_column', function ( $col, $post_id ) {
	$m = function ( $k ) use ( $post_id ) { return (string) get_post_meta( $post_id, $k, true ); };
	switch ( $col ) {
		case 'das_photo':
			$t = get_the_post_thumbnail( $post_id, array( 72, 72 ) );
			echo $t ? $t : '<div style="width:72px;height:72px;border-radius:8px;background:#f0f0f1;display:grid;place-items:center;color:#8c8f94;font-size:11px">No photo</div>';
			break;
		case 'das_role':
			echo $m('staff_role') ? esc_html( $m('staff_role') ) : '<span style="color:#b32d2e">not set</span>';
			break;
		case 'das_email':
			echo $m('staff_email') ? '<a href="mailto:' . esc_attr( $m('staff_email') ) . '">' . esc_html( $m('staff_email') ) . '</a>' : '&mdash;';
			break;
		case 'das_office':
			echo $m('office_phone') ? esc_html( $m('office_phone') ) : '&mdash;';
			break;
		case 'das_mobile':
			echo $m('mobile_phone') ? esc_html( $m('mobile_phone') ) : '&mdash;';
			break;
		case 'das_show':
			echo '1' === $m('staff_show') ? '<span style="color:#008a20;font-weight:600">Yes</span>' : '<span style="color:#8c8f94">Hidden</span>';
			break;
	}
}, 10, 2 );

/**
 * Vehicles and staff use the classic editor.
 *
 * show_in_rest is true because the DAS Manager sync needs the REST API, but
 * that flag also switches the edit screen to the block editor, which hides the
 * vehicle metabox and leaves whoever clicked Add vehicle staring at an empty
 * page. The two settings are independent: turn the block editor off here and
 * keep REST on.
 */
add_filter( 'use_block_editor_for_post_type', function ( $use, $post_type ) {
	if ( in_array( $post_type, array( 'car', 'staff-page' ), true ) ) { return false; }
	return $use;
}, 10, 2 );

/**
 * Self-healing rewrite rules.
 *
 * A one-shot flush guarded by an option is fragile: if it runs before the
 * post type is fully registered it writes no car rules, then marks itself
 * done and never runs again - and every vehicle URL returns 404 while the
 * admin screens look perfectly fine.
 *
 * This checks that the rules actually exist and rebuilds them if not. It is
 * cheap: one option read per request, a flush only when something is wrong.
 */
add_action( 'wp_loaded', function () {
	if ( wp_doing_ajax() || wp_doing_cron() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) { return; }

	$rules = get_option( 'rewrite_rules' );
	if ( ! is_array( $rules ) ) { return; }   // permalinks set to plain, nothing to do

	foreach ( array_keys( $rules ) as $regex ) {
		if ( 0 === strpos( $regex, 'car/' ) ) { return; }   // rules present, all good
	}

	flush_rewrite_rules( false );
} );
