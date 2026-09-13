<?php
/** Search form, styled to match the theme. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<form role="search" method="get" class="das-search" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="das-s">Search vehicles</label>
	<input type="search" id="das-s" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="Search vehicles&hellip;">
	<button type="submit" class="btn btn-brand">Search</button>
</form>
