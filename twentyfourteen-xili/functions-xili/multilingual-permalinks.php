<?php
/**
 * File reserved
 * for donators - http://dev.xiligroup.com/?page_id=2419
 * for contributors : free published themes,  free published plugins,  translators, authors of docs about xili-language trilogy, testers.
 *
 * must be in themes/... theme name .../functions-xili/ sub-folder
 * examples of embedded themes like  twentythirteen-xili (http://2013.extend.xiligroup.org/)
 *
 * After installation and language alias defining, permalinks must be refreshed
 *
 *
 * Author:mswppi dev.xiligroup
 */


// 2014-02-01 - latest tests with XL 2.10.0

// updated 2013-12-06 - new permalinks class added in xili-language plugin with more rewrite rules
// updated 2013-11-11 - no resolution if menu settings - proxy fixes
// updated 2013-11-09 - category_list lang - and dummy lang for tag - thanks to Henriette report
// special for proxy 20130409
// best performance with custom structure : /%post_id%/%postname%/

/**
 * only if permalink structure and alias for language
 */
add_filter ( 'alias_rule', 'xili_language_trans_slug_qv' ) ;


/**
 * called from XL (plugins_loaded priority 1)
 *
 */
function xl_permalinks_init () {
	global $XL_Permalinks_rules;
	if ( get_option('permalink_structure') && class_exists('XL_Permalinks_rules') ) {
		$XL_Permalinks_rules = new XL_Permalinks_rules ();

		add_permastruct ( 'language', '%lang%', true, 1 );
		add_permastruct ( 'language', '%lang%', array('with_front' => false) );

	}
}

?>