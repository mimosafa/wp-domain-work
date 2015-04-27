<?php
namespace WPDW\Device\Asset;

trait asset_model {

	/**
	 * @access public
	 *
	 * @param  int|WP_Post $post
	 * @return mized
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
	 * Model: post_meta - get
	 *
	 * @access private
	 *
	 * @param  WP_Post $post
	 * @return mixed
	 */
	private function get_post_meta( \WP_Post $post ) {
		return \get_post_meta( $post->ID, $this->name, ! $this->multiple );
	}

	/**
	 * Model: post_meta - update
	 *
	 * @access private
	 *
	 * @param  WP_Post $post
	 * @param  mixed $value
	 */
	private function update_post_meta( \WP_Post $post, $value ) {
		if ( is_array( $value ) ) {
			if ( ! $this->multiple )
				return;
			$nowValue = $this->get_post_meta( $post );
			$new = array_diff( $value, $nowValue );
			$old = array_diff( $nowValue, $value );
			if ( $new )
				foreach ( $new as $val )
					$this->add_post_meta( $post, $val, false );
			if ( $old )
				foreach ( $old as $del )
					$this->delete_post_meta( $post, $del );
		} else {
			$value = filter_var( $value, \FILTER_CALLBACK, [ 'options' => [ 'callback' => [ $this, 'filter' ] ] ] );
			if ( $value !== null )
				return \update_post_meta( $post->ID, $this->name, $value );
		}
	}

	/**
	 * Model: post_meta - add
	 *
	 * @access private
	 *
	 * @param  WP_Post $post
	 * @param  mixed $value
	 * @param  boolean $unique Optional
	 */
	private function add_post_meta( \WP_Post $post, $value, $unique = false ) {
		$value = filter_var( $value, \FILTER_CALLBACK, [ 'options' => [ 'callback' => [ $this, 'filter' ] ] ] );
		if ( $value !== null )
			return \add_post_meta( $post->ID, $this->name, $value, $unique );
	}

	/**
	 * Model: post_meta - delete
	 *
	 * @access private
	 *
	 * @param  WP_Post $post
	 * @param  mixed $value
	 */
	private function delete_post_meta( \WP_Post $post, $value ) {
		return \delete_post_meta( $post->ID, $this->name, $value );
	}

}
