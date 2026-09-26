<?php
/**
 * Einrichtung beim Aktivieren des Themes (und per «wp bit setup»):
 * Seiten, Startseite, Menues, Permalinks, Zeitzone, Datenschutz-Seite.
 * Bestehende Seiten werden nie ueberschrieben.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'after_switch_theme', 'bit_setup_site' );

/** Die Seiten der Webseite mit ihrem Starttext. */
function bit_seed_pages() {
	return array(
		'start'            => array( 'Start', '' ),
		'dienstleistungen' => array( 'Dienstleistungen', '' ),
		'kontakt'          => array( 'Kontakt', '' ),
		'ueber-uns'        => array( 'Über uns', '' ),
		'impressum'        => array( 'Impressum', bit_seed_impressum() ),
		'datenschutz'      => array( 'Datenschutz', bit_seed_datenschutz() ),
	);
}

function bit_seed_impressum() {
	return <<<'HTML'
<h2>Verantwortlich für den Inhalt</h2>
<p>[bit key="legal"], Aktiengesellschaft, handelnd unter [bit key="brand"]. Sitz [bit key="seat"], Domizil [bit key="street"], [bit key="zip"] [bit key="city_long"]. UID [bit key="uid"][bit key="vat" before=", MWST-Nr. "], eingetragen im Handelsregister des Kantons Bern am [bit key="hr_date"], Statuten vom [bit key="statutes"]. Zeichnungsberechtigt: [bit key="signatory"].</p>

<h2>Kapital und Zweck</h2>
<p>Aktienkapital CHF 100’000, voll liberiert, eingeteilt in 1’000 vinkulierte Namenaktien à CHF 100. Zweck sind Immobilien sowie der Handel mit Neu- und Occasionswagen und der Betrieb einer Autowerkstätte.</p>

<h2>Kontakt</h2>
<p>Telefon [bit_tel key="phone1"] oder [bit_tel key="phone2"], E-Mail [bit_mail]. Wir antworten während der Öffnungszeiten, [bit key="hours_week"] und [bit key="hours_sat"].</p>

<h2>Haftung</h2>
<p>Die Angaben zu den Fahrzeugen werden mit Sorgfalt erfasst, sind aber unverbindlich und ersetzen keine Besichtigung. Verbindlich ist allein der schriftliche Kaufvertrag. Für Inhalte verlinkter Seiten – etwa AutoScout24 – sind deren Betreiber verantwortlich.</p>

<h2>Urheberrecht</h2>
<p>Die Fotografien auf dieser Seite stammen aus unserem Showroom und gehören uns. Texte, Bilder und Aufbau dürfen ohne unsere Zustimmung nicht weiterverwendet werden.</p>
HTML;
}

function bit_seed_datenschutz() {
	return <<<'HTML'
<p class="lead">Wir fragen nie nach AHV-Nummer, Geburtsdatum oder Bankdaten. Was wir brauchen, ist Ihr Name, eine Telefonnummer und das Fahrzeug, um das es geht.</p>

<h2>Verantwortlich</h2>
<p>[bit key="legal"] ([bit key="brand"]), [bit key="street"], [bit key="zip"] [bit key="city"]. Fragen zum Datenschutz: [bit_mail] oder [bit_tel key="phone1"].</p>

<h2>Anfragen über das Formular</h2>
<p>Name, Telefonnummer, E-Mail und Ihre Nachricht werden bei uns gespeichert und als E-Mail an unser Postfach geschickt, damit wir zurückrufen können. Wir verwenden diese Angaben für nichts anderes, geben sie nicht weiter und löschen sie, wenn die Anfrage erledigt ist – spätestens nach zwölf Monaten.</p>

<h2>Beim Kauf oder Verkauf</h2>
<p>Für Kaufvertrag, Garantieschein und Übergabeprotokoll brauchen wir Ihre Adresse und einen Ausweis. Für die Abmeldung geht die Stammnummer ans Strassenverkehrs- und Schifffahrtsamt. Diese Unterlagen bewahren wir auf, solange das Gesetz es verlangt – zehn Jahre.</p>

<h2>Diese Webseite</h2>
<p>Kein Tracking, keine Werbe-Cookies, keine eingebetteten Karten von Dritten. Der Knopf «Route anzeigen» öffnet Google Maps erst, wenn Sie ihn antippen. Der Server hält ein technisches Protokoll (IP-Adresse, Zeitpunkt, aufgerufene Seite) für den Betrieb und die Sicherheit; es wird nach kurzer Zeit gelöscht. Die Schriften liegen auf unserem eigenen Server, es wird kein Google Fonts geladen. Cookies setzt die Seite nur beim Login unserer Mitarbeiter.</p>

<h2>Ihre Rechte</h2>
<p>Sie können jederzeit fragen, was wir über Sie gespeichert haben, es berichtigen oder löschen lassen. Ein Anruf genügt: [bit_tel key="phone1"].</p>
HTML;
}

/** Alles einrichten. Gibt ein kurzes Protokoll zurueck. */
function bit_setup_site() {
	$log = array();
	$ids = array();

	foreach ( bit_seed_pages() as $slug => $page ) {
		$existing = get_page_by_path( $slug, OBJECT, 'page' );
		if ( $existing ) {
			$ids[ $slug ] = $existing->ID;
			$log[]        = "Seite «{$page[0]}» besteht schon (#{$existing->ID}).";
			continue;
		}
		$ids[ $slug ] = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => $page[0],
				'post_name'    => $slug,
				'post_content' => $page[1],
			)
		);
		$log[] = "Seite «{$page[0]}» angelegt (#{$ids[ $slug ]}).";
	}

	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $ids['start'] );
	update_option( 'wp_page_for_privacy_policy', $ids['datenschutz'] );
	update_option( 'blogname', 'BIT Automobile' );
	update_option( 'blogdescription', 'Occasionen in Oberwangen b. Bern' );
	update_option( 'timezone_string', 'Europe/Zurich' );
	update_option( 'WPLANG', 'de_CH' ); // Sprachpaket installiert WordPress selbst (Einstellungen → Allgemein).
	update_option( 'date_format', 'd.m.Y' );
	update_option( 'time_format', 'H:i' );
	update_option( 'start_of_week', 1 );
	update_option( 'default_comment_status', 'closed' );
	update_option( 'default_ping_status', 'closed' );
	if ( ! get_option( 'permalink_structure' ) ) {
		update_option( 'permalink_structure', '/%postname%/' );
	}
	$log[] = 'Startseite, Datenschutz-Seite, Zeitzone und Permalinks gesetzt.';

	// Beispielseite und -beitrag von WordPress entfernen.
	foreach ( array( 'sample-page', 'beispiel-seite' ) as $sample ) {
		$p = get_page_by_path( $sample );
		if ( $p ) {
			wp_delete_post( $p->ID, true );
		}
	}
	$hello = get_page_by_path( 'hello-world', OBJECT, 'post' );
	if ( $hello ) {
		wp_delete_post( $hello->ID, true );
	}

	// Menues.
	$menus = array(
		'primary' => array(
			'name'  => 'Hauptnavigation',
			'items' => array( 'start', 'fahrzeuge', 'dienstleistungen', 'kontakt' ),
		),
		'footer'  => array(
			'name'  => 'Fusszeile',
			'items' => array( 'ueber-uns', 'impressum', 'datenschutz' ),
		),
	);
	$locations = get_theme_mod( 'nav_menu_locations', array() );
	foreach ( $menus as $location => $menu ) {
		if ( ! empty( $locations[ $location ] ) && wp_get_nav_menu_object( $locations[ $location ] ) ) {
			$log[] = "Menü «{$menu['name']}» besteht schon.";
			continue;
		}
		$menu_obj = wp_get_nav_menu_object( $menu['name'] );
		$menu_id  = $menu_obj ? $menu_obj->term_id : wp_create_nav_menu( $menu['name'] );
		foreach ( $menu['items'] as $slug ) {
			if ( 'fahrzeuge' === $slug ) {
				wp_update_nav_menu_item(
					$menu_id,
					0,
					array(
						'menu-item-title'  => 'Fahrzeuge',
						'menu-item-type'   => 'post_type_archive',
						'menu-item-object' => 'fahrzeug',
						'menu-item-status' => 'publish',
					)
				);
				continue;
			}
			wp_update_nav_menu_item(
				$menu_id,
				0,
				array(
					'menu-item-title'     => bit_seed_pages()[ $slug ][0],
					'menu-item-object'    => 'page',
					'menu-item-object-id' => $ids[ $slug ],
					'menu-item-type'      => 'post_type',
					'menu-item-status'    => 'publish',
				)
			);
		}
		$locations[ $location ] = $menu_id;
		$log[]                  = "Menü «{$menu['name']}» angelegt.";
	}
	set_theme_mod( 'nav_menu_locations', $locations );

	flush_rewrite_rules();
	return $log;
}
