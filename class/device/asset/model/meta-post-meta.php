<?php
namespace WPDW\Device\Asset\Model;

trait meta_post_meta {

	/**
	 * @access public
	 */
	public function get_meta_post_meta( $post_id, $prefix ) {
		$prefix .= $this->name;
		$multipleCache = $this->multiple; // Cache

		$i = 0;
		$return = [];
		do {
			$meta_key = $prefix . '_' . $i;
			$this->multiple = false; // Fix false for filter
			$value = $this->filter( get_post_meta( $post_id, $meta_key, true ) );
			$return[] = $value;
			$this->multiple = $multipleCache; // Reset
			if ( ! $this->multiple )
				break;
			$i++;
		} while ( $value );

		return $this->multiple ? $return : array_shift( $return );
	}

}
