/**/
'use strict';

window.WPDW = window.WPDW || {};

( function( $, _, Backbone ) {

	if ( typeof WPDW.assetForms === 'undefined' || typeof WPDW.form_id_prefix === 'undefined' )
		return;

	// Templates
	var templates = {
		nonce: _.template(
			'<input type="hidden" id="<%- name %>" name="<%- name %>" value="<%- value %>" />' +
			'<input type="hidden" name="_wp_http_referer" value="<%- refer %>" />'
		),
		string: _.template(
			'<input class="regular-text" type="text" name="<%- name %>" value="<%- value %>" />'
		),
	};

	// Backbone.js application

	// Model: Asset
	var Asset = Backbone.Model.extend( {

		defaults: function() {
			return {
				name : '',
				nonce: '',
				value: ''
			};
		},

		initialize: function() {
			/*
			var elID = '#' + WPDW.form_id_prefix + this.get( 'name' );
			var assetForm = new AssetView( { el: elID, model: this } );
			*/
		},

	} );

	var AssetsAsset = Asset.extend();

	var Assets = Backbone.Collection.extend( {
		model: AssetsAsset,
		initialize: function() {
			console.log( this );
		},
	} );

	// View: Asset
	var AssetView = Backbone.View.extend( {

		tagName: 'fieldset',
		model  : Asset,
		events : {},

		initialize: function() {
			if ( ! this.$el.length )
				return;
			this.render();
		},

		render: function() {
			var params = this.model.attributes;
			if ( ! params['name'] || ! params.nonce )
				return;
			var $form = this.renderForm( params );
			this.$el.html( $form ).append( this.renderNonce( params.nonce ) );
			return this;
		},

		renderForm: function( params ) {
			if ( params['type'] === 'string' )
				return templates.string( params );
		},

		renderNonce: function( nonce ) {
			return templates.nonce( nonce );
		},

	} );

	// View: Assets
	var AssetsView = AssetView.extend( {

		collection: Assets,

		initialize: function() {
			if ( ! this.$el.length )
				return;
		},

		renderLayout: function( params ) {
			if ( params.admin_form_style === 'block' ) {
				this.renderTable( params );
			}
		},

		render: function() {
			var params = this.model.attributes;
			if ( ! params['name'] || ! params.nonce )
				return;
			var $form = this.renderForm( params );
			this.$el.html( $form ).append( this.renderNonce( params.nonce ) );
			return this;
		},

		renderForm: function( params ) {
			if ( params['type'] === 'string' )
				return templates.string( params );
		},

	} );













	_.each( WPDW.assetForms, function( asset ) {

		var elID = '#' + WPDW.form_id_prefix + asset,
		    params = WPDW.assets[asset];

		$.extend( true, params, { value: WPDW.assetValues[asset] } );

		if ( typeof params.assets !== 'undefined' ) {
			//
		} else {
			var asset = new Asset( params );
			var assetView = new AssetView( { el: elID, model: asset } );
			//var model = new Asset( params );
		}

	} );



} )( jQuery, _, Backbone );
