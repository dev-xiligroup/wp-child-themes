<?php
/**
 * The template for displaying Tag pages.
 *
 * Used to display archive-type pages for posts in a tag.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Twenty_Thirteen
 * @since Twenty Thirteen 1.0
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<div id="content" class="site-content" role="main">

		<?php if ( have_posts() ) : ?>
			<header class="archive-header">
				<h1 class="archive-title"><?php printf( __( 'Tag Archives: %s', 'twentythirteen' ), single_tag_title( '', false ) ); ?></h1>

				<?php if ( tag_description() ) : // Show an optional tag description ?>
				<div class="archive-meta"><?php echo tag_description(); ?></div>
				<?php endif; ?>
				<div class="archive-meta"><?php 
				
				//echo serialize( is_tag() ); 
				
				//echo serialize( is_archive() );
				if ( class_exists ( 'xili_tidy_tags' )  ) {
					$format = ( class_exists ( 'xili_language' ) ) ? '%1$s [%2$s]' : '%1$s' ;
					echo xili_tidy_tags_group_links ( array( 'separator'=> ', ', 'lang' => $format)); 
					//if ( class_exists ( 'xili_language' ) && 'en_us' != the_curlang() )
						//echo ' - a link: <a href="' . xili_tidy_tag_in_other_lang("format=term_link&lang=en_US") . '" >in english</a> (test new XTT) '; 
				}
				?></div>
			</header><!-- .archive-header -->

			<?php /* The loop */ ?>
			<?php while ( have_posts() ) : the_post(); ?>
				<?php get_template_part( 'content', get_post_format() ); ?>
			<?php endwhile; ?>

			<?php twentythirteen_paging_nav(); ?>

		<?php else : ?>
			<?php get_template_part( 'content', 'none' ); ?>
		<?php endif; ?>

		</div><!-- #content -->
	</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>