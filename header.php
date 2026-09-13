<?php
/** Theme header: top bar, static nav, shared SVG symbols. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?> id="top">
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#main">Skip to content</a>

<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
	<defs>
		<symbol id="carIcon" viewBox="0 0 64 64">
			<path d="M8 38l4-12c1-3 3-4 6-4h28c3 0 5 1 6 4l4 12" stroke="#8FB3FF" stroke-width="2.5" fill="none" stroke-linecap="round"/>
			<rect x="5" y="38" width="54" height="10" rx="3" stroke="#AAB8D9" stroke-width="2.5" fill="none"/>
			<circle cx="17" cy="50" r="5" stroke="#AAB8D9" stroke-width="2.5" fill="none"/>
			<circle cx="47" cy="50" r="5" stroke="#AAB8D9" stroke-width="2.5" fill="none"/>
		</symbol>
		<symbol id="phoneIcon" viewBox="0 0 24 24">
			<path d="M6.6 10.8a15.1 15.1 0 0 0 6.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.2.4 2.4.6 3.6.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1C10.6 21 3 13.4 3 4c0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.3.2 2.5.6 3.6.1.4 0 .8-.2 1l-2.3 2.2z"/>
		</symbol>
	</defs>
</svg>

<header class="site">
	<div class="wrap nav">
		<a class="logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( das_info( 'name' ) ); ?> home">
			<?php das_logo_img(); ?>
			<span class="logo-text"><b><?php echo esc_html( das_info( 'name' ) ); ?></b></span>
		</a>

		<button class="hamb" type="button" aria-label="Open menu" aria-expanded="false" aria-controls="menu">
			<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
		</button>

		<nav class="menu" id="menu">
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu( array(
					'theme_location' => 'primary',
					'container'      => false,
					'depth'          => 2,
					'items_wrap'     => '<ul>%3$s</ul>',
				) );
			} else {
				das_fallback_menu();
			}
			?>
			<?php
			// The number lives in the aside on a vehicle page, bigger and beside the price.
			// Carrying it here as well put the same Call on one page twice, in two sizes.
			if ( ! is_singular( 'car' ) ) : ?>
			<a class="btn btn-brand" href="tel:<?php echo esc_attr( das_info( 'tel1' ) ); ?>">
				<svg viewBox="0 0 24 24" aria-hidden="true"><use href="#phoneIcon"/></svg>
				<?php echo esc_html( das_info( 'phone1' ) ); ?>
			</a>
			<?php endif; ?>
		</nav>
	</div>
</header>
<main id="main">