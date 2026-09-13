<?php
/** Static pages. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<div class="page-hero">
		<div class="wrap"><h1><?php the_title(); ?></h1></div>
	</div>
	<section>
		<div class="wrap" style="max-width:860px">
			<article class="entry">
				<?php the_content(); ?>
				<?php wp_link_pages(); ?>
			</article>
		</div>
	</section>
<?php endwhile; ?>

<?php get_footer(); ?>