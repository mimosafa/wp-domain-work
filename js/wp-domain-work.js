/* global wpdw */
var WPDW = {};

( function( $ ) {

	if ( typeof WPDWData === 'undefined' )
		return;

	WPDW = $.extend( WPDW, WPDWData );

} )( jQuery );
