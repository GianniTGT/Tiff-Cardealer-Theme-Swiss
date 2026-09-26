<?php
/** Jede andere Seite: Titel, Linie, Inhalt. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
while ( have_posts() ) :
	the_post();
	?>
	<div class="intro">
		<h1 class="t-page"><?php the_title(); ?></h1>
	</div>
	<?php bit_flow(); ?>
	<section class="sec">
		<div class="content"><?php the_content(); ?></div>
	</section>
	<?php
endwhile;
get_footer();
