<?php
/**
 * Start — wie im Entwurf: Hero, Fakten, drei Fahrzeuge, Ankauf,
 * Showroom, Der Platz (Zaehler), Dienstleistungen.
 * Jedes Foto kommt auf dieser Seite nur einmal vor.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

$cars  = bit_get_vehicles( array( 'posts_per_page' => 3 ) );
$count = bit_vehicle_count();
$tel1  = bit_tel( bit_info( 'phone1' ) );
$tel2  = bit_tel( bit_info( 'phone2' ) );
?>

<section class="hero">
	<img class="breathe" src="<?php echo esc_url( bit_asset( 'img/showroom-innen.jpg' ) ); ?>" alt="Showroom von BIT Automobile in Oberwangen" fetchpriority="high">
	<div class="shade"></div>
	<div class="in">
		<h1 class="t-hero">Autos, die man nicht überall sieht.</h1>
		<p class="lead lead--hero">Grüessech. Wir kaufen und verkaufen Occasionen in der ganzen Schweiz – vom sauberen Kombi bis zum AMG. Geprüft und aufbereitet, bevor sie auf den Platz kommen.</p>
		<div class="actions">
			<a class="bit-btn bit-btn--blue" href="<?php echo esc_url( bit_page_url( 'fahrzeuge' ) ); ?>">Fahrzeuge ansehen</a>
			<a class="bit-btn bit-btn--ghost" href="<?php echo esc_url( bit_service_url( 'fahrzeugbewertung' ) ); ?>">Fahrzeug bewerten lassen</a>
		</div>
	</div>
</section>

<?php bit_flow( true ); ?>

<section class="sec sec--top">
	<?php bit_facts(); ?>
</section>

<?php if ( $cars ) : ?>
<section class="sec">
	<div class="sec-head">
		<div>
			<?php bit_kicker( 'Im Showroom' ); ?>
			<h2 class="t-sec"><?php echo 3 === count( $cars ) ? 'Drei, die gerade hier stehen' : 'Was gerade hier steht'; ?></h2>
			<p class="lead lead--tight">Tippen Sie ein Fahrzeug an – dort stehen alle Daten.</p>
		</div>
		<a class="bit-btn bit-btn--ghost bit-btn--sm" href="<?php echo esc_url( bit_page_url( 'fahrzeuge' ) ); ?>">Alle Fahrzeuge</a>
	</div>
	<div class="grid-veh">
		<?php foreach ( $cars as $car ) { bit_vehicle_card( $car->ID ); } ?>
	</div>
</section>
<?php endif; ?>

<section class="sec<?php echo $cars ? ' sec--flat' : ''; ?>">
	<div class="banner">
		<img src="<?php echo esc_url( bit_asset( 'img/amg-gt-43.jpg' ) ); ?>" alt="" loading="lazy">
		<div class="shade"></div>
		<div class="in">
			<?php bit_kicker( 'Ankauf' ); ?>
			<h2 class="t-sec">Wir kaufen auch Ihres.</h2>
			<p class="lead">Bringen Sie das Fahrzeug vorbei oder rufen Sie an. Wir schauen es an, prüfen den Zustand und machen Ihnen ein Angebot – Barzahlung und Abmeldung inbegriffen.</p>
			<div class="actions">
				<a class="bit-btn" href="tel:<?php echo esc_attr( $tel1 ); ?>"><?php echo esc_html( bit_info( 'phone1' ) ); ?></a>
				<a class="bit-btn bit-btn--ghost" href="<?php echo esc_url( bit_service_url( 'an-und-verkauf' ) ); ?>">So läuft der Ankauf</a>
			</div>
		</div>
	</div>
</section>

<section class="sec sec--flat">
	<div class="split">
		<div class="slant">
			<img src="<?php echo esc_url( bit_asset( 'img/herzwilstrasse-262.jpg' ) ); ?>" alt="Herzwilstrasse 262, Oberwangen" loading="lazy">
			<span aria-hidden="true"></span>
		</div>
		<div>
			<?php bit_kicker( 'Neuer Showroom' ); ?>
			<h2 class="t-sec">Kommen Sie vorbei.</h2>
			<p class="lead">Unser Showroom in Oberwangen ist offen. Vor Ort stehen die Fahrzeuge, die Sie hier sehen – anschauen, Probe fahren, fragen. Ohne Termin geht auch.</p>
			<div class="actions">
				<a class="bit-btn bit-btn--blue" href="<?php echo esc_url( bit_info( 'maps_url' ) ); ?>" target="_blank" rel="noopener">Route anzeigen</a>
				<a class="bit-btn bit-btn--ghost" href="tel:<?php echo esc_attr( $tel2 ); ?>"><?php echo esc_html( bit_info( 'phone2' ) ); ?></a>
			</div>
		</div>
	</div>
</section>

<?php if ( $count ) : ?>
<section class="platz" data-platz data-count="<?php echo esc_attr( $count ); ?>">
	<div class="stick">
		<img src="<?php echo esc_url( bit_asset( 'img/amg-detail.jpg' ) ); ?>" alt="" loading="lazy">
		<div class="shade"></div>
		<div class="in">
			<?php bit_kicker( 'Der Platz' ); ?>
			<p class="count" data-counter aria-hidden="true"><?php echo esc_html( $count ); ?></p>
			<div class="meter" aria-hidden="true"><i data-meter></i></div>
			<h2 class="t-sec"><span class="sr-only"><?php echo esc_html( $count ); ?> </span><?php echo 1 === $count ? 'Fahrzeug steht gerade in Oberwangen.' : 'Fahrzeuge stehen gerade in Oberwangen.'; ?></h2>
			<p class="lead">Jedes einzelne geprüft, aufbereitet und ab MFK. Kein Katalog, den wir nachbestellen – das ist der Platz, wie er heute aussieht.</p>
			<div class="actions">
				<a class="bit-btn bit-btn--blue" href="<?php echo esc_url( bit_page_url( 'fahrzeuge' ) ); ?>"><?php echo 1 === $count ? 'Ansehen' : esc_html( 'Alle ' . $count . ' ansehen' ); ?></a>
			</div>
		</div>
	</div>
</section>
<?php endif; ?>

<section class="sec sec--flat">
	<div class="sec-head sec-head--stack">
		<?php bit_kicker( 'Dienstleistungen' ); ?>
		<h2 class="t-sec">Alles unter einem Dach</h2>
	</div>
	<?php bit_service_cards(); ?>
</section>

<?php
get_footer();
