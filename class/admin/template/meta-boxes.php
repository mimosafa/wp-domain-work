<?php

namespace admin\template;

class meta_boxes {
	use \singleton;

	protected function __construct() {
		//
	}

	public static function add( $arg ) {
		_var_dump( $arg );
	}

}
