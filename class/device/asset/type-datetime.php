<?php
namespace WPDW\Device\Asset;

class type_datetime extends asset_simple {
	use asset_trait, Model\meta_post_meta;

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
	public function filter_singular( $value ) {

		// @todo
		return $value;

	}

	public function print_column( $value, $post_id ) {
		// yet
		return esc_html( $value );
	}

	/**
	 * Get DOM array to render form html element
	 *
	 * @access public
	 *
	 * @see    mimosafa\Decoder
	 *
	 * @todo   multiple value
	 *
	 * @param  mixed  $value
	 * @param  string $namespace
	 * @return array
	 */
	public function admin_form_element_dom_array( $value, $namespace = '' ) {
		$name  = $namespace ? sprintf( '%s[%s]', $namespace, $this->name ) : $this->name;
		$name .= $this->multiple ? '[]' : '';
		$value = (array) $value;

		$domArray = [];
		do {
			$val = current( $value );
			$val = $val !== false ? $val : '';
			$domArray[] = [
				'element' => 'input',
				'attribute' => [
					'type' => $this->unit,
					'name' => esc_attr( $name ),
					'value' => esc_attr( $val ),
				]
			];
		} while ( next( $value ) !== false );

		return $domArray;
	}

}
