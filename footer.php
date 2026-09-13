<?php
/** Theme footer: CTA band, footer columns, legal line. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
</main>

<?php
// The closing call-to-action is a good last word on the home page and the inventory,
// and a third repeat of the same phone number on a vehicle page — which already carries
// it in the header and again as the aside's Call button, above the enquiry form. So it
// stands down on a single vehicle and nowhere else.
if ( ! is_singular( 'car' ) ) :
?>
<section class="band band-brand">
	<div class="wrap">
		<div>
			<h2>Found something you like?</h2>
			<p>Call ahead and we'll have it warmed up and ready for a test drive.</p>
		</div>
		<a class="btn btn-light" href="tel:<?php echo esc_attr( das_info( 'tel1' ) ); ?>">
			<svg viewBox="0 0 24 24" aria-hidden="true"><use href="#phoneIcon"/></svg>
			<?php echo esc_html( das_info( 'phone1' ) ); ?>
		</a>
	</div>
</section>
<?php endif; ?>

<footer>
	<div class="wrap">
		<div class="foot-grid">
			<div>
				<a class="logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<?php das_logo_img(); ?>
					<span class="logo-text"><b style="color:#fff"><?php echo esc_html( das_info( 'name' ) ); ?></b></span>
				</a>
				<p class="foot-slogan">Dependable. Alaskan. Straightforward.</p>
				<p style="margin-top:10px;max-width:300px">A small Alaskan business helping our neighbors find the right vehicle &mdash; first car, family car, or work truck.</p>
				<div class="socials">
					<a href="https://wa.me/<?php echo esc_attr( ltrim( das_info( 'tel1' ), '+' ) ); ?>" aria-label="WhatsApp" target="_blank" rel="noopener"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15L2 22l5.2-1.4A10 10 0 1 0 12 2zm0 18.2c-1.5 0-3-.4-4.3-1.2l-.3-.2-3.1.8.8-3-.2-.3A8.2 8.2 0 1 1 12 20.2zm4.6-6.1c-.3-.1-1.5-.7-1.7-.8-.2-.1-.4-.1-.6.1-.2.3-.6.8-.8 1-.1.2-.3.2-.5.1a6.7 6.7 0 0 1-3.3-2.9c-.3-.4 0-.5.1-.7l.4-.5c.1-.2.1-.3.2-.5 0-.2 0-.4-.1-.5l-.8-1.9c-.2-.5-.4-.4-.6-.4h-.5c-.2 0-.5.1-.7.3-.2.3-.9.9-.9 2.2s.9 2.5 1.1 2.7c.1.2 1.9 2.9 4.5 4a15 15 0 0 0 1.5.6c.6.2 1.2.2 1.7.1.5-.1 1.5-.6 1.7-1.2.2-.6.2-1.1.2-1.2-.1-.1-.3-.2-.6-.3z"/></svg></a>
					<a href="mailto:<?php echo esc_attr( das_info( 'email' ) ); ?>" aria-label="Email"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2zm8 7.4L20 7H4l8 5.4zM4 9.2V17h16V9.2l-8 5.4-8-5.4z"/></svg></a>
				</div>
			</div>
			<div>
				<h4>Explore</h4>
				<ul>
					<li><a href="<?php echo esc_url( das_inventory_url() ); ?>">Inventory</a></li>
					<li><a href="<?php echo esc_url( home_url( '/#about' ) ); ?>">Our story</a></li>
					<li><a href="<?php echo esc_url( home_url( '/#financing' ) ); ?>">Financing</a></li>
					<li><a href="<?php echo esc_url( home_url( '/#visit' ) ); ?>">Visit &amp; hours</a></li>
					<li><a href="<?php echo esc_url( das_contact_url() ); ?>">Contact</a></li>
				</ul>
			</div>
			<div>
				<h4>Popular</h4>
				<ul>
					<?php foreach ( array( 'suv' => 'SUVs', 'pickup_truck' => 'Pickup trucks', 'sedan' => 'Sedans', 'hatchback' => 'Hatchbacks' ) as $slug => $label ) : ?>
						<li><a href="<?php echo esc_url( add_query_arg( 'body', $slug, das_inventory_url() ) ); ?>"><?php echo esc_html( $label ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</div>
			<div>
				<h4>Contact</h4>
				<ul>
					<li><?php echo esc_html( das_info( 'street' ) ); ?><br><?php echo esc_html( das_info( 'city' ) . ', ' . das_info( 'state' ) . ' ' . das_info( 'zip' ) ); ?></li>
					<li><a href="tel:<?php echo esc_attr( das_info( 'tel1' ) ); ?>"><?php echo esc_html( das_info( 'phone1' ) ); ?></a></li>
					<li><a href="tel:<?php echo esc_attr( das_info( 'tel2' ) ); ?>"><?php echo esc_html( das_info( 'phone2' ) ); ?></a></li>
					<li><a href="mailto:<?php echo esc_attr( das_info( 'email' ) ); ?>"><?php echo esc_html( das_info( 'email' ) ); ?></a></li>
					<?php if ( das_info( 'email2' ) ) : ?><li><a href="mailto:<?php echo esc_attr( das_info( 'email2' ) ); ?>"><?php echo esc_html( das_info( 'email2' ) ); ?></a></li><?php endif; ?>
				</ul>
			</div>
		</div>
		<div class="foot-bottom">
			<span>&copy; <?php echo esc_html( das_info( 'founded' ) ); ?>&ndash;<?php echo esc_html( wp_date( 'Y' ) ); ?> <?php echo esc_html( das_info( 'legal' ) ); ?>. All rights reserved.</span>
			<span><?php echo esc_html( das_info( 'addr' ) ); ?></span>
			<?php if ( get_privacy_policy_url() ) : ?><span><a href="<?php echo esc_url( get_privacy_policy_url() ); ?>">Privacy policy</a></span><?php endif; ?>
			<span class="foot-credit"><a class="tiff-credit" href="<?php echo esc_url( apply_filters( 'das_designer_url', 'https://tiff-software-solutions.com' ) ); ?>" rel="noopener"><img src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/tiff-mark-footer.svg' ); ?>" alt="" width="13" height="14" loading="lazy"><span>Site by <b>TIFF</b> Software Solutions</span></a></span>
			<span class="foot-staff">
				<?php if ( is_user_logged_in() ) : ?>
					<a href="<?php echo esc_url( admin_url() ); ?>">Dashboard</a> &middot;
					<a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>">Log out</a>
				<?php else : ?>
					<a href="<?php echo esc_url( wp_login_url() ); ?>" rel="nofollow">Staff login</a>
				<?php endif; ?>
			</span>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>