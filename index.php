<?php
/** Fallback template for every request without a more specific template. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

get_header();

$title = 'Downtown Auto Sales';
if ( is_search() ) {
	$title = 'Search results';
} elseif ( is_404() ) {
	$title = 'Page not found';
} elseif ( is_archive() ) {
	$title = get_the_archive_title();
} elseif ( is_home() && ! is_front_page() ) {
	$title = single_post_title( '', false );
} elseif ( is_singular() ) {
	$title = get_the_title();
}
?>
<div class="page-hero">
	<div class="wrap">
		<h1><?php echo wp_kses_post( $title ); ?></h1>
	</div>
</div>

<section>
	<div class="wrap" style="max-width:860px">
		<?php if ( have_posts() ) : ?>
			<?php if ( is_singular() ) : ?>
				<?php while ( have_posts() ) : the_post(); ?>
					<article class="entry">
						<?php the_content(); ?>
						<?php wp_link_pages(); ?>
					</article>
				<?php endwhile; ?>
			<?php else : ?>
				<div class="entry">
					<?php while ( have_posts() ) : the_post(); ?>
						<h2 style="margin-top:0"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						<?php the_excerpt(); ?>
						<hr style="border:none;border-top:1px solid var(--line);margin:26px 0">
					<?php endwhile; ?>
				</div>
				<?php das_pagination(); ?>
			<?php endif; ?>
		<?php else : ?>
			<div class="entry">
				<p>We couldn't find that page. Try our <a href="<?php echo esc_url( das_inventory_url() ); ?>">inventory</a>, or call us on <a href="tel:<?php echo esc_attr( das_info( 'tel1' ) ); ?>"><?php echo esc_html( das_info( 'phone1' ) ); ?></a>.</p>
				<?php get_search_form(); ?>
			</div>
		<?php endif; ?>
	</div>
</section>

<?php get_footer(); ?>