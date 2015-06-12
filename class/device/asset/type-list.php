<?php
namespace WPDW\Device\Asset;

class type_list extends asset_unit implements asset, writable {
	use asset_trait;

	/**
	 * @var array|string
	 */
	protected $options;

	/**
	 * @var string
	 */
	protected $admin_form_style = 'select';

	/**
	 * @access public
	 *
	 * @uses   WPDW\Device\Asset\asset_simple::arguments_walker()
	 *
	 * @param  mixed &$arg
	 * @param  string $key
	 * @param  string $asset
	 * @return (void)
	 */
	public static function arguments_walker( &$arg, $key, $asset ) {
		if ( $key === 'options' ) :
			/**
			 * @var array|string $options
			 */
			self::_options_filter( $arg );
		elseif ( $key === 'admin_form_style' ) :
			static $form_styles = [ 'selct', 'ragio', 'ragio_inline', 'checkbox', 'checkbox_inline' ];
			$arg = in_array( $arg, $form_styles, true ) ? $arg : 'select';
		else :
			parent::arguments_walker( $arg, $key, $asset );
		endif;
	}

	/**
	 * Validate $options argument
	 * - More validation exists in _validate_after_constructed()
	 *
	 * @access protected
	 *
	 * @uses   WPDW\Device\Asset\provision::is_valid_asset_name_string()
	 */
	protected static function _options_filter( &$arg ) {
		if ( is_array( $arg ) ) {
			$def = array_fill_keys( array_keys( $arg ), \FILTER_REQUIRE_SCALAR );
			$arg = array_filter( filter_var_array( $arg, $def ) );
		} else if ( ! provision::is_valid_asset_name_string( $arg ) ) {
			$arg = null;
		}
	}

	/**
	 * @access protected
	 *
	 * @param  array $args
	 * @return boolean
	 */
	protected static function is_met_requirements( Array $args ) {
		return $args['options'] ? true : false;
	}

	/**
	 * @todo
	 *
	 * Validate $options argument
	 *
	 * @access protected
	 *
	 * @return boolean
	 */
	protected function _validate_after_constructed() {
		if ( is_array( $this->options ) )
			return true;

		$maybeAsset = $this->options;
		if ( ! $optAsset = \WPDW\_property( $this->domain )->get_setting( $maybeAsset ) )
			return false;
		if ( ! $optAsset['multiple'] )
			return false;
		//
		return true;
	}

	/**
	 * @access public
	 *
	 * @param  mixed $value
	 * @return mixed
	 */
	public function filter_singular( $value ) {

		// @todo
		return $value;

	}

	/**
	 * Get DOM array to render form html element
	 *
	 * @access public
	 *
	 * @see    mimosafa\Decoder
	 *
	 * @todo   Other form style (radio, checkbox)
	 *
	 * @param  mixed  $value
	 * @param  string $namespace
	 * @return array
	 */
	public function admin_form_element_dom_array( $value, $namespace = '' ) {
		$name  = $namespace ? sprintf( '%s[%s]', $namespace, $this->name ) : $this->name;
		$value = (array) $value;

		$select = [ 'element' => 'select', 'children' => [], 'attribute' => [ 'name' => $name ] ];
		$attr =& $select['attribute'];
		if ( $this->multiple ) {
			$attr['multiple'] = 'multiple';
		}
		$options =& $select['children'];
		if ( ! $this->required ) {
			$options[] = [ 'element' => 'option', 'text' => '-' ];
		}
		foreach ( $this->options as $key => $val ) {
			$option = [
				'element' => 'option',
				'text' => esc_html( $val ),
				'attribute' => [ 'value' => esc_attr( $key ) ]
			];
			if ( in_array( $key, $value, true ) )
				$option['attribute']['selected'] = 'selected';
			$options[] = $option;
		}

		return [ $select ];
	}

	public function print_column( $value, $post_id ) {}

}
