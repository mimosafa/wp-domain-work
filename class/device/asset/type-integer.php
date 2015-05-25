<?php
namespace WPDW\Device\Asset;

class type_integer extends asset_simple {
	use asset_vars;

	/**
	 * @var int|null
	 */
	protected $min = null;
	protected $max = null;

	/**
	 * @var int
	 */
	protected $step; // @todo

	/**
	 * Constructor
	 *
	 * @access public
	 *
	 * @uses   WPDW\Device\Asset\asset_simple::__construct()
	 *
	 * @param  array $args
	 * @return (void)
	 */
	public function __construct( Array $args ) {
		parent::__construct( $args );
		if ( is_int( $this->min ) && is_int( $this->max) && $this->min > $this->max )
			$this->min = $this->max = null;
	}

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
		if ( in_array( $key, [ 'min', 'max' ], true ) ) :
			/**
			 * @var int $min|$max
			 */
			$options = [
				'options' => [ 'default' => null ]
			];
			$arg = filter_var( $arg, \FILTER_VALIDATE_INT, $options );
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

			$options = [ 'default' => null ];
			/**
			 * Min-range
			 */
			if ( $this->min !== null )
				$options['min_range'] = $this->min;
			/**
			 * Max-range
			 */
			if ( $this->max !== null )
				$options['max_range'] = $this->max;

			return filter_var( $value, \FILTER_VALIDATE_INT, [ 'options' => $options ] );

		}
	}

	/**
	 * Print value in list table column - Hooked on '_wpdw_{$name}_column'
	 *
	 * @access public
	 *
	 * @see    WPDW\Device\Admin\posts_column::column_callback()
	 *
	 * @param  mixed $value
	 * @param  int   $post_id
	 * @return string
	 */
	public function print_column( $value, $post_id ) {
		return esc_html( $value );
	}

}
