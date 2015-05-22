/* global wpdw */
var wpdw = {

	init: function() {
		console.log( this );
	}

};

( function( $ ) {

	if ( typeof wpdwData === 'undefined' )
		return;

	wpdw = $.extend( wpdw, wpdwData );

	//wpdw.init();

} )( jQuery );
