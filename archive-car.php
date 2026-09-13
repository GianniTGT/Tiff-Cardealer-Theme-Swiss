<?php
/**
 * Inventory archive.
 *
 * The car CPT archive is rendered from das_get_cars() rather than the main loop, for the
 * same reason das_get_cars() avoids meta_query: the plugin writes car_* meta as SQL NULL
 * and any meta comparison against NULL silently drops every row. Filtering happens in PHP
 * and every filter is a real GET parameter, so the page works with JavaScript switched
 * off and any result can be sent to somebody as a link.
 *
 * The controls are built from the vehicles actually on the lot (das_filter_options), not
 * from a fixed list. On a lot of fourteen cars, a filter offering choices nothing matches
 * is worse than no filter at all.
 *
 * Taxonomy archives (carproducer) still use the normal loop.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

get_header();

$is_tax = is_tax( 'carproducer' );
$cars   = $is_tax ? array() : das_get_cars( array( 'posts_per_page' => 200 ) );

$g = function ( $key ) {
	return isset( $_GET[ $key ] ) ? sanitize_text_field( wp_unslash( $_GET[ $key ] ) ) : '';
};
$filters = array(
	'q'         => $g( 'q' ),
	'body'      => isset( $_GET['body'] ) ? sanitize_key( wp_unslash( $_GET['body'] ) ) : '',
	'make'      => $g( 'make' ),
	'fuel'      => $g( 'fuel' ),
	'trans'     => $g( 'trans' ),
	'price_max' => (int) $g( 'price_max' ),
	'year_min'  => (int) $g( 'year_min' ),
	'sort'      => $g( 'sort' ),
);
if ( ! array_key_exists( $filters['sort'], das_sort_options() ) ) { $filters['sort'] = ''; }

$options = $is_tax ? array() : das_filter_options( $cars );

// The body chips count what the *other* filters leave behind, so a chip never promises
// vehicles the active make or price would then remove.
$without_body = $is_tax ? array() : das_filter_cars( $cars, array_merge( $filters, array( 'body' => '' ) ) );
$types = array();
foreach ( $without_body as $c ) {
	if ( ! $c['body'] ) { continue; }
	if ( ! isset( $types[ $c['body'] ] ) ) {
		$types[ $c['body'] ] = array( 'label' => das_label( $c['body'], das_body_types() ), 'n' => 0 );
	}
	$types[ $c['body'] ]['n']++;
}
if ( $filters['body'] && ! isset( $types[ $filters['body'] ] ) ) { $filters['body'] = ''; }

$shown  = $is_tax ? array() : das_filter_cars( $cars, $filters );
$active = array_filter( $filters, function ( $v, $k ) { return 'sort' !== $k && '' !== $v && 0 !== $v; }, ARRAY_FILTER_USE_BOTH );

$heading = $is_tax
	? single_term_title( '', false )
	: ( $filters['body'] ? $types[ $filters['body'] ]['label'] . ' for sale' : 'Our inventory' );

/** Keeps the other filters when one control is changed. */
$hidden = function ( $except ) use ( $filters ) {
	foreach ( $filters as $k => $v ) {
		if ( $k === $except || '' === $v || 0 === $v ) { continue; }
		printf( '<input type="hidden" name="%s" value="%s">', esc_attr( $k ), esc_attr( $v ) );
	}
};
?>
<div class="page-hero">
	<div class="wrap">
		<h1><?php echo esc_html( $heading ); ?></h1>
		<p>Every vehicle is inspected before it goes on sale. Call <a href="tel:<?php echo esc_attr( das_info( 'tel1' ) ); ?>"><?php echo esc_html( das_info( 'phone1' ) ); ?></a> to confirm availability.</p>
	</div>
</div>

<section>
	<div class="wrap">
		<?php if ( ! $is_tax ) : ?>
			<form class="inv-filter" method="get" action="<?php echo esc_url( das_inventory_url() ); ?>" role="search">
				<div class="inv-filter-row">
					<label class="inv-search">
						<span class="screen-reader-text">Search the inventory</span>
						<input type="search" name="q" value="<?php echo esc_attr( $filters['q'] ); ?>"
						       placeholder="Search make, model, year or colour">
					</label>

					<?php if ( ! empty( $options['make'] ) ) : ?>
						<label>
							<span class="screen-reader-text">Make</span>
							<select name="make">
								<option value="">Any make</option>
								<?php foreach ( $options['make'] as $name => $n ) : ?>
									<option value="<?php echo esc_attr( $name ); ?>" <?php selected( $filters['make'], $name ); ?>>
										<?php echo esc_html( $name ); ?> (<?php echo (int) $n; ?>)
									</option>
								<?php endforeach; ?>
							</select>
						</label>
					<?php endif; ?>

					<?php if ( ! empty( $options['prices'] ) ) : ?>
						<label>
							<span class="screen-reader-text">Maximum price</span>
							<select name="price_max">
								<option value="">Any price</option>
								<?php foreach ( $options['prices'] as $p ) : ?>
									<option value="<?php echo (int) $p; ?>" <?php selected( $filters['price_max'], $p ); ?>>
										Under $<?php echo esc_html( number_format_i18n( $p ) ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</label>
					<?php endif; ?>

					<?php if ( count( $options['years'] ) > 1 ) : ?>
						<label>
							<span class="screen-reader-text">Earliest year</span>
							<select name="year_min">
								<option value="">Any year</option>
								<?php foreach ( $options['years'] as $y ) : ?>
									<option value="<?php echo (int) $y; ?>" <?php selected( $filters['year_min'], $y ); ?>>
										<?php echo (int) $y; ?> or newer
									</option>
								<?php endforeach; ?>
							</select>
						</label>
					<?php endif; ?>

					<?php if ( ! empty( $options['fuel'] ) ) : ?>
						<label>
							<span class="screen-reader-text">Fuel</span>
							<select name="fuel">
								<option value="">Any fuel</option>
								<?php foreach ( $options['fuel'] as $name => $n ) : ?>
									<option value="<?php echo esc_attr( $name ); ?>" <?php selected( $filters['fuel'], $name ); ?>>
										<?php echo esc_html( $name ); ?> (<?php echo (int) $n; ?>)
									</option>
								<?php endforeach; ?>
							</select>
						</label>
					<?php endif; ?>

					<?php if ( ! empty( $options['trans'] ) ) : ?>
						<label>
							<span class="screen-reader-text">Transmission</span>
							<select name="trans">
								<option value="">Any transmission</option>
								<?php foreach ( $options['trans'] as $name => $n ) : ?>
									<option value="<?php echo esc_attr( $name ); ?>" <?php selected( $filters['trans'], $name ); ?>>
										<?php echo esc_html( $name ); ?> (<?php echo (int) $n; ?>)
									</option>
								<?php endforeach; ?>
							</select>
						</label>
					<?php endif; ?>

					<?php if ( $filters['body'] ) : ?>
						<input type="hidden" name="body" value="<?php echo esc_attr( $filters['body'] ); ?>">
					<?php endif; ?>

					<button class="btn" type="submit">Search</button>
					<?php if ( $active ) : ?>
						<a class="inv-clear" href="<?php echo esc_url( das_inventory_url() ); ?>">Clear</a>
					<?php endif; ?>
				</div>
			</form>

			<?php if ( $types ) : ?>
				<div class="filters" role="group" aria-label="Filter by body type">
					<a class="chip<?php echo $filters['body'] ? '' : ' active'; ?>"
					   href="<?php echo esc_url( remove_query_arg( 'body', add_query_arg( $active, das_inventory_url() ) ) ); ?>">
						All <span class="chip-n"><?php echo count( $without_body ); ?></span>
					</a>
					<?php foreach ( $types as $slug => $t ) : ?>
						<a class="chip<?php echo $filters['body'] === $slug ? ' active' : ''; ?>"
						   href="<?php echo esc_url( add_query_arg( array_merge( $active, array( 'body' => $slug ) ), das_inventory_url() ) ); ?>">
							<?php echo esc_html( $t['label'] ); ?> <span class="chip-n"><?php echo (int) $t['n']; ?></span>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<div class="inv-meta">
				<p class="inv-count">
					<strong><?php echo count( $shown ); ?></strong>
					<?php echo 1 === count( $shown ) ? 'vehicle' : 'vehicles'; ?>
					<?php if ( count( $shown ) !== count( $cars ) ) : ?>
						<span class="inv-of">of <?php echo count( $cars ); ?></span>
					<?php endif; ?>
				</p>
				<form class="inv-sort" method="get" action="<?php echo esc_url( das_inventory_url() ); ?>">
					<?php $hidden( 'sort' ); ?>
					<label>
						<span class="screen-reader-text">Sort by</span>
						<select name="sort" onchange="this.form.submit()">
							<?php foreach ( das_sort_options() as $value => $label ) : ?>
								<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $filters['sort'], $value ); ?>>
									<?php echo esc_html( $label ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</label>
					<noscript><button class="btn-ghost" type="submit">Sort</button></noscript>
				</form>
			</div>
		<?php endif; ?>

		<div class="grid">
			<?php
			if ( $is_tax ) {
				if ( have_posts() ) {
					while ( have_posts() ) {
						the_post();
						$c = das_get_car( get_the_ID() );
						if ( $c ) { echo das_car_card( $c ); }
					}
				} else {
					echo '<p class="grid-empty">Nothing here right now &mdash; call us, new inventory arrives weekly.</p>';
				}
			} elseif ( $shown ) {
				foreach ( $shown as $c ) { echo das_car_card( $c ); }
			} elseif ( $active ) {
				// A dead end with no way out of it is the thing this page must not become.
				printf(
					'<p class="grid-empty">No vehicle matches that. <a href="%s">Show all %d vehicles</a> &mdash; or call us, new inventory arrives weekly.</p>',
					esc_url( das_inventory_url() ),
					count( $cars )
				);
			} else {
				echo '<p class="grid-empty">No vehicles listed right now &mdash; call us, new inventory arrives weekly.</p>';
			}
			?>
		</div>

		<?php if ( $is_tax ) { das_pagination(); } ?>
	</div>
</section>

<?php get_footer(); ?>
