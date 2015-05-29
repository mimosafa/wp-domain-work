<?php
namespace WPDW\Device\Asset\Model;

trait meta_post_meta {

	/**
	 * @access public
	 */
	public function get_meta_post_meta( $post_id, $prefix ) {
		$prefix .= $this->name;

		$i = 0;
		$return = [];
		do {
			$meta_key = $prefix . '_' . $i;
			$value = get_post_meta( $post_id, $meta_key, true );
			$return[] = $value;
			if ( ! $this->multiple )
				break;
			$i++;
		} while ( $value );

		return $this->multiple ? $return : array_shift( $return );
	}

}
