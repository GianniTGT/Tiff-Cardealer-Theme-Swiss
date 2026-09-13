<?php
/**
 * Which vehicles people actually look at.
 *
 * Counted here rather than read out of an analytics account because the question
 * this answers - which cars on the lot are getting attention - is one the site can
 * answer exactly, by vehicle, with the price and the status beside it. How many
 * people came, where from, and how long they stayed is a different job that needs
 * Google Analytics; the page says so rather than pretending otherwise.
 *
 * No cookie is set. A view is de-duplicated for 30 minutes on a salted hash of
 * address and browser held in a transient, so a customer clicking through a photo
 * gallery counts once and nothing identifying is ever stored.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Anything that is plainly not a customer. An empty user agent counts as a bot. */
function das_traffic_is_bot() {
	$ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? strtolower( (string) $_SERVER['HTTP_USER_AGENT'] ) : '';
	if ( '' === $ua ) { return true; }
	foreach ( array( 'bot', 'crawl', 'spider', 'slurp', 'facebookexternalhit', 'headless', 'curl', 'wget', 'python', 'java/', 'monitor', 'preview', 'lighthouse', 'pagespeed', 'uptime', 'scan', 'fetch' ) as $marker ) {
		if ( false !== strpos( $ua, $marker ) ) { return true; }
	}
	return false;
}

add_action( 'template_redirect', function () {
	if ( ! is_singular( 'car' ) ) { return; }
	if ( is_user_logged_in() || wp_doing_ajax() || das_traffic_is_bot() ) { return; }

	$id = (int) get_queried_object_id();
	if ( ! $id ) { return; }

	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? (string) $_SERVER['REMOTE_ADDR'] : '';
	$ua  = isset( $_SERVER['HTTP_USER_AGENT'] ) ? (string) $_SERVER['HTTP_USER_AGENT'] : '';
	$key = 'das_v_' . substr( md5( $ip . '|' . $ua . '|' . $id . '|' . wp_salt() ), 0, 24 );
	if ( get_transient( $key ) ) { return; }
	set_transient( $key, 1, 30 * MINUTE_IN_SECONDS );

	if ( ! get_option( 'das_views_since' ) ) {
		update_option( 'das_views_since', current_time( 'Y-m-d' ), false );
	}

	update_post_meta( $id, 'das_views_total', (int) get_post_meta( $id, 'das_views_total', true ) + 1 );

	$daily = get_post_meta( $id, 'das_views_daily', true );
	if ( ! is_array( $daily ) ) { $daily = array(); }
	$today = current_time( 'Y-m-d' );
	$daily[ $today ] = isset( $daily[ $today ] ) ? (int) $daily[ $today ] + 1 : 1;
	if ( count( $daily ) > 70 ) {
		krsort( $daily );
		$daily = array_slice( $daily, 0, 70, true );
	}
	update_post_meta( $id, 'das_views_daily', $daily );
} );

/** Views in the last N days. Dates are Y-m-d strings, so a string compare is the range. */
function das_views_last_days( $post_id, $days = 30 ) {
	$daily = get_post_meta( (int) $post_id, 'das_views_daily', true );
	if ( ! is_array( $daily ) ) { return 0; }
	$cut = gmdate( 'Y-m-d', strtotime( current_time( 'Y-m-d' ) . ' -' . (int) $days . ' days' ) );
	$sum = 0;
	foreach ( $daily as $day => $n ) {
		if ( (string) $day >= $cut ) { $sum += (int) $n; }
	}
	return $sum;
}

add_action( 'admin_menu', function () {
	add_submenu_page(
		'edit.php?post_type=car',
		__( 'Traffic', 'das-v4' ),
		__( 'Traffic', 'das-v4' ),
		'edit_posts',
		'das-traffic',
		'das_traffic_page'
	);
} );

function das_traffic_page() {
	if ( ! current_user_can( 'edit_posts' ) ) { return; }

	$rows = array();
	foreach ( get_posts( array(
		'post_type'      => 'car',
		'post_status'    => array( 'publish', 'draft' ),
		'posts_per_page' => -1,
	) ) as $post ) {
		$rows[] = array(
			'id'     => $post->ID,
			'title'  => get_the_title( $post ),
			'status' => get_post_status( $post ),
			'd30'    => das_views_last_days( $post->ID, 30 ),
			'all'    => (int) get_post_meta( $post->ID, 'das_views_total', true ),
			'legacy' => (int) get_post_meta( $post->ID, 'car_views_count', true ),
			'url'    => get_permalink( $post ),
		);
	}
	usort( $rows, function ( $a, $b ) {
		if ( $a['d30'] === $b['d30'] ) {
			if ( $a['all'] === $b['all'] ) { return $b['legacy'] <=> $a['legacy']; }
			return $b['all'] <=> $a['all'];
		}
		return $b['d30'] <=> $a['d30'];
	} );

	$since = (string) get_option( 'das_views_since' );
	$d30   = array_sum( wp_list_pluck( $rows, 'd30' ) );
	$all   = array_sum( wp_list_pluck( $rows, 'all' ) );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Vehicle traffic', 'das-v4' ); ?></h1>

		<?php do_action( 'das_traffic_top' ); ?>

		<?php if ( '' === $since ) : ?>
			<div class="notice notice-info inline"><p>
				<?php esc_html_e( 'Counting is on, but nobody has opened a vehicle page yet. Signed-in staff and known bots are never counted, so your own visits will not appear here.', 'das-v4' ); ?>
			</p></div>
		<?php else : ?>
			<p class="description" style="font-size:13px">
				<?php printf( esc_html__( 'Counting since %s. Signed-in staff and known bots are not counted, and one person counts once per vehicle per 30 minutes.', 'das-v4' ), esc_html( $since ) ); ?>
			</p>
			<p style="font-size:15px;margin:14px 0 18px">
				<strong style="font-size:22px"><?php echo esc_html( number_format_i18n( $d30 ) ); ?></strong>
				<?php esc_html_e( 'vehicle views in the last 30 days', 'das-v4' ); ?>
				&nbsp;&middot;&nbsp;
				<strong><?php echo esc_html( number_format_i18n( $all ) ); ?></strong>
				<?php esc_html_e( 'since counting began', 'das-v4' ); ?>
			</p>
		<?php endif; ?>

		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Vehicle', 'das-v4' ); ?></th>
					<th style="width:110px"><?php esc_html_e( 'Status', 'das-v4' ); ?></th>
					<th style="width:120px"><?php esc_html_e( 'Last 30 days', 'das-v4' ); ?></th>
					<th style="width:120px"><?php esc_html_e( 'Since counting', 'das-v4' ); ?></th>
					<th style="width:150px"><?php esc_html_e( 'Before Aug 2026', 'das-v4' ); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php if ( ! $rows ) : ?>
				<tr><td colspan="5"><?php esc_html_e( 'No vehicles yet.', 'das-v4' ); ?></td></tr>
			<?php endif; ?>
			<?php foreach ( $rows as $row ) : ?>
				<tr>
					<td>
						<strong><a href="<?php echo esc_url( (string) get_edit_post_link( $row['id'] ) ); ?>"><?php echo esc_html( $row['title'] ); ?></a></strong>
						<div class="row-actions"><span><a href="<?php echo esc_url( $row['url'] ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'View on site', 'das-v4' ); ?></a></span></div>
					</td>
					<td><?php echo 'publish' === $row['status'] ? esc_html__( 'Live', 'das-v4' ) : esc_html__( 'Draft', 'das-v4' ); ?></td>
					<td><strong><?php echo esc_html( number_format_i18n( $row['d30'] ) ); ?></strong></td>
					<td><?php echo esc_html( number_format_i18n( $row['all'] ) ); ?></td>
					<td><?php echo $row['legacy'] ? esc_html( number_format_i18n( $row['legacy'] ) ) : '&mdash;'; ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>

		<h2 style="margin-top:32px"><?php esc_html_e( 'What this page does not answer', 'das-v4' ); ?></h2>
		<p style="max-width:760px">
			<?php esc_html_e( 'How many people visited the site, which town they were in, how long they stayed and which advert brought them are questions this site cannot answer honestly on its own - they need Google Analytics, which is free and is also what Google Ads reads when you want to advertise to the people who already looked at your vehicles. The column on the right is the old plugin\'s counter, frozen in August 2026: useful as history, not comparable with the columns beside it.', 'das-v4' ); ?>
		</p>
	</div>
	<?php
}
