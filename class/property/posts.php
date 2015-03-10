<?php

namespace WP_Domain_Work\Property;

class posts extends basic {

	/**
	 * @var string
	 */
	public $singular;

	/**
	 * @var string
	 */
	public $plural;

	/**
	 * @var array
	 */
	protected $query_args;

	/**
	 * @var array
	 */
	public $value = [];

	/**
	 * Constructer
	 *
	 * @param  string $var
	 * @param  array $args
	 * @param  integer|WP_Post $post
	 * @return bool
	 */
	public function __construct( $var, Array $args, $post = 0 ) {
		if ( ! parent::__construct( $var, $args ) ) {
			return false;
		}
		$query_args = array_key_exists( 'query_args', $args ) ? (array) $args['query_args'] : [];
		$this->query_args = wp_parse_args( $this->parse_query_args( $args, $post ), $query_args );
		$this->query_args = apply_filters( 'wp_domain_work_posts_property_query_args', $this->query_args, $args, $post );

		if ( $this->query_args ) {
			$this->set_value();
		}

		$singular = $plural = $var;
		if ( array_key_exists( 'singular', $args ) && is_string( $args['singular'] ) && $args['singular'] ) {
			$this->singular = $args['singular'];
		}
		if ( array_key_exists( 'plural', $args ) && is_string( $args['plural'] ) && $args['plural'] ) {
			$this->plural = $args['plural'];
		}
		return true;
	}

	protected function set_value() {
		$posts = get_posts( $this->query_args );
		if ( $posts ) {
			$this->value = $posts;
		}
	}

	public function getArray() {
		return get_object_vars( $this );
	}

	protected function parse_query_args( $args, $post ) {
		$r = [];
		if ( ! array_key_exists( 'post_status', $args ) ) {
			$stati = is_admin() ? get_post_stati( [ 'internal' => false ] ) : get_post_stati( [ 'public' => true ] );
			$r['post_status'] = $stati;
		} else {
			$r['post_status'] = $args['post_status'];
		}
		if ( array_key_exists( 'post_type', $args ) ) {
			$post_type = array_filter( (array) $args['post_type'], function( $post_type ) {
				return post_type_exists( $post_type );
			} );
			if ( $post_type ) {
				$r['post_type'] = $post_type;
			}
		}
		if ( ! array_key_exists( 'post_type', $r ) ) {
			$r['post_type'] = get_post_type( $post );
		}
		return $r;
	}

}
