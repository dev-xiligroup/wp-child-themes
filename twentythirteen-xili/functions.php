<?php

// dev.xiligroup.com - msc - 2013-03-03 - initial release
// dev.xiligroup.com - msc - 2013-05-28 - public release
// dev.xiligroup.com - msc - 2013-07-15 - more options to propagate - see 2013-xili example
// 1.0 - 2013-08-20 - first downloadable version - http://2013.extend.xiligroup.org
// 1.0.2 - 2013-10-10 - add option for multilingual bk banner
// 1.1.0 - 2013-11-03 - aligned to parent 2013 v 1.1
// 1.1.1 - 2013-11-03 - improved permalinks options
// 1.1.2 - 2013-11-11 - fixes permalinks options
// 1.1.3 - 2014-01-08 - fixes wp_title issue with new filter twentythirteen_xili_wp_title
// 1.1.4 - 2014-01-19 - fixes require_once of multilingual-functions.php (thanks to Herold) - add is_xili_adjacent_filterable (reserved future uses and in class embedding)
// 1.1.5 - 2014-02-09 - Need XL 2.10.0+ - Adaptated for new class of permalinks

define( 'TWENTYTHIRTEEN_XILI_VER', '1.1.5'); // as parent style.css

// main initialisation functions

function twentythirteen_xilidev_setup () {

	$theme_domain = 'twentythirteen';

	load_theme_textdomain( $theme_domain, STYLESHEETPATH . '/langs' ); // now use .mo of child

	$xl_required_version = false;

	$minimum_xl_version = '2.9.9';

	if ( class_exists('xili_language') ) { // if temporary disabled

		$xl_required_version = version_compare ( XILILANGUAGE_VER, $minimum_xl_version, '>' );

		global $xili_language;

		$xili_language_includes_folder = $xili_language->plugin_path .'xili-includes';

		$xili_functionsfolder = get_stylesheet_directory() . '/functions-xili' ;

		if ( file_exists( $xili_functionsfolder . '/multilingual-classes.php') ) {
			require_once ( $xili_functionsfolder . '/multilingual-classes.php' ); // xili-options created by developers in child theme in priority

		} elseif ( file_exists( $xili_language_includes_folder . '/theme-multilingual-classes.php') ) {
			require_once ( $xili_language_includes_folder . '/theme-multilingual-classes.php' ); // ref xili-options based in plugin
		}

	 	if ( file_exists( $xili_functionsfolder . '/multilingual-functions.php') ) {
			require_once ( $xili_functionsfolder . '/multilingual-functions.php' );
		}

		global $xili_language_theme_options ; // used on both side
	// Args dedicaced to this theme named Twenty Thirteen
		$xili_args = array (
	 		'customize_clone_widget_containers' => true, // comment or set to true to clone widget containers
	 		'settings_name' => 'xili_2013_theme_options', // name of array saved in options table
	 		'theme_name' => 'Twenty Thirteen',
	 		'theme_domain' => $theme_domain,
	 		'child_version' => TWENTYTHIRTEEN_XILI_VER
		);

		if ( is_admin() ) {

		// Admin args dedicaced to this theme

			$xili_admin_args = array_merge ( $xili_args, array (
		 		'customize_adds' => true, // add settings in customize page
		 		'customize_addmenu' => false, // done by 2013
		 		'capability' => 'edit_theme_options',
		 		'authoring_options_admin' => true,
		 		// possible to adapt propagate options - here - as example - add post_content / post_excerpt to other default values - 2.8.10
		 		'propagate_options_default' => array( 'post_content' => array ( 'default'=> '1', 'data' => 'post' ), 'post_excerpt' => array ( 'default'=> '1', 'data' => 'post' ) ),
		 		'propagate_options' => array (
							'post_content' => array ('name' => __('Post Content', $theme_domain ),
							'description' => __('Copy Post Content.', $theme_domain)
							)),
							array (
							'post_excerpt' => array ('name' => __('Post Excerpt', $theme_domain ),
							'description' => __('Copy Post Excerpt.', $theme_domain)
							)),
			) );

			if ( class_exists ( 'xili_language_theme_options_admin' )  ) {
				$xili_language_theme_options = new xili_language_theme_options_admin ( $xili_admin_args );
				$class_ok = true ;
			} else {
				$class_ok = false ;
			}


		} else { // visitors side - frontend

			if ( class_exists ( 'xili_language_theme_options' )  ) {
				$xili_language_theme_options = new xili_language_theme_options ( $xili_args );
				$class_ok = true ;
			} else {
				$class_ok = false ;
			}
		}

		$xili_theme_options = get_theme_xili_options() ;
		// to collect checked value in xili-options of theme
		if ( file_exists( $xili_functionsfolder . '/multilingual-permalinks.php') && $xili_language->is_permalink && isset ( $xili_theme_options['perma_ok'] ) && $xili_theme_options['perma_ok'] ) {
			require_once ( $xili_functionsfolder . '/multilingual-permalinks.php' ); // require subscribing premium services
		}

	}

	// errors and installation informations

	if ( ! class_exists( 'xili_language' ) ) {

		$msg = '
		<div class="error">
			<p>' . sprintf ( __('The %s child theme requires xili-language plugin installed and activated', $theme_domain ), get_option( 'current_theme' ) ).'</p>
		</div>';

	} elseif ( $class_ok === false )  {

		$msg = '
		<div class="error">
			<p>' . sprintf ( __('The %s child theme requires <em>xili_language_theme_options</em> class to set multilingual features.', $theme_domain ), get_option( 'current_theme' ) ).'</p>
		</div>';

	} elseif ( $xl_required_version )  {

		$msg = '
		<div class="updated">
			<p>' . sprintf ( __('The %s child theme was successfully activated with xili-language.', $theme_domain ), get_option( 'current_theme' ) ).'</p>
		</div>';

	} else {

		$msg = '
		<div class="error">
			<p>' . sprintf ( __('The %1$s child theme requires xili-language version %2$s+', $theme_domain ), get_option( 'current_theme' ), $minimum_xl_version ).'</p>
		</div>';
	}
	// after activation and in themes list
	if ( isset( $_GET['activated'] ) || ( ! isset( $_GET['activated'] ) && ( ! $xl_required_version || ! $class_ok ) ) )
		add_action( 'admin_notices', $c = create_function( '', 'echo "' . addcslashes( $msg, '"' ) . '";' ) );

	// end errors...
	// new filter added at end - 2014-01-08
	remove_filter( 'wp_title', 'twentythirteen_wp_title', 10, 2 );
}
add_action( 'after_setup_theme', 'twentythirteen_xilidev_setup', 11 ); // after parent functions


function xili_customize_js_footer () {

	wp_enqueue_script( 'customize-xili-js-footer', get_stylesheet_directory_uri(). '/functions-xili' . '/js/xili_theme_customizer.js' , array( 'customize-preview' ), TWENTYTHIRTEEN_XILI_VER, true );

}
// need to be here not as hook not in class
add_action( 'customize_preview_init', 'xili_customize_js_footer', 9  ); // before parent 2013 to be in footer

function twentythirteen_xilidev_setup_custom_header () {

	// %2$s = in child
	register_default_headers( array(
		'xili2013' => array(

			'url'           => '%2$s/images/headers/xili-2013.jpg',
			'thumbnail_url' => '%2$s/images/headers/xili-2013-thumbnail.jpg',
			'description'   => _x( '2013 by xili', 'header image description', 'twentythirteen' )
		))
	);

	$args = array(
		// Text color and image (empty to use none).
		'default-text-color'     => 'fffff0', // diff of parent
		'default-image'          => '%2$s/images/headers/xili-2013.jpg',

		// Set height and width, with a maximum value for the width.
		'height'                 => 230,
		'width'                  => 1600,

		// Callbacks for styling the header and the admin preview.
		'wp-head-callback'       => 'twentythirteen_xili_header_style',
		'admin-head-callback'    => 'twentythirteen_admin_header_style',
		'admin-preview-callback' => 'twentythirteen_admin_header_image',
	);

	add_theme_support( 'custom-header', $args ); // need 8 in add_action to overhide parent

}
add_action( 'after_setup_theme', 'twentythirteen_xilidev_setup_custom_header', 9 );


add_action("admin_head-appearance_page_custom-header", "twentythirteen_xili_header_help", 15);

function twentythirteen_xili_header_help ( ) {
	global $xili_language_theme_options;
	$header_setting_url = admin_url('/themes.php?page='. $xili_language_theme_options->settings_name );

	get_current_screen()->add_help_tab( array(
			'id'      => 'set-header-image-xili',
			'title'   => __('Multilingual Header Image in 2013-xili', 'twentythirteen'),
			'content' =>
				'<p>' . __( 'You can set a custom image header for your site according each current language. When the language changes, the header image will change. The default header image is assigned to unknown unaffected language.', 'twentythirteen' ) . '</p>' .
				'<p>' . sprintf( __( 'The images will be assigned to the language in the %1$sXili-Options%2$s  Appearance settings page.', 'twentythirteen'),'<a href="'.$header_setting_url.'">' ,'</a>' ). '</p>'
		) );

}

// function twentythirteen_header_style() for xili
function twentythirteen_xili_header_style () {

	$header_image_url = get_header_image();
	$text_color   = get_header_textcolor();

	// If no custom options for text are set, let's bail.
	if ( empty( $header_image_url ) && $text_color == get_theme_support( 'custom-header', 'default-text-color' ) )
		return;
	$xili_theme_options = get_theme_xili_options() ;
	// If we get this far, we have custom styles.
	?>
	<style type="text/css" id="twentythirteen-header-css">
	<?php
		if ( ! empty( $header_image_url ) ) :
			if ( class_exists ( 'xili_language' ) && isset ( $xili_theme_options['xl_header'] ) &&  $xili_theme_options['xl_header'] ) {
				global $xili_language, $xili_language_theme_options ;
				// check if image exists in current language
				// 2013-10-10 - Tiago suggestion
				$curlangslug = ( '' == the_curlang() ) ? strtolower( $xili_language->default_lang ) :  the_curlang() ;


					$headers = get_uploaded_header_images(); // search in uploaded header list

					$this_default_headers = $xili_language_theme_options->get_processed_default_headers () ;
					if ( ! empty( $this_default_headers ) ) {
						$headers = array_merge( $this_default_headers, $headers );
					}
					foreach ( $headers as $header_key => $header ) {

						if ( isset ( $xili_theme_options['xl_header_list'][$curlangslug] ) && $header_key == $xili_theme_options['xl_header_list'][$curlangslug] ) {
							$header_image_url =  $header['url'];
							 break ;
						}
					}
			 }
	?>
		.site-header {
			background: url(<?php echo $header_image_url;  ?>) no-repeat scroll top;
			background-size: 1600px auto;
		}
	<?php
		endif; // image exists

		// Has the text been hidden?
		if ( ! display_header_text() ) :
	?>
		.site-title,
		.site-description {
			position: absolute;
			clip: rect(1px 1px 1px 1px); /* IE7 */
			clip: rect(1px, 1px, 1px, 1px);
		}
	<?php
			if ( empty( $header_image ) ) :
	?>
		.site-header .home-link {
			min-height: 0;
		}
	<?php
			endif;

		// If the user has set a custom color for the text, use that.
		elseif ( $text_color != get_theme_support( 'custom-header', 'default-text-color' ) ) :
	?>
		.site-title,
		.site-description {
			color: #<?php echo esc_attr( $text_color ); ?>;
		}
	<?php endif; ?>
	</style>
	<?php
}



function twentythirteen_reset_default_theme_value ( $theme ) {
	set_theme_mod( 'header-text-color', 'fffff0' ); // to force first insertion // same in css
}
add_action('after_switch_theme', 'twentythirteen_reset_default_theme_value' );


/**
 * define when search form is completed by radio buttons to sub-select language when searching
 *
 */
function special_head() {

	// to change search form of widget
	// if ( is_front_page() || is_category() || is_search() )
	if ( is_search() ) {
	 	add_filter('get_search_form', 'my_langs_in_search_form_2013', 10, 1); // in multilingual-functions.php
	}
	$xili_theme_options = get_theme_xili_options() ;

	if ( !isset( $xili_theme_options['no_flags'] ) || $xili_theme_options['no_flags'] != '1' ) {
		twentythirteen_flags_style(); // insert dynamic css
	}
}
if ( class_exists('xili_language') )  // if temporary disabled
	add_action( 'wp_head', 'special_head', 11);

/**
 * dynamic style for flag depending current list and option no_flags
 *
 * @since 1.0.2 - add #access
 *
 */
function twentythirteen_flags_style () {

	if ( class_exists('xili_language') ) {
		global $xili_language ;
		$language_xili_settings = get_option('xili_language_settings');
		if ( !is_array( $language_xili_settings['langs_ids_array'] ) ) {
			$xili_language->get_lang_slug_ids(); // update array when no lang_perma 110830 thanks to Pierre
			update_option( 'xili_language_settings', $xili_language->xili_settings );
			$language_xili_settings = get_option('xili_language_settings');
		}

		$language_slugs_list =  array_keys ( $language_xili_settings['langs_ids_array'] ) ;

		?>
		<style type="text/css">
		<?php

		$path = get_stylesheet_directory_uri();

		$ulmenus = array();
		foreach ( $language_slugs_list as $slug ) {
			echo "ul.nav-menu li.menu-separator { margin:0; }\n";
			echo "ul.nav-menu li.lang-{$slug} a { background: transparent url('{$path}/images/flags/{$slug}.png') no-repeat center 16px; margin:0;}\n";
			echo "ul.nav-menu li.lang-{$slug}:hover {background: #AD9065}\n"; // find menu bk
			echo "ul.nav-menu li.lang-{$slug} a:hover {background: transparent url('{$path}/images/flags/{$slug}.png') no-repeat center 17px !important;}\n";
			$ulmenus[] = "ul.nav-menu li.lang-{$slug} a";
		}
			echo implode (', ', $ulmenus ) . " {text-indent:-9999px; width:24px;  }\n";
		?>
		</style>
		<?php

	}
}

// overhide default
function twentythirteen_entry_meta() {
	if ( is_sticky() && is_home() && ! is_paged() )
		echo '<span class="featured-post">' . __( 'Sticky', 'twentythirteen' ) . '</span>';

	if ( ! has_post_format( 'aside' ) && ! has_post_format( 'link' ) && 'post' == get_post_type() )
		twentythirteen_entry_date();

	// Translators: used between list items, there is a space after the comma.
	$categories_list = get_the_category_list( __( ', ', 'twentythirteen' ) );
	if ( $categories_list ) {
		echo '<span class="categories-links">' . $categories_list . '</span>';
	}

	// Translators: used between list items, there is a space after the comma.
	$tag_list = get_the_tag_list( '', __( ', ', 'twentythirteen' ) );
	if ( $tag_list ) {
		echo '<span class="tags-links">' . $tag_list . '</span>';
	}

	// Post author
	if ( 'post' == get_post_type() ) {
		printf( '<span class="author vcard"><a class="url fn n" href="%1$s" title="%2$s" rel="author">%3$s</a></span>',
			esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
			esc_attr( sprintf( __( 'View all posts by %s', 'twentythirteen' ), get_the_author() ) ),
			get_the_author()
		);
	}

	if ( is_singular() && class_exists('xili_language') ) {
		global $post;
		echo '&nbsp;-&nbsp;';
		$xili_theme_options = get_theme_xili_options() ;
		if ( xiliml_new_list() ) xiliml_the_other_posts($post->ID, $xili_theme_options['linked_title']);
	}
}

/**
 * to choice xiliml_the_other_posts in singular
 * @since 1.1
 */
function xiliml_new_list() {
	if ( class_exists('xili_language') ) {
		global $xili_language;

		$xili_theme_options = get_theme_xili_options() ; // see below

		if ( $xili_theme_options['linked_posts'] == 1 ) {
			if (is_page() && is_front_page() ) {
				return false;
			} else {
				return true;
			}
		}

		if ( is_active_widget ( false, false, 'xili_language_widgets' ) ) {

			$xili_widgets = get_option('widget_xili_language_widgets', array());
			foreach ( $xili_widgets as $key => $arrprop ) {
				if ( $key != '_multiwidget' ) {
					if ( $arrprop['theoption'] == 'typeonenew' ) {  // widget with option for singular
						if ( is_active_widget( false, 'xili_language_widgets-'.$key, 'xili_language_widgets' ) ) return false ;
					}
				}
			}
		}
		// since xl 2.8.5
		if ( XILILANGUAGE_VER > '2.0.0' && isset($xili_language -> xili_settings['navmenu_check_options']) && in_array ( $xili_language -> xili_settings['navmenu_check_options']['primary']['navtype'], array ('navmenu-1', 'navmenu-1a') ) ) return false ;

	}

	return true ;

}

//add_action( 'xl_propagate_post_attributes', 'my_propagate_post_content' , 11, 2); // 11 because after built filters

function my_propagate_post_content ( $from_post_ID, $post_ID ) {

		$from_post = get_post( $from_post_ID, ARRAY_A);
		$to_post = array ( 'ID' => $post_ID );

		// here the column(s) that you want to copy

		$to_post['post_content'] = $from_post['post_content'];

		// $to_post['post_excerpt'] = $from_post['post_excerpt'];

		wp_update_post( $to_post ) ;
}

/**
 * add search other languages in form - see functions.php when fired
 *
 */
function my_langs_in_search_form_2013 ( $the_form ) {

	$form = str_replace ( '</form>', '', $the_form ) . '<span class="xili-s-radio">' . xiliml_langinsearchform ( $before='<span class="radio-lang">', $after='</span>', false) . '</span>';
	$form .= '</form>';
	return $form ;
}

/**
 * condition to filter adjacent links
 * @since 1.1.4
 *
 */

function is_xili_adjacent_filterable() {

	if ( is_search () ) { // for multilingual search
		return false;
	}
	return true;
}


function twentythirteen_xili_credits () {
	printf( __("Multilingual child theme of Twenty Thirteen by %s", 'twentythirteen' ),"<a href=\"http://dev.xiligroup.com\">dev.xiligroup</a> - " );
}

add_action ('twentythirteen_credits','twentythirteen_xili_credits');


//remove_filter ( 'xl_propagate_post_attributes', array($xili_language, 'propagate_categories') ) ;

//remove_filter('xili_language_list', array( $xili_language, 'xili_language_list' ) );
//add_filter ( 'xili_language_list', 'my_xili_language_list', 10, 5 ) ;

function my_xili_language_list ( $a, $b, $c, $d, $e ) {

	return '<li>coucou</li>';

}

/**
 * Filter the page title - to fixe issue of parent. 2014-01-08
 *
 * Creates a nicely formatted and more specific title element text for output
 * in head of document, based on current view.
 *
 * @since 1.1.3
 *
 * @param string $title Default title text for current view.
 * @param string $sep   Optional separator.
 * @return string The filtered title.
 */
function twentythirteen_xili_wp_title( $title, $sep ) {
	global $paged, $page;

	if ( is_feed() )
		return $title;

	// Add the site name.
	$title .= get_bloginfo( 'name', 'display' ); // add display param for translation

	// Add the site description for the home/front page.
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) )
		$title = "$title $sep $site_description";

	// Add a page number if necessary.
	if ( $paged >= 2 || $page >= 2 )
		$title = "$title $sep " . sprintf( __( 'Page %s', 'twentythirteen' ), max( $paged, $page ) );

	return $title;
}
// original removed in after theme setup 2014-01-08
add_filter( 'wp_title', 'twentythirteen_xili_wp_title', 10, 2 );



?>