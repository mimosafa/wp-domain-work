<?php
namespace WPDW\Device\Asset;

class type_set extends asset_assets {
	use asset_trait;

	protected static function arguments_walker( &$arg, $key, $asset ) {
		if ( $key === 'admin_form_style' ) :
			/**
			 * @var string $admin_form_style (Fixed: block)
			 */
			$arg = in_array( $arg, [ 'block', 'inline', 'hide' ], true ) ? $arg : 'block';
		else :
			parent::arguments_walker( $arg, $key, $asset );
		endif;
	}

	/**
	 * Render html element as form element
	 *
	 * @access public
	 *
	 * @uses   mimosafa\Decoder::getArrayToHtmlString() as getHtml()
	 *
	 * @param  WP_Post $post
	 * @return (void)
	 */
	public function admin_form_element( \WP_Post $post ) {
		if ( $this->admin_form_style !== 'inline' )
			return parent::admin_form_element( $post );
		
		$value = $this->get( $post );
		$domArray = $this->admin_form_element_dom_array( $value );
		return self::getHtml( $domArray );
	}

	/**
	 * Get DOM array to render form html element
	 *
	 * @access public
	 *
	 * @see    mimosafa\Decoder
	 *
	 * @param  mixed  $value
	 * @param  string $namespace
	 * @return array
	 */
	public function admin_form_element_dom_array( Array $value, $namespace = '' ) {
		$name = $namespace ? sprintf( '%s[%s]', $namespace, $this->name ) : $this->name;
		$fieldset = [ 'element' => 'fieldset', 'children' => [] ];
		$forms =& $fieldset['children'];

		$property = \WPDW\_property( $this->domain );
		foreach ( $this->assets as $asset ) {
			$val = $value[$asset] ?: '';
			$domArray = $property->$asset->admin_form_element_dom_array( $val, $name );
			$forms = array_merge( $forms, $domArray );
		}

		return [ $fieldset ];
	}

}
