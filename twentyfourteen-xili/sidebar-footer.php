<?php
/**
 * The Footer Sidebar
 *
 * @package WordPress
 * @subpackage Twenty_Fourteen
 * @since Twenty Fourteen 1.0
 */
if ( class_exists('xili_language') ) { // if temporary disabled
	$options = get_theme_xili_options();
	$curlang_suffix = ( the_curlang() == 'en_us' || the_curlang() == "" ) ? '' : '_'.the_curlang()  ;
} else {
	$curlang_suffix = '';
}
if ( $curlang_suffix != '' && !isset( $options['sidebar_'.'sidebar-3'] ) ) $curlang_suffix = '' ; //display default  - no clone

if ( is_active_sidebar( 'sidebar-3' . $curlang_suffix ) ) : ?>
	<div id="supplementary">
		<div id="footer-sidebar" class="footer-sidebar widget-area" role="complementary">
			<?php dynamic_sidebar( 'sidebar-3' . $curlang_suffix ); ?>
		</div><!-- #footer-sidebar -->
	</div><!-- #supplementary -->
<?php endif; ?>
