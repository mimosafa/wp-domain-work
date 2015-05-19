<?php
namespace WPDW\Device\Asset\Model;

trait post {

	/**
	 * Model: post_children - get
	 *
	 * @access protected
	 *
	 * @param  WP_Post $post
	 * @param  mixed $value
	 */
	protected function get_post( \WP_Post $post ) {
		$q = $this->query_args ?: [];
		if ( $this->post_type )
			$q['post_type'] = $this->post_type;
		if ( $this->context && ( $method = $this->context . '_arguments' ) && method_exists( __TRAIT__, $method ) )
			$this->$method( $q, $post );
		return get_posts( $q );
	}

	/**
	 * @access protected
	 *
	 * @param  array &$args
	 * @param  WP_Post $post
	 * @return (void)
	 */
	protected function post_children_arguments( Array &$args, \WP_Post $post ) {
		$args['post_parent'] = $post->ID;
	}

}
