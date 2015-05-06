<?php
namespace WPDW\Device\Status;

class pending {

	public static $definition = [
		'name' => \FILTER_SANITIZE_FULL_SPECIAL_CHARS,
		'save' => \FILTER_SANITIZE_FULL_SPECIAL_CHARS,
	];

	public static function get_filter_definition() {
		//
	}

}
