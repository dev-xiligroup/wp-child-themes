<?php

// xili-options class
// copy of part of xili-language plugin (theme-multilingual-classes.php) - can be renamed in theme sub-folder functions-xili if adapted (multilingual-classes.php) - see first lines of functions.php
// xl 2.9.1 - tmc v.1.2
// xl 2.9.10 - 2013-xili v1.0.2 - tmc v.1.3.1
// xl 2.9.11 - 2014-xili v0.1 - tmc v.1.3.2

if ( ! class_exists ( 'xili_language_theme_options' )  ) {
	
	class xili_language_theme_options  {
		
		var $settings_name;
		var $theme_name;
		var $theme_domain;
		var $child_version;
		var $capability;
		var $menu_lang_sep; // used when nav menu slug contain _
		var $class_version = '1.3.2';
		var $child_suffix = '-xili';
		
		var $propagate_options_default_ref = array ( 
		'post_format' => array ( 'default'=> '1', 'data' => 'attribute' ), 
		'page_template' => array ( 'default'=> '1', 'data' => 'meta' ), 
		'comment_status' => array ( 'default'=> '', 'data' => 'post' ), 
		'ping_status' => array ( 'default'=> '', 'data' => 'post' ), 
		'post_parent' => array ( 'default'=> '', 'data' => 'post' ), 
		'menu_order' => array ( 'default'=> '', 'data' => 'post' ), 
		'thumbnail_id' => array ( 'default'=> '1', 'data' => 'meta' ) );
		
		function __construct( $xili_args ) {
			$xili_args_default = array (
 				'settings_name' => 'xili_theme_options', // name of array saved in options table
 				'theme_name' => 'Twenty Twelve',
 				'theme_domain' => 'twentytwelve',
 				'child_version' => '0.0.1',
 				'capability' => 'edit_theme_options',
 				'menu_lang_sep' => '_', // by default and for update
 				'customize_adds' => true, // add settings in customize page only if called by developer
 				'customize_add_menu' => false,
 				'customize_clone_widget_containers' => false, // set during instancing by developer
 				'propagate_options_default' => $this->propagate_options_default_ref,
			);
			
			$xili_args = wp_parse_args( $xili_args, $xili_args_default );
			
			$this->settings_name = $xili_args ['settings_name'];
			$this->theme_name = $xili_args ['theme_name'];
			$this->theme_domain = $xili_args ['theme_domain'];
			$this->child_version = $xili_args ['child_version'];
			$this->capability = $xili_args ['capability'];
			$this->menu_lang_sep = $xili_args ['menu_lang_sep'];
			$this->customize_adds = $xili_args ['customize_adds'];
			$this->customize_clone_widget_containers = $xili_args ['customize_clone_widget_containers'];
			 
			$this->propagate_options_default = $xili_args ['propagate_options_default'];
			
			if ( $this->customize_adds ) {
				add_action( 'customize_register', array( $this, 'customize_registering' ), 1 ); 
			}

			add_action( 'wp_head', array( $this, 'special_head' ), 11 );
			
			add_action ( 'init', array( $this, 'xili_create_menu_locations'), 100 );
			add_filter ( 'wp_nav_menu_args', array( $this, 'xili_wp_nav_menu_args') ); // called at line #145 in nav-menu-template.php
			
			if ( $this->customize_clone_widget_containers ) {
				add_action ( 'init', array( $this, 'xili_clone_sidebar_container'), 101);
			}
		}
		
		function get_theme_xili_options() {
			return get_option( $this->settings_name, $this->get_default_theme_xili_options() );
		}

		function get_default_theme_xili_options() {
			$propagate_option_default = array();
			if ( $this->propagate_options_default != array() ) {
				foreach ( $this->propagate_options_default as $key => $one_options ) {
					$propagate_option_default[$key]  = $one_options['default'];
				}
			} 
			
			$arr = array_merge ( array( 'no_flags' => '' , 'linked_posts' => '1', 'linked_title' => 'Read this post in',  'nav_menus' => 'no'), $propagate_option_default );
			//error_log ( serialize ( $arr ));
			return $arr ;
		}
		
		// create options in customize theme screen to be refreshed
		function customize_registering ( $wp_customize ) {
			global $xili_language;
			
			// in_nav_menu
		
			$locations      = get_registered_nav_menus();
			$menus          = wp_get_nav_menus();
			$menu_locations = get_nav_menu_locations();
			$num_locations  = count( array_keys( $locations ) );
			
			if ( $num_locations >= 1 && count ( $menu_locations ) >= 1 ) {
				
				$wp_customize->add_section( 'xili_options_section' , array(
		    	'title'      => __('Multilingual Options ©xili', 'xili-language' ),
		    	'priority'   => 300,
		    	'description'    => sprintf( _n('Your theme supports %s menu. Customize style.', 'Your theme supports %s menus. Customize style.', $num_locations, 'xili-language'), number_format_i18n( $num_locations ) ) . "\n\n" . __('You can edit your menu content on the Menus screen in the Appearance section.'),
			) );
		
				$location_slugs = array_keys( $locations );
				if ( 0 == $xili_language->has_languages_list_menu ( $location_slugs[0] ) ) { // only test one menu during transition
		
					$wp_customize->add_setting( 'xili_language_settings[in_nav_menu]' , array(
				    	'default'     => '',
				    	'transport'   => 'refresh',	
				    	'type'     => 'option',
				    	'capability'  => $this->capability,
				    	
					) );
				
					$wp_customize->add_control( 'in_nav_menu', array(
				    	'settings' => 'xili_language_settings[in_nav_menu]',
				    	'label'    => __( 'Append the languages', 'xili-language' ),
				    	'section'  => 'xili_options_section',
				    	'type'     => 'radio',
				    	'choices'    => array(
						'disable'	 => __('No languages menu-items', 'xili-language' ),
						'enable'     => __('Show languages menu-items', 'xili-language' )
					)
					) );
					
					$wp_customize->add_setting( 'xili_language_settings[nav_menu_separator]' , array(
				    	'default'     => '|',
				    	'transport'   => 'refresh',	
				    	'type'     => 'option',
				    	'capability'  => $this->capability
					) );
				
					$wp_customize->add_control( 'nav_menu_separator', array(
				    	'settings' => 'xili_language_settings[nav_menu_separator]',
				    	'label'    => __( 'Separator before language list (Character or Entity Number or Entity Name)', 'xili-language' ), // xl domain not
				    	'section'  => 'xili_options_section',
				    	'type'     => 'text'
					
					) );
				}
			$wp_customize->add_setting( $this->settings_name.'[no_flags]' , array(
		    	'default'     => '',
		    	'transport'   => 'refresh',	
		    	'type'     => 'option',
		    	'capability'  => $this->capability
			) );
		
			$wp_customize->add_control( 'no_flags', array(
		    	'settings' => $this->settings_name.'[no_flags]',
		    	'label'    => __( 'Hide the flags', 'xili-language' ),
		    	'section'  => 'xili_options_section',
		    	'type'     => 'checkbox', 
			) );
		
		} else {
			
			$wp_customize->add_section( 'xili_options_section', array(
			'title'          => __('Multilingual Options ©xili', 'xili-language' ),
			
			'priority'       => 300,
			'description'    => __( 'None nav menus location seems active', 'xili-language' )
		) );
			
		}
			$wp_customize->add_setting( $this->settings_name.'[linked_posts]' , array(
		    	'default'     => '',
		    	'transport'   => 'refresh',
		    	'type'     => 'option',
		    	'capability'  => $this->capability
			) );
		
			$wp_customize->add_control( 'linked_posts', array(
		    	'settings' => $this->settings_name.'[linked_posts]',
		    	'label'    => __('Show linked posts', 'xili-language' ),
		    	'section'  => 'xili_options_section',
		    	'type'     => 'checkbox',
			) );

	
		}
		
		function special_head ( ) {
		
			printf ('<!-- Website powered by child-theme %1$s%2$s v. %3$s of dev.xiligroup.com -->'."\n", $this->theme_name, $this->child_suffix, $this->child_version ) ;
			echo '<link rel="shortcut icon" href="' . get_stylesheet_directory_uri() . '/images/favicon.ico" type="image/x-icon"/>'."\n";
			echo '<link rel="apple-touch-icon" href="' . get_stylesheet_directory_uri() . '/images/apple-touch-icon.png"/>'."\n";
	
		}
		
		
		
		/**
		 * filter to create one menu per language for dashboard and front-end
		 * detect the default one created by theme ($menu_locations_keys[0])
		 * @since 0.9.7
		 * @updated 1.0.2
		 */
		
		function xili_create_menu_locations () {
			
			$xili_theme_options = $this->get_theme_xili_options() ;
			 
			if ( isset ( $xili_theme_options['nav_menus'] ) && $xili_theme_options['nav_menus'] === 'nav_menus' ) {  // ok for automatic insertion of one menu per lang...
				$menu_locations = get_registered_nav_menus() ; 
				$menu_locations_keys =  array_keys( $menu_locations );
				$navmenu_count = count ( $menu_locations_keys ) ;
				global $xili_language ;
				$default = 'en_us'; // currently the default language of theme in core WP
				$language_xili_settings = get_option('xili_language_settings');
				$language_slugs_list =  array_keys ( $language_xili_settings['langs_ids_array'] ) ;
				if ( $menu_locations_keys ) {
					foreach ( $menu_locations_keys as $oneloc ) {
						foreach ( $language_slugs_list as $slug ) {
							$one_menu_location = $oneloc . $this->menu_lang_sep . $slug ;
							$indice = 'nav_menu_'.$oneloc ;
							
							$do_it = ( $navmenu_count == 1 )  ?  true : (isset( $xili_theme_options[$indice] ) &&  $xili_theme_options[$indice] == 'nav_menu') ;
							if ( $do_it && $slug != $default ) {
								register_nav_menu ( $one_menu_location,  sprintf( __( '%s for %s', $this->theme_domain ), $menu_locations[$oneloc], $slug ) );
							}
						}
					}
				}
				
			}
		}
		
		/**
		 * filter to avoid modifying theme's header and changes 'virtually' location for each language
		 * @since 0.9.7
		 */
		function xili_wp_nav_menu_args ( $args ) {
			
			$xili_theme_options = get_theme_xili_options() ; 
			$ok =  ( isset ( $xili_theme_options['nav_menus'] ) && $xili_theme_options['nav_menus'] === 'nav_menus' ) ? true : false ;
			
			global $xili_language ;
			$default = 'en_us'; // currently the default language of theme as in core WP
			$slug = the_curlang();
			if ( $default != $slug  && $ok ) { 
				$theme_location = $args['theme_location'];
				if ( has_nav_menu ( $theme_location . $this->menu_lang_sep . $slug ) ) { // only if a menu is set by webmaster in menus dashboard
					$args['theme_location'] = $theme_location . $this->menu_lang_sep . $slug ;
				}	
			}
				
			return $args;
		}
		
		


		/**
		 * create if option clone of sidebar container by language
		 *
		 *
		 */
		function xili_clone_sidebar_container () {
			global $wp_registered_sidebars;
			
			$xili_theme_options = get_theme_xili_options() ; // 1.1.2 
			
			$language_xili_settings = get_option('xili_language_settings');
			$language_slugs_list =  array_keys ( $language_xili_settings['langs_ids_array'] ) ;
			
			foreach ( $language_slugs_list as $slug) {
				
				if ( $slug != 'en_us'  ) {
		
					$language = get_term_by( 'slug', $slug, TAXONAME ); //$language = xiliml_get_language( $slug );
					
					foreach ( $wp_registered_sidebars as $one_key => $one_sidebar ) { 
						$indice = 'sidebar_'.$one_key ;
						if ( false === strpos( $one_key , '_'. $slug ) && isset ( $xili_theme_options[$indice] ) ) {	// don't use _xx_XX lang in root sidebar id 	
							register_sidebar( array(
								'name' => sprintf ( __('%1$s in %2$s', $this->theme_domain),  $one_sidebar['name'],  $language->description ),
								'id' => $one_sidebar['id'].'_'.$slug,
								'description' => $one_sidebar['description'],
								'before_widget' => $one_sidebar['before_widget'],
								'after_widget' => $one_sidebar['after_widget'],
								'before_title' => $one_sidebar['before_title'],
								'after_title' => $one_sidebar['after_title'],
							) );
						}	
					}
				}
			}
		}
		
		
		/**
		 * Default functions for attributes and features propagation when creating a translation
		 *
		 * here to be used also in frontend
		 *
		 */
		
		// return array of default_headers
		
		function get_processed_default_headers () {
			global $_wp_default_headers;
			if ( !isset($_wp_default_headers) )
				return array();
				
			$this_default_headers = $_wp_default_headers;
			    		$template_directory_uri = get_template_directory_uri();
						$stylesheet_directory_uri = get_stylesheet_directory_uri();
						foreach ( array_keys($this_default_headers) as $header ) {
							$this_default_headers[$header]['url'] =  sprintf( $this_default_headers[$header]['url'], $template_directory_uri, $stylesheet_directory_uri );
							$this_default_headers[$header]['thumbnail_url'] =  sprintf( $this_default_headers[$header]['thumbnail_url'], $template_directory_uri, $stylesheet_directory_uri );
						}
			return 	$this_default_headers ;		
		
		}

		// propagate post_formats
		function propagate_post_format ( $from_post_ID, $post_ID ) {
			if ( $format = get_post_format( $from_post_ID ) ){
				set_post_format( (int)$post_ID , $format);
			}
		}
		
		// propagate page_template
		function propagate_page_template ( $from_post_ID, $post_ID ) {
			if ( 'page' == get_post_type( $from_post_ID ) ) { // post_type_supports( 'page', 'page-attributes' );
				$template = get_post_meta ( $from_post_ID, '_wp_page_template', true ) ;
				update_post_meta ( $post_ID, '_wp_page_template', $template );
			}
		}
		
		// propagate comment_status ping_status menu_order post_parent  in oneshot
		function propagate_post_columns ( $from_post_ID, $post_ID ) {
			
			// list columns to update
			$options = $this->get_theme_xili_options();
			
			$from_post = get_post( $from_post_ID, ARRAY_A);
			
			$to_post = array ( 'ID' => $post_ID );
			
			if ( $this->propagate_options_default != array() ) {
				$i = 0;
				foreach ( $this->propagate_options_default as $key => $one_propagate ) {
					if (  $one_propagate['data'] == 'post' && isset  ( $options[$key] )  ) {
						
						if ( $key == 'post_parent' ) {
							$parent_key = $from_post[$key];  
							// to language
							$to_lang = get_cur_language( $post_ID ) ;   
							$translated_parent_key = xl_get_linked_post_in ( $parent_key, $to_lang ) ;  // return ID
							$to_post[$key] = $translated_parent_key ;
							
						} else {
							$to_post[$key] = $from_post[$key]; // ready also for excerpt...
						}
						
						$i++;
					}
				}
				
				if ( $i > 0 ) wp_update_post( $to_post ) ;
			}
		}
		
		function propagate_thumbnail_id ( $from_post_ID, $post_ID ) {
			$thumbnail_id = get_post_meta ( $from_post_ID, '_thumbnail_id', true ) ;
			
			if ( $thumbnail_id ) {
				$to_lang = get_cur_language( $post_ID ) ;
				$translated_value = xl_get_linked_post_in ( $thumbnail_id, $to_lang ) ;
				$value = ( $translated_value != 0 ) ? $translated_value : $thumbnail_id ; // a translation exist ( title / alt / ...)
			  	update_post_meta ( $post_ID, '_thumbnail_id', $value );
			}
		}
		
		
		
	} // end class
}

if ( ! class_exists ( 'xili_language_theme_options_admin' )  ) {
	
	class xili_language_theme_options_admin extends xili_language_theme_options  {
		
		var $customize_adds;
		var $capability;
		var $xili_theme_page; // set in menu creation - used by help
		var $propagate_options = array ();
		var $admin_ui_domain = 'xili-language';
		
		
		function __construct( $xili_admin_args ) {
			
			$this->theme_domain = ( isset ( $xili_admin_args ['theme_domain'] ) ) ? $xili_admin_args ['theme_domain'] : 'twentytwelve' ;
			
			if ( false === strpos ( plugins_url ('', __FILE__), WP_PLUGIN_URL . '/xili-language' ) ) { 
				$this->admin_ui_domain = $this->theme_domain; // the class is in functions-xili subfolder of theme folder
			}
			
			$xili_admin_args_default = array (
 				
 				'settings_name' => 'xili_theme_options', // name of array saved in options table
 				'theme_name' => 'Twenty Twelve',
 				//'theme_domain' => 'twentytwelve',	 
 				'capability' => 'edit_theme_options',
 				'child_version' => '0.0.1',
 				'customize_adds' => true,
 				'customize_add_menu' => false,
 				'customize_clone_widget_containers' => false,
 				'authoring_options_admin' => false,
 				'propagate_options' => array (
							'post_format' => array ('name' => __('Post Format', $this->theme_domain ),  
							'description' => __('Copy Post Format.', $this->admin_ui_domain) 
							),
							'page_template' => array ('name' => __('Page template', $this->admin_ui_domain), 
							'description' => __('Copy Page template.', $this->admin_ui_domain)
							),
							'comment_status' => array ('name' => __('Comment Status', $this->admin_ui_domain), 
							'description' => __('Copy Comment Status.', $this->admin_ui_domain)
							),
							'ping_status' => array ('name' => __('Ping Status', $this->admin_ui_domain), 
							'description' => __('Copy Ping Status.', $this->admin_ui_domain),
							),
							'post_parent' => array ('name' => __('Post Parent', $this->admin_ui_domain), 
							'description' => __('Copy Post Parent if translated (try to find the parent of the translated post).', $this->admin_ui_domain), 'data' => 'post'
							),
							'menu_order' => array ('name' => __('Order', $this->admin_ui_domain), 
							'description' => __('Copy Page Order', $this->admin_ui_domain),
							),
							'thumbnail_id' => array ('name' => __('Featured image', $this->admin_ui_domain), 
							'description' => __('Linked translated post will have the same featured image, (try to find the translated media). ', $this->admin_ui_domain),
							),
 											
 						),
 				'propagate_options_default' => $this->propagate_options_default_ref,
			);
			
			$xili_admin_args = wp_parse_args( $xili_admin_args, $xili_admin_args_default );
			
			$xili_args = array ( 
				
				'settings_name' => $xili_admin_args ['settings_name'],
				'theme_name' => $xili_admin_args ['theme_name'],
				'theme_domain' => $xili_admin_args ['theme_domain'],
				'child_version' => $xili_admin_args ['child_version'],
				'capability' => $xili_admin_args ['capability'],
				'customize_adds' => $xili_admin_args ['customize_adds'],
				'customize_add_menu' => $xili_admin_args ['customize_add_menu'],
				'customize_clone_widget_containers' => $xili_admin_args ['customize_clone_widget_containers'],
				'propagate_options_default' => $xili_admin_args ['propagate_options_default'],
			);
			
			$this->authoring_options_admin = $xili_admin_args ['authoring_options_admin'];
			
			parent::__construct( $xili_args );
			
			$this->customize_adds = $xili_admin_args ['customize_adds'];
			$this->customize_addmenu = $xili_admin_args ['customize_add_menu'];
			$this->customize_clone_widget_containers = $xili_admin_args ['customize_clone_widget_containers'];
			$this->settings_name = $xili_admin_args ['settings_name'];
			$this->theme_name = $xili_admin_args ['theme_name'];
			
			$this->capability = $xili_admin_args ['capability'];
			$this->child_version = $xili_admin_args ['child_version'];
			
			if ( isset ( $xili_admin_args ['child_suffix'] ) ) 
				$this->child_suffix = $xili_admin_args ['child_suffix']; // overhide default set in var of parent class
			
			// add to merge complementary value selected by theme	- 2.8.10		
			$this->propagate_options = array_merge ( $xili_admin_args ['propagate_options'], $xili_admin_args_default [ 'propagate_options' ] );
			$this->propagate_options_default = $xili_admin_args ['propagate_options_default'];
			
			add_action( 'admin_menu', array( $this, 'xili_options_theme_menu' ) );
			
			add_action( 'admin_init', array( $this, 'xili_register_settings' ) );
			
			add_action( 'admin_print_styles', array(&$this, 'print_styles_xili_options'),9 );
			
			add_action( 'admin_print_scripts', array(&$this, 'print_scripts_xili_options') );
			
			$options = $this->get_theme_xili_options();
			
			if ( $this->propagate_options_default != array() ) {
				foreach ( $this->propagate_options_default as $key => $one_propagate ) {
					if (  $one_propagate['data'] != 'post' && isset  ( $options[$key] )  ) { // && ! has_filter ( 'xl_propagate_post_attributes', array( 'xili_language_theme_options', 'propagate_'.$key ) )   ) {
						add_action( 'xl_propagate_post_attributes', array( &$this, 'propagate_'.$key ) , 10, 2); 
					}
				}
				
				add_action( 'xl_propagate_post_attributes', array( &$this, 'propagate_post_columns' ) , 10, 2);
			}
			
		}
		
		
		
		// create appareance sub-menus
		function xili_options_theme_menu() {
			
			$this->xili_theme_page = add_theme_page( sprintf(__('%1$s%2$s Theme Options', 'xili-language'), $this->theme_name, $this->child_suffix ) , 'Xili Options', 'manage_options', $this->settings_name, array( $this,'xili_options_theme_page' ) );
			
			if ( $this->customize_addmenu )
				add_theme_page( __('Customize'), __('Customize'), 'edit_theme_options', 'customize.php' );
				
			add_action('load-'.$this->xili_theme_page, array( $this, 'xili_theme_options_help_page' ) );
		}
		
		function xili_options_theme_page() {
			
			$indice = ( $this->admin_ui_domain == 'xili-language' ) ? 'X' : 'T' ;
		?>
		<div class="section panel">
		<h1><?php printf( __('Multilingual options for %1$s%2$s theme ', $this->admin_ui_domain ), $this->theme_name, $this->child_suffix ); ?></h1>
		<form method="post" enctype="multipart/form-data" action="options.php">
		<p><em><?php _e ('Click on line with arrow to reveal options to set... Don’t forget to save after changing...', $this->admin_ui_domain ) ?></em></p>
		<p class="submit">
		   <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
		</p>
		<div id="settings-accordion" class="accordion">
		        <?php
		          settings_fields( $this->settings_name );  // nounce, action (plugin.php)
		          do_settings_sections( $this->settings_name );
		        ?>
		</div>
		<p class="submit">
		   <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
		</p>            
		</form>
		<p><small><?php echo $this->theme_domain . $this->child_suffix ?> v. <?php echo $this->child_version; ?> , a multilingual child by <a href="http://dev.xiligroup.com" target="_blank" >dev.xiligroup.com</a> (©2013) <?php echo '(xl v.'.XILILANGUAGE_VER . ') (' . $this->class_version . '.' . $indice . ')'?></small></p>
		
		</div>
		<script type="text/javascript">
	//<![CDATA[
			jQuery(document).ready( function($) {
				// for header image list selector
				$('.xl_header').change(function() {
                 	var x = $(this).val();
                 	var changing = $(this).attr('id');
                 	$('.xl_header').filter( function (index) {
                 		var thecurrent = $(this).attr('id');
                 		return thecurrent !== changing && x === $(this).val();
                 		}).val(function( id, value ) {
                 			var current = $(this).attr('id');
                 			if ( current !== changing )
                 				$(this).val('');
                 		});
    			});
    			    			jQuery( "#settings-accordion" ).accordion({
    				header : 'h3+p',
    				active : false,
    				
    				collapsible: true,
    				heightStyle:'content' 
    			});
    			
			});
			//]]>
		</script>
		<?php
		}
		
		
		/**
		 * Function to register the settings
		 */
		function xili_register_settings()
		{
			
			global $wp_registered_sidebars, $xili_language;
			if ( false === get_option( $this->settings_name, false ) )
				add_option( $this->settings_name, $this->get_default_theme_xili_options() );
				
		    // Register the settings with Validation callback
		    
		    $options = $this->get_theme_xili_options();
		    
		    register_setting( $this->settings_name, $this->settings_name, array( $this,'xili_validate_settings' ) );
		    // Add settings section
		    add_settings_section( 'xili_option_section_1', __('Look and feel of theme on visitors side', 'xili-language'), array( $this, 'xili_display_one_section' ), $this->settings_name );
		    
		    $field_args = array(
		      'option_name' => $this->settings_name,
		      'title'	  => __('Hide Flags', 'xili-language'),
		      'type'      => 'checkbox',
		      'id'        => 'no_flags',
		      'name'      => 'no_flags',
		      'desc'      => __('If checked, default flags are hidden...', 'xili-language'),
		      'std'       => '1', // like via customizer
		      'label_for' => 'no_flags',
		      'class'     => 'css_class'
		    );
		    add_settings_field( $field_args['id'], $field_args['title'] , array( $this, 'xili_display_one_setting') , $this->settings_name, 'xili_option_section_1', $field_args );
		    
		    $field_args = array(
		      'option_name' => $this->settings_name,
		      'title'	  => __('Show linked posts', 'xili-language'),
		      'type'      => 'checkbox',
		      'id'        => 'linked_posts',
		      'name'      => 'linked_posts',
		      'desc'      => __('Show Other Posts links in meta in single (even if menu or widget languages list).', 'xili-language'),
		      'std'       => '1',
		      'label_for' => 'linked_posts',
		      'class'     => 'css_class'
		    );
		    
		    add_settings_field( $field_args['id'], $field_args['title'] , array( $this, 'xili_display_one_setting'), $this->settings_name, 'xili_option_section_1', $field_args );
		    
		    $field_args = array(
		      'option_name' => $this->settings_name,
		      'title'	  => __('Text of link title', 'xili-language' ),
		      'type'      => 'text',
		      'id'        => 'linked_title',
		      'name'      => 'linked_title',
		      'desc'      => __('The text before the links of linked posts in other language', 'xili-language'),
		      'std'       => 'Read this post in',
		      'label_for' => 'linked_title',
		      'class'     => 'css_class'
		    );
		    
		    add_settings_field( $field_args['id'], $field_args['title'] , array( $this, 'xili_display_one_setting'), $this->settings_name, 'xili_option_section_1', $field_args );
		    
		    // section #2
		    
		    $menu_locations = get_registered_nav_menus() ;
			$navmenu_count =  0 ;
			foreach ( $menu_locations as $one_key => $one_location ) { 
					if ( false === strpos( $one_key , $this->menu_lang_sep ) ) $navmenu_count ++ ; // only core nav menu
			}
			
			if ( $navmenu_count > 0 )  {
				
				add_settings_section( 'xili_option_section_2', '<hr />' .__('Navigation Menus', 'xili-language'), array( $this, 'xili_display_one_section'), $this->settings_name );	
				
				$field_args = array(
		      'option_name' => $this->settings_name,
		      'title'	  => __('Instancing nav menus', 'xili-language'),
		      'type'      => 'checkbox',
		      'id'        => 'nav_menus',
		      'name'      => 'nav_menus',
		      'desc'      => __('Instantiation of nav menu for each language.', 'xili-language'),
		      'std'       => 'nav_menus',
		      'label_for' => 'nav_menus',
		      'class'     => 'css_class menus_instancing'
		    	);
		    	add_settings_field( $field_args['id'], $field_args['title'] , array( $this, 'xili_display_one_setting'), $this->settings_name, 'xili_option_section_2', $field_args );
			
		    	if ( $navmenu_count > 1 )  {
		    		
		    		foreach ( $menu_locations as $one_key => $one_location ) { 
						if ( false === strpos( $one_key , $this->menu_lang_sep ) ) {
							$indice = 'nav_menu_'.$one_key ;
							$nav_value = isset( $options[$indice] ) ? $options[$indice] : "";
				
							$field_args = array(
		      'option_name' => $this->settings_name,
		      'title'	  => sprintf( '-&nbsp;'.__('Instancing menu named %s:', 'xili-language') , '<strong> '.$one_location.'</strong>' ),
		      'type'      => 'checkbox',
		      'id'        => $indice,
		      'name'      => $indice,
		      'desc'      => sprintf( '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . __('Instantiation nav menu named %s  for each language.', 'xili-language') , '<strong> '.$one_location.'</strong>' ),
		      'std'       => 'nav_menu',
		      'label_for' => $indice,
		      'class'     => 'css_class menu_locations'
		    				);
		    				
		    				add_settings_field( $field_args['id'], $field_args['title'] , array( $this, 'xili_display_one_setting'), $this->settings_name, 'xili_option_section_2', $field_args );
		    
		    			}
					}
		    	}
			}
			// section #3 - widgets
			if ( $this->customize_clone_widget_containers ) {
			
				add_settings_section( 'xili_option_section_3', __('Sidebars', 'xili-language'), array( $this, 'xili_display_one_section'), $this->settings_name );
				
				foreach ( $wp_registered_sidebars as $one_key => $one_sidebar ) { 
					if ( false === strpos( $one_key , '_' ) ) {
						$indice = 'sidebar_'.$one_key ;
						
						
						$field_args = array(
			      'option_name' => $this->settings_name,
			      'title'	  => sprintf( __('Instancing widget named %s:', 'xili-language') , '<strong> '.$one_sidebar['name'].'</strong>' ),
			      'type'      => 'checkbox',
			      'id'        => $indice,
			      'name'      => $indice,
			      'desc'      => sprintf( __('Instantiation widget named %s  for each language.', 'xili-language') , '<strong> '.$one_sidebar['name'].'</strong>' ),
			      'std'       => 'sidebar_clone',
			      'label_for' => $indice,
			      'class'     => 'css_class'
			    				);
			    				
			    				add_settings_field( $field_args['id'], $field_args['title'] , array( $this, 'xili_display_one_setting'), $this->settings_name, 'xili_option_section_3', $field_args );
						
					}
				}
			}
			
			// Header Images
			// 2.9.10 - 1.3 -
			
				add_settings_section( 'xili_option_section_5', '<hr />' . __('Header image options', $this->admin_ui_domain), array( $this, 'xili_display_one_section'), $this->settings_name );
			
				$field_args = array(
		      'option_name' => $this->settings_name,
		      'title'	  => __('Multilingual header', 'xili-language'),
		      'type'      => 'checkbox',
		      'id'        => 'xl_header',
		      'name'      => 'xl_header',
		      'desc'      => __('If checked, one header per language can be selected', 'xili-language'),
		      'std'       => '1', // like via customizer
		      'label_for' => 'xl_header',
		      'class'     => 'css_class'
		    );
		    	add_settings_field( $field_args['id'], $field_args['title'] , array( $this, 'xili_display_one_setting') , $this->settings_name, 'xili_option_section_5', $field_args );
		    	
		    	
		    	add_settings_section( 'xili_option_section_6', __('Header image assignment', $this->admin_ui_domain), array( $this, 'xili_display_one_section'), $this->settings_name );
		    	
		    	$section = 0;
		    	// list the images
		    	if ( get_uploaded_header_images() ) {
		    	// add fields and language pop-up
		    		
		    		$this_upload_headers = get_uploaded_header_images();
		    		
		    		$this->show_header_assignment_fields ( $this_upload_headers );
					$section++;
		    	}
		    	
		    	$this_default_headers = $this->get_processed_default_headers () ;
		    	
		    	if ( !empty ( $this_default_headers ) ) {
		    		
					$this->show_header_assignment_fields ( $this_default_headers );
		    		$section++;
		    	}
		    	
		    	if ( $section == 0 ) {
		    		$header_setting_url = admin_url('/themes.php?page=custom-header'  );
		    		$field_args = array(
				      'option_name' => $this->settings_name,
				      'title'	  => __('Message:', $this->admin_ui_domain ),
				      'type'      => 'message',
				      'id'        => 'no_header_image',
				      'name'      => 'no_header_image',
				      'desc'      => __('This theme does not contains header image !', $this->admin_ui_domain ) . '<br />' . sprintf( __( 'The images must be uploaded (and cropped) in the %1$sheader settings%2$s page.', $this->admin_ui_domain),'<a href="'.$header_setting_url.'">' ,'</a>' ),
				      
				      'label_for' => 'no_header_image',
				      'class'     => 'css_class propagate'
				    );
		    		
		    		add_settings_field( $field_args['id'], $field_args['title'] , array( $this, 'xili_display_one_setting'), $this->settings_name, 'xili_option_section_6', $field_args );
		    		
		    	}
			
			// Authoring cloning
			
			if ( $this->authoring_options_admin ) {
			
				add_settings_section( 'xili_option_section_4', '<hr />' . __('Authoring options', $this->admin_ui_domain), array( $this, 'xili_display_one_section'), $this->settings_name );
				
				foreach ( $this->propagate_options as $one_key => $one_option ) { 
						
					$field_args = array(
				      'option_name' => $this->settings_name,
				      'title'	  => $one_option['name'],
				      'type'      => 'checkbox',
				      'id'        => $one_key,
				      'name'      => $one_key,
				      'desc'      => $one_option['description'],
				      'std'       => '1',
				      'label_for' => $one_key,
				      'class'     => 'css_class propagate'
				    );
				    				
				    add_settings_field( $field_args['id'], $field_args['title'] , array( $this, 'xili_display_one_setting'), $this->settings_name, 'xili_option_section_4', $field_args );
							
					
				}
			}
			
			// Permalinks option
			$xili_functionsfolder = get_stylesheet_directory() . '/functions-xili' ;
			if ( file_exists( $xili_functionsfolder . '/multilingual-permalinks.php') && $xili_language->is_permalink ) {
			add_settings_section( 'xili_option_section_7', '<hr />' .__( 'Permalinks for languages', $this->admin_ui_domain ), array( $this, 'xili_display_one_section' ), $this->settings_name );
		    
		    $field_args = array(
		      'option_name' => $this->settings_name,
		      'title'	  => __('Activate Permalinks', $this->admin_ui_domain),
		      'type'      => 'checkbox',
		      'id'        => 'perma_ok',
		      'name'      => 'perma_ok',
		      'desc'      => __('If checked, xili-language incorporate language in permalinks... (premium services for donators, see docs)', $this->admin_ui_domain),
		      'std'       => '1', // like via customizer
		      'label_for' => 'perma_ok',
		      'class'     => 'css_class permalinks-set'
		    );
		    add_settings_field( $field_args['id'], $field_args['title'] , array( $this, 'xili_display_one_setting') , $this->settings_name, 'xili_option_section_7', $field_args );
			
			}
			
		}


		function xili_display_one_section( $section ){ 
			switch ( $section['id'] ) {
		        case 'xili_option_section_1':
						echo '<p><span class="triangle"></span>'. __('Some settings for visitors..', 'xili-language') .'</p>';
					break;
				case 'xili_option_section_2':
						echo '<p><span class="triangle"></span>'. sprintf (__( 'Enable (or not) instantiation of the registered menu locations.<br /> After changes saved, <a href="%s" >go to Menus settings</a> and fill menus for each language.', 'xili-language' ) , 'nav-menus.php') .'</p>';
					break;
				case 'xili_option_section_3':
						echo '<p><span class="triangle"></span>'. sprintf (__( 'Enable (or not) instantiation of the registered sidebars.<br /> After changes saved, <a href="%s" >go to Widget Menus</a> and fill sidebar for each language.', 'xili-language' ) , 'widgets.php') .'</p>';
					break;
				case 'xili_option_section_4':
						echo '<p><span class="triangle"></span>'. __( 'Allow (or not) to copy feature or attribute when creating a post from one language to another.', $this->admin_ui_domain ) .'</p>';
					break;
				case 'xili_option_section_5':
						echo '<p><span class="triangle"></span>'. __( 'Allow (or not) to choose a header image per language.', $this->admin_ui_domain ) .'</p>';
					break;
				case 'xili_option_section_6':
						echo '<p><span class="triangle"></span>'. __( 'Assignment of header image to a language.', $this->admin_ui_domain ) .'</p>';
					break;
				case 'xili_option_section_7':
						echo '<p><span class="triangle"></span>'. __( 'Activate Languages (or aliases) in permalinks.', $this->admin_ui_domain ) .'</p>';
					break;	
			}
				
		}

		/**
		 * one line in section
		 */
		function xili_display_one_setting( $args )
		{
		    extract( $args );
		    
		    $options = $this->get_theme_xili_options();
		    
		    switch ( $type ) {
		    	
		    	  case 'message';
		    		echo ($desc != '') ? "<span class='description'>$desc</span>" : "...";
		          break;
		          
		          case 'text':
		          	$set = ( isset ( $options[$id] ) ) ? $options[$id] : '';
		          	$set = stripslashes($set);
		    		$set = esc_attr( $set);	
		              
		              echo "<input class='regular-text$class' type='text' id='$id' name='" . $option_name . "[$id]' value='$set' />";
		              echo ($desc != '') ? "<br /><span class='description'>$desc</span>" : "";
		          break;
		          
		          case 'checkbox':
		          	  $set = ( isset ( $options[$id] ) ) ? $options[$id] : false;
		          	  
		          	  $checked = checked ( $set, $std, false ); 
		          	  echo "<input $checked class='$class' type='checkbox' id='$id' name='" . $option_name . "[$id]' value='$std' />";
		              echo ($desc != '') ? "<br /><span class='description'>$desc</span>" : "";
		          
		          break;
		          
		          case 'select':
		          		$set = ( isset ( $options[$id] ) ) ? $options[$id] : false;
		          		
		          		echo "<select id='$id' name='" . $option_name . "[$id]' />";
		          			
		          			foreach ( $option_values as $value => $content ) {
		          				
		          				echo "<option value='$value' " . selected ( $set , $value , false) . ">$content</option>";
		          			}
		          			
		          		echo "</select>";
		          		echo ($desc != '') ? "<br /><span class='description'>$desc</span>" : "";
		          break;
		    }
		}
		
		function show_header_assignment_fields ( $headers ) {
			
			$language_xili_settings = get_option('xili_language_settings'); 
			$language_list = array();
			foreach ( $language_xili_settings['languages_list'] as $one_language ) {
				
				$language_list[$one_language->slug] = $one_language;
				
			}			
			
			foreach ( $headers as $header_key => $header ) {
				
				$header_thumbnail = $header['thumbnail_url'];
				$header_url = $header['url'];
				$header_desc = empty( $header['description'] ) ? $header['attachment_id'] : $header['description'];
				
				if ( isset ( $header['attachment_id'] ) ) {
					
					$title = get_the_title( $header['attachment_id'] );
					
					$header_desc = ( '' == $title ) ? $header_key . '(' . $header['attachment_id'] . ')' : $title;
					 
				} else { // default from theme
					
					$header_desc = empty( $header['description'] ) ? '?' : $header['description'];
				}
				
				
				$field_args = array(
				      'option_name' => $this->settings_name,
				      'title'	  => $header_desc,
				      'desc'	  => __('Select language where this header will be displayed...', $this->admin_ui_domain ),
				      'id'        => esc_attr ( $header_key ), // sanitize ??
				      'name'      => $header_key,
				      'header_desc'      => $header_desc,
				      'std'       => '1',
				      'label_for' => $header_key,
				      'class'     => 'css_class header-img',
				      'header_thumbnail' => $header_thumbnail,
				      'header_url' => $header_url,
				      //'attachment_id' => $header['attachment_id'],
				      'type ' => 'select',
				      'option_values'   => $language_list,
				    );
				    				
				    add_settings_field( $field_args['id'], $field_args['title'] , array( $this, 'show_one_header_to_select'), $this->settings_name, 'xili_option_section_6', $field_args );
			}
			
		}
		
		function show_one_header_to_select ( $header ) {
			extract( $header ); 
			echo '<div class"'.$class.'">';
			
			$default_class = ( $header_url == get_theme_mod( 'header_image' ) ) ? ' class= "header-imgs outlined" ' : ' class= "header-imgs" ';
			
			$width = ' width="230" ';
			echo '<img src="' . set_url_scheme( $header['header_thumbnail'] ) . '" alt="' . esc_attr( $header['header_desc'] ) .'" title="' . esc_attr( $header['header_desc'] ) . '"' . $width . $default_class . '/>';
			      	  
		    $this->show_language_pop_up_selector ( $header ) ;
			
			echo '</div>';
			
		}
		
		function show_language_pop_up_selector ( $header ) {
			extract( $header );
			
			$options = $this->get_theme_xili_options();  
				
			$set = ( isset ( $options[$id] ) ) ? $options[$id] : false; // to finish
		          		
		     echo "&nbsp;&nbsp;&nbsp;<select class='xl_header' id='$id' name='" . $option_name . "[$id]' />";
		     
		     echo "<option value='' >".__('Select language...', $this->admin_ui_domain )."</option>";     			
		     foreach ( $option_values as $value => $language ) {
		          				
		         echo "<option value='$value' " . selected ( $set , $value , false) . ">" . $language->description . "</option>";
		     }
		     echo "</select>";
		     echo ($desc != '') ? "<br /><span class='description'>$desc</span>" : "";
		
		}
		
		function xili_validate_settings ($input) {
			
		  	
		  	$default = 'en_us'; // currently the default language of theme in core WP
			$language_xili_settings = get_option('xili_language_settings');
			$language_slugs_list =  array_keys ( $language_xili_settings['langs_ids_array'] ) ;
			
		  	$checked_locations = array();
		  	$header_language = array();
		  	foreach($input as $k => $v)
		  	{
		    	$newinput[$k] = trim($v);
		    	
		    	if ( false !== strpos ( $k , 'nav_menu_' ) ) {
		    		 $location = str_replace ( 'nav_menu_', '', $k);
		    		 $checked_locations[] = $location;
		    	}
		    	if ( in_array ( $v, $language_slugs_list ) ) {
		    		$header_language[$v] = $k;
		    	}		    	
		  	}
		  	
			
			$theme_mod_locations = get_theme_mod( 'nav_menu_locations' );
			if ( $theme_mod_locations ) { // if new theme not started
				$menu_locations_keys = array_keys ( $theme_mod_locations );
				
				foreach ( $menu_locations_keys as $oneloc ) {
					// multiple locations or one only location
					if (  ( $checked_locations && !in_array ( $oneloc, $checked_locations ) ) || ( $checked_locations == array() && !isset ( $input['nav_menus'] ) ) ) {
						foreach ( $language_slugs_list as $slug ) {
							$one_menu_location = $oneloc . $this->menu_lang_sep . $slug ;
							unset ( $theme_mod_locations[$one_menu_location] ); // previous menu location set when menu content attached to location
						}
					}
				}
				set_theme_mod( 'nav_menu_locations',  $theme_mod_locations ); 
			}
			
		  	// 'no_flags' => false , 'linked_posts' => 'show_linked',  'nav_menus' => false
		  	if ( !isset ( $input['no_flags'] ) ) $newinput['no_flags'] = '';
		  	if ( !isset ( $input['linked_posts'] ) ) $newinput['linked_posts'] = '';
		  	if ( !isset ( $input['nav_menus'] ) ) $newinput['nav_menus'] = '';
		  	if ( !isset ( $input['xl_header'] ) ) $newinput['xl_header'] = '';
		  	if ( !isset ( $input['perma_ok'] ) ) $newinput['perma_ok'] = '';
		  	
		  	if ( $newinput['perma_ok'] == 1 ) flush_rewrite_rules( false ); // to refresh
		  	
		  	$newinput['xl_header_list'] = $header_language;
		  	return $newinput;
		}
		
		
		function print_styles_xili_options ( $params ) {
			
			
			
			$screen = get_current_screen();
			$triangle = admin_url( '/images/arrows-dark-vs.png' );
			if ( $screen->id == $this->xili_theme_page ) { 
				echo "<!---- xl css --->\n";
	   			echo '<style type="text/css" media="screen">'."\n";
				echo ".menu_locations { display:block; margin-left:20px !important; } \n";
				echo ".outlined { border:1px #666 solid !important; } \n";
				echo ".header-imgs { display:inline-block; vertical-align:middle; } \n";
				
				echo "h3+p span.triangle { float:left; width:20px; height:20px; background-repeat:no-repeat; background-image: url($triangle); background-position:0 -106px; } \n";
				echo "h3+p:hover, p.ui-state-active { background-color: #ededed;}  \n";
				echo "h3+p  { outline:0; }  \n";
				echo "p.ui-state-active span.triangle { background-position:0 0}  \n";
				
				echo '</style>'."\n";
			}
		
		}
		
		
		function print_scripts_xili_options() {
			wp_enqueue_script('jquery-ui-accordion');

		}
		
		function xili_theme_options_help_page () {
			
			$screen = get_current_screen();
			$header_setting_url = admin_url('/themes.php?page=custom-header'  );
			if ( $screen->id != $this->xili_theme_page )
		        return;
			$help = '<p>' . sprintf (__( 'Some themes provide customization options that are grouped together on a Theme Options screen. If you change themes, options may change or disappear, as they are theme-specific. Your current theme, %1$s%2$s, provides the following Theme Options:', 'xili-language' ), $this->theme_name, $this->child_suffix ) . '</p>' .
					'<ol>' .
						'<li>' . __( '<strong>Multilingual Flags style</strong>: Check if you want to hidden flags and see only language names. (no style generated)...', 'xili-language' ) . '</li>' .
						'<li>' . __( '<strong>Other posts in other languages links in singular (page or post)</strong>: Check if you want to show links of posts in other languages.', 'xili-language' ) . '</li>' .
						'<li>' . __( '<strong>Instancing nav menu for each language</strong>: Check if you want to clone menu location.', 'xili-language' ) . '</li>' .
						'<li>' . __( '<strong>Enable instantiation for the registered sidebars</strong>: Check if you want to clone one the sidebars for each language.', 'xili-language' ) . '</li>' .
						'<li>' . __( '<strong>Propagation of attributes and/or features of the post during creation of a translation</strong>: Check if you want to clone one of these attributes.', $this->admin_ui_domain ) . '</li>' .
						'<li>' . __( '<strong>One Header Image per language</strong>: After adding (and cropping) a series of images in Appearance Header settings page, here you assign a language per image. If not choosen, the default header image (black border) is selected for unknown language.', $this->admin_ui_domain ) . '</li>' .
						'<p>' . sprintf( __( 'The images must be uploaded (and cropped) in the %1$sheader settings%2$s page.', $this->admin_ui_domain),'<a href="'.$header_setting_url.'">' ,'</a>' ). '</p>'.
						'<li>' . __( '<strong>Language in Permalinks</strong>: Reserved for donators. Never forget to refresh permalinks after changing settings !', $this->admin_ui_domain ) . '</li>' .
						
					'</ol>' .
					'<p>' . __( 'Remember to click "Save Changes" to save any changes you have made to the theme options.', 'xili-language' ) . '</p>' .
					'<p><strong>' . __( 'For more information:', 'xili-language' ) . '</strong></p>' .
					'<p>' . __( '<a href="http://codex.wordpress.org/Appearance_Theme_Options_Screen" target="_blank">WP Documentation on Theme Options</a>', 'xili-language' ) . '</p>' .
					'<p>' . __( '<a href="http://wiki.xiligroup.org" target="_blank">Xili Wiki</a>', 'xili-language' ) . '</p>'.
					'<p>' . __( '<a href="http://dev.xiligroup.com/?post_type=forum" target="_blank">Xili Support Forums</a>', 'xili-language' ) . '</p>';
		
			$screen->add_help_tab(  array(
		        'id'	=> $this->xili_theme_page,
		        'title'	=> __('Help'),
		        'content'	=>	$help	));
		}
	
	} // end class
}

/**
 * use it to get xili-options of the current multilingual child theme
 *
 */
function get_theme_xili_options() {
	global $xili_language_theme_options ;
	return get_option( $xili_language_theme_options->settings_name, $xili_language_theme_options->get_default_theme_xili_options() );
}

?>