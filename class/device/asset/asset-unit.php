<?php
namespace WPDW\Device\Asset;

abstract class asset_unit extends asset_abstract {
	use Model\post_meta, Model\post_attribute, Model\post, Model\meta_post_meta;

	/**
	 * @var string
	 */
	protected $model;

	/**
	 * @var string
	 */
	protected $prefix;
	protected $safix;

	/**
	 * Array_walk callback function
	 *
	 * @access protected
	 *
	 * @see    WPDW\Device\asset_trait::prepare_arguments()
	 *
	 * @param  mixed  &$arg
	 * @param  string $key
	 * @param  string $asset
	 * @return (void)
	 */
	protected static function arguments_walker( &$arg, $key, $asset ) {
		if ( $key === 'model' ) :
			/**
			 * @var string $model
			 */
			$arg = trait_exists( __NAMESPACE__ . '\\Model\\' . $arg ) ? $arg : null;
		elseif ( in_array( $key, [ 'prefix', 'safix' ], true ) ) :
			/**
			 * @var string $prefix|$safix
			 */
			$arg = filter_var( $arg, \FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		else :
			parent::arguments_walker( $arg, $key, $asset );
		endif;
	}

	/**
	 * @access protected
	 *
	 * @see    WPDW\Device\asset_trait::prepare_arguments()
	 *
	 * @param  array $args
	 * @return boolean
	 */
	protected static function is_met_requirements( Array $args ) {
		return $args['model'] || $args['name'][0] === '_' ? true : false;
	}

	/**
	 * Filter value(s)
	 * - Child class must define below method OR overwrite this method
	 * - Method: filter_singular( $value )
	 *
	 * @access public
	 *
	 * @param  mixed $value
	 * @return string|array|null
	 */
	public function filter( $value ) {
		static $_filter_multiple = false; // Flag for multidimentional array

		if ( $this->multiple && is_array( $value ) ) {

			if ( $_filter_multiple )
				return null;

			$filter_multiple = true; // Set flag true
			$filtered = [];
			foreach ( $value as $val )
				$filtered[] = $this->filter( $val );

			$_filter_multiple = false; // Reset flag

			return array_filter( $filtered );

		} else {

			return $this->filter_singular( $value );

		}
	}

	/**
	 * Filter input value(s)
	 * - Default is same as filter() method. If necessary, overwrite in class.
	 *
	 * @access public
	 *
	 * @param  mixed $value
	 * @return mixed|null
	 */
	public function filter_input( $value ) {
		return $this->filter( $value );
	}

	/**
	 * Get value
	 *
	 * @access public
	 *
	 * @uses   WPDW\Device\Asset\asset_abstract::check_dependency()
	 *
	 * @param  int|WP_Post $post
	 * @return mixed
	 */
	public function get( $post ) {
		if ( ! $this->model || ! ( $post = get_post( $post ) ) || ! $this->check_dependency( $post ) )
			return null;
		$get = 'get_' . $this->model;
		return $this->filter( $this->$get( $post ) );
	}

	/**
	 * Update value
	 *
	 * @access public
	 *
	 * @uses   WPDW\Device\Asset\asset_abstract::check_dependency()
	 *
	 * @param  int|WP_Post $post
	 * @param  mixed $value
	 */
	public function update( $post, $value ) {
		if ( ! $this->model || ! ( $post = get_post( $post ) ) || ! $this->check_dependency( $post ) )
			return null;
		$value = $this->filter_input( $value );
		$update = 'update_' . $this->model;
		if ( isset( $value ) )
			return $this->$update( $post, $value );
	}

	/**
	 * Render html element as form element
	 *
	 * @access public
	 *
	 * @uses   mimosafa\Decoder::getArrayToHtmlString() as getHtml()
	 *
	 * @param  WP_Post $post
	 * @return string  Entities html strings
	 */
	public function admin_form_element( \WP_Post $post ) {
		$value = $this->get( $post );
		$domArray = $this->admin_form_element_dom_array( $value );
		return self::getHtml( $domArray );
	}

	/**
	 * Get DOM array to render form html element
	 * - Child class must define below method OR overwrite this method
	 * - Method: single_admin_form_element_dom_array( $name, $val )
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

			$domArray[] = $this->single_admin_form_element_dom_array( $name, $val );
		} while ( next( $value ) !== false );

		return $domArray;
	}

	/**
	 * Utility method for generating DOM array (as admin form)
	 *
	 * @access protected
	 */

	/**
	 * Add fixtures
	 */
	protected function _add_fixtures_admin_form_element_dom_array( &$domArray ) {
		if ( ! $this->prefix && ! $this->safix )
			return;

		$return = [ 'element' => 'label', 'children' => [] ];

		if ( $this->prefix ) {
			$return['children'][] = [
				'element' => 'span',
				'text' => esc_html( $this->prefix ),
				'attribute' => [ 'class' => 'wpdw-admin-form-prefix']
			];
		}

		$return['children'][] = $domArray;

		if ( $this->safix ) {
			$return['children'][] = [
				'element' => 'span',
				'text' => $this->safix,
				'attribute' => [ 'class' => 'wpdw-admin-form-safix']
			];
		}

		// Overwrite $domArray
		$domArray = $return;
	}

}
