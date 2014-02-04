<?php
/**
 * The sidebar containing the footer widget area and multilingual cloning.
 *
 * If no active widgets in this sidebar, it will be hidden completely.
 *
 * @package WordPress
 * @subpackage Twenty_Thirteen_xili
 * @since Twenty Thirteen 1.0
 */

if ( class_exists('xili_language') ) { // if temporary disabled
	$options = get_theme_xili_options();
	$curlang_suffix = ( the_curlang() == 'en_us' || the_curlang() == "" ) ? '' : '_'.the_curlang()  ;
} else {
	$curlang_suffix = '';
}
if ( $curlang_suffix != '' && !isset( $options['sidebar_'.'sidebar-1'] ) ) $curlang_suffix = '' ; //display default  - no clone

if ( is_active_sidebar( 'sidebar-1' . $curlang_suffix ) ) : ?>
	<div id="secondary" class="sidebar-container" role="complementary">
		<div class="widget-area">
			<?php dynamic_sidebar( 'sidebar-1' . $curlang_suffix ); ?>
		</div><!-- .widget-area -->
	</div><!-- #secondary -->
<?php endif; ?>