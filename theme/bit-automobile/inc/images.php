<?php
/**
 * Fotos klein halten – ohne sichtbaren Unterschied auf der Webseite.
 *
 * Was beim Hochladen (Admin, Mediathek, AutoScout24-Import) passiert:
 *  1. Fotos groesser als 2000 px werden auf 2000 px (lange Seite) verkleinert.
 *     Die Webseite zeigt nie mehr als 1600 px; 2000 px bleiben als Reserve.
 *  2. Das riesige Original vom Handy wird danach geloescht. Es wird nirgends
 *     angezeigt und ist meist der groesste Teil des Speichers.
 *  3. Wenn der Server es kann, werden die Bilder als WebP gespeichert statt JPEG
 *     (gleiche Qualitaet, etwa ein Drittel kleiner; alle heutigen Browser koennen es).
 *  4. Zwei Grossformate, die die Seite nie braucht (1536 und 2048 px), entstehen nicht mehr.
 *  5. Kamera-Daten (GPS-Ort, Geraet) werden entfernt – sie verschwinden mit dem Original.
 *
 * Was man verliert: In der Mediathek laesst sich ein bearbeitetes Bild nicht mehr
 * auf das Handy-Original zuruecksetzen. Fuer Fahrzeugfotos ist das egal.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const BIT_IMG_MAX     = 2000; // laengste Seite in Pixel
const BIT_IMG_QUALITY = 82;   // JPEG/WebP-Qualitaet (WordPress-Standard ist 82)

/** 1. Grosse Fotos auf 2000 px verkleinern (WordPress-Standard waere 2560). */
add_filter( 'big_image_size_threshold', fn() => BIT_IMG_MAX );

/** 4. Die Formate 1536 und 2048 px braucht die Seite nicht. */
add_filter(
	'intermediate_image_sizes_advanced',
	function ( $sizes ) {
		unset( $sizes['1536x1536'], $sizes['2048x2048'] );
		return $sizes;
	}
);

/** Qualitaet fuer alle erzeugten Bilder. */
add_filter( 'wp_editor_set_quality', fn() => BIT_IMG_QUALITY );

/** 3. JPEG als WebP speichern, sofern der Server WebP schreiben kann. */
add_filter(
	'image_editor_output_format',
	function ( $formats ) {
		if ( bit_webp_supported() ) {
			$formats['image/jpeg'] = 'image/webp';
		}
		return $formats;
	}
);

/** Kann der Server WebP schreiben? (einmal pro Aufruf gepruefte Antwort) */
function bit_webp_supported() {
	static $ok = null;
	if ( null === $ok ) {
		$ok = (bool) apply_filters( 'bit_use_webp', wp_image_editor_supports( array( 'mime_type' => 'image/webp' ) ) );
	}
	return $ok;
}

/**
 * 2. Nach dem Hochladen: das Handy-Original loeschen, wenn eine verkleinerte
 * Fassung existiert. WordPress merkt sich das Original in «original_image».
 */
add_filter(
	'wp_generate_attachment_metadata',
	function ( $meta, $attachment_id ) {
		if ( empty( $meta['original_image'] ) || empty( $meta['file'] ) ) {
			return $meta;
		}
		$current  = get_attached_file( $attachment_id );
		$original = trailingslashit( dirname( $current ) ) . $meta['original_image'];
		if ( $current && file_exists( $current ) && $original !== $current && file_exists( $original ) ) {
			$saved = filesize( $original );
			wp_delete_file( $original );
			unset( $meta['original_image'] );
			update_post_meta( $attachment_id, '_bit_original_removed', (int) $saved );
		}
		return $meta;
	},
	20,
	2
);

/** Speicher, den die hochgeladenen Bilder belegen (fuer die Uebersicht, 12 h zwischengespeichert). */
function bit_uploads_size() {
	$cached = get_transient( 'bit_uploads_size' );
	if ( false !== $cached ) {
		return (int) $cached;
	}
	$dir   = wp_upload_dir()['basedir'];
	$bytes = 0;
	if ( is_dir( $dir ) ) {
		$it = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS ) );
		foreach ( $it as $file ) {
			if ( $file->isFile() ) {
				$bytes += $file->getSize();
			}
		}
	}
	set_transient( 'bit_uploads_size', $bytes, 12 * HOUR_IN_SECONDS );
	return $bytes;
}
add_action( 'add_attachment', fn() => delete_transient( 'bit_uploads_size' ) );
add_action( 'delete_attachment', fn() => delete_transient( 'bit_uploads_size' ) );
