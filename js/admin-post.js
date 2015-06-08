/**/
'use strict';

window.WPDW = window.WPDW || {};

( function( $, _, Backbone ) {

	if ( typeof WPDW.assetForms === 'undefined' || typeof WPDW.form_id_prefix === 'undefined' )
		return;

	// Functions

	// Create WP nonce elements
	var createNonce = function( params ) {
		return _.template(
			'<input type="hidden" id="<%- name %>" name="<%- name %>" value="<%- value %>" />' +
			'<input type="hidden" name="_wp_http_referer" value="<%- refer %>" />',
			params
		);
	};

	var createInputString = function( params ) {
		return _.template(
			'<input type="' +
			'<% if ( type === \'string\' ) { %>' +
			'text" class="regular-text' +
			'<% } %>' +
			'" name="<%- name %>" value="<%- value %>" />',
			params
		);
	};

	// Backbone.js application

	// Model: Asset
	var Asset = Backbone.Model.extend( {
		initialize: function() {
			var elID = '#' + WPDW.form_id_prefix + this.get( 'name' );
			var assetForm = new AssetView( { el: elID, model: this } );
		},
	} );

	// View: AssetView
	var AssetView = Backbone.View.extend( {
		initialize: function() {
			if ( ! this.$el.length )
				return;
			this.render();
		},
		render: function() {
			var params = this.model.attributes;
			if ( typeof params['name'] === 'undefined' || typeof params.nonce === 'undefined' )
				return;
			var $form = this.renderForm( params );
			this.$el.html( $form ).append( this.renderNonce( params.nonce ) );
		},
		renderForm: function( params ) {
			return createInputString( params );
		},
		renderNonce: function( nonce ) {
			return createNonce( nonce );
		}
	} );













	_.each( WPDW.assetForms, function( asset ) {
		var params = WPDW.assets[asset];
		$.extend( true, params, {
			value: typeof WPDW.assetValues[asset] === 'undefined' ? '' : WPDW.assetValues[asset]
		} );
		if ( typeof params.asset !== 'undefined' ) {
			if ( params.admin_form_style === 'block' ) {
				//
			} else {
				//
			}
		} else {
			var model  = new Asset( params );
		}
		
		//console.log( model );
	} );



} )( jQuery, _, Backbone );
