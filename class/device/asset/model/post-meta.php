<?php
namespace WPDW\Device\Asset\Model;

trait post_meta {

	/**
	 * Model: post_meta - get
	 *
	 * @access protected
	 *
	 * @param  WP_Post $post
	 * @return mixed
	 */
	protected function get_post_meta( \WP_Post $post ) {
		return \get_post_meta( $post->ID, $this->name, ! $this->multiple );
	}

	/**
	 * Model: post_meta - update
	 *
	 * @access protected
	 *
	 * @param  WP_Post $post
	 * @param  mixed $value
	 */
	protected function update_post_meta( \WP_Post $post, $value ) {
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
			return \update_post_meta( $post->ID, $this->name, $value );
		}
	}

	/**
	 * Model: post_meta - add
	 *
	 * @access protected
	 *
	 * @param  WP_Post $post
	 * @param  mixed $value
	 * @param  boolean $unique Optional
	 */
	protected function add_post_meta( \WP_Post $post, $value, $unique = false ) {
		return \add_post_meta( $post->ID, $this->name, $value, $unique );
	}

	/**
	 * Model: post_meta - delete
	 *
	 * @access protected
	 *
	 * @param  WP_Post $post
	 * @param  mixed $value
	 */
	protected function delete_post_meta( \WP_Post $post, $value ) {
		return \delete_post_meta( $post->ID, $this->name, $value );
	}

}
