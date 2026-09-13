<?php
/** One clean vehicle metabox + useful admin columns. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'add_meta_boxes', function () {
	add_meta_box( 'das_vehicle', __( 'Vehicle details', 'das-v4' ), 'das_vehicle_box', 'car', 'normal', 'high' );
} );

/**
 * The vehicle form.
 *
 * ── Why it grew ──────────────────────────────────────────────────────────
 * Putting a vehicle online from wp-admin is a real second route beside the desktop
 * application, so this screen has to hold everything the old ThemeMakers one held.
 * Three things it stored were being **displayed by the theme and could not be edited
 * anywhere** — which is worse than a missing feature, because the site shows a fact
 * nobody can correct:
 *
 *   car_condition   the Condition line in the spec grid
 *   car_price_plus  the note beside the price
 *   advanced        the whole equipment list, on 53 of the 60 vehicles
 *
 * The fourth, `cars_videos`, was on the old form and is read by nothing. It is here with
 * its half on the vehicle page, because a box that saves into a void is not a feature.
 *
 * ── What the application does to all of this: nothing ────────────────────────
 * Tiff Cardealer Manager pushes a **whitelist of the sixteen `car_*` keys** and nothing
 * else, and `inc/privacy-boundary.php` registers only those sixteen with REST. So
 * `car_condition`, `advanced` and `cars_videos` are invisible to the sync in both
 * directions: a push cannot carry them and cannot clear them. `car_price_plus` is on the
 * whitelist but the application has no column for it, so it is never sent either.
 *
 * And `save_post_car` below returns immediately without this box's nonce, which a REST
 * write never has — so the push cannot run this handler at all. A vehicle entered by hand
 * keeps its videos, its condition and its equipment list for good; the sixteen shared
 * fields stay the application's to own, which is §9's rule and does not change here.
 */
function das_vehicle_box( $post ) {
	wp_nonce_field( 'das_save_vehicle', 'das_vehicle_nonce' );
	$g = function ( $k ) use ( $post ) { return esc_attr( get_post_meta( $post->ID, $k, true ) ); };
	$select = function ( $key, $label, $options, $current ) {
		echo '<p><label for="' . esc_attr( $key ) . '"><strong>' . esc_html( $label ) . '</strong></label><br>';
		echo '<select name="' . esc_attr( $key ) . '" id="' . esc_attr( $key ) . '" style="width:100%;max-width:320px">';
		echo '<option value="">— select —</option>';
		foreach ( $options as $v => $l ) {
			echo '<option value="' . esc_attr( $v ) . '"' . selected( $current, $v, false ) . '>' . esc_html( $l ) . '</option>';
		}
		echo '</select></p>';
	};
	?>
	<style>
	.das-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:4px 24px}
	.das-grid p{margin:8px 0}.das-grid input[type=text]{width:100%;max-width:320px}
	.das-flags{background:#f6f7f9;border:1px solid #dcdcde;border-radius:6px;padding:10px 14px;margin-top:14px}
	.das-flags label{margin-right:26px;font-weight:600}
	.das-sec{margin-top:26px;padding-top:18px;border-top:1px solid #e0e0e0}
	.das-sec h3{margin:0 0 4px;font-size:14px}
	.das-sec .description{margin-top:0}
	.das-video-row{display:flex;gap:8px;align-items:center;margin:6px 0;max-width:760px}
	.das-video-row input{flex:1}
	.das-fgroup{margin-top:16px}
	.das-fgroup h4{margin:0 0 8px;font-size:13px;text-transform:uppercase;letter-spacing:.04em;color:#50575e}
	.das-fpicks{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:4px 24px;margin-bottom:6px}
	.das-fpicks p{margin:6px 0}
	.das-fboxes{display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:2px 24px}
	.das-fboxes label{display:block;padding:3px 0}
	</style>
	<div class="das-grid">
		<p><label for="car_vin"><strong>VIN</strong></label><br><input type="text" name="car_vin" id="car_vin" value="<?php echo $g('car_vin'); ?>" maxlength="17" style="text-transform:uppercase"></p>
		<p><label for="car_year"><strong>Year</strong></label><br><input type="text" name="car_year" id="car_year" value="<?php echo $g('car_year'); ?>" maxlength="4"></p>
		<p><label for="car_price"><strong>Price ($)</strong></label><br><input type="text" name="car_price" id="car_price" value="<?php echo $g('car_price'); ?>" inputmode="numeric"> <span class="description">Numbers only, e.g. 17900</span></p>
		<p><label for="car_price_plus"><strong>Price note</strong></label><br><input type="text" name="car_price_plus" id="car_price_plus" value="<?php echo $g('car_price_plus'); ?>" placeholder="e.g. or best offer"> <span class="description">Prints beside the price.</span></p>
		<p><label for="car_mileage"><strong>Mileage (mi)</strong></label><br><input type="text" name="car_mileage" id="car_mileage" value="<?php echo $g('car_mileage'); ?>" inputmode="numeric"></p>
		<?php $select( 'car_condition', 'Condition', das_conditions(), get_post_meta( $post->ID, 'car_condition', true ) ); ?>
		<?php $select( 'car_body', 'Body type', das_body_types(), get_post_meta( $post->ID, 'car_body', true ) ); ?>
		<?php $select( 'car_fuel_type', 'Fuel', das_fuel_types(), get_post_meta( $post->ID, 'car_fuel_type', true ) ); ?>
		<?php $select( 'car_transmission', 'Transmission', das_transmissions(), get_post_meta( $post->ID, 'car_transmission', true ) ); ?>
		<p><label for="car_engine_size"><strong>Engine size (L)</strong></label><br><input type="text" name="car_engine_size" id="car_engine_size" value="<?php echo $g('car_engine_size'); ?>" placeholder="2.5"></p>
		<p><label for="car_engine_additional"><strong>Engine detail</strong></label><br><input type="text" name="car_engine_additional" id="car_engine_additional" value="<?php echo $g('car_engine_additional'); ?>" placeholder="4 Cylinder/Turbo"></p>
		<p><label for="car_exterior_color"><strong>Exterior color</strong></label><br><input type="text" name="car_exterior_color" id="car_exterior_color" value="<?php echo $g('car_exterior_color'); ?>"></p>
		<p><label for="car_interrior_color"><strong>Interior color</strong></label><br><input type="text" name="car_interrior_color" id="car_interrior_color" value="<?php echo $g('car_interrior_color'); ?>"></p>
		<p><label for="car_doors_count"><strong>Doors</strong></label><br><input type="text" name="car_doors_count" id="car_doors_count" value="<?php echo $g('car_doors_count'); ?>" inputmode="numeric"></p>
		<p><label for="car_owner_number"><strong>Previous owners</strong></label><br><input type="text" name="car_owner_number" id="car_owner_number" value="<?php echo $g('car_owner_number'); ?>" inputmode="numeric"></p>
	</div>
	<div class="das-flags">
		<label><input type="checkbox" name="car_is_featured" value="1" <?php checked( get_post_meta( $post->ID, 'car_is_featured', true ), '1' ); ?>> Featured — show in the homepage showcase</label>
		<label><input type="checkbox" name="car_is_sold" value="1" <?php checked( get_post_meta( $post->ID, 'car_is_sold', true ), '1' ); ?>> Sold — hide from the website</label>
	</div>
	<p class="description" style="margin-top:12px">Set the make under "Makes &amp; models" on the right. Main photo = Featured image; extra photos: Add Media → Uploaded to this post.</p>

	<?php das_vehicle_videos_field( $post ); ?>
	<?php das_vehicle_features_field( $post ); ?>
	<?php
}

/**
 * Videos.
 *
 * The old form had this and **not one of the fifty-three vehicles carrying the meta has a
 * URL in it** — every row is an empty array. So this is a capability rather than a
 * restoration, and it only earns its place because the vehicle page renders it now.
 *
 * Stored under the old key `cars_videos`, in the old shape: a flat list of URLs.
 */
function das_vehicle_videos_field( $post ) {
	$videos = das_car_videos( $post->ID );
	if ( ! $videos ) { $videos = array( '' ); }
	?>
	<div class="das-sec">
		<h3>Videos</h3>
		<p class="description">YouTube or Vimeo links. They play under the photographs on the vehicle page.</p>
		<div id="das-videos">
			<?php foreach ( $videos as $url ) : ?>
				<div class="das-video-row">
					<input type="url" name="cars_videos[]" value="<?php echo esc_attr( $url ); ?>"
					       placeholder="https://www.youtube.com/watch?v=… or https://vimeo.com/…">
					<button type="button" class="button das-video-del">Remove</button>
				</div>
			<?php endforeach; ?>
		</div>
		<p><button type="button" class="button" id="das-video-add">+ Add video</button></p>
	</div>
	<script>
	( function () {
		var list = document.getElementById( 'das-videos' );
		var add  = document.getElementById( 'das-video-add' );
		if ( ! list || ! add ) { return; }
		add.addEventListener( 'click', function () {
			var row = list.firstElementChild.cloneNode( true );
			row.querySelector( 'input' ).value = '';
			list.appendChild( row );
			row.querySelector( 'input' ).focus();
		} );
		list.addEventListener( 'click', function ( e ) {
			if ( ! e.target.classList.contains( 'das-video-del' ) ) { return; }
			var row = e.target.closest( '.das-video-row' );
			// Never remove the last row — clear it, so there is always somewhere to type.
			if ( list.children.length > 1 ) { row.remove(); } else { row.querySelector( 'input' ).value = ''; }
		} );
	}() );
	</script>
	<?php
}

/**
 * Equipment.
 *
 * **Drawn entirely from `das_feature_groups()`**, which is the same declaration the
 * vehicle page reads. That is the split this project uses everywhere — `lib/dealer-fields.js`
 * for the dealer profile, `das_filter_options()` for the inventory: add a field there and it
 * appears on this form and on the public page at once, rather than on whichever one somebody
 * remembered.
 *
 * The stored shape is the old plugin's: `advanced[group][field]`, with `1`/`0` for a plain
 * yes, a machine string for the mapped ones (`fullleather`, `frontandrear`) and a digit for
 * the seat count. It is written back in exactly that shape, so 53 vehicles of existing data
 * keep rendering.
 */
function das_vehicle_features_field( $post ) {
	$data = das_car_advanced( $post->ID );
	?>
	<div class="das-sec">
		<h3>Vehicle features</h3>
		<p class="description">What the vehicle page lists under “Features &amp; equipment”.</p>
		<?php foreach ( das_feature_groups() as $group => $conf ) : ?>
			<div class="das-fgroup">
				<h4><?php echo esc_html( $conf['label'] ); ?></h4>
				<?php
				$picks = array();
				$boxes = array();
				foreach ( $conf['fields'] as $field => $spec ) {
					$rule = isset( $spec[1] ) ? $spec[1] : null;
					if ( null === $rule ) { $boxes[ $field ] = $spec; } else { $picks[ $field ] = $spec; }
				}
				$name = function ( $field ) use ( $group ) {
					return 'advanced[' . $group . '][' . $field . ']';
				};
				$value = function ( $field ) use ( $data, $group ) {
					return isset( $data[ $group ][ $field ] ) ? trim( (string) $data[ $group ][ $field ] ) : '';
				};
				?>
				<?php if ( $picks ) : ?>
					<div class="das-fpicks">
						<?php foreach ( $picks as $field => $spec ) : $current = $value( $field ); ?>
							<p>
								<label><strong><?php echo esc_html( $spec[0] ); ?></strong></label><br>
								<?php if ( 'count' === $spec[1] ) : ?>
									<input type="text" inputmode="numeric" style="width:100%;max-width:320px"
									       name="<?php echo esc_attr( $name( $field ) ); ?>"
									       value="<?php echo esc_attr( $current ); ?>">
								<?php else : ?>
									<select name="<?php echo esc_attr( $name( $field ) ); ?>" style="width:100%;max-width:320px">
										<option value="">— none —</option>
										<?php foreach ( $spec[1] as $v => $l ) : ?>
											<option value="<?php echo esc_attr( $v ); ?>"<?php selected( $current, $v ); ?>><?php echo esc_html( $l ); ?></option>
										<?php endforeach; ?>
									</select>
								<?php endif; ?>
							</p>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
				<?php if ( $boxes ) : ?>
					<div class="das-fboxes">
						<?php foreach ( $boxes as $field => $spec ) : ?>
							<label>
								<input type="checkbox" value="1"
								       name="<?php echo esc_attr( $name( $field ) ); ?>"
								       <?php checked( '1', $value( $field ) ); ?>>
								<?php echo esc_html( $spec[0] ); ?>
							</label>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>
	<?php
}

add_action( 'save_post_car', function ( $post_id ) {
	// Without this box's nonce nothing here runs — which is what keeps a REST push from
	// the desktop application out of this handler entirely.
	if ( ! isset( $_POST['das_vehicle_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['das_vehicle_nonce'] ), 'das_save_vehicle' ) ) { return; }
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }

	$numeric = array( 'car_price', 'car_mileage', 'car_year', 'car_doors_count', 'car_owner_number' );
	foreach ( array_keys( das_car_meta_keys() ) as $key ) {
		if ( in_array( $key, array( 'car_is_featured', 'car_is_sold' ), true ) ) { continue; }
		if ( ! isset( $_POST[ $key ] ) ) { continue; }
		$val = sanitize_text_field( wp_unslash( $_POST[ $key ] ) );
		if ( in_array( $key, $numeric, true ) ) { $val = preg_replace( '/\D/', '', $val ); }
		if ( 'car_vin' === $key ) { $val = strtoupper( preg_replace( '/[^A-Za-z0-9]/', '', $val ) ); }
		update_post_meta( $post_id, $key, $val );
	}
	update_post_meta( $post_id, 'car_is_featured', isset( $_POST['car_is_featured'] ) ? '1' : '0' );
	update_post_meta( $post_id, 'car_is_sold', isset( $_POST['car_is_sold'] ) ? '1' : '0' );

	// Condition. Validated against the list that produced the box, so a value the form
	// never offered cannot be stored — the same rule as the financing income band.
	$condition = isset( $_POST['car_condition'] ) ? sanitize_text_field( wp_unslash( $_POST['car_condition'] ) ) : '';
	update_post_meta( $post_id, 'car_condition', isset( das_conditions()[ $condition ] ) ? $condition : '' );

	// Videos. Empty rows drop out, duplicates drop out, and the order is the order typed.
	$videos = array();
	foreach ( (array) ( isset( $_POST['cars_videos'] ) ? wp_unslash( $_POST['cars_videos'] ) : array() ) as $raw ) {
		$url = esc_url_raw( trim( (string) $raw ) );
		if ( '' !== $url && ! in_array( $url, $videos, true ) ) { $videos[] = $url; }
	}
	update_post_meta( $post_id, 'cars_videos', $videos );

	// Equipment.
	//
	// Merged onto what is stored rather than rebuilt from the form, and that is
	// load-bearing: the old plugin also wrote `fuelconsumption`, `emissionsticker` and
	// `emissionclass`, which das_feature_groups() does not list because the theme has
	// nothing to say about them. Rebuilding would delete real data on 53 vehicles the
	// first time somebody pressed Update.
	$stored = das_car_advanced( $post_id );
	$posted = isset( $_POST['advanced'] ) && is_array( $_POST['advanced'] ) ? wp_unslash( $_POST['advanced'] ) : array();
	foreach ( das_feature_groups() as $group => $conf ) {
		if ( ! isset( $stored[ $group ] ) || ! is_array( $stored[ $group ] ) ) { $stored[ $group ] = array(); }
		foreach ( $conf['fields'] as $field => $spec ) {
			$rule = isset( $spec[1] ) ? $spec[1] : null;
			$raw  = isset( $posted[ $group ][ $field ] ) ? sanitize_text_field( (string) $posted[ $group ][ $field ] ) : '';
			if ( 'count' === $rule ) {
				$val = preg_replace( '/\D/', '', $raw );
			} elseif ( is_array( $rule ) ) {
				$val = isset( $rule[ $raw ] ) ? $raw : '';
			} else {
				$val = '' === $raw ? '0' : '1';
			}
			$stored[ $group ][ $field ] = $val;
		}
	}
	update_post_meta( $post_id, 'advanced', $stored );
} );

/**
 * The vehicle list, as close to the old ThemeMakers admin as it deserves to be.
 *
 * Gianni put the two screens side by side and asked for parity in three things by name:
 * *"Shiqo renditjen p.sh. emrimin. filtrat"* — the ordering, the naming, the filters.
 * Putting a vehicle online from WordPress stays a real second route beside the desktop
 * application, so this screen has to be as workable as the one it replaced.
 *
 * ── The columns, in the old order and under the old captions ────────────────
 *
 *   Title | Thumbnail | Price | Year | Mileage | Engine Size | Featured | Sold |
 *   Draft | User | Date
 *
 * Body and Makes & models are ours and the old admin had neither; they sit inside the
 * spec run rather than at the end, so the sequence a person already knows is intact.
 *
 * ── Featured, Sold and Draft are three independent boxes ────────────────────
 * What was here before this was one read-only "Status" column printing Sold, or Featured,
 * or "On the lot" — written with elseif, so a vehicle that was both sold and featured only
 * ever admitted to being sold, and there was no way to draft one from the list at all.
 *
 *   Featured  car_is_featured  — shows in the homepage showcase
 *   Sold      car_is_sold      — drops out of the inventory grid, keeps its own page
 *   Draft     post_status      — off the site entirely, address and photographs kept
 *
 * A vehicle the desktop application manages is pushed again every time its status changes
 * there, and that push carries all three. So a tick here is the last word only until the
 * next push — which for the legacy stock, none of which the application has ever pushed,
 * means for good.
 */

/**
 * One read of a vehicle per row.
 *
 * `manage_car_posts_custom_column` fires once per column, and das_get_car() runs
 * wp_get_post_terms() and das_car_photos() each time — nine columns was nine of each, per
 * row, per page.
 */
function das_admin_car( $post_id ) {
	static $cache = array();
	$post_id = (int) $post_id;
	if ( ! array_key_exists( $post_id, $cache ) ) {
		$car = das_get_car( $post_id );
		$cache[ $post_id ] = is_array( $car ) ? $car : array();
	}
	return $cache[ $post_id ];
}

add_filter( 'manage_car_posts_columns', function ( $cols ) {
	// Built outright rather than inserted after 'title', because the point here is the
	// order: an insert leaves whatever WordPress and the taxonomy put at the end wherever
	// they happened to put it.
	return array(
		'cb'                   => $cols['cb'] ?? '',
		'title'                => $cols['title'] ?? __( 'Title', 'das-v4' ),
		'das_thumb'            => __( 'Thumbnail', 'das-v4' ),
		'das_price'            => __( 'Price', 'das-v4' ),
		'das_year'             => __( 'Year', 'das-v4' ),
		'das_miles'            => __( 'Mileage', 'das-v4' ),
		'das_engine'           => __( 'Engine Size', 'das-v4' ),
		'das_body'             => __( 'Body', 'das-v4' ),
		'taxonomy-carproducer' => $cols['taxonomy-carproducer'] ?? __( 'Makes & models', 'das-v4' ),
		'das_featured'         => __( 'Featured', 'das-v4' ),
		'das_sold'             => __( 'Sold', 'das-v4' ),
		'das_draft'            => __( 'Draft', 'das-v4' ),
		'das_user'             => __( 'User', 'das-v4' ),
		'date'                 => $cols['date'] ?? __( 'Date', 'das-v4' ),
	);
} );

/** One checkbox. `disabled` when this user may not edit that vehicle, rather than absent. */
function das_flag_box( $post_id, $flag, $on ) {
	$can = current_user_can( 'edit_post', $post_id );
	printf(
		'<input type="checkbox" class="das-flag" data-post="%d" data-flag="%s"%s%s>',
		(int) $post_id,
		esc_attr( $flag ),
		$on ? ' checked' : '',
		$can ? '' : ' disabled'
	);
}

add_action( 'manage_car_posts_custom_column', function ( $col, $post_id ) {
	$c = das_admin_car( $post_id );
	switch ( $col ) {
		case 'das_thumb':
			// das_car_photos() falls back to the legacy ThemeMakers folders, which is where
			// 53 of the 60 vehicles keep their pictures. A vehicle with none says so, rather
			// than leaving a hole that reads as a broken image.
			$src = (string) ( $c['thumb'] ?? '' );
			if ( $src ) {
				printf(
					'<a href="%s"><img src="%s" alt="" class="das-thumb"></a>',
					esc_url( (string) get_edit_post_link( $post_id ) ),
					esc_url( $src )
				);
			} else {
				echo '<span class="das-nothumb">' . esc_html__( 'No photo', 'das-v4' ) . '</span>';
			}
			break;
		case 'das_price':  echo esc_html( $c['price_txt'] ?? '—' ); break;
		case 'das_year':   echo esc_html( ( $c['year'] ?? '' ) !== '' ? $c['year'] : '—' ); break;
		case 'das_miles':  echo esc_html( $c['miles_txt'] ?? '—' ); break;
		case 'das_engine': echo esc_html( $c['engine'] ?? '—' ); break;
		case 'das_body':   echo esc_html( ( $c['body_txt'] ?? '' ) ?: '—' ); break;
		case 'das_featured': das_flag_box( $post_id, 'featured', ! empty( $c['featured'] ) ); break;
		case 'das_sold':     das_flag_box( $post_id, 'sold',     ! empty( $c['sold'] ) ); break;
		case 'das_draft':
			das_flag_box( $post_id, 'draft', 'draft' === get_post_status( $post_id ) );
			break;
		case 'das_user':
			// The old admin called this User and it is the post author. It is a link, because
			// the reason to look at it is to see the rest of that person's vehicles.
			$uid  = (int) get_post_field( 'post_author', $post_id );
			$name = $uid ? get_the_author_meta( 'display_name', $uid ) : '';
			if ( ! $name ) { echo '&mdash;'; break; }
			printf(
				'<a href="%s">%s</a>',
				esc_url( add_query_arg( array( 'post_type' => 'car', 'author' => $uid ), admin_url( 'edit.php' ) ) ),
				esc_html( $name )
			);
			break;
	}
}, 10, 2 );

/**
 * Toggling one flag from the list.
 *
 * Nonce, then `edit_post` on that exact vehicle — the capability is checked per post and
 * not once for the screen, because a screen somebody can open is not a vehicle they may
 * change. The answer carries the state that was actually stored, so the box goes back to
 * where it was if the write did not happen.
 */
add_action( 'wp_ajax_das_toggle_car_flag', function () {
	check_ajax_referer( 'das_car_flags', 'nonce' );

	$id   = isset( $_POST['post'] ) ? (int) $_POST['post'] : 0;
	$flag = isset( $_POST['flag'] ) ? sanitize_key( wp_unslash( $_POST['flag'] ) ) : '';
	$on   = ! empty( $_POST['on'] ) && 'false' !== $_POST['on'];

	$post = get_post( $id );
	if ( ! $post || 'car' !== $post->post_type ) { wp_send_json_error( array( 'msg' => 'Not a vehicle.' ), 404 ); }
	if ( ! current_user_can( 'edit_post', $id ) ) { wp_send_json_error( array( 'msg' => 'Not allowed.' ), 403 ); }

	switch ( $flag ) {
		case 'featured':
			update_post_meta( $id, 'car_is_featured', $on ? '1' : '0' );
			break;
		case 'sold':
			update_post_meta( $id, 'car_is_sold', $on ? '1' : '0' );
			break;
		case 'draft':
			// Never a deletion, in either direction: the address and the photographs are
			// kept whichever way this goes. Untick returns it to publish.
			wp_update_post( array( 'ID' => $id, 'post_status' => $on ? 'draft' : 'publish' ) );
			break;
		default:
			wp_send_json_error( array( 'msg' => 'Unknown flag.' ), 400 );
	}

	wp_send_json_success( array(
		'featured' => '1' === (string) get_post_meta( $id, 'car_is_featured', true ),
		'sold'     => '1' === (string) get_post_meta( $id, 'car_is_sold', true ),
		'draft'    => 'draft' === get_post_status( $id ),
	) );
} );

/* ── The filters above the list ────────────────────────────────────────────── */

/**
 * The makes that are actually on vehicles here — drafts included.
 *
 * Deliberately not `get_terms( hide_empty => true )`: a term's count only counts published
 * posts, and 46 of the 60 vehicles are drafts, so most makes would be missing from a
 * dropdown built that way. The catalogue itself is 1,131 terms (§11) and belongs nowhere
 * near a filter box.
 *
 * Only the makes are offered, never the models. A vehicle tagged with a model carries its
 * make too, and where it does not the taxonomy query matches children of the make anyway —
 * so a make narrows the list correctly either way, and 1,050 models would not.
 */
function das_admin_makes() {
	global $wpdb;
	$rows = $wpdb->get_results(
		"SELECT DISTINCT t.term_id, t.name, t.slug, tt.parent
		 FROM {$wpdb->term_relationships} tr
		 INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
		 INNER JOIN {$wpdb->terms} t ON t.term_id = tt.term_id
		 INNER JOIN {$wpdb->posts} p ON p.ID = tr.object_id
		 WHERE tt.taxonomy = 'carproducer' AND p.post_type = 'car'
		   AND p.post_status IN ('publish','draft','pending','private')"
	);

	$makes = array();
	foreach ( (array) $rows as $r ) {
		$term = $r->parent ? get_term( (int) $r->parent, 'carproducer' ) : $r;
		if ( ! $term || is_wp_error( $term ) ) { continue; }
		$makes[ (string) $term->slug ] = (string) $term->name;
	}
	asort( $makes );
	return $makes;
}

/**
 * The body types actually stored, rather than a fixed list.
 *
 * The same rule das_filter_options() applies on the front end. It matters here because the
 * old plugin wrote `Pickup` and `Wagon/SUV` beside our own `pickup_truck` and `suv`: those
 * are real values on real vehicles, and a filter built from das_body_types() alone could
 * never find them. das_label() renders whatever it does not recognise readably.
 */
function das_admin_body_values() {
	global $wpdb;
	$vals = $wpdb->get_col(
		"SELECT DISTINCT m.meta_value
		 FROM {$wpdb->postmeta} m
		 INNER JOIN {$wpdb->posts} p ON p.ID = m.post_id
		 WHERE m.meta_key = 'car_body' AND p.post_type = 'car'
		   AND p.post_status IN ('publish','draft','pending','private')
		   AND m.meta_value <> ''"
	);
	$out = array();
	foreach ( (array) $vals as $v ) { $out[ (string) $v ] = das_label( $v, das_body_types() ); }
	asort( $out );
	return $out;
}

/** The people who actually have vehicles here. Two of them, not every account on the site. */
function das_admin_car_authors() {
	global $wpdb;
	$ids = $wpdb->get_col(
		"SELECT DISTINCT post_author FROM {$wpdb->posts}
		 WHERE post_type = 'car' AND post_status IN ('publish','draft','pending','private')"
	);
	$out = array();
	foreach ( (array) $ids as $id ) {
		$name = get_the_author_meta( 'display_name', (int) $id );
		if ( $name ) { $out[ (int) $id ] = $name; }
	}
	asort( $out );
	return $out;
}

function das_admin_select( $name, $all_label, $options, $current ) {
	// A control that cannot narrow anything is left out entirely — the same rule as
	// das_filter_options() on the inventory page. One make, or one body type, is a box that
	// only ever produces the list you are already looking at.
	if ( count( $options ) < 2 ) { return; }
	echo '<select name="' . esc_attr( $name ) . '" style="max-width:180px">';
	echo '<option value="">' . esc_html( $all_label ) . '</option>';
	foreach ( $options as $value => $label ) {
		printf(
			'<option value="%s"%s>%s</option>',
			esc_attr( (string) $value ),
			selected( (string) $current, (string) $value, false ),
			esc_html( (string) $label )
		);
	}
	echo '</select>';
}

add_action( 'restrict_manage_posts', function ( $post_type ) {
	if ( 'car' !== $post_type ) { return; }
	$get = function ( $key ) {
		return isset( $_GET[ $key ] ) ? sanitize_text_field( wp_unslash( $_GET[ $key ] ) ) : '';
	};

	// `carproducer` is the taxonomy's own query var, so edit.php filters on it with no help
	// from us — and it matches a make's models too, since the taxonomy is hierarchical.
	das_admin_select( 'carproducer', __( 'All makes', 'das-v4' ), das_admin_makes(), $get( 'carproducer' ) );
	das_admin_select( 'das_body', __( 'All body types', 'das-v4' ), das_admin_body_values(), $get( 'das_body' ) );
	das_admin_select( 'das_flag', __( 'Featured, sold or for sale', 'das-v4' ), array(
		'featured' => __( 'Featured', 'das-v4' ),
		'sold'     => __( 'Sold', 'das-v4' ),
		'forsale'  => __( 'For sale', 'das-v4' ),
	), $get( 'das_flag' ) );

	// Written out rather than wp_dropdown_users(), which hardcodes 0 as the value of its
	// "all" option — and WP_Query reads author=0 as a real author with no vehicles.
	das_admin_select( 'author', __( 'All users', 'das-v4' ), das_admin_car_authors(), $get( 'author' ) );
} );

/**
 * Which vehicles carry a meta value — and which do not.
 *
 * One SQL query each rather than a meta_query, and the reason is in the data: **eleven
 * vehicles have car_is_sold stored as SQL NULL**, written that way by the old plugin (§12).
 * Against SQL NULL `!= '1'` and NOT EXISTS are both false at once, so a meta_query for
 * "for sale" would quietly drop exactly those eleven and nothing would look wrong.
 * COALESCE answers it, and the LEFT JOIN covers a vehicle with no such row at all.
 */
function das_admin_ids_with_meta( $key, $value ) {
	global $wpdb;
	return array_map( 'intval', (array) $wpdb->get_col( $wpdb->prepare(
		"SELECT p.ID FROM {$wpdb->posts} p
		 INNER JOIN {$wpdb->postmeta} m ON m.post_id = p.ID AND m.meta_key = %s
		 WHERE p.post_type = 'car' AND m.meta_value = %s",
		$key, $value
	) ) );
}

function das_admin_ids_without_meta( $key, $value ) {
	global $wpdb;
	return array_map( 'intval', (array) $wpdb->get_col( $wpdb->prepare(
		"SELECT p.ID FROM {$wpdb->posts} p
		 LEFT JOIN {$wpdb->postmeta} m ON m.post_id = p.ID AND m.meta_key = %s
		 WHERE p.post_type = 'car' AND COALESCE(m.meta_value, '') <> %s",
		$key, $value
	) ) );
}

add_action( 'parse_query', function ( $q ) {
	global $pagenow;
	if ( ! is_admin() || 'edit.php' !== $pagenow || ! $q->is_main_query() ) { return; }
	if ( 'car' !== (string) $q->get( 'post_type' ) ) { return; }

	$body = isset( $_GET['das_body'] ) ? sanitize_text_field( wp_unslash( $_GET['das_body'] ) ) : '';
	$flag = isset( $_GET['das_flag'] ) ? sanitize_key( wp_unslash( $_GET['das_flag'] ) ) : '';
	if ( '' === $body && '' === $flag ) { return; }

	$sets = array();
	if ( '' !== $body ) { $sets[] = das_admin_ids_with_meta( 'car_body', $body ); }
	if ( 'featured' === $flag ) { $sets[] = das_admin_ids_with_meta( 'car_is_featured', '1' ); }
	if ( 'sold' === $flag )     { $sets[] = das_admin_ids_with_meta( 'car_is_sold', '1' ); }
	if ( 'forsale' === $flag )  { $sets[] = das_admin_ids_without_meta( 'car_is_sold', '1' ); }
	if ( ! $sets ) { return; }

	$ids = array_shift( $sets );
	foreach ( $sets as $set ) { $ids = array_intersect( $ids, $set ); }

	// post__in with an empty array is IGNORED by WP_Query — it would show every vehicle,
	// which is the opposite of "nothing matched". 0 is an id no post can have.
	$q->set( 'post__in', $ids ? array_values( $ids ) : array( 0 ) );
} );

/**
 * Sorting.
 *
 * The values here used to be the meta keys themselves, which does nothing at all:
 * WP_Query drops an `orderby` it does not recognise, and a meta key is not one of them.
 * So Price and Mileage have never sorted. They also have to sort **numerically** — as
 * strings $9,500 comes after $33,999.
 */
add_filter( 'manage_edit-car_sortable_columns', function ( $cols ) {
	$cols['das_price'] = 'das_price';
	$cols['das_miles'] = 'das_miles';
	$cols['das_year']  = 'das_year';
	return $cols;
} );

add_action( 'pre_get_posts', function ( $q ) {
	global $pagenow;
	if ( ! is_admin() || 'edit.php' !== $pagenow || ! $q->is_main_query() ) { return; }
	if ( 'car' !== (string) $q->get( 'post_type' ) ) { return; }

	$map = array( 'das_price' => 'car_price', 'das_miles' => 'car_mileage', 'das_year' => 'car_year' );
	$by  = (string) $q->get( 'orderby' );
	if ( ! isset( $map[ $by ] ) ) { return; }

	// §12: a meta_key in WP_Query filters as well as sorts, so this would hide a vehicle
	// with no such row. Safe for these three: both writers — the metabox save above and the
	// application's push — write all three keys every time, empty string included.
	$q->set( 'meta_key', $map[ $by ] );
	$q->set( 'orderby', 'meta_value_num' );
} );

/** The list screen only, and only for vehicles. */
add_action( 'admin_footer-edit.php', function () {
	global $typenow;
	if ( 'car' !== $typenow ) { return; }
	$nonce = wp_create_nonce( 'das_car_flags' );
	?>
	<style>
		.column-das_featured, .column-das_sold, .column-das_draft { width: 72px; text-align: center; }
		td.column-das_featured, td.column-das_sold, td.column-das_draft { text-align: center; }
		.column-das_thumb { width: 76px; }
		.column-das_price, .column-das_miles { width: 92px; }
		.column-das_year { width: 62px; }
		.column-das_engine, .column-das_body { width: 96px; }
		.column-das_user { width: 130px; }
		img.das-thumb { width: 64px; height: 48px; object-fit: cover; border-radius: 4px; display: block; }
		.das-nothumb { display: grid; place-items: center; width: 64px; height: 48px; border-radius: 4px;
		               background: #f0f0f1; color: #8c8f94; font-size: 10px; text-align: center; }
		.das-flag.is-busy { opacity: .4; }
		.das-flag-note { display: block; font-size: 11px; line-height: 1.3; margin-top: 2px; }
		.das-flag-note.ok { color: #1d7a4c; }
		.das-flag-note.bad { color: #b32d2e; }
	</style>
	<script>
	( function () {
		var nonce = <?php echo wp_json_encode( $nonce ); ?>;
		var url = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;

		function note( box, text, cls ) {
			var n = box.parentNode.querySelector( '.das-flag-note' );
			if ( ! n ) {
				n = document.createElement( 'span' );
				n.className = 'das-flag-note';
				box.parentNode.appendChild( n );
			}
			n.className = 'das-flag-note ' + ( cls || '' );
			n.textContent = text;
			if ( 'ok' === cls ) { setTimeout( function () { n.textContent = ''; }, 1600 ); }
		}

		document.addEventListener( 'change', function ( e ) {
			var box = e.target;
			if ( ! box.classList || ! box.classList.contains( 'das-flag' ) ) { return; }

			var wanted = box.checked;
			var body = new URLSearchParams();
			body.set( 'action', 'das_toggle_car_flag' );
			body.set( 'nonce', nonce );
			body.set( 'post', box.dataset.post );
			body.set( 'flag', box.dataset.flag );
			body.set( 'on', wanted ? '1' : '' );

			box.classList.add( 'is-busy' );
			box.disabled = true;

			fetch( url, { method: 'POST', credentials: 'same-origin', body: body } )
				.then( function ( r ) { return r.json().catch( function () { return null; } ); } )
				.then( function ( j ) {
					if ( ! j || ! j.success ) {
						// Put the box back where it was: it says what is stored, not what was clicked.
						box.checked = ! wanted;
						note( box, ( j && j.data && j.data.msg ) || 'Not saved.', 'bad' );
						return;
					}
					var row = box.closest( 'tr' );
					if ( row ) {
						[ 'featured', 'sold', 'draft' ].forEach( function ( f ) {
							var b = row.querySelector( '.das-flag[data-flag="' + f + '"]' );
							if ( b ) { b.checked = !! j.data[ f ]; }
						} );
					}
					note( box, 'Saved', 'ok' );
				} )
				.catch( function () {
					box.checked = ! wanted;
					note( box, 'Not saved.', 'bad' );
				} )
				.finally( function () {
					box.classList.remove( 'is-busy' );
					box.disabled = false;
				} );
		} );
	}() );
	</script>
	<?php
} );

/**
 * Hide WordPress's raw Custom Fields box on vehicles and staff.
 *
 * It lists every meta key underneath our own tidy box - the same data twice,
 * the second time editable as raw text. Confusing at best, and one stray edit
 * to car_price breaks the listing.
 */
add_action( 'admin_menu', function () {
	foreach ( array( 'car', 'staff-page' ) as $type ) {
		remove_meta_box( 'postcustom', $type, 'normal' );
	}
} );

add_filter( 'default_hidden_meta_boxes', function ( $hidden, $screen ) {
	if ( in_array( $screen->post_type ?? '', array( 'car', 'staff-page' ), true ) ) {
		$hidden[] = 'postcustom';
	}
	return $hidden;
}, 10, 2 );
