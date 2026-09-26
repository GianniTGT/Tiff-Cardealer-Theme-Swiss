<?php
/**
 * Fahrzeugkarte (Liste, Startseite, «Ähnlich»).
 * data-* Werte braucht die Sofort-Suche auf /fahrzeuge/.
 *
 * @var array $args ['v' => bit_vehicle()]
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$v    = $args['v'];
$hay  = strtolower( implode( ' ', array( $v['title'], $v['subtitle'], $v['gearbox'], $v['year'], $v['power'], $v['brand'], $v['fuel'] ) ) );
?>
<article class="bit-veh"
	data-brand="<?php echo esc_attr( $v['brand_slug'] ); ?>"
	data-price="<?php echo esc_attr( (int) $v['price'] ); ?>"
	data-km="<?php echo esc_attr( (int) $v['km'] ); ?>"
	data-year="<?php echo esc_attr( (int) $v['year'] ); ?>"
	data-hay="<?php echo esc_attr( $hay ); ?>">
	<a class="shot" href="<?php echo esc_url( $v['url'] ); ?>" tabindex="-1" aria-hidden="true">
		<?php if ( 'reserviert' === $v['status'] ) : ?>
			<span class="bit-phototag bit-phototag--muted">Reserviert</span>
		<?php elseif ( $v['mfk'] ) : ?>
			<span class="bit-phototag bit-phototag--mfk">Ab MFK</span>
		<?php endif; ?>
		<?php if ( $v['flag'] ) : ?>
			<span class="bit-phototag bit-phototag--accent"><?php echo esc_html( $v['flag'] ); ?></span>
		<?php endif; ?>
		<?php if ( $v['demo'] ) : ?>
			<span class="bit-phototag bit-phototag--demo">Demo · Testdaten</span>
		<?php endif; ?>
		<?php echo bit_vehicle_image( $v ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	</a>
	<div class="body">
		<div class="rowtop">
			<div>
				<h3><a href="<?php echo esc_url( $v['url'] ); ?>"><?php echo esc_html( $v['title'] ); ?></a></h3>
				<?php if ( $v['subtitle'] ) : ?><p class="sub"><?php echo esc_html( $v['subtitle'] ); ?></p><?php endif; ?>
			</div>
			<span class="price"><?php echo esc_html( $v['price_txt'] ); ?></span>
		</div>
		<ul class="specs">
			<?php if ( $v['year'] ) : ?><li><?php echo bit_icon( 'cal' ); // phpcs:ignore ?><?php echo esc_html( $v['year'] ); ?></li><?php endif; ?>
			<?php if ( $v['km_txt'] ) : ?><li><?php echo bit_icon( 'km' ); // phpcs:ignore ?><?php echo esc_html( $v['km_txt'] ); ?></li><?php endif; ?>
			<?php if ( $v['power'] ) : ?><li><?php echo bit_icon( 'ps' ); // phpcs:ignore ?><?php echo esc_html( $v['power'] ); ?></li><?php endif; ?>
			<?php if ( $v['gearbox'] ) : ?><li><?php echo bit_icon( 'gear' ); // phpcs:ignore ?><?php echo esc_html( $v['gearbox'] ); ?></li><?php endif; ?>
		</ul>
		<a class="bit-btn" href="<?php echo esc_url( $v['url'] ); ?>"><span>Details ansehen</span><span class="sr-only"> – <?php echo esc_html( $v['title'] ); ?></span></a>
	</div>
</article>
