<?php
namespace WPDW\Device\Asset;

abstract class asset_simple extends asset_abstract {
	use Model\post_meta, Model\post_attribute, Model\post;

	/**
	 * @var string
	 */
	protected $model;

	/**
	 * Abstract method: Filter singular value
	 *
	 * @access protected
	 *
	 * @param  mixed $value
	 * @return mixed|null
	 */
	abstract public function filter_singular( $value );

	/**
	 * Filter value(s)
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
	 * Input value filter. Default is same as filter() method.
	 * If necessary, overwrite in class.
	 *
	 * @access public
	 *
	 * @param  mixed $value
	 * @param  WP_Post $post
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
		if ( ! $this->model || ! $post = get_post( $post ) )
			return null;
		if ( ! $this->check_dependency( $post ) )
			return null;
		$get = 'get_' . $this->model;
		$value = $this->$get( $post );

		return $this->filter( $value );
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
		if ( ! $this->model || ! $post = get_post( $post ) )
			return null;
		if ( ! $this->check_dependency( $post ) )
			return null;
		$update = 'update_' . $this->model;
		$value = $this->filter_input( $value, $post );
		if ( isset( $value ) )
			return $this->$update( $post, $value );
	}

	/**
	 * Return recipe of the asset as array
	 *
	 * @access public
	 *
	 * @param  int|WP_Post $post
	 * @return array
	 */
	public function get_recipe( $post = null ) {
		$recipe = get_object_vars( $this );
		return $post ? array_merge( $recipe, [ 'value' => $this->get( $post ) ] ) : $recipe;
	}

	/**
	 * Array_walk callback function
	 *
	 * @see    WPDW\Device\asset_vars::prepare_arguments()
	 *
	 * @access protected
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
		return $args['model'] || $args['name'][0] === '_' ? true : false;
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
		$value = $this->get( $post );
		$domArray = $this->admin_form_element_dom_array( $value );
		#return var_export( $domArray, true );
		return self::getHtml( [ $domArray ] );
	}

}
