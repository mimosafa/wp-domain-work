<?php
namespace WPDW\Device\Asset;

trait asset_methods {

	/**
	 * Constructor
	 *
	 * @param  array $args
	 */
	public function __construct( Array $args ) {
		foreach ( $args as $key => $val ) {
			if ( property_exists( __CLASS__, $key ) )
				$this->$key = $val;
		}
	}

	/**
	 * @access public
	 *
	 * @param  int|WP_Post $post
	 * @return mixed
	 */
	public function get( $post ) {
		if ( ! $post = get_post( $post ) )
			return;
		$get = 'get_' . $this->model;
		if ( method_exists( __CLASS__, $get ) )
			return $this->$get( $post );
	}

	/**
	 * @access public
	 *
	 * @param  int|WP_Post $post
	 * @param  mixed $value
	 */
	public function update( $post, $value ) {
		if ( ! $post = get_post( $post ) )
			return;
		$update = 'update_' . $this->model;
		if ( method_exists( __CLASS__, $update ) )
			return $this->$update( $post, $value );
	}

	/**
	 * @access public
	 *
	 * @param  int|WP_Post $post
	 * @return array
	 */
	public function get_vars( $post ) {
		return array_merge( get_object_vars( $this ), [ 'value' => $this->get( $post ) ] );
	}

}
