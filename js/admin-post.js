window.wpdw = window.wpdw || {};

( function( $ ) {

	if ( typeof wpdw.forms !== 'undefined' ) {
		$.each( wpdw.forms, function( asset, args ) {
			if ( args.deps ) {
				console.log( asset );
				console.log( args.deps );
			}
		} )
	}

	if ( typeof wpdw.metaboxes === 'undefined' )
		return;

	//

} )( jQuery );