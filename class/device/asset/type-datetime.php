<?php
namespace WPDW\Device\Asset;

class type_datetime extends asset_abstract {
	use asset_vars, asset_models;

	protected $input_type = 'datetime_local';

	protected $input_format  = 'Y-m-d H:i:s';
	protected $output_format = 'Y-m-d H:i';

	protected $min = null;
	protected $max = null;

	protected static function arguments_walker( &$arg, $key, $asset ) {
		if ( $key === 'input_type' && isset( $arg ) ) :
			static $typeLists = [ 'datetime_local', 'date', 'time' ];
			$arg = in_array( $arg, $typeLists, true ) ? $arg : $typeLists[0];
		elseif ( in_array( $key, [ 'input_format', 'output_format' ], true ) ) :
			// yet
		elseif ( in_array( $key, [ 'min', 'max'], true ) ) :
			// yet

		else :
			parent::arguments_walker( $arg, $key, $asset );
		endif;
	}

	public function output_filter( $var ) {
		// yet
		return filter_var( $var /**/ );
	}

	public function print_column( $value, $post_id ) {
		// yet
		return esc_html( $value );
	}

}
