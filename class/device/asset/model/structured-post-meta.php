<?php
namespace WPDW\Device\Asset\Model;

trait structured_post_meta {

	/**
	 * @access protected
	 *
	 * @param  WP_Post $post
	 * @return array
	 */
	protected function get_structured_post_meta( \WP_Post $post ) {
		$param = get_post_meta( $post->ID, $this->name, true );

		if ( $this->with_key && is_array( $param ) )
			$array = $param;
		else if ( ! $this->with_key && filter_var( $param, \FILTER_VALIDATE_INT ) )
			$array = range( 0, $param - 1 );

		if ( ! isset( $array ) )
			return [];

		$return = [];
		$property = \WPDW\_property( $this->domain );
		foreach ( $array as $key ) {
			$return[$key] = [];
			foreach ( $this->assets as $asset ) {
				$prefix = $this->name . '_' . $key;
				$return[$key][$asset] = $property->$asset->get_meta_post_meta( $post->ID, $prefix );
			}
		}
		return $this->multiple ? $return : array_shift( $return );
	}

	/**
	 * @access protected
	 *
	 * @param  WP_Post $post
	 * @param  mixed $value
	 */
	protected function update_structured_post_meta( \WP_Post $post, $value ) {
		//
	}

}
