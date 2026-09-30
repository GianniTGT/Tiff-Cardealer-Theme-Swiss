<?php
/** Ueber uns — Schild, Auswahl-Kriterien, Einladung in den Showroom. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
?>
<section class="hero hero--about">
	<img src="<?php echo esc_url( bit_asset( 'img/schild.jpg' ) ); ?>" alt="Das Schild am Gebäude an der Herzwilstrasse">
	<div class="shade"></div>
	<div class="in">
		<?php bit_kicker( 'Über uns' ); ?>
		<h1 class="t-hero">Das Schild steht seit <?php echo esc_html( bit_info( 'founded' ) ); ?>.</h1>
	</div>
</section>
<?php bit_flow(); ?>

<section class="sec">
	<div class="sec-head sec-head--stack">
		<h2 class="t-sec">Wie wir die Fahrzeuge auswählen</h2>
		<p class="lead">Wir kaufen weniger, als wir könnten. Vier Dinge entscheiden, ob ein Auto auf den Platz kommt – wenn eines davon nicht stimmt, lassen wir es stehen.</p>
	</div>
	<div class="criteria">
		<?php foreach ( bit_criteria() as $k ) : ?>
			<div>
				<p class="bit-label"><?php echo esc_html( $k[0] ); ?></p>
				<h3><?php echo esc_html( $k[1] ); ?></h3>
				<p class="muted"><?php echo esc_html( $k[2] ); ?></p>
			</div>
		<?php endforeach; ?>
	</div>
</section>

<?php
while ( have_posts() ) {
	the_post();
	if ( trim( get_the_content() ) ) {
		echo '<section class="sec sec--flat"><div class="panel"><div class="content">';
		the_content();
		echo '</div></div></section>';
	}
}
?>

<section class="sec sec--flat">
	<div class="panel">
		<?php bit_kicker( 'Showroom' ); ?>
		<h2 class="t-sec" style="max-width:22ch">Kommen Sie vorbei.</h2>
		<p class="lead" style="max-width:56ch">Unser Showroom in Oberwangen ist offen. Vor Ort stehen die Fahrzeuge, die Sie hier sehen – anschauen, Probe fahren, fragen. Ohne Termin geht auch.</p>
		<div class="actions">
			<a class="bit-btn bit-btn--ghost" href="<?php echo esc_url( bit_page_url( 'fahrzeuge' ) ); ?>">Fahrzeuge ansehen</a>
			<a class="bit-btn bit-btn--blue" href="<?php echo esc_url( bit_info( 'maps_url' ) ); ?>" target="_blank" rel="noopener">Route anzeigen</a>
		</div>
	</div>
</section>
<?php
get_footer();
