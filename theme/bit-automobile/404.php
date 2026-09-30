<?php
/** Seite nicht gefunden. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
?>
<div class="intro">
	<?php bit_kicker( 'Fehler 404' ); ?>
	<h1 class="t-hero t-hero--wide">Diese Seite gibt es nicht.</h1>
	<p class="lead lead--intro">Vielleicht ist das Fahrzeug schon verkauft. Schauen Sie, was gerade auf dem Platz steht – oder rufen Sie an.</p>
	<div class="actions">
		<a class="bit-btn bit-btn--blue" href="<?php echo esc_url( bit_page_url( 'fahrzeuge' ) ); ?>">Fahrzeuge ansehen</a>
		<a class="bit-btn bit-btn--ghost" href="tel:<?php echo esc_attr( bit_tel( bit_info( 'phone1' ) ) ); ?>"><?php echo esc_html( bit_info( 'phone1' ) ); ?></a>
	</div>
</div>
<div style="height:var(--band)"></div>
<?php
get_footer();
