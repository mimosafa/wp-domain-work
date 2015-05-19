<?php
namespace WPDW\Device\Asset;

abstract class asset_simple extends asset_abstract {
	use Model\post_meta, Model\post_attribute, Model\post;

	/**
	 * @var string
	 */
	protected $model;

	/**
	 * Abstract method: Filter values
	 *
	 * @access protected
	 *
	 * @param  mixed $value
	 * @param  null|WP_Post Optional
	 * @return mixed|null
	 */
	abstract protected function filter_value( $value, $post = null );

	/**
	 * Input value filter. default is same as filter_value method.
	 * If necessary, overwrite in class.
	 *
	 * @access protected
	 *
	 * @param  mixed $value
	 * @param  WP_Post $post
	 * @return mixed|null
	 */
	protected function filter_input( $value, \WP_Post $post ) {
		return $this->filter_value( $value, $post );
	}

	/**
	 * Get value
	 *
	 * @access public
	 *
	 * @param  int|WP_Post $post
	 * @return mixed
	 */
	public function get( $post ) {
		if ( ! $this->model || ! $post = get_post( $post ) )
			return;
		$get = 'get_' . $this->model;
		return $this->filter( $this->$get( $post ), $post );
	}

	/**
	 * Update value
	 *
	 * @access public
	 *
	 * @uses   WPDW\Device\type_{$type}::input_filter()
	 *
	 * @param  int|WP_Post $post
	 * @param  mixed $value
	 */
	public function update( $post, $value ) {
		if ( ! $this->model || ! $post = get_post( $post ) )
			return;
		$update = 'update_' . $this->model;
		return $this->$update( $post, $this->filter_input( $value, $post ) );
	}

	/**
	 * Filter values
	 *
	 * @access protected
	 *
	 * @param  mixed $value
	 * @param  null|WP_Post $post (Optional) 
	 */
	protected function filter( $value, $post = null ) {
		if ( ! $value = parent::filter( $value, $post ) )
			return null;

		/**
		 * Define filter callback method.
		 * If $post is set input validation.
		 */
		$callback = isset( $post ) ? 'filter_input' : 'filter_value';

		if ( $this->multiple ) {
			$filtered = [];
			foreach ( $value as $val ) {
				$val = $this->$callback( $val, $post );
				if ( isset( $val ) )
					$filtered[] = $val;
			}
			return $filtered;
		}
		return $this->$callback( $value, $post );
	}

	/**
	 * Return recipe of the asset as array
	 *
	 * @access public
	 *
	 * @param  int|WP_Post $post
	 * @return array
	 */
	public function get_recipe( $post ) {
		return array_merge( get_object_vars( $this ), [ 'value' => $this->get( $post ) ] );
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
			$arg = trait_exists( __NAMESPACE__ . '\\Model\\' . $arg ) ? $arg : null;
		else :
			parent::arguments_walker( $arg, $key, $asset );
		endif;
	}

	protected static function is_met_requirements( Array $args ) {
		return $args['model'] ? true : false;
	}

}
