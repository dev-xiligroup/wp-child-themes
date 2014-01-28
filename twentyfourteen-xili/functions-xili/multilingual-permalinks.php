<?php
// updated 2013-12-06 - new class and more rewrite rules
// updated 2013-11-11 - no resolution if menu settings - proxy fixes
// updated 2013-11-09 - category_list lang - and dummy lang for tag - thanks to Henriette report 
// special for proxy 20130409
// best performance with custom structure : /%post_id%/%postname%/

function xili_trans_slug_qv ( $lang_slug ) {
	global $xili_language;
	
	if ( isset ( $_POST['language_alias'] ) ) $xili_language->xili_settings = get_option('xili_language_settings'); // need update !
	
	$short = ( isset ( $xili_language->xili_settings['lang_features'][$lang_slug]['alias'] ) ) ?  $xili_language->xili_settings['lang_features'][$lang_slug]['alias'] : $lang_slug ;
	
	return $short;
} 
add_filter ( 'alias_rule', 'xili_trans_slug_qv' ) ;


/** 
 * called from XL (plugins_loaded 1)
 *
 */
function xl_permalinks_init () { 
	global $XL_Permalinks_rules;
	if ( get_option('permalink_structure') ) { 
		$XL_Permalinks_rules = new XL_Permalinks_rules ();
	
		add_permastruct ( 'language', '%lang%', true, 1 );
		add_permastruct ( 'language', '%lang%', array('with_front' => false) );
	
	}
}



?>