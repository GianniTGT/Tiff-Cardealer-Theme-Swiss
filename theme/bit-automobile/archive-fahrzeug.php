<?php
/**
 * Fahrzeuge — Liste mit Sofort-Suche, Marken-Chips und drei Reglern
 * (Preis, Kilometer, Jahrgang), wie im Entwurf. Ohne JavaScript werden
 * alle Fahrzeuge gezeigt; die Filter wirken dann ueber die Adresse (?marke=…).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

$brands = bit_active_brands();
$total  = (int) $GLOBALS['wp_query']->post_count;

// Reglergrenzen aus dem echten Bestand, gerundet wie im Entwurf.
$max_price = 20000;
$max_km    = 20000;
$min_year  = (int) wp_date( 'Y' );
$max_year  = 0;
while ( have_posts() ) {
	the_post();
	$max_price = max( $max_price, (int) bit_meta( get_the_ID(), 'price' ) );
	$max_km    = max( $max_km, (int) bit_meta( get_the_ID(), 'km' ) );
	$y         = (int) bit_meta( get_the_ID(), 'year' );
	if ( $y ) {
		$min_year = min( $min_year, $y );
		$max_year = max( $max_year, $y );
	}
}
rewind_posts();
$max_price = (int) ( ceil( $max_price / 10000 ) * 10000 );
$max_km    = (int) ( ceil( $max_km / 10000 ) * 10000 );
$max_year  = $max_year ? $max_year : (int) wp_date( 'Y' );
$active    = isset( $_GET['marke'] ) ? sanitize_title( wp_unslash( $_GET['marke'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
?>

<div class="intro">
	<?php bit_kicker( 'Fahrzeuge' ); ?>
	<h1 class="t-hero t-hero--wide">Occasionen aus Oberwangen</h1>
	<p class="lead lead--intro">Alles geprüft, aufbereitet und ab MFK. Was Sie hier sehen, steht auf dem Platz an der Herzwilstrasse.</p>
</div>
<?php bit_flow(); ?>

<section class="sec" data-finder
	data-total="<?php echo esc_attr( $total ); ?>"
	data-max-price="<?php echo esc_attr( $max_price ); ?>"
	data-max-km="<?php echo esc_attr( $max_km ); ?>"
	data-min-year="<?php echo esc_attr( $min_year ); ?>">

	<?php if ( $total ) : ?>
	<form class="find" role="search" onsubmit="return false">
		<label class="bit-search">
			<span class="sr-only">Fahrzeuge durchsuchen</span>
			<?php echo bit_icon( 'search', 18 ); // phpcs:ignore ?>
			<input type="search" data-q placeholder="Marke, Modell, Getriebe … z.B. Porsche" autocomplete="off">
		</label>
		<button type="reset" class="bit-btn bit-btn--ghost" data-reset>Zurücksetzen</button>
	</form>

	<?php if ( count( $brands ) > 1 ) : ?>
	<div class="chips" role="group" aria-label="Marke">
		<button type="button" class="bit-chip" data-brand="" aria-pressed="<?php echo $active ? 'false' : 'true'; ?>">Alle</button>
		<?php foreach ( $brands as $b ) : ?>
			<button type="button" class="bit-chip" data-brand="<?php echo esc_attr( $b->slug ); ?>" aria-pressed="<?php echo $active === $b->slug ? 'true' : 'false'; ?>"><?php echo esc_html( $b->name ); ?></button>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>

	<div class="ranges">
		<label class="range">
			<span class="bit-label">Preis bis</span>
			<b data-out="price">ohne Grenze</b>
			<input type="range" data-range="price" min="<?php echo esc_attr( min( 20000, $max_price ) ); ?>" max="<?php echo esc_attr( $max_price ); ?>" step="5000" value="<?php echo esc_attr( $max_price ); ?>">
		</label>
		<label class="range">
			<span class="bit-label">Kilometer bis</span>
			<b data-out="km">ohne Grenze</b>
			<input type="range" data-range="km" min="<?php echo esc_attr( min( 20000, $max_km ) ); ?>" max="<?php echo esc_attr( $max_km ); ?>" step="5000" value="<?php echo esc_attr( $max_km ); ?>">
		</label>
		<label class="range">
			<span class="bit-label">Jahrgang ab</span>
			<b data-out="year"><?php echo esc_html( $min_year ); ?></b>
			<input type="range" data-range="year" min="<?php echo esc_attr( $min_year ); ?>" max="<?php echo esc_attr( $max_year ); ?>" step="1" value="<?php echo esc_attr( $min_year ); ?>">
		</label>
	</div>
	<?php endif; ?>

	<?php if ( bit_has_demo_vehicles() ) : ?>
		<p class="demo-note"><b>Testdaten</b>Die Fahrzeuge mit «Demo» sind erfundene Beispiele zum Testen (AutoScout24-Anbindung). Sie werden vor dem Livegang gelöscht.</p>
	<?php endif; ?>

	<p class="count-label" data-count-label aria-live="polite"><?php echo esc_html( 1 === $total ? '1 Fahrzeug auf dem Platz' : $total . ' Fahrzeuge auf dem Platz' ); ?></p>

	<div class="grid-veh grid-veh--gap" data-grid>
		<?php
		while ( have_posts() ) {
			the_post();
			bit_vehicle_card( get_the_ID() );
		}
		?>
	</div>

	<div class="empty" data-empty<?php echo $total ? ' hidden' : ''; ?>>
		<p><?php echo $total ? 'Kein Fahrzeug passt dazu.' : 'Gerade ist der Platz leer – rufen Sie an, wir sagen Ihnen, was diese Woche kommt.'; ?></p>
		<div class="actions">
			<?php if ( $total ) : ?><button type="button" class="bit-btn bit-btn--ghost" data-reset>Alle Fahrzeuge zeigen</button><?php endif; ?>
			<a class="bit-btn bit-btn--blue" href="tel:<?php echo esc_attr( bit_tel( bit_info( 'phone1' ) ) ); ?>"><?php echo esc_html( bit_info( 'phone1' ) ); ?></a>
		</div>
	</div>
</section>

<?php
get_footer();
