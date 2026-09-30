<?php
/** Impressum — Text aus der Seite (im Admin bearbeitbar), daneben die Registerdaten. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
the_post();

$register = array(
	array( 'UID', bit_info( 'uid' ) ),
	array( 'MWST', bit_info( 'vat' ) ),
	array( 'Eintrag', bit_info( 'hr_date' ) ),
	array( 'Statuten', bit_info( 'statutes' ) ),
	array( 'Sitz', bit_info( 'seat' ) ),
	array( 'Kapital', bit_info( 'capital' ), true ),
	array( 'Aktien', bit_info( 'shares' ), true ),
	array( 'Zeichnungsberechtigt', bit_info( 'signatory' ), true ),
);
// Leere Werte weg; bei ungerader Zahl halber Felder wird das letzte breit (keine leere Zelle).
$register = array_values( array_filter( $register, fn( $r ) => '' !== (string) $r[1] ) );
$halves   = array_keys( array_filter( $register, fn( $r ) => empty( $r[2] ) ) );
if ( count( $halves ) % 2 ) {
	$register[ end( $halves ) ][2] = true;
}
?>
<div class="intro">
	<?php bit_kicker( 'Impressum' ); ?>
	<h1 class="t-page">Wer diese Seite betreibt</h1>
</div>
<?php bit_flow(); ?>
<section class="sec">
	<div class="legal">
		<div class="text"><?php the_content(); ?></div>
		<aside class="side">
			<?php bit_kicker( 'Handelsregister' ); ?>
			<h3><?php echo esc_html( bit_info( 'legal' ) ); ?></h3>
			<p>Handelnd unter <?php echo esc_html( bit_info( 'brand' ) ); ?>. Eingetragen im Handelsregister des Kantons Bern.</p>
			<div class="bit-kv">
				<?php foreach ( $register as $row ) : ?>
					<div<?php echo ! empty( $row[2] ) ? ' class="wide"' : ''; ?>><span class="k"><?php echo esc_html( $row[0] ); ?></span><span class="v"><?php echo esc_html( $row[1] ); ?></span></div>
				<?php endforeach; ?>
			</div>
		</aside>
	</div>
</section>
<?php
get_footer();
