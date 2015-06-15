<?php
namespace WPDW\Device\Asset\Model;

trait post_attribute {

	/**
	 * Model: post_attribute - get
	 *
	 * @access protected
	 *
	 * @param  WP_Post $post
	 * @param  mixed $value
	 */
	protected function get_post_attribute( \WP_Post $post ) {
		return property_exists( $post, $this->name ) ? $post->{$this->name} : null;
	}

	/**
	 * Model: post_attribute - update
	 *
	 * @access protected
	 *
	 * @param  WP_Post $post
	 * @param  mixed $value
	 */
	protected function update_post_attribute( \WP_Post $post, $value ) {
		// ..yet
	}
	
}
