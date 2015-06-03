window.WPDW = window.WPDW || {};
WPDW.Prop = WPDW.Prop || {};

( function( $ ) {
	"use strict";

	// Model
	WPDW.Prop.Asset = Backbone.Model.extend( {
		defaults: {
			name: '',
			type: '',
			multiple: false,
		},
	} );

	// Collection
	WPDW.Prop.Assets = Backbone.Collection.extend( {
		model: WPDW.Prop.Asset,
		initialize: function() {
			if ( typeof WPDW.assetForms === 'undefined' )
				return;
			_.each( WPDW.assetForms, function( asset ) {
				console.log( WPDW.assets[asset] );
			} );
		}
	} );

	// View
	WPDW.Prop.View = Backbone.View.extend();

} )( jQuery, _, Backbone );

( function( $, _ ) {

	var assets = new WPDW.Prop.Assets();

} )( jQuery, _ );
