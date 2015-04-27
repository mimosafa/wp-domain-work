<?php
/*
Register As: Custom Post Type
Supports: title, custom-fields
Menu Icon: dashicons-calendar-alt
*/
namespace WP_Domain\event;

class property {
	use \WPDW\Device\property;

	private $assets = [

		'date' => [
			'type' => 'date',
			'date_format' => 'Y-m-d',
		],

	];

}
