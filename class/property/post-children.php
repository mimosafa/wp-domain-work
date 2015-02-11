<?php

namespace property;

class post_children {

	public $name;
	public $label;
	public $value = [];

	protected $_type = 'post_children';

	public function __construct( $var, Array $args, $post = 0 ) {
		if ( ! is_string( $var ) || ! $var ) {
			return false;
		}
		$this->name = $var;
		$this->label = array_key_exists( 'label', $args ) && is_string( $args['label'] )
			? $args['label']
			: ucwords( str_replace( [ '_', '-' ], ' ', trim( $var ) ) );
		;
		if ( array_key_exists( 'description', $args ) ) {
			$this->description = $args['description'];
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