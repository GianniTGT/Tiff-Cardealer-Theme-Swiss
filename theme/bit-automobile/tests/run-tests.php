<?php
/**
 * Selbsttest des Themes, ausfuehren mit:
 *   wp eval-file wp-content/themes/bit-automobile/tests/run-tests.php
 *
 * Prueft Formatierung, AutoScout24-Zuordnung und den Import mit
 * erfundenen Daten (anlegen, erneut = nichts, Preis aendern, verschwindet
 * = verkauft). Raeumt danach auf. Schreibt «OK» oder «FEHLER» pro Test.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit( 1 );
}

$fail = 0;
$ok   = 0;
$t    = function ( $name, $cond, $detail = '' ) use ( &$fail, &$ok ) {
	if ( $cond ) {
		++$ok;
		echo "OK      {$name}\n";
	} else {
		++$fail;
		echo "FEHLER  {$name}" . ( $detail ? " — {$detail}" : '' ) . "\n";
	}
};

// --- Formatierung (Schweiz) ---
$t( 'CHF mit Apostroph', 'CHF 129’900' === bit_chf( 129900 ), bit_chf( 129900 ) );
$t( 'Preis leer = auf Anfrage', 'Preis auf Anfrage' === bit_chf( '' ) );
$t( 'Kilometer', '1’234’567' === bit_num( 1234567 ) );
$t( 'Telefon Festnetz', '+41315520002' === bit_tel( '031 552 00 02' ), bit_tel( '031 552 00 02' ) );
$t( 'Telefon Mobil', '+41788688797' === bit_tel( '078 868 87 97' ) );
$t( 'Telefon international', '+41315520002' === bit_tel( '+41 31 552 00 02' ), bit_tel( '+41 31 552 00 02' ) );
$t( 'MWST-Nr. im Impressum-Kurzcode', ', MWST-Nr. CHE-345.577.846 MWST' === do_shortcode( '[bit key="vat" before=", MWST-Nr. "]' ), do_shortcode( '[bit key="vat" before=", MWST-Nr. "]' ) );

// --- Zuordnung ---
$m = bit_as24_map_listing(
	array(
		'id'                    => 'x1',
		'makeKey'               => 'mercedes-benz',
		'modelName'             => 'C 300 d',
		'versionFullName'       => 'T-Modell',
		'price'                 => 33500,
		'mileage'               => 88900,
		'firstRegistrationDate' => '2020-06-08',
		'kiloWatts'             => 180,
		'transmissionType'      => 'automatic',
		'fuelType'              => 'phev-petrol',
		'driveType'             => 'all',
		'bodyColor'             => 'gray',
		'inspected'             => true,
		'warrantyType'          => 'from-delivery',
		'warrantyDuration'      => 12,
		'consumptionCombined'   => 5.64,
		'images'                => array( array( 'url' => 'https://example.invalid/a.jpg' ), 'https://example.invalid/b.jpg' ),
	)
);
$t( 'Titel aus Marke + Modell', 'Mercedes-Benz C 300 d' === $m['title'], $m['title'] );
$t( 'Erstzulassung MM.JJJJ', '06.2020' === $m['fields']['first_reg'] && '2020' === $m['fields']['year'] );
$t( 'kW → PS', '245 PS' === $m['fields']['power'], $m['fields']['power'] );
$t( 'Getriebe deutsch', 'Automat' === $m['fields']['gearbox'] );
$t( 'Treibstoff deutsch', 'Plug-in-Hybrid Benzin' === $m['fields']['fuel'] );
$t( 'Antrieb deutsch', 'Allrad' === $m['fields']['drive'] );
$t( 'Farbe aus Enum', 'Grau' === $m['fields']['colour'] );
$t( 'MFK aus inspected', '1' === $m['fields']['mfk'] );
$t( 'Garantie', 'Garantie ab Übergabe, 12 Monate' === $m['fields']['warranty'], $m['fields']['warranty'] );
$t( 'Verbrauch mit Komma', '5,6 l/100 km' === $m['fields']['consumption'], $m['fields']['consumption'] );
$t( 'Zwei Bilder', 2 === count( $m['images'] ) );
$t( 'Ohne ID übersprungen', null === bit_as24_map_listing( array( 'makeKey' => 'bmw' ) ) );
$t( 'Stammnummer nie übernommen', '' === $m['fields']['stamm'] );

// --- Import mit Testdaten (ohne Bilder, damit es schnell geht) ---
$items = array_slice( bit_demo_listings(), 0, 3 );
foreach ( $items as &$it ) {
	$it['id'] = 'test-' . $it['id'];
}
unset( $it );
$opts = array( 'images' => false, 'demo' => false );
$demo_before = count( bit_get_vehicles( array( 'meta_key' => '_bit_demo', 'meta_value' => '1', 'fields' => 'ids' ) ) );

$r1 = bit_as24_import( $items, $opts );
$t( 'Import legt 3 an', 3 === $r1['created'], wp_json_encode( $r1 ) );

$r2 = bit_as24_import( $items, $opts );
$t( 'Zweiter Import ändert nichts', 0 === $r2['created'] && 0 === $r2['updated'] && 3 === $r2['unchanged'], wp_json_encode( $r2 ) );

$items[0]['price'] = 111000;
$r3                = bit_as24_import( $items, $opts );
$pid               = bit_as24_find_post( $items[0]['id'] );
$t( 'Preisänderung übernommen', 1 === $r3['updated'] && '111000' === bit_meta( $pid, 'price' ) );

$r4   = bit_as24_import( array_slice( $items, 1 ), $opts );
$gone = bit_as24_find_post( $items[0]['id'] );
$t( 'Fehlt im Feed → verkauft', 1 === $r4['sold'] && 'verkauft' === bit_meta( $gone, 'status' ), wp_json_encode( $r4 ) );
$t( 'Verkauft nicht mehr in der Liste', ! in_array( $gone, wp_list_pluck( bit_get_vehicles(), 'ID' ), true ) );

$dry = bit_as24_import( array( array_merge( $items[1], array( 'id' => 'test-neu' ) ) ), array_merge( $opts, array( 'dry_run' => true, 'mark_missing' => false ) ) );
$t( 'Probelauf schreibt nichts', 1 === $dry['created'] && 0 === bit_as24_find_post( 'test-neu' ) );

$v = bit_vehicle( bit_as24_find_post( $items[1]['id'] ) );
$t( 'Marke gesetzt', '' !== $v['brand'], $v['brand'] );
$t( 'Echter Import ist nicht Demo', false === $v['demo'] );
$t( 'Demo-Fahrzeuge unberührt', count( bit_get_vehicles( array( 'meta_key' => '_bit_demo', 'meta_value' => '1', 'fields' => 'ids' ) ) ) === $demo_before );

// Aufraeumen: nur die Test-Fahrzeuge.
foreach ( $items as $it ) {
	$p = bit_as24_find_post( $it['id'] );
	if ( $p ) {
		wp_delete_post( $p, true );
	}
}

// --- Foto-Verkleinerung: grosses Foto hochladen, Ergebnis pruefen ---
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';
$gd = imagecreatetruecolor( 3000, 2250 );
for ( $i = 0; $i < 4000; $i++ ) { // Struktur, damit das Bild nicht trivial klein komprimiert
	imagefilledrectangle( $gd, wp_rand( 0, 2999 ), wp_rand( 0, 2249 ), wp_rand( 0, 2999 ), wp_rand( 0, 2249 ), imagecolorallocate( $gd, wp_rand( 0, 255 ), wp_rand( 0, 255 ), wp_rand( 0, 255 ) ) );
}
$big = wp_tempnam( 'bit-grossfoto.jpg' );
imagejpeg( $gd, $big, 95 );
$upload_bytes = filesize( $big );
$att  = media_handle_sideload( array( 'name' => 'bit-grossfoto.jpg', 'tmp_name' => $big ), 0 );
$meta = wp_get_attachment_metadata( $att );
$main = get_attached_file( $att );
$dir  = dirname( $main );
$left = glob( $dir . '/bit-grossfoto*' );
$sum  = array_sum( array_map( 'filesize', $left ) );
$t( 'Foto: auf 2000 px verkleinert', 2000 === (int) ( $meta['width'] ?? 0 ), ( $meta['width'] ?? '?' ) . ' px' );
$t( 'Foto: Handy-Original gelöscht', empty( $meta['original_image'] ) && ! file_exists( $dir . '/bit-grossfoto.jpg' ) );
$t( 'Foto: keine 1536/2048-Formate', ! isset( $meta['sizes']['1536x1536'] ) && ! isset( $meta['sizes']['2048x2048'] ) );
$t( 'Foto: Karten- und Detailgrösse vorhanden', isset( $meta['sizes']['bit-card'], $meta['sizes']['bit-large'] ) );
$t( 'Foto: alles zusammen kleiner als das Original', $sum < $upload_bytes, round( $sum / 1024 ) . ' KB von ' . round( $upload_bytes / 1024 ) . ' KB' );
$t( 'Foto: WebP, wenn der Server es kann', ! bit_webp_supported() || str_ends_with( $main, '.webp' ), basename( $main ) );
wp_delete_attachment( $att, true );

// --- Probelauf gegen die AutoScout24-Attrappe (kein Internet, nichts wird gespeichert) ---
require_once BIT_DIR . '/tests/as24-mock.php';
$saved_settings = get_option( BIT_AS24_OPTION, null );
update_option( BIT_AS24_OPTION, array( 'seller_id' => '12345', 'client_id' => 'test', 'client_secret' => 'test-secret' ) );
delete_transient( 'bit_as24_token' );
add_filter( 'pre_http_request', 'bit_as24_mock_response', 10, 3 );
$before = wp_count_posts( 'fahrzeug' )->publish;
$probe  = bit_as24_sync( true );
$t( 'Probelauf: 11 Inserate gelesen', 11 === ( $probe['found'] ?? 0 ), wp_json_encode( $probe['errors'] ?? array() ) );
$t( 'Probelauf: würde 11 anlegen', 11 === ( $probe['created'] ?? 0 ) );
$t( 'Probelauf: speichert keine Fahrzeuge', (int) $before === (int) wp_count_posts( 'fahrzeug' )->publish && 0 === bit_as24_find_post( 'probe-demo-1001' ) );
$t( 'Probelauf: keine Hinweise (alle Felder gefunden)', empty( $probe['errors'] ), wp_json_encode( $probe['errors'] ?? array() ) );
$t( 'Probelauf: Zeile mit Preis und km', (bool) preg_grep( '/würde anlegen: Mercedes-Benz AMG GT C – CHF 129’900 – 31’500 km – 2018 – 3 Bilder/u', $probe['log'] ?? array() ) );
$raw_path = bit_as24_raw_dir() . '/' . ( $probe['raw_file'] ?? 'x' );
$t( 'Probelauf: Rohdaten gespeichert', is_readable( $raw_path ) && str_contains( (string) file_get_contents( $raw_path ), 'probe-demo-1001' ) );
$t( 'Probelauf: Rohdaten ohne Token', ! str_contains( (string) file_get_contents( $raw_path ), 'mock-token' ) );
$t( 'Rohdaten-Ordner gesperrt', is_readable( bit_as24_raw_dir() . '/.htaccess' ) );
$t( 'Probelauf-Bericht separat', ( get_option( BIT_AS24_PROBE )['raw_file'] ?? '' ) === $probe['raw_file'] );
delete_transient( 'bit_as24_token' );
update_option( BIT_AS24_OPTION, array( 'seller_id' => '12345', 'client_id' => 'test', 'client_secret' => 'falsch' ) );
$bad = bit_as24_sync( true );
$t( 'Falsches Secret: klare Fehlermeldung', str_contains( implode( ' ', $bad['errors'] ?? array() ), 'Anmeldung fehlgeschlagen' ) );
remove_filter( 'pre_http_request', 'bit_as24_mock_response', 10 );
delete_transient( 'bit_as24_token' );
delete_option( BIT_AS24_PROBE );
wp_delete_file( $raw_path );
null === $saved_settings ? delete_option( BIT_AS24_OPTION ) : update_option( BIT_AS24_OPTION, $saved_settings );

echo "\n{$ok} OK, {$fail} FEHLER\n";
if ( $fail ) {
	exit( 1 );
}
