<?php
/**
 * Fahrzeug-Maske im Admin: alle Daten in einem Kasten, Bildergalerie,
 * Spalten in der Liste (Bild, Preis, Kilometer, Status).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'add_meta_boxes_fahrzeug',
	function () {
		add_meta_box( 'bit_vehicle_data', 'Fahrzeugdaten', 'bit_vehicle_box', 'fahrzeug', 'normal', 'high' );
	}
);

function bit_vehicle_box( WP_Post $post ) {
	wp_nonce_field( 'bit_vehicle_save', 'bit_vehicle_nonce' );
	$fields = bit_vehicle_fields();
	echo '<style>
		.bitv{display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:14px 18px}
		.bitv label{display:block;font-weight:600;margin-bottom:4px}
		.bitv input[type=text],.bitv input[type=number],.bitv select{width:100%}
		.bitv .help{color:#646970;font-size:12px;margin-top:3px}
		.bitv .wide{grid-column:1/-1}
		.bitv-gal{display:flex;flex-wrap:wrap;gap:8px;margin:8px 0}
		.bitv-gal img{width:96px;height:72px;object-fit:cover;border-radius:6px}
		.bitv-demo{background:#fcf0dc;border-left:4px solid #dba617;padding:8px 12px;margin-bottom:12px}
	</style>';
	if ( bit_meta( $post->ID, 'demo' ) ) {
		echo '<p class="bitv-demo"><strong>Demo-Fahrzeug:</strong> erfundene Testdaten. Vor dem Livegang löschen (Werkzeuge → BIT Demo-Daten).</p>';
	}
	echo '<div class="bitv">';
	foreach ( $fields as $key => $def ) {
		list( $label, $type, $help ) = $def;
		$name  = 'bit_' . $key;
		$value = bit_meta( $post->ID, $key );
		$wide  = in_array( $type, array( 'gallery' ), true ) ? ' wide' : '';
		echo '<div class="field' . esc_attr( $wide ) . '">';
		switch ( $type ) {
			case 'checkbox':
				$checked = ( '' === $value && 'auto-draft' === $post->post_status ) ? true : (bool) $value;
				printf( '<label><input type="checkbox" name="%1$s" value="1" %2$s> %3$s</label>', esc_attr( $name ), checked( $checked, true, false ), esc_html( $label ) );
				break;
			case 'select':
				printf( '<label for="%1$s">%2$s</label><select id="%1$s" name="%1$s">', esc_attr( $name ), esc_html( $label ) );
				foreach ( bit_vehicle_statuses() as $k => $l ) {
					printf( '<option value="%s" %s>%s</option>', esc_attr( $k ), selected( $value ? $value : 'verfuegbar', $k, false ), esc_html( $l ) );
				}
				echo '</select>';
				break;
			case 'gallery':
				printf( '<label>%s</label>', esc_html( $label ) );
				printf( '<input type="hidden" id="bit_gallery" name="%s" value="%s">', esc_attr( $name ), esc_attr( $value ) );
				echo '<div class="bitv-gal" id="bit_gallery_preview">';
				foreach ( array_filter( array_map( 'intval', explode( ',', (string) $value ) ) ) as $att ) {
					echo wp_get_attachment_image( $att, 'thumbnail' );
				}
				echo '</div><button type="button" class="button" id="bit_gallery_pick">Bilder wählen</button> <button type="button" class="button-link" id="bit_gallery_clear">Leeren</button>';
				break;
			default:
				printf(
					'<label for="%1$s">%2$s</label><input type="%3$s" id="%1$s" name="%1$s" value="%4$s"%5$s>',
					esc_attr( $name ),
					esc_html( $label ),
					esc_attr( $type ),
					esc_attr( $value ),
					'number' === $type ? ' min="0" step="1"' : ''
				);
		}
		if ( $help ) {
			printf( '<div class="help">%s</div>', esc_html( $help ) );
		}
		echo '</div>';
	}
	echo '</div>';
	?>
	<script>
	jQuery(function($){
		var frame;
		$('#bit_gallery_pick').on('click', function(e){
			e.preventDefault();
			if (frame) { frame.open(); return; }
			frame = wp.media({ title: 'Bilder für dieses Fahrzeug', button: { text: 'Übernehmen' }, multiple: 'add', library: { type: 'image' } });
			frame.on('open', function(){
				var sel = frame.state().get('selection');
				($('#bit_gallery').val() || '').split(',').filter(Boolean).forEach(function(id){ sel.add(wp.media.attachment(id)); });
			});
			frame.on('select', function(){
				var ids = [], html = '';
				frame.state().get('selection').each(function(a){
					ids.push(a.id);
					var s = a.get('sizes'); var u = (s && s.thumbnail) ? s.thumbnail.url : a.get('url');
					html += '<img src="' + u + '" alt="">';
				});
				$('#bit_gallery').val(ids.join(','));
				$('#bit_gallery_preview').html(html);
			});
			frame.open();
		});
		$('#bit_gallery_clear').on('click', function(e){ e.preventDefault(); $('#bit_gallery').val(''); $('#bit_gallery_preview').empty(); });
	});
	</script>
	<?php
}

add_action(
	'admin_enqueue_scripts',
	function ( $hook ) {
		$screen = get_current_screen();
		if ( $screen && 'fahrzeug' === $screen->post_type && in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			wp_enqueue_media();
		}
	}
);

add_action(
	'save_post_fahrzeug',
	function ( $post_id ) {
		if ( ! isset( $_POST['bit_vehicle_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['bit_vehicle_nonce'] ), 'bit_vehicle_save' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		foreach ( bit_vehicle_fields() as $key => $def ) {
			$type = $def[1];
			$raw  = isset( $_POST[ 'bit_' . $key ] ) ? wp_unslash( $_POST[ 'bit_' . $key ] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			switch ( $type ) {
				case 'number':
					$val = '' === trim( (string) $raw ) ? '' : (string) absint( preg_replace( '/[^\d]/', '', (string) $raw ) );
					break;
				case 'checkbox':
					$val = $raw ? '1' : '0';
					break;
				case 'select':
					$val = array_key_exists( $raw, bit_vehicle_statuses() ) ? $raw : 'verfuegbar';
					break;
				case 'gallery':
					$val = implode( ',', array_filter( array_map( 'absint', explode( ',', (string) $raw ) ) ) );
					break;
				default:
					$val = sanitize_text_field( $raw );
			}
			update_post_meta( $post_id, '_bit_' . $key, $val );
		}
	}
);

/* ---------- Spalten in der Fahrzeugliste ---------- */
add_filter(
	'manage_fahrzeug_posts_columns',
	function ( $cols ) {
		$new = array();
		foreach ( $cols as $k => $l ) {
			if ( 'title' === $k ) {
				$new['bit_thumb'] = 'Bild';
			}
			$new[ $k ] = $l;
			if ( 'title' === $k ) {
				$new['bit_price']  = 'Preis';
				$new['bit_km']     = 'Kilometer';
				$new['bit_year']   = 'Jahrgang';
				$new['bit_status'] = 'Status';
			}
		}
		return $new;
	}
);

add_action(
	'manage_fahrzeug_posts_custom_column',
	function ( $col, $post_id ) {
		switch ( $col ) {
			case 'bit_thumb':
				echo get_the_post_thumbnail( $post_id, array( 72, 54 ), array( 'style' => 'width:72px;height:54px;object-fit:cover;border-radius:6px' ) );
				break;
			case 'bit_price':
				echo esc_html( bit_chf( bit_meta( $post_id, 'price' ) ) );
				break;
			case 'bit_km':
				$km = bit_meta( $post_id, 'km' );
				echo esc_html( '' !== $km ? bit_num( $km ) . ' km' : '–' );
				break;
			case 'bit_year':
				echo esc_html( bit_meta( $post_id, 'year' ) );
				break;
			case 'bit_status':
				$s = bit_meta( $post_id, 'status' );
				$s = $s ? $s : 'verfuegbar';
				echo esc_html( bit_vehicle_statuses()[ $s ] ?? $s );
				if ( bit_meta( $post_id, 'demo' ) ) {
					echo ' <span style="background:#dba617;color:#fff;border-radius:3px;padding:1px 5px;font-size:11px">DEMO</span>';
				}
				break;
		}
	},
	10,
	2
);

/** Fahrzeuge im einfachen Editor: Titel, Text, Datenkasten, Bilder – alles auf einer Seite. */
add_filter(
	'use_block_editor_for_post_type',
	function ( $use, $post_type ) {
		return 'fahrzeug' === $post_type ? false : $use;
	},
	10,
	2
);
