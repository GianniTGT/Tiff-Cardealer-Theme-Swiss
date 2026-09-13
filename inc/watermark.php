<?php
/**
 * Photo watermark for vehicle galleries.
 *
 * The site serves a watermarked COPY. The uploaded original is never touched:
 * a mark burned into the file cannot be undone the day the logo changes, and
 * Google Merchant Center rejects a product image carrying promotional text, so
 * a future feed names the clean original while the gallery serves the marked one.
 *
 * It runs on demand, not at upload. The desktop app uploads a photo and attaches
 * it to the vehicle in two separate calls, so at upload time nothing yet says
 * this is a car photo. Built once, then remembered in attachment meta.
 *
 * Legacy ThemeMakers photos are left alone: they already carry the old plugin
 * mark, being the production files byte for byte.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function das_watermark_settings() {
	return apply_filters( 'das_watermark_settings', array(
		'width_pct'  => 22,
		'margin_pct' => 3,
		'opacity'    => 70,
		'min_width'  => 500,
	) );
}

function das_watermark_logo_path() {
	$id = (int) get_option( 'das_watermark_logo_id' );
	if ( $id ) {
		$f = get_attached_file( $id );
		if ( $f && file_exists( $f ) ) { return $f; }
	}
	$up = wp_upload_dir();
	if ( file_exists( $up['basedir'] . '/2021/06/das.png' ) ) {
		return $up['basedir'] . '/2021/06/das.png';
	}
	$theme = get_template_directory() . '/assets/img/logo.png';
	return file_exists( $theme ) ? $theme : '';
}
/**
 * Composite the logo onto a copy of $src, writing $dest. Returns true on success.
 * Anything unexpected returns false, and the caller then serves the original:
 * a missing watermark is a smaller fault than a missing photograph.
 */
function das_watermark_build( $src, $dest ) {
	$logo_path = das_watermark_logo_path();
	if ( ! $logo_path || ! file_exists( $src ) || ! function_exists( 'imagecreatetruecolor' ) ) {
		return false;
	}
	$info = @getimagesize( $src );
	if ( ! $info ) { return false; }
	$cfg = das_watermark_settings();
	if ( $info[0] < (int) $cfg['min_width'] ) { return false; }

	switch ( $info['mime'] ) {
		case 'image/jpeg': $img = @imagecreatefromjpeg( $src ); break;
		case 'image/png':  $img = @imagecreatefrompng( $src ); break;
		case 'image/webp': $img = function_exists( 'imagecreatefromwebp' ) ? @imagecreatefromwebp( $src ) : false; break;
		default: return false;
	}
	if ( ! $img ) { return false; }

	$logo = @imagecreatefrompng( $logo_path );
	if ( ! $logo ) { imagedestroy( $img ); return false; }

	$sw = imagesx( $img );
	$sh = imagesy( $img );
	$lw = imagesx( $logo );
	$lh = imagesy( $logo );
	$tw = max( 24, (int) round( $sw * $cfg['width_pct'] / 100 ) );
	$th = max( 12, (int) round( $lh * $tw / $lw ) );

	$scaled = imagecreatetruecolor( $tw, $th );
	imagealphablending( $scaled, false );
	imagesavealpha( $scaled, true );
	imagefill( $scaled, 0, 0, imagecolorallocatealpha( $scaled, 0, 0, 0, 127 ) );
	imagecopyresampled( $scaled, $logo, 0, 0, 0, 0, $tw, $th, $lw, $lh );
	imagedestroy( $logo );

	$op = max( 0, min( 100, (int) $cfg['opacity'] ) ) / 100;
	for ( $y = 0; $y < $th; $y++ ) {
		for ( $x = 0; $x < $tw; $x++ ) {
			$c = imagecolorat( $scaled, $x, $y );
			$a = ( $c >> 24 ) & 0x7F;
			$na = 127 - (int) round( ( 127 - $a ) * $op );
			imagesetpixel( $scaled, $x, $y, ( $na << 24 ) | ( $c & 0xFFFFFF ) );
		}
	}

	$m = (int) round( $sw * $cfg['margin_pct'] / 100 );
	imagealphablending( $img, true );
	imagecopy( $img, $scaled, $sw - $tw - $m, $sh - $th - $m, 0, 0, $tw, $th );
	imagedestroy( $scaled );

	$ok = @imagejpeg( $img, $dest, 86 );
	imagedestroy( $img );
	return (bool) $ok;
}
/** The file behind one registered size, falling back to the original. */
function das_watermark_source_path( $id, $size ) {
	$file = get_attached_file( $id );
	if ( ! $file || ! file_exists( $file ) ) { return ''; }
	$meta = wp_get_attachment_metadata( $id );
	if ( 'full' !== $size && ! empty( $meta['sizes'][ $size ]['file'] ) ) {
		$sized = dirname( $file ) . '/' . $meta['sizes'][ $size ]['file'];
		if ( file_exists( $sized ) ) { return $sized; }
	}
	return $file;
}

/**
 * URL of the watermarked copy, built on first use. Falls back to the plain URL
 * whenever it cannot be built, so a photograph is never lost to this feature.
 */
function das_watermark_url( $id, $size = 'das_hero' ) {
	$id    = (int) $id;
	$plain = wp_get_attachment_image_url( $id, $size );
	if ( ! $plain ) { return ''; }
	if ( ! apply_filters( 'das_watermark_enabled', true, $id, $size ) ) { return $plain; }

	$up  = wp_upload_dir();
	$key = '_das_wm_' . $size;
	$rel = (string) get_post_meta( $id, $key, true );
	if ( $rel && file_exists( $up['basedir'] . '/' . $rel ) ) {
		return $up['baseurl'] . '/' . $rel;
	}

	$src = das_watermark_source_path( $id, $size );
	if ( ! $src ) { return $plain; }

	$rel  = 'das-wm/' . $id . '-' . sanitize_key( $size ) . '.jpg';
	$dest = $up['basedir'] . '/' . $rel;
	wp_mkdir_p( dirname( $dest ) );
	if ( das_watermark_build( $src, $dest ) ) {
		update_post_meta( $id, $key, $rel );
		return $up['baseurl'] . '/' . $rel;
	}
	return $plain;
}

/** Take the copies with the attachment, so nothing outlives what it depicts. */
function das_watermark_cleanup( $id ) {
	$up = wp_upload_dir();
	foreach ( (array) get_post_meta( $id ) as $k => $v ) {
		if ( 0 !== strpos( $k, '_das_wm_' ) ) { continue; }
		$f = $up['basedir'] . '/' . (string) $v[0];
		if ( $v[0] && file_exists( $f ) ) { @unlink( $f ); }
	}
}
add_action( 'delete_attachment', 'das_watermark_cleanup' );