<?php
/**
 * Eine Dienstleistung (Unterseite von /dienstleistungen/): Bild-Kopf, Text aus
 * dem Admin, Frage-Kasten mit Telefon und Anfrage, die anderen Dienstleistungen.
 * Wird ueber den Filter in inc/setup.php fuer alle Kinder von «Dienstleistungen» genommen.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
the_post();

$slug    = bit_current_service();
$service = bit_services()[ $slug ];
$image   = has_post_thumbnail() ? get_the_post_thumbnail_url( null, 'bit-large' ) : bit_asset( $service[2] );
$topic   = 'fahrzeugbewertung' === $slug ? 'schaetzung' : ( 'an-und-verkauf' === $slug ? 'ankauf' : $slug );
?>
<section class="hero hero--page">
	<img src="<?php echo esc_url( $image ); ?>" alt="">
	<div class="shade"></div>
	<div class="in">
		<p class="bit-kicker"><a href="<?php echo esc_url( bit_page_url( 'dienstleistungen' ) ); ?>">Dienstleistungen</a></p>
		<h1 class="t-hero t-hero--wide"><?php the_title(); ?></h1>
		<p class="lead lead--hero"><?php echo esc_html( $service[1] ); ?></p>
	</div>
</section>
<?php bit_flow( true ); ?>

<section class="sec">
	<div class="split split--top split--even">
		<div class="text prose"><?php the_content(); ?></div>
		<aside class="panel ask">
			<?php bit_kicker( 'Haben Sie noch eine Frage?' ); ?>
			<h2 class="t-sub">Unser Team steht Ihnen gerne zur Verfügung.</h2>
			<p class="muted"><?php echo esc_html( bit_info( 'hours_week' ) . ' · ' . bit_info( 'hours_sat' ) ); ?></p>
			<div class="actions actions--even">
				<a class="bit-btn bit-btn--blue" href="tel:<?php echo esc_attr( bit_tel( bit_info( 'phone1' ) ) ); ?>"><?php echo esc_html( bit_info( 'phone1' ) ); ?></a>
				<a class="bit-btn bit-btn--ghost" href="<?php echo esc_url( add_query_arg( 'anliegen', $topic, bit_page_url( 'kontakt' ) ) ); ?>#formular"><?php echo 'fahrzeugbewertung' === $slug ? 'Bewertung anfragen' : 'Anfrage schreiben'; ?></a>
			</div>
		</aside>
	</div>
</section>

<section class="sec sec--flat">
	<div class="sec-head sec-head--stack">
		<?php bit_kicker( 'Alles unter einem Dach' ); ?>
		<h2 class="t-sec">Weitere Dienstleistungen</h2>
	</div>
	<?php bit_service_cards( $slug ); ?>
</section>
<?php
get_footer();
