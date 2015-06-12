<?php
namespace WPDW\Device\Asset;

class type_integer extends asset_unit implements asset, writable {
	use asset_trait;

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
	 * Constructor
	 *
	 * @access public
	 *
	 * @uses   WPDW\Device\Asset\asset_simple::__construct()
	 *
	 * @param  WPDW\Device\Asset\verified $args
	 * @return (void)
	 */
	public function __construct( verified $args ) {
		parent::__construct( $args );
		if ( is_int( $this->min ) && is_int( $this->max) && $this->min > $this->max )
			$this->min = $this->max = null;
	}

	/**
	 * @access public
	 *
	 * @param  mixed $value
	 * @return int|array|null
	 */
	public function filter_singular( $value ) {
		$options = [ 'default' => null ];

		// Min-range
		if ( $this->min !== null )
			$options['min_range'] = $this->min;
		// Max-range
		if ( $this->max !== null )
			$options['max_range'] = $this->max;

		return filter_var( $value, \FILTER_VALIDATE_INT, [ 'options' => $options ] );
	}

	/**
	 * @access public
	 *
	 * @param  string $name
	 * @param  string $value
	 */
	public function single_admin_form_element_dom_array( $name, $value ) {
		$name  = filter_var( $name );
		$value = filter_var( $value );

		$domArray = [
			'element' => 'input',
			'attribute' => [ 'type' => 'number', 'name' => esc_attr( $name ), 'value' => esc_attr( $value ), ]
		];

		if ( $this->min )
			$domArray['attribute']['min'] = esc_attr( $this->min );
		if ( $this->max )
			$domArray['attribute']['max'] = esc_attr( $this->max );
		if ( $this->step )
			$domArray['attribute']['step'] = esc_attr( $this->step );

		$this->_add_fixtures_admin_form_element_dom_array( $domArray );

		return $domArray;
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
