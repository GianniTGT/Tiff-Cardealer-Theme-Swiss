<?php
/** Datenschutz — Text aus der Seite (im Admin bearbeitbar), daneben «Entschieden». */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
the_post();
?>
<div class="intro">
	<?php bit_kicker( 'Datenschutz' ); ?>
	<h1 class="t-page">Was wir speichern – und was nicht</h1>
</div>
<?php bit_flow(); ?>
<section class="sec">
	<div class="legal">
		<div class="text"><?php the_content(); ?></div>
		<aside class="side">
			<?php bit_kicker( 'Entschieden' ); ?>
			<h3>Die Daten bleiben in der Schweiz</h3>
			<p>Hosting und Datenhaltung erfolgen in der Schweiz, nach dem Schweizer Datenschutzgesetz (DSG). Es gehen keine Kundendaten in ein Land ausserhalb der Schweiz.</p>
			<p>Anfragen über das Formular löschen wir automatisch nach zwölf Monaten.</p>
		</aside>
	</div>
</section>
<?php
get_footer();
