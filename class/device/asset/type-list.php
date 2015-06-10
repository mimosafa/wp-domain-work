<?php
namespace WPDW\Device\Asset;

class type_list extends asset_simple {
	use asset_trait, Model\meta_post_meta;

	/**
	 * @var array
	 */
	protected $options;

	/**
	 * @var string
	 */
	protected $admin_form_style = 'select';

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

	public function print_column( $value, $post_id ) {}

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
			 * @var array $options
			 */
			if ( ! is_array( $arg ) ) {
				$arg = null;
			} else {
				$def = array_fill_keys( array_keys( $arg ), \FILTER_REQUIRE_SCALAR );
				$arg = array_filter( filter_var_array( $arg, $def ) );
			}
		elseif ( $key === 'admin_form_style' ) :
			$arg = in_array( $arg, [ 'selct', 'ragio', 'ragio_inline', 'checkbox', 'checkbox_inline' ], true ) ? $arg : 'select';
		else :
			parent::arguments_walker( $arg, $key, $asset );
		endif;
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
		#$name .= $this->multiple ? '[]' : '';
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

}
