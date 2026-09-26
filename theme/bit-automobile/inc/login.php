<?php
/**
 * Login-Seite im BIT-Design: dunkel, Logo, blaue Knoepfe, Radius 16px.
 * Adresse bleibt /wp-login.php (bzw. /wp-admin/).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'login_enqueue_scripts',
	function () {
		$logo = esc_url( bit_asset( 'logo/logo-alpha.png' ) );
		$font = esc_url( bit_asset( 'fonts/archivo-latin.woff2' ) );
		?>
		<style>
			@font-face{font-family:'Archivo';font-weight:400 900;font-display:swap;src:url(<?php echo $font; // phpcs:ignore ?>) format('woff2')}
			body.login{background:#0C0E10;color:#EDEFF1;font-family:'Archivo',system-ui,sans-serif}
			body.login::before{content:"";position:fixed;left:0;right:0;top:0;height:3px;
				background:linear-gradient(90deg,transparent,#3FA9A0,#79D8CB,#4A8FD1,transparent)}
			#login{width:360px;padding-top:9vh}
			#login h1 a{background:#fff;-webkit-mask:url(<?php echo $logo; // phpcs:ignore ?>) center/contain no-repeat;mask:url(<?php echo $logo; // phpcs:ignore ?>) center/contain no-repeat;
				background-image:none;width:220px;height:60px;margin:0 auto 26px}
			body.login #loginform,body.login #lostpasswordform,body.login #registerform{background:#141719!important;border:1px solid rgba(255,255,255,.10)!important;border-radius:16px;box-shadow:0 30px 60px rgba(0,0,0,.6)!important;padding:26px 24px}
			.login label{color:#A8B0B7;font-size:13px;font-weight:600}
			body.login input[type=text],body.login input[type=password],body.login input[type=email]{background:#1B1F22!important;border:1.5px solid rgba(255,255,255,.10)!important;border-radius:16px!important;color:#fff!important;font-size:15px;padding:10px 14px;min-height:44px;box-shadow:none!important}
			.login input[type=text]:focus,.login input[type=password]:focus{border-color:#4A8FD1;box-shadow:none;background:#232830}
			body.login .button.wp-hide-pw{color:#79838B!important;height:44px;background:transparent!important;border:0!important}
			.login .button.wp-hide-pw:focus{box-shadow:none;border-color:transparent}
			body.login .button-primary{background:linear-gradient(152deg,#4A8FD1,#2B5672)!important;border:0!important;border-radius:16px!important;min-height:44px;padding:0 22px!important;font-weight:700;font-size:15px;text-shadow:none;box-shadow:none}
			body.login .button-primary:hover,body.login .button-primary:focus{background:linear-gradient(152deg,#5a9bd8,#2B5672);box-shadow:0 12px 34px rgba(74,143,209,.30)}
			.login #nav,.login #backtoblog{text-align:center}
			.login #nav a,.login #backtoblog a,.login .privacy-policy-link{color:#A8B0B7}
			.login #nav a:hover,.login #backtoblog a:hover{color:#fff}
			.login .message,.login .notice,.login #login_error{background:#141719;border-left-color:#4A8FD1;border-radius:10px;color:#EDEFF1;box-shadow:none}
			.login #login_error{border-left-color:#E05A55}
			.login .forgetmenot label{color:#A8B0B7}
			.login input[type=checkbox]{background:#1B1F22;border-color:rgba(255,255,255,.2);border-radius:4px}
			.language-switcher{display:none}
		</style>
		<?php
	}
);

add_filter( 'login_headerurl', fn() => home_url( '/' ) );
add_filter( 'login_headertext', fn() => bit_info( 'brand' ) );
add_filter( 'login_display_language_dropdown', '__return_false' );

/** Nach dem Login direkt zur Uebersicht (nicht zum Profil). */
add_filter(
	'login_redirect',
	function ( $to, $requested, $user ) {
		if ( $user instanceof WP_User && ! $requested ) {
			return admin_url();
		}
		return $to;
	},
	10,
	3
);
