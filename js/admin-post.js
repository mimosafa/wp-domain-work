/**/
"use strict";

window.WPDW = window.WPDW || {};

( function( $, _, Backbone ) {

	if ( typeof WPDW.assetForms === 'undefined' )
		return;

	var AssetModel = Backbone.Model.extend();

	var AssetView = Backbone.View.extend( {
		//
	} );

	_.each( WPDW.assetForms, function( asset ) {
		var params = WPDW.assets[asset];
		$.extend( true, params, { value: WPDW.assetValues[asset] } );
		var model  = new AssetModel( params );
		console.log( model );
	} );















} )( jQuery, _, Backbone );
