<?php
// latest tests before shipping - wp_title - 2014-01-12
// dev.xiligroup.com - msc - 2013-11-02 - first test with 2014 0.1
// 1.0.1 - 2014-01-20 - add is_xili_adjacent_filterable (reserved future uses and in class embedding)
// 1.0.2 - 2014-02-09 - adapted for new permalinks class of XL 2.10.0
// 1.0.4 - 2014-02-26 - add category, updated featured
// 1.0.5 - 2014-03-04 - add searchform.php
// 1.0.6 - 2014-03-18 - fixes I10n functionswith variables
// 1.0.7 - 2014-04-28 - need XL 2.12 - WP 3.9

define( 'TWENTYFOURTEEN_XILI_VER', '1.0.7-wp3.9'); // as parent style.css

// main initialisation functions and version testing and message

function twentyfourteen_xilidev_setup () {

	$theme_domain = 'twentyfourteen';

	$minimum_xl_version = '2.11.9';

	load_theme_textdomain( $theme_domain, get_stylesheet_directory() . '/langs' ); // now use .mo of child

	$xl_required_version = false;

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
		// Args dedicated to this theme named Twenty Fourteen
		$xili_args = array (
	 		'customize_clone_widget_containers' => true, // comment or set to true to clone widget containers
	 		'settings_name' => 'xili_2014_theme_options', // name of array saved in options table
	 		'theme_name' => 'Twenty Fourteen',
	 		'theme_domain' => $theme_domain,
	 		'child_version' => TWENTYFOURTEEN_XILI_VER
		);

		if ( is_admin() ) {

		// Admin args dedicaced to this theme

			$xili_admin_args = array_merge ( $xili_args, array (
		 		'customize_adds' => true, // add settings in customize page
		 		'customize_addmenu' => false, // done by 2013
		 		'capability' => 'edit_theme_options',
		 		'authoring_options_admin' => false
			) );

			if ( class_exists ( 'xili_language_theme_options_admin' ) ) {
				$xili_language_theme_options = new xili_language_theme_options_admin ( $xili_admin_args );
				$class_ok = true ;
			} else {
				$class_ok = false ;
			}


		} else { // visitors side - frontend

			if ( class_exists ( 'xili_language_theme_options' ) ) {
				$xili_language_theme_options = new xili_language_theme_options ( $xili_args );
				$class_ok = true ;
			} else {
				$class_ok = false ;
			}
		}
		// new ways to add parameters in authoring propagation
		add_theme_support('xiliml-authoring-rules', array (
			'post_content' => array('default' => '1',
				'data' => 'post',
				'hidden' => '',
				'name' => 'Post Content',
				'description' => __('Will copy content in the future translated post', 'twentyfourteen')
		),
			'post_parent' => array('default' => '1',
				'data' => 'post',
				'name' => 'Post Parent',
				'hidden' => '1',
				'description' => __('Will copy translated parent id (if original has parent and translated parent)!', 'twentyfourteen')
		))
		); //

		$xili_theme_options = get_theme_xili_options() ;
		// to collect checked value in xili-options of theme
		if ( file_exists( $xili_functionsfolder . '/multilingual-permalinks.php') && $xili_language->is_permalink && isset( $xili_theme_options['perma_ok'] ) && $xili_theme_options['perma_ok']) {
			require_once ( $xili_functionsfolder . '/multilingual-permalinks.php' ); // require subscribing premium services
		}

	}

	// errors and installation informations

	if ( ! class_exists( 'xili_language' ) ) {

		$msg = '
		<div class="error">
			<p>' . sprintf ( translate('The %s child theme requires xili-language plugin installed and activated', $theme_domain ), get_option( 'current_theme' ) ).'</p>
		</div>';

	} elseif ( $class_ok === false ) {

		$msg = '
		<div class="error">
			<p>' . sprintf ( translate('The %s child theme requires <em>xili_language_theme_options</em> class to set multilingual features.', $theme_domain ), get_option( 'current_theme' ) ).'</p>
		</div>';

	} elseif ( $xl_required_version ) {

		$msg = '
		<div class="updated">
			<p>' . sprintf ( translate('The %s child theme was successfully activated with xili-language.', $theme_domain ), get_option( 'current_theme' ) ).'</p>
		</div>';

	} else {

		$msg = '
		<div class="error">
			<p>' . sprintf ( translate('The %1$s child theme requires xili-language version %2$s+', $theme_domain ), get_option( 'current_theme' ), $minimum_xl_version ).'</p>
		</div>';
	}
	// after activation and in themes list
	if ( isset( $_GET['activated'] ) || ( ! isset( $_GET['activated'] ) && ( ! $xl_required_version || ! $class_ok ) ) )
		add_action( 'admin_notices', $c = create_function( '', 'echo "' . addcslashes( $msg, '"' ) . '";' ) );

	// end errors...

	// remove_filter( 'wp_title', 'twentyfourteen_wp_title', 10, 2 ); // see function and new filter at end fixed in WP 3.9

}
add_action( 'after_setup_theme', 'twentyfourteen_xilidev_setup', 11 );


function twentyfourteen_xilidev_setup_featured () {
	remove_theme_support( 'featured-content' );
	// Add support for featured content.
	add_theme_support( 'featured-content', array(
		'featured_content_filter' => 'twentyfourteen_get_featured_posts',
		'max_posts' => 3
	) );
}
add_action( 'after_setup_theme', 'twentyfourteen_xilidev_setup_featured', 11 ); // comment to reset max_posts to 6 as in parent


//To avoid conflict if a plugin has this class Featured_Content
if ( ! class_exists( 'Featured_Content' ) && 'plugins.php' !== $GLOBALS['pagenow'] ) {
	require get_stylesheet_directory() . '/inc/featured-content.php';
}

add_action( 'widgets_init', 'xili_twentyfourteen_widgets_init', 11 );

function xili_twentyfourteen_widgets_init () {
	unregister_widget( 'Twenty_Fourteen_Ephemera_Widget' );
	require get_stylesheet_directory() . '/inc/widgets.php'; // in child

	register_widget( 'Twenty_Fourteen_xili_Ephemera_Widget');
}

function xili_customize_js_footer () {

	wp_enqueue_script( 'customize-xili-js-footer', get_stylesheet_directory_uri(). '/functions-xili' . '/js/xili_theme_customizer.js' , array( 'customize-preview' ), TWENTYFOURTEEN_XILI_VER, true );

}
// need to be here not as hook not in class
add_action( 'customize_preview_init', 'xili_customize_js_footer', 9 ); // before parent 2013 to be in footer


/**
 * define when search form is completed by radio buttons to sub-select language when searching
 *
 */
function special_head() {

	// to change search form of widget
	// if ( is_front_page() || is_category() || is_search() )
	if ( is_search() || is_404() ) {
	 	add_filter('get_search_form', 'my_langs_in_search_form_2014', 10, 1); // here below
	}
	$xili_theme_options = get_theme_xili_options() ;

	if ( !isset( $xili_theme_options['no_flags'] ) || $xili_theme_options['no_flags'] != '1' ) {
		twentyfourteen_flags_style(); // insert dynamic css
	}
}
if ( class_exists('xili_language') )  // if temporary disabled
	add_action( 'wp_head', 'special_head', 11);


function twentyfourteen_xilidev_setup_custom_header () {

	// %2$s = in child
	register_default_headers( array(
		'xili2014' => array(

			'url'           => '%2$s/images/headers/header-xili.jpg',
			'thumbnail_url' => '%2$s/images/headers/header-xili-thumbnail.jpg',
			'description'   => _x( '2014 by xili', 'header image description', 'twentyfourteen' )
			),
		'xili2014-2' => array(

			'url'           => '%2$s/images/headers/header-xili2.jpg',
			'thumbnail_url' => '%2$s/images/headers/header-xili2-thumbnail.jpg',
			'description'   => _x( '2014.2 by xili', 'header image description', 'twentyfourteen' )
			)
		)
	);

	$args = array(
		// Text color and image (empty to use none).
		'default-text-color'     => 'fffff0', // diff of parent
		'default-image'          => '%2$s/images/headers/header-xili.jpg',

		// Set height and width, with a maximum value for the width.
		'height'                 => 48,
		'width'                  => 1260,

		// Callbacks for styling the header and the admin preview.
		'wp-head-callback'       => 'twentyfourteen_header_style',
		'admin-head-callback'    => 'twentyfourteen_admin_header_style',
		'admin-preview-callback' => 'twentyfourteen_admin_header_image',
	);

	add_theme_support( 'custom-header', $args ); // need 8 in add_action to overhide parent

}
add_action( 'after_setup_theme', 'twentyfourteen_xilidev_setup_custom_header', 9 );

function twentyfourteen_reset_default_theme_value ( $theme ) {
	set_theme_mod( 'header-text-color', 'fffff0' ); // to force first insertion // same in css
}
add_action('after_switch_theme', 'twentyfourteen_reset_default_theme_value' );

add_action("admin_head-appearance_page_custom-header", "twentyfourteen_xili_header_help", 15);

function twentyfourteen_xili_header_help ( ) {
	global $xili_language_theme_options;
	$header_setting_url = admin_url('/themes.php?page='. $xili_language_theme_options->settings_name );

	get_current_screen()->add_help_tab( array(
			'id'      => 'set-header-image-xili',
			'title'   => __('Multilingual Header Image in 2014-xili', 'twentyfourteen'),
			'content' =>
				'<p>' . __( 'You can set a custom image header for your site according each current language. When the language changes, the header image will change. The default header image is assigned to unknown unaffected language.', 'twentyfourteen' ) . '</p>' .
				'<p>' . sprintf( __( 'The images will be assigned to the language in the %1$sXili-Options%2$s  Appearance settings page.', 'twentyfourteen'),'<a href="'.$header_setting_url.'">' ,'</a>' ). '</p>'
		) );

}

// for xili
function twentyfourteen_xili_header_image () {

	$header_image_url = get_header_image();

	$text_color   = get_header_textcolor();

	// If no custom options for text are set, let's bail.
	if ( empty( $header_image_url ) )
		return;
	$xili_theme_options = get_theme_xili_options() ;
	// If we get this far, we have custom styles.

		if ( ! empty( $header_image_url ) ) :
			$header_image_width =  get_custom_header()->width; // default values
			$header_image_height = get_custom_header()->height;
			if ( class_exists ( 'xili_language' ) && isset ( $xili_theme_options['xl_header'] ) && $xili_theme_options['xl_header'] ) {
				global $xili_language, $xili_language_theme_options ;
				// check if image exists in current language
				// 2013-10-10 - Tiago suggestion
				$curlangslug = ( '' == the_curlang() ) ? strtolower( $xili_language->default_lang ) : the_curlang() ;


					$headers = get_uploaded_header_images(); // search in uploaded header list

					$this_default_headers = $xili_language_theme_options->get_processed_default_headers () ;
					if ( ! empty( $this_default_headers ) ) {
						$headers = array_merge( $this_default_headers, $headers );
					}
					foreach ( $headers as $header_key => $header ) {

						if ( isset ( $xili_theme_options['xl_header_list'][$curlangslug] ) && $header_key == $xili_theme_options['xl_header_list'][$curlangslug] ) {
							$header_image_url = $header['url'];
							$header_image_width = $header['width'];
							$header_image_height = $header['height'];
							 break ;
						}
					}
			 }
	?>

	<div id="site-header">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
			<img src="<?php echo $header_image_url; ?>" width="<?php echo $header_image_width; ?>" height="<?php echo $header_image_height; ?>" alt="" />
		</a>
	</div>

	<?php endif;
}

// patch before updating and fixe of parent theme

function twentyfourteen_xili_wp_title( $title, $sep ) {
	global $paged, $page;

	if ( is_feed() ) {
		return $title;
	}

	// Add the site name.
	$title .= get_bloginfo( 'name', 'display' ); // xili 2014-01-12 - fixed in WP 3.9 - no need filter

	// Add the site description for the home/front page.
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) ) {
		$title = "$title $sep $site_description";
	}

	// Add a page number if necessary.
	if ( $paged >= 2 || $page >= 2 ) {
		$title = "$title $sep " . sprintf( __( 'Page %s', 'twentyfourteen' ), max( $paged, $page ) );
	}

	return $title;
}
// original removed in after theme setup 2014-01
// add_filter( 'wp_title', 'twentyfourteen_xili_wp_title', 10, 2 );


/**
 * dynamic style for flag depending current list and option no_flags
 *
 * @since 1.0.2 - add #access
 *
 */
function twentyfourteen_flags_style () {

	if ( class_exists('xili_language') ) {
		global $xili_language ;
		$language_xili_settings = get_option('xili_language_settings');
		if ( !is_array( $language_xili_settings['langs_ids_array'] ) ) {
			$xili_language->get_lang_slug_ids(); // update array when no lang_perma 110830 thanks to Pierre
			update_option( 'xili_language_settings', $xili_language->xili_settings );
			$language_xili_settings = get_option('xili_language_settings');
		}

		$language_slugs_list = array_keys ( $language_xili_settings['langs_ids_array'] ) ;

		?>
		<style type="text/css">
		<?php

		$path = get_stylesheet_directory_uri();

		$ulmenus = array();
		foreach ( $language_slugs_list as $slug ) {
			echo "ul.nav-menu li.menu-separator { margin:0; }\n";
			echo "ul.nav-menu li.lang-{$slug} a { background: transparent url('{$path}/images/flags/{$slug}.png') no-repeat center 16px; margin:0;}\n";
			echo "ul.nav-menu li.lang-{$slug}:hover {background: #41a62a}\n"; // find menu bk
			echo "ul.nav-menu li.lang-{$slug} a:hover {background: transparent url('{$path}/images/flags/{$slug}.png') no-repeat center 17px !important;}\n";
			$ulmenus[] = "ul.nav-menu li.lang-{$slug} a";
		}
			echo implode (', ', $ulmenus ) . " {text-indent:-9999px; width:24px; }\n";
		?>
		</style>
		<?php

	}
}


/**
 * add search other languages in form - see functions.php when fired
 *
 */
function my_langs_in_search_form_2014 ( $the_form ) {

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


function twentyfourteen_xili_credits () {
	printf( __("Multilingual child theme of Twenty Fourteen by %s", 'twentyfourteen' ),"<a href=\"http://dev.xiligroup.com\">dev.xiligroup</a> - " );
}

add_action ('twentyfourteen_credits', 'twentyfourteen_xili_credits');

/* comment filter to unable link manager */
add_filter( 'pre_option_link_manager_enabled', '__return_true' );

?>