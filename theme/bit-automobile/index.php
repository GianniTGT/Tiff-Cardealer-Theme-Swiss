<?php
/** Rueckfall fuer alles, was keine eigene Vorlage hat. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
?>
<div class="intro">
	<h1 class="t-page"><?php echo esc_html( is_search() ? 'Suche' : wp_get_document_title() ); ?></h1>
</div>
<?php bit_flow(); ?>
<section class="sec">
	<div class="content">
		<?php
		if ( have_posts() ) {
			while ( have_posts() ) {
				the_post();
				printf( '<h2><a href="%s">%s</a></h2>', esc_url( get_permalink() ), esc_html( get_the_title() ) );
				the_excerpt();
			}
		} else {
			echo '<p>Hier gibt es nichts.</p>';
		}
		?>
	</div>
</section>
<?php
get_footer();
