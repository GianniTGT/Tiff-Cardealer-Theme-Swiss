<?php
/** Kopf: Logo, vier Punkte, Telefon. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" href="<?php echo esc_url( bit_asset( 'logo/favicon.png' ) ); ?>">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip" href="#inhalt">Zum Inhalt</a>
<header class="bit-sitehead">
	<div class="in">
		<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( bit_info( 'brand' ) ); ?> – Start"><?php bit_logo(); ?></a>
		<nav aria-label="Hauptnavigation">
			<?php foreach ( bit_nav_items( 'primary' ) as $item ) : ?>
				<a href="<?php echo esc_url( $item['url'] ); ?>"<?php echo bit_nav_is_current( $item ) ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $item['label'] ); ?></a>
			<?php endforeach; ?>
			<?php echo bit_tel_link( 'phone1', 'bit-btn bit-btn--blue bit-btn--sm tel' ); // phpcs:ignore ?>
		</nav>
	</div>
</header>
<main id="inhalt">
