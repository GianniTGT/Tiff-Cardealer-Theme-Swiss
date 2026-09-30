<?php
/** Fuss: Logo, Showroom, Kontakt, Seite, Firmenzeile. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
</main>
<footer class="sitefoot">
	<a class="logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="Start"><?php bit_logo( true ); ?></a>
	<div class="cols">
		<div>
			<h3>Showroom</h3>
			<p><?php echo esc_html( bit_info( 'street' ) ); ?><br><?php echo esc_html( bit_info( 'zip' ) . ' ' . bit_info( 'city_long' ) ); ?></p>
			<p class="num"><?php echo esc_html( bit_info( 'hours_week' ) ); ?><br><?php echo esc_html( bit_info( 'hours_sat' ) ); ?></p>
		</div>
		<div>
			<h3>Kontakt</h3>
			<p class="num" style="line-height:1.7">
				<?php echo bit_tel_link( 'phone1' ); // phpcs:ignore ?><br>
				<?php echo bit_tel_link( 'phone2' ); // phpcs:ignore ?><br>
				<a href="mailto:<?php echo esc_attr( bit_info( 'email' ) ); ?>"><?php echo esc_html( bit_info( 'email' ) ); ?></a>
			</p>
		</div>
		<div>
			<h3>Seite</h3>
			<div class="links">
				<?php foreach ( bit_nav_items( 'footer' ) as $item ) : ?>
					<a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['label'] ); ?></a>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
	<p class="legalline">
		<b><?php echo esc_html( bit_info( 'brand' ) ); ?></b> · <?php echo esc_html( bit_info( 'legal' ) ); ?> · <span class="num"><?php echo esc_html( bit_info( 'uid' ) ); ?></span> · Occasionen in der ganzen Schweiz · <?php echo esc_html( bit_info( 'city_long' ) ); ?>
		<?php if ( is_user_logged_in() ) : ?>
			· <a class="staff" href="<?php echo esc_url( admin_url() ); ?>">Verwaltung</a>
		<?php else : ?>
			· <a class="staff" href="<?php echo esc_url( wp_login_url() ); ?>" rel="nofollow">Login</a>
		<?php endif; ?>
	</p>
	<p class="credit">Diese Seite wurde gestaltet von <a href="https://tiff-software-solutions.com" target="_blank" rel="noopener">Tiff Software Solutions</a></p>
</footer>
<?php wp_footer(); ?>
</body>
</html>
