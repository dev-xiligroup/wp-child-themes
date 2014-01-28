// javascript to preview customize

( function( $ ){
    wp.customize( 'xili_2013_theme_options[no_flags]', function( value ) { 
        value.bind( function( to ) {
        	
            //$( '#site-generator a.wptuts-credits' ).html( to );
            
        } );
    } );
} )( jQuery );