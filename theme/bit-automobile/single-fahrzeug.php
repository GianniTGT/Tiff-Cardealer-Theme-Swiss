<?php
/**
 * Ein Fahrzeug — Galerie, Preis, Anruf-Kasten, Daten, Leasing-Rechner,
 * drei aehnliche Fahrzeuge. Wie im Entwurf.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
the_post();

$v    = bit_vehicle( get_the_ID() );
$tel1 = bit_tel( bit_info( 'phone1' ) );
$sold = 'verkauft' === $v['status'];

$data = array(
	array( 'Jahrgang', $v['year'] ),
	array( '1. Inverkehrsetzung', $v['first_reg'] ),
	array( 'Kilometer', $v['km_txt'] ),
	array( 'Leistung', $v['power'] ),
	array( 'Getriebe', $v['gearbox'] ),
	array( 'Antrieb', $v['drive'] ),
	array( 'Treibstoff', $v['fuel'] ),
	array( 'Verbrauch', $v['consumption'] ),
	array( 'Türen / Plätze', ( $v['doors'] || $v['seats'] ) ? trim( $v['doors'] . ' / ' . $v['seats'], ' /' ) : '' ),
	array( 'Farbe', $v['colour'] ),
	array( 'Stammnummer', $v['stamm'], true ),
	array( 'Prüfung', trim( ( $v['mfk'] ? 'Ab MFK' : '' ) . ( $v['mfk'] && $v['warranty'] ? ', ' : '' ) . $v['warranty'] ), true ),
);
$data = array_values( array_filter( $data, fn( $row ) => '' !== (string) $row[1] ) );

// Aehnlich: gleiche Marke zuerst, dann der Rest.
$similar = array();
$pool    = bit_get_vehicles( array( 'post__not_in' => array( get_the_ID() ) ) );
foreach ( $pool as $p ) {
	if ( $v['brand_slug'] && has_term( $v['brand_slug'], 'marke', $p ) ) {
		$similar[] = $p->ID;
	}
}
foreach ( $pool as $p ) {
	if ( ! in_array( $p->ID, $similar, true ) ) {
		$similar[] = $p->ID;
	}
}
$similar = array_slice( $similar, 0, 3 );
?>

<div class="wrap" style="padding-top:26px">
	<a class="back" href="<?php echo esc_url( bit_page_url( 'fahrzeuge' ) ); ?>">← Alle Fahrzeuge</a>
</div>

<section class="sec" style="padding-top:22px">
	<div class="detail" data-gallery>
		<div>
			<?php echo bit_vehicle_image( $v, 'bit-large', 0, array( 'class' => 'main-photo', 'data-main' => '1', 'loading' => 'eager' ) ); // phpcs:ignore ?>
			<?php if ( count( $v['images'] ) > 1 ) : ?>
			<div class="thumbs">
				<?php foreach ( $v['images'] as $i => $att ) : ?>
					<button type="button" aria-pressed="<?php echo 0 === $i ? 'true' : 'false'; ?>" aria-label="Bild <?php echo esc_attr( $i + 1 ); ?>"
						data-full="<?php echo esc_url( wp_get_attachment_image_url( $att, 'bit-large' ) ); ?>">
						<?php echo wp_get_attachment_image( $att, 'bit-card', false, array( 'alt' => '' ) ); ?>
					</button>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
		</div>
		<div>
			<?php if ( $v['demo'] ) : ?><p class="demo-note" style="margin:0 0 18px"><b>Testdaten</b>Erfundenes Beispiel zum Testen, kein echtes Angebot.</p><?php endif; ?>
			<?php bit_kicker( $sold ? 'Verkauft' : ( ( $v['mfk'] ? 'Ab MFK · ' : '' ) . 'Occasion' ) ); ?>
			<h1 class="t-detail"><?php the_title(); ?></h1>
			<?php if ( $v['subtitle'] ) : ?><p class="sub"><?php echo esc_html( $v['subtitle'] ); ?></p><?php endif; ?>
			<p class="price<?php echo $sold ? ' status-sold' : ''; ?>"><?php echo esc_html( $sold ? 'Verkauft' : $v['price_txt'] ); ?></p>
			<div class="aside">
				<p><?php echo $sold ? 'Dieses Fahrzeug ist verkauft. Rufen Sie an – vielleicht steht ein ähnliches auf dem Platz oder kommt bald.' : ( 'reserviert' === $v['status'] ? 'Dieses Fahrzeug ist reserviert. Rufen Sie an, wir sagen Ihnen, ob es wieder frei wird.' : 'Dieses Fahrzeug steht in Oberwangen. Rufen Sie an, dann wissen Sie in zwei Minuten, ob es passt.' ); ?></p>
				<div class="actions">
					<a class="bit-btn bit-btn--blue" href="tel:<?php echo esc_attr( $tel1 ); ?>"><?php echo esc_html( bit_info( 'phone1' ) ); ?></a>
					<a class="bit-btn bit-btn--ghost" href="<?php echo esc_url( add_query_arg( 'fz', get_the_ID(), bit_page_url( 'kontakt' ) ) ); ?>#formular">Termin anfragen</a>
				</div>
			</div>
			<?php if ( $data ) : ?>
			<h2 class="t-h2">Daten</h2>
			<div class="bit-kv">
				<?php foreach ( $data as $row ) : ?>
					<div<?php echo ! empty( $row[2] ) ? ' class="wide"' : ''; ?>><span class="k"><?php echo esc_html( $row[0] ); ?></span><span class="v"><?php echo esc_html( $row[1] ); ?></span></div>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
			<?php if ( trim( get_the_content() ) ) : ?>
			<h2 class="t-h2">Beschreibung</h2>
			<div class="desc"><?php the_content(); ?></div>
			<?php endif; ?>
		</div>
	</div>
</section>

<?php if ( ! $sold && (int) $v['price'] > 0 ) : ?>
<section class="sec sec--flat">
	<div class="panel leasing" data-leasing data-price="<?php echo esc_attr( (int) $v['price'] ); ?>" data-rate="<?php echo esc_attr( (float) str_replace( ',', '.', bit_info( 'leasing_rate' ) ) ); ?>">
		<div>
			<?php bit_kicker( 'Leasing, gerechnet' ); ?>
			<h2 class="t-sub">Was es pro Monat macht.</h2>
			<p class="muted" style="margin-top:12px;max-width:44ch">Eine Rechnung, keine Offerte. Der Zinssatz ist mit <?php echo esc_html( str_replace( '.', ',', bit_info( 'leasing_rate' ) ) ); ?>% angenommen; die verbindliche Zahl kommt von der Leasinggesellschaft.</p>
			<label class="range">
				<span class="bit-label">Laufzeit</span>
				<b data-out="months">48 Monate</b>
				<input type="range" data-in="months" min="12" max="60" step="12" value="48">
			</label>
			<label class="range">
				<span class="bit-label">Anzahlung</span>
				<b data-out="down">10% · <?php echo esc_html( bit_chf( $v['price'] * 0.1 ) ); ?></b>
				<input type="range" data-in="down" min="0" max="40" step="5" value="10">
			</label>
		</div>
		<div>
			<div class="bit-calc">
				<div class="ln"><span>Fahrzeugpreis</span><b><?php echo esc_html( bit_chf( $v['price'] ) ); ?></b></div>
				<div class="ln"><span>Anzahlung</span><b data-out="downchf">– <?php echo esc_html( bit_chf( $v['price'] * 0.1 ) ); ?></b></div>
				<div class="ln ln--sum"><span>Finanziert</span><b data-out="principal"><?php echo esc_html( bit_chf( $v['price'] * 0.9 ) ); ?></b></div>
				<div class="ln ln--win"><span>Rate pro Monat</span><b data-out="rate"><?php echo esc_html( bit_chf( round( bit_leasing_rate( (int) $v['price'] ) ) ) ); ?></b></div>
			</div>
			<p class="fine">Ohne Versicherung, Vollamortisation, Restwert nicht berücksichtigt. Beispielrechnung, unverbindlich.</p>
		</div>
	</div>
</section>
<?php endif; ?>

<?php if ( $similar ) : ?>
<section class="sec sec--flat">
	<div class="sec-head sec-head--stack" style="margin-bottom:26px">
		<?php bit_kicker( 'Ähnlich' ); ?>
		<h2 class="t-sub"><?php echo 3 === count( $similar ) ? 'Drei, die auch passen könnten' : 'Die auch passen könnten'; ?></h2>
	</div>
	<div class="grid-veh">
		<?php foreach ( $similar as $sid ) { bit_vehicle_card( $sid ); } ?>
	</div>
</section>
<?php endif; ?>

<?php
get_footer();
