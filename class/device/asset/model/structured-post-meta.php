<?php
namespace WPDW\Device\Asset\Model;

trait structured_post_meta {
	use post_meta;

	/**
	 *
	 */

	/**
	 * @access protected
	 *
	 * @param  WP_Post $post
	 * @return array
	 */
	protected function get_structured_post_meta( \WP_Post $post ) {
		//
	}

	/**
	 * Model: post_meta - update
	 *
	 * @access protected
	 *
	 * @param  WP_Post $post
	 * @param  mixed $value
	 */
	protected function update_structured_post_meta( \WP_Post $post, $value ) {
		//
	}

}
