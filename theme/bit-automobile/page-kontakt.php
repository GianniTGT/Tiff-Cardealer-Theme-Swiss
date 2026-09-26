<?php
/** Kontakt — Visitenkarte als Block, Formular, Fakten. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

$state   = isset( $_GET['gesendet'] ) ? sanitize_key( wp_unslash( $_GET['gesendet'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
$car_id  = isset( $_GET['fz'] ) ? absint( $_GET['fz'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
$topic   = isset( $_GET['anliegen'] ) ? sanitize_key( wp_unslash( $_GET['anliegen'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
$car     = ( $car_id && 'fahrzeug' === get_post_type( $car_id ) ) ? get_post( $car_id ) : null;
$prefill = '';
if ( $car ) {
	$prefill = 'Ich interessiere mich für: ' . get_the_title( $car ) . '. ';
} elseif ( 'schaetzung' === $topic ) {
	$prefill = 'Ich möchte mein Fahrzeug schätzen lassen: Marke, Modell, Jahrgang, Kilometer … ';
} elseif ( 'ankauf' === $topic ) {
	$prefill = 'Ich möchte mein Fahrzeug verkaufen: Marke, Modell, Jahrgang, Kilometer … ';
}
$notes = array(
	'ok'    => array( 'form-note--ok', 'Danke – Ihre Nachricht ist angekommen. Wir melden uns, meistens noch am selben Tag.' ),
	'fehlt' => array( 'form-note--err', 'Bitte Name und Telefon oder E-Mail angeben – sonst können wir nicht zurückrufen.' ),
	'fehler' => array( 'form-note--err', 'Das hat nicht geklappt. Bitte rufen Sie uns an: ' . bit_info( 'phone1' ) . '.' ),
);
?>
<div class="intro">
	<?php bit_kicker( 'Kontakt' ); ?>
	<h1 class="t-hero t-hero--wide">So erreichen Sie uns</h1>
	<p class="lead lead--intro">Rufen Sie an, schreiben Sie, oder kommen Sie vorbei. Am schnellsten geht das Telefon.</p>
</div>
<?php bit_flow(); ?>

<section class="sec sec--top">
	<div class="vcard">
		<span class="cut" aria-hidden="true"></span>
		<div class="cols">
			<div class="l">
				<img src="<?php echo esc_url( bit_asset( 'logo/logo-blue.png' ) ); ?>" alt="<?php echo esc_attr( bit_info( 'brand' ) ); ?>" width="186" height="50">
				<p><?php echo esc_html( bit_info( 'street' ) ); ?><br><?php echo esc_html( bit_info( 'zip' ) . ' ' . bit_info( 'city' ) ); ?></p>
			</div>
			<div class="r">
				<div>
					<p class="ttl">Verkauf und Beratung</p>
					<p class="hrs"><?php echo esc_html( bit_info( 'hours_week' ) . ' · ' . bit_info( 'hours_sat' ) ); ?></p>
				</div>
				<p class="ways">
					<a href="mailto:<?php echo esc_attr( bit_info( 'email' ) ); ?>"><?php echo esc_html( bit_info( 'email' ) ); ?></a><br>
					<?php echo bit_tel_link( 'phone1' ); // phpcs:ignore ?><br>
					<?php echo bit_tel_link( 'phone2' ); // phpcs:ignore ?>
				</p>
			</div>
		</div>
	</div>
</section>

<section class="sec" id="formular">
	<div class="split split--top">
		<div>
			<h2 class="t-sec">Schreiben Sie uns</h2>
			<form class="form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="bit_lead">
				<input type="hidden" name="fz" value="<?php echo esc_attr( $car ? $car->ID : 0 ); ?>">
				<input type="hidden" name="t" value="<?php echo esc_attr( time() ); ?>">
				<?php wp_nonce_field( 'bit_lead', 'bit_lead_nonce' ); ?>
				<div class="hp" aria-hidden="true"><label>Firma <input type="text" name="firma" tabindex="-1" autocomplete="off"></label></div>
				<label class="bit-field"><span>Name</span><input class="ctl" type="text" name="name" placeholder="Vor- und Nachname" autocomplete="name" required maxlength="120"></label>
				<label class="bit-field"><span>Telefon</span><input class="ctl" type="tel" name="tel" placeholder="079 000 00 00" autocomplete="tel" maxlength="40"></label>
				<label class="bit-field full"><span>E-Mail</span><input class="ctl" type="email" name="mail" placeholder="name@beispiel.ch" autocomplete="email" maxlength="160"></label>
				<label class="bit-field full"><span>Nachricht</span><textarea class="ctl" name="msg" placeholder="Um welches Fahrzeug geht es?" maxlength="4000"><?php echo esc_textarea( $prefill ); ?></textarea></label>
				<label class="consent full"><input type="checkbox" name="ok" value="1" required> <span>Ich bin einverstanden, dass BIT Automobile meine Angaben speichert, um mich zu kontaktieren. Mehr dazu im <a href="<?php echo esc_url( bit_page_url( 'datenschutz' ) ); ?>">Datenschutz</a>.</span></label>
				<div class="full"><button class="bit-btn bit-btn--blue" type="submit">Nachricht senden</button></div>
			</form>
			<?php if ( isset( $notes[ $state ] ) ) : ?>
				<p class="form-note <?php echo esc_attr( $notes[ $state ][0] ); ?>" role="status"><?php echo esc_html( $notes[ $state ][1] ); ?></p>
			<?php else : ?>
				<p class="form-note">Wir fragen nie nach AHV-Nummer, Geburtsdatum oder Bankdaten.</p>
			<?php endif; ?>
		</div>
		<?php bit_facts(); ?>
	</div>
</section>
<?php
get_footer();
