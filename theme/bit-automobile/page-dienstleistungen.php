<?php
/** Dienstleistungen — vier Karten (je eine eigene Seite), Band «Auto verkaufen, ohne Inserat». */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
?>
<div class="intro">
	<?php bit_kicker( 'Dienstleistungen' ); ?>
	<h1 class="t-hero t-hero--wide">Vier Dinge, alle unter einem Dach</h1>
	<p class="lead lead--intro">Vom An- und Verkauf über die Aufbereitung bis zur eigenen Werkstatt – und wenn Sie wissen wollen, was Ihr Auto wert ist, bewerten wir es.</p>
</div>
<?php bit_flow(); ?>

<section class="sec">
	<?php bit_service_cards(); ?>
</section>

<section class="sec sec--flat">
	<div class="banner">
		<img src="<?php echo esc_url( bit_asset( 'img/showroom-innen.jpg' ) ); ?>" alt="" loading="lazy">
		<div class="shade"></div>
		<div class="in">
			<h2 class="t-sec">Auto verkaufen, ohne Inserat.</h2>
			<p class="lead">Kein Fotografieren, kein Warten, keine Besichtigungen am Sonntagabend. Ein Termin, ein Preis, erledigt.</p>
			<div class="actions">
				<a class="bit-btn bit-btn--blue" href="tel:<?php echo esc_attr( bit_tel( bit_info( 'phone1' ) ) ); ?>"><?php echo esc_html( bit_info( 'phone1' ) ); ?></a>
				<a class="bit-btn bit-btn--ghost" href="<?php echo esc_url( bit_service_url( 'an-und-verkauf' ) ); ?>">So läuft der Ankauf</a>
			</div>
		</div>
	</div>
</section>

<?php
while ( have_posts() ) {
	the_post();
	if ( trim( get_the_content() ) ) {
		echo '<section class="sec sec--flat"><div class="content">';
		the_content();
		echo '</div></section>';
	}
}
get_footer();
