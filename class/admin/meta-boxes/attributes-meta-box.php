<?php

namespace admin\meta_boxes;

/**
 * @see https://github.com/WordPress/WordPress/blob/master/wp-admin/includes/meta-boxes.php#L709
 */
class attributes_meta_box {
	use \singleton;

	protected function __construct() {
		//
	}

	public static function set( $attr, $property ) {
		_var_dump( $attr );
		_var_dump( $property );
	}

	//

}
