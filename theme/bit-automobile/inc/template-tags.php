<?php
/**
 * Bausteine fuer die Vorlagen: Logo, Symbol, Stroemung, Fakten,
 * Dienstleistungen, Fahrzeugkarte, Navigation.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Bild aus dem Theme (assets/img). */
function bit_asset( $path ) {
	return BIT_URI . '/assets/' . ltrim( $path, '/' );
}

/** Das Logo als Maske; die Farbe kommt aus CSS. */
function bit_logo( $muted = false ) {
	printf(
		'<span class="bit-logo%s" style="--logo-src:url(%s)" role="img" aria-label="%s"><span class="mark"></span></span>',
		$muted ? ' bit-logo--muted' : '',
		esc_url( bit_asset( 'logo/logo-alpha.png' ) ),
		esc_attr( bit_info( 'brand' ) )
	);
}

/** Ein Symbol aus dem Sprite. */
function bit_icon( $id, $size = 16 ) {
	return sprintf( '<svg class="bit-icon" width="%1$d" height="%1$d" viewBox="0 0 24 24" aria-hidden="true"><use href="#i-%2$s"></use></svg>', (int) $size, esc_attr( $id ) );
}

/** Die Aare-Linie unter Seitenkoepfen. */
function bit_flow( $flush = false ) {
	printf( '<div class="flowwrap%s" aria-hidden="true"><div class="bit-flow"><i></i></div></div>', $flush ? ' flowwrap--flush' : '' );
}

/** Kicker ueber einer Ueberschrift. */
function bit_kicker( $text ) {
	printf( '<p class="bit-kicker">%s</p>', esc_html( $text ) );
}

/** Adresse einer Seite nach Slug, sonst Startseite. */
function bit_page_url( $slug ) {
	if ( 'fahrzeuge' === $slug ) {
		return get_post_type_archive_link( 'fahrzeug' );
	}
	$page = get_page_by_path( $slug );
	return $page ? get_permalink( $page ) : home_url( '/' . $slug . '/' );
}

/** Telefon-Link. */
function bit_tel_link( $which = 'phone1', $class = '' ) {
	$phone = bit_info( $which );
	return sprintf( '<a%s href="tel:%s">%s</a>', $class ? ' class="' . esc_attr( $class ) . '"' : '', esc_attr( bit_tel( $phone ) ), esc_html( $phone ) );
}

/** Die vier Fakten (Showroom, Telefon, E-Mail, Offen). */
function bit_facts() {
	?>
	<dl class="bit-facts">
		<div><dt>Showroom</dt><dd><?php echo esc_html( bit_info( 'street' ) . ', ' . bit_info( 'zip' ) . ' ' . bit_info( 'city' ) ); ?></dd></div>
		<div><dt>Telefon</dt><dd class="num"><?php echo bit_tel_link( 'phone1' ); ?> · <?php echo bit_tel_link( 'phone2' ); ?></dd></div>
		<div><dt>E-Mail</dt><dd><a href="mailto:<?php echo esc_attr( bit_info( 'email' ) ); ?>"><?php echo esc_html( bit_info( 'email' ) ); ?></a></dd></div>
		<div><dt>Offen</dt><dd class="num"><?php echo esc_html( bit_info( 'hours_week' ) . ' · ' . bit_info( 'hours_sat' ) ); ?></dd></div>
	</dl>
	<?php
}

/**
 * Die vier Dienstleistungen – dieselben wie auf der bisherigen bit-automobile.ch.
 * Jede hat eine eigene Seite unter /dienstleistungen/<slug>/ (Text im Admin bearbeitbar).
 */
function bit_services() {
	return apply_filters(
		'bit_services',
		array(
			'an-und-verkauf'            => array( 'An- und Verkauf', 'Geprüfte Occasionen kaufen, Ihr Auto verkaufen oder eintauschen – Barzahlung und Abmeldung inbegriffen.', 'img/amg-wide.jpg' ),
			'fahrzeugaufbereitung'      => array( 'Fahrzeugaufbereitung', 'Innen und aussen, bis es so aussieht, wie es sich fährt. Auf Wunsch mit Lack-Schutz.', 'img/amg-gt-c.jpg' ),
			'carrosserie-und-werkstatt' => array( 'Carrosserie und Werkstatt', 'Wartung, Reparatur und MFK-Vorbereitung für alle Marken – mit moderner Diagnose.', 'img/platz-herzwilstrasse.jpg' ),
			'fahrzeugbewertung'         => array( 'Fahrzeugbewertung', 'Was ist Ihr Auto wert? Mit den Angaben aus dem Fahrzeugausweis sagen wir es Ihnen.', 'img/porsche-911.jpg' ),
		)
	);
}

/** Adresse einer Dienstleistungs-Seite. */
function bit_service_url( $slug ) {
	$page = get_page_by_path( 'dienstleistungen/' . $slug );
	return $page ? get_permalink( $page ) : home_url( '/dienstleistungen/' . $slug . '/' );
}

/** Ist die aktuelle Seite eine Dienstleistung? Gibt den Slug zurueck oder ''. */
function bit_current_service() {
	if ( ! is_page() ) {
		return '';
	}
	$page   = get_queried_object();
	$parent = $page && $page->post_parent ? get_post( $page->post_parent ) : null;
	return ( $parent && 'dienstleistungen' === $parent->post_name && isset( bit_services()[ $page->post_name ] ) ) ? $page->post_name : '';
}

/** Dienstleistungs-Karten; die ganze Karte fuehrt auf die eigene Seite. $except = Slug weglassen. */
function bit_service_cards( $except = '' ) {
	$items = array_filter( bit_services(), fn( $k ) => $k !== $except, ARRAY_FILTER_USE_KEY );
	printf( '<div class="svc-grid svc-grid--%d">', count( $items ) );
	foreach ( $items as $slug => $s ) {
		?>
		<article class="svc">
			<span class="glow" aria-hidden="true"></span>
			<div class="in">
				<h3><?php echo esc_html( $s[0] ); ?></h3>
				<p><?php echo esc_html( $s[1] ); ?></p>
				<a class="more" href="<?php echo esc_url( bit_service_url( $slug ) ); ?>">Mehr erfahren <i aria-hidden="true">→</i><span class="sr-only"> über <?php echo esc_html( $s[0] ); ?></span></a>
			</div>
		</article>
		<?php
	}
	echo '</div>';
}

/** Die vier Kriterien (Ueber uns). */
function bit_criteria() {
	return array(
		array( '01', 'Herkunft ist bekannt', 'Ein Fahrzeug kommt nur auf den Platz, wenn wir wissen, woher es kommt und wer es gefahren hat.' ),
		array( '02', 'Serviceheft stimmt', 'Lückenhafte Papiere sind kein Verhandlungsspielraum, sondern ein Grund, nein zu sagen.' ),
		array( '03', 'Ab MFK, immer', 'Jedes Fahrzeug wird geprüft, bevor es angeboten wird – nicht erst, wenn es verkauft ist.' ),
		array( '04', 'Wir kaufen weniger', 'Ungefähr jedes dritte Auto, das wir anschauen, lassen wir stehen. Das ist der Grund, warum der Platz klein ist.' ),
	);
}

/** Bild eines Fahrzeugs; ohne Bild ein Platzhalter aus dem Showroom. */
function bit_vehicle_image( $v, $size = 'bit-card', $index = 0, $attr = array() ) {
	$attr = wp_parse_args(
		$attr,
		array(
			'alt'     => $v['title'],
			'loading' => 'lazy',
		)
	);
	if ( ! empty( $v['images'][ $index ] ) ) {
		return wp_get_attachment_image( $v['images'][ $index ], $size, false, $attr );
	}
	return sprintf( '<img src="%s" alt="%s" loading="lazy">', esc_url( bit_asset( 'img/showroom-innen.jpg' ) ), esc_attr( $attr['alt'] ) );
}

/** Eine Fahrzeugkarte, wie im Entwurf. */
function bit_vehicle_card( $post_id ) {
	$v = bit_vehicle( $post_id );
	get_template_part( 'template-parts/vehicle-card', null, array( 'v' => $v ) );
}

/** Kopf-Navigation: Menue «primary», sonst die vier Seiten aus dem Entwurf. */
function bit_nav_items( $location = 'primary' ) {
	$items     = array();
	$locations = get_nav_menu_locations();
	if ( ! empty( $locations[ $location ] ) ) {
		foreach ( (array) wp_get_nav_menu_items( $locations[ $location ] ) as $item ) {
			if ( (int) $item->menu_item_parent ) {
				continue;
			}
			$items[] = array(
				'label' => $item->title,
				'url'   => $item->url,
				'id'    => (int) $item->object_id,
				'type'  => $item->object,
			);
		}
	}
	if ( ! $items ) {
		$slugs = 'footer' === $location
			? array( 'ueber-uns' => 'Über uns', 'impressum' => 'Impressum', 'datenschutz' => 'Datenschutz' )
			: array( '' => 'Start', 'fahrzeuge' => 'Fahrzeuge', 'dienstleistungen' => 'Dienstleistungen', 'kontakt' => 'Kontakt' );
		foreach ( $slugs as $slug => $label ) {
			$items[] = array(
				'label' => $label,
				'url'   => '' === $slug ? home_url( '/' ) : bit_page_url( $slug ),
				'id'    => 0,
				'type'  => $slug,
			);
		}
	}
	return $items;
}

/** Ist dieser Menuepunkt die aktuelle Seite? Fahrzeug-Details zaehlen zu «Fahrzeuge». */
function bit_nav_is_current( $item ) {
	$url = untrailingslashit( $item['url'] );
	if ( is_front_page() ) {
		return untrailingslashit( home_url( '/' ) ) === $url;
	}
	if ( is_singular( 'fahrzeug' ) || is_post_type_archive( 'fahrzeug' ) || is_tax( 'marke' ) ) {
		return untrailingslashit( (string) get_post_type_archive_link( 'fahrzeug' ) ) === $url;
	}
	if ( is_page() && $item['id'] ) {
		// Unterseiten (z.B. eine Dienstleistung) markieren ihre Elternseite.
		$id = get_queried_object_id();
		return $id === $item['id'] || in_array( $item['id'], get_post_ancestors( $id ), true );
	}
	global $wp;
	return untrailingslashit( home_url( $wp->request ) ) === $url;
}
