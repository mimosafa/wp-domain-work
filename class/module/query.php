<?php

namespace module;

/**
 * This is 'Trait',
 * must be used in '\(domain)\query' class.
 *
 * @uses
 */
trait query {

	public function __construct() {

		/**
		 * Custom init method defined each domains
		 */
		if ( method_exists( $this, 'init' ) ) {
			$this->init();
		}

		/**
		 * Hide in front-end
		 */
		if ( ! is_admin() && property_exists( $this, 'private_in_frontend' ) && true === $this->private_in_frontend ) {
			$this->forbidden();
		}

		/**
		 * 
		 */
		if ( is_admin() && property_exists( $this, 'filter_others') && true === $this->filter_others ) {
			add_action( 'pre_get_posts', [ $this, 'filter_others' ], 11 );
		}

		/**
		 * Main query
		 */
		if ( property_exists( $this, 'query_args' ) && is_array( $this->query_args ) ) {
			add_action( 'pre_get_posts', [ $this, 'main_query' ], 10 );
		}

	}

	/**
	 *
	 */
	public function main_query( $query ) {
		if ( ! $query->is_main_query() || $query->is_singular() ) {
			return;
		}
		foreach ( $this->query_args as $key => $val ) {
			$query->set( $key, $val );
		}
	}

	public function filter_others( $query ) {
		$post_type = $query->query_vars['post_type'];
		if ( current_user_can( 'edit_others_posts', $post_type ) ) {
			return;
		}
		$user_id = get_current_user_id();
		$query->set( 'author', $user_id );
	}

	/**
	 * Force 403 forbidden for not permitted user.
	 */
	private function forbidden() {
		//status_header( 403 );
		header( 'HTTP/1.1 403 Forbidden' );
		echo '<h1>403 Forbidden</h1>';
		die();
	}

}
