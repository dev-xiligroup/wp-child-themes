<?php
/**
 * The template for displaying featured posts on the front page
 *
 * @package WordPress
 * @subpackage Twenty_Fourteen
 * @since Twenty Fourteen 1.0
 */

$post_id = get_the_ID();

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<a class="post-thumbnail" href="<?php the_permalink(); ?>">
	<?php
		// Output the featured image.
		if ( has_post_thumbnail() ) :
			if ( 'grid' == get_theme_mod( 'featured_content_layout' ) ) {
				the_post_thumbnail();
			} else {
				the_post_thumbnail( 'twentyfourteen-full-width' );
			}
		endif;
	?>
	</a>

	<header class="entry-header">
		<?php // Search meta grid-meta-display - more features added in twentyfourteen-xili 1.0.3
		$type_meta= get_post_meta( $post_id, 'grid-meta-display', true );
		if ( $type_meta && $type_meta != 'category' ) {
			switch ( $type_meta ) {

				case 'featured-sub-title'; // a text
				?>
				<div class="entry-meta">
				<span class="cat-links"><?php echo get_post_meta( $post_id, 'featured-sub-title', true ); ?></span>
				</div><!-- .entry-meta -->
				<?php
				break ;
				case 'excerpt'; // the excerpt as sub-title
				?>
				<div class="entry-meta">
				<span class="cat-links"><?php the_excerpt(); ?></span>
				</div><!-- .entry-meta -->
				<?php
				break ;
			}
		} else {
			if ( in_array( 'category', get_object_taxonomies( get_post_type() ) ) && twentyfourteen_categorized_blog() ) : ?>
				<div class="entry-meta">
				<span class="cat-links"><?php echo get_the_category_list( _x( ', ', 'Used between list items, there is a space after the comma.', 'twentyfourteen' ) ); ?></span>
				</div><!-- .entry-meta -->
		<?php endif;
		} ?>

		<?php the_title( '<h1 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">','</a></h1>' ); ?>
	</header><!-- .entry-header -->
</article><!-- #post-## -->
