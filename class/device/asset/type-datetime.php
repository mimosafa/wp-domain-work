<?php
namespace WPDW\Device\Asset;

class type_datetime extends asset_simple {
	use asset_vars;

	protected $unit = 'datetime_local';

	protected $input_format  = 'Y-m-d H:i:s';
	protected $output_format = 'Y-m-d H:i';

	protected $min = null;
	protected $max = null;

	protected $step = null;

	/**
	 * @access protected
	 *
	 * @uses   WPDW\Device\Asset\asset_simple::arguments_walker()
	 *
	 * @param  mixed &$arg
	 * @param  string $key
	 * @param  string $asset
	 * @return (void)
	 */
	protected static function arguments_walker( &$arg, $key, $asset ) {
		if ( $key === 'unit' && isset( $arg ) ) :
			/**
			 * @var string $unit
			 */
			static $typeLists = [ 'datetime_local', 'date', 'time' ];
			$arg = in_array( $arg, $typeLists, true ) ? $arg : $typeLists[0];
		elseif ( in_array( $key, [ 'input_format', 'output_format' ], true ) ) :
			// yet
		elseif ( in_array( $key, [ 'min', 'max'], true ) ) :
			// yet
		elseif ( $key === 'step' ) :
			/**
			 * @var int $step
			 */
			$options = [
				'options' => [
					'default' => null,
					'min_range' => 1
				]
			];
			$arg = filter_var( $arg, \FILTER_VALIDATE_INT, $options );
		else :
			parent::arguments_walker( $arg, $key, $asset );
		endif;
	}

	/**
	 * @access public
	 *
	 * @param  mixed $value
	 * @return int|array|null
	 */
	public function filter( $value ) {
		static $_filter_multiple = false;
		if ( $this->multiple && is_array( $value ) ) {

			if ( $_filter_multiple )
				return null;
			$filter_multiple = true;
			$filtered = [];
			foreach ( $value as $val )
				$filtered[] = $this->filter( $val );
			return array_filter( $filtered );

		} else {

			// @todo
			return $value;

		}

	}

	public function print_column( $value, $post_id ) {
		// yet
		return esc_html( $value );
	}

}
