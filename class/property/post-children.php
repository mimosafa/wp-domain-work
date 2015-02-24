<?php

namespace WP_Domain_Work\Property;

class post_children extends basic {

	private $_parent_id;

	private $_post_type = [];

	private $_posts_per_page;

	public $value = [];

	public function __construct( $var, Array $args, $post = 0 ) {
		if ( ! parent::__construct( $var, $args ) ) {
			return false;
		}
		$this->_parent_id = get_post( $post )->ID;
		if ( array_key_exists( 'plural', $args ) && is_string( $args['plural'] ) && $args['plural'] ) {
			$this->plural = $args['plural'];
		}
		if ( array_key_exists( 'post_type', $args ) ) {
			$post_type = (array) $args['post_type'];
			$this->_post_type = array_filter( $post_type, function( $post_type ) {
				return post_type_exists( $post_type );
			} );
		}
		
		$query_args = array_key_exists( 'query_args', $args ) && is_array( $args['query_args'] )
			? $args['query_args'] : []
		;
		$_query_args = [];
		$_query_args['post_parent'] = get_post( $post )->ID;
		$_query_args['post_type'] = array_key_exists( 'post_type', $args ) && post_type_exists( $args['post_type'] )
			? $args['post_type'] : 'any'
		;
		$_query_args['posts_per_page'] = array_key_exists( 'posts_per_page', $args ) && absint( $args['posts_per_page'] ) > 0
			? absint( $args['posts_per_page'] ) : -1
		;
		$query_args = array_merge( $query_args, $_query_args );
		if ( $children = get_posts( $query_args ) ) {
			$this->value = $children;
		}
	}

	public function getArray() {
		return get_object_vars( $this );
	}

}
