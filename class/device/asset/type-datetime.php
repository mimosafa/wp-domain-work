<?php
namespace WPDW\Device\Asset;

class type_datetime extends asset_simple {
	use asset_vars;

	protected $input_type = 'datetime_local';

	protected $input_format  = 'Y-m-d H:i:s';
	protected $output_format = 'Y-m-d H:i';

	protected $min = null;
	protected $max = null;

	protected $step = null;

	protected static function arguments_walker( &$arg, $key, $asset ) {
		if ( $key === 'input_type' && isset( $arg ) ) :
			static $typeLists = [ 'datetime_local', 'date', 'time' ];
			$arg = in_array( $arg, $typeLists, true ) ? $arg : $typeLists[0];
		elseif ( in_array( $key, [ 'input_format', 'output_format' ], true ) ) :
			// yet
		elseif ( in_array( $key, [ 'min', 'max'], true ) ) :
			// yet
		elseif ( $key === 'step' ) :
			$arg = self::validate_integer( $arg, null, 1 );
		else :
			parent::arguments_walker( $arg, $key, $asset );
		endif;
	}

	protected function filter_value( $value, $post = null ) {
		// @todo
		return filter_var( $value );
	}

	public function print_column( $value, $post_id ) {
		// yet
		return esc_html( $value );
	}

}
