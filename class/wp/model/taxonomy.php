<?php

namespace WP_Domain_Work\WP\model;

class taxonomy {

	protected $post;

	public function __construct( $post ) {
		if ( ! $post = get_post( $post ) ) {
			return;
		}
		$this->post = $post;
	}

	public function get( $taxonomy, $single = false ) {
		$value = get_the_terms( $this->post, $taxonomy );
		if ( $single === false && count( $value ) === 1 ) {
			return array_shift( $value );
		}
		return $value ? $value : false;
	}

}
