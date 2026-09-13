<?php
/** Single vehicle page. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

get_header();

while ( have_posts() ) :
	the_post();
	$c      = das_get_car( get_the_ID() );
	$images = das_car_photos( get_the_ID() );
	// Keyed by the icon, not by the label: das_spec_icon() and the caption then come from
	// one entry, so a renamed caption cannot leave a spec wearing somebody else's picture.
	$specs  = array(
		'year'         => array( 'Year', $c['year'] ),
		'make'         => array( 'Make', $c['make'] ),
		'mileage'      => array( 'Mileage', $c['miles_txt'] ),
		'body'         => array( 'Body type', $c['body_txt'] ),
		'engine'       => array( 'Engine', $c['engine'] ),
		'fuel'         => array( 'Fuel', $c['fuel_txt'] ),
		'transmission' => array( 'Transmission', $c['trans_txt'] ),
		'ext_color'    => array( 'Exterior colour', $c['ext_color'] ),
		'int_color'    => array( 'Interior colour', $c['int_color'] ),
		'doors'        => array( 'Doors', $c['doors'] ),
		'owners'       => array( 'Previous owners', in_array( trim( (string) $c['owners'] ), array( '', '0' ), true ) ? '' : $c['owners'] ),
		'vin'          => array( 'VIN', $c['vin'] ),
		'condition'    => array( 'Condition', das_car_condition( get_the_ID() ) ),
	);
	?>
	<section>
		<div class="wrap">
			<p style="margin-bottom:18px"><a href="<?php echo esc_url( das_inventory_url() ); ?>" style="color:var(--amber-dark);font-weight:600">&larr; Back to inventory</a></p>

			<div class="car-head">
				<h1><?php the_title(); ?></h1>
				<span class="price"><?php echo esc_html( $c['price_txt'] ); ?><?php echo $c['plus'] ? ' <small style="font-size:14px;color:var(--steel)">' . esc_html( $c['plus'] ) . '</small>' : ''; ?></span>
			</div>

			<?php $highlights = das_car_highlights( get_the_ID() ); ?>
			<?php if ( $highlights ) : ?>
				<ul class="car-highlights">
					<?php foreach ( $highlights as $h ) : ?>
						<li><?php echo esc_html( $h ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<?php if ( $c['sold'] ) : ?>
				<div class="das-notice das-notice-err" style="margin-bottom:24px">This vehicle has been sold. Call us &mdash; we may have something similar arriving.</div>
			<?php endif; ?>

			<div class="car-grid">
				<div>
					<div class="car-gallery">
						<?php $many = count( $images ) > 1; ?>
						<div class="car-stage<?php echo $many ? ' is-slider' : ''; ?>" id="carStage"
							<?php if ( $many ) : ?> tabindex="0" role="group" aria-roledescription="carousel" aria-label="Vehicle photos"<?php endif; ?>>
							<?php if ( $images ) : ?>
								<?php foreach ( $images as $i => $img ) : ?>
									<img src="<?php echo esc_url( $img['full'] ); ?>"
										alt="<?php echo esc_attr( get_the_title() . ' — photo ' . ( $i + 1 ) ); ?>"
										<?php echo $i ? ' loading="lazy"' : ''; ?>>
								<?php endforeach; ?>
							<?php else : ?>
								<svg aria-hidden="true"><use href="#carIcon"/></svg>
							<?php endif; ?>
						</div>

						<?php if ( $many ) : ?>
							<button type="button" class="car-nav prev" id="galPrev" hidden aria-label="Previous photo">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 5 8 12l7 7"/></svg>
							</button>
							<button type="button" class="car-nav next" id="galNext" aria-label="Next photo">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 5 7 7-7 7"/></svg>
							</button>
							<div class="car-count" id="galCount" aria-live="polite">1 / <?php echo count( $images ); ?></div>
						<?php endif; ?>
					</div>

					<?php if ( $many ) : ?>
						<div class="car-thumbs" id="carThumbs">
							<?php foreach ( $images as $i => $img ) : ?>
								<button type="button" class="<?php echo 0 === $i ? 'on' : ''; ?>" data-i="<?php echo (int) $i; ?>" aria-label="Show photo <?php echo (int) $i + 1; ?>">
									<img src="<?php echo esc_url( $img['thumb'] ); ?>" alt="" loading="lazy">
								</button>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<?php $videos = das_car_video_embeds( get_the_ID() ); ?>
					<?php if ( $videos ) : ?>
						<div class="car-videos">
							<h2><?php echo count( $videos ) > 1 ? 'Videos' : 'Video'; ?></h2>
							<?php foreach ( $videos as $vi => $vsrc ) : ?>
								<div class="car-video">
									<iframe src="<?php echo esc_url( $vsrc ); ?>"
										title="<?php echo esc_attr( get_the_title() . ' — video ' . ( $vi + 1 ) ); ?>"
										loading="lazy" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen
										allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<?php if ( trim( get_the_content() ) ) : ?>
						<div class="car-desc">
							<h2>About this vehicle</h2>
							<?php the_content(); ?>
						</div>
					<?php endif; ?>

					<?php
					// Features sit in this column rather than full-width below it. The
					// aside — thirteen specs, two buttons and the payment estimator — runs
					// about 870px, and the gallery above runs about 620, so the page had a
					// quarter-metre of empty left column that Gianni marked on a screenshot.
					// This is the only block long enough to fill it with something a buyer
					// wants, and beside the specs is where they look for it anyway.
					$features = das_car_feature_sections( get_the_ID() );
					?>
					<?php if ( $features ) : ?>
						<div class="car-features">
							<h2>Features &amp; equipment</h2>
							<?php foreach ( $features as $section ) : ?>
								<?php $tid = 'feat-' . sanitize_title( $section['label'] ); ?>
								<div class="feat-tier<?php echo $section['star'] ? ' is-key' : ''; ?>">
									<h3><?php echo esc_html( $section['label'] ); ?></h3>
									<div class="feat-body" id="<?php echo esc_attr( $tid ); ?>">
										<ul class="feat-list">
											<?php foreach ( $section['items'] as $item ) : ?>
												<li><?php echo esc_html( $item ); ?></li>
											<?php endforeach; ?>
										</ul>
									</div>
									<?php if ( ! empty( $section['clamp'] ) && count( $section['items'] ) > 12 ) : ?>
										<?php /* Printed `hidden`, and the script below unhides it. With scripting
										   off — or for a crawler — the list is never clamped and the control
										   that would do nothing is never shown. Same rule as the reviews fold. */ ?>
										<button class="feat-toggle" type="button" hidden
											aria-controls="<?php echo esc_attr( $tid ); ?>" aria-expanded="true"
											data-more="Show all <?php echo (int) count( $section['items'] ); ?> features"
											data-less="Show fewer"></button>
									<?php endif; ?>
								</div>
							<?php endforeach; ?>
							<script>(function(){var r=document.currentScript.parentElement;Array.prototype.forEach.call(r.querySelectorAll('.feat-toggle'),function(b){var y=document.getElementById(b.getAttribute('aria-controls'));if(!y){return;}y.classList.add('is-clamped');b.hidden=false;b.setAttribute('aria-expanded','false');b.textContent=b.getAttribute('data-more');b.addEventListener('click',function(){var open=!y.classList.toggle('is-clamped');b.setAttribute('aria-expanded',String(open));b.textContent=open?b.getAttribute('data-less'):b.getAttribute('data-more');});});})();</script>
						</div>
					<?php endif; ?>
				</div>

				<aside class="car-aside">
					<ul class="spec-list">
						<?php foreach ( $specs as $key => $spec ) : ?>
							<?php
							list( $label, $value ) = $spec;
							if ( '' === trim( (string) $value ) || '&mdash;' === $value ) {
								continue;
							}
							?>
							<li class="spec-<?php echo esc_attr( $key ); ?>">
								<?php echo das_spec_icon( $key, $value ); ?>
								<span class="spec-txt">
									<span class="spec-k"><?php echo esc_html( $label ); ?></span>
									<span class="spec-v"><?php echo esc_html( $value ); ?></span>
								</span>
							</li>
						<?php endforeach; ?>
					</ul>
					<a class="btn btn-brand" style="width:100%" href="tel:<?php echo esc_attr( das_info( 'tel1' ) ); ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><use href="#phoneIcon"/></svg> Call <?php echo esc_html( das_info( 'phone1' ) ); ?></a>

					<?php if ( $c['price'] ) : ?>
						<div class="car-calc">
							<h3>Estimate a payment</h3>
							<label for="calcDown">Down payment ($)</label>
							<input type="number" id="calcDown" min="0" step="100" value="<?php echo (int) round( $c['price'] * 0.1 / 100 ) * 100; ?>">
							<div class="car-calc-row">
								<div>
									<label for="calcTerm">Term</label>
									<select id="calcTerm">
										<option value="36">36 mo</option>
										<option value="48">48 mo</option>
										<option value="60" selected>60 mo</option>
										<option value="72">72 mo</option>
									</select>
								</div>
								<div>
									<label for="calcApr">APR (%)</label>
									<input type="number" id="calcApr" min="0" max="30" step="0.1" value="8.5">
								</div>
							</div>
							<div class="car-calc-out"><span id="calcMonthly">&mdash;</span><small>/mo</small></div>
							<p class="car-calc-note">Estimate only, not a credit offer. Taxes, title and dealer fees are not included.</p>
						</div>
						<script>
						(function () {
							var price = <?php echo (int) $c['price']; ?>;
							var down = document.getElementById('calcDown');
							var term = document.getElementById('calcTerm');
							var apr  = document.getElementById('calcApr');
							var out  = document.getElementById('calcMonthly');
							if ( ! down || ! term || ! apr || ! out ) { return; }
							function money( n ) { return '$' + Math.round( n ).toLocaleString('en-US'); }
							function calc() {
								var d = Math.max( 0, parseFloat( down.value ) || 0 );
								var n = parseInt( term.value, 10 ) || 60;
								var r = ( parseFloat( apr.value ) || 0 ) / 100 / 12;
								var loan = Math.max( 0, price - d );
								if ( loan <= 0 ) { out.textContent = money( 0 ); return; }
								var pay = r > 0 ? loan * r / ( 1 - Math.pow( 1 + r, -n ) ) : loan / n;
								out.textContent = money( pay );
							}
							[ down, term, apr ].forEach( function ( el ) {
								el.addEventListener( 'input', calc );
								el.addEventListener( 'change', calc );
							} );
							calc();
						})();
						</script>
					<?php endif; ?>

					<?php
					// The enquiry form lives in this column rather than full-width below it.
					// Gianni's second pass on the symmetry: with Features moved left, the
					// left column outran the aside, and a form centred under both columns
					// still left the page ending on two different lines. Here the two
					// columns finish together, and the form sits where somebody already is
					// when they decide to ask — beside the price and the Call button.
					das_lead_form( array(
						'id'              => 'carenquiry',
						'title'           => 'Ask about this vehicle',
						'intro'           => 'Send us a note and we will get back to you the same business day.',
						'car_id'          => get_the_ID(),
						'show_car_select' => false,
						'source'          => 'vehicle',
						'button'          => 'Send enquiry',
					) );
					?>
				</aside>
			</div>

			<?php
			// Four, not three: the grid is repeat(auto-fill, minmax(268px,1fr)) and at a
			// 1140px wrap that is exactly four columns, so three left a hole on the right.
			$similar = das_similar_cars( $c, 4 );
			?>
			<?php if ( $similar ) : ?>
				<div class="car-similar">
					<h2>Similar vehicles</h2>
					<div class="grid">
						<?php foreach ( $similar as $s ) { echo das_car_card( $s ); } ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</section>
<?php endwhile; ?>

<?php get_footer(); ?>