<?php
/**
 * The Sidebar containing the main widget area
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
if ( $curlang_suffix != '' && !isset( $options['sidebar_'.'sidebar-1'] ) ) $curlang_suffix = '' ; //display default  - no clone


?>
<div id="secondary">
	<?php
		$description = get_bloginfo( 'description', 'display' );
		if ( ! empty ( $description ) ) :
	?>
	<h2 class="site-description"><?php echo esc_html( $description ); ?></h2>
	<?php endif; ?>

	<?php if ( has_nav_menu( 'secondary' ) ) : ?>
	<nav role="navigation" class="navigation site-navigation secondary-navigation">
		<?php wp_nav_menu( array( 'theme_location' => 'secondary' ) ); ?>
	</nav>
	<?php endif; ?>

	<?php if ( is_active_sidebar( 'sidebar-1' . $curlang_suffix ) ) : ?>
	<div id="primary-sidebar" class="primary-sidebar widget-area" role="complementary">
		<?php
			do_action( 'before_sidebar' );
			dynamic_sidebar( 'sidebar-1' . $curlang_suffix );
		?>
	</div><!-- #primary-sidebar .primary-sidebar -->
	<?php endif; ?>
</div><!-- #secondary -->
