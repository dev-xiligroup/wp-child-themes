<?php 
	if ( 'html5' == $format ) {
		$form = '<form role="search" method="get" class="search-form" action="' . esc_url( home_url( '/' ) ) . '">
			<label>
				<span class="screen-reader-text">' . _x( 'Search for:', 'label', 'twentyfourteen' ) . '</span>
				<input type="search" class="search-field" placeholder="' . esc_attr_x( 'Search &hellip;', 'placeholder', 'twentyfourteen' ) . '" value="' . get_search_query() . '" name="s" title="' . esc_attr_x( 'Search for:', 'label', 'twentyfourteen' ) . '" />
			</label>
			<input type="submit" class="search-submit" value="'. esc_attr_x( 'Search', 'submit button', 'twentyfourteen' ) .'" />
		</form>';
	} else {
		$form = '<form role="search" method="get" id="searchform" class="searchform" action="' . esc_url( home_url( '/' ) ) . '">
			<div>
				<label class="screen-reader-text" for="s">' . _x( 'Search for:', 'label', 'twentyfourteen' ) . '</label>
				<input type="text" value="' . get_search_query() . '" name="s" id="s" />
				<input type="submit" id="searchsubmit" value="'. esc_attr_x( 'Search', 'submit button', 'twentyfourteen' ) .'" />
			</div>
		</form>';
	}
	echo $form;
?>