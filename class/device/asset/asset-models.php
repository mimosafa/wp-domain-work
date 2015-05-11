<?php
namespace WPDW\Device\Asset;

trait asset_models {

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
			$value = filter_var( $value, \FILTER_CALLBACK, [ 'options' => [ $this, 'filter' ] ] );
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
		$value = filter_var( $value, \FILTER_CALLBACK, [ 'options' => [ $this, 'filter' ] ] );
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

	/**
	 * Model: post_attribute - get
	 *
	 * @access private
	 *
	 * @param  WP_Post $post
	 * @param  mixed $value
	 */
	private function get_post_attribute( \WP_Post $post ) {
		if ( property_exists( $post, $this->name ) )
			$return = $post->{$this->name};
		if ( 'menu_order' === $this->name )
			return (int) $return;
		if ( 'post_parent' === $this->name )
			return $return ? get_post( $return ) : null;
	}

}
