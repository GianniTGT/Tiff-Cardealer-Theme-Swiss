<?php
/** Leads: CPT storage + front-end form + email notification. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* ---------- Storage ---------- */

add_action( 'init', function () {
	register_post_type( 'das_lead', array(
		'labels' => array(
			'name'               => __( 'Leads', 'das-v4' ),
			'singular_name'      => __( 'Lead', 'das-v4' ),
			'menu_name'          => das_menu_label( __( 'Leads', 'das-v4' ) ),
			'all_items'          => __( 'All leads', 'das-v4' ),
			'edit_item'          => __( 'Lead', 'das-v4' ),
			'search_items'       => __( 'Search leads', 'das-v4' ),
			'not_found'          => __( 'No leads yet.', 'das-v4' ),
		),
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_rest'        => false,
		'menu_position'       => 26,
		'menu_icon'           => 'dashicons-email-alt',
		'supports'            => array( 'title' ),
		'capability_type'     => 'post',
		'map_meta_cap'        => true,
		'capabilities'        => array( 'create_posts' => 'do_not_allow' ),
		'exclude_from_search' => true,
		'has_archive'         => false,
		'rewrite'             => false,
	) );
} );

function das_lead_fields() {
	return array( 'lead_name', 'lead_phone', 'lead_email', 'lead_message', 'lead_car_id', 'lead_source', 'lead_type', 'lead_down_payment', 'lead_income_band', 'lead_employed', 'lead_ip', 'lead_ua' );
}

/* ---------- Form rendering ---------- */

/**
 * Renders the lead form.
 *
 * @param array $args id, title, intro, car_id (preselected), show_car_select, source.
 */
function das_lead_form( $args = array() ) {
	$args = wp_parse_args( $args, array(
		'id'               => 'contactform',
		'title'            => 'Send us a message',
		'intro'            => "Tell us what you're looking for and we'll get back to you the same business day.",
		'car_id'           => 0,
		'show_car_select'  => true,
		'source'           => 'contact',
		'button'           => 'Send message',
		'type'             => 'inquiry',
		'financing'        => false,
	) );
	$status = isset( $_GET['lead'] ) ? sanitize_key( wp_unslash( $_GET['lead'] ) ) : '';
	$uid    = esc_attr( $args['id'] );
	?>
	<div class="fin-card cform rv" id="<?php echo $uid; ?>">
		<?php if ( $args['title'] ) : ?>
			<h2 style="font-size:28px;margin-bottom:6px"><?php echo esc_html( $args['title'] ); ?></h2>
		<?php endif; ?>
		<?php if ( $args['intro'] ) : ?>
			<p style="color:var(--steel);margin-bottom:18px"><?php echo esc_html( $args['intro'] ); ?></p>
		<?php endif; ?>

		<?php if ( 'ok' === $status ) : ?>
			<div class="das-notice das-notice-ok">Thank you — your message is in. We will call or email you shortly. For anything urgent, call <a href="tel:<?php echo esc_attr( das_info( 'tel1' ) ); ?>"><?php echo esc_html( das_info( 'phone1' ) ); ?></a>.</div>
		<?php elseif ( 'slow' === $status ) : ?>
			<div class="das-notice das-notice-err">That is a few requests in a short time. Give it an hour, or just call <a href="tel:<?php echo esc_attr( das_info( 'tel1' ) ); ?>"><?php echo esc_html( das_info( 'phone1' ) ); ?></a> &mdash; a person answers faster anyway.</div>
		<?php elseif ( 'error' === $status ) : ?>
			<div class="das-notice das-notice-err">Something went wrong. Please check the required fields, or just call <a href="tel:<?php echo esc_attr( das_info( 'tel1' ) ); ?>"><?php echo esc_html( das_info( 'phone1' ) ); ?></a>.</div>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="das_lead">
			<input type="hidden" name="lead_source" value="<?php echo esc_attr( $args['source'] ); ?>">
			<input type="hidden" name="lead_type" value="<?php echo esc_attr( $args['type'] ); ?>">
			<input type="hidden" name="lead_redirect" value="<?php echo esc_url( das_current_url() ); ?>">
			<input type="hidden" name="lead_anchor" value="<?php echo $uid; ?>">
			<?php wp_nonce_field( 'das_lead_submit', 'das_lead_nonce' ); ?>
			<p class="das-hp"><label>Leave this field empty<input type="text" name="das_website" tabindex="-1" autocomplete="off"></label></p>

			<div class="form-grid">
				<div>
					<label for="<?php echo $uid; ?>_name">Your name</label>
					<input id="<?php echo $uid; ?>_name" name="lead_name" type="text" placeholder="John Doe" required>
				</div>
				<div>
					<label for="<?php echo $uid; ?>_phone">Phone</label>
					<input id="<?php echo $uid; ?>_phone" name="lead_phone" type="tel" placeholder="907-555-0100" required>
				</div>
			</div>

			<label for="<?php echo $uid; ?>_email">Email <span style="font-weight:400;text-transform:none;letter-spacing:0;color:var(--steel)">(optional)</span></label>
			<input id="<?php echo $uid; ?>_email" name="lead_email" type="email" placeholder="you@example.com">

			<?php if ( $args['show_car_select'] ) : ?>
				<label for="<?php echo $uid; ?>_car">Vehicle of interest</label>
				<select id="<?php echo $uid; ?>_car" name="lead_car_id">
					<option value="0">&mdash; Select a vehicle (optional) &mdash;</option>
					<?php foreach ( das_get_cars( array( 'posts_per_page' => 200 ) ) as $c ) : ?>
						<option value="<?php echo (int) $c['id']; ?>" <?php selected( (int) $args['car_id'], (int) $c['id'] ); ?>><?php echo esc_html( $c['title'] . ' — ' . $c['price_txt'] ); ?></option>
					<?php endforeach; ?>
				</select>
			<?php else : ?>
				<input type="hidden" name="lead_car_id" value="<?php echo (int) $args['car_id']; ?>">
			<?php endif; ?>

			<?php if ( $args['financing'] ) : ?>
				<div class="form-grid">
					<div>
						<label for="<?php echo $uid; ?>_down">Down payment you have ready ($)</label>
						<input id="<?php echo $uid; ?>_down" name="lead_down_payment" type="number" min="0" step="100" placeholder="2000" inputmode="numeric">
					</div>
					<div>
						<label for="<?php echo $uid; ?>_employed">Working at the moment?</label>
						<select id="<?php echo $uid; ?>_employed" name="lead_employed">
							<option value="">Prefer not to say</option>
							<option value="1">Yes</option>
							<option value="0">Not right now</option>
						</select>
					</div>
				</div>

				<label for="<?php echo $uid; ?>_income">Roughly what comes in each month</label>
				<select id="<?php echo $uid; ?>_income" name="lead_income_band">
					<option value="">Prefer not to say</option>
					<?php foreach ( das_income_bands() as $band ) : ?>
						<option value="<?php echo esc_attr( $band ); ?>"><?php echo esc_html( $band ); ?></option>
					<?php endforeach; ?>
				</select>
				<p class="fin-note" style="margin:6px 0 16px">A range is all we need &mdash; never an exact figure, and never a Social Security number.</p>
			<?php endif; ?>

			<label for="<?php echo $uid; ?>_msg">Message</label>
			<textarea id="<?php echo $uid; ?>_msg" name="lead_message" placeholder="I'd like to schedule a test drive… / I have a 2015 Outback to trade…" required></textarea>

			<button type="submit" class="btn btn-brand"><?php echo esc_html( $args['button'] ); ?></button>
			<p class="fin-note">By sending this form you agree to be contacted by <?php echo esc_html( das_info( 'legal' ) ); ?> about your inquiry. We never sell your details.</p>
		</form>
	</div>
	<?php
}

function das_current_url() {
	$scheme = is_ssl() ? 'https://' : 'http://';
	$host   = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
	$uri    = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/';
	$url    = $scheme . $host . $uri;
	return remove_query_arg( 'lead', $url );
}

/* ---------- Submission handling ---------- */

add_action( 'admin_post_nopriv_das_lead', 'das_handle_lead' );
add_action( 'admin_post_das_lead', 'das_handle_lead' );

function das_handle_lead() {
	$redirect = isset( $_POST['lead_redirect'] ) ? esc_url_raw( wp_unslash( $_POST['lead_redirect'] ) ) : home_url( '/' );
	$redirect = wp_validate_redirect( $redirect, home_url( '/' ) );
	$anchor   = isset( $_POST['lead_anchor'] ) ? sanitize_key( wp_unslash( $_POST['lead_anchor'] ) ) : 'contactform';

	$fail = function ( $why = 'error' ) use ( $redirect, $anchor ) {
		wp_safe_redirect( add_query_arg( 'lead', $why, $redirect ) . '#' . $anchor );
		exit;
	};

	if ( ! isset( $_POST['das_lead_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['das_lead_nonce'] ), 'das_lead_submit' ) ) {
		$fail();
	}
	// Honeypot: silently accept, store nothing.
	if ( ! empty( $_POST['das_website'] ) ) {
		wp_safe_redirect( add_query_arg( 'lead', 'ok', $redirect ) . '#' . $anchor );
		exit;
	}

	$type = isset( $_POST['lead_type'] ) ? sanitize_key( wp_unslash( $_POST['lead_type'] ) ) : 'inquiry';
	if ( '' === $type ) { $type = 'inquiry'; }

	// Only the financing form carries this. It is the one with real money behind it, and
	// a honeypot alone does not stop somebody running the same form by hand.
	if ( 'financing' === $type ) {
		$rate_key = 'das_finrate_' . substr( md5( ( isset( $_SERVER['REMOTE_ADDR'] ) ? (string) $_SERVER['REMOTE_ADDR'] : '' ) . '|' . wp_salt() ), 0, 24 );
		$seen     = (int) get_transient( $rate_key );
		if ( $seen >= 3 ) { $fail( 'slow' ); }
		set_transient( $rate_key, $seen + 1, HOUR_IN_SECONDS );
	}

	$name    = isset( $_POST['lead_name'] ) ? sanitize_text_field( wp_unslash( $_POST['lead_name'] ) ) : '';
	$phone   = isset( $_POST['lead_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['lead_phone'] ) ) : '';
	$email   = isset( $_POST['lead_email'] ) ? sanitize_email( wp_unslash( $_POST['lead_email'] ) ) : '';
	$message = isset( $_POST['lead_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['lead_message'] ) ) : '';
	$car_id  = isset( $_POST['lead_car_id'] ) ? absint( $_POST['lead_car_id'] ) : 0;
	$source  = isset( $_POST['lead_source'] ) ? sanitize_key( wp_unslash( $_POST['lead_source'] ) ) : 'contact';

	if ( '' === $name || '' === $phone || '' === $message ) { $fail(); }
	if ( $car_id && 'car' !== get_post_type( $car_id ) ) { $car_id = 0; }

	$car_title = $car_id ? get_the_title( $car_id ) : '';
	$title     = sprintf( '%s — %s%s', $name, $phone, $car_title ? ' — ' . $car_title : '' );

	$lead_id = wp_insert_post( array(
		'post_type'   => 'das_lead',
		'post_status' => 'publish',
		'post_title'  => wp_strip_all_tags( $title ),
	), true );

	if ( is_wp_error( $lead_id ) ) { $fail(); }

	update_post_meta( $lead_id, 'lead_name', $name );
	update_post_meta( $lead_id, 'lead_phone', $phone );
	update_post_meta( $lead_id, 'lead_email', $email );
	update_post_meta( $lead_id, 'lead_message', $message );
	update_post_meta( $lead_id, 'lead_car_id', $car_id );
	update_post_meta( $lead_id, 'lead_source', $source );
	update_post_meta( $lead_id, 'lead_type', $type );
	if ( 'financing' === $type ) {
		$down   = isset( $_POST['lead_down_payment'] ) ? preg_replace( '/[^0-9]/', '', (string) wp_unslash( $_POST['lead_down_payment'] ) ) : '';
		$band   = isset( $_POST['lead_income_band'] ) ? sanitize_text_field( wp_unslash( $_POST['lead_income_band'] ) ) : '';
		$employ = isset( $_POST['lead_employed'] ) ? sanitize_text_field( wp_unslash( $_POST['lead_employed'] ) ) : '';
		// A band we did not offer is not stored: the list is the contract, and an exact
		// figure typed into it is the one thing that must never be kept here.
		if ( '' !== $band && ! in_array( $band, das_income_bands(), true ) ) { $band = ''; }
		if ( ! in_array( $employ, array( '0', '1' ), true ) ) { $employ = ''; }
		update_post_meta( $lead_id, 'lead_down_payment', $down );
		update_post_meta( $lead_id, 'lead_income_band', $band );
		update_post_meta( $lead_id, 'lead_employed', $employ );
	}
	update_post_meta( $lead_id, 'lead_ip', isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '' );
	update_post_meta( $lead_id, 'lead_ua', isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ), 0, 255 ) : '' );

	das_notify_lead( $lead_id );

	wp_safe_redirect( add_query_arg( 'lead', 'ok', $redirect ) . '#' . $anchor );
	exit;
}

function das_notify_lead( $lead_id ) {
	$to = apply_filters( 'das_lead_recipient', das_info( 'email' ) );
	$g  = function ( $k ) use ( $lead_id ) { return (string) get_post_meta( $lead_id, $k, true ); };
	$car_id = (int) $g( 'lead_car_id' );

	$lines = array(
		'New website lead — ' . das_info( 'name' ),
		'',
		'Name:    ' . $g( 'lead_name' ),
		'Phone:   ' . $g( 'lead_phone' ),
		'Email:   ' . ( $g( 'lead_email' ) ?: '—' ),
		'Vehicle: ' . ( $car_id ? get_the_title( $car_id ) . ' (' . get_permalink( $car_id ) . ')' : '— not specified —' ),
		'Source:  ' . $g( 'lead_source' ),
		'Type:    ' . ( $g( 'lead_type' ) ?: 'inquiry' ),
		'Time:    ' . get_the_date( 'Y-m-d H:i', $lead_id ) . ' ' . wp_timezone_string(),
		'',
		'Message:',
		$g( 'lead_message' ),
		'',
		'—',
		'Open in admin: ' . admin_url( 'post.php?post=' . $lead_id . '&action=edit' ),
	);

	$fin = das_lead_financing_rows( $lead_id );
	if ( $fin ) {
		$lines[] = '';
		$lines[] = 'Financing answers:';
		foreach ( $fin as $label => $value ) {
			$lines[] = '  ' . $label . ': ' . $value;
		}
	}

	$subject = sprintf( '[Website lead] %s — %s', $g( 'lead_name' ), $car_id ? get_the_title( $car_id ) : 'general inquiry' );
	$headers = array( 'Content-Type: text/plain; charset=UTF-8' );
	if ( $g( 'lead_email' ) && is_email( $g( 'lead_email' ) ) ) {
		$headers[] = 'Reply-To: ' . $g( 'lead_name' ) . ' <' . $g( 'lead_email' ) . '>';
	}

	$sent = wp_mail( $to, $subject, implode( "\n", $lines ), $headers );
	update_post_meta( $lead_id, 'lead_mailed', $sent ? '1' : '0' );
	return $sent;
}

/* ---------- Admin UI ---------- */

add_action( 'add_meta_boxes', function () {
	add_meta_box( 'das_lead_box', __( 'Lead details', 'das-v4' ), 'das_lead_box', 'das_lead', 'normal', 'high' );
} );

function das_lead_box( $post ) {
	$g = function ( $k ) use ( $post ) { return (string) get_post_meta( $post->ID, $k, true ); };
	$car_id = (int) $g( 'lead_car_id' );
	$rows = array(
		'Name'    => esc_html( $g( 'lead_name' ) ),
		'Phone'   => $g( 'lead_phone' ) ? '<a href="tel:' . esc_attr( preg_replace( '/[^0-9+]/', '', $g( 'lead_phone' ) ) ) . '">' . esc_html( $g( 'lead_phone' ) ) . '</a>' : '—',
		'Email'   => $g( 'lead_email' ) ? '<a href="mailto:' . esc_attr( $g( 'lead_email' ) ) . '">' . esc_html( $g( 'lead_email' ) ) . '</a>' : '—',
		'Vehicle' => $car_id ? '<a href="' . esc_url( get_edit_post_link( $car_id ) ) . '">' . esc_html( get_the_title( $car_id ) ) . '</a>' : '—',
		'Source'  => esc_html( $g( 'lead_source' ) ?: '—' ),
		'Type'    => esc_html( $g( 'lead_type' ) ?: 'inquiry' ),
		'Emailed' => '1' === $g( 'lead_mailed' ) ? 'yes' : '<span style="color:#b32d2e">no — check WP Mail SMTP log</span>',
		'IP'      => esc_html( $g( 'lead_ip' ) ?: '—' ),
	);
	foreach ( das_lead_financing_rows( $post->ID ) as $label => $value ) {
		$rows[ $label ] = esc_html( $value );
	}
	echo '<table class="widefat striped" style="margin-bottom:16px"><tbody>';
	foreach ( $rows as $k => $v ) {
		echo '<tr><td style="width:150px"><strong>' . esc_html( $k ) . '</strong></td><td>' . $v . '</td></tr>';
	}
	echo '</tbody></table>';
	echo '<h4 style="margin:0 0 6px">Message</h4>';
	echo '<div style="background:#f6f7f9;border:1px solid #dcdcde;border-radius:6px;padding:14px;white-space:pre-wrap">' . esc_html( $g( 'lead_message' ) ) . '</div>';
}

add_filter( 'manage_das_lead_posts_columns', function ( $cols ) {
	return array(
		'cb'         => $cols['cb'] ?? '',
		'title'      => 'Lead',
		'lead_phone' => 'Phone',
		'lead_car'   => 'Vehicle',
		'lead_src'   => 'Source',
		'date'       => 'Received',
	);
} );

add_action( 'manage_das_lead_posts_custom_column', function ( $col, $post_id ) {
	switch ( $col ) {
		case 'lead_phone':
			echo esc_html( get_post_meta( $post_id, 'lead_phone', true ) ?: '—' );
			break;
		case 'lead_car':
			$cid = (int) get_post_meta( $post_id, 'lead_car_id', true );
			echo $cid ? esc_html( get_the_title( $cid ) ) : '—';
			break;
		case 'lead_src':
			echo esc_html( get_post_meta( $post_id, 'lead_source', true ) ?: '—' );
			break;
	}
}, 10, 2 );

add_action( 'admin_menu', function () {
	$n = wp_count_posts( 'das_lead' );
	if ( ! $n || empty( $n->publish ) ) { return; }
} );

/**
 * Income is asked as a range and stored as one: a band, never an exact figure, and
 * never anything the FTC Safeguards Rule would make us responsible for holding.
 */
function das_income_bands() {
	return array(
		'Under $2,000 a month',
		'$2,000 - $3,500 a month',
		'$3,500 - $5,000 a month',
		'$5,000 - $7,500 a month',
		'Over $7,500 a month',
	);
}

/** The financing answers, for the notification email and the admin box. */
function das_lead_financing_rows( $lead_id ) {
	$g = function ( $k ) use ( $lead_id ) { return (string) get_post_meta( $lead_id, $k, true ); };
	if ( 'financing' !== $g( 'lead_type' ) ) { return array(); }
	$employed = $g( 'lead_employed' );
	return array(
		'Down payment' => '' !== $g( 'lead_down_payment' ) ? '$' . number_format_i18n( (int) $g( 'lead_down_payment' ) ) : 'not stated',
		'Income'       => $g( 'lead_income_band' ) ?: 'not stated',
		'Working'      => ( '1' === $employed ? 'yes' : ( '0' === $employed ? 'not right now' : 'not stated' ) ),
	);
}
