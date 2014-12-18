<?php

namespace property;

class post_children {

	public $name;

	public $label;

	protected $_type = 'post_children';

	protected $_query_args = [];

	public function __construct( $var, Array $arg ) {
		
		if ( !is_string( $var ) ) {
			return null;
		}

		//

		if ( !$this -> set_arg( $arg ) ) {
			return null;
		}

		//

		/**
		 * property name
		 */
		$this -> name = $var;

		/**
		 * Property label
		 */
		$this -> label = array_key_exists( 'label', $arg ) && is_string( $arg['label'] )
			? $arg['label']
			: ucwords( str_replace( [ '_', '-' ], ' ', trim( $var ) ) );
		;

		/**
		 * Description
		 */
		if ( array_key_exists( 'description', $arg ) ) {
			$this -> description = $arg['description'];
		}

	}

	private function set_arg( $arg ) {

		if ( !array_key_exists( 'parent', $arg ) || ( !$post_parent = absint( $arg['parent'] ) ) ) {
			$post_parent = get_post() -> ID;
		}
		if ( !$post_parent ) {
			return;
		}

		$post_type = array_key_exists( 'post_type', $arg ) ? (array) $arg['post_type'] : 'any';

		$posts_per_page = -1;
		//
		
		$_query_args = compact( 'post_parent', 'post_type', 'posts_per_page' );
		if ( array_key_exists( 'query_args', $arg ) && is_array( $arg['query_args'] ) ) {
			$_query_args = \utility\md_array_merge( $arg['query_args'], $_query_args );
		}

		$this -> _query_args = $_query_args;

		return true;

	}

	public function getQueryArgs() {
		return $this -> _query_args;
	}

	public function getArray() {
		return get_object_vars( $this );
	}

}