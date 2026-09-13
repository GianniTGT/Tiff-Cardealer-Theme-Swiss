<?php
/**
 * Make and model archives.
 *
 * WordPress does NOT route taxonomy archives through archive-car.php. The
 * hierarchy is taxonomy-{tax}.php, taxonomy.php, archive.php, index.php.
 * Without this file the make archives fell through to index.php and rendered
 * a plain post list instead of vehicle cards.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

require get_template_directory() . '/archive-car.php';
