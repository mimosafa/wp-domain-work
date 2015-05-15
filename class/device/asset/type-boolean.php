<?php
namespace WPDW\Device\Asset;

class type_boolean extends asset_abstract {
	use asset_vars, asset_models;

	// yet

	public function __construct( Array $args ) {
		parent::__construct( $args );
		if ( $this->multiple )
			$this->multiple = false;
	}

	public static function arguments_walker( &$arg, $key, $asset ) {
		// yet
		parent::arguments_walker( $arg, $key, $asset );
	}

	protected function output_filter( $value ) {
		return filter_var( $value, \FILTER_VALIDATE_BOOLEAN, \FILTER_NULL_ON_FAILURE );
	}

	protected function input_filter( $value, \WP_Post $post ) {
		// @todo
		return $value;
	}

	public function print_column( $value, $post_id ) {
		$output = $this->label ?: (string) $value;
		return $value ? $output : '';
	}

}
