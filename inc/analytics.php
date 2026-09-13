<?php
/**
 * Google Analytics 4, and the notice that has to come with it.
 *
 * The Measurement ID lives in an option rather than in the theme, because the same
 * theme is sold to the next dealership and their property is not this one's (§5).
 * Nothing is emitted until an ID that actually looks like one is saved, so a fresh
 * install ships no tag, no cookie and no notice.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/** The stored Measurement ID, or '' if none is set or it is malformed. */
function das_ga4_id() {
	$id = trim( (string) get_option( 'das_ga4_id', '' ) );
	return preg_match( '/^G-[A-Z0-9]{4,20}$/i', $id ) ? strtoupper( $id ) : '';
}

/**
 * Staff are excluded, for the same reason the vehicle counter excludes them: a
 * salesperson refreshing a listing all day is not a customer, and a report that
 * counts him is a report that plans advertising around his own browsing.
 */
add_action( 'wp_head', function () {
	$id = das_ga4_id();
	if ( '' === $id || is_user_logged_in() ) { return; }
	?>
<!-- Google Analytics 4 -->
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo rawurlencode( $id ); ?>"></script>
<script>
	window.dataLayer = window.dataLayer || [];
	function gtag(){ dataLayer.push( arguments ); }
	gtag( 'js', new Date() );
	gtag( 'config', '<?php echo esc_js( $id ); ?>' );
</script>
	<?php
}, 5 );

/**
 * A notice, not a consent gate.
 *
 * This dealership sells cars in Anchorage to people who walk onto the lot, so the
 * proportionate thing is to tell visitors plainly and link the policy - not to put a
 * blocking European-style consent wall in front of a customer looking at a truck. If
 * the site ever needs EU-grade consent, this is where Google Consent Mode goes, and
 * the tag above becomes conditional on it.
 *
 * Nothing renders until analytics is actually switched on: no ID, no cookies, nothing
 * to disclose.
 */
add_action( 'wp_footer', function () {
	if ( '' === das_ga4_id() || is_user_logged_in() ) { return; }
	$policy = get_privacy_policy_url();
	?>
<div class="cookie-note" id="cookieNote" hidden>
	<p>
		We use cookies to see how this site is used, so we can make it better.
		<?php if ( $policy ) : ?><a href="<?php echo esc_url( $policy ); ?>">Privacy policy</a><?php endif; ?>
	</p>
	<button type="button" id="cookieNoteOk" class="btn btn-light">Got it</button>
</div>
<script>
(function () {
	var key = 'dasCookieNote';
	var box = document.getElementById('cookieNote');
	var ok  = document.getElementById('cookieNoteOk');
	if ( ! box || ! ok ) { return; }
	var seen = false;
	try { seen = window.localStorage.getItem( key ) === '1'; } catch ( e ) { seen = false; }
	if ( seen ) { return; }
	box.hidden = false;
	ok.addEventListener( 'click', function () {
		box.hidden = true;
		try { window.localStorage.setItem( key, '1' ); } catch ( e ) {}
	} );
})();
</script>
	<?php
} );

/**
 * The Measurement ID is set on the Traffic page, because that is the screen somebody
 * is already looking at when they wonder why it cannot tell them where visitors came
 * from. Viewing traffic needs `edit_posts`; changing what the site loads for every
 * visitor needs `manage_options`.
 */
add_action( 'das_traffic_top', function () {
	$can_edit = current_user_can( 'manage_options' );
	$saved    = false;

	if ( $can_edit && isset( $_POST['das_ga4_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['das_ga4_nonce'] ) ), 'das_save_ga4' ) ) {
		$value = isset( $_POST['das_ga4_id'] ) ? strtoupper( trim( sanitize_text_field( wp_unslash( $_POST['das_ga4_id'] ) ) ) ) : '';
		if ( '' === $value || preg_match( '/^G-[A-Z0-9]{4,20}$/', $value ) ) {
			update_option( 'das_ga4_id', $value, false );
			$saved = true;
		} else {
			echo '<div class="notice notice-error inline"><p>' . esc_html__( 'That does not look like a Measurement ID. It starts with G- followed by letters and digits.', 'das-v4' ) . '</p></div>';
		}
	}

	$id = das_ga4_id();
	if ( $saved ) {
		echo '<div class="notice notice-success inline"><p>' . ( '' === $id
			? esc_html__( 'Google Analytics switched off.', 'das-v4' )
			: esc_html__( 'Saved. Google Analytics is now collecting on this site.', 'das-v4' ) ) . '</p></div>';
	}
	?>
	<div class="card" style="background:#fff;border:1px solid #dcdcde;border-radius:6px;padding:14px 16px;margin:14px 0 20px;max-width:760px">
		<h2 style="margin:0 0 8px;font-size:15px"><?php esc_html_e( 'Google Analytics', 'das-v4' ); ?></h2>
		<?php if ( '' === $id ) : ?>
			<p style="margin:0 0 10px"><?php esc_html_e( 'Not connected. Visitors, the towns they are in, how long they stay and which advert brought them are answered there, not here.', 'das-v4' ); ?></p>
		<?php else : ?>
			<p style="margin:0 0 10px">
				<?php
				printf(
					/* translators: %s: Google Analytics Measurement ID */
					esc_html__( 'Connected as %s. Signed-in staff are never counted.', 'das-v4' ),
					'<code>' . esc_html( $id ) . '</code>'
				);
				?>
			</p>
		<?php endif; ?>
		<?php if ( $can_edit ) : ?>
			<form method="post" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
				<?php wp_nonce_field( 'das_save_ga4', 'das_ga4_nonce' ); ?>
				<label class="screen-reader-text" for="das_ga4_id"><?php esc_html_e( 'Measurement ID', 'das-v4' ); ?></label>
				<input type="text" id="das_ga4_id" name="das_ga4_id" value="<?php echo esc_attr( $id ); ?>" placeholder="G-XXXXXXXXXX" class="regular-text" style="max-width:220px">
				<button type="submit" class="button button-primary"><?php esc_html_e( 'Save', 'das-v4' ); ?></button>
				<span class="description"><?php esc_html_e( 'Leave empty to switch analytics off.', 'das-v4' ); ?></span>
			</form>
		<?php endif; ?>
	</div>
	<?php
} );
